var aMainObj={};
var warnAudio;
var quickDlg="";
$(document).ready(function(){
	warnAudio = document.createElement('audio');
	warnAudio.setAttribute('src', 'assets/audio/small-ring.mp3');
	
	$("body").on("click",".readonly",function(){
		return false;
	});
	$("body").on("click",".toggle-bar",function(){
		$(this).siblings(".left-bar").toggle();
	});

	$("body").on("click",".btncloseinfo",function(){
		$(this).closest(".div-info").remove();
	});

	$("body .btnviewdlg").off("click");
	$("body").on("click",".btnviewdlg",function(){
		sFile=$(this).attr("data-file");
		if(sFile=="") return;
		sQs=$(this).attr("data-qs");
		sTitle=$(this).attr("title");
		sW=$(this).attr("data-dlgw");
		sH=$(this).attr("data-dlgh");
		sModal=$(this).attr("data-modal");
		if(sW==""||sW==undefined) sW="99%";
		if(sH==""||sH==undefined) sH="99%";
		if(sModal==""||sModal==undefined) sModal=true;

		showDialog(sFile+".php?"+sQs,sTitle,sH,sW,"",
		function(sResult){

		},false,function(){
			//Load Done Function
		},sModal);
	});

	$("body .btnquicklogin").off("click");
	$("body").on("click",".btnquicklogin",function(){
		showLogin();
	});




	$("body .fl-cas-head").off("click");
	$("body").on("click",".fl-cas-head",function(){
		//$(this).closest(".fl-cas-wrap").find(".fl-cas-body").hide();
		$(this).next(".fl-cas-body").toggle();
	});
	/*
	$("#pribta21").on("mouseover",".auto-height",function(){
		sOH=$(this).css("min-height");
		$(this).attr("data-oh",sOH);
		$(this).css("min-height","80%");
		//$(this).animate({"min-height":"80%"},500);
	});

	$("#pribta21").on("mouseout",".auto-height",function(){
		sOH=$(this).attr("data-oh");
		$(this).css("min-height",sOH);
		
		//$(this).animate({"min-height":sOH},500);
	});
	*/
	jQuery.expr[':'].Contains = function(a, i, m) {
	  return jQuery(a).text().toUpperCase()
	      .indexOf(m[3].toUpperCase()) >= 0;
	};

	$("body .ki-next").off("keydown");
	$("body").on("keydown",".ki-next",function(ev){
		if(ev.which=="40"){
			isNext =false;
			$("body .ki-next").each(function(ix,objx){
				if(isNext==true){
					$($(objx).focus());
					$isNext=false;
				}
				if($(objx)==$(this)){
					isNext=true;
				}
			});
		}
	});


/*
$.notify('hello !!', {
  style: 'happyblue'
});
and you can use the superblue class with:

$.notify('HELLO !!!!', {
  style: 'happyblue',
  className: 'superblue'
});
*/
	$("body").on("click",".popupbox",function(){
		//showPopup(htmlCode,dlgTitle,dlgHeight,dlgWidth,closeHide);
		sHtml=$(this).html();
		showPopup(sHtml,"","500","400","");
	});

	$(document).on('click', '.ui-widget-overlay', function() {
	    var dialogAria = $(this).next().attr('aria-describedby');        
	    $(quickDlg).dialog("close");
	});


	$("body").on("click",".copy-to-clip",function(){
	    var $temp = $("<input>");
	    $("body").append($temp);
	    $temp.val($(this).text()).select();
	    document.execCommand("copy");
	    $.notify("Copy!",{className:"success",showDuration: 100,autoHideDelay: 500});
	    $temp.remove();

	});

	$("body").on("click",".btn-sort-col",function(){
		objSortHead = $(this).closest(".row-header");
		if(objSortHead==undefined){$.notify("No row-header found"); return;}
		objSortBody = $(objSortHead).next(".row-body");
		if(objSortBody==undefined){$.notify("No row-body found"); return;}
		objRow = $(objSortBody).find(".row-data");
		if(objSortBody==undefined){return;}
		sColunm = $(this).attr('data-sort');
		if(objSortBody==undefined){$.notify("No data-sort found"); return;}
		sOrd = $(this).attr("data-sortord");
		if(sOrd==undefined || sOrd=="Z") $(this).attr("data-sortord","A");
		else if(sOrd=="A") $(this).attr("data-sortord","Z");

		sOrd = $(this).attr("data-sortord");

		iTot = 0;
		$(objRow).each(function(ix,objx){
			sV = $(objx).find('.'+sColunm);
			if(sV==undefined) return;
			else sV=$(sV).html().toUpperCase();

			var iRow = 1;
			$(objRow).each(function(ir,objv){
				sNV = $(objv).find("."+sColunm).html().toUpperCase();
				if(sOrd=="A" && sV > sNV)iRow++;
				else if(sOrd=="Z" && sV < sNV)iRow++;
			});
			$(objx).attr("data-order",iRow);
			iTot++;
		});

		for(ix=1;ix<=iTot;ix++){
			objTmp = $(objSortBody).find(".row-data[data-order='"+ix+"']");
			if(objTmp!=undefined) $(objSortBody).append(objTmp);
		}
	});
});


