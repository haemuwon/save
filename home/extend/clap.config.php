<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 랜덤 테이블값 추가
$g5['clap_table'] = G5_TABLE_PREFIX.'clap';


// 랜덤 테이블이 없을 경우 생성
if(!sql_query(" DESC {$g5['clap_table']} ")) {
	sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['clap_table']}` (
	  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
	  `cl_ip` varchar(255)  NOT NULL default '',
	  `cl_date` datetime NULL,
	  `cl_cnt` int(11) NOT NULL default 1,
	  `cl_val` varchar(255) NOT NULL default '',
	  PRIMARY KEY (`cl_id`)
	) ", false);
}


$last=date("Y-m-d",strtotime("today -30 day"));
$be=sql_fetch("select cl_id,cl_date from {$g5['clap_table']} where cl_cnt>1 and date_format(cl_date, '%Y-%m-%d') <= '{$last}' order by cl_date limit 1"); 

if($be['cl_id']) $last=date("Y-m-d",strtotime($be['cl_date']));

$sum=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date, '%Y-%m-%d') <= '{$last}'");
if($sum['sum']){
	$cnt=$sum['sum'];
	sql_query("delete from {$g5['clap_table']} where date_format(cl_date, '%Y-%m-%d') <= '{$last}'");
	sql_query("insert into {$g5['clap_table']} set cl_cnt='{$cnt}', cl_date='".G5_TIME_YMD."', cl_val=1");
}
?>