var errorJs = errorJs || {};
errorJs = {
    init: function () {
        var jump_url = $('#jump_url').attr('href');
        var timer = setInterval(function () {
            var time_span = document.getElementById('time_span');
            var time_num = time_span.innerHTML;
            time_span.innerHTML = time_num-1;
        },1000);
        setTimeout(function () {
            clearInterval(timer);
            window.location = jump_url;
        },10000);
    },
}
jQuery(document).ready(function () {
    errorJs.init();
});