<?
//if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
include_once('./_common.php');

if($clap_max==0 || ($clap_max>0 && $cl_cnt<$clap_max)){
    sql_query("insert into {$g5['clap_table']}
        set cl_ip='{$_SERVER['REMOTE_ADDR']}',
            cl_date='".date("Y-m-d H",strtotime(G5_TIME_YMDHIS))."'
    ");
}
if($return_url)
goto_url($return_url);
?>
