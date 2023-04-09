var withdrawOrderJs = withdrawOrderJs || {};
withdrawOrderJs = {
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
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/withdrawOrder/getOrderInfo',{id:id}, function (response) {
                if(typeof response == 'object' && !response.status){
                    dialog.msg(response.msg,'error');
                }
                else {
                    $('tr.child').remove();
                    $('#' + id).after(response);
                }
            });
        }
        else{
            $('[pid='+id+']').hide();
        }
    },
    verifyOrder: function (url,ids) {
        var data = {
            id:ids,
            status:$('input[name=reply_status]:checked').val(),
            memo:$('#reply_content').val()
        };
        if(data.status==2 && data.memo==''){
            dialog.msg('请输入备注内容');
            $('#reply_content').focus();
        }
        else{
            App.ajax('PUT', url, data, function (response) {
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
    finishPayOrder: function (url,id) {
        var msg = '确定将该支付订单设置为已付款吗';
        dialog.confirm({
            content:msg,
            icon:3,
            ok_callback:function(){
                App.ajax('PUT', url, {id:id}, function (response) {
                    if (response.status) {
                        dialog.msg('操作成功', 'success',1000,function (){
                            window.location.reload();
                        });
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
}
var appCallback = {
    closeOrder:function (url,ids){
        dialog.confirm({
            content:'确定关闭该订单并释放冻结金额吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('DELETE', url, {id:ids[0]}, function (response) {
                    if (response.status) {
                        dialog.msg('操作成功', 'success',1000,function (){
                            window.location.reload();
                        });
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
    verifyOrder: function (url, ids) {
        var is_submit = true;
        $.each(ids,function (i,id){
            if($('#chk'+id).data('status')!=0){
                is_submit = false;
                var order_no = $('#chk'+id).data('order');
                dialog.msg(order_no+'不能进行审核');
            }
        });
        if(is_submit){
            var html= '<div class="row mt-15" id="withdrawBox">\
                        <div class="portlet-body form">\
                            <div class="form-group">\
                                <label class="col-md-3 text-right">操作:</label>\
                                <div class="col-md-8">\
                                    <div class="md-radio-inline mt-0">\
                                        <input type="radio" id="status_1" checked="" name="reply_status" value="1" class="md-radiobtn" />\
                                        <label for="status_1">\
                                            <span class="check"></span>\
                                            <span class="box"></span> 同意\
                                        </label>\
                                        <input type="radio" id="status_2" name="reply_status" value="2" class="md-radiobtn" />\
                                        <label for="status_2">\
                                            <span class="check"></span>\
                                            <span class="box"></span> 拒绝\
                                        </label>\
                                    </div>\
                                </div>\
                            </div>\
                            <div class="clearfix"></div>\
                            <div class="form-group mb-10">\
                                <label class="col-md-3 text-right">备注:</label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" id="reply_content" rows="3"></textarea>\
                                </div>\
                            </div>\
                        </div>\
                    </div>';
            dialog.open({
                title:'提现审核',
                content:html,
                area:['500px','280px'],
                btn: ['确定','取消'],
            },function(index){
                withdrawOrderJs.verifyOrder(url,ids);
            });
        }
    },
    finishOrder: function (url, ids) {
        var is_submit = true;
        $.each(ids,function (i,id){
            if($('#chk'+id).data('status')!=1){
                is_submit = false;
                var order_no = $('#chk'+id).data('order');
                dialog.msg(order_no+'不能设置为已打款');
            }
        });
        if(is_submit){
            withdrawOrderJs.finishPayOrder(url,ids[0]);
        }
    },
    upload:function(response){
        global.setUploadData('attachment',response);
    },
}
jQuery(document).ready(function () {
    withdrawOrderJs.init();
});