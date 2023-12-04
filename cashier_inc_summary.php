<?
include_once("in_session.php");
include_once("in_php_function.php");

$sClinicId=getSS("clinic_id");
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sBillId=getQS("billid");

include("in_db_conn.php");
$sHtml="";
$aSup=array();
$aSum=array();$aInfo=array();
$iSumTotal = 0;
$aPInfo=array();
$sToday=date("Y-m-d");

function getSubRow($sUid,$sColD,$sColT,$order_id,$data_id,$supply_name,$row_icon,$is_paid,$sale_opt_name,$order_type,$total_price,$supply_amt=""){
	$sBtnEdit = "<i class='btneditsaleopt btn-cashier-edit fabtn fas fa-edit' data-ordertype='".$order_type."' data-orderid='".$order_id."' data-dataid='".$data_id."' ></i>";
	$sToday=date("Y-m-d");
	//if($sColD != $sToday || $is_paid) $sBtnEdit = "";
	//if($is_paid) $sBtnEdit = "<i class='fas fa-dollar-sign fa-lg' title='Paid'></i>";

	return "<div class=' fl-wrap-row row-hover h-25 row-detail row-color-2 ' data-ispaid='$is_paid' data-uid='$sUid' data-coldate='$sColD' data-coltime='$sColT'><div class='fl-fix w-50 fl-mid'><i class='$row_icon'></i></div><div class='fl-fill' title='$data_id'>".$supply_name."</div>
				<div class='fl-fix w-30 fl-mid'>$sBtnEdit</div>
				<div class='fl-fix w-180 fs-xsmall v-mid lh-12'>$sale_opt_name</div>
				<div class='fl-fix w-100 fs-smaller fl-mid'>".(($supply_amt=="")?"":$supply_amt)."</div>
			<div class='fl-fix w-100 fl-mid'>$total_price</div>
			</div>";


}
$aSaleOptSum = array(); $aSaleName=array();
//Try retrieve BillId if not sent
if($sBillId==""){
	$query="SELECT bill_id FROM i_queue_list IQL
	LEFT JOIN i_bill_detail IBD
	ON IBD.clinic_id = IQL.clinic_id
	AND IBD.bill_q = IQL.queue
    AND IBD.bill_date = IQL.collect_date
	AND IBD.bill_q_type=IQL.queue_type
	WHERE IQL.clinic_id=? AND IQL.uid = ? AND IQL.collect_date = ? AND IQL.collect_time = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sClinicId,$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($bill_id);
		while($stmt->fetch()){
			$sBillId=$bill_id;
		}
	}
}

