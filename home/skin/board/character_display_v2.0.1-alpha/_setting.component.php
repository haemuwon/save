<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/************************/
/******* Component ******/
/************************/

/******* Nav Button ******/
function nav_btn_song ($ch, $icon = "play_pause", $text = "BGM") {
    if (!isset($ch['theme']['song'])) return; // Skip undefined var
    if (!$ch['theme']['song']) return;
    // If value exists
    $result = "";
    $result .= "<li class=\"nav-btn__item\">";
    $result .= "<div class=\"nav-btn__item__container js-btn-voice__container animate-onload\" data-label=\"on\">";
	$result .= "<i class=\"nav-btn__item__icon material-symbols-outlined\">{$icon}</i>";
	$result .= "<span class=\"nav-btn__item__text\">{$text}</span>";
	$result .= "</div>";
	$result .= "</li>";
    return $result;
}

function nav_btn ($wr_input, $key) {
    if (!isset($wr_input[$key."_symbol"])) return; // Skip undefined var
    if (!$wr_input[$key."_symbol"]) return;
    // If symbol exists
    $symbol = "<i class='nav-btn__item__icon material-symbols-outlined'>{$wr_input[$key."_symbol"]}</i>";
    $name = "<span class='nav-btn__item__text'>{$wr_input[$key."_name"]}</span>";
    if ($wr_input[$key."_type"] == "keywordchat") {
        $href = "javascript:open_keywordchat();";
        $target = "_self";
    } elseif ($wr_input[$key."_type"] == "myroom") {
        $href = "javascript:open_myroom();";
        $target = "_self";
    } else {
        $href = $wr_input[$key."_url"];
        $target = "_blank";
    }
    return "<li class='nav-btn__item'><a class='nav-btn__item__container animate-onload' href='{$href}' target='{$target}'>{$symbol}{$name}</a></li>";
};

/******* Class component ******/
function article_class ($ch, $name = "No") {
    if (!isset($ch['article']['no'])) return; // Skip undefined var
    if (!$ch['article']['no']) return;
    // If value exists
    $result = "<article class=\"article-class\">";
    $result .= "<a class=\"article-class__slash animate-onload\"></a>";
    $result .= "<div class=\"article-class__top\">";
    $result .= "<div class=\"article-class__top__text animate-onload\">{$name}</div>";
    $result .= "</div>";
    $result .= "<div class=\"article-class__bottom\">";
    $result .= "<div class=\"article-class__bottom__text animate-onload\">{$ch['article']['no']}</div>";
    $result .= "</div>";
    $result .= "</article>";
    return $result;
}

/******* Emblem component ******/
function article_emblem ($ch) {
    if (!isset($ch['source']['emblem'])) return; // Skip undefined var
    if (!$ch['source']['emblem']) return;
    // If value exists
    $result = "<article class=\"article-emblem\">";
    $result .= "<div class=\"article-emblem__container animate-onload\">";
    $result .= "<img class=\"article-emblem__img\" src=\"{$ch['source']['emblem']}\">";
    $result .= "</div>";
    $result .= "</article>";
    return $result;
}

/******* Line component ******/
function article_line ($ch, $root_element_classname = "article-voice", $icon = "arrow_drop_down") {
    if (!isset($ch['article']['line'])) return; // Skip undefined var
    if (!$ch['article']['line']) return;
    // If value exists
    $result = "<article class=\"{$root_element_classname}\">";
    $result .= "<div class=\"animate-box\">";
    $result .= "    <div class=\"{$root_element_classname}__text animate-onload\">{$ch['article']['line']}</div>";
    $result .= "</div>";
    $result .= "<div class=\"animate-box\">";
    $result .= "    <div class=\"{$root_element_classname}__arrow-container animate-onload\">";
    $result .= "        <div class=\"{$root_element_classname}__arrow\">";
	$result .= "        <i class=\"{$root_element_classname}__arrow__icon material-symbols-outlined\">{$icon}</i>";
    $result .= "        </div>";
    $result .= "    </div>";
    $result .= "</div>";
    $result .= "</article>";
    return $result;
}

/******* Summary component ******/
function article_summary ($ch) {
    if (!isset($ch['summary'])) return; // Skip undefined var
    if (!$ch['summary']) return;
    $text = nl2br($ch['summary']);
    // If value exists
    $result = "<article class=\"article-summary\">";
    $result .= "<div class=\"article-summary__container animate-box\">";
    $result .= "<div class=\"article-summary__text animate-onload\">{$text}</div>";
    $result .= "</div>";
    $result .= "</article>";
    return $result;
}

