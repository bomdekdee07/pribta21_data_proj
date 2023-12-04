/*

function loadLink(sUrl, selector_dest, selector_loader){

//	console.log("loadlink99: "+sUrl);
	startLoad(selector_dest,selector_loader);
	selector_dest.html("");
	selector_dest.load(sUrl,function(){
		endLoad(selector_dest,selector_loader);
	});

}
*/


function callAjaxFileUpload(saveURL,aData,retFunction){
	var request = $.ajax({
	  url: saveURL,
	  method: "POST",
	  cache:false,
	  data: aData,
		contentType: false,
		processData: false,
	});

	request.done(function( retdata ) {
		if(retdata!="")	var rtnObj = jQuery.parseJSON( retdata );
		if(checkFunction(retFunction)) retFunction(rtnObj,aData);
	});

	request.fail(function( jqXHR, textStatus ) {
	  	console.log(jqXHR.status);
	});
}

function setDivAuthComponent(divSelector){

	if(!$(divSelector).hasClass('allow_view'))
	$(divSelector+' .auth-view').hide();

	if(!$(divSelector).hasClass('allow_data')){
		$(divSelector+' .auth-data').hide();
	}

  if(!$(divSelector).hasClass('allow_admin')){
    $(divSelector+' .auth-admin').hide();
  }

}


function getWDataCompValue(obj){
	var sValue = "";
  if($(obj)){
	//	console.log("obj id: "+$(obj).attr('data-id'));
    var sTagName = $(obj).prop("tagName").toUpperCase();
    if(sTagName=="INPUT"){
      if($(obj).prop("type")){
        if($(obj).prop("type").toLowerCase()=="checkbox"){
          sValue = ($(obj).prop("checked"))?1:0;
        }else if($(obj).prop("type").toLowerCase()=="radio"){
          var sName = $(obj).attr("name");

        //  sValue = ( $(obj).parent().find("input[name='"+sName+"']").filter(":checked").length > 0 )? $(obj).parent().find("input[name='"+sName+"']").filter(":checked").val():"";
					sValue = ( $("input[name='"+sName+"']").filter(":checked").length > 0 )? $("input[name='"+sName+"']").filter(":checked").val():"";
//console.log("obj name: "+$(obj).attr('name')+" / value: "+sValue);
	        //console.log("obj id: "+$(obj).attr('data-id')+" / value: "+sValue);
		    }
				else{
					if($(obj).hasClass("v-date-th")){ // change to en date eg. 2021-07-15
						sValue = changeTh2EnDate($(obj).val());
					}else{
						sValue = $(obj).val();
					}

        }
      }else{
        sValue = $(obj).val();
      }
    }else{
      sValue = $(obj).val();
    }
  }
  return sValue;
}



function getWODataComp(obj){ // get odata

	var sValue = "";
  if($(obj)){

    var sTagName = $(obj).prop("tagName").toUpperCase();
    if(sTagName=="INPUT"){
      if($(obj).prop("type")){
        if($(obj).prop("type").toLowerCase()=="radio"){
          var sName = $(obj).attr("name");

          sValue =  $(obj).parent().find("input[name='"+sName+"']").attr("data-odata");
        }else{
          sValue = $(obj).attr("data-odata");
        }
      }else{
        sValue = $(obj).attr("data-odata");
      }
    }else{
      sValue = $(obj).attr("data-odata");
    }
  }
  return sValue;
}


function setWODataComp(obj, val){ // set odata
  if($(obj)){
		$(obj).attr("data-odata", val);
  }
}


function checkValidData(obj){ // check valid data in data component
	let sValue = $(obj).val();
	let flag_valid = true;
  if($(obj)){
		if(sValue != ''){
			if($(obj).hasClass('v-date')) {
				if($(obj).hasClass('v-date-partial'))
				flag_valid = checkDateEnComp($(obj), true);
				else flag_valid = checkDateEnComp($(obj), false);
			}
			else if($(obj).hasClass('v-date-th')){
				if($(obj).hasClass('v-date-partial')) flag_valid = checkDateThComp($(obj), true);
				else flag_valid = checkDateThComp($(obj), false);
			}
			else if($(obj).hasClass('v-number') && !validateNumber(sValue)) flag_valid = false;
		}
		else{ // blank value
			//if($(obj).attr('data-isrequire') == '1') flag_valid = false;
		}

	}
  //console.log("checkValidData "+$(obj).attr('data-id')+' : '+$(obj).attr('class')+' : '+sValue+' - '+flag_valid);

	return flag_valid;
}


