<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0); 

$owner_front = get_style('mmb_owner_name', 'cs_etc_2');		// 자기 로그 접두문자
$owner_front = $owner_front['cs_etc_2'];
$owner_behind = get_style('mmb_owner_name', 'cs_etc_3');		// 자기 로그 접미문자
$owner_behind = $owner_behind['cs_etc_3'];
$upload_max_filesize = round($board['bo_upload_size']/1000000 , 2)."Mb";
$comment_min=$board['bo_comment_min'];
$comment_max=$board['bo_comment_max'];
?>
<div id="load_log_board" <?if($board['bo_table_width']>0){?>style="max-width:<?=$board['bo_table_width']?><?=$board['bo_table_width']>100 ? "px":"%"?>;margin:0 auto;"<?}?>>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- 자비란 상단 공지 부분 -->
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


<!-- 게시판 카테고리 시작 { -->
	<?php if ($is_category) { ?>
	<nav id="navi_category">
		<ul>
			<span class=highlight4>◆ <?php echo $category_option ?> ◆</span>
		</ul>
	</nav>
	<?php } ?>
<!-- } 게시판 카테고리 끝 -->

	<div class="ui-mmb-button">
	<?php if ($write_href) { 
		// 췩 사용 여부를 체크 한다.\
			$action_url = G5_BBS_URL."/write_update.php";
	?>
		<div class="ui-mmb-list-write">
			 <?include($board_skin_path.'/write.skin.php');?>
		</div> <?}?> 
<a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>" class="ui-btn small">새로고침</a><?//@200914?>

<a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>&sst=wr_last&sod=desc" class="ui-btn small">답글순</a>
		<a href="<?php echo $board_skin_url ?>/emoticon_list.php" class="ui-btn small new_win">이모티콘</a>
		<?if($is_admin){?>
		<a href="<?php echo G5_ADMIN_URL ?>/board_form.php?bo_table=<?=$bo_table?>&w=u" class="ui-btn small admin">관리자</a><?}?>
	</div>



	<? if($write_pages) { ?><div class="ui-paging"><?php echo $write_pages;  ?></div><? } ?>


	<!-- 리스트 시작 -->
	<div id="log_list" class="none-trans">
	<?
		for ($i=0; $i<count($list); $i++) {
			$list_item = $list[$i];
			include($board_skin_path."/list.log.skin.php");
		}
		if (count($list) == 0) { echo "<div class=\"empty_list\">등록된 로그가 없습니다.</div>"; } 
	?>
	</div>

	<? if($write_pages) { ?>
	<div class="ui-paging">
		<?php echo $write_pages;  ?>
	</div>
<? } ?>

	<div class="searc-sub-box">

		<form name="fsearch" method="get">
			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="sca" value="<?php echo $sca ?>">
			<input type="hidden" name="sop" value="and">
			<input type="hidden" name="hash" value="<?=$hash?>">

			<div class="ui-search-box">
				<fieldset class="sch_category select-box">
					<select name="sfl" id="sfl"> 
						<option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>코멘트</option>
						<option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>작성자</option>
						<option value="wr_name"<?php echo get_selected($sfl, 'wr_name'); ?>>작성자(코)</option>
						<option value="wr_1"<?php echo get_selected($sfl, 'wr_1'); ?>>메모</option>
						<option value="wr_tag"<?php echo get_selected($sfl, 'wr_tag'); ?>>이미지태그</option>
						<!--<option value="hash"<?php echo get_selected($sfl, 'hash'); ?>>해시태그</option>
						<option value="log"<?php echo get_selected($sfl, 'log'); ?>>로그번호</option>-->
					</select>
				</fieldset>
				<fieldset class="sch_text">
					<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" >
				</fieldset>
				<fieldset class="sch_button">
					<button type="submit" class="ui-btn point">검색</button>
				</fieldset>
			</div> 

		</form>
	</div>

</div>

<script>
var avo_mb_id = "<?=$member['mb_id']?>";
var avo_board_skin_path = "<?=$board_skin_path?>";
var avo_board_skin_url = "<?=$board_skin_url?>";

var save_before = '';
var save_html = '';

