<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//-- COC
?>
<tr class="form_option coc">
	<th scope="row" rowspan=<?php echo count($wr_option4_name['coc']); ?>>COC</th>
	<?php foreach ($wr_option4_name['coc'] as $key => $name) {
		$value = isset($wr_option4[$key]) ? decode_wr_option4($wr_option4[$key]) : "";
		$input = "<textarea name='wr_option4_{$key}'>{$value}</textarea>";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr class='form_option coc'>";
		unset($value); unset($input);
	} ?>
</tr>

<?php
//-- Insane
$category = array("폭력", "정서", "지각", "기술", "지식", "괴이");
?>

<tr class="form_option insane">
	<th scope="row" rowspan=<?php echo count($wr_option5_name['insane']); ?>>inSANe</th>
	<?php foreach ($wr_option5_name['insane'] as $key => $name) {
		$value = isset($wr_option5[$key]) ? $wr_option5[$key] : "";
		$value = stripslashes($value);
		if ($key == "curious") { // 호기심일 경우
			$options = "";
			for($i=0; $i < count($category); $i++) {
				$selected = ($value == $category[$i]) ? "selected" : "";
				$options .= "<option value=\"{$category[$i]}\" {$selected}>{$category[$i]}</option>";
			}
			$input = "<select name=\"wr_option5_{$key}\">{$options}</select>";
		} else {
			$input = "<input type=\"text\" name=\"wr_option5_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"50\" maxlength=\"255\">";
		}
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr class='form_option insane'>";
		unset($value); unset($input);
	} ?>
</tr>

<?php
//-- DX3
$syndrome = array("", "엔젤 헤일로", "발로르", "블랙 독", "브람=스토커", "엑자일", "하누만", "키마이라", "노이만", "모르페우스", "오르쿠스", "샐러맨더", "솔라리스", "우로보로스");
?>

<tr class="form_option dx3">
	<th scope="row" rowspan=10>DX3</th>
	<td>D로이스</td>
	<td>
		<input type="text" name="wr_option4_dlois" value="<?php echo isset($wr_option4['dlois']) ? stripslashes($wr_option4['dlois']) : ""; ?>" class="frm_input" size="30" maxlength="255">
	</td>
