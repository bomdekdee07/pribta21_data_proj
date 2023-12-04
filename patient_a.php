<?
//JENG
header('Content-type: text/html; charset=UTF-8');
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sSid = getSS("s_id");
$sEmail = urldecode(getQS("e"));
$sPass=getQS("p");
$sQ=getQS("q");
$sUid=getQS("u");
if($sUid=="") $sUid=getQS("uid");
$sClinic=getSS("clinic");
$sMode=getQS("u_mode");
$aRes=array(); $sMessage="";
$aRes["res"] = 0;
$aRes["msg"] = "";

$isLog = false; $aLog=array();
$sColDate=getQS("coldate");
$sColTime=getQS("coltime");
$sToday = date("Y-m-d");
$curTime = date("H:i:s");
$sNow = $sToday." ".$curTime;
$sTime = "";
$isPLog=false;



$aP = array('uic','fname','sname','en_fname','en_sname','nickname','clinic_type','sex','gender','date_of_birth','nation','blood_type','citizen_id','passport_id','id_address','id_district','id_province','id_zone','id_postal_code','use_id_address','address','district','province','zone','postal_code','country_other','tel_no','email','line_id','em_name_1','em_relation_1','em_phone_1','em_name_2','em_relation_2','em_phone_2','update_datetime','religion','remark','note_all_clinic','prep_nhso');




if($sClinic=="") $sClinic="IHRI";

if($sUid=="" && $sMode!="create_uid" && $sMode!="upload_image" && $sMode != "get_uid_by_q" && $sMode != "create_visit_data"){
	$aRes["msg"]="No UID";
	$returnData = json_encode($aRes);
	echo($returnData);
	exit();
}

//For Log
$aPost = getAllQS();
$aLogData=array(); $sLogCol="";
$sPreS="ssss"; $sPreQ="?,?,?,?";
foreach ($aP as $iInd => $sCol) {

	if(isset($aPost[$sCol])){

		$sPreS.="s";
		$sPreQ.=",?";
		$sLogCol.=",".$sCol;

		if($sCol=="citizen_id" || $sCol=="tel_no"){
			$aPost[$sCol] = str_replace("-", "", $aPost[$sCol]);
		}
		array_push($aLogData, $aPost[$sCol]);
	}
}



include("in_db_conn.php");

