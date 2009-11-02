
var RightContext={
TYPE_MENU:0,
TYPE_TEXT:1,
TYPE_TEXT_EXT:2,
TYPE_SEPERATOR:3,
TYPE_ATTRIBUTES:4,
menuTriggerEvent:"RIGHT",
mousePos:{x:0,y:0},
rightOffset:-15,
allowedContexts:["a","div","span","input"],
menuCollection:new Object(),
contextMenu:null,
isShowing:false,
abortKill:false,
images:new Object(),
req:null,
initialize:function(){
this.attachContextEvents();
},
addMenu:function(n,m){
this.menuCollection[n]=m;
},
getMenu:function(n){
return this.menuCollection[n];
},
attachContextEvents:function(){
var tagContext,thisTag;
for(var t=0;t<this.allowedContexts.length;t++){
tags=document.getElementsByTagName(this.allowedContexts[t]);
for(e=0;e<tags.length;e++){
thisTag=tags[e];
tagContext=thisTag.getAttribute("id");
if(tagContext!=null&&tagContext!="undefined"){
this.bindEvent('mousemove',tags[e],function(e){return RightContext.locateMousePos(e);});
if(this.menuTriggerEvent=="RIGHT"){
tags[e].oncontextmenu=function(){return RightContext.render(this);};
}else if(this.menuTriggerEvent=="LEFT"){
tags[e].onclick=function(e){
RightContext.killBubble(e);
return RightContext.render(this)
};
}else if(this.menuTriggerEvent=="MOVE"){
if(!document.all){
this.bindEvent('mouseover',tags[e],function(e){RightContext.locateMousePos(e);return RightContext.render(this);});
this.bindEvent('mouseout',tags[e],function(e){setTimeout("RightContext.killMenu()",10);});
}else{
tags[e].onmouseover=function(e){RightContext.locateMousePos(e);return RightContext.render(this);};
tags[e].onmouseout=function(e){setTimeout("RightContext.killMenu()",10);};
}
}
}
}
}
},
killBubble:function(e){
if(!e)var e=window.event;
e.cancelBubble=true;
if(e.stopPropagation)e.stopPropagation();
},
bindEvent:function(evt,obj,act,bubble){
if(!bubble)bubble=false;
if(obj.addEventListener){
obj.addEventListener(evt,act,bubble);
}else if(obj.attachEvent){
obj.attachEvent('on'+evt,act);
}
},
render:function(caller,name){

var url,title;
var name=name||caller.getAttribute("id");
var thisMenu=this.getMenu(name);

try{

var attributes=thisMenu["attributes"].split(',');
var items=thisMenu.items;

} catch (e) {
	return;
}

var objMap=this.buildAttributeMap(attributes,caller);
this.killMenu();
this.buildMenu(caller);
tbl=document.createElement("TABLE");
tbl.id="rcRightContextTable";
for(var m=0;m<items.length;m++){
switch(items[m]["type"]){
case this.TYPE_MENU:
if(this.isDisplayed(items[m],objMap)){
this.addMenuItem(items[m],objMap,tbl);
}
break;
case this.TYPE_TEXT:
text=this.transform(items[m]["text"],objMap);
cell=this.addTableCell(tbl,"rcMenuItemText",text);
break;
case this.TYPE_TEXT_EXT:
cell=this.addTableCell(tbl,"rcMenuItemTextExt");
url=this.transform(items[m]["url"],objMap);
// this.request(url,function(){if(RightContext.req.readyState==4&&RightContext.req.status==200){cell.innerHTML=RightContext.req.responseText}});
break;
case this.TYPE_SEPERATOR:
cell=this.addTableCell(tbl);
cell.appendChild(this.getSeparator());
break;
default:
break;
}
}
this.contextMenu.appendChild(tbl);
this.repositionMenu();
if(this.menuTriggerEvent=="MOVE"){
this.bindEvent('mouseout',this.contextMenu,function(e){RightContext.abortKill=false;setTimeout("RightContext.killMenu()",10);});
this.bindEvent('mouseover',this.contextMenu,function(e){RightContext.abortKill=true;});
}else if(this.menuTriggerEvent=="LEFT"||this.menuTriggerEvent=="RIGHT"){
this.bindEvent('click',document.body,function(e){setTimeout("RightContext.killMenu();",10);},false);
}
this.isShowing=true;
return false;
},
isDisplayed:function(item,objMap){
var reqVar,reqVal;
var shown=true;
if(item["requires"]!=null&&item["requires"]!="undefined"){
reqVar=item["requires"][0];
reqVal=item["requires"][1];
if(objMap[reqVar]!=null&&objMap[reqVar]!="undefined"){
if(objMap[reqVar]!=reqVal){
shown=false;
}
}else{
shown=false;
}
}
return shown;
},
repositionMenu:function(){
var mPos=this.findPosition(this.contextMenu);
var mDim=this.getDimensions(this.contextMenu);
var winHeight=window.innerHeight||document.body.clientHeight;
var winWidth=window.innerWidth||document.body.clientWidth;
if(mPos.y+mDim.height>winHeight-30){
this.position(this.contextMenu,mPos.x,mPos.y-mDim.height);
mPos=this.findPosition(this.contextMenu);
}
if(mPos.x+mDim.width>winWidth-30){
this.position(this.contextMenu,mPos.x-mDim.width,mPos.y);
}
},
getSeparator:function(){
var sep=document.createElement("HR");
sep.className="rcMenuSeparator";
return sep;
},
addTableCell:function(table,className,content){
row=table.insertRow(-1);
cell=row.insertCell(0);
if(className){
cell.className=className;
if(content){
cell.innerHTML=content;
}
}
return cell;
},
addMenuItem:function(item,objMap,tbl){
var title=this.transform(item["text"],objMap);
var url,frame,img,imgAlign,itemSrc,tmp,itemAction;
var cell=this.addTableCell(tbl,"rcMenuItem",title);
cell.style.cursor=document.all?'hand':'pointer';
this.bindEvent('mouseover',cell,function(e){this.className="rcMenuItemHover";});
this.bindEvent('mouseout',cell,function(e){this.className="rcMenuItem";});
if(item["image"]!=null&&item["image"]!="undefined"){
imgAlign=(item["align"]!=null&&item["align"]!="undefined")?item["align"]:"absmiddle";
if(this.images[item["image"]]!=null&&this.images[item["image"]]!="undefined"){
img=this.images[item["image"]];
}else{
img=this.loadImage(item["image"]);
}
img.align=imgAlign;
cell.insertBefore(this.images[item["image"]],cell.childNodes[0]);
}
if(item["url"]!=null&&item["url"]!="undefined"){
url=this.transform(item["url"],objMap);
frame=false;
if(item["frame"]!=null&&item["frame"]!="undefined"){
frame=item["frame"];
}
cell.onclick=function(){RightContext.redirect(url,frame);}
}else{
itemAction=item["onclick"];
try{
itemSrc=item["onclick"].toString();
if(itemSrc.indexOf('[')>-1){
itemSrc=this.transform(itemSrc,objMap);
if(document.all){
eval('itemAction = '+itemSrc);
}else{
itemAction=eval(itemSrc);
}
}
}catch(e){
}
cell.onclick=itemAction;
}
},
transform:function(str,map){
var tStr,tmp;
tStr=str;
for(p in map){
tmp="["+p+"]";
tStr=tStr.replace(tmp,map[p]);
}
return tStr;
},
getMenuAttributeArray:function(menu){
for(var i=0;i<menu.length;i++){
if(menu[i].type==this.TYPE_ATTRIBUTES){
return menu[i]["attributes"].split(',');
}
}
return new Array(0);
},
buildAttributeMap:function(attribs,obj){
var thisAttr,thisValue;
var attrMap=new Object();
for(var a=0;a<attribs.length;a++){
thisAttr=attribs[a];
thisValue=obj.getAttribute(attribs[a]);
if(typeof thisValue!="undefined"){
attrMap[thisAttr]=thisValue;
}
}
return attrMap;
},
findPosition:function(obj){
var lft=0;
var top=0;
if(obj.offsetParent){
lft=obj.offsetLeft;
top=obj.offsetTop;
while(obj=obj.offsetParent){
lft+=obj.offsetLeft;
top+=obj.offsetTop;
}
}
return{x:lft,y:top};
},
getDimensions:function(obj){
var objStyle=obj.style;
var originalVisibility=objStyle.visibility;
var originalPosition=objStyle.position;
var originalDisplay=objStyle.display;
objStyle.visibility='hidden';
objStyle.position='absolute';
objStyle.display='block';
var originalWidth=obj.clientWidth;
var originalHeight=obj.clientHeight;
objStyle.display=originalDisplay;
objStyle.position=originalPosition;
objStyle.visibility=originalVisibility;
return{width:originalWidth,height:originalHeight};
},
position:function(obj,x,y){
obj.style.left=x+'px';
obj.style.top=y+'px';
},
buildMenu:function(parent){
var pos,dim,tbl;
this.contextMenu=document.createElement("DIV");
this.contextMenu.id="rcRightContext";
this.contextMenu.className='rcMenuContainer';
pos=this.findPosition(parent);
dim=this.getDimensions(parent);
// Position context menu depending on browser
// All browsers
this.position(this.contextMenu,this.mousePos.x+this.rightOffset,pos.y+dim.height);
// Opera
if( navigator.userAgent.indexOf("opera") ) {
this.position(this.contextMenu,this.mousePos.x+this.rightOffset,pos.y-dim.height);
}
if(this.menuTriggerEvent=="RIGHT"){
this.contextMenu.oncontextmenu=function(){return false;};
}
document.body.appendChild(this.contextMenu);
},
killMenu:function(){
if(!this.abortKill&&this.isShowing){
try{
rc=this.contextMenu;
document.body.removeChild(rc);
}catch(e){
}
this.contextMenu=null;
this.isShowing=false;
this.abortKill=false;
}
},
locateMousePos:function(e){
var posx=0,posy=0;
if(e==null)e=window.event;
if(e.pageX||e.pageY){
posx=e.pageX;posy=e.pageY;
}else if(e.clientX||e.clientY){
if(document.documentElement.scrollTop){
posx=e.clientX+document.documentElement.scrollLeft;
posy=e.clientY+document.documentElement.scrollTop;
}else{
posx=e.clientX+document.body.scrollLeft;
posy=e.clientY+document.body.scrollTop;
}
}
this.mousePos={x:posx,y:posy};
},
redirect:function(u,frame){
if(!frame){
document.location=u;
}else{
if(frame=="_blank"){
w=window.open(u,'w');
}else{
window.frames[frame].document.location=u;
}
}
},
request:function(url,callBack){
if(window.XMLHttpRequest){
this.req=new XMLHttpRequest();
this.req.onreadystatechange=callBack;
this.req.open("GET",url,true);
this.req.send(null);
}else if(window.ActiveXObject){
this.req=new ActiveXObject("Microsoft.XMLHTTP");
if(this.req){
this.req.onreadystatechange=callBack;
this.req.open("GET",url,true);
this.req.send();
}
}
},
loadImage:function(url){
var img=new Image();
img.src=url;
img.className="rcImage";
this.images[url]=img;
return img;
}
};
