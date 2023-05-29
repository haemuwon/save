<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

	?>

		<section id="bo_w" class="mainskin--board--write" style="display:none;">
			<div class="mainskin--edit">
			<!-- 게시물 작성/수정 시작 { -->
			<form name="fwrite" id="fwrite" action="./write_update.php" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" >
			<strong>레이아웃 등록</strong>
			<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
			<input type="hidden" name="w">
			<input type="hidden" name="has_comment" value="0" id="has_comment">

			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
			<input type="hidden" name="wr_parent" value="<?php echo $wr_parent ?>">
			<input type="hidden" name="wr_type" value="layout">
			<input type="hidden" name="wr_subject" value="새 레이아웃">
			<input type="hidden" name="margin_auto" value="auto">
			<input type="hidden" name="padding_auto" value="auto">
			<input type="hidden" name="theme" value="main_default">
			
			<!-- 요소 등록 부분 -->

		<dl>
		<dt>가로 너비</dt>
		<dd>
		<input class="mainskin--input" type="text" id="div_width" name="div_width">
		<br>
		비워두시면 100%로 설정됩니다.
		</dd>
	</dl>
	<dl>
		<dt>세로 길이</dt>
		<dd>
		<input class="mainskin--input" type="text" id="div_height" name="div_height">
		<br>
		비워두시면 800px로 설정됩니다.
		</dd>
	</dl>
	
		<dl>
		<dt>레이아웃 방향</dt>
		<dd>
		<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="wr_direction" id="column" value="column" checked>
		<label class="form-check-label" for="column"><i class="material-icons">view_stream</i> 세로</label>
	</div>
	<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="wr_direction" id="row" value="row">
		<label class="form-check-label" for="row"><i class="material-icons">view_week</i> 가로</label>
	</div>
	</dd>
	</dl>
	<input type="hidden" name="bf_file[]" title="파일첨부 <?php echo 1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
 		<button type="submit" id="btn_submit" accesskey="s" class="ui-btn point">등록</button>
		 <button class="ui-btn" onclick="controlDiv('', '', 'close'); return false;">닫기</button>
			</form>
		</div>
		</section>

		<section id="bo_w_c" class="mainskin--board--write" style="display:none;">
			<div class="mainskin--edit">
			<!-- 게시물 작성/수정 시작 { -->
			<form name="fwritereply" id="fwritereply" action="./write_comment_update.php" onsubmit="return fwritereply_submit(this);" method="post" autocomplete="off">
			레이아웃 추가
			<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
			<input type="hidden" name="w" id="w" value="c">
			<input type="hidden" name="has_comment" value="0" id="has_comment">
			<input type="hidden" name="wr_comment_reply" value="<?=$wr_comment_reply?>" id="wr_comment_reply">

			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="wr_id" id="wr_id" value="<?php echo $wr_id ?>">
			<input type="hidden" name="comment_id" id="comment_id" value="<? echo $c_id ?>" >
			<input type="hidden" name="wr_type" value="layout">
			<input type="hidden" name="wr_is_comment" id="wr_is_comment" value="1">
			<input type="hidden" name="wr_subject" id="wr_subject" value="내부 레이아웃">
			<input type="hidden" name="wr_content" id="wr_content" value="">
			<input type="hidden" name="margin_auto" value="auto">
			<input type="hidden" name="padding_auto" value="auto">
			<input type="hidden" name="theme" value="main_default">

			<!-- 요소 등록 부분 -->

		<dl>
		<dt>가로 너비</dt>
		<dd>
		<input class="form-control form-control-sm" type="text" id="div_width" name="div_width">
		<br>
		비워두시면 100%로 설정됩니다.
		</dd>
	</dl>
	<dl>
		<dt>세로 길이</dt>
		<dd>
		<input class="form-control form-control-sm" type="text" id="div_height" name="div_height">
		<br>
		비워두시면 100%로 설정됩니다.
		</dd>
	</dl>
	
	
		<dl>
		<dt>레이아웃 방향</dt>
		<dd>
		<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="wr_direction" id="column" value="column" checked>
		<label class="form-check-label" for="column"><i class="material-icons">view_stream</i> 세로</label>
		</div>
		<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="wr_direction" id="row" value="row">
		<label class="form-check-label" for="row"><i class="material-icons">view_week</i> 가로</label>
		</div>
	</dd>
	</dl>
	<dl>
	<input type="hidden" name="bf_file[]" title="파일첨부 <?php echo 1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
 		<button type="submit" id="btn_submit" accesskey="s" class="ui-btn point">등록</button>
			</form></div>
		</section>
	<!-- } 게시물 작성/수정 끝 -->

<script>
	function fwrite_submit(f)
	{
		<?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}
	
	function fwritereply_submit(f)
	{
		<?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>
		document.getElementById("btn_submit").disabled = "disabled";
		return true;
	}
 

</script>
