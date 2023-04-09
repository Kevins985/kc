var systemJs = systemJs || {};
systemJs = {
    init: function () {
        this.initFormScript();
        if (initScriptData.initData) {
            global.initData(initScriptData.initData);
        }
    },
    initFormScript: function () {
        $('#searchBtn').on('click', function () {

            var keyword = $('#searchValue').val();
            if ($.trim(keyword) != '') {
                if ($('#searchType').val() == 'created_time' && !App.isDatetime(keyword)) {
                    dialog.tips('时间格式错误', $('#searchValue'), 'top');
                    $('#searchValue').focus();
                    return;
                }
            }
            $('#searchForm').submit();
        });
    },
}
var appCallback = {
    websiteSubmit:function(response){
        if(response.status){
            dialog.msg("修改成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    emailSubmit:function(response){
        if(response.status){
            dialog.msg("修改成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    upload:function(response){
        dialog.loading("close");
        if(response && response.status){
            var data = response.data;
            var dom = (data.dom?data.dom:'file');
            var img_url = (data.cut[1] ? data.cut[1] :data.file_url);
            if (data.type == 'dict') {
                var html = '<a href="javascript:;" class="delImageBtn"><i class="fa fa-remove"></i></a>\
                           <img class="img-rounded" src="' + img_url + '" />\
                           <input type="hidden" id="'+dom+'" name="'+dom+'" value="'+img_url+'" />';
                $('#uploadImageContainer').prev().hide();
                $('#uploadImageContainer').show().html(html);
                $('.delImageBtn').bind('click', function (e) {
                    e.preventDefault();
                    var self = this;
                    $(self).parent().prev().show();
                    $(self).parent().hide().html('');
                });
                $('.uploadImageBtn').val('');
            }
        }
        else{
            dialog.msg(response.msg,'error');
        }
    }
}
jQuery(document).ready(function () {
    systemJs.init();
});