var adminJs = adminJs || {};
adminJs = {
    init: function () {
        this.initFormScript();
        if(initScriptData.initData){
            global.initData(initScriptData.initData);
        }
        if($('.bs-select').get(0)){
            $('.bs-select').selectpicker({
                iconBase: 'fa',
                tickIcon: 'fa-check'
            });
        }
        $('.grant_user_id').on('click',function(){
            var userid = $(this).val();
            var user_check = $(this).prop('checked');
            $('.user'+userid).each(function(i,el){
                $(el).prop('checked',user_check);
            });

        });
        if ($("#menu_container").get(0)) {
            this.initTreeMenus();
        }
        $('#setUserGrantSubmit').on('click',function (){
            adminJs.setUserGrant();
        });
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
        var userid = 0;
        if ($('#userid').get(0)) {
            userid = $('#userid').val();
        }
        var zNodes = [];
        window.setTimeout(function () {
            App.ajax( 'GET', '/backend/menu/getTreeMenus',{userid: userid}, function (result) {
                if (result.status) {
                    zNodes = result.data;
                    $.fn.zTree.init($("#menuTree"), {check: {enable: true}, data: {simpleData: {enable: true}}}, zNodes);
                    setCheck();
                }
            }, 'json');
        }, 500);
    },
    modifyPassword:function(userid,callback){
        var data = {
            userid:userid,
            new_pass:$('#new_pass').val(),
        };
        App.ajax('PUT','/backend/admin/modifyUserPwd',data,function(response){
            if(response.status){
                dialog.msg('修改成功','success');
                if(callback) callback();
            }
            else{
                dialog.msg(response.msg,'error');
            }
        });
    },
    viewModifyPassowrd(url,id){
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">新密码</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="password" style="width:200px;" class="form-control"  id="new_pass" />\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">确认密码</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="password" style="width:200px;" class="form-control" id="confirm_pass" />\
                            </div>\
                        </div>\
                     </div>';
        dialog.open({
            title:'修改用户密码',
            content:html,
            area: ['380px', '230px'],
            btn:['确定', '取消'],
        },function(index){
            if($.trim($('#new_pass').val())==''){
                dialog.msg('请输入新密码','error');
            }
            else if($.trim($('#confirm_pass').val())==''){
                dialog.msg('请输入确认密码','error');
            }
            else if($('#confirm_pass').val()!=$('#new_pass').val()){
                dialog.msg('两次输入密码不一致','error');
            }
            else{
                adminJs.modifyPassword(id,function(){
                    dialog.close(index);
                });
            }
        });
    },
    setUserGrant:function(response){
        this.nodesArr = [];
        var zTree = $.fn.zTree.getZTreeObj("menuTree");
        this.getCheckTreeNodes(zTree.getNodes());
        if (this.nodesArr.length == 0) {
            dialog.msg('请选择角色对应的菜单权限', 'error');
        }
        else{
            var data = {
                userid:$('#userid').val(),
                menu_ids:adminJs.nodesArr,
                grant_user_id:[],
                grant_store_name:[],
            };
            $('.grant_user_id:checked').each(function(i,el){
                data.grant_user_id.push($(this).val());
            })
            $('.grant_store_name:checked').each(function(i,el){
                data.grant_store_name.push($(this).val());
            })
            App.ajaxLoading('PUT', '/backend/admin/setUserGrant', data, function (response) {
                if (response.status) {
                    dialog.msg('保存成功', 'success');
                } else {
                    dialog.msg(response.msg, 'error');
                }
            },'数据保存中');
        }
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
                ok_btn:'继续添加管理员',
                cancel_btn:'返回管理员列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/admin/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/admin/list');
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
    modifyPasswordSubmit:function (response){
        if(response.status){
            dialog.msg("修改成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    saveUserInfoSubmit:function (response){
        if(response.status){
            dialog.msg("保存成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete:function(url,ids){
        adminJs.delete(url,ids);
    },
    modifyUserPwd:function(url,ids){
        adminJs.viewModifyPassowrd(url,ids[0]);
    },
    upload:function(response){
        global.setUploadData('photo_url',response);
    },
}
jQuery(document).ready(function () {
    adminJs.init();
});