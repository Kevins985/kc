var projectJs = projectJs || {};
projectJs = {
    editor:null,
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
    setProjectStatus:function (obj,type){
        var id = $(obj).data('id');
        var status = $(obj).data('status');
        var msg = '保存';
        if(status==1){                    //进行
            status = (type=='yes'?0:2);
            msg = (type=='yes'?'待审核':'下架');
        }
        else if(status==2){               //下架
            status = (type=='yes'?1:0);
            msg = (type=='yes'?'上架':'待审核');
        }
        else if(status==0){              //待审核
            status = (type=='yes'?1:2);
            msg = (type=='yes'?'上架':'下架');
        }
        else{
            status = 1;
            msg = '上架';
        }
        dialog.confirm({
            content:'确定将该项目状态设置为'+msg+'吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT','/backend/project/setStatus', {id:id,status:status}, function (response) {
                    if (response.status) {
                        $(obj).remove();
                        dialog.msg('操作成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/project/getProjectInfo',{id:id}, function (response) {
                if(typeof response == 'object' && !response.status){
                    dialog.msg(response.msg,'error');
                }
                else{
                    $('tr.child').remove();
                    $('#'+id).after(response);
                }
            });
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
        global.setUploadData('image',response);
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加项目',
                cancel_btn:'返回项目列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/project/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/project/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        projectJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    projectJs.init();
});