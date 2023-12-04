<?
include('in_db_conn.php');
include_once("in_php_function.php");
include_once("class_pdf.php");

$sTime = urlDecode(getQS("coltime"));
$sUid = getQS("uid");
$sCode = getQS("supcode");
$orderCode = getQS("ordercode");
$sColDate = urlDecode(getQS("coldate"));
$lot = getQS("lot");
$sLang = getQS("lang","th");
// echo "test".$orderCode."/".$sCode;

//Get Que #
$query = "SELECT queue FROM i_queue_list WHERE uid=? AND collect_date = ? AND collect_time=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sTime);

$iQueue ="";
if($stmt->execute()){
	$stmt->bind_result($queue );
	while ($stmt->fetch()) {
		$iQueue = $queue;
	}
}

$query = "select JSO.supply_code,
	JSO.supply_lot as stock_lot,
	JSO.order_by,
	JSO.dose_day,
	JSO.dose_per_time,
	JSO.dose_before,
	JSO.dose_breakfast,
	JSO.dose_lunch,
	JSO.dose_dinner,
	JSO.dose_night,
	JSO.dose_day as total_amt,
	JSO.sale_opt_id,
	JSO.order_note,
	JSO.order_status, 
	JSO.supply_desc,
	JSSO.stock_exp_date,
	JSM.supply_name,
	JSM.dose_note,
	JSM.supply_unit,
	PI.fname,
	PI.sname
from i_stock_order JSO
LEFT JOIN i_stock_list JSSO ON (JSSO.supply_code = JSO.supply_code AND JSSO.clinic_id = JSO.clinic_id AND JSSO.stock_lot = JSO.supply_lot)
LEFT JOIN i_stock_master JSM ON (JSM.supply_code = JSSO.supply_code)
LEFT JOIN patient_info PI ON (PI.uid = JSO.uid)
where collect_date = ? and collect_time = ? and JSO.uid = ? and JSO.supply_code = ? and order_code = ? and JSO.supply_lot = ?
order by order_code ASC
LIMIT 1;";

$aRes = array();
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssssss", $sColDate, $sTime, $sUid, $sCode, $orderCode, $lot);
if($stmt->execute()){
	$stmt->bind_result($supply_code,$stock_lot,
	$order_by,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,
	$dose_lunch,$dose_dinner,$dose_night,$total_amt,$sale_opt_id,
	$order_note,$order_status, $supply_desc, $stock_exp_date, $supply_name, 
	$dose_note,$supply_unit,$fname,$sname);

	while ($stmt->fetch()) {
		$aRes["supply_code"] = $supply_code;
		$aRes["stock_lot"] = $stock_lot;
		$aRes["order_by"] = $order_by;
		$aRes["dose_day"] = $dose_day;
		$aRes["dose_per_time"] = $dose_per_time;
		$aRes["dose_before"] = $dose_before;
		$aRes["dose_breakfast"] = $dose_breakfast;
		$aRes["dose_lunch"] = $dose_lunch;
		$aRes["dose_dinner"] = $dose_dinner;
		$aRes["dose_night"] = $dose_night;
		$aRes["total_amt"] = $total_amt;
		$aRes["sale_opt_id"] = $sale_opt_id;
		$aRes["order_note"] = urlDecode($order_note);
		$aRes["order_status"] = $order_status;
		// $aRes["sale_price"] = $sale_price;
		$aRes["supply_name"] = $supply_name;
		$aRes["dose_note"] =  urlDecode($dose_note);
		$aRes["supply_unit"] = $supply_unit;
		$aRes["supply_desc"] = $supply_desc;
		$aRes["stock_exp_date"] = $stock_exp_date;
		$aRes["fname"] = $fname;
		$aRes["sname"] = $sname;
	}
	// print_r($aRes);

}
$stmt->close();
$mysqli->close();

//Create BC Year
$aDate = explode("-",$sColDate); 
if($sLang == "th"){
	$thYear = $aDate[0]+543;
}
else{
	$thYear = $aDate[0];
}
$th_date="$aDate[2]/$aDate[1]/$thYear";

