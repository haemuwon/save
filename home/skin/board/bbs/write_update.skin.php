<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
$temp=sql_fetch("select * from {$write_table}");
if(!isset($temp['wr_protect'])){
	sql_query(" ALTER TABLE `{$write_table}` ADD `wr_protect` varchar(255) NOT NULL DEFAULT '' AFTER `wr_url` ");
	} 
unset($temp);

if($w!='c' && $w!='cu'){

$temp_wr_id = $wr_id;

$sec=""; 
$mem=0;
$protect="";
if($set_secret) {

	if($set_secret=='secret'){
		$sec="secret";
	} 
	else if ($set_secret=='member'){
		$mem=1;
	}
	else if($set_secret == 'protect' && $wr_protect!=''){
		$protect=$wr_protect;
	}
}
	sql_query("update {$write_table} set wr_option='$html,$sec', wr_secret='{$mem}', wr_protect= '{$wr_protect}' where wr_id='{$wr_id}'");
} 
	goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.$qstr);
?>