if($sBillId==""){
	$query="SELECT order_code,ISO.supply_code,supply_name,supply_group_name,ISG.supply_group_type,IST.supply_type_name,is_service,total_price,is_paid,SO.sale_opt_id,sale_opt_name FROM i_stock_order ISO LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISO.supply_code LEFT JOIN i_stock_group ISG ON ISG.supply_group_code = ISM.supply_group_code LEFT JOIN i_stock_type IST ON IST.supply_group_type = ISG.supply_group_type LEFT JOIN sale_option SO ON SO.sale_opt_id = ISO.sale_opt_id WHERE uid=? AND collect_date=? AND collect_time=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($order_code,$supply_code,$supply_name,$supply_group_name,$supply_group_type,$supply_type_name,$is_service,$total_price,$is_paid,$sale_opt_id,$sale_opt_name);
		while($stmt->fetch()){
			$aSaleOptSum[$sale_opt_id] = (isset($aSaleOptSum[$sale_opt_id])?$aSaleOptSum[$sale_opt_id]:0)+$total_price;
			$aSaleName[$sale_opt_id] = $sale_opt_name;

			$aSum[$supply_group_type] = (isset($aSum[$supply_group_type])?$aSum[$supply_group_type]:0) + $total_price;
			$aInfo[$supply_group_type]["name"] = $supply_type_name;
			$aInfo[$supply_group_type]["isservice"] = $is_service;
			$aSup[$supply_group_type] = (isset($aSup[$supply_group_type])?$aSup[$supply_group_type]:"").
			getSubRow($sUid,$sColD,$sColT,$order_code,$supply_code,$supply_name,"fas fa-chevron-circle-right",$is_paid,$sale_opt_name,"supply",$total_price);
		}
	}

	$query="SELECT PLO.lab_order_id,PLT.lab_id,PLT.lab_name,lab_group_name,ref_lab_id,PLOLT.sale_opt_id,sale_opt_name,PLOLT.sale_price,PLOLT.is_paid,PLOLT.paid_datetime,PLOLT.sale_cost FROM p_lab_order_lab_test PLOLT
		LEFT JOIN p_lab_order PLO

		ON PLO.uid = PLOLT.uid
		AND PLO.collect_date = PLOLT.collect_date
		AND PLO.collect_time = PLOLT.collect_time

		LEFT JOIN p_lab_test PLT
		ON PLT.lab_id = PLOLT.lab_id
		AND PLT.lab_group_id != ''

        LEFT JOIN p_lab_test_group PLTG
        ON PLTG.lab_group_id = PLT.lab_group_id

		LEFT JOIN sale_option SO
		ON SO.sale_opt_id = PLOLT.sale_opt_id


		WHERE PLOLT.uid=? AND PLOLT.collect_date=? AND PLOLT.collect_time=? AND lab_order_status != 'C'
		";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($lab_order_id,$lab_id,$lab_name,$lab_group_name,$ref_lab_id,$sale_opt_id,$sale_opt_name,$sale_price,$is_paid,$paid_datetime,$sale_cost);
		while($stmt->fetch()){
			$aSaleOptSum[$sale_opt_id] = (isset($aSaleOptSum[$sale_opt_id])?$aSaleOptSum[$sale_opt_id]:0)+$sale_price;
			$aSaleName[$sale_opt_id] = $sale_opt_name;

			//if(($ref_lab_id!="" && $ref_lab_id!=$lab_id) || ($sale_cost==0 && $ref_lab_id=="")){
			if(($ref_lab_id!="" && $ref_lab_id!=$lab_id)){
			}else{
				//Ok
				$aSum["lab"] = (isset($aSum["lab"])?$aSum["lab"]:0) + $sale_price;
				$aInfo["lab"]["name"] = "ค่าปฏิบัติการห้องแล๊บ/Laboratory";
				$aInfo["lab"]["isservice"] = "0";
				//$sHtml.="<div class='fl-wrap-row'>$supply_code</div>";
				

				$sLabName="";
				if($ref_lab_id=="" ){
					$sLabName = $lab_group_name." : <span class='fs-xsmall'>".$lab_name."</span>";
				}else if($lab_id==$ref_lab_id){
					$sLabName = $lab_group_name;
				}

				if($sLabName!=""){
					$aSup["lab"] = (isset($aSup["lab"])?$aSup["lab"]:"").getSubRow($sUid,$sColD,$sColT,$lab_order_id,$lab_id,$sLabName,"fas fa-chevron-circle-right",$is_paid,$sale_opt_name."[".$sale_cost."]","lab",$sale_price);	
				}
				

			}
		}
	}


}else{
	$query = "SELECT IQL.uid,IQL.collect_date,IQL.collect_time,ISO.supply_code,ISO.supply_desc,supply_lot,order_code,order_status,ISO.dose_day,total_price,is_paid,paid_datetime,is_pickup,order_by,ISM.supply_name,supply_unit,ISG.supply_group_code,ISG.supply_group_name,ISG.supply_group_type,is_service,supply_group_icon,supply_type_name,s_name,fname,sname,en_fname,en_sname,SO.sale_opt_id,sale_opt_name FROM i_bill_detail IBD
	LEFT JOIN i_queue_list IQL
	ON IQL.clinic_id=IBD.clinic_id	AND IQL.collect_date=IBD.bill_date
	AND IQL.queue=IBD.bill_q	AND IQL.queue_type=IBD.bill_q_type

	LEFT JOIN i_stock_order ISO
	ON ISO.clinic_id=IQL.clinic_id	AND ISO.uid= IQL.uid
	AND ISO.collect_date=IQL.collect_date	AND ISO.collect_time=IQL.collect_time

	LEFT JOIN i_stock_master ISM	ON ISM.supply_code = ISO.supply_code

	LEFT JOIN i_stock_group ISG	ON ISG.supply_group_code = ISM.supply_group_code

	LEFT JOIN i_stock_type IST	ON IST.supply_group_type = ISG.supply_group_type

	LEFT JOIN sale_option SO	ON SO.sale_opt_id = ISO.sale_opt_id

	LEFT JOIN p_staff PS	ON PS.s_id=ISO.order_by

	LEFT JOIN patient_info PI	ON PI.uid=IQL.uid

	WHERE IBD.clinic_id=? AND IBD.bill_id=? 

	ORDER BY IQL.uid,ISG.supply_group_type,ISG.supply_group_code,added_datetime";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicId,$sBillId);
	if($stmt->execute()){
		$stmt->bind_result($uid,$collect_date,$collect_time,$supply_code,$supply_desc,$supply_lot,$order_code,$order_status,$dose_day,$total_price,$is_paid,$paid_datetime,$is_pickup,$order_by,$supply_name,$supply_unit,$supply_group_code,$supply_group_name,$supply_group_type,$is_service,$supply_group_icon,$supply_type_name,$s_name,$fname,$sname,$fname_en,$sname_en,$sale_opt_id,$sale_opt_name);
		while($stmt->fetch()){
			$aSaleOptSum[$sale_opt_id] = (isset($aSaleOptSum[$sale_opt_id])?$aSaleOptSum[$sale_opt_id]:0)+$total_price;
			$aSaleName[$sale_opt_id] = $sale_opt_name;
			$aSum[$uid][$supply_group_type] = (isset($aSum[$uid][$supply_group_type])?$aSum[$uid][$supply_group_type]:0) + $total_price;
			$aInfo[$supply_group_type]["name"] = $supply_type_name;
			$aInfo[$supply_group_type]["is_service"] = $is_service;
			$aInfo[$supply_group_type]["icon"] = $supply_group_icon;

			$aPInfo[$uid]= (($fname=="")?$fname_en." ".$sname_en:$fname." ".$sname);
			//$sHtml.="<div class='fl-wrap-row'>$supply_code</div>";

			$iPrice = (($total_price==0)?0:$total_price/$dose_day);

			$sRow = ""; $sAmt = "";
			if($is_service){}else $sAmt=$dose_day." x ". $iPrice;

			if($supply_name!="")
			$sRow = getSubRow($uid,$collect_date,$collect_time,$order_code,$supply_code,$supply_name,"fas fa-chevron-circle-right",$is_paid,$sale_opt_name,"supply",$total_price,$sAmt);

			/*
			if($is_service){
				$aSup[$uid][$supply_group_code] = (isset($aSup[$uid][$supply_group_code])?$aSup[$uid][$supply_group_code]:"").$sRow;
			}else{
				
			}*/

			$aSup[$uid][$supply_group_type] = (isset($aSup[$uid][$supply_group_type])?$aSup[$uid][$supply_group_type]:"").$sRow;

		}
	}

	$query="SELECT IQL.uid,IQL.collect_date,IQL.collect_time,PLO.lab_order_id,PLT.lab_id,PLT.lab_name,lab_group_name,ref_lab_id,PLOLT.sale_opt_id,sale_opt_name,PLOLT.sale_price,PLOLT.is_paid,PLOLT.paid_datetime ,fname,en_fname,sname,en_sname
		FROM  i_bill_detail IBD 

		LEFT JOIN i_queue_list IQL		ON IQL.clinic_id = IBD.clinic_id
		AND IQL.queue = IBD.bill_q
		AND IQL.collect_date = IBD.bill_date
		AND IQL.queue_type = IBD.bill_q_type
            
		JOIN p_lab_order PLO		ON PLO.uid = IQL.uid
		AND PLO.collect_date = IQL.collect_date		AND PLO.collect_time = IQL.collect_time
		AND lab_order_status != 'C'

		LEFT JOIN p_lab_order_lab_test PLOLT ON PLOLT.uid = PLO.uid
		AND PLOLT.collect_date = PLO.collect_date		AND PLOLT.collect_time = PLO.collect_time

		LEFT JOIN p_lab_test PLT		ON PLT.lab_id = PLOLT.lab_id		AND PLT.lab_group_id != ''

        LEFT JOIN p_lab_test_group PLTG        ON PLTG.lab_group_id = PLT.lab_group_id

		LEFT JOIN sale_option SO		ON SO.sale_opt_id = PLOLT.sale_opt_id

		LEFT JOIN patient_info PI	ON PI.uid=IQL.uid

		WHERE IBD.clinic_id=? AND IBD.bill_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicId,$sBillId);
	if($stmt->execute()){
		$stmt->bind_result($uid,$collect_date,$collect_time,$lab_order_id,$lab_id,$lab_name,$lab_group_name,$ref_lab_id,$sale_opt_id,$sale_opt_name,$sale_price,$is_paid,$paid_datetime,$fname,$fname_en,$sname,$sname_en);
		while($stmt->fetch()){
				//$sLabName = ($lab_id==$ref_lab_id && $ref_lab_id != "")?$lab_group_name:$lab_name;
				$aSaleOptSum[$sale_opt_id] = (isset($aSaleOptSum[$sale_opt_id])?$aSaleOptSum[$sale_opt_id]:0)+$sale_price;
				$aSaleName[$sale_opt_id] = $sale_opt_name;


				//Ok
				$aSum[$uid]["lab"] = (isset($aSum[$uid]["lab"])?$aSum[$uid]["lab"]:0) + $sale_price;
				$aInfo["lab"]["name"] = "ค่าปฏิบัติการห้องแล๊บ/Laboratory";
				$aInfo["lab"]["is_service"] = "0";
				$aInfo["lab"]["icon"] = "fas fa-vial";

				$aPInfo[$uid]= (($fname=="")?$fname_en." ".$sname_en:$fname." ".$sname);
				$sRow = "";
			
				$sLabName="";
				if($ref_lab_id=="" ){
					$sLabName = $lab_group_name." : <span class='fs-xsmall'>".$lab_name."</span>";
				}else if($lab_id==$ref_lab_id){
					$sLabName = $lab_group_name;
				}

				if($sLabName!=""){
					$sRow  = getSubRow($uid,$collect_date,$collect_time,$lab_order_id,$lab_id,$sLabName,"fas fa-chevron-circle-right",$is_paid,$sale_opt_name,"lab",$sale_price,"");
					$aSup[$uid]["lab"] = (isset($aSup[$uid]["lab"])?$aSup[$uid]["lab"]:"").$sRow;
				}
		}
	}


}





