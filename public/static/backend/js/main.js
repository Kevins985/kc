var mainJs = mainJs || {};
mainJs = {
    init: function () {
        $('#resetServerBtn').on('click',function (){
            dialog.confirm({
                content:'确定需要重启服务吗?',
                ok_callback:function(){
                    mainJs.resetServer();
                }
            });
        });
    },
    resetServer:function (){
        socketJs.send({type:"restart",value:'server'});
    }
}
jQuery(document).ready(function() { 
    mainJs.init();
});
