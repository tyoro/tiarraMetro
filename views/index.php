<div class='pivot-item'>
	<h3>channel</h3>
	<ul class="channel_list">
	<?php foreach( $channels as $ch ){ ?>
	<li id="ch_<?php print $ch['id']; ?>" class="<?php if($ch['cnt']>0){ print "new"; } ?>"><span class="ch_name"><?php print $ch['name']; ?></span>(<span class="ch_num"><?php print $ch['cnt']; ?></span>)</li>
	<?php } ?>
	</ul>
</div>
<div class='pivot-item'>
	<h3 id="ch_name"></h3>
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
</div>
<div class='pivot-item'>
	<h3>search</h3>
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
	<hr/>
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
	var currentChannel = null;
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
						if( channel_id == currentChannel ){
							$.each( logs, add_log );
						}
						
						$('#ch_'+channel_id).attr('class','new');
						num = $('#ch_'+channel_id+' span.ch_num');
						num.text( num.text()-0+logs.length );
						
						$.each( pickup_word,function(j,w){
							$.map( logs, function( log,i){
								if( log.log.indexOf(w) > 0 ){
									log.log = log.log.replace( w, '<span class="pickup">'+w+'</span>' );
									$('#ch_'+channel_id).attr('class','hit');
									//$('span.ch_name',this).html( '<blink>' + $('span.ch_name',this).text() +'</blink>' );
								}
								return log;
							});
						});
					
						chLogs[channel_id] = logs.concat(chLogs[channel_id]).slice(0,30);
					});
				}
				chkTime = json['checktime'];
			}
		});	 
	};
	var autoReload =  setInterval( 	reload_func, 10*1000);

	var add_log = function( i, log ){
		$('#list tbody').prepend('<tr><td class="name '+log.nick+'">'+log.nick+'</td><td class="log '+log.nick+'">'+log.log+'</td><td class="time">'+log.time.substring(5)+'</td></tr>');
	}
	var add_result = function( i, log ){
		$('#search-list tbody').prepend('<tr><td class="channel">'+log.channel_name+'</td><td class="name '+log.nick+'">'+log.nick+'</td><td class="log '+log.nick+'">'+log.log+'</td><td class="time">'+log.time.substring(5)+'</td></tr>');
	}

	var select_channel = function( ){
		var ch_id = this.id.substring(3);
		currentChannel = ch_id;
		
		$('div.headers span.header[index=1]').html( $('span.ch_name',this).text() );
		$('#ch_'+ch_id).attr('class','');
		$('#ch_'+ch_id+' span.ch_num').text(0);
		$('#list tbody tr').each(function( i,e ){ $(e).remove(); });
		$.each( [].concat( chLogs[ch_id]).reverse() , add_log );
		$("div.metro-pivot").data("controller").goToNext();
		//scrollTo(0,0);

		$.ajax({
			url:'/api/read/'+ch_id,
			dataType:'json',
			type:'POST',
		});
	}

	$('ul.channel_list li').click(select_channel);

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
});
</script>
