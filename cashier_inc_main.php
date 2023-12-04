<?
include_once("in_session.php");
include_once("in_php_function.php");
$sToday=date("Y-m-d");
$sQ=getQS("q");
$sUid=getQS("uid");
$sClinicId=getSS("clinic_id");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sQCall="";
$sCurRoom=getSS("room_no");
$sPK = getHiddenPk($sUid,$sColD,$sColT,"phx_input");
?>

<div id='divCashIncM' class='fl-wrap-col' data-q='<? echo($sQ); ?>' data-uid='<? echo($sUid); ?>' >
	<div class='fl-wrap-col f-border h-155' >
		<? echo($sPK); $_GET["showinfo"]="1"; include("phar_inc_patient_info.php");	

		$sCashAdd="<div id='btnCashierAdd' class='fabtn fl-mid fl-fix w-100 f-border' style='background-color: orange'>จัดการ</div>";
		//if($sColD!=$sToday) $sCashAdd="";


		?>
	</div>
	<div class='fl-wrap-row h-30 lh-20 fs-small bg-head-1'>
		<div class='fl-fill al-left'>แพทย์ผู้ตรวจ : <? //echo($sMD); ?></div>

		<div class='fabtn fl-fix w-30 fl-mid' title='ส่วนลด'><i class='fa fa-tags fa-lg'></i></div>

	</div>
	<div class='fl-wrap-row'>
		<div id='divSupplyOrder' class='fl-wrap-col'>
			<div class='fl-wrap-row h-25 bg-head-1 fl-mid f-border'>
				<div id='btnLabSaleReport' class='fabtn fl-mid fl-fix w-150 f-border' style='background-color: orange'>Lab Sale</div>
				<div id='btnToggleList' class='fabtn fl-fill fl-mid'>รายการ <i class='fa fa-expand-alt w-25'></i></div>
				<? echo($sCashAdd); ?>
			</div>
			<div id='divSupplyOrderList' class='fl-wrap-col'>
				<? include("cashier_inc_summary.php"); ?>
			</div>
		</div>
		<div id='divPaymentInfo' class='fl-wrap-col w-300 f-border'  data-q='<? echo($sQ); ?>' data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColD); ?>' data-coltime='<? echo($sColT); ?>'>
			<? include("cashier_inc_bill_info.php"); ?>
		</div>
	</div>




</div>

<script>
	function reloadSummaryList(billId){
		sUrl="cashier_inc_summary.php?billid="+billId;
		$("#divCashIncM #divSupplyOrder #divSupplyOrderList").load(sUrl,function(){

		});
	}
