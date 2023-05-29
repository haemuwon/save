<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$left = sql_fetch("select * from {$write_table} where wr_id = '{$list[$i]['wr_2']}'");
$right = sql_fetch("select * from {$write_table} where wr_id = '{$list[$i]['wr_3']}'");

$left_mode = json_decode($left['wr_2_txt'], true);
$right_mode = json_decode($right['wr_2_txt'], true);

unset($dday);

if ($list[$i]['wr_10']=="no-date") {
	$dday = $list[$i]['wr_subject'];
} else {
	$startDate = strtotime($list[$i]['wr_subject']);
	$endDate = strtotime(date('Y-m-d'));
	$dday = ($endDate - $startDate)/(60*60*24)+1;
}

$use_img = $list[$i]['wr_1'] != "" ? "use_img" : "";
$edit_href = $list[$i]['mb_id'] == $member['mb_id'] ? "./write.php?bo_table={$bo_table}&wr_id={$list[$i]['wr_id']}&w=u" : "";
?>
<style>
	<?php echo ".".$classname_rand_suffix; ?> {
		--left-color: <?php echo $left_mode['color']; ?>;
		--right-color: <?php echo $right_mode['color']; ?>;
	}
</style>
<li class="list__grid__item__full <?php echo $classname_rand_suffix; ?>">
	<article class="article-pair <?php echo $use_img; ?> <?php echo $is_bitmap; ?>">
		<div class="article-pair__background">
			<div class="article-pair__background__img" style="background-image:url(<?php echo $list[$i]['wr_1']; ?>);"></div>
		</div>
		<ul class="list__grid--pair">
			<li class="list__grid--pair__item">
				<div class="article-pair__relation--left">
					<div class="article-pair__relation__container--left">
						<div class="article-pair__relation__name">
							<?php echo $left["wr_subject"]; ?>
						</div>
						<? if($list[$i]['wr_4']) { ?>
							<div class="article-pair__relation__desc">
								<?php echo $list[$i]['wr_4']; ?>
							</div>
						<? } ?>
					</div>
				</div>
			</li>
			<? // include left character
				$ch = $left;
				$classname_rand_suffix = uniqid("ch_left{$i}_");
				include($board_skin_path."/list.character.skin.php");
			?>
			<li class="list__grid--pair__item">
				<div class="article-pair__counter">
					<div class="article-pair__counter__header">
					</div>
					<div class="article-pair__counter__body">
						<div class="dday" title="DATE.<?php echo $list[$i]['wr_subject']; ?>" <? if ($edit_href) {?>onclick="location.href='<?php echo $edit_href; ?>'" style="cursor:pointer;"<?}?> >
							<div class="days">
								<div class="mid">
									<div class="article-pair__counter__container">
										<div class="article-pair__counter__number"><?php echo $dday; ?></div>
										<div class="article-pair__counter__unit"><?php echo $list[$i]['wr_10']=="no-date"?"":"DAYS"; ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="article-pair__counter__footer">
						<div class="article-pair__counter__date"><?php echo $list[$i]['wr_subject']; ?></div>
					</div>
				</div>
			</li>
			<? // include right character
				$ch = $right;
				$classname_rand_suffix = uniqid("ch_right{$i}_");
				include($board_skin_path."/list.character.skin.php");
			?>
			<li class="list__grid--pair__item">
				<div class="article-pair__relation--right">
					<div>
					</div>
					<div class="article-pair__relation__container--right">
						<div class="article-pair__relation__name">
							<?php echo $right["wr_subject"]; ?>
						</div>
						<? if($list[$i]['wr_5']) { ?>
							<div class="article-pair__relation__desc">
								<?php echo $list[$i]['wr_5']; ?>
							</div>
						<? } ?>
					</div>
				</div>
			</li>
		</ul>
	</article>
</li>