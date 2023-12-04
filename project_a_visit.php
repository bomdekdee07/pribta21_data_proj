<?
/* Project Thumbnail list  */

include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");
include_once("project_a_visit_proj_option.php");

$uMode = getQS("u_mode");
$res = 0;
$msg_error = "";
$query ="";

$sSID = getSS("s_id");
if($sSID == "")  $sSID = getQS("s_id");


if($uMode == "create_schedule_visit"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");

  $dateEnroll = getToday();
	$txt_query_visit = ""; $txtLog = "";
	$query = "SELECT visit_id, visit_day
	FROM p_visit_list as v
	WHERE v.proj_id=? AND (v.group_id=? OR v.group_id='')
	AND visit_status=1 AND visit_order >= 0 AND visit_id <> 'FU'
	ORDER BY v.visit_order
	";
//echo "$sProjid, $sGroupid / query: $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss", $sProjid, $sGroupid);
	if($stmt->execute()){
		$stmt->bind_result($visit_id, $visit_day);
		while ($stmt->fetch()) {
      $schedule_date = addDayToDate($dateEnroll, $visit_day);
      $txt_query_visit .= "('$sUID', '$sProjid', '$sGroupid', '$visit_id', '$schedule_date', '1', '0'),";
      $txtLog .= "[$visit_id|$schedule_date]";
		}// if
	}
	else{
		$msg_error .= $stmt->error;
	  error_log("project_a_visit.php: ".$stmt->error);
	}
	$stmt->close();

	if($txt_query_visit != ''){
	//	echo $txt_query_visit;
		$txt_query_visit = substr($txt_query_visit,0,strlen($txt_query_visit)-1) ;
		$query =" INSERT INTO p_project_uid_visit (uid, proj_id, group_id, visit_id, schedule_date, visit_main, visit_status)
		VALUES $txt_query_visit
		";

	//	echo "<br>query $query";
		$stmt = $mysqli->prepare($query);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: Create visit schedule $txtLog" , $sSID);
				}
				$rtn["visit_amt"] = $affect_row;
			}

	}

}

else if($uMode == "checkin_visit"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sVisitid = getQS("visitid");

	$sVisitstatus = getQS("visitstatus");
	$sScheduledate = getQS("scheduledate");

	$sClinicid = getQS("clinicid");
	if($sClinicid == "")
	$sClinicid = isset($_SESSION["clinic_id"])? $_SESSION["clinic_id"]:"NA";

	$visitdate = date('Y-m-d');
	$count_visit = 0;

  if($sVisitstatus == "10"){
		$visitdate = "0000-00-00"; // set tp lost to visit
	}
	else{
		$query = "SELECT  count(uid) as visit_amt
		FROM p_project_uid_visit as PUV
		WHERE PUV.uid=? AND PUV.visit_date =? AND PUV.proj_id=? AND PUV.visit_status NOT IN('C', '10')
		";
	//error_log("$sUID,  $visitdate, $sProjid / query: $query") ;
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sUID,  $visitdate, $sProjid);
		if($stmt->execute()){
			$stmt->bind_result($count_visit);
			$row = 1;
			if ($stmt->fetch()) {
			}
		}
		$stmt->close();
	}

//	error_log("countvisit: $count_visit ");
  if($count_visit == 0){ // no duplicate visit date 

		$query ="UPDATE p_project_uid_visit SET visit_date=?, visit_status=?, visit_clinic_id=?
		WHERE uid=? AND proj_id=? AND group_id=? AND visit_id=? AND schedule_date=?
		";
	//echo "$visitdate, $sVisitstatus, $sClinicid, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssssssss",$visitdate, $sVisitstatus, $sClinicid, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: [$sVisitid] change visit_status=$sVisitstatus, visit_date=$visitdate", $sSID);
				}
			}
			$stmt->close();

      doProjectVisitOption($sProjid, $sVisitid, $sUID, $sSID);
	}
	else{
		$msg_error = "วันเข้านัดซ้ำกับที่มีอยู่ | Visit date is already exist.";
	}


		$rtn['visitdate'] = $visitdate;
}


