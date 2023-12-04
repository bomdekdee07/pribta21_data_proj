<?
session_start();
include('./inc/connect.php');
include_once("in_php_function.php");
mysqli_set_charset($connect,'utf8');
$sUid = getQueryString("uid");
$sColDate = getQueryString("visit_date");
$sColTime = urldecode(getQueryString("visit_time"));
$sQrd = getQueryString("qrd");
$sQNo = getQueryString("que");
$sVisit = getQueryString("visit");
//Check if Que is Provided but no sUid So Get UID from QUEUE
$sPName="";

if($sUid=="" && $sQNo != ""){
	$query = "SELECT patient_uid FROM k_queue_row_detail WHERE id = ? ";
	$stmt = $connect->prepare($query);
	$stmt->bind_param("s",$sQrd);
	$iNewNo = 0;
	if($stmt->execute()){
	  $stmt->bind_result($uid);
	  while ($stmt->fetch()) {
	  	$sUid = $uid;
	  }
	}
}

if($sUid==""){
	echo("No UID provided. ไม่พบ UID");
	exit();
}

$query = "SELECT fname,sname,en_fname,en_sname FROM patient_info
WHERE uid = ? ";
$stmt = $connect->prepare($query);
$stmt->bind_param("s",$sUid);

if($stmt->execute()){
  $stmt->bind_result($fname,$sname,$en_fname,$en_sname );
  while ($stmt->fetch()) {
  	if($fname=="") $sPName=$en_fname." ".$en_sname;
  	else $sPName=$fname." ".$sname;
  	if($sPName=="") $sPName = "(ไม่มีระบุ)";
  }
}


//WAITING TO FINISH
$year=date("Y");
$dm=date("d/m");
$year_thai=$year+543;
$full_date_thai_print=$dm.'/'.$year_thai;

$aD = explode("-",$sColDate); 
$full_date_thai=$aD[2]."/".$aD[1]."/".(($aD[0]*1+543));

//Get Que #
$query = "SELECT queue FROM k_visit_data WHERE uid=? AND date_of_visit = ? AND time_of_visit=?";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

$iQueue ="";
if($stmt->execute()){
  $stmt->bind_result($queue );
  while ($stmt->fetch()) {
  	$iQueue = $queue;

  }
}


//Check if bill is issued or not 
$sBillNo = "";
$query = "SELECT t_number FROM k_cashier_receipt WHERE uid=? AND collect_date = ? AND collect_time=?";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

$iNewNo = 0;
if($stmt->execute()){
  $stmt->bind_result($t_number );
  while ($stmt->fetch()) {
  	$sBillNo = $t_number;
  }
}



$iTotal = 0; $iDF = 0; $iLab = 0; $iDrug = 0; 

//Create Bill Number
if($sBillNo==""){
	//Since Bill is not found this is not paid patient.
	
	/*
	$query = "select id,t_number from k_cashier_master where t_year = ?;";
	$stmt = $connect->prepare($query);
	$stmt->bind_param("s",$year_thai);

	$iNewNo = 0;
	if($stmt->execute()){
	  $stmt->bind_result($id,$t_number );
	  while ($stmt->fetch()) {
	  	$iNewNo = $t_number + 1;
	  }
	}
	if($iNewNo==0){
		$iNewNo = 1;
		$query = "INSERT INTO k_cashier_master(t_number,t_year) VALUES(?,?);";
	}else{
		$query = "UPDATE k_cashier_master SET t_number = ? WHERE t_year = ?;";
	}


	$stmt = $connect->prepare($query);
	$stmt->bind_param("ss",$iNewNo,$year_thai);
	if($stmt->execute()){}

	$sBillNo = $year_thai."/".(str_pad($iNewNo,4,"0",STR_PAD_LEFT));

	$query = "INSERT INTO k_cashier_receipt(uid,collect_date,collect_time,t_number,time_record) VALUES(?,?,?,?,NOW());";
	$stmt = $connect->prepare($query);
	$stmt->bind_param("ssss",$sUid,$sColDate,$sColTime,$sBillNo);
	if($stmt->execute()){
	}
	*/
}
	