function fncBeforeClose(){
	bDataChange = true;

	$("#divDocPInfo"),"save-data"
	$("body").find(".before-close").each(function(ix,objx){
		clsSaveId = $(objx).attr('data-saveid');
		$(objx).find("."+clsSaveId).each(function(ir,objr){
			oldData = $(objr).attr('data-odata');
			newData = getOV($(objr));

		var sTagName = $(objr).prop("tagName").toUpperCase();
		if(sTagName=="INPUT"){
			if($(objr).prop("type")){
				if($(objr).prop("type").toLowerCase()=="checkbox"){
					if(oldData=="") oldData="0";
					if(newData=="") newData="0";
				}
			}
		}


			if(oldData != newData){
				//$.notify($(objr).attr('data-id')+":"+$(objr).attr('data-odata')+":"+getOV($(objr)));

				bDataChange =false;
			}
		});

	});


	if(bDataChange==false){
		return confirm("ละทิ้งข้อมูลที่เปลี่ยนแปลง?\r\n Ignore all data changed?");
	}else return true;

}

function checkEmail(sEmail){
	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	return (emailReg.test(sEmail));

}

function startLoad(objCont,objLoad){
	$(objCont).hide();
	$(objLoad).show();
}

function endLoad(objCont,objLoad){
	$(objCont).show();
	$(objLoad).hide();
}


function loadDivURL(sDivName,sPage,retFunction,doneLoadFunction){
	//$( window ).off("resize.windowsresize");
	var objDiv = "";
	if(jQuery.type(sDivName) == "object"){
		objDiv = sDivName;
	}else if(sDivName.indexOf("#") < 0){
		objDiv = "#"+sDivName;
	}else{
		objDiv = sDivName;
	}


	//$("#loading").focus();

	if(sPage.indexOf("?") >=0) sPage += "&caxx=" + (new Date()).getTime();
	else  sPage += "?caxx=" + (new Date()).getTime();

	if($(objDiv).length){

		$(objDiv).html("");
		$(objDiv).load(sPage,function(responseTxt, statusTxt, xhr){
			if(checkFunction(doneLoadFunction)) doneLoadFunction();

			if(statusTxt == "success"){
				//return true;
				if(responseTxt=="E99") {
					//showLogin();
					if(checkFunction(retFunction)) retFunction(false,objDiv);
				}
				if(checkFunction(retFunction)) retFunction(true);
			}else{
				$.notify( "Error "+statusTxt ,"error");
				//return false;
				if(checkFunction(retFunction)) retFunction(true);
			}

		});
		$(objDiv).show();
	}else{

	}


}
function checkFunction(functionName){
	if (typeof functionName == 'function') return true;
	else return false;
	}
