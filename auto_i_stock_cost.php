<?
$sToday=date("Y-m-d");
$sColD=getQS("coldate",$sToday);

$aToday=explode("-", $sColD);

$sD=$aToday[2];
$sM=$aToday[1];
$sY=$aToday[0];
$sPrevM="";
//Check if January
if($sM-1 < 0) {$sY= $sY-1; $sM="12"}
else $sM=$sM-1;
$sPrevM=$sY."-".str_pad($sM, 2,"0")."-01";

$sNow=date("Y-m-d H:i:s");
$sError="";

include("in_db_conn.php");
$query = "INSERT INTO i_stock_cost(cost_date,clinic_id,supply_code,stock_lot,stock_amt,stock_cost,stock_exp_date,stock_location,stock_note,updated_date)
SELECT ?,ISL.clinic_id,ISL.supply_code,stock_lot,stock_amt,stock_cost,stock_exp_date,stock_location,stock_note,NOW() 
FROM i_stock_list ISL LEFT JOIN i_stock_master ISM
ON ISM.supply_code=ISL.supply_code
LEFT JOIN i_stock_group ISG
ON ISG.supply_group_code=ISM.supply_group_code
WHERE ISG.supply_group_type IN (1,9) AND stock_amt > 0
ON DUPLICATE KEY UPDATE stock_amt = ISL.stock_amt,updated_date=NOW();";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$sColD);
if($stmt->execute()){
	$iAffRow =$stmt->affected_rows;
	if($iAffRow > 0) {
		$sError.=$sNow." : $iAffRow Row(s) Inserted.".PHP_EOL;
		//Update total amount
		$query="INSERT INTO i_stock_cost(cost_date,clinic_id,supply_code,stock_lot,stock_amt,total_amt)
			SELECT ?,clinic_id,supply_code,supply_lot,0,total_amt
			FROM (SELECT ISO.clinic_id,ISO.supply_code,ISO.supply_lot,sum(ISO.dose_day) AS total_amt
			FROM i_stock_order ISO 
			LEFT JOIN i_stock_master ISM ON ISM.supply_code=ISO.supply_code
			LEFT JOIN i_stock_group ISG	ON ISG.supply_group_code=ISM.supply_group_code
			WHERE ISG.supply_group_type IN (1,9) AND ISO.dose_day > 0 AND collect_date >= ? AND collect_date < ?
			GROUP BY ISO.clinic_id,ISO.supply_code,ISO.supply_lot) AS ISOAMT
			ON DUPLICATE KEY UPDATE total_amt = ISOAMT.total_amt";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sss',$sColD,$sPrevM,$sColD);
		if($stmt->execute()){}


		$query="INSERT INTO i_stock_cost(cost_date,clinic_id,supply_code,stock_lot,stock_amt,stock_exp_date,received_amt)
			SELECT ?,'IHRI',supply_code,stock_lot,0,exp_date,total_amt
			FROM (SELECT ISR.supply_code,ISR.stock_lot,ISR.exp_date,SUM(ISR.supply_amt) AS total_amt
			FROM i_stock_recieved ISR 
			LEFT JOIN i_stock_master ISM
			ON ISM.supply_code=ISR.supply_code
			LEFT JOIN i_stock_group ISG
			ON ISG.supply_group_code=ISM.supply_group_code
			WHERE ISG.supply_group_type IN (1,9) AND ISR.supply_amt > 0 AND recieved_datetime >= ? AND recieved_datetime < ?
			GROUP BY ISR.clinic_id,ISR.supply_code,ISR.stock_lot,ISR.exp_date) AS ISOAMT
			ON DUPLICATE KEY UPDATE received_amt = ISOAMT.total_amt";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sss',$sColD,$sPrevM,$sColD);
		if($stmt->execute()){}

	}else{
		$sError.=$sNow." : Error No Row Updated.".PHP_EOL;
	}
}else{
	$sError.=$sNow." : Error Excuted Query - ".$stmt->error.PHP_EOL;
}
$stmt->close();
$mysqli->close();

if($sError!=""){
	$myfile = fopen("logs/i_stock_cost_logs.txt", "a") or die("Unable to open file!");
	fwrite($myfile, $sError );
	fclose($myfile);
	unset($myfile);
}
exit;