<h2>チャットルーム</h2>

<?php foreach($rooms as $room): ?>
<?=$this->Html->link($room->roomName, ['controller'=>'Chat', 'action'=>'chat', $room->roomId]); ?></br>


<?php endforeach; ?>