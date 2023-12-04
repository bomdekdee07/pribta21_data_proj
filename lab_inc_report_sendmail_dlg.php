<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $lab_order_id = getQS("oid");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $aLabList = explode("||", getQS("lablist"));
    $sLabList = "'".implode("','", $aLabList)."'";
    $hidename = isset($_POST["hidename"])?$_POST["hidename"]:"";
    $hideproject = isset($_POST["hideproject"])?$_POST["hideproject"]:"";
    // echo $sLabList."/".$lab_order_id."/".$uid."/";
    
    $bind_param = "s";
    $array_val = array($uid);
    $email_to = "";

    $query = "SELECT email 
    from patient_info 
    where uid = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $email_to = $row["email"];
        }
    }
    $stmt->close();

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $html_echo = "";

    $query = "SELECT
        PLT.lab_id2,
        PLR.lab_id,
        PLT.lab_name,
        PLT.lab_name_report,
        PLR.lab_serial_no,
        PLR.external_lab AS ext_lab,
        PLR.lab_result_report,
        PLR.lab_result_note,
        PLR.lab_result_status,
        PLM.lab_method_name,
        PLT.lab_group_id,
        PLO.laboratory_id,
        PLT.specimen_transform,
        PS.s_name AS staff_save,
        PS.license_lab AS staff_save_license,
        PC.s_name AS staff_confirm,
        PC.license_lab AS staff_confirm_license,
        PP.s_name AS staff_print_by,
        PLP.time_lab_confirm,
        PLTRH.lab_std_male_txt AS m_lab_std_txt,
        PLT.lab_result_min_male AS m_min,
        PLT.lab_result_max_male AS m_max,
        PLTRH.lab_std_female_txt AS f_lab_std_txt,
        PLT.lab_result_min_female AS f_min,
        PLT.lab_result_max_female AS f_max 
    FROM
        p_lab_result PLR
        LEFT JOIN p_lab_order_lab_test PLO ON PLO.uid = PLR.uid 
        AND PLO.collect_date = PLR.collect_date 
        AND PLO.collect_time = PLR.collect_time 
        AND PLO.lab_id = PLR.lab_id
        LEFT JOIN p_lab_test PLT ON PLT.lab_id = PLR.lab_id
        LEFT JOIN p_lab_test_group PLTG ON PLTG.lab_group_id = PLT.lab_group_id
        LEFT JOIN p_lab_method PLM ON PLM.lab_method_id = PLTG.lab_method_id
        LEFT JOIN p_lab_test_result_hist PLTRH ON PLTRH.lab_id = PLT.lab_id
        LEFT JOIN p_lab_process PLP ON ( PLP.lab_serial_no = PLR.lab_serial_no AND PLP.lab_process_status = 'P1' )
        LEFT JOIN p_staff PS ON PS.s_id = PLP.staff_save
        LEFT JOIN p_staff PC ON PC.s_id = PLP.staff_confirm
        LEFT JOIN p_staff PP ON PP.s_id = 'P21008' 
    WHERE
        PLTRH.start_date <= now() AND PLTRH.stop_date > now() 
        AND PLT.lab_id IN (".$sLabList.") 
        AND PLR.uid = ?
        AND PLR.collect_date = ?
        AND PLR.collect_time = ?
        AND PLR.lab_result <> '' 
    ORDER BY
        PLR.external_lab,
        PLT.lab_group_id,
        PLT.lab_id2;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $html_echo .=   '<div class="fl-wrap-row h-20 row-hover row-color">
                                <div class="fl-fix w-10"></div>
                                <div class="fl-fix w-250 fl-mid font-s-1">'.$row["lab_name"].'</div>
                                <div class="fl-fill fl-mid-left font-s-1">'.$row["lab_result_report"].'</div>
                                <div class="fl-fix w-10"></div>
                            </div>';
        }
    }
    $stmt->close();

    // Query check log send aready
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_log_send_eamil = array();

    $query = "SELECT email_f,
        email, 
        upd_date 
    from log_send_email_lab_result 
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    order by upd_date;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_log_send_eamil[$row["upd_date"]]["email_from"] = $row["email_f"];
            $data_log_send_eamil[$row["upd_date"]]["email_to"] = $row["email"];
            $data_log_send_eamil[$row["upd_date"]]["upd_date"] = $row["upd_date"];
        }
    }
    $stmt->close();
    $mysqli->close();
    
    $html_echo_log = "";
    if(count($data_log_send_eamil) > 0){
        $html_echo_log = '<i class="fa fa-check" aria-hidden="true" style="color: #81DB42;"> ส่งแล้ว</i>';
    }
