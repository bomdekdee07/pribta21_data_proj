<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    if($sSID == null){
        $sSID = getQS("s_id");
    }
    $sClinicID = getSS("clinic_id");
    if($sClinicID == null){
        $sClinicID = getQS("clinic_id");
    }
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));

    // Function convert date to formate Thai
    function DateThai($strDate)
	{
		$strYear = date("Y",strtotime($strDate))+543;
		$strMonth= date("n",strtotime($strDate))-1;
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
		$strMonthThai=$strMonthCut[$strMonth];
        $edaytxt = array("Sunday" => "อาทิตย์","Monday" => "จันทร์","Tuesday" => "อังคาร","Wednesday" => "พุธ","Thursday" => "พฤหัสบดี","Friday" => "ศุกร์","Saturday" => "เสาร์");

        $date_name = date ("l", strtotime($strDate));

		return "$edaytxt[$date_name] $strDay $strMonthThai $strYear, $strHour:$strMinute";
	}

    // Appointments Date
    $d_appointments_date = "";
    $d_send_appointments_date = "";
    $query = "SELECT appointment_date, appointment_time from i_appointment
    where uid = ?
    and clinic_id = ?
    and is_confirm = 0
    and appointment_date > CURDATE()
    order by appointment_date DESC";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("ss", $sUid, $sClinicID);

    if($stmt -> execute()){
        $stmt -> bind_result($appointment_date, $appointment_time);

        while($stmt -> fetch()){
            $d_appointments_date = $appointment_date." ".$appointment_time;
            $d_send_appointments_date = $appointment_date;
        }
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS_appointment = "";

    // input val appointments date
    if($d_appointments_date != ""){
        $d_appointments_date = DateThai($d_appointments_date);
        $sJS_appointment .= '$("input[name=nextvisit_date]").val("'.$d_appointments_date.'");';
        $sJS_appointment .= '$("input[name=nextvisit_date]").attr("data-odata","'.$d_appointments_date.'");';

        //nextvisit_date_send
        $sJS_appointment .= '$("input[name=nextvisit_date_send]").val("'.$d_send_appointments_date.'");';
        $sJS_appointment .= '$("input[name=nextvisit_date_send]").attr("data-odata","'.$d_send_appointments_date.'");';
    }
?>

<div id="appointment_box" data-uid="<? echo $sUid; ?>" data-ss="<? echo $sSID; ?>" data-clinicid="<? echo $sClinicID; ?>" data-coldate="<? echo $sColDate; ?>" data-coltime="<? echo $sColTime; ?>">
<div class="fl-warp-col doctor-appointments">
    <div class="fl-wrap-row">
        <div class="fl-fix smallfont2 appointments-ml-1 appointments-mt-3" style='min-width:56px'>
            <b><span class='language_en'><label>Next visit:</label></span><span class='language_th'><label>วันนัด:</label></span></b>
        </div>
        <div class='fl-fix appointments-mt-4' name="appointments_schedule" style='min-width:168px'>
            <input type='text' id="nextvisit_date" name='nextvisit_date' data-id='nextvisit_date' data-require='' data-odata='' class='save-data v_text smallfont2 input-group' value='' readonly>
            <input type="hidden" name='nextvisit_date_send' data-id='nextvisit_date_send' data-require='' data-odata='' class='save-data v_text smallfont2 input-group' value=''>
        </div>
        <div class="fl-fix" style="min-width: 3px;"></div>
        <div class="fl-fix appointments-mt-4" style="min-width: 35px;">
            <button class="btn smallfont2 input-group appointments-calendar" style="text-align: center;" title="เลือกวันนัด"><i class="fa fa-calendar" aria-hidden="true"></i></button>
        </div>
        <div class="fl-fix" style="min-width: 3px;"></div>
        <div class="fl-fix appointments-mt-4" style="min-width: 35px;">
            <button class="btn smallfont2 input-group" id="print-appointment" style="text-align: center;" title="พิมพ์"><i class="fa fa-print" aria-hidden="true"></i></button>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function(){
        $(".language_en").hide();

        // echo value or result
        <? echo $sJS_appointment; ?>

        // OnClick appointments date
        var uid_send = $("#appointment_box").data("uid");
        var date_res = $("#appointment_box input[name=nextvisit_date_send]").val();
        var clinic_id = $("#appointment_box").data("clinicid");
        var coldate_s = $("#appointment_box").data("coldate");
        var coltime_s = $("#appointment_box").data("coltime");
        var s_id = $("#appointment_box").data("ss");
        var sUrl_appoint = "appointments_calendar.php?uid="+uid_send+"&date_res="+date_res+"&data_ss="+s_id+"&clinic_id="+clinic_id+"&coldate="+coldate_s+"&coltime="+coltime_s;

        // Open Dlg appointment calendar
        $("#appointment_box .appointments-calendar").unbind("click");
        $("#appointment_box .appointments-calendar").on("click", function(){
            showDialog(sUrl_appoint, "Schedule an appointment", "750", "1300", "", function(sResult){
                var url_gen_doc = "doctor_inc_appointments.php?uid="+uid_send+"&clinic_id="+clinic_id+"&s_id="+s_id+"&coldate="+coldate_s+"&coltime="+coltime_s;

                $("#appointment_box").parent().load(url_gen_doc);
            }, false, function(sResult){});
        });

        $("#appointment_box #print-appointment").unbind("click");
        $("#appointment_box #print-appointment").click(function(){
            if($("#nextvisit_date").val() != ""){
                var uid_send = $("#appointment_box").data("uid");
                var clinic_id = $("#appointment_box").data("clinicid");
                var gen_url = "appointments_print_pdf.php?uid="+uid_send+"&clinic_id="+clinic_id+"&coldate="+coldate_s+"&coltime="+coltime_s;
                window.open(gen_url, 'Appointment document');
            }
            else{
                alert("ไม่มีข้อมูลวันนัดคนไข้");
            }
        });
    });
</script>