function callAjax(saveURL,aData,retFunction){
	var request = $.ajax({
	  url: saveURL,
	  method: "POST",
	  cache:false,
	  data: aData,
	});

	request.done(function( retdata ) {
		if(retdata!="")	var rtnObj = jQuery.parseJSON( retdata );
		if(checkFunction(retFunction)) retFunction(rtnObj,aData);
	});
	 
	request.fail(function( jqXHR, textStatus ) {
	  	console.log(jqXHR.status);
	});
}

function callAjaxForm(saveURL,aData,retFunction){
	var request = $.ajax({
	  url: saveURL,
	  method: "POST",
	  cache:false,
	  data: aData,
	  contentType: false,  
	  processData: false
	});

	request.done(function( retdata ) {
		if(retdata!="")	var rtnObj = jQuery.parseJSON( retdata );
		if(checkFunction(retFunction)) retFunction(rtnObj,aData);
	});
	 
	request.fail(function( jqXHR, textStatus ) {
	  	console.log(jqXHR.status);
	});
}

function createDialog(objDlg,isModal,autoOpen){
	$(objDlg).dialog({ "modal": isModal,autoOpen:autoOpen,
		open: function(event, ui) {
			//$(".ui-dialog-titlebar-close", ui.dialog | ui).show();
			
		},
		close: function(event,ui){

		}
    });


}

function closeDlg(objThis="",sResult=""){
	if(objThis==""){
		var iDlg = 50; var bFinding = true; var objCurDlg = "";
		do {
			iDlg--;

			objCurDlg = ".divdlg[data-id='"+iDlg+"']";
			if($(objCurDlg).hasClass('ui-dialog-content')){
				bFinding=$(objCurDlg).dialog("isOpen");
				if(bFinding) {
					if(sResult!="") $(objCurDlg).find(".dlgResult").val(sResult);
					$(objCurDlg).dialog("close");
				}
			}
		}
		while (bFinding && iDlg > 0);
	}else{
		dlgThis = $(objThis).closest(".divdlg");
		iDlgId = $(dlgThis).attr("data-id");
		if(sResult!="") 
			$(dlgThis).find(".dlgResult").val(sResult);
		$(dlgThis).dialog("close");
	}
}

function setDlgResult(sResult,objThis=""){
	if(objThis==""){
		var iDlg = 50; var bFinding = true; var objCurDlg = "";
		do {
			iDlg--;
			objCurDlg = ".divdlg[data-id='"+iDlg+"']";
			if($(objCurDlg).hasClass('ui-dialog-content')){
				bFinding=$(objCurDlg).dialog("isOpen");
				if(bFinding) $(objCurDlg).find(".dlgResult").val(sResult);
			}
		}
		while (bFinding && iDlg > 0);
	}else{
		dlgThis = $(objThis).closest(".divdlg");
		if($(dlgThis).length){
			iDlgId = $(dlgThis).attr("data-id");
			$(dlgThis).find(".dlgResult").val(sResult);
		}else{
			bFinding =false;
		}
	}
	return bFinding;
}

function getDlgResult(objThis=""){
	var iDlg = 50; var bFinding = true; var objCurDlg = "";
	if(objThis==""){
		do {
			iDlg--;

			objCurDlg = ".divdlg[data-id='"+iDlg+"']";
			if($(objCurDlg).hasClass('ui-dialog-content')){
				bFinding=$(objCurDlg).dialog("isOpen");
			}
		}
		while (bFinding && iDlg > 0);
	}else{
		dlgThis = $(objThis).closest(".divdlg");
		if($(dlgThis).length){
			iDlgId = $(dlgThis).attr("data-id");
			return $(dlgThis).find(".dlgResult").val();
		}else{
			bFinding =false;
		}
	}
	if(bFinding) {
		return $(objCurDlg).find(".dlgResult").val();
	}else{
		return "";
	}

}

