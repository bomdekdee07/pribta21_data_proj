<?

include_once("in_php_function.php");
include('in_db_conn.php');

$sTime = urlDecode(getQS("coltime"));
$sUid = getQS("uid");
$sCode = getQS("supcode");
$sColDate = urlDecode(getQS("coldate"));
$sLang = getQS("lang","th");

//Get Que #
$query = "SELECT queue FROM i_queue_list WHERE uid=? AND collect_date = ? AND collect_time=?";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sTime);

$iQueue ="";
if($stmt->execute()){
  $stmt->bind_result($queue );
  while ($stmt->fetch()) {
  	$iQueue = $queue;
  }
}



$query = "SELECT
JSO.supply_code,JSO.stock_lot,
JSO.order_by,JSO.dose_day,JSO.dose_per_time,JSO.dose_before,JSO.dose_breakfast,
JSO.dose_lunch,JSO.dose_dinner,JSO.dose_night,JSO.total_amt,JSO.sale_opt_id,
JSO.order_note,JSO.order_status,JSSO.sale_price,JSM.supply_name,
JSM.dose_note,JSM.supply_unit,JSO.supply_desc,PI.fname,PI.sname
FROM
j_stock_order JSO
LEFT JOIN j_stock_sale_opt JSSO ON JSSO.supply_code = JSO.supply_code AND JSSO.sale_opt_id = JSO.sale_opt_id
LEFT JOIN j_stock_master JSM ON JSM.supply_code = JSO.supply_code
LEFT JOIN patient_info PI ON PI.uid = JSO.uid
where collect_date = ? and collect_time = ? and JSO.uid = ? and JSO.supply_code = ? and order_status <> '00' ";

$aRes = array();
$stmt = $connect->prepare($query);
$stmt->bind_param("ssss",$sColDate,$sTime,$sUid,$sCode);
if($stmt->execute()){
  $stmt->bind_result($supply_code,$stock_lot,
  	$order_by,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,
  	$dose_lunch,$dose_dinner,$dose_night,$total_amt,$sale_opt_id,
  	$order_note,$order_status,$sale_price,$supply_name,
  	$dose_note,$supply_unit,$supply_desc,$fname,$sname);

  
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
	$aRes["sale_price"] = $sale_price;
	$aRes["supply_name"] = $supply_name;
	$aRes["dose_note"] =  urlDecode($dose_note);
	$aRes["supply_unit"] = $supply_unit;
	$aRes["supply_desc"] = $supply_desc;
	$aRes["fname"] = $fname;
	$aRes["sname"] = $sname;
  }

}
$mysqli->close();

//Create BC Year
$aDate = explode("-",$sColDate); 
$thYear = $aDate[0]+543;
$th_date="$aDate[2]/$aDate[1]/$thYear";


$temp_total_dose = $aRes["dose_breakfast"]+$aRes["dose_lunch"]+$aRes["dose_dinner"]+$aRes["dose_night"];


if($temp_total_dose=='0' || $temp_total_dose==""){

	$temp_total_dose = $aRes["supply_desc"];
}else{

	$temp_total_dose = $aRes["supply_desc"]." วันละ ". $temp_total_dose ." ครั้ง";
	
	if($aRes["dose_per_time"]!="0" ) $temp_total_dose .= " ครั้งล่ะ ".$aRes["dose_per_time"]." ".$aRes["supply_unit"];

	if($aRes["dose_breakfast"]=="0" && $aRes["dose_lunch"]=="0" && $aRes["dose_dinner"]=="0" && $aRes["dose_night"]=="1" ){

	}else{
		if($aRes["dose_before"]=="A"){ $temp_total_dose .= 'หลัง อาหาร'; }
		else if($aRes["dose_before"]=="B"){ $temp_total_dose .= 'ก่อน อาหาร'; }
	}


	if($aRes["dose_breakfast"]=="1") $temp_total_dose .= " เช้า";
	if($aRes["dose_lunch"]=="1") $temp_total_dose .= " กลางวัน";
	if($aRes["dose_dinner"]=="1") $temp_total_dose .= " เย็น";
	if($aRes["dose_night"]=="1") $temp_total_dose .= " ก่อนนอน";


}


