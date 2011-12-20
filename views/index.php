<div class="metro-pivot">
<div class='pivot-item' name="list">
	<h3><?php print $options->channel_list_label; ?></h3>
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
	<div class="search">
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
	</div>
	<div class="util">
		<h4>utilities</h4>
		<input type="button" id="unread_reset" value="reset unread count" />
		<input type="button" id="setting_button" value="setting" />
		<input type="button" id="logout" value="logout" />
	</div>
</div>
<div class='pivot-item' name="channel">
	<h3></h3>
	<form method="POST" id="post_form" class="theme-bg">
		<input type="text" name="post" id="message" />
		<input type="submit" value="post" />
		<label for='notice'>notice</label><input type="checkbox" name="notice" id="notice" value="true" />
	</form>
	<hr class="status-notifier" />
	<div id="list" class="list">
	</div>
	<div id="ch_foot"></div>
</div>
<div class='pivot-item' name="search">
	<h3></h3>
	<span id="search_result_message">search result</span>
	<div id="search-list" class="list">
	</div>
	<div id="search_foot"></div>
</div>
<div class='pivot-item' name="setting">
	<h3></h3>
	<span id="setting_message">setting</span>
	<h4>channels setting</h4>
	<div class="setting_entry" >
		<form id="setting_form" >
			<select name="channel" id="channel_setting_select" >
				<option value="" >----</option>
				<?php foreach( $all_channels as $ch ){ ?>
					<option value="<?php print $ch['id']; ?>"><?php print $ch['name']; ?></option>
				<?php } ?>
			</select>
			<div id="channel_setting_elements" style="display:none;" >
			アイコンの表示:
			<select name="on_icon">
				<option value="default">既定値</option>
				<option value="on">オン</option>
				<option value="off">オフ</option>
			</select><br/>
			チャンネル一覧への表示:
			<select name="view">
				<option value="on">オン</option>
				<option value="off">オフ</option>
			</select><br/>
			新着のチェック:
			<select name="new_check">
				<option value="on">オン</option>
				<option value="off">オフ</option>
			</select><br/>
			ピックアップのチェック:
			<select name="pickup_check">
				<option value="on">オン</option>
				<option value="off">オフ</option>
			</select><br/>
			<br/>
			<input type="submit" value='submit' />
			</div>
		</form>
	</div>
	<h4>setting menu</h4>
	<input type="button" value="setting reset" id="setting_reset" />
	<div id="setting_foot">
	<input type="button" value="close" id="setting_close" />
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

