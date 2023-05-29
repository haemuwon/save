<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$option = '';
$option_hidden = '';
if ($is_notice || $is_html || $is_secret || $is_mail) {
	$option = '';
	if ($is_notice && !$no_id) {
		 $option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
	}

	if ($is_html) {
		if ($is_dhtml_editor) {
			$option_hidden .= '<input type="hidden" value="html1" name="html">';
		} else {
			//$option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">html</label>';
		}
	}

	if ($is_secret) {
		if ($is_secret==1) {
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
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
$clap_o=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date, '%Y-%m-%d')='".G5_TIME_YMD."' and cl_ip='{$_SERVER['REMOTE_ADDR']}'");
?>
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
	<input type="hidden" name="wr_subject" value="박수">
	<input type="text" name="wr_dum" value="" style="display:none;">
	<?= $option_hidden ?>
	
	<div class="ui-write-box">
		<textarea id="content" name="wr_content" rows=6 itemname="내용" required><?=$content?></textarea>
		<button type="submit" id="btn_submit" class="ui-btn point" accesskey='s'>메세지<br>남기기</button>
	<div class="ui-control">
		<?php echo $option ?>&nbsp;&nbsp;
	<? if(!$is_member){ ?>
		<input type="hidden" maxlength="20" name="wr_name" id="wr_name" placeholder="NAME" itemname="이름" required value="익명" />
		<input type="hidden" maxlength="20" id="wr_password" name="wr_password" placeholder="PASSWORD" itemname="패스워드" value="<?=time();?>" <?=$password_required?> />
	<? } ?>
	<?if($is_admin && $w=='u') {?>
		<?for($k=0;$k<$board['bo_upload_count'];$k++){?>
		<dl class="files">
			<dt>
				<?php if($file[$k]['file']) { ?>
				<a href="<?=G5_DATA_URL."/file/".$bo_table."/".$file[$k]['file']?>" target="_blank">
				<img src="<?=G5_DATA_URL."/file/".$bo_table."/".$file[$k]['file']?>"></a>
				<?}?>
			</dt>
			<dd>
			<input type="file" name="bf_file[]" title="파일첨부 <?php echo $k+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input full">

			<input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$k]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="frm_file frm_input" size="50">

			<?php if($file[$k]['file']) { ?>
			<input type="checkbox" id="bf_file_del<?php echo $k ?>" name="bf_file_del[<?php echo $k;  ?>]" value="1"> <label for="bf_file_del<?php echo $k ?>"><?php echo $file[$k]['source'].'('.$file[$k]['size'].')';  ?> 파일 삭제</label>
			<?php } ?>
			</dd>
		</dl>
		<?}?>
	<?}?>
	</div>
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
function fwrite_submit(f)
{	
	<?if(!$is_admin){?>
		let clap_max=10;
		<? if($board['bo_1']!=''){?>
			clap_max=parseInt('<?=$board['bo_1']?>');
		<?}?>
		let clap_t=parseInt('<?=$clap_o['sum']?>');
		if(clap_max>0 && clap_t>=clap_max){
			alert("박수는 하루에 "+clap_max+"번 까지 칠 수 있습니다.");
			return false;
		}		
	<?}?>
	if(f.wr_dum.value!=''){
	alert("스팸방지");
	return false;
	}else{
	if(f.w!=u)
	alert("메시지 감사합니다!");
	return true;
	}
}
</script>
