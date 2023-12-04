<?

	include("in_session.php");
	include_once("in_php_function.php");
	$sUid=getQS("uid");
	$sColDate=getQS("collect_date");
	$sColTime=getQS("collect_time");
	if($sColDate=="") $sColDate=getQS("coldate");
	if($sColTime=="") $sColTime=getQS("coltime");
	$sSid = getSS("s_id");
?>


<style>
.result-green{
  background-color: #d0fa85;
	color:black;
}
.result-red{
  background-color: #fdb3b3;
	color:black;
}
</style>

<div class='fl-wrap-row lab-info h-30 lh-30 f-border bg-msoft3' style='vertical-align:middle; padding:2px 10px;' data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColDate); ?>' data-coltime='<? echo($sColTime); ?>'>
	<div class="fl-fix w-90 btn btn-success" id="btnViewHis" style="padding: 0px 10px 0px 10px;"><i class="fa fa-hourglass-start" aria-hidden="true"> History</i></div>
	<div class="fl-fix w-1"></div>
	<div class='fl-fix w-210 btn pbtn-blue' id='btnSubmitCustomLab' style="padding: 0px 10px 0px 10px;"><i class="fa fa-file-pdf fa-lg" aria-hidden="true" style='margin:0px 5px;'></i><b>Lab Report </b> [PDF]</div>

	<div class="fl-fix w-150 fl-mid font-s-2"><button class="btn btn-warning" name="btn_send_mail" style="padding: 0px 5px 0px 5px;"><i class="fa fa-envelope-open" aria-hidden="true"> Send Email</i></button></div>
	<div class='fl-fill fl-mid '>UID : <u style='background-color: white;margin-right:10px'><? echo($sUid); ?></u>Visit Date : <u style='background-color: white'><? echo($sColDate." ".$sColTime); ?></u> </div>
	<div class='fl-fix w-200 al-right div-pdf-thumbnail'></div>
	<div class='fl-fix w-30 fl-mid  hideme'>
		<i id='btnDeletePdf' class='fabtn fa fa-trash fa-lg'  style='margin-left:20px' ></i>
	</div>
	<div class='fl-fix w-300 fl-mid'>
		<SELECT id='ddlPDFList' class='subinfo w-fill'><? include("lab_opt_pdf_list.php"); ?></SELECT>


	</div>
	<div class='fl-fix w-30 fl-mid' style='color:orange'><i id="btn_view_pdf" class="fabtn far fa-file-pdf fa-lg" ></i></div>

	<div class='fl-fill fl-mid hideme'>
		<form method="post" action="" enctype="multipart/form-data" id="myform" style="margin-left:10px;display:inline;">
			Note : <input id='txtFileDesc' class='subinfo' size='10' name='filedesc' title='File Title' />
			<input type="file" id="filePDF"   class='subinfo' name="file" accept=".pdf" title='Less than 8MB only' /><input type="button" class="button" value="Upload" style='display:none' id="btnUploadPdf" /><i class='fa fa-spinner fa-spin fa-lg' style='display:none' ></i>
		</form>
	</div>
</div>
<div class='fl-wrap-row lab-head row-header h-30 fs-smaller bg-mdark1 ptxt-white ptxt-b ' style='padding:0 10px;'>
	<div class='fl-fix w-30 fl-mid'><input id='chkAllLab' type='checkbox' class='bigcheckbox' checked="checked" /></div>
	<div class='fl-fix w-200 lab-id lh-30'>
		Lab Id/Lab Name
	</div>
	<div class='fl-fix fl-mid lab-paid lh-30 w-80'>
		Paid
	</div>
	<div class='fl-fill fl-mid lab-result lh-30'>
		Result
	</div>
	<div class='fl-fix fl-mid lab-external w-30 lh-30' title='External Lab'>
		Ext.
	</div>
	<div class='fl-fill fl-mid lab-result-report lh-30'>
		Lab Result Report
	</div>
	<div class='fl-fill  lab-ref lh-30'>
		Reference
	</div>
	<div class='fl-fix fl-mid lab-status w-80 lh-30'>
		Normal?
	</div>
	<div class='fl-fill fl-mid lab-note lh-30'>
		Note
	</div>
</div>

