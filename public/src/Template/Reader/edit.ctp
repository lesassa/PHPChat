<script>
jQuery(function ($) {

	$(document).on('click','#upload',function(){
		var fd = new FormData();
		fd.append( "icon", $('input[name=icon]').prop("files")[0]);

    	$.ajax({
    	    url: "<?=$this->Url->build(['controller' =>'Reader','action' => 'uploadIcon'], true); ?>",
    	    type: "POST",
    	    data: fd,
    	    cache       : false,
            contentType : false,
            processData : false,
            dataType    : "html",
    	    //通信成功時の処理
    	    success : function(response){

    	    	var msg = JSON.parse(response);

    	    	//エラーメッセージ初期化
    	    	$(".error-message").remove();

				//メッセージなし
				if (msg["status"] == "success") {
					var html = msg["html"];
					$(".icon img").remove();
					$(".icon").prepend(html[".icon"]);
					//フォームリセット
	        		$('input[name=icon]').val('');
	            	var error = [
	                    "<div class=\"error-message\">",
	                    "アップロード完了",
	                    "</div>",
	                ].join("");
	        		$("[name=icon]").after(error);
					return;
				}

	            //エラーメッセージ表示
	            for(var key in msg["errors"]){
	            	for(var key2 in msg["errors"][key]){
		            	var error = [
		                    "<div class=\"error-message\">",
		                    msg["errors"][key][key2],
		                    "</div>",
		                ].join("");

	            	}
	            }
    	    },
    	  	//通信失敗時の処理
    	    error: function(response){

    	        alert('[アイコンアップロード]通信失敗');
    	    }
    	});
	});

});
</script>

<?=$this->Form->create($member,['type' => 'post']) ?>
	<table>
		<tr><th>アカウント作成</th></tr>
		<tr><td>
			ニックネーム<br/>
			<?=$this->Form->input("memberName", ["type" => "text",]) ?>
		</td></tr>
		<tr><td>
			ログインID<br/>
			<?=$member->login->loginId ?>
			<?=$this->Form->input("login.loginId", ["type" => "hidden",]) ?>
		</td></tr>
		<tr><td><?=$this->Form->input("edit", ["type" => "submit",]) ?></td></tr>
	</table>
<?=$this->Form->end() ?>


<?=$this->Form->create(null,['type' => 'post']) ?>
	<table>
		<tr>
			<th>
				アイコン
				<div class="icon"><img src="/icon/<?=$member->icon ?>" alt="<?=$member->memberName ?>" /></div>
			</th>
			<td><?=$this->Form->input("icon", ["type" => "file",]) ?></td>
		</tr>
	<tr><td colspan="2"><?=$this->Form->input("upload", ["type" => "button",]) ?></td></tr>
	</table>
<?=$this->Form->end() ?>

<?=$this->Html->link('戻る', ['controller'=>'Chat', 'action'=>'index']); ?>