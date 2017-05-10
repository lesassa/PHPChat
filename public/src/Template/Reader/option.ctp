<h2>各種設定</h2>

<h3>企画名</h3>
<?=TITLE ?>(<?=$param->paramName ?>)<br/>
<br/>
<h3>チケット</h3>
<table>
	<tr><th>券種</th><th>単価(円/人)</th><th>通常券種</th><th>発券枚数</th><th>発券可否</th></tr>
	<?php foreach($tickets as $ticket): ?>
	<tr>
		<td><?=$ticket->ticketName ?></td>
		<td><?=$ticket->ticketPrice ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($ticket->ticketNormal) ?></td>
		<td><?=$ticket->ticketNumber ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($ticket->ticketSign) ?></td>
	</tr>
	<?php endforeach; ?>
</table>
<br/>
<h3>ステージ</h3>
<table>
	<tr><th>日程</th><th>予約終了時刻</th><th>座席</th><th>空席</th></tr>
	<?php foreach($stages as $stage): ?>
	<tr>
		<td><?=$stage->stageTime ?></td>
		<td><?=$stage->stageEffectEnd ?></td>
		<td><?=$stage->stageSeats ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($stage->stageEmpty) ?></td>
	</tr>
	<?php endforeach; ?>
</table>
<br/>
<h3>定期報告</h3>
※12:00に予約報告が届きます。<br/>
<table>
	<tr><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th><th>日</th></tr>
	<tr>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportMon) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportTue) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportWed) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportThurs) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportFri) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportSat) ?></td>
		<td><?=$this->Common->getTrueFalseSymbol($param->reportSun) ?></td>
	</tr>
</table>
<br/>
<h3>API</h3>
<table>
	<?php foreach($apis as $api): ?>
	<tr>
		<th><?=$api->apiName ?></th>
		<td><?=$this->Common->getTrueFalseSymbol($api->apiTolken) ?></td>
	</tr>
	<?php endforeach; ?>
</table>