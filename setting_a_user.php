<?
include("in_session.php");
include_once("in_php_encode.php");
include_once("in_php_function.php");
include("in_setting_row.php");

$sMode=getQS("u_mode");
$sSid=getSS("s_id");
$sStaffId=getQS("pid");
$sResult=array();
$isEcho=getQS("isecho");
$sTable="p_staff";

if(($sStaffId=="" || $sSid == "" || $sMode=="") && $sMode!="change_password" && $sMode!="mail_reset_password") {
	$sResult["res"]="0";
	$sResult["msg"]="Not all data are supplied";
	$returnData = json_encode($sResult);
	if($isEcho!="0") echo($returnData);
	exit();
}


include("in_db_conn.php");

if($sMode=="user_add"){
	$sName = getQS("name");
	$sEmail = getQS("email");
	$sPhone = getQS("phone");
	$sStatus = getQS("status");
	$sLiLab = getQS("lilab");
	$sRemark = getQS("remark");


	$query ="INSERT INTO p_staff(s_id,s_name,s_remark,s_email,s_tel,s_status,license_lab) VALUES(?,?,?,?,?,?,?);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssssss",$sStaffId,$sName,$sRemark,$sEmail,$sPhone,$sStatus,$sLiLab);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$sResult["msg"] = getUserRow($sStaffId,$sName,$sEmail,$sPhone,$sStatus,$sLiLab,$sRemark);

			/* Add Log
				$query = "INSERT INTO i_room_log(clinic_id,room_number,s_id,log_datetime) VALUES(?,?,?,NOW());";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss",$sClinic,$sRoom,$sSid);
				if($stmt->execute()){

				}
			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}else if($sMode=="user_update"){
	$sName = getQS("name");
	$sNameEn = getQS("nameen");
	$sEmail = getQS("email");
	$sPhone = getQS("phone");
	$sLiLab = getQS("lilab");
	$sLiMd = getQS("limd");
	$sStatus = getQS("status");
	$sRemark = getQS("remark");

	$sCol ="s_name|s_name_en|s_remark|s_email|s_tel|s_status|license_lab|license_md";
	$sColVal = $sName."|".$sNameEn."|".$sRemark."|".$sEmail."|".$sPhone."|".$sStatus."|".$sLiLab."|".$sLiMd;
	$sColW = "s_id";
	$sColWVal = $sStaffId;

	$query ="UPDATE p_staff
	SET s_name= ?,s_name_en=?,s_remark= ?,s_email= ?,s_tel= ?,s_status= ?,license_lab = ?,license_md=?
	WHERE s_id = ?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("sssssssss",$sName,$sNameEn,$sRemark,$sEmail,$sPhone,$sStatus,$sLiLab,$sLiMd,$sStaffId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$query = "INSERT INTO i_system_log(s_id,log_module,log_event,input_col,input_val,where_col,where_val,log_datetime) VALUES(?,?,?,?,?,?,?,NOW());";

			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssssss",$sSid,$sTable,$sMode,$sCol,$sColVal,$sColW,$sColWVal);
			if($stmt->execute()){}
			$sResult["s_name"]=$sName;
			$sResult["s_name_en"]=$sNameEn;
			$sResult["s_remark"]=$sRemark;
			$sResult["s_email"]=$sEmail;
			$sResult["s_tel"]=$sPhone;
			$sResult["s_status"]=$sStatus;
			$sResult["license_lab"]=$sLiLab;
			$sResult["license_md"]=$sLiMd;

		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}else if($sMode=="reset_password"){


	$sPass1=getQS("p1");
	$sPass2=getQS("p2");
	$sKey = "";
	if($sPass1!=$sPass2 || $sPass1 == ""){
		$sResult["res"] = "0";
		$sResult["msg"] = "Incomplete data";
	}else{

		$sKey = encodeSingleLink($sPass1);
		$query ="UPDATE p_staff
		SET s_pwd = ?
		WHERE s_id = ?;";
		$stmt = $mysqli->prepare($query);

		$stmt->bind_param("ss",$sKey,$sStaffId);
		if($stmt->execute()){
			$iAff = $mysqli->affected_rows;
			if($iAff > 0){
				$sResult["res"] = 1;

			}else{
				$sResult["res"] = "0";
				$sResult["msg"] = "No row update. Please try again with different password.";
			}
		}

	}
}else if($sMode=="change_password"){

	$sPassCur=getQS("pcur");
	$sPass1=getQS("p1");
	$sPass2=getQS("p2");
	$sKey = "";
	if($sPass1!=$sPass2 || $sPass1 == ""){
		$sResult["res"] = "0";
		$sResult["msg"] = "Password and Confirm Password doesn't match.";
	}else{

		$sKey = encodeSingleLink($sPass1);
		$sCur = encodeSingleLink($sPassCur);
		$query ="UPDATE p_staff
		SET s_pwd = ?
		WHERE s_id = ? AND s_pwd=?;";
		$stmt = $mysqli->prepare($query);

		$stmt->bind_param("sss",$sKey,$sSid,$sCur);
		if($stmt->execute()){
			$iAff = $mysqli->affected_rows;
			if($iAff > 0){
				$sResult["res"] = 1;

			}else{
				$sResult["res"] = "0";
				$sResult["msg"] = "Incorrect Password.";
			}
		}

	}
}else if($sMode=="delete_clinic_auth"){
	$sClinicId = getQS("clinicid");

	$query ="DELETE FROM i_staff_clinic WHERE clinic_id=? AND s_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicId,$sStaffId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}else if($sMode=="update_clinic_auth"){
	$sClinicId = getQS("clinicid");
	$sSecId = getQS("secid");
	$sStatus = getQS("status");

	$query ="INSERT INTO i_staff_clinic(s_id,clinic_id,section_id,sc_status,create_date) VALUES(?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE sc_status=VALUES(sc_status);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sStaffId,$sClinicId,$sSecId,$sStatus);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			/* Add Log
				$query = "INSERT INTO i_room_log(clinic_id,room_number,s_id,log_datetime) VALUES(?,?,?,NOW());";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss",$sClinic,$sRoom,$sSid);
				if($stmt->execute()){
				}
			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}else if($sMode=="copy_clinic_auth"){
	$sClinicId = getQS("clinicid");
	$sSource = getQS("sourceid");

	/*
	$query="DELETE FROM i_staff_clinic WHERE s_id=? AND clinic_id=?";
		$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sStaffId,$sClinicId);
	if($stmt->execute()){}
	*/

	$query ="INSERT INTO i_staff_clinic(s_id,clinic_id,section_id,sc_status,create_date)
	SELECT s_id,?,section_id,sc_status,NOW() FROM i_staff_clinic WHERE s_id=? AND clinic_id=? ON DUPLICATE KEY UPDATE sc_status=VALUES(sc_status)";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinicId,$sStaffId,$sSource);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}