function showPopup(htmlCode,dlgTitle,dlgHeight,dlgWidth,closeHide){
	quickDlg=$("body #quickDlg").dialog({ "modal": true,autoOpen:true,"height":dlgHeight,"width":dlgWidth,
		open: function(event, ui) {
			//$(".ui-dialog-titlebar-close", ui.dialog | ui).show();

			$("body #quickDlg").html(htmlCode);
			$("body #quickDlg").closest(".ui-dialog").find(".ui-dialog-titlebar").hide();
		},
		close: function(event,ui){
			$("body #quickDlg").html("");
			$(quickDlg).dialog("destroy");
		}
    });

}

function showDialog(dlgPage,dlgTitle,dlgHeight,dlgWidth,dlgCss,closeFunction,closeHide,doneLoadFunction,isModal=true,posX="",posY="",dlgName=""){

	var iDlg = 0; var bFinding = true; var objCurDlg = "";

	if(dlgName!=""){
		objName = $(".divdlg[data-dlgname='"+dlgName+"']");
		if($(objName).length){
			objCurDlg = objName;
			iDlg=$(objName).attr('data-id');
		}else{
			bFinding=false;
		}
	}

	if(objCurDlg==""){
		do {
			iDlg++;
			objCurDlg = ".divdlg[data-id='"+iDlg+"']";
			if($(objCurDlg).hasClass('ui-dialog-content')){
				bFinding=$(objCurDlg).dialog("isOpen");
			}else{
				bFinding=false;
				let sDlg = "<div title='dialog' class='divdlg dbmsfontfamily fl-wrap-col' data-id='"+iDlg+"' data-dlgname='"+dlgName+"' ><div class='fl-fill divloading fl-mid'><img src='assets/image/spinner.gif' /></div><input type='hidden' class='dlgResult' /><input type='hidden' class='dlgData' /><div class='dlgContent fl-wrap-col'></div></div>";
				$("body").append(sDlg);
				//$(".divdlg[data-id='"+iDlg+"']").asortable('refresh');
				createDialog($(".divdlg[data-id='"+iDlg+"']"),isModal,false);
			}
		}while (bFinding);
	}


	if(dlgCss != "") $(objCurDlg).css(dlgCss);
	const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
	const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

	oHt = dlgHeight;
	oWt = dlgWidth;

	if(dlgHeight.indexOf("%") >=0){
		iH = dlgHeight.replace("%","");
		dlgHeight = Math.floor(vh*(iH/100));

		//dlgHeight = vh;
		//alert(vh);
		//ixHeight = dlgHeight.replace("%","");
		//dlgHeight = ($("#pribta21").height()*(ixHeight/100));
	}

	if(dlgWidth.indexOf("%") !== false){
		//ixWidth = dlgWidth.replace("%","");
		//dlgWidth = ($("#pribta21").width()*(ixWidth/100));
	}


	



	$(objCurDlg).siblings().find(".ui-dialog-title").html((dlgTitle));
	$(objCurDlg).siblings().find(".ui-dialog-title").css("text-align","left");

	$(objCurDlg).prev(".ui-dialog-titlebar").css({"background-color":"#0059b3","color":"white","font-size":"14px"});

	/*
	if(closeHide || !isModal){
		$(objCurDlg).closest("div[role='dialog']").find(".ui-dialog-titlebar-close").hide();
	}else{
		$(objCurDlg).closest("div[role='dialog']").find(".ui-dialog-titlebar-close").show();
	}
	*/

	$(objCurDlg).css({"background-color":"#white"});
	$(objCurDlg).off("dialogclose");
	if(checkFunction(closeFunction)) {
		$(objCurDlg).dialog({
		   close: function(event, ui) 
		    { 
		    	$(objCurDlg).dialog( 'option', 'hide', 'none' );
		    	return closeFunction($(objCurDlg).find(".dlgResult").val(),dlgPage); 
			}
		});
	}

	dlgPage += ((dlgPage.indexOf("?") >=0 )?"&":"?")+"dlg="+iDlg;
	$(objCurDlg).find(".dlgResult").val("");

	

	if(posX!=""){
		$(objCurDlg).dialog({"position":{ my: "top left", at: "left top", of: window },"height":dlgHeight,"width":dlgWidth, closeOnEscape: !closeHide});
		
	}else{
		$(objCurDlg).dialog({"position":{ my: "center", at: "center", of: window },"height":dlgHeight,"width":dlgWidth, closeOnEscape: !closeHide});
	}
	
	/*
	if(posX=="") $(objCurDlg).dialog("option","position",{ my: "center", at: "center", of: window });
	else $(objCurDlg).dialog("option","position",{ my: "top left", at: "left+"+posX+" top+"+posY });
	*/
	$(objCurDlg).dialog( "option", "modal", isModal ).dialog("close").dialog("open");

	//$(objCurDlg).dialog("open");
	$(objCurDlg).find(".divloading").show();
	$(objCurDlg).closest(".ui-dialog").hide();


	loadDivURL($(objCurDlg).find(".dlgContent"),dlgPage,function(bResult){
		//if(bResult) $(objCurDlg).dialog("open");
		oDlg= $(objCurDlg).closest(".ui-dialog");
		if(posX!=""){
			$(oDlg).css("top",posY);
			$(oDlg).css("left",posX);
		}
		$(oDlg).show();
		$(objCurDlg).find(".divloading").hide();
		if(oHt.indexOf("%")>0){
			$(oDlg).css("width",oWt);
			$(oDlg).css("height",oHt);
		}
		//$(objCurDlg).closest(".ui-dialog").find(".ui-dialog-titlebar-close").show();
	},doneLoadFunction);

}

