<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($is_admin) set_session("ss_delete_token", $token = uniqid(time()));
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$width=$board['bo_width'] ? $board['bo_width'] : 100;
if($width<=100) $width=$width."%";
else $width=$width."px";


$clap_max=10;
if($board['bo_1']!='') $clap_max=$board['bo_1'];
$clap_t=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date, '%Y-%m-%d')='".G5_TIME_YMD."' and cl_ip='{$_SERVER['REMOTE_ADDR']}'");
$cl_cnt=$clap_t['sum'];

?>

<div id="page_board_content" style="max-width:<?=$width?>;margin:0 auto;">

	<? if($admin_href){?><p class="txt-right"><a href="<?=$admin_href?>" class="ui-btn admin" target="_blank">관리자</a></p><hr class="padding"><?}?>

	<?
$no_id='';

$get_notice=sql_fetch("select bo_notice from {$g5['board_table']} where bo_table='{$bo_table}'");
if($get_notice['bo_notice']!=''){
	$notice_id=explode(",",$get_notice['bo_notice']);
	$no_id=$notice_id[0]; 
		
	$file=get_file($bo_table,$no_id);
	$img_list = array();
	$file_content = array(); // 파일 설명을 저장할 배열 추가
	$cnt=0;
	for ($k=0;$k<$board['bo_upload_count'];$k++){
		if($file[$k]['file']){
			$img_list[$cnt]=G5_DATA_URL."/file/".$bo_table."/".$file[$k]['file'];
			$file_content[$cnt] = $file[$k]['bf_content']; // 이미지 파일의 파일설명을 저장
			$cnt++;
		}
	}

	if(count($img_list)>0 && !$board['bo_3']){
		$random_index = rand(0, count($img_list) - 1);
		?>
		<div class="rand_img txt-center"> 
			<img src="<?=$img_list[$random_index]?>" style="max-height: 350px; background-size:cover;">
		</div>
		<div class="txt-sub"><?=$file_content[$random_index]?></div> <!-- 이미지 파일의 파일설명을 출력 -->
	<?}?>
<?}?>
	<br>
	<!-- 상단 공지 부분 -->
	<? if($board['bo_content_head']) { ?>
		<div class="board-notice">
			<?=stripslashes($board['bo_content_head']);?>
		</div>
		<hr class="padding" />
	<? } ?>
	<hr class="padding" />
	<!-- 버튼 링크 -->
	<?if($is_admin || $no_id){?>
	<?if($no_id){ 
		if(!$is_admin)
		include_once($board_skin_path.'/update_hit.php');
	?>
	<div class="clap_box txt-center ">
		<form name="clap" id="clap" action="<?=$board_skin_url?>/update_hit.php" method="post" enctype="multipart/form-data" onsubmit="return clap_submit(this)" autocomplete="off">
			<input type="hidden" name="bo_table" value="<?=$bo_table?>">
			<input type="hidden" name="clap_max" value="<?=$clap_max?>">
			<input type="hidden" name="cl_cnt" value="<?=$cl_cnt?>">
			<input type="hidden" name="return_url" value="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>">
			<button type="submit" class="ui-btn point clap" >박수!</button>
		</form>
	</div>
	<?}?>
	<hr class="padding" /> 
	<?if (!$no_id && $is_admin){?>
		<p class="txt-center">* 통계 확인 및 웹박수 랜덤이미지 등록을 위해 아래 공지 체크후 메시지를 작성해주세요.<br>* 랜덤 이미지는 글 등록 후 수정(M)을 눌러 이미지를 업로드 해주시면 됩니다.<br>
		* 공지글로 작성한 내용은 노출되지 않습니다.</p>
		<hr class="padding">
	<? } ?>	
	<? if ($write_href) { ?>
	<div class="ui-write-area">
		<? include ($board_skin_path."/write.php"); ?>
	</div>
	<hr class="padding">
	<? } ?> 
	<div class="ui-qna-list">
		<ul>
		<? 
		$lists = array();
		$cnt = 0;
		for ($i=0; $i<count($list); $i++) {
				if(!$list[$i]['is_notice'] && !$is_admin && $list[$i]['wr_comment']<1) continue;
				$lists[$cnt]=$list[$i];
				$cnt++;
			} 
		for ($ii=0; $ii < count($lists); $ii++) {
			
			include "$board_skin_path/inc.list_main.php"; 
			$lists[$ii]['datetime']=date('Y/m/d (H:i:s)', strtotime($lists[$ii]['wr_datetime']));

			$is_open = false;

			if(get_cookie('read_'.$lists[$ii]['wr_id']) == $lists[$ii]['wr_password']) { 
				$is_open = true;
			}

			$lists[$ii]['content'] = conv_content($lists[$ii]['wr_content'], 0, 'wr_content');
			$lists[$ii]['content'] = search_font($stx, $lists[$ii]['content']);
		?>
				<li>
					<div  class="theme-box question">
					<form name="fboardlist" method="post" action="<?=$board_skin_url?>/password.php" style="margin:0">
						<input type="hidden" name="bo_table" value="<?=$bo_table?>">
						<input type="hidden" name="sfl"      value="<?=$sfl?>">
						<input type="hidden" name="stx"      value="<?=$stx?>">
						<input type="hidden" name="spt"      value="<?=$spt?>">
						<input type="hidden" name="page"     value="<?=$page?>">
						<input type="hidden" name="wr_idx"     value="<?=$lists[$ii]['wr_id']?>">
						<input type="hidden" name="sw"       value="">
						
							<? if($lists[$ii]['is_notice']) { ?>
								
							<?	$clap_total=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']}");
								$clap_today=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d')='".G5_TIME_YMD."' and cl_val=''");	
							?>
							<p id="stat_total"><em>오늘: <?=sprintf("%01d",$clap_today['sum'])?></em> / <em>전체: <?=sprintf("%01d",$clap_total['sum'])?></em></p>
							<?if($is_admin){?>
							<p class="notice">
								<strong>
									<? if(($member['mb_id'] && ($member['mb_id'] == $lists[$ii]['mb_id'])) || $is_admin) { ?>
										<a href="<?=$delete_href?>">D</a>
										<a href="<?=$update_href?>">M</a>
									<? }?>
									
								</strong> 
							</p>

							<?
							include_once($board_skin_path.'/inc.stat.php');
							if($lists[$ii]['wr_file']>0){?>
							<a href="#" onclick="$(this).next().slideToggle();return false;" class="ui-btn small">▶ 랜덤이미지 확인</a>
							<p style="display:none;" class="txt-center">
								<?
								for ($k=0;$k<$board['bo_upload_count'];$k++){
									if($file[$k]['file']){
									?>
									<img src="<?=G5_DATA_URL."/file/".$bo_table."/".$file[$k]['file']?>">
								<?}?>
								<?}?>
							</p>
							<?}else{ if(!$board['bo_3']){?>
								<p class="txt-center">* 수정(M)을 눌러 웹박수 랜덤이미지를 등록 해주세요.</p>
							<?} }?>
							<?}?>
							<? } else { ?>
							<p> 
								<span class="date">
									<?=$lists[$ii]['datetime']?>
								</span> 
								<?if($is_admin){?><?=$lists[$ii]['wr_ip']?><?}?>
								<strong>
									<? if($is_admin) { ?>
									<a href="<?=$delete_href?>">D</a>
									<a href="javascript:comment_wri('comment_write', '<?=$lists[$ii]['wr_id']?>');">R</a> 
									<? } ?>
								</strong> 
							</p>
							<div class="qna-content <?=!$is_admin ? " guest" : "";?>">
								<?if(!$board['bo_2'] || $is_admin){
									if(strstr($lists[$ii]['wr_option'], 'secret')) { ?>
									<span class="txt-point">[SECRET]</span><br />
								<? } ?>
								<?if((!strstr($lists[$ii]['wr_option'], 'secret')) || $is_admin) { ?>
									<?= $lists[$ii]['content'] ?>
								<? } } ?> 
							</div>
							<? } ?>
					</form>
					<?  
							$wr_id = $lists[$ii]['wr_id'];
							include ("$board_skin_path/view_comment.php"); 
					?>
					</div>
				</li>
		<?	}
		// 필터 
		?> 
		</ul>

		<!-- 페이지 -->

		<div class="ui-page">
			<?
			$add="";
			if(!$is_admin)
			$add="and wr_comment=1 ";
			$total=sql_fetch("select count(distinct wr_id) as cnt from {$write_table} where wr_id=wr_parent {$add}");
			$total_count=$total['cnt'];
			$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
			$from_record = ($page - 1) * $page_rows; // 시작 열을 구함
			$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './board.php?bo_table='.$bo_table.$qstr.'&amp;page=');
			echo $write_pages;
			?>
		</div>

	</div> 
	<?}?>
