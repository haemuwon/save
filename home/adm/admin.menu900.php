<?php
$menu['menu900'] = array (
	array('900000', '기타 관리', G5_ADMIN_URL.'/config_form.php',   'config'),
	array('900100', 'DB관리', G5_DB_URL, ''),
	array('900800', '세션파일 일괄삭제',G5_ADMIN_URL.'/session_file_delete.php', 'cf_session', 1),
    array('900920', '썸네일파일 일괄삭제',G5_ADMIN_URL.'/thumbnail_file_delete.php',   'cf_thumbnail', 1)
); 

?>