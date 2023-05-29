<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

$color_bak = get_style('color_bak');
$color_bak = hex2rgba($color_bak['cs_value'], $color_bak['cs_etc_1']);
$color_bak_rgb = explode(",", $color_bak);
// PHP < 5.4 update (2022-12-12)
$color_bak_r = explode("(",$color_bak_rgb[0]);
$color_bak_b = explode(")",$color_bak_rgb[2]);
$color_bak_rgb = implode(",", array($color_bak_r[1], $color_bak_rgb[1], $color_bak_b[0]));
$color_default = get_style('color_default');
$color_default = hex2rgba($color_default['cs_value'], $color_default['cs_etc_1']);
$color_default_rgb = explode(",", $color_default);
// PHP < 5.4 update (2022-12-12)
$color_default_r = explode("(",$color_default_rgb[0]);
$color_default_b = explode(")",$color_default_rgb[2]);
$color_default_rgb = implode(",", array($color_default_r[1], $color_default_rgb[1], $color_default_b[0]));
?>
<style>
	:root {
		<?php
			if($ch["theme"]["font"]=="serif") { echo "--font-family-display: {$ch_display_skin_config["font-serif"]};"; } 
			else { echo "--font-family-display: {$ch_display_skin_config["font-gothic"]};"; } 

			echo "--font-family-basic: {$ch_display_skin_config["font-basic"]};";
			echo "--font-family-serif: {$ch_display_skin_config["font-serif"]};";
			echo "--font-family-gothic: {$ch_display_skin_config["font-gothic"]};";

			echo "--background-color-theme:{$color_bak};";
			echo "--background-color-theme-rgb:{$color_bak_rgb};";
			echo "--foreground-color-theme:{$color_default};";
			echo "--foreground-color-theme-rgb:{$color_default_rgb};";

			echo "--background-blur:{$ch_display_skin_config["bg-blur"]}px;";
		?>

		--theme-color: <?php echo $ch["theme"]["color"]; ?>;
	}

	<?php if (substr($ch["theme"]["mode"], 0, 4) != "home") { ?>
		html,
		body,
		.admin-preview-box,
		html.single:before {
			background-color: unset !important;
			background-image: none !important;
			background: none !important;
		}
		
		.view__background {
			background-color: var(--background-color);
			background: var(--background);
		}		
	<?php } ?>

	html[data-theme='theme'],
	html[data-theme='theme'] ::before,
	html[data-theme='theme'] ::after {
		--foreground-color: var(--foreground-color-theme);
		--foreground-color-rgb: var(--foreground-color-theme-rgb);

		--background-color: var(--background-color-theme);
		--background-color-c: rgba(var(--background-color-theme-rgb), .7);
		--background-color-8: rgba(var(--background-color-theme-rgb), .5);
		--background-color-3: rgba(var(--background-color-theme-rgb), .3);

		--foreground-600: rgba(var(--foreground-color-rgb), .3);
		--foreground-400: rgba(var(--foreground-color-rgb), .2);
		--foreground-200: rgba(var(--foreground-color-rgb), .1);
	}
	::selection {
		color: initial;
		background-color: <?php echo $ch["theme"]["color"]; ?>;
	}
	
	.ch-txt-point,
	.ch-txt-point *		{ color: var(--theme-color); }
	.ch-bg-point		{ background-color: var(--theme-color) !important; --bg-color: var(--theme-color); }
	.ch-border-point	{ --border-color: var(--theme-color); }
	.ch-border-point ::before,
	.ch-border-point ::after	{ --border-color: var(--theme-color); }
	.ch-shadow-point	{ --shadow-color: var(--theme-color); }

	.article-content__container span.ui-spoiler		{ color: var(--theme-color) !important;background-color: var(--theme-color) !important; }
</style>

<? // View page ?>
<div class="view__mask">
	<div class="view__mask__area-hide js-view__mask__area-hide off" onclick="hideContent();"></div>
	<div class="view__mask__area-loading js-view__mask__area-loading">
		<div class="view__mask__area-loading__container js-view__mask__area-loading__container">
			<div class="view__mask__area-loading__text"><?php echo $ch["article"]["line"]; ?></div>
		</div>
	</div>
</div>
<div class="view__header">
	<div class="view__copyright">
		<a class="view__copyright__btn" href="https://rhdophyta.postype.com/" target="_blank">RhD</a>
	</div>
	<div class="view__header__container animate-box">
		<!-- <div class="view__header__text animate-toggle">PROFILE</div> -->
	</div>
	<div class="view__btn-back">
		<a class="animated-arrow" href="<?php echo $back_url; ?>" <?php echo $back_target; ?> onclick="locateBack('<?php echo $back_url; ?>');">
			<span class="animated-arrow__arrow--left">
				<span class="animated-arrow__shaft--left"></span>
			</span>
			<span class="animated-arrow__container">
				<span class="animated-arrow__text">Back</span>
				<span class="animated-arrow__arrow--right">
					<span class="animated-arrow__shaft--right"></span>
				</span>
			</span>
		</a>
	</div>
