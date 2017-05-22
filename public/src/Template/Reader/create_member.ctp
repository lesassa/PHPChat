<?=$this->Form->create($member,['type' => 'post']) ?>
	<table>
		<tr><th>アカウント作成</th></tr>
		<tr><td>
			ニックネーム<br/>
			<?=$this->Form->input("memberName", ["type" => "text",]) ?>
		</td></tr>
		<tr><td>
			ログインID<br/>
			<?=$this->Form->input("login.loginId", ["type" => "text",]) ?>
		</td></tr>
		<tr><td>
			パスワード<br/>
			<?=$this->Form->input("login.password", ["type" => "password",]) ?>
		</td></tr>
		<tr><td>
			パスワード（確認）<br/>
			<?=$this->Form->input("login.passwordCheck", ["type" => "password",]) ?>
		</td></tr>
		<tr><td><?=$this->Form->input("create", ["type" => "submit",]) ?></td></tr>
	</table>
<?=$this->Form->end() ?>
<?=$this->Html->link('戻る', ['controller'=>'Reader', 'action'=>'login']); ?>