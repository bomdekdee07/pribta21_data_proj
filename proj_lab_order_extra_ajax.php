<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $u_mode = getQS("u_mode");
    $projid = getQS("projid");
    $uid = getQS("uid");

    if($u_mode == "first_master_visitid"){
        $bind_param = "s";
        $array_val = array($projid);
        $data_visit_master = "";

        $query = "SELECT visit_id 
        from p_project_visit_timepoint 
        where proj_id = ? 
        order by seq_no limit 1;";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $data_visit_master = $row["visit_id"];
            }
        }
        $stmt->close();

        echo $data_visit_master;
    }
    else if($u_mode == "count_order_note"){
        $bind_param = "s";
        $array_val = array($uid);
        $data_count_extra = 0;

        $query = "SELECT count(*)+1 AS count_extra 
        from p_lab_order 
        where uid = ? 
        and lab_order_note like '%extra%';";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $data_count_extra = $row["count_extra"];
            }
        }
        $stmt->close();

        echo $data_count_extra;
    }

    $mysqli->close();
?>