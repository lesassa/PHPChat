<?= $this->Html->script('autobahn.min.js') ?>
<!--�z�M (WampServer��onPublish���\�b�h���Ă΂��)-->
<div id="publish">
<select>
    <option value="topic_1">Topic 1</option>
    <option value="topic_2">Topic 2</option>
    <option value="topic_3">Topic 3</option>
    <option value="invalid_topic">Invalid Topic</option>
</select>
<input type="text" />
<button>Publish</button>
</div>

<hr />

<!--�w�� (WampServer��onSubscribe���\�b�h���Ă΂��)-->
<div id="subscribe">
<select>
    <option value="topic_1">Topic 1</option>
    <option value="topic_2">Topic 2</option>
    <option value="topic_3">Topic 3</option>
    <option value="invalid_topic">Invalid Topic</option>
</select>
<button>Subscribe</button>
</div>

<hr />

<!--�w�ǉ��� (WampServer��onUnSubscribe���\�b�h���Ă΂��)-->
<div id="unsubscribe">
<select>
    <option value="topic_1">Topic 1</option>
    <option value="topic_2">Topic 2</option>
    <option value="topic_3">Topic 3</option>
    <option value="invalid_topic">Invalid Topic</option>
</select>
<button>Unsubscribe</button>
</div>

<hr />

<!--RPC (WampServer��onCall���\�b�h���Ă΂��)-->
<div id="rpc">
<select>
    <option value="get_subscribing_topics">Get Subscribing Topics</option>
    <option value="invalid_method">Invalid Method</option>
</select>
<button>Call</button>
</div>
<div id="send">
<button>send</button>
</div>
<hr />

<p>Check your console.</p>

<script>
    var sess = new ab.Session('ws://localhost:8080',
        function() {
        	console.log("Connected!");
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );
    // �z�M (WampServer��onPublish���\�b�h���Ă΂��)
    $("#publish > button").click(function() {
        var input = $("#publish > input");
        var select = $("#publish > select");

        console.log("-- Publish --");
        if(input.val().length == 0) {
            console.log("Input is empty.");
        } else {
            console.log("Topic: " + select.val());
            console.log("Input: " + input.val());

            sess.publish(select.val(), JSON.stringify({input: input.val()}));
            input.val('');
        }
    });

    // �w�� (WampServer��onSubscribe���\�b�h���Ă΂��)
    $("#subscribe > button").click(function() {
        var select = $("#subscribe > select");

        console.log("-- Subscribe --");
        console.log("Topic: " + select.val());

        sess.subscribe(select.val(), function (topic, event) {
            console.log("-- Received --");
            console.log("Topic: " + topic);
            console.log("event: " + event);
        });
    });

    // �w�ǉ��� (WampServer��onUnSubscribe���\�b�h���Ă΂��)
    $("#unsubscribe > button").click(function() {
        var select = $("#unsubscribe > select");

        console.log("-- Unsubscribe --");
        console.log("Topic: " + select.val());

        try {
            sess.unsubscribe(select.val());
        } catch(e) {
            console.warn(e);
        }
    });

    // RPC (WampServer��onCall���\�b�h���Ă΂��)
    $("#rpc > button").click(function() {
        var select = $("#rpc > select");

        console.log("-- RPC --");
        console.log("Method: " + select.val());

        sess.call(select.val()).then(function (result) {
           // do stuff with the result
           console.log(result);
        }, function(error) {
           // handle the error
           console.log(error);
        });
    });

    $("#send > button").click(function() {
    $.ajax({
        url: "<?=$this->Url->build(['controller' =>'Reader','action' => 'addbot'], true); ?>",
        type: "POST",
        data: {
	         },
        success : function(response){
            //通信成功時の処理
        	console.log(response);
        },
        error: function(response){
            //通信失敗時の処理
            alert('通信失敗・発信');
            return;
        }
    });
    });

</script>
