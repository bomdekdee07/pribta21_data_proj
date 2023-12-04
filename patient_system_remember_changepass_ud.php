<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $check_val_pass = getQS("val");
    $new_pass = getQS("newpass");

    $data_check_have = "";
    $query = "select uid from patient_info  
    where uid = ?
    and passwd = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $uid, $check_val_pass);

    if($stmt->execute()){
        $stmt->bind_result($uid);
        while($stmt->fetch()){
            $data_check_have = $uid;
        }
    }

    $stmt->close();

    if($data_check_have != ""){
        $msg_error = "";
        $new_pass = PASSWORD_HASH($new_pass, PASSWORD_DEFAULT);

        $query = "update patient_info set passwd = ?
        where uid = ?
        and passwd = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("sss", $new_pass, $uid, $check_val_pass);

        if($stmt->execute()){
            echo true;
        }
        else{
            echo false;
            $msg_error .= $stmt->error; //error จะบอกตรงนี้ ถ้า duplicate kry
        }
        $stmt->close();
    }
    else{
        echo false;
    }

    $mysqli->close();
?>