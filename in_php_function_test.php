<?

function isEmpty($sText){
	if($sText==null||is_null($sText)||$sText=="") return true;
	else return false;
}
function getOrderStatus($sOrdStatus,$lang="th"){

	$aStatusTH = array("A0"=>"ยืนยัน","B0"=>"รอชำระเงิน","P0"=>"รอรับยา","P1"=>"เสร็จสิ้น");
	$aStatusEN = array("A0"=>"Confirm","B0"=>"Payment pPending","P0"=>"Drug pending","P1"=>"Done");
 
	if($lang=="th"){
		return $aStatusTH[$sOrdStatus];
	}else{
		return $aStatusEN[$sOrdStatus];
	}
}

function getQS($sName,$sDef=""){
	$sResult = (isset($_GET[$sName])?urlencode($_GET[$sName]):"");
	if($sResult=="") $sResult = (isset($_POST[$sName])?urlencode($_POST[$sName]):"");
	if($sResult=="null" || $sResult=="") $sResult=$sDef;
	return urldecode($sResult);
	
}
function getQSObj($sName){
	return (isset($_REQUEST[$sName])?$_REQUEST[$sName]:array());
}
function getSS($sName){
	$sResult = (isset($_SESSION[$sName])?urldecode($_SESSION[$sName]):"");
	return $sResult;
}
function getPerm($sModule,$sCode="",$sMode=""){
	//sMode : view,insert,update,delete,admin;
	$sResult = null;
	if($sCode==""){
		$sResult = (isset($_SESSION["MODULE"][$sModule])?($_SESSION["MODULE"][$sModule]):"");
	}else if($sMode==""){
		$sResult = (isset($_SESSION["MODULE"][$sModule][$sCode])?($_SESSION["MODULE"][$sModule][$sCode]):"");
	}else{
		$sResult = (isset($_SESSION["MODULE"][$sModule][$sCode][$sMode])?($_SESSION["MODULE"][$sModule][$sCode][$sMode]):"");
	}
	
	return $sResult;
}

function getAllQS(){
	$aPost = array();
	foreach ($_POST as $key => $value) {
		if(gettype($value)=="string") $aPost[$key] = htmlspecialchars($value);
		else $aPost[$key] = $value;
	}
	foreach ($_GET as $key => $value) {
		if(isset($aPost[$key]) ){

		}else{
			$aPost[$key] = htmlspecialchars($value);
		}
	}
	return $aPost;
}
function j_numtothaistring($iNum){
	$aTxtNum = array('','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า');
	$aTxtUnit = array('','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน');
	$aNum = explode(".",$iNum);
	$aNumList = str_split($aNum[0]."");
	$iTotal = count($aNumList);

	$sTxt = "";
	foreach ($aNumList as $iInd => $sNum) {
		if($iTotal > 1){
			if($iInd == $iTotal-1 && $sNum=="1" && $aNumList[$iTotal-2] !== "0") $sTxt .= "เอ็ด";
			else if($iInd==$iTotal-2 && $sNum=="2") $sTxt .= "ยี่สิบ";
			else if($iInd==$iTotal-2 && $sNum=="1") $sTxt .= "สิบ";
			else if($sNum=="0") $sTxt .= "";
			else{
			
				$sTxt.= $aTxtNum[$sNum].$aTxtUnit[$iTotal-$iInd-1];
			}
		}else{
			$sTxt .= $aTxtNum[$sNum];
		}
	}
	$sTxt.="บาท";

	$aNumList = array();
	if(isset($aNum[1])) $aNumList = str_split($aNum[1]."");

	$iTotal = count($aNumList);
	if($iTotal > 0 ){
		foreach ($aNumList as $iInd => $sNum) {
			if($iTotal > 1){
				if($iInd == $iTotal-1 && $sNum=="1" && $aNumList[$iTotal-2] !== "0") $sTxt .= "เอ็ด";
				else if($iInd == $iTotal-2 && $sNum=="2") $sTxt .= "ยี่สิบ";
				else if($iInd == $iTotal-2 && $sNum=="1") $sTxt .= "สิบ";
				else if($sNum=="0") $sTxt .= "";
				else{
				
					$sTxt.= $aTxtNum[$sNum].$aTxtUnit[$iTotal-$iInd-1];
				}
			}else{
				$sTxt .= (isset($aTxtNum[$sNum])?$aTxtNum[$sNum]:"");
			}
		}
		$sTxt.="สตางค์";
	}

	return $sTxt;
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function easy_dec($sKey){
	return base64_decode(urldecode($sKey));
}
function easy_enc($sKey){
	return urlencode(base64_encode($sKey));
}
function j_enc($token)