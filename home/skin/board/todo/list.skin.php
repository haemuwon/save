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
$offset=$board['bo_2']? " ".$board['bo_2']." hours" : "";
$today=date('Ymd',strtotime("today".$offset));
if(!$frdate) $frdate=$today;
?>

<div class="wrap_content">
<div id="page_board_content" style="max-width:<?php echo $width; ?>;margin: 0 auto;">

<!-- 상단 공지 부분 -->
<? if($board['bo_content_head']) { ?>
	<div class="board-notice theme-box">
		<?=stripslashes($board['bo_content_head']);?>
	</div>
	<hr class="padding" />
<? } ?>

	
	<nav class="frdates">
		<ul>
			<?
			$adds=[' -1 months',' -7 days',' -3 days',' -2 days',' -1 days','',' +1 days',' +2 days',' +3 days',' +7 days',' +1 months'];
			$yoil=['일','월','화','수','목','금','토'];
			for ($i=0;$i<11;$i++){
				$full_date=date('Y/m/d',strtotime($frdate.$adds[$i]));
				$date=substr($full_date,5);
				$day=date('w',strtotime($frdate.$adds[$i]));
				$day=str_replace($day,$yoil[$day],$day);
				$class="";
				if($i==0){
					$date="《";
					$day="한달 전";
					$class.="alt";
				}
				if($i==1){
					$date="〈";
					$day="일주일 전";
					$class.="alt";
				}
				if($i==9){
					$date="〉";
					$day="일주일 후";
					$class.="alt";
				}
				if($i==10){
					$date="》";
					$day="한달 후";
					$class.="alt";
				}
				if($i==0|| $i==10 || $i==2 ||$i==8) $class.=" day-r";
				?>
				<li class="<?=$class?>"><a href="./board.php?bo_table=<?=$bo_table?>&frdate=<?=date("Ymd",strtotime($frdate.$adds[$i]))?>" class="ui-btn<?=date('Ymd',strtotime($full_date))==$today ? " today":""; ?><?=$i==5 ? " point":""?>" title="<?=($i>1 && $i<9) ? $full_date." " :""?><?=$day;?>"><?=$date?></a></li>
			<?}?>
		</ul>
	</nav>
	<!-- 버튼 링크 -->
	<div class="fx-control">
		<p class="txt-left"><a href="./board.php?bo_table=<?=$bo_table?>&frdate=<?=$today?>" class="ui-btn point small">오늘</a></p>
		<p class="txt-right"><? if($admin_href){?><a href="<?=$admin_href?>" class="ui-btn admin small" target="_blank">관리자</a><?}?></p>
	</div>
	
	<div class="ui-memo-list">
		<? if ($is_member && $write_href) { ?>
		<div class="ui-top theme-box point">
			<div class="ui-write-area">
			<? include ($board_skin_path."/write.php"); ?>
			</div>
		</div>
		<? } ?>
		<ul id="sortable">
	<? 
		$this_day=date("w",strtotime($frdate));
		$search_query="and wr_1<='{$frdate}' and ( (wr_2='' and wr_4='') or ((wr_4=''or wr_4!='' and wr_4 like '%{$this_day}%') and ( wr_2='' or wr_2!='' and '{$frdate}' between wr_1 and wr_2) ) )";
		$list_item=sql_query("select * from {$write_table} where wr_reply='' and wr_is_comment=0 {$search_query} order by wr_9*1, wr_6*1, wr_num"); 
		$count=0;
		$lists=array();
		for($k=0;$row=sql_fetch_array($list_item);$k++){
			$list[$k]=get_list($row,$board,$board_skin_url);
			if(strstr($list[$k]['wr_option'],'secret') && ($is_guest || ($lists[$k]['mb_id'] && $member['mb_id']!=$lists[$k]['mb_id']))) continue;
			if($list[$k]['wr_9'] && $frdate>$list[$k]['wr_9']) continue;
			$lists[$count]=$list[$k];
			$count++;
		}

		for ($ii=0; $ii < count($lists); $ii++) {
			include "$board_skin_path/inc.list_main.php"; 

			$is_open = false;

			if(get_cookie('read_'.$lists[$ii]['wr_id']) == $lists[$ii]['wr_password']) { 
				$is_open = true;
			}

			$lists[$ii]['content'] = conv_content($lists[$ii]['wr_content'], 0, 'wr_content');
 			$done="";
			$over="";
			if ($lists[$ii]['wr_9']) $done="done";
			if ($frdate<$today||($lists[$ii]['wr_2'] && $lists[$ii]['wr_2']<$frdate)) $over="over";
			$is_mod=false;
			if($is_admin || $lists[$ii]['mb_id'] && $member['mb_id']) $is_mod=true;
			$check=$done ? "del":"check"; 
			$cklink=$is_mod ? "checkList('".$lists[$ii]['wr_id']."','".$check."','".$frdate."');return false;":"return false"; 
	?>
				<li id="wr_<?=$lists[$ii]['wr_id']?>" class="theme-box<?=$over ? " over":""?><?=$done ? " done":""?>" data-idx="<?=$lists[$ii]['wr_id']?>">
					<form name="fboardlist" method="post" action="<?=$board_skin_url?>/password.php" style="margin:0">
						<input type="hidden" name="bo_table" value="<?=$bo_table?>">
						<input type="hidden" name="sfl"      value="<?=$sfl?>">
						<input type="hidden" name="stx"      value="<?=$stx?>">
						<input type="hidden" name="spt"      value="<?=$spt?>">
						<input type="hidden" name="page"     value="<?=$page?>">
						<input type="hidden" name="sw"       value="">
						<input type="hidden" name="wr_idx"     value="<?=$lists[$ii]['wr_id']?>">
						<input type="hidden" name="frdate" value="<?=$frdate?>">
						
						<div class="content-area">
							<i class="mng">
								<span class="btn-ctrl sortable">↕</span>
								<button id="wr_9_<?=$ii?>" class="txt-default chk_id unsortable <?=$lists[$ii]['wr_3']?>" <?=$done ? "checked":""?> <?if($over){ echo "disabled"; } else {?>onclick="<?=$cklink?>"<?}?>></button>
							</i>
							<p class="con">
							<?if(strstr($lists[$ii]['wr_option'], 'secret')) { ?>
								<span class="txt-point">::</span>
							<? } ?>
							<a href="#" onclick="<?if(!$over){ echo $cklink; }?>" class="txt-default"><?= $lists[$ii]['content'] ?></a>
							</p>
							<span class="tdate"><? 
								$repeat_dates="";
								if ($lists[$ii]['wr_4']){
									if($lists[$ii]['wr_4']=='1|2|3|4|5|6|0') $repeat_dates="매일";
									else $repeat_dates=$lists[$ii]['wr_4'];
									unset($src);
									unset($dst);
									$src[] = "/0/i";
									$dst[] = "일";
									$src[] = "/1/i";
									$dst[] = "월";
									$src[] = "/2/i";
									$dst[] = "화";
									$src[] = "/3/i";
									$dst[] = "수"; 
									$src[] = "/4/i";
									$dst[] = "목";
									$src[] = "/5/i";
									$dst[] = "금"; 
									$src[] = "/6/i";
									$dst[] = "토";
									$src[] = "/\|/i";
									$dst[] = ",";

									$repeat_dates = preg_replace($src, $dst, $repeat_dates); 
								}
								if ($repeat_dates=='매일') echo $repeat_dates;
								else if ($repeat_dates) echo '매 '.$repeat_dates;
								else if($lists[$ii]['wr_2']) echo'~'.date('m/d',strtotime($lists[$ii]['wr_2']));
								?>
							</span>
							<p class="control"><? if($update_href){?><a href="<?=$update_href?>" class="btn-ctrl unsortable">*</a><?}
							if($delete_href){?><a href="<?=($is_member) ? "":$delete_href?>" class="btn-ctrl unsortable" <?if($is_member) {?>onclick="<?=$delete_href?>return false;"<?}?>>-</a><? } ?> </p>
						</div>
					</form>
				</li>
	<?	}  
	?>
	<? if (count($lists) == 0) { echo "<li class='no-data theme-box'>내역이 없습니다.</li>"; } ?>
		</ul>
	</div> 
		<form id="listupdate" action="<?=$board_skin_url?>/todo_update.php" method="post" style="display:none;">
		<input type="hidden" name="bo_table" value="<?=$bo_table?>">
		<input type="hidden" name="write_table" value="<?=$write_table?>">
		<input type="hidden" name="frdate" value="<?=$frdate?>">
		<input type="submit" name="submit_form" value="">
		</form> 
	<?if($is_admin){?>
		<form name="fchecklist"  id="fchecklist" action="./board_list_update.php" method="post" style="display:none;">
		<input type="hidden" name="bo_table" value="<?=$bo_table?>">
		<input type="hidden" name="write_table" value="<?=$write_table?>">
		<input type="hidden" name="frdate" value="<?=$frdate?>">
		<input type="hidden" name="btn_submit" id="ck_submit" value="">
		</form>
		<div class="fx-control">
			<p class="txt-left">
				<button id="btn_modify" class="ui-btn admin small unsortable" onclick="sortList('sort');">순서변경</button> 
				<button id="btn_confirm" class="ui-btn point small sortable" onclick="flistupdate();">확인</button>
				<button id="btn_cancel" class="ui-btn etc small sortable" onclick="sortList('unsort');">취소</button> 
			</p>
			<p class="txt-right"></p>
		</div>
	<?}?>
