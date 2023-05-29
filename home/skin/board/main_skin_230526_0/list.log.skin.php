<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//옵션 생성
function write_option($option, $input, $text){
	echo '<option value="'.$option.'" ';
	if($input == $option) echo ' selected >';
	else echo '>';
	echo $text;
	echo '</option>';
}

$need_close_count = 0;
$result = sql_query("select * from {$write_table} order by wr_1, wr_2, wr_3, wr_4, wr_5, wr_6");

if (!$result || is_null($result)) { echo "<div class=\"empty_list\">레이아웃이 존재하지 않습니다.</div>"; } else {
$list_item = array();
for($i=0; $row=sql_fetch_array($result); $i++){
	$list_item[$i]['wr_1'] = $row['wr_1'];
	$list_item[$i]['wr_2'] = $row['wr_2'];
	$list_item[$i]['wr_3'] = $row['wr_3'];
	$list_item[$i]['wr_4'] = $row['wr_4'];
	$list_item[$i]['wr_5'] = $row['wr_5'];
	$list_item[$i]['wr_6'] = $row['wr_6'];

	$list_item[$i] = $row;
	$list_item[$i]['wr_type'] = $row['wr_type'];
	$list_item[$i]['wr_id'] = $row['wr_id'];
	$list_item[$i]['wr_content'] = $row['wr_content'];
	$list_item[$i]['div_width'] = $row['div_width'];
	$list_item[$i]['div_height'] = $row['div_height'];
	$list_item[$i]['wr_direction'] = $row['wr_direction'];
	$list_item[$i]['has_comment'] = $row['has_comment'];
	$list_item[$i]['wr_parent'] = $row['wr_parent'];
	$list_item[$i]['wr_comment'] = $row['wr_comment'];
	$list_item[$i]['wr_is_comment'] = $row['wr_is_comment'];
	$list_item[$i]['wr_comment_reply'] = $row['wr_comment_reply'];

	$list_item[$i]['wr_url'] = $row['wr_url'];
	$list_item[$i]['wr_style'] = $row['wr_style'];

	
	$wr_style_detail = json_decode($row['wr_style_detail'], true);
	$list_item[$i]['color'] = $wr_style_detail['color'];
	$list_item[$i]['back1'] = $wr_style_detail['back1'];
	$list_item[$i]['back2'] = $wr_style_detail['back2'];
	$list_item[$i]['back3'] = $wr_style_detail['back3'];
	$list_item[$i]['back4'] = $wr_style_detail['back4'];
	$list_item[$i]['back5'] = $wr_style_detail['back5'];
	$list_item[$i]['wr_move'] = $wr_style_detail['wr_move'];
	$list_item[$i]['margin_auto'] = $wr_style_detail['margin_auto'];
	$list_item[$i]['margin_top'] = $wr_style_detail['margin_top'];
	$list_item[$i]['margin_bottom'] = $wr_style_detail['margin_bottom'];
	$list_item[$i]['margin_right'] = $wr_style_detail['margin_right'];
	$list_item[$i]['margin_left'] = $wr_style_detail['margin_left'];
	$list_item[$i]['padding_auto'] = $wr_style_detail['padding_auto'];
	$list_item[$i]['padding_top'] = $wr_style_detail['padding_top'];
	$list_item[$i]['padding_bottom'] = $wr_style_detail['padding_bottom'];
	$list_item[$i]['padding_left'] = $wr_style_detail['padding_left'];
	$list_item[$i]['padding_right'] = $wr_style_detail['padding_right'];

	$wr_style_detail2 = json_decode($row['wr_style_detail2'], true);
	$list_item[$i]['border_width'] = $wr_style_detail2['border_width'];
	$list_item[$i]['border_style'] = $wr_style_detail2['border_style'];
	$list_item[$i]['border_color'] = $wr_style_detail2['border_color'];
	$list_item[$i]['border_radius_all'] = $wr_style_detail2['border_radius_all'];
	$list_item[$i]['border_radius_top'] = $wr_style_detail2['border_radius_top'];
	$list_item[$i]['border_radius_right'] = $wr_style_detail2['border_radius_right'];
	$list_item[$i]['border_radius_bottom'] = $wr_style_detail2['border_radius_bottom'];
	$list_item[$i]['border_radius_left'] = $wr_style_detail2['border_radius_left'];
	$list_item[$i]['justify_content'] = $wr_style_detail2['justify_content'];
	$list_item[$i]['align_items'] = $wr_style_detail2['align_items'];
	$list_item[$i]['theme'] = $wr_style_detail2['theme'];

	$themearr = explode(' ', $list_item[$i]['theme']);
	if(is_array($themearr)) $arrlen = count($themearr);
	else $arrlen = 0;
	$list_item[$i]['theme'] = '';
	for ($t = 0; $t < $arrlen; $t++){
		if(strpos($themearr[$t], 'effect') !== false){
			$list_item[$i]['theme3'] = $themearr[$t];
		} else {
			if ($list_item[$i]['theme'] == ''){
				$list_item[$i]['theme'] = $themearr[$t];
			} else $list_item[$i]['theme'] = $list_item[$i]['theme'].' '.$themearr[$t];
		}
	}
	$list_item[$i]['theme'] = rtrim($list_item[$i]['theme']);

	$wr_content_detail = json_decode($row['wr_content_detail'], true);
	$list_item[$i]['content_type'] = $wr_content_detail['content_type'];
	if($list_item[$i]['content_type'] == 'text'){
		$list_item[$i]['text1'] = $wr_content_detail['text1'];
		$list_item[$i]['text2'] = $wr_content_detail['text2'];
		$list_item[$i]['text3'] = $wr_content_detail['text3'];
	}
	if($list_item[$i]['content_type'] == 'board'){
		$list_item[$i]['board1'] = $wr_content_detail['board1'];
	}
	if($list_item[$i]['content_type'] == 'page'){
		$list_item[$i]['page1'] = $wr_content_detail['page1'];
	}
	if($list_item[$i]['content_type'] == 'slider'){
	$list_item[$i]['slider1'] = $wr_content_detail['slider1'];
	$list_item[$i]['slider2'] = $wr_content_detail['slider2'];
	}
	if($list_item[$i]['content_type'] == 'image'){
	$list_item[$i]['image1'] = $wr_content_detail['image1'];
	$list_item[$i]['image2'] = $wr_content_detail['image2'];
	$list_item[$i]['image3'] = $wr_content_detail['image3'];
	$list_item[$i]['image4'] = $wr_content_detail['image4'];
	}
	if($list_item[$i]['content_type'] == 'clock'){
	$list_item[$i]['clock1'] = $wr_content_detail['clock1'];
	$list_item[$i]['clock1_'] = $wr_content_detail['clock1_'];
	$list_item[$i]['clock1_1'] = $wr_content_detail['clock1_1'];
	$list_item[$i]['clock1_2'] = $wr_content_detail['clock1_2'];
	$list_item[$i]['clock2'] = $wr_content_detail['clock2'];
	$list_item[$i]['clock2_'] = $wr_content_detail['clock2_'];
	$list_item[$i]['clock2_1'] = $wr_content_detail['clock2_1'];
	$list_item[$i]['clock2_2'] = $wr_content_detail['clock2_2'];
	$list_item[$i]['clock3'] = $wr_content_detail['clock3'];
	$list_item[$i]['clock3_'] = $wr_content_detail['clock3_'];
	$list_item[$i]['clock3_1'] = $wr_content_detail['clock3_1'];
	$list_item[$i]['clock3_2'] = $wr_content_detail['clock3_2'];
	$list_item[$i]['clock4'] = $wr_content_detail['clock4'];
	}
	if($list_item[$i]['content_type'] == 'dday'){
	$list_item[$i]['dday1'] = $wr_content_detail['dday1'];
	$list_item[$i]['dday2'] = $wr_content_detail['dday2'];
	$list_item[$i]['dday3'] = $wr_content_detail['dday3'];
	$list_item[$i]['dday4'] = $wr_content_detail['dday4'];
	$list_item[$i]['dday1_'] = $wr_content_detail['dday1_'];
	$list_item[$i]['dday2_'] = $wr_content_detail['dday2_'];
	$list_item[$i]['dday3_'] = $wr_content_detail['dday3_'];
	$list_item[$i]['dday4_'] = $wr_content_detail['dday4_'];
	$list_item[$i]['dday1_1'] = $wr_content_detail['dday1_1'];
	$list_item[$i]['dday2_1'] = $wr_content_detail['dday2_1'];
	$list_item[$i]['dday3_1'] = $wr_content_detail['dday3_1'];
	$list_item[$i]['dday4_1'] = $wr_content_detail['dday4_1'];
	$list_item[$i]['dday5'] = $wr_content_detail['dday5'];
	$list_item[$i]['dday6'] = $wr_content_detail['dday6'];
	}
	if($list_item[$i]['content_type'] == 'latest'){
	$list_item[$i]['latest1'] = $wr_content_detail['latest1'];
	$list_item[$i]['latest2'] = $wr_content_detail['latest2'];
	$list_item[$i]['latest3'] = $wr_content_detail['latest3'];
	}
	if($list_item[$i]['content_type'] == 'links'){
		$list_item[$i]['links1'] = $wr_content_detail['links1'];
		$list_item[$i]['links2'] = $wr_content_detail['links2'];
		$list_item[$i]['links2_1'] = $wr_content_detail['links2_1'];
		$list_item[$i]['links2_1_'] = $wr_content_detail['links2_1_'];
		$list_item[$i]['links2_2'] = $wr_content_detail['links2_2'];
		$list_item[$i]['links2_3'] = $wr_content_detail['links2_3'];
		$list_item[$i]['links3_1'] = $wr_content_detail['links3_1'];
		$list_item[$i]['links3_1_'] = $wr_content_detail['links3_1_'];
		$list_item[$i]['links3_2'] = $wr_content_detail['links3_2'];
		$list_item[$i]['links3_3'] = $wr_content_detail['links3_3'];
		$list_item[$i]['links4_1'] = $wr_content_detail['links4_1'];
		$list_item[$i]['links4_1_'] = $wr_content_detail['links4_1_'];
		$list_item[$i]['links4_2'] = $wr_content_detail['links4_2'];
		$list_item[$i]['links4_3'] = $wr_content_detail['links4_3'];
		$list_item[$i]['links5_1'] = $wr_content_detail['links5_1'];
		$list_item[$i]['links5_1_'] = $wr_content_detail['links5_1_'];
		$list_item[$i]['links5_2'] = $wr_content_detail['links5_2'];
		$list_item[$i]['links5_3'] = $wr_content_detail['links5_3'];
	}
	
	
}

for($i=0; $i < count($list_item); $i++){
set_session('ss_delete_comment_'.$list_item[$i]['wr_id'].'_token', $token = uniqid(time()));
$delete_href = '';
if($list_item[$i]['wr_is_comment'] != 0){
	$delete_href  = './delete_comment.php?bo_table='.$bo_table.'&amp;comment_id='.$list_item[$i]['wr_id'].'&amp;wr_parent='.$list_item[$i]['wr_parent'].'&amp;token='.$token;
} else {
	$delete_href ='./delete.php?bo_table='.$bo_table.'&amp;wr_id='.$list_item[$i]['wr_id'].'&amp;token='.$token;
}
$query_string = clean_query_string($_SERVER['QUERY_STRING']);
$reply_href = './board.php?'.$query_string.'&amp;wr_id='.$list_item[$i]['wr_id'].'&amp;w=r&amp;wr_parent='.$list_item[$i]['wr_id'].'#bo_w_c';
$edit_href = './board.php?'.$query_string.'&amp;wr_id='.$list_item[$i]['wr_id'].'&amp;w=u#bo_w_c_'.$list_item[$i]['wr_id'].'#bo_w_c_'.$list_item[$i]['wr_id'];

if($list_item[$i]['wr_type'] == 'layout'){?>
<div class="main_layout<?if($list_item[$i]['theme']) echo ' '.$list_item[$i]['theme'];else echo ' main_default';if($list_item[$i]['theme3']) echo ' '.$list_item[$i]['theme3']; if($list_item[$i]['has_comment'] == 0) echo ' children';?>" id="layout-<?=$list_item[$i]['wr_id']?>" style="<?
if( !$list_item[$i]['div_width'] && !$list_item[$i]['div_height'] ){
	echo 'width: 100%; height: 800px;';
} else if ($list_item[$i]['margin_auto'] == 'auto' || !$list_item[$i]['margin_auto']){
	if($list_item[$i]['has_comment'] == 0){
	echo 'width: calc('.$list_item[$i]['div_width'].' - 20px);';
	echo 'height: calc('.$list_item[$i]['div_height'].' - 20px);';
	} else {
	echo 'width:'.$list_item[$i]['div_width'].';';
	echo 'height:'.$list_item[$i]['div_height'].';';
	}
} else if($list_item[$i]['margin_left']&&$list_item[$i]['margin_right']&&$list_item[$i]['margin_top']&&$list_item[$i]['margin_bottom']){
	echo 'width: calc('.$list_item[$i]['div_width'].' - '.$list_item[$i]['margin_left'].' - '.$list_item[$i]['margin_right'].');';
	echo 'height: calc('.$list_item[$i]['div_height'].' - '.$list_item[$i]['margin_top'].' - '.$list_item[$i]['margin_bottom'].');';
} else {
	echo 'width:'.$list_item[$i]['div_width'].';';
	echo 'height:'.$list_item[$i]['div_height'].';';
}?>
flex-direction:<?=$list_item[$i]['wr_direction']?>;
<?if($list_item[$i]['wr_url'] && $list_item[$i]['wr_move'] != 'on') echo 'background-image: url('.$list_item[$i]['wr_url'].');';
if($list_item[$i]['color']) echo 'background-color:'.$list_item[$i]['color'].';';
if($list_item[$i]['back1']) echo 'background-repeat:'.$list_item[$i]['back1'].';';
if($list_item[$i]['back2'] && $list_item[$i]['back3']) echo 'background-position:'.$list_item[$i]['back2'].' '.$list_item[$i]['back3'].';';
if($list_item[$i]['back4']) echo 'background-size:'.$list_item[$i]['back4'].';';
if($list_item[$i]['back5']) echo 'background-attachment:'.$list_item[$i]['back5'].';';
if($list_item[$i]['border_width']) echo 'border-width: '.$list_item[$i]['border_width'].';';
if($list_item[$i]['border_style']) echo 'border-style: '.$list_item[$i]['border_style'].';';
if($list_item[$i]['border_color']) echo 'border-color: '.$list_item[$i]['border_color'].';';

if($list_item[$i]['margin_auto'] == 'manual') echo 'margin: '.$list_item[$i]['margin_top'].' '.$list_item[$i]['margin_right'].' '.$list_item[$i]['margin_bottom'].' '.$list_item[$i]['margin_left'].';'; else if($list_item[$i]['has_comment'] == 0) echo 'margin: 10px;';

if($list_item[$i]['padding_auto'] == 'manual') echo 'padding: '.$list_item[$i]['padding_top'].' '.$list_item[$i]['padding_right'].' '.$list_item[$i]['padding_bottom'].' '.$list_item[$i]['padding_left'].';'; else if ($list_item[$i]['has_comment'] == 0) echo 'padding:10px;';
if($list_item[$i]['border_radius_all'] || $list_item[$i]['border_radius_top']){
	if($list_item[$i]['border_radius_all'] == ''){
	echo 'border-radius:'.$list_item[$i]['border_radius_top'].' '.$list_item[$i]['border_radius_right'].' '.$list_item[$i]['border_radius_bottom'].' '.$list_item[$i]['border_radius_left'].';';
} else echo 'border-radius:'.$list_item[$i]['border_radius_all'].';';
}
if($list_item[$i]['justify_content'] || $list_item[$i]['align_items']){
if($list_item[$i]['wr_direction'] == 'row') echo 'justify-content:'.$list_item[$i]['justify_content'].';';else echo 'justify-content:'.$list_item[$i]['align_items'].';';
} if($list_item[$i]['justify_content'] || $list_item[$i]['align_items']){
if($list_item[$i]['wr_direction'] == 'row') echo 'align-items:'.$list_item[$i]['align_items'].';'; else echo 'align-items:'.$list_item[$i]['justify_content'].';';
} if($list_item[$i]['wr_style']) echo $list_item[$i]['wr_style'];?>
<?echo 'z-index:'.strlen($list_item[$i]['wr_comment_reply']).';';?>
">
<input type="hidden" id="origin_width_<?=$list_item[$i]['wr_id']?>" value="<?=$list_item[$i]['div_width']?>">
<input type="hidden" id="origin_height_<?=$list_item[$i]['wr_id']?>" value="<?=$list_item[$i]['div_height']?>">
<? 
  // 요소마다 삭제/수정버튼을 넣어줌
  if($is_admin){
		echo '<div class="mainskin--btns">';
		?><div class="block_num">
			<?echo $list_item[$i]['wr_1'];
			if ($list_item[$i]['wr_2']) echo ' > '.$list_item[$i]['wr_2'];
			if ($list_item[$i]['wr_3']) echo ' > '.$list_item[$i]['wr_3'];
			if ($list_item[$i]['wr_4']) echo ' > '.$list_item[$i]['wr_4'];
			if ($list_item[$i]['wr_5']) echo ' > '.$list_item[$i]['wr_5'];
			if ($list_item[$i]['wr_6']) echo ' > '.$list_item[$i]['wr_6'];
			?>
		</div>
		<?
	if($list_item[$i]['content_type'] == ''){?>
	<a class="ui-btn small mainskin--btn" href="<?=$reply_href?>" onclick="controlDiv('<?=$list_item[$i]['wr_id']?>', '<?=$list_item[$i]['wr_comment_reply']?>', 'c'); return false;"><i class="material-icons">dashboard_customize</i></a>
  <a class="ui-btn small mainskin--btn" href="javascript:void(0)" onclick="controlDiv('<?=$list_item[$i]['wr_id']?>', '<?=$list_item[$i]['wr_comment_reply']?>', 'u'); return false;"><i class="material-icons">settings</i></a>
	<?} else {?>
	<a class="ui-btn small mainskin--btn" href="javascript:void(0)" onclick="controlDiv('<?=$list_item[$i]['wr_id']?>', '<?=$list_item[$i]['wr_comment_reply']?>', 'u'); return false;"><i class="material-icons">settings</i></a>
	<?}?>
	<a href="<?php echo $delete_href ?>" class="ui-btn small point del mainskin--btn" onclick="del(this.href); return false;"><i class="material-icons">delete</i></a>
	<?echo '</div>';
}?>
<?if($list_item[$i]['wr_move'] == 'on' && $list_item[$i]['wr_url']){?>
	<div class="mainskin--background"
	style="<?='background-image: url('.$list_item[$i]['wr_url'].');'?>
	<?if($list_item[$i]['color']) echo 'background-color:'.$list_item[$i]['color'].';';
	if($list_item[$i]['back1']) echo 'background-repeat:'.$list_item[$i]['back1'].';';
	if($list_item[$i]['back2'] && $list_item[$i]['back3']) echo 'background-position:'.$list_item[$i]['back2'].' '.$list_item[$i]['back3'].';';
	if($list_item[$i]['back4']) echo 'background-size:'.$list_item[$i]['back4'].';';
	if($list_item[$i]['back5']) echo 'background-attachment:'.$list_item[$i]['back5'].';';?>
	"></div>
<?}
if($list_item[$i]['wr_direction'] == 'row' && $list_item[$i]['theme'] == 'main_digital'){
	} else {?>
	<div class="mainskin--themehead"></div>	
<?}
// 여기부터 내용물

	if($list_item[$i]['content_type'] == 'default'){?>

	<?=nl2br($list_item[$i]['wr_content'])?>	

	<?} else if($list_item[$i]['content_type'] == 'text'){?>
		<span class="mainskin--text--text"
		style="<?if($list_item[$i]['text1']) echo 'font-family:'.$list_item[$i]['text1'].';';?>
		<?if($list_item[$i]['text2'] == 'bold') echo 'font-weight: bold;'; else if ($list_item[$i]['text2'] == 'italic') echo 'font-style:italic;'; else if ($list_item[$i]['text2'] == 'bold-italic') echo 'font-weight: bold; font-style:italic;';?>
		<?if($list_item[$i]['text3']) echo $list_item[$i]['text3'];?>
		">
		<?
		echo nl2br(htmlspecialchars($list_item[$i]['wr_content']));
		?>

		</span>	
	<?} else if($list_item[$i]['content_type'] == 'board'){?>

		<iframe src="<?=$board_skin_url?>/board.php?bo_table=<?=$list_item[$i]['board1']?>" name="frm"id="<?='iframe_'.$list_item[$i]['wr_id']?>" 
		frameborder="0" scrolling="auto" 
		style="width:100%; height:100%"></iframe>

	<?} else if($list_item[$i]['content_type'] == 'page'){?>

		<iframe src="<?=$board_skin_url?>/content.php?co_id=<?=$list_item[$i]['page1']?>" name="frm" id="<?='iframe_'.$list_item[$i]['wr_id']?>" 
		style="width:100%;height:100%;" 
    frameborder="0" scrolling="auto"></iframe>

	<?} else if($list_item[$i]['content_type'] == 'slider') {?>
		<?
			$tmp_slider_table = $g5['write_prefix'] . $list_item[$i]['slider1'];
			if(!$list_item[$i]['slider2']) $list_item[$i]['slider2'] = 5;
			$slider_sql = " SELECT * from $tmp_slider_table where wr_is_comment = 0 order by wr_datetime DESC limit {$list_item[$i]['slider2']} ";
			
			$slider_res = sql_query($slider_sql);
			for ($s=0; $r = sql_fetch_array($slider_res); $s++) {
				if ($r['wr_url'] != '') {
					$file_link[$s] = $r['wr_url'];
				} else {
				$sql = " SELECT bf_file from avo_board_file where wr_id = {$r['wr_id']} and bo_table = '{$list_item[$i]['slider1']}' ";
				$res = sql_fetch($sql);
				 $file_link[$s] = G5_URL.'/data/file/'.$list_item[$i]['slider1'].'/'.$res['bf_file'];
				}
				$file_adult[$s] = $r['wr_adult'];
				$file_cont[$s] = $r['wr_content'];
			}

			if(count($file_link)){
			?>
		<div class="mainskin--slider">
			
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<?for ($s = 0; $s < count($file_link); $s++) {
							echo '<div class="swiper-slide">';
							echo '<div class="mainskin--slider--image" style="background: url(';
							echo "'".$file_link[$s]."');";
							if ($file_adult[$s] == '1') {
								echo 'filter:blur(10px);';
							} 
							echo ' background-position:';
							echo $list_item[$i]['back2'].' '.$list_item[$i]['back3'].';';
							echo ' background-size: '.$list_item[$i]['back4'].';';
							echo 'background-repeat: '.$list_item[$i]['back1'].'; background-attachment: '.$list_item[$i]['back5'].'">';
							echo '</div>';
							echo '<div class="mainskin--slider--text">';
							echo $file_cont[$s];
							echo '</div></div>';
						}?>
					</div>
				</div>
				
				<div class="swiper-pagination"></div>

				<div class="swiper-prev">
					<div class="material-icons">arrow_back</div>
				</div>

				<div class="swiper-next">
					<div class="material-icons">arrow_forward</div>
				</div>

		</div>
		<?} else echo '이미지가 존재하지 않습니다.';?>

	<?} else if($list_item[$i]['content_type'] == 'image'){?>
		<img src="<?=$list_item[$i]['image1']?>" style="width: 100%; height:100%;
		object-position:<?=$list_item[$i]['image2'].' '.$list_item[$i]['image3']?>; object-fit:<?=$list_item[$i]['image4']?>;">
	<?} else if($list_item[$i]['content_type'] == 'clock') {?>
		<div class="mainskin--clocks" style="align-items: <?=$list_item[$i]['clock4']?>">
		<a href="https://time.is/Seoul" id="time_is_link" rel="nofollow"
        style="display:none; pointer-events: none;"></a>
      <span id="Seoul_z43c" class="mainskin--clock <?=$list_item[$i]['clock1_1']?>" style="<?=$list_item[$i]['clock1_2']?>"></span>
			<span id="Ulsan_z43c" class="mainskin--clock <?=$list_item[$i]['clock2_1']?>" style="<?=$list_item[$i]['clock2_2']?>"></span>
			<span id="KST_z43c" class="mainskin--clock <?=$list_item[$i]['clock3_1']?>" style="<?=$list_item[$i]['clock3_2']?>"></span>
			<!-- 한국 기준입니다. 타국 기준 시계가 필요하다면 https://time.is/widgets 을 참고해주세요. -->
      <script src="//widget.time.is/ko.js"></script>
      <script>
          time_is_widget.init({
              Seoul_z43c: {
                  template: "DATE TIME",
                  date_format: "<?=$list_item[$i]['clock1']?>",
									time_format: "<?=$list_item[$i]['clock1_']?>"
              },
              Ulsan_z43c: {
                  template: "DATE TIME",
									date_format: "<?=$list_item[$i]['clock2']?>",
									time_format: "<?=$list_item[$i]['clock2_']?>"
              },
              KST_z43c: {
                  template: "DATE TIME",
									date_format: "<?=$list_item[$i]['clock3']?>",
									time_format: "<?=$list_item[$i]['clock3_']?>"
              }
          });
      </script>
	</div>
	<?} else if($list_item[$i]['content_type'] == 'dday') {?>

		<div class="mainskin--ddays">
		<?
		$today = date("Y-m-d",time());
		$dday = array();
		if($list_item[$i]['dday1']){
			$dday1 = $list_item[$i]['dday1'];
			$d_day1 = intval((strtotime($today)-strtotime($dday1)) / 86400);
			if ($list_item[$i]['dday1_1'] == 'on') $d_day1 = $d_day1 + 1;
			echo "<div class='mainskin--dday' style='align-items:".$list_item[$i]['dday5']."'>";
			echo "<div class='mainskin--dday--text'>";
			echo $list_item[$i]['dday1_'].'</div>';
			echo "<div class='mainskin--dday--day'>";
			if($d_day1 > 0){
				echo "+ ";
				echo $d_day1.'<span class="mainskin--dday--text2">days</span></div>';
			} else if ($d_day1 < 0){
				echo "- ";
				echo ($d_day1 * -1).'<span class="mainskin--dday--text2">days</span></div>';
			} else {
				echo "D - Day";
			}
			echo "</div>";
		}
		if($list_item[$i]['dday2']){
			$dday2 = $list_item[$i]['dday2'];
			$d_day2 = intval((strtotime($today)-strtotime($dday2)) / 86400);
			if ($list_item[$i]['dday2_1'] == 'on') $d_day2 = $d_day2 + 1;

			echo "<div class='mainskin--dday' style='align-items:".$list_item[$i]['dday5']."'>";
			echo "<div class='mainskin--dday--text'>";
			echo $list_item[$i]['dday2_'].'</div>';
			echo "<div class='mainskin--dday--day'>";
			if($d_day2 > 0){
				echo "+ ";
				echo $d_day2.'<span class="mainskin--dday--text2">days</span></div>';
			} else if ($d_day2 < 0){
				echo "- ";
				echo ($d_day2 * -1).'<span class="mainskin--dday--text2">days</span></div>';
			} else {
				echo "D - Day";
			}
			echo "</div>";
		}
		if($list_item[$i]['dday3']){
			$dday3 = $list_item[$i]['dday3'];
			$d_day3 = intval((strtotime($today)-strtotime($dday3)) / 86400);
			if ($list_item[$i]['dday3_1'] == 'on') $d_day3 = $d_day3 + 1;

			echo "<div class='mainskin--dday' style='align-items:".$list_item[$i]['dday5']."'>";
			echo "<div class='mainskin--dday--text'>";
			echo $list_item[$i]['dday3_'].'</div>';
			echo "<div class='mainskin--dday--day'>";
			if($d_day3 > 0){
				echo "+ ";
				echo $d_day3.'<span class="mainskin--dday--text2">days</span></div>';
			} else if ($d_day3 < 0){
				echo "- ";
				echo ($d_day3 * - 1).'<span class="mainskin--dday--text2">days</span></div>';
			} else {
				echo "D - Day";
			}
			echo "</div>";
		}
		if($list_item[$i]['dday4']){
			$dday4 = $list_item[$i]['dday4'];
			$d_day4 = intval((strtotime($today)-strtotime($dday4)) / 86400);
			if ($list_item[$i]['dday4_1'] == 'on') $d_day4 = $d_day4 + 1;

			echo "<div class='mainskin--dday' style='align-items:".$list_item[$i]['dday5']."'>";
			echo "<div class='mainskin--dday--text'>";
			echo $list_item[$i]['dday4_'].'</div>';
			echo "<div class='mainskin--dday--day'>";
			if($d_day4 > 0){
				echo "+ ";
				echo $d_day4.'<span class="mainskin--dday--text2">days</span></div>';
			} else if ($d_day4 < 0){
				echo "- ";
				echo ($d_day4 * -1).'<span class="mainskin--dday--text2">days</span></div>';
			} else {
				echo "D - Day";
			}
			echo "</div>";
		}
		?>
	</div>

	<?} else if($list_item[$i]['content_type'] == 'latest') {?>

		<?include_once($board_skin_path.'/main_latest.lib.php');
		if(!$list_item[$i]['latest1'] || $list_item[$i]['latest1'] == ''){
			$list_item[$i]['latest1'] = 'main_latest';
		}
		if($list_item[$i]['latest2'] == ''){
			$list_item[$i]['latest2'] = 10;
		}
		if ($list_item[$i]['latest3'] && $list_item[$i]['latest3'] != ''){
			$excepts = explode(",", $list_item[$i]['latest3']);
			for($e = 0; $e < count($excepts); $e++){
				$except_lists[$e]= "'".$excepts[$e]."'";
			}
			$except_list = join(", ", $except_lists);
		} else {
			$except_list = "''";
		}
		echo latest_main($board['bo_skin'], $member['mb_level'],$list_item[$i]['latest1'], $list_item[$i]['latest2'], $except_list);?>

	<?} else if($list_item[$i]['content_type'] == 'links') {?>
		<div class="mainskin--links" 
		<?if($list_item[$i]['links2']) echo 'style= "flex-direction:'.$list_item[$i]['links2'].';"';?>>
		<?for($link = 2; $link < 6; $link++) {

			$table = 'links'.$link;
			$table1 = $table.'_1'; //이름
			$table1_ = $table.'_1_'; //아이콘
			$table2 = $table.'_2'; //주소
			$table3 = $table.'_3'; //권한

			if(!$list_item[$i][$table1]&&!$list_item[$i][$table2]&&!$list_item[$i][$table1_]&&!$list_item[$i][$table3]) continue;

			if($member['mb_level'] >= $list_item[$i][$table3]){
				echo '<a class="mainskin--link '.$list_item[$i]['links1'];
				if($list_item[$i]['links2'] == 'row') echo ' link_row'; else echo ' link_column';
				echo '" href="'.$list_item[$i][$table2].'">';
				echo '<div class="mainskin--icon"><i class="material-icons">'.$list_item[$i][$table1_].'</i></div>';
				echo '<div class="mainskin--link--text" style="';
				if($list_item[$i]['links2'] == 'row') echo 'top:35px;'; else echo 'left:35px; text-align: left;';
				echo '">';
				echo $list_item[$i][$table1].'</div>';
				echo '</a>';
				} else {
				echo '<a class="mainskin--link '.$list_item[$i]['links1'];
				if($list_item[$i]['links2'] == 'row') echo ' link_row'; else echo ' link_column';
				echo '" style="opacity:0.5; pointer-events:none;';
				echo '">';
				echo '<div class="mainskin--icon"><i class="material-icons">'.$list_item[$i][$table1_].'</i></div>';
				echo '<div class="mainskin--link--text" style="';
				if($list_item[$i]['links2'] == 'row') echo 'top:35px;'; else echo 'left:35px; text-align: left;';
				echo '">';
				echo $list_item[$i][$table1].'</div>';	
				echo '</a>';
			}
			
			
		}?>
	</div>
	
	<?}?>
	<!-- 선택 출력 끝 -->



<?

if($list_item[$i]['has_comment'] == 0){
  echo '</div>';
} else {
	$need_close_count++;
}

if($list_item[$i]['has_comment'] == 0 && strlen($list_item[$i]['wr_comment_reply']) > strlen($list_item[$i + 1]['wr_comment_reply'])){
	for ($de = 0; $de < (strlen($list_item[$i]['wr_comment_reply']) - strlen($list_item[$i + 1]['wr_comment_reply'])); $de++){
			echo '</div>';
			$need_close_count--;
	}
}

if(!$list_item[$i + 1]['wr_id'] && $need_close_count > 0){
for ($c = 0; $c < $need_close_count; $c++){
	echo '</div>';
}
}

}

include($board_skin_path.'/list_update.skin.php');

}}?>

