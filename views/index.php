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
	<div class="util">
		<h4>utilities</h4>
		<input type="button" id="unread_reset" value="reset unread count" />
		<input type='button' id='search_open' value='search' />
		<input type="button" id="setting_button" value="setting" />
		<input type="button" id="logout" value="logout" />
	</div>
</div>
<div class='pivot-item' name="channel">
	<h3></h3>
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
<div class='pivot-item' name="search">
	<h3></h3>
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

	<span id="search_result_message">input search options, then press 'search'.</span>
	<div id="search-list" class="list">
	</div>
	<div id="search_foot"><h4>misc.</h4><input type="button" id='search_close' name='close' value="close" /></div>
</div>
<div class='pivot-item' name="setting">
	<h3></h3>
	<!-- span id="setting_message">setting</span -->
	<h4>current configurations</h4>
	<div class='setting_view'>
		<dl>
			<dt id='setting_view_my_name_title'>ユーザー名</dt>
			<dd id='setting_view_my_name'></dd>
			<dt>ログイン方法</dt>
			<dd id='setting_view_cookie'></dd>
			<dt>使用中のテンプレート</dt>
			<dd id='setting_view_template'></dd>
			<dt>抽出キーワード</dt>
			<dd id='setting_view_pickup_word'></dd>
			<dt>アイコンの表示</dt>
			<dd id='setting_view_on_icon'></dd>
			<dt>アイコンのTwitterリンク</dt>
			<dd id='setting_view_on_twitter_link'></dd>
			<dt>画像の表示方法</dt>
			<dd id='setting_view_on_image'></dd>
			<dt>表示URLリンク先の短縮</dt>
			<dd id='setting_view_shorten_url'></dd>
			<dt>短縮URLを展開して表示する</dt>
			<dd id='setting_view_expand_url'></dd>
			<dt>スワイプでタブを切り替える</dt>
			<dd id='setting_view_disable_swipe'></dd>
			<dt>アイコン表示の切り替え</dt>
			<dd id='setting_view_disable_icon_hideout'></dd>
			<dt>ID末尾の '_' を自動削除</dt>
			<dd id='setting_view_auto_tail_delete'></dd>
			<dt>入力ヒストリ機能</dt>
			<dd id='setting_view_keymapping_input_histry'></dd>
			<dt>クイック投稿を自動で閉じる</dt>
			<dd id='setting_view_quickpost_auto_close'></dd>
		</dl>
	</div>
        <h4>client settings</h4>
        <div class="setting_entry" >
                <form id="client_setting_form" >
                        <ul>
                                <li><input type='checkbox' id='enable_swipe' name='enable_swipe' value='on' /><label for='enable_swipe'>スワイプでタブを切り替える</label></li>
				<li><input type='checkbox' id='enable_icon_hideout' name='enable_icon_hideout' value='on' /><label for='enable_icon_hideout'>ヘッダをタップしてアイコン表示の切り替え</label></li>
                        </ul>
                        <input type='submit' name='submit' value='save' />
                </form>
        </div>
	<h4>channel settings</h4>
	<div class="setting_entry" >
		<form id="setting_form" >
			<select name="channel" id="channel_setting_select" >
				<option value="" >----</option>
				<?php foreach( $all_channels as $ch ){ ?>
					<option value="<?php print $ch['id']; ?>"><?php print $ch['name']; ?></option>
				<?php } ?>
			</select>
			<input type='button' id='setting_next' name='next' value='next' style='content: &raquo;' onclick="var obj = document.getElementById('channel_setting_select'); var i = obj.selectedIndex ; obj.selectedIndex = (i>=obj.length-1 ? 0:i+1); $('select#channel_setting_select').trigger('change'); return false;" />
			<div id="channel_setting_elements" style="display:none;" >
				<ul>
					<li>
						アイコンの表示
                                        	<input type='radio' id='show_icon_def' name='on_icon' value='default' /><label for='show_icon_def'>規定値</label>
                                   		<input type='radio' id='show_icon_on' name='on_icon' value='on' /><label for='show_icon_on'>オン</label>
                                        	<input type='radio' id='show_icon_off' name='on_icon' value='off' /><label for='show_icon_off'>オフ</label>
					</li>
					<li><input type='checkbox' id='show_to_list' name='view' value='on' /><label for='show_to_list'>チャンネル一覧へ表示する</label></li>
					<li><input type='checkbox' id='check_new' name='new_check' value='on' /><label for='check_new'>新着をチェックする</label></li>
					<li><input type='checkbox' id='check_pickup' name='pickup_check' value='on' /><label for='check_pickup'>キーワードヒットさせる</label></li>
					<li><input type='checkbox' id='to_rounds' name='to_rounds' value='on' /><label for='to_rounds'>巡回機能の対象に入れる</label></li>
				</ul>
				<input type="submit" name='submit' value='save' />
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

