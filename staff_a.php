<?
include_once("in_session.php");
include_once("in_php_function.php");

$sMode=getQS("u_mode");
$sSid=getSS("s_id");
$gType_leg = getQS("type_leg") == "TH"? "_TH":"_EN";
$aRes=array(); 
$aRes["res"] = 0;
$aRes["msg"] = "";


if($sSid==""){
	$aRes["msg"] = "E00 Error : Please login";
}else if($sMode=="update_signature"){
	$objSig = getQS("imgsig");
	$sFile = "staff_signature/".$sSid.$gType_leg.".png";
	/*
	$oPng = imagecreatefrompng($objSig);
	imagegif($oPng, $sFile);
	imagedestroy($oPng);
	*/

	list($type, $objSig) = explode(';', $objSig);
	list(, $objSig)      = explode(',', $objSig);
	$objSig = base64_decode($objSig);
	file_put_contents($sFile, $objSig);

	$aRes["res"] = 1;		
	$aRes["msg"] = "Signature was Updated";
}else if($sMode=="upload_signature"){
	$supportFile=",png,";
	$reqDir = "staff_signature/";
	$oldFN = basename($_FILES["signature_file"]["name"]);
	$filename = $_FILES['signature_file']['name'];
	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	$reqFile = $reqDir.$sSid.$gType_leg.".png";

	if(strpos($supportFile,$ext)>0){
		if (move_uploaded_file($_FILES["signature_file"]["tmp_name"], $reqFile)) {
			$aRes["res"] = "1";
		} else {
			$aRes["msg"] = "Error Upload File. Please try again.";
		}
	}else{
		$aRes["msg"] = "Only PNG file is support.";
	}
}


$returnData = json_encode($aRes);
echo($returnData);
?>