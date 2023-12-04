<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $data_list = array();
    $query = "SELECT supply_group_code, 
        supply_group_name
    from i_stock_group 
    where supply_group_type = '1';";

    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_code, $supply_group_name);
        while ($stmt->fetch()) {
            $data_list[$supply_group_code]["code"] = $supply_group_code;
            $data_list[$supply_group_code]["name"] = $supply_group_name;
        }
    }
    else{
        $msg_error .= $stmt->error;
    }
    
    $stmt->close();
    $mysqli->close();

    $stListDrug = "";
    if(getPerm("STOCK", "1", "view")){
        foreach($data_list as $group_code => $val){
            $stListDrug .= '<option value='.$val["code"].'>'.$val["name"].'</option>';
        }
    }

    echo $stListDrug;
?>