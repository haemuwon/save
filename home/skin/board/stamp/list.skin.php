<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if($is_admin) set_session("ss_delete_token", $token = uniqid(time()));

if($is_member)  {
	$comment_token = uniqid(time());
	set_session('ss_comment_token', $comment_token);
}

$is_comment_write = false;
if($board['bo_table_width']==0) $width="100%";
?>


<div id="page_board_content" style="max-width:<?php echo $width; ?>">

	 
<!-- 상단 공지 부분 -->
<? if($board['bo_content_head']) { ?>
	<div class="board-notice theme-box">
		<?=stripslashes($board['bo_content_head']);?>
	</div>
	<hr class="padding" />
<? } ?>

	<!-- 버튼 링크 -->
	<div class="adm-box">
	<?if($write_href && $is_member){?><a href="<? echo $write_href ?>" class="ui-btn small point">추가</a><?}?>
	<? if($admin_href){?><a href="<?=$admin_href?>" class="ui-btn admin" target="_blank">관리자</a><?}?>
	</div>
	
	<div class="stamp-list"> 
	
		<ul>
	<? 
		$lists = array();
		for ($i=0; $i<count($list); $i++) { $lists[$i] = $list[$i]; } 
		
		for ($ii=0; $ii < count($lists); $ii++) { 
			include "$board_skin_path/inc.list_main.php"; 
			$lists[$ii]['datetime']=substr($lists[$ii]['wr_datetime'],0,4)."/".substr($lists[$ii]['wr_datetime'],5,2)."/".substr($lists[$ii]['wr_datetime'],8,2)." (".substr($lists[$ii]['wr_datetime'],11,8).")";

			$is_open = false;

			if(get_cookie('read_'.$lists[$ii]['wr_id']) == $lists[$ii]['wr_password']) { 
				$is_open = true;
			}

			$lists[$ii]['content'] = conv_content($lists[$ii]['wr_content'], 0, 'wr_content');
			$lists[$ii]['content'] = search_font($stx, $lists[$ii]['content']);
			$box_style=explode("||",$lists[$ii]['wr_3']);  
			$stamp_size=explode("||",$lists[$ii]['wr_2']);
			$stamps=explode("|",$lists[$ii]['wr_content']);
			$max_width=$stamp_size[0]*$lists[$ii]['wr_6'];
			if($lists[$ii]['wr_4']) $duration=explode("||",$lists[$ii]['wr_4']);
			if(strstr($lists[$ii]['wr_content'],'x')&& $duration[1] && (G5_SERVER_TIME > strtotime($duration[1]))){$complete="달성실패";}
			else if( !strstr($lists[$ii]['wr_content'],'x')){$complete="달성";}
			else $complete="";
	?>
				<li> 
					<form name="fboardlist" method="post" action="<?=$board_skin_url?>/password.php" style="margin:0">
						<input type="hidden" name="bo_table" value="<?=$bo_table?>">
						<input type="hidden" name="sfl"      value="<?=$sfl?>">
						<input type="hidden" name="stx"      value="<?=$stx?>">
						<input type="hidden" name="spt"      value="<?=$spt?>">
						<input type="hidden" name="page"     value="<?=$page?>">
						<input type="hidden" name="wr_idx"     value="<?=$lists[$ii]['wr_id']?>">
						<input type="hidden" name="sw"       value="">
						
					<div id="stamp_<?=$lists[$ii]['wr_id']?>" class="stamp-area theme-box" 
						style="<?=$box_style[0] ? "background:".$box_style[0].";" : ""?><?=$box_style[1]? "border:1px solid ".$box_style[1].";" : "";?>">
						<p class="complete">
							<?if($complete=='달성실패'){?><span class="ui-btn admin">달성실패</span><?}else if($complete=='달성'){?><span class="ui-btn point">달성</span><?}?>
						</p>
						<p class="control">
							<em>
							<?php if ($is_checkbox) { ?>
							<input type="checkbox" name="chk_wr_id[]" value="<?php echo $lists[$ii]['wr_id'] ?>" id="chk_wr_id_<?php echo $ii ?>" class="chk_id">
							<?php } ?>
							</em>
							<? if(($member['mb_id'] && ($member['mb_id'] == $lists[$ii]['mb_id'])) || $is_admin) {
								if($update_href){?><a href="<?=$update_href?>" class="ui-btn">M</a>
								<?}?><a href="<?=$delete_href?>" class="ui-btn admin">D</a><? 
							} 
							?> 
						</p>
						<h2><? if(strstr($lists[$ii]['wr_option'], 'secret')) echo ":: ";?><?=$lists[$ii]['wr_subject']?></h2>
						<? if(!$duration[0]) $duration[0]=$lists[$ii]['wr_datetime'];
								if($duration[1]){?>
							<p class="date"><?=date('Y/m/d',strtotime($duration[0]))?> ~ <?=date('Y/m/d',strtotime($duration[1]))?></p>
							<?}?>
						<hr class="line">
						 
						<div class="stamp-content"> 
						<?
							if(strstr($lists[$ii]['wr_option'], 'secret') && !$is_admin && !$is_open) {?>
							<div class="con empty">
							<?if(!$lists[$ii]['mb_id']){
						?> 
								<a href="#" class="write_open secret ui-btn">***</a><p class="pass_in"><input type="password" name="wr_password" id="wr_password_<?=$ii?>" value="" placeholder="비밀번호"/>
								<button type="submit" class="ui-btn">입력</button></p>
							
							<?}else{?>
								<p>비밀글입니다.</p>
							<?}?></div>
						<? } else { ?>
						
							<div class="con" style="max-width:<?=$max_width?>px;" data-max="<?=$max_width?>" data-min="<?=$stamp_size[0]?>">
							<?
							$last=0;
							for($k=0;$k<count($stamps);$k++){
								
								if($stamps[$k]=='x') {
									$done=false;
									if($lists[$ii]['wr_file']>0)
										$thumb=sql_fetch("select bf_file from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$lists[$ii]['wr_id']}' and bf_no=0"); 
									else $thumb=array();
									if(!empty($thumb) && $thumb['bf_file']) $thumb['src']=G5_DATA_URL."/file/".$bo_table."/".$thumb['bf_file'];
									else  $thumb['src']=$board_skin_url.'/no_stamp.png';
								}
								else { 
									$last=1;
									$done=true;
									$stamp_t=explode(",",$stamps[$k]);
									if($lists[$ii]['wr_file']>1) 
									$thumb=sql_fetch("select bf_file from {$g5['board_file_table']} where bo_table='{$bo_table}' and wr_id='{$lists[$ii]['wr_id']}' and bf_no='{$stamp_t[0]}'"); 
									else $thumb=array();
									if(!empty($thumb) && $thumb['bf_file']) $thumb['src']=G5_DATA_URL."/file/".$bo_table."/".$thumb['bf_file']; 
									else $thumb['src']=$board_skin_url.'/stamp.png';
								} 
							?><p id="s_<?=$lists[$ii]['wr_id']?>_<?=$k?>" style="width:<?=$stamp_size[0]?>px;height:<?=$stamp_size[1]?>px;">
							<span class="num txt-point"><?=$k+1?></span>
							<?if($duration[1] && G5_SERVER_TIME>strtotime($duration[1]) || ($lists[$ii]['mb_id']!=$member['mb_id'])){?>
							<img src="<?=$thumb['src']?>"><?}else{?>
							<a href="#" onclick="stamp_check('<?=$lists[$ii]['wr_id']?>','<?=$k?>','<?=$done ? 'off' : 'on'?>');return false;"><img src="<?=$thumb['src']?>"></a>
							<?}?></p><?
							}?>
							</div>
						<? } ?>  
						<div class="bo_fx">
						<?if($last==1 && $complete==''){?>
						<div class="last-date">
							<div class="reward">
							<span>마지막 스탬프: <?=date('m.d',strtotime($lists[$ii]['wr_last']));?></span>
							<hr class="line">
							</div>
						</div>
						<?}?>
						<? if($lists[$ii]['wr_5']){?>
							<div class="reward-box">
								<div class="reward">
									<span>달성보상 : <?=nl2br($lists[$ii]['wr_5'])?></span>
									<hr class="line">
								</div>
							</div>
						<?}?>
						</div>
						</div>
					</div>
					</form> 
				</li>
	<?	}  
	?>
	<? if (count($lists) == 0) { echo "<li class='no-data'>내역이 없습니다.</li>"; } ?>
		</ul> 
