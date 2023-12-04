<?
include("in_session.php");
include_once("in_php_function.php");


/*
$s_id = getQS("s_id");
if($s_id == ''){
  if(isset($_SESSION["s_id"])){
    $s_id = $_SESSION["s_id"];
  }
}
*/

$s_id = getSS("s_id");
if($s_id == ''){
  $s_id = getQS("s_id");
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

$proj_id = getQS("projid");

//echo "umode : $u_mode";
if($u_mode == "form_data_update"){ // form_data_update
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $collect_date = isset($_POST["collect_date"])?$_POST["collect_date"]:"";
  $collect_time = isset($_POST["collect_time"])?$_POST["collect_time"]:"";
  $lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];

  $form_id = isset($_POST["formid"])?$_POST["formid"]:"";
  $form_done = isset($_POST["form_done"])?$_POST["form_done"]:"";
  //$s_id = isset($_POST["s_id"])?$_POST["s_id"]:$s_id;
//error_log(print_r($lst_data));
$lastupdate = date("Y-m-d H:i:s");
foreach($lst_data as $item){
    foreach($item as $data_id=>$data_result){

          $query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id, data_result, lastupdate, s_id, proj_id)
          VALUES(?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE lastupdate=?, s_id=?, data_result=?;
          ";
          //error_log( "query : $uid, $collect_date, $collect_time, $data_id, $data_result, $lastupdate, $s_id/ $query");
          $stmt = $mysqli->prepare($query);
          $stmt->bind_param("sssssssssss", $uid, $collect_date, $collect_time, $data_id, $data_result, $lastupdate, $s_id, $proj_id, $lastupdate, $s_id, $data_result);
          if($stmt->execute()){
            $affect_row = $stmt->affected_rows;
            if($affect_row > 0){
              $res = 1;
              addToLogDataResult($s_id, $uid, $collect_date, $collect_time,$form_id." ".$proj_id, $data_id, $data_result, $lastupdate );
              //addToLog("update [$uid] $data_id:$data_result", $s_id);
            }
          }
          else{
            error_log($stmt->error);
          }
          $stmt->close();
          $rtn['lastupdate'] = $lastupdate;
    }
  } // foreach

  if($form_done != ""){
    $query = "INSERT INTO p_data_form_done
     (uid,collect_date,collect_time,form_id, is_done, record_datetime, update_datetime, update_by)
     VALUES(?,?,?,?,?,now(),now(),?)
     ON DUPLICATE KEY UPDATE is_done=VALUES(is_done), update_datetime=VALUES(update_datetime), update_by=VALUES(update_by)
     ";
  //  error_log( "query :$uid, $collect_date, $collect_time, $form_id,$form_done,  $s_id/ $query");
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssss", $uid, $collect_date, $collect_time, $form_id,
    $form_done,  $s_id);
    if($stmt->execute()){
      $affect_row = $stmt->affected_rows;
      if($affect_row > 0){
        addToLog("update form [$uid] $form_id:$form_done", $s_id);
      }
    }

  }// formdone
}
else if($u_mode == "get_import_formdata"){ // form_data_update
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $collect_date = isset($_POST["collect_date"])?$_POST["collect_date"]:"";
  $collect_time = isset($_POST["collect_time"])?$_POST["collect_time"]:"";
  $form_id = isset($_POST["formid"])?$_POST["formid"]:"";

  $query = "  SELECT  PDR.data_id, PDR.data_result
  FROM p_form_list_data as PLD, p_data_result as PDR
  WHERE PDR.data_id = PLD.data_id AND PDR.collect_time <> '00:00:00' AND
  PDR.uid=? AND PDR.collect_date=? AND PLD.form_id=?
  ORDER BY PDR.data_id
  ";

  $arr_data = array();
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('sss',$uid,$collect_date,$form_id);
  if($stmt->execute()){
    $stmt->bind_result($data_id ,$data_result);
    while ($stmt->fetch()) {
        $arr_data[$data_id] = $data_result;
    }//while
  }
  $stmt->close();
  $rtn['datalist'] = $arr_data;
  $res = 1;

}//get_import_formdata
else if($u_mode == "get_import_labdata"){ // lab_data_update in form
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $collect_date = isset($_POST["collect_date"])?$_POST["collect_date"]:"";
  $collect_time = isset($_POST["collect_time"])?$_POST["collect_time"]:"";


  $query = "SELECT LR.lab_id, LR.lab_result
  FROM p_lab_order as LO
  JOIN p_lab_result as LR ON
  LR.uid=LO.uid AND LR.collect_date=LO.collect_date AND LR.collect_time=LO.collect_time
  AND (LR.time_confirm != 'NULL' || LR.time_confirm != '')
  WHERE LO.uid=? AND LO.collect_date=? AND LO.collect_time=?
  ORDER BY LR.lab_id, LR.collect_date asc
  ";

  $arr_data = array();
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('sss',$uid,$collect_date,$collect_time);
  if($stmt->execute()){
    $stmt->bind_result($data_id ,$data_result);
    while ($stmt->fetch()) {
        $arr_data[$data_id] = $data_result;
    }//while
  }
  $stmt->close();
  $rtn['datalist'] = $arr_data;
  $res = 1;

}//get_import_formdata

