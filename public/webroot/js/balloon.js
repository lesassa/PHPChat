/**
 * 各種吹き出し制御
 */
jQuery(function ($) {

	//返信ボタン
	$('#main').on('mouseenter', '[name^=reply]', function() {
		$(this).showBalloon({contents: "返信"})
	});
	$('#main').on('mouseleave', '[name^=reply]', function() {
		$(this).hideBalloon();
	});
	$('#main').on('click', '[name^=reply]', function() {
		$(this).css('fill','#0099B9');;
	});

	//イイネボタン
//	$('#main').on('mouseenter', '.goodButton', function() {
//		$(this).showBalloon({contents: "どうでもイイネ"})
//	});
//	$('#main').on('mouseleave', '.goodButton', function() {
//		$(this).hideBalloon();
//	});
});
