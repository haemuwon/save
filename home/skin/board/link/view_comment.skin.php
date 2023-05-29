<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
 
	$cmt_link = sql_fetch("select count(wr_id) as cnt from {$write_table} where wr_is_comment=1 and wr_parent='{$wr_id}' and wr_content!='옷장'");
	$clo = sql_fetch("select count(wr_id) as cnt from {$write_table} where wr_is_comment=1 and wr_parent='{$wr_id}' and wr_content='옷장'");
?> 
<div class="board-comment-view">
<script>
// 글자수 제한
var char_min = parseInt(<? echo $comment_min ?>); // 최소
var char_max = parseInt(<? echo $comment_max ?>); // 최대
</script>
<?
if($member['mb_level']>=$board['bo_comment_level']){
?> 
<div class="board-comment-list">
<?if($cmt_link['cnt']>0){?> 
<strong>로그</strong><?}?>
	<?
	$cmt_amt = count($list);
	for ($i=0; $i<$cmt_amt; $i++) {
		if($list[$i]['wr_content']=='옷장') continue;
		$comment_id = $list[$i]['wr_id'];
		$cmt_depth = ""; // 댓글단계
		$cmt_depth = strlen($list[$i]['wr_comment_reply']) * 10;
		$comment = $list[$i]['content'];
		$comment2 = $list[$i]['content1'];

		$cmt_sv = $cmt_amt - $i + 1; // 댓글 헤더 z-index 재설정 ie8 이하 사이드뷰 겹침 문제 해결
	?> 
	<div class="item" id="c_<? echo $comment_id ?>"> 
		<div class="co-content">
			<div class="co-inner">
				<? $co_links=explode(PHP_EOL,$list[$i]['wr_content']); 
					if(count($co_links)>0){
						$co_list=array();
						for($k=0;$k<count($co_links);$k++){
							$co_list[$k]=explode('|',$co_links[$k]);
							$c_link='<a href="'.$co_list[$k][1].'" class="ui-btn" target="_blank">'.$co_list[$k][0].'</a>';
							echo $c_link;
						}
					}
				?>
			</div>

			<div class="co-info">  
				<? if($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) {
					$query_string = clean_query_string($_SERVER['QUERY_STRING']);

					if($w == 'cu') {
						$sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
						$cmt = sql_fetch($sql);
						if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id'])))
							$cmt['wr_content'] = '';
						$c_wr_content = $cmt['wr_content'];
					}
 
					$c_edit_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
				?> 
				<? if ($list[$i]['is_edit']) { ?><span><a href="javascript:comment_box('<? echo $comment_id ?>', 'cu','link');">m</a></span><? } ?>
				<? if ($list[$i]['is_del'])  { ?><span><a href="<? echo $list[$i]['del_link'];  ?>" onclick="return comment_delete();">d</a></span><? } ?>
				<? } ?>
			</div>

		</div>

			<span id="edit_<? echo $comment_id ?>"></span><!-- 수정 --> 
	</div> 

	<input type="hidden" value="<? echo strstr($list[$i]['wr_option'],"secret") ?>" id="secret_comment_<? echo $comment_id ?>">
	<textarea id="save_comment_<? echo $comment_id ?>" style="display:none"><? echo get_text($list[$i]['content1'], 0) ?></textarea>
	<input type="hidden" id="save_cl_<?=$comment_id?>" name="bf_content" >
	<input type="hidden" id="save_order_<?=$comment_id?>" name="wr_10">

	<? } ?>

</div>

<? } 
if ($view['mb_id']==$member['mb_id']) {
	if($w == '')
		$w = 'c';
?>

<div class="board-clothes-list board-comment-list"> 
<?if($clo['cnt']>0){?> 
	<strong>옷장</strong>
<?}?>
	<? 
	$clothes=sql_query("select * from {$write_table} where wr_content='옷장' and wr_parent='{$wr_id}' and wr_is_comment=1 order by wr_10 asc");
	for ($k=0;$cl=sql_fetch_array($clothes); $k++) {  
		$comment_id = $cl['wr_id'];  
	?> 
	<div class="item" id="cl_<? echo $comment_id ?>"> 
		<div class="co-content">
			<div class="co-inner">
				<?	$files=get_file($bo_table,$comment_id); 
					$filelink=G5_DATA_URL.'/file/'.$bo_table.'/'.$files[0]['file']; ?>
					<a href="<?=$filelink?>" target="_blank"><?=$files[0]['content']?></a> 

			</div>

			<div class="co-info">  
				<? if($member['mb_id']==$cl['mb_id']) {
					$query_string = clean_query_string($_SERVER['QUERY_STRING']);

					if($w == 'cu') {
						$sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
						$cmt = sql_fetch($sql);
						if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id'])))
							$cmt['wr_content'] = '';
						$c_wr_content = $cmt['wr_content'];
					}
				  $token = '';

					if ($member['mb_id'])
					{
						if ($cl['mb_id'] === $member['mb_id'] || $is_admin)
						{
							set_session('ss_delete_comment_'.$cl['wr_id'].'_token', $token = uniqid(time()));
							$cl['del_link']  = './delete_comment.php?bo_table='.$bo_table.'&amp;comment_id='.$cl['wr_id'].'&amp;token='.$token.'&amp;page='.$page.$qstr; 
						}
					}
					else
					{
						if (!$cl['mb_id']) {
							$cl['del_link'] = './password.php?w=x&amp;bo_table='.$bo_table.'&amp;comment_id='.$cl['wr_id'].'&amp;page='.$page.$qstr; 
						}
					}
					$c_edit_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_cl_w'; 

				?> 
				 <span><a href="javascript:comment_box('<? echo $comment_id ?>', 'cu', 'clo');">m</a></span> 
				 <span><a href="<? echo $cl['del_link'];  ?>" onclick="return comment_delete();">d</a></span> 
				<? } ?>
			</div>
 
		</div>

			<span id="edit_cl_<? echo $comment_id ?>"></span><!-- 수정 -->
	</div>
	 
	<input type="hidden" value="<? echo strstr($cl['wr_option'],"secret") ?>" id="secret_comment_<? echo $comment_id ?>">
	<textarea id="save_comment_<? echo $comment_id ?>" style="display:none">옷장</textarea>
	<input type="hidden" id="save_cl_<?=$comment_id?>" name="bf_content" value="<?=$files[0]['content']?>" >
	<input type="hidden" id="save_order_<?=$comment_id?>" name="wr_10" value="<?=$cl['wr_10']?>">
	<? } ?>

</div>
<!-- 댓글 쓰기 시작 { --> 
<?if($cmt_link['cnt']==0)	{?><p class="txt-left add"><a href="javascript:comment_box('', 'c','link');">▶ 로그 링크 추가</a></p><?}?>
<p class="add txt-right"><a href="javascript:comment_box('', 'c','clo');">▶ 의상 추가</a></p> 
<div id="bo_vc_w" class="board-comment-write" style="display:none;">
	<form name="fviewcomment" action="./write_comment_update.php" onsubmit="return fviewcomment_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="w" value="<? echo $w ?>" id="w">
		<input type="hidden" name="bo_table" value="<? echo $bo_table ?>">
		<input type="hidden" name="wr_id" value="<? echo $wr_id ?>">
		<input type="hidden" name="comment_id" value="<? echo $c_id ?>" id="comment_id">
		<input type="hidden" name="sca" value="<? echo $sca ?>">
		<input type="hidden" name="sfl" value="<? echo $sfl ?>">
		<input type="hidden" name="stx" value="<? echo $stx ?>">
		<input type="hidden" name="spt" value="<? echo $spt ?>">
		<input type="hidden" name="page" value="<? echo $page ?>"> 
		
		<div class="board-comment-form">
		
		<input type="text" name="bf_content" id="bf_content" placeholder="의상 이름" style="width:80%"><input type="text" name="wr_10" id="wr_10" placeholder="순서" style="width:20%;"><input type="file" name="bf_file" id="bf_file" class="frm_file frm_input full upload-data on"> 
	<? if ($comment_min || $comment_max) { ?><strong id="char_cnt"><span id="char_count"></span>글자</strong><? } ?>
			<textarea id="wr_content" name="wr_content" maxlength="10000" required class="required" placeholder="제목|링크로 로그를 링크할 수 있습니다. 여러개 입력시 엔터로 구분해주세요. 예시:
링크1|http://url-1
링크2|http://url-2
링크3|http://url-3
..."
			<? if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<? } ?>><? echo $c_wr_content;  ?></textarea>
			<? if ($comment_min || $comment_max) { ?><script> check_byte('wr_content', 'char_count'); </script><? } ?>
			<script>
			$(document).on( "keyup change", "textarea#wr_content[maxlength]", function(){
				var str = $(this).val()
				var mx = parseInt($(this).attr("maxlength"))
				if (str.length > mx) {
					$(this).val(str.substr(0, mx));
					return false;
				}
			});
			</script>

			<p style="display:none;"><input type="checkbox" name="secret" value="1" id="secret"> <label for="secret">비밀글</label></p>

			<div class="btn_confirm">
				<button type="submit" id="btn_submit" class="ui-btn">등록</button>
			</div>
		</div>
		
	</form>
</div> 
<? } ?>

