<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<header class="article-content__header">
    <div class="">EDIT</b></div>
    <div class="article-content__header__divider--h"></div>
    <div class=""><?php echo $ch["article"]["codename"]; ?></div>
</header>
<article class="article-content">
    <div id="view-inner-edit" class="article-content__container--edit">
        <h1 class="article-content__title--edit">관리</h1>
        <h2 class="article-content__subtitle--edit">캐릭터 관리</h2>
        <div class="article-content__subcontent--edit grid-3">
        <?php if ($update_href) { ?><a href="<?php echo $update_href; ?>" class="ui-btn etc"><i class="material-icons">edit</i> 수정</a><?php } ?>
        <?php if ($delete_href) { ?><a href="<?php echo $delete_href; ?>" class="ui-btn etc" onclick="del(this.href); return false;"><i class="material-icons">delete</i> 삭제</a><?php } ?>
        <a href="<?php echo $list_href.$qstr; ?>" class="ui-btn etc"><i class="material-icons">list</i> 목록</a>
        </div>
        <?php if ($update_href && $ch['source']['body']) { ?>
            <h2 class="article-content__subtitle--edit">옷장 관리</h2>
            <div class="article-content__subcontent--edit grid-3">					
                <div class="body-add-form theme-box">
                    <form method="post" id="frm_add_body" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>" />
                        <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>" />
                        <div class="input-box">
                            <input type="text" name="add_new_body_name" value="" placeholder="옷장 이름" />
                            <input type="text" name="add_new_body" value="" placeholder="외부 링크" />
                            <input type="checkbox" name="add_new_body_secret" id="add_new_body_secret" value="1" /><label for="add_new_body_secret">스포일러</label>
                            <input type="file" name="add_new_body_file" title="용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" accept="image/*"/>
                            <button type="button" onclick="fn_body_add_form('frm_add_body');" class="ui-btn">등록</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    <footer class="article-content__footer"></footer>
</article>
<script>
    const skin_path = "<?php echo $board['bo_skin']; ?>";
    const skin_url = g5_url + "/skin/board/" + skin_path;

    function fn_body_add_form(frm) {
        var form = $("#" + frm)[0];
        var formData = new FormData(form);
        var url = skin_url;
        $('#frm_add_body').hide();
        $('.body-add-form').append("<span id='loading_add_body'>등록 중...</span>");
        $.ajax({
            cache : false,
            url : url + "/proc/add_body.php", // 요기에
            type : 'POST',
            processData: false,
            contentType: false,
            data : formData, 
            success: function(data) {
                // Toss
                var response = data;
                $('.view__body__container-closet').empty().append(response);
                form.reset();
            },
            error: function(data, status, err) {
                console.log("error!!");
            },
            complete: function() { 
                // Complete
                $('#loading_add_body').remove();
                $('#frm_add_body').show();
                $(".view__body__container-closet .animate-onload").addClass("on");
                // $('.loading').removeClass('mask');
            }
        });
    }
</script>