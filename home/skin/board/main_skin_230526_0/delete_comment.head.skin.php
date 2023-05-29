<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

	//전체삭제
  $result = sql_fetch("select wr_1, wr_2, wr_3, wr_4, wr_5, wr_6 from {$write_table} where wr_id = '{$comment_id}' ");
  for ($d=1; $d < 7; $d++) {
    if($result['wr_'.$d]){
       if($result['wr_'.$d] == $comment_id){
        $found = 'wr_'.$d;
        $found_next = 'wr_'.($d + 1);
    }
    }
  }

  $del_result = sql_query("select * from {$write_table} where {$found} = '{$comment_id}' and {$found_next} not in('') ");
  for ($d=0; $row = sql_fetch_array($del_result); $d++) {
    sql_query(" delete from $write_table where wr_id = '{$row['wr_id']}' ");
    // 새글 삭제
    sql_query(" delete from {$g5['board_new_table']} where bo_table = '{$bo_table}' and wr_id = '{$row['wr_id']}' "); 
  }
  
  // 부모 요소 코멘트 여부 전환
  // 다른 자식이 있는가?
  $child = sql_query(" SELECT * from {$write_table} where wr_parent = '{$wr_parent}' ");
  $is_child = sql_num_rows($child);
  if( $is_child <= 1 ){
    sql_query(" UPDATE $write_table 
    set has_comment = 0 
    where wr_id = {$wr_parent} 
    ");
  }
  
?>