else if($u_mode == "get_import_labdata_logform"){ // lab_data_update in log form
  $uid = isset($_POST["uid"])?$_POST["uid"]:"";
  $sColdate = isset($_POST["collect_date"])?$_POST["collect_date"]:"";
  $sProjid = isset($_POST["projid"])?$_POST["projid"]:"";
  $sVisitid = isset($_POST["visitid"])?$_POST["visitid"]:"";
  $sFormid = isset($_POST["formid"])?$_POST["formid"]:"";

  $labID_in = "";
  $arr_data = array(); $arr_log_row = array();
  $queryinsert = "";
    // check log row data
    $query = "SELECT collect_time, timepoint_id
    FROM p_data_log_row
    WHERE form_id=? AND uid=?  AND visit_id=?
    ORDER BY collect_time asc
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss',$sFormid,$uid, $sVisitid);
    if($stmt->execute()){
      $stmt->bind_result($collect_date, $timepoint_id);
      while ($stmt->fetch()) {
          $arr_log_row[$collect_date] =$timepoint_id;
      }//while
    }
    $stmt->close();

  if(count($arr_log_row) > 0){
      // check related lab id
      $query = "  SELECT LR.lab_id, LR.lab_result, LO.timepoint_id
      FROM p_lab_order as LO
      JOIN p_lab_result as LR ON
      LR.uid=LO.uid AND LR.collect_date=LO.collect_date AND LR.collect_time=LO.collect_time
      AND (LR.time_confirm != 'NULL' || LR.time_confirm != '')
      WHERE LO.uid=? AND LO.proj_id=? AND LO.proj_visit=?
      ORDER BY LR.lab_id, LR.collect_date asc
      ";
//error_log("$uid,$sColdate,$sProjid, $sVisitid / $query");
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('sss',$uid,$sProjid, $sVisitid);
      if($stmt->execute()){
        $stmt->bind_result($data_id ,$data_result, $timepoint_id);
        while ($stmt->fetch()) {
          //  if($timepoint_id='0')$timepoint_id='';
            $arr_data["$data_id:$timepoint_id"] = $data_result;
            $labID_in .= "'$data_id',";
        }//while
      }
      $stmt->close();
      if($labID_in != ""){
        $labID_in = substr($labID_in,0, strlen($labID_in)-1);

          $query = "SELECT data_id
          FROM p_form_list_data
          WHERE form_id=? AND data_id IN($labID_in)
          ORDER BY data_id asc
          ";

//error_log("$sFormid / $query");
          $stmt = $mysqli->prepare($query);
          $stmt->bind_param('s',$sFormid);
          if($stmt->execute()){
            $stmt->bind_result($data_id);
            while ($stmt->fetch()) {
               foreach($arr_log_row as $log_coltime => $log_timepoint){
                 //error_log("$data_id log : $log_coltime => $log_timepoint");
                   if($log_timepoint == '0' || $log_timepoint == '0Day') $log_timepoint='';
                   if(isset($arr_data["$data_id:$log_timepoint"])){
                    // error_log("isset : $data_id - $log_timepoint");
                      $lab_result = $arr_data["$data_id:$log_timepoint"];
                      $queryinsert .= "('$uid', '0000-00-00','$log_coltime', '$data_id','$lab_result','$s_id','$proj_id'),";
                   }
               }//foreach

            }//while
          }
          $stmt->close();
          if($queryinsert != ""){
            $queryinsert = substr($queryinsert,0, strlen($queryinsert)-1);

            $queryinsert = "INSERT INTO p_data_result
            (uid, collect_date, collect_time, data_id, data_result, s_id, proj_id)
            VALUES $queryinsert ON DUPLICATE KEY UPDATE
            data_result=values(data_result), s_id=values(s_id)
            ";

/*
            $queryinsert = "INSERT IGNORE INTO p_data_result
            (uid, collect_date, collect_time, data_id, data_result, s_id)
            VALUES $queryinsert ";
*/
            //error_log("query2: $queryinsert");
            $stmt = $mysqli->prepare($queryinsert);
            if($stmt->execute()){
              $affect_row = $stmt->affected_rows;
              if($affect_row > 0){
                $res = 1;
                addToLog("Import lab log row [$uid|$sProjid|$sVisitid] $sFormid|amt:$affect_row", $s_id);
              }else{
                error_log("ERROR: Import lab log row [$uid|$sProjid|$sVisitid] $sFormid");
                $msg_error = 'Import Data 0 row.';
              }
            }
            else{
              error_log($stmt->error);
              $msg_error .= " ".$stmt->error;
            }
            $stmt->close();
          }
          else{
            $msg_error = 'No Lab import';
          }
      }
      else{
        $msg_error = 'No related lab test found to insert log form.';
      }
  }
  else{
    $msg_error = 'No log row to insert data.';
  }

  $rtn['datalist'] = $arr_data;

}//get_import_labdata_logform



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
