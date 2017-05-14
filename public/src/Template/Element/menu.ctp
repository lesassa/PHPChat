<?php foreach($rooms as $room): ?>
	<div class="menu"><p class="room<?=$room->roomId ?>"><?=$room->roomName ?></p></div>
<?php endforeach; ?>