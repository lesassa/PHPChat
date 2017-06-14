<?= $this->Html->script('autobahn.min.js') ?>
<script>
jQuery(function ($) {

	//デスクトップ通知サポート確認
	$ (document ).ready(function() {
		if ( !notify.isSupported )
		{
			$( '#supportbutton' ).css( 'display', 'none' );
		}
	});
	$( '#supportbutton' ).click(function() {
			notify.requestPermission();
	});

	//デスクトップ通知
	function show(title, msg)
	{
		notify.createNotification( title, { body: msg, icon: '<?=$this->Url->image('cake.icon.png');?>' } )
	}

	//Websocket接続
	var conn = new ab.Session('ws:' + document.domain + ':8088',
		//接続時の処理
        function(e) {
        	console.log("Connected!");
    	    $("#status").append("接続済");
    	    ping();

	        conn.call("login", [memberId]).then(function (result) {
	           // do stuff with the result
	           console.log(result);
	        }, function(error) {
	           // handle the error
	           console.log(error);
	        });

    	    //受信処理登録
		    conn.subscribe("9999", function (topic, event) {
		    	received(topic, event);
		    });

		    //予約購読ルームを読込
			$.ajax({
		        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'getSubscribes'], true); ?>",
		        type: "POST",
		        data: {
			         },
		        success : function(response){
		            //通信成功時の処理

		            //購読
		            var msg = JSON.parse(response);
		            for (var i = 0; i < msg.length; i++) {
		            	subscribe(String(msg[i]));
		            }
		        },
		        error: function(response){
		            //通信失敗時の処理
		            alert('通信失敗・予約購読');
		        }
			});

			//現ログイン情報取得
		    $.ajax({
		        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'getParticipants'], true); ?>",
		        type: "POST",
		        data: { roomId : room,
			         },
		        success : function(response){
		            //通信成功時の処理
		        	var participants = JSON.parse(response);
		        	for(var i in participants){
			        	var participant = participants[i];
						var login = [
							"<div class=\"room" + String(participant["roomId"]) +"\" memberId=\"" + String(participant["memberId"]) +"\">",
							participant["memberName"],
							"</div>",
					    ].join("")
						$("#login").append(login);
						$("#login [class^=room]").hide();
						$("#login .room9999").show();
		        	}
					return;

		        },
		        error: function(response){
		            //通信失敗時の処理
		            alert('通信失敗・現ログイン情報取得');
		            return;
		        }
		    });
        },
        //切断時の処理
        function(reason) {
            console.warn('WebSocket connection closed');
    		$("#status").text("");
    		$("#status").text("切断。ページをリロードしてください。");

    		//エラーがタイムアウトの場合、ページのリロード
    		if(reason == ab.CONNECTION_LOST) {
    			conn.close();
    			window.location.href = "http://" + document.domain + ":8765"; // 通常の遷移
    		}
        },
        {'skipSubprotocolCheck': true}
    );

	//疎通確認
	var ping = function(){
	    conn.call("ping").then(function (result) {
	           // do stuff with the result
	           console.log(result);
	        }, function(error) {
	           // handle the error
	           console.log(error);
	        });
	    setTimeout(ping, 120000);
	}

	//ルーム切り替え
	var room = "9999";
	$('nav').on('click', '[class^=room]', function() {

		//フォームリセット
		$("[name=chatText]").val('');
		$("[name=replyId]").val('');

		//切り替え
		var roomButton  = $(this).attr("class");
		$("#main [class^=room]").hide();
		$("#main ." + roomButton).show();
		$("#login [class^=room]").hide();
		$("#login ." + roomButton).show();
		room = roomButton.slice(4);
		resetUnread(room)

		if (room == 9999) {
			$("#main #sendChat").hide();
		} else {
			$("#main #sendChat").show();
		}

// 		conn.call("login").then(function (result) {
// 	           // do stuff with the result
// 	           console.log(result);
// 	        }, function(error) {
// 	           // handle the error
// 	           console.log(error);
// 	     });
	});

	//初期表示ルーム切り替え
	$(document).ready(function(){
		$("#main [class^=room]").hide();
		$("#main .room" + room).show();
		$("#login [class^=room]").hide();
		$("#login .room" + room).show();
		if (room == 9999) {
			$("#main #sendChat").hide();
		} else {
			$("#main #sendChat").show();
		}
	});

	//未読カウンターリセット
	function resetUnread(roomId) {
		var counter = $("#unread" + roomId).text().slice(3);
		if (counter != ""){
			titlenotifier.sub(counter);
		}
		$("#unread" + roomId).text("");
	}

	//未読カウンター
	function upUnread(roomId) {
		var counter = $("#unread" + roomId).text().slice(3);
		if (counter == "") {
			counter = 0;
		}
		counter = parseInt(counter) + 1;
		$("#unread" + roomId).text("new" + String(counter));
	}

	//ウィンドウアクティブ時の未読カウンター処理
	var activeUnread = 0;
	$(window).focusin(function(e) {
		titlenotifier.sub(activeUnread);
		activeUnread = 0;
	});

	//返信ボタン押下
	$('#main').on('click', '[name^=reply]', function() {
		var replyId = $(this).attr('value');
		$('[name=replyId]').val(replyId);
	});

	//引用ボタン押下
	$('#main').on('click', '[name^=quotation]', function() {
		var chatNumber = $(this).val();
		$("html,body").animate({scrollTop:$('[class=room' + room + '] [class=chatNumber' + chatNumber + ']').offset().top}, { duration: 500});
	});


	$('.room9999').on('click', '.subscribe > button', function() {
		var roomId = $(this).val();
		var roomName = $(".subscribe > [name=roomName" + roomId + "]").val();

		//購読済みの場合は終了
		if($('.menu .room' + roomId).length){
			return;
	    }

	    //購読処理
		subscribe(roomId);

		//入室状態保存
		$.ajax({
        	url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'saveSubscribe'], true); ?>",
	        type: "POST",
	        data: {
    			roomId : parseInt(roomId),
		         },
		   	//通信成功時の処理
	        success : function(response){

	        	//既出チャットの設定
	            var chats = JSON.parse(response);
	            for(var i in chats){
	            	var msg = chats[i];
		            if (msg["replyId"] == null || msg["replyId"] == 0) {
				    var chat = [
				        "<hr>",
				        "<p class=\"chatNumber" + String(msg["chatNumber"]) + "\">",
				        String(msg["chatNumber"]) + ":",
				        String(msg["memberName"]),
				        " ＜ " + msg["chatText"],
				        "<br/>",
				        "<svg class=\"goodButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 54 72\">",
				        "<path d=\"M38.723,12c-7.187,0-11.16,7.306-11.723,8.131C26.437,19.306,22.504,12,15.277,12C8.791,12,3.533,18.163,3.533,24.647 C3.533,39.964,21.891,55.907,27,56c5.109-0.093,23.467-16.036,23.467-31.353C50.467,18.163,45.209,12,38.723,12z\"/>",
				        "</svg>",
				    	"<svg class=\"replyButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 65 72\" name=\"reply" + String(msg["chatNumber"]) + "\" value=\"" + String(msg["chatNumber"]) + "\">",
				  		"<path d=\"M41 31h-9V19c0-1.14-.647-2.183-1.668-2.688-1.022-.507-2.243-.39-3.15.302l-21 16C5.438 33.18 5 34.064 5 35s.437 1.82 1.182 2.387l21 16c.533.405 1.174.613 1.82.613.453 0 .908-.103 1.33-.312C31.354 53.183 32 52.14 32 51V39h9c5.514 0 10 4.486 10 10 0 2.21 1.79 4 4 4s4-1.79 4-4c0-9.925-8.075-18-18-18z\"/>",
						"</svg>",
				        "</p>",
				    ].join("").replace(/\n/g, "<br />");
			    	} else {
				    var chat = [
				        "<hr>",
				        "<p class=\"chatNumber" + String(msg["chatNumber"]) + "\">",
				        "<button type=\"button\" name=\"quotation" + String(msg["replyId"]) + "\" value=\"" + String(msg["replyId"]) + "\" id=\"14\">>> " + String(msg["replyId"]) + "</button><br/>",
				        String(msg["chatNumber"]) + ":",
				        String(msg["memberName"]),
				        " ＜ " + msg["chatText"],
				        "<br/>",
				        "<svg class=\"goodButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 54 72\">",
				        "<path d=\"M38.723,12c-7.187,0-11.16,7.306-11.723,8.131C26.437,19.306,22.504,12,15.277,12C8.791,12,3.533,18.163,3.533,24.647 C3.533,39.964,21.891,55.907,27,56c5.109-0.093,23.467-16.036,23.467-31.353C50.467,18.163,45.209,12,38.723,12z\"/>",
				        "</svg>",
				    	"<svg class=\"replyButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 65 72\" name=\"reply" + String(msg["chatNumber"]) + "\" value=\"" + String(msg["chatNumber"]) + "\">",
				  		"<path d=\"M41 31h-9V19c0-1.14-.647-2.183-1.668-2.688-1.022-.507-2.243-.39-3.15.302l-21 16C5.438 33.18 5 34.064 5 35s.437 1.82 1.182 2.387l21 16c.533.405 1.174.613 1.82.613.453 0 .908-.103 1.33-.312C31.354 53.183 32 52.14 32 51V39h9c5.514 0 10 4.486 10 10 0 2.21 1.79 4 4 4s4-1.79 4-4c0-9.925-8.075-18-18-18z\"/>",
						"</svg>",
				        "</p>",
				    ].join("").replace(/\n/g, "<br />");
				    }
				    $("#chats" + msg["roomId"]).prepend(chat);
	            }

	            //ルーム切替
				$("[name=chatText]").val('');
				$("[name=replyId]").val('');

				//切り替え
				var roomButton  = "room" + roomId;
				$("#main [class^=room]").hide();
				$("#main ." + roomButton).show();
				$("#login [class^=room]").hide();
				$("#login ." + roomButton).show();
				$("#main #sendChat").show();
				room = roomId;
	        },
	      	//通信失敗時の処理
	        error: function(response){

	            alert('[入室状態保存]通信失敗');
	        }
	    });

	});

	function received(topic, event) {
	        console.log("-- Received --");
	        console.log("Topic: " + topic);
	        console.log("event: " + event);
		    var msg = JSON.parse(event);

			//ルームの作成時
			if (msg.roomCreate) {
				var roomName = [
		        	"<div class=\"room" + String(msg["roomId"]) + "\"><h2>",
		        	String(msg["roomName"]),
					"</h2></div>",
			    ].join("");
		    	$("#main form").before(roomName);

				var roomCreate = [
		        	"<div class=\"room" + String(msg["roomId"]) + "\">",
					"<div id=\"chats" + String(msg["roomId"]) + "\">",
					"</div>",
					"</div>",
			    ].join("");
	        	$(".inner").append(roomCreate);

				var roomList = [
					"<table>",
					"<tr><th>ルームネーム</th>",
					"<td>" + String(msg["roomName"]) + "</td></tr>",
		        	"<tr><th>ルーム説明</th>",
					"<td>" + String(msg["roomDescription"]) + "</td></tr>",
					"<tr><td class=\"subscribe\"><button type=\"button\" value=\"" + String(msg["roomId"]) + "\" id=\"ru-shi\">入室</button>",
					"<input type=\"hidden\" name=\"roomName" + String(msg["roomId"]) + "\" id=\"roomName" + String(msg["roomId"]) + "\" value=\"" + String(msg["roomName"]) + "\"/></td>",
					"<td class=\"unsubscribe\"><button type=\"button\" value=\"" + String(msg["roomId"]) + "\" id=\"tui-shi\">退室</button></td>",
					"</tr></table>"
			    ].join("");
				$("#main .room9999").append(roomList);
				$("#main [class^=room]").hide();
				$("#main .room" + room).show();
				return;
			}

		  	//ログイン情報を受信した場合
			if (msg.loginId) {
				for(var i in msg){
					if (i == "loginId") {
						return;
					}
					var participant = msg[i];
					var login = [
						"<div class=\"room" + String(participant["roomId"]) +"\" memberId=\"" + String(participant["memberId"]) +"\">",
						String(participant["memberName"]),
						"</div>",
				    ].join("");
				$("#login").append(login);
				$("#login [class^=room]").hide();
				$("#login .room" + room).show();
				}
				return;
			}

		  	//ログアウト情報を受信した場合
			if (msg.logoutId) {
				$("#login [memberId=" + msg.memberId + "]").remove();
				return;
			}

		  	//退室を受信した場合
			if (msg.unsubscribeId) {
				$("#login .room" + msg.roomId + "[memberId=" + msg.memberId + "]").remove();
				return;
			}


		    //通常メッセージを受信した場合
		    if (msg["replyId"] == null || msg["replyId"] == 0) {
			    var chat = [
			        "<hr>",
			        "<p class=\"chatNumber" + String(msg["chatNumber"]) + "\">",
			        String(msg["chatNumber"]) + ":",
			        String(msg["memberName"]),
			        " ＜ " + msg["chatText"],
			        "<br/>",
			        "<svg class=\"goodButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 54 72\">",
			        "<path d=\"M38.723,12c-7.187,0-11.16,7.306-11.723,8.131C26.437,19.306,22.504,12,15.277,12C8.791,12,3.533,18.163,3.533,24.647 C3.533,39.964,21.891,55.907,27,56c5.109-0.093,23.467-16.036,23.467-31.353C50.467,18.163,45.209,12,38.723,12z\"/>",
			        "</svg>",
			    	"<svg class=\"replyButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 65 72\" name=\"reply" + String(msg["chatNumber"]) + "\" value=\"" + String(msg["chatNumber"]) + "\">",
			  		"<path d=\"M41 31h-9V19c0-1.14-.647-2.183-1.668-2.688-1.022-.507-2.243-.39-3.15.302l-21 16C5.438 33.18 5 34.064 5 35s.437 1.82 1.182 2.387l21 16c.533.405 1.174.613 1.82.613.453 0 .908-.103 1.33-.312C31.354 53.183 32 52.14 32 51V39h9c5.514 0 10 4.486 10 10 0 2.21 1.79 4 4 4s4-1.79 4-4c0-9.925-8.075-18-18-18z\"/>",
					"</svg>",
// 			        "<button type=\"button\" value=\"" + String(msg["chatNumber"]) + "\" name=\"reply" + String(msg["chatNumber"]) + "\" id=\"fan-xin\">返信</button>",
			        "</p>",
			    ].join("").replace(/\n/g, "<br />");
		    } else {
			    var chat = [
			        "<hr>",
			        "<p class=\"chatNumber" + String(msg["chatNumber"]) + "\">",
			        "<button type=\"button\" name=\"quotation" + String(msg["replyId"]) + "\" value=\"" + String(msg["replyId"]) + "\" id=\"14\">>> " + String(msg["replyId"]) + "</button><br/>",
			        String(msg["chatNumber"]) + ":",
			        String(msg["memberName"]),
			        " ＜ " + msg["chatText"],
			        "<br/>",
			        "<svg class=\"goodButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 54 72\">",
			        "<path d=\"M38.723,12c-7.187,0-11.16,7.306-11.723,8.131C26.437,19.306,22.504,12,15.277,12C8.791,12,3.533,18.163,3.533,24.647 C3.533,39.964,21.891,55.907,27,56c5.109-0.093,23.467-16.036,23.467-31.353C50.467,18.163,45.209,12,38.723,12z\"/>",
			        "</svg>",
			    	"<svg class=\"replyButton\" xmlns=\"http://www.w3.org/2000/svg\"  viewBox=\"0 0 65 72\" name=\"reply" + String(msg["chatNumber"]) + "\" value=\"" + String(msg["chatNumber"]) + "\">",
			  		"<path d=\"M41 31h-9V19c0-1.14-.647-2.183-1.668-2.688-1.022-.507-2.243-.39-3.15.302l-21 16C5.438 33.18 5 34.064 5 35s.437 1.82 1.182 2.387l21 16c.533.405 1.174.613 1.82.613.453 0 .908-.103 1.33-.312C31.354 53.183 32 52.14 32 51V39h9c5.514 0 10 4.486 10 10 0 2.21 1.79 4 4 4s4-1.79 4-4c0-9.925-8.075-18-18-18z\"/>",
					"</svg>",
// 			        "<button type=\"button\" value=\"" + String(msg["chatNumber"]) + "\" name=\"reply" + String(msg["chatNumber"]) + "\" id=\"fan-xin\">返信</button>",
			        "</p>",
			    ].join("").replace(/\n/g, "<br />");
		    }
		    $("#chats" + msg["roomId"]).prepend(chat);

		    //他の人からはデスクトップに通知する
		    if (memberId != msg["memberId"]) {
			  	show(msg["roomName"], msg["chatText"]);
			  	if (parseInt(msg["roomId"]) != room) {
				  	upUnread(msg["roomId"]);
				  	titlenotifier.add();
			  	} else {
			  		if (!document.hasFocus()) {
			  			titlenotifier.add();
			  			activeUnread = activeUnread + 1;
			  		}
			  	}
		    }
	}
	//購読処理
	function subscribe(roomId) {

	    console.log("-- Subscribe --");
	    console.log("Topic: " + roomId);

	  	//受信処理登録
	    conn.subscribe(roomId, function (topic, event) {
	    	received(topic, event);
	    });

		//ルームボタン作成
		var roomName = $(".subscribe > [name=roomName" + roomId + "]").val();
		var roomBotton = [
			"<div class=\"menu\">",
			"<p class=\"room" +roomId + "\">",
			roomName,
			"<span class=\"unread\" id=\"unread" + roomId + "\"></span>",
			"</p>",
			"</div>",
	    ].join("");
		$("#room9999").after(roomBotton);

	}

	var memberId = <?=$loginTable->memberId ?>;