function showDialog_edit(dlgPage,dlgTitle,dlgHeight,dlgWidth,dlgCss,closeFunction,closeHide,doneLoadFunction,isModal=true,posX="",posY="",dlgName=""){

	var iDlg = 0; var bFinding = true; var objCurDlg = "";

	if(dlgName!=""){
		objName = $(".divdlg[data-dlgname='"+dlgName+"']");
		if($(objName).length){
			objCurDlg = objName;
			iDlg=$(objName).attr('data-id');
		}else{
			bFinding=false;
		}
	}

	if(objCurDlg==""){
		do {
			iDlg++;
			objCurDlg = ".divdlg[data-id='"+iDlg+"']";
			if($(objCurDlg).hasClass('ui-dialog-content')){
				bFinding=$(objCurDlg).dialog("isOpen");
			}else{
				bFinding=false;
				let sDlg = "<div title='dialog' class='divdlg dbmsfontfamily fl-wrap-col' data-id='"+iDlg+"' data-dlgname='"+dlgName+"' ><div class='fl-fill divloading fl-mid'><img src='assets/image/spinner.gif' /></div><input type='hidden' class='dlgResult' /><input type='hidden' class='dlgData' /><div class='dlgContent fl-wrap-col' style='padding: 10px 5px 10px 5px;'></div></div>";
				$("body").append(sDlg);
				//$(".divdlg[data-id='"+iDlg+"']").asortable('refresh');
				createDialog($(".divdlg[data-id='"+iDlg+"']"),isModal,false);
			}
		}while (bFinding);
	}


	if(dlgCss != "") $(objCurDlg).css(dlgCss);
	const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
	const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

	oHt = dlgHeight;
	oWt = dlgWidth;

	if(dlgHeight.indexOf("%") >=0){
		iH = dlgHeight.replace("%","");
		dlgHeight = Math.floor(vh*(iH/100));

		//dlgHeight = vh;
		//alert(vh);
		//ixHeight = dlgHeight.replace("%","");
		//dlgHeight = ($("#pribta21").height()*(ixHeight/100));
	}

	if(dlgWidth.indexOf("%") !== false){
		//ixWidth = dlgWidth.replace("%","");
		//dlgWidth = ($("#pribta21").width()*(ixWidth/100));
	}


	



	$(objCurDlg).siblings().find(".ui-dialog-title").html((dlgTitle));
	$(objCurDlg).siblings().find(".ui-dialog-title").css("text-align","left");

	$(objCurDlg).prev(".ui-dialog-titlebar").css({"background-color":"#0059b3","color":"white","font-size":"14px"});

	if(closeHide || !isModal){
		$(objCurDlg).closest("div[role='dialog']").find(".ui-dialog-titlebar-close").hide();
	}else{
		$(objCurDlg).closest("div[role='dialog']").find(".ui-dialog-titlebar-close").show();
	}

	$(objCurDlg).css({"background-color":"#white"});
	$(objCurDlg).off("dialogclose");
	if(checkFunction(closeFunction)) {
		$(objCurDlg).dialog({
		   close: function(event, ui) 
		    { 
		    	$(objCurDlg).dialog( 'option', 'hide', 'none' );
		    	return closeFunction($(objCurDlg).find(".dlgResult").val(),dlgPage); 
			}
		});
	}

	dlgPage += ((dlgPage.indexOf("?") >=0 )?"&":"?")+"dlg="+iDlg;
	$(objCurDlg).find(".dlgResult").val("");

	

	if(posX!=""){
		$(objCurDlg).dialog({"position":{ my: "top left", at: "left top", of: window },"height":dlgHeight,"width":dlgWidth, closeOnEscape: !closeHide});
		
	}else{
		$(objCurDlg).dialog({"position":{ my: "center", at: "center", of: window },"height":dlgHeight,"width":dlgWidth, closeOnEscape: !closeHide});
	}
	
	
	if(posX=="") $(objCurDlg).dialog("option","position",{ my: "center", at: "center", of: window });
	else $(objCurDlg).dialog("option","position",{ my: "top left", at: "left+"+posX+" top+"+posY });
	
	$(objCurDlg).dialog( "option", "modal", isModal ).dialog("close").dialog("open");

	//$(objCurDlg).dialog("open");
	$(objCurDlg).find(".divloading").show();
	$(objCurDlg).closest(".ui-dialog").hide();


	loadDivURL($(objCurDlg).find(".dlgContent"),dlgPage,function(bResult){
		//if(bResult) $(objCurDlg).dialog("open");
		oDlg= $(objCurDlg).closest(".ui-dialog");
		if(posX!=""){
			$(oDlg).css("top",posY);
			$(oDlg).css("left",posX);
		}
		$(oDlg).show();
		$(objCurDlg).find(".divloading").hide();
		if(oHt.indexOf("%")>0){
			$(oDlg).css("width",oWt);
			$(oDlg).css("height",oHt);
		}
		//$(objCurDlg).closest(".ui-dialog").find(".ui-dialog-titlebar-close").show();
	},doneLoadFunction);

}


