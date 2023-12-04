<?
include_once("in_session.php");
include_once("in_php_function.php");
$sClinicId = getSS("clinic_id");
?>
<div id='divCashM' class='fl-wrap-row' data-clinicid='<? echo($sClinicId); ?>'>
	<div class='fl-wrap-col left-bar w-300' style='background-color:white '>
		<div class='fl-fix h-25 fl-mid' style='background-color: #00D9D9'>
			<i class="fas fa-dollar-sign" title="Cashier System"> Cashier System</i>
		</div>
		<div id='divCashQueueList' class='fl-wrap-col  '>
			<? $_GET["hidecall"]='1'; $_GET["module"]='CASHIER';  $_GET["waitlist"]='1'; include("queue_main.php"); ?>
		</div>

		<? $_GET["showunpaid"]=1; $_GET["showunbill"]=1;  include("leftbar_tools.php"); ?>
	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div id='divCashInfo' class='fl-wrap-col right-bar'></div>
	<div class='fl-wrap-col right-bar-loader' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>
	<div class='fl-wrap-col w-150 fl-cas-wrap' style="background-color:white">
		<!-- div class='fl-fix h-25 row-color fl-mid fl-cas-head'>รอชำระเงิน</div>
		<div id='divCashWait' class='fl-wrap-col fl-cas-body'></div>
		<div class='fl-fix h-25 row-color-2 fl-mid fl-cas-head'>ชำระเงินแล้ว</div>
		<div id='divCashDone' class='fl-wrap-col fl-cas-body hideme'></div -->
		<div class='fl-fix h-25 row-color fl-mid'>รอชำระเงิน</div>
		<div id='divCashWait' class='fl-wrap-col fl-auto'></div>
		<!--div class='fl-fix h-25 row-color-2 fl-mid'>ชำระเงินแล้ว</div>
		<div id='divCashDone' class='fl-wrap-col fl-auto'></div-->
	</div>


</div>
<script>
function loadWaitList(){
	objList = "";
	$("#divCashM #divCashWait").html("");

	//<div class=" q-row main-q-row row-color fl-wrap-row h-50 row-hover" data-coldate="2021-12-09" data-queue="1" data-roomno="26" data-uid="P21-01214" data-coltime="13:06:27" data-drugprep="" data-drugcheck="" data-drugpick="" data-paid="" data-qcall="1" data-billid="2564/3760" data-istoday="1" data-status="1" style="display: none;">

	
	//<div class=" q-row main-q-row row-color fl-wrap-row h-50 row-hover" data-coldate="2021-12-09" data-queue="2" data-roomno="26" data-uid="P20-11108" data-coltime="15:15:04" data-drugprep="" data-drugcheck="" data-drugpick="" data-paid="" data-qcall="0" data-billid="" data-istoday="1" data-status="1">


	$($("#divCashM #divCashQueueList").find(".main-q-list")).find(".main-q-row").each(function(ix,objx){
		sBillId = $(objx).attr('data-billid');
		sPaidBy = $(objx).attr('data-paid');
		sOnCall=$(objx).attr('data-qcall');
		sStatus=$(objx).attr('data-status');

		if(sOnCall=="1" && sBillId!="" && sPaidBy==""){
			sQ=$(objx).attr('data-queue');
			sUid=$(objx).attr('data-uid');
			sColD=$(objx).attr('data-coldate');
			sColT=$(objx).attr('data-coltime');
			sName=$(objx).find(".subj_name").html();

			objList+=getWaitList(sQ,sUid,sColD,sColT,sName);
			//$(objx).hide();
		}

	});
	$("#divCashM #divCashWait").append(objList);
	
}
	
$(document).ready(function(){

/*
	$("#divCashM #divCashInfo #btnCashCall").off("click");
	$("#divCashM #divCashInfo").on("click","#btnCashCall",function(){
		if($("#divCashM #divCashInfo #divSupplyOrder .data-row[data-ispaid='0']").length<1){
			if(confirm("ไม่พบรายการที่ต้องชำระ ยืนยันเรียกผู้รับบริการ?\r\nNo item require payment. Confirm call this customer?")==false)
				return;
		}
		sQ=$("#divCashM #divCashInfo #divCashIncM").attr('data-q');
		sURL="queue_a.php";
		aData={u_mode:"cashier_call",q:sQ};
		callAjax(sURL,aData,function(jRes,retAData){
			if(jRes.res=="1"){
				$("#divCashM #divCashInfo #btnCashCall").hide();
				$("#divCashM #divCashQueueList .q-row[data-queue='"+retAData.q+"']").hide();
				$("#divCashM #divCashWait").append(getWaitQRow(retAData.q));
			}else{
				
			}
    	});
	});

	function getWaitQRow(sQ){
		objQ=$("#divCashM #divCashQueueList .q-row[data-queue='"+sQ+"']");
		colD=$(objQ).attr("data-coldate");
		colT=$(objQ).attr("data-coltime");
		sName=$(objQ).find(".subj_name").html();
		return("<div class='fabtn btn-q-info fl-wrap-col row-color h-75 row-hover q-row' data-queue='"+sQ+"' data-uid='"+getUIDfromQ(sQ)+"' data-coldate='"+colD+"' data-coltime='"+colT+"' ><div class='h-30 fl-fix fl-mid fs-xlarge'>"+sQ+"</div><div class='h-15 fl-fix fl-mid fs-small'>"+getUIDfromQ(sQ)+"</div><div class='fl-fill lh-15 fs-small fl-mid fw-b' style='text-align:center'>"+sName+"</div></div>");
	}
*/
	


	loadWaitList();
	$("#divCashM #divCashQueueList").unbind("change");
	$("#divCashM").on("change","#divCashQueueList",function(){
		loadWaitList();
	});


	$("#divCashM .btn-q-info").off("click");
	$("#divCashM").on("click",".btn-q-info",function(){
		qRow = $(this).closest(".q-row");

		let sUid = $(qRow).attr('data-uid');
		let sQ = $(qRow).attr('data-queue');
		let isToday = $(qRow).attr("data-istoday");
		let sColD=$(qRow).attr('data-coldate');
		let sColT=$(qRow).attr('data-coltime');
		if(sColD==undefined) sColD="";
		if(sColT==undefined) sColT="";

		if(sUid=="" && sQ!=""){
			$.notify("Please add UID to this queue before continue");
			$("#divCashM #btnClearInput").trigger("click");					
			$("#divCashM #txtQueue").val(sQ);	
		}else{
			//sColD = $(qRow).attr('data-coldate');
			//sColT = $(qRow).attr('data-coltime');
			sQ+="&coldate="+sColD+"&coltime="+sColT;
			showCashier(sUid,"&q="+sQ);
		}
	});




	$("#divCashM .ddl-visit").off("change");
	$("#divCashM").on("change",".ddl-visit",function(){
		sUid = $(this).data("uid");
		sT = $(this).val();
		aVisit = sT.split(" ");
		sQS = "&coldate="+aVisit[0]+"&coltime="+aVisit[1];
		showCashier(sUid,sQS);
	});

});

function showCashier(sUid,sOthQS="",sColD="",sColT=""){
	startLoad($("#divCashM #divCashInfo"),$("#divCashM .right-bar-loader"));
	sUrl="cashier_inc_main.php?showq=1&uid="+sUid+sOthQS;
	$("#divCashM #divCashInfo").load(sUrl,function(){
		endLoad($("#divCashM #divCashInfo"),$("#divCashM .right-bar-loader"));
	});	
}
</script>