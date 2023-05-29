<?php
// 아보카도에 설정된 디자인 설정을 가져옵니다.
include_once('./_common.php');
header("Content-Type: text/css; charset=utf-8");

// CSS 설정 가져오기
$css_sql = sql_query("select * from {$g5['css_table']}");
$css = array();
for($i=0; $cs = sql_fetch_array($css_sql); $i++) {
	$css[$cs['cs_name']][0] = $cs['cs_value'];
	$css[$cs['cs_name']][1] = $cs['cs_etc_1'];
	$css[$cs['cs_name']][2] = $cs['cs_etc_2'];
	$css[$cs['cs_name']][3] = $cs['cs_etc_3'];
	$css[$cs['cs_name']][4] = $cs['cs_etc_4'];
	$css[$cs['cs_name']][5] = $cs['cs_etc_5'];
	$css[$cs['cs_name']][6] = $cs['cs_etc_6'];
	$css[$cs['cs_name']][7] = $cs['cs_etc_7'];
	$css[$cs['cs_name']][8] = $cs['cs_etc_8'];
	$css[$cs['cs_name']][9] = $cs['cs_etc_9'];
	$css[$cs['cs_name']][10] = $cs['cs_etc_10'];
}

?>

<? // *********기본********* ?>
.main_layout {
	color: <?=$css['color_default'][0]?>;
}

<? // *********디데이********* ?>
.mainskin--dday--day {
	color: <?=$css['color_point'][0]?>;
}
.mainskin--dday--text2 {
	color: <?=$css['color_default'][0]?>;
}

<? // *********최신글 스킨********* ?>
.main_lt_mas {
	background: <?=$css['color_bak'][0]?>;
}

.main_lt_mas:hover {
	color: <?=$css['color_bak'][0]?>;
	background: <?=$css['color_point'][0]?>;
}
.main_lt_mas:hover .material-icons {
	color: <?=$css['color_bak'][0]?>;
}
.main_lt_mas:hover .main_lt_ma {
	color: <?=$css['color_bak'][0]?>;
}
.main_lt_ma {
	color: <?=$css['color_default'][0]?>;
}
.main_lt_ma .material-icons {
	color: <?=$css['color_point'][0]?>;
}
 
<? // *********링크********* ?>
.mainskin--link .mainskin--icon {
	background: <?=$css['color_point'][0]?>;
	color: <?=$css['color_bak'][0]?>;
}
.mainskin--link:hover .mainskin--icon {
	background: <?=$css['color_default'][0]?>;
	color: <?=$css['color_point'][0]?>;
}
.mainskin--link--text {
	color: <?=$css['color_default'][0]?>;
}

.mainskin--link.text {
	background: <?=$css['color_point'][0]?>;
	color: <?=$css['color_default'][0]?>;
}
.mainskin--link.text:hover {
	color: <?=$css['color_point'][0]?>;
	background: <?=$css['color_bak'][0]?>;
}
.mainskin--link.text .mainskin--icon {
	background: none;
	color: <?=$css['color_default'][0]?>;
}

.mainskin--link.text:hover .mainskin--icon {
	color: <?=$css['color_point'][0]?>;
}

<? // *********테마********* ?>

.main_layout.main_digital {
	box-shadow: 4px 5px 0px rgba(0,0,0,0.4);
}
.main_layout.main_digital::before {
	background: linear-gradient(to right, rgb(243 162 188), #ffffff);
	border-color: #22506b;
	box-shadow: 0px 1px 0px #18394e; 
} 