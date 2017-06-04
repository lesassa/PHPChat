<div class="room<?=$room->roomId?>">
<div id="chats<?=$room->roomId?>">
	<?php foreach( $room->chats as $chat): ?>
		<?= $this->element('chat', ['chat'=>$chat]) ?>
	<?php endforeach; ?>
</div>
</div>

