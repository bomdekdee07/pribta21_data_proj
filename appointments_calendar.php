<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");
    if($sSID == ""){
        exit("Please login again.");
    }
    // echo $sSID."/".$sClinicID;

    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));
    $sSID_check = getQS("s_id") == "undefined"?"":getQS("s_id"); // มีการส่ง undefined มาครับเลขเช็คกันไว้
    $not_QS_sid = true;
    // echo $sSID."QS:".$sSID_check;

    if($sSID_check != ""){
        $sSID = $sSID_check;
    }
    else{
        $not_QS_sid = false;
    }
?>

<?
    // con date all formate to formate colrect
    $date = getQS("date_res");
    if($date == ""){
        $date = null;
    }

    // $date = "2021-05-20";
    date_default_timezone_set("Asia/Bangkok");
    $active_year = $date != null ? date('Y', strtotime($date)) : date('Y');
    $active_month = $date != null ? date('m', strtotime($date)) : date('m');
    $active_day = $date != null ? date('d', strtotime($date)) : date('d');

    $JSvalue_echo = "";
    $JSvalue_echo .= '$("#appointments_calendar [name=appoinments_month]").val("'.$active_month.'");';
    $JSvalue_echo .= '$("#appointments_calendar [name=appoinments_year]").val("'.$active_year.'");';
?>

