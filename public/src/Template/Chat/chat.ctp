<script>

//Websocket接続
var conn = new WebSocket('ws:172.19.122.53:443');


jQuery(function ($) {
	conn.onopen = function(e) {
	    console.log("Connection established!");
	    $("#status").append("Connection established!<br/>");
	    countup();
	};

	conn.onclose = function(e) { /* 切断時の処理 */


		if(e.code == "1006") {

		} else {

		   	$("#status").append("DisConnection<br/>");
		   	$("#status").append(e.code);
	   	}
	};

	conn.onmessage = function(e) {
	    console.log(e.data);
//	    var data = e.data.split(",");
//	    var roomId = data[0].split(":");
//	    var chatNumber = data[1].split(":");
	    var msg = JSON.parse(e.data);
	    $.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'getChat'], true); ?>",
	        type: "POST",
	        data: {
		        	roomId : msg["roomId"],
		        	chatNumber : msg["chatNumber"],
		         },
	        success : function(response){
	            //通信成功時の処理
	    		$("#chats").append(response);
	        },
	        error: function(response){
	            //通信失敗時の処理
	            alert('通信失敗');
	        }
	    });
	};

	conn.onerror = function(e) {
		console.log(e);
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
		//入力チェック
		if(msg == "") {
			return false;
		}
	    $.ajax({
	        url: "<?=$this->Url->build(['controller' =>'Chat','action' => 'addChat'], true); ?>",
	        type: "POST",
	        data: { chatText : msg },
	        success : function(response){
	            //通信成功時の処理
	    		conn.send(response);
	    		$("[name=chatText]").val('');
	        },
	        error: function(response){
	            //通信失敗時の処理
	            alert('通信失敗');
	            $("[name=chatText]").val(response);	        }
	    });
	}

	var countup = function(){
	    conn.send("ping");
	    setTimeout(countup, 180000);
	  }

});



</script>
<h2>チャット</h2>
<p id="status"></p>
<div id="chats"></div>

<?=$this->Form->create(null,['type' => 'post']) ?>
<table>
	<tr><td><?=$this->Form->input("chatText", ["type" => "textarea",]) ?></td></tr>
	<tr><td><?=$this->Form->input("send", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>
