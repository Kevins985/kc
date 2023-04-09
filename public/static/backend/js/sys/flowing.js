var flowingJs = flowingJs || {};
flowingJs = {
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
        $('input[name="flow_random"]').on('click',function(){
            if($(this).val()==1){
                $('#flow_start_val').val(0);
                $('#normalCreate').addClass('hide');
            }
            else{
                $('#flow_start_val').val(1);
                $('#normalCreate').removeClass('hide');
            }
        });
        $('#exampleBtn').on('click',function (){
            var flow_rule = $('[name=flow_rule]:checked').val();
            var flow_prefix = $('#flow_prefix').val();
            var flow_start_val = parseInt($('#flow_start_val').val());
            var flow_digit = parseInt($('#flow_digit').val());
            var flow_suffix = $('#flow_suffix').val();
            var is_random = $('input[name="flow_random"]:checked').val();
            if($.trim(flow_prefix)==''){
                dialog.msg('流水前缀不能为空','error');
            }
            var sn = flow_prefix;
            var d = new Date();
            if(flow_rule==1){
                sn+=(d.format("yyyy"));
            }
            else if(flow_rule==2){
                sn+=(d.format("yyyyMM"));
            }
            else if(flow_rule==3){
                sn+=(d.format("yyyyMMdd"));
            }
            else if(flow_rule==4){
                sn+=(d.format("yyyyMMddhh"));
            }
            else if(flow_rule==5){
                sn+=(d.format("yyyyMMddhhmm"));
            }
            else if(flow_rule==6){
                sn+=(d.format("yyyyMMddhhmmss"));
            }
            if(is_random==1){
                global.getRandomStr(0,flow_digit,function (str){
                    sn+=str;
                    sn+=flow_suffix;
                    $('#flow_sn').val(sn);
                });
            }
            else{
                sn+= global.getPrefixZero(flow_start_val,flow_digit);
                sn+=flow_suffix;
                $('#flow_sn').val(sn);
            }
        });
    },
    deleteTr:function (obj){
        if($('.table tbody tr').length>1){
            $(obj).parents('tr').remove();
        }
        else{
            dialog.msg('必须保留一个','error');
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
                ok_btn:'继续添加流水号',
                cancel_btn:'返回流水号列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/flowing/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/flowing/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        flowingJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    flowingJs.init();
});