<?php
include_once('./_common.php');
$write_table=$g5['write_prefix'].$bo_table;
if ($w == 's') {
    $qstr = 'bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page;

    $wr = get_write($write_table, $wr_id);

    if (!check_password($wr_password, $wr['wr_password'])){
        echo help('비밀번호가 틀립니다.');
		$is_viewer=false;
	} else {

    // 세션에 아래 정보를 저장. 하위번호는 비밀번호없이 보아야 하기 때문임. 
    $ss_name = 'ss_secret_'.$bo_table.'_'.$wr['wr_num']; 
    set_session($ss_name, TRUE);
	$is_viewer=true;
	echo $wr_id;
	}

} else if ($w == 'p') {
    $qstr = 'bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page;

    $wr = get_write($write_table, $wr_id);
	
    if ($wr_password!=$wr['wr_protect']){
        echo help('비밀번호가 틀립니다.');
		$is_viewer=false;
	} else {

    // 세션에 아래 정보를 저장. 하위번호는 비밀번호없이 보아야 하기 때문임. 
    $ss_name = 'ss_secret_'.$bo_table.'_'.$wr['wr_num']; 
    set_session($ss_name, TRUE);
	$is_viewer=true;
	echo $wr_id;
	}
} else
    echo help('w 값이 제대로 넘어오지 않았습니다.');

?>
