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
	// $image_url = '<img src="'.$thumb['src'].'" onclick="window.open(this.src)" />';
	$image_url = '<a class="log-'.$list_item['wr_id'].'-image-link" href="'.$thumb['src'].'" data-lightbox="'.$list_item['wr_id'].'">'
				.'<img class="log-'.$list_item['wr_id'].'-image" src="'.$thumb['src'].'" />'
				.'</a>';
} else if($list_item['wr_type'] == 'URL') {
	// URL 형태로 로그를 등록 하였을 때
	// $image_url = '<img src="'.$list_item['wr_url'].'" onclick="window.open(this.src)" />';
	$image_url = '<a class="log-'.$list_item['wr_id'].'-image-link" href="'.$list_item['wr_url'].'" data-lightbox="'.$list_item['wr_id'].'">'
				.'<img class="log-'.$list_item['wr_id'].'-image" src="'.$list_item['wr_url'].'" />'
				.'</a>';
}else if($list_item['wr_type'] == 'VIDEO') {
	// VIDEO 형태로 로그를 등록 하였을 때
	$image_url = $list_item['wr_video'];
}else if($list_item['wr_type'] == 'TEXT') {
	// TEXT 형태로 로그를 등록 하였을 때
	if ($list_item['wr_height']>0) $height=$list_item['wr_height']."px";
	else $height="100%";
	$image_url = "<div style='height:".$height."'>".conv_content($list_item['wr_text'],0)."</div>";
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

// 리플 수 확인 : 모든 리플
$sql = " select * from $write_table where wr_parent = '{$list_item['wr_id']}' and wr_content != '' and wr_is_comment = 1 order by wr_is_comment, wr_comment, wr_comment_reply ";
// 리플 수 확인: 1차 리플만
// $sql = " select * from $write_table where wr_parent = '{$list_item['wr_id']}' and wr_content != '' and wr_is_comment = 1 and wr_comment_reply = '' order by wr_is_comment, wr_comment, wr_comment_reply "
$result = sql_query($sql);
$c_cnt = mysqli_num_rows($result);

// 알람 내역이 있을 경우, 확인으로체크
//sql_query("update {$g5['call_table']} set bc_check = 1 where re_mb_id = '{$member['mb_id']}' and bo_table ='{$bo_table}' and wr_id = '{$list_item['wr_id']}'");
?>
<div class="grid-item">
<div class="item" id="log_<?=$list_item['wr_id']?>">
	<div class="item-inner">
		<!--  로그 이미지 출력 부분 -->
		<div class="ui-pic <?=$no_member_class?>">

			<!-- 로그 상단 영역 -->
			<div class="pic-header">
			<? if($is_viewer==$is_viewer){ ?>
				<a href="#" onclick="$(this).parents('.item').addClass('back').removeClass('front');return false;">
					<span class="mr-bookmark front"><?if($c_cnt) echo $c_cnt?></span>
				</a>
			<? } ?> 
			<?php if ($is_checkbox) { ?>
                <label for="chk_wr_id_<?php echo $i ?>" class="chk-id">
                <input type="checkbox" name="chk_wr_id[]" value="<?=$list_item['wr_id'] ?>" id="chk_wr_id_<?=$i?>" class="chk_id"></label>
                <?php } ?>
				<p class="no">
					<? // 로그 넘버링 출력 ?>
					No. <?=($list_item['wr_num'] * -1)?>

					<? // 카테고리 출력
					if($list_item['ca_name']){ ?>
					<span data-category="<?=$list_item['ca_name']?>" class="ico-category">
						<?=$list_item['ca_name']?>
					</span>
					<? } ?>
				</p>
					<? // 19금 필터링 마크 
					if($is_viewer && $list_item['wr_adult']) { ?>
						<span class="co-blind mr-highlight2 on">B</span>
					<? } ?>
					<?if(strstr($list_item['wr_option'],"secret")){?>
						&nbsp;&nbsp;<span class="co-secret mr-highlight">S</span>
					<?}else if ($list_item['wr_protect']!=''){?>
						&nbsp;&nbsp;<span class="co-secret mr-highlight">P</span>
					<?}else if($list_item['wr_secret'] == '1' ){?>
						&nbsp;&nbsp;<span class="co-member mr-highlight">M</span>
					<?}//@200602 ?>

				<? if($is_viewer) { 
					// 보기 권한이 존재 할 경우 (멤버의 경우)
					// -- 버튼 영역 출력
					?><!--<a href="?bo_table=<?=$bo_table?>&log=<?=$list_item['wr_num'] * -1?>&single=Y" target="_blank" class="new">로그링크</a> -->
					<? if ($update_href)	{ ?><a href="<?php echo $update_href ?>" class="mod">수정</a><? } ?>
					<?if ($delete_href)		{ ?><a href="<?php echo $delete_href ?>" class="del" onclick="del(this.href); return false;">삭제</a><?	} ?>
				<? } ?>
			</div>
			<!-- // 로그 상단 영역 -->

			<!-- 로그 이미지 -->
			<div class="pic-data">
			<? if(!$is_viewer) { 
				// 비공개 이미지
			?>
				<div>
					<?if($no_member_class=='empty secret' && $list_item['mb_id']==''){?>
					<a href="./password.php?w=s&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					<!-- <img src="<?=$board_skin_url?>/img/img_lock.png" /> -->
					<div class="imglock-box-mr">
						<div class="imglock-mr">
							숨겨진 게시글입니다.
						</div>
					</div>
					</a>
					<?}else if ($no_member_class=='empty protect'){?> 
					<a href="./password.php?w=p&amp;bo_table=<?=$bo_table?>&amp;wr_id=<?=$list_item['wr_id'].$qstr?>" class="s_cmt">
					<!-- <img src="<?=$board_skin_url?>/img/img_lock.png" /> -->
					<div class="imglock-box-mr">
						<div class="imglock-mr">
							숨겨진 게시글입니다.
						</div>
					</div>
					</a>
					<?}else{?>
					<!-- <img src="<?=$board_skin_url?>/img/img_lock.png" style="cursor:default;" />  -->
					<div class="imglock-box-mr">
						<div class="imglock-mr">
							숨겨진 게시글입니다.
						</div>
					</div>
					<? } ?>
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
				<?=date('Y-m-d H:i:s', strtotime($list_item['wr_datetime']))?>
			</span>
		</div>
		<?if($list_item['wr_content']!=''||$list_item['wr_1']||$list_item['wr_2']){?>
			
		<!-- 작성자 이름을 보이게 하려면 아랫줄의 style="display:none;" 을 지운다. -->
		<div class="co-header" style="display:none;">
			<?
			$is_comment_owner = false;
			$comment_owner_front = "";		// 자기 로그 접두문자
			$comment_owner_behind = "";		// 자기 로그 접미문자
			if(!$list_item['wr_noname'] && $list_item['mb_id'] == $list_item['mb_id']) { 
				$is_comment_owner = true;
				$comment_owner_front = $owner_front;
				$comment_owner_behind = $owner_behind;
			} else {
			$is_comment_owner = false;
			}
			// 로그 작성자와 코멘트 작성자가 동일할 경우, class='owner' 를 추가한다. ?>
			<? if(!$list_item['wr_noname']) {  
			?>
			<p <?=$is_comment_owner ? ' class="owner"' : ''?>>
				<?=$comment_owner_front?>
				<strong><?=$list_item['ch_name'].$list_item['name']?></strong>
				<?=$comment_owner_behind?>
			</p>
			<? } else { ?>
			<p>익명의 누군가</p>
			<? } ?>
		</div>
		<div class="co-content">
			<!-- 제목&부제 -->
			<?if($list_item['wr_2']){?>
				<p class="mr-titlebox">
					<span class="co-memo mr-title"><?=$list_item['wr_2']?></span>
					<span class="co-memo mr-subtitle"><?=$list_item['wr_3']?></span>
				</p>
			<?}?>

			<!-- 메모 -->
			<?if($list_item['wr_1']){?>
				<p><span class="co-memo mr-highlight"><?=$list_item['wr_1']?></span></p>
			<?}?>

			<!-- 공개범위 -->
			<?if($is_viewer){?>
				<?if(strstr($list_item['wr_option'],"secret")){?>
					<span class="co-secret mr-highlight4">#비밀글</span>&nbsp;
				<?}else if ($list_item['wr_protect']!=''){?>
					<span class="co-secret mr-highlight4">#보호글</span>&nbsp;
				<?}else if($list_item['wr_secret'] == '1' ){?>
					<span class="co-member mr-highlight4">#멤버공개</span>&nbsp;
				<?} 
			}?>

			<div class="original_comment_area<?=$list_item['wr_reply_more'] ? " re_more":"";?>">
				<? // 공개범위 내
				if($is_viewer) { ?>
					<? // 주사위를 굴린 정보가 있을 경우
					if($list_item['wr_dice1']) { ?>
					<span class="dice"> 
						<img src="<?=$board_skin_url?>/img/d<?=$list_item['wr_dice1']?>.png" />
						<img src="<?=$board_skin_url?>/img/d<?=$list_item['wr_dice2']?>.png" /> 
					</span>
					<?} ?>

					<? // 로그 등록 시 입력한 외부 링크 정보
					if($list_item['wr_link1']) { ?>
					<span class="link-box">
						<? if($list_item['wr_link1']) { ?>
							<a href="<?=$list_item['wr_link1']?>" target="_blank" ><i class="fas fa-link"></i> LINK</a>
						<? } ?>
						<? if($list_item['wr_link2']) { ?>
							<a href="<?=$list_item['wr_link2']?>" target="_blank" ><i class="fas fa-link"></i> LINK</a>
						<? } ?>
					</span>
					<? } 
					// 코멘트 출력 부분	.
					$html = 0;
					if (strstr($list_item['wr_option'], 'html1')) 		$html = 1;
					else if (strstr($list_item['wr_option'], 'html2')) 	$html = 2;

					if($html){ 
						echo autolink(emote_ev($list_item['wr_content']), $bo_table, $stx);
					}else{
						$list_item['wr_content']=conv_content($list_item['wr_content'], $html);
						$list_item['wr_content'] = autolink($list_item['wr_content'], $bo_table, $stx); // 자동 링크 및 해시태그, 로그 링크 등 컨트롤 함수
						$list_item['wr_content'] = emote_ev($list_item['wr_content']); // 이모티콘 출력 함수
						echo $list_item['wr_content'];
					}?>
				<? }else { ?>
					<span class="mr-highlight4">숨겨진 게시글입니다.</span>
				<? } ?>
			</div>
		</div>
		<?}?>
		</div>

		<!--  로그 코멘트 (뒷면) 부분 -->
		<? if($is_viewer==$is_viewer){ ?>
		<div class="ui-comment co-comment">
			<a href="#" onclick="$(this).parents('.item').removeClass('back').addClass('front');return false;">
				<span class="mr-bookmark back"></span>
			</a>
			<h3>No. <?=($list_item['wr_num'] * -1)?></h3>
			<hr class="line">
			<div class="item-comment-box">
				<? include($board_skin_path."/view_comment.php");?>
			</div>
			<div class="item-comment-form-box">
				<? include($board_skin_path."/write_comment.php");?>
			</div>
		</div>
		<? } ?>
		<!-- // 로그 코멘트 부분 -->

	</div>
</div>
</div>