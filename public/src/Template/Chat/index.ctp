<script>

//Websocket接続
var conn = new WebSocket('ws:' + document.domain + ':443');


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

	conn.onopen = function(e) {
	    console.log("Connection established!");
	    $("#status").append("接続済");
	    ping();
	};

	conn.onclose = function(e) { /* 切断時の処理 */
		$("#status").text("");
		$("#status").text("切断。ページをリロードしてください。");

		//エラーがタイムアウトの場合、再接続
		if(e.code == "1006") {
			conn.close();
			location.reload();
		}
	};

	var memberId = <?=$loginTable->memberId ?>;
	conn.onmessage = function(e) {
	    console.log(e.data);

	    //自分のリソースIDを受信した場合
	    if (!isNaN(e.data)) {
		    $.ajax({
		        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'enter'], true); ?>",
		        type: "POST",
		        data: { resourceId : e.data,
	        			roomId : room,
			         },
		        success : function(response){
		            //通信成功時の処理
		            var member = JSON.stringify({"memberId":"<?=$loginTable->memberId ?>", "memberName": "<?=$loginTable->memberName ?>", "resourceId": e.data});
		            conn.send(member);
		            resourceId = e.data;
		        	return;
		        },
		        error: function(response){
		            //通信失敗時の処理
		            alert('通信失敗・ログイン情報発信');
		            return;
		        }
		    });
		    return;
	    }

	    var msg = JSON.parse(e.data);

	  	//ログイン情報を受信した場合
		if (msg.resourceId) {
			var login = [
				"<div id=\"resource" + msg["resourceId"] +"\">",
				msg["memberName"],
				"</div>",
		    ].join("")
			$("#login").append(login);
			return;
		}

	  	//ログアウト情報を受信した場合
		if (msg.logoutId) {
			$("#resource" + msg.logoutId).remove();
			return;
		}

		//ルームの作成時
		if (msg.roomCreate) {
			var roomBotton = [
					"<div class=\"menu\">",
					"<p class=\"room" + String(msg["roomId"]) + "\">",
					String(msg["roomName"]),
					"<span class=\"unread\" id=\"unread" + String(msg["roomId"]) + "\"></span>",
					"</p>",
					"</div>",
			    ].join("");
				$("nav").prepend(roomBotton);
			var roomCreate = [
		        	"<div class=\"room" + String(msg["roomId"]) + "\">",
					"<div id=\"chats" + String(msg["roomId"]) + "\">",
					"</div>",
					"</div>",
			    ].join("");
	        $(".inner").append(roomCreate);
			var roomName = [
	        	"<div class=\"room" + String(msg["roomId"]) + "\"><h2>",
	        	String(msg["roomName"]),
				"</h2></div>",
		    ].join("");
        	$("#main form").before(roomName);

			var roomList = [
				"<table>",
				"<tr><th>ルームネーム</th>",
				"<td>" + String(msg["roomName"]) + "</td></tr>",
	        	"<tr><th>ルーム説明</th>",
				"<td>" + String(msg["roomDescription"]) + "</td>",
				"</tr></table>",
		    ].join("");
			$("#main .room9999").append(roomList);
			$("#main [class^=room]").hide();
			$("#main .room" + room).show();
			return;
		}

	    //通常メッセージを受信した場合
	    var chat = [
	        "<hr>",
	        "<p>",
	        String(msg["chatNumber"]) + ":",
	        String(msg["memberName"]),
	        " ＜ " + msg["chatText"],
	        "</p>",
	    ].join("").replace(/\n/g, "<br />");
	    $("#chats" + msg["roomId"]).prepend(chat);

	    //他の人からはデスクトップに通知する
	    if (memberId != msg["memberId"]) {
		  	show(msg["roomName"], msg["chatText"]);
		  	if (parseInt(msg["roomId"]) != room) {
			  	upUnread(msg["roomId"]);
		  	}
	    }
	};

	//イベント
	$("[name=chatText]").on("keydown", function(e) {
		//エンターを押下
		if(e.keyCode === 13) {
			//シフト＋エンターは送信しない
			if (e.shiftKey) {
				return true;
			}
			send();
			return false;
		}
	});
	$("#send").click(send);

	function send() {
		var msg = $("[name=chatText]").val();
		$("[name=chatText]").val('');
		//入力チェック
		if(msg == "") {
			return false;
		}
	    $.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'addChat'], true); ?>",
	        type: "POST",
	        data: { chatText : msg,
	        		roomId : room,
		         },
	        success : function(response){
	            //通信成功時の処理
	    		conn.send(response);
	        },
	        error: function(response){
	            //通信失敗時の処理
	            alert('通信失敗');
	            $("[name=chatText]").val(response);	        }
	    });
	}

	//疎通確認
	var ping = function(){
	    conn.send("ping");
	    setTimeout(ping, 180000);
	  }


	//現ログイン情報取得
	$(document).ready(function(){
	    $.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'getParticipants'], true); ?>",
	        type: "POST",
	        data: { roomId : room,
		         },
	        success : function(response){
	            //通信成功時の処理
	        	var participants = JSON.parse(response);
	        	for (var i = 0; i < participants.length; i++) {
		        	var participant = participants[i];
					var login = [
						"<div id=\"resource" + participant["resourceId"] +"\">",
						participant["memberName"],
						"</div>",
				    ].join("")
					$("#login").append(login);
	        	}
				return;

	        },
	        error: function(response){
	            //通信失敗時の処理
	            alert('通信失敗・現ログイン情報取得');
	            return;
	        }
	    });

	});

	$(document).ready(function(){
		$("#main [class^=room]").hide();
		$("#main .room" + room).show();
		if (room == 9999) {
			$("#main #sendChat").hide();
		} else {
			$("#main #sendChat").show();
		}
	});


	var room = "9999";
	$('nav').on('click', '[class^=room]', function() {
	//$("[class^=room]").click(function(event) {
		var roomId  = $(this).attr("class");
		$("#main [class^=room]").hide();
		$("#main ." + roomId).show();
		room = roomId.slice(4);
		resetUnread(room)
		if (room == 9999) {
			$("#main #sendChat").hide();
		} else {
			$("#main #sendChat").show();
		}
	});

	$("#create").click(function(){
		var roomName = $("[name=roomName]").val();
		var roomDescription = $("[name=roomDescription]").val();
		//入力チェック
		if(roomName == "" || roomDescription == "" ) {
			return false;
		}

		$.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'createRoom'], true); ?>",
	        type: "POST",
	        data: { roomName : roomName,
	        		roomDescription : roomDescription,
		         },
	        success : function(response){
	            //通信成功時の処理

				//他の参加者にお知らせ
				conn.send(JSON.stringify({"roomId":response, "roomName": roomName, "roomCreate": "1", "roomDescription": roomDescription}));

	        	$("[name=roomDescription]").val('');
	        	$("[name=roomName]").val('');
	        },
	        error: function(response){
	            //通信失敗時の処理
	            alert('通信失敗');
	            $("[name=chatText]").val(response);
	        }
		});
	});

	//未読カウンター
	function upUnread(roomId) {
		var counter = $("#unread" + roomId).text().slice(3);
		if (counter == "") {
			counter = 0;
		}
		counter = parseInt(counter) + 1;
		$("#unread" + roomId).text("new" + String(counter));
	}

	//未読カウンターリセット
	function resetUnread(roomId) {
		$("#unread" + roomId).text("");
	}


});



</script>
<?php foreach($rooms as $room): ?>
	<div class="room<?=$room->roomId?>"><h2><?=$room->roomName ?></h2></div>
<?php endforeach; ?>
<?=$this->Form->create(null,['type' => 'post']) ?>
<table id="sendChat">
	<tr><td><?=$this->Form->input("chatText", ["type" => "textarea",]) ?></td></tr>
	<tr><td><?=$this->Form->input("send", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>
<?= $this->element('rooms', ['rooms'=> $rooms]) ?>
<?php foreach($rooms as $room): ?>
	<?= $this->element('room', ['room'=> $room, 'chats'=> $room->chats]) ?>
<?php endforeach; ?>
