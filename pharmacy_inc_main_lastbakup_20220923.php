<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sMD="";
include("in_db_conn.php");
$sQ=getQS("q");

$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");

$sToday=date("Y-m-d");
$sRoom=getSS("room_no");
$sClinicId=getSS("clinic_id");

$btnSaveMode="";$btnSave="";
$aQInfo=array();
$sBillId="";

$bInRoom=false;

//Load queue info
$query ="SELECT queue,IQL.uid,collect_date,collect_time,queue_status,queue_call,queue_note,room_no,prepare_drug_by,check_drug_by,issue_drug_by,receive_by,queue_type,prepare_drug_date,check_drug_date,issue_drug_date,IBD.bill_id FROM i_queue_list IQL 
LEFT JOIN i_bill_detail IBD
ON IBD.clinic_id=IQL.clinic_id
AND IBD.bill_date = IQL.collect_date
AND IBD.bill_q=IQL.queue
AND IBD.bill_q_type = IQL.queue_type
LEFT JOIN i_bill_list IBL
ON IBL.bill_id = IBD.bill_id
WHERE IQL.clinic_id=? AND IQL.uid=? ";

if($sColD!=""){
	$query.=" AND collect_date=? AND collect_time=?";
}else{

}

$query.=" ORDER BY collect_date DESC LIMIT 1";

$stmt = $mysqli->prepare($query);
if($sColD!="") $stmt->bind_param("ssss",$sClinicId,$sUid,$sColD,$sColT);
else $stmt->bind_param("ss",$sClinicId,$sUid);

if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $aQInfo = $row;
	$_GET["coldate"]=$aQInfo["collect_date"];
	$_GET["coltime"]=$aQInfo["collect_time"];
	$_GET["uid"]=$aQInfo["uid"];

	$bInRoom = $sRoom == $aQInfo["room_no"];

	if($aQInfo["collect_date"] != $sToday ){
		//Archive History
		$btnSaveMode="archive";
	}else if($aQInfo["prepare_drug_by"]==""){
		//Wait Pharmacist to prepare
		$btnSaveMode="wait_prepare";
	}else if($aQInfo["check_drug_by"]==""){
		//Wait Pharmacist to check
		$btnSaveMode="wait_confirm";
	}else if($aQInfo["receive_by"]==""){
		//Not Paid Wait Payment No button
		$btnSaveMode="wait_payment";
	}else if($aQInfo["receive_by"]!="" && $aQInfo["issue_drug_by"]==""){
		//Paid but not issued
		$btnSaveMode="pickup";
	}else if($aQInfo["prepare_drug_by"]!="" && $aQInfo["check_drug_by"]!="" && $aQInfo["issue_drug_by"]!="" )   {
		//All Done
		$btnSaveMode="complete";
	}

  }
}
//


//Get QS Again
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sPK = getHiddenPk($sUid,$sColD,$sColT,"phx_input");
//


//Get MD Name
	$query ="SELECT data_id,data_result ,s_name
		FROM p_data_result PDR
		LEFT JOIN p_staff PS ON PS.s_id=data_result
		WHERE uid=? AND collect_date=? AND collect_time=? AND data_id = 'staff_md' AND data_result !='';";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($data_id,$data_result,$s_name);
		while($stmt->fetch()){
			$sMD=$s_name;
		}
	}
	$stmt->close();
	$mysqli->close();
//


if($btnSaveMode=="archive"){
	$btnSave="
		<div class='fl-fill fl-mid lh-15 fs-small' style='text-align:center;background-color:green;color:white'>
			ข้อมูลของวันที่ $sColD ".(($aQInfo["issue_drug_by"]=="")?"":"จ่ายยาเมื่อ".$aQInfo["issue_drug_date"])."<br/>Data date $sColD ".(($aQInfo["issue_drug_by"]=="")?"":"Issue on ".$aQInfo["issue_drug_date"])."
		</div>";
}else if($btnSaveMode=="wait_prepare"){
	$btnSave="
		<div class='fl-fill'></div>
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='prepare_drug' style='text-align:center;background-color:#90ee90'>
			จัดยาเสร็จ<br/>Done Preparing Drug.
		</div>";
}else if($btnSaveMode=="pickup"){
	$btnSave="
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='q_ready_cancel' style='text-align:center;background-color:red;color:white'>
			ยกเลิก<br/>Cancel.
		</div>
		<div class='fl-fill'></div>
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='supply_pickup' style='text-align:center;background-color:green;color:white'>
			รับยาแล้ว->กลับบ้าน<br/>Drug was pickup.
		</div>";
}else if($btnSaveMode=="wait_confirm"){
	$btnSave="
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='q_ready_cancel' style='text-align:center;background-color:orange;color:white'>
			ยกเลิก<br/>Cancel.
		</div>
		<div class='fl-fill'></div>
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='supply_is_check' style='text-align:center;background-color:yellow;color:black'>
			ยืนยัน<br/>Confirm Drug.
		</div>";
}else if($btnSaveMode=="wait_payment"){
	$btnSave="
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='q_ready_cancel' style='text-align:center;background-color:red;color:white'>
			ยกเลิก<br/>Cancel.
		</div>
		<div class='fl-fill'></div>
		<div class='fl-fix w-150 fl-mid lh-15 fs-small' style='text-align:center;background-color:orange;color:white'>
			รอชำระเงิน<br/>Waiting for Payment
		</div>";
}else if($btnSaveMode=="complete"){
	$btnSave="
		<div class='fl-fix fabtn w-150 fl-mid lh-15 fs-small btnpharupdate' data-queue='$sQ' data-mode='q_ready_cancel' style='text-align:center;background-color:red;color:white'>
			ยกเลิก<br/>Cancel.
		</div>

		<div class='fl-fill fl-mid lh-15 fs-small' style='text-align:center;background-color:green;color:white'>
			เสร็จสิ้นการจ่ายยา เมื่อเวลา ".$aQInfo["issue_drug_date"]."<br/>Drug successfully issued on ".$aQInfo["issue_drug_date"]."
		</div>
		

		";
}else{
	$btnSave="
		<div class='fl-fill fl-mid lh-15 fs-small' style='text-align:center;background-color:red;color:white'>
			ไม่พบข้อมูล Visit ของ UID นี้<br/>No visit's data found for this UID
		</div>";
}


