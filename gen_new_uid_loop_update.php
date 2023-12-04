<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $gUid = getQS("uid");
    $gPid = getQS("pid");
    $u_mode = getQS("u_mode");

    if($u_mode == "create_uid"){
        $bind_param = "ss";
        $array_val = array($gUid, $gPid);

        $query = "update temp_genuid_real set uid_new_gen = ? where pid = ?;";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            echo true;
        }
    }

    else if("update_old_uid"){
        $bind_param = "ss";
        $array_val = array($gUid, $gPid);

        $query = "update datarst set uid = ? where pid = ?;";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            echo $gPid;
        }
    }
?>