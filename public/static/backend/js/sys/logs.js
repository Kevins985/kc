var logsJs = logsJs || {};
logsJs = {
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
    getLogsData:function (id,type){
        if(type=='show'){
            if($('[pid='+id+']').get(0)){
                $('[pid='+id+']').show();
            }
            else if($('#'+id).data('empty')!='1'){
                App.ajax('GET','/backend/logs/getOperationData', {id:id}, function (response) {
                    if(response.status){
                        var html = '<tr pid="'+id+'"><td colspan="10" style="text-align:left;padding-left:5px;">'+response.data.request_data+'</td></tr>';
                        $('#'+id).after(html);
                    }
                    else{
                        $('#'+id).data('empty',1);
                    }
                });
            }
        }
        else{
            $('[pid='+id+']').hide();
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
    delete: function (url, ids) {
        logsJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    logsJs.init();
});