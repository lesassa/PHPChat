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
		<tr><td><?=$this->Form->input("create", ["type" => "submit",]) ?></td></tr>
	</table>
<?=$this->Form->end() ?>
<?=$this->Html->link('戻る', ['controller'=>'Reader', 'action'=>'login']); ?>