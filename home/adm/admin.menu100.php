<?php
$menu['menu100'] = array (
	array('100000', '환경설정', G5_ADMIN_URL.'/config_form.php',   'config'),
	array('100100', '기본환경설정', G5_ADMIN_URL.'/config_form.php',   'cf_basic'), 
	array('100300', '디자인 설정', G5_ADMIN_URL.'/design_form.php', 'cf_design_basic'), 
	array('100320', '메뉴관리', G5_ADMIN_URL.'/menu_list.php', ''),
	array('100330', '폰트관리', G5_ADMIN_URL.'/font_list.php', ''),  
    array('100400', '회원관리', G5_ADMIN_URL.'/member_list.php', '')
);

/*
if(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE) {
	$menu['menu100'][] = array('100510', 'Browscap 업데이트', G5_ADMIN_URL.'/browscap.php', 'cf_browscap');
	$menu['menu100'][] = array('100520', '접속로그 변환', G5_ADMIN_URL.'/browscap_convert.php', 'cf_visit_cnvrt');
}
$menu['menu100'][] = array('100400', '부가서비스', G5_ADMIN_URL.'/service.php', 'cf_service');
*/
?>