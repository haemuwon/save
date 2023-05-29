<?php
include_once('./_common.php');
define('_INDEX_', true);
$main_link=get_main_link();
if (G5_IS_MOBILE) {
	goto_url($main_link); 
}
include_once('./head.sub.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/index.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/musicplayer.css">', 0);
?>

<? if($config['cf_bgm']) { // 배경음악 출력부분 ?>
	<div id="site_bgm_box">
	<iframe src="./bgm.php?action=play" name="bgm_frame" id="bgm_frame" border="0" frameborder="0" marginheight="0" marginwidth="0" topmargin="0" scrolling="no" allowTransparency="true" allow="autoplay; encrypted-media"></iframe>
</div>
  <? } ?>
	
	<!-- 마테리얼 아이콘 연결 -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">

	<div class="music-player" id="mplayer" >
	<div class="music-thumb" id="music-thumb"></div>
	
	<div class="music-ui">

		<span id="play-text">now playing…</span>

		<p><span id="songtitle"></span></p>

		<div class="player_detail">
			<div style="text-align:center;">
				<div id="player-infos1"></div>
				<input id="player-progress" type="range" value="0" min="0" max="100"
					onchange="playerCurrentTimeChange(this.value);" oninput="playerCurrentTimeSlide();">
				<label for="player-progress"></label>
				<div id="player-infos2"></div>
			</div>
			
			<div class="music_ctl">
			<a onclick="view_list()"><i class="material-icons"
						style="float:left;">list</i></a>
				<a onclick="bgm_func(1)"><i class="material-icons">skip_previous</i></a>
				<a onclick="bgm_func(2)" id="playicon" style="display:none;"><i class="material-icons">play_arrow</i></a>
				<a onclick="bgm_func(3)" id="pauseicon" style="display:inline-block;"><i class="material-icons">pause</i></a>
				<a onclick="bgm_func(4)"><i class="material-icons">skip_next</i></a>
						<a onclick="bgm_func(5)"><i class="material-icons"
						style="position: absolute; right: 37px; opacity:0.5;" id="shonicon">shuffle</i></a>
						<a onclick="bgm_vol()"><i class="material-icons"
						style="float:right;" >volume_up</i></a>
						<div id="volume-window" style="display:none;">
						<input id="YouTube-player-volume" type="range" value="50" min="0" max="100" onchange="youTubePlayerVolumeChange(this.value);">
					</div>
			</div>
		</div>
	</div>
	<div class="music-list" id="mslist"></div>
</div>


<script>
	islistview = false;

	function view_list() {
		
		if (islistview == false) {
			document.getElementById("mplayer").style.height='390px';
			document.getElementById("music-thumb").classList.add('active');
			document.querySelector(".music-ui").classList.add('active');
			document.querySelector(".music-list").classList.add('active');
		} else {
			document.getElementById("mplayer").style.height='145px';
			document.getElementById("music-thumb").classList.remove('active');
			document.querySelector(".music-ui").classList.remove('active');
			document.querySelector(".music-list").classList.remove('active');
		}
		islistview = !islistview;
	}

	volume_show = false;
	function bgm_vol() {
		if (volume_show == false){
		document.getElementById("volume-window").style.display="flex";
		}
		else {
			document.getElementById("volume-window").style.display="none";
		}
		volume_show = !volume_show;
	}

function playerCurrentTimeChange(currentTime) {
	'use strict';
	var called_frame = parent.document.getElementById("bgm_frame").contentWindow;
    

    called_frame.player.personalPlayer.currentTimeSliding = false;
    called_frame.player.seekTo(currentTime*called_frame.player.getDuration()/100, true);
}

function playerCurrentTimeSlide() {
    'use strict';
	var called_frame = parent.document.getElementById("bgm_frame").contentWindow;

    called_frame.player.personalPlayer.currentTimeSliding = true;
}

function playerDisplayInfos() {
    'use strict';
	var called_frame = parent.document.getElementById("bgm_frame").contentWindow;

    if ((this.nbCalls === undefined) || (this.nbCalls >= 3)) {
        this.nbCalls = 0;
    }
    else {
        ++this.nbCalls;
    }


	
        var current = called_frame.player.getCurrentTime();
        var duration = called_frame.player.getDuration();
        var currentPercent = (current && duration
                              ? current*100/duration
                              : 0);
  		var seconds = Math.floor((duration) % 60),
    		minutes = Math.floor((duration / 60) % 60),
    		hours = Math.floor((duration / (60 * 60)) % 24);

  			hours = (hours < 10) ? "0" + hours : hours;
  			minutes = (minutes < 10) ? "0" + minutes : minutes;
  			seconds = (seconds < 10) ? "0" + seconds : seconds;

		var seconds2 = Math.floor((current) % 60),
    	minutes2 = Math.floor((current / 60) % 60),
    	hours2 = Math.floor((current / (60 * 60)) % 24);

		hours2 = (hours2 < 10) ? "0" + hours2 : hours2;
		minutes2 = (minutes2 < 10) ? "0" + minutes2 : minutes2;
		seconds2 = (seconds2 < 10) ? "0" + seconds2 : seconds2;



        if (!current) {
            current = 0;
        }
        if (!duration) {
            duration = 0;
        }

        var volume = Math.round(called_frame.player.getVolume());
        var volumeItem = parent.document.getElementById('YouTube-player-volume');

        if (volumeItem && (Math.round(volumeItem.value) != volume)) {
            volumeItem.value = volume;
        }

        if (!called_frame.player.personalPlayer.currentTimeSliding) {
            document.getElementById('player-progress').value = currentPercent;
        }

			document.getElementById('player-infos1').innerHTML = (
			hours2 + ':' + minutes2 + ':' + seconds2
        );
        document.getElementById('player-infos2').innerHTML = (
			hours + ':' + minutes + ':' + seconds
        );
				
				
	
}

