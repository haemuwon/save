<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$ch_display_skin_config = array(
    "theme"             => "circular", // rect, circular, ddeeboo, ddeeboo bitmap
    /* List 설정 */
    "list_grid_column"   => 6, // 한 줄에 들어가는 항목 갯수
    "list_grid_margin"   => 32, // 줄 사이 간격
    "list_grid_gutter"   => 8, // 항목 사이 간격
    /* Font 설정 */
    "font-basic"        => "'Pretendard', sans-serif", // 기본 폰트 설정
    "font-gothic"       => "'Montserrat', 'Pretendard', sans-serif", // 고딕 계열 폰트 설정
    "font-serif"        => "'Cinzel Decorative', 'Noto Serif KR', 'kepler-std-extended-display', 'optique-display', 'abajiminm-syuveb', serif", // 세리프 계열 폰트 설정
    /* Background 설정 */
    "bg-blur"           => 8, // 배경 블러 효과 수준 (기본 8, 최소 0)
    /* Animation 설정 */
    "onload-delay"      => 1500 // 뷰페이지 접속 시 최소 로딩 시간 (대사 출력 시간에 영향)
);
?>