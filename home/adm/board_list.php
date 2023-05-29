<?php
$sub_menu = "200200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g5['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin != "super") {
	$sql_common .= " , {$g5['group_table']} b ";
	$sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '{$member['mb_id']}') ";
}

if ($stx) {
	$sql_search .= " and ( ";
	switch ($sfl) {
		case "bo_table" :
			$sql_search .= " ($sfl like '$stx%') ";
			break;
		case "a.gr_id" :
			$sql_search .= " ($sfl = '$stx') ";
			break;
		default :
			$sql_search .= " ($sfl like '%$stx%') ";
			break;
	}
	$sql_search .= " ) ";
}

if (!$sst) {
	$sst  = "a.gr_id, a.bo_table";
	$sod = "asc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '게시판관리';
include_once('./admin.head.php');

$colspan = 10;
 
?>

<div class="local_ov01 local_ov">
	<?php echo $listall ?>
	생성된 게시판수 <?php echo number_format($total_count) ?>개
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="bo_table"<?php echo get_selected($_GET['sfl'], "bo_table", true); ?>>TABLE</option>
	<option value="bo_subject"<?php echo get_selected($_GET['sfl'], "bo_subject"); ?>>제목</option>
	<option value="a.gr_id"<?php echo get_selected($_GET['sfl'], "a.gr_id"); ?>>그룹ID</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<?php if ($is_admin == 'super') { ?>
<div class="btn_add01 btn_add">
	<a href="./board_form.php" id="bo_add">게시판 추가</a>
</div>
<?php } ?>

<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">

<div class="tbl_head01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr>
		<th scope="col">
			<label for="chkall" class="sound_only">게시판 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th> 
		<th scope="col"><?php echo subject_sort_link('bo_table') ?>TABLE</a></th>
		<th scope="col"><?php echo subject_sort_link('bo_skin', '', 'desc') ?>스킨</a></th>
		<th scope="col"><?php echo subject_sort_link('bo_subject') ?>제목</a></th>
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
		$one_copy = '<a href="./board_copy.php?bo_table='.$row['bo_table'].'" class="board_copy" target="win_board_copy">복사</a>';

		$bg = 'bg'.($i%2);
	?>

	<tr class="<?php echo $bg; ?>">
		<td>
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bo_subject']) ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td> 
		<td>
			<input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $row['bo_table'] ?>">
			<a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $row['bo_table'] ?>"><?php echo $row['bo_table'] ?></a>
		</td>
		<td>
			<label for="bo_skin_<?php echo $i; ?>" class="sound_only">스킨</label>
			<?php echo get_skin_select('board', 'bo_skin_'.$i, "bo_skin[$i]", $row['bo_skin']); ?>
		</td>
		<td>
			<label for="bo_subject_<?php echo $i; ?>" class="sound_only">게시판 제목<strong class="sound_only"> 필수</strong></label>
			<input type="text" name="bo_subject[<?php echo $i ?>]" value="<?php echo get_text($row['bo_subject']) ?>" id="bo_subject_<?php echo $i ?>" required class="required frm_input bo_subject full_input" size="25">
		</td>
		<td> 
			<select name="bo_list_level[<?=$i?>]">
				<option value="1" <?=$row['bo_list_level']=="1"? "selected":"";?> >1</option>
				<option value="2" <?=$row['bo_list_level']=="2"? "selected":"";?> >2</option>
				<option value="3" <?=$row['bo_list_level']=="3"? "selected":"";?> >3</option>
				<option value="4" <?=$row['bo_list_level']=="4"? "selected":"";?> >4</option>
				<option value="5" <?=$row['bo_list_level']=="5"? "selected":"";?> >5</option>
				<option value="6" <?=$row['bo_list_level']=="6"? "selected":"";?> >6</option>
				<option value="7" <?=$row['bo_list_level']=="7"? "selected":"";?> >7</option>
				<option value="8" <?=$row['bo_list_level']=="8"? "selected":"";?> >8</option>
				<option value="9" <?=$row['bo_list_level']=="9"? "selected":"";?> >9</option>
				<option value="10" <?=$row['bo_list_level']=="10"? "selected":"";?> >10</option> 
			</select> 
		</td>
		<td>
			
			<label for="bo_write_point_<?php echo $i; ?>" class="sound_only">읽기권한</label>
			<select name="bo_read_level[<?=$i?>]">
				<option value="1" <?=$row['bo_read_level']=="1"? "selected":"";?> >1</option>
				<option value="2" <?=$row['bo_read_level']=="2"? "selected":"";?> >2</option>
				<option value="3" <?=$row['bo_read_level']=="3"? "selected":"";?> >3</option>
				<option value="4" <?=$row['bo_read_level']=="4"? "selected":"";?> >4</option>
				<option value="5" <?=$row['bo_read_level']=="5"? "selected":"";?> >5</option>
				<option value="6" <?=$row['bo_read_level']=="6"? "selected":"";?> >6</option>
				<option value="7" <?=$row['bo_read_level']=="7"? "selected":"";?> >7</option>
				<option value="8" <?=$row['bo_read_level']=="8"? "selected":"";?> >8</option>
				<option value="9" <?=$row['bo_read_level']=="9"? "selected":"";?> >9</option>
				<option value="10" <?=$row['bo_read_level']=="10"? "selected":"";?> >10</option> 
			</select> 
		</td>
		<td>
			<label for="bo_write_level_<?php echo $i; ?>" class="sound_only">쓰기권한</label>
			<select name="bo_write_level[<?=$i?>]">
				<option value="1" <?=$row['bo_write_level']=="1"? "selected":"";?> >1</option>
				<option value="2" <?=$row['bo_write_level']=="2"? "selected":"";?> >2</option>
				<option value="3" <?=$row['bo_write_level']=="3"? "selected":"";?> >3</option>
				<option value="4" <?=$row['bo_write_level']=="4"? "selected":"";?> >4</option>
				<option value="5" <?=$row['bo_write_level']=="5"? "selected":"";?> >5</option>
				<option value="6" <?=$row['bo_write_level']=="6"? "selected":"";?> >6</option>
				<option value="7" <?=$row['bo_write_level']=="7"? "selected":"";?> >7</option>
				<option value="8" <?=$row['bo_write_level']=="8"? "selected":"";?> >8</option>
				<option value="9" <?=$row['bo_write_level']=="9"? "selected":"";?> >9</option>
				<option value="10" <?=$row['bo_write_level']=="10"? "selected":"";?> >10</option> 
			</select> 
		</td>
		<td>
			<label for="bo_comment_level_<?php echo $i; ?>" class="sound_only">댓글권한</label>
			<select name="bo_comment_level[<?=$i?>]">
				<option value="1" <?=$row['bo_comment_level']=="1"? "selected":"";?> >1</option>
				<option value="2" <?=$row['bo_comment_level']=="2"? "selected":"";?> >2</option>
				<option value="3" <?=$row['bo_comment_level']=="3"? "selected":"";?> >3</option>
				<option value="4" <?=$row['bo_comment_level']=="4"? "selected":"";?> >4</option>
				<option value="5" <?=$row['bo_comment_level']=="5"? "selected":"";?> >5</option>
				<option value="6" <?=$row['bo_comment_level']=="6"? "selected":"";?> >6</option>
				<option value="7" <?=$row['bo_comment_level']=="7"? "selected":"";?> >7</option>
				<option value="8" <?=$row['bo_comment_level']=="8"? "selected":"";?> >8</option>
				<option value="9" <?=$row['bo_comment_level']=="9"? "selected":"";?> >9</option>
				<option value="10" <?=$row['bo_comment_level']=="10"? "selected":"";?> >10</option> 
			</select> 
		</td>
		<td>
			<?php echo $one_update ?>
			<?php echo $one_copy ?>
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_list01 btn_list">
	<input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value">
	<?php if ($is_admin == 'super') { ?>
	<input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
	<?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?>

<script>
function fboardlist_submit(f)
{
	if (!is_checked("chk[]")) {
		alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
		return false;
	}

	if(document.pressed == "선택삭제") {
		if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
			return false;
		}
	}

	return true;
}

$(function(){
	$(".board_copy").click(function(){
		window.open(this.href, "win_board_copy", "left=100,top=100,width=550,height=450");
		return false;
	});
});
</script>

<?php
include_once('./admin.tail.php');
?>
