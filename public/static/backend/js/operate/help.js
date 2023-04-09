var helpJs = helpJs || {};
helpJs = {
    editor:null,
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
        this.editor = KindEditor.create('textarea[name="content"]',{
            resizeType: 1,
            uploadJson : '/backend/upload/editor',
            fileManagerJson : '/backend/upload/editorManager',
            allowFileManager : true,
            afterBlur:function(){this.sync();}
        });
        $('#helpSubmitBtn').on('click',function (){
            var status = $('#status').prop('checked')?1:0;
            $('#status').val(status);
            $('#content').val(helpJs.editor.html())
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
                ok_btn:'继续添加帮助文档',
                cancel_btn:'返回帮助文档列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/help/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/help/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        helpJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    helpJs.init();
});