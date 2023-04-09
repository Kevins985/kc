var realAuthJs = realAuthJs || {};
realAuthJs = {
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
    },
    verify: function (url,ids) {
        var data = {
            id:ids,
            status:$('input[name=reply_status]:checked').val(),
            descr:$('#reply_content').val()
        };
        if(data.status==2 && data.descr==''){
            dialog.msg('请输入备注内容');
            $('#reply_content').focus();
        }
        else{
            App.ajax('PUT', url, data, function (response) {
                if (response.status) {
                    dialog.msg('操作成功', 'success',1000,function (){
                        window.location.reload();
                    });
                } else {
                    dialog.msg(response.msg, 'error');
                }
            });
        }
    },
}
var appCallback = {
    delete:function(url,ids){
        realAuthJs.delete(url,ids);
    },
    verify:function(url,ids){
        App.ajax('GET', url, {id:ids[0]}, function (response) {
            if (typeof response=='string') {
                dialog.open({
                    title:'实名认证审核',
                    content:response,
                    area:['530px','450px'],
                    btn: ['确定','取消'],
                },function(index){
                    realAuthJs.verify(url,ids);
                });
            }
            else if(!response.status) {
                dialog.msg(response.msg, 'error');
            }
        });
    },
}
jQuery(document).ready(function () {
    realAuthJs.init();
});