function youTubePlayerVolumeChange(volume) {
    'use strict';
		var called_frame = parent.document.getElementById("bgm_frame").contentWindow;
			called_frame.player.setVolume(volume);
}

(function () {
    'use strict';

    function init() {
        // Load YouTube library
        var tag = document.createElement('script');

        tag.src = 'https://www.youtube.com/iframe_api';

        var first_script_tag = document.getElementsByTagName('script')[0];

        first_script_tag.parentNode.insertBefore(tag, first_script_tag);


        // Set timer to display infos
        setInterval(playerDisplayInfos, 1000);
    }





    if (window.addEventListener) {
        window.addEventListener('load', init);
    } else if (window.attachEvent) {
        window.attachEvent('onload', init);
    }
}());

 function fn_control_bgm(state) {

	if($('html').hasClass('single')) { 
		return false;
	} else {
		return true;
	}
}
			var isplaying = true;
		  function imgToggle(){
			  var playicon = document.getElementById("playicon");
			  var pauseicon = document.getElementById("pauseicon");
			  var pltext = document.getElementById("play-text");
			  if(isplaying == true){ //이미 재생 중이라면 정지 표시
				  pauseicon.style.display='none';
				  playicon.style.display='inline-block';
					pltext.innerText = 'PAUSE';
			  }else{ // 정지 중이라면 재생 표시
					pauseicon.style.display='inline-block';
				  playicon.style.display='none';
					pltext.innerText = 'now playing…';
			  }
			  isplaying = !isplaying;
			}

			isshuffle = false;
			function shuffleimgToggle(){

			  var shonicon = document.getElementById("shonicon");

			  if(isshuffle == true){
				shonicon.style.opacity='0.5';
			  }
			  else {
				shonicon.style.opacity='1';
			  }
			  isshuffle = !isshuffle;
		  }
		  

		  function bgm_func(val){
			  var called_frame = parent.document.getElementById("bgm_frame").contentWindow;
			  //alert(called_frame.player.pauseVideo);
			  
			  if(val == 1){				//이전곡 
				  called_frame.player.previousVideo();
				  if(isplaying != true){imgToggle();}
				  fn_control_bgm('play');
			  } 
			  else if (val == 2){		//재생 
				  called_frame.player.playVideo();
				  fn_control_bgm('play');
				  imgToggle();
			  }
			  else if (val == 3){		//정지 
				  called_frame.player.pauseVideo();
				  fn_control_bgm('stop');
				  imgToggle();
			  }
			  else if (val == 4){		//다음곡 
				  called_frame.player.nextVideo();
				  if(isplaying != true){imgToggle();}
				  fn_control_bgm('play');
			  }
			  else if (val == 5){		//셔플
				if(isshuffle == true){
				  called_frame.player.setShuffle(false);
				  shuffleimgToggle();
				} else {
				called_frame.player.setShuffle(true);
				shuffleimgToggle();
				}
			  }
			  else {
				  					 
			  }
			  
				
			 
		  }
			
			function changeMusic(id) {
				var called_frame = parent.document.getElementById("bgm_frame").contentWindow;
				var list = called_frame.player.getPlaylist();
				where = list.indexOf(id);
				called_frame.player.playVideoAt(where);
				console.log(list, where, id);
			}


			//여기부터 draggable 기능
	const draggable = ($target) => {
  let isPress = false,
      prevPosX = 0,
      prevPosY = 0;
  
  $target.onmousedown = start;
  $target.onmouseup = end;
    
  // 상위 영역
  window.onmousemove = move;
 
  function start(e) {
    prevPosX = e.clientX;
    prevPosY = e.clientY;

    isPress = true;
  }

  function move(e) {
    if (!isPress) return;

    const posX = prevPosX - e.clientX; 
    const posY = prevPosY - e.clientY; 
    
    prevPosX = e.clientX; 
    prevPosY = e.clientY;
    
    $target.style.left = ($target.offsetLeft - posX) + "px";
    $target.style.top = ($target.offsetTop - posY) + "px";
  }

  function end() {
    isPress = false;
  }
}

window.onload = () => {
  const $target = document.querySelector('.music-player');
  
  draggable($target);
}

	//draggable 기능 끝
		  </script>
</div>

<div id="wrapper">

	<iframe src="<?echo $main_link?>" name="frm_main" id="main" border="0" frameborder="0" marginheight="0" marginwidth="0" topmargin="0" scrolling="auto" allowTransparency="true"></iframe>
	
<script>
$(document.body).on("keydown", this, function (event) {
	if (event.keyCode == 116) {
		document.getElementById('main').contentDocument.location.reload(true);
		return false;
	}
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>