include_once("in_pdf_class.php");

$pdf = new PDF('L','mm',array(90,60));

$pdf->SetThaiFont();

//$pdf->SetMargins(25, 34);
$pdf->SetAutoPageBreak(false,60);

$pdf->AddPage();
$pdf->Image('./images/sticker_drug.jpg', 0, 0, 90, 60);

//$pdf->GetPageWidth();  // Width of Current Page
//$pdf->GetPageHeight(); // Height of Current Page

$pdf->SetFont('THSarabun', '', 8);
$pdf->SetXY(2.5,18);
$pdf->tCell(0, 0, "เลขที่ 319 อาคารจัตุรัสจามจุรี ชั้น 11 ยูนิต 1109-1116", 0, 1, 'L');
$pdf->SetXY(2.5,21);
$pdf->tCell(0, 0, "ถ.พญาไท แขวงปทุมวัน เขตปทุมวัน กรุงเทพฯ 10330", 0, 1, 'L');


$pdf->SetFont('THSarabun', '', 11);
$pdf->SetXY(48,11);
$pdf->tCell(0, 0, "หมายเลขผู้รับบริการ", 0, 1, 'L');
$pdf->SetXY(49,16);
$pdf->tCell(0, 0, "วันที่จ่าย", 0, 1, 'L');
$pdf->SetXY(2,25);
$pdf->tCell(0, 0, "ชื่อ-นามสกุลผู้รับบริการ", 0, 1, 'L');
$pdf->SetXY(2,46);
$pdf->tCell(0, 0, "คำแนะนำการใช้ยา", 0, 1, 'L');
$pdf->SetXY(62,15);
$pdf->Cell(0, 0, $th_date, 0, 1, 'L');


$pdf->SetFont('THSarabun', '', 12);

$pdf->SetXY(31,24);
$pdf->tCell(0, 0, $aRes["fname"]." ".$aRes["sname"], 0, 1, 'L');


$pdf->SetXY(76,6);
$pdf->tCell(0, 0, "#".$iQueue, 0, 1, 'L');


$pdf->SetXY(70,10);
$pdf->tCell(0, 0, $sUid, 0, 1, 'L');

$pdf->SetXY(2,30);
$pdf->tCell(0, 0, "ชื่อยา", 0, 1, 'L');

//ขื่อยา
$pdf->SetXY(10,29.5);
$pdf->tCell(0, 0, $aRes["supply_name"], 0, 1, 'L');
//จำนวน

$pdf->SetXY(62,29.5);
$pdf->tCell(0, 0, "จำนวน ".$aRes["total_amt"]." ".$aRes["supply_unit"], 0, 1, 'L');
//วิธีใช้

//ทุก
$pdf->SetXY(2,35);
$pdf->tCell(0, 0, "วิธีใช้", 0, 1, 'L');

$pdf->SetXY(3,40);

//$pdf->tCell(0, 0, " $temp_total_dose  $temp_dose_before   $temp_dose_breakfast_m  $temp_dose_lunch_m  $temp_dose_dinner_m  $temp_dose_night_m ", 0, 1, 'L');


$pdf->SetFont('THSarabun', 'B', 13);
$pdf->SetXY(8,35);
//$pdf->tCell(0, 0, $aRes["supply_desc"], 0, 1, 'L');


//$temp_total_dose = (($temp_total_dose=="")?$aRes["supply_desc"]:$temp_total_dose);
$pdf->SetXY(2,32);
$pdf->tMultiCell(82,5, "       ".$temp_total_dose, 0, "L", '0',false);


$sDoseNote = (($aRes["dose_note"]=="")?$aRes["dose_note"]:$aRes["order_note"]);
$pdf->SetXY(2,43);
$pdf->tMultiCell(82,5, "                    ".($sDoseNote), 0, "L", '0',false);


$pdf->Output(); 
?>