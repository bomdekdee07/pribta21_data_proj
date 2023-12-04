<?
//Sale Option
include_once("in_php_function.php");
$sToday = date("Y-m-d");
$sColDate = getQS("coldate");
$sColTime=getQS("coltime");
$sUid = getQS("uid");
$isToday = ($sToday==$sColDate);

?>
<style>
	.order-body{
		font-size:12px;
	}
	.order-body .order-row{

	}
	.order-body .order-row:hover{
		filter:brightness(80%);
	}

	.order-body .order-name{
		min-width:200px;
	}
	.order-body .order-details{
		min-width:100px;
		display:none;
	}
	.order-body .order-dose-day{
		min-width:50px;max-width:50px;
	}
	.order-body .order-amt{
		min-width:50px;max-width:50px;
	}
	.order-body .order-price{
		min-width:50px;max-width:50px;
	}
	.order-body .order-total{
		max-width:50px;
		min-width:50px;
	}
</style>

<div class='fl-wrap-col order-head row-info' style='max-height: 20px' data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColDate); ?>' data-coltime='<? echo($sColTime); ?>'>
	<div class='order-row fl-wrap-row row-color fl-mid' >
		<div id='btnAddSupply' class='order-cmd fl-fix w-m '><i class='fabtn far fa-plus-square f-border roundcorner' style='padding:2px 10px;background-color: green;color:white; <? echo(($isToday)?"":"display:none"); ?>'>Add</i></div> 
		<div class='order-name fl-fill'>Name</div>
		<div class='order-dose-day fl-fix order-hide'  style='display:none'></div>
		<div class='order-details fl-fill' style='display:none'>
			<div class='order-times drug-only'>
				<span class='lang-th'>Dose</span>
				<span class='lang-th order-bf'>BF</span>
				<span class='lang-th order-lunch'>L</span>
				<span class='lang-th order-dinner'>D</span>
				<span class='lang-th order-bed'>Bed</span>
			</div>
			<div style='display:none'>Desc</div>
			<div>Note</div>
		</div>
		<div class='order-amt fl-fix w-m'>Amt</div>
		<div id='btnAddLab' class='order-cmd fl-fix w-m '><i class='fabtn far fa-plus-square f-border roundcorner' style='padding:2px 10px;background-color: green;color:white;'>Lab</i></div> 
	</div>
</div>
<div class='fl-fill fl-auto order-body fs-xs' style='background-color:white'>
	<? include("supply_inc_order_drug.php"); ?>
</div>

<script>
$(function(){
	$("#btnAddSupply").off("click");
	$("#btnAddSupply").on("click",function(){
		obR = $(this).closest(".row-info");
		sUid = $(obR).attr("data-uid");
		sColDate = $(obR).attr("data-coldate");
		sColTime = $(obR).attr("data-coltime");

		sUrl="supply_order_dlg.php?"+qsTxt(sUid,sColDate,sColTime);
		showDialog(sUrl,"Supply Order "+qsTitle(sUid,sColDate,sColTime),"90%","90%","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});

	$("#btnAddLab").off("click");
	$("#btnAddLab").on("click",function(){
		obR = $(this).closest(".row-info");
		sUid = $(obR).attr("data-uid");
		sColDate = $(obR).attr("data-coldate");
		sColTime = $(obR).attr("data-coltime");

		sUrl="lab_order_inc_main.php?is_doctor=1&is_pribta=1&"+qsTxt(sUid,sColDate,sColTime);
		showDialog(sUrl,"Lab Order "+qsTitle(sUid,sColDate,sColTime),"99%","99%","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});
});
</script>