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
	<div class="status-notifier">
		<hr />
	</div>
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
	<h4>channel settings</h4>
	<div class="setting_entry" >
		<form id="setting_form" >
			<select name="channel" id="channel_setting_select" >
				<option value="" >----</option>
				<?php foreach( $all_channels as $ch ){ ?>
					<option value="<?php print $ch['id']; ?>"><?php print $ch['name']; ?></option>
				<?php } ?>
			</select>
			<div id="channel_setting_elements" style="display:none;" >
<!--
			<dl>
				<dt>アイコンの表示</dt>
				<dd>
					<select name="on_icon">
						<option value="default">既定値</option>
						<option value="on">オン</option>
						<option value="off">オフ</option>
					</select>
				</dd>
				<dt>チャンネル一覧への表示</dt>
				<dd>
					<select name="view">
						<option value="on">オン</option>
						<option value="off">オフ</option>
					</select>
				</dd>
				<dt>新着のチェック</dt>
				<dd>
					<select name="new_check">
						<option value="on">オン</option>
						<option value="off">オフ</option>
					</select>
				</dd>
				<dt>ピックアップのチェック</dt>
				<dd>
					<select name="pickup_check">
						<option value="on">オン</option>
						<option value="off">オフ</option>
					</select>
				</dd>
			</dl>
-->
			<dl>
				<dt>アイコンの表示</dt>
				<dd>
					<input type='radio' id='show_icon_def' name='on_icon' value='default' /><label for='show_icon_def'>規定値</label>
					<input type='radio' id='show_icon_on' name='on_icon' value='on' /><label for='show_icon_on'>オン</label>
					<input type='radio' id='show_icon_off' name='on_icon' value='off' /><label for='show_icon_off'>オフ</label>
				</dd>
				<dt>チャンネル一覧への表示</dt>
				<dd>
					<input type='radio' id='show_to_list_on' name='view' value='on' /><label for='show_to_list_on'>オン</label>
					<input type='radio' id='show_to_list_off' name='view' value='off' /><label for='show_to_list_off'>オフ</label>
				</dd>
				<dt>新着のチェック</dt>
				<dd>
					<input type='radio' id='check_new_on' name='new_check' value='on' /><label for='check_new_on'>オン</label>
					<input type='radio' id='check_new_off' name='new_check' value='off' /><label for='check_new_off'>オフ</label>
				</dd>
				<dt>ピックアップのチェック</dt>
				<dd>
					<input type='radio' id='check_pickup_on' name='pickup_check' value='on' /><label for='check_pickup_on'>オン</label>
					<input type='radio' id='check_pickup_off' name='pickup_check' value='off' /><label for='check_pickup_off'>オフ</label>
				</dd>
			</dl>
			<input type="submit" value='submit' />
			</div>
		</form>
	</div>
	<h4>setting menu</h4>
	<div id="setting_foot">
		<input type="button" value="setting reset" id="setting_reset" />
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

