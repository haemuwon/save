<?php
$sub_menu = "100320";
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

// 메뉴테이블 생성
if( !isset($g5['menu_table']) ){
    die('<meta charset="utf-8">dbconfig.php 파일에 <strong>$g5[\'menu_table\'] = G5_TABLE_PREFIX.\'menu\';</strong> 를 추가해 주세요.');
}
if(!sql_query(" DESCRIBE {$g5['menu_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['menu_table']}` (
                  `me_id` int(11) NOT NULL AUTO_INCREMENT,
                  `me_code` varchar(255) NOT NULL DEFAULT '',
                  `me_name` varchar(255) NOT NULL DEFAULT '',
                  `me_link` varchar(255) NOT NULL DEFAULT '',
                  `me_target` varchar(255) NOT NULL DEFAULT '0',
                  `me_order` int(11) NOT NULL DEFAULT '0',
                  `me_use` tinyint(4) NOT NULL DEFAULT '0',
                  `me_mobile_use` tinyint(4) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`me_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
}

$temp=sql_fetch("select * from {$g5['menu_table']}");
if (!isset($temp['me_level'])) {
	sql_query(" ALTER TABLE `{$g5['menu_table']}` ADD `me_level` int(11) NOT NULL DEFAULT '1' after `me_mobile_use`, ADD `me_img` varchar(255) NOT NULL DEFAULT '' after `me_name`", false);
}
if (!isset($temp['me_img2'])) {
	sql_query(" ALTER TABLE `{$g5['menu_table']}` ADD `me_img2` varchar(255) NOT NULL DEFAULT '' after `me_img`", false);
}

$sql = " select * from {$g5['menu_table']} order by me_order*1, me_id ";
$result = sql_query($sql);

$g5['title'] = "메뉴설정";
include_once('./admin.head.php');

$colspan = 11;
?>

<div class="local_desc01 local_desc">
    <p><strong>주의!</strong> 메뉴설정 작업 후 반드시 <strong>확인</strong>을 누르셔야 저장됩니다.</p>
</div>

<form name="fmenulist" id="fmenulist" method="post" action="./menu_list_update.php" onsubmit="return fmenulist_submit(this);" enctype="multipart/form-data">
<input type="hidden" name="token" value="">

<div class="btn_add01 btn_add">
    <button type="button" onclick="return add_menu();">메뉴추가<span class="sound_only"> 새창</span></button>
</div>

<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
	<colgroup>
		<col style="width:100px;">
		<col style="width:80px;">
		<col>
		<col style="width:80px;">
		<col>
		<col> 
		<col style="width:80px;">
		<col style="width:80px;">
		<col style="width:80px;">
		<col style="width:50px;">
		<col style="width:80px;">
	</colgroup>
    <thead>
    <tr>
        <th scope="col">메뉴</th>
		<th scope="col" colspan="2">이미지
			<?=help('* 사용하지 않을 경우 공란으로 남겨두시면 됩니다.')?></th>
		<th scope="col" colspan="2">마우스오버
			<?=help('* 사용하지 않을 경우 공란으로 남겨두시면 됩니다.')?></th>
        <th scope="col">링크</th>
        <th scope="col">새창</th>
        <th scope="col">순서</th>
        <th scope="col">권한</th> 
        <th scope="col">사용</th> 
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $bg = 'bg'.($i%2);
        $sub_menu_class = '';
        if(strlen($row['me_code']) == 4) {
            $sub_menu_class = ' sub_menu_class';
            $sub_menu_info = '<span class="sound_only">'.$row['me_name'].'의 서브</span>';
            $sub_menu_ico = '<span class="sub_menu_ico"></span>';
        }

        $search  = array('"', "'");
        $replace = array('&#034;', '&#039;');
        $me_name = str_replace($search, $replace, $row['me_name']);
    ?>
    <tr class="<?php echo $bg; ?> menu_list menu_group_<?php echo substr($row['me_code'], 0, 2); ?>">
        <td class="td_category<?php echo $sub_menu_class; ?>">
            <input type="hidden" name="code[]" value="<?php echo substr($row['me_code'], 0, 2) ?>"> 
            <input type="text" name="me_name[]" value="<?php echo $me_name; ?>" id="me_name_<?php echo $i; ?>" required class="required frm_input full_input" style="width:100%;">
        </td>
        <td>
            <?=$row['me_img'] ? '<img src="'.$row['me_img'].'" style="height:35px;">' : "-";?>
        </td>
        <td class="txt-left"> 
            <p>파일 <input type="file" name="me_img_file[]" " id="me_img_file_<?php echo $i; ?>" class=" frm_input full_input" style="width:80%"></p>
            <p>외부링크 <input type="text" name="me_img[]" value="<?php echo $row['me_img'] ?>" id="me_img_<?php echo $i; ?>" class=" frm_input full_input" style="width:80%"></p>
        </td>
        <td>
            <?=$row['me_img2'] ? '<img src="'.$row['me_img2'].'" style="height:35px;">' : "-";?>
        </td>
        <td class="txt-left"> 
            <p>파일 <input type="file" name="me_img2_file[]" " id="me_img2_file_<?php echo $i; ?>" class=" frm_input full_input" style="width:80%"></p>
            <p>외부링크 <input type="text" name="me_img2[]" value="<?php echo $row['me_img2'] ?>" id="me_img2_<?php echo $i; ?>" class=" frm_input full_input" style="width:80%"></p>
        </td>
        <td> 
            <input type="text" name="me_link[]" value="<?php echo $row['me_link'] ?>" id="me_link_<?php echo $i; ?>" required class="required frm_input full_input" style="width:100%;">
        </td>
        <td class="td_mng"> 
            <select name="me_target[]" id="me_target_<?php echo $i; ?>">
                <option value="self"<?php echo get_selected($row['me_target'], 'self', true); ?>>현재창</option>
                <option value="blank"<?php echo get_selected($row['me_target'], 'blank', true); ?>>새창</option> 
            </select>
        </td>
        <td class="td_num"> 
            <input type="text" name="me_order[]" value="<?php echo $row['me_order'] ?>" id="me_order_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num"> 
			<select name="me_level[]" id="me_level_<?php echo $i; ?>">
				<option value="1" <?=$row['me_level']=="1"? "selected":"";?> >1</option>
				<option value="2" <?=$row['me_level']=="2"? "selected":"";?> >2</option>
				<option value="3" <?=$row['me_level']=="3"? "selected":"";?> >3</option>
				<option value="4" <?=$row['me_level']=="4"? "selected":"";?> >4</option>
				<option value="5" <?=$row['me_level']=="5"? "selected":"";?> >5</option>
				<option value="6" <?=$row['me_level']=="6"? "selected":"";?> >6</option>
				<option value="7" <?=$row['me_level']=="7"? "selected":"";?> >7</option>
				<option value="8" <?=$row['me_level']=="8"? "selected":"";?> >8</option>
				<option value="9" <?=$row['me_level']=="9"? "selected":"";?> >9</option>
				<option value="10" <?=$row['me_level']=="10"? "selected":"";?> >10</option> 
			</select> 
        </td>
        <td class="td_mng"> <?// input -> select 박스로 변경 @221024?>
            <select name="me_use[]" id="me_use_<?=$i?>">
            <option value="1" <?=$row['me_use']==1 ? "selected":"";?>>사용</option>
            <option value="0" <?=$row['me_use']==0 ? "selected":"";?>>사용안함</option>
            </select>
        </td> 
        <td class="td_mng"> 
            <button type="button" class="btn_del_menu">삭제</button>
        </td>
    </tr>
    <?php
    }

    if ($i==0)
        echo '<tr id="empty_menu_list"><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" name="act_button" value="확인" class="btn_submit">
</div>

</form>

<script>
$(function() {
    $(document).on("click", ".btn_add_submenu", function() {
        var code = $(this).closest("tr").find("input[name='code[]']").val().substr(0, 2);
        add_submenu(code);
    });

    $(document).on("click", ".btn_del_menu", function() {
        if(!confirm("메뉴를 삭제하시겠습니까?"))
            return false;

        var $tr = $(this).closest("tr");
        if($tr.find("td.sub_menu_class").size() > 0) {
            $tr.remove();
        } else {
            var code = $(this).closest("tr").find("input[name='code[]']").val().substr(0, 2);
            $("tr.menu_group_"+code).remove();
        }

        if($("#menulist tr.menu_list").size() < 1) {
            var list = "<tr id=\"empty_menu_list\"><td colspan=\"<?php echo $colspan; ?>\" class=\"empty_table\">자료가 없습니다.</td></tr>\n";
            $("#menulist table tbody").append(list);
        } else {
            $("#menulist tr.menu_list").each(function(index) {
                $(this).removeClass("bg0 bg1")
                    .addClass("bg"+(index % 2));
            });
        }
    });
});

function add_menu()
{
    var max_code = base_convert(0, 10, 36);
    $("#menulist tr.menu_list").each(function() {
        var me_code = $(this).find("input[name='code[]']").val().substr(0, 2);
        if(max_code < me_code)
            max_code = me_code;
    });

    var url = "./menu_form.php?code="+max_code+"&new=new";
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function add_submenu(code)
{
    var url = "./menu_form.php?code="+code;
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function base_convert(number, frombase, tobase) {
  //  discuss at: http://phpjs.org/functions/base_convert/
  // original by: Philippe Baumann
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  //   example 1: base_convert('A37334', 16, 2);
  //   returns 1: '101000110111001100110100'

  return parseInt(number + '', frombase | 0)
    .toString(tobase | 0);
}

function fmenulist_submit(f)
{
    return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>
