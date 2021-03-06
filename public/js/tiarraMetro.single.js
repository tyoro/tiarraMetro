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
			this.sending = false;
			this.jsConf = param.jsConf;
			this.mountPoint = param.mountPoint;
			this.variable = {};
			this.currentLog = {};
			this.addedLogCount = 0;
			this.unread_num = 0;
			this.history = { i:-1, log: new Array() };

			// localStorageからの設定読み込み
			disable_swipe = localStorage.getItem('disable_swipe');
			if (disable_swipe != null) {
				this.jsConf['disable_swipe'] = disable_swipe;
			}

			this.popup = $('#log_popup_menu');
			this.autoReload =  setInterval(function(){self.reload();}, this.jsConf["update_time"]*1000);
			this.htmlInitialize( param );

			this.keymappingInitialize( param.jsConf[ 'keymapping' ] );

			Shadowbox.init({skipSetup: true});
			$(document).on("click", "#sb-player", function() { Shadowbox.close(); });
		},
		htmlInitialize: function( param ){
			var self = this;

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

				self.history.log.unshift( message );
				self.history.i = -1;

				$.ajax({
					url:self.mountPoint+'/api/post/',
					data:{
						channel_id:self.currentChannel,
						post:message,
						notice:$('input#notice').attr('checked') == 'checked',
					},
					dataType:'json',
					type:'POST',
					success:function( json ){
						if( json[ 'error' ] ){
							$('input#message').removeAttr('disabled').addClass('error');
							$('form#post_form input[type=submit]').removeAttr('disabled');
							alert( "Socket通信時にエラーが発生しました(投稿に成功している場合もあります)。\r\n" + json[ 'msg' ] );
							return;
						}
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
				if( self.sending ){ return; }
				self.sending = true;
				var form = this;
				var post = $('input[name="post"]',form);
				message = post.val();
				if( message.length == 0 ){ return false; }

                                self.history.log.unshift( message );
                                self.history.i = -1;

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
						self.sending = false;
					},
					error:function(){
						post.removeAttr('disabled').removeClass('error');
						$('input[type=submit]',form).removeAttr('disabled');
						self.sending = false;
					},
				});
				return false;
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
				},
				selectedItemChanged:function( index ){
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
			});

			self.updateStatusNotifier();
		},// /htmlInitialize
		keymappingInitialize: function( keymapping ){
			var self = this;
			if( keymapping ){
				if( keymapping.hasOwnProperty( 'action' ) ){
					$.each( keymapping[ 'action' ] , function(key,val){
						switch(key){
							case 'input_focus':
								$(document).bind('keydown', val, function(e){
									$('input#message').focus();
									e.preventDefault();
								});
								break;
							case 'input_blur':
								$('input#message').bind('keydown', val, function(){
									$('input#message').blur();
								});
								break;
							case 'sample':
								$(document).bind('keydown', val, function(){  });
								break;
						}
					});
				}
				if( keymapping.hasOwnProperty( 'input_histry' ) && keymapping[ 'input_histry'] ){
					$('input#message').bind('keydown', 'up', function(){
						// 入力中にうっかりしたとき対策
						message = document.getElementById('message').value;
						if (self.history.i < 0) {
							if (message != '') {	// maybe -1
								if (message != self.history.log[0]) {
									self.history.log.unshift( message );
									// self.history.i = 0;
								}
							}
						}
						if( self.history.log.length > self.history.i+1){
							self.history.i++;
							$('input#message').val( self.history.log[ self.history.i ] );
						}
					});
					$('input#message').bind('keydown', 'down', function(){
						if( self.history.i > 0 ){
							self.history.i--;
							$('input#message').val( self.history.log[ self.history.i ] );
						} else if (self.history.i < 0) {
							// 入力中にうっかりしたとき対策
						}else{
							self.history.i = -1;
							$('input#message').val( '' );
						}
					});
				}
			}
		},
		onClickPivotHeader: function(header) {
			var self = this;

			if (header.hasClass("current")) {
				switch( header.attr("name") ){
					case "list":
						header.toggleClass('closed')
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
					current: self.isCurrentPivotByName("list") ? "" : self.currentChannel,
					single_mode: true
				},
				success:function(json){
					if( 'debug' in json ){
						console.log(json['debug']);
					}
					if (json['error']) {
						if (json['msg'] == 'no login.') {
							// セッション期限切れ
							location.href = self.mountPoint+'/logout';
							return false;
						}
					}

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

									currentNum = Number($('small',num).text())-0+logs.length;

									if( currentNum > 0 ){
										num.html( '<small>'+currentNum+'</small>' );
									}
								}else{
									$('#ch_'+channel_id).removeClass('hit new');
								}
							}


							/* 選択中のチャンネルの場合、domへの流し込みを行う */
							if( channel_id == self.currentChannel ){
								$.each( logs.reverse(), function(i,log){ self.add_log(i,log, -1); } );
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

      if (self.jsConf['emoji']) {
        log.log = twemoji.parse(log.log);
      }

			log.filtered = true;

			return log;
		},

		add_log:function( i, log, unread_point ){
			var self = this;
			var row = self.createRow(log);

			if( unread_point == i ){
				row.addClass( 'unread_border' );
			}
			$('#list').prepend(row);
		},
		more_log : function( i, log, unread_point ){
			var self = this;
			var row = self.createRow(log);

			if( unread_point == i ){
				row.addClass( 'unread_border' );
			}

			$('#list').append(row);
		},
		add_result : function( i, log ){
			$('#search-list').prepend(this.createRow(log,true));
		},
		afterAdded : function(channel_id){
			if(this.jsConf.on_image === 2 ) {
				$("#list a.boxviewimage").each(function() {
					link = $(this);
					player = link.data("player");
					if (player) {
						Shadowbox.setup(link.get(), {
							gallery: "preview",
							player: player,
						});
					}
				});
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
									case 'action':
										li.on('click',function(event){
											switch( label ){
												case 'close':
													$('div.headers span.header[name=channel]').html( '' );
												case 'list':
													if (!self.isCurrentPivotByName("list")) {
														self.goToPivotByName("list");
														self.onListInvisible();
													}
													break;
												case 'tour':
													$(".status-notifier").click();
													break;
												case 'top':
													$( window ).scrollTop(0);
													self.popup.css('display','none');
													break;
												case 'post':
													self.popup.css('display','none');
													$.ajax({
														url:self.mountPoint+'/api/post/',
														data:{
															channel_id:self.currentChannel,
															post:menu['value'],
															notice:false,
														},
														dataType:'json',
														type:'POST',
													});
													break;
											}
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
			
			if( this.jsConf[ 'auto_tail_delete' ] ){
				nick = nick.replace(/_+$/g, "");
			}

			if( this.jsConf['alias'] && nick in this.jsConf['alias'] ){ nick = this.jsConf['alias'][ nick ]; }
			
			var ret = '<img src="http://usericons.relucks.org/twitter/'+nick+'" alt="'+nick+'">';

			if( this.jsConf['on_twitter_link'] == 1 ){
				ret = '<a class="avatar" href="http://mobile.twitter.com/'+nick+'" target="_blank">'+ret+'</a>';
			}else{
				ret = '<span class="avatar" >' + ret + '</span>';
			}

			return ret;
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

			$('.channel_list li').removeClass("current");
			$('#ch_'+channel_id).addClass("current");

			$("#list").empty();
			$("#ch_foot").empty();
			this.popup.css('display','none');

			this.currentLog = {};

			this.loadChannel(channel_id, channel_name);

			this.goToPivotByName("channel");
		},
		loadChannel : function( channel_id, channel_name ){
			var self = this;
			self.unread_num = $('#ch_'+channel_id+' span.ch_num small').text()-0;

			$('div.headers span.header[name=channel]').html( channel_name );

			channel_name.match( new RegExp( '(' + self.jsConf['log_popup_menu']['separator']+'\\w+)' ) );
			self.currentMenu = self.jsConf['log_popup_menu']['network'][ RegExp.$1 ]?self.jsConf['log_popup_menu']['network'][ RegExp.$1 ]:null;

			self.channel_setting = self.getChannelSettings( channel_id );
			if( ( ! ( 'on_icon' in self.channel_setting ) )?self.jsConf['on_icon']:self.channel_setting['on_icon'] ){ 
				$('#list').addClass( 'on_icon' );
			}else{
				$('#list').removeClass( 'on_icon' );
			}

			var logs = [].concat(self.chLogs[channel_id]).reverse();
			var unread_point = self.unread_num > 0 ? logs.length - self.unread_num: -1;
			$.each( logs , function(i,log){ self.add_log(i,log, unread_point); } );
			self.addedLogCount = logs.length;
			self.afterAdded( channel_id );

			$.ajax({
				url:self.mountPoint+'/api/read/'+channel_id,
				dataType:'json',
				type:'POST',
			});

			self.addMoreButton( );
		},
		addMoreButton : function(){
			var self = this;
			button = $('<input type="button" value="more">');
			button.click(function(){
				$('div#ch_foot').html( '<div id="spinner"><img src="images/spinner_b.gif" width="32" height="32" border="0" align="center" alt="loading..."></div>' );

				$.ajax({
					url:self.mountPoint+'/api/logs/'+self.currentChannel,
					data:{
						prev_id: $('#list div.line').last().attr('id'),
					},
					dataType:'json',
					type:'POST',
					success:function(json){
						if( json['error'] ){ return; }

						logs = json['logs'];
						$.each(logs, function(i, log) { self.more_log(i, log, self.unread_num-self.addedLogCount); });
						self.addedLogCount += logs.length;

						if (logs.length > 0) {
							self.addMoreButton();
						} else {
							$('div#ch_foot').empty();
						}

						self.afterAdded(self.currentChannel);
					}
				});
			});
			$('div#ch_foot').html(button);
		},
		onListInvisible: function(){
			if( $('ul.channel_list li.new').length || $('ul.channel_list li.hit').length ){
				$('div.headers span.header[name="list"]').addClass('closed');
				$('ul.channel_list').addClass('invisible');
			}else{
				$('div.headers span.header[name="list"]').removeClass('closed');
				$('ul.channel_list').removeClass('invisible');
			}
		},
		offListInvisible: function(){
			$('div.headers span.header[name="list"]').removeClass('closed');
			$('ul.channel_list').removeClass('invisible');
		},
		updateStatusNotifier: function() {
			$(".status-notifier")
				.toggleClass('new', !!$('.channel_list li.new').length)
				.toggleClass('hit', !!$('.channel_list li.hit').length)
				;
		},
		viewScroll: function( elm ){
			var et = elm.offset().top;
			var eh = elm.height();
			var st = $(window).scrollTop();
			var wh = $(window).height();
			if ( st+wh < et+eh || st > et ) $("html,body").animate( {scrollTop:et-$('div.headers').height()},100);
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
                deleteChannelSetting: function( channel_id, key ){
                        channels = localStorage.getItem( 'channels' );
                        if( channels == null ){ channels = {}; }
                        else{ channels = JSON.parse( channels ); }
                        if( !channels.hasOwnProperty(channel_id) ) { channels[ channel_id ] = {}; }
			if (channels[channel_id].hasOwnProperty(key)) {
				delete channels[channel_id][key];
			}
                        localStorage.setItem( 'channels', JSON.stringify(channels) );
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
			this.getPivotController().pivotHeader_Click( this.getPivotHeaderByName(name) );
		},
		goToPivotByIndex: function(index) {
			this.getPivotController().pivotHeader_Click( this.getPivotHeaderByIndex(index) );
		},
		goToNextPivot: function(){
			var next = $(".metro-pivot .headers .header:gt(0):not(:empty):first");
			if (next.length) this.goToPivotByName(next.attr("name"));
		},
		goToPreviousPivot: function(){
			var prev = $(".metro-pivot .headers .header:not(:empty):last");
			if (prev.length) this.goToPivotByName(prev.attr("name"));
		}
	};

	window.TiarraMetroClass = TiarraMetroClass;
});
