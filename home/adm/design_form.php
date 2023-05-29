<?php
$sub_menu = "100300";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql = "ALTER TABLE {$g5['css_table']} CHANGE  `cs_value`  `cs_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
sql_query($sql);

if ($is_admin != 'super')
	alert('최고관리자만 접근 가능합니다.');

$g5['title'] = '디자인 설정';
include_once ('./admin.head.php');

$design_result = sql_query("select * from {$g5['css_table']}");
$de = array();
for($i=0; $row = sql_fetch_array($design_result); $i++) {
	$de[$row['cs_name']] = $row;
}


if (!isset($config['cf_menu_content'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_menu_content` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_rank_name` ", false);
}

if (!isset($config['cf_main_content'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_main_content` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_rank_name` ", false);
}
if (!isset($config['cf_footer_content'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_footer_content` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_rank_name` ", false);
}
if (!isset($config['cf_logo_use'])) {
	sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_logo_use` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_rank_name` ", false);
}

$pg_anchor = '<ul class="anchor">
	<li><a href="#anc_001">화면 디자인</a></li>
	<li><a href="#anc_002">메뉴 디자인</a></li>
	<li><a href="#anc_005">기본 디자인</a></li>  
	<li><a href="#anc_004">로드비 디자인</a></li>
</ul>';

$frm_submit = '<div class="btn_confirm01 btn_confirm">
	<input type="submit" value="확인" class="btn_submit" accesskey="s">
</div>';


// 현재 : 번호 44번까지 사용 중

if($config['cf_menu_content']!=''){
	$cf_menu=explode(",",$config['cf_menu_content']);
	$co_exist=sql_fetch("select co_id from {$g5['content_table']} where co_id = '{$cf_menu[1]}'");
	if(!$co_exist) sql_query("update {$g5['config_table']} set cf_menu_content=''");
}
if($config['cf_main_content']!=''){
	$cf_main=explode(",",$config['cf_main_content']);
	if($cf_main[0]=='content')
	$co_exist=sql_fetch("select co_id from {$g5['content_table']} where co_id = '{$cf_main[1]}'");
	else if($cf_main[0]=='board')
	$co_exist=sql_fetch("select bo_table from {$g5['board_table']} where bo_table = '{$cf_main[1]}'");
	if(!$co_exist) sql_query("update {$g5['config_table']} set cf_menu_content=''");
}
if($config['cf_footer_content']!=''){
	$cf_footer=explode(",",$config['cf_footer_content']);
	$co_exist=sql_fetch("select co_id from {$g5['content_table']} where co_id = '{$cf_footer[1]}'");
	if(!$co_exist) sql_query("update {$g5['config_table']} set cf_footer_content=''");
}

?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data">
<input type="hidden" name="token" value="" id="token">

<section id="anc_001">
	<h2 class="h2_frm">화면 디자인 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="local_desc02 local_desc">
		<p>다양한 디자인을 적용하기 위해선 직접 HTML / CSS 파일을 수정해 주시길 바랍니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">
						헤더사용
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[32]" value="use_header" readonly size="15"/>
					</td>
					<td>
						<?php echo help('로고 및 메뉴 영역 사용에 대한 선택입니다. 출력을 원치 않을 경우 사용하지 않음을 선택해 주세요.') ?>
						<select name="cs_value[32]">
							<option value="">사용</option>
							<option value="N" <?=$de['use_header']['cs_value'] == 'N' ? "selected" : ""?>>사용하지 않음</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						푸터사용
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[44]" value="use_footer" readonly size="15"/>
					</td>
					<td>
						<?php echo help('카피라잇 등 하단에 상시출력될 내용 사용에 대한 선택입니다. 출력을 원치 않을 경우 사용하지 않음을 선택해 주세요.') ?>
						<select name="cs_value[44]">
							<option value="">사용</option>
							<option value="N" <?=$de['use_footer']['cs_value'] == 'N' ? "selected" : ""?>>사용하지 않음</option>
						</select>
					</td>
				</tr> 
				<tr>
					<th scope="row">
						로고사용
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						-
					</td>
					<td>
						<?php echo help('로고 및 메뉴 영역 사용에 대한 선택입니다. 출력을 원치 않을 경우 사용하지 않음을 선택해 주세요.') ?>
						<select name="cf_logo_use">
							<option value="">사용</option>
							<option value="N" <?=$config['cf_logo_use'] == 'N' ? "selected" : ""?>>사용하지 않음</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						메뉴 화면
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						-
					</td>
					<td>
						<?php echo help('로고를 제외한 메뉴 영역으로 출력될 페이지를 설정합니다. 기본 설정시 head.php 내 표시된 부분을 수정하여 메뉴화면을 수정할 수 있습니다.') ?> 
						<select name="cf_menu_content" id="cf_menu_content">
							<option value="">기본</option> 
							<?$co_option=sql_query("select co_id, co_subject from {$g5['content_table']} where co_subject!=''");
							if(!empty($co_option)){?> 
							<?for ($i=0;$co_list=sql_fetch_array($co_option);$i++){
								$option="content,".$co_list['co_id'];
								if($i==0){?>
								<option value="" disabled >--페이지중 선택--</option><?}?>
								<option value="<?=$option?>" <?=($config['cf_menu_content']==$option) ? "selected":"";?>><?=$co_list['co_subject']?></option>
							<?}
							}?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						메인화면
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						-
					</td>
					<td>
						<?php echo help('메인영역으로 출력될 페이지를 설정합니다. 기본 설정시 main.php를 수정하여 메인화면을 수정할 수 있습니다.') ?> 
						<select name="cf_main_content" id="cf_main_content">
							<option value="">기본</option>
							<?$bbs_option=sql_query("select bo_table, bo_subject from {$g5['board_table']} where bo_subject!=''");
							if(!empty($bbs_option)){?>
							<?for ($i=0;$bbs_list=sql_fetch_array($bbs_option);$i++){
								$option="board,".$bbs_list['bo_table'];
								if($i==0){?>
								<option value="" disabled >--게시판중 선택--</option><?}?>
								<option value="<?=$option?>" <?=($config['cf_main_content']==$option) ? "selected":"";?>><?=$bbs_list['bo_subject']?></option> 
							<?}
							}?>
							<?$co_option=sql_query("select co_id, co_subject from {$g5['content_table']} where co_subject!=''");
							if(!empty($co_option)){?> 
							<?for ($i=0;$co_list=sql_fetch_array($co_option);$i++){
								$option="content,".$co_list['co_id'];
								if($i==0){?>
								<option value="" disabled >--페이지중 선택--</option><?}?>
								<option value="<?=$option?>" <?=($config['cf_main_content']==$option) ? "selected":"";?>><?=$co_list['co_subject']?></option>
							<?}
							}?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						하단(푸터) 화면
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						-
					</td>
					<td>
						<?php echo help('카피라잇 등 하단 영역으로 출력될 페이지를 설정합니다. 기본 설정시 tail.php 내 표시된 부분을 수정하여 하단화면을 수정할 수 있습니다.') ?> 
						<select name="cf_footer_content" id="cf_footer_content">
							<option value="">기본</option> 
							<?$co_option=sql_query("select co_id, co_subject from {$g5['content_table']} where co_subject!=''");
							if(!empty($co_option)){?> 
							<?for ($i=0;$co_list=sql_fetch_array($co_option);$i++){
								$option="content,".$co_list['co_id'];
								if($i==0){?>
								<option value="" disabled >--페이지중 선택--</option><?}?>
								<option value="<?=$option?>" <?=($config['cf_footer_content']==$option) ? "selected":"";?>><?=$co_list['co_subject']?></option>
							<?}
							}?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<br />
		<br />
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th rowspan="2" scope="row">
						로고
					</th>
					<td rowspan="2" class="bo-right bo-left txt-center">
						<? if($de['logo']['cs_value']) { ?>
							<img src="<?=$de['logo']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[0]" value="logo" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[0]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[0]" value="<?=$de['logo']['cs_value']?>" size="50"/>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						모바일 로고
					</th>
					<td rowspan="2" class="bo-right bo-left txt-center">
						<? if($de['m_logo']['cs_value']) { ?>
							<img src="<?=$de['m_logo']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[1]" value="m_logo" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[1]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[1]" value="<?=$de['m_logo']['cs_value']?>" size="50"/>
					</td>
				</tr>

				<tr>
					<th rowspan="3" scope="row">
						배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['background']['cs_value']) { ?>
							<img src="<?=$de['background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[2]" value="background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[2]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[2]" value="<?=$de['background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[2]" value="<?=$de['background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[2]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;<?//모든 배경위치의 중단에 해당하는 css가 middle로 되어있던 오류 수정 @20220418?>
						<select name="cs_etc_3[2]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[2]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[2]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				<tr>
					<th rowspan="3" scope="row">
						헤더 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['header_background']['cs_value']) { ?>
							<img src="<?=$de['header_background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[34]" value="header_background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[34]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[34]" value="<?=$de['header_background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[34]" value="<?=$de['header_background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['header_background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[34]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['header_background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['header_background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['header_background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[34]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['header_background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['header_background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['header_background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['header_background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['header_background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['header_background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['header_background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['header_background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[34]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['header_background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['header_background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['header_background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[34]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['header_background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				<tr>
					<th rowspan="3" scope="row">
						모바일 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['m_background']['cs_value']) { ?>
							<img src="<?=$de['m_background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[3]" value="m_background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[3]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[3]" value="<?=$de['m_background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[3]" value="<?=$de['m_background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['m_background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[3]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['m_background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['m_background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['m_background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[3]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['m_background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['m_background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['m_background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['m_background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['m_background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['m_background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['m_background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['m_background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[3]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['m_background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['m_background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['m_background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[3]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['m_background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				<tr>
					<th rowspan="3" scope="row">
						모바일 헤더 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['m_header_background']['cs_value']) { ?>
							<img src="<?=$de['m_header_background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[33]" value="m_header_background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[33]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[33]" value="<?=$de['m_header_background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[33]" value="<?=$de['m_header_background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['m_header_background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[33]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['m_header_background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['m_header_background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['m_header_background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[33]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['m_header_background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['m_header_background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['m_header_background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['m_header_background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['m_header_background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['m_header_background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['m_header_background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['m_header_background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[33]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['m_header_background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['m_header_background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['m_header_background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[33]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['m_header_background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>
				<tr>
					<th rowspan="3" scope="row">
						푸터 (하단) 배경
					</th> 
					<td class="txt-center bo-right">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[45]" value="footer_background" readonly size="15"/>
					</td> 
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[45]" value="<?=$de['footer_background']['cs_value']?>" size="15"/>
						<i class="color-preview" style="background: <?=$de['footer_background']['cs_value']?>;"></i> 
					</td>
				</tr>

			</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>

<section id="anc_002">
	<h2 class="h2_frm">메뉴 디자인 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="local_desc02 local_desc">
		<p>다양한 디자인을 적용하기 위해선 직접 HTML / CSS 파일을 수정해 주시길 바랍니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">
						메뉴 위치
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[4]" value="menu_pos" readonly size="15"/>
					</td>
					<td>
						<select name="cs_value[4]">
							<option value="left">좌측</option>
							<option value="top" <?=$de['menu_pos']['cs_value'] == 'top' ? "selected" : ""?>>상단</option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						가로사이즈
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[5]" value="menu_width" readonly size="15"/>
					</td>
					<td>
						<input type="text" name="cs_value[5]" value="<?=$de['menu_width']['cs_value']?>" size="10"/> px
					</td>
				</tr>

				<tr>
					<th scope="row">
						세로사이즈
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[6]" value="menu_height" readonly size="15"/>
					</td>
					<td>
						<input type="text" name="cs_value[6]" value="<?=$de['menu_height']['cs_value']?>" size="10"/> px
					</td>
				</tr>

				<tr>
					<th rowspan="3" scope="row">
						메뉴 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['menu_background']['cs_value']) { ?>
							<img src="<?=$de['menu_background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[7]" value="menu_background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[7]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[7]" value="<?=$de['menu_background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[7]" value="<?=$de['menu_background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['menu_background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[7]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['menu_background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['menu_background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['menu_background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[7]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['menu_background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['menu_background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['menu_background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['menu_background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['menu_background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['menu_background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['menu_background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['menu_background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[7]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['menu_background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['menu_background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['menu_background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[7]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['menu_background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				

				<tr>
					<th rowspan="3" scope="row">
						모바일 메뉴 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['m_menu_background']['cs_value']) { ?>
							<img src="<?=$de['m_menu_background']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[8]" value="m_menu_background" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[8]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[8]" value="<?=$de['m_menu_background']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[8]" value="<?=$de['m_menu_background']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['m_menu_background']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[8]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['m_menu_background']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['m_menu_background']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['m_menu_background']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[8]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['m_menu_background']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['m_menu_background']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['m_menu_background']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['m_menu_background']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['m_menu_background']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['m_menu_background']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['m_menu_background']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['m_menu_background']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[8]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['m_menu_background']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['m_menu_background']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['m_menu_background']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[8]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['m_menu_background']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						폰트색상
					</th>
					<td class="bo-right bo-left txt-center">
						일반상태
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[31]" value="menu_text" readonly size="15"/>
					</td>
					<td>
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_value[31]" value="<?=$de['menu_text']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['menu_text']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트크기&nbsp;&nbsp; <input type="text" name="cs_etc_1[31]" value="<?=$de['menu_text']['cs_etc_1']?>" size="5"/> px

					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						마우스 오버
					</td>
					<td>
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[31]" value="<?=$de['menu_text']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['menu_text']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;
						폰트크기&nbsp;&nbsp; <input type="text" name="cs_etc_3[31]" value="<?=$de['menu_text']['cs_etc_3']?>" size="5"/> px
					</td>
				</tr>
				<tr>
					<th scope="row" rowspan="2">
						메뉴 스타일
					</th> 
					<td class="bo-right bo-left txt-center">
						메뉴 간격
					</td>
					<td class="bo-right" rowspan="2">
						<input type="text" name="cs_name[43]" value="menu_style" readonly size="15"/>
					</td>
					<td>
					<?=help("상단 메뉴일 경우 양 옆 간격, 좌측 메뉴일 경우 위 아래 간격을 정합니다.")?>
						<input type="text" name="cs_value[43]" value="<?=$de['menu_style']['cs_value']?>" size="10"/>   px
						
					</td>
					</tr><tr>
					<td class="bo-right bo-left txt-center">메뉴 정렬</td>
					<td>
					<?=help("로고는 정렬되지 않습니다")?>
						<select name="cs_etc_1[43]" >
							<option value="left" <?=$de['menu_style']['cs_etc_1']=='left' ? 'selected': '';?>>좌측</option>
							<option value="center" <?=$de['menu_style']['cs_etc_1']=='center' ? 'selected': '';?>>중앙</option>
							<option value="right" <?=$de['menu_style']['cs_etc_1']=='right' ? 'selected': '';?>>우측</option>
						</select>
					</td>
				</tr>

			</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>

<section id="anc_005">
	<h2 class="h2_frm">기본 디자인 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="local_desc02 local_desc">
		<p>다양한 디자인을 적용하기 위해선 직접 HTML / CSS 파일을 수정해 주시길 바랍니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">
						기본색
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[26]" value="color_default" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[26]" value="<?=$de['color_default']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['color_default']['cs_value']?>;"></i>
					</td>
				</tr>

				<tr>
					<th scope="row">
						전경색
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[27]" value="color_bak" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[27]" value="<?=$de['color_bak']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['color_bak']['cs_value']?>;"></i>
					</td>
				</tr>

				<tr>
					<th scope="row">
						강조색
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[28]" value="color_point" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[28]" value="<?=$de['color_point']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['color_point']['cs_value']?>;"></i>
					</td>
				</tr>

				
				<tr>
					<th scope="row">
						입력폼
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[29]" value="input_bak" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[29]" value="<?=$de['input_bak']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['input_bak']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[29]" value="<?=$de['input_bak']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['input_bak']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[29]" value="<?=$de['input_bak']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['input_bak']['cs_etc_2']?>;"></i>
					</td>
				</tr>

				
				<tr>
					<th scope="row">
						기본박스
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[30]" value="box_style" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[30]" value="<?=$de['box_style']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['box_style']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[30]" value="<?=$de['box_style']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['box_style']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[30]" value="<?=$de['box_style']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['box_style']['cs_etc_2']?>;"></i>
					</td>
				</tr>

				<tr>
				<th rowspan="2" scope="row">
					기본버튼
				</th>
				<td class="bo-right bo-left txt-center">
					일반상태
				</td>
				<td rowspan="2" class="bo-right">
					<input type="text" name="cs_name[9]" value="btn_default" readonly size="15"/>
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_value[9]" value="<?=$de['btn_default']['cs_value']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_value']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[9]" value="<?=$de['btn_default']['cs_etc_1']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_etc_1']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[9]" value="<?=$de['btn_default']['cs_etc_2']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_etc_2']?>;"></i>
				</td></tr><tr>
				<td class="bo-right bo-left txt-center">
					마우스 오버
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_etc_3[9]" value="<?=$de['btn_default']['cs_etc_3']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_etc_3']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_4[9]" value="<?=$de['btn_default']['cs_etc_4']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_etc_4']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_5[9]" value="<?=$de['btn_default']['cs_etc_5']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_default']['cs_etc_5']?>;"></i>
				</td>
			</tr>

			<tr>
				<th rowspan="2" scope="row">
					포인트버튼
				</th>
				<td class="bo-right bo-left txt-center">
					일반상태
				</td>
				<td rowspan="2" class="bo-right">
					<input type="text" name="cs_name[10]" value="btn_point" readonly size="15"/>
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_value[10]" value="<?=$de['btn_point']['cs_value']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_value']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[10]" value="<?=$de['btn_point']['cs_etc_1']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_etc_1']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[10]" value="<?=$de['btn_point']['cs_etc_2']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_etc_2']?>;"></i>
				</td></tr><tr>
				<td class="bo-right bo-left txt-center">
					마우스 오버
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_etc_3[10]" value="<?=$de['btn_point']['cs_etc_3']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_etc_3']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_4[10]" value="<?=$de['btn_point']['cs_etc_4']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_etc_4']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_5[10]" value="<?=$de['btn_point']['cs_etc_5']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_point']['cs_etc_5']?>;"></i>
				</td>
			</tr>

			<tr>
				<th rowspan="2" scope="row">
					기타버튼
				</th>
				<td class="bo-right bo-left txt-center">
					일반상태
				</td>
				<td rowspan="2" class="bo-right">
					<input type="text" name="cs_name[11]" value="btn_etc" readonly size="15"/>
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_value[11]" value="<?=$de['btn_etc']['cs_value']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_value']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[11]" value="<?=$de['btn_etc']['cs_etc_1']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_etc_1']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[11]" value="<?=$de['btn_etc']['cs_etc_2']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_etc_2']?>;"></i>
				</td></tr><tr>
				<td class="bo-right bo-left txt-center">
					마우스 오버
				</td>
				<td>
					텍스트색상&nbsp;&nbsp; <input type="text" name="cs_etc_3[11]" value="<?=$de['btn_etc']['cs_etc_3']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_etc_3']?>;"></i>
					&nbsp;&nbsp;

					배경색상&nbsp;&nbsp; <input type="text" name="cs_etc_4[11]" value="<?=$de['btn_etc']['cs_etc_4']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_etc_4']?>;"></i>
					&nbsp;&nbsp;

					라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_5[11]" value="<?=$de['btn_etc']['cs_etc_5']?>" size="10"/>
					<i class="color-preview" style="background: <?=$de['btn_etc']['cs_etc_5']?>;"></i>
				</td>
			</tr>
				<tr style="display:none;">
					<th rowspan="2" scope="row">
						서브메뉴
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[41]" value="sub_menu" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[41]" value="<?=$de['sub_menu']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['sub_menu']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[41]" value="<?=$de['sub_menu']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['sub_menu']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;
						오버색상&nbsp;&nbsp; <input type="text" name="cs_etc_6[41]" value="<?=$de['sub_menu']['cs_etc_6']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['sub_menu']['cs_etc_6']?>;"></i>
					</td></tr><tr style="display:none;">
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[41]" value="<?=$de['sub_menu']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['sub_menu']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[41]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['sub_menu']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['sub_menu']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['sub_menu']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['sub_menu']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[41]" value="<?=$de['sub_menu']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[41][]" id="cs_etc_5_1_sub_menu" value="top" <?=strstr($de['sub_menu']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_sub_menu">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[41][]" id="cs_etc_5_2_sub_menu" value="bottom" <?=strstr($de['sub_menu']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_sub_menu">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[41][]" id="cs_etc_5_3_sub_menu" value="left" <?=strstr($de['sub_menu']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_sub_menu">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[41][]" id="cs_etc_5_4_sub_menu" value="right" <?=strstr($de['sub_menu']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_sub_menu">우</label>
					</td>
				</tr> 
				<tr>
					<th scope="row">
						이퀄라이저 색상
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[35]" value="equalizer" readonly size="15"/>
					</td>
					<td>
						막대색상&nbsp;&nbsp; <input type="text" name="cs_value[35]" value="<?=$de['equalizer']['cs_value']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['equalizer']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						외부광선&nbsp;&nbsp; <input type="text" name="cs_etc_1[35]" value="<?=$de['equalizer']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['equalizer']['cs_etc_1']?>;"></i>&nbsp;&nbsp;&nbsp;
						<input type="checkbox" name="cs_etc_2[35]" value='1' <?=$de['equalizer']['cs_etc_2']==1 ? "checked":"";?> > 사용안함
					</td>
				</tr>

		</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>

<section id="anc_006" style="display:none;">
	<h2 class="h2_frm">테이블(게시판)디자인</h2>
	<?php echo $pg_anchor ?>

	<div class="local_desc02 local_desc">
		<p>다양한 디자인을 적용하기 위해선 직접 HTML / CSS 파일을 수정해 주시길 바랍니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th rowspan="2" scope="row">
						전체테이블
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[36]" value="board_table" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[36]" value="<?=$de['board_table']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['board_table']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[36]" value="<?=$de['board_table']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['board_table']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[36]" value="<?=$de['board_table']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['board_table']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[36]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['board_table']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['board_table']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['board_table']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['board_table']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[36]" value="<?=$de['board_table']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[36][]" id="cs_etc_5_1_board_table" value="top" <?=strstr($de['board_table']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_board_table">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[36][]" id="cs_etc_5_2_board_table" value="bottom" <?=strstr($de['board_table']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_board_table">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[36][]" id="cs_etc_5_3_board_table" value="left" <?=strstr($de['board_table']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_board_table">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[36][]" id="cs_etc_5_4_board_table" value="right" <?=strstr($de['board_table']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_board_table">우</label>
					</td>
				</tr>
				<tr>
					<th rowspan="2" scope="row">
						목록-제목
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[37]" value="list_header" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[37]" value="<?=$de['list_header']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_header']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[37]" value="<?=$de['list_header']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_header']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[37]" value="<?=$de['list_header']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_header']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[37]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['list_header']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['list_header']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['list_header']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['list_header']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[37]" value="<?=$de['list_header']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[37][]" id="cs_etc_5_1_list_header" value="top" <?=strstr($de['list_header']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_list_header">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[37][]" id="cs_etc_5_2_list_header" value="bottom" <?=strstr($de['list_header']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_list_header">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[37][]" id="cs_etc_5_3_list_header" value="left" <?=strstr($de['list_header']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_list_header">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[37][]" id="cs_etc_5_4_list_header" value="right" <?=strstr($de['list_header']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_list_header">우</label>
					</td>
				</tr>
				<tr>
					<th rowspan="2" scope="row">
						목록-항목
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[38]" value="list_body" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[38]" value="<?=$de['list_body']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_body']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[38]" value="<?=$de['list_body']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_body']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[38]" value="<?=$de['list_body']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['list_body']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[38]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['list_body']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['list_body']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['list_body']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['list_body']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[38]" value="<?=$de['list_body']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[38][]" id="cs_etc_5_1_list_body" value="top" <?=strstr($de['list_body']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_list_body">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[38][]" id="cs_etc_5_2_list_body" value="bottom" <?=strstr($de['list_body']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_list_body">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[38][]" id="cs_etc_5_3_list_body" value="left" <?=strstr($de['list_body']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_list_body">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[38][]" id="cs_etc_5_4_list_body" value="right" <?=strstr($de['list_body']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_list_body">우</label>
					</td>
				</tr>
				<tr>
					<th rowspan="2" scope="row">
						양식-제목
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[39]" value="form_header" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[39]" value="<?=$de['form_header']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_header']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[39]" value="<?=$de['form_header']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_header']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[39]" value="<?=$de['form_header']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_header']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[39]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['form_header']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['form_header']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['form_header']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['form_header']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[39]" value="<?=$de['form_header']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[39][]" id="cs_etc_5_1_form_header" value="top" <?=strstr($de['form_header']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_form_header">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[39][]" id="cs_etc_5_2_form_header" value="bottom" <?=strstr($de['form_header']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_form_header">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[39][]" id="cs_etc_5_3_form_header" value="left" <?=strstr($de['form_header']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_form_header">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[39][]" id="cs_etc_5_4_form_header" value="right" <?=strstr($de['form_header']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_form_header">우</label>
					</td>
				</tr>
				<tr>
					<th rowspan="2" scope="row">
						양식-내용
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[40]" value="form_body" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[40]" value="<?=$de['form_body']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_body']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[40]" value="<?=$de['form_body']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_body']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[40]" value="<?=$de['form_body']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['form_body']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[40]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['form_body']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['form_body']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['form_body']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['form_body']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[40]" value="<?=$de['form_body']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[40][]" id="cs_etc_5_1_form_body" value="top" <?=strstr($de['form_body']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_form_body">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[40][]" id="cs_etc_5_2_form_body" value="bottom" <?=strstr($de['form_body']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_form_body">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[40][]" id="cs_etc_5_3_form_body" value="left" <?=strstr($de['form_body']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_form_body">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[40][]" id="cs_etc_5_4_form_body" value="right" <?=strstr($de['form_body']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_form_body">우</label>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</section>

<?php //echo $frm_submit; ?>


<section id="anc_004">
	<h2 class="h2_frm">로드 게시판 디자인 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="local_desc02 local_desc">
		<p>다양한 디자인을 적용하기 위해선 직접 HTML / CSS 파일을 수정해 주시길 바랍니다.</p>
	</div>

	<div class="tbl_frm01 tbl_wrap">
		<table>
			<caption>홈페이지 기본환경 설정</caption>
			<colgroup>
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col style="width: 130px;">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th rowspan="3" scope="row">
						전체 페이지 배경
					</th>
					<td rowspan="3" class="bo-right bo-left txt-center">
						<? if($de['mmb_contain_bak']['cs_value']) { ?>
							<img src="<?=$de['mmb_contain_bak']['cs_value']?>" class="prev_thumb"/>
						<? } else { ?>
						이미지 미등록
						<? } ?>
					</td>
					<td rowspan="3" class="bo-right">
						<input type="text" name="cs_name[12]" value="mmb_contain_bak" readonly size="15"/>
					</td>
					<td>
						직접등록&nbsp;&nbsp; <input type="file" name="cs_value_file[12]" value="" size="50">
					</td></tr><tr>
					<td>
						외부경로&nbsp;&nbsp; <input type="text" name="cs_value[12]" value="<?=$de['mmb_contain_bak']['cs_value']?>" size="50"/>				
					</td></tr><tr>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_etc_1[12]" value="<?=$de['mmb_contain_bak']['cs_etc_1']?>" size="8"/>
						<i class="color-preview" style="background: <?=$de['mmb_contain_bak']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;

						배경반복&nbsp;&nbsp;
						<select name="cs_etc_2[12]">
							<option value="">반복</option>
							<option value="no-repeat" <?=$de['mmb_contain_bak']['cs_etc_2'] == 'no-repeat' ? "selected" : ""?>>반복없음</option>
							<option value="repeat-x"  <?=$de['mmb_contain_bak']['cs_etc_2'] == 'repeat-x' ? "selected" : ""?>>가로반복</option>
							<option value="repeat-y"  <?=$de['mmb_contain_bak']['cs_etc_2'] == 'repeat-y' ? "selected" : ""?>>세로반복</option>
						</select>
						&nbsp;&nbsp;

						배경위치&nbsp;&nbsp;
						<select name="cs_etc_3[12]">
							<option value="">왼쪽 상단</option>
							<option value="left center"		<?=$de['mmb_contain_bak']['cs_etc_3'] == 'left center' ? "selected" : ""?>>왼쪽 중단</option>
							<option value="left bottom"		<?=$de['mmb_contain_bak']['cs_etc_3'] == 'left bottom' ? "selected" : ""?>>왼쪽 하단</option>

							<option value="center top"		<?=$de['mmb_contain_bak']['cs_etc_3'] == 'center top' ? "selected" : ""?>>중간 상단</option>
							<option value="center center"	<?=$de['mmb_contain_bak']['cs_etc_3'] == 'center center' ? "selected" : ""?>>중간 중단</option>
							<option value="center bottom"	<?=$de['mmb_contain_bak']['cs_etc_3'] == 'center bottom' ? "selected" : ""?>>중간 하단</option>

							<option value="right top"		<?=$de['mmb_contain_bak']['cs_etc_3'] == 'right top' ? "selected" : ""?>>오른쪽 상단</option>
							<option value="right center"	<?=$de['mmb_contain_bak']['cs_etc_3'] == 'right center' ? "selected" : ""?>>오른쪽 중단</option>
							<option value="right bottom"	<?=$de['mmb_contain_bak']['cs_etc_3'] == 'right bottom' ? "selected" : ""?>>오른쪽 하단</option>
						</select>
						&nbsp;&nbsp;

						배경크기&nbsp;&nbsp;
						<select name="cs_etc_4[12]">
							<option value="">원본크기</option>
							<option value="contain"		<?=$de['mmb_contain_bak']['cs_etc_4'] == 'contain' ? "selected" : ""?>>맞춤</option>
							<option value="cover"		<?=$de['mmb_contain_bak']['cs_etc_4'] == 'cover' ? "selected" : ""?>>꽉참</option>
							<option value="100% 100%"	<?=$de['mmb_contain_bak']['cs_etc_4'] == '100% 100%' ? "selected" : ""?>>늘이기</option>
						</select>
						&nbsp;&nbsp;

						고정&nbsp;&nbsp;
						<select name="cs_etc_5[12]">
							<option value="">스크롤</option>
							<option value="fixed"		<?=$de['mmb_contain_bak']['cs_etc_5'] == 'fixed' ? "selected" : ""?>>고정</option>
						</select>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						공지사항 박스
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[13]" value="mmb_notice" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[13]" value="<?=$de['mmb_notice']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_notice']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[13]" value="<?=$de['mmb_notice']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_notice']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[13]" value="<?=$de['mmb_notice']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_notice']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[13]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_notice']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_notice']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_notice']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_notice']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[13]" value="<?=$de['mmb_notice']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[13][]" id="cs_etc_5_1_mmb_notice" value="top" <?=strstr($de['mmb_notice']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_notice">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[13][]" id="cs_etc_5_2_mmb_notice" value="bottom" <?=strstr($de['mmb_notice']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_notice">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[13][]" id="cs_etc_5_3_mmb_notice" value="left" <?=strstr($de['mmb_notice']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_notice">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[13][]" id="cs_etc_5_4_mmb_notice" value="right" <?=strstr($de['mmb_notice']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_notice">우</label>
					</td>
				</tr>

				<tr>
					<th scope="row">
						접속자 카운터
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[42]" value="mmb_counter" readonly size="15"/>
					</td>
					<td>
						<textarea name="cs_value[42]" style="height: 80px;"><?=$de['mmb_counter']['cs_value']?></textarea>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						리스트영역
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[14]" value="mmb_list" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[14]" value="<?=$de['mmb_list']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[14]" value="<?=$de['mmb_list']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[14]" value="<?=$de['mmb_list']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[14]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_list']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_list']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_list']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_list']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[14]" value="<?=$de['mmb_list']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[14][]" id="cs_etc_5_1_mmb_list" value="top" <?=strstr($de['mmb_list']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_list">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[14][]" id="cs_etc_5_2_mmb_list" value="bottom" <?=strstr($de['mmb_list']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_list">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[14][]" id="cs_etc_5_3_mmb_list" value="left" <?=strstr($de['mmb_list']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_list">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[14][]" id="cs_etc_5_4_mmb_list" value="right" <?=strstr($de['mmb_list']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_list">우</label>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						게시물 영역
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[15]" value="mmb_list_item" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[15]" value="<?=$de['mmb_list_item']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list_item']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[15]" value="<?=$de['mmb_list_item']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list_item']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;
						하단여백&nbsp;&nbsp; <input type="text" name="cs_etc_6[15]" value="<?=$de['mmb_list_item']['cs_etc_6']?>" size="10"/> px
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[15]" value="<?=$de['mmb_list_item']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_list_item']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[15]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_list_item']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_list_item']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_list_item']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_list_item']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[15]" value="<?=$de['mmb_list_item']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[15][]" id="cs_etc_5_1_mmb_list_item" value="top" <?=strstr($de['mmb_list_item']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_list_item">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[15][]" id="cs_etc_5_2_mmb_list_item" value="bottom" <?=strstr($de['mmb_list_item']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_list_item">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[15][]" id="cs_etc_5_3_mmb_list_item" value="left" <?=strstr($de['mmb_list_item']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_list_item">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[15][]" id="cs_etc_5_4_mmb_list_item" value="right" <?=strstr($de['mmb_list_item']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_list_item">우</label>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						로그 영역
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[16]" value="mmb_log" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[16]" value="<?=$de['mmb_log']['cs_value']?>" size="10"/>	
						<i class="color-preview" style="background: <?=$de['mmb_log']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[16]" value="<?=$de['mmb_log']['cs_etc_1']?>" size="10"/>	
						<i class="color-preview" style="background: <?=$de['mmb_log']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[16]" value="<?=$de['mmb_log']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_log']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[16]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_log']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_log']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_log']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_log']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[16]" value="<?=$de['mmb_log']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[16][]" id="cs_etc_5_1_mmb_log" value="top" <?=strstr($de['mmb_log']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_log">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[16][]" id="cs_etc_5_2_mmb_log" value="bottom" <?=strstr($de['mmb_log']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_log">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[16][]" id="cs_etc_5_3_mmb_log" value="left" <?=strstr($de['mmb_log']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_log">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[16][]" id="cs_etc_5_4_mmb_log" value="right" <?=strstr($de['mmb_log']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_log">우</label>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						코멘트 영역
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[17]" value="mmb_reply" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[17]" value="<?=$de['mmb_reply']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[17]" value="<?=$de['mmb_reply']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply']['cs_etc_1']?>;"></i>
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[17]" value="<?=$de['mmb_reply']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[17]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_reply']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_reply']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_reply']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_reply']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[17]" value="<?=$de['mmb_reply']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[17][]" id="cs_etc_5_1_mmb_reply" value="top" <?=strstr($de['mmb_reply']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_reply">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[17][]" id="cs_etc_5_2_mmb_reply" value="bottom" <?=strstr($de['mmb_reply']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_reply">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[17][]" id="cs_etc_5_3_mmb_reply" value="left" <?=strstr($de['mmb_reply']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_reply">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[17][]" id="cs_etc_5_4_mmb_reply" value="right" <?=strstr($de['mmb_reply']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_reply">우</label>
					</td>
				</tr>

				<tr>
					<th rowspan="2" scope="row">
						각 코멘트 영역
					</th>
					<td class="bo-right bo-left txt-center">
						배경/폰트/여백
					</td>
					<td rowspan="2" class="bo-right">
						<input type="text" name="cs_name[18]" value="mmb_reply_item" readonly size="15"/>
					</td>
					<td>
						배경색상&nbsp;&nbsp; <input type="text" name="cs_value[18]" value="<?=$de['mmb_reply_item']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply_item']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트색상&nbsp;&nbsp; <input type="text" name="cs_etc_1[18]" value="<?=$de['mmb_reply_item']['cs_etc_1']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply_item']['cs_etc_1']?>;"></i>
						&nbsp;&nbsp;
						하단여백&nbsp;&nbsp; <input type="text" name="cs_etc_6[18]" value="<?=$de['mmb_reply_item']['cs_etc_6']?>" size="10"/> px
					</td></tr><tr>
					<td class="bo-right bo-left txt-center">
						라인
					</td>
					<td>
						라인색상&nbsp;&nbsp; <input type="text" name="cs_etc_2[18]" value="<?=$de['mmb_reply_item']['cs_etc_2']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_reply_item']['cs_etc_2']?>;"></i>
						&nbsp;&nbsp;

						라인타입&nbsp;&nbsp;
						<select name="cs_etc_3[18]" style="width: 84px;">
							<option value="">라인없음</option>
							<option value="solid" <?=$de['mmb_reply_item']['cs_etc_3'] == 'solid' ? "selected" : ""?>>실선</option>
							<option value="dotted" <?=$de['mmb_reply_item']['cs_etc_3'] == 'dotted' ? "selected" : ""?>>점선</option>
							<option value="dashed" <?=$de['mmb_reply_item']['cs_etc_3'] == 'dashed' ? "selected" : ""?>>대쉬선</option>
							<option value="double" <?=$de['mmb_reply_item']['cs_etc_3'] == 'double' ? "selected" : ""?>>이중선</option>
						</select>
						&nbsp;&nbsp;

						라인굵기&nbsp;&nbsp; <input type="text" name="cs_etc_4[18]" value="<?=$de['mmb_reply_item']['cs_etc_4']?>" size="5"/> px
						&nbsp;&nbsp;&nbsp;&nbsp;

						<input type="checkbox" name="cs_etc_5[18][]" id="cs_etc_5_1_mmb_reply_item" value="top" <?=strstr($de['mmb_reply_item']['cs_etc_5'], 'top') ? "checked" : ""?> />
						<label for="cs_etc_5_1_mmb_reply_item">상&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[18][]" id="cs_etc_5_2_mmb_reply_item" value="bottom" <?=strstr($de['mmb_reply_item']['cs_etc_5'], 'bottom') ? "checked" : ""?> />
						<label for="cs_etc_5_2_mmb_reply_item">하&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[18][]" id="cs_etc_5_3_mmb_reply_item" value="left" <?=strstr($de['mmb_reply_item']['cs_etc_5'], 'left') ? "checked" : ""?> />
						<label for="cs_etc_5_3_mmb_reply_item">좌&nbsp;</label>

						<input type="checkbox" name="cs_etc_5[18][]" id="cs_etc_5_4_mmb_reply_item" value="right" <?=strstr($de['mmb_reply_item']['cs_etc_5'], 'right') ? "checked" : ""?> />
						<label for="cs_etc_5_4_mmb_reply_item">우</label>
					</td>
				</tr>

				<tr>
					<th scope="row">
						[작성자] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[19]" value="mmb_name" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[19]" value="<?=$de['mmb_name']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_name']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트크기&nbsp;&nbsp; <input type="text" name="cs_etc_1[19]" value="<?=$de['mmb_name']['cs_etc_1']?>" size="5"/> px
					</td>
				</tr>

				<tr>
					<th scope="row">
						[로그작성자] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[20]" value="mmb_owner_name" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[20]" value="<?=$de['mmb_owner_name']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_owner_name']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트크기&nbsp;&nbsp; <input type="text" name="cs_etc_1[20]" value="<?=$de['mmb_owner_name']['cs_etc_1']?>" size="5"/> px
						&nbsp;&nbsp;
						접두문자&nbsp;&nbsp; <input type="text" name="cs_etc_2[20]" value="<?=$de['mmb_owner_name']['cs_etc_2']?>" size="5"/>
						&nbsp;&nbsp;
						접미문자&nbsp;&nbsp; <input type="text" name="cs_etc_3[20]" value="<?=$de['mmb_owner_name']['cs_etc_3']?>" size="5"/>
					</td>
				</tr>

				<tr>
					<th scope="row">
						[날짜] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[21]" value="mmb_datetime" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[21]" value="<?=$de['mmb_datetime']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_datetime']['cs_value']?>;"></i>
						&nbsp;&nbsp;
						폰트크기&nbsp;&nbsp; <input type="text" name="cs_etc_1[21]" value="<?=$de['mmb_datetime']['cs_etc_1']?>" size="5"/> px
					</td>
				</tr>

				<tr>
					<th scope="row">
						[외부링크] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[22]" value="mmb_link" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[22]" value="<?=$de['mmb_link']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_link']['cs_value']?>;"></i>
					</td>
				</tr>

				<tr style="display:none;">
					<th scope="row">
						[멤버호출] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[23]" value="mmb_call" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[23]" value="<?=$de['mmb_call']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_call']['cs_value']?>;"></i>
					</td>
				</tr>

				<tr>
					<th scope="row">
						[로그앵커] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[24]" value="mmb_log_ank" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[24]" value="<?=$de['mmb_log_ank']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_log_ank']['cs_value']?>;"></i>
					</td>
				</tr>

				<tr>
					<th scope="row">
						[해시태그] 폰트
					</th>
					<td class="bo-right bo-left txt-center">
						-
					</td>
					<td class="bo-right">
						<input type="text" name="cs_name[25]" value="mmb_hash" readonly size="15"/>
					</td>
					<td>
						색상코드&nbsp;&nbsp; <input type="text" name="cs_value[25]" value="<?=$de['mmb_hash']['cs_value']?>" size="10"/>
						<i class="color-preview" style="background: <?=$de['mmb_hash']['cs_value']?>;"></i>
					</td>
				</tr>

			</tbody>
		</table>
	</div>
</section>

<?php echo $frm_submit; ?>



</form>

<script>
function fconfigform_submit(f)
{
	f.action = "./design_form_update.php";
	return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>
