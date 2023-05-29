<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// Set ch output
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

$ch = array(
    "ch_name" => $write['wr_subject'],
    "summary" => $write['wr_content'],
    "source" => $wr_option1,
    "theme" => $wr_option2,
    "article" => array(),
    "status" => array(),
    "status_bar" => array(),
    "detail" => array(),
);

function setChStatus ($ch, $option_name, $option_value) {
    foreach($option_name as $key => $name){
        if (!isset($option_value[$key])) continue; // Skip undefined var
        if (!$option_value[$key]) continue; // Skip empty status
        // If value exists
        $name = stripslashes($name);
        $value = $option_value[$key];
        if ($key === "er") { // 침식률 처리 (2023-01-16)
            $ch['status_bar'][$key] = array(
                "name" => $name,
                "value" => $value,
                "max" => 100,
                "percent" => $value / 100 * 100,
            );
        }
        else {
            $ch['status'][$key] = array(
                "name" => $name,
                "value" => $value,
            );
        }
    }
    return $ch;
}

function setChArticle ($ch, $option_name, $option_value) {
    foreach ($option_name as $key => $name) {
        if (!isset($option_value[$key])) continue; // Skip undefined var
        if($option_value[$key]) { // If value exists
            $value = $option_value[$key];
            // PHP 5.2 대응 추가 후 수정 (2022-12-12)
            $value = str_replace("\\n", "\n", $value);
            $value = str_replace("\\r", "\r", $value);
            $value = str_replace("\\t", "\t", $value);
            $value = stripslashes($value);
            $value = nl2br($value);
            $ch['article'][$key] = $value;
        }
    }
    return $ch;
}

// Set body
$check_body = $ch['source']['body'];

$body_cnt = sql_fetch("select count(*) as cnt from {$g5['character_body_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}'");
$body_cnt = $body_cnt['cnt'];
if($body_cnt > 0) {
    $check_bodys = sql_fetch("select bd_url from {$g5['character_body_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bd_use = '1'");
    $check_body = $check_bodys['bd_url'] ? $check_bodys['bd_url'] : $check_body;
}
$body_className = ($check_body & $ch['theme']['color']) ? "ch-shadow-point" : "";

// Set global articles
foreach ($wr_option3 as $key => $value) {
    $value = stripslashes($value);
    $ch['article'][$key] = $value;
}
$ch = setChArticle ($ch, $wr_option5_name['global'], $wr_option5);

// Set type articles
$ch_profile_type = $ch['theme']['type'];
if (isset($wr_option4_name[$ch_profile_type])) { // Type에 맞는 option이 지정되어 있는 경우
    $ch = setChArticle ($ch, $wr_option4_name[$ch_profile_type], $wr_option4);
}
if (isset($wr_option5_name[$ch_profile_type])) { // Type에 맞는 option이 지정되어 있는 경우
    $ch = setChArticle ($ch, $wr_option5_name[$ch_profile_type], $wr_option5);
}
if (isset($wr_option6_name[$ch_profile_type])) { // Type에 맞는 option이 지정되어 있는 경우
    if ($ch_profile_type == "basic") {
        $option_name = $write['wr_6'];
    } else {
        $option_name = $wr_option6_name[$ch_profile_type];
    }
    $option_value = $wr_option6[$ch_profile_type];
    $ch = setChStatus($ch, $option_name, $option_value);
}

/******************************/
/******* Load Components ******/
/******************************/

/******* Basic Components ******/
$ch_subcontent_basic = "";
$ch_subcontent_basic_long = "";
foreach ($wr_option3_name as $key => $name) { // 기본 정보
    $ch_subcontent_basic .= article_subcontent_listitem($ch, $key, $name);
}
if (is_array($write['wr_3'])) {
    foreach ($write['wr_3'] as $key => $name) { // 인적사항 입력 필드 추가 (2022-12-12)
        $ch_subcontent_basic .= article_subcontent_listitem($ch, $key, $name);
    }
}
if ($wr_option5_name[$ch_profile_type]) { // Type에 맞는 option이 지정되어 있는 경우
    $option_name = $wr_option5_name[$ch_profile_type];
    foreach ($option_name as $key => $name) {
        $ch_subcontent_basic_long .= article_subcontent_listitem($ch, $key, $name);
    }
}

/******* Point Components ******/
$ch_subcontent_point = "";
$ch_subcontent_backstory = "";
if (isset($wr_option4_name[$ch_profile_type])) { // Type에 맞는 option이 지정되어 있는 경우
    $option_name = $wr_option4_name[$ch_profile_type];
    foreach ($option_name as $key => $name) {
        if ($ch_profile_type == "coc") $ch_subcontent_backstory .= article_subcontent_textarea($ch, $key, $name);
        else $ch_subcontent_point .= article_subcontent_listitem($ch, $key, $name);
    }
}

/******* Etc Components ******/
$ch_subcontent_etc = "";
foreach ($wr_option5_name['global'] as $key => $name) { // 기타 사항
    $ch_subcontent_etc .= article_subcontent_listitem($ch, $key, $name);
}

// 내용 처리
$ch_detail_keys = array("wr_7","wr_8","wr_9","wr_10");
foreach ($ch_detail_keys as $key) {
    if ($write[$key]['data']) {
        $detail = array(
            "name" => stripslashes($write[$key]['data']),
            "type" => $write[$key]['type'],
            "value" => $write[$key.'_txt'],
        );
        $ch['detail'][$key] = $detail;
    }
}
?>
<?php
    if($ch["theme"]["song"]) { // Add bgm if needed (2022-12-11 Update: Single page handling)
        $rootUrl = G5_URL."/bgm.php";
        $selfUrl = "{$board_skin_url}/_bgm.php";
?>
<script>
    $(document).ready(function () {
        const root = window.parent.document.getElementById('bgm_frame');
        // Split global BGM & character BGM (2022-12-13)
        if (root) { // If global BGM is on, turn it off
            if (!root.src.endsWith("/bgm.php")) { root.src = "<?php echo $rootUrl; ?>"; }
        }
        const newBgmBox = document.createElement("div");
        newBgmBox.setAttribute("id", "ch_bgm_box");
        newBgmBox.innerHTML = '<iframe src="<?php echo $selfUrl; ?>" name="ch_bgm_frame" id="ch_bgm_frame" border="0" frameborder="0" marginheight="0" marginwidth="0" topmargin="0" scrolling="no" allowTransparency="true"></iframe>';
        const targetDOM = document.getElementsByClassName("view__mask")[0];
        targetDOM.parentNode.insertBefore(newBgmBox, targetDOM);
    });
</script>
<?php } ?>