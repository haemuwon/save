<?
include_once('./_common.php');

// 권한 확인
if(!$is_admin) { 
	if($member['mb_id'] && $write['mb_id'] != $member['mb_id']) { // 왜 이런 오류가...? (2022-12-27)
		exit;
	} else if ($member['mb_level'] < $board['bo_write_level']) {
		exit;
	}
}

$update_href = true;

$sql_article = "";
if($add_new_body != "") {
	$sql_article = "bd_url = '{$add_new_body}' ";
} else {
	// -- 전신이미지
	if($_FILES['add_new_body_file']['name']) {
		// 확장자 따기
		$exp = explode(".", $_FILES['add_new_body_file']['name']);
		$exp = $exp[count($exp)-1];
		$image_name = "body_{$wr_id}_".time().".".$exp;
		upload_file($_FILES['add_new_body_file']['tmp_name'], $image_name, $character_image_path);
		$sql_article = "bd_url = '{$character_image_url}/{$image_name}' ";
	}
}

if($sql_article != "") {
	$sql_article = $sql_article.", bd_name = '{$add_new_body_name}', bd_secret = '{$add_new_body_secret}', bo_table = '{$bo_table}', wr_id = '{$wr_id}'";
	$sql = "insert into {$g5['character_body_table']} set {$sql_article}";
	sql_query($sql);
}

include($board_skin_path."/view_body.skin.php");
?>
