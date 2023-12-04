<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $s_id = getSS("s_id");
    $row_array = json_decode($_POST['data'], true);
    $type_name = "";
    foreach($row_array AS $key=>$val){
        $type_name = $val["type_name"];
        $name_show = $val["name_show"];
    }

    $rtn_succ = false;
    // check path
    $check_dir_type = is_dir('\\\192.168.100.46\Clinic\img\\'.$type_name);
    // print_r($output);
    
    if(!$check_dir_type){
        mkdir('//192.168.100.46/Clinic/img/'.$type_name, 0777, true); // create forder type

        $current_date = date('Y-m-d H:i:s');
        $bind_param = "sssss";
        $array_val = array($type_name, $name_show, "1", $current_date, $s_id);

        $query = "INSERT into type_img_master(type_img_id, type_img_name, status, upd_date, s_id) values(?,?,?,?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            $rtn_succ = true;
        }
        $stmt->close();
        $mysqli->close();
    }

    echo $rtn_succ;
?>