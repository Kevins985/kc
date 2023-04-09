var langJs = langJs || {};
langJs = {
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
        $('#langSubmitBtn').on('click',function(){
            if(!$('#image').get(0) || $('#image').val()==''){
                dialog.msg("请上传图标");
                return false;
            }
            return true;
        });
    },
    deleteTr:function (obj){
        if($('.table tbody tr').length>1){
            $(obj).parents('tr').remove();
        }
        else{
            dialog.msg('必须保留一个','error');
        }
    },
    generate: function (url,ids) {
        dialog.confirm({
            content:'确定需要替换现有的语言包数据吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('POST', url, {ids:ids}, function (response) {
                    if (response.status) {
                        dialog.msg('操作成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
    delete: function (url,ids) {
        dialog.confirm({
            content:'确定需要删除该数据吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('DELETE', url, {id:ids}, function (response) {
                    if (response.status) {
                        $('.checkboxes').each(function (i, el) {
                            if ($(el).val() == ids) {
                                $(el).parent().parent().parent().remove();
                            }
                        });
                        dialog.msg('删除成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
}
var appCallback = {
    upload:function(response){
        global.setUploadData('image',response);
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加语言',
                cancel_btn:'返回语言列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/lang/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/lang/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    generate: function (url, ids) {
        langJs.generate(url,ids);
    },
    delete: function (url, ids) {
        langJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    langJs.init();
});