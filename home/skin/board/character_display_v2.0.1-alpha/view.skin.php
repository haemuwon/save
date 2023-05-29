<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/* Testing code */
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if($write['wr_type'] == 'pair') {
	goto_url($list_href.$qstr);
}

include_once($board_skin_path.'/_config.php');
include_once($board_skin_path.'/_setting.php');
include_once($board_skin_path.'/_setting.option.php');
include_once($board_skin_path.'/_setting.component.php');
include_once($board_skin_path.'/_setting.view.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/common.animate.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.view.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.animate.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.closet.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$back_url = $list_href."&amp;sst=".$sst;

include_once($board_skin_path."/view.skin.core.php");
?>