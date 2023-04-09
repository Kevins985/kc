var spuJs = spuJs || {};
spuJs = {
    editor:null,
    max_spec_cnt:3,
    images_cnt:10,
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
        this.editor = KindEditor.create('textarea[name="description"]',{
            uploadJson : '/backend/upload/editor',
            fileManagerJson : '/backend/upload/editorManager',
            allowFileManager : true,
            resizeType: 1,
            afterBlur:function(){this.sync();}
        });
        $('#spuSubmitBtn').on('click',function (){
            $('#description').val(spuJs.editor.html());
            var data = spuJs.dataValidation();
            if(data){
                spuJs.submitSpuSave(data);
            }
        });
        $('input[type=number]').on('blur',function (){
            if($(this).val()==''){
                $(this).val(0);
            }
        })
        $('.setSpuStatusBtn').on("confirmed.bs.confirmation",function(){
            spuJs.setSpuStatus(this,'yes');
        }).on("canceled.bs.confirmation",function(){
            spuJs.setSpuStatus(this,'no');
        });
    },
    submitSpuSave:function(data){
        var url = $("#goodsForm").attr('action');
        var method = $("#goodsForm").attr('method');
        App.ajaxLoading(method, url, data, function (response) {
            if (response.status) {
                if (method=='post') {
                    dialog.confirm({
                        content: '保存成功',
                        ok_btn: '继续添加商品',
                        cancel_btn: '返回商品列表',
                        ok_callback: function () {
                            window.location.href = url;
                        },
                        cancel_callback: function () {
                            window.location.href = App.createUrl('/backend/spu/list');
                        }
                    });
                }
                else {
                    dialog.msg('保存成功', 'success');
                }
            }
            else {
                dialog.msg(response.msg, 'error');
            }
        });
    },
    setSpuStatus:function (obj,type){
        var id = $(obj).data('id');
        var status = $(obj).data('status');
        var msg = '保存';
        if(status==1){                    //上架
            status = (type=='yes'?0:2);
            msg = (type=='yes'?'待审核':'下架');
        }
        else if(status==2){               //下架
            status = (type=='yes'?1:0);
            msg = (type=='yes'?'上架':'待审核');
        }
        else if(status==0){              //待审核
            status = (type=='yes'?1:2);
            msg = (type=='yes'?'上架':'下架');
        }
        else{
            status = 1;
            msg = '上架';
        }
        dialog.confirm({
            content:'确定将该商品状态设置为'+msg+'吗?',
            icon:3,
            ok_callback:function(){
                App.ajax('PUT','/backend/spu/setStatus', {id:id,status:status}, function (response) {
                    if (response.status) {
                        $(obj).remove();
                        dialog.msg('操作成功', 'success');
                    } else {
                        dialog.msg(response.msg, 'error');
                    }
                });
            }
        });
    },
    dataValidation:function (){
        var is_submit = true;
        var data = {
            title:$('#title').val(),
            spu_no:$('#spu_no').val(),
            category_id:$('#category_id').val(),
            image_url:($('#image_url').get(0)?$('#image_url').val():''),
            photo:[],
            market_price:$('#market_price').val(),
            sell_price:$('#sell_price').val(),
            sales_cnt:$('#sales_cnt').val(),
            sort:$('#sort').val(),
            status:$('input[name=status]:checked').val(),
            brief:$('#brief').val(),
            description:$('#description').val()
        };
        if($('#spu_id').get(0)){
            data['spu_id'] = $('#spu_id').val();
        }
        $('#uploadPhotoContainer').find('.photo').each(function(i,el){
            data.photo.push($(el).val());
        });
        //基本信息验证
        if(data.title==''){
            dialog.msg('商品名称不能为空','error');
            $('#title').focus();
            is_submit = false;
        }
        else if(data.image_url==''){
            dialog.msg('请上传商品展示图','error');
            is_submit = false;
        }
        else if(data.photo.length==0){
            dialog.msg('请上传商品轮播图','error');
            is_submit = false;
        }
        else if(!data.status){
            dialog.msg('请选择商品状态','error');
            is_submit = false;
        }
        else if(!data.market_price){
            dialog.msg('请输入市场价格','error');
            is_submit = false;
        }
        else if(!data.sell_price){
            dialog.msg('请输入销售价格','error');
            is_submit = false;
        }
        else if(data.description==''){
            dialog.msg('请输入商品详情内容','error');
            $('#description').focus();
            is_submit = false;
        }
        if(is_submit){
            return data;
        }
        return false;
    },
    showListChild:function (id,type){
        if(type=='show'){
            App.ajax('GET','/backend/spu/getGoodsInfo',{id:id}, function (response) {
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
    setSpuCategory: function (url,ids) {
        var category_id = $('#set_category_id').val();
        if(category_id==0){
            dialog.msg("请选择商品分类");
            return false;
        }
        else{
            App.ajax("PUT",url,{category_id:category_id,spu_ids:ids},function(response){
                if(response.status){
                    dialog.msg("操作成功",'success',1000,function(){
                        window.location.reload();
                    })
                }
                else{
                    dialog.msg(response.msg);
                }
            });
            return false;
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
                            if($.inArray($(el).val(),ids)>-1){
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
    setLangTran:function (url,ids){
        window.location.href = url+'/'+ids[0];
    },
    upload:function(response){
        if(response.data.type=='spu_images'){
            var image_cnt = $('#uploadPhotoContainer').find('.photo').length;
            if(image_cnt<spuJs.images_cnt){
                global.appendUploadData('photo',response,'uploadPhotoContainer');
            }
            else{
                dialog.msg('最多只能上传'+spuJs.images_cnt+'张轮播图片');
            }
        }
        else{
            global.setUploadData('image_url',response,'uploadImageContainer');
        }
    },
    setSpuCategory:function (url,ids){
        App.ajax('GET',url,{},function(response){
            if(typeof response=='string'){
                dialog.open({
                    title:'批量设置分类',
                    content:response,
                    area:['350px','180px'],
                    btn: ['确定','取消'],
                },function(index){
                    spuJs.setSpuCategory(url, ids);
                });
            }
            else{
                dialog.msg(response.msg,'error');
            }
        })
    },
    addSubmit:function (response){
        if(response.status){
            dialog.confirm({
                content:'保存成功',
                icon:1,
                ok_btn:'继续添加项目',
                cancel_btn:'返回项目列表',
                ok_callback:function(){
                    window.location.href = App.createUrl('/backend/spu/add');;
                },
                cancel_callback:function(){
                    window.location.href = App.createUrl('/backend/spu/list');
                }
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    delete: function (url, ids) {
        spuJs.delete(url,ids);
    },
}
jQuery(document).ready(function () {
    spuJs.init();
});