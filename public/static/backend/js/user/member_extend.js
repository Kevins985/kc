var memberExtendJs = memberExtendJs || {};
memberExtendJs = {
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
    add:function(url,params){
        dialog.confirm({
            content:'确定需要添加该资产数据吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT',url,params,function(response){
                    if (response.status) {
                        dialog.msg('操作成功','success',1000,function(){
                            window.location.reload();
                        });
                    }
                    else {
                        dialog.msg(response.msg,'error');
                    }
                });
            }
        });
    },
    minus:function(url,params){
        dialog.confirm({
            content:'确定需要扣除该资产数据吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT',url,params,function(response){
                    if (response.status) {
                        dialog.msg('操作成功','success',1000,function(){
                            window.location.reload();
                        });
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
    add:function(url,ids){
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">类型</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <label><input type="radio" name="type" value="point" checked="" />积分</label>\
                                <label><input type="radio" name="type" value="wallet" />钱包余额</label>\
                                <label><input type="radio" name="type" value="profit" />收益金额</label>\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">资产数字</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="number" style="width:200px;" class="form-control"  id="num" />\
                            </div>\
                        </div>\
                     </div>';
        dialog.open({
            title:'添加用户资产数据',
            content:html,
            area: ['380px', '230px'],
            btn:['确定', '取消'],
        },function(index){
            var num = $('#num').val();
            if($.trim(num)==''){
                dialog.msg('请输入资产数据','error');
            }
            else{
                var params = {userid:ids[0],num:num,type:$('input[name=type]:checked').val()}
                memberExtendJs.add(url,params);
            }
        });
    },
    minus:function(url,ids){
        var html = '<div class="profile-user-info pt-25">\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">类型</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <label><input type="radio" name="type" value="point" checked="" />积分</label>\
                                <label><input type="radio" name="type" value="wallet" />钱包余额</label>\
                                <label><input type="radio" name="type" value="profit" />收益金额</label>\
                            </div>\
                        </div>\
                        <div class="profile-info-row" style="border:none;">\
                            <div class="profile-info-name" style="width:100px;line-height:30px;">资产数字</div>\
                            <div class="profile-info-value" style="margin-left:100px;">\
                                <input type="number" style="width:200px;" class="form-control"  id="num" />\
                            </div>\
                        </div>\
                     </div>';
        dialog.open({
            title:'扣除用户资产数据',
            content:html,
            area: ['380px', '230px'],
            btn:['确定', '取消'],
        },function(index){
            var num = $('#num').val();
            if($.trim(num)==''){
                dialog.msg('请输入资产数据','error');
            }
            else{
                var params = {userid:ids[0],num:num,type:$('input[name=type]:checked').val()}
                memberExtendJs.minus(url,params);
            }
        });
    },
    logs:function(url,ids){
        window.location.href = url+'/'+ids[0];
    }
}
jQuery(document).ready(function () {
    memberExtendJs.init();
});