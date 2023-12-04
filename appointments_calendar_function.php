<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include("in_session.php");

    // GET Ajax
    $date = isset($_POST["date_res"])?$_POST["date_res"]:"";
    if($date == ""){
        $date = null;
    }

    $sSID = isset($_POST["data_ss"])?$_POST["data_ss"]:"";
    if($sSID == ""){
        $sSID = null;
    }

    $sClinicID = getSS("clinic_id");

    if($sClinicID == ""){
        exit("Please login again.");
    }

    $check_ssID = isset($_POST["data_check_ss"])?$_POST["data_check_ss"]:"";
    if($check_ssID == ""){
        $check_ssID = null;
    }

    if($sSID != null){
        if($check_ssID != $sSID){
            $sSID = null;
        }
    }
    
    $U_ID = isset($_POST["data_uid"])?$_POST["data_uid"]:"";
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));

    // GET Query String
    if($U_ID == ""){
        $date = getQS("date_res");
        $sSID = getQS("data_ss");
        $U_ID = getQS("data_uid");
        $sClinicID = getQS("clinicid");
    }
    // echo "date string: ".$U_ID;

    // function convert month to formate string
    function appointments_convert_months($months){
        $months_str = "";
        $months_str = $months;
        if($months_str < 10){
            $months_str = "0".$months_str;
            // echo "BOM".$months_str;
        }
        else{
            $months_str = $months;
        }

        return $months_str;
    }

    // function convert single quote jquery
    function convert_singel_c($value_S){
        $values_con = "'".$value_S."'";

        return $values_con;
    }

    // echo "DATA: ".$date."/".$sSID."/".$U_ID."/".$sClinicID;

    if($sSID == null){
        $live_off_date = array();
        $query = "select m.clinic_id, m.holiday_date, m.s_id, n.s_name, m.holiday_title
        from i_holiday m
            left join p_staff n on(m.s_id = n.s_id)
        where m.clinic_id = ?
        ORDER BY m.holiday_date;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $sClinicID);

        if($stmt->execute()){
            $stmt->bind_result($clinic_id, $holiday_date, $s_id, $s_name, $tital);
            while($stmt -> fetch()){
                $live_off_date[$holiday_date.$s_id]["holiday"] = $holiday_date;
                $live_off_date[$holiday_date.$s_id]["s_id"] = $s_id;
                $live_off_date[$holiday_date.$s_id]["s_name"] = $s_name;
                $live_off_date[$holiday_date.$s_id]["tital"] = $tital;
            }
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        $data_event = array();
        $query = "select uid, appointment_date, appointment_time, s_id, service_clinic
        from i_appointment
        where is_confirm = 0
        ORDER BY  appointment_date, appointment_time;";

        $stmt = $mysqli -> prepare($query);

        if($stmt->execute()){
            $stmt->bind_result($uid, $appointment_date, $appointment_time, $s_id, $service_clinic);
            while($stmt -> fetch()){
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["uid"] = $uid;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["appointment_date"] = $appointment_date;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["appointment_time"] = $appointment_time;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["s_id"] = $s_id;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["service"] = $service_clinic;
                // print($data_event[$appointment_date.$appointment_time]["appointment_date"]."<br>");
            }
            // print_r($data_event);
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();
    }else{
        $live_off_date = array();
        $query = "select m.clinic_id, m.holiday_date, m.s_id, n.s_name, m.holiday_title
        from i_holiday m
            left join p_staff n on(m.s_id = n.s_id)
        where m.clinic_id = ?
            and m.s_id = ?
            or m.s_id = 'none'
        ORDER BY m.holiday_date;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sClinicID, $sSID);

        if($stmt->execute()){
            $stmt->bind_result($clinic_id, $holiday_date, $s_id, $s_name, $tital);
            while($stmt -> fetch()){
                $live_off_date[$holiday_date]["holiday"] = $holiday_date;
                $live_off_date[$holiday_date]["s_id"] = $s_id;
                $live_off_date[$holiday_date]["s_name"] = $s_name;
                $live_off_date[$holiday_date]["tital"] = $tital;
            }
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();

        $data_event = array();
        $query = "select uid, appointment_date, appointment_time, s_id, service_clinic
        from i_appointment
        where is_confirm = 0
        and s_id = ?
        ORDER BY  appointment_date, appointment_time;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $sSID);

        if($stmt->execute()){
            $stmt->bind_result($uid, $appointment_date, $appointment_time, $s_id, $service_clinic);
            while($stmt -> fetch()){
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["uid"] = $uid;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["appointment_date"] = $appointment_date;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["appointment_time"] = $appointment_time;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["s_id"] = $s_id;
                $data_event[$appointment_date.$appointment_time.$uid.$s_id]["service"] = $service_clinic;
                // print($data_event[$appointment_date.$appointment_time]["uid"]."<br>");
            }
            // print_r($data_event);
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();
    }

    $clinic_holiday_main = array();
    $query = "select clinic_id, clinic_holiday 
    from p_clinic 
    where clinic_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $sClinicID);
    
    if($stmt->execute()){
        $stmt->bind_result($clinic_id, $clinic_holiday);
        while($stmt -> fetch()){
            $clinic_holiday_main[$clinic_id]["array_date"] = explode(',', $clinic_holiday);
        }
        // print_r($clinic_holiday_main);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();

    $mysqli->close();

    $count_n = 0;
    $html = "";
    $html_head = "";
    // con date all formate to formate colrect
    // $date = "2021-05-20";
    $active_year = $date != null ? date('Y', strtotime($date)) : date('Y');
    $active_month = $date != null ? date('m', strtotime($date)) : date('m');
    $active_day = $date != null ? date('d', strtotime($date)) : date('d');

    $JSvalue_echo = "";
    $JSvalue_echo .= '$("#appointments_calendar [name=appoinments_month]").val("'.$active_month.'");';
    $JSvalue_echo .= '$("#appointments_calendar [name=appoinments_year]").val("'.$active_year.'");';

    // end total date per month
    $num_days = date('t', strtotime($active_day . '-' . $active_month . '-' . $active_year));
    // echo $num_days."<br>";

    // last date previous month
    $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($active_day . '-' . $active_month . '-' . $active_year)));
    // echo $num_days_last_month."<br>";

    // array match number day with name day
    $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

    // first day of this week 
    $first_day_of_week = array_search(date('D', strtotime($active_year . '-' . $active_month . '-1')), $days);
    // echo $first_day_of_week."<br>";

    $html_head .= '<div class="fl-wrap-col border w-200 h-300 border" id="have_uid_appointment" data-coldate="'.$sColDate.'" data-coltime="'.$sColTime.'">';
    // ตัดไป include
    // ตัดไป include END
    $html .=    '</div>';
    $html .= '<div class="fl-wrap-col border w-800">';
    $html .=    '<div class="fl-wrap-row h-30">';
    foreach ($days as $day) {
    $html .=        '<div class="fl-fix appointments-day_name">
                        ' . $day . '
                    </div>';
    }
    $html .=    '</div>';
    $html .=    '<div class="fl-wrap-row">';
    for ($i = $first_day_of_week; $i > 0; $i--) {
    $html .=        '<div class="fl-fix appointments-day_num appointments-ignore">
                        ' . ($num_days_last_month-$i+1) . '
                    </div>';
        $count_n = $count_n+1;
    }
    
    $count_n = $count_n+1;
    
    for ($i = 1; $i <= $num_days; $i++) {
        $selected = '';
        $event_off = '';
        $day_num = ' fa-stack appointments-day-select" onclick="onClick_events_create('.convert_singel_c($U_ID).', '.convert_singel_c($sSID).', '.convert_singel_c(appointments_convert_months($i)).', '.convert_singel_c($sClinicID).')"';
        // $day_num = ' fa-stack appointments-day-select';
        $css_even = '';
        foreach($live_off_date as $key=>$value){
            // print "IN".":".$key."::".$value["holiday"];
            if ($value["holiday"] == $active_year."-".$active_month."-".appointments_convert_months($i) && $value["s_id"] == "none"){
                $selected .= ' appointments-ignore-off';
                $day_num = '"';
                $event_off = '<i class="fa fa-window-close" style="font-size:11px; color: black"></i> <b><span>'.$value["tital"].'</span></b><br>';
                $css_even = 'fl-auto event';
            }
            else if($value["holiday"] == $active_year."-".$active_month."-".appointments_convert_months($i) && $value["s_id"] != "none" && $sSID == null){
                $selected .= ' appointments-ignore-live';
                // $day_num .= ' appointments-mb-01';
                $event_off .= '<span class="overflow ellipsis staff-live" onclick="onClick_holiday('.convert_singel_c($value["s_id"]).', '.convert_singel_c($sClinicID).', '.convert_singel_c($active_year."-".$active_month."-".appointments_convert_months($i)).', '.convert_singel_c($U_ID).')"><i class="fa fa-window-close" style="font-size:11px; color: red"></i> <b>'.$value["s_name"].'</b></span><br>';
                $css_even = 'fl-auto event';
            }
            else if($value["holiday"] == $active_year."-".$active_month."-".appointments_convert_months($i) && $value["s_id"] != "none" && $sSID != null){
                $selected .= ' appointments-ignore-live';
                $day_num = ' appointments-mb-01"';
                $event_off .= '<span class="overflow ellipsis staff-live" onclick="onClick_holiday('.convert_singel_c($value["s_id"]).', '.convert_singel_c($sClinicID).', '.convert_singel_c($active_year."-".$active_month."-".appointments_convert_months($i)).', '.convert_singel_c($U_ID).')"><i class="fa fa-window-close" style="font-size:11px; color: red"></i> <b>'.$value["s_name"].'</b></span><br>';
                $css_even = 'fl-auto event';
            }
        }
        
        foreach($clinic_holiday_main as $key => $value){
            for($n = 0; $n < count($value["array_date"]); $n++){
                if(($count_n-1) == $value["array_date"][$n]){
                    $selected .= ' appointments-ignore-off';
                    $day_num = '"';
                    $event_off = '<i class="fa fa-window-close" style="font-size:11px; color: black"></i> <b><span>Clinic Off.</span></b><br>';
                    $css_even = 'fl-auto event';
                }
            }
        }

        $event_action = '';
        foreach($data_event as $key => $value){
            $convert_time_str = substr($value["appointment_time"], 0, 5);
            $class_colore = "";

            if($value["service"] == "1"){
                $class_colore = "event-sub-pt";
            }
            else if($value["service"] == "2"){
                $class_colore = "event-sub-tg";
            }
            else{
                $class_colore = "event-sub-rs";
            }

            // echo "IN even:".$key.":".$value["appointment_date"]."/".$value["uid"]."<br>";
            if($value["appointment_date"] == $active_year."-".$active_month."-".appointments_convert_months($i) && $U_ID != $value["uid"]){
                $event_action .= '<div class="fl-fix '.$class_colore.' appointments-select">';
                $event_action .= '<span onclick="onClick_events('.convert_singel_c($value["uid"]).', '.convert_singel_c($value["s_id"]).', '.convert_singel_c($value["appointment_date"]).', '.convert_singel_c($sClinicID).')"><a href="#"><i class="fa fa-edit" style="font-size:11px; color: #74F044"></i></a> <span>'.$value["uid"].' - '.$convert_time_str.'</span></span><br>';
                $event_action .= '</div>';
                $css_even = ' fl-auto event';
                // print "TEST:".$value["uid"].":".$value["appointment_date"]."/".$active_year."-".$active_month."-".appointments_convert_months($i)."/".$sUID."<br>";
            }
            else if($value["appointment_date"] == $active_year."-".$active_month."-".appointments_convert_months($i) && $U_ID == $value["uid"]){
                $event_action .= '<div class="fl-fix '.$class_colore.' appointments-select-selected appointments-select">';
                $event_action .= '<span onclick="onClick_events('.convert_singel_c($value["uid"]).', '.convert_singel_c($value["s_id"]).', '.convert_singel_c($value["appointment_date"]).', '.convert_singel_c($sClinicID).')"><a href="#"><i class="fa fa-edit" style="font-size:11px; color: #74F044"></i></a> <span>'.$value["uid"].' - '.$convert_time_str.'</span></span><br>';
                $event_action .= '</div>';
                $css_even = ' fl-auto event';
            }
        }
    
        if ($i == $active_day) {
            $selected .= ' selected';
        }
        $html .=    '<div class="fl-fix appointments-day_num' . $selected . '">';
        $html .=     '<span class="'.$day_num.'>'.$i.'</span>';
        $html .=        '<div class="fl-fill'.$css_even.'">';
        $html .=            $event_off;
        $html .=            $event_action;
        $html .=        '</div>';
        $html .=    '</div>';
        if($count_n == 7){
            $html .=    '</div>';
            $html .= '<div class="fl-wrap-row h-105 count-row-appoint">';
            $count_n = 0;
        }
        $count_n = $count_n+1;
    }
