<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $data_new = isset($_POST["data_new"])?$_POST["data_new"] : "";
    $supply_group_code = isset($_POST["supply_group_code"])?$_POST["supply_group_code"] : "";
    $data_mode = isset($_POST["mode"])?$_POST["mode"] : "";
    // echo $data_new;

    if($data_mode == ""){
        $data_check_dup_group_type = array();
        $query = "select distinct supply_group_type from i_stock_type;";

        $stmt = $mysqli->prepare($query);

        if($stmt->execute()){
            $stmt->bind_result($supply_group_type);
            while ($stmt->fetch()) {
                $data_check_dup_group_type[$supply_group_type] = $supply_group_type;
            }
            // print_r($data_check_dup_group_type);
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        $sJS = "";
        if(count($data_check_dup_group_type) > 0){
            foreach($data_check_dup_group_type as $key => $value){
                if($data_new == $value){
                    $sJS = "";
                    echo "duplicate";
                    break;
                }
                else{
                    $sJS = "unduplicate";
                }
            }
        }

        echo $sJS;
    }
    else if($data_mode = "dup_supply_code"){
        $data_check_dup = "";
        $query = "select count(*) as count_check_data 
        from i_stock_group where supply_group_code = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $supply_group_code);

        if($stmt->execute()){
            $stmt->bind_result($count_check_data);
            while ($stmt->fetch()) {
                $data_check_dup = $count_check_data;
            }
            // print_r($data_check_dup_group_type);
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        echo $data_check_dup;
    }
    $mysqli->close();
?>