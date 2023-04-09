var menusJs = menusJs || {};
menusJs = {
    init: function () {
        this.initFormScript();
        if (initScriptData.initData) {
            global.initData(initScriptData.initData);
        }
    },
    initFormScript: function () {
        $('#parent_id').select2();
        if(initScriptData.initData && initScriptData.initData.parent_id){
            $("#parent_id").select2("val", initScriptData.initData.parent_id);
        }
        $('input[name=menu_type]').on('click', function () {
            var self = this;
            if ($(self).val() > 1) {
                $('.url_container').removeClass('hide');
            } else {
                $('#url').val('');
                $('.url_container').addClass('hide');
            }
            if ($(self).val() >= 3) {
                $('.choice_container').removeClass('hide');
            } else {
                $('input[name=choice_ids]:eq(0)').prop('checked', true);
                $('.choice_container').addClass('hide');
            }
            menusJs.getChildList($(self).val());
            var name = $.trim($(self).parent().text());
            $('#vname').html(name + '名称');
            $("#menu_name").attr('placeholder', name + '名称');
        });
        //选择功能点
        $('#urlChoiceBtn').on('click', function () {
            menusJs.showMenuUrl();
        });
        $('#iconChoiceBtn').on('click', function () {
            menusJs.showMenuIcon();
        });
        $('#btnChoiceBtn').on('click', function () {
            menusJs.showMenuBtnStyle();
        });
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
    showMenuIcon:function (){
        if (initScriptData.uuid) {
            App.ajax("GET",'/backend/menu/getMenusStyle',{'type':'icon'},function (response){
                dialog.open({title: '选择图标', "width":"720px", content: response});
                $('i.fa,span.glyphicon,.simplelineicons-demo .item span').on('click',function (){
                    $('#icon').val($(this).attr('class'));
                    dialog.closeAll();
                });
            });
        }
        else {
            dialog.msg('您暂未登陆');
        }
    },
    showMenuBtnStyle:function (){
        if (initScriptData.uuid) {
            App.ajax("GET",'/backend/menu/getMenusStyle',{'type':'button'},function (response){
                dialog.open({title: '选择按钮样式', "width":"600px", content: response});
                $('[type=button]').on('click',function (){
                    $('#btn_class,a.btn').val($(this).attr('class')+'  btn-sm');
                    dialog.closeAll();
                });
            });
        }
        else {
            dialog.msg('您暂未登陆');
        }
    },
    showMenuUrl: function () {
        if (initScriptData.uuid) {
            var html = '<div className="form-body setGrantId" style="padding-left:25px;">\
                            <style>.select2{margin-left:80px;}</style>\
                            <div class="form-group">\
                                <label style="float:left;width:80px;line-height: 33px;">模块名称:</label>\
                                <select class="form-control" id="module_name" style="width:200px;float:left;" >\
                                    <option value="0">请选择</option>\
                                </select>\
                           </div>\
                           <div class="clearfix"></div>\
                           <div class="form-group" style="margin-top:10px;">\
                                <label style="float:left;width:80px;line-height: 33px;">控制名称:</label>\
                                <select class="form-control" id="controller_name" style="width:200px;float:left;">\
                                    <option value="0">请选择</option>\
                                </select>\
                            </div>\
                           <div class="clearfix"></div>\
                            <div class="form-group">\
                                <label style="float:left;width:80px;line-height: 33px;">功能名称:</label>\
                                <select class="form-control" id="action_name" style="width:200px;float:left;">\
                                    <option value="0">请选择</option>\
                                </select>\
                            </div>\
                           <div class="clearfix"></div>\
                        </div>';
            dialog.showModal({
                title: '选择模块功能',
                width: 380,
                content: html,
                okCallback: function () {
                    var module_name = $('#module_name').val();
                    var controller_name = $('#controller_name').val();
                    var action_name = $('#action_name').val();
                    if (module_name == '0') {
                        dialog.tips('请选择模块名称', $('#module_name'));
                        return false;
                    } else if (controller_name == '0') {
                        dialog.tips('请选择模块名称', $('#controller_name'));
                        return false;
                    } else if (action_name == '0') {
                        dialog.tips('请选择功能名称', $('#action_name'));
                        return false;
                    } else {
                        var url = module_name + '/' + controller_name + '/' + action_name;
                        $('#url').val(url);
                        $('#route_url').val('/'+url);
                        $('#route_id').val(hex_md5(url.toLowerCase()));
                        return true;
                    }
                }
            });
            $('#controller_name').select2();
            var arr = $('#url').val().split('/');
            if (arr && arr.length == 3) {
                menusJs.getMenuUrls('', '', 'module_name', arr[0]);
                menusJs.getMenuUrls(arr[0], '', 'controller_name', arr[1]);
                menusJs.getMenuUrls(arr[0], arr[1], 'action_name', arr[2]);
            } else {
                menusJs.getMenuUrls('', '', 'module_name');
            }
            $('#module_name').on('change', function (e) {
                e.preventDefault();
                if ($(this).val() == 0) {
                    $('#controller_name').html('<option value="0">请选择</option>');
                    $('#action_name').html('<option value="0">请选择</option>');
                } else {
                    menusJs.getMenuUrls($(this).val(), '', 'controller_name');
                }
            });
            $('#controller_name').on('change', function (e) {
                e.preventDefault();
                if ($(this).val() == 0) {
                    $('#action_name').html('<option value="0">请选择</option>');
                } else {
                    menusJs.getMenuUrls($('#module_name').val(), $(this).val(), 'action_name');
                }
            });
        } else {
            dialog.msg('您暂未登陆');
        }
    },
    getMenuUrls: function (module, controller, type, sel) {
        App.ajax('GET', '/backend/menu/getUrlMenus', {module: module, controller: controller}, function (response) {
            if (response.status) {
                var html = '<option value="0">请选择</option>';
                for (var i = 0; i < response.data.length; i++) {
                    html += '<option value="' + response.data[i] + '" ' + (sel == response.data[i] ? 'selected' : '') + '>' + response.data[i] + '</option>';
                }
                $('#' + type).html(html);
            } else {
                $('#' + type).html('<option value="0">请选择</option>');
                dialog.msg(response.msg, 'error');
            }
        });
    },
    getChildList: function (menu_type) {
        $('#parent_id').empty();
        App.ajax('GET', '/backend/menu/getChildList', {menu_type: menu_type}, function (response) {
            if (response.status) {
                var html = ''
                $.each(response.data,function (i,el){
                    html += '<option value="' + el.menu_id + '">' + el.menu_name + '</option>';
                });
                $('#parent_id').html(html);
            }
        }, 'json');
    },
    delete: function (url, ids) {
        dialog.confirm({
            content: '确定需要删除该数据吗?',
            icon:3,
            ok_callback: function () {
                App.ajax('DELETE', url, {id: ids}, function (response) {
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
    }
}
var appCallback = {
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加资源',
                cancel_btn:'返回资源列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/menu/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/menu/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    updateSubmit:function(response){
        if(response.status){
            dialog.msg("修改成功",'success');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        menusJs.delete(url, ids[0]);
    },
    setSortNum:function(obj){
        global.setSort(obj,'/manager/menu/setSort');
    },
}
jQuery(document).ready(function () {
    menusJs.init();
});