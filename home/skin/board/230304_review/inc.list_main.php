<?
// 답변 링크
$reply_href = '';
if ($member['mb_level'] >= $board['bo_reply_level'])
    $reply_href = './write.php?w=r&amp;bo_table=' . $bo_table . '&amp;wr_id=' . $list[$i]['wr_id'] . $qstr;
// 수정, 삭제 링크
$update_href = $delete_href = "";
// 로그인중이고 자신의 글이라면 또는 관리자라면 패스워드를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] == $list[$i]['mb_id'])) || $is_admin) {
    $update_href = "./write.php?w=u&bo_table=$bo_table&wr_id={$list[$i]['wr_id']}&page=$page" . $qstr;
    $delete_href = "./delete.php?bo_table=$bo_table&wr_id={$list[$i]['wr_id']}&page=$page" . urldecode($qstr) . "";
    if ($is_admin) {
        $delete_href = "./delete.php?bo_table=$bo_table&wr_id={$list[$i]['wr_id']}&token=$token&page=$page" . urldecode($qstr) . "";
    }
} else if (!$list[$i]['mb_id']) { // 회원이 쓴 글이 아니라면
    $update_href = "./password.php?w=u&bo_table=$bo_table&wr_id={$list[$i]['wr_id']}&page=$page" . $qstr;
    $delete_href = "./password.php?w=d&bo_table=$bo_table&wr_id={$list[$i]['wr_id']}&page=$page" . $qstr;
}