//check date en component
function checkDateEnComp(chkDateEn,  isPartial=false){ // yyyy-mm-dd eg. 2021-09-30
  let flag_valid = true;
  let dateVal = $(chkDateEn).val();
  dateVal = dateVal.replace("d", "");

  let y =""; let m=""; let d="";
  let dateEn = dateVal.split("-");
  if(dateEn.length==3){
    y = dateEn[0];  m = dateEn[1]; d = dateEn[2];
    if(m.length == 1) m=m.padStart(2, '0');
    if(d.length == 1) d=d.padStart(2, '0');

    let dateVal = y+'-'+m+'-'+d;
    if(checkDateFormat(dateVal)){
      if(y > 1900){
        if (y > 2400)	y = y-543;

        dateVal = y+'-'+m+'-'+d;
        if(isPartial){ // partial date
          if(parseInt(m) > 12) {
						flag_valid = false;
						$(chkDateEn).notify('เดือนไม่ถูกต้อง | Invalid month', 'warning');
					}
					else if(parseInt(d) > 31) {
						flag_valid = false;
						$(chkDateEn).notify('วันที่ไม่ถูกต้อง | Invalid date', 'warning');
					}
        }//partial
        else{ // ordinary date
          if (validateOrinaryDate(dateVal)){
          }
          else{
            flag_valid = false;
            $(chkDateEn).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
          }
        }// ordinary
        $(chkDateEn).val(dateVal);
      }
      else{
        flag_valid = false;
        $(chkDateEn).notify('ปีไม่ถูกต้อง | Invalid year', 'warning');
      }
    }
    else{
      flag_valid = false;
      $(chkDateEn).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
    }
  }
  else{
    flag_valid = false;
    $(chkDateEn).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
  }

  //console.log('checkDateEnComp '+flag_valid);
  return flag_valid;
}

//check date th component
function checkDateThComp(chkDateTh,  isPartial=false){ // dd/mm/yyyy eg. 30/09/2564
  let flag_valid = true;
  let dateVal = $(chkDateTh).val();
  dateVal = dateVal.replace("d", "");
  dateVal = dateVal.replace("m", "");

  let y =""; let m=""; let d="";
  let dateTh = dateVal.split("/");
  if(dateTh.length==3){
    y = dateTh[2];  m = dateTh[1]; d = dateTh[0];
    if(m.length == 1) m=m.padStart(2, '0');
    if(d.length == 1) d=d.padStart(2, '0');

    let dateVal = d+'/'+m+'/'+y;
    if(checkDateFormatTh(dateVal)){
      y_en = y-543;
      if(y < 2400){ // input is BC year
        y_en = y;
        y = parseInt(y)+543;
        dateVal = d+'/'+m+'/'+y;
      }
        dateVal_en = y_en+'-'+m+'-'+d;
				if(isPartial){ // partial date
          if(parseInt(m) > 12) {
						flag_valid = false;
						$(chkDateEn).notify('เดือนไม่ถูกต้อง | Invalid month', 'warning');
					}
					else if(parseInt(d) > 31) {
						flag_valid = false;
						$(chkDateEn).notify('วันที่ไม่ถูกต้อง | Invalid date', 'warning');
					}
        }//partial
        else{ // ordinary date
          if (validateOrinaryDate(dateVal_en)){
          }
          else{
            flag_valid = false;
            $(chkDateTh).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
          }
        }// ordinary
        $(chkDateTh).val(dateVal);
    }
    else{
      flag_valid = false;
      $(chkDateTh).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
    }
  }
  else{
    flag_valid = false;
    $(chkDateTh).notify('ข้อมูลวันที่ไม่ถูกต้อง | Invalid date', 'warning');
  }

  //console.log('checkDateThComp '+flag_valid);
  return flag_valid;
}
function validateOrinaryDate(sDate){ // format yyyy-mm-dd
  var d = new Date(sDate);
  var dNum = d.getTime();
  if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
  return d.toISOString().slice(0,10) === sDate;
}

function checkDateFormat(sDate){ // format yyyy-mm-dd
	var regEx = /^\d{4}-\d{2}-\d{2}$/;
  if(!sDate.match(regEx)) return false;
	else return true;
}
function checkDateFormatTh(sDate){ // format dd/mm/yyyy
	var regEx = /^\d{2}\/\d{2}\/\d{4}$/;
  if(!sDate.match(regEx)) return false;
	else return true;
}



