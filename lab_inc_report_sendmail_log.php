<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $order_lab_id = getQS("oid");
    $email_f = getQS("email_from");
    $email = getQS("email");
    $s_id = getSS("s_id");
    $txt_interpret = getQS("txt_interpret");
    // echo $uid."/".$order_lab_id."/".$email;

    $bind_param = "ss";
    $array_val = array($uid, $order_lab_id);
    $data_uid_array = array();

    $query = "SELECT collect_date, collect_time 
    from p_lab_order 
    where uid = ? and lab_order_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_uid_array["coldate"] = $row["collect_date"];
            $data_uid_array["coltime"] = $row["collect_time"];
        }
    }
    $stmt->close();

    $today = "";
    $today = date("Y-m-d H:i:s");
    $status = "1";

    $bind_param = "sssssssss";
    $array_val = array($uid, $data_uid_array["coldate"], $data_uid_array["coltime"], $email_f, $email, $txt_interpret, $s_id, $status, $today);

    $query = "INSERT into log_send_email_lab_result (uid, collect_date, collect_time, email_f, email, interpret_txt, s_id, status_t, upd_date) values(?, ?, ?, ?, ?, ?, ?, ?, ?);";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){}

    $stmt->close();
    $mysqli->close();
?>