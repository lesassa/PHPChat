<script>

//Websocket接続
var conn = new WebSocket('ws:' + document.domain + ':443');


jQuery(function ($) {


	conn.onopen = function(e) {
	    console.log("Connection established!");
	    $("#status").append("Connection established!<br/>");
	    ping();
	};

	conn.onclose = function(e) { /* 切断時の処理 */
	};

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
		        	return;
		        },
		        error: function(response){
		            //通信失敗時の処理
		            alert('通信失敗');
		            return;
		        }
		    });
	    }

	    var msg = JSON.parse(e.data);

	  	//ログイン情報を受信した場合
		if (msg.resourceId) {
			var login = [
				msg["memberName"],
				"<br/>",
		    ].join("")
			$("#login").append(login);
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
	    $("#chats" + msg["roomId"]).append(chat);
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

	$(document).ready(function(){
		$("[id^=room]").hide();
		$("#room<?=$roomId ?>").show();
	});

	var room = "<?=$roomId ?>";
	$("[class^=room]").click(function(event) {
		var roomId  = $(this).attr("class");
		$("[id^=room]").hide();
		$("#" + roomId).show();
		room = roomId.slice(4);
	});
});



</script>
<h2>チャット</h2>
<p id="status"></p>
<?php foreach($rooms as $room): ?>
	<?= $this->element('room', ['roomId'=> $room->roomId, 'chats'=> $room->chats]) ?>
<?php endforeach; ?>
<?=$this->Form->create(null,['type' => 'post']) ?>
<table>
	<tr><td><?=$this->Form->input("chatText", ["type" => "textarea",]) ?></td></tr>
	<tr><td><?=$this->Form->input("send", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>
<?=$this->Html->link("ルーム一覧へ", ['controller'=>'Chat', 'action'=>'index']); ?>
