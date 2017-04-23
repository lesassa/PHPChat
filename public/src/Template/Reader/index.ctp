<script>
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
			return;
		}
		conn.send(msg);
		$("[name=chatText]").val("");
	}

	//イベント
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