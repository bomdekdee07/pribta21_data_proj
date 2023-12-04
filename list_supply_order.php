<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
include_once("in_setting_row.php");

$sUid = getQS("uid");
$sColDate =getQS("coldate");
$sColTime =getQS("coltime");

/*
$sUid = "P20-09124";
$sColDate = "2021-01-11";
$sColTime = "10:29:07";
*/

$aSaleOpt = array();
$query = "SELECT sale_opt_id,sale_opt_name FROM sale_option;";
$stmt=$mysqli->prepare($query);
if($stmt->execute()){
	$stmt->bind_result($sale_opt_id,$sale_opt_name);
	while ($stmt->fetch()) {
	   $aSaleOpt[$sale_opt_id] = $sale_opt_name;
	}
}

$query = "SELECT order_id,JSO.supply_code,JSM.supply_name,supply_unit,JSO.dose_day,JSO.dose_per_time,JSO.dose_before,JSO.dose_breakfast,JSO.dose_lunch,JSO.dose_dinner,JSO.dose_night,JSO.order_note,JSO.sale_opt_id,JSO.stock_lot,sale_price,total_amt,order_status,supply_group_type,JSO.supply_desc,s_name FROM j_stock_order JSO
LEFT JOIN j_stock_master JSM
ON JSM.supply_code = JSO.supply_code
LEFT JOIN j_stock_group JSG
ON JSG.supply_group_code = JSM.supply_group_code
LEFT JOIN j_stock_sale_opt JSSO
ON JSSO.supply_code = JSO.supply_code
AND JSSO.sale_opt_id = JSO.sale_opt_id 
LEFT JOIN p_staff PS
ON PS.s_id = JSO.order_by";

$query .="  WHERE collect_date=? AND collect_time=? AND uid=? AND order_status != '00'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sColDate,$sColTime,$sUid);
if($stmt->execute()){
	$stmt->bind_result($order_id,$supply_code,$supply_name,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$order_note,$sale_opt_id,$stock_lot,$sale_price,$total_amt,$order_status,$supply_group_type,$supply_desc,$s_name);

	$sHtml = ""; 
	while ($stmt->fetch()) {
		$sHtml .= getSupOrder($order_id,$supply_code,$supply_name,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$order_note,$sale_opt_id,$stock_lot,$sale_price,$total_amt,$order_status,$supply_group_type,$supply_desc,$s_name);
	}
}
$mysqli->close();
echo($sHtml);	
?>