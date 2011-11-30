<div class="metro-pivot">
<div class='pivot-item'>
	<h3 name="list">channel</h3>
	<ul class="channel_list">
	<?php foreach( $channels as $ch ){ ?>
	<li id="ch_<?php print $ch['id']; ?>" class="<?php if($ch['cnt']>0){ print "new"; } ?>"><span class="ch_name"><?php print $ch['name']; ?></span>&nbsp;
		<span class="ch_num">
		<?php if( !empty($ch['cnt']) ){ ?>
		<small><?php print $ch['cnt']; ?></small>
		<?php } ?>
		</span>
	</li>
	<?php } ?>
	</ul>
	<h4>search</h4>
	<form method="POST" id="search_form" role="search">
	<input type="text" name="word"  id="keyword" />
	<select name="channel" id="channel_select">
		<option value="" >----</option>
		<?php foreach( $channels as $ch ){ ?>
			<option value="<?php print $ch['id']; ?>"><?php print $ch['name']; ?></option>
		<?php } ?>
	</select>
	<input type="submit" id="search" name="search" value='search' />
	</form>
	<h4>util</h4>
	<input type="button" id="unread_reset" value="unread reset" />
</div>
<div class='pivot-item'>
	<h3 id="ch_name" name="channel" ></h3>
	<form method="POST" id="post_form">
		<input type="text" name="post" id="message" /><input type="submit" value="post" />
	</form>
	<hr/>
	<table id="list" class="list">
<?php /*		<thead>
			<tr>
				<th>nick</th><th>log</th><th>time</th>
			</tr>
		</thead>
*/ ?>
		<tbody></tbody>
	</table>
	<div id="ch_foot"></div>
</div>
<div class='pivot-item'>
	<h3 name="search"></h3>
	<span id="search_result_message">search result</span>
	<table id="search-list" class="list">
<?php /*
		<thead>
		<tr>
			<th>channel</th><th>nick</th><th>log</th><th>time</th>
		</tr>
		</thead>
*/ ?>
		<tbody></tbody>
	</table>
	<div id="search_foot"></div>