</div>
</div>
<script>

$(".write_open").click(function(){
	$(this).next().toggleClass("on");
});

function comment_box(co_id, wr_id) { 
	$('.modify_area').hide();
	$('.comment-content').show();

	$('#c_'+co_id).find('.modify_area').show();
	$('#c_'+co_id).find('.comment-content').hide();

	$('#save_co_comment_'+co_id).focus();

	var modify_form = document.getElementById('frm_modify_comment');
	modify_form.wr_id.value = wr_id;
	modify_form.comment_id.value = co_id;
}

function mod_comment(co_id) { 
	var modify_form = document.getElementById('frm_modify_comment');
	var wr_content = $('#save_co_comment_'+co_id).val(); 
	var wr_option = '';  
	modify_form.wr_content.value = wr_content;
	modify_form.wr_option.value = wr_option;
	modify_form.wr_id.value = co_id;
	modify_form.comment_id.value = co_id;
	$('#frm_modify_comment').submit();
} 
function checkList(wr_id,sw,date) {
	var today="<?=$today?>";
	if(today<date){
		alert("미래의 할일에 체크하실 수 없습니다");
		return false;
	}
	$("#listupdate").append('<input type="hidden" value="'+wr_id+'" name="wr_id"><input type="hidden" value="'+sw+'" name="sw">');
	$("#listupdate").submit();
}
</script>

