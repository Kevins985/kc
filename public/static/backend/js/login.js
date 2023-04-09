var loginJs = loginJs || {};
loginJs = {
    init:function(){
        $(document).ready(function () {
            $('input:first').focus();
            $('input').bind('keydown', function (e) {
                if (e.which == 13) {
                    if ($('#account').val() != '' && $('#password').val() != '' && $('#captcha').val() != '') {
                        loginJs.loginSubmit();
                    }
                }
            });
            $('#loginSubmitBtn').click(function () {
                loginJs.loginSubmit(this);
            });
            $('#captchaImg').click(function () {
                $(this).attr('src', $(this).data('src') + '?' + new Date().getTime());
            });
        });
    },
    loginSubmit: function () {
        var args = {
            account: $('#account').val(),
            password: $('#password').val(),
            captcha: $('#captcha').val(),
            is_remember:($('input[name=is_remember]').prop('checked')?1:0)
        };
        dialog.loading();
        $.post('/backend/login', args, function (response) {
            dialog.loading('close');
            if (response.status) {
                App.setCache("token",response.data.token);
                if(args.is_remember){
                    $.cookie("token",response.data.token);
                }
                window.location.href = response.data.url;
            } 
            else {
                $('#captcha').val('');
                $('#captchaImg').attr('src', $('#captchaImg').data('src') + '?' + new Date().getTime());
                dialog.msg(response.msg,'sad');
            }
        }, 'json');
    },
}
jQuery(document).ready(function() { 
    loginJs.init();
});