<div class='fl-wrap-col fl-scroll lab-body f-border fs-small' style='padding:0 10px' >
	<form id='divCustomLab' method="POST" action="../weclinic/lab/custom_lab_report.php" target="_blank">
	<input type='hidden' name='uid' value='<? echo($sUid); ?>' />
	<input type='hidden' name='oid' value='' />
	<input type='hidden' name='collect_date' value='<? echo($sColDate); ?>'/>
	<input type='hidden' name='collect_time' value='<? echo($sColTime); ?>'/>
	<input type='hidden' name='s_id' value='<? echo($sSid); ?>'/>

	<? include("list_lab_report.php"); ?>
	</form>
</div>
<div class='fl-wrap-row lab-footer h-50'>

	<div class='fl-fix hideme' style='min-width:400px;text-align: center'><SELECT id='ddlLabMT'><? include("lab_opt_mt.php"); ?></SELECT><i id="btnConfirmLabResult" class="fabtn fa fa-clipboard-check" style='background-color: orange;' >Confirm Lab Report</i><i class='fa fa-spinner fa-spin fa-lg' style='display:none' ></i></div>
	<div class='fl-fill fl-mid'></div>
	<div class='fl-fix hideme' style='min-width:200px;text-align: center'><i id="btnSaveLabResult" class="fabtn fa fa-save" style=';background-color: green;color:white' >Save Data</i><i class='fa fa-spinner fa-spin fa-lg' style='display:none' ></i></div>
</div>

