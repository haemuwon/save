<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$customer_sql = "";

	if($div_width == ''){
		$div_width = '100%';
	}
	if($div_height == ''){
		$div_height = '100%';
	}

$customer_sql .= "wr_type = '{$wr_type}'";

include_once($board_skin_path.'/write_update.inc.php');

	$sql = " UPDATE {$write_table}
				set {$customer_sql}
			  where wr_id = '{$comment_id}' ";
	sql_query($sql);

	$sql2 = " UPDATE {$write_table}
	set has_comment = '1'
	where wr_id = '{$wr_id}' ";			
	sql_query($sql2);


if ($file_upload_msg) {
	alert($file_upload_msg, G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
} else {
	goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
}
?>