?>

<div class="fl-wrap-col" id="lab_send_mail_main" 
    data-email="<? echo $email_to; ?>" 
    data-uid="<? echo $uid; ?>" 
    data-coldate="<? echo $coldate; ?>"
    data-coltime="<? echo $coltime; ?>"
    data-orderlabid="<? echo $lab_order_id; ?>"
    data-lablist="<? echo getQS("lablist"); ?>">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-10"></div>
        <div class="fl-fix w-80 font-s-1 fw-b fl-mid-left">Language:</div>
        <div class="fl-fix w-100 font-s-1 fw-b fl-mid-left">
            <select id="lg_value" name="lg_value" class="w-100">
                <option value="EN">English</option>
                <option value="TH">ไทย</option>
            </select>
        </div>
        <div class="fl-fix w-30"></div>
        <div class="fl-fix w-80 font-s-1 fw-b fl-mid-left">Email Clinic:</div>
        <div class="fl-fix w-120 font-s-1 fw-b fl-mid-left">
            <select id="email_clicni_from_select" name="email_clicni_from_select" class="w-120">
                <option value="PRIBTA">Pribta Clinic</option>
                <option value="TG">Tangerine Clinic</option>
            </select>
        </div>
        <div class="fl-fix w-1"></div>
        <div class="fl-fix w-200 font-s-1 fw-b fl-mid-left fw-b">
            <input type="text" name="email_clinic_from" class="font-s-1 fw-b w-200 h-20" style="background-color: #D9D9D9;" value="pribtaclinic@ihri.org" readonly />
        </div>
        <div class="fl-fill"></div>
        <div class="fl-fix w-130 fw-b font-s-1 fl-mid-left"><button class="btn" style="padding: 0px 3px 0px 3px;" name="bt_view_log_sendmail"><i class="fa fa-eye" aria-hidden="true"></i></button> สถานะกาารส่ง:</div>
        <div class="fl-fix w-55 fw-b font-s-1 fl-mid-left">
            <? echo $html_echo_log; ?>
        </div>
        <div class="fl-fix w-10"></div>
    </div>
    <div class="fl-wrap-row h-125">
        <div class="fl-fix w-10"></div>
        <div class="fl-wrap-col border">
            <div class="fl-wrap-row">
                <div class="fl-wrap-col w-65">
                    <div class="fl-wrap-row h-10"></div>
                    <div class="fl-wrap-row h-65">
                        <div class="fl-fill fl-mid font-s-2">
                            <button class="btn border" name="send_email_lab_result" style="padding: 8px 5px 8px 5px;"><i class="fa fa-envelope" aria-hidden="true"></i><br>Send</button><i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
                        </div>
                    </div>
                </div>
                <div class="fl-wrap-col">
                    <div class="fl-wrap-row h-10"></div>
                    <div class="fl-wrap-row h-30">
                        <div class="fl-fix w-80 fl-mid-left font-s-1">
                            <button class="border-line-1" style="padding: 2px 10px 2px 10px; width: 90%; padding-right: 23px;">To...</button>
                        </div>
                        <div class="fl-fill font-s-1 fl-mid-left">
                            <input type="text" name="mail_to" style="min-height: 24px; max-height: 24px; width: 100%;" />
                        </div>
                        <div class="fl-fix w-10"></div>
                    </div>
                    <div class="fl-wrap-row h-30">
                        <div class="fl-fix w-80 fl-mid-left font-s-1">
                            <button class="border-line-1" style="padding: 2px 10px 2px 10px; width: 90%;">Subject</button>
                        </div>
                        <div class="fl-fill font-s-1 fl-mid-left">
                            <input type="text" name="subject_mail" style="min-height: 24px; max-height: 24px; width: 100%;" />
                        </div>
                        <div class="fl-fix w-10"></div>
                    </div>
                    <div class="fl-wrap-row">
                        <div class="fl-wrap-col w-80">
                            <div class="fl-wrap-row h-30">
                                <div class="fl-fix w-5"></div>
                                <div class="fl-fix w-80 fl-mid-left font-s-1">Attached</div>
                            </div>
                        </div>
                        <div class="fl-wrap-col">
                            <div class="fl-wrap-row h-50">
                                <div class="fl-fix w-50 font-s-1 fl-mid">
                                    <button class="btn" name="btn_view_pdf" style="padding: 9px 1px 9px 1px; width: 93%;" title="Click view example file"><i class="fa fa-file-pdf fa-lg" style="color:red;" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fl-fix w-10"></div>
    </div>
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-150">
        <div class="fl-fix w-10"></div>
        <div class="fl-wrap-col border">
            <div class="fl-wrap-row h-10"></div>
            <div class="fl-wrap-row h-20">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-250 fl-mid font-s-2 fw-b border-bt">Lab Name</div>
                <div class="fl-fill fl-mid-left font-s-2 fw-b border-bt">Lab Result</div>
                <div class="fl-fix w-10"></div>
            </div>
            <div class="fl-wrap-col fl-auto">
                <? echo $html_echo; ?>
            </div>
        </div>
        <div class="fl-fix w-10"></div>
    </div>

    <form action="custom_interpret_report_view.php?doc_mode=view" method="post" target="_blank">
        <div class="fl-wrap-row h-10"></div>
        <div class="fl-wrap-row h-300">
            <div class="fl-fix w-10"></div>
            <div class="fl-wrap-col border fl-auto">
                <div class="fl-wrap-row h-10"></div>
                <div class="fl-wrap-row h-25">
                    <div class="fl-fix w-10"></div>
                    <div class="fl-fix w-150 fl-mid-left font-s-2 fw-b border-bt">รายละเอียดการแปลผล</div>
                    <div class="fl-fill fl-mid-left font-s-1 fw-b border-bt"><button name="bt_view_interpret" type="submit" class="btn" style="padding: 1px 1px 1px 1px; width: 3%; display: none;" title="Click view example file"><i class="fa fa-file-pdf fa-lg" style="color:red;" aria-hidden="true"></i></button></div>
                    <div class="fl-fix w-10"></div>
                </div>
                <div class="fl-wrap-row h-5"></div>
                <div class="fl-wrap-row h-250 ">
                    <div class="fl-fix w-10"></div>
                    <div class="fl-fill font-s-1 fl-mid-left">
                        <textarea name="txt_interpret" rows="13" style="width: 100%"></textarea>
                    </div>
                    <div class="fl-fix w-10"></div>
                </div>
            </div>
            <div class="fl-fix w-10"></div>
        </div>
        <input type="hidden" name="interpret_uid">
        <input type="hidden" name="interpret_laborder_id">
        <input type="hidden" name="interpret_language_type">
    </form>
