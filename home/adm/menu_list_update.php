<?php
$sub_menu = "100320";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_admin_token();

// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query($sql);

$group_code = null;
$primary_code = null;
$count = count($_POST['code']);

$site_path = G5_DATA_PATH.'/site';
$site_url = G5_DATA_URL.'/site';

@mkdir($site_path, G5_DIR_PERMISSION);
@chmod($site_path, G5_DIR_PERMISSION);

for ($i=0; $i<$count; $i++)
{
$img_link = $_POST['me_img'][$i];
if ($_FILES['me_img_file']['name'][$i]) {
    if (!preg_match("/\.(gif|jpg|png)$/i",$_FILES['me_img_file']['name'][$i])) {
        alert("이미지가 gif, jpg, png 파일이 아닙니다.");
    } else {
		// 확장자 따기
		$exp = explode(".", $_FILES['me_img_file']['name'][$i]);
		$exp = $exp[count($exp)-1]; 
 
		$image_name = time().$i.".".$exp;  
		upload_file($_FILES['me_img_file']['tmp_name'][$i], $image_name, $site_path);
		$img_link = $site_url."/".$image_name;
	}
}
$img_link2 = $_POST['me_img2'][$i];
if ($_FILES['me_img2_file']['name'][$i]) {
    if (!preg_match("/\.(gif|jpg|png)$/i",$_FILES['me_img2_file']['name'][$i])) {
        alert("이미지가 gif, jpg, png 파일이 아닙니다.");
    } else {
		// 확장자 따기
		$exp = explode(".", $_FILES['me_img2_file']['name'][$i]);
		$exp = $exp[count($exp)-1]; 
 
		$image_name = time().$i."_o.".$exp;  
		upload_file($_FILES['me_img2_file']['tmp_name'][$i], $image_name, $site_path);
		$img_link2 = $site_url."/".$image_name;
	}
}

    $_POST = array_map_deep('trim', $_POST);

    $code    = $_POST['code'][$i];
    $me_name = $_POST['me_name'][$i];
    $me_link = $_POST['me_link'][$i];

    if(!$code || !$me_name || !$me_link)
        continue;

    $sub_code = '';
    if($group_code == $code) {
        $sql = " select MAX(SUBSTRING(me_code,3,2)) as max_me_code
                    from {$g5['menu_table']}
                    where SUBSTRING(me_code,1,2) = '$primary_code' ";
        $row = sql_fetch($sql);

        $sub_code = base_convert($row['max_me_code'], 36, 10);
        $sub_code += 36;
        $sub_code = base_convert($sub_code, 10, 36);

        $me_code = $primary_code.$sub_code;
    } else {
        $sql = " select MAX(SUBSTRING(me_code,1,2)) as max_me_code
                    from {$g5['menu_table']}
                    where LENGTH(me_code) = '2' ";
        $row = sql_fetch($sql);

        $me_code = base_convert($row['max_me_code'], 36, 10);
        $me_code += 36;
        $me_code = base_convert($me_code, 10, 36);

        $group_code = $code;
        $primary_code = $me_code;
    }

    // 메뉴 등록
    $sql = " insert into {$g5['menu_table']}
                set me_code         = '$me_code',
                    me_name         = '$me_name',
                    me_link         = '$me_link',
                    me_img			= '{$img_link}',
                    me_img2			= '{$img_link2}',
                    me_target       = '{$_POST['me_target'][$i]}',
                    me_order        = '{$_POST['me_order'][$i]}',
                    me_use          = '{$_POST['me_use'][$i]}',
                    me_mobile_use   = '{$_POST['me_use'][$i]}',
                    me_level		= '{$_POST['me_level'][$i]}' ";
    sql_query($sql);
}

goto_url('./menu_list.php');
?>
