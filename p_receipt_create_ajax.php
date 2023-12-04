<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $title = getQS("title");

    $bind_param = "ss";
    $array_val = array($uid, $title);

    $data_check = "";
    $query = "SELECT count(*) AS count_check 
    from j_bill_custom 
    where uid = ?
    and bill_title = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($count_check);
        while($stmt->fetch()){
            $data_check = $count_check;
        }
    }
    $stmt->close();
    $mysqli->close();

    if($data_check > 0){
        echo true;
    }
    else{
        echo false;
    }
?>