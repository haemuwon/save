<?php
include_once('./_common.php');

$g5['title'] = '비밀번호 입력';

switch ($w) {
    case 'u' :
        $action = G5_HTTP_BBS_URL.'/write.php';
        $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        break;
    case 'd' :
        set_session('ss_delete_token', $token = uniqid(time()));
        $action = https_url(G5_BBS_DIR).'/delete.php?token='.$token;
        $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        break;
    case 'x' :
        set_session('ss_delete_comment_'.$comment_id.'_token', $token = uniqid(time()));
        $action = https_url(G5_BBS_DIR).'/delete_comment.php?token='.$token;
        $row = sql_fetch(" select wr_parent from $write_table where wr_id = '$comment_id' ");
        $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$row['wr_parent'];
        break;
    case 's' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else {
            $action = https_url(G5_BBS_DIR).'/password_check.php';
            $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table;
        }
        break;
    case 'p' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else {
            $action = https_url(G5_BBS_DIR).'/password_check.php';
            $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table;
        }
        break;
    case 'sc' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else {
            $action = https_url(G5_BBS_DIR).'/password_check.php';
            $return_url = G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        }
        break;
	case 'bs' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin)
            goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
        else {
            $action = https_url(G5_BBS_DIR).'/password_check.php';
            $return_url = G5_URL;
        }
        break;
    default :
        alert('w 값이 제대로 넘어오지 않았습니다.');
}

include_once(G5_PATH.'/head.sub.php');

//if ($board['bo_include_head'] && is_include_path_check($board['bo_content_head'])) { @include ($board['bo_include_head']); }
//if ($board['bo_content_head']) { echo html_purifier(stripslashes($board['bo_content_head'])); }

/* 비밀글의 제목을 가져옴 지운아빠 2013-01-29 */
$sql = " select wr_subject from {$write_table}
                      where wr_num = '{$write['wr_num']}'
                      and wr_reply = ''
                      and wr_is_comment = 0 ";
$row = sql_fetch($sql);

$g5['title'] = get_text($row['wr_subject']);
$delete_str = "";
if ($w == 'x') $delete_str = "댓";
if ($w == 'u') $g5['title'] = $delete_str."글 수정";
else if ($w == 'd' || $w == 'x') $g5['title'] = $delete_str."글 삭제";
else $g5['title'] = $g5['title'];

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/style.basic.css">', 0);
?>

<!-- 비밀번호 확인 시작 { -->
<div id="password_box">

	<h1 class="ui-btn point">비밀번호 확인</h1>

	<div class="descript theme-box">
		<p>
			<?php if ($w == 'u') { ?>
			<strong>작성자만 글을 수정할 수 있습니다.</strong>
			작성자 본인이라면, 글 작성시 입력한 비밀번호를 입력하여 글을 수정할 수 있습니다.
			<?php } else if ($w == 'd' || $w == 'x') {  ?>
			<strong>작성자만 글을 삭제할 수 있습니다.</strong>
			작성자 본인이라면, 글 작성시 입력한 비밀번호를 입력하여 글을 삭제할 수 있습니다.
			<?php } else if($w=='bs'){
			
			}else if($w=='p'){?> 
			<strong>보호글입니다.</strong>
			열람을 위해 비밀번호를 입력 해 주세요.
			<?} else {  ?>
			<strong>비밀글 기능으로 보호된 글입니다.</strong>
			작성자와 관리자만 열람하실 수 있습니다. 본인이라면 비밀번호를 입력하세요.
			<?php }  ?>
		</p>
	

		<div class="pass-form">

			<form name="fboardpassword" action="<?php echo $action;  ?>" method="post">
			<input type="hidden" name="w" value="<?php echo $w ?>">
			<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
			<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
			<input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
			<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
			<input type="hidden" name="stx" value="<?php echo $stx ?>">
			<input type="hidden" name="page" value="<?php echo $page ?>">
			<fieldset class="box-pw">
				<?if($w=='p'){?>
				<input type="text" name="wr_password" id="password_wr_password" required class="frm_input required" size="15" maxlength="20">
				<?}else {?>
				<input type="password" name="wr_password" id="password_wr_password" required class="frm_input required" size="15" maxlength="20">
				<?}?>
			</fieldset>

			<fieldset class="box-btn">
				<input type="submit" value="확인" class="btn_submit ui-btn">
			</fieldset>
			
			</form>

		</div>
	</div>

	<div class="btn_confirm">
		<a href="<?php echo $return_url ?>" class="ui-btn">이전으로 돌아가기</a>
	</div>

</div>
<!-- } 비밀번호 확인 끝 --><? 

//if ($board['bo_content_tail']) { echo html_purifier(stripslashes($board['bo_content_tail'])); }
//if ($board['bo_include_tail'] && is_include_path_check($board['bo_content_tail'])) { @include ($board['bo_include_tail']); }

include_once(G5_PATH.'/tail.sub.php');
?>
