<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

// 불법접근을 막도록 토큰생성
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);
set_session("ss_cert_no",   "");
set_session("ss_cert_hash", "");
set_session("ss_cert_type", "");

 

if ($w == "") {

    // 회원 로그인을 한 경우 회원가입 할 수 없다
    // 경고창이 뜨는것을 막기위해 아래의 코드로 대체
    // alert("이미 로그인중이므로 회원 가입 하실 수 없습니다.", "./");
    if ($is_member) {
        goto_url(G5_URL);
    }
	if(!$config['cf_1'])
		alert('회원가입 기간이 아닙니다.', G5_URL);

    // 리퍼러 체크
    referer_check();

    if ($config['cf_stipulation_use'] && !$_POST['agree']) {
        alert('회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
    }

    if ($config['cf_privacy_use'] && !$_POST['agree2']) {
        alert('개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
    }

    $agree  = preg_replace('#[^0-9]#', '', $_POST['agree']);
    $agree2 = preg_replace('#[^0-9]#', '', $_POST['agree2']);

    $member['mb_birth'] = '';
    $member['mb_sex']   = '';
    $member['mb_name']  = '';
    if (isset($_POST['birth'])) {
        $member['mb_birth'] = $_POST['birth'];
    }
    if (isset($_POST['sex'])) {
        $member['mb_sex']   = $_POST['sex'];
    }
    if (isset($_POST['mb_name'])) {
        $member['mb_name']  = $_POST['mb_name'];
    }

    $g5['title'] = '회원 가입';

} else if ($w == 'u') {

    if ($is_admin)
        alert('관리자의 회원정보는 관리자 화면에서 수정해 주십시오.', G5_URL);

    if (!$is_member)
        alert('로그인 후 이용하여 주십시오.', G5_URL);

    if ($member['mb_id'] != $_POST['mb_id'])
        alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.');

    /*
    if (!($member[mb_password] == sql_password($_POST[mb_password]) && $_POST[mb_password]))
        alert("비밀번호가 틀립니다.");

    // 수정 후 다시 이 폼으로 돌아오기 위해 임시로 저장해 놓음
    set_session("ss_tmp_password", $_POST[mb_password]);
    */

    if ($_POST['mb_password']) {
        // 수정된 정보를 업데이트후 되돌아 온것이라면 비밀번호가 암호화 된채로 넘어온것임
        if ($_POST['is_update'])
            $tmp_password = $_POST['mb_password'];
        else
            $tmp_password = get_encrypt_string($_POST['mb_password']);

        if ($member['mb_password'] != $tmp_password)
            alert('비밀번호가 틀립니다.');
    }

    $g5['title'] = '회원 정보 수정';

    set_session("ss_reg_mb_name", $member['mb_name']);
    set_session("ss_reg_mb_hp", $member['mb_hp']);

    $member['mb_email']       = get_text($member['mb_email']);
    $member['mb_homepage']    = get_text($member['mb_homepage']);
    $member['mb_birth']       = get_text($member['mb_birth']); 
    $member['mb_profile']     = get_text($member['mb_profile']);
    $member['mb_1']           = get_text($member['mb_1']);
    $member['mb_2']           = get_text($member['mb_2']);
    $member['mb_3']           = get_text($member['mb_3']);
    $member['mb_4']           = get_text($member['mb_4']);
    $member['mb_5']           = get_text($member['mb_5']);
    $member['mb_6']           = get_text($member['mb_6']);
    $member['mb_7']           = get_text($member['mb_7']);
    $member['mb_8']           = get_text($member['mb_8']);
    $member['mb_9']           = get_text($member['mb_9']);
    $member['mb_10']          = get_text($member['mb_10']);
} else {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

include_once('./_head.php');

// 회원아이콘 경로
$mb_icon_path = G5_DATA_PATH.'/member/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';
$mb_icon_url  = G5_DATA_URL.'/member/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';

// 회원이미지 경로
$mb_img_path = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';
$mb_img_url  = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.$member['mb_id'].'.gif';

$register_action_url = G5_HTTPS_BBS_URL.'/register_form_update.php';
$req_nick = !isset($member['mb_nick_date']) || (isset($member['mb_nick_date']) && $member['mb_nick_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400)));
$required = ($w=='') ? 'required' : '';
$readonly = ($w=='u') ? 'readonly' : '';

$agree  = preg_replace('#[^0-9]#', '', $agree);
$agree2 = preg_replace('#[^0-9]#', '', $agree2);
 
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/style.basic.css">', 0);
?>

<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
<script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>


<div id="member_page">
<hr class="padding">
	<h1 class="member-title">
		<strong>계정 정보 <?=$w == '' ? "등록" : "수정"?></strong>
		<span>《 Community Register Form 》</span>
	</h1>

	<div class="member-contents register-pannel">
		<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
			<input type="hidden" name="w" value="<?php echo $w ?>">
			<input type="hidden" name="url" value="<?php echo $urlencode ?>">
			<input type="hidden" name="agree" value="<?php echo $agree ?>">
			<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
			<input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
			<input type="hidden" name="cert_no" value="">
			<input type="hidden" name="mb_open_default" value="<?php echo $member['mb_open'] ?>">
			<input type="hidden" name="mb_open" value="1">

			<?php if (isset($member['mb_sex'])) {  ?><input type="hidden" name="mb_sex" value="<?php echo $member['mb_sex'] ?>"><?php }  ?>
			<?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 닉네임수정일이 지나지 않았다면  ?>
			<input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
			<input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
			<?php }  ?>

			<table class="member-form theme-form">
				<colgroup>
					<col style="width: 110px;" />
					<col />
				</colgroup>
				<tbody>
					<tr>
						<th>아이디</th>
						<td>
							<input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?> class="frm_input <?php echo $required ?> <?php echo $readonly ?>" minlength="3" maxlength="20">
							<span id="msg_mb_id"></span>
						</td>
					</tr>
					<tr>
						<th scope="row">비밀번호<?=$w!='' ? " 변경":"";?></th>
						<td><input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?> class="frm_input <?php echo $required ?>" minlength="3" maxlength="20"></td>
					</tr>
					<tr>
						<th scope="row">비밀번호 확인</th>
						<td><input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?> class="frm_input <?php echo $required ?>" minlength="3" maxlength="20"></td>
					</tr>
				</tbody>
			</table>

			<table class="member-form theme-form">
				<colgroup>
					<col style="width: 110px;" />
					<col />
				</colgroup>
				<tbody>
					<tr>
						<th scope="row">닉네임</th>
						<td>
							<input type="text" name="mb_nick" value="<?php echo isset($member['mb_nick'])?get_text($member['mb_nick']):''; ?>" required class="frm_input required" maxlength="10">
						</td>
					</tr>
					<tr>
						<th scope="row">E-mail</th>
						<td>
							<span class="frm_info">
							<?php if ($config['cf_use_email_certify']) {  ?>
								<?php if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; }  ?>
								<?php if ($w=='u') { echo "E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; }  ?><?php }  ?>
							</span>
							<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
							<input type="text" name="mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" id="reg_mb_email" required class="frm_input email full" maxlength="100">
							
						</td>
					</tr>
				</tbody>
			</table> <br>
			<div class="btn_confirm txt-center">
				<button type="submit" id="btn_submit" class="ui-btn point" accesskey="s"><?php echo $w==''?'회원가입':'정보수정'; ?></button>
				<a href="<?php echo G5_URL ?>" class="ui-btn">취소</a>
			</div>
		</form> 
</div>
	

<script>
	// submit 최종 폼체크
	function fregisterform_submit(f)
	{
		// 회원아이디 검사
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}

		if (f.w.value == "") {
			if (f.mb_password.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password.value != f.mb_password_re.value) {
			alert("비밀번호가 같지 않습니다.");
			f.mb_password_re.focus();
			return false;
		}

		if (f.mb_password.value.length > 0) {
			if (f.mb_password_re.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password_re.focus();
				return false;
			}
		}  
		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}
</script>

</div>
<!-- } 회원정보 입력/수정 끝 -->

<?
include_once('./_tail.php');
?>