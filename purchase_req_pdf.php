<?
include_once("in_php_function.php");

$sReqId = (getQS("reqid"));
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sDocId=getQS("doctype","PURCHASE_REQ");
$sSid = getSS("s_id");
$sDocNote=getQS("docnote");
$sNow=date("Y-m-d H:i:s");
$sFileDate = preg_replace('/[- :]/', '', $sNow);
$sNoFile=getQS("nofile");
if($sReqId=="{NEW}" || $sReqId=="") exit;

include_once("in_setting_row.php");
include("in_db_conn.php");


$query="SELECT request_id,request_title,request_detail,request_datetime,require_date,request_status,delivery_to,delivery_other,request_type,request_proj,finance_req_no,finance_rec_date,request_po_no,s_name,request_by FROM i_stock_request_list ISRL LEFT JOIN p_staff PS ON PS.s_id = ISRL.request_by 
WHERE ISRL.request_id = ?";
$sHtml=""; $aReq=array();
$stmt=$mysqli->prepare($query);
$stmt->bind_param("s",$sReqId);
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$aReq = $row;
	}
}

$aSub=array();
$query="SELECT request_id,ISRSI.supply_code,request_item_no,request_supply_note,request_amt,request_exact_amt,request_vat,request_item_price,request_total_price,request_total_price_final,request_unit,request_project,request_account 
FROM i_stock_request_show_item ISRSI
LEFT JOIN i_stock_master ISM
ON ISM.supply_code = ISRSI.supply_code
WHERE ISRSI.request_id = ? AND request_item_show =1 ORDER BY request_item_no";
$stmt=$mysqli->prepare($query);
$stmt->bind_param("s",$sReqId);
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$aSub[$row["request_item_no"]] = $row;
	}
}

$aSig=array();
//Get Signature List
$query="SELECT IDD.sig_code,sig_value,s_name FROM i_doc_data IDD
LEFT JOIN p_staff PS
ON PS.s_id=IDD.sig_value
WHERE IDD.option_code=? AND IDD.doc_code='PURCHASE_REQ'";
$stmt=$mysqli->prepare($query);
$stmt->bind_param("s",$sReqId);
if($stmt->execute()){
	$stmt->bind_result($sig_code,$sig_value,$s_name);
	while ($stmt->fetch()) {
		$aSig[$sig_code]["s_name"]=$s_name;
		$aSig[$sig_code]["s_id"]=$sig_value;
	}	
}


if($sNoFile!="1"){
	$query="INSERT INTO i_doc_list(doc_code,doc_title,doc_datetime,doc_note,uid,collect_date,collect_time,s_id,doc_status) SELECT 'PURCHASE_REQ','ใบขอจัดซื้อ',?,?,request_id,request_datetime,request_datetime,?,1 FROM i_stock_request_list WHERE request_id=?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ssss",$sNow,$sDocNote,$sSid,$sReqId);
	if($stmt->execute()){
	}
}
	

$mysqli->close();


if(count($aSub)<1){
	echo("ไม่พบข้อมูลรายการภายใต้ ID : $sReqId<br/>No Item Order Found In Request ID : $sReqId");
	exit();
}

include_once("class_pdf.php");

$pdf = new PDF();
$pdf->SetThaiFont();
$sToday = date("d M Y h:i:s");


