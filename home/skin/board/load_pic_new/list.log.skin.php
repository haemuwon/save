<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$update_href = $delete_href = '';

// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] == $list_item['mb_id'])) || $is_admin) {

	$update_href = './write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$list_item['wr_id'].'&amp;page='.$page.$qstr;
	if(!$list_item['wr_log'] || $is_admin) { 
		set_session('ss_delete_token', $token = uniqid(time()));
		$delete_href ='./delete.php?bo_table='.$bo_table.'&amp;wr_id='.$list_item['wr_id'].'&amp;token='.$token.'&amp;page='.$page.urldecode($qstr);
	}
}
else if (!$list_item['mb_id']) { // 회원이 쓴 글이 아니라면
	$update_href = './password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$list_item['wr_id'].'&amp;page='.$page.$qstr;
	$delete_href = './password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$list_item['wr_id'].'&amp;page='.$page.$qstr;
}

// 즐겨찾기 (스크랩) 여부 체크
//$is_favorite = sql_fetch("select count(*) as cnt from {$g5['scrap_table']} where mb_id = '{$member[mb_id]}' and wr_id = '{$list_item['wr_id']}' and bo_table = '{$bo_table}'");
//$is_favorite = $is_favorite[cnt] > 0 ? true : false;

if($list_item['wr_type'] == 'UPLOAD') { 
	// Upload 형태로 로그를 등록 하였을 때
	$thumb = get_mmb_image($bo_table, $list_item['wr_id']);
	$image_url = '<img src="'.$thumb['src'].'" onclick="window.open(this.src)" ';
	$image_width = $thumb['width'];
	$image_height = $thumb['height'];
} else if($list_item['wr_type'] == 'URL') {
	// URL 형태로 로그를 등록 하였을 때
	$image_url = '<img src="'.$list_item['wr_url'].'"
onclick="window.open(this.src)" />';
	$image_width = $list_item['wr_width'];
	$image_height = $list_item['wr_height'];
}else if($list_item['wr_type'] == 'VIDEO') {
	// VIDEO 형태로 로그를 등록 하였을 때
	$image_url = $list_item['wr_video'];
	$image_width = $list_item['wr_width'];
	$image_height = $list_item['wr_height'];
}else if($list_item['wr_type'] == 'TEXT') {
	// TEXT 형태로 로그를 등록 하였을 때
	if ($list_item['wr_height']>0) $height=$list_item['wr_height']."px";
	else $height="100%";
	if ($list_item['wr_back'] == '6') {
	$text_u="<u>";
	$text_ue="</u>"; }
	else {
		$text_u="";
		$text_ue="";
	}
	$image_url = "<div style='height:".$height."'>$text_u".conv_content($list_item['wr_text'],0)."$text_ue</div>";
	$image_width = $list_item['wr_width'] ? $list_item['wr_width'] : 500;
	$image_height = $list_item['wr_height'];
}

$log_class = '';
$blind_class ='';
$h_class = '';
$msg="";
$pic_class= '';
$gray='';
$back_class='';

// 멤버공개 데이터일 시
$is_viewer = true;
$data_width = 300;
$no_member_class = '';    
if(strstr($list_item['wr_option'],"secret") ) {
	if( !$list_item['mb_id'] && get_session("ss_secret_{$bo_table}_{$list_item['wr_num']}") ||  $list_item['mb_id'] && $list_item['mb_id']==$member['mb_id'] || $is_admin )
		$is_viewer = true;
	else {
	$is_viewer = false;
	$no_member_class= 'empty secret'; 
	}
} else if ($list_item['wr_protect']!=''){
	if( get_session("ss_secret_{$bo_table}_{$list_item['wr_num']}") ||  $list_item['mb_id'] && $list_item['mb_id']==$member['mb_id'] || $is_admin )
		$is_viewer = true;
	else {
	$is_viewer = false;
	$no_member_class= 'empty protect'; 
	}
}else if($list_item['wr_secret'] == '1') {
	if($board['bo_read_level'] < $member['mb_level'] && $is_member)
		$is_viewer = true; 
	else {
	$is_viewer = false;
	$no_member_class = ' empty ';
	}
}  else {
	$is_viewer=true;
	$tb_width=1000;
	if($board['bo_table_width']>100)
		$tb_width=$board['bo_table_width'];
	if($board['bo_image_width']>0 && $image_width>$board['bo_image_width'] || $image_width>$tb_width) {
		if($list_item['wr_type']!='VIDEO' && $list_item['wr_type']!='TEXT' )
			$msg=""; 
		} //@200602
	if($board['bo_image_width']>0 && $image_width>$board['bo_image_width'] && $list_item['wr_wide']!='1'){
		$data_width=$board['bo_image_width'];
		}
	else if($image_width<300)
		$data_width=300;
	else $data_width = $image_width;
}

