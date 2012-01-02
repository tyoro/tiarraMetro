package Log::DBI;
use strict;
use warnings;
use IO::File;
use File::Spec;
use Tiarra::Encoding;
use base qw(Module);
use Module::Use qw(Tools::DateConvert Log::Logger Log::Writer);
use Tools::DateConvert;
use Log::Logger;
use Log::Writer;
use ControlPort;
use Mask;
use Multicast;

use DBI;

sub new {
    my $class = shift;
    my $this = $class->SUPER::new(@_);
    $this->{channels} = []; # 要素はマスク
    $this->{matching_cache} = {}; # 適当な文字列
    $this->{writer_cache} = {}; # <チャンネル名,Log::Writer>
    $this->{logger} =
        Log::Logger->new(
            sub {
                $this->_search_and_write(@_);
            },
            $this,
            'S_PRIVMSG','C_PRIVMSG','S_NOTICE','C_NOTICE');

    $this->_init;
    $this->_load_replace;
    $this->_load_schema;

    main::printmsg("Log::DBI loaded.");

    $this;
}

sub _init {
    my $this = shift;
    foreach ($this->config->channel('all')) {
	push @{$this->{channels}},$_;
    }

    $this;
}

sub _load_replace {
    my $self = shift;

    foreach my $line ($self->config->replace('all')) {
        my ($match, $after) = split(/\s+/, $line);
        push(@{ $self->{_replace} }, +{ match => $match, after => $after });
    }
}

sub _load_schema {
    my $self = shift;

    my $param;
    $param->{source} = $self->config->source;
    $param->{user} = $self->config->user;
    $param->{pass} = $self->config->pass;
    
	$param->{charset} = $self->config->charset;


    my $dbh = DBI->connect($param->{source}, $param->{user}, $param->{pass},
                       { RaiseError => 1, AutoCommit => 1 })
        or die $DBI::errstr;

	$dbh->do("SET NAMES ".$self->config->charset);

    $self->{dbh} = $dbh;
}

sub _resolve_id {
    my ($self, $moniker, $name) = @_;
    my $dbh = $self->{dbh};

    die unless ($moniker =~ /^(?:channel|nick)$/i);

    $moniker = lc($moniker);
    my $id = $self->{_table_maps}->{$moniker}->{$name};
    return $id if $id;

    my $sth = $dbh->prepare("select id from $moniker where name = ?")
         or $dbh->errstr;
    $sth->execute($name)
        or die $sth->errstr;

    while (my @rel = $sth->fetchrow_array) {
        my $data;
        @{$data}{qw/id/} = @rel;

        $id = $self->{_table_maps}->{$moniker}->{$name} = $data->{id};
        $sth->finish;
        return $id;
    }

    $sth = $dbh->prepare("insert into $moniker (name,created_on,updated_on) values (?,now(),now())")
        or $dbh->errstr;
    $sth->execute($name)
        or die $sth->errstr;

    $id = $self->{_table_maps}->{$moniker}->{$name} = $dbh->{mysql_insertid};
    return $id;
}

sub store {
    my ($self, $param) = @_;
    my $dbh = $self->{dbh};

    my $channel = delete($param->{channel});
	if( $channel ne "priv" ){
    	for my $rule (@{ $self->{_replace} }) {
    	    $channel =~ s/$rule->{match}/$rule->{after}/;
    	}

    	$param->{channel_id} = $self->_resolve_id('Channel', $channel);
	}
    $param->{nick_id} = $self->_resolve_id('Nick', delete($param->{nick}));

    my @key = keys(%$param);
    my @val = ();
    foreach my $value (@key) {
      push(@val, $param->{$value});
    }

    (my $str = ('?,' x @key)) =~ s/,$//;
    my $sql = sprintf("insert into %s (%s) values (%s)",
					  $channel eq "priv" ? 'priv': 'log',
                      join(",", @key) . ",created_on,updated_on",
                      $str . ",now(),now()",
                  );

    # main::printmsg("sql: $sql");
    # main::printmsg("  with: " . join(",", @val));
    my $sth = $dbh->prepare($sql)
         or $dbh->errstr;
    $sth->execute(@val)
        or die $sth->errstr;

    return $dbh->{mysql_insertid};
}

