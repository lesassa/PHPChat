<hr>
<p class="chatNumber<?=$chat->chatNumber ?>">
	<?php if ($chat->replyId != ""): ?>
		<?=$this->Form->input(">> ".$chat->replyId, ["type" => "button", "name" => "quotation".$chat->replyId, "value" => $chat->replyId,]) ?><br/>
	<?php endif; ?>
	<?=$chat->chatNumber ?>:<?=$chat->member->memberName ?> ＜ <?=nl2br (h($chat->chatText)) ?><br/>
	<?=$this->Form->input("返信", ["type" => "button", "value" => $chat->chatNumber, "name" => "reply".$chat->chatNumber,]) ?>
</p>
