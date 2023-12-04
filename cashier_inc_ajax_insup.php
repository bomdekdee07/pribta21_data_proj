<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSid = getQS("sid");
    $sUid = getQS("uid");
    $sColdate = getQS("coldate");
    $sColtime = getQS("coltime");
    $sDate_now = getQS("date_now");
    $sMode = getQS("mode");

    $flag_auth=1;
    $msg_error = "";

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
            $s_idUpdate = getSS("s_id");
        
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

                $sql_cmd = "ins/update:[$tbl_name] $col_update";
                $query = "INSERT INTO a_log_cmd (update_user, sql_cmd) VALUES(?, ?)";
                $stmt = $mysqli->prepare($query);

                // echo "query: $query";
                $stmt->bind_param('ss',$s_idUpdate,$sql_cmd);

                if($stmt->execute()){}
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
            $s_idUpdate = getSS("s_id");
            $flag_success = true;
        
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
                $flag_success = false;
                $msg_error .= $stmt->error;
            }
            $stmt->close();
            }
        
            $sql_cmd = "delete:[$tbl_name] $str_where";
            $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
            VALUES(?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ss', $s_idUpdate ,$sql_cmd);
            if($stmt->execute()){
        
            }
            else{
                $flag_success = false;
                $msg_error .= $stmt->error;
            }
            $stmt->close();
            // $mysqli->close();

            return $flag_success;
        }
    }

    // Approve
    if($sMode == "approve"){
        $flag_auth=1;

        $bind_param = "sss";
        $array_val = array($sUid, $sColdate, $sColtime);
        $queue = "";
        $bill_id = "";
        $date_now = "";
        $prepare_drug_by = "";
        $check_drug_by = "";
        
        $query = "SELECT queue.queue,
            bill.bill_id,
            billcan.date_now,
            queue.prepare_drug_by,
            queue.check_drug_by
        from i_queue_list queue
        join i_bill_detail bill on(bill.bill_q = queue.queue and bill.bill_date = queue.collect_date)
        join i_bill_cancel_approve billcan on(billcan.uid = queue.uid and billcan.collect_date = queue.collect_date and billcan.collect_time = queue.collect_time)
        where queue.uid = ?
        and queue.collect_date = ?
        and queue.collect_time = ?
        and billcan.status = 'W';";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);
        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $queue = $row["queue"];
                $bill_id = $row["bill_id"];
                $date_now = $row["date_now"];
                $prepare_drug_by = $row["prepare_drug_by"];
                $check_drug_by = $row["check_drug_by"];
            }
        }
        $stmt->close();

        $tbl_name = "i_bill_cancel_approve";
        $lst_data_update = array();
        if($flag_auth == 1 && $queue != "" && $bill_id != ""){      
            $lst_data_update["queue"] = $queue;
            $lst_data_update["bill_id"] = $bill_id;
            $lst_data_update["uid"] = $sUid;
            $lst_data_update["collect_date"] = $sColdate;
            $lst_data_update["collect_time"] = $sColtime;
            $lst_data_update["date_now"] = $date_now;
            $lst_data_update["md_sid"] = $sSid;
            $lst_data_update["update_date"] = $sDate_now;
            $lst_data_update["status"] = "A";

            // i_bill_cancel_approve
            updateListDataObj($tbl_name, $lst_data_update);

            $tbl_name = "i_queue_list";
            $lst_data_update = array();
            $lst_data_update["clinic_id"] = 'IHRI';
            $lst_data_update["queue_type"] = '1';
            $lst_data_update["queue"] = $queue;
            $lst_data_update["collect_date"] = $sColdate;
            $lst_data_update["uid"] = $sUid;
            $lst_data_update["prepare_drug_by"] = "";
            $lst_data_update["check_drug_by"] = "";
            $lst_data_update["issue_drug_by"] = "";

            // i_queue_list
            updateListDataObj($tbl_name, $lst_data_update);

            $tbl_name = "i_bill_list";
            $lst_data_update = array();
            $lst_data_update["bill_id"] = $bill_id;
            $lst_data_update["clinic_id"] = 'IHRI';
            $lst_data_update["receive_by"] = "";
            $lst_data_update["receive_amt"] = "";
            $lst_data_update["paid_amt"] = "";
            $lst_data_update["paid_datetime"] = "";

            // i_bill_list
            updateListDataObj($tbl_name, $lst_data_update);

            $bind_param = "sss";
            $array_val = array($sUid, $sColdate, $sColtime);
            $data_loop_order = array();
            $supply_code = "";
            $supply_lot = "";
            $order_code = "";

            $query = "SELECT supply_code,
                supply_lot,
                order_code
            from i_stock_order 
            where uid = ?
            and collect_date = ?
            and collect_time = ?
            order by order_code;";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param($bind_param, ...$array_val);

            if($stmt->execute()){
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    $data_loop_order[$row["supply_code"]]["supply_code"] = $row["supply_code"];
                    $data_loop_order[$row["supply_code"]]["supply_lot"] = $row["supply_lot"];
                    $data_loop_order[$row["supply_code"]]["order_code"] = $row["order_code"];
                }
                // print_r($data_loop_order);
            }
            $stmt->close();

            $tbl_name = "i_stock_order";
            foreach($data_loop_order as $col => $val){
                $lst_data_update = array();
                $lst_data_update["clinic_id"] = 'IHRI';
                $lst_data_update["supply_code"] = $val["supply_code"];
                $lst_data_update["supply_lot"] = $val["supply_lot"];
                $lst_data_update["order_code"] = $val["order_code"];
                $lst_data_update["uid"] = $sUid;
                $lst_data_update["collect_date"] = $sColdate;
                $lst_data_update["collect_time"] = $sColtime;
                $lst_data_update["is_pickup"] = "0";
                $lst_data_update["is_paid"] = "0";

                // i_stock_order
                updateListDataObj($tbl_name, $lst_data_update);
            }
        }
        else{
            $msg_error = "Not found billId/Queue/date_now";
        }
    }
    // Complete
    else if($sMode == "complete"){
        $flag_auth=1;

        $bind_param = "sss";
        $array_val = array($sUid, $sColdate, $sColtime);
        $queue = "";
        $bill_id = "";
        $date_now = "";
        $prepare_drug_by = "";
        $check_drug_by = "";
        
        $query = "SELECT queue.queue,
            bill.bill_id,
            billcan.date_now,
            billcan.prepare_drug_by,
            billcan.check_drug_by
        from i_queue_list queue
        join i_bill_detail bill on(bill.bill_q = queue.queue and bill.bill_date = queue.collect_date)
        join i_bill_cancel_approve billcan on(billcan.uid = queue.uid and billcan.collect_date = queue.collect_date and billcan.collect_time = queue.collect_time)
        where queue.uid = ?
        and queue.collect_date = ?
        and queue.collect_time = ?
        and billcan.status = 'A';";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);
        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $queue = $row["queue"];
                $bill_id = $row["bill_id"];
                $date_now = $row["date_now"];
                $prepare_drug_by = $row["prepare_drug_by"];
                $check_drug_by = $row["check_drug_by"];
            }
        }
        $stmt->close();

        $tbl_name = "i_bill_cancel_approve";
        $lst_data_update = array();
        if($flag_auth == 1 && $queue != "" && $bill_id != ""){      
            $lst_data_update["queue"] = $queue;
            $lst_data_update["bill_id"] = $bill_id;
            $lst_data_update["uid"] = $sUid;
            $lst_data_update["collect_date"] = $sColdate;
            $lst_data_update["collect_time"] = $sColtime;
            $lst_data_update["date_now"] = $date_now;
            $lst_data_update["md_sid"] = $sSid;
            $lst_data_update["update_date"] = $sDate_now;
            $lst_data_update["status"] = "F";

            // i_bill_cancel_approve
            updateListDataObj($tbl_name, $lst_data_update);

            $tbl_name = "i_queue_list";
            $lst_data_update = array();
            $lst_data_update["clinic_id"] = 'IHRI';
            $lst_data_update["queue_type"] = '1';
            $lst_data_update["queue"] = $queue;
            $lst_data_update["collect_date"] = $sColdate;
            $lst_data_update["uid"] = $sUid;
            $lst_data_update["prepare_drug_by"] = $prepare_drug_by;
            $lst_data_update["check_drug_by"] = "";//$check_drug_by;
            $lst_data_update["issue_drug_by"] = "";

            // i_queue_list
            updateListDataObj($tbl_name, $lst_data_update);

            $bind_param = "sss";
            $array_val = array($sUid, $sColdate, $sColtime);
            $data_loop_order = array();
            $supply_code = "";
            $supply_lot = "";
            $order_code = "";

            $query = "SELECT supply_code,
                supply_lot,
                order_code
            from i_stock_order 
            where uid = ?
            and collect_date = ?
            and collect_time = ?
            order by order_code;";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param($bind_param, ...$array_val);

            if($stmt->execute()){
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    $data_loop_order[$row["supply_code"]]["supply_code"] = $row["supply_code"];
                    $data_loop_order[$row["supply_code"]]["supply_lot"] = $row["supply_lot"];
                    $data_loop_order[$row["supply_code"]]["order_code"] = $row["order_code"];
                }
                // print_r($data_loop_order);
            }
            $stmt->close();

            $tbl_name = "i_stock_order";
            foreach($data_loop_order as $col => $val){
                $lst_data_update = array();
                $lst_data_update["clinic_id"] = 'IHRI';
                $lst_data_update["supply_code"] = $val["supply_code"];
                $lst_data_update["supply_lot"] = $val["supply_lot"];
                $lst_data_update["order_code"] = $val["order_code"];
                $lst_data_update["uid"] = $sUid;
                $lst_data_update["collect_date"] = $sColdate;
                $lst_data_update["collect_time"] = $sColtime;
                $lst_data_update["is_pickup"] = "1";

                // i_stock_order
                updateListDataObj($tbl_name, $lst_data_update);
            }
        }
        else{
            $msg_error = "Not found billId/Queue/date_now";
        }
    }
    else if($sMode == "reject"){
        $flag_auth=1;

        $bind_param = "sss";
        $array_val = array($sUid, $sColdate, $sColtime);
        $queue = "";
        $bill_id = "";
        $date_now = "";
        $prepare_drug_by = "";
        $check_drug_by = "";
        $receive_by = "";
        $receive_amt = "";
        $paid_amt = "";
        $paid_datetime = "";
        
        $query = "SELECT queue.queue,
            bill.bill_id,
            billcan.date_now,
            queue.prepare_drug_by,
            queue.check_drug_by,
            billcan.receive_by,
            billcan.receive_amt,
            billcan.paid_amt,
            billcan.paid_datetime
        from i_queue_list queue
        join i_bill_detail bill on(bill.bill_q = queue.queue and bill.bill_date = queue.collect_date)
        join i_bill_cancel_approve billcan on(billcan.uid = queue.uid and billcan.collect_date = queue.collect_date and billcan.collect_time = queue.collect_time)
        where queue.uid = ?
        and queue.collect_date = ?
        and queue.collect_time = ?
        and billcan.status = 'W';";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);
        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $queue = $row["queue"];
                $bill_id = $row["bill_id"];
                $date_now = $row["date_now"];
                $prepare_drug_by = $row["prepare_drug_by"];
                $check_drug_by = $row["check_drug_by"];
                $receive_by = $row["receive_by"];
                $receive_amt = $row["receive_amt"];
                $paid_amt = $row["paid_amt"];
                $paid_datetime = $row["paid_datetime"];
            }
        }
        $stmt->close();

        $tbl_name = "i_bill_cancel_approve";
        $lst_data_update = array();
        if($flag_auth == 1 && $queue != "" && $bill_id != ""){      
            $lst_data_update["queue"] = $queue;
            $lst_data_update["bill_id"] = $bill_id;
            $lst_data_update["uid"] = $sUid;
            $lst_data_update["collect_date"] = $sColdate;
            $lst_data_update["collect_time"] = $sColtime;
            $lst_data_update["date_now"] = $date_now;
            $lst_data_update["md_sid"] = $sSid;
            $lst_data_update["update_date"] = $sDate_now;
            $lst_data_update["status"] = "C";

            // i_bill_cancel_approve
            updateListDataObj($tbl_name, $lst_data_update);

            $tbl_name = "i_bill_list";
            $lst_data_update = array();
            $lst_data_update["bill_id"] = $bill_id;
            $lst_data_update["clinic_id"] = 'IHRI';
            $lst_data_update["paid_method"] = 'CASH';
            $lst_data_update["receive_by"] = $receive_by;
            $lst_data_update["receive_amt"] = $receive_amt;
            $lst_data_update["paid_amt"] = $paid_amt;
            $lst_data_update["paid_datetime"] = $paid_datetime;

            // i_bill_list
            updateListDataObj($tbl_name, $lst_data_update);
        }
    }

    $mysqli->close();
    echo $msg_error;
?>