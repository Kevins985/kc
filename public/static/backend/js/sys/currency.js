var currencyJs = currencyJs || {};
currencyJs = {
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
        $('#addExchangeBtn').on('click',function (){
            currencyJs.addExchange();
        });
        $('.editExchangeBtn').on('click',function(){
            currencyJs.editExchange(this);
        });
        $('.saveExchangeBtn').on('click',function(){
            var id = $(this).data('id');
            var rate_dom = $(this).parents('tr').find('.rate');
            if(rate_dom.val()==''){
                dialog.msg('请输入汇率的值');
                rate_dom.focus();
            }
            else{
                currencyJs.saveExchange({type:'update',data:{id:id,currency_rate:rate_dom.val()}},function(response){
                    if(response.status){
                        dialog.msg('修改成功','success',2000,function(){
                            window.location.reload();
                        })
                    }
                    else{
                        dialog.msg(response.msg,'error');
                    }
                })
            }
        });
        $('.deleteExchangeBtn').on('click',function(){
            currencyJs.deleteExchange(this);
        });
    },
    addExchange:function(){
        if($('.rate').get(0)){
            dialog.msg('请先保存编辑中的汇率数据');
        }
        else{
            var options = '<option value="0">请选择</option>';
            $.each(initScriptData.currency,function(i,el){
                options+='<option value="'+el.currency_code+'">'+el.currency_name+'</option>';
            });
            var data = {
                currency_id:$('#currency_id').val(),
                current_name:$('#currency_id').data('name'),
                current_currency:$('#currency_id').data('code'),
            };
            var html = '<div class="profile-user-info pt-25">\
                            <div class="profile-info-row" style="border:none;">\
                                <div class="profile-info-name" style="width:100px;line-height:30px;">当前币种</div>\
                                <div class="profile-info-value choiceBtnBox" style="margin-left:100px;">\
                                    <input type="text" style="width:200px;" readonly="" class="form-control"  value="'+data.current_name+'" />\
                                </div>\
                            </div>\
                            <div class="profile-info-row" style="border:none;">\
                                <div class="profile-info-name" style="width:100px;line-height:30px;">目标币种</div>\
                                <div class="profile-info-value choiceBtnBox" style="margin-left:100px;">\
                                    <select style="width:200px;" class="form-control" id="target_currency" >'+options+'</select>\
                                </div>\
                            </div>\
                            <div class="profile-info-row" style="border:none;">\
                                <div class="profile-info-name" style="width:100px;line-height:30px;">汇率</div>\
                                <div class="profile-info-value" style="margin-left:100px;">\
                                    <input type="number" step="0.01" style="width:200px;" class="form-control" id="currency_rate" />\
                                </div>\
                            </div>\
                         </div>';
            dialog.open({
                title:'设置汇率',
                content:html,
                area: ['400px', '300px'],
                btn: ['确定','取消'],
            },function(index){
                var target_currency = $('#target_currency').val();
                var currency_rate = $('#currency_rate').val();
                if(target_currency=='0'){
                    dialog.msg('请选择目标币种');
                }
                else if(currency_rate==''){
                    dialog.msg('请输入汇率');
                    $('#calc_weight').focus();
                }
                else{
                    data['target_currency'] = target_currency;
                    data['currency_rate'] = currency_rate;
                    data['target_name'] = $('#target_currency').find('option:selected').text();
                    currencyJs.saveExchange({type:'add',data:data},function(response){
                        if(response.status){
                            dialog.msg('添加成功','success',2000,function(){
                                window.location.reload();
                            })
                        }
                        else{
                            dialog.msg(response.msg,'error');
                        }
                    })
                }
            });
        }
    },
    deleteExchange:function(obj){
        var id = $(obj).data('id');
        dialog.confirm({
            content:'确定需要删除该数据吗?',
            icon:3,
            ok_callback:function(){
                currencyJs.saveExchange({type:'delete',data:id},function(response){
                    if(response.status){
                        dialog.msg('删除成功','success',2000,function(){
                            $(obj).parents('tr').remove();
                        })
                    }
                    else{
                        dialog.msg(response.msg,'error');
                    }
                })
            }
        });
    },
    editExchange:function(obj){
        var rate_dom = $(obj).parents('tr').find('.currency_rate');
        var rate = rate_dom.text();
        rate_dom.html('<input type="number" class="form-control rate" style="width:100%;" value="'+rate+'">');
        $(obj).parent().find('.saveExchangeBtn').removeClass('hide');
        $(obj).remove();
    },
    saveExchange:function(data,callback){
        App.ajax('POST','/backend/currency/setCurrencyExchange', data, function (response) {
            if(callback) callback(response);
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
                ok_btn:'继续添加币种',
                cancel_btn:'返回币种列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/currency/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/currency/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        currencyJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    currencyJs.init();
});