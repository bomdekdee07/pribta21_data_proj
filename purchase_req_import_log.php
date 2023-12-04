<?
include_once("in_php_function.php");
$sReqId=getQS("reqid");

if($sReqId==""){
	echo("Error: No request_id found.");
	exit();
}
include("in_db_conn.php");

$query="SELECT ISR.supply_code,supply_name,stock_lot,supply_amt,exp_date,recieved_by,recieved_datetime,s_name,supply_unit FROM i_stock_recieved ISR
LEFT JOIN i_stock_master ISM
ON ISM.supply_code=ISR.supply_code
LEFT JOIN p_staff PS
ON PS.s_id=ISR.recieved_by
WHERE ISR.request_id=? ORDER BY recieved_datetime";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sReqId);
$sHtml="";
if($stmt->execute()){
	$stmt->bind_result($supply_code,$supply_name,$stock_lot,$supply_amt,$exp_date,$recieved_by,$recieved_datetime,$s_name,$supply_unit);
	while ($stmt->fetch()) {
		$sHtml.="<div class='fl-wrap-row row-color row-hover h-30'>
			<div class='fl-fix w-150 fl-mid'>$recieved_datetime</div>
			<div class='fl-wrap-col fs-smaller'>
				<div class='fl-fix h-15 fw-b'>$supply_code</div>
				<div class='fl-fix h-15 lh-15'>$supply_name</div>
			</div>
			<div class='fl-wrap-col fs-smaller'>
				<div class='fl-fix h-15 fw-b'>$s_name</div>
				<div class='fl-fix h-15 lh-15'>$recieved_datetime</div>
			</div>
			<div class='fl-fix w-100'>$stock_lot</div>
			<div class='fl-fix w-130'>$exp_date</div>
			<div class='fl-fix w-150'>$supply_amt $supply_unit</div>
		</div>";
	}	
}

$mysqli->close();

?>
<div class='fl-wrap-row bg-head-1 h-30'>
	<div class='fl-fix w-150'>Date</div>
	<div class='fl-fill'>Supply</div>
	<div class='fl-fill'>User</div>
	<div class='fl-fix w-100'>Lot #</div>
	<div class='fl-fix w-130'>Exp. Date</div>
	<div class='fl-fix w-150'>Amt</div>
</div>
<div class='fl-wrap-col fl-auto'>
	<? echo($sHtml); ?>
</div>