import{A as He}from"./ApplicationLogo--Hstclxs.js";import{d as A,l as Oe,y as Ue,i as D,k as O,g as S,o as h,b as l,j as le,a as f,r as w,z as ue,T as qe,w as d,n as k,c as ne,u as T,P as z,A as Ve,B as fe,D as We,E as Ge,G as Qe,h as Ye,f as m,t as V}from"./app-CG7sjmso.js";const Ze={class:"relative"},Je=A({__name:"Dropdown",props:{align:{default:"right"},width:{default:"48"},contentClasses:{default:"py-1 bg-white dark:bg-gray-700"}},setup(e){const t=e,r=s=>{o.value&&s.key==="Escape"&&(o.value=!1)};Oe(()=>document.addEventListener("keydown",r)),Ue(()=>document.removeEventListener("keydown",r));const n=D(()=>({48:"w-48"})[t.width.toString()]),a=D(()=>t.align==="left"?"ltr:origin-top-left rtl:origin-top-right start-0":t.align==="right"?"ltr:origin-top-right rtl:origin-top-left end-0":"origin-top"),o=O(!1);return(s,i)=>(h(),S("div",Ze,[l("div",{onClick:i[0]||(i[0]=u=>o.value=!o.value)},[w(s.$slots,"trigger")]),le(l("div",{class:"fixed inset-0 z-40",onClick:i[1]||(i[1]=u=>o.value=!1)},null,512),[[ue,o.value]]),f(qe,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"opacity-0 scale-95","enter-to-class":"opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"opacity-100 scale-100","leave-to-class":"opacity-0 scale-95"},{default:d(()=>[le(l("div",{class:k(["absolute z-50 mt-2 rounded-md shadow-lg",[n.value,a.value]]),style:{display:"none"},onClick:i[2]||(i[2]=u=>o.value=!1)},[l("div",{class:k(["rounded-md ring-1 ring-black ring-opacity-5",s.contentClasses])},[w(s.$slots,"content")],2)],2),[[ue,o.value]])]),_:3})]))}}),de=A({__name:"DropdownLink",props:{href:{}},setup(e){return(t,r)=>(h(),ne(T(z),{href:t.href,class:"block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"},{default:d(()=>[w(t.$slots,"default")]),_:3},8,["href"]))}}),W=A({__name:"NavLink",props:{href:{},active:{type:Boolean}},setup(e){const t=e,r=D(()=>t.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out");return(n,a)=>(h(),ne(T(z),{href:n.href,class:k(r.value)},{default:d(()=>[w(n.$slots,"default")]),_:3},8,["href","class"]))}}),G=A({__name:"ResponsiveNavLink",props:{href:{},active:{type:Boolean}},setup(e){const t=e,r=D(()=>t.active?"block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out":"block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out");return(n,a)=>(h(),ne(T(z),{href:n.href,class:k(r.value)},{default:d(()=>[w(n.$slots,"default")]),_:3},8,["href","class"]))}});function p(e,t){Xe(e)&&(e="100%");var r=Ke(e);return e=t===360?e:Math.min(t,Math.max(0,parseFloat(e))),r&&(e=parseInt(String(e*t),10)/100),Math.abs(e-t)<1e-6?1:(t===360?e=(e<0?e%t+t:e%t)/parseFloat(String(t)):e=e%t/parseFloat(String(t)),e)}function Xe(e){return typeof e=="string"&&e.indexOf(".")!==-1&&parseFloat(e)===1}function Ke(e){return typeof e=="string"&&e.indexOf("%")!==-1}function er(e){return e=parseFloat(e),(isNaN(e)||e<0||e>1)&&(e=1),e}function E(e){return e<=1?"".concat(Number(e)*100,"%"):e}function Q(e){return e.length===1?"0"+e:String(e)}function rr(e,t,r){return{r:p(e,255)*255,g:p(t,255)*255,b:p(r,255)*255}}function Y(e,t,r){return r<0&&(r+=1),r>1&&(r-=1),r<1/6?e+(t-e)*(6*r):r<1/2?t:r<2/3?e+(t-e)*(2/3-r)*6:e}function tr(e,t,r){var n,a,o;if(e=p(e,360),t=p(t,100),r=p(r,100),t===0)a=r,o=r,n=r;else{var s=r<.5?r*(1+t):r+t-r*t,i=2*r-s;n=Y(i,s,e+1/3),a=Y(i,s,e),o=Y(i,s,e-1/3)}return{r:n*255,g:a*255,b:o*255}}function nr(e,t,r){e=p(e,255),t=p(t,255),r=p(r,255);var n=Math.max(e,t,r),a=Math.min(e,t,r),o=0,s=n,i=n-a,u=n===0?0:i/n;if(n===a)o=0;else{switch(n){case e:o=(t-r)/i+(t<r?6:0);break;case t:o=(r-e)/i+2;break;case r:o=(e-t)/i+4;break}o/=6}return{h:o,s:u,v:s}}function ar(e,t,r){e=p(e,360)*6,t=p(t,100),r=p(r,100);var n=Math.floor(e),a=e-n,o=r*(1-t),s=r*(1-a*t),i=r*(1-(1-a)*t),u=n%6,c=[r,s,o,o,i,r][u],C=[i,r,r,s,o,o][u],_=[o,o,i,r,r,s][u];return{r:c*255,g:C*255,b:_*255}}function or(e,t,r,n){var a=[Q(Math.round(e).toString(16)),Q(Math.round(t).toString(16)),Q(Math.round(r).toString(16))];return a.join("")}function ce(e){return g(e)/255}function g(e){return parseInt(e,16)}var ge={aliceblue:"#f0f8ff",antiquewhite:"#faebd7",aqua:"#00ffff",aquamarine:"#7fffd4",azure:"#f0ffff",beige:"#f5f5dc",bisque:"#ffe4c4",black:"#000000",blanchedalmond:"#ffebcd",blue:"#0000ff",blueviolet:"#8a2be2",brown:"#a52a2a",burlywood:"#deb887",cadetblue:"#5f9ea0",chartreuse:"#7fff00",chocolate:"#d2691e",coral:"#ff7f50",cornflowerblue:"#6495ed",cornsilk:"#fff8dc",crimson:"#dc143c",cyan:"#00ffff",darkblue:"#00008b",darkcyan:"#008b8b",darkgoldenrod:"#b8860b",darkgray:"#a9a9a9",darkgreen:"#006400",darkgrey:"#a9a9a9",darkkhaki:"#bdb76b",darkmagenta:"#8b008b",darkolivegreen:"#556b2f",darkorange:"#ff8c00",darkorchid:"#9932cc",darkred:"#8b0000",darksalmon:"#e9967a",darkseagreen:"#8fbc8f",darkslateblue:"#483d8b",darkslategray:"#2f4f4f",darkslategrey:"#2f4f4f",darkturquoise:"#00ced1",darkviolet:"#9400d3",deeppink:"#ff1493",deepskyblue:"#00bfff",dimgray:"#696969",dimgrey:"#696969",dodgerblue:"#1e90ff",firebrick:"#b22222",floralwhite:"#fffaf0",forestgreen:"#228b22",fuchsia:"#ff00ff",gainsboro:"#dcdcdc",ghostwhite:"#f8f8ff",goldenrod:"#daa520",gold:"#ffd700",gray:"#808080",green:"#008000",greenyellow:"#adff2f",grey:"#808080",honeydew:"#f0fff0",hotpink:"#ff69b4",indianred:"#cd5c5c",indigo:"#4b0082",ivory:"#fffff0",khaki:"#f0e68c",lavenderblush:"#fff0f5",lavender:"#e6e6fa",lawngreen:"#7cfc00",lemonchiffon:"#fffacd",lightblue:"#add8e6",lightcoral:"#f08080",lightcyan:"#e0ffff",lightgoldenrodyellow:"#fafad2",lightgray:"#d3d3d3",lightgreen:"#90ee90",lightgrey:"#d3d3d3",lightpink:"#ffb6c1",lightsalmon:"#ffa07a",lightseagreen:"#20b2aa",lightskyblue:"#87cefa",lightslategray:"#778899",lightslategrey:"#778899",lightsteelblue:"#b0c4de",lightyellow:"#ffffe0",lime:"#00ff00",limegreen:"#32cd32",linen:"#faf0e6",magenta:"#ff00ff",maroon:"#800000",mediumaquamarine:"#66cdaa",mediumblue:"#0000cd",mediumorchid:"#ba55d3",mediumpurple:"#9370db",mediumseagreen:"#3cb371",mediumslateblue:"#7b68ee",mediumspringgreen:"#00fa9a",mediumturquoise:"#48d1cc",mediumvioletred:"#c71585",midnightblue:"#191970",mintcream:"#f5fffa",mistyrose:"#ffe4e1",moccasin:"#ffe4b5",navajowhite:"#ffdead",navy:"#000080",oldlace:"#fdf5e6",olive:"#808000",olivedrab:"#6b8e23",orange:"#ffa500",orangered:"#ff4500",orchid:"#da70d6",palegoldenrod:"#eee8aa",palegreen:"#98fb98",paleturquoise:"#afeeee",palevioletred:"#db7093",papayawhip:"#ffefd5",peachpuff:"#ffdab9",peru:"#cd853f",pink:"#ffc0cb",plum:"#dda0dd",powderblue:"#b0e0e6",purple:"#800080",rebeccapurple:"#663399",red:"#ff0000",rosybrown:"#bc8f8f",royalblue:"#4169e1",saddlebrown:"#8b4513",salmon:"#fa8072",sandybrown:"#f4a460",seagreen:"#2e8b57",seashell:"#fff5ee",sienna:"#a0522d",silver:"#c0c0c0",skyblue:"#87ceeb",slateblue:"#6a5acd",slategray:"#708090",slategrey:"#708090",snow:"#fffafa",springgreen:"#00ff7f",steelblue:"#4682b4",tan:"#d2b48c",teal:"#008080",thistle:"#d8bfd8",tomato:"#ff6347",turquoise:"#40e0d0",violet:"#ee82ee",wheat:"#f5deb3",white:"#ffffff",whitesmoke:"#f5f5f5",yellow:"#ffff00",yellowgreen:"#9acd32"};function P(e){var t={r:0,g:0,b:0},r=1,n=null,a=null,o=null,s=!1,i=!1;return typeof e=="string"&&(e=lr(e)),typeof e=="object"&&(v(e.r)&&v(e.g)&&v(e.b)?(t=rr(e.r,e.g,e.b),s=!0,i=String(e.r).substr(-1)==="%"?"prgb":"rgb"):v(e.h)&&v(e.s)&&v(e.v)?(n=E(e.s),a=E(e.v),t=ar(e.h,n,a),s=!0,i="hsv"):v(e.h)&&v(e.s)&&v(e.l)&&(n=E(e.s),o=E(e.l),t=tr(e.h,n,o),s=!0,i="hsl"),Object.prototype.hasOwnProperty.call(e,"a")&&(r=e.a)),r=er(r),{ok:s,format:e.format||i,r:Math.min(255,Math.max(t.r,0)),g:Math.min(255,Math.max(t.g,0)),b:Math.min(255,Math.max(t.b,0)),a:r}}var ir="[-\\+]?\\d+%?",sr="[-\\+]?\\d*\\.\\d+%?",y="(?:".concat(sr,")|(?:").concat(ir,")"),Z="[\\s|\\(]+(".concat(y,")[,|\\s]+(").concat(y,")[,|\\s]+(").concat(y,")\\s*\\)?"),J="[\\s|\\(]+(".concat(y,")[,|\\s]+(").concat(y,")[,|\\s]+(").concat(y,")[,|\\s]+(").concat(y,")\\s*\\)?"),b={CSS_UNIT:new RegExp(y),rgb:new RegExp("rgb"+Z),rgba:new RegExp("rgba"+J),hsl:new RegExp("hsl"+Z),hsla:new RegExp("hsla"+J),hsv:new RegExp("hsv"+Z),hsva:new RegExp("hsva"+J),hex3:/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,hex6:/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,hex4:/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,hex8:/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/};function lr(e){if(e=e.trim().toLowerCase(),e.length===0)return!1;var t=!1;if(ge[e])e=ge[e],t=!0;else if(e==="transparent")return{r:0,g:0,b:0,a:0,format:"name"};var r=b.rgb.exec(e);return r?{r:r[1],g:r[2],b:r[3]}:(r=b.rgba.exec(e),r?{r:r[1],g:r[2],b:r[3],a:r[4]}:(r=b.hsl.exec(e),r?{h:r[1],s:r[2],l:r[3]}:(r=b.hsla.exec(e),r?{h:r[1],s:r[2],l:r[3],a:r[4]}:(r=b.hsv.exec(e),r?{h:r[1],s:r[2],v:r[3]}:(r=b.hsva.exec(e),r?{h:r[1],s:r[2],v:r[3],a:r[4]}:(r=b.hex8.exec(e),r?{r:g(r[1]),g:g(r[2]),b:g(r[3]),a:ce(r[4]),format:t?"name":"hex8"}:(r=b.hex6.exec(e),r?{r:g(r[1]),g:g(r[2]),b:g(r[3]),format:t?"name":"hex"}:(r=b.hex4.exec(e),r?{r:g(r[1]+r[1]),g:g(r[2]+r[2]),b:g(r[3]+r[3]),a:ce(r[4]+r[4]),format:t?"name":"hex8"}:(r=b.hex3.exec(e),r?{r:g(r[1]+r[1]),g:g(r[2]+r[2]),b:g(r[3]+r[3]),format:t?"name":"hex"}:!1)))))))))}function v(e){return!!b.CSS_UNIT.exec(String(e))}var N=2,pe=.16,ur=.05,fr=.05,dr=.15,Te=5,Ae=4,cr=[{index:7,opacity:.15},{index:6,opacity:.25},{index:5,opacity:.3},{index:5,opacity:.45},{index:5,opacity:.65},{index:5,opacity:.85},{index:4,opacity:.9},{index:3,opacity:.95},{index:2,opacity:.97},{index:1,opacity:.98}];function be(e){var t=e.r,r=e.g,n=e.b,a=nr(t,r,n);return{h:a.h*360,s:a.s,v:a.v}}function F(e){var t=e.r,r=e.g,n=e.b;return"#".concat(or(t,r,n))}function gr(e,t,r){var n=r/100,a={r:(t.r-e.r)*n+e.r,g:(t.g-e.g)*n+e.g,b:(t.b-e.b)*n+e.b};return a}function me(e,t,r){var n;return Math.round(e.h)>=60&&Math.round(e.h)<=240?n=r?Math.round(e.h)-N*t:Math.round(e.h)+N*t:n=r?Math.round(e.h)+N*t:Math.round(e.h)-N*t,n<0?n+=360:n>=360&&(n-=360),n}function ve(e,t,r){if(e.h===0&&e.s===0)return e.s;var n;return r?n=e.s-pe*t:t===Ae?n=e.s+pe:n=e.s+ur*t,n>1&&(n=1),r&&t===Te&&n>.1&&(n=.1),n<.06&&(n=.06),Number(n.toFixed(2))}function he(e,t,r){var n;return r?n=e.v+fr*t:n=e.v-dr*t,n>1&&(n=1),Number(n.toFixed(2))}function ee(e){for(var t=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},r=[],n=P(e),a=Te;a>0;a-=1){var o=be(n),s=F(P({h:me(o,a,!0),s:ve(o,a,!0),v:he(o,a,!0)}));r.push(s)}r.push(F(n));for(var i=1;i<=Ae;i+=1){var u=be(n),c=F(P({h:me(u,i),s:ve(u,i),v:he(u,i)}));r.push(c)}return t.theme==="dark"?cr.map(function(C){var _=C.index,H=C.opacity,I=F(gr(P(t.backgroundColor||"#141414"),P(r[_]),H*100));return I}):r}var X={red:"#F5222D",volcano:"#FA541C",orange:"#FA8C16",gold:"#FAAD14",yellow:"#FADB14",lime:"#A0D911",green:"#52C41A",cyan:"#13C2C2",blue:"#1890FF",geekblue:"#2F54EB",purple:"#722ED1",magenta:"#EB2F96",grey:"#666666"},R={},K={};Object.keys(X).forEach(function(e){R[e]=ee(X[e]),R[e].primary=R[e][5],K[e]=ee(X[e],{theme:"dark",backgroundColor:"#141414"}),K[e].primary=K[e][5]});var pr=R.blue,br=Symbol("iconContext"),je=function(){return Ve(br,{prefixCls:O("anticon"),rootClassName:O(""),csp:O()})};function ae(){return!!(typeof window<"u"&&window.document&&window.document.createElement)}function mr(e,t){return e&&e.contains?e.contains(t):!1}var ye="data-vc-order",vr="vc-icon-key",re=new Map;function Pe(){var e=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},t=e.mark;return t?t.startsWith("data-")?t:"data-".concat(t):vr}function oe(e){if(e.attachTo)return e.attachTo;var t=document.querySelector("head");return t||document.body}function hr(e){return e==="queue"?"prependQueue":e?"prepend":"append"}function $e(e){return Array.from((re.get(e)||e).children).filter(function(t){return t.tagName==="STYLE"})}function Me(e){var t=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{};if(!ae())return null;var r=t.csp,n=t.prepend,a=document.createElement("style");a.setAttribute(ye,hr(n)),r&&r.nonce&&(a.nonce=r.nonce),a.innerHTML=e;var o=oe(t),s=o.firstChild;if(n){if(n==="queue"){var i=$e(o).filter(function(u){return["prepend","prependQueue"].includes(u.getAttribute(ye))});if(i.length)return o.insertBefore(a,i[i.length-1].nextSibling),a}o.insertBefore(a,s)}else o.appendChild(a);return a}function yr(e){var t=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},r=oe(t);return $e(r).find(function(n){return n.getAttribute(Pe(t))===e})}function xr(e,t){var r=re.get(e);if(!r||!mr(document,r)){var n=Me("",t),a=n.parentNode;re.set(e,a),e.removeChild(n)}}function kr(e,t){var r=arguments.length>2&&arguments[2]!==void 0?arguments[2]:{},n=oe(r);xr(n,r);var a=yr(t,r);if(a)return r.csp&&r.csp.nonce&&a.nonce!==r.csp.nonce&&(a.nonce=r.csp.nonce),a.innerHTML!==e&&(a.innerHTML=e),a;var o=Me(e,r);return o.setAttribute(Pe(r),t),o}function xe(e){for(var t=1;t<arguments.length;t++){var r=arguments[t]!=null?Object(arguments[t]):{},n=Object.keys(r);typeof Object.getOwnPropertySymbols=="function"&&(n=n.concat(Object.getOwnPropertySymbols(r).filter(function(a){return Object.getOwnPropertyDescriptor(r,a).enumerable}))),n.forEach(function(a){wr(e,a,r[a])})}return e}function wr(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function ke(e){return typeof e=="object"&&typeof e.name=="string"&&typeof e.theme=="string"&&(typeof e.icon=="object"||typeof e.icon=="function")}function te(e,t,r){return r?fe(e.tag,xe({key:t},r,e.attrs),(e.children||[]).map(function(n,a){return te(n,"".concat(t,"-").concat(e.tag,"-").concat(a))})):fe(e.tag,xe({key:t},e.attrs),(e.children||[]).map(function(n,a){return te(n,"".concat(t,"-").concat(e.tag,"-").concat(a))}))}function Ie(e){return ee(e)[0]}function Ee(e){return e?Array.isArray(e)?e:[e]:[]}var Cr=`
.anticon {
  display: inline-block;
  color: inherit;
  font-style: normal;
  line-height: 0;
  text-align: center;
  text-transform: none;
  vertical-align: -0.125em;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.anticon > * {
  line-height: 1;
}

.anticon svg {
  display: inline-block;
}

.anticon::before {
  display: none;
}

.anticon .anticon-icon {
  display: block;
}

.anticon[tabindex] {
  cursor: pointer;
}

.anticon-spin::before,
.anticon-spin {
  display: inline-block;
  -webkit-animation: loadingCircle 1s infinite linear;
  animation: loadingCircle 1s infinite linear;
}

@-webkit-keyframes loadingCircle {
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

@keyframes loadingCircle {
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
`;function Ne(e){return e&&e.getRootNode&&e.getRootNode()}function _r(e){return ae()?Ne(e)instanceof ShadowRoot:!1}function Sr(e){return _r(e)?Ne(e):null}var Or=function(){var t=je(),r=t.prefixCls,n=t.csp,a=Ge(),o=Cr;r&&(o=o.replace(/anticon/g,r.value)),We(function(){if(ae()){var s=a.vnode.el,i=Sr(s);kr(o,"@ant-design-vue-icons",{prepend:!0,csp:n.value,attachTo:i})}})},Tr=["icon","primaryColor","secondaryColor"];function Ar(e,t){if(e==null)return{};var r=jr(e,t),n,a;if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(a=0;a<o.length;a++)n=o[a],!(t.indexOf(n)>=0)&&Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}function jr(e,t){if(e==null)return{};var r={},n=Object.keys(e),a,o;for(o=0;o<n.length;o++)a=n[o],!(t.indexOf(a)>=0)&&(r[a]=e[a]);return r}function B(e){for(var t=1;t<arguments.length;t++){var r=arguments[t]!=null?Object(arguments[t]):{},n=Object.keys(r);typeof Object.getOwnPropertySymbols=="function"&&(n=n.concat(Object.getOwnPropertySymbols(r).filter(function(a){return Object.getOwnPropertyDescriptor(r,a).enumerable}))),n.forEach(function(a){Pr(e,a,r[a])})}return e}function Pr(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}var M=Qe({primaryColor:"#333",secondaryColor:"#E6E6E6",calculated:!1});function $r(e){var t=e.primaryColor,r=e.secondaryColor;M.primaryColor=t,M.secondaryColor=r||Ie(t),M.calculated=!!r}function Mr(){return B({},M)}var x=function(t,r){var n=B({},t,r.attrs),a=n.icon,o=n.primaryColor,s=n.secondaryColor,i=Ar(n,Tr),u=M;if(o&&(u={primaryColor:o,secondaryColor:s||Ie(o)}),ke(a),!ke(a))return null;var c=a;return c&&typeof c.icon=="function"&&(c=B({},c,{icon:c.icon(u.primaryColor,u.secondaryColor)})),te(c.icon,"svg-".concat(c.name),B({},i,{"data-icon":c.name,width:"1em",height:"1em",fill:"currentColor","aria-hidden":"true"}))};x.props={icon:Object,primaryColor:String,secondaryColor:String,focusable:String};x.inheritAttrs=!1;x.displayName="IconBase";x.getTwoToneColors=Mr;x.setTwoToneColors=$r;function Ir(e,t){return Rr(e)||Fr(e,t)||Nr(e,t)||Er()}function Er(){throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Nr(e,t){if(e){if(typeof e=="string")return we(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);if(r==="Object"&&e.constructor&&(r=e.constructor.name),r==="Map"||r==="Set")return Array.from(e);if(r==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return we(e,t)}}function we(e,t){(t==null||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function Fr(e,t){var r=e==null?null:typeof Symbol<"u"&&e[Symbol.iterator]||e["@@iterator"];if(r!=null){var n=[],a=!0,o=!1,s,i;try{for(r=r.call(e);!(a=(s=r.next()).done)&&(n.push(s.value),!(t&&n.length===t));a=!0);}catch(u){o=!0,i=u}finally{try{!a&&r.return!=null&&r.return()}finally{if(o)throw i}}return n}}function Rr(e){if(Array.isArray(e))return e}function Fe(e){var t=Ee(e),r=Ir(t,2),n=r[0],a=r[1];return x.setTwoToneColors({primaryColor:n,secondaryColor:a})}function Br(){var e=x.getTwoToneColors();return e.calculated?[e.primaryColor,e.secondaryColor]:e.primaryColor}var Dr=A({name:"InsertStyles",setup:function(){return Or(),function(){return null}}}),Lr=["class","icon","spin","rotate","tabindex","twoToneColor","onClick"];function zr(e,t){return Vr(e)||qr(e,t)||Ur(e,t)||Hr()}function Hr(){throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ur(e,t){if(e){if(typeof e=="string")return Ce(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);if(r==="Object"&&e.constructor&&(r=e.constructor.name),r==="Map"||r==="Set")return Array.from(e);if(r==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return Ce(e,t)}}function Ce(e,t){(t==null||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function qr(e,t){var r=e==null?null:typeof Symbol<"u"&&e[Symbol.iterator]||e["@@iterator"];if(r!=null){var n=[],a=!0,o=!1,s,i;try{for(r=r.call(e);!(a=(s=r.next()).done)&&(n.push(s.value),!(t&&n.length===t));a=!0);}catch(u){o=!0,i=u}finally{try{!a&&r.return!=null&&r.return()}finally{if(o)throw i}}return n}}function Vr(e){if(Array.isArray(e))return e}function _e(e){for(var t=1;t<arguments.length;t++){var r=arguments[t]!=null?Object(arguments[t]):{},n=Object.keys(r);typeof Object.getOwnPropertySymbols=="function"&&(n=n.concat(Object.getOwnPropertySymbols(r).filter(function(a){return Object.getOwnPropertyDescriptor(r,a).enumerable}))),n.forEach(function(a){$(e,a,r[a])})}return e}function $(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function Wr(e,t){if(e==null)return{};var r=Gr(e,t),n,a;if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(a=0;a<o.length;a++)n=o[a],!(t.indexOf(n)>=0)&&Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}function Gr(e,t){if(e==null)return{};var r={},n=Object.keys(e),a,o;for(o=0;o<n.length;o++)a=n[o],!(t.indexOf(a)>=0)&&(r[a]=e[a]);return r}Fe(pr.primary);var j=function(t,r){var n,a=_e({},t,r.attrs),o=a.class,s=a.icon,i=a.spin,u=a.rotate,c=a.tabindex,C=a.twoToneColor,_=a.onClick,H=Wr(a,Lr),I=je(),U=I.prefixCls,ie=I.rootClassName,Re=(n={},$(n,ie.value,!!ie.value),$(n,U.value,!0),$(n,"".concat(U.value,"-").concat(s.name),!!s.name),$(n,"".concat(U.value,"-spin"),!!i||s.name==="loading"),n),q=c;q===void 0&&_&&(q=-1);var Be=u?{msTransform:"rotate(".concat(u,"deg)"),transform:"rotate(".concat(u,"deg)")}:void 0,De=Ee(C),se=zr(De,2),Le=se[0],ze=se[1];return f("span",_e({role:"img","aria-label":s.name},H,{onClick:_,class:[Re,o],tabindex:q}),[f(x,{icon:s,primaryColor:Le,secondaryColor:ze,style:Be},null),f(Dr,null,null)])};j.props={spin:Boolean,rotate:Number,icon:Object,twoToneColor:[String,Array]};j.displayName="AntdIcon";j.inheritAttrs=!1;j.getTwoToneColor=Br;j.setTwoToneColor=Fe;var Qr={icon:{tag:"svg",attrs:{viewBox:"64 64 896 896",focusable:"false"},children:[{tag:"path",attrs:{d:"M766.4 744.3c43.7 0 79.4-36.2 79.4-80.5 0-53.5-79.4-140.8-79.4-140.8S687 610.3 687 663.8c0 44.3 35.7 80.5 79.4 80.5zm-377.1-44.1c7.1 7.1 18.6 7.1 25.6 0l256.1-256c7.1-7.1 7.1-18.6 0-25.6l-256-256c-.6-.6-1.3-1.2-2-1.7l-78.2-78.2a9.11 9.11 0 00-12.8 0l-48 48a9.11 9.11 0 000 12.8l67.2 67.2-207.8 207.9c-7.1 7.1-7.1 18.6 0 25.6l255.9 256zm12.9-448.6l178.9 178.9H223.4l178.8-178.9zM904 816H120c-4.4 0-8 3.6-8 8v80c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-80c0-4.4-3.6-8-8-8z"}}]},name:"bg-colors",theme:"outlined"};function Se(e){for(var t=1;t<arguments.length;t++){var r=arguments[t]!=null?Object(arguments[t]):{},n=Object.keys(r);typeof Object.getOwnPropertySymbols=="function"&&(n=n.concat(Object.getOwnPropertySymbols(r).filter(function(a){return Object.getOwnPropertyDescriptor(r,a).enumerable}))),n.forEach(function(a){Yr(e,a,r[a])})}return e}function Yr(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}var L=function(t,r){var n=Se({},t,r.attrs);return f(j,Se({},n,{icon:Qr}),null)};L.displayName="BgColorsOutlined";L.inheritAttrs=!1;const Zr={class:"min-h-screen bg-gray-100 dark:bg-gray-900"},Jr={class:"border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"},Xr={class:"mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"},Kr={class:"flex h-16 justify-between"},et={class:"flex"},rt={class:"flex shrink-0 items-center"},tt={class:"hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"},nt={class:"hidden sm:ms-6 sm:flex sm:items-center"},at={key:0},ot={key:1},it={class:"relative ms-3"},st={class:"inline-flex rounded-md"},lt={type:"button",class:"inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"},ut={class:"-me-2 flex items-center sm:hidden"},ft={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},dt={class:"space-y-1 pb-3 pt-2"},ct={class:"border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"},gt={class:"px-4"},pt={class:"text-base font-medium text-gray-800 dark:text-gray-200"},bt={class:"text-sm font-medium text-gray-500"},mt={class:"mt-3 space-y-1"},vt={key:0,class:"bg-white shadow dark:bg-gray-800"},ht={class:"mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"},kt=A({__name:"AuthenticatedLayout",setup(e){const t=O(!1),r=()=>{t.value=!t.value,document.documentElement.classList.toggle("dark",t.value)};Oe(()=>{t.value=document.documentElement.classList.contains("dark")});const n=O(!1);return(a,o)=>(h(),S("div",null,[l("div",Zr,[l("nav",Jr,[l("div",Xr,[l("div",Kr,[l("div",et,[l("div",rt,[f(T(z),{href:a.route("dashboard")},{default:d(()=>[f(He,{class:"block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"})]),_:1},8,["href"])]),l("div",tt,[f(W,{href:a.route("dashboard"),active:a.route().current("dashboard")},{default:d(()=>o[1]||(o[1]=[m(" Dashboard ")])),_:1},8,["href","active"]),f(W,{href:a.route("packages.index"),active:a.route().current("packages.*")},{default:d(()=>o[2]||(o[2]=[m(" Packages ")])),_:1},8,["href","active"]),f(W,{href:a.route("package-add-ons.index"),active:a.route().current("package-add-ons.*")},{default:d(()=>o[3]||(o[3]=[m(" Package Add-Ons ")])),_:1},8,["href","active"])])]),l("div",nt,[l("button",{onClick:r,class:"hidden inline-flex items-center gap-2 px-4 py-2 rounded-md shadow transition-colors duration-300 text-gray-800 dark:text-gray-100 border border-gray-400 dark:border-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none"},[t.value?(h(),S("span",at,[f(T(L))])):(h(),S("span",ot,[f(T(L))]))]),l("div",it,[f(Je,{align:"right",width:"48"},{trigger:d(()=>[l("span",st,[l("button",lt,[m(V(a.$page.props.auth.user.name)+" ",1),o[4]||(o[4]=l("svg",{class:"-me-0.5 ms-2 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[l("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1))])])]),content:d(()=>[f(de,{href:a.route("profile.edit")},{default:d(()=>o[5]||(o[5]=[m(" Profile ")])),_:1},8,["href"]),f(de,{href:a.route("logout"),method:"post",as:"button"},{default:d(()=>o[6]||(o[6]=[m(" Log Out ")])),_:1},8,["href"])]),_:1})])]),l("div",ut,[l("button",{onClick:o[0]||(o[0]=s=>n.value=!n.value),class:"inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"},[(h(),S("svg",ft,[l("path",{class:k({hidden:n.value,"inline-flex":!n.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),l("path",{class:k({hidden:!n.value,"inline-flex":n.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),l("div",{class:k([{block:n.value,hidden:!n.value},"sm:hidden"])},[l("div",dt,[f(G,{href:a.route("dashboard"),active:a.route().current("dashboard")},{default:d(()=>o[7]||(o[7]=[m(" Dashboard ")])),_:1},8,["href","active"])]),l("div",ct,[l("div",gt,[l("div",pt,V(a.$page.props.auth.user.name),1),l("div",bt,V(a.$page.props.auth.user.email),1)]),l("div",mt,[f(G,{href:a.route("profile.edit")},{default:d(()=>o[8]||(o[8]=[m(" Profile ")])),_:1},8,["href"]),f(G,{href:a.route("logout"),method:"post",as:"button"},{default:d(()=>o[9]||(o[9]=[m(" Log Out ")])),_:1},8,["href"])])])],2)]),a.$slots.header?(h(),S("header",vt,[l("div",ht,[w(a.$slots,"header")])])):Ye("",!0),l("main",null,[w(a.$slots,"default")])])]))}});export{kt as _};
