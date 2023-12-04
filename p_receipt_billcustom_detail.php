<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $bill_id = getQS("billid");
    $bill_drug = getQS("bill_drug");
    $bill_lab = getQS("bill_lab");
    $uid = getQS("uid");
    $type_leg = "TH";

    // echo "IN".$bill_drug;

    // DETAIL ALL
    $bill_id_head = "";
    $queue_head = "";
    $uid_bill_detail = "";
    $supply_group_type_old = "";
    $supply_name_old = "";
    $supply_group_code_old = "";
    $uid_old = "";
    $date_full_coldate = "";
    $total_price_sum_group_all = "";
    $total_price_sum = "";

    $total_all_data = array();
    $uid_count_array = array();
    $query = "SELECT bill_d.bill_id,
        queue_l.uid as uid_detail,
        queue_l.collect_date,
        queue_l.collect_time,
        queue_l.queue,
        st_type.supply_type_name as name_n_service,
        st_group.supply_group_name as name_is_service,
        st_type.supply_type_name_en,
        st_master.supply_name,
        st_master.supply_name_en,
        st_type.supply_type_initial,
        st_order.total_price,
        st_group.supply_group_type,
        st_type.is_service,
        st_group.supply_group_code,
        st_order.supply_code
    FROM i_bill_detail bill_d
    join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join i_stock_order st_order on(st_order.uid = queue_l.uid and st_order.collect_date = queue_l.collect_date and st_order.collect_time = queue_l.collect_time)
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    where bill_id = ?
    order by queue_l.collect_date, queue_l.uid, st_group.supply_group_code;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($bill_id, $uid_detail, $collect_date, $collect_time, $queue, $name_n_service, $name_is_service, $supply_type_name_en, $supply_name, $supply_name_en, $supply_type_initial, $total_price, $supply_group_type, $is_service, $supply_group_code, $supply_code);
        while ($stmt->fetch()) {
            if($supply_group_type_old != $supply_group_type)
                $total_price_sum = "";

            if($is_service == 1){
                if($bill_drug == "1"){
                    if($supply_name_old != $supply_name)
                        $total_price_sum = "";
                    
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["uid"] = $uid_detail;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["lab_name"] = (($type_leg == "EN")?$supply_name_en:$supply_name);
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["code_name"] = $supply_type_initial;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["group_code"] = $supply_group_code;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["supply_code"] = $supply_code;
                    
                    $total_price_sum = $total_price_sum+$total_price;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["total_price"] = $total_price_sum;
                    $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
                }
                else{
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["uid"] = $uid_detail;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["lab_name"] = (($type_leg == "EN")?$name_is_service:$name_n_service);
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["code_name"] = $supply_type_initial;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["group_code"] = $supply_group_code;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["supply_code"] = $supply_code;
                    
                    $total_price_sum = $total_price_sum+$total_price;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code]["total_price"] = $total_price_sum;
                    $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
                }
            }
            else{
                if($bill_drug == "1"){
                    if($supply_name_old != $supply_name)
                        $total_price_sum = "";

                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["uid"] = $uid_detail;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["lab_name"] = (($type_leg=="EN")?$supply_name:$supply_name);
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["code_name"] = $supply_type_initial;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["group_code"] = $supply_group_code;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["supply_code"] = $supply_code;
                    $total_price_sum = $total_price_sum+$total_price;
                    $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["total_price"] = $total_price_sum;
                    $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
                }
                else{
                    if($uid_old != $uid_detail)
                        $total_price_sum = "";

                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["uid"] = $uid_detail;
                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["lab_name"] = (($type_leg=="EN")?$supply_type_name_en:$name_n_service);
                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["code_name"] = $supply_type_initial;
                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["group_code"] = $supply_group_code;
                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["supply_code"] = $supply_code;
                    $total_price_sum = $total_price_sum+$total_price;
                    $total_all_data[$uid_detail][$supply_group_type]["phamar"]["total_price"] = $total_price_sum;
                    $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
                }
            }

            if($uid_detail == $uid){
                $date = date_create($collect_date);
                $date_con = date_format($date,"d/m");
                if($type_leg == 'TH'){
                    $year_con = date_format($date,"Y")+543;
                }
                else{
                    $year_con = date_format($date,"Y");
                }
                $date_full_coldate = $date_con."/".$year_con." ".$collect_time;
                $coldate = $collect_date;
                $coltime = $collect_time;
                $queue_head = $queue;
            }
            
            $supply_group_type_old = $supply_group_type;
            $supply_name_old = $supply_name;
            $supply_group_code_old = $supply_group_code;
            $uid_old = $uid_detail;
            $uid_count_array[$uid_detail] = $uid_detail;
        }
        // print_r($total_all_data);
        // echo count($uid_count_array);
    }
    $stmt->close();

    // LAB TOTAL
    $lab_saleprice_data = array();
    $total_price_sum_lab_all = "";
    $query = "SELECT queue_l.uid as uid_lab,
        lab_test.lab_name,
        lab_order_lab_test.sale_price,
        lab_order_lab_test.lab_id
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join p_lab_order lab_order on(lab_order.uid = queue_l.uid and lab_order.collect_date = queue_l.collect_date and lab_order.collect_time = queue_l.collect_time)
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    where lab_order.lab_order_status != 'C'
    and bill_d.bill_id = ?
    order by queue_l.uid;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    $old_lab_uid = "";
    $old_labid = "";
    $total_sum = 0;
    if($stmt->execute()){
        $stmt->bind_result($uid_lab, $lab_name, $sale_price, $lab_id);
        while ($stmt->fetch()) {
            // $lab_saleprice_data[$uid_lab]["lab_name"] = "Lab";
            
            if($bill_lab == "1"){
                if($old_labid != $lab_id || $old_lab_uid != $uid_lab){
                    $total_price_sum_lab_all = 0;
                }

                $total_price_sum_lab_all = $total_price_sum_lab_all+$sale_price;
                $lab_saleprice_data[$uid_lab][$lab_id]["name_lab"] = $lab_name;
                $lab_saleprice_data[$uid_lab][$lab_id]["total_price"] = $total_price_sum_lab_all;
                $lab_saleprice_data[$uid_lab][$lab_id]["lab_id"] = $lab_id;
                $total_sum += $sale_price;
            }
            else{
                if($old_lab_uid != $uid_lab){
                    $total_price_sum_lab_all = 0;
                }

                $total_price_sum_lab_all = $total_price_sum_lab_all+$sale_price;
                $lab_saleprice_data[$uid_lab]["name_lab"] = "Lab";
                $lab_saleprice_data[$uid_lab]["total_price"] = $total_price_sum_lab_all;
                $lab_saleprice_data[$uid_lab]["lab_id"] = $lab_id;
                $total_sum += $sale_price;
            }

            $old_labid = $lab_id;
            $old_lab_uid = $uid_lab;
        }
        // print_r($lab_saleprice_data);
    }

    $stmt->close();
    $mysqli->close();

    $htmlBindDetail = "";
    foreach($total_all_data as $keyUid => $valkeyUid){
        foreach($valkeyUid as $keyGrouptype => $valkeyGrouptype){
            foreach($valkeyGrouptype as $keySupplyname => $val){
                $htmlBindDetail .= '    <div class="fl-wrap-row h-25 font-s-2">
                                            <div class="fl-fix w-40"></div>
                                            <div class="fl-fill">
                                                <div class="fl-wrap-row h-25 row-hover row-color">
                                                    <div class="fl-fix w-20"></div>
                                                    <label><div class="fl-fix w-450 fl-mid-left">
                                                        <input class="ck-list-bill-cash list-save" type="checkbox" style="transform: scale(1.2);" data-groupcode="'.$val["group_code"].'" data-supplycode="'.$val["supply_code"].'" value="1"/><span class="holiday-ml-s1">'.$val["lab_name"].'</span>
                                                    </div></label>
                                                    <label><div class="fl-fix w-100 fl-mid-left">
                                                        '.$val["total_price"].'
                                                    </div></label>
                                                </div>
                                            </div>
                                            <div class="fl-fix w-40"></div>
                                        </div>';
            }
        }

        if(count($lab_saleprice_data) > 0 && isset($lab_saleprice_data[$keyUid])){
            if($bill_lab == "1"){
                foreach($lab_saleprice_data[$keyUid] as $keyLabuid => $valkeyLabuid){
                    // foreach($valkeyLabuid as $keyLabid => $val){
                        $htmlBindDetail .= '    <div class="fl-wrap-row h-25 font-s-2">
                                                        <div class="fl-fix w-40"></div>
                                                        <div class="fl-fill">
                                                            <div class="fl-wrap-row h-25 row-hover row-color">
                                                                <div class="fl-fix w-20"></div>
                                                                <label><div class="fl-fix w-450 fl-mid-left">
                                                                    <input class="ck-lab-bill-cash list-save" type="checkbox" style="transform: scale(1.2);" data-groupcodelab="Lab" data-labid="'.$valkeyLabuid["lab_id"].'" value="1"/><span class="holiday-ml-s1">'.$valkeyLabuid["name_lab"].'</span>
                                                                </div></label>
                                                                <label><div class="fl-fix w-100 fl-mid-left">
                                                                    '.$valkeyLabuid["total_price"].'
                                                                </div></label>
                                                            </div>
                                                        </div>
                                                        <div class="fl-fix w-40"></div>
                                                    </div>';
                    // }
                }
            }
            else{
                $htmlBindDetail .= '    <div class="fl-wrap-row h-25 font-s-2">
                                                <div class="fl-fix w-40"></div>
                                                <div class="fl-fill">
                                                    <div class="fl-wrap-row h-25 row-hover row-color">
                                                        <div class="fl-fix w-20"></div>
                                                        <label><div class="fl-fix w-450 fl-mid-left">
                                                            <input class="ck-lab-bill-cash list-save" type="checkbox" style="transform: scale(1.2);" data-groupcodelab="Lab" data-labid="'.$lab_saleprice_data[$keyUid]["lab_id"].'" value="1"/><span class="holiday-ml-s1">'.$lab_saleprice_data[$keyUid]["name_lab"].'</span>
                                                        </div></label>
                                                        <label><div class="fl-fix w-100 fl-mid-left">
                                                            '.$lab_saleprice_data[$keyUid]["total_price"].'
                                                        </div></label>
                                                    </div>
                                                </div>
                                                <div class="fl-fix w-40"></div>
                                            </div>';
            }
        }
    }

    echo $htmlBindDetail;
?>