sub sync {
    my $this = shift;
    RunLoop->shared->notify_msg("Channel logs synchronized.");
}

sub control_requested {
    my ($this,$request) = @_;
    if ($request->ID eq 'synchronize') {
        $this->sync;
        ControlPort::Reply->new(204,'No Content');
    }
    else {
        die "Log::Channel received control request of unsupported ID ".$request->ID."\n";
    }
}

sub message_arrived {
    my ($this,$message,$sender) = @_;

    # __PACKAGE__/commandにマッチするか？
    if (Mask::match(lc($this->config->command || '*'),lc($message->command))) {
        $this->{logger}->log($message,$sender);
    }

    $message;
}

*S_PRIVMSG = \&PRIVMSG_or_NOTICE;
*S_NOTICE = \&PRIVMSG_or_NOTICE;
*C_PRIVMSG = \&PRIVMSG_or_NOTICE;
*C_NOTICE = \&PRIVMSG_or_NOTICE;
sub PRIVMSG_or_NOTICE {
    my ($this,$msg,$sender) = @_;
    my $target = Multicast::detatch($msg->param(0));
    my $is_priv = Multicast::nick_p($target);
    my $cmd = $msg->command;

    my $line = do {
        if ($is_priv) {
            # privの時は自分と相手を必ず区別する。
            if ($sender->isa('IrcIO::Client')) {
  		          +{ channel => 'priv', nick => $msg->param(0), msg => $msg->param(1), is_notice => $cmd eq 'PRIVMSG' ? 0 : 1,  is_me => 1 };
            } else {
				 if( $msg->nick ){
  		         	 +{ channel => 'priv', nick => $msg->nick , msg => $msg->param(1), is_notice => $cmd eq 'PRIVMSG' ? 0 : 1, is_me => 0 };
				 }else{
				 	# server message
  		         #	 +{ channel => 'priv', nick => $sender->current_nick, msg => $msg->param(1), is_notice => $cmd eq 'PRIVMSG' ? 0 : 1, is_me => 0 };
				 	
				 }
            }
        }
        else {
            my $nick = do {
                if ($sender->isa('IrcIO::Client')) {
                    RunLoop->shared_loop->network(
                      (Multicast::detatch($msg->param(0)))[1])
                        ->current_nick;
                }
                else {
                    $msg->nick || $sender->current_nick;
                }
            };
            +{ channel => $msg->param(0), nick => $nick, log => $msg->param(1), is_notice => $cmd eq 'PRIVMSG' ? 0 : 1 };
        }
    };

    [$is_priv ? 'priv' : $msg->param(0),$line];
}

sub _channel_match {
    my ($this,$channel) = @_;

    my $cached = $this->{matching_cache}->{$channel};
    if (defined $cached) {
	if ($cached eq '') {
	    # マッチするエントリは存在しない、という結果がキャッシュされている。
	    return undef;
	}
	else {
	    return $cached;
	}
    }

    foreach my $ch (@{$this->{channels}}) {
		if (Mask::match($ch,$channel)) {
		#if ( $ch eq $channel ) {
	    	$this->{matching_cache}->{$channel} = 'match';
			return 'match';
		}
    }
    $this->{matching_cache}->{$channel} = '';
    undef;
}

sub _search_and_write {
    my ($this,$channel,$line) = @_;
	if( $channel eq 'priv' ){
    	$this->_write($channel,$line);
	}else{
    	my $mask = $this->_channel_match($channel);
   		if (defined $mask) {
    		$this->_write($channel,$line);
    	}
	}
}

sub _write {
    my ($this,$channel,$line) = @_;

    if (ref($line) eq 'HASH') {
      	$this->store($line);
    } else {
		if( defined($line) && $line ne '' ){
			main::printmsg( Tiarra::Encoding->new( "$line\n",'utf8')->conv( $this->config->charset || 'jis'));
		}
    }
}

sub flush_all_file_handles {
    my $this = shift;
}

sub destruct {
    my $this = shift;
    # 開いている全てのLog::Writerを閉じて、キャッシュを空にする。
    foreach my $cached_elem (values %{$this->{writer_cache}}) {
        eval {
            $cached_elem->unregister;
        };
    }
    %{$this->{writer_cache}} = ();
}

1;