<div id="appointments_calendar" class="fl-wrap-col appointments-mt-1" style="min-width:1000;">
    <span class="date_defult" data-date=<? echo $active_day; ?> data-months=<? echo $active_month; ?> data-year=<? echo $active_year; ?> data-appointments=<? echo $date != null ? $date:"-"; ?> data-uid="<? echo $sUid; ?>" data-clinicid=<? echo $sClinicID; ?> data-ss=<? echo $sSID; ?> data-colDate="<? echo $sColDate; ?>" data-colTime="<? echo $sColTime; ?>"></span>
    <div class="fl-wrap-row appointments-pd-bt h-40">
        <div class="fl-wrap-col w-200">
            <!-- <div class="fl-wrap-row appointments-pd-bt h-20"></div> -->
            <div class="fl-wrap-row appointments-pd-bt h-45 smallfont2 fw-b">
                <div class="fl-fill fl-mid-left ml-1">
                    <label><input type="checkbox" class="bigcheckbox filter-clinic" id="chkFilterPt" checked> <span>PT: <i class="fa fa-circle" aria-hidden="true" style="color: #518fce;"></i></span></label>
                </div>
                <div class="fl-fill fl-mid-left ml-1">
                    <label><input type="checkbox" class="bigcheckbox filter-clinic" id="chkFilterTg" checked><span>TG: <i class="fa fa-circle" aria-hidden="true" style="color: #df7639;"></i></span></label>
                </div>
                <div class="fl-fill fl-mid-left ml-1">
                    <label><input type="checkbox" class="bigcheckbox filter-clinic" id="chkFilterRs" checked><span>RS: <i class="fa fa-circle" aria-hidden="true" style="color: #1e7e1a;"></i></span></label>
                </div>
            </div>
        </div>
        <div class="fl-wrap-col" style="min-width: 800px;">
            <div class="fl-wrap-row appointments-pd-bt">
                <div class="fl-fix appointments-bt-1 appointments-ml-0">
                    <button id="appointments_today" onclick="appointments_reload(this.form, 'today')" class="btn smallfont2"><span><b>Today</b></span></button>
                </div>
                <div class="fl-fix appointments-bt-0 appointments-ml-1">
                    <button id="appointments_back_month" onclick="appointments_reload(this.form, 'back')" class="btn smallfont2"><span><b><i class="fa fa-chevron-left"></i></b></span></button>
                </div>
                <div class="fl-fix appointments-dd appointments-ml-1 smallfont2">
                    <b><span class="language_en">Month</span>
                    <span class="language_th">เดือน</span></b>
                    <select name="appoinments_month" data-id="appoinments_month" onchange="appointments_reload(this.form)" class="input-group">
                        <option value="">Please Select.</option>
                        <option value="01">มกราคม</option>
                        <option value="02">กุมภาพันธ์</option>
                        <option value="03">มีนาคม</option>
                        <option value="04">เมษายน</option>
                        <option value="05">พฤษภาคม</option>
                        <option value="06">มิถุนายน</option>
                        <option value="07">กรกฎาคม</option>
                        <option value="08">สิงหาคม</option>
                        <option value="09">กันยายน</option>
                        <option value="10">ตุลาคม</option>
                        <option value="11">พฤศจิกายน</option>
                        <option value="12">ธันวาคม</option>
                    </select>
                </div>
                <div class="fl-fix appointments-dd appointments-ml-1 smallfont2">
                    <b><span class="language_en">Year</span>
                    <span class="language_th">ปี</span></b>
                    <select name="appoinments_year" data-id="appoinments_year" onchange="appointments_reload(this.form)" class="input-group">
                        <!-- Append function auto year -->
                    </select>
                </div>
                <div class="fl-fix appointments-bt-0 appointments-ml-3">
                    <button id="appointments_next_month" onclick="appointments_reload(this.form, 'next')" class="btn smallfont2"><span><b><i class="fa fa-chevron-right"></i></b></span></button>
                </div>
                <div class="fl-fix appointments-bt-2 appointments-ml-1">
                    <button id="appointments_next_3month" onclick="appointments_reload(this.form, 'next_3')" class="btn smallfont2"><span><b>Next 3 Months</b></span></button>
                </div>
                <div class="fl-fix appointments-bt-2 appointments-ml-1">
                    <button id="appointments_next_6month" onclick="appointments_reload(this.form, 'next_6')" class="btn smallfont2"><span><b>Next 6 Months</b></span></button>
                </div>
                <div class="fl-fill"></div>
                <div class="fl-fix appointments-dd appointments-ml-1 appointments-mr-1 smallfont2">
                    <b><span class="language_en">Doctor</span>
                    <span class="language_th">ชื่อ</span></b>
                    <select name='appoinments_doctor' data-id='appoinments_doctor' class='input-group'>
                        <? $data_id = "staff_md"; $data_result_staff = ""; include("doctor_opt_staff_md.php"); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="fl-fill fl-auto">
        <div class="fl-wrap-col">
            <div class='fl-wrap-row appointments-pd-bt calendar-echo' id="calendar-echo">
                <!-- Include file appointment function -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(".language_en").hide();

        // Function auto strat and end year
        $("#appointments_calendar [name=appoinments_year]").each(function(index, obj){
            appointment_auto_year(3, 5, $(this));
        });

        // input value show data
        <? echo $JSvalue_echo?>

        $("#appointments_today").unbind("click");
        $("#appointments_next_month").unbind("click");
        $("#appointments_next_3month").unbind("click");
        $("#appointments_next_6month").unbind("click");

        // first page load data
        if($("#appointments_calendar .date_defult").data("appointments") == "-"){
            appointments_reload(this.form, 'today');
        }
        else{
            appointments_reload(this.form, 'param_data');
        }

        var coldate_s = $("#appointments_calendar .date_defult").data("colDate");
        var coltime_s = $("#appointments_calendar .date_defult").data("colTime");

        // Select list name doctor
        $("#appointments_calendar [name=appoinments_doctor]").unbind("change");
        $("#appointments_calendar [name=appoinments_doctor]").on("change", function(){
            var month_val_list = $("#appointments_calendar [name=appoinments_month]").val(); // collect month value
            var year_val_list = $("#appointments_calendar [name=appoinments_year]").val(); // collect year value

            var aData = {
                date_res: $("#appointments_calendar .date_defult").data("appointments") != ""? year_val_list+"-"+month_val_list+"-01": null,
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $("#appointments_calendar [name=appoinments_month]").val($("#appointments_calendar [name=appoinments_month]").val());
            $("#appointments_calendar [name=appoinments_year]").val($("#appointments_calendar [name=appoinments_year]").val());

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        });

        $("#appointments_calendar .filter-clinic").off("change");
        $("#appointments_calendar .filter-clinic").on("change", function(){
            filter_clinic();
        });
    });

    function appointment_auto_year(back_year, next_year, element){
        var d = new Date();
        var curYear = d.getFullYear();
        var back_date = (curYear-back_year);
        var next_date = (curYear+next_year);
        var temp_st = "<option value=''>Please Select.</option>";

        for(var n=back_date;n <= next_date; n++){
            temp_st += "<option value='"+n+"'>"+n+"</option>";
        }
        
        element.children().remove();
        element.append(temp_st);
        filter_clinic();
    }

    function appointments_reload(form, type_button){
        var month_val = $("#appointments_calendar [name=appoinments_month]").val(); // collect month value
        var year_val = $("#appointments_calendar [name=appoinments_year]").val(); // collect year value
        var coldate_s = $("#appointments_calendar .date_defult").data("coldate");
        var coltime_s = $("#appointments_calendar .date_defult").data("coltime");

        if(type_button === undefined){ 
            var aData = {
                date_res:year_val+"-"+month_val+"-01",
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $("#appointments_calendar [name=appoinments_month]").val(month_val);
            $("#appointments_calendar [name=appoinments_year]").val(year_val);

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "today"){
            var aData = {
                date_res: null,
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            var d = new Date();
            var month = d.getMonth()+1;
            var year = d.getFullYear();

            $("#appointments_calendar [name=appoinments_month]").val(appointments_convert_months(month));
            $("#appointments_calendar [name=appoinments_year]").val(appointments_convert_months(year));

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "back"){
            month_val = parseInt(month_val)-1;
            if(month_val > 12){
                month_val = (month_val-12);
                year_val = parseInt(year_val)-1;
            }

            $("#appointments_calendar [name=appoinments_month]").val(appointments_convert_months(month_val));
            $("#appointments_calendar [name=appoinments_year]").val(appointments_convert_months(year_val));

            var check_date = $("#appointments_calendar .date_defult").data("appointments").substr(0, $("#appointments_calendar .date_defult").data("appointments").indexOf("-")+3);
            var aData = {
                date_res: check_date == year_val+"-"+appointments_convert_months(month_val)? $("#appointments_calendar .date_defult").data("appointments") : year_val+"-"+appointments_convert_months(month_val)+"-01",
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "next"){
            month_val = parseInt(month_val)+1;
            if(month_val > 12){
                month_val = (month_val-12);
                year_val = parseInt(year_val)+1;
            }

            $("#appointments_calendar [name=appoinments_month]").val(appointments_convert_months(month_val));
            $("#appointments_calendar [name=appoinments_year]").val(appointments_convert_months(year_val));

            var check_date = $("#appointments_calendar .date_defult").data("appointments").substr(0, $("#appointments_calendar .date_defult").data("appointments").indexOf("-")+3);
            var aData = {
                date_res: check_date == year_val+"-"+appointments_convert_months(month_val)? $("#appointments_calendar .date_defult").data("appointments"): year_val+"-"+appointments_convert_months(month_val)+"-01",
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "next_3"){
            console.log("IN3");
            month_val = parseInt(month_val)+3;
            if(month_val > 12){
                month_val = (month_val-12);
                year_val = parseInt(year_val)+1;
            }

            debugger;
            
            $("#appointments_calendar [name=appoinments_month]").val(appointments_convert_months(month_val));
            $("#appointments_calendar [name=appoinments_year]").val(appointments_convert_months(year_val));

            var check_date = $("#appointments_calendar .date_defult").data("appointments").substr(0, $("#appointments_calendar .date_defult").data("appointments").indexOf("-")+3);

            var aData = {
                date_res: $("#appointments_calendar .date_defult").data("appointments") != ""? check_date == year_val+"-"+appointments_convert_months(month_val)? $("#appointments_calendar .date_defult").data("appointments"): year_val+"-"+appointments_convert_months(month_val)+"-01": year_val+"-"+month_val+"-01",
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "next_6"){
            month_val = parseInt(month_val)+6;
            if(month_val > 12){
                month_val = (month_val-12);
                year_val = parseInt(year_val)+1;
            }

            $("#appointments_calendar [name=appoinments_month]").val(appointments_convert_months(month_val));
            $("#appointments_calendar [name=appoinments_year]").val(appointments_convert_months(year_val));

            var check_date = $("#appointments_calendar .date_defult").data("appointments").substr(0, $("#appointments_calendar .date_defult").data("appointments").indexOf("-")+3);

            var aData = {
                date_res: $("#appointments_calendar .date_defult").data("appointments") != ""? check_date == year_val+"-"+appointments_convert_months(month_val)? $("#appointments_calendar .date_defult").data("appointments"): year_val+"-"+appointments_convert_months(month_val)+"-01": year_val+"-"+month_val+"-01",
                data_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $.ajax({url: "appointments_calendar_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
        else if(type_button == "param_data"){
            $("#appointments_calendar [name=appoinments_month]").val($("#appointments_calendar .date_defult").data("months"));
            $("#appointments_calendar [name=appoinments_year]").val($("#appointments_calendar .date_defult").data("year"));

            var aData = {
                date_res: $("#appointments_calendar .date_defult").data("appointments"),
                data_ss: $("#appointments_calendar .date_defult").data("ss"),
                data_check_ss: $("#appointments_calendar [name=appoinments_doctor]").val(),
                data_uid: $("#appointments_calendar .date_defult").data("uid"),
                clinicid: $("#appointments_calendar .date_defult").data("clinicid"),
                coldate: coldate_s,
                coltime: coltime_s
            };

            $.ajax({url: "appointments_calendar_function.php",
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#appointments_calendar .calendar-echo").children().remove();
                    $("#appointments_calendar .calendar-echo").append(result);

                    filter_clinic();
            }});
        }
    }

    function appointments_convert_months(months){
        var months_str = months.toString();
        months_str = months_str.padStart(2, "0");

        return months_str;
    }

    function filter_clinic(){
        var chk_pt = $("#appointments_calendar #chkFilterPt").is(":checked");
        var chk_tg = $("#appointments_calendar #chkFilterTg").is(":checked");
        var chk_rs = $("#appointments_calendar #chkFilterRs").is(":checked");

        if(chk_pt)
        $("#appointments_calendar .event-sub-pt").show();
        else
        $("#appointments_calendar .event-sub-pt").hide();

        if(chk_tg)
        $("#appointments_calendar .event-sub-tg").show();
        else
        $("#appointments_calendar .event-sub-tg").hide();

        if(chk_rs)
        $("#appointments_calendar .event-sub-rs").show();
        else
        $("#appointments_calendar .event-sub-rs").hide();
    
    }
</script>