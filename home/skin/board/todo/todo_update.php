<?
include_once("./_common.php");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($_POST['sw']){
	if($_POST['sw']=='del'){ 
		sql_query("update {$write_table} set wr_9='' where wr_id='{$_POST['wr_id']}'");
		$wr=sql_fetch("select wr_7,wr_8 from {$write_table} where wr_id='{$_POST['wr_id']}'");
		if($wr['wr_8']){
			if($wr['wr_7'])	sql_query("update {$write_table} set wr_2='{$wr['wr_7']}' where wr_id='{$_POST['wr_id']}'");
			sql_query("delete from {$write_table} where wr_id='{$wr['wr_8']}'");
			sql_query("update {$g5['board_table']} set bo_count_write=bo_count_write-1 where bo_table='{$bo_table}'");
		}
	}else{
		$wr=sql_fetch("select * from {$write_table} where wr_id='{$_POST['wr_id']}'");
		if($wr['wr_4']){
			$now=date("N",$frdate);
			$tmp=str_replace('0','7',$wr['wr_4']);
			$nextdate="";
			for($k=1;$k<=7;$k++){
				$plusdate=$frdate."+".$k." days";
				$newdate=date('N',strtotime($plusdate));
				if(strstr($tmp,$newdate)){
					$nextdate=date("Ymd",strtotime($plusdate));
					break;
				}
			}
			$sql="";
			$next_full="";
			$wr_1="";
			$wr_7="";
			$next_wr_num = get_next_num($write_table);
			if($wr['wr_2']){
				if($nextdate<$wr['wr_2']){
					$tomorrow=date("Ymd",strtotime($frdate." +1 days"));
					$next_full=date("Y-m-d H:i:s",strtotime($tomorrow));
					$wr_1=$tomorrow;
					sql_query("update {$write_table} set wr_2='{$frdate}' where wr_id='{$_POST['wr_id']}'");
					$wr_7=$wr['wr_2'];
				}
			}else{
				$next_full=date("Y-m-d H:i:s",strtotime($nextdate));
				$wr_1=$nextdate;
			}
			if(!$wr['wr_2'] || ($wr['wr_2'] && $wr['wr_2']>$nextdate)){
				$sql = " insert into $write_table
				set wr_num = '$next_wr_num',
					wr_option = '{$wr['wr_option']}',
					wr_subject = '".addslashes($wr['wr_subject'])."',
					wr_content = '".addslashes($wr['wr_content'])."',
					mb_id = '{$wr['mb_id']}',
					wr_password = '{$wr['wr_password']}',
					wr_name = '".addslashes($wr['wr_name'])."',
					wr_datetime = '{$next_full}',
					wr_last = '{$next_full}',
					wr_ip = '{$wr['wr_ip']}',
					wr_type = '{$wr['wr_type']}',
					wr_1 = '{$wr_1}',
					wr_2 = '".addslashes($wr['wr_2'])."',
					wr_3 = '".addslashes($wr['wr_3'])."',
					wr_4 = '".addslashes($wr['wr_4'])."',
					wr_5 = '".addslashes($wr['wr_5'])."',
					wr_7 = '".addslashes($wr['wr_5'])."',
					wr_8 = '".addslashes($wr['wr_8'])."',
					wr_10 = '".addslashes($wr['wr_10'])."' ";
				sql_query($sql);
				$wr_parent = sql_insert_id();
				sql_query("update {$write_table} set wr_parent='{$wr_parent}' where wr_id='{$wr_parent}'");
				sql_query("update {$write_table} set wr_7='{$wr_7}', wr_8='{$wr_parent}' where wr_id='{$_POST['wr_id']}'");
				sql_query("update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}'");
			}	
		}
		sql_query("update {$write_table} set wr_9='".date('Ymd')."' where wr_id='{$_POST['wr_id']}'");
	}
}else{
for ($i=0;$i<count($_POST['wr_id']);$i++){
	$k=$_POST['wr_id'][$i];
	sql_query("update {$write_table}
		set wr_6 = '{$_POST['wr_6'][$k]}'
		where wr_id='{$k}'
	");
}
}
goto_url(G5_BBS_URL."/board.php?bo_table=$bo_table&frdate=$frdate");
?>
