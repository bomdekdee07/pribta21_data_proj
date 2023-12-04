<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
$sQ=getQS("q");
$sUid = getQS("uid");
$sColD = getQS("coldate");
$sColT = urldecode(getQS("coltime"));

$query="";
if($sQ=="" && $sUid!="" && $sColD==""){
	//Just UID Provided. get Last Visit if possible
	$query ="SELECT queue,uid,collect_date,collect_time,queue_status,queue_call,queue_note,room_no,check_drug_by FROM i_queue_list WHERE uid=? ORDER BY collect_date DESC LIMIT 1";
}else if($sQ!="" && $sUid==""){
	//Just Q Provided
	$query ="SELECT queue,uid,collect_date,collect_time,queue_status,queue_call,queue_note,room_no,check_drug_by FROM i_queue_list WHERE queue=? AND collect_date=? AND clinic_id=? ORDER BY collect_date DESC LIMIT 1";

}



if($query!=""){
	include("in_db_conn.php");
	$stmt = $mysqli->prepare($query);
	if($sQ!="" && $sUid=="") $stmt->bind_param("sss",$sQ,$sToday,$sClinicId);
	else $stmt->bind_param("s",$sUid);
	
	if($stmt->execute()){
		$stmt->bind_result($queue,$uid,$collect_date,$collect_time,$queue_status,$queue_call,$queue_note,$room_no,$check_drug_by);
		while($stmt->fetch()){
			$_GET["q"]=$queue;
			$_GET["uid"]=$uid;
			$_GET["coldate"]=$collect_date;
			$_GET["coltime"]=$collect_time;
		}
	}
	$mysqli->close();
	$sQ=getQS("q");
	$sUid = getQS("uid");
	$sColD = getQS("coldate");
	$sColT = urldecode(getQS("coltime"));
}


$sHt=getDataAttr($sUid,$sColD,$sColT,$sQ);
?>


<div class='fl-wrap-col h-sm border patient-form'  >
    <? include("lab_inc_patient_info.php"); ?>
</div>
<div  id='divDIM' class='fl-wrap-col doctor-main' <? echo($sHt); ?> >
	<? if($sColD!="") include("doctor_main.php"); else echo("ไม่พบข้อมูล / No data found."); ?>
</div>
<div class='fl-wrap-col doctor-main-load' style='display:none'>
	<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
</div>
<script>
	$(document).ready(function(){



		$("#divDIM #btnAddDrug").off("click");
		$("#divDIM").on("click","#btnAddDrug",function(){
			obR = $(this).closest("#divDIM");
			sUid = $(obR).attr("data-uid");
			sColD = $(obR).attr("data-coldate");
			sColT = $(obR).attr("data-coltime");

			sUrl="supply_order_dlg.php?"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,"Supply Order "+qsTitle(sUid,sColD,sColT),"90%","90%","",
			function(sResult){
				if(sResult=="REFRESH"){
					sUrl  ="medicine_inc_total_value.php?"+qsTxt(sUid,sColD,sColT);
					$("#divDIM").find("#divTotalMedicine").load(sUrl,function(){
						resetBill(sUid,sColD,sColT);
					});
				}
			},false,function(){
				//Load Done Function
			});

		});

		$("#divDIM #btnAddLab").off("click");
		$("#divDIM").on("click","#btnAddLab",function(){
			obR = $(this).closest("#divDIM");
			sUid = $(obR).attr("data-uid");
			sColD = $(obR).attr("data-coldate");
			sColT = $(obR).attr("data-coltime");

			sUrl="lab_order_inc_main.php?is_doctor=1&is_pribta=1&"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,"Lab Order "+qsTitle(sUid,sColD,sColT),"99%","99%","",
			function(sResult){
				resetBill(sUid,sColD,sColT);

				if(sResult=="REFRESH"){
					
				}
			},false,function(){
				//Load Done Function
			});
		});



		$(".patient-form .ddl-visit").off("change");
		$(".patient-form").on("change",".ddl-visit",function(){
			let sT=$(this).val();
			let aT=sT.split(" ");
			sUid=$(this).closest(".data-row").attr("data-uid");
			startLoad($(".doctor-main"),$(".doctor-main-load"));
			sTarget = "doctor_main.php";
			sUrl=sTarget+"?uid="+sUid+"&coldate="+aT[0]+"&coltime="+aT[1];
			$(".doctor-main").load(sUrl,function(){
				endLoad($(".doctor-main"),$(".doctor-main-load"));
			});
		});

		function resetBill(sUid,sColD,sColT){
			sUrl  ="lab_inc_total_pric.php?"+qsTxt(sUid,sColD,sColT);
			$("#divDIM").find("#divTotalBill").load(sUrl,function(){

			});
		}
	});

</script>