var jobJs = jobJs || {};
jobJs = {
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
        $('input[name=is_notify]').on('click',function(){
            if($(this).val()==1){
                $('#notifyBox').removeClass('hide');
            }
            else{
                $('#notifyBox').addClass('hide');
            }
        })
        $('#cronChoiceBtn').on('click',function(){
            App.ajax('GET','/backend/job/setRules',{},function(response){
                if(typeof response=='string'){
                    dialog.open({
                        title:'设置规则',
                        area: ['750px', '450px'],
                        content:response
                    })
                }
                else{
                    dialog.msg(response.msg,'error');
                }
            });
        });
        $('#cmdChoiceBtn').on('click',function(){
            App.ajax('GET','/backend/job/getTaskList',{},function(response){
                if(typeof response=='string'){
                    dialog.open({
                        title:'选择任务',
                        area: ['700px', '450px'],
                        content:response,
                        btn: ['确定','取消'],
                    },function(index){
                        var key = $('input[name=cmd_key]:checked').val();
                        if(!key){
                            dialog.msg('请选择需要执行的命令');
                        }
                        else{
                            $('#job_name').val($('#cmd_'+key).find('.doc').text());
                            $('#job_command').val($('#cmd_'+key).find('.cmd').text());
                            dialog.close(index);
                        }
                    });
                }
                else{
                    dialog.msg(response.msg,'error');
                }
            });
        });
    },
    setStatus: function (url,ids) {
        dialog.confirm({
            title:false,
            icon:-1,
            content: '设置任务(开启/关闭)状态',
            time:0,
            ok_btn: '开启',
            cancel_btn: '关闭',
            ok_callback:function(){
                App.ajax('PUT', url, {ids:ids,status:1}, function (response) {
                    if (response.status) {
                        $.each(ids,function (i,id){
                            $('#tr_'+id).find('td:last-child').html('<span class="label label-sm label-success">正常</span>');
                        })
                        dialog.msg('设置成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            },
            cancel_callback:function(){
                App.ajax('PUT', url, {ids:ids,status:2}, function (response) {
                    if (response.status) {
                        $.each(ids,function (i,id){
                            $('#tr_'+id).find('td:last-child').html('<span class="label label-sm label-default">关闭</span>');
                        })
                        dialog.msg('设置成功', 'success');
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
    exec: function (url,ids) {
        dialog.confirm({
            content:'确定手动执行该任务吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('POST', url, {id:ids}, function (response) {
                    if (response.status) {
                        dialog.msg('提交成功', 'success',1000,function(){
                            window.location.reload();
                        });
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
                ok_btn:'继续添加定时任务',
                cancel_btn:'返回定时任务列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/job/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/job/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    logs:function(url,ids){
        window.location.href = App.createUrl('/backend/job/logs/'+ids[0]);
    },
    delete: function (url, ids) {
        jobJs.delete(url,ids[0]);
    },
    exec: function (url, ids) {
        jobJs.exec(url,ids);
    },
    setStatus: function (url, ids) {
        jobJs.setStatus(url,ids);
    },
}
jQuery(document).ready(function () {
    jobJs.init();
});