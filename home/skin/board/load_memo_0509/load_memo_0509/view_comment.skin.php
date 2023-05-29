<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

for ($index=0; $index<count($comment); $index++) {

	$log_comment = $comment[$index];
	$comment_id = $log_comment['wr_id'];

	$content = $log_comment['content'];
	$content = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $content);
 
	if($log_comment['wr_id'] != $log_comment['wr_id'] && ($log_comment['is_reply'] || $log_comment['is_edit'] || $log_comment['is_del'])) {
		// 답변, 수정, 삭제가 가능할 경우
		// 또한, 본문의 id와 코멘트의 id가 다를 경우 (같을 경우엔 로그의 상단에 있는 컨트롤을 통해 액션 수행이 가능하다)
		$query_string = str_replace("&", "&amp;", $_SERVER['QUERY_STRING']);
		if($w == 'cu') {
			$sql = " select wr_id, wr_content from $write_table where wr_id = '$indexd' and wr_is_comment = '1' ";
			$cmt = sql_fetch($sql);
			$c_wr_content = $cmt['wr_content'];
		}

		$c_reply_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w_'.$list_item['wr_id'];
		$c_edit_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w_'.$list_item['wr_id'];
	}

	// 캐릭터 정보 출력
	$is_comment_owner = false;
	$comment_owner_front = "";		// 자기 로그 접두문자
	$comment_owner_behind = "";		// 자기 로그 접미문자
	$pic_class='';
	$gray='';
	$log_icon_path = G5_DATA_PATH.'/member/'.substr($log_comment['mb_id'],0,2).'/'.$log_comment['mb_id'].'.gif';
	$log_icon_url  = G5_DATA_URL.'/member/'.substr($log_comment['mb_id'],0,2).'/'.$log_comment['mb_id'].'.gif';

	if(!$log_comment['wr_noname']) { 
		if(is_file(G5_DATA_PATH."/site/ico_admin") && $config['cf_admin'] == $log_comment['mb_id']) {
			// 관리자 아이콘이 존재하고, 관리자와 작성자가 동일 할 경우
			// 관리자 아이콘을 출력한다.
			$log_comment['ch_name'] = "<img src='".G5_DATA_URL."/site/ico_admin' alt='관리자' />";
		} else {
			if(is_file($log_icon_path) && $log_comment['mb_id']) {
				// 멤버 아이콘이 존재하고... 하... 십알... 파이팅
				// 작성자와 멤버가 동일할 경우...는 어케 설정해야됨? 사람살려
				$log_comment['ch_name'] = "<img src='".G5_DATA_URL."/member/".substr($log_comment['mb_id'],0,2)."/".$log_comment['mb_id'].".gif' />";
			}
			else {
				$log_comment['ch_name'] = "";
			} 
		}}
	

		// 오너 정보 출력
		if($log_comment['mb_id']) {
			$log_comment['name'] = $log_comment['wr_name'];

		} else { 
			$log_comment['name'] = $log_comment['wr_name'];
		}

		if(!$list_item['wr_noname'] && $list_item['mb_id'] == $log_comment['mb_id']) { 
			$is_comment_owner = true;
			$comment_owner_front = $owner_front;
			$comment_owner_behind = $owner_behind;
		} else {
		$is_comment_owner = false;

	}   
	$cmt_depth = strlen($log_comment['wr_comment_reply']) * 3;

	$has_child=sql_fetch("select wr_id from {$write_table} where wr_parent='{$list_item['wr_id']}' and wr_comment='{$log_comment['wr_comment']}' and wr_comment_reply!='' order by wr_comment_reply desc limit 1");
?>
<?
	//흰 외곽선 넣기 여부 설정
	if($log_comment['wr_effect'] == '1') { 
		$pic_class = "whiteline";
	}
	//그림자 넣기 여부 설정
	if($log_comment['wr_effect'] == '2') { 
		$pic_class = "pic_shadow";
	}
	//블러 넣기 여부 설정
	if($log_comment['wr_effect'] == '3') { 
		$pic_class = "blur2";
	}
	//실루엣 처리 여부 설정
	if($log_comment['wr_effect'] == '4') { 
		$pic_class = "blackshadow";
	}
	//무지개 외곽선 여부 설정
	if($log_comment['wr_effect'] == '5') { 
		$pic_class = "rainbowline";
	}
	//마젠타 외곽선 여부 설정
	if($log_comment['wr_effect'] == '6') { 
		$pic_class = "pinkline";
	}
	//마우스 오버 시 그림 변화
	if($log_comment['wr_effect'] == '3') {
		}
	elseif($log_comment['wr_effect'] == '4') {
	}
	elseif($log_comment['wr_effect'] == '5') {
	}
		else {
		$gray="gray";
			}
?>


<div class="<?=$cmt_depth>0 ? "item-reply ":"";?><?=($has_child['wr_id']&&($has_child['wr_id']!=$log_comment['wr_id'])&&$cmt_depth==0) ? "parent " : "";?><?=$has_child['wr_id']==$log_comment['wr_id'] ? "last ": "";?>item-comment" id="c_<?php echo $comment_id ?>">
	<div class="co-header">
		<? // 로그 작성자와 코멘트 작성자가 동일할 경우, class='owner' 를 추가한다. ?>
		<? if(!$log_comment['wr_noname']) {  
		?>
		<p2 <?=$is_comment_owner ? ' class="owner"' : ''?>>
			<?=$comment_owner_front?>
			<?=$log_comment['ch_name'].$log_comment['name']?>
			<?=$comment_owner_behind?>
		</p>
		<? } else { ?>
		<p>익명의 누군가</p>
		<? } ?>
	<span class="date">