$mysqli->close();

if($sBillId==""){
	foreach ($aSum as $sGrpType => $iTotal) {
		$sHtml.="
				<div class='fl-wrap-row h-30 row-color row-hover fl-cas-head' style='border-bottom:1px solid silver'>
					<div class='fl-fill'>".$aInfo[$sGrpType]["name"]."</div>
					<div class='fl-fix w-130 al-right'>".$iTotal." บาท</div>
				</div>
				<div class='fl-cas-body fl-auto' style='display:none'>
				".$aSup[$sGrpType]."
				</div>
			";
		$iSumTotal += ($iTotal*1);
	}
}else{
	$aHtml= array();
	$aHead= array(); 
	foreach ($aSum as $sUid => $aGrp) {
		$aHead[$sUid]="<div class='fl-wrap-row h-25 f-border bg-head-2'>
			<div class='fl-fix w-30 fl-mid fabtn btntogglesub' style='color:white'><i class='fa fa-toggle-on'></i></div>
			<div class='fl-fill'>".$sUid." - ".$aPInfo[$sUid]."</div>";
		$iUidSum=0;
		foreach ($aGrp as $sGrpType => $iTotal) {

			if($aInfo[$sGrpType]["name"]==""){

			}else{
				$aHtml[$sUid]=(isset($aHtml[$sUid])?$aHtml[$sUid]:"")."
					<div class='fl-wrap-row h-30 row-color row-hover fl-cas-head' style='border-bottom:1px solid silver'>
						<div class='fl-fix w-30 fl-mid'>".(($aInfo[$sGrpType]["icon"]!="")?"<i class='fa ".$aInfo[$sGrpType]["icon"]." '></i>":"")."</div>
						<div class='fl-fill'>".$aInfo[$sGrpType]["name"]."</div>
						<div class='fl-fix w-130 al-right'>".$iTotal." บาท</div>
					</div>
					<div class='fl-cas-body' style='display:none'>
					".$aSup[$sUid][$sGrpType]."
					</div>
				";
			}

			$iUidSum+= ($iTotal*1);
			$iSumTotal+= ($iTotal*1);


		}
		$aHead[$sUid].="<div class='fl-fix w-100 al-right'>$iUidSum บาท</div>
		</div>";
	}
	foreach ($aHead as $sUid => $sHeader) {
		$sHtml.=$sHeader.(isset($aHtml[$sUid])?$aHtml[$sUid]:"");
	}
}

