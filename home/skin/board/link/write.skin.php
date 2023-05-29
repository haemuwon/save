<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$category_option = "";

if($is_category && $board['bo_category_list']){

	$categories = explode("|", $board['bo_category_list']); // 구분자가 | 로 되어 있음
	for ($i=0; $i<count($categories); $i++) {
		$category = trim($categories[$i]);
		if (!$category) continue;

		$category_option .= "<option value=\"$categories[$i]\"";
		if ($category == $ca_name) {
			$category_option .= ' selected="selected"';
		}
		$category_option .= ">$categories[$i]</option>\n";
	}
}

?>


<hr class="padding">
<section id="bo_w">
	<!-- 게시물 작성/수정 시작 { -->
	<form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="sst" value="<?php echo $sst ?>">
	<input type="hidden" name="sod" value="<?php echo $sod ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="wr_subject" value="링크">
	<input type="hidden" name="wr_content" value="링크">
	<?php
	$option = '';
	$option_hidden = '';
	if ($is_notice || $is_html || $is_secret || $is_mail) {
		$option = '';
		if ($is_notice) {
			//$option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
		}

		if ($is_html) {
			if ($is_dhtml_editor) {
				$option_hidden .= '<input type="hidden" value="html1" name="html">';
			} else {
				$option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">html</label>';
			}
		}

		if ($is_secret) {
			if ($is_admin || $is_secret==1) {
				$option .= "\n".'<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'<label for="secret">비밀글</label>';
			} else {
				$option_hidden .= '<input type="hidden" name="secret" value="secret">';
			}
		}

		if ($is_mail) {
			$option .= "\n".'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'<label for="mail">답변메일받기</label>';
		}
	}

	echo $option_hidden;
	?>

	<div class="board-write theme-box">
		<? if($is_category){?>
		<dl>
			<dt style="line-height:150%;">카테고리</dt>
			<dd><select name="ca_name" id="ca_name" required class="required" >
				<option value="">선택하세요</option>
				<?php echo $category_option ?>
			</select>  
			</dd>
		</dl>   
		<?}?>
		<dl>
			<dt>제목</dt>
			<dd><div class="subject" style="margin-top: 20px;">
			<input type="text" name="wr_1" value="<?=$write['wr_1']?>" class="frm_input full" maxlength="255">
			</div></dd>
		</dl>
		<dl>
			<dt>배너</dt>
			<dd><div class="wr_link1" style="margin-top: 20px;">
			<input type="text" name="wr_link1" value="<?php echo $write['wr_link1'] ?>" class="frm_input full" maxlength="255">
			<?=help("img 태그를 제외한 순수 이미지 링크만 입력(ex. http://link.com/banner.png) 없을시 제목이 출력됩니다.");?>
			</div></dd>
		</dl>
		<dl>
			<dt>링크</dt>
			<dd><div class="wr_link2" style="margin-top: 20px;">
			<input type="text" name="wr_link2" value="<?php echo $write['wr_link2'] ?>" class="frm_input full" maxlength="255">
			</div></dd>
		</dl>
		<dl>
			<dt>순서</dt>
			<dd><div class="wr_10">
			<input type="text" name="wr_10" value="<?=$w=='u' ? $write['wr_10']:"";?>" >
			<?=help("* 순서는 1이 가장 처음으로 순서를 정하지 않을 경우 먼저 등록한 순서대로 정렬됩니다.");?>
			</div>
			</dd>
		</dl>  
	</div>

	<hr class="padding" />
	<div class="btn_confirm txt-center">
		<input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit ui-btn point">
		<a href="./board.php?bo_table=<?php echo $bo_table ?>" class="btn_cancel ui-btn">취소</a>
	</div>
	</form>

	<script>
	<?php if($write_min || $write_max) { ?>
	// 글자수 제한
	var char_min = parseInt(<?php echo $write_min; ?>); // 최소
	var char_max = parseInt(<?php echo $write_max; ?>); // 최대
	check_byte("wr_content", "char_count");


	$(function() {
		$("#wr_content").on("keyup", function() {
			check_byte("wr_content", "char_count");
		});
	});

	<?php } ?> 
 
	function html_auto_br(obj)
	{
		if (obj.checked) {
			result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
			if (result)
				obj.value = "html2";
			else
				obj.value = "html1";
		}
		else
			obj.value = "";
	}

	function fwrite_submit(f)
	{
		<?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>
		 
		var subject = "";
		var content = "";
		$.ajax({
			url: g5_bbs_url+"/ajax.filter.php",
			type: "POST",
			data: {
				"subject": f.wr_subject.value,
				"content": f.wr_content.value
			},
			dataType: "json",
			async: false,
			cache: false,
			success: function(data, textStatus) {
				subject = data.subject;
				content = data.content;
			}
		});

		if (subject) {
			alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
			f.wr_subject.focus();
			return false;
		}

		if (content) {
			alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
			if (typeof(ed_wr_content) != "undefined")
				ed_wr_content.returnFalse();
			else
				f.wr_content.focus();
			return false;
		}

		if (document.getElementById("char_count")) {
			if (char_min > 0 || char_max > 0) {
				var cnt = parseInt(check_byte("wr_content", "char_count"));
				if (char_min > 0 && char_min > cnt) {
					alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
					return false;
				}
				else if (char_max > 0 && char_max < cnt) {
					alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
					return false;
				}
			}
		} 
		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	} 
	$(".wr_8").on("change",function(){
		if($(this).val()=='wr_1'){
			$("#crop").css("display","block");
		}else {
			$("#crop").css("display","none");
		}
	});
	</script>
</section>
<!-- } 게시물 작성/수정 끝 -->