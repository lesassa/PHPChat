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
<h1>チャット</h1>
<div id="chat"></div>

<?=$this->Form->create(null,['type' => 'post']) ?>
<?=$this->Form->input("chatText", ["type" => "text",]) ?>
<?=$this->Form->input("send", ["type" => "button",]) ?>
<?=$this->Form->end() ?>
