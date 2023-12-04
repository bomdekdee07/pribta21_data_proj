<?
include_once("in_php_function.php");
$aRes=array();
$sToday=date("Y-m-d");
$isEcho =getQS("echo");

function getSeed(){
	$seed = str_split('abcdefghijklmnopqrstuvwxyz'
	                     .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
	                     .'0123456789'); // and any other characters
	shuffle($seed); // probably optional since array_is randomized; this may be redundant
	$rand = '';
	foreach (array_rand($seed, 20) as $k) $rand .= $seed[$k];
	$milliseconds = round(microtime(true) * 1000);	
	$mix_key = $rand.''.$milliseconds;
	return $mix_key;
}
//Start convert_base64_create_profile_proc.php

$sQ = getQS("q");
$sUid = getQS("uid");
$aRes["res"] = "0";
$isCont = true;
$sColDate = "";
$sColTime = "";

if($sUid=="" || $sQ==""){
	
	$aRes["msg"] = "No data provided.";
	$isCont = false;
}

include("in_db_conn.php");
if($isCont){
	//Find if the uid is already bind with other q;
	$query = "SELECT queue FROM k_visit_data WHERE date_of_visit = ? AND uid=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sToday,$sUid);
	$stmt->execute();
	$stmt->bind_result($queue);
	while ($stmt->fetch()) {
		//Queue Already Exist just return Error Message
		$aRes["msg"] = "UID already bind with queue : ".$queue;
		$isCont = false;
	}
}
if($isCont){
	//Find if the queue is already bind with other uid;
	$query = "SELECT uid FROM k_visit_data WHERE date_of_visit = ? AND queue=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sToday,$sQ);
	$stmt->execute();
	$stmt->bind_result($uid);
	while ($stmt->fetch()) {
		//Queue Already Exist just return Error Message
		$aRes["msg"] = "Queue already bind with uid : ".$uid;
		$isCont = false;
	}
}



$sStupidId = "";
if($isCont){
	//Check if queue was created by stupid Dol program yet?
	$query = "SELECT KQRDH.time_record,KQRD.id FROM k_queue_row_detail KQRD 
	JOIN k_queue_row KQR
	ON KQR.id = KQRD.queue_row_id
	JOIN k_queue_row_detail_history KQRDH ON KQRD.id = KQRDH.from_qrd_id 

	WHERE KQRD.queue_row_detail = ? AND KQR.time_record > ?
	ORDER BY KQRDH.time_record LIMIT 1;";

	//UPDATE `k_queue_row_detail` SET patient_uid='P20-11911' WHERE id = @stupiddol;";
	
	$stmt = $mysqli->prepare($query);
	//$stmt->bind_param($sParam,...$aUpdData);
	$stmt->bind_param("ss",$sQ,$sToday);
	if($stmt->execute()){
		$stmt->bind_result($sTime,$fuckingID);
		while ($stmt->fetch()) {
			$sStupidId = $fuckingID;
			$aTime = explode(" ",$sTime);
			$sColDate = $aTime[0];
			$sColTime = $aTime[1];
		}
	}
	if($sColDate=="") {
		$isCont = false;
		$aRes["msg"] = $sQ." : Queue is not created yet. Please go to print Queue ticket first.";
	}
}

if($isCont){

	if($sStupidId !=""){
		$query = "UPDATE `k_queue_row_detail` SET patient_uid=? WHERE id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUid,$sStupidId);
		if($stmt->execute()){

		}

		//Now insert into stupid idiot k_booking

		$query = "INSERT INTO k_booking (appointment_by,appointment_date,appointment_time,
		appointment_q,p2_name,p2_lastname,p2_idcard,p2_sex,p2_email,p2_gender,p2_mobile,p2_birthday,
		p2_address,p2_province,p2_district,p2_zone,p2_postal_code,token,
		status,p2_uid,p2_uic,p2_date_come,p2_date_regis,p2_dob,p2_newuser,p2_telephone,time_record)
		SELECT uid,?,?,?,fname,sname,citizen_id,sex,email,gender,tel_no,date_of_birth,id_address,id_province,id_district,id_zone,id_postal_code,?,1,uid,uic,?,?,date_of_birth,1,tel_no,NOW()
		FROM patient_info WHERE uid=?";
		$sSeed = getSeed();
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssssss",$sColDate,$sColTime,$sQ,$sSeed,$sColDate,$sColTime,$sUid);
		if($stmt->execute()){
			$AffRow =$stmt->affected_rows;
			if($AffRow > 0) {
				//SaveSuccess
				$isCont = true;
			}else{
				$isCont = false;
			}
		}
		//End convert_base64_create_profile_proc.php


		$isFound = false; $iVisit=1;
		$query = "SELECT date_of_visit,time_of_visit,queue FROM k_visit_data WHERE uid=?";
		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("s",$sQ);
		$sDupQ = "";
		if($stmt->execute()){
			$stmt->bind_result($sTime,$fuckingID,$queue);

			while ($stmt->fetch()) {
				if($date_of_visit == $sColDate) {
					$isFound=true; 
					$sDupQ=$queue;
				}
				else $iVisit++;
			}
		}

		if($isFound){
			$aRes["res"] = "0";
			$aRes["msg"] = "UID already bind with Q : ".$sDupQ;
		}else{
			$query = "INSERT INTO k_visit_data (uid,visit_number,date_of_visit,time_of_visit,queue,time_record)
			VALUES (?,?,?,?,?,NOW());";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssss",$sUid,$iVisit,$sColDate,$sColTime,$sQ);
			if($stmt->execute()){
				$AffRow =$stmt->affected_rows;
				if($AffRow > 0) {
					//SaveSuccess
					$aRes["res"] = "1";
				}else{
					$aRes["res"] = "0";
					$aRes["msg"] = "Error create k_visit_data";
				}
			}
		}
	}
}

$mysqli->close();

$returnData = json_encode($aRes);
if($isEcho!="0") echo($returnData);

?>