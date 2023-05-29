<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);



$is_secret=$board['bo_use_secret']; 

$is_error = false;
$option = '';
$option_hidden = '';


if(!$is_error) { 


	// 카테고리 재정의$is_category = false;
	$category_option = '';
	if ($board['bo_use_category']) {
		$ca_name = "";
		if (isset($write['ca_name']))
			$ca_name = $write['ca_name'];
		$category_option = get_category_option($bo_table, $ca_name);
		$is_category = true;
	}


	$image_url = $board_skin_url."/img/no_image.png";
	if($w == 'u') { 
		if($write['wr_type'] == 'URL') {
			$image_url = $write['wr_url'];
			$img_data = "width : ".$write['wr_width']."px / height : ".$write['wr_height']."px";
		} else if($file[0]['file']) { 
			$image_url = $file[0]['path']."/".$file[0]['file'];
			$img_data = "width : ".$file[0]['wr_width']."px / height : ".$file[0]['wr_height']."px";
		} 
	}
		$write['wr_subject'] = $member['mb_nick'];
		if(!$write['wr_subject']) $write['wr_subject'] = 'GUEST';

	?>

	<div id="load_log_board">
		<section id="bo_w" class="mmb-board chick">
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
			<input type="hidden" name="wr_subject" value="<?=$write['wr_subject']?>" /> 
			<input type="hidden" name="wr_width" id="wr_width" value="<?php if($w=='u') echo $write['wr_width']; ?>">
			<input type="hidden" name="wr_height" id="wr_height" value="<?php if($w=='u') echo $write['wr_height']; ?>">
			<?php echo $option_hidden; ?>
			
			<!-- LOG 등록 부분 -->
			

			<?if($w!='u'){?>
				<? if(!$is_member && $board['bo_write_level']) { ?> 
				<div class="guest-box">
					<input type="text" name="wr_name" value="<?=$_COOKIE['MMB_NAME']?>" placeholder="이름" /> 
					<input type="password" name="wr_password" value="<?=$_COOKIE['MMB_PW']?>" placeholder="비밀번호" />&nbsp;&nbsp;
					<input type="checkbox" value="1" name="cookie" id="cookie" <?=($_COOKIE['MMB_NAME']||$_COOKIE['MMB_PW']) ? "checked":""; ?>>
					<label for="cookie">쿠키</label>
				</div> 
				<? } }?>
				<div class="uploadline">
				<?php if ($is_category) { ?>
				<select name="ca_name" id="ca_name" required class="required" >
				<option value="">카테고리</option>
				<?php echo $category_option ?>
				</select> 
			<?php } ?>
			
 
					<!-- 일반 커맨드 -->
					<?
						/******************************************************
						* :: 주사위의 경우, 한번 굴린 데이터가 남아 있을 시 수정 불가
						* :: 이때, 다른 카테고리의 선택을 할 수 없다.
						*******************************************************/
		$sec='';
	if ($is_notice || $is_html || $is_secret || $is_mail) {
		$option = '';
		//if ($is_notice) {
		//	$option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
		//}


	}

					?> 
					<select name="set_secret" id="set_secret">
						<option value="">전체공개</option>
						<?=$sec?>
					</select>

							<?php if ($option) {echo $option;} ?>
					<dl id="set_protect" style="display:<?=$w=='u' && $pro_select ? 'block':'none'?>;">
						<dt><label for="wr_protect">보호글 암호</label></dt>
						<dd><input type="text" name="wr_protect" id="wr_protect" value="<?=$write['wr_protect']?>" maxlength="20"></dd>
					</dl>
			<div class="upload_box">
				
					<select name="wr_type" onchange="fn_log_type(this.value);" style="display:none;">
						<option value="TEXT" <?=$write['wr_type'] == "TEXT" ? "selected" : ""?>>제목</option>
					</select>
					<div id="add_TEXT">
						<textarea id="wr_text" name="wr_text" class="view_image_area" cols="20" rows="1" placeholder="새 항목"><?=$write['wr_text']?></textarea>
					</div>
			</div>
			
					<button type="submit" id="btn_submit" accesskey="s" class="ui-btn point">게시</button>
			</div>
			</form>
		</section>
	<!-- } 게시물 작성/수정 끝 -->
	</div>