/******* Codename component ******/
function article_codename ($ch) {
    if (!isset($ch['article']['codename'])) return; // Skip undefined var
    if (!$ch['article']['codename']) return;
    // If value exists
    $result = "<article class=\"article-codename\">";
    // $result .= "<div class=\"article-codename__divider--top ch-bg-point animate-onload\"></div>";
    $result .= "<div class=\"article-codename__main animate-box\">";
    $result .= "    <div class=\"article-codename__main__text animate-onload\">{$ch['article']['codename']}</div>";
    $result .= "</div>";
    $result .= "<div class=\"article-codename__rubi animate-box\">";
    $result .= "    <div class=\"article-codename__rubi__text animate-onload\">{$ch['article']['codename_rubi']}</div>";
    $result .= "</div>";
    // Add catchphrase here
    $result .= "<div class=\"article-codename__title animate-box\">";
    $result .= "    <div class=\"article-codename__title__text animate-onload\">{$ch['article']['phrase']}</div>";
    $result .= "</div>";
    $result .= "</article>";
    return $result;
}

/******* Profile component ******/
function article_profile ($ch,  $key = "status") {
    if (!isset($ch['source']['thumb']) && count($ch[$key]) < 1) return; // Skip undefined var
    if (!$ch['source']['thumb'] && count($ch[$key]) < 1) return;
    // If value exists
    $result = "<div class=\"article-profile__thumb-container only-pc\">";
    $result .= article_profile_thumb($ch);
    $result .= article_profile_status($ch, $key);
    $result .= "</div>";
    return $result;
}

function article_profile_thumb ($ch, $root_element_classname = "article-profile__thumb") {
    if (!isset($ch['source']['thumb'])) return; // Skip undefined var
    if (!$ch['source']['thumb']) return;
    // If value exists
    $result = "<div class=\"{$root_element_classname}\">";
    $result .= "<article class=\"article-thumb animate-onload js-measure\">";
    $result .= "<div class=\"article-thumb__background\"></div>";
    $result .= "<div class=\"article-thumb__container\">";
    $result .= "    <img class=\"article-thumb__img\" src=\"{$ch['source']['thumb']}\">";
    $result .= "</div>";
    $result .= "<div class=\"corner-top\"></div>";
    $result .= "<div class=\"corner-bottom\"></div>";
    $result .= "</article>";
    $result .= "</div>";
    return $result;
}

function article_profile_detail ($ch, $key) {
    if (!isset($ch['article'][$key])) return;
    if (!$ch['article'][$key]) return;
    // If value exists
    $result = "<div class=\"article-profile__detail\">";
    $result .= "<div class=\"article-profile__detail__name\">";
    $result .= "    <span class=\"article-profile__detail__name__text animate-onload\">{$key}</span>";
    $result .= "</div>";
    $result .= "<div class=\"article-profile__detail__container ch-border-point animate-box\">";
    $result .= "    <span class=\"article-profile__detail__text animate-onload\">{$ch['article'][$key]}</span>";
    $result .= "</div>";
    $result .= "</div>";
    return $result;
}

/******* Status component ******/
function article_status_bar ($ch, $root_element_classname = "article-status-bar__list") {
    if (count($ch['status_bar']) < 1) return;
    // If value exists
    $result = "<ul class=\"{$root_element_classname}\">";
    foreach ($ch['status_bar'] as $key => $st) {
        $suffix = "%";
        $result .=
        "
        <li class=\"{$root_element_classname}__item\">
            <div class=\"{$root_element_classname}__item__header\">
                <div class=\"{$root_element_classname}__item__name\">
                    <span class=\"{$root_element_classname}__item__name__text\">{$st['name']}</span>
                </div>
                <div class=\"{$root_element_classname}__item__value\">
                    <span class=\"{$root_element_classname}__item__value__now\">{$st['value']}{$suffix}</span>
                    <span class=\"{$root_element_classname}__item__value__max\">{$st['max']}{$suffix}</span>
                </div>
            </div>
            <div class=\"{$root_element_classname}__item__bar-container\">
                <div class=\"{$root_element_classname}__item__bar ch-bg-point\" style=\"width: {$st['percent']}%; \"></div>
                <div class=\"{$root_element_classname}__item__marker\" style=\"margin-right: {$st['percent']}%; \"></div>
                <div class=\"{$root_element_classname}__item__slot\"></div>
            </div>
        </li>
        ";
    }
    $result .= "</ul>";
    return $result;
}

