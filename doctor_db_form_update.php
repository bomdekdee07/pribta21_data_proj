<?
include_once("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");

$s_idUpdate = getSS("s_id");

$flag_auth=1;
$msg_error = "";
$msg_info = "";
$returnData = "";
$u_mode = "form_data_update";
$tbl_name = "p_form_list";

if($flag_auth != 0){
  function selectData($tbl_name, $select_field, $lst_where_data_item, $query_add, $order_by){
    global $mysqli; // db
  
    global $msg_error;
    $arr_data_list = array();
    $str_where = "";
  
    foreach ($lst_where_data_item as $col => $value){
      $str_where .= " $col = '$value' AND ";
    }
    if($str_where != ""){
      $str_where = substr($str_where,0,strlen($str_where)-4);
      $str_where = " WHERE $str_where ";
    }
    else {
      if(trim($query_add) != "") $str_where = " WHERE $query_add ";
    }
  
    $order_by = ($order_by !="")?" ORDER BY $order_by ":"" ;
    $query = "SELECT $select_field FROM $tbl_name $str_where $order_by ";
    $stmt = $mysqli->prepare($query);
    //echo "query : $query";
    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $arr_data_list[] = $row;
      }
    }
    else{
      $msg_error .= $stmt->error;
    }
    $stmt->close();
  
    return $arr_data_list;
  }
  
  function selectDataSql($sqlCmd){
    global $mysqli; // db
    global $msg_error;
  
    $arr_data_list = array();
    $stmt = $mysqli->prepare($sqlCmd);
    //echo "query : $sqlCmd";
  
    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $arr_data_list[] = $row;
      }
    }
    else{
      $msg_error .= $stmt->error;
    }
    $stmt->close();
  
    return $arr_data_list;
  }
  
  function selectCount($tbl_name, $col_id, $lst_where_data_item, $query_add){
    global $mysqli; // db
  
    global $msg_error;
    $arr_data_list = array();
    $str_where = "";
    $count=0;
    foreach ($lst_where_data_item as $col => $value){
      $str_where .= " $col = '$value' AND ";
    }
    if($str_where != ""){
      $str_where = substr($str_where,0,strlen($str_where)-4);
      $str_where = " WHERE $str_where ";
    }
  
    $query = "SELECT count($col_id) FROM $tbl_name $str_where";
    $stmt = $mysqli->prepare($query);
    //echo "query : $query";
  
    if($stmt->execute()){
      $stmt->bind_result($count);
      if ($stmt->fetch()) {
  
      }
    }
    else{
      $msg_error .= $stmt->error;
    }
    $stmt->close();
  
    return $count;
  }
  
  function updateListDataObj($tbl_name, $lst_data_item){
    //print_r($lst_data_item);
    global $mysqli; // db
    global $msg_error;
    $s_idUpdate = getSS("s_id");
  
    $flag_success = true;
    $col_insert = "";
    $col_update = "";
    $col_value = "";
    $colume_val = "";
    $colume_val_id = "";
    $sid_log = "DoctorMain";

    foreach ($lst_data_item as $col => $value){
      if($col == "data_old"){
        if($value != ""){
          $colume_val = $value;
        }
        else{
          $colume_val = null;
        }
      }
    }

    foreach ($lst_data_item as $col => $value){
      if($col == "data_old_id"){
        if($value != ""){
          $colume_val_id = $value;
        }
        else{
          $colume_val_id = null;
        }
      }
    }
    // echo $colume_val."/".$colume_val_id;
  
    foreach ($lst_data_item as $col => $value){
      // echo "TESTLL:"."$col / $value"."<br>";
      if($col != "data_old"){
        if($col != "data_old_id"){
          $col_insert .= $col.",";
          $col_value .= "'".($colume_val_id == $col? ($colume_val != null? $colume_val : $value) : $value)."',";
          $col_update .= $col."='".$value."',";
        }
      }
    }
  
    $col_insert = ($col_insert !="")?substr($col_insert,0,strlen($col_insert)-1):"" ;
    $col_update = ($col_update !="")?substr($col_update,0,strlen($col_update)-1):"" ;
    $col_value = ($col_value !="")?substr($col_value,0,strlen($col_value)-1):"" ;
  
    if($col_value != ""){
      $query = "INSERT INTO $tbl_name ($col_insert)
      VALUES ($col_value) On Duplicate Key
      Update $col_update";
      // echo $query;
      $stmt = $mysqli->prepare($query);
  
      if($stmt->execute()){}
      else{
        $flag_success = false;
        $msg_error .= $stmt->error; //error จะบอกตรงนี้ ถ้า duplicate kry
      }
      $stmt->close();
  
      $sql_cmd = "update:[$tbl_name] $col_update";
      $query = "INSERT INTO a_log_cmd (update_user, sql_cmd) VALUES(?, ?)";
      $stmt = $mysqli->prepare($query);

      // echo "query: $query";
      $stmt->bind_param('ss',$s_idUpdate,$sql_cmd);

      if($stmt->execute()){
      }
      else{
        $msg_error .= $stmt->error;
      }
      $stmt->close();
  
    }// if($col_value != "")
  
    return $flag_success;
  }
  
  // delete
  function deleteListDataObj($tbl_name,$lst_where_data_item){
      global $mysqli; // db
      global $msg_error;
      $col_delete = "";
      $s_id = "delete_off_appointment";
  
      $str_where = "";
      foreach ($lst_where_data_item as $col => $value){
        $str_where .= " $col = '$value' AND ";
      }
  
      if($str_where != ""){
        $str_where = substr($str_where,0,strlen($str_where)-4);
        $str_where = " WHERE $str_where ";
  
        $query = "DELETE FROM $tbl_name $str_where";
        // echo "query: $query";
        $stmt = $mysqli->prepare($query);
        if($stmt->execute()){}
        else{
          $msg_error .= $stmt->error;
        }
        $stmt->close();
      }
  
      $sql_cmd = "delete:[$tbl_name] $str_where";
      $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
      VALUES(?, ?)";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('ss', $s_id ,$sql_cmd);
      if($stmt->execute()){
  
      }
      else{
        $msg_error .= $stmt->error;
      }
      $stmt->close();
  }
  
  function addToLog($msgInfo){
    global $mysqli; // db
    global $msg_error;
  
    $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
    VALUES(?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s',$msgInfo);
    if($stmt->execute()){
    }
    else{
      $msg_error .= $stmt->error;
    }
    $stmt->close();
  }

  function changeToThaiDate($dateData){
    $dateVal = explode("-", $dateData);
    $dateVal[0] = $dateVal[0] + 543;
    $value = $dateVal[2]."/".$dateVal[1]."/".$dateVal[0];
    return $value;
  }

  // get Date from DB and change to date format d-M-y
  function getDBDate($dateData){
    $txtDate = "";
    if($dateData != "0000-00-00" || $dateData != "0000-00-00 00:00:00"){
      $txtDate = (new DateTime($dateData))->format('d M y');
    }
    return $txtDate;
  }

  function getDBTime($dateData){
    $txtDate = "";
    if($dateData != "0000-00-00 00:00:00"){
      $txtDate = (new DateTime($dateData))->format('H:i');
    }

    return $txtDate;
  }

  function getDBDateThai($dateData){
    $txtDate = "";
    if($dateData != "0000-00-00" || $dateData != "0000-00-00 00:00:00"){
      // Thai Month
      $mtn_arr = array();
      $mtn_arr[] = "ทั้งหมด";
      $mtn_arr[] = "ม.ค.";
      $mtn_arr[] = "ก.พ.";
      $mtn_arr[] = "มี.ค.";
      $mtn_arr[] = "เม.ย.";
      $mtn_arr[] = "พ.ค.";
      $mtn_arr[] = "มิ.ย.";
      $mtn_arr[] = "ก.ค.";
      $mtn_arr[] = "ส.ค.";
      $mtn_arr[] = "ก.ย.";
      $mtn_arr[] = "ต.ค.";
      $mtn_arr[] = "พ.ย.";
      $mtn_arr[] = "ธ.ค.";

      $num_date = (int) (new DateTime($dateData))->format('d');
      $num_month = (int) (new DateTime($dateData))->format('m');
      $thai_year = (int)(new DateTime($dateData))->format('Y') + 543;
      $txtDate = $num_date." ".$mtn_arr[$num_month]." $thai_year";
    }
    return $txtDate;
  }

  function getDBDateTime($dateData){
    $txtDate = "";
    if($dateData != "0000-00-00 00:00:00"){
      $txtDate = (new DateTime($dateData))->format('d M y H:i:s');
    }
    return $txtDate;
  }

  function getToday(){
    $txtDate = (new DateTime())->format('Y-m-d');
    return $txtDate; 
  }

  function getDateToString($dateObj){
    $txtDate = $dateObj->format('Y-m-d');
    return $txtDate;
  }

  $u_mode = isset($_POST["app_mode"])?$_POST["app_mode"] : "form_data_update";
  // echo "MODE:".$u_mode."<br>";

  if($u_mode == "form_data_update"){ // form_data_update
    $uid = isset($_POST["uid"])?$_POST["uid"]:"";
    $collect_date = isset($_POST["coldate"])?$_POST["coldate"]:"";
    $collect_time = isset($_POST["coltime"])?$_POST["coltime"]:"";
    $sid = isset($_POST["sid"])?$_POST["sid"]:"";
    $proj_id = "";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"]:[];
    // $param_s_id = isset($_POST["s_id"])?$_POST["s_id"]:"";

    //print_r($data_obj_list);
    $flag_auth=1;

    $tbl_name = "p_data_result";
    if($flag_auth == 1){
      foreach($data_obj_list as $data_obj) {
        $lst_data_update = array();
        $lst_data_update["uid"] = "$uid";
        $lst_data_update["collect_date"] = "$collect_date";
        $lst_data_update["collect_time"] = "$collect_time";
        $lst_data_update["proj_id"] = $proj_id;
        $lst_data_update["s_id"] = "$sid";
        // $lst_data_update["s_id"] = "$param_s_id";
        $queue_note = "";

        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update["data_id"] = "$data_id";
          $lst_data_update["data_result"] = "$data_result";

          if($data_id == "cn_patient_note"){
            $queue_note = $data_result;
          }
        }

        updateListDataObj($tbl_name, $lst_data_update);
        
        // update i_queue_list: queue_note
        if($queue_note != ""){
          $bind_param = "ssss";
          $array_val = array($queue_note, $uid, $collect_date, $collect_time);

          $ud_queue_list = "UPDATE i_queue_list 
          SET queue_note = ? 
          WHERE uid = ?
          and collect_date = ?
          and collect_time = ?";

          $stmt = $mysqli->prepare($ud_queue_list);
          $stmt->bind_param($bind_param, ...$array_val);

          if($stmt->execute()){}
          else{
            $flag_success = false;
            $msg_error .= $stmt->error; //error จะบอกตรงนี้ ถ้า duplicate kry
          }
          $stmt->close();
        }
      }
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }
  
  // Appointment
  if($u_mode == "appointments"){ // form_data_update
    $mode_save = isset($_POST["mode_save"])?$_POST["mode_save"]:"";
    $uid = isset($_POST["uid"])?$_POST["uid"]:"";
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $is_confirm = isset($_POST["is_confirm"])?$_POST["is_confirm"] : "0";
    $s_id = isset($_POST["s_id"])?$_POST["s_id"]:"";
    if($s_id == "patient" || $s_id == "")
      $s_id = "ANYONE";
    $app_date = isset($_POST["app_date"])?$_POST["app_date"]:"";
    $app_date_old = isset($_POST["app_date_old"])?$_POST["app_date_old"]:"";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : []; 
    $update_dateTime = date("Y-m-d H:i:s");   
    $app_time = getQS("app_time");
    $app_time_old = getQS("app_time_old");
    // echo "SID:".$s_id;

    // echo "confirm:".$is_confirm;
    // print_r($data_obj_list);
    $flag_auth=1;

    $tbl_name = "i_appointment";
    if($flag_auth == 1){
      $lst_data_update = array();
      foreach($data_obj_list as $data_obj) {
        $lst_data_update["uid"] = $uid;
        $lst_data_update["clinic_id"] = $clinic_id;
        $lst_data_update["is_confirm"] = $is_confirm;
        $lst_data_update["s_id"] = $s_id;
        $lst_data_update["appointment_date"] = $app_date;
        $lst_data_update["updated_date"] = $update_dateTime;
        $lst_data_update["updated_by"] = $s_idUpdate;

        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }

      // INSERT
      if($mode_save == "insert"){
        // updateListDataObj($tbl_name, $lst_data_update);
        $flag_success = true;
        $col_insert = "";
        $col_value = "";
        $colume_val = "";
        $colume_val_id = "";
        $sid_log = "Appoinment";

        foreach ($lst_data_update as $col => $value){
          if($col == "data_old"){
            if($value != ""){
              $colume_val = $value;
            }
            else{
              $colume_val = null;
            }
          }
        }

        foreach ($lst_data_update as $col => $value){
          if($col == "data_old_id"){
            if($value != ""){
              $colume_val_id = $value;
            }
            else{
              $colume_val_id = null;
            }
          }
        }
        // echo $colume_val."/".$colume_val_id;

        $bind_param = "ssss";
        $array_val = array($app_date, $uid, $clinic_id, $s_id);
        $data_count_uid = 0;

        $query_check = "SELECT count(*) AS c_data 
        FROM i_appointment 
        WHERE appointment_date = ?
        and uid = ? 
        and clinic_id = ?
        and s_id = ?;";

        $stmt = $mysqli->prepare($query_check);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
          $stmt->bind_result($c_data);
          while($stmt->fetch()){
            $data_count_uid = $c_data;
          }
        }

        $stmt->close();

        $bind_param = "ssss";
        $array_val = array($app_date, $uid, $clinic_id, $app_time);
        $data_count_sid = 0;

        $query_check2 = "SELECT count(*) AS sid_check
        FROM i_appointment 
        WHERE appointment_date = ?
        and uid = ?
        and clinic_id = ?
        and appointment_time = ?;";

        $stmt = $mysqli->prepare($query_check2);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
          $stmt->bind_result($sid_check);
          while($stmt->fetch()){
            $data_count_sid = $sid_check;
          }
        }
        $stmt->close();
      
        if($data_count_uid == 0 && $data_count_sid == 0){
          foreach ($lst_data_update as $col => $value){
            // echo "TESTLL:"."$col / $value"."<br>";
            if($col != "data_old"){
              if($col != "data_old_id"){
                $col_insert .= $col.",";
                $col_value .= "'".($colume_val_id == $col? ($colume_val != null? $colume_val : $value) : $value)."',";
              }
            }
          }
        
          $col_insert = ($col_insert !="")?substr($col_insert,0,strlen($col_insert)-1):"" ;
          $col_value = ($col_value !="")?substr($col_value,0,strlen($col_value)-1):"" ;
        
          if($col_value != ""){
            $query = "INSERT INTO $tbl_name ($col_insert)
            VALUES ($col_value)";
            // echo $query;
            $stmt = $mysqli->prepare($query);
        
            if($stmt->execute()){}
            else{
              $flag_success = false;
              $msg_error .= $stmt->error; //error จะบอกตรงนี้ ถ้า duplicate kry
            }
            $stmt->close();
          }
        }
        else{
          $flag_success = false;
          $msg_error = "have dup uid";
        }
        // return $flag_success;
      }
      // UPDATE  
      else if($mode_save == "update"){
        $flag_success = true;
        $col_insert = "";
        $col_update = "";
        $col_value = "";
        $col_where = "";
        $colume_val = "";
        $colume_val_id = "";
        $sid_log = "Appoinment";
        $data_check_time = 0;

        $bind_param = "ssss";
        $array_val = array($app_date, $uid, $clinic_id, $app_time);

        $query_check2 = "SELECT count(*) AS sid_check
        FROM i_appointment 
        WHERE appointment_date = ?
        and uid = ?
        and clinic_id = ?
        and appointment_time = ?;";

        $stmt = $mysqli->prepare($query_check2);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
          $stmt->bind_result($sid_check);
          while($stmt->fetch()){
            $data_check_time = $sid_check;
          }
        }
        $stmt->close();

        if($app_time_old == $app_time){
          $data_check_time = 0;
        }

        if($data_check_time == 0){
          foreach ($lst_data_update as $col => $value){
            // echo "TESTLL:"."$col / $value"."<br>";
            if($col != "data_old"){
              if($col != "data_old_id"){
                $col_update .= $col."='".$value."',";
              }
            }
          }
        
          $col_update = ($col_update !="")?substr($col_update,0,strlen($col_update)-1):"" ;
          $col_where = "uid ='".$uid."' and clinic_id ='".$clinic_id."' and is_confirm ='".$is_confirm."' and s_id ='".$s_id."' and appointment_date ='".$app_date_old."'";

          if($col_update != ""){
            $query = "UPDATE ".$tbl_name."
            SET ".$col_update."
            where ".$col_where.";";
            // echo $query;

            $stmt = $mysqli->prepare($query);
        
            if($stmt->execute()){}
            else{
              $flag_success = false;
              $msg_error .= $stmt->error; //error จะบอกตรงนี้ ถ้า duplicate kry
            }
            $stmt->close();
        
            $sql_cmd = "update:[$tbl_name] $col_update";
            $query = "INSERT INTO a_log_cmd (update_user, sql_cmd) VALUES(?, ?)";
            $stmt = $mysqli->prepare($query);

            // echo "query: $query";
            $stmt->bind_param('ss',$sid_log,$sql_cmd);

            if($stmt->execute()){
            }
            else{
              $msg_error .= $stmt->error;
            }
            $stmt->close();

            // return $flag_success;
          }
        }
        else{
          $flag_success = false;
          $msg_error = "have dup uid";
        }
      }
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }

  // HOLIDAY
  if($u_mode == "holiday"){
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $date_res = isset($_POST["date_res"])?$_POST["date_res"] : "";
    $s_id = isset($_POST["sid"])?$_POST["sid"] : "none";
    $check_data_old = isset($_POST["data_old"])?$_POST["data_old"] : "";
    $check_data_old_id = isset($_POST["data_old_id"])?$_POST["data_old_id"] : "";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    if($s_id == ""){
      $s_id = "none";
    }

    $flag_auth=1;

    $tbl_name = "i_holiday";
    $lst_data_update = array();
    if($flag_auth == 1){
      
      $lst_data_update["clinic_id"] = $clinic_id;
      $lst_data_update["data_old"] = $check_data_old;
      $lst_data_update["data_old_id"] = $check_data_old_id;
      $lst_data_update["holiday_date"] = $date_res;
      $lst_data_update["s_id"] = $s_id;

      foreach($data_obj_list as $data_obj) {
        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }
      // print_r($lst_data_update);
      updateListDataObj($tbl_name, $lst_data_update);
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }
  if($u_mode == "delete_holiday"){
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $date_res = isset($_POST["date_res"])?$_POST["date_res"] : "";
    $s_id = isset($_POST["sid"])?$_POST["sid"] : "none";

    if($s_id == ""){
      $s_id = "none";
    }

    $flag_auth=1;

    $tbl_name = "i_holiday";
    $lst_data_update = array();
    if($flag_auth == 1){
      
      $lst_data_update["clinic_id"] = $clinic_id;
      $lst_data_update["holiday_date"] = $date_res;
      $lst_data_update["s_id"] = $s_id;

      // print_r($lst_data_update);
      deleteListDataObj($tbl_name, $lst_data_update);
    }
  }

  // DOCUMENT
  if($u_mode == "document"){
    $doc_code = isset($_POST["doc_code"])?$_POST["doc_code"] : "";
    $date_time = isset($_POST["doc_datetime"])?$_POST["doc_datetime"] : "";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    $flag_auth=1;

    $tbl_name = "i_doc_list";
    $lst_data_update = array();
    if($flag_auth == 1){      
      $lst_data_update["doc_code"] = $doc_code;
      $lst_data_update["doc_datetime"] = $date_time;

      foreach($data_obj_list as $data_obj) {
        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }
      // print_r($lst_data_update);
      updateListDataObj($tbl_name, $lst_data_update);
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }

  // SUPPLY GROUP TYPE
  if($u_mode == "supply_group_type"){ // form_data_update
    $group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"]:"";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    // echo "confirm:".$is_confirm;
    //print_r($data_obj_list);
    $flag_auth=1;

    $tbl_name = "i_stock_type";
    if($flag_auth == 1){
      foreach($data_obj_list as $data_obj) {
        $lst_data_update = array();
        $lst_data_update["supply_group_type"] = $group_type;

        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
        // print_r($lst_data_update);

        updateListDataObj($tbl_name, $lst_data_update);
      }
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }
  if($u_mode == "deleted_supply_group_type"){
    $group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"]:"";

    $flag_auth=1;

    $tbl_name = "i_stock_type";
    $lst_data_update = array();
    if($flag_auth == 1){
      
      $lst_data_update["supply_group_type"] = $group_type;

      // print_r($lst_data_update);
      deleteListDataObj($tbl_name, $lst_data_update);
    }
  }

  // SUB GROUP
  if($u_mode == "supply_sub_group"){ // form_data_update
    $group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"]:"";
    $supply_group_code = isset($_POST["supply_group_code"])?$_POST["supply_group_code"]:"";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    // echo "confirm:".$is_confirm;
    //print_r($data_obj_list);
    $flag_auth=1;

    $tbl_name = "i_stock_group";
    if($flag_auth == 1){
      foreach($data_obj_list as $data_obj) {
        $lst_data_update = array();
        $lst_data_update["supply_group_type"] = $group_type;
        $lst_data_update["supply_group_code"] = $supply_group_code;

        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
        // print_r($lst_data_update);

        updateListDataObj($tbl_name, $lst_data_update);
      }
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }
  if($u_mode == "deleted_sub_group"){
    $group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"]:"";
    $supply_group_code = isset($_POST["supply_group_code"])?$_POST["supply_group_code"]:"";

    $flag_auth=1;

    $tbl_name = "i_stock_group";
    $lst_data_update = array();
    if($flag_auth == 1){
      
      $lst_data_update["supply_group_type"] = $group_type;
      $lst_data_update["supply_group_code"] = $supply_group_code;

      // print_r($lst_data_update);
      deleteListDataObj($tbl_name, $lst_data_update);
    }
  }

  // SUPPLY Request
  if($u_mode == "supply_request"){ // form_data_update
    $request_id = isset($_POST["request_id"])?$_POST["request_id"]:"";
    $mode_save = isset($_POST["mode_save"])?$_POST["mode_save"]:"";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    // echo "confirm:".$is_confirm;
    //print_r($data_obj_list);
    $flag_auth=1;

    $tbl_name = "i_stock_request_list";
    if($flag_auth == 1){
      $lst_data_update = array();
      $lst_data_update["request_id"] = $request_id;
      $lst_data_update["request_datetime"] = date("Y-m-d h:i:s");
      if($mode_save == "true"){
        $lst_data_update["request_status"] = '00';
      }

      foreach($data_obj_list as $data_obj) {
        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }
      // print_r($lst_data_update);
      updateListDataObj($tbl_name, $lst_data_update);
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }

  // MONIOR QUEUE
  if($u_mode == "monitor_queue"){
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $queue = isset($_POST["queue"])?$_POST["queue"] : "";
    $collect_date = isset($_POST["collect_date"])?$_POST["collect_date"] : "";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    $flag_auth=1;

    $tbl_name = "i_queue_list";
    $lst_data_update = array();
    if($flag_auth == 1){      
      $lst_data_update["clinic_id"] = $clinic_id;
      $lst_data_update["queue"] = $queue;
      $lst_data_update["collect_date"] = $collect_date;
      $lst_data_update["queue_type"] = 1;

      foreach($data_obj_list as $data_obj) {
        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }
      // print_r($lst_data_update);
      updateListDataObj($tbl_name, $lst_data_update);
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }

  // BILL CUSTOM
  if($u_mode == "bill_custom"){
    $uid = isset($_POST["uid"])?$_POST["uid"] : "";
    $addrtitle = isset($_POST["addrtitle"])?$_POST["addrtitle"] : "";
    $data_obj_list = isset($_POST["dataid"])?$_POST["dataid"] : [];

    $flag_auth=1;

    $tbl_name = "j_bill_custom";
    $lst_data_update = array();
    if($flag_auth == 1){      
      $lst_data_update["uid"] = $uid;
      $lst_data_update["bill_title"] = $addrtitle;

      foreach($data_obj_list as $data_obj) {
        foreach($data_obj as $data_id => $data_result) {
          $lst_data_update[$data_id] = $data_result;
        }
      }
      // print_r($lst_data_update);
      updateListDataObj($tbl_name, $lst_data_update);
    }
  }
  else if($u_mode == "select_form_detail"){ // select_form_detail
      $id = isset($_POST["id"])?$_POST["id"]:"";
      //selectData($tbl_name, "*", $lst_where_data_item, $query_add, $order_by){
      $arr_where = array("form_id"=>"$id");
      $arr_data_list = selectData("p_form_list_data", "*", $arr_where, "", "data_seq");
      $rtn['data_obj_list'] =$arr_data_list[0];
  }

  

  // RETURN VAL
  $uid_send = isset($_POST["uid"])?$_POST["uid"]: "";
  $collect_date_select = isset($_POST["coldate"])?$_POST["coldate"]:"";
  $collect_time_select = isset($_POST["coltime"])?$_POST["coltime"]:"";

  $queue_send = "";
  if($uid_send != ""){
    $query = "select queue from i_queue_list
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    and clinic_id = 'IHRI';";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss',$uid_send, $collect_date_select, $collect_time_select); // echo "query : $query";
    
    if($stmt->execute()){
        $stmt->bind_result($queue);
        while ($stmt->fetch()) {
            if(!isset($d_data_result[$data_id]))
                $queue_send = $queue;
        }
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
  }

  // Close database
  $mysqli->close();
}

// return object
$rtn['mode'] = $u_mode;
$rtn['msg_error'] = $msg_error;
$rtn['msg_info'] = $msg_info;
$rtn['flag_auth'] = $flag_auth;
if($queue_send != ""){
  $rtn['uid_rtn'] = $uid_send;
  $rtn['queue_rtn'] = $queue_send;
}

// change to javascript readable form
$returnData = json_encode($rtn);
echo $returnData;

function addFormComponent($form_id, $data_type, $data_value){
  //print_r($lst_data_item);
  global $mysqli; // db
  global $msg_error;

  $rtnObj = array();
  $id_prefix = $form_id."_C"; // prefix & current year eg IH20
  $substr_pos_begin = 1+strlen($id_prefix);
  $where_substr_pos_end = strlen($id_prefix);
  $id_digit = 4; // 0001-9999

  $inQuery = "INSERT INTO p_form_list_data (data_id, data_seq,
  data_type, data_value, form_id)
  SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(data_id) IS NULL,0,MAX(data_id)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
    ,@seq_no := (select IF(MAX(data_seq) IS NULL,0,MAX(data_seq)) +10 from p_form_list_data where form_id='$form_id') ,
    '$data_type', '$data_value', '$form_id'
  FROM p_form_list_data WHERE SUBSTRING(data_id,1,$where_substr_pos_end) = '$id_prefix';
  ";
   //echo $inQuery;

  $stmt = $mysqli->prepare($inQuery);

  if($stmt->execute()){
    $inQuery = "SELECT @keyid, @seq_no;";
    $stmt = $mysqli->prepare($inQuery);
    $stmt->bind_result($data_id, $data_seq);
      if($stmt->execute()){
        if($stmt->fetch()){
          $rtnObj = array("data_id"=>"$data_id", "data_seq"=>"$data_seq", "data_type"=>"$data_type", "data_value"=>"$data_value");
        }
      }
  }
  else{
    $msg_error .= $stmt->error;
  }
    
  $stmt->close();

  return $rtnObj;
}