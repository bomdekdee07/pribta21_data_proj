<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include_once("in_front_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $note_all = getQS("note_all");
    $s_id = getSS("s_id");
    // echo "test: ".$uid."/".$note_all;

    $upd_date = date("Y-m-d H:i:s");
    $status = "0";
    $query = "UPDATE patient_info set note_all_clinic = '$note_all', upd_note_all_clinic = '$upd_date', user_note_all_clinic = '$s_id' where uid = '$uid'";
    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $status = "1";
    }

    echo $status;
?>