<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $gClinicid = getSS("clinic_id");
    $gSid = getSS("s_id");
    $gUid = getQS("uid");
    $gCollect_date = getQS("coldate");

    $bind_param = "";
    $array_val = array();
    $data_consent_detail = array();

    $query = "SELECT d_result.uid, 
        max(d_result.collect_date) as collect_date, 
        c_data.date_time_stemp, 
        p_info.fname, 
        p_info.sname, 
        p_info.en_fname, 
        p_info.en_sname, 
        p_info.tel_no, 
        p_info.email
    from p_data_result d_result
    left join consent_data c_data on(c_data.uid = d_result.uid)
    left join patient_info p_info on(p_info.uid = d_result.uid)
    where d_result.collect_date != '0000-00-00' ";

    if($gUid != ""){
        $bind_param .= "s";
        $array_val[] = $gUid;
        $query .= " and d_result.uid = ? ";
    }

    if($gCollect_date != ""){
        $bind_param .= "s";
        $array_val[] = $gCollect_date;
        $query .= " and d_result.collect_date = ? ";
    }

    $query .= " group by d_result.uid
    order by d_result.collect_date DESC, d_result.uid
    limit 200;";

    $stmt = $mysqli->prepare($query);
    if($gUid != "" || $gCollect_date != "")
        $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($uid, $collect_date, $date_time_stemp, $fname, $sname, $en_fname, $en_sname, $tel_no, $email);
        while($stmt->fetch()){
            $data_consent_detail[$uid]["uid"] = $uid;
            $data_consent_detail[$uid]["coldate"] = $collect_date;
            $data_consent_detail[$uid]["name"] = ($fname != ""? $fname." ".$sname : $en_fname." ".$en_sname);
            $data_consent_detail[$uid]["consent_date"] = $date_time_stemp;
            $data_consent_detail[$uid]["tel"] = $tel_no;
            $data_consent_detail[$uid]["email"] = $email;
        }
    }
    // print_r($data_consent_detail);
    $stmt->close();
    $mysqli->close();

    $js_html = "";
    $html_status_consent = "";
    foreach($data_consent_detail as $key_uid => $val){
        if(isset($val["consent_date"])){
            $html_status_consent = '<span class="font-s-3"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></span>';
        }
        else{
            $html_status_consent = '<span class="font-s-3"><i class="fa fa-times" aria-hidden="true" style="color:red"></i></span>';
        }
        $js_html .= '<div class="fl-wrap-row font-s-2 row-hover row-color h-30">
                        <div class="fl-fix w-20" style="background-color: white;"></div>
                        <div class="fl-fix fl-mid link-consent" style="min-width: 60px; max-width: 60px" data-uid="'.$val["uid"].'">
                            <i class="fa fa-book consent-icon-ef fa-stack" aria-hidden="true"></i>
                        </div>
                        <div class="fl-fix fl-mid fl-left" style="min-width: 150px; max-width: 150px">
                            <span>'.$val["uid"].'</span>
                        </div>
                        <div class="fl-fill fl-left">
                            '.$val["name"].'
                        </div>
                        <div class="fl-fix w-150 fl-left">
                            '.$val["tel"].'
                        </div>
                        <div class="fl-fix w-250 fl-left">
                            '.$val["email"].'
                        </div>
                        <div class="fl-fix w-200 fl-mid">
                            '.$val["coldate"].'
                        </div>
                        <div class="fl-fix w-150 fl-mid">
                            '.$html_status_consent.'
                        </div>
                    </div>';
    }

    echo $js_html;
?>

<script>
    $(document).ready(function(){
        $(".link-consent").off("click");
        $(".link-consent").on("click", function(){
            var suid = $(this).data("uid");
            var sUrl_consent_main = "consent_data_main.php?uid="+suid;
            showDialog(sUrl_consent_main, "หนังสือให้ความยินยอมเกี่ยวกับข้อมูลส่วนบุคคล", "100%", "70%", false, function(sResult){}, true, function(sResult){});
        });
    });
</script>