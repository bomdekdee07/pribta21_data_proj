<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sSupCode = getQS("supply_code");
    $sSupCode = explode("%", $sSupCode);
    
    $where_str = "";
    foreach($sSupCode AS $key){
        $where_str .= "'".$key."',";
    }
    // echo Substr($where_str, 0, -1);

    $data_amt_left = array();
    $query = "SELECT supply_code,
        stock_amt
    from i_stock_list
    where supply_code in (".Substr($where_str, 0, -1).")
    order by supply_code;";
    $stmt = $mysqli->prepare($query);

    $amt_left = 0;
    $old_supcode = "";
    if($stmt->execute()){
        $resul = $stmt->get_result();
        while($row = $resul->fetch_assoc()){
            if($old_supcode != $row["supply_code"])
                $amt_left = 0;

            $amt_left += $row["stock_amt"];
            $data_amt_left[$row["supply_code"]] = $amt_left;

            $old_supcode = $row["supply_code"];
        }
        // print_r($data_amt_left);
    }
    $stmt->close();
    $mysqli->close();

    $data_amt_left = json_encode($data_amt_left);
    echo $data_amt_left;
?>