<?php if ($is_checkbox) { ?>  
	<div class="bo_fx txt-right"> 
	
		<form name="fchecklist"  id="fchecklist" action="./board_list_update.php" method="post">
		<input type="hidden" name="write_table" value="<?=$write_table?>">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="spt" value="<?php echo $spt ?>">
		<input type="hidden" name="sst" value="<?php echo $sst ?>">
		<input type="hidden" name="sod" value="<?php echo $sod ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">
		<input type="hidden" name="sw" value=""> 
		<input type="hidden" name="btn_submit" value="">
		</form>
		
		<?if($is_checkbox && count($lists)>0){?>
		<span class="chkall"><input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
		</span>
		<?}?>
		<input type="submit" name="btn_submit" value="선택삭제" onclick="select_delete();" class="ui-btn small admin">
		<input type="submit" name="btn_submit" value="선택복사" onclick="select_copy('copy');" class="ui-btn small admin">
		<input type="submit" name="btn_submit" value="선택이동" onclick="select_copy('move');" class="ui-btn small admin">
	</div>
	<?php } ?> 
	</div>
	<!-- 페이지 -->
	<? echo $write_pages;  ?>
</div>

<script language="JavaScript">
//if ("<?=$sca?>") document.fcategory.sca.value = "<?=$sca?>";
if ("<?=$stx?>") {
	document.fsearch.sfl.value = "<?=$sfl?>";
	document.fsearch.sop.value = "<?=$sop?>";
}
$('.stamp-content .con').each(function(e){
	var max=$(this).data('max');
	var min=$(this).data('min');
	var w=$(this).width();
	var d=Math.floor(w/min);
	var new_w=d*min;
	if(w<max) $(this).css('max-width',new_w+"px");
});
 function stamp_check(wr_id,idx,state){ 
	hlink='<?=$board_skin_url?>/stamp.php?bo_table=<?=$bo_table?>&wr_id='+wr_id+'&idx='+idx+'&state='+state;
	$.ajax({
		async: true
		, url: hlink
		, beforeSend: function() {}
		, success: function(data) {
			// Toss
			var response = data.split('--'); 
			$('#s_'+wr_id+'_'+idx).empty().append(response[0]);  
			$('#stamp_'+wr_id+' .last-date .reward').empty().append(response[1]);
		}
		, error: function(data, status, err) { 
			// error
		}
		, complete: function() { 
			$("")
			// Complete
		}
	});
	return false;
 }
