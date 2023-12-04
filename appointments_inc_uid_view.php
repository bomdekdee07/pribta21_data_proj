<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("in_session.php");

    $sClinicID = getSS("clinic_id");
    $sUid = getQS("uid");
    $s_id_ss = getSS("s_id");
    // echo $sUid;

    // $data_uid_view = array("uid" => "", "s_id" => "", "appointment_date" => "", "appointment_time" => "");
    $data_uid_view = array();
    $query = "select uid, 
        appointment_date, 
        appointment_time, 
        s_id,
        service_clinic
    from i_appointment
    where is_confirm = 0
    and uid = ?
    and clinic_id = ?
    -- and appointment_date >= curdate()
    ORDER BY  appointment_date DESC, appointment_time;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss', $sUid, $sClinicID);

    if($stmt->execute()){
        $stmt->bind_result($uid, $appointment_date, $appointment_time, $s_id, $service_clinic);
        while ($stmt->fetch()) {
            $data_uid_view[$uid.$appointment_date.$appointment_time]["uid"] = $uid;
            $data_uid_view[$uid.$appointment_date.$appointment_time]["s_id"] = $s_id;
            $data_uid_view[$uid.$appointment_date.$appointment_time]["appointment_date"] = $appointment_date;
            $data_uid_view[$uid.$appointment_date.$appointment_time]["appointment_time"] = $appointment_time;
            $data_uid_view[$uid.$appointment_date.$appointment_time]["service"] = $service_clinic;
        }	
    }
    // print_r($data_uid_view);

    $stmt->close();
    $mysqli->close();

    if($sUid == ""){
        $sUid = "No UID";
    }

    $html_uid_view = "";
    $html_uid_view .=   '<div class="fl-wrap-row h-30" id="">
                            <div class="fl-fix fl-mid w-200 fw-b">
                                <i class="fa fa-address-card" aria-hidden="true"> <span class="font-s-2"> '.$sUid.'</span></i>
                            </div>
                        </div>
                        <div class="fl-wrap-row h-15 border-bt">
                            <div class="fl-fill fl-mid-right">
                                <buttton class="btn btn-info font-s-1 show-dt" style="padding: 4px 3px 3px 4px;" id="old_detail_appointment">Show all</buttton>
                            </div>
                        </div>';

    $html_uid_view .=   '<div class="fl-wrap-col fl-auto" id="sub_uid_view">';
    
    $today_check = "";
    $class_show = "";
    $today_check = date("Y-m-d");
    foreach($data_uid_view as $key => $value){
        if($value["uid"] != ""){
            $color_row = "";
            if($value["service"] == "1"){
                $color_row = "#518fce";
            }
            else if($value["service"] == "2"){
                $color_row = "#df7639";
            }
            else{
                $color_row = "#1e7e1a";
            }

            
            if($value["appointment_date"] >= $today_check){
                $class_show = "show-befor";
            }
            else{
                $class_show = "show-after";
            }

            $html_uid_view .=   '<div class="fl-wrap-row row-color h-25 smallfont2 fl-mid w-199 fabtn uid-edit '.$class_show.'" data-uid="'.$sUid.'" data-sid="'.$value["s_id"].'" data-date="'.$value["appointment_date"].'" data-time="'.$value["appointment_time"].'" data-clinic="'.$sClinicID.'" data-sidss="'.$s_id_ss.'">';
            $html_uid_view .=       '<i class="fa fa-circle" aria-hidden="true" style="color: '.$color_row.'"></i><span>'.$value["appointment_date"].' เวลา:'.$value["appointment_time"].'</span>';
            $html_uid_view .=   '</div>';
        }
    }

    $html_uid_view .=   '</div>';

    echo $html_uid_view;
?>

<script>
    $(document).ready(function(){
        $("#sub_uid_view .show-after").hide();

        $("#old_detail_appointment").off("click");
        $("#old_detail_appointment").on("click", function(){
            var check_show_dt =  $("div").find(".show-dt").val();
            if(typeof check_show_dt !== "undefined"){
                $("div").find(".show-dt").addClass("hide-dt");
                $("div").find(".show-dt").removeClass("show-dt");
                $(this).html("Hide");
                $("#sub_uid_view .show-after").show();
            }
            else{
                $("div").find(".hide-dt").addClass("show-dt");
                $("div").find(".hide-dt").removeClass("hide-dt");
                $(this).html("Show all");
                $("#sub_uid_view .show-after").hide();
            }
        });

        $("#sub_uid_view .uid-edit").off("click");
        $("#sub_uid_view .uid-edit").click(function(){
            var uid_s = $(this).data("uid");
            var sid_s = $(this).data("sid");
            var appointments_date_s = $(this).data("date");
            var clinic_id_s = $(this).data("clinic");
            var sid_ss_s = $(this).data("sidss");
            var sUrl_appoint = "appointments_main.php?uid="+uid_s+"&data_ss="+sid_s+"&appointment_date="+appointments_date_s+"&clinic_id="+clinic_id_s;
            console.log(sid_s+"/"+sid_ss_s);

            showDialog(sUrl_appoint, "Document Management", "600", "500", "", function(sResult){
                var url_gen_doc = "appointments_inc_uid_view.php?uid="+uid_s+"&s_id="+sid_s;
                var url_gen = "appointments_calendar_function.php?date_res="+appointments_date_s+"&data_ss="+sid_s+"&data_uid="+uid_s+"&clinicid="+clinic_id_s;

                $("#sub_uid_view").parent().load(url_gen_doc);
                if(sid_s == sid_ss_s)
                $("#appointments_calendar #calendar-echo").load(url_gen);
            }, false, function(sResult){});
        });
    });
</script>