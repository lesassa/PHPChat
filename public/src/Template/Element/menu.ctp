<ul>
	<li><?=$this->Html->link('メイン', ['controller'=>'Reader', 'action'=>'index']); ?></li>
	<li class="arrow"><?=$this->Html->link('予約', ['controller'=>'Reader', 'action'=>'index']); ?></li>
	<li class="arrow"><?=$this->Html->link('WEB管理', ['controller'=>'Web', 'action'=>'index']); ?>
		<ul class="ddmenu">
			<li><?=$this->Html->link('怪談', ['controller'=>'Kwaidan', 'action'=>'index']); ?></li>
			<li><?=$this->Html->link('お問い合わせ', ['controller'=>'Contacts', 'action'=>'index']); ?></li>
		</ul>
	</li>
	<li class="arrow"><?=$this->Html->link('組織管理', ['controller'=>'Team', 'action'=>'index']); ?>
		<ul class="ddmenu">
			<li><?=$this->Html->link('掲示板', ['controller'=>'BBS', 'action'=>'index']); ?></li>
			<li><?=$this->Html->link('共有フォルダ', ['controller'=>'Files', 'action'=>'index']); ?></li>
			<li><?=$this->Html->link('スケジュール', ['controller'=>'Schedule', 'action'=>'index']); ?></li>
		</ul>
	</li>
	<li class="arrow"><?=$this->Html->link('アカウント', ['controller'=>'Member', 'action'=>'index']); ?>
		<ul class="ddmenu">
			<li><?=$this->Html->link('変更', ['controller'=>'Member', 'action'=>'edit', $loginTable->memberId]); ?></li>
			<li><?=$this->Html->link('招待', ['controller'=>'Member', 'action'=>'invite']); ?></li>
		</ul>
	</li>
	<li><?=$this->Html->link('ログアウト', ['controller'=>'Reader', 'action'=>'logout']); ?></li>
</ul>
