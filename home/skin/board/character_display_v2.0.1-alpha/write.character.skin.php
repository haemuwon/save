<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">', 0);

if($w == "") {
	$wr_option2 = array(
		"color" => "#000000",
		"type" => "basic",
		"mode" => "light",
		"font" => "gothic",
	);
	$wr_option3 = array();
	$wr_option4 = array();
	$wr_option5 = array();
	$wr_option6 = array();
	$write['wr_3'] = $wr_option3_name_more;
	$write['wr_6'] = $wr_option6_name['basic'];
	$write['wr_7']['data'] = "프로필";
	$write['wr_7']['type'] = "text";
	$write['wr_8']['data'] = "세션 이력";
	$write['wr_8']['type'] = "list";
	$write['wr_9']['data'] = "";
	$write['wr_9']['type'] = "";
	$write['wr_10']['data'] = "";
	$write['wr_10']['type'] = "";
} else { // 수정인 경우
	/* Extended fields */
	$wr_option1 = json_decode($write['wr_1_txt'], true);
	$wr_option2 = json_decode($write['wr_2_txt'], true);
	$wr_option3 = json_decode($write['wr_3_txt'], true);
	$wr_option4 = json_decode($write['wr_4_txt'], true);
	$wr_option5 = json_decode($write['wr_5_txt'], true);
	$wr_option6 = json_decode($write['wr_6_txt'], true);
	$write['wr_3'] = json_decode($write['wr_3'], true);
	$write['wr_6'] = json_decode($write['wr_6'], true);
	$write['wr_7'] = json_decode($write['wr_7'], true);
	$write['wr_8'] = json_decode($write['wr_8'], true);
	$write['wr_9'] = json_decode($write['wr_9'], true);
	$write['wr_10'] = json_decode($write['wr_10'], true);
}

function decode_wr_option4 ($value) {
	// PHP 5.2 대응 추가 후 수정 (2022-12-12)
	$value = str_replace("\\n", "\n", $value);
	$value = str_replace("\\r", "\r", $value);
	$value = str_replace("\\t", "\t", $value);
	$value = stripslashes($value);
	return $value;
}

?>
<tr>
	<th scope="row">정렬순서</th>
	<td>
		<input type="text" name="wr_ing" value="<?php echo isset($write['wr_ing']) ? $write['wr_ing'] : ""; ?>" class="frm_input" size="5" maxlength="255">
	</td>
</tr>
</tbody>
</table>
<br />
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<tr>
	<th scope="row" rowspan=5>테마 설정</th>
	<td>컬러</td>
	<td>
		<input type="color" name="wr_option2_color" value="<?php echo $wr_option2['color']; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>폰트</td>
	<td>
		<select name="wr_option2_font">
			<option value="gothic" <?php if($wr_option2['font'] == "gothic") echo "selected"; ?>>고딕</option>
			<option value="serif" <?php if($wr_option2['font'] == "serif") echo "selected"; ?>>세리프</option>
		</select>
	</td>
</tr>
<tr>
	<td>모드</td>
	<td>
		<select name="wr_option2_mode">
			<option value="light" <?php if($wr_option2['mode'] == "light") echo "selected"; ?>>Light</option>
			<option value="dark" <?php if($wr_option2['mode'] == "dark") echo "selected"; ?>>Dark</option>
			<option value="color" <?php if($wr_option2['mode'] == "color") echo "selected"; ?>>Color</option>
			<option value="home" <?php if($wr_option2['mode'] == "home") echo "selected"; ?>>Home</option>
			<option value="home-light" <?php if($wr_option2['mode'] == "home-light") echo "selected"; ?>>Home (Light)</option>
			<option value="home-dark" <?php if($wr_option2['mode'] == "home-dark") echo "selected"; ?>>Home (Dark)</option>
		</select>
	</td>
