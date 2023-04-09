var dictJs = dictJs || {};
dictJs = {
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
        $('#addDataBtn').on('click',function (){
            var tr = $('.table tbody tr:last-child');
            var html = tr.clone()
            html.find('input').val('');
            html.removeAttr('data-id');
            $('.delDataBtn',html).on('click',function (){
                dictJs.deleteTr(this);
            });
            $('.moveUpBtn',html).on('click',function (){
                var tr = $(this).parents('tr');
                if(tr.prev()){
                    tr.prev().before(tr);
                }
            });
            tr.after(html);
        });
        $('.delDataBtn').on('click',function (){
            dictJs.deleteTr(this);
        });
        $('.moveUpBtn').on('click',function (){
            var tr = $(this).parents('tr');
            if(tr.prev()){
                tr.prev().before(tr);
            }
        });
        $('#submitDictBtn').on('click',function (){
            dictJs.saveDictList();
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
    saveDictList:function (){
        var dict_code = $('#dict_code').val();
        var data = {'dict_code':dict_code,'list':[]};
        var is_submit = true;
        $('.table tbody tr').each(function (k,el){
            var id = $(el).data('id')??0;
            var field_name = $(el).find('.field_name').val();
            var field_code = $(el).find('.field_code').val();
            var field_type = $(el).find('.field_type').val();
            var field_value = $(el).find('.field_value').val();
            var field_required  = $(el).find('.field_required').val();
            var field_tips = $(el).find('.field_tips').val();
            var value_range_txt = $(el).find('.value_range_txt').val();
            var value_range = $(el).find('.value_range').val();
            if(is_submit && $.trim(field_name)==''){
                dialog.msg('字段名称不能为空');
                $(el).find('.field_name').focus();
                is_submit = false;
            }
            else if(is_submit && $.trim(field_code)==''){
                dialog.msg('字段代码不能为空');
                $(el).find('.field_code').focus();
                is_submit = false;
            }
            else if(is_submit && $.trim(field_type)==''){
                dialog.msg('字段类型不能为空');
                $(el).find('.field_type').focus();
                is_submit = false;
            }
            else if(is_submit && field_required=='Y' && $.trim(field_value)==''){
                dialog.msg('字段值不能为空');
                $(el).find('.field_value').focus();
                is_submit = false;
            }
            if(is_submit){
                data.list.push({
                    'id':id,
                    'field_sort':(k+1),
                    'field_name':field_name,
                    'field_code':field_code,
                    'field_type':field_type,
                    'field_value':field_value,
                    'field_required':field_required,
                    'field_tips':field_tips,
                    'value_range_txt':value_range_txt,
                    'value_range':value_range
                })
            }
        });
        if(is_submit){
            App.ajax('POST','/backend/dict/setting', data, function (response) {
                if (response.status) {
                    dialog.msg('保存成功', 'success');
                } else {
                    dialog.msg(response.msg, 'error');
                }
            });
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
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加字典',
                cancel_btn:'返回字典列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/dict/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/dict/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        dictJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    dictJs.init();
});