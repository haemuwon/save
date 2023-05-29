<?php
include_once('./_common.php');

$g5['title'] = '관리자메인';
include_once ('./admin.head.php');
 
	$total=sql_fetch("select count(*) as cnt from {$g5['board_table']}");
	$total_count=$total['cnt'];
	$sql="select * from {$g5['board_table']} order by bo_table asc";
	$result=sql_query($sql);
	$colspan=9;

?>
<section>
	<h2>게시판 목록</h2>
<div class="btn_add01 btn_add">
	<a href="./board_list.php">게시판 관리 상세</a>
<?php if ($is_admin == 'super') { ?>
	<a href="./board_form.php" id="bo_add">게시판 추가</a>
<?php } ?>
</div> 
	<div class="local_desc02 local_desc">
		생성된 게시판수 <?php echo number_format($total_count) ?>개
	</div>

	<div class="tbl_head01 tbl_wrap">
		<table>
		<caption>게시판목록</caption>
		<thead>
		<tr>
			<th scope="col">TABLE</th>
			<th scope="col">스킨</th>
			<th scope="col">제목</th>
			<th scope="col">목록권한</th>
			<th scope="col">읽기권한</th>
			<th scope="col">쓰기권한</th>
			<th scope="col">댓글권한</th>  
			<th scope="col">관리</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=0; $row=sql_fetch_array($result); $i++) { 
			$one_update = '<a href="./board_form.php?w=u&amp;bo_table='.$row['bo_table'].'&amp;'.$qstr.'">수정</a>';
			$bg = 'bg'.($i%2);
		?>
		
	<tr class="<?php echo $bg; ?>">
		<td>
			<input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $row['bo_table'] ?>">
			<a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $row['bo_table'] ?>"><?php echo $row['bo_table'] ?></a>
		</td>
		<td> 
			<?=$row['bo_skin']?>
		</td>
		<td>
			<?=$row['bo_subject']?>
		</td>
		<td> 
			<?=$row['bo_list_level']?>
		</td>
		<td>
			<?=$row['bo_read_level']?> 
		</td>
		<td>
			<?=$row['bo_write_level']?>
		</td>
		<td>
			<?=$row['bo_comment_level']?>
		</td>
		<td>
			<?php echo $one_update ?> 
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
	?>
		</table>
	</div>

</section>

<?
	$total_c=sql_fetch("select count(*) as cnt from {$g5['content_table']}");
	$total_c_count=$total_c['cnt'];
	$sql_c="select * from {$g5['content_table']} order by co_id asc";
	$result_c=sql_query($sql_c);
	$colspan_c=3;

?>
<section>
	<h2>페이지 목록</h2>
<div class="btn_add01 btn_add">
	<a href="./contentlist.php">페이지 관리 상세</a>
<?php if ($is_admin == 'super') { ?>
	<a href="./contentform.php" id="bo_add">페이지 추가</a>
<?php } ?>
</div> 
	<div class="local_desc02 local_desc">
		생성된 게시판수 <?php echo number_format($total_c_count) ?>개
	</div>

	<div class="tbl_head01 tbl_wrap">
		<table>
		<caption>게시판목록</caption>
		<colgroup>
			<col>
			<col>
			<col style="width:80px;">
		</colgroup>
		<thead>
		<tr>
			<th scope="col">ID</th> 
			<th scope="col">제목</th>
			<th scope="col">관리</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=0; $row=sql_fetch_array($result_c); $i++) { 
			$co_update = '<a href="./contentform.php?w=u&amp;co_id='.$row['co_id'].'&amp;'.$qstr.'">수정</a>';
			$bg = 'bg'.($i%2);
		?>
		
	<tr class="<?php echo $bg; ?>">
		<td>
			<a href="<?php echo G5_BBS_URL ?>/content.php?co_id=<?php echo $row['co_id'] ?>"><?php echo $row['co_id'] ?></a>
		</td>
		<td> 
			<?=$row['co_subject']?>
		</td> 
		<td>
			<?php echo $co_update ?> 
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
	?>
		</table>
	</div>

</section>

<?
$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (1) ";

if ($is_admin != 'super')
	$sql_search .= " and mb_level <= '{$member['mb_level']}' ";

if (!$sst) {
	$sst = "mb_datetime";
	$sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$new_member_rows} ";
$result = sql_query($sql);

$colspan = 12;
?>
 
<section>
	<h2>신규가입회원 <?php echo $new_member_rows ?>건 목록</h2>
	<div class="btn_add01 btn_add">
		<a href="./member_list.php">회원 전체보기</a>
	</div>
	<div class="local_desc02 local_desc">
		총회원수 <?php echo number_format($total_count) ?>명 중 차단 <?php echo number_format($intercept_count) ?>명, 탈퇴 : <?php echo number_format($leave_count) ?>명
	</div>

	<div class="tbl_head01 tbl_wrap">
		<table>
		<caption>신규가입회원</caption>
		<thead>
		<tr>
			<th scope="col">회원아이디</th>
			<th scope="col">이름</th>
			<th scope="col">닉네임</th>
			<th scope="col">권한</th> 
			<th scope="col">수신</th>
			<th scope="col">공개</th>
			<th scope="col">인증</th>
			<th scope="col">차단</th>
			<th scope="col">그룹</th>
		</tr>
		</thead>
		<tbody>
		<?php
		for ($i=0; $row=sql_fetch_array($result); $i++)
		{
			// 접근가능한 그룹수
			$sql2 = " select count(*) as cnt from {$g5['group_member_table']} where mb_id = '{$row['mb_id']}' ";
			$row2 = sql_fetch($sql2);
			$group = "";
			if ($row2['cnt'])
				$group = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">'.$row2['cnt'].'</a>';

			if ($is_admin == 'group')
			{
				$s_mod = '';
				$s_del = '';
			}
			else
			{
				$s_mod = '<a href="./member_form.php?$qstr&amp;w=u&amp;mb_id='.$row['mb_id'].'">수정</a>';
				$s_del = '<a href="./member_delete.php?'.$qstr.'&amp;w=d&amp;mb_id='.$row['mb_id'].'&amp;url='.$_SERVER['SCRIPT_NAME'].'" onclick="return delete_confirm(this);">삭제</a>';
			}
			$s_grp = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">그룹</a>';

			$leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date("Ymd", G5_SERVER_TIME);
			$intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date("Ymd", G5_SERVER_TIME);

			$mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

			$mb_id = $row['mb_id'];
			if ($row['mb_leave_date'])
				$mb_id = $mb_id;
			else if ($row['mb_intercept_date'])
				$mb_id = $mb_id;

		?>
		<tr>
			<td class="td_mbid"><?php echo $mb_id ?></td>
			<td class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
			<td class="td_mbname sv_use"><div><?php echo $mb_nick ?></div></td>
			<td class="td_num"><?php echo $row['mb_level'] ?></td> 
			<td class="td_boolean"><?php echo $row['mb_mailling']?'예':'아니오'; ?></td>
			<td class="td_boolean"><?php echo $row['mb_open']?'예':'아니오'; ?></td>
			<td class="td_boolean"><?php echo preg_match('/[1-9]/', $row['mb_email_certify'])?'예':'아니오'; ?></td>
			<td class="td_boolean"><?php echo $row['mb_intercept_date']?'예':'아니오'; ?></td>
			<td class="td_category"><?php echo $group ?></td>
		</tr>
		<?php
			}
		if ($i == 0)
			echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
		?>
		</tbody>
		</table>
	</div>

	

</section>

 

<?php
include_once ('./admin.tail.php');
?>
