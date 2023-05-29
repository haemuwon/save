<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
$tab_width = 1000; ?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- 모바일 모드에서 메뉴를 열고 닫기 할 수 있는 버튼 -->
<a href="#" class="mob_open" id="gnb_control_box" onclick="return false;">
		<i class="material-icons mob_menu">arrow_right_alt</i>
		</a>
		<script>
		$(".mob_open").click(function(){
    $(".mini-menu").toggleClass("mini-menu3");
	});

  </script>
		<!-- 모바일 메뉴 열고 닫기 버튼 종료 -->
		<div id="gnb_wrapper">
			<?
			if($config['cf_menu_content']){
			$menu_co=explode(",",$config['cf_menu_content']);
			$menu_content = get_site_content($menu_co[1]);
			echo '<div id="gnb">'.$menu_content.'</div>';
			}else{
		/**************************************************************
		----------------------------메뉴 영역 시작----------------------------
		* 원하실 경우 하단의 <div id="gnb"> ....  </div> 부분을 수정/삭제 해주세요.
		**************************************************************/?> 

<div class="mini-menu mini-menu3">
	<a href="#" onclick="return false" class="openmenu"><li class="mini-item"><i class="material-icons mini-icon2">zoom_out_map</i></li></a>
	<script>
	$(".openmenu").click(function(){
    $(".mini-menu").toggleClass("menu-open");
	});	
		
  </script>
  <br><br>
<a href="https://eternally.ivyro.net/home"><li class="mini-item"><i class="fa-solid fa-house" style="font-size:22px; padding:3px 3px 3px 4px;"></i><div class="menu-txt">HOME</div></li></a>
<div class=menu-line></div>
<a href="https://eternally.ivyro.net/home/bbs/board.php?bo_table=epere"><li class="mini-item"><i class="fa-solid fa-heart" style="font-size:22px; padding:5px;"></i></i><div class="menu-txt">EPERE</div></li></a>
<a href="https://eternally.ivyro.net/home/bbs/content.php?co_id=world"><li class="mini-item"><i class="fa-solid fa-earth-asia" style="font-size:22px; padding:5px;"></i></i><div class="menu-txt">WORLD</div></li></a>
<div class=menu-line></div>
<a href="https://eternally.ivyro.net/home/bbs/board.php?bo_table=log"><li class="mini-item"><i class="fa-brands fa-pagelines" style="font-size:24px; padding:5px 7px 5px 7px;"></i></i><div class="menu-txt"> LOG</div></li></a>
<a href="https://eternally.ivyro.net/home/bbs/board.php?bo_table=pic"><li class="mini-item"><i class="fa-regular fa-image" style="font-size:22px; padding:5px;"></i><div class="menu-txt">PIC</div></li></a>
<a href="https://eternally.ivyro.net/home/bbs/board.php?bo_table=other"><li class="mini-item"><i class="fa-solid fa-tags" style="font-size:22px; padding:5px;"></i><div class="menu-txt">OTHER</div></li></a>
<a href="https://eternally.ivyro.net/home/bbs/board.php?bo_table=board"><li class="mini-item"><i class="fa-solid fa-paragraph" style="font-size:23px; padding:5px 8px 5px 5px;"></i></i><div class="menu-txt"> BOARD</div></li></a>
<div class=menu-line></div>
<?if ($is_member){?>
  <a href="#"><li class="mini-item"><i class="fa-solid fa-quote-left" style="font-size:20px; padding:3px 6px 6px 9px;"></i><div class="menu-txt" ><b><?=$member['mb_name']?></b> 님</div></li></a>
  <?if($is_admin){?>
      <a href="<?=G5_ADMIN_URL?>" target="_blank"><li class="mini-item"><i class="material-icons mini-icon">settings</i><div class="menu-txt">관리자</div></li></a><?}
        if($is_member && !$is_admin){?>
          <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" id="ol_after_info"><li class="mini-item"><i class="material-icons mini-icon">construction</i><div class="menu-txt">정보수정</div></li></a><?}?>
		<a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout"><li class="mini-item"><i class="material-icons mini-icon"><span class="material-icons-outlined">power_settings_new</i><div class="menu-txt">로그아웃</div></li></a><?}else{?>
		<a href="<?=G5_BBS_URL?>/login.php"><li class="mini-item"><i class="material-icons mini-icon"><span class="material-icons-outlined">power_settings_new</i><div class="menu-txt">로그인</div></li></a>
			<?if($config['cf_1']){?>
				<a href="<?php echo G5_BBS_URL ?>/register.php"><li class="mini-item"><i class="material-icons mini-icon">how_to_reg</i><div class="menu-txt">회원가입</div></li></a><?}?>
				<?}?>
        <!--
				<li class="mini-item"><a href="<?=G5_URL?>/bgm.php?action=play" target="bgm_frame" class="play playy music_btn" onclick="return fn_control_bgm('play')">
			</a><div class="menu-txt onoff play2"></div></li>
			<li class="mini-item"><a href="<?=G5_URL?>/bgm.php" target="bgm_frame" class="stop music_btn" onclick="return fn_control_bgm('stop')">
			</a></li>
			<script type="text/javascript">
var bgm_effect = null;

function fn_control_bgm(state) {
	if(state == 'play') { 
		$('.play').addClass('playy');
		$('.onoff').addClass('play2');
		$('.onoff').removeClass('stop2');
	} else { 
		$('.play').removeClass('playy');
		$('.onoff').addClass('stop2');
		$('.onoff').removeClass('play2');
	}

	if($('html').hasClass('single')) { 
		return false;
	} else {
		return true;
	}
}
bgm_effect = setInterval(set_equalizer, 300);
</script> -->


</div>



			
			<? /**************************************************************
			----------------------------메뉴 영역 끝----------------------------
			**************************************************************/ }?> 
		</div>

    



<? /* 아래는 스타일시트 */ ?>
<style>

:root {
  --menu-icon:#fff;
  --menu-text:#000;
  --menu-point:rgba(158, 168, 255, 0.842);
  --menu-point-trans:rgba(255,255,255,0.4);
  --menu-shadow:rgba(50, 88, 130, 0.32);
  --menu-background:linear-gradient(to bottom, rgb(27 77 104), rgb(243 162 188));
}

<? /* 모바일 반응형---- */ ?>
@media all and (min-width: <?=($tab_width + 1)?>px) { 
  #header			{
    padding:0 !important;
    position:fixed !important;
    z-index:2 !important;
  }
}

