<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getQS("data_ss");
    $sUid = getQS("uid");
    $appointment_date_res = getQS("appointment_date");
    $clinic_id = getQS("clinic_id");
    $create = getQS("create");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));
    // echo "TEST:".$sSID."/".$sUid."/".$appointment_date_res."/".$clinic_id;

    $mode_ins_up = $create != "true"? "update" : "insert";

    $data_appointmentd = array();
    if($mode_ins_up != "insert"){
        $query = "select m.remark, n.fname, n.sname, m.appointment_time, m.is_confirm, m.appointment_date, m.service_clinic
        from i_appointment as m
            left join patient_info as n on (m.uid = n.uid)
        where m.is_confirm = 0
        and m.s_id = ?
        and m.appointment_date = ?
        and m.uid = ?
        and m.clinic_id = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssss',$sSID, $appointment_date_res, $sUid, $clinic_id); // echo "query : $query";

        if($stmt->execute()){
            $stmt->bind_result($remark, $fname, $sname, $appointment_time, $is_confirm, $appointment_date, $service_clinic);
            while ($stmt->fetch()) {
                $data_appointmentd["appointment"]["remark"] = json_encode($remark);
                $data_appointmentd["appointment"]["time"] = $appointment_time;
                $data_appointmentd["appointment"]["is_confirm"] = $is_confirm;
                $data_appointmentd["appointment"]["appointment_date"] = $appointment_date;
                $data_appointmentd["appointment"]["servise_clinic"] = json_encode($service_clinic);
                // print $data_appointmentd["appointment"]["l_name"];
            }
            // print_r($data_appointmentd);
        }
        else{
            $msg_error .= $stmt->error; //เดี่ยว
        }
        $stmt->close();
    }

    //Check_mode_save
    $mode_ins_up = count($data_appointmentd) != 0? "update" : "insert";

    $data_name = array();
    if($sUid != ""){
        $query = "select fname, sname from 
        patient_info
        where uid = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $sUid);

        if($stmt->execute()){
            $stmt->bind_result($fname, $sname);
            while($stmt->fetch()){
                $data_name["appointment"]["f_name"] = $fname;
                $data_name["appointment"]["l_name"] = $sname;
            }
        }else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();
    }

    $date_check_date_dup = array();
    $query = "select app.appointment_date, app.appointment_time, app.s_id, staff.s_name
    from i_appointment app
        left join p_staff staff on (app.s_id = staff.s_id)
    where app.clinic_id = ?
    and app.uid = ?
    and app.is_confirm = 0;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $clinic_id, $sUid);

    if($stmt->execute()){
        $stmt->bind_result($appointment_date, $appointment_time, $s_id, $s_name);
        while($stmt->fetch()){
            $date_check_date_dup["check_dateDup"]["date"] = $appointment_date;
            $date_check_date_dup["check_dateDup"]["time"] = $appointment_time;
            $date_check_date_dup["check_dateDup"]["name"] = $s_name;
        }
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();

    // p_data_result
    $data_p_result = "";
    if($sColDate != "" && $sColTime != ""){ 
        $query = "select data_result from p_data_result where uid = ? and collect_date = ? and collect_time = ? and data_id = 'service_clinic';";
        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("sss", $sUid, $sColDate, $sColTime);

        if($stmt->execute()){
            $stmt->bind_result($data_result);
            while($stmt->fetch()){
                $data_p_result = $data_result;
            }
        }
        $stmt->close();
    }
    $mysqli->close();

    $sJS = "";
    $con_name = "";
    $time_old = "";
    $time_old = isset($data_appointmentd["appointment"]["time"])? $data_appointmentd["appointment"]["time"]:"";
    
    $sJS .= '$("select[name=clinic_id]").val("'.$clinic_id.'");';
    $sJS .= '$("select[name=clinic_id]").attr("data-odata","'.(count($data_appointmentd) != 0? $clinic_id : "").'");';

    $sJS .= '$("input[name=uid]").val("'.$sUid.'");';
    $sJS .= '$("input[name=uid]").attr("data-odata","'.(count($data_appointmentd) != 0? $sUid : "").'");';

    $con_name = (count($data_name) != 0? $data_name["appointment"]["f_name"] : "")." ".(count($data_name) != 0? $data_name["appointment"]["l_name"] : "");
    $sJS .= '$("input[name=appointment_name_patient]").val("'.$con_name.'");';
    $sJS .= '$("input[name=appointment_name_patient]").attr("data-odata","'.$con_name.'");';

    $con_date_and = count($data_appointmentd) != 0? $data_appointmentd["appointment"]["appointment_date"] : $appointment_date_res;
    $con_time_and = count($data_appointmentd) != 0? $data_appointmentd["appointment"]["time"] : "00:00:00";
    $sJS .= '$("input[name=appointment_date_show]").val("'.$con_date_and.'");';
    $sJS .= '$("#appointments_main [name=appointment_time_show]").val("'.$con_time_and.'");';
    $sJS .= '$("input[name=appointment_date]").val("'.$con_date_and.'");';
    $sJS .= '$("input[name=appointment_time]").val("'.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["time"] : "").'");';
    $sJS .= '$("input[name=appointment_date]").attr("data-odata","'.(count($data_appointmentd) != 0? $con_date_and : "").'");';
    $sJS .= '$("input[name=appointment_time]").attr("data-odata","'.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["time"] : "").'");';

    $sJS .= '$("textarea[name=remark]").val('.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["remark"] : "").');';
    $sJS .= '$("textarea[name=remark]").attr("data-odata",'.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["remark"] : "").');';

    $sJS .= '$("select[name=s_id]").attr("data-odata","'.(count($data_appointmentd) != 0? $sSID : "").'");';

    $sJS .= '$("input[name=is_confirm]").val("'.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["is_confirm"] : "0").'");';

    $sJS .= '$("#appointments_main [name=service_clinic]").val('.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["servise_clinic"]:"").');';
    $sJS .= '$("#appointments_main [name=service_clinic]").attr("data-odata",'.(count($data_appointmentd) != 0? $data_appointmentd["appointment"]["servise_clinic"]:"").');';

    // Wait code
    $con_date_check = (count($date_check_date_dup) != 0? date("d/m/Y H:i", strtotime($date_check_date_dup["check_dateDup"]["date"]." ".$date_check_date_dup["check_dateDup"]["time"]))." โดยคุณหมอ ".$date_check_date_dup["check_dateDup"]["name"] : "");
    $sJS .= '$("input[name=check_dateDup]").val("'.$con_date_check.'");';

    // Check have UID
    $stJs_HaveUid = "";
    if($sUid != ""){
        $stJs_HaveUid .= '$("#appointments_main [name=uid]").attr("disabled", true);';
        $stJs_HaveUid .= '$(".anonymous-bt").attr("hidden", true);';
    }
    else{
        $stJs_HaveUid .= '$("#appointments_main [name=uid]").attr("disabled", false);';
        $stJs_HaveUid .= '$(".anonymous-bt").attr("hidden", false);';
        $stJs_HaveUid .= '$(".uid-hide").removeClass("appointments-mt-2");';
    }