</div>
<div class="view__body">
    <div class="view__body__area-visual <?php echo $check_body ? "js-measure" : ""; ?>">
		<div class="view__body__area-visual__container animate-onload <?php echo $body_className; ?>">
			<div class="view__body__area-visual__img-holder">
				<?php echo $check_body ? "<img class=\"view__body__area-visual__img\" src=\"{$check_body}\" />" : ""; ?>
			</div>
		</div>
	</div>
	<!-- 버튼 바 영역 -->
	<div class="view__body__nav-btn only-pc">
		<ul class="nav-btn">
			<?php echo nav_btn_song($ch); ?>
			<?php for ($i=1; $i<6; $i++) {
				$key = "button{$i}";
				echo nav_btn($wr_option2, $key);
			} ?>
		</ul>
	</div>
	<div class="view__body__grid">
		<!-- 클래스 영역 -->
		<div class="view__body__container-class">
			<?php echo article_class($ch); ?>
		</div>
		<!-- 요약 영역 -->
		<div class="view__body__container-abstract animate-toggle">
			<?php echo article_line($ch); ?>
			<div class="view__body__container-summary">
				<?php echo article_summary($ch); ?>
			</div>
		</div>
		<div class="view__body__grid-stack--bottom-left">
			<!-- 코드네임 영역 -->
			<div class="view__body__container-codename animate-toggle">
				<?php echo article_emblem($ch); ?>
				<?php echo article_codename($ch); ?>
			</div>
			<!-- 프로필 영역 -->
			<div class="view__body__container-profile animate-toggle">
				<article class="article-profile animate-onload">
					<?php echo article_profile($ch); ?>
					<!-- <div class="article-profile__thumb-container only-pc">
						<?php //echo article_profile_thumb($ch); ?>
						<?php //echo article_profile_status($ch); ?>
					</div> -->
					<!-- <div class="article-profile__content">
						<div class="article-profile__title animate-box">
							<div class="article-profile__title__text animate-onload"><?php //echo $ch['article']['phrase']; ?></div>
						</div>
					</div> -->
					<div class="article-profile__details">
						<!-- 프로필 이름 영역 -->
						<!-- <div class="article-profile__name">
							<div class="article-profile__name--big animate-box">
								<div class="article-profile__name--big__text animate-onload"><?php //echo $ch['ch_name']?></div>
							</div>
							<div class="article-profile__name--small animate-box">
								<div class="article-profile__name--small__text animate-onload">
									<?php //echo $ch['article']['name_eng']?>
								</div>
							</div>
						</div> -->
						<?php
							echo article_profile_detail($ch, "keyword");
							echo article_profile_detail($ch, "dlois");
							echo article_profile_detail($ch, "syndrome");
							echo article_profile_detail($ch, "phase");
							echo article_profile_detail($ch, "magician");
						?>
					</div>
				</article>
			</div>
		</div>
		<!-- 스테이터스 영역 -->
		<div class="view__body__container-status animate-toggle">
			<article class="article-status animate-onload only-pc">
				<!-- <header class="article-status__header">
					<span class="article-status__header__text">스테이터스 Status</span>
				</header> -->
				<?php
					echo article_status_bar($ch);
					// echo article_status($ch, "status", "%02d", "article-status__list", "grid-auto-col");
				?>
			</article>
		</div>
		<!-- 옷장 영역 -->
		<div class="view__body__container-closet animate-toggle">
			<?php if($ch['source']['body'] != "") { 
				// 전신 리스트 출력 (단, 추가로 등록한 전신이 있을 경우에만)
				include_once($board_skin_path."/view_body.skin.php");
				}
			?>
		</div>
	</div>
	<!-- 앰블렘 영역 -->
	<!-- <div class="view__body__container-emblem">
		<?php //echo article_emblem($ch); ?>
	</div> -->
	<!-- 설정 탭 네비게이션 영역 -->
	<div class="view__body__nav-content">
		<ul class="nav-content">
			<?php
				echo article_content_tab("basic", "기본 정보");
				if ($ch_subcontent_backstory) echo article_content_tab("backstory", "백스토리");
				foreach ($ch['detail'] as $key => $content) {
					echo article_content_tab($key, $content['name']);
				}
				if ($update_href) echo article_content_tab("edit", "관리", "edit");
			?>
		</ul>
	</div>
    <div class="view__body__area-content">
		<div class="view__body__area-content__mask">
			<? include_once($board_skin_path."/view_content.skin.php"); ?>
		</div>
	</div>
	<?php if($update_href) { ?>
	<div class="view__body__area-edit">
		<div class="view__body__area-content__mask">
			<? include_once($board_skin_path."/view_edit.skin.php"); ?>
		</div>
	</div>
	<?php } ?>
