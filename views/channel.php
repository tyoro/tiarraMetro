<div class='pivot-item'>
	<h3> <?php print $name; ?></h3>
	<form method="POST">
		<input type="text" name="post" /><input type="submit" value="post" />
	</form>
	<label><input type="checkbox" name="auto_reload" checked="checked" id="auto_reload" />auto reload</label>
	<hr/>
	<table id="list">
		<thead>
		<tr>
			<th>nick</th><th>log</th><th>time</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach( $logs as $l ){ ?>
		<tr>
			<td class="name <?php print $l['name']; ?>"><?php print $l['name']; ?></td>
			<td class="log <?php print $l['name']; ?>"><?php print $l['log']; ?></td>
			<td class="time"><?php print str_replace(' ',"<br/>\n",substr($l['time'],5)); ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<div class='pivot-item'>
	<h3>channels</h3>
	<ul class="channel_list">
	<?php foreach( $channels as $ch ){ ?>
	<li><a href="/channel/<?php print $ch['id']; ?>" class="<?php if($ch['cnt']>0){ print "new"; } ?>"><?php print $ch['name']."(".$ch['cnt'].")"; ?></a></li>
	<?php } ?>
	</ul>
</div>
<div class='pivot-item'>
	<h3>search</h3>
	<form method="POST" action="/search">
	<input type="text" name="word" />
	<input type="hidden" name="channel_id" value="<?php print $id ?>" />
	<input type="submit" name="search" />
	</form>
</div>
<script>
$(function(){
	var chkTime = '<?php print $checktime; ?>';

	var reload_func = function(){
	//setTimeout( function(){
		$.ajax({
			url:'/api/log/<?php print $id; ?>',
			dataType:'json',
			data:{checktime:chkTime},
			success:function(json){
				if( json['update'] ){
					$.each( json['log'], function(){
						$('#list tbody tr:first').before('<tr><td class="name '+this.name+'">'+this.name+'</td><td class="log '+this.name+'">'+this.log+'</td><td class="time">'+this.time.substring(5)+'</td></tr>');
					});
				}
				chkTime = json['checktime'];
			}
		});	 
	};
	var autoReload =  setInterval( 	reload_func, 10*1000);

	$('#auto_reload').change(function(e){
		if( e.target.checked == 'checked' ){
			clearInterval(autoReload);
		}else{
			autoReload =  setInterval(  reload_func, 10*1000);
		}
	});
});
</script>
