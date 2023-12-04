<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT data_result
    from p_data_result 
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    and collect_time != '00:00:00'
    and data_id = 'cn_plan'
    and data_result != '';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_result = "";
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_result = $row["data_result"];
        }
    }
    $stmt->close();
    $mysqli->close();

    echo $data_result;
?>