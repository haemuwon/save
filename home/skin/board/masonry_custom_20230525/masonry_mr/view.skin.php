<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가


if($wr_id) {
	$log=sql_fetch("select wr_num from {$write_table} where wr_id='{$wr_id}'");
	$log=$log['wr_num']*-1;
	goto_url(G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&log='.$log); 
}
?>
