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
    getProjectOrderData:function (params){
        App.ajax('GET','/backend/project/getOrderMembers',params, function (response) {
            if(typeof response == 'object' && !response.status){
                dialog.msg(response.msg,'error');
            }
            else{
                dialog.open({
                    title:'查看数据',
                    content:response,
                    area:['750px','550px']
                });
            }
        });
    },
    initTreeNumbers: function (project_id) {
        var zNodes = [];
        window.setTimeout(function () {
            App.ajax( 'GET', '/backend/project/getTreeNumbers',{project_id: project_id}, function (result) {
                if (result.status) {
                    zNodes = result.data;
                    var setting = {
                        check: {enable: false},
                        data : {
                            simpleData: {enable: true},
                            key : { title : "code"}
                        },
                        callback: {
                            onClick: function (e){
                                projectJs.getProjectOrderData({
                                    type:'number',
                                    val:$(e.target).text(),
                                });
                            }
                        }
                    };
                    $.fn.zTree.init($("#projectNumberTree"),setting, zNodes);
                }
            }, 'json');
        }, 500);
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
                    $('.projectOrderBtn').on('click',function(){
                        projectJs.getProjectOrderData({
                            type:$(this).data('type'),
                            val:$(this).data('val'),
                        });
                    });
                    projectJs.initTreeNumbers(id);
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
    addProjectNumber:function (url, ids) {
        dialog.confirm({
            content:'确定为该项目添加一个排期号',
            icon:1,
            ok_callback:function(){
                App.ajax('POST', url, {project_id:ids[0]}, function (response) {
                    if (response.status) {
                        dialog.msg('添加成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
    delete: function (url, ids) {
        projectJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    projectJs.init();
});