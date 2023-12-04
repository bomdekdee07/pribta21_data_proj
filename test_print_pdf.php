<?
include_once("in_php_function.php");

$sUid = getQS("uid");
$sColDate = getQS("coldate");
$sColTime = urldecode(getQS("coltime"));


include_once("class_pdf.php");

$pdf = new PDF();
$pdf->SetThaiFont();
$pdf->AddPage('P',"A4",'mm');

//กำหนด Font Size
$pdf->SetFont('THSarabun', '', 12);

//ใส่รูปเข้าไป ถ้าจะให้รูปเป็น background ให้ใส่ไว้แรกสุด
//$pdf->Image('assets/image/lab_report.jpg', 0, 0, 210, 290);

//Create Header -> It will automatic repeat wen using writeRow
$pdf->SetHeaderImage('assets/image/lab_report.jpg', 0, 0, 210, 290);
$pdf->SetHeaderTxt(37,10,"นามสกุล",'THSarabun', '', 12,array(0,0,0));
$pdf->SetPageNo(180,280,$sTxt="Page {p}/{tp}",'THSarabun', '', 11);

//แบบ สร้าง Block ขึ้นมา แล้วเอา Text ไปใส่ เหมาะกับการทำแบบตาราง เพราะกำหนดความกว้างสูงได้
//ใส่ตำแหน่งของ Cell ด้วย SetXY ก่อน
$pdf->SetXY(10,0);
//เมื่อกำหนด Block แล้วให้ใช้ tCell เพื่อใส่ข้อความ (ภาษาไทยได้ ภาษาอังกฤษใช้ Cell เฉยๆก็ได้)
//tCell(37, 4, "ชื่อ", 0, 1, 'C');
//tCell($iX, $iY, $sStr, $iBorder, $iLine, $sOrient,$fill=false);
//โดยที่ iX = ความกว้างของ Cell
//และ iY = ความสูงของ Cell
//ต.ย. นี้ผมจะใส่ Border ไว้ จะได้เห็นชัดๆ เวลาใส่ค่า iX,iY

//Set Start New Page
$pdf->SetTopMargin(70);
//Set Footer
$pdf->SetAutoPageBreak(true,100);

//สร้าง Column สำหรับ Table
$pdf->SetTableColWidth(array(40,40,40));
$pdf->SetTableColOrient(array("L","L","L"));
$pdf->SetTableLineHeight(5);
$pdf->SetTableLineMargin(5);
$pdf->SetLeftMargin(50);
$pdf->SetTableColColor(array(array(1,100,160),array(1,120,150),array(1,120,110) ));
//$pdf->tMultiCell(37, 5, ("//เมื่อกำหนด Block แล้วให้ใช้ tCell เพื่อใส่ข้อ\nความ \n(ภาษาไทยได้ ภาษาอังกฤษใช้ Cell เฉยๆก็ได้)\n
//tCell(37, 4, ชื่อ, 0, 1, 'C');
//tCell(iX, iY, sStr, iBorder, iLine, sOrient,fill=false);
//โดยที่ iX = ความกว้างของ Cell\r\n
//และ iY = ความสูงของ Cell
//ต.ย. นี้ผมจะใส่ Border ไว้ จะได้เห็นชัดๆ เวลาใส่ค่า iX,iY"), 1, 'J');

$pdf->writeRow(array("TESTING X","TESTTING COL 2","TESTTING COL 3"));
$pdf->writeRow(array("TESTING X","TESTTING COL 2","TESTTING COL 3\nTESTTING COL 3"));
$pdf->writeRow(array("TESTING X","TESTTING COL 2","TESTTING COL 3"));

//$pdf->tMultiCell(37, 5, ("//เมื่อกำหนด Block แล้วให้ใช้ tCell เพื่อใส่ข้อ\nความ \n(ภาษาไทยได้ ภาษาอังกฤษใช้ Cell เฉยๆก็ได้)\n
//tCell(37, 4, ชื่อ, 0, 1, 'C');
//tCell(iX, iY, sStr, iBorder, iLine, sOrient,fill=false);
//โดยที่ iX = ความกว้างของ Cell\r\n
//และ iY = ความสูงของ Cell
//ต.ย. นี้ผมจะใส่ Border ไว้ จะได้เห็นชัดๆ เวลาใส่ค่า iX,iY"), 1, 'J');


$pdf->writeRow(array("TESTTING COL 2","TESTTING COL 2","TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL\n 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING \nCOL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING\n COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING \nCOL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING \nCOL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL\n 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2TESTTING COL 2")  );


//แบบข้อความธรรมดา ถ้าใช้ Text ธรรมดาจะใช้ไทยไม่ได้
//Text(iX,iY,$sString);
//แบบข้อความธรรมดา ถ้าใช้ Text ธรรมดาจะใช้ไทยไม่ได้

$pdf->tText(50,10,$pdf->getCurPage());

//tCell จะสามารถ ตกมาอีกบันทัดได้หากเกินความกว้างที่กำหนด ส่วน tText จะเป็น Text 1 บันทัดเท่านั้น
$sToday=date("ymdhis");
$filename="pdfoutput/tempFile_".$sToday.".pdf";
//$pdf->Output($filename,'F');
$pdf->Output($filename,'F');
$pdf->Output();

?>