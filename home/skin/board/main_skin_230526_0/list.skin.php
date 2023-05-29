<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.css" />';
echo '<script src="'.$board_skin_url.'/load.board.js"></script>';
echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.css.php" type="text/css" />';

$upload_max_filesize = round($board['bo_upload_size']/1000000 , 2)."Mb";
?>

<!-- 갈무리 폰트 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/galmuri@latest/dist/galmuri.css">

<!-- SWIPER -->
<link rel="stylesheet" href="https://unpkg.com/swiper@6.8.4/swiper-bundle.min.css" />
<script src="https://unpkg.com/swiper@6.8.4/swiper-bundle.min.js"></script>

<!-- 머테리얼 아이콘 -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div id="mainskin--board" <?if($board['bo_table_width']>0){?>style="max-width:<?=$board['bo_table_width']?><?=$board['bo_table_width']>100 ? "px":"%"?>;margin:0 auto;"<?}?>>

<!-- 상단 공지 부분 -->
<? if($board['bo_content_head']) { ?>
	<div class="board-notice">
		<?=stripslashes($board['bo_content_head']);?>
	</div>
<? } ?>

<?
	/*-------------------------------------------
		동접자 카운터 설정
	---------------------------------------------*/
	$wiget = get_style('mmb_counter');
	if($wiget['cs_value']) { echo '<div class="connect-wiget">'.$wiget['cs_value'].'</div>'; }
?>

  <!-- 등록 버튼과 관리자 버튼 -->
	<div class="mainskin--header">
	<?php if($is_admin) {  // 관리자라면 버튼들을 생성한다 ?>
		
			<a href='javascript:void(0);' class="ui-btn mainskin--viewbtn" id="view-btn" onclick="controlDiv('', '', ''); return false;">새 레이아웃</a>
			<a href='javascript:void(0);' class="ui-btn mainskin--viewfixbtn" id="view-fixbtn" onclick="view_hide_fix();">편집 모드</a>
			<a href="<?php echo G5_ADMIN_URL ?>/board_form.php?bo_table=<?=$bo_table?>&w=u" class="ui-btn small admin">관리자</a></div>

		<div class="mainskin--write" id="mainskin--write">
			 <?include($board_skin_path.'/write.skin.php');?>
		<?}?>
</div>
	<!-- 리스트 시작 -->
	<div id="mainskin--list">
	<?
			include($board_skin_path."/list.log.skin.php");
	?>

	</div>


</div>
<div class="edit_window" id="edit_window">
</div>
<div class="mobile_window" id="mobile_window">
</div>
<div class="load_window" id="load_window">
	Loading . . .
</div>

<script>
var avo_mb_id = "<?=$member['mb_id']?>";
var avo_board_skin_path = "<?=$board_skin_path?>";
var avo_board_skin_url = "<?=$board_skin_url?>";

function element_delete()
{
	return confirm("이 요소를 삭제하시겠습니까?");
}

function view_hide_upload() {
	var upload = document.querySelector('.mainskin--board--write');
	if(upload.style.display == 'none') upload.style.display = 'flex';
	else if(upload.style.display == 'flex') upload.style.display = 'none';
	}

	var fixmod = false;
	function view_hide_fix() {
	var fixbtn = document.querySelectorAll('.mainskin--btns');
	var layouts = document.querySelectorAll('.main_layout');
	if (fixmod == false){
		fixbtn.forEach((x) => {
		x.style.display = 'block';
		fixmod = true;
	})
	layouts.forEach((a) => {
		a.classList.add('active');
	})
	} else {
		fixbtn.forEach((x) => {
		x.style.display = 'none';
		fixmod = false;
	})
	layouts.forEach((a) => {
		a.classList.remove('active');
	})
	}
	
	}

	function html_auto_br(obj)
	{
				obj.value = "html2";
	}

</script>
  

<script src="<?=$board_skin_url?>/load.board.js"></script>