// 	conn.onmessage = function(e) {
// 	    console.log(e.data);

// 	    //自分のリソースIDを受信した場合
// 	    if (!isNaN(e.data)) {
// 		    $.ajax({
		        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'enter'], true); ?>",
// 		        type: "POST",
// 		        data: { resourceId : e.data,
// 	        			roomId : room,
// 			         },
// 		        success : function(response){
// 		            //通信成功時の処理
//		            var member = JSON.stringify({"memberId":"<?=$loginTable->memberId ?>", "memberName": "<?=$loginTable->memberName ?>", "resourceId": e.data});
// 		          	conn.publish(room, member);
// 		        	return;
// 		        },
// 		        error: function(response){
// 		            //通信失敗時の処理
// 		            alert('通信失敗・ログイン情報発信');
// 		            return;
// 		        }
// 		    });
// 		    return;
// 	    }
// 	};

	$('.room9999').on('click', '.unsubscribe > button', function() {
		var roomId = $(this).val();
		unsubscribe(roomId);
		$("#chats" + roomId).empty();
		$(".menu .room" + roomId).closest('.menu').remove();
		$("#login .room" + roomId + "[memberId=" + memberId + "]").remove();
	});

	//購読停止処理
    function unsubscribe(roomId) {

        console.log("-- Unsubscribe --");
        console.log("Topic: " + roomId);

        try {
        	conn.unsubscribe(roomId);
            //送信処理
    	    $.ajax({
    	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'unsubscribe'], true); ?>",
    	        type: "POST",
    	        data: {
    	        		roomId : roomId,
    		         },
    		    //通信成功時の処理
    	        success : function(response){
    	        },
    	      	//通信失敗時の処理
    	        error: function(response){
    	            alert('通信失敗');
    	        }
    	    });

        } catch(e) {
            console.warn(e);
        }
    }

	//エンターキーの送信
	$("[name=chatText],[name=replyId]").on("keydown", function(e) {

		//エンターを押下
		if(e.keyCode === 13) {
			//シフト＋エンターは送信しない
			if (e.shiftKey) {
				return true;
			}
			$("#send").trigger('click');

			return false;
		}
	});

	//送信ボタン処理
	$("#send").click(send);

	//送信処理
    function send() {

    	var msg = $("[name=chatText]").val();
		var replyId = $("[name=replyId]").val();
		$("[name=chatText]").val('');
		$("[name=replyId]").val('');


        console.log("-- Publish --");
        console.log("Topic: " + room);
        console.log("Input: " + msg);

        //送信処理
    	$.ajax({
    	    url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'addChat'], true); ?>",
    	    type: "POST",
    	    data: { chatText : msg,
    	    		roomId : room,
    	    		replyId : replyId,
    	         },
    	    //通信成功時の処理
    	    success : function(response){

    	    	//エラーメッセージ初期化
    	    	$("#sendChat .error-message").remove();

				//メッセージなし
				if (response == "") {
					//フォームリセット
					$("[name=chatText]").val('');
					$("[name=replyId]").val('');
					return;
				}

    	        //エラーメッセージ表示
    	        var msg = JSON.parse(response);
    	        if (msg.errors) {
    	        	for(var key in msg["errors"]){
    	        		for(var key2 in msg["errors"][key]){
    	            		var error = [
    	            	        "<div class=\"error-message\">",
    	            	        msg["errors"][key][key2],
    	            	        "</div>",
    	            	    ].join("");
    	        			$("[name=" + key + "]").after(error);
    	        		}
    	        	}
    	        }
    	    },
    	  	//通信失敗時の処理
    	    error: function(response){

    	        alert('[チャット送信]通信失敗');
    	    }
    	});
    }



	//ルーム作成
	$("#create").click(function(){
		var roomName = $("[name=roomName]").val();
		var roomDescription = $("[name=roomDescription]").val();

		$.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'createRoom'], true); ?>",
	        type: "POST",
	        data: { roomName : roomName,
	        		roomDescription : roomDescription,
		         },
		    //通信成功時の処理
	        success : function(response){

	            //エラーメッセージ初期化
    	    	$("#createRoom .error-message").remove();

				//メッセージなし
				if (response == "") {
					//フォームリセット
	        		$("[name=roomName]").val('');
	        		$("[name=roomDescription]").val('');
					return;
				}

	            //エラーメッセージ表示
	            var msg = JSON.parse(response);
	            for(var key in msg["errors"]){
	            	for(var key2 in msg["errors"][key]){
		            	var error = [
		                    "<div class=\"error-message\">",
		                    msg["errors"][key][key2],
		                    "</div>",
		                ].join("");
	            		$("[name=" + key + "]").after(error);
	            	}
	            }
	        },
	      	//通信失敗時の処理
	        error: function(response){

	            alert('通信失敗');
	            $("[name=chatText]").val(response);
	        }
		});
	});

});



</script>
<?php foreach($rooms as $room): ?>
	<div class="room<?=$room->roomId?>"><h2><?=$room->roomName ?></h2></div>
<?php endforeach; ?>
<?=$this->Form->create(null,['type' => 'post']) ?>
<table id="sendChat">
	<tr><td>
		返信：<?=$this->Form->input("replyId", ["type" => "text", "class" => "smallForm"]) ?><br/>
		<?=$this->Form->input("chatText", ["type" => "textarea",]) ?>
	</td></tr>
	<tr><td><?=$this->Form->input("send", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>
<?= $this->element('rooms', ['rooms'=> $rooms]) ?>
<?php foreach($rooms as $room): ?>
	<?= $this->element('room', ['room'=> $room]) ?>
<?php endforeach; ?>