function showLogin(){
	let sUrl = "login_inc.php?hidelogo=1";
	showDialog(sUrl,"Staff Login","440","420","",function(sResult){
		//CLose function
		if(sResult=="1"){
			//$("#isLogin").val("1");
		}
	},true,function(){
		//Load Done Function

	});
}

function resetRowColor(objRows){
	$(objRows).removeClass("row-odd");
	$(objRows).removeClass("row-even");
	$(objRows).filter(":odd").addClass("row-odd");
	$(objRows).filter(":even").addClass("row-even");
}
function setKeyVal(objdiv,keyid,sVal,setOData=true,clsinput="saveinput"){
	objx = $(objdiv).find("."+clsinput+"[data-keyid='"+keyid+"']");
	if($(objx).length){
		var sTagName = $(objx).prop("tagName").toUpperCase();
		if(sTagName=="INPUT"){
			if($(objx).prop("type")){
				if($(objx).prop("type").toLowerCase()=="checkbox"){
					if(sVal) $(objx).prop("checked",true);
					else $(objx).prop("checked",false); 
					if(setOData) $(objx).attr('data-odata',((sVal)?"1":"0"));					
				}else if($(objx).prop("type").toLowerCase()=="radio"){

					if(sVal=="") $(objdiv).find("input[name='"+keyid+"']:checked").attr("checked",false);
					else $(objdiv).find("input[name='"+keyid+"'][value='"+sVal+"']").attr("checked",true);
					if(setOData) $(objdiv).find("."+clsinput+"[data-keyid='"+keyid+"']").attr('data-odata',sVal);
				}else{
					$(objx).val(sVal);
					if(setOData) $(objx).attr('data-odata',sVal);
				}
			}else{
				$(objx).val(sVal);
				if(setOData) $(objx).attr('data-odata',sVal);
			}
		}else if(sTagName=="SPAN"){
			$(objx).html(sVal);
		}else{
			$(objx).val(sVal);
			if(setOData) $(objx).attr('data-odata',sVal);
		}
	}
}
function getKeyVal(objdiv,keyid,clsinput="saveinput"){
	var sValue ="";
	objx = $(objdiv).find("."+clsinput+"[data-keyid='"+keyid+"']");

	if($(objx).length){
		var sTagName = $(objx).prop("tagName").toUpperCase();
		if(sTagName=="SPAN") return "";
		var sValue = $(objx).val();
		if(sTagName=="INPUT"){
			if($(objx).prop("type")){
				if($(objx).prop("type").toLowerCase()=="checkbox"){
					sValue = ($(objx).is(":checked") )?"1":"0";
				}else if($(objx).prop("type").toLowerCase()=="radio"){
					sValue = $(objdiv).find("input[name='"+keyid+"']:checked").val();
					if(sValue==undefined) sValue="";
				}
			}
		}
	}
	return sValue;
}
function getKeyObj(objdiv,keyid,clsinput="saveinput"){
	objx = $(objdiv).find("."+clsinput+"[data-keyid='"+keyid+"']");
	return objx;
}
function getKeyOldVal(objdiv,keyid,clsinput="saveinput"){
	objx = $(objdiv).find("."+clsinput+"[data-keyid='"+keyid+"']");
	var sValue = "";
	if($(objx).length){
		sValue = $(objx).attr("data-odata");
	}
	return sValue;
}
function setKeyAllOld(objdiv,clsinput="saveinput"){
	$(objdiv).find("."+clsinput).each(function(ix,objx){
		sKey = $(objx).attr('data-keyid');
		sVal = getKeyVal(objdiv,sKey,clsinput);
		$(objx).attr('data-odata',sVal);
	});
}
function setOV(objx,sVal=""){
	if($(objx).length){
		var sTagName = $(objx).prop("tagName").toUpperCase();
		if(sTagName=="INPUT"){

			if($(objx).prop("type")){
				if($(objx).prop("type").toLowerCase()=="checkbox"){
					if(sVal) $(objx).prop("checked",true);
					else $(objx).prop("checked",false); 					
				}else if($(objx).prop("type").toLowerCase()=="radio"){
					var sName = $(objx).attr("name");
					$(objx).parent().find("input[name='"+sName+"']").attr("checked",true);
				}else{
					$(objx).val(sVal);
				}
			}else{
				$(objx).val(sVal);
			}
		}else{
			$(objx).val(sVal);
		}
	}
}
function getOV(objx,clsinput='saveinput'){
	var sValue ="";

	if($(objx).length){
		var sValue = $(objx).val();

		var sTagName = $(objx).prop("tagName").toUpperCase();
		if(sTagName=="INPUT"){
			if($(objx).prop("type")){
				if($(objx).prop("type").toLowerCase()=="checkbox"){
					sValue = ($(objx).is(":checked") )?"1":"0";

				}else if($(objx).prop("type").toLowerCase()=="radio"){
					var sName = $(objx).attr("name");
					sValue = ( $(objx).parent().find("input[name='"+sName+"']").filter(":checked").length > 0 )? $(objx).parent().find("input[name='"+sName+"']").filter(":checked").val():$(objx).attr("data-odata");
				}
			}
		}
	}
	return sValue;
}

