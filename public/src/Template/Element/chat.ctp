<hr>
<p class="chatNumber<?=$chat->chatNumber ?>">
	<?php if ($chat->replyId != ""): ?>
		<?=$this->Form->input(">> ".$chat->replyId, ["type" => "button", "name" => "quotation".$chat->replyId, "value" => $chat->replyId,]) ?><br/>
	<?php endif; ?>
	<?=$chat->chatNumber ?>:<?=$chat->member->memberName ?> ＜ <?=nl2br (h($chat->chatText)) ?><br/>

	<svg class="goodButton" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 54 72">
		<path d="M38.723,12c-7.187,0-11.16,7.306-11.723,8.131C26.437,19.306,22.504,12,15.277,12C8.791,12,3.533,18.163,3.533,24.647 C3.533,39.964,21.891,55.907,27,56c5.109-0.093,23.467-16.036,23.467-31.353C50.467,18.163,45.209,12,38.723,12z"/>
	</svg>
	<svg class="replyButton" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 65 72" name="reply<?=$chat->chatNumber ?>" value="<?=$chat->chatNumber ?>">
  		<path d="M41 31h-9V19c0-1.14-.647-2.183-1.668-2.688-1.022-.507-2.243-.39-3.15.302l-21 16C5.438 33.18 5 34.064 5 35s.437 1.82 1.182 2.387l21 16c.533.405 1.174.613 1.82.613.453 0 .908-.103 1.33-.312C31.354 53.183 32 52.14 32 51V39h9c5.514 0 10 4.486 10 10 0 2.21 1.79 4 4 4s4-1.79 4-4c0-9.925-8.075-18-18-18z"/>
	</svg>
</p>
