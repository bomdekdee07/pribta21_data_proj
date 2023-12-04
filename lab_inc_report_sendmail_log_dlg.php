<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $lab_order_id = getQS("order_labid");

    $bind_param = "ss";
    $array_val = array($uid, $lab_order_id);
    $data_uid_array = array();

    $query = "SELECT collect_date, collect_time 
    from p_lab_order 
    where uid = ? and lab_order_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_uid_array["coldate"] = $row["collect_date"];
            $data_uid_array["coltime"] = $row["collect_time"];
        }
    }
    $stmt->close();

    $bind_param = "sss";
    $array_val = array($uid, $data_uid_array["coldate"], $data_uid_array["coltime"]);
    $data_log_send_eamil = array();

    $query = "SELECT lg.uid,
        lg.email_f,
        lg.email,
        st.s_name,
        lg.upd_date
    from log_send_email_lab_result lg
    left join p_staff st on(st.s_id = lg.s_id)
    where lg.uid = ?
    and lg.collect_date = ?
    and lg.collect_time = ?
    order by lg.upd_date DESC;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_log_send_eamil[$row["upd_date"]]["uid"] = $row["uid"];
            $data_log_send_eamil[$row["upd_date"]]["email_from"] = $row["email_f"];
            $data_log_send_eamil[$row["upd_date"]]["email_to"] = $row["email"];
            $data_log_send_eamil[$row["upd_date"]]["staff"] = $row["s_name"];
            $data_log_send_eamil[$row["upd_date"]]["upd_date"] = $row["upd_date"];
        }
    }
    $stmt->close();
    $mysqli->close();
    
    $html_detail = "";
    foreach($data_log_send_eamil as $key_date => $val){
        $html_detail .= '<div class="fl-wrap-row h-25 font-s-1 row-color row-hover">
                            <div class="fl-fix w-10"></div>
                            <div class="fl-fix w-150 fl-mid ">'.$val["uid"].'</div>
                            <div class="fl-fix w-190 fl-mid-left ">'.$val["email_from"].'</div>
                            <div class="fl-fix w-190 fl-mid-left ">'.$val["email_to"].'</div>
                            <div class="fl-fix w-150 fl-mid-left ">'.$val["staff"].'</div>
                            <div class="fl-fix w-150 fl-mid ">'.$val["upd_date"].'</div>
                            <div class="fl-fix w-150 fl-mid "><button class="btn btn-info" style="padding: 0px 20px 0px 20px;" name="bt_view_txt_interpret_log" data-uid = "'.$val["uid"].'" data-coldate = "'.$data_uid_array["coldate"].'" data-coltime = "'.$data_uid_array["coltime"].'" data-upddate = "'.$val["upd_date"].'"><i class="fa fa-file" aria-hidden="true"></i></button></div>
                            <div class="fl-fix w-10"></div>
                        </div>';
    }
?>

<div class="fl-wrap-col" id="main_log_sendmail_lab_result">
    <div class="fl-wrap-row h-15"></div>
    <div class="fl-wrap-row h-25 font-s-2 fw-b">
        <div class="fl-fix w-10"></div>
        <div class="fl-fix w-150 fl-mid border-bt">UID</div>
        <div class="fl-fix w-190 fl-mid-left border-bt">Email From</div>
        <div class="fl-fix w-190 fl-mid-left border-bt">Email To</div>
        <div class="fl-fix w-150 fl-mid-left border-bt">Staff</div>
        <div class="fl-fix w-150 fl-mid border-bt">Send Date</div>
        <div class="fl-fix w-150 fl-mid border-bt">Interpret</div>
        <div class="fl-fix w-10"></div>
    </div>
    <div class="fl-wrap-col fl-auto">
        <?echo $html_detail; ?>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#main_log_sendmail_lab_result [name=bt_view_txt_interpret_log]").off("click");
        $("#main_log_sendmail_lab_result [name=bt_view_txt_interpret_log]").on("click", function(){
            var uid_s = $(this).attr("data-uid");
            var coldate_s = $(this).attr("data-coldate");
            var coltime_s = $(this).attr("data-coltime");
            var upddate_s = $(this).attr("data-upddate");
            sUrl="lab_inc_report_sendmail_interpret_dlg.php?uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&upd_date="+encodeURI(upddate_s);

            showDialog(sUrl, "Interpret report log", "85%", "70%","",
            function(sResult){
                //CLose function
            },false,function(){
                //Load Done Function
            });
        });
    });
</script>