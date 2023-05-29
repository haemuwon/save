(function($){
	$(document).ready(function() {
		$(".smarteditor2").each( function(index){
			var get_id = $(this).attr("id");

			if( !get_id || $(this).prop("nodeName") != 'TEXTAREA' ) return true;

			nhn.husky.EZCreator.createInIFrame({
				oAppRef: oEditors,
				elPlaceHolder: get_id,
				sSkinURI: g5_editor_url+"/SmartEditor2Skin.html",	
				htParams : {
					bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
					bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
					bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
					aAdditionalFontList : [['Noto Sans KR', 'Noto Sans KR'],['Nanum Gothic', '나눔고딕'], ['Nanum Myeongjo', '나눔명조'], ['S-CoreDream-3Light', '에스코어드림'], ['Chosunilbo_myungjo', '조선일보명조체'], ['InkLipquid', '잉크립퀴드'], ['establishRetrosansOTF', '설립체 유건욱'], ['양진체', '양진체'], ['KCC-eunyoung', 'KCC은영체'], ['PyeongChangPeace-Bold', '평창평화체'], ['TTTtangsbudaejjigaeB', '땅스부대찌개'], ['SangSangShin', '신과장'], ['Gothic_Goding', '고딕아니고고딩'], ['LeferiPoint-SpecialItalicA', '레페리포인트'], ['ChosunGu', '조선굴림체'], ['RIDIBatang', '리디바탕체']],		// 추가 글꼴 목록
					fOnBeforeUnload : function(){
						//alert("완료!");
					}
				}, //boolean
				fOnAppLoad : function(){
					//예제 코드
					//oEditors.getById["ir1"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
				},
				fCreator: "createSEditor2"
			});
		});
	});
})(jQuery);