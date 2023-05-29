<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
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
				//$option_hidden .= '<input type="hidden" value="html1" name="html">';
			} else {
				//$option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">html</label>';
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
			//$option .= "\n".'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'<label for="mail">답변메일받기</label>';
		}
	}

	echo $option_hidden;
	?>

	<div class="board-write theme-box">
	 
	<dl>
		<dt>기본 옵션</dt>
		<dd>
			<div>
			<?php if ($is_category) { ?>
			<nav id="write_category">
				<select name="ca_name" id="ca_name" required class="required" >
					<option value="">카테고리</option>
					<?php echo $category_option ?>
				</select> 
				</nav> 
			<?}?>
			<?php echo $option ?>
			</div>
			<p>찍을 스탬프 개수: <input type="text" value="<?=$write['wr_1']?>" name="wr_1" size="5" required />
			한줄에 최대 <input type="text" value="<?=$write['wr_6'] ? $write['wr_6'] : 10?>" size="5" name="wr_6" required>개</p>
			<p>스탬프 사이즈(여백포함): 
			<? if($write['wr_2']) $stamp_size=explode("||",$write['wr_2']);?>
				가로 <input type="text" value="<?=$stamp_size[0] ? $stamp_size[0] : 80?>" size="3" name="stamp_size[0]">px 
				세로 <input type="text" value="<?=$stamp_size[1] ? $stamp_size[1] : 80?>" size="3" name="stamp_size[1]">px</p>
		</dd>
	</dl>
	<hr class="line">
	<dl>
		<dt>추가 옵션</dt>
		<dd>
			<?echo help("설정하지 않을시 - 박스스타일: 기본 박스스타일, 기간:시작일 적지 않을시 글 작성 당일부터 시작, 마감일 적지 않을시 무기한(내용 출력안함)");
			if($write['wr_3']) $box_style=explode("||",$write['wr_3']);
			if($write['wr_4']) $duration = explode("||",$write['wr_4']);
			?>
			<p>박스 스타일: 배경색 <input type="text" value="<?=$box_style[0]?>" name="box_style[0]" size="8">&nbsp;&nbsp;
				라인색 <input type="text" value="<?=$box_style[1]?>" name="box_style[1]" size="8"> </p>
			<p>기간 (형식:YYYYMMDD): 시작 <input type="text" value="<?=$duration[0]?>" name="duration[0]" size="8"> 
				마감 <input type="text" value="<?=$duration[1]?>" name="duration[1]" size="8"></p>
			<p>보상(태그사용가능): <input type="text" value="<?=$write['wr_5']?>" name="wr_5" maxlength="255" class="form-input full"></p>
			<?=help("보상내용 태그포함 최대 255자까지 가능");?>
		</dd>
	</dl>
	<hr class="line">
	<dl>
		<dt>제목</dt>
		<dd><input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input required full" size="50" maxlength="255"></dd>
	</dl>
	<input type="hidden" value="<?=$write['wr_content']?>" name="wr_content">
	<hr class="line">
	<dl>
		<dt>빈 스탬프</dt>
		<dd> 
		<?=help("업로드하지 않을 경우 스킨 폴더에 있는 no_stamp.png 파일을 사용합니다.")?>
			<input type="file" name="bf_file[0]" title="파일첨부 1 : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input full">
			<?php if ($is_file_content) { ?>
			<input type="text" name="bf_content[0]" value="<?php echo ($w == 'u') ? $file[0]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
			<?php } ?>
			<?php if($w == 'u' && $file[0]['file']) { ?>
			<input type="checkbox" id="bf_file_del0" name="bf_file_del[0]" value="1"> <label for="bf_file_del0"><?php echo $file[0]['source'].'('.$file[0]['size'].')';  ?> 파일 삭제</label>
			<?php } ?></dd>
	</dl>
	<hr class="line">
	<dl>
		<dt>스탬프 이미지</dt>
		<dd>
		<?if($board['bo_upload_count']<2) echo help("게시판 설정에서 파일 업로드 개수를 2 이상으로 설정 해주세요.");?>
		<?=help("업로드하지 않을 경우 스킨 폴더에 있는 stamp.png 파일을 사용합니다. 여러개 업로드 할 경우 랜덤으로 출력됩니다.")?>
		<?for($i=1;$i<$board['bo_upload_count'];$i++){?>
			<input type="file" name="bf_file[]" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input full">
			<?php if ($is_file_content) { ?>
			<input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
			<?php } ?>
			<?php if($w == 'u' && $file[$i]['file']) { ?>
			<input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일 삭제</label>
			<?php } ?>
		<?}?>
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
	</script>
</section>
<!-- } 게시물 작성/수정 끝 -->