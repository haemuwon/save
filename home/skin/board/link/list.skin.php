<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
include_once(G5_LIB_PATH.'/thumbnail.lib.php'); 
if($board['bo_table_width']==0) $width="100%";

$cate=array();
$lists=array(); 
?>
<div class="board-skin-basic" style="max-width:<?=$width?>;">
<? if($board['bo_content_head']) { ?>
	<div class="content_head">
		<?=stripslashes($board['bo_content_head']);?>
	</div>
<? } ?>
<?
if($is_category) { 
	$cate=explode('|',$board['bo_category_list']);
} 
for($h=0;$h<=count($cate);$h++){
	if(count($cate)>0 && $h==count($cate)) continue;
	$list_item=sql_query("select * from {$write_table} where wr_reply='' and wr_is_comment=0 order by wr_10*1, wr_id");
 
	for($k=0;$row=sql_fetch_array($list_item);$k++){
		$lists[$k]=get_list($row,$board,$board_skin_url);
	}
?>
<div class="bo_list">
	<?if($is_category){?><h2><?=$cate[$h]?></h2><?}?>
	<ul class="list_box"><?
		 if(count($lists)>0){
		for ($i=0; $i<count($lists); $i++) {
			// 수정, 삭제 링크
			$update_href = $delete_href = "";
			// 로그인중이고 자신의 글이라면 또는 관리자라면 패스워드를 묻지 않고 바로 수정, 삭제 가능
			if (($member['mb_id'] && ($member['mb_id'] == $lists[$i]['mb_id'])) || $is_admin) {
				$update_href = "./write.php?w=u&bo_table=$bo_table&wr_id={$lists[$i]['wr_id']}&page=$page" . $qstr;
				$delete_href = "javascript:del('./delete.php?bo_table=$bo_table&wr_id={$lists[$i]['wr_id']}&page=$page".urldecode($qstr)."');";
				if ($is_admin) 
				{
					$delete_href = "javascript:del('./delete.php?bo_table=$bo_table&wr_id={$lists[$i]['wr_id']}&token=$token&page=$page".urldecode($qstr)."');";
				}
			}
			else if (!$lists[$i]['mb_id']) { // 회원이 쓴 글이 아니라면
				$update_href = "./password.php?w=u&bo_table=$bo_table&wr_id={$lists[$i]['wr_id']}&page=$page" . $qstr;
				$delete_href = "./password.php?w=d&bo_table=$bo_table&wr_id={$lists[$i]['wr_id']}&page=$page" . $qstr;
			}

			if($is_category) {
				if(($lists[$i]['ca_name']!=$cate[$h]) || ($sca && $lists[$i]['ca_name']!=$sca)) continue;
			}
		?><li class="bo-list">
			<a href="<?=$lists[$i]['wr_link2'] ? $lists[$i]['wr_link2'] : "javascript:void(0);"?>" target="_blank" class="link-banner <?if(strstr($lists[$i]['wr_option'],'secret')) echo "secret";?>" <?if(!$lists[$i]['wr_link2']){?>style="cursor:default;"<?}?>> 
			<?if($lists[$i]['wr_link1']){?><img src="<?=$lists[$i]['wr_link1']?>" alt="<?=$lists[$i]['wr_1']?>"><?}else{?><strong><?=$lists[$i]['wr_1']?></strong><?}?>
			</a>
			<p class="link-desc">
				<?if($lists[$i]['wr_1']&&$lists[$i]['wr_link1']){?>
				<a href="<?=$lists[$i]['wr_link2'] ? $lists[$i]['wr_link2'] : "javascript:void(0);"?>" target="_blank" <?if(!$lists[$i]['wr_link2']){?>style="cursor:default;"<?}?> class="name">
				<strong>
				<?=$lists[$i]['wr_1']?>
				</strong> 
				</a>
				<?}?>
			<?if($update_href || $delete_href){?>
				<span class="options">
				<?if($update_href){?><a href="<?=$update_href?>">*</a><?}?>
				<?if($delete_href){?><a href="<?=$delete_href?>">-</a><?}?>
				</span>
			<?}?>
			</p>
		</li><? }}?></ul>  
</div>
<?}?>	

<? if($board['bo_content_tail']) { ?>
	<div class="content_head">
		<?=stripslashes($board['bo_content_tail']);?>
	</div>
<? } ?>
 <? if ($list_href || $is_checkbox || $write_href) { ?>
	<div class="bo_fx txt-right" style="padding: 20px 0;">
		<? if ($list_href || $write_href) { ?>
		<? if ($list_href) { ?><a href="<? echo $list_href ?>" class="ui-btn">목록</a><? } ?>
		<? if ($write_href) { ?>
		<a href="<? echo $write_href ?>" class="ui-btn point">링크 추가</a><? } ?>
		<? } ?>
		<? if($admin_href){?>
		<a href="<?=$board_skin_url?>/set_order.php?bo_table=<?=$bo_table?>&write_table=<?=$write_table?>" class="ui-btn">링크 관리</a>
		<a href="<?=$admin_href?>" class="ui-btn admin">관리자</a><?}?>
	</div> 
	<? } ?>  
</div> 

<!-- } 게시판 목록 끝 -->
