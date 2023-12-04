<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $formid = getQS("formid");
    $isdone = getQS("isdone");
    $sid = getSS("s_id");

    $current_date = date('Y-m-d H:i:s');

    $query = "INSERT into p_data_form_done(uid, collect_date, collect_time, form_id, is_done, record_datetime, update_datetime, update_by)
    VALUES('$uid', '$coldate', '$coltime', '$formid', '$isdone', '$current_date', '$current_date', '$sid')
    ON DUPLICATE KEY UPDATE is_done = '$isdone';";
    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        echo "complete";
    }
    else{
        echo $stmt->error;
    }
?>