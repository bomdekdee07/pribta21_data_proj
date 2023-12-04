<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sUid = getQS("uid");
    $sProj_id = getQS("projid");
    $data_check_duplicate = array();

    $bind_param = "ss";
    $array_val = array($sUid, $sProj_id);

    $query = "SELECT count(*) AS check_data,
        proj_group_id
    from p_project_uid_list 
    where uid = ?
    and proj_id = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_check_duplicate["check_data"] = $row["check_data"];
            $data_check_duplicate["group_id"] = $row["proj_group_id"];
        }
        echo $data_check_duplicate["check_data"].",".$data_check_duplicate["group_id"];
    }
    $stmt->close();
    $mysqli->close();
?>