// check filter only unique array item   eg. lst_data_obj = lst_data_obj.filter(onlyUnique);
function onlyUnique(value, index, self) {
	return self.indexOf(value) === index;
}

function validateFileType(fileComp){
	let flag_valid = true;
	let fileType = $(fileComp).attr('data-filetype');
	//console.log("file type "+$(fileComp).attr('data-filetype'));

  if($(fileComp)[0].files[0].size > 5120000){ // file size limit 5 MB
		$(fileComp).notify("ไฟล์มีขนาดเกินกำหนด 5 MB | File size limit is only 5MB.", "info");
		flag_valid = false;
	}
	else{
		let fileExtension = [];


		if(fileType == 'fileimage') fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
		else if(fileType == 'filepdf') fileExtension = ['pdf'];

		if ($.inArray($(fileComp).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
			flag_valid = false;
			$(fileComp).notify("ไฟล์ที่ใช้คือ | File formats are allowed : "+fileExtension.join(', '), "info");
		}
	}
	return flag_valid;
}










function validateDate(sDate){ // format yyyy-mm-dd
  var regEx = /^\d{4}-\d{2}-\d{2}$/;
  if(!sDate.match(regEx)) return false;  // Invalid format
  var d = new Date(sDate);
  var dNum = d.getTime();
  if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
  return d.toISOString().slice(0,10) === sDate;
}

function validateDateTH(sThDate){ // format dd/mm/yyyy
  var sDate = changeTh2EnDate(sThDate);
  var d = new Date(sDate);
  var dNum = d.getTime();
  if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
  return d.toISOString().slice(0,10) === sDate;
}

function validatePartialDate(sDate){ // format yyyy-mm-dd
  var regEx = /^\d{4}-\d{2}-\d{2}$/;
  if(!sDate.match(regEx)) return false;
	else return true;
}


function validateNumber(sValue) {
  if (typeof sValue != "string") return false // we only process strings!
  return !isNaN(sValue) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
         !isNaN(parseFloat(sValue)) // ...and ensure strings of whitespace fail
}

function getJS_EnDate(sEnDate){ // eg. 2021-07-15
	let sDate = new Date();
	aT = sEnDate.split("-");
	if(aT.length==3){
		sDate.setDate(aT[2]);
    sDate.setMonth(aT[1]-1);
    sDate.setYear(aT[0]);
	}
	return sDate;
}
function getJS_ThDate(sThDate){ // eg. 15/07/2564
	let sDate = new Date();
	aT = sThDate.split("/");
	if(aT.length==3){
		sDate.setDate(aT[0]);
    sDate.setMonth(aT[1]-1);
    sDate.setYear(aT[2]-543);
	}
	return sDate;
}
/*
function getDCDate(sBCDate){
	sTemp=sBCDate;
	aT = sTemp.split("-");
	if(aT.length==3){
		if(aT[0]>2400){
			sTemp = (aT[0]-543)+"-"+aT[1]+"-"+aT[2];

		}
	}
	return sTemp;
}
*/

// eg. change 05/07/2564 to 2021-07-05
function changeTh2EnDate(sThDate){
	let sTemp="";
	aT = sThDate.split("/");
	if(aT.length==3){
		if(aT[2]>2400){
			sTemp = (aT[2]-543)+"-"+aT[1]+"-"+aT[0];
		}
		else{
			sTemp = aT[2]+"-"+aT[1]+"-"+aT[0];
		}
	}

	return sTemp;
}
// eg. change 2021-07-05 to 05/07/2564
function changeEn2ThDate(sEnDate){
	let sTemp="";
	aT = sEnDate.split("-");
	if(aT.length==3){
			sTemp = aT[2]+"/"+aT[1]+"/"+(parseInt(aT[0])+543);
	}
	return sTemp;
}

function getShowText(sObjValue){
	var	result = "";

	if(sObjValue){
		skey = new RegExp(/&/i,'g');
		result = sObjValue.replace(skey,"&#38;");
		skey = new RegExp(/'/i,'g');
		result = result.replace(skey,"&#39;");

		skey = new RegExp(/"/i,'g');
		result = result.replace(skey,"&#34;");

		skey = new RegExp(/</i,'g');
		result = result.replace(skey,"&#60;");

		skey = new RegExp(/>/i,'g');
		result = result.replace(skey,"&#62;");

		skey = new RegExp(/ /i,'g');
		result = result.replace(skey,"&#32;");
	}
	result = decodeURIComponent(result.replace(/\+/g, ' '));

	return result;
}
