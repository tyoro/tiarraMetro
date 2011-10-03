<div class="metro-pivot">
<div class='pivot-item'>
	<h3 name="list">channel</h3>
	<ul class="channel_list">
	<?php foreach( $channels as $ch ){ ?>
	<li id="ch_<?php print $ch['id']; ?>" class="<?php if($ch['cnt']>0){ print "new"; } ?>"><span class="ch_name"><?php print $ch['name']; ?></span>(<span class="ch_num"><?php print $ch['cnt']; ?></span>)</li>
	<?php } ?>
	</ul>
	<form method="POST" id="search_form">
	<input type="text" name="word"  id="keyword" />
	<select name="channel" id="channel_select">
		<option value="" >----</option>
		<?php foreach( $channels as $ch ){ ?>
			<option value="<?php print $ch['id']; ?>"><?php print $ch['name']; ?></option>
		<?php } ?>
	</select>
	<input type="submit" name="search" />
	</form>
</div>
<div class='pivot-item'>
	<h3 id="ch_name" name="channel" ></h3>
	<form method="POST" id="post_form">
		<input type="text" name="post" id="message" /><input type="submit" value="post" />
	</form>
	<hr/>
	<table id="list" class="list">
		<thead>
			<tr>
				<th>nick</th><th>log</th><th>time</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
	<div id="ch_foot"></div>
</div>
<div class='pivot-item'>
	<h3 name="search"></h3>
	<span id="search_result_message">search result</span>
	<table id="search-list" class="list">
		<thead>
		<tr>
			<th>channel</th><th>nick</th><th>log</th><th>time</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<script>