</div>
<script>
//if ("<?=$sca?>") document.fcategory.sca.value = "<?=$sca?>";
if ("<?=$stx?>") {
	document.fsearch.sfl.value = "<?=$sfl?>";
	document.fsearch.sop.value = "<?=$sop?>";
} 

// HTML 로 넘어온 <img ... > 태그의 폭이 테이블폭보다 크다면 테이블폭을 적용한다.
function resize_image()
{
	var target = document.getElementsByName('target_resize_image[]');
	var image_width = parseInt("<?=$board['bo_image_width']?>");
	var image_height = 0;

	for(i=0; i<target.length; i++) { 
		// 원래 사이즈를 저장해 놓는다
		target[i].tmp_width  = target[i].width;
		target[i].tmp_height = target[i].height;
		// 이미지 폭이 테이블 폭보다 크다면 테이블폭에 맞춘다
		if(target[i].width > image_width) {
			image_height = parseFloat(target[i].width / target[i].height)
			target[i].width = image_width;
			target[i].height = parseInt(image_width / image_height);
		}
	}
}
window.onload = resize_image;

const bg = $(".question.theme-box").css("background-color");
$(".qna-content.guest").css({"color":bg,"opacity":1,"background-color":bg});

let clap_max=10;
<? if($board['bo_1']!=''){?>
	clap_max=parseInt('<?=$board['bo_1']?>');
<?}?>
let clap_t=parseInt('<?=$clap_t['sum']?>');

