webpackJsonp([10],{"02cY":function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=r("OtAX"),a=r("07eV"),s=!1;var o=function(e){s||r("5/eG")},i=r("VU/8")(n.a,a.a,!1,o,null,null);i.options.__file="pages/server/process-tree.vue",t.default=i.exports},"07eV":function(e,t,r){"use strict";var n=function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("v-content",[r("v-container",{attrs:{fluid:"",tag:"section",id:"grid"}},[r("v-layout",{attrs:{row:"",wrap:""}},[r("v-flex",{attrs:{"d-flex":"",xs12:"","order-xs5":""}},[r("v-layout",{attrs:{column:""}},[r("v-flex",{staticClass:"display-1 mb-3",attrs:{tag:"h1"}},[e._v("\n            Server - Process Tree\n          ")]),r("v-flex",[r("v-alert",{attrs:{type:"error",value:e.error}},[e._v("\n              "+e._s(e.error)+"\n            ")]),r("pre",[r("code",[e._v(e._s(e.items.pstree))])])],1)],1)],1)],1)],1)],1)};n._withStripped=!0;var a={render:n,staticRenderFns:[]};t.a=a},"5/eG":function(e,t,r){var n=r("gGRW");"string"==typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);r("rjj0")("35a95152",n,!1,{sourceMap:!1})},OtAX:function(e,t,r){"use strict";var n=r("Xxa5"),a=r.n(n),s=r("exGp"),o=r.n(s),i=r("Dd8w"),c=r.n(i),u=r("NYxO"),d=r("mtWM"),l=r.n(d);t.a={middleware:["authenticated"],components:{},computed:c()({},Object(u.mapGetters)({isAuthenticated:"auth/isAuthenticated",loggedUser:"auth/loggedUser",loggedToken:"auth/loggedToken"})),data:function(){return{error:"",items:{cpuinfo:"",server_cpu_usage:0}}},mounted:function(){this.initialize()},methods:{initialize:function(){var e=o()(a.a.mark(function e(){var t;return a.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return l.a.defaults.headers.common.Authorization="Bearer "+this.loggedToken,e.next=3,l.a.get(this.loggedUser.sub+"/api/server/information/process-tree");case 3:t=e.sent,this.items=t.data.data;case 5:case"end":return e.stop()}},e,this)}));return function(){return e.apply(this,arguments)}}()}}},gGRW:function(e,t,r){(e.exports=r("FZ+f")(!1)).push([e.i,'code{padding:5px}code:after,code:before,kbd:after,kbd:before{content:"";letter-spacing:-1px}',""])}});