<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $u_mode = isset($_POST["mode"])?$_POST["mode"] : "";
    $req_id = isset($_POST["req_id"])?$_POST["req_id"] : "";
    // echo $u_mode;

    if($u_mode == "gen_running_code_req"){
        $head = "";
        $data_running_code = "";
        $query = "select CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000', '1')) as head,
            CASE 
                WHEN SUBSTRING(max(request_id), 8) < 10 THEN CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000',SUBSTRING(max(request_id), 8)+1)) 
                WHEN SUBSTRING(max(request_id), 8) > 9 THEN CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000',SUBSTRING(max(request_id), 8)+1))
                WHEN SUBSTRING(max(request_id), 8) > 99 THEN CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000',SUBSTRING(max(request_id), 8)+1))
                WHEN SUBSTRING(max(request_id), 8) > 999 THEN CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000',SUBSTRING(max(request_id), 8)+1))
                WHEN SUBSTRING(max(request_id), 8) > 9999 THEN CONCAT(CONCAT('RQ', concat(DATE_FORMAT(curdate(), '%y'), DATE_FORMAT(curdate(), '%m'))), CONCAT('/0000',SUBSTRING(max(request_id), 8)+1))
            else 'ERROR' end run_num
        from i_stock_request_list;";

        $stmt = $mysqli->prepare($query);

        if($stmt->execute()){
            $stmt->bind_result($head, $run_num);
            while($stmt->fetch()){
                $head = $head;
                $data_running_code = $run_num;
            }
        }else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        if($data_running_code == "ERROR"){
            echo $head;
        }else{
            echo $data_running_code;
        }
    }

    else if($u_mode == "check_dup_running_code_req"){
        $data_check_req_code = "";
        $query = "select count(*) as check_have_data
        from i_stock_request_list
        where request_id = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $req_id);

        if($stmt->execute()){
            $stmt->bind_result($check_have_data);
            while($stmt->fetch()){
                $data_check_req_code = $check_have_data;
            }
        }else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        echo $data_check_req_code;
    }

    $mysqli->close();
?>