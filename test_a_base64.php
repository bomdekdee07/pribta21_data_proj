<?
include_once("in_php_function.php");
$aRes=array();

$aPost = getAllQS();
$objImg = $aPost["idimg"];

if($aPost["u_mode"]=="upload_image"){
	
	list($type, $objImg) = explode(';', $objImg);
	list(, $objImg)      = explode(',', $objImg);
	$objImg = base64_decode($objImg);
	file_put_contents("idimg/".$aPost["cid"].".png", $objImg);


	$aRes["res"] = 1;		
	$aRes["msg"] = "";
}



$sTemp=json_encode($aRes);
echo($sTemp);
?>