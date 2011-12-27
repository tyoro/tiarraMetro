$(function(){

	$.escapeHTML = function(val) {
		return $("<div />").text(val).html();
	};

	var Class = function(){ return function(){this.initialize.apply(this,arguments)}};

	var TiarraMetroClass = new Class();

	TiarraMetroClass.prototype = {
		initialize: function( param ){
			var self = this;
			this.max_id = param.max_id;
			this.currentChannel = param.default_channel.id <0?null:param.default_channel.id;
			this.currentMenu = null;
			this.chLogs = param.chLogs;
			this.updating = param.updating;
			this.jsConf = param.jsConf;
			this.mountPoint = param.mountPoint;
			this.variable = {};
			this.currentLog = {};
			this.channelBuffer = [];

			var bufferBase = {
				id: null,
				name: null,
				unread: 0,
				page: 0,

				// 継ぎ足し未読管理用のプール
				logPool: []
			};

			for (var channel_id in param.chLogs) {
				if (param.chLogs.hasOwnProperty(channel_id)) {
					this.channelBuffer[channel_id] = $.extend({}, bufferBase);
				}
			}

			this.popup = $('#log_popup_menu');
			this.autoReload =  setInterval(function(){self.reload();}, this.jsConf["update_time"]*1000);
			this.htmlInitialize( param );
		},
		htmlInitialize: function( param ){
			var self = this;

			/* チャンネルの選択 */
			$("ul.channel_list").on("click", "li", function() {
				channel_id = this.id.substring(3);
				channel_name = self.getChannelName(channel_id);
				self.selectChannel(channel_id, channel_name);
				self.myPushState(channel_name,'/channel/'+channel_id);
			});

			/* 投稿 */
			$('form#post_form').submit(function(){
				message = $('input#message').val();
				if( message.length == 0 ){
					//空postで 更新取得中フラグを強制リセットさせてみる
					self.updating = false;
					return false;
				}

				$('input#message').attr('disabled','disabled');
				$('form#post_form input[type=submit]').attr('disabled','disabled');
				$.ajax({
					url:self.mountPoint+'/api/post/',
					data:{
						channel_id:self.currentChannel,
						post:message,
						notice:$('input#notice').attr('checked') == 'checked',
					},
					dataType:'json',
					type:'POST',
					success:function(){
						$('input#message').removeAttr('disabled').removeClass('error');
						$('form#post_form input[type=submit]').removeAttr('disabled');
						$('input#message').val('');
						$('input#notice').removeAttr('checked');
					},
					error:function(){
						$('input#message').removeAttr('disabled').addClass('error');
						$('form#post_form input[type=submit]').removeAttr('disabled');
					},
				});
				return false;
			});
			
			/* クイック投稿 */
			$('form#quick_form').submit(function(){
				var form = this;
				var post = $('input[name="post"]',form);
				message = post.val();
				if( message.length == 0 ){ return false; }

				post.attr('disabled','disabled');
				$('input[type=submit]',form).attr('disabled','disabled');
				$.ajax({
					url:self.mountPoint+'/api/post/',
					data:{
						channel_id:self.currentChannel,
						post:message,
						notice:false,
					},
					dataType:'json',
					type:'POST',
					success:function(){
						post.removeAttr('disabled').removeClass('error').val('');
						$('input[type=submit]',form).removeAttr('disabled');
						if( !('auto_close' in self.currentMenu) || self.currentMenu[ 'auto_close' ] ){
							self.popup.css('display','none');
						}
					},
					error:function(){
						post.removeAttr('disabled').removeClass('error');
						$('input[type=submit]',form).removeAttr('disabled');
					},
				});
				return false;
			});

			/* 検索 */
			$('form#search_form').submit(function(){
				kw = $('input#keyword').val();
				if( kw.length == 0 ){ return false; }

				$('#search-list').empty();
				$('div#search_foot').html( '<div id="spinner"><img src="images/spinner_b.gif" width="32" height="32" border="0" align="center" alt="searching..."></div>' );

				$('div.headers span.header[name=search]').text( 'search' );
				if (!self.isCurrentPivotByName("search")) {
					self.goToPivotByName("search");
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

						self.afterAdded(null);
					}
				})
				return false;
			});
			
			/* 設定画面の表示 */
			$('input#setting_button').click(function(){
				$('div.headers span.header[name=setting]').text( 'setting' );
				if (!self.isCurrentPivotByName("setting")) {
					self.goToPivotByName("setting");
				}
			});
			/* 設定画面を閉じる */
			$('input#setting_close').click(function(){
				$('div.headers span.header[name=setting]').html( '' );
				if (!self.isCurrentPivotByName("list")) {
					self.goToPivotByName("list");
					self.onListInvisible();
				}
			});
			/* 設定のチャンネルリストの変更 */
			$('select#channel_setting_select').change( function(){
				channel_id = $('select#channel_setting_select option:selected').val();
				if( channel_id == '' ){ $('#channel_setting_elements').css('display','none'); }
				else{ $('#channel_setting_elements').css('display','block'); }

				setting = self.getChannelSettings( channel_id );
				
				if( setting.hasOwnProperty( 'on_icon' ) ){
					on_icon = setting['on_icon'];
				}else{
					on_icon = self.jsConf['on_icon'];
				}
				$('form#setting_form select[name=on_icon]').val( on_icon?'on':'off' );
				if( $('ul.channel_list li#ch_'+channel_id ).length ){
					view = true;
				}else{
					view = false;
				}
				$('form#setting_form select[name=view]').val( view?'on':'off' );

				$('form#setting_form select[name=new_check]').val( (setting.hasOwnProperty( 'new_check' )?setting['new_check']:true)?'on':'off'  );
				$('form#setting_form select[name=pickup_check]').val( (setting.hasOwnProperty( 'pickup_check' )?setting['new_check']:true)?'on':'off'  );
			});
			/* チャンネル設定の適用 */
			$('form#setting_form').submit( function(){
				var submit = $('input[type=submit]', this );
				submit.attr('disabled','disabled');

				channel_id = $('select#channel_setting_select option:selected').val();
				on_icon = $('form#setting_form select[name=on_icon] option:selected').val();
				if( on_icon == 'default' ){
					self.deleteChannelSetting( channel_id, 'on_icon' );
				}else{
					self.setChannelSetting( channel_id, 'on_icon', on_icon == 'on' );
				}
				self.setChannelSetting( channel_id, 'new_check', $('form#setting_form select[name=new_check] option:selected').val()=='on' );
				self.setChannelSetting( channel_id, 'pickup_check', $('form#setting_form select[name=pickup_check] option:selected').val()=='on' );

				$.ajax({
					url:self.mountPoint+'/api/setting/view/'+channel_id,
					dataType:'json',
					type:'POST',
					data:{
						value: $('form#setting_form select[name=view] option:selected').val()
					},
					success: function( data ){
						submit.removeAttr('disabled');
					}
				});

				return false;
			});
			/* localStrageのリセット*/
			$('input#setting_reset').click(function(){
				localStorage.clear();
			});

			/* 未読のリセット */
			$('input#unread_reset').click(function(){
				$.ajax({
					url:self.mountPoint+'/api/reset/unread',
					dataType:'json',
					type:'POST',
				});

				self.offListInvisible();

				$('.channel_list li').attr('class','');
				$('.channel_list li span.ch_num').html('');

				for (var channel_id in self.channelBuffer) {
					if (self.channelBuffer.hasOwnProperty(channel_id) && 'unread' in self.channelBuffer[channel_id]) {
						$(self.channelBuffer[channel_id].logPool).removeClass('unread_border');

						self.channelBuffer[channel_id].logPool = [];
						self.channelBuffer[channel_id].unread = 0;
						self.channelBuffer[channel_id].page = 0;
					}
				}
			});
			
			/* ログアウト */
			$('input#logout').click(function(){
				location.href = self.mountPoint+'/logout';
			});

			/* ブラウザの戻る、進むのフック */
			$(window).bind('popstate', function(event) {
				switch( event.originalEvent.state ){
					case '/':
						self.goToPivotByName("list");
						break;
					case '/search/':
						self.goToPivotByName("search");
						break;
					case '/setting/':
						self.goToPivotByName("setting");
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

			/* フリックによるヘッダー遷移 */
			$(document).touchwipe({
				preventDefaultEvents: false,
				min_move_x: 75,
				wipeLeft: function() { self.goToNextPivot(); },
				wipeRight: function() { self.goToPreviousPivot(); }
			});

			/* pivot化 */
			$(".metro-pivot").metroPivot({
				controlInitialized: function() {
					var metroPivot = $(this);
					var headers = metroPivot.find(".headers .header");

					/* ホビロン */
					metroPivot.find(".pivot-item").each(function(i, item) {
						self.getPivotHeaderByIndex(i).attr("name", $(item).attr("name"));
					});

					/* headers に背景色をもたせる */
					metroPivot.children(".headers").addClass("theme-bg");

					switch ( param.default_pivot ) {
						case 'channel':
							self.loadChannel( param.default_channel.id , param.default_channel.name);
						default:
							self.goToPivotByName(param.default_pivot);
							break;
						case 'list':
						case 'default':
							break;
						case 'search':
							//TODO: 検索の再現？
						case 'setting':
							$('div.headers span.header[name='+param.default_pivot +']').text( param.default_pivot );
							self.goToPivotByName(param.default_pivot);
							break;
					}

					// FIXME: 本来のクリック処理を外して別のイベントを挟んでから戻す */
					var newOnClick = $.proxy(self.onClickPivotHeader, self);
					var oldOnClick = $.proxy(self.getPivotController().pivotHeader_Click, self.getPivotController());
					headers
						.off("click")
						.on("click", function() { newOnClick($(this)); })
						.on("click", function() { oldOnClick($(this)); })
						;
				}
			});

			$(".status-notifier").on("click", function(event) {
				var channels = $(".channel_list li");
				var target = channels.siblings(".hit:first");
				if (!target.length) {
					target = channels.siblings(".new:first");
				}
				target.click();
				self.updateStatusNotifier();
			});
			
			self.updateStatusNotifier();
		},
		onClickPivotHeader: function(header) {
			var self = this;
			var index = header.attr("index");

			if (header.hasClass("current")) {
				switch( header.attr("name") ){
					case "list":
						$("ul.channel_list").toggleClass("invisible");
						break;
					case 'channel':
						on_icon = $('#list').hasClass( 'on_icon' );
						if( on_icon ){ 
							$('#list').removeClass( 'on_icon' );
						}else{
							$('#list').addClass( 'on_icon' );
						}
						self.setChannelSetting( self.currentChannel, 'on_icon', !on_icon );
						break;
				}
			}
			else {
				self.popup.css('display','none');
				self.updateStatusNotifier();

				switch (index) {
				case '0': //channel list
					self.myPushState( 'channel list','/' );
					self.onListInvisible();
					break;
				case '1':
					self.myPushState($('div.headers span.header[index=1]').text(),'/channel/'+self.currentChannel );
					break;
				case '2': //search
					self.myPushState('search','/search/' );
					break;
				case '3': //setting
					self.myPushState('setting','/setting/' );
					break;
				}
			}
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
					current: self.isCurrentPivotByName("list") ? "" : self.currentChannel
				},
				success:function(json){
					var currentNum = [];

					if( json['update'] ){
						$.each( json['logs'], function(channel_id, logs){
							
							//新しいチャンネルの場合
							if(! $('#ch_'+channel_id).length ){
								$('ul.channel_list').prepend('<li id="ch_'+channel_id+'" ><span class="ch_name">new channel</span>&nbsp;'+'<span class="ch_num"></span></li>');

								self.chLogs[ channel_id ] = new Array();

								$.ajax({
									url:self.mountPoint+'/api/channel/name/'+channel_id,
									dataType:'json',
									type:'POST',
									success:function(chData){
										$('#ch_'+chData.id+' span.ch_name').text( chData.name );
									},
								});

								//todo: settingのチャンネル一覧に追加
							}

							/* 設定のロード */
							setting = self.getChannelSettings( channel_id );

							/* 重複チェック */
							logs = $.map( logs, function( log,i){
								if( self.currentLog.hasOwnProperty( log.id ) ){ return null; }
								self.currentLog[ log.id ] = log;
								return log;
							});
							if( !logs.length ){ return; }

							/* pickup word の検出とフラグの追加 */
							if( ( !('pickup_check' in setting) || setting['pickup_check'] ) && self.jsConf.pickup_word && self.jsConf.pickup_word.length ){
								$.each( logs, function( i,log){
									if( log.is_notice != 1 && log.nick != self.jsConf.my_name ){
										$.each( self.jsConf.pickup_word,function(j,w){
											if( log.log.indexOf(w) >= 0 ){
												$.jGrowl( log.nick+':'+ log.log +'('+self.getChannelName(channel_id)+')' ,{ header: 'keyword hit',life: 5000 } );
												$('#ch_'+channel_id).addClass('hit');
												logs[i].pickup = true;
											}
										});
									}
								});
							}
							
							/* 内部的に保持するログを各チャンネル30に制限 */
							self.chLogs[channel_id] = logs.concat(self.chLogs[channel_id]).slice(0,30);

							if( !('new_check' in setting) || setting['new_check'] ){
								if( channel_id != self.currentChannel || self.isCurrentPivotByName("list") ){
									$('#ch_'+channel_id).addClass('new');

									num = $('#ch_'+channel_id+' span.ch_num');

									self.channelBuffer[channel_id].unread = currentNum[channel_id] = Number($('small',num).text())-0+logs.length;
									self.channelBuffer[channel_id].page = 0;

									if( currentNum[channel_id] > 0 ){
										num.html( '<small>'+currentNum[channel_id]+'</small>' );
									}
								}else{
									$('#ch_'+channel_id).removeClass('hit new');

									self.channelBuffer[channel_id].unread = currentNum[channel_id] = logs.length;
									self.channelBuffer[channel_id].page = 0;
								}
							}


							/* 選択中のチャンネルの場合、domへの流し込みを行う */
							if( channel_id == self.currentChannel ){
								self.channelBuffer[channel_id].logPool, function (i, e) { $(e).removeClass('unread_border') });
								$.each( logs.reverse(), function(i,log){ self.add_log(i,log, logs.length); } );

								self.afterAdded(channel_id);
							}
						});
						self.max_id = json['max_id'];
					}
					self.updateStatusNotifier();
					self.updating = false;
				},
				error:function(){
					self.updating = false;
				}
			});	 
		},

		/* log build */
		logFilter : function(log){
			var self = this;
			if( log.filtered ){ return log; }

			//log.log = $.escapeHTML( log.log );

			/* pickupタグの適用 */
			if( log.pickup ){
				$.each( self.jsConf.pickup_word,function(j,w){
					log.log = log.log.replace( w, '<strong class="highlight">'+w+'</strong>' );
				});
			}

			log.filtered = true;

			return log;
		},

		add_log:function( i, log, l ){
			var self = this;
			var row = self.createRow(log);
			var path = window.location.pathname.substring(1).split('/');

			var channel_id = null;

			if (self.currentChannel && self.currentChannel in self.channelBuffer) {
				channel_id = self.currentChannel;
			} else if (path && path[0] === 'channel' && channel_id in self.channelBuffer) {
				channel_id = Number(path[1]);
			}

			if (channel_id) {
				var channel = self.channelBuffer[channel_id];

				if (channel.unread > 0 && i + 1 === channel.unread) {
					row.addClass('unread_border');
					channel.logPool.push(row.get(0));
				}
			}

			$('#list').prepend(row);
		},
		more_log : function( i, log, l ){
			var self = this;
			var row = self.createRow(log);

			var path = window.location.pathname.substring(1).split('/');

			var channel_id = null;

			if (self.currentChannel && self.currentChannel in self.channelBuffer) {
				channel_id = self.currentChannel;
			} else if (path && path[0] === 'channel' && channel_id in self.channelBuffer) {
				channel_id = Number(path[1]);
			}

			if (channel_id) {
				var channel = self.channelBuffer[channel_id];

				$.each(channel.logPool, function (i, e) { $(e).removeClass('unread_border') })

				if (channel.unread > 0 && l > i && channel && l - i === channel.unread) {
					row.addClass('unread_border');
					channel.logPool.push(row.get(0));
				}
			}

			$('#list').append(row);
		},
		add_result : function( i, log ){
			$('#search-list').prepend(this.createRow(log,true));
		},
		afterAdded : function(channel_id){
			if(this.jsConf.on_image === 2 ) {
				$('#list .boxviewimage').lightBox();
			}

			if (channel_id) {
				this.channelBuffer[channel_id].logPool = [];
			}
		},
		createRow : function( log,searchFlag ){
			var self = this;

			log = self.logFilter(log);

			self.variable.alternate = !self.variable.alternate;
			var result =  '<div id="'+log.id+'" type="'+(log.is_notice == 1?'notice':'privmsg')+'" class="line text" nick="'+log.nick+'" alternate="'+(self.variable.alternate?'odd':'even')+'" highlight="'+(log.pickup?'true':'false')+'" >';
			searchFlag = (searchFlag==undefined?false:searchFlag);
			/* 検索の場合はチャンネルも記述する */
			if( searchFlag ){
				result += '<span class="channel">'+log.channel_name+'</span>';
				time = log.time.substring(log.time.indexOf('-')+1,log.time.lastIndexOf(' '))+' '+log.time.substring(log.time.indexOf(' ')+1,log.time.lastIndexOf(':'));
			}else{
				time = log.time.substring(log.time.indexOf(' ')+1,log.time.lastIndexOf(':'));
			}

			//time
			result += '<span class="time">'+time+'</span>';

			//icon
			result += self.getIconString(log);

			//sender
			result += '<span class="sender" type="'+(log.nick==self.jsConf['my_name']?'myself':'normal')+'">'+log.nick+'</span>';

			//log
			result += '<span class="message" type="'+(log.is_notice == 1?'notice':'privmsg')+'">'+log.log+'</span>';
			//TODO: ここのtypeいんのか？

			//end
			result += '</div>';
			
			result = $(result);

			/* log popup menuの処理 */
			if( !searchFlag && self.currentMenu != null ){
				logElement = result;//$('span.message',result);
				if( !( 'match' in self.currentMenu) ||  logElement.text().match(new RegExp((self.currentMenu['match']) ) ) ){
					if( 'match' in self.currentMenu){
						var matchStr = RegExp.$1;
					}
					logElement.on( "click", function(event){
						event.stopPropagation();
						if( self.popup.css('display') == 'block' ){
							self.popup.css('display','none');
							return;
						}
						var ul = $('ul',self.popup);
						if( ul.children().length ){
							ul.empty();
						}
						$('form#quick_form input[name="post"]').val('' );
						if( 'menu' in self.currentMenu ){
							$.each( self.currentMenu['menu'], function(label,menu){
								var li = $('<li />').text(menu['label']?menu['label']:label);
								switch( menu['type'] ){
									case 'typablemap':
										li.on('click',function(event){
											self.popup.css('display','none');
											$.ajax({
												url:self.mountPoint+'/api/post/',
												data:{
													channel_id:self.currentChannel,
													post:label+' '+matchStr,
													notice:false,
												},
												dataType:'json',
												type:'POST',
											});
										});
										break;
									case 'typablemap_comment':
										li.on('click',function(event){
											ul.empty();
											$('form#quick_form input[name="post"]').val(label+' '+matchStr+' ' ).focus();
										});
										break;
								}
								ul.append( li );
							});
						}	
						self.popup.css('top', event.pageY).append(ul).css('display','block');
					} );
					//リンククリック時にメニューが出るのを阻止する。
					logElement.on( "click", 'a', function( event ){
						event.stopPropagation();
					});
				}
			}
			return result;
		},
		getIconString : function ( log ){
			nick = log.nick;
			if( this.jsConf['alias'] && nick in this.jsConf['alias'] ){ nick = this.jsConf['alias'][ nick ]; }
			
			return '<a class="avatar" href="http://mobile.twitter.com/'+nick+'" target="_blank"><img src="http://img.tweetimag.es/i/'+nick+'_n" alt="'+nick+'"></a>';
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

			$("#list").empty();
			$("#ch_foot").empty();

			this.currentLog = {};

			var unread_num = $('#ch_'+channel_id+' span.ch_num small');
			this.channelBuffer[channel_id].unread = unread_num.length > 0 ? Number(unread_num.text()) : 0 ;

			this.loadChannel(channel_id, channel_name);

			if( !this.isCurrentPivotByName("channel")){
				this.goToPivotByName("channel");
			}
		},
		loadChannel : function( channel_id, channel_name ){
			var self = this;

			$('div.headers span.header[name=channel]').html( channel_name );
			$('#ch_'+channel_id).attr('class','');
			$('#ch_'+channel_id+' span.ch_num').html('');

			channel_name.match( new RegExp( '(' + self.jsConf['log_popup_menu']['separator']+'\\w+)' ) );
			self.currentMenu = self.jsConf['log_popup_menu']['network'][ RegExp.$1 ]?self.jsConf['log_popup_menu']['network'][ RegExp.$1 ]:null;

			self.channel_setting = self.getChannelSettings( channel_id );
			if( ( ! ( 'on_icon' in self.channel_setting ) )?self.jsConf['on_icon']:self.channel_setting['on_icon'] ){ 
				$('#list').addClass( 'on_icon' );
			}else{
				$('#list').removeClass( 'on_icon' );
			}

			self.channelBuffer[channel_id].logPool, function (i, e) { $(e).removeClass('unread_border') });

			var logs = [].concat(self.chLogs[channel_id]).reverse();
			$.each( logs , function(i,log){ self.add_log(i,log, logs.length); } );

			self.afterAdded(channel_id);

			$.ajax({
				url:self.mountPoint+'/api/read/'+channel_id,
				dataType:'json',
				type:'POST',
			});

			if( self.chLogs[channel_id].length >= 30 ){
				self.addMoreButton( );
			}
		},
		addMoreButton : function(){
			var self = this;
			button = $('<input type="button" value="more">');
			button.click(function(){
				$('div#ch_foot').html( '<div id="spinner"><img src="images/spinner_b.gif" width="32" height="32" border="0" align="center" alt="loading..."></div>' );
				self.channelBuffer[self.currentChannel].page++;

				$.ajax({
					url:self.mountPoint+'/api/logs/'+self.currentChannel,
					data:{
						prev_id: $('#list div.line').last().attr('id'),
					},
					dataType:'json',
					type:'POST',
					success:function(json){
						if( json['error'] ){ return; }
						$.each(json['logs'],function(i,log){ self.more_log(i,log, json['logs'].length); });
						self.addMoreButton( );

						self.afterAdded(self.currentChannel);
					}
				});
			});
			$('div#ch_foot').html(button);
		},
		addCloseButton : function(){
			var self = this;
			button = $('<input type="button" value="close">');
			button.click(function(){
				$('div.headers span.header[name=search]').html( '' );
				if (!self.isCurrentPivotByName("list")) {
					self.goToPivotByName("list");
					self.onListInvisible();
				}
			});
			$('div#search_foot').html(button);
		},
		onListInvisible: function(){
			if( $('ul.channel_list li.new').length || $('ul.channel_list li.hit').length ){
				$('ul.channel_list').addClass('invisible');
			}else{
				$('ul.channel_list').removeClass('invisible');
			}
		},
		offListInvisible: function(){
			$('ul.channel_list').removeClass('invisible');
		},
		/* local strage */
		getChannelSettings: function( channel_id ){
			channels = localStorage.getItem( 'channels' );
			if( channels == null ){ channels = {}; }
			else{ channels = JSON.parse( channels ); }
			if( !channels.hasOwnProperty(channel_id) ) { channels[ channel_id ] = {}; }

			localStorage.setItem( 'channels', JSON.stringify(channels) );
			return channels[ channel_id ];
		},
		getChannelSetting: function( channel_id, key ){
			channel = this.getChannelSettings( channel_id );
			if( channel == null ){ return null; }
			if( channel.hasOwnProperty(key) ) { return channel[ key ]; }
			return null;
		},
		setChannelSetting: function( channel_id, key, value ){
			channels = localStorage.getItem( 'channels' );
			if( channels == null ){ channels = {}; }
			else{ channels = JSON.parse( channels ); }
			if( !channels.hasOwnProperty(channel_id) ) { channels[ channel_id ] = {}; }
			channels[ channel_id ][ key ] = value;
			localStorage.setItem( 'channels', JSON.stringify(channels) );
		},
		updateStatusNotifier: function() {
			$(".status-notifier")
				.toggleClass('new', !!$('.channel_list li.new').length)
				.toggleClass('hit', !!$('.channel_list li.hit').length)
				;
		},

		/* Pivot helpers */
		getPivotController: function() {
			return $(".metro-pivot").data("controller");
		},
		getPivotHeaders: function() {
			return this.getPivotController().headers;
		},
		getPivotHeaderByName: function(name) {
			return this.getPivotHeaders().children(".header[name="+name+"]");
		},
		getPivotHeaderByIndex: function(index) {
			return this.getPivotHeaders().children(".header[index="+index+"]");
		},
		isCurrentPivotByName: function(name) {
			return this.getPivotHeaderByName(name).hasClass("current");
		},
		isCurrentPivotByIndex: function(index) {
			return this.getPivotHeaderByIndex(index).hasClass("current");
		},
		goToPivotByName: function(name) {
			this.getPivotHeaderByName(name).click();
		},
		goToPivotByIndex: function(index) {
			this.getPivotHeaderByIndex(index).click();
		},
		goToNextPivot: function(){
			var next = $(".metro-pivot .headers .header:gt(0):not(:empty):first");
			if (next) this.goToPivotByName(next.attr("name"));
		},
		goToPreviousPivot: function(){
			var prev = $(".metro-pivot .headers .header:not(:empty):last");
			if (prev) this.goToPivotByName(prev.attr("name"));
		}
	};

	window.TiarraMetroClass = TiarraMetroClass;
});
