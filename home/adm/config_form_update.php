<?php
$sub_menu = "100100";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'w');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$mb = get_member($cf_admin);
if (!$mb['mb_id'])
    alert('최고관리자 회원아이디가 존재하지 않습니다.');

check_admin_token();

// 본인확인을 사용할 경우 아이핀, 휴대폰인증 중 하나는 선택되어야 함
if($_POST['cf_cert_use'] && !$_POST['cf_cert_ipin'] && !$_POST['cf_cert_hp'])
    alert('본인확인을 위해 아이핀 또는 휴대폰 본인학인 서비스를 하나이상 선택해 주십시오');

if(!$_POST['cf_cert_use']) {
    $_POST['cf_cert_ipin'] = '';
    $_POST['cf_cert_hp'] = '';
}

$site_style_path = G5_DATA_PATH."/site";
$site_style_url = G5_DATA_URL."/site";

@mkdir($site_style_path, G5_DIR_PERMISSION);
@chmod($site_style_path, G5_DIR_PERMISSION);

$cf_site_img = $_POST['cf_site_img'];


if ($_FILES['cf_favicon_file']['name']) {
	// 확장자 따기
	$exp = explode(".", $_FILES['cf_favicon_file']['name']);
	$exp = $exp[count($exp)-1];

	$image_name = "site_favicon.".$exp;
	upload_file($_FILES['cf_favicon_file']['tmp_name'], $image_name, $site_style_path);
	$cf_favicon = $site_style_url."/".$image_name;
}
// 이미지 등록 시, 이미지를 업로드한 뒤 - 해당 이미지 경로를 삽입
if ($_FILES['admin_icon_file']['name']) {
	$image_name = "ico_admin";
	upload_file($_FILES['admin_icon_file']['tmp_name'], $image_name, $site_style_path);
}


