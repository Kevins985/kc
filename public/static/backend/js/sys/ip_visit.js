var ipVisitJs = ipVisitJs || {};
ipVisitJs = {
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
        $('.setLimitTypeBtn').on("confirmed.bs.confirmation",function(){
            ipVisitJs.setLimitStatus(this,'yes');
        }).on("canceled.bs.confirmation",function(){
            ipVisitJs.setLimitStatus(this,'no');
        });
    },
    setLimitStatus:function (obj,type){
        var id = $(obj).data('id');
        var status = $(obj).data('status');
        var msg = '保存';
        if(status==1){                    //黑名单
            status = (type=='yes'?0:2);
            msg = (type=='yes'?'不设置':'白名单');
        }
        else if(status==2){               //白名单
            status = (type=='yes'?0:1);
            msg = (type=='yes'?'不设置':'黑名单');
        }
        else{              //未设置
            status = (type=='yes'?2:1);
            msg = (type=='yes'?'白名单':'黑名单');
        }
        dialog.confirm({
            content:'确定将该商品状态设置为'+msg+'吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT','/backend/ipVisit/setLimitStatus', {id:id,status:status}, function (response) {
                    if (response.status) {
                        dialog.msg('操作成功', 'success',1000,function(){
                            window.location.reload();
                        });
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
                            if ($.inArray($(el).val(),ids) !== -1) {
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
    addIpBlacklist:function (url,params){
        App.ajax('POST', url, params, function (response) {
            if (response.status) {
                dialog.msg('添加成功', 'success',1000,function (){
                    window.location.reload();
                });
            } else {
                dialog.msg(response.msg, 'error');
            }
        });
    }
}
var appCallback = {
    delete: function (url, ids) {
        ipVisitJs.delete(url,ids);
    },
    add: function (url, ids) {
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">类型</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <label><input type="radio" name="limit_type" checked="" value="1" />黑名单</label>\
                                <label><input type="radio" name="limit_type" value="2" />白名单</label>\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">IP地址</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="text" style="width:200px;" class="form-control"  id="client_ip" />\
                            </div>\
                        </div>\
                     </div>';
        dialog.open({
            title:'添加IP名单',
            content:html,
            area: ['380px', '230px'],
            btn:['确定', '取消'],
        },function(index){
            var client_ip = $('#client_ip').val();
            if($.trim(client_ip)==''){
                dialog.msg('请输入IP地址','error');
            }
            else{
                var params = {ip:client_ip,status:$('input[name=limit_type]:checked').val()}
                ipVisitJs.addIpBlacklist(url,params);
            }
        });
    },
}
jQuery(document).ready(function () {
    ipVisitJs.init();
});