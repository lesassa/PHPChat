<?php foreach($rooms as $room): ?>
	<div class="menu"><p class="room<?=$room->roomId ?>">
		<?=$room->roomName ?>
		<span class="unread" id="unread<?=$room->roomId ?>"></span>
	</p></div>
<?php endforeach; ?>