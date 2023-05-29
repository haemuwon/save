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
//$is_favorite = sql_fetch("select count(*) as cnt from {$g5['scrap_table']} where mb_id = '{$member['mb_id']}' and wr_id = '{$list_item['wr_id']}' and bo_table = '{$bo_table}'");
//$is_favorite = $is_favorite['cnt'] > 0 ? true : false;

if($list_item['wr_type'] == 'UPLOAD') { 
	// Upload 형태로 로그를 등록 하였을 때
	$thumb = get_mmb_image($bo_table, $list_item['wr_id']);
	$image_url = '<img src="'.$thumb['src'].'" onclick="window.open(this.src)" />';
} else if($list_item['wr_type'] == 'URL') {
	// URL 형태로 로그를 등록 하였을 때
	$image_url = '<blockquote class="twitter-tweet"><p lang="ko" dir="ltr"><a href="'.$list_item['wr_url'].'?ref_src=twsrc%5Etfw"></a></blockquote>';
}else if($list_item['wr_type'] == 'VIDEO') {
	// VIDEO 형태로 로그를 등록 하였을 때
	$image_url = '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$list_item['wr_video'].'"title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}else if($list_item['wr_type'] == 'TEXT') {
	// TEXT 형태로 로그를 등록 하였을 때
	if ($list_item['wr_height']>0) $height=$list_item['wr_height']."px";
	else $height="100%";
	$image_url = "<div style='height:".$height."; font-family:".$list_item['wr_2']."; font-size:".$list_item['wr_3']."px;'>".conv_content($list_item['wr_text'],0)."</div>";
	$image_height = $list_item['wr_height'];
}

$blind_class ='';
$msg="";

// 멤버공개 데이터일 시
$is_viewer = true;
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
	if($board['bo_read_level'] <= $member['mb_level'] && $is_member) // @211118
		$is_viewer = true; 
	else {
	$is_viewer = false;
	$no_member_class = ' empty ';
	}
}  else {
	$is_viewer=true;
}

if($is_viewer) {  
	// 블라인드 여부 설정
	if($list_item['wr_adult'] == '1') { 
		$blind_class = "ui-blind";
	} 
} 

