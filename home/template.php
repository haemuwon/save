

<? if($str=='join'){
	if(!$is_member && $config['cf_1']) { 
//join?>

<a href="<?=G5_BBS_URL?>/register.php" class="ui-btn">회원가입</a>
<? } 
}
//join 끝
?> 
<? if($str=='setting'){
	if($is_member && !$is_admin) { 
//setting ?>
<a href="<?=G5_BBS_URL?>/member_confirm.php?url=register_form.php" class="ui-btn">정보수정</a>
<? } 
}
//setting 끝
?>
<? if($str=='admin'){
	if($is_admin) { 
//setting ?>
<a href="<?=G5_URL?>/adm/" class="ui-btn admin" target="_blank">관리자</a>
<? } 
}
//setting 끝
?>
<? if($str=='more_start'){ 
//more start ?>
	<a href="#" class="more_open_close">열기</a><div class="more_content_box" style="display: none;"> 
<?//more start 끝
	}
?>
<? if($str=='div_start') { 
	//div start 
	?>
 <div class="theme-box" style="margin: 0 auto;">
<? } 
 //div start 끝
?>

<? if($str=='div_end') { 
	//div end
	?></div><? } 
 //div end 끝
?>

<? if($str=='login') { 
	if(!$is_member) { 
//login ?>
<a href="<?=G5_BBS_URL?>/login.php" class="ui-btn point">로그인</a>
<? }  
}
//login 끝
?>


<? if($str=='logout') { 
	if($is_member) { 
//logout ?>
<a href="<?=G5_BBS_URL?>/logout.php" class="ui-btn">로그아웃</a>
<? }
}
//logout 끝
?>