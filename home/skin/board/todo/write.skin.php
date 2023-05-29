<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0-rc.1/themes/smoothness/jquery-ui.css">',99);
$option = '';
$option_hidden = '';
if ($is_notice || $is_html || $is_secret || $is_mail) {
	$option = '';
	if ($is_notice) {
		// $option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
	}

	if ($is_html) {
		if ($is_dhtml_editor) {
			$option_hidden .= '<input type="hidden" value="html1" name="html">';
		} else {
			//$option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">html</label>';
		}
	}

	if ($is_secret) {
		if ($is_admin || $is_secret==1) {
			$option .= "\n".'<label for="secret" style="white-space:nowrap;"><input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'비밀글</label>';
		} else {
			$option_hidden .= '<input type="hidden" name="secret" value="secret">';
		}
	}

	//if ($is_mail) {
	//	$option .= "\n".'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'<label for="mail">답변메일받기</label>';
	//}
}

echo $option_hidden;

if($board['bo_table_width']==0) $width="100%";

$offset=$board['bo_2']? " ".$board['bo_2']." hours" : "";
$today=date('Ymd',strtotime("today".$offset));
if(!$frdate) $frdate=$today;

?>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js" ></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" ></script>
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
	<input type="hidden" name="frdate" value="<?=$frdate?>">
	<input type="hidden" name="wr_subject" value="<?=$board['bo_subject']?>">
	<input type="hidden" name="wr_1" id="wr_1" class="date" value="<?=$write['wr_1'] ? $write['wr_1'] : $frdate?>">
	<input type="hidden" name="wr_6" value="<?=$write['wr_6']?>">
	<input type="hidden" name="wr_9" value="<?=$write['wr_9']?>">

	<?= $option_hidden ?> 
	<div class="ui-write-box<?=$w=='u'? " update":"";?>">
		<a href="#" class="write_open ui-btn point">+</a>
		<div class="write-cont">
			<div class="action-type color-select">
				<?php $j=$board['bo_1'] ? $board['bo_1']:4;//색상 바리에이션
				for ($k=1 ; $k<=$j ; $k++) {
					$cl=false;
					if(!$write['wr_3'] && $k==1 || $write['wr_3'] == "color_".$k) $cl=true; ?> 
					<input type="radio" name="wr_3" id="color_<?=$k?>" class="colors ck-input" value="color_<?=$k?>"<?php if($cl) echo " checked"; ?> required><label for="color_<?=$k?>" class="ck-label color_<?=$k?><?php if($cl) echo " point"; ?>">
					<i class="ico">&nbsp;</i></label>
				<?php } ?>  
			</div>
			<div class="action-type type-select">
				<p id="wr_type_1_cont">
					<input type="text" name="wr_2" id="wr_2" class="date" value="<?=$write['wr_2']?>" placeholder="마감" size="25">
				</p>
				<p id="wr_type_2_cont">
					<? $days_arr=['월','화','수','목','금','토','일'];
					for($k=1;$k<=7;$k++){
						$l=$k;
						if($k==7) $l='0';
						$chk="";
						$str=str_replace('0','7',$write['wr_4']);
						if(strstr($str,(string)($k))) $chk="check";
						?>
					<input type="checkbox" id="wr_ck_<?=$l?>" class="repeat_ck ck-input" name="wr_ck[]" value="<?=$l?>" <?=$chk ? "checked":"";?>><label for="wr_ck_<?=$l?>" class="alt ck-label ui-btn<?=$chk ? " point":"";?>"> <?=$days_arr[$k-1]?></label>
					<?}?>
				</p>
			</div>
			<div class="content-box">
				<input type="text" name="wr_content" id="content" class="frm-input full" required value="<?=$content?>" placeholder="내용" maxlength="255">
				<div class="options">
					<p class="txt-left">
					<?php if ($option) { ?>
						&nbsp;<?php echo $option ?>
					<?php } ?>
					</p>
					<p class="txt-right">
					<?if($w=='u'){?><a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>" class="ui-btn etc">뒤로</a><?}?>
					<button type="submit" id="btn_submit" class="ui-btn point" accesskey='s'>입력</button></p>
				</div>
			</div>
			
		</div>
	</div> 
</form>

<script>
let frdate="<?=$frdate?>";
$(".date").datepicker({
	 changeMonth: true, 
	 changeYear: true, 
	 dateFormat: "yymmdd", 
	 showButtonPanel: true, 
	 yearRange: "c-99:c+99",
	 minDate: frdate
	});
$(".ck-input").change(function(){
	if($(this).attr('type')=='radio'){
		$(this).siblings('.ck-label').removeClass('point');
		$(this).next('.ck-label').addClass('point');
	}else{
		if($(this).is(":checked")){
			$(this).next('.ck-label').addClass('point');
		}else{
			$(this).next('.ck-label').removeClass('point');
		}
	}
});
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

function fwrite_submit(f)
{
	return true;
}
</script>
