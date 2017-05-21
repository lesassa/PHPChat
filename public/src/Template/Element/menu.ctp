<?php foreach($rooms as $room): ?>
		<div class="menu"><p class="room<?=$room->roomId ?>">
			<?=$room->roomName ?>
			<span class="unread" id="unread<?=$room->roomId ?>"></span>
		</p></div>
<?php endforeach; ?>
<div class="menu"><p class="room9999">
	ルーム一覧
	<span class="unread" id="unread9999"></span>
</p></div>