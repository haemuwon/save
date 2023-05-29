<?
if($w == '')
	$w = 'u';

?>
<section id="bo_w_c_<?=$list_item[$i]['wr_id']?>" class="mainskin--board--write--add" style="display:none;">
<div class="mainskin--edit">
			<h1>레이아웃 수정</h1>
			<!-- 게시물 작성/수정 시작 { -->
			<form name="fwriteadd" id="fwriteadd" action="./write_update.php" onsubmit="return fwriteadd_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
			<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
			<input type="hidden" name="w" value="<? echo $w ?>" id="w_<?=$list_item[$i]['wr_id'] ?>">
			<input type="hidden" name="has_comment" value="<? echo $list_item[$i]['has_comment'] ?>" id="comment_id">

			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="wr_id" id="wr_id" value="<?=$list_item[$i]['wr_id'] ?>">
			<input type="hidden" name="wr_type" value="layout">
			<input type="hidden" name="wr_1" value="<?=$list_item[$i]['wr_1'] ?>">
			<input type="hidden" name="wr_2" value="<?=$list_item[$i]['wr_2'] ?>">
			<input type="hidden" name="wr_3" value="<?=$list_item[$i]['wr_3'] ?>">
			<input type="hidden" name="wr_4" value="<?=$list_item[$i]['wr_4'] ?>">
			<input type="hidden" name="wr_5" value="<?=$list_item[$i]['wr_5'] ?>">
			<input type="hidden" name="wr_6" value="<?=$list_item[$i]['wr_6'] ?>">
			
			<input type="hidden" name="wr_subject" value="<?=$list_item[$i]['wr_subject']?>">
			

			<!-- 요소 등록 부분 -->

		※ 입력 시 주의사항: 길이 등은 단위까지 포함해 작성해야 정상적으로 출력됩니다. (ex: px, %, vw, vh...) 
		<div class="edit_1">
			<div class="edit_2">
				<div class="edit_2_name">
					가로 너비
				</div>
				<div class="edit_2_value">
				<input class="mainskin--input sml"  type="text" value="<?php echo $list_item[$i]['div_width']; ?>" id="div_width" name="div_width" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				</div>
			</div>
			<div class="edit_2">
				<div class="edit_2_name">세로 길이</div>
				<div class="edit_2_value"><input class="mainskin--input sml"  type="text" value="<?php echo $list_item[$i]['div_height']; ?>" id="div_height" name="div_height" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" ></div>
			</div>
		</div>
		<div class="edit_1_name">블럭 정렬 방향</div>
		<div class="edit_1">
			<div class="edit_1_value">
			<input type="radio" name="wr_direction" id="column" value="column" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" <?if($list_item[$i]['wr_direction'] == 'row') echo ''; else echo 'checked';?> >
		<label for="column"><i class="material-icons">view_stream</i> 세로</label>
		<input type="radio" name="wr_direction" id="row" value="row"  onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" <?if($list_item[$i]['wr_direction'] == 'row') echo 'checked'; else echo '';?>>
		<label for="row"><i class="material-icons">view_week</i> 가로</label>
			</div>
		</div>
		<div class="edit_1_name">내부 요소 정렬</div>
		<div class="edit_1">
			<div class="edit_2">
				<div class="edit_2_name">가로 정렬</div>
				<div class="edit_2_value"><select name="justify_content" id="justify_content">
			<option value="">---</option>
			<?=write_option('flex-start', $list_item[$i]['justify_content'], '좌측');?>
			<?=write_option('center', $list_item[$i]['justify_content'], '중앙');?>
			<?=write_option('flex-end', $list_item[$i]['justify_content'], '우측');?>
			</select></div>
			</div>
			<div class="edit_2">
				<div class="edit_2_name">세로 정렬</div>
				<div class="edit_2_value"><select name="align_items" id="align_items">
			<option value="">---</option>
			<?=write_option('flex-start', $list_item[$i]['align_items'], '상단');?>
			<?=write_option('center', $list_item[$i]['align_items'], '중간');?>
			<?=write_option('flex-end', $list_item[$i]['align_items'], '하단');?>
			</select></div>
			</div>
		</div>
		<div class="edit_1_name">테마</div>
		<div class="edit_2">
			<div class="edit_2_value">
				<select name="theme2" id="theme2" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
					<option value="">---</option>
						<?=write_option('main_default', $list_item[$i]['theme'], '기본');?>
						<?=write_option('main_default2', $list_item[$i]['theme'], '기본2');?>
						<?=write_option('main_empty', $list_item[$i]['theme'], '스타일 없음(투명)');?>
						<?=write_option('main_glass', $list_item[$i]['theme'], '유리 재질');?>
						<?=write_option('main_digital', $list_item[$i]['theme'], '디지털');?>
						<?=write_option('main_gothic', $list_item[$i]['theme'], '고딕');?>
						<?=write_option('main_gothic2', $list_item[$i]['theme'], '고딕2');?>
						<?=write_option('main_pola', $list_item[$i]['theme'], '폴라로이드');?>
						<option value="text">직접 입력</option>
					</select>
					<input type="text" name="theme" id="theme" class="mainskin--input" value="<?=$list_item[$i]['theme']?>"  style="<?if($list_item[$i]['theme'] != 'main_default' && $list_item[$i]['theme'] != 'main_empty' && $list_item[$i]['theme'] != 'main_digital' && $list_item[$i]['theme'] != 'main_gothic' && $list_item[$i]['theme'] != 'main_glass' && $list_item[$i]['theme'] != 'main_pola' && $list_item[$i]['theme']) echo ''; else echo 'display:none;';?>">
			</div>
		</div>
		<div class="edit_2">
			<div class="edit_2_name">효과</div>
			<div class="edit_2_value">
			<select name="theme3" id="theme3" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
					<option value="">없음</option>
						<?=write_option('effect_tilt', $list_item[$i]['theme3'], '기울기');?>
						<?=write_option('effect_sparkle', $list_item[$i]['theme3'], '반사광');?>
						<?=write_option('effect_larger', $list_item[$i]['theme3'], '확대');?>
						<?=write_option('effect_lift', $list_item[$i]['theme3'], '들림');?>
						<?=write_option('effect_colorful', $list_item[$i]['theme3'], '흑백 → 컬러');?>
						<?=write_option('effect_mono', $list_item[$i]['theme3'], '컬러 → 흑백');?>
					</select>
			</div>
		</div>
		<div class="edit_1_name">배경</div>
		<div class="edit_1">
			<div class="edit_2">
				<div class="edit_2_name">외부 링크</div> 
				<div class="edit_2_value"><input class="mainskin--input" type="text" placeholder="외부 링크" value="<?php echo $list_item[$i]['wr_url'] ?>" name="wr_url" id="wr_url" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.id, this.value);"></div>
				<input type="hidden" name="bf_file[]" title="파일첨부 <?php echo 1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file frm_input">
			</div>
			<div class="edit_2">
				<div class="edit_2_value"><input type="checkbox" name="wr_move" id="wr_move" <?if($list_item[$i]['wr_move'] == 'on') echo 'checked';?>> 배경 움직임 설정
				<div class="desc" onclick="$(this).next().toggle();">※</div><div class="desc_">
				체크하면 배경이 마우스 움직임에 따라 흔들립니다.
				</div>
			</div>
			</div>
		</div>
		<div class="edit_1">
			<div class="edit_2_name">배경 색상</div>
			<div class="edit_2_value"><input class="mainskin--input sml" type="color" name="color" id="color" value="<?=$list_item[$i]['color']?>" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.id, this.value);">
			<input class="mainskin--input sml" type="text" name="color" id="color_text" value="<?=$list_item[$i]['color']?>" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.id, this.value);"></div>
		</div>
	<div class="edit_1_name">배경 설정</div>
	<div class="edit_1">
		<div class="edit_2_name">반복 설정</div>
		<div class="edit_2_value"><select name="back1" id="back1" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
			<?=write_option('repeat', $list_item[$i]['back1'], '반복');?>
			<?=write_option('no-repeat', $list_item[$i]['back1'], '반복 없음');?>
			<?=write_option('repeat-x', $list_item[$i]['back1'], '가로 반복');?>
			<?=write_option('repeat-y', $list_item[$i]['back1'], '세로 반복');?>
		</select></div>
	</div>
	<div class="edit_2_name">배경 위치</div>
	<div class="edit_1">
		<div class="edit_2">
			<div class="edit_2_name">가로 위치</div>
			<div class="edit_2_value">
				<select name="back2_" id="back2_" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<option value="">---</option>
		<?=write_option('0%', $list_item[$i]['back2'], '좌측');?>
		<?=write_option('50%', $list_item[$i]['back2'], '중앙');?>
		<?=write_option('100%', $list_item[$i]['back2'], '우측');?>
		<option value="text">직접 입력</option>
		</select>
		<input type="text" name="back2" id="back2" class="mainskin--input sml" value="<?=$list_item[$i]['back2']?>"  style="<?if($list_item[$i]['back2'] && $list_item[$i]['back2'] != '0%' && $list_item[$i]['back2'] != '50%' && $list_item[$i]['back2'] != '100%') echo ''; else echo 'display:none;';?>">
			</div>
		</div>
		<div class="edit_2">
		<div class="edit_2_name">세로 위치</div>
			<div class="edit_2_value"><select name="back3_" id="back3_" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<option value="">---</option>
		<?=write_option('0%', $list_item[$i]['back3'], '상단');?>
		<?=write_option('50%', $list_item[$i]['back3'], '중간');?>
		<?=write_option('100%', $list_item[$i]['back3'], '하단');?>
		<option value="text" >직접 입력</option>
		</select>
		<input type="text" name="back3" id="back3" class="mainskin--input sml" value="<?=$list_item[$i]['back3']?>"  style="<?if($list_item[$i]['back3'] && $list_item[$i]['back3'] != '0%' && $list_item[$i]['back3'] != '50%' && $list_item[$i]['back3'] != '100%') echo ''; else echo 'display:none;';?>"></div>
		</div>
	</div>
	<div class="edit_1">
		<div class="edit_2">
			<div class="edit_2_name">배경 사이즈</div>
			<div class="edit_2_value"><select name="back4" id="back4" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<?=write_option('auto', $list_item[$i]['back4'], '원본 크기');?>
		<?=write_option('contain', $list_item[$i]['back4'], '전부 보이게');?>
		<?=write_option('cover', $list_item[$i]['back4'], '채우기');?>
		</select></div>
		</div>
		<div class="edit_2">
		<div class="edit_2_name">배경 이동</div>
			<div class="edit_2_value"><select name="back5" id="back5" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<?=write_option('scroll', $list_item[$i]['back5'], '스크롤');?>
		<?=write_option('fixed', $list_item[$i]['back5'], '고정');?>
		</select></div>
		</div>
	</div>
    <div class="edit_2">
			<div class="edit_2_name">바깥쪽 여백</div>
			<div class="edit_2_value">
				<select name="margin_auto" id="margin_auto" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
				<?=write_option('auto', $list_item[$i]['margin_auto'], '자동');?>
				<?=write_option('manual', $list_item[$i]['margin_auto'], '수동');?>
				</select>
				상 <input class="mainskin--input sml" type="text" value="<?=$list_item[$i]['margin_top']; ?>" id="margin_top" name="margin_top" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				하 <input class="mainskin--input sml" type="text" value="<?=$list_item[$i]['margin_bottom']; ?>" id="margin_bottom" name="margin_bottom" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				좌 <input class="mainskin--input sml" type="text" value="<?=$list_item[$i]['margin_left']; ?>" id="margin_left" name="margin_left" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				우 <input class="mainskin--input sml" type="text" value="<?=$list_item[$i]['margin_right']; ?>" id="margin_right" name="margin_right" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			</div>
		</div>
		
		<div class="edit_2">
			<div class="edit_2_name">안쪽 여백</div>
			<div class="edit_2_value">
				<select name="padding_auto" id="padding_auto" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
				<?=write_option('auto', $list_item[$i]['padding_auto'], '자동');?>
				<?=write_option('manual', $list_item[$i]['padding_auto'], '수동');?>
				</select>
				상 <input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['padding_top']; ?>" id="padding_top" name="padding_top" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				하 <input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['padding_bottom']; ?>" id="padding_bottom" name="padding_bottom" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				좌 <input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['padding_left']; ?>" id="padding_left" name="padding_left" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				우 <input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['padding_right']; ?>" id="padding_right" name="padding_right" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			</div>
		</div>
    <div class="desc" onclick="$(this).next().toggle();">※여백 수동 입력 시</div>
		<div class="desc_">
			수동으로 설정 시 상하좌우 모두 (단위 포함) 입력해야 정상 작동합니다. 
		</div>
		<div class="edit_1_name">테두리 설정</div>
		<div class="edit_1">
			<div class="edit_2">
				테두리 두께 <input class="mainskin--input" type="text" value="<?php echo $list_item[$i]['border_width']; ?>" name="border_width" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
				테두리 스타일 
		<select name="border_style" id="border_style" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<option value="">---</option>
		<?=write_option('solid', $list_item[$i]['border_style'], '실선');?>
		<?=write_option('dotted', $list_item[$i]['border_style'], '점선');?>
		<?=write_option('dashed', $list_item[$i]['border_style'], '파선');?>
		<?=write_option('double', $list_item[$i]['border_style'], '두 겹');?>
		<?=write_option('ridge', $list_item[$i]['border_style'], '테두리 돌출');?>
		<?=write_option('groove', $list_item[$i]['border_style'], '테두리 함몰');?>
		<?=write_option('outset', $list_item[$i]['border_style'], '요소 돌출');?>
		<?=write_option('inset', $list_item[$i]['border_style'], '요소 함몰');?>
		</select>
		테두리 색상 <input class="mainskin--input" type="text" value="<?php echo $list_item[$i]['border_color']; ?>" name="border_color" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
		<div class="edit_2_name">
			<div class="desc" onclick="$(this).next().toggle();">※</div>
		<div class="desc_">네 모서리의 테두리를 다르게 설정할 수도 있어요! <br> ex) 1px 3px 2px 3px 혹은 red white black pink ...</div>
		</div>
			</div>
		
		</div>
	
	<dl>
		<dt>둥글기 설정</dt>
		<dd>
			전체
			<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['border_radius_all']; ?>" name="border_radius_all" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			※전체가 설정되어 있으면 개별 적용이 작동하지 않습니다.
			</dd>
			<dd>
			↖
			<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['border_radius_top']; ?>" name="border_radius_top" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			↗
			<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['border_radius_right']; ?>" name="border_radius_right" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			↘
			<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['border_radius_bottom']; ?>" name="border_radius_bottom" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
			↙
			<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['border_radius_left']; ?>" name="border_radius_left" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" >
		</dd>
	</dl>
  <dl>
		
    <dt>
      스타일 직접 작성
    </dt>
    <dd>
    <textarea name="wr_style" id="wr_style" rows="3" size="50" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);" ><?=$list_item[$i]['wr_style']?></textarea>
    </dd>
		<div class="desc" onclick="$(this).next().toggle();">※</div>
		<div class="desc_">color: black; background: white; 식으로 입력합니다.<br>
		몇 가지 예시 <br>
		글자색 color: white; / 문자그림자 text-shadow: 2px 2px 0px black; <br>
		그라디언트 배경 background: linear-gradient(45deg, white, transparent); <br>
		원근감 있게 돌리기 transform: perspective(300px) rotate3d(0, 1, 0, 1deg);
	</div>
  </dl>
  <div class="edit_1"></div>
	<div class="edit_1_name">
		연결 관리
	</div>
  
	<?if($list_item[$i]['has_comment'] == 1){
		echo '<br>※ 내부에 블럭이 존재해 연결이 불가능합니다.';
		echo '<br>';
		} else {?> 
  ※ 연결된 요소가 존재하면 구조 추가가 불가능해집니다.

	
	
  <dl>
    <dt>타입</dt>
    <dd>
    <select name="content_type" id="content_type" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
			<option value="">연결 없음</option>
			<option value="default" <?if($list_item[$i]['content_type'] == 'default') echo 'selected';?>>직접 입력(html)</option>
      <option value="text" <?if($list_item[$i]['content_type'] == 'text') echo 'selected';?> >텍스트</option>
      <option value="board" <?if($list_item[$i]['content_type'] == 'board') echo 'selected';?>>게시판</option>
      <option value="page" <?if($list_item[$i]['content_type'] == 'page') echo 'selected';?>>페이지</option>
			<option value="slider" <?if($list_item[$i]['content_type'] == 'slider') echo 'selected';?>>슬라이더</option>
      <option value="image" <?if($list_item[$i]['content_type'] == 'image') echo 'selected';?>>이미지</option>
      <option value="clock" <?if($list_item[$i]['content_type'] == 'clock') echo 'selected';?>>시계</option>
      <option value="dday" <?if($list_item[$i]['content_type'] == 'dday') echo 'selected';?>>디데이</option>
			<option value="latest" <?if($list_item[$i]['content_type'] == 'latest') echo 'selected';?>>최신글</option>
			<option value="links" <?if($list_item[$i]['content_type'] == 'links') echo 'selected';?>>링크</option>
    </select>
    </dd>
  </dl>
  <dl id="default_edit" class=" <?if($list_item[$i]['content_type'] == 'default') echo 'active_edit';?>" >
    <dt>직접 입력</dt>
    <dd>
      <textarea name="wr_content_default" id="wr_content_default"><?=$list_item[$i]['wr_content']?></textarea>
    </dd>
  </dl>
  <dl id="text_edit" class="<?if($list_item[$i]['content_type'] == 'text') echo 'active_edit';?>" >
    <dt>텍스트 연결</dt>
    <dd>
			<textarea name="wr_content_text" id="wr_content_text"><?=$list_item[$i]['wr_content']?></textarea>
		</dd>
		<dd style="align-items: flex-start; flex-direction: column;">
			폰트 <br>
			<input class="mainskin--input" type="text" placeholder="폰트명(영문)" value="<?php echo $list_item[$i]['text1'] ?>" name="text1" id="text1" >
			스타일 <br>
			<select name="text2" id="text2" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
			<option value="">일반</option>
			<?=write_option('bold', $list_item[$i]['text2'], '두껍게');?>
			<?=write_option('italic', $list_item[$i]['text2'], '기울게');?>
			<?=write_option('bold-italic', $list_item[$i]['text2'], '두껍고 기울게');?>
		</select><br>
		스타일 직접 지정<br>
		<textarea name="text3" id="text3"><?=$list_item[$i]['text3']?></textarea>
		</dd>
  </dl>
  <dl id="board_edit" class=" <?if($list_item[$i]['content_type'] == 'board') echo 'active_edit';?>" >
    <dt>게시판 연결</dt>
    <dd>
		<select name="board1" id="board1">
			<option value="">--게시판중 선택--</option>
			<?$bbs_option=sql_query("select bo_table, bo_subject from {$g5['board_table']} where bo_subject!=''");
			if(!empty($bbs_option)){?>
			<?for ($b=0;$bbs_list=sql_fetch_array($bbs_option);$b++){
				$option=$bbs_list['bo_table'];
				?>
				<option value="<?=$option?>" <?=($list_item[$i]['board1']==$option) ? "selected":"";?>><?=$bbs_list['bo_subject']?></option> 
			<?}
			}?>
		</select>
		</dd>
  </dl>
  <dl id="page_edit" class=" <?if($list_item[$i]['content_type'] == 'page') echo 'active_edit';?>" >
    <dt>페이지 연결</dt>
    <dd>
		<select name="page1" id="page1">
			<option value="" disabled >--페이지중 선택--</option>
			<?$co_option=sql_query("select co_id, co_subject from {$g5['content_table']} where co_subject!=''");
			if(!empty($co_option)){?> 
			<?for ($c=0;$co_list=sql_fetch_array($co_option);$c++){
				$option=$co_list['co_id'];
				?>
				<option value="<?=$option?>" <?=($list_item[$i]['page1']==$option) ? "selected":"";?>><?=$co_list['co_subject']?></option>
			<?}
			}?>
		</select>
		</dd>
  </dl>
  <dl id="slider_edit" class=" <?if($list_item[$i]['content_type'] == 'slider') echo 'active_edit';?>" >
    <dt>이미지 슬라이더</dt>
    <dd>
		<select name="slider1" id="slider1">
			<option value="">--게시판중 선택--</option>
			<?$bbs_option=sql_query("select bo_table, bo_subject from {$g5['board_table']} where bo_subject!=''");
			if(!empty($bbs_option)){?>
			<?for ($b=0;$bbs_list=sql_fetch_array($bbs_option);$b++){
				$option=$bbs_list['bo_table'];
				?>
				<option value="<?=$option?>" <?=($list_item[$i]['slider1']==$option) ? "selected":"";?>><?=$bbs_list['bo_subject']?></option> 
			<?}
			}?>
		</select>
		※연결할 게시판을 선택해주세요.
		출력 수
		<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['slider2']; ?>" name="slider2" >
		※ 기본 5개
		</dd>
  </dl>
  <dl id="image_edit" class=" <?if($list_item[$i]['content_type'] == 'image') echo 'active_edit';?>" >
    <dt>단일 이미지 연결</dt>
    <dd>
		<strong>외부 링크 </strong>
		<input class="mainskin--input" type="text" placeholder="외부 링크" value="<?php echo $list_item[$i]['image1'] ?>" name="image1" id="image1" >
		</dd>
		이미지 위치
		<select name="image2_" id="image2_" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<option value="">---</option>
		<?=write_option('0%', $list_item[$i]['image2'], '좌측');?>
		<?=write_option('50%', $list_item[$i]['image2'], '중앙');?>
		<?=write_option('100%', $list_item[$i]['image2'], '우측');?>
		<option value="text">직접 입력</option>
		</select>
		<input type="text" name="image2" id="image2" class="mainskin--input sml" value="<?=$list_item[$i]['image2']?>"  style="<?if($list_item[$i]['image2'] != '0%' && $list_item[$i]['image2'] != '50%' && $list_item[$i]['image2'] != '100%'&&$list_item[$i]['image2']) echo ''; else echo 'display:none;';?>">
		<select name="image3_" id="image3_" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<option value="">---</option>
		<?=write_option('0%', $list_item[$i]['image3'], '좌측');?>
		<?=write_option('50%', $list_item[$i]['image3'], '중앙');?>
		<?=write_option('100%', $list_item[$i]['image3'], '우측');?>
		<option value="text">직접 입력</option>
		</select>
		<input type="text" name="image3" id="image3" class="mainskin--input sml" value="<?=$list_item[$i]['image3']?>"  style="<?if($list_item[$i]['image3'] != '0%' && $list_item[$i]['image3'] != '50%' && $list_item[$i]['image3'] != '100%') echo ''; else echo 'display:none;';?>">
			</dd>
			<dd>
		이미지 사이즈
		<select name="image4" id="image4" onchange="changeDisign('<?=$list_item[$i]['wr_id']?>', this.name, this.value);">
		<?=write_option('auto', $list_item[$i]['image4'], '원본 크기');?>
		<?=write_option('contain', $list_item[$i]['image4'], '전부 보이게');?>
		<?=write_option('cover', $list_item[$i]['image4'], '채우기');?>
		</select>
		<dd>

		</dd>
  </dl>
  <dl id="clock_edit" class=" <?if($list_item[$i]['content_type'] == 'clock') echo 'active_edit';?>" >

    <dt>시계 연결</dt>
		※두 블럭 이상 연결하면 오류가 발생합니다.<br>
		<div class="desc" onclick="$(this).next().toggle();">출력형태 예시</div>
		<div class="desc_">
			<strong> 날짜 </strong><br>
			<b>년도</b> year = 2023 / yy = 23 <br>
			<b>월</b> monthname = 5월 / monthnum = 05 / mnum = 5 <br>
			<b>일</b> dayname = 목요일 / daynum = 08 / dnum = 8 <br><br>
			<strong> 시간 </strong><br>
			<b>오전/오후</b> AMPM <br>
			<b>시간</b> hours = 18 / 12hours = 6 <br>
			<b>분</b> minutes <br>
			<b>초</b> seconds <br><br>
			<strong> 예 </strong><br>
			year년 monthnum월 dnum일 dayname<br>
			-> 2023년 05월 8일 목요일<br>
			12hours :: minutes :: seconds :: AMPM<br>
			-> 7 :: 24 :: 31 :: AM
		</div>
		<dd>
			시계 정렬
			<select name="clock4" id="clock4">
			<option value="">---</option>
			<?=write_option('flex-start', $list_item[$i]['clock4'], '좌측');?>
			<?=write_option('center', $list_item[$i]['clock4'], '중앙');?>
			<?=write_option('flex-end', $list_item[$i]['clock4'], '우측');?>
			</select>
		</dd>
    <dd>
		<storng>―― 1열 ――</strong><br>
		날짜 출력 형태
		<input type="text" name="clock1" id="clock1" class="mainskin--input" value="<?=$list_item[$i]['clock1']?>"><br>
		시간 출력 형태
		<input type="text" name="clock1_" id="clock1_" class="mainskin--input" value="<?=$list_item[$i]['clock1_']?>"><br>
		출력 스타일
		<select name="clock1_1" id="clock1_1">
		<option value="">---</option>
		<?=write_option('big', $list_item[$i]['clock1_1'], '크게');?>
		<?=write_option('medium', $list_item[$i]['clock1_1'], '중간');?>
		<?=write_option('small', $list_item[$i]['clock1_1'], '작게');?>
		</select><br>
		스타일 직접 지정
		<input type="text" name="clock1_2" id="clock1_2" class="mainskin--input" value="<?=$list_item[$i]['clock1_2']?>">
		</dd>
		<dd>
		<storng>―― 2열 ――</strong><br>
		날짜 출력 형태
		<input type="text" name="clock2" id="clock2" class="mainskin--input" value="<?=$list_item[$i]['clock2']?>"><br>
		시간 출력 형태
		<input type="text" name="clock2_" id="clock2_" class="mainskin--input" value="<?=$list_item[$i]['clock2_']?>"><br>
		출력 스타일
		<select name="clock2_1" id="clock2_1">
		<option value="">---</option>
		<?=write_option('big', $list_item[$i]['clock2_1'], '크게');?>
		<?=write_option('medium', $list_item[$i]['clock2_1'], '중간');?>
		<?=write_option('small', $list_item[$i]['clock2_1'], '작게');?>
		</select><br>
		스타일 직접 지정
		<input type="text" name="clock2_2" id="clock2_2" class="mainskin--input" value="<?=$list_item[$i]['clock2_2']?>">
		</dd>
		<dd>
		
		<storng>―― 3열 ――</strong><br>
		날짜 출력 형태
		<input type="text" name="clock3" id="clock3" class="mainskin--input" value="<?=$list_item[$i]['clock3']?>"><br>
		시간 출력 형태
		<input type="text" name="clock3_" id="clock3_" class="mainskin--input" value="<?=$list_item[$i]['clock3_']?>"><br>

		출력 스타일
		<select name="clock3_1" id="clock3_1">
		<option value="">---</option>
		<?=write_option('big', $list_item[$i]['clock3_1'], '크게');?>
		<?=write_option('medium', $list_item[$i]['clock3_1'], '중간');?>
		<?=write_option('small', $list_item[$i]['clock3_1'], '작게');?>
		</select><br>
		스타일 직접 지정
		<input type="text" name="clock3_2" id="clock3_2" class="mainskin--input" value="<?=$list_item[$i]['clock3_2']?>">
		</dd>
  </dl>

  <dl id="dday_edit" class=" <?if($list_item[$i]['content_type'] == 'dday') echo 'active_edit';?>" >
    <dt>디데이 리스트</dt>
		<dd>
				디데이 정렬
			<select name="dday5" id="dday5">
			<option value="">---</option>
			<?=write_option('flex-start', $list_item[$i]['dday5'], '좌측');?>
			<?=write_option('center', $list_item[$i]['dday5'], '중앙');?>
			<?=write_option('flex-end', $list_item[$i]['dday5'], '우측');?>
			</select>
		</dd>
    <dd style="display:block;"><br>
		―― 1열 ――<br>
		디데이 명<br>
		<input type="text" class="mainskin--input" id="dday1_" name="dday1_"
			value="<?=$list_item[$i]['dday1_']?>"><br>
		날짜<br>
		<input type="date" id="dday1" name="dday1"
			value="<?=$list_item[$i]['dday1']?>"><br>
		<input type="checkbox" id="dday1_1" name="dday1_1" <?if($list_item[$i]['dday1_1'] == 'on') echo 'checked';?>> 1일부터 시작
		</dd>
		<dd style="display:block;"><br>
		―― 2열 ――<br>
		디데이 명<br>
		<input type="text" class="mainskin--input" id="dday2_" name="dday2_"
			value="<?=$list_item[$i]['dday2_']?>"><br>
		날짜<br>
		<input type="date" id="dday2" name="dday2"
			value="<?=$list_item[$i]['dday2']?>"><br>
			<input type="checkbox" id="dday2_1" name="dday2_1" <?if($list_item[$i]['dday2_1'] == 'on') echo 'checked';?>> 1일부터 시작
			</dd>
		<dd style="display:block;"><br>
		―― 3열 ――<br>
		디데이 명<br>
		<input type="text" class="mainskin--input" id="dday3_" name="dday3_"
			value="<?=$list_item[$i]['dday3_']?>"><br>
		날짜<br>
		<input type="date" id="dday3" name="dday3"
			value="<?=$list_item[$i]['dday3']?>"><br>
			<input type="checkbox" id="dday3_1" name="dday3_1" <?if($list_item[$i]['dday3_1'] == 'on') echo 'checked';?>> 1일부터 시작
			</dd>
			
		<dd style="display:block;"><br>
		―― 4열 ――<br>
		디데이 명<br>
		<input type="text" class="mainskin--input" id="dday4_" name="dday4_"
			value="<?=$list_item[$i]['dday4_']?>"><br>
		날짜<br>
		<input type="date" id="dday4" name="dday4"
			value="<?=$list_item[$i]['dday4']?>"><br>
			<input type="checkbox" id="dday4_1" name="dday4_1" <?if($list_item[$i]['dday4_1'] == 'on') echo 'checked';?>> 1일부터 시작
		</dd>
  </dl>

	<dl id="latest_edit" class=" <?if($list_item[$i]['content_type'] == 'latest') echo 'active_edit';?>" >
    <dt>최신글 연결</dt>
    <dd>
				스킨명
				<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['latest1'] ?>" name="latest1" id="latest1" >
				※ 비어있을 땐 basic스킨으로 출력됩니다.
		</dd><dd>
				출력 갯수
				<input class="mainskin--input sml" type="text" value="<?php echo $list_item[$i]['latest2'] ?>" name="latest2" id="latest2" >
				※ 기본 10개
		</dd>
		<dd>
				제외 게시판
				<input class="mainskin--input" type="text" placeholder="table1,table2, table3…" value="<?php echo $list_item[$i]['latest3'] ?>" name="latest3" id="latest3" >
				※게시판 테이블명을 입력합니다. 띄어쓰기 없이 ,(반점, 쉼표)로 구분합니다. 
		</dd>
  </dl>

	<dl id="links_edit" class=" <?if($list_item[$i]['content_type'] == 'links') echo 'active_edit';?>" >
	<dt>링크 연결</dt>
	<dd>
		<strong>링크 스타일</strong>
		<select name="links1" id="links1">
			<option value="">---</option>
			<?=write_option('round', $list_item[$i]['links1'], '둥글게');?>
			<?=write_option('diamond', $list_item[$i]['links1'], '마름모');?>
			<?=write_option('text', $list_item[$i]['links1'], '길게');?>
			</select>
	</dd>
	<dd>
		<strong>링크 정렬</strong>
		<select name="links2" id="links2">
			<option value="">---</option>
			<?=write_option('row', $list_item[$i]['links2'], '가로');?>
			<?=write_option('column', $list_item[$i]['links2'], '세로');?>
			</select>
	</dd>
	<dt>
		1열
	</dt>
	<dd style="display:block;">
		링크 이름<br>
		<input type="text" class="mainskin--input" id="links2_1" name="links2_1"
			value="<?=$list_item[$i]['links2_1']?>"><br>
		링크 아이콘<br>
		<input type="text" class="mainskin--input" id="links2_1" name="links2_1_"
			value="<?=$list_item[$i]['links2_1_']?>"><br>
		주소<br>
		<input type="text" class="mainskin--input" id="links2_2" name="links2_2"
			value="<?=$list_item[$i]['links2_2']?>">
		권한
		<select name="links2_3" id="links2_3">
		<option value="">---</option>
		<?for($l = 1; $l < 11; $l++){
			$links = 'links2_3';
			write_option($l, $list_item[$i][$links], $l);
			}?>
		</select>
	</dd>
	<dt>
		2열
	</dt>
	<dd style="display:block;">
		링크 이름<br>
		<input type="text" class="mainskin--input" id="links3_1" name="links3_1"
			value="<?=$list_item[$i]['links3_1']?>"><br>
		링크 아이콘<br>
		<input type="text" class="mainskin--input" id="links3_1" name="links3_1_"
			value="<?=$list_item[$i]['links3_1_']?>"><br>
		주소<br>
		<input type="text" class="mainskin--input" id="links3_2" name="links3_2"
			value="<?=$list_item[$i]['links3_2']?>">
		권한
		<select name="links3_3" id="links3_3">
		<option value="">---</option>
		<?for($l = 1; $l < 11; $l++){
			$links = 'links3_3';
			write_option($l, $list_item[$i][$links], $l);
			}?>
		</select>
	</dd>
	<dt>
		3열
	</dt>
	<dd style="display:block;">
		링크 이름<br>
		<input type="text" class="mainskin--input" id="links4_1" name="links4_1"
		value="<?=$list_item[$i]['links4_1']?>"><br>
		링크 아이콘<br>
		<input type="text" class="mainskin--input" id="links4_1" name="links4_1_"
			value="<?=$list_item[$i]['links4_1_']?>"><br>
		주소<br>
		<input type="text" class="mainskin--input" id="links4_2" name="links4_2"
			value="<?=$list_item[$i]['links4_2']?>">
		권한
		<select name="links4_3" id="links4_3">
		<option value="">---</option>
		<?for($l = 1; $l < 11; $l++){
			$links = 'links4_3';
			write_option($l, $list_item[$i][$links], $l);
			}?>
		</select>
	</dd>
	<dt>
		4열
	</dt>
	<dd style="display:block;">
		링크 이름<br>
		<input type="text" class="mainskin--input" id="links5_1" name="links5_1"
			value="<?=$list_item[$i]['links5_1']?>"><br>
		링크 아이콘<br>
		<input type="text" class="mainskin--input" id="links5_1" name="links5_1_"
			value="<?=$list_item[$i]['links5_1_']?>"><br>
		주소<br>
		<input type="text" class="mainskin--input" id="links5_2" name="links5_2"
			value="<?=$list_item[$i]['links5_2']?>">
		권한
		<select name="links5_3" id="links5_3">
		<option value="">---</option>
		<?for($l = 1; $l < 11; $l++){
			$links = 'links5_3';
			write_option($l, $list_item[$i][$links], $l);
			}?>
		</select>
	</dd>
		</dl>
		<?}?>
			
  

 		<button type="submit" id="btn_submit" accesskey="s" class="ui-btn point">등록</button>
    <button class="ui-btn" onclick="controlDiv('', '', 'close'); return false;">닫기</button>
			</form>	</div>
		</section>
	<!-- } 게시물 작성/수정 끝 -->
  <script>
function fwriteadd_submit(f)
	{
		<?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

		document.getElementById("btn_submit").disabled = "disabled";
		return true;
	}
  </script>