?>
<? echo $html_head; ?>
<? $_GET["uid"] = $U_ID; include("appointments_inc_uid_view.php"); ?>
<? echo $html; ?>
</div>

<script>
    $(document).ready(function(){
        <? echo $JSvalue_echo; ?>
    });

    function onClick_events(uid, sid, appointments_date, clinic_id){
        var coldate_s = $("#have_uid_appointment").data("coldate");
        var coltime_s = $("#have_uid_appointment").data("coltime");
        var sUrl_appoint = "appointments_main.php?uid="+uid+"&data_ss="+sid+"&appointment_date="+appointments_date+"&clinic_id="+clinic_id+"&coldate="+coldate_s+"&coltime="+coltime_s;
        // console.log(sUrl_appoint);

        showDialog(sUrl_appoint, "Appointments Main", "600", "500", "", function(sResult){
            if(sResult != "1"){
                var date_res = $("#appointments_main [name=appointment_date]").val();
                // var data_ss = $("#appointments_main [name=s_id]").val();
                var data_ss = $("#appointments_calendar [name=appoinments_doctor]").val();
                var data_uid = $("#appointments_calendar .date_defult").data("uid");//$("#appointments_main [name=uid]").val();
                var clinicid = $("#appointments_main [name=clinic_id]").val();
                var url_gen = "appointments_calendar_function.php?date_res="+date_res+"&data_ss="+data_ss+"&data_uid="+data_uid+"&clinicid="+clinicid+"&coldate="+coldate_s+"&coltime="+coltime_s;
                // console.log(url_gen);

                $("#appointments_calendar #calendar-echo").load(url_gen, function(){
                    filter_clinic();
                });
            }
        }, false, function(sResult){});
    }

    function onClick_events_create(uid, sid, appointments_date, clinic_id){
        var coldate_s = $("#have_uid_appointment").data("coldate");
        var coltime_s = $("#have_uid_appointment").data("coltime");
        var date_con = $("#appointments_calendar [name=appoinments_year]").val()+"-"+$("#appointments_calendar [name=appoinments_month]").val()+"-"+appointments_date;
        var sUrl_appoint = "appointments_main.php?uid="+uid+"&data_ss="+sid+"&appointment_date="+date_con+"&clinic_id="+clinic_id+"&create=true"+"&coldate="+coldate_s+"&coltime="+coltime_s;
        var check_name_doc =  $("[name=appoinments_doctor]").val();

        if(check_name_doc != ""){
            showDialog(sUrl_appoint, "Appointments Main", "600", "500", "", function(sResult){
                if(sResult != "1"){
                    var date_res = $("#appointments_calendar [name=appoinments_year]").val()+"-"+$("#appointments_calendar [name=appoinments_month]").val()+"-01";//$("#appointments_main [name=appointment_date]").val();
                    // var data_ss = $("#appointments_main [name=s_id]").val();
                    var data_ss = $("#appointments_calendar [name=appoinments_doctor]").val();
                    var data_uid = $("#appointments_calendar .date_defult").data("uid");//$("#appointments_main [name=uid]").val();
                    var clinicid = $("#appointments_main [name=clinic_id]").val();
                    var url_gen = "appointments_calendar_function.php?date_res="+date_res+"&data_ss="+data_ss+"&data_uid="+data_uid+"&clinicid="+clinicid+"&coldate="+coldate_s+"&coltime="+coltime_s;
                    // console.log(url_gen);

                    $("#appointments_calendar #calendar-echo").load(url_gen, function(){
                        filter_clinic();
                    });
                }
            }, false, function(sResult){});
        }
        else{
            alert("กรุณาเลือกชื่อคุณหมอหรือคอนเซลเลอร์");
        }
    }

    function onClick_holiday(sid, clinic_id, appointments_date, uid){
        var coldate_s = $("#have_uid_appointment").data("coldate");
        var coltime_s = $("#have_uid_appointment").data("coltime");
        var sUrl_appoint = "holiday_management_edit_create.php?s_id="+sid+"&clinic_id="+clinic_id+"&date_res="+appointments_date+"&coldate="+coldate_s+"&coltime="+coltime_s;;
        // console.log("open: "+sUrl_appoint);

        showDialog(sUrl_appoint, "Holiday Information", "500", "500", "", function(sResult){
            if(sResult != "1"){
                var date_res = appointments_date;
                var data_ss = sid;
                var data_uid = uid;
                var clinicid = clinic_id;
                var url_gen = "appointments_calendar_function.php?date_res="+date_res+"&data_ss="+data_ss+"&data_uid="+data_uid+"&clinicid="+clinicid+"&coldate="+coldate_s+"&coltime="+coltime_s;;
                // console.log("close: "+url_gen);

                $("#appointments_calendar #calendar-echo").load(url_gen, function(){
                    filter_clinic();
                });
            }
        }, false, function(sResult){});
    }
</script>