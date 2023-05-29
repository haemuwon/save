function animateOnLoad(k, item, baseline_delay = 0, delay_interval = 200) {
    setTimeout(function () { $(item).addClass("on") }, baseline_delay + k * delay_interval);
};

function autoContrast(hex) {
    threshold = 130; /* about half of 256. Lower threshold equals more dark text on dark background  */

    hRed = hexToR(hex);
    hGreen = hexToG(hex);
    hBlue = hexToB(hex);

    function hexToR(h) { return parseInt((cutHex(h)).substring(0, 2), 16) }
    function hexToG(h) { return parseInt((cutHex(h)).substring(2, 4), 16) }
    function hexToB(h) { return parseInt((cutHex(h)).substring(4, 6), 16) }
    function cutHex(h) { return (h.charAt(0) == "#") ? h.substring(1, 7) : h }

    cBrightness = ((hRed * 299) + (hGreen * 587) + (hBlue * 114)) / 1000;
    // if (cBrightness > threshold){return "#121212";} else { return "#fafafa";}
    if (cBrightness > threshold) { return "light"; } else { return "dark"; }
};

function measureImage() {
    // 2022-12-10
    $(".js-measure").each(function () {
        const container = $(this);
        const container_ratio = container.width() / container.height();
        const img = $(this).find("img")[0];
        const img_ratio = img.naturalWidth / img.naturalHeight;

        if (img_ratio > container_ratio) {
            $(this).find("img").removeClass("landscape portrait").addClass("landscape");
        } else {
            $(this).find("img").removeClass("landscape portrait").addClass("portrait");
        }
        // 이미지가 작을 경우 종축 중앙 정렬 (2022-12-11)
        if (container.height() > img.naturalHeight) {
            $(this).find("img").addClass("middle");
        } else {
            $(this).find("img").removeClass("middle");
        }
    });
};

function measureHeight() {
	const vh = window.innerHeight * 0.01;
	document.documentElement.style.setProperty("--vh", `${vh}px`);
}

function transforms(x, y, el, constrain = 100, pers = "1000px", rotate = true, translate = true) {
    let box = el.getBoundingClientRect();
    let calcX = -(y - box.y - (box.height / 2)) / constrain;
    let calcY = (x - box.x - (box.width / 2)) / constrain;
    let movement = translate ? "   translate3d(" + calcX + "%, " + calcY + "%, 0px)" : "";
    let rotation = rotate ? "   rotateX(" + calcX + "deg) " + "   rotateY(" + calcY + "deg) " : "";
    return movement + rotation;
};

function transformElement(el, xyEl) {
    el.style.transform = transforms.apply(null, xyEl);
};

function floatingVisuals() {
    let mouseOverContainer = document.getElementById("body");
    // let ex1Layer = document.getElementsByClassName("view__background__overlay")[0];
    let ex2Layer = document.getElementsByClassName("view__body__area-visual__img-holder")[0];

    mouseOverContainer.onmousemove = function (e) {
        let xy = [e.clientX, e.clientY];
        // let position = xy.concat([ex1Layer, 100, "2000px", false]);
        let position2 = xy.concat([ex2Layer, -1000, "1000px", false]);
        // if (ex1Layer) window.requestAnimationFrame(function () { transformElement(ex1Layer, position); });
        if (ex2Layer) window.requestAnimationFrame(function () { transformElement(ex2Layer, position2); });
    };
}