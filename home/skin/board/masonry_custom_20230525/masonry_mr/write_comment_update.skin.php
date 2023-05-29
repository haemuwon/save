<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
 
$customer_sql = "";
$temp_wr_id = $comment_id;
$wr_num = $wr['wr_num']; 

include_once($board_skin_path.'/write_update.inc.php');  

if($bf_file_del){
	$file=sql_fetch("select * from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$comment_id}' and bf_no='0'");
	sql_query("delete from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$comment_id}' and bf_no='0'");
	sql_query("update {$write_table} set wr_file='0', wr_type='' where wr_id='{$comment_id}'");
}

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
	$customer_sql .= ",wr_type='UPLOAD', wr_file='1', wr_width='{$files['img'][0]}', wr_height='{$files['img'][1]}'";
 }

if($w != 'cu') {  
	$sql = " update {$write_table}
				set wr_subject = '{$wr_subject}'
				{$customer_sql}
			  where wr_id = '{$comment_id}' ";
	sql_query($sql);
} else {
	$sql = " update {$write_table}
				set wr_id = '{$comment_id}'
				{$customer_sql}
			  where wr_id = '{$comment_id}' ";
	sql_query($sql);

}


goto_url('./board.php?bo_table='.$bo_table.'&amp;'.$qstr.'&amp;#c_'.$comment_id);
?>