function fviewcomment_submit(f)
{
	set_comment_token(f);
	var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자

	var content = "";
	$.ajax({
		url: g5_bbs_url+"/ajax.filter.php",
		type: "POST",
		data: {
			"content": f.wr_content.value
		},
		dataType: "json",
		async: false,
		cache: false,
		success: function(data, textStatus) {
			content = data.content;
		}
	});

	if (content) {
		alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
		f.wr_content.focus();
		return false;
	}
	if (char_min > 0 || char_max > 0)
	{
		var wr_id=f.wr_id.value;
		check_byte("wr_content_"+wr_id, "char_count_"+wr_id);
		var cnt = parseInt(document.getElementById("char_count_"+wr_id).innerHTML);
		if (char_min > 0 && char_min > cnt)
		{
			alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
			return false;
		} else if (char_max > 0 && char_max < cnt)
		{
			alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
			return false;
		}
	}
	else if (!f.wr_content.value) {
		alert("댓글을 입력하여 주십시오.");
		return false;
	}

	if (typeof(f.wr_name) != 'undefined')
	{
		f.wr_name.value = f.wr_name.value.replace(pattern, "");
		if (f.wr_name.value == '')
		{
			alert('이름이 입력되지 않았습니다.');
			f.wr_name.focus();
			return false;
		}
	}

	if (typeof(f.wr_password) != 'undefined')
	{
		f.wr_password.value = f.wr_password.value.replace(pattern, "");
		if (f.wr_password.value == '')
		{
			alert('비밀번호가 입력되지 않았습니다.');
			f.wr_password.focus();
			return false;
		}
	}

	return true;
}

function comment_delete()
{
	return confirm("이 댓글을 삭제하시겠습니까?");
}
 
function comment_box(wr_id,co_id, work)
{
	save_html=document.getElementById('bo_vc_w_'+wr_id).innerHTML;

	var el_id;
	// 댓글 아이디가 넘어오면 답변, 수정
	if (co_id)
	{
		if (work == 'c')
			el_id = 'reply_' + co_id;
		else
			el_id = 'edit_' + co_id;
	}
	else
		el_id = 'bo_vc_w_' + wr_id;
 
	if (save_before != el_id)
	{
		if (save_before)
		{
			document.getElementById(save_before).style.display = 'none';
			document.getElementById(save_before).innerHTML = '';
		}

		document.getElementById(el_id).style.display = '';
		document.getElementById(el_id).innerHTML = save_html;
		// 댓글 수정
		if (work == 'cu')
		{	
			$('#' + el_id + ' .item-comment-form-box').hide();
			$('#' + el_id + ' button.ui-comment-submit').text('수정');
			var wr_content = $('#save_co_comment_'+co_id).val(); 
			var wr_subject = $('#save_co_subject_'+co_id).val();  
			var wr_1 = $('#save_co_memo_'+co_id).val();
			var option = $('#save_co_html_'+co_id);

			$('#' + el_id + ' .wr_content').val(wr_content); 
			if (typeof char_count != 'undefined')
				check_byte('wr_content_'+el_id, 'char_count_'+el_id);

			$('#' + el_id + ' .wr_subject').val(wr_subject);
			$('#' + el_id + ' .wr_1').val(wr_1); 
			if(option.children('.html').prop('checked')) $('#' + el_id + ' .html').prop('checked',true);
				else $('#' + el_id + ' .html').prop('checked',false);
			if(option.children('.secret').prop('checked')) $('#' + el_id + ' .secret').prop('checked',true);
				else $('#' + el_id + ' .secret').prop('checked',false);
			if(option.children('.re-more').prop('checked')) $('#' + el_id + ' .re-more').prop('checked',true);
				else $('#' + el_id + ' .re-more').prop('checked',false);
			if($('#save_wr_type_'+co_id).val()=='UPLOAD') $('#' + el_id + ' .file_del').css('display','block');
				else $('#' + el_id + ' .file_del').css('display','none');
			if($('#save_dice_'+co_id).val()>0) $('#' + el_id + ' .game').remove();
 
 
		}
		
		$('#' + el_id + ' .co_id').val(co_id);
		$('#' + el_id + ' .w').val(work);
 

		save_before = el_id;
	}
		if(co_id && work=='c')
			$('#'+el_id+' h4').css('display','block').text('');
		else if (co_id && work =='cu')
			$('#'+el_id+' h4').css('display','block').text('');
		else
			$('#'+el_id+' h4').css('display','none');
}



$(".co-more").click(function(){
	$(this).next(".original_comment_area").slideToggle();
	$(this).toggleClass("on");
	return false;
});
</script>
  

<script src="<?=$board_skin_url?>/load.board.js"></script>