// echo count($aRes);
if(count($aRes) > 0){
	$temp_total_dose = $aRes["dose_breakfast"]+$aRes["dose_lunch"]+$aRes["dose_dinner"]+$aRes["dose_night"];


	if($temp_total_dose =='0' || $temp_total_dose == ""){
		$temp_total_dose = $aRes["supply_desc"];
	}
	else{
		$temp_total_dose = $aRes["supply_desc"]." วันละ ". $temp_total_dose ." ครั้ง";
		
		if($aRes["dose_per_time"]!="0" ) $temp_total_dose .= " ครั้งละ ".$aRes["dose_per_time"]." ".$aRes["supply_unit"];

		if($aRes["dose_breakfast"]=="0" && $aRes["dose_lunch"]=="0" && $aRes["dose_dinner"]=="0" && $aRes["dose_night"]=="1" ){

		}else{
			if($aRes["dose_before"]=="A"){ 
				$temp_total_dose .= 'หลัง อาหาร'; 
			}
			else if($aRes["dose_before"]=="B")
			{ 
				$temp_total_dose .= 'ก่อน อาหาร'; 
			}
		}

		if($aRes["dose_breakfast"]=="1") $temp_total_dose .= " เช้า";
		if($aRes["dose_lunch"]=="1") $temp_total_dose .= " กลางวัน";
		if($aRes["dose_dinner"]=="1") $temp_total_dose .= " เย็น";
		if($aRes["dose_night"]=="1") $temp_total_dose .= " ก่อนนอน";
	}

	$pdf = new PDF('L','mm',array(90,60));

	$pdf->SetThaiFont();

	//$pdf->SetMargins(25, 34);
	$pdf->SetAutoPageBreak(false,60);

	$pdf->AddPage();

	if($sLang == "th"){
		$pdf->Image('assets/image/sticker_drug.jpg', 0, 0, 90, 60);
	}
	else{
		$pdf->Image('assets/image/sticker_drug_en.jpg', 0, 0, 90, 60);
	}

	$pdf->GetPageWidth();  // Width of Current Page
	$pdf->GetPageHeight(); // Height of Current Page

	$pdf->SetFont('THSarabun', 'B', 11);
	$pdf->SetXY(48,11);
	$pdf->tCell(0, 0, "", 0, 1, 'L');
	$pdf->SetXY(49,16);
	$pdf->tCell(0, 0, "", 0, 1, 'L');

	$pdf->SetFont('THSarabun', '', 12);
	$pdf->SetXY(59,15);
	$pdf->Cell(0, 0, $th_date, 0, 1, 'L');

	$pdf->SetXY(75,15);
	$pdf->tCell(0, 0, "#".$iQueue, 0, 1, 'L');

	// ชื่อคน
	$pdf->SetXY(3,24);
	$pdf->tCell(0, 0, $aRes["fname"]." ".$aRes["sname"], 0, 1, 'L');


	$pdf->SetXY(70,10);
	$pdf->tCell(0, 0, $sUid, 0, 1, 'L');

	$pdf->SetFont('THSarabun', 'B', 10.5);
	//ขื่อยา
	$pdf->SetXY(3,29.5);
	$pdf->tCell(0, 0, $aRes["supply_name"], 0, 1, 'L');

	$pdf->SetFont('THSarabun', '', 12);
	//จำนวน
	$pdf->SetXY(73,29.5);
	$pdf->tCell(0, 0, $aRes["total_amt"]." ".$aRes["supply_unit"], 0, 1, 'L');

	//วิธีใช้
	//ทุก
	$pdf->SetFont('THSarabun', 'B', 11);
	$pdf->SetXY(3,32);
	$pdf->tMultiCell(79,5, $temp_total_dose, 0, "L", '0',false);

	$sDoseNote = (($aRes["order_note"] != "")?$aRes["order_note"]:$aRes["dose_note"]);
	$pdf->SetXY(3,42);
	$pdf->tMultiCell(79,5, $sDoseNote, 0, "L", '0',false);

	// LOT, EXP
	$pdf->SetFont('THSarabun', 'B', 9);
	$pdf->SetXY(53,56);
	$pdf->tCell(0, 0, "LOT: ".$aRes["stock_lot"]."   EXP: ".$aRes["stock_exp_date"], 0, 1, 'L');

	$pdf->Output(); 
}
?>