</script>

<? if ($is_checkbox) { ?>
<script>

var count=0; 
$('.chk_id').change(function(){  
	if($(this).prop('checked')){ 
		$("#fchecklist").append('<input type="checkbox" id="ck_id_'+$(this).val()+'" name="chk_wr_id[]" value="'+$(this).val()+'" checked style="display:none;">');
		count++;  
	}
	if($(this).prop('checked')==false){
		$('#ck_id_'+$(this).val()).remove();
		count--; 
	}
});

function all_checked(sw) {
	var clen=$('.chk_id').length;
	$('.chk_id').prop('checked',sw); 
	if(sw==true){
		for(i=0;i<clen;i++){
			$("#fchecklist").append('<input type="checkbox" id="ck_id_'+$('.chk_id').eq(i).val()+'" class="chk_wr" name="chk_wr_id[]" value="'+$('.chk_id').eq(i).val()+'" checked style="display:none;">');
			count++; 
		}
	 
	}else{
		$('.chk_wr').remove();
		count--; 
	}
}

function check_confirm(str)
{
	var f = $('.chk_id');
	var chk_count = 0;

	for (var i=0; i<f.length; i++) {
		if (f.prop("checked")){
			chk_count++; 
		}
	}

	if (!chk_count) {
		alert(str + "할 게시물을 하나 이상 선택하세요.");
		return false;
	}
	return true;
}

// 선택한 게시물 삭제
function select_delete()
{
	var f = document.fchecklist; 

	str = "삭제";
	if (!check_confirm(str))
		return;

	if (!confirm("선택한 게시물을 정말 "+str+" 하시겠습니까?\n\n한번 "+str+"한 자료는 복구할 수 없습니다"))
		return; 
	f.btn_submit.value="선택삭제";
	f.removeAttribute("target");
	f.action = "./board_list_update.php";
	f.submit();
}

// 선택한 게시물 복사 및 이동
function select_copy(sw)
{
	var f = document.fchecklist; 

	if (sw == "copy")
		str = "복사";
	else
		str = "이동";

	if (!check_confirm(str))
		return;

	var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

	f.sw.value = sw;
	f.btn_submit.vaule="선택"+str;
	f.target = "move";
	f.action = "./move.php";
	f.submit();
}
</script>
<? } ?>