<?php
include_once('./_common.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/style.basic.css">', 0);

if (isset($_SESSION['ss_mb_reg']))
    $mb = get_member($_SESSION['ss_mb_reg']);

// 회원정보가 없다면 초기 페이지로 이동
if (!$mb['mb_id'])
    goto_url(G5_URL);

$g5['title'] = '회원가입 완료';
include_once('./_head.php');?>


<div id="member_page"> 
<hr class="padding">
	<h1 class="member-title txt-center">
		<strong>계정 생성 완료</strong> 
		<span>《 Community Account Set 》</span>
	</h1> 

	<div class="member-contents register-pannel">

		<section>
			<h2 class="txt-center">정보관리 안내</h2>
			<div class="theme-box">
				<p><strong class="txt-point">"<?php echo get_text($mb['mb_name']); ?>"</strong>님의 <strong><?=$config['cf_title']?></strong> 가입을 진심으로 축하합니다.</p>
				<p>회원님의 비밀번호는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.</p>
				<p>아이디, 비밀번호 분실시에는 총괄에게 문의해 주시길 바랍니다.</p>
			</div>
		</section> 

		<div class="ui-button-box txt-center">
			<button class="ui-btn point" onclick="location.href='<?=G5_URL?>';">메인화면으로</button>
		</div>
	</div>
</div>


<?
include_once('./_tail.php');
?>