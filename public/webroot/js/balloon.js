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

	//返信ボタン
	$('#main').on('mouseenter', '[name^=quotation]', function() {
		var replyId = $(this).val();
		var url = 'http://172.19.118.45:8765/chat/get-chat/' + room + '/' + replyId;
		$(this).showBalloon({
			html: true,
			contents: '<img src="https://urin.github.io/jquery.balloon.js/img/balloon-sample-loading.gif" alt="loading..." width="25" height="25">',
			url: url,
		})
	});
	$('#main').on('mouseleave', '[name^=quotation]', function() {
		$(this).hideBalloon();
	});
});