if($is_viewer) { 

	// 접기 여부 설정
	if(($board['bo_gallery_height'] && $image_height > $board['bo_gallery_height']) || $list_item['wr_plip'] == '1') {  
			$log_class .= "ui-slide"; 
	}
	// 블라인드 여부 설정
	if($list_item['wr_adult'] == '1') { 
		$blind_class = "ui-blind";
	}
	// 리플 아래로 내리기 여부 설정
	if($list_item['wr_wide'] == '1') { 
		$h_class = "ui-wrap";
	}
	//흰 외곽선 넣기 여부 설정
	if($list_item['wr_effect'] == '1') { 
		$pic_class = "whiteline";
	}
	//그림자 넣기 여부 설정
	if($list_item['wr_effect'] == '2') { 
		$pic_class = "pic_shadow";
	}
	//블러 넣기 여부 설정
	if($list_item['wr_effect'] == '3') { 
		$pic_class = "blur2";
	}
	//실루엣 처리 여부 설정
	if($list_item['wr_effect'] == '4') { 
		$pic_class = "blackshadow";
	}
	//무지개 외곽선 여부 설정
	if($list_item['wr_effect'] == '5') { 
		$pic_class = "rainbowline";
	}
	//마젠타 외곽선 여부 설정
	if($list_item['wr_effect'] == '6') { 
		$pic_class = "pinkline";
	}
	if($list_item['wr_effect'] == '7') { 
		$pic_class = "blush";
	}
	//마우스 오버 시 그림 변화
	if($list_item['wr_effect'] == '3') {
		}
	else if($list_item['wr_effect'] == '4') {
	}
	else if($list_item['wr_effect'] == '5') {
	}
	else {
	$gray="gray";
	}
	//바탕화면
	if($list_item['wr_back'] == '1') { 
		$back_class = "nightstars";
	}
	if($list_item['wr_back'] == '2') { 
		$back_class = "backgr";
	}	
	if($list_item['wr_back'] == '3') { 
		$back_class = "backgr2";
	}	
	if($list_item['wr_back'] == '4') { 
		$back_class = "backgr3";
	}	
	if($list_item['wr_back'] == '5') { 
		$back_class = "backgr4";
	}
	if($list_item['wr_back'] == '7') { 
		$back_class = "backpic";
	}
	if($list_item['wr_effect'] == '7') { 
		$back_class = "backblush";
	}
} 

// 알람 내역이 있을 경우, 확인으로체크
//sql_query("update {$g5['call_table']} set bc_check = 1 where re_mb_id = '{$member[mb_id]}' and bo_table ='{$bo_table}' and wr_id = '{$list_item['wr_id']}'");
?>