// 알람 내역이 있을 경우, 확인으로체크
//sql_query("update {$g5['call_table']} set bc_check = 1 where re_mb_id = '{$member['mb_id']}' and bo_table ='{$bo_table}' and wr_id = '{$list_item['wr_id']}'");
?>
<div class="grid-item">
<div class="item" id="log_<?=$list_item['wr_id']?>" >

	<div class="item-inner" style="width:<?if($board['bo_table_width']>100){?><?=round($board['bo_table_width']/$bo_gallery_cols) - 20;} else{?> <?=round(1000 * ($board['bo_table_width']/100)/$bo_gallery_cols) - 20;} ?>px;">
	
	<!--  로그 이미지 출력 부분 -->
		<div class="ui-pic <?=$no_member_class?>">

			<!-- 로그 상단 영역 -->
			<div class="pic-header">
			<?php if ($is_checkbox) { ?>
                <label for="chk_wr_id_<?php echo $i ?>" class="chk-id">
                <input type="checkbox" name="chk_wr_id[]" value="<?=$list_item['wr_id'] ?>" id="chk_wr_id_<?=$i?>" class="chk_id"></label>
                <?php } ?>

				<p class="no highlight"><? // 메모 출력 ?><?if($list_item['wr_1']){?><?=$list_item['wr_1']?>	<?}?> 

					<? if($list_item['ca_name']){ 
						// 카테고리 출력
					?>
					<span data-category="<?=$list_item['ca_name']?>" class="ico-category">
						: <?=$list_item['ca_name']?>
					</span>
					<? } ?>
					<? if($list_item['wr_adult']) {
						// 19금 필터링 마크
					?>
					<span style="color:#d3393d;">■</span>
					<? } ?>
					<?if(strstr($list_item['wr_option'],"secret")){?>
					&nbsp;<i class="fas fa-lock" alt="비밀글" title="비밀글"></i>
					<?}else if ($list_item['wr_protect']!=''){?>
					&nbsp;<i class="fas fa-key" alt="보호글" title="보호글"></i>
					<?}else if($list_item['wr_secret'] == '1' ){?>
					&nbsp;<i class="fas fa-user-check" alt="멤버 공개" title="멤버 공개"></i>
					<?}//@200602 ?>
				</p>


				<? if($is_viewer) { 
					// 보기 권한이 존재 할 경우 (멤버의 경우)
					// -- 버튼 영역 출력
					?><!--<a href="?bo_table=<?=$bo_table?>&log=<?=$list_item['wr_num'] * -1?>&single=Y" target="_blank" class="new">로그링크</a> -->
					<? if ($update_href)	{ ?><a href="<?php echo $update_href ?>" class="mod">수정</a><? } ?>
					<?if ($delete_href)		{ ?><a href="<?php echo $delete_href ?>" class="del" onclick="del(this.href); return false;">삭제</a><?	} ?>
				<? } ?>
				<?if($member['mb_level']>=$board['bo_comment_level']){?>
					<a href="#" onclick="$(this).parents('.item').addClass('back').removeClass('front');return false;" class="open-comment"><i class="fas fa-comment-alt"></i></a>
				<?}?> 
			</div>
			<!-- // 로그 상단 영역 -->

			<!-- 로그 이미지 -->
			<div class="pic-data" style="min-width:<?=$bo_gallery_cols>=4 ? "240px" : "300px"?>;">
			<? if(!$is_viewer) { 
				// 비공개 이미지
			?>
				<div style="padding-top: 50%; transform: translateY(-50%);">
				<span class="highlight-nopin">
					<?if($no_member_class=='empty secret' && $list_item['mb_id']==''){?>
					<a href="./password.php?w=s&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					권한이 없습니다.
					</a>
					<?}else if ($no_member_class=='empty protect'){?> 
					<a href="./password.php?w=p&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					패스워드가 필요합니다.(CLICK)
					</a>
					<?}else{?>
					권한이 없습니다.
					<? } ?>
				</span>
				</div>	
			<?} else { ?>

				<? if($image_url) { ?>
					<div class="img-data <?=$blind_class?> <?if($list_item['wr_type']=='TEXT') { if($list_item['wr_height']=='0') echo "theme-box"; else echo "theme-box scroll";}?>">
						<?=$image_url?>
					</div>
					<?	if($blind_class) { 
						// 블라인드 (19금 필터링)
					?>
					<a href="#" class="ui-remove-blind"><span>해당 로그는 필터 된 로그 입니다.<br />확인을 원하실 경우 클릭해주세요.</span></a>
					<? } ?>
				<? } ?>

			<? } ?>
			</div>
			<!-- // 로그 상단 영역 -->

		</div>
	<!--  // 로그 이미지 출력 부분 -->

	<!-- 로그 본문 출력 부분 -->
	<div class="ui-comment pic-comment">
	<div class="co-footer txt-right">
		<span class="date">
			<?=date('H:i / m.d', strtotime($list_item['wr_datetime']))?>
		</span>
	</div>

	</div>

	<!--  로그 코멘트 부분 -->
		<div class="ui-comment co-comment">
		<a href="#" class="close-comment" onclick="$(this).parents('.item').removeClass('back').addClass('front');return false;"><i class="fas fa-times"></i></a>
			<h3 class="highlight"><?if($list_item['wr_1']){?>	<?=$list_item['wr_1']?>	<?}?> </h3>
			<hr class="line">
			<div class="item-comment-box">
				<? include($board_skin_path."/view_comment.php");?>
			</div>
			<div class="item-comment-form-box">
				<? include($board_skin_path."/write_comment.php");?>
			</div>
		</div>
	<!-- // 로그 코멘트 부분 -->

	</div>
</div>
</div>