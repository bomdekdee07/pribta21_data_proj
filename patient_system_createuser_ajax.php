<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $citizen_id = getQS("citizen");
    $tel_no = getQS("telno");
    $fname = getQS("name");
    $lname = getQS("lastname");
    $birth_day = getQS("birthday");

    $data_check_citizen = "";
    $data_check_tel = "";
    $data_check_name = "";
    if($citizen_id != ""){
        $query = "SELECT uid FROM patient_info WHERE citizen_id=?";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $citizen_id);

        if($stmt->execute()){
            $stmt->bind_result($uid);
            while($stmt->fetch()){
                $data_check_citizen = $uid;
            }
        }

        $stmt->close();
    }

    if($tel_no != ""){
        $query = "SELECT uid FROM patient_info WHERE tel_no=?";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $tel_no);

        if($stmt->execute()){
            $stmt->bind_result($uid);
            while($stmt->fetch()){
                $data_check_tel = $uid;
            }
        }

        $stmt->close();
    }

    if($fname != "" && $lname && $birth_day){
        $query = "SELECT uid FROM patient_info WHERE fname=? AND sname=? AND date_of_birth=?";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("sss",$fname,$lname,$birth_day);

        if($stmt->execute()){
            $stmt->bind_result($uid);
            while($stmt->fetch()){
                $data_check_name = $uid;
            }
        }

        $stmt->close();
    }
    $mysqli->close();

    echo $data_check_citizen.",".$data_check_tel.",".$data_check_name;
?>