<?
include("in_session.php");
include_once("in_php_function.php");


$s_id = getQS("s_id");
if($s_id == ''){
  if(isset($_SESSION["s_id"])){
    $s_id = $_SESSION["s_id"];
  }
}

$flag_auth=1;

$res = 0;
$msg_error = "";
$msg_info = "";
$returnData = "";

$u_mode = isset($_POST["u_mode"])?$_POST["u_mode"]:"";

if($flag_auth != 0){ // valid user session

include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

//echo "umode : $u_mode";
if($u_mode == "transfer_uid_data"){ // transfer_uid_data
  $uid1 = getQS("uid1"); // uid origin  (source of data)
  $uid2 = getQS("uid2"); // uid destination (transfer data to)

  $arr_sql_uid1 = array();
  $arr_sql_uid2 = array();

  $txt_info = "";
  $row_amt = 0;

  $query = "SELECT proj_id, uid, proj_group_id, pid, screen_date, enroll_date,
  uid_status, uid_remark, clinic_id, is_consent
  FROM p_project_uid_list
  WHERE uid=? AND uid_status IN ('1', '2')
  ORDER BY proj_id
  ";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('s',$uid1);
  //echo "query : $query";
  if($stmt->execute()){
    $stmt->bind_result($proj_id, $uid, $proj_group_id, $pid, $screen_date, $enroll_date,
    $uid_status, $uid_remark, $clinic_id, $is_consent);
  //  $stmt->store_result();
  //  $row_amt = $stmt->num_rows;
    while ($stmt->fetch()) {

       $arr_sql1 = array();
       $arr_sql1['sql'] = "UPDATE p_project_uid_list SET uid_status='10' WHERE uid='$uid' AND proj_group_id='$proj_group_id' ";
       $arr_sql1['info'] = "[$uid] Cancel pid:$pid|proj_id:$proj_id|group_id:$proj_group_id";
       $arr_sql_uid1[] = $arr_sql1;

       $arr_sql1['sql'] = "UPDATE patient_info SET remark=CONCAT('[Move to: $uid2]', ' ', remark) WHERE uid='$uid' ";
       $arr_sql1['info'] = "";
       $arr_sql_uid1[] = $arr_sql1;

       $arr_sql2 = array();
/*
       $arr_sql2['sql'] = "
       INSERT IGNORE INTO p_project_uid_list (proj_id, uid, proj_group_id, pid,
        screen_date, enroll_date,uid_status, uid_remark, clinic_id, is_consent) VALUES
       ('$proj_id','$uid2', '$proj_group_id', '$pid', '$screen_date', '$enroll_date',
      '$uid_status', '$uid_remark', '$clinic_id', '$is_consent')";
*/
      $arr_sql2['sql'] = "
      INSERT INTO p_project_uid_list (proj_id, uid, proj_group_id, pid,
       screen_date, enroll_date,uid_status, uid_remark, clinic_id, is_consent) VALUES
      ('$proj_id','$uid2', '$proj_group_id', '$pid', '$screen_date', '$enroll_date',
     '$uid_status', '$uid_remark', '$clinic_id', '$is_consent') On Duplicate Key
      Update uid_status=VALUES(uid_status)";

       $arr_sql2['info'] = "[$uid2] Insert pid:$pid|proj_id:$proj_id|group_id:$proj_group_id";
       $arr_sql_uid2[] = $arr_sql2;

       $arr_sql2['sql'] = "UPDATE patient_info SET remark=CONCAT('[Move from: $uid]', ' ', remark) WHERE uid='$uid2' ";
       $arr_sql2['info'] = "";
       $arr_sql_uid2[] = $arr_sql2;
    }// while
  }
  else{
    $msg_error .= $stmt->error;
  }
  $stmt->close();



    $query = "SELECT PUV.proj_id, PUV.uid, PUV.group_id, PUV.visit_id, PUV.schedule_date, PUV.visit_date,
      PUV.visit_main, PUV.visit_status, PUV.visit_clinic_id, PUV.visit_note, PUV.schedule_note
    FROM p_project_uid_visit PUV, p_project_uid_list PUL
    WHERE PUV.uid=? AND PUV.uid=PUL.uid AND PUV.proj_id=PUL.proj_id
    AND PUL.uid_status IN ('1', '2')
    ORDER BY PUV.uid, PUV.schedule_date
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s',$uid1);
    //echo "query : $query";
    if($stmt->execute()){
      $stmt->bind_result($proj_id, $uid, $group_id, $visit_id, $schedule_date, $visit_date,
      $visit_main, $visit_status,  $visit_clinic_id, $visit_note, $schedule_note);

      while ($stmt->fetch()) {
        $arr_sql2 = array();
        $arr_sql2['sql'] = "
        INSERT INTO p_project_uid_visit (proj_id, uid, group_id, visit_id, schedule_date, visit_date,
        visit_main, visit_status, visit_clinic_id, visit_note, schedule_note) VALUES
        ('$proj_id', '$uid2', '$group_id', '$visit_id', '$schedule_date', '$visit_date',
        '$visit_main', '$visit_status', '$visit_clinic_id', '$visit_note', '$schedule_note')
        On Duplicate Key Update visit_status=VALUES(visit_status)";

         $arr_sql2['info'] = "[$uid2] Insert Visit visit_id:$visit_id|proj_id:$proj_id|group_id:$proj_group_id";
         $arr_sql_uid2[] = $arr_sql2;
      }// while

      $arr_sql1 = array();
      $arr_sql1['sql'] = "UPDATE p_project_uid_visit SET visit_status='C' WHERE uid='$uid' ";
      $arr_sql1['info'] = "[$uid] Cancel All Visit";
      $arr_sql_uid1[] = $arr_sql1;

    }
    else{
      $msg_error .= $stmt->error;
    }
    $stmt->close();


        $query = "SELECT uid, collect_date, collect_time, data_id, data_result, lastupdate, s_id
        FROM p_data_result
        WHERE uid = ?
        ORDER BY collect_date
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s',$uid1);
        //echo "query : $query";
        if($stmt->execute()){
          $stmt->bind_result($uid, $collect_date, $collect_time, $data_id, $data_result, $lastupdate, $s_id);

          while ($stmt->fetch()) {
            $arr_sql1 = array();
            $arr_sql1['sql'] = "
            INSERT INTO p_data_result_log (uid, collect_date, collect_time, data_id, data_result, lastupdate, s_id) VALUES
            ('$uid', '$collect_date', '$collect_time', '$data_id', '$data_result', '$lastupdate', '$s_id')
            On Duplicate Key Update data_result=VALUES(data_result), lastupdate=VALUES(lastupdate), s_id=VALUES(s_id)";
             $arr_sql1['info'] = "";
             $arr_sql_uid1[] = $arr_sql1;

            $arr_sql2 = array();
            $arr_sql2['sql'] = "
            INSERT IGNORE INTO p_data_result (uid, collect_date, collect_time, data_id, data_result, lastupdate, s_id) VALUES
            ('$uid2', '$collect_date', '$collect_time', '$data_id', '$data_result', '$lastupdate', '$s_id')
            ";
             $arr_sql2['info'] = "";
             $arr_sql_uid2[] = $arr_sql2;
          }// while

          $arr_sql1 = array();
          $arr_sql1['sql'] = "DELETE FROM p_data_result WHERE uid='$uid' ";
          $arr_sql1['info'] = "[$uid] Delete data_result";
          $arr_sql_uid1[] = $arr_sql1;

        }
        else{
          $msg_error .= $stmt->error;
        }
        $stmt->close();


        $query = "SELECT uid, collect_date, collect_time, form_id, row_id
        FROM p_data_log_row
        WHERE uid = ?
        ORDER BY collect_date
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s',$uid1);
        //echo "query : $query";
        if($stmt->execute()){
          $stmt->bind_result($uid, $collect_date, $collect_time, $form_id, $row_id);

          while ($stmt->fetch()) {
            $arr_sql1 = array();
            $arr_sql1['sql'] = "
            INSERT IGNORE INTO p_data_log_row_log (uid, collect_date, collect_time, form_id, row_id) VALUES
            ('$uid1', '$collect_date', '$collect_time', '$form_id', '$row_id')
            ";
             $arr_sql1['info'] = "";
             $arr_sql_uid1[] = $arr_sql1;

            $arr_sql2 = array();
            $arr_sql2['sql'] = "
            INSERT IGNORE INTO p_data_log_row (uid, collect_date, collect_time, form_id, row_id) VALUES
            ('$uid2', '$collect_date', '$collect_time', '$form_id', '$row_id')
            ";
             $arr_sql2['info'] = "";
             $arr_sql_uid2[] = $arr_sql2;
          }// while

          $arr_sql1 = array();
          $arr_sql1['sql'] = "DELETE FROM p_data_log_row_log WHERE uid='$uid' ";
          $arr_sql1['info'] = "[$uid] Delete data_result";
          $arr_sql_uid1[] = $arr_sql1;

        }
        else{
          $msg_error .= $stmt->error;
        }
    $stmt->close();

  // lab data **************************************************************
  $arr_sql1 = array();
  $arr_sql1['sql'] = "UPDATE p_lab_order SET uid='$uid2' WHERE uid='$uid1'";
  $arr_sql1['info'] = "[$uid1] move p_lab_order to $uid2";
  $arr_sql_uid1[] = $arr_sql1;

  $arr_sql1 = array();
  $arr_sql1['sql'] = "UPDATE p_lab_order_lab_test SET uid='$uid2' WHERE uid='$uid1'";
  $arr_sql1['info'] = "[$uid1] move p_lab_order_lab_test to $uid2";
  $arr_sql_uid1[] = $arr_sql1;

  $arr_sql1 = array();
  $arr_sql1['sql'] = "UPDATE p_lab_order_specimen SET uid='$uid2' WHERE uid='$uid1'";
  $arr_sql1['info'] = "[$uid1] move p_lab_order_specimen to $uid2";
  $arr_sql_uid1[] = $arr_sql1;

  $arr_sql1 = array();
  $arr_sql1['sql'] = "UPDATE p_lab_result SET uid='$uid2' WHERE uid='$uid1'";
  $arr_sql1['info'] = "[$uid1] move p_lab_result to $uid2";
  $arr_sql_uid1[] = $arr_sql1;


  if(count($arr_sql_uid1) > 0){
    foreach($arr_sql_uid1 as $sql_uid){
      //error_log($sql_uid['sql']) ;
      $stmt = $mysqli->prepare($sql_uid['sql']);
      if($stmt->execute()){
        $affect_row = $stmt->affected_rows;
        if($affect_row > 0) $txt_info .= ($sql_uid['info'] != '')?$sql_uid['info'].'\n':"";
      }
      else error_log($stmt->error);

      $stmt->close();
    }//foreach
  }

  if(count($arr_sql_uid2) > 0){
    foreach($arr_sql_uid2 as $sql_uid){
      //error_log($sql_uid['sql']) ;
      $stmt = $mysqli->prepare($sql_uid['sql']);
      if($stmt->execute()){
        $affect_row = $stmt->affected_rows;
        if($affect_row > 0) $txt_info .= ($sql_uid['info'] != '')?$sql_uid['info'].'\n':"";
      }
      else{
        error_log($stmt->error);
      }

      $stmt->close();
    }//foreach

  }


  if($txt_info != ""){
    addToLog("Data transfer Info: $uid1 to $uid2 \n $txt_info", $s_id);
    addToLog("[$uid1] Data transfer: $uid1 to $uid2", $s_id);
    $res = 1;
  }
  $rtn['txtinfo'] = $txt_info;

}
else if($u_mode == "update_uic"){ // update uic
  $sUID = getQS("uid"); // uid
  $sUIC = getQS("uic"); // uic
  $sOld_UIC = getQS("olduic"); // uic

		$query = "UPDATE patient_info SET uic=? WHERE uid=? ";
	//echo "$sProjid, $sGroupid,$sUID, $sProjid, $sGroupid, $sVisitid / query: $query";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $sUIC, $sUID);
    if($stmt->execute()){
      $affect_row = $stmt->affected_rows;
      if($affect_row > 0) {
        $res = 1;
        addToLog("[$sUID] change UIC: $sOld_UIC to $sUIC", $s_id);
      }
    }
    $stmt->close();


}// update uic





$mysqli->close();

}//$flag_auth != 0

 // return object
 $rtn['res'] = $res;
 $rtn['mode'] = $u_mode;
 $rtn['msg_error'] = $msg_error;
 $rtn['msg_info'] = $msg_info;

 $rtn['flag_auth'] = $flag_auth;



 // change to javascript readable form
 $returnData = json_encode($rtn);
 echo $returnData;


 function fileuploadUpdate($file_upload){

 }
