<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $start_date = getQS("start_date");
    $end_date = getQS("end_date");
    $sup_code = getQS("sup_code");
    $clinic_id = getSS("clinic_id");

    $bind_parameter = "s";
    $bind_value = array($clinic_id);

    $data_loop_medician_summary = array();
    $query = "SELECT st_order.supply_code,
        st_master.supply_name,
        st_order.uid,
        st_order.dose_day,
        st_master.supply_unit
    FROM i_stock_order st_order
    LEFT JOIN i_stock_master st_master ON(st_master.supply_code = st_order.supply_code)
    LEFT JOIN i_stock_group st_group ON(st_group.supply_group_code = st_master.supply_group_code)
    WHERE st_group.supply_group_type = '1'
    AND st_order.clinic_id = ?";

    if($sup_code != ""){
        $query .= " AND st_group.supply_group_code = ?";
        $bind_parameter .= "s";
        $bind_value[] = $sup_code;
    }

    if($start_date != ""){
        $query .= " AND st_order.order_datetime >= ?";
        $bind_parameter .= "s";
        $bind_value[] = $start_date;

        if($end_date != ""){
            $query .= " AND st_order.order_datetime <= ?";
            $bind_parameter .= "s";
            $bind_value[] = $end_date." 23:59:59";
        }
    }

    $query .= " ORDER BY st_order.supply_code, st_order.supply_lot, st_order.uid;";

    // echo $query;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_parameter, ...$bind_value);

    $old_supCode = "";
    $old_uid = "";
    $total_caseUid = 0;
    $count_caseUid = 0;
    $total_dispendeDrug = 0;
    if($stmt->execute()){
        $stmt->bind_result($supply_code, $supply_name, $uid, $amt, $supply_unit);
        while($stmt->fetch()){
            if($old_supCode != $supply_code){
                $count_caseUid = 0;
                $total_caseUid = 0;
            }

            if($old_supCode == $supply_code && $old_uid == $uid){
                $count_caseUid = $count_caseUid-1;
            }
            else if($old_supCode == $supply_code && $old_uid != $uid){
                $total_caseUid = 0;
            }
            // echo $supply_code."/".$total_caseUid.":".$uid."=".$count_caseUid."<br>";

            if($old_supCode != $supply_code){
                $total_dispendeDrug = 0;
            }

            $data_loop_medician_summary[$supply_code]["code"] = $supply_code;
            $data_loop_medician_summary[$supply_code]["name"] = $supply_name;

            $count_caseUid = ($count_caseUid+(count($uid)));
            $total_caseUid = $total_caseUid+$count_caseUid;
            $data_loop_medician_summary[$supply_code]["total_uid"] = $total_caseUid;

            $total_dispendeDrug = $total_dispendeDrug+$amt;
            $data_loop_medician_summary[$supply_code]["total_dispense"] = $total_dispendeDrug;
            $data_loop_medician_summary[$supply_code]["unit"] = $supply_unit;

            $old_supCode = $supply_code;
            $old_uid = $uid;
        }
    }

    $stmt->close();
    $mysqli->close();

    $stHtmlDetail = "";
                                                                                                                                                                                    
    ksort($data_loop_medician_summary);
    foreach($data_loop_medician_summary as $supCode => $value){
        $stHtmlDetail .=        '<div class="fl-wrap-row font-s-2 row-hover row-color h-30">
                                    <div class="fl-fix w-20" style="background-color: white"></div>
                                    <div class="fl-fix fl-mid-left w-100">
                                        <span class="ml-1">'.$value["code"].'</span>
                                    </div>
                                    <div class="fl-fill fl-mid-left">
                                        <span class="ml-1">'.$value["name"].'</span>
                                    </div>
                                    <div class="fl-fix fl-mid w-150">
                                        '.$value["total_uid"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-200">
                                        '.$value["total_dispense"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-150">
                                        '.$value["unit"].'
                                    </div>
                                </div>';
    }

    echo $stHtmlDetail;
?>