@media all and (max-width: <?=$tab_width?>px) {
  .mini-menu3{
		margin-left:-20% !important;
	}
}
@media all and (max-width: 1000px) {
#gnb_control_box {
    left: 10px !important;
  }
}
<? /* ----모바일 반응형 끝 */ ?>

.mob_menu {
  color:var(--menu-icon);
  background: var(--menu-point);
  border-radius: 100%;
}

.menu-line {
  position: relative;
  height:0px;
  border-top:1px dashed rgba(255,255,255,0.4);
  width:auto;
  margin:2px 0px;
  background-color:rgba(255,255,255,0.4);
}
.mini-menu {
  transition-property: width, margin-left;
  transition-duration: 0.5s;
  transition-timing-function: cubic-bezier(0.645, 0.045, 0.355, 1);
  width: 34px;
  height:fit-content;
  border-radius: 12px;
  padding: 8px 0px;
  background: var(--menu-background);
  position:fixed;
  left:2%;
  margin-left:0%;
  top: 50%;
  transform: translateY(-50%);
  text-align: left; 
  filter:drop-shadow(0px 1px 3px rgba(50, 88, 130, 0.4));
  overflow: hidden;
  z-index: 15;
}

.mini-item {
  display:block;
  border-radius: 10px;
}
.mini-item:hover {
  filter:drop-shadow(0 0 2px white);
  background:var(--menu-point-trans);
}

.play2 {
}

.play2:before {
  content:"♬";
}

.stop2 {

}
.stop2:before {
  content: "∥";
}
.onoff {

}


.menu-open {
  transition-duration: 0.5s;
  width: 100px;
}

.mini-icon {
  color: var(--menu-icon);
  margin: 3px 5px;
}

.mini-icon2 {
  color: var(--menu-icon);
  border-radius: 100%;
  margin: 3px 2px 3px 2px;
  padding: 3px;
  transform: rotate();
}

.menu-txt {
  position:absolute;
  color: var(--menu-text);
  display: inline-block;
  word-break:keep-all;
  transform: translateX(2px) translateY(6px);
  font-size: 11px;
  font-weight: normal;
  -webkit-font-smoothing: antialiased;
}


<?/* 뮤직 플레이어 */?>

.playy {
animation-name: playying;
animation-duration: 0.4s;
animation-direction: alternate;
animation-iteration-count: infinite;
}

.music_btn {
display: inline-block;
vertical-align:middle;
position: relative;
width: 18px;
height: 18px;
text-indent: -999px;
overflow: hidden;
background: var(--menu-icon);
transform: rotate(45deg);
margin: 5px 8px;
}

.music_btn:hover {
background-color: var(--menu-text);
}
.music_btn:before {
content: "";
color: var(--color-point);
display: block;
position: relative;
text-indent: 0;
font-weight:normal;
text-align: center;
font-size: 9px;
margin:auto;
transform: rotate(-45deg);
}

.play:before { content: "ON"; }
.stop:before { content: "OFF"; }

@keyframes playying {
from {
  filter:drop-shadow(0px 0px 0px rgba(255,255,255,1));
}
 
to {
filter:drop-shadow(0px 0px 5px rgba(255,255,255,1));
}
  }
<?/* 뮤직 플레이어 끝 */?>
</style>