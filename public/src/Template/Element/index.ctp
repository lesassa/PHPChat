
<?=$this->Form->create(null,['type' => 'post']) ?>
<table id="createRoom">
	<tr><th>新規ルーム作成</th></tr>
	<tr><td>
		ルーム名<br/>
		<?=$this->Form->input("roomName", ["type" => "text",]) ?>
	</td></tr>
	<tr><td>
		ルームの説明<br/>
		<?=$this->Form->input("roomDescription", ["type" => "textarea",]) ?>
	</td></tr>
	<tr><td><?=$this->Form->input("create", ["type" => "button",]) ?></td></tr>
</table>
<?=$this->Form->end() ?>