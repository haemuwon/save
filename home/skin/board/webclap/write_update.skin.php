<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($w!=u && $notice!=1)
include_once($board_skin_path.'/update_hit.php');

// 자신만의 코드를 넣어주세요.
goto_url("./board.php?bo_table=$bo_table");
?>
