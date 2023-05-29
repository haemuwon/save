<?php
// if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once('./_common.php');

if(!$action) { $action = $_GET["action"]; }

if($action == "ch_play") { 
?>
<!doctype html>
<html lang="ko">
<head><meta charset="utf-8"></head>
<body>
<iframe id="ytplayer" width="640" height="360" src="https://www.youtube.com/embed/<?php echo $_GET['yt_id']; ?>?mute=0&autoplay=1&loop=1&playlist=<?php echo $_GET['yt_id']; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
</body>
</html>

<?php } ?>
