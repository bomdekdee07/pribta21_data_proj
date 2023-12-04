<?
include_once("in_session.php");
include_once("in_php_function.php");
$sClinicId = getSS("clinic_id");
?>
<div id='divReceptionMain' class='fl-wrap-row' data-clinicid='<? echo($sClinicId); ?>'>
	<div class='fl-wrap-col left-bar' style='max-width:200px;max-width:20%;background-color:white '>
		<div class='fl-fix h-25 fl-mid' style='background-color: #00D9D9'>
			<i class="fa fa-laptop-medical"> Reception System</i>
		</div>
		<div class='fl-wrap-col'>
			<? $_GET["is_form_done"]="1"; $_GET["hidecall"]=1; $_GET["module"]="RECEPTION"; include("queue_main.php"); ?>
		</div>
		<? $_GET["showall"]="1"; include("leftbar_tools.php"); ?>
	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div id='divReceptionPInfo' class='fl-wrap-col right-bar'>
		<? include("reception_inc_patient_info.php"); ?>
	</div>

	<div class='fl-wrap-col right-bar-load' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>

</div>
<script>
$(document).ready(function(){

		$("#divReceptionMain .btnorderlab").off("click");
		$("#divReceptionMain").on("click",".btnorderlab",function(){
			qRow = $(this).closest(".q-row");
			sUid = $(qRow).attr('data-uid');
			sQ = $(qRow).attr('data-queue');
			sColD=$(qRow).attr('data-coldate');
			sColT=$(qRow).attr('data-coltime');

			sUrl="lab_order_inc_main.php?"+qsTxt(sUid,sColD,sColT)+"&is_pribta=1&is_doctor=1";
			showDialog(sUrl,"Lab Order "+qsTitle(sUid,sColD,sColT),"99%","99%","",
			function(sResult){
				//resetBill(sUid,sColD,sColT);

				if(sResult=="REFRESH"){
					
				}
			},false,function(){
				//Load Done Function
			});
		});

	$("#divReceptionMain .btn-q-info").unbind("click");
	$("#divReceptionMain").on("click",".btn-q-info",function(){
		qRow = $(this).closest(".q-row");
		let sUid = $(qRow).attr('data-uid');
		let sQ = $(qRow).attr('data-queue');
		sColD=$(qRow).attr('data-coldate');
		sColT=$(qRow).attr('data-coltime');
		isToday=$(qRow).attr('data-istoday');

		if(sUid=="" && sQ!="" && isToday==1){
			$.notify("Please add UID to this queue before continue");
			$("#divReceptionMain #btnClearInput").trigger("click");					
			$("#divReceptionMain #txtQueue").val(sQ);	
		}else{
			if(isToday==1){
				sQ += "&coldate="+sColD+"&coltime="+sColT;	
			}else{
				sQ="&coldate="+sColD+"&coltime="+sColT;
			}
			
			showReceptionInfo(sUid,"&loadq=1&lockq=1&q="+sQ);
		}
	});



});

function showReceptionInfo(sUid,sOtherQS=""){
	startLoad($("#divReceptionPInfo #divPInfoIdCard"),$("#divReceptionPInfo #divUidSearchResult-loader"));
	sUrl="patient_info_idcard_new.php?showq=1&uid="+sUid+sOtherQS;
	$("#divPInfoIdCard").load(sUrl,function(){
		endLoad($("#divReceptionPInfo #divPInfoIdCard"),$("#divReceptionPInfo #divUidSearchResult-loader"));
	});
}

</script>