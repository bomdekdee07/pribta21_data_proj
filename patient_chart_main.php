<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
$sClinicId = getSS("clinic_id");
$sSid=getSS("s_id");
$sCurRoom=getSS("room_no");
$_GET["showonline"]=1;
$sData = " data-clinicid='".$sClinicId."' data-sid='".$sSid."' data-roomno='".$sCurRoom."' ";
$_GET["module"]='PHYSICIAN';
?>
<div id='divDocM' class='fl-wrap-row' <? echo($sData); ?> >
	<div class='fl-wrap-col left-bar w-300' style='background-color:white '>
		<div class='fl-fix h-25 fl-mid' style='background-color: #00D9D9'>
			<i class="fa fa-user"title="Patient Chart"> Patient Chart</i>
		</div>
		<div id='divQueueList' class='fl-wrap-col'>
			<?  include("queue_main.php"); ?>
		</div>
		<? include("leftbar_tools.php"); ?>
	</div>
	<div class='fl-wrap-col hidden-left-bar w-300' style='display:none'>

	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div id='divDocPInfo' class='fl-wrap-col right-bar before-close' data-saveid='save-data'>

	</div>
	<div class='fl-wrap-col right-bar-loader' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>

</div>
<script>

$(document).ready(function(){
	function showPChart(sUid,sOthQS){
		//if(fncBeforeClose()==false) return;

		startLoad($("#divDocM #divDocPInfo"),$("#divDocM .right-bar-loader"));
		sUrl="doctor_inc_main.php?showq=1&uid="+sUid+sOthQS;
		$("#divDocM #divDocPInfo").load(sUrl,function(){
			endLoad($("#divDocM #divDocPInfo"),$("#divDocM .right-bar-loader"));
		});		
	}

	loadWaitList();

	$("#divDocM #divQueueList").off("change");
	$("#divDocM").on("change","#divQueueList",function(){
		loadWaitList();
	});

	function loadWaitList(){
		objList = $("#divDocM #divQueueList").find(".main-q-list");
		$(objList).find(".main-q-row").each(function(ix,objx){
			sQ= $(objx).attr("data-queue");
			sUid= $(objx).attr("data-uid");
			sColD= $(objx).attr("data-coldate");
			sColT= $(objx).attr("data-coltime");
			sPrep= $(objx).attr("data-drugprep");
			sCheck= $(objx).attr("data-drugcheck");
			sPick= $(objx).attr("data-drugpick");
			sPaid= $(objx).attr("data-paid");
			sCallCode= $(objx).attr("data-qcall");
			sQStatus= $(objx).attr("data-status");
			sRoom=$(objx).attr("data-roomno");
			sName=$(objx).find(".subj_name").html();

			sCurRoom=$("#divDocM").attr('data-roomno');
			
//Q is on call <div class=" q-row main-q-row row-color fl-wrap-row h-50 row-hover" data-coldate="2021-12-09" data-queue="1" data-roomno="16" data-uid="P21-01214" data-coltime="13:06:27" data-drugprep="" data-drugcheck="" data-drugpick="" data-paid="" data-qcall="1" data-billid="" data-istoday="1" data-status="1">

//Q in the room <div class="row-notin q-row main-q-row row-color fl-wrap-row h-50 row-hover" data-coldate="2021-12-09" data-queue="1" data-roomno="16" data-uid="P21-01214" data-coltime="13:06:27" data-drugprep="" data-drugcheck="" data-drugpick="" data-paid="" data-qcall="0" data-billid="" data-istoday="1" data-status="2" style="">


			if((sCallCode=="1" || sQStatus=="2") && sRoom==sCurRoom){
				$(objx).addClass("row-notin");
				//$(objx).find(".btncallq").hide();
			}
		});
		
	}


	$("#divDocM #divDocPInfo .ddl-visit").off("change");
	$("#divDocM #divDocPInfo").on("change",".ddl-visit",function(){
		sOpt=$(this).val();
		if(sOpt=="") return;
		sUid=$(this).attr("data-uid");
		aVisit=sOpt.split(" ");
		showPChart(sUid,"&coldate="+aVisit[0]+"&coltime="+aVisit[1]);
	});

	$("#divDocM .btn-q-info").off("click");
	$("#divDocM").on("click",".btn-q-info",function(){
		qRow = $(this).closest(".q-row");
		let sUid = $(qRow).attr('data-uid');
		let sQ = $(qRow).attr('data-queue');
		let s_id = $("#divDocM").attr('data-sid');
		let sColD = $(qRow).attr('data-coldate');
		let sColT = $(qRow).attr('data-coltime');


		if(sUid=="" && sQ!=""){
			$.notify("Please add UID to this queue before continue");
			$("#divDocM #btnClearInput").trigger("click");					
			$("#divDocM #txtQueue").val(sQ);	
		}else{
			sQS = "&s_id="+s_id;
			if(sColD!=undefined) sQS+="&coldate="+sColD+"&coltime="+sColT;
			showPChart(sUid,sQS);
		}
	});


	if($("#divCurQueue").find(".btn-q-info").length){
		$("#divCurQueue").find(".btn-q-info").trigger("click");
		$.notify("Patient is in the room.",'success');
	}

});

</script>