# -----------------------------------------------------------------------------
# $Id: SendMessage.pm 11365 2008-05-10 14:58:28Z topia $
# -----------------------------------------------------------------------------
# SendMessage - メッセージを外部から送信するためのモジュール。
# -----------------------------------------------------------------------------
# Copyright (C) 2004 Yoichi Imai <yoichi@silver-forest.com>
package System::SendMessage;
use strict;
use warnings;
use base qw(Module);
use Mask;
use Multicast;
use ControlPort;
use Auto::Utils;
use Tiarra::Utils;

sub control_requested {
    my ($this,$request) = @_;
    # 外部コントロールプログラムからのメッセージが来た。
    # 戻り値はControlPort::Reply。
    #
    # $request:
    #    内容 : ControlPort::Request
    #          送られたリクエスト

    # << NOTIFY System::SendMessage TIARRACONTROL/1.0
    # << Channel: !????channel@network
    # << Charset: UTF-8
    # << Text: message

    # >> TIARRACONTROL/1.0 200 OK

    my $mask = $request->table->{Channel};
    my $text = $request->table->{Text};
    my $command = utils->cond_yesno($request->table->{Notice}, 1) ?
	'NOTICE' : 'PRIVMSG';
    unless ($mask) {
	return new ControlPort::Reply(403, "Channel is not set");
    }
    unless ($text) {
	return new ControlPort::Reply(403, "Doesn't have remark");
    }

    my ($channel_mask, $network_name) = Multicast::detach($mask);

    my $server = $this->_runloop->network($network_name);
    unless (defined $server) {
	return new ControlPort::Reply(404, "Server Not Found");
    }

    my $matched = 0;

    foreach my $chinfo ($server->channels_list) {
	  if (Mask::match_array([$channel_mask], $chinfo->name)) {
	    ++$matched;
	    Auto::Utils::sendto_channel_closure(
		$chinfo->fullname, $command, undef, undef, undef, 0
	       )->($text);
           $this->_runloop->mod_manager->get('Log::Channel')->message_arrived(
               Tiarra::IRC::Message->new(
                 Command => $command,
                 Params  => [ $chinfo->fullname, $text ]
               ),
               $this->_runloop->{sockets}->[1]
          );
	  }
    }
    if ($matched) {
	my $reply = ControlPort::Reply->new(200, 'OK');
	$reply->MatchedChannels($matched);
	return $reply;
    } else {
	return new ControlPort::Reply(404, "Channel Not Found");
    }
}

1;
