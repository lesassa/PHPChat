	<div class="menu"><?=$this->Html->link('メイン', ['controller'=>'Reader', 'action'=>'index']); ?></div>
	<div class="arrow"><?=$this->Html->link('予約', ['controller'=>'Reader', 'action'=>'index']); ?></div>
	<div class="arrow"><?=$this->Html->link('WEB管理', ['controller'=>'Web', 'action'=>'index']); ?>	</div>
	<div class="arrow"><?=$this->Html->link('組織管理', ['controller'=>'Team', 'action'=>'index']); ?>	</div>
	<div class="arrow"><?=$this->Html->link('アカウント', ['controller'=>'Member', 'action'=>'index']); ?>	</div>
	<div><?=$this->Html->link('ログアウト', ['controller'=>'Reader', 'action'=>'logout']); ?></div>
