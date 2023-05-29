<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$temp_wr_id=$wr_id;

if($w==''||$w=='u'){
$stamp="";
$box="";
$due="";
if($stamp_size) $stamp=implode("||",$stamp_size);
if($box_style) $box=implode("||",$box_style);
if($duration) $due=implode("||",$duration);

if($w==''){
	$stamp_list=array();
	$stamp_list=array_fill(0,$wr_1,'x');
	$stamps=implode("|",$stamp_list);
}
else if($w=='u'){
	$stamp_list=explode("|",$wr_content);
	if($wr_1!=count($stamp_list)){
		$stamp_cnt=$wr_1-count($stamp_list);
		if($stamp_cnt<0){ 
			$stamp_cnt=($stamp_cnt*-1)-1; 
			$max=count($stamp_list)-1;
			for($k=$max;$k>$stamp_cnt;$k--){
			unset($stamp_list[$k]);
			}
		}else{
			$max=$stamp_cnt+count($stamp_list);
			for($k=count($stamp_list);$k<$max;$k++){
			$stamp_list[$k]='x';
			}
		}
		$stamps=implode("|",$stamp_list);
	}
	else $stamps=$wr_content;
}

sql_query("update {$write_table} set wr_2='{$stamp}', wr_3='{$box}' , wr_4= '{$due}', wr_content='{$stamps}' where wr_id='{$wr_id}' ");
}
// 자신만의 코드를 넣어주세요.
goto_url("./board.php?bo_table=$bo_table");
?>
