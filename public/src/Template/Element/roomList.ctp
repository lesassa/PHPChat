<table>
	<tr>
		<th>ルームネーム</th>
		<td><?=$room->roomName ?></td>
	</tr>
	<tr>
		<th>ルーム説明</th>
		<td><?=$room->roomDescription ?></td>
	</tr>
	<tr>
		<td class="subscribe">
			<?=$this->Form->input("入室", ["type" => "button", "value" => $room->roomId]) ?>
			<?=$this->Form->input("roomName".$room->roomId, ["type" => "hidden", "value" => $room->roomName]) ?>
		</td>
		<td class="unsubscribe"><?=$this->Form->input("退室", ["type" => "button", "value" => $room->roomId]) ?></td>
	</tr>
</table>
