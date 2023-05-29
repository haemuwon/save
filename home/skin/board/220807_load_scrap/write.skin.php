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
		<section id="bo_w" class="mmb-board<?if($board['bo_use_chick']){echo " chick";}?>">
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
			
			<!-- LOG 등록 부분 -->
			

			
				<? if(!$is_member && $board['bo_write_level']) { ?> 
				<div class="guest-box">
					<input type="text" name="wr_name" value="<?=$_COOKIE['MMB_NAME']?>" placeholder="이름" /> 
					<input type="password" name="wr_password" value="<?=$_COOKIE['MMB_PW']?>" placeholder="비밀번호" />&nbsp;&nbsp;
					<input type="checkbox" value="1" name="cookie" id="cookie" <?=($_COOKIE['MMB_NAME']||$_COOKIE['MMB_PW']) ? "checked":""; ?>>
					<label for="cookie"><i class="fas fa-cookie"></i></label>
				</div> 
				<? } ?>
			<div class="txt-left option_box">
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


		if ($is_secret) {
			if ($is_admin || $is_secret==1) {
				if($secret_checked)$sec_select="selected";
				$sec .='<option value="secret" '.$sec_select.'>비밀글</option>';
			} else {
				$option_hidden .= '<input type="hidden" name="secret" value="secret">';
			}
		} 
	}
		if($write['wr_secret']=='1') $mem_select="selected";
		if($write['wr_protect']!='') $pro_select="selected";
		if($is_member) {$sec .='<option value="protect" '.$pro_select.'>보호글</option>';
		$sec .='<option value="member" '.$mem_select.'>멤버공개</option>';}

					?> <?php echo $option_hidden; ?>
					<select name="set_secret" id="set_secret">
						<option value="">전체공개</option>
						<?=$sec?>
					</select>
							<?php if ($option) {echo $option;} ?>
					
							<input type="checkbox" id="wr_adult" name="wr_adult" value="1" <?=$write['wr_adult'] ? "checked" : ""?>/> 
							<label for="wr_adult">블라인드</label>
						<? if($board['bo_use_noname'] && $is_member) { ?> 
							<input type="checkbox" id="wr_noname" name="wr_noname" value="1" <?=$write['wr_noname'] ? "checked" : ""?>/>
							<label for="wr_noname">익명</label> 
							<? } ?>
						<?if(!$board['bo_use_chick']||$w=='u'){?><a href="<?php echo $board_skin_url ?>/emoticon_list.php" class="new_win">[이모티콘]</a> <?}?>
					<dl id="set_protect" style="display:<?=$w=='u' && $pro_select ? 'block':'none'?>;">
						<dt><label for="wr_protect">보호글 암호</label></dt>
						<dd><input type="text" name="wr_protect" id="wr_protect" value="<?=$write['wr_protect']?>" maxlength="20"></dd>
					</dl>
			</div>
			<hr class="padding small" /> 
			<div class="upload_box">
							<dl>
					<dt>
						<label for="wr_1">Scrap Memo.</label>
					</dt>
					<dd>
						<input type="text" name="wr_1" value="<?php if($w=="u"){echo$write['wr_1'];} ?>" id="wr_1" class="frm_input" size="50" maxlength="12" placeholder="글자수 제한 12자 있음">
					</dd>
				</dl>	
			<dl>
				<dt>
					<select name="wr_type" onchange="fn_log_type(this.value);">
						<option value="UPLOAD" <?=$write['wr_type'] == "UPLOAD" ? "selected" : ""?>>UPLOAD</option>
						<option value="URL" <?=$write['wr_type'] == "URL" ? "selected" : ""?>>TWITTER</option>
						<option value="VIDEO" <?=$write['wr_type'] == "VIDEO" ? "selected" : ""?>>VIDEO</option>
						<option value="TEXT" <?=$write['wr_type'] == "TEXT" ? "selected" : ""?>>TEXT</option>
					</select>
				</dt>
				<dd>
					<div id="add_UPLOAD" <?=($write['wr_type'] == "UPLOAD" || $write['wr_type'] == "") ? "":"style='display: none;'"?>>
						<input type="file" id="wr_file" name="bf_file[]" title="로그등록 :  용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input view_image_area" />
						<?php if($w == 'u' && $file[0]['file']) { ?>
							<input type="checkbox" id="bf_file_del0" name="bf_file_del[0]" value="1"> <label for="bf_file_del0"><?php echo $file[0]['source'].'('.$file[0]['size'].')';  ?> 로그 삭제</label>
						<?php } ?>
					</div>
					<div id="add_URL" <?=$write['wr_type'] == "URL" ? "": "style='display: none;'"; ?>>
						<input type="text"  name="wr_url" value="<?=$write['wr_url']?>" title="트위터 링크를 가져와 주시길 바랍니다." id="wr_url" class="frm_input view_image_area" placeholder="트위터 링크 입력"/>* 트위터 개별 링크를 그대로 가져와주세요. 비공개 계정은 불가능.
					</div>
					<div id="add_VIDEO" <?=$write['wr_type'] == "VIDEO" ? "" : "style='display: none;'"?>>
						<input type="text" id="wr_video" name="wr_video" value="<?=$write['wr_video']?>" class="frm_input view_image_area" cols="20" rows="2" placeholder="유튜브 영상 주소 입력">
						<p style="font-size:12px;">* 주소창의 https://www.youtube.com/watch?v='이 부분'을 입력해주세요.</p> 
					</div>
					<div id="add_TEXT" <?=$write['wr_type'] == "TEXT" ? "" : "style='display: none;'"?>>
						<textarea id="wr_text" name="wr_text" class="view_image_area" cols="20" rows="8" placeholder="내용"><?=$write['wr_text']?></textarea>
						서체 <select name="wr_2" id="wr_2">
						<option value="Pretendard JP" <?=$write['wr_2'] == "Pretendard JP" ? "selected" : ""?>>Pretendard</option>
						<option value="ChosunSm" <?=$write['wr_2'] == "ChosunSm" ? "selected" : ""?>>조선신명조</option>
						<option value="Chosunilbo_myungjo" <?=$write['wr_2'] == "Chosunilbo_myungjo" ? "selected" : ""?>>조선일보명조</option>
						<option value="HBIOS-SYS" <?=$write['wr_2'] == "HBIOS-SYS" ? "selected" : ""?>>HBIOS-SYS</option>
						<option value="Galmuri9" <?=$write['wr_2'] == "Galmuri9" ? "selected" : ""?>>갈무리9</option>
						<option value="DungGeunMo" <?=$write['wr_2'] == "DungGeunMo" ? "selected" : ""?>>둥근모</option>
						<option value="RIDIBatang" <?=$write['wr_2'] == "RIDIBatang" ? "selected" : ""?>>리디바탕</option>
						<option value="EarlyFontDiary" <?=$write['wr_2'] == "EarlyFontDiary" ? "selected" : ""?>>다이어리체</option>
						<option value="KyoboHandwriting2020A" <?=$write['wr_2'] == "KyoboHandwriting2020A" ? "selected" : ""?>>교보손글씨 2020 박도연</option>
						<option value="Dotum" <?=$write['wr_2'] == "Dotum" ? "selected" : ""?>>돋움</option>
						</select>
						&nbsp;&nbsp;글씨 크기 <input type="text" name="wr_3" id="wr_3" size="5" value="<?=$write['wr_3']?>"> px
						<p style="font-size:12px">* 폰트 기본 크기 13px</p>
						<br>
						H <input type="text" name="t_height" id="t_height" size="5" value="<?=$write['wr_height']?>"> px
						<p style="font-size:12px">* 태그사용불가. H값 설정시 고정된 높이에서 스크롤 됩니다.</p>
					</div>
					<input type="text" style="display:none;" name="wr_dum" value=""><?//@201012?>
				</dd>
			</dl>

		
			<?if($board['bo_use_chick']){?><button type="submit" id="btn_submit" accesskey="s" class="ui-btn point">업로드</button><?}?>
			</div>
			<hr class="padding small" /> 

				<?if(!$board['bo_use_chick']){?>
			<div>

				<?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
				<!--<dl>
					<dt>
						<label for="wr_link<?php echo $i ?>"><i class="icon link"></i>Link #<?php echo $i ?></label>
					</dt>
					<dd>
						<input type="text" name="wr_link<?php echo $i ?>" value="<?php if($w=="u"){echo$write['wr_link'.$i];} ?>" id="wr_link<?php echo $i ?>" class="frm_input" size="50">
					</dd>
				</dl>-->
				<?php }?>

				 
			</div>	 

				<?php }else {?>
				<input type="hidden" name="wr_content" value="" id="wr_content">
				<?}?>
		<?if(!$board['bo_use_chick']||$w=='u'){?>	
			<div>
				<?php if ($is_name) { ?>  
					<input type="text" name="wr_name" value="<?=$_COOKIE['MMB_NAME']?>" id="wr_name" required class="frm_input required" size="10" maxlength="20" style="max-width:40%;" placeholder="이름" />
				<?php } ?>

				<?php if ($is_password) { ?> 
					<input type="password" name="wr_password" id="wr_password" value="<?=$_COOKIE['MMB_PW']?>" class="frm_input" maxlength="20" style="max-width:40%;" placeholder="비밀번호" />
				<?php } ?>

				<?if($is_name||$is_password){?><input type="checkbox" value="1" name="cookie" id="cookie" <?=($_COOKIE['MMB_NAME']||$_COOKIE['MMB_PW']) ? "checked":""; ?>>
					<label for="cookie">쿠키</label>	
				<?}?>
			</div>
			<hr class="padding" />
		
			<div class="txt-center">
				<button type="submit" id="btn_submit" accesskey="s" class="ui-btn point"><?=$w=='u' ? "수정":"등록";?></button>
				
			</div>



		<?}?>
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
				"content": f.wr_content.value, 
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
		if(f.wr_dum.value!=''){
			alert("");
			return false;
		}//@201012

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