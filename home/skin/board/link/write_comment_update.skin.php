<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
  
$customer_sql = "";
$temp_wr_id = $comment_id;
$wr_num = $wr['wr_num'];
if(!$wr_num) $wr_num = $comment['wr_num'];
include_once($board_skin_path.'/upload_file.php');
 if($_FILES['bf_file']['name']){ 
	$files=upload_c_file('bf_file',$bo_table); 
	$file=sql_fetch("select * from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$comment_id}' and bf_no='0'");
	$sql_common="bf_source='{$files['source']}',
				bf_file='{$files['name']}',
				bf_filesize='{$files['size']}',
				bf_width = '{$files['img'][0]}',
				bf_height = '{$files['img'][1]}',
				bf_type = '{$files['img'][2]}',
				bf_content = '{$bf_content}',
				bf_datetime = '".G5_TIME_YMDHIS."' ";
	if($file['wr_id']){
		sql_query("update {$g5['board_file_table']}
			set {$sql_common}
				where bo_table = '{$bo_table}' and wr_id='{$comment_id}' and bf_no = '0'
		");
	}else{
		sql_query("insert into {$g5['board_file_table']}
			set {$sql_common},
				wr_id = '{$comment_id}' ,
				bo_table = '{$bo_table}' ,
				bf_no = '0'
		");

	}
 }

if($w=='cu' && $bf_content){ 
sql_query("update {$g5['board_file_table']}
			set bf_content = '{$bf_content}'
			where wr_id ='{$comment_id}' and
				  bo_table = '{$bo_table}' and
				  bf_no = '0'
		");
}
?>
