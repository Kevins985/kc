var memberJs = memberJs || {};
memberJs = {
    init: function () {
        this.initFormScript();
        if(initScriptData.initData){
            global.initData(initScriptData.initData);
        }
        if($('#tags').get(0)){
            $("#tags").select2();
            if(initScriptData.initData.tags){
                var val = JSON.parse(initScriptData.initData.tags);
                $("#tags").select2("val", val);
            }
        }
        if($('.bs-select').get(0)){
            $('.bs-select').selectpicker({
                iconBase: 'fa',
                tickIcon: 'fa-check'
            });
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
    setMemberRemark(id,remark){
        App.ajax('PUT','/backend/member/setRemark',{id:id,remark:remark}, function (response) {
            if (response.status) {
                dialog.msg('设置成功', 'success',1000,function (){
                    $('#memberRemark').text(remark);
                    dialog.closeAll();
                });
            } else {
                dialog.msg(response.msg, 'error');
            }
        });
    },
    initTreeMembers: function (user_id) {
        var zNodes = [];
        window.setTimeout(function () {
            App.ajax( 'GET', '/backend/member/getTreeMembers',{user_id: user_id}, function (result) {
                if (result.status) {
                    zNodes = result.data;
                    $.fn.zTree.init($("#memberTree"), {check: {enable: false}, data: {simpleData: {enable: true}}}, zNodes);
                }
            }, 'json');
        }, 500);
    },
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/member/getMemberInfo',{id:id}, function (response) {
                if(typeof response == 'object' && !response.status){
                    dialog.msg(response.msg,'error');
                }
                else{
                    $('tr.child').remove();
                    $('#'+id).after(response);
                    $('.setRemark').on('click',function(){
                        var user_id = $(this).data('id');
                        var remark = $(this).data('remark');
                        dialog.open({
                            title:'设置备注',
                            content:'<textarea class="form-control" maxlength="30" id="remark" rows="5">'+remark+'</textarea>',
                            area:['450px','220px'],
                            btn: ['确定','取消'],
                        },function(index){
                            memberJs.setMemberRemark(user_id,$('#remark').val());
                        });
                    });
                    memberJs.initTreeMembers(id);
                }
            });
        }
        else{
            $('[pid='+id+']').hide();
        }
    },
    modifyPassword:function(userid){
        var data = {
            userid:userid,
            new_pass:$('#new_pass').val(),
            pass_type:$('input[name=pass_type]:checked').val()
        };
        App.ajax('PUT','/backend/member/modifyUserPwd',data,function(response){
            if(response.status){
                dialog.msg('修改成功','success',1000,function(){
                    dialog.closeAll();
                });
            }
            else{
                dialog.msg(response.msg,'error');
            }
        });
    },
    viewModifyPassowrd(url,id){
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">类型</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <label><input type="radio" name="pass_type" checked="" value="login" />登陆密码</label>\
                                <label><input type="radio" name="pass_type" value="pay" />支付密码</label>\
                            </div>\
                        </div>\
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
            area: ['380px', '290px'],
            btn:['确定', '取消']
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
                memberJs.modifyPassword(id);
            }
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
                               $('#info_'+$(el).val()).remove();
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
    viewAddUserRecharge:function(url,id){
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">登陆密码</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="password" style="width:250px;" class="form-control" placeholder="登陆密码"  id="password" />\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">充值金额</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="number" style="width:250px;" class="form-control" id="money" placeholder="美金" />\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">描述</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <textarea class="form-control" style="width:250px;" placeholder="操作的理由" rows="3" id="descr" ></textarea>\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">付款凭证</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="hidden" id="attachment" value="" />\
                                <input type="file" name="file" class="uploadImageBtn" data-type="attachment" />\
                            </div>\
                        </div>\
                     </div>';
        dialog.open({
            title:'充值申请',
            content:html,
            area: ['450px', '350px'],
            btn:['确定', '取消'],
        },function(index){
            var data = {
                user_id:id,
                password:$('#password').val(),
                money:$('#money').val(),
                descr:$('#descr').val(),
                attachment:$('#attachment').val(),
            };
            if($.trim(data.password)==''){
                dialog.msg('请输入登陆密码','error');
            }
            else if(data.money==''){
                dialog.msg('请输入充值金额','error');
            }
            else if(data.descr==''){
                dialog.msg('请输入操作理由','error');
            }
            else if(data.attachment==''){
                dialog.msg('请输入充值附件','error');
            }
            else{
                memberJs.addUserRecharge(url,data);
            }
        });
        $('.uploadImageBtn').on('change', function () {
            global.checkPic(this,'attachment');
        });
    },
    addUserRecharge:function(url,data){
        App.ajax('POST',url,data,function(response){
            if(response.status){
                dialog.msg('添加成功','success',2000,function(){
                    dialog.closeAll();
                });
            }
            else{
                dialog.msg(response.msg,'error');
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
                ok_btn:'继续添加会员',
                cancel_btn:'返回会员列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/member/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/member/list');
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
    addUserRecharge:function(url,ids){
        memberJs.viewAddUserRecharge(url,ids[0]);
    },
    delete:function(url,ids){
        memberJs.delete(url,ids);
    },
    modifyUserPwd:function(url,ids){
        memberJs.viewModifyPassowrd(url,ids[0]);
    },
    upload:function(response){
        if(response.data.type=='attachment'){
            $('#attachment').val(response.data.file_md5).attr('val',response.data.file_md5);
            dialog.loading("close");
        }
        else{
            global.setUploadData('photo_url',response);
        }
    },
}
jQuery(document).ready(function () {
    memberJs.init();
});