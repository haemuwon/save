<?php
@set_time_limit(0);
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

include_once ('../config.php');
include_once ('../lib/common.lib.php');

$title = G5_VERSION." 설치 완료 3/3";
include_once ('./install.inc.php');

//print_r($_POST); exit;

$mysql_host  = $_POST['mysql_host'];
$mysql_user  = $_POST['mysql_user'];
$mysql_pass  = $_POST['mysql_pass'];
$mysql_db    = $_POST['mysql_db'];
$table_prefix= $_POST['table_prefix'];
$admin_id    = $_POST['admin_id'];
$admin_pass  = $_POST['admin_pass'];
$admin_name  = $_POST['admin_name'];
$admin_email = $_POST['admin_email'];
$absolute_password = $_POST['absolute_password'];
$table_url = $_POST['table_url'];

$dblink = sql_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if (!$dblink) {
?>

<div class="ins_inner">
    <p>MySQL Host, User, Password 를 확인해 주십시오.</p>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>

<?php
    include_once ('./install.inc2.php');
    exit;
}

$select_db = sql_select_db($mysql_db, $dblink);
if (!$select_db) {
?>

<div class="ins_inner">
    <p>MySQL DB 를 확인해 주십시오.</p>
    <div class="inner_btn"><a href="./install_config.php">뒤로가기</a></div>
</div>

<?php
    include_once ('./install.inc2.php');
    exit;
}

$mysql_set_mode = 'false';
sql_set_charset('utf8', $dblink);
$result = sql_query(" SELECT @@sql_mode as mode ", true, $dblink);
$row = sql_fetch_array($result);
if($row['mode']) {
    sql_query("SET SESSION sql_mode = ''", true, $dblink);
    $mysql_set_mode = 'true';
}
unset($result);
unset($row);
?>

<div class="ins_inner">
    <h2><?php echo G5_VERSION ?> 설치가 시작되었습니다.</h2>

    <ol>
<?php
// 테이블 생성 ------------------------------------
$file = implode('', file('./gnuboard5.sql'));
eval("\$file = \"$file\";");

$file = preg_replace('/^--.*$/m', '', $file);
$file = preg_replace('/`avo_([^`]+`)/', '`'.$table_prefix.'$1', $file);
$f = explode(';', $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == '') continue;
    sql_query($f[$i], true, $dblink);
}
// 테이블 생성 ------------------------------------
?>

        <li>전체 테이블 생성 완료</li>

<?php
$read_point = 0;
$write_point = 0;
$comment_point = 0;
$download_point = 0;