</div>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;
 

function fviewcomment_submit(f)
{
	var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자 

	var subject = "";
	var content = "";
	$.ajax({
		url: g5_bbs_url+"/ajax.filter.php",
		type: "POST",
		data: {
			"subject": "",
			"content": f.wr_content.value
		},
		dataType: "json",
		async: false,
		cache: false,
		success: function(data, textStatus) {
			subject = data.subject;
			content = data.content;
		}
	});

	if (content) {
		alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
		f.wr_content.focus();
		return false;
	}

	// 양쪽 공백 없애기
	var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
	document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
	if (char_min > 0 || char_max > 0)
	{
		check_byte('wr_content', 'char_count');
		var cnt = parseInt(document.getElementById('char_count').innerHTML);
		if (char_min > 0 && char_min > cnt)
		{
			alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
			return false;
		} else if (char_max > 0 && char_max < cnt)
		{
			alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
			return false;
		}
	}
	else if (!document.getElementById('wr_content').value)
	{
		alert("댓글을 입력하여 주십시오.");
		return false;
	}

	if (typeof(f.wr_name) != 'undefined')
	{
		f.wr_name.value = f.wr_name.value.replace(pattern, "");
		if (f.wr_name.value == '')
		{
			alert('이름이 입력되지 않았습니다.');
			f.wr_name.focus();
			return false;
		}
	}

	if (typeof(f.wr_password) != 'undefined')
	{
		f.wr_password.value = f.wr_password.value.replace(pattern, "");
		if (f.wr_password.value == '')
		{
			alert('비밀번호가 입력되지 않았습니다.');
			f.wr_password.focus();
			return false;
		}
	}
 

	set_comment_token(f);

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}

