var configs = {
    urlArgs:"version="+version,
    baseUrl:"/static/",
    waitSeconds: 0,
    map: {
        "*": {"css": "common/js/require.css"}
    },
    shim: {
        "jquery_cookie":{
            deps: ["jQuery"]
        },
        "layer":{
            deps: ["jQuery","css!./plugins/layer/skin/default/layer.css"]
         },
        "jquery_ui":{
            deps:["jQuery","css!./plugins/jquery-ui/jquery-ui.min.css"]
        },
        "select2":{
             deps: ["jQuery","css!./plugins/select2/css/select2.min.css","css!./plugins/select2/css/select2-bootstrap.min.css"]
         }, 
         "fancybox":{
             deps: ["jQuery",'css!./plugins/fancybox/css/jquery.fancybox.min.css']
         }, 
        "bootstrap":{
            deps:["jQuery"]
        },
        "bootstrap_switch":{
            deps:["jQuery"]
        },
        "jquery-toast":{
            deps:["jQuery","css!./plugins/jquery-toast/css/toast.style.css"]
        },
        "bootstrap-select":{
            deps:["jQuery","css!./plugins/bootstrap-select/css/bootstrap-select.min.css"]
        },
        "bootstrap_tagsinput":{
            deps:["jQuery","css!./plugins/bootstrap-tagsinput/bootstrap-tagsinput.css"]
        },
        "bootstrap_confirmation":{
            deps:["jQuery",'bootstrap']
        },
        "bootstrap_touchspin":{
            deps:["jQuery",'bootstrap',"css!./plugins/bootstrap-touchspin/bootstrap.touchspin.css"]
        },
        "bootstrap_rangeSlider":{
            deps:["jQuery",'bootstrap',"css!./plugins/ion.rangeslider/css/ion.rangeSlider.css","css!./plugins/ion.rangeslider/css/ion.rangeSlider.skinFlat.css"]
        },
        "jquery.multi-select":{
            deps:["jQuery","css!./plugins/jquery-multi-select/css/multi-select.css"]
        },
        "form-validation":{
            deps:["jQuery","css!./plugins/form-validation/css/formValidation.css"]
        },
        "form-validation-bootstrap":{
            deps:["jQuery","form-validation"]
        },
        "form-validation-lang":{
            deps:["form-validation-bootstrap"]
        },
        "colorpicker":{
            deps:["jQuery","css!./plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css"]
        },
        "cscolor":{
            deps:["jQuery","css!./plugins/cscolor/jquery.cxcolor.css"]
        },
        "validation":{
            deps:["jQuery"]
        },
        "app":{
            deps:["jQuery","layer","dialog"]
        },
        "dialog":{
            deps:["jQuery","layer"]
        },
        "layout":{
            deps:["app"]
        },
        "jquery-ui":{
            deps:["jQuery","css!./plugins/jquery-ui/jquery-ui.min.css"]
        },
        "kindeditor":{
            deps:["jQuery","md5","css!./plugins/kindeditor/themes/default/default.css"]
        },
        "kindeditor_cn":{
            deps:["kindeditor"]
        },
        "datetimepicker":{
            deps:["jQuery","css!./plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"]
        },
        "quick_sidebar":{
            deps:["app"]
        },
        "ztree":{
            deps:["jQuery","css!./plugins/ztree/css/zTreeStyle/zTreeStyle.css"]
        },
        "global":{
            deps: ["jquery-toast","jquery_cookie","bootstrap","bootstrap_switch","app","layout","quick_sidebar","form-validation-lang","socket"]
        },
        "backend_login":{
            deps: ["app","jquery_cookie"]
        },
        "backend_main":{
            deps: ["global"]
        },
        "backend_admin":{
            deps: ["ztree","bootstrap-select","global"]
        },
        "backend_role":{
            deps: ["ztree","global"]
        },
        "backend_menu":{
            deps: ["bootstrap-select","global","md5","select2"]
        },
        "backend_dict":{
            deps:["global"]
        },
        "backend_country":{
            deps:["global"]
        },
        "backend_system":{
            deps: ["global"]
        },
        "backend_logs":{
            deps:["global"]
        },
        "backend_flowing":{
            deps:["bootstrap-select", "global"]
        },
        "backend_currency":{
            deps:["bootstrap-select", "global"]
        },
        "backend_job":{
            deps:["global","jquery_ui"]
        },
        "backend_jobgroup":{
            deps:["global"]
        },
        "backend_database":{
            deps:[ "global"]
        },
        "backend_help":{
            deps:["global","kindeditor_cn"]
        },
        "backend_helpcategory":{
            deps:["global"]
        },
        "backend_article":{
            deps:["global","kindeditor_cn"]
        },
        "backend_articlecategory":{
            deps:["global"]
        },
        "backend_notice":{
            deps:["global","kindeditor_cn"]
        },
        "backend_noticecategory":{
            deps:["global"]
        },
        "backend_advtype":{
            deps:["global"]
        },
        "backend_advlocation":{
            deps:["global"]
        },
        "backend_adv":{
            deps:["global","datetimepicker"]
        },
        "backend_tags":{
            deps: ["global"]
        },
        "backend_tagscategory":{
            deps: ["global"]
        },
        "backend_level":{
            deps: ["global"]
        },
        "backend_realauth":{
            deps: ["global"]
        },
        "backend_member":{
            deps: ["bootstrap-select","global","select2"]
        },
        "backend_memberextend":{
            deps: ["global","datetimepicker"]
        },
        "backend_withdraworder":{
            deps: ["global"]
        },
        "backend_rechargeorder":{
            deps: ["global"]
        },
        "backend_order":{
            deps: ["global"]
        },
        "backend_lang":{
            deps:["global"]
        },
        "backend_langkey":{
            deps:["global"]
        },
        "backend_spu":{
            deps:["global","kindeditor_cn","bootstrap_confirmation","select2"]
        },
        "backend_project":{
            deps:["global","datetimepicker"]
        },
        "backend_brand":{
            deps:["global"]
        },
        "backend_category":{
            deps:["bootstrap-select", "global"]
        },
        "backend_banktype":{
            deps: ["global"]
        },
        "backend_ipvisit":{
            deps: ["global","bootstrap_confirmation"]
        },
        "backend_error":{
            deps:["global"]
        }
    },
    paths: {
        "jQuery": "common/js/jquery-1.12.4.min",
        "jquery_cookie": "common/js/jquery.cookie",
        "jquery_ui":"plugins/jquery-ui/jquery-ui.min",
        "md5":"common/js/md5",
        "dialog": "common/js/dialog",
        "socket":"common/js/socket",
        "layer": "plugins/layer/layer",
        "cscolor":"plugins/cscolor/jquery.cxcolor.min",
        "colorpicker":"plugins/bootstrap-colorpicker/js/bootstrap-colorpicker",
        "validation":"plugins/jquery-validation/js/jquery.validate",
        "html-minifier":"plugins/kindeditor/htmlminifier.min",
        "kindeditor":"plugins/kindeditor/kindeditor-all",
        "kindeditor_cn":"plugins/kindeditor/lang/zh_CN",
        "select2": "plugins/select2/js/select2.min",
        "fancybox":'plugins/fancybox/js/jquery.fancybox.min',
        "jquery-ui":"plugins/jquery-ui/jquery-ui.min",
        "ztree":"plugins/ztree/js/jquery.ztree.all",
        "bootstrap": "plugins/bootstrap/3.3.6/js/bootstrap.min",
        "datetimepicker":"plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min",
        "bootstrap_switch":"plugins/bootstrap-switch/js/bootstrap-switch.min",
        "jquery-toast":"plugins/jquery-toast/js/toast.script",
        "bootstrap-select":"plugins/bootstrap-select/js/bootstrap-select.min",
        "bootstrap_tagsinput":"plugins/bootstrap-tagsinput/bootstrap-tagsinput",
        "bootstrap_confirmation":"plugins/bootstrap-confirmation/bootstrap-confirmation",
        "bootstrap_rangeSlider":"plugins/ion.rangeslider/js/ion.rangeSlider.min",
        "bootstrap_touchspin":"plugins/bootstrap-touchspin/bootstrap.touchspin",
        "jquery.multi-select":"plugins/jquery-multi-select/js/jquery.multi-select",
        "form-validation":"plugins/form-validation/js/formValidation",
        "form-validation-lang":"plugins/form-validation/language/zh_CN",
        "form-validation-bootstrap":"plugins/form-validation/js/bootstrap",
        "global":"backend/js/global/global",
        "app": "backend/js/global/app",
        "layout": "backend/js/global/layout",
        "quick_sidebar": "backend/js/global/quick-sidebar",
        "backend_login":"backend/js/login",
        "backend_main":"backend/js/main",
        "backend_menu":"backend/js/sys/menu",
        "backend_role":"backend/js/sys/role",
        "backend_admin":"backend/js/sys/admin",
        "backend_dict":"backend/js/sys/dict",
        "backend_country":"backend/js/sys/country",
        "backend_system":"backend/js/sys/system",
        "backend_logs":"backend/js/sys/logs",
        "backend_flowing":"backend/js/sys/flowing",
        "backend_currency":"backend/js/sys/currency",
        "backend_job":"backend/js/sys/job",
        "backend_jobgroup":"backend/js/sys/job_group",
        "backend_database":"backend/js/sys/database",
        "backend_help":"backend/js/operate/help",
        "backend_helpcategory":"backend/js/operate/help_category",
        "backend_article":"backend/js/operate/article",
        "backend_articlecategory":"backend/js/operate/article_category",
        "backend_notice":"backend/js/operate/notice",
        "backend_noticecategory":"backend/js/operate/notice_category",
        "backend_advtype":"backend/js/operate/adv_type",
        "backend_advlocation":"backend/js/operate/adv_location",
        "backend_adv":"backend/js/operate/adv",
        "backend_tags":"backend/js/user/tags",
        "backend_tagscategory":"backend/js/user/tags_category",
        "backend_level":"backend/js/user/level",
        "backend_realauth":"backend/js/user/real_auth",
        "backend_member":"backend/js/user/member",
        "backend_memberextend":"backend/js/user/member_extend",
        "backend_withdraworder":"backend/js/user/withdraw_order",
        "backend_rechargeorder":"backend/js/user/recharge_order",
        "backend_order":"backend/js/user/order",
        "backend_spu":"backend/js/goods/spu",
        "backend_project":"backend/js/goods/project",
        "backend_brand":"backend/js/goods/brand",
        "backend_lang":"backend/js/sys/lang",
        "backend_langkey":"backend/js/sys/lang_key",
        "backend_category":"backend/js/goods/category",
        "backend_banktype":"backend/js/sys/bank_type",
        "backend_ipvisit":"backend/js/sys/ip_visit",
        "backend_error":"backend/js/user/error",
    },
}
require.config(configs);
if(initScriptData){
    var module = initScriptData.m+"_"+initScriptData.c;
    if(configs.paths[module]){
        require([module],function($){});
    }
    else{
        alert(module+"未定义");
    }
}

