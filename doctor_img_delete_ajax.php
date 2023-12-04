<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $img_id = getQS("img_id");
    $img_split = explode("/", $img_id)[8];
    $img_split = explode(".", $img_split)[0];
    $s_id = getSS("s_id");

    $rtn_err = 0;
    $current_date = "";
    $current_date = date('Y-m-d H:i:s');

    if(!unlink($img_id)){
        $rtn_err = 0;
    }
    else{
        $rtn_err = 1;
    }

    if($rtn_err == 1){
        $query = "UPDATE img_uid_info SET upd_date = '$current_date', s_id = '$s_id', status = 1 where img_id = '$img_split';";
        $stmt = $mysqli->prepare($query);
        // $stmt->bind_param($bind_param, ...$array_val);
        if($stmt->execute()){
            $rtn_err = 1;
        }
        else{
            $rtn_err = 0;
        }
    }

    echo $rtn_err;
?>