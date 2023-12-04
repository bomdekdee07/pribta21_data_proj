<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $condition_type = getQS("conditionmatch");
    $val_check_db = getQS("phoneorcitizenid");

    $data_check_have = "";
    $uid_s = "";

    if($condition_type == "telno"){
        $query = "select citizen_id as val_have, uid from patient_info where tel_no = ? order by uid;";
    }
    else{
        $query = "select tel_no as val_have, uid from patient_info where citizen_id = ? order by uid;";
    }

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $val_check_db);

    if($stmt->execute()){
        $stmt->bind_result($val_have, $uid);
        while($stmt->fetch()){
            $data_check_have = $val_have;
            $uid_s = $uid;
        }
    }

    $stmt->close();
    $mysqli->close();

    if($condition_type == "citizenid"){
        $data_check_have = substr($data_check_have, 0, 7);
    }
    else{
        $data_check_have = substr($data_check_have, 0, 9);
    }

    if($data_check_have != ""){
        echo $data_check_have.','.$uid_s;
    }
    else{
        echo false, $uid_s.','.$uid_s;
    }
?>