<?
include_once("in_php_function.php");

$aPost = getAllQS();
$aPost["col"] = (isset($aPost["col"])?$aPost["col"]:"");
$aPost["colpk"] = (isset($aPost["colpk"])?$aPost["colpk"]:"");
$aCol = explode(",",$aPost["col"]);
$aColPk = explode(",",$aPost["colpk"]);

$sInsVal=""; $sParam=""; $aInsData=array(); $aUpdData=array();
$sInsCol="";
$sUpdWhere="";
$sUpdSet="";

foreach ($aCol as $sKey => $value) {
	if($value!=""){
		$sInsCol.=(($sInsCol=="")?"":",").$value;
		$sInsVal.= (($sInsVal=="")?"?":",?");
		$sParam.="s";
		$sUpdSet.=(($sUpdSet=="")?"":",").$value."=?";	
		array_push($aUpdData,$aPost[$value]);	
	}
}
foreach ($aColPk as $sKey => $value) {
	if($value!=""){
		$sUpdWhere.=(($sUpdWhere=="")?"":" AND ").$value."=?";
		$sInsCol.=(($sInsCol=="")?"":",").$value;
		if($aPost[$value]=="{NEW}"){
			$sInsVal.= (($sInsVal=="")?"":",")."{NEW_".$value."}";
			
		}else{
			$sInsVal.= (($sInsVal=="")?"?":",?");	
			$sParam.="s";
			array_push($aUpdData,$aPost[$value]);
		}
		
		
		
	}
}

?>