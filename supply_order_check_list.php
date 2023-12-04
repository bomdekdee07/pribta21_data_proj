<?
include_once("in_session.php");
include_once("in_php_function.php");

$sClinicId=getSS("clinic_id");
$sUid = getQS("uid");
$sColD = getQS("coldate");
$sColT = getQS("coltime");
$sToday=date("Y-m-d");
$aOrder = array();

include("in_db_conn.php");
$query = "SELECT ISO.supply_code,supply_name,ISO.supply_lot,ISO.order_code,ISO.order_status,ISO.dose_day,ISO.sale_price,ISO.is_paid,ISO.total_price,ISO.proj_id,proj_name FROM i_stock_order ISO
LEFT JOIN i_stock_master ISM
ON ISM.supply_code=ISO.supply_code

LEFT JOIN i_stock_group ISG
ON ISG.supply_group_code = ISM.supply_group_code

LEFT JOIN p_project PP
ON PP.proj_id = ISO.proj_id


WHERE ISG.supply_group_type != '3' AND clinic_id=? AND ISO.uid=? AND collect_date=? AND collect_time=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssss",$sClinicId,$sUid,$sColD,$sColT);
if($stmt->execute()){
	$stmt->bind_result($supply_code,$supply_name,$supply_lot,$order_code,$order_status,$dose_day,$sale_price,$is_paid,$total_price,$proj_id,$proj_name);
	while ($stmt->fetch()) {
		$aOrder[$supply_code]["supply_lot"] = $supply_lot;
		$aOrder[$supply_code]["supply_name"] = $supply_name;
		$aOrder[$supply_code]["total_price"] = $total_price;
		$aOrder[$supply_code]["is_paid"] = $is_paid;
		$aOrder[$supply_code]["order_code"] = $order_code;
		$aOrder[$supply_code]["proj_id"] = $proj_id;
		$aOrder[$supply_code]["proj_name"] = $proj_name;
	}	
}

//Lab List

$aLabOrder=array();
$query="SELECT PLOLT.lab_id,lab_name,lab_unit,is_paid,sale_price,PLOLT.proj_id,proj_name FROM p_lab_order_lab_test PLOLT
LEFT JOIN p_lab_test PLT
ON PLT.lab_id= PLOLT.lab_id
LEFT JOIN p_project PP
ON PP.proj_id=PLOLT.proj_id
WHERE PLOLT.sale_price > 0 AND uid=? AND collect_date=? AND collect_time=? AND sale_price > 0";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColD,$sColT);
if($stmt->execute()){
	$stmt->bind_result($lab_id,$lab_name,$lab_unit,$is_paid,$sale_price,$proj_id,$proj_name);
	while ($stmt->fetch()) {
		$aLabOrder[$lab_id]["lab_name"] = $lab_name;
		$aLabOrder[$lab_id]["sale_price"] = $sale_price;
		$aLabOrder[$lab_id]["is_paid"] = $is_paid;
		$aLabOrder[$lab_id]["proj_id"] = $proj_id;
		$aLabOrder[$lab_id]["proj_name"] = $proj_name;
	}	
}



$mysqli->close();

$sHtml="";

foreach ($aOrder as $supply_code => $aT) {
	$sHtml.="<div class='order-row fl-wrap-row h-30 row-hover row-color ' data-supcode='".$supply_code."' data-type='supply' data-ordercode='".$aT["order_code"]."' data-total='".$aT["total_price"]."'>
		<div class='fl-fix w-50 fl-mid'><input type='checkbox' class='chksalecode bigcheckbox' /></div>
		<div class='fl-fix w-150 fs-small'>".$aT["proj_name"]."</div>
		<div class='fl-fill fs-smaller lh-15 al-left'>$supply_code ".$aT["supply_name"]."</div>
		<div class='fl-fix w-30 fl-mid'>".(($aT["is_paid"])?"<i class='fa fa-dollar-sign fa-lg'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".$aT["total_price"]."</div>
	</div>";
}

foreach ($aLabOrder as $lab_id => $aT) {
	$sHtml.="<div class='order-row fl-wrap-row h-30 row-hover row-color ' data-labid='".$lab_id."' data-type='lab' data-total='".$aT["sale_price"]."'>
		<div class='fl-fix w-50 fl-mid'><input type='checkbox' class='chksalecode bigcheckbox' /></div>
		<div class='fl-fix w-150 fs-small'>".$aT["proj_name"]."</div>
		<div class='fl-fill fs-smaller lh-15 al-left'>$lab_id ".$aT["lab_name"]."</div>
		<div class='fl-fix w-30 fl-mid'>".(($aT["is_paid"])?"<i class='fa fa-dollar-sign fa-lg'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".$aT["sale_price"]."</div>
	</div>";
}


echo($sHtml);
?>

