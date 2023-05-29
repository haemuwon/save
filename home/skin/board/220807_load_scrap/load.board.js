

// 즐겨찾기 추가 - Ajax
$('a[data-function="favorite"]').on('click', function() {
	var formData = new FormData();
	var idx = $(this).data('idx');
	var obj = $(this);
	formData.append("wr_id", idx);
	formData.append("bo_table", g5_bo_table);
	formData.append("mb_id", avo_mb_id);

	$.ajax({
		url:avo_board_skin_url + '/ajax/add_favorite.php'
		, data: formData
		, processData: false
		, contentType: false
		, type: 'POST'
		, success: function(data){
			if(data == 'on') { 
				obj.removeClass('on');
				obj.addClass(data);
			}else if(data == 'off') { 
				obj.removeClass('on');
			}
		}
	});

	return false;
});


$('a.ui-open-log').on('click', function() {

	var obj = $(this).closest('.pic-data').children('div');
	var state = $(obj).hasClass('on');
	var height = $(obj).data('height');
	var original_height;
	if (height==0)
	{
		original_height='100%';
	}else original_height=height+'px'; 

	if(state){ 
		//닫기
		$(obj).stop().slideToggle(600);
		$(obj).removeClass('on');
		$(this).text("OPEN");
	} else {
		// 열기
		$(obj).stop().slideToggle(600);
		$(obj).addClass('on');
		$(this).text("CLOSE");
	}

	return false;
});

$('a.ui-remove-blind').on('click', function() {
	$(this).closest('.pic-data').removeClass('ui-blind');
	$(this).fadeOut();
	return false;
});

$('.send_memo').on('click', function() {
	var target = $(this).attr('href');
	window.open(target, 'memo', "width=500, height=300");
	return false;
});

$('.btn-search-guide').on('click', function() {
	$('#searc_keyword').toggleClass('on');
	return false;
}); 

$('.new_win').on('click', function() {
	var target = $(this).attr('href');
	window.open(target, 'emoticon', "width=400, height=600");
	return false;
}); 