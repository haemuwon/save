<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<!-- 최신글 시작 { -->
<div class="main_lt" style="margin: -8px 0px 0px 0px;">
<div class="main_lt_title">최근 갱신된 글</div>
    <ul>
    <?php for ($i=0; $i<count($board); $i++) {  ?>
        <div class="main_lt_mas">
            <?php
            echo "<a href=\"".$board[$i]['href']."\">";
            ?>
        <div class="main_lt_ma">
        <li>
            <?php
            
            if ($board[$i]['bo_subject']=='other' && !$board[$i]['wr_is_comment']) {
                // 게시판 이름이 'other'이고 댓글이 아닐 때
                echo "  <i class='material-icons-outlined'>mail</i>";
                    
            }else if($board[$i]['bo_subject']=='other') {
                // 게시판 이름이 'other'이고 댓글일 때
                echo "  <i class='material-icons-outlined'>drafts</i>";
                    
            }else if ($board[$i]['wr_is_comment']) {
                // other X 댓글O
                echo "  <i class='material-icons'>subdirectory_arrow_right</i> ";
                    
            }else {
                // other X 댓글X (로그작성)
                echo "  <i class='material-icons'>notes</i> ";
            }
            

            echo $board[$i]['bo_subject'];
            echo " No. " . $board[$i]['wr_num'];

            if ($board[$i]['wr_is_comment'])
            echo " [" . $board[$i]['wr_comment'] . "]";
            if ($board[$i]['wr_comment_reply'] != "")
            echo ' + ';

            //나의 열람권한이 게시판 목록권한보다 낮거나 비밀 설정이라면
            if ($memberlevel < $board[$i]['bo_level'] || $board[$i]['wr_option'] == 'secret') {
                echo " <i class='material-icons'>lock</i> ";
            }
            echo "</div></a>";
            
            //이름 출력
            echo "<div class='main_lt_name'>";
            echo $board[$i]['wr_name'] . "</div>";


            echo "<div class='main_lt_time'>";
            echo date('m.d', strtotime($board[$i]['wr_datetime'])) . " ";
            echo date('H:i', strtotime($board[$i]['wr_datetime'])) . "</div>";
            
             ?>
        </li>
        </div>
    <?php }  ?>
    <?php if (count($board) == 0) { //게시물이 없을 때  ?>
    <li>게시물이 없습니다.</li>
    <?php }  ?>
    </ul>
</div>
<!-- } <?php echo $bo_subject; ?> 최신글 끝 -->