//request_item_no,ISRI.supply_code,request_supply_note,request_amt,request_item_price,request_total_price,request_item_status,supply_name,supply_unit
$iCount=0; $iPage=0; $iTotalPage = ceil(count($aSub)/8);
$iTotal = 0; $iVat = 0; $iDiscount=0; $iTotalPrice=0; $iTotalFinal=0;
$iItemNo=0;
foreach ($aSub as $itemno => $aItem) {
	if($iCount%8==0){
		$iPage++;
		$pdf->AddPage('P',"A4",'mm');
		$pdf->Image('assets/image/supply_pr.jpg', 0, 0, 210, 290);
		$pdf->SetXY(0,0);
		$pdf->SetFont('THSarabun', '', 11);
		$pdf->tText(185,10,$aReq["request_id"]);
		$pdf->tText(43,54,$aReq["s_name"]);
		$pdf->tText(98,54,substr($aReq["request_datetime"],0,10));
		$pdf->tText(43,58,$aReq["require_date"]);
		$pdf->tText(170,54,$aReq["finance_req_no"]);
		if($aReq["finance_rec_date"]!="0000-00-00 00:00:00") $pdf->tText(170,58,$aReq["finance_rec_date"]);
		$pdf->tText(170,62,$aReq["request_po_no"]);
		$sDel = $aReq["delivery_to"];
		$pdf->SetFont('THSarabun', '', 12);
		if($sDel=="IHRI"){
			$pdf->tText(45,61.5,"X");
		}else if($sDel=="HIVNAT"){
			$pdf->tText(84,61.5,"X");
		}else if($sDel=="OPR"){
			$pdf->tText(116.5,61.5,"X");
		}else if($sDel=="OTHER"){
			$pdf->tText(45,65.5,"X");
			$pdf->SetFont('THSarabun', '', 11);
			$pdf->tText(80,65.5,$aReq["delivery_other"]);
		}
		$sDel = $aReq["request_type"];
		$pdf->SetFont('THSarabun', '', 12);
		if($sDel=="P"){
			$pdf->tText(37.5,71,"X");
		}else if($sDel=="S"){
			$pdf->tText(94,71,"X");
		}else if($sDel=="C"){
			$pdf->tText(145,71,"X");
		}else if($sDel=="O"){
			$pdf->tText(145,79.5,"X");
		}
		$pdf->tText(9,84,$aReq["request_proj"]);
		$pdf->tText(9,94.5,$aReq["request_title"]);

		//Preapared By

		

		$sReqSig="staff_signature/".$aReq["request_by"].".gif";
		$sName=$aReq["s_name"];
		if(file_exists($sReqSig)) {
			$pdf->Image($sReqSig, 6, 227, 62, 20);
			$sName="[Pribta e-sig:21-1]".$aReq["s_name"];
		}
		$pdf->SetXY(5,243);
		$pdf->SetFont('THSarabun', '', 9);
		$pdf->tCell(63, 5, $sName, 0, 1, 'C');

		//Signature Supervisor
		if(isset($aSig["SUPERVISOR"])){
			$sReqSig="staff_signature/".$aSig["SUPERVISOR"]["s_id"].".png";
			if(file_exists($sReqSig)) $pdf->Image($sReqSig, 68, 231, 31, 10);
			$sName=$aSig["SUPERVISOR"]["s_name"];
			$pdf->SetXY(68,247);
			$pdf->tCell(31, 4.5, $sName, 0, 1, 'C');
		}


		//Signature Finance
		if(isset($aSig["FINANCE"])){
			$sReqSig="staff_signature/".$aSig["FINANCE"]["s_id"].".png";
			if(file_exists($sReqSig)) $pdf->Image($sReqSig, 99, 231, 31, 10);
			$sName=$aSig["FINANCE"]["s_name"];
			$pdf->SetXY(99,247);
			$pdf->tCell(31, 4.5, $sName, 0, 1, 'C');
		}
		//Signature Approve
		if(isset($aSig["APPROVE"])){
			$sReqSig="staff_signature/".$aSig["APPROVE"]["s_id"].".png";
			$pdf->Image($sReqSig, 136, 229, 64, 15);
			$sName=$aSig["APPROVE"]["s_name"];
			$pdf->SetXY(131,247);
			$pdf->SetFont('THSarabun', '', 9);
			$pdf->tCell(73.5, 4.5, $sName, 0, 1, 'C');
		}

		
		$pdf->SetFont('THSarabun', '', 12);
		$pdf->tText(160,280,"printed on ".$sToday);
		$pdf->tText(190,285,"Page ".$iPage."/".$iTotalPage);
		$iRow = 112; $iX=115.5;
		
	}
	$iItemNo++;
	$pdf->SetXY(5,$iRow);
	$pdf->tCell(5.9, 5.3, $iItemNo, 0, 1, 'C');
	$pdf->tText(12,$iX,$aItem["request_supply_note"]);

	$pdf->SetXY(81,$iRow);
	$pdf->tCell(17.5, 5.3, $aItem["request_amt"], 0, 1, 'C');

	$pdf->SetXY(98.5,$iRow);
	$pdf->tCell(15.5, 5.3, $aItem["request_unit"], 0, 1, 'C');

	$pdf->SetXY(114,$iRow);
	$pdf->tCell(17, 5.3, $aItem["request_item_price"], 0, 1, 'C');
	
	$sTemp = number_format($aItem["request_total_price"],2,".",",");
	$pdf->SetXY(131,$iRow);
	$pdf->tCell(18.5, 5.3, $sTemp, 0, 1, 'R');

	$pdf->SetXY(149.5,$iRow);
	$pdf->tCell(24, 5.3, $aItem["request_project"], 0, 1, 'C');	
	$pdf->SetXY(173.5,$iRow);
	$pdf->tCell(31, 5.3, $aItem["request_account"], 0, 1, 'C');	



	$iTotal += ($aItem["request_total_price"]*1);
	$iTemp = number_format($aItem["request_total_price_final"]*1,2,".",",");
	$iTotalFinal+=($aItem["request_total_price_final"]);
	$iRow+=5.3; $iX+=5.3; $iCount++;
	

}


if($iTotalFinal > 0){
	$iVat = ($iTotal*(7/100));
	$iTotalVat= $iTotal+$iVat;
	$iDiscount= $iTotalFinal-($iTotalVat);



	$iVat = number_format($iVat,2,".",",");
	$iTotal = number_format($iTotal,2,".",",");
	$iTotalVat = number_format($iTotalVat,2,".",",");
	$iTotalFinal = number_format($iTotalFinal,2,".",",");
	$iDiscount = number_format($iDiscount,2,".",",");

	if($iDiscount>0){
		
		$pdf->SetXY(95,153.5);
		$pdf->tCell(18.5, 5.3, $iDiscount, 2, 1, 'R');	
	}

	$pdf->SetXY(131,153.5);
	$pdf->tCell(18.5, 5.3, $iVat, 0, 1, 'R');
	$pdf->SetXY(95,159);
	$pdf->tCell(18.5, 5.3, $iTotal, 0, 1, 'R');	
	$pdf->SetXY(131,159);
	$pdf->tCell(18.5, 5.3, $iTotalFinal, 0, 1, 'R');	

}






unset($aReq);
unset($aSub);

/*
//E Signature System In the future.
$sSig = "staff_signature/DTEST01.gif";
if(file_exists($sSig)){
	$pdf->Image($sSig, 7, 230, 50, 10);
	$pdf->tText(7,240,"DTEST01-2108-1");
}
*/

$sReqId=str_replace("/", "-", $sReqId);


if($sNoFile=="1"){

}else{
	$filename="pdfoutput/".$sDocId."_".$sReqId."_".$sFileDate.".pdf";
	$pdf->Output($filename,'F');	
}

$pdf->Output();
?>