</tr>
<tr>
	<td>배경 이미지</td>
	<td>
		<span class="frm_info">배경에 사용될 이미지 또는 동영상 외부 링크를 입력해주세요.</span>
		<input type="text" name="wr_option2_bg" value="<?php echo isset($wr_option2['bg']) ? $wr_option2['bg'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>배경음악</td>
	<td>
		<span class="frm_info">캐릭터 테마 곡을 삽입할 수 있습니다. 유튜브 곡 고유코드로 적어주세요.</span>
		<input type="text" name="wr_option2_song" value="<?php echo isset($wr_option2['song']) ? $wr_option2['song'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
</tbody>
</table>
<br />
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col style="width: 100px;" />
	<col style="width: 100px;" />
	<col style="width: 140px;" />
	<col />
</colgroup>
<tbody>
<tr>
	<th scope="row" rowspan=5>버튼 설정</th>
	<? for ($i=1; $i<6; $i++) {
		$key = "button{$i}";
		$name = isset($wr_option2[$key."_name"]) ? $wr_option2[$key."_name"] : "";
		$symbol = isset($wr_option2[$key."_symbol"]) ? $wr_option2[$key."_symbol"] : "";
		$url = isset($wr_option2[$key."_url"]) ? $wr_option2[$key."_url"] : "";
		$input_symbol = "<input type='text' name='wr_option2_{$key}_symbol' value='{$symbol}' placeholder='아이콘' class='frm_input' size='30' maxlength='255'>";
		$input_name = "<input type='text' name='wr_option2_{$key}_name' value='{$name}' placeholder='이름' class='frm_input' size='30' maxlength='255'>";
		
		$options = "";
		foreach(array(""=>"직접입력", "keywordchat"=>"키워드 채팅", "myroom"=>"마이룸") as $value => $type) {
			$selected = (isset($wr_option2[$key."_type"]) && $wr_option2[$key.'_type'] == $value) ? "selected" : "";
			$options .= "<option value='{$value}' {$selected}>{$type}</option>";
		}
		$selector = "<select name='wr_option2_{$key}_type'>{$options}</select>";
		$input_url = "<input type='url' placeholder='자유입력 URL' name='wr_option2_{$key}_url' value='{$url}' class='frm_input full'>";

		echo "<td><i class='material-symbols-outlined'>{$symbol}</i></td>";
		echo "<td>{$input_symbol}</td>";
		echo "<td>{$input_name}</td>";
		echo "<td>{$selector}</td>";
		echo "<td class='form_option_url'>{$input_url}</td>";
		echo "</tr><tr>";

		unset($key, $name, $symbol, $url, $options);
	}?>
</tr>
</tbody>
</table>
<br />
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<tr>
	<th rowspan="2" scope="row">두상</th>
	<td>외부경로</td>
	<td>
		<input type="hidden" name="wr_option1_thumb_prev" value="<?php echo isset($wr_option1['thumb']) ? $wr_option1['thumb'] : ""; ?>" />
		<input type="text" name="wr_option1_thumb" value="<?php echo isset($wr_option1['thumb']) ? $wr_option1['thumb'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>업로드</td>
	<td>
		<input type="file" name="wr_option1_thumb_file" title="파일첨부 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file frm_input">
	</td>
</tr>
<tr>
	<th rowspan="2" scope="row">흉상</th>
	<td>외부경로</td>
	<td>
		<input type="hidden" name="wr_option1_head_prev" value="<?php echo isset($wr_option1['head']) ? $wr_option1['head'] : ""; ?>" />
		<input type="text" name="wr_option1_head" value="<?php echo isset($wr_option1['head']) ? $wr_option1['head'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>업로드</td>
	<td>
		<input type="file" name="wr_option1_head_file" title="파일첨부 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file frm_input">
	</td>
</tr>
<tr>
	<th rowspan="2" scope="row">전신</th>
	<td>외부경로</td>
	<td>
		<input type="hidden" name="wr_option1_body_prev" value="<?php echo isset($wr_option1['body']) ? $wr_option1['body'] : ""; ?>" />
		<input type="text" name="wr_option1_body" value="<?php echo isset($wr_option1['body']) ? $wr_option1['body'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>업로드</td>
	<td>
		<input type="file" name="wr_option1_body_file" title="파일첨부 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file frm_input">
	</td>
