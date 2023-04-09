var rechargeOrderJs = rechargeOrderJs || {};
rechargeOrderJs = {
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
    verifyOrder: function (url,ids) {
        var data = {
            id:ids,
            status:$('input[name=reply_status]:checked').val(),
            descr:$('#reply_content').val()
        };
        if(data.status==2 && data.memo==''){
            dialog.msg('请输入备注内容');
            $('#reply_content').focus();
        }
        else{
            App.ajax('POST', url, data, function (response) {
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
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/rechargeOrder/getOrderInfo',{id:id}, function (response) {
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
}
var appCallback = {
    delete:function(url,ids){
        rechargeOrderJs.delete(url,ids);
    },
    verify: function (url, ids) {
        var is_submit = true;
        $.each(ids,function (i,id){
            if($('#chk'+id).data('status')!=0){
                is_submit = false;
                var order_no = $('#chk'+id).data('order');
                dialog.msg(order_no+'不能进行审核');
            }
        });
        if(is_submit){
            App.ajax('GET',url,{id:ids[0]},function(response){
                dialog.open({
                    title:'充值审核',
                    content:response,
                    area:['500px','450px'],
                    btn: ['确定','取消'],
                },function(index){
                    rechargeOrderJs.verifyOrder(url,ids);
                });
            });
        }
    },
}
jQuery(document).ready(function () {
    rechargeOrderJs.init();
});