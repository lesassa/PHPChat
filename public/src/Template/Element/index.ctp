新規ルーム作成
<?=$this->Form->create(null,['type' => 'post']) ?>
<table>
	<tr><td>
		ルーム名<br/>
		<?=$this->Form->input("roomName", ["type" => "text",]) ?>
	</td></tr>
	<tr><td>
		ルームの説明<br/>
		<?=$this->Form->input("roomDescription", ["type" => "textarea",]) ?>
	</td></tr>
</table>
<?=$this->Form->input("create", ["type" => "button",]) ?>
<?=$this->Form->end() ?>