var categoryJs = categoryJs || {};
categoryJs = {
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
        $('.setStatus').on('click',function(){
            var self = this;
            var txt = $(this).text();
            var category_id = $(this).data('id');
            var status = (txt=='是'?0:1);
            App.ajax('PUT', '/backend/category/setStatus', {id:category_id,status:status}, function (response) {
                if (response.status) {
                    if(status==0){
                        $(self).html('<span class="label label-sm label-danger">否</span>');
                        $('[pid='+category_id+']').find('.setStatus').html('<span class="label label-sm label-danger">否</span>');
                    }
                    else{
                        $(self).html('<span class="label label-sm label-success">是</span>');
                    }
                } else {
                    dialog.msg(response.msg, 'error');
                }
            });
        });
    },
    getChildData:function (id,type){
        if(type=='show'){
            if($('[pid='+id+']').get(0)){
                $('[pid='+id+']').show();
            }
        }
        else{
            $('[pid='+id+']').hide();
        }
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
        global.setUploadData('icon',response);
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加商品分类',
                cancel_btn:'返回商品分类列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/category/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/category/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        categoryJs.delete(url,ids[0]);
    },
    setLangTran:function (url,ids){
        window.location.href = url+'/'+ids[0];
    },
}
jQuery(document).ready(function () {
    categoryJs.init();
});