<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $uid = isset($_POST["sid"])?$_POST["sid"] : "";
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $app_date = isset($_POST["app_date"])?$_POST["app_date"] : "";
    $app_time = isset($_POST["app_time"])?$_POST["app_time"] : "";

    $data_check = "";
    $data_name_uid = "";
    $query = "select count(*) as check_data from i_appointment 
    where s_id = ?
    and clinic_id = ?
    and appointment_date >= ?
    and appointment_date <= ?
    and appointment_time = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("sssss", $uid, $clinic_id, $app_date, $app_date, $app_time);

    if($stmt->execute()){
        $stmt->bind_result($check_data);
        while($stmt->fetch()){
            $data_check = $check_data;
        }
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    echo $data_check;
?>