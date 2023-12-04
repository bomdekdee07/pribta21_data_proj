<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $col_date = getQS("collect_date");
    $col_time = getQS("collect_time");
    $request_id = getQS("req_id");
    $supply_code = getQS("sup_code");
    $supply_lot = getQS("sup_lot");

    $data_st_order = array("action" => "", "sup_code" => "", "name" => "", "lot" => "", "desc" => "", "amt" => "", "unit" => "", "exp_date" => "", "add_date" => "");
    if($request_id == "undefined"){
        $query = "select 'จ่ายยา' as action,
            st_order.supply_code,
            st_master.supply_name,
            st_order.supply_lot,
            st_order.supply_desc,
            st_order.dose_day,
            st_master.supply_unit,
            JSSO.stock_exp_date,
            JSSO.stock_added_datetime
        from i_stock_order st_order
        LEFT JOIN i_stock_list JSSO ON (JSSO.supply_code = st_order.supply_code AND JSSO.clinic_id = st_order.clinic_id AND JSSO.stock_lot = st_order.supply_lot)
        left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
        left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
        left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
        where st_group.supply_group_type = 1
        and st_order.uid = ?
        and st_order.collect_date = ?
        and st_order.collect_time = ?
        and st_order.supply_code = ?
        and st_order.supply_lot = ?
        order by st_group.supply_group_type, st_order.supply_code, st_group.supply_group_code;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("sssss", $uid, $col_date, $col_time, $supply_code, $supply_lot);
    }
    else{
        $query = "select 'นำเข้า' as action,
            recevic.supply_code,
            st_master.supply_name,
            recevic.stock_lot as supply_lot,
            recevic.remark as supply_desc,
            recevic.supply_amt as stock_amt,
            st_master.supply_unit,
            JSSO.stock_exp_date,
            recevic.recieved_datetime as stock_added_datetime
        from i_stock_recieved recevic
        LEFT JOIN i_stock_list JSSO ON (JSSO.supply_code = recevic.supply_code AND JSSO.stock_lot = recevic.stock_lot)
        left join i_stock_master st_master on(st_master.supply_code = recevic.supply_code)
        left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
        left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
        where st_group.supply_group_type = 1
        and recevic.request_id = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $request_id);
    }

    if($stmt->execute()){
        $stmt->bind_result($action, $supply_code, $supply_name, $supply_lot, $supply_desc, $stock_amt, $supply_unit, $stock_exp_date, $stock_added_datetime);
        while ($stmt->fetch()) {
            $data_st_order["action"] = $action;
            $data_st_order["sup_code"] = $supply_code;
            $data_st_order["name"] = $supply_name;
            $data_st_order["lot"] = $supply_lot;
            $data_st_order["desc"] = $supply_desc;
            $data_st_order["amt"] = $stock_amt;
            $data_st_order["unit"] = $supply_unit;
            $data_st_order["exp_date"] = $stock_exp_date;
            $data_st_order["add_date"] = $stock_added_datetime;
        }
        // print_r($data_st_order);
    }
    $stmt->close();
    $mysqli->close();

    $sJS_specification = "";
    $sJS_specification .=   '<div class="fl-wrap-col fs-small" id="supply_detail_specification">';
    $sJS_specification .=   '<div class="fl-wrap-row h-20 holiday-mt-1">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Type:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["action"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Supply Code:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["sup_code"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Supply Name:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["name"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Supply Lot:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["lot"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-40">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Supply Des:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["desc"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Amount:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["amt"]." ".$data_st_order["unit"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Expriry Date:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["exp_date"].'</span>
                                </div>
                            </div>
                            <div class="fl-wrap-row h-20">
                                <div class="fl-fix w-110 fw-b">
                                    <span class="fl-mid-right">Add Date:</span>
                                </div>
                                <div class="fl-fix w-10"></div>
                                <div clsss="fl-fix w-150">
                                    <span class="fl-mid-left">'.$data_st_order["add_date"].'</span>
                                </div>
                            </div>';
    $sJS_specification .=   '</div>';

    echo $sJS_specification;
?>