<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sSupCode = getQS("supply_code");
    $sSaleOpt= getQS("sale_opt");

    $bind_param = "ss";
    $array_val = array($sSupCode, $sSaleOpt);
    $data_sale_price = array();
    $sum_sale_price = 0;
    $old_supCode = "";

    $query = "SELECT supply_code,
        sale_price 
    from i_stock_price 
    where supply_code = ? 
    and sale_opt_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            if($old_supCode != $row["supply_code"])
                $sum_sale_price = 0;

            $sum_sale_price += $row["sale_price"];
            $data_sale_price[$row["supply_code"]] = $sum_sale_price;

            $old_supCode = $row["supply_code"];
        }
    }
    $stmt->close();
    $mysqli->close();

    $data_sale_price = json_encode($data_sale_price);
    echo $data_sale_price;
?>