<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $uid_ins_log = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $type_img  = getQS("type_img");
    $img_comment = getQS("img_comment");
    $s_id = getSS("s_id");

    // query pk count
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $cont_pk_log_img = 0;

    $query = "SELECT img_id AS count_pk
    from img_uid_info
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    order by img_seq;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $cont_pk_log_img = explode("_", $row["count_pk"])[2];
        }
        // echo $cont_pk_log_img;
    }
    $stmt->close();

    // login and Check drive NAS
    $user = "pribtatg";
    $password = "@PribTaTG|Img1#";
    $path_str_to = "";
    $uid = str_replace(' ', '', $uid);
    $uid = preg_replace('/-+/', '', $uid);
    $coldate = str_replace(' ', '', $coldate);
    $coldate = preg_replace('/-+/', '', $coldate);

    // check path
    $output = "";
    exec('net use "\\\192.168.100.46\Clinic\img /user:"'.$user.'" "'.$password.'" /persistent:no 2>&1', $output);
    $check_dir_type = is_dir('\\\192.168.100.46\Clinic\img\\'.$type_img);
    // print_r($output);
    
    if(!$check_dir_type){
        mkdir('//192.168.100.46/Clinic/img/'.$type_img, 0777, true); // create forder type
    }

    $check_dir_uid = is_dir('\\\192.168.100.46\Clinic\img\\'.$type_img."\\".$uid);
    if(!$check_dir_uid){
        mkdir('//192.168.100.46/Clinic/img/'.$type_img."/".$uid, 0777, true); // create forder UID
    }

    $check_dir_coldate = is_dir('\\\192.168.100.46\Clinic\img\\'.$type_img."\\".$uid."\\".$coldate);
    if(!$check_dir_coldate){
        mkdir('//192.168.100.46/Clinic/img/'.$type_img."/".$uid."/".$coldate, 0777, true); // create forder collect_date
    }

    $path_str_to = $type_img."\\".preg_replace('/[^A-Za-z0-9\-]/', '', $uid)."\\".preg_replace('/[^A-Za-z0-9\-]/', '', $coldate);
    $path_str_img = $type_img."/".preg_replace('/[^A-Za-z0-9\-]/', '', $uid)."/".preg_replace('/[^A-Za-z0-9\-]/', '', $coldate);
    // echo exec('net use "\\\192.168.100.46\Clinic\img\\'.$path_str_to.'" /user:"'.$user.'" "'.$password.'" /persistent:no'); // ฟังชั่น command line การเข้าถึง network path     
    $files = scandir('\\\192.168.100.46\Clinic\img\\'.$path_str_to); // scan file

    $target_file = array();
    $target_file = $_FILES["fileToUpload"]["name"];
    // print_r($target_file);
    $uploadOk = 1;
    $error_str = "";
    $imageFileType = array();

    // rtn value
    $rtn_value = array();

    // new loop multi file
    $uploadOk = 0;
    $extension = array("jpeg","jpg","png","gif");

    $cont_pk_log_img = (intval($cont_pk_log_img)+1);

    foreach($target_file as $key=>$tmp_name) {
        $imageFileType[] = strtolower(pathinfo($target_file[$key],PATHINFO_EXTENSION));
        // print_r($imageFileType);
        $file_name = $target_file[$key];
        $file_tmp = $_FILES["fileToUpload"]["tmp_name"][$key];

        // Set dir to
        $target_dir = '\\\192.168.100.46\Clinic\img\\'.$path_str_to.'\\'. $type_img."_".$uid."_".(intval($cont_pk_log_img)).".".$imageFileType[$key];

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"][$key]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $error_str .= "File is not an image.<br>";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_dir)) {
            $error_str .= "Sorry, file already exists.<br>";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"][$key] > 1000000) {
            $error_str .= "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        if(in_array($imageFileType[$key], $extension)) {
            if($uploadOk == 1) {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$key], $target_dir)) {
                    // ins log img
                    $rtn_val = "0";
                    $uid_str_con = "";
                    $uid_str_con = str_replace(' ', '', $uid);
                    $uid_str_con = preg_replace('/-+/', '', $uid);

                    $img_id = "";
                    $img_id = $type_img."_".$uid_str_con."_".(intval($cont_pk_log_img));
                    $current_date = "";
                    $current_date = date('Y-m-d H:i:s');

                    $bind_param = "sssssssssss";
                    $array_val = array();
                    $array_val = array($img_id, (intval($cont_pk_log_img)), $type_img, $imageFileType[$key], $uid_ins_log, $coldate, $coltime, $img_comment, $current_date, $s_id, '0');
                    // print_r($array_val);
                    $query = "INSERT into img_uid_info values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param($bind_param, ...$array_val);

                    if($stmt->execute()){
                        $rtn_value["status"] = $uploadOk;
                        $rtn_value["type_file"] = $imageFileType[$key];
                    }
                    else{
                        echo "error img log: ".$stmt->error."<br>";
                    }
                    $stmt->close();
                    intval($cont_pk_log_img++);
                }
                else{
                    $error_str .= "Sorry, there was an error uploading your file.<br>";
                    $uploadOk = 0;
                    $rtn_value["status"] = $uploadOk;
                    $rtn_value["type_file"] = $error_str;
                }
            }
            else{
                $rtn_value["status"] = $uploadOk;
                $rtn_value["type_file"] = $error_str;
            }
        }
        else{
            $error_str .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }
    }

    echo json_encode($rtn_value);
    $mysqli->close();
?>