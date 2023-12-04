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

if($u_mode == "update_log_data"){ // form_data_update
  $formid = isset($_POST["formid"])?$_POST["formid"]:"";
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
//print_r($lst_data);

/*
$query = "UPDATE p_data_result SET data_result=?, lastupdate=?, s_id=?
WHERE data_id=? AND uid=?
AND CONCAT(collect_date, collect_time)=(select concat(collect_date, collect_time)
from p_data_log_row  where form_id=? and uid=? and row_id=?)
";
*/

$queryX = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id, data_result, lastupdate, s_id)
SELECT uid, collect_date, collect_time, ?, ?, NOW(), ?
FROM p_data_log_row WHERE uid=? AND form_id=? AND row_id=?
ON DUPLICATE KEY UPDATE lastupdate=NOW(), s_id=VALUES(s_id), data_result=VALUES(data_result);
";

//echo "query : $uid, $collect_date, $collect_time, $form_id/ $query";


$res=1;
foreach($lst_data as $itm){

  if($res){
    $stmtX = $mysqli->prepare($queryX);
    $arr_data = explode(':', $itm); // limit 3 first array
    $row_id = $arr_data[0];
    $data_id = $arr_data[1];
    $data_result = urldecode($arr_data[2]);
      $stmtX->bind_param("ssssss", $data_id, $data_result,$s_id, $uid, $formid, $row_id );
      if($stmtX->execute()){
        $affect_row = $stmtX->affected_rows;
        if($affect_row > 0){
          $res = 1;
          addToLog("update log [$uid|$formid|$row_id] $data_id:$data_result", $s_id);
        }else{
          $res = 0;
          error_log($data_id. $data_result. $s_id. $uid. $formid. $row_id );
        }
      }
      else{
        error_log($stmtX->error);
      }
      $stmtX->close();

    }
  }// foreach

  $rtn['lastupdate'] = date("Y-m-d H:i:s");

}

else if($u_mode == "add_row_log"){
  $formid = isset($_POST["formid"])?$_POST["formid"]:"";
  $visitid = isset($_POST["visitid"])?$_POST["visitid"]:"";
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";

  $rowid='';

/*
  $query = "INSERT INTO p_data_log_row (form_id, uid, collect_date, collect_time, row_id)
  SELECT ?, ?, now(), (IFNULL(max(collect_time),0)+1 ),
  @rowid :=  (IFNULL(max(row_id),0)+1) from p_data_log_row where form_id=? AND uid=?";
*/
/*
  $query = "INSERT INTO p_data_log_row (form_id, uid, collect_date, collect_time, row_id)
  SELECT ?, ?, now(), (IFNULL(max(collect_time)+0,0)+1 ),
  @rowid :=  (IFNULL(max(row_id),0)+1) from p_data_log_row where form_id=? AND uid=?";
*/
  $query = "INSERT INTO p_data_log_row (form_id, visit_id,  uid, collect_date, collect_time, row_id)
  SELECT ?,?, ?, now(), DATE_ADD(IFNULL(max(collect_time),STR_TO_DATE('00:00:00','%H:%i:%s')), INTERVAL 1 second),
  @rowid :=  (IFNULL(max(row_id),0)+1) from p_data_log_row where uid=?";



  //echo "query : $formid, $uid/ $query";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ssss",$formid,$visitid, $uid, $uid);
  if($stmt->execute()){
    $affect_row = $stmt->affected_rows;
    if($affect_row > 0){
      $res = 1;
      $inQuery = "SELECT @rowid;";
      $stmt = $mysqli->prepare($inQuery);
      $stmt->bind_result($rowid);
        if($stmt->execute()){
          if($stmt->fetch()){

          }
        }
    }


    }
  else{
    error_log($stmt->error);
  }
  $stmt->close();


  if($res == 1){
    if($rowid != '' || $rowid == NULL){
      $res = 1;
      if($rowid == NULL) $rowid='0';

      addToLog("add log [$uid|$formid|$rowid]", $s_id);
    }
  }

  $rtn['rowid'] = $rowid;
}

