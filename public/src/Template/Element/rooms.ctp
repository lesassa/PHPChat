<div class="room9999">
	<h2>ルーム一覧</h2>
	<?php foreach($rooms as $room): ?>
		<?= $this->element('roomList', ['room'=> $room]) ?>
	<?php endforeach; ?>
</div>

