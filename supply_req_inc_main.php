<?
include_once("in_session.php");
include_once("in_php_function.php");

$sReqId = getQS("request_id","{NEW}");
$aRInfo=array();
$sSName = "";


$cssIsNew="";
$sJS = "";
if(strpos($sReqId, "S")!==false){
		include("in_db_conn.php");
		$query = "SELECT request_id,request_by,s_name,request_title,request_detail,request_datetime,require_date,request_status,delivery_to,delivery_other,request_type,request_proj,finance_req_no,recieved_date,request_po_no 
		FROM i_stock_request_list ISRL
		LEFT JOIN p_staff PS
		ON PS.s_id=ISRL.request_by
		WHERE request_id=?";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("s",$sReqId);
		if($stmt->execute()){
			//$stmt->bind_result($request_by,$s_name,$request_title,$request_detail,$request_datetime,$request_status,$delivery_to,$delivery_other,$request_type,$request_proj,$finance_req_no,$recieved_date,$request_po_no);
			$result = $stmt->get_result();
			while ($row = $result->fetch_assoc()) {
				$aRInfo = $row;

			}
		}
		$stmt->close();
		$mysqli->close();

}

if(!isset($aRInfo["request_id"]) || ($aRInfo["request_id"])!=$sReqId){

	$sReqId="{NEW}";
	$_GET["request_id"]="{NEW}";
	$cssIsNew="hideme";
}else{
	$sSName=(isset($aRInfo["s_name"])?$aRInfo["s_name"]:"");
}
//$sSName=getSS("s_name");

foreach ($aRInfo as $KeyId => $sVal) {
	$sJS.="setKeyVal($(\"#divRF\"),'".$KeyId."',".json_encode($sVal).");";
	//$aRInfo[$KeyId] = json_encode($sVal);
}
$sReqStatus = isset($aRInfo["request_status"])?$aRInfo["request_status"]:"";

if($sReqId=="{NEW}"){
	$sJS .= "requestMode('NEW');";
}else if($sReqStatus=="1" || $sReqStatus=="01"){
	$sJS .= "requestMode('1');";
}else if($sReqStatus=="" || $sReqStatus=="00" || $sReqStatus=="0"){
	$sJS .= "requestMode('0');";
}else if($sReqStatus=="CC"){
	$sJS .= "requestMode('CC');";
}else if($sReqStatus=="FIN"){
	$sJS .= "requestMode('FIN');";
}
?>

