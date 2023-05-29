<?php 
include_once("./_common.php");
include_once(G5_PATH."/head.sub.php"); 
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0); 
if($is_admin){
?>
<form action="<?=$board_skin_url?>/set_order_update.php" name="orders" method="post" autocomplete="off">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="write_table" value="<?=$write_table?>">
<div style="max-width:1000px;margin:10px auto;">
<table class="theme-form">
<colgroup>
	<?if($board['bo_use_category']&&$board['bo_category_list']){?><col style="width:100px;"><?}?>
	<col style="width:150px;">
	<col>
	<col>
	<col style="width:80px;">
</colgroup>
<tbody>
	<tr>
	<?if($board['bo_use_category']&&$board['bo_category_list']){?><th>카테고리</th><?}?>
	<th>제목</th>
	<th>배너</th>
	<th>링크</th>
	<th>순서</th>
	</tr>
	<?
	$order=sql_query("select * from {$write_table} order by wr_10*1, wr_id"); 
	for ($i=0;$row=sql_fetch_array($order);$i++){ ?>
		<tr>
			<?if($board['bo_use_category']&&$board['bo_category_list']){?>
			<td><select name="category[<?=$row['wr_id']?>]">
				<option value="">카테고리</option>
				<?$cate=explode('|',$board['bo_category_list']);
				for($h=0;$h<count($cate);$h++){?>
					<option value="<?=$cate[$h]?>" <?=$row['ca_name']==$cate[$h] ? "selected": "";?>><?=$cate[$h]?></option>
				<?}?>
			</select><?}?>
			</td>
			<td><input type="text" name="subject[<?=$row['wr_id']?>]" value="<?=$row['wr_1']?>" style="width:100%;"></td>
			<td><input type="text" name="banner[<?=$row['wr_id']?>]" value="<?=$row['wr_link1']?>" style="width:100%;"></td>
			<td><input type="text" name="link[<?=$row['wr_id']?>]" value="<?=$row['wr_link2']?>" style="width:100%;"></td>
			<td><input type="text" name="order[<?=$row['wr_id']?>]" value="<?=$row['wr_10']?>" size="4">
			<input type="hidden" name="idx[]" value="<?=$row['wr_id']?>">
			</td>
		</tr>
	<? } ?> 
</tbody>
</table>
<br>
<div style="float:right;">
<button type="submit" class="ui-btn point">확인</button>
<a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>" class="ui-btn">목록으로</a>
</div>
</div>	
</form>
<?}?>