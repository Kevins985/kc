var langKeyJs = langKeyJs || {};
langKeyJs = {
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
    deleteTr:function (obj){
        if($('.table tbody tr').length>1){
            $(obj).parents('tr').remove();
        }
        else{
            dialog.msg('必须保留一个','error');
        }
    },
    getTranslateValue:function (data,callback){
        App.ajax('POST','/backend/langKey/getTranslateValue', data, function (response) {
            if (response.status) {
                callback(response.data);
            } else {
                dialog.msg(response.msg, 'error');
            }
        });
    },
    translateSave:function (url,data){
        App.ajax('POST',url, data, function (response) {
            if (response.status) {
                dialog.msg('保存成功', 'success');
            } else {
                dialog.msg(response.msg, 'error');
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
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加语言翻译',
                cancel_btn:'返回语言翻译列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/langKey/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/langKey/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    translate:function(url,ids){
        App.ajax('POST',url,{type:'view',ids:ids},function(response){
            if(typeof response=='string'){
                dialog.open({
                    title:'翻译内容',
                    content:response,
                    area: ['600px', '400px'],
                    btn:['确定', '取消'],
                },function(index){
                    var lang_id = $('#tran_lang_id').val();
                    var data = {
                        type:'save',
                        data:[],
                    };
                    $('.key_value').each(function(i,el){
                        data.data.push({
                            'lang_id':lang_id,
                            'lang_key_id':$(el).data('id'),
                            'value_name':$(el).val(),
                        })
                    })
                    if(lang_id=='0'){
                        dialog.msg('请输入需要翻译的语言','error');
                    }
                    else{
                        langKeyJs.translateSave(url,data);
                    }
                });
                $('#tran_lang_id').on('change',function (){
                    var lang_id = $(this).val();
                    if(lang_id==0){
                        $('.key_value').val('');
                    }
                    else{
                        langKeyJs.getTranslateValue({ids:ids,lang_id:lang_id},function (result){
                            $('.key_value').each(function(i,el){
                                var key_id = $(el).data('id');
                                $(el).val(result[key_id]);
                            })
                        });
                    }
                })
                $('#translateBtn').on('click',function (){
                    var lang_id = $('#tran_lang_id').val();
                    if(lang_id=='0'){
                        dialog.msg('请输入需要翻译的语言','error');
                    }
                    else{
                        langKeyJs.getTranslateValue({ids:ids,lang_id:lang_id,type:'translate'},function (result){
                            $('.key_value').each(function(i,el){
                                var key_id = $(el).data('id');
                                $(el).val(result[key_id]);
                            })
                        });
                    }
                });
            }
            else if(!response.status){
                dialog.msg(response.msg);
            }
        });
    },
    delete: function (url, ids) {
        langKeyJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    langKeyJs.init();
});