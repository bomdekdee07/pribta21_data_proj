<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $supply_group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"] : "";
    
    $head = "";
    $data_group_code = "";
    $query = "select main.supply_type_initial,
    CONCAT(main.supply_type_initial, CASE 
        WHEN (MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1) < 10 THEN CONCAT('0000',(MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1)) 
        WHEN (MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1) > 9 THEN CONCAT('000',(MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1))
        WHEN (MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1) > 99 THEN CONCAT('00',(MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1))
        WHEN (MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1) > 999 THEN CONCAT('0',(MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1))
        WHEN (MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1) > 9999 THEN CONCAT('',(MAX(CAST(SUBSTRING(sub.supply_group_code, 6) AS DECIMAL))+1))
        else 'ERROR' end) as run_num
    from i_stock_type AS main
    left join i_stock_group as sub on (main.supply_group_type = sub.supply_group_type)
    where main.supply_group_type = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $supply_group_type);

    if($stmt->execute()){
        $stmt->bind_result($supply_type_initial, $run_num);
        while($stmt->fetch()){
            $head = $supply_type_initial;
            $data_group_code = $run_num;
            // echo $data_group_code;
        }
        // echo count($data_group_code);
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    if($data_group_code == null){
        echo ($head == ""? "" : $head."00001,".$head);
    }else{
        echo $data_group_code.",".$head;
    }
    
?>