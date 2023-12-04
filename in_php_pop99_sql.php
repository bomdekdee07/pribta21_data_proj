<?
include_once("in_db_conn.php");

//update  or insert UID , collect_date, collect_time
function updateListDataObj($tbl_name, $lst_data_item, $s_id){

//print_r($lst_data_item);
  global $mysqli; // db
  global $msg_error;
  $affect_row = 0;

        $flag_success = true;
      	$aObjData = array();
        $col_insert = ""; $col_update = ""; $col_value = ""; $col_update_log = "";
        $sPrepare = "";

      foreach ($lst_data_item as $col => $value){
        //echo " $col / $value";
        array_push($aObjData,$value);
        $col_insert .= $col.",";
        $sPrepare .="s";
        $col_value.="?,";
        $col_update .= $col."=VALUES(".$col."),";

        $col_update_log .= $col."='".$value."',";

      }//foreach

      $col_insert = ($col_insert !="")?substr($col_insert,0,strlen($col_insert)-1):"" ;
      $col_update = ($col_update !="")?substr($col_update,0,strlen($col_update)-1):"" ;
      $col_update_log = ($col_update_log !="")?substr($col_update_log,0,strlen($col_update_log)-1):"" ;
      $col_value = ($col_value !="")?substr($col_value,0,strlen($col_value)-1):"" ;

      if($col_value != ""){
        $query = "INSERT INTO $tbl_name ($col_insert)
        VALUES ($col_value) On Duplicate Key
        Update $col_update";
  //echo "query: $query";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($sPrepare,...$aObjData);

        if($stmt->execute()){
          $affect_row = $stmt->affected_rows;
        }
        else{
          $flag_success = false;
          $msg_error .= $stmt->error;
        }

      //  $affect_row = $mysqli ->affected_rows;
      // echo "affectrow: $affect_row";
        $stmt->close();

       if($affect_row > 0){
        $sql_cmd = "update:[$tbl_name] $col_update_log";
        $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
        VALUES(?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ss',$s_id,$sql_cmd);
        if($stmt->execute()){
        }
        else{
          $msg_error .= $stmt->error;
        }
        $stmt->close();
       }

      }// if($col_value != "")

      //return $flag_success;
      return $affect_row;
}

// delete
function deleteListDataObj($tbl_name,$lst_where_data_item, $s_id){
    global $mysqli; // db
    $affect_row = 0;
    $col_delete = "";

    $str_where = "";
    foreach ($lst_where_data_item as $col => $value){
      $str_where .= " $col = '$value' AND ";
    }

    if($str_where != ""){
      $str_where = substr($str_where,0,strlen($str_where)-4);
      $str_where = " WHERE $str_where ";

      $query = "DELETE FROM $tbl_name $str_where";
    //  echo "query: $query";
      $stmt = $mysqli->prepare($query);
      if($stmt->execute()){
        $affect_row = $stmt->affected_rows;
      }
      else{
        $msg_error .= $stmt->error;
      }
      $stmt->close();

    }

      if($affect_row > 0){
        $sql_cmd = "delete:[$tbl_name] $str_where";
        $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
        VALUES(?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ss',$s_id,$sql_cmd);
        if($stmt->execute()){

        }
        else{
          $msg_error .= $stmt->error;
        }
        $stmt->close();
      }



   return $affect_row ;

}//deleteListDataObj


function addToLog($msgInfo, $s_id){
  global $mysqli; // db
  global $msg_error;



  $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
  VALUES(?, ?)";
  //  error_log("addtolog: $s_id,$msgInfo / $query");
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss',$s_id,$msgInfo);
  if($stmt->execute()){
  }
  else{
    error_log($stmt->error);
  }
  $stmt->close();
}

// update log after update data in form
function addToLogDataResult($update_s_id, $uid, $collect_date, $collect_time,
 $form_id, $data_id, $data_result, $lastupdate='now()'){

  global $mysqli; // db
  global $msg_error;


  $query = "INSERT INTO a_log_data_result (update_user, uid,collect_date,collect_time,
  form_id, data_id, data_result, update_time) VALUES(?,?,?,?,?,?,?, '$lastupdate')";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('sssssss',$update_s_id,$uid, $collect_date, $collect_time,
   $form_id, $data_id, $data_result );
  if($stmt->execute()){
  }
  else{
    error_log($stmt->error);
  }
  $stmt->close();
}


?>
