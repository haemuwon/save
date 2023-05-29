<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once($board_skin_path."/_setting.php");
include_once($board_skin_path."/_setting.option.php");

$sql_article = " wr_ing = '{$wr_ing}'
	, wr_7_txt = '{$wr_7_txt}'
	, wr_8_txt = '{$wr_8_txt}'
	, wr_9_txt = '{$wr_9_txt}'
	, wr_10_txt = '{$wr_10_txt}'";

if($type == "pair") {

	$sql_article .= " , wr_type = '{$type}'";

	// 이미지 등록
	// -- 배경
	if($wr_1 != $wr_1_prev && strpos($wr_1_prev, "{$bo_table}/pair_{$wr_id}_")) {
		// wr_1 값이 빈 값이 아니고 wr_1이 이전에 직접 등록한 데이터일 경우
		$prev_file_path = str_replace(G5_URL, G5_PATH, $wr_1_prev);
		@unlink($prev_file_path);
	}
	if($_FILES['wr_1_file']['name']) {
		// 확장자 따기
		$exp = explode(".", $_FILES['wr_1_file']['name']);
		$exp = $exp[count($exp)-1];

		$image_name = "pair_{$wr_id}_".time().".".$exp;
		upload_file($_FILES['wr_1_file']['tmp_name'], $image_name, $character_image_path);
		$wr_1 = $character_image_url."/".$image_name;
	}
	$sql_article .= " , wr_1 = '{$wr_1}'";

} else {
	// -- DX3
	$wr_breed = "퓨어";
	$wr_option4_syndrome = "";
	$wr_syndrome_cnt = 0;
	foreach(array($wr_option4_syndrome1, $wr_option4_syndrome2, $wr_option4_syndrome3) as $s) {
		if ($s != "") { // 값이 공백이 아닌 경우
			$wr_syndrome_cnt++;
			if ($wr_syndrome_cnt == 2) $wr_option4_syndrome .= "×";
			if ($wr_syndrome_cnt == 3) $wr_option4_syndrome .= "+";
			$wr_option4_syndrome .= $s;
		}
	}
	if ($wr_syndrome_cnt == 2) $wr_breed = "크로스";
	if ($wr_syndrome_cnt == 3) $wr_breed = "트라이";
	if ($wr_syndrome_cnt > 0) $wr_option4_syndrome .= " {$wr_breed} 브리드";

	// -- MGLG
	$wr_option4_magician = "{$wr_option4_history}";
	if ($wr_option4_institute != "") $wr_option4_magician .= " · {$wr_option4_institute}";

	// 두상 및 전신 저장 처리
	function register_ch_image ($image_input, $image_input_prev, $type, $name_header, $bo_table, $wr_id, $character_image_path, $character_image_url) {
		// 이미지 등록
		if($image_input != $image_input_prev && strpos($image_input_prev, "{$bo_table}/{$type}_{$wr_id}_")) {
			// {$name_header.$type} 값이 빈 값이 아니고 wr_option1_thumb이 이전에 직접 등록한 데이터일 경우
			$prev_file_path = str_replace(G5_URL, G5_PATH, $image_input_prev);
			@unlink($prev_file_path);
		}
		$fname = "{$name_header}{$type}_file";
		if($_FILES[$fname]['name']) {
			// 확장자 따기
			$exp = explode(".", $_FILES[$fname]['name']);
			$exp = $exp[count($exp)-1];
			$time = time();
			$image_name = "{$type}_{$wr_id}_{$time}.{$exp}";
			upload_file($_FILES[$fname]['tmp_name'], $image_name, $character_image_path);
			$image_input = "{$character_image_url}/{$image_name}";
		}
		return $image_input;
	}
	// Loop function
	$wr_option1 = array();
	$name_header = "wr_option1_";
	foreach(array("thumb", "head", "body", "emblem", "mini") as $input) {
		$image_input = ${$name_header.$input};
		$image_input_prev = ${$name_header.$input."_prev"};
		$wr_option1[$input] = register_ch_image($image_input, $image_input_prev, $input, $name_header, $bo_table, $wr_id, $character_image_path, $character_image_url);
	}
	/* Chat plug-in 대응 (2022-12-19) */
	$wr_1 = $wr_option1['thumb'];
	$wr_1_txt = json_encode($wr_option1);

	/* Extended fields */
	$wr_option2 = array();
	foreach(array("type", "color", "font", "mode", "bg", "song") as $input) {
		$wr_option2[$input] = ${"wr_option2_".$input};
	}
	/* Custom button bar (2022-12-25) */
	for ($i=1; $i<6; $i++) {
		$key = "button{$i}";
		$wr_option2[$key."_name"] = ${"wr_option2_".$key."_name"};
		$wr_option2[$key."_symbol"] = ${"wr_option2_".$key."_symbol"};
		$wr_option2[$key."_url"] = ${"wr_option2_".$key."_url"};
		$wr_option2[$key."_type"] = ${"wr_option2_".$key."_type"};
	}
	$wr_2_txt = json_encode($wr_option2);

	$wr_name_3 = array();
	foreach($wr_option3_name_more as $input => $value) {
		$input_value = ${"wr_3_".$input};
		if ($input_value) { $wr_name_3[$input] = htmlentities($input_value); }
	}
	$wr_3 = json_encode($wr_name_3);

	$wr_option3 = array();
	foreach(array("no", "phrase", "line", "name_native", "name_eng", "codename", "codename_rubi") as $input) {
		$wr_value = htmlentities(${"wr_option3_".$input});
		$wr_option3[$input] = $wr_value;
	}
	foreach($wr_option3_name as $input => $value) {
		$wr_value = htmlentities(${"wr_option3_".$input});
		$wr_option3[$input] = $wr_value;
	}
	foreach($wr_option3_name_more as $input => $value) {
		$wr_value = htmlentities(${"wr_option3_".$input});
		$wr_option3[$input] = $wr_value;
	}
	$wr_3_txt = json_encode($wr_option3);

	$wr_option4 = array();
	foreach(array("syndrome1", "syndrome2", "syndrome3",) as $input) {
		$wr_value = ${"wr_option4_".$input};
		$wr_option4[$input] = htmlentities($wr_value);
	}
	foreach(array("history", "institute") as $input) {
		$wr_value = ${"wr_option4_".$input};
		$wr_option4[$input] = htmlentities($wr_value);
	}
	foreach($wr_option4_name as $type => $array) {
		foreach($array as $input => $value) {
			$desc = ${"wr_option4_".$input};
			$desc = htmlentities($desc);
			$conv_desc = str_replace("\n", "\\n", $desc);
			$conv_desc = str_replace("\r", "\\r", $conv_desc);
			$conv_desc = str_replace("\t", "\\t", $conv_desc);
			$wr_option4[$input] = $conv_desc;
		}
	}
	$wr_4_txt = json_encode($wr_option4);

	$wr_option5 = array();
	foreach($wr_option5_name as $type => $array) {
		foreach($array as $input => $value) {
			$wr_value = ${"wr_option5_".$input};
			$wr_option5[$input] = htmlentities($wr_value);
		}
	}
	$wr_5_txt = json_encode($wr_option5);

	$wr_name_6 = array();
	foreach($wr_option6_name['basic'] as $input => $value) {
		$wr_value = ${"wr_6_".$input};
		$wr_name_6[$input] = htmlentities($wr_value);
	}
	$wr_6 = json_encode($wr_name_6);

	$wr_option6 = array();
	foreach($wr_option6_name as $type => $array) {
		$wr_option6[$type] = array();
		foreach($array as $input => $value) {
			$wr_value = ${"wr_option6_".$type."_".$input};
			$wr_option6[$type][$input] = htmlentities($wr_value);
		}
	}
	$wr_6_txt = json_encode($wr_option6);

	// PHP 5.2 대응 (2022-12-12)
	$wr_1_txt = str_replace("\\", "\\\\", $wr_1_txt);
	$wr_2_txt = str_replace("\\", "\\\\", $wr_2_txt);
	// $wr_3_txt = str_replace("\\", "\\\\", $wr_3_txt);
	// $wr_4_txt = str_replace("\\", "\\\\", $wr_4_txt);
	// $wr_5_txt = str_replace("\\", "\\\\", $wr_5_txt);
	// $wr_6_txt = str_replace("\\", "\\\\", $wr_6_txt);
	// $wr_3 = str_replace("\\", "\\\\", $wr_3);
	// $wr_6 = str_replace("\\", "\\\\", $wr_6);
	// $wr_1_txt = addslashes($wr_1_txt);
	// $wr_2_txt = addslashes($wr_2_txt);
	$wr_3_txt = addslashes($wr_3_txt);
	$wr_4_txt = addslashes($wr_4_txt);
	$wr_5_txt = addslashes($wr_5_txt);
	$wr_6_txt = addslashes($wr_6_txt);
	$wr_3 = addslashes($wr_3);
	$wr_6 = addslashes($wr_6);

	$sql_article .= " , wr_1_txt = '{$wr_1_txt}'
	, wr_2_txt = '{$wr_2_txt}'
	, wr_3_txt = '{$wr_3_txt}'
	, wr_4_txt = '{$wr_4_txt}'
	, wr_5_txt = '{$wr_5_txt}'
	, wr_6_txt = '{$wr_6_txt}'
	, wr_1 = '{$wr_1}'
	, wr_3 = '{$wr_3}'
	, wr_6 = '{$wr_6}'";

	/* Text fields */
	$wr_7 = json_encode(array("type" => $wr_7_type, "data" => $wr_7));
	$wr_8 = json_encode(array("type" => $wr_8_type, "data" => $wr_8));
	$wr_9 = json_encode(array("type" => $wr_9_type, "data" => $wr_9));
	$wr_10 = json_encode(array("type" => $wr_10_type, "data" => $wr_10));

	// PHP 5.2 대응 (2022-12-12)
	$wr_7 = addslashes($wr_7);
	$wr_8 = addslashes($wr_8);
	$wr_9 = addslashes($wr_9);
	$wr_10 = addslashes($wr_10);

	$sql_article .= ", wr_7 = '{$wr_7}',
	wr_8 = '{$wr_8}',
	wr_9 = '{$wr_9}',
	wr_10 = '{$wr_10}'";
}

$sql = " update {$write_table}
			set {$sql_article}
		where wr_id = '{$wr_id}' ";
sql_query($sql);

?>
