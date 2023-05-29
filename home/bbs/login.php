<?php
include_once('./_common.php');

if( function_exists('social_check_login_before') ){
    $social_login_html = social_check_login_before();
}
if(strstr($url, 'adm'))  $g5['title'] = '관리자 로그인';
else $g5['title'] = '로그인';
include_once('./_head.sub.php');

//$url = $_GET['url'];
if(!$url)
$url = $_SERVER['HTTP_REFERER'];//@200907
else $url=$_GET['url'];

// url 체크
check_url_host($url);
// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url($url);
    else
$main_link=get_main_link();
        goto_url($main_link);
}

$login_url        = login_url($url);
$login_action_url = G5_HTTPS_BBS_URL."/login_check.php";
 
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/login.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/style.basic.css">', 0);


/*********** Logo Data ************/
$logo = get_logo('pc');
$m_logo = get_logo('mo');

$logo_data = "";
if($logo)		$logo_data .= "<img src='".$logo."' />";
/*********************************/


?>



<div class="login-box">

<?
// 등록된 로고 파일이 있을 경우에만 출력 한다.
if($logo_data) { ?>
	<div class="login-logo">
		<?=$logo_data?>
	</div>
	<hr class="padding" />
<? } ?>

	<form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
		<input type="hidden" name="url" value='<?php echo $login_url ?>'>
		<input type="hidden" name="auto_login" value="">
		<fieldset>
			<input type="text" name="mb_id" id="login_id" autocomplete="username" required class="frm_input required" size="20" maxLength="20" placeholder="아이디">
		</fieldset>
		<fieldset>
			<input type="password" autocomplete="current-password" name="mb_password" id="login_pw" required class="frm_input required" size="20" maxLength="20" placeholder="비밀번호">
		</fieldset>
		<fieldset>
			<button type="submit" class="ui-btn point full">로그인</button>
		</fieldset>
	</form> 
		<a href="<?=G5_URL?>" class="ui-btn full" style="margin-top:5px;">메인으로</a> 
	<!--<?if($config['cf_1']==1){?>
		<fieldset style="padding-top:5px;">
			<a href="<?=G5_BBS_URL?>/register.php" class="ui-btn full">JOIN</a>
		</fieldset><?}?>
	-->
</div>


<script>
function flogin_submit(f)
{
	var r = confirm("자동로그인을 하시겠습니까?\n사용시 한달간 쿠키가 저장됩니다.");
	if(r==true) f.auto_login.value=1;
	
    return true;
}
</script>
<!-- } 로그인 끝 -->  

<?
include_once('./_tail.sub.php');
?>
