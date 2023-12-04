<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $supply_code = getQS("supply_code");
    $stock_lot = getQS("stock_lot");
    $start_date = getQS("start_date");
    $stop_date = getQS("stop_date")!=""?getQS("stop_date"):"2999-12-01";
    $clinic_id = getSS("clinic_id", "IHRI");

    $bind_parameter = "ss";
    $array_val = array($clinic_id,$supply_code);

    $bind_parameter2 = "ss";
    $array_val2 = array($clinic_id,$supply_code);

    // ST ORDER
    $data_st_order = array();
    $query = "SELECT 'จ่ายยา' AS action,
        st_order.supply_lot,
        st_order.uid,
        st.s_name,
        st_order.dose_day,
        st_master.supply_unit,
        st_order.order_datetime,
        st_order.collect_date,
        st_order.collect_time
    FROM i_stock_order st_order
    LEFT JOIN i_stock_list JSSO ON (JSSO.supply_code = st_order.supply_code AND JSSO.clinic_id = st_order.clinic_id AND JSSO.stock_lot = st_order.supply_lot)
    LEFT JOIN i_stock_master st_master ON(st_master.supply_code = st_order.supply_code)
    LEFT JOIN p_staff st ON(st.s_id = st_order.order_by)
    WHERE st_order.clinic_id = ?
    AND st_order.supply_code = ?";

    // ST RC
    $query3 = "SELECT 'นำเข้า' as action,
        receive.stock_lot,
        receive.request_id,
        st.s_name,
        receive.supply_amt,
        st_master.supply_unit,
        receive.recieved_datetime
    from i_stock_recieved receive
    left join i_stock_master st_master on(st_master.supply_code = receive.supply_code)
    left join p_staff st on(st.s_id = receive.recieved_by)
    where receive.clinic_id = ?
    AND receive.supply_code = ?";

    // ST LOG
    $query2 = "SELECT st_log.action_mode,
        st_log.supply_lot,
        st_log.uid,
        st.s_name,
        supply_amt,
        st_master.supply_unit,
        st_log.updated_date
    FROM i_stock_log st_log
    left join i_stock_master st_master on(st_master.supply_code = st_log.supply_code)
    left join p_staff st on(st.s_id = st_log.updated_by)
    where st_log.action_mode IN ('ADJUST', 'ADJUST_COST')
    and st_log.clinic_id = ?
    and st_log.supply_code = ?";

    if($stock_lot != ""){
        $query .= " AND st_order.supply_lot = ?";
        $bind_parameter .= "s";
        $array_val[] = $stock_lot;

        $query2 .= " AND st_log.supply_lot = ?";
        $bind_parameter2 .= "s";
        $array_val2[] = $stock_lot;

        $query3 .= " AND receive.stock_lot = ?";
    }

    if($start_date != ""){
        $query .= " AND st_order.collect_date >= ?";
        $bind_parameter .= "s";
        $array_val[] = $start_date;

        $query2 .= " AND st_log.updated_date >= ?";
        $bind_parameter2 .= "s";
        $array_val2[] = $start_date;

        $query3 .= " AND receive.recieved_datetime >= ?";

        if($stop_date != ""){
            $query .= " AND st_order.collect_date <= ?";
            $bind_parameter .= "s";
            $array_val[] = $stop_date;

            $query2 .= " AND st_log.updated_date <= ?";
            $bind_parameter2 .= "s";
            $array_val2[] = $stop_date." 23:59:59";

            $query3 .= " AND receive.recieved_datetime <= ?";
        }
    }

    $query .= " ORDER BY st_order.order_datetime DESC";
    $query2 .= " ORDER BY st_log.updated_date DESC";
    $query3 .= " ORDER BY receive.recieved_datetime DESC";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param($bind_parameter, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($action, $supply_lot, $uid, $s_name, $dose_day, $supply_unit, $order_datetime, $collect_date, $collect_time);
        while ($stmt->fetch()) {
            $iX=(isset($data_st_order[$order_datetime])?count($data_st_order[$order_datetime]):0);
            $data_st_order[$order_datetime][$iX]["action"] = $action;
            $data_st_order[$order_datetime][$iX]["lot"] = $supply_lot;
            $data_st_order[$order_datetime][$iX]["uid"] = $uid;
            // $data_st_order[$order_datetime][]["by"] = $order_by;
            $data_st_order[$order_datetime][$iX]["by_name"] = $s_name;
            $data_st_order[$order_datetime][$iX]["amt"] = $dose_day;
            $data_st_order[$order_datetime][$iX]["unit"] = $supply_unit;
            $data_st_order[$order_datetime][$iX]["order_date"] = $order_datetime;
            $data_st_order[$order_datetime][$iX]["col_date"] = $collect_date;
            $data_st_order[$order_datetime][$iX]["col_time"] = $collect_time;
        }
        // print_r($data_st_order);
    }
    $stmt->close();

    $stmt = $mysqli->prepare($query2);
    $stmt -> bind_param($bind_parameter2, ...$array_val2);

    if($stmt->execute()){
        $stmt->bind_result($action, $supply_lot, $uid, $s_name, $dose_day, $supply_unit, $order_datetime);
        while ($stmt->fetch()) {
            $iX=(isset($data_st_order[$order_datetime])?count($data_st_order[$order_datetime]):0);
            $data_st_order[$order_datetime][$iX]["action"] = $action;
            $data_st_order[$order_datetime][$iX]["lot"] = $supply_lot;
            $data_st_order[$order_datetime][$iX]["uid"] = $uid;
            // $data_st_order[$order_datetime][$iX]["by"] = $order_by;
            $data_st_order[$order_datetime][$iX]["by_name"] = $s_name;
            $data_st_order[$order_datetime][$iX]["amt"] = $dose_day;
            $data_st_order[$order_datetime][$iX]["unit"] = $supply_unit;
            $data_st_order[$order_datetime][$iX]["order_date"] = $order_datetime;
            $data_st_order[$order_datetime][$iX]["col_date"] = "";
            $data_st_order[$order_datetime][$iX]["col_time"] = "";
        }
        // print_r($data_st_order);
    }
    $stmt->close();

    $stmt = $mysqli->prepare($query3);
    $stmt -> bind_param($bind_parameter2, ...$array_val2);

    if($stmt->execute()){
        $stmt->bind_result($action, $supply_lot, $req_id, $s_name, $dose_day, $supply_unit, $order_datetime);
        while ($stmt->fetch()) {
            $iX=(isset($data_st_order[$order_datetime])?count($data_st_order[$order_datetime]):0);
            $data_st_order[$order_datetime][$iX]["action"] = $action;
            $data_st_order[$order_datetime][$iX]["lot"] = $supply_lot;
            $data_st_order[$order_datetime][$iX]["uid"] = $req_id;
            // $data_st_order[$order_datetime][$iX]["by"] = $order_by;
            $data_st_order[$order_datetime][$iX]["by_name"] = $s_name;
            $data_st_order[$order_datetime][$iX]["amt"] = $dose_day;
            $data_st_order[$order_datetime][$iX]["unit"] = $supply_unit;
            $data_st_order[$order_datetime][$iX]["order_date"] = $order_datetime;
            $data_st_order[$order_datetime][$iX]["col_date"] = "";
            $data_st_order[$order_datetime][$iX]["col_time"] = "";
        }
        // print_r($data_st_order);
    }
    $stmt->close();
    $mysqli->close();

    $sjs_supply_detail_function="";
    // ksort($data_st_order);
    foreach($data_st_order as $order_datetime => $array_suplot){
        foreach($array_suplot as $row_no => $val){
        $sjs_supply_detail_function .=  '<div class="fl-wrap-row row-color fs-small h-25">
                                            <div class="fl-fix w-100 fl-mid">
                                                <span>'.$val["action"].'</span>
                                            </div>
                                            <div class="fl-fix w-150 fl-mid">
                                                <span>'.$val["lot"].'</span>
                                            </div>
                                            <div class="fl-fix w-150 fl-mid">
                                                <span>'.$val["uid"].'</span>
                                            </div>
                                            <div class="fl-fix w-260 fl-mid-left">
                                                <span class="holiday-ml-2">'.$val["by_name"].'</span>
                                            </div>
                                            <div class="fl-fix w-100 fl-mid">
                                                <span>'.$val["amt"].'</span>
                                            </div>
                                            <div class="fl-fix w-75 fl-mid">
                                                <span>'.$val["unit"].'</span>
                                            </div>
                                            <div class="fl-fix w-250 fl-mid">
                                                <span>'.$val["order_date"].'</span>
                                            </div>
                                            <div class="fl-wrap-col h-20" style="min-width: 102px; max-width: 102px;">
                                            
                                                <div class="fl-wrap-row">
                                                    <div class="fl-fix smallfont2 fl-mid">
                                                        <button id="view_log" class="btn holiday-ml-1 button-action-detail" style="height: 19px; width: 85px; text-align: center; padding: 0;" data-supplycode="'.$supply_code.'" data-lot="'.$val["lot"].'" data-action="'.$val["action"].'" data-uid="'.$val["uid"].'" data-coldate="'.$val["col_date"].'" data-time="'.$val["col_time"].'"><i class="fa fa-search" aria-hidden="true"></i> Detail</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
        
        }
    }

    echo $sjs_supply_detail_function;
?>