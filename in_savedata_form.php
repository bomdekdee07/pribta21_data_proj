<script>

function save_data_ajax(objData, pageURL, returnFunc){
  //alert("save_data_ajax: "+returnFunc);
  //alert("pageURL: "+pageURL);

//  loadingShow();

  var flag_save_success = true;
  var request = $.ajax({
      url:pageURL,
      type:'POST',
      data:objData, // should be consisted of u_mode and any data to update

      success: function(result) {
    //  alert("successx: "+result);
      console.log("successx: "+result);

    //  loadingHide();

       var rtnObj = jQuery.parseJSON( result );
       if(typeof rtnObj.flag_auth !== "undefined"){

         //alert("flag_auth : "+rtnObj.flag_auth);
         if(rtnObj.flag_auth == '0'){

      //     myModalDlgLogin("te", "Time Expired");
           /*
           alert("session expired / Please Login to system");
           gotoLogin();
           */ 

         }
       }
        //alert ("rtnObj : "+rtnObj.msg_error+" / "+rtnObj.msg_info);

       if(rtnObj.msg_error == ""){
         flag_save_success = true;

         if(rtnObj.msg_info != ""){
           /*
           myModalContent("Information",
           rtnObj.msg_info,
           "info");
           */
           $.notify(rtnObj.msg_info, "info");

         }
       } //msg_err == ""
       else {
         //alert(""+rtnObj.msg_error);
         flag_save_success = false;
         if(rtnObj.msg_error == "session_expired"){
           //sessionExpired();
           $.notify(rtnObj.msg_error, "error");
         }
         else{
           /*
           myModalContent("Error",
           rtnObj.msg_error,
           "info");
           */

           $.notify(rtnObj.msg_error, "error");
         }


       }

        returnFunc(flag_save_success, rtnObj, objData );

     }, // end success
      error: function(xhr){
      //  loadingHide();
        /*
        myModalContent("Error",
        "xhr.status : "+xhr.status,
        "info");
        */
        alert("error: "+xhr.status);
        returnFunc(flag_save_success, xhr.status, objData );
      }
  });

 }


 function save_data_ajax_silent(objData, pageURL, returnFunc){

   var request = $.ajax({
       url:pageURL,
       type:'POST',
       data:objData, // should be consisted of u_mode and any data to update

       success: function(result) {
     //  alert("successx: "+result);

        var rtnObj = jQuery.parseJSON( result );
        if(typeof rtnObj.flag_auth !== "undefined"){

          //alert("flag_auth : "+rtnObj.flag_auth);
          if(rtnObj.flag_auth == '0'){

          //  myModalDlgLogin("te", "Time Expired");

          }
        }
         //alert ("rtnObj : "+rtnObj.msg_error+" / "+rtnObj.msg_info);

        if(rtnObj.msg_error == ""){
          flag_save_success = true;

          if(rtnObj.msg_info != ""){
            $.notify(rtnObj.msg_info, "info");
          }
        } //msg_err == ""
        else {
          //alert(""+rtnObj.msg_error);
          flag_save_success = false;
          if(rtnObj.msg_error == "session_expired"){
            $.notify(rtnObj.msg_error, "error");
          }
          else{

            $.notify(rtnObj.msg_error, "error");
          }


        }

         returnFunc(flag_save_success, rtnObj, objData );

      }, // end success
       error: function(xhr){

         alert("error: "+xhr.status);
         returnFunc(flag_save_success, xhr.status, objData );
       }
   });

  }


