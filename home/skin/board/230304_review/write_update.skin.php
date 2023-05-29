<?
if (!defined("_GNUBOARD_")) exit;

if($w!='c' && $w!='cu'){

$temp_wr_id = $wr_id;

$cover_img_path = G5_DATA_PATH."/file/".$bo_table;
$cover_img_url = G5_DATA_URL."/file/".$bo_table;

@mkdir($cover_img_path, G5_DIR_PERMISSION);
@chmod($cover_img_path, G5_DIR_PERMISSION);


if($w == 'u') { 
	// 이미지 삭제
	if($review_cover_del) {
		$prev_file_path = str_replace(G5_URL, G5_PATH, $review_cover);
		@unlink($prev_file_path);
		$review_cover = "";
	}
}

// 이미지 등록

// -- 세션카드
if($_FILES['review_cover']['name']) {
	// 확장자 따기
	$exp = explode(".", $_FILES['review_cover']['name']);
	$exp = $exp[count($exp)-1];

	$image_name = "cover_".time().".".$exp;
	upload_file($_FILES['review_cover']['tmp_name'], $image_name, $cover_img_path);
	$review_cover = $cover_img_url."/".$image_name;
}
$sql_article .= "wr_1		= '{$review_cover}'";

sql_query("update {$write_table} set wr_option='$html', {$sql_article} where wr_id='{$wr_id}'");

}

if ($wr_pass) {
        $sql = "update $write_table 
        set wr_password = '" . get_encrypt_string($wr_pass) . "'
        where wr_id = '$wr_id'";
        sql_query($sql);
}

	goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.$qstr);
