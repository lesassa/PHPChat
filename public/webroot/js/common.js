jQuery(function ($) {

	//上に戻る
	$("#move-page-top").click(
			function(){
				//[id:move-page-top]をクリックしたら起こる処理
				$("html,body").animate({scrollTop:0},"slow");
			}
	);

});
