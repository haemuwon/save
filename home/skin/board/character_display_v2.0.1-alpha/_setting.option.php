<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// Define necessary functions (2022-12-13)
if (!function_exists("hex2rgba")) {
    function hex2rgba($color, $opacity = false) {
        $default = 'rgb(0,0,0)';
        if(empty($color)) return $default; 
        if ($color[0] == '#' ) {
            $color = substr($color, 1);
        }
        if (strlen($color) == 6) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }
    
        $rgb =  array_map('hexdec', $hex);
    
        if($opacity){
            $opacity = (100 - $opacity) / 100;
            if(abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(",",$rgb).')';
        }
        return $output;
    }
}

$wr_option3_name = array(
    "age" => "나이",
    "gender" => "성별",
    "height" => "키",
    "weight" => "체중",
    "bodyform" => "체형",
    "bday" => "생일",
    "bloodtype" => "혈액형",
    "mbti" => "MBTI",
);
// 인적사항 입력 필드 추가 (2022-12-12)
$wr_option3_name_more = array(
    "1" => "",
    "2" => "",
    "3" => "",
    "4" => "",
    "5" => "",
    "6" => "",
    "7" => "",
    "8" => "",
    "9" => "",
);
$wr_option4_name = array(
    "basic" => array(
        "keyword" => "",
    ),
    "coc" => array(
        "look" => "겉보기",
        "character" => "성격",
        "belief" => "사상/신념",
        "scar" => "부상과 흉터",
        "beloved" => "중요한 사람",
        "horror" => "공포증과 집착증",
        "place" => "의미 있는 장소",
        "artifact" => "신화서/주문/유물",
        "cherish" => "소중한 물건",
        "experience" => "기이한 존재와의 만남",
    ),
    "insane" => array(
        "keyword" => "",
    ),
    "dx3" => array(
        "dlois" => "",
        "syndrome" => "",
        "awake" => "각성",
        "impulse" => "충동",
    ),
    "mglg" => array(
        "phase" => "",
        "magician" => "",
        "domain" => "영역",
        "soul" => "혼의 특기",
        "trueform" => "진정한 모습",
        "spellbook" => "마도서",
    ),
);
$wr_option5_name = array(
    "global" => array(
        "hobby" => "취미",
        "special" => "특기",
        "like" => "좋아하는 것",
        "hate" => "싫어하는 것",
        "belonging" => "소지품",
    ),
    "basic" => array(
        "job" => "직업",
        "era" => "시대",
        "nation" => "국적",
        "reside" => "거주지",
    ),
    "coc" => array(
        "job" => "직업",
        "era" => "시대",
        "nation" => "국적",
        "reside" => "거주지",
    ),
    "insane" => array(
        "curious" => "호기심",
        "fear" => "공포심",
        "skill" => "특기",
        "ability" => "어빌리티",
    ),
    "dx3" => array(
        "cover" => "커버",
        "works" => "웍스",
        "alias" => "소속",
    ),
    "mglg" => array(
        "species" => "종족",
        "social" => "사회적 신분",
    ),
);
$wr_option6_name = array(
    "basic" => array(
        "1" => "",
        "2" => "",
        "3" => "",
        "4" => "",
        "5" => "",
        "6" => "",
        "7" => "",
        "8" => "",
        "9" => "",
    ),
    "coc" => array(
        "str" => "근력",
        "dex" => "민첩",
        "pow" => "정신",
        "con" => "건강",
        "app" => "외모",
        "edu" => "교육",
        "siz" => "크기",
        "int" => "지능",
        "luk" => "행운",
    ),
    "dx3" => array(
        "con" => "육체",
        "dex" => "감각",
        "pow" => "정신",
        "app" => "사회",
        // "er" => "침식률",
    ),
    "mglg" => array(
        "atk" => "공격력",
        "def" => "방어력",
        "src" => "근원력",
    ),
);
?>