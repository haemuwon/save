<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<script language="JavaScript">
// 글자수 제한
var char_min = parseInt(<?=$comment_min?>); // 최소
var char_max = parseInt(<?=$comment_max?>); // 최대
</script>

<!-- 코멘트 리스트 -->

<ul>
<!-- 코멘트 리스트 -->
<?
for ($i=0; $i<count($list); $i++) {
	$comment_id = $list[$i]['wr_id'];
?>
			
	<li id="c_<?=$comment_id?>">
		<a name="c_<?=$comment_id?>"></a>
		<a href="#c_<?=$comment_id?>" onclick="$(this).next().slideToggle(); return false;" class="ui-btn small">▶ 답변<?=$lists[$ii]['wr_comment']>1 ? $i+1:""?></a>
		<div class="qna-comment-content" style="display:none;">
			<!-- 코멘트 출력 -->
			<?
			if (strstr($list[$i]['wr_option'], "secret")) echo "<span style='color:#ff6600;'>*</span> ";
			$str = $list[$i]['content'];
			if (strstr($list[$i]['wr_option'], "secret"))
				$str = "<span class='small' style='color:#ff6600;'>$str</span>";

			$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $str);
			$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(swf)\".*\<\/a\>\]/i", "<script>doc_write(flash_movie('$1://$2.$3'));</script>", $str);
			$str = preg_replace("/\[\<a\s*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(gif|png|jpg|jpeg|bmp)\"\s*[^\>]*\>[^\s]*\<\/a\>\]/i", "<img src='$1://$2.$3' id='target_resize_image[]' onclick='image_window(this);' border='0'>", $str);
			echo $str;
			$query_string = clean_query_string($_SERVER['QUERY_STRING']);
		
	
			if($w == 'cu') {
				$sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$comment_id' and wr_is_comment = '1' ";
				$cmt = sql_fetch($sql);
				if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id'])))
					$cmt['wr_content'] = '';
				$c_wr_content = $cmt['wr_content'];
			}

			$c_edit_href = './board.php?'.$query_string.'&amp;comment_id='.$comment_id.'&amp;wr_id='.$wr_id.'w=cu';

			?>
		<? if ($list[$i]['is_edit']||$list[$i]['is_del']) { ?>
		<p class="clear">
			<strong>
				<? if ($list[$i]['is_edit']) { ?><span><a href="javascript:comment_box('<? echo $comment_id ?>', '<?=$list[$ii]['wr_id']?>');">M</a></span><? } ?>	
				<? if ($list[$i]['is_del']) { ?><span><a href="javascript:comment_delete('<?=$list[$i]['del_link']?>');">D</a></span><?}?>			
			</strong>
		</p>
		<?}?>
			<span id="edit_<? echo $comment_id ?>"></span><!-- 수정 -->

			<input type="hidden" value="<? echo strstr($list[$i]['wr_option'],"secret") ?>" id="secret_comment_<? echo $comment_id ?>">
			<textarea id="save_comment_<? echo $comment_id ?>" style="display:none"><? echo get_text($list[$i]['content1'], 0) ?></textarea>
		</div>

		<? if ($list[$i]['is_edit'])  { ?>
		<div class="modify_area" id="save_comment_<?php echo $comment_id ?>" style="display:none;"> 
			<textarea id="save_co_comment_<?php echo $comment_id ?>" rows="4"><?php echo get_text($list[$i]['wr_content'], 0) ?></textarea>
			 
			<p class="txt-right"><button type="button" class="mod_comment ui-btn" onclick="modify_commnet('<?php echo $comment_id ?>'); return false;">수정</button></p>
		</div>
		<? } ?>

	</li>
<? } ?>
</ul>
<? if ($is_comment_write) { 
	if($w == '') $w = 'c';
	?>
<div class="ui-write-area" id="comment_write<?=$lists[$ii]['wr_id']?>" style="display:none;">
	<!-- 코멘트 입력테이블시작 -->
	<form name="fviewcomment" method="post" action="./write_comment_update.php" autocomplete="off"> 
	<input type="hidden" name="w" value="<? echo $w ?>" >
		<input type="hidden" name="bo_table" value="<? echo $bo_table ?>">
		<input type="hidden" name="wr_id" value="<? echo $wr_id ?>"> 
		<input type="hidden" name="sca" value="<? echo $sca ?>">
		<input type="hidden" name="sfl" value="<? echo $sfl ?>">
		<input type="hidden" name="stx" value="<? echo $stx ?>">
		<input type="hidden" name="spt" value="<? echo $spt ?>">
		<input type="hidden" name="page" value="<? echo $page ?>"> 


		<textarea id="wr_content<?=$comment_id?>" name="wr_content" rows="4" itemname="내용" required
		<? if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?> style='width:100%; word-break:break-all;' class='tx'><?=$list[$i]['wr_content']?></textarea>
		<? if ($comment_min || $comment_max) { ?><script type="text/javascript"> check_byte('wr_content', 'char_count'); </script><?}?>

		<div class="txt-right" style="padding-bottom:5px;">
			<button type="submit" class="ui-btn" accesskey='s'>입력</button>
		</div>
		
	</form>
</div>
<? } ?>
<script language='JavaScript'>
function fviewcomment_submit(f)
{
    return true;
} 
 
</script>