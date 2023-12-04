<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $username = getQS("user");
    $pass = getQS("pass");

    $data_check_user = "";
    $data_check_pass = "";
    if($username != ""){
        $query = "select passwd from patient_info where uid = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $username);

        if($stmt->execute()){
            $stmt->bind_result($passwd);
            while($stmt->fetch()){
                $data_check_user = $passwd;
            }
        }

        $stmt->close();

        if($data_check_user == ""){
            $query = "select passwd from patient_info where email = ?;";

            $stmt = $mysqli->prepare($query);
            $stmt -> bind_param("s", $username);

            if($stmt->execute()){
                $stmt->bind_result($passwd);
                while($stmt->fetch()){
                    $data_check_user = $passwd;
                }
            }

            $stmt->close();
        }

        if($data_check_user == ""){
            $query = "select passwd from patient_info where tel_no = ?;";

            $stmt = $mysqli->prepare($query);
            $stmt -> bind_param("s", $username);

            if($stmt->execute()){
                $stmt->bind_result($passwd);
                while($stmt->fetch()){
                    $data_check_user = $passwd;
                }
            }

            $stmt->close();
        }

        if($data_check_user != ""){
            if (password_verify($pass, $data_check_user)) {
                echo true;
            }
            else{
                echo "รหัสผ่านไม่ถูกต้อง";
            }
        }
        
        $mysqli->close();
    }

    if($data_check_user == ""){
        echo "ไม่มีผู้ใช้นี้อยู่ในระบบ";
    }
?>