<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php'); 

/*********** Logo Data ************/
$logo = get_logo('pc');
$m_logo = get_logo('mo');

$logo_data = "";
if(!$logo && !$m_logo)$logo_data=$config['cf_title'];
else{
if($logo)		$logo_data .= "<img src='".$logo."' ";
if($m_logo)		$logo_data .= "class='only-pc' /><img src='".$m_logo."' class='not-pc'";
if($logo_data)	$logo_data.= " />";
}
/*********************************/

$main_link=get_main_link();
?>

	<div class="night">
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
	</div>

	<div class="night night2">
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
		<div class="shooting_star"></div>
	</div>
</div>

<!-- 헤더 영역 -->
<header id="header">
	<div class="fix-layout">
		<!-- 로고 영역 : PC 로고 / 모바일 로고 동시 출력 - 디자인 사용을 체크하지 않을 시, 제대로 출력되지 않을 수 있습니다. -->
		<!-- 관리자 기능을 사용하지 않고 로고를 넣고 싶을 시, < ? = $ log_data ? > 항목을 제거 하고 <img> 태그를 넣으세요. -->
		<?if($config['cf_logo_use']!='N'){?>
		<h1 id="logo">
			<a href="<?=$main_link?>">
				<?=$logo_data?>
			</a>
		</h1>
		<?}?>
		<!-- 로고를 삭제하고 싶을 경우 위의 <h1 ... </h1> 부분을 삭제하시면 됩니다 -->

		<?include(G5_PATH."/menu.php");?>
		<script src="https://kit.fontawesome.com/aa58923499.js" crossorigin="anonymous"></script>

	</div>
</header>
<!-- // 헤더 영역 -->

<section id="body">
	<div class="fix-layout" style="max-width:1000px;"> 
<hr class="padding" />
