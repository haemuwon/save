<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<header class="article-content__header">
    <div class="">PROFILE <b><?php echo $ch["article"]["no"]; ?></b></div>
    <div class="article-content__header__divider--h"></div>
    <div class=""><?php echo $ch["article"]["codename"]; ?></div>
</header>
<article class="article-content">
    <div id="view-inner-basic" class="article-content__container">
        <?php // echo article_line($ch, "article-content__voice"); ?>
        <h1 class="article-content__title">기본 정보</h1>
        <!-- 프로필 내용 영역 -->
        <div class="article-content__profile">
            <?php echo article_profile_thumb($ch, "article-content__profile__thumb"); ?>
            <div class="article-content__profile__content">
                <div class="article-content__profile__name--big">
                    <?php echo $ch['ch_name']; ?>
                </div>
                <div class="article-content__profile__name--small">
                    <div class="article-content__profile__name--small__text"><?php echo $ch['article']['name_native']; ?></div>
                    <div class="article-content__profile__name--small__text"><?php echo $ch['article']['name_eng']; ?></div>
                    <?php if($ch['theme']['color']){ ?>
                    <article class="article-palette">
                        <div class="article-palette__thumb ch-bg-point"></div>
                        <div class="article-palette__label"><?php echo $ch['theme']['color']; ?></div>
                    </article>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php if ($ch['summary']) { $summary = nl2br($ch['summary']); echo "<div class='article-content__content'>{$summary}</div>"; } ?>
        <?php if ($ch_subcontent_basic || $ch_subcontent_basic_long) { ?>
            <h2 class="article-content__subtitle">기본 정보</h2>
            <div class="article-content__subcontent grid-3">
                <?php echo ($ch_subcontent_basic)?'<ul class="article-content__subcontent__list grid-2">'.$ch_subcontent_basic.'</ul>':''; ?>
                <?php echo ($ch_subcontent_basic_long)?'<ul class="article-content__subcontent__list grid-1">'.$ch_subcontent_basic_long.'</ul>':''; ?>
            </div>
        <?php } ?>
        <?php if ($ch_subcontent_point) {?>
            <h2 class="article-content__subtitle">특이사항</h2>
            <div class="article-content__subcontent grid-3">
                <ul class="article-content__subcontent__list grid-1">
                    <?php echo $ch_subcontent_point; ?>
                </ul>
            </div>
        <?php } ?>
        <?php if ($ch_subcontent_etc) { ?>
            <h2 class="article-content__subtitle">기타사항</h2>
            <div class="article-content__subcontent grid-3">
                <ul class="article-content__subcontent__list grid-1">
                    <?php echo $ch_subcontent_etc; ?>
                </ul>
            </div>
        <?php } ?>
        <?php if (count($ch["status"]) > 0 || count($ch["status_bar"]) > 0) { // Status 처리 ?>
            <h2 class="article-content__subtitle">스테이터스</h2>
            <div class="article-content__subcontent grid-3">
                <?php
                    echo article_status($ch, "status_bar", "%d", "article-content__subcontent__list", "grid-1");
                    echo article_status($ch, "status", "%d", "article-content__subcontent__list", "grid-2");
                ?>
            </div>
        <?php } ?>
    </div>
    <?php if ($ch_subcontent_backstory) {?>
    <div id="view-inner-backstory" class="article-content__container">
        <h1 class="article-content__title">백스토리</h1>
        <?php echo $ch_subcontent_backstory; ?>
    </div>
    <?php } ?>
    <?php
        foreach ($ch['detail'] as $key => $content) {
            echo article_content($ch, $key, $content);
        }
    ?>
    <footer class="article-content__footer"></footer>
</article>