//Doctor Fee
$query =" SELECT p2_doctor_fee from k_physician
WHERE uid = ? AND visit_date = ? AND visit_time = ? order by time_record DESC LIMIT 1";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

if($stmt->execute()){
  $stmt->bind_result($p2_doctor_fee );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iTotal += ($p2_doctor_fee*1);
  	$iDF=($p2_doctor_fee*1);
  }
}


//LAB Cost
$query = "SELECT lab_price
FROM p_lab_order_lab_test PLOLT
LEFT JOIN p_lab_test_sale_price PLTSP
ON PLTSP.lab_id = PLOLT.lab_id
AND PLTSP.sale_opt_id = PLOLT.sale_opt_id
WHERE uid=? AND collect_date = ? AND collect_time = ? ";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

if($stmt->execute()){
  $stmt->bind_result($lab_price );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iTotal += ($lab_price*1);
  	$iLab += ($lab_price*1); 
  }
}

/*
//ADD to k_cahier_receipt for ???
$query = "INSERT INTO  k_cashier_receipt(uid,collect_date,collect_time,lab_id,lab_group_id,laboratory_id,sale_opt_id,lab_result,lab_result_note,lab_id_2,sale_opt_id_2,lab_price_2,visit,qrd,t_number,time_record) 

SELECT uid,collect_date,collect_time,PLOLT.lab_id,PLOLT.sale_opt_id,lab_result,lab_result_note,PLOLT.lab_id,PLOLT.sale_opt_id,lab_price,?,?,?,NOW()
	FROM p_lab_order_lab_test PLOLT
	LEFT JOIN p_lab_test_sale_price PLTSP
	ON PLTSP.lab_id = PLOLT.lab_id
	AND PLTSP.sale_opt_id = PLOLT.sale_opt_id
	WHERE PLOLT.uid=? AND PLOLT.collect_date = ? AND PLOLT.collect_time = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("ssssss",$sVisit,$sQrd,$sBillNo,$sUid,$sColDate,$sColTime);

if($stmt->execute()){}
*/

//Drug Cost
$query = "SELECT JSO.supply_code,supply_name,supply_unit,total_amt,sale_price,JSG.supply_group_type,JSO.order_note,JSG.supply_group_code,supply_group_name FROM j_stock_order JSO
LEFT JOIN j_stock_master JSM
ON JSM.supply_code = JSO.supply_code

LEFT JOIN j_stock_group JSG
ON JSG.supply_group_code = JSM.supply_group_code

LEFT JOIN j_stock_sale_opt JSSO
ON JSSO.supply_code = JSO.supply_code
AND JSSO.sale_opt_id = JSO.sale_opt_id

WHERE JSO.uid = ? AND JSO.collect_date = ? AND JSO.collect_time = ? AND JSO.order_status != '00'
ORDER BY JSG.supply_group_type,JSG.supply_group_code,JSM.supply_code";
$stmt = $connect->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);


$aService = array();

if($stmt->execute()){
  $stmt->bind_result($supply_code,$supply_name,$supply_unit,$total_amt,$sale_price,$supply_group_type,$order_note,$supply_group_code,$supply_group_name );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iTotal += $total_amt * $sale_price;
  	if($supply_group_type==1) $iDrug += $total_amt * $sale_price;

  	$iCount = count($aService);
  	$aService[$iCount]["code"] = $supply_code;
  	$aService[$iCount]["type"] = $supply_group_type;
  	$aService[$iCount]["group"] = $supply_group_code;
  	$aService[$iCount]["groupname"] = $supply_group_name;
  	$aService[$iCount]["name"] = urldecode($supply_name);
  	$aService[$iCount]["unit"] = $supply_unit;
  	$aService[$iCount]["note"] = urldecode($order_note);
  	$aService[$iCount]["price"] = $total_amt * $sale_price;
  }
}
$sName = "";
if(isset($_SESSION['pribta_clinic_s_id'])){
	$sID = $_SESSION['pribta_clinic_s_id'];
	$query = "SELECT s_name FROM p_staff WHERE s_id=?";
	$stmt = $connect->prepare($query);
	$stmt->bind_param("s",$sID);

	if($stmt->execute()){
	  $stmt->bind_result($s_name);

	  $sTableBody = "";
	  while ($stmt->fetch()) {
	  	$sName = $s_name;	
	  }
	}
}
$connect->close();


