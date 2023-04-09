var articleJs = articleJs || {};
articleJs = {
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
            uploadJson : '/backend/upload/editor',
            fileManagerJson : '/backend/upload/editorManager',
            allowFileManager : true,
            resizeType: 1,
            afterBlur:function(){this.sync();}
        });
        $('#articleSubmitBtn').on('click',function (){
            var is_rec = $('[name=is_rec]').prop('checked')?1:0;
            $('#is_rec').val(is_rec);
            $('#content').val(articleJs.editor.html());
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
        global.setUploadData('image',response);
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加文章',
                cancel_btn:'返回文章列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/article/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/article/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        articleJs.delete(url,ids[0]);
    },
}
jQuery(document).ready(function () {
    articleJs.init();
});