</tr>
<tr>
	<th rowspan="2" scope="row">로고</th>
	<td>외부경로</td>
	<td>
		<input type="hidden" name="wr_option1_emblem_prev" value="<?php echo isset($wr_option1['emblem']) ? $wr_option1['emblem'] : ""; ?>" />
		<input type="text" name="wr_option1_emblem" value="<?php echo isset($wr_option1['emblem']) ? $wr_option1['emblem'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>업로드</td>
	<td>
		<input type="file" name="wr_option1_emblem_file" title="파일첨부 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file frm_input">
	</td>
</tr>
<tr>
	<th rowspan="2" scope="row">미니</th>
	<td>외부경로</td>
	<td>
		<input type="hidden" name="wr_option1_mini_prev" value="<?php echo isset($wr_option1['mini']) ? $wr_option1['mini'] : ""; ?>" />
		<input type="text" name="wr_option1_mini" value="<?php echo isset($wr_option1['mini']) ? $wr_option1['mini'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>업로드</td>
	<td>
		<input type="file" name="wr_option1_mini_file" title="파일첨부 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file frm_input">
	</td>
</tr>
</tbody>
</table>
<br />
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<?/* Option 3 */?>
<? if (isset($wr_option3)) {
	foreach ($wr_option3 as $key => $value) {
		$wr_option3[$key] = stripslashes($value);
	}
} ?>
<tr>
	<th scope="row">No.</th>
	<td colspan=2>
		<input type="text" name="wr_option3_no" value="<?php echo isset($wr_option3['no']) ? $wr_option3['no'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<th scope="row" rowspan=3>캐릭터 이름</th>
	<td>-</td>
	<td>
		<input type="text" name="wr_subject" value="<?php echo $subject; ?>" required class="frm_input required" size="50" maxlength="255">
	</td>
</tr>
<tr>
	<td>원어</td>
	<td>
		<input type="text" name="wr_option3_name_native" value="<?php echo isset($wr_option3['name_native']) ? $wr_option3['name_native'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>영어</td>
	<td>
		<input type="text" name="wr_option3_name_eng" value="<?php echo isset($wr_option3['name_eng']) ? $wr_option3['name_eng'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<th scope="row" rowspan=2>코드네임</th>
	<td>-</td>
	<td>
		<input type="text" name="wr_option3_codename" value="<?php echo isset($wr_option3['codename']) ? $wr_option3['codename'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<td>루비문자</td>
	<td>
		<input type="text" name="wr_option3_codename_rubi" value="<?php echo isset($wr_option3['codename_rubi']) ? $wr_option3['codename_rubi'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<th scope="row">캐치프레이즈</th>
	<td colspan=2>
		<input type="text" name="wr_option3_phrase" value="<?php echo isset($wr_option3['phrase']) ? $wr_option3['phrase'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<th scope="row">대사</th>
	<td colspan=2>
		<input type="text" name="wr_option3_line" value="<?php echo isset($wr_option3['line']) ? $wr_option3['line'] : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr>
	<th scope="row" rowspan=<?php echo count($wr_option3_name); ?>>인적사항</th>
	<?php foreach ($wr_option3_name as $key => $name) {
		$value = isset($wr_option3[$key]) ? $wr_option3[$key] : "";
		// $value = stripslashes($value);
		$input = "<input type=\"text\" name=\"wr_option3_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"12\" maxlength=\"255\">";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr>";
		unset($value); unset($input);
	} ?>
