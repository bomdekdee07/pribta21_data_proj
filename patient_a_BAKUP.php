<?
header('Content-type: text/html; charset=UTF-8');
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sEmail = urldecode(getQS("e"));
$sPass=getQS("p");
$sQ=getQS("q");
$sUid=getQS("u");
$sClinic=getSS("clinic");
$sMode=getQS("u_mode");
$aRes=array(); $sMessage="";

$sColDate=getQS("coldate");
$sColTime=getQS("coltime");

if($sClinic=="") $sClinic="IHRI";

if($sQ == "" && $sUid=="" && $sMode!="upload_image" && $sMode!="create_uid"){
	$aRes["res"] = "0";
	$aRes["msg"] = "UID and Queue is Missing";
	$returnData = json_encode($aRes);
	echo($returnData);
	exit();
}
$sToday = date("Y-m-d");
$curTime = date("H:i:s");
$sTime = "";

if($sMode=="upload_image"){
	$objImg = getQS("idimg");
	$sCid =getQS("cid");
	$sIssueD =getQS("issued");
	list($type, $objImg) = explode(';', $objImg);
	list(, $objImg)      = explode(',', $objImg);
	$objImg = base64_decode($objImg);
	file_put_contents("idimg/".$sCid.".png", $objImg);

	$sOldFile = "idimg/".$sCid."_".$sIssueD.".png";
	if(!file_exists($sOldFile)){
		file_put_contents($sOldFile, $objImg);
	}
	

	$aRes["res"] = 1;		
	$aRes["msg"] = "";

}else if($sMode=="get_uid_by_q"){
	include("in_db_conn.php");
	$query =" SELECT uid FROM k_visit_data WHERE queue=? AND site=? AND date_of_visit=?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sQ,$sClinic,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($uid);
	  while ($stmt->fetch()) {
		if($uid != "") $aRes["uid"] = $uid;
	  }
	}
	$mysqli->close();
}else if($sMode=="get_q_by_uid"){
	include("in_db_conn.php");
	$query =" SELECT queue FROM k_visit_data WHERE uid=? AND site=? AND date_of_visit=?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sClinic,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($queue );
	  while ($stmt->fetch()) {
		if($queue != "") $aRes["q"] = $queue;
	  }
	}
	$mysqli->close();
}else if($sMode=="create_visit_data"){
	include("in_db_conn.php");
	
	$query = "SELECT uid,queue,date_of_visit,time_of_visit FROM k_visit_data WHERE (uid=? OR queue=?)  AND date_of_visit=? AND site=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sUid,$sQ,$sToday,$sClinic);

	$isFound=false; $iCount = 0; $isBreak = false;
	$mUid = false; $mQ = false; 

	if($stmt->execute()){
	  $stmt->bind_result($uid,$queue,$date_of_visit,$time_of_visit );
 
	  while ($stmt->fetch()) {
	  	$isFound=true; $sTime = $time_of_visit;
		if($uid==$sUid && $queue==""){
			//Queue is Missing but uid match
			$mUid = true;
		}else if($queue==$sQ && $uid=="" ){
			//Uid is Missing or match with other Q but q is match
			$mQ = true;
		}else if($queue==$sQ && $uid!=$sUid && $uid != "" ){
			//Uid is match with other Q but q is match
			//This is terrible must stoppppp
			$aRes["msg"] = "Q is not available\r\nQ ถูกใช้งานแล้ว\r\nUID:".$uid;
			$isBreak=true;
			$mQ = true;
		}else if($queue==$sQ && $uid==$sUid ){
			//Row already exist no add is require
		}else if($queue!=$sQ && $uid==$sUid ){
			//UID exist but Q is not
			$aRes["msg"] = "UID is already added\r\nUID อยู่ในระบบอยู่แล้ว\r\nQ:".$queue;
			$isBreak=true;

		}
		$iCount++;
	  }
	}

	if($isBreak){

	}else if($isFound && $mUid && !$mQ && $iCount==1){
		//Row Found UID is Missing but Q is correct
		//Update UID to the Q
		$query = "UPDATE k_visit_data SET queue=? WHERE uid=? AND date_of_visit=? AND site=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sQ,$sUid,$sToday,$sClinic);
		if($stmt->execute()){
		  while ($stmt->fetch()) {
			$aRes["res"] = 1;
		  }
		}
	}else if($isFound && !$mUid && $mQ && $iCount==1){
		//Row Found Q is Missing but UID is correct
		//Update Q to the record
		$query = "UPDATE k_visit_data SET uid=? WHERE queue=? AND date_of_visit=? AND site=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sUid,$sQ,$sToday,$sClinic);
		if($stmt->execute()){
		  while ($stmt->fetch()) {
			$aRes["res"] = 1;
		  }
		}
	}else if($iCount > 1 && ($mUid || $mQ)){
		//THere are more than one 1 to be found. Find both UID and Q in different row
		//return and lets the user check Q or UID again
		$isBreak = true;
		$aRes["msg"] = "Q or UID is not available\r\nQ หรือ UID ถูกเพิ่มในระบบแล้ว";
	}else if($isFound==false){
		//No row found safe to add new Record
		$query = "INSERT INTO k_visit_data(site,uid,visit_number,date_of_visit,time_of_visit,queue,time_record)
			VALUES(?,?,1,?,?,?,NOW());";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss",$sClinic,$sUid,$sToday,$curTime,$sQ);
		if($stmt->execute()){
		  while ($stmt->fetch()) {
			$aRes["res"] = 1;
		  }
		}
		$sTime = $curTime;
	}

	$mysqli->close();

	if($isBreak){
		$aRes["res"] = 0;
		//$aRes["msg"] = 0;
	}else{
		$aRes["res"] = 1;
		$aRes["uid"] = $sUid;
		$aRes["curdate"] = $sToday;
		$aRes["curtime"] = $sTime;
	}
}else if($sMode=="update_patient_info"){
	$sUid = getQS("u");
	$aRes["res"] = 0;
	$aRes["uicdup"]="0";

	if($sUid==""){
		$returnData = json_encode($aRes);
		echo($returnData);
		exit();
	}
	include("in_db_conn.php");
	$aP = array('uic','fname','sname','en_fname','en_sname','nickname','clinic_type','sex','gender','date_of_birth','nation','blood_type','citizen_id','passport_id','id_address','id_district','id_province','id_zone','id_postal_code','use_id_address','address','district','province','zone','postal_code','country_other','tel_no','email','line_id','em_name_1','em_relation_1','em_phone_1','em_name_2','em_relation_2','em_phone_2','last_modify_date','religion','remark');

	$aObjData = array($sUid); $sIN = ""; $sVal="";  $sONDUP = "last_modify_date=NOW()"; $sPrepare = "s";
	foreach ($aP as $iKey => $colName) {
		if(isset($_POST[$colName]) || isset($_GET[$colName])){
			$sTemp = rawurldecode(getQS($colName));
			if($colName=="date_of_birth"){
				if($sTemp!=""){
					$aT = explode("-",$sTemp);
					if(count($aT)==3){
						//Number
						if($aT[0]>2400){
							//This is thai year convert to DC
							$sTemp = ($aT[0]-543)."-".$aT[1]."-".$aT[2];
						}
					}
				}
			}
			
			$sIN .= ",".$colName;
			array_push($aObjData,$sTemp);
			$sPrepare .= "s";
			$sONDUP .= ",".$colName."=VALUES(".$colName.")";
			$sVal .= ",?";

		}

	}

	//Check if Citizen ID is updated
	$isError = false;
	$sCid = rawurldecode(getQS("citizen_id"));

	if($sCid!="" && $sCid != "0000000000000"){
		$aRes["dupuid"] = "";
		//Citizen ID is set try to get if this one is already exists.
		$sCid = str_replace("-","",$sCid);
		$query = "SELECT uid FROM patient_info WHERE citizen_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sCid);
		
		if($stmt->execute()){
			$stmt->bind_result($uid);
			while ($stmt->fetch()) {
				$aRes["res"] = "0";
				$aRes["msg"] = "Duplicate Citizen ID with UID : ".$uid;
				$aRes["dupuid"] = $uid;
				$isError = true;
			}
		}
	}

	if($isError){

	}else{
		$AffRow =0;

		$query = "INSERT INTO patient_info(uid,last_modify_date".$sIN.") VALUES (?,NOW()".$sVal.") ON DUPLICATE KEY UPDATE ".$sONDUP.";";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sPrepare,...$aObjData);
		if($stmt->execute()){
			$AffRow =$stmt->affected_rows;
			if($AffRow > 0) $aRes["res"] = 1;
		}

		//Check if UIC is duplicate
		if(isset($_POST["uic"]) || isset($_GET["uic"])){
			$sUic = rawurldecode(getQS("uic"));
			$query = "SELECT uid FROM patient_info WHERE uic = ? AND uid != ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss",$sUic,$sUid);

			if($stmt->execute()){
			  $stmt->bind_result($uid);
			  while ($stmt->fetch()) {
				$aRes["uicdup"]="1";
				$aRes["dupuid"]=$uid;

			  }
			}
		}

		//Update LOG
		if($AffRow>0){
			$sSid = getSS("s_id");
			$query = "INSERT INTO patient_info_log(uid,update_datetime".$sIN.",update_by) VALUES (?,NOW()".$sVal.",'".$sSid."');";
			//error_log($query);
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($sPrepare,...$aObjData);
			if($stmt->execute()){
				$AffRow =$stmt->affected_rows;
				if($AffRow > 0) $aRes["res"] = 1;
			}
		}		
	}



	$mysqli->close();
}else if($sMode=="find_pinfo_by_uid"){
	include("in_db_conn.php");
	$query =" SELECT uid,fname,sname,sex,gender,date_of_birth FROM patient_info WHERE uid LIKE ?;";
	$sFindUid = "%".$sUid."%";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sFindUid);
	$sHtml="";
	if($stmt->execute()){
	  $stmt->bind_result( $uid,$fname,$sname,$sex,$gender,$date_of_birth  );
	  while ($stmt->fetch()) {
			$sHtml.=getPatientRow($uid,$fname,$sname,$sex,$gender,$date_of_birth );
	  }
	}
	$mysqli->close();
	$aRes["res"] = "1";
	$aRes["msg"] = $sHtml;
}else if($sMode=="find_visit_by_uid"){
	include("in_db_conn.php");
	$query ="  SELECT site,KVD.uid,date_of_visit,time_of_visit,fname,sname,date_of_birth FROM k_visit_data KVD
	 LEFT JOIN patient_info PI
	 ON PI.uid=KVD.uid
	 WHERE KVD.uid LIKE ?";
	 $aRes["res"] = "0";

	$sFindUid = "%".$sUid."%";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sFindUid);
	$sHtml="";
	if($stmt->execute()){
	  $stmt->bind_result( $site,$uid,$date_of_visit,$time_of_visit,$fname,$sname,$date_of_birth   );
	  while ($stmt->fetch()) {
	  		$aRes["res"] = "1";
			$sHtml.=getPatientVisitRow($site,$uid,$date_of_visit,$time_of_visit,$fname,$sname,$date_of_birth );
	  }
	}
	$mysqli->close();
	
	$aRes["msg"] = (($sHtml=="")?"No record found":$sHtml);
}else if($sMode=="create_uid"){
	include("in_db_conn.php");
	$aRes["res"] = "0";
	$aRes["dupuid"] = "";
	$aRes["dupname"]="";
	$aObjData=array(); $sPrepare = ""; $sVal = ""; $sIN="";

	$aP = array('fname','sname','en_fname','en_sname','nickname','clinic_type','sex','gender','date_of_birth','nation','blood_type','citizen_id','passport_id','id_address','id_district','id_province','id_zone','id_postal_code','use_id_address','address','district','province','zone','postal_code','country_other','tel_no','email','line_id','em_name_1','em_relation_1','em_phone_1','em_name_2','em_relation_2','em_phone_2','last_modify_date','religion','remark','passwd');

	//Create all variable $aObjData
	$sBCDOB = ""; $sDOBUic = "";

	$sFName = rawurldecode(getQS("fname"));
	$sSName = rawurldecode(getQS("sname"));
	$sDOB = rawurldecode(getQS("date_of_birth"));

	foreach ($aP as $iKey => $colName) {
		if(isset($_POST[$colName]) || isset($_GET[$colName])){
			$sTemp = rawurldecode(getQS($colName));
			if($colName=="date_of_birth"){
				if($sTemp!=""){
					$aT = explode("-",$sTemp);
					$sDCDate = "";
					if(count($aT)==3){
						//Number
						if($aT[0]>2400){
							//This is thai year convert to DC
							$sBCDOB = $sTemp;
							$sTemp = ($aT[0]-543)."-".$aT[1]."-".$aT[2];
							$sDCDate = $sTemp;
						}else{
							$sDCDate = $sTemp;
							$sTemp = ($aT[0]+543)."-".$aT[1]."-".$aT[2];
							$sBCDOB = $sTemp;

						}
						$sBYear = $aT[0];
						if($aT[0]<2400){
							$sBYear = $aT[0]+543;
						}

						$sDOBUic = $aT[2].$aT[1].substr($sBYear,2,2);
					}
				}
				array_push($aObjData,$sDCDate);
			}
			else if($colName=="passwd"){
				$sTemp = PASSWORD_HASH($sTemp, PASSWORD_DEFAULT);
				array_push($aObjData,$sTemp);
			}
			else{
				array_push($aObjData,$sTemp);
			}
			
			$sIN .= ($sIN==""?"":",").$colName;
			$sPrepare .= "s";
			$sVal .= ",?";
		}

	}



	$isError = false;
	$sCid = rawurldecode(getQS("citizen_id"));

	if($sCid!=""){
		//Citizen ID is set try to get if this one is already exists.
		$sCid = str_replace("-","",$sCid);
		$query = "SELECT uid FROM patient_info WHERE citizen_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sCid);
		
		if($stmt->execute()){
			$stmt->bind_result($uid);
			while ($stmt->fetch()) {
				$aRes["res"] = "0";
				$aRes["msg"] = "Duplicate Citizen ID with UID : ".$uid;
				$aRes["dupuid"] = $uid;
				$isError = true;
			}
		}
	}

	//Check if Name and DOB exactly Matched
	$query = "SELECT uid FROM patient_info WHERE fname=? AND sname=? AND date_of_birth=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sFName,$sSName,$sDOB);
	
	if($stmt->execute()){
		$stmt->bind_result($uid);
		while ($stmt->fetch()) {
			$aRes["res"] = "0";
			$aRes["msg"] = "Name and DOB is exact match with : ".$uid;
			$aRes["dupname"] = $uid;
			$isError = true;
		}
	}



	if($isError){

	}else{
		$aUic = array();
		$sUic = rawurldecode(getQS("uic"));

		$sFinalUic = "";

		if($sUic==""){
			//Create UIC if UIC is not available


			if($sFName=="") $sFName = rawurldecode(getQS("en_fname"));
			if($sSName=="") $sSName = rawurldecode(getQS("en_sname"));
			if($sFName=="")	$s1st = "J";
			if($sSName=="")	$s2nd = "D";
			$aUic = getAllUIC($sFName,$sSName,$sDOBUic);
			//Check if all any uic is available
			$query = "SELECT uic FROM basic_reg WHERE uic IN ('".implode("','",$aUic)."')";
			$stmt = $mysqli->prepare($query);
			$aDupUic = "";
			if($stmt->execute()){
			  $stmt->bind_result($uic );
			  while ($stmt->fetch()) {
					$aDupUic[$uic] = $uic;
			  }
			}

			//Find undup UIC
			$iC =  count($aUic); 
			for($ix=0;$ix<$iC;$ix++){
				if(isset($aDupUic[$aUic[$ix]])){
					//Can't used this uic;
				}else{
					//Use this UIC
					$sFinalUic = $aUic[$ix];
					$ix= $iC+10;
				}
			}



		}else{
			$sFinalUic=$sUic;
		}

		$sPrefix = "P".date("y");

		if($sFinalUic!=""){
			//Insert into basic REG
			$query="INSERT INTO basic_reg(uic,reg_date,fname,sname,national_id) VALUES(?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssss",$sFinalUic,$sToday,$sFName,$sSName,$sCid);
			if($stmt->execute()){
			  while ($stmt->fetch()) {
				
			  }
			}

			array_push($aObjData,$sFinalUic);
			$sIN .= ",uic";
			$sPrepare .= "s";
			$sVal .= ",?";
			$sUid = "";
		}

		$query = "";
		if(getQS("clinic_type")==""){
			//Not set or not assigned
			$query = "INSERT INTO patient_info (clinic_type,uid,".$sIN.",last_modify_date) SELECT  'P',@uid := CONCAT('".$sPrefix."-',LPAD((SUBSTRING(IFNULL(MAX(uid),0),5,5)*1)+1,5,0))".$sVal.",NOW() FROM patient_info WHERE uid LIKE '".$sPrefix."-%';";
		}else{
			$query = "INSERT INTO patient_info (uid,".$sIN.",last_modify_date) SELECT  @uid := CONCAT('".$sPrefix."-',LPAD((SUBSTRING(IFNULL(MAX(uid),0),5,5)*1)+1,5,0))".$sVal.",NOW() FROM patient_info WHERE uid LIKE '".$sPrefix."-%';";
		}
		

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sPrepare,...$aObjData);
		if($stmt->execute()){
		  	$AffRow =$stmt->affected_rows;
			if($AffRow > 0){ 
				$aRes["res"] = 1;
				
				$query2 = "SELECT @uid";
				$stmt2 = $mysqli->prepare($query2);
				if($stmt2->execute()){
					$stmt2->bind_result($uid);
					  while ($stmt2->fetch()) {
						$sUid = $uid;
					  }
				}
		  	}
		}

		if($sUid!=""){
			$aRes["uid"] = $sUid;
			$aRes["uic"] = $sFinalUic;

			$query2 = "INSERT INTO uic_gen(uic,uic2,uid,dob,clinic_id,reg_date) VALUES(?,?,?,?,'IHRI',?);";
			$stmt2 = $mysqli->prepare($query2);
			$stmt2->bind_param("sssss",$sFinalUic,$sFinalUic,$sUid,$sDOB,$sToday);
			if($stmt2->execute()){
				//Since it is not important just create it so UID won't duplicate
			}
			$sSid = getSS("s_id");
			$query = "INSERT INTO patient_info_log (uid,".$sIN.",update_datetime,update_by) VALUES(?".$sVal.",NOW(),'$sSid');";
			
			array_unshift($aObjData, $sUid);

			$sPrepare .= "s";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($sPrepare,...$aObjData);
			if($stmt->execute()){
				$AffRow =$stmt->affected_rows;
				if($AffRow > 0) $aRes["res"] = 1;
			}


		}else{
			$aRes["msg"]="Error UID can't create. Please try again";
		}




	}

	$mysqli->close();
}