$(function(){

	function changeMode(sMode){
		$("#divCashIncM .btn-mode").hide();
		if(sMode=="oncall"){
			//ShowCancel
			$("#divCashIncM #btnCashCancelCall").show();
			$("#divCashIncM #btnCreateBill").show();
			
		}else if(sMode=="normal"){
			$("#divCashIncM #btnCashCall").show();
		}
	}
	$("#divCashIncM #btnLabSaleReport").off("click");
	$("#divCashIncM").on("click","#btnLabSaleReport",function(){	
		obR = $("#divCashIncM #divPaymentInfo");
		sUid = $(obR).attr("data-uid");
		sColDate = $(obR).attr("data-coldate");
		sColTime = $(obR).attr("data-coltime");

		sUrl="lab_sale_report_pdf.php?"+qsTxt(sUid,sColDate,sColTime);
		showDialog(sUrl,"LabSaleReport:"+qsTitle(sUid,sColDate,sColTime),"90%","90%","",
		function(sResult){
			//CLose function
		},false,function(){
			//Load Done Function
		});
	});

	$("#divCashIncM #btnToggleList").off("click");
	$("#divCashIncM").on("click","#btnToggleList",function(){
		$("#divSupplyOrderList .fl-cas-body").toggle();
	});


	$("#divCashIncM #btnCashierAdd").off("click");
	$("#divCashIncM").on("click","#btnCashierAdd",function(){	
		obR = $("#divCashIncM #divPaymentInfo");
		sUid = $(obR).attr("data-uid");
		sColDate = $(obR).attr("data-coldate");
		sColTime = $(obR).attr("data-coltime");

		sUrl="supply_order_dlg.php?"+qsTxt(sUid,sColDate,sColTime);
		showDialog(sUrl,"Supply Order "+qsTitle(sUid,sColDate,sColTime),"90%","90%","",
		function(sResult){
			//CLose function
			if(sResult=="REFRESH"){
				sUrl="cashier_inc_summary.php?"+qsTxt(sUid,sColDate,sColTime);
				$("#divCashIncM #divSupplyOrder #divSupplyOrderList").load(sUrl,function(){

				});

			}
		},false,function(){
			//Load Done Function
		});
	});




	$("#divCashIncM #btnCashCancelCall").off("click");
	$("#divCashIncM").on("click","#btnCashCancelCall",function(){
		sQ=$("#divCashIncM").attr('data-q');
		if(confirm("ยกเลิกเรียกคิวนี้?\r\nCancel Call for this Q?\r\nQueue #"+sQ)==false)
				return;
	
		sQ=$("#divCashIncM").attr('data-q');
		sURL="queue_a.php";
		aData={u_mode:"cashier_cancel_call",q:sQ};
		startLoad($(this),$("#divCashIncM .btn-cashier-loader"));
		callAjax(sURL,aData,function(jRes,rData){
			if(jRes.res=="1"){
				$("#divQueueList .main-q-row[data-queue='"+rData.q+"']").show();
				if($("#divCashWait .btn-q-info[data-queue='"+rData.q+"']").length>0) $("#divCashWait .btn-q-info[data-queue='"+rData.q+"']").remove();
				//changeMode("normal");
			}else{
				
			}
			$("#divCashIncM .btn-cashier-loader").hide();
    	});
	});

	$("#divCashIncM #btnCreateBill").off("click");
	$("#divCashIncM").on("click","#btnCreateBill",function(){
		if($("#divCashIncM #divSupplyOrder .row-detail[data-ispaid='0']").length<1){
			if(confirm("ไม่พบรายการที่ต้องชำระ ยืนยันออกเลขบิล?\r\nNo item require payment. Confirm issue bill number?")==false)
				return;
		}else if(confirm("ยืนยันออกเลขบิล\r\nConfirm issue bill number")==false){
			return
		}
		sQ=$("#divPaymentInfo").attr('data-q');
		sUid=$("#divPaymentInfo").attr('data-uid');
		sColD=$("#divPaymentInfo").attr('data-coldate');
		sColT=$("#divPaymentInfo").attr('data-coltime');

		sURL="cashier_a.php";
		aData={u_mode:"bill_create",q:sQ,uid:sUid,coldate:sColD,coltime:sColT};
		startLoad($(this),$("#divCashIncM .btn-cashier-loader"));
		callAjax(sURL,aData,function(jRes,rData){
			if(jRes.res=="1"){
				sUrl = "cashier_inc_bill_info.php?q="+sQ+"&"+qsTxt(sUid,sColD,sColT)+"&billid="+jRes.msg;
				$("#divCashIncM #divPaymentInfo").load(sUrl,function(){
					$("#divQueueList").find(".main-q-row[data-queue='"+sQ+"']").hide();
					objWait=$("#divQueueList").find(".waiting-list").find(".btn-q-info[data-queue='"+sQ+"']");
					$("#divCashWait").append(objWait);
				});
			}else{
				endLoad($("#divCashIncM #btnCreateBill"),$("#divCashIncM .btn-cashier-loader"));
			}
			
    	});
	});
	function getWaitQRow(sQ){
		objQ=$("#divQueueList .main-q-row[data-queue='"+sQ+"']");
		if($(objQ).length<1) return;
		sUid=$(objQ).attr("data-uid");
		colD=$(objQ).attr("data-coldate");
		colT=$(objQ).attr("data-coltime");
		sName=$(objQ).find(".subj_name").html();
		return("<div class='fabtn btn-q-info fl-wrap-col row-color h-75 row-hover q-row' data-queue='"+sQ+"' data-uid='"+sUid+"' data-coldate='"+colD+"' data-coltime='"+colT+"' ><div class='h-30 fl-fix fl-mid fs-xlarge'>"+sQ+"</div><div class='h-15 fl-fix fl-mid fs-small'>"+sUid+"</div><div class='fl-fill lh-15 fs-small fl-mid fw-b' style='text-align:center'>"+sName+"</div></div>");
	}


});
</script>