else if($uMode == "create_new_fu_visit"){ // project new (extra,normal visit)
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sScheduleDate = getQS("scheduledate");

	$sVisitid = 'FU';
	$visitStatus = 0;
	$visit_date = "0000-00-00";


	$sClinicid = getQS("clinicid");
	if($sClinicid == "")
	$sClinicid = isset($_SESSION["clinic_id"])? $_SESSION["clinic_id"]:"NA";

  $duplicate_visit_id = "";
	$query ="SELECT visit_id
	FROM p_project_uid_visit
	WHERE uid=? AND proj_id=? AND (schedule_date=? OR visit_date=?) ORDER BY schedule_date, visit_date
	";
//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sUID, $sProjid,$sScheduleDate,$sScheduleDate );
		if($stmt->execute()){
			$stmt->bind_result($duplicate_visit_id);
			if($stmt->fetch()){
			}//if
		}
		$stmt->close();

  if($duplicate_visit_id == ""){// no duplicate schedule date
		$todaydate = date('Y-m-d');
		if($sScheduleDate == $todaydate) {
			$visit_date = $sScheduleDate;
			$visitStatus = 20;
		}

			$query ="INSERT INTO p_project_uid_visit
			(visit_id, visit_main, uid, proj_id, group_id, schedule_date, visit_date, visit_status, visit_clinic_id)
			VALUES('FU',0,?,?,?,?,?,?,?) On Duplicate Key
			Update uid=VALUES(uid),proj_id=VALUES(proj_id),group_id=VALUES(group_id),
			schedule_date=VALUES(schedule_date),
			visit_date=VALUES(visit_date),visit_status=VALUES(visit_status),visit_clinic_id=VALUES(visit_clinic_id)
			";

	//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssssss",$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: [$sVisitid] Make new visit $sScheduleDate|$visit_date", $sSID);
				}
			}
			$stmt->close();
	}
	else{
		$res=0;
		$msg_error = "สร้างนัดหมายไม่ได้เนื่องจากมีวันนัดหมาย (schedule date) หรือวันเข้านัดหมาย (visit date) ในวันที่ $sScheduleDate อยู่แล้วใน $duplicate_visit_id \r\n Can not create schedule date due to duplicate on visit: $duplicate_visit_id. ";
	}


		$rtn['visitdate'] = $visit_date;
		$rtn['visit_status'] = $visitStatus;
}

else if($uMode == "remove_fu_visit"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sScheduleDate = getQS("scheduledate");


	$sClinicid = getQS("clinicid");
	if($sClinicid == "")
	$sClinicid = isset($_SESSION["clinic_id"])? $_SESSION["clinic_id"]:"NA";
  $sVisitid = 'FU';


	$query ="DELETE FROM p_project_uid_visit
	WHERE uid=? AND proj_id=? AND schedule_date=? AND visit_id='FU'
	";
//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUID, $sProjid, $sScheduleDate);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
				addToLog("$sUID|$sProjid: [$sVisitid] Remove FU Visit $sScheduleDate", $sSID);
			}
		}
		$stmt->close();

}

else if($uMode == "remove_pid"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");

	$visit_amt = 0;  $visit_id=""; $visit_date="";
		$query ="SELECT visit_id, visit_date
		FROM p_project_uid_visit
		WHERE uid=? AND proj_id=? 
		ORDER BY schedule_date, visit_date
		";
	//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUID, $sProjid);
			if($stmt->execute()){
				$stmt->bind_result($visit_id, $visit_date);
				while($stmt->fetch()){
          $visit_amt++;
				}//while
			}
			$stmt->close();
  if($visit_amt < 0){
		$res=0;
		$msg_error = "Please remove all available schedule (except 1st schedule) before remove PID.";
	}
	else{ // delete related data of this pid
		  if($visit_amt > 0){
				$query ="DELETE FROM p_project_uid_visit
				WHERE uid=? AND proj_id=?
				";
			//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("ss",$sUID, $sProjid);
					if($stmt->execute()){
						$affect_row = $stmt->affected_rows;
						if($affect_row > 0){
							$res = 1;
							addToLog("$sUID|$sProjid: [$visit_id] Remove Visit $visit_date", $sSID);
						}
					}
					$stmt->close();


				$query ="DELETE FROM p_data_result
				WHERE uid=? AND collect_date=? AND collect_time='00:00:00' ";
			//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("ss",$sUID, $visit_date);
					if($stmt->execute()){
						$affect_row = $stmt->affected_rows;
						if($affect_row > 0){
							$res = 1;
							addToLog("$sUID|$sProjid: Remove data result in $visit_date", $sSID);
						}
					}
					$stmt->close();
			}//$visit_amt == 1

			$query ="DELETE FROM p_project_uid_list
			WHERE uid=? AND proj_id=?
			";
		//echo "$sUID, $sProjid,$sGroupid, $sScheduleDate, $visit_date,$visitStatus, $sClinicid / $query";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss",$sUID, $sProjid);
				if($stmt->execute()){
					$affect_row = $stmt->affected_rows;
					if($affect_row > 0){
						$res = 1;
						addToLog("$sUID|$sProjid: Remove PID", $sSID);
					}
				}
				$stmt->close();
		}
}
else  if($uMode == "change_schedule_date"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sVisitid = getQS("visitid");
	$sPreviousdate = getQS("previousdate");
	$sNewdate = getQS("newdate");

	$query ="UPDATE p_project_uid_visit SET schedule_date=?
	WHERE uid=? AND proj_id=? AND group_id=? AND visit_id=? AND schedule_date=?
	";
	//echo "$sNewScheduledate, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate / $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssssss",$sNewdate, $sUID, $sProjid,$sGroupid, $sVisitid, $sPreviousdate);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
				addToLog("$sUID|$sProjid|$sGroupid: [$sVisitid] change schedule date from $sPreviousdate to $sNewdate", $sSID);
			}
		}
		$stmt->close();

}

