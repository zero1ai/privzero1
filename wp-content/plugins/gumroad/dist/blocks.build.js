!function(e){function t(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,t),o.l=!0,o.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=0)}([function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});n(1)},function(e,t,n){"use strict";function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function o(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!==typeof t&&"function"!==typeof t?e:t}function a(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}var l=n(2),u=(n.n(l),function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}()),c=wp.i18n.__,i=wp.element.Component,s=wp.blocks.registerBlockType,p=wp.editor,m=p.InspectorControls,d=p.RichText,f=wp.components,b=f.SelectControl,g=f.PanelBody,h=f.CheckboxControl,y=/(https:\/\/gumroad\.com\/a\/.+\/)|(https:\/\/gumroad\.com\/l\/)|(https:\/\/gumroad\.com\/)|(https:\/\/gum\.co\/l\/)|(https:\/\/gum\.co\/)/g,w=function(e){function t(e){return r(this,t),o(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return a(t,e),u(t,[{key:"extract_id",value:function(e){var t=e.replace(y,"");return console.log("id: ",t),t}},{key:"render",value:function(){var e=this,t=[{value:"none",label:c("Inline link (anchor tag)")},{value:"overlay",label:c("Overlay")},{value:"embed",label:c("Embed")}],n=this.props,r=n.setAttributes,o=n.attributes,a=(o.id,o.type),l=o.button,u=(o.classes,o.wanted),i=o.url,s=o.disabled;return wp.element.createElement(m,{key:"GumControls"},wp.element.createElement(g,null,wp.element.createElement("label",null,"Product URL:"),wp.element.createElement(d,{tagName:"div",placeholder:c("https://gumroad.com/l/demo"),value:i,onChange:function(t){r({url:t}),r({id:e.extract_id(t)})}}),wp.element.createElement("br",null),wp.element.createElement(b,{label:c("Behavior"),description:c("Set the type of Gumroad link behavior: Link, Overlay, or Embed."),options:t,value:a,onChange:function(e){r({type:e}),r("embed"==e?{disabled:"disabled-gumbox"}:{disabled:"enabled"})}}),wp.element.createElement(h,{label:"Display as Gumroad button",checked:l,className:s,onChange:function(e){return r({button:e})}}),wp.element.createElement(h,{label:"Auto-trigger the payment form",checked:u,className:s,onChange:function(e){return r({wanted:e})}})))}}]),t}(i),v=function(e){function t(){return r(this,t),o(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return a(t,e),u(t,[{key:"render",value:function(){var e=this.props,t=e.attributes,n=(t.id,t.text),r=(t.type,t.url,t.button,t.classes,t.wanted,t.disabled,e.setAttributes);return[wp.element.createElement(w,Object.assign({setAttributes:r},this.props)),wp.element.createElement("div",{id:"gumroad-block",className:"gumroad-block"},wp.element.createElement(d,{tagName:"div",placeholder:c("I want this!"),keepPlaceholderOnFocus:!0,className:"gumroad-block-link",value:n,onChange:function(e){return r({text:e})}}))]}}]),t}(i);s("gumroad/gumroad-block",{title:c("Gumroad Block"),icon:"shield",category:"common",keywords:[c("gumroad-block")],attributes:{id:{type:"string",default:""},type:{type:"string",default:"none"},classes:{type:"string",default:""},text:{type:"string",default:"I want this!"},wanted:{type:"string",default:""},button:{type:"string",default:"false"},url:{type:"string",default:""}},edit:v,save:function(e){var t=e.attributes,n=(t.id,t.type),r=t.classes,o=t.text,a=t.wanted,l=t.button,u=t.url,c=u,i="",s=r;return c=c.replace("gum.co/a/","gumroad.com/a/").replace("gum.co/l/","gumroad.com/l/").replace("gum.co/","gumroad.com/l/"),"true"!=a&&1!=a||(i="?wanted=true"),c+=i,"true"!=l&&1!=l||(s=r+" gumroad-button"),"embed"==n?wp.element.createElement("div",{class:"gumroad-product-embed"},wp.element.createElement("a",{href:c})):"overlay"==n?wp.element.createElement("a",{href:c,className:s},o&&!!o.length&&wp.element.createElement(d.Content,{value:o})):wp.element.createElement("a",{href:"#",className:s,onClick:"window.open('"+c+"', '_blank')"},o&&!!o.length&&wp.element.createElement(d.Content,{value:o}))}})},function(e,t){}]);