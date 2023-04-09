var icon = -1;      //图标。信息框和加载层的私有参数。类型：Number，默认：-1（信息框）/0（加载层）
var time = 0;       //自动关闭所需毫秒。类型：Number，默认：0默认不会自动关闭
var anim = 0;       //弹出动画。类型：Number。 取值：0 平滑放大（默认）、1从上掉落、2从最底部往上滑入、3从左滑入、4从左翻滚、5渐显、6抖动
var loadingIndex;   //加载层序号，便于关闭
var icons = {success: 1, error: 2, ask: 3, lock: 4, sad: 5, happy: 6};
var positon = {top: 1, right: 2, bottom: 3, left: 4};
layer.config({
    anim: 5, //默认动画风格
    // skin: 'layui-layer-molv' //皮肤layui-layer-lan，layui-layer-molv（默认）
});
var dialog = {
    index:null,
    close:function (index){
        layer.close(index);
    },
    closeAll:function (){
        layer.closeAll();
    },
    /**
     * 提示框[一些简单的提示信息]
     * @param message 提示内容
     * @param icon [1,2,3,4,5,6]
     * @param time 自动关闭所需毫秒（如果不配置，默认是3秒）
     * @param func 自动关闭后执行特定的函数 string(函数名),bool(false)表示不执行
     */
    msg : function(message, icon, time = 2000, func = false){
        if(icon && icons[icon]){
            icon = icons[icon];
        }
        var index = layer.msg(message, {
            icon: icon,
            time: time, //3秒关闭
        }, function(){
            if(typeof func == 'function'){
                func();
                dialog.close(index);
            }
            else if(typeof func == 'string'){
                eval(func+'()');
                dialog.close(index);
            }
            else{
                dialog.close(index);
            }
        });
    },
    /**
     * 提示框[带按钮的提示信息]
     * @param message 提示内容
     * @param icon 图标[1=>绿色;2=>红色;3=>黄色;4=>黑色;5=>红色;6=>绿色;]
     * @param btn 确定按钮名称,取消按钮名称
     * @param func 自动关闭后执行特定的函数 string(函数名),bool(false)表示不执行
     * @param time 自动关闭所需毫秒,这里不需要关闭（如果不配置，默认是0秒）
     */
    msgBtn : function(message, icon, func = false, btn = ['确定','取消'], time = 0){
        if(icon && icons[icon]){
            icon = icons[icon];
        }
        var index = layer.msg(message, {
            icon: icon,
            btn: btn,
            time: time,
            yes: function(){
                if(typeof func == 'function'){
                    func();
                    dialog.close(index);
                }
                else if(typeof func == 'string'){
                    eval(func+'()');
                    dialog.close(index);
                }
                else{
                    dialog.close(index);
                }
            }
        });
    },
    /**
     * 普通信息框
     * @param message 提示内容
     * @param title 信息框标题,为false时不显示标题
     * @param icon 图标[1=>绿色;2=>红色;3=>黄色;4=>黑色;5=>红色;6=>绿色;]
     * @param time 自动关闭所需毫秒，默认为0 表示不关闭
     */
    alert : function(title, content, icon = 0, func = false, time = 0){
        if(icon && icons[icon]){
            icon = icons[icon];
        }
        var index = layer.alert(content, {
            icon: icon,
            title: title,
            time: time
        },
        function(index){
            if(typeof func == 'function'){
                func();
                dialog.close(index);
            }
            else if(typeof func == 'string'){
                eval(func+'()');
                dialog.close(index);
            }
            else{
                dialog.close(index);
            }
        });
    },
    /**
     * 询问框
     * @param message 提示内容
     * @param title 信息框标题,为false时不显示标题
     * @param icon 图标[1=>绿色;2=>红色;3=>黄色;4=>黑色;5=>红色;6=>绿色;]
     * @param yes 点击确定按钮后执行的函数名,如不需执行传入false即可。
     * @param no 点击取消按钮后执行的函数名,如不需执行传入false即可。
     * @param time 自动关闭所需毫秒，默认为0 表示不关闭
     */
    confirm : function(options){
        var opts = {
            title:'信息',             //false不显示
            icon:-1,
            content: '提示信息',
            time:0,
            ok_btn: '确定',
            cancel_btn: '取消',
            ok_callback: false,
            cancel_callback: false
        }
        for (var k in options) {
            opts[k] = options[k];
        }
        layer.confirm(opts.content, {
            btn: [opts.ok_btn, opts.cancel_btn],
            btnAlign:opts.btnAlign,
            icon: opts.icon,
            title: opts.title,
            time: opts.time
        },
        function(index){
            if(typeof opts.ok_callback == 'function'){
                var bol = opts.ok_callback();
                if (bol !== false) {
                    dialog.close(index);
                }
            }
            else if(typeof opts.ok_callback == 'string'){
                var bol = eval(opts.ok_callback+'()');
                if (bol !== false) {
                    dialog.close(index);
                }
            }
            else{
                dialog.close(index);
            }
        },
        function(index){
            if(typeof opts.cancel_callback == 'function'){
                opts.cancel_callback();
                dialog.close(index);
            }
            else if(typeof opts.cancel_callback == 'string'){
                eval(opts.cancel_callback+'()');
                dialog.close(index);
            }
            else{
                dialog.close(index);
            }
        });
    },
    /**
     * 加载层
     * @param icon 加载图标  支持0-2
     * @param time 自动关闭所需毫秒，默认为0 表示不关闭
     * @param opacity 透明度
     * @param color 背景颜色
     */
    loading : function(icon,time = 0,opacity=0.1,color='#000'){
        if(icon=='close'){
            if(this.index){
                layer.close(this.index);
            }
        }
        else{
            this.index = layer.load(icon, {
                time: time,
                shade: [opacity, color]  //0.1透明度的白色背景
            });
        }
    },
    /**
     * tips层
     * @param content 提示内容
     * @param id 绑定的DOM
     * @param tips 位置[1=>上;2=>右;3=>下;4=>左]
     * @param color 背景颜色
     */
    tips : function(content,id,tips = 1, color = '#000000'){
        if(tips && positon[tips]){
            tips = positon[tips];
        }
        layer.tips(content, id, {
            tips: [tips, color],
            tipsMore: true
        });
    },
    /**
     * 输入层
     * @param formType 输入框类型，支持0（文本）默认1（密码）2（多行文本）
     * @param title 输入层标题,为false时不显示标题
     * @param value 初始时的值，默认空字符
     * @param area 自定义文本域宽高,formType为2时有效
     * @param maxlength: 140, //可输入文本的最大长度，默认500
     */
    prompt : function(formType = 1, title = false, value = '', func =false, area = []){
        layer.prompt({
            formType: formType,
            value: value,
            title: title,
            area: area
        }, function(value, index, elem){
            if(typeof func == 'function'){
                func(value,index);
            }
            else if(typeof func == 'string'){
                eval(func+'(value,index)');
            }
            else{
                dialog.close(index);
            }
        });
    },
    /**
     * tab层
     * @param area 自定义文本域宽高,['600px', '300px']
     * @param tabs [{title: 'TAB1',content: '内容1'},{title: 'TAB2',content: '内容2'}]
     */
    tab : function(options,ok_func=false,canel_func=false,is_full=false) {
        var opts = {
            area: ['800px', '600px'],
            tab: [{title: 'TAB1',content: '内容1'},{title: 'TAB2',content: '内容2'}],
            // btn: ['确定','取消'],
            yes: function(index, layero){
                if(typeof ok_func == 'function'){
                    ok_func(index);
                }
                else if(typeof func == 'string'){
                    eval(ok_func+'(index)');
                }
                else{
                    dialog.close(index);
                }
            },
            cancel: function(index, layero){
                if(typeof canel_func == 'function'){
                    canel_func();
                    dialog.close(index);
                }
                else if(typeof func == 'string'){
                    eval(canel_func+'()');
                    dialog.close(index);
                }
                else{
                    dialog.close(index);
                }
            },
        };
        if(options && options['icon'] && icons[options['icon']]){
            options['icon'] = icons[options['icon']];
        }
        for (var k in options) {
            opts[k] = options[k];
        }
        var index = layer.tab(opts);
        if(is_full){
            layer.full(index);
        }
    },
    /**
     * 弹出框 [一般用于操作成功且不需要跳转的提示]
     * @param content 提示内容-->iframe[url, 'yes'], //的url，no代表不显示滚动条
     * @param title 信息框标题
     * @param area 自定义文本域宽高
     * @param shadeClose 是否开启遮罩层
     * @param icon 图标[1=>绿色;2=>红色;3=>黄色;4=>黑色;5=>红色;6=>绿色;]
     * @param time 自动关闭所需毫秒，默认为0 表示不关闭
     * @param btn  按钮文字['确定','取消'],
     * @param maxmin  是否开启最大化最小化按钮
     * @param is_full 是否全屏打开
     */
    open: function (options,ok_func=false,canel_func=false,is_full=false) {
        var opts = {
            type: 1,         //获取当前页面元素(0:加载带图标的内容,1:加载内容,2:跳转页面)
            closeBtn: 1,     //是否显示关闭按钮
            maxmin: false,    //开启最大化最小化按钮
            shade: [0],      //shade: false,//不显示遮罩
            shadeClose: false, //点击空白区域关闭
            anim: 2,         //动画方式,默认0平滑放大,1从上掉落,2从最底部往上滑入,3从左滑入,4从左翻滚,5渐显,6抖动.
            // offset: 'rb', //右下角弹出
            // offset: [
            //     ($(window).height()-height)/2,
            //     ($(window).width()-width)/2
            // ],
            area: ['600px', '500px'],
            time :0,
            icon: 1,
            title: '信息',
            content: '内容',
            // btn: ['确定','取消'],
            yes: function(index, layero){
                if(typeof ok_func == 'function'){
                    ok_func(index);
                }
                else if(typeof func == 'string'){
                    eval(ok_func+'(index)');
                }
                else{
                    dialog.close(index);
                }
            },
            cancel: function(index, layero){
                if(typeof canel_func == 'function'){
                    canel_func();
                    dialog.close(index);
                }
                else if(typeof func == 'string'){
                    eval(canel_func+'()');
                    dialog.close(index);
                }
                else{
                    dialog.close(index);
                }
            },
            //最终都会执行的方法
            end:function(){}
        }
        if(options && options['icon'] && icons[options['icon']]){
            options['icon'] = icons[options['icon']];
        }
        for (var k in options) {
            opts[k] = options[k];
        }
        var index = layer.open(opts);
        if(is_full){
            layer.full(index);
        }
    },
    //弹出loading modal
    loadingModal: function (type, msg) {
        if (type == 'hide') {
            if ($('#loadingModel').get(0)) {
                $('#loadingModel').modal('hide');
                $('#loadingModel').remove();
            }
        }
        else {
            msg = (!msg ? '数据加载中，请稍等...' : msg);
            if (!$('#loadingModel').get(0)) {
                var html = '<div id="loadingModel" class="modal" style="margin:0px;width:300px;line-height:60px;text-align:center;">\
                                <div class="modal-body">\
                                    <span><img src="' + domain.STATIC + '/common/images/load.gif" /></span>\
                                    <span style="color:red;font-size:15px;margin-left:10px;">' + msg + '</span>\
                                </div>\
                           </div>';
                $(document.body).append(html);
                var width = document.documentElement.clientWidth || document.body.clientWidth;
                var height = document.documentElement.clientHeight || document.body.clientHeight;
                var left = (width - 300) / 2;
                var top = (height) / 2;
                $('#loadingModel').css({'top': top, 'left': left});
                $('#loadingModel').modal({backdrop: 'static'});
                $('#loadingModel').modal('show');
            }
            if (type == 'auto') {
                var st = window.setTimeout(function () {
                    dialog.loadingModal('hide');
                    window.clearTimeout(st);
                }, 15000);
            }
        }
    },
    //弹出modal
    showModal:function(options) {
        if (options === 'loading') {
            if ($('#modalEventBox').get(0)) {
                $('#modalEventBox > div').css('display', 'none');
                $('#modalEventBox').append('<div id="loadingBar" style="text-align: center;height:50px;line-height: 50px;">' +
                    '<span><img src="' + domain.STATIC + '/common/images/load.gif" /></span>' +
                    '<span style="color:#ff0000;font-size:15px;margin-left:10px;">数据处理中</span></div>');
            }
            return;
        }
        else if (options === 'hide') {
            if ($('#modalEventBox').get(0)) {
                $('.modal-backdrop.fade').remove();
                $('#modalEventBox').remove();
            }
            return;
        }
        var opts  = {
            title    : '添加模块',
            content : '',
            width: 560,
            afterShowCallback: false,
            cancelCallback:false,
            cancelTitle: '取消',
            okCallback:false,
            okTitle:'确定'
        }
        for(var k in options){
            opts[k] = options[k];
        }
        var html = '<div id="modalEventBox" class="modal fade" style="width:'+opts['width']+'px">\
                            <div class="modal-content">\
                                <div class="modal-header" style="display:block;">\
                                  <button class="close" type="button" id="modalEventClose"></button>\
                                  <h4 class="modal-title" id="myModalLabel">'+opts['title']+'</h3>\
                                </div>\
                                <div class="modal-body">'+opts['content']+'</div>\
                                <div class="modal-footer"> \
                                    <button type="button" class="btn btn-default" id="modalEventCancel">'+opts['cancelTitle']+'</button>\
                                    <button type="button" class="btn btn-primary" id="modalEventSubmit">'+opts['okTitle']+'</button>\
                                </div>\
                            </div>\
                       </div>';
        $('#pageContentContainer').append(html);
        var height = ($(window).height()-350)/2;
        var width = ($(window).width()- opts['width'])/2;
        $('#modalEventBox').modal('show');
        $('#modalEventBox').css({'top':height+'px','left':width+'px'});
        if (options['afterShowCallback'] && typeof options['afterShowCallback'] === 'function') {
            options['afterShowCallback']();
        }
        $('#modalEventCancel,#modalEventClose').click(function(e){
            e.preventDefault();
            // $(document.body).css({"overflow-y": "auto"});
            $('.modal-backdrop.fade').remove();
            $('#modalEventBox').remove();
            if(options['cancelCallback'] && typeof options['cancelCallback'] == 'function'){
                options['cancelCallback']();
            };
        });
        $('#modalEventSubmit').click(function(e){
            e.preventDefault();
            if(options['okCallback'] && typeof options['okCallback'] == 'function'){
                var bol = options['okCallback']();
                if(bol!=false){
                    $('.modal-backdrop.fade').remove();
                    $('#modalEventBox').remove();
                }
            }
        });
    }
}