</tr>
<? // 인적사항 입력 필드 추가 (2022-12-12) ?>
<tr>
	<th scope="row" rowspan=<?php echo count($wr_option3_name_more); ?>>인적사항 (추가 입력)</th>
	<?php foreach($wr_option3_name_more as $key => $empty) {
		$name = isset($write['wr_3'][$key]) ? stripslashes($write['wr_3'][$key]) : "";
		$name = "<input type=\"text\" name=\"wr_3_{$key}\" value=\"{$name}\" class=\"frm_input\" />";
		$value = isset($wr_option3[$key]) ? $wr_option3[$key] : "";
		// $value = stripslashes($value);
		$input = "<input type=\"text\" name=\"wr_option3_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"12\" maxlength=\"255\">";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr>";
		unset($value); unset($input);
	} ?>
</tr>
<?/* Option 3 End */?>
<?/* Option 4 */?>
</tbody>
</table>
<br>
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<?php // Global Option 5 ?>
<tr>
	<th scope="row" rowspan=<?php echo count($wr_option5_name['global']); ?>>공통</th>
	<?php foreach ($wr_option5_name['global'] as $key => $name) {
		$value = isset($wr_option5[$key]) ? $wr_option5[$key] : "";
		$value = stripslashes($value);
		$input = "<input type=\"text\" name=\"wr_option5_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"50\" maxlength=\"255\">";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr>";
		unset($value); unset($input);
	} ?>
</tr>
<?php // Global Option 5 End ?>
<tr>
	<th scope="row">프로필 타입</th>
	<td colspan=2>
		<select id="form_input_select" name="wr_option2_type" onchange="form_change(this.value);">
			<option value="basic" <?php if($wr_option2['type'] == "basic") echo "selected"; ?>>기본</option>
			<option value="coc" <?php if($wr_option2['type'] == "coc") echo "selected"; ?>>COC</option>
			<option value="dx3" <?php if($wr_option2['type'] == "dx3") echo "selected"; ?>>DX3</option>
			<option value="insane" <?php if($wr_option2['type'] == "insane") echo "selected"; ?>>inSANe</option>
			<option value="mglg" <?php if($wr_option2['type'] == "mglg") echo "selected"; ?>>MGLG</option>
		</select>
	</td>
</tr>
<?php // Global ?>
<tr class="form_option basic coc">
	<th scope="row" rowspan=<?php echo count($wr_option5_name['basic']); ?>>기본</th>
	<?php $start = true; foreach ($wr_option5_name['basic'] as $key => $name) {
		$value = isset($wr_option5[$key]) ? $wr_option5[$key] : "";
		$value = stripslashes($value);
		$input = "<input type=\"text\" name=\"wr_option5_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"30\" maxlength=\"255\">";
		if (!$start) echo "<tr class='form_option basic coc'>";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr>";
		unset($value); unset($input); $start = false;
	} ?>
	<tr class="form_option basic insane">
		<th scope="row">요약</th>
		<td>키워드</td>
		<td>
			<input type="text" name="wr_option4_keyword" value="<?php echo isset($wr_option4['keyword']) ? stripslashes($wr_option4['keyword']) : ""; ?>" class="frm_input" size="50" maxlength="255">
		</td>
	</tr>
</tr>
<?php // Global End ?>
<?php
	// Custom fields
	include_once($board_skin_path."/write.character.skin.sheet.php");
?>
</tbody>
</table>
<?/* Option 6 */?>
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
	<col style="width: 80px;" />
	<col />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<?php foreach($wr_option6_name as $type => $array) {
	$i = 0;
	$rcnt = ceil(count($array)/3);
	foreach($array as $key => $name) {
		$i++; $th = ""; $tr = "";
		if ($i == 1) $th = "<th scope='row' rowspan={$rcnt}>스테이터스</th>"; // Write header
		if ($i % 3 == 1) $tr = "<tr class='form_option {$type}'>"; // Write row
		if ($type == "basic") {
			$name = isset($write['wr_6'][$key]) ? stripslashes($write['wr_6'][$key]) : "";
			$name = "<input type=\"text\" name=\"wr_6_{$key}\" value=\"{$name}\" class=\"frm_input\" />";
		}
		$value = isset($wr_option6[$type][$key]) ? $wr_option6[$type][$key] : "";
		$input = "<input type=\"number\" name=\"wr_option6_{$type}_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"12\" maxlength=\"11\">";
		// Write data
		echo ($tr=="" & $i!=1) ? "" : "</tr>";
		echo $tr;
		echo $th;
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
	}
} ?>
</tbody>
</table>
<br>
<table class="theme-form">
<colgroup>
	<col style="width: 100px;" />
	<col style="width: 80px;" />
	<col />
