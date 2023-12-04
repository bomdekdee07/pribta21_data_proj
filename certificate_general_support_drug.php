<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $langauge_select = getQS("langauge_select");

    $query_txt_select = "";
    if($langauge_select == "TH"){
        $query_txt_select .= " st_master.supply_unit ";
    }
    else{
        $query_txt_select .= " st_master.supply_unit_en ";
    }

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_txt_name_drug = "";

    $query = "SELECT st_master.supply_name,
        st_order.dose_day,
        ".$query_txt_select."
    from i_stock_group st_group
    join i_stock_master st_master on(st_master.supply_group_code = st_group.supply_group_code)
    left join i_stock_order st_order on(st_order.supply_code = st_master.supply_code)
    where st_order.uid = ?
    and st_order.collect_date = ?
    and st_order.collect_time = ?
    and st_group.supply_group_type = '1';";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            if($langauge_select == "TH")
                $data_txt_name_drug .= $row["supply_name"]." ".$row["dose_day"]." ".$row["supply_unit"].",";
            else
                $data_txt_name_drug .= $row["supply_name"]." ".$row["dose_day"]." ".$row["supply_unit_en"].",";
        }
    }
    $stmt->close();
    $mysqli->close();

    echo substr($data_txt_name_drug, 0, -1);
?>