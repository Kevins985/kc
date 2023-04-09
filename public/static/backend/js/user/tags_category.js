var tagsCategoryJs = tagsCategoryJs || {};
tagsCategoryJs = {
    init: function () {
        this.initFormScript();
        if(initScriptData.initData){
            global.initData(initScriptData.initData);
        }
    },
    initFormScript:function(){
        $('#searchBtn').on('click',function(){

            var keyword = $('#searchValue').val();
            if ($.trim(keyword) != '') {
                if ($('#searchType').val() == 'created_time' && !App.isDatetime(keyword)) {
                    dialog.tips('时间格式错误',$('#searchValue'),'top');
                    $('#searchValue').focus();
                    return;
                }
            }
            $('#searchForm').submit();
        });
    },
    delete:function(url,ids){
        dialog.confirm({
            content:'确定需要删除该数据吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('DELETE',url,{id:ids},function(response){
                    if (response.status) {
                        $('.checkboxes').each(function(i,el){
                            if($.inArray($(el).val(),ids)>-1){
                                $(el).parent().parent().parent().remove();
                            }
                        });
                        dialog.msg('删除成功','success');
                    }
                    else {
                        dialog.msg(response.msg,'error');
                    }
                });
            }
        });
    }
}
var appCallback = {
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加标签分类',
                cancel_btn:'返回标签分类列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/tagsCategory/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/tagsCategory/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete:function(url,ids){
        tagsCategoryJs.delete(url,ids);
    },
}
jQuery(document).ready(function () {
    tagsCategoryJs.init();
});