</div>
<div class="view__footer">
	<div class="view__footer__container--bottom-right only-pc">
		<!-- <div class="article__owner">OWNER 야생김</div> -->
	</div>
</div>
<div class="view__background">
	<div class="view__background__overlay animate-onload ch-border-point only-pc"></div>
	<div class="view__background__mask animate-onload"></div>
	<div class="view__background__container animate-onload <?php echo $ch["theme"]["mode"] == "color"?"ch-bg-point":""; ?>" style="background-image:url('<?php echo $wr_option2['bg']; ?>');">
		<?php echo $ch["theme"]["bg"]==""?"":'<video loop muted autoplay><source src="'.$ch["theme"]['bg'].'"></video>'; ?>
	</div>
</div>
<div class="plugin-holder" style="display:none;">
<?php 
	/* Avocado Edition Extensions (2022-12-21) */
	$extend_file = array();
	$tmp = dir(G5_PATH."/plugin/board");
	if ($tmp) {
		while ($entry = $tmp->read()) {
			// php 파일만 include 함
			if (preg_match("/(\.php)$/i", $entry))
				$extend_file[] = $entry;
		}
		if(!empty($extend_file) && is_array($extend_file)) {
			natsort($extend_file);
			foreach($extend_file as $file) {
				include_once(G5_PATH."/plugin/board/".$file);
			}
		}
	}
	unset($extend_file);
?>
</div>

<!-- 스크립트 영역 -->
<script type="text/javascript" src="<?php echo $board_skin_url; ?>/js/common.js"></script>
<script>
let onloadTimer;
let onloadTime = 0;
let onloadFlag = false;

function onloadTimeCheck () {
	// Add everytime when called
	onloadTime += 500;
	if (onloadTime >= <?=$ch_display_skin_config["onload-delay"]?> & onloadFlag) {
		$(".js-view__mask__area-loading").addClass("off");
		$(".view__background .animate-onload").each(function (k, item) {animateOnLoad(k, item, 800, 200)});
		$(".view__body__area-visual .animate-onload").each(function (k, item) {animateOnLoad(k, item, 1600)});
		$(".view__body__container-abstract .animate-onload").each(function (k, item) {animateOnLoad(k, item, 2000)});
		$(".view__body__container-codename .animate-onload").each(function (k, item) {animateOnLoad(k, item, 2400, 100)});
		$(".view__body__container-profile .animate-onload").each(function (k, item) {animateOnLoad(k, item, 2600, 100)});
		$(".view__body__container-status .animate-onload").each(function (k, item) {animateOnLoad(k, item, 3000, 100)});
		$(".view__body__nav-content .animate-onload").each(function (k, item) {animateOnLoad(k, item, 3600)});
		// $(".view__body__container-emblem .animate-onload").each(function (k, item) {animateOnLoad(k, item, 3800, 100)});
		$(".view__body__container-closet .animate-onload").each(function (k, item) {animateOnLoad(k, item, 3900, 100)});
		$(".view__body__nav-btn .animate-onload").each(function (k, item) {animateOnLoad(k, item, 4000)});
		$(".view__body__container-class .animate-onload").each(function (k, item) {animateOnLoad(k, item, 4200)});
		$(".view__header .animate-onload").each(function (k, item) {animateOnLoad(k, item)});
		$(".view__footer").fadeIn(1000);
		measureHeight();
		measureImage();
		clearInterval(onloadTimer);
	}
}