function isObjChanged(objx){
	sValue = getOV(objx);

}
function setODataRow(inputRow,clsinput="saveinput"){
	$(inputRow).find("."+clsinput).each(function(ix,objx){
		sVal = getOV(objx);
		$(objx).attr("data-odata",sVal);
		
	});
}
function getAllData(inputRow,clsinput="saveinput"){
	let aData={};
	$(inputRow).find("."+clsinput).each(function(ix,objx){
		sKey=$(objx).attr("data-keyid");
		sVal=getKeyVal(inputRow,sKey,clsinput);
		aData[sKey]=sVal;
	});
	return aData;
}
function getAllDataChanged(inputRow,clsinput="saveinput"){
	let aData={};
	$(inputRow).find("."+clsinput).each(function(ix,objx){
		sKey=$(objx).attr("data-keyid");
		sVal=getKeyVal(inputRow,sKey,clsinput);
		sOVal=getKeyOldVal(inputRow,sKey,clsinput);
		if(sVal!=sOVal && sOVal != undefined)	{
			aData[sKey]=sVal;
		}
	});
	if(Object.keys(aData).length==0) aData="";
	return aData;
}
/*
function isDataChanged(inputRow,keyid,clsinput="saveinput"){
	objRow=$(inputRow).find("."+clsinput+"[data-keyid='"+keyid+"']");
	if($(objRow).length!==false){
		sVal = getKeyVal(inputRow,keyid,clsinput);
		var sTagName = $(objRow).prop("tagName").toUpperCase();
		if(sTagName=="SPAN"){
			return false;
		}else if(sVal!=decodeURIComponent($(objRow).attr('data-odata'))){
			return true;
		}
	}else{
		return false;
	}
}
*/