?>

<div id="appointments_main" class="fl-wrap-col appointments-mt-1" style="min-width:500;">
    <span id="data_defult" data-mode="<? echo $mode_ins_up; ?>" data-coldate="<? echo $sColDate; ?>" data-coltime="<? echo $sColTime; ?>" data-service="<?echo $data_p_result;?>" data-timeold="<? echo $time_old; ?>"></span>
    <div class="fl-wrap-col fl-auto">
        <input type="hidden" name="is_confirm" data-id="is_confirm" data-require='' class='input-group' value="">
        <input type="hidden" name="check_dateDup" data-id="check_dateDup" data-require='' class='input-group' value="">
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-20">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>Clinic ID:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <b><select name='clinic_id' data-id='clinic_id' data-odata='' class='save-data input-group'>
                    <option value="">-- Please Select --</option>
                    <option value="<? echo $clinic_id; ?>" data-id="clinic_id"> <? echo $clinic_id; ?> </option>
                </select></b>
            </div>
            <div class="fl-fill fl-mid">
                <button id='btn_print' class='btn' type='button'><i class="fa fa-print" aria-hidden='true'></i></button>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 h-20 anonymous-bt" hidden>
            <div class="fl-fix appointments-text-right" style="min-width:286px"></div>
            <div class="fl-fix w-120 h-20 fs-smaller fl-mid-left">
                <button class="btn" id="btAddAnonymous" style="background-color: #731ED9; color:seashell;">Anonymous <i class="fa fa-user-plus" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="fl-wrap-row smallfont3 appointments-mt-2 h-25 uid-hide">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>UID:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px; max-width: 250px;">
                <input type='text' name='uid' data-id ='uid' data-require='' data-odata='' class='save-data input-group' value='' onkeyup="if (/\s/g.test(this.value)) this.value = this.value.replace(/\s/g,'')">
            </div>
            <div class="fl-fix w-45 h-25 fl-mid">
                <button id="BtClearUid" class="btn border" hidden><i class="fa fa-window-close fa-sm fw-b" aria-hidden="true" style="color: red;"></i></button>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-25" style="margin-bottom: 27px;">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ชื่อ-นามสกุล:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <input type='text' name='appointment_name_patient' data-id ='appointment_name_patient' data-require='' data-odata='' class='input-group' value=''>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 hide-old-date h-25" style="display:none; color:red;">
            <div class="fl-fix appointments-text-right smallfont1" style="min-width:150px">
                <span>มีข้อมูลเก่า!:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont1" style="min-width:320px">
                <span class='input-group'><? echo $con_date_check; ?></span>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-25" style="margin-top: 0px;">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>วันที่:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <!-- <input type='datetime-local' name='appointment_date_show' data-id ='appointment_date_show' data-require='' data-odata='' class='input-group' value=''> -->
                <input id='appointment_date_show' name='appointment_date_show' class='txtdate input-group' placeholder="yy-mm-dd" maxlength="10"  readonly="true"/>
                <input type="hidden" name='appointment_date' data-id ='appointment_date' data-require='' data-odata='' class='save-data input-group' value=''>
                <input type="hidden" name='appointment_time' data-id ='appointment_time' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-25">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ช่วงเวลา:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont3" style="min-width:250px">
                <select name='appointment_time_show' data-id='appointment_time_show' data-odata='' class='input-group'>
                    <option value="00:00:00">-- Please Select --</option>
                    <option value="08:00:00" data-id="appointment_time"> 08:00 - 09:00 </option>
                    <option value="09:00:00" data-id="appointment_time"> 09:00 - 10:00 </option>
                    <option value="10:00:00" data-id="appointment_time"> 10:00 - 11:00 </option>
                    <option value="11:00:00" data-id="appointment_time"> 11:00 - 12:00 </option>
                    <option value="12:00:00" data-id="appointment_time"> 12:00 - 13:00 </option>
                    <option value="13:00:00" data-id="appointment_time"> 13:00 - 14:00 </option>
                    <option value="14:00:00" data-id="appointment_time"> 14:00 - 15:00 </option>
                    <option value="15:00:00" data-id="appointment_time"> 15:00 - 16:00 </option>
                    <option value="16:00:00" data-id="appointment_time"> 16:00 - 17:00 </option>
                    <option value="17:00:00" data-id="appointment_time"> 17:00 - 18:00 </option>
                </select>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-25">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>หน่วยบริการ:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont3" style="min-width:250px">
                <select name='service_clinic' data-id='service_clinic' data-odata='' class='input-group save-data'>
                    <option value="1">Pribta clinic</option>
                    <option value="2">Tangerine clinic</option>
                    <option value="3">Research project</option>
                </select>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-80">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>หมายเหตุ:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <textarea name="remark" data-id="remark" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='4'></textarea>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-25">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ชื่อผู้ตรวจ:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:90px">
                <select name='type_appointment' data-id='type_appointment' class='input-group'>
                    <option value="">-- Please Select --</option>
                    <option value="D05" data-id="type_appointment"> Counselor </option>
                    <option value="D06" data-id="type_appointment" selected> Doctor </option>
                </select>
            </div>
            <div class="fl-fix smallfont2" style="min-width:160px">
                <select name='s_id' data-id='s_id' data-odata='' class='save-data input-group'>
                    <? $data_id = "s_id"; $data_result_staff = ""; $not_QS_sid = "false"; include("doctor_opt_staff_md.php"); ?>
                </select>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3 h-40">
            <div class="fl-fix appointments-text-right" style="min-width:250px">
                <button id='btn_save_form_view' class='btn btn-success border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> บันทึกข้อมูล </button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
            </div>
            <div class="fl-fix" style="min-width: 30px;"></div>
            <div class="fl-fix appointments-text-left" style="min-width:250px">
                <button id='btn_cancel' class='btn btn-danger border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> ยกเลิกนัด </button>
            </div>
            <div class="fl-fix appointments-text-left" style="min-width:10px">
                <button id='btn_cancel_hide' hidden class='btn btn-danger border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();
        var year = d.getFullYear();
        if (month < 10) {
            month = "0" + month;
        }

        var date_cur = year+"-"+month+"-"+day;;
        $("#appointments_main .txtdate").datepicker({
			dateFormat: "yy-mm-dd",
            minDate: date_cur,
			changeMonth: true,
			changeYear: true
		});

        // echo value or result
        <? 
            echo $sJS;
            echo $stJs_HaveUid;
        ?>

        //Add Anonymous
        $("#appointments_main #btAddAnonymous").off("click");
        $("#appointments_main #btAddAnonymous").on("click", function(){
            var sUrl_anonymous = "appointments_anonymous.php";

            showDialog(sUrl_anonymous, "Create Anonymous Appointments", "45%", "45%", "", function(sResult){
                if(sResult != ""){
                    $("#appointments_main [name=uid]").val(sResult);
                    $("#appointments_main [name=uid]").blur();
                }
            }, false, function(sResult){});
        })

        //Print pdf appointments
        $("#appointments_main #btn_print").off("click");
        $("#appointments_main #btn_print").on("click", function(){
            if($("#appointments_main [name=appointment_date]").val() != ""){
                var uid_send = $("#appointments_main [name=uid]").val();
                var clinic_id = $("#appointments_main [name=clinic_id]").val();
                var ap_date_s = $("#appointments_main [name=appointment_date]").val();
                var sid_s = $("#appointments_main [name=s_id]").val();
                var gen_url = "appointments_print_pdf.php?uid="+uid_send+"&clinic_id="+clinic_id+"&coldate=&coltime="+"&ap_date="+ap_date_s+"&sid="+sid_s;
                window.open(gen_url, 'Appointment document');
            }
            else{
                alert("ไม่มีข้อมูลวันนัดคนไข้");
            }
        });

        // Show print pdf
        var mode_check = $("#appointments_main #data_defult").data("mode");
        if(mode_check != "insert"){
            $("#appointments_main #btn_print").show();
        }
        else{
            $("#appointments_main #btn_print").hide();
        }

        // category service
        var check_service = $("#appointments_main [name=service_clinic]").val();
        var service_id = $("#appointments_main #data_defult").data("service");
        if(check_service == ""){
            if(service_id != "")
            $("#appointments_main [name=service_clinic]").val(service_id);
        }

        $("#appointments_main #btn_cancel_hide").off("click");
        $("#appointments_main #btn_cancel_hide").on("click", function(){
            var objthis = $(this);
            closeDlg(objthis, "0");
        });

        // Clos dialog
        $("#appointments_main #btn_cancel").off("click");
        $("#appointments_main").on("click", "#btn_cancel", function(){
            if(confirm('คุณแน่ใจที่จะยกเลิกหรือไม่?')){
                var clinicid_s = $("#appointments_main [name=clinic_id]").val();
                var ap_date_s = $("#appointments_main [name=appointment_date_show]").val();
                var uid_s = $("#appointments_main [name=uid]").val();
                var sid_s = $("#appointments_main [name=s_id]").val();
                var app_time_s = $("#appointments_main [name=appointment_time_show]").val();

                var aData = {
                    clinicid: clinicid_s,
                    ap_date: ap_date_s,
                    uid: uid_s,
                    sid: sid_s,
                    u_mode: "del_appointment",
                    app_time: app_time_s
                };
                // console.log(aData);

                setDlgResult(1, $(this));

                $.ajax({
                    url: "appointment_a.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var check = JSON.parse(result);
                        if(check["res"] == 1){
                            $("#appointments_main #btn_cancel_hide").click();
                        }else{
                            alert("ไม่สามารถยกเลิกได้");
                        }
                    }
                });
            }
        })

        $("#appointments_main #btn_save_form_view").off();
        $("#appointments_main #btn_save_form_view").on("click", function(){
            saveFormData_appointments($(this));
        });

        // not edit
        $("#appointments_main [name=clinic_id]").attr("disabled", true);
        $("#appointments_main [name=appointment_name_patient]").attr("disabled", true);

        // check change date
        $("#appointments_main [name=appointment_date_show]").on("change", function(){
            if($("#appointments_main [name=check_dateDup]").val() != ""){
                $("#appointments_main .hide-old-date").show();
            }
        });

        //Check data in base alert
        $("#appointments_main [name=uid]").unbind("blur");
        $("#appointments_main [name=uid]").on("blur", function(){
            var aData = {
                uid: $(this).val().replace(/\s/g, '')
            };

            $.ajax({url: "appointments_calendar_ajax.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var check_have_data = result.split(",");
                    var check_null = $("#appointments_main [name=uid]").val();

                    if(check_have_data[0] == "1"){
                        // console.log(check_have_data[0]);
                        $("#appointments_main [name=appointment_name_patient]").val(check_have_data[1]);
                        $("#appointments_main #btn_save_form_view").attr("disabled", false);
                        $("#appointments_main [name=uid]").attr("disabled", true);
                        $("#appointments_main #BtClearUid").attr("hidden", false); 
                    }
                    else if(check_have_data[0] != "1" && check_null != ""){
                        alert("ไม่มีข้อมูล UID ในระบบ!");
                        $("#appointments_main [name=appointment_name_patient]").val("");
                        $("#appointments_main #btn_save_form_view").attr("disabled", true)
                    }
                }
            });
        });

        //Clear UID
        $("#appointments_main #BtClearUid").off("click");
        $("#appointments_main #BtClearUid").on("click", function(){
            $("#appointments_main [name=uid]").attr("disabled", false);
            $("#appointments_main [name=uid]").val("");
            $("#appointments_main [name=appointment_name_patient]").val("");
            $("#appointments_main #BtClearUid").attr("hidden", true);
        })
    })

    function getWObjValue(obj){
        var sValue = "";
        if($(obj)){
            var sTagName = $(obj).prop("tagName").toUpperCase();

            if(sTagName=="INPUT"){
                if($(obj).prop("type")){
                    if($(obj).prop("type").toLowerCase()=="checkbox"){
                        sValue = ($(obj).prop("checked"))?1:"";
                    }
                    else if($(obj).prop("type").toLowerCase()=="radio"){
                        var sName = $(obj).attr("name");
                        sValue = $("input[name='"+sName+"']").filter(":checked").val();
                    }
                    else{
                        sValue = $(obj).val();
                    }
                }
                else{
                    sValue = $(obj).val();
                }
            }
            else{
                sValue = $(obj).val();
            }

            if($(obj).hasClass("v_date")){
                var arrDate = sValue.split("/");

                if(arrDate.length == 3){
                    sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
                }
            }
            
            return sValue;
        }
    }

    function saveFormData_appointments(objThis){
        var lst_data_obj = [];

        $("#appointments_main [name=appointment_date]").val($("#appointments_main [name=appointment_date_show]").val());
        $("#appointments_main [name=appointment_time]").val($("#appointments_main [name=appointment_time_show]").val());

        var old_value = "";
        $("#appointments_main .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            objVal = getWObjValue($(objx));
            odata_val = $(objx).data("odata");
            if(typeof odata_val === "undefined"){
                odata_val = "";
            }
            if(typeof objVal === "undefined"){
                objVal  = "";
            }
            odata_val = odata_val.toString().replace(/'/g,"");
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = objVal;
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var mdoe_save = $("#appointments_main #data_defult").data("mode");
            var aData = {
                app_mode: "appointments",
                mode_save: mdoe_save,
                uid: $("#appointments_main [name=uid]").val(),
                clinic_id: $("#appointments_main [name=clinic_id]").val(),
                is_confirm: $("#appointments_main [name=is_confirm]").val(),
                s_id: $("#appointments_main [name=s_id]").val(),
                app_date: $("#appointments_main [name=appointment_date]").val(),
                app_date_old: $("#appointments_main [name=appointment_date]").data("odata"),
                app_time: $("#appointments_main [name=appointment_time_show]").val(),
                app_time_old: $("#appointments_main #data_defult").data("timeold"),
                dataid: lst_data_obj,
            };
            // console.log(aData);
            var aData_check = {
                sid: $("#appointments_main [name=s_id]").val(),
                clinic_id: $("#appointments_main [name=clinic_id]").val(),
                app_date: $("#appointments_main [name=appointment_date]").val(),
                app_time: $("#appointments_main [name=appointment_time_show]").val()
            };

            $.ajax({url: "appointments_calendar_ajax_case.php", 
                method: "POST",
                cache: false,
                data: aData_check,
                success: function(result){
                    var check_have_data = result;

                    if(check_have_data < 4){
                        var check_name_doc = $("#appointments_main [name=s_id]").val();
                        var check_uid = $("#appointments_main [name=uid]").val();
                        var check_time = $("#appointments_main [name=appointment_time_show]").val();
                        if(check_name_doc != "" && check_uid != "" && check_time != "00:00:00"){
                            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_appointment);
                            $("#appointments_main .hide-old-date").hide();
                            $("#appointments_main #btn_save_form_view").next("#appointments_main .spinner").show();
                            $("#appointments_main #btn_save_form_view").hide();
                        }
                        else if(check_name_doc == ""){
                            alert("กรุณาเลือกชื่อคุณหมอหรือคอนเซลเลอร์");
                        }
                        else if(check_uid == ""){
                            alert("กรุณาใส่ UID");
                        }
                        else if(check_time == "00:00:00"){
                            alert("กรุณาเลือกช่วงเวลา");
                        }
                    }
                    else{
                        alert("ไม่สามารถบันทึกข้อมูลได้ เนื่องจากคุณหมอมีเคสครบ 4 เคสแล้ว!");
                    }

                    setDlgResult(1, objThis);
                }
            });
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_appointment(flagSave, aData, rtnDataAjax){
        // console.log(flagSave["msg_error"]);
        if(flagSave["msg_error"] == ""){
            $.notify("Save Data", "success");

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });

            $("#appointments_main #btn_save_form_view").next("#appointments_main .spinner").hide();
            $("#appointments_main #btn_save_form_view").show();

            $("#appointments_main #btn_cancel_hide").click();
        }
        else{
            alert("คนไข้คนนี้มีนัดภายในวันนี้แล้ว");
            $("#appointments_main #btn_save_form_view").next("#appointments_main .spinner").hide();
            $("#appointments_main #btn_save_form_view").show();
        }
    }
</script>