else if($u_mode == "delete_row_log"){
  $formid = isset($_POST["formid"])?$_POST["formid"]:"";
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $row_id = isset($_POST["rowid"])?$_POST["rowid"]:"";

/*
$query = "DELETE FROM p_data_result
  WHERE CONCAT(uid,collect_date,collect_time) IN (
  SELECT CONCAT(uid,collect_date, collect_time) FROM p_data_log_row
  WHERE row_id=? AND uid=? AND form_id=?)
  AND data_id IN (select data_id from p_form_list_data where form_id=?)
  ";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ssss",$row_id, $uid, $formid, $form_id);

    if($stmt->execute()){
    }
    else{
      error_log($stmt->error);
    }
    $stmt->close();
*/

    $log_coldate = ""; $log_coltime="";
    $inQuery = "SELECT collect_date, collect_time FROM p_data_log_row
    WHERE row_id=? AND uid=? AND form_id=?";
  //  error_log("$row_id, $uid, $formid / $inQuery");
    $stmt = $mysqli->prepare($inQuery);
    $stmt->bind_param("sss",$row_id, $uid, $formid);
    $stmt->bind_result($log_coldate, $log_coltime);
      if($stmt->execute()){
        if($stmt->fetch()){
        }
      }
      else{
        error_log($stmt->error);
      }
    $stmt->close();

    if($log_coldate != "" && $log_coltime != "" ){
      $affect_row = 0;
      $inQuery = "DELETE FROM p_data_result
      WHERE uid=? AND collect_date=? AND collect_time=?
      AND data_id IN (select data_id from p_form_list_data where form_id=?)
      ";
      //error_log("$uid, $log_coldate, $log_coltime,  $formid / $inQuery");
      $stmt = $mysqli->prepare($inQuery);
      $stmt->bind_param("ssss",$uid, $log_coldate, $log_coltime, $formid);
        if($stmt->execute()){
          $affect_row = $stmt->affected_rows;
          if($affect_row > 0){
            $res = 1;
            addToLog("delete log data result [$uid|$formid|$row_id|$log_coldate|$log_coltime]", $s_id);
          }
        }
        else{
          error_log($stmt->error);
        }
        $stmt->close();


        $query = "DELETE FROM p_data_log_row
          WHERE row_id=? AND uid=? AND form_id=?";
          $stmt = $mysqli->prepare($query);
          $stmt->bind_param("sss",$row_id, $uid, $formid);
          if($stmt->execute()){
            $affect_row = $stmt->affected_rows;
            if($affect_row > 0){
              $res = 1;
              addToLog("delete log row [$uid|$formid|$row_id]", $s_id);
            }
            else{
              $res = 0;
            }
          }
          else{
            error_log($stmt->error);
          }
          $stmt->close();


    }//if($log_coldate != "" && $log_coltime != "" )

}//delete_row_log
else if($u_mode == "update_timepoint"){
  $formid = isset($_POST["formid"])?$_POST["formid"]:"";
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $row_id = isset($_POST["rowid"])?$_POST["rowid"]:"";
  $timepoint_id = isset($_POST["timepointid"])?$_POST["timepointid"]:"";

  $query = "UPDATE p_data_log_row SET timepoint_id=?
    WHERE row_id=? AND uid=? AND form_id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssss",$timepoint_id, $row_id, $uid, $formid);
    if($stmt->execute()){
      $affect_row = $stmt->affected_rows;
      if($affect_row > 0){
        $res = 1;
        addToLog("update log timepoint: $timepoint_id [$uid|$formid|$row_id]", $s_id);
      }
      else{
        $res = 0;
      }
    }
    else{
      error_log($stmt->error);
    }
    $stmt->close();

}//update_timepoint






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
