<?
include("in_session.php");
include_once("in_php_encode.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$aRes=array();
$isEcho=getQS("echo");
include("array_post.php");
$sToday = date("Y-m-d");
$sClinicId=getSS("clinic_id");
$sSid=getSS("s_id");
$sMode = getQS("u_mode");
if($sMode==""){
	$returnData = json_encode($aRes);
	if($isEcho!="0") echo($returnData);
	exit();
}

include("in_db_conn.php");

if($sMode=="room_add"){

	$query = "INSERT INTO i_room_list (".$sInsCol.") VALUES (".$sInsVal.");";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
	  	$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;		
			$aRes["msg"] = getRoomRow($aPost["clinic_id"],$aPost["room_no"],$aPost["room_name"],$aPost["room_detail"],$aPost["room_status"],$aPost["section_id"],$aPost["default_room"]);
		}else{
			$aRes["res"] = 0;
			$aRes["msg"] = "Duplicate Key";
		}
	}
}else if($sMode=="room_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE i_room_list SET ".$sUpdSet." WHERE ".$sUpdWhere;


		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}else if($sMode=="room_del"){

	$sClinicId = getQS("clinicid");
	$sRoomNo = getQS("roomno");
	if($sClinicId=="" || $sRoomNo==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "ClinicId or RoomNo is not provide";
	}else{
		$query = "DELETE FROM i_room_list WHERE clinic_id=? AND room_no=?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sClinicId,$sRoomNo);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}

}else if($sMode=="room_enter"){
	$aRes["res"]="0";
	if($sSid==""){
		
		$aRes["res"]="Session Expired.";

	}else{
		$sRoom=getQS("roomno");
		$isCF = getQS("iscf");

		$sRoomName = "";
		$sCurRoomOwner = "";
		$sCurRoomStatus = "";

		//Double check if the room is available and not occupied by other. Get Room Name too
		$query = "SELECT IRL.room_detail,s_id,IRLOG.room_status FROM i_room_list IRL LEFT JOIN i_room_login IRLOG ON IRLOG.room_no = IRL.room_no AND IRLOG.clinic_id = IRL.clinic_id AND visit_date=? WHERE IRL.clinic_id=? AND IRL.room_no=?;";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sToday,$sClinicId,$sRoom);
		if($stmt->execute()){
			$stmt->bind_result($room_detail,$s_id,$room_status);
			while ($stmt->fetch()) {
				$sRoomName=$room_detail;
				$sCurRoomOwner=$s_id;
				$sCurRoomStatus=$room_status;
			}
		}

		if($sCurRoomOwner==$sSid || $isCF && !is_null($sCurRoomOwner)){
			//Same user just update time and status
			$query = "UPDATE i_room_login SET s_id=?,room_status=1,staff_logdate=NOW() WHERE clinic_id=? AND room_no=? AND visit_date=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ssss",$sSid,$sClinicId,$sRoom,$sToday);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					$aRes["res"] = "1";
					$_SESSION["room_no"] = $sRoom;
				}else{
					$aRes["res"] = "0";
					$aRes["msg"] = "Room is occupied by other staff";
				}
			}
		}else if(is_null($sCurRoomOwner)){
			//No Row Add yet. just add it.
			$query = "INSERT INTO i_room_login(clinic_id,room_no,visit_date,s_id,staff_logdate,room_status) VALUES(?,?,?,?,NOW(),1);";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ssss",$sClinicId,$sRoom,$sToday,$sSid);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					$aRes["res"] = "1";
				}else{
					$aRes["msg"] = "1 second late. Please try again.";
				}
			}
		}else{
			$aRes["msg"] = "Somethings wrong. Please try again.";
		}
		if($aRes["res"]=="1"){
			//Update Log
			$_SESSION["room_no"]=$sRoom;
			$_SESSION["room_detail"]=$sRoomName;
  			$query = "INSERT INTO i_room_login_log(clinic_id,room_no,visit_date,s_id,staff_logdate,room_status) VALUES(?,?,?,?,NOW(),1);";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ssss",$sClinicId,$sRoom,$sToday,$sSid);
			if($stmt->execute()){

			}
		}	
	}

}else if($sMode=="room_exit"){
	$aRes["res"] = "0";
	$aRes["msg"] = "You not in the room";
	$sRoom=getSS("room_no");
	
	$query = "UPDATE i_room_login SET room_status=0,staff_logdate=NOW() WHERE clinic_id=? AND room_no=? AND visit_date=? AND s_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sClinicId,$sRoom,$sToday,$sSid);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$_SESSION["room_no"] = "";
			$aRes["res"] = "1";
			$aRes["msg"] = "";

		}else{
			$aRes["msg"] = "Can't find the room you are login.";
		}
	}



	if($aRes["res"]=="1"){
		//Update Log
		$_SESSION["room_no"]="";
		$_SESSION["room_detail"]="";
		$query = "INSERT INTO i_room_login_log(clinic_id,room_no,visit_date,s_id,staff_logdate,room_status) VALUES(?,?,?,?,NOW(),0);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sClinicId,$sRoom,$sToday,$sSid);
		if($stmt->execute()){

		}
	}	
	
}else if($sMode=="room_forward_old"){
	$aRes["res"] = "0";
	$aRes["msg"] = "You not in the room";
	//$sRoom=getSS("room_no");
	
	$fwdRoom = getQS("fwdroom");
	$sQ = getQS("q");
	$isError = false;
	$sSaleOpt = getQS("saleid");
	$sSaleTxt = getQS("saletxt");
	$sNoteToAll = getQS("notetoall");

	if($sQ=="" || $fwdRoom==""){
		$aRes["msg"] = "Room and Q is not provided.";
		$isError = true;
	}

	if(!$isError){
		$qrdId = "";
		$query = "SELECT id FROM k_queue_row_detail WHERE time_record > '$sToday' AND queue_row_detail=? ORDER BY time_record DESC LIMIT 1;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sQ);
		if($stmt->execute()){
			$stmt->bind_result($id);
			while ($stmt->fetch()) {
				$qrdId=$id;
			}	
		}

		$query = "INSERT INTO k_queue_row_detail_history(from_qrd_id,id_room,time_record)
		VALUES(?,?,NOW())";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$qrdId,$fwdRoom);
		if($stmt->execute()){
		}		

		$query = "UPDATE k_queue_row_detail SET first_call='3', sale_opt_id=?,sale_opt_id_detail=? 
		,note_to_all=?
		WHERE id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sSaleOpt,$sSaleTxt,$sNoteToAll,$qrdId);
		if($stmt->execute()){
		}	

		$query = "INSERT INTO k_data_jaaey(from_qrd_id,id_room,id_who,time_record)
		VALUES (?,?,?,NOW());";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$qrdId,$fwdRoom,$sSid);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = "1";	
				$aRes["msg"] = "";
			}else{
				$aRes["res"] = "0";	
				$aRes["msg"] = "Can't Insert into jaaey pong table";
			}


		}

	}
}

$mysqli->close();



$returnData = json_encode($aRes);
if($isEcho!="0") echo($returnData);


?>