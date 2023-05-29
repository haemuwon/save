
$(document).ready(function() {
  if (document.querySelector('.textggu--sub6')) {
    var sub6 = document.querySelectorAll('.textggu--sub6');
      for (let i = 0; i < sub6.length; i++) {
        var text = sub6[i].innerText;
        var text_array = [];
          for (let i = 0; i < text.length; i++){
            if (text[i] != ' '){
              text_array.push('<div class="textggu--sub6--circle">')
              text_array.push(text[i]);
              text_array.push('</div>');
              } else {
              text_array.push('<div class="textggu--sub6--circle">')
              text_array.push('&nbsp;');
              text_array.push('</div>');
              }
            }
          text_array = text_array.join('');
          sub6[i].innerHTML = text_array;
          }
    }
    
    if (document.querySelector('.textggu--sub7')) {
    var sub7 = document.querySelectorAll('.textggu--sub7');
      for (let i = 0; i < sub7.length; i++) {
        var text = sub7[i].innerText;
        var text_array = [];
          for (let i = 0; i < text.length; i++){
            if (text[i] != ' '){
              text_array.push('<div class="textggu--sub7--square"><span class="textggu--sub7--text">')
              text_array.push(text[i]);
              text_array.push('</span></div>');
              } else {
              text_array.push('<div class="textggu--sub7--square"><span class="textggu--sub7--text">')
              text_array.push('&nbsp;');
              text_array.push('</span></div>');
              }
            }
          text_array = text_array.join('');
          sub7[i].innerHTML = text_array;
          }
    }
  });