$sql = " update {$g5['config_table']}
            set cf_admin = '{$_POST['cf_admin']}',
                cf_admin_email = '{$_POST['cf_admin_email']}',
                cf_admin_email_name = '{$_POST['cf_admin_email_name']}',
                cf_add_script = '{$_POST['cf_add_script']}',
                cf_use_point = '{$_POST['cf_use_point']}',
                cf_point_term = '{$_POST['cf_point_term']}',
                cf_use_copy_log = '{$_POST['cf_use_copy_log']}',
                cf_use_email_certify = '{$_POST['cf_use_email_certify']}',
                cf_login_point = '{$_POST['cf_login_point']}',
                cf_cut_name = '{$_POST['cf_cut_name']}',
                cf_nick_modify = '{$_POST['cf_nick_modify']}',
                cf_new_skin = '{$_POST['cf_new_skin']}',
                cf_new_rows = '{$_POST['cf_new_rows']}',
                cf_search_skin = '{$_POST['cf_search_skin']}',
                cf_connect_skin = '{$_POST['cf_connect_skin']}',
                cf_faq_skin = '{$_POST['cf_faq_skin']}',
                cf_read_point = '{$_POST['cf_read_point']}',
                cf_write_point = '{$_POST['cf_write_point']}',
                cf_comment_point = '{$_POST['cf_comment_point']}',
                cf_download_point = '{$_POST['cf_download_point']}',
                cf_write_pages = '{$_POST['cf_write_pages']}',
                cf_mobile_pages = '{$_POST['cf_mobile_pages']}',
                cf_link_target = '{$_POST['cf_link_target']}',
                cf_delay_sec = '{$_POST['cf_delay_sec']}',
                cf_filter = '{$_POST['cf_filter']}',
                cf_possible_ip = '".trim($_POST['cf_possible_ip'])."',
                cf_intercept_ip = '".trim($_POST['cf_intercept_ip'])."',
                cf_analytics = '{$_POST['cf_analytics']}',
                cf_add_meta = '{$_POST['cf_add_meta']}',
                cf_member_skin = '{$_POST['cf_member_skin']}',
                cf_use_homepage = '{$_POST['cf_use_homepage']}',
                cf_req_homepage = '{$_POST['cf_req_homepage']}',
                cf_use_tel = '{$_POST['cf_use_tel']}',
                cf_req_tel = '{$_POST['cf_req_tel']}',
                cf_use_hp = '{$_POST['cf_use_hp']}',
                cf_req_hp = '{$_POST['cf_req_hp']}',
                cf_use_addr = '{$_POST['cf_use_addr']}',
                cf_req_addr = '{$_POST['cf_req_addr']}',
                cf_use_signature = '{$_POST['cf_use_signature']}',
                cf_req_signature = '{$_POST['cf_req_signature']}',
                cf_use_profile = '{$_POST['cf_use_profile']}',
                cf_req_profile = '{$_POST['cf_req_profile']}',
                cf_register_level = '{$_POST['cf_register_level']}',
                cf_register_point = '{$_POST['cf_register_point']}',
                cf_icon_level = '{$_POST['cf_icon_level']}',
                cf_use_recommend = '{$_POST['cf_use_recommend']}',
                cf_recommend_point = '{$_POST['cf_recommend_point']}',
                cf_leave_day = '{$_POST['cf_leave_day']}',
                cf_search_part = '{$_POST['cf_search_part']}',
                cf_email_use = '{$_POST['cf_email_use']}',
                cf_email_wr_super_admin = '{$_POST['cf_email_wr_super_admin']}',
                cf_email_wr_group_admin = '{$_POST['cf_email_wr_group_admin']}',
                cf_email_wr_board_admin = '{$_POST['cf_email_wr_board_admin']}',
                cf_email_wr_write = '{$_POST['cf_email_wr_write']}',
                cf_email_wr_comment_all = '{$_POST['cf_email_wr_comment_all']}',
                cf_email_mb_super_admin = '{$_POST['cf_email_mb_super_admin']}',
                cf_email_mb_member = '{$_POST['cf_email_mb_member']}',
                cf_email_po_super_admin = '{$_POST['cf_email_po_super_admin']}',
                cf_prohibit_id = '{$_POST['cf_prohibit_id']}',
                cf_prohibit_email = '{$_POST['cf_prohibit_email']}',
                cf_new_del = '{$_POST['cf_new_del']}',
                cf_visit_del = '{$_POST['cf_visit_del']}',
                cf_use_member_icon = '{$_POST['cf_use_member_icon']}',
                cf_member_icon_size = '{$_POST['cf_member_icon_size']}',
                cf_member_icon_width = '{$_POST['cf_member_icon_width']}',
                cf_member_icon_height = '{$_POST['cf_member_icon_height']}',
                cf_login_minutes = '{$_POST['cf_login_minutes']}',
                cf_image_extension = '{$_POST['cf_image_extension']}',
                cf_flash_extension = '{$_POST['cf_flash_extension']}',
                cf_movie_extension = '{$_POST['cf_movie_extension']}',
                cf_formmail_is_member = '{$_POST['cf_formmail_is_member']}',
                cf_page_rows = '{$_POST['cf_page_rows']}',
                cf_mobile_page_rows = '{$_POST['cf_mobile_page_rows']}',
                cf_stipulation = '{$_POST['cf_stipulation']}',
                cf_privacy = '{$_POST['cf_privacy']}',
                cf_open_modify = '{$_POST['cf_open_modify']}',
                cf_memo_send_point = '{$_POST['cf_memo_send_point']}',
                cf_mobile_new_skin = '{$_POST['cf_mobile_new_skin']}',
                cf_mobile_search_skin = '{$_POST['cf_mobile_search_skin']}',
                cf_mobile_connect_skin = '{$_POST['cf_mobile_connect_skin']}',
                cf_mobile_faq_skin = '{$_POST['cf_mobile_faq_skin']}',
                cf_mobile_member_skin = '{$_POST['cf_mobile_member_skin']}',
                cf_captcha_mp3 = '{$_POST['cf_captcha_mp3']}',
                cf_editor = '{$_POST['cf_editor']}',
				cf_title			= '{$_POST['cf_title']}',
				cf_bgm				= '{$_POST['cf_bgm']}',
				cf_twitter			= '{$_POST['cf_twitter']}',
				cf_side_title		= '{$_POST['cf_side_title']}',
				cf_class_title		= '{$_POST['cf_class_title']}',
				cf_shop_category	= '{$_POST['cf_shop_category']}',
				cf_item_category	= '{$_POST['cf_item_category']}',
				cf_open				= '{$_POST['cf_open']}',
				cf_site_descript	= '{$_POST['cf_site_descript']}',
				cf_site_img			= '{$cf_site_img}',
				cf_favicon			= '{$cf_favicon}',
				cf_character_count	= '{$_POST['cf_character_count']}', 
				cf_search_count		= '{$_POST['cf_search_count']}', 
				cf_money			= '{$_POST['cf_money']}',
				cf_money_pice		= '{$_POST['cf_money_pice']}',
				cf_exp_name			= '{$_POST['cf_exp_name']}',
				cf_exp_pice			= '{$_POST['cf_exp_pice']}',
				cf_rank_name		= '{$_POST['cf_rank_name']}',
				cf_status_point		= '{$_POST['cf_status_point']}',
				cf_1				= '{$_POST['cf_1']}',
				cf_2				= '{$_POST['cf_2']}',
				cf_3				= '{$_POST['cf_3']}',
				cf_4				= '{$_POST['cf_4']}',
				cf_5				= '{$_POST['cf_5']}',
				cf_6				= '{$_POST['cf_6']}',
				cf_7				= '{$_POST['cf_7']}',
				cf_8				= '{$_POST['cf_8']}',
				cf_9				= '{$_POST['cf_9']}',
				cf_10				= '{$_POST['cf_10']}'";
sql_query($sql);

//sql_query(" OPTIMIZE TABLE `$g5[config_table]` ");

goto_url('./config_form.php', false);
?>