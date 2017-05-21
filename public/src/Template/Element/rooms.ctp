<div class="room9999">
	<h2>ルーム一覧</h2>
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

