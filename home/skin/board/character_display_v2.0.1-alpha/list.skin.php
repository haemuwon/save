<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/* Testing code */
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if (function_exists("get_category_list")) $category_list = get_category_list($bo_table, $sca);
?>

<!-- 게시판 목록 시작 { -->
<div id="character-page">
	
	<? if($board['bo_content_head']) { ?>
		<div class="board-notice-box">
			<?=stripslashes($board['bo_content_head']);?>
		</div>
	<? } ?>

	<!-- 게시판 카테고리 시작 { -->
	<?php if (isset($category_list) && $is_category) { ?>
	<nav class="board-category">
		<ul>
			<li><a href="./board.php?bo_table=<?=$bo_table?>" class="ui-btn <?=!$sca || $sca == ''? 'point' : 'etc'?>">ALL</a></li>
			<? echo $category_list; ?>
		</ul>
	</nav>
	<!-- 아보카도 에디션 퍼스널 대응 (2023-01-19) -->
	<?php } else if ($is_category) { ?>
		<nav id="navi_category">
			<ul>
				<?php echo $category_option ?>
			</ul>
		</nav>
	<?php } ?>
	<!-- } 게시판 카테고리 끝 -->

	<? include($board_skin_path."/list.skin.core.php"); ?>

	<div class="btn_confirm">
		<? 
			if($is_notice || $sst == 'wr_id') { 
				if ($list_href || $sst == 'wr_id') {
		?>
				<a href="./board.php?bo_table=<?=$bo_table?>" class="ui-btn etc"><i class="material-icons">list</i> 메인</a>
			<? } else { ?>
				<a href="./board.php?bo_table=<?=$bo_table?>&sst=wr_id" class="ui-btn etc"><i class="material-icons">list</i> 전체 목록</a>
			<? } 
			} else {
				if ($list_href) { ?>
					<a href="<?php echo $list_href ?>" class="ui-btn etc"><i class="material-icons">list</i> 목록</a>
				<?php }
			}
			if ($write_href) { ?>
				<? if($total_count >= 2) { ?>
				<a href="<?php echo $write_href ?>&type=pair" class="ui-btn"><i class="material-icons">edit</i> 페어 등록하기</a>
				<? } ?>
				<a href="<?php echo $write_href ?>&sst=<?=$sst?>" class="ui-btn point"><i class="material-icons">edit</i> 캐릭터 등록하기</a>
			<?php } ?>
	</div>
		
	<!-- 페이지 -->
	<?php echo $write_pages;  ?>
</div>