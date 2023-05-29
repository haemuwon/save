<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$temp_wr_id = $wr_id;
$customer_sql = "";
if(!$wr_num) $wr_num = $write['wr_num'];

$temp = sql_fetch("select * from {$write_table}"); 

if(!isset($temp['wr_direction'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_direction` text NOT NULL DEFAULT '' AFTER `wr_url` ");
}
if(!isset($temp['div_width'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `div_width` text NOT NULL DEFAULT '' AFTER `wr_url` ");
}
if(!isset($temp['div_height'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `div_height` text NOT NULL DEFAULT '' AFTER `wr_url` ");
}
if(!isset($temp['has_comment'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `has_comment` text NOT NULL DEFAULT '' AFTER `wr_comment` ");
}
if(!isset($temp['wr_style'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_style` text NOT NULL DEFAULT '' AFTER `wr_content` ");
}
if(!isset($temp['wr_style_detail'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_style_detail` text NOT NULL default '' AFTER `wr_content` ");
}
if(!isset($temp['wr_style_detail2'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_style_detail2` text NOT NULL default '' AFTER `wr_content` ");
}
if(!isset($temp['wr_content_detail'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_content_detail` text NOT NULL default '' AFTER `wr_content` ");
}

unset($temp);


if($div_width == '' || !$div_width){
	$div_width = '100%';
}
if($div_height == '' || !$div_height){
	$div_height = '800px';
}


if($write['wr_ing'] != '1' || $w == 'u') { 

	include_once($board_skin_path.'/write_update.inc.php');
	
	$sql = " update {$write_table}
				set wr_id = '{$wr_id}'
				{$customer_sql}
			  where wr_id = '{$wr_id}' ";
	sql_query($sql);
	
}

	

if ($file_upload_msg) {
	alert($file_upload_msg, G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
} else {
	goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
}
?>
