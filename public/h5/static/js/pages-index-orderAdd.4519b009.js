(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-index-orderAdd"],{"0c12":function(e,n,t){"use strict";t.r(n);var i=t("534c"),a=t("d84c");for(var o in a)["default"].indexOf(o)<0&&function(e){t.d(n,e,(function(){return a[e]}))}(o);var r=t("f0c5"),c=Object(r["a"])(a["default"],i["b"],i["c"],!1,null,"621c0e26",null,!1,i["a"],void 0);n["default"]=c.exports},"534c":function(e,n,t){"use strict";t.d(n,"b",(function(){return a})),t.d(n,"c",(function(){return o})),t.d(n,"a",(function(){return i}));var i={hxNavbar:t("6bb3").default},a=function(){var e=this,n=e.$createElement,t=e._self._c||n;return t("v-uni-view",{staticStyle:{background:"#f5f5f5","min-height":"100vh"}},[t("hx-navbar",{ref:"hxnb",attrs:{config:e.config}}),t("v-uni-view",{staticClass:"viewPad"},[t("v-uni-view",{staticClass:"whiteBox p15 f14 c3 fb"},[t("v-uni-text",{staticClass:" corange fr"},[e._v("¥"+e._s(e.money))]),e._v("支付金额:")],1),"Y"==e.patmentInfo.payment_open.field_value?[e.patmentInfo.payment_name?t("v-uni-view",{staticClass:"whiteBox f14 c9 mt10",staticStyle:{padding:"30rpx 30rpx 20rpx 30rpx"}},[t("v-uni-text",{staticClass:"fb c3 fr"},[e._v(e._s(e.patmentInfo.payment_name.field_value))]),e._v(e._s(e.patmentInfo.payment_name.field_name)+":")],1):e._e(),e.patmentInfo.payment_account?t("v-uni-view",{staticClass:"whiteBox f14 c9",staticStyle:{padding:"20rpx 30rpx"}},[t("v-uni-text",{staticClass:"fb c3 fr"},[e._v(e._s(e.patmentInfo.payment_account.field_value))]),e._v(e._s(e.patmentInfo.payment_account.field_name)+":")],1):e._e(),t("v-uni-view",{staticClass:"whiteBox f12 corange tc",staticStyle:{padding:"20rpx 30rpx"}},[e._v("*付款成功后，请截图和拍照支付凭证上传。")])]:e._e(),t("v-uni-view",{staticClass:"whiteBox p15 mt10 f14 fb "},[e._v("请上传支付凭证:"),e.photo_url?t("v-uni-image",{staticClass:"payPicUp mt10",attrs:{src:e.photo_url},on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.upImage()}}}):t("v-uni-view",{staticClass:"payPicUp mt10",on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.upImage()}}})],1),t("v-uni-view",{staticClass:"formFoot pt30"},[t("v-uni-button",{staticClass:"submitBtn",on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.submit()}}},[e._v("确认支付")])],1)],2)],1)},o=[]},b857:function(e,n,t){"use strict";t("7a82"),Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var i=t("34da"),a=t("cc51"),o={data:function(){return{config:{title:"订单支付",backgroundColor:[1,"#fff"]},id:"",money:"",photo_url:"",patmentInfo:{}}},onLoad:function(e){this.id=e.id,this.money=e.money},onShow:function(){var e=this;(0,i.getPaymentlnfo)().then((function(n){e.patmentInfo=n.data}))},onUnload:function(){},methods:{upImage:function(){var e=this;uni.chooseImage({count:1,success:function(n){var t=n.tempFilePaths[0];uni.getImageInfo({src:t,success:function(n){(0,a.pathToBase64)(n.path).then((function(n){(0,i.uploadImage)({image:n,type:"order"}).then((function(n){uni.showToast({title:n.msg,icon:"none"}),1==n.status&&(e.photo_url=n.data.file_url)}))})).catch((function(e){console.error(e)}))}})}})},submit:function(){if(this.photo_url){var e={spu_id:this.id,file_url:this.photo_url,payment:"offline"};(0,i.createGoodsOrder)(e).then((function(e){1==e.status?uni.showModal({title:"提交成功！",confirmText:"回首页",cancelText:"账户中心",success:function(e){e.confirm?uni.reLaunch({url:"/pages/index/index"}):uni.reLaunch({url:"/pages/me/index"})}}):uni.showToast({title:e.msg,icon:"none"})}))}else uni.showToast({title:"请上传支付凭证",icon:"none"})}}};n.default=o},cc51:function(e,n,t){"use strict";t("7a82");var i=t("4ea4").default;Object.defineProperty(n,"__esModule",{value:!0}),n.base64ToPath=function(e){return new Promise((function(n,t){if("object"===("undefined"===typeof window?"undefined":(0,a.default)(window))&&"document"in window){e=e.split(",");var i=e[0].match(/:(.*?);/)[1],c=atob(e[1]),u=c.length,s=new Uint8Array(u);while(u--)s[u]=c.charCodeAt(u);return n((window.URL||window.webkitURL).createObjectURL(new Blob([s],{type:i})))}var f=e.split(",")[0].match(/data\:\S+\/(\S+);/);f?f=f[1]:t(new Error("base64 error"));var d=function(){return Date.now()+String(r++)}()+"."+f;if("object"!==("undefined"===typeof plus?"undefined":(0,a.default)(plus)))if("object"===("undefined"===typeof wx?"undefined":(0,a.default)(wx))&&wx.canIUse("getFileSystemManager")){l=wx.env.USER_DATA_PATH+"/"+d;wx.getFileSystemManager().writeFile({filePath:l,data:o(e),encoding:"base64",success:function(){n(l)},fail:function(e){t(e)}})}else t(new Error("not support"));else{var l="_doc/uniapp_temp/"+d;if(!function(e,n){for(var t=e.split("."),i=n.split("."),a=!1,o=0;o<i.length;o++){var r=t[o]-i[o];if(0!==r){a=r>0;break}}return a}("Android"===plus.os.name?"1.9.9.80627":"1.9.9.80472",plus.runtime.innerVersion))return void plus.io.resolveLocalFileSystemURL("_doc",(function(i){i.getDirectory("uniapp_temp",{create:!0,exclusive:!1},(function(i){i.getFile(d,{create:!0,exclusive:!1},(function(i){i.createWriter((function(i){i.onwrite=function(){n(l)},i.onerror=t,i.seek(0),i.writeAsBinary(o(e))}),t)}),t)}),t)}),t);var p=new plus.nativeObj.Bitmap(d);p.loadBase64Data(e,(function(){p.save(l,{},(function(){p.clear(),n(l)}),(function(e){p.clear(),t(e)}))}),(function(e){p.clear(),t(e)}))}}))},n.pathToBase64=function(e){return new Promise((function(n,t){if("object"===("undefined"===typeof window?"undefined":(0,a.default)(window))&&"document"in window){if("function"===typeof FileReader){var i=new XMLHttpRequest;return i.open("GET",e,!0),i.responseType="blob",i.onload=function(){if(200===this.status){var e=new FileReader;e.onload=function(e){n(e.target.result)},e.onerror=t,e.readAsDataURL(this.response)}},i.onerror=t,void i.send()}var o=document.createElement("canvas"),r=o.getContext("2d"),c=new Image;return c.onload=function(){o.width=c.width,o.height=c.height,r.drawImage(c,0,0),n(o.toDataURL()),o.height=o.width=0},c.onerror=t,void(c.src=e)}"object"!==("undefined"===typeof plus?"undefined":(0,a.default)(plus))?"object"===("undefined"===typeof wx?"undefined":(0,a.default)(wx))&&wx.canIUse("getFileSystemManager")?wx.getFileSystemManager().readFile({filePath:e,encoding:"base64",success:function(e){n("data:image/png;base64,"+e.data)},fail:function(e){t(e)}}):t(new Error("not support")):plus.io.resolveLocalFileSystemURL(function(e){if(0===e.indexOf("_www")||0===e.indexOf("_doc")||0===e.indexOf("_documents")||0===e.indexOf("_downloads"))return e;if(0===e.indexOf("file://"))return e;if(0===e.indexOf("/storage/emulated/0/"))return e;if(0===e.indexOf("/")){var n=plus.io.convertAbsoluteFileSystem(e);if(n!==e)return n;e=e.substr(1)}return"_www/"+e}(e),(function(e){e.file((function(e){var i=new plus.io.FileReader;i.onload=function(e){n(e.target.result)},i.onerror=function(e){t(e)},i.readAsDataURL(e)}),(function(e){t(e)}))}),(function(e){t(e)}))}))};var a=i(t("53ca"));function o(e){var n=e.split(",");return n[n.length-1]}t("c975"),t("d3b7"),t("d9e2"),t("d401"),t("ac1f"),t("466d"),t("81b2"),t("0eb6"),t("b7ef"),t("8bd4"),t("ace4"),t("5cc6"),t("907a"),t("9a8c"),t("a975"),t("735e"),t("c1ac"),t("d139"),t("3a7b"),t("986a"),t("1d02"),t("d5d6"),t("82f8"),t("e91f"),t("60bd"),t("5f96"),t("3280"),t("3fcc"),t("ca91"),t("25a1"),t("cd26"),t("3c5d"),t("2954"),t("649e"),t("219c"),t("b39a"),t("72f7"),t("3ca3"),t("ddb0"),t("2b3d"),t("9861");var r=0},d84c:function(e,n,t){"use strict";t.r(n);var i=t("b857"),a=t.n(i);for(var o in i)["default"].indexOf(o)<0&&function(e){t.d(n,e,(function(){return i[e]}))}(o);n["default"]=a.a}}]);