$(document).ready(function() {
	const colorModeConfig = "<?php echo $ch['theme']['mode']; ?>";
	const colorTone = autoContrast("<?php echo $ch['theme']['color']; ?>");
	const foreColor = (colorTone == "light") ? "#121212" : "#fafafa";
	const backColor = (colorTone == "light") ? "#eeeeee" : "#333333";
	let colorMode;
	// Set colorMode
	if (colorModeConfig == "color") {
		// Shadow styling 2022-12-07
		const shadowed = document.querySelector(".ch-shadow-point");
		if (shadowed) shadowed.style.setProperty("--shadow-color", backColor);
		// Border styling 2023-01-17
		const bordered = document.getElementsByClassName("ch-border-point");
		for (let item of bordered) item.style.setProperty("--border-color", backColor);
		// Bg styling 2023-01-17
		const colored = document.getElementsByClassName("ch-bg-point");
		for (let item of colored) item.style.setProperty("--bg-color", backColor);
		colorMode = colorTone;
	} else if (colorModeConfig.startsWith("home")) {
		// Home mode styling
		if (colorModeConfig == "home") colorMode = "theme";
		else colorMode = colorModeConfig.substr(5); // light or dark
	} else {
		colorMode = colorModeConfig;
	}
	document.documentElement.dataset.theme = colorMode;
	document.documentElement.style.setProperty("--ch-forecolor", foreColor);
	document.documentElement.style.setProperty("--ch-backcolor", backColor);
	// Set overlay mode
	if (colorMode === "light" && colorTone === "light") {
		const overlay = document.querySelector(".view__background__overlay");
		if (overlay) overlay.style.setProperty("mix-blend-mode", "normal");
	}
	// BGM load
	$(".js-view__mask__area-loading__container").fadeIn();
	// Minimum time delay (2022-12-11)
	onloadTimer = setInterval(() => {
		onloadTimeCheck();
	}, (500));

	$(".js-btn-voice__container").on("click", function (e) {
		bgmControl(this);
	});

	$(window.parent.document.getElementById("header")).fadeOut();
	$(window.parent.document.getElementById("mo_header")).fadeOut();

	floatingVisuals();
});

$(window).on("load", function () {
	<?php if($ch['theme']['song']) {?>
		const href = "<?php echo $board_skin_url; ?>/_bgm.php?action=ch_play&yt_id=<?php echo $ch['theme']['song']; ?>";
		const myself = document.getElementById("ch_bgm_frame");
		if (myself) { myself.src = href; }
	<?php } ?>
	onloadFlag = true;
});

$(window).resize(function () {
	measureHeight();
	measureImage();
});

$(".js-nav-content__item").click(function(){
	$(".js-nav-content__item").removeClass("selected");
	$(this).addClass("selected");
});

function showContent(target) {
	$(".js-view__mask__area-hide").removeClass("off").addClass("on");
	$(".article-content__container").hide().children().removeClass('on');
	$(".article-content__container--edit").hide().children().removeClass('on');
	$(".view__body__area-content").removeClass("off").addClass("on");
	$(".view__body__area-content .animate-onload").addClass("on");
	$(".view__body__area-edit").removeClass("on").addClass("off");
	$(".view__body__area-visual").removeClass("back").addClass("slide");
	$(".view__body__nav-content").removeClass("back").addClass("slide");
	$(".animate-toggle").removeClass("on").addClass("off");
	$(target).show().children().each(function(k){
		let item = $(this);
		setTimeout(function(){ item.addClass('on') }, 100*k);
	});
}

function showEdit(target) {
	$(".js-view__mask__area-hide").removeClass("off").addClass("on");
	$(".view__body__area-edit").removeClass("off").addClass("on");
	$(".view__body__area-edit .animate-onload").addClass("on");
	$(".view__body__nav-content").removeClass("back").addClass("slide");
	$(target).show().children().each(function(k){
		let item = $(this);
		setTimeout(function(){ item.addClass('on') }, 100*k);
	});
}

function hideContent()	{
	$(".js-view__mask__area-hide").removeClass("on").addClass("off");
	$(".view__body__area-content").removeClass("on").addClass("off");
	$(".view__body__area-edit").removeClass("on").addClass("off");
	$(".view__body__area-visual").removeClass("slide").addClass("back");
	$(".view__body__nav-content").removeClass("slide").addClass("back");
	$(".js-nav-content__item").removeClass("selected");
	$(".animate-toggle").removeClass("off").addClass("on");
}

function locateBack(url) {
	event.preventDefault();
	hideContent();
	$(".view__footer").fadeOut(500);
	$(".view__body__grid").fadeOut(500);
	$(".view__body__nav-btn").fadeOut(500);
	$(".view__body__nav-content").fadeOut(500);
	$(".view__body__area-content").fadeOut(500);
	$(".view__body__area-edit").fadeOut(500);
	$(".view__body__area-visual").fadeOut(1000);
	$(".view__background__overlay").fadeOut(1000);
	$(".view__background__mask").addClass("off");
	setTimeout(() => {
		$(location).attr('href', url);
	}, 1000);
}

function bgmControl (item) {
	<?php
	$play_url = "{$board_skin_url}/_bgm.php?action=ch_play&yt_id={$ch['theme']['song']}";
	$pause_url = "{$board_skin_url}/_bgm.php";
	?>
	const playUrl = "<?php echo $play_url; ?>";
	const pauseUrl = "<?php echo $pause_url; ?>";
	const status = $(item).attr("data-label");

	let href;
	if (status == "on") {
		href = pauseUrl;
		$(item).attr("data-label", "off");
	} else {
		href = playUrl;
		$(item).attr("data-label", "on");
	}
	const myself = document.getElementById("ch_bgm_frame");
	if (myself) { myself.src = href; }
}
</script>