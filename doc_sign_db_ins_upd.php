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
        function updateListDataObj($tbl_name, $lst_data_item){
            //print_r($lst_data_item);
            global $mysqli; // db
            global $msg_error;
            
            $flag_success = true;
            $col_insert = "";
            $col_update = "";
            $col_value = "";
            $colume_val = "";
            $colume_val_id = "";
            $sid_log = "doc_signature";
        
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

        $u_mode = getQS("app_mode")!=""? getQS("app_mode"):"";
        // Create Sinature
        if($u_mode == "ins_docsign"){
            $clinic_id = getQS("clinic_id");
            $doc_code = getQS("doc_code");
            $s_id = getQS("s_id");
            $uid = getQS("uid");
            $collect_date = getQS("collect_date");
            $collect_time = getQS("collect_time");
            $sig_status = getQS("sig_status");
            $sig_leg_type = getQS("sig_leg_type");

            $flag_auth=1;

            $tbl_name = "i_doc_signatures";
            $lst_data_update = array();
            if($flag_auth == 1){      
                $lst_data_update["clinic_id"] = $clinic_id;
                $lst_data_update["doc_code"] = $doc_code;
                $lst_data_update["s_id"] = $s_id;
                $lst_data_update["uid"] = $uid;
                $lst_data_update["collect_date"] = $collect_date;
                $lst_data_update["collect_time"] = $collect_time;
                $lst_data_update["sig_leg_type"] = $sig_leg_type;
                $lst_data_update["sig_status"] = $sig_status;
                $lst_data_update["sig_time_stemp"] = date('Y-m-d H:i:s');

                // print_r($lst_data_update);
                updateListDataObj($tbl_name, $lst_data_update);
            }
        }
    }

    // return object
    $rtn['mode'] = $u_mode;
    $rtn['msg_error'] = $msg_error;
    $rtn['msg_info'] = $msg_info;
    $rtn['flag_auth'] = $flag_auth;

    $returnData = json_encode($rtn);
    echo $returnData;
?>