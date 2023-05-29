<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//정렬
if($w != 'u'){
	if($w != 'c'){
		//새 글일 때
		$customer_sql .= ", wr_1 = '{$wr_id}' ";
	} else {
		// 코멘트일 때
		$num = strlen($wr_comment_reply) + 2;
		$pa = sql_fetch(" SELECT wr_parent from {$write_table} where wr_id = '{$comment_id}' ");
		$up = sql_fetch(" SELECT wr_1, wr_2, wr_3, wr_4, wr_5, wr_6 from {$write_table} where wr_id = '{$pa['wr_parent']}' ");
		if ($up['wr_1']){
			$customer_sql .= ", wr_1 = '{$up['wr_1']}' ";
		}
		if ($up['wr_2']){
			$customer_sql .= ", wr_2 = '{$up['wr_2']}' ";
		}
		if ($up['wr_3']){
			$customer_sql .= ", wr_3 = '{$up['wr_3']}' ";
		}
		if ($up['wr_4']){
			$customer_sql .= ", wr_4 = '{$up['wr_4']}' ";
		}
		if ($up['wr_5']){
			$customer_sql .= ", wr_5 = '{$up['wr_5']}' ";
		}
		if ($up['wr_6']){
			$customer_sql .= ", wr_6 = '{$up['wr_6']}' ";
		}

		$customer_sql .= ", wr_{$num} = '{$comment_id}' ";
	}
} 

	if($w == 'u'){
	//배열화
	$wr_style_detail = array();
	foreach(array("back1", "back2", "back3", "back4", "back5", "wr_move",
	"color", 
	"margin_auto", "margin_top", "margin_right", "margin_bottom", "margin_left", 
	"padding_auto", "padding_top", "padding_right", "padding_bottom", "padding_left") as $input) {
		$wr_style_detail[$input] = ${$input};
	}
	$temp_style = json_encode($wr_style_detail, JSON_UNESCAPED_UNICODE);
	$temp_style = str_replace("\\", "\\\\", $temp_style);

	//테마와 효과 동시 저장
	$theme = $theme.' '.$theme3;

	$wr_style_detail2 = array();
	foreach(array("border_width", "border_style", "border_color",
	"theme",
	"border_radius_all", "border_radius_top", "border_radius_right", "border_radius_bottom", "border_radius_left",
	"justify_content", "align_items") as $input) {
		$wr_style_detail2[$input] = ${$input};
	}
	$temp_style2 = json_encode($wr_style_detail2, JSON_UNESCAPED_UNICODE);
	$temp_style2 = str_replace("\\", "\\\\", $temp_style2);

	$wr_content_detail = array();
	foreach(array("content_type",
	"text1", "text2", "text3",
	"board1",
	"page1",
	"slider1", "slider2",
	"image1", "image2", "image3", "image4",
	"clock1", "clock1_", "clock1_1", "clock1_2", "clock2", "clock2_", "clock2_1", "clock2_2", "clock3", "clock3_", "clock3_1", "clock3_2", "clock4",
	"dday1", "dday2", "dday3", "dday4", "dday1_", "dday2_", "dday3_", "dday4_", "dday1_1", "dday2_1", "dday3_1", "dday4_1", "dday5", 
	"latest1", "latest2", "latest3",
	"links1", "links2", "links2_1", "links2_1_", "links2_2", "links2_3", "links3_1",  "links3_1_", "links3_2", "links3_3", "links4_1",  "links4_1_", "links4_2", "links4_3", "links5_1", "links5_1_", "links5_2", "links5_3"
	) as $input) {
		$wr_content_detail[$input] = ${$input};
	}
	$temp_content = json_encode($wr_content_detail, JSON_UNESCAPED_UNICODE);
	$temp_content = str_replace("\\", "\\\\", $temp_content);


	$customer_sql .= ", wr_url = '{$wr_url}', wr_style = '{$wr_style}', wr_style_detail = '{$temp_style}', wr_style_detail2 = '{$temp_style2}', wr_content_detail = '{$temp_content}' ";

	if($content_type == 'default'){
		$customer_sql .= ", wr_content = '{$wr_content_default}' ";
	} else if($content_type == 'text'){
		$customer_sql .= ", wr_content = '{$wr_content_text}' ";
	}

	}

$customer_sql .= ", wr_direction = '{$wr_direction}', div_width = '{$div_width}', div_height = '{$div_height}', has_comment = '{$has_comment}' ";

?>