</div>
<script>
$(function(){
    var Class = function(){ return function(){this.initialize.apply(this,arguments)}};

	var TiarraMetroClass = new Class();
	TiarraMetroClass.prototype = {
		initialize: function( param ){
			var self = this;
			this.max_id = param.max_id;
			this.currentChannel = param.currentChannel;
			this.chLogs = param.chLogs;
			this.updating = param.updating;
			this.jsConf = param.jsConf;
			this.mountPoint = param.mountPoint;

			this.autoReload =  setInterval(function(){self.reload();}, this.jsConf["update_time"]*1000);
			this.htmlInitialize();
		},
		htmlInitialize: function(){
			var self = this;
			$('ul.channel_list li').click(function(){
				channel_id = this.id.substring(3);
				channel_name = self.getChannelName(channel_id);

				self.selectChannel( channel_id, channel_name );

				self.myPushState(channel_name,'/channel/'+channel_id);
			});

			$('form#post_form').submit(function(){
				message = $('input#message').val();
				if( message.length == 0 ){ return false; }

				$('input#message').attr('disabled','disabled');
				$('form#post_form submit').attr('disabled','disabled');

				$.ajax({
					url:self.mountPoint+'/api/post/',
					data:{
						channel_id:self.currentChannel,
						post:message,
					},
					dataType:'json',
					type:'POST',
					success:function(){
						$('input#message').attr('disabled','');
						$('form#post_form submit').attr('disabled','');
						$('input#message').val('');
					},
					error:function(){
						$('input#message').attr('disabled','');
						$('form#post_form submit').attr('disabled','');
					},
				});
				return false;
			});

			$('form#search_form').submit(function(){
				kw = $('input#keyword').val();
				if( kw.length == 0 ){ return false; }

				$('#search-list tbody tr').each(function( i,e ){ $(e).remove(); });
				$('div#search_foot').html( '<div id="spinner"><img src="images/spinner_b.gif" width="32" height="32" border="0" align="center" alt="searching..." /></div>' );

				$('div.headers span.header[name=search]').html( 'search' );
				if( ! $("div.metro-pivot").data("controller").isCurrentByName( 'search' ) ){
					$("div.metro-pivot").data("controller").goToItemByName('search');
				}

				d = { keyword:kw };
				select = $('select#channel_select option:selected').val();
				if( select.length ){
					d['channel_id'] = select;
				}

				$.ajax({
					url:self.mountPoint+'/api/search/',
					data:d,
					dataType:'json',
					type:'POST',
					success:function(json){
						$('#search_result_message').text('search result '+json.length);
						if( json.length	){
							$.each( json, function(i,log){ self.add_result(i,log); } ); 
						}
						self.addCloseButton();
					}
				})
				return false;
			});

			$('input#unread_reset').click(function(){
				$.ajax({
					url:self.mountPoint+'/api/reset/unread',
					dataType:'json',
					type:'POST',
				});
				$('.channel_list li').attr('class','');
				$('.channel_list li span.ch_num').html('');
			});

			$(window).bind('popstate', function(event) {
				switch( event.originalEvent.state ){
					case '/':
						$("div.metro-pivot").data("controller").goToItemByName( 'list' );
						break;
					case '/search/':
						$("div.metro-pivot").data("controller").goToItemByName( 'search');
						break;
					case null:
						break;
					default:
						channel_id = event.originalEvent.state.substring( event.originalEvent.state.lastIndexOf( '/' )+1 );
						channel_name = self.getChannelName(channel_id);
						self.selectChannel(channel_id,channel_name);
						break;
				}
			}, false);

			$(document).swipe({
				swipeLeft: function(event) { $("div.metro-pivot").data("controller").goToPrevious(); },
				swipeRight: function(event) { $("div.metro-pivot").data("controller").goToNext(); }
			});

			$("div.metro-pivot").metroPivot({
				clickedItemHeader:function(i){
					switch( i ){
						case '0': //channel list
							self.myPushState( 'channel list','/' );
							break;
						case '1':
							self.myPushState($('div.headers span.header[index=1]').text(),'/channel/'+self.currentChannel );
							break;
						case '2': //search
							self.myPushState('search','/search/' );
							break;
					}
				},
				controlInitialized:function(){
					default_pivot = '<?php print $pivot; ?>';
					switch( default_pivot ){
						case 'channel':
							self.loadChannel( <?php print $default_channel['id']; ?>,'<?php print $default_channel['name'];  ?>');
						default:
							$("div.metro-pivot").data("controller").goToItemByName( default_pivot);
							break;
						case 'list':
						case 'default':
							break;
					}
				}
			});
		},
		reload: function(){
			var self = this;
			if( self.updating ){ return; }
			self.updating = true;
			$.ajax({
				url:self.mountPoint+'/api/logs/',
				dataType:'json',
				type:'POST',
				data:{
					max_id:self.max_id,
					current: ($("div.metro-pivot").data("controller").isCurrentByName( 'list' )?null:self.currentChannel )
				},
				success:function(json){
					if( json['update'] ){
						$.each( json['logs'], function(channel_id, logs){
							logs = $.map( logs, function( log,i){
								if( $("#"+log.id ).length ){ return null; }
								return log;
							});
							if( !logs.length ){ return; }

							$.each( logs, function( i,log){
								if( self.jsConf.pickup_word && self.jsConf.pickup_word.length && log.nick != self.jsConf.my_name ){
									$.each( self.jsConf.pickup_word,function(j,w){
										if( log.log.indexOf(w) >= 0 ){
											$.jGrowl( log.nick+':'+ log.log +'('+self.getChannelName(channel_id)+')' ,{ header: 'keyword hit',life: 5000 } );
											log.log = log.log.replace( w, '<span class="pickup">'+w+'</span>' );
											$('#ch_'+channel_id).attr('class','hit');
										}
									});
								}
							});
							
							self.chLogs[channel_id] = logs.concat(self.chLogs[channel_id]).slice(0,30);

							if( channel_id == self.currentChannel ){
								$.each( logs.reverse(), function(i,log){ self.add_log(i,log); } );
							}
							
							if( channel_id != self.currentChannel || $("div.metro-pivot").data("controller").isCurrentByName( 'list' ) ){
								if( $('#ch_'+channel_id).attr('class') != 'hit' ){
									$('#ch_'+channel_id).attr('class','new');
								}
								num = $('#ch_'+channel_id+' span.ch_num');
								currentNum = $('small',num).text()-0+logs.length;
								if( currentNum > 0 ){
									num.html( '<small>'+currentNum+'</small>' );
								}
							}
						});
						self.max_id = json['max_id'];
					}
					self.updating = false;
				},
				error:function(){
					self.updating = false;
				}
			});	 
		},
		logFilter : function(log){
			if( log.filtered ){ return log; }
			log.log = log.log.replace( /((?:https?|ftp):\/\/[^\s　]+)/g, '<a href="$1" >$1</a>'  );
			log.filtered = true;
			return log;
		},
		add_log:function( i, log ){
			$('#list tbody').prepend(this.createRow(log));
		},
		more_log : function( i,log ){
			$('#list tbody').append(this.createRow(log));
		},
		add_result : function( i, log ){
			$('#search-list tbody').prepend(this.createRow(log,true));
		},
		createRow : function( log,searchFlag ){
			var result = '<tr id="'+log.id+'">';
			searchFlag = (searchFlag==undefined?false:searchFlag);
			
			if( this.jsConf['on_icon'] ){ nick = this.getIconString(log)+log.nick; }
			else{ nick = log.nick; }

			log = this.logFilter(log);

			if( searchFlag ){
				result += '<td class="channel">'+log.channel_name+'</td>';
				time = log.time.substring(log.time.indexOf('-')+1,log.time.lastIndexOf(' '))+' '+log.time.substring(log.time.indexOf(' ')+1,log.time.lastIndexOf(':'));
			}else{
				time = log.time.substring(log.time.indexOf(' ')+1,log.time.lastIndexOf(':'));
			}

			result += '<td class="name'+(log.nick==this.jsConf['my_name']?' self':'')+'">'+nick+'</td><td class="log '+((log.is_notice == 1)?'notice':'')+'">'+log.log+'</td><td class="time">'+time+'</td></tr>';
			return result;
		},
		getIconString : function ( log ){
			return '<img src="http://img.tweetimag.es/i/'+log.nick+'_n" width="64" height="64" alt="'+log.nick+'" />';
		},
		getChannelName : function( i ){
			return $('li#ch_'+i+' span.ch_name').text();
		},
		myPushState : function( name, url ){
			if( history.pushState ){
				history.pushState( window.location.pathname ,name, this.mountPoint+url );
			}
		},
		selectChannel : function( channel_id, channel_name ){
			this.currentChannel = channel_id;

			$('#list tbody tr').each(function( i,e ){ $(e).remove(); });
			$('div#ch_foot').html('');

			this.loadChannel( channel_id, channel_name);
		
			$("div.metro-pivot").data("controller").goToItemByName('channel');
			//scrollTo(0,0);
		},
		loadChannel : function( channel_id, channel_name ){
			var self = this;

			$('div.headers span.header[name=channel]').html( channel_name );
			$('#ch_'+channel_id).attr('class','');
			$('#ch_'+channel_id+' span.ch_num').html('');
			
			$.each( [].concat( this.chLogs[channel_id]).reverse() , function(i,log){ self.add_log(i,log); } );

			$.ajax({
				url:this.mountPoint+'/api/read/'+channel_id,
				dataType:'json',
				type:'POST',
			});

			if( this.chLogs[channel_id].length >= 30 ){
				this.addMoreButton( );
			}
		},
		addMoreButton : function(){
			var self = this;
			button = $('<input type="button" value="more" />');
			button.click(function(){
				$('div#ch_foot').html( '<div id="spinner"><img src="images/spinner_b.gif" width="32" height="32" border="0" align="center" alt="loading..." /></div>' );
				$.ajax({
					url:self.mountPoint+'/api/logs/'+self.currentChannel,
					data:{
						prev_id: $('#list tbody tr').last().attr('id'),
					},
					dataType:'json',
					type:'POST',
					success:function(json){
						if( json['error'] ){ return; }
						$.each(json['logs'],function(i,log){ self.more_log(i,log); });
						self.addMoreButton( );
					}
				});
			});
			$('div#ch_foot').html(button);
		},
		addCloseButton : function(){
			button = $('<input type="button" value="close" />');
			button.click(function(){
				$('div.headers span.header[name=search]').html( '' );
				if( ! $("div.metro-pivot").data("controller").isCurrentByName( 'list' ) ){
					$("div.metro-pivot").data("controller").goToItemByName('list');
				}
			});
			$('div#search_foot').html(button);
		}
	};

	tiarraMetro = new TiarraMetroClass({
		max_id : '<?php print $max_id; ?>',
		currentChannel : <?php print $default_channel['id']<0?"null":$default_channel['id']; ?>,
		chLogs : <?php print json_encode($logs); ?>,
		updating : false,
		jsConf : <?php print json_encode($jsConf); ?>,
		mountPoint : "<?php print $mount_point; ?>",
	});
});
</script>
</div>
