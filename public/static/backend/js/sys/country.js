var countryJs = countryJs || {};
countryJs = {
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
    getCountryList:function(params,callback){
        App.ajax('GET','/backend/country/getCountryList', params, function (response) {
            callback(response);
        });
    },
    countrySelect:function(type){
        if(typeof type=='object'){
            var is_check = $(type).prop('checked');
            var code = $(type).val();
            var name = $(type).data('name');
            if($('a[data-code='+code+']').get(0) && is_check==false){
                $('a[data-code='+code+']').remove();
            }
            else if(!$('a[data-code='+code+']').get(0) && is_check){
                html= $('<a href="javascript:;" data-code="'+code+'" data-name="'+name+'" class="icon-btn" style="height: 40px;">\
                          <span>'+name+'</span>\
                          <span class="badge badge-danger"><i class="fa fa-close"></i></span>\
                      </a>');
                $('.fa-close',html).on('click',function(){
                    var select = $(this).parents('a');
                    var code = select.data('code');
                    select.remove();
                    $('#country_'+code).prop('checked',false);
                });
                $('#selCountryBox').append(html);

            }
        }
        else{
            var data = [];
            var html = '';
            $('#countryModalBox').find('.check').each(function (i,el){
                if($(el).prop('checked')){
                    data.push({
                        code:$(el).val(),
                        name:$(el).data('name')
                    })
                    html+= '<a href="javascript:;" data-code="'+$(el).val()+'" data-name="'+$(el).data('name')+'" class="icon-btn" style="height: 40px;">\
                          <span>'+$(el).data('name')+'</span>\
                          <span class="badge badge-danger"><i class="fa fa-close"></i></span>\
                      </a>';
                }
            });
            if(type=='set'){
                $('#selCountryBox').html(html);
                $('.fa-close','#selCountryBox').on('click',function(){
                    var select = $(this).parents('a');
                    var code = select.data('code');
                    select.remove();
                    $('#country_'+code).prop('checked',false);
                });
            }
            return data;
        }
    },
    showCountryBox:function (checkeds,callback=false){
        countryJs.getCountryList({type:'modal'},function (response) {
            dialog.open({
                title:'指定国家',
                content:response,
                area: ['800px', '450px'],
                btn: ['确定','取消'],
            },function (index){
                var data = countryJs.countrySelect('get');
                if(data.length==0){
                    dialog.msg('请选择国家');
                }
                else{
                    if(typeof callback == 'function'){
                        callback(data);
                    }
                    else if(typeof callback == 'string'){
                        eval(callback+'(data)');
                    }
                    dialog.close(index);
                }
            });
            if(checkeds.length>0){
                $.each(checkeds,function(i,code){
                    $('#country_'+code).prop('checked',true);
                });
                countryJs.countrySelect('set');
            }
            $('.check').on('click',function(){
                countryJs.countrySelect(this);
            });
            $('.checkAll').on('click',function(){
                var is_check = $(this).prop('checked');
                var id = $(this).data('id');
                $('.continent'+id).each(function(i,el){
                   $(el).prop('checked',is_check);
                });
                countryJs.countrySelect('set');
            });
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
                ok_btn:'继续添加国家',
                cancel_btn:'返回国家列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/country/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/country/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        countryJs.delete(url,ids[0]);
    },
    setLangTran:function (url,ids){
        window.location.href = url+'/'+ids[0];
    },
}
jQuery(document).ready(function () {
    countryJs.init();
});