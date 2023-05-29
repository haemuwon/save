<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 자신만의 코드를 넣어주세요.
if($wr_ck) {
    $src=['/0/i','/1/i','/2/i','/3/i','/4/i','/5/i','/6/i','/\|/i'];
    $dst=['일','월','화','수','목','금','토',','];
    $wr_ck=implode("|",$wr_ck);
    $repeat_days=$wr_ck;
    if($wr_ck=='1|2|3|4|5|6|0')
        $repeat_days="매일";
    else $repeat_days="매 ".preg_replace($src,$dst,$repeat_days);
    sql_query("update {$write_table} set wr_4='{$wr_ck}',wr_10='{$repeat_days}' where wr_id='{$wr_id}'");

}
sql_query("update {$write_table} set wr_subject='{$wr_content}' where wr_id='{$wr_id}'");

goto_url("./board.php?bo_table=$bo_table&frdate=$frdate");
?>