<?if($is_admin){?>
	<script>
		var editCss = {};
		$(document).ready(function(){
			//모든 직접 입력 스타일 값을 저장한다.
		$('.main_layout').map(function() {
		var layoutId = this.id.substr(7);
		var layoutEdit = $('#bo_w_c_' + layoutId).find('#wr_style');
		var editText = layoutEdit.val();
		editText = editText.replace(/: /g, ':');
		editText = editText.replace(/; /g, ';');
		editCss[layoutId] = editText;
	});
	});
	

function changeDisign(id, where, changedValue){
	var targetEl = document.getElementById('layout-' + id);
	var targetW = document.getElementById('bo_w_c_' + id);
	

	if(where == 'wr_direction'){
		targetEl.style.flexDirection = changedValue;
	}
	if(where == 'div_width'){
		targetEl.style.width = changedValue;
	}
	if(where == 'div_height'){
		targetEl.style.height = changedValue;
	}

	if(where == 'wr_url'){
		targetEl.style.backgroundImage = 'url(' + changedValue + ')';
	}
	if(where == 'back1'){
		targetEl.style.backgroundRepeat = changedValue;
	}
	if(where == 'back2_' || where == 'back3_'){
		if(where == 'back2_'){
		if (changedValue == 'text'){
			$('#bo_w_c_' + id + ' #back2').val('');
			$('#bo_w_c_' + id + ' #back2').show();
		} else {
			$('#bo_w_c_' + id + ' #back2').val(changedValue);
			$('#bo_w_c_' + id + ' #back2').hide();
		}
		}
		if(where == 'back3_'){
		if (changedValue == 'text'){
			$('#bo_w_c_' + id + ' #back3').val('');
			$('#bo_w_c_' + id + ' #back3').show();
		} else {
			$('#bo_w_c_' + id + ' #back3').val(changedValue);
			$('#bo_w_c_' + id + ' #back3').hide();
		}
		}
		var x = document.querySelector('#bo_w_c_' + id + ' #back2');
		var y = document.querySelector('#bo_w_c_' + id + ' #back3');
		targetEl.style.backgroundPosition = x.value + ' ' + y.value;
	}
	if(where == 'back4'){
		targetEl.style.backgroundSize = changedValue;
	}
	if(where == 'back5'){
		targetEl.style.backgroundAttachment = changedValue;
	}
	if(where == 'color' || where == 'color_text'){
		var color = document.querySelector('#bo_w_c_' + id + ' #color');
		var color_text = document.querySelector('#bo_w_c_' + id + ' #color_text');
		if (color.value == color_text.value){
			return;
		}
		color.setAttribute('value', changedValue);
		color_text.setAttribute('value', changedValue);
		targetEl.style.backgroundColor = changedValue;
	}

	if(where == 'margin_auto'){
		if( changedValue == 'manual'){
			var a = document.querySelector('#bo_w_c_' + id + ' #margin_top');
			var b = document.querySelector('#bo_w_c_' + id + ' #margin_right');
			var c = document.querySelector('#bo_w_c_' + id + ' #margin_bottom');
			var d = document.querySelector('#bo_w_c_' + id + ' #margin_left');
			targetEl.style.margin = a.value + ' ' + b.value + ' ' + c.value + ' ' + d.value;
			targetEl.style.width = 'calc(' + $('#origin_width_' + id).val() + ' - ' + b.value + ' - ' + d.value + ')';
			targetEl.style.height = 'calc(' + $('#origin_height_' + id).val() + ' - ' + a.value + ' - ' + c.value + ')';
	} else {
		targetEl.style.margin = '10px';
		targetEl.style.width = 'calc(' + $('#origin_width_' + id).val() + ' - 20px)';
		targetEl.style.height = 'calc(' + $('#origin_height_' + id).val() + ' - 20px)';
	}
}	
	if(where == 'margin_top' || where == 'margin_right' || where == 'margin_bottom' || where == 'margin_left') {
		if($('#bo_w_c_' + id + ' #margin_auto').val() == 'auto'){
			return;
		} else {
			var a = document.querySelector('#bo_w_c_' + id + ' #margin_top');
			var b = document.querySelector('#bo_w_c_' + id + ' #margin_right');
			var c = document.querySelector('#bo_w_c_' + id + ' #margin_bottom');
			var d = document.querySelector('#bo_w_c_' + id + ' #margin_left');
			targetEl.style.margin = a.value + ' ' + b.value + ' ' + c.value + ' ' + d.value;
			targetEl.style.width = 'calc(' + $('#origin_width_' + id).val() + ' - ' + b.value + ' - ' + d.value + ')';
			targetEl.style.height = 'calc(' + $('#origin_height_' + id).val() + ' - ' + a.value + ' - ' + c.value + ')';
		}
	}
	if(where == 'padding_auto'){
		if( changedValue == 'manual'){
			var a = document.querySelector('#bo_w_c_' + id + ' #padding_top');
			var b = document.querySelector('#bo_w_c_' + id + ' #padding_right');
			var c = document.querySelector('#bo_w_c_' + id + ' #padding_bottom');
			var d = document.querySelector('#bo_w_c_' + id + ' #padding_left');
			targetEl.style.padding = a.value + ' ' + b.value + ' ' + c.value + ' ' + d.value;
	} else {
		targetEl.style.padding = '10px';
	}
}	
	if(where == 'padding_top' || where == 'padding_right' || where == 'padding_bottom' || where == 'padding_left') {
		if($('#bo_w_c_' + id + ' #padding_auto').val() == 'auto'){
			return;
		} else {
			var a = document.querySelector('#bo_w_c_' + id + ' #padding_top');
			var b = document.querySelector('#bo_w_c_' + id + ' #padding_right');
			var c = document.querySelector('#bo_w_c_' + id + ' #padding_bottom');
			var d = document.querySelector('#bo_w_c_' + id + ' #padding_left');
			targetEl.style.padding = a.value + ' ' + b.value + ' ' + c.value + ' ' + d.value;
		}
	}

	if(where == 'border_width'){
		targetEl.style.borderWidth = changedValue;
	}
	if(where == 'border_style'){
		targetEl.style.borderStyle = changedValue;
	}
	if(where == 'border_color'){
		targetEl.style.borderColor = changedValue;
	}
	

	if(where == 'theme2'){
		if (changedValue == 'text'){
			$('#bo_w_c_' + id + ' #theme').val('');
			$('#bo_w_c_' + id + ' #theme').show();
		} else {
			$('#bo_w_c_' + id + ' #theme').val(changedValue);
			$('#bo_w_c_' + id + ' #theme').hide();
		}
	}

	if(where == 'content_type'){
		if(document.querySelector('#bo_w_c_' + id + ' .active_edit')){
			document.querySelector('#bo_w_c_' + id + ' .active_edit').classList.remove('active_edit');
		}
		
		document.querySelector('#bo_w_c_' + id + ' #' + changedValue + '_edit').classList.add('active_edit');
		if(changedValue == 'default' || changedValue == 'text'){
			$('#bo_w_c_' + id + ' #wr_content_text').val('');
			$('#bo_w_c_' + id + ' #wr_content_default').val('');
		}
	}
	if(where == 'wr_style'){
		var layoutStyle = '';
		var originStyle = '';
		layoutStyle = targetEl.style.cssText;
		//현재 적용된 스타일
		layoutStyle = layoutStyle.replace(/: /g, ':');
		layoutStyle = layoutStyle.replace(/; /g, ';');
		originStyle = layoutStyle.replace(editCss[id], ''); 
		//원래 적용되어 있던 스타일을 제거한다.
		targetEl.style.cssText = originStyle + changedValue;
		changedValue = changedValue.replace(/: /g, ':');
		changedValue = changedValue.replace(/; /g, ';');
		editCss[id] = changedValue;
	}
	if(where == 'image2_' || where == 'image3_'){
  var x = document.querySelector('#bo_w_c_' + id + ' #image2');
  var y = document.querySelector('#bo_w_c_' + id + ' #image3');
  targetEl.style.imagegroundPosition = x.value + ' ' + y.value;
  if(where == 'image2_'){
  if (changedValue == 'text'){
    $('#bo_w_c_' + id + ' #image2').val('');
    $('#bo_w_c_' + id + ' #image2').show();
  } else {
    $('#bo_w_c_' + id + ' #image2').val(changedValue);
    $('#bo_w_c_' + id + ' #image2').hide();
  }
}
if(where == 'image3_'){
  if (changedValue == 'text'){
    $('#bo_w_c_' + id + ' #image3').val('');
    $('#bo_w_c_' + id + ' #image3').show();
  } else {
    $('#bo_w_c_' + id + ' #image3').val(changedValue);
    $('#bo_w_c_' + id + ' #image3').hide();
  }
}
}
}
</script>
<?}?>
<script>

// new Swiper(선택자, 옵션)
new Swiper('.swiper-container', {
  slidesPerView: 1, //한번에 보여줄 슬라이드 개수
  spaceBetween: 10, //슬라이드 사이 여백
  loop: true,
  allowTouchMove: true,
  autoplay: {
      delay: 3000
  },
  pagination: {
      el: '.swiper-pagination', //페이지 번호 요소 선택자
      clickable: true //사용자가 페이지 번호 요소 제어 가능 여부
  },
  navigation: {
      prevEl: '.swiper-prev',
      nextEl: '.swiper-next'
  }
});
</script>