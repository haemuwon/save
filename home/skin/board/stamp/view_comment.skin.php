<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<script language="JavaScript">
// 글자수 제한
var char_min = parseInt(<?=$comment_min?>); // 최소
var char_max = parseInt(<?=$comment_max?>); // 최대
</script>

<!-- 코멘트 쓰기 -->

<? if ($is_comment_write) { 
	if($w == '') $w = 'c';
	?>
<div class="ui-write-area" id="comment_write<?=$lists[$ii]['wr_id']?>" style="display:none;">
	<!-- 코멘트 입력테이블시작 -->
	<form name="fviewcomment" action="<?=G5_BBS_URL?>/write_comment_update.php" method="post" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="wr_id" value="<?php echo $lists[$ii]['wr_id'] ?>">
		<input type="hidden" name="sca" value="<?php echo $sca ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="spt" value="<?php echo $spt ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">   
		
		<p class="ui-text-area">
			<input type="text" name="wr_content" required class="frm-input full" value="<?=$list[$i]['wr_content']?>">
			<button type="submit" class="ui-btn" accesskey='s'>입력</button>
		</p>
		<?if(!$is_member && $is_comment_write){?>
		<p>
		<input type="text" name="wr_name" placeholder="이름" value="<?=$_COOKIE['MMB_NAME']?>" style="max-width:40%" />
		<input type="password" name="wr_password" value="<?=$_COOKIE['MMB_PW']?>" placeholder="비밀번호" style="max-width:40%" />
		</p> 
		<?}?>
	</form>
</div>
<? } ?>
<ul>
<!-- 코멘트 리스트 -->
<?
for ($i=0; $i<count($list); $i++) {
	$comment_id = $list[$i]['wr_id'];
?> 	
	<li id="c_<?=$comment_id?>"> 
		<a name="c_<?=$comment_id?>"></a>
		<div class="comment-content content-area">
			<em>→</em>
			<span class="date"><?=date("Y/m/d",strtotime($list[$i]['wr_datetime']))?></span>
			<!-- 코멘트 출력 -->
			<?
			if (strstr($list[$i]['wr_option'], "secret")) echo "<span style='color:#ff6600;'>*</span> ";
			$str = $list[$i]['content'];
			if (strstr($list[$i]['wr_option'], "secret"))
			$str = "<span style='color:#ff6600;'>$str</span>";

			$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $str);
			$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(swf)\".*\<\/a\>\]/i", "<script>doc_write(flash_movie('$1://$2.$3'));</script>", $str);
			$str = preg_replace("/\[\<a\s*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(gif|png|jpg|jpeg|bmp)\"\s*[^\>]*\>[^\s]*\<\/a\>\]/i", "<img src='$1://$2.$3' id='target_resize_image[]' onclick='image_window(this);' border='0'>", $str);
			echo "<span class='con'>".$str."</span>";
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
			<p class="control"><? 
			if ($list[$i]['is_edit']) { ?><a href="javascript:comment_box('<? echo $comment_id ?>', '<?=$list[$ii]['wr_id']?>');" >*</a><? }
			if ($list[$i]['is_del'])  { echo "<a href=\"javascript:comment_delete('{$list[$i]['del_link']}');\">-</a>"; }
			 ?></p>
		<?}?>
			<span id="edit_<? echo $comment_id ?>"></span><!-- 수정 -->

			<input type="hidden" value="<? echo strstr($list[$i]['wr_option'],"secret") ?>" id="secret_comment_<? echo $comment_id ?>">
			<input type="text" id="save_comment_<? echo $comment_id ?>" style="display:none" value="<? echo get_text($list[$i]['content1'], 0) ?>">
		</div>

		<? if ($list[$i]['is_edit'])  { ?>
		<div class="modify_area ui-text-area" id="save_comment_<?php echo $comment_id ?>" style="display:none;"> 
			<input type="text" id="save_co_comment_<?php echo $comment_id ?>" value="<?php echo get_text($list[$i]['wr_content'], 0) ?>" class="full">  
			<p class="txt-right"><button type="button" class="mod_comment ui-btn" onclick="modify_commnet('<?php echo $comment_id ?>'); return false;">수정</button></p>
		</div>
		<? } ?> 
	</li>
<? } ?>
</ul> 

<? 
include_once("$board_skin_path/view_skin_js.php");
?>