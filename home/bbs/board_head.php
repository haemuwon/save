<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 게시판 관리의 상단 내용
/*if (G5_IS_MOBILE) {
    // 모바일의 경우 설정을 따르지 않는다.
    include_once(G5_BBS_PATH.'/_head.php');
    echo stripslashes($board['bo_mobile_content_head']);
} else {*/

    if(is_include_path_check($board['bo_include_head'])) {  //파일경로 체크
        @include ($board['bo_include_head']);
    } else {    //파일경로가 올바르지 않으면 기본파일을 가져옴
        include_once(G5_BBS_PATH.'/_head.php');
    }
    //echo stripslashes($board['bo_content_head']);
//}

if($board['bo_use_pass'] ){
	$bo_key = md5($board['bo_pass']);
	$input_key = get_session('bo_pass_'.$board['bo_table']);
	if(!$is_admin && $bo_key != $input_key) { 
	goto_url('./password.php?w=bs&amp;bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page);
	
}
}
?>