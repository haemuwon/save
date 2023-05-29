<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//-- 리스트 정렬을 임의로 조정 하는 기능을 합니다.
include_once($board_skin_path.'/_config.php');
include_once($board_skin_path.'/_setting.php');
include_once($board_skin_path.'/_setting.option.php');
include_once($board_skin_path.'/list.order.skin.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/common.animate.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.list.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.list.animate.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$is_notice = false;

/* RhDophyta edit (2022-12-06) */
$board_skin_theme = $ch_display_skin_config['theme'];
$slide_wh = 2;
$slide_radius = 0;
$slide_class = "";
if($board_skin_theme == "circular") {$slide_radius = 0.5; $slide_class = "circle";}
if($board_skin_theme == "ddeeboo") {$slide_radius = "4px"; $slide_wh = 1.3; $slide_class = "ddeeboo";}
if($board_skin_theme == "ddeeboo bitmap") {$slide_radius = 0; $slide_wh = 1.3; $slide_class = "ddeeboo bitmap";}

$is_bitmap = substr($board_skin_theme, strlen($board_skin_theme)-6, 6) == "bitmap" ? "bitmap" : "";
?>

<style>
:root {
	--margin: <?=$ch_display_skin_config['list_grid_margin']?>px;
	--gutter: <?=$ch_display_skin_config['list_grid_gutter']?>px;

	<?
		if($board_skin_theme == "circular") { echo "--font-family-display: {$ch_display_skin_config["font-serif"]};"; } 
		else { echo "--font-family-display: {$ch_display_skin_config["font-gothic"]};"; } 

		echo "--font-family-basic: {$ch_display_skin_config["font-basic"]};";
		echo "--font-family-serif: {$ch_display_skin_config["font-serif"]};";
		echo "--font-family-gothic: {$ch_display_skin_config["font-gothic"]};";
	?>
}

.list__grid {
	grid-template-columns: repeat(<?=$ch_display_skin_config["list_grid_column"]?>, 1fr);
}
</style>

<hr class="padding">
<hr class="padding">
<hr class="padding">
<hr class="padding">
<div class="list__body">
	<ul class="list__grid character-card-list content-grid">
		<?
			for($i=0; $i<count($list); $i++) {
				unset($ch, $classname_rand_suffix);
				if($list[$i]['is_notice']) {
					$is_notice = true;
					$classname_rand_suffix = uniqid("pair{$i}_");
					include($board_skin_path."/list.couple.skin.php");
				} else {
					// if($is_notice) { break; }
					$ch = $list[$i];
					$classname_rand_suffix = uniqid("ch{$i}_");
					if(!$is_notice) include($board_skin_path."/list.character.skin.php");
				}
			}
		
		if (count($list) == 0) { echo "<li class=\"empty_list\">등록된 캐릭터가 없습니다.</li>"; } ?>
	</ul>
</div>

<script>
$(document).ready(function(){
	$('.auto-contrast').each(function(k){
		let item = $(this);
		let color = item.attr('data-label');

		let foreColor = autoContrast(color);
		let backColor = autoContrast(foreColor);

		// // foreColor == light인 경우: filter invert
		// if(foreColor == "#fafafa") { item.find('img').css({'filter': 'invert()'}); }
		item.find('div').css({'color':foreColor});

		// // auto-contrast-reverse 처리
		// let itemReverse = item.closest('.board-list-item').find('.auto-contrast-reverse');
		// itemReverse.find('div').css({'color':backColor});
		// itemReverse.find('a').css({'background-color':backColor});
	});

	<? if($config['cf_bgm']) { ?>
		// Read BGM status (2022-12-12)
		const root = window.parent.document.getElementById('bgm_frame');
		if (root) {
			let href;
			const bgmOnBtn = window.parent.document.getElementsByClassName('control-bgm-play')[0];
			if (bgmOnBtn) {
				if ($(bgmOnBtn).css("display") == "none") {
					// BGM is on
					href = "<?=G5_URL?>/bgm.php?action=play";
				} else {
					// BGM is off
					href = "<?=G5_URL?>/bgm.php";
				}
			} else {
				// If unable to detect bgm button status, Force BGM play (2023-01-19)
				href = "<?=G5_URL?>/bgm.php?action=play";
			}
			if (!root.src.endsWith("/bgm.php?action=play")) { root.src = href; }
		}
	<? } ?>
});

$(window).on("load", function () {
	// Page onload Animation
	$('.list__grid__item').delay(1000).each(function(k){
		let item = $(this);
		setTimeout(function(){ item.addClass('on') }, 100*k);
	});
	$('.list__grid__item__full').delay(1000).each(function(k){
		let item = $(this);
		setTimeout(function(){ item.addClass('on') }, 1000*k);
	});
	styleSlide();
});

$(window).resize(function () {
	styleSlide();
});

function autoContrast (hex)	{
	threshold = 130; /* about half of 256. Lower threshold equals more dark text on dark background  */

	hRed = hexToR(hex);
	hGreen = hexToG(hex);
	hBlue = hexToB(hex);

	function hexToR(h) {return parseInt((cutHex(h)).substring(0,2),16)}
	function hexToG(h) {return parseInt((cutHex(h)).substring(2,4),16)}
	function hexToB(h) {return parseInt((cutHex(h)).substring(4,6),16)}
	function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h}
	
	cBrightness = ((hRed * 299) + (hGreen * 587) + (hBlue * 114)) / 1000;
	if (cBrightness > threshold){return "#121212";} else { return "#fafafa";}
}

function styleSlide () {
	const width = parseInt($(".list__grid__item").css("width"));
	/* Rhd edit */
	const ratio = <?=$slide_wh?>;
	let radius = "<?=$slide_radius?>";
	if (radius == Number(radius)) { // 숫자일 경우
		radius = Number(radius)*width+"px";
	}
	const className = "<?=$slide_class?>";
	$(".list__grid__item").css({
		"height": ratio*width+"px",
		"border-radius": radius,
	}).addClass(className);
}
</script>