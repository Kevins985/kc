(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-me-index"],{"199c":function(t,a,e){"use strict";e.r(a);var s=e("52e3"),i=e("9fe0");for(var r in i)["default"].indexOf(r)<0&&function(t){e.d(a,t,(function(){return i[t]}))}(r);var n=e("f0c5"),o=Object(n["a"])(i["default"],s["b"],s["c"],!1,null,"70e79312",null,!1,s["a"],void 0);a["default"]=o.exports},3746:function(t,a,e){t.exports=e.p+"static/img/noPhoto.590367f8.png"},"52e3":function(t,a,e){"use strict";e.d(a,"b",(function(){return i})),e.d(a,"c",(function(){return r})),e.d(a,"a",(function(){return s}));var s={hxNavbar:e("6bb3").default,oRow:e("03fe").default,oCol:e("5759").default,qiunDataCharts:e("1972").default},i=function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("v-uni-view",{staticStyle:{background:"#F5F5F5","min-height":"100vh"}},[s("hx-navbar",{ref:"hxnb",staticClass:"noShadow",attrs:{config:t.config}}),s("v-uni-view",{staticClass:"myHeader",staticStyle:{"padding-bottom":"50rpx"}},[s("v-uni-view",{staticClass:"myHeaderUser"},[s("v-uni-navigator",{staticClass:"myHeaderUserRight",attrs:{url:"/pages/me/share"}},[t._v("分享")]),t.userInfo.photo_url?s("v-uni-image",{staticClass:"myHeaderUserPic",attrs:{src:t.userInfo.photo_url}}):s("v-uni-image",{staticClass:"myHeaderUserPic",attrs:{src:e("3746")}}),s("v-uni-view",{staticClass:"myHeaderUserMain"},[s("v-uni-view",{staticClass:"fb f16 c1 pt10"},[t.userInfo.nickname?[t._v(t._s(t.userInfo.nickname))]:[t._v(t._s(t.userInfo.account))]],2),s("v-uni-view",{staticClass:"f13 c6 pt10"},[t._v(t._s(t.userInfo.user_no))])],1)],1)],1),s("v-uni-view",{staticClass:"viewPad"},[s("v-uni-view",{staticClass:"whiteBox f14 c3"},[s("v-uni-navigator",{attrs:{url:"/pages/me/order"}},[s("v-uni-view",{staticClass:"p15 fb navRightIcon",staticStyle:{"border-bottom":"2rpx solid rgba(0, 0, 0, .05)",position:"relative"}},[t._v("我的订单")])],1),s("v-uni-view",{staticClass:"p15"},t._l(t.goodsOrder,(function(a,e){return s("v-uni-navigator",{staticStyle:{overflow:"hidden"},attrs:{url:""}},[s("v-uni-image",{staticClass:"fl",staticStyle:{width:"160rpx",height:"90rpx"},attrs:{src:a.goods.image_url}}),s("v-uni-view",{staticStyle:{"margin-left":"200rpx"}},[s("v-uni-view",{staticClass:"f14 orderTitle"},[t._v(t._s(a.goods.title))]),1==a.status?s("v-uni-view",{staticClass:"pt5 f12 corange"},[t._v("进行中")]):t._e(),2==a.status?s("v-uni-view",{staticClass:"pt5 f12 c9"},[t._v("已完成")]):t._e()],1)],1)})),1)],1),s("v-uni-view",{staticClass:"whiteBox f14 c3 mt10"},[s("v-uni-navigator",{attrs:{url:"/pages/me/project"}},[s("v-uni-view",{staticClass:"p15 fb navRightIcon",staticStyle:{"border-bottom":"2rpx solid rgba(0, 0, 0, .05)",position:"relative"}},[t._v("我的活动")])],1),s("v-uni-view",{staticClass:"p15",staticStyle:{"padding-bottom":"0"}},[s("v-uni-view",{staticClass:"f14 fb tc"},[t._v(t._s(t.projectData.project_name)+" - "+t._s(t.projectData.project_number_name))]),s("v-uni-view",{staticClass:"pt15"},[s("o-row",{attrs:{cols:"2",gutter:"10"}},[s("o-col",[s("v-uni-view",{staticClass:"tc f12 c9"},[t._v("本轮总进度")]),s("qiun-data-charts",{staticClass:"mt5",staticStyle:{height:"280rpx"},attrs:{type:"arcbar",opts:t.totalOpts,chartData:t.totalChartData}})],1),s("o-col",[s("v-uni-view",{staticClass:"tc f12 c9"},[t._v("个人出彩进度")]),s("qiun-data-charts",{staticClass:"mt5",staticStyle:{height:"280rpx"},attrs:{type:"arcbar",opts:t.userOpts,chartData:t.userChartData}})],1)],1),s("v-uni-view",{staticClass:"f14 c9",staticStyle:{padding:"30rpx 0",overflow:"hidden"}},[s("v-uni-text",{staticClass:"fr c3 fb"},[t._v("第 "+t._s(t.projectData.user_number)+" 位")]),t._v("我的位置")],1),s("v-uni-view",{staticClass:"f14 c9",staticStyle:{padding:"30rpx 0",overflow:"hidden"}},[s("v-uni-text",{staticClass:"fr c3 fb"},[t._v(t._s(t.projectData.team_cnt))]),t._v("我的团队")],1),s("v-uni-view",{staticClass:"f14 c9",staticStyle:{padding:"30rpx 0",overflow:"hidden"}},[s("v-uni-text",{staticClass:"fr c3 fb"},[t._v(t._s(t.projectData.team_money))]),t._v("我的业绩")],1),s("v-uni-view",{staticClass:"f14 c9",staticStyle:{"border-top":"2rpx solid rgba(0, 0, 0, .05)",padding:"30rpx 0",overflow:"hidden"}},[s("v-uni-text",{staticClass:"fr c3 fb"},[t._v(t._s(t.projectData.invite_cnt)+" 人")]),t._v("我的直推")],1),s("v-uni-view",{staticClass:"c9 f14",staticStyle:{"border-top":"2rpx solid rgba(0, 0, 0, .05)",padding:"30rpx 0",overflow:"hidden"}},[s("v-uni-text",{staticClass:"fr c3 fb"},[t._v(t._s(t.projectData.point)+" W")]),t._v("出彩奖励")],1)],1)],1)],1),s("v-uni-view",{staticClass:"navBox mt10"},[s("v-uni-navigator",{staticClass:"navBoxRow navRightIcon",attrs:{url:"/pages/me/pwd"}},[s("v-uni-text",{staticClass:"iconfont icon-shezhimima"}),t._v("修改密码")],1)],1),t.userInfo.token?s("v-uni-view",{staticClass:"navBox mt10"},[s("v-uni-view",{staticClass:"navBoxRow navRightIcon",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.logout()}}},[s("v-uni-text",{staticClass:"iconfont icon-zhuxiao"}),t._v("退出登录")],1)],1):t._e()],1)],1)},r=[]},5685:function(t,a,e){"use strict";e("7a82"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0,e("e9c4");var s=e("34da"),i=e("c641"),r={data:function(){return{config:{fixed:!1,title:"我的",backgroundColor:[1,"#C5E3FF"],color:"#111"},userInfo:{},goodsOrder:[],projectData:{},totalChartData:{},totalOpts:{color:["#1890FF","#91CB74","#FAC858","#EE6666","#73C0DE","#3CA272","#FC8452","#9A60B4","#ea7ccc"],padding:[0,0,0,0],title:{name:"80%",fontSize:12,color:"#3785D5"},subtitle:{name:""},extra:{arcbar:{type:"circle",width:6,backgroundColor:"#E9E9E9",startAngle:1.5,endAngle:.25,gap:2}}},userChartData:{},userOpts:{color:["#1890FF","#91CB74","#FAC858","#EE6666","#73C0DE","#3CA272","#FC8452","#9A60B4","#ea7ccc"],padding:[0,0,0,0],title:{name:"80%",fontSize:12,color:"#3785D5"},subtitle:{name:""},extra:{arcbar:{type:"circle",width:6,backgroundColor:"#E9E9E9",startAngle:1.5,endAngle:.25,gap:2}}}}},watch:{"$store.state.userInfo":{handler:function(t,a){this.userInfo=t}}},onLoad:function(t){this.userInfo=this.$store.state.userInfo},onShow:function(){var t=this;this.userInfo.token&&(this.$store.dispatch("setUserData"),(0,s.goodsOrders)({page:1,status:1}).then((function(a){t.goodsOrder=a.data.data})))},onReady:function(){this.getServerData()},methods:{getServerData:function(){var t=this;(0,s.getCurrencyProject)({page:1,status:1}).then((function(a){t.projectData=a.data;var e={series:[{name:"正确率",color:"#3785D5",data:t.projectData.project_order_cnt/t.projectData.project_total_cnt}]},s={series:[{name:"正确率",color:"#3785D5",data:t.projectData.user_progress/t.projectData.user_total_cnt}]};t.totalOpts.title.name=t.projectData.project_order_cnt+" / "+t.projectData.project_total_cnt,t.userOpts.title.name=t.projectData.user_progress+" / "+t.projectData.user_total_cnt,t.totalChartData=JSON.parse(JSON.stringify(e)),t.userChartData=JSON.parse(JSON.stringify(s))}))},logout:function(){var t=this;uni.showModal({title:"您确定要退出登录",confirmText:"确定",cancelText:"取消",success:function(a){a.confirm&&(uni.setStorageSync("noticeNewId",""),(0,i.logout)().then((function(t){uni.showToast({title:"退出成功！",icon:"none"})})),setTimeout((function(){t.$store.dispatch("reLogin")}),800))}})}}};a.default=r},"9fe0":function(t,a,e){"use strict";e.r(a);var s=e("5685"),i=e.n(s);for(var r in s)["default"].indexOf(r)<0&&function(t){e.d(a,t,(function(){return s[t]}))}(r);a["default"]=i.a}}]);