$(function(){
	var chkTime = '<?php print $checktime; ?>';
	var currentChannel = <?php print $default_channel['id']; ?>;
	if( currentChannel < 0 ){ currentChannel = null; }
	var chLogs = <?php print json_encode($logs); ?>;
	var pickup_word = <?php print json_encode($pickup); ?>;

	var reload_func = function(){
		$.ajax({
			url:'/api/logs/',
			dataType:'json',
			type:'POST',
			data:{checktime:chkTime,current:currentChannel},
			success:function(json){
				if( json['update'] ){
					$.each( json['logs'], function(channel_id, logs){
						
						$.each( pickup_word,function(j,w){
							$.map( logs, function( log,i){
								if( log.log.indexOf(w) >= 0 ){
									$.jGrowl( log.nick+':'+ log.log +'('+getChannelName(channel_id)+')' ,{ header: 'keyword hit',life: 5000 } );
									log.log = log.log.replace( w, '<span class="pickup">'+w+'</span>' );
									$('#ch_'+channel_id).attr('class','hit');
								}
								return log;
							});
						});
					
						chLogs[channel_id] = logs.concat(chLogs[channel_id]).slice(0,30);

						if( channel_id == currentChannel ){
							$.each( logs.reverse(), add_log );
						}else{
							if( $('#ch_'+channel_id).attr('class') != 'hit' ){
								$('#ch_'+channel_id).attr('class','new');
							}
							num = $('#ch_'+channel_id+' span.ch_num');
							num.text( num.text()-0+logs.length );
						}
						
					});
				}
				chkTime = json['checktime'];
			}
		});	 
	};
	var autoReload =  setInterval( 	reload_func, 5*1000);

	var add_log = function( i, log ){
		$('#list tbody').prepend('<tr><td class="name '+log.nick+'">'+log.nick+'</td><td class="log '+((log.is_notice == 1)?'notice':'')+'">'+log.log+'</td><td class="time">'+log.time.substring(5)+'</td></tr>');
	}
	var more_log = function( i,log ){
		$('#list tbody').append('<tr><td class="name '+log.nick+'">'+log.nick+'</td><td class="log '+((log.is_notice == 1)?'notice':'')+'">'+log.log+'</td><td class="time">'+log.time.substring(5)+'</td></tr>');
	}
	var add_result = function( i, log ){
		$('#search-list tbody').prepend('<tr><td class="channel">'+log.channel_name+'</td><td class="name '+log.nick+'">'+log.nick+'</td><td class="log '+(log.is_notice==1?'notice':'')+'">'+log.log+'</td><td class="time">'+log.time.substring(5)+'</td></tr>');
	}
	var getChannelName = function( i ){
		return $('li#ch_'+i+' span.ch_name').text();
	}
	var myPushState = function( name, url ){
		if( history.pushState ){
			history.pushState( window.location.pathname ,name, url );
		}
	}

	var selectChannel = function( channel_id, channel_name ){
		currentChannel = channel_id;

		$('#list tbody tr').each(function( i,e ){ $(e).remove(); });
		$('div#ch_foot').html('');

		loadChannel( channel_id, channel_name);
		
		$("div.metro-pivot").data("controller").goToItemByName('channel');
		//scrollTo(0,0);

	}

	var loadChannel = function( channel_id, channel_name ){
		$('div.headers span.header[name=channel]').html( channel_name );
		$('#ch_'+channel_id).attr('class','');
		$('#ch_'+channel_id+' span.ch_num').text(0);

		$.each( [].concat( chLogs[channel_id]).reverse() , add_log );

		$.ajax({
			url:'/api/read/'+channel_id,
			dataType:'json',
			type:'POST',
		});

		if( chLogs[channel_id].length >= 30 ){
			addMoreButton( );
		}
		
	}

	addMoreButton = function(){
			button = $('<input type="button" value="more" />');
			button.click(function(){
				$('div#ch_foot').html( 'more loading...' );
				$.ajax({
					url:'/api/logs/'+currentChannel,
					data:{
						start: $('#list tbody tr').length ,
					},
					dataType:'json',
					type:'POST',
					success:function(json){
						if( json['error'] ){ return; }
						$.each(json['logs'],more_log);
						addMoreButton( );
					}
				});
			});
			$('div#ch_foot').html(button);
	}

	$('ul.channel_list li').click(function(){
		channel_id = this.id.substring(3);
		channel_name = getChannelName(channel_id);

		selectChannel( channel_id, channel_name );

		myPushState(channel_name,'/channel/'+channel_id);
	});

	$('form#post_form').submit(function(){
		message = $('input#message').val();
		if( message.length == 0 ){ return false; }
		$.ajax({
			url:'/api/post/',
			data:{
				channel_id:currentChannel,
				post:message,
			},
			dataType:'json',
			type:'POST',
		});
		$('input#message').val('');
		return false;
	});

	$('form#search_form').submit(function(){
		kw = $('input#keyword').val();
		if( kw.length == 0 ){ return false; }

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
			url:'/api/search/',
			data:d,
			dataType:'json',
			type:'POST',
			success:function(json){
				$('#search-list tbody tr').each(function( i,e ){ $(e).remove(); });
				$('#search_result_message').text('search result '+json.length);
				if( json.length	){
					$.each( json, add_result ); 
				}
			}
		})
		return false;
	});

	$(window).bind('popstate', function(event) {
	console.log(event.originalEvent.state);
		switch( event.originalEvent.state ){
			case '/':
				$("div.metro-pivot").data("controller").goToItemByName( 'list' );
				break;
			case '/search/':
				$("div.metro-pivot").data("controller").goToItemByName( 'search');
				break;
			default:
				channel_id = event.originalEvent.state.substring( event.originalEvent.state.lastIndexOf( '/' )+1 );
				channel_name = getChannelName(channel_id);
				selectChannel(channel_id,channel_name);
				break;
		}
	}, false);

	$("div.metro-pivot").metroPivot({
		clickedItemHeader:function(i){

		//console.log('click:'+i);
		//console.log(window.location.pathname);
			switch( i ){
				case '0': //channel list
					myPushState( 'channel list','/' );
					break;
				case '1':
					myPushState($('div.headers span.header[index=1]').text(),'/channel/'+currentChannel );
					break;
				case '2': //search
					myPushState('search','/search/' );
					break;
			}
		},
		controlInitialized:function(){
			default_pivot = '<?php print $pivot; ?>';
			switch( default_pivot ){
				case 'channel':
					loadChannel( <?php print $default_channel['id']; ?>,'<?php print $default_channel['name'];  ?>');
				default:
					//$("div.metro-pivot").data("controller").goToItemByName(default_pivot);
					$("div.metro-pivot").data("controller").goToItemByName( default_pivot);
					break;
				case 'list':
				case 'default':
					break;
			}
		}
	});
});
</script>
</div>