$sSaleHtml="";
foreach ($aSaleOptSum as $sale_opt_id => $iSaleOptSum) {
	$sSaleHtml.="<div class='fl-wrap-row row-color'>
		<div class='fl-fix w-80 fl-mid'>$sale_opt_id</div>
		<div class='fl-fill fl-mid'>".$aSaleName[$sale_opt_id]."</div>
		<div class='fl-fix w-100'>$iSaleOptSum</div>
	</div>";
}


$sHtml = "<div id='divSupplyTotal' class='fl-wrap-col supply-order-list fl-auto' data-uid='$sUid' data-coldate='$sColD' data-coltime='$sColT' data-total='$iSumTotal'>
		$sHtml
	</div>
	<div class='fl-wrap-row h-25 lh-25 row-hover fl-cas-head' style='background-color:#323211;color:white ;border-bottom:1px solid silver'>
		<div class='fl-fill'>ยอดรวม/Total <span class='fs-xsmall'>(click to view in detail)</span></div>
		<div class='fl-fix w-130 al-right'>".$iSumTotal." บาท</div>
	</div>
	<div class='fl-cas-body' style='display:none'>
		<div class='fl-wrap-col fl-auto'>
			$sSaleHtml
		</div>
	</div>
	";

?>
<? echo($sHtml); ?>


