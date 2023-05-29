<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$content_skin_url.'/style.css">', 0);
 
?> 
<div class='co_container theme-box'>
<?=$is_admin? '<div class="ctt_admin"><a href="'.G5_ADMIN_URL.'/contentform.php?w=u&amp;co_id='.$co_id.'" target="_blank" class="ui-btn admin">내용 수정</a></div>':"";?>
<div class="co_content"> 
<?php echo $str; ?> 
</div>
</div> 