include_once("in_pdf_class.php");


$pdf = new PDF();
$pdf->SetThaiFont();
$pdf->AddPage('L',"A4",'mm');


$pdf->SetFont('THSarabun', '', 12);

$iMult = 145;
$pdf->Image('./images/pribta_receipt_form.png', 0, 0, 290, 210);

//$sName has been pull from database now.
$pdf->SetXY(89,170);
$pdf->tCell(37, 4, $sName, 0, 1, 'C');

$pdf->SetXY(89+$iMult,170);
$pdf->tCell(37, 4, $sName, 0, 1, 'C');
//$pdf->tText(91,174,$sName);	
//$pdf->tText(91+$iMult,174,$sName);

/* WHAT IS THIS CODE ?????? WHY WOULD YOU FIXED LOGIN ID HERE 
if($_SESSION['pribta_clinic_s_id']=='P20042')
{
//$pdf->Image('./images/aum.png', 100, 160, 10, 10);
$pdf->tText(100,174,'อธิพันธ์ พวงภู่');	
//$pdf->Image('./images/aum.png', 238, 160, 10, 10);
$pdf->tText(238,174,'อธิพันธ์ พวงภู่');		
}

*/
// Header 


$pdf->Text(120,14,$sBillNo);
$pdf->Text(120+$iMult,14,$sBillNo);


$pdf->Text(125,19,"#".$iQueue);
$pdf->Text(125+$iMult,19,"#".$iQueue);

if($sUid=="P99-99999"){
	$sUid="ลูกค้า";
}else if($sUid=="P00-00000"){
	$sUid="ใช้ในคลีนิก";
}else{
	//$sUid.= " ".$sPName;
	$sPName = $sUid." ".$sPName;
}

$pdf->tText(77,39,$sPName);
$pdf->tText(77+$iMult,39,$sPName);


$pdf->Text(20,40,$full_date_thai." ".$sColTime);
$pdf->Text(20+$iMult,40,$full_date_thai." ".$sColTime);

$pdf->Text(100,207,"print on ".$full_date_thai_print." ".$sColTime);
$pdf->Text(100+$iMult,207,"print on ".$full_date_thai_print." ".$sColTime);
//$pdf->Text(36,40,$sColTime);
//$pdf->Text(36+$iMult,40,$sColTime);

$pdf->Text(122,155,$iTotal);
$pdf->Text(122+$iMult,155,$iTotal);


$sThaiString = j_numtothaistring($iTotal);

//Thai String
$pdf->SetXY(33,151);
$pdf->tCell(65, 4, $sThaiString, 0, 1, 'C');
$pdf->SetXY(33+$iMult,151);
$pdf->tCell(65, 4, $sThaiString, 0, 1, 'C');

//$pdf->tText(40,155,$sThaiString);
//$pdf->tText(40+$iMult,155,$sThaiString);


//End Header

/*
	$aService[$supply_code]["type"] = $supply_name;
  	$aService[$supply_code]["name"] = $supply_name;
  	$aService[$supply_code]["unit"] = $supply_unit;
  	$aService[$supply_code]["price"] = $total_amt * $sale_price;
  	$iTotal = 0; $iDF = 0; $iLab = 0; $iDrug = 0;
*/
//X = Fix no change , Y = +8 
$iNameX = 18; $iRowY = 66;
$iPriceX = 122; 

