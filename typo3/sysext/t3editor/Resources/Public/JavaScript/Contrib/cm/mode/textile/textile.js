!function(a){"object"==typeof exports&&"object"==typeof module?a(require("../../lib/codemirror")):"function"==typeof define&&define.amd?define(["../../lib/codemirror"],a):a(CodeMirror)}(function(a){"use strict";function b(a,b){b.mode=m.newLayout,b.tableHeading=!1,"definitionList"===b.layoutType&&b.spanningLayout&&a.match(j("definitionListEnd"),!1)&&(b.spanningLayout=!1)}function c(a,b,c){if("_"===c)return a.eat("_")?d(a,b,"italic",/__/,2):d(a,b,"em",/_/,1);if("*"===c)return a.eat("*")?d(a,b,"bold",/\*\*/,2):d(a,b,"strong",/\*/,1);if("["===c)return a.match(/\d+\]/)&&(b.footCite=!0),e(b);if("("===c){var f=a.match(/^(r|tm|c)\)/);if(f)return g(b,k.specialChar)}if("<"===c&&a.match(/(\w+)[^>]+>[^<]+<\/\1>/))return g(b,k.html);if("?"===c&&a.eat("?"))return d(a,b,"cite",/\?\?/,2);if("="===c&&a.eat("="))return d(a,b,"notextile",/==/,2);if("-"===c&&!a.eat("-"))return d(a,b,"deletion",/-/,1);if("+"===c)return d(a,b,"addition",/\+/,1);if("~"===c)return d(a,b,"sub",/~/,1);if("^"===c)return d(a,b,"sup",/\^/,1);if("%"===c)return d(a,b,"span",/%/,1);if("@"===c)return d(a,b,"code",/@/,1);if("!"===c){var h=d(a,b,"image",/(?:\([^\)]+\))?!/,1);return a.match(/^:\S+/),h}return e(b)}function d(a,b,c,d,f){var g=a.pos>f?a.string.charAt(a.pos-f-1):null,h=a.peek();if(b[c]){if((!h||/\W/.test(h))&&g&&/\S/.test(g)){var i=e(b);return b[c]=!1,i}}else(!g||/\W/.test(g))&&h&&/\S/.test(h)&&a.match(new RegExp("^.*\\S"+d.source+"(?:\\W|$)"),!1)&&(b[c]=!0,b.mode=m.attributes);return e(b)}function e(a){var b=f(a);if(b)return b;var c=[];return a.layoutType&&c.push(k[a.layoutType]),c=c.concat(h(a,"addition","bold","cite","code","deletion","em","footCite","image","italic","link","span","strong","sub","sup","table","tableHeading")),"header"===a.layoutType&&c.push(k.header+"-"+a.header),c.length?c.join(" "):null}function f(a){var b=a.layoutType;switch(b){case"notextile":case"code":case"pre":return k[b];default:return a.notextile?k.notextile+(b?" "+k[b]:""):null}}function g(a,b){var c=f(a);if(c)return c;var d=e(a);return b?d?d+" "+b:b:d}function h(a){for(var b=[],c=1;c<arguments.length;++c)a[arguments[c]]&&b.push(k[arguments[c]]);return b}function i(a){var b=a.spanningLayout,c=a.layoutType;for(var d in a)a.hasOwnProperty(d)&&delete a[d];a.mode=m.newLayout,b&&(a.layoutType=c,a.spanningLayout=!0)}function j(a){return l.cache[a]||(l.cache[a]=l.createRe(a))}var k={addition:"positive",attributes:"attribute",bold:"strong",cite:"keyword",code:"atom",definitionList:"number",deletion:"negative",div:"punctuation",em:"em",footnote:"variable",footCite:"qualifier",header:"header",html:"comment",image:"string",italic:"em",link:"link",linkDefinition:"link",list1:"variable-2",list2:"variable-3",list3:"keyword",notextile:"string-2",pre:"operator",p:"property",quote:"bracket",span:"quote",specialChar:"tag",strong:"strong",sub:"builtin",sup:"builtin",table:"variable-3",tableHeading:"operator"},l={cache:{},single:{bc:"bc",bq:"bq",definitionList:/- .*?:=+/,definitionListEnd:/.*=:\s*$/,div:"div",drawTable:/\|.*\|/,foot:/fn\d+/,header:/h[1-6]/,html:/\s*<(?:\/)?(\w+)(?:[^>]+)?>(?:[^<]+<\/\1>)?/,link:/[^"]+":\S/,linkDefinition:/\[[^\s\]]+\]\S+/,list:/(?:#+|\*+)/,notextile:"notextile",para:"p",pre:"pre",table:"table",tableCellAttributes:/[\/\\]\d+/,tableHeading:/\|_\./,tableText:/[^"_\*\[\(\?\+~\^%@|-]+/,text:/[^!"_=\*\[\(<\?\+~\^%@-]+/},attributes:{align:/(?:<>|<|>|=)/,selector:/\([^\(][^\)]+\)/,lang:/\[[^\[\]]+\]/,pad:/(?:\(+|\)+){1,2}/,css:/\{[^\}]+\}/},createRe:function(a){switch(a){case"drawTable":return l.makeRe("^",l.single.drawTable,"$");case"html":return l.makeRe("^",l.single.html,"(?:",l.single.html,")*","$");case"linkDefinition":return l.makeRe("^",l.single.linkDefinition,"$");case"listLayout":return l.makeRe("^",l.single.list,j("allAttributes"),"*\\s+");case"tableCellAttributes":return l.makeRe("^",l.choiceRe(l.single.tableCellAttributes,j("allAttributes")),"+\\.");case"type":return l.makeRe("^",j("allTypes"));case"typeLayout":return l.makeRe("^",j("allTypes"),j("allAttributes"),"*\\.\\.?","(\\s+|$)");case"attributes":return l.makeRe("^",j("allAttributes"),"+");case"allTypes":return l.choiceRe(l.single.div,l.single.foot,l.single.header,l.single.bc,l.single.bq,l.single.notextile,l.single.pre,l.single.table,l.single.para);case"allAttributes":return l.choiceRe(l.attributes.selector,l.attributes.css,l.attributes.lang,l.attributes.align,l.attributes.pad);default:return l.makeRe("^",l.single[a])}},makeRe:function(){for(var a="",b=0;b<arguments.length;++b){var c=arguments[b];a+="string"==typeof c?c:c.source}return new RegExp(a)},choiceRe:function(){for(var a=[arguments[0]],b=1;b<arguments.length;++b)a[2*b-1]="|",a[2*b]=arguments[b];return a.unshift("(?:"),a.push(")"),l.makeRe.apply(null,a)}},m={newLayout:function(a,b){if(a.match(j("typeLayout"),!1))return b.spanningLayout=!1,(b.mode=m.blockType)(a,b);var c;return f(b)||(a.match(j("listLayout"),!1)?c=m.list:a.match(j("drawTable"),!1)?c=m.table:a.match(j("linkDefinition"),!1)?c=m.linkDefinition:a.match(j("definitionList"))?c=m.definitionList:a.match(j("html"),!1)&&(c=m.html)),(b.mode=c||m.text)(a,b)},blockType:function(a,b){var c,d;return b.layoutType=null,(c=a.match(j("type")))?(d=c[0],(c=d.match(j("header")))?(b.layoutType="header",b.header=parseInt(c[0][1])):d.match(j("bq"))?b.layoutType="quote":d.match(j("bc"))?b.layoutType="code":d.match(j("foot"))?b.layoutType="footnote":d.match(j("notextile"))?b.layoutType="notextile":d.match(j("pre"))?b.layoutType="pre":d.match(j("div"))?b.layoutType="div":d.match(j("table"))&&(b.layoutType="table"),b.mode=m.attributes,e(b)):(b.mode=m.text)(a,b)},text:function(a,b){if(a.match(j("text")))return e(b);var d=a.next();return'"'===d?(b.mode=m.link)(a,b):c(a,b,d)},attributes:function(a,b){return b.mode=m.layoutLength,a.match(j("attributes"))?g(b,k.attributes):e(b)},layoutLength:function(a,b){return a.eat(".")&&a.eat(".")&&(b.spanningLayout=!0),b.mode=m.text,e(b)},list:function(a,b){var c=a.match(j("list"));b.listDepth=c[0].length;var d=(b.listDepth-1)%3;return d?1===d?b.layoutType="list2":b.layoutType="list3":b.layoutType="list1",b.mode=m.attributes,e(b)},link:function(a,b){return b.mode=m.text,a.match(j("link"))?(a.match(/\S+/),g(b,k.link)):e(b)},linkDefinition:function(a,b){return a.skipToEnd(),g(b,k.linkDefinition)},definitionList:function(a,b){return a.match(j("definitionList")),b.layoutType="definitionList",a.match(/\s*$/)?b.spanningLayout=!0:b.mode=m.attributes,e(b)},html:function(a,b){return a.skipToEnd(),g(b,k.html)},table:function(a,b){return b.layoutType="table",(b.mode=m.tableCell)(a,b)},tableCell:function(a,b){return a.match(j("tableHeading"))?b.tableHeading=!0:a.eat("|"),b.mode=m.tableCellAttributes,e(b)},tableCellAttributes:function(a,b){return b.mode=m.tableText,a.match(j("tableCellAttributes"))?g(b,k.attributes):e(b)},tableText:function(a,b){return a.match(j("tableText"))?e(b):"|"===a.peek()?(b.mode=m.tableCell,e(b)):c(a,b,a.next())}};a.defineMode("textile",function(){return{startState:function(){return{mode:m.newLayout}},token:function(a,c){return a.sol()&&b(a,c),c.mode(a,c)},blankLine:i}}),a.defineMIME("text/x-textile","textile")});