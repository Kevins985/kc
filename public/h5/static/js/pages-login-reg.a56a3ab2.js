(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-login-reg"],{"0b1a":function(o,n,i){o.exports=i.p+"static/img/logo.687d8d78.png"},"98ea":function(o,n,i){"use strict";i.r(n);var t=i("a6f5"),e=i("c01d");for(var s in e)["default"].indexOf(s)<0&&function(o){i.d(n,o,(function(){return e[o]}))}(s);var a=i("f0c5"),l=Object(a["a"])(e["default"],t["b"],t["c"],!1,null,"042e0fe3",null,!1,t["a"],void 0);n["default"]=l.exports},a6f5:function(o,n,i){"use strict";i.d(n,"b",(function(){return t})),i.d(n,"c",(function(){return e})),i.d(n,"a",(function(){}));var t=function(){var o=this,n=o.$createElement,t=o._self._c||n;return t("v-uni-view",[t("v-uni-view",{staticClass:"loginHeader",staticStyle:{padding:"30rpx 0"}},[t("v-uni-image",{staticClass:"loginLogo",attrs:{src:i("0b1a")}})],1),t("v-uni-view",{staticClass:"loginMain"},[t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-zhanghao"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"text",focus:o.codeFocus,placeholder:"请输入登陆账号","placeholder-style":"color: #ccc;"},model:{value:o.login.account,callback:function(n){o.$set(o.login,"account",n)},expression:"login.account"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-zhanghao"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"number",focus:o.codeFocus,maxlength:"11",placeholder:"请输入11位手机号码","placeholder-style":"color: #ccc;"},model:{value:o.login.mobile,callback:function(n){o.$set(o.login,"mobile",n)},expression:"login.mobile"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-shezhizhifumima"}),t("v-uni-text",{directives:[{name:"show",rawName:"v-show",value:o.verShow,expression:"verShow"}],staticClass:"getCode",on:{click:function(n){arguments[0]=n=o.$handleEvent(n),o.GetCode()}}},[o._v("获取验证码")]),t("v-uni-text",{directives:[{name:"show",rawName:"v-show",value:!o.verShow,expression:"!verShow"}],staticClass:"getCode disabled",attrs:{disabled:!0}},[o._v(o._s(o.timer)+"S后重新获取")]),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"number",focus:o.codeFocus,maxlength:"6",placeholder:"请输入您的验证码","placeholder-style":"color: #ccc;"},model:{value:o.login.code,callback:function(n){o.$set(o.login,"code",n)},expression:"login.code"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-mima"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"text",maxlength:"32",placeholder:"请输入您的密码","placeholder-style":"color: #ccc;",password:"true"},model:{value:o.login.password,callback:function(n){o.$set(o.login,"password",n)},expression:"login.password"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-mima"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"text",maxlength:"32",placeholder:"再次确认您的密码","placeholder-style":"color: #ccc;",password:"true"},model:{value:o.login.confirmPassword,callback:function(n){o.$set(o.login,"confirmPassword",n)},expression:"login.confirmPassword"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-yaoqingmaguanli"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"text",placeholder:"请输入姓名","placeholder-style":"color: #ccc;"},model:{value:o.login.nickname,callback:function(n){o.$set(o.login,"nickname",n)},expression:"login.nickname"}})],1),t("v-uni-view",{staticClass:"loginFormRow"},[t("v-uni-text",{staticClass:"iconfont icon-yaoqingmaguanli"}),t("v-uni-input",{staticClass:"loginFormRowInput",attrs:{type:"text",placeholder:"请输入邀请码","placeholder-style":"color: #ccc;"},model:{value:o.login.invitationCode,callback:function(n){o.$set(o.login,"invitationCode",n)},expression:"login.invitationCode"}})],1),t("v-uni-view",{staticClass:"loginFoot"},[t("v-uni-button",{staticClass:"loginBtn",on:{click:function(n){arguments[0]=n=o.$handleEvent(n),o.bindLogin()}}},[o._v("注 册")])],1)],1)],1)},e=[]},c01d:function(o,n,i){"use strict";i.r(n);var t=i("d5f2"),e=i.n(t);for(var s in t)["default"].indexOf(s)<0&&function(o){i.d(n,o,(function(){return t[o]}))}(s);n["default"]=e.a},d5f2:function(o,n,i){"use strict";i("7a82"),Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var t=i("4845"),e={data:function(){return{login:{account:"",mobile:"",password:"",confirmPassword:"",invitationCode:"",nickname:"",code:""},verShow:!0,timer:60,codeFocus:!1,phoneFocus:!1}},onLoad:function(o){this.login.invitationCode=o.code},onShow:function(){this.type=uni.getSystemInfoSync().uniPlatform,this.$store.state.token&&uni.navigateTo({url:"/pages/index/index"})},methods:{GetCode:function(){var o=this;if(11==o.login.mobile.length){var n={from:"register",type:"mobile",account:o.login.mobile};(0,t.sendMsgCode)(n).then((function(n){if(1==n.code){uni.showToast({icon:"none",title:"发送成功！"}),o.codeFocus=!0,o.verShow=!1;var i=setInterval((function(){o.timer--,o.timer<=0&&(o.verShow=!0,o.timer=60,clearInterval(i))}),1e3)}else uni.showToast({icon:"none",title:n.msg})}))}else uni.showToast({icon:"none",title:"手机号不正确"})},bindLogin:function(){if(this.login.account.length<6)uni.showToast({icon:"none",title:"登陆账号最低需要6位字符！"});else if(this.login.mobile.length<11)uni.showToast({icon:"none",title:"手机号码不正确！"});else if(this.login.password.length<6)uni.showToast({icon:"none",title:"密码不正确"});else if(this.login.password==this.login.confirmPassword)if(this.login.code){var o={account:this.login.account,mobile:this.login.mobile,password:this.login.password,vcode:this.login.code,type:"mobile",nickname:this.login.nickname,invitationCode:this.login.invitationCode};(0,t.postReg)(o).then((function(o){uni.showToast({title:o.msg,icon:"none"}),1==o.status&&setTimeout((function(){uni.navigateTo({url:"/pages/login/login"})}),800)}))}else uni.showToast({icon:"none",title:"验证码不正确！"});else uni.showToast({icon:"none",title:"两次输入密码不一致！"})}}};n.default=e}}]);