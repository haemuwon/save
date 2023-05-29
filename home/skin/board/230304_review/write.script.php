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

    function html_auto_br(obj) {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        } else
            obj.value = "";
    }

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
        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url + "/ajax.filter.php",
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
            alert("제목에 금지단어('" + subject + "')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('" + content + "')가 포함되어있습니다");
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
                    alert("내용은 " + char_min + "글자 이상 쓰셔야 합니다.");
                    return false;
                } else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 " + char_max + "글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  
        ?>

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