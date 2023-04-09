var pointOrderJs = pointOrderJs || {};
pointOrderJs = {
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
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/order/getOrderInfo',{id:id}, function (response) {
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
        pointOrderJs.delete(url,ids);
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
                        pointOrderJs.dispatched(url,data);
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
    pointOrderJs.init();
});