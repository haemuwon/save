if(typeof window.nhn=='undefined'){window.nhn = {};}
if (!nhn.husky){nhn.husky = {};}
(function(){
var _rxMsgHandler = /^\$(LOCAL|BEFORE|ON|AFTER)_/,
_rxMsgAppReady = /^\$(BEFORE|ON|AFTER)_MSG_APP_READY$/,
_aHuskyCores = [],	
_htLoadedFile = {};	
nhn.husky.HuskyCore = jindo.$Class({
name : "HuskyCore",
aCallerStack : null,
bMobile : jindo.$Agent().navigator().mobile || jindo.$Agent().navigator().msafari,
$init : function(htOptions){
this.htOptions = htOptions||{};
_aHuskyCores.push(this);
if( this.htOptions.oDebugger ){
nhn.husky.HuskyCore.getCore = function() {
return _aHuskyCores;
};
this.htOptions.oDebugger.setApp(this);
}
this.messageQueue = [];
this.oMessageMap = {};
this.oDisabledMessage = {};
this.oLazyMessage = {};
this.aPlugins = [];
this.appStatus = nhn.husky.APP_STATUS.NOT_READY;
this.aCallerStack = [];
this._fnWaitForPluginReady = jindo.$Fn(this._waitForPluginReady, this).bind();
this.registerPlugin(this);
},
setDebugger: function(oDebugger) {
this.htOptions.oDebugger = oDebugger;
oDebugger.setApp(this);
},
exec : function(msg, args, oEvent){
if(this.appStatus == nhn.husky.APP_STATUS.NOT_READY){
this.messageQueue[this.messageQueue.length] = {'msg':msg, 'args':args, 'event':oEvent};
return true;
}
this.exec = this._exec;
this.exec(msg, args, oEvent);
},
delayedExec : function(msg, args, nDelay, oEvent){
var fExec = jindo.$Fn(this.exec, this).bind(msg, args, oEvent);
setTimeout(fExec, nDelay);
},
_exec : function(msg, args, oEvent){
return (this._exec = this.htOptions.oDebugger?this._execWithDebugger:this._execWithoutDebugger).call(this, msg, args, oEvent);
},
_execWithDebugger : function(msg, args, oEvent){
this.htOptions.oDebugger.log_MessageStart(msg, args);
var bResult = this._doExec(msg, args, oEvent);
this.htOptions.oDebugger.log_MessageEnd(msg, args);
return bResult;
},
_execWithoutDebugger : function(msg, args, oEvent){
return this._doExec(msg, args, oEvent);
},
_doExec : function(msg, args, oEvent){
var bContinue = false;
if(this.oLazyMessage[msg]){
var htLazyInfo = this.oLazyMessage[msg];
this._loadLazyFiles(msg, args, oEvent, htLazyInfo.aFilenames, 0);
return false;
}
if(!this.oDisabledMessage[msg]){
var allArgs = [];
if(args && args.length){
var iLen = args.length;
for(var i=0; i<iLen; i++){allArgs[i] = args[i];}
}
if(oEvent){allArgs[allArgs.length] = oEvent;}
bContinue = this._execMsgStep("BEFORE", msg, allArgs);
if(bContinue){bContinue = this._execMsgStep("ON", msg, allArgs);}
if(bContinue){bContinue = this._execMsgStep("AFTER", msg, allArgs);}
}
return bContinue;
},
registerPlugin : function(oPlugin){
if(!oPlugin){throw("An error occured in registerPlugin(): invalid plug-in");}
oPlugin.nIdx = this.aPlugins.length;
oPlugin.oApp = this;
this.aPlugins[oPlugin.nIdx] = oPlugin;
if(oPlugin.status != nhn.husky.PLUGIN_STATUS.NOT_READY){oPlugin.status = nhn.husky.PLUGIN_STATUS.READY;}
if(this.appStatus != nhn.husky.APP_STATUS.NOT_READY){
for(var funcName in oPlugin){
if(_rxMsgHandler.test(funcName)){
this.addToMessageMap(funcName, oPlugin);
}
}
}
this.exec("MSG_PLUGIN_REGISTERED", [oPlugin]);
return oPlugin.nIdx;
},
disableMessage : function(sMessage, bDisable){this.oDisabledMessage[sMessage] = bDisable;},
registerBrowserEvent : function(obj, sEvent, sMessage, aParams, nDelay){
aParams = aParams || [];
var func = (nDelay)?jindo.$Fn(this.delayedExec, this).bind(sMessage, aParams, nDelay):jindo.$Fn(this.exec, this).bind(sMessage, aParams);
return jindo.$Fn(func, this).attach(obj, sEvent);
},
run : function(htOptions){
this.htRunOptions = htOptions || {};
this._changeAppStatus(nhn.husky.APP_STATUS.WAITING_FOR_PLUGINS_READY);
var iQueueLength = this.messageQueue.length;
for(var i=0; i<iQueueLength; i++){
var curMsgAndArgs = this.messageQueue[i];
this.exec(curMsgAndArgs.msg, curMsgAndArgs.args, curMsgAndArgs.event);
}
this._fnWaitForPluginReady();
},
acceptLocalBeforeFirstAgain : function(oPlugin, bAccept){
oPlugin._husky_bRun = !bAccept;
},
createMessageMap : function(sMsgHandler){
this.oMessageMap[sMsgHandler] = [];
var nLen = this.aPlugins.length;
for(var i=0; i<nLen; i++){this._doAddToMessageMap(sMsgHandler, this.aPlugins[i]);}
},
addToMessageMap : function(sMsgHandler, oPlugin){
if(!this.oMessageMap[sMsgHandler]){return;}
this._doAddToMessageMap(sMsgHandler, oPlugin);
},
_changeAppStatus : function(appStatus){
this.appStatus = appStatus;
if(this.appStatus == nhn.husky.APP_STATUS.READY){this.exec("MSG_APP_READY");}
},
_execMsgStep : function(sMsgStep, sMsg, args){
return (this._execMsgStep = this.htOptions.oDebugger?this._execMsgStepWithDebugger:this._execMsgStepWithoutDebugger).call(this, sMsgStep, sMsg, args);
},
_execMsgStepWithDebugger : function(sMsgStep, sMsg, args){
this.htOptions.oDebugger.log_MessageStepStart(sMsgStep, sMsg, args);
var bStatus = this._execMsgHandler("$"+sMsgStep+"_"+sMsg, args);
this.htOptions.oDebugger.log_MessageStepEnd(sMsgStep, sMsg, args);
return bStatus;
},
_execMsgStepWithoutDebugger : function(sMsgStep, sMsg, args){
return this._execMsgHandler ("$"+sMsgStep+"_"+sMsg, args);
},
_execMsgHandler : function(sMsgHandler, args){
var i;
if(!this.oMessageMap[sMsgHandler]){
this.createMessageMap(sMsgHandler);
}
var aPlugins = this.oMessageMap[sMsgHandler];
var iNumOfPlugins = aPlugins.length;
if(iNumOfPlugins === 0){return true;}
var bResult = true;
if(_rxMsgAppReady.test(sMsgHandler)){
for(i=0; i<iNumOfPlugins; i++){
if(this._execHandler(aPlugins[i], sMsgHandler, args) === false){
bResult = false;
break;
}
}
}else{
for(i=0; i<iNumOfPlugins; i++){
if(!aPlugins[i]._husky_bRun){
aPlugins[i]._husky_bRun = true;
if(typeof aPlugins[i].$LOCAL_BEFORE_FIRST == "function" && this._execHandler(aPlugins[i], "$LOCAL_BEFORE_FIRST", [sMsgHandler, args]) === false){continue;}
}
if(typeof aPlugins[i].$LOCAL_BEFORE_ALL == "function"){
if(this._execHandler(aPlugins[i], "$LOCAL_BEFORE_ALL", [sMsgHandler, args]) === false){continue;}
}
if(this._execHandler(aPlugins[i], sMsgHandler, args) === false){
bResult = false;
break;
}
}
}
return bResult;
},
_execHandler : function(oPlugin, sHandler, args){
return	(this._execHandler = this.htOptions.oDebugger?this._execHandlerWithDebugger:this._execHandlerWithoutDebugger).call(this, oPlugin, sHandler, args);
},
_execHandlerWithDebugger : function(oPlugin, sHandler, args){
this.htOptions.oDebugger.log_CallHandlerStart(oPlugin, sHandler, args);
var bResult;
try{
this.aCallerStack.push(oPlugin);
bResult = oPlugin[sHandler].apply(oPlugin, args);
this.aCallerStack.pop();
}catch(e){
this.htOptions.oDebugger.handleException(e);
bResult = false;
}
this.htOptions.oDebugger.log_CallHandlerEnd(oPlugin, sHandler, args);
return bResult;
},
_execHandlerWithoutDebugger : function(oPlugin, sHandler, args){
this.aCallerStack.push(oPlugin);
var bResult = oPlugin[sHandler].apply(oPlugin, args);
this.aCallerStack.pop();
return bResult;
},
_doAddToMessageMap : function(sMsgHandler, oPlugin){
if(typeof oPlugin[sMsgHandler] != "function"){return;}
var aMap = this.oMessageMap[sMsgHandler];
for(var i=0, iLen=aMap.length; i<iLen; i++){
if(this.oMessageMap[sMsgHandler][i] == oPlugin){return;}
}
this.oMessageMap[sMsgHandler][i] = oPlugin;
},
_waitForPluginReady : function(){
var bAllReady = true;
for(var i=0; i<this.aPlugins.length; i++){
if(this.aPlugins[i].status == nhn.husky.PLUGIN_STATUS.NOT_READY){
bAllReady = false;
break;
}
}
if(bAllReady){
this._changeAppStatus(nhn.husky.APP_STATUS.READY);
}else{
setTimeout(this._fnWaitForPluginReady, 100);
}
},
_loadLazyFiles : function(sMsg, aArgs, oEvent, aFilenames, nIdx){
var nLen = aFilenames.length;
if(nLen <= nIdx){
this.oLazyMessage[sMsg] = null;
this.oApp.exec(sMsg, aArgs, oEvent);
return;
}
var sFilename = aFilenames[nIdx];
if(_htLoadedFile[sFilename]){
this._loadLazyFiles(sMsg, aArgs, oEvent, aFilenames, nIdx+1);
}else{
jindo.LazyLoading.load(nhn.husky.SE2M_Configuration.LazyLoad.sJsBaseURI+"/"+sFilename,
jindo.$Fn(function(sMsg, aArgs, oEvent, aFilenames, nIdx){
var sFilename = aFilenames[nIdx];
_htLoadedFile[sFilename] = 1;
this._loadLazyFiles(sMsg, aArgs, oEvent, aFilenames, nIdx+1);
}, this).bind(sMsg, aArgs, oEvent, aFilenames, nIdx),
"utf-8"
);
}
},
registerLazyMessage : function(aMsgs, aFilenames){
aMsgs = aMsgs || [];
aFilenames = aFilenames || [];
for(var i = 0, sMsg, htLazyInfo; (sMsg = aMsgs[i]); i++){
htLazyInfo = this.oLazyMessage[sMsg];
if(htLazyInfo){
htLazyInfo.aFilenames = htLazyInfo.aFilenames.concat(aFilenames);
}else{
this.oLazyMessage[sMsg] = {
sMsg : sMsg,
aFilenames : aFilenames
};
}
}
}
});
nhn.husky.HuskyCore._htLoadedFile = {};
nhn.husky.HuskyCore.addLoadedFile = function(sFilename){
_htLoadedFile[sFilename] = 1;
};
nhn.husky.HuskyCore.mixin = function(oClass, htMixin, bOverride, sFilename){
var aPlugins = [];
for(var i = 0, oHuskyCore; (oHuskyCore = _aHuskyCores[i]); i++){
for(var j = 0, oPlugin; (oPlugin = oHuskyCore.aPlugins[j]); j++){
if(oPlugin instanceof oClass){
aPlugins.push(oPlugin);
if(typeof oPlugin["$LOCAL_BEFORE_FIRST"] !== "function"){
oPlugin.oApp.acceptLocalBeforeFirstAgain(oPlugin, true);
}
}else if(oPlugin._$superClass === oClass){
if(typeof oPlugin["$LOCAL_BEFORE_FIRST"] !== "function"){
oPlugin.oApp.acceptLocalBeforeFirstAgain(oPlugin, true);
}
for(var k in htMixin){
if(bOverride || !oPlugin.hasOwnProperty(k)){
oPlugin[k] = htMixin[k];
if(_rxMsgHandler.test(k)){
oPlugin.oApp.addToMessageMap(k, oPlugin);
}}}}}}
for(var k in htMixin){
if(bOverride || !oClass.prototype.hasOwnProperty(k)){
oClass.prototype[k] = htMixin[k];
if(_rxMsgHandler.test(k)){
for(var j = 0, oPlugin; (oPlugin = aPlugins[j]); j++){
oPlugin.oApp.addToMessageMap(k, oPlugin);
}}}}
};
nhn.husky.APP_STATUS = {
'NOT_READY' : 0,
'WAITING_FOR_PLUGINS_READY' : 1,
'READY' : 2
};
nhn.husky.PLUGIN_STATUS = {
'NOT_READY' : 0,
'READY' : 1
};
})();
if(typeof window.nhn=='undefined'){window.nhn = {};}
nhn.CurrentSelection_IE = function(){
this.getCommonAncestorContainer = function(){
try{
this._oSelection = this._document.selection;
if(this._oSelection.type == "Control"){
return this._oSelection.createRange().item(0);
}else{
return this._oSelection.createRangeCollection().item(0).parentElement();
}
}catch(e){
return this._document.body;
}
};
this.isCollapsed = function(){
this._oSelection = this._document.selection;
return this._oSelection.type == "None";
};
};
nhn.CurrentSelection_FF = function(){
this.getCommonAncestorContainer = function(){
return this._getSelection().commonAncestorContainer;
};
this.isCollapsed = function(){
var oSelection = this._window.getSelection();
if(oSelection.rangeCount<1){ return true; }
return oSelection.getRangeAt(0).collapsed;
};
this._getSelection = function(){
try{
return this._window.getSelection().getRangeAt(0);
}catch(e){
return this._document.createRange();
}
};
};
nhn.CurrentSelection = new (jindo.$Class({
$init : function(){
var oAgentInfo = jindo.$Agent().navigator();
if(oAgentInfo.ie && document.selection){
nhn.CurrentSelection_IE.apply(this);
}else{
nhn.CurrentSelection_FF.apply(this);
}
},
setWindow : function(oWin){
this._window = oWin;
this._document = oWin.document;
}
}))();
nhn.W3CDOMRange = jindo.$Class({
$init : function(win){
this.reset(win);
},
reset : function(win){
this._window = win;
this._document = this._window.document;
this.collapsed = true;
this.commonAncestorContainer = this._document.body;
this.endContainer = this._document.body;
this.endOffset = 0;
this.startContainer = this._document.body;
this.startOffset = 0;
this.oBrowserSelection = new nhn.BrowserSelection(this._window);
this.selectionLoaded = this.oBrowserSelection.selectionLoaded;
},
cloneContents : function(){
var oClonedContents = this._document.createDocumentFragment();
var oTmpContainer = this._document.createDocumentFragment();
var aNodes = this._getNodesInRange();
if(aNodes.length < 1){return oClonedContents;}
var oClonedContainers = this._constructClonedTree(aNodes, oTmpContainer);
var oTopContainer = oTmpContainer.firstChild;
if(oTopContainer){
var elCurNode = oTopContainer.firstChild;
var elNextNode;
while(elCurNode){
elNextNode = elCurNode.nextSibling;
oClonedContents.appendChild(elCurNode);
elCurNode = elNextNode;
}
}
oClonedContainers = this._splitTextEndNodes({oStartContainer: oClonedContainers.oStartContainer, iStartOffset: this.startOffset,
					oEndContainer: oClonedContainers.oEndContainer, iEndOffset: this.endOffset});
if(oClonedContainers.oStartContainer && oClonedContainers.oStartContainer.previousSibling){
nhn.DOMFix.parentNode(oClonedContainers.oStartContainer).removeChild(oClonedContainers.oStartContainer.previousSibling);
}
if(oClonedContainers.oEndContainer && oClonedContainers.oEndContainer.nextSibling){
nhn.DOMFix.parentNode(oClonedContainers.oEndContainer).removeChild(oClonedContainers.oEndContainer.nextSibling);
}
return oClonedContents;
},
_constructClonedTree : function(aNodes, oClonedParentNode){
var oClonedStartContainer = null;
var oClonedEndContainer = null;
var oStartContainer = this.startContainer;
var oEndContainer = this.endContainer;
var _recurConstructClonedTree = function(aAllNodes, iCurIdx, oClonedParentNode){
if(iCurIdx < 0){return iCurIdx;}
var iChildIdx = iCurIdx-1;
var oCurNodeCloneWithChildren = aAllNodes[iCurIdx].cloneNode(false);
if(aAllNodes[iCurIdx] == oStartContainer){oClonedStartContainer = oCurNodeCloneWithChildren;}
if(aAllNodes[iCurIdx] == oEndContainer){oClonedEndContainer = oCurNodeCloneWithChildren;}
while(iChildIdx >= 0 && nhn.DOMFix.parentNode(aAllNodes[iChildIdx]) == aAllNodes[iCurIdx]){
iChildIdx = this._recurConstructClonedTree(aAllNodes, iChildIdx, oCurNodeCloneWithChildren);
}
oClonedParentNode.insertBefore(oCurNodeCloneWithChildren, oClonedParentNode.firstChild);
return iChildIdx;
};
this._recurConstructClonedTree = _recurConstructClonedTree;
aNodes[aNodes.length] = nhn.DOMFix.parentNode(aNodes[aNodes.length-1]);
this._recurConstructClonedTree(aNodes, aNodes.length-1, oClonedParentNode);
return {oStartContainer: oClonedStartContainer, oEndContainer: oClonedEndContainer};
},
cloneRange : function(){
return this._copyRange(new nhn.W3CDOMRange(this._window));
},
_copyRange : function(oClonedRange){
oClonedRange.collapsed = this.collapsed;
oClonedRange.commonAncestorContainer = this.commonAncestorContainer;
oClonedRange.endContainer = this.endContainer;
oClonedRange.endOffset = this.endOffset;
oClonedRange.startContainer = this.startContainer;
oClonedRange.startOffset = this.startOffset;
oClonedRange._document = this._document;
return oClonedRange;
},
collapse : function(toStart){
if(toStart){
this.endContainer = this.startContainer;
this.endOffset = this.startOffset;
}else{
this.startContainer = this.endContainer;
this.startOffset = this.endOffset;
}
this._updateRangeInfo();
},
compareBoundaryPoints : function(how, sourceRange){
switch(how){
case nhn.W3CDOMRange.START_TO_START:
return this._compareEndPoint(this.startContainer, this.startOffset, sourceRange.startContainer, sourceRange.startOffset);
case nhn.W3CDOMRange.START_TO_END:
return this._compareEndPoint(this.endContainer, this.endOffset, sourceRange.startContainer, sourceRange.startOffset);
case nhn.W3CDOMRange.END_TO_END:
return this._compareEndPoint(this.endContainer, this.endOffset, sourceRange.endContainer, sourceRange.endOffset);
case nhn.W3CDOMRange.END_TO_START:
return this._compareEndPoint(this.startContainer, this.startOffset, sourceRange.endContainer, sourceRange.endOffset);
}
},
_findBody : function(oNode){
if(!oNode){return null;}
while(oNode){
if(oNode.tagName == "BODY"){return oNode;}
oNode = nhn.DOMFix.parentNode(oNode);
}
return null;
},
_compareEndPoint : function(oContainerA, iOffsetA, oContainerB, iOffsetB){
return this.oBrowserSelection.compareEndPoints(oContainerA, iOffsetA, oContainerB, iOffsetB);
var iIdxA, iIdxB;
if(!oContainerA || this._findBody(oContainerA) != this._document.body){
oContainerA = this._document.body;
iOffsetA = 0;
}
if(!oContainerB || this._findBody(oContainerB) != this._document.body){
oContainerB = this._document.body;
iOffsetB = 0;
}
var compareIdx = function(iIdxA, iIdxB){
if(iIdxB == -1){iIdxB = iIdxA+1;}
if(iIdxA < iIdxB){return -1;}
if(iIdxA == iIdxB){return 0;}
return 1;
};
var oCommonAncestor = this._getCommonAncestorContainer(oContainerA, oContainerB);
var oNodeA = oContainerA;
var oTmpNode = null;
if(oNodeA != oCommonAncestor){
while((oTmpNode = nhn.DOMFix.parentNode(oNodeA)) != oCommonAncestor){oNodeA = oTmpNode;}
iIdxA = this._getPosIdx(oNodeA)+0.5;
}else{
iIdxA = iOffsetA;
}
var oNodeB = oContainerB;
if(oNodeB != oCommonAncestor){
while((oTmpNode = nhn.DOMFix.parentNode(oNodeB)) != oCommonAncestor){oNodeB = oTmpNode;}
iIdxB = this._getPosIdx(oNodeB)+0.5;
}else{
iIdxB = iOffsetB;
}
return compareIdx(iIdxA, iIdxB);
},
_getCommonAncestorContainer : function(oNode1, oNode2){
oNode1 = oNode1 || this.startContainer;
oNode2 = oNode2 || this.endContainer;
var oComparingNode = oNode2;
while(oNode1){
while(oComparingNode){
if(oNode1 == oComparingNode){return oNode1;}
oComparingNode = nhn.DOMFix.parentNode(oComparingNode);
}
oComparingNode = oNode2;
oNode1 = nhn.DOMFix.parentNode(oNode1);
}
return this._document.body;
},
deleteContents : function(){
if(this.collapsed){return;}
this._splitTextEndNodesOfTheRange();
var aNodes = this._getNodesInRange();
if(aNodes.length < 1){return;}
var oPrevNode = aNodes[0].previousSibling;
while(oPrevNode && this._isBlankTextNode(oPrevNode)){oPrevNode = oPrevNode.previousSibling;}
var oNewStartContainer, iNewOffset = -1;
if(!oPrevNode){
oNewStartContainer = nhn.DOMFix.parentNode(aNodes[0]);
iNewOffset = 0;
}
for(var i=0; i<aNodes.length; i++){
var oNode = aNodes[i];
if(!oNode.firstChild || this._isAllChildBlankText(oNode)){
if(oNewStartContainer == oNode){
iNewOffset = this._getPosIdx(oNewStartContainer);
oNewStartContainer = nhn.DOMFix.parentNode(oNode);
}
nhn.DOMFix.parentNode(oNode).removeChild(oNode);
}else{
if(oNewStartContainer == oNode && iNewOffset === 0){
iNewOffset = this._getPosIdx(oNewStartContainer);
oNewStartContainer = nhn.DOMFix.parentNode(oNode);
}
}
}
if(!oPrevNode){
this.setStart(oNewStartContainer, iNewOffset, true, true);
}else{
if(oPrevNode.tagName == "BODY"){
this.setStartBefore(oPrevNode, true);
}else{
this.setStartAfter(oPrevNode, true);
}
}
this.collapse(true);
},
extractContents : function(){
var oClonedContents = this.cloneContents();
this.deleteContents();
return oClonedContents;
},
getInsertBeforeNodes : function(){
var oFirstNode = null;
var oParentContainer;
if(this.startContainer.nodeType == "3"){
oParentContainer = nhn.DOMFix.parentNode(this.startContainer);
if(this.startContainer.nodeValue.length <= this.startOffset){
oFirstNode = this.startContainer.nextSibling;
}else{
oFirstNode = this.startContainer.splitText(this.startOffset);
}
}else{
oParentContainer = this.startContainer;
oFirstNode = nhn.DOMFix.childNodes(this.startContainer)[this.startOffset];
}
if(!oFirstNode || !nhn.DOMFix.parentNode(oFirstNode)){oFirstNode = null;}
return {elParent: oParentContainer, elBefore: oFirstNode};
},
insertNode : function(newNode){
var oInsertBefore = this.getInsertBeforeNodes();
oInsertBefore.elParent.insertBefore(newNode, oInsertBefore.elBefore);
this.setStartBefore(newNode);
},
selectNode : function(refNode){
this.reset(this._window);
this.setStartBefore(refNode);
this.setEndAfter(refNode);
},
selectNodeContents : function(refNode){
this.reset(this._window);
this.setStart(refNode, 0, true);
this.setEnd(refNode, nhn.DOMFix.childNodes(refNode).length);
},
_endsNodeValidation : function(oNode, iOffset){
if(!oNode || this._findBody(oNode) != this._document.body){throw new Error("INVALID_NODE_TYPE_ERR oNode is not part of current document");}
if(oNode.nodeType == 3){
if(iOffset > oNode.nodeValue.length){iOffset = oNode.nodeValue.length;}
}else{
if(iOffset > nhn.DOMFix.childNodes(oNode).length){iOffset = nhn.DOMFix.childNodes(oNode).length;}
}
return iOffset;
},
setEnd : function(refNode, offset, bSafe, bNoUpdate){
if(!bSafe){offset = this._endsNodeValidation(refNode, offset);}
this.endContainer = refNode;
this.endOffset = offset;
if(!bNoUpdate){
if(!this.startContainer || this._compareEndPoint(this.startContainer, this.startOffset, this.endContainer, this.endOffset) != -1){
this.collapse(false);
}else{
this._updateRangeInfo();
}
}
},
setEndAfter : function(refNode, bNoUpdate){
if(!refNode){throw new Error("INVALID_NODE_TYPE_ERR in setEndAfter");}
if(refNode.tagName == "BODY"){
this.setEnd(refNode, nhn.DOMFix.childNodes(refNode).length, true, bNoUpdate);
return;
}
this.setEnd(nhn.DOMFix.parentNode(refNode), this._getPosIdx(refNode)+1, true, bNoUpdate);
},
setEndBefore : function(refNode, bNoUpdate){
if(!refNode){throw new Error("INVALID_NODE_TYPE_ERR in setEndBefore");}
if(refNode.tagName == "BODY"){
this.setEnd(refNode, 0, true, bNoUpdate);
return;
}
this.setEnd(nhn.DOMFix.parentNode(refNode), this._getPosIdx(refNode), true, bNoUpdate);
},
setStart : function(refNode, offset, bSafe, bNoUpdate){
if(!bSafe){offset = this._endsNodeValidation(refNode, offset);}
this.startContainer = refNode;
this.startOffset = offset;
if(!bNoUpdate){
if(!this.endContainer || this._compareEndPoint(this.startContainer, this.startOffset, this.endContainer, this.endOffset) != -1){
this.collapse(true);
}else{
this._updateRangeInfo();
}
}
},
setStartAfter : function(refNode, bNoUpdate){
if(!refNode){throw new Error("INVALID_NODE_TYPE_ERR in setStartAfter");}
if(refNode.tagName == "BODY"){
this.setStart(refNode, nhn.DOMFix.childNodes(refNode).length, true, bNoUpdate);
return;
}
this.setStart(nhn.DOMFix.parentNode(refNode), this._getPosIdx(refNode)+1, true, bNoUpdate);
},
setStartBefore : function(refNode, bNoUpdate){
if(!refNode){throw new Error("INVALID_NODE_TYPE_ERR in setStartBefore");}
if(refNode.tagName == "BODY"){
this.setStart(refNode, 0, true, bNoUpdate);
return;
}
this.setStart(nhn.DOMFix.parentNode(refNode), this._getPosIdx(refNode), true, bNoUpdate);
},
surroundContents : function(newParent){
newParent.appendChild(this.extractContents());
this.insertNode(newParent);
this.selectNode(newParent);
},
toString : function(){
var oTmpContainer = this._document.createElement("DIV");
oTmpContainer.appendChild(this.cloneContents());
return oTmpContainer.textContent || oTmpContainer.innerText || "";
},
fixCommonAncestorContainer : function(){
if(!jindo.$Agent().navigator().ie){
return;
}
this.commonAncestorContainer = this._getCommonAncestorContainer();
},
_isBlankTextNode : function(oNode){
if(oNode.nodeType == 3 && oNode.nodeValue == ""){return true;}
return false;
},
_isAllChildBlankText : function(elNode){
for(var i=0, nLen=elNode.childNodes.length; i<nLen; i++){
if(!this._isBlankTextNode(elNode.childNodes[i])){return false;}
}
return true;
},
_getPosIdx : function(refNode){
var idx = 0;
for(var node = refNode.previousSibling; node; node = node.previousSibling){idx++;}
return idx;
},
_updateRangeInfo : function(){
if(!this.startContainer){
this.reset(this._window);
return;
}
this.collapsed = this.oBrowserSelection.isCollapsed(this) || (this.startContainer === this.endContainer && this.startOffset === this.endOffset);
this.commonAncestorContainer = this.oBrowserSelection.getCommonAncestorContainer(this);
},
_isCollapsed : function(oStartContainer, iStartOffset, oEndContainer, iEndOffset){
var bCollapsed = false;
if(oStartContainer == oEndContainer && iStartOffset == iEndOffset){
bCollapsed = true;
}else{
var oActualStartNode = this._getActualStartNode(oStartContainer, iStartOffset);
var oActualEndNode = this._getActualEndNode(oEndContainer, iEndOffset);
oActualStartNode = this._getNextNode(this._getPrevNode(oActualStartNode));
oActualEndNode = this._getPrevNode(this._getNextNode(oActualEndNode));
if(oActualStartNode && oActualEndNode && oActualEndNode.tagName != "BODY" &&
(this._getNextNode(oActualEndNode) == oActualStartNode || (oActualEndNode == oActualStartNode && this._isBlankTextNode(oActualEndNode)))
){
bCollapsed = true;
}
}
return bCollapsed;
},
_splitTextEndNodesOfTheRange : function(){
var oEndPoints = this._splitTextEndNodes({oStartContainer: this.startContainer, iStartOffset: this.startOffset,
					oEndContainer: this.endContainer, iEndOffset: this.endOffset});
this.startContainer = oEndPoints.oStartContainer;
this.startOffset = oEndPoints.iStartOffset;
this.endContainer = oEndPoints.oEndContainer;
this.endOffset = oEndPoints.iEndOffset;
},
_splitTextEndNodes : function(oEndPoints){
oEndPoints = this._splitStartTextNode(oEndPoints);
oEndPoints = this._splitEndTextNode(oEndPoints);
return oEndPoints;
},
_splitStartTextNode : function(oEndPoints){
var oStartContainer = oEndPoints.oStartContainer;
var iStartOffset = oEndPoints.iStartOffset;
var oEndContainer = oEndPoints.oEndContainer;
var iEndOffset = oEndPoints.iEndOffset;
if(!oStartContainer){return oEndPoints;}
if(oStartContainer.nodeType != 3){return oEndPoints;}
if(iStartOffset === 0){return oEndPoints;}
if(oStartContainer.nodeValue.length <= iStartOffset){return oEndPoints;}
var oLastPart = oStartContainer.splitText(iStartOffset);
if(oStartContainer == oEndContainer){
iEndOffset -= iStartOffset;
oEndContainer = oLastPart;
}
oStartContainer = oLastPart;
iStartOffset = 0;
return {oStartContainer: oStartContainer, iStartOffset: iStartOffset, oEndContainer: oEndContainer, iEndOffset: iEndOffset};
},
_splitEndTextNode : function(oEndPoints){
var oStartContainer = oEndPoints.oStartContainer;
var iStartOffset = oEndPoints.iStartOffset;
var oEndContainer = oEndPoints.oEndContainer;
var iEndOffset = oEndPoints.iEndOffset;
if(!oEndContainer){return oEndPoints;}
if(oEndContainer.nodeType != 3){return oEndPoints;}
if(iEndOffset >= oEndContainer.nodeValue.length){return oEndPoints;}
if(iEndOffset === 0){return oEndPoints;}
oEndContainer.splitText(iEndOffset);
return {oStartContainer: oStartContainer, iStartOffset: iStartOffset, oEndContainer: oEndContainer, iEndOffset: iEndOffset};
},
_getNodesInRange : function(){
if(this.collapsed){return [];}
var oStartNode = this._getActualStartNode(this.startContainer, this.startOffset);
var oEndNode = this._getActualEndNode(this.endContainer, this.endOffset);
return this._getNodesBetween(oStartNode, oEndNode);
},
_getActualStartNode : function(oStartContainer, iStartOffset){
var oStartNode = oStartContainer;
if(oStartContainer.nodeType == 3){
if(iStartOffset >= oStartContainer.nodeValue.length){
oStartNode = this._getNextNode(oStartContainer);
if(oStartNode.tagName == "BODY"){oStartNode = null;}
}else{
oStartNode = oStartContainer;
}
}else{
if(iStartOffset < nhn.DOMFix.childNodes(oStartContainer).length){
oStartNode = nhn.DOMFix.childNodes(oStartContainer)[iStartOffset];
}else{
oStartNode = this._getNextNode(oStartContainer);
if(oStartNode.tagName == "BODY"){oStartNode = null;}
}
}
return oStartNode;
},
_getActualEndNode : function(oEndContainer, iEndOffset){
var oEndNode = oEndContainer;
if(iEndOffset === 0){
oEndNode = this._getPrevNode(oEndContainer);
if(oEndNode.tagName == "BODY"){oEndNode = null;}
}else if(oEndContainer.nodeType == 3){
oEndNode = oEndContainer;
}else{
oEndNode = nhn.DOMFix.childNodes(oEndContainer)[iEndOffset-1];
}
return oEndNode;
},
_getNextNode : function(oNode){
if(!oNode || oNode.tagName == "BODY"){return this._document.body;}
if(oNode.nextSibling){return oNode.nextSibling;}
return this._getNextNode(nhn.DOMFix.parentNode(oNode));
},
_getPrevNode : function(oNode){
if(!oNode || oNode.tagName == "BODY"){return this._document.body;}
if(oNode.previousSibling){return oNode.previousSibling;}
return this._getPrevNode(nhn.DOMFix.parentNode(oNode));
},
_getNodesBetween : function(oStartNode, oEndNode){
var aNodesBetween = [];
this._nNodesBetweenLen = 0;
if(!oStartNode || !oEndNode){return aNodesBetween;}
try{
this._recurGetNextNodesUntil(oStartNode, oEndNode, aNodesBetween);
}catch(e){
return [];
}
return aNodesBetween;
},
_recurGetNextNodesUntil : function(oNode, oEndNode, aNodesBetween){
if(!oNode){return false;}
if(!this._recurGetChildNodesUntil(oNode, oEndNode, aNodesBetween)){return false;}
var oNextToChk = oNode.nextSibling;
while(!oNextToChk){
if(!(oNode = nhn.DOMFix.parentNode(oNode))){return false;}
aNodesBetween[this._nNodesBetweenLen++] = oNode;
if(oNode == oEndNode){return false;}
oNextToChk = oNode.nextSibling;
}
return this._recurGetNextNodesUntil(oNextToChk, oEndNode, aNodesBetween);
},
_recurGetChildNodesUntil : function(oNode, oEndNode, aNodesBetween){
if(!oNode){return false;}
var bEndFound = false;
var oCurNode = oNode;
if(oCurNode.firstChild){
oCurNode = oCurNode.firstChild;
while(oCurNode){
if(!this._recurGetChildNodesUntil(oCurNode, oEndNode, aNodesBetween)){
bEndFound = true;
break;
}
oCurNode = oCurNode.nextSibling;
}
}
aNodesBetween[this._nNodesBetweenLen++] = oNode;
if(bEndFound){return false;}
if(oNode == oEndNode){return false;}
return true;
}
});
nhn.W3CDOMRange.START_TO_START = 0;
nhn.W3CDOMRange.START_TO_END = 1;
nhn.W3CDOMRange.END_TO_END = 2;
nhn.W3CDOMRange.END_TO_START = 3;
nhn.HuskyRange = jindo.$Class({
_rxCursorHolder : /^(?:\uFEFF|\u00A0|\u200B|<br>)$/i,
_rxTextAlign : /text-align:[^"';]*;?/i,
setWindow : function(win){
this.reset(win || window);
},
$init : function(win){
this.HUSKY_BOOMARK_START_ID_PREFIX = "husky_bookmark_start_";
this.HUSKY_BOOMARK_END_ID_PREFIX = "husky_bookmark_end_";
this.sBlockElement = "P|DIV|LI|H[1-6]|PRE";
this.sBlockContainer = "BODY|TABLE|TH|TR|TD|UL|OL|BLOCKQUOTE|FORM";
this.rxBlockElement = new RegExp("^("+this.sBlockElement+")$");
this.rxBlockContainer = new RegExp("^("+this.sBlockContainer+")$");
this.rxLineBreaker = new RegExp("^("+this.sBlockElement+"|"+this.sBlockContainer+")$");
this.rxHasBlock = new RegExp("(?:<(?:"+this.sBlockElement+"|"+this.sBlockContainer+").*?>|style=[\"']?[^>]*?(?:display\s?:\s?block)[^>]*?[\"']?)", "gi");
this.setWindow(win);
},
select : function(){
try{
this.oBrowserSelection.selectRange(this);
}catch(e){}
},
setFromSelection : function(iNum){
this.setRange(this.oBrowserSelection.getRangeAt(iNum), true);
},
setRange : function(oW3CRange, bSafe){
this.reset(this._window);
this.setStart(oW3CRange.startContainer, oW3CRange.startOffset, bSafe, true);
this.setEnd(oW3CRange.endContainer, oW3CRange.endOffset, bSafe);
},
setEndNodes : function(oSNode, oENode){
this.reset(this._window);
this.setEndAfter(oENode, true);
this.setStartBefore(oSNode);
},
splitTextAtBothEnds : function(){
this._splitTextEndNodesOfTheRange();
},
getStartNode : function(){
if(this.collapsed){
if(this.startContainer.nodeType == 3){
if(this.startOffset === 0){return null;}
if(this.startContainer.nodeValue.length <= this.startOffset){return null;}
return this.startContainer;
}
return null;
}
if(this.startContainer.nodeType == 3){
if(this.startOffset >= this.startContainer.nodeValue.length){return this._getNextNode(this.startContainer);}
return this.startContainer;
}else{
if(this.startOffset >= nhn.DOMFix.childNodes(this.startContainer).length){return this._getNextNode(this.startContainer);}
return nhn.DOMFix.childNodes(this.startContainer)[this.startOffset];
}
},
getEndNode : function(){
if(this.collapsed){return this.getStartNode();}
if(this.endContainer.nodeType == 3){
if(this.endOffset === 0){return this._getPrevNode(this.endContainer);}
return this.endContainer;
}else{
if(this.endOffset === 0){return this._getPrevNode(this.endContainer);}
return nhn.DOMFix.childNodes(this.endContainer)[this.endOffset-1];
}
},
getNodeAroundRange : function(bBefore, bStrict){
if(!this.collapsed){return this.getStartNode();}
if(this.startContainer && this.startContainer.nodeType == 3){return this.startContainer;}
var oBeforeRange, oAfterRange, oResult;
if(this.startOffset >= nhn.DOMFix.childNodes(this.startContainer).length){
oAfterRange = this._getNextNode(this.startContainer);
}else{
oAfterRange = nhn.DOMFix.childNodes(this.startContainer)[this.startOffset];
}
if(this.endOffset === 0){
oBeforeRange = this._getPrevNode(this.endContainer);
}else{
oBeforeRange = nhn.DOMFix.childNodes(this.endContainer)[this.endOffset-1];
}
if(bBefore){
oResult = oBeforeRange;
if(!oResult && !bStrict){oResult = oAfterRange;}
}else{
oResult = oAfterRange;
if(!oResult && !bStrict){oResult = oBeforeRange;}
}
return oResult;
},
_getXPath : function(elNode){
var sXPath = "";
while(elNode && elNode.nodeType == 1){
sXPath = "/" + elNode.tagName+"["+this._getPosIdx4XPath(elNode)+"]" + sXPath;
elNode = nhn.DOMFix.parentNode(elNode);
}
return sXPath;
},
_getPosIdx4XPath : function(refNode){
var idx = 0;
for(var node = refNode.previousSibling; node; node = node.previousSibling){
if(node.tagName == refNode.tagName){idx++;}
}
return idx;
},
_evaluateXPath : function(sXPath, oDoc){
sXPath = sXPath.substring(1, sXPath.length-1);
var aXPath = sXPath.split(/\//);
var elNode = oDoc.body;
for(var i=2; i<aXPath.length && elNode; i++){
aXPath[i].match(/([^\[]+)\[(\d+)/i);
var sTagName = RegExp.$1;
var nIdx = RegExp.$2;
var aAllNodes = nhn.DOMFix.childNodes(elNode);
var aNodes = [];
var nLength = aAllNodes.length;
var nCount = 0;
for(var ii=0; ii<nLength; ii++){
if(aAllNodes[ii].tagName == sTagName){aNodes[nCount++] = aAllNodes[ii];}
}
if(aNodes.length < nIdx){
elNode = null;
}else{
elNode = aNodes[nIdx];
}
}
return elNode;
},
_evaluateXPathBookmark : function(oBookmark){
var sXPath = oBookmark["sXPath"];
var nTextNodeIdx = oBookmark["nTextNodeIdx"];
var nOffset = oBookmark["nOffset"];
var elContainer = this._evaluateXPath(sXPath, this._document);
if(nTextNodeIdx > -1 && elContainer){
var aChildNodes = nhn.DOMFix.childNodes(elContainer);
var elNode = null;
var nIdx = nTextNodeIdx;
var nOffsetLeft = nOffset;
while((elNode = aChildNodes[nIdx]) && elNode.nodeType == 3 && elNode.nodeValue.length < nOffsetLeft){
nOffsetLeft -= elNode.nodeValue.length;
nIdx++;
}
elContainer = nhn.DOMFix.childNodes(elContainer)[nIdx];
nOffset = nOffsetLeft;
}
if(!elContainer){
elContainer = this._document.body;
nOffset = 0;
}
return {elContainer: elContainer, nOffset: nOffset};
},
getXPathBookmark : function(){
var nTextNodeIdx1 = -1;
var htEndPt1 = {elContainer: this.startContainer, nOffset: this.startOffset};
var elNode1 = this.startContainer;
if(elNode1.nodeType == 3){
htEndPt1 = this._getFixedStartTextNode();
nTextNodeIdx1 = this._getPosIdx(htEndPt1.elContainer);
elNode1 = nhn.DOMFix.parentNode(elNode1);
}
var sXPathNode1 = this._getXPath(elNode1);
var oBookmark1 = {sXPath:sXPathNode1, nTextNodeIdx:nTextNodeIdx1, nOffset: htEndPt1.nOffset};
if(this.collapsed){
var oBookmark2 = {sXPath:sXPathNode1, nTextNodeIdx:nTextNodeIdx1, nOffset: htEndPt1.nOffset};
}else{
var nTextNodeIdx2 = -1;
var htEndPt2 = {elContainer: this.endContainer, nOffset: this.endOffset};
var elNode2 = this.endContainer;
if(elNode2.nodeType == 3){
htEndPt2 = this._getFixedEndTextNode();
nTextNodeIdx2 = this._getPosIdx(htEndPt2.elContainer);
elNode2 = nhn.DOMFix.parentNode(elNode2);
}
var sXPathNode2 = this._getXPath(elNode2);
var oBookmark2 = {sXPath:sXPathNode2, nTextNodeIdx:nTextNodeIdx2, nOffset: htEndPt2.nOffset};
}
return [oBookmark1, oBookmark2];
},
moveToXPathBookmark : function(aBookmark){
if(!aBookmark){return false;}
var oBookmarkInfo1 = this._evaluateXPathBookmark(aBookmark[0]);
var oBookmarkInfo2 = this._evaluateXPathBookmark(aBookmark[1]);
if(!oBookmarkInfo1["elContainer"] || !oBookmarkInfo2["elContainer"]){return;}
this.startContainer = oBookmarkInfo1["elContainer"];
this.startOffset = oBookmarkInfo1["nOffset"];
this.endContainer = oBookmarkInfo2["elContainer"];
this.endOffset = oBookmarkInfo2["nOffset"];
return true;
},
_getFixedTextContainer : function(elNode, nOffset){
while(elNode && elNode.nodeType == 3 && elNode.previousSibling && elNode.previousSibling.nodeType == 3){
nOffset += elNode.previousSibling.nodeValue.length;
elNode = elNode.previousSibling;
}
return {elContainer:elNode, nOffset:nOffset};
},
_getFixedStartTextNode : function(){
return this._getFixedTextContainer(this.startContainer, this.startOffset);
},
_getFixedEndTextNode : function(){
return this._getFixedTextContainer(this.endContainer, this.endOffset);
},
placeStringBookmark : function(){
if(this.collapsed || jindo.$Agent().navigator().ie || jindo.$Agent().navigator().firefox){
return this.placeStringBookmark_NonWebkit();
}else{
return this.placeStringBookmark_Webkit();
}
},
placeStringBookmark_NonWebkit : function(){
var sTmpId = (new Date()).getTime();
var oInsertionPoint = this.cloneRange();
oInsertionPoint.collapseToEnd();
var oEndMarker = this._document.createElement("SPAN");
oEndMarker.id = this.HUSKY_BOOMARK_END_ID_PREFIX+sTmpId;
oInsertionPoint.insertNode(oEndMarker);
var oInsertionPoint = this.cloneRange();
oInsertionPoint.collapseToStart();
var oStartMarker = this._document.createElement("SPAN");
oStartMarker.id = this.HUSKY_BOOMARK_START_ID_PREFIX+sTmpId;
oInsertionPoint.insertNode(oStartMarker);
if(jindo.$Agent().navigator().ie){
try{
oStartMarker.innerHTML = unescape("%uFEFF");
}catch(e){}
try{
oEndMarker.innerHTML = unescape("%uFEFF");
}catch(e){}
}
this.moveToBookmark(sTmpId);
return sTmpId;
},
placeStringBookmark_Webkit : function(){
var sTmpId = (new Date()).getTime();
var elInsertBefore, elInsertParent;
var oInsertionPoint = this.cloneRange();
oInsertionPoint.collapseToEnd();
elInsertBefore = this._document.createTextNode("");
oInsertionPoint.insertNode(elInsertBefore);
elInsertParent = elInsertBefore.parentNode;
if(elInsertBefore.previousSibling && elInsertBefore.previousSibling.tagName == "TD"){
elInsertParent = elInsertBefore.previousSibling;
elInsertBefore = null;
}
var oEndMarker = this._document.createElement("SPAN");
oEndMarker.id = this.HUSKY_BOOMARK_END_ID_PREFIX+sTmpId;
elInsertParent.insertBefore(oEndMarker, elInsertBefore);
var oInsertionPoint = this.cloneRange();
oInsertionPoint.collapseToStart();
elInsertBefore = this._document.createTextNode("");
oInsertionPoint.insertNode(elInsertBefore);
elInsertParent = elInsertBefore.parentNode;
if(elInsertBefore.nextSibling && elInsertBefore.nextSibling.tagName == "TD"){
elInsertParent = elInsertBefore.nextSibling;
elInsertBefore = elInsertParent.firstChild;
}
var oStartMarker = this._document.createElement("SPAN");
oStartMarker.id = this.HUSKY_BOOMARK_START_ID_PREFIX+sTmpId;
elInsertParent.insertBefore(oStartMarker, elInsertBefore);
this.moveToBookmark(sTmpId);
return sTmpId;
},
cloneRange : function(){
return this._copyRange(new nhn.HuskyRange(this._window));
},
moveToBookmark : function(vBookmark){
if(typeof(vBookmark) != "object"){
return this.moveToStringBookmark(vBookmark);
}else{
return this.moveToXPathBookmark(vBookmark);
}
},
getStringBookmark : function(sBookmarkID, bEndBookmark){
if(bEndBookmark){
return this._document.getElementById(this.HUSKY_BOOMARK_END_ID_PREFIX+sBookmarkID);
}else{
return this._document.getElementById(this.HUSKY_BOOMARK_START_ID_PREFIX+sBookmarkID);
}
},
moveToStringBookmark : function(sBookmarkID, bIncludeBookmark){
var oStartMarker = this.getStringBookmark(sBookmarkID);
var oEndMarker = this.getStringBookmark(sBookmarkID, true);
if(!oStartMarker || !oEndMarker){return false;}
this.reset(this._window);
if(bIncludeBookmark){
this.setEndAfter(oEndMarker);
this.setStartBefore(oStartMarker);
}else{
this.setEndBefore(oEndMarker);
this.setStartAfter(oStartMarker);
}
return true;
},
removeStringBookmark : function(sBookmarkID){
this._removeAll(this.HUSKY_BOOMARK_START_ID_PREFIX+sBookmarkID);
this._removeAll(this.HUSKY_BOOMARK_END_ID_PREFIX+sBookmarkID);
},
_removeAll : function(sID){
var elNode;
while((elNode = this._document.getElementById(sID))){
nhn.DOMFix.parentNode(elNode).removeChild(elNode);
}
},
collapseToStart : function(){
this.collapse(true);
},
collapseToEnd : function(){
this.collapse(false);
},
createAndInsertNode : function(sTagName){
var tmpNode = this._document.createElement(sTagName);
this.insertNode(tmpNode);
return tmpNode;
},
getNodes : function(bSplitTextEndNodes, fnFilter){
if(bSplitTextEndNodes){this._splitTextEndNodesOfTheRange();}
var aAllNodes = this._getNodesInRange();
var aFilteredNodes = [];
if(!fnFilter){return aAllNodes;}
for(var i=0; i<aAllNodes.length; i++){
if(fnFilter(aAllNodes[i])){aFilteredNodes[aFilteredNodes.length] = aAllNodes[i];}
}
return aFilteredNodes;
},
getTextNodes : function(bSplitTextEndNodes){
var txtFilter = function(oNode){
if (oNode.nodeType == 3 && oNode.nodeValue != "\n" && oNode.nodeValue != ""){
return true;
}else{
return false;
}
};
return this.getNodes(bSplitTextEndNodes, txtFilter);
},
surroundContentsWithNewNode : function(sTagName){
var oNewParent = this._document.createElement(sTagName);
this.surroundContents(oNewParent);
return oNewParent;
},
isRangeinRange : function(oAnoterRange, bIncludePartlySelected){
var startToStart = this.compareBoundaryPoints(this.W3CDOMRange.START_TO_START, oAnoterRange);
var startToEnd = this.compareBoundaryPoints(this.W3CDOMRange.START_TO_END, oAnoterRange);
var endToStart = this.compareBoundaryPoints(this.W3CDOMRange.ND_TO_START, oAnoterRange);
var endToEnd = this.compareBoundaryPoints(this.W3CDOMRange.END_TO_END, oAnoterRange);
if(startToStart <= 0 && endToEnd >= 0){return true;}
if(bIncludePartlySelected){
if(startToEnd == 1){return false;}
if(endToStart == -1){return false;}
return true;
}
return false;
},
isNodeInRange : function(oNode, bIncludePartlySelected, bContentOnly){
var oTmpRange = new nhn.HuskyRange(this._window);
if(bContentOnly && oNode.firstChild){
oTmpRange.setStartBefore(oNode.firstChild);
oTmpRange.setEndAfter(oNode.lastChild);
}else{
oTmpRange.selectNode(oNode);
}
return this.isRangeInRange(oTmpRange, bIncludePartlySelected);
},
pasteText : function(sText){
this.pasteHTML(sText.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/ /g, "&nbsp;").replace(/"/g, "&quot;"));
},
pasteHTML : function(sHTML){
var oTmpDiv = this._document.createElement("DIV");
oTmpDiv.innerHTML = sHTML;
if(!oTmpDiv.firstChild){
this.deleteContents();
return;
}
var clone = this.cloneRange();
var sBM = clone.placeStringBookmark();
var oLineInfo = clone.getLineInfo(),
oStart = oLineInfo.oStart,
oEnd = oLineInfo.oEnd;
if(oStart.oLineBreaker && oStart.oLineBreaker.nodeName === "P" && clone.rxHasBlock.test(sHTML)){
clone.deleteContents();
var oParentNode = oStart.oLineBreaker.parentNode,
oNextSibling = oStart.oLineBreaker.nextSibling;
if(oStart.oLineBreaker === oEnd.oLineBreaker){
var elBM = clone.getStringBookmark(sBM);
clone.setEndNodes(elBM, oEnd.oLineBreaker);
var oNextContents = clone.extractContents();
if(oNextSibling){
oParentNode.insertBefore(oNextContents, oNextSibling);
}else{
oParentNode.appendChild(oNextContents);
}
oNextSibling = oStart.oLineBreaker.nextSibling;
}
oTmpDiv.style.cssText = oStart.oLineBreaker.style.cssText.replace(this._rxTextAlign, '');
oTmpDiv.align = oStart.oLineBreaker.align;
if(oNextSibling){
oParentNode.insertBefore(oTmpDiv, oNextSibling);
}else{
oParentNode.appendChild(oTmpDiv);
}
clone.removeStringBookmark(sBM);
this._removeEmptyP(this._getPrevElement(oTmpDiv));
var elNextLine = this._getNextElement(oTmpDiv);
if(elNextLine){
var elAfterNext = this._getNextElement(elNextLine);
if(elAfterNext){
this._removeEmptyP(elNextLine);
elNextLine = elAfterNext;
}
}else{
elNextLine = this._document.createElement("P");
elNextLine.style.cssText = oStart.oLineBreaker.style.cssText;
elNextLine.align = oStart.oLineBreaker.align;
elNextLine.innerHTML = '\uFEFF';
oParentNode.appendChild(elNextLine);
}
this.selectNodeContents(elNextLine);
this.collapseToStart();
}else{
var oFirstNode = oTmpDiv.firstChild;
var oLastNode = oTmpDiv.lastChild;
this.collapseToStart();
while(oTmpDiv.lastChild){this.insertNode(oTmpDiv.lastChild);}
this.setEndNodes(oFirstNode, oLastNode);
clone.moveToBookmark(sBM);
clone.deleteContents();
clone.removeStringBookmark(sBM);
}
},
_removeEmptyP : function(el){
if(el && el.nodeName === "P"){
var sInner = el.innerHTML;
if(sInner === "" || this._rxCursorHolder.test(sInner)){
el.parentNode.removeChild(el);
}
}
},
_getSiblingElement : function(oNode, bPrev){
if(!oNode){
return null;
}
var oSibling = oNode[bPrev ? "previousSibling" : "nextSibling"];
if(oSibling && oSibling.nodeType === 1){
return oSibling;
}else{
return arguments.callee(oSibling, bPrev);
}
},
_getPrevElement : function(oNode){
return this._getSiblingElement(oNode, true);
},
_getNextElement : function(oNode){
return this._getSiblingElement(oNode, false);
},
toString : function(){
this.toString = nhn.W3CDOMRange.prototype.toString;
return this.toString();
},
toHTMLString : function(){
var oTmpContainer = this._document.createElement("DIV");
oTmpContainer.appendChild(this.cloneContents());
return oTmpContainer.innerHTML;
},
findAncestorByTagName : function(sTagName){
var oNode = this.commonAncestorContainer;
while(oNode && oNode.tagName != sTagName){oNode = nhn.DOMFix.parentNode(oNode);}
return oNode;
},
selectNodeContents : function(oNode){
if(!oNode){return;}
var oFirstNode = oNode.firstChild?oNode.firstChild:oNode;
var oLastNode = oNode.lastChild?oNode.lastChild:oNode;
this.reset(this._window);
if(oFirstNode.nodeType == 3){
this.setStart(oFirstNode, 0, true);
}else{
this.setStartBefore(oFirstNode);
}
if(oLastNode.nodeType == 3){
this.setEnd(oLastNode, oLastNode.nodeValue.length, true);
}else{
this.setEndAfter(oLastNode);
}
},
_hasTextDecoration : function(oNode, sValue){
if(!oNode || !oNode.style){
return false;
}
if(oNode.style.textDecoration.indexOf(sValue) > -1){
return true;
}
if(sValue === "underline" && oNode.tagName === "U"){
return true;
}
if(sValue === "line-through" && (oNode.tagName === "S" || oNode.tagName === "STRIKE")){
return true;
}
return false;
},
_setTextDecoration : function(oNode, sValue){
if (jindo.$Agent().navigator().firefox) {
oNode.style.textDecoration = (oNode.style.textDecoration) ? oNode.style.textDecoration + " " + sValue : sValue;
}
else{
if(sValue === "underline"){
oNode.innerHTML = "<U>" + oNode.innerHTML + "</U>"
}else if(sValue === "line-through"){
oNode.innerHTML = "<STRIKE>" + oNode.innerHTML + "</STRIKE>"
}
}
},
_checkTextDecoration : function(oNode){
if(oNode.tagName !== "SPAN"){
return;
}
var bUnderline = false,
bLineThrough = false,
sTextDecoration = "",
oParentNode = null;
oChildNode = oNode.firstChild;

while(oChildNode){
if(oChildNode.nodeType === 1){
bUnderline = (bUnderline || oChildNode.tagName === "U");
bLineThrough = (bLineThrough || oChildNode.tagName === "S" || oChildNode.tagName === "STRIKE");
}
if(bUnderline && bLineThrough){
return;
}
oChildNode = oChildNode.nextSibling;
}
oParentNode = nhn.DOMFix.parentNode(oNode);

while(oParentNode && oParentNode.tagName !== "BODY"){
if(oParentNode.nodeType !== 1){
oParentNode = nhn.DOMFix.parentNode(oParentNode);
continue;
}
if(!bUnderline && this._hasTextDecoration(oParentNode, "underline")){
bUnderline = true;
this._setTextDecoration(oNode, "underline");
}
if(!bLineThrough && this._hasTextDecoration(oParentNode, "line-through")){
bLineThrough = true;
this._setTextDecoration(oNode, "line-through");
}
if(bUnderline && bLineThrough){
return;
}
oParentNode = nhn.DOMFix.parentNode(oParentNode);
}
},
styleRange : function(oStyle, oAttribute, sNewSpanMarker, bIncludeLI, bCheckTextDecoration){
var aStyleParents = this.aStyleParents = this._getStyleParentNodes(sNewSpanMarker, bIncludeLI);
if(aStyleParents.length < 1){return;}
var sName, sValue;
for(var i=0; i<aStyleParents.length; i++){
for(var x in oStyle){
sName = x;
sValue = oStyle[sName];
if(typeof sValue != "string"){continue;}
if(bCheckTextDecoration && oStyle.color){
this._checkTextDecoration(aStyleParents[i]);
}
aStyleParents[i].style[sName] = sValue;
}
if(!oAttribute){continue;}
for(var x in oAttribute){
sName = x;
sValue = oAttribute[sName];
if(typeof sValue != "string"){continue;}
if(sName == "class"){
jindo.$Element(aStyleParents[i]).addClass(sValue);
}else{
aStyleParents[i].setAttribute(sName, sValue);
}
}
}
this.reset(this._window);
this.setStartBefore(aStyleParents[0]);
this.setEndAfter(aStyleParents[aStyleParents.length-1]);
},
expandBothEnds : function(){
this.expandStart();
this.expandEnd();
},
expandStart : function(){
if(this.startContainer.nodeType == 3 && this.startOffset !== 0){return;}
var elActualStartNode = this._getActualStartNode(this.startContainer, this.startOffset);
elActualStartNode = this._getPrevNode(elActualStartNode);
if(elActualStartNode.tagName == "BODY"){
this.setStartBefore(elActualStartNode);
}else{
this.setStartAfter(elActualStartNode);
}
},
expandEnd : function(){
if(this.endContainer.nodeType == 3 && this.endOffset < this.endContainer.nodeValue.length){return;}
var elActualEndNode = this._getActualEndNode(this.endContainer, this.endOffset);
elActualEndNode = this._getNextNode(elActualEndNode);
if(elActualEndNode.tagName == "BODY"){
this.setEndAfter(elActualEndNode);
}else{
this.setEndBefore(elActualEndNode);
}
},
_getStyleParentNodes : function(sNewSpanMarker, bIncludeLI){
this._splitTextEndNodesOfTheRange();
var oSNode = this.getStartNode();
var oENode = this.getEndNode();
var aAllNodes = this._getNodesInRange();
var aResult = [];
var nResult = 0;
var oNode, oTmpNode, iStartRelPos, iEndRelPos, oSpan;
var nInitialLength = aAllNodes.length;
var arAllBottomNodes = jindo.$A(aAllNodes).filter(function(v){return (!v.firstChild || (bIncludeLI && v.tagName=="LI"));});
var elTmpNode = this.commonAncestorContainer;
if(bIncludeLI){
while(elTmpNode){
if(elTmpNode.tagName == "LI"){
if(this._isFullyContained(elTmpNode, arAllBottomNodes)){
aResult[nResult++] = elTmpNode;
}
break;
}
elTmpNode = elTmpNode.parentNode;
}
}
for(var i=0; i<nInitialLength; i++){
oNode = aAllNodes[i];
if(!oNode){continue;}
if(bIncludeLI && oNode.tagName == "LI" && this._isFullyContained(oNode, arAllBottomNodes)){
aResult[nResult++] = oNode;
continue;
}
if(oNode.nodeType != 3){continue;}
if(oNode.nodeValue == "" || oNode.nodeValue.match(/^(\r|\n)+$/)){continue;}
var oParentNode = nhn.DOMFix.parentNode(oNode);
if(oParentNode.tagName == "SPAN"){
if(this._isFullyContained(oParentNode, arAllBottomNodes, oNode)){
aResult[nResult++] = oParentNode;
continue;
}
}else{
var oParentSingleSpan = this._findParentSingleSpan(oParentNode);
if(oParentSingleSpan){
aResult[nResult++] = oParentSingleSpan;
continue;
}
}
oSpan = this._document.createElement("SPAN");
oParentNode.insertBefore(oSpan, oNode);
oSpan.appendChild(oNode);
aResult[nResult++] = oSpan;
if(sNewSpanMarker){oSpan.setAttribute(sNewSpanMarker, "true");}
}
this.setStartBefore(oSNode);
this.setEndAfter(oENode);
return aResult;
},
_findParentSingleSpan : function(oNode){
if(!oNode){
return null;
}
for(var i = 0, nCnt = 0, sValue, oChild, aChildNodes = oNode.childNodes; (oChild = aChildNodes[i]); i++){
sValue = oChild.nodeValue;
if(this._rxCursorHolder.test(sValue)){
continue;
}else{
nCnt++;
}
if(nCnt > 1){
return null;
}
}
if(oNode.nodeName === "SPAN"){
return oNode;
}else{
return this._findParentSingleSpan(oNode.parentNode);
}
},
_isFullyContained : function(elContainer, waAllNodes, oNode){
var nSIdx, nEIdx;
var oTmpNode = this._getVeryFirstRealChild(elContainer);
if(oNode && oTmpNode == oNode){
nSIdx = 1;
}else{
nSIdx = waAllNodes.indexOf(oTmpNode);
}
if(nSIdx != -1){
oTmpNode = this._getVeryLastRealChild(elContainer);
if(oNode && oTmpNode == oNode){
nEIdx = 1;
}else{
nEIdx = waAllNodes.indexOf(oTmpNode);
}
}
return (nSIdx != -1 && nEIdx != -1);
},
_getVeryFirstChild : function(oNode){
if(oNode.firstChild){return this._getVeryFirstChild(oNode.firstChild);}
return oNode;
},
_getVeryLastChild : function(oNode){
if(oNode.lastChild){return this._getVeryLastChild(oNode.lastChild);}
return oNode;
},
_getFirstRealChild : function(oNode){
var oFirstNode = oNode.firstChild;
while(oFirstNode && oFirstNode.nodeType == 3 && oFirstNode.nodeValue == ""){oFirstNode = oFirstNode.nextSibling;}
return oFirstNode;
},
_getLastRealChild : function(oNode){
var oLastNode = oNode.lastChild;
while(oLastNode && oLastNode.nodeType == 3 && oLastNode.nodeValue == ""){oLastNode = oLastNode.previousSibling;}
return oLastNode;
},
_getVeryFirstRealChild : function(oNode){
var oFirstNode = this._getFirstRealChild(oNode);
if(oFirstNode){return this._getVeryFirstRealChild(oFirstNode);}
return oNode;
},
_getVeryLastRealChild : function(oNode){
var oLastNode = this._getLastRealChild(oNode);
if(oLastNode){return this._getVeryLastChild(oLastNode);}
return oNode;
},
_getLineStartInfo : function(node){
var frontEndFinal = null;
var frontEnd = node;
var lineBreaker = node;
var bParentBreak = false;
var rxLineBreaker = this.rxLineBreaker;
function getLineStart(node){
if(!node){return;}
if(frontEndFinal){return;}
if(rxLineBreaker.test(node.tagName)){
lineBreaker = node;
frontEndFinal = frontEnd;
bParentBreak = true;
return;
}else{
frontEnd = node;
}
getFrontEnd(node.previousSibling);
if(frontEndFinal){return;}
getLineStart(nhn.DOMFix.parentNode(node));
}
function getFrontEnd(node){
if(!node){return;}
if(frontEndFinal){return;}
if(rxLineBreaker.test(node.tagName)){
lineBreaker = node;
frontEndFinal = frontEnd;
bParentBreak = false;
return;
}
if(node.firstChild && node.tagName != "TABLE"){
var curNode = node.lastChild;
while(curNode && !frontEndFinal){
getFrontEnd(curNode);
curNode = curNode.previousSibling;
}
}else{
frontEnd = node;
}
if(!frontEndFinal){
getFrontEnd(node.previousSibling);
}
}
if(rxLineBreaker.test(node.tagName)){
frontEndFinal = node;
}else{
getLineStart(node);
}
return {oNode: frontEndFinal, oLineBreaker: lineBreaker, bParentBreak: bParentBreak};
},
_getLineEndInfo : function(node){
var backEndFinal = null;
var backEnd = node;
var lineBreaker = node;
var bParentBreak = false;
var rxLineBreaker = this.rxLineBreaker;
function getLineEnd(node){
if(!node){return;}
if(backEndFinal){return;}
if(rxLineBreaker.test(node.tagName)){
lineBreaker = node;
backEndFinal = backEnd;
bParentBreak = true;
return;
}else{
backEnd = node;
}
getBackEnd(node.nextSibling);
if(backEndFinal){return;}
getLineEnd(nhn.DOMFix.parentNode(node));
}
function getBackEnd(node){
if(!node){return;}
if(backEndFinal){return;}
if(rxLineBreaker.test(node.tagName)){
lineBreaker = node;
backEndFinal = backEnd;
bParentBreak = false;
return;
}
if(node.firstChild && node.tagName != "TABLE"){
var curNode = node.firstChild;
while(curNode && !backEndFinal){
getBackEnd(curNode);
curNode = curNode.nextSibling;
}
}else{
backEnd = node;
}
if(!backEndFinal){
getBackEnd(node.nextSibling);
}
}
if(rxLineBreaker.test(node.tagName)){
backEndFinal = node;
}else{
getLineEnd(node);
}
return {oNode: backEndFinal, oLineBreaker: lineBreaker, bParentBreak: bParentBreak};
},
getLineInfo : function(bAfter){
var bAfter = bAfter || false;
var oSNode = this.getStartNode();
var oENode = this.getEndNode();
if(!oSNode){oSNode = this.getNodeAroundRange(!bAfter, true);}
if(!oENode){oENode = this.getNodeAroundRange(!bAfter, true);}
var oStart = this._getLineStartInfo(oSNode);
var oStartNode = oStart.oNode;
var oEnd = this._getLineEndInfo(oENode);
var oEndNode = oEnd.oNode;
if(oSNode != oStartNode || oENode != oEndNode){
var iRelativeStartPos = this._compareEndPoint(nhn.DOMFix.parentNode(oStartNode), this._getPosIdx(oStartNode), this.endContainer, this.endOffset);
var iRelativeEndPos = this._compareEndPoint(nhn.DOMFix.parentNode(oEndNode), this._getPosIdx(oEndNode)+1, this.startContainer, this.startOffset);
if(!(iRelativeStartPos <= 0 && iRelativeEndPos >= 0)){
oSNode = this.getNodeAroundRange(false, true);
oENode = this.getNodeAroundRange(false, true);
oStart = this._getLineStartInfo(oSNode);
oEnd = this._getLineEndInfo(oENode);
}
}
return {oStart: oStart, oEnd: oEnd};
},
_findSingleChild : function(oNode){
if(!oNode){
return null;
}
var oSingleChild = null;
for(var i = 0, nCnt = 0, sValue, oChild, aChildNodes = oNode.childNodes; (oChild = aChildNodes[i]); i++){
sValue = oChild.nodeValue;
if(this._rxCursorHolder.test(sValue)){
continue;
}else{
oSingleChild = oChild;
nCnt++;
}
if(nCnt > 1){
return null;
}
}
return oSingleChild;
},
_hasCursorHolderOnly : function(oNode){
if(!oNode || oNode.nodeType !== 1){
return false;
}
if(this._rxCursorHolder.test(oNode.innerHTML)){
return true;
}else{
return this._hasCursorHolderOnly(this._findSingleChild(oNode));
}
}
}).extend(nhn.W3CDOMRange);
nhn.BrowserSelection = function(win){
this.init = function(win){
this._window = win || window;
this._document = this._window.document;
};
this.init(win);
if(!!this._document.createRange){
nhn.BrowserSelectionImpl_FF.apply(this);
}else{
nhn.BrowserSelectionImpl_IE.apply(this);
}
this.selectRange = function(oRng){
this.selectNone();
this.addRange(oRng);
};
this.selectionLoaded = true;
if(!this._oSelection){this.selectionLoaded = false;}
};
nhn.BrowserSelectionImpl_FF = function(){
this._oSelection = this._window.getSelection();
this.getRangeAt = function(iNum){
iNum = iNum || 0;
try{
var oFFRange = this._oSelection.getRangeAt(iNum);
}catch(e){return new nhn.W3CDOMRange(this._window);}
return this._FFRange2W3CRange(oFFRange);
};
this.addRange = function(oW3CRange){
var oFFRange = this._W3CRange2FFRange(oW3CRange);
this._oSelection.addRange(oFFRange);
};
this.selectNone = function(){
this._oSelection.removeAllRanges();
};
this.getCommonAncestorContainer = function(oW3CRange){
var oFFRange = this._W3CRange2FFRange(oW3CRange);
return oFFRange.commonAncestorContainer;
};
this.isCollapsed = function(oW3CRange){
var oFFRange = this._W3CRange2FFRange(oW3CRange);
return oFFRange.collapsed;
};
this.compareEndPoints = function(elContainerA, nOffsetA, elContainerB, nOffsetB){
var oFFRangeA = this._document.createRange();
var oFFRangeB = this._document.createRange();
oFFRangeA.setStart(elContainerA, nOffsetA);
oFFRangeB.setStart(elContainerB, nOffsetB);
oFFRangeA.collapse(true);
oFFRangeB.collapse(true);
try{
return oFFRangeA.compareBoundaryPoints(1, oFFRangeB);
}catch(e){
return 1;
}
};
this._FFRange2W3CRange = function(oFFRange){
var oW3CRange = new nhn.W3CDOMRange(this._window);
oW3CRange.setStart(oFFRange.startContainer, oFFRange.startOffset, true);
oW3CRange.setEnd(oFFRange.endContainer, oFFRange.endOffset, true);
return oW3CRange;
};
this._W3CRange2FFRange = function(oW3CRange){
var oFFRange = this._document.createRange();
oFFRange.setStart(oW3CRange.startContainer, oW3CRange.startOffset);
oFFRange.setEnd(oW3CRange.endContainer, oW3CRange.endOffset);
return oFFRange;
};
};
nhn.BrowserSelectionImpl_IE = function(){
this._oSelection = this._document.selection;
this.oLastRange = {
oBrowserRange : null,
elStartContainer : null,
nStartOffset : -1,
elEndContainer : null,
nEndOffset : -1
};
this._updateLastRange = function(oBrowserRange, oW3CRange){
this.oLastRange.oBrowserRange = oBrowserRange;
this.oLastRange.elStartContainer = oW3CRange.startContainer;
this.oLastRange.nStartOffset = oW3CRange.startOffset;
this.oLastRange.elEndContainer = oW3CRange.endContainer;
this.oLastRange.nEndOffset = oW3CRange.endOffset;
};
this.getRangeAt = function(iNum){
iNum = iNum || 0;
var oW3CRange, oBrowserRange;
if(this._oSelection.type == "Control"){
oW3CRange = new nhn.W3CDOMRange(this._window);
var oSelectedNode = this._oSelection.createRange().item(iNum);
if(!oSelectedNode || oSelectedNode.ownerDocument != this._document){return oW3CRange;}
oW3CRange.selectNode(oSelectedNode);
return oW3CRange;
}else{
oBrowserRange = this._oSelection.createRange();
var oSelectedNode = oBrowserRange.parentElement();
if(!oSelectedNode || oSelectedNode.ownerDocument != this._document){
oW3CRange = new nhn.W3CDOMRange(this._window);
return oW3CRange;
}
oW3CRange = this._IERange2W3CRange(oBrowserRange);
return oW3CRange;
}
};
this.addRange = function(oW3CRange){
var oIERange = this._W3CRange2IERange(oW3CRange);
oIERange.select();
};
this.selectNone = function(){
this._oSelection.empty();
};
this.getCommonAncestorContainer = function(oW3CRange){
return this._W3CRange2IERange(oW3CRange).parentElement();
};
this.isCollapsed = function(oW3CRange){
var oRange = this._W3CRange2IERange(oW3CRange);
var oRange2 = oRange.duplicate();
oRange2.collapse();
return oRange.isEqual(oRange2);
};
this.compareEndPoints = function(elContainerA, nOffsetA, elContainerB, nOffsetB){
var oIERangeA, oIERangeB;
if(elContainerA === this.oLastRange.elStartContainer && nOffsetA === this.oLastRange.nStartOffset){
oIERangeA = this.oLastRange.oBrowserRange.duplicate();
oIERangeA.collapse(true);
}else{
if(elContainerA === this.oLastRange.elEndContainer && nOffsetA === this.oLastRange.nEndOffset){
oIERangeA = this.oLastRange.oBrowserRange.duplicate();
oIERangeA.collapse(false);
}else{
oIERangeA = this._getIERangeAt(elContainerA, nOffsetA);
}
}
if(elContainerB === this.oLastRange.elStartContainer && nOffsetB === this.oLastRange.nStartOffset){
oIERangeB = this.oLastRange.oBrowserRange.duplicate();
oIERangeB.collapse(true);
}else{
if(elContainerB === this.oLastRange.elEndContainer && nOffsetB === this.oLastRange.nEndOffset){
oIERangeB = this.oLastRange.oBrowserRange.duplicate();
oIERangeB.collapse(false);
}else{
oIERangeB = this._getIERangeAt(elContainerB, nOffsetB);
}
}
return oIERangeA.compareEndPoints("StartToStart", oIERangeB);
};
this._W3CRange2IERange = function(oW3CRange){
if(this.oLastRange.elStartContainer === oW3CRange.startContainer &&
this.oLastRange.nStartOffset === oW3CRange.startOffset &&
this.oLastRange.elEndContainer === oW3CRange.endContainer &&
this.oLastRange.nEndOffset === oW3CRange.endOffset){
return this.oLastRange.oBrowserRange;
}
var oStartIERange = this._getIERangeAt(oW3CRange.startContainer, oW3CRange.startOffset);
var oEndIERange = this._getIERangeAt(oW3CRange.endContainer, oW3CRange.endOffset);
oStartIERange.setEndPoint("EndToEnd", oEndIERange);
this._updateLastRange(oStartIERange, oW3CRange);
return oStartIERange;
};
this._getIERangeAt = function(oW3CContainer, iW3COffset){
var oIERange = this._document.body.createTextRange();
var oEndPointInfoForIERange = this._getSelectableNodeAndOffsetForIE(oW3CContainer, iW3COffset);
var oSelectableNode = oEndPointInfoForIERange.oSelectableNodeForIE;
var iIEOffset = oEndPointInfoForIERange.iOffsetForIE;
oIERange.moveToElementText(oSelectableNode);
oIERange.collapse(oEndPointInfoForIERange.bCollapseToStart);
oIERange.moveStart("character", iIEOffset);
return oIERange;
};
this._getSelectableNodeAndOffsetForIE = function(oW3CContainer, iW3COffset){
var oNonTextNode = null;
var aChildNodes =  null;
var iNumOfLeftNodesToCount = 0;
if(oW3CContainer.nodeType == 3){
oNonTextNode = nhn.DOMFix.parentNode(oW3CContainer);
aChildNodes = nhn.DOMFix.childNodes(oNonTextNode);
iNumOfLeftNodesToCount = aChildNodes.length;
}else{
oNonTextNode = oW3CContainer;
aChildNodes = nhn.DOMFix.childNodes(oNonTextNode);
iNumOfLeftNodesToCount = (iW3COffset<aChildNodes.length)?iW3COffset:aChildNodes.length;
}
var oNodeTester = null;
var iResultOffset = 0;
var bCollapseToStart = true;
for(var i=0; i<iNumOfLeftNodesToCount; i++){
oNodeTester = aChildNodes[i];
if(oNodeTester.nodeType == 3){
if(oNodeTester == oW3CContainer){break;}
iResultOffset += oNodeTester.nodeValue.length;
}else{
oNonTextNode = oNodeTester;
iResultOffset = 0;
bCollapseToStart = false;
}
}
if(oW3CContainer.nodeType == 3){iResultOffset += iW3COffset;}
return {oSelectableNodeForIE:oNonTextNode, iOffsetForIE: iResultOffset, bCollapseToStart: bCollapseToStart};
};
this._IERange2W3CRange = function(oIERange){
var oW3CRange = new nhn.W3CDOMRange(this._window);
var oIEPointRange = null;
var oPosition = null;
oIEPointRange = oIERange.duplicate();
oIEPointRange.collapse(true);
oPosition = this._getW3CContainerAndOffset(oIEPointRange, true);
oW3CRange.setStart(oPosition.oContainer, oPosition.iOffset, true, true);
var oCollapsedChecker = oIERange.duplicate();
oCollapsedChecker.collapse(true);
if(oCollapsedChecker.isEqual(oIERange)){
oW3CRange.collapse(true);
}else{
oIEPointRange = oIERange.duplicate();
oIEPointRange.collapse(false);
oPosition = this._getW3CContainerAndOffset(oIEPointRange);
oW3CRange.setEnd(oPosition.oContainer, oPosition.iOffset, true);
}
this._updateLastRange(oIERange, oW3CRange);
return oW3CRange;
};
this._getW3CContainerAndOffset = function(oIEPointRange, bStartPt){
var oRgOrigPoint = oIEPointRange;
var oContainer = oRgOrigPoint.parentElement();
var offset = -1;
var oRgTester = this._document.body.createTextRange();
var aChildNodes = nhn.DOMFix.childNodes(oContainer);
var oPrevNonTextNode = null;
var pointRangeIdx = 0;
for(var i=0;i<aChildNodes.length;i++){
if(aChildNodes[i].nodeType == 3){continue;}
oRgTester.moveToElementText(aChildNodes[i]);
if(oRgTester.compareEndPoints("StartToStart", oIEPointRange)>=0){break;}
oPrevNonTextNode = aChildNodes[i];
}
var pointRangeIdx = i;
if(pointRangeIdx !== 0 && aChildNodes[pointRangeIdx-1].nodeType == 3){
var oRgTextStart = this._document.body.createTextRange();
var oCurTextNode = null;
if(oPrevNonTextNode){
oRgTextStart.moveToElementText(oPrevNonTextNode);
oRgTextStart.collapse(false);
oCurTextNode = oPrevNonTextNode.nextSibling;
}else{
oRgTextStart.moveToElementText(oContainer);
oRgTextStart.collapse(true);
oCurTextNode = oContainer.firstChild;
}
var oRgTextsUpToThePoint = oRgOrigPoint.duplicate();
oRgTextsUpToThePoint.setEndPoint("StartToStart", oRgTextStart);
var textCount = oRgTextsUpToThePoint.text.replace(/[\r\n]/g,"").length;
while(textCount > oCurTextNode.nodeValue.length && oCurTextNode.nextSibling){
textCount -= oCurTextNode.nodeValue.length;
oCurTextNode = oCurTextNode.nextSibling;
}
var oTmp = oCurTextNode.nodeValue;
if(bStartPt && oCurTextNode.nextSibling && oCurTextNode.nextSibling.nodeType == 3 && textCount == oCurTextNode.nodeValue.length){
textCount -= oCurTextNode.nodeValue.length;
oCurTextNode = oCurTextNode.nextSibling;
}
oContainer = oCurTextNode;
offset = textCount;
}else{
oContainer = oRgOrigPoint.parentElement();
offset = pointRangeIdx;
}
return {"oContainer" : oContainer, "iOffset" : offset};
};
};
nhn.DOMFix = new (jindo.$Class({
$init : function(){
if(jindo.$Agent().navigator().ie || jindo.$Agent().navigator().opera){
this.childNodes = this._childNodes_Fix;
this.parentNode = this._parentNode_Fix;
}else{
this.childNodes = this._childNodes_Native;
this.parentNode = this._parentNode_Native;
}
},
_parentNode_Native : function(elNode){
return elNode.parentNode;
},
_parentNode_Fix : function(elNode){
if(!elNode){return elNode;}
while(elNode.previousSibling){elNode = elNode.previousSibling;}
return elNode.parentNode;
},
_childNodes_Native : function(elNode){
return elNode.childNodes;
},
_childNodes_Fix : function(elNode){
var aResult = null;
var nCount = 0;
if(elNode){
var aResult = [];
elNode = elNode.firstChild;
while(elNode){
aResult[nCount++] = elNode;
elNode=elNode.nextSibling;
}
}
return aResult;
}
}))();
nhn.husky.CorePlugin = jindo.$Class({
name : "CorePlugin",
htLazyLoadRequest_plugins : {},
htLazyLoadRequest_allFiles : {},
htHTMLLoaded : {},
$AFTER_MSG_APP_READY : function(){
this.oApp.exec("EXEC_ON_READY_FUNCTION", []);
},
$ON_ADD_APP_PROPERTY : function(sPropertyName, oProperty){
this.oApp[sPropertyName] = oProperty;
},
$ON_REGISTER_BROWSER_EVENT : function(obj, sEvent, sMsg, aParams, nDelay){
this.oApp.registerBrowserEvent(obj, sEvent, sMsg, aParams, nDelay);
},
$ON_DISABLE_MESSAGE : function(sMsg){
this.oApp.disableMessage(sMsg, true);
},
$ON_ENABLE_MESSAGE : function(sMsg){
this.oApp.disableMessage(sMsg, false);
},
$ON_LOAD_FULL_PLUGIN : function(aFilenames, sClassName, sMsgName, oThisRef, oArguments){
var oPluginRef = oThisRef.$this || oThisRef;
var sFilename = aFilenames[0];
if(!this.htLazyLoadRequest_plugins[sFilename]){
this.htLazyLoadRequest_plugins[sFilename] = {nStatus:1, sContents:""};
}
if(this.htLazyLoadRequest_plugins[sFilename].nStatus === 2){
this.oApp.exec("MSG_FULL_PLUGIN_LOADED", [sFilename, sClassName, sMsgName, oThisRef, oArguments, false]);
}else{
this._loadFullPlugin(aFilenames, sClassName, sMsgName, oThisRef, oArguments, 0);
}
},
_loadFullPlugin : function(aFilenames, sClassName, sMsgName, oThisRef, oArguments, nIdx){
jindo.LazyLoading.load(nhn.husky.SE2M_Configuration.LazyLoad.sJsBaseURI+"/"+aFilenames[nIdx],
jindo.$Fn(function(aFilenames, sClassName, sMsgName, oThisRef, oArguments, nIdx){
var sCurFilename = aFilenames[nIdx];
var sFilename = aFilenames[0];
if(nIdx == aFilenames.length-1){
this.htLazyLoadRequest_plugins[sFilename].nStatus=2;
this.oApp.exec("MSG_FULL_PLUGIN_LOADED", [aFilenames, sClassName, sMsgName, oThisRef, oArguments]);
return;
}
this._loadFullPlugin(aFilenames, sClassName, sMsgName, oThisRef, oArguments, nIdx+1);
}, this).bind(aFilenames, sClassName, sMsgName, oThisRef, oArguments, nIdx),
"utf-8"
);
},
$ON_MSG_FULL_PLUGIN_LOADED : function(aFilenames, sClassName, sMsgName, oThisRef, oArguments, oRes){
var oPluginRef = oThisRef.$this || oThisRef;
var sFilename = aFilenames;
for(var i=0, nLen=oThisRef._huskyFLT.length; i<nLen; i++){
var sLoaderHandlerName = "$BEFORE_"+oThisRef._huskyFLT[i];
var oRemoveFrom = (oThisRef.$this && oThisRef[sLoaderHandlerName])?oThisRef:oPluginRef;
oRemoveFrom[sLoaderHandlerName] = null;
this.oApp.createMessageMap(sLoaderHandlerName);
}
var oPlugin = eval(sClassName+".prototype");
var bAcceptLocalBeforeFirstAgain = false;
if(typeof oPluginRef["$LOCAL_BEFORE_FIRST"] !== "function"){
this.oApp.acceptLocalBeforeFirstAgain(oPluginRef, true);
}
for(var x in oPlugin){
if(oThisRef.$this && (!oThisRef[x] || (typeof oPlugin[x] === "function" && x != "constructor"))){
oThisRef[x] = jindo.$Fn(oPlugin[x], oPluginRef).bind();
}
if(oPlugin[x] && (!oPluginRef[x] || (typeof oPlugin[x] === "function" && x != "constructor"))){
oPluginRef[x] = oPlugin[x];
if(x.match(/^\$(LOCAL|BEFORE|ON|AFTER)_/)){
this.oApp.addToMessageMap(x, oPluginRef);
}
}
}
if(bAcceptLocalBeforeFirstAgain){
this.oApp.acceptLocalBeforeFirstAgain(oPluginRef, true);
}
if(!oThisRef.$this){
this.oApp.exec(sMsgName, oArguments);
}
},
$ON_LOAD_HTML : function(sId){
if(this.htHTMLLoaded[sId]) return;
var elTextarea = jindo.$("_llh_"+sId);
if(!elTextarea) return;
this.htHTMLLoaded[sId] = true;
var elTmp = document.createElement("DIV");
elTmp.innerHTML = elTextarea.value;
while(elTmp.firstChild){
elTextarea.parentNode.insertBefore(elTmp.firstChild, elTextarea);
}
},
$ON_EXEC_ON_READY_FUNCTION : function(){
if(typeof this.oApp.htRunOptions.fnOnAppReady == "function"){this.oApp.htRunOptions.fnOnAppReady();}
}
});
nhn.husky.HuskyRangeManager = jindo.$Class({
name : "HuskyRangeManager",
oWindow : null,
$init : function(win){
this.oWindow = win || window;
},
$BEFORE_MSG_APP_READY : function(){
if(this.oWindow && this.oWindow.tagName == "IFRAME"){
this.oWindow = this.oWindow.contentWindow;
nhn.CurrentSelection.setWindow(this.oWindow);
}
this.oApp.exec("ADD_APP_PROPERTY", ["getSelection", jindo.$Fn(this.getSelection, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getEmptySelection", jindo.$Fn(this.getEmptySelection, this).bind()]);
},
$ON_SET_EDITING_WINDOW : function(oWindow){
this.oWindow = oWindow;
},
getEmptySelection : function(oWindow){
var oHuskyRange = new nhn.HuskyRange(oWindow || this.oWindow);
return oHuskyRange;
},
getSelection : function(oWindow){
this.oApp.exec("RESTORE_IE_SELECTION", []);
var oHuskyRange = this.getEmptySelection(oWindow);
try{
oHuskyRange.setFromSelection();
}catch(e){}
return oHuskyRange;
}
});
nhn.husky.SE2M_Toolbar = jindo.$Class({
name : "SE2M_Toolbar",
toolbarArea : null,
toolbarButton : null,
uiNameTag : "uiName",
nUIStatus : 1,
sUIClassPrefix : "husky_seditor_ui_",
aUICmdMap : null,
elFirstToolbarItem : null,
_assignHTMLElements : function(oAppContainer){
oAppContainer = jindo.$(oAppContainer) || document;
this.rxUI = new RegExp(this.sUIClassPrefix+"([^ ]+)");
this.toolbarArea = jindo.$$.getSingle(".se2_tool", oAppContainer);
this.aAllUI = jindo.$$("[class*=" + this.sUIClassPrefix + "]", this.toolbarArea);
this.elTextTool = jindo.$$.getSingle("div.husky_seditor_text_tool", this.toolbarArea);
this.welToolbarArea = jindo.$Element(this.toolbarArea);
for (var i = 0, nCount = this.aAllUI.length; i < nCount; i++) {
if (this.rxUI.test(this.aAllUI[i].className)) {
var sUIName = RegExp.$1;
if(this.htUIList[sUIName] !== undefined){
continue;
}
this.htUIList[sUIName] = this.aAllUI[i];
this.htWrappedUIList[sUIName] = jindo.$Element(this.htUIList[sUIName]);
}
}
if (jindo.$$.getSingle("DIV.se2_icon_tool") != null) {
this.elFirstToolbarItem = jindo.$$.getSingle("DIV.se2_icon_tool UL.se2_itool1>li>button");
}
},
$LOCAL_BEFORE_FIRST : function(sMsg) {
var aToolItems = jindo.$$(">ul>li[class*=" + this.sUIClassPrefix + "]>button", this.elTextTool);
var nItemLength = aToolItems.length;
this.elFirstToolbarItem = this.elFirstToolbarItem || aToolItems[0];
this.elLastToolbarItem = aToolItems[nItemLength-1];
this.oApp.registerBrowserEvent(this.toolbarArea, "keydown", "NAVIGATE_TOOLBAR", []);
},
$init : function(oAppContainer, htOptions){
this._htOptions = htOptions || {};
this.htUIList = {};
this.htWrappedUIList = {};
this.aUICmdMap = {};
this._assignHTMLElements(oAppContainer);
},
$ON_MSG_APP_READY : function(){
if(this.oApp.bMobile){
this.oApp.registerBrowserEvent(this.toolbarArea, "touchstart", "EVENT_TOOLBAR_TOUCHSTART");
}else{
this.oApp.registerBrowserEvent(this.toolbarArea, "mouseover", "EVENT_TOOLBAR_MOUSEOVER");
this.oApp.registerBrowserEvent(this.toolbarArea, "mouseout", "EVENT_TOOLBAR_MOUSEOUT");
}
this.oApp.registerBrowserEvent(this.toolbarArea, "mousedown", "EVENT_TOOLBAR_MOUSEDOWN");
this.oApp.exec("ADD_APP_PROPERTY", ["getToolbarButtonByUIName", jindo.$Fn(this.getToolbarButtonByUIName, this).bind()]);
var elTool = jindo.$$.getSingle(".se2_tool");
this.oApp.exec("REGISTER_HOTKEY", ["esc", "FOCUS_EDITING_AREA", [], elTool]);
if(this._htOptions.aDisabled){
this._htOptions._sDisabled = "," + this._htOptions.aDisabled.toString() + ",";
this.oApp.exec("DISABLE_UI", [this._htOptions.aDisabled]);
}
},
$ON_NAVIGATE_TOOLBAR : function(weEvent) {
var TAB_KEY_CODE = 9;
if ((weEvent.element == this.elLastToolbarItem) && (weEvent.key().keyCode == TAB_KEY_CODE) ) {
if (weEvent.key().shift) {
} else {
this.elFirstToolbarItem.focus();
weEvent.stopDefault();
}
}
if (weEvent.element == this.elFirstToolbarItem && (weEvent.key().keyCode == TAB_KEY_CODE)) {
if (weEvent.key().shift) {
weEvent.stopDefault();
this.elLastToolbarItem.focus();
}
}
},
$ON_FOCUS_EDITING_AREA : function() {
this.oApp.exec("FOCUS");
},
$ON_TOGGLE_TOOLBAR_ACTIVE_LAYER : function(elLayer, elBtn, sOpenCmd, aOpenArgs, sCloseCmd, aCloseArgs){
this.oApp.exec("TOGGLE_ACTIVE_LAYER", [elLayer, "MSG_TOOLBAR_LAYER_SHOWN", [elLayer, elBtn, sOpenCmd, aOpenArgs], sCloseCmd, aCloseArgs]);
},
$ON_MSG_TOOLBAR_LAYER_SHOWN : function(elLayer, elBtn, aOpenCmd, aOpenArgs){
this.oApp.exec("POSITION_TOOLBAR_LAYER", [elLayer, elBtn]);
if(aOpenCmd){
this.oApp.exec(aOpenCmd, aOpenArgs);
}
},
$ON_SHOW_TOOLBAR_ACTIVE_LAYER : function(elLayer, sCmd, aArgs, elBtn){
this.oApp.exec("SHOW_ACTIVE_LAYER", [elLayer, sCmd, aArgs]);
this.oApp.exec("POSITION_TOOLBAR_LAYER", [elLayer, elBtn]);
},
$ON_ENABLE_UI : function(sUIName){
this._enableUI(sUIName);
},
$ON_DISABLE_UI : function(sUIName){
if(sUIName instanceof Array){
for(var i = 0, sName; (sName = sUIName[i]); i++){
this._disableUI(sName);
}
}else{
this._disableUI(sUIName);
}
},
$ON_SELECT_UI : function(sUIName){
var welUI = this.htWrappedUIList[sUIName];
if(!welUI){
return;
}
welUI.removeClass("hover");
welUI.addClass("active");
},
$ON_DESELECT_UI : function(sUIName){
var welUI = this.htWrappedUIList[sUIName];
if(!welUI){
return;
}
welUI.removeClass("active");
},
$ON_TOGGLE_UI_SELECTED : function(sUIName){
var welUI = this.htWrappedUIList[sUIName];
if(!welUI){
return;
}
if(welUI.hasClass("active")){
welUI.removeClass("active");
}else{
welUI.removeClass("hover");
welUI.addClass("active");
}
},
$ON_ENABLE_ALL_UI : function(htOptions){
if(this.nUIStatus === 1){
return;
}
var sUIName, className;
htOptions = htOptions || {};
var waExceptions = jindo.$A(htOptions.aExceptions || []);
for(sUIName in this.htUIList){
if(sUIName && !waExceptions.has(sUIName)){
this._enableUI(sUIName);
}
}
this.nUIStatus = 1;
},
$ON_DISABLE_ALL_UI : function(htOptions){
if(this.nUIStatus === 2){
return;
}
var sUIName;
htOptions = htOptions || {};
var waExceptions = jindo.$A(htOptions.aExceptions || []);
var bLeavlActiveLayer = htOptions.bLeaveActiveLayer || false;
if(!bLeavlActiveLayer){
this.oApp.exec("HIDE_ACTIVE_LAYER");
}
for(sUIName in this.htUIList){
if(sUIName && !waExceptions.has(sUIName)){
this._disableUI(sUIName);
}
}
this.nUIStatus = 2;
},
$ON_MSG_STYLE_CHANGED : function(sAttributeName, attributeValue){
if(attributeValue === "@^"){
this.oApp.exec("SELECT_UI", [sAttributeName]);
}else{
this.oApp.exec("DESELECT_UI", [sAttributeName]);
}
},
$ON_POSITION_TOOLBAR_LAYER : function(elLayer, htOption){
var nLayerLeft, nLayerRight, nToolbarLeft, nToolbarRight;
elLayer = jindo.$(elLayer);
htOption = htOption || {};
var elBtn = jindo.$(htOption.elBtn);
var sAlign = htOption.sAlign;
var nMargin = -1;
if(!elLayer){
return;
}
if(elBtn && elBtn.tagName && elBtn.tagName == "BUTTON"){
elBtn.parentNode.appendChild(elLayer);
}
var welLayer = jindo.$Element(elLayer);
if(sAlign != "right"){
elLayer.style.left = "0";
nLayerLeft = welLayer.offset().left;
nLayerRight = nLayerLeft + elLayer.offsetWidth;
nToolbarLeft = this.welToolbarArea.offset().left;
nToolbarRight = nToolbarLeft + this.toolbarArea.offsetWidth;
if(nLayerRight > nToolbarRight){
welLayer.css("left", (nToolbarRight-nLayerRight-nMargin)+"px");
}
if(nLayerLeft < nToolbarLeft){
welLayer.css("left", (nToolbarLeft-nLayerLeft+nMargin)+"px");
}
}else{
elLayer.style.right = "0";
nLayerLeft = welLayer.offset().left;
nLayerRight = nLayerLeft + elLayer.offsetWidth;
nToolbarLeft = this.welToolbarArea.offset().left;
nToolbarRight = nToolbarLeft + this.toolbarArea.offsetWidth;
if(nLayerRight > nToolbarRight){
welLayer.css("right", -1*(nToolbarRight-nLayerRight-nMargin)+"px");
}
if(nLayerLeft < nToolbarLeft){
welLayer.css("right", -1*(nToolbarLeft-nLayerLeft+nMargin)+"px");
}
}
},
$ON_EVENT_TOOLBAR_MOUSEOVER : function(weEvent){
if(this.nUIStatus === 2){
return;
}
var aAffectedElements = this._getAffectedElements(weEvent.element);
for(var i=0; i<aAffectedElements.length; i++){
if(!aAffectedElements[i].hasClass("active")){
aAffectedElements[i].addClass("hover");
}
}
},
$ON_EVENT_TOOLBAR_MOUSEOUT : function(weEvent){
if(this.nUIStatus === 2){
return;
}
var aAffectedElements = this._getAffectedElements(weEvent.element);
for(var i=0; i<aAffectedElements.length; i++){
aAffectedElements[i].removeClass("hover");
}
},
$ON_EVENT_TOOLBAR_MOUSEDOWN : function(weEvent){
var elTmp = weEvent.element;
while(elTmp){
if(elTmp.className && elTmp.className.match(/active/) && (elTmp.childNodes.length>2 || elTmp.parentNode.className.match(/se2_pair/))){
return;
}
elTmp = elTmp.parentNode;
}
this.oApp.exec("HIDE_ACTIVE_LAYER_IF_NOT_CHILD", [weEvent.element]);
},
_enableUI : function(sUIName){
if(this._htOptions._sDisabled && this._htOptions._sDisabled.indexOf(","+sUIName+",") > -1){
return;
}
var i, nLen;
this.nUIStatus = 0;
var welUI = this.htWrappedUIList[sUIName];
var elUI = this.htUIList[sUIName];
if(!welUI){
return;
}
welUI.removeClass("off");
var aAllBtns = elUI.getElementsByTagName("BUTTON");
for(i=0, nLen=aAllBtns.length; i<nLen; i++){
aAllBtns[i].disabled = false;
}
var sCmd = "";
if(this.aUICmdMap[sUIName]){
for(i=0; i<this.aUICmdMap[sUIName].length;i++){
sCmd = this.aUICmdMap[sUIName][i];
this.oApp.exec("ENABLE_MESSAGE", [sCmd]);
}
}
},
_disableUI : function(sUIName){
var i, nLen;
this.nUIStatus = 0;
var welUI = this.htWrappedUIList[sUIName];
var elUI = this.htUIList[sUIName];
if(!welUI){
return;
}
welUI.addClass("off");
welUI.removeClass("hover");
var aAllBtns = elUI.getElementsByTagName("BUTTON");
for(i=0, nLen=aAllBtns.length; i<nLen; i++){
aAllBtns[i].disabled = true;
}
var sCmd = "";
if(this.aUICmdMap[sUIName]){
for(i=0; i<this.aUICmdMap[sUIName].length;i++){
sCmd = this.aUICmdMap[sUIName][i];
this.oApp.exec("DISABLE_MESSAGE", [sCmd]);
}
}
},
_getAffectedElements : function(el){
var elLi, welLi;
if(!el.bSE2_MDCancelled){
el.bSE2_MDCancelled = true;
var aBtns = el.getElementsByTagName("BUTTON");
for(var i=0, nLen=aBtns.length; i<nLen; i++){
aBtns[i].onmousedown = function(){return false;};
}
}
if(!el || !el.tagName){ return []; }
if((elLi = el).tagName == "BUTTON"){
if((elLi = elLi.parentNode) && elLi.tagName == "LI" && this.rxUI.test(elLi.className)){
return [jindo.$Element(elLi)];
}
elLi = el;
if((elLi = elLi.parentNode.parentNode) && elLi.tagName == "LI" && (welLi = jindo.$Element(elLi)).hasClass("se2_pair")){
return [welLi, jindo.$Element(el.parentNode)];
}
return [];
}
if((elLi = el).tagName == "SPAN"){
if((elLi = elLi.parentNode.parentNode) && elLi.tagName == "LI" && this.rxUI.test(elLi.className)){
return [jindo.$Element(elLi)];
}
if((elLi = elLi.parentNode) && elLi.tagName == "LI" && this.rxUI.test(elLi.className)){
return [jindo.$Element(elLi)];
}
}
return [];
},
$ON_REGISTER_UI_EVENT : function(sUIName, sEvent, sCmd, aParams){
if(!this.htUIList[sUIName]){
return;
}
var elButton;
if(!this.aUICmdMap[sUIName]){this.aUICmdMap[sUIName] = [];}
this.aUICmdMap[sUIName][this.aUICmdMap[sUIName].length] = sCmd;
elButton = jindo.$$.getSingle('button', this.htUIList[sUIName]);
if(!elButton){return;}
this.oApp.registerBrowserEvent(elButton, sEvent, sCmd, aParams);
},
getToolbarButtonByUIName : function(sUIName){
return jindo.$$.getSingle("BUTTON", this.htUIList[sUIName]);
}
});
nhn.husky.SE_EditingAreaManager = jindo.$Class({
name : "SE_EditingAreaManager",
oActivePlugin : null,
elContentsField : null,
bIsDirty : false,
bAutoResize : false,
$init : function(sDefaultEditingMode, elContentsField, oDimension, fOnBeforeUnload, elAppContainer){
this.sDefaultEditingMode = sDefaultEditingMode;
this.elContentsField = jindo.$(elContentsField);
this._assignHTMLElements(elAppContainer);
this.fOnBeforeUnload = fOnBeforeUnload;
this.oEditingMode = {};
this.elContentsField.style.display = "none";
this.nMinWidth = parseInt((oDimension.nMinWidth || 60), 10);
this.nMinHeight = parseInt((oDimension.nMinHeight || 60), 10);
var oWidth = this._getSize([oDimension.nWidth, oDimension.width, this.elEditingAreaContainer.offsetWidth], this.nMinWidth);
var oHeight = this._getSize([oDimension.nHeight, oDimension.height, this.elEditingAreaContainer.offsetHeight], this.nMinHeight);
this.elEditingAreaContainer.style.width = oWidth.nSize + oWidth.sUnit;
this.elEditingAreaContainer.style.height = oHeight.nSize + oHeight.sUnit;
if(oWidth.sUnit === "px"){
elAppContainer.style.width = (oWidth.nSize + 2) + "px";
}else if(oWidth.sUnit === "%"){
elAppContainer.style.minWidth = this.nMinWidth + "px";
}
},
_getSize : function(aSize, nMin){
var i, nLen, aRxResult, nSize, sUnit, sDefaultUnit = "px";
nMin = parseInt(nMin, 10);
for(i=0, nLen=aSize.length; i<nLen; i++){
if(!aSize[i]){
continue;
}
if(!isNaN(aSize[i])){
nSize = parseInt(aSize[i], 10);
sUnit = sDefaultUnit;
break;
}
aRxResult = /([0-9]+)(.*)/i.exec(aSize[i]);
if(!aRxResult || aRxResult.length < 2 || aRxResult[1] <= 0){
continue;
}
nSize = parseInt(aRxResult[1], 10);
sUnit = aRxResult[2];
if(!sUnit){
sUnit = sDefaultUnit;
}
if(nSize < nMin && sUnit === sDefaultUnit){
nSize = nMin;
}
break;
}
if(!sUnit){
sUnit = sDefaultUnit;
}
if(isNaN(nSize) || (nSize < nMin && sUnit === sDefaultUnit)){
nSize = nMin;
}
return {nSize : nSize, sUnit : sUnit};
},
_assignHTMLElements : function(elAppContainer){
this.elEditingAreaContainer = jindo.$$.getSingle("DIV.husky_seditor_editing_area_container", elAppContainer);
this.toolbarArea = jindo.$$.getSingle(".se2_tool", elAppContainer);
},
$BEFORE_MSG_APP_READY : function(msg){
this.oApp.exec("ADD_APP_PROPERTY", ["version", nhn.husky.SE_EditingAreaManager.version]);
this.oApp.exec("ADD_APP_PROPERTY", ["elEditingAreaContainer", this.elEditingAreaContainer]);
this.oApp.exec("ADD_APP_PROPERTY", ["welEditingAreaContainer", jindo.$Element(this.elEditingAreaContainer)]);
this.oApp.exec("ADD_APP_PROPERTY", ["getEditingAreaHeight", jindo.$Fn(this.getEditingAreaHeight, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getEditingAreaWidth", jindo.$Fn(this.getEditingAreaWidth, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getRawContents", jindo.$Fn(this.getRawContents, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getContents", jindo.$Fn(this.getContents, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getIR", jindo.$Fn(this.getIR, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["setContents", this.setContents]);
this.oApp.exec("ADD_APP_PROPERTY", ["setIR", this.setIR]);
this.oApp.exec("ADD_APP_PROPERTY", ["getEditingMode", jindo.$Fn(this.getEditingMode, this).bind()]);
},
$ON_MSG_APP_READY : function(){
this.htOptions =  this.oApp.htOptions[this.name] || {};
this.sDefaultEditingMode = this.htOptions["sDefaultEditingMode"] || this.sDefaultEditingMode;
this.iframeWindow = this.oApp.getWYSIWYGWindow();
this.oApp.exec("REGISTER_CONVERTERS", []);
this.oApp.exec("CHANGE_EDITING_MODE", [this.sDefaultEditingMode, true]);
this.oApp.exec("LOAD_CONTENTS_FIELD", [false]);
this.oApp.exec("REGISTER_HOTKEY", ["esc", "CLOSE_LAYER_POPUP", [], document]);
if(!!this.fOnBeforeUnload){
window.onbeforeunload = this.fOnBeforeUnload;
}else{
window.onbeforeunload = jindo.$Fn(function(){
if(this.getRawContents() != this.sCurrentRawContents || this.bIsDirty){
return this.oApp.$MSG("SE_EditingAreaManager.onExit");
}
}, this).bind();
}
},
$ON_CLOSE_LAYER_POPUP : function() {
this.oApp.exec("ENABLE_ALL_UI");               
this.oApp.exec("DESELECT_UI", ["helpPopup"]);
this.oApp.exec("HIDE_ALL_DIALOG_LAYER", []);
this.oApp.exec("HIDE_EDITING_AREA_COVER");             
this.oApp.exec("FOCUS");
},
$AFTER_MSG_APP_READY : function(){
this.oApp.exec("UPDATE_RAW_CONTENTS");
if(!!this.oApp.htOptions[this.name] && this.oApp.htOptions[this.name].bAutoResize){
this.bAutoResize = this.oApp.htOptions[this.name].bAutoResize;
}
if(this.oApp.oNavigator.msafari){
this.bAutoResize = true;
}
this.startAutoResize();
},
$ON_LOAD_CONTENTS_FIELD : function(bDontAddUndo){
var sContentsFieldValue = this.elContentsField.value;
sContentsFieldValue = sContentsFieldValue.replace(/^\s+/, "");
this.oApp.exec("SET_CONTENTS", [sContentsFieldValue, bDontAddUndo]);
},
$ON_UPDATE_CONTENTS_FIELD : function(){
this.elContentsField.value = this.oApp.getContents();
this.oApp.exec("UPDATE_RAW_CONTENTS");
},
$ON_UPDATE_RAW_CONTENTS : function(){
this.sCurrentRawContents = this.oApp.getRawContents();
},
$BEFORE_CHANGE_EDITING_MODE : function(sMode){
if(!this.oEditingMode[sMode]){
return false;
}
this.stopAutoResize();
this._oPrevActivePlugin = this.oActivePlugin;
this.oActivePlugin = this.oEditingMode[sMode];
},
$AFTER_CHANGE_EDITING_MODE : function(sMode, bNoFocus){
if(this._oPrevActivePlugin){
var sIR = this._oPrevActivePlugin.getIR();
this.oApp.exec("SET_IR", [sIR]);
this._setEditingAreaDimension();
}
this.startAutoResize();
if(!bNoFocus){
this.oApp.delayedExec("FOCUS", [], 0);
}
},
$ON_SET_IS_DIRTY : function(bIsDirty){
this.bIsDirty = bIsDirty;
},
$ON_FOCUS : function(isPopupOpening){
if(!this.oActivePlugin || typeof this.oActivePlugin.setIR != "function"){
return;
}
if(!!this.iframeWindow && this.iframeWindow.document.hasFocus && !this.iframeWindow.document.hasFocus() && this.oActivePlugin.sMode == "WYSIWYG"){
this.iframeWindow.focus();
}else{
this.oActivePlugin.focus();
}
if(isPopupOpening && this.oApp.bMobile){
return;
}
this.oActivePlugin.focus();
},
$ON_IE_FOCUS : function(){
if(!this.oApp.oNavigator.ie){
return;
}
this.oApp.exec("FOCUS");
},
$ON_SET_CONTENTS : function(sContents, bDontAddUndoHistory){
this.setContents(sContents, bDontAddUndoHistory);
},
$BEFORE_SET_IR : function(sIR, bDontAddUndoHistory){
bDontAddUndoHistory = bDontAddUndoHistory || false;
if(!bDontAddUndoHistory){
this.oApp.exec("RECORD_UNDO_ACTION", ["BEFORE SET CONTENTS", {sSaveTarget:"BODY"}]);
}
},
$ON_SET_IR : function(sIR){
if(!this.oActivePlugin || typeof this.oActivePlugin.setIR != "function"){
return;
}
this.oActivePlugin.setIR(sIR);
},
$AFTER_SET_IR : function(sIR, bDontAddUndoHistory){
bDontAddUndoHistory = bDontAddUndoHistory || false;
if(!bDontAddUndoHistory){
this.oApp.exec("RECORD_UNDO_ACTION", ["AFTER SET CONTENTS", {sSaveTarget:"BODY"}]);
}
},
$ON_REGISTER_EDITING_AREA : function(oEditingAreaPlugin){
this.oEditingMode[oEditingAreaPlugin.sMode] = oEditingAreaPlugin;
if(oEditingAreaPlugin.sMode == 'WYSIWYG'){
this.attachDocumentEvents(oEditingAreaPlugin.oEditingArea);
}
this._setEditingAreaDimension(oEditingAreaPlugin);
},
$ON_MSG_EDITING_AREA_RESIZE_STARTED : function(){
this._isLayerReasonablyShown = false;
var elSelectedUI = jindo.$$.getSingle("ul[class^='se2_itool']>li.active", this.toolbarArea, {oneTimeOffCache : true});
if(elSelectedUI){
var elSelectedUIParent = elSelectedUI.parentNode;
}
if(elSelectedUIParent && (elSelectedUIParent.className == "se2_itool2" || elSelectedUIParent.className == "se2_itool4")){
this._isLayerReasonablyShown = true;
}
this._fitElementInEditingArea(this.elEditingAreaContainer);
this.oApp.exec("STOP_AUTORESIZE_EDITING_AREA");
this.oApp.exec("SHOW_EDITING_AREA_COVER");
this.elEditingAreaContainer.style.overflow = "hidden";
this.iStartingHeight = parseInt(this.elEditingAreaContainer.style.height, 10);
},
$ON_STOP_AUTORESIZE_EDITING_AREA : function(){
if(!this.bAutoResize){
return;
}
this.stopAutoResize();
this.bAutoResize = false;
},
startAutoResize : function(){
if(!this.bAutoResize || !this.oActivePlugin || typeof this.oActivePlugin.startAutoResize != "function"){
return;
}
this.oActivePlugin.startAutoResize();
},
stopAutoResize : function(){
if(!this.bAutoResize || !this.oActivePlugin || typeof this.oActivePlugin.stopAutoResize != "function"){
return;
}
this.oActivePlugin.stopAutoResize();
},
$ON_RESIZE_EDITING_AREA: function(ipNewWidth, ipNewHeight){
if(ipNewWidth !== null && typeof ipNewWidth !== "undefined"){
this._resizeWidth(ipNewWidth, "px");
}
if(ipNewHeight !== null && typeof ipNewHeight !== "undefined"){
this._resizeHeight(ipNewHeight, "px");
}
this._fitElementInEditingArea(this.elResizingBoard);
this._setEditingAreaDimension();
},
_resizeWidth : function(ipNewWidth, sUnit){
var iNewWidth = parseInt(ipNewWidth, 10);
if(iNewWidth < this.nMinWidth){
iNewWidth = this.nMinWidth;
}
if(ipNewWidth){
this.elEditingAreaContainer.style.width = iNewWidth + sUnit;
}
},
_resizeHeight : function(ipNewHeight, sUnit){
var iNewHeight = parseInt(ipNewHeight, 10);
if(iNewHeight < this.nMinHeight){
iNewHeight = this.nMinHeight;
}
if(ipNewHeight){
this.elEditingAreaContainer.style.height = iNewHeight + sUnit;
}
},
$ON_RESIZE_EDITING_AREA_BY : function(ipWidthChange, ipHeightChange){
var iWidthChange = parseInt(ipWidthChange, 10);
var iHeightChange = parseInt(ipHeightChange, 10);
var iWidth;
var iHeight;
if(ipWidthChange !== 0 && this.elEditingAreaContainer.style.width.indexOf("%") === -1){
iWidth = this.elEditingAreaContainer.style.width?parseInt(this.elEditingAreaContainer.style.width, 10)+iWidthChange:null;
}
if(iHeightChange !== 0){
iHeight = this.elEditingAreaContainer.style.height?this.iStartingHeight+iHeightChange:null;
}
if(!ipWidthChange && !iHeightChange){
return;
}
this.oApp.exec("RESIZE_EDITING_AREA", [iWidth, iHeight]);
},
$ON_MSG_EDITING_AREA_RESIZE_ENDED : function(FnMouseDown, FnMouseMove, FnMouseUp){
if(!this._isLayerReasonablyShown){
this.oApp.exec("HIDE_EDITING_AREA_COVER");
}
this.elEditingAreaContainer.style.overflow = "";
this._setEditingAreaDimension();
},
$ON_SHOW_EDITING_AREA_COVER : function(){
if(!this.elResizingBoard){
this.createCoverDiv();
}
this.elResizingBoard.style.display = "block";
},
$ON_HIDE_EDITING_AREA_COVER : function(){
if(!this.elResizingBoard){
return;
}
this.elResizingBoard.style.display = "none";
},
$ON_KEEP_WITHIN_EDITINGAREA : function(elLayer, nHeight){
var nTop = parseInt(elLayer.style.top, 10);
if(nTop + elLayer.offsetHeight > this.oApp.elEditingAreaContainer.offsetHeight){
if(typeof nHeight == "number"){
elLayer.style.top = nTop - elLayer.offsetHeight - nHeight + "px";
}else{
elLayer.style.top = this.oApp.elEditingAreaContainer.offsetHeight - elLayer.offsetHeight + "px";
}
}
var nLeft = parseInt(elLayer.style.left, 10);
if(nLeft + elLayer.offsetWidth > this.oApp.elEditingAreaContainer.offsetWidth){
elLayer.style.left = this.oApp.elEditingAreaContainer.offsetWidth - elLayer.offsetWidth + "px";
}
},
$ON_EVENT_EDITING_AREA_KEYDOWN : function(){
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
},
$ON_EVENT_EDITING_AREA_MOUSEDOWN : function(){
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
},
$ON_EVENT_EDITING_AREA_SCROLL : function(){
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
},
_setEditingAreaDimension : function(oEditingAreaPlugin){
oEditingAreaPlugin = oEditingAreaPlugin || this.oActivePlugin;
this._fitElementInEditingArea(oEditingAreaPlugin.elEditingArea);
},
_fitElementInEditingArea : function(el){
el.style.height = this.elEditingAreaContainer.offsetHeight+"px";
},
attachDocumentEvents : function(doc){
this.oApp.registerBrowserEvent(doc, "click", "EVENT_EDITING_AREA_CLICK");
this.oApp.registerBrowserEvent(doc, "dblclick", "EVENT_EDITING_AREA_DBLCLICK");
this.oApp.registerBrowserEvent(doc, "mousedown", "EVENT_EDITING_AREA_MOUSEDOWN");
this.oApp.registerBrowserEvent(doc, "mousemove", "EVENT_EDITING_AREA_MOUSEMOVE");
this.oApp.registerBrowserEvent(doc, "mouseup", "EVENT_EDITING_AREA_MOUSEUP");
this.oApp.registerBrowserEvent(doc, "mouseout", "EVENT_EDITING_AREA_MOUSEOUT");
this.oApp.registerBrowserEvent(doc, "mousewheel", "EVENT_EDITING_AREA_MOUSEWHEEL");
this.oApp.registerBrowserEvent(doc, "keydown", "EVENT_EDITING_AREA_KEYDOWN");
this.oApp.registerBrowserEvent(doc, "keypress", "EVENT_EDITING_AREA_KEYPRESS");
this.oApp.registerBrowserEvent(doc, "keyup", "EVENT_EDITING_AREA_KEYUP");
this.oApp.registerBrowserEvent(doc, "scroll", "EVENT_EDITING_AREA_SCROLL");
},
createCoverDiv : function(){
this.elResizingBoard = document.createElement("DIV");
this.elEditingAreaContainer.insertBefore(this.elResizingBoard, this.elEditingAreaContainer.firstChild);
this.elResizingBoard.style.position = "absolute";
this.elResizingBoard.style.background = "#000000";
this.elResizingBoard.style.zIndex=100;
this.elResizingBoard.style.border=1;
this.elResizingBoard.style["opacity"] = 0.0;
this.elResizingBoard.style.filter="alpha(opacity=0.0)";
this.elResizingBoard.style["MozOpacity"]=0.0;
this.elResizingBoard.style["-moz-opacity"] = 0.0;
this.elResizingBoard.style["-khtml-opacity"] = 0.0;
this._fitElementInEditingArea(this.elResizingBoard);
this.elResizingBoard.style.width = this.elEditingAreaContainer.offsetWidth+"px";
this.elResizingBoard.style.display = "none";
},
$ON_GET_COVER_DIV : function(sAttr,oReturn){
if(!!this.elResizingBoard) {
oReturn[sAttr] = this.elResizingBoard;
}
},
getIR : function(){
if(!this.oActivePlugin){
return "";
}
return this.oActivePlugin.getIR();
},
setIR : function(sIR, bDontAddUndo){
this.oApp.exec("SET_IR", [sIR, bDontAddUndo]);
},
getRawContents : function(){
if(!this.oActivePlugin){
return "";
}
return this.oActivePlugin.getRawContents();
},
getContents : function(){
var sIR = this.oApp.getIR();
var sContents;
if(this.oApp.applyConverter){
sContents = this.oApp.applyConverter("IR_TO_DB", sIR, this.oApp.getWYSIWYGDocument());
}else{
sContents = sIR;
}
sContents = this._cleanContents(sContents);
return sContents;
},
_cleanContents : function(sContents){
return sContents.replace(new RegExp("(<img [^>]*>)"+unescape("%uFEFF")+"", "ig"), "$1");
},
setContents : function(sContents, bDontAddUndo){
var sIR;
if(this.oApp.applyConverter){
sIR = this.oApp.applyConverter("DB_TO_IR", sContents, this.oApp.getWYSIWYGDocument());
}else{
sIR = sContents;
}
this.oApp.exec("SET_IR", [sIR, bDontAddUndo]);
},
getEditingMode : function(){
return this.oActivePlugin.sMode;
},
getEditingAreaWidth : function(){
return this.elEditingAreaContainer.offsetWidth;
},
getEditingAreaHeight : function(){
return this.elEditingAreaContainer.offsetHeight;
}
});
var nSE2Version = "4259f59";
nhn.husky.SE_EditingAreaManager.version = {
revision : "4259f59",
type : "open",
number : "2.8.2"
};
nhn.husky.SE_EditingArea_WYSIWYG = jindo.$Class({
name : "SE_EditingArea_WYSIWYG",
status : nhn.husky.PLUGIN_STATUS.NOT_READY,
sMode : "WYSIWYG",
iframe : null,
doc : null,
bStopCheckingBodyHeight : false,
bAutoResize : false,
nBodyMinHeight : 0,
nScrollbarWidth : 0,
iLastUndoRecorded : 0,
_nIFrameReadyCount : 50,
bWYSIWYGEnabled : false,
$init : function(iframe){
this.iframe = jindo.$(iframe);
var oAgent = jindo.$Agent().navigator();
if(oAgent.ie){
this.iframe.style.display = "none";
}
this.sBlankPageURL = "smart_editor2_inputarea.html";
this.sBlankPageURL_EmulateIE7 = "smart_editor2_inputarea_ie8.html";
this.aAddtionalEmulateIE7 = [];
this.htOptions = nhn.husky.SE2M_Configuration.SE_EditingAreaManager;
if (this.htOptions) {
this.sBlankPageURL = this.htOptions.sBlankPageURL || this.sBlankPageURL;
this.sBlankPageURL_EmulateIE7 = this.htOptions.sBlankPageURL_EmulateIE7 || this.sBlankPageURL_EmulateIE7;
this.aAddtionalEmulateIE7 = this.htOptions.aAddtionalEmulateIE7 || this.aAddtionalEmulateIE7;
}
this.aAddtionalEmulateIE7.push(8);
this.sIFrameSrc = this.sBlankPageURL;
if(oAgent.ie && jindo.$A(this.aAddtionalEmulateIE7).has(oAgent.nativeVersion)) {
this.sIFrameSrc = this.sBlankPageURL_EmulateIE7;
}
var sIFrameSrc = this.sIFrameSrc,
iframe = this.iframe,
fHandlerSuccess = jindo.$Fn(this.initIframe, this).bind(),
fHandlerFail =jindo.$Fn(function(){this.iframe.src = sIFrameSrc;}, this).bind();
if(!oAgent.ie || (oAgent.version >=9 && !!document.addEventListener)){
iframe.addEventListener("load", fHandlerSuccess, false);
iframe.addEventListener("error", fHandlerFail, false);
}else{
iframe.attachEvent("onload", fHandlerSuccess);
iframe.attachEvent("onerror", fHandlerFail);
}
iframe.src = sIFrameSrc;
this.elEditingArea = iframe;
},
$BEFORE_MSG_APP_READY : function(){
this.oEditingArea = this.iframe.contentWindow.document;
this.oApp.exec("REGISTER_EDITING_AREA", [this]);
this.oApp.exec("ADD_APP_PROPERTY", ["getWYSIWYGWindow", jindo.$Fn(this.getWindow, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getWYSIWYGDocument", jindo.$Fn(this.getDocument, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["isWYSIWYGEnabled", jindo.$Fn(this.isWYSIWYGEnabled, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getRawHTMLContents", jindo.$Fn(this.getRawHTMLContents, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["setRawHTMLContents", jindo.$Fn(this.setRawHTMLContents, this).bind()]);
if (!!this.isWYSIWYGEnabled()) {
this.oApp.exec('ENABLE_WYSIWYG_RULER');
}
this.oApp.registerBrowserEvent(this.getDocument().body, 'paste', 'EVENT_EDITING_AREA_PASTE');
},
$ON_MSG_APP_READY : function(){
if(!this.oApp.hasOwnProperty("saveSnapShot")){
this.$ON_EVENT_EDITING_AREA_MOUSEUP = function(){};
this._recordUndo = function(){};
}
this._bIERangeReset = true;
if(this.oApp.oNavigator.ie){
jindo.$Fn(
function(weEvent){
var oSelection = this.iframe.contentWindow.document.selection;
if(oSelection && oSelection.type.toLowerCase() === 'control' && weEvent.key().keyCode === 8){
this.oApp.exec("EXECCOMMAND", ['delete', false, false]);
weEvent.stop();
}
this._bIERangeReset = false;
}, this
).attach(this.iframe.contentWindow.document, "keydown");
jindo.$Fn(
function(weEvent){
this._oIERange = null;
this._bIERangeReset = true;
}, this
).attach(this.iframe.contentWindow.document.body, "mousedown");
if(!this.getDocument().createRange){
jindo.$Fn(this._onIEBeforeDeactivate, this).attach(this.iframe.contentWindow.document.body, "beforedeactivate");
}
jindo.$Fn(
function(weEvent){
this._bIERangeReset = false;
}, this
).attach(this.iframe.contentWindow.document.body, "mouseup");
}else if(this.oApp.oNavigator.bGPadBrowser){
this.$ON_EVENT_TOOLBAR_TOUCHSTART = function(){
this._oIERange = this.oApp.getSelection().cloneRange();
}
}
this.fnSetBodyHeight = jindo.$Fn(this._setBodyHeight, this).bind();
this.fnCheckBodyChange = jindo.$Fn(this._checkBodyChange, this).bind();
this.fnSetBodyHeight();
this._setScrollbarWidth();
},
_setScrollbarWidth : function(){
var oDocument = this.getDocument(),
elScrollDiv = oDocument.createElement("div");
elScrollDiv.style.width = "100px";
elScrollDiv.style.height = "100px";
elScrollDiv.style.overflow = "scroll";
elScrollDiv.style.position = "absolute";
elScrollDiv.style.top = "-9999px";
oDocument.body.appendChild(elScrollDiv);
this.nScrollbarWidth = elScrollDiv.offsetWidth - elScrollDiv.clientWidth;
oDocument.body.removeChild(elScrollDiv);
},
$AFTER_EVENT_EDITING_AREA_KEYUP : function(oEvent){
if(!this.bAutoResize){
return;
}
var oKeyInfo = oEvent.key();
if((oKeyInfo.keyCode >= 33 && oKeyInfo.keyCode <= 40) || oKeyInfo.alt || oKeyInfo.ctrl || oKeyInfo.keyCode === 16){
return;
}
this._setAutoResize();
},
$AFTER_PASTE_HTML : function(){
if(!this.bAutoResize){
return;
}
this._setAutoResize();
},
startAutoResize : function(){
this.oApp.exec("STOP_CHECKING_BODY_HEIGHT");
this.bAutoResize = true;
var oBrowser = this.oApp.oNavigator;
if(oBrowser.ie && oBrowser.version < 9){
jindo.$Element(this.getDocument().body).css({ "overflow" : "visible" });
}else{
jindo.$Element(this.getDocument().body).css({ "overflowX" : "visible", "overflowY" : "hidden" });
}
this._setAutoResize();
this.nCheckBodyInterval = setInterval(this.fnCheckBodyChange, 500);
this.oApp.exec("START_FLOAT_TOOLBAR");
},
stopAutoResize : function(){
this.bAutoResize = false;
clearInterval(this.nCheckBodyInterval);
this.oApp.exec("STOP_FLOAT_TOOLBAR");
jindo.$Element(this.getDocument().body).css({ "overflow" : "visible", "overflowY" : "visible" });
this.oApp.exec("START_CHECKING_BODY_HEIGHT");
},
_checkBodyChange : function(){
if(!this.bAutoResize){
return;
}
var nBodyLength = this.getDocument().body.innerHTML.length;
if(nBodyLength !== this.nBodyLength){
this.nBodyLength = nBodyLength;
this._setAutoResize();
}
},
_getResizeHeight : function(){
var elBody = this.getDocument().body,
welBody,
nBodyHeight,
aCopyStyle = ['width', 'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'lineHeight', 'letterSpacing', 'textTransform', 'wordSpacing'],
oCss, i;
if(this.oApp.oNavigator.msafari || (!this.oApp.oNavigator.firefox && !this.oApp.oNavigator.safari)){
if(this.oApp.oNavigator.ie && this.oApp.oNavigator.version === 8 && document.documentMode === 8){
jindo.$Element(elBody).css("height", "0px");
}
nBodyHeight = parseInt(elBody.scrollHeight, 10);
if(nBodyHeight < this.nBodyMinHeight){
nBodyHeight = this.nBodyMinHeight;
}
return nBodyHeight;
}
if(!this.elDummy){
this.elDummy = document.createElement('div');
this.elDummy.className = "se2_input_wysiwyg";
this.oApp.elEditingAreaContainer.appendChild(this.elDummy);
this.elDummy.style.cssText = 'position:absolute !important; left:-9999px !important; top:-9999px !important; z-index: -9999 !important; overflow: auto !important;';
this.elDummy.style.height = this.nBodyMinHeight + "px";
}
welBody = jindo.$Element(elBody);
i = aCopyStyle.length;
oCss = {};
while(i--){
oCss[aCopyStyle[i]] = welBody.css(aCopyStyle[i]);
}
if(oCss.lineHeight.indexOf("px") > -1){
oCss.lineHeight = (parseInt(oCss.lineHeight, 10)/parseInt(oCss.fontSize, 10));
}
jindo.$Element(this.elDummy).css(oCss);
this.elDummy.innerHTML = elBody.innerHTML;
nBodyHeight = this.elDummy.scrollHeight;
return nBodyHeight;
},
_setAutoResize : function(){
var elBody = this.getDocument().body,
welBody = jindo.$Element(elBody),
nBodyHeight,
nContainerHeight,
oCurrentStyle,
nStyleSize,
bExpand = false,
oBrowser = this.oApp.oNavigator;
this.nTopBottomMargin = this.nTopBottomMargin || (parseInt(welBody.css("marginTop"), 10) + parseInt(welBody.css("marginBottom"), 10));
this.nBodyMinHeight = this.nBodyMinHeight || (this.oApp.getEditingAreaHeight() - this.nTopBottomMargin);
if((oBrowser.ie && oBrowser.nativeVersion >= 9) || oBrowser.chrome || this.oApp.oNavigator.msafari){
welBody.css("height", "0px");
this.iframe.style.height = "0px";
}
nBodyHeight = this._getResizeHeight();
if(oBrowser.ie){
if(nBodyHeight > this.nBodyMinHeight){
oCurrentStyle = this.oApp.getCurrentStyle();
nStyleSize = this._getStyleSize(oCurrentStyle);
if(nStyleSize < this.nTopBottomMargin){
nStyleSize = this.nTopBottomMargin;
}
nContainerHeight = nBodyHeight + nStyleSize;
nContainerHeight += 18;
bExpand = true;
}else{
nBodyHeight = this.nBodyMinHeight;
nContainerHeight = this.nBodyMinHeight + this.nTopBottomMargin;
}
}else{
if(nBodyHeight > this.nBodyMinHeight){
oCurrentStyle = this.oApp.getCurrentStyle();
nStyleSize = this._getStyleSize(oCurrentStyle);
if(nStyleSize < this.nTopBottomMargin){
nStyleSize = this.nTopBottomMargin;
}
nContainerHeight = nBodyHeight + nStyleSize;
bExpand = true;
}else{
nBodyHeight = this.nBodyMinHeight;
nContainerHeight = this.nBodyMinHeight + this.nTopBottomMargin;
}
}
if(!oBrowser.firefox){
welBody.css("height", nBodyHeight + "px");
}
this.iframe.style.height = nContainerHeight + "px";			
this.oApp.welEditingAreaContainer.height(nContainerHeight);	
if(!this.oApp.oNavigator.msafari){
this.oApp.checkResizeGripPosition(bExpand);
}
},
_getStyleSize : function(oCurrentStyle){
var nResult;
if(oCurrentStyle){
var nLineHeight = oCurrentStyle.lineHeight;
if(nLineHeight && /[^\d\.]/.test(nLineHeight)){
if(/\d/.test(nLineHeight) && /[A-Za-z]/.test(nLineHeight)){
if(/px$/.test(nLineHeight)){
return parseFloat(nLineHeight, 10);
}else if(/pt$/.test(nLineHeight)){
return parseFloat(nLineHeight, 10) * 4 / 3;
}else if(/em$/.test(nLineHeight)){
return parseFloat(nLineHeight, 10) * 16;
}else if(/cm$/.test(nLineHeight)){
return parseFloat(nLineHeight, 10) * 96 / 2.54;
}
}else if(/\d/.test(nLineHeight) && /%/.test(nLineHeight)){
nLineHeight = parseFloat(nLineHeight, 10) * 100;
}else if(!/[^A-Za-z]/.test(nLineHeight)){
nLineHeight = 1.2;
}
}
var sFontSize = oCurrentStyle.fontSize;
if(sFontSize && !/px$/.test(sFontSize)){
if(/pt$/.test(sFontSize)){
sFontSize = parseFloat(sFontSize, 10) * 4 / 3 + "px";
}else if(/em$/.test(sFontSize)){
sFontSize = parseFloat(sFontSize, 10) * 16 + "px";
}else if(/cm$/.test(sFontSize)){
sFontSize = parseFloat(sFontSize, 10) * 96 / 2.54 + "px";
}else if(sFontSize == "medium"){
sFontSize = "16px";
}else{
sFontSize = "16px";
}
}
nResult = parseFloat(sFontSize, 10) * nLineHeight;
}else{
nResult = 12 * 1.5;
}
return nResult;
},
_setBodyHeight : function(){
if( this.bStopCheckingBodyHeight ){
return;
}
var elBody = this.getDocument().body,
welBody = jindo.$Element(elBody),
nMarginTopBottom = parseInt(welBody.css("marginTop"), 10) + parseInt(welBody.css("marginBottom"), 10),
nContainerOffset = this.oApp.getEditingAreaHeight(),
nMinBodyHeight = nContainerOffset - nMarginTopBottom,
nBodyHeight = welBody.height(),
nScrollHeight,
nNewBodyHeight;
this.nTopBottomMargin = nMarginTopBottom;
if(nBodyHeight === 0){
welBody.css("height", nMinBodyHeight + "px");
setTimeout(this.fnSetBodyHeight, 500);
return;
}
var htBrowser = jindo.$Agent().navigator(),
isIE11 = (htBrowser.ie && htBrowser.nativeVersion === 11),
isShrinkingUnnecessary = (this.nBodyHeight_last === nBodyHeight);
if(!(isIE11 && isShrinkingUnnecessary)){
welBody.css("height", "0px");
}

nScrollHeight = parseInt(elBody.scrollHeight, 10);
nNewBodyHeight = (nScrollHeight > nContainerOffset ? nScrollHeight - nMarginTopBottom : nMinBodyHeight);
if(this._isHorizontalScrollbarVisible()){
nNewBodyHeight -= this.nScrollbarWidth;
}
if(!(isIE11 && isShrinkingUnnecessary)){
welBody.css("height", nNewBodyHeight + "px");
}
this.nBodyHeight_last = nNewBodyHeight;

setTimeout(this.fnSetBodyHeight, 500);
},
_isHorizontalScrollbarVisible : function(){
var oDocument = this.getDocument();
if(oDocument.documentElement.clientWidth < oDocument.documentElement.scrollWidth){
return true;
}
return false;
},
$ON_STOP_CHECKING_BODY_HEIGHT :function(){
if(!this.bStopCheckingBodyHeight){
this.bStopCheckingBodyHeight = true;
}
},
$ON_START_CHECKING_BODY_HEIGHT :function(){
if(this.bStopCheckingBodyHeight){
this.bStopCheckingBodyHeight = false;
this.fnSetBodyHeight();
}
},
$ON_IE_CHECK_EXCEPTION_FOR_SELECTION_PRESERVATION : function(){
var oSelection = this.getDocument().selection;
if(oSelection && oSelection.type === "Control"){
this._oIERange = null;
}
},
_onIEBeforeDeactivate : function(wev){
this.oApp.delayedExec("IE_CHECK_EXCEPTION_FOR_SELECTION_PRESERVATION", null, 0);
if(this._oIERange){
return;
}
if(this._bIERangeReset){
return;
}
this._oIERange = this.oApp.getSelection().cloneRange();
},
$ON_CHANGE_EDITING_MODE : function(sMode, bNoFocus){
if(sMode === this.sMode){
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && htBrowser.nativeVersion > 8){
var elFirstChild = jindo.$$.getSingle("DIV.husky_seditor_editing_area_container").childNodes[0];
if((elFirstChild.tagName == "DIV") && (elFirstChild.style.zIndex == 1000)){
elFirstChild.parentNode.removeChild(elFirstChild);
}
}
this.iframe.style.visibility = "visible";
if(this.iframe.style.display != "block"){
this.iframe.style.display = "block";
}
this.oApp.exec("REFRESH_WYSIWYG");
this.oApp.exec("SET_EDITING_WINDOW", [this.getWindow()]);
this.oApp.exec("START_CHECKING_BODY_HEIGHT");
}else{
this.iframe.style.display = "none";
this.iframe.style.visibility = "hidden";
this.oApp.exec("STOP_CHECKING_BODY_HEIGHT");
}
},
$AFTER_CHANGE_EDITING_MODE : function(sMode, bNoFocus){
this._oIERange = null;
},
$ON_REFRESH_WYSIWYG : function(){
if(!jindo.$Agent().navigator().firefox){
return;
}
this._disableWYSIWYG();
this._enableWYSIWYG();
},
$ON_ENABLE_WYSIWYG : function(){
this._enableWYSIWYG();
},
$ON_DISABLE_WYSIWYG : function(){
this._disableWYSIWYG();
},
$ON_IE_HIDE_CURSOR : function(){
if(!this.oApp.oNavigator.ie){
return;
}
this._onIEBeforeDeactivate();
var oSelection = this.oApp.getWYSIWYGDocument().selection;
if(oSelection && oSelection.createRange){
try{
oSelection.empty();
}catch(e){
oSelection = this.oApp.getSelection();
oSelection.select();
oSelection.oBrowserSelection.selectNone();
}
}else{
this.oApp.getEmptySelection().oBrowserSelection.selectNone();
}
},
$AFTER_SHOW_ACTIVE_LAYER : function(){
this.oApp.exec("IE_HIDE_CURSOR");
this.bActiveLayerShown = true;
},
$BEFORE_EVENT_EDITING_AREA_KEYDOWN : function(oEvent){
this._bKeyDown = true;
},
$ON_EVENT_EDITING_AREA_KEYDOWN : function(oEvent){
if(this.oApp.getEditingMode() !== this.sMode){
return;
}
var oKeyInfo = oEvent.key();
if(this.oApp.oNavigator.ie){
switch(oKeyInfo.keyCode){
case 33:
this._pageUp(oEvent);
break;
case 34:
this._pageDown(oEvent);
break;
case 8:	
this._backspace(oEvent);
break;
default:
}
}else if(this.oApp.oNavigator.firefox){
if(oKeyInfo.keyCode === 8){			
this._backspace(oEvent);
}
}
this._recordUndo(oKeyInfo);
},
_backspace : function(weEvent){
var oSelection = this.oApp.getSelection(),
preNode = null;
if(!oSelection.collapsed){
return;
}
preNode = oSelection.getNodeAroundRange(true, false);
if(preNode && preNode.nodeType === 3){
if(/^[\n]*$/.test(preNode.nodeValue)){
preNode = preNode.previousSibling;
}else if(preNode.nodeValue === "\u200B" || preNode.nodeValue === "\uFEFF"){
preNode.nodeValue = "";
}
}
if(!!preNode && preNode.nodeType === 1 && preNode.tagName === "TABLE"){
jindo.$Element(preNode).leave();
weEvent.stop(jindo.$Event.CANCEL_ALL);
}
},
$BEFORE_EVENT_EDITING_AREA_KEYUP : function(oEvent){
if(!this._bKeyDown){
return false;
}
this._bKeyDown = false;
},
$ON_EVENT_EDITING_AREA_MOUSEUP : function(oEvent){
this.oApp.saveSnapShot();
},
$BEFORE_PASTE_HTML : function(){
if(this.oApp.getEditingMode() !== this.sMode){
this.oApp.exec("CHANGE_EDITING_MODE", [this.sMode]);
}
},
$ON_PASTE_HTML : function(sHTML, oPSelection, bNoUndo){
var oSelection, oNavigator, sTmpBookmark,
oStartContainer, aImgChild, elLastImg, elChild, elNextChild;
if(this.oApp.getEditingMode() !== this.sMode){
return;
}
if(!bNoUndo){
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["PASTE HTML"]);
}
oNavigator = jindo.$Agent().navigator();
oSelection = oPSelection || this.oApp.getSelection();
if(oNavigator.ie && oNavigator.nativeVersion == 8 && document.documentMode == 8){
sHTML = sHTML + unescape("%uFEFF");
}
oSelection.pasteHTML(sHTML);
if(!oNavigator.ie){
sTmpBookmark = oSelection.placeStringBookmark();
this.oApp.getWYSIWYGDocument().body.innerHTML = this.oApp.getWYSIWYGDocument().body.innerHTML;
oSelection.moveToBookmark(sTmpBookmark);
oSelection.collapseToEnd();
oSelection.select();
oSelection.removeStringBookmark(sTmpBookmark);
oSelection = this.oApp.getSelection();
if(!!oPSelection){
oPSelection.setRange(oSelection);
}
}else{
oSelection.collapseToEnd();
oSelection.select();
this._oIERange = null;
this._bIERangeReset = false;
}
if(sHTML.indexOf("<img") > -1){
oStartContainer = oSelection.startContainer;
if(oStartContainer.nodeType === 1 && oStartContainer.tagName === "P"){
aImgChild = jindo.$Element(oStartContainer).child(function(v){
return (v.$value().nodeType === 1 && v.$value().tagName === "IMG");
}, 1);
if(aImgChild.length > 0){
elLastImg = aImgChild[aImgChild.length - 1].$value();
elChild = elLastImg.nextSibling;
while(elChild){
elNextChild = elChild.nextSibling;
if (elChild.nodeType === 3 && (elChild.nodeValue === "&nbsp;" || elChild.nodeValue === unescape("%u00A0"))) {
oStartContainer.removeChild(elChild);
}
elChild = elNextChild;
}
}
}
}
if(!bNoUndo){
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["PASTE HTML"]);
}
},
$ON_FOCUS_N_CURSOR : function (bEndCursor, sId){
var el, oSelection;
if(sId && ( el = jindo.$(sId, this.getDocument()) )){
clearTimeout(this._nTimerFocus);
this._nTimerFocus = setTimeout(jindo.$Fn(function(el){
this._scrollIntoView(el);
this.oApp.exec("FOCUS");
}, this).bind(el), 300);
return;
}
oSelection = this.oApp.getSelection();
if(!oSelection.collapsed){
if(bEndCursor){
oSelection.collapseToEnd();
} else {
oSelection.collapseToStart();
}
oSelection.select();
}else if(bEndCursor){
this.oApp.exec("FOCUS");
el = this.getDocument().body;
oSelection.selectNode(el);
oSelection.collapseToEnd();
oSelection.select();
this._scrollIntoView(el);
}else{
this.oApp.exec("FOCUS");
}
},
_getElementVerticalPosition : function(el){
var nTop = 0,
elParent = el,
htPos = {nTop : 0, nBottom : 0};
if(!el){
return htPos;
}
try{
while(elParent) {
nTop += elParent.offsetTop;
elParent = elParent.offsetParent;
}
}catch(e){}
htPos.nTop = nTop;
htPos.nBottom = nTop + jindo.$Element(el).height();
return htPos;
},
_getVisibleVerticalPosition : function(){
var oWindow, oDocument, nVisibleHeight,
htPos = {nTop : 0, nBottom : 0};
oWindow = this.getWindow();
oDocument = this.getDocument();
nVisibleHeight = oWindow.innerHeight ? oWindow.innerHeight : oDocument.documentElement.clientHeight || oDocument.body.clientHeight;
htPos.nTop = oWindow.pageYOffset || oDocument.documentElement.scrollTop;
htPos.nBottom = htPos.nTop + nVisibleHeight;
return htPos;
},
_isElementVisible : function(htElementPos, htVisiblePos){
return (htElementPos.nTop >= htVisiblePos.nTop && htElementPos.nBottom <= htVisiblePos.nBottom);
},
_scrollIntoView : function(el){
var htElementPos = this._getElementVerticalPosition(el),
htVisiblePos = this._getVisibleVerticalPosition(),
nScroll = 0;
if(this._isElementVisible(htElementPos, htVisiblePos)){
return;
}
if((nScroll = htElementPos.nBottom - htVisiblePos.nBottom) > 0){
this.getWindow().scrollTo(0, htVisiblePos.nTop + nScroll);
return;
}
this.getWindow().scrollTo(0, htElementPos.nTop);
},
$BEFORE_MSG_EDITING_AREA_RESIZE_STARTED  : function(){
if(!jindo.$Agent().navigator().ie){
var oSelection = null;
oSelection = this.oApp.getSelection();
this.sBM = oSelection.placeStringBookmark();
}
},
$AFTER_MSG_EDITING_AREA_RESIZE_ENDED : function(FnMouseDown, FnMouseMove, FnMouseUp){
if(this.oApp.getEditingMode() !== this.sMode){
return;
}
this.oApp.exec("REFRESH_WYSIWYG");
if(!jindo.$Agent().navigator().ie){
var oSelection = this.oApp.getEmptySelection();
oSelection.moveToBookmark(this.sBM);
oSelection.select();
oSelection.removeStringBookmark(this.sBM);
}
},
$ON_CLEAR_IE_BACKUP_SELECTION : function(){
this._oIERange = null;
},
$ON_RESTORE_IE_SELECTION : function(){
if(this._oIERange){
try{
this._oIERange.select();
this._oPrevIERange = this._oIERange;
this._oIERange = null;
}catch(e){}
}
},
$ON_EVENT_EDITING_AREA_PASTE : function(oEvent){
this.oApp.delayedExec('EVENT_EDITING_AREA_PASTE_DELAY', [oEvent], 0);
},
$ON_EVENT_EDITING_AREA_PASTE_DELAY : function(weEvent) {
this._replaceBlankToNbsp(weEvent.element);
},
_replaceBlankToNbsp : function(el){
var oNavigator = this.oApp.oNavigator;
if(!oNavigator.ie){
return;
}
if(oNavigator.nativeVersion !== 9 || document.documentMode !== 7) {
return;
}
if(el.nodeType !== 1){
return;
}
if(el.tagName === "BR"){
return;
}
var aEl = jindo.$$("p:empty()", this.oApp.getWYSIWYGDocument().body, { oneTimeOffCache:true });
jindo.$A(aEl).forEach(function(value, index, array) {
value.innerHTML = "&nbsp;";
});
},
_pageUp : function(we){
var nEditorHeight = this._getEditorHeight(),
htPos = jindo.$Document(this.oApp.getWYSIWYGDocument()).scrollPosition(),
nNewTop;
if(htPos.top <= nEditorHeight){
nNewTop = 0;
}else{
nNewTop = htPos.top - nEditorHeight;
}
this.oApp.getWYSIWYGWindow().scrollTo(0, nNewTop);
we.stop();
},
_pageDown : function(we){
var nEditorHeight = this._getEditorHeight(),
htPos = jindo.$Document(this.oApp.getWYSIWYGDocument()).scrollPosition(),
nBodyHeight = this._getBodyHeight(),
nNewTop;
if(htPos.top+nEditorHeight >= nBodyHeight){
nNewTop = nBodyHeight - nEditorHeight;
}else{
nNewTop = htPos.top + nEditorHeight;
}
this.oApp.getWYSIWYGWindow().scrollTo(0, nNewTop);
we.stop();
},
_getEditorHeight : function(){
return this.oApp.elEditingAreaContainer.offsetHeight - this.nTopBottomMargin;
},
_getBodyHeight : function(){
return parseInt(this.getDocument().body.scrollHeight, 10);
},
initIframe : function(){
try {
if (!this.iframe.contentWindow.document || !this.iframe.contentWindow.document.body || this.iframe.contentWindow.document.location.href === 'about:blank'){
throw new Error('Access denied');
}
var sCSSBaseURI = (!!nhn.husky.SE2M_Configuration.SE2M_CSSLoader && nhn.husky.SE2M_Configuration.SE2M_CSSLoader.sCSSBaseURI) ?
nhn.husky.SE2M_Configuration.SE2M_CSSLoader.sCSSBaseURI : "";
if(!!nhn.husky.SE2M_Configuration.SE_EditingAreaManager.sCSSBaseURI){
sCSSBaseURI = nhn.husky.SE2M_Configuration.SE_EditingAreaManager.sCSSBaseURI;
}
if (sCSSBaseURI){
var doc = this.getDocument();
var headNode = doc.getElementsByTagName("head")[0];
var linkNode = doc.createElement('link');
linkNode.type = 'text/css';
linkNode.rel = 'stylesheet';
linkNode.href = sCSSBaseURI + '/smart_editor2_in.css';
linkNode.onload = jindo.$Fn(function(){
if(this.oApp && this.oApp.getEditingMode && this.oApp.getEditingMode() === this.sMode){
this.oApp.exec("RESET_STYLE_STATUS");
}
}, this).bind();
headNode.appendChild(linkNode);
}
this._enableWYSIWYG();
this.status = nhn.husky.PLUGIN_STATUS.READY;
} catch(e) {
if(this._nIFrameReadyCount-- > 0){
setTimeout(jindo.$Fn(this.initIframe, this).bind(), 100);
}else{
throw("iframe for WYSIWYG editing mode can't be initialized. Please check if the iframe document exists and is also accessable(cross-domain issues). ");
}
}
},
getIR : function(){
var sContent = this.iframe.contentWindow.document.body.innerHTML,
sIR;
if(this.oApp.applyConverter){
sIR = this.oApp.applyConverter(this.sMode+"_TO_IR", sContent, this.oApp.getWYSIWYGDocument());
}else{
sIR = sContent;
}
return sIR;
},
setIR : function(sIR){
var sContent,
oNavigator = this.oApp.oNavigator,
tmp_htBrowser = jindo.$Agent().navigator(),
bUnderIE11 = oNavigator.ie && tmp_htBrowser.nativeVersion < 11,
sCursorHolder = bUnderIE11 ? "" : "<br>";
if(this.oApp.applyConverter){
sContent = this.oApp.applyConverter("IR_TO_"+this.sMode, sIR, this.oApp.getWYSIWYGDocument());
}else{
sContent = sIR;
}
if(sContent.replace(/[\r\n\t\s]*/,"") === ""){
if(this.oApp.sLineBreaker !== "BR"){
sCursorHolder = "<p>" + sCursorHolder + "</p>";
}
sContent = sCursorHolder;
}
this.iframe.contentWindow.document.body.innerHTML = sContent;
if(bUnderIE11 && this.oApp.getEditingMode() === this.sMode){
var pNodes = this.oApp.getWYSIWYGDocument().body.getElementsByTagName("P");
for(var i=0, nMax = pNodes.length; i < nMax; i++){
if(pNodes[i].childNodes.length === 1 && pNodes[i].innerHTML === "&nbsp;"){
pNodes[i].innerHTML = '';
}
}
}
},
getRawContents : function(){
return this.iframe.contentWindow.document.body.innerHTML;
},
getRawHTMLContents : function(){
return this.getRawContents();
},
setRawHTMLContents : function(sContents){
this.iframe.contentWindow.document.body.innerHTML = sContents;
},
getWindow : function(){
return this.iframe.contentWindow;
},
getDocument : function(){
return this.iframe.contentWindow.document;
},
focus : function(){
this.getDocument().body.focus();
this.oApp.exec("RESTORE_IE_SELECTION");
},
_recordUndo : function(oKeyInfo){
if(oKeyInfo.keyCode >= 33 && oKeyInfo.keyCode <= 40){
this.oApp.saveSnapShot();
return;
}
if(oKeyInfo.alt || oKeyInfo.ctrl || oKeyInfo.keyCode === 16){
return;
}
if(this.oApp.getLastKey() === oKeyInfo.keyCode){
return;
}
this.oApp.setLastKey(oKeyInfo.keyCode);
if(!oKeyInfo.enter && oKeyInfo.keyCode !== 46 && oKeyInfo.keyCode !== 8){
return;
}
this.oApp.exec("RECORD_UNDO_ACTION", ["KEYPRESS(" + oKeyInfo.keyCode + ")", {bMustBlockContainer:true}]);
},
_enableWYSIWYG : function(){
if (this.iframe.contentWindow.document.body.contentEditable !== null) {
this.iframe.contentWindow.document.body.contentEditable = true;
} else {
this.iframe.contentWindow.document.designMode = "on";
}
this.bWYSIWYGEnabled = true;
if(jindo.$Agent().navigator().firefox){
setTimeout(jindo.$Fn(function(){
this.iframe.contentWindow.document.execCommand('enableInlineTableEditing', false, false);
}, this).bind(), 0);
}
},
_disableWYSIWYG : function(){
if (this.iframe.contentWindow.document.body.contentEditable !== null){
this.iframe.contentWindow.document.body.contentEditable = false;
} else {
this.iframe.contentWindow.document.designMode = "off";
}
this.bWYSIWYGEnabled = false;
},
isWYSIWYGEnabled : function(){
return this.bWYSIWYGEnabled;
}
});
nhn.husky.SE_EditingArea_HTMLSrc = jindo.$Class({
name : "SE_EditingArea_HTMLSrc",
sMode : "HTMLSrc",
bAutoResize : false,
nMinHeight : null,	
$init : function(sTextArea) {
this.elEditingArea = jindo.$(sTextArea);
},
$BEFORE_MSG_APP_READY : function() {
this.oNavigator = jindo.$Agent().navigator();
this.oApp.exec("REGISTER_EDITING_AREA", [this]);
},
$ON_MSG_APP_READY : function() {
if(!!this.oApp.getEditingAreaHeight){
this.nMinHeight = this.oApp.getEditingAreaHeight();
}
},
$ON_CHANGE_EDITING_MODE : function(sMode) {
if (sMode == this.sMode) {
this.elEditingArea.style.display = "block";
this.elEditingArea.style.position = "absolute";
this.elEditingArea.style.top = "0px";
} else {
this.elEditingArea.style.display = "none";
this.elEditingArea.style.position = "";
this.elEditingArea.style.top = "";
}
},
$AFTER_CHANGE_EDITING_MODE : function(sMode, bNoFocus) {
if (sMode == this.sMode && !bNoFocus) {
var o = new TextRange(this.elEditingArea);
o.setSelection(0, 0);
}
},
startAutoResize : function(){
var htOption = {
nMinHeight : this.nMinHeight,
wfnCallback : jindo.$Fn(this.oApp.checkResizeGripPosition, this).bind()
};
if(this.oNavigator.msafari){
htOption.wfnCallback = function(){};
}
this.bAutoResize = true;
this.AutoResizer = new nhn.husky.AutoResizer(this.elEditingArea, htOption);
this.AutoResizer.bind();
},
stopAutoResize : function(){
this.AutoResizer.unbind();
},
getIR : function() {
var sIR = this.getRawContents();
if (this.oApp.applyConverter) {
sIR = this.oApp.applyConverter(this.sMode + "_TO_IR", sIR, this.oApp.getWYSIWYGDocument());
}
return sIR;
},
setIR : function(sIR) {
if(sIR.toLowerCase() === "<br>" || sIR.toLowerCase() === "<p>&nbsp;</p>" || sIR.toLowerCase() === "<p><br></p>" || sIR.toLowerCase() === "<p></p>"){
sIR="";
}
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && htBrowser.nativeVersion == 11 && document.documentMode == 11){
sIR = sIR.replace(/(<br><br>$)/, "");
}
var sContent = sIR;
if (this.oApp.applyConverter) {
sContent = this.oApp.applyConverter("IR_TO_" + this.sMode, sContent, this.oApp.getWYSIWYGDocument());
}
this.setRawContents(sContent);
},
setRawContents : function(sContent) {
if (typeof sContent !== 'undefined') {
this.elEditingArea.value = sContent;
}
},
getRawContents : function() {
return this.elEditingArea.value;
},
focus : function() {
this.elEditingArea.focus();
}
});
if (typeof window.TextRange == 'undefined') { window.TextRange = {}; }
TextRange = function(oEl, oDoc) {
this._o = oEl;
this._oDoc = (oDoc || document);
};
TextRange.prototype.getSelection = function() {
var obj = this._o;
var ret = [-1, -1];
if(isNaN(this._o.selectionStart)) {
obj.focus();
var range = this._oDoc.body.createTextRange();
var rangeField = null;
rangeField = this._oDoc.selection.createRange().duplicate();
range.moveToElementText(obj);
rangeField.collapse(true);
range.setEndPoint("EndToEnd", rangeField);
ret[0] = range.text.length;
rangeField = this._oDoc.selection.createRange().duplicate();
range.moveToElementText(obj);
rangeField.collapse(false);
range.setEndPoint("EndToEnd", rangeField);
ret[1] = range.text.length;
obj.blur();
} else {
ret[0] = obj.selectionStart;
ret[1] = obj.selectionEnd;
}
return ret;
};
TextRange.prototype.setSelection = function(start, end) {
var obj = this._o;
if (typeof end == 'undefined') {
end = start;
}
if (obj.setSelectionRange) {
obj.setSelectionRange(start, end);
} else if (obj.createTextRange) {
var range = obj.createTextRange();
range.collapse(true);
range.moveStart("character", start);
range.moveEnd("character", end - start);
range.select();
obj.blur();
}
};
TextRange.prototype.copy = function() {
var r = this.getSelection();
return this._o.value.substring(r[0], r[1]);
};
TextRange.prototype.paste = function(sStr) {
var obj = this._o;
var sel = this.getSelection();
var value = obj.value;
var pre = value.substr(0, sel[0]);
var post = value.substr(sel[1]);
value = pre + sStr + post;
obj.value = value;
var n = 0;
if (typeof this._oDoc.body.style.maxHeight == "undefined") {
var a = pre.match(/\n/gi);
n = ( a !== null ? a.length : 0 );
}
this.setSelection(sel[0] + sStr.length - n);
};
TextRange.prototype.cut = function() {
var r = this.copy();
this.paste('');
return r;
};
nhn.husky.SE_EditingArea_TEXT = jindo.$Class({
name : "SE_EditingArea_TEXT",
sMode : "TEXT",
sRxConverter : '@[0-9]+@',
bAutoResize : false,
nMinHeight : null,	
$init : function(sTextArea) {
this.elEditingArea = jindo.$(sTextArea);
},
$BEFORE_MSG_APP_READY : function() {
this.oNavigator = jindo.$Agent().navigator();
this.oApp.exec("REGISTER_EDITING_AREA", [this]);
this.oApp.exec("ADD_APP_PROPERTY", ["getTextAreaContents", jindo.$Fn(this.getRawContents, this).bind()]);
},
$ON_MSG_APP_READY : function() {
if(!!this.oApp.getEditingAreaHeight){
this.nMinHeight = this.oApp.getEditingAreaHeight();
}
},
$ON_REGISTER_CONVERTERS : function() {
this.oApp.exec("ADD_CONVERTER", ["IR_TO_TEXT", jindo.$Fn(this.irToText, this).bind()]);
this.oApp.exec("ADD_CONVERTER", ["TEXT_TO_IR", jindo.$Fn(this.textToIr, this).bind()]);
},
$ON_CHANGE_EDITING_MODE : function(sMode) {
if (sMode == this.sMode) {
this.elEditingArea.style.display = "block";
this.elEditingArea.style.position = "absolute";
this.elEditingArea.style.top = "0px";
} else {
this.elEditingArea.style.display = "none";
this.elEditingArea.style.position = "";
this.elEditingArea.style.top = "";
}
},
$AFTER_CHANGE_EDITING_MODE : function(sMode, bNoFocus) {
if (sMode == this.sMode && !bNoFocus) {
var o = new TextRange(this.elEditingArea);
o.setSelection(0, 0);
}
},
irToText : function(sHtml) {
var sContent = sHtml, nIdx = 0;
var aTemp = sContent.match(new RegExp(this.sRxConverter));
if (aTemp !== null) {
sContent = sContent.replace(new RegExp(this.sRxConverter), "");
}
sContent = sContent.replace(/\r/g, '');
sContent = sContent.replace(/[\n|\t]/g, '');
sContent = sContent.replace(/[\v|\f]/g, '');
sContent = sContent.replace(/<p><br><\/p>/gi, '\n');
sContent = sContent.replace(/<P>&nbsp;<\/P>/gi, '\n');
sContent = sContent.replace(/<br(\s)*\/?>/gi, '\n');
sContent = sContent.replace(/<br(\s[^\/]*)?>/gi, '\n');
sContent = sContent.replace(/<\/p(\s[^\/]*)?>/gi, '\n');
sContent = sContent.replace(/<\/li(\s[^\/]*)?>/gi, '\n');
sContent = sContent.replace(/<\/tr(\s[^\/]*)?>/gi, '\n');
nIdx = sContent.lastIndexOf('\n');
if (nIdx > -1 && sContent.substring(nIdx) == '\n') {
sContent = sContent.substring(0, nIdx);
}
sContent = jindo.$S(sContent).stripTags().toString();
sContent = this.unhtmlSpecialChars(sContent);
if (aTemp !== null) {
sContent = aTemp[0] + sContent;
}
return sContent;
},
textToIr : function(sHtml) {
if (!sHtml) {
return;
}
var sContent = sHtml, aTemp = null;
aTemp = sContent.match(new RegExp(this.sRxConverter));
if (aTemp !== null) {
sContent = sContent.replace(aTemp[0], "");
}
sContent = this.htmlSpecialChars(sContent);
sContent = this._addLineBreaker(sContent);
if (aTemp !== null) {
sContent = aTemp[0] + sContent;
}
return sContent;
},
_addLineBreaker : function(sContent){
if(this.oApp.sLineBreaker === "BR"){
return sContent.replace(/\r?\n/g, "<BR>");
}
var oContent = new StringBuffer(),
aContent = sContent.split('\n'),
aContentLng = aContent.length,
sTemp = "";
for (var i = 0; i < aContentLng; i++) {
sTemp = jindo.$S(aContent[i]).trim().$value();
if (i === aContentLng -1 && sTemp === "") {
break;
}
if (sTemp !== null && sTemp !== "") {
oContent.append('<P>');
oContent.append(aContent[i]);
oContent.append('</P>');
} else {
if (!jindo.$Agent().navigator().ie) {
oContent.append('<P><BR></P>');
} else {
oContent.append('<P>&nbsp;<\/P>');
}
}
}
return oContent.toString();
},
startAutoResize : function(){
var htOption = {
nMinHeight : this.nMinHeight,
wfnCallback : jindo.$Fn(this.oApp.checkResizeGripPosition, this).bind()
};
if(this.oNavigator.msafari){
htOption.wfnCallback = function(){};
}
this.bAutoResize = true;
this.AutoResizer = new nhn.husky.AutoResizer(this.elEditingArea, htOption);
this.AutoResizer.bind();
},
stopAutoResize : function(){
this.AutoResizer.unbind();
},
getIR : function() {
var sIR = this.getRawContents();
if (this.oApp.applyConverter) {
sIR = this.oApp.applyConverter(this.sMode + "_TO_IR", sIR, this.oApp.getWYSIWYGDocument());
}
return sIR;
},
setIR : function(sIR) {
var sContent = sIR;
if (this.oApp.applyConverter) {
sContent = this.oApp.applyConverter("IR_TO_" + this.sMode, sContent, this.oApp.getWYSIWYGDocument());
}
this.setRawContents(sContent);
},
setRawContents : function(sContent) {
if (typeof sContent !== 'undefined') {
this.elEditingArea.value = sContent;
}
},
getRawContents : function() {
return this.elEditingArea.value;
},
focus : function() {
this.elEditingArea.focus();
},
htmlSpecialChars : function(sText) {
return sText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;');
},
unhtmlSpecialChars : function(sText) {
return sText.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&');
}
});
nhn.husky.SE2M_EditingAreaRuler = jindo.$Class({
name : 'SE2M_EditingAreaRuler',
$init : function(elAppContainer) {
this._assignHTMLElements(elAppContainer);
this.htConfig = (nhn.husky.SE2M_Configuration.SE2M_EditingAreaRuler || {});
},
_assignHTMLElements : function(elAppContainer) {
this.elEditingAreaRuler = jindo.$$.getSingle('DIV.se2_editor_mark', elAppContainer);
},
_adjustWysiwygWidth : function() {
var welWysiwygBody = jindo.$Element(this.oApp.getWYSIWYGDocument().body);
if (!welWysiwygBody || !this.htConfig[this.nRulerWidth]) { return; }
var sStyle = '{' + this.htConfig[this.nRulerWidth].sStyle.replace(/;/ig, ',').replace(/\"/ig, '') + '}';
var oStyle = jindo.$Json(sStyle.replace(/(\w+)\s?:\s?([\w\s]*[^,}])/ig, '$1:"$2"'));
welWysiwygBody.css(oStyle.toObject());
var welEditingAreaRuler = jindo.$Element(this.elEditingAreaRuler);
var oRulerStyle = { "width" : welWysiwygBody.css('width'), "marginLeft" : welWysiwygBody.css('marginLeft'), "top" : welWysiwygBody.css('marginTop') };
welEditingAreaRuler.css(oRulerStyle);
if (!!this.bUse) {
welEditingAreaRuler.show();
} else {
welEditingAreaRuler.hide();
}
},
$ON_ENABLE_WYSIWYG_RULER : function() {
if (!!this.oApp.htOptions[this.name]) {
this.bUse = (this.oApp.htOptions[this.name].bUse || false);
this.nRulerWidth = (this.oApp.htOptions[this.name].nRulerWidth || 0);
}
if (!this.elEditingAreaRuler || 0 >= this.nRulerWidth) { return; }
this._adjustWysiwygWidth();
},
$ON_CHANGE_EDITING_MODE : function(sMode) {
if (!this.elEditingAreaRuler) { return; }
if ('WYSIWYG' === sMode && !!this.bUse && !!this.htConfig[this.nRulerWidth]) {
jindo.$Element(this.elEditingAreaRuler).show();
} else {
jindo.$Element(this.elEditingAreaRuler).hide();
}
}
});
nhn.husky.SE_EditingAreaVerticalResizer = jindo.$Class({
name : "SE_EditingAreaVerticalResizer",
oResizeGrip : null,
sCookieNotice : "bHideResizeNotice",
nEditingAreaMinHeight : null,
htConversionMode : null,
$init : function(elAppContainer, htConversionMode){
this.htConversionMode = htConversionMode;
this._assignHTMLElements(elAppContainer);
},
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["isUseVerticalResizer", jindo.$Fn(this.isUseVerticalResizer, this).bind()]);
},
$ON_MSG_APP_READY : function(){
if(this.oApp.bMobile){
this.oResizeGrip.disabled = true;
this.oResizeGrip.style.height = '0';
}else{
this.oApp.exec("REGISTER_HOTKEY", ["shift+esc", "FOCUS_RESIZER"]);
if(this.isUseVerticalResizer()){
this.oResizeGrip.style.display = 'block';
if(!!this.welNoticeLayer && !Number(jindo.$Cookie().get(this.sCookieNotice))){
this.welNoticeLayer.delegate("click", "BUTTON.bt_clse", jindo.$Fn(this._closeNotice, this).bind());
this.welNoticeLayer.show();
}
this.$FnMouseDown = jindo.$Fn(this._mousedown, this);
this.$FnMouseMove = jindo.$Fn(this._mousemove, this);
this.$FnMouseUp = jindo.$Fn(this._mouseup, this);
this.$FnMouseOver = jindo.$Fn(this._mouseover, this);
this.$FnMouseOut = jindo.$Fn(this._mouseout, this);
this.$FnMouseDown.attach(this.oResizeGrip, "mousedown");
this.$FnMouseOver.attach(this.oResizeGrip, "mouseover");
this.$FnMouseOut.attach(this.oResizeGrip, "mouseout");
}else{
this.oResizeGrip.style.display = 'none';
if(!this.oApp.isUseModeChanger()){
this.elModeToolbar.style.display = "none";
}
}
}
this.oApp.exec("ADD_APP_PROPERTY", ["checkResizeGripPosition", jindo.$Fn(this.checkResizeGripPosition, this).bind()]);
if(!!this.oApp.getEditingAreaHeight){
this.nEditingAreaMinHeight = this.oApp.getEditingAreaHeight();
}
},
isUseVerticalResizer : function(){
return (typeof(this.htConversionMode) === 'undefined' || typeof(this.htConversionMode.bUseVerticalResizer) === 'undefined' || this.htConversionMode.bUseVerticalResizer === true) ? true : false;
},
checkResizeGripPosition : function(bExpand){
var oDocument = jindo.$Document();
var nGap = (jindo.$Element(this.oResizeGrip).offset().top - oDocument.scrollPosition().top + 25) - oDocument.clientSize().height;
if(nGap <= 0){
return;
}
if(bExpand){
if(this.nEditingAreaMinHeight > this.oApp.getEditingAreaHeight() - nGap){
nGap = (-1) * (this.nEditingAreaMinHeight - this.oApp.getEditingAreaHeight());
}
this.oApp.exec("MSG_EDITING_AREA_RESIZE_STARTED");
this.oApp.exec("RESIZE_EDITING_AREA_BY", [0, (-1) * nGap]);
this.oApp.exec("MSG_EDITING_AREA_RESIZE_ENDED");
}
this.oApp.exec("STOP_AUTORESIZE_EDITING_AREA");
},
$ON_FOCUS_RESIZER : function(){
this.oApp.exec("IE_HIDE_CURSOR");
this.oResizeGrip.focus();
},
_assignHTMLElements : function(elAppContainer, htConversionMode){
this.oResizeGrip = jindo.$$.getSingle("BUTTON.husky_seditor_editingArea_verticalResizer", elAppContainer);
this.elModeToolbar = jindo.$$.getSingle("DIV.se2_conversion_mode", elAppContainer);
this.welNoticeLayer = jindo.$Element(jindo.$$.getSingle("DIV.husky_seditor_resize_notice", elAppContainer));
this.welConversionMode = jindo.$Element(this.oResizeGrip.parentNode);
},
_mouseover : function(oEvent){
oEvent.stopBubble();
this.welConversionMode.addClass("controller_on");
},
_mouseout : function(oEvent){
oEvent.stopBubble();
this.welConversionMode.removeClass("controller_on");
},
_mousedown : function(oEvent){
this.iStartHeight = oEvent.pos().clientY;
this.iStartHeightOffset = oEvent.pos().layerY;
this.$FnMouseMove.attach(document, "mousemove");
this.$FnMouseUp.attach(document, "mouseup");
this.iStartHeight = oEvent.pos().clientY;
this.oApp.exec("HIDE_ACTIVE_LAYER");
this.oApp.exec("HIDE_ALL_DIALOG_LAYER");
this.oApp.exec("MSG_EDITING_AREA_RESIZE_STARTED", [this.$FnMouseDown, this.$FnMouseMove, this.$FnMouseUp]);
},
_mousemove : function(oEvent){
var iHeightChange = oEvent.pos().clientY - this.iStartHeight;
this.oApp.exec("RESIZE_EDITING_AREA_BY", [0, iHeightChange]);
},
_mouseup : function(oEvent){
this.$FnMouseMove.detach(document, "mousemove");
this.$FnMouseUp.detach(document, "mouseup");
this.oApp.exec("MSG_EDITING_AREA_RESIZE_ENDED", [this.$FnMouseDown, this.$FnMouseMove, this.$FnMouseUp]);
},
_closeNotice : function(){
this.welNoticeLayer.hide();
jindo.$Cookie().set(this.sCookieNotice, 1, 365*10);
}
});
nhn.husky.SE_WYSIWYGEnterKey = jindo.$Class({
name : "SE_WYSIWYGEnterKey",
$init : function(sLineBreaker){
if(sLineBreaker == "BR"){
this.sLineBreaker = "BR";
}else{
this.sLineBreaker = "P";
}
this.htBrowser = jindo.$Agent().navigator();
if(this.htBrowser.opera && this.sLineBreaker == "P"){
this.$ON_MSG_APP_READY = function(){};
}
if(this.htBrowser.ie){
this._addCursorHolder = this._addCursorHolderSpace;
}else{
this._addExtraCursorHolder = function(){};
this._addBlankText = function(){};
}
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["sLineBreaker", this.sLineBreaker]);
this.oSelection = this.oApp.getEmptySelection();
this.tmpTextNode = this.oSelection._document.createTextNode(unescape("%u00A0"));
jindo.$Fn(this._onKeyDown, this).attach(this.oApp.getWYSIWYGDocument(), "keydown");
},
_onKeyDown : function(oEvent){
var oKeyInfo = oEvent.key();
if(oKeyInfo.shift){
return;
}
if(oKeyInfo.enter){
if(this.sLineBreaker == "BR"){
this._insertBR(oEvent);
}else{
this._wrapBlock(oEvent);
}
}
},
$ON_REGISTER_CONVERTERS : function(){
this.oApp.exec("ADD_CONVERTER", ["IR_TO_DB", jindo.$Fn(this.onIrToDB, this).bind()]);
},
onIrToDB : function(sHTML){
var sContents = sHTML,
rxRegEx = /<br(\s[^>]*)?\/?>((?:<\/span>)?<\/p>)/gi,
rxRegExWhitespace = /(<p[^>]*>)(?:[\s^>]*)(<\/p>)/gi;
sContents = sContents.replace(rxRegEx, "&nbsp;$2");
sContents = sContents.replace(rxRegExWhitespace, "$1&nbsp;$2");
return sContents;
},
_addBlankText : function(oSelection){
var oNodes = oSelection.getNodes(),
i, nLen, oNode, oNodeChild, tmpTextNode;
for(i=0, nLen=oNodes.length; i<nLen; i++){
oNode = oNodes[i];
if(oNode.nodeType !== 1 || oNode.tagName !== "SPAN"){
continue;
}
if(oNode.id.indexOf(oSelection.HUSKY_BOOMARK_START_ID_PREFIX) > -1 ||
oNode.id.indexOf(oSelection.HUSKY_BOOMARK_END_ID_PREFIX) > -1){
continue;
}
oNodeChild = oNode.firstChild;
if(!oNodeChild ||
(oNodeChild.nodeType == 3 && nhn.husky.SE2M_Utils.isBlankTextNode(oNodeChild)) ||
(oNodeChild.nodeType == 1 && oNode.childNodes.length == 1 &&
(oNodeChild.id.indexOf(oSelection.HUSKY_BOOMARK_START_ID_PREFIX) > -1 || oNodeChild.id.indexOf(oSelection.HUSKY_BOOMARK_END_ID_PREFIX) > -1))){
tmpTextNode = oSelection._document.createTextNode(unescape("%uFEFF"));
oNode.appendChild(tmpTextNode);
}
}
},
_addCursorHolder : function(elWrapper){
var elStyleOnlyNode = elWrapper;
if(elWrapper.innerHTML == "" || (elStyleOnlyNode = this._getStyleOnlyNode(elWrapper))){
elStyleOnlyNode.innerHTML = "<br>";
}
if(!elStyleOnlyNode){
elStyleOnlyNode = this._getStyleNode(elWrapper);
}
return elStyleOnlyNode;
},
_addCursorHolderSpace : function(elWrapper){
var elNode;
this._addSpace(elWrapper);
elNode = this._getStyleNode(elWrapper);
if(elNode.innerHTML == "" && elNode.nodeName.toLowerCase() != "param"){
try{
elNode.innerHTML = unescape("%uFEFF");
}catch(e) {
}
}
return elNode;
},
_getBlockEndNode : function(oStart, oEnd){
if(!oStart){
return oEnd;
}else if(oStart.nodeName === "BR"){
return oStart;
}else if(oStart === oEnd){
return oEnd;
}else{
return this._getBlockEndNode(oStart.nextSibling, oEnd);
}
},
_convertHeadSpace : function(elBookmark){
var elNext;
if(elBookmark && (elNext = elBookmark.nextSibling) && elNext.nodeType === 3){
var sText = elNext.nodeValue, sSpaces = "";
for(var i = 0, ch;(ch = sText[i]); i++){
if(ch !== "\u0020"){
break;
}
sSpaces += "\u00A0";
}
if(i > 0){
elNext.nodeValue = sSpaces + sText.substring(i);
}
}
},
_wrapBlock : function(oEvent, sWrapperTagName){
var oSelection = this.oApp.getSelection(),
sBM = oSelection.placeStringBookmark(),
oLineInfo = oSelection.getLineInfo(),
oStart = oLineInfo.oStart,
oEnd = oLineInfo.oEnd,
oSWrapper,
oEWrapper,
elStyleOnlyNode;
if(!oStart.bParentBreak || oSelection.rxBlockContainer.test(oStart.oLineBreaker.tagName)){
oEvent.stop();
oSelection.deleteContents();
if(!!oStart.oNode.parentNode && oStart.oNode.parentNode.nodeType !== 11){
oSWrapper = this.oApp.getWYSIWYGDocument().createElement(this.sLineBreaker);
oSelection.moveToBookmark(sBM);
oSelection.setStartBefore(oStart.oNode);
this._addBlankText(oSelection);
oSelection.surroundContents(oSWrapper);
oSelection.collapseToEnd();
oEWrapper = this.oApp.getWYSIWYGDocument().createElement(this.sLineBreaker);
var oEndNode = this._getBlockEndNode(oStart.oNode, oEnd.oNode);
if(oEndNode === oStart.oNode){
oEndNode = oEnd.oNode;
}
oSelection.setEndAfter(oEndNode);
this._addBlankText(oSelection);
oSelection.surroundContents(oEWrapper);
oSelection.moveToStringBookmark(sBM, true);
oSelection.collapseToEnd();				
oSelection.removeStringBookmark(sBM);
oSelection.select();
if(oEWrapper.lastChild !== null && oEWrapper.lastChild.tagName == "BR"){
oEWrapper.removeChild(oEWrapper.lastChild);
}
elStyleOnlyNode = this._addCursorHolder(oEWrapper);
if(oEWrapper.nextSibling && oEWrapper.nextSibling.tagName == "BR"){
oEWrapper.parentNode.removeChild(oEWrapper.nextSibling);
}
oSelection.selectNodeContents(elStyleOnlyNode);
oSelection.collapseToStart();
oSelection.select();
this.oApp.exec("CHECK_STYLE_CHANGE");
sBM = oSelection.placeStringBookmark();
setTimeout(jindo.$Fn(function(sBM){
var elBookmark = oSelection.getStringBookmark(sBM);
if(!elBookmark){return;}
oSelection.moveToStringBookmark(sBM);
oSelection.select();
oSelection.removeStringBookmark(sBM);
}, this).bind(sBM), 0);
return;
}
}
var elBookmark = oSelection.getStringBookmark(sBM, true);
if(this.htBrowser.firefox){
if(elBookmark && elBookmark.nextSibling && elBookmark.nextSibling.tagName == "IFRAME"){
setTimeout(jindo.$Fn(function(sBM){
var elBookmark = oSelection.getStringBookmark(sBM);
if(!elBookmark){return;}
oSelection.moveToStringBookmark(sBM);
oSelection.select();
oSelection.removeStringBookmark(sBM);
}, this).bind(sBM), 0);
}else{
setTimeout(jindo.$Fn(function(sBM){
var elBookmark = oSelection.getStringBookmark(sBM, true);
if(!elBookmark){return;}
this._convertHeadSpace(elBookmark);
oSelection.removeStringBookmark(sBM);
}, this).bind(sBM), 0);
}
}else if(this.htBrowser.ie){
var elParentNode = elBookmark.parentNode,
bAddUnderline = false,
bAddLineThrough = false;
if(!elBookmark || !elParentNode){
oSelection.removeStringBookmark(sBM);
return;
}
oSelection.removeStringBookmark(sBM);
bAddUnderline = (elParentNode.tagName === "U" || nhn.husky.SE2M_Utils.findAncestorByTagName("U", elParentNode) !== null);
bAddLineThrough = (elParentNode.tagName === "S" || elParentNode.tagName === "STRIKE" ||
(nhn.husky.SE2M_Utils.findAncestorByTagName("S", elParentNode) !== null && nhn.husky.SE2M_Utils.findAncestorByTagName("STRIKE", elParentNode) !== null));
if(bAddUnderline || bAddLineThrough){
setTimeout(jindo.$Fn(this._addTextDecorationTag, this).bind(bAddUnderline, bAddLineThrough), 0);
return;
}
setTimeout(jindo.$Fn(this._addExtraCursorHolder, this).bind(elParentNode), 0);
}else{
this._convertHeadSpace(elBookmark);
oSelection.removeStringBookmark(sBM);
}
},
_addExtraCursorHolder : function(elUpperNode){
var oNodeChild,
oPrevChild,
elHtml;
elUpperNode = this._getStyleOnlyNode(elUpperNode);
if(!!elUpperNode && elUpperNode.tagName === "SPAN"){
oNodeChild = elUpperNode.lastChild;
while(!!oNodeChild){
oPrevChild = oNodeChild.previousSibling;
if(oNodeChild.nodeType !== 3){
oNodeChild = oPrevChild;
continue;
}
if(nhn.husky.SE2M_Utils.isBlankTextNode(oNodeChild)){
oNodeChild.parentNode.removeChild(oNodeChild);
}
oNodeChild = oPrevChild;
}
elHtml = elUpperNode.innerHTML;
if(elHtml.replace("\u200B","").replace("\uFEFF","") === ""){
elUpperNode.innerHTML = "\u200B";
}
}
var oSelection = this.oApp.getSelection(),
sBM,
elLowerNode,
elParent;
if(!oSelection.collapsed){
return;
}
oSelection.fixCommonAncestorContainer();
elLowerNode = oSelection.commonAncestorContainer;
if(!elLowerNode){
return;
}
elLowerNode = oSelection._getVeryFirstRealChild(elLowerNode);
if(elLowerNode.nodeType === 3){
elLowerNode = elLowerNode.parentNode;
}
if(!elLowerNode || elLowerNode.tagName !== "SPAN"){
return;
}
elHtml = elLowerNode.innerHTML;
if(elHtml.replace("\u200B","").replace("\uFEFF","") === ""){
elLowerNode.innerHTML = "\u200B";
}
oSelection.selectNodeContents(elLowerNode);
oSelection.collapseToStart();
oSelection.select();
},
_addSpace : function(elNode){
var tmpTextNode, elChild, elNextChild, bHasNBSP, aImgChild, elLastImg;
if(!elNode){
return;
}
if(elNode.nodeType === 3){
return elNode.parentNode;
}
if(elNode.tagName !== "P"){
return elNode;
}
aImgChild = jindo.$Element(elNode).child(function(v){
return (v.$value().nodeType === 1 && v.$value().tagName === "IMG");
}, 1);
if(aImgChild.length > 0){
elLastImg = aImgChild[aImgChild.length - 1].$value();
elChild = elLastImg.nextSibling;
while(elChild){
elNextChild = elChild.nextSibling;
if (elChild.nodeType === 3 && (elChild.nodeValue === "&nbsp;" || elChild.nodeValue === unescape("%u00A0") || elChild.nodeValue === "\u200B")) {
elNode.removeChild(elChild);
}
elChild = elNextChild;
}
return elNode;
}
elChild = elNode.firstChild;
elNextChild = elChild;
bHasNBSP = false;
while(elChild){
elNextChild = elChild.nextSibling;
if(elChild.nodeType === 3){
if(elChild.nodeValue === unescape("%uFEFF")){
elNode.removeChild(elChild);
}
if(!bHasNBSP && (elChild.nodeValue === "&nbsp;" || elChild.nodeValue === unescape("%u00A0") || elChild.nodeValue === "\u200B")){
bHasNBSP = true;
}
}
elChild = elNextChild;
}
if(!bHasNBSP){
tmpTextNode = this.tmpTextNode.cloneNode();
elNode.appendChild(tmpTextNode);
}
return elNode;
},
_addTextDecorationTag : function(bAddUnderline, bAddLineThrough){
var oTargetNode, oNewNode,
oSelection = this.oApp.getSelection();
if(!oSelection.collapsed){
return;
}
oTargetNode = oSelection.startContainer;
while(oTargetNode){
if(oTargetNode.nodeType === 3){
oTargetNode = nhn.DOMFix.parentNode(oTargetNode);
break;
}
if(!oTargetNode.childNodes || oTargetNode.childNodes.length === 0){
break;
}
oTargetNode = oTargetNode.firstChild;
}
if(!oTargetNode){
return;
}
if(oTargetNode.tagName === "U" || oTargetNode.tagName === "S" || oTargetNode.tagName === "STRIKE"){
return;
}
if(bAddUnderline){
oNewNode = oSelection._document.createElement("U");
oTargetNode.appendChild(oNewNode);
oTargetNode = oNewNode;
}
if(bAddLineThrough){
oNewNode = oSelection._document.createElement("STRIKE");
oTargetNode.appendChild(oNewNode);
}
oNewNode.innerHTML = "\u200B";
oSelection.selectNodeContents(oNewNode);
oSelection.collapseToEnd();
oSelection.select();
},
_getStyleNode : function(elNode){
while(elNode.firstChild && this.oSelection._isBlankTextNode(elNode.firstChild)){
elNode.removeChild(elNode.firstChild);
}
var elFirstChild = elNode.firstChild;
if(!elFirstChild){
return elNode;
}
if(elFirstChild.nodeType === 3 ||
(elFirstChild.nodeType === 1 &&
(elFirstChild.tagName == "IMG" || elFirstChild.tagName == "BR" || elFirstChild.tagName == "HR" || elFirstChild.tagName == "IFRAME"))){
return elNode;
}
return this._getStyleNode(elNode.firstChild);
},
_getStyleOnlyNode : function(elNode){
if(!elNode){
return null;
}
if(!elNode.insertBefore){
return null;
}
if(elNode.tagName == "IMG" || elNode.tagName == "BR" || elNode.tagName == "HR" || elNode.tagName == "IFRAME"){
return null;
}
while(elNode.firstChild && this.oSelection._isBlankTextNode(elNode.firstChild)){
elNode.removeChild(elNode.firstChild);
}
if(elNode.childNodes.length>1){
return null;
}
if(!elNode.firstChild){
return elNode;
}
if(elNode.firstChild.nodeType === 3){
return nhn.husky.SE2M_Utils.isBlankTextNode(elNode.firstChild) ? elNode : null;
}
return this._getStyleOnlyNode(elNode.firstChild);
},
_insertBR : function(oEvent){
oEvent.stop();
var oSelection = this.oApp.getSelection();
var elBR = this.oApp.getWYSIWYGDocument().createElement("BR");
oSelection.insertNode(elBR);
oSelection.selectNode(elBR);
oSelection.collapseToEnd();
if(!this.htBrowser.ie){
var oLineInfo = oSelection.getLineInfo();
var oEnd = oLineInfo.oEnd;
if(oEnd.bParentBreak){
while(oEnd.oNode && oEnd.oNode.nodeType == 3 && oEnd.oNode.nodeValue == ""){
oEnd.oNode = oEnd.oNode.previousSibling;
}
var nTmp = 1;
if(oEnd.oNode == elBR || oEnd.oNode.nextSibling == elBR){
nTmp = 0;
}
if(nTmp === 0){
oSelection.pasteHTML("<br type='_moz'/>");
oSelection.collapseToEnd();
}
}
}
oSelection.insertNode(this.oApp.getWYSIWYGDocument().createTextNode(""));
oSelection.select();
}
});
nhn.husky.SE2M_EditingModeChanger = jindo.$Class({
name : "SE2M_EditingModeChanger",
htConversionMode : null,
$init : function(elAppContainer, htConversionMode){
this.htConversionMode = htConversionMode;
this._assignHTMLElements(elAppContainer);
},
_assignHTMLElements : function(elAppContainer){
elAppContainer = jindo.$(elAppContainer) || document;
this.elWYSIWYGButton = jindo.$$.getSingle("BUTTON.se2_to_editor", elAppContainer);
this.elHTMLSrcButton = jindo.$$.getSingle("BUTTON.se2_to_html", elAppContainer);
this.elTEXTButton = jindo.$$.getSingle("BUTTON.se2_to_text", elAppContainer);
this.elModeToolbar = jindo.$$.getSingle("DIV.se2_conversion_mode", elAppContainer);
this.welWYSIWYGButtonLi = jindo.$Element(this.elWYSIWYGButton.parentNode);
this.welHTMLSrcButtonLi = jindo.$Element(this.elHTMLSrcButton.parentNode);
this.welTEXTButtonLi = jindo.$Element(this.elTEXTButton.parentNode);
},
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["isUseModeChanger", jindo.$Fn(this.isUseModeChanger, this).bind()]);
},
$ON_MSG_APP_READY : function(){
if(this.oApp.htOptions.bOnlyTextMode){
this.elWYSIWYGButton.style.display = 'none';
this.elHTMLSrcButton.style.display = 'none';
this.elTEXTButton.style.display = 'block';
this.oApp.exec("CHANGE_EDITING_MODE", ["TEXT"]);
}else{
this.oApp.registerBrowserEvent(this.elWYSIWYGButton, "click", "EVENT_CHANGE_EDITING_MODE_CLICKED", ["WYSIWYG"]);
this.oApp.registerBrowserEvent(this.elHTMLSrcButton, "click", "EVENT_CHANGE_EDITING_MODE_CLICKED", ["HTMLSrc"]);
this.oApp.registerBrowserEvent(this.elTEXTButton, "click", "EVENT_CHANGE_EDITING_MODE_CLICKED", ["TEXT", false]);
this.showModeChanger();
if(this.isUseModeChanger() === false && this.oApp.isUseVerticalResizer() === false){
this.elModeToolbar.style.display = "none";
}
}
},
showModeChanger : function(){
if(this.isUseModeChanger()){
this.elWYSIWYGButton.style.display = 'block';
this.elHTMLSrcButton.style.display = 'block';
this.elTEXTButton.style.display = 'block';
}else{
this.elWYSIWYGButton.style.display = 'none';
this.elHTMLSrcButton.style.display = 'none';
this.elTEXTButton.style.display = 'none';
}
},
isUseModeChanger : function(){
return (typeof(this.htConversionMode) === 'undefined' || typeof(this.htConversionMode.bUseModeChanger) === 'undefined' || this.htConversionMode.bUseModeChanger === true) ? true : false;
},
$ON_EVENT_CHANGE_EDITING_MODE_CLICKED : function(sMode, bNoAlertMsg){
if (sMode == 'TEXT') {
var sContent = this.oApp.getIR();
if (sContent.length > 0 && !bNoAlertMsg) {
if ( !confirm(this.oApp.$MSG("SE2M_EditingModeChanger.confirmTextMode")) ) {
return false;
}
}
this.oApp.exec("CHANGE_EDITING_MODE", [sMode]);
}else{
this.oApp.exec("CHANGE_EDITING_MODE", [sMode]);
}
if ('HTMLSrc' == sMode) {
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['htmlmode']);
} else if ('TEXT' == sMode) {
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['textmode']);
} else {
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['editormode']);
}
},
$ON_DISABLE_ALL_UI : function(htOptions){
htOptions = htOptions || {};
var waExceptions = jindo.$A(htOptions.aExceptions || []);
if(waExceptions.has("mode_switcher")){
return;
}
if(this.oApp.getEditingMode() == "WYSIWYG"){
this.welWYSIWYGButtonLi.removeClass("active");
this.elHTMLSrcButton.disabled = true;
this.elTEXTButton.disabled = true;
} else if (this.oApp.getEditingMode() == 'TEXT') {
this.welTEXTButtonLi.removeClass("active");
this.elWYSIWYGButton.disabled = true;
this.elHTMLSrcButton.disabled = true;
}else{
this.welHTMLSrcButtonLi.removeClass("active");
this.elWYSIWYGButton.disabled = true;
this.elTEXTButton.disabled = true;
}
},
$ON_ENABLE_ALL_UI : function(){
if(this.oApp.getEditingMode() == "WYSIWYG"){
this.welWYSIWYGButtonLi.addClass("active");
this.elHTMLSrcButton.disabled = false;
this.elTEXTButton.disabled = false;
} else if (this.oApp.getEditingMode() == 'TEXT') {
this.welTEXTButtonLi.addClass("active");
this.elWYSIWYGButton.disabled = false;
this.elHTMLSrcButton.disabled = false;
}else{
this.welHTMLSrcButtonLi.addClass("active");
this.elWYSIWYGButton.disabled = false;
this.elTEXTButton.disabled = false;
}
},
$ON_CHANGE_EDITING_MODE : function(sMode){
if(sMode == "HTMLSrc"){
this.welWYSIWYGButtonLi.removeClass("active");
this.welHTMLSrcButtonLi.addClass("active");
this.welTEXTButtonLi.removeClass("active");
this.elWYSIWYGButton.disabled = false;
this.elHTMLSrcButton.disabled = true;
this.elTEXTButton.disabled = false;
this.oApp.exec("HIDE_ALL_DIALOG_LAYER");
this.oApp.exec("DISABLE_ALL_UI", [{aExceptions:["mode_switcher"]}]);
} else if (sMode == 'TEXT') {
this.welWYSIWYGButtonLi.removeClass("active");
this.welHTMLSrcButtonLi.removeClass("active");
this.welTEXTButtonLi.addClass("active");
this.elWYSIWYGButton.disabled = false;
this.elHTMLSrcButton.disabled = false;
this.elTEXTButton.disabled = true;
this.oApp.exec("HIDE_ALL_DIALOG_LAYER");
this.oApp.exec("DISABLE_ALL_UI", [{aExceptions:["mode_switcher"]}]);
}else{
this.welWYSIWYGButtonLi.addClass("active");
this.welHTMLSrcButtonLi.removeClass("active");
this.welTEXTButtonLi.removeClass("active");
this.elWYSIWYGButton.disabled = true;
this.elHTMLSrcButton.disabled = false;
this.elTEXTButton.disabled = false;
this.oApp.exec("RESET_STYLE_STATUS");
this.oApp.exec("ENABLE_ALL_UI", []);
}
}
});
nhn.husky.SE_PasteHandler = jindo.$Class({
name : "SE_PasteHandler",
$init : function(sParagraphContainer){
this.sParagraphContainer = sParagraphContainer || "P";
this.aConversionTarget = ["TABLE"];
this.htBrowser = jindo.$Agent().navigator();
},
$ON_REGISTER_CONVERTERS : function() {
if(this.htBrowser.ie){
this.oApp.exec('ADD_CONVERTER', ['IR_TO_DB', jindo.$Fn(this.irToDb, this).bind()]);
}
},
irToDb : function(sHtml) {
if ('undefined' == typeof(sHtml) || !sHtml) { return sHtml; }
var sFilteredHtml = this._filterPastedContents(sHtml, true);
return sFilteredHtml;
},
$ON_MSG_APP_READY : function(){
this.elEditingAreaContainer = jindo.$$.getSingle("DIV.husky_seditor_editing_area_container", null, {oneTimeOffCache : true});
this.oApp.exec("ADD_APP_PROPERTY", ["filterPastedContents", jindo.$Fn(this._filterPastedContents, this).bind()]);
if(this.htBrowser.chrome || (this.htBrowser.safari && this.htBrowser.version >= 6)){
this.$ON_EVENT_EDITING_AREA_PASTE = this._handlePaste;
}
},
_handlePaste : function(we){
var sClipboardData, elTmp, elStyle, sStyleFromClipboard,
bProcess = false;
sClipboardData = we.$value().clipboardData.getData("text/html");
elTmp = document.createElement("DIV");
elTmp.innerHTML = sClipboardData;
elStyle = jindo.$$.getSingle("style", elTmp, {oneTimeOffCache : true});
if(elStyle){
sStyleFromClipboard = elStyle.innerHTML;
sStyleFromClipboard = sStyleFromClipboard.replace(/"/g, "'");
this._sStyleFromClipboard = sStyleFromClipboard;
}
bProcess = this._hasConversionTarget(sClipboardData);
if(bProcess){
this._preparePaste();
setTimeout(jindo.$Fn(function(){
var rxZwspStart = new RegExp("^[\u200b]");
if(this.zwspStart){
if(typeof(this.zwspStart.nodeValue) == "unknown"){
if(typeof(this.zwspStart.parentNode) != "unknown" && this.zwspStart.parentNode){
this.zwspStart.parentNode.removeChild(this.zwspStart);
}
}else{
if(this.zwspStart.nodeValue){
this.zwspStart.nodeValue = this.zwspStart.nodeValue.replace(rxZwspStart, "");
}
if(this.zwspStart.nodeValue == "" && this.zwspStart.parentNode){
this.zwspStart.parentNode.removeChild(this.zwspStart);
}
}
}
var rxZwspEnd = new RegExp("[\u200b]$");
if(this.zwspEnd){
if(typeof(this.zwspEnd.nodeValue) == "unknown"){
if(typeof(this.zwspEnd.parentNode) != "unknown" && this.zwspEnd.parentNode){
this.zwspEnd.parentNode.removeChild(this.zwspEnd);
}
}else{
if(this.zwspEnd.nodeValue){
this.zwspEnd.nodeValue = this.zwspEnd.nodeValue.replace(rxZwspEnd, "");
}
if(this.zwspEnd.nodeValue == "" && this.zwspEnd.parentNode){
this.zwspEnd.parentNode.removeChild(this.zwspEnd);
}
}
}
var oSelection = this.oApp.getSelection();
this.oSelectionClone = null;
try{
this._processPaste();
}catch(e){
if(typeof(JEagleEyeClient) != "undefined"){
var el = "http://blog.naver.com/hp_SE_PasteHandler.js/_handlePaste";
var line = e.lineNumber;
if(!line){
line = 0;
}
JEagleEyeClient.sendError(e, el, line);
}
}
oSelection.moveToStringBookmark(this._sBM);
oSelection.collapseToEnd();
oSelection.select();
oSelection.removeStringBookmark(this._sBM);
}, this).bind(), 0);
}
},
_hasConversionTarget : function(sClipboardData){
var hasTable = false,
rxTable = new RegExp("<(" + this.aConversionTarget.join("|") + ")[^>]*>", "i");
if(sClipboardData && rxTable.test(sClipboardData)){
hasTable = true;
}
return hasTable;
},
_preparePaste : function(){
this._securePasteArea();
},
_securePasteArea : function(){
var oSelection = this.oApp.getSelection();
this._sBM = oSelection.placeStringBookmark();
var elEndBookmark = oSelection.getStringBookmark(this._sBM, true);
var elStartBookmark = oSelection.getStringBookmark(this._sBM);
var emptyTextNode = document.createTextNode("");
this.zwspStart = emptyTextNode.cloneNode(true);
this.zwspStart.nodeValue = "\u200b";
this.zwspEnd = this.zwspStart.cloneNode(true);
var elNextToStartBookmark = elStartBookmark.nextSibling;
if(elNextToStartBookmark){
if(this._isEmptyTextNode(elNextToStartBookmark)){
elNextToStartBookmark = elNextToStartBookmark.nextSibling;
}
elStartBookmark.parentNode.insertBefore(this.zwspStart, elNextToStartBookmark);
}else{
elStartBookmark.parentNode.appendChild(this.zwspStart);
}
elEndBookmark.parentNode.insertBefore(this.zwspEnd, elEndBookmark);
elStartBookmark.innerHTML = "\u200b";
oSelection.setStartAfter(elStartBookmark);
oSelection.setEndBefore(this.zwspEnd);
oSelection.select();
},
_processPaste : function(){
this._savePastedContents();
if(this._sTarget){
try{
if(!!this.elPasteHelper){
this._clearPasteHelper();
this._showPasteHelper();
}else{
this._createPasteHelper();
}
this._loadToPasteHelper();
this._loadToBody();
this._hidePasteHelper();
}catch(e){
this._hidePasteHelper();
throw e;
}
}
},
_savePastedContents : function(){
var oSelection = this.oApp.getSelection();
oSelection.moveToStringBookmark(this._sBM);
oSelection.select();
this.oSelectionClone = oSelection.cloneContents();
oSelection.collapseToEnd();
oSelection.select();
var sTarget = this._outerHTML(this.oSelectionClone);
this._isPastedContentsEmpty = true;
if(sTarget != ""){
this._isPastedContentsEmpty = false;
if(this.htBrowser.firefox || (this.htBrowser.safari && this.htBrowser.version >= 6)){
var aStyleFromClipboard = sTarget.match(/<style>([^<>]+)<\/style>/i);
if(aStyleFromClipboard){
var sStyleFromClipboard = aStyleFromClipboard[1];
sStyleFromClipboard = sStyleFromClipboard.replace(/"/g, "'");
if(this._sStyleFromClipboard){
this._sStyleFromClipboard += sStyleFromClipboard;
}else{
this._sStyleFromClipboard = sStyleFromClipboard;
}
}
}
this._sTarget = this._filterPastedContents(sTarget, true);
}
},
_outerHTML : function(el){
var sOuterHTML = "";
if(el.outerHTML){
sOuterHTML = el.outerHTML;
}else{
var elTmp = document.createElement("DIV");
elTmp.appendChild(el.cloneNode(true));
sOuterHTML = elTmp.innerHTML;
}
return sOuterHTML;
},
_filterPastedContents : function(sOriginalContent, bUseTableFilter){
this._isPastedMultiParagraph = false;
this._aGoesPreviousParagraph = [];
var bParagraphChangeStart = false,
bParagraphChangeEnd = false,
nParagraphHierarchy = 0,
nParagraphChangeCount = 0,
bParagraphIsOpen = false;
var sMatch,
sResult,
aResult = [],
nPreviousIndex = -1,
sTagName,
sPreviousContent = "",
aMultiParagraphIndicator = ["BLOCKQUOTE", "DD", "DIV", "DL", "FORM", "H1", "H2", "H3", "H4", "H5", "H6",
	"HR", "OL", "P", "TABLE", "UL", "IFRAME"],
rxMultiParagraphIndicator = new RegExp("^(" + aMultiParagraphIndicator.join("|") + ")$", "i"),
isInTableContext = false,
nTdIndex = 0,
nTdLength = 0,
aColumnWidth = [],
nRowHeight = 0;
var nTableDepth = 0;
var aaColumnWidth = [];
var rxOpeningTag = /^<[^!?\/\s>]+(([\s]{0})|([\s]+[^>]+))>/,
rxTagName = /^<[\/]?([^\s]+)(([\s]{0})|([\s]+[^>]+))>/,
rxClosingTag = /^<\/[A-Za-z]+>/,
rxOpeningAndClosingTag = /^<[^>]+\/>/,
rxCommentTag = /^<!--[^<>]+-->/,
rxOpeningCommentTag = /^<!--[^->]+>/,
rxClosingCommentTag = /^<![^->]+-->/,
rxWhiteSpace = /^[\s]+/,
rxNonTag = /^[^<\n]+/,
rxExceptedOpeningTag = /^<[^<>]+>/,
rxMsoStyle = /(mso-[^:]+[\s]*:[\s]*)([^;"]*)([;]?)/gi,
rxStyle = /(style[\s]*=[\s]*)(["'])([^"']*)(["'])/i,
rxClass = /class[\s]*=[\s]*(?:(?:["']([^"']*)["'])|([^\s>]+))/i,
rxTableClassAdd = /(^<table)/gi,
rxApplied;
var rxQuot = /&quot;/gi;
var sClassAttr = "";
var aClass = [];
var sClass, sRx, rxClassForStyle;
var sMatchedStyle = "";
var sMatchTmp = "";
var rxClass_rest = /(class[\s]*=[\s]*["'])([^"']*)(["'])/i;
var rxSingleClass_underIE8 = /(class[\s]*=[\s]*)([^"'\s>]+)/i;
var _nSpan = 0;
var nColumnWidth;
var rxBorderWidth = /(border)([-]?[^:]*)(:[\s]*)([^;'"]+)/gi;
var rxBorderWidth_pointFive = /([^:\d])([0]?.[0-5][0-9]*pt)/gi;
var rxBorderWidth_pointFive_veryFirst = /(:)([\s]*([0]?.[0-5][0-9]*pt))/gi;
var _widthAttribute = "", _heightAttribute = "", _widthStyle = "", _heightStyle = "", _nWidth = "", _nHeight = "",
_bWidthStyleReplaceNeed = false, _bHeightStyleReplaceNeed = false,
rxWidthAttribute = /([^\w-])(width[\s]*=[\s]*)(["']?)([A-Za-z0-9.]+[%]?)([;]?["']?)/i,
rxHeightAttribute = /([^\w-])(height[\s]*=[\s]*)(["']?)([A-Za-z0-9.]+[%]?)([;]?["']?)/i,
rxWidthStyle = /(["';\s])(width[\s]*:[\s]*)([A-Za-z0-9.]+[%]?)([;]?)/i,
rxHeightStyle = /(["';\s])(height[\s]*:[\s]*)([A-Za-z0-9.]+[%]?)([;]?)/i;
var rxOpeningTag_endPart = /([\s]*)(>)/g;
var rxSpan = /span[\s]*=[\s]*"([\d]+)"/i;
var rxColspan = /colspan[\s]*=[\s]*"([\d]+)"/i;
var rxRowspan = /rowspan[\s]*=[\s]*"([\d]+)"/i;
this._doFilter = jindo.$Fn(function(sOriginalContent){
this._isPastedMultiParagraph = false;
this._aGoesPreviousParagraph = [];
var bParagraphChangeStart = false,
bParagraphChangeEnd = false,
nParagraphHierarchy = 0,
nParagraphChangeCount = 0,
bParagraphIsOpen = false;
sMatch,
sResult,
aResult = [],
nPreviousIndex = -1,
sTagName,
sPreviousContent = "",
isInTableContext = false,
nTdIndex = 0,
nTdLength = 0,
aColumnWidth = [],
nRowHeight = 0;
nTableDepth = 0;
aaColumnWidth = [];
rxApplied;
sClassAttr = "";
aClass = [];
sClass, sRx, rxClassForStyle;
sMatchedStyle = "";
sMatchTmp = "";
_nSpan = 0;
nColumnWidth;
_widthAttribute = "", _heightAttribute = "", _widthStyle = "", _heightStyle = "", _nWidth = "", _nHeight = "",
_bWidthStyleReplaceNeed = false, _bHeightStyleReplaceNeed = false;
while(sOriginalContent != ""){
sResult = "",
sMatch = "";
if(rxOpeningAndClosingTag.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxOpeningAndClosingTag)[0];
sResult = sMatch;
rxApplied = rxOpeningAndClosingTag;
}else if(rxOpeningTag.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxOpeningTag)[0];
sTagName = sMatch.match(rxTagName)[1].toUpperCase();
sClassAttr = "";
if(rxClass.test(sMatch)){
sClassAttr = sMatch.match(rxClass)[1] || sMatch.match(rxClass)[2];
}
sMatch = sMatch.replace(rxQuot, "'");
if(this.htBrowser.chrome || this.htBrowser.firefox || (this.htBrowser.safari && this.htBrowser.version >= 6)){
aClass = [];
if(sClassAttr && (sClassAttr.indexOf('mso') === -1)){
aClass = sClassAttr.split(" ");
}
if(aClass && aClass.length > 0){
for(var i = 0, len = aClass.length; i < len; i++){
sClass = "", sRx = "", rxClassForStyle = null, sMatchedStyle = "";
sClass = aClass[i];
sRx = sClass + "[\\n\\r\\t\\s]*{([^}]*)}";
rxClassForStyle = new RegExp(sRx);
if(rxClassForStyle.test(this._sStyleFromClipboard)){
	sMatchedStyle = this._sStyleFromClipboard.match(rxClassForStyle)[1];
}
if(sMatchedStyle){

	if(!!rxStyle.test(sMatch)){
		sMatch = sMatch.replace(rxStyle, "$1$2" + sMatchedStyle + " $3$4");
	}else{
		sMatch = sMatch.replace(rxOpeningTag_endPart, ' style="' + sMatchedStyle + '"$2');
	}
}
}
}
}
sTagName = sMatch.match(rxTagName)[1].toUpperCase();
if(sTagName === 'TABLE'){
if(nTableDepth === 0){
if(sClassAttr){
if(sClassAttr.indexOf('__se_tbl') === -1){
	if(rxClass_rest.test(sMatch)){
		sMatch = sMatch.replace(rxClass_rest, "$1$2 __se_tbl_ext$3");
	}else if(rxSingleClass_underIE8.test(sMatch)){
		sMatch = sMatch.replace(rxSingleClass_underIE8, '$1"$2 __se_tbl_ext"');
	}
}
}else{
sMatch = sMatch.replace(rxTableClassAdd, '$1 class="__se_tbl_ext"');
}
}
isInTableContext = true;
nTableDepth++;
}
else if(!this.htBrowser.ie && (sTagName === 'COL')){
if(rxWidthStyle.test(sMatch)){
_widthStyle = sMatch.match(rxWidthStyle)[3];
}else{
_widthStyle = "";
}
_nSpan = 0;
if(rxSpan.test(sMatch)){
_nSpan = sMatch.match(rxSpan)[1];
}
if(!!aaColumnWidth[nTableDepth] && typeof(aaColumnWidth[nTableDepth].length) === "number"){
aColumnWidth = aaColumnWidth[nTableDepth];
}else{
aColumnWidth = [];
}
if(_nSpan){
_nSpan = parseInt(_nSpan, 10);
for(var i = 0; i < _nSpan; i++){
aColumnWidth.push(_widthStyle);
nTdLength++;
}
}else{
nTdLength++;
aColumnWidth.push(_widthStyle);
}
aaColumnWidth[nTableDepth] = aColumnWidth;
}
else if(!this.htBrowser.ie && (sTagName === 'TR')){
if(!(rxHeightStyle.test(sMatch))){
nRowHeight = null;
}else{
_heightStyle = sMatch.match(rxHeightStyle)[3];
nRowHeight = _heightStyle;
}
}else if((sTagName === 'TD') || (sTagName === 'TH')){
sMatch = sMatch.replace(rxBorderWidth, function(){
return arguments[0].replace(rxBorderWidth_pointFive_veryFirst, "$11px").replace(rxBorderWidth_pointFive, " 1px");
});
nColumnWidth = undefined, aColumnWidth = undefined, _nSpan = undefined;
if(!this.htBrowser.ie){
aColumnWidth = aaColumnWidth[nTableDepth];
if(!!aColumnWidth && aColumnWidth.length > 0){
if(rxColspan.test(sMatch)){
	_nSpan = sMatch.match(rxColspan)[1];
	_nSpan = parseInt(_nSpan, 10);
}
nColumnWidth = aColumnWidth[nTdIndex];
if(!rxWidthStyle.test(sMatch) && nColumnWidth){
	if(_nSpan){
		if(nColumnWidth.indexOf('pt') != -1){
			nColumnWidth = parseFloat(nColumnWidth, 10) * _nSpan + 'pt';
		}else if(nColumnWidth.indexOf('px') != -1){
			nColumnWidth = parseFloat(nColumnWidth, 10) * _nSpan + 'px';
		}
	}
	if(!!rxStyle.test(sMatch)){
		sMatch = sMatch.replace(rxStyle, "$1$2width:" + nColumnWidth + "; $3$4");
	}else{
		sMatch = sMatch.replace(rxOpeningTag_endPart, ' style="width:' + nColumnWidth + ';"$2');
	}
} 
}
if(!rxHeightStyle.test(sMatch) && !!nRowHeight){
if(!!rxStyle.test(sMatch)){
	sMatch = sMatch.replace(rxStyle, "$1$2height:" + nRowHeight + "; $3$4");
}else{
	sMatch = sMatch.replace(rxOpeningTag_endPart, ' style="height:' + nRowHeight + ';"$2');
}
}
}
if(_nSpan){
nTdIndex += _nSpan;
}else{
nTdIndex++;
}
}
if(rxMultiParagraphIndicator.test(sTagName)){
this._isPastedMultiParagraph = true;
bParagraphChangeStart = true;
}
sResult += sMatch;
rxApplied = rxOpeningTag;
}else if(rxWhiteSpace.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxWhiteSpace)[0];
sResult = sMatch;
rxApplied = rxWhiteSpace;
}else if(rxNonTag.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxNonTag)[0];
sResult = sMatch;
rxApplied = rxNonTag;
}else if(rxClosingTag.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxClosingTag)[0];
sTagName = sMatch.match(rxTagName)[1].toUpperCase();
if(sTagName === 'TABLE'){
aaColumnWidth[nTableDepth] = null;
nTableDepth--;
isInTableContext = false;
nTdLength = 0;
nTdIndex = 0;
}else if(sTagName === 'TR'){
nTdIndex = 0;
}
if(rxMultiParagraphIndicator.test(sTagName)){
bParagraphChangeEnd = true;
}
sResult += sMatch;
rxApplied = rxClosingTag;
}
else if(rxExceptedOpeningTag.test(sOriginalContent)){
sMatch = sOriginalContent.match(rxExceptedOpeningTag)[0];
sResult = sMatch;
rxApplied = rxExceptedOpeningTag;
}else{
throw new Error("Unknown Node : If the content isn't invalid, please let us know.");
}
if(sResult != ""){
sPreviousContent = sResult;
nPreviousIndex++;
var sGoesPreviousParagraph = "";
if(!this._isPastedMultiParagraph){
sGoesPreviousParagraph = sResult;
}
if(!bParagraphChangeStart){
if(!bParagraphIsOpen && (nParagraphHierarchy == 0)){
if(!this.htBrowser.ie){
sResult = "<" + this.sParagraphContainer + ">" + sResult;
}
bParagraphIsOpen = true;
}
}else{
if(bParagraphIsOpen){
if(!this.htBrowser.ie){
sResult = "</" + this.sParagraphContainer + ">" + sResult;
}
bParagraphIsOpen = false;
}
nParagraphChangeCount++;
nParagraphHierarchy++;
}
if(bParagraphChangeEnd){
bParagraphChangeStart = false;
bParagraphChangeEnd = false;
nParagraphHierarchy--;
}
if(!this._isPastedMultiParagraph){
this._aGoesPreviousParagraph.push(sGoesPreviousParagraph);
}
aResult.push(sResult);
}
sOriginalContent = sOriginalContent.replace(rxApplied, "");
};
if(!this.htBrowser.ie && nParagraphChangeCount == 0){
var rxParagraphContainer = new RegExp("^<" + this.sParagraphContainer + ">");
if(aResult[0]){
aResult[0] = aResult[0].replace(rxParagraphContainer, "");
}
}
return aResult.join("");
}, this).bind();
var sFilteredContents = bUseTableFilter ? this._filterTableContents(sOriginalContent) : this._doFilter(sOriginalContent);
return sFilteredContents;
},
_filterTableContents : function(sOriginalContents){
var _sTarget = sOriginalContents;
var _rxTable_start = new RegExp('<table(([\\s]{0})|([\\s]+[^>]+))>', 'ig');
var _rxTable_end = new RegExp('</table>', 'ig');
var _res,
_nStartIndex,
_nEndIndex, 
_aStartIndex = [/* _nStartIndex */],
_aEndIndex = [/* _nEndIndex */],
_nLastIndex_tmp_start,
_nLastIndex_tmp_end,
_aTable = [],
_nTable_start = 0,
_nTable_end = 0;
function _findAndMarkTable(){
_nLastIndex_tmp_start = _rxTable_start.lastIndex;
_nLastIndex_tmp_end = _rxTable_end.lastIndex;
var res_tmp_start = _rxTable_start.exec(_sTarget);
var res_tmp_end = _rxTable_end.exec(_sTarget);
var nLastIndex_start = _rxTable_start.lastIndex;
var nLastIndex_end = _rxTable_end.lastIndex;
_rxTable_start.lastIndex = _nLastIndex_tmp_start;
_rxTable_end.lastIndex = _nLastIndex_tmp_end;
if(res_tmp_start === null){
if(res_tmp_end !== null){
_doRxTable_end();
}
}else if(res_tmp_end === null){
if(res_tmp_start !== null){
_doRxTable_start();
}
}else{
if(nLastIndex_start < nLastIndex_end){
_doRxTable_start();
}else if(nLastIndex_start > nLastIndex_end){
_doRxTable_end();
}
}
}
function _doRxTable_start(){
_res = _rxTable_start.exec(_sTarget);
_rxTable_end.lastIndex = _rxTable_start.lastIndex;
if(_res !== null){
_nTable_start++;
}
if(_nTable_start == 1){
_nStartIndex = _res.index;
_aStartIndex.push(_nStartIndex);
}
_findAndMarkTable();
}
function _doRxTable_end(){
_res = _rxTable_end.exec(_sTarget);
_rxTable_start.lastIndex = _rxTable_end.lastIndex;
if(_res !== null){
_nTable_end++;
}
if((_nTable_start !== 0) && (_nTable_end !== 0) && (_nTable_start == _nTable_end)){
_nEndIndex = _res.index;
_aEndIndex.push(_nEndIndex + 8);
_aTable.push(_sliceTable());
_initVar();
}
_findAndMarkTable();
}
var _sliceTable = function(){
return _sTarget.slice(_nStartIndex, _nEndIndex + 8);
};
var _initVar = function(){
_nStartIndex = undefined;
_nEndIndex = undefined;
_nTable_start = 0;
_nTable_end = 0;
};
_findAndMarkTable();
for(var i = 0, len = _aTable.length; i < len; i++){
var sTable = _aTable[i];
sTable = this._doFilter(sTable);
_aTable[i] = sTable;
}
if(this.htBrowser.ie){
var _aResult = [];
var _sResult = '';
var _nStartIndexLength = _aStartIndex.length;
var _nEndIndexLength = _aEndIndex.length;
var __nStartIndex, __nEndIndex;
if((_nStartIndexLength > 0) && (_nStartIndexLength == _nEndIndexLength)){
__nStartIndex = _aStartIndex[0];
_aResult.push(_sTarget.slice(0, __nStartIndex));
for(var i = 0, len = _aStartIndex.length; i < len; i++){
if((_nStartIndexLength > 1) && (i > 0)){
__nStartIndex = _aEndIndex[i - 1];
__nEndIndex = _aStartIndex[i];
_aResult.push(_sTarget.slice(__nStartIndex, __nEndIndex));
}
__nStartIndex = _aStartIndex[i];
__nEndIndex = _aEndIndex[i];
_aResult.push(_aTable[i]);
}
__nEndIndex = _aEndIndex[_nEndIndexLength - 1];
_aResult.push(_sTarget.slice(__nEndIndex, _sTarget.length + 1));
return _aResult.join('');
}else{
return _sTarget;
}
}else{
return _aTable.join('');
}
},
_createPasteHelper : function(){
if(!this.elPasteHelper){
this.elPasteHelper = document.createElement("DIV");
this.elPasteHelper.className = "husky_seditor_paste_helper";
this.elPasteHelper.style.width = "0px";
this.elPasteHelper.style.height = "0px";
this.elPasteHelper.style.overflow = "hidden";
this.elPasteHelper.contentEditable = "true";
this.elPasteHelper.style.position = "absolute";
this.elPasteHelper.style.top = "9999px";
this.elPasteHelper.style.left = "9999px";
this.elEditingAreaContainer.appendChild(this.elPasteHelper);
}
},
_showPasteHelper : function(){
if(!!this.elPasteHelper && this.elPasteHelper.style.display == "none"){
this.elPasteHelper.style.display = "block";
}
},
_hidePasteHelper : function(){
if(!!this.elPasteHelper && this.elPasteHelper.style.display != "none"){
this.elPasteHelper.style.display = "none";
}
},
_clearPasteHelper : function(){
if(!!this.elPasteHelper){
this.elPasteHelper.innerHTML = "";
}
},
_loadToPasteHelper : function(){
var aParagraph = [];
var elTmp, sGoesPreviousParagraph, _aGoesPreviousParagraph, waParagraph;
if(this._isPastedMultiParagraph){
elTmp = document.createElement("DIV");
elTmp.innerHTML = this._sTarget;
aParagraph = elTmp.childNodes;
}
if(this._aGoesPreviousParagraph && this._aGoesPreviousParagraph.length > 0){
sGoesPreviousParagraph = this._aGoesPreviousParagraph.join("");
elTmp = document.createElement("DIV");
elTmp.innerHTML = sGoesPreviousParagraph;
_aGoesPreviousParagraph = elTmp.childNodes;
for(var i = 0, len = _aGoesPreviousParagraph.length; i < len; i++){
this.elPasteHelper.appendChild(_aGoesPreviousParagraph[i].cloneNode(true));
}
if(aParagraph.length > 0){
if(!!aParagraph.splice){
aParagraph.splice(0, 1);
}else{
waParagraph = jindo.$A(aParagraph);
waParagraph.splice(0, 1);
aParagraph = waParagraph.$value();
}
}
}
if(aParagraph.length > 0){
for(var i = 0, len = aParagraph.length; i < len; i++){
this.elPasteHelper.appendChild(aParagraph[i].cloneNode(true));
}
}
return;
},
_loadToBody : function(){
var oSelection = this.oApp.getSelection();
try{
oSelection.moveToStringBookmark(this._sBM);
oSelection.select();
var aSelectedNode_original = oSelection.getNodes();
var aConversionIndex_original = this._markMatchedElementIndex(aSelectedNode_original, this.aConversionTarget);
var oSelection_parent = this.oApp.getSelection(this.oApp.getWYSIWYGWindow().parent);
oSelection_parent.setStartBefore(this.elPasteHelper.firstChild);
oSelection_parent.setEndAfter(this.elPasteHelper.lastChild);
oSelection_parent.select();
var aSelectedNode_filtered = oSelection_parent.getNodes();
var aConversionIndex_filtered = this._markMatchedElementIndex(aSelectedNode_filtered, this.aConversionTarget);
var nDiff_original_filtered = aConversionIndex_original.length - aConversionIndex_filtered.length;
if(aConversionIndex_original.length > 0 && aConversionIndex_original.length == aConversionIndex_filtered.length){
var nConversionIndex_original, nConversionIndex_filtered, elConversion_as_is, elConversion_to_be;
for(var i = 0, len = aConversionIndex_filtered.length; i < len; i++){
nConversionIndex_original = aConversionIndex_original[i + nDiff_original_filtered];
nConversionIndex_filtered = aConversionIndex_filtered[i];
elConversion_as_is = aSelectedNode_original[nConversionIndex_original];
elConversion_to_be = aSelectedNode_filtered[nConversionIndex_filtered];
if(!/__se_tbl/.test(elConversion_as_is.className)){
elConversion_as_is.parentNode.replaceChild(elConversion_to_be.cloneNode(true), elConversion_as_is);
}
}
}
}catch(e){
oSelection.moveToStringBookmark(this._sBM);
oSelection.select();
oSelection.deleteContents();
var elEndBookmark = oSelection.getStringBookmark(this._sBM, true);
elEndBookmark.parentNode.insertBefore(this.oSelectionClone.cloneNode(true), elEndBookmark);
throw e;
}
},
_markMatchedElementIndex : function(aNodeList, aTagName, aFilter, sFilterLogic){
var aMatchedElementIndex = [];
var sPattern = aTagName.join("|");
var rxTagName = new RegExp("^(" + sPattern + ")$", "i");
var elNode, fFilter, isFilteringSuccess;
if(aFilter){
sFilterLogic = sFilterLogic || "OR";
if(sFilterLogic.toUpperCase() === "AND"){
isFilteringSuccess = true;
}else if(sFilterLogic.toUpperCase() === "OR"){
isFilteringSuccess = false;
}
}
for(var i = 0, len = aNodeList.length; i < len; i++){
elNode = aNodeList[i];
if(rxTagName.test(elNode.nodeName)){
if(aFilter){
for(var ii = aFilter.length; ii--;){
fFilter = aFilter[ii];
if(sFilterLogic.toUpperCase() === "AND"){
if(!fFilter.apply(elNode)){
isFilteringSuccess = false;
break;
}
}else if(sFilterLogic.toUpperCase() === "OR"){
if(fFilter.apply(elNode)){
isFilteringSuccess = true;
break;
}
}
}
if(isFilteringSuccess){
aMatchedElementIndex.push(i);
}
}else{
aMatchedElementIndex.push(i);
}
}
}
return aMatchedElementIndex;
},
_isEmptyTextNode : function(node){
return node.nodeType == 3 && !/\S/.test(node.nodeValue);
}
});
nhn.husky.SE2M_ExecCommand = jindo.$Class({
name : "SE2M_ExecCommand",
oEditingArea : null,
oUndoOption : null,
_rxTable : /^(?:TBODY|TR|TD)$/i,
_rxCmdInline : /^(?:bold|underline|italic|strikethrough|superscript|subscript)$/i,
$init : function(oEditingArea){
this.oEditingArea = oEditingArea;
this.nIndentSpacing = 40;
this.rxClickCr = new RegExp('^bold|underline|italic|strikethrough|justifyleft|justifycenter|justifyright|justifyfull|insertorderedlist|insertunorderedlist|outdent|indent$', 'i');
},
$BEFORE_MSG_APP_READY : function(){
if(this.oEditingArea && this.oEditingArea.tagName == "IFRAME"){
this.oEditingArea = this.oEditingArea.contentWindow.document;
}
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+b", "EXECCOMMAND", ["bold", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+u", "EXECCOMMAND", ["underline", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+i", "EXECCOMMAND", ["italic", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+d", "EXECCOMMAND", ["strikethrough", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["meta+b", "EXECCOMMAND", ["bold", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["meta+u", "EXECCOMMAND", ["underline", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["meta+i", "EXECCOMMAND", ["italic", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["meta+d", "EXECCOMMAND", ["strikethrough", false, false]]);
this.oApp.exec("REGISTER_HOTKEY", ["tab", "INDENT"]);
this.oApp.exec("REGISTER_HOTKEY", ["shift+tab", "OUTDENT"]);
this.oApp.exec("REGISTER_UI_EVENT", ["bold", "click", "EXECCOMMAND", ["bold", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["underline", "click", "EXECCOMMAND", ["underline", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["italic", "click", "EXECCOMMAND", ["italic", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["lineThrough", "click", "EXECCOMMAND", ["strikethrough", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["superscript", "click", "EXECCOMMAND", ["superscript", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["subscript", "click", "EXECCOMMAND", ["subscript", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["justifyleft", "click", "EXECCOMMAND", ["justifyleft", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["justifycenter", "click", "EXECCOMMAND", ["justifycenter", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["justifyright", "click", "EXECCOMMAND", ["justifyright", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["justifyfull", "click", "EXECCOMMAND", ["justifyfull", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["orderedlist", "click", "EXECCOMMAND", ["insertorderedlist", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["unorderedlist", "click", "EXECCOMMAND", ["insertunorderedlist", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["outdent", "click", "EXECCOMMAND", ["outdent", false, false]]);
this.oApp.exec("REGISTER_UI_EVENT", ["indent", "click", "EXECCOMMAND", ["indent", false, false]]);
this.oNavigator = jindo.$Agent().navigator();
if(!this.oNavigator.safari && !this.oNavigator.chrome){
this._getDocumentBR = function(){};
this._fixDocumentBR	= function(){};
}
if(!this.oNavigator.ie){
this._fixCorruptedBlockQuote = function(){};
if(!this.oNavigator.safari && !this.oNavigator.chrome){
this._insertBlankLine = function(){};
}
}
if(!this.oNavigator.firefox){
this._extendBlock = function(){};
}
},
$ON_INDENT : function(){
this.oApp.delayedExec("EXECCOMMAND", ["indent", false, false], 0);
},
$ON_OUTDENT : function(){
this.oApp.delayedExec("EXECCOMMAND", ["outdent", false, false], 0);
},
_isTextBetweenTable : function(oNode){
var oTmpNode;
if(oNode && oNode.nodeType === 3){
if((oTmpNode = oNode.previousSibling) && this._rxTable.test(oTmpNode.nodeName)){
return true;
}
if((oTmpNode = oNode.nextSibling) && this._rxTable.test(oTmpNode.nodeName)){
return true;
}
}
return false;
},
$BEFORE_EXECCOMMAND : function(sCommand, bUserInterface, vValue, htOptions){
var elTmp, oSelection;
this.oApp.exec("FOCUS");
this._bOnlyCursorChanged = false;
oSelection = this.oApp.getSelection();
for(var i = 0, aNodes = oSelection.getNodes(), oNode;(oNode = aNodes[i]); i++){
if(this._isTextBetweenTable(oNode)){
oNode.parentNode.removeChild(oNode);
}
}
if(/^insertorderedlist|insertunorderedlist$/i.test(sCommand)){
this._getDocumentBR();
this._checkBlockQuoteCondition_IE();
}
if(/^justify*/i.test(sCommand)){
this._removeSpanAlign();
}
if(this._rxCmdInline.test(sCommand)){
this.oUndoOption = {bMustBlockElement:true};
if(nhn.CurrentSelection.isCollapsed()){
this._bOnlyCursorChanged = true;
}
}
if(sCommand == "indent" || sCommand == "outdent"){
if(!htOptions){htOptions = {};}
htOptions["bDontAddUndoHistory"] = true;
}
if((!htOptions || !htOptions["bDontAddUndoHistory"]) && !this._bOnlyCursorChanged){
if(/^justify*/i.test(sCommand)){
this.oUndoOption = {sSaveTarget:"BODY"};
}else if(sCommand === "insertorderedlist" || sCommand === "insertunorderedlist"){
this.oUndoOption = {bMustBlockContainer:true};
}
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", [sCommand, this.oUndoOption]);
}
if(this.oNavigator.ie && this.oApp.getWYSIWYGDocument().selection){
if(this.oApp.getWYSIWYGDocument().selection.type === "Control"){
oSelection = this.oApp.getSelection();
oSelection.select();
}
}
if(sCommand == "insertorderedlist" || sCommand == "insertunorderedlist"){
this._insertBlankLine();
}
},
_checkBlockQuoteCondition_IE : function(){
var htBrowser = jindo.$Agent().navigator();
var bProcess = false;
var elBlockquote;
if((htBrowser.ie && (htBrowser.nativeVersion >= 9 && htBrowser.nativeVersion <= 11) && (htBrowser.version >= 9 && htBrowser.version <= 11))
|| (this.oApp.oAgent.os().winxp && htBrowser.ie && htBrowser.nativeVersion <= 8)){
var oSelection = this.oApp.getSelection();
var elCommonAncestorContainer = oSelection.commonAncestorContainer;
var htAncestor_blockquote = nhn.husky.SE2M_Utils.findAncestorByTagNameWithCount("BLOCKQUOTE", elCommonAncestorContainer);
elBlockquote = htAncestor_blockquote.elNode;
if(elBlockquote){
var htAncestor_cell = nhn.husky.SE2M_Utils.findClosestAncestorAmongTagNamesWithCount(["td", "th"], elCommonAncestorContainer);
if(htAncestor_cell.elNode){
if(htAncestor_cell.nRecursiveCount > htAncestor_blockquote.nRecursiveCount){
bProcess = true;
}
}else{
bProcess = true;
}
}
}
if(bProcess){
this._insertDummyParagraph_IE(elBlockquote);
}
},
_insertDummyParagraph_IE : function(el){
this._elDummyParagraph = document.createElement("P");
el.appendChild(this._elDummyParagraph);
},
_removeDummyParagraph_IE : function(){
if(this._elDummyParagraph && this._elDummyParagraph.parentNode){
this._elDummyParagraph.parentNode.removeChild(this._elDummyParagraph);
}
},
$ON_EXECCOMMAND : function(sCommand, bUserInterface, vValue){
var bSelectedBlock = false;
var htSelectedTDs = {};
var oSelection = this.oApp.getSelection();
bUserInterface = (bUserInterface == "" || bUserInterface)?bUserInterface:false;
vValue = (vValue == "" || vValue)?vValue:false;
this.oApp.exec("IS_SELECTED_TD_BLOCK",['bIsSelectedTd',htSelectedTDs]);
bSelectedBlock = htSelectedTDs.bIsSelectedTd;
if( bSelectedBlock){
if(sCommand == "indent"){
this.oApp.exec("SET_LINE_BLOCK_STYLE", [null, jindo.$Fn(this._indentMargin, this).bind()]);
}else if(sCommand == "outdent"){
this.oApp.exec("SET_LINE_BLOCK_STYLE", [null, jindo.$Fn(this._outdentMargin, this).bind()]);
}else{
this._setBlockExecCommand(sCommand, bUserInterface, vValue);
}
} else {
switch(sCommand){
case "indent":
case "outdent":
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", [sCommand]);
var sBookmark = oSelection.placeStringBookmark();
if(sCommand === "indent"){
this.oApp.exec("SET_LINE_STYLE", [null, jindo.$Fn(this._indentMargin, this).bind(), {bDoNotSelect : true, bDontAddUndoHistory : true}]);
}else{
this.oApp.exec("SET_LINE_STYLE", [null, jindo.$Fn(this._outdentMargin, this).bind(), {bDoNotSelect : true, bDontAddUndoHistory : true}]);
}
oSelection.moveToStringBookmark(sBookmark);
oSelection.select();
oSelection.removeStringBookmark(sBookmark);
setTimeout(jindo.$Fn(function(sCommand){
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", [sCommand]);
}, this).bind(sCommand), 25);
break;
case "justifyleft":
case "justifycenter":
case "justifyright":
case "justifyfull":
var oSelectionClone = this._extendBlock();
this.oEditingArea.execCommand(sCommand, bUserInterface, vValue);
if(!!oSelectionClone){
oSelectionClone.select();
}
break;
default:
if(oSelection.collapsed && this._rxCmdInline.test(sCommand)){
var sBM = oSelection.placeStringBookmark(),
oBM = oSelection.getStringBookmark(sBM),
oHolderNode = oBM.previousSibling;
if(!oHolderNode || oHolderNode.nodeValue !== "\u200B"){
oHolderNode = this.oApp.getWYSIWYGDocument().createTextNode("\u200B");
oSelection.insertNode(oHolderNode);
}
oSelection.removeStringBookmark(sBM);
oSelection.selectNodeContents(oHolderNode);
oSelection.select();
this.oEditingArea.execCommand(sCommand, bUserInterface, vValue);
oSelection.collapseToEnd();
oSelection.select();
var oSingleNode = this._findSingleNode(oHolderNode);
if(oSingleNode && oSelection._hasCursorHolderOnly(oSingleNode.nextSibling)){
oSingleNode.parentNode.removeChild(oSingleNode.nextSibling);
}
}else{
this.oEditingArea.execCommand(sCommand, bUserInterface, vValue);
}
}
}
this._countClickCr(sCommand);
},
_findSingleNode : function(oNode){
if(!oNode){
return null;
}
if(oNode.parentNode.childNodes.length === 1){
return this._findSingleNode(oNode.parentNode);
}else{
return oNode;
}
},
$AFTER_EXECCOMMAND : function(sCommand, bUserInterface, vValue, htOptions){
if(this.elP1 && this.elP1.parentNode){
this.elP1.parentNode.removeChild(this.elP1);
}
if(this.elP2 && this.elP2.parentNode){
this.elP2.parentNode.removeChild(this.elP2);
}
if(/^insertorderedlist|insertunorderedlist$/i.test(sCommand)){
this._removeDummyParagraph_IE();
this._fixCorruptedBlockQuote(sCommand === "insertorderedlist" ? "OL" : "UL");
if(this.oNavigator.bGalaxyBrowser){
this._removeBlockQuote();
}
}
if((/^justify*/i.test(sCommand))){
this._fixAlign(sCommand === "justifyfull" ? "justify" : sCommand.substring(7));
}
if(sCommand == "indent" || sCommand == "outdent"){
if(!htOptions){htOptions = {};}
htOptions["bDontAddUndoHistory"] = true;
}
if((!htOptions || !htOptions["bDontAddUndoHistory"]) && !this._bOnlyCursorChanged){
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", [sCommand, this.oUndoOption]);
}
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
_removeSpanAlign : function(){
var oSelection = this.oApp.getSelection(),
aNodes = oSelection.getNodes(),
elNode = null;
for(var i=0, nLen=aNodes.length; i<nLen; i++){
elNode = aNodes[i];
if(elNode.tagName && elNode.tagName === "SPAN"){
elNode.style.textAlign = "";
elNode.removeAttribute("align");
}
}
},
_getAlignNode : function(elNode){
if(elNode.tagName && (elNode.tagName === "P" || elNode.tagName === "DIV")){
return elNode;
}
elNode = elNode.parentNode;
while(elNode && elNode.tagName){
if(elNode.tagName === "P" || elNode.tagName === "DIV"){
return elNode;
}
elNode = elNode.parentNode;
}
},
_fixAlign : function(sAlign){
var oSelection = this.oApp.getSelection(),
aNodes = [],
elNode = null,
elParentNode = null;
var removeTableAlign = !this.oNavigator.ie ? function(){} : function(elNode){
if(elNode.tagName && elNode.tagName === "TABLE"){
elNode.removeAttribute("align");
return true;
}
return false;
};
if(oSelection.collapsed){
aNodes[0] = oSelection.startContainer;
}else{
aNodes = oSelection.getNodes();
}
for(var i=0, nLen=aNodes.length; i<nLen; i++){
elNode = aNodes[i];
if(elNode.nodeType === 3){
elNode = elNode.parentNode;
}
if(elParentNode && (elNode === elParentNode || jindo.$Element(elNode).isChildOf(elParentNode))){
continue;
}
elParentNode = this._getAlignNode(elNode);
if(elParentNode && elParentNode.align !== elParentNode.style.textAlign){
elParentNode.style.textAlign = sAlign;
elParentNode.setAttribute("align", sAlign);
}
}
},
_getDocumentBR : function(){
var i, nLen;
this.aBRs = this.oApp.getWYSIWYGDocument().getElementsByTagName("BR");
this.aBeforeBRs = [];
for(i=0, nLen=this.aBRs.length; i<nLen; i++){
this.aBeforeBRs[i] = this.aBRs[i];
}
},
_fixDocumentBR : function(){
if(this.aBeforeBRs.length === this.aBRs.length){
return;
}
var waBeforeBRs = jindo.$A(this.aBeforeBRs),
i, iLen = this.aBRs.length;
for(i=iLen-1; i>=0; i--){
if(waBeforeBRs.indexOf(this.aBRs[i])<0){
this.aBRs[i].parentNode.removeChild(this.aBRs[i]);
}
}
},
_setBlockExecCommand : function(sCommand, bUserInterface, vValue){
var aNodes, aChildrenNode, htSelectedTDs = {};
this.oSelection = this.oApp.getSelection();
this.oApp.exec("GET_SELECTED_TD_BLOCK",['aTdCells',htSelectedTDs]);
aNodes = htSelectedTDs.aTdCells;
for( var j = 0; j < aNodes.length ; j++){
this.oSelection.selectNodeContents(aNodes[j]);
this.oSelection.select();
if(this.oNavigator.firefox){
this.oEditingArea.execCommand("styleWithCSS", bUserInterface, false);
}
aChildrenNode = this.oSelection.getNodes();
for( var k = 0; k < aChildrenNode.length ; k++ ) {
if(aChildrenNode[k].tagName == "UL" || aChildrenNode[k].tagName == "OL" ){
jindo.$Element(aChildrenNode[k]).css("color",vValue);
}
}
this.oEditingArea.execCommand(sCommand, bUserInterface, vValue);
}
},
_indentMargin : function(elDiv){
var elTmp = elDiv,
aAppend, i, nLen, elInsertTarget, elDeleteTarget, nCurMarginLeft;
while(elTmp){
if(elTmp.tagName && elTmp.tagName === "LI"){
elDiv = elTmp;
break;
}
elTmp = elTmp.parentNode;
}
if(elDiv.tagName === "LI"){
if(elDiv.previousSibling && elDiv.previousSibling.tagName && elDiv.previousSibling.tagName === elDiv.parentNode.tagName){
if(elDiv.nextSibling && elDiv.nextSibling.tagName && elDiv.nextSibling.tagName === elDiv.parentNode.tagName){
aAppend = [elDiv];
for(i=0, nLen=elDiv.nextSibling.childNodes.length; i<nLen; i++){
aAppend.push(elDiv.nextSibling.childNodes[i]);
}
elInsertTarget = elDiv.previousSibling;
elDeleteTarget = elDiv.nextSibling;
for(i=0, nLen=aAppend.length; i<nLen; i++){
elInsertTarget.insertBefore(aAppend[i], null);
}
elDeleteTarget.parentNode.removeChild(elDeleteTarget);
}else{
elDiv.previousSibling.insertBefore(elDiv, null);
}
return;
}
if(elDiv.nextSibling && elDiv.nextSibling.tagName && elDiv.nextSibling.tagName === elDiv.parentNode.tagName){
elDiv.nextSibling.insertBefore(elDiv, elDiv.nextSibling.firstChild);
return;
}
elTmp = elDiv.parentNode.cloneNode(false);
elDiv.parentNode.insertBefore(elTmp, elDiv);
elTmp.appendChild(elDiv);
return;
}
nCurMarginLeft = parseInt(elDiv.style.marginLeft, 10);
if(!nCurMarginLeft){
nCurMarginLeft = 0;
}
nCurMarginLeft += this.nIndentSpacing;
elDiv.style.marginLeft = nCurMarginLeft+"px";
},
_outdentMargin : function(elDiv){
var elTmp = elDiv,
elParentNode, elInsertBefore, elNewParent, elInsertParent, oDoc, nCurMarginLeft;
while(elTmp){
if(elTmp.tagName && elTmp.tagName === "LI"){
elDiv = elTmp;
break;
}
elTmp = elTmp.parentNode;
}
if(elDiv.tagName === "LI"){
elParentNode = elDiv.parentNode;
elInsertBefore = elDiv.parentNode;
if(elDiv.previousSibling && elDiv.previousSibling.tagName && elDiv.previousSibling.tagName.match(/LI|UL|OL/)){
if(elDiv.nextSibling && elDiv.nextSibling.tagName && elDiv.nextSibling.tagName.match(/LI|UL|OL/)){
elNewParent = elParentNode.cloneNode(false);
while(elDiv.nextSibling){
elNewParent.insertBefore(elDiv.nextSibling, null);
}
elParentNode.parentNode.insertBefore(elNewParent, elParentNode.nextSibling);
elInsertBefore = elNewParent;
}else{
elInsertBefore = elParentNode.nextSibling;
}
}
elParentNode.parentNode.insertBefore(elDiv, elInsertBefore);
if(!elParentNode.innerHTML.match(/LI/i)){
elParentNode.parentNode.removeChild(elParentNode);
}
if(!elDiv.parentNode.tagName.match(/OL|UL/)){
elInsertParent = elDiv.parentNode;
elInsertBefore = elDiv;
oDoc = this.oApp.getWYSIWYGDocument();
elInsertParent = oDoc.createElement("P");
elInsertBefore = null;
elDiv.parentNode.insertBefore(elInsertParent, elDiv);
while(elDiv.firstChild){
elInsertParent.insertBefore(elDiv.firstChild, elInsertBefore);
}
elDiv.parentNode.removeChild(elDiv);
}
return;
}
nCurMarginLeft = parseInt(elDiv.style.marginLeft, 10);
if(!nCurMarginLeft){
nCurMarginLeft = 0;
}
nCurMarginLeft -= this.nIndentSpacing;
if(nCurMarginLeft < 0){
nCurMarginLeft = 0;
}
elDiv.style.marginLeft = nCurMarginLeft+"px";
},
_insertBlankLine : function(){
var oSelection = this.oApp.getSelection();
var elNode = oSelection.commonAncestorContainer;
this.elP1 = null;
this.elP2 = null;
while(elNode){
if(elNode.tagName == "BLOCKQUOTE"){
this.elP1 = jindo.$("<p>&nbsp;</p>", this.oApp.getWYSIWYGDocument());
elNode.parentNode.insertBefore(this.elP1, elNode);
this.elP2 = jindo.$("<p>&nbsp;</p>", this.oApp.getWYSIWYGDocument());
elNode.parentNode.insertBefore(this.elP2, elNode.nextSibling);
break;
}
elNode = elNode.parentNode;
}
if(!this.elP1 && !this.elP2){
elNode = oSelection.commonAncestorContainer;
elNode = (elNode.nodeType !== 1) ? elNode.parentNode : elNode;
var elPrev = elNode.previousSibling,
elNext = elNode.nextSibling;
if(elPrev && elPrev.tagName === "BLOCKQUOTE"){
this.elP1 = jindo.$("<p>&nbsp;</p>", this.oApp.getWYSIWYGDocument());
elPrev.parentNode.insertBefore(this.elP1, elPrev.nextSibling);
}
if(elNext && elNext.tagName === "BLOCKQUOTE"){
this.elP1 = jindo.$("<p>&nbsp;</p>", this.oApp.getWYSIWYGDocument());
elNext.parentNode.insertBefore(this.elP1, elNext);
}
}
},
_fixCorruptedBlockQuote : function(sTagName){
var aNodes = this.oApp.getWYSIWYGDocument().getElementsByTagName(sTagName),
elCorruptedBlockQuote, elTmpParent, elNewNode, aLists,
i, nLen, nPos, el, oSelection;
for(i=0, nLen=aNodes.length; i<nLen; i++){
if(aNodes[i].firstChild && aNodes[i].firstChild.tagName == sTagName){
elCorruptedBlockQuote = aNodes[i];
break;
}
}
if(!elCorruptedBlockQuote){return;}
elTmpParent = elCorruptedBlockQuote.parentNode;
nPos = this._getPosIdx(elCorruptedBlockQuote);
el = this.oApp.getWYSIWYGDocument().createElement("DIV");
el.innerHTML = elCorruptedBlockQuote.outerHTML.replace("<"+sTagName, "<BLOCKQUOTE");
elCorruptedBlockQuote.parentNode.insertBefore(el.firstChild, elCorruptedBlockQuote);
elCorruptedBlockQuote.parentNode.removeChild(elCorruptedBlockQuote);
elNewNode = elTmpParent.childNodes[nPos];
aLists = elNewNode.getElementsByTagName(sTagName);
for(i=0, nLen=aLists.length; i<nLen; i++){
if(aLists[i].childNodes.length<1){
aLists[i].parentNode.removeChild(aLists[i]);
}
}
oSelection = this.oApp.getEmptySelection();
oSelection.selectNodeContents(elNewNode);
oSelection.collapseToEnd();
oSelection.select();
},
_removeBlockQuote : function(){
var sVendorSpanClass = "Apple-style-span",
elVendorSpan,
aelVendorSpanDummy,
oSelection = this.oApp.getSelection(),
elNode = oSelection.commonAncestorContainer,
elChild = elNode,
elLi;
while(elNode){
if(elNode.tagName === "LI"){
elLi = elNode;
elNode = (elNode.style.cssText === "display: inline !important; ") ? elNode.parentNode : null;
}else if(elNode.tagName === "SPAN" && elNode.className === sVendorSpanClass){
elVendorSpan = elNode;
elNode = (!elLi) ? elNode.parentNode : null;
}else{
elNode = elNode.parentNode;
}
}
if(elLi && elVendorSpan){
elNode = elVendorSpan.parentNode;
elNode.replaceChild(elChild, elVendorSpan);
oSelection.selectNodeContents(elNode);
oSelection.collapseToEnd();
oSelection.select();
}
while(elNode){
if(elNode.tagName === "BLOCKQUOTE"){
aelVendorSpanDummy = elNode.getElementsByClassName(sVendorSpanClass);
for(var i = 0;(elVendorSpan = aelVendorSpanDummy[i]); i++){
elVendorSpan.parentNode.removeChild(elVendorSpan);
}
elNode = null;
}else{
elNode = elNode.parentNode;
}
}
},
_getPosIdx : function(refNode){
var idx = 0;
for(var node = refNode.previousSibling; node; node = node.previousSibling){idx++;}
return idx;
},
_countClickCr : function(sCommand) {
if (!sCommand.match(this.rxClickCr)) {
return;
}
this.oApp.exec('MSG_NOTIFY_CLICKCR', [sCommand.replace(/^insert/i, '')]);
},
_extendBlock : function(){
var oSelection = this.oApp.getSelection(),
oStartContainer = oSelection.startContainer,
oEndContainer = oSelection.endContainer,
aChildImg = [],
aSelectedImg = [],
oSelectionClone = oSelection.cloneRange();
if(!(oStartContainer === oEndContainer && oStartContainer.nodeType === 1 && oStartContainer.tagName === "P")){
return;
}
aChildImg = jindo.$A(oStartContainer.childNodes).filter(function(value, index, array){
return (value.nodeType === 1 && value.tagName === "IMG");
}).$value();
aSelectedImg = jindo.$A(oSelection.getNodes()).filter(function(value, index, array){
return (value.nodeType === 1 && value.tagName === "IMG");
}).$value();
if(aChildImg.length <= aSelectedImg.length){
return;
}
oSelection.selectNode(oStartContainer);
oSelection.select();
return oSelectionClone;
}
});
nhn.husky.SE_WYSIWYGStyler = jindo.$Class({
name : "SE_WYSIWYGStyler",
_sCursorHolder : "\uFEFF",
$init : function(){
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && htBrowser.version > 8){
this._sCursorHolder = "\u2060";
this.$ON_REGISTER_CONVERTERS = function(){
var rx2060 = /\u2060/g;
this.oApp.exec("ADD_CONVERTER", ["WYSIWYG_TO_IR", jindo.$Fn(function(sContents){
return sContents.replace(rx2060, "\uFEFF");
}, this).bind()]);
};
}
},
$PRECONDITION : function(sFullCommand, aArgs){
return (this.oApp.getEditingMode() == "WYSIWYG");
},
$ON_SET_WYSIWYG_STYLE : function(oStyles){
var oSelection = this.oApp.getSelection();
var htSelectedTDs = {};
this.oApp.exec("IS_SELECTED_TD_BLOCK",['bIsSelectedTd',htSelectedTDs]);
var bSelectedBlock = htSelectedTDs.bIsSelectedTd;
if(oSelection.collapsed && !bSelectedBlock){
this.oApp.exec("RECORD_UNDO_ACTION", ["FONT STYLE", {bMustBlockElement : true}]);
var oSpan, bNewSpan = false;
var elCAC = oSelection.commonAncestorContainer;
if(elCAC.nodeType == 3){
elCAC = elCAC.parentNode;
}
if(elCAC && oSelection._rxCursorHolder.test(elCAC.innerHTML)){
oSpan = oSelection._findParentSingleSpan(elCAC);
}
if(!oSpan){
oSpan = this.oApp.getWYSIWYGDocument().createElement("SPAN");
oSpan.innerHTML = this._sCursorHolder;
bNewSpan = true;
}else if(oSpan.innerHTML == ""){
oSpan.innerHTML = this._sCursorHolder;
}
var sValue;
for(var sName in oStyles){
sValue = oStyles[sName];
if(typeof sValue != "string"){
continue;
}
oSpan.style[sName] = sValue;
}
if(bNewSpan){
if(oSelection.startContainer.tagName == "BODY" && oSelection.startOffset === 0){
var oVeryFirstNode = oSelection._getVeryFirstRealChild(this.oApp.getWYSIWYGDocument().body);
var bAppendable = true;
var elTmp = oVeryFirstNode.cloneNode(false);
try{
elTmp.innerHTML = "test";
if(elTmp.innerHTML != "test"){
bAppendable = false;
}
}catch(e){
bAppendable = false;
}
if(bAppendable && elTmp.nodeType == 1 && elTmp.tagName == "BR"){
oSelection.selectNode(oVeryFirstNode);
oSelection.collapseToStart();
oSelection.insertNode(oSpan);
}else if(bAppendable && oVeryFirstNode.tagName != "IFRAME" && oVeryFirstNode.appendChild && typeof oVeryFirstNode.innerHTML == "string"){
oVeryFirstNode.appendChild(oSpan);
}else{
oSelection.selectNode(oVeryFirstNode);
oSelection.collapseToStart();
oSelection.insertNode(oSpan);
}
}else{
oSelection.collapseToStart();
oSelection.insertNode(oSpan);
}
}else{
oSelection = this.oApp.getEmptySelection();
}
if(!!oStyles.color){
oSelection._checkTextDecoration(oSpan);
}
oSelection.selectNodeContents(bNewSpan?oSpan:elCAC);
oSelection.collapseToEnd();
oSelection._window.focus();
oSelection._window.document.body.focus();
oSelection.select();
return;
}
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["FONT STYLE", {bMustBlockElement:true}]);
if(bSelectedBlock){
var aNodes;
this.oApp.exec("GET_SELECTED_TD_BLOCK",['aTdCells',htSelectedTDs]);
aNodes = htSelectedTDs.aTdCells;
for( var j = 0; j < aNodes.length ; j++){
oSelection.selectNodeContents(aNodes[j]);
oSelection.styleRange(oStyles);
oSelection.select();
}
} else {
var bCheckTextDecoration = !!oStyles.color;
var bIncludeLI = oStyles.fontSize || oStyles.fontFamily;
oSelection.styleRange(oStyles, null, null, bIncludeLI, bCheckTextDecoration);
if(jindo.$Agent().navigator().firefox){
var aStyleParents = oSelection.aStyleParents;
for(var i=0, nLen=aStyleParents.length; i<nLen; i++){
var elNode = aStyleParents[i];
if(elNode.nextSibling && elNode.nextSibling.tagName == "BR" && !elNode.nextSibling.nextSibling){
elNode.parentNode.removeChild(elNode.nextSibling);
}
}
}
oSelection._window.focus();
oSelection.select();
}
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["FONT STYLE", {bMustBlockElement:true}]);
}
});
nhn.husky.SE_WYSIWYGStyleGetter = jindo.$Class({
name : "SE_WYSIWYGStyleGetter",
hKeyUp : null,
getStyleInterval : 200,
oStyleMap : {
fontFamily : {
type : "Value",
css : "fontFamily"
},
fontSize : {
type : "Value",
css : "fontSize"
},
lineHeight : {
type : "Value",
css : "lineHeight",
converter : function(sValue, oStyle){
if(!sValue.match(/px$/)){
return sValue;
}
return Math.ceil((parseInt(sValue, 10)/parseInt(oStyle.fontSize, 10))*10)/10;
}
},
bold : {
command : "bold"
},
underline : {
command : "underline"
},
italic : {
command : "italic"
},
lineThrough : {
command : "strikethrough"
},
superscript : {
command : "superscript"
},
subscript : {
command : "subscript"
},
justifyleft : {
command : "justifyleft"
},
justifycenter : {
command : "justifycenter"
},
justifyright : {
command : "justifyright"
},
justifyfull : {
command : "justifyfull"
},
orderedlist : {
command : "insertorderedlist"
},
unorderedlist : {
command : "insertunorderedlist"
}
},
$init : function(){
this.oStyle = this._getBlankStyle();
},
$LOCAL_BEFORE_ALL : function(){
return (this.oApp.getEditingMode() == "WYSIWYG");
},
$ON_MSG_APP_READY : function(){
this.oDocument = this.oApp.getWYSIWYGDocument();
this.oApp.exec("ADD_APP_PROPERTY", ["getCurrentStyle", jindo.$Fn(this.getCurrentStyle, this).bind()]);
if(jindo.$Agent().navigator().safari || jindo.$Agent().navigator().chrome || jindo.$Agent().navigator().ie){
this.oStyleMap.textAlign = {
type : "Value",
css : "textAlign"
};
}
},
$ON_EVENT_EDITING_AREA_MOUSEUP : function(oEvnet){
this.oApp.exec("CHECK_STYLE_CHANGE");
},
$ON_EVENT_EDITING_AREA_KEYPRESS : function(oEvent){
var oKeyInfo;
if(this.oApp.oNavigator.firefox){
oKeyInfo = oEvent.key();
if(oKeyInfo.ctrl && oKeyInfo.keyCode == 97){
return;
}
}
if(this.bAllSelected){
this.bAllSelected = false;
return;
}
this.oApp.exec("CHECK_STYLE_CHANGE");
},
$ON_EVENT_EDITING_AREA_KEYDOWN : function(oEvent){
var oKeyInfo = oEvent.key();
if((this.oApp.oNavigator.ie || this.oApp.oNavigator.firefox) && oKeyInfo.ctrl && oKeyInfo.keyCode == 65){
this.oApp.exec("RESET_STYLE_STATUS");
this.bAllSelected = true;
return;
}
if(!(oKeyInfo.keyCode == 8 || (oKeyInfo.keyCode >= 33 && oKeyInfo.keyCode <= 40) || oKeyInfo.keyCode == 45 || oKeyInfo.keyCode == 46)) return;
if(oKeyInfo.shift && oKeyInfo.keyCode === 35){
this.oApp.exec("RESET_STYLE_STATUS");
this.bAllSelected = true;
return;
}
if(this.bAllSelected){
if(this.oApp.oNavigator.firefox){
return;
}
this.bAllSelected = false;
return;
}
this.oApp.exec("CHECK_STYLE_CHANGE");
},
$ON_CHECK_STYLE_CHANGE : function(){
this._getStyle();
},
$ON_RESET_STYLE_STATUS : function(){
this.oStyle = this._getBlankStyle();
var oBodyStyle = this._getStyleOf(this.oApp.getWYSIWYGDocument().body);
this.oStyle.fontFamily = oBodyStyle.fontFamily;
this.oStyle.fontSize = oBodyStyle.fontSize;
this.oStyle["justifyleft"]="@^";
for(var sAttributeName in this.oStyle){
this.oApp.exec("MSG_STYLE_CHANGED", [sAttributeName, this.oStyle[sAttributeName]]);
}
},
getCurrentStyle : function(){
return this.oStyle;
},
_check_style_change : function(){
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
_getBlankStyle : function(){
var oBlankStyle = {};
for(var attributeName in this.oStyleMap){
if(this.oStyleMap[attributeName].type == "Value"){
oBlankStyle[attributeName] = "";
}else{
oBlankStyle[attributeName] = 0;
}
}
return oBlankStyle;
},
_getStyle : function(){
var oStyle;
if(nhn.CurrentSelection.isCollapsed()){
oStyle = this._getStyleOf(nhn.CurrentSelection.getCommonAncestorContainer());
}else{
var oSelection = this.oApp.getSelection();
var funcFilter = function(oNode){
if (!oNode.childNodes || oNode.childNodes.length == 0)
return true;
else
return false;
}
var aBottomNodes = oSelection.getNodes(false, funcFilter);
if(aBottomNodes.length == 0){
oStyle = this._getStyleOf(oSelection.commonAncestorContainer);
}else{
oStyle = this._getStyleOf(aBottomNodes[0]);
}
}
for(attributeName in oStyle){
if(this.oStyleMap[attributeName].converter){
oStyle[attributeName] = this.oStyleMap[attributeName].converter(oStyle[attributeName], oStyle);
}
if(this.oStyle[attributeName] != oStyle[attributeName]){
if((typeof(document.body.currentStyle) != "object") && (typeof(getComputedStyle) == "function")){
if((attributeName == "fontSize") && (this.oStyle["fontFamily"] != oStyle["fontFamily"])){
continue;
}
}
this.oApp.exec("MSG_STYLE_CHANGED", [attributeName, oStyle[attributeName]]);
}
}
this.oStyle = oStyle;
},
_getStyleOf : function(oNode){
var oStyle = this._getBlankStyle();
if(!oNode){
return oStyle;
}
if( oNode.nodeType == 3 ){
oNode = oNode.parentNode;
}else if( oNode.nodeType == 9 ){
oNode = oNode.body;
}
var welNode = jindo.$Element(oNode);
var attribute, cssName;
for(var styleName in this.oStyle){
attribute = this.oStyleMap[styleName];
if(attribute.type && attribute.type == "Value"){
try{
if(attribute.css){
var sValue = welNode.css(attribute.css);
if(styleName == "fontFamily"){
sValue = sValue.split(/,/)[0];
}
oStyle[styleName] = sValue;
} else if(attribute.command){
oStyle[styleName] = this.oDocument.queryCommandState(attribute.command);
} else {
}
}catch(e){}
}else{
if(attribute.command){
try{
if(this.oDocument.queryCommandState(attribute.command)){
oStyle[styleName] = "@^";
}else{
oStyle[styleName] = "@-";
}
}catch(e){}
}else{
}
}
}
switch(oStyle["textAlign"]){
case "left":
oStyle["justifyleft"]="@^";
break;
case "center":
oStyle["justifycenter"]="@^";
break;
case "right":
oStyle["justifyright"]="@^";
break;
case "justify":
oStyle["justifyfull"]="@^";
break;
}
if(oStyle["justifyleft"]=="@-" && oStyle["justifycenter"]=="@-" && oStyle["justifyright"]=="@-" && oStyle["justifyfull"]=="@-"){oStyle["justifyleft"]="@^";}
return oStyle;
}
});
nhn.husky.SE2M_FontSizeWithLayerUI = jindo.$Class({
name : "SE2M_FontSizeWithLayerUI",
$init : function(elAppContainer){
this._assignHTMLElements(elAppContainer);
},
_assignHTMLElements : function(elAppContainer){
this.oDropdownLayer = jindo.$$.getSingle("DIV.husky_se_fontSize_layer", elAppContainer);
this.elFontSizeLabel = jindo.$$.getSingle("SPAN.husky_se2m_current_fontSize", elAppContainer);
this.aLIFontSizes = jindo.$A(jindo.$$("LI", this.oDropdownLayer)).filter(function(v,i,a){return (v.firstChild != null);})._array;
this.sDefaultText = this.elFontSizeLabel.innerHTML;
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["fontSize", "click", "SE2M_TOGGLE_FONTSIZE_LAYER"]);
this.oApp.exec("SE2_ATTACH_HOVER_EVENTS", [this.aLIFontSizes]);
for(var i=0; i<this.aLIFontSizes.length; i++){
this.oApp.registerBrowserEvent(this.aLIFontSizes[i], "click", "SET_FONTSIZE", [this._getFontSizeFromLI(this.aLIFontSizes[i])]);
}
},
$ON_SE2M_TOGGLE_FONTSIZE_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.oDropdownLayer, null, "SELECT_UI", ["fontSize"], "DESELECT_UI", ["fontSize"]]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['size']);
},
_rxPX : /px$/i,
_rxPT : /pt$/i,
$ON_MSG_STYLE_CHANGED : function(sAttributeName, sAttributeValue){
if(sAttributeName == "fontSize"){
if(this._rxPX.test(sAttributeValue)){
var num = parseFloat(sAttributeValue.replace(this._rxPX, ""));
if(num > 0){
sAttributeValue = num * 72 / 96 + "pt";
}else{
sAttributeValue = this.sDefaultText;
}
}
if(this._rxPT.test(sAttributeValue)){
var num = parseFloat(sAttributeValue.replace(this._rxPT, ""));
var integerPart = Math.floor(num);
var decimalPart = num - integerPart;
if(decimalPart >= 0 && decimalPart < 0.25){
num = integerPart + 0;
}else if(decimalPart >= 0.25 && decimalPart < 0.75){
num = integerPart + 0.5;
}else{
num = integerPart + 1;
}
sAttributeValue = num + "pt";
}
if(!sAttributeValue){
sAttributeValue = this.sDefaultText;
}
var elLi = this._getMatchingLI(sAttributeValue);
this._clearFontSizeSelection();
if(elLi){
this.elFontSizeLabel.innerHTML = sAttributeValue;
jindo.$Element(elLi).addClass("active");
}else{
this.elFontSizeLabel.innerHTML = sAttributeValue;
}
}
},
$ON_SET_FONTSIZE : function(sFontSize){
if(!sFontSize){return;}
this.oApp.exec("SET_WYSIWYG_STYLE", [{"fontSize":sFontSize}]);
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
_getMatchingLI : function(sFontSize){
var elLi;
sFontSize = sFontSize.toLowerCase();
for(var i=0; i<this.aLIFontSizes.length; i++){
elLi = this.aLIFontSizes[i];
if(this._getFontSizeFromLI(elLi).toLowerCase() == sFontSize){return elLi;}
}
return null;
},
_getFontSizeFromLI : function(elLi){
return elLi.firstChild.firstChild.style.fontSize;
},
_clearFontSizeSelection : function(elLi){
for(var i=0; i<this.aLIFontSizes.length; i++){
jindo.$Element(this.aLIFontSizes[i]).removeClass("active");
}
}
});
nhn.husky.SE2M_LineStyler = jindo.$Class({
name : "SE2M_LineStyler",
$BEFORE_MSG_APP_READY : function() {
this.oApp.exec("ADD_APP_PROPERTY", ["getLineStyle", jindo.$Fn(this.getLineStyle, this).bind()]);
},
$ON_SET_LINE_STYLE : function(sStyleName, styleValue, htOptions){
this.oSelection = this.oApp.getSelection();
var nodes = this._getSelectedNodes(false);
this.setLineStyle(sStyleName, styleValue, htOptions, nodes);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
$ON_SET_LINE_BLOCK_STYLE : function(sStyleName, styleValue, htOptions){
this.oSelection = this.oApp.getSelection();
this.setLineBlockStyle(sStyleName, styleValue, htOptions);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
_getSelectedTDs : function(){
var htSelectedTDs = {};
this.oApp.exec("GET_SELECTED_TD_BLOCK",['aTdCells',htSelectedTDs]);
return htSelectedTDs.aTdCells || [];
},
getLineStyle : function(sStyle){
var nodes = this._getSelectedNodes(false);
var curWrapper, prevWrapper;
var sCurStyle, sStyleValue;
if(nodes.length === 0){return null;}
var iLength = nodes.length;
if(iLength === 0){
sStyleValue = null;
}else{
prevWrapper = this._getLineWrapper(nodes[0]);
sStyleValue = this._getWrapperLineStyle(sStyle, prevWrapper);
}
var firstNode = this.oSelection.getStartNode();
if(sStyleValue != null){
for(var i=1; i<iLength; i++){
if(this._isChildOf(nodes[i], curWrapper)){continue;}
if(!nodes[i]){continue;}
curWrapper = this._getLineWrapper(nodes[i]);
if(curWrapper == prevWrapper){continue;}
sCurStyle = this._getWrapperLineStyle(sStyle, curWrapper);
if(sCurStyle != sStyleValue){
sStyleValue = null;
break;
}
prevWrapper = curWrapper;
}
}
curWrapper = this._getLineWrapper(nodes[iLength-1]);
var lastNode = this.oSelection.getEndNode();
setTimeout(jindo.$Fn(function(firstNode, lastNode){
var aNodes = this._getSelectedTDs();
if(aNodes.length > 0){
var elFirstTD = nhn.husky.SE2M_Utils.findAncestorByTagName("TD", firstNode);
var elLastTD = nhn.husky.SE2M_Utils.findAncestorByTagName("TD", lastNode);
firstNode = (elFirstTD || !firstNode) ? aNodes[0].firstChild : firstNode;
lastNode = (elLastTD || !lastNode) ? aNodes[aNodes.length - 1].lastChild : lastNode;
}
this.oSelection.setEndNodes(firstNode, lastNode);
this.oSelection.select();
this.oApp.exec("CHECK_STYLE_CHANGE", []);
}, this).bind(firstNode, lastNode), 0);
return sStyleValue;
},
setLineStyle : function(sStyleName, styleValue, htOptions, nodes){
thisRef = this;
var bWrapperCreated = false;
function _setLineStyle(div, sStyleName, styleValue){
if(!div){
bWrapperCreated = true;
try{
div = thisRef.oSelection.surroundContentsWithNewNode("P");
}catch(e){
div = thisRef.oSelection.surroundContentsWithNewNode("DIV");
}
}
if(typeof styleValue == "function"){
styleValue(div);
}else{
div.style[sStyleName] = styleValue;
}
if(div.childNodes.length === 0){
div.innerHTML = "&nbsp;";
}
return div;
}
function isInBody(node){
while(node && node.tagName != "BODY"){
node = nhn.DOMFix.parentNode(node);
}
if(!node){return false;}
return true;
}
if(nodes.length === 0){
return;
}
var curWrapper, prevWrapper;
var iLength = nodes.length;
if((!htOptions || !htOptions["bDontAddUndoHistory"])){
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["LINE STYLE"]);
}
prevWrapper = this._getLineWrapper(nodes[0]);
prevWrapper = _setLineStyle(prevWrapper, sStyleName, styleValue);
var startNode = prevWrapper;
var endNode = prevWrapper;
for(var i=1; i<iLength; i++){
try{
if(!isInBody(nhn.DOMFix.parentNode(nodes[i]))){continue;}
}catch(e){continue;}
if(this._isChildOf(nodes[i], curWrapper)){continue;}
curWrapper = this._getLineWrapper(nodes[i]);
if(curWrapper == prevWrapper){continue;}
curWrapper = _setLineStyle(curWrapper, sStyleName, styleValue);
prevWrapper = curWrapper;
}
endNode = curWrapper || startNode;
if(bWrapperCreated && (!htOptions || !htOptions.bDoNotSelect)) {
setTimeout(jindo.$Fn(function(startNode, endNode, htOptions){
if(startNode == endNode){
this.oSelection.selectNodeContents(startNode);
if(startNode.childNodes.length==1 && startNode.firstChild.tagName == "BR"){
this.oSelection.collapseToStart();
}
}else{
this.oSelection.setEndNodes(startNode, endNode);
}
this.oSelection.select();
if((!htOptions || !htOptions["bDontAddUndoHistory"])){
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["LINE STYLE"]);
}
}, this).bind(startNode, endNode, htOptions), 0);
}
},
setLineBlockStyle : function(sStyleName, styleValue, htOptions) {
var aTempNodes = [];
var aTextnodes = [];
var aNodes = this._getSelectedTDs();
for( var j = 0; j < aNodes.length ; j++){
this.oSelection.selectNode(aNodes[j]);
aTempNodes = this.oSelection.getNodes();
for(var k = 0, m = 0; k < aTempNodes.length ; k++){
if(aTempNodes[k].nodeType == 3 || (aTempNodes[k].tagName == "BR" && k == 0)) {
aTextnodes[m] = aTempNodes[k];
m ++;
}
}
this.setLineStyle(sStyleName, styleValue, htOptions, aTextnodes);
aTempNodes = aTextnodes = [];
}
},
getTextNodes : function(bSplitTextEndNodes, oSelection){
var txtFilter = function(oNode){
if((oNode.nodeType == 3 && oNode.nodeValue != "\n" && oNode.nodeValue != "" && oNode.nodeValue != "\uFEFF") || (oNode.tagName == "LI" && oNode.innerHTML == "") || (oNode.tagName == "P" && oNode.innerHTML == "")){
return true;
}else{
return false;
}
};
return oSelection.getNodes(bSplitTextEndNodes, txtFilter);
},
_getSelectedNodes : function(bDontUpdate){
if(!bDontUpdate){
this.oSelection = this.oApp.getSelection();
}
if(this.oSelection.endContainer.tagName == "LI" && this.oSelection.endOffset == 0 && this.oSelection.endContainer.innerHTML == ""){
this.oSelection.setEndAfter(this.oSelection.endContainer);
}
if(this.oSelection.collapsed){
var aNodes = this._getSelectedTDs();
if(aNodes.length > 0){
return [aNodes[0].firstChild, aNodes[aNodes.length - 1].lastChild];
}
this.oSelection.selectNode(this.oSelection.commonAncestorContainer);
}
var nodes = this.getTextNodes(false, this.oSelection);
if(nodes.length === 0){
var tmp = this.oSelection.getStartNode();
if(tmp){
nodes[0] = tmp;
}else{
var elTmp = this.oSelection._document.createTextNode("\u00A0");
this.oSelection.insertNode(elTmp);
nodes = [elTmp];
}
}
return nodes;
},
_getWrapperLineStyle : function(sStyle, div){
var sStyleValue = null;
if(div && div.style[sStyle]){
sStyleValue = div.style[sStyle];
}else{
div = this.oSelection.commonAncesterContainer;
while(div && !this.oSelection.rxLineBreaker.test(div.tagName)){
if(div && div.style[sStyle]){
sStyleValue = div.style[sStyle];
break;
}
div = nhn.DOMFix.parentNode(div);
}
}
return sStyleValue;
},
_isChildOf : function(node, container){
while(node && node.tagName != "BODY"){
if(node == container){return true;}
node = nhn.DOMFix.parentNode(node);
}
return false;
},
_getLineWrapper : function(node){
var oTmpSelection = this.oApp.getEmptySelection();
oTmpSelection.selectNode(node);
var oLineInfo = oTmpSelection.getLineInfo();
var oStart = oLineInfo.oStart;
var oEnd = oLineInfo.oEnd;
var a, b;
var breakerA, breakerB;
var div = null;
a = oStart.oNode;
breakerA = oStart.oLineBreaker;
b = oEnd.oNode;
breakerB = oEnd.oLineBreaker;
this.oSelection.setEndNodes(a, b);
if(breakerA == breakerB){
if(breakerA.tagName == "P" || breakerA.tagName == "DIV" || breakerA.tagName == "LI"){
div = breakerA;
}else{
this.oSelection.setEndNodes(breakerA.firstChild, breakerA.lastChild);
}
}
return div;
}
});
nhn.husky.SE2M_LineHeightWithLayerUI = jindo.$Class({
name : "SE2M_LineHeightWithLayerUI",
MIN_LINE_HEIGHT : 50,
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["lineHeight", "click", "SE2M_TOGGLE_LINEHEIGHT_LAYER"]);
this.oApp.registerLazyMessage(["SE2M_TOGGLE_LINEHEIGHT_LAYER"], ["hp_SE2M_LineHeightWithLayerUI$Lazy.js"]);
}
});
nhn.husky.SE2M_ColorPalette = jindo.$Class({
name : "SE2M_ColorPalette",
elAppContainer : null,
bUseRecentColor : false,
nLimitRecentColor : 17,
rxRGBColorPattern : /rgb\((\d+), ?(\d+), ?(\d+)\)/i,
rxColorPattern : /^#?[0-9a-fA-F]{6}$|^rgb\(\d+, ?\d+, ?\d+\)$/i,
aRecentColor : [],
URL_COLOR_LIST : "",
URL_COLOR_ADD : "",
URL_COLOR_UPDATE : "",
sRecentColorTemp : "<li><button type=\"button\" title=\"{RGB_CODE}\" style=\"background:{RGB_CODE}\"><span><span>{RGB_CODE}</span></span></button></li>",
$init : function(elAppContainer){
this.elAppContainer = elAppContainer;
},
$ON_MSG_APP_READY : function(){},
_assignHTMLElements : function(oAppContainer){
var htConfiguration = nhn.husky.SE2M_Configuration.SE2M_ColorPalette;
if(htConfiguration){
this.bUseRecentColor = htConfiguration.bUseRecentColor || false;
this.URL_COLOR_ADD = htConfiguration.addColorURL || "http://api.se2.naver.com/1/colortable/TextAdd.nhn";
this.URL_COLOR_UPDATE = htConfiguration.updateColorURL || "http://api.se2.naver.com/1/colortable/TextUpdate.nhn";
this.URL_COLOR_LIST = htConfiguration.colorListURL || "http://api.se2.naver.com/1/colortable/TextList.nhn";
}
this.elColorPaletteLayer = jindo.$$.getSingle("DIV.husky_se2m_color_palette", oAppContainer);
this.elColorPaletteLayerColorPicker = jindo.$$.getSingle("DIV.husky_se2m_color_palette_colorpicker", this.elColorPaletteLayer);
this.elRecentColorForm = jindo.$$.getSingle("form", this.elColorPaletteLayerColorPicker);
this.elBackgroundColor = jindo.$$.getSingle("ul.husky_se2m_bgcolor_list", oAppContainer);
this.elInputColorCode = jindo.$$.getSingle("INPUT.husky_se2m_cp_colorcode", this.elColorPaletteLayerColorPicker);
this.elPreview = jindo.$$.getSingle("SPAN.husky_se2m_cp_preview", this.elColorPaletteLayerColorPicker);
this.elCP_ColPanel = jindo.$$.getSingle("DIV.husky_se2m_cp_colpanel", this.elColorPaletteLayerColorPicker);
this.elCP_HuePanel = jindo.$$.getSingle("DIV.husky_se2m_cp_huepanel", this.elColorPaletteLayerColorPicker);
this.elCP_ColPanel.style.position = "relative";
this.elCP_HuePanel.style.position = "relative";
this.elColorPaletteLayerColorPicker.style.display = "none";
this.elMoreBtn = jindo.$$.getSingle("BUTTON.husky_se2m_color_palette_more_btn", this.elColorPaletteLayer);
this.welMoreBtn = jindo.$Element(this.elMoreBtn);
this.elOkBtn = jindo.$$.getSingle("BUTTON.husky_se2m_color_palette_ok_btn", this.elColorPaletteLayer);
if(this.bUseRecentColor){
this.elColorPaletteLayerRecent = jindo.$$.getSingle("DIV.husky_se2m_color_palette_recent", this.elColorPaletteLayer);
this.elRecentColor = jindo.$$.getSingle("ul.se2_pick_color", this.elColorPaletteLayerRecent);
this.elDummyNode = jindo.$$.getSingle("ul.se2_pick_color > li", this.elColorPaletteLayerRecent) || null;
this.elColorPaletteLayerRecent.style.display = "none";
}
},
$LOCAL_BEFORE_FIRST : function(){
this._assignHTMLElements(this.elAppContainer);
if(this.elDummyNode){
jindo.$Element(jindo.$$.getSingle("ul.se2_pick_color > li", this.elColorPaletteLayerRecent)).leave();
}
if( this.bUseRecentColor ){
this._ajaxRecentColor(this._ajaxRecentColorCallback);
}
this.oApp.registerBrowserEvent(this.elColorPaletteLayer, "click", "EVENT_CLICK_COLOR_PALETTE");
if(!this.oApp.bMobile){
this.oApp.registerBrowserEvent(this.elBackgroundColor, "mouseover", "EVENT_MOUSEOVER_COLOR_PALETTE");
this.oApp.registerBrowserEvent(this.elColorPaletteLayer, "mouseover", "EVENT_MOUSEOVER_COLOR_PALETTE");
this.oApp.registerBrowserEvent(this.elBackgroundColor, "mouseout", "EVENT_MOUSEOUT_COLOR_PALETTE");
this.oApp.registerBrowserEvent(this.elColorPaletteLayer, "mouseout", "EVENT_MOUSEOUT_COLOR_PALETTE");
}
},
$ON_EVENT_MOUSEOVER_COLOR_PALETTE : function(oEvent){
var elHovered = oEvent.element;
while(elHovered && elHovered.tagName && elHovered.tagName.toLowerCase() != "li"){
elHovered = elHovered.parentNode;
}
if(!elHovered || !elHovered.nodeType || elHovered.nodeType == 9){return;}
if(elHovered.className == "" || (!elHovered.className) || typeof(elHovered.className) == 'undefined'){jindo.$Element(elHovered).addClass("hover");}
},
$ON_EVENT_MOUSEOUT_COLOR_PALETTE : function(oEvent){
var elHovered = oEvent.element;
while(elHovered && elHovered.tagName && elHovered.tagName.toLowerCase() != "li"){
elHovered = elHovered.parentNode;
}
if(!elHovered){return;}
if(elHovered.className == "hover"){jindo.$Element(elHovered).removeClass("hover");}
},
$ON_EVENT_CLICK_COLOR_PALETTE : function(oEvent){
var elClicked = oEvent.element;
while(elClicked.tagName == "SPAN"){elClicked = elClicked.parentNode;}
if(elClicked.tagName && elClicked.tagName == "BUTTON"){
if(elClicked == this.elMoreBtn){
this.oApp.exec("TOGGLE_COLOR_PICKER");
return;
}
this.oApp.exec("APPLY_COLOR", [elClicked]);
}
},
$ON_APPLY_COLOR : function(elButton){
var sColorCode = this.elInputColorCode.value,
welColorParent = null;
if(sColorCode.indexOf("#") == -1){
sColorCode = "#" + sColorCode;
this.elInputColorCode.value = sColorCode;
}
if(elButton == this.elOkBtn){
if(!this._verifyColorCode(sColorCode)){
this.elInputColorCode.value = "";
alert(this.oApp.$MSG("SE_Color.invalidColorCode"));
this.elInputColorCode.focus();
return;
}
this.oApp.exec("COLOR_PALETTE_APPLY_COLOR", [sColorCode,true]);
return;
}
welColorParent = jindo.$Element(elButton.parentNode.parentNode.parentNode);
sColorCode = elButton.title;
if(welColorParent.hasClass("husky_se2m_color_palette")){			
this.oApp.exec("COLOR_PALETTE_APPLY_COLOR", [sColorCode, nhn.husky.SE2M_Configuration.SE2M_ColorPalette.bAddRecentColorFromDefault]);
}else if(welColorParent.hasClass("husky_se2m_color_palette_recent")){
this.oApp.exec("COLOR_PALETTE_APPLY_COLOR", [sColorCode,true]);
}
},
$ON_RESET_COLOR_PALETTE : function(){
this._initColor();
},
$ON_TOGGLE_COLOR_PICKER : function(){
if(this.elColorPaletteLayerColorPicker.style.display == "none"){
this.oApp.exec("SHOW_COLOR_PICKER");
}else{
this.oApp.exec("HIDE_COLOR_PICKER");
}
},
$ON_SHOW_COLOR_PICKER : function(){
this.elColorPaletteLayerColorPicker.style.display = "";
this.cpp = new nhn.ColorPicker(this.elCP_ColPanel, {huePanel:this.elCP_HuePanel});
var fn = jindo.$Fn(function(oEvent) {
this.elPreview.style.backgroundColor = oEvent.hexColor;
this.elInputColorCode.value = oEvent.hexColor;
}, this).bind();
this.cpp.attach("colorchange", fn);
this.$ON_SHOW_COLOR_PICKER = this._showColorPickerMain;
this.$ON_SHOW_COLOR_PICKER();
},
$ON_HIDE_COLOR_PICKER : function(){
this.elColorPaletteLayerColorPicker.style.display = "none";
this.welMoreBtn.addClass("se2_view_more");
this.welMoreBtn.removeClass("se2_view_more2");
},
$ON_SHOW_COLOR_PALETTE : function(sCallbackCmd, oLayerContainer){
this.sCallbackCmd = sCallbackCmd;
this.oLayerContainer = oLayerContainer;
this.oLayerContainer.insertBefore(this.elColorPaletteLayer, null);
this.elColorPaletteLayer.style.display = "block";
this.oApp.delayedExec("POSITION_TOOLBAR_LAYER", [this.elColorPaletteLayer.parentNode.parentNode], 0);
},
$ON_HIDE_COLOR_PALETTE : function(){
this.elColorPaletteLayer.style.display = "none";
},
$ON_COLOR_PALETTE_APPLY_COLOR : function(sColorCode , bAddRecentColor){
bAddRecentColor = (!bAddRecentColor)? false : bAddRecentColor;
sColorCode = this._getHexColorCode(sColorCode);
if( this.bUseRecentColor && !!bAddRecentColor ){
this.oApp.exec("ADD_RECENT_COLOR", [sColorCode]);
}
this.oApp.exec(this.sCallbackCmd, [sColorCode]);
},
$ON_EVENT_MOUSEUP_COLOR_PALETTE : function(oEvent){
var elButton = oEvent.element;
if(! elButton.style.backgroundColor){return;}
this.oApp.exec("COLOR_PALETTE_APPLY_COLOR", [elButton.style.backgroundColor,false]);
},
$ON_ADD_RECENT_COLOR : function(sRGBCode){
var bAdd = (this.aRecentColor.length === 0);
this._addRecentColor(sRGBCode);
if(bAdd){
this._ajaxAddColor();
}else{
this._ajaxUpdateColor();
}
this._redrawRecentColorElement();
},
_verifyColorCode : function(sColorCode){
return this.rxColorPattern.test(sColorCode);
},
_getHexColorCode : function(sColorCode){
if(this.rxRGBColorPattern.test(sColorCode)){
var dec2Hex = function(sDec){
var sTmp = parseInt(sDec, 10).toString(16);
if(sTmp.length<2){sTmp = "0"+sTmp;}
return sTmp.toUpperCase();
};
var sR = dec2Hex(RegExp.$1);
var sG = dec2Hex(RegExp.$2);
var sB = dec2Hex(RegExp.$3);
sColorCode = "#"+sR+sG+sB;
}
return sColorCode;
},
_addRecentColor : function(sRGBCode){
var waRecentColor = jindo.$A(this.aRecentColor);
waRecentColor = waRecentColor.refuse(sRGBCode);
waRecentColor.unshift(sRGBCode);
if(waRecentColor.length() > this.nLimitRecentColor){
waRecentColor.length(this.nLimitRecentColor);
}
this.aRecentColor = waRecentColor.$value();
},
_redrawRecentColorElement : function(){
var aRecentColorHtml = [],
nRecentColor = this.aRecentColor.length,
i;
if(nRecentColor === 0){
return;
}
for(i=0; i<nRecentColor; i++){
aRecentColorHtml.push(this.sRecentColorTemp.replace(/\{RGB_CODE\}/gi, this.aRecentColor[i]));
}
this.elRecentColor.innerHTML = aRecentColorHtml.join("");
this.elColorPaletteLayerRecent.style.display = "block";
},
_ajaxAddColor : function(){
jindo.$Ajax(this.URL_COLOR_ADD, {
type : "jsonp",
onload: function(){}
}).request({
text_key : "colortable",
text_data : this.aRecentColor.join(",")
});
},
_ajaxUpdateColor : function(){
jindo.$Ajax(this.URL_COLOR_UPDATE, {
type : "jsonp",
onload: function(){}
}).request({
text_key : "colortable",
text_data : this.aRecentColor.join(",")
});
},
_showColorPickerMain : function(){
this._initColor();
this.elColorPaletteLayerColorPicker.style.display = "";
this.welMoreBtn.removeClass("se2_view_more");
this.welMoreBtn.addClass("se2_view_more2");
},
_initColor : function(){
if(this.cpp){this.cpp.rgb({r:0,g:0,b:0});}
this.elPreview.style.backgroundColor = '#'+'000000';
this.elInputColorCode.value = '#'+'000000';
this.oApp.exec("HIDE_COLOR_PICKER");
},
_ajaxRecentColor : function(fCallback){
jindo.$Ajax(this.URL_COLOR_LIST, {
type : "jsonp",
onload : jindo.$Fn(fCallback, this).bind()
}).request();
},
_ajaxRecentColorCallback : function(htResponse){
var aColorList = htResponse.json()["result"],
waColorList,
i, nLen;
if(!aColorList || !!aColorList.error){
return;
}
waColorList = jindo.$A(aColorList).filter(this._verifyColorCode, this);
if(waColorList.length() > this.nLimitRecentColor){
waColorList.length(this.nLimitRecentColor);
}
aColorList = waColorList.reverse().$value();
for(i = 0, nLen = aColorList.length; i < nLen; i++){
this._addRecentColor(this._getHexColorCode(aColorList[i]));
}
this._redrawRecentColorElement();
}
}).extend(jindo.Component);
nhn.husky.SE2M_FontColor = jindo.$Class({
name : "SE2M_FontColor",
rxColorPattern : /^#?[0-9a-fA-F]{6}$|^rgb\(\d+, ?\d+, ?\d+\)$/i,
$init : function(elAppContainer){
this._assignHTMLElements(elAppContainer);
},
_assignHTMLElements : function(elAppContainer){
this.elLastUsed = jindo.$$.getSingle("SPAN.husky_se2m_fontColor_lastUsed", elAppContainer);
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_se2m_fontcolor_layer", elAppContainer);
this.elPaletteHolder = jindo.$$.getSingle("DIV.husky_se2m_fontcolor_paletteHolder", this.elDropdownLayer);
this._setLastUsedFontColor("#000000");
},
$BEFORE_MSG_APP_READY : function() {
this.oApp.exec("ADD_APP_PROPERTY", ["getLastUsedFontColor", jindo.$Fn(this.getLastUsedFontColor, this).bind()]);
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["fontColorA", "click", "APPLY_LAST_USED_FONTCOLOR"]);
this.oApp.exec("REGISTER_UI_EVENT", ["fontColorB", "click", "TOGGLE_FONTCOLOR_LAYER"]);
this.oApp.registerLazyMessage(["APPLY_LAST_USED_FONTCOLOR", "TOGGLE_FONTCOLOR_LAYER"], ["hp_SE2M_FontColor$Lazy.js"]);
},
_setLastUsedFontColor : function(sFontColor){
this.sLastUsedColor = sFontColor;
this.elLastUsed.style.backgroundColor = this.sLastUsedColor;
},
getLastUsedFontColor : function(){
return (!!this.sLastUsedColor) ? this.sLastUsedColor : '#000000';
}
});
nhn.husky.SE2M_BGColor = jindo.$Class({
name : "SE2M_BGColor",
rxColorPattern : /^#?[0-9a-fA-F]{6}$|^rgb\(\d+, ?\d+, ?\d+\)$/i,
$init : function(elAppContainer){
this._assignHTMLElements(elAppContainer);
},
_assignHTMLElements : function(elAppContainer){
this.elLastUsed = jindo.$$.getSingle("SPAN.husky_se2m_BGColor_lastUsed", elAppContainer);
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_se2m_BGColor_layer", elAppContainer);
this.elBGColorList = jindo.$$.getSingle("UL.husky_se2m_bgcolor_list", elAppContainer);
this.elPaletteHolder = jindo.$$.getSingle("DIV.husky_se2m_BGColor_paletteHolder", this.elDropdownLayer);
this._setLastUsedBGColor("#777777");
},
$BEFORE_MSG_APP_READY : function() {
this.oApp.exec("ADD_APP_PROPERTY", ["getLastUsedBackgroundColor", jindo.$Fn(this.getLastUsedBGColor, this).bind()]);
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["BGColorA", "click", "APPLY_LAST_USED_BGCOLOR"]);
this.oApp.exec("REGISTER_UI_EVENT", ["BGColorB", "click", "TOGGLE_BGCOLOR_LAYER"]);
this.oApp.registerBrowserEvent(this.elBGColorList, "click", "EVENT_APPLY_BGCOLOR", []);
this.oApp.registerLazyMessage(["APPLY_LAST_USED_BGCOLOR", "TOGGLE_BGCOLOR_LAYER"], ["hp_SE2M_BGColor$Lazy.js"]);
},
_setLastUsedBGColor : function(sBGColor){
this.sLastUsedColor = sBGColor;
this.elLastUsed.style.backgroundColor = this.sLastUsedColor;
},
getLastUsedBGColor : function(){
return (!!this.sLastUsedColor) ? this.sLastUsedColor : '#777777';
}
});
nhn.husky.SE2M_Hyperlink = jindo.$Class({
name : "SE2M_Hyperlink",
sATagMarker : "HTTP://HUSKY_TMP.MARKER/",
_assignHTMLElements : function(elAppContainer){
this.oHyperlinkButton = jindo.$$.getSingle("li.husky_seditor_ui_hyperlink", elAppContainer);
this.oHyperlinkLayer = jindo.$$.getSingle("div.se2_layer", this.oHyperlinkButton);
this.oLinkInput = jindo.$$.getSingle("INPUT[type=text]", this.oHyperlinkLayer);
this.oBtnConfirm = jindo.$$.getSingle("button.se2_apply", this.oHyperlinkLayer);
this.oBtnCancel = jindo.$$.getSingle("button.se2_cancel", this.oHyperlinkLayer);
this.oCbNewWin = jindo.$$.getSingle("INPUT[type=checkbox]", this.oHyperlinkLayer) || null;
},
_generateAutoLink : function(sAll, sBreaker, sURL, sWWWURL, sHTTPURL) {
sBreaker = sBreaker || "";
var sResult;
if (sWWWURL){
var exp = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?)/gi;
if( sWWWURL.match(exp) ){
sResult = sWWWURL.replace(exp, '<a href="http://$1" >$1</a>');
} else {
sResult = '<a href="http://'+sWWWURL+'">'+sURL+'</a>';
}
} else {
var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
if( sHTTPURL.match(exp) ){
sResult = sHTTPURL.replace(exp,"<a href='$1'>$1</a>");
} else {
sResult = '<a href="'+sHTTPURL+'">'+sURL+'</a>';
}
}
return sBreaker+sResult;
},
$BEFORE_MSG_APP_READY : function(){
var htOptions = nhn.husky.SE2M_Configuration.SE2M_Hyperlink;
if(htOptions && htOptions.bAutolink === false){
this.$ON_REGISTER_CONVERTERS = null;
this.$ON_DISABLE_MESSAGE = null;
this.$ON_ENABLE_MESSAGE = null;
try{ this.oApp.getWYSIWYGDocument().execCommand("AutoUrlDetect", false, false); } catch(e){}
}
},
$ON_MSG_APP_READY : function(){
this.bLayerShown = false;
this.oApp.exec("REGISTER_UI_EVENT", ["hyperlink", "click", "TOGGLE_HYPERLINK_LAYER"]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+k", "TOGGLE_HYPERLINK_LAYER", []]);
this.oApp.registerLazyMessage(["TOGGLE_HYPERLINK_LAYER", "APPLY_HYPERLINK"], ["hp_SE2M_Hyperlink$Lazy.js"]);
},
$ON_REGISTER_CONVERTERS : function(){
this.oApp.exec("ADD_CONVERTER_DOM", ["IR_TO_DB", jindo.$Fn(this.irToDb, this).bind()]);
},
$LOCAL_BEFORE_FIRST : function(sMsg){
if(!!sMsg.match(/(REGISTER_CONVERTERS)/)){
this.oApp.acceptLocalBeforeFirstAgain(this, true);
return true;
}
this._assignHTMLElements(this.oApp.htOptions.elAppContainer);
this.sRXATagMarker = this.sATagMarker.replace(/\//g, "\\/").replace(/\./g, "\\.");
this.oApp.registerBrowserEvent(this.oBtnConfirm, "click", "APPLY_HYPERLINK");
this.oApp.registerBrowserEvent(this.oBtnCancel, "click", "HIDE_ACTIVE_LAYER");
this.oApp.registerBrowserEvent(this.oLinkInput, "keydown", "EVENT_HYPERLINK_KEYDOWN");
},
$ON_EVENT_HYPERLINK_KEYDOWN : function(oEvent){
if (oEvent.key().enter){
this.oApp.exec("APPLY_HYPERLINK");
oEvent.stop();
}
},
$ON_DISABLE_MESSAGE : function(sCmd) {
if(sCmd !== "TOGGLE_HYPERLINK_LAYER"){
return;
}
try{ this.oApp.getWYSIWYGDocument().execCommand("AutoUrlDetect", false, false); } catch(e){}
this._bDisabled = true;
},
$ON_ENABLE_MESSAGE : function(sCmd) {
if(sCmd !== "TOGGLE_HYPERLINK_LAYER"){
return;
}
try{ this.oApp.getWYSIWYGDocument().execCommand("AutoUrlDetect", false, true); } catch(e){}
this._bDisabled = false;
},
irToDb : function(oTmpNode){
if(this._bDisabled){
return;
}
var oCopyNode = oTmpNode.cloneNode(true);
try{
oCopyNode.innerHTML;
}catch(e) {
oCopyNode = jindo.$(oTmpNode.outerHTML);
}
var oTmpRange = this.oApp.getEmptySelection();
var elFirstNode = oTmpRange._getFirstRealChild(oCopyNode);
var elLastNode = oTmpRange._getLastRealChild(oCopyNode);
var waAllNodes = jindo.$A(oTmpRange._getNodesBetween(elFirstNode, elLastNode));
var aAllTextNodes = waAllNodes.filter(function(elNode){return (elNode && elNode.nodeType === 3);}).$value();
var a = aAllTextNodes;
var aCleanTextNodes = aAllTextNodes;
var elTmpDiv = this.oApp.getWYSIWYGDocument().createElement("DIV");
var elParent, bAnchorFound;
var sTmpStr = "@"+(new Date()).getTime()+"@";
var rxTmpStr = new RegExp(sTmpStr, "g");
for(var i=0, nLen=aAllTextNodes.length; i<nLen; i++){
elParent = a[i].parentNode;
bAnchorFound = false;
while(elParent){
if(elParent.tagName === "A" || elParent.tagName === "PRE"){
bAnchorFound = true;
break;
}
elParent = elParent.parentNode;
}
if(bAnchorFound){
continue;
}
elTmpDiv.innerHTML = "";
try {
elTmpDiv.appendChild(a[i].cloneNode(true));
elTmpDiv.innerHTML = (sTmpStr+elTmpDiv.innerHTML).replace(/(&nbsp|\s)?(((?!http[s]?:\/\/)www\.(?:(?!\&nbsp;|\s|"|').)+)|(http[s]?:\/\/(?:(?!&nbsp;|\s|"|').)+))/ig, this._generateAutoLink);
a[i].parentNode.insertBefore(elTmpDiv, a[i]);
a[i].parentNode.removeChild(a[i]);
} catch(e1) {
}
while(elTmpDiv.firstChild){
elTmpDiv.parentNode.insertBefore(elTmpDiv.firstChild, elTmpDiv);
}
elTmpDiv.parentNode.removeChild(elTmpDiv);
}
elTmpDiv = oTmpRange = elFirstNode = elLastNode = waAllNodes = aAllTextNodes = a = aCleanTextNodes = elParent = null;
oCopyNode.innerHTML = oCopyNode.innerHTML.replace(rxTmpStr, "");
oTmpNode.innerHTML = oCopyNode.innerHTML;
oCopyNode = null;
}
});
nhn.husky.SE2M_FontNameWithLayerUI = jindo.$Class({
name : "SE2M_FontNameWithLayerUI",
FONT_SEPARATOR : "husky_seditor_font_separator",
_rxQuote : /['"]/g,
_rxComma : /\s*,\s*/g,
$init : function(elAppContainer, aAdditionalFontList){
this.elLastHover = null;
this._assignHTMLElements(elAppContainer);
this.htBrowser = jindo.$Agent().navigator();
this.aAdditionalFontList = aAdditionalFontList || [];
},
addAllFonts : function(){
var aDefaultFontList, aFontList, htMainFont, aFontInUse, i;
this.htFamilyName2DisplayName = {};
this.htAllFonts = {};
this.aBaseFontList = [];
this.aDefaultFontList = [];
this.aTempSavedFontList = [];
this.htOptions =  this.oApp.htOptions.SE2M_FontName;
if(this.htOptions){
aDefaultFontList = this.htOptions.aDefaultFontList || [];
aFontList = this.htOptions.aFontList;
htMainFont = this.htOptions.htMainFont;
aFontInUse = this.htOptions.aFontInUse;
if(this.htBrowser.ie && aFontList){
for(i=0; i<aFontList.length; i++){
this.addFont(aFontList[i].id, aFontList[i].name, aFontList[i].size, aFontList[i].url, aFontList[i].cssUrl);
}
}
for(i=0; i<aDefaultFontList.length; i++){
this.addFont(aDefaultFontList[i][0], aDefaultFontList[i][1], 0, "", "", 1);
}
if(htMainFont && htMainFont.id) {
this.setMainFont(htMainFont.id, htMainFont.name, htMainFont.size, htMainFont.url, htMainFont.cssUrl);
}
if(this.htBrowser.ie && aFontInUse){
for(i=0; i<aFontInUse.length; i++){
this.addFontInUse(aFontInUse[i].id, aFontInUse[i].name, aFontInUse[i].size, aFontInUse[i].url, aFontInUse[i].cssUrl);
}
}
}
if(!this.htOptions || !this.htOptions.aDefaultFontList || this.htOptions.aDefaultFontList.length === 0){
this.addFont("돋움,Dotum", "돋움", 0, "", "", 1, null, true);
this.addFont("돋움체,DotumChe,AppleGothic", "돋움체", 0, "", "", 1, null, true);
this.addFont("굴림,Gulim", "굴림", 0, "", "", 1, null, true);
this.addFont("굴림체,GulimChe", "굴림체", 0, "", "", 1, null, true);
this.addFont("바탕,Batang,AppleMyungjo", "바탕", 0, "", "", 1, null, true);
this.addFont("바탕체,BatangChe", "바탕체", 0, "", "", 1, null, true);
this.addFont("궁서,Gungsuh,GungSeo", "궁서", 0, "", "", 1, null, true);
this.addFont('Arial', 'Arial', 0, "", "", 1, "abcd", true);
this.addFont('Tahoma', 'Tahoma', 0, "", "", 1, "abcd", true);
this.addFont('Times New Roman', 'Times New Roman', 0, "", "", 1, "abcd", true);
this.addFont('Verdana', 'Verdana', 0, "", "", 1, "abcd", true);
this.addFont('Courier New', 'Courier New', 0, "", "", 1, "abcd", true);
}
if(!!this.aAdditionalFontList && this.aAdditionalFontList.length > 0){
for(i = 0, nLen = this.aAdditionalFontList.length; i < nLen; i++){
this.addFont(this.aAdditionalFontList[i][0], this.aAdditionalFontList[i][1], 0, "", "", 1);
}
}
},
$ON_MSG_APP_READY : function(){
this.bDoNotRecordUndo = false;
this.oApp.exec("ADD_APP_PROPERTY", ["addFont", jindo.$Fn(this.addFont, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["addFontInUse", jindo.$Fn(this.addFontInUse, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["setMainFont", jindo.$Fn(this.setMainFont, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["setDefaultFont", jindo.$Fn(this.setDefaultFont, this).bind()]);
this.oApp.exec("REGISTER_UI_EVENT", ["fontName", "click", "SE2M_TOGGLE_FONTNAME_LAYER"]);
},
$AFTER_MSG_APP_READY : function(){
this._initFontName();
this._attachIEEvent();
},
_assignHTMLElements : function(elAppContainer){
this.oDropdownLayer = jindo.$$.getSingle("DIV.husky_se_fontName_layer", elAppContainer);
this.elFontNameLabel = jindo.$$.getSingle("SPAN.husky_se2m_current_fontName", elAppContainer);
this.elFontNameList = jindo.$$.getSingle("UL", this.oDropdownLayer);
this.elInnerLayer = this.elFontNameList.parentNode;
this.aelFontInMarkup = jindo.$$("LI", this.oDropdownLayer);
this.elFontItemTemplate = this.aelFontInMarkup.shift();	
this.aLIFontNames = jindo.$A(jindo.$$("LI", this.oDropdownLayer)).filter(function(v,i,a){return (v.firstChild !== null);})._array;
this.sDefaultText = this.elFontNameLabel.innerHTML;
},
_initFontName : function(){
this._addFontInMarkup();
this.addAllFonts();
var oStyle;
if(this.oApp.getCurrentStyle && (oStyle = this.oApp.getCurrentStyle())){
this.$ON_MSG_STYLE_CHANGED("fontFamily", oStyle.fontFamily);
}
this.oApp.registerBrowserEvent(this.oDropdownLayer, "mouseover", "EVENT_FONTNAME_LAYER_MOUSEOVER", []);
this.oApp.registerBrowserEvent(this.oDropdownLayer, "click", "EVENT_FONTNAME_LAYER_CLICKED", []);
},
_checkFontLI : function(el, sFontName){
if(!el){
return false;
}
var bInstalled = IsInstalledFont(sFontName);
el.style.display = bInstalled ? "block" : "none";
return bInstalled;
},
_addFontInMarkup : function(){
for(var i = 0, elLi, sFontFamily, elSeparator, bUseSeparator; (elLi = this.aelFontInMarkup[i]); i++){
if(elLi.firstChild){
sFontFamily = this._getFontFamilyFromLI(elLi).replace(this._rxQuote, "").replace(this._rxComma, ",");
bUseSeparator |= this._checkFontLI(elLi, sFontFamily);
}else if(elLi.className.indexOf(this.FONT_SEPARATOR) > -1){
if(elSeparator){
elSeparator.style.display = bUseSeparator ? "block" : "none";
}
elSeparator = elLi;	
bUseSeparator = false;
}else{
elLi.style.display = "none";
}
}
if(elSeparator){
elSeparator.style.display = bUseSeparator ? "block" : "none";
}
},
_attachIEEvent : function(){
if(!this.htBrowser.ie){
return;
}
if(this.htBrowser.nativeVersion < 9){	
this._wfOnPasteWYSIWYGBody = jindo.$Fn(this._onPasteWYSIWYGBody, this);
this._wfOnPasteWYSIWYGBody.attach(this.oApp.getWYSIWYGDocument().body, "paste");
return;
}
if(document.documentMode < 9){
this._wfOnFocusWYSIWYGBody = jindo.$Fn(this._onFocusWYSIWYGBody, this);
this._wfOnFocusWYSIWYGBody.attach(this.oApp.getWYSIWYGDocument().body, "focus");
return;
}
this.welEditingAreaCover = jindo.$Element('<DIV style="width:100%; height:100%; position:absolute; top:0px; left:0px; z-index:1000;"></DIV>');
this.oApp.welEditingAreaContainer.prepend(this.welEditingAreaCover);
jindo.$Fn(this._onMouseupCover, this).attach(this.welEditingAreaCover.$value(), "mouseup");
},
_onFocusWYSIWYGBody : function(e){
this._wfOnFocusWYSIWYGBody.detach(this.oApp.getWYSIWYGDocument().body, "focus");
this._loadAllBaseFont();
},
_onPasteWYSIWYGBody : function(e){
this._wfOnPasteWYSIWYGBody.detach(this.oApp.getWYSIWYGDocument().body, "paste");
this._loadAllBaseFont();
},
_onMouseupCover : function(e){
e.stop();
if(this.welEditingAreaCover){
this.welEditingAreaCover.leave();
}
var oMouse = e.mouse(),
elBody = this.oApp.getWYSIWYGDocument().body,
welBody = jindo.$Element(elBody),
oSelection = this.oApp.getEmptySelection();
oSelection.selectNode(elBody);
oSelection.collapseToStart();
oSelection.select();
welBody.fireEvent("mousedown", {left : oMouse.left, middle : oMouse.middle, right : oMouse.right});
welBody.fireEvent("mouseup", {left : oMouse.left, middle : oMouse.middle, right : oMouse.right});
if(this.oApp.oNavigator.ie && document.documentMode < 11 && this.oApp.getEditingMode() === "WYSIWYG"){
if(this.oApp.getWYSIWYGDocument().body.innerHTML == "<p></p>"){
this.oApp.getWYSIWYGDocument().body.innerHTML = '<p><span id="husky_bookmark_start_INIT"></span><span id="husky_bookmark_end_INIT"></span></p>';
var oSelection = this.oApp.getSelection();
oSelection.moveToStringBookmark("INIT");
oSelection.select();
oSelection.removeStringBookmark("INIT");
}
}else if(this.oApp.oNavigator.ie && this.oApp.oNavigator.nativeVersion == 11 && document.documentMode == 11 && this.oApp.getEditingMode() === "WYSIWYG"){
if(this.oApp.getWYSIWYGDocument().body.innerHTML == "<p><br></p>"){
var elCursorHolder_br = jindo.$$.getSingle("br", elBody);
oSelection.setStartBefore(elCursorHolder_br);
oSelection.setEndBefore(elCursorHolder_br);
oSelection.select();
}
}
},
$ON_EVENT_TOOLBAR_MOUSEDOWN : function(){
if(this.htBrowser.nativeVersion < 9 || document.documentMode < 9){
return;
}
if(this.welEditingAreaCover){
this.welEditingAreaCover.leave();
}
},
_loadAllBaseFont : function(){
var i, nFontLen;
if(!this.htBrowser.ie){
return;
}
if(this.htBrowser.nativeVersion < 9){
for(i=0, nFontLen=this.aBaseFontList.length; i<nFontLen; i++){
this.aBaseFontList[i].loadCSS(this.oApp.getWYSIWYGDocument());
}
}else if(document.documentMode < 9){
for(i=0, nFontLen=this.aBaseFontList.length; i<nFontLen; i++){
this.aBaseFontList[i].loadCSSToMenu();
}
}
this._loadAllBaseFont = function(){};
},
_addFontToMenu: function(sDisplayName, sFontFamily, sSampleText){
var elItem = document.createElement("LI");
elItem.innerHTML = this.elFontItemTemplate.innerHTML.replace("@DisplayName@",  sDisplayName).replace("FontFamily", sFontFamily).replace("@SampleText@", sSampleText);
this.elFontNameList.insertBefore(elItem, this.elFontItemTemplate);
this.aLIFontNames[this.aLIFontNames.length] = elItem;
if(this.aLIFontNames.length > 20){
this.oDropdownLayer.style.overflowX = 'hidden';
this.oDropdownLayer.style.overflowY = 'auto';
this.oDropdownLayer.style.height = '400px';
this.oDropdownLayer.style.width = '204px';
}
},
$ON_EVENT_FONTNAME_LAYER_MOUSEOVER : function(wev){
var elTmp = this._findLI(wev.element);
if(!elTmp){
return;
}
this._clearLastHover();
elTmp.className = "hover";
this.elLastHover = elTmp;
},
$ON_EVENT_FONTNAME_LAYER_CLICKED : function(wev){
var elTmp = this._findLI(wev.element);
if(!elTmp){
return;
}
var sFontFamily = this._getFontFamilyFromLI(elTmp);
var htFontInfo = this.htAllFonts[sFontFamily.replace(/\"/g, nhn.husky.SE2M_FontNameWithLayerUI.CUSTOM_FONT_MARKS)];
var nDefaultFontSize;
if(htFontInfo){
nDefaultFontSize = htFontInfo.defaultSize+"pt";
}else{
nDefaultFontSize = 0;
}
this.oApp.exec("SET_FONTFAMILY", [sFontFamily, nDefaultFontSize]);
},
_findLI : function(elTmp){
while(elTmp.tagName != "LI"){
if(!elTmp || elTmp === this.oDropdownLayer){
return null;
}
elTmp = elTmp.parentNode;
}
if(elTmp.className.indexOf(this.FONT_SEPARATOR) > -1){
return null;
}
return elTmp;
},
_clearLastHover : function(){
if(this.elLastHover){
this.elLastHover.className = "";
}
},
$ON_SE2M_TOGGLE_FONTNAME_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.oDropdownLayer, null, "MSG_FONTNAME_LAYER_OPENED", [], "MSG_FONTNAME_LAYER_CLOSED", []]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['font']);
},
$ON_MSG_FONTNAME_LAYER_OPENED : function(){
this.oApp.exec("SELECT_UI", ["fontName"]);
},
$ON_MSG_FONTNAME_LAYER_CLOSED : function(){
this._clearLastHover();
this.oApp.exec("DESELECT_UI", ["fontName"]);
},
$ON_MSG_STYLE_CHANGED : function(sAttributeName, sAttributeValue){
if(sAttributeName == "fontFamily"){
sAttributeValue = sAttributeValue.replace(/["']/g, "");
var elLi = this._getMatchingLI(sAttributeValue);
this._clearFontNameSelection();
if(elLi){
this.elFontNameLabel.innerHTML = this._getFontNameLabelFromLI(elLi);
jindo.$Element(elLi).addClass("active");
}else{
var sDisplayName = this.sDefaultText;
this.elFontNameLabel.innerHTML = sDisplayName;
}
}
},
$BEFORE_RECORD_UNDO_BEFORE_ACTION : function(){
return !this.bDoNotRecordUndo;
},
$BEFORE_RECORD_UNDO_AFTER_ACTION : function(){
return !this.bDoNotRecordUndo;
},
$BEFORE_RECORD_UNDO_ACTION : function(){
return !this.bDoNotRecordUndo;
},
$ON_SET_FONTFAMILY : function(sFontFamily, sDefaultSize){
if(!sFontFamily){return;}
var oFontInfo = this.htAllFonts[sFontFamily.replace(/\"/g, nhn.husky.SE2M_FontNameWithLayerUI.CUSTOM_FONT_MARKS)];
if(!!oFontInfo){
oFontInfo.loadCSS(this.oApp.getWYSIWYGDocument());
}
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["SET FONTFAMILY", {bMustBlockElement:true}]);
this.bDoNotRecordUndo = true;
if(parseInt(sDefaultSize, 10) > 0){
this.oApp.exec("SET_WYSIWYG_STYLE", [{"fontSize":sDefaultSize}]);
}
this.oApp.exec("SET_WYSIWYG_STYLE", [{"fontFamily":sFontFamily}]);
this.bDoNotRecordUndo = false;
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["SET FONTFAMILY", {bMustBlockElement:true}]);
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
},
_getMatchingLI : function(sFontName){
sFontName = sFontName.toLowerCase();
var elLi, aFontFamily;
for(var i=0; i<this.aLIFontNames.length; i++){
elLi = this.aLIFontNames[i];
aFontFamily = this._getFontFamilyFromLI(elLi).toLowerCase().split(",");
for(var h=0; h < aFontFamily.length;h++){
if( !!aFontFamily[h] && jindo.$S(aFontFamily[h].replace(/['"]/ig, "")).trim().$value() == sFontName){
return elLi;
}
}
}
return null;
},
_getFontFamilyFromLI : function(elLi){
return (elLi.getElementsByTagName("EM")[0]).style.fontFamily;
},
_getFontNameLabelFromLI : function(elLi){
return elLi.firstChild.firstChild.firstChild.nodeValue;
},
_clearFontNameSelection : function(elLi){
for(var i=0; i<this.aLIFontNames.length; i++){
jindo.$Element(this.aLIFontNames[i]).removeClass("active");
}
},
addFont : function (fontId, fontName, defaultSize, fontURL, fontCSSURL, fontType, sSampleText, bCheck) {
if(!this.htBrowser.ie && fontCSSURL){
return null;
}
if(bCheck && !IsInstalledFont(fontId)){
return null;
}
fontId = fontId.toLowerCase();
var newFont = new fontProperty(fontId, fontName, defaultSize, fontURL, fontCSSURL);
var sFontFamily;
var sDisplayName;
if(defaultSize>0){
sFontFamily = fontId+"_"+defaultSize;
sDisplayName = fontName+"_"+defaultSize;
}else{
sFontFamily = fontId;
sDisplayName = fontName;
}
if(!fontType){
sFontFamily = nhn.husky.SE2M_FontNameWithLayerUI.CUSTOM_FONT_MARKS + sFontFamily + nhn.husky.SE2M_FontNameWithLayerUI.CUSTOM_FONT_MARKS;
}
if(this.htAllFonts[sFontFamily]){
return this.htAllFonts[sFontFamily];
}
this.htAllFonts[sFontFamily] = newFont;
if(this.htBrowser.ie && this.htBrowser.nativeVersion >= 9 && document.documentMode >= 9) {
newFont.loadCSSToMenu();
}
this.htFamilyName2DisplayName[sFontFamily] = fontName;
sSampleText = sSampleText || this.oApp.$MSG('SE2M_FontNameWithLayerUI.sSampleText');
this._addFontToMenu(sDisplayName, sFontFamily, sSampleText);
if(!fontType){
this.aBaseFontList[this.aBaseFontList.length] = newFont;
}else{
if(fontType == 1){
this.aDefaultFontList[this.aDefaultFontList.length] = newFont;
}else{
this.aTempSavedFontList[this.aTempSavedFontList.length] = newFont;
}
}
return newFont;
},
addFontInUse : function (fontId, fontName, defaultSize, fontURL, fontCSSURL, fontType) {
var newFont = this.addFont(fontId, fontName, defaultSize, fontURL, fontCSSURL, fontType);
if(!newFont){
return null;
}
newFont.loadCSS(this.oApp.getWYSIWYGDocument());
return newFont;
},
setMainFont : function (fontId, fontName, defaultSize, fontURL, fontCSSURL, fontType) {
var newFont = this.addFontInUse(fontId, fontName, defaultSize, fontURL, fontCSSURL, fontType);
if(!newFont){
return null;
}
this.setDefaultFont(newFont.fontFamily, defaultSize);
return newFont;
},
setDefaultFont : function(sFontFamily, nFontSize){
var elBody = this.oApp.getWYSIWYGDocument().body;
elBody.style.fontFamily = sFontFamily;
if(nFontSize>0){elBody.style.fontSize   = nFontSize + 'pt';}
}
});
nhn.husky.SE2M_FontNameWithLayerUI.CUSTOM_FONT_MARKS = "'";
function fontProperty(fontId, fontName, defaultSize, fontURL, fontCSSURL){
this.fontId = fontId;
this.fontName = fontName;
this.defaultSize = defaultSize;
this.fontURL = fontURL;
this.fontCSSURL = fontCSSURL;
this.displayName = fontName;
this.isLoaded = true;
this.fontFamily = this.fontId;
if(this.fontCSSURL != ""){
this.displayName += '' + defaultSize;
this.fontFamily += '_' + defaultSize;
this.isLoaded = false;
this.loadCSS = function(doc){
if(this.isLoaded){
return;
}
this._importCSS(doc);
this.isLoaded = true;
};
this.loadCSSToMenu = function(){
this._importCSS(document);
};
this._importCSS = function(doc){
var nStyleSheet = doc.styleSheets.length;
var oStyleSheet = doc.styleSheets[nStyleSheet - 1];
if(nStyleSheet === 0 || oStyleSheet.imports.length == 30){
if(doc.createStyleSheet){
oStyleSheet = doc.createStyleSheet();
}else{
oStyleSheet = doc.createElement("style");
doc.documentElement.firstChild.appendChild(oStyleSheet);
oStyleSheet = oStyleSheet.sheet;
}
}
oStyleSheet.addImport(this.fontCSSURL);
};
}else{
this.loadCSS = function(){};
this.loadCSSToMenu = function(){};
}
this.toStruct = function(){
return {fontId:this.fontId, fontName:this.fontName, defaultSize:this.defaultSize, fontURL:this.fontURL, fontCSSURL:this.fontCSSURL};
};
}
nhn.ColorPicker = jindo.$Class({
elem : null,
huePanel : null,
canvasType : "Canvas",
_hsvColor  : null,
$init : function(oElement, oOptions) {
this.elem = jindo.$Element(oElement).empty();
this.huePanel   = null;
this.cursor     = jindo.$Element("<div>").css("overflow", "hidden");
this.canvasType = jindo.$(oElement).filters?"Filter":jindo.$("<canvas>").getContext?"Canvas":null;
if(!this.canvasType) {
return false;
}
this.option({
huePanel : null,
huePanelType : "horizontal"
});
this.option(oOptions);
if (this.option("huePanel")) {
this.huePanel = jindo.$Element(this.option("huePanel")).empty();
}
this._hsvColor = this._hsv(0,100,100);
for(var name in this) {
if (/^_on[A-Z][a-z]+[A-Z][a-z]+$/.test(name)) {
this[name+"Fn"] = jindo.$Fn(this[name], this);
}
}
this._onDownColorFn.attach(this.elem, "mousedown");
if (this.huePanel) {
this._onDownHueFn.attach(this.huePanel, "mousedown");
}
this.paint();
},
rgb : function(rgb) {
this.hsv(this._rgb2hsv(rgb.r, rgb.g, rgb.b));
},
hsv : function(hsv) {
if (typeof hsv == "undefined") {
return this._hsvColor;
}
var rgb = null;
var w = this.elem.width();
var h = this.elem.height();
var cw = this.cursor.width();
var ch = this.cursor.height();
var x = 0, y = 0;
if (this.huePanel) {
rgb = this._hsv2rgb(hsv.h, 100, 100);
this.elem.css("background", "#"+this._rgb2hex(rgb.r, rgb.g, rgb.b));
x = hsv.s/100 * w;
y = (100-hsv.v)/100 * h;
} else {
var hw = w / 2;
if (hsv.v > hsv.s) {
hsv.v = 100;
x = hsv.s/100 * hw;
} else {
hsv.s = 100;
x = (100-hsv.v)/100 * hw + hw;
}
y = hsv.h/360 * h;
}
x = Math.max(Math.min(x-1,w-cw), 1);
y = Math.max(Math.min(y-1,h-ch), 1);
this.cursor.css({left:x+"px",top:y+"px"});
this._hsvColor = hsv;
rgb = this._hsv2rgb(hsv.h, hsv.s, hsv.v);
this.fireEvent("colorchange", {type:"colorchange", element:this, currentElement:this, rgbColor:rgb, hexColor:"#"+this._rgb2hex(rgb.r, rgb.g, rgb.b), hsvColor:hsv} );
},
paint : function() {
if (this.huePanel) {
this["_paintColWith"+this.canvasType]();
this["_paintHueWith"+this.canvasType]();
} else {
this["_paintOneWith"+this.canvasType]();
}
this.cursor.appendTo(this.elem);
this.cursor.css({position:"absolute",top:"1px",left:"1px",background:"white",border:"1px solid black"}).width(3).height(3);
this.hsv(this._hsvColor);
},
_paintColWithFilter : function() {
jindo.$Element("<div>").css({
position : "absolute",
top      : 0,
left     : 0,
width    : "100%",
height   : "100%",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType=1,StartColorStr='#FFFFFFFF',EndColorStr='#00FFFFFF')"
}).appendTo(this.elem);
jindo.$Element("<div>").css({
position : "absolute",
top      : 0,
left     : 0,
width    : "100%",
height   : "100%",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType=0,StartColorStr='#00000000',EndColorStr='#FF000000')"
}).appendTo(this.elem);
},
_paintColWithCanvas : function() {
var cvs = jindo.$Element("<canvas>").css({width:"100%",height:"100%"});
cvs.appendTo(this.elem.empty());
var ctx = cvs.attr("width", cvs.width()).attr("height", cvs.height()).$value().getContext("2d");
var lin = null;
var w   = cvs.width();
var h   = cvs.height();
lin = ctx.createLinearGradient(0,0,w,0);
lin.addColorStop(0, "rgba(255,255,255,1)");
lin.addColorStop(1, "rgba(255,255,255,0)");
ctx.fillStyle = lin;
ctx.fillRect(0,0,w,h);
lin = ctx.createLinearGradient(0,0,0,h);
lin.addColorStop(0, "rgba(0,0,0,0)");
lin.addColorStop(1, "rgba(0,0,0,1)");
ctx.fillStyle = lin;
ctx.fillRect(0,0,w,h);
},
_paintOneWithFilter : function() {
var sp, ep, s_rgb, e_rgb, s_hex, e_hex;
var h = this.elem.height();
for(var i=1; i < 7; i++) {
sp = Math.floor((i-1)/6 * h);
ep = Math.floor(i/6 * h);
s_rgb = this._hsv2rgb((i-1)/6*360, 100, 100);
e_rgb = this._hsv2rgb(i/6*360, 100, 100);
s_hex = "#FF"+this._rgb2hex(s_rgb.r, s_rgb.g, s_rgb.b);
e_hex = "#FF"+this._rgb2hex(e_rgb.r, e_rgb.g, e_rgb.b);
jindo.$Element("<div>").css({
position : "absolute",
left   : 0,
width  : "100%",
top    : sp + "px",
height : (ep-sp) + "px",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType=0,StartColorStr='"+s_hex+"',EndColorStr='"+e_hex+"')"
}).appendTo(this.elem);
}
jindo.$Element("<div>").css({
position : "absolute",
top      : 0,
left     : 0,
width    : "50%",
height   : "100%",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType=1,StartColorStr='#FFFFFFFF',EndColorStr='#00FFFFFF')"
}).appendTo(this.elem);
jindo.$Element("<div>").css({
position : "absolute",
top      : 0,
right    : 0,
width    : "50%",
height   : "100%",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType=1,StartColorStr='#00000000',EndColorStr='#FF000000')"
}).appendTo(this.elem);
},
_paintOneWithCanvas : function() {
var rgb = {r:0, g:0, b:0};
var cvs = jindo.$Element("<canvas>").css({width:"100%",height:"100%"});
cvs.appendTo(this.elem.empty());
var ctx = cvs.attr("width", cvs.width()).attr("height", cvs.height()).$value().getContext("2d");
var w = cvs.width();
var h = cvs.height();
var lin = ctx.createLinearGradient(0,0,0,h);
for(var i=0; i < 7; i++) {
rgb = this._hsv2rgb(i/6*360, 100, 100);
lin.addColorStop(i/6, "rgb("+rgb.join(",")+")");
}
ctx.fillStyle = lin;
ctx.fillRect(0,0,w,h);
lin = ctx.createLinearGradient(0,0,w,0);
lin.addColorStop(0, "rgba(255,255,255,1)");
lin.addColorStop(0.5, "rgba(255,255,255,0)");
lin.addColorStop(0.5, "rgba(0,0,0,0)");
lin.addColorStop(1, "rgba(0,0,0,1)");
ctx.fillStyle = lin;
ctx.fillRect(0,0,w,h);
},
_paintHueWithFilter : function() {
var sp, ep, s_rgb, e_rgb, s_hex, e_hex;
var vert = (this.option().huePanelType == "vertical");
var w = this.huePanel.width();
var h = this.huePanel.height();
var elDiv = null;
var nPanelBorderWidth = parseInt(this.huePanel.css('borderWidth'), 10);
if (!!isNaN(nPanelBorderWidth)) { nPanelBorderWidth = 0; }
w -= nPanelBorderWidth * 2;
for(var i=1; i < 7; i++) {
sp = Math.floor((i-1)/6 * (vert?h:w));
ep = Math.floor(i/6 * (vert?h:w));
s_rgb = this._hsv2rgb((i-1)/6*360, 100, 100);
e_rgb = this._hsv2rgb(i/6*360, 100, 100);
s_hex = "#FF"+this._rgb2hex(s_rgb.r, s_rgb.g, s_rgb.b);
e_hex = "#FF"+this._rgb2hex(e_rgb.r, e_rgb.g, e_rgb.b);
elDiv = jindo.$Element("<div>").css({
position : "absolute",
filter : "progid:DXImageTransform.Microsoft.Gradient(GradientType="+(vert?0:1)+",StartColorStr='"+s_hex+"',EndColorStr='"+e_hex+"')"
});
var width = (ep - sp) + 1;
elDiv.appendTo(this.huePanel);
elDiv.css(vert?"left":"top", 0).css(vert?"width":"height", '100%');
elDiv.css(vert?"top":"left", sp + "px").css(vert?"height":"width", width + "px");
}
},
_paintHueWithCanvas : function() {
var opt = this.option(), rgb;
var vtc = (opt.huePanelType == "vertical");
var cvs = jindo.$Element("<canvas>").css({width:"100%",height:"100%"});
cvs.appendTo(this.huePanel.empty());
var ctx = cvs.attr("width", cvs.width()).attr("height", cvs.height()).$value().getContext("2d");
var lin = ctx.createLinearGradient(0,0,vtc?0:cvs.width(),vtc?cvs.height():0);
for(var i=0; i < 7; i++) {
rgb = this._hsv2rgb(i/6*360, 100, 100);
lin.addColorStop(i/6, "rgb("+rgb.join(",")+")");
}
ctx.fillStyle = lin;
ctx.fillRect(0,0,cvs.width(),cvs.height());
},
_rgb2hsv : function(r,g,b) {
var h = 0, s = 0, v = Math.max(r,g,b), min = Math.min(r,g,b), delta = v - min;
s = (v ? delta/v : 0);
if (s) {
if (r == v) {
h = 60 * (g - b) / delta;
} else if (g == v) {
h = 120 + 60 * (b - r) / delta;
} else if (b == v) {
h = 240 + 60 * (r - g) / delta;
}
if (h < 0) {
h += 360;
}
}
h = Math.floor(h);
s = Math.floor(s * 100);
v = Math.floor(v / 255 * 100);
return this._hsv(h,s,v);
},
_hsv2rgb : function(h,s,v) {
h = (h % 360) / 60; s /= 100; v /= 100;
var r=0, g=0, b=0;
var i = Math.floor(h);
var f = h-i;
var p = v*(1-s);
var q = v*(1-s*f);
var t = v*(1-s*(1-f));
switch (i) {
case 0: r=v; g=t; b=p; break;
case 1: r=q; g=v; b=p; break;
case 2: r=p; g=v; b=t; break;
case 3: r=p; g=q; b=v; break;
case 4: r=t; g=p; b=v; break;
case 5: r=v; g=p; b=q;break;
case 6: break;
}
r = Math.floor(r*255);
g = Math.floor(g*255);
b = Math.floor(b*255);
return this._rgb(r,g,b);
},
_rgb2hex : function(r,g,b) {
r = r.toString(16);
if (r.length == 1) {
r = '0'+r;
}
g = g.toString(16);
if (g.length==1) {
g = '0'+g;
}
b = b.toString(16);
if (b.length==1) {
b = '0'+b;
}
return r+g+b;
},
_hex2rgb : function(hex) {
var m = hex.match(/#?([0-9a-f]{6}|[0-9a-f]{3})/i);
if (m[1].length == 3) {
m = m[1].match(/./g).filter(function(c) {
return c+c;
});
} else {
m = m[1].match(/../g);
}
return {
r : Number("0x" + m[0]),
g : Number("0x" + m[1]),
b : Number("0x" + m[2])
};
},
_rgb : function(r,g,b) {
var ret = [r,g,b];
ret.r = r;
ret.g = g;
ret.b = b;
return ret;
},
_hsv : function(h,s,v) {
var ret = [h,s,v];
ret.h = h;
ret.s = s;
ret.v = v;
return ret;
},
_onDownColor : function(e) {
if (!e.mouse().left) {
return false;
}
var pos = e.pos();
this._colPagePos = [pos.pageX, pos.pageY];
this._colLayerPos = [pos.layerX, pos.layerY];
this._onUpColorFn.attach(document, "mouseup");
this._onMoveColorFn.attach(document, "mousemove");
this._onMoveColor(e);
},
_onUpColor : function(e) {
this._onUpColorFn.detach(document, "mouseup");
this._onMoveColorFn.detach(document, "mousemove");
},
_onMoveColor : function(e) {
var hsv = this._hsvColor;
var pos = e.pos();
var x = this._colLayerPos[0] + (pos.pageX - this._colPagePos[0]);
var y = this._colLayerPos[1] + (pos.pageY - this._colPagePos[1]);
var w = this.elem.width();
var h = this.elem.height();
x = Math.max(Math.min(x, w), 0);
y = Math.max(Math.min(y, h), 0);
if (this.huePanel) {
hsv.s = hsv[1] = x / w * 100;
hsv.v = hsv[2] = (h - y) / h * 100;
} else {
hsv.h = y/h*360;
var hw = w/2;
if (x < hw) {
hsv.s = x/hw * 100;
hsv.v = 100;
} else {
hsv.s = 100;
hsv.v = (w-x)/hw * 100;
}
}
this.hsv(hsv);
e.stop();
},
_onDownHue : function(e) {
if (!e.mouse().left) {
return false;
}
var pos = e.pos();
this._huePagePos  = [pos.pageX, pos.pageY];
this._hueLayerPos = [pos.layerX, pos.layerY];
this._onUpHueFn.attach(document, "mouseup");
this._onMoveHueFn.attach(document, "mousemove");
this._onMoveHue(e);
},
_onUpHue : function(e) {
this._onUpHueFn.detach(document, "mouseup");
this._onMoveHueFn.detach(document, "mousemove");
},
_onMoveHue : function(e) {
var hsv = this._hsvColor;
var pos = e.pos();
var cur = 0, len = 0;
var x = this._hueLayerPos[0] + (pos.pageX - this._huePagePos[0]);
var y = this._hueLayerPos[1] + (pos.pageY - this._huePagePos[1]);
if (this.option().huePanelType == "vertical") {
cur = y;
len = this.huePanel.height();
} else {
cur = x;
len = this.huePanel.width();
}
hsv.h = hsv[0] = (Math.min(Math.max(cur, 0), len)/len * 360)%360;
this.hsv(hsv);
e.stop();
}
}).extend(jindo.Component);
nhn.husky.SE2M_Accessibility = jindo.$Class({
name : "SE2M_Accessibility",
$init: function(elAppContainer, sLocale, sEditorType) {
this._assignHTMLElements(elAppContainer);
if(!!sLocale){
this.sLang = sLocale;
}
if(!!sEditorType){
this.sType = sEditorType;
}
},
_assignHTMLElements : function(elAppContainer){
this.elHelpPopupLayer = jindo.$$.getSingle("DIV.se2_accessibility", elAppContainer);
this.welHelpPopupLayer = jindo.$Element(this.elHelpPopupLayer);
this.oCloseButton = jindo.$$.getSingle("BUTTON.se2_close", this.elHelpPopupLayer);
this.oCloseButton2 = jindo.$$.getSingle("BUTTON.se2_close2", this.elHelpPopupLayer);
this.nDefaultTop = 150;
this.elAppContainer = elAppContainer;
},
$ON_MSG_APP_READY : function(){
this.htAccessOption = nhn.husky.SE2M_Configuration.SE2M_Accessibility || {};
this.oApp.exec("REGISTER_HOTKEY", ["alt+F10", "FOCUS_TOOLBAR_AREA", []]);
this.oApp.exec("REGISTER_HOTKEY", ["alt+COMMA", "FOCUS_BEFORE_ELEMENT", []]);
this.oApp.exec("REGISTER_HOTKEY", ["alt+PERIOD", "FOCUS_NEXT_ELEMENT", []]);
if ((this.sType == 'basic' || this.sType == 'light') && (this.sLang != 'ko_KR'))  {
return;
} else {
this.oApp.exec("REGISTER_HOTKEY", ["alt+0", "OPEN_HELP_POPUP", []]);
this.oApp.exec("REGISTER_HOTKEY", ["esc", "CLOSE_HELP_POPUP", [], document]);
}
if (this.htAccessOption.sTitleElementId) {
this.oApp.registerBrowserEvent(document.getElementById(this.htAccessOption.sTitleElementId), "keydown", "MOVE_TO_EDITAREA", []);
}
},
$ON_MOVE_TO_EDITAREA : function(weEvent) {
var TAB_KEY_CODE = 9;
if (weEvent.key().keyCode == TAB_KEY_CODE) {
if(weEvent.key().shift) {return;}
this.oApp.delayedExec("FOCUS", [], 0);
}
},
$LOCAL_BEFORE_FIRST : function(sMsg){
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("CLOSE_HELP_POPUP", [this.oCloseButton]), this).attach(this.oCloseButton, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("CLOSE_HELP_POPUP", [this.oCloseButton2]), this).attach(this.oCloseButton2, "click");
var elIframe = this.oApp.getWYSIWYGWindow().frameElement;
this.htOffsetPos = jindo.$Element(elIframe).offset();
this.nEditorWidth = elIframe.offsetWidth;
this.htInitialPos = this.welHelpPopupLayer.offset();
var htScrollXY = this.oApp.oUtils.getScrollXY();
this.nLayerWidth = 590;
this.nLayerHeight = 480;
this.htTopLeftCorner = {x:parseInt(this.htOffsetPos.left, 10), y:parseInt(this.htOffsetPos.top, 10)};
},
$ON_FOCUS_NEXT_ELEMENT : function() {
this._currentNextFocusElement = null;
if(this.htAccessOption.sNextElementId){
this._currentNextFocusElement = document.getElementById(this.htAccessOption.sNextElementId);
}else{
this._currentNextFocusElement = this._findNextFocusElement(this.elAppContainer);
}
if(this._currentNextFocusElement){
window.focus();
this._currentNextFocusElement.focus();
}else if(parent && parent.nhn && parent.nhn.husky && parent.nhn.husky.EZCreator && parent.nhn.husky.EZCreator.elIFrame){
parent.focus();
if(this._currentNextFocusElement = this._findNextFocusElement(parent.nhn.husky.EZCreator.elIFrame)){
this._currentNextFocusElement.focus();
}
}
},
_findNextFocusElement : function(targetElement){
var target = null;
var el = targetElement.nextSibling;
while(el){
if(el.nodeType !== 1){
el = this._switchToSiblingOrNothing(el);
if(!el){
break;
}else{
continue;
}
}
this._recursivePreorderTraversalFilter(el, this._isFocusTag);
if(this._nextFocusElement){
target = this._nextFocusElement;
this._bStopFindingNextElement = false;
this._nextFocusElement = null;
break;
}else{
el = this._switchToSiblingOrNothing(el);
if(!el){
break;
}
}
}
return target;
},
_switchToSiblingOrNothing : function(targetElement, isPreviousOrdered){
var el = targetElement;
if(isPreviousOrdered){
if(el.previousSibling){
el = el.previousSibling;
}else{
while(el.nodeName.toUpperCase() != "BODY" && !el.previousSibling){
el = el.parentNode;
}
if(el.nodeName.toUpperCase() == "BODY"){
el = null;
}else{
el = el.previousSibling;
}
}
}else{
if(el.nextSibling){
el = el.nextSibling;
}else{
while(el.nodeName.toUpperCase() != "BODY" && !el.nextSibling){
el = el.parentNode;
}
if(el.nodeName.toUpperCase() == "BODY"){
el = null;
}else{
el = el.nextSibling;
}
}
}
return el;
},
_recursivePreorderTraversalFilter : function(node, filterFunction, isReversed){
var self = this;
var _bStopFindingNextElement = filterFunction.apply(node);
if(_bStopFindingNextElement){
self._bStopFindingNextElement = true;
if(isReversed){
self._previousFocusElement = node;
}else{
self._nextFocusElement = node;
}
return;
}else{
if(isReversed){
for(var len = node.childNodes.length, i = len - 1; i >= 0; i--){
self._recursivePreorderTraversalFilter(node.childNodes[i], filterFunction, true);
if(!!this._bStopFindingNextElement){
break;
}
}
}else{
for(var i=0, len = node.childNodes.length; i < len; i++){
self._recursivePreorderTraversalFilter(node.childNodes[i], filterFunction);
if(!!this._bStopFindingNextElement){
break;
}
}
}
}
},
_isFocusTag : function(){
var self = this;
var aFocusTagViaTabKey = ["A", "BUTTON", "INPUT", "TEXTAREA"];
var bFocusTagExists = false;
for(var i = 0, len = aFocusTagViaTabKey.length; i < len; i++){
if(self.nodeType === 1 && self.nodeName && self.nodeName.toUpperCase() == aFocusTagViaTabKey[i] && !self.disabled && jindo.$Element(self).visible()){
bFocusTagExists = true;
break;
}
}
return bFocusTagExists;
},
$ON_FOCUS_BEFORE_ELEMENT : function() {
this._currentPreviousFocusElement = null;
if(this.htAccessOption.sBeforeElementId){
this._currentPreviousFocusElement = document.getElementById(this.htAccessOption.sBeforeElementId);
}else{
this._currentPreviousFocusElement = this._findPreviousFocusElement(this.elAppContainer);
}
if(this._currentPreviousFocusElement){
window.focus();
this._currentPreviousFocusElement.focus();
}else if(parent && parent.nhn && parent.nhn.husky && parent.nhn.husky.EZCreator && parent.nhn.husky.EZCreator.elIFrame){
parent.focus();
if(this._currentPreviousFocusElement = this._findPreviousFocusElement(parent.nhn.husky.EZCreator.elIFrame)){
this._currentPreviousFocusElement.focus();
}
}
},
_findPreviousFocusElement : function(targetElement){
var target = null;
var el = targetElement.previousSibling;
while(el){
if(el.nodeType !== 1){ 
el = this._switchToSiblingOrNothing(el, /*isReversed*/true);
if(!el){
break;
}else{
continue;
}
}
this._recursivePreorderTraversalFilter(el, this._isFocusTag, true);
if(this._previousFocusElement){
target = this._previousFocusElement;
this._bStopFindingNextElement = false;
this._previousFocusElement = null;
break;
}else{
el = this._switchToSiblingOrNothing(el, /*isReversed*/true);
if(!el){
break;
}
}
}
return target;
},
$ON_FOCUS_TOOLBAR_AREA : function(){
this.oButton = jindo.$$.getSingle("BUTTON.se2_font_family", this.elAppContainer);
if(this.oButton && !this.oButton.disabled){
window.focus();
this.oButton.focus();
}
},
$ON_OPEN_HELP_POPUP : function() {
this.oApp.exec("DISABLE_ALL_UI", [{aExceptions: ["se2_accessibility"]}]);
this.oApp.exec("SHOW_EDITING_AREA_COVER");
this.oApp.exec("SELECT_UI", ["se2_accessibility"]);
this.nCalcX = this.htTopLeftCorner.x + this.oApp.getEditingAreaWidth() - this.nLayerWidth;
this.nCalcY = this.htTopLeftCorner.y - 30;
this.oApp.exec("SHOW_DIALOG_LAYER", [this.elHelpPopupLayer, {
elHandle: this.elTitle,
nMinX : this.htTopLeftCorner.x + 0,
nMinY : this.nDefaultTop + 77,
nMaxX : this.nCalcX,
nMaxY : this.nCalcY
}]);
this.welHelpPopupLayer.offset(this.nCalcY, (this.nCalcX)/2);
if(jindo.$Agent().navigator().ie) {
window.focus();
}
var self = this;
setTimeout(function(){
try{
self.oCloseButton2.focus();
}catch(e){
}
},200);
},
$ON_CLOSE_HELP_POPUP : function() {
this.oApp.exec("ENABLE_ALL_UI");	
this.oApp.exec("DESELECT_UI", ["helpPopup"]);
this.oApp.exec("HIDE_ALL_DIALOG_LAYER", []);
this.oApp.exec("HIDE_EDITING_AREA_COVER");	
this.oApp.exec("FOCUS");
}
});
nhn.husky.SE2M_SCharacter = jindo.$Class({
name : "SE2M_SCharacter",
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["sCharacter", "click", "TOGGLE_SCHARACTER_LAYER"]);
this.oApp.registerLazyMessage(["TOGGLE_SCHARACTER_LAYER"], ["hp_SE2M_SCharacter$Lazy.js"]);
}
});
nhn.husky.SE2M_FindReplacePlugin = jindo.$Class({
name : "SE2M_FindReplacePlugin",
oEditingWindow : null,
oFindReplace :  null,
bFindMode : true,
bLayerShown : false,
$init : function(){
this.nDefaultTop = 20;
},
$ON_MSG_APP_READY : function(){
this.oEditingWindow = this.oApp.getWYSIWYGWindow();
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+f", "SHOW_FIND_LAYER", []]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+h", "SHOW_REPLACE_LAYER", []]);
this.oApp.exec("REGISTER_UI_EVENT", ["findAndReplace", "click", "TOGGLE_FIND_REPLACE_LAYER"]);
this.oApp.registerLazyMessage(["TOGGLE_FIND_REPLACE_LAYER","SHOW_FIND_LAYER","SHOW_REPLACE_LAYER","SHOW_FIND_REPLACE_LAYER"], ["hp_SE2M_FindReplacePlugin$Lazy.js","N_FindReplace.js"]);
},
$ON_SHOW_ACTIVE_LAYER : function(){
this.oApp.exec("HIDE_DIALOG_LAYER", [this.elDropdownLayer]);
}
});
nhn.husky.SE2M_Quote = jindo.$Class({
name : "SE2M_Quote",
htQuoteStyles_view : null,
$init : function(){
var htConfig = nhn.husky.SE2M_Configuration.Quote || {};
var sImageBaseURL = htConfig.sImageBaseURL;
this.nMaxLevel = htConfig.nMaxLevel || 14;
this.htQuoteStyles_view = {};
this.htQuoteStyles_view["se2_quote1"] = "_zoom:1;padding:0 8px; margin:0 0 30px 20px; margin-right:15px; border-left:2px solid #cccccc;color:#888888;";
this.htQuoteStyles_view["se2_quote2"] = "_zoom:1;margin:0 0 30px 13px;padding:0 8px 0 16px;background:url("+sImageBaseURL+"/bg_quote2.gif) 0 3px no-repeat;color:#888888;";
this.htQuoteStyles_view["se2_quote3"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px dashed #cccccc;color:#888888;";
this.htQuoteStyles_view["se2_quote4"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px dashed #66b246;color:#888888;";
this.htQuoteStyles_view["se2_quote5"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px dashed #cccccc;background:url("+sImageBaseURL+"/bg_b1.png) repeat;_background:none;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+sImageBaseURL+"/bg_b1.png',sizingMethod='scale');color:#888888;";
this.htQuoteStyles_view["se2_quote6"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px solid #e5e5e5;color:#888888;";
this.htQuoteStyles_view["se2_quote7"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px solid #66b246;color:#888888;";
this.htQuoteStyles_view["se2_quote8"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:1px solid #e5e5e5;background:url("+sImageBaseURL+"/bg_b1.png) repeat;_background:none;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+sImageBaseURL+"/bg_b1.png',sizingMethod='scale');color:#888888;";
this.htQuoteStyles_view["se2_quote9"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:2px solid #e5e5e5;color:#888888;";
this.htQuoteStyles_view["se2_quote10"] = "_zoom:1;margin:0 0 30px 0;padding:10px;border:2px solid #e5e5e5;background:url("+sImageBaseURL+"/bg_b1.png) repeat;_background:none;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+sImageBaseURL+"/bg_b1.png',sizingMethod='scale');color:#888888;";
},
_assignHTMLElements : function(){
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_seditor_blockquote_layer", this.oApp.htOptions.elAppContainer);
this.aLI = jindo.$$("LI", this.elDropdownLayer);
},
$ON_REGISTER_CONVERTERS : function(){
this.oApp.exec("ADD_CONVERTER", ["DB_TO_IR", jindo.$Fn(function(sContents){
sContents = sContents.replace(/<(blockquote)[^>]*class=['"]?(se2_quote[0-9]+)['"]?[^>]*>/gi, "<$1 class=$2>");
return sContents;
}, this).bind()]);
this.oApp.exec("ADD_CONVERTER", ["IR_TO_DB", jindo.$Fn(function(sContents){
var htQuoteStyles_view = this.htQuoteStyles_view;
sContents = sContents.replace(/<(blockquote)[^>]*class=['"]?(se2_quote[0-9]+)['"]?[^>]*>/gi, function(sAll, sTag, sClassName){
return '<'+sTag+' class='+sClassName+' style="'+htQuoteStyles_view[sClassName]+'">';
});
return sContents;
}, this).bind()]);
this.htSE1toSE2Map = {
"01" : "1",
"02" : "2",
"03" : "6",
"04" : "8",
"05" : "9",
"07" : "3",
"08" : "5"
};
},
$LOCAL_BEFORE_FIRST : function(){
this._assignHTMLElements();
this.oApp.registerBrowserEvent(this.elDropdownLayer, "click", "EVENT_SE2_BLOCKQUOTE_LAYER_CLICK", []);
this.oApp.delayedExec("SE2_ATTACH_HOVER_EVENTS", [this.aLI], 0);
},
$ON_MSG_APP_READY: function(){
this.oApp.exec("REGISTER_UI_EVENT", ["quote", "click", "TOGGLE_BLOCKQUOTE_LAYER"]);
this.oApp.registerLazyMessage(["TOGGLE_BLOCKQUOTE_LAYER"], ["hp_SE2M_Quote$Lazy.js"]);
},
$ON_EVENT_EDITING_AREA_KEYDOWN : function(weEvent) {
var oSelection,
elParentQuote;
if ('WYSIWYG' !== this.oApp.getEditingMode()){
return;
}
if(8 !== weEvent.key().keyCode){
return;
}
oSelection = this.oApp.getSelection();
oSelection.fixCommonAncestorContainer();
elParentQuote = this._findParentQuote(oSelection.commonAncestorContainer);
if(!elParentQuote){
return;
}
if(this._isBlankQuote(elParentQuote)){
weEvent.stop(jindo.$Event.CANCEL_DEFAULT);
oSelection.selectNode(elParentQuote);
oSelection.collapseToStart();
jindo.$Element(elParentQuote).leave();
oSelection.select();
}
},
$ON_EVENT_EDITING_AREA_KEYUP : function(weEvent) {
var oSelection,
elParentQuote,
oP;
if ('WYSIWYG' !== this.oApp.getEditingMode()){
return;
}
if(46 !== weEvent.key().keyCode){
return;
}
oSelection = this.oApp.getSelection();
oSelection.fixCommonAncestorContainer();
elParentQuote = this._findParentQuote(oSelection.commonAncestorContainer);
if(!elParentQuote){
return false;
}
if(!elParentQuote.nextSibling){
weEvent.stop(jindo.$Event.CANCEL_DEFAULT);
oP = oSelection._document.createElement("P");
oP.innerHTML = "&nbsp;";
jindo.$Element(elParentQuote).after(oP);
setTimeout(jindo.$Fn(function(oSelection){
var sBookmarkID = oSelection.placeStringBookmark();
oSelection.select();
oSelection.removeStringBookmark(sBookmarkID);
},this).bind(oSelection), 0);
}
},
_isBlankQuote : function(elParentQuote){
var	elChild,
aChildNodes,
i, nLen,
bChrome = this.oApp.oNavigator.chrome,
bSafari = this.oApp.oNavigator.safari,
isBlankText = function(sText){
sText = sText.replace(/[\r\n]/ig, '').replace(unescape("%uFEFF"), '');
if(sText === ""){
return true;
}
if(sText === "&nbsp;" || sText === " "){
return true;
}
return false;
},
isBlank = function(oNode){
if(oNode.nodeType === 3 && isBlankText(oNode.nodeValue)){
return true;
}
if((oNode.tagName === "P" || oNode.tagName === "SPAN") &&
(isBlankText(oNode.innerHTML) || oNode.innerHTML === "<br>")){
return true;
}
return false;
},
isBlankTable = function(oNode){
if((jindo.$$("tr", oNode)).length === 0){
return true;
}
return false;
};
if(isBlankText(elParentQuote.innerHTML) || elParentQuote.innerHTML === "<br>"){
return true;
}
if(bChrome || bSafari){
var aTable = jindo.$$("TABLE", elParentQuote),
nTable = aTable.length,
elTable;
for(i=0; i<nTable; i++){
elTable = aTable[i];
if(isBlankTable(elTable)){
jindo.$Element(elTable).leave();
}
}
}
aChildNodes = elParentQuote.childNodes;
for(i=0, nLen=aChildNodes.length; i<nLen; i++){
elChild = aChildNodes[i];
if(!isBlank(elChild)){
return false;
}
}
return true;
},
_findParentQuote : function(el){
return this._findAncestor(jindo.$Fn(function(elNode){
if(!elNode){return false;}
if(elNode.tagName !== "BLOCKQUOTE"){return false;}
if(!elNode.className){return false;}
var sClassName = elNode.className;
if(!this.htQuoteStyles_view[sClassName]){return false;}
return true;
}, this).bind(), el);
},
_findAncestor : function(fnCondition, elNode){
while(elNode && !fnCondition(elNode)){elNode = elNode.parentNode;}
return elNode;
}
});
nhn.husky.SE2M_TableCreator = jindo.$Class({
name : "SE2M_TableCreator",
_sSETblClass : "__se_tbl",
nRows : 3,
nColumns : 4,
nBorderSize : 1,
sBorderColor : "#000000",
sBGColor: "#000000",
nBorderStyleIdx : 3,
nTableStyleIdx : 1,
nMinRows : 1,
nMaxRows : 20,
nMinColumns : 1,
nMaxColumns : 20,
nMinBorderWidth : 1,
nMaxBorderWidth : 10,
rxLastDigits : null,
sReEditGuideMsg_table : null,
oSelection : null,
$ON_MSG_APP_READY : function(){
this.sReEditGuideMsg_table = this.oApp.$MSG("SE2M_ReEditAction.reEditGuideMsg.table");
this.oApp.exec("REGISTER_UI_EVENT", ["table", "click", "TOGGLE_TABLE_LAYER"]);
this.oApp.registerLazyMessage(["TOGGLE_TABLE_LAYER"], ["hp_SE2M_TableCreator$Lazy.js"]);
},
$ON_REGISTER_CONVERTERS : function(){
this.oApp.exec("ADD_CONVERTER_DOM", ["IR_TO_DB", jindo.$Fn(this.irToDbDOM, this).bind()]);
this.oApp.exec("ADD_CONVERTER_DOM", ["DB_TO_IR", jindo.$Fn(this.dbToIrDOM, this).bind()]);
},
irToDbDOM : function(oTmpNode){
var aNoBorderTable = [];
var aTables = jindo.$$('table[class=__se_tbl]', oTmpNode, {oneTimeOffCache:true});
jindo.$A(aTables).forEach(function(oValue, nIdx, oArray) {
if(jindo.$Element(oValue).attr("attr_no_border_tbl")){
aNoBorderTable.push(oValue);
}
}, this);
if(aNoBorderTable.length < 1){
return;
}
var aTDs = [], oTable;
for(var i = 0, nCount = aNoBorderTable.length; i < nCount; i++){
oTable = aNoBorderTable[i];
jindo.$Element(oTable).css({"border": "", "borderLeft": "", "borderBottom": ""});
jindo.$Element(oTable).attr({"border": 0, "cellpadding": 1});
aTDs = jindo.$$('tbody>tr>td', oTable);
jindo.$A(aTDs).forEach(function(oTD, nIdx, oTDArray) {
jindo.$Element(oTD).css({"border": "", "borderTop": "", "borderRight": ""});
});
}
},
dbToIrDOM : function(oTmpNode){
var aNoBorderTable = [];
var aTables = jindo.$$('table[class=__se_tbl]', oTmpNode, {oneTimeOffCache:true});
jindo.$A(aTables).forEach(function(oValue, nIdx, oArray) {
if(jindo.$Element(oValue).attr("attr_no_border_tbl")){
aNoBorderTable.push(oValue);
}
}, this);
if(aNoBorderTable.length < 1){
return;
}
var aTDs = [], oTable;
for(var i = 0, nCount = aNoBorderTable.length; i < nCount; i++){
oTable = aNoBorderTable[i];
jindo.$Element(oTable).css({"border": "1px dashed #c7c7c7", "borderLeft": 0, "borderBottom": 0});
jindo.$Element(oTable).attr({"border": 1, "cellpadding": 0});
aTDs = jindo.$$('tbody>tr>td', oTable);
jindo.$A(aTDs).forEach(function(oTD, nIdx, oTDArray) {
jindo.$Element(oTD).css({"border": "1px dashed #c7c7c7", "borderTop": 0, "borderRight": 0});
});
}
}
});
nhn.husky.SE2M_TableBlockStyler = jindo.$Class({
name : "SE2M_TableBlockStyler",
nSelectedTD : 0,
htSelectedTD : {},
aTdRange : [],
$init : function(){ },
$LOCAL_BEFORE_ALL : function(){
return (this.oApp.getEditingMode() == "WYSIWYG");
},
$ON_MSG_APP_READY : function(){
this.oDocument = this.oApp.getWYSIWYGDocument();
},
$ON_EVENT_EDITING_AREA_MOUSEUP : function(wevE){
if(this.oApp.getEditingMode() != "WYSIWYG") return;
this.setTdBlock();
},
$ON_IS_SELECTED_TD_BLOCK : function(sAttr,oReturn) {
if( this.nSelectedTD > 0){
oReturn[sAttr] = true;
return oReturn[sAttr];
}else{
oReturn[sAttr] = false;
return oReturn[sAttr];
}
},
$ON_GET_SELECTED_TD_BLOCK : function(sAttr,oReturn){
oReturn[sAttr] = this.htSelectedTD.aTdCells;
},
setTdBlock : function() {
this.oApp.exec("GET_SELECTED_CELLS",['aTdCells',this.htSelectedTD]);
var aNodes = this.htSelectedTD.aTdCells;
if(aNodes){
this.nSelectedTD = aNodes.length;
}
},
$ON_DELETE_BLOCK_CONTENTS : function(){
var self = this, welParent, oBlockNode, oChildNode;
this.setTdBlock();
for (var j = 0; j < this.nSelectedTD ; j++){
jindo.$Element(this.htSelectedTD.aTdCells[j]).child( function(elChild){
welParent = jindo.$Element(elChild._element.parentNode);
welParent.remove(elChild);
oBlockNode = self.oDocument.createElement('P');
if (jindo.$Agent().navigator().firefox) {
oChildNode = self.oDocument.createElement('BR');
} else {
oChildNode = self.oDocument.createTextNode('\u00A0');
}
oBlockNode.appendChild(oChildNode);
welParent.append(oBlockNode);
}, 1);
}
}
});
nhn.husky.SE2M_StyleRemover = jindo.$Class({
name: "SE2M_StyleRemover",
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_UI_EVENT", ["styleRemover", "click", "CHOOSE_REMOVE_STYLE", []]);
},
$LOCAL_BEFORE_FIRST : function(){
this.oHuskyRange = this.oApp.getEmptySelection();
this._document = this.oHuskyRange._document;
},
$ON_CHOOSE_REMOVE_STYLE : function(oSelection){
var bSelectedBlock = false;
var htSelectedTDs = {};
this.oApp.exec("IS_SELECTED_TD_BLOCK",['bIsSelectedTd',htSelectedTDs]);
bSelectedBlock = htSelectedTDs.bIsSelectedTd;
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["REMOVE STYLE", {bMustBlockElement:true}]);
if( bSelectedBlock ){
this.oApp.exec("REMOVE_STYLE_IN_BLOCK", []);
}else{
this.oApp.exec("REMOVE_STYLE", []);
}
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["REMOVE STYLE", {bMustBlockElement:true}]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['noeffect']);
},
$ON_REMOVE_STYLE_IN_BLOCK : function(oSelection){
var htSelectedTDs = {};
this.oSelection = this.oApp.getSelection();
this.oApp.exec("GET_SELECTED_TD_BLOCK",['aTdCells',htSelectedTDs]);
var aNodes = htSelectedTDs.aTdCells;
for( var j = 0; j < aNodes.length ; j++){
this.oSelection.selectNodeContents(aNodes[j]);
this.oSelection.select();
this.oApp.exec("REMOVE_STYLE", []);
}
},
$ON_REMOVE_STYLE : function(oSelection){
if(!oSelection || !oSelection.commonAncestorContainer){
oSelection = this.oApp.getSelection();
}
if(oSelection.collapsed){return;}
oSelection.expandBothEnds();
var sBookmarkID = oSelection.placeStringBookmark();
var aNodes = oSelection.getNodes(true);
this._removeStyle(aNodes);
oSelection.moveToBookmark(sBookmarkID);
var aNodes = oSelection.getNodes(true);
for(var i=0; i<aNodes.length; i++){
var oNode = aNodes[i];
if(oNode.style && oNode.tagName != "BR" && oNode.tagName != "TD" && oNode.tagName != "TR" && oNode.tagName != "TBODY" && oNode.tagName != "TABLE"){
oNode.removeAttribute("align");
oNode.removeAttribute("style");
if((jindo.$Element(oNode).css("display") == "inline" && oNode.tagName != "IMG" && oNode.tagName != "IFRAME") && (!oNode.firstChild || oSelection._isBlankTextNode(oNode.firstChild))){
oNode.parentNode.removeChild(oNode);
}
}
}
oSelection.moveToBookmark(sBookmarkID);
if(oSelection.commonAncestorContainer.tagName === "TBODY"){
oSelection = this.oApp.getSelection();
}
oSelection.select();
var oMarker = this._document.getElementById(oSelection.HUSKY_BOOMARK_START_ID_PREFIX+sBookmarkID);
while(oMarker){
oParent = nhn.DOMFix.parentNode(oMarker);
oParent.removeChild(oMarker);
while(oParent && (!oParent.firstChild || (!oParent.firstChild.nextSibling && oSelection._isBlankTextNode(oParent.firstChild)))){
var oNextParent = oParent.parentNode;
oParent.parentNode.removeChild(oParent);
oParent = oNextParent;
}
oMarker = this._document.getElementById(oSelection.HUSKY_BOOMARK_START_ID_PREFIX+sBookmarkID);
}
var oMarker = this._document.getElementById(oSelection.HUSKY_BOOMARK_END_ID_PREFIX+sBookmarkID);
while(oMarker){
oParent = nhn.DOMFix.parentNode(oMarker);
oParent.removeChild(oMarker);
while(oParent && (!oParent.firstChild || (!oParent.firstChild.nextSibling && oSelection._isBlankTextNode(oParent.firstChild)))){
var oNextParent = oParent.parentNode;
oParent.parentNode.removeChild(oParent);
oParent = oNextParent;
}
oMarker = this._document.getElementById(oSelection.HUSKY_BOOMARK_END_ID_PREFIX+sBookmarkID);
}
this.oApp.exec("CHECK_STYLE_CHANGE");
},
$ON_REMOVE_STYLE2 : function(aNodes){
this._removeStyle(aNodes);
},
$ON_REMOVE_STYLE_AND_PASTE_HTML : function(sHtml, bNoUndo){
var htBrowser,
elDivHolder,
elFirstTD,
aNodesInSelection,
oSelection;
htBrowser = jindo.$Agent().navigator();
if(!sHtml) {return false;}
if(this.oApp.getEditingMode() != "WYSIWYG"){
this.oApp.exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);
}
if(!bNoUndo){
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["REMOVE STYLE AND PASTE HTML"]);
}
oSelection = this.oApp.getSelection();
oSelection.deleteContents();
elDivHolder = this.oApp.getWYSIWYGDocument().createElement("DIV");
oSelection.insertNode(elDivHolder);
if (!!htBrowser.webkit) {
elDivHolder.innerHTML = "&nbsp;";
}
oSelection.selectNode(elDivHolder);
this.oApp.exec("REMOVE_STYLE", [oSelection]);
if( htBrowser.ie ){
sHtml += "<p>&nbsp;</p>";
}else if(htBrowser.firefox){
sHtml += "<p>﻿<br></p>";
}
oSelection.selectNode(elDivHolder);
oSelection.pasteHTML(sHtml);
aNodesInSelection = oSelection.getNodes() || [];
for(var i = 0; i < aNodesInSelection.length ; i++){
if(!!aNodesInSelection[i].tagName && aNodesInSelection[i].tagName.toLowerCase() == 'td'){
elFirstTD = aNodesInSelection[i];
oSelection.selectNodeContents(elFirstTD.firstChild || elFirstTD);
oSelection.collapseToStart();
oSelection.select();
break;
}
}
oSelection.collapseToEnd();
oSelection.select();
this.oApp.exec("FOCUS");
if (!elDivHolder) {
elDivHolder.parentNode.removeChild(elDivHolder);
}
if(!bNoUndo){
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["REMOVE STYLE AND PASTE HTML"]);
}
},
_removeStyle : function(aNodes){
var arNodes = jindo.$A(aNodes);
for(var i=0; i<aNodes.length; i++){
var oNode = aNodes[i];
if(!oNode || !oNode.parentNode || !oNode.parentNode.tagName){continue;}
var bDontSplit = false;
if(jindo.$Element(oNode.parentNode).css("display") != "inline"){
continue;
}
var parent = oNode.parentNode;
if(oNode.firstChild){
if(arNodes.indexOf(this.oHuskyRange._getVeryLastRealChild(oNode)) == -1){continue;}
if(arNodes.indexOf(this.oHuskyRange._getVeryFirstRealChild(oNode)) == -1){continue;}
}
if(!oNode.nextSibling){
i--;
var tmp = oNode;
while(tmp){
var prevNode = tmp.previousSibling;
parent.parentNode.insertBefore(tmp, parent.nextSibling);
if(!prevNode){break;}
if(arNodes.indexOf(this._getVeryFirst(prevNode)) == -1){break;}
tmp = prevNode;
}
if(parent.childNodes.length === 0){parent.parentNode.removeChild(parent);}
continue;
}
if(arNodes.indexOf(this._getVeryLast(oNode.nextSibling)) != -1){continue;}
i--;
if(arNodes.indexOf(this._getVeryFirst(oNode.parentNode)) != -1){
var tmp = oNode;
var lastInserted = parent;
while(tmp){
var prevNode = tmp.previousSibling;
parent.parentNode.insertBefore(tmp, lastInserted);
lastInserted = tmp;
if(!prevNode){break;}
tmp = prevNode;
}
if(parent.childNodes.length === 0){parent.parentNode.removeChild(parent);}
}else{
if(bDontSplit){
i++;
continue;
}
var oContainer = this._document.createElement("SPAN");
var tmp = oNode;
parent.insertBefore(oContainer, tmp.nextSibling);
while(tmp){
var prevNode = tmp.previousSibling;
oContainer.insertBefore(tmp, oContainer.firstChild);
if(!prevNode){break;}
if(arNodes.indexOf(this._getVeryFirst(prevNode)) == -1){break;}
tmp = prevNode;
}
this._splitAndAppendAtTop(oContainer);
while(oContainer.firstChild){
oContainer.parentNode.insertBefore(oContainer.firstChild, oContainer);
}
oContainer.parentNode.removeChild(oContainer);
}
}
},
_splitAndAppendAtTop : function(oSpliter){
var targetNode = oSpliter;
var oTmp = targetNode;
var oCopy = oTmp;
while(jindo.$Element(oTmp.parentNode).css("display") == "inline"){
var oNode = oTmp.parentNode.cloneNode(false);
while(oTmp.nextSibling){
oNode.appendChild(oTmp.nextSibling);
}
oTmp = oTmp.parentNode;
oNode.insertBefore(oCopy, oNode.firstChild);
oCopy = oNode;
}
oTop = oTmp.parentNode;
oTop.insertBefore(targetNode, oTmp.nextSibling);
oTop.insertBefore(oCopy, targetNode.nextSibling);
},
_getVeryFirst : function(oNode){
if(!oNode){return null;}
if(oNode.firstChild){
return this.oHuskyRange._getVeryFirstRealChild(oNode);
}else{
return oNode;
}
},
_getVeryLast : function(oNode){
if(!oNode){return null;}
if(oNode.lastChild){
return this.oHuskyRange._getVeryLastRealChild(oNode);
}else{
return oNode;
}
}
});
nhn.husky.SE2M_TableEditor = jindo.$Class({
name : "SE2M_TableEditor",
_sSETblClass : "__se_tbl",
_sSEReviewTblClass : "__se_tbl_review",
STATUS : {
S_0 : 1,			
MOUSEDOWN_CELL : 2,	
CELL_SELECTING : 3,	
CELL_SELECTED : 4,	
MOUSEOVER_BORDER : 5,
MOUSEDOWN_BORDER : 6
},
CELL_SELECTION_CLASS : "se2_te_selection",
MIN_CELL_WIDTH : 5,
MIN_CELL_HEIGHT : 5,
TMP_BGC_ATTR : "_se2_tmp_te_bgc",
TMP_BGIMG_ATTR : "_se2_tmp_te_bg_img",
ATTR_TBL_TEMPLATE : "_se2_tbl_template",
nStatus : 1,
nMouseEventsStatus : 0,
aSelectedCells : [],
$ON_REGISTER_CONVERTERS : function(){
this.oApp.exec("ADD_CONVERTER_DOM", ["WYSIWYG_TO_IR", jindo.$Fn(function(elTmpNode){
if(this.aSelectedCells.length < 1){
return;
}
var aCells;
var aCellType = ["TD", "TH"];
for(var n = 0; n < aCellType.length; n++){
aCells = elTmpNode.getElementsByTagName(aCellType[n]);
for(var i = 0, nLen = aCells.length; i < nLen; i++){
if(aCells[i].className){
aCells[i].className = aCells[i].className.replace(this.CELL_SELECTION_CLASS, "");
if(aCells[i].getAttribute(this.TMP_BGC_ATTR)){
aCells[i].style.backgroundColor = aCells[i].getAttribute(this.TMP_BGC_ATTR);
aCells[i].removeAttribute(this.TMP_BGC_ATTR);
}else if(aCells[i].getAttribute(this.TMP_BGIMG_ATTR)){
jindo.$Element(this.aCells[i]).css("backgroundImage",aCells[i].getAttribute(this.TMP_BGIMG_ATTR));
aCells[i].removeAttribute(this.TMP_BGIMG_ATTR);
}
}
}
}
}, this).bind()]);
},
$ON_MSG_APP_READY : function(){
this.oApp.registerLazyMessage(["EVENT_EDITING_AREA_MOUSEMOVE","STYLE_TABLE"], ["hp_SE2M_TableEditor$Lazy.js","SE2M_TableTemplate.js"]);
}
});
nhn.husky.SE2M_QuickEditor_Common = jindo.$Class({
name : "SE2M_QuickEditor_Common",
_environmentData : "",
_currentType :"",
_in_event : false,
_bUseConfig : false,
_sBaseAjaxUrl : "",
_sAddTextAjaxUrl : "",
$init : function() {
this.waHotkeys = new jindo.$A([]);
this.waHotkeyLayers = new jindo.$A([]);
},
$ON_MSG_APP_READY : function() {
var htConfiguration = nhn.husky.SE2M_Configuration.QuickEditor;
if(htConfiguration){
this._bUseConfig = (!!htConfiguration.common && typeof htConfiguration.common.bUseConfig !== "undefined") ? htConfiguration.common.bUseConfig : true;
}
if(!this._bUseConfig){
this.setData("{table:'full',img:'full',review:'full'}");
} else {
this._sBaseAjaxUrl = htConfiguration.common.sBaseAjaxUrl;
this._sAddTextAjaxUrl = htConfiguration.common.sAddTextAjaxUrl;
this.getData();
}
this.oApp.registerLazyMessage(["OPEN_QE_LAYER"], ["hp_SE2M_QuickEditor_Common$Lazy.js"]);
},
$ON_EVENT_EDITING_AREA_KEYDOWN : function(oEvent){
var oKeyInfo = oEvent.key();
if (oKeyInfo.keyCode == 8 || oKeyInfo.keyCode == 46 ) {
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && htBrowser.nativeVersion > 8){
var elFirstChild = jindo.$$.getSingle("DIV.husky_seditor_editing_area_container").childNodes[0];
if((elFirstChild.tagName == "DIV") && (elFirstChild.style.zIndex == 1000)){
elFirstChild.parentNode.removeChild(elFirstChild);
}
}
this.oApp.exec("CLOSE_QE_LAYER", [oEvent]);
}
},
getData : function() {
var self = this;
jindo.$Ajax(self._sBaseAjaxUrl, {
type : "jsonp",
timeout : 1,
onload: function(rp) {
var result = rp.json().result;
if (!!result && !!result.text_data) {
self.setData(result.text_data);
} else {
self.setData("{table:'full',img:'full',review:'full'}");
}
},
onerror : function() {
self.setData("{table:'full',img:'full',review:'full'}");
},
ontimeout : function() {
self.setData("{table:'full',img:'full',review:'full'}");
}
}).request({ text_key : "qeditor_fold" });
},
setData : function(sResult){
var oResult = {
table : "full",
img : "full",
review : "full"
};
if(sResult){
oResult = eval("("+sResult+")");
}
this._environmentData = {
table : {
isOpen   : false,
type     : oResult["table"],
isFixed  : false,
position : []
},
img : {
isOpen   : false,
type     : oResult["img"],
isFixed  : false
},
review : {
isOpen   : false,
type     : oResult["review"],
isFixed  : false,
position : []
}
};
this.waTableTagNames =jindo.$A(["table","tbody","td","tfoot","th","thead","tr"]);
},
$ON_REGISTER_HOTKEY : function(sHotkey, sCMD, aArgs){
if(sHotkey != "tab" && sHotkey != "shift+tab"){
this.waHotkeys.push([sHotkey, sCMD, aArgs]);
}
}
});
function Shortcut(sKey,sId){
var sKey = sKey.replace(/\s+/g,"");
var store = Shortcut.Store;
var action = Shortcut.Action;
if(typeof sId === "undefined"&&sKey.constructor == String){
store.set("document",sKey,document);
return action.init(store.get("document"),sKey);
}else if(sId.constructor == String&&sKey.constructor == String){
store.set(sId,sKey,jindo.$(sId));
return action.init(store.get(sId),sKey);
}else if(sId.constructor != String&&sKey.constructor == String){
var fakeId = "nonID"+new Date().getTime();
fakeId = Shortcut.Store.searchId(fakeId,sId);
store.set(fakeId,sKey,sId);
return action.init(store.get(fakeId),sKey);
}
alert(sId+"는 반드시 string이거나  없어야 됩니다.");
};
Shortcut.Store = {
anthorKeyHash:{},
datas:{},
currentId:"",
currentKey:"",
searchId:function(sId,oElement){
jindo.$H(this.datas).forEach(function(oValue,sKey){
if(oElement == oValue.element){
sId = sKey;
jindo.$H.Break();
}
});
return sId;
},
set:function(sId,sKey,oElement){
this.currentId = sId;
this.currentKey = sKey;
var idData = this.get(sId);
this.datas[sId] =  idData?idData.createKey(sKey):new Shortcut.Data(sId,sKey,oElement);
},
get:function(sId,sKey){
if(sKey){
return this.datas[sId].keys[sKey];
}else{
return this.datas[sId];
}
},
reset:function(sId){
var data = this.datas[sId];
Shortcut.Helper.bind(data.func,data.element,"detach");
delete this.datas[sId];
},
allReset: function(){
jindo.$H(this.datas).forEach(jindo.$Fn(function(value,key) {
this.reset(key);
},this).bind());
}
};
Shortcut.Data = jindo.$Class({
$init:function(sId,sKey,oElement){
this.id = sId;
this.element = oElement;
this.func = jindo.$Fn(this.fire,this).bind();
Shortcut.Helper.bind(this.func,oElement,"attach");
this.keys = {};
this.keyStemp = {};
this.createKey(sKey);
},
createKey:function(sKey){
this.keyStemp[Shortcut.Helper.keyInterpretor(sKey)] = sKey;
this.keys[sKey] = {};
var data = this.keys[sKey];
data.key = sKey;
data.events = [];
data.commonExceptions = [];
data.stopDefalutBehavior = true;
return this;
},
getKeyStamp : function(eEvent){
var sKey     = eEvent.keyCode || eEvent.charCode;
var returnVal = "";
returnVal += eEvent.altKey?"1":"0";
returnVal += eEvent.ctrlKey?"1":"0";
returnVal += eEvent.metaKey?"1":"0";
returnVal += eEvent.shiftKey?"1":"0";
returnVal += sKey;
return returnVal;
},
fire:function(eEvent){
eEvent = eEvent||window.eEvent;
var oMatchKeyData = this.keyStemp[this.getKeyStamp(eEvent)];
if(oMatchKeyData){
this.excute(new jindo.$Event(eEvent),oMatchKeyData);
}
},
excute:function(weEvent,sRawKey){
var isExcute = true;
var staticFun = Shortcut.Helper;
var data = this.keys[sRawKey];
if(staticFun.notCommonException(weEvent,data.commonExceptions)){
jindo.$A(data.events).forEach(function(v){
if(data.stopDefalutBehavior){
var leng = v.exceptions.length;
if(leng){
for(var i=0;i<leng;i++){
if(!v.exception[i](weEvent)){
isExcute = false;
break;
}
}
if(isExcute){
v.event(weEvent);
if(jindo.$Agent().navigator().ie){
var e = weEvent._event;
e.keyCode = "";
e.charCode = "";
}
weEvent.stop();
}else{
jindo.$A.Break();
}
}else{
v.event(weEvent);
if(jindo.$Agent().navigator().ie){
var e = weEvent._event;
e.keyCode = "";
e.charCode = "";
}
weEvent.stop();
}
}
});
}
},
addEvent:function(fpEvent,sRawKey){
var events = this.keys[sRawKey].events;
if(!Shortcut.Helper.hasEvent(fpEvent,events)){
events.push({
event:fpEvent,
exceptions:[]
});
};
},
addException:function(fpException,sRawKey){
var commonExceptions = this.keys[sRawKey].commonExceptions;
if(!Shortcut.Helper.hasException(fpException,commonExceptions)){
commonExceptions.push(fpException);
};
},
removeException:function(fpException,sRawKey){
var commonExceptions = this.keys[sRawKey].commonExceptions;
commonExceptions = jindo.$A(commonExceptions).filter(function(exception){
 return exception!=fpException;
}).$value();
},
removeEvent:function(fpEvent,sRawKey){
var events = this.keys[sRawKey].events;
events = jindo.$A(events).filter(function(event) {
return event!=fpEvent;
}).$value();
this.unRegister(sRawKey);
},
unRegister:function(sRawKey){
var aEvents = this.keys[sRawKey].events;
if(aEvents.length)
delete this.keys[sRawKey];
var hasNotKey = true;
for(var i in this.keys){
hasNotKey  =false;
break;
}
if(hasNotKey){
Shortcut.Helper.bind(this.func,this.element,"detach");
delete Shortcut.Store.datas[this.id];
}
},
startDefalutBehavior: function(sRawKey){
this._setDefalutBehavior(sRawKey,false);
},
stopDefalutBehavior: function(sRawKey){
this._setDefalutBehavior(sRawKey,true);
},
_setDefalutBehavior: function(sRawKey,bType){
this.keys[sRawKey].stopDefalutBehavior = bType;
}
});
Shortcut.Helper = {
keyInterpretor:function(sKey){
var keyArray = sKey.split("+");
var wKeyArray = jindo.$A(keyArray);
var returnVal = "";
returnVal += wKeyArray.has("alt")?"1":"0";
returnVal += wKeyArray.has("ctrl")?"1":"0";
returnVal += wKeyArray.has("meta")?"1":"0";
returnVal += wKeyArray.has("shift")?"1":"0";
var wKeyArray = wKeyArray.filter(function(v){
return !(v=="alt"||v=="ctrl"||v=="meta"||v=="shift")
});
var key = wKeyArray.$value()[0];
if(key){
var sKey  = Shortcut.Store.anthorKeyHash[key.toUpperCase()]||key.toUpperCase().charCodeAt(0);
returnVal += sKey;
}
return returnVal;
},
notCommonException:function(e,exceptions){
var leng = exceptions.length;
for(var i=0;i<leng ;i++){
if(!exceptions[i](e))
return false;
}
return true;
},
hasEvent:function(fpEvent,aEvents){
var nLength = aEvents.length;
for(var i=0; i<nLength; ++i){
if(aEvents.event==fpEvent){
return true;
}
};
return false;
},
hasException:function(fpException,commonExceptions){
var nLength = commonExceptions.length;
for(var i=0; i<nLength; ++i){
if(commonExceptions[i]==fpException){
return true;
}
};
return false;
},
bind:function(wfFunc,oElement,sType){
if(sType=="attach"){
domAttach(oElement,"keydown",wfFunc);
}else{
domDetach(oElement,"keydown",wfFunc);
}
}
};
(function domAttach (){
if(document.addEventListener){
window.domAttach = function(dom,ev,fn){
dom.addEventListener(ev, fn, false);
}
}else{
window.domAttach = function(dom,ev,fn){
dom.attachEvent("on"+ev, fn);
}
}
})();
(function domDetach (){
if(document.removeEventListener){
window.domDetach = function(dom,ev,fn){
dom.removeEventListener(ev, fn, false);
}
}else{
window.domDetach = function(dom,ev,fn){
dom.detachEvent("on"+ev, fn);
}
}
})();
Shortcut.Action ={
init:function(oData,sRawKey){
this.dataInstance = oData;
this.rawKey = sRawKey;
return this;
},
addEvent:function(fpEvent){
this.dataInstance.addEvent(fpEvent,this.rawKey);
return this;
},
removeEvent:function(fpEvent){
this.dataInstance.removeEvent(fpEvent,this.rawKey);
return this;
},
addException : function(fpException){
this.dataInstance.addException(fpException,this.rawKey);
return this;
},
removeException : function(fpException){
this.dataInstance.removeException(fpException,this.rawKey);
return this;
},
startDefalutBehavior: function(){
this.dataInstance.startDefalutBehavior(this.rawKey);
return this;
},
stopDefalutBehavior: function(){
this.dataInstance.stopDefalutBehavior(this.rawKey);
return this;
},
resetElement: function(){
Shortcut.Store.reset(this.dataInstance.id);
return this;
},
resetAll: function(){
Shortcut.Store.allReset();
return this;
}
};
(function (){
Shortcut.Store.anthorKeyHash = {
BACKSPACE : 8,
TAB		  : 9,
ENTER	  : 13,
ESC		  : 27,
SPACE	  : 32,
PAGEUP	  : 33,
PAGEDOWN  : 34,
END		  : 35,
HOME	  : 36,
LEFT	  : 37,
UP		  : 38,
RIGHT	  : 39,
DOWN	  : 40,
DEL	  	  : 46,
COMMA	  : 188,
PERIOD	  : 190,
SLASH	  : 191
};
var hash = Shortcut.Store.anthorKeyHash;
for(var i=1 ; i < 13 ; i++){
Shortcut.Store.anthorKeyHash["F"+i] = i+111;
}
var agent = jindo.$Agent().navigator();
if(agent.ie||agent.safari||agent.chrome){
hash.HYPHEN = 189;
hash.EQUAL  = 187;
}else{
hash.HYPHEN = 109;
hash.EQUAL  = 61;
}
})();
var shortcut = Shortcut;
nhn.husky.Hotkey = jindo.$Class({
name : "Hotkey",
$init : function(){
this.oShortcut = shortcut;
},
$ON_ADD_HOTKEY : function(sHotkey, sCMD, aArgs, elTarget){
if(!aArgs){aArgs = [];}
var func = jindo.$Fn(this.oApp.exec, this.oApp).bind(sCMD, aArgs);
this.oShortcut(sHotkey, elTarget).addEvent(func);
}
});
nhn.husky.SE_UndoRedo = jindo.$Class({
name : "SE_UndoRedo",
oCurStateIdx : null,
iMinimumSizeChange : 1,
nMaxUndoCount : 20,
nAfterMaxDeleteBuffer : 100,
sBlankContentsForFF : "<br>",
sDefaultXPath : "/HTML[0]/BODY[0]",
$init : function(){
this.aUndoHistory = [];
this.oCurStateIdx = {nIdx: 0, nStep: 0};
this.nHardLimit = this.nMaxUndoCount + this.nAfterMaxDeleteBuffer;
},
$LOCAL_BEFORE_ALL : function(sCmd){
if(sCmd.match(/_DO_RECORD_UNDO_HISTORY_AT$/)){
return true;
}
try{
if(this.oApp.getEditingMode() != "WYSIWYG"){
return false;
}
}catch(e){
return false;
}
return true;
},
$BEFORE_MSG_APP_READY : function(){
this._historyLength = 0;
this.oApp.exec("ADD_APP_PROPERTY", ["getUndoHistory", jindo.$Fn(this._getUndoHistory, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getUndoStateIdx", jindo.$Fn(this._getUndoStateIdx, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["saveSnapShot", jindo.$Fn(this._saveSnapShot, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["getLastKey", jindo.$Fn(this._getLastKey, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["setLastKey", jindo.$Fn(this._setLastKey, this).bind()]);
this._saveSnapShot();
this.oApp.exec("DO_RECORD_UNDO_HISTORY_AT", [this.oCurStateIdx, "", "", "", null, this.sDefaultXPath]);
},
_getLastKey : function(){
return this.sLastKey;
},
_setLastKey : function(sLastKey){
this.sLastKey = sLastKey;
},
$ON_MSG_APP_READY : function(){
var oNavigator = jindo.$Agent().navigator();
this.bIE = oNavigator.ie;
this.bFF = oNavigator.firefox;
this.oApp.exec("REGISTER_UI_EVENT", ["undo", "click", "UNDO"]);
this.oApp.exec("REGISTER_UI_EVENT", ["redo", "click", "REDO"]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+z", "UNDO"]);
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+y", "REDO"]);
},
$ON_UNDO : function(){
this._doRecordUndoHistory("UNDO", { nStep : 0, bSkipIfEqual : true, bMustBlockContainer : true });
if(this.oCurStateIdx.nIdx <= 0){
return;
}
var oUndoCallback = this.aUndoHistory[this.oCurStateIdx.nIdx].oUndoCallback[this.oCurStateIdx.nStep];
var sCurrentPath = this.aUndoHistory[this.oCurStateIdx.nIdx].sParentXPath[this.oCurStateIdx.nStep];
if(oUndoCallback){
this.oApp.exec(oUndoCallback.sMsg, oUndoCallback.aParams);
}
if(this.oCurStateIdx.nStep > 0){
this.oCurStateIdx.nStep--;
}else{
var oTmpHistory = this.aUndoHistory[this.oCurStateIdx.nIdx];
this.oCurStateIdx.nIdx--;
if(oTmpHistory.nTotalSteps>1){
this.oCurStateIdx.nStep = 0;
}else{
oTmpHistory = this.aUndoHistory[this.oCurStateIdx.nIdx];
this.oCurStateIdx.nStep = oTmpHistory.nTotalSteps-1;
}
}
var sUndoHistoryPath = this.aUndoHistory[this.oCurStateIdx.nIdx].sParentXPath[this.oCurStateIdx.nStep];
var bUseDefault = false;
if(sUndoHistoryPath !== sCurrentPath && sUndoHistoryPath.indexOf(sCurrentPath) === 0){
bUseDefault = true;
}
this.oApp.exec("RESTORE_UNDO_HISTORY", [this.oCurStateIdx.nIdx, this.oCurStateIdx.nStep, bUseDefault]);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
this.sLastKey = null;
},
$ON_REDO : function(){
if(this.oCurStateIdx.nIdx >= this.aUndoHistory.length){
return;
}
var oCurHistory = this.aUndoHistory[this.oCurStateIdx.nIdx];
if(this.oCurStateIdx.nIdx == this.aUndoHistory.length-1 && this.oCurStateIdx.nStep >= oCurHistory.nTotalSteps-1){
return;
}
if(this.oCurStateIdx.nStep < oCurHistory.nTotalSteps-1){
this.oCurStateIdx.nStep++;
}else{
this.oCurStateIdx.nIdx++;
oCurHistory = this.aUndoHistory[this.oCurStateIdx.nIdx];
this.oCurStateIdx.nStep = oCurHistory.nTotalSteps-1;
}
var oRedoCallback = this.aUndoHistory[this.oCurStateIdx.nIdx].oRedoCallback[this.oCurStateIdx.nStep];
if(oRedoCallback){
this.oApp.exec(oRedoCallback.sMsg, oRedoCallback.aParams);
}
this.oApp.exec("RESTORE_UNDO_HISTORY", [this.oCurStateIdx.nIdx, this.oCurStateIdx.nStep]);
this.oApp.exec("CHECK_STYLE_CHANGE", []);
this.sLastKey = null;
},
$ON_RECORD_UNDO_ACTION : function(sAction, oSaveOption){
oSaveOption = oSaveOption || { sSaveTarget : null, elSaveTarget : null, bMustBlockElement : false, bMustBlockContainer : false, bDontSaveSelection : false };
oSaveOption.nStep = 0;
oSaveOption.bSkipIfEqual = false;
oSaveOption.bTwoStepAction = false;
this._doRecordUndoHistory(sAction, oSaveOption);
},
$ON_RECORD_UNDO_BEFORE_ACTION : function(sAction, oSaveOption){
oSaveOption = oSaveOption || { sSaveTarget : null, elSaveTarget : null, bMustBlockElement : false, bMustBlockContainer : false, bDontSaveSelection : false };
oSaveOption.nStep = 0;
oSaveOption.bSkipIfEqual = false;
oSaveOption.bTwoStepAction = true;
this._doRecordUndoHistory(sAction, oSaveOption);
},
$ON_RECORD_UNDO_AFTER_ACTION : function(sAction, oSaveOption){
oSaveOption = oSaveOption || { sSaveTarget : null, elSaveTarget : null, bMustBlockElement : false, bMustBlockContainer : false, bDontSaveSelection : false };
oSaveOption.nStep = 1;
oSaveOption.bSkipIfEqual = false;
oSaveOption.bTwoStepAction = true;
this._doRecordUndoHistory(sAction, oSaveOption);
},
$ON_RESTORE_UNDO_HISTORY : function(nUndoIdx, nUndoStateStep, bUseDefault){
this.oApp.exec("HIDE_ACTIVE_LAYER");
this.oCurStateIdx.nIdx = nUndoIdx;
this.oCurStateIdx.nStep = nUndoStateStep;
var oCurHistory = this.aUndoHistory[this.oCurStateIdx.nIdx],
sContent = oCurHistory.sContent[this.oCurStateIdx.nStep],
sFullContents = oCurHistory.sFullContents[this.oCurStateIdx.nStep],
oBookmark = oCurHistory.oBookmark[this.oCurStateIdx.nStep],
sParentXPath = oCurHistory.sParentXPath[this.oCurStateIdx.nStep],
oParent = null,
sCurContent = "",
oSelection = this.oApp.getEmptySelection();
this.oApp.exec("RESTORE_IE_SELECTION");
if(bUseDefault){
this.oApp.getWYSIWYGDocument().body.innerHTML = sFullContents;
sFullContents = this.oApp.getWYSIWYGDocument().body.innerHTML;
sCurContent = sFullContents;
sParentXPath = this.sDefaultXPath;
}else{
oParent = oSelection._evaluateXPath(sParentXPath, oSelection._document);
try{
oParent.innerHTML = sContent;
sCurContent = oParent.innerHTML;
}catch(e){
this.oApp.getWYSIWYGDocument().body.innerHTML = sFullContents;
sFullContents = this.oApp.getWYSIWYGDocument().body.innerHTML;
sCurContent = sFullContents;
sParentXPath = this.sDefaultXPath;
}
}
if(this.bFF && sCurContent == this.sBlankContentsForFF){
sCurContent = "";
}
oCurHistory.sFullContents[this.oCurStateIdx.nStep] = sFullContents;
oCurHistory.sContent[this.oCurStateIdx.nStep] = sCurContent;
oCurHistory.sParentXPath[this.oCurStateIdx.nStep] = sParentXPath;
if(oBookmark && oBookmark.sType == "scroll"){
setTimeout(jindo.$Fn(function(){this.oApp.getWYSIWYGDocument().documentElement.scrollTop = oBookmark.nScrollTop;}, this).bind(), 0);
}else{
oSelection = this.oApp.getEmptySelection();
if(oSelection.selectionLoaded){
if(oBookmark){
oSelection.moveToXPathBookmark(oBookmark);
}else{
oSelection = this.oApp.getEmptySelection();
}
oSelection.select();
}
}
},
_doRecordUndoHistory : function(sAction, htRecordOption){
htRecordOption = htRecordOption || {};
var nStep = htRecordOption.nStep || 0,
bSkipIfEqual = htRecordOption.bSkipIfEqual || false,
bTwoStepAction = htRecordOption.bTwoStepAction || false,
sSaveTarget = htRecordOption.sSaveTarget || null,
elSaveTarget = htRecordOption.elSaveTarget || null,
bDontSaveSelection = htRecordOption.bDontSaveSelection || false,
bMustBlockElement = htRecordOption.bMustBlockElement || false,
bMustBlockContainer = htRecordOption.bMustBlockContainer || false,
oUndoCallback = htRecordOption.oUndoCallback,
oRedoCallback = htRecordOption.oRedoCallback;
this._historyLength = this.aUndoHistory.length;
if(this.oCurStateIdx.nIdx !== this._historyLength-1){
bSkipIfEqual = true;
}
var oCurHistory = this.aUndoHistory[this.oCurStateIdx.nIdx],
sHistoryFullContents = oCurHistory.sFullContents[this.oCurStateIdx.nStep],
sCurContent = "",
sFullContents = "",
sParentXPath = "",
oBookmark = null,
oSelection = null,
oInsertionIdx = {nIdx:this.oCurStateIdx.nIdx, nStep:this.oCurStateIdx.nStep};
oSelection = this.oApp.getSelection();
if(oSelection.selectionLoaded){
oBookmark = oSelection.getXPathBookmark();
}
if(elSaveTarget){
sParentXPath = oSelection._getXPath(elSaveTarget);
}else if(sSaveTarget){
sParentXPath = this._getTargetXPath(oBookmark, sSaveTarget);
}else{
sParentXPath = this._getParentXPath(oBookmark, bMustBlockElement, bMustBlockContainer);
}
sFullContents = this.oApp.getWYSIWYGDocument().body.innerHTML;
if(sParentXPath === this.sDefaultXPath){
sCurContent = sFullContents;
}else{
sCurContent = oSelection._evaluateXPath(sParentXPath, oSelection._document).innerHTML;
}
if(this.bFF && sCurContent == this.sBlankContentsForFF){
sCurContent = "";
}
if(!bTwoStepAction && bSkipIfEqual){
if(sHistoryFullContents.length === sFullContents.length){
return;
}
var elCurrentDiv = document.createElement("div");
var elHistoryDiv = document.createElement("div");
elCurrentDiv.innerHTML = sFullContents;
elHistoryDiv.innerHTML = sHistoryFullContents;
var elDocFragment = document.createDocumentFragment();
elDocFragment.appendChild(elCurrentDiv);
elDocFragment.appendChild(elHistoryDiv);
sFullContents = elCurrentDiv.innerHTML;
sHistoryFullContents = elHistoryDiv.innerHTML;
elCurrentDiv = null;
elHistoryDiv = null;
elDocFragment = null;
if(sHistoryFullContents.length === sFullContents.length){
return;
}
}
if(bDontSaveSelection){
oBookmark = { sType : "scroll", nScrollTop : this.oApp.getWYSIWYGDocument().documentElement.scrollTop };
}
oInsertionIdx.nStep = nStep;
if(oInsertionIdx.nStep === 0 && this.oCurStateIdx.nStep === oCurHistory.nTotalSteps-1){
oInsertionIdx.nIdx = this.oCurStateIdx.nIdx+1;
}
this._doRecordUndoHistoryAt(oInsertionIdx, sAction, sCurContent, sFullContents, oBookmark, sParentXPath, oUndoCallback, oRedoCallback);
},
$ON_DO_RECORD_UNDO_HISTORY_AT : function(oInsertionIdx, sAction, sContent, sFullContents, oBookmark, sParentXPath){
this._doRecordUndoHistoryAt(oInsertionIdx, sAction, sContent, sFullContents, oBookmark, sParentXPath);
},
_doRecordUndoHistoryAt : function(oInsertionIdx, sAction, sContent, sFullContents, oBookmark, sParentXPath, oUndoCallback, oRedoCallback){
if(oInsertionIdx.nStep !== 0){
this.aUndoHistory[oInsertionIdx.nIdx].nTotalSteps = oInsertionIdx.nStep+1;
this.aUndoHistory[oInsertionIdx.nIdx].sContent[oInsertionIdx.nStep] = sContent;
this.aUndoHistory[oInsertionIdx.nIdx].sFullContents[oInsertionIdx.nStep] = sFullContents;
this.aUndoHistory[oInsertionIdx.nIdx].oBookmark[oInsertionIdx.nStep] = oBookmark;
this.aUndoHistory[oInsertionIdx.nIdx].sParentXPath[oInsertionIdx.nStep] = sParentXPath;
this.aUndoHistory[oInsertionIdx.nIdx].oUndoCallback[oInsertionIdx.nStep] = oUndoCallback;
this.aUndoHistory[oInsertionIdx.nIdx].oRedoCallback[oInsertionIdx.nStep] = oRedoCallback;
}else{
var oNewHistory = {sAction:sAction, nTotalSteps: 1};
oNewHistory.sContent = [];
oNewHistory.sContent[0] = sContent;
oNewHistory.sFullContents = [];
oNewHistory.sFullContents[0] = sFullContents;
oNewHistory.oBookmark = [];
oNewHistory.oBookmark[0] = oBookmark;
oNewHistory.sParentXPath = [];
oNewHistory.sParentXPath[0] = sParentXPath;
oNewHistory.oUndoCallback = [];
oNewHistory.oUndoCallback[0] = oUndoCallback;
oNewHistory.oRedoCallback = [];
oNewHistory.oRedoCallback[0] = oRedoCallback;
this.aUndoHistory.splice(oInsertionIdx.nIdx, this._historyLength - oInsertionIdx.nIdx, oNewHistory);
this._historyLength = this.aUndoHistory.length;
}
if(this._historyLength > this.nHardLimit){
this.aUndoHistory.splice(0, this.nAfterMaxDeleteBuffer);
oInsertionIdx.nIdx -= this.nAfterMaxDeleteBuffer;
}
this.oCurStateIdx.nIdx = oInsertionIdx.nIdx;
this.oCurStateIdx.nStep = oInsertionIdx.nStep;
},
_saveSnapShot : function(){
this.oSnapShot = {
oBookmark : this.oApp.getSelection().getXPathBookmark()
};
},
_getTargetXPath : function(oBookmark, sSaveTarget){
var sParentXPath = this.sDefaultXPath,
aStartXPath = oBookmark[0].sXPath.split("/"),
aEndXPath = oBookmark[1].sXPath.split("/"),
aParentPath = [],
nPathLen = aStartXPath.length < aEndXPath.length ? aStartXPath.length : aEndXPath.length,
nPathIdx = 0, nTargetIdx = -1;
if(sSaveTarget === "BODY"){
return sParentXPath;
}
for(nPathIdx=0; nPathIdx<nPathLen; nPathIdx++){
if(aStartXPath[nPathIdx] !== aEndXPath[nPathIdx]){
break;
}
aParentPath.push(aStartXPath[nPathIdx]);
if(aStartXPath[nPathIdx] === "" || aStartXPath[nPathIdx] === "HTML" || aStartXPath[nPathIdx] === "BODY"){
continue;
}
if(aStartXPath[nPathIdx].indexOf(sSaveTarget) > -1){
nTargetIdx = nPathIdx;
}
}
if(nTargetIdx > -1){
aParentPath.length = nTargetIdx;
}
sParentXPath = aParentPath.join("/");
if(sParentXPath.length < this.sDefaultXPath.length){
sParentXPath = this.sDefaultXPath;
}
return sParentXPath;
},
_getParentXPath : function(oBookmark, bMustBlockElement, bMustBlockContainer){
var sParentXPath = this.sDefaultXPath,
aStartXPath, aEndXPath,
aSnapShotStart, aSnapShotEnd,
nSnapShotLen, nPathLen,
aParentPath = ["", "HTML[0]", "BODY[0]"],
nPathIdx = 0, nBlockIdx = -1,
sPath, sTag;
if(!oBookmark){
return sParentXPath;
}
if(oBookmark[0].sXPath === sParentXPath || oBookmark[1].sXPath === sParentXPath){
return sParentXPath;
}
aStartXPath = oBookmark[0].sXPath.split("/");
aEndXPath = oBookmark[1].sXPath.split("/");
aSnapShotStart = this.oSnapShot.oBookmark[0].sXPath.split("/");
aSnapShotEnd = this.oSnapShot.oBookmark[1].sXPath.split("/");
nSnapShotLen = aSnapShotStart.length < aSnapShotEnd.length ? aSnapShotStart.length : aSnapShotEnd.length;
nPathLen = aStartXPath.length < aEndXPath.length ? aStartXPath.length : aEndXPath.length;
nPathLen = nPathLen < nSnapShotLen ? nPathLen : nSnapShotLen;
if(nPathLen < 3){
return sParentXPath;
}
bMustBlockElement = bMustBlockElement || false;
bMustBlockContainer = bMustBlockContainer || false;
for(nPathIdx=3; nPathIdx<nPathLen; nPathIdx++){
sPath = aStartXPath[nPathIdx];
if(sPath !== aEndXPath[nPathIdx] ||
sPath !== aSnapShotStart[nPathIdx] ||
sPath !== aSnapShotEnd[nPathIdx] ||
aEndXPath[nPathIdx] !== aSnapShotStart[nPathIdx] ||
aEndXPath[nPathIdx] !== aSnapShotEnd[nPathIdx] ||
aSnapShotStart[nPathIdx] !== aSnapShotEnd[nPathIdx]){
break;
}
aParentPath.push(sPath);
sTag = sPath.substring(0, sPath.indexOf("["));
if(bMustBlockElement && (sTag === "P" || sTag === "LI" || sTag === "DIV")){
nBlockIdx = nPathIdx;
}else if(sTag === "UL" || sTag === "OL" || sTag === "TD" || sTag === "TR" || sTag === "TABLE" || sTag === "BLOCKQUOTE"){
nBlockIdx = nPathIdx;
}
}
if(nBlockIdx > -1){
aParentPath.length = nBlockIdx + 1;
}else if(bMustBlockElement || bMustBlockContainer){
return sParentXPath;
}
return aParentPath.join("/");
},
_getUndoHistory : function(){
return this.aUndoHistory;
},
_getUndoStateIdx : function(){
return this.oCurStateIdx;
}
});
nhn.husky.Utils = jindo.$Class({
name : "Utils",
$init : function(){
var oAgentInfo = jindo.$Agent();
var oNavigatorInfo = oAgentInfo.navigator();
if(oNavigatorInfo.ie && oNavigatorInfo.version == 6){
try{
document.execCommand('BackgroundImageCache', false, true);
}catch(e){}
}
},
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["htBrowser", jindo.$Agent().navigator()]);
},
$ON_ATTACH_HOVER_EVENTS : function(aElms, htOptions){
htOptions = htOptions || [];
var sHoverClass = htOptions.sHoverClass || "hover";
var fnElmToSrc = htOptions.fnElmToSrc || function(el){return el};
var fnElmToTarget = htOptions.fnElmToTarget || function(el){return el};
if(!aElms) return;
var wfAddClass = jindo.$Fn(function(wev){
jindo.$Element(fnElmToTarget(wev.currentElement)).addClass(sHoverClass);
}, this);
var wfRemoveClass = jindo.$Fn(function(wev){
jindo.$Element(fnElmToTarget(wev.currentElement)).removeClass(sHoverClass);
}, this);
for(var i=0, len = aElms.length; i<len; i++){
var elSource = fnElmToSrc(aElms[i]);
wfAddClass.attach(elSource, "mouseover");
wfRemoveClass.attach(elSource, "mouseout");
wfAddClass.attach(elSource, "focus");
wfRemoveClass.attach(elSource, "blur");
}
}
});
nhn.husky.DialogLayerManager = jindo.$Class({
name : "DialogLayerManager",
aMadeDraggable : null,
aOpenedLayers : null,
$init : function(){
this.aMadeDraggable = [];
this.aDraggableLayer = [];
this.aOpenedLayers = [];
},
$BEFORE_MSG_APP_READY : function() {
this.oNavigator = jindo.$Agent().navigator();
},
$ON_MSG_APP_READY : function() {
this.oApp.registerLazyMessage(["SHOW_DIALOG_LAYER","TOGGLE_DIALOG_LAYER"], ["hp_DialogLayerManager$Lazy.js", "N_DraggableLayer.js"]);
}
});
nhn.husky.ActiveLayerManager = jindo.$Class({
name : "ActiveLayerManager",
oCurrentLayer : null,
$BEFORE_MSG_APP_READY : function() {
this.oNavigator = jindo.$Agent().navigator();
},
$ON_TOGGLE_ACTIVE_LAYER : function(oLayer, sOnOpenCmd, aOnOpenParam, sOnCloseCmd, aOnCloseParam){
if(oLayer == this.oCurrentLayer){
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
}else{
this.oApp.exec("SHOW_ACTIVE_LAYER", [oLayer, sOnCloseCmd, aOnCloseParam]);
if(sOnOpenCmd){this.oApp.exec(sOnOpenCmd, aOnOpenParam);}
}
},
$ON_SHOW_ACTIVE_LAYER : function(oLayer, sOnCloseCmd, aOnCloseParam){
oLayer = jindo.$(oLayer);
var oPrevLayer = this.oCurrentLayer;
if(oLayer == oPrevLayer){return;}
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
this.sOnCloseCmd = sOnCloseCmd;
this.aOnCloseParam = aOnCloseParam;
oLayer.style.display = "block";
this.oCurrentLayer = oLayer;
this.oApp.exec("ADD_APP_PROPERTY", ["oToolBarLayer", this.oCurrentLayer]);
},
$ON_HIDE_ACTIVE_LAYER : function(){
var oLayer = this.oCurrentLayer;
if(!oLayer){return;}
oLayer.style.display = "none";
this.oCurrentLayer = null;
if(this.sOnCloseCmd){
this.oApp.exec(this.sOnCloseCmd, this.aOnCloseParam);
}
},
$ON_HIDE_ACTIVE_LAYER_IF_NOT_CHILD : function(el){
var elTmp = el;
while(elTmp){
if(elTmp == this.oCurrentLayer){
return;
}
elTmp = elTmp.parentNode;
}
this.oApp.exec("HIDE_ACTIVE_LAYER");
},
$ON_HIDE_CURRENT_ACTIVE_LAYER : function(){
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
}
});
nhn.husky.StringConverterManager = jindo.$Class({
name : "StringConverterManager",
oConverters : null,
$init : function(){
this.oConverters = {};
this.oConverters_DOM = {};
this.oAgent = jindo.$Agent().navigator();
},
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["applyConverter", jindo.$Fn(this.applyConverter, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["addConverter", jindo.$Fn(this.addConverter, this).bind()]);
this.oApp.exec("ADD_APP_PROPERTY", ["addConverter_DOM", jindo.$Fn(this.addConverter_DOM, this).bind()]);
},
applyConverter : function(sRuleName, sContents, oDocument){
var sTmpStr =  "@"+(new Date()).getTime()+"@";
var rxTmpStr = new RegExp(sTmpStr, "g");
var oRes = {sContents:sTmpStr+sContents};
oDocument = oDocument || document;
this.oApp.exec("MSG_STRING_CONVERTER_STARTED", [sRuleName, oRes]);
var aConverters;
sContents = oRes.sContents;
aConverters = this.oConverters_DOM[sRuleName];
if(aConverters){
var elContentsHolder = oDocument.createElement("DIV");
elContentsHolder.innerHTML = sContents;
for(var i=0; i<aConverters.length; i++){
aConverters[i](elContentsHolder);
}
sContents = elContentsHolder.innerHTML;
if(!!elContentsHolder.parentNode){
elContentsHolder.parentNode.removeChild(elContentsHolder);
}
elContentsHolder = null;
if( jindo.$Agent().navigator().ie ){
sTmpStr = sTmpStr +'(\r\n)?';
rxTmpStr = new RegExp(sTmpStr , "g");
}
}
aConverters = this.oConverters[sRuleName];
if(aConverters){
for(var i=0; i<aConverters.length; i++){
var sTmpContents = aConverters[i](sContents);
if(typeof sTmpContents != "undefined"){
sContents = sTmpContents;
}
}
}
oRes = {sContents:sContents};
this.oApp.exec("MSG_STRING_CONVERTER_ENDED", [sRuleName, oRes]);
oRes.sContents = oRes.sContents.replace(rxTmpStr, "");
return oRes.sContents;
},
$ON_ADD_CONVERTER : function(sRuleName, funcConverter){
var aCallerStack = this.oApp.aCallerStack;
funcConverter.sPluginName = aCallerStack[aCallerStack.length-2].name;
this.addConverter(sRuleName, funcConverter);
},
$ON_ADD_CONVERTER_DOM : function(sRuleName, funcConverter){
var aCallerStack = this.oApp.aCallerStack;
funcConverter.sPluginName = aCallerStack[aCallerStack.length-2].name;
this.addConverter_DOM(sRuleName, funcConverter);
},
addConverter : function(sRuleName, funcConverter){
var aConverters = this.oConverters[sRuleName];
if(!aConverters){
this.oConverters[sRuleName] = [];
}
this.oConverters[sRuleName][this.oConverters[sRuleName].length] = funcConverter;
},
addConverter_DOM : function(sRuleName, funcConverter){
var aConverters = this.oConverters_DOM[sRuleName];
if(!aConverters){
this.oConverters_DOM[sRuleName] = [];
}
this.oConverters_DOM[sRuleName][this.oConverters_DOM[sRuleName].length] = funcConverter;
}
});
nhn.husky.MessageManager = jindo.$Class({
name : "MessageManager",
oMessageMap : null,
sLocale : "ko_KR",
$init : function(oMessageMap, sLocale){
switch(sLocale) {
case "ja_JP" :
this.oMessageMap = oMessageMap_ja_JP;
break;
case "en_US" :
this.oMessageMap = oMessageMap_en_US;
break;
case "zh_CN" :
this.oMessageMap = oMessageMap_zh_CN;
break;
default : 
this.oMessageMap = oMessageMap;
break;
}
},
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["$MSG", jindo.$Fn(this.getMessage, this).bind()]);
},
getMessage : function(sMsg){
if(this.oMessageMap[sMsg]){return unescape(this.oMessageMap[sMsg]);}
return sMsg;
}
});
nhn.husky.LazyLoader = jindo.$Class({
name : "LazyLoader",
htMsgInfo : null,
aLoadingInfo : null,
$init : function(aToDo){
this.htMsgInfo = {};
this.aLoadingInfo = [];
this.aToDo = aToDo;
},
$ON_MSG_APP_READY : function(){
for(var i=0; i<this.aToDo.length; i++){
var htToDoDetail = this.aToDo[i];
this._createBeforeHandlersAndSaveURLInfo(htToDoDetail.oMsgs, htToDoDetail.sURL, htToDoDetail.elTarget, htToDoDetail.htOptions);
}
},
$LOCAL_BEFORE_ALL : function(sMsgHandler, aParams){
var sMsg = sMsgHandler.replace("$BEFORE_", "");
var htCurMsgInfo = this.htMsgInfo[sMsg];
if(htCurMsgInfo.nLoadingStatus == 1){return true;}
if(htCurMsgInfo.nLoadingStatus == 2){
this[sMsgHandler] = function(){
this._removeHandler(sMsgHandler);
this.oApp.delayedExec(sMsg, aParams, 0);
return false;
};
return true;
}
htCurMsgInfo.bLoadingStatus = 1;
(new jindo.$Ajax(htCurMsgInfo.sURL, {
onload : jindo.$Fn(this._onload, this).bind(sMsg, aParams)
})).request();
return true;
},
_onload : function(sMsg, aParams, oResponse){
if(oResponse._response.readyState == 4) {
this.htMsgInfo[sMsg].elTarget.innerHTML = oResponse.text();
this.htMsgInfo[sMsg].nLoadingStatus = 2;
this._removeHandler("$BEFORE_"+sMsg);
this.oApp.exec("sMsg", aParams);
}else{
this.oApp.exec(this.htMsgInfo[sMsg].sFailureCallback, []);
}
},
_removeHandler : function(sMsgHandler){
delete this[sMsgHandler];
this.oApp.createMessageMap(sMsgHandler);
},
_createBeforeHandlersAndSaveURLInfo : function(oMsgs, sURL, elTarget, htOptions){
htOptions = htOptions || {};
var htNewInfo = {
sURL : sURL,
elTarget : elTarget,
sSuccessCallback : htOptions.sSuccessCallback,
sFailureCallback : htOptions.sFailureCallback,
nLoadingStatus : 0
};
this.aLoadingInfo[this.aLoadingInfo.legnth] = htNewInfo;
if(!(oMsgs instanceof Array)){
var oPlugin = oMsgs;
oMsgs = [];
var htMsgAdded = {};
for(var sFunctionName in oPlugin){
if(sFunctionName.match(/^\$(BEFORE|ON|AFTER)_(.+)$/)){
var sMsg = RegExp.$2;
if(sMsg == "MSG_APP_READY"){continue;}
if(!htMsgAdded[sMsg]){
oMsgs[oMsgs.length] = RegExp.$2;
htMsgAdded[sMsg] = true;
}
}
}
}
for(var i=0; i<oMsgs.length; i++){
var sTmpMsg = "$BEFORE_"+oMsgs[i];
this[sTmpMsg] = function(){return false;};
this.oApp.createMessageMap(sTmpMsg);
this.htMsgInfo[oMsgs[i]] = htNewInfo;
}
}
});
nhn.husky.PopUpManager = {};
nhn.husky.PopUpManager._instance = null;
nhn.husky.PopUpManager._pluginKeyCnt = 0;
nhn.husky.PopUpManager.getInstance = function(oApp) {
if (this._instance==null) {
this._instance = new (function(){
this._whtPluginWin = new jindo.$H();
this._whtPlugin = new jindo.$H();
this.addPlugin = function(sKey, vValue){
this._whtPlugin.add(sKey, vValue);
};
this.getPlugin = function() {
return this._whtPlugin;
};
this.getPluginWin = function() {
return this._whtPluginWin;
};
this.openWindow = function(oWinOpt) {
var op= {
oApp : null,
sUrl : "",
sName : "popup",
sLeft : null,
sTop : null,
nWidth : 400,
nHeight : 400,
sProperties : null,
bScroll : true
};
for(var i in oWinOpt) op[i] = oWinOpt[i];
if(op.oApp == null) {
alert("팝업 요청시 옵션으로 oApp(허스키 reference) 값을 설정하셔야 합니다.");
}
var left = op.sLeft || (screen.availWidth-op.nWidth)/2;
var top  = op.sTop ||(screen.availHeight-op.nHeight)/2;
var sProperties = op.sProperties != null ? op.sProperties :
"top="+ top +",left="+ left +",width="+op.nWidth+",height="+op.nHeight+",scrollbars="+(op.bScroll?"yes":"no")+",status=yes";
var win = window.open(op.sUrl, op.sName,sProperties);
if (!!win) {
setTimeout( function(){
try{win.focus();}catch(e){}
}, 100);
}
this.removePluginWin(win);
this._whtPluginWin.add(this.getCorrectKey(this._whtPlugin, op.oApp), win);
return win;
};
this.getCorrectKey = function(whtData, oCompare) {
var key = null;
whtData.forEach(function(v,k){
if (v == oCompare) {
key = k;
return;
}
});
return key;
};
this.removePluginWin = function(vValue) {
var list = this._whtPluginWin.search(vValue);
if (list) {
this._whtPluginWin.remove(list);
this.removePluginWin(vValue);
}
}
})();
}
this._instance.addPlugin("plugin_" + (this._pluginKeyCnt++), oApp);
return nhn.husky.PopUpManager._instance;
};
nhn.husky.PopUpManager.setCallback = function(oOpenWin, sMsg, oData) {
if (this._instance.getPluginWin().hasValue(oOpenWin)) {
var key = this._instance.getCorrectKey(this._instance.getPluginWin(), oOpenWin);
if (key) {
this._instance.getPlugin().$(key).exec(sMsg, oData);
}
}
};
nhn.husky.PopUpManager.getFunc = function(oOpenWin, sFunc) {
if (this._instance.getPluginWin().hasValue(oOpenWin)) {
var key = this._instance.getCorrectKey(this._instance.getPluginWin(), oOpenWin);
if (key) {
return this._instance.getPlugin().$(key)[sFunc]();
}
}
};
if(typeof window.nhn == 'undefined') { window.nhn = {}; }
if(!nhn.husky) { nhn.husky = {}; }
(function(){
var ua = navigator.userAgent,
oAgent = jindo.$Agent(),
browser = oAgent.navigator(),
os = oAgent.os();
var aMatch = ua.match(/(SHW-|Chrome|Safari)/gi) || "";
if(aMatch.length === 2 && aMatch[0] === "SHW-" && aMatch[1] === "Safari"){
browser.bGalaxyBrowser = true;
}else if(ua.indexOf("LG-V500") > -1 && ua.indexOf("Version/4.0") > -1){
browser.bGPadBrowser = true;
}
if(typeof os.ios === 'undefined'){
os.ios = ua.indexOf("iPad") > -1 || ua.indexOf("iPhone") > -1;
if(os.ios){
aMatch = ua.match(/(iPhone )?OS ([\d|_]+)/);
if(aMatch != null && aMatch[2] != undefined){
os.version = String(aMatch[2]).split("_").join(".");
}
}
}
})();
nhn.husky.SE2M_UtilPlugin = jindo.$Class({
name : "SE2M_UtilPlugin",
$BEFORE_MSG_APP_READY : function(){
this.oApp.exec("ADD_APP_PROPERTY", ["oAgent", jindo.$Agent()]);
this.oApp.exec("ADD_APP_PROPERTY", ["oNavigator", jindo.$Agent().navigator()]);
this.oApp.exec("ADD_APP_PROPERTY", ["oUtils", this]);
},
$ON_REGISTER_HOTKEY : function(sHotkey, sCMD, aArgs, elTarget) {
this.oApp.exec("ADD_HOTKEY", [sHotkey, sCMD, aArgs, (elTarget || this.oApp.getWYSIWYGDocument())]);
},
$ON_SE2_ATTACH_HOVER_EVENTS : function(aElms){
this.oApp.exec("ATTACH_HOVER_EVENTS", [aElms, {fnElmToSrc: this._elm2Src, fnElmToTarget: this._elm2Target}]);
},
_elm2Src : function(el){
if(el.tagName == "LI" && el.firstChild && el.firstChild.tagName == "BUTTON"){
return el.firstChild;
}else{
return el;
}
},
_elm2Target : function(el){
if(el.tagName == "BUTTON" && el.parentNode.tagName == "LI"){
return el.parentNode;
}else{
return el;
}
},
getScrollXY : function(){
var scrollX,scrollY;
var oAppWindow = this.oApp.getWYSIWYGWindow();
if(typeof oAppWindow.scrollX == "undefined"){
scrollX = oAppWindow.document.documentElement.scrollLeft;
scrollY = oAppWindow.document.documentElement.scrollTop;
}else{
scrollX = oAppWindow.scrollX;
scrollY = oAppWindow.scrollY;
}
return {x:scrollX, y:scrollY};
}
});
nhn.husky.SE2M_Utils = {
sURLPattern : '(http|https|ftp|mailto):(?:\\/\\/)?((:?\\w|-)+(:?\\.(:?\\w|-)+)+)([^ <>]+)?',
getCustomCSS : function(sStr, rxValue, sDivision) {
var ret = [];
if('undefined' == typeof(sStr) || 'undefined' == typeof(rxValue) || !sStr || !rxValue) {
return ret;
}
var aMatch = sStr.match(rxValue);
if(aMatch && aMatch[0]&&aMatch[1]) {
if(sDivision) {
ret = aMatch[1].split(sDivision);
} else {
ret[0] = aMatch[1];
}
}
return ret;
},
toStringSamePropertiesOfArray : function(v, sKey, sSeperator) {
if (v instanceof Array) {
var a = [];
for (var i = 0; i < v.length; i++) {
a.push(v[i][sKey]);
}
return a.join(",").replace(/,/g, sSeperator);
}
else {
if (typeof v[sKey] == "undefined") {
return "";
}
if (typeof v[sKey] == "string") {
return v[sKey];
}
}
},
makeArray : function(v) {
if (v === null || typeof v === "undefined"){
return [];
}
if (v instanceof Array) {
return v;
}
var a = [];
a.push(v);
return a;
},
ellipsis : function(elText, elContainer, sStringTail, nLine) {
sStringTail = sStringTail || "...";
if (typeof nLine == "undefined") {
nLine = 1;
}
var welText = jindo.$Element(elText);
var welContainer = jindo.$Element(elContainer);
var sText = welText.html();
var nLength = sText.length;
var nCurrentHeight = welContainer.height();
var nIndex = 0;
welText.html('A');
var nHeight = welContainer.height();
if (nCurrentHeight < nHeight * (nLine + 0.5)) {
return welText.html(sText);
}
nCurrentHeight = nHeight;
while(nCurrentHeight < nHeight * (nLine + 0.5)) {
nIndex += Math.max(Math.ceil((nLength - nIndex)/2), 1);
welText.html(sText.substring(0, nIndex) + sStringTail);
nCurrentHeight = welContainer.height();
}
while(nCurrentHeight > nHeight * (nLine + 0.5)) {
nIndex--;
welText.html(sText.substring(0, nIndex) + sStringTail);
nCurrentHeight = welContainer.height();
}
},
ellipsisByPixel : function(elText, sStringTail, nPixel, fCondition) {
sStringTail = sStringTail || "...";
var welText = jindo.$Element(elText);
var nCurrentWidth = welText.width();
if (nCurrentWidth < nPixel) {
return;
}
var sText = welText.html();
var nLength = sText.length;
var nIndex = 0;
if (typeof fCondition == "undefined") {
var nWidth = welText.html('A').width();
nCurrentWidth = nWidth;
while(nCurrentWidth < nPixel) {
nIndex += Math.max(Math.ceil((nLength - nIndex)/2), 1);
welText.html(sText.substring(0, nIndex) + sStringTail);
nCurrentWidth = welText.width();
}
fCondition = function() {
return true;
};
}
nIndex = welText.html().length - sStringTail.length;
while(nCurrentWidth > nPixel) {
if (!fCondition()) {
break;
}
nIndex--;
welText.html(sText.substring(0, nIndex) + sStringTail);
nCurrentWidth = welText.width();
}
},
ellipsisElementsToDesinatedWidth : function(aElement, sStringTail, aMinWidth, fCondition) {
jindo.$A(aElement).forEach(function(el, i){
if (!el) {
jindo.$A.Continue();
}
nhn.husky.SE2M_Utils.ellipsisByPixel(el, sStringTail, aMinWidth[i], fCondition);
});
},
paddingZero : function(nNumber, nLength) {
var sResult = nNumber.toString();
while (sResult.length < nLength) {
sResult = ("0" + sResult);
}
return sResult;
},
cutStringToByte : function(sString, nByte, sTail){
if(sString === null || sString.length === 0) {
return sString;
}
sString = sString.replace(/  +/g, " ");
if (!sTail && sTail != "") {
sTail = "...";
}
var maxByte = nByte;
var n=0;
var nLen = sString.length;
for(var i=0; i<nLen;i++){
n += this.getCharByte(sString.charAt(i));
if(n == maxByte){
if(i == nLen-1) {
return sString;
} else {
return sString.substring(0,i)+sTail;
}
} else if( n > maxByte ) {
return sString.substring(0, i)+sTail;
}
}
return sString;
},
getCharByte : function(ch){
if (ch === null || ch.length < 1) {
return 0;
}
var byteSize = 0;
var str = escape(ch);
if ( str.length == 1 ) {  
byteSize ++;
} else if ( str.indexOf("%u") != -1 ) { 
byteSize += 2;
} else if ( str.indexOf("%") != -1 ) { 
byteSize += str.length/3;
}
return byteSize;
},
getFilteredHashTable : function(htUnfiltered, vKey) {
if (!(vKey instanceof Array)) {
return arguments.callee.call(this, htUnfiltered, [ vKey ]);
}
var waKey = jindo.$A(vKey);
return jindo.$H(htUnfiltered).filter(function(vValue, sKey){
if (waKey.has(sKey) && vValue) {
return true;
} else {
return false;
}
}).$value();
},
isBlankNode : function(elNode){
var isBlankTextNode = this.isBlankTextNode;
var bEmptyContent = function(elNode){
if(!elNode) {
return true;
}
if(isBlankTextNode(elNode)){
return true;
}
if(elNode.tagName == "BR") {
return true;
}
if(elNode.innerHTML == "&nbsp;" || elNode.innerHTML == "") {
return true;
}
return false;
};
var bEmptyP = function(elNode){
if(elNode.tagName == "IMG" || elNode.tagName == "IFRAME"){
return false;
}
if(bEmptyContent(elNode)){
return true;
}
if(elNode.tagName == "P"){
for(var i=elNode.childNodes.length-1; i>=0; i--){
var elTmp = elNode.childNodes[i];
if(isBlankTextNode(elTmp)){
elTmp.parentNode.removeChild(elTmp);
}
}
if(elNode.childNodes.length == 1){
if(elNode.firstChild.tagName == "IMG" || elNode.firstChild.tagName == "IFRAME"){
return false;
}
if(bEmptyContent(elNode.firstChild)){
return true;
}
}
}
return false;
};
if(bEmptyP(elNode)){
return true;
}
for(var i=0, nLen=elNode.childNodes.length; i<nLen; i++){
var elTmp = elNode.childNodes[i];
if(!bEmptyP(elTmp)){
return false;
}
}
return true;
},
isBlankTextNode : function(oNode){
var sText;
if(oNode.nodeType == 3){
sText = oNode.nodeValue;
sText = sText.replace(unescape("%uFEFF"), '');
if(sText == "") {
return true;
}
}
return false;
},
isFirstChildOfNode : function(sTagName, sParentTagName, elNode){
if(!elNode){
return false;
}
if(elNode.tagName == sParentTagName && elNode.firstChild.tagName == sTagName){
return true;
}
return false;
},
findAncestorByTagName : function(sTagName, elNode){
while(elNode && elNode.tagName != sTagName) {
elNode = elNode.parentNode;
}
return elNode;
},
findAncestorByTagNameWithCount : function(sTagName, elNode){
var nRecursiveCount = 0;
var htResult = {};
while(elNode && elNode.tagName != sTagName) {
elNode = elNode.parentNode;
nRecursiveCount += 1;
}
htResult = {
elNode : elNode,
nRecursiveCount : nRecursiveCount
}
return htResult;
},
findClosestAncestorAmongTagNames : function(aTagName, elNode){
var rxTagNames = new RegExp("^(" + aTagName.join("|") + ")$", "i");
while(elNode && !rxTagNames.test(elNode.tagName)){
elNode = elNode.parentNode;
}
return elNode;
},
findClosestAncestorAmongTagNamesWithCount : function(aTagName, elNode){
var nRecursiveCount = 0;
var htResult = {};
var rxTagNames = new RegExp("^(" + aTagName.join("|") + ")$", "i");
while(elNode && !rxTagNames.test(elNode.tagName)){
elNode = elNode.parentNode;
nRecursiveCount += 1;
}
htResult = {
elNode : elNode,
nRecursiveCount : nRecursiveCount
}
return htResult;
},
loadCSS : function(url, fnCallback){
var oDoc = document;
var elHead = oDoc.getElementsByTagName("HEAD")[0];
var elStyle = oDoc.createElement ("LINK");
elStyle.setAttribute("type", "text/css");
elStyle.setAttribute("rel", "stylesheet");
elStyle.setAttribute("href", url);
if(fnCallback){
if ('onload' in elStyle) {
elStyle.onload = function(){
fnCallback();
};
} else {
elStyle.onreadystatechange = function(){
if(elStyle.readyState != "complete"){
return;
}
if(elStyle.getAttribute("_complete")){
return;
}
elStyle.setAttribute("_complete", true);
fnCallback();
};
}
}
elHead.appendChild (elStyle);
},
getUniqueId : function(sPrefix) {
return (sPrefix || '') + jindo.$Date().time() + (Math.random() * 100000).toFixed();
},
clone : function(oSrc, oChange) {
if ('undefined' != typeof(oSrc) && !!oSrc && (oSrc.constructor == Array || oSrc.constructor == Object)) {
var oCopy = (oSrc.constructor == Array ? [] : {} );
for (var property in oSrc) {
if ('undefined' != typeof(oChange) && !!oChange[property]) {
oCopy[property] = arguments.callee(oChange[property]);
} else {
oCopy[property] = arguments.callee(oSrc[property]);
}
}
return oCopy;
}
return oSrc;
},
getHtmlTagAttr : function(sHtmlTag, sAttr) {
var rx = new RegExp('\\s' + sAttr + "=('([^']*)'|\"([^\"]*)\"|([^\"' >]*))", 'i');
var aResult = rx.exec(sHtmlTag);
if (!aResult) {
return '';
}
var sAttrTmp = (aResult[1] || aResult[2] || aResult[3]);
if (!!sAttrTmp) {
sAttrTmp = sAttrTmp.replace(/[\"]/g, '');
}
return sAttrTmp;
},
iframeAlignConverter : function(el, oDoc){
var sTagName = el.tagName.toUpperCase();
if(sTagName == "DIV" || sTagName == 'P'){
if(el.parentNode === null ){
return;
}
var elWYSIWYGDoc = oDoc;
var wel = jindo.$Element(el);
var sHtml = wel.html();
var sAlign = jindo.$Element(el).attr('align') || jindo.$Element(el).css('text-align');
var welAfter = jindo.$Element(jindo.$('<div></div>', elWYSIWYGDoc));
welAfter.html(sHtml).attr('align', sAlign);
wel.replace(welAfter);
}
},
getJsonDatafromXML : function(sXML) {
var o  = {};
var re = /\s*<(\/?[\w:\-]+)((?:\s+[\w:\-]+\s*=\s*(?:"(?:\\"|[^"])*"|'(?:\\'|[^'])*'))*)\s*((?:\/>)|(?:><\/\1>|\s*))|\s*<!\[CDATA\[([\w\W]*?)\]\]>\s*|\s*>?([^<]*)/ig;
var re2= /^[0-9]+(?:\.[0-9]+)?$/;
var re3= /^\s+$/g;
var ec = {"&amp;":"&","&nbsp;":" ","&quot;":"\"","&lt;":"<","&gt;":">"};
var fg = {tags:["/"],stack:[o]};
var es = function(s){
if (typeof s == "undefined") {
return "";
}
return s.replace(/&[a-z]+;/g, function(m){ return (typeof ec[m] == "string")?ec[m]:m; });
};
var at = function(s,c) {
s.replace(/([\w\:\-]+)\s*=\s*(?:"((?:\\"|[^"])*)"|'((?:\\'|[^'])*)')/g, function($0,$1,$2,$3) {
c[$1] = es(($2?$2.replace(/\\"/g,'"'):undefined)||($3?$3.replace(/\\'/g,"'"):undefined));
});
};
var em = function(o) {
for(var x in o){
if (o.hasOwnProperty(x)) {
if(Object.prototype[x]) {
continue;
}
return false;
}
}
return true;
};
var cb = function($0,$1,$2,$3,$4,$5) {
var cur, cdata = "";
var idx = fg.stack.length - 1;
if (typeof $1 == "string" && $1) {
if ($1.substr(0,1) != "/") {
var has_attr = (typeof $2 == "string" && $2);
var closed   = (typeof $3 == "string" && $3);
var newobj   = (!has_attr && closed)?"":{};
cur = fg.stack[idx];
if (typeof cur[$1] == "undefined") {
cur[$1] = newobj;
cur = fg.stack[idx+1] = cur[$1];
} else if (cur[$1] instanceof Array) {
var len = cur[$1].length;
cur[$1][len] = newobj;
cur = fg.stack[idx+1] = cur[$1][len];
} else {
cur[$1] = [cur[$1], newobj];
cur = fg.stack[idx+1] = cur[$1][1];
}
if (has_attr) {
at($2,cur);
}
fg.tags[idx+1] = $1;
if (closed) {
fg.tags.length--;
fg.stack.length--;
}
} else {
fg.tags.length--;
fg.stack.length--;
}
} else if (typeof $4 == "string" && $4) {
cdata = $4;
} else if (typeof $5 == "string" && $5.replace(re3, "")) {
cdata = es($5);
}
if (cdata.length > 0) {
var par = fg.stack[idx-1];
var tag = fg.tags[idx];
if (re2.test(cdata)) {
}else if (cdata == "true" || cdata == "false"){
cdata = new Boolean(cdata);
}
if(typeof par =='undefined') {
return;
}
if (par[tag] instanceof Array) {
var o = par[tag];
if (typeof o[o.length-1] == "object" && !em(o[o.length-1])) {
o[o.length-1].$cdata = cdata;
o[o.length-1].toString = function(){ return cdata; };
} else {
o[o.length-1] = cdata;
}
} else {
if (typeof par[tag] == "object" && !em(par[tag])) {
par[tag].$cdata = cdata;
par[tag].toString = function() { return cdata; };
} else {
par[tag] = cdata;
}
}
}
};
sXML = sXML.replace(/<(\?|\!-)[^>]*>/g, "");
sXML.replace(re, cb);
return jindo.$Json(o);
},
replaceSpecialChar : function(sString){
return (typeof(sString) == "string") ? (sString.replace(/\&/g, "&amp;").replace(/\"/g, "&quot;").replace(/\'/g, "&#39;").replace(/</g, "&lt;").replace(/\>/g, "&gt;")) : "";
},
restoreSpecialChar : function(sString){
return (typeof(sString) == "string") ? (sString.replace(/&quot;/g, "\"").replace(/&#39;/g, "'").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&")) : "";
}
};
nhn.husky.AutoResizer = jindo.$Class({
welHiddenDiv : null,
welCloneDiv : null,
elContainer : null,
$init : function(el, htOption){
var aCopyStyle = [
'lineHeight', 'textDecoration', 'letterSpacing',
'fontSize', 'fontFamily', 'fontStyle', 'fontWeight',
'textTransform', 'textAlign', 'direction', 'wordSpacing', 'fontSizeAdjust',
'paddingTop', 'paddingLeft', 'paddingBottom', 'paddingRight', 'width'
],
i = aCopyStyle.length,
oCss = {
"position" : "absolute",
"top" : -9999,
"left" : -9999,
"opacity": 0,
"overflow": "hidden",
"wordWrap" : "break-word"
};
this.nMinHeight = htOption.nMinHeight;
this.wfnCallback = htOption.wfnCallback;
this.elContainer = el.parentNode;
this.welTextArea = jindo.$Element(el);
this.welHiddenDiv = jindo.$Element('<div>');
this.wfnResize = jindo.$Fn(this._resize, this);
this.sOverflow = this.welTextArea.css("overflow");
this.welTextArea.css("overflow", "hidden");
while(i--){
oCss[aCopyStyle[i]] = this.welTextArea.css(aCopyStyle[i]);
}
this.welHiddenDiv.css(oCss);
this.nLastHeight = this.welTextArea.height();
},
bind : function(){
this.welCloneDiv = jindo.$Element(this.welHiddenDiv.$value().cloneNode(false));
this.wfnResize.attach(this.welTextArea, "keyup");
this.welCloneDiv.appendTo(this.elContainer);
this._resize();
},
unbind : function(){
this.wfnResize.detach(this.welTextArea, "keyup");
this.welTextArea.css("overflow", this.sOverflow);
if(this.welCloneDiv){
this.welCloneDiv.leave();
}
},
_resize : function(){
var sContents = this.welTextArea.$value().value,
bExpand = false,
nHeight;
if(sContents === this.sContents){
return;
}
this.sContents = sContents.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;').replace(/\n/g, '<br>');
this.sContents += "<br>";
this.welCloneDiv.html(this.sContents);
nHeight = this.welCloneDiv.height();
if(nHeight < this.nMinHeight){
nHeight = this.nMinHeight;
}
this.welTextArea.css("height", nHeight + "px");
this.elContainer.style.height = nHeight + "px";
if(this.nLastHeight < nHeight){
bExpand = true;
}
this.wfnCallback(bExpand);
}
});
if ('undefined' != typeof(StringBuffer)) {
StringBuffer = {};
}
StringBuffer = function(str) {
this._aString = [];
if ('undefined' != typeof(str)) {
this.append(str);
}
};
StringBuffer.prototype.append = function(str) {
this._aString.push(str);
return this;
};
StringBuffer.prototype.toString = function() {
return this._aString.join('');
};
StringBuffer.prototype.setLength = function(nLen) {
if('undefined' == typeof(nLen) || 0 >= nLen) {
this._aString.length = 0;
} else {
this._aString.length = nLen;
}
};
(function() {
var oDummy = null, rx = /,/gi;
IsInstalledFont = function(sFont) {
var sDefFont = sFont == 'Comic Sans MS' ? 'Courier New' : 'Comic Sans MS';
if (!oDummy) {
oDummy = document.createElement('div');
}
var sStyle = 'position:absolute !important; font-size:200px !important; left:-9999px !important; top:-9999px !important;';
oDummy.innerHTML = 'mmmmiiiii'+unescape('%uD55C%uAE00');
oDummy.style.cssText = sStyle + 'font-family:"' + sDefFont + '" !important';
var elBody = document.body || document.documentElement;
if(elBody.firstChild){
elBody.insertBefore(oDummy, elBody.firstChild);
}else{
document.body.appendChild(oDummy);
}
var sOrg = oDummy.offsetWidth + '-' + oDummy.offsetHeight;
oDummy.style.cssText = sStyle + 'font-family:"' + sFont.replace(rx, '","') + '", "' + sDefFont + '" !important';
var bInstalled = sOrg != (oDummy.offsetWidth + '-' + oDummy.offsetHeight);
document.body.removeChild(oDummy);
return bInstalled;
};
})();
nhn.husky.SE2B_CSSLoader = jindo.$Class({
name : "SE2B_CSSLoader",
bCssLoaded : false,
aInstantLoadTrigger : ["OPEN_QE_LAYER", "SHOW_ACTIVE_LAYER", "SHOW_DIALOG_LAYER", "START_SPELLCHECK"],
aDelayedLoadTrigger : ["MSG_SE_OBJECT_EDIT_REQUESTED", "OBJECT_MODIFY", "MSG_SE_DUMMY_OBJECT_EDIT_REQUESTED", "TOGGLE_TOOLBAR_ACTIVE_LAYER", "SHOW_TOOLBAR_ACTIVE_LAYER"],
$init : function(){
this.htOptions = nhn.husky.SE2M_Configuration.SE2B_CSSLoader;
if(!jindo.$Agent().navigator().ie){
this.loadSE2CSS();
}else{
for(var i=0, nLen = this.aInstantLoadTrigger.length; i<nLen; i++){
this["$BEFORE_"+this.aInstantLoadTrigger[i]] = jindo.$Fn(function(){
this.loadSE2CSS();
}, this).bind();
}
for(var i=0, nLen = this.aDelayedLoadTrigger.length; i<nLen; i++){
var sMsg = this.aDelayedLoadTrigger[i];
this["$BEFORE_"+this.aDelayedLoadTrigger[i]] = jindo.$Fn(function(sMsg){
var aArgs = jindo.$A(arguments).$value();
aArgs = aArgs.splice(1, aArgs.length-1);
return this.loadSE2CSS(sMsg, aArgs);
}, this).bind(sMsg);
}
}
},
loadSE2CSS : function(sMsg, oArgs){
if(this.bCssLoaded){return true;}
this.bCssLoaded = true;
var fnCallback = null;
if(sMsg){
fnCallback = jindo.$Fn(this.oApp.exec, this.oApp).bind(sMsg, oArgs);
}
nhn.husky.SE2M_Utils.loadCSS(this.htOptions.sCSSBaseURI+"/smart_editor2_items.css", fnCallback);
return false;
}
});
if(typeof window.nhn=='undefined'){window.nhn = {};}
var oMessageMap = {
'SE_EditingAreaManager.onExit' : '내용이 변경되었습니다.',
'SE_Color.invalidColorCode' : '색상 코드를 올바르게 입력해 주세요. \n\n 예) #000000, #FF0000, #FFFFFF, #ffffff, ffffff',
'SE_Hyperlink.invalidURL' : '입력하신 URL이 올바르지 않습니다.',
'SE_FindReplace.keywordMissing' : '찾으실 단어를 입력해 주세요.',
'SE_FindReplace.keywordNotFound' : '찾으실 단어가 없습니다.',
'SE_FindReplace.replaceAllResultP1' : '일치하는 내용이 총 ',
'SE_FindReplace.replaceAllResultP2' : '건 바뀌었습니다.',
'SE_FindReplace.notSupportedBrowser' : '현재 사용하고 계신 브라우저에서는 사용하실수 없는 기능입니다.\n\n이용에 불편을 드려 죄송합니다.',
'SE_FindReplace.replaceKeywordNotFound' : '바뀔 단어가 없습니다',
'SE_LineHeight.invalidLineHeight' : '잘못된 값입니다.',
'SE_Footnote.defaultText' : '각주내용을 입력해 주세요',
'SE.failedToLoadFlash' : '플래시가 차단되어 있어 해당 기능을 사용할 수 없습니다.',
'SE2M_EditingModeChanger.confirmTextMode' : '텍스트 모드로 전환하면 작성된 내용은 유지되나, \n\n글꼴 등의 편집효과와 이미지 등의 첨부내용이 모두 사라지게 됩니다.\n\n전환하시겠습니까?',
'SE2M_FontNameWithLayerUI.sSampleText' : '가나다라'
};
nhn.husky.SE_OuterIFrameControl = $Class({
name : "SE_OuterIFrameControl",
oResizeGrip : null,
$init : function(oAppContainer){
this.aHeightChangeKeyMap = [-100, 100, 500, -500, -1, -10, 1, 10];
this._assignHTMLObjects(oAppContainer);
this.$FnKeyDown = $Fn(this._keydown, this);
if(this.oResizeGrip){
this.$FnKeyDown.attach(this.oResizeGrip, "keydown");
}
if(!!jindo.$Agent().navigator().ie){
this.$FnMouseDown = $Fn(this._mousedown, this);
this.$FnMouseMove = $Fn(this._mousemove, this);
this.$FnMouseMove_Parent = $Fn(this._mousemove_parent, this);
this.$FnMouseUp = $Fn(this._mouseup, this);
if(this.oResizeGrip){
this.$FnMouseDown.attach(this.oResizeGrip, "mousedown");
}
}
},
_assignHTMLObjects : function(oAppContainer){
oAppContainer = jindo.$(oAppContainer) || document;
this.oResizeGrip = cssquery.getSingle(".husky_seditor_editingArea_verticalResizer", oAppContainer);
this.elIFrame = window.frameElement;
this.welIFrame = $Element(this.elIFrame);
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("SE_FIT_IFRAME", []);
},
$ON_MSG_EDITING_AREA_SIZE_CHANGED : function(){
this.oApp.exec("SE_FIT_IFRAME", []);
},
$ON_SE_FIT_IFRAME : function(){
this.elIFrame.style.height = document.body.offsetHeight+"px";
},
$AFTER_RESIZE_EDITING_AREA_BY : function(ipWidthChange, ipHeightChange){
this.oApp.exec("SE_FIT_IFRAME", []);
},
_keydown : function(oEvent){
var oKeyInfo = oEvent.key();
if(oKeyInfo.keyCode >= 33 && oKeyInfo.keyCode <= 40){
this.oApp.exec("MSG_EDITING_AREA_RESIZE_STARTED", []);
this.oApp.exec("RESIZE_EDITING_AREA_BY", [0, this.aHeightChangeKeyMap[oKeyInfo.keyCode-33]]);
this.oApp.exec("MSG_EDITING_AREA_RESIZE_ENDED", []);
oEvent.stop();
}
},
_mousedown : function(oEvent){
this.iStartHeight = oEvent.pos().clientY;
this.iStartHeightOffset = oEvent.pos().layerY;
this.$FnMouseMove.attach(document, "mousemove");
this.$FnMouseMove_Parent.attach(parent.document, "mousemove");
this.$FnMouseUp.attach(document, "mouseup");
this.$FnMouseUp.attach(parent.document, "mouseup");
this.iStartHeight = oEvent.pos().clientY;
this.oApp.exec("MSG_EDITING_AREA_RESIZE_STARTED", [this.$FnMouseDown, this.$FnMouseMove, this.$FnMouseUp]);
},
_mousemove : function(oEvent){
var iHeightChange = oEvent.pos().clientY - this.iStartHeight;
this.oApp.exec("RESIZE_EDITING_AREA_BY", [0, iHeightChange]);
},
_mousemove_parent : function(oEvent){
var iHeightChange = oEvent.pos().pageY - (this.welIFrame.offset().top + this.iStartHeight);
this.oApp.exec("RESIZE_EDITING_AREA_BY", [0, iHeightChange]);
},
_mouseup : function(oEvent){
this.$FnMouseMove.detach(document, "mousemove");
this.$FnMouseMove_Parent.detach(parent.document, "mousemove");
this.$FnMouseUp.detach(document, "mouseup");
this.$FnMouseUp.detach(parent.document, "mouseup");
this.oApp.exec("MSG_EDITING_AREA_RESIZE_ENDED", [this.$FnMouseDown, this.$FnMouseMove, this.$FnMouseUp]);
}
});
nhn.husky.SE_ToolbarToggler = $Class({
name : "SE_ToolbarToggler",
bUseToolbar : true,
$init : function(oAppContainer, bUseToolbar){
this._assignHTMLObjects(oAppContainer, bUseToolbar);
},
_assignHTMLObjects : function(oAppContainer, bUseToolbar){
oAppContainer = jindo.$(oAppContainer) || document;
this.toolbarArea = cssquery.getSingle(".se2_tool", oAppContainer);
if( typeof(bUseToolbar) == 'undefined' || bUseToolbar === true){
this.toolbarArea.style.display = "block";
}else{
this.toolbarArea.style.display = "none";
}
},
$ON_MSG_APP_READY : function(){
this.oApp.exec("REGISTER_HOTKEY", ["ctrl+t", "SE_TOGGLE_TOOLBAR", []]);
},
$ON_SE_TOGGLE_TOOLBAR : function(){
this.toolbarArea.style.display = (this.toolbarArea.style.display == "none")?"block":"none";
this.oApp.exec("MSG_EDITING_AREA_SIZE_CHANGED", []);
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_FindReplacePlugin$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_FindReplacePlugin, {
_assignHTMLElements : function(){
var oAppContainer = this.oApp.htOptions.elAppContainer;
this.oApp.exec("LOAD_HTML", ["find_and_replace"]);
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_se2m_findAndReplace_layer", oAppContainer);
this.welDropdownLayer = jindo.$Element(this.elDropdownLayer);
var oTmp = jindo.$$("LI", this.elDropdownLayer);
this.oFindTab = oTmp[0];
this.oReplaceTab = oTmp[1];
oTmp = jindo.$$(".container > .bx", this.elDropdownLayer);
this.oFindInputSet = jindo.$$.getSingle(".husky_se2m_find_ui", this.elDropdownLayer);
this.oReplaceInputSet = jindo.$$.getSingle(".husky_se2m_replace_ui", this.elDropdownLayer);
this.elTitle = jindo.$$.getSingle("H3", this.elDropdownLayer);
this.oFindInput_Keyword = jindo.$$.getSingle("INPUT", this.oFindInputSet);
oTmp = jindo.$$("INPUT", this.oReplaceInputSet);
this.oReplaceInput_Original = oTmp[0];
this.oReplaceInput_Replacement = oTmp[1];
this.oFindNextButton = jindo.$$.getSingle("BUTTON.husky_se2m_find_next", this.elDropdownLayer);
this.oReplaceFindNextButton = jindo.$$.getSingle("BUTTON.husky_se2m_replace_find_next", this.elDropdownLayer);
this.oReplaceButton = jindo.$$.getSingle("BUTTON.husky_se2m_replace", this.elDropdownLayer);
this.oReplaceAllButton = jindo.$$.getSingle("BUTTON.husky_se2m_replace_all", this.elDropdownLayer);
this.aCloseButtons = jindo.$$("BUTTON.husky_se2m_cancel", this.elDropdownLayer);
},
$LOCAL_BEFORE_FIRST : function(sMsg){
this._assignHTMLElements();
this.oFindReplace = new nhn.FindReplace(this.oEditingWindow);
for(var i=0; i<this.aCloseButtons.length; i++){
var func = jindo.$Fn(this.oApp.exec, this.oApp).bind("HIDE_FIND_REPLACE_LAYER", [this.elDropdownLayer]);
jindo.$Fn(func, this).attach(this.aCloseButtons[i], "click");
}
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("SHOW_FIND", []), this).attach(this.oFindTab, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("SHOW_REPLACE", []), this).attach(this.oReplaceTab, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("FIND", []), this).attach(this.oFindNextButton, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("FIND", []), this).attach(this.oReplaceFindNextButton, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("REPLACE", []), this).attach(this.oReplaceButton, "click");
jindo.$Fn(jindo.$Fn(this.oApp.exec, this.oApp).bind("REPLACE_ALL", []), this).attach(this.oReplaceAllButton, "click");
this.oFindInput_Keyword.value = "";
this.oReplaceInput_Original.value = "";
this.oReplaceInput_Replacement.value = "";
var elIframe = this.oApp.getWYSIWYGWindow().frameElement;
this.htOffsetPos = jindo.$Element(elIframe).offset();
this.nEditorWidth = elIframe.offsetWidth;
this.elDropdownLayer.style.display = "block";
this.htInitialPos = this.welDropdownLayer.offset();
var htScrollXY = this.oApp.oUtils.getScrollXY();
this.welDropdownLayer.offset(this.htOffsetPos.top, this.htOffsetPos.left);
this.htTopLeftCorner = {x:parseInt(this.elDropdownLayer.style.left, 10), y:parseInt(this.elDropdownLayer.style.top, 10)};
this.nLayerWidth = 258;
this.nLayerHeight = 160;
this.elDropdownLayer.style.display = "none";
},
$ON_TOGGLE_FIND_REPLACE_LAYER : function(){
if(!this.bLayerShown) {
this.oApp.exec("SHOW_FIND_REPLACE_LAYER");
} else {
this.oApp.exec("HIDE_FIND_REPLACE_LAYER");
}
},
$ON_SHOW_FIND_REPLACE_LAYER : function(){
this.bLayerShown = true;
this.oApp.exec("DISABLE_ALL_UI", [{aExceptions: ["findAndReplace"]}]);
this.oApp.exec("SELECT_UI", ["findAndReplace"]);
this.oApp.exec("HIDE_ALL_DIALOG_LAYER", []);
this.elDropdownLayer.style.top = this.nDefaultTop+"px";
this.oApp.exec("SHOW_DIALOG_LAYER", [this.elDropdownLayer, {
elHandle: this.elTitle,
fnOnDragStart : jindo.$Fn(this.oApp.exec, this.oApp).bind("SHOW_EDITING_AREA_COVER"),
fnOnDragEnd : jindo.$Fn(this.oApp.exec, this.oApp).bind("HIDE_EDITING_AREA_COVER"),
nMinX : this.htTopLeftCorner.x,
nMinY : this.nDefaultTop,
nMaxX : this.htTopLeftCorner.x + this.oApp.getEditingAreaWidth() - this.nLayerWidth,
nMaxY : this.htTopLeftCorner.y + this.oApp.getEditingAreaHeight() - this.nLayerHeight,
sOnShowMsg : "FIND_REPLACE_LAYER_SHOWN"
}]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['findreplace']);
},
$ON_HIDE_FIND_REPLACE_LAYER : function() {
this.oApp.exec("ENABLE_ALL_UI");
this.oApp.exec("DESELECT_UI", ["findAndReplace"]);
this.oApp.exec("HIDE_ALL_DIALOG_LAYER", []);
this.bLayerShown = false;
},
$ON_FIND_REPLACE_LAYER_SHOWN : function(){
this.oApp.exec("POSITION_TOOLBAR_LAYER", [this.elDropdownLayer]);
if(this.bFindMode){
this.oFindInput_Keyword.value = "_clear_";
this.oFindInput_Keyword.value = "";
this.oFindInput_Keyword.focus();
}else{
this.oReplaceInput_Original.value = "_clear_";
this.oReplaceInput_Original.value = "";
this.oReplaceInput_Replacement.value = "";
this.oReplaceInput_Original.focus();
}
this.oApp.exec("HIDE_CURRENT_ACTIVE_LAYER", []);
},
$ON_SHOW_FIND_LAYER : function(){
this.oApp.exec("SHOW_FIND");
this.oApp.exec("SHOW_FIND_REPLACE_LAYER");
},
$ON_SHOW_REPLACE_LAYER : function(){
this.oApp.exec("SHOW_REPLACE");
this.oApp.exec("SHOW_FIND_REPLACE_LAYER");
},
$ON_SHOW_FIND : function(){
this.bFindMode = true;
this.oFindInput_Keyword.value = this.oReplaceInput_Original.value;
jindo.$Element(this.oFindTab).addClass("active");
jindo.$Element(this.oReplaceTab).removeClass("active");
jindo.$Element(this.oFindNextButton).removeClass("normal");
jindo.$Element(this.oFindNextButton).addClass("strong");
this.oFindInputSet.style.display = "block";
this.oReplaceInputSet.style.display = "none";
this.oReplaceButton.style.display = "none";
this.oReplaceAllButton.style.display = "none";
jindo.$Element(this.elDropdownLayer).removeClass("replace");
jindo.$Element(this.elDropdownLayer).addClass("find");
},
$ON_SHOW_REPLACE : function(){
this.bFindMode = false;
this.oReplaceInput_Original.value = this.oFindInput_Keyword.value;
jindo.$Element(this.oFindTab).removeClass("active");
jindo.$Element(this.oReplaceTab).addClass("active");
jindo.$Element(this.oFindNextButton).removeClass("strong");
jindo.$Element(this.oFindNextButton).addClass("normal");
this.oFindInputSet.style.display = "none";
this.oReplaceInputSet.style.display = "block";
this.oReplaceButton.style.display = "inline";
this.oReplaceAllButton.style.display = "inline";
jindo.$Element(this.elDropdownLayer).removeClass("find");
jindo.$Element(this.elDropdownLayer).addClass("replace");
},
$ON_FIND : function(){
var sKeyword;
if(this.bFindMode){
sKeyword = this.oFindInput_Keyword.value;
}else{
sKeyword = this.oReplaceInput_Original.value;
}
var oSelection = this.oApp.getSelection();
oSelection.select();
switch(this.oFindReplace.find(sKeyword, false)){
case 1:
alert(this.oApp.$MSG("SE_FindReplace.keywordNotFound"));
oSelection.select();
break;
case 2:
alert(this.oApp.$MSG("SE_FindReplace.keywordMissing"));
break;
}
},
$ON_REPLACE : function(){
var sOriginal = this.oReplaceInput_Original.value;
var sReplacement = this.oReplaceInput_Replacement.value;
var oSelection = this.oApp.getSelection();
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["REPLACE"]);
var iReplaceResult = this.oFindReplace.replace(sOriginal, sReplacement, false);
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["REPLACE"]);
switch(iReplaceResult){
case 1:
case 3:
alert(this.oApp.$MSG("SE_FindReplace.keywordNotFound"));
oSelection.select();
break;
case 4:
alert(this.oApp.$MSG("SE_FindReplace.keywordMissing"));
break;
}
},
$ON_REPLACE_ALL : function(){
var sOriginal = this.oReplaceInput_Original.value;
var sReplacement = this.oReplaceInput_Replacement.value;
var oSelection = this.oApp.getSelection();
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["REPLACE ALL", {sSaveTarget:"BODY"}]);
var iReplaceAllResult = this.oFindReplace.replaceAll(sOriginal, sReplacement, false);
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["REPLACE ALL", {sSaveTarget:"BODY"}]);
if(iReplaceAllResult === 0){
alert(this.oApp.$MSG("SE_FindReplace.replaceKeywordNotFound"));
oSelection.select();
this.oApp.exec("FOCUS");
}else{
if(iReplaceAllResult<0){
alert(this.oApp.$MSG("SE_FindReplace.keywordMissing"));
oSelection.select();
}else{
alert(this.oApp.$MSG("SE_FindReplace.replaceAllResultP1")+iReplaceAllResult+this.oApp.$MSG("SE_FindReplace.replaceAllResultP2"));
oSelection = this.oApp.getEmptySelection();
oSelection.select();
this.oApp.exec("FOCUS");
}
}
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_Quote$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_Quote, {
$ON_TOGGLE_BLOCKQUOTE_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.elDropdownLayer, null, "SELECT_UI", ["quote"], "DESELECT_UI", ["quote"]]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['quote']);
},
$ON_EVENT_SE2_BLOCKQUOTE_LAYER_CLICK : function(weEvent){
var elButton = nhn.husky.SE2M_Utils.findAncestorByTagName("BUTTON", weEvent.element);
if(!elButton || elButton.tagName != "BUTTON"){return;}
var sClass = elButton.className;
this.oApp.exec("APPLY_BLOCKQUOTE", [sClass]);
},
$ON_APPLY_BLOCKQUOTE : function(sClass){
if(sClass.match(/(se2_quote[0-9]+)/)){
this._wrapBlock("BLOCKQUOTE", RegExp.$1);
}else{
this._unwrapBlock("BLOCKQUOTE");
}
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
},
_isExceedMaxDepth : function(elNode){
var countChildQuote = function(elNode){
var elChild = elNode.firstChild;
var nCount = 0;
var nMaxCount = 0;
if(!elChild){
if(elNode.tagName && elNode.tagName === "BLOCKQUOTE"){
return 1;
}else{
return 0;
}
}
while(elChild){
if(elChild.nodeType === 1){
nCount = countChildQuote(elChild);
if(elChild.tagName === "BLOCKQUOTE"){
nCount += 1;
}
if(nMaxCount < nCount){
nMaxCount = nCount;
}
if(nMaxCount >= this.nMaxLevel){
return nMaxCount;
}
}
elChild = elChild.nextSibling;
}
return nMaxCount;
};
return (countChildQuote(elNode) >= this.nMaxLevel);
},
_unwrapBlock : function(tag){
var oSelection = this.oApp.getSelection();
var elCommonAncestor = oSelection.commonAncestorContainer;
while(elCommonAncestor && elCommonAncestor.tagName != tag){elCommonAncestor = elCommonAncestor.parentNode;}
if(!elCommonAncestor){return;}
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["CANCEL BLOCK QUOTE", {sSaveTarget:"BODY"}]);
var oLastTextNode = oSelection.commonAncestorContainer;
if(oLastTextNode.nodeType !== 3){
var aTextNodesInRange = oSelection.getTextNodes() || "",
nLastIndex = aTextNodesInRange.length - 1;
oLastTextNode = (nLastIndex > -1) ? aTextNodesInRange[nLastIndex] : null;
}
while(elCommonAncestor.firstChild){elCommonAncestor.parentNode.insertBefore(elCommonAncestor.firstChild, elCommonAncestor);}
elCommonAncestor.parentNode.removeChild(elCommonAncestor);
if(oLastTextNode){
oSelection.selectNodeContents(oLastTextNode);
oSelection.collapseToEnd();
oSelection.select();
}
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["CANCEL BLOCK QUOTE", {sSaveTarget:"BODY"}]);
},
_wrapBlock : function(tag, className){
var oSelection,
oLineInfo,
oStart, oEnd,
rxDontUseAsWhole = /BODY|TD|LI/i,
oStartNode, oEndNode, oNode,
elCommonAncestor,
elCommonNode,
elParentQuote,
elInsertBefore,
oFormattingNode,
elNextNode,
elParentNode,
aQuoteChild,
aQuoteCloneChild,
i, nLen, oP,
sBookmarkID;
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["BLOCK QUOTE", {sSaveTarget:"BODY"}]);
oSelection = this.oApp.getSelection();
if(oSelection.startContainer === oSelection.endContainer &&
oSelection.startContainer.nodeType === 1 &&
oSelection.startContainer.tagName === "P"){
if(nhn.husky.SE2M_Utils.isBlankNode(oSelection.startContainer) ||
nhn.husky.SE2M_Utils.isFirstChildOfNode("IMG", oSelection.startContainer.tagName, oSelection.startContainer) ||
nhn.husky.SE2M_Utils.isFirstChildOfNode("IFRAME", oSelection.startContainer.tagName, oSelection.startContainer)){
oLineInfo = oSelection.getLineInfo(true);
}else{
oLineInfo = oSelection.getLineInfo(false);
}
}else{
oLineInfo = oSelection.getLineInfo(false);
}
oStart = oLineInfo.oStart;
oEnd = oLineInfo.oEnd;
if(oStart.bParentBreak && !rxDontUseAsWhole.test(oStart.oLineBreaker.tagName)){
oStartNode = oStart.oNode.parentNode;
}else{
oStartNode = oStart.oNode;
}
if(oEnd.bParentBreak && !rxDontUseAsWhole.test(oEnd.oLineBreaker.tagName)){
oEndNode = oEnd.oNode.parentNode;
}else{
oEndNode = oEnd.oNode;
}
oSelection.setStartBefore(oStartNode);
oSelection.setEndAfter(oEndNode);
oNode = this._expandToTableStart(oSelection, oEndNode);
if(oNode){
oEndNode = oNode;
oSelection.setEndAfter(oNode);
}
oNode = this._expandToTableStart(oSelection, oStartNode);
if(oNode){
oStartNode = oNode;
oSelection.setStartBefore(oNode);
}
oNode = oStartNode;
oSelection.fixCommonAncestorContainer();
elCommonAncestor = oSelection.commonAncestorContainer;
if(oSelection.startContainer == oSelection.endContainer && oSelection.endOffset-oSelection.startOffset == 1){
elCommonNode = oSelection.startContainer.childNodes[oSelection.startOffset];
}else{
elCommonNode = oSelection.commonAncestorContainer;
}
elParentQuote = this._findParentQuote(elCommonNode);
if(elParentQuote){
elParentQuote.className = className;
this._setStyle(elParentQuote, this.htQuoteStyles_view[className]);
return;
}
while(!elCommonAncestor.tagName || (elCommonAncestor.tagName && elCommonAncestor.tagName.match(/UL|OL|LI|IMG|IFRAME/))){
elCommonAncestor = elCommonAncestor.parentNode;
}
while(oNode && oNode != elCommonAncestor && oNode.parentNode != elCommonAncestor){oNode = oNode.parentNode;}
if(oNode == elCommonAncestor){
elInsertBefore = elCommonAncestor.firstChild;
}else{
elInsertBefore = oNode;
}
oFormattingNode = oSelection._document.createElement(tag);
if(className){
oFormattingNode.className = className;
this._setStyle(oFormattingNode, this.htQuoteStyles_view[className]);
}
elCommonAncestor.insertBefore(oFormattingNode, elInsertBefore);
oSelection.setStartAfter(oFormattingNode);
oSelection.setEndAfter(oEndNode);
oSelection.surroundContents(oFormattingNode);
if(this._isExceedMaxDepth(oFormattingNode)){
alert(this.oApp.$MSG("SE2M_Quote.exceedMaxCount").replace("#MaxCount#", (this.nMaxLevel + 1)));
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
elNextNode = oFormattingNode.nextSibling;
elParentNode = oFormattingNode.parentNode;
aQuoteChild = oFormattingNode.childNodes;
aQuoteCloneChild = [];
jindo.$Element(oFormattingNode).leave();
for(i = 0, nLen = aQuoteChild.length; i < nLen; i++){
aQuoteCloneChild[i] = aQuoteChild[i];
}
for(i = 0, nLen = aQuoteCloneChild.length; i < nLen; i++){
if(!!elNextNode){
jindo.$Element(elNextNode).before(aQuoteCloneChild[i]);
}else{
jindo.$Element(elParentNode).append(aQuoteCloneChild[i]);
}
}
return;
}
oSelection.selectNodeContents(oFormattingNode);
if(oFormattingNode && oFormattingNode.parentNode && oFormattingNode.parentNode.tagName == "BODY" && !oFormattingNode.nextSibling){
oP = oSelection._document.createElement("P");
oP.innerHTML = "&nbsp;";
oFormattingNode.parentNode.insertBefore(oP, oFormattingNode.nextSibling);
}
if(nhn.husky.SE2M_Utils.isBlankNode(oFormattingNode)){
oFormattingNode.innerHTML = "&nbsp;";
oSelection.selectNodeContents(oFormattingNode.firstChild);
oSelection.collapseToStart();
oSelection.select();
}
this.oApp.exec("REFRESH_WYSIWYG");
setTimeout(jindo.$Fn(function(oSelection){
sBookmarkID = oSelection.placeStringBookmark();
oSelection.select();
oSelection.removeStringBookmark(sBookmarkID);
this.oApp.exec("FOCUS");
},this).bind(oSelection), 0);
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["BLOCK QUOTE", {sSaveTarget:"BODY"}]);
return oFormattingNode;
},
_expandToTableStart : function(oSelection, oNode){
var elCommonAncestor = oSelection.commonAncestorContainer;
var oResultNode = null;
var bLastIteration = false;
while(oNode && !bLastIteration){
if(oNode == elCommonAncestor){bLastIteration = true;}
if(/TBODY|TFOOT|THEAD|TR/i.test(oNode.tagName)){
oResultNode = this._getTableRoot(oNode);
break;
}
oNode = oNode.parentNode;
}
return oResultNode;
},
_getTableRoot : function(oNode){
while(oNode && oNode.tagName != "TABLE"){oNode = oNode.parentNode;}
return oNode;
},
_setStyle : function(el, sStyle) {
el.setAttribute("style", sStyle);
el.style.cssText = sStyle;
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_SCharacter$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_SCharacter, {
_assignHTMLObjects : function(oAppContainer){
oAppContainer = jindo.$(oAppContainer) || document;
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_seditor_sCharacter_layer", oAppContainer);
this.oTextField = jindo.$$.getSingle("INPUT", this.elDropdownLayer);
this.oInsertButton =  jindo.$$.getSingle("BUTTON.se2_confirm", this.elDropdownLayer);
this.aCloseButton = jindo.$$("BUTTON.husky_se2m_sCharacter_close", this.elDropdownLayer);
this.aSCharList = jindo.$$("UL.husky_se2m_sCharacter_list", this.elDropdownLayer);
var oLabelUL = jindo.$$.getSingle("UL.se2_char_tab", this.elDropdownLayer);
this.aLabel = jindo.$$(">LI", oLabelUL);
},
$LOCAL_BEFORE_FIRST : function(sFullMsg){
this.bIE = jindo.$Agent().navigator().ie;
this._assignHTMLObjects(this.oApp.htOptions.elAppContainer);
this.charSet = [];
this.charSet[0] = unescape('FF5B FF5D 3014 3015 3008 3009 300A 300B 300C 300D 300E 300F 3010 3011 2018 2019 201C 201D 3001 3002 %B7 2025 2026 %A7 203B 2606 2605 25CB 25CF 25CE 25C7 25C6 25A1 25A0 25B3 25B2 25BD 25BC 25C1 25C0 25B7 25B6 2664 2660 2661 2665 2667 2663 2299 25C8 25A3 25D0 25D1 2592 25A4 25A5 25A8 25A7 25A6 25A9 %B1 %D7 %F7 2260 2264 2265 221E 2234 %B0 2032 2033 2220 22A5 2312 2202 2261 2252 226A 226B 221A 223D 221D 2235 222B 222C 2208 220B 2286 2287 2282 2283 222A 2229 2227 2228 FFE2 21D2 21D4 2200 2203 %B4 FF5E 02C7 02D8 02DD 02DA 02D9 %B8 02DB %A1 %BF 02D0 222E 2211 220F 266D 2669 266A 266C 327F 2192 2190 2191 2193 2194 2195 2197 2199 2196 2198 321C 2116 33C7 2122 33C2 33D8 2121 2668 260F 260E 261C 261E %B6 2020 2021 %AE %AA %BA 2642 2640').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
this.charSet[1] = unescape('%BD 2153 2154 %BC %BE 215B 215C 215D 215E %B9 %B2 %B3 2074 207F 2081 2082 2083 2084 2160 2161 2162 2163 2164 2165 2166 2167 2168 2169 2170 2171 2172 2173 2174 2175 2176 2177 2178 2179 FFE6 %24 FFE5 FFE1 20AC 2103 212B 2109 FFE0 %A4 2030 3395 3396 3397 2113 3398 33C4 33A3 33A4 33A5 33A6 3399 339A 339B 339C 339D 339E 339F 33A0 33A1 33A2 33CA 338D 338E 338F 33CF 3388 3389 33C8 33A7 33A8 33B0 33B1 33B2 33B3 33B4 33B5 33B6 33B7 33B8 33B9 3380 3381 3382 3383 3384 33BA 33BB 33BC 33BD 33BE 33BF 3390 3391 3392 3393 3394 2126 33C0 33C1 338A 338B 338C 33D6 33C5 33AD 33AE 33AF 33DB 33A9 33AA 33AB 33AC 33DD 33D0 33D3 33C3 33C9 33DC 33C6').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
this.charSet[2] = unescape('3260 3261 3262 3263 3264 3265 3266 3267 3268 3269 326A 326B 326C 326D 326E 326F 3270 3271 3272 3273 3274 3275 3276 3277 3278 3279 327A 327B 24D0 24D1 24D2 24D3 24D4 24D5 24D6 24D7 24D8 24D9 24DA 24DB 24DC 24DD 24DE 24DF 24E0 24E1 24E2 24E3 24E4 24E5 24E6 24E7 24E8 24E9 2460 2461 2462 2463 2464 2465 2466 2467 2468 2469 246A 246B 246C 246D 246E 3200 3201 3202 3203 3204 3205 3206 3207 3208 3209 320A 320B 320C 320D 320E 320F 3210 3211 3212 3213 3214 3215 3216 3217 3218 3219 321A 321B 249C 249D 249E 249F 24A0 24A1 24A2 24A3 24A4 24A5 24A6 24A7 24A8 24A9 24AA 24AB 24AC 24AD 24AE 24AF 24B0 24B1 24B2 24B3 24B4 24B5 2474 2475 2476 2477 2478 2479 247A 247B 247C 247D 247E 247F 2480 2481 2482').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
this.charSet[3] = unescape('3131 3132 3133 3134 3135 3136 3137 3138 3139 313A 313B 313C 313D 313E 313F 3140 3141 3142 3143 3144 3145 3146 3147 3148 3149 314A 314B 314C 314D 314E 314F 3150 3151 3152 3153 3154 3155 3156 3157 3158 3159 315A 315B 315C 315D 315E 315F 3160 3161 3162 3163 3165 3166 3167 3168 3169 316A 316B 316C 316D 316E 316F 3170 3171 3172 3173 3174 3175 3176 3177 3178 3179 317A 317B 317C 317D 317E 317F 3180 3181 3182 3183 3184 3185 3186 3187 3188 3189 318A 318B 318C 318D 318E').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
this.charSet[4] = unescape('0391 0392 0393 0394 0395 0396 0397 0398 0399 039A 039B 039C 039D 039E 039F 03A0 03A1 03A3 03A4 03A5 03A6 03A7 03A8 03A9 03B1 03B2 03B3 03B4 03B5 03B6 03B7 03B8 03B9 03BA 03BB 03BC 03BD 03BE 03BF 03C0 03C1 03C3 03C4 03C5 03C6 03C7 03C8 03C9 %C6 %D0 0126 0132 013F 0141 %D8 0152 %DE 0166 014A %E6 0111 %F0 0127 I 0133 0138 0140 0142 0142 0153 %DF %FE 0167 014B 0149 0411 0413 0414 0401 0416 0417 0418 0419 041B 041F 0426 0427 0428 0429 042A 042B 042C 042D 042E 042F 0431 0432 0433 0434 0451 0436 0437 0438 0439 043B 043F 0444 0446 0447 0448 0449 044A 044B 044C 044D 044E 044F').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
this.charSet[5] = unescape('3041 3042 3043 3044 3045 3046 3047 3048 3049 304A 304B 304C 304D 304E 304F 3050 3051 3052 3053 3054 3055 3056 3057 3058 3059 305A 305B 305C 305D 305E 305F 3060 3061 3062 3063 3064 3065 3066 3067 3068 3069 306A 306B 306C 306D 306E 306F 3070 3071 3072 3073 3074 3075 3076 3077 3078 3079 307A 307B 307C 307D 307E 307F 3080 3081 3082 3083 3084 3085 3086 3087 3088 3089 308A 308B 308C 308D 308E 308F 3090 3091 3092 3093 30A1 30A2 30A3 30A4 30A5 30A6 30A7 30A8 30A9 30AA 30AB 30AC 30AD 30AE 30AF 30B0 30B1 30B2 30B3 30B4 30B5 30B6 30B7 30B8 30B9 30BA 30BB 30BC 30BD 30BE 30BF 30C0 30C1 30C2 30C3 30C4 30C5 30C6 30C7 30C8 30C9 30CA 30CB 30CC 30CD 30CE 30CF 30D0 30D1 30D2 30D3 30D4 30D5 30D6 30D7 30D8 30D9 30DA 30DB 30DC 30DD 30DE 30DF 30E0 30E1 30E2 30E3 30E4 30E5 30E6 30E7 30E8 30E9 30EA 30EB 30EC 30ED 30EE 30EF 30F0 30F1 30F2 30F3 30F4 30F5 30F6').replace(/(\S{4})/g, function(a){return "%u"+a;}).split(' ');
var funcInsert = jindo.$Fn(this.oApp.exec, this.oApp).bind("INSERT_SCHARACTERS", [this.oTextField.value]);
jindo.$Fn(funcInsert, this).attach(this.oInsertButton, "click");
this.oApp.exec("SET_SCHARACTER_LIST", [this.charSet]);
for(var i=0; i<this.aLabel.length; i++){
var func = jindo.$Fn(this.oApp.exec, this.oApp).bind("CHANGE_SCHARACTER_SET", [i]);
jindo.$Fn(func, this).attach(this.aLabel[i].firstChild, "mousedown");
}
for(var i=0; i<this.aCloseButton.length; i++){
this.oApp.registerBrowserEvent(this.aCloseButton[i], "click", "HIDE_ACTIVE_LAYER", []);
}
this.oApp.registerBrowserEvent(this.elDropdownLayer, "click", "EVENT_SCHARACTER_CLICKED", []);
this.oApp.registerBrowserEvent(this.oTextField, "keydown", "EVENT_SCHARACTER_KEYDOWN");
},
$ON_EVENT_SCHARACTER_KEYDOWN : function(oEvent){
if (oEvent.key().enter){
this.oApp.exec("INSERT_SCHARACTERS");
oEvent.stop();
}
},
$ON_TOGGLE_SCHARACTER_LAYER : function(){
this.oTextField.value = "";
this.oSelection = this.oApp.getSelection();
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.elDropdownLayer, null, "MSG_SCHARACTER_LAYER_SHOWN", [], "MSG_SCHARACTER_LAYER_HIDDEN", [""]]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['symbol']);
},
$ON_MSG_SCHARACTER_LAYER_SHOWN : function(){
this.oTextField.focus();
this.oApp.exec("SELECT_UI", ["sCharacter"]);
},
$ON_MSG_SCHARACTER_LAYER_HIDDEN : function(){
this.oApp.exec("DESELECT_UI", ["sCharacter"]);
},
$ON_EVENT_SCHARACTER_CLICKED : function(weEvent){
var elButton = nhn.husky.SE2M_Utils.findAncestorByTagName("BUTTON", weEvent.element);
if(!elButton || elButton.tagName != "BUTTON"){return;}
if(elButton.parentNode.tagName != "LI"){return;}
var sChar = elButton.firstChild.innerHTML;
if(sChar.length > 1){return;}
this.oApp.exec("SELECT_SCHARACTER", [sChar]);
weEvent.stop();
},
$ON_SELECT_SCHARACTER : function(schar){
this.oTextField.value += schar;
if(this.oTextField.createTextRange){
var oTextRange = this.oTextField.createTextRange();
oTextRange.collapse(false);
oTextRange.select();
}else{
if(this.oTextField.selectionEnd){
this.oTextField.selectionEnd = this.oTextField.value.length;
this.oTextField.focus();
}
}
},
$ON_INSERT_SCHARACTERS : function(){
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["INSERT SCHARACTER"]);
this.oApp.exec("PASTE_HTML", [this.oTextField.value]);
this.oApp.exec("FOCUS");
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["INSERT SCHARACTER"]);
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
},
$ON_CHANGE_SCHARACTER_SET : function(nSCharSet){
for(var i=0; i<this.aSCharList.length; i++){
if(jindo.$Element(this.aLabel[i]).hasClass("active")){
if(i == nSCharSet){return;}
jindo.$Element(this.aLabel[i]).removeClass("active");
}
}
this._drawSCharList(nSCharSet);
jindo.$Element(this.aLabel[nSCharSet]).addClass("active");
},
$ON_SET_SCHARACTER_LIST : function(charSet){
this.charSet = charSet;
this.bSCharSetDrawn = new Array(this.charSet.length);
this._drawSCharList(0);
},
_drawSCharList : function(i){
if(this.bSCharSetDrawn[i]){return;}
this.bSCharSetDrawn[i] = true;
var len = this.charSet[i].length;
var aLI = new Array(len);
this.aSCharList[i].innerHTML = '';
var button, span;
for(var ii=0; ii<len; ii++){
aLI[ii] = jindo.$("<LI>");
if(this.bIE){
button = jindo.$("<BUTTON>");
button.setAttribute('type', 'button');
}else{
button = jindo.$("<BUTTON>");
button.type = "button";
}
span = jindo.$("<SPAN>");
span.innerHTML = unescape(this.charSet[i][ii]);
button.appendChild(span);
aLI[ii].appendChild(button);
aLI[ii].onmouseover = function(){this.className='hover'};
aLI[ii].onmouseout = function(){this.className=''};
this.aSCharList[i].appendChild(aLI[ii]);
}
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_TableCreator$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_TableCreator, {
_assignHTMLObjects : function(oAppContainer){
this.oApp.exec("LOAD_HTML", ["create_table"]);
var tmp = null;
this.elDropdownLayer = jindo.$$.getSingle("DIV.husky_se2m_table_layer", oAppContainer);
this.welDropdownLayer = jindo.$Element(this.elDropdownLayer);
tmp = jindo.$$("INPUT", this.elDropdownLayer);
this.elText_row = tmp[0];
this.elText_col = tmp[1];
this.elRadio_manualStyle = tmp[2];
this.elText_borderSize = tmp[3];
this.elText_borderColor = tmp[4];
this.elText_BGColor = tmp[5];
this.elRadio_templateStyle = tmp[6];
tmp = jindo.$$("BUTTON", this.elDropdownLayer);
this.elBtn_rowInc = tmp[0];
this.elBtn_rowDec = tmp[1];
this.elBtn_colInc = tmp[2];
this.elBtn_colDec = tmp[3];
this.elBtn_borderStyle = tmp[4];
this.elBtn_incBorderSize = jindo.$$.getSingle("BUTTON.se2m_incBorder", this.elDropdownLayer);
this.elBtn_decBorderSize = jindo.$$.getSingle("BUTTON.se2m_decBorder", this.elDropdownLayer);
this.elLayer_Dim1 = jindo.$$.getSingle("DIV.se2_t_dim0", this.elDropdownLayer);
this.elLayer_Dim2 = jindo.$$.getSingle("DIV.se2_t_dim3", this.elDropdownLayer);
tmp = jindo.$$("SPAN.se2_pre_color>BUTTON", this.elDropdownLayer);
this.elBtn_borderColor = tmp[0];
this.elBtn_BGColor = tmp[1];
this.elBtn_tableStyle =  jindo.$$.getSingle("DIV.se2_select_ty2>BUTTON", this.elDropdownLayer);
tmp = jindo.$$("P.se2_btn_area>BUTTON", this.elDropdownLayer);
this.elBtn_apply = tmp[0];
this.elBtn_cancel = tmp[1];
this.elTable_preview = jindo.$$.getSingle("TABLE.husky_se2m_table_preview", this.elDropdownLayer);
this.elLayer_borderStyle = jindo.$$.getSingle("DIV.husky_se2m_table_border_style_layer", this.elDropdownLayer);
this.elPanel_borderStylePreview = jindo.$$.getSingle("SPAN.husky_se2m_table_border_style_preview", this.elDropdownLayer);
this.elPanel_borderColorPallet = jindo.$$.getSingle("DIV.husky_se2m_table_border_color_pallet", this.elDropdownLayer);
this.elPanel_bgColorPallet = jindo.$$.getSingle("DIV.husky_se2m_table_bgcolor_pallet", this.elDropdownLayer);
this.elLayer_tableStyle = jindo.$$.getSingle("DIV.husky_se2m_table_style_layer", this.elDropdownLayer);
this.elPanel_tableStylePreview = jindo.$$.getSingle("SPAN.husky_se2m_table_style_preview", this.elDropdownLayer);
this.aElBtn_borderStyle = jindo.$$("BUTTON", this.elLayer_borderStyle);
this.aElBtn_tableStyle = jindo.$$("BUTTON", this.elLayer_tableStyle);
this.sNoBorderText = jindo.$$.getSingle("SPAN.se2m_no_border", this.elDropdownLayer).innerHTML;
this.rxLastDigits = RegExp("([0-9]+)$");
},
$LOCAL_BEFORE_FIRST : function(){
this._assignHTMLObjects(this.oApp.htOptions.elAppContainer);
this.oApp.registerBrowserEvent(this.elText_row, "change", "TABLE_SET_ROW_NUM", [null, 0]);
this.oApp.registerBrowserEvent(this.elText_col, "change", "TABLE_SET_COLUMN_NUM", [null, 0]);
this.oApp.registerBrowserEvent(this.elText_borderSize, "change", "TABLE_SET_BORDER_SIZE", [null, 0]);
this.oApp.registerBrowserEvent(this.elBtn_rowInc, "click", "TABLE_INC_ROW");
this.oApp.registerBrowserEvent(this.elBtn_rowDec, "click", "TABLE_DEC_ROW");
jindo.$Fn(this._numRowKeydown, this).attach(this.elText_row.parentNode, "keydown");
this.oApp.registerBrowserEvent(this.elBtn_colInc, "click", "TABLE_INC_COLUMN");
this.oApp.registerBrowserEvent(this.elBtn_colDec, "click", "TABLE_DEC_COLUMN");
jindo.$Fn(this._numColKeydown, this).attach(this.elText_col.parentNode, "keydown");
this.oApp.registerBrowserEvent(this.elBtn_incBorderSize, "click", "TABLE_INC_BORDER_SIZE");
this.oApp.registerBrowserEvent(this.elBtn_decBorderSize, "click", "TABLE_DEC_BORDER_SIZE");
jindo.$Fn(this._borderSizeKeydown, this).attach(this.elText_borderSize.parentNode, "keydown");
this.oApp.registerBrowserEvent(this.elBtn_borderStyle, "click", "TABLE_TOGGLE_BORDER_STYLE_LAYER");
this.oApp.registerBrowserEvent(this.elBtn_tableStyle, "click", "TABLE_TOGGLE_STYLE_LAYER");
this.oApp.registerBrowserEvent(this.elBtn_borderColor, "click", "TABLE_TOGGLE_BORDER_COLOR_PALLET");
this.oApp.registerBrowserEvent(this.elBtn_BGColor, "click", "TABLE_TOGGLE_BGCOLOR_PALLET");
this.oApp.registerBrowserEvent(this.elRadio_manualStyle, "click", "TABLE_ENABLE_MANUAL_STYLE");
this.oApp.registerBrowserEvent(this.elRadio_templateStyle, "click", "TABLE_ENABLE_TEMPLATE_STYLE");
this.oApp.exec("SE2_ATTACH_HOVER_EVENTS", [this.aElBtn_borderStyle]);
this.oApp.exec("SE2_ATTACH_HOVER_EVENTS", [this.aElBtn_tableStyle]);
var i;
for(i=0; i<this.aElBtn_borderStyle.length; i++){
this.oApp.registerBrowserEvent(this.aElBtn_borderStyle[i], "click", "TABLE_SELECT_BORDER_STYLE");
}
for(i=0; i<this.aElBtn_tableStyle.length; i++){
this.oApp.registerBrowserEvent(this.aElBtn_tableStyle[i], "click", "TABLE_SELECT_STYLE");
}
this.oApp.registerBrowserEvent(this.elBtn_apply, "click", "TABLE_INSERT");
this.oApp.registerBrowserEvent(this.elBtn_cancel, "click", "HIDE_ACTIVE_LAYER");
this.oApp.exec("TABLE_SET_BORDER_COLOR", [/#[0-9A-Fa-f]{6}/.test(this.elText_borderColor.value) ? this.elText_borderColor.value : "#cccccc"]);
this.oApp.exec("TABLE_SET_BGCOLOR", [/#[0-9A-Fa-f]{6}/.test(this.elText_BGColor.value) ? this.elText_BGColor.value : "#ffffff"]);
this.nStyleMode = 1;
this.aTableStyleByBorder = [
'',
'border="1" cellpadding="0" cellspacing="0" style="border:1px dashed #c7c7c7; border-left:0; border-bottom:0;"',
'border="1" cellpadding="0" cellspacing="0" style="border:#BorderSize#px dashed #BorderColor#; border-left:0; border-bottom:0;"',
'border="0" cellpadding="0" cellspacing="0" style="border:#BorderSize#px solid #BorderColor#; border-left:0; border-bottom:0;"',
'border="0" cellpadding="0" cellspacing="1" style="border:#BorderSize#px solid #BorderColor#;"',
'border="0" cellpadding="0" cellspacing="1" style="border:#BorderSize#px double #BorderColor#;"',
'border="0" cellpadding="0" cellspacing="1" style="border-width:#BorderSize*2#px #BorderSize#px #BorderSize#px #BorderSize*2#px; border-style:solid;border-color:#BorderColor#;"',
'border="0" cellpadding="0" cellspacing="1" style="border-width:#BorderSize#px #BorderSize*2#px #BorderSize*2#px #BorderSize#px; border-style:solid;border-color:#BorderColor#;"'
];
this.aTDStyleByBorder = [
'',
'style="border:1px dashed #c7c7c7; border-top:0; border-right:0; background-color:#BGColor#"',
'style="border:#BorderSize#px dashed #BorderColor#; border-top:0; border-right:0; background-color:#BGColor#"',
'style="border:#BorderSize#px solid #BorderColor#; border-top:0; border-right:0; background-color:#BGColor#"',
'style="border:#BorderSize#px solid #BorderColor#; background-color:#BGColor#"',
'style="border:#BorderSize+2#px double #BorderColor#; background-color:#BGColor#"',
'style="border-width:#BorderSize#px #BorderSize*2#px #BorderSize*2#px #BorderSize#px; border-style:solid;border-color:#BorderColor#; background-color:#BGColor#"',
'style="border-width:#BorderSize*2#px #BorderSize#px #BorderSize#px #BorderSize*2#px; border-style:solid;border-color:#BorderColor#; background-color:#BGColor#"'
];
this.oApp.registerBrowserEvent(this.elDropdownLayer, "keydown", "EVENT_TABLE_CREATE_KEYDOWN");
this._drawTableDropdownLayer();
},
$ON_TABLE_SELECT_BORDER_STYLE : function(weEvent){
var elButton = weEvent.currentElement;
var aMatch = this.rxLastDigits.exec(elButton.className);
this._selectBorderStyle(aMatch[1]);
},
$ON_TABLE_SELECT_STYLE : function(weEvent){
var aMatch = this.rxLastDigits.exec(weEvent.element.className);
this._selectTableStyle(aMatch[1]);
},
$ON_TOGGLE_TABLE_LAYER : function(){
this._showNewTable();
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.elDropdownLayer, null, "SELECT_UI", ["table"], "TABLE_CLOSE", []]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['table']);
},
$ON_TABLE_CLOSE_ALL : function(){
this.oApp.exec("TABLE_HIDE_BORDER_COLOR_PALLET", []);
this.oApp.exec("TABLE_HIDE_BGCOLOR_PALLET", []);
this.oApp.exec("TABLE_HIDE_BORDER_STYLE_LAYER", []);
this.oApp.exec("TABLE_HIDE_STYLE_LAYER", []);
},
$ON_TABLE_INC_ROW : function(){
this.oApp.exec("TABLE_SET_ROW_NUM", [null, 1]);
},
$ON_TABLE_DEC_ROW : function(){
this.oApp.exec("TABLE_SET_ROW_NUM", [null, -1]);
},
$ON_TABLE_INC_COLUMN : function(){
this.oApp.exec("TABLE_SET_COLUMN_NUM", [null, 1]);
},
$ON_TABLE_DEC_COLUMN : function(){
this.oApp.exec("TABLE_SET_COLUMN_NUM", [null, -1]);
},
$ON_TABLE_SET_ROW_NUM : function(nRows, nRowDiff){
nRows = nRows || parseInt(this.elText_row.value, 10) || 0;
nRowDiff = nRowDiff || 0;
nRows += nRowDiff;
if(nRows < this.nMinRows){nRows = this.nMinRows;}
if(nRows > this.nMaxRows){nRows = this.nMaxRows;}
this.elText_row.value = nRows;
this._showNewTable();
},
$ON_TABLE_SET_COLUMN_NUM : function(nColumns, nColumnDiff){
nColumns = nColumns || parseInt(this.elText_col.value, 10) || 0;
nColumnDiff = nColumnDiff || 0;
nColumns += nColumnDiff;
if(nColumns < this.nMinColumns){nColumns = this.nMinColumns;}
if(nColumns > this.nMaxColumns){nColumns = this.nMaxColumns;}
this.elText_col.value = nColumns;
this._showNewTable();
},
_getTableString : function(){
var sTable;
if(this.nStyleMode == 1){
sTable = this._doGetTableString(this.nColumns, this.nRows, this.nBorderSize, this.sBorderColor, this.sBGColor, this.nBorderStyleIdx);
}else{
sTable = this._doGetTableString(this.nColumns, this.nRows, this.nBorderSize, this.sBorderColor, this.sBGColor, 0);
}
return sTable;
},
$ON_TABLE_INSERT : function(){
this.oApp.exec("IE_FOCUS", []);
this.oApp.exec("TABLE_SET_COLUMN_NUM");
this.oApp.exec("TABLE_SET_ROW_NUM");
this._loadValuesFromHTML();
var sTable,
elLinebreak,
elBody,
welBody,
elTmpDiv,
elTable,
elFirstTD,
oSelection,
elTableHolder,
htBrowser;
elBody = this.oApp.getWYSIWYGDocument().body;
welBody = jindo.$Element(elBody);
htBrowser = jindo.$Agent().navigator();
this.nTableWidth = elBody.offsetWidth;
sTable = this._getTableString();
elTmpDiv = this.oApp.getWYSIWYGDocument().createElement("DIV");
elTmpDiv.innerHTML = sTable;
elTable = elTmpDiv.firstChild;
elTable.className = this._sSETblClass;
oSelection = this.oApp.getSelection();
oSelection = this._divideParagraph(oSelection);
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["INSERT TABLE", {sSaveTarget:"BODY"}]);
elTableHolder = this.oApp.getWYSIWYGDocument().createElement("DIV");
oSelection.deleteContents();
oSelection.insertNode(elTableHolder);
oSelection.selectNode(elTableHolder);
this.oApp.exec("REMOVE_STYLE", [oSelection]);
if(htBrowser.ie && this.oApp.getWYSIWYGDocument().body.childNodes.length === 1 && this.oApp.getWYSIWYGDocument().body.firstChild === elTableHolder){
elTableHolder.insertBefore(elTable, null);
}else{
elTableHolder.parentNode.insertBefore(elTable, elTableHolder);
elTableHolder.parentNode.removeChild(elTableHolder);
}
if(htBrowser.firefox){
elLinebreak = this.oApp.getWYSIWYGDocument().createElement("BR");
elTable.parentNode.insertBefore(elLinebreak, elTable.nextSibling);
}else if(htBrowser.ie ){
elLinebreak = this.oApp.getWYSIWYGDocument().createElement("p");
elTable.parentNode.insertBefore(elLinebreak, elTable.nextSibling);
}
if(this.nStyleMode == 2){
this.oApp.exec("STYLE_TABLE", [elTable, this.nTableStyleIdx]);
}
elFirstTD = elTable.getElementsByTagName("TD")[0];
oSelection.selectNodeContents(elFirstTD.firstChild || elFirstTD);
oSelection.collapseToEnd();
oSelection.select();
this.oApp.exec("FOCUS");
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["INSERT TABLE", {sSaveTarget:"BODY"}]);
this.oApp.exec("HIDE_ACTIVE_LAYER", []);
this.oApp.exec('MSG_DISPLAY_REEDIT_MESSAGE_SHOW', [this.name, this.sReEditGuideMsg_table]);
},
_divideParagraph : function(oSelection){
var oParentP,
welParentP,
sNodeVaule,
sBM, oSWrapper, oEWrapper;
oSelection.fixCommonAncestorContainer();
var _elCommonAncestorContainer = oSelection.commonAncestorContainer;
var _htAncestor_P = nhn.husky.SE2M_Utils.findAncestorByTagNameWithCount("P", _elCommonAncestorContainer);
var _elAncestor_P = _htAncestor_P.elNode;
if(_elAncestor_P){
var _htAncestor_Cell = nhn.husky.SE2M_Utils.findClosestAncestorAmongTagNamesWithCount(["TD", "TH"], _elCommonAncestorContainer);
var _elAncestor_Cell = _htAncestor_Cell.elNode;
if(_elAncestor_Cell){
var _nRecursiveCount_P = _htAncestor_P.nRecursiveCount;
var _nRecursiveCount_Cell = _htAncestor_Cell.nRecursiveCount;
if(_nRecursiveCount_P < _nRecursiveCount_Cell){
oParentP = _elAncestor_P;
}
}else{
oParentP = _elAncestor_P;
}
}
if(!oParentP){
return oSelection;
}
if(!oParentP.firstChild || nhn.husky.SE2M_Utils.isBlankNode(oParentP)){
oSelection.selectNode(oParentP);
oSelection.select();
return oSelection;
}
sBM = oSelection.placeStringBookmark();
oSelection.moveToBookmark(sBM);
oSWrapper = this.oApp.getWYSIWYGDocument().createElement("P");
oSelection.setStartBefore(oParentP.firstChild);
oSelection.surroundContents(oSWrapper);
oSelection.collapseToEnd();
oEWrapper = this.oApp.getWYSIWYGDocument().createElement("P");
oSelection.setEndAfter(oParentP.lastChild);
oSelection.surroundContents(oEWrapper);
oSelection.collapseToStart();
oSelection.removeStringBookmark(sBM);
welParentP = jindo.$Element(oParentP);
welParentP.after(oEWrapper);
welParentP.after(oSWrapper);
welParentP.leave();
oSelection = this.oApp.getEmptySelection();
oSelection.setEndAfter(oSWrapper);
oSelection.setStartBefore(oEWrapper);
oSelection.select();
return oSelection;
},
$ON_TABLE_CLOSE : function(){
this.oApp.exec("TABLE_CLOSE_ALL", []);
this.oApp.exec("DESELECT_UI", ["table"]);
},
$ON_TABLE_SET_BORDER_SIZE : function(nBorderWidth, nBorderWidthDiff){
nBorderWidth = nBorderWidth || parseInt(this.elText_borderSize.value, 10) || 0;
nBorderWidthDiff = nBorderWidthDiff || 0;
nBorderWidth += nBorderWidthDiff;
if(nBorderWidth < this.nMinBorderWidth){nBorderWidth = this.nMinBorderWidth;}
if(nBorderWidth > this.nMaxBorderWidth){nBorderWidth = this.nMaxBorderWidth;}
this.elText_borderSize.value = nBorderWidth;
},
$ON_TABLE_INC_BORDER_SIZE : function(){
this.oApp.exec("TABLE_SET_BORDER_SIZE", [null, 1]);
},
$ON_TABLE_DEC_BORDER_SIZE : function(){
this.oApp.exec("TABLE_SET_BORDER_SIZE", [null, -1]);
},
$ON_TABLE_TOGGLE_BORDER_STYLE_LAYER : function(){
if(this.elLayer_borderStyle.style.display == "block"){
this.oApp.exec("TABLE_HIDE_BORDER_STYLE_LAYER", []);
}else{
this.oApp.exec("TABLE_SHOW_BORDER_STYLE_LAYER", []);
}
},
$ON_TABLE_SHOW_BORDER_STYLE_LAYER : function(){
this.oApp.exec("TABLE_CLOSE_ALL", []);
this.elBtn_borderStyle.className = "se2_view_more2";
this.elLayer_borderStyle.style.display = "block";
this._refresh();
},
$ON_TABLE_HIDE_BORDER_STYLE_LAYER : function(){
this.elBtn_borderStyle.className = "se2_view_more";
this.elLayer_borderStyle.style.display = "none";
this._refresh();
},
$ON_TABLE_TOGGLE_STYLE_LAYER : function(){
if(this.elLayer_tableStyle.style.display == "block"){
this.oApp.exec("TABLE_HIDE_STYLE_LAYER", []);
}else{
this.oApp.exec("TABLE_SHOW_STYLE_LAYER", []);
}
},
$ON_TABLE_SHOW_STYLE_LAYER : function(){
this.oApp.exec("TABLE_CLOSE_ALL", []);
this.elBtn_tableStyle.className = "se2_view_more2";
this.elLayer_tableStyle.style.display = "block";
this._refresh();
},
$ON_TABLE_HIDE_STYLE_LAYER : function(){
this.elBtn_tableStyle.className = "se2_view_more";
this.elLayer_tableStyle.style.display = "none";
this._refresh();
},
$ON_TABLE_TOGGLE_BORDER_COLOR_PALLET : function(){
if(this.welDropdownLayer.hasClass("p1")){
this.oApp.exec("TABLE_HIDE_BORDER_COLOR_PALLET", []);
}else{
this.oApp.exec("TABLE_SHOW_BORDER_COLOR_PALLET", []);
}
},
$ON_TABLE_SHOW_BORDER_COLOR_PALLET : function(){
this.oApp.exec("TABLE_CLOSE_ALL", []);
this.welDropdownLayer.addClass("p1");
this.welDropdownLayer.removeClass("p2");
this.oApp.exec("SHOW_COLOR_PALETTE", ["TABLE_SET_BORDER_COLOR_FROM_PALETTE", this.elPanel_borderColorPallet]);
this.elPanel_borderColorPallet.parentNode.style.display = "block";
},
$ON_TABLE_HIDE_BORDER_COLOR_PALLET : function(){
this.welDropdownLayer.removeClass("p1");
this.oApp.exec("HIDE_COLOR_PALETTE", []);
this.elPanel_borderColorPallet.parentNode.style.display = "none";
},
$ON_TABLE_TOGGLE_BGCOLOR_PALLET : function(){
if(this.welDropdownLayer.hasClass("p2")){
this.oApp.exec("TABLE_HIDE_BGCOLOR_PALLET", []);
}else{
this.oApp.exec("TABLE_SHOW_BGCOLOR_PALLET", []);
}
},
$ON_TABLE_SHOW_BGCOLOR_PALLET : function(){
this.oApp.exec("TABLE_CLOSE_ALL", []);
this.welDropdownLayer.removeClass("p1");
this.welDropdownLayer.addClass("p2");
this.oApp.exec("SHOW_COLOR_PALETTE", ["TABLE_SET_BGCOLOR_FROM_PALETTE", this.elPanel_bgColorPallet]);
this.elPanel_bgColorPallet.parentNode.style.display = "block";
},
$ON_TABLE_HIDE_BGCOLOR_PALLET : function(){
this.welDropdownLayer.removeClass("p2");
this.oApp.exec("HIDE_COLOR_PALETTE", []);
this.elPanel_bgColorPallet.parentNode.style.display = "none";
},
$ON_TABLE_SET_BORDER_COLOR_FROM_PALETTE : function(sColorCode){
this.oApp.exec("TABLE_SET_BORDER_COLOR", [sColorCode]);
this.oApp.exec("TABLE_HIDE_BORDER_COLOR_PALLET", []);
},
$ON_TABLE_SET_BORDER_COLOR : function(sColorCode){
this.elText_borderColor.value = sColorCode;
this.elBtn_borderColor.style.backgroundColor = sColorCode;
},
$ON_TABLE_SET_BGCOLOR_FROM_PALETTE : function(sColorCode){
this.oApp.exec("TABLE_SET_BGCOLOR", [sColorCode]);
this.oApp.exec("TABLE_HIDE_BGCOLOR_PALLET", []);
},
$ON_TABLE_SET_BGCOLOR : function(sColorCode){
this.elText_BGColor.value = sColorCode;
this.elBtn_BGColor.style.backgroundColor = sColorCode;
},
$ON_TABLE_ENABLE_MANUAL_STYLE : function(){
this.nStyleMode = 1;
this._drawTableDropdownLayer();
},
$ON_TABLE_ENABLE_TEMPLATE_STYLE : function(){
this.nStyleMode = 2;
this._drawTableDropdownLayer();
},
$ON_EVENT_TABLE_CREATE_KEYDOWN : function(oEvent){
if (oEvent.key().enter){
this.elBtn_apply.focus();
this.oApp.exec("TABLE_INSERT");
oEvent.stop();
}
},
_drawTableDropdownLayer : function(){
if(this.nBorderStyleIdx == 1){
this.elPanel_borderStylePreview.innerHTML = this.sNoBorderText;
this.elLayer_Dim1.className = "se2_t_dim2";
}else{
this.elPanel_borderStylePreview.innerHTML = "";
this.elLayer_Dim1.className = "se2_t_dim0";
}
if(this.nStyleMode == 1){
this.elRadio_manualStyle.checked = true;
this.elLayer_Dim2.className = "se2_t_dim3";
this.elText_borderSize.disabled = false;
this.elText_borderColor.disabled = false;
this.elText_BGColor.disabled = false;
}else{
this.elRadio_templateStyle.checked = true;
this.elLayer_Dim2.className = "se2_t_dim1";
this.elText_borderSize.disabled = true;
this.elText_borderColor.disabled = true;
this.elText_BGColor.disabled = true;
}
this.oApp.exec("TABLE_CLOSE_ALL", []);
},
_selectBorderStyle : function(nStyleNum){
this.elPanel_borderStylePreview.className = "se2_b_style"+nStyleNum;
this.nBorderStyleIdx = nStyleNum;
this._drawTableDropdownLayer();
},
_selectTableStyle : function(nStyleNum){
this.elPanel_tableStylePreview.className = "se2_t_style"+nStyleNum;
this.nTableStyleIdx = nStyleNum;
this._drawTableDropdownLayer();
},
_showNewTable : function(){
var oTmp = document.createElement("DIV");
this._loadValuesFromHTML();
oTmp.innerHTML = this._getPreviewTableString(this.nColumns, this.nRows);
var oNewTable = oTmp.firstChild;
this.elTable_preview.parentNode.insertBefore(oNewTable, this.elTable_preview);
this.elTable_preview.parentNode.removeChild(this.elTable_preview);
this.elTable_preview = oNewTable;
this._refresh();
},
_getPreviewTableString : function(nColumns, nRows){
var sTable = '<table border="0" cellspacing="1" class="se2_pre_table husky_se2m_table_preview">';
var sRow = '<tr>';
for(var i=0; i<nColumns; i++){
sRow += "<td><p>&nbsp;</p></td>\n";
}
sRow += "</tr>\n";
sTable += "<tbody>";
for(var i=0; i<nRows; i++){
sTable += sRow;
}
sTable += "</tbody>\n";
sTable += "</table>\n";
return sTable;
},
_loadValuesFromHTML : function(){
this.nColumns = parseInt(this.elText_col.value, 10) || 1;
this.nRows = parseInt(this.elText_row.value, 10) || 1;
this.nBorderSize = parseInt(this.elText_borderSize.value, 10) || 1;
this.sBorderColor = this.elText_borderColor.value;
this.sBGColor = this.elText_BGColor.value;
},
_doGetTableString : function(nColumns, nRows, nBorderSize, sBorderColor, sBGColor, nBorderStyleIdx){
var nTDWidth = parseInt(this.nTableWidth/nColumns, 10);
var nBorderSize = this.nBorderSize;
var sTableStyle = this.aTableStyleByBorder[nBorderStyleIdx].replace(/#BorderSize#/g, this.nBorderSize).replace(/#BorderSize\*([0-9]+)#/g, function(sAll, s1){return nBorderSize*parseInt(s1, 10);}).replace(/#BorderSize\+([0-9]+)#/g, function(sAll, s1){return nBorderSize+parseInt(s1, 10);}).replace("#BorderColor#", this.sBorderColor).replace("#BGColor#", this.sBGColor);
var sTDStyle = this.aTDStyleByBorder[nBorderStyleIdx].replace(/#BorderSize#/g, this.nBorderSize).replace(/#BorderSize\*([0-9]+)#/g, function(sAll, s1){return nBorderSize*parseInt(s1, 10);}).replace(/#BorderSize\+([0-9]+)#/g, function(sAll, s1){return nBorderSize+parseInt(s1, 10);}).replace("#BorderColor#", this.sBorderColor).replace("#BGColor#", this.sBGColor);
if(nTDWidth){
sTDStyle += " width="+nTDWidth;
}else{
sTableStyle += "class=se2_pre_table";
}
var sTempNoBorderClass = (nBorderStyleIdx == 1) ? 'attr_no_border_tbl="1"' : '';
var sTable = "<table "+sTableStyle+" "+sTempNoBorderClass+">";
var sRow = "<tr>";
for(var i=0; i<nColumns; i++){
sRow += "<td "+sTDStyle+"><p>&nbsp;</p></td>\n";
}
sRow += "</tr>\n";
sTable += "<tbody>\n";
for(var i=0; i<nRows; i++){
sTable += sRow;
}
sTable += "</tbody>\n";
sTable += "</table>\n<br>";
return sTable;
},
_numRowKeydown : function(weEvent){
var oKeyInfo = weEvent.key();
if(oKeyInfo.keyCode == 38){
this.oApp.exec("TABLE_INC_ROW", []);
}
if(oKeyInfo.keyCode == 40){
this.oApp.exec("TABLE_DEC_ROW", []);
}
},
_numColKeydown : function(weEvent){
var oKeyInfo = weEvent.key();
if(oKeyInfo.keyCode == 38){
this.oApp.exec("TABLE_INC_COLUMN", []);
}
if(oKeyInfo.keyCode == 40){
this.oApp.exec("TABLE_DEC_COLUMN", []);
}
},
_borderSizeKeydown : function(weEvent){
var oKeyInfo = weEvent.key();
if(oKeyInfo.keyCode == 38){
this.oApp.exec("TABLE_INC_BORDER_SIZE", []);
}
if(oKeyInfo.keyCode == 40){
this.oApp.exec("TABLE_DEC_BORDER_SIZE", []);
}
},
_refresh : function(){
this.elDropdownLayer.style.zoom=0;
this.elDropdownLayer.style.zoom="";
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_TableEditor$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_TableEditor, {
_aCellName : ["TD", "TH"],
_assignHTMLObjects : function(){
this.oApp.exec("LOAD_HTML", ["qe_table"]);
this.elQELayer = jindo.$$.getSingle("DIV.q_table_wrap", this.oApp.htOptions.elAppContainer);
this.elQELayer.style.zIndex = 150;
this.elBtnAddRowBelow = jindo.$$.getSingle("BUTTON.se2_addrow", this.elQELayer);
this.elBtnAddColumnRight = jindo.$$.getSingle("BUTTON.se2_addcol", this.elQELayer);
this.elBtnSplitRow = jindo.$$.getSingle("BUTTON.se2_seprow", this.elQELayer);
this.elBtnSplitColumn = jindo.$$.getSingle("BUTTON.se2_sepcol", this.elQELayer);
this.elBtnDeleteRow = jindo.$$.getSingle("BUTTON.se2_delrow", this.elQELayer);
this.elBtnDeleteColumn = jindo.$$.getSingle("BUTTON.se2_delcol", this.elQELayer);
this.elBtnMergeCell = jindo.$$.getSingle("BUTTON.se2_merrow", this.elQELayer);
this.elBtnBGPalette = jindo.$$.getSingle("BUTTON.husky_se2m_table_qe_bgcolor_btn", this.elQELayer);
this.elBtnBGIMGPalette = jindo.$$.getSingle("BUTTON.husky_se2m_table_qe_bgimage_btn", this.elQELayer);
this.elPanelBGPaletteHolder = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_bg_paletteHolder", this.elQELayer);
this.elPanelBGIMGPaletteHolder = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_bg_img_paletteHolder", this.elQELayer);
this.elPanelTableBGArea = jindo.$$.getSingle("DIV.se2_qe2", this.elQELayer);
this.elPanelTableTemplateArea = jindo.$$.getSingle("DL.se2_qe3", this.elQELayer);
this.elPanelReviewBGArea = jindo.$$.getSingle("DL.husky_se2m_tbl_qe_review_bg", this.elQELayer);
this.elPanelBGImg = jindo.$$.getSingle("DD", this.elPanelReviewBGArea);
this.welPanelTableBGArea = jindo.$Element(this.elPanelTableBGArea);
this.welPanelTableTemplateArea = jindo.$Element(this.elPanelTableTemplateArea);
this.welPanelReviewBGArea = jindo.$Element(this.elPanelReviewBGArea);
this.elPanelDim1 = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_dim1", this.elQELayer);
this.elPanelDim2 = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_dim2", this.elQELayer);
this.elPanelDimDelCol = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_dim_del_col", this.elQELayer);
this.elPanelDimDelRow = jindo.$$.getSingle("DIV.husky_se2m_tbl_qe_dim_del_row", this.elQELayer);
this.elInputRadioBGColor = jindo.$$.getSingle("INPUT.husky_se2m_radio_bgc", this.elQELayer);
this.elInputRadioBGImg = jindo.$$.getSingle("INPUT.husky_se2m_radio_bgimg", this.elQELayer);
this.elSelectBoxTemplate = jindo.$$.getSingle("DIV.se2_select_ty2", this.elQELayer);
this.elInputRadioTemplate = jindo.$$.getSingle("INPUT.husky_se2m_radio_template", this.elQELayer);
this.elPanelQETemplate = jindo.$$.getSingle("DIV.se2_layer_t_style", this.elQELayer);
this.elBtnQETemplate = jindo.$$.getSingle("BUTTON.husky_se2m_template_more", this.elQELayer);
this.elPanelQETemplatePreview = jindo.$$.getSingle("SPAN.se2_t_style1", this.elQELayer);
this.aElBtn_tableStyle = jindo.$$("BUTTON", this.elPanelQETemplate);
for(i = 0; i < this.aElBtn_tableStyle.length; i++){
this.oApp.registerBrowserEvent(this.aElBtn_tableStyle[i], "click", "TABLE_QE_SELECT_TEMPLATE");
}
},
$LOCAL_BEFORE_FIRST : function(sMsg){
if(sMsg.indexOf("REGISTER_CONVERTERS") > -1){
this.oApp.acceptLocalBeforeFirstAgain(this, true);
return true;
}
this.htResizing = {};
this.nDraggableCellEdge = 2;
var elBody = jindo.$Element(document.body);
this.nPageLeftRightMargin = parseInt(elBody.css("marginLeft"), 10) + parseInt(elBody.css("marginRight"), 10);
this.nPageTopBottomMargin = parseInt(elBody.css("marginTop"), 10) + parseInt(elBody.css("marginBottom"), 10);
this.QE_DIM_MERGE_BTN = 1;
this.QE_DIM_BG_COLOR = 2;
this.QE_DIM_REVIEW_BG_IMG = 3;
this.QE_DIM_TABLE_TEMPLATE = 4;
this.rxLastDigits = RegExp("([0-9]+)$");
this._assignHTMLObjects();
this.oApp.exec("SE2_ATTACH_HOVER_EVENTS", [this.aElBtn_tableStyle]);
this.addCSSClass(this.CELL_SELECTION_CLASS, "background-color:#B4C9E9;");
this._createCellResizeGrip();
this.elIFrame = this.oApp.getWYSIWYGWindow().frameElement;
var elTarget;
this.sEmptyTDSrc = "";
if(this.oApp.oNavigator.firefox){
this.sEmptyTDSrc = "<p><br/></p>";
}else{
this.sEmptyTDSrc = "<p>&nbsp;</p>";
}
elTarget = this.oApp.getWYSIWYGDocument();
elTarget = this.elResizeCover;
this.wfnMousedown_ResizeCover = jindo.$Fn(this._mousedown_ResizeCover, this);
this.wfnMousemove_ResizeCover = jindo.$Fn(this._mousemove_ResizeCover, this);
this.wfnMouseup_ResizeCover = jindo.$Fn(this._mouseup_ResizeCover, this);
this.wfnMousedown_ResizeCover.attach(elTarget, "mousedown");
this._changeTableEditorStatus(this.STATUS.S_0);
this.oApp.registerBrowserEvent(this.elBtnMergeCell, "click", "TE_MERGE_CELLS");
this.oApp.registerBrowserEvent(this.elBtnSplitColumn, "click", "TE_SPLIT_COLUMN");
this.oApp.registerBrowserEvent(this.elBtnSplitRow, "click", "TE_SPLIT_ROW");
this.oApp.registerBrowserEvent(this.elBtnAddColumnRight, "click", "TE_INSERT_COLUMN_RIGHT");
this.oApp.registerBrowserEvent(this.elBtnAddRowBelow, "click", "TE_INSERT_ROW_BELOW");
this.oApp.registerBrowserEvent(this.elBtnDeleteColumn, "click", "TE_DELETE_COLUMN");
this.oApp.registerBrowserEvent(this.elBtnDeleteRow, "click", "TE_DELETE_ROW");
this.oApp.registerBrowserEvent(this.elInputRadioBGColor, "click", "DRAW_QE_RADIO_OPTION", [2]);
this.oApp.registerBrowserEvent(this.elInputRadioBGImg, "click", "DRAW_QE_RADIO_OPTION", [3]);
this.oApp.registerBrowserEvent(this.elInputRadioTemplate, "click", "DRAW_QE_RADIO_OPTION", [4]);
this.oApp.registerBrowserEvent(this.elBtnBGPalette, "click", "TABLE_QE_TOGGLE_BGC_PALETTE");
this.oApp.registerBrowserEvent(this.elBtnBGIMGPalette, "click", "TABLE_QE_TOGGLE_IMG_PALETTE");
this.oApp.registerBrowserEvent(this.elPanelBGIMGPaletteHolder, "click", "TABLE_QE_SET_IMG_FROM_PALETTE");
this.oApp.registerBrowserEvent(this.elBtnQETemplate, "click", "TABLE_QE_TOGGLE_TEMPLATE");
this.oApp.registerBrowserEvent(document.body, "mouseup", "EVENT_OUTER_DOC_MOUSEUP");
this.oApp.registerBrowserEvent(document.body, "mousemove", "EVENT_OUTER_DOC_MOUSEMOVE");
this._rxCellNames = new RegExp("^(" + this._aCellName.join("|") + ")$", "i");
},
$ON_EVENT_EDITING_AREA_KEYUP : function(oEvent){
var oKeyInfo = oEvent.key();
if(oKeyInfo.keyCode == 229 || oKeyInfo.alt || oKeyInfo.ctrl || oKeyInfo.keyCode == 16){
return;
}else if(oKeyInfo.keyCode == 8 || oKeyInfo.keyCode == 46){
this.oApp.exec("DELETE_BLOCK_CONTENTS");
oEvent.stop();
}
switch(this.nStatus){
case this.STATUS.CELL_SELECTED:
this._changeTableEditorStatus(this.STATUS.S_0);
break;
}
},
$ON_TABLE_QE_SELECT_TEMPLATE : function(weEvent){
var aMatch = this.rxLastDigits.exec(weEvent.element.className);
var elCurrentTable = this.elSelectionStartTable;
this._changeTableEditorStatus(this.STATUS.S_0);
this.oApp.exec("STYLE_TABLE", [elCurrentTable, aMatch[1]]);
var elSaveTarget = !!elCurrentTable && elCurrentTable.parentNode ? elCurrentTable.parentNode : null;
var sSaveTarget = !elCurrentTable ? "BODY" : null;
this.oApp.exec("RECORD_UNDO_ACTION", ["CHANGE_TABLE_STYLE", {elSaveTarget:elSaveTarget, sSaveTarget : sSaveTarget, bDontSaveSelection:true}]);
},
$BEFORE_CHANGE_EDITING_MODE : function(sMode, bNoFocus){
if(sMode !== "WYSIWYG" && this.nStatus !== this.STATUS.S_0){
this._changeTableEditorStatus(this.STATUS.S_0);
}
},
$ON_TABLE_QE_TOGGLE_BGC_PALETTE : function(){
if(this.elPanelBGPaletteHolder.parentNode.style.display == "block"){
this.oApp.exec("HIDE_TABLE_QE_BGC_PALETTE", []);
}else{
this.oApp.exec("SHOW_TABLE_QE_BGC_PALETTE", []);
}
},
$ON_SHOW_TABLE_QE_BGC_PALETTE : function(){
this.elPanelBGPaletteHolder.parentNode.style.display = "block";
this.oApp.exec("SHOW_COLOR_PALETTE", ["TABLE_QE_SET_BGC_FROM_PALETTE", this.elPanelBGPaletteHolder]);
},
$ON_HIDE_TABLE_QE_BGC_PALETTE : function(){
this.elPanelBGPaletteHolder.parentNode.style.display = "none";
this.oApp.exec("HIDE_COLOR_PALETTE", []);
},
$ON_TABLE_QE_SET_BGC_FROM_PALETTE : function(sColorCode){
this.oApp.exec("TABLE_QE_SET_BGC", [sColorCode]);
if(this.oSelection){
this.oSelection.select();
}
this._changeTableEditorStatus(this.STATUS.S_0);
},
$ON_TABLE_QE_SET_BGC : function(sColorCode){
this.elBtnBGPalette.style.backgroundColor = sColorCode;
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
this.aSelectedCells[i].setAttribute(this.TMP_BGC_ATTR, sColorCode);
this.aSelectedCells[i].removeAttribute(this.TMP_BGIMG_ATTR);
}
this.sQEAction = "TABLE_SET_BGCOLOR";
},
$ON_TABLE_QE_TOGGLE_IMG_PALETTE : function(){
if(this.elPanelBGIMGPaletteHolder.parentNode.style.display == "block"){
this.oApp.exec("HIDE_TABLE_QE_IMG_PALETTE", []);
}else{
this.oApp.exec("SHOW_TABLE_QE_IMG_PALETTE", []);
}
},
$ON_SHOW_TABLE_QE_IMG_PALETTE : function(){
this.elPanelBGIMGPaletteHolder.parentNode.style.display = "block";
},
$ON_HIDE_TABLE_QE_IMG_PALETTE : function(){
this.elPanelBGIMGPaletteHolder.parentNode.style.display = "none";
},
$ON_TABLE_QE_SET_IMG_FROM_PALETTE : function(elEvt){
this.oApp.exec("TABLE_QE_SET_IMG", [elEvt.element]);
if(this.oSelection){
this.oSelection.select();
}
this._changeTableEditorStatus(this.STATUS.S_0);
},
$ON_TABLE_QE_SET_IMG : function(elSelected){
var sClassName = jindo.$Element(elSelected).className();
var welBtnBGIMGPalette = jindo.$Element(this.elBtnBGIMGPalette);
var aBtnClassNames = welBtnBGIMGPalette.className().split(" ");
for(var i = 0, nLen = aBtnClassNames.length; i < nLen; i++){
if(aBtnClassNames[i].indexOf("cellimg") > 0){
welBtnBGIMGPalette.removeClass(aBtnClassNames[i]);
}
}
jindo.$Element(this.elBtnBGIMGPalette).addClass(sClassName);
var n = sClassName.substring(11, sClassName.length);
var sImageName = "pattern_";
if(n === "0"){
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
jindo.$Element(this.aSelectedCells[i]).css("backgroundImage", "");
this.aSelectedCells[i].removeAttribute(this.TMP_BGC_ATTR);
this.aSelectedCells[i].removeAttribute(this.TMP_BGIMG_ATTR);
}
}else{
if(n == 19 || n == 20 || n == 21 || n == 22 || n == 25 || n == 26){
sImageName = sImageName + n + ".jpg";
}else{
sImageName = sImageName + n + ".gif";
}
for(var j = 0, nLen = this.aSelectedCells.length; j < nLen ; j++){
jindo.$Element(this.aSelectedCells[j]).css("backgroundImage", "url("+"http://static.se2.naver.com/static/img/"+sImageName+")");
this.aSelectedCells[j].removeAttribute(this.TMP_BGC_ATTR);
this.aSelectedCells[j].setAttribute(this.TMP_BGIMG_ATTR, "url("+"http://static.se2.naver.com/static/img/"+sImageName+")");
}
}
this.sQEAction = "TABLE_SET_BGIMAGE";
},
$ON_SAVE_QE_MY_REVIEW_ITEM : function(){
this.oApp.exec("SAVE_MY_REVIEW_ITEM");
this.oApp.exec("CLOSE_QE_LAYER");
},
$ON_SHOW_COMMON_QE : function(){
if(jindo.$Element(this.elSelectionStartTable).hasClass(this._sSETblClass)){
this.oApp.exec("SHOW_TABLE_QE");
}else{
if(jindo.$Element(this.elSelectionStartTable).hasClass(this._sSEReviewTblClass)){
this.oApp.exec("SHOW_REVIEW_QE");
}
}
},
$ON_SHOW_TABLE_QE : function(){
this.oApp.exec("HIDE_TABLE_QE_BGC_PALETTE", []);
this.oApp.exec("TABLE_QE_HIDE_TEMPLATE", []);
this.oApp.exec("SETUP_TABLE_QE_MODE", [0]);
this.oApp.exec("OPEN_QE_LAYER", [this.htMap[this.htSelectionEPos.x][this.htSelectionEPos.y], this.elQELayer, "table"]);
},
$ON_SHOW_REVIEW_QE : function(){
this.oApp.exec("SETUP_TABLE_QE_MODE", [1]);
this.oApp.exec("OPEN_QE_LAYER", [this.htMap[this.htSelectionEPos.x][this.htSelectionEPos.y], this.elQELayer, "review"]);
},
$ON_CLOSE_SUB_LAYER_QE : function(){
if(typeof this.elPanelBGPaletteHolder != 'undefined'){
this.elPanelBGPaletteHolder.parentNode.style.display = "none";
}
if(typeof this.elPanelBGIMGPaletteHolder != 'undefined'){
this.elPanelBGIMGPaletteHolder.parentNode.style.display = "none";
}
},
$ON_SETUP_TABLE_QE_MODE : function(nMode){
var bEnableMerge = true;
if(typeof nMode == "number"){
this.nQEMode = nMode;
}
if(this.aSelectedCells.length < 2){
bEnableMerge = false;
}
this.oApp.exec("TABLE_QE_DIM", [this.QE_DIM_MERGE_BTN, bEnableMerge]);
var sBackgroundColor = this.aSelectedCells[0].getAttribute(this.TMP_BGC_ATTR) || "rgb(255,255,255)";
var bAllMatched = true;
for(var i = 1, nLen = this.aSelectedCells.length; i < nLen; i++){
if(this.aSelectedCells[i]){
if(sBackgroundColor != this.aSelectedCells[i].getAttribute(this.TMP_BGC_ATTR)){
bAllMatched = false;
break;
}
}
}
if(bAllMatched){
this.elBtnBGPalette.style.backgroundColor = sBackgroundColor;
}else{
this.elBtnBGPalette.style.backgroundColor = "#FFFFFF";
}
var sBackgroundImage = this.aSelectedCells[0].getAttribute(this.TMP_BGIMG_ATTR) || "";
var bAllMatchedImage = true;
var sPatternInfo, nPatternImage = 0;
var welBtnBGIMGPalette = jindo.$Element(this.elBtnBGIMGPalette);
if(!!sBackgroundImage){
var aPattern = sBackgroundImage.match(/\_[0-9]*/);
sPatternInfo = (!!aPattern)?aPattern[0] : "_0";
nPatternImage = sPatternInfo.substring(1, sPatternInfo.length);
for(var i = 1, nLen = this.aSelectedCells.length; i < nLen; i++){
if(sBackgroundImage != this.aSelectedCells[i].getAttribute(this.TMP_BGIMG_ATTR)){
bAllMatchedImage = false;
break;
}
}
}
var aBtnClassNames = welBtnBGIMGPalette.className().split(/\s/);
for(var j = 0, nLen = aBtnClassNames.length; j < nLen; j++){
if(aBtnClassNames[j].indexOf("cellimg") > 0){
welBtnBGIMGPalette.removeClass(aBtnClassNames[j]);
}
}
if(bAllMatchedImage && nPatternImage > 0){
welBtnBGIMGPalette.addClass("se2_cellimg" + nPatternImage);
}else{
welBtnBGIMGPalette.addClass("se2_cellimg0");
}
if(this.nQEMode === 0){	
this.elPanelTableTemplateArea.style.display = "block";
this.elPanelReviewBGArea.style.display = "none";
jindo.$Element(this.elPanelTableBGArea).className("se2_qe2");
var nTpl = this.parseIntOr0(this.elSelectionStartTable.getAttribute(this.ATTR_TBL_TEMPLATE));
if(nTpl){
}else{
this.elInputRadioBGColor.checked = "true";
nTpl = 1;
}
this.elPanelQETemplatePreview.className = "se2_t_style" + nTpl;
this.elPanelBGImg.style.position = "";
}else if(this.nQEMode == 1){
this.elPanelTableTemplateArea.style.display = "none";
this.elPanelReviewBGArea.style.display = "block";
var nTpl = this.parseIntOr0(this.elSelectionStartTable.getAttribute(this.ATTR_REVIEW_TEMPLATE));
this.elPanelBGImg.style.position = "relative";
}else{
this.elPanelTableTemplateArea.style.display = "none";
this.elPanelReviewBGArea.style.display = "none";
}
this.oApp.exec("DRAW_QE_RADIO_OPTION", [0]);
},
$ON_DRAW_QE_RADIO_OPTION : function(nClickedIdx){
if(nClickedIdx !== 0 && nClickedIdx != 2){
this.oApp.exec("HIDE_TABLE_QE_BGC_PALETTE", []);
}
if(nClickedIdx !== 0 && nClickedIdx != 3){
this.oApp.exec("HIDE_TABLE_QE_IMG_PALETTE", []);
}
if(nClickedIdx !== 0 && nClickedIdx != 4){
this.oApp.exec("TABLE_QE_HIDE_TEMPLATE", []);
}
if(this.nQEMode === 0){
if(this.elInputRadioBGImg.checked){
this.elInputRadioBGColor.checked = "true";
}
if(this.elInputRadioBGColor.checked){
this.oApp.exec("TABLE_QE_DIM", [this.QE_DIM_TABLE_TEMPLATE, false]);
}else{
this.oApp.exec("TABLE_QE_DIM", [this.QE_DIM_BG_COLOR, false]);
}
}else{
if(this.elInputRadioTemplate.checked){
this.elInputRadioBGColor.checked = "true";
}
if(this.elInputRadioBGColor.checked){
this.oApp.exec("TABLE_QE_DIM", [this.QE_DIM_REVIEW_BG_IMG, false]);
}else{
this.oApp.exec("TABLE_QE_DIM", [this.QE_DIM_BG_COLOR, false]);
}
}
},
$ON_TABLE_QE_DIM : function(nPart, bUndim){
var elPanelDim;
var sDimClassPrefix = "se2_qdim";
if(nPart == 1){
elPanelDim = this.elPanelDim1;
}else{
elPanelDim = this.elPanelDim2;
}
if(bUndim){
nPart = 0;
}
elPanelDim.className = sDimClassPrefix + nPart;
},
$ON_TE_SELECT_TABLE : function(elTable){
this.elSelectionStartTable = elTable;
this.htMap = this._getCellMapping(this.elSelectionStartTable);
},
$ON_TE_SELECT_CELLS : function(htSPos, htEPos){
this._selectCells(htSPos, htEPos);
},
$ON_TE_MERGE_CELLS : function(){
if(this.aSelectedCells.length === 0 || this.aSelectedCells.length == 1){
return;
}
this._removeClassFromSelection();
var i, elFirstTD, elTD;
elFirstTD = this.aSelectedCells[0];
var elTable = nhn.husky.SE2M_Utils.findAncestorByTagName("TABLE", elFirstTD);
var nHeight, nWidth;
var elCurTD, elLastTD = this.aSelectedCells[0];
nHeight = parseInt(elLastTD.style.height || elLastTD.getAttribute("height"), 10);
nWidth = parseInt(elLastTD.style.width || elLastTD.getAttribute("width"), 10);
for(i = this.htSelectionSPos.x + 1; i < this.htSelectionEPos.x + 1; i++){
curTD = this.htMap[i][this.htSelectionSPos.y];
if(curTD == elLastTD){
continue;
}
elLastTD = curTD;
nWidth += parseInt(curTD.style.width || curTD.getAttribute("width"), 10);
}
elLastTD = this.aSelectedCells[0];
for(i = this.htSelectionSPos.y + 1; i < this.htSelectionEPos.y + 1; i++){
curTD = this.htMap[this.htSelectionSPos.x][i];
if(curTD == elLastTD){
continue;
}
elLastTD = curTD;
nHeight += parseInt(curTD.style.height || curTD.getAttribute("height"), 10);
}
if(nWidth){
elFirstTD.style.width = nWidth + "px";
}
if(nHeight){
elFirstTD.style.height = nHeight + "px";
}
elFirstTD.setAttribute("colSpan", this.htSelectionEPos.x - this.htSelectionSPos.x + 1);
elFirstTD.setAttribute("rowSpan", this.htSelectionEPos.y - this.htSelectionSPos.y + 1);
for(i = 1; i < this.aSelectedCells.length; i++){
elTD = this.aSelectedCells[i];
if(elTD.parentNode){
if(!nhn.husky.SE2M_Utils.isBlankNode(elTD)){
elFirstTD.innerHTML += elTD.innerHTML;
}
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion == 9 || htBrowser.nativeVersion == 10) && (htBrowser.version == 9 || htBrowser.version == 10)){
this._removeEmptyTextNode_IE(elTD);
}
elTD.parentNode.removeChild(elTD);
}
}
this.htMap = this._getCellMapping(this.elSelectionStartTable);
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
this._showTableTemplate(this.elSelectionStartTable);
this._addClassToSelection();
this.sQEAction = "TABLE_CELL_MERGE";
this.oApp.exec("SHOW_COMMON_QE");
},
$ON_TABLE_QE_TOGGLE_TEMPLATE : function(){
if(this.elPanelQETemplate.style.display == "block"){
this.oApp.exec("TABLE_QE_HIDE_TEMPLATE");
}else{
this.oApp.exec("TABLE_QE_SHOW_TEMPLATE");
}
},
$ON_TABLE_QE_SHOW_TEMPLATE : function(){
this.elPanelQETemplate.style.display = "block";
this.oApp.exec("POSITION_TOOLBAR_LAYER", [this.elPanelQETemplate]);
},
$ON_TABLE_QE_HIDE_TEMPLATE : function(){
this.elPanelQETemplate.style.display = "none";
},
$ON_STYLE_TABLE : function(elTable, nTableStyleIdx){
if(!elTable){
if(!this._t){
this._t = 1;
}
elTable = this.elSelectionStartTable;
nTableStyleIdx = (this._t++) % 20 + 1;
}
if(this.oSelection){
this.oSelection.select();
}
this._applyTableTemplate(elTable, nTableStyleIdx);
},
$ON_TE_DELETE_COLUMN : function(){
if(this.aSelectedCells.length === 0) {
return;
}
this._selectAll_Column();
this._deleteSelectedCells();
this.sQEAction = "DELETE_TABLE_COLUMN";
this._changeTableEditorStatus(this.STATUS.S_0);
},
$ON_TE_DELETE_ROW : function(){
if(this.aSelectedCells.length === 0) {
return;
}
this._selectAll_Row();
this._deleteSelectedCells();
this.sQEAction = "DELETE_TABLE_ROW";
this._changeTableEditorStatus(this.STATUS.S_0);
},
$ON_TE_INSERT_COLUMN_RIGHT : function(){
if(this.aSelectedCells.length === 0) {
return;
}
this._selectAll_Column();
this._insertColumnAfter(this.htSelectionEPos.x);
},
$ON_TE_INSERT_COLUMN_LEFT : function(){
this._selectAll_Column();
this._insertColumnAfter(this.htSelectionSPos.x - 1);
},
$ON_TE_INSERT_ROW_BELOW : function(){
if(this.aSelectedCells.length === 0) {
return;
}
this._insertRowBelow(this.htSelectionEPos.y);
},
$ON_TE_INSERT_ROW_ABOVE : function(){
this._insertRowBelow(this.htSelectionSPos.y - 1);
},
$ON_TE_SPLIT_COLUMN : function(){
var nSpan, nNewSpan, nWidth, nNewWidth;
var elCurCell, elNewTD;
if(this.aSelectedCells.length === 0) {
return;
}
this._removeClassFromSelection();
var elLastCell = this.aSelectedCells[0];
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
elCurCell = this.aSelectedCells[i];
nSpan = parseInt(elCurCell.getAttribute("colSpan"), 10) || 1;
if(nSpan > 1){
continue;
}
var htPos = this._getBasisCellPosition(elCurCell);
for(var y = 0; y < this.htMap[0].length;){
elCurCell = this.htMap[htPos.x][y];
nSpan = parseInt(elCurCell.getAttribute("colSpan"), 10) || 1;
elCurCell.setAttribute("colSpan", nSpan+1);
y += parseInt(elCurCell.getAttribute("rowSpan"), 10) || 1;
}
}
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
elCurCell = this.aSelectedCells[i];
nSpan = parseInt(elCurCell.getAttribute("colSpan"), 10) || 1;
nNewSpan = (nSpan/2).toFixed(0);
elCurCell.setAttribute("colSpan", nNewSpan);
elNewTD = this._shallowCloneTD(elCurCell);
elNewTD.setAttribute("colSpan", nSpan-nNewSpan);
elLastCell = elNewTD;
nSpan = parseInt(elCurCell.getAttribute("rowSpan"), 10) || 1;
elNewTD.setAttribute("rowSpan", nSpan);
elNewTD.innerHTML = "&nbsp;";
nWidth = elCurCell.width || elCurCell.style.width;
if(nWidth){
nWidth = this.parseIntOr0(nWidth);
elCurCell.removeAttribute("width");
nNewWidth = (nWidth/2).toFixed();
elCurCell.style.width = nNewWidth + "px";
elNewTD.style.width = (nWidth - nNewWidth) + "px";
}
elCurCell.parentNode.insertBefore(elNewTD, elCurCell.nextSibling);
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion >= 9 || htBrowser.nativeVersion <= 11) && (htBrowser.version >= 9 || htBrowser.version <= 11)){
elNewTD.style.cssText = elCurCell.style.cssText;
}
}
this._reassignCellSizes(this.elSelectionStartTable);
this.htMap = this._getCellMapping(this.elSelectionStartTable);
var htPos = this._getBasisCellPosition(elLastCell);
this.htSelectionEPos.x = htPos.x;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
this.sQEAction = "SPLIT_TABLE_COLUMN";
this.oApp.exec("SHOW_COMMON_QE");
},
$ON_TE_SPLIT_ROW : function(){
var nSpan, nNewSpan, nHeight, nHeight;
var elCurCell, elNewTD, htPos, elNewTR;
if(this.aSelectedCells.length === 0) {
return;
}
var aTR = jindo.$$(">TBODY>TR", this.elSelectionStartTable, {oneTimeOffCache:true});
this._removeClassFromSelection();
var nNewRows = 0;
var elNextTRInsertionPoint;
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
elCurCell = this.aSelectedCells[i];
nSpan = parseInt(elCurCell.getAttribute("rowSpan"), 10) || 1;
if(nSpan > 1){
continue;
}
htPos = this._getBasisCellPosition(elCurCell);
elNextTRInsertionPoint = aTR[htPos.y];
elNewTR = this.oApp.getWYSIWYGDocument().createElement("TR");
elNextTRInsertionPoint.parentNode.insertBefore(elNewTR, elNextTRInsertionPoint.nextSibling);
nNewRows++;
for(var x = 0; x < this.htMap.length;){
elCurCell = this.htMap[x][htPos.y];
nSpan = parseInt(elCurCell.getAttribute("rowSpan"), 10) || 1;
elCurCell.setAttribute("rowSpan", nSpan + 1);
x += parseInt(elCurCell.getAttribute("colSpan"), 10) || 1;
}
}
aTR = jindo.$$(">TBODY>TR", this.elSelectionStartTable, {oneTimeOffCache:true});
var htPos1, htPos2;
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
elCurCell = this.aSelectedCells[i];
nSpan = parseInt(elCurCell.getAttribute("rowSpan"), 10) || 1;
nNewSpan = (nSpan/2).toFixed(0);
elCurCell.setAttribute("rowSpan", nNewSpan);
elNewTD = this._shallowCloneTD(elCurCell);
elNewTD.setAttribute("rowSpan", nSpan - nNewSpan);
nSpan = parseInt(elCurCell.getAttribute("colSpan"), 10) || 1;
elNewTD.setAttribute("colSpan", nSpan);
elNewTD.innerHTML = "&nbsp;";
nHeight = elCurCell.height || elCurCell.style.height;
if(nHeight){
nHeight = this.parseIntOr0(nHeight);
elCurCell.removeAttribute("height");
nNewHeight = (nHeight/2).toFixed();
elCurCell.style.height = nNewHeight + "px";
elNewTD.style.height = (nHeight - nNewHeight) + "px";
}
var nTRIdx = jindo.$A(aTR).indexOf(elCurCell.parentNode);
var nNextTRIdx = parseInt(nTRIdx, 10)+parseInt(nNewSpan, 10);
var elTRInsertTo = aTR[nNextTRIdx];
var oSiblingTDs = elTRInsertTo.childNodes;
var elInsertionPt = null;
var tmp;
htPos1 = this._getBasisCellPosition(elCurCell);
for(var ii = 0, nNumTDs = oSiblingTDs.length; ii < nNumTDs; ii++){
tmp = oSiblingTDs[ii];
if(!tmp.tagName || !this._rxCellNames.test(tmp.tagName)){
continue;
}
htPos2 = this._getBasisCellPosition(tmp);
if(htPos1.x < htPos2.x){
elInsertionPt = tmp;
break;
}
}
elTRInsertTo.insertBefore(elNewTD, elInsertionPt);
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion >= 9 || htBrowser.nativeVersion <= 11) && (htBrowser.version >= 9 || htBrowser.version <= 11)){
elNewTD.style.cssText = elNewTD.style.cssText;
}
}
this._reassignCellSizes(this.elSelectionStartTable);
this.htMap = this._getCellMapping(this.elSelectionStartTable);
this.htSelectionEPos.y += nNewRows;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
this.sQEAction = "SPLIT_TABLE_ROW";
this.oApp.exec("SHOW_COMMON_QE");
},
$ON_MSG_CELL_SELECTED : function(){
this.elPanelDimDelCol.className = "se2_qdim6r";
this.elPanelDimDelRow.className = "se2_qdim6c";
if(this.htSelectionSPos.x === 0 && this.htSelectionEPos.x === this.htMap.length - 1){
this.oApp.exec("MSG_ROW_SELECTED");
}
if(this.htSelectionSPos.y === 0 && this.htSelectionEPos.y === this.htMap[0].length - 1){
this.oApp.exec("MSG_COL_SELECTED");
}
this.oApp.exec("SHOW_COMMON_QE");
},
$ON_MSG_ROW_SELECTED : function(){
this.elPanelDimDelRow.className = "";
},
$ON_MSG_COL_SELECTED : function(){
this.elPanelDimDelCol.className = "";
},
$ON_EVENT_EDITING_AREA_MOUSEDOWN : function(wevE){
if(!this.oApp.isWYSIWYGEnabled()){
return;
}
switch(this.nStatus){
case this.STATUS.S_0:
if(!wevE.element){return;}
if(wevE.element.tagName == "IMG"){return;}
if(this.oApp.getEditingMode() !== "WYSIWYG"){return;}
var elTD = nhn.husky.SE2M_Utils.findClosestAncestorAmongTagNames(this._aCellName, wevE.element);
if(elTD && this._rxCellNames.test(elTD.tagName)){
var elTBL = nhn.husky.SE2M_Utils.findAncestorByTagName("TABLE", elTD);
if(!jindo.$Element(elTBL).hasClass(this._sSETblClass) && !jindo.$Element(elTBL).hasClass(this._sSEReviewTblClass)){return;}
if(!this._isValidTable(elTBL)){
jindo.$Element(elTBL).removeClass(this._sSETblClass);
jindo.$Element(elTBL).removeClass(this._sSEReviewTblClass);
return;
}
if(elTBL){
this.elSelectionStartTD = elTD;
this.elSelectionStartTable = elTBL;
this._changeTableEditorStatus(this.STATUS.MOUSEDOWN_CELL);
}
}
break;
case this.STATUS.MOUSEDOWN_CELL:
break;
case this.STATUS.CELL_SELECTING:
break;
case this.STATUS.CELL_SELECTED:
this._changeTableEditorStatus(this.STATUS.S_0);
break;
}
},
$ON_EVENT_EDITING_AREA_MOUSEMOVE : function(wevE){
if(this.oApp.getEditingMode() != "WYSIWYG"){return;}
switch(this.nStatus){
case this.STATUS.S_0:
if(this._isOnBorder(wevE)){
this._showCellResizeGrip(wevE);
}else{
this._hideResizer();
}
break;
case this.STATUS.MOUSEDOWN_CELL:
var elTD = nhn.husky.SE2M_Utils.findClosestAncestorAmongTagNames(this._aCellName, wevE.element);
if((elTD && elTD !== this.elSelectionStartTD) || !elTD){
if(!elTD){elTD = this.elSelectionStartTD;}
this._reassignCellSizes(this.elSelectionStartTable);
this._startCellSelection();
this._selectBetweenCells(this.elSelectionStartTD, elTD);
}
break;
case this.STATUS.CELL_SELECTING:
var elTD = nhn.husky.SE2M_Utils.findClosestAncestorAmongTagNames(this._aCellName, wevE.element);
if(!elTD || elTD === this.elLastSelectedTD){return;}
var elTBL = nhn.husky.SE2M_Utils.findAncestorByTagName("TABLE", elTD);
if(elTBL !== this.elSelectionStartTable){return;}
this.elLastSelectedTD = elTD;
this._selectBetweenCells(this.elSelectionStartTD, elTD);
break;
case this.STATUS.CELL_SELECTED:
break;
}
},
$ON_EVENT_OUTER_DOC_MOUSEMOVE : function(wevE){
switch(this.nStatus){
case this.STATUS.CELL_SELECTING:
var htPos = wevE.pos();
var nYPos = htPos.pageY;
var nXPos = htPos.pageX;
if(nYPos < this.htEditingAreaPos.top){
var y = this.htSelectionSPos.y;
if(y > 0){
this.htSelectionSPos.y--;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
var oSelection = this.oApp.getSelection();
oSelection.selectNodeContents(this.aSelectedCells[0]);
oSelection.select();
oSelection.oBrowserSelection.selectNone();
}
}else{
if(nYPos > this.htEditingAreaPos.bottom){
var y = this.htSelectionEPos.y;
if(y < this.htMap[0].length - 1){
this.htSelectionEPos.y++;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
var oSelection = this.oApp.getSelection();
oSelection.selectNodeContents(this.htMap[this.htSelectionEPos.x][this.htSelectionEPos.y]);
oSelection.select();
oSelection.oBrowserSelection.selectNone();
}
}
}
if(nXPos < this.htEditingAreaPos.left){
var x = this.htSelectionSPos.x;
if(x > 0){
this.htSelectionSPos.x--;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
var oSelection = this.oApp.getSelection();
oSelection.selectNodeContents(this.aSelectedCells[0]);
oSelection.select();
oSelection.oBrowserSelection.selectNone();
}
}else{
if(nXPos > this.htEditingAreaPos.right){
var x = this.htSelectionEPos.x;
if(x < this.htMap.length - 1){
this.htSelectionEPos.x++;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
var oSelection = this.oApp.getSelection();
oSelection.selectNodeContents(this.htMap[this.htSelectionEPos.x][this.htSelectionEPos.y]);
oSelection.select();
oSelection.oBrowserSelection.selectNone();
}
}
}
break;
}
},
$ON_EVENT_OUTER_DOC_MOUSEUP : function(wevE){
this._eventEditingAreaMouseup(wevE);
},
$ON_EVENT_EDITING_AREA_MOUSEUP : function(wevE){
this._eventEditingAreaMouseup(wevE);
},
_eventEditingAreaMouseup : function(wevE){
if(this.oApp.getEditingMode() != "WYSIWYG"){return;}
switch(this.nStatus){
case this.STATUS.S_0:
break;
case this.STATUS.MOUSEDOWN_CELL:
this._changeTableEditorStatus(this.STATUS.S_0);
break;
case this.STATUS.CELL_SELECTING:
this._changeTableEditorStatus(this.STATUS.CELL_SELECTED);
break;
case this.STATUS.CELL_SELECTED:
break;
}
},
$ON_GET_SELECTED_CELLS : function(sAttr,oReturn){
if(!!this.aSelectedCells){
oReturn[sAttr] = this.aSelectedCells;
}
},
_coverResizeLayer : function(){
this.elResizeCover.style.position = "fixed";
var size = jindo.$Document().clientSize();
this.elResizeCover.style.width = size.width - this.nPageLeftRightMargin + "px";
this.elResizeCover.style.height = size.height - this.nPageTopBottomMargin + "px";
document.body.appendChild(this.elResizeCover);
},
_uncoverResizeLayer : function(){
this.elResizeGrid.appendChild(this.elResizeCover);
this.elResizeCover.style.position = "";
this.elResizeCover.style.width = "100%";
this.elResizeCover.style.height = "100%";
},
_reassignCellSizes : function(elTable){
var allCells = new Array(2);
allCells[0] = jindo.$$(">TBODY>TR>TD", elTable, {oneTimeOffCache:true});
allCells[1] = jindo.$$(">TBODY>TR>TH", elTable, {oneTimeOffCache:true});
var aAllCellsWithSizeInfo = new Array(allCells[0].length + allCells[1].length);
var numCells = 0;
var nTblBorderPadding = this.parseIntOr0(elTable.border);
var nTblCellPadding = this.parseIntOr0(elTable.cellPadding);
for(var n = 0; n < 2; n++){
for(var i = 0; i < allCells[n].length; i++){
var elCell = allCells[n][i];
var welCell = jindo.$Element(elCell);
var htBrowser = jindo.$Agent().navigator();
var nPaddingLeft, nPaddingRight, nPaddingTop, nPaddingBottom;
var nBorderLeft, nBorderRight, nBorderTop, nBorderBottom;
var nOffsetWidth, nOffsetHeight;
if(elCell.getComputedStyle){
nPaddingLeft = parseFloat(getComputedStyle(elCell).paddingLeft, 10);
nPaddingRight = parseFloat(getComputedStyle(elCell).paddingRight, 10);
nPaddingTop = parseFloat(getComputedStyle(elCell).paddingTop, 10);
nPaddingBottom = parseFloat(getComputedStyle(elCell).paddingBottom, 10);
nBorderLeft = parseFloat(getComputedStyle(elCell).borderLeftWidth, 10);
nBorderRight = parseFloat(getComputedStyle(elCell).borderRightWidth, 10);
nBorderTop = parseFloat(getComputedStyle(elCell).borderTopWidth, 10);
nBorderBottom = parseFloat(getComputedStyle(elCell).borderBottomWidth, 10);
}else{
nPaddingLeft = this.parseIntOr0(elCell.style.paddingLeft);
nPaddingRight = this.parseIntOr0(elCell.style.paddingRight);
nPaddingTop = this.parseIntOr0(elCell.style.paddingTop);
nPaddingBottom = this.parseIntOr0(elCell.style.paddingBottom);
nBorderLeft = this.parseBorder(welCell.css("borderLeftWidth"), welCell.css("borderLeftStyle"));
nBorderRight = this.parseBorder(welCell.css("borderRightWidth"), welCell.css("borderRightStyle"));
nBorderTop = this.parseBorder(welCell.css("borderTopWidth"), welCell.css("borderTopStyle"));
nBorderBottom = this.parseBorder(welCell.css("borderBottomWidth"), welCell.css("borderBottomStyle"));
}
var nWidth = jindo.$Element(elCell).attr("width");
var nHeight = jindo.$Element(elCell).attr("height");
if(!nWidth && !nHeight){
nOffsetWidth = elCell.style.width;
nOffsetHeight = elCell.style.height;
}else{
nOffsetWidth = elCell.offsetWidth - (nPaddingLeft + nPaddingRight + nBorderLeft + nBorderRight) + "px";
nOffsetHeight = elCell.offsetHeight - (nPaddingTop + nPaddingBottom + nBorderTop + nBorderBottom) + "px";
}
aAllCellsWithSizeInfo[numCells++] = [elCell, nOffsetWidth, nOffsetHeight];
}
}
for(var i = 0; i < numCells; i++){
var aCellInfo = aAllCellsWithSizeInfo[i];
aCellInfo[0].removeAttribute("width");
aCellInfo[0].removeAttribute("height");
aCellInfo[0].style.width = aCellInfo[1];
aCellInfo[0].style.height = aCellInfo[2];
}
elTable.removeAttribute("width");
elTable.removeAttribute("height");
elTable.style.width = "";
elTable.style.height = "";
},
_mousedown_ResizeCover : function(oEvent){
this.bResizing = true;
this.nStartHeight = oEvent.pos().clientY;
this.bResizingCover = true;
this.wfnMousemove_ResizeCover.attach(this.elResizeCover, "mousemove");
this.wfnMouseup_ResizeCover.attach(document, "mouseup");
this._coverResizeLayer();
this.elResizeGrid.style.border = "1px dotted black";
this.nStartHeight = oEvent.pos().clientY;
this.nStartWidth = oEvent.pos().clientX;
this.nClientXDiff = this.nStartWidth - this.htResizing.htEPos.clientX;
this.nClientYDiff = this.nStartHeight - this.htResizing.htEPos.clientY;
this._reassignCellSizes(this.htResizing.elTable);
this.htMap = this._getCellMapping(this.htResizing.elTable);
var htPosition = this._getBasisCellPosition(this.htResizing.elCell);
var nOffsetX = (parseInt(this.htResizing.elCell.getAttribute("colspan")) || 1) - 1;
var nOffsetY = (parseInt(this.htResizing.elCell.getAttribute("rowspan")) || 1) - 1;
var x = htPosition.x + nOffsetX + this.htResizing.nHA;
var y = htPosition.y + nOffsetY + this.htResizing.nVA;
if(x < 0 || y < 0){return;}
this.htAllAffectedCells = this._getAllAffectedCells(x, y, this.htResizing.nResizeMode, this.htResizing.elTable);
},
_mousemove_ResizeCover : function(oEvent){
if(jindo.$Agent().navigator().chrome || jindo.$Agent().navigator().safari){
if(this.htResizing.nPreviousResizeMode != undefined && this.htResizing.nPreviousResizeMode != 0){
if(this.htResizing.nResizeMode != this.htResizing.nPreviousResizeMode){
this.htResizing.nResizeMode = this.htResizing.nPreviousResizeMode;
this._showResizer();
}
}
}
var nHeightChange = oEvent.pos().clientY - this.nStartHeight;
var nWidthChange = oEvent.pos().clientX - this.nStartWidth;
var oEventPos = oEvent.pos();
if(this.htResizing.nResizeMode == 1){
this.elResizeGrid.style.left = oEvent.pos().clientX - this.nClientXDiff - this.parseIntOr0(this.elResizeGrid.style.width)/2 + "px";
}else{
this.elResizeGrid.style.top = oEvent.pos().clientY - this.nClientYDiff - this.parseIntOr0(this.elResizeGrid.style.height)/2 + "px";
}
},
_mouseup_ResizeCover : function(oEvent){
this.bResizing = false;
this._hideResizer();
this._uncoverResizeLayer();
this.elResizeGrid.style.border = "";
this.wfnMousemove_ResizeCover.detach(this.elResizeCover, "mousemove");
this.wfnMouseup_ResizeCover.detach(document, "mouseup");
var nHeightChange = 0;
var nWidthChange = 0;
if(this.htResizing.nResizeMode == 2){
nHeightChange = oEvent.pos().clientY - this.nStartHeight;
}
if(this.htResizing.nResizeMode == 1){
nWidthChange = oEvent.pos().clientX - this.nStartWidth;
if(this.htAllAffectedCells.nMinBefore != -1 && nWidthChange < -1*this.htAllAffectedCells.nMinBefore){
nWidthChange = -1 * this.htAllAffectedCells.nMinBefore + this.MIN_CELL_WIDTH;
}
if(this.htAllAffectedCells.nMinAfter != -1 && nWidthChange > this.htAllAffectedCells.nMinAfter){
nWidthChange = this.htAllAffectedCells.nMinAfter - this.MIN_CELL_WIDTH;
}
}
var width, height;
var aCellsBefore = this.htAllAffectedCells.aCellsBefore;
for(var i = 0; i < aCellsBefore.length; i++){
var elCell = aCellsBefore[i];
width = 0, height = 0;
width = elCell.style.width;
if(isNaN(parseFloat(width, 10))){
width = 0;
}else{
width = parseFloat(width, 10);
}
width += nWidthChange;
height = elCell.style.height;
if(isNaN(parseFloat(height, 10))){
height = 0;
}else{
height = parseFloat(height, 10);
}
height += nHeightChange;
elCell.style.width = Math.max(width, this.MIN_CELL_WIDTH) + "px";
elCell.style.height = Math.max(height, this.MIN_CELL_HEIGHT) + "px";
}
var aCellsAfter = this.htAllAffectedCells.aCellsAfter;
for(var i = 0; i < aCellsAfter.length; i++){
var elCell = aCellsAfter[i];
width = 0, height = 0;
width = elCell.style.width;
if(isNaN(parseFloat(width, 10))){
width = 0;
}else{
width = parseFloat(width, 10);
}
width -= nWidthChange;
height = elCell.style.height;
if(isNaN(parseFloat(height, 10))){
height = 0;
}else{
height = parseFloat(height, 10);
}
height -= nHeightChange;
elCell.style.width = Math.max(width, this.MIN_CELL_WIDTH) + "px";
elCell.style.height = Math.max(height, this.MIN_CELL_HEIGHT) + "px";
}
this.bResizingCover = false;
},
$ON_CLOSE_QE_LAYER : function(){
this._changeTableEditorStatus(this.STATUS.S_0);
},
_changeTableEditorStatus : function(nNewStatus){
if(this.nStatus == nNewStatus){return;}
this.nStatus = nNewStatus;
switch(nNewStatus){
case this.STATUS.S_0:
if(this.nStatus == this.STATUS.MOUSEDOWN_CELL){
break;
}
this._deselectCells();
if(!!this.sQEAction){
this.oApp.exec("RECORD_UNDO_ACTION", [this.sQEAction, {elSaveTarget:this.elSelectionStartTable, bDontSaveSelection:true}]);
this.sQEAction = "";
}
if(this.oApp.oNavigator["safari"] || this.oApp.oNavigator["chrome"]){
this.oApp.getWYSIWYGDocument().onselectstart = null;
}
this.oApp.exec("ENABLE_WYSIWYG", []);
this.oApp.exec("CLOSE_QE_LAYER");
this.elSelectionStartTable = null;
break;
case this.STATUS.CELL_SELECTING:
if(this.oApp.oNavigator.ie){
document.body.setCapture(false);
}
break;
case this.STATUS.CELL_SELECTED:
this.oApp.delayedExec("MSG_CELL_SELECTED", [], 0);
if(this.oApp.oNavigator.ie){
document.body.releaseCapture();
}
break;
}
this.oApp.exec("TABLE_EDITOR_STATUS_CHANGED", [this.nStatus]);
},
_isOnBorder : function(wevE){
this.htResizing.nResizeMode = 0;
this.htResizing.elCell = wevE.element;
if(!this._rxCellNames.test(wevE.element.tagName)){return false;}
this.htResizing.elTable = nhn.husky.SE2M_Utils.findAncestorByTagName("TABLE", this.htResizing.elCell);
if(!this.htResizing.elTable){return;}
if(!jindo.$Element(this.htResizing.elTable).hasClass(this._sSETblClass) && !jindo.$Element(this.htResizing.elTable).hasClass(this._sSEReviewTblClass)){return;}
this.htResizing.nVA = 0;
this.htResizing.nHA = 0;
this.htResizing.nBorderLeftPos = 0;
this.htResizing.nBorderTopPos = -1;
this.htResizing.htEPos = wevE.pos(true);
this.htResizing.nBorderSize = this.parseIntOr0(this.htResizing.elTable.border);
var nAdjustedDraggableCellEdge1;
var nAdjustedDraggableCellEdge2;
if(jindo.$Agent().navigator().ie || jindo.$Agent().navigator().safari){
nAdjustedDraggableCellEdge1 = this.htResizing.nBorderSize + this.nDraggableCellEdge;
nAdjustedDraggableCellEdge2 = this.nDraggableCellEdge;
}else{
nAdjustedDraggableCellEdge1 = this.nDraggableCellEdge;
nAdjustedDraggableCellEdge2 = this.htResizing.nBorderSize + this.nDraggableCellEdge;
}
var elCellWidth = this.htResizing.elCell.clientWidth,
elCellHeight = this.htResizing.elCell.clientHeight;
nRightBorderCriteria = elCellWidth - this.htResizing.htEPos.offsetX,
nBottomBorderCriteria = elCellHeight - this.htResizing.htEPos.offsetY;
if(this.htResizing.htEPos.offsetY <= nAdjustedDraggableCellEdge1){
if(this.htResizing.elCell.parentNode.previousSibling){
this.htResizing.nVA = -1;
this.htResizing.nResizeMode = 4;
}
}
if(nBottomBorderCriteria <= nAdjustedDraggableCellEdge2){
this.htResizing.nBorderTopPos = this.htResizing.elCell.offsetHeight + nAdjustedDraggableCellEdge1 - 1;
this.htResizing.nResizeMode = 2;
}
if(this.htResizing.htEPos.offsetX <= nAdjustedDraggableCellEdge1){
if(this.htResizing.elTable.scrollLeft != this.htResizing.elCell.offsetLeft){
this.htResizing.nHA = -1;
this.htResizing.nResizeMode = 3;
}
}
if(nRightBorderCriteria <= nAdjustedDraggableCellEdge1){
this.htResizing.nBorderLeftPos = this.htResizing.elCell.offsetWidth + nAdjustedDraggableCellEdge1 - 1;
this.htResizing.nResizeMode = 1;
}
if(jindo.$Agent().navigator().chrome || jindo.$Agent().navigator().safari){
if(!this.htResizing.elPreviousCell){
this.htResizing.elPreviousCell = this.htResizing.elCell;
}else{
if(this.htResizing.elCell != this.htResizing.elPreviousCell){
this.htResizing.elPreviousCell = this.htResizing.elCell;
}
}
}
if(this.htResizing.nResizeMode === 0){return false;}
return true;
},
_showCellResizeGrip : function(){
if(this.htResizing.nResizeMode == 1 || this.htResizing.nResizeMode == 3){
this.elResizeCover.style.cursor = "col-resize";
}else if(this.htResizing.nResizeMode == 2 || this.htResizing.nResizeMode == 4){
this.elResizeCover.style.cursor = "row-resize";
}
this._showResizer();
if(this.htResizing.nResizeMode == 1){
this._setResizerSize((this.htResizing.nBorderSize + this.nDraggableCellEdge) * 2, this.parseIntOr0(jindo.$Element(this.elIFrame).css("height")));
this.elResizeGrid.style.top = "0px";
this.elResizeGrid.style.left = this.htResizing.elCell.clientWidth + this.htResizing.htEPos.clientX - this.htResizing.htEPos.offsetX - this.parseIntOr0(this.elResizeGrid.style.width)/2 + "px";
}else if(this.htResizing.nResizeMode == 2){
var elIFrameWidth = this.oApp.elEditingAreaContainer.offsetWidth + "px";
this._setResizerSize(this.parseIntOr0(elIFrameWidth), (this.htResizing.nBorderSize + this.nDraggableCellEdge) * 2);
this.elResizeGrid.style.top = this.htResizing.elCell.clientHeight + this.htResizing.htEPos.clientY - this.htResizing.htEPos.offsetY - this.parseIntOr0(this.elResizeGrid.style.height)/2 + "px";
this.elResizeGrid.style.left = "0px";
}else if(this.htResizing.nResizeMode == 3){
this._setResizerSize((this.htResizing.nBorderSize + this.nDraggableCellEdge) * 2, this.parseIntOr0(jindo.$Element(this.elIFrame).css("height")));
this.elResizeGrid.style.top = "0px";
this.elResizeGrid.style.left = + this.htResizing.htEPos.clientX - this.htResizing.htEPos.offsetX - this.parseIntOr0(this.elResizeGrid.style.width)/2 + "px";
this.htResizing.nResizeMode = 1;
}else if(this.htResizing.nResizeMode == 4){
var elIFrameWidth = this.oApp.elEditingAreaContainer.offsetWidth + "px";
this._setResizerSize(this.parseIntOr0(elIFrameWidth), (this.htResizing.nBorderSize + this.nDraggableCellEdge) * 2);
this.elResizeGrid.style.top = this.htResizing.htEPos.clientY - this.htResizing.htEPos.offsetY - this.parseIntOr0(this.elResizeGrid.style.height)/2 + "px";
this.elResizeGrid.style.left = "0px";
this.htResizing.nResizeMode = 2;
}
},
_getAllAffectedCells : function(basis_x, basis_y, iResizeMode, oTable){
if(!oTable){return [];}
var oTbl = this._getCellMapping(oTable);
var iTblX = oTbl.length;
var iTblY = oTbl[0].length;
var aCellsBefore = [];
var aCellsAfter = [];
var htResult;
var nMinBefore = -1, nMinAfter = -1;
if(iResizeMode == 1){
for(var y = 0; y < iTblY; y++){
if(aCellsBefore.length>0 && aCellsBefore[aCellsBefore.length-1] == oTbl[basis_x][y]){continue;}
aCellsBefore[aCellsBefore.length] = oTbl[basis_x][y];
var nWidth = parseInt(oTbl[basis_x][y].style.width);
if(nMinBefore == -1 || nMinBefore > nWidth){
nMinBefore = nWidth;
}
}
if(oTbl.length > basis_x+1){
for(var y = 0; y < iTblY; y++){
if(aCellsAfter.length>0 && aCellsAfter[aCellsAfter.length-1] == oTbl[basis_x+1][y]){continue;}
aCellsAfter[aCellsAfter.length] = oTbl[basis_x+1][y];
var nWidth = parseInt(oTbl[basis_x + 1][y].style.width);
if(nMinAfter == -1 || nMinAfter > nWidth){
nMinAfter = nWidth;
}
}
}
htResult = {aCellsBefore: aCellsBefore, aCellsAfter: aCellsAfter, nMinBefore: nMinBefore, nMinAfter: nMinAfter};
}else{
for(var x = 0; x < iTblX; x++){
if(aCellsBefore.length>0 && aCellsBefore[aCellsBefore.length - 1] == oTbl[x][basis_y]){continue;}
aCellsBefore[aCellsBefore.length] = oTbl[x][basis_y];
if(nMinBefore == -1 || nMinBefore > oTbl[x][basis_y].style.height){
nMinBefore = oTbl[x][basis_y].style.height;
}
}
htResult = {aCellsBefore: aCellsBefore, aCellsAfter: aCellsAfter, nMinBefore: nMinBefore, nMinAfter: nMinAfter};
}
return htResult;
},
_createCellResizeGrip : function(){
this.elTmp = document.createElement("DIV");
try{
this.elTmp.innerHTML = '<div style="position:absolute; overflow:hidden; z-index: 99; "><div onmousedown="return false" style="background-color:#000000;filter:alpha(opacity=0);opacity:0.0;-moz-opacity:0.0;-khtml-opacity:0.0;cursor: col-resize; left: 0px; top: 0px; width: 100%; height: 100%;font-size:1px;z-index: 999; "></div></div>';
this.elResizeGrid = this.elTmp.firstChild;
this.elResizeCover = this.elResizeGrid.firstChild;
}catch(e){}
var oContainer = jindo.$$.getSingle(".husky_seditor_editing_area_container");
oContainer.appendChild(this.elResizeGrid);
},
_selectAll_Row : function(){
this.htSelectionSPos.x = 0;
this.htSelectionEPos.x = this.htMap.length - 1;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
},
_selectAll_Column : function(){
this.htSelectionSPos.y = 0;
this.htSelectionEPos.y = this.htMap[0].length - 1;
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
},
_deleteSelectedCells : function(){
var elTmp;
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
elTmp = this.aSelectedCells[i];
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion == 9 || htBrowser.nativeVersion == 10) && (htBrowser.version == 9 || htBrowser.version == 10)){
this._removeEmptyTextNode_IE(elTmp);
}
elTmp.parentNode.removeChild(elTmp);
}
var aTR = jindo.$$(">TBODY>TR", this.elSelectionStartTable, {oneTimeOffCache:true});
var nSelectionWidth = this.htSelectionEPos.x - this.htSelectionSPos.x + 1;
var nWidth = this.htMap.length;
if(nSelectionWidth == nWidth){
for(var i = 0, nLen = aTR.length; i < nLen; i++){
elTmp = aTR[i];
if(!this.htMap[0][i] || !this.htMap[0][i].parentNode || this.htMap[0][i].parentNode.tagName !== "TR"){
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion == 9 || htBrowser.nativeVersion == 10) && (htBrowser.version == 9 || htBrowser.version == 10)){
this._removeEmptyTextNode_IE(elTmp);
}
elTmp.parentNode.removeChild(elTmp);
}
}
aTR = jindo.$$(">TBODY>TR", this.elSelectionStartTable, {oneTimeOffCache:true});
}
if(aTR.length < 1){
this.elSelectionStartTable.parentNode.removeChild(this.elSelectionStartTable);
}
this._updateSelection();
},
_insertColumnAfter : function(){
this._removeClassFromSelection();
this._hideTableTemplate(this.elSelectionStartTable);
var aTR = jindo.$$(">TBODY>TR", this.elSelectionStartTable, {oneTimeOffCache:true});
var sInserted;
var sTmpAttr_Inserted = "_tmp_inserted";
var elCell, elCellClone, elCurTR, elInsertionPt;
var htBrowser = jindo.$Agent().navigator();
for(var y = 0, nYLen = this.htMap[0].length; y < nYLen; y++){
elCurTR = aTR[y];
for(var x = this.htSelectionEPos.x; x >= this.htSelectionSPos.x; x--){
elCell = this.htMap[x][y];
elCellClone = this._shallowCloneTD(elCell);
var nSpan = parseInt(elCell.getAttribute("rowSpan"));
if(nSpan > 1){
elCellClone.setAttribute("rowSpan", 1);
elCellClone.style.height = "";
}
nSpan = parseInt(elCell.getAttribute("colSpan"));
if(nSpan > 1){
elCellClone.setAttribute("colSpan", 1);
elCellClone.style.width = "";
}
elInsertionPt = null;
for(var xx = this.htSelectionEPos.x; xx >= this.htSelectionSPos.x; xx--){
if(this.htMap[xx][y].parentNode == elCurTR){
elInsertionPt = this.htMap[xx][y].nextSibling;
break;
}
}
elCurTR.insertBefore(elCellClone, elInsertionPt);
if(htBrowser.ie && (htBrowser.nativeVersion >= 9 || htBrowser.nativeVersion <= 11) && (htBrowser.version >= 9 || htBrowser.version <= 11)){
elCellClone.style.cssText = elCellClone.style.cssText;
}
}
}
for(var i = 0, nLen = this.aSelectedCells.length; i < nLen; i++){
this.aSelectedCells[i].removeAttribute(sTmpAttr_Inserted);
}
var nSelectionWidth = this.htSelectionEPos.x - this.htSelectionSPos.x + 1;
var nSelectionHeight = this.htSelectionEPos.y - this.htSelectionSPos.y + 1;
this.htSelectionSPos.x += nSelectionWidth;
this.htSelectionEPos.x += nSelectionWidth;
this.htMap = this._getCellMapping(this.elSelectionStartTable);
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
this._showTableTemplate(this.elSelectionStartTable);
this._addClassToSelection();
this.sQEAction = "INSERT_TABLE_COLUMN";
this.oApp.exec("SHOW_COMMON_QE");
},
_insertRowBelow : function(){
this._selectAll_Row();
this._removeClassFromSelection();
this._hideTableTemplate(this.elSelectionStartTable);
var elRowClone;
var elTBody = this.htMap[0][0].parentNode.parentNode;
var aTRs = jindo.$$(">TR", elTBody, {oneTimeOffCache:true});
var elInsertionPt = aTRs[this.htSelectionEPos.y + 1] || null;
var htBrowser = jindo.$Agent().navigator();
for(var y = this.htSelectionSPos.y; y <= this.htSelectionEPos.y; y++){
elRowClone = this._getTRCloneWithAllTD(y);
elTBody.insertBefore(elRowClone, elInsertionPt);
if(htBrowser.ie && (htBrowser.nativeVersion >= 9 || htBrowser.nativeVersion <= 11) && (htBrowser.version >= 9 || htBrowser.version <= 11)){
var elPreviousSiblingParent = this.htMap[0][y].parentNode;
var aOriginalPreviousSibling = elPreviousSiblingParent.childNodes;
var aPreviousSibling = [];
for(var i = 0, len = aOriginalPreviousSibling.length; i < len; i++){
if(this._rxCellNames.test(aOriginalPreviousSibling[i].nodeName)){
aPreviousSibling.push(aOriginalPreviousSibling[i].cloneNode());
}
}
var aCellClone = elRowClone.childNodes;
for(var i = 0, len = aCellClone.length; i < len; i++){
var elCloneTD = aCellClone[i];
var elPreviousTD = aPreviousSibling[i];
if(this._rxCellNames.test(elCloneTD.nodeName) && elPreviousTD && this._rxCellNames.test(elPreviousTD.nodeName)){
elCloneTD.style.cssText = elPreviousTD.style.cssText;
}
}
}
}
var nSelectionWidth = this.htSelectionEPos.x - this.htSelectionSPos.x + 1;
var nSelectionHeight = this.htSelectionEPos.y - this.htSelectionSPos.y + 1;
this.htSelectionSPos.y += nSelectionHeight;
this.htSelectionEPos.y += nSelectionHeight;
this.htMap = this._getCellMapping(this.elSelectionStartTable);
this._selectCells(this.htSelectionSPos, this.htSelectionEPos);
this._showTableTemplate(this.elSelectionStartTable);
this._addClassToSelection();
this.sQEAction = "INSERT_TABLE_ROW";
this.oApp.exec("SHOW_COMMON_QE");
},
_updateSelection : function(){
this.aSelectedCells = jindo.$A(this.aSelectedCells).filter(function(v){return (v.parentNode!==null && v.parentNode.parentNode!==null);}).$value();
},
_startCellSelection : function(){
this.htMap = this._getCellMapping(this.elSelectionStartTable);
this.oApp.getEmptySelection().oBrowserSelection.selectNone();
if(this.oApp.oNavigator["safari"] || this.oApp.oNavigator["chrome"]){
this.oApp.getWYSIWYGDocument().onselectstart = function(){return false;};
}
var elIFrame = this.oApp.getWYSIWYGWindow().frameElement;
this.htEditingAreaPos = jindo.$Element(elIFrame).offset();
this.htEditingAreaPos.height = elIFrame.offsetHeight;
this.htEditingAreaPos.bottom = this.htEditingAreaPos.top + this.htEditingAreaPos.height;
this.htEditingAreaPos.width = elIFrame.offsetWidth;
this.htEditingAreaPos.right = this.htEditingAreaPos.left + this.htEditingAreaPos.width;
this._changeTableEditorStatus(this.STATUS.CELL_SELECTING);
},
_selectBetweenCells : function(elCell1, elCell2){
this._deselectCells();
var oP1 = this._getBasisCellPosition(elCell1);
var oP2 = this._getBasisCellPosition(elCell2);
this._setEndPos(oP1);
this._setEndPos(oP2);
var oStartPos = {}, oEndPos = {};
oStartPos.x = Math.min(oP1.x, oP1.ex, oP2.x, oP2.ex);
oStartPos.y = Math.min(oP1.y, oP1.ey, oP2.y, oP2.ey);
oEndPos.x = Math.max(oP1.x, oP1.ex, oP2.x, oP2.ex);
oEndPos.y = Math.max(oP1.y, oP1.ey, oP2.y, oP2.ey);
this._selectCells(oStartPos, oEndPos);
},
_getNextCell : function(elCell){
while(elCell){
elCell = elCell.nextSibling;
if(elCell && elCell.tagName && elCell.tagName.match(/^TD|TH$/)){return elCell;}
}
return null;
},
_getCellMapping : function(elTable){
var aTR = jindo.$$(">TBODY>TR", elTable, {oneTimeOffCache:true});
var nTD = 0;
var aTD_FirstRow = aTR[0].childNodes;
for(var i = 0; i < aTD_FirstRow.length; i++){
var elTmp = aTD_FirstRow[i];
if(!elTmp.tagName || !elTmp.tagName.match(/^TD|TH$/)){continue;}
if(elTmp.getAttribute("colSpan")){
nTD += this.parseIntOr0(elTmp.getAttribute("colSpan"));
}else{
nTD ++;
}
}
var nTblX = nTD;
var nTblY = aTR.length;
var aCellMapping = new Array(nTblX);
for(var x = 0; x < nTblX; x++){
aCellMapping[x] = new Array(nTblY);
}
for(var y = 0; y < nTblY; y++){
var elCell = aTR[y].childNodes[0];
if(!elCell){continue;}
if(!elCell.tagName || !elCell.tagName.match(/^TD|TH$/)){elCell = this._getNextCell(elCell);}
var x = -1;
while(elCell){
x++;
if(!aCellMapping[x]){aCellMapping[x] = [];}
if(aCellMapping[x][y]){continue;}
var colSpan = parseInt(elCell.getAttribute("colSpan"), 10) || 1;
var rowSpan = parseInt(elCell.getAttribute("rowSpan"), 10) || 1;
for(var yy = 0; yy < rowSpan; yy++){
for(var xx = 0; xx < colSpan; xx++){
if(!aCellMapping[x+xx]){
aCellMapping[x+xx] = [];
}
aCellMapping[x+xx][y+yy] = elCell;
}
}
elCell = this._getNextCell(elCell);
}
}
var bRowRemoved = false;
var elLastCell = null;
for(var y = 0, nRealY = 0, nYLen = aCellMapping[0].length; y < nYLen; y++, nRealY++){
elLastCell = null;
if(!aTR[y].innerHTML.match(/TD|TH/i)){
for(var x = 0, nXLen = aCellMapping.length; x < nXLen; x++){
elCell = aCellMapping[x][y];
if(!elCell || elCell === elLastCell){
continue;
}
elLastCell = elCell;
var rowSpan = parseInt(elCell.getAttribute("rowSpan"), 10) || 1;
if(rowSpan > 1){
elCell.setAttribute("rowSpan", rowSpan - 1);
}
}
var htBrowser = jindo.$Agent().navigator();
if(htBrowser.ie && (htBrowser.nativeVersion == 9 || htBrowser.nativeVersion == 10) && (htBrowser.version == 9 || htBrowser.version == 10)){
this._removeEmptyTextNode_IE(aTR[y]);
}
aTR[y].parentNode.removeChild(aTR[y]);
if(this.htSelectionEPos.y >= nRealY){
nRealY--;
this.htSelectionEPos.y--;
}
bRowRemoved = true;
}
}
if(bRowRemoved){
return this._getCellMapping(elTable);
}
return aCellMapping;
},
_selectCells : function(htSPos, htEPos){
this.aSelectedCells = this._getSelectedCells(htSPos, htEPos);
this._addClassToSelection();
},
_deselectCells : function(){
this._removeClassFromSelection();
this.aSelectedCells = [];
this.htSelectionSPos = {x:-1, y:-1};
this.htSelectionEPos = {x:-1, y:-1};
},
_addClassToSelection : function(){
var welCell, elCell;
for(var i = 0; i < this.aSelectedCells.length; i++){
elCell = this.aSelectedCells[i];
if(elCell){
if(elCell.ondragstart == null){
elCell.ondragstart = function(){
return false;
};
}
welCell = jindo.$Element(elCell);
welCell.addClass(this.CELL_SELECTION_CLASS);
welCell.addClass("undraggable");
if(elCell.style.backgroundColor){
elCell.setAttribute(this.TMP_BGC_ATTR, elCell.style.backgroundColor);
welCell.css("backgroundColor", "");
}
if(elCell.style.backgroundImage) {
elCell.setAttribute(this.TMP_BGIMG_ATTR, elCell.style.backgroundImage);
welCell.css("backgroundImage", "");
}
}
}
},
_removeClassFromSelection : function(){
var welCell, elCell;
for(var i = 0; i < this.aSelectedCells.length; i++){
elCell = this.aSelectedCells[i];
if(elCell){
welCell = jindo.$Element(elCell);
welCell.removeClass(this.CELL_SELECTION_CLASS);
welCell.removeClass("undraggable");
if(elCell.getAttribute(this.TMP_BGC_ATTR)){
elCell.style.backgroundColor = elCell.getAttribute(this.TMP_BGC_ATTR);
elCell.removeAttribute(this.TMP_BGC_ATTR);
}
if(elCell.getAttribute(this.TMP_BGIMG_ATTR)) {
welCell.css("backgroundImage",elCell.getAttribute(this.TMP_BGIMG_ATTR));
elCell.removeAttribute(this.TMP_BGIMG_ATTR);
}
}
}
},
_expandAndSelect : function(htPos1, htPos2){
var x, y, elTD, nTmp, i;
if(htPos1.y > 0){
for(x = htPos1.x; x <= htPos2.x; x++){
elTD = this.htMap[x][htPos1.y];
if(this.htMap[x][htPos1.y - 1] == elTD){
nTmp = htPos1.y - 2;
while(nTmp >= 0 && this.htMap[x][nTmp] == elTD){
nTmp--;
}
htPos1.y = nTmp + 1;
this._expandAndSelect(htPos1, htPos2);
return;
}
}
}
if(htPos1.x > 0){
for(y = htPos1.y; y <= htPos2.y; y++){
elTD = this.htMap[htPos1.x][y];
if(this.htMap[htPos1.x - 1][y] == elTD){
nTmp = htPos1.x - 2;
while(nTmp >= 0 && this.htMap[nTmp][y] == elTD){
nTmp--;
}
htPos1.x = nTmp + 1;
this._expandAndSelect(htPos1, htPos2);
return;
}
}
}
if(htPos2.y < this.htMap[0].length - 1){
for(x = htPos1.x; x <= htPos2.x; x++){
elTD = this.htMap[x][htPos2.y];
if(this.htMap[x][htPos2.y + 1] == elTD){
nTmp = htPos2.y + 2;
while(nTmp < this.htMap[0].length && this.htMap[x][nTmp] == elTD){
nTmp++;
}
htPos2.y = nTmp - 1;
this._expandAndSelect(htPos1, htPos2);
return;
}
}
}
if(htPos2.x < this.htMap.length - 1){
for(y = htPos1.y; y <= htPos2.y; y++){
elTD = this.htMap[htPos2.x][y];
if(this.htMap[htPos2.x + 1][y] == elTD){
nTmp = htPos2.x + 2;
while(nTmp < this.htMap.length && this.htMap[nTmp][y] == elTD){
nTmp++;
}
htPos2.x = nTmp - 1;
this._expandAndSelect(htPos1, htPos2);
return;
}
}
}
},
_getSelectedCells : function(htPos1, htPos2){
this._expandAndSelect(htPos1, htPos2);
var x1 = htPos1.x;
var y1 = htPos1.y;
var x2 = htPos2.x;
var y2 = htPos2.y;
this.htSelectionSPos = htPos1;
this.htSelectionEPos = htPos2;
var aResult = [];
for(var y = y1; y <= y2; y++){
for(var x = x1; x <= x2; x++){
if(jindo.$A(aResult).has(this.htMap[x][y])){
continue;
}
aResult[aResult.length] = this.htMap[x][y];
}
}
return aResult;
},
_setEndPos : function(htPos){
var nColspan, nRowspan;
nColspan = parseInt(htPos.elCell.getAttribute("colSpan"), 10) || 1;
nRowspan = parseInt(htPos.elCell.getAttribute("rowSpan"), 10) || 1;
htPos.ex = htPos.x + nColspan - 1;
htPos.ey = htPos.y + nRowspan - 1;
},
_getBasisCellPosition : function(elCell){
var x = 0, y = 0;
for(x = 0; x < this.htMap.length; x++){
for(y = 0; y < this.htMap[x].length; y++){
if(this.htMap[x][y] == elCell){
return {'x': x, 'y': y, elCell: elCell};
}
}
}
return {'x': 0, 'y': 0, elCell: elCell};
},
_applyTableTemplate : function(elTable, nTemplateIdx){
if (!elTable) {
return;
}
this._clearAllTableStyles(elTable);
this._doApplyTableTemplate(elTable, nhn.husky.SE2M_TableTemplate[nTemplateIdx], false);
elTable.setAttribute(this.ATTR_TBL_TEMPLATE, nTemplateIdx);
},
_clearAllTableStyles : function(elTable){
elTable.removeAttribute("border");
elTable.removeAttribute("cellPadding");
elTable.removeAttribute("cellSpacing");
elTable.style.padding = "";
elTable.style.border = "";
elTable.style.backgroundColor = "";
elTable.style.color = "";
var aTD = jindo.$$(">TBODY>TR>TD", elTable, {oneTimeOffCache:true});
for(var i = 0, nLen = aTD.length; i < nLen; i++){
aTD[i].style.padding = "";
aTD[i].style.border = "";
aTD[i].style.backgroundColor = "";
aTD[i].style.color = "";
}
var aTH = jindo.$$(">TBODY>TR>TH", elTable, {oneTimeOffCache:true});
for(var i = 0, nLen = aTH.length; i < nLen; i++){
aTH[i].style.padding = "";
aTH[i].style.border = "";
aTH[i].style.backgroundColor = "";
aTH[i].style.color = "";
}
},
_hideTableTemplate : function(elTable){
if(elTable.getAttribute(this.ATTR_TBL_TEMPLATE)){
this._doApplyTableTemplate(elTable, nhn.husky.SE2M_TableTemplate[this.parseIntOr0(elTable.getAttribute(this.ATTR_TBL_TEMPLATE))], true);
}
},
_showTableTemplate : function(elTable){
if(elTable.getAttribute(this.ATTR_TBL_TEMPLATE)){
this._doApplyTableTemplate(elTable, nhn.husky.SE2M_TableTemplate[this.parseIntOr0(elTable.getAttribute(this.ATTR_TBL_TEMPLATE))], false);
}
},
_doApplyTableTemplate : function(elTable, htTableTemplate, bClearStyle){
var htTableProperty = htTableTemplate.htTableProperty;
var htTableStyle = htTableTemplate.htTableStyle;
var ht1stRowStyle = htTableTemplate.ht1stRowStyle;
var ht1stColumnStyle = htTableTemplate.ht1stColumnStyle;
var aRowStyle = htTableTemplate.aRowStyle;
var elTmp;
if(htTableProperty){
this._copyAttributesTo(elTable, htTableProperty, bClearStyle);
}
if(htTableStyle){
this._copyStylesTo(elTable, htTableStyle, bClearStyle);
}
var aTR = jindo.$$(">TBODY>TR", elTable, {oneTimeOffCache:true});
var nStartRowNum = 0;
if(ht1stRowStyle){
var nStartRowNum = 1;
for(var ii = 0, nNumCells = aTR[0].childNodes.length; ii < nNumCells; ii++){
elTmp = aTR[0].childNodes[ii];
if(!elTmp.tagName || !elTmp.tagName.match(/^TD|TH$/)){continue;}
this._copyStylesTo(elTmp, ht1stRowStyle, bClearStyle);
}
}
var nRowSpan;
var elFirstEl;
if(ht1stColumnStyle){
var nRowStart = ht1stRowStyle ? 1 : 0;
for(var i = nRowStart, nLen = aTR.length; i < nLen;){
elFirstEl = aTR[i].firstChild;
nRowSpan = 1;
if(elFirstEl && elFirstEl.tagName.match(/^TD|TH$/)){
nRowSpan = parseInt(elFirstEl.getAttribute("rowSpan"), 10) || 1;
this._copyStylesTo(elFirstEl, ht1stColumnStyle, bClearStyle);
}
i += nRowSpan;
}
}
if(aRowStyle){
var nNumStyles = aRowStyle.length;
for(var i = nStartRowNum, nLen = aTR.length; i < nLen; i++){
for(var ii = 0, nNumCells = aTR[i].childNodes.length; ii < nNumCells; ii++){
var elTmp = aTR[i].childNodes[ii];
if(!elTmp.tagName || !elTmp.tagName.match(/^TD|TH$/)){continue;}
this._copyStylesTo(elTmp, aRowStyle[(i+nStartRowNum)%nNumStyles], bClearStyle);
}
}
}
},
_copyAttributesTo : function(oTarget, htProperties, bClearStyle){
var elTmp;
for(var x in htProperties){
if(htProperties.hasOwnProperty(x)){
if(bClearStyle){
if(oTarget[x]){
elTmp = document.createElement(oTarget.tagName);
elTmp[x] = htProperties[x];
if(elTmp[x] == oTarget[x]){
oTarget.removeAttribute(x);
}
}
}else{
elTmp = document.createElement(oTarget.tagName);
elTmp.style[x] = "";
if(!oTarget[x] || oTarget.style[x] == elTmp.style[x]){oTarget.setAttribute(x, htProperties[x]);}
}
}
}
},
_copyStylesTo : function(oTarget, htProperties, bClearStyle){
var elTmp;
for(var x in htProperties){
if(htProperties.hasOwnProperty(x)){
if(bClearStyle){
if(oTarget.style[x]){
elTmp = document.createElement(oTarget.tagName);
elTmp.style[x] = htProperties[x];
if(elTmp.style[x] == oTarget.style[x]){
oTarget.style[x] = "";
}
}
}else{
elTmp = document.createElement(oTarget.tagName);
elTmp.style[x] = "";
if(!oTarget.style[x] || oTarget.style[x] == elTmp.style[x] || x.match(/^border/)){oTarget.style[x] = htProperties[x];}
}
}
}
},
_hideResizer : function(){
this.elResizeGrid.style.display = "none";
},
_showResizer : function(){
this.elResizeGrid.style.display = "block";
},
_setResizerSize : function(width, height){
this.elResizeGrid.style.width = width + "px";
this.elResizeGrid.style.height = height + "px";
},
parseBorder : function(vBorder, sBorderStyle){
if(sBorderStyle == "none"){return 0;}
var num = parseInt(vBorder, 10);
if(isNaN(num)){
if(typeof(vBorder) == "string"){
return 1;
}
}
return num;
},
parseIntOr0 : function(num){
num = parseInt(num, 10);
if(isNaN(num)){return 0;}
return num;
},
_getTRCloneWithAllTD : function(nRow){
var elResult = this.htMap[0][nRow].parentNode.cloneNode(false);
var elCurTD, elCurTDClone;
for(var i = 0, nLen = this.htMap.length; i < nLen; i++){
elCurTD = this.htMap[i][nRow];
if(this._rxCellNames.test(elCurTD.tagName)){
elCurTDClone = this._shallowCloneTD(elCurTD);
elCurTDClone.setAttribute("rowSpan", 1);
elCurTDClone.setAttribute("colSpan", 1);
elCurTDClone.style.width = "";
elCurTDClone.style.height = "";
elResult.insertBefore(elCurTDClone, null);
}
}
return elResult;
},
_shallowCloneTD : function(elTD){
var elResult = elTD.cloneNode(false);
elResult.innerHTML = this.sEmptyTDSrc;
return elResult;
},
_isValidTable : function(elTbl){
if(!elTbl || !elTbl.tagName || elTbl.tagName != "TABLE"){
return false;
}
this.htMap = this._getCellMapping(elTbl);
var nXSize = this.htMap.length;
if(nXSize < 1){return false;}
var nYSize = this.htMap[0].length;
if(nYSize < 1){return false;}
for(var i = 1; i < nXSize; i++){
if(this.htMap[i].length != nYSize || !this.htMap[i][nYSize - 1]){
return false;
}
for(var j = 0; j < nYSize; j++){
if(!this.htMap[i] || !this.htMap[i][j]){
return false;
}
}
}
return true;
},
addCSSClass : function(sClassName, sClassRule){
var oDoc = this.oApp.getWYSIWYGDocument();
if(oDoc.styleSheets[0] && oDoc.styleSheets[0].addRule){
oDoc.styleSheets[0].addRule("." + sClassName, sClassRule);
}else{
var elHead = oDoc.getElementsByTagName("HEAD")[0];
var elStyle = oDoc.createElement ("STYLE");
elHead.appendChild (elStyle);
elStyle.sheet.insertRule("." + sClassName + " { "+sClassRule+" }", 0);
}
},
_removeEmptyTextNode_IE : function(elPair){
var elEmptyTextNode = elPair.nextSibling;
if(elEmptyTextNode && elEmptyTextNode.nodeType == 3 && !/\S/.test(elEmptyTextNode.nodeValue)){
elPair.parentNode.removeChild(elEmptyTextNode);
}
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_BGColor$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_BGColor, {
$ON_TOGGLE_BGCOLOR_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.elDropdownLayer, null, "BGCOLOR_LAYER_SHOWN", [], "BGCOLOR_LAYER_HIDDEN", []]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['bgcolor']);
},
$ON_BGCOLOR_LAYER_SHOWN : function(){
this.oApp.exec("SELECT_UI", ["BGColorB"]);
this.oApp.exec("SHOW_COLOR_PALETTE", ["APPLY_BGCOLOR", this.elPaletteHolder]);
},
$ON_BGCOLOR_LAYER_HIDDEN : function(){
this.oApp.exec("DESELECT_UI", ["BGColorB"]);
this.oApp.exec("RESET_COLOR_PALETTE", []);
},
$ON_EVENT_APPLY_BGCOLOR : function(weEvent){
var elButton = weEvent.element;
while(elButton.tagName == "SPAN"){elButton = elButton.parentNode;}
if(elButton.tagName != "BUTTON"){return;}
var sBGColor, sFontColor;
sBGColor = elButton.style.backgroundColor;
sFontColor = elButton.style.color;
this.oApp.exec("APPLY_BGCOLOR", [sBGColor, sFontColor]);
},
$ON_APPLY_LAST_USED_BGCOLOR : function(){
this.oApp.exec("APPLY_BGCOLOR", [this.sLastUsedColor]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['bgcolor']);
},
$ON_APPLY_BGCOLOR : function(sBGColor, sFontColor){
if(!this.rxColorPattern.test(sBGColor)){
alert(this.oApp.$MSG("SE_Color.invalidColorCode"));
return;
}
this._setLastUsedBGColor(sBGColor);
var oStyle = {"backgroundColor": sBGColor};
if(sFontColor){oStyle.color = sFontColor;}
this.oApp.exec("SET_WYSIWYG_STYLE", [oStyle]);
this.oApp.exec("HIDE_ACTIVE_LAYER");
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_FontColor$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_FontColor, {
$ON_TOGGLE_FONTCOLOR_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.elDropdownLayer, null, "FONTCOLOR_LAYER_SHOWN", [], "FONTCOLOR_LAYER_HIDDEN", []]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['fontcolor']);
},
$ON_FONTCOLOR_LAYER_SHOWN : function(){
this.oApp.exec("SELECT_UI", ["fontColorB"]);
this.oApp.exec("SHOW_COLOR_PALETTE", ["APPLY_FONTCOLOR", this.elPaletteHolder]);
},
$ON_FONTCOLOR_LAYER_HIDDEN : function(){
this.oApp.exec("DESELECT_UI", ["fontColorB"]);
this.oApp.exec("RESET_COLOR_PALETTE", []);
},
$ON_APPLY_LAST_USED_FONTCOLOR : function(){
this.oApp.exec("APPLY_FONTCOLOR", [this.sLastUsedColor]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['fontcolor']);
},
$ON_APPLY_FONTCOLOR : function(sFontColor){
if(!this.rxColorPattern.test(sFontColor)){
alert(this.oApp.$MSG("SE_FontColor.invalidColorCode"));
return;
}
this._setLastUsedFontColor(sFontColor);
this.oApp.exec("SET_WYSIWYG_STYLE", [{"color":sFontColor}]);
this.oApp.exec("HIDE_ACTIVE_LAYER");
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_Hyperlink$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_Hyperlink, {
$ON_TOGGLE_HYPERLINK_LAYER : function(){
if(!this.bLayerShown){
this.oApp.exec("IE_FOCUS", []);
this.oSelection = this.oApp.getSelection();
}
this.oApp.delayedExec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.oHyperlinkLayer, null, "MSG_HYPERLINK_LAYER_SHOWN", [], "MSG_HYPERLINK_LAYER_HIDDEN", [""]], 0);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['hyperlink']);
},
$ON_MSG_HYPERLINK_LAYER_SHOWN : function(){
this.bLayerShown = true;
var oAnchor = this.oSelection.findAncestorByTagName("A");
if (!oAnchor) {
oAnchor = this._getSelectedNode();
}
this.oCbNewWin.checked = false;
if(oAnchor && !this.oSelection.collapsed){
this.oSelection.selectNode(oAnchor);
this.oSelection.select();
var sTarget = oAnchor.target;
if(sTarget && sTarget == "_blank"){this.oCbNewWin.checked = true;}
try{
var sHref = oAnchor.getAttribute("href");
this.oLinkInput.value = sHref && sHref.indexOf("#") == -1 ? sHref : "http://";
}catch(e){
this.oLinkInput.value = "http://";
}
this.bModify = true;
}else{
this.oLinkInput.value = "http://";
this.bModify = false;
}
this.oApp.delayedExec("SELECT_UI", ["hyperlink"], 0);
this.oLinkInput.focus();
this.oLinkInput.value = this.oLinkInput.value;
this.oLinkInput.select();
},
$ON_MSG_HYPERLINK_LAYER_HIDDEN : function(){
this.bLayerShown = false;
this.oApp.exec("DESELECT_UI", ["hyperlink"]);
},
_validateTarget : function() {
var oNavigator = jindo.$Agent().navigator(),
bReturn = true;
if(oNavigator.ie) {
jindo.$A(this.oSelection.getNodes(true)).forEach(function(elNode, index, array){
if(!!elNode && elNode.nodeType == 1 && elNode.tagName.toLowerCase() == "iframe" && elNode.getAttribute('s_type').toLowerCase() == "db") {
bReturn = false;
jindo.$A.Break();
}
jindo.$A.Continue();
}, this);
}
return bReturn;
},
$ON_APPLY_HYPERLINK : function(){
if(!this._validateTarget()){
alert(this.oApp.$MSG("SE_Hyperlink.invalidTarget"));
return;
}
var sURL = this.oLinkInput.value;
if(!/^((http|https|ftp|mailto):(?:\/\/)?)/.test(sURL)){
sURL = "http://"+sURL;
}
sURL = sURL.replace(/\s+$/, "");
var oAgent = jindo.$Agent().navigator();
var sBlank = "";
this.oApp.exec("IE_FOCUS", []);
if(oAgent.ie){sBlank = "<span style=\"text-decoration:none;\">&nbsp;</span>";}
if(this._validateURL(sURL)){
sTarget = "_self";
if(this.oCbNewWin.checked){
if(false){
sTarget = "_self";
}else{
sTarget = "_blank";
}
}
this.oApp.exec("RECORD_UNDO_BEFORE_ACTION", ["HYPERLINK", {sSaveTarget:(this.bModify ? "A" : null)}]);
var sBM;
if(this.oSelection.collapsed){
var str = "<a href='" + sURL + "' target="+sTarget+">" + sURL + "</a>" + sBlank;
this.oSelection.pasteHTML(str);
sBM = this.oSelection.placeStringBookmark();
}else{
sBM = this.oSelection.placeStringBookmark();
this.oSelection.select();
if(oAgent.ie && (oAgent.version === 8 || oAgent.nativeVersion === 8)){
this.oApp.exec("IE_FOCUS", []);
this.oSelection.moveToBookmark(sBM);
this.oSelection.select();
}
var nSession = Math.ceil(Math.random()*10000);
if(sURL == ""){
this.oApp.exec("EXECCOMMAND", ["unlink"]);
}else{		
if(this._isExceptional()){
this.oApp.exec("EXECCOMMAND", ["unlink", false, "", {bDontAddUndoHistory: true}]);
var sTempUrl = "<a href='" + sURL + "' target="+sTarget+">";
jindo.$A(this.oSelection.getNodes(true)).forEach(function(value, index, array){
var oEmptySelection = this.oApp.getEmptySelection();
if(value.nodeType === 3){
oEmptySelection.selectNode(value);
oEmptySelection.pasteHTML(sTempUrl + value.nodeValue + "</a>");
}else if(value.nodeType === 1 && value.tagName === "IMG"){
oEmptySelection.selectNode(value);
oEmptySelection.pasteHTML(sTempUrl + jindo.$Element(value).outerHTML() + "</a>");
}
}, this);
}else{
this.oApp.exec("EXECCOMMAND", ["createLink", false, this.sATagMarker+nSession+encodeURIComponent(sURL), {bDontAddUndoHistory: true}]);
}
}
var oDoc = this.oApp.getWYSIWYGDocument();
var aATags = oDoc.body.getElementsByTagName("A");
var nLen = aATags.length;
var rxMarker = new RegExp(this.sRXATagMarker+nSession, "gi");
var elATag;
for(var i=0; i<nLen; i++){
elATag = aATags[i];
var sHref = "";
try{
sHref = elATag.getAttribute("href");
}catch(e){}
if (sHref && sHref.match(rxMarker)) {
var sNewHref = sHref.replace(rxMarker, "");
var sDecodeHref = decodeURIComponent(sNewHref);
if(oAgent.ie){
jindo.$Element(elATag).attr({
"href" : sDecodeHref,
"target" : sTarget
});
}else{
var sAContent = jindo.$Element(elATag).html();
jindo.$Element(elATag).attr({
"href" : sDecodeHref,
"target" : sTarget
});
if(this._validateURL(sAContent)){
jindo.$Element(elATag).html(jindo.$Element(elATag).attr("href"));
}
}
}
}
}
this.oApp.exec("HIDE_ACTIVE_LAYER");
setTimeout(jindo.$Fn(function(){
var oSelection = this.oApp.getEmptySelection();
oSelection.moveToBookmark(sBM);
oSelection.collapseToEnd();
oSelection.select();
oSelection.removeStringBookmark(sBM);
this.oApp.exec("FOCUS");
this.oApp.exec("RECORD_UNDO_AFTER_ACTION", ["HYPERLINK", {sSaveTarget:(this.bModify ? "A" : null)}]);
}, this).bind(), 17);
}else{
alert(this.oApp.$MSG("SE_Hyperlink.invalidURL"));
this.oLinkInput.focus();
}
},
_isExceptional : function(){
var oNavigator = jindo.$Agent().navigator(),
bImg = false, bEmail = false;
if(!oNavigator.ie){
return false;
}
if(this.oApp.getWYSIWYGDocument().selection && this.oApp.getWYSIWYGDocument().selection.type === "None"){
bImg = jindo.$A(this.oSelection.getNodes()).some(function(value, index, array){
if(value.nodeType === 1 && value.tagName === "IMG"){
return true;
}
}, this);
if(bImg){
return true;
}
}
if(oNavigator.nativeVersion > 8){
return false;
}
bEmail = jindo.$A(this.oSelection.getTextNodes()).some(function(value, index, array){
if(value.nodeValue.indexOf("@") >= 1){
return true;
}
}, this);
if(bEmail){
return true;
}
return false;
},
_getSelectedNode : function(){
var aNodes = this.oSelection.getNodes();
for (var i = 0; i < aNodes.length; i++) {
if (aNodes[i].tagName && aNodes[i].tagName == "A") {
return aNodes[i];
}
}
},
_validateURL : function(sURL){
if(!sURL){return false;}
try{
var aURLParts = sURL.split("?");
aURLParts[0] = aURLParts[0].replace(/%[a-z0-9]{2}/gi, "U");
decodeURIComponent(aURLParts[0]);
}catch(e){
return false;
}
return /^(http|https|ftp|mailto):(\/\/)?(([-가-힣]|\w)+(?:[\/\.:@]([-가-힣]|\w)+)+)\/?(.*)?\s*$/i.test(sURL);
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_LineHeightWithLayerUI$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_LineHeightWithLayerUI, {
_assignHTMLObjects : function(elAppContainer) {
this.oDropdownLayer = jindo.$$.getSingle("DIV.husky_se2m_lineHeight_layer", elAppContainer);
this.aLIOptions = jindo.$A(jindo.$$("LI", this.oDropdownLayer)).filter(function(v,i,a){return (v.firstChild !== null);})._array;
this.oInput = jindo.$$.getSingle("INPUT", this.oDropdownLayer);
var tmp = jindo.$$.getSingle(".husky_se2m_lineHeight_direct_input", this.oDropdownLayer);
tmp = jindo.$$("BUTTON", tmp);
this.oBtn_up = tmp[0];
this.oBtn_down = tmp[1];
this.oBtn_ok = tmp[2];
this.oBtn_cancel = tmp[3];
},
$LOCAL_BEFORE_FIRST : function(){
this._assignHTMLObjects(this.oApp.htOptions.elAppContainer);
this.oApp.exec("SE2_ATTACH_HOVER_EVENTS", [this.aLIOptions]);
for(var i=0; i<this.aLIOptions.length; i++){
this.oApp.registerBrowserEvent(this.aLIOptions[i], "click", "SET_LINEHEIGHT_FROM_LAYER_UI", [this._getLineHeightFromLI(this.aLIOptions[i])]);
}
this.oApp.registerBrowserEvent(this.oBtn_up, "click", "SE2M_INC_LINEHEIGHT", []);
this.oApp.registerBrowserEvent(this.oBtn_down, "click", "SE2M_DEC_LINEHEIGHT", []);
this.oApp.registerBrowserEvent(this.oBtn_ok, "click", "SE2M_SET_LINEHEIGHT_FROM_DIRECT_INPUT", []);
this.oApp.registerBrowserEvent(this.oBtn_cancel, "click", "SE2M_CANCEL_LINEHEIGHT", []);
this.oApp.registerBrowserEvent(this.oInput, "keydown", "EVENT_SE2M_LINEHEIGHT_KEYDOWN");
},
$ON_EVENT_SE2M_LINEHEIGHT_KEYDOWN : function(oEvent){
if (oEvent.key().enter){
this.oApp.exec("SE2M_SET_LINEHEIGHT_FROM_DIRECT_INPUT");
oEvent.stop();
}
},
$ON_SE2M_TOGGLE_LINEHEIGHT_LAYER : function(){
this.oApp.exec("TOGGLE_TOOLBAR_ACTIVE_LAYER", [this.oDropdownLayer, null, "LINEHEIGHT_LAYER_SHOWN", [], "LINEHEIGHT_LAYER_HIDDEN", []]);
this.oApp.exec('MSG_NOTIFY_CLICKCR', ['lineheight']);
},
$ON_SE2M_INC_LINEHEIGHT : function(){
this.oInput.value = parseInt(this.oInput.value, 10) || this.MIN_LINE_HEIGHT;
this.oInput.value++;
},
$ON_SE2M_DEC_LINEHEIGHT : function(){
this.oInput.value = parseInt(this.oInput.value, 10) || this.MIN_LINE_HEIGHT;
if(this.oInput.value > this.MIN_LINE_HEIGHT){this.oInput.value--;}
},
$ON_LINEHEIGHT_LAYER_SHOWN : function(){
this.oApp.exec("SELECT_UI", ["lineHeight"]);
this.oInitialSelection = this.oApp.getSelection();
var nLineHeight = this.oApp.getLineStyle("lineHeight");
if(nLineHeight != null && nLineHeight !== 0){
this.oInput.value = (nLineHeight*100).toFixed(0);
var elLi = this._getMatchingLI(this.oInput.value+"%");
if(elLi){jindo.$Element(elLi.firstChild).addClass("active");}
}else{
this.oInput.value = "";
}
},
$ON_LINEHEIGHT_LAYER_HIDDEN : function(){
this.oApp.exec("DESELECT_UI", ["lineHeight"]);
this._clearOptionSelection();
},
$ON_SE2M_SET_LINEHEIGHT_FROM_DIRECT_INPUT : function(){
var nInputValue = parseInt(this.oInput.value, 10);
var sValue = (nInputValue < this.MIN_LINE_HEIGHT) ? this.MIN_LINE_HEIGHT : nInputValue;
this._setLineHeightAndCloseLayer(sValue);
},
$ON_SET_LINEHEIGHT_FROM_LAYER_UI : function(sValue){
this._setLineHeightAndCloseLayer(sValue);
},
$ON_SE2M_CANCEL_LINEHEIGHT : function(){
this.oInitialSelection.select();
this.oApp.exec("HIDE_ACTIVE_LAYER");
},
_setLineHeightAndCloseLayer : function(sValue){
var nLineHeight = parseInt(sValue, 10)/100;
if(nLineHeight>0){
this.oApp.exec("SET_LINE_STYLE", ["lineHeight", nLineHeight]);
}else{
alert(this.oApp.$MSG("SE_LineHeight.invalidLineHeight"));
}
this.oApp.exec("SE2M_TOGGLE_LINEHEIGHT_LAYER", []);
var oNavigator = jindo.$Agent().navigator();
if(oNavigator.chrome || oNavigator.safari){
this.oApp.exec("FOCUS");
}
},
_getMatchingLI : function(sValue){
var elLi;
sValue = sValue.toLowerCase();
for(var i=0; i<this.aLIOptions.length; i++){
elLi = this.aLIOptions[i];
if(this._getLineHeightFromLI(elLi).toLowerCase() == sValue){return elLi;}
}
return null;
},
_getLineHeightFromLI : function(elLi){
return elLi.firstChild.firstChild.innerHTML;
},
_clearOptionSelection : function(elLi){
for(var i=0; i<this.aLIOptions.length; i++){
jindo.$Element(this.aLIOptions[i].firstChild).removeClass("active");
}
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_SE2M_QuickEditor_Common$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.SE2M_QuickEditor_Common, {
setOpenType : function(sType,bBol){
if(typeof(this._environmentData) == "undefined" || this._environmentData == null){
this._environmentData = {};
}
if(typeof(this._environmentData[sType]) == "undefined" || this._environmentData[sType] == null){
this._environmentData[sType] = {};
}
if(typeof(this._environmentData[sType].isOpen) == "undefined" || this._environmentData[sType].isOpen == null){
this._environmentData[sType].isOpen = true;
}
this._environmentData[sType].isOpen = bBol;
},
$ON_OPEN_QE_LAYER : function(oEle,oLayer,sType){
if(this.waHotkeys.length() > 0 && !this.waHotkeyLayers.has(oLayer)){
this.waHotkeyLayers.push(oLayer);
var aParam;
for(var i=0, nLen=this.waHotkeys.length(); i<nLen; i++){
aParam = this.waHotkeys.get(i);
this.oApp.exec("ADD_HOTKEY", [aParam[0], aParam[1], aParam[2], oLayer]);
}
}
var  type = sType;
if(type){
this.targetEle = oEle;
this.currentEle = oLayer;
this.layer_show(type,oEle);
}
},
$ON_CLOSE_QE_LAYER : function(weEvent){
if(!this.currentEle){return;}
this.oApp.exec("CLOSE_SUB_LAYER_QE");
this.layer_hide(weEvent);
},
$LOCAL_BEFORE_FIRST : function(sMsg) {
if (!sMsg.match(/OPEN_QE_LAYER/)) {
this.oApp.acceptLocalBeforeFirstAgain(this, true);
if(sMsg.match(/REGISTER_HOTKEY/)){
return true;
}
return false;
}
this.woEditor = jindo.$Element(this.oApp.elEditingAreaContainer);
this.woStandard = jindo.$Element(this.oApp.htOptions.elAppContainer).offset();
this._qe_wrap = jindo.$$.getSingle("DIV.quick_wrap", this.oApp.htOptions.elAppContainer);
var that = this;
new jindo.DragArea(this._qe_wrap, {
sClassName : 'q_dragable',
bFlowOut : false,
nThreshold : 1
}).attach({
beforeDrag : function(oCustomEvent) {
oCustomEvent.elFlowOut = oCustomEvent.elArea.parentNode;
},
dragStart: function(oCustomEvent){
if(!jindo.$Element(oCustomEvent.elDrag).hasClass('se2_qmax')){
oCustomEvent.elDrag = oCustomEvent.elDrag.parentNode;
}
that.oApp.exec("SHOW_EDITING_AREA_COVER");
},
dragEnd : function(oCustomEvent){
that.changeFixedMode();
that._in_event = false;
var richEle = jindo.$Element(oCustomEvent.elDrag);
that._environmentData[that._currentType].position = [richEle.css("top"),richEle.css("left")];
that.oApp.exec("HIDE_EDITING_AREA_COVER");
}
});
var imgFn = jindo.$Fn(this.toggle,this).bind("img");
var tableFn = jindo.$Fn(this.toggle,this).bind("table");
jindo.$Fn(imgFn,this).attach(jindo.$$.getSingle(".q_open_img_fold", this.oApp.htOptions.elAppContainer),"click");
jindo.$Fn(imgFn,this).attach(jindo.$$.getSingle(".q_open_img_full", this.oApp.htOptions.elAppContainer),"click");
jindo.$Fn(tableFn,this).attach(jindo.$$.getSingle(".q_open_table_fold", this.oApp.htOptions.elAppContainer),"click");
jindo.$Fn(tableFn,this).attach(jindo.$$.getSingle(".q_open_table_full", this.oApp.htOptions.elAppContainer),"click");
},
toggle : function(sType,weEvent){
sType = this._currentType;
this.oApp.exec("CLOSE_QE_LAYER", [weEvent]);
if(this._environmentData[sType].type=="full"){
this._environmentData[sType].type = "fold";
}else{
this._environmentData[sType].type = "full";
}
if (this._environmentData && this._bUseConfig) {
jindo.$Ajax(this._sAddTextAjaxUrl,{
type : "jsonp",
onload: function(){}
}).request({
text_key :"qeditor_fold",
text_data : "{table:'"+this._environmentData["table"]["type"]+"',img:'"+this._environmentData["img"]["type"]+"',review:'"+this._environmentData["review"]["type"]+"'}"
});
}
this.oApp.exec("OPEN_QE_LAYER", [this.targetEle,this.currentEle,sType]);
this._in_event = false;
weEvent.stop(jindo.$Event.CANCEL_DEFAULT);
},
positionCopy:function(beforeX, beforeY, sAfterEle){
jindo.$Element(jindo.$$.getSingle("._"+sAfterEle,this.currentEle)).css({
top : beforeY,
left : beforeX
});
},
changeFixedMode : function(){
this._environmentData[this._currentType].isFixed = true;
},
$ON_HIDE_ACTIVE_LAYER : function(){
this.oApp.exec("CLOSE_QE_LAYER");
},
$ON_EVENT_EDITING_AREA_MOUSEDOWN:function(weEvent){
if(this._currentType&&(!this._in_event)&&this._environmentData[this._currentType].isOpen){
this.oApp.exec("CLOSE_QE_LAYER", [weEvent]);
}
this._in_event = false;
},
$ON_EVENT_EDITING_AREA_MOUSEWHEEL:function(weEvent){
if(this._currentType&&(!this._in_event)&&this._environmentData[this._currentType].isOpen){
this.oApp.exec("CLOSE_QE_LAYER", [weEvent]);
}
this._in_event = false;
},
get_type : function(oEle){
var tagName = oEle.tagName.toLowerCase();
if(this.waTableTagNames.has(tagName)){
return "table";
}else if(tagName=="img"){
return "img";
}
},
$ON_QE_IN_KEYUP : function(){
this._in_event = true;
},
$ON_QE_IN_MOUSEDOWN : function(){
this._in_event = true;
},
$ON_QE_IN_MOUSEWHEEL : function(){
this._in_event = true;
},
layer_hide : function(weEvent){
this.setOpenType(this._currentType,false);
jindo.$Element(jindo.$$.getSingle("._"+this._environmentData[this._currentType].type,this.currentEle)).hide();
},
lazy_common : function(){
this.oApp.registerBrowserEvent(jindo.$(this._qe_wrap), "keyup", "QE_IN_KEYUP");
this.oApp.registerBrowserEvent(jindo.$(this._qe_wrap), "mousedown", "QE_IN_MOUSEDOWN");
this.oApp.registerBrowserEvent(jindo.$(this._qe_wrap), "mousewheel", "QE_IN_MOUSEWHEEL");
this.lazy_common = function(){};
},
layer_show : function(sType,oEle){
this._currentType = sType;
this.setOpenType(this._currentType,true);
var  layer = jindo.$$.getSingle("._"+this._environmentData[this._currentType].type,this.currentEle);
jindo.$Element(layer)
.show()
.css( this.get_position_layer(oEle , layer) );
this.lazy_common();
},
get_position_layer : function(oEle , oLayer){
if(!this.isCurrentFixed() || this._environmentData[this._currentType].type == "fold"){
return this.calculateLayer(oEle , oLayer);
}
var position = this._environmentData[this._currentType].position;
var nTop = parseInt(position[0], 10);
var nAppHeight = this.getAppPosition().h;
var nLayerHeight = jindo.$Element(oLayer).height();
if((nTop + nLayerHeight + this.nYGap) > nAppHeight){
nTop = nAppHeight - nLayerHeight;
this._environmentData[this._currentType].position[0] = nTop;
}
return {
top : nTop + "px",
left :position[1]
};
},
isCurrentFixed : function(){
return this._environmentData[this._currentType].isFixed;
},
calculateLayer : function(oEle, oLayer){
var positionInfo = this.getPositionInfo(oEle, oLayer);
return {
top  : positionInfo.y + "px",
left : positionInfo.x + "px"
};
},
getPositionInfo : function(oEle, oLayer){
this.nYGap = jindo.$Agent().navigator().ie? -16 : -18;
this.nXGap = 1;
var oRevisePosition = {};
var eleInfo = this.getElementPosition(oEle, oLayer);
var appInfo = this.getAppPosition();
var layerInfo = {
w : jindo.$Element(oLayer).width(),
h : jindo.$Element(oLayer).height()
};
if((eleInfo.x + layerInfo.w + this.nXGap) > appInfo.w){
oRevisePosition.x = appInfo.w - layerInfo.w ;
}else{
oRevisePosition.x = eleInfo.x + this.nXGap;
}
if((eleInfo.y + layerInfo.h + this.nYGap) > appInfo.h){
oRevisePosition.y = appInfo.h - layerInfo.h - 2;
}else{
oRevisePosition.y = eleInfo.y + this.nYGap;
}
return {
x : oRevisePosition.x ,
y : oRevisePosition.y
};
},
getElementPosition : function(eEle, oLayer){
var wEle, oOffset, nEleWidth, nEleHeight, nScrollX, nScrollY;
if(eEle){
wEle = jindo.$Element(eEle);
oOffset = wEle.offset();
nEleWidth = wEle.width();
nEleHeight = wEle.height();
}else{
oOffset = {
top : parseInt(oLayer.style.top, 10) - this.nYGap,
left : parseInt(oLayer.style.left, 10) - this.nXGap
};
nEleWidth = 0;
nEleHeight = 0;
}
var oAppWindow = this.oApp.getWYSIWYGWindow();
if(typeof oAppWindow.scrollX == "undefined"){
nScrollX = oAppWindow.document.documentElement.scrollLeft;
nScrollY = oAppWindow.document.documentElement.scrollTop;
}else{
nScrollX = oAppWindow.scrollX;
nScrollY = oAppWindow.scrollY;
}
var oEditotOffset = this.woEditor.offset();
return {
x : oOffset.left - nScrollX + nEleWidth,
y : oOffset.top  - nScrollY + nEleHeight
};
},
getAppPosition : function(){
return {
w : this.woEditor.width(),
h : this.woEditor.height()
};
}
});
nhn.husky.HuskyCore.addLoadedFile("hp_DialogLayerManager$Lazy.js");
nhn.husky.HuskyCore.mixin(nhn.husky.DialogLayerManager, {
$ON_SHOW_DIALOG_LAYER : function(elLayer, htOptions){
elLayer = jindo.$(elLayer);
htOptions = htOptions || {};
if(!elLayer){return;}
if(jindo.$A(this.aOpenedLayers).has(elLayer)){return;}
this.oApp.exec("POSITION_DIALOG_LAYER", [elLayer]);
this.aOpenedLayers[this.aOpenedLayers.length] = elLayer;
var oDraggableLayer;
var nIdx = jindo.$A(this.aMadeDraggable).indexOf(elLayer);
if(nIdx == -1){
oDraggableLayer = new nhn.DraggableLayer(elLayer, htOptions);
this.aMadeDraggable[this.aMadeDraggable.length] = elLayer;
this.aDraggableLayer[this.aDraggableLayer.length] = oDraggableLayer;
}else{
if(htOptions){
oDraggableLayer = this.aDraggableLayer[nIdx];
oDraggableLayer.setOptions(htOptions);
}
elLayer.style.display = "block";
}
if(htOptions.sOnShowMsg){
this.oApp.exec(htOptions.sOnShowMsg, htOptions.sOnShowParam);
}
},
$ON_HIDE_LAST_DIALOG_LAYER : function(){
this.oApp.exec("HIDE_DIALOG_LAYER", [this.aOpenedLayers[this.aOpenedLayers.length-1]]);
},
$ON_HIDE_ALL_DIALOG_LAYER : function(){
for(var i=this.aOpenedLayers.length-1; i>=0; i--){
this.oApp.exec("HIDE_DIALOG_LAYER", [this.aOpenedLayers[i]]);
}
},
$ON_HIDE_DIALOG_LAYER : function(elLayer){
elLayer = jindo.$(elLayer);
if(elLayer){elLayer.style.display = "none";}
this.aOpenedLayers = jindo.$A(this.aOpenedLayers).refuse(elLayer).$value();
},
$ON_TOGGLE_DIALOG_LAYER : function(elLayer, htOptions){
if(jindo.$A(this.aOpenedLayers).indexOf(elLayer)){
this.oApp.exec("SHOW_DIALOG_LAYER", [elLayer, htOptions]);
}else{
this.oApp.exec("HIDE_DIALOG_LAYER", [elLayer]);
}
},
$ON_SET_DIALOG_LAYER_POSITION : function(elLayer, nTop, nLeft){
elLayer.style.top = nTop;
elLayer.style.left = nLeft;
}
});
nhn.husky.HuskyCore.addLoadedFile("N_FindReplace.js");
nhn.FindReplace = jindo.$Class({
sKeyword : "",
window : null,
document : null,
bBrowserSupported : false,
_bLGDevice : false,
bEOC : false,
$init : function(win){
this.sInlineContainer = "SPAN|B|U|I|S|STRIKE";
this.rxInlineContainer = new RegExp("^("+this.sInlineContainer+")$");
this.window = win;
this.document = this.window.document;
if(this.document.domain != this.document.location.hostname){
var oAgentInfo = jindo.$Agent();
var oNavigatorInfo = oAgentInfo.navigator();
if(oNavigatorInfo.firefox && oNavigatorInfo.version < 3){
this.bBrowserSupported = false;
this.find = function(){return 3;};
return;
}
}
this._bLGDevice = (navigator.userAgent.indexOf("LG-") > -1);
this.bBrowserSupported = true;
},
find : function(sKeyword, bCaseMatch, bBackwards, bWholeWord){
var bSearchResult, bFreshSearch;
if(!this._bLGDevice){
this.window.focus();
}
if(!sKeyword) return 2;
this.bEOC = false;
bSearchResult = this.findNext(sKeyword, bCaseMatch, bBackwards, bWholeWord);
if(bSearchResult) return 0;
this.bEOC = true;
bSearchResult = this.findNew(sKeyword, bCaseMatch, bBackwards, bWholeWord);
if(bSearchResult) return 0;
return 1;
},
findNew : function (sKeyword, bCaseMatch, bBackwards, bWholeWord){
this.findReset();
return this.findNext(sKeyword, bCaseMatch, bBackwards, bWholeWord);
},
findNext : function(sKeyword, bCaseMatch, bBackwards, bWholeWord){
var bSearchResult;
bCaseMatch = bCaseMatch || false;
bWholeWord = bWholeWord || false;
bBackwards = bBackwards || false;
if(this.window.find){
var bWrapAround = false;
return this.window.find(sKeyword, bCaseMatch, bBackwards, bWrapAround, bWholeWord);
}
if(this.document.body.createTextRange){
try{
var iOption = 0;
if(bBackwards) iOption += 1;
if(bWholeWord) iOption += 2;
if(bCaseMatch) iOption += 4;
this.window.focus();
if(this.document.selection){
this._range = this.document.selection.createRangeCollection().item(0);
this._range.collapse(false);
}else if(!this._range){		
this._range = this.document.body.createTextRange();
}else{						
this._range.collapse(false);
}
bSearchResult = this._range.findText(sKeyword, 1, iOption);
this._range.select();
return bSearchResult;
}catch(e){
return false;
}
}
return false;
},
findReset : function() {
if (this.window.find){
this.window.getSelection().removeAllRanges();
return;
}
if(this.document.body.createTextRange){
this._range = this.document.body.createTextRange();
this._range.collapse(true);
this._range.select();
}
},
replace : function(sOriginalWord, Replacement, bCaseMatch, bBackwards, bWholeWord){
return this._replace(sOriginalWord, Replacement, bCaseMatch, bBackwards, bWholeWord);
},
_replace : function(sOriginalWord, Replacement, bCaseMatch, bBackwards, bWholeWord, oSelection){
if(!sOriginalWord) return 4;
oSelection = oSelection || new nhn.HuskyRange(this.window);
oSelection.setFromSelection();
bCaseMatch = bCaseMatch || false;
var bMatch, selectedText = oSelection.toString();
if(bCaseMatch)
bMatch = (selectedText == sOriginalWord);
else
bMatch = (selectedText.toLowerCase() == sOriginalWord.toLowerCase());
if(!bMatch)
return this.find(sOriginalWord, bCaseMatch, bBackwards, bWholeWord)+2;
if(typeof Replacement == "function"){
oSelection = Replacement(oSelection);
}else{
oSelection.pasteText(Replacement);
}
oSelection.select();
return this.find(sOriginalWord, bCaseMatch, bBackwards, bWholeWord);
},
replaceAll : function(sOriginalWord, Replacement, bCaseMatch, bWholeWord){
if(!sOriginalWord) return -1;
var bBackwards = false;
var iReplaceResult;
var iResult = 0;
var win = this.window;
if(this.find(sOriginalWord, bCaseMatch, bBackwards, bWholeWord) !== 0){
return iResult;
}
var oSelection = new nhn.HuskyRange(this.window);
oSelection.setFromSelection();
oSelection.collapseToStart();
var oTmpNode = this.window.document.createElement("SPAN");
oTmpNode.innerHTML = unescape("%uFEFF");
oSelection.insertNode(oTmpNode);
oSelection.select();
var sBookmark = oSelection.placeStringBookmark();
this.bEOC = false;
while(!this.bEOC){
iReplaceResult = this._replace(sOriginalWord, Replacement, bCaseMatch, bBackwards, bWholeWord, oSelection);
if(iReplaceResult == 0 || iReplaceResult == 1){
iResult++;
}
}
var startingPointReached = function(){
var oCurSelection = new nhn.HuskyRange(win);
oCurSelection.setFromSelection();
oSelection.moveToBookmark(sBookmark);
var pos = oSelection.compareBoundaryPoints(nhn.W3CDOMRange.START_TO_END, oCurSelection);
if(pos == 1) return false;
return true;
};
iReplaceResult = 0;
this.bEOC = false;
while(!startingPointReached() && iReplaceResult == 0 && !this.bEOC){
iReplaceResult = this._replace(sOriginalWord, Replacement, bCaseMatch, bBackwards, bWholeWord, oSelection);
if(iReplaceResult == 0 || iReplaceResult == 1){
iResult++;
}
}
oSelection.moveToBookmark(sBookmark);
oSelection.deleteContents();
oSelection.removeStringBookmark(sBookmark);
setTimeout(function(){
oTmpNode.parentNode.removeChild(oTmpNode);
}, 0);
return iResult;
},
_isBlankTextNode : function(oNode){
if(oNode.nodeType == 3 && oNode.nodeValue == ""){return true;}
return false;
},
_getNextNode : function(elNode, bDisconnected){
if(!elNode || elNode.tagName == "BODY"){
return {elNextNode: null, bDisconnected: false};
}
if(elNode.nextSibling){
elNode = elNode.nextSibling;
while(elNode.firstChild){
if(elNode.tagName && !this.rxInlineContainer.test(elNode.tagName)){
bDisconnected = true;
}
elNode = elNode.firstChild;
}
return {elNextNode: elNode, bDisconnected: bDisconnected};
}
return this._getNextNode(nhn.DOMFix.parentNode(elNode), bDisconnected);
},
_getNextTextNode : function(elNode, bDisconnected){
var htNextNode, elNode;
while(true){
htNextNode = this._getNextNode(elNode, bDisconnected);
elNode = htNextNode.elNextNode;
bDisconnected = htNextNode.bDisconnected;
if(elNode && elNode.nodeType != 3 && !this.rxInlineContainer.test(elNode.tagName)){
bDisconnected = true;
}
if(!elNode || (elNode.nodeType==3 && !this._isBlankTextNode(elNode))){
break;
}
}
return {elNextText: elNode, bDisconnected: bDisconnected};
},
_getFirstTextNode : function(){
var elFirstNode = this.document.body.firstChild;
while(!!elFirstNode && elFirstNode.firstChild){
elFirstNode = elFirstNode.firstChild;
}
if(!elFirstNode){
return null;
}
if(elFirstNode.nodeType != 3 || this._isBlankTextNode(elFirstNode)){
var htTmp = this._getNextTextNode(elFirstNode, false);
elFirstNode = htTmp.elNextText;
}
return elFirstNode;
},
_addToTextMap : function(elNode, aTexts, aElTexts, nLen){
var nStartPos = aTexts[nLen].length;
for(var i=0, nTo=elNode.nodeValue.length; i<nTo; i++){
aElTexts[nLen][nStartPos+i] = [elNode, i];
}
aTexts[nLen] += elNode.nodeValue;
},
_createTextMap : function(){
var aTexts = [];
var aElTexts = [];
var nLen=-1;
var elNode = this._getFirstTextNode();
var htNextNode = {elNextText: elNode, bDisconnected: true};
while(elNode){
if(htNextNode.bDisconnected){
nLen++;
aTexts[nLen] = "";
aElTexts[nLen] = [];
}
this._addToTextMap(htNextNode.elNextText, aTexts, aElTexts, nLen);
htNextNode = this._getNextTextNode(elNode, false);
elNode = htNextNode.elNextText;
}
return {aTexts: aTexts, aElTexts: aElTexts};
},
replaceAll_js : function(sOriginalWord, Replacement, bCaseMatch, bWholeWord){
try{
var t0 = new Date();
var htTmp = this._createTextMap();
var t1 = new Date();
var aTexts = htTmp.aTexts;
var aElTexts = htTmp.aElTexts;
var nMatchCnt = 0;
var nOriginLen = sOriginalWord.length;
for(var i=0, niLen=aTexts.length; i<niLen; i++){
var sText = aTexts[i];
for(var j=sText.length-nOriginLen; j>=0; j--){
var sTmp = sText.substring(j, j+nOriginLen);
if(bWholeWord &&
(j > 0 && sText.charAt(j-1).match(/[a-zA-Z가-힣]/))
){
continue;
}
if(sTmp == sOriginalWord){
nMatchCnt++;
var oSelection = new nhn.HuskyRange(this.window);
var elContainer, nOffset;
if(j+nOriginLen < aElTexts[i].length){
elContainer = aElTexts[i][j+nOriginLen][0];
nOffset = aElTexts[i][j+nOriginLen][1];
}else{
elContainer = aElTexts[i][j+nOriginLen-1][0];
nOffset = aElTexts[i][j+nOriginLen-1][1]+1;
}
oSelection.setEnd(elContainer, nOffset, true, true);
oSelection.setStart(aElTexts[i][j][0], aElTexts[i][j][1], true);
if(typeof Replacement == "function"){
oSelection = Replacement(oSelection);
}else{
oSelection.pasteText(Replacement);
}
j -= nOriginLen;
}
continue;
}
}
return nMatchCnt;
}catch(e){
return nMatchCnt;
}
}
});
nhn.husky.HuskyCore.addLoadedFile("SE2M_TableTemplate.js");
nhn.husky.SE2M_TableTemplate = [
{},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#c7c7c7"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#c7c7c7"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#666666"
},
{
padding				: "3px 4px 2px",
backgroundColor		: "#f3f3f3",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
htTableStyle : {
backgroundColor		: "#ffffff",
borderTop			: "1px solid #c7c7c7"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #c7c7c7",
backgroundColor		: "#ffffff",
color				: "#666666"
},
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #c7c7c7",
backgroundColor		: "#f3f3f3",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
htTableStyle : {
border				: "1px solid #c7c7c7"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#f3f3f3",
color				: "#666666",
borderRight			: "1px solid #e7e7e7",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
borderTop			: "1px solid #e7e7e7",
borderRight			: "1px solid #e7e7e7",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#c7c7c7"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#f8f8f8",
color				: "#666666"
},
{
padding				: "3px 4px 2px",
backgroundColor		: "#ebebeb",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
borderTop			: "1px solid #000000",
borderBottom		: "1px solid #000000",
backgroundColor		: "#333333",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #ebebeb",
backgroundColor		: "#ffffff",
color				: "#666666"
},
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #ebebeb",
backgroundColor		: "#f8f8f8",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#c7c7c7"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#333333",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
ht1stColumnStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#f8f8f8",
color				: "#666666",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#c7c7c7"
},
ht1stColumnStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#333333",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#666666"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#a6bcd1"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#a6bcd1"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
},
{
padding				: "3px 4px 2px",
backgroundColor		: "#f6f8fa",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
htTableStyle : {
backgroundColor		: "#ffffff",
borderTop			: "1px solid #a6bcd1"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #a6bcd1",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
},
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #a6bcd1",
backgroundColor		: "#f6f8fa",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
htTableStyle : {
border				: "1px solid #a6bcd1"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#f6f8fa",
color				: "#3d76ab",
borderRight			: "1px solid #e1eef7",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
borderTop			: "1px solid #e1eef7",
borderRight			: "1px solid #e1eef7",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#a6bcd1"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#fafbfc",
color				: "#3d76ab"
},
{
padding				: "3px 4px 2px",
backgroundColor		: "#e6ecf2",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "0"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
borderTop			: "1px solid #466997",
borderBottom		: "1px solid #466997",
backgroundColor		: "#6284ab",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #ebebeb",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
},
{
padding				: "3px 4px 2px",
borderBottom		: "1px solid #ebebeb",
backgroundColor		: "#f6f8fa",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#a6bcd1"
},
ht1stRowStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#6284ab",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
ht1stColumnStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#f6f8fa",
color				: "#3d76ab",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
}
]
},
{
htTableProperty : {
border		: "0",
cellPadding	: "0",
cellSpacing	: "1"
},
htTableStyle : {
backgroundColor		: "#a6bcd1"
},
ht1stColumnStyle : {
padding				: "3px 4px 2px",
backgroundColor		: "#6284ab",
color				: "#ffffff",
textAlign			: "left",
fontWeight			: "normal"
},
aRowStyle : [
{
padding				: "3px 4px 2px",
backgroundColor		: "#ffffff",
color				: "#3d76ab"
}
]
}
];
nhn.husky.HuskyCore.addLoadedFile("N_DraggableLayer.js");
nhn.DraggableLayer = jindo.$Class({
$init : function(elLayer, oOptions){
this.elLayer = elLayer;
this.setOptions(oOptions);
this.elHandle = this.oOptions.elHandle;
elLayer.style.display = "block";
elLayer.style.position = "absolute";
elLayer.style.zIndex = "9999";
this.aBasePosition = this.getBaseOffset(elLayer);
var nTop = (this.toInt(jindo.$Element(elLayer).offset().top) - this.aBasePosition.top);
var nLeft = (this.toInt(jindo.$Element(elLayer).offset().left) - this.aBasePosition.left);
var htXY = this._correctXY({x:nLeft, y:nTop});
elLayer.style.top = htXY.y+"px";
elLayer.style.left = htXY.x+"px";
this.$FnMouseDown = jindo.$Fn(jindo.$Fn(this._mousedown, this).bind(elLayer), this);
this.$FnMouseMove = jindo.$Fn(jindo.$Fn(this._mousemove, this).bind(elLayer), this);
this.$FnMouseUp = jindo.$Fn(jindo.$Fn(this._mouseup, this).bind(elLayer), this);
this.$FnMouseDown.attach(this.elHandle, "mousedown");
this.elHandle.ondragstart = new Function('return false');
this.elHandle.onselectstart = new Function('return false');
},
_mousedown : function(elLayer, oEvent){
if(oEvent.element.tagName == "INPUT") return;
this.oOptions.fnOnDragStart();
this.MouseOffsetY = (oEvent.pos().clientY-this.toInt(elLayer.style.top)-this.aBasePosition['top']);
this.MouseOffsetX = (oEvent.pos().clientX-this.toInt(elLayer.style.left)-this.aBasePosition['left']);
this.$FnMouseMove.attach(elLayer.ownerDocument, "mousemove");
this.$FnMouseUp.attach(elLayer.ownerDocument, "mouseup");
this.elHandle.style.cursor = "move";
},
_mousemove : function(elLayer, oEvent){
var nTop = (oEvent.pos().clientY-this.MouseOffsetY-this.aBasePosition['top']);
var nLeft = (oEvent.pos().clientX-this.MouseOffsetX-this.aBasePosition['left']);
var htXY = this._correctXY({x:nLeft, y:nTop});
elLayer.style.top = htXY.y + "px";
elLayer.style.left = htXY.x + "px";
},
_mouseup : function(elLayer, oEvent){
this.oOptions.fnOnDragEnd();
this.$FnMouseMove.detach(elLayer.ownerDocument, "mousemove");
this.$FnMouseUp.detach(elLayer.ownerDocument, "mouseup");
this.elHandle.style.cursor = "";
},
_correctXY : function(htXY){
var nLeft = htXY.x;
var nTop = htXY.y;
if(nTop<this.oOptions.nMinY) nTop = this.oOptions.nMinY;
if(nTop>this.oOptions.nMaxY) nTop = this.oOptions.nMaxY;
if(nLeft<this.oOptions.nMinX) nLeft = this.oOptions.nMinX;
if(nLeft>this.oOptions.nMaxX) nLeft = this.oOptions.nMaxX;
return {x:nLeft, y:nTop};
},
toInt : function(num){
var result = parseInt(num);
return result || 0;
},
findNonStatic : function(oEl){
if(!oEl) return null;
if(oEl.tagName == "BODY") return oEl;
if(jindo.$Element(oEl).css("position").match(/absolute|relative/i)) return oEl;
return this.findNonStatic(oEl.offsetParent);
},
getBaseOffset : function(oEl){
var oBase = this.findNonStatic(oEl.offsetParent) || oEl.ownerDocument.body;
var tmp = jindo.$Element(oBase).offset();
return {top: tmp.top, left: tmp.left};
},
setOptions : function(htOptions){
this.oOptions = htOptions || {};
this.oOptions.bModal = this.oOptions.bModal || false;
this.oOptions.elHandle = this.oOptions.elHandle || this.elLayer;
this.oOptions.nMinX = this.oOptions.nMinX || -999999;
this.oOptions.nMinY = this.oOptions.nMinY || -999999;
this.oOptions.nMaxX = this.oOptions.nMaxX || 999999;
this.oOptions.nMaxY = this.oOptions.nMaxY || 999999;
this.oOptions.fnOnDragStart = this.oOptions.fnOnDragStart || function(){};
this.oOptions.fnOnDragEnd = this.oOptions.fnOnDragEnd || function(){};
}
});