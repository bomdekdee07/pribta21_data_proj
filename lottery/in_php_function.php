<?
function getDataAttr($sUid,$sColDate,$sColTime,$sQ=""){
	$sHtmlAttr = " data-uid='$sUid' data-coldate='$sColDate' data-coltime='$sColTime'";
	if($sQ!="") $sHtmlAttr.=" data-q='$sQ' ";
	return $sHtmlAttr;
}
function getHiddenPk($sUid,$sColDate,$sColTime,$sClsInput="saveinput"){
	$sHtmlKeyId= "
	<input type='hidden' class='".$sClsInput."' data-keyid='uid' data-pk='1' value='$sUid'  data-odata='$sUid' />
	<input type='hidden' class='".$sClsInput."' data-keyid='collect_date' data-pk='1' value='$sColDate' data-odata='$sColDate'/>
	<input type='hidden' class='".$sClsInput."' data-keyid='collect_time' data-pk='1' value='$sColTime' data-odata='$sColTime'/>
";
	return $sHtmlKeyId;
}
function getBirthSex($sSex,$sStyle="short"){
	$aSexS = ["","M","F","B"];
	$aSexF = ["","Male","Female","Both"];

	if($sStyle=="short"){
		return (isset($aSexS[$sSex])?$aSexS[$sSex]:$sSex);
	}else if($sStyle=="full"){
		return (isset($aSexF[$sSex])?$aSexF[$sSex]:$sSex);
	}else{
		return $sSex;
	}
}

function getDoseBefore($sDose,$lang="TH"){
	$aText=["B"=>"Before","A"=>"After","P"=>"With"];
	$aTextTH=["B"=>"ก่อน","A"=>"หลัง","P"=>"พร้อม"];
	if($lang=="TH"){
		return (isset($aTextTH[$sDose])?$aTextTH[$sDose]:"");
	}else if($lang=="EN"){
		return (isset($aText[$sDose])?$aText[$sDose]:"");
	}
}
function strReplaceOnce($haystack,$needle,$replace){
	$pos = strpos($haystack, $needle);
	if ($pos !== false) {
	    return substr_replace($haystack, $replace, $pos, strlen($needle));
	}else{
		return $haystack;
	}
}
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
function j_enc($token){
	$cipher_method = 'aes-128-ctr';

  $cipher_method = 'aes-128-cbc';
  $enc_key = openssl_digest(php_uname(), 'SHA256', TRUE);
  $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
  $crypted_token = openssl_encrypt($token, $cipher_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);
  unset($token, $cipher_method, $enc_key, $enc_iv);
  return $crypted_token;
}

function j_dec($crypted_token){
  list($crypted_token, $enc_iv) = explode("::", $crypted_token);
  $cipher_method = 'aes-128-ctr';
  $cipher_method = 'aes-128-cbc';
  $enc_key = openssl_digest(php_uname(), 'SHA256', TRUE);
  $token = openssl_decrypt($crypted_token, $cipher_method, $enc_key, 0, hex2bin($enc_iv));
  unset($crypted_token, $cipher_method, $enc_key, $enc_iv);
  return $token;
}


function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

function getAgeDetail($dob){
	$dob_a = explode("-", $dob);
	$today_a = explode("-", date("Y-m-d"));
	$dob_d = $dob_a[2];$dob_m = $dob_a[1];$dob_y = $dob_a[0];
	$today_d = $today_a[2];$today_m = $today_a[1];$today_y = $today_a[0];
	if(($dob_d*1)==0) $dob_d = 15;
	if(($dob_m*1)==0) $dob_m = 7;

	$years = $today_y - $dob_y;
	$months = $today_m - $dob_m;
	$days=$today_d - $dob_d;
	if ($today_m.$today_d < $dob_m.$dob_d) {
		$years--;
		$months = 12 + $today_m - $dob_m;
	}

	if ($today_d < $dob_d) $months--;

	$firstMonths=array(1,3,5,7,8,10,12);
	$secondMonths=array(4,6,9,11);
	$thirdMonths=array(2);

	if($today_m - $dob_m == 1){
		if(in_array($dob_m, $firstMonths)){
			array_push($firstMonths, 0);
		}elseif(in_array($dob_m, $secondMonths)) {
			array_push($secondMonths, 0);
		}elseif(in_array($dob_m, $thirdMonths)){
			array_push($thirdMonths, 0);
		}
	}
	return " $years ปี $months เดือน ".abs($days)." วัน";
}




