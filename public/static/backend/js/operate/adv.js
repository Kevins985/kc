var advJs = advJs || {};
advJs = {
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
        $('#advSubmitBtn').on('click',function (){
            var status = $('#status').prop('checked')?1:0;
            $('#status').val(status);
            if(!$('#adv_image').get(0) || $('#adv_image').val()==''){
                dialog.msg('请上传广告图片');
                return false;
            }
        });
        $('input[name=from_term]').on('click',function (){
            var from_term = $(this).val();
            advJs.getAdvTypeOrLocation(from_term);
        });
    },
    getAdvTypeOrLocation:function (from_term){
        var type = '<option value="">请选择</option>';
        var location = '<option value="">请选择</option>';
        App.ajax('GET','/backend/adv/getAdvTypeOrLocation',{'from_term':from_term},function (response){
            if(response.status){
                $.each(response.data.typeList,function (i,el){
                    type+='<option value="'+el.type_id+'">'+el.type_name+'</option>'
                })
                $.each(response.data.locationList,function (i,el){
                    location+='<option value="'+el.location_id+'">'+el.location_name+'</option>'
                })
                $('#type_id').html(type);
                $('#location_id').html(location);
            }
            else{
                dialog.msg(response.msg,'error');
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
    upload:function(response){
        global.setUploadData('adv_image',response);
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加广告',
                cancel_btn:'返回广告列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/adv/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/adv/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        advJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    advJs.init();
});