<?php
$sub_menu = "100330";
include_once('./_common.php');

if ($is_admin != 'super')
    alert_close('최고관리자만 접근 가능합니다.');

// 폰트테이블 생성
if( !isset($g5['font_table']) ){
    die('<meta charset="utf-8">data/dbconfig.php 파일에 <strong>$g5[\'font_table\'] = G5_TABLE_PREFIX.\'font\';</strong> 를 추가해 주세요.');
}
if(!sql_query(" DESCRIBE {$g5['font_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['font_table']}` (
                `ft_id` int(11) NOT NULL AUTO_INCREMENT,
                `ft_type` varchar(255) NOT NULL DEFAULT '',
                `ft_code` varchar(255) NOT NULL DEFAULT '',
                `ft_group` varchar(255) NOT NULL DEFAULT '',
                `ft_name` varchar(255) NOT NULL DEFAULT '',
                `ft_option` varchar(255) NOT NULL DEFAULT '',
                `ft_src` varchar(255) NOT NULL DEFAULT '',
                `ft_file` varchar(255) NOT NULL DEFAULT '',
                `ft_exp` varchar(255) NOT NULL DEFAULT '',
                `ft_weight` varchar(255) NOT NULL DEFAULT '',
                `ft_style` varchar(255) NOT NULL DEFAULT '',
                `ft_order` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`ft_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 collate=utf8mb4_unicode_ci ", true);
}
$temp=sql_fetch("select * from {$g5['font_table']}");
if (!isset($temp['ft_file'])) {
	sql_query(" ALTER TABLE `{$g5['font_table']}` ADD `ft_file` varchar(255) NOT NULL DEFAULT '' after `ft_src`", false);
}
if (!isset($temp['ft_exp'])) {
	sql_query(" ALTER TABLE `{$g5['font_table']}` ADD `ft_exp` varchar(255) NOT NULL DEFAULT '' after `ft_file`", false);
}
if (!isset($temp['ft_weight'])) {
	sql_query(" ALTER TABLE `{$g5['font_table']}` ADD `ft_weight` varchar(255) NOT NULL DEFAULT '' after `ft_exp`", false);
}
if (!isset($temp['ft_style'])) {
	sql_query(" ALTER TABLE `{$g5['font_table']}` ADD `ft_style` varchar(255) NOT NULL DEFAULT '' after `ft_weight`", false);
}
// PK
// GROUP CODE
// GROUP or FONT
// GROUP NAME
// FONT NAME
// SRC or URL
// ORDER

$sql = " select * from {$g5['font_table']} order by ft_order*1, ft_code*1, ft_id ";
$result = sql_query($sql);

$g5['title'] = "폰트설정";
include_once('./admin.head.php');

$colspan = 5;
$_weight = array('normal','bold','100','200','300','400','500','600','700','800','900','inherit','initial','unset'); 
$_style = array('normal', 'italic'); 
$sample_txt = 'Font미리보기0123';
?>

<!-- STYLE -->
<style>
/* SAMPLE TEXT INPUT BOX */
#fontform                         { position:relative; }
.sample_input                     { position:absolute; }
.sample_input #sample_txt         { width:300px;height:33px!important; }
.sample_input #sample_size        { width:50px;height:33px!important; }

/* TABLE STYLE */
#fontlist input.w100, 
#fontlist select.w100             { width:100%; }
thead tr th                       { background:#e6e6e6!important; }
.tbl_head01 tbody th	            { background:#eee;text-align:center;padding:7px 10px;color:#5b5b5a;border:1px solid #ddd;border-top-width:0;border-left-width:0; }
.tbl_head01 tbody th:first-child	{ border-left-width:1px; }
.tbl_head01 tbody th a	          { color:#29c7c9; }
.fontlist-row                     { height:80px; }
.sort-placeholder                 { background:#f9f9f9;height:80px; }
textarea                          { height:50px!important; }
.ft-file                          { text-align:left;margin:3px 0; }
.ft-file input                    { width:80%!important; }

/* DRAG SORT */
.sort-handler                     { width:28px; }
.ft-name                          { width:159px; }
.ft-option                        { width:79px; }
.ft-sample                        { width:229px;font-size:20px; }
.ft-src                           { width:591px; }
.ft-manage                        { width:49px; }

/* DELETE BUTTON */
.del_font                         { padding:2px 0; }

/* FONT */
<?
$style_sql = " select * from {$g5['font_table']} where ft_type = 'font' order by ft_order*1, ft_code*1, ft_id ";
$style_result = sql_query($style_sql);
for ($i=0; $row=sql_fetch_array($style_result); $i++) {
	if($row['ft_option'] == 'url') { ?>
<?=$row['ft_src']?>

<?	}else if($row['ft_option'] == 'file') { ?>
@font-face {
    font-family: '<?=$row['ft_name']?>';
    src: url('<?=$row['ft_file']?>') format('<?=$row['ft_exp']?>');
    font-weight: <?=$row['ft_weight']?>;
    font-style: <?=$row['ft_style']?>;
}
<?	} 
}?>

</style>

<div class="local_desc01 local_desc">
    <p><strong>주의!</strong> 폰트설정 작업 후 반드시 <strong>확인</strong>을 누르셔야 저장됩니다.</p>
</div>

<form name="fontform" id="fontform" method="post" action="./font_list_update.php" onsubmit="return font_submit(this);" enctype="multipart/form-data">
  <input type="hidden" name="token" value="">

  <div class="sample_input">
    <input id="sample_txt" type="text" placeholder="폰트 미리보기 문구 입력">
    <input id="sample_size" type="text" placeholder="SIZE" value='20'>
    <input id="sample_bold" type="checkbox">
    <label for="sample_bold">굵게</label>
    <input id="sample_italic" type="checkbox">
    <label for="sample_italic">기울이기</label>
  </div>

  <div class="btn_add01 btn_add">
      <button type="button" onclick="return add_group();">그룹추가</button>
      <button type="button" onclick="return add_font();">폰트추가</button>
  </div>

  <div id="fontlist" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <colgroup>
      <col style="width:50px;">  <!-- handler -->
      <col style="width:180px;"> <!-- 이름 -->
      <col style="width:100px;">  <!-- 옵션 -->
      <col style="width:250px;"> <!-- 샘플 -->
      <col> <!-- 링크 -->
      <col style="width:70px;">  <!-- 순서 -->
    </colgroup>
    <thead>
        <tr>
          <th class="title-name" scope="col" colspan="2">이름</th>
          <th class="title-option" scope="col">옵션</th>
            <th class="title-sample" scope="col">샘플</th>
            <th class="row-100" scope="col">링크</th>
            <th class="title-manage" scope="col">순서</th>
        </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
      if($row['ft_type'] == 'group') {
        // GROUP
    ?>
        <tr class='fontgroup-row open <?= ($i!=0)?'sort-item sort-cancel':'' ?>'>
          <th class='toggle-handler'>
            <span class='toggle-handler-text'>[접기]</span>
            <input type='hidden' name='ft_type[]' value='group'/>
            <input type='hidden' name='ft_name[]'/>
            <input type='hidden' name='ft_option[]'/>
            <input type='hidden' name='ft_src[]'/>
            <input type='file' name='ft_file_1[]' style='display:none;'/>
            <input type='hidden' name='ft_file_2[]'/>
            <input type='hidden' name='ft_weight[]'/>
            <input type='hidden' name='ft_style[]'/>
          </th>
          <th class='row-100'><input type='text' class='ft_group w100' name='ft_group[]' value='<?=$row['ft_group']?>'/></th>
          <th>GROUP</th>
          <th colspan='2'></th>
          <th><input type='text' class='w100' name='ft_order[]' value='<?=$row['ft_order']?>' required/></th>
        </tr>
    <?}else{
        // FONT
    ?>
        <tr class='fontlist-row sort-item'>
          <td class='sort-handler'> ≡
            <input type='hidden' name='ft_type[]' value='font'/>
            <input type='hidden' name='ft_group[]'/>
            <input type='hidden' name='ft_order[]'/>
          </td>
          <td class='ft-name'><input type='text' class='w100' name='ft_name[]' value='<?=$row['ft_name']?>' required/></td>
          <td class='ft-option'>
            <select name='ft_option[]' class='w100'>
              <option value='url' <? if($row['ft_option'] == 'url' ) echo 'selected'; ?>>url</option>
              <option value='file' <? if($row['ft_option'] == 'file' ) echo 'selected'; ?>>file</option>
            </select>
          </td>
          <td class='ft-sample' style="font-family:'<?=$row['ft_name']?>';"><?=$sample_txt?></td>
          <td class='ft-src row-100'>
            <textarea class='ft-url' <? if($row['ft_option'] == 'file' ) echo "style='display:none;'"; ?> name='ft_src[]'><?=$row['ft_src']?></textarea>
            <p class='ft-file' <? if($row['ft_option'] == 'url' ) echo "style='display:none;'"; ?>>
              파일 <input type="file" name="ft_file_1[]">
            </p>
            <p class='ft-file' <? if($row['ft_option'] == 'url' ) echo "style='display:none;'"; ?>>
              외부링크 <input type="text" name="ft_file_2[]" value="<?php echo $row['ft_file'] ?>">
            </p>
            <p class='ft-file' <? if($row['ft_option'] == 'url' ) echo "style='display:none;'"; ?>>
              font-weight
              <select name='ft_weight[]'>
                <?for($i=0;$i<count($_weight);$i++){?>
                  <option value="<?=$_weight[$i]?>" <?if($_weight[$i]==$row['ft_weight'])echo 'selected';?>><?=$_weight[$i]?></option>
                <?}?>
              </select>
              font-style
              <select name='ft_style[]'>
                <?for($i=0;$i<count($_style);$i++){?>
                  <option value="<?=$_style[$i]?>" <?if($_style[$i]==$row['ft_style'])echo 'selected';?>><?=$_style[$i]?></option>
                <?}?>
              </select>
            </p>
          </td>
          <td class='ft-manage'><input type='button' class='del_font' value='삭제'/></td>
        </tr>
    <?}
    }?>

    <? if ($i==0) { ?>
      <tr class='fontgroup-row open'>
        <th class='toggle-handler'>
          <span class='toggle-handler-text'>[접기]</span>
          <input type='hidden' name='ft_type[]' value='group'/>
          <input type='hidden' name='ft_name[]'/>
          <input type='hidden' name='ft_option[]'/>
          <input type='hidden' name='ft_src[]'/>
        </th>
        <th class='row-100'><input type='text' class='ft_group w100' name='ft_group[]' value='기본그룹' required/></th>
        <th>GROUP</th>
        <th colspan='2'></th>
        <th><input type='text' class='w100' name='ft_order[]' value='0' required/></th>
      </tr>
      <tr id="empty_font_list">
        <td colspan="<?=$colspan?>" class="empty_table">자료가 없습니다.</td>
      </tr>
    <? } ?>
    </tbody>
    </table>
  </div>

  <div class="btn_confirm01 btn_confirm">
      <input type="submit" name="act_button" value="확인" class="btn_submit">
  </div>

</form>


<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
/***********************************************************
 * DRAG AND DROP REORDER
***********************************************************/
function fontlist_sort(){
  $( "#fontlist tbody" ).sortable({
      handle: ".sort-handler",
      placeholder: "sort-placeholder",
      items: ".sort-item",
      cancel: ".sort-cancel"
  });
  $( "#fontlist tbody" ).disableSelection();
}


/***********************************************************
 * FONT ROWS
***********************************************************/
function toggle_sub(){
  var toggle_handler = $(this);


  if($(this).parent().hasClass('open')) {
    $(this).parent().nextUntil('.fontgroup-row').hide();
    $(this).parent().removeClass('open');
    $(this).find('.toggle-handler-text').text('[펼치기]');
  }else {
    $(this).parent().nextUntil('.fontgroup-row').show();
    $(this).parent().addClass('open');
    $(this).find('.toggle-handler-text').text('[접기]');
  }
}

function fnSample(){
  var _id = $(this).attr('id');
  var _val = $(this).val();
  var _chk = $(this).prop('checked');
  if(_id == 'sample_txt') {
    $('.ft-sample').text(_val);
    if(!_val){
      $('.ft-sample').text('<?=$sample_txt?>');
    }
  }else if(_id == 'sample_size') {
    if(Number(_val)<14) _val = '14';
    $('.ft-sample').css('font-size', _val+'px');
    $('.ft-sample').css('line-height', _val+'px');
  }else if(_id == 'sample_bold') {
    if(_chk) $('.ft-sample').css('font-weight', 'bold');
    else $('.ft-sample').css('font-weight', 'initial');
  }else if(_id == 'sample_italic') {
    if(_chk) $('.ft-sample').css('font-style', 'italic');
    else $('.ft-sample').css('font-style', 'initial');
  }
}

function toggle_src(){
  var _val = $(this).val();
  if(_val == 'url') {
    $(this).closest('.fontlist-row').children('.ft-src').find('.ft-url').show();
    $(this).closest('.fontlist-row').children('.ft-src').find('.ft-file').hide();
  }else {
    $(this).closest('.fontlist-row').children('.ft-src').find('.ft-url').hide();
    $(this).closest('.fontlist-row').children('.ft-src').find('.ft-file').show();
  }
}

function delete_row(){
  if(!confirm('폰트를 삭제하시겠습니까?')) {
    return false;
  }
  $(this).closest('.fontlist-row').remove();
}

function add_group(){
  var _tbody = $('#fontlist tbody');
  var _row = "";
  _row += "<tr class='fontgroup-row open sort-item sort-cancel'>";
  _row += "  <th class='toggle-handler'>";
  _row += "    <span class='toggle-handler-text'>[접기]</span>";
  _row += "    <input type='hidden' name='ft_type[]' value='group'/>";
  _row += "    <input type='hidden' name='ft_name[]'/>";
  _row += "    <input type='hidden' name='ft_option[]'/>";
  _row += "    <input type='hidden' name='ft_src[]'/>";
  _row += "    <input type='file' name='ft_file_1[]' style='display:none;'/>";
  _row += "    <input type='hidden' name='ft_file_2[]'/>";
  _row += "    <input type='hidden' name='ft_weight[]'/>";
  _row += "    <input type='hidden' name='ft_style[]'/>";
  _row += "  </th>";
  _row += "  <th class='row-100'><input type='text' class='ft_group w100' name='ft_group[]'/></th>";
  _row += "  <th>GROUP</th>";
  _row += "  <th colspan='2'></th>";
  _row += "  <th><input type='text' class='w100' name='ft_order[]' value='0' required/></th>";
  _row += "</tr>";

  _tbody.append(_row);
  fontlist_sort();
  $('#empty_font_list').remove();
}

function add_font(){
  var _tbody = $('#fontlist tbody');
  if( !_tbody.find('tr.fontgroup-row').length) {
    alert('그룹을 먼저 추가해주세요.');
    return;
  }
  var _row = "";
  _row += "<tr class='fontlist-row sort-item'>";
  _row += "  <td class='sort-handler'> ≡ ";
  _row += "    <input type='hidden' name='ft_type[]' value='font'/>";
  _row += "    <input type='hidden' name='ft_group[]'/>";
  _row += "    <input type='hidden' name='ft_order[]'/>";
  _row += "  </td>";
  _row += "  <td class='ft-name'><input type='text' class='w100' name='ft_name[]' required/></td>";
  _row += "  <td class='ft-option'>";
  _row += "    <select name='ft_option[]' class='w100'>";
  _row += "      <option value='url'>url</option>";
  _row += "      <option value='file'>file</option>";
  _row += "    </select>";
  _row += "  </td>";
  _row += "  <td class='ft-sample'></td>";
  _row += "  <td class='ft-src row-100'>";
  _row += "    <textarea class='ft-url' name='ft_src[]'></textarea>";
  _row += "    <p class='ft-file' style='display:none;'>";
  _row += "      파일 <input type='file' name='ft_file_1[]'>";
  _row += "    </p>";
  _row += "    <p class='ft-file' style='display:none;'>";
  _row += "      외부링크 <input type='text' name='ft_file_2[]'>";
  _row += "    </p>";
  _row += "    <p class='ft-file' style='display:none;'>";
  _row += "      font-weight <select name='ft_weight[]'>";
  <?for($i=0;$i<count($_weight);$i++){?>
  _row += "        <option value='<?=$_weight[$i]?>'><?=$_weight[$i]?></option>";
  <?}?>
  _row += "      </select>";
  _row += "      font-style <select name='ft_style[]'>";
  <?for($i=0;$i<count($_style);$i++){?>
  _row += "        <option value='<?=$_style[$i]?>'><?=$_style[$i]?></option>";
  <?}?>
  _row += "      </select>";
  _row += "    </p>";
  _row += "  </td>";
  _row += "  <td class='ft-manage'><input type='button' class='del_font' value='삭제'/></td>";
  _row += "</tr>";

  _tbody.append(_row);
  fontlist_sort();
  $('#empty_font_list').remove();
}


/***********************************************************
 * SUBMIT SETTINGS
***********************************************************/
function remove_emptygroup(){
  // remove empty group
  var _group = $('#fontlist table tbody').children('.fontgroup-row');
  $.each(_group, function(idx, _row){
    _row = $(_row);
    if(!_row.nextUntil('.fontgroup-row').length) {
        _row.remove();
    }
  })
}


function valid_fontlist(){
  // 조건 check~
  $.each($('input.ft_group'), function(idx, _input){
    if(!$(_input).val()){
      alert('그룹명을 입력해주세요.');
      $(_input).focus();
      return false;
    }
  });
  // font name 중복 체크
  // file src 있는지 체크
  return true;
}

function font_submit(){
  remove_emptygroup();
  const chk = valid_fontlist();
  if(chk){
    return true;
  }else {
    return false;
  }
}


/***********************************************************
 * DOCUMENT READY
***********************************************************/
$( function() {
  fontlist_sort();

  // group toggle
  $(document).on("click", ".toggle-handler", toggle_sub);
  // delete font
  $(document).on("click", ".del_font", delete_row);
  // sample text
  $(document).on("input", ".sample_input > input", fnSample);
  // select url/file
  $(document).on("change", ".ft-option > select", toggle_src);
});
</script>



<?php
include_once ('./admin.tail.php');
?>