else  if($uMode == "change_visit_date"){ // change visit date (admin_mode)
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sVisitid = getQS("visitid");
	$sPreviousdate = getQS("previousdate");
	$sNewdate = getQS("newdate");
	$sRow = getQS("row"); // visit no.

	if($sRow == '1'){ // first visit  change to all schedule visit

	}
	$query ="UPDATE p_project_uid_visit SET visit_date=?
	WHERE uid=? AND proj_id=? AND group_id=? AND visit_id=? AND visit_date=?
	";
//	echo "$sNewdate, $sUID, $sProjid,$sGroupid, $sVisitid, $sPreviousdate / $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssssss",$sNewdate, $sUID, $sProjid,$sGroupid, $sVisitid, $sPreviousdate);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
				addToLog("$sUID|$sProjid|$sGroupid: [$sVisitid] change visit date from $sPreviousdate to $sNewdate", $sSID);
			}
		}
		$stmt->close();

    if($res == 1){
			$query ="UPDATE p_data_result SET collect_date=?
			WHERE uid=? AND collect_date=? AND collect_time='00:00:00' ";
			//echo "$sNewScheduledate, $sUID, $sProjid,$sGroupid, $sVisitid, $sScheduledate / $query";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss",$sNewdate, $sUID, $sPreviousdate);
				if($stmt->execute()){
					$affect_row_data = $stmt->affected_rows;
					if($affect_row_data > 0){
						addToLog("$sUID: [p_data_result] change collect_date from $sPreviousdate to $sNewdate", $sSID);
					}
				}
				$stmt->close();
		}


}
else  if($uMode == "delete_data_result"){ // delete only crf (admin_mode)
	$sUID = getQS("uid");
	$sVisitdate = getQS("visitdate");

	$query ="DELETE FROM p_data_result
	WHERE uid=? AND collect_date=? AND collect_time='00:00:00'
	";
//	echo "$sNewdate, $sUID, $sProjid,$sGroupid, $sVisitid, $sPreviousdate / $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss", $sUID, $sVisitdate);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
				addToLog("$sUID: [p_data_result] Delete data in $sVisitdate", $sSID);
			}
		}
		$stmt->close();
}


else  if($uMode == "update_visit_status"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$visit_id = getQS("visitid");
	$visit_date = getQS("visitdate");
	$status_id = getQS("status_id");



	if($status_id !=""){
		$query_add = "";
		if($status_id == '0') $query_add .=" , visit_date='0000-00-00' "; // เปลี่ยนสถานะเป็น รอเข้านัดหมาย (0)
		$query ="UPDATE p_project_uid_visit SET visit_status=? $query_add
		WHERE uid=? AND proj_id=? AND group_id=? AND visit_id=? AND visit_date=?
		";
	//	echo "$status_id, $sUID, $sProjid,$sGroupid, $visit_id, $visit_date  / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssssss",$status_id, $sUID, $sProjid,$sGroupid, $visit_id, $visit_date );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: [$visit_id|$visit_date] visit_status=$status_id", $sSID);
				}
			}
			$stmt->close();
	}
}

else if($uMode == "update_note"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$visit_id = getQS("visitid");
	$schedule_date = getQS("scheduledate");
	$notetype = getQS("notetype");
	$txtnote = getQS("txtnote");

	$col_update_note =  $notetype."_note";
		$query ="UPDATE p_project_uid_visit SET $col_update_note=?
		WHERE uid=? AND proj_id=? AND group_id=?  AND visit_id=? AND schedule_date=?";
		$stmt = $mysqli->prepare($query);
	  //echo "$visit_note, $sUID, $sProjid,$sGroupid ,$visit_id, $visit_date / $query";
		$stmt->bind_param("ssssss",$txtnote, $sUID, $sProjid,$sGroupid ,$visit_id, $schedule_date );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: [$visit_id|$schedule_date] Update $col_update_note", $sSID);
				}
			}
}