<script>

	$("#divSupplyTotal .btneditsaleopt").off("click");
	$("#divSupplyTotal").on("click",".btneditsaleopt",function(){
		obR = $(this).closest(".row-detail");
		sUid = $(obR).attr("data-uid");
		sColDate = $(obR).attr("data-coldate");
		sColTime = $(obR).attr("data-coltime");
		sSupplyType=$(this).attr("data-ordertype");
		sDataId=$(this).attr("data-dataid");
		sOrderCode=$(this).attr("data-orderid");


		sUrl="supply_order_edit_dlg.php?"+qsTxt(sUid,sColDate,sColTime)+"&supply_code="+sDataId+"&datatype="+sSupplyType+"&order_code="+sOrderCode+"&u_mode=cashier";

		if(sSupplyType=="lab") sUrl="lab_inc_sale_opt.php?"+qsTxt(sUid,sColDate,sColTime)+"&labid="+sDataId+"&datatype="+sSupplyType+"&laborderid="+sOrderCode+"&u_mode=cashier";

		showDialog(sUrl,"Supply Order "+qsTitle(sUid,sColDate,sColTime),"250","50%","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
				
				sUrl="cashier_inc_summary.php?"+qsTxt(sUid,sColDate,sColTime);
				$("#divSupplyTotal").parent().load(sUrl,function(){
					
				});
			}
		},false,function(){
			//Load Done Function
		});
	});

</script>