<?php
$sub_menu = "100330";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_admin_token();

// 폰트 파일
$font_path = G5_DATA_PATH.'/font';
$font_url = G5_DATA_URL.'/font';

@mkdir($font_path, G5_DIR_PERMISSION);
@chmod($font_path, G5_DIR_PERMISSION);


// 이전 폰트정보 삭제
$sql = " delete from {$g5['font_table']} ";
sql_query($sql);

$count = count($_POST['ft_type']);

$group_name = '';

$group_num = 0;
$ft_num = 0;
$ft_code = '';

$order = 0;

$_POST = array_map_deep('trim', $_POST);
for($i=0; $i<$count; $i++){
    // group & code
    if($_POST['ft_type'][$i] == 'group') {
        $group_name = $_POST['ft_group'][$i];
        $order = $_POST['ft_order'][$i];
        $group_num++;
        $ft_num = 0;
    }
    $ft_code = ($group_num>9 ? ''.$group_num : '0'.$group_num);
    $ft_code .= ($ft_num>9 ? ''.$ft_num : '0'.$ft_num);

    // file
    $file_link = $_POST['ft_file_2'][$i];
    $exp = '';
    $fname = '';
    $fweight = '';
    $fstyle = '';
    if ($_POST['ft_option'][$i]=='file' && $_FILES['ft_file_1']['name'][$i]) {
        if (!preg_match("/\.(woff|otf|ttf)$/i",$_FILES['ft_file_1']['name'][$i])) {
            alert("woff, otf, ttf 파일이 아닙니다.");
        } else {
            // 확장자 따기
            $uploadnames = explode(".", $_FILES['ft_file_1']['name'][$i]);
            $fname = $uploadnames[0];
            $exp   = $uploadnames[count($uploadnames)-1]; 
    
            $fname = time()."_".$fname.".".$exp;
            upload_file($_FILES['ft_file_1']['tmp_name'][$i], $fname, $font_path);
            $file_link = $font_url."/".$fname;

            $fweight = $_POST['ft_weight'][$i];
            $fstyle = $_POST['ft_style'][$i];
        }
    }else if($file_link) {
        $uploadnames = explode(".", $file_link);
        $exp   = $uploadnames[count($uploadnames)-1]; 
        $fweight = $_POST['ft_weight'][$i];
        $fstyle = $_POST['ft_style'][$i];
    }

    $sql = "
    INSERT INTO {$g5['font_table']}
    SET ft_type     = '{$_POST['ft_type'][$i]}',
        ft_code     = '{$ft_code}',
        ft_group    = '{$group_name}',
        ft_name     = '{$_POST['ft_name'][$i]}',
        ft_option   = '{$_POST['ft_option'][$i]}',
        ft_src      = '{$_POST['ft_src'][$i]}',
        ft_file     = '{$file_link}',
        ft_exp      = '{$exp}',
        ft_weight   = '{$fweight}',
        ft_style    = '{$fstyle}',
        ft_order    = '{$order}';
    ";
    $ft_num++;
    sql_query($sql);
}

goto_url('./font_list.php');
?>