</tr>
<tr class="form_option dx3">
	<td>신드롬 1</td>
	<td>
		<select name="wr_option4_syndrome1">
			<?
				for($i=0; $i < count($syndrome); $i++) { 
					$value = stripslashes($syndrome[$i]);
                    $selected = (isset($wr_option4['syndrome1']) && $wr_option4['syndrome1'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option dx3">
	<td>신드롬 2</td>
	<td>
		<select name="wr_option4_syndrome2">
			<?
				for($i=0; $i < count($syndrome); $i++) { 
					$value = stripslashes($syndrome[$i]);
                    $selected = (isset($wr_option4['syndrome2']) && $wr_option4['syndrome2'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option dx3">
	<td>신드롬 3</td>
	<td>
		<select name="wr_option4_syndrome3">
			<?
				for($i=0; $i < count($syndrome); $i++) { 
					$value = stripslashes($syndrome[$i]);
                    $selected = (isset($wr_option4['syndrome3']) &&  $wr_option4['syndrome3'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option dx3">
	<?php foreach ($wr_option4_name['dx3'] as $key => $name) {
		if ($name != "") { // 값이 비어있지 않은 경우에만
			$value = isset($wr_option4[$key]) ? $wr_option4[$key] : "";
			$value = stripslashes($value);
			$input = "<input type=\"text\" name=\"wr_option4_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"50\" maxlength=\"255\">";
			echo "<td>{$name}</td>";
			echo "<td>{$input}</td>";
			echo "</tr><tr class='form_option dx3'>";
			unset($value); unset($input);
		}
	} ?>
</tr>
<tr class="form_option dx3">
	<?php foreach ($wr_option5_name['dx3'] as $key => $name) {
		$value = isset($wr_option5[$key]) ? $wr_option5[$key] : "";
		$value = stripslashes($value);
		$input = "<input type=\"text\" name=\"wr_option5_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"50\" maxlength=\"255\">";
		echo "<td>{$name}</td>";
		echo "<td>{$input}</td>";
		echo "</tr><tr class='form_option dx3'>";
		unset($value); unset($input);
	} ?>
</tr>

<?php
//-- MGLG
$mglg_domain = array(
    "별",
    "짐승",
    "힘",
    "노래",
    "꿈",
    "어둠",
);
$mglg_phase = array(
    "제1계제: 신참 Neopyte",
    "제2계제: 이론가 Theoricus",
    "제3계제: 실천자 Practicus",
    "제4계제: 철학자 Philosophus",
    "제5계제: 달인 Adeptus",
    "제6계제: 마도사 Magus",
    "제7계제: 초월자 Ipsissimus"
);
$mglg_history = array(
    "서경",
    "사서",
    "서공",
    "방문자",
    "이단자",
    "외전",
    "Bookwatch",
    "Librian",
    "Artisan",
    "Guest",
    "Outsider",
    "Apocrypha",
);
$mglg_institute = array(
    "",
    "원탁",
    "학원",
    "천애",
    "엽귀",
    "아방궁",
    "문호",
    "Table of Contents",
    "Academy",
    "Horizon",
    "Cyclops",
    "Laboratory",
    "Portal",
);
?>

<tr class="form_option mglg">
	<th scope="row" rowspan=11>MGLG</th>
	<td>계제</td>
	<td>
		<select name="wr_option4_phase">
			<?
				for($i=0; $i < count($mglg_phase); $i++) { 
					$value = stripslashes($mglg_phase[$i]);
                    $selected = (isset($wr_option4['phase']) && $wr_option4['phase'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option mglg">
	<td>경력</td>
	<td>
		<select name="wr_option4_history">
			<?
				for($i=0; $i < count($mglg_history); $i++) { 
					$value = stripslashes($mglg_history[$i]);
                    $selected = (isset($wr_option4['history']) && $wr_option4['history'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option mglg">
	<td>기관</td>
	<td>
		<select name="wr_option4_institute">
			<?
				for($i=0; $i < count($mglg_institute); $i++) { 
					$value = stripslashes($mglg_institute[$i]);
                    $selected = (isset($wr_option4['institute']) && $wr_option4['institute'] == $value) ? "selected" : "";
					echo "<option value=\"{$value}\" {$selected}>{$value}</option>";
				}
			?>
		</select>
	</td>
</tr>
<tr class="form_option mglg">
	<?php foreach ($wr_option4_name['mglg'] as $key => $name) {
		if ($name != "") { // 값이 비어있지 않은 경우에만
			$value = isset($wr_option4[$key]) ? $wr_option4[$key] : "";
			$value = stripslashes($value);
			if ($key == "domain") { // 영역일 경우
				$options = "";
				for($i=0; $i < count($mglg_domain); $i++) {
					$selected = ($value == $mglg_domain[$i]) ? "selected" : "";
					$options .= "<option value=\"{$mglg_domain[$i]}\" {$selected}>{$mglg_domain[$i]}</option>";
				}
				$input = "<select name=\"wr_option4_{$key}\">{$options}</select>";
			} else {
				$input = "<input type=\"text\" name=\"wr_option4_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"30\" maxlength=\"255\">";
			}
			echo "<td>{$name}</td>";
			echo "<td>{$input}</td>";
			echo "</tr><tr class='form_option mglg'>";
			unset($value); unset($input);
		}
	} ?>
</tr>
<?php // MGLG Option 5 ?>
<tr class="form_option mglg">
	<?php foreach ($wr_option5_name['mglg'] as $key => $name) {
		if ($name != "") { // 값이 비어있지 않은 경우에만
			$value = isset($wr_option5[$key]) ? $wr_option5[$key] : "";
			$value = stripslashes($value);
            if ($key == "domain") { // 영역일 경우
                $options = "";
                for($i=0; $i < count($mglg_domain); $i++) {
                    $selected = ($value == $mglg_domain[$i]) ? "selected" : "";
                    $options .= "<option value=\"{$mglg_domain[$i]}\" {$selected}>{$mglg_domain[$i]}</option>";
                }
                $input = "<select name=\"wr_option5_{$key}\">{$options}</select>";
            } else {
                $input = "<input type=\"text\" name=\"wr_option5_{$key}\" value=\"{$value}\" class=\"frm_input\" size=\"30\" maxlength=\"255\">";
            }
			echo "<td>{$name}</td>";
			echo "<td>{$input}</td>";
			echo "</tr><tr class='form_option mglg'>";
			unset($value); unset($input);
		}
	} ?>
</tr>

<?php
// File end
?>