</div>

<script>
    $(document).ready(function(){
        // defult value
        var mail_to = $("#lab_send_mail_main").attr("data-email");
        $("#lab_send_mail_main [name=mail_to]").val(mail_to);
        $("#lab_send_mail_main [name=subject_mail]").val("LAB FROM PRIBTA CLINIC");

        // Custom text area can do [TAP] in box.
        $("#lab_send_mail_main [name=txt_interpret]").on('keydown', function(e) {
            if (e.key == 'Tab') {
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;

                // set textarea value to: text before caret + tab + text after caret
                this.value = this.value.substring(0, start) +
                "\t" + this.value.substring(end);

                // put caret at right position again
                this.selectionStart =
                this.selectionEnd = start + 1;
            }
        });

        // View interpret
        $("#lab_send_mail_main [name=bt_view_interpret]").off("click");
        $("#lab_send_mail_main [name=bt_view_interpret]").on("click", function(){
            var lab_order_id_s = $("#lab_send_mail_main").attr("data-orderlabid");
            var uid_s = $("#lab_send_mail_main").attr("data-uid");
            var language_val = $("#lab_send_mail_main [name=lg_value]").val();

            $("#lab_send_mail_main [name=interpret_uid]").val(uid_s);
            $("#lab_send_mail_main [name=interpret_laborder_id]").val(lab_order_id_s);
            $("#lab_send_mail_main [name=interpret_language_type]").val(language_val);

            $(this).submit();
        });

        // Change interpret
        $("#lab_send_mail_main [name=txt_interpret]").off("keyup");
        $("#lab_send_mail_main [name=txt_interpret]").on("keyup", function(){
            var this_val = $(this).val();
            if(this_val.length > 1){
                $("#lab_send_mail_main [name=bt_view_interpret]").show();
            }
            else{
                $("#lab_send_mail_main [name=bt_view_interpret]").hide();
            }
        });

        // view log send email
        $("#lab_send_mail_main [name=bt_view_log_sendmail]").off("click");
        $("#lab_send_mail_main [name=bt_view_log_sendmail]").on("click", function(ev){
            ev.preventDefault();
            var uid_s = $("#lab_send_mail_main").attr("data-uid");
            var lab_order_id_s = $("#lab_send_mail_main").attr("data-orderlabid");
            sUrl="lab_inc_report_sendmail_log_dlg.php?uid="+uid_s+"&order_labid="+lab_order_id_s;

            showDialog(sUrl, "Log Send Email Lab Result", "40%", "53%","",
            function(sResult){
                //CLose function
            },false,function(){
                //Load Done Function
            });
        });

        // Change Email
        $("#lab_send_mail_main [name=email_clicni_from_select]").off("click");
        $("#lab_send_mail_main [name=email_clicni_from_select]").on("click", function(ev){
            ev.preventDefault();
            var this_val = $(this).val();
            if(this_val == "TG"){
                $("#lab_send_mail_main [name=email_clinic_from]").val("tangerineclinic@ihri.org");
                $("#lab_send_mail_main [name=subject_mail]").val("LAB FROM TANGERINE CLINIC");
            }
            else{
                $("#lab_send_mail_main [name=email_clinic_from]").val("pribtaclinic@ihri.org");
                $("#lab_send_mail_main [name=subject_mail]").val("LAB FROM PRIBTA CLINIC");
            }
        });

        // Change leangaue
        $("#lab_send_mail_main [name=lg_value]").off("off");
        $("#lab_send_mail_main [name=lg_value]").on("click", function(ev){
            ev.preventDefault();
            var this_val = $(this).val();
            // if(this_val == "TH"){
            //     $("#lab_send_mail_main [name=subject_mail]").val("LAB FROM PRIBTA CLINIC");
            // }
            // else{
            //     $("#lab_send_mail_main [name=subject_mail]").val("LAB FROM PRIBTA CLINIC");
            // }
        });

        // Click view pdf
        $("#lab_send_mail_main [name=btn_view_pdf]").off("click");
        $("#lab_send_mail_main [name=btn_view_pdf]").on("click", function(ev){
            ev.preventDefault();
            var lab_order_id_s = $("#lab_send_mail_main").attr("data-orderlabid");
            var uid_s = $("#lab_send_mail_main").attr("data-uid");
            var lablist_s = $("#lab_send_mail_main").attr("data-lablist");
            var link_str = "../weclinic/lab/custom_lab_report_view.php?uid="+uid_s+"&oid="+lab_order_id_s+"&lablist="+encodeURIComponent(lablist_s);
            // console.log(link_str);
            
            window.open(link_str, '_blank');
        });

        // Send email
        $("#lab_send_mail_main [name=send_email_lab_result]").off("click");
        $("#lab_send_mail_main [name=send_email_lab_result]").on("click", function(ev){
            ev.preventDefault();
            var email = $("#lab_send_mail_main [name=mail_to]").val();

            if (confirm("คุณต้องการส่งอีเมล์ใช่หรือไม่") == true) {
                if(email != ""){
                    $(this).hide();
                    $(this).next().show();

                    var lab_order_id_s = $("#lab_send_mail_main").attr("data-orderlabid");
                    var uid_s = $("#lab_send_mail_main").attr("data-uid");
                    var lablist_s = $("#lab_send_mail_main").attr("data-lablist");
                    var email_s = $("#lab_send_mail_main [name=mail_to]").val();
                    var subject_s = $("#lab_send_mail_main [name=subject_mail]").val();
                    var language_val = $("#lab_send_mail_main [name=lg_value]").val();
                    var interpret_val_length = $("#lab_send_mail_main [name=txt_interpret]").val().length;
                    var email_clinic_from = $("#lab_send_mail_main [name=email_clinic_from]").val();
                    var interpret_val = $("#lab_send_mail_main [name=txt_interpret]").val().replace(/\n/g, "\\n").replace(/\t+/g, "\\t");
                    var typeClinic = $("#lab_send_mail_main [name=email_clicni_from_select]").val();

                    var aData = {
                        uid: uid_s,
                        oid: lab_order_id_s,
                        lablist: lablist_s,
                        email: email_s,
                        subject: subject_s,
                        language: language_val,
                        interpret: interpret_val_length,
                        email_from: email_clinic_from,
                        txt_interpret: interpret_val,
                        interpret_uid: uid_s,
                        interpret_laborder_id: lab_order_id_s,
                        interpret_language_type: language_val,
                        type_clicni: typeClinic
                    };
                    // console.log(aData);

                    $.ajax({
                        url: "../weclinic/lab/custom_lab_report_sendmail.php",
                        method: "POST",
                        cache: false,
                        data: aData,
                        success: function(sResult){
                            var lab_order_id_s = $("#lab_send_mail_main").attr("data-orderlabid");
                            var uid_s = $("#lab_send_mail_main").attr("data-uid");
                            var lablist_s = $("#lab_send_mail_main").attr("data-lablist");
                            var email_s = $("#lab_send_mail_main [name=mail_to]").val();
                            var subject_s = $("#lab_send_mail_main [name=subject_mail]").val();
                            var language_val = $("#lab_send_mail_main [name=lg_value]").val();
                            var interpret_val_length = $("#lab_send_mail_main [name=txt_interpret]").val().length;
                            var email_clinic_from = $("#lab_send_mail_main [name=email_clinic_from]").val();
                            var interpret_val = $("#lab_send_mail_main [name=txt_interpret]").val();

                            var aData = {
                                uid: uid_s,
                                oid: lab_order_id_s,
                                lablist: lablist_s,
                                email: email_s,
                                subject: subject_s,
                                language: language_val,
                                interpret: interpret_val_length,
                                email_from: email_clinic_from,
                                txt_interpret: interpret_val,
                                interpret_uid: uid_s,
                                interpret_laborder_id: lab_order_id_s,
                                interpret_language_type: language_val
                            };
                            // console.log(aData);

                            $.ajax({
                                url: "lab_inc_report_sendmail_log.php",
                                method: "POST",
                                cache: false,
                                data: aData,
                                success: function(sResult){
                                    $("#lab_send_mail_main [name=send_email_lab_result]").show();
                                    $("#lab_send_mail_main [name=send_email_lab_result]").next().hide();
                                }
                            })
                        }
                    });
                }
                else{
                    alert("กรุณากรอก Email");
                    $("#lab_send_mail_main [name=mail_to]").focus();
                }
            }
        });
    });
</script>