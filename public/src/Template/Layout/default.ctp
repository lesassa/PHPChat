<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITLE ?></title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

	<?= $this->Html->css('style.css') ?>

	<!-- javascript -->
	<?= $this->Html->script('jquery-3.2.1.min.js') ?>
</head>

<body>
	<div id="container">

		<!-- ヘッダー -->
		<header>
			ポータルサイト created by <a href="" target="_self"><b>????</b></a>
			<h1><?=TITLE ?></h1>
<!--			ようこそ  <?=$loginTable->memberName ?>さん -->
		</header>
		<!-- /ヘッダー -->

		<!-- メニュー -->
		<nav>
			<?= $this->element('menu') ?>
		</nav>
		<!-- /メニュー -->

		<!-- メイン -->
		<div id="main"><div class="inner">
			<?php if (isset($loginTable)): ?>
			<br class="none"/>
			<?php endif; ?>
			<?= $this->fetch('content') ?>
		</div></div>
		<!-- /メイン -->

	</div>

	<!-- フッター -->
	<footer>
		<p><input type="button" value="上に戻る" id="move-page-top" /></p>
		<small>Copyright&copy; ????　All Rights Reserved.</small>
	</footer>
	<!-- /フッター -->

</body>
</html>