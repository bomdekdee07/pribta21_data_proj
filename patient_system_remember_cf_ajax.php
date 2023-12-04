<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $condition_type = getQS("conditionmatch");
    $val_check_db_first = getQS("valfirst");
    $val_check_db_end = getQS("valend");
    $uid = getQS("uid");

    $total_txt = $val_check_db_first.$val_check_db_end;

    $data_check_have = "";
    $data_check_pass = "";

    if($condition_type == "citizenid"){
        $query = "select uid as val_have, passwd from patient_info where tel_no = ? and uid = ?;";
    }
    else{
        $query = "select uid as val_have, passwd from patient_info where citizen_id = ? and uid = ?;";
    }

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $total_txt, $uid);

    if($stmt->execute()){
        $stmt->bind_result($val_have, $passwd);
        while($stmt->fetch()){
            $data_check_have = $val_have;
            $data_check_pass = $passwd;
        }
    }

    $stmt->close();
    $mysqli->close();

    if($data_check_have != ""){
        echo true.",".$data_check_pass;
    }
    else{
        echo false.",".$data_check_pass;
    }
?>