<?php
include_once('./_common.php');
include_once('./password.skin.php');

if ($_POST['wr_pass']) {
	set_cookie('read_' . $_POST['wr_id'], $_POST['wr_pass'], 3600);
}

die("{\"url\": \"" . G5_HTTP_BBS_URL . '/board.php?bo_table=' . $bo_table . '&amp;wr_id=' . $wr_id . $qstr . "\"}");
