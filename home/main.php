<?php
include_once('./_common.php');
define('_MAIN_', true);
include_once(G5_PATH.'/head.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/main.css">', 0); 
?>

<div id="main_body">
 
<?
$main_content = get_site_content('site_main');
if($main_content) { 
	echo $main_content;
} else { 
?>
	<div id="no_design_main">  
		<div class="txt-center"> 
			<iframe src="https://everla.tistory.com/91" frameborder="0" style="width:100%;height:450px;"></iframe>
		</div> 
	</div>
<?php } ?>
</div>


<?
include_once(G5_PATH.'/tail.php');
?>