<script>
$(document).ready(function(){

	$('.lab-result').hide();
	$('.lab_save').prop('disabled', true);
	getPDFThumbnailShow();

	$("#chkAllLab").on("click",function(){
		$("#divCustomLab .chklabid").prop("checked", $(this).is(":checked"));
	});

	$("#btnViewHis").off("click");
	$("#btnViewHis").on("click", function(){
		sUid = $(".lab-info").attr("data-uid");
		sColD = $(".lab-info").attr("data-coldate");
		sColT = $(".lab-info").attr("data-coltime");
		sDocCode = "LAB_REPORT_HIS";

		dlgUrl = "document_sys_main.php?uid="+sUid+"&coldate="+sColD+"&coltime="+sColT+"&doctype="+sDocCode;
		showDialog(dlgUrl, "Lab History Report", "60%", "70%","",
			function(sResult){
				//CLose function
			},false,function(){
				//Load Done Function
		});
	});

	$("#btnSubmitCustomLab").off("click");
	$("#btnSubmitCustomLab").on("click",function(){
		sColD=$(".lab-info").attr("data-coldate");
		sColT=$(".lab-info").attr("data-coltime");
		sSid=$(".lab-info").attr("data-sid");
		sOrderId=$("#divCustomLab .lab-row").first().attr("data-orderid");
		$("#divCustomLab input[name='oid']").val(sOrderId);
		$("#divCustomLab input[name='collect_date']").val(sColD);
		$("#divCustomLab input[name='collect_time']").val(sColT);
		$("#divCustomLab input[name='s_id']").val(sSid);
		$("#divCustomLab").submit();
	});

	$("[name=btn_send_mail]").off("click");
	$("[name=btn_send_mail]").on("click", function(){
		sUid = $(".lab-info").attr("data-uid");
		sColD=$(".lab-info").attr("data-coldate");
		sColT=$(".lab-info").attr("data-coltime");
		sSid=$(".lab-info").attr("data-sid");
		sOrderId=$("#divCustomLab .lab-row").first().attr("data-orderid");

		var lablist = "";
		$(".chklabid").each(function(index, obj){
			if($(this).is(":checked")){
				lablist += $(this).val()+"||";
			}
		})

		sUrl="lab_inc_report_sendmail_dlg.php?uid="+sUid+"&coldate="+sColD+"&coltime="+sColT+"&oid="+sOrderId+"&s_id="+sSid+"&lablist="+encodeURIComponent(lablist);
		// console.log(sUrl);

		showDialog(sUrl, "Send Email Lab Result", "75%", "50%","",
		function(sResult){
			//CLose function
		},false,function(){
			//Load Done Function
		});
	})

	$(".lab-body .lab_save").unbind("change");
	$(".lab-body .lab_save").on("change",function(){
		//$(this).closest(".lab-row").addClass("row-unsaved");

		objRow = $(this).closest(".lab-row");
		if($(this).hasClass("lexternal")){
			if($(this).is(":checked")){
				$(objRow).find(".lnote").val("See Lab result from attached PDF file.");
				if($("#ddlPDFList option").length == 0){
					//$("#ddlPDFList").notify("Please don't forget to upload PDF\r\nกรุณาอัพโหลด PDF");
				}
			}else{
				$(objRow).find(".lnote").val("");
			}
		}else if($(this).hasClass("lresult")){
			if($(this).prop("tagName").toLowerCase()=="select"){
				sText = $(this).find("option[value='"+$(this).val()+"']").text();
				if($(this).val()==""){
					sText="";
					$(objRow).find(".lstatus").val("L0");
				}else{
					//Check if normal
					sIsNorm = $(this).find("option[value='"+$(this).val()+"']").attr("data-isnormal");
					$(objRow).find(".lstatus").val( ((sIsNorm=="1")?"L1":"L2") );
				}
				$(objRow).find(".lreport").val(sText);

			}else{
				if($(this).val()==""){
					$(objRow).find(".lreport").val("");
					$(objRow).find(".lstatus").val("L0");
				}else{
					sSex = $("#txtSex").val();
					sUnit = ($(objRow).find(".lunit").html());

					if($(this).data("ltype")=="num"){
						iMax = $(this).data("max");
						iMin = $(this).data("min");
						iMaxM = $(this).data("maxm");
						iMinM = $(this).data("minm");
						iMaxF = $(this).data("maxf");
						iMinF = $(this).data("minf");
						iVal = $(this).val();
						if(iVal < iMin){
							$(this).val(iMin);
							sTmp=iMin+((sUnit=="")?"":" "+encodeURI(sUnit));
							$(objRow).find(".lreport").val(sTmp);
							$(this).notify("Can't lower than minimum");
						}else if(iVal > iMax){
							$(this).val(iMax);
							sTmp=iMax+((sUnit=="")?"":" "+encodeURI(sUnit));
							$(objRow).find(".lreport").val(sTmp);
							$(this).notify("Can't more than maximum");
						}else{
							sTmp=$(this).val()+((sUnit=="")?"":" "+encodeURI(sUnit));
							$(objRow).find(".lreport").val(sTmp);
						}


						if(sSex=="1" && iMaxM!="" && iMinM!=""){
							//Male
							if(iVal>=iMinM && iVal<=iMaxM){
								$(objRow).find(".lstatus").val("L1");
							}else{
								$(objRow).find(".lstatus").val("L2");
							}
						}else if(sSex=="2" && iMaxF!="" && iMinF!=""){
							//Male
							if(iVal>=iMinF && iVal<=iMaxF){
								$(objRow).find(".lstatus").val("L1");
							}else{
								$(objRow).find(".lstatus").val("L2");
							}
						}
					}


				}


			}

		}


		checkDataChanged();
	});

	$(".lab-info #filePDF").unbind("change");
	$(".lab-info #filePDF").on("change",function(){
		if($(this).val()!=""){
		  $("#btnUploadPdf").show();
		}else{
		  $("#btnUploadPdf").hide();
		}
	});

	$(".lab-info #btnDeletePdf").unbind("click");
	$("#btnDeletePdf").on("click",function(){
		let sFileId = $("#ddlPDFList").val();

		if(sFileId=="" || sFileId== undefined){
			$.notify("No file selected.");
			return;
		}

		let sFileText = $("#ddlPDFList option[value='"+sFileId+"']").text();
		if(confirm("Confirm delete this PDF : "+sFileText)==false) return;

		sReason = prompt("Enter your reason.\r\nขอเหตุผลดีๆซักข้อ");
		sReason = sReason.trim();
		if(sReason == ""){
			$.notify("Please give me a reason to delete.");
			return;
		}

		sUid = $(".lab-info").attr("data-uid");
		sColDate = $(".lab-info").attr("data-coldate");
		sColTime = $(".lab-info").attr("data-coltime");

		var fd = new FormData();
		fd.append("mode","delete_pdf");
		fd.append("uid",sUid);
		fd.append("collect_date",sColDate);
		fd.append("collect_time",sColTime);
		fd.append("reason",sReason);
		fd.append("fileid",sFileId);
		$("#btnDeletePdf").hide();
		$("#myform").hide();
		$("#btnDeletePdf").next(".fa-spinner").show();
		$.ajax({
		  url: '../weclinic/lab/db_lab_pdf.php',
		  type: 'post',
		  data: fd,
		  contentType: false,
		  processData: false,

		  success: function(response){
		      if(response != 0){
		        $("#ddlPDFList").find("option[value='"+response+"']").remove();
		      }
		      else{
		          $.notify("delete error","error");
		      }
		    $("#btnDeletePdf").next(".fa-spinner").hide();
		    $("#btnDeletePdf").show();
		    $("#myform").show();
        getPDFThumbnailShow();
		  },
		});

	});


	$("#btnUploadPdf").unbind("click");
	$("#btnUploadPdf").on("click",function() {
		var fd = new FormData();
		var files = $('#filePDF')[0].files[0];
		fd.append('file', files);
		sUid = $(".lab-info").attr("data-uid");
		sColDate = $(".lab-info").attr("data-coldate");
		sColTime = $(".lab-info").attr("data-coltime");
		var sDesc = $("#txtFileDesc").val();

		if(sDesc==""){
		$("#txtFileDesc").focus();
		$("#txtFileDesc").notify("Please enter short file info\r\n กรุณาใส่รายละเอียดไฟล์สั้นๆก่อนครับ");
			return;
		}
		fd.append("uid",sUid);
		fd.append("coldate",sColDate);
		fd.append("coltime",sColTime);
		fd.append("filedesc",sDesc);

		$("#btnUploadPdf").hide();
		$("#myform").hide();
		$("#btnUploadPdf").next(".fa-spinner").show();

		$.ajax({
		  url: '../weclinic/lab/pdf_upload.php',
		  type: 'post',
		  data: fd,
		  contentType: false,
		  processData: false,

		  success: function(response){
		      if(response != 0){
		        $("#filePDF").val("");
		         $.notify("file uploaded","success");
		         $("#txtFileDesc").val("");
		         $("#btnUploadPdf").hide();
		         $("#ddlPDFList").append(response);
						 getPDFThumbnailShow();
		      }
		      else{
		          $.notify("uploaded error","error");
		      }
			$("#btnUploadPdf").hide();
			$("#btnUploadPdf").next(".fa-spinner").hide();
			$("#myform").show();

		  },
		});
	});

	$("#btn_view_pdf").unbind("click");
  	$("#btn_view_pdf").on("click",function(){
	    let sFileId = $("#ddlPDFList").val();
	    if(sFileId==""||sFileId==undefined){
	      $.notify("No file available\r\nยังไม่มีไฟล์");
	      return;
	    }

		sUid = $(".lab-info").attr("data-uid");
		sColDate = $(".lab-info").attr("data-coldate");
		sColTime = $(".lab-info").attr("data-coltime");
	    sTime = sColTime.split(":");
	    sNewTime = sTime[0]+sTime[1]+sTime[2];
	    window.open("../weclinic/lab/pdf_result/"+sUid+"_"+sColDate+"_"+sNewTime+"_"+sFileId+".pdf");
	});


	function getPDFThumbnailShow(){
		let sUid = $(".lab-info").attr("data-uid");
		let sColDate = $(".lab-info").attr("data-coldate");
		let sColTime = $(".lab-info").attr("data-coltime");

	  sTime = sColTime.split(":");
	  sNewTime = sTime[0]+sTime[1]+sTime[2];

	  let txt_thumbnail = "";
	  $("#ddlPDFList option").each(function(){
	      //console.log("file -"+$(this).text());
	      txt_thumbnail += "<a href='../weclinic/lab/pdf_result/"+sUid+"_"+sColDate+"_"+sNewTime+"_"+$(this).val()+".pdf' target='_blank'> <i class='mx-1 far fa-file-pdf fa-lg pbtn bg-msoft1  file-pdf-view' title='"+$(this).text()+"' data-id='"+$(this).val()+"'></i></a>";
	  });
	  //console.log("txt: "+txt_thumbnail);
	  if(txt_thumbnail != ""){
	    $('.div-pdf-thumbnail').html(txt_thumbnail);
	  }
	}




	$(".lab-footer #btnSaveLabResult").unbind("click");
	$(".lab-footer #btnSaveLabResult").on("click",function(){
		var aLabId=getA_LabDataChanged();
		if(aLabId.length>0){
			$(this).hide();
			$(".lab-footer #ddlLabMT").hide();
			$(".lab-footer #btnConfirmLabResult").hide();
			$(this).next(".fa-spinner").show();

			sUid = $(".lab-info").data('uid');
			sColDate = $(".lab-info").data('coldate');
			sColTime = $(".lab-info").data('coltime');

			var aData = {u_mode:"save_lab_result",uid:sUid,coldate:sColDate,coltime:sColTime};

			let aTemp=[];
			for(ix=0;ix<aLabId.length;ix++){
				objRow = $(".lab-body .lab-row[data-labid='"+aLabId[ix]+"']");
				sResult = $(objRow).find(".lresult").val();
				sExt = (($(objRow).find(".lexternal").is(":checked"))?"1":"0");
				sReport = $(objRow).find(".lreport").val();
				sStatus = $(objRow).find(".lstatus").val();
				sNote = $(objRow).find(".lnote").val();

				aTemp.push ({labid:aLabId[ix],labres:sResult,labext:sExt,labrep:sReport,labstat:sStatus,labnote:sNote});

			}
			aData.aobjdata = aTemp;

			callAjax("lab_a.php",aData,function(rtnObj,aData){

				objRow = $(".lab-body .lab-row[data-labid='"+aData.labid+"']");
				if(rtnObj.res=="0") $.notify("Error : "+rtnObj.msg,"error");
				else if(rtnObj.res=="99"){
					$.notify("Error : "+rtnObj.msg,"error");
					showLogin();
				}else if(rtnObj.res=="1") {
					sErrList = "";
					if ('errlist' in rtnObj)  sErrList = rtnObj.errlist;
					$.notify("Data saved","success");
					setOdata(sErrList,rtnObj.time);


				}

				$(".lab-footer #ddlLabMT").show();
				$(".lab-footer #btnConfirmLabResult").show();
				$(".lab-footer #btnSaveLabResult").show();
				$(".lab-footer #btnSaveLabResult").next(".fa-spinner").hide();


			});
		}else{
			$.notify("No Data Changed","warn");
		}
	});






	$(".lab-footer #btnConfirmLabResult").unbind("click");
	$(".lab-footer #btnConfirmLabResult").on("click",function(){
		var sMT=$(".lab-footer #ddlLabMT").val();
		if($(".row-unsaved").length){
			$.notify("Please save before confirm.\r\nกรุณาทำการบันทึกข้อมูลก่อน")
		}else if(sMT==""){
			$(".lab-footer #ddlLabMT").notify("Please select.",{
			  elementPosition: "top left"
			});
		}else{
			$(".lab-footer #ddlLabMT").hide();
			$(".lab-footer #btnSaveLabResult").hide();
			$(".lab-footer #btnConfirmLabResult").hide();
			$(".lab-footer #btnConfirmLabResult").next(".fa-spinner").show();

			sUid = $(".lab-info").data('uid');
			sColDate = $(".lab-info").data('coldate');
			sColTime = $(".lab-info").data('coltime');
			var aData = {u_mode:"confirm_lab",uid:sUid,coldate:sColDate,coltime:sColTime,mt:sMT};
			callAjax("lab_a.php",aData,function(rtnObj,aData){

				if(rtnObj.res=="0"){
					$.notify("Error : "+rtnObj.msg,"error");

				}else if(rtnObj.res=="99"){
					$.notify("Error : "+rtnObj.msg,"error");
					showLogin();
				}else if(rtnObj.res=="1"){
					$.notify("Data Confirm","success");
					$(".lab-body .lconfirm").each(function(ix,objx){
						objRow = $(objx).closest(".lab-row");
						sHtml = $(objx).html();
						if(sHtml=="" || sHtml=="0000-00-00 00:00:00"){
							$(objx).html(rtnObj.time);
							$(objRow).attr('data-isconfirm',"1");
							$(objx).removeClass("row-notconfirm");
						}
					});
				}

				$(".lab-footer #ddlLabMT").show();
				$(".lab-footer #btnConfirmLabResult").show();
				$(".lab-footer #btnSaveLabResult").show();
				$(".lab-footer #btnConfirmLabResult").next(".fa-spinner").hide();
			});
		}

	});

});

</script>
