<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $col_date = isset($_POST["col_date"])?$_POST["col_date"] : "";
    $clinic_id = isset($_POST["clinic_id"])?$_POST["clinic_id"] : "";
    $sid = isset($_POST["s_id"])?$_POST["s_id"] : "";

    $data_queue_room = "";
    if($sid == "D07" || $sid == "D03"){
        $query = "select CONCAT(main.queue, '*', main.room_no, ':', sub.section_id) as queue
        from i_queue_list as main, i_room_list as sub
        where main.collect_date = ?
        and main.clinic_id = ?
        and main.queue_call = 2
        and main.queue_status != 0
        and main.clinic_id = sub.clinic_id
        and main.room_no = sub.room_no
        and sub.section_id in ('D07', 'D03')
        and main.queue_type = 1
        and main.queue_status != 2
        order by main.queue_datetime;";

        $data_sid = "D07,D03";
    }
    else if($sid == "D05" || $sid == "D06" || $sid == "D02" || $sid == "D09"){
        $query = "select CONCAT(main.queue, '*', main.room_no, ':', sub.section_id) as queue
        from i_queue_list as main, i_room_list as sub
        where main.collect_date = ?
        and main.clinic_id = ?
        and main.queue_call = 2
        and main.queue_status != 0
        and main.clinic_id = sub.clinic_id
        and main.room_no = sub.room_no
        and sub.room_status != 0
        and sub.section_id in ('D05', 'D06', 'D02', 'D09')
        and main.queue_type = 1
        and main.queue_status != 2
        order by main.queue_datetime;";

        $data_sid = "D05^D06,D02,D09";
    }

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $col_date, $clinic_id);

    if($stmt->execute()){
        $stmt->bind_result($queue);
        while ($stmt->fetch()) {
            $data_queue_room .= $queue;
            $data_queue_room .= ",";
        }
        // print_r($data_queue_room);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    echo substr($data_queue_room, 0, strlen($data_queue_room)-1)."/".$data_sid;
?>