?>
<div id='divPharIncM' class='fl-wrap-col' data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColD); ?>' data-coltime='<? echo($sColT); ?>'>
	<div class='fl-wrap-col f-border h-155'>
		<? echo($sPK); $_GET["showinfo"]="1"; include("phar_inc_patient_info.php"); 
		?>
	</div>
	<div class='fl-wrap-row h-30 lh-20 fs-small bg-head-1'>
		<div id='btnAddItem' class='fl-fix fabtn w-110 fl-mid' style='background-color:orange;color:white' title='เพิ่มรายการอื่นๆ'>
			<i class='fa fa-plus'> เพิ่มรายการ</i>
		</div>
		<div class='fl-fill al-left'>แพทย์ผู้ตรวจ : <? echo($sMD); ?></div>
		<div class='fl-fix w-180 fl-mid'>
			<? $_GET["doc_group"] = "DRUG_PRESC"; include("document_sys_bt.php"); ?>
			<!-- div id='btnPresc' class='fl-fix fabtn h-fill fl-mid lh-15 fs-small' data-queue='$sQ' style='text-align:center;background-color:blue;color:white'>
			ใบสั่งยา / Prescription.
			</div -->
		</div>
		<div class='fl-fix w-25'></div>
		<div class='fl-fix w-200 fl-mid al-right'>ผู้รับบริการต้องการเอกสารภาษา : </div>
		<div class='fl-fix w-100  fl-mid al-right'><SELECT class='fill-box h-20' id='prefLang'>
			<option value='th'>ไทย/TH</option>
			<option value='en'>อังกฤษ/EN</option>
		</SELECT></div>
		<div id='btnPrintAllLabel' class=' fl-fix fabtn w-100 fl-mid' style='background-color:orange;color:white' title='พิมพ์สติ๊กเกอร์ทุกรายการ แยกแต่ล่ะ Lot'>
			<i class='fa fa-print'> PRINT ALL</i>
		</div>
		<div id='btnPrintAllLabelSum' class='fl-fix fabtn w-130 fl-mid' style='background-color:cyan;color:white; display:none;' title='พิมพ์สติ๊กเกอร์ทุกรายการ โดยรวมที่เป็น ID เดียวกัน'>
			<i class='fa fa-print'> PRINT ALL SUM</i>
		</div>
	</div>
	<div id='divSupOrder' class='fl-wrap-col supply-order-list'>
		<? $_GET["viewmode"]="PHX"; if($sUid!="") include("supply_order_list.php"); ?>
	</div>
	<div id='btnViewLabOrder' class='fabtn fl-wrap-row h-20 bg-head-4 lh-20'>
		Laboratory List
	</div>
	<div id='divLabOrder' class='fl-wrap-col lab-order-list hideme fl-auto'>
		<? if($sUid!="") include("lab_order_inc_list.php"); ?>
	</div>
	<div class='fl-wrap-row h-30' style='background-color: white'>
		
		<? echo($btnSave); ?>
		<div class='fl-fix w-150 btnpharupdate-loader' style='display:none'>
			<i class='fa fa-spinner fa-spin'></i>
		</div>
	</div>
</div>

