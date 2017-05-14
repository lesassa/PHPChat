新規ルーム作成
<?=$this->Form->create(null,['type' => 'post']) ?>
<table>
	<tr><td><?=$this->Form->input("roomName", ["type" => "text",]) ?></td></tr>
	<tr><td><?=$this->Form->input("roomDescription", ["type" => "textarea",]) ?></td></tr>
</table>
<?=$this->Form->input("create", ["type" => "button",]) ?>
<?=$this->Form->end() ?>