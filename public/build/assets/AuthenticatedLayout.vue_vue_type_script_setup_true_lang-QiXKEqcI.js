import{A as D}from"./ApplicationLogo-YT1DDKZ9.js";import{d as p,k as M,l as N,i as m,p as B,g as v,o as l,b as e,j as $,a,r as g,q as C,T as j,w as s,n as u,c as _,u as y,P as b,h as E,f as d,t as k}from"./app-Cn2VnDoH.js";const z={class:"relative"},P=p({__name:"Dropdown",props:{align:{default:"right"},width:{default:"48"},contentClasses:{default:"py-1 bg-white dark:bg-gray-700"}},setup(i){const o=i,r=f=>{n.value&&f.key==="Escape"&&(n.value=!1)};M(()=>document.addEventListener("keydown",r)),N(()=>document.removeEventListener("keydown",r));const t=m(()=>({48:"w-48"})[o.width.toString()]),h=m(()=>o.align==="left"?"ltr:origin-top-left rtl:origin-top-right start-0":o.align==="right"?"ltr:origin-top-right rtl:origin-top-left end-0":"origin-top"),n=B(!1);return(f,c)=>(l(),v("div",z,[e("div",{onClick:c[0]||(c[0]=w=>n.value=!n.value)},[g(f.$slots,"trigger")]),$(e("div",{class:"fixed inset-0 z-40",onClick:c[1]||(c[1]=w=>n.value=!1)},null,512),[[C,n.value]]),a(j,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"opacity-0 scale-95","enter-to-class":"opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"opacity-100 scale-100","leave-to-class":"opacity-0 scale-95"},{default:s(()=>[$(e("div",{class:u(["absolute z-50 mt-2 rounded-md shadow-lg",[t.value,h.value]]),style:{display:"none"},onClick:c[2]||(c[2]=w=>n.value=!1)},[e("div",{class:u(["rounded-md ring-1 ring-black ring-opacity-5",f.contentClasses])},[g(f.$slots,"content")],2)],2),[[C,n.value]])]),_:3})]))}}),L=p({__name:"DropdownLink",props:{href:{}},setup(i){return(o,r)=>(l(),_(y(b),{href:o.href,class:"block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"},{default:s(()=>[g(o.$slots,"default")]),_:3},8,["href"]))}}),S=p({__name:"NavLink",props:{href:{},active:{type:Boolean}},setup(i){const o=i,r=m(()=>o.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out");return(t,h)=>(l(),_(y(b),{href:t.href,class:u(r.value)},{default:s(()=>[g(t.$slots,"default")]),_:3},8,["href","class"]))}}),x=p({__name:"ResponsiveNavLink",props:{href:{},active:{type:Boolean}},setup(i){const o=i,r=m(()=>o.active?"block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out":"block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out");return(t,h)=>(l(),_(y(b),{href:t.href,class:u(r.value)},{default:s(()=>[g(t.$slots,"default")]),_:3},8,["href","class"]))}}),V={class:"min-h-screen bg-gray-100 dark:bg-gray-900"},A={class:"border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"},O={class:"mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"},T={class:"flex h-16 justify-between"},q={class:"flex"},R={class:"flex shrink-0 items-center"},U={class:"hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"},F={class:"hidden sm:ms-6 sm:flex sm:items-center"},G={class:"relative ms-3"},H={class:"inline-flex rounded-md"},I={type:"button",class:"inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"},J={class:"-me-2 flex items-center sm:hidden"},K={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},Q={class:"space-y-1 pb-3 pt-2"},W={class:"border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"},X={class:"px-4"},Y={class:"text-base font-medium text-gray-800 dark:text-gray-200"},Z={class:"text-sm font-medium text-gray-500"},ee={class:"mt-3 space-y-1"},te={key:0,class:"bg-white shadow dark:bg-gray-800"},re={class:"mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"},ae=p({__name:"AuthenticatedLayout",setup(i){const o=B(!1);return(r,t)=>(l(),v("div",null,[e("div",V,[e("nav",A,[e("div",O,[e("div",T,[e("div",q,[e("div",R,[a(y(b),{href:r.route("dashboard")},{default:s(()=>[a(D,{class:"block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"})]),_:1},8,["href"])]),e("div",U,[a(S,{href:r.route("dashboard"),active:r.route().current("dashboard")},{default:s(()=>t[1]||(t[1]=[d(" Dashboard ")])),_:1},8,["href","active"])])]),e("div",F,[e("div",G,[a(P,{align:"right",width:"48"},{trigger:s(()=>[e("span",H,[e("button",I,[d(k(r.$page.props.auth.user.name)+" ",1),t[2]||(t[2]=e("svg",{class:"-me-0.5 ms-2 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[e("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1))])])]),content:s(()=>[a(L,{href:r.route("profile.edit")},{default:s(()=>t[3]||(t[3]=[d(" Profile ")])),_:1},8,["href"]),a(L,{href:r.route("logout"),method:"post",as:"button"},{default:s(()=>t[4]||(t[4]=[d(" Log Out ")])),_:1},8,["href"])]),_:1})])]),e("div",J,[e("button",{onClick:t[0]||(t[0]=h=>o.value=!o.value),class:"inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"},[(l(),v("svg",K,[e("path",{class:u({hidden:o.value,"inline-flex":!o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),e("path",{class:u({hidden:!o.value,"inline-flex":o.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),e("div",{class:u([{block:o.value,hidden:!o.value},"sm:hidden"])},[e("div",Q,[a(x,{href:r.route("dashboard"),active:r.route().current("dashboard")},{default:s(()=>t[5]||(t[5]=[d(" Dashboard ")])),_:1},8,["href","active"])]),e("div",W,[e("div",X,[e("div",Y,k(r.$page.props.auth.user.name),1),e("div",Z,k(r.$page.props.auth.user.email),1)]),e("div",ee,[a(x,{href:r.route("profile.edit")},{default:s(()=>t[6]||(t[6]=[d(" Profile ")])),_:1},8,["href"]),a(x,{href:r.route("logout"),method:"post",as:"button"},{default:s(()=>t[7]||(t[7]=[d(" Log Out ")])),_:1},8,["href"])])])],2)]),r.$slots.header?(l(),v("header",te,[e("div",re,[g(r.$slots,"header")])])):E("",!0),e("main",null,[g(r.$slots,"default")])])]))}});export{ae as _};