<script>
	$(function(){
		$("#divPharIncM #btnViewHistory").off("click");
		$("#divPharIncM #btnViewHistory").on("click",function(){
			sUid=$("#divPharIncM .phx_input[data-keyid='uid']").val();
			sColD=$("#divPharIncM .phx_input[data-keyid='collect_date']").val();
			sColT=$("#divPharIncM .phx_input[data-keyid='collect_time']").val();
			sUrl="patient_inc_dx_history_dlg.php?"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,"ประวัติการรักษา/Treatment History","480","820","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
				}
			},false,function(){
				//Load Done Function
			});
		});

		$("#divPharIncM #btnViewLabOrder").off("click");
		$("#divPharIncM #btnViewLabOrder").on("click",function(){
			$("#divPharIncM #divLabOrder").toggle();
		});


		$("#divPharIncM #btnAddItem").off("click");
		$("#divPharIncM").on("click","#btnAddItem",function(){	
			obR = $("#divPharIncM");
			sUid = $(obR).attr("data-uid");
			sColDate = $(obR).attr("data-coldate");
			sColTime = $(obR).attr("data-coltime");

			sUrl="supply_order_dlg.php?"+qsTxt(sUid,sColDate,sColTime);
			showDialog(sUrl,"Supply Order "+qsTitle(sUid,sColDate,sColTime),"90%","90%","",
			function(sResult){
				//CLose function
				if(sResult=="REFRESH"){
					$.notify("YEAH");
					sUrl="supply_order_list.php?viewmode=PHX&"+qsTxt(sUid,sColDate,sColTime);
					$("#divPharIncM #divSupOrder").load(sUrl,function(){

					});

				}
			},false,function(){
				//Load Done Function
			});
		});

		$("#divPharIncM .btneditorder").off("click");
		$("#divPharIncM").on("click",".btneditorder",function(){
			objr = $(this).closest(".data-row");
			sSupCode = $(objr).attr("data-supcode");
			sSupName = ($(objr).find(".supply-name").html());
			sUid=$("#divPharIncM").attr("data-uid");
			sColD=$("#divPharIncM").attr("data-coldate");
			sColT=$("#divPharIncM").attr("data-coltime");
			sOCode = $(objr).attr("data-ocode");
			sStatus = $(objr).attr("data-ostatus");
			sIsService = $(objr).attr("data-isservice");
			sIsPaid = $(objr).attr("data-ispaid");
			sIsPickup = $(objr).attr("data-ispickup");
			objThis = $(this);
			sURL="supply_order_edit_dlg.php?"+qsTxt(sUid,sColD,sColT)+"&supply_code="+sSupCode+"&order_code="+sOCode; 
			showDialog(sURL,qsTitle(sUid,sColD,sColT),"320","480","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
					sUrl="supply_order_list.php?viewmode=PHX&uid="+sUid+"&coldate="+sColD+"&coltime="+sColT;
					$(objThis).closest("#divPharIncM").find(".supply-order-list").load(sUrl,function(){
						
					});
					//reloadSOD_List(sUid,sColD,sColT);
				}
			},false,function(){
				//Load Done Function
			});

		});

		$("#divPharIncM .btnprintlabel").off("click");
		$("#divPharIncM").on("click",".btnprintlabel",function(){
			sOCode=$(this).closest(".data-row").attr('data-ocode');
			sSupCode=$(this).closest(".data-row").attr('data-supcode');
			sLot=$(this).closest(".data-row").attr('data-stklot');
			printSticker("label",sSupCode,sOCode,sLot);
		});

		$("#divPharIncM .btnPrintLabelSum").off("click");
		$("#divPharIncM").on("click",".btnPrintLabelSum",function(){
			sSupCode=$(this).closest(".data-row").attr('data-supcode');
			printSticker("sumlabel",sSupCode);
		});

		$("#divPharIncM #btnPrintAllLabel").off("click");
		$("#divPharIncM #btnPrintAllLabel").on("click",function(){
			printSticker("all");
		});

		$("#divPharIncM #btnPrintAllLabelSum").off("click");
		$("#divPharIncM #btnPrintAllLabelSum").on("click",function(){
			printSticker("sumall");
		});

		function printSticker(sMode,sSupCode="",sOCode="",sLot){
			sLang=$("#divPharIncM #prefLang").val();
			sUid=$("#divPharIncM .phx_input[data-keyid='uid']").val();
			sColD=$("#divPharIncM .phx_input[data-keyid='collect_date']").val();
			sColT=$("#divPharIncM .phx_input[data-keyid='collect_time']").val();
			//sLot=$("#divPharIncM .phx_input[data-keyid='stock_lot']").val();
			sUrl = "";
			if(sMode=="label"){
				sUrl="print_drug_label.php?"+qsTxt(sUid,sColD,sColT)+"&ordercode="+sOCode+"&supcode="+sSupCode+"&lang="+sLang+"&lot="+sLot;
			}else if(sMode=="all"){
				sUrl="print_drug_label_all.php?"+qsTxt(sUid,sColD,sColT)+"&lang="+sLang;
			}else if(sMode=="sumlabel"){
				sUrl="print_drug_label_sum.php?"+qsTxt(sUid,sColD,sColT)+"&supcode="+sSupCode+"&lang="+sLang;
			}else if(sMode=="sumall"){
				sUrl="print_drug_label_sum_all.php?"+qsTxt(sUid,sColD,sColT)+"&lang="+sLang;
			}
			window.open(sUrl);
		}

	});
</script>