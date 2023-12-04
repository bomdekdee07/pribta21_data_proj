<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT
        queue 
    FROM
        i_queue_list 
    WHERE
        uid = ?
        AND collect_date = ?
        AND collect_time = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_queue = false;
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_queue = $row["queue"];
        }
    }
    $stmt->close();
    $mysqli->close();

    // get today
    $today = date("Y-m-d");

    if($data_queue != ""){
        if(substr($data_queue,0,1) != "L" && $data_queue != false && $today == $coldate)
            $data_queue = true;
        else
            $data_queue = false;

    }
    
    echo $data_queue;
?>