<div id='divRF' class='fl-wrap-col divform' style='min-width:1100px'>
	<div class='fl-wrap-row h-30 fl-mid fw-b'>
		<div class='fl-fix w-20'></div>
		<div class='fl-fix w-200'>
			Status : <SELECT id='ddlReqStatus' disabled='true'>
			<option value=''>NEW</option>
			<option value='0'>Pending</option>
			<option value='1'>Submitted</option>
			<option value='CC'>Cancelled</option>
			<option value='CF'>Confirmed</option>
			<option value='FIN'>Item Imported</option>
			</SELECT>
		</div>
		<div class='fl-fill fl-mid'>แบบฟอร์มนำของ เข้าระบบ</div>
		<div class='fl-fix w-200'><input readonly="true" class='saveinput saveinputsub fill-box' value='<? echo($sReqId); ?>' data-keyid='request_id'  data-pk='1' /> </div>
		<div class='fl-fix w-20'></div>
	</div>
	<div class='fl-wrap-row reqbody'>
		<div class='fl-fix w-20'></div>
		<div class='fl-wrap-col fs-smaller fl-auto'>
			<div class='fl-wrap-row h-30 row-color-2'>
				<div class='fl-fix w-200'>
					Request By (ชื่อผู้ขอ) :
				</div>
				<div class='fl-fill'>
					<input class='fill-box' disabled="true" value='<? echo($sSName); ?>' />
				</div>
				<div class='fl-fix w-200'>
					Date (วันที่) :
				</div>
				<div class='fl-fill'>
					<input class='fill-box' disabled="true"  value=''  data-odata='' />
				</div>
			</div>
			<div class='fl-wrap-row h-30 row-color-2'>
				<div class='fl-fix w-200'>
					หัวข้อ/Title
				</div>
				<div class='fl-fill fw-b'>
					<input class='fill-box saveinput' data-odata='' value='เพื่อจัดซื้อยาสำหรับใช้ที่พริบตาแทนเจอรีนสหคลินิก <? echo(date("Y-m-d")); ?>' data-keyid='request_title' />
				</div>
			</div>
			<div id='divListItem' class='fl-wrap-col h-300' >
				<div class='fl-wrap-row f-border h-40 lh-20 fl-mid data-row row-color row-hover fs-xsmall fw-b' >
					<div class='fl-fix w-50 fl-mid row-color'>
						No.
					</div>
					<div class='fl-fix w-110'>
						Supply Code
					</div>
					<div class='fl-fill lh-15'>
						Description<br/>(รายการที่ต้องการขออนุมัติซื้อ)
					</div>
					<div class='fl-fix w-80 fl-mid'>
						Quantity<br/>(จำนวน)
					</div>
					<div class='fl-fix w-100 fl-mid'>
						Unit
					</div>
					<div class='fl-fix w-80 fl-mid'>
						Unit Price<br/>(ราคาต่อหน่วย)
					</div>
					<div class='fl-fix w-80 fl-mid'>
						Amount<br/>(จำนวนเงิน)
					</div>
					<div class='fl-fix w-100'>
						Project<br/>(โครงการ)
					</div>
					<div class='fl-fix w-100'>
						Account Code<br/>(รหัสบัญชี)
					</div>
					<div class='fl-fix w-50 fl-mid' style='color:red'>
					</div>
				</div>
				<div class='fl-wrap-row f-border h-40 lh-20 fl-mid data-row row-color row-hover fs-xsmall fw-b' >
					<div id='btnClearInput' class='fl-fix w-50 h-40 fl-mid fabtn'><i class='fa fa-trash-alt fa-lg'></i>
						</div>
					<div class='fl-fix w-80'>
						<input id='txtSupCode' class='fill-box saveinputsub inputsub' data-keyid='supply_code' data-odata='' readonly="true" data-pk='1' data-odata />
					</div>
					<div id='btnSearchSupply' class='fabtn fl-fix w-30'>
						<i  class=' fas fa-search fa-lg'></i>
					</div>
					<div class='fl-fill lh-15'>
						<input id='txtSupplyNote' class='w-fill h-25 saveinputsub inputsub ' data-keyid='request_supply_note' data-odata />
					</div>
					<div class='fl-fix w-80 fl-mid'>
						<input class='fill-box saveinputsub inputsub item_amt' type='number' data-keyid='request_amt' data-odata />
					</div>
					<div class='fl-fix w-100 fl-mid'>
						<input id='txtUnit' class='fill-box inputsub' readonly="true" />
					</div>
					<div class='fl-fix w-80 fl-mid'>
						<input class='fill-box saveinputsub  inputsub item_price' type='number'  data-keyid='request_item_price' data-odata />
					</div>
					<div class='fl-fix w-80 fl-mid'>
						<input class='fill-box saveinputsub  inputsub item_total' type='number'  data-keyid='request_total_price' data-odata />
					</div>
					<div class='fl-fix w-100'>
						<input data-keyid='' data-odata class='fill-box dis' disabled="true" title='This item is not implement yet' />
					</div>
					<div class='fl-fix w-100'>
						<input data-keyid='' data-odata class='fill-box ' disabled="true" title='This item is not implement yet' />
					</div>
					<div class='fl-fix w-50 fl-mid' style='color:green'>
						<i id='btnAddNewRow' class='fas fa-plus-square fa-2x fabtn '></i>
					</div>
				</div>
				<div class='fl-wrap-col h-260 f-border row-color fl-scroll' >
					<div id='divRIL' class='fl-wrap-col'>
						<? include("purchase_req_item_list.php"); ?>
					</div>

				</div>
			</div>

		</div>
		<div class='fl-wrap-col w-200'>
			<div id='btnShowUpload' class='fabtn fl-fix h-25 fl-mid row-color-2 fs-small'>
				<i class='fas fa-upload' >Upload File</i>
			</div>
			<div id='divFUL' class='fl-wrap-col fl-auto fs-xsmall'>
				<? $_GET["u_mode"]="request_file_list"; include("purchase_req_a.php"); ?>
			</div>
		</div>
		<div class='fl-fix w-20'></div>
	</div>
	<div class='divsave fl-wrap-row h-40 fl-mid'>
		<div class='fl-fix w-20'></div>
		<div class='fl-fix w-100 fl-mid reqbtn reqnew  req00 hideme'>
			<input id='btnSaveRequest' type='button' class='fill-box' value='Save' data-mode='request_supply_add' />
		</div>
		<div class='fl-fix w-100 fl-mid reqbtn req01 hideme'>
			<input id='btnCancelRequest' type='button' class='fill-box' value='Cancel' />
		</div>
		<div class='fl-fill fl-mid'>
			<!-- input id='btnPrintReq' class='' type='button' value='Print PR' /-->
		</div>
		<div class='fl-fix w-100 fl-mid reqbtn req00 hideme'>
			<input id='btnSubmitRequest' type='button' class='fill-box' value='Submit' />
		</div>
		
		<div class='fl-fix w-100 fl-mid reqbtn hideme'>
			<input id='btnCopyRequest' type='button' class='fill-box' value='Copy' />
		</div>
		<div class='fl-fix w-100 fl-mid reqbtn req01 hideme'>
			<input id='btnApproveRequest' type='button' class='fill-box hideme' value='Approve' />
		</div>
		<div class='fl-fix w-100 fl-mid reqbtn req01 hideme'>
			<input id='btnImportItem' type='button' class='fill-box' value='Import' />
		</div>
		<div class='fl-fix w-20'></div>
	</div>
	<div class='divsave-loader fl-wrap-row h-40' style='display:none'>
		<div class='fl-fill fl-mid'>
			<i class='fa fa-spinner fa-spin fa-2x'></i>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	function requestMode(sStatus){
		$("#divRF #ddlReqStatus").val(sStatus);

		if(sStatus=="NEW"){
			$("#divRF .reqbtn").hide();
			$("#divRF .reqnew").show();
			$("#divRF #divListItem").hide();
			$("#divRF #ddlReqStatus").val("");
		}else if(sStatus=="0"){
			$("#divRF .reqbtn").hide();
			$("#divRF .req00").show();
			$("#divRF #btnPrintReq").show();
		}else if(sStatus=="1"){
			$("#divRF .reqbody input").prop('disabled','true');
			$("#divRF .fabtn").hide();
			$("#divRF .reqbtn").hide();
			$("#divRF .req01").show();
		}else if(sStatus=="CC"){
			$("#divRF .reqbody input").prop('disabled','true');
			$("#divRF .fabtn").hide();
			$("#divRF .reqbtn").hide();
			$("#divRF .reqCC").show();
		}else if(sStatus=="CF"){
			$("#divRF .reqbody input").prop('disabled','true');
			$("#divRF .fabtn").hide();
			$("#divRF .reqbtn").hide();
			$("#divRF .reqCC").show();
			$("#divRF #btnPrintReq").show();
		}else if(sStatus=="FIN"){
			$("#divRF .reqbody input").prop('disabled','true');
			$("#divRF .fabtn").hide();
			$("#divRF .reqbtn").hide();
			$("#divRF .reqCC").show();
			$("#divRF #btnPrintReq").show();
		}
	}
	<? echo($sJS); ?>
	$("#divRF .date-data").datepicker({
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true
	});
	function setEditMode(){
		$(getKeyObj($("#divRF"),"request_title")).attr("readonly",true);
		$(getKeyObj($("#divRF"),"request_datetime")).attr("readonly",true);
	}


	$("#divRF .item_price").off("change");
	$("#divRF .item_amt").off("change");
	$("#divRF .item_price,#divRF .item_amt").on("change",function(){
		calculatePrice();
	});
	
	function calculatePrice(){
		iPrice = $("#divRF .item_price").val();
		iAmt = $("#divRF .item_amt").val();
		if(iPrice=="" || iAmt=="") return;
		iTotal = iPrice*iAmt;
		$("#divRF .item_total").val(iTotal);
	}



	$("#divRF #btnAddNewRow").off("click");
	$("#divRF #btnAddNewRow").on("click",function(){
		iPrice = $("#divRF .item_price").val().trim();
		iAmt = $("#divRF .item_amt").val();
		supCode = $("#divRF #txtSupCode").val();


		$("#divRF .bg-error").removeClass("bg-error");
		if(supCode==""){
			$.notify("Item can't be blank");
			$("#divRF #txtSupCode").addClass("bg-error");
			return;
		}

		if(iAmt=="" || iAmt==0){
			$.notify("Item Amount can't be blank");
			$("#divRF .item_amt").addClass("bg-error");
			return;
		}
		if(iPrice==""){
			$.notify("Item Price can't be blank. If it free, please enter 0.");
			$("#divRF .item_price").addClass("bg-error");
			return;
		}

		let aData=getDataRow($("#divRF"),"saveinputsub");
        if(aData==""){
        	$.notify("H-No Data Changed");
        	return;
        }

        aData.u_mode="request_item_add";
        startLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Data Saved","success");
          	/*$("#divRF .item_price").val("");
          	$("#divRF .item_amt").val("");
          	$("#divRF .item_total").val("");*/
          	$("#divRF #txtUnit").val("");
          	$("#divRF .saveinputsub[data-pk!='1']").val("");
          	$("#divRF .saveinputsub[data-keyid='supply_code']").val("");
          	$("#divRF #divRIL").append(jRes.msg);
         }else{
         	$.notify(jRes.msg);
         }
         endLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
         $("#divRF #btnClearInput").trigger("click");
        });

	});
	$("#divRF #txtSupplyNote").off("keypress");
	$("#divRF #txtSupplyNote").on("keypress",function(e){
		if(e.which == 13) {
			showSearchSupply($(this).val().trim());
		}
	});

	function showSearchSupply(sKey=""){
		sUrl = "supply_inc_list.php";
		if(sKey!="") sUrl+="?find="+encodeURIComponent(sKey);
		showDialog(sUrl,"Find Supply List : ","320","640","",
		function(sResult){
			//CLose function
			if(sResult != ""){
				aRes = sResult.split(",");
				$("#divRF .saveinputsub[data-keyid='supply_code']").val(aRes[0]);
				$("#divRF .saveinputsub[data-keyid='request_supply_note']").val(aRes[1]);
				$("#divRF #txtUnit").val(aRes[2]);
			}
		},false,function(){});
	}

	$("#divRF #btnClearInput").off("click");
	$("#divRF #btnClearInput").on("click",function(){
		$("#divRF .inputsub").val("");

	});

	$("#divRF #btnShowUpload").off("click");
	$("#divRF #btnShowUpload").on("click",function(){
		sReqId = getKeyVal($("#divRF"),"request_id");
		if(sReqId=="" || sReqId=="{NEW}") return;
		sTitle = getKeyVal($("#divRF"),"request_title");
		sUrl = "supply_inc_upload.php?reqid="+sReqId;
		showDialog(sUrl,"Upload File For : "+sTitle,"230","440","",
		function(sResult){
			//CLose function
			if(sResult == "1"){
				sUrl="purchase_req_a.php?request_id="+sReqId+"&u_mode=request_file_list";
				$("#divRF #divFUL").load(sUrl,function(){

				});
			}
		},false,function(){});
	});

	$("#divRF #btnImportItem").off("click");
	$("#divRF #btnImportItem").on("click",function(){
		sReqId = getKeyVal($("#divRF"),"request_id");
		sTitle = getKeyVal($("#divRF"),"request_title");
		sUrl = "stock_import_form.php?reqid="+sReqId;
		showDialog(sUrl,"Import Items. : "+sTitle,"360","800","",
		function(sResult){
			//CLose function
			if(sResult == "1"){
				requestMode("FIN");
			}
		},false,function(){});
	});


	$("#divRF #btnSearchSupply").off("click");
	$("#divRF #btnSearchSupply").on("click",function(){
		sUrl = "supply_inc_list.php";
		showSearchSupply();
	});

	$("#divRF #btnSaveRequest").off("click");
	$("#divRF #btnSaveRequest").on("click",function(){
		if($("#divRF .saveinput[data-keyid='request_id']").val()=="{NEW}"){
			sMode = "request_supply_add";
		}else{
			sMode = "request_update";
		}
		//sMode = $(this).attr("data-mode");

		//Validate
		if(getKeyVal($("#divRF"),"request_title").trim()==""){
			$.notify("Title can't be empty");
			getKeyObj($("#divRF"),"request_title").focus();
			return;
		}

        var aData=getDataRow($("#divRF"));
        if(aData==""){
        	$.notify("No Data Changed");
        	return;
        }

        aData.u_mode=sMode;
        startLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Data Saved","success");
          	if(retAData.u_mode=="request_supply_add"){
          		$("#divRF").parent().load("supply_req_inc_main.php?request_id="+jRes.request_id)
          		//setKeyVal($("#divRF"),"request_id",jRes.request_id);
          		//$("#divRF #btnSaveRequest").attr('data-mode','request_update');
          		
          	}else if(retAData.u_mode=="request_update"){
	          	$("#divRF #divListItem").show();
	          	$("#divRF #btnSubmitRequest").show();
	          	setKeyAllOld($("#divRF"));
          	}

         }else{
         	$.notify(jRes.msg);
         }
         endLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        });
	});


	$("#divRF #btnCancelRequest").off("click");
	$("#divRF #btnCancelRequest").on("click",function(){
		sMode = "request_cancel";
		if(!(confirm("Do you want to cancel this request?\r\nยืนยันยกเลิก Purcase Request"))){
			return;
		}
		sReqId=$("#divRF .saveinput[data-keyid='request_id']").val();
		if(sReqId=="" || sReqId=="{NEW}") return;
        var aData={u_mode:sMode,request_id:sReqId}

        startLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Request was cancelled.","success");
          	requestMode("CC");
         }else{
         	$.notify(jRes.msg);
         }
         endLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        });
	});

	$("#divRF #btnSubmitRequest").off("click");
	$("#divRF #btnSubmitRequest").on("click",function(){
		if($("#divRF #divRIL .data-row").length){
			//Item Found
		}else{
			$.notify("no item added.\r\nไม่พบ Item ถูกเพิ่ม");
			return;
		}

		sMode = "request_submit";
		if(!(confirm("Do you want to submit this request?\r\nยืนยันส่ง Request"))){
			return;
		}
		sReqId=$("#divRF .saveinput[data-keyid='request_id']").val();
		if(sReqId=="" || sReqId=="{NEW}") return;
        var aData={u_mode:sMode,request_id:sReqId}

        startLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Request was submit.","success");
          	requestMode("1");
         }else{
         	$.notify(jRes.msg);
         }
         endLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        });
	});


	$("#divRF .btndelete").off("click");
	$("#divRF").on("click",".btndelete",function(){
		objRow = $(this).closest(".data-row");
		reqId = $(objRow).attr("data-reqid");
		supCode = $(objRow).attr("data-supcode");
		sMode = "request_item_remove";
		if(!(confirm("Do you want to remove this "+supCode+"?\r\nยืนยันลบรายการ "+supCode+" Purcase Request"))){
			return;
		}
        var aData={u_mode:sMode,request_id:reqId,supply_code:supCode};
        startLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Item removed.","success");
          	$("#divRF .data-row[data-reqid='"+aData.request_id+"'][data-supcode='"+aData.supply_code+"']").remove();
         }else{
         	$.notify(jRes.msg);
         }
         endLoad($("#divRF .divsave"),$("#divRF .divsave-loader"));
        });
	});

	$("#divRF #btnPrintReq").off("click");
	$("#divRF #btnPrintReq").on("click",function(){
		sReqId = $(getKeyObj($("#divRF"),"request_id")).val();
		if(sReqId=="" || sReqId=="{NEW}") return;
		sUrl = "purchase_req_pdf.php?reqid="+sReqId;
		window.open(sUrl);
	});

});

</script>