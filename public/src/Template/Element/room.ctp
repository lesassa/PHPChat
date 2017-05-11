<div id="room<?=$roomId ?>">

<div id="chats<?=$roomId ?>">
	<?php foreach($chats as $chat): ?>
		<?= $this->element('chat', ['chat'=>$chat]) ?>
	<?php endforeach; ?>
</div>
</div>

