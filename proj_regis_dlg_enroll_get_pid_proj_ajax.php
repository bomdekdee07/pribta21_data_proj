<?
    include("in_session.php");
    include_once("in_php_function.php");
    include_once("in_php_fn_date.php");
    include("in_db_conn.php");

    $sUID = getQS("uid");
    $sProjid = getQS("proj_id");

    $bind_param = "ss";
    $arra_val = array($sUID, $sProjid);
    $pid_val = "";

    $query = "SELECT pid
    from p_project_uid_list 
    where uid = ?
    and proj_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$arra_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $pid_val = $row["pid"];
        }
    }
    $stmt->close();
    $mysqli->close();

    echo $pid_val;
?>