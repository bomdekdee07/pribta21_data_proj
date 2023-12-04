<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $bill_id = getQS("billid");
    $bill_id = substr($bill_id, 0, 4)."/".substr($bill_id, 4);
    $data_id = getQS("dataid");
    $uid_opt = getQS("uid");

    $sopt = "";
    $query = "select queue_l.uid as uid_bill 
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    where bill_d.bill_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt -> execute()){
        $stmt -> bind_result($uid_bill);
        while($stmt -> fetch()){
            if($uid_opt == $uid_bill){
                $sopt .= "<option value=".$uid_bill." data-id=".$data_id." selected>".$uid_bill."</option>";
            }
            else{
                $sopt .= "<option value=".$uid_bill." data-id=".$data_id.">".$uid_bill."</option>";
            }
        }
        // print_r($data_uid);
    }

    $stmt->close();
    $mysqli->close();

    echo $sopt;
?>