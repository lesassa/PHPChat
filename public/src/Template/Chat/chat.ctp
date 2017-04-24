<script>

//Websocket接続
var conn = new WebSocket('ws:localhost:8080');

jQuery(function ($) {
	conn.onopen = function(e) {
	    console.log("Connection established!");
	    $("#chat").append("Connection established!<br/>");
	};

	conn.onmessage = function(e) {
	    console.log(e.data);
	    $("#chat").append(e.data + "<br/>");
	};

	function send() {
		var msg = $("[name=chatText]").val();
		//入力チェック
		if(msg == "") {
			return false;
		}
		conn.send(msg);
		$("[name=chatText]").val('');
	}

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
});

</script>
<h2>チャット</h2>
<p id="chat"></p>

<?=$this->Form->create(null,['type' => 'post']) ?>
<table>
	<tr><td><?=$this->Form->input("chatText", ["type" => "textarea",]) ?></td></tr>
	<tr><td><?=$this->Form->input("send", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>