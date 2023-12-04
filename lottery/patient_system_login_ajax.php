<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $username = getQS("username");
    $pass = getQS("password");

    $data_check_user = "";
    if($username != ""){
        $query = "SELECT password from staff where user = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $username);

        if($stmt->execute()){
            $stmt->bind_result($passwd);
            while($stmt->fetch()){
                $data_check_user = $passwd;
            }
        }

        $stmt->close();
        $mysqli->close();

        if($data_check_user != ""){
            if (password_verify($pass, $data_check_user)) {
                echo true;
            }
            else{
                echo "รหัสผ่านไม่ถูกต้อง";
            }
        }
    }

    if($data_check_user == ""){
        echo "ไม่มีผู้ใช้นี้อยู่ในระบบ";
    }
?>