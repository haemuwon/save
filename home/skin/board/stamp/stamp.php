<?php
include_once("./_common.php");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
	if($bo_table && $wr_id ) { 
		if($idx==0)
			$idx="0";
		
		$file=array();
		$file_ck=sql_fetch("select wr_file,wr_content,wr_last from {$write_table} where wr_id='{$wr_id}'");
		$stamps=explode("|",$file_ck['wr_content']);

		if($file_ck['wr_file']>0){
		$files=sql_query("select bf_file, bf_no from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$wr_id}' ");	
		$count=0;
		for($i=0;$file_list=sql_fetch_array($files);$i++){
			if(!$file_list['bf_file']) continue;
			$file[$i]['src']=G5_DATA_URL."/file/".$bo_table."/".$file_list['bf_file'];
			$count++;
		}
		} 
			if(!$file[0]['src'])$file[0]['src']=$board_skin_url.'/no_stamp.png';
			if(!$file[1]['src'])$file[1]['src']=$board_skin_url.'/stamp.png';
		 
		
		if($state=='off'){
			$stamps[$idx]="x";
			$stamp=implode("|",$stamps);
			sql_query("update {$write_table} set wr_content='{$stamp}' where wr_id='{$wr_id}'");
			$count_s=0;
			for ($h=($idx+1);$h<count($stamps);$h++){
				if($stamps[$h]!="x") $count_s++;
			}
			if($count_s>0){ 
				$last_day=date('m.d',strtotime($file_ck['wr_last']));
			}else{
			if($idx>1){
			for($k=1;$k<=$idx;$k++){
				$last_s=$stamps[$idx-$k];
				if($last_s!='x') break;
			}
			$last=explode(",",$last_s);
			$last_day=$last[1];
			$date=date('Y-m-d H:i:s',strtotime( date('Y').'.'.$last_day));
			sql_query("update {$write_table} set wr_last='{$date}'");
			}else{
				$last_day="";
			}}
			?> 
			<span class="num txt-point"><?=$idx+1?></span>
			<a href="#" onclick="stamp_check('<?=$wr_id?>','<?=$idx?>','on');return false;"><img src="<?=$file[0]['src']?>"></a>
			--
			<span><?=$last_day ? "마지막 스탬프: ".$last_day : "";?></span>
			<?if($last_day) echo '<hr class="line">';?>
		<?}else{ 
			if($file_ck['wr_file']<2)
			$seed=1;
			else $seed=rand(1,$file_ck['wr_file']-1); 
			$count_s=0;
			for ($h=($idx+1);$h<count($stamps);$h++){
				if($stamps[$h]!='x') $count_s++;
			}
			if($count_s>0){
				$last_day=date('m.d',strtotime($file_ck['wr_last']));
				$date="";
			}else{
			$last_day="";
			$date=",wr_last='".G5_TIME_YMDHIS."'";
			$today=date('m.d',G5_SERVER_TIME);
			}
			$stamps[$idx]=$seed.','.$today;
			$stamp=implode("|",$stamps);
			sql_query("update {$write_table} set wr_content='{$stamp}' {$date} where wr_id='{$wr_id}'");
		?>	
			<span class="num txt-point"><?=$idx+1?></span>
			<a href="#" onclick="stamp_check('<?=$wr_id?>','<?=$idx?>','off');return false;"><img src="<?=$file[$seed]['src']?>"></a>
			--
			<span>마지막 스탬프:  <?=$last_day ? $last_day : $today;?></span>
			<hr class="line">
		<?}
	}
?>
