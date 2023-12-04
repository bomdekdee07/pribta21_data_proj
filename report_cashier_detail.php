<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $filter_date = getQS("start_date");
    $clinic_id = getSS("clinic_id");

    $bind_parameter = "sss";
    $bind_value = array($clinic_id, $filter_date, $filter_date);

    $data_loop_cashier = array();
    $query = "SELECT bill_d.bill_id,
        queue_l.uid as uid_detail,
        queue_l.collect_date,
        queue_l.queue,
        SUM(st_order.total_price) AS total_sale_service,
        total_drug.total_sale_drug,
        lab_sale.total_lab
    FROM i_bill_detail bill_d
    JOIN i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    LEFT JOIN i_stock_order st_order on(st_order.uid = queue_l.uid and st_order.collect_date = queue_l.collect_date and st_order.collect_time = queue_l.collect_time)
    LEFT JOIN i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    LEFT JOIN i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code and st_group.supply_group_type != '1')
    LEFT JOIN i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    LEFT JOIN (
        SELECT lab_order.uid,
            lab_order.collect_date,
            lab_order.collect_time,
            SUM(lab_order_lab_test.sale_price) total_lab
        FROM p_lab_order lab_order
        LEFT JOIN p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
        left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
        WHERE lab_order.lab_order_status != 'C'
        GROUP BY lab_order.uid, lab_order.collect_date, lab_order.collect_time
    ) lab_sale ON(lab_sale.uid = queue_l.uid AND lab_sale.collect_date = queue_l.collect_date AND lab_sale.collect_time = queue_l.collect_time)
    LEFT JOIN (
        SELECT st_order_drug.uid,
            st_order_drug.collect_date,
            st_order_drug.collect_time,
            SUM(st_order_drug.total_price) total_sale_drug
        FROM i_stock_order st_order_drug
        LEFT JOIN i_stock_master st_master_drug on(st_master_drug.supply_code = st_order_drug.supply_code)
        LEFT JOIN i_stock_group st_group_drug on(st_group_drug.supply_group_code = st_master_drug.supply_group_code)
        LEFT JOIN i_stock_type st_type_drug on(st_type_drug.supply_group_type = st_group_drug.supply_group_type)
        WHERE st_group_drug.supply_group_type = '1'
        GROUP BY st_order_drug.uid, st_order_drug.collect_date
    ) total_drug ON(total_drug.uid = queue_l.uid and total_drug.collect_date = queue_l.collect_date and total_drug.collect_time = queue_l.collect_time)
    where bill_d.clinic_id = ?
    AND bill_d.bill_date >= ? and bill_d.bill_date <= ?";

    $query .= " GROUP BY bill_d.bill_id, queue_l.uid, queue_l.collect_date, queue_l.queue
                ORDER BY bill_d.bill_id, queue_l.uid;";

    // echo $query;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_parameter, ...$bind_value);

    $total_all_drug = 0;
    $total_all_service = 0;
    $total_all_lab = 0;
    $total_all_per_bill = 0;
    $total_all_net = 0;
    if($stmt->execute()){
        $stmt->bind_result($bill_id, $uid_detail, $collect_date, $queue, $total_sale_service, $total_sale_drug, $total_lab);
        while($stmt->fetch()){
            $data_loop_cashier[$bill_id.$queue]["billId"] = $bill_id;
            $data_loop_cashier[$bill_id.$queue]["uid"] = $uid_detail;
            $data_loop_cashier[$bill_id.$queue]["queue"] = $queue;
            $data_loop_cashier[$bill_id.$queue]["colDate"] = $collect_date;
            $data_loop_cashier[$bill_id.$queue]["totalSaleDrug"] = $total_sale_drug;
            $data_loop_cashier[$bill_id.$queue]["totalSaleService"] = $total_sale_service;
            $data_loop_cashier[$bill_id.$queue]["totalSaleLab"] = $total_lab;

            $total_all_drug += $total_sale_drug;
            $total_all_service += $total_sale_service;
            $total_all_lab += $total_lab;
            $total_all_per_bill = ($total_sale_drug+$total_sale_service+$total_lab);
            $total_all_net += $total_all_per_bill;
            $data_loop_cashier[$bill_id.$queue]["totalAllPerBill"] = $total_all_per_bill;
        }
        // print_r($data_loop_cashier);
    }

    $stmt->close();
    $mysqli->close();

    $stHtmlDetail = "";                                                                                                                                                                            
    foreach($data_loop_cashier as $key_billid => $value){
        $stHtmlDetail .=        '<div class="fl-wrap-row font-s-2 row-hover row-color h-30">
                                    <div class="fl-fix w-20" style="background-color: white;"></div>
                                    <div class="fl-fill fl-mid">
                                        '.$value["billId"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-100">
                                        '.$value["uid"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-80">
                                        '.$value["queue"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-150">
                                        '.$value["colDate"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-170">
                                        <span class="holiday-mr-3 sale-drug">'.number_format($value["totalSaleDrug"]).'</span>
                                    </div>
                                    <div class="fl-fix fl-mid-right w-170">
                                        <span class="holiday-mr-3 sale-service">'.number_format($value["totalSaleService"]).'</span>
                                    </div>
                                    <div class="fl-fix fl-mid-right w-170">
                                        <span class="holiday-mr-3 sale-lab">'.number_format($value["totalSaleLab"]).'</span>
                                    </div>
                                    <div class="fl-fix fl-mid-right w-170">
                                        <span class="holiday-mr-3 sale-bill">'.number_format($value["totalAllPerBill"]).'</span>
                                    </div>
                                </div>';
    }

    echo $stHtmlDetail;
?>