else if($uMode == "update_visit_note"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$visit_id = getQS("visitid");
	$visit_date = getQS("visitdate");
	$visit_note = getQS("visitnote");

		$query ="UPDATE p_project_uid_visit SET visit_note=?
		WHERE uid=? AND proj_id=? AND group_id=?  AND visit_id=? AND visit_date=?";
		$stmt = $mysqli->prepare($query);
	  //echo "$visit_note, $sUID, $sProjid,$sGroupid ,$visit_id, $visit_date / $query";
		$stmt->bind_param("ssssss",$visit_note, $sUID, $sProjid,$sGroupid ,$visit_id, $visit_date );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: [$visit_id|$visit_date] visit_note=$visit_note", $sSID);
				}
			}
}


else  if($uMode == "update_project_status"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$status_id = getQS("status_id");

	if($status_id !=""){
		$query ="UPDATE p_project_uid_list SET uid_status=?
		WHERE uid=? AND proj_id=? AND proj_group_id=?
		";
	//	echo "$status_id, $sUID, $sProjid,$sGroupid  / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$status_id, $sUID, $sProjid,$sGroupid );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: uid_status=$status_id", $sSID);
				}
			}
			$stmt->close();
	}
}
else if($uMode == "update_project_note"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sProjNote = getQS("proj_note");

		$query ="UPDATE p_project_uid_list SET uid_remark=?
		WHERE uid=? AND proj_id=? AND proj_group_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sProjNote, $sUID, $sProjid,$sGroupid );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID|$sProjid|$sGroupid: uid_remark=$sProjNote", $sSID);
				}
  }


}
else if($uMode == "update_form_done"){
	$sUID = getQS("uid");
	$sFormid = getQS("formid");
	$sVisitdate = getQS("visitdate");

		$query ="REPLACE INTO p_visit_form_done (uid, collect_date, form_id)
		VALUES(?,?,?)";
		//echo "$sUID, $sVisitdate,$sFormid  / $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sUID, $sVisitdate,$sFormid );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("$sUID [p_visit_form_done] $sFormid | $sVisitdate", $sSID);
				}
			}
			$stmt->close();

}
else if($uMode == "update_visit_id"){ // change visit id in schedule list by admin
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sVisitid = getQS("visitid");
	$sNewvisitid = getQS("newvisitid");
	$sScheduledate = getQS("scheduledate");


		$query ="UPDATE p_project_uid_visit SET visit_id=?
		WHERE uid=? AND proj_id=? AND visit_id=? AND schedule_date=?";
		//error_log("$sNewvisitid, $sUID,$sProjid, $sVisitid,$sScheduledate / $query");
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss",$sNewvisitid, $sUID,$sProjid, $sVisitid,$sScheduledate );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("[p_project_uid_visit] $sUID Change visit id $sVisitid to $sNewvisitid.", $sSID);
				}
			}
			$stmt->close();

}
else if($uMode == "undo_missing_visit"){ // undo missing visit ไม่มาตามนัด
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sClinicid = getQS("clinicid");
	$sScheduledate = getQS("scheduledate");
    $sNow = date('Y-m-d');
		$query ="UPDATE p_project_uid_visit SET visit_clinic_id='', visit_status='0'
		WHERE uid=? AND proj_id=? AND schedule_date=? AND visit_status='10' ";
		//error_log("$sNewvisitid, $sUID,$sProjid, $sVisitid,$sScheduledate / $query");
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss", $sUID,$sProjid, $sScheduledate);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("[p_project_uid_visit] $sUID Undo missing visit : $sScheduledate.", $sSID);
				}
			}
			$stmt->close();

}
else if($uMode == "uid_clinic_transfer"){ // change to other clinic by admin
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sClinicid = getQS("clinicid");
    $sNow = date('Y-m-d');
    $uid_remark = "Transfer to clinic: $sClinicid [$sNow]";
		$query ="UPDATE p_project_uid_list SET clinic_id=?, uid_remark=CONCAT(uid_remark, '\n','$uid_remark')
		WHERE uid=? AND proj_id=?";
		//error_log("$sNewvisitid, $sUID,$sProjid, $sVisitid,$sScheduledate / $query");
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sClinicid, $sUID, $sProjid);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) {
					$res = 1;
					addToLog("[p_project_uid_list] $sUID Change clinic id to $sClinicid.", $sSID);
				}
			}
			$stmt->close();

}



  $no_echo = getQS('no_echo');

	/*
		if($mysqli->ping()){
			$mysqli->close();
		}
		$no_close = getQS('no_close');
		if($no_close !='1') $mysqli->close();
	*/


	if(get_object_vars($mysqli)["sqlstate"]!=""){
   $mysqli->close();
  }

	if($no_echo != '1'){
		$rtn["res"] = $res;
		$rtn["msg_error"] = $msg_error;
		$returnData = json_encode($rtn);
		echo $returnData;
	}


?>
