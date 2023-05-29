<?php
$sub_menu = "100100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

if ($is_admin != 'super')
	alert('최고관리자만 접근 가능합니다.');

if (!isset($config['cf_add_script'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_add_script` TEXT NOT NULL AFTER `cf_admin_email_name` ", true);
}

if (!isset($config['cf_mobile_new_skin'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_mobile_new_skin` VARCHAR(255) NOT NULL AFTER `cf_memo_send_point`,
					ADD `cf_mobile_search_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_new_skin`,
					ADD `cf_mobile_connect_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_search_skin`,
					ADD `cf_mobile_member_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_connect_skin` ", true);
}

if (isset($config['cf_gcaptcha_mp3'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					CHANGE `cf_gcaptcha_mp3` `cf_captcha_mp3` VARCHAR(255) NOT NULL DEFAULT '' ", true);
} else if (!isset($config['cf_captcha_mp3'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_captcha_mp3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_mobile_member_skin` ", true);
}

if(!isset($config['cf_editor'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_editor` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_captcha_mp3` ", true);
}


if(!isset($config['cf_mobile_pages'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_mobile_pages` INT(11) NOT NULL DEFAULT '0' AFTER `cf_write_pages` ", true);
	sql_query(" UPDATE `{$g5['config_table']}` SET cf_mobile_pages = '5' ", true);
}

// uniqid 테이블이 없을 경우 생성
if(!sql_query(" DESC {$g5['uniqid_table']} ", false)) {
	sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['uniqid_table']}` (
				  `uq_id` bigint(20) unsigned NOT NULL,
				  `uq_ip` varchar(255) NOT NULL,
				  PRIMARY KEY (`uq_id`)
				) ", false);
}

if(!sql_query(" SELECT uq_ip from {$g5['uniqid_table']} limit 1 ", false)) {
	sql_query(" ALTER TABLE {$g5['uniqid_table']} ADD `uq_ip` VARCHAR(255) NOT NULL ");
}
 

if(!isset($config['cf_admin_email'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_admin_email` VARCHAR(255) NOT NULL AFTER `cf_admin` ", true);
}

if(!isset($config['cf_admin_email_name'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_admin_email_name` VARCHAR(255) NOT NULL AFTER `cf_admin_email` ", true);
} 
if(!isset($config['cf_analytics'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_analytics` TEXT NOT NULL AFTER `cf_intercept_ip` ", true);
}

if(!isset($config['cf_add_meta'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_add_meta` TEXT NOT NULL AFTER `cf_analytics` ", true);
}
 
if(!isset($config['cf_mobile_page_rows'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_mobile_page_rows` int(11) NOT NULL DEFAULT '0' AFTER `cf_page_rows` ", true);
}

if(!isset($config['cf_cert_req'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_cert_req` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_cert_limit` ", true);
}

if(!isset($config['cf_faq_skin'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_faq_skin` varchar(255) NOT NULL DEFAULT '' AFTER `cf_connect_skin`,
					ADD `cf_mobile_faq_skin` varchar(255) NOT NULL DEFAULT '' AFTER `cf_mobile_connect_skin` ", true);
}
 
if(!isset($config['cf_optimize_date'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}`
					ADD `cf_optimize_date` date NOT NULL default '0000-00-00' AFTER `cf_popular_del` ", true);
}
 

// 접속자 정보 필드 추가
if(!sql_query(" select vi_browser from {$g5['visit_table']} limit 1 ")) {
	sql_query(" ALTER TABLE `{$g5['visit_table']}`
					ADD `vi_browser` varchar(255) NOT NULL DEFAULT '' AFTER `vi_agent`,
					ADD `vi_os` varchar(255) NOT NULL DEFAULT '' AFTER `vi_browser`,
					ADD `vi_device` varchar(255) NOT NULL DEFAULT '' AFTER `vi_os` ", true);
}
 

$g5['title'] = '환경설정';
include_once ('./admin.head.php');

$pg_anchor = '<ul class="anchor">
	<li><a href="#anc_001">기본환경</a></li>
	<li><a href="#anc_002">게시판기본</a></li>
	<li><a href="#anc_003">회원가입</a></li>  
</ul>';

$frm_submit = '<div class="btn_confirm01 btn_confirm">
	<input type="submit" value="확인" class="btn_submit" accesskey="s">
</div>';

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
	$userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data">
<input type="hidden" name="token" value="" id="token">

<section id="anc_001">
	<h2 class="h2_frm">기본환경 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>홈페이지 기본환경 설정</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row">공개설정</th>
			<td>
				<input type="checkbox" name="cf_open" value="1" id="cf_open" <?php echo $config['cf_open']?'checked':''; ?>>
				<label for="cf_open">사이트공개</label>
				&nbsp;&nbsp;
				<input type="checkbox" name="cf_1" value="1" id="cf_1" <?php echo $config['cf_1']?'checked':''; ?>>
				<label for="cf_1">계정생성 가능</label>
				&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<th scope="row">홈페이지 제목</th>
			<td><input type="text" name="cf_title" value="<?php echo $config['cf_title'] ?>" id="cf_title" required class="required" size="40"></td>
		</tr>
		<tr>
			<th>사이트설명</th>
			<td>
				<input type="text" name="cf_site_descript" value="<?php echo $config['cf_site_descript'] ?>" size="50" />
			</td>
		</tr>
		<tr>
			<th rowspan="2">파비콘</th>
			<td>
				<?php echo help('파비콘 확장자는 ico 로 등록해 주셔야 적용됩니다.') ?>
				직접등록&nbsp;&nbsp; <input type="file" name="cf_favicon_file" value="" size="50">
			</td></tr><tr>
			<td>
				외부경로&nbsp;&nbsp; <input type="text" name="cf_favicon" value="<?=$config['cf_favicon']?>" size="50"/>
			</td>
		</tr>
		<tr>
			<th rowspan="2">사이트이미지</th>
			<td>
				<?php echo help('사이트 링크 추가시, SNS에서 미리보기로 뜨는 썸네일 이미지를 등록합니다. 290px * 160px 파일로 업로드해 주시길 바랍니다.') ?>
				직접등록&nbsp;&nbsp; <input type="file" name="cf_site_img_file" value="" size="50">
			</td></tr><tr>
			<td>
				외부경로&nbsp;&nbsp; <input type="text" name="cf_site_img" value="<?=$config['cf_site_img']?>" size="50"/>
			</td>
		</tr>
		
		<tr>
			<th scope="row"><label for="site_back">배경음악</label></th>
			<td>
				<?php echo help('유튜브 재생목록 아이디 (https://www.youtube.com/watch?list=재생목록고유아이디) 를 입력해 주세요.') ?>
				<input type="text" name="cf_bgm" value="<?php echo $config['cf_bgm'] ?>" id="cf_bgm" size="50">
			</td>
		</tr> 
		<tr>
			<th scope="row">기타설정</th>
			<td>
				<?php echo help('디자인 관리 사용을 하시면, 기본 디자인 + 관리자 디자인 설정을 사용하실 수 있습니다.') ?>
				<?php echo help('직접 디자인 수정을 원하신다면, 디자인 관리 사용하지 않음에 체크 하세요.') ?>
				<input type="checkbox" name="cf_7" value="1" id="cf_7" <?php echo $config['cf_7']?'checked':''; ?>>
				<label for="cf_7">디자인 관리 사용하지 않음</label>
				
			</td>
		</tr>
		</tbody>
		</table>
	</div> 
<br >
	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>홈페이지 기본환경 설정</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row"><label for="cf_admin">최고관리자<strong class="sound_only">필수</strong></label></th>
			<td colspan="3"><?php echo get_member_id_select('cf_admin', 10, $config['cf_admin'], 'required') ?></td>
		</tr>
		
		<tr>
			<th>관리자 아이콘</th>
			<td>
				<i class="admin-icon-box">
				<? if(is_file(G5_DATA_PATH."/site/ico_admin")) { ?>
					<img src="<?=G5_DATA_URL?>/site/ico_admin" alt="관리자 아이콘" />
				<? } ?>
				</i>
				<input type="file" name="admin_icon_file" value="" size="50">
			</td>
		</tr>
 
		<tr>
			<th scope="row"><label for="cf_cut_name">이름(닉네임) 표시</label></th>
			<td colspan="3">
				<input type="text" name="cf_cut_name" value="<?php echo $config['cf_cut_name'] ?>" id="cf_cut_name" class="frm_input" size="5"> 자리만 표시
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_nick_modify">닉네임 수정</label></th>
			<td>수정하면 <input type="text" name="cf_nick_modify" value="<?php echo $config['cf_nick_modify'] ?>" id="cf_nick_modify" class="frm_input" size="3"> 일 동안 바꿀 수 없음</td>
			<th scope="row"><label for="cf_open_modify">정보공개 수정</label></th>
			<td>수정하면 <input type="text" name="cf_open_modify" value="<?php echo $config['cf_open_modify'] ?>" id="cf_open_modify" class="frm_input" size="3"> 일 동안 바꿀 수 없음</td>
		</tr>
		<tr style="display:none;">
			<th scope="row"><label for="cf_new_del">최근게시물 삭제</label></th>
			<td colspan="3">
				<?php echo help('설정일이 지난 최근게시물 자동 삭제') ?>
				<input type="text" name="cf_new_del" value="<?php echo $config['cf_new_del'] ?>" id="cf_new_del" class="frm_input" size="5"> 일
			</td>
		</tr> 
		<tr>
			<th scope="row"><label for="cf_page_rows">한페이지당 라인수</label></th>
			<td>
				<?php echo help('목록(리스트) 한페이지당 라인수') ?>
				<input type="text" name="cf_page_rows" value="<?php echo $config['cf_page_rows'] ?>" id="cf_page_rows" class="frm_input" size="3"> 라인
			</td>
			<th scope="row"><label for="cf_write_pages">페이지 표시 수<strong class="sound_only">필수</strong></label></th>
			<td><input type="text" name="cf_write_pages" value="<?php echo $config['cf_write_pages'] ?>" id="cf_write_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
		</tr>
		<tr style="display:none;">
			<th scope="row"><label for="cf_editor">에디터 선택</label></th>
			<td colspan="3">
				<?php echo help(G5_EDITOR_URL.' 밑의 DHTML 에디터 폴더를 선택합니다.') ?>
				<select name="cf_editor" id="cf_editor">
				<?php
				$arr = get_skin_dir('', G5_EDITOR_PATH);
				for ($i=0; $i<count($arr); $i++) {
					if ($i == 0) echo "<option value=\"\">사용안함</option>";
					echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_editor'], $arr[$i]).">".$arr[$i]."</option>\n";
				}
				?>
				</select>
			</td>
		</tr>
	  
		<tr>
			<th scope="row"><label for="cf_possible_ip">접근가능 IP</label></th>
			<td>
				<?php echo help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
				<textarea name="cf_possible_ip" id="cf_possible_ip"><?php echo $config['cf_possible_ip'] ?></textarea>
			</td>
			<th scope="row"><label for="cf_intercept_ip">접근차단 IP</label></th>
			<td>
				<?php echo help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
				<textarea name="cf_intercept_ip" id="cf_intercept_ip"><?php echo $config['cf_intercept_ip'] ?></textarea>
			</td>
		</tr>
		<tr style="display:none;">
			<th scope="row"><label for="cf_analytics">방문자분석 스크립트</label></th>
			<td colspan="3">
				<?php echo help('방문자분석 스크립트 코드를 입력합니다. 예) 구글 애널리틱스'); ?>
				<textarea name="cf_analytics" id="cf_analytics"><?php echo $config['cf_analytics']; ?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_add_meta">추가 메타태그</label></th>
			<td colspan="3">
				<?php echo help('추가로 사용하실 meta 태그를 입력합니다.'); ?>
				<textarea name="cf_add_meta" id="cf_add_meta"><?php echo $config['cf_add_meta']; ?></textarea>
			</td>
		</tr> 
		</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>

<section id="anc_002">
	<h2 class="h2_frm">게시판 기본 설정</h2>
	<?php echo $pg_anchor ?>
	<div class="local_desc02 local_desc">
		<p>각 게시판 관리에서 개별적으로 설정 가능합니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>게시판 기본 설정</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row"><label for="cf_delay_sec">글쓰기 간격<strong class="sound_only">필수</strong></label></th>
			<td><input type="text" name="cf_delay_sec" value="<?php echo $config['cf_delay_sec'] ?>" id="cf_delay_sec" required class="required numeric frm_input" size="3"> 초 지난후 가능</td>
			<th scope="row"><label for="cf_link_target">새창 링크</label></th>
			<td>
				<?php echo help('글내용중 자동 링크되는 타켓을 지정합니다.') ?>
				<select name="cf_link_target" id="cf_link_target">
					<option value="_blank"<?php echo get_selected($config['cf_link_target'], '_blank') ?>>_blank</option>
					<option value="_self"<?php echo get_selected($config['cf_link_target'], '_self') ?>>_self</option>
					<option value="_top"<?php echo get_selected($config['cf_link_target'], '_top') ?>>_top</option>
					<option value="_new"<?php echo get_selected($config['cf_link_target'], '_new') ?>>_new</option>
				</select>
			</td>
		</tr> 
		<tr>
			<th scope="row"><label for="cf_search_part">검색 단위</label></th>
			<td colspan="3"><input type="text" name="cf_search_part" value="<?php echo $config['cf_search_part'] ?>" id="cf_search_part" class="frm_input" size="4"> 건 단위로 검색</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_image_extension">이미지 업로드 확장자</label></th>
			<td colspan="3">
				<?php echo help('게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분') ?>
				<input type="text" name="cf_image_extension" value="<?php echo $config['cf_image_extension'] ?>" id="cf_image_extension" class="frm_input" size="70">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_flash_extension">플래쉬 업로드 확장자</label></th>
			<td colspan="3">
				<?php echo help('게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분') ?>
				<input type="text" name="cf_flash_extension" value="<?php echo $config['cf_flash_extension'] ?>" id="cf_flash_extension" class="frm_input" size="70">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_movie_extension">동영상 업로드 확장자</label></th>
			<td colspan="3">
				<?php echo help('게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분') ?>
				<input type="text" name="cf_movie_extension" value="<?php echo $config['cf_movie_extension'] ?>" id="cf_movie_extension" class="frm_input" size="70">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="cf_filter">단어 필터링</label></th>
			<td colspan="3">
				<?php echo help('입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분합니다.') ?>
				<textarea name="cf_filter" id="cf_filter" rows="7"><?php echo $config['cf_filter'] ?></textarea>
			 </td>
		</tr>
		</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>

<section id="anc_003">
	<h2 class="h2_frm">회원가입 설정</h2>
	<?php echo $pg_anchor ?>
	<div class="local_desc02 local_desc">
		<p>회원가입 시 사용할 스킨과 입력 받을 정보 등을 설정할 수 있습니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>회원가입 설정</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tbody>
		<tr style="display:none;">
			<th scope="row"><label for="cf_member_skin">회원 스킨</label></th>
			<td colspan="3">
				<?php echo get_skin_select('member', 'cf_member_skin', 'cf_member_skin', $config['cf_member_skin']); ?>
			</td>
		</tr>
		<tr style="display: none;">
			<th scope="row">홈페이지 입력</th>
			<td colspan="3">
				<input type="checkbox" name="cf_use_homepage" value="1" id="cf_use_homepage" <?php echo $config['cf_use_homepage']?'checked':''; ?>> <label for="cf_use_homepage">보이기</label>
				<input type="checkbox" name="cf_req_homepage" value="1" id="cf_req_homepage" <?php echo $config['cf_req_homepage']?'checked':''; ?>> <label for="cf_req_homepage">필수입력</label>
			</td> 
		</tr> 
		<tr>
			<th scope="row"><label for="cf_register_level">회원가입시 권한</label></th>
			<td>
				<?php echo help('숫자가 낮을수록 권한이 낮음. 1은 비회원, 10은 관리자') ?>
				<?php echo get_member_level_select('cf_register_level', 1, 9, $config['cf_register_level']) ?>
			</td> 
			<th scope="row" id="th310"><label for="cf_leave_day">회원탈퇴후 삭제일</label></th>
			<td ><input type="text" name="cf_leave_day" value="<?php echo $config['cf_leave_day'] ?>" id="cf_leave_day" class="frm_input" size="2"> 일 후 자동 삭제</td>
		</tr>
		<tr style="display: none;">
			<th scope="row"><label for="cf_use_member_icon">회원아이콘 사용</label></th>
			<td>
				<?php echo help('게시물에 게시자 닉네임 대신 아이콘 사용') ?>
				<select name="cf_use_member_icon" id="cf_use_member_icon">
					<option value="0"<?php echo get_selected($config['cf_use_member_icon'], '0') ?>>미사용
					<option value="1"<?php echo get_selected($config['cf_use_member_icon'], '1') ?>>아이콘만 표시
					<option value="2"<?php echo get_selected($config['cf_use_member_icon'], '2') ?>>아이콘+이름 표시
				</select>
			</td>
			<th scope="row"><label for="cf_icon_level">아이콘 업로드 권한</label></th>
			<td><?php echo get_member_level_select('cf_icon_level', 1, 9, $config['cf_icon_level']) ?> 이상</td>
		</tr>
		<tr style="display: none;">
			<th scope="row"><label for="cf_member_icon_size">회원아이콘 용량</label></th>
			<td><input type="text" name="cf_member_icon_size" value="<?php echo $config['cf_member_icon_size'] ?>" id="cf_member_icon_size" class="frm_input" size="10"> 바이트 이하</td>
			<th scope="row">회원아이콘 사이즈</th>
			<td>
				<label for="cf_member_icon_width">가로</label>
				<input type="text" name="cf_member_icon_width" value="<?php echo $config['cf_member_icon_width'] ?>" id="cf_member_icon_width" class="frm_input" size="2">
				<label for="cf_member_icon_height">세로</label>
				<input type="text" name="cf_member_icon_height" value="<?php echo $config['cf_member_icon_height'] ?>" id="cf_member_icon_height" class="frm_input" size="2">
				픽셀 이하
			</td>
		</tr> 
		<tr>
			<th scope="row"><label for="cf_prohibit_id">아이디,닉네임 금지단어</label></th>
			<td>
				<?php echo help('회원아이디, 닉네임으로 사용할 수 없는 단어를 정합니다. 쉼표 (,) 로 구분') ?>
				<textarea name="cf_prohibit_id" id="cf_prohibit_id" rows="5"><?php echo $config['cf_prohibit_id'] ?></textarea>
			</td>
			<th scope="row"><label for="cf_prohibit_email">입력 금지 메일</label></th>
			<td>
				<?php echo help('입력 받지 않을 도메인을 지정합니다. 엔터로 구분 ex) hotmail.com') ?>
				<textarea name="cf_prohibit_email" id="cf_prohibit_email" rows="5"><?php echo $config['cf_prohibit_email'] ?></textarea>
			</td>
		</tr> 
		</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>
 
 
<section id="anc_010" style="display:none;">
	<h2 class="h2_frm">레이아웃 추가설정</h2>
	<?php echo $pg_anchor; ?>
	<div class="local_desc02 local_desc">
		<p>기본 설정된 파일 경로 및 script, css 를 추가하거나 변경할 수 있습니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>레이아웃 추가설정</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row"><label for="cf_add_script">추가 script, css</label></th>
			<td>
				<?php echo help('HTML의 &lt;/HEAD&gt; 태그위로 추가될 JavaScript와 css 코드를 설정합니다.<br>관리자 페이지에서는 이 코드를 사용하지 않습니다.') ?>
				<textarea name="cf_add_script" id="cf_add_script"><?php echo get_text($config['cf_add_script']); ?></textarea>
			</td>
		</tr>
		</tbody>
		</table>
	</div>
</section>

<?php //echo $frm_submit; ?>
 
</form>

<script>
$(function(){
	<?php
	if(!$config['cf_cert_use'])
		echo '$(".cf_cert_service").addClass("cf_cert_hide");';
	?>
	$("#cf_cert_use").change(function(){
		switch($(this).val()) {
			case "0":
				$(".cf_cert_service").addClass("cf_cert_hide");
				break;
			default:
				$(".cf_cert_service").removeClass("cf_cert_hide");
				break;
		}
	});

	$(".get_theme_confc").on("click", function() {
		var type = $(this).data("type");
		var msg = "기본환경 스킨 설정";
		if(type == "conf_member")
			msg = "기본환경 회원스킨 설정";

		if(!confirm("현재 테마의 "+msg+"을 적용하시겠습니까?"))
			return false;

		$.ajax({
			type: "POST",
			url: "./theme_config_load.php",
			cache: false,
			async: false,
			data: { type: type },
			dataType: "json",
			success: function(data) {
				if(data.error) {
					alert(data.error);
					return false;
				}

				var field = Array('cf_member_skin', 'cf_mobile_member_skin', 'cf_new_skin', 'cf_mobile_new_skin', 'cf_search_skin', 'cf_mobile_search_skin', 'cf_connect_skin', 'cf_mobile_connect_skin', 'cf_faq_skin', 'cf_mobile_faq_skin');
				var count = field.length;
				var key;

				for(i=0; i<count; i++) {
					key = field[i];

					if(data[key] != undefined && data[key] != "")
						$("select[name="+key+"]").val(data[key]);
				}
			}
		});
	});
});

function fconfigform_submit(f)
{
	f.action = "./config_form_update.php";
	return true;
}
</script>

<?php 

include_once ('./admin.tail.php');
?>