if($iDF!=0){
	$pdf->tText($iNameX,$iRowY,'ค่าบริการทางการแพทย์');
	$pdf->Text($iPriceX,$iRowY,$iDF);
	$pdf->tText($iNameX+$iMult,$iRowY,'ค่าบริการทางการแพทย์');
	$pdf->Text($iPriceX+$iMult,$iRowY,$iDF);
	$iRowY += 8;
}
if($iLab!=0){
	$pdf->tText($iNameX,$iRowY,'ค่าตรวจทางห้องปฏิบัติการ');
	$pdf->Text($iPriceX,$iRowY,$iLab);
	$pdf->tText($iNameX+$iMult,$iRowY,'ค่าตรวจทางห้องปฏิบัติการ');
	$pdf->Text($iPriceX+$iMult,$iRowY,$iLab);

	$iRowY += 8;
}
if($iDrug!=0){
	$pdf->tText($iNameX,$iRowY,'ค่ายา');
	$pdf->Text($iPriceX,$iRowY,$iDrug);
	$pdf->tText($iNameX+$iMult,$iRowY,'ค่ายา');
	$pdf->Text($iPriceX+$iMult,$iRowY,$iDrug);
	$iRowY += 8;
}


/*
foreach ($aService as $supply_code => $aInfo) {
	if($aInfo["type"]!=1){
		//$sText = $aInfo["name"].$aInfo["note"];
		$sText = $aInfo["name"];
		$pdf->tText($iNameX,$iRowY,$sText);
		$pdf->Text($iPriceX,$iRowY,$aInfo["price"]);
		$pdf->tText($iNameX+$iMult,$iRowY,$sText);
		$pdf->Text($iPriceX+$iMult,$iRowY,$aInfo["price"]);
		$iRowY += 8;
	}
}
*/



$curCode = ""; $iTotal = 0; $sName = "	";
foreach ($aService as $iRow => $aInfo) {
	if($aInfo["group"]=='S00005' || $aInfo["group"]=='S00006'){
		//$sText = $aInfo["name"].$aInfo["note"];
		if($curCode == $aInfo["group"]){
			//Same Code DO SUM
			$iTotal = $iTotal + ($aInfo["price"]*1);
		}else{
			if($curCode!=""){
				// NEW Code End Previous
				if($iTotal!=0){
					$pdf->tText($iNameX,$iRowY,$sName); 
					$pdf->Text($iPriceX,$iRowY,$iTotal);
					$pdf->tText($iNameX+$iMult,$iRowY,$sName);
					$pdf->Text($iPriceX+$iMult,$iRowY,$iTotal);
					$iRowY += 8;
				}
			}
			$curCode = $aInfo["group"];
			$sName = $aInfo["groupname"];
			$iTotal = ($aInfo["price"]*1);
		}

	}
}
if($iTotal!=0){
	$pdf->tText($iNameX,$iRowY,$sName); 
	$pdf->Text($iPriceX,$iRowY,$iTotal);
	$pdf->tText($iNameX+$iMult,$iRowY,$sName);
	$pdf->Text($iPriceX+$iMult,$iRowY,$iTotal);
	$iRowY += 8;
}


foreach ($aService as $iKey => $aInfo) {
	if($aInfo["type"]!=1 && $aInfo["group"]!='S00005' && $aInfo["group"]!='S00006'){
		//$sText = $aInfo["name"].$aInfo["note"];
		if($aInfo["price"]!="0"){
			$sText = $aInfo["name"];
			$pdf->tText($iNameX,$iRowY,$sText);
			$pdf->Text($iPriceX,$iRowY,$aInfo["price"]);
			$pdf->tText($iNameX+$iMult,$iRowY,$sText);
			$pdf->Text($iPriceX+$iMult,$iRowY,$aInfo["price"]);
			$iRowY += 8;
		}

	}
}


$filename="receipt/".$sUid."_".$sColDate.".pdf";
$pdf->Output($filename,'F');
$pdf->Output();
?>