else if($sMode=="mail_reset_password"){
	$sID=getQS("s_id"); $msg_info=""; $res='0';
	$query ="SELECT s_email, s_name FROM p_staff
	WHERE s_id=? ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sID);
	if($stmt->execute()){
		$stmt->bind_result($s_email, $s_name);
		if ($stmt->fetch()) {
		}
	}
	$stmt->close();
    if($s_email != ''){
		$string = 'abcdefghijkmnpqrstuvwxyz123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$string_shuffled = str_shuffle($string);
		$new_password = substr($string_shuffled, 0, 6);
		$s_password_encode = encodeSingleLink($new_password);
		$query ="UPDATE p_staff SET s_pwd=?
		WHERE s_id=? AND s_email=?
			";
		//echo "$visitdate, $sVisitstatus, $sClinicid, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$s_password_encode, $sID, $s_email);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
				include_once('in_php_mail.php');
				$email_subject = "Pribta Clinic User Account";
				$email_message = "
				เรียน คุณ$s_name <br>
				<p>
				ท่านสามารถเข้าระบบได้โดยใช้ข้อมูลดังนี้ <br>
				Email: $s_email <br>
				Password: $new_password <br>
				<small><i>*โดยท่านสามารถเปลี่ยน Password ได้เองในระบบ (ไอคอนรูปกุญแจ)</i></small>
				</p>
				<p>
				โดยสามารถเข้าระบบผ่านลิ้งค์
				<a href='http://161.82.242.164/pribta21/'>Pribta Clinic System</a>
				</p>
				ขอบคุณครับ<br>
				<i>อีเมลฉบับนี้เป็นการแจ้งข้อมูลของระบบ IHRI  <u>กรุณาอย่าตอบกลับ</u></i>
				";

				$rtn = sendEmail($email_subject, $email_message, $emailListTO=array("$s_email"=>"$s_name"));
				$res = $rtn['res'];
				$msg_info = $rtn['msg_info'];


			}
		}
		$stmt->close();
	}

    if($res == '1'){
		$log_msg = "mail reset password [$sID|$s_email]";
		$query ="INSERT INTO a_log_cmd (sql_cmd, update_user) VALUES (?,?)
		";
		//echo "$visitdate, $sVisitstatus, $sClinicid, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$s_password_encode, $sSid);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;
				}
			}
		}

		$sResult['res'] = $res;
		$sResult['msg_info'] = $msg_info;

}// reset password


$mysqli->close();


$returnData = json_encode($sResult);
if($isEcho!="0") echo($returnData);


?>
