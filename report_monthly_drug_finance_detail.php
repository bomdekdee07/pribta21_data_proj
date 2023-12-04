<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
    
    $date_start = getQS("start_date");
    $clinicid = getSS("clinic_id");
    
    $end_dateInMonth = "";
    $end_dateInMonth = date('Y-m-t',strtotime($date_start));
    $end_dateInMonth_time = $end_dateInMonth." 23:59:59";
    $binde_parameter = "sssssss";
    $array_val = array($date_start, $end_dateInMonth_time, $clinicid, $date_start, $end_dateInMonth, $date_start, $end_dateInMonth);
    $data_drug = array();

    // echo $date_start."/".$end_dateInMonth;

    $query = "SELECT st_order.supply_code,
        st_order.supply_lot,
        st_master.supply_name,
        st_order.uid,
        st_order.dose_day,
        st_master.supply_unit,
        st_order.sale_price,
        st_cost.received_amt,
        st_cost.stock_amt,
        st_cost.stock_cost,
        st_rec.supply_amt AS rec_amt
    FROM i_stock_order st_order
    LEFT JOIN i_stock_master st_master ON(st_master.supply_code = st_order.supply_code)
    LEFT JOIN i_stock_group st_group ON(st_group.supply_group_code = st_master.supply_group_code)
    LEFT JOIN i_stock_cost st_cost ON(st_cost.supply_code = st_order.supply_code and st_cost.stock_lot = st_order.supply_lot)
    LEFT JOIN i_stock_recieved st_rec ON(st_rec.supply_code = st_order.supply_code and st_rec.stock_lot = st_order.supply_lot AND st_rec.recieved_datetime >= ? and st_rec.recieved_datetime <= ?)
    WHERE st_group.supply_group_type = '1'
    AND st_order.clinic_id = ?
    AND order_datetime >= ? and order_datetime <= ?
    AND st_cost.cost_date >= ? and st_cost.cost_date <= ?
    order by st_order.supply_code, st_order.sale_price ASC, st_cost.stock_cost ASC;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($binde_parameter, ...$array_val);

    $total_export_drug = 0;
    $total_received_amt = 0;
    $total_stock_amt = 0;
    $total_next_month = 0;
    $total_rec_amt = 0;
    $old_lot = "";
    $old_supcode = "";
    if($stmt->execute()){
        $stmt->bind_result($supply_code, $supply_lot, $supply_name, $uid, $dose_day, $supply_unit, $sale_price, $received_amt, $stock_amt, $stock_cost, $rec_amt);
        while($stmt->fetch()){
            if($old_supcode != $supply_code){
                $total_export_drug = 0;
                $total_stock_amt = 0;
                $total_stock_amt = $stock_amt;
                $total_rec_amt = $rec_amt;
            }
            
            if($old_supcode == $supply_code && $old_lot != $supply_lot){
                $total_received_amt = 0;
                $total_stock_amt += $stock_amt;
                $total_rec_amt += $rec_amt;
            }

            $data_drug[$supply_code]["code"] = $supply_code;
            $data_drug[$supply_code]["lot"] = $supply_lot;
            $data_drug[$supply_code]["name"] = $supply_name;

            $total_export_drug += $dose_day;
            $data_drug[$supply_code]["export_drug"] = $total_export_drug;
            $data_drug[$supply_code]["unit"] = $supply_unit;
            $data_drug[$supply_code]["sale_price"] = $sale_price;

            $total_received_amt += $received_amt;
            $data_drug[$supply_code]["received_stock"] = $total_received_amt+$total_rec_amt;
            $data_drug[$supply_code]["old_stock_amt"] = $total_stock_amt;
            $data_drug[$supply_code]["buy_price"] = $stock_cost;

            $total_next_month = ($total_stock_amt+($total_received_amt+$total_rec_amt))-$total_export_drug;
            $data_drug[$supply_code]["amt_next_month"] = $total_next_month;

            $old_lot = $supply_lot;
            $old_supcode = $supply_code;
        }
        // print_r($data_drug);
    }

    $stmt->close();
    $mysqli->close();

    $stHtmlDetail = "";

    foreach($data_drug as $key_supcode => $value){
        $stHtmlDetail .=        '<div class="fl-wrap-row font-s-2 row-hover row-color h-30">
                                    <div class="fl-fix w-20" style="background-color: white;"></div>
                                    <div class="fl-fill fl-mid-left">
                                        <span class="ml-1">'.$value["name"].'</span>
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["old_stock_amt"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["buy_price"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["received_stock"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["export_drug"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["sale_price"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-120">
                                        '.$value["amt_next_month"].'
                                    </div>
                                </div>';
    }

    echo $stHtmlDetail;
?>