<?if(!$is_admin){?>
$(document).keydown(function(event){
	if( (event.ctrlKey == true && (event.keyCode == 78 || event.keyCode == 82)) || (event.keyCode == 116)) {
		if(clap_max==0 || (clap_max>0 && clap_t<clap_max)){
			alert("꺅! 박수 감사합니다!");
		}else{
			event.keyCode = 0;
			event.cancelBubble = true;
			event.returnValue = false;
			alert("박수는 하루에 "+clap_max+"번 까지 칠 수 있습니다.");
			return false;
		}
	}
});
<?}?>

function clap_submit(f)
{
	if(clap_max>0 && clap_t>=clap_max){
		alert("박수는 하루에 "+clap_max+"번 까지 칠 수 있습니다.");
		return false;
	}
	else return true;
}

function comment_wri(name, id) { 
	$('.modify_area').hide();
	var layer = document.getElementById(name+id); 
	layer.style.display = (layer.style.display == "none")? "block" : "none"; 
}
function comment_delete(url)
{
    if (confirm("이 코멘트를 삭제하시겠습니까?")) location.href = url;
}

</script>	
<? if ($is_checkbox) { ?>
<script>
function all_checked(sw)
{
	var f = document.fboardlist;

	for (var i=0; i<f.length; i++) {
		if (f.elements[i].name == "chk_wr_id[]")
			f.elements[i].checked = sw;
	}
}

function check_confirm(str)
{
	var f = document.fboardlist;
	var chk_count = 0;

	for (var i=0; i<f.length; i++) {
		if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
			chk_count++;
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
	var f = document.fboardlist;

	str = "삭제";
	if (!check_confirm(str))
		return;

	if (!confirm("선택한 게시물을 정말 "+str+" 하시겠습니까?\n\n한번 "+str+"한 자료는 복구할 수 없습니다"))
		return;

	f.action = "./delete_all.php";
	f.submit();
}

// 선택한 게시물 복사 및 이동
function select_copy(sw)
{
	var f = document.fboardlist;

	if (sw == "copy")
		str = "복사";
	else
		str = "이동";

	if (!check_confirm(str))
		return;

	var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

	f.sw.value = sw;
	f.target = "move";
	f.action = "./move.php";
	f.submit();
}

function comment_box(co_id, wr_id) { 
	$('.modify_area').hide();
	$('#c_'+co_id).find('.modify_area').show();
	$('#c_'+co_id).find('.qna-comment-content').hide();

	$('#save_co_comment_'+co_id).focus();

	var modify_form = document.getElementById('frm_modify_comment');
	modify_form.wr_id.value = wr_id;
	modify_form.comment_id.value = co_id;
}

function modify_commnet(co_id) { 
	var modify_form = document.getElementById('frm_modify_comment');
	var wr_content = $('#save_co_comment_'+co_id).val(); 
	var wr_option = '';  
	modify_form.wr_content.value = wr_content;
	modify_form.wr_option.value = wr_option;
	$('#frm_modify_comment').submit();
}

</script>

<form name="modify_comment" id="frm_modify_comment"  action="./write_comment_update.php" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
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
<? } ?>