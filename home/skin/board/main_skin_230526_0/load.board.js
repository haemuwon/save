

var ondiv = '';
function controlDiv(id, depth, action)
{
var wr_id = document.querySelector('#bo_w_c #wr_id');
var co_id = document.querySelector('#bo_w_c #comment_id');
var co_depth = document.querySelector('#bo_w_c #wr_comment_reply');

if (action == 'u'){
    $('#edit_window').show();
    $('#bo_w').hide();
    if (ondiv != ''){
        ondiv.hide();
        ondiv = '';
    }
    $('#bo_w_c_' + id).show();
    $('#edit_window').append($('#bo_w_c_' + id));
    ondiv = $('#bo_w_c_' + id);
}
if (action == 'c'){
    wr_id.value = id;
    co_id.value = id;
    co_depth.value = depth;
    document.getElementById('fwritereply').submit();
}
if (action == ''){
    
    $('#edit_window').append($('#bo_w' + id));
    if (ondiv != ''){
        ondiv.hide();
        ondiv = '';
    }
    $('#edit_window').show();
    $('#bo_w').show();
}
if (action == 'close'){
  $('#edit_window').hide();
}
}

// 드래거블 함수

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
      if(e.target.tagName == 'INPUT' || e.target.tagName == 'TEXTAREA'){
              isPress = false;
      }
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
    const $target = document.querySelector('#edit_window');

    draggable($target);
    
    // 흔들리는 이미지
    const mainmove = e =>{
    var amountMovedX = (e.clientX * -0.25 / 6);
		var amountMovedY = (e.clientY * -0.25 / 6);
		$('.mainskin--background').css('transform', 'translate3d(' + amountMovedX +'px, '+amountMovedY+ 'px, 0px)');
	}
  if($('#main')) $('body')[0].addEventListener('mousemove', mainmove);
  else document.addEventListener('mousemove', mainmove);
	

  //iframe 끊김 처리
  function bindIFrame(iframe){
  iframe.contentWindow.addEventListener('mousemove', function(event) {
      var clRect = iframe.getBoundingClientRect();
      var evt = new CustomEvent('mousemove', {bubbles: true, cancelable: false});

      evt.clientX = event.clientX + clRect.left;
      evt.clientY = event.clientY + clRect.top;

      iframe.dispatchEvent(evt);
      });
  };

  
  htmlEl = parent.$('#main').contents().find('html');
  if(htmlEl[0] && htmlEl[0].querySelector('iframe')){
    inn = htmlEl[0].querySelectorAll('iframe');
  if (inn.length > 0) {
  inn.forEach ((x) => {
      bindIFrame(x);
  });
  }}
  }

$(window).on("load",function() {
//모바일 대응 화면 전환
  var layouts = document.querySelectorAll('.main_layout');
  var children = document.querySelectorAll('.children');
  var mobilew = document.querySelector('.mobile_window');
  var parents = [];
  var here = [];
  var child_height = [];
  var origin_height = [];
  var sumHeight = 0;

  children.forEach((child, i) => {
    parents[i] = child.parentNode;
    here[i] = document.createElement( "div" );
    
    //요소의 원본 높이를 가져온다. 원본 높이 설정도 저장....
    child_height[i] = child.clientHeight;
    origin_height[i] = child.style.height;
    //처음부터 높이값이 0이라면 설정값을 가져온다.
    if (child_height[i] == 0) child_height[i] = child.style.height;
    
    //검색을 위해 문자열로
    child_height[i] = child_height[i].toString();

    if (child_height[i].includes('%') || child_height[i].includes('calc')){
      //퍼센테이지나 계산식이라면
      var heights = child_height[i].split(' ');
      for (var h = 0; h < heights.length; h++){
        if (heights[h] == '+' || heights[h] == '-' || heights[h] == '*' || heights[h] == '/'){
          continue;
        }
        var regex = /[^0-9]/g;
        if (heights[h].includes('%')){
          heights[h] = heights[h].replace(regex, '');
          heights[h] = heights[h] * 4; // 400px 기준으로 퍼센테이지 계산
        } else {
          heights[h] = heights[h].replace(regex, '');
        }
      }
      child_height[i] = eval(heights.join(' '));
      
    }
    //다시 숫자로
    child_height[i] = parseInt(child_height[i]);

    sumHeight += child_height[i];
    
    here[i].setAttribute( "id", "here" + i );
    here[i].setAttribute( "style", "display: none;" );
    child.parentNode.insertBefore(here[i], child.previousSibling);
  });

  mobilew.style.height = sumHeight + "px";
  //모바일 창의 높이를 원본 div들 높이의 합으로 바꿔준다.
  
   var mobileVer = false;

    if (window.innerWidth <= 1000){
      children.forEach((child, i) => {
        //처음부터 모바일 버전이라면
        mobilew.appendChild(child);
        child.style.height = child_height[i] + 'px';
        mobileVer = true;
        $('#load_window').hide();
      });
      
    }
    
    var resizeTimer;
    window.addEventListener('resize', () => {
      if (resizeTimer != null ) {
        clearTimeout(resizeTimer);
      }
        resizeTimer = setTimeout(() => {
        onResize();
      }, 1000);
    });

  function onResize() {
    if (window.innerWidth <= 1000){
      if (mobileVer == false) {
        //pc -> 모바일
        children.forEach((child, i) => {
        child.style.height = child_height[i] + 'px';
        mobilew.appendChild(child);
         });
      mobileVer = true;
      }
     } else {
      if (mobileVer == true) {
        //모바일 -> pc
        children.forEach((child, i) => {
        here[i].parentNode.insertBefore(child, here[i].nextSibling);
        child.style.height = origin_height[i];
        $('#load_window').hide();
      });
      mobileVer = false;
      }
     
    }
    
  }
});