</colgroup>
<tbody>
<?/* Option 6 End */?>
<tr>
	<th scope="row">요약 정보</th>
	<td>-</td>
	<td>
		<textarea name="wr_content" placeholder=""><?php echo isset($write['wr_content']) ? $write['wr_content'] : ""; ?></textarea>
	</td>
</tr>
<tr>
	<th scope="row"><input type="text" name="wr_7" value="<?php echo isset($write['wr_7']['data']) ? stripslashes($write['wr_7']['data']) : ""; ?>" class="frm_input full" /></th>
	<td>
		<select name="wr_7_type">
			<option value="text" <?php if (isset($write['wr_7']['type']) && $write['wr_7']['type'] == "text") echo "selected"; ?>>텍스트</option>
			<option value="list" <?php if (isset($write['wr_7']['type']) && $write['wr_7']['type'] == "list") echo "selected"; ?>>목록</option>
		</select>
	</td>
	<td>
		<span class="frm_info">두 번 줄바꿈하면 문단/항목이 구분됩니다.</span>
		<span class="frm_info">스포일러 내용은 /*이렇게*/표기하면 가려집니다.</span>
		<textarea name="wr_7_txt"><?php echo isset($write['wr_7_txt']) ? $write['wr_7_txt'] : ""; ?></textarea>
	</td>
</tr>
<tr>
	<th scope="row"><input type="text" name="wr_8" value="<?php echo stripslashes($write['wr_8']['data']); ?>" class="frm_input full" /></th>
	<td>
		<select name="wr_8_type">
			<option value="text" <?php if($write['wr_8']['type'] == "text") echo "selected"; ?>>텍스트</option>
			<option value="list" <?php if($write['wr_8']['type'] == "list") echo "selected"; ?>>목록</option>
		</select>
	</td>
	<td>
		<textarea name="wr_8_txt"><?php echo isset($write['wr_8_txt']) ? $write['wr_8_txt'] : ""; ?></textarea>
	</td>
</tr>
<tr>
	<th scope="row"><input type="text" name="wr_9" value="<?php echo stripslashes($write['wr_9']['data']); ?>" class="frm_input full" /></th>
	<td>
		<select name="wr_9_type">
			<option value="text" <?php if($write['wr_9']['type'] == "text") echo "selected"; ?>>텍스트</option>
			<option value="list" <?php if($write['wr_9']['type'] == "list") echo "selected"; ?>>목록</option>
		</select>
	</td>
	<td>
		<textarea name="wr_9_txt"><?php echo isset($write['wr_9_txt']) ? $write['wr_9_txt'] : ""; ?></textarea>
	</td>
</tr>
<tr>
	<th scope="row"><input type="text" name="wr_10" value="<?php echo stripslashes($write['wr_10']['data']); ?>" class="frm_input full" /></th>
	<td>
		<select name="wr_10_type">
			<option value="text" <?php if($write['wr_10']['type'] == "text") echo "selected"; ?>>텍스트</option>
			<option value="list" <?php if($write['wr_10']['type'] == "list") echo "selected"; ?>>목록</option>
		</select>
	</td>
	<td>
		<textarea name="wr_10_txt"><?php echo isset($write['wr_10_txt']) ? $write['wr_10_txt'] : ""; ?></textarea>
	</td>
</tr>

<?php // Input controls ?>
<style>
	.form_option {display: none;}
	.form_option.on {display: table-row;}
</style>
<script>
	function form_change (value) {
		const className = `.form_option.${value}`;
		$(".form_option").removeClass("on");
		$(className).addClass("on");
	}
	$(function () {
		form_change("<?php echo $wr_option2['type'];?>");
	});
</script>