function comment_box(comment_id, work, type)
{
	var el_id;
	// 댓글 아이디가 넘어오면 답변, 수정
	if (comment_id)
	{
		if (work == 'c')
			el_id = 'reply_' + comment_id;
		else{
			if(type=='clo')
				el_id = 'edit_cl_' + comment_id;
			else
				el_id = 'edit_' + comment_id;
		}
	}
	else
		el_id = 'bo_vc_w';

	if (save_before != el_id)
	{
		if (save_before)
		{
			document.getElementById(save_before).style.display = 'none';
			document.getElementById(save_before).innerHTML = ''; 
		}

		document.getElementById(el_id).style.display = '';
		document.getElementById(el_id).innerHTML = save_html; 
			
		// 댓글 수정
		if (work == 'cu')
		{
			$('#btn_submit').text('수정');
			document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
			if(type=='clo'){
			document.getElementById('wr_content').value = '옷장';
			document.getElementById('bf_content').value = document.getElementById('save_cl_' + comment_id).value;
			document.getElementById('wr_10').value = document.getElementById('save_order_'+comment_id).value;
			}
			if (typeof char_count != 'undefined')
				check_byte('wr_content', 'char_count');
			if (document.getElementById('secret_comment_'+comment_id).value)
				document.getElementById('secret').checked = true;
			else
				document.getElementById('secret').checked = false;
		}

		document.getElementById('comment_id').value = comment_id;
		document.getElementById('w').value = work;

		save_before = el_id;
	}
	if(type=='clo'){
		$('#'+el_id+' #wr_content').hide();
		$('#'+el_id+' #bf_content, #'+el_id+' #bf_file, #'+el_id+' #wr_10').show();
		if(work=='c'){
			$('#'+el_id+' #wr_content').text('옷장');
		}
	}
	if (type=='link')
	{	
		if(work=='c'){
			$('#'+el_id+' #wr_content').text('');
		}
		$('#'+el_id+' #bf_content, #'+el_id+' #bf_file, #'+el_id+' #wr_10').hide();
		$('#'+el_id+' #wr_content').show();
	}
}

function comment_delete()
{
	return confirm("이 댓글을 삭제하시겠습니까?");
}

 // 댓글 입력폼이 보이도록 처리하기위해서 추가 (root님)

<? if($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
// sns 등록
$(function() {
	$("#bo_vc_send_sns").load(
		"<? echo G5_SNS_URL; ?>/view_comment_write.sns.skin.php?bo_table=<? echo $bo_table; ?>",
		function() {
			save_html = document.getElementById('bo_vc_w').innerHTML;
		}
	);
});
<? } ?>
</script>