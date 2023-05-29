<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
?>

<div class="frame-theme-BODY">
    <div class="inner">

        <section id="bo_w">
            <!-- 게시물 작성/수정 시작 { -->
            <!--onsubmit="return fwrite_submit(this);" action="<?php echo $action_url ?>"  -->
            <form name="fwrite" id="fwrite" method="post" enctype="multipart/form-data" autocomplete="off" onsubmit="return fwrite_submit(this);" action="<?php echo $action_url ?>">
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
                        $option .= "\n" . '<input type="checkbox" id="notice" name="notice" value="1" ' . $notice_checked . '>' . "\n" . '<label for="notice">공지</label>';
                    }


					if ($is_html) {
						if ($is_dhtml_editor) {
							$option_hidden .= '<input type="hidden" value="html1" name="html">';
						} else {
							$option .= "\n".'<input type="checkbox" id="html" name="html" value="html2" '.$html_checked.'>'."\n".'<label for="html">html</label>';
						}
					}

                    if ($is_secret && $member['mb_id']) {
                        if ($is_admin || $is_secret == 1) {
                            $option .= "\n" . '<input type="checkbox" id="secret" name="secret" value="secret" ' . $secret_checked . '>' . "\n" . '<label for="secret">비밀글</label>';
                        } else {
                            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                        }
                    }
                }

                echo $option_hidden;
				if($w == 'r'){
					$write['wr_2'] = '';
					$write['wr_3'] = '';
					$write['wr_4'] = '';
				}
                ?>

                <div class="theme-form">
                    <div class="wr_header">
                        <?php if ($is_name) { ?>
                            <div><label for="wr_name">이름<strong class="sound_only">필수</strong></label>
                                <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input required" size="10" maxlength="20">
                            </div>
                        <?php } ?>
                        <div class="div_wr_pass <? if ($member['mb_id']) { ?>hide<? } ?> ">
                            <label for="wr_pass">비밀번호 <strong class="sound_only">필수</strong></label>
                            <input type="password" name="wr_pass" id="wr_pass" <? if (!$member['mb_id']) { ?>required<? } ?> class="frm_input<? if (!$member['mb_id']) { ?>required<? } ?>" maxlength="20">
                            <? if (!$member['mb_id']) { ?><br><span>*비회원은 비밀글을 작성할 수 없으며 해당 비밀번호는 글 수정/삭제시 사용됩니다.</span><? } ?>
                        </div>

                    </div>
						<?php if ($is_category) { ?>
						<div class="wr_header">
						<nav id="write_category">
						<select name="ca_name" id="ca_name" required class="required" >
							<option value="">Category</option>
							<?php echo $category_option ?>
							</select>
						</div>
						<?}?>

                    <div class="wr_header">
                        <?php if ($option) { ?>
                            <?php echo $option ?>
                        <?php } ?>
						<input type="checkbox" name="wr_10" id="wr_spoiler" value="spoiler" <?=$write['wr_10']=="spoiler" ? "checked":""?>> <label for="wr_spoiler">스포일러</label>
						<a href="<?php echo $board_skin_url ?>/emoticon_list.php" target="_blank">[이모티콘]</a>
                        <div id="autosave_wrapper">
                            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input required" size="50" maxlength="255" placeholder="제목">
                        </div>
                    </div>

					<div>
						<label for="wr_2" class="wr_label">점수</label>
						<select id="wr_2" name="wr_2">
						<option value="" <?=$write['wr_2'] == "" ? "selected":""?>></option>
						<option value="0.5" <?=$write['wr_2'] == "0.5" ? "selected":""?>>☆</option>
						<option value="1" <?=$write['wr_2'] == "1" ? "selected":""?>>★</option>
						<option value="1.5" <?=$write['wr_2'] == "1.5" ? "selected":""?>>★☆</option>
						<option value="2" <?=$write['wr_2'] == "2" ? "selected":""?>>★★</option>
						<option value="2.5" <?=$write['wr_2'] == "2.5" ? "selected":""?>>★★☆</option>
						<option value="3" <?=$write['wr_2'] == "3" ? "selected":""?>>★★★</option>
						<option value="3.5" <?=$write['wr_2'] == "5.5" ? "selected":""?>>★★★☆</option>
						<option value="4" <?=$write['wr_2'] == "4" ? "selected":""?>>★★★★</option>
						<option value="4.5" <?=$write['wr_2'] == "4.5" ? "selected":""?>>★★★★☆</option>
						<option value="5" <?=$write['wr_2'] == "5" ? "selected":""?>>★★★★★</option>
						</select>
					</div>
						<?
							$StampDir = $board_skin_path."/img/stamp/";
							$stampes = glob($StampDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
							if($stampes) { ?>
					<div>
						<?
							foreach($stampes as $stampfile){
								$filename = basename($stampfile);
								$stampimg = $board_skin_url."/img/stamp/".$filename;
						?>
							<input type="radio" name="wr_2" id="<?=$stampimg?>" value="<?=$stampimg?>" <?=$write['wr_2'] == $stampimg ? "checked":""?>>
							<label class="stamp_img" for="<?=$stampimg?>"><img src="<?=$stampimg?>"></label>
						<?}?>
					</div>
						<?}?>
					<div>
						<label for="wr_3" class="wr_label">완결 여부</label>
						<select id="wr_3" name="wr_3" value="<?=$write['wr_3']?>">
						<option value="" <?=$write['wr_3'] == "" ? "selected":""?>></option>
						<option value="단편" <?=$write['wr_3'] == "단편" ? "selected":""?>>단편</option>
						<option value="미완결" <?=$write['wr_3'] == "미완결" ? "selected":""?>>미완결</option>
						<option value="완결" <?=$write['wr_3'] == "완결" ? "selected":""?>>완결</option>
						<option value="시즌 완결" <?=$write['wr_3'] == "시즌 완결" ? "selected":""?>>시즌 완결</option>
						<option value="후기" <?=$write['wr_3'] == "후기" ? "selected":""?>>후기</option>
						<option value="감상 중" <?=$write['wr_3'] == "감상 중" ? "selected":""?>>감상 중</option>
						<option value="감상완료" <?=$write['wr_3'] == "감상완료" ? "selected":""?>>감상완료</option>
						</select>
					</div>
					<div>
						<label for="wr_4" class="wr_label">제작자</label>
						<input type="text" name="wr_4" id="wr_4" value="<?=$write['wr_4']?>" placeholder="작가/감독 등">
					</div>
					<div>
						<label for="wr_9" class="wr_label">해시태그</label>
						<input type="text" name="wr_9" id="wr_9" value="<?=$write['wr_9']?>" placeholder=",로 나뉘어집니다">
					</div>
					<div>
						<a class="textggu-open" style="cursor:pointer;">[서식보기]</a>
					</div>
					<div class="textggu-ex off">
						<div>
						<span class="textggu--title1">제목①</span>
						<span class="textggu--title2">제목②</span>
						<span class="textggu--title3">제목③</span>
						<span class="textggu--title4">제목④</span>
						<span class="textggu--title5">제목⑤</span>
						<span class="textggu--title6" data-text="부제는 여기에 작성합니다">제목⑥</span>
						<span class="textggu--title7">제목⑦</span>
						</div>
						<div>
						<span class="textggu--sub1">소제1</span>
						<span class="textggu--sub2">소제2</span>
						<span class="textggu--sub3">소제3</span>
						<span class="textggu--sub4">소제4</span>
						<span class="textggu--sub5">소제5</span>
						<span class="textggu--sub6">소제6</span>
						<span class="textggu--sub7">소제7</span>
						</div>
						<div>
						<span class="textggu--etc1">기타1</span>
						<span class="textggu--etc2">기타2</span>
						 <span class="textggu--etc3">기타3</span>
						 <span class="textggu--etc4">기타4</span>
						 <span class="textggu--etc5">기타5</span>
						 <span class="textggu--etc6">기타6</span>	
						</div>
					</div>
                    <div class="wr_content">
                        <?php if ($write_min || $write_max) { ?>
                            <!-- 최소/최대 글자 수 사용 시 -->
                            <p id="char_count_desc">이 게시판은 최소 <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하까지 글을 쓰실 수 있습니다.</p>
                        <?php } ?>
                        <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 
                        ?>
                        <?php if ($write_min || $write_max) { ?>
                            <!-- 최소/최대 글자 수 사용 시 -->
                            <div id="char_count_wrap"><span id="char_count"></span>글자</div>
                        <?php } ?>
                    </div>

						<div>
							<label for="review_cover">리뷰 커버</label>
							<input type="file" id="review_cover" name="review_cover" title="파일첨부 : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
							<input type="text" name="review_cover" value="<?=$write['wr_1']?>" class="frm_input" placeholder="외부 링크 이용시 사용"/>
							<?php if($w == 'u' && $write['wr_1']) { ?>
								<input type="checkbox" id="review_cover_del" name="review_cover_del" value="1"> <label for="review_cover_del">파일 삭제</label>
							<? } ?>							
						</div>
					<?if(!$is_mobile) {?>
                    <div class="wr_footer">
						<div class="wr_file">
							<? for($ii=0; $is_file && $ii < $file_count; $ii++){?>
							<label for="file<?php echo $ii ?>" class="cfile_btn">파일 #<?=$ii?></label>
							<div class="upload_file"></div>
							<input type="file" id="file<?php echo $ii ?>" name="bf_file[]" title="파일첨부 <?php echo $ii ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input full bf_file">
							<div class="div_img">
							<?php if ($is_file_content) { ?>
							 <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$ii]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
							<?php } ?>
							<?php if($w == 'u' && $file[$ii]['file']) { ?>
							<div class="img">
							<input type="checkbox" class="bf_file_del" id="bf_file_del<?php echo $ii ?>" name="bf_file_del[<?php echo $ii;  ?>]" value="1" style="display:none"> <label for="bf_file_del<?php echo $ii ?>"><img src="<? echo $file[$ii]['path'] . '/' . $file[$ii]['file']; ?>"></label>
							</div>
							<?php } ?>
							</div>
							<?}?>
						</div>
					</div>
					<?}?>
					<?if($is_mobile) {?>
                    <div class="wr_footer">
                        <div class="wr_file">
                            <label for="bf_file" class="file_btn">이미지 업로드</label>
                            <div class="upload_file"></div>
                            <input type="file" multiple="multiple" id="bf_file" name="bf_file[]" accept=".jpg, .jpeg, .png, .gif" data-filecount="<?php echo $file_count ?> " title="파일첨부 <?php echo $i + 1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input bf_file">
                            <div class="div_img">
                                <?php for ($i = 0; $is_file && $i < $file_count; $i++) { ?>
                                    <?php if ($is_file_content) { ?>
                                        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">
                                    <?php } ?>
                                    <?php if ($w == 'u' && $file[$i]['file']) { ?>
                                        <div class="img">
                                            <input type="checkbox" class="bf_file_del" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1" style="display:none"> <label for="bf_file_del<?php echo $i ?>"><img src="<? echo $file[$i]['path'] . '/' . $file[$i]['file']; ?>"></label>
                                        </div>
                                    <?php } ?>
                                <? } ?>
                            </div>
                        </div>
                    </div>
					<?}?>
                        <div style="justify-content: end;">
                            <button id="btn_submit" class="btn_submit ui-btn point" onclick="fwrite_submit(); return false;">SEND</button>
                            <a id="modal_close" href="#close" rel="modal:close" style="display:none"></a>
                        </div>
                </div>
            </form>

        </section>
        <!-- } 게시물 작성/수정 끝 -->

    </div>
