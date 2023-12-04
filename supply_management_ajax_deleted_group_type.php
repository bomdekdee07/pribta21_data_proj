<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $group_type = isset($_POST["supply_group_type"])?$_POST["supply_group_type"] : "";
    // echo $data_new;

    $data_check_dup_master = "";
    $query = "select count(*) as check_have_data from i_stock_group
    where supply_group_type = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $group_type);

    if($stmt->execute()){
        $stmt->bind_result($check_have_data);
        while ($stmt->fetch()) {
            $data_check_dup_master = $check_have_data;
        }
        // print_r($data_check_dup_group_type);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    echo $data_check_dup_master;
?>