<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<hr class="padding" />
</div>
</section>

		<?
if($config['cf_footer_content']){
	$footer_co=explode(",", $config['cf_footer_content']);
$footer_content = get_site_content($footer_co[1]);
echo '<footer id="footer">'.$footer_content.'</footer>';
} else { 
/**************************************************************
----------------------------푸터 영역 시작----------------------------
* 원하실 경우 하단의 <footer ....  </footer> 부분을 수정/삭제 해주세요.
**************************************************************/?>

	<footer id="footer" class="txt-center">
		COPYRIGHT &copy; 2017 by Avocado edited by <a href="https://extrashot.tistory.com/" target="_blank">daehakwan</a>
	</footer>

<?/**************************************************************
----------------------------푸터 영역 끝----------------------------
**************************************************************/}?> 

<a href="#header" id="goto_top" class="scroll-fix">
	<img src="<?=G5_IMG_URL?>/btn_top.png" />
</a>
<script>
$('#goto_top').click(function () {
	$('body,html').animate({
		scrollTop: 0
	}, 800);
	return false;
});

var h=$("header").outerHeight();
var f=$("footer").outerHeight();
var w=$(window).height();
if(h>=w) h=0;
$("#body").css({"min-height":(w-f-h)+"px"}); 
window.onresize=function(){ 
	h=$("header").outerHeight();
	w=$(window).height();
	if(h>=w) h=0;
	$("#body").css({"min-height":(w-f-h)+"px"}); 
}; //@200926
</script>

 
<script src="<?php echo G5_JS_URL ?>/_custom.js"></script>
 
<?php
include_once(G5_PATH."/tail.sub.php");
?>