</div>
<!-- </div> -->

<script>
    let list_href = "./board.php?bo_table=<?php echo $bo_table ?>&page=<?php echo $page ?>"



    $("#secret").on("click ", function() {
        if (this.checked) {
            $(".div_wr_pass").removeClass("hide");
            $("#wr_pass").addClass("required");
            $("#wr_pass").attr("required", true);
        } else {
            $(".div_wr_pass").addClass("hide");
            $("#wr_pass").removeClass("required");
            $("#wr_pass").removeAttr("required");
        }
    })

    $(".bf_file_del").on("click", function() {
        if (this.checked) {
            $(this).parent().addClass("selected");
        } else {
            $(this).parent().removeClass("selected");
        }
    });
    <?php if ($write_min || $write_max) { ?>
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



    function fwrite_submit() {

        var f = $("#fwrite")[0];
        if (f.bf_file[0].files.length > $(f.bf_file[0]).data("filecount")) {
            alert("파일 첨부 개수를 초과했습니다.");
            return false;
        }
        $(f.bf_file[0].files).each(function(idx, file) {
            if (file != "") {
                var ext = file.name.split('.').pop().toLowerCase();
                if ($.inArray(ext, ['jpg', 'jpeg', 'png', 'gif']) == -1) {
                    alert('jpg, jpeg, png, gif 파일만 업로드 할 수 있습니다.');
                    return false;
                }
            }
        });
        if ($("#wr_name").val() == "" ||
            $("#wr_subject").val() == "" ||
            $("#wr_content").val() == "") {
            alert("폼을 모두 채우신 후 등록 버튼을 눌러 주세요.");
            return false;
        }
        if (!$(".div_wr_pass").hasClass("hide") && $("#wr_pass").val() == "") {
            alert("비밀번호를 입력해 주세요.");
            return false;
        } else if ($(".div_wr_pass").hasClass("hide")) {
            $("#wr_pass").val("");
        }

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

        $.ajax({
            url: g5_bbs_url + "/write_update.php",
            type: "POST",
            data: new FormData(f),
            processData: false,
            contentType: false,
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                $("#fwrite")[0].reset();
                $('#modal_close').get(0).click();
                acyncMovePage(list_href);
            },
            error: function(xhr, status) {
                // console.log(xhr.responseText);
                $('.modal').children().remove();
                $('.modal').hide().replaceWith(xhr.responseText).fadeIn(0);
                location.href = list_href;
            }
        });
    }
</script>