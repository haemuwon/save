<?if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가?>
<?/**************************************************************
	웹폰트
***************************************************************/?>
@import url('https://cdn.jsdelivr.net/npm/galmuri@latest/dist/galmuri.css');

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