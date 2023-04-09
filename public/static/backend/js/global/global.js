var global = global || {};
global = {
    is_authorization:false,
    init: function () {
        this.initPaginator();
        this.initListHandle();
        this.initPageHandle();
        if ($("[data-toggle='popover']").get(0)) {
            $("[data-toggle='popover']").popover();
        }
        if($('.top-menu .control-sidebar').get(0)){
            $('.top-menu .control-sidebar').on('click',function (){
                $(this).parent().toggleClass("active");
                $('.page-container .control-sidebar').animate({width:'toggle'});
            })
        }
        if ($('.sidebar-toggler').get(0)){
            $('.sidebar-toggler').on('click',function(){
                var sidebar_close = $('.page-sidebar-closed').get(0)?1:0;
                $.cookie("sidebar_closed",sidebar_close);
            });
        }
        if ($('.copyDataBtn').get(0)) {
            global.initCopyEvent();
        }
        if($('.choiceImagesBtn').get(0)){
            $('.choiceImagesBtn').on('click',function (e){
                e.preventDefault();
                global.choiceImages(this);
            });
        }
        if($('.formValidation').get(0)){
            global.regFormValidation($('.formValidation'));
        }
    },
    regFormValidation(dom){
        var options = {
            icon: {
                message:'This value is not valid',
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            //trigger: 'blur',
        };
        var container = dom.data('container');
        if(container){
            options.err = {'container':container};
        }
        dom.formValidation(options).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            var method = $form.attr('method');
            var data = $form.serialize();
            var callback = $form.data('callback');
            var url = $form.attr('action');
            // Get the FormValidation instance
            // var bv = $form.data('formValidation');
            App.ajax(method,url,data,function (response){
                if (appCallback && appCallback[callback]) {
                    appCallback[callback](response,url);
                }
                else{
                    if(response.status){
                        dialog.msg("保存成功",'success');
                    }
                    else{
                        dialog.msg(response.msg,'error');
                    }
                }
            });
            return false;
        });
    },
    initPageHandle:function (){
        if ($('.auto-height').get(0)) {
            $('.auto-height').on('focus', function () {
                window.activeobj = this;
                this.clock = setInterval(function () {
                    activeobj.style.height = activeobj.scrollHeight + 'px';
                }, 200);
            }).on('blur', function () {
                clearInterval(this.clock);
            });
        }
        if ($('.form_datetime').get(0)) {
            if (jQuery().datetimepicker) {
                $.fn.datetimepicker.dates['zh'] = {
                    days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
                    daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
                    daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
                    months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                    monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                    today: "今日",
                    suffix: [],
                    meridiem: []
                };
                $(".form_datetime").datetimepicker({
                    language: 'zh',
                    format: 'yyyy-mm-dd',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    minView: 2,
                }).on('changeDate', function (e) {
                    $(this).datetimepicker('hide');
                });
            }
        }
        $('.getRandomBtn').on('click', function () {
            var type = $(this).data('type');
            var length = $(this).data('length');
            var id = $(this).data('id');
            global.getRandomStr(type, length, function (str) {
                if ($('#' + id).is('input')) {
                    $('#' + id).val(str);
                } else {
                    $('#' + id).text(str);
                }
            });
        });
        $('.uploadImageBtn').on('change', function () {
            global.checkPic(this, $(this).data('type'));
        });
        $('.delImageBtn').on('click', function (e) {
            e.preventDefault();
            $(this).parent().prev().show();
            $(this).parent().hide().html('');
        });
        $('.delImageListBtn').on('click', function (e) {
            e.preventDefault();
            $(this).parent().prev().show();
            $(this).parent().hide().html('');
        });
        window.setInterval(function (){
            socketJs.send({type:"backend_message"});
        }, 15000);
    },
    messageTips:function(title,content){
        $.Toast(title, content, "notice",{
            // append to body
            appendTo: "#toastTips",
            // is stackable?
            stack: false,
            // 'toast-top-left'
            // 'toast-top-right'
            // 'toast-top-center'
            // 'toast-bottom-left'
            // 'toast-bottom-right'
            // 'toast-bottom-center'
            position_class: "toast-top-right",
            // true = snackbar
            fullscreen: false,
            // width
            width: 250,
            // space between toasts
            spacing: 20,
            // in milliseconds
            timeout: 15000,
            // has close button
            has_close_btn: true,
            // has icon
            has_icon: true,
            // is sticky
            sticky: false,
            // border radius in pixels
            border_radius: 6,
            // has progress bar
            has_progress: false,
            // RTL support
            rtl: false
        });
    },
    listenPlay:function(result){
        if(result.recharge_num>0){
            $('#audioNotice').attr('src','/static/files/notice.wav');
            $('#audioNotice')[0].play();
            global.messageTips('充值审核','<div>你有'+result.recharge_num+'条待审核充值记录<a style="margin-left:6px;" href="/backend/rechargeOrder/list?order_status=pending">查看</a></div>');
        }
        else if(result.withdraw_num>0){
            $('#audioNotice').attr('src','/static/files/p.wav');
            $('#audioNotice')[0].play();
            global.messageTips('提现审核','<div>你有'+result.withdraw_num+'条待审核提现记录<a style="margin-left:6px;" href="/backend/withdrawOrder/list?order_status=pending">查看</a></div>');
        }
        else if(result.order_num>0){
            $('#audioNotice').attr('src','/static/files/newOrder.mp3');
            $('#audioNotice')[0].play();
            global.messageTips('新订单提醒','<div>你有'+result.order_num+'条新订单数据<a style="margin-left:6px;" href="/backend/order/list">查看</a></div>');
        }
    },
    initListHandle:function (){
        if($('.searchBoxBtn').get(0) && $('#listSearchBox').get(0)){
            $('.searchBoxBtn').on('click',function (){
                $('#listSearchBox').toggleClass('hide')
            });
        }
        if($('.refreshListBtn').get(0)){
            $('.refreshListBtn').on('click',function (){
                window.location.reload();
            });
        }
        if($('.clearSearchBtn').get(0) && $('#listSearchBox').get(0)){
            $('.clearSearchBtn').on('click',function (){
                if($('#searchForm').get(0)){
                    document.getElementById("searchForm").reset();
                }
            });
        }
        $('.group-checkable').change(function () {
            var set = $('tbody > tr > td:nth-child(1) input[type="checkbox"]');
            var checked = $(this).prop("checked");
            $(set).each(function () {
                $(this).prop("checked", checked);
            });
        });
        $('#callUrlBtn a').on('click', function () {
            var self = this;
            var url = $(self).data('url');
            if(url.substr(0,1)!='/'){
                url = '/' + url;
            }
            var choice = $(self).data('choice');
            var ids = global.getCheckedIds();
            var arr = url.split('/');
            if (choice == 0) {
                if (appCallback && appCallback[arr[3]]) {
                    appCallback[arr[3]](url, ids);
                } else {
                    window.location.href = App.createUrl(url);
                }
            }
            else if (choice == 1) {
                if (ids.length == 1) {
                    if (appCallback && appCallback[arr[3]]) {
                        appCallback[arr[3]](url, ids);
                    } else {
                        url += '?id=' + ids[0];
                        window.location.href = App.createUrl(url);
                    }
                }
                else if (ids.length == 0) {
                    dialog.msg('请选择一条对应的数据', 'error');
                }
                else {
                    dialog.msg('只能选择一条对应的数据', 'error');
                }
            } else {
                if (ids.length > 0) {
                    if (appCallback && appCallback[arr[3]]) {
                        appCallback[arr[3]](url, ids);
                    } else {
                        url += '?id=' + ids.join(',');
                        window.location.href = App.createUrl(url);
                    }
                } else {
                    dialog.msg('请至少选择一条对应的数据', 'error');
                }
            }
        });
        $('.setSortBox').on('dblclick', function () {
            if (appCallback['setSortNum']) {
                var num = $.trim($(this).text());
                var type = $(this).data('type');
                if (!$('#sort_num').get(0)) {
                    $(this).html('<input type="text" size="3" title="移开保存" id="sort_num" data-id="' + $(this).data('id') + '" data-type="' + (type ? type : 0) + '" data-sort="' + num + '" value="' + num + '" />');
                    $('#sort_num').on('blur', function () {
                        appCallback['setSortNum'](this);
                    });
                }
            }
        });
        if ($('.setRecBox').get(0)) {
            $('.setRecBox').each(function (i, el) {
                var num = $(el).data('num');
                if (num == 1) {
                    $(el).html('<span class="fa fa-check is_rec"></span>');
                }
            });
            $('.setRecBox').on('dblclick', function () {
                if (appCallback['setRec']) {
                    appCallback['setRec'](this);
                }
            });
        }
        if ($('.getExtendTrBtn').get(0)) {
            $('.getExtendTrBtn').click(function (e) {
                e.preventDefault();
                var callback = $(this).data('callback');
                global.displayData(this,callback);
            });
        }
    },
    initPaginator:function (){
        $('#pageSubmit').on('change', function () {
            var url = $(this).data('url');
            window.location.href = url + '&size=' + $(this).val();
        });
        $('#paginatorContainer a').on('click',function (e){
            var self = this;
            var url = $('#paginatorContainer').data('url');
            var size = $('#pageSize').val();
            var page = parseInt($(this).attr('page'));
            var last = parseInt($('#paginatorContainer #pageSize').data('page'));
            var range_first = parseInt($('#paginatorContainer .range:first').attr('page'));
            var range_last = parseInt($('#paginatorContainer .range:last').attr('page'));
            $('#paginatorContainer .active').removeClass('active');
            if(range_last<last && page>range_last){
                var ele = $('#paginatorContainer .range:first').attr('page',page).text(page).parent().addClass('active');
                $('#paginatorContainer .next').parent().before(ele);
            }
            else if(range_first>1 && page<range_first){
                var ele = $('#paginatorContainer .range:last').attr('page',page).text(page).parent().addClass('active');
                $('#paginatorContainer .prev').parent().after(ele);
            }
            if($(self).hasClass('range')){
                $(self).parent().addClass('active');
            }else{
                $('#paginatorContainer .range[page='+page+']').parent().addClass('active');
            }
            if(page==1){
                $('#paginatorContainer .prev').attr('page',1);
                $('#paginatorContainer .next').attr('page',page+1);
                $('#paginatorContainer .prev').parent().addClass('disabled');
                $('#paginatorContainer .next').parent().removeClass('disabled');
            }
            else if(page==last){
                $('#paginatorContainer .prev').attr('page',page-1);
                $('#paginatorContainer .next').attr('page',last);
                $('#paginatorContainer .prev').parent().removeClass('disabled');
                $('#paginatorContainer .next').parent().addClass('disabled');
            }
            else{
                $('#paginatorContainer .prev').attr('page',page-1);
                $('#paginatorContainer .next').attr('page',page+1);
                $('#paginatorContainer .prev').parent().removeClass('disabled');
                $('#paginatorContainer .next').parent().removeClass('disabled');
            }
            var reg=new RegExp("page=(\\d+)","gmi");
            url=url.replace(reg,'page='+page)+'&=size='+size;
            if (appCallback['paginatorAjax']) {
                appCallback['paginatorAjax'](url);
            }
        });
    },
    initCopyEvent: function () {
        var clipboard = new Clipboard('.copyDataBtn');
        clipboard.on('success', function (e) {
            console.info('Action:', e.action);
            console.info('Text:', e.text);
            console.info('Trigger:', e.trigger);
            e.clearSelection();
            dialog.msg('复制成功');
        });
        clipboard.on('error', function (e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
    },
    initData: function (data) {
        if (!$('#formFieldData').get(0) || !data)
            return;
        $.each(data, function (k, v) {
            var el = $('#formFieldData').find("[name='" + k + "']");
            if (el.length == 0)
                return;
            if (el.attr('type') == 'checkbox') {
                el.each(function (i, item) {
                    if ($.isArray(v)) {
                        $.each(v, function (r, rItem) {
                            $(item).val() == rItem && $(item).prop('checked', true);
                        });
                    } else {
                        $(item).val() == v && $(item).prop('checked', true);
                    }
                });
                return;
            }
            if (el.attr('type') == 'radio') {
                el.each(function (i, item) {
                    $(item).val() == v && $(item).prop('checked', true);
                });
                return;
            }
            el.is('input') && el.val(v);
            el.is('textarea') && el.val(v);
            if (el.is('select')) {
                if ($.isArray(v)) {
                    $.each(v, function (sk, sv) {
                        el.children("[value=" + sv + "]").prop('selected', 'selected');
//                        el.change();
                    });
                } else {
                    el.children("[value=" + v + "]").prop('selected', 'selected');
//                    el.change();
                }
                return;
            }
            el.is('span') && el.html(v);
        });
    },
    displayData:function (obj,func=false){
        var id = $(obj).parents('tr').attr('id');
        var close = $(obj).data('close');
        var type = null;
        if($(obj).attr('title') == "关闭")
        {
            $(obj).removeClass("fa-minus-square-o").addClass("fa-plus-square-o");
            $(obj).attr('title','打开');
            type = 'hide';
        }
        else
        {
            if(close==1){
                $('.getExtendTrBtn').removeClass("fa-minus-square-o").addClass("fa-plus-square-o").attr('title','打开');
            }
            $(obj).removeClass("fa-plus-square-o").addClass("fa-minus-square-o");
            $(obj).attr('title','关闭');
            type = 'show';
        }
        if(typeof func == 'function'){
            func(id,type);
        }
        else if(typeof func == 'string'){
            eval(func+'(id,type)');
        }
    },
    setRec: function (obj,url) {
        url = App.createUrl(url);
        var params = {
            id: $(obj).data('id'),
            num: ($(obj).data('num') == 1 ? 0 : 1)
        }
        App.ajax('GET', url, params, function (response) {
            if (response.status) {
                $(obj).data('num', params.num).html((params.num == 0 ? '' : '<span class="fa fa-check is_rec"></span>'));
                dialog.msg('设置成功', 'success');
            } else {
                dialog.msg(response.msg, 'error');
            }
        });
    },
    setSort: function (obj, url) {
        var sort = $(obj).val();
        if (sort != $(obj).data('sort')) {
            url = App.createUrl(url);
            App.ajax('GET', url, {id: $(obj).data('id'), sort: sort}, function (response) {
                if (response.status) {
                    $(obj).parent().html(sort);
                    dialog.msg('设置成功', 'success');
                } else {
                    dialog.msg(response.msg, 'error');
                }
            });
        } else {
            $(obj).parent().html(sort);
        }
    },
    choiceImages:function (obj){
        if($(obj).data('click')==1){
            return;
        }
        $(obj).data('click',1);
        var type = $(obj).data('type');
        var callback = $(obj).data('callback');
        var count = $(obj).data('count');
        App.ajax('GET','/backend/upload/images', {type:type}, function (response) {
            dialog.open({
                title: '图库',
                area: ['650px', '520px'],
                btn: ['确定','取消'],
                yes: function(index, layero){
                    var type = $('#imagesModelContainer').find('li.active').data('type');
                    var img = $('#'+type,'#imagesModelContainer').find('img.select');
                    if(!img.get(0)){
                        dialog.msg('请选择图片');
                        return false;
                    }
                    var params = {"url":img.attr('src'),"md5":img.data('md5')};
                    if(typeof callback == "function")
                        callback(obj,params);
                    else{
                        eval(callback+"(obj,params);");
                    }
                    dialog.close(index)
                    $(obj).data('click',0);
                },
                canel: function(index, layero){
                    dialog.close(index);
                    $(obj).data('click',0);
                },
                content: response
            });
            $('#imagesModelContainer img').on('click',function (){
                if(!count || count<2){
                    $('#imagesModelContainer img').removeClass('select');
                }
                $(this).addClass('select');
            });
            $('.uploadImageBtn').on('change', function () {
                global.checkPic(this,type);
            });
            $('#getImagesBtn').on('click',function (){
                var url = $('#curl_images').val();
                if($.trim(url)==''){
                    dialog.msg('请输入图片地址','error');
                }
                else{
                    var type = $(this).data('type');
                    global.checkCurlPic(url,type);
                }
            });
        });
    },
    checkCurlPic:function(file_url,type){
        var value = file_url.substring(file_url.lastIndexOf(".") + 1);
        var ext = ['jpg', 'png', 'gif','jpeg'];
        value = value.toLowerCase();
        if ($.inArray(value, ext) == '-1') {
            dialog.msg('上传的文件格式必须是(' + (ext.join('、')) + ')', 'error');
            $(obj).val("");
            return;
        }
        App.ajax('post','/backend/upload/curl',{url:file_url,type:type},function (response){
            if(response.status){
                var html = '<img class="img-thumbnail" src="'+response.data.file_url+'" data-md5="'+response.data.file_md5+'" width="55px" height="55px">';
                $('#curl','#imagesContainer').find('.socicons').append(html);
                $('#curl','#imagesContainer').find('img').on('click',function (){
                    $('#imagesContainer img').removeClass('select');
                    $(this).addClass('select');
                });
                $('#curl_images').val('');
            }
        });
    },
    checkPic: function (obj,type,is_hide_load) {
        var file_value = $(obj).val();
        var value = file_value.substring(file_value.lastIndexOf(".") + 1);
        var ext = ['jpg', 'png', 'gif','jpeg'];
        value = value.toLowerCase();
        if ($.inArray(value, ext) == '-1') {
            dialog.msg('上传的文件格式必须是(' + (ext.join('、')) + ')', 'error');
            $(obj).val("");
            return;
        }
        var up_url = App.createUrl('/backend/upload/file');
        var html = '<div id="imageCache" style="display:none;">';
        html += '<form action="' + up_url + '" method="post" target="imageIframe" id="imageForm" enctype="multipart/form-data"></form>';
        html += '<iframe id="imageIframe" name="imageIframe" style="border:0px;"></iframe>'
        html += '</div>';
        if (!$('#imageCache').get(0)) {
            $(html).appendTo(document.body);
        }
        if ($('#imageForm').get(0)) {
            $('#imageForm').empty();
            var clone = $(obj).clone();
            clone.change(function () {
                global.checkPic(this, $(this).data('type'));
            });
            //clone.appendTo($('#imageForm'));
            $(obj).after(clone);
            $(obj).appendTo($('#imageForm'));
            if ($(obj).data('num') != undefined) {
                $('#imageForm').append('<input type="hidden" name="num" value="' + $(obj).data('num') + '" />');
            }
            if ($(obj).data('dom') != undefined) {
                $('#imageForm').append('<input type="hidden" name="dom" value="' + $(obj).data('dom') + '" />');
            }
            $('#imageForm').append('<input type="hidden" name="userid" value="' + initScriptData.uuid + '" />');
            $('#imageForm').append('<input type="hidden" name="type" value="' + type + '" />');
            var callback = 'appCallback.upload';
            if(type=='spec'){
                callback = 'globalCallback.upload'
            }
            $('#imageForm').append('<input type="hidden" name="callback" value="'+callback+'" />');
            $('#imageForm').submit();
            if(!is_hide_load){
                dialog.loading(0);
            }
        }
    },
    getCheckedIds: function () {
        var ids = [];
        $('.checkboxes:checked').each(function (i, el) {
            if($.trim($(el).val())!=''){
                ids.push($(el).val());
            }
        });
        return ids;
    },
    getPrefixZero:function(num, len) {
        return (Array(len).join(0) + num).slice(-len);
    },
    getRandomStr: function (type, length, callback) {
        if ($.inArray(type, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]) == "-1" || !$.isNumeric(length)) {
            dialog.msg('参数输入错误', 'error');
        } else {
            var url = App.createUrl('/backend/main/getRandomStr');
            App.ajax('POST', url, {type: type, length: length}, function (response) {
                if (response.status) {
                    callback(response.data.random);
                } else {
                    dialog.msg(response.msg, 'error');
                }
            });
        }
    },
    appendUploadData:function(dom,response,container,file_type='url'){
        dialog.loading("close");
        if(response && response.status){
            var data = response.data;
            var img_url = (data.cut[1] ? data.cut[1] :data.file_url);
            var dom_value = (file_type=='md5'?data.file_md5:img_url);
            var html = '<div class="col-md-2 images">\
                            <a href="javascript:;" class="delImageListBtn fa fa-remove"></a>\
                            <img class="img-rounded" src="' + img_url + '"/>\
                            <input type="hidden" class="'+dom+'" value="'+dom_value+'"/>\
                        </div>';
            $('#'+container).find('.images:last').before(html);
            $('.delImageListBtn').bind('click', function (e) {
                e.preventDefault();
                $(this).parent().remove();
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    setUploadData:function(dom,response,container='uploadImageContainer',file_type='url'){
        dialog.loading("close");
        if(response && response.status){
            var data = response.data;
            var img_url = (data.cut[1] ? data.cut[1] :data.file_url);
            var dom_value = (file_type=='md5'?data.file_md5:img_url);
            var html = '<a href="javascript:;" class="delImageBtn"><i class="fa fa-remove"></i></a>\
                        <img class="img-rounded" src="' + img_url + '" />\
                        <input type="hidden" id="'+dom+'" name="'+dom+'" value="'+dom_value+'" />';
            $('#'+container).prev().hide();
            $('#'+container).show().html(html);
            $('.delImageBtn').bind('click', function (e) {
                e.preventDefault();
                var self = this;
                $(self).parent().prev().show();
                $(self).parent().hide().html('');
            });
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
    authorization:function(){
        socketJs.send({type:"authorization",value:initScriptData.uuid},function(res){
            if(res){
                global.is_authorization = true;
            }
        });
    }
}
var globalCallback = {
    upload:function(response){
        dialog.loading("close");
        if(response && response.status){
            var html = '<img class="img-thumbnail" src="'+response.data.file_url+'" data-md5="'+response.data.file_md5+'" width="55px" height="55px">';
            $('#local','#imagesModelContainer').find('.socicons').append(html);
            $('#local','#imagesModelContainer').find('img').on('click',function (){
                $('#imagesModelContainer img').removeClass('select');
                $(this).addClass('select');
            });
            $('#curl_images').val('');
        }
        else{
            dialog.msg(response.msg,'error');
        }
    },
}
jQuery(document).ready(function () {
    global.init();
    if (initScriptData.uuid && initScriptData.socket_url) {
        socketJs.init({
            url:initScriptData.socket_url,
            open_callback:function(evt){
                setInterval(function(){
                    socketJs.send({type:"ping",value:initScriptData.uuid});
                },5000);
            },
            close_callback:function(evt){
//                setInterval(function(){
//                    global.getUserMessage();
//                },5000);
            },
            receive_callback:function(evt){
                if(evt.data=='success'){
                    socketJs.socket_status = 1;
                }
                else{
                    var res = JSON.parse(evt.data);  //{type,msg,data}
                    // if(initScriptData.uuid==1 && res.type!='ping'){
                    //     this.writeLog(res);
                    // }
                    if(res.type=='ping'){
                        console.log('连接正常');
                    }
                    else if(res.type=='backend_message'){
                        global.listenPlay(res);
                    }
                    else if(res.type=='tips'){
                        dialog.msg(res.msg);
                    }
                    else if(res.type=='success'){
                        dialog.msg(res.msg,'success');
                    }
                    else if(res.type=='error'){
                        dialog.msg(res.msg, 'error')
                    }
                }
            }
        });
    }
});
Date.prototype.format = function(fmt, date) {
    if (!fmt) return '';
    date = date || new Date();
    var o = {
        "M+" : this.getMonth()+1, //月份
        "d+" : this.getDate(), //日
        "h+" : this.getHours()%12 == 0 ? 12 : this.getHours()%12, //小时
        "H+" : this.getHours(), //小时
        "m+" : this.getMinutes(), //分
        "s+" : this.getSeconds(), //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S" : this.getMilliseconds() //毫秒
    };
    var week = {
        "0" : "/u65e5",
        "1" : "/u4e00",
        "2" : "/u4e8c",
        "3" : "/u4e09",
        "4" : "/u56db",
        "5" : "/u4e94",
        "6" : "/u516d"
    };
    if(/(y+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    }
    if(/(E+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, ((RegExp.$1.length>1) ? (RegExp.$1.length>2 ? "/u661f/u671f" : "/u5468") : "")+week[this.getDay()+""]);
    }
    for(var k in o){
        if(new RegExp("("+ k +")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    return fmt;
}
