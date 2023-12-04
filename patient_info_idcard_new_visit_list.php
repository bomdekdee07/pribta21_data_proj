<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sUid = getQS("uid");
    
    $bind_param = "s";
    $array_val = array($sUid);
    
    $query = "SELECT distinct collect_date,
        collect_time
    from p_data_result 
    where uid = ?
    and collect_time != '00:00:00'
    and data_id = 'cn_plan'
    and data_result != ''
    order by collect_date;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_visit_list = "";
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_visit_list .= "<option value='".$row["collect_date"]." ".$row["collect_time"]."'>".$row["collect_date"]." ".$row["collect_time"]."</option>";
        }
    }
    $stmt->close();
    $mysqli->close();

    if(empty($data_visit_list)){
        $data_visit_list .= '<option value="">Not found Data.</option>';
    }

    echo $data_visit_list;
?>