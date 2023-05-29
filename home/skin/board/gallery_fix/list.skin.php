<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
if($board['bo_type']=='board')$category_option = get_category_option($bo_table, $sca);
if($board['bo_table_width']==0) $width="100%";
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0); 

$gall_size=array();
$gall_margin=array();
$gall_w=240;
$gall_h=240;
$gall_mw = 10;
$gall_mh = 10;
if($board['bo_1']){
    $gall_size=explode("|",$board['bo_1']);
    $gall_w=trim($gall_size[0]);
    if(count($gall_size)>1)
        $gall_h=trim($gall_size[1]);
    else
        $gall_h=trim($gall_size[0]);
}
if($board['bo_2']){
    $gall_margin=explode("|",$board['bo_2']);
    $gall_mh=trim($gall_margin[0]);
    if(count($gall_margin)>1)
        $gall_mw=trim($gall_margin[1]);
    else
        $gall_mw=trim($gall_margin[0]);
}
?>
<style>
    #gall_ul .gall_li{
        margin-bottom:<?=$gall_mh?>px;
    }
    #gall_ul .gall_li:not(.bo_notice){
        width:<?=$gall_w?>px;
        height:<?=$gall_h?>px;
    }
</style>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="max-width:<?php echo $width; ?>">

	
	<!-- 게시판 카테고리 시작 { -->
	<? if ($is_category) { ?>
	<nav id="navi_category">
	<?if($board['bo_type']=='board'){?>
		<select name="sca" id="sca" onchange="location.href='?bo_table=<?=$bo_table?>&sca=' + this.value;">
			<option value="">전체</option>
			<? echo $category_option ?>
		</select>
	<?}else {?>
		<ul>
			<?php echo $category_option ?>
		</ul>
	<?}?>
	</nav>
	<? } ?>
	<!-- } 게시판 카테고리 끝 -->


    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div id="bo_btn_top"> 
        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user"> 
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" target="_black" class="admin ui-btn">게시판관리</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="ui-btn point">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

    <form name="fboardlist"  id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">


    <ul id="gall_ul" class="gall_row">
        <?php for ($i=0; $i<count($list); $i++) {

			$img_content="none";

			if (!$list[$i]['is_notice']) {  
				$thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'],$gall_w,$gall_h,false,true);
				if($thumb['src']) 
					$img_content =  "url('".$thumb['src']."') center no-repeat;background-size:cover";
				else $img_content="none";
			} 


         ?>
        <li class="gall_li<? if ($list[$i]['is_notice']) echo " bo_notice";?>">
            <div class="gall_box theme-box" style="background:<?=$img_content?>;">
                <div class="gall_chk">
                <?php if ($is_checkbox) { ?>
                <label for="chk_wr_id_<?php echo $i ?>"><span class="sound_only"><?php echo $list[$i]['subject'] ?></span>
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>"></label>
                <?php } ?>
                <span class="sound_only">
                    <?php
                    if ($wr_id == $list[$i]['wr_id'])
                        echo "<span class=\"bo_current\">열람중</span>";
                    else
                        echo $list[$i]['num'];
                     ?>
                </span>
                </div>
                <div class="gall_con">  
					<a href="#" onclick="<?=$board['bo_read_level']<=$member['mb_level'] ? "open_view('".$bo_table."','".$list[$i]['wr_id']."');return false;": "void(0);";?>" class="bo_tit" >
						<?php if ($list[$i]['is_notice']) { // 공지사항  ?>
						<i class="ui-btn point notice_icon">!</i>
						<?php }?>
						<strong <?php if (!$list[$i]['is_notice']) { ?>class="sound_only"<?}?>>
						<?
						// echo $list[$i]['icon_reply']; 갤러리는 reply 를 사용 안 할 것 같습니다. - 지운아빠 2013-03-04
						if ($is_category && $list[$i]['ca_name']) {
						 ?>
						[<?php echo $list[$i]['ca_name'] ?>]
						<?php } ?>
						<?php echo $list[$i]['subject'] ?>
						</strong>  						
					 </a>  
                </div>
            </div>
        </li>
        <?php } ?>
        <?php if (count($list) == 0) { echo "<li class=\"empty_list\">게시물이 없습니다.</li>"; } ?>
    </ul>

     <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($is_checkbox) { ?> 
			<li><input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
			<label for="chkall"><span id="checkall" class="ui-btn etc" >전체</span></label></li>
            <li><input type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="ui-btn admin"></li>
            <li><input type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="ui-btn admin"></li>
            <li><input type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="ui-btn admin"></li>
            <?php } ?>
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="ui-btn">목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="ui-btn point">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>
    <?php } ?>
    </form>
     
       <!-- 게시판 검색 시작 { -->
    <fieldset id="bo_sch">
        <legend>게시물 검색</legend>

        <form name="fsearch" method="get">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sop" value="and">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
           <!-- <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
            <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option> -->
            <option value="mb_id,1"<?php echo get_selected($sfl, 'mb_id,1'); ?>>회원아이디</option>
            <!--<option value="mb_id,0"<?php echo get_selected($sfl, 'mb_id,0'); ?>>회원아이디(코)</option>-->
            <option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>글쓴이</option>
           <!-- <option value="wr_name,0"<?php echo get_selected($sfl, 'wr_name,0'); ?>>글쓴이(코)</option>-->
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색">
        <button type="submit" value="검색" class="ui-btn">검색</button>
        </form>
    </fieldset>
    <!-- } 게시판 검색 끝 -->   

</div>

<div id="view_box">
	<a href="#" id="close_area"><span class="sound_only">close</span></a>
<div class="fix-layout">
	<div id="view_area" style="width:100%">
	</div>
</div>
</div>
<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>



<!-- 페이지 -->
<?php echo $write_pages;  ?>
<script> 
const gall_max=$("#gall_ul").width();
const gall_w=<?=$gall_w?>;
const margin=<?=$gall_mw?>;
let cols=Math.floor((gall_max+margin)/(gall_w+margin));
let new_w=(cols-1)*(gall_w+margin)+gall_w;
$("#gall_ul").css("max-width",new_w+"px");
let html=[];
const items=$(".gall_li:not(.bo_notice)").length;
let leftover=cols-items%cols;
if(leftover==cols) leftover=0;
for(i=0;i<leftover;i++){
    html.push('<li class="gall_li empty"></li>');
}
$("#gall_ul").append(html.join(''));

$("#close_area").click(function(){
	$("#view_box").removeClass('on');
	$("#view_area").empty();
	return false;
});

function open_view(table,idx) {
	hlink='<?=$board_skin_url?>/view.php?bo_table='+table+'&wr_id='+idx;
	$.ajax({
		async: true
		, url: hlink
		, beforeSend: function() {}
		, success: function(data) {
			// Toss
			var response = data;
			$('#view_area').empty().append(response);
			$('#view_box').addClass('on');
		}
		, error: function(data, status, err) {
			$('#view_area').empty();
			$('#view_box').removeClass('on');
		}
		, complete: function() { 
			// Complete
		}
	});
	return false;
}
<?php if ($is_checkbox) { ?>
$("#checkall").click(function(){
	$(this).toggleClass("on");
});
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = "./board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
<?php } ?>
</script>
<!-- } 게시판 목록 끝 -->
