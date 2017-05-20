<div id="rooms">
	<?php foreach($rooms as $room): ?>
		<table>
			<tr>
				<th>ルームネーム</th>
				<td><?=$room->roomName ?></td>
			</tr>
			<tr>
				<th>ルーム説明</th>
				<td><?=$room->roomDescription ?></td>
			</tr>
		</table>
	<?php endforeach; ?>
</div>

