(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-index-index"],{"011f":function(t,e,a){t.exports=a.p+"static/img/titleBg.edfb5044.png"},"31c1":function(t,e,a){"use strict";a.r(e);var i=a("ea03"),n=a.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=n.a},b589:function(t,e,a){"use strict";a.r(e);var i=a("d3be"),n=a("31c1");for(var o in n)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(o);var s=a("f0c5"),r=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"0a7506d4",null,!1,i["a"],void 0);e["default"]=r.exports},d3be:function(t,e,a){"use strict";a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return i}));var i={hxNavbar:a("fafe").default,mescrollBody:a("96a0").default,oRow:a("d9dc").default,oCol:a("301f").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",[i("hx-navbar",{ref:"hxnb",attrs:{config:t.config}},[i("template",{attrs:{slot:"left"},slot:"left"},[i("v-uni-view",{staticClass:"topBarTitle"},[t._v("KC")])],1),i("template",{attrs:{slot:"right"},slot:"right"},[i("v-uni-navigator",{staticClass:"topBarRight",attrs:{url:"/pages/me/index"}},[i("v-uni-text",{staticClass:"iconfont icon-zhanghao"})],1)],1)],2),t.banner[0]?i("v-uni-swiper",{staticClass:"moneyBanner",attrs:{circular:!0}},t._l(t.banner,(function(t,e){return i("v-uni-swiper-item",[i("v-uni-image",{staticClass:"moneyBannerPic",attrs:{src:t.image_url,mode:"aspectFill"}})],1)})),1):t._e(),i("v-uni-view",{staticStyle:{padding:"30rpx 10rpx"}},[i("mescroll-body",{ref:"mescrollRef",attrs:{up:t.upOption},on:{init:function(e){arguments[0]=e=t.$handleEvent(e),t.mescrollInit.apply(void 0,arguments)},down:function(e){arguments[0]=e=t.$handleEvent(e),t.downCallback.apply(void 0,arguments)},up:function(e){arguments[0]=e=t.$handleEvent(e),t.upCallback.apply(void 0,arguments)},emptyclick:function(e){arguments[0]=e=t.$handleEvent(e),t.emptyClick.apply(void 0,arguments)}}},t._l(t.projectList,(function(e,n){return i("v-uni-view",{staticStyle:{"padding-bottom":"20rpx",margin:"0 20rpx"}},[i("v-uni-navigator",{attrs:{url:"/pages/index/view?id="+e.spu_id}},[i("v-uni-view",{staticClass:"blueShadowBoxHeaer"},[i("v-uni-view",{staticClass:"blueShadowBoxHeaerMain",staticStyle:{"padding-right":"60rpx"}},[i("v-uni-view",{staticClass:"boxHeaerTitle"},[t._v(t._s(e.title))])],1),i("v-uni-image",{staticClass:"blueShadowBoxHeaerBg",attrs:{src:a("011f"),mode:"aspectFill"}})],1),i("v-uni-view",{staticClass:"blueShadowBox",staticStyle:{"border-top-left-radius":"0","border-top-right-radius":"0","box-shadow":"0 0 18rpx 0 #CBD6E4"}},[i("v-uni-image",{staticStyle:{width:"690rpx",height:"314rpx"},attrs:{src:e.image_url}}),i("v-uni-view",{staticClass:"p15"},[i("o-row",{staticClass:"f11 c9"},[i("o-col",{attrs:{span:"18"}},[i("v-uni-view",{staticClass:"fb corange f14"},[t._v("¥"+t._s(e.sell_price))]),i("v-uni-view",{staticClass:"c9 f11",staticStyle:{"text-decoration":"line-through"}},[t._v("¥"+t._s(e.market_price))])],1),i("o-col",{staticClass:"tr",staticStyle:{"padding-top":"4rpx"},attrs:{span:"6"}},[i("v-uni-button",{staticClass:"productFootBtn"},[t._v("购买")])],1)],1),i("v-uni-view",{staticClass:"f12 c6 pt15 mt15",staticStyle:{"border-top":"2rpx  #eee  dashed"}},[t._v(t._s(e.description))])],1)],1)],1)],1)})),1)],1)],1)},o=[]},ea03:function(t,e,a){"use strict";a("7a82");var i=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("99af");var n=i(a("9ae8")),o=a("e942"),s={mixins:[n.default],data:function(){return{config:{back:!1,leftSlot:!0,centerSlot:!0,rightSlot:!0,backgroundColor:[1,"#0983FF"]},projectList:[],tabsNav:["进行中","已完成"],tabsNavIndex:0,array:[{name:"美元"},{name:"欧元"},{name:"英镑"},{name:"日元"}],index:0,banner:[],upOption:{page:{num:0,size:8},noMoreSize:1,empty:{tip:"~ 暂无数据 ~"},textNoMore:"没有更多了"},videoSrc:"",videoPic:"",videoShow:!1}},onShow:function(){var t=this;(0,o.getSwiper)({location_code:"wap_index_banner"}).then((function(e){t.banner=e.data}))},onUnload:function(){this.mescroll.resetUpScroll()},methods:{emptyClick:function(){uni.showToast({title:"点击了按钮,具体逻辑自行实现"})},upCallback:function(t){var e=this,a={page:t.num,pagesize:t.size};(0,o.goodsList)(a).then((function(a){var i=a.data.data;e.mescroll.endSuccess(i.length),1==t.num&&(e.projectList=[]),e.projectList=e.projectList.concat(i),console.log(i,"resData----")})).catch((function(){e.mescroll.endErr()}))}}};e.default=s}}]);