<div class="item <?=$h_class?>" id="log_<?=$list_item['wr_id']?>">
	<div class="item-inner">
	<!--  로그 이미지 출력 부분 -->
		<div class="ui-pic <?=$no_member_class?>" data-width="100%" data-image="<?=$image_width?>">

			<!-- 로그 상단 영역 -->
			<div class="pic-header">
				<p class="no">
					
					<? // 로그 넘버링 출력 ?>
					<span class="hologram"><?=($list_item['wr_num'] * -1)?>번째 로그</span>
					<? if($list_item['ca_name']){ 
						// 카테고리 출력
					?><span data-category="<?=$list_item['ca_name']?>" class="ico-category">| <?=$list_item['ca_name']?></span>
					<? } ?>
					<? if($list_item['wr_adult']) {
						// 19금 필터링 마크
					?>
					<span style="color:#d3393d;">■</span>
					<? } ?>
				</p>
					<?if(strstr($list_item['wr_option'],"secret")){?>
					&nbsp;&nbsp;<span class="co-secret highlight5">#secret</span>
					<?}else if ($list_item['wr_protect']!=''){?>
					&nbsp;&nbsp;<span class="co-secret highlight5">#protect</span>
					<?}else if($list_item['wr_secret'] == '1' ){?>
					&nbsp;&nbsp;<span class="co-member highlight5">#member only</span>
					<?}//@200602 ?>

				<? if($is_viewer) { 
					// 보기 권한이 존재 할 경우 (멤버의 경우)
					// -- 버튼 영역 출력
				
	if ($delete_href)		{ ?><a href="<?php echo $delete_href ?>" class="del holoback" onclick="del(this.href); return false;">삭제</a><?	} ?>
												<!--<a href="?bo_table=<?=$bo_table?>&log=<?=$list_item['wr_num'] * -1?>&single=Y" target="_blank" class="new">로그링크</a> -->
					<? if ($update_href)	{ ?><a href="<?php echo $update_href ?>" class="mod holoback">수정</a><? } ?>
				<? } ?>
			</div>

			<!-- // 로그 상단 영역 -->

			<!-- 로그 이미지 -->
			<div class="pic-data <?=$log_class?> <?=$back_class?>"  style=" <?if($list_item['wr_back'] == '7') {?>background:url(<?=$thumb['src']?>) 50% center / 200%, white; background-blend-mode:overlay;<?} else if($list_item['wr_effect'] == '7') {?>background:url(<?=$thumb['src']?>) 50% center, white;<?}else {}?>">
			<? if(!$is_viewer) { 
				// 비공개 이미지
			?>
				<div>
					<?if($no_member_class=='empty secret' && $list_item['mb_id']==''){?>
					<a href="./password.php?w=s&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					<img src="<?=$board_skin_url?>/img/img_lock.png" />
					</a>
					<?}else if ($no_member_class=='empty protect'){?> 
					<a href="./password.php?w=p&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					<img src="<?=$board_skin_url?>/img/img_lock.png" />
					</a>
					<?}else{?>
					<img src="<?=$board_skin_url?>/img/img_lock.png" style="cursor:default;" /> 
					<? } ?>
				</div>	
			<?} else { ?>

				
					<div class="img-data <?=!$member['mb_adult'] ? $blind_class : ""?> <?if($list_item['wr_type']=='TEXT') { if($list_item['wr_height']=='0') echo "theme-box2"; else echo "theme-box2 scroll";}?> " data-height="<?=$image_height?>">
						<? if($image_url) { ?><?=$image_url?> 
<?if($list_item['wr_type'] == 'UPLOAD') { ?>
class="<?=$pic_class?> <?=$gray?>">
<? }
 else {?>
<?} ?>
						<?=$msg ? help($msg) : "";?>
					</div>
					<?	if($log_class) { 
						// 접기 기능 (펼치기)
					?>
					<a href="#" class="ui-open-log ui-btn etc">OPEN</a>
					<? } ?>
					<?	if($blind_class) { 
						// 블라인드 (19금 필터링)
					?>
					<a href="#" class="ui-remove-blind"><span>해당 로그는 필터 된 로그 입니다.<br />확인을 원하실 경우 클릭해주세요.</span></a>
					<? } ?>
				<? } ?>

			<? } ?>
			
			</div>
			<!-- // 로그 상단 영역 -->
			<div class="pic-footer">
		<div class="hologram"><i class="material-icons mini-icon">chat_bubble_outline</i><i class="material-icons mini-icon">favorite_border</i></div>
	<div class="footer-memo">
<?
		if($html==2){ 
				echo autolink(emote_ev($list_item['wr_tag']), $bo_table, $stx);
			}else{
				$list_item['wr_tag'] = autolink($list_item['wr_tag'], $bo_table, $stx);
				 // 자동 링크 및 해시태그, 로그 링크 등 컨트롤 함수
				echo $list_item['wr_tag'];
			}?>
		</div></div>
		</div>
		
	<!--  // 로그 이미지 출력 부분 -->
	
	<!--  로그 코멘트 출력 부분 -->
		<div class="ui-comment"> 
			<div class="item-comment-box">
				<? include($board_skin_path."/view_comment.php");?>
			</div>
			
			<a onclick="this.nextSibling.style.display=(this.nextSibling.style.display=='none')?'block':'none';" href="javascript:void(0)">
 <p align="center"><span class="highlight3 font2 combox">+ comment</span> </p>
</a><div style="display:none;">
			<div class="item-comment-form-box">
				<? include($board_skin_path."/write_comment.php");?>
			</div> 
		</div>
	<!-- // 로그 코멘트 출력 부분 -->
	</div>
</div>
</div>
