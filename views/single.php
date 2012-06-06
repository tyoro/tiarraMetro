<div class="metro-pivot">
<div class='pivot-item' name="channel">
	<h3></h3>
	<div>
		<form method="POST" id="post_form" class="theme-bg">
			<input type="text" name="post" id="message" />
			<input type="submit" id='post_submit' name='post' value="post" />
			<input type="checkbox" name="notice" id="notice" value="true" /><label for='notice'>notice</label>
		</form>
		<div class="status-notifier">
			<hr />
		</div>
		<div id="list" class="list">
		</div>
		<div id="ch_foot"></div>
	</div>
	<div name="side-channel-list">
	</div>
</div>
<div id="log_popup_menu" style="display:none;">
	<form method="post" id="quick_form" >
		<input type="text" name="post" id="quick_message">
		<input type="submit" value="post">
	</form>
	<ul id='click_menu'>
	</ul>
</div>
<script>
$(function(){
	tiarraMetro = new TiarraMetroClass({
		max_id : '<?php print $max_id; ?>',
		chLogs : <?php print json_encode($logs); ?>,
		updating : false,
		jsConf : <?php print json_encode($jsConf); ?>,
		mountPoint : "<?php print $mount_point; ?>",
		default_pivot : '<?php print $pivot; ?>',
		default_channel :  <?php print json_encode($default_channel); ?>,
	});
});
</script>