//2nd Version
function getAllUIC($sFName,$sSName,$sDob){

	$aAllUic = array();
	$s1st = ""; $s2nd = "";
	$sThVowels="*&^%$#@!_/\\\";:+.ZX{}[]|ๆ็๋?<>=-0123456789เแ์ๅะาิีืึุูโใไำั้";
	$aVowel=str_split($sThVowels,1);

	$aTemp = getMBStrSplit($sFName);
	$a1st = array();
	foreach ($aTemp as $ikey => $sChar) {
		if(mb_strpos($sThVowels, $sChar)===false){
			array_push($a1st,$sChar);
		}
	}
	unset($aTemp);
	$aTemp = getMBStrSplit($sSName);
	$a2nd = array();
	foreach ($aTemp as $ikey => $sChar) {
		if(mb_strpos($sThVowels, $sChar)===false){
			array_push($a2nd,$sChar);
		}
	}

	foreach ($a1st as $iKey1 => $s1) {
		$sTempUic = "";
		foreach ($a2nd as $iKey2 => $s2) {
			$sTempUic = $s1.$s2.$sDob;
			array_push($aAllUic,$sTempUic);
		}
		
	}
	//error_log($sFName." ".$sSName);
	//error_log(implode(",",$aAllUic));

	return $aAllUic;
}

// Convert a string to an array with multibyte string
// I got it from here https://www.thaicreate.com/php/forum/076169.html

function getMBStrSplit($string, $split_length = 1){
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8'); 
	
	$split_length = ($split_length <= 0) ? 1 : $split_length;
	$mb_strlen = mb_strlen($string, 'utf-8');
	$array = array();
	$i = 0; 
	
	while($i < $mb_strlen)
	{
		$array[] = mb_substr($string, $i, $split_length);
		$i = $i+$split_length;
	}
	
	return $array;
}


$returnData = json_encode($aRes);
echo($returnData);

?>