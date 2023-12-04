<?
include_once("in_php_function.php");
include_once("in_setting_row.php");
include("in_db_conn.php");
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sToday=date("Y-m-d");
$sViewMode=getQS("viewmode");
$sTemp="";
$query = "SELECT order_code,ISO.supply_code,supply_name,supply_unit,order_status,ISO.sale_opt_id,sale_opt_name,ISO.dose_before,ISO.dose_breakfast,ISO.dose_lunch,ISO.dose_dinner,ISO.dose_night,ISO.supply_desc,ISO.order_note,ISO.dose_day,ISO.sale_price,ISO.total_price,is_service,is_paid,is_pickup,ISG.supply_group_type,ISO.supply_lot
FROM i_stock_order ISO
LEFT JOIN i_stock_master ISM
ON ISM.supply_code = ISO.supply_code

LEFT JOIN i_stock_group ISG
ON ISG.supply_group_code = ISM.supply_group_code

LEFT JOIN i_stock_type IST
ON IST.supply_group_type = ISG.supply_group_type

LEFT JOIN sale_option SO
ON SO.sale_opt_id = ISO.sale_opt_id

WHERE uid=? AND collect_date=? AND collect_time=? ORDER BY supply_group_type,supply_code";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColD,$sColT);
if($stmt->execute()){
	$stmt->bind_result($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_type,$supply_lot);
	$sPrevSupCode="";
	while($stmt->fetch()){
		if($sViewMode=="CASH"){
			$sTemp.=getCashOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_type);
		}else if($sViewMode=="PHX"){
			$show_print_sum="";
			if($sPrevSupCode==$supply_code) $show_print_sum=1; 
			$sTemp.=getViewOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_type,$show_print_sum,$supply_lot);
			$sPrevSupCode=$supply_code;
		}else{
			$sTemp.=getOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_type);
		}
		
	}
}


//


$mysqli->close();
echo($sTemp);
?>