if($sMode=="get_pinfo" && $sUid!=""){
	
	$query =" SELECT uid,fname,sname,en_fname,en_sname,sex,gender,date_of_birth FROM patient_info WHERE uid = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	$sHtml="";
	if($stmt->execute()){
	  $stmt->bind_result( $uid,$fname,$sname,$en_fname,$en_sname,$sex,$gender,$date_of_birth  );
	  while ($stmt->fetch()) {
		$aRes["res"] = "1";
		$aRes["uid"]=$uid;
		$aRes["fname"]=$fname;
		if($fname=="") $aRes["fname"]=$en_fname;
		$aRes["sname"]=$sname;
		if($sname=="") $aRes["sname"]=$en_sname;

	  }
	}
}else if($sMode=="add_relationship" && $sUid!=""){
	$sRelUid=getQS("reluid");
	$sRelType=getQS("reltype");
	$sName=getQS("name");

	if($sUid=="" || $sRelUid=="" || $sRelType==""){
		$aRes["msg"]="Something is missing. Please check";
	}else{

		$query =" INSERT INTO patient_info_relate(uid,rel_uid,rel_type) VALUES(?,?,?);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sUid,$sRelUid,$sRelType);
		$sHtml="";
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$aRes["msg"] = getRelationship($sUid,$sRelUid,$sName,"","","","",$sRelType,1);
				$isLog=true;
				$aLog[] = array($sSid,"P_RELATE",$sMode,"uid,rel_uid,rel_type",urlencode($sUid).",".urlencode($sRelUid).",".urlencode($sRelType),"","");
			}else{
				//Not Success
			}
		}else{
			$aRes["msg"] = "Duplicate Relationship";
		}
		
	}
}else if($sMode=="del_relationship" && $sUid!=""){
	$sRelUid=getQS("reluid");

	if($sUid=="" || $sRelUid==""){
		$aRes["msg"]="Something is missing. Please check";
	}else{
		
		$query =" DELETE FROM patient_info_relate WHERE (uid=? AND rel_uid=?) OR (uid=? AND rel_uid=?) ";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sUid,$sRelUid,$sRelUid,$sUid);
		$sHtml="";
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$isLog=true;
				$aLog[] = array($sSid,"P_RELATE",$sMode,"","","uid,rel_uid",$sUid.",".$sRelUid);
			}else{
				$aRes["msg"] = "Cant delete. Please try again.";
			}
		}else{
			$aRes["msg"] = $stmt->error;
		}
		
	}
}else if($sMode=="upload_image"){
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
}else if($sMode=="get_uid_by_q" && $sQ!=""){
	
	$sClinic=getQS("clinic");
	$query =" SELECT uid FROM i_queue_list WHERE queue=? AND clinic_id=? AND collect_date=?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sQ,$sClinic,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($uid);
	  while ($stmt->fetch()) {
		if($uid != "") {
			$aRes["res"]="1";
			$aRes["uid"] = $uid;
		}
	  }
	}
	
}else if($sMode=="get_q_by_uid" && $sUid!=""){
	
	$query =" SELECT queue FROM i_queue_list WHERE uid=? AND clinic_id=? AND collect_date=?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sClinic,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($queue );
	  while ($stmt->fetch()) {
		if($queue != "") $aRes["q"] = $queue;
	  }
	}
	
}else if($sMode=="create_visit_data" && $sUid!=""){
	
	
	$query = "SELECT uid,queue,collect_date,collect_time FROM i_queue_list WHERE (uid=? OR queue=?)  AND collect_date=? AND clinic_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sUid,$sQ,$sToday,$sClinic);

	$isFound=false; $iCount = 0; $isBreak = false;
	$mUid = false; $mQ = false; 

	if($stmt->execute()){
	  $stmt->bind_result($uid,$queue,$collect_date,$collect_time );
 
	  while ($stmt->fetch()) {
	  	$isFound=true; $sTime = $collect_time;
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
		$query = "UPDATE i_queue_list SET queue=? WHERE uid=? AND collect_date=? AND clinic_id=?";
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
		$query = "UPDATE i_queue_list SET uid=? WHERE queue=? AND collect_date=? AND clinic_id=?";
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
		$query = "INSERT INTO i_queue_list(clinic_id,uid,collect_date,collect_time,queue,queue_datetime)
			VALUES(?,?,?,?,?,NOW());";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss",$sClinic,$sUid,$sToday,$curTime,$sQ);
		if($stmt->execute()){
		  while ($stmt->fetch()) {
			$aRes["res"] = 1;
		  }
		}
		$sTime = $curTime;
	}

	

	if($isBreak){
		$aRes["res"] = 0;
		//$aRes["msg"] = 0;
	}else{
		$aRes["res"] = 1;
		$aRes["uid"] = $sUid;
		$aRes["curdate"] = $sToday;
		$aRes["curtime"] = $sTime;
	}
}else if($sMode=="update_patient_info" && $sUid!=""){
	$aRes["uicdup"]="0";
	//Check if Citizen ID is updated
	$isError = false;

	
	$aObjData = array($sUid); $sIN = ""; $sVal="";  $sONDUP = "last_modify_date=NOW()"; $sPrepare = "s";

	$sCid = rawurldecode(getQS("citizen_id"));

	if($sCid!="" && $sCid*1 != 0 && $sCid != "1111111111111"){
		$aRes["dupuid"] = "";
		//Citizen ID is set try to get if this one is already exists.
		$sCid = str_replace("-","",$sCid);
		$query = "SELECT uid FROM patient_info WHERE citizen_id=? AND uid!=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sCid,$sUid);
		
		if($stmt->execute()){
			$stmt->bind_result($uid);
			while ($stmt->fetch()) {
				$aRes["res"] = "0";
				$aRes["msg"] = "Duplicate Citizen ID with UID : ".$uid;
				$aRes["dupuid"] = $uid;
				$isError = true;
			}
		}
		$_POST["nation"]="THA"; $_GET["nation"]="THA";
	}



	if($isError){

	}else{
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
				}else if($colName=="citizen_id" || $colName=="tel_no"){
					$sTemp = str_replace("-", "", $sTemp);
				}
				
				$sIN .= ",".$colName;
				array_push($aObjData,$sTemp);
				$sPrepare .= "s";
				$sONDUP .= ",".$colName."=VALUES(".$colName.")";
				$sVal .= ",?";

			}

		}



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

		$sSid = getSS("s_id");
		/*
		$query = "INSERT INTO patient_info_log(uid,update_datetime".$sIN.",update_by) VALUES (?,NOW()".$sVal.",'".$sSid."');";
		//error_log($query);
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sPrepare,...$aObjData);
		if($stmt->execute()){
			$AffRow =$stmt->affected_rows;
			if($AffRow > 0) $aRes["res"] = 1;
		}
		*/
	}

}else if($sMode=="find_pinfo_by_uid" && $sUid!=""){
	
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
	
	$aRes["res"] = "1";
	$aRes["msg"] = $sHtml;
}else if($sMode=="find_visit_by_uid" && $sUid!=""){
	
	$query ="  SELECT clinic_id,IQL.uid,collect_date,collect_time,fname,sname,date_of_birth FROM i_queue_list IQL
	 LEFT JOIN patient_info PI
	 ON PI.uid=IQL.uid
	 WHERE IQL.uid LIKE ? ORDER BY IQL.uid,collect_date,collect_time";
	 $aRes["res"] = "0";

	$sFindUid = "%".$sUid."%";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sFindUid);
	$sHtml="";
	if($stmt->execute()){
	  $stmt->bind_result( $clinic_id,$uid,$collect_date,$collect_time,$fname,$sname,$date_of_birth   );
	  while ($stmt->fetch()) {
	  		$aRes["res"] = "1";
			$sHtml.=getPatientVisitRow($clinic_id,$uid,$collect_date,$collect_time,$fname,$sname,$date_of_birth );
	  }
	}
	
	
	$aRes["msg"] = (($sHtml=="")?"No record found":$sHtml);
}else if($sMode=="create_uid"){
	
	$aRes["dupuid"] = "";
	$aRes["dupname"]="";
	$aObjData=array(); $sPrepare = ""; $sVal = ""; $sIN="";

	$isError = false;
	$sCid = rawurldecode(getQS("citizen_id"));

	if($sCid!="" && $sCid != "0000000000000" && $sCid != "1111111111111"){
		//Citizen ID is set try to get if this one is already exists.
		$sCid = str_replace("-","",$sCid);
		$query = "SELECT uid FROM patient_info WHERE REPLACE(citizen_id,'-','')=?";
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
		$_POST["nation"]="THA"; $_GET["nation"]="THA";
	}

	//Create all variable $aObjData
	$sBCDOB = ""; $sDOBUic = "";

	$sFName = rawurldecode(getQS("fname"));
	$sSName = rawurldecode(getQS("sname"));
	$sDOB = rawurldecode(getQS("date_of_birth"));

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

		$sDOB = rawurldecode(getQS("date_of_birth"));
		if($sDOB!=""){
			$aT = explode("-",$sDOB);
			$sDCDate = "";
			if(count($aT)==3){
				//Number
				if($aT[0]>2400){
					//This is thai year convert to DC
					$sBCDOB = $sDOB;
					$sDOB = ($aT[0]-543)."-".$aT[1]."-".$aT[2];
					$sDCDate = $sDOB;
				}else{
					$sDCDate = $sDOB;
					$sDOB = ($aT[0]+543)."-".$aT[1]."-".$aT[2];
					$sBCDOB = $sDOB;

				}
				$sBYear = $aT[0];
				if($aT[0]<2400){
					$sBYear = $aT[0]+543;
				}

				$sDOBUic = $aT[2].$aT[1].substr($sBYear,2,2);
			}
			$_POST["uic"]=$sDCDate;
			$_GET["uic"]=$sDCDate;
		}
		


		if($sUic==""){
			//Create UIC if UIC is not available
			if($sFName=="") $sFName = rawurldecode(getQS("en_fname"));
			if($sSName=="") $sSName = rawurldecode(getQS("en_sname"));
			if($sFName=="")	$s1st = "J";
			if($sSName=="")	$s2nd = "D";
			$aUic = getAllUIC($sFName,$sSName,$sDOBUic);
			
			$query = "SELECT uic FROM patient_info WHERE uic IN ('".implode("','",$aUic)."')";
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

		if($sFinalUic!="") {
			$_POST["uic"]=$sFinalUic;
			$_GET["uic"]=$sFinalUic;
		}

		foreach ($aP as $iKey => $colName) {
			if(isset($_POST[$colName]) || isset($_GET[$colName])){
				$sTemp = rawurldecode(getQS($colName));
				if($colName=="clinic_type" && $sTemp==""){
					$sTemp="P";
				}
				array_push($aObjData,$sTemp);
				$sIN .= ($sIN==""?"":",").$colName;
				$sPrepare .= "s";
				$sVal .= ",?";
			}

		}




		$sPrefix = "P".date("y");

		$query = "INSERT INTO patient_info (uid,".$sIN.",last_modify_date) SELECT @uid :=CONCAT('".$sPrefix."-',LPAD(
		IFNULL(
			MAX( 
				REPLACE(uid,'".$sPrefix."-','')*1 
			),0
		)+1,5,0))";
		$query.=$sVal.",NOW() FROM patient_info WHERE uid LIKE '".$sPrefix."-%';";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sPrepare,...$aObjData);
		//error_log($query.PHP_EOL.$sVal.PHP_EOL.$sPrepare.PHP_EOL.implode(",", $aObjData));
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
		}else{
			error_log($stmt->error);
		}

		if($sUid!=""){
			$aRes["uid"] = $sUid;
			$aRes["uic"] = $sFinalUic;
			/*
			$query2 = "INSERT INTO uic_gen(uic,uic2,uid,dob,clinic_id,reg_date) VALUES(?,?,?,?,'IHRI',?);";
			$stmt2 = $mysqli->prepare($query2);
			$stmt2->bind_param("sssss",$sFinalUic,$sFinalUic,$sUid,$sDOB,$sToday);
			if($stmt2->execute()){
				//Since it is not important just create it so UID won't duplicate
			}
			*/
			$sSid = getSS("s_id");
			/*
			$query = "INSERT INTO patient_info_log (event_code,uid,".$sIN.",update_datetime,update_by) VALUES(?,?".$sVal.",NOW(),'$sSid');";
			
			array_unshift($aObjData, $sUid);
			array_unshift($aObjData, $sMode);

			$sPrepare .= "s";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($sPrepare,...$aObjData);
			if($stmt->execute()){
				$AffRow =$stmt->affected_rows;
				if($AffRow > 0) $aRes["res"] = 1;
			}
			*/

		}else{
			$aRes["msg"]="Error UID can't create. Please try again";
		}




	}

	
}




if($isLog){
	//For relationship only
	include("in_log.php");
}
if(count($aLogData)>0 && $aRes["res"]=="1"){

	array_unshift($aLogData,$sSid);
	array_unshift($aLogData,$sNow);
	array_unshift($aLogData,$sMode);
	array_unshift($aLogData,$sUid);

	$query = "INSERT INTO patient_info_log (uid, event_code, update_datetime, update_by ".$sLogCol.") VALUES($sPreQ);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sPreS,...$aLogData);
	if($stmt->execute()){

	}
}

$mysqli->close();


//2nd Version
function getAllUIC($sFName,$sSName,$sDob){

	$aAllUic = array();
	$s1st = ""; $s2nd = "";
	$sThVowels="*&^%$#@!_/\\\";:+.(){}[]|ๆ?<>=-0123456789เแ์ๅะาิีืึุูำั้ ่๊๋็ฯํฯ'า,โใไ";
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