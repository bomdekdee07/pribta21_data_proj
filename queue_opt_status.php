<?
include_once("in_php_function.php");
$sLang = getQS("lang","en");

$aLang_en=array("","Waiting","In Room")
$aLang_th=array("","คอย","อยู่ในห้อง")

$aT=($sLang=="en")?$aLang_en:$aLang_th;
$sOpt = "<option value='0'></option>
<option value='1'>".$aT[1]."</option>
<option value='2'>".$aT[2]."</option>


";
echo($sOpt);
?>
