var orderJs = orderJs || {};
orderJs = {
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
    getProjectOrderData:function (params){
        App.ajax('GET','/backend/project/getOrderMembers',params, function (response) {
            if(typeof response == 'object' && !response.status){
                dialog.msg(response.msg,'error');
            }
            else{
                dialog.open({
                    title:'查看数据',
                    content:response,
                    area:['700px','550px']
                });
            }
        });
    },
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/order/getOrderInfo',{id:id}, function (response) {
                if(typeof response == 'object' && !response.status){
                    dialog.msg(response.msg,'error');
                }
                else{
                    $('tr.child').remove();
                    $('#'+id).after(response);
                    $('.projectOrderBtn').on('click',function(){
                        orderJs.getProjectOrderData({
                            type:$(this).data('type'),
                            val:$(this).data('val'),
                        });
                    });
                }
            });
        }
        else{
            $('[pid='+id+']').hide();
        }
    },
    verifyOrder:function(url,data){
        App.ajax('POST',url,data,function(response){
            if (response.status) {
                dialog.msg('操作成功','success',1000,function(){
                    window.location.reload();
                });
            }
            else {
                dialog.msg(response.msg,'error');
            }
        });
    },
    dispatched:function(url,data){
        dialog.confirm({
            content:'确定需要发货吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT',url,data,function(response){
                    if (response.status) {
                        dialog.msg('发货成功','success',1000,function(){
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
}
var appCallback = {
    delete:function(url,ids){
        orderJs.delete(url,ids);
    },
    dispatched:function(url,ids){
        App.ajax('GET',url,{id:ids[0]},function(response){
            if(typeof response=='string'){
                dialog.open({
                    title:'发货',
                    content:response,
                    area: ['450px', '300px'],
                    btn:['确定', '取消'],
                },function(index){
                    var data = {
                        order_id:ids[0],
                        tracking_name:$('#tracking_name').val(),
                        tracking_number:$('#tracking_number').val(),
                        tracking_url:$('#tracking_url').val(),
                    };
                    if($.trim(data.tracking_name)==''){
                        dialog.msg('请输入快递公司名称','error');
                    }
                    else if(data.tracking_number==''){
                        dialog.msg('请输入快递单号','error');
                    }
                    else{
                        orderJs.dispatched(url,data);
                    }
                });
            }
            else if(!response.status){
                dialog.msg(response.msg);
            }
        });
    },
    verifyOrder:function(url,ids){
        App.ajax('GET',url,{},function(response){
            if(typeof response=='string'){
                dialog.open({
                    title:'审核订单',
                    content:response,
                    area: ['450px', '300px'],
                    btn:['确定', '取消'],
                },function(index){
                    var data = {
                        id:ids,
                        status:$('input[name=verify_status]:checked').val(),
                        remark:$('#verify_remark').val()
                    };
                    if(data.status=='refused' && data.remark==''){
                        dialog.msg('请输入拒绝的备注','error');
                    }
                    else{
                        orderJs.verifyOrder(url,data);
                    }
                });
            }
            else if(!response.status){
                dialog.msg(response.msg);
            }
        });
    },
}
jQuery(document).ready(function () {
    orderJs.init();
});