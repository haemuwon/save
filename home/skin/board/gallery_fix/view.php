<?php 
include_once('./_common.php'); 
	$view=sql_fetch("select * from {$write_table} where wr_id='{$wr_id}'" );
    // 윗글을 얻음 
    $prev = sql_fetch(" select wr_id, wr_num from {$write_table} where wr_is_comment = 0 and wr_num < '{$view['wr_num']}' and wr_parent=wr_id order by wr_num desc limit 1 ");

    // 아래글을 얻음 
    $next = sql_fetch(" select wr_id, wr_num from {$write_table} where wr_is_comment = 0 and wr_num > '{$view['wr_num']}' and wr_parent=wr_id order by wr_num asc limit 1 ");
 
	// 수정, 삭제 링크
	$update_href = $delete_href = '';
	// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
	if (($member['mb_id'] && ($member['mb_id'] === $view['mb_id'])) || $is_admin) {
		$update_href = './write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
		set_session('ss_delete_token', $token = uniqid(time()));
		$delete_href ='./delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token;
	}else if (!$view['mb_id']) { // 회원이 쓴 글이 아니라면
	$update_href = './password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$view['wr_id'].'&amp;page='.$page.$qstr;
	$delete_href = './password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$view['wr_id'].'&amp;page='.$page.$qstr;
} 
?>

<!-- 게시물 읽기 시작 { -->

	<div class="bo_v_nb">
		<? if ($prev['wr_id']) { ?><a href="javascript:open_view('<?=$bo_table?>','<?=$prev['wr_id']?>');" class="prev" ><span>이전글</span></a><? } ?>
		<? if ($next['wr_id']) { ?><a href="javascript:open_view('<?=$bo_table?>','<?=$next['wr_id']?>');" class="next" ><span>다음글</span></a><? } ?>
	</div>
<article id="bo_v" class="theme-box" >

<?if($view['wr_protect']!=''){ 
	if( get_session("ss_secret_{$bo_table}_{$view['wr_num']}") ||  $view['mb_id'] && $view['mb_id']==$member['mb_id'] || $is_admin )
		$is_viewer = true;
	else {
	$is_viewer = false;?>
<div id="password_box">
		<p>
			<strong>보호글입니다.</strong>
			열람을 위해 비밀번호를 입력 해 주세요.
		</p>
		<div class="pass-form">

			<form name="fboardpassword" action="<?=$board_skin_url?>/password_check.php" method="post" id="fboardpass">
			<input type="hidden" name="w" value="p">
			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>"> 
			<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
			<input type="hidden" name="stx" value="<?php echo $stx ?>">
			<input type="hidden" name="page" value="<?php echo $page ?>">
			<fieldset class="box-pw">
				<input type="text" name="wr_password" id="password_wr_password" required class="frm_input required" size="15" maxlength="20">
				<input type="submit" value="확인" class="pass-btn btn_submit ui-btn">
			</fieldset>
			
			</form>

		</div>
</div>
	
<? }}else if(strstr($view['wr_option'],'secret')){
	if( !$view['mb_id'] && get_session("ss_secret_{$bo_table}_{$view['wr_num']}") ||  $view['mb_id'] && $view['mb_id']==$member['mb_id'] || $is_admin )
		$is_viewer = true;
	else {
	$is_viewer = false;
	if(!$view['mb_id']){?>
	<div id="password_box">
			<p class="txt-center">
				<strong>비밀글입니다.</strong>
				열람을 위해 비밀번호를 입력 해 주세요.
			</p>
			<div class="pass-form">

				<form name="fboardpassword" action="<?=$board_skin_url?>/password_check.php" id="fboardpass">
				<input type="hidden" name="w" value="s">
				<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
				<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>"> 
				<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
				<input type="hidden" name="stx" value="<?php echo $stx ?>">
				<input type="hidden" name="page" value="<?php echo $page ?>">
				<fieldset class="box-pw">
					<input type="password" name="wr_password" id="password_wr_password" required class="frm_input required" size="15" maxlength="20"> 
					<input type="submit" value="확인" class="pass-btn btn_submit ui-btn">
				</fieldset>
				
				</form>

			</div>
	</div>
	<?}else {?>
		<div id="password_box">
			<p class="txt-center" style="padding:30px 0;"><strong>비밀글입니다.</strong></p>
		</div>		
<?}
}}else if ($view['wr_secret'] && !$is_member){ 
		$is_viewer=false;?>
		<div id="password_box">
			<p class="txt-center" style="padding:30px 0;"><strong>멤버공개글입니다.</strong></p>
		</div>	
	<?
}else {if($board['bo_read_level']<=$member['mb_level'] ) $is_viewer=true;}
 if($is_viewer){?>
	<div class="bo_block">&nbsp;</div>

    <!-- 게시물 상단 버튼 시작 { -->
    <div id="bo_v_top"> 
        <ul class="bo_v_com"> 
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" >M</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">D</a></li><?php } ?>
        </ul> 
    </div>
    <!-- } 게시물 상단 버튼 끝 -->

    <section id="bo_v_atc" class="txt-center"> 
		<?php
        // 파일 출력 
		if($board['bo_use_dhtml_editor']){
		echo conv_content($view['wr_content'],2);
		}else{
		if ($view['wr_file']>0) {  /* view 인 경우 */ 
			$result = sql_query(" select * from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' order by bf_no ");
			for ($k=0;$file = sql_fetch_array($result);$k++)
			{
				$file_url=G5_DATA_URL."/file/".$file['bo_table']."/".$file['bf_file'];
			?>
				<p><img src="<?=$file_url?>"></p>				
			<?}
		}
		}
         ?> 
    </section>

<!-- 게시물 하단 영역 -->
	 <hr class="line">
    <section id="bo_v_info" class="txt-right"> 
        <h2 id="bo_v_title" >
            <span class="bo_v_tit txt-point">
            <?php
            echo $view['wr_subject']; // 글제목 출력
            ?></span>
        </h2>  
         <?php if ($board['bo_use_category'] && $view['ca_name']) { ?>
         <strong class="bo_v_cate"><?php echo $view['ca_name']; // 분류 출력 끝 ?></strong> 
         <?php } ?>
		 <strong></strong>
		<!--       
		<span class="sound_only">작성자</span> <strong><?php echo $view['wr_name'] ?></strong>
		-->
        <strong class="if_date"><span class="sound_only">작성일</span><?php echo date("Y/m/d", strtotime($view['wr_datetime'])) ?></strong>

    </section> 
<!-- 게시물 하단 영역 끝-->
<?}?>
</article>

<!-- } 게시글 읽기 끝 -->

<script>
	var w=document.documentElement.clientHeight-100;
	$("#bo_v").css('max-height',w+'px');
  
	$('#fboardpass').submit(function(e){
	e.preventDefault(); 
	var form = $(this);
	var post_url = form.attr('action');
	var post_data = form.serialize();
	$.ajax({
		type: 'POST',
		url: post_url,
		data:post_data,
		success: function(data) {
			// Toss
			var response = data;
			if(response>0) 
				$('#view_area').empty().load("<?=$board_skin_url?>/view.php?bo_table=<?=$bo_table?>&wr_id="+response);
			else $("#password_box").append(response);
		}
		, error: function(data, status, err) {
			
		}
		, complete: function() { 
			// Complete
		}
	});
	});
</script>