<script>
	<?php if($board['bo_write_min'] || $board['bo_write_max']) { ?>
	// 글자수 제한
	var char_min = parseInt(<?php echo $board['bo_write_min']; ?>); // 최소
	var char_max = parseInt(<?php echo $board['bo_write_max']; ?>); // 최대
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

<? if($w == '') { ?>
		if(f.wr_type.value == 'UPLOAD') {
			if(document.getElementById('wr_file').value == '') { 
				alert("업로드할 로그를 등록해 주시길 바랍니다.");
				return false;
			}
		} else if(f.wr_type.value == 'URL') { 
			var url = document.getElementById('wr_url').value;
			url = url.replace(/^\s+|\s+$/gm,'');
			if(url.search(/http/i)=='-1') url='';
			if(url == '') { 
				alert("등록할 로그 URL을 http를 포함하여 입력해 주시길 바랍니다.");
				return false;
			}
		}else if(f.wr_type.value == 'VIDEO') {
			var video = document.getElementById('wr_video').value;
			video = video.replace(/^\s+|\s+$/gm,'');
			if(video.search(/video|iframe|embed/i)=='-1') video='';
			if(video == '') { 
				alert("등록할 영상 소스 코드를 입력해 주시길 바랍니다.");
				return false;
			}
		}else if(f.wr_type.value == 'TEXT') {
			var text = document.getElementById('wr_text').value;
			text = text.replace(/^\s+|\s+$/gm,'');
			if(text == '') { 
				alert("등록할 내용을 입력해 주시길 바랍니다.");
				return false;
			}
		}
<? } ?>
		document.getElementById("btn_submit").disabled = "disabled";
		return true;
	}


	$('.view_image_area').on('change', function() {
		var image = $(this).val();
		var type = $(this).attr('type');

		if(type == 'file') { 
			previewImage(this,'view_image'); 
		} else {
			if($(this).attr('id')=='wr_video'){  
			$("#view_image").append("<div id='prev_view_image'>"+image+"</div>"); 
			var w=image.match(/(width=").*?"|(width=).*?\s/gi);
			var h=image.match(/(height=").*?"|(height=).*?\s/gi);
			w=w[0].replace('width=','');
			h=h[0].replace('height=','');
			w=w.replace(/"/g, '');
			h=h.replace(/"/g, '');
			$('#wr_width').val(trim(w));
			$('#wr_height').val(trim(h)); 
			$('#view_image > span').text("width : " + w + "px / height : " + h + "px");
			}
			else checkImage(image, complete, '', 'view_image');
		}
	}); 

	function reset_image(previewId) {
		var preview = document.getElementById(previewId);
		var prevImg = document.getElementById("prev_" + previewId); //이전에 미리보기가 있다면 삭제
		if (prevImg) {
			preview.removeChild(prevImg);
		}

		$('#wr_width').val('');
		$('#wr_height').val('');

		$('#view_image > span').text("");
	}

	function previewImage(targetObj, previewId) {
		var preview = document.getElementById(previewId); //div id   
		var ua = window.navigator.userAgent;
		var files = targetObj.files;

		$('#view_image_loading').show();

		reset_image(previewId);

		for ( var i = 0; i < files.length; i++) {

			var file = files[i];

			var imageType = /image.*/; //이미지 파일일경우만.. 뿌려준다.
			if (!file.type.match(imageType)) {
				continue;
			}

			var img = document.createElement("img");
			img.id = "prev_" + previewId;
			img.classList.add("obj");
			img.file = file;

			if (window.FileReader) { // FireFox, Chrome, Opera 확인.
				var reader = new FileReader();
				reader.onloadend = (function(aImg) {
					return function(e) {
						aImg.src = e.target.result;
						$('#view_image_loading').hide();
						preview.appendChild(img);
						checkImage(aImg.src,complete,'','view_image');
					};
				})(img);
				reader.readAsDataURL(file);
			} else { // safari is not supported FileReader
				//alert('not supported FileReader');
				if (!document.getElementById("sfr_preview_error_"
						+ previewId)) {
					var info = document.createElement("p");
					info.id = "sfr_preview_error_" + previewId;
					info.innerHTML = "not supported FileReader";
					preview.insertBefore(info, null);
				}
			}
		}
		
		if(i > 0) { 
			
			//preview.style.background="none";
		} else {
			complete('F');
		}
	}


	function checkImage(url, callback, timeout, previewId) {
		timeout = timeout || 5000;
		
		$('#view_image_loading').show();

		var timedOut = false, timer;
		var img = new Image();
		var preview = document.getElementById(previewId);

		reset_image(previewId);

		img.onerror = img.onabort = function() {
			if (!timedOut) {
				clearTimeout(timer);
				callback("F");
			}
		};
		img.onload = function() {
			if (!timedOut) {
				clearTimeout(timer);
				img.id = "prev_" + previewId;
				img.classList.add("obj");
				callback("S", img.width, img.height);
				preview.appendChild(img);
				$('#view_image_loading').hide(); 
			}
		};
		img.src = url;

		timer = setTimeout(function() {
			timedOut = true;
			callback("F");
		}, timeout); 
	}

	function complete(message, w, h) {
		if(message == 'S') { 
			$('#wr_width').val(w);
			$('#wr_height').val(h);
			$('#view_image > span').text("width : " + w + "px / height : " + h + "px");
		} else { 
			$('#view_image > span').text("");
		}
	}

	function fn_log_type(type) { 
		$('#add_'+type).siblings().hide();
		$('#add_'+type).show();

		$('#wr_url').val('');
		$('#wr_file').val('');
		$('#wr_video').val('');
		$('#wr_text').val('');

		reset_image('view_image');
	}
	$('#set_secret').on('change', function() {
		var selection = $(this).val();
		if(selection=='protect') $('#set_protect').css('display','block');
		else {$('#set_protect').css('display','none'); $('#wr_protect').val('');}
	});  

 
$('.new_win').on('click', function() {
	var target = $(this).attr('href');
	window.open(target, 'emoticon', "width=400, height=600");
	return false;
});

$('.change-thumb').on('change', function() {
	var select_item = $(this).find('option:selected');

	var thumb = select_item.data('thumb');

	if(typeof(thumb) != "undefined") {
		// 썸네일이 있는 경우
		$(this).closest('.has-thumb').find('.ui-thumb').empty().append("<img src='"+thumb+"' alt='' />");
	} else { 
		$(this).closest('.has-thumb').find('.ui-thumb').empty();
	}
});
 

</script>

<? } ?>