function urlEncode(urlText){
  if (typeof urlText === "undefined") return "";
  else if(urlText=="0") return "0";
  else if(urlText==false) return "";
  else if(urlText=="") return "";
  urlText =	encodeURIComponent(urlText);
  urlText = urlText.toString().replace(/!/g, '%21');
    urlText = urlText.toString().replace(/'/g, '%27');
    urlText = urlText.toString().replace(/\(/g, '%28');
    urlText = urlText.toString().replace(/\)/g, '%29');
    urlText = urlText.toString().replace(/\*/g, '%2A');
    //urlText = urlText.replace(/%20/g, '+')
  return (urlText);
}
function urlDecode(urlText){
  if (typeof urlText === "undefined") return "";
  else if(urlText=="0") return "0";
  else if(urlText==false) return "";
  else if(urlText=="") return "";
  return decodeURIComponent(urlText.toString().replace(/\+/g, "%20"));
}



function getWObjValue(obj){
  var sValue = "";
  if($(obj)){
    var sTagName = $(obj).prop("tagName").toUpperCase();

    if(sTagName=="INPUT"){
      if($(obj).prop("type")){
        if($(obj).prop("type").toLowerCase()=="checkbox"){
          sValue = ($(obj).prop("checked"))?1:0;
        }else if($(obj).prop("type").toLowerCase()=="radio"){
          var sName = $(obj).attr("name");
          sValue = ( $(obj).parent().find("input[name='"+sName+"']").filter(":checked").length > 0 )? $(obj).parent().find("input[name='"+sName+"']").filter(":checked").val():$(obj).data("odata");
        }else{
          sValue = $(obj).val();
        }
      }else{
        sValue = $(obj).val();
      }
    }else{
      sValue = $(obj).val();
    }

    /*
    if($(obj).hasClass("v_date") || $(obj).hasClass("showdate")){
      sValue=getDateData(sValue);
    }
    */

    if($(obj).hasClass("v_date")){
        var arrDate = sValue.split("/");
        if(arrDate.length == 3){
          //sValue = arrDate[0]+"/"+ arrDate[1]+"/"+ (parseInt(arrDate[2]) - 543);
          sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
          //alert("date : "+sValue);
        }
    }



  }
  return sValue;
}


function setWObjValue(obj,value){
  if($(obj).length){
    //alert($(obj).attr("data-name") );
    var sTagName = $(obj).prop("tagName").toUpperCase();

    if(sTagName=="INPUT" && ($(obj).prop("type"))){
//console.log("INPUT: "+value+ " type: "+$(obj).prop("type"));

        if($(obj).prop("type").toLowerCase()=="checkbox"){

          if(value=="1"){
            //	$(obj).attr("checked","checked");
              $(obj).prop("checked",true);
            //  console.log("chkbox: "+value);
          }
          else{
            $(obj).prop("checked",false);
          //  $(obj).prop("checked",true);
            //$(obj).removeAttr("checked");
          }
        }else if($(obj).prop("type").toLowerCase()=="radio"){
          //alert($(obj).prop("id") + " : " + value);
          $(obj).filter("[value='" + value + "']").prop('checked', true);
        }else{ //text
          $(obj).val(value);
        }

    }else{

      $(obj).val(value);
    }



    /*
    if($(obj).hasClass("datedata") || $(obj).hasClass("showdate")){
      $(obj).val(getLongDateData(value));
    }
    */

    //$(obj).data("odata",urlEncode(value));
    $(obj).data("odata",value);
  }
}

function setOData(divSaveData){
  $("#"+divSaveData + " .save-data").each(function(i,objx){
    if($(objx).hasClass("v_date")){
      var value = getWObjValue(objx);
      $(objx).data("odata",value);
    }
    else{
      setWObjValue(objx, getWObjValue(objx)  );
    }

  });
}

function setODataList(divSaveData){
  $("#"+divSaveData + " .save-data-list").each(function(i,objx){
    setWObjValue(objx, getWObjValue(objx)  );
  });
}

function getDateData(strDate){
  var tempVal = (new Date(strDate)=="Invalid Date")?"":new Date(strDate);
  return (tempVal=="0000-00-00")?"":(tempVal.getFullYear()+"-"+pad(tempVal.getMonth()+1,2)+"-"+pad(tempVal.getDate(),2));

}





</script>