<?=date('m.d ✷ H:i', strtotime($log_comment['wr_datetime']))?>
		</span>
	</div>

	<div class="co-content">
		<?if($log_comment['wr_1']){?>
			<p><span class="co-memo highlight3">[ <?=$log_comment['wr_1']?> ]</span></p>
		<?}?>
		<?if(strstr($log_comment['wr_option'],"secret")){?>
			<span class="co-secret highlight5 ui-btn">비밀글</span>
		<?}else if ($log_comment['wr_secret']==1){?>
			<span class="co-member highlight5 ui-btn">로그인 전용</span>
		<?} 
			if ($log_comment['wr_reply_more']==1){?>
			<a href="#" class="co-more highlight2 ui-btn"><span>펼치기</span></a>
			<?} 
		?>
		<div class="original_comment_area<?=$log_comment['wr_reply_more'] ? " re_more":"";?>">
			<?  
				// 주사위를 굴린 정보가 있을 경우
				if($log_comment['wr_dice1']) {
			?>
			<span class="dice"> 
				<img src="<?=$board_skin_url?>/img/d<?=$log_comment['wr_dice1']?>.png" />
				<img src="<?=$board_skin_url?>/img/d<?=$log_comment['wr_dice2']?>.png" /> 
			</span>
			<?}?>   

			<?	if($log_comment['wr_link1']) {
					// 로그 등록 시 입력한 외부 링크 정보
			?>
				<span class="link-box">
					<? if($log_comment['wr_link1']) { ?>
						<a href="<?=$log_comment['wr_link1']?>" target="_blank" ><i class="fas fa-link"></i> LINK</a>
					<? } ?>
					<? if($log_comment['wr_link2']) { ?>
						<a href="<?=$log_comment['wr_link2']?>" target="_blank" ><i class="fas fa-link"></i> LINK</a>
					<? } ?>
				</span>
			<? } 
			// 코멘트 출력 부분
			if($log_comment['wr_type']=="UPLOAD" && $log_comment['wr_is_comment']!=0 && $log_comment['c_view']==true){
				$thumb = get_list_thumbnail($bo_table, $log_comment['wr_id'], $log_comment['wr_width'], $log_comment['wr_height'] , true, true);?>
				<p><img src="<?=$thumb['src']?>"  style="border-radius:15px;" onclick="window.open(this.src)" style="cursor:pointer;" class="<?=$pic_class?> <?=$gray?>"></p>
			<?}
			
			if($html==2){ 
				echo autolink(emote_ev($log_comment['content']), $bo_table, $stx);
			}else{
				$log_comment['content'] = autolink($log_comment['content'], $bo_table, $stx);
				 // 자동 링크 및 해시태그, 로그 링크 등 컨트롤 함수
				$log_comment['content'] = emote_ev($log_comment['content']); // 이모티콘 출력 함수
				echo $log_comment['content'];
			}?>
		</div>
		<div id="edit_<? echo $comment_id ?>" class="bo_vc_w"></div><!-- 수정 -->
		<div id="reply_<? echo $comment_id ?>" class="bo_vc_w"></div><!-- 답변 -->
		<? if($log_comment['is_edit']) { ?>
		<div class="modify_area" id="save_comment_<?php echo $comment_id ?>"> 
			<input type="hidden" id="save_co_subject_<?=$comment_id?>" value="<?=$log_comment['wr_subject']?>"> 
			<input type="text" id="save_co_memo_<?=$comment_id?>" value="<?=$log_comment['wr_1']?>" class="full" placeholder="memo">
			<textarea id="save_co_comment_<?php echo $comment_id ?>"><?php echo get_text($log_comment['content1'], 0) ?></textarea>
			<input type="hidden" id="save_wr_type_<?=$comment_id?>" value="<?=$log_comment['wr_type']?>">
			<p class="txt-right" id="save_co_html_<?php echo $comment_id ?>"> 
			<input type="checkbox" id="save_html_<?php echo $comment_id ?>" name="html" value="html2" class="html" <?if(strstr($log_comment['wr_option'],'html2')) echo "checked";?> />
			<input type="checkbox" id="save_secret_<?php echo $comment_id ?>" name="secret" value="secret" class="secret" <?if(strstr($log_comment['wr_option'],'secret')) echo "checked";?> />
			<input type="checkbox" id="save_reply_more_<?php echo $comment_id ?>" name="wr_reply_more" value="1" class="re-more" <?if($log_comment['wr_reply_more']==1) echo "checked";?> />
			<select name="wr_effect" id="save_effect_<?php echo $comment_id ?>" value="1" <?if($log_comment['wr_effect'] == 1) echo "1";?>/>
			</select>
			<input type="hidden" id="save_dice_<?=$comment_id?>" name="dice" value="<?=$log_comment['wr_dice1']?>">
			</p>
		</div>
		<? } ?>
	</div>

	<div class="co-footer">
		<?php if ($log_comment['is_del'])  { ?><a href="<?php echo $log_comment['del_link'];  ?>" onclick="return comment_delete();" class="del ui-btn">삭제</a><?php } ?>
		<?php if ($log_comment['is_edit']) { ?><a href="<?php echo $c_edit_href;  ?>" onclick="comment_box('<?=$list_item['wr_id']?>','<?php echo $comment_id ?>', 'cu'); return false;" class="mod ui-btn">수정</a><?php } ?>
		<? if ($log_comment['is_reply'] && $log_comment['wr_is_comment']!=0) { ?><a href="<? echo $c_reply_href;  ?>" onclick="comment_box('<?=$list_item['wr_id']?>','<? echo $comment_id ?>', 'c'); return false;" class="re ui-btn">답변</a><? } ?>
	</div>
</div>
<? } ?>