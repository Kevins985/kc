var roleJs = roleJs || {};
roleJs = {
    init: function () {
        this.initFormScript();
        if (initScriptData.initData) {
            global.initData(initScriptData.initData);
        }
        if ($("#menu_container").get(0)) {
            this.initTreeMenus();
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
        $('#setMenusSubmit').on('click',function (){
            roleJs.saveRoleMenus();
        });
    },
    nodesArr:[],
    getCheckTreeNodes:function(nodes){
        var self = this;
        $.each(nodes, function (i, obj) {
            if (obj.checked) {
                self.nodesArr.push(obj.id);
                if (obj.children) {
                    self.getCheckTreeNodes(obj.children);
                }
            }
        });
    },
    initTreeMenus: function () {
        var code;
        function setCheck() {
            var zTree = $.fn.zTree.getZTreeObj("menuTree"),
                    type = {"Y": 'p' + 's', "N": 'p' + 's'};
            zTree.setting.check.chkboxType = type;
            showCode('setting.check.chkboxType = { "Y" : "' + type.Y + '", "N" : "' + type.N + '" };');
        }
        function showCode(str) {
            if (!code) code = $("#code");
            code.empty();
            code.append("<li>" + str + "</li>");
        }
        var role_id = 0;
        if ($('#role_id').get(0)) {
            role_id = $('#role_id').val();
        }
        var zNodes = [];
        window.setTimeout(function () {
            App.ajax( 'GET', '/backend/menu/getTreeMenus',{role_id: role_id}, function (result) {
                if (result.status) {
                    zNodes = result.data;
                    $.fn.zTree.init($("#menuTree"), {check: {enable: true}, data: {simpleData: {enable: true}}}, zNodes);
                    setCheck();
                }
            }, 'json');
        }, 500);
    },
    saveRoleMenus: function () {
        this.nodesArr = [];
        var zTree = $.fn.zTree.getZTreeObj("menuTree");
        this.getCheckTreeNodes(zTree.getNodes());
        if (this.nodesArr.length == 0) {
            dialog.msg('请选择角色对应的菜单权限', 'error');
        }
        else{
            var data = {
                role_id:$('#role_id').val(),
                menu_ids:roleJs.nodesArr
            };
            App.ajaxLoading('POST', '/backend/role/setMenus', data, function (response) {
                if (response.status) {
                    dialog.msg('保存成功', 'success');
                } else {
                    dialog.msg(response.msg, 'error');
                }
            },'数据保存中');
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
                ok_btn:'继续添加角色',
                cancel_btn:'返回角色列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/role/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/role/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    updateSubmit:function(response){
        if(response.status){
            dialog.msg("修改成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        roleJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    roleJs.init();
});