<? if ($is_checkbox) { ?>
<script>

function sortList(flag){
	if(flag=='sort'){		
		$("#sortable, .fx-control").addClass('mod');
		$( "#sortable" ).sortable({disabled:false,items:"li:not(.done)"}).disableSelection();
	}else if(flag=='unsort'){
		$("#sortable, .fx-control").removeClass('mod');	
		$( "#sortable" ).sortable({disabled:true}).sortable("cancel").enableSelection();
	}
}
function flistupdate() {
	for (i=0;i<$("#sortable li").length;i++){
		let wr_id=$("#sortable li").eq(i).data('idx');
		$("#listupdate").append('<input type="hidden" name="wr_id['+i+']" value="'+wr_id+'"><input type="hidden" name="wr_6['+wr_id+']" value="'+i+'">');
	}
	$("#listupdate").submit();
}

var count=0; 

$('.chk_id').change(function(){  
	if($(this).prop('checked')){ 
		$("#fchecklist").append('<input type="checkbox" id="ck_id_'+$(this).val()+'" name="chk_wr_id[]" class="chkd" value="'+$(this).val()+'" checked style="display:none;">');
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
			$("#fchecklist").append('<input type="checkbox" id="ck_id_'+$('.chk_id').eq(i).val()+'" class="chkd" name="chk_wr_id[]" value="'+$('.chk_id').eq(i).val()+'" checked style="display:none;">');
			count++; 
		}
	 
	}else{
		$('.chkd').remove();
		count--; 
	}
}

function check_confirm(str)
{
	var f = $('.chkd');
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
<form name="modify_comment" id="frm_modify_comment"  action="./write_comment_update.php" method="post" autocomplete="off">
	<input type="hidden" name="w" value="cu">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">

	<input type="hidden" name="comment_id" value="">
	<input type="hidden" name="wr_id" value="">
	<input type="hidden" name="wr_option" value="" >
	<textarea name="wr_content" style="display: none;"></textarea>
	<button type="submit" style="display: none;"></button>
</form>