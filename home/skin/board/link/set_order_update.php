<?php 
include_once("./_common.php");

for ($i=0;$i<count($idx);$i++){
	$k=$idx[$i];
	sql_query("update {$write_table}
		set wr_10 = '{$order[$k]}',
			wr_1 = '{$subject[$k]}',
			wr_link1='{$banner[$k]}',
			wr_link2='{$link[$k]}'
		where wr_id='{$k}'
	");
	if($board['bo_use_category']&&$board['bo_category_list'] && $category[$k]!=''){
		sql_query("update {$write_table} set ca_name='{$category[$k]}' where wr_id='{$k}'");
	}
}
goto_url('./set_order.php?bo_table='.$bo_table.'&write_table='.$write_table);
?>
