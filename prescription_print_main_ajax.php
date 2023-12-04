<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");

    $data_check_drug = "";
    $query = "select queue.uid
    from i_queue_list queue
    join i_stock_order st_order on(st_order.uid = queue.uid and st_order.collect_date = queue.collect_date and st_order.collect_time = queue.collect_time)
    where queue.uid = ?
    and queue.collect_date = ?
    and queue.collect_time = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("sss", $uid, $coldate, $coltime);

    if($stmt->execute()){
        $stmt->bind_result($uid);
        while ($stmt->fetch()) {
            $data_check_drug = $uid;
        }
    }

    $stmt->close();
    $mysqli->close();

    if($data_check_drug != ""){
        echo true;
    }
    else{
        echo false;
    }
?>