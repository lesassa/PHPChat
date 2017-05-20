<?php foreach($rooms as $room): ?>
		<div class="menu"><p class="room<?=$room->roomId ?>">
			<?=$room->roomName ?>
			<span class="unread" id="unread<?=$room->roomId ?>"></span>
		</p></div>
<?php endforeach; ?>
<div class="menu"><p class="rooms">
	ルーム一覧
	<span class="unread" id="unreads"></span>
</p></div>