//-------------------------------------------------------------------------------------------------
// config 테이블 설정
$sql = " insert into `{$table_prefix}config`
            set cf_title = '".$_POST['community_title']."',
                cf_theme = 'basic',
                cf_admin = '$admin_id',
                cf_admin_email = '$admin_email',
                cf_admin_email_name = '".$_POST['community_title']."',
                cf_use_point = '1',
                cf_use_copy_log = '0',
                cf_login_point = '0',
                cf_memo_send_point = '0',
                cf_cut_name = '15',
                cf_nick_modify = '0',
                cf_new_skin = 'basic',
                cf_new_rows = '15',
                cf_search_skin = 'basic',
                cf_connect_skin = 'basic',
                cf_read_point = '$read_point',
                cf_write_point = '$write_point',
                cf_comment_point = '$comment_point',
                cf_download_point = '$download_point',
                cf_write_pages = '10',
                cf_mobile_pages = '5',
                cf_link_target = '_blank',
                cf_delay_sec = '0',
                cf_filter = '',
                cf_possible_ip = '',
                cf_intercept_ip = '',
                cf_member_skin = 'basic',
                cf_mobile_new_skin = 'basic',
                cf_mobile_search_skin = 'basic',
                cf_mobile_connect_skin = 'basic',
                cf_mobile_member_skin = 'basic',
                cf_faq_skin = 'basic',
                cf_mobile_faq_skin = 'basic',
                cf_editor = 'smarteditor2',
                cf_captcha_mp3 = 'basic',
                cf_register_level = '2',
                cf_register_point = '0',
                cf_icon_level = '2',
                cf_leave_day = '30',
                cf_search_part = '10000',
                cf_email_use = '1',
                cf_prohibit_id = 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객',
                cf_prohibit_email = '',
                cf_new_del = '30',
                cf_visit_del = '180',
                cf_use_member_icon = '2',
                cf_member_icon_size = '5000',
                cf_member_icon_width = '22',
                cf_member_icon_height = '22',
                cf_login_minutes = '10',
                cf_image_extension = 'gif|jpg|jpeg|png',
                cf_flash_extension = 'swf',
                cf_movie_extension = 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
                cf_formmail_is_member = '1',
                cf_page_rows = '15',
                cf_mobile_page_rows = '15',
                cf_cert_limit = '2',
                cf_stipulation = '해당 커뮤니티에 맞는 커뮤니티 활동 규칙을 입력합니다. 회원 가입시 오너 동의 사항으로 출력됩니다.',
                cf_privacy = '해당 커뮤니티에 맞는 캐릭터 생성 유의사항을 입력합니다. 회원 가입시 오너 동의 사항으로 출력됩니다.',
				cf_side_title = '소속',
				cf_class_title = '종족',
				cf_character_count = '1',
				cf_search_count = '5',
				cf_money = '화폐',
				cf_money_pice = '원',
				cf_exp_name = '경험치',
				cf_exp_pice = 'exp',
				cf_rank_name = '랭킹',
				cf_shop_category = '일반||프로필수정||기타',
				cf_item_category = '일반||프로필수정||아이템추가||서브캐릭터추가'
                ";
sql_query($sql, true, $dblink);


// 관리자 회원가입
$sql = " insert into `{$table_prefix}member`
            set mb_id = '$admin_id',
                 mb_password = '".get_encrypt_string($admin_pass)."',
                 mb_name = '$admin_name',
                 mb_nick = '$admin_name',
                 mb_email = '$admin_email',
                 mb_level = '10',
                 mb_mailling = '1',
                 mb_open = '1',
                 mb_email_certify = '".G5_TIME_YMDHIS."',
                 mb_datetime = '".G5_TIME_YMDHIS."',
                 mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                 ";
sql_query($sql, true, $dblink);



// 기본그룹 생성
 