function getDateText($sDate,$sLang='TH',$sStyle='shortTH',$sSpace=' '){
	//sDate  can be either 2021-01-01, 2021/01/01 , 01/01/2021, 01-01-2021
	//The middle number must be month
	//$sLang == "TH" "US";
	//$sStyle == "short" "shortTH" "shortCap" "shortLow" "full" "fullTH" "fullCap" "fullLow" "number";
	//$sSpace == Sperator "/",":","-" or any as you wish
	$sResult = "";
	if($sDate=="" || $sDate=="0000-00-00"){
		return $sDate;
	}
	$aD="";
	if(strpos($sDate,"-")>=0){
		$aD = explode("-",$sDate);
	}else if(strpos($sDate,"/")>=0){
		$aD = explode("/",$sDate);
	}else{
		return $sDate;
	}
	if(count($aD)<3) return $sDate;
	
	$sShortM = ['Uk','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	$sShortMTh = ['Uk','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

	$sFullM = ['Unknown','January','Febuary','March','April','May','June','July','August','September','October','November','December'];
	$sFullMTh = ['ไม่ระบุ','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

	$sM="";$sD="";$sY="";
	if($aD[0]>1000){
		//First array is Year
		$sY=$aD[0];
		$sD=$aD[2];
	}else if($aD[2]>1000){
		//Last array is Year
		$sY=$aD[2];
		$sD=$aD[0];
	}else{
		//Format is not support
		return $sDate;
	}
	$sM=$aD[1]*1;

	if($sY*1==0){
		$sY = "0000";
	}else if($sLang=="TH" && $sY < 2300){
		$sY = ($sY * 1) + 543;
	}else if($sLang=="US" && $sY > 2300){
		$sY = ($sY * 1) - 543;
	}
	$sD=str_pad($sD, 2, "0", STR_PAD_LEFT);

	if($sStyle=="short"){
		return $sD.$sSpace.$sShortM[$sM].$sSpace.$sY;
	}else if($sStyle=="shortTH"){
		return $sD.$sSpace.($sShortMTh[$sM]).$sSpace.$sY;
	}else if($sStyle=="shortCap"){
		return $sD.$sSpace.strtoupper($sShortM[$sM]).$sSpace.$sY;
	}else if($sStyle=="shortLow"){
		return $sD.$sSpace.strtolower($sShortM[$sM]).$sSpace.$sY;
	}else if($sStyle=="full"){
		return $sD.$sSpace.($sFullM($sM)).$sSpace.$sY;
	}else if($sStyle=="fullTH"){
		return $sD.$sSpace.($sFullMTh($sM)).$sSpace.$sY;
	}else if($sStyle=="fullCap"){
		return $sD.$sSpace.strtoupper($sFullM($sM)).$sSpace.$sY;
	}else if($sStyle=="fullLow"){
		return $sD.$sSpace.strtolower($sFullM($sM)).$sSpace.$sY;
	}else if($sStyle=="number"){
		$sM = str_pad($sM, 2, "0", STR_PAD_LEFT);
		return $sD.$sSpace.$sM.$sSpace.$sY;
	}else if($sStyle=="db"){
		$sM = str_pad($sM, 2, "0", STR_PAD_LEFT);
		return $sY.$sSpace.$sM.$sSpace.$sD;
	}
	return $sDate;
}
date_default_timezone_set("Asia/Bangkok");

?>