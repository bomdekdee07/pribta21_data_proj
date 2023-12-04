<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $uid = getQS("uid")!=""? getQS("uid"): "0";

    $data_check = "";
    $data_name_uid = "";
    $query = "select count(*) as check_daat,
        CONCAT(CONCAT(fname, ' ') , sname) as name
    from patient_info where uid = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $uid);

    if($stmt->execute()){
        $stmt->bind_result($check_daat, $name);
        while($stmt->fetch()){
            $data_check = $check_daat;
            $data_name_uid = $name;
        }
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    echo $data_check.",".$data_name_uid;
?>