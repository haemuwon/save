<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if ($is_comment_write) {
	if($w == '') $w = 'c';

$c_option = '';
	$c_option_hidden = '';
	$c_is_html = false;
	if ($member['mb_level'] >= $board['bo_html_level'])
		$c_is_html = true; 
	$c_is_secret = $board['bo_use_secret'];
	if ($c_is_html || $c_is_secret ) {
		$c_option = '';
		if ($c_is_html) {
				$c_option .= "\n".'<span><input type="checkbox" class="html" id="html_'.$list_item['wr_id'].'" name="html" value="html2">'."\n".'<label for="html_'.$list_item['wr_id'].'">html</label></span>';
		}

		if ($c_is_secret) {
			if ($is_admin || $c_is_secret==1) {
				$c_option .= "\n".'<span><input type="checkbox" class="secret" id="secret_'.$list_item['wr_id'].'" name="secret" value="secret">'."\n".'<label for="secret_'.$list_item['wr_id'].'">비밀글</label></span>';
			} else {
				$c_option_hidden .= '<input type="hidden" name="secret" value="secret">';
			}
		} 
	}
$comment_min=$is_admin?0:$board['bo_comment_min'];
$comment_max=$is_admin?0:$board['bo_comment_max'];
?>
	
<script>
// 글자수 제한
var char_min = parseInt(<? echo $comment_min ?>); // 최소
var char_max = parseInt(<? echo $comment_max ?>); // 최대
</script>
<!-- 댓글 쓰기 시작 { -->
<aside class="bo_vc_w" id="bo_vc_w_<?=$list_item['wr_id']?>">
	<h4>코멘트</h4>
	<form name="fviewcomment" action="./write_comment_update.php" onsubmit="return fviewcomment_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="w" value="<?php echo $w ?>" class="w">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="wr_id" value="<?php echo $list_item['wr_id'] ?>" class="wr_id">
	<input type="hidden" name="comment_id" value="" class="co_id">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>"> 
	
	<div class="input-comment"> 
		<?	/**********************************************
						  캐릭터 대화용
		**********************************************/?> 
		<input type="hidden" name="wr_subject" value="<?=$member['mb_nick'] ? $member['mb_nick'] : $wr_name; ?>" class="wr_subject"/>
		<?/**********************************************
					    캐릭터 대화 끝
		**********************************************/?>
		<p class="file_box"><span style="display:none;" class="file_del txt-right" ><input type="checkbox" name="bf_file_del" id="file_del_<?=$list_item['wr_id']?>" value="1"><label for="file_del_<?=$list_item['wr_id']?>"> 파일삭제</label></span> 
		<input type="file" name="bf_file" title="로그등록 :  용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input full" /></p>
		<p class="memo_box"><input type="text" name="wr_1" class="wr_1 frm_input full" placeholder="memo"><a href="#" onclick="$(this).parent().prev().slideToggle();return false;" class="ui-btn etc"><span>▲</span></a></p>
		<? if ($comment_min || $comment_max) { ?><p class="form-input full small"><strong id="char_cnt"><span id="char_count_<?=$list_item['wr_id']?>"></span>글자</strong></p><? } ?>
			<textarea name="wr_content" id="wr_content_<?=$list_item['wr_id']?>" required class="wr_content required" title="내용"
			<? if ($comment_min || $comment_max) { ?>onkeyup='check_byte("wr_content_<?=$list_item['wr_id']?>", "char_count_<?=$list_item['wr_id']?>");'<? } ?>></textarea>
			<? if ($comment_min || $comment_max) { ?><script> check_byte("wr_content_<?=$list_item['wr_id']?>", "char_count_<?=$list_item['wr_id']?>"); </script><? } ?>
			<script>
			$(document).on( "keyup", "textarea#wr_content_<?=$list_item['wr_id']?>[maxlength]", function(){
				var str = $(this).val()
				var mx = parseInt($(this).attr("maxlength"))
				if (str.length > mx) {
					$(this).val(str.substr(0, mx));
					return false;
				}
			});
			</script> 

		<div class="action-check form-input"> 
		<?if(!$is_member && $board['bo_write_level']){?>
		<p><input type="text" name="wr_name" placeholder="이름" value="<?=$_COOKIE['MMB_NAME']?>" style="max-width:40%" />
		<input type="password" name="wr_password" value="<?=$_COOKIE['MMB_PW']?>" placeholder="비밀번호" style="max-width:40%" />
		&nbsp;&nbsp;<input type="checkbox" value="1" id="cookie_<?=$list_item['wr_id']?>" name="cookie" <?=($_COOKIE['MMB_NAME']||$_COOKIE['MMB_PW']) ? "checked":""; ?>>
		<label for="cookie_<?=$list_item['wr_id']?>">쿠키</label>		
		</p>
	<?}?>
			<?if($c_option){ echo $c_option; }?>
			<span><input type="checkbox" name="wr_reply_more" class="re-more" id="wr_reply_more_<?=$list_item['wr_id']?>" value="1">
			<label for="wr_reply_more_<?=$list_item['wr_id']?>">리플접기</label></span>
			<span><input type="checkbox" name="game" id="game_<?=$list_item['wr_id']?>" class="game" value="dice" />
			<label for="game_<?=$list_item['wr_id']?>" class="game">주사위</label></span>

		<? if($board['bo_use_noname'] && $is_member) { ?>
			<span>
			<input type="checkbox" name="wr_noname" id="wr_noname_<?=$list_item['wr_id']?>" class="noname" value="1" />
			<label for="wr_noname_<?=$list_item['wr_id']?>">익명</label></span>
		<? } ?> 
			
			<span>
			<a href="<?php echo $board_skin_url ?>/emoticon_list.php" class="new_win" onclick="window.open('<?php echo $board_skin_url ?>/emoticon_list.php', 'emoticon', 'width=400, height=600');return false;">[이모티콘]</a>
			</span>
	
		<div class="btn_confirm">
			<button type="submit" class="ui-comment-submit ui-btn point">입력</button>
		</div>
		</div>

	</div>

	</form>
</aside>
<?
}
?>
 