//Use this one for row data.
function getDataRow(inputRow,clsinput="saveinput",pkonly=false){
	let aData={}; let sCol=""; let sColPk=""; let isValid =true;
	//PK only
	$(inputRow).find("."+clsinput).each(function(ix,objx){

		sKey = $(objx).attr('data-keyid');
		sIsPK = $(objx).attr("data-pk");
		sVal = getKeyVal(inputRow,sKey,clsinput);
		var sTagName = $(objx).prop("tagName").toUpperCase();

		if(sTagName=="SPAN"){

		}else if(sIsPK==undefined || sIsPK==""){
			if(sVal!=$(objx).attr('data-odata')){
				sCol+= ((sCol=="")?"":",")+sKey;
				aData[sKey] = sVal;
			}
		}else{
			//Primary Key can't be none.
			sColPk+= ((sColPk=="")?"":",")+sKey;
			aData[sKey] = sVal;
			if(sVal=="") {
				isValid=false;
				console.log(sKey+": cannot be blank.");
			}
			//$.notify("Key:"+sCol);
		}
	});
	if(sColPk!="") aData["colpk"] = sColPk;
	if(sCol!="" && isValid) aData["col"] = sCol;
	else if(pkonly){
	}else{
		aData="";
	} 
	return aData;
}

function qsTxt(sUid,sColDate,sColTime){
	return "uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime;
}
function qsTitle(sUid,sColDate,sColTime){
	return "UID :"+sUid+"| Date :"+sColDate +"| Time :"+sColTime;
}

function pribtaDecode(sTxt){
	sResult= (sTxt).replace(/\+/g,"%20");
	sResult= decodeURI(sResult);
	sResult= (sResult).replace(/%2B/g,"+");
	return sResult;
}

function getWaitList(sQ,sUid,sColD,sColT,sName=""){
	sHtml="<div class='fabtn btn-q-info fl-wrap-col row-color-2 h-75 row-hover q-row' data-coldate='"+sColD+"' data-queue='"+sQ+"'data-uid='"+sUid+"' data-coltime='"+sColT+"'><div class='h-30 fl-fix fl-mid fs-xlarge'>"+sQ+"</div><div class='h-15 fl-fix fl-mid fs-small'>"+sUid+"</div><div class='fl-fill lh-15 fs-small fl-mid fw-b' style='text-align:center'>"+sName+"</div></div>";

	return sHtml;
}

function getCookieValue(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}