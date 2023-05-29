<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


function title_plus($str, $stx = '')
{
    global $g5, $config;

    $str = ' ' . $str;
    $bo_table = preg_replace('/[^a-z0-9_]/i', '', trim($_REQUEST['bo_table']));
    $bo_table = substr($bo_table, 0, 20);

    $str = str_replace("&#039;", "'", $str);
    $str = str_replace("&#034;", "&quot;", $str);

    // 해시태그 설정
    $hash_pattern = "/\\#([0-9a-zA-Z가-힣_])([0-9a-zA-Z가-힣_]*)/";
    $str = preg_replace($hash_pattern, '<a href="?bo_table=' . $bo_table . '&amp;sfl=wr_content&amp;stx=%23$1$2" class="link_hash_tag">&#35;$1$2</a>', $str);
    $str = preg_replace('`제목1\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title1">$1</span>', $str);
    $str = preg_replace('`제목2\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title2">$1</span>', $str);
    $str = preg_replace('`제목3\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title3">$1</span>', $str);
    $str = preg_replace('`제목4\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title4">$1</span>', $str);
    $str = preg_replace('`제목5\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title5">$1</span>', $str);
    $str = preg_replace('`제목6\[(?![\s*])(.*?)(?<![\s*])\]-(?![\s*])(.*?)(?<![\s*])-`', '<span class="textggu--title6" data-text="$2">$1</span>', $str);
    $str = preg_replace('`제목7\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--title7">$1</span>', $str);
    $str = preg_replace('`소제1\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub1">$1</span>', $str);
    $str = preg_replace('`소제2\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub2">$1</span>', $str);
    $str = preg_replace('`소제3\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub3">$1</span>', $str);
    $str = preg_replace('`소제4\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub4">$1</span>', $str);
    $str = preg_replace('`소제5\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub5">$1</span>', $str);
    $str = preg_replace('`소제6\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub6">$1</span>', $str);
    $str = preg_replace('`소제7\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--sub7">$1</span>', $str);
    $str = preg_replace('`기타1\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc1">$1</span>', $str);
    $str = preg_replace('`기타2\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc2">$1</span>', $str);
    $str = preg_replace('`기타3\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc3">$1</span>', $str);
    $str = preg_replace('`기타4\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc4">$1</span>', $str);
    $str = preg_replace('`기타5\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc5">$1</span>', $str);
    $str = preg_replace('`기타6\[(?![\s*])(.*?)(?<![\s*])\]`', '<span class="textggu--etc6">$1</span>', $str);

    return $str;
}