$sql = " insert into `{$table_prefix}group`
            (`gr_id`, `gr_subject`, `gr_device`, `gr_admin`, `gr_use_access`, `gr_order`, `gr_1_subj`, `gr_2_subj`, `gr_3_subj`, `gr_4_subj`, `gr_5_subj`, `gr_6_subj`, `gr_7_subj`, `gr_8_subj`, `gr_9_subj`, `gr_10_subj`, `gr_1`, `gr_2`, `gr_3`, `gr_4`, `gr_5`, `gr_6`, `gr_7`, `gr_8`, `gr_9`, `gr_10`) VALUES
			('original','$community_title','both', '', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
			 ";
sql_query($sql, true, $dblink);
 

// 디자인 설정 파일
function g5_path()
{
    $result['path'] = str_replace('\\', '/', dirname(__FILE__));
    $tilde_remove = preg_replace('/^\/\~[^\/]+(.*)$/', '$1', $_SERVER['SCRIPT_NAME']);
    $document_root = str_replace($tilde_remove, '', $_SERVER['SCRIPT_FILENAME']);
    $root = str_replace($document_root, '', $result['path']);
    $port = $_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '';
    $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
    $user = str_replace(str_replace($document_root, '', $_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_NAME']);
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    if(isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))
        $host = preg_replace('/:[0-9]+$/', '', $host);
    $host = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", '', $host);
    $result['url'] = $http.$host.$port.$user.$root;
	$result['url'] = str_replace("/install", "", $result['url']);
    return $result;
}
$g5_path = g5_path();
$sql = " INSERT INTO `{$table_prefix}css_config` (`cs_id`, `cs_name`, `cs_value`, `cs_descript`, `cs_etc_1`, `cs_etc_2`, `cs_etc_3`, `cs_etc_4`, `cs_etc_5`, `cs_etc_6`, `cs_etc_7`, `cs_etc_8`, `cs_etc_9`, `cs_etc_10`) VALUES
		(1, 'logo', '', '', '', '', '', '', '', '', '', '', '', ''),
		(2, 'm_logo', '', '', '', '', '', '', '', '', '', '', '', ''),
		(3, 'background', '".$g5_path['url']."/img/bak_admin_login_top_pattern.png', '', '', '', '', '', '', '', '', '', '', ''),
		(4, 'm_background', '".$g5_path['url']."/img/bak_admin_login_top_pattern.png', '', '', '', '', '', '', '', '', '', '', ''),
		(5, 'menu_pos', 'top', '', '', '', '', '', '', '', '', '', '', ''),
		(6, 'menu_width', '200', '', '', '', '', '', '', '', '', '', '', ''),
		(7, 'menu_height', '50', '', '', '', '', '', '', '', '', '', '', ''),
		(8, 'menu_background', '', '', '', '', '', '', '', '', '', '', '', ''),
		(9, 'm_menu_background', '', 'rgba(0, 0, 0, .5)', '', '', '', '', '', '', '', '', '', ''),
		(10, 'btn_default', '#ffffff', '', '#000000', '#000000', '#ffffff', '#222222', '#000000', '', '', '', '', ''),
		(11, 'btn_point', '#ffffff', '', '#29c7c9', '#29c7c9', '#ffffff', '#29c7c9', '#29c7c9', '', '', '', '', ''),
		(12, 'btn_etc', '#ffffff', '', '#555555', '#333333', '#ffffff', '#555555', '#333333', '', '', '', '', ''),
		(13, 'mmb_contain_bak', '', '', '', '', '', '', '', '', '', '', '', ''),
		(14, 'mmb_notice', 'rgba(0, 0, 0, .5)', '', '#ffffff', '', '', '', '', '', '', '', '', ''),
		(15, 'mmb_list', '', '', '', '', '', '', '', '', '', '', '', ''),
		(16, 'mmb_list_item', '', '', '', '', '', '', '', '', '', '', '', ''),
		(17, 'mmb_log', '', '', '', '', '', '', '', '', '', '', '', ''),
		(18, 'mmb_reply', '', '', '', '', '', '', '', '', '', '', '', ''),
		(19, 'mmb_reply_item', 'rgba(0, 0, 0, .5)', '', '#eaeaea', '', '', '', '', '10', '', '', '', ''),
		(20, 'mmb_name', '#eeeeee', '', '12', '', '', '', '', '', '', '', '', ''),
		(21, 'mmb_owner_name', '#29c7c9', '', '12', '▶', '◀', '', '', '', '', '', '', ''),
		(22, 'mmb_datetime', '#bbbbbb', '', '11', '', '', '', '', '', '', '', '', ''),
		(23, 'mmb_link', '#29c7c9', '', '', '', '', '', '', '', '', '', '', ''),
		(24, 'mmb_call', '#29c7c9', '', '', '', '', '', '', '', '', '', '', ''),
		(25, 'mmb_log_ank', '#29c7c9', '', '', '', '', '', '', '', '', '', '', ''),
		(26, 'mmb_hash', '#29c7c9', '', '', '', '', '', '', '', '', '', '', ''),
		(27, 'color_default', '#ffffff', '', '', '', '', '', '', '', '', '', '', ''),
		(28, 'color_bak', '#333333', '', '', '', '', '', '', '', '', '', '', ''),
		(29, 'color_point', '#29c7c9', '', '', '', '', '', '', '', '', '', '', ''),
		(30, 'input_bak', '#000000', '', '#eeeeee', '#222222', '', '', '', '', '', '', '', ''),
		(31, 'box_style', 'rgba(0, 0, 0, .5)', '', '#eeeeee', '', '', '', '', '', '', '', '', ''),
		(32, 'menu_text', '#ffffff', '', '14', '#29c7c9', '14', '', '', '', '', '', '', ''),
		(33, 'use_header', '', '', '', '', '', '', '', '', '', '', '', ''),
		(34, 'm_header_background', '', '', 'rgba(0, 0, 0, .5)', '', '', '', '', '', '', '', '', ''),
		(35, 'header_background', '', '', 'rgba(0, 0, 0, .5)', '', '', '', '', '', '', '', '', ''),
		(36, 'equalizer', '#29c7c9', '', '#000000', '', '', '', '', '', '', '', '', ''),
		(37, 'board_table', '', '', '', '', '', '', '', '', '', '', '', ''),
		(38, 'list_header', '#000000', '', '#ffffff', '#333333', 'double', '1', '||top||bottom||', '', '', '', '', ''),
		(39, 'list_body', '', '', '#ffffff', '', '', '', '', '', '', '', '', ''),
		(40, 'form_header', '#000000', '', '#ffffff', '', '', '', '', '', '', '', '', ''),
		(41, 'form_body', 'rgba(255, 255, 255, .1)', '', '#ffffff', '#333333', 'solid', '1', '||top||bottom||', '', '', '', '', ''),
		(42, 'sub_menu', 'rgba(0, 0, 0, .5)', '', '#eeeeee', '#555555', 'dashed', '1', '||top||bottom||', '#29c7c9', '', '', '', ''),
		(43, 'mmb_counter', '', '', '', '', '', '', '', '', '', '', '', ''),
		(44, 'use_footer', '', '', '', '', '', '', '', '', '', '', '', '')";
sql_query($sql, true, $dblink);



?>

        <li>DB설정 완료</li>

<?php
//-------------------------------------------------------------------------------------------------

// 디렉토리 생성
$dir_arr = array (
    $data_path.'/cache',
    $data_path.'/editor',
    $data_path.'/file',
    $data_path.'/log',
    $data_path.'/member',
    $data_path.'/session',
    $data_path.'/content',
    $data_path.'/tmp',
	$data_path.'/emoticon',
	$data_path.'/site'
);

for ($i=0; $i<count($dir_arr); $i++) {
    @mkdir($dir_arr[$i], G5_DIR_PERMISSION);
    @chmod($dir_arr[$i], G5_DIR_PERMISSION);
}
?>

        <li>데이터 디렉토리 생성 완료</li>

<?php
//-------------------------------------------------------------------------------------------------

// DB 설정 파일 생성
$file = '../'.G5_DATA_DIR.'/'.G5_DBCONFIG_FILE;
$f = @fopen($file, 'a');

fwrite($f, "<?php\n");
fwrite($f, "if (!defined('_GNUBOARD_')) exit;\n");
fwrite($f, "define('G5_MYSQL_HOST', '{$mysql_host}');\n");
fwrite($f, "define('G5_MYSQL_USER', '{$mysql_user}');\n");
fwrite($f, "define('G5_MYSQL_PASSWORD', '{$mysql_pass}');\n");
fwrite($f, "define('G5_MYSQL_DB', '{$mysql_db}');\n"); 

fwrite($f, "define('G5_DB_URL', '{$table_url}');\n");

fwrite($f, "define('G5_MYSQL_SET_MODE', {$mysql_set_mode});\n\n");
fwrite($f, "define('G5_TABLE_PREFIX', '{$table_prefix}');\n\n");

fwrite($f, "\$g5['write_prefix'] = G5_TABLE_PREFIX.'write_'; // 게시판 테이블명 접두사\n\n");
fwrite($f, "\$g5['auth_table'] = G5_TABLE_PREFIX.'auth'; // 관리권한 설정 테이블\n");
fwrite($f, "\$g5['config_table'] = G5_TABLE_PREFIX.'config'; // 기본환경 설정 테이블\n");
fwrite($f, "\$g5['group_table'] = G5_TABLE_PREFIX.'group'; // 게시판 그룹 테이블\n");
fwrite($f, "\$g5['group_member_table'] = G5_TABLE_PREFIX.'group_member'; // 게시판 그룹+회원 테이블\n");
fwrite($f, "\$g5['board_table'] = G5_TABLE_PREFIX.'board'; // 게시판 설정 테이블\n");
fwrite($f, "\$g5['board_file_table'] = G5_TABLE_PREFIX.'board_file'; // 게시판 첨부파일 테이블\n");
fwrite($f, "\$g5['board_good_table'] = G5_TABLE_PREFIX.'board_good'; // 게시물 추천,비추천 테이블\n");
fwrite($f, "\$g5['board_new_table'] = G5_TABLE_PREFIX.'board_new'; // 게시판 새글 테이블\n");
fwrite($f, "\$g5['login_table'] = G5_TABLE_PREFIX.'login'; // 로그인 테이블 (접속자수)\n"); 
fwrite($f, "\$g5['member_table'] = G5_TABLE_PREFIX.'member'; // 회원 테이블\n");
fwrite($f, "\$g5['memo_table'] = G5_TABLE_PREFIX.'memo'; // 메모 테이블\n"); 
fwrite($f, "\$g5['visit_table'] = G5_TABLE_PREFIX.'visit'; // 방문자 테이블\n");
fwrite($f, "\$g5['visit_sum_table'] = G5_TABLE_PREFIX.'visit_sum'; // 방문자 합계 테이블\n");
fwrite($f, "\$g5['uniqid_table'] = G5_TABLE_PREFIX.'uniqid'; // 유니크한 값을 만드는 테이블\n");
fwrite($f, "\$g5['content_table'] = G5_TABLE_PREFIX.'content'; // 내용(컨텐츠)정보 테이블\n");
fwrite($f, "\$g5['menu_table'] = G5_TABLE_PREFIX.'menu'; // 메뉴관리 테이블\n");
fwrite($f, "\$g5['emoticon_table'] = G5_TABLE_PREFIX.'emoticon'; // 이모티콘 테이블\n");
fwrite($f, "\$g5['css_table'] = G5_TABLE_PREFIX.'css_config'; // CSS STYLE 정의 저장하는 테이블\n");
fwrite($f, "\$g5['backup_table'] = G5_TABLE_PREFIX.'backup'; // 백업 테이블\n");
fwrite($f, "?>");

fclose($f);
@chmod($file, G5_FILE_PERMISSION);
?>

        <li>DB설정 파일 생성 완료 (<?php echo $file ?>)</li>

<?php
// data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl 파일을 실행할수 없게함.
$f = fopen($data_path.'/.htaccess', 'w');
$str = <<<EOD
<FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll])">
Order allow,deny
Deny from all
</FilesMatch>
EOD;
fwrite($f, $str);
fclose($f);
//-------------------------------------------------------------------------------------------------
?>
    </ol>

    <p>축하합니다. <?php echo G5_VERSION ?> 설치가 완료되었습니다.</p>

</div>

<div class="ins_inner">

    <h2>환경설정 변경은 다음의 과정을 따르십시오.</h2>

    <ol>
        <li>메인화면으로 이동</li>
        <li>관리자 로그인</li>
        <li>관리자 모드 접속</li>
        <li>환경설정 메뉴의 기본환경설정 페이지로 이동</li>
    </ol>

    <div class="inner_btn">
        <a href="../adm/">관리자 페이지로 이동</a>
    </div>

</div>

<?php
include_once ('./install.inc2.php');
?>