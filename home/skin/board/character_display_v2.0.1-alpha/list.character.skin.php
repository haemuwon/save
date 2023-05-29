<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/* Set local variables */
$src = json_decode($ch['wr_1_txt'], true);
$mode = json_decode($ch['wr_2_txt'], true);
$option = json_decode($ch['wr_3_txt'], true);

// Set variables
$view_href = isset($ch['href']) ? $ch['href'] : "./board.php?bo_table={$bo_table}&wr_id={$ch['wr_id']}&page={$page}&sst={$sst}";

// Set names
$name = $option['codename'] ? $option['codename'] : $ch['wr_subject'];
$subname = $option['codename'] ? $option['codename_rubi'] : $option['phrase'];

// Set number
$ch_no = $option['no'];
if (substr($ch_display_skin_config['theme'], 0, 7) == "ddeeboo" && strlen($ch_no) < 3) {
	// 숫자가 3자리 이하일 때만 자동 변환 수행 (2022-12-11 수정)
	$ch_no = sprintf("%03d", $ch_no);
}

// Set img
$img_content = "";
$emblem_content = "";
if ($src) {
	// Select img source
	$img_src = substr($ch_display_skin_config["theme"], 0, 7) == "ddeeboo" ? $src['mini'] : $src['head'];
	$img_content = "<img class=\"article-card__thumb__img\" src=\"{$img_src}\"/>";
	// Set emblem
	$emblem_content = "<div class='article-card__content__emblem auto-contrast {$classname_rand_suffix}' data-label='{$mode['color']}'><img class='article-card__content__emblem__img' src='{$src['emblem']}'></div>";
}
?>
<? 
	if ($is_notice) echo "<li class=\"list__grid--pair__item\"><div class=\"list__grid__item glide__slide--active\">"; 
	else echo "<li class=\"list__grid__item glide-slide\">";
?>
	<style>
		<?php echo ".".$classname_rand_suffix; ?> {
			--ch-point: <?php echo $mode['color']; ?>;
		}
	</style>
	<article class="article-card <?php echo $classname_rand_suffix;?>">
		<div class="article-card__header"></div>
		<div class="article-card__body glide-slide-box" onClick="location.href='<?php echo $view_href; ?>'" style="cursor: pointer">
			<div class="article-card__container">
				<div class="article-card__background">
					<!-- Glitch this element -->
					<div class="article-card__background__cover"></div>
					<div class="article-card__background__class auto-contrast" data-label="<?php echo $mode['color']; ?>">
						<?php if($ch_no) { //PC 정보 출력 ?>
							<article class="article-class">
								<div class="article-class__top">
									<div class="article-class__top__text"><?php echo $ch_no; ?></div>
								</div>
							</article>
						<?php } ?>
					</div>
				</div>
				<div class="article-card__visual">
					<div class="article-card__thumb">
						<?php echo $img_content; ?>
					</div>
				</div>
				<div class="article-card__content">
					<?php echo $emblem_content; ?>
					<div class="article-card__content__codename animate-box">
						<div class="article-card__content__codename__text"><?php echo $name; ?></div>
					</div>
					<div class="article-card__content__rubi animate-box">
						<div class="article-card__content__rubi__text"><?php echo $subname; ?></div>
					</div>
				</div>
			</div>
		</div>
		<div class="article-card__footer">
		</div>
	</article>
<?php
	if ($is_notice) { echo "</div>"; } 
?>
</li>