function article_status ($ch, $key = "status", $format = "%02d", $root_element_classname = "article-status__list", $root_element_grid = "grid-4") {
    if (count($ch[$key]) < 1) return;
    if (count($ch[$key]) > 4 && $root_element_grid == "grid-auto-col") $root_element_grid = "grid-4";
    // If value exists
    $result = "<ul class=\"{$root_element_classname} {$root_element_grid} {$ch['theme']['type']}\">";
    foreach ($ch[$key] as $key => $st) {
        $value = sprintf($format, $st['value']);
        $result .=
        "
        <li class=\"{$root_element_classname}__item animate-box\">
            <div class=\"{$root_element_classname}__item__name animate-onload\">{$st['name']}</div>
            <div class=\"{$root_element_classname}__item__value--big animate-onload\">{$value}</div>
        </li>
        ";
    }
    $result .= "</ul>";
    return $result;
}

function article_profile_status ($ch, $key = "status", $format = "%02d", $root_element_classname = "article-profile__status__list") {
    if (count($ch[$key]) < 1) return;
    // If value exists
    $result = "<ul class=\"{$root_element_classname} {$ch['theme']['type']}\">";
    foreach ($ch[$key] as $key => $st) {
        $value = sprintf($format, $st['value']);
        $value_class = $value < 10 ? "one-digit" : "";
        $result .=
        "
        <li class=\"{$root_element_classname}__item animate-box\">
            <div class=\"{$root_element_classname}__item__name animate-onload\">{$st['name']}</div>
            <div class=\"{$root_element_classname}__item__value {$value_class} animate-onload\">{$value}</div>
        </li>
        ";
    }
    $result .= "</ul>";
    return $result;
}

/******* Content component ******/
function article_content_text ($row) {
    // Text processing
    $row = nl2br($row);
    $row = url_auto_link($row);
    return "<p class='article-content__text'>{$row}</p>";
}

function article_content_list ($row) {
    // Text processing
    $row = nl2br($row);
    $row = url_auto_link($row);
    return "<li class='article-content__list__item'><div>{$row}</div></li>";
}

function article_content ($ch, $key, $content) {
    if (!isset($content['value'])) return;
    if (!$content['value']) return;
    // If value exists
    $row_content = $content['value'];
    // 자동 김칠 (2022-01-11 추가)
    // /* */로 감싸인 문자를 자동으로 김칠함
    $row_content = str_replace("/*","<span class=ui-spoiler>",$row_content);
    $row_content = str_replace("*/","</span>",$row_content);
    // 텍스트 파싱 (2022-12-05 추가)
    // text, list 타입에 따라 개별 처리
    $rows = preg_split("/\n[\s]*\n/", $row_content);
    $row_content_text = "";
    foreach ($rows as $row) {
        if ($content['type'] == "text") $row_content_text .= article_content_text($row);
        if ($content['type'] == "list") $row_content_text .= article_content_list($row);
    }
    if ($content['type'] == "list") {
        $row_content_text = "<ul class='article-content__list'>{$row_content_text}</ul>";
    }

    // Format
    $content_dom_id = "view-inner-{$key}";
    $result = "<div id='{$content_dom_id}' class='article-content__container'>";
    $result .= "<h1 class='article-content__title'>{$content['name']}</h1>";
    $result .= $row_content_text;
    $result .= "</div>";

    return $result;
}

function article_content_tab ($key, $name, $container = null) {
    $function = $container ? "showEdit('#view-inner-{$key}');" : "showContent('#view-inner-{$key}');";
    $result = 
    "<li class=\"nav-content__item js-nav-content__item animate-onload\" onclick=\"{$function}\">
        <a class=\"nav-content__item__btn js-nav-content__item__btn\">{$name}</a>
    </li>";
    return $result;
}

/******* Subcontent component ******/
function article_subcontent_textarea ($ch, $key, $name) {
    if (!isset($ch['article'][$key])) return; // Skip undefined var
    if (!$ch['article'][$key]) return;
    // If value exists
    $value = $ch['article'][$key];
    if ($name) {
        $name = stripslashes($name);
        $name = "<h2 class='article-content__subtitle'>{$name}</h2>";
    }
    $result = "{$name}<div class='article-content__subcontent'>{$value}</div>";
    return $result;
}

function article_subcontent_listitem ($ch, $key, $name) {
    if (!isset($ch['article'][$key])) return; // Skip undefined var
    if (!$ch['article'][$key]) return;
    // If value exists
    $value = $ch['article'][$key];
    if ($name) {
        $name = stripslashes($name);
        $name = "<div class='article-content__subcontent__list__item__name'>{$name}</div>";
    }
    $result = "<li class='article-content__subcontent__list__item'>";
    $result .= "{$name}<div class='article-content__subcontent__list__item__value'>{$value}</div>";
    $result .= "</li>";
    return $result;
}
?>