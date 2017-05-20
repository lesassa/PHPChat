<h2>ログイン認証</h2>
<?=$this->Form->create( null,['type' => 'post']) ?>
	<fieldset>
		<legend>ログインフォーム</legend>
		<div class="error-message"><?=$errorMessage ?></div>
		<table>
			<tr>
				<th>ログインID</th>
				<td><?=$this->Form->input("loginId", ["type" => "text", "label" => false,]) ?></td>
			</tr>
			<tr>
				<th>パスワード</th>
				<td><?=$this->Form->input("password", ["value" => "", "type" => "password", "label" => false,]) ?></td>
			</tr>
			<tr><td colspan="2"></label><?=$this->Form->submit('ログイン') ?></td></tr>
		</table>
		<?=$this->Html->link('アカウント作成', ['controller'=>'Reader', 'action'=>'createMember']); ?>
	</fieldset>
<?=$this->Form->end() ?>