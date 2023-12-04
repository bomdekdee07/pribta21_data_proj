<?
include_once("in_session.php");
include_once("in_php_function.php");
$sSid=getSS("s_id");
$sFile="staff_signature/".$sSid.".png";
$sToday=date("YmdHis");
if(file_exists($sFile)){
	$sFile.="?dt=".$sToday;
}else{
	$sFile="assets/image/nophoto.jpg";
}
echo("<img src='$sFile' class='h-70 f-border' />");
?>