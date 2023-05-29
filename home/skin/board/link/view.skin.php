<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
 
if($board['bo_table_width']==0) $width="100%";?> 
<!-- 링크 버튼 시작 { -->
<div id="bo_v_bot">
	<? ob_start(); ?> 
	<div class="bo_v_com">
		<a href="<? echo $list_href ?>" class="ui-btn left">목록</a>
		<? if ($update_href) { ?><a href="<? echo $update_href ?>" class="ui-btn">수정</a><? } ?>
		<? if ($delete_href) { ?><a href="<? echo $delete_href ?>" class="ui-btn admin" onclick="del(this.href); return false;">삭제</a><? } ?>
		<? if ($write_href) { ?><a href="<? echo $write_href ?>" class="ui-btn point">링크 추가</a><? } ?>
	</div>
	<?
	$link_buttons = ob_get_contents();
	ob_end_flush();
	?>
</div> 
<!-- } 링크 버튼 끝 --> 
<div class="board-viewer">
<div class="contents">  
	<!-- 본문 내용 시작 { -->
	<div id="bo_v_con"><? echo get_view_thumbnail($view['content']); ?></div>
	<?//echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
	<!-- } 본문 내용 끝 -->   
</div>
</div> 
<!-- } 게시글 읽기 끝 --> 