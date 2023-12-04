<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $now_time = "";
    for($i = 2; $i<=120; $i++){
        $now_time = date("h:i:sa");
        $query = "insert into i_queue_list values('IHRI', '".$i."', '2022-07-27', '".$now_time."', '2', '2022-07-27 ".$now_time."', '1', '1', '0', '1', '', '', '', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '')";
        $stmt = $mysqli->prepare($query);

        if($stmt->execute()){
            echo $i."<br>";
        }
        sleep(1);
    }
    $stmt->close();
    $mysqli->close();
?>