<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $doc_code = getQS("doctype");
    $sid = getSS("s_id");

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_patient = array();
    $data_body = array();

    $query = "SELECT uid_info.fname,
        uid_info.sname,
        uid_info.en_fname,
        uid_info.en_sname,
        CONCAT(uid_info.id_address, ' ', uid_info.id_zone, ' ', uid_info.id_district, ' ', uid_info.id_province, ' ', uid_info.id_postal_code) AS id_address,
        uid_info.citizen_id,
        uid_info.passport_id,
        uid_info.sex,
        body_info.data_id,
        body_info.data_result
    from p_data_result body_info
    left join patient_info uid_info on(body_info.uid = uid_info.uid)
    where body_info.uid = ?
    and body_info.collect_date = ?
    and body_info.collect_time = ?
    and body_info.data_id in ('cn_weight', 'heigh', 'cn_bp_systolic_h', 'cn_diastolic_bp_d', 'cn_pr', 'cn_rr', 'staff_md');";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_patient = $row;
            $data_body[$row["data_id"]] = $row["data_result"];
        }
        // print_r($data_body);
    }
    $stmt->close();

    if(isset($data_body["staff_md"])){
        $bind_param = "s";
        $array_val = array($data_body["staff_md"]);
        $data_md = array();

        $query = "SELECT s_name,
            s_name_en,
            license_md
        from p_staff
        where s_id = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $data_md = $row;
            }
            // print_r($data_md);
        }
        $stmt->close();
    }

    // data result defult
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_result_loop = array();

    $query = "SELECT result.data_id,
        result.data_result,
        data_list.data_type
    from p_data_result result
    left join p_data_list data_list on(data_list.data_id = result.data_id)
    where result.uid = ?
    and result.collect_date = ?
    and result.collect_time = ?
    and result.data_id in ('congenital_disease', 'congenital_disease_txt', 'surgery', 'surgery_txt');";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_result_loop[$row["data_id"]]["result"] = $row["data_result"];
            $data_result_loop[$row["data_id"]]["type"] = $row["data_type"];
        }
        // print_r($data_result_loop);
    }
    $stmt->close();

    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_check_staff = "";

    $query = "SELECT count(*) AS check_staffmd 
    from p_data_result 
    where uid = ? and collect_date = ? and collect_time = ? and data_id = 'staff_md';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($check_staffmd);
        while($stmt->fetch()){
            $data_check_staff = $check_staffmd;
        }
    }
    $stmt->close();
    $mysqli->close();

    $patient_name_th = "";
    $patient_name_th = isset($data_patient["fname"])? $data_patient["fname"]." ".$data_patient["sname"]: (isset($data_patient["en_fname"])? $data_patient["en_fname"] : "")." ".(isset($data_patient["en_sname"])? $data_patient["en_sname"] : "");
    $patient_name_en = "";
    $patient_name_en = (isset($data_patient["en_fname"])? $data_patient["en_fname"] : "")." ".(isset($data_patient["en_sname"])? $data_patient["en_sname"] : "");

    $js_html = "";
    foreach($data_result_loop as $data_id => $val){
        if($val["type"] == "radio"){
            $js_html .= '$("[name='.$data_id.'_pdf][value='.$val["result"].']").prop("checked", true);';
        }
        else if($val["type"] == "text"){
            $js_html .= '$("[name='.$data_id.'_pdf]").val('.json_encode($val["result"]).');';
        }
    }
?>
<div class="fl-wrap-row h-5 font-s-2 fw-b" id="certificate_health_checkup" 
    data-docname_th="<? echo isset($data_md["s_name"])? $data_md["s_name"]: ""; ?>" 
    data-docname_en="<? echo isset($data_md["s_name_en"])? $data_md["s_name_en"]: ""; ?>" 
    data-doclicen="<? echo isset($data_md["license_md"])? $data_md["license_md"]: ""; ?>"
    data-patient_name="<? echo $patient_name_th; ?>" 
    data-patient_name_en="<? echo $patient_name_en; ?>"
    data-idaddress="<? echo isset($data_patient["id_address"])? $data_patient["id_address"]: ""; ?>" 
    data-citizen="<? echo isset($data_patient["citizen_id"])? $data_patient["citizen_id"]: ""; ?>" 
    data-passport="<? echo isset($data_patient["passport_id"])? $data_patient["passport_id"]: ""; ?>" 
    data-sex="<? echo isset($data_patient["sex"])? $data_patient["sex"]: ""; ?>"
    data-weight="<? echo isset($data_body["cn_weight"])? $data_body["cn_weight"]: ""; ?>" 
    data-height="<? echo isset($data_body["heigh"])? $data_body["heigh"]: ""; ?>" 
    data-bph="<? echo isset($data_body["cn_bp_systolic_h"])? $data_body["cn_bp_systolic_h"]: ""; ?>" 
    data-bpd="<? echo isset($data_body["cn_diastolic_bp_d"])? $data_body["cn_diastolic_bp_d"]: ""; ?>" 
    data-pr="<? echo (isset($data_body["cn_pr"])? $data_body["cn_pr"]: ""); ?>"
    data-rr="<? echo (isset($data_body["cn_rr"])? $data_body["cn_rr"]: ""); ?>"
    data-uid="<? echo $uid; ?>"
    data-coldate="<? echo $coldate; ?>"
    data-coltime="<? echo $coltime; ?>"
    data-checkstaff="<? echo $data_check_staff; ?>"
    data-sid="<? echo $sid; ?>">
</div>

<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-80 fl-mid-left">
        รูปแบบภาษา
    </div>
    <div class="fl-fill fl-mid-left holiday-ml-1">
        <select id="format_leg_doc" name="format_leg_doc" style="width: 100px">
            <option value="TH">Thai</option>
            <option value="EN">English</option>
        </select>
    </div>
</div>
<div class="fl-wrap-row h-10 font-s-2 fw-b"></div>
<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-450 fl-mid-left"><label><input type="checkbox" style="width: 15px; height: 15px;" name="chk_custom_citizen_ppn"> กำหนดเลขบัตรประชาชน หรือ เลขหนังสือเดินทาง</label></div>
</div>

<div class="fl-wrap-row h-65 custom-citizen-ppn" style="display: none;">
    <div class="fl-fix w-60"></div>
    <div class="fl-wrap-col border font-s-2">
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-25">
            <div class="fl-fix w-10"></div>
            <div class="fl-fix fl-mid-left w-175">หมายเลขบัตรประชาชน:</div>
            <div class="fl-fill fl-mid-left"><input type="text" class="fw-b" style="width: 100%; height: 90%;" name="citizen_no_custom"></div>
            <div class="fl-fill"></div>
        </div>
        <div class="fl-wrap-row h-25">
            <div class="fl-fix w-10"></div>
            <div class="fl-fix fl-mid-left w-175">หมายเลขหนังสือเดินทาง:</div>
            <div class="fl-fill fl-mid-left"><input type="text" class="fw-b" style="width: 100%; height: 90%;" name="passport_no_custom"></div>
            <div class="fl-fill"></div>
        </div>
    </div>
    <div class="fl-fix w-60"></div>
</div>
<div class="fl-wrap-row h-5 font-s-2 fw-b custom-citizen-ppn" style="display: none;"></div>

<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        สภาพร่างกายอยู่ในเกณฑ์
    </div>
</div>
<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioHealth" name="healthcheckup_physical_condition" value="N" checked /> ปกติ</label>
    </div>
    <div class="fl-fix w-105 fl-mid-left">
        <label><input type="radio" class="changeRadioHealth" name="healthcheckup_physical_condition" value="Y" /> ผิดปกติ (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="healthcheckup_physical_condition_text" style="height: 24px; width: 665px;" maxlength="70"></label>
    </div>
</div>

<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        โรคอื่นๆถ้ามี
    </div>
</div>
<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="healthcheckup_other_diseases_text" style="height: 24px; width: 830px;" maxlength="100"></label>
    </div>
</div>

<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        สรุปความคิดเห็นและข้อแนะนำของแพทย์
    </div>
</div>
<div class="fl-wrap-row h-80 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        <label><textarea name="healthcheckup_comment_text" rows="2" style="height: 50px; width: 830px;" maxlength="125"></textarea></label>
    </div>
</div>

<div class="fl-wrap-row font-s-2 h-125" id="dlg_signature_main">
    <? include("doc_signature_main.php"); ?>
</div>

<div class="fl-wrap-row font-s-2 h-35">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-130 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-success" id="btPrintPdfHealthcheckupCertificate" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
        <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
    </div>
    <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-danger" id="btCancelHealthcheckup" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
    </div>
    <div class="fl-fix w-100 fl-mid-left">
        <button class="btn btn-primary" id="btPrintPdfHealthcheckupCertificate_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>  
    </div>
</div>

<script>
    $(document).ready(function(){
        <?  echo $js_html; ?>

        var check_citizen = $("#certificate_health_checkup").attr("data-citizen");
        var check_ppn = $("#certificate_health_checkup").attr("data-passport");

        if(check_citizen != ""){
            $("[name=citizen_no_custom]").val(check_citizen);
        }
        if(check_ppn != ""){
            $("[name=passport_no_custom]").val(check_ppn);
        }

        // Defult comment Doctor
        $("[name=format_leg_doc]").off("change");
        $("[name=format_leg_doc]").on("change", function(){
            var leg_val = $(this).val();
            if(leg_val == "TH")
                $('[name=healthcheckup_comment_text]').val('มีสุขภาพสมบูรณ์แข็งแรง');
            else
                $('[name=healthcheckup_comment_text]').val('Have good health.');
        });

        $("[name=format_leg_doc]").change();

        // Custom citizen and PPN
        $("[name=chk_custom_citizen_ppn]").off("change");
        $("[name=chk_custom_citizen_ppn]").on("change", function(){
            if($(this).is(":checked")){
                $(".custom-citizen-ppn").show();
            }
            else{
                $(".custom-citizen-ppn").hide();
                $("[name=citizen_no_custom]").val("");
                $("[name=passport_no_custom]").val("");

                if(check_citizen != ""){
                    $("[name=citizen_no_custom]").val(check_citizen);
                }
                if(check_ppn != ""){
                    $("[name=passport_no_custom]").val(check_ppn);
                }
            }
        });

        // format number only
        $("[name=citizen_no_custom]").keypress(function (e) {    
            var charCode = (e.which) ? e.which : event.keyCode    
            if (String.fromCharCode(charCode).match(/[^0-9]/g))    
                return false;               
        });

        // Input maxlength check
        $("[name=healthcheckup_physical_condition_text]").off("input");
        $("[name=healthcheckup_physical_condition_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=healthcheckup_physical_condition_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Input maxlength check
        $("[name=healthcheckup_comment_text]").off("input");
        $("[name=healthcheckup_comment_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=healthcheckup_comment_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Input maxlength check
        $("[name=healthcheckup_other_diseases_text]").off("input");
        $("[name=healthcheckup_other_diseases_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=healthcheckup_other_diseases_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Radio change
        $(".changeRadioHealth").off("change");
        $(".changeRadioHealth").change(function(){
            // debugger;
            var check_condition = this.name.indexOf("_pdf");
            if(check_condition > 0){
                var name_con = this.name.substr(0, this.name.indexOf("_pdf"))+"_txt_pdf";    
            }
            else{
                var name_con = this.name+"_text";
            }
            
            var value_click = $("[name="+this.name+"]:checked").val();
            console.log(value_click);
            if(value_click == "N" || value_click === undefined){
                $("[name="+name_con+"]").prop("disabled", true);
                $("[name="+name_con+"]").val("");
            }
            else{
                $("[name="+name_con+"]").prop("disabled", false);
            }
        });
        $(".changeRadioHealth").change();

        // Button confrim
        $("#btPrintPdfHealthcheckupCertificate").off("click");
        $("#btPrintPdfHealthcheckupCertificate").on("click", function(){
            var sUid = $("#certificate_health_checkup").data("uid");
            var sColdate = $("#certificate_health_checkup").data("coldate");
            var sColtime = $("#certificate_health_checkup").data("coltime");

            var sDoc_th = $("#certificate_health_checkup").data("docname_th");
            var sDoc_en = $("#certificate_health_checkup").data("docname_en");
            var sDo_licen = $("#certificate_health_checkup").data("doclicen");
            var sPatient_name = $("#certificate_health_checkup").data("patient_name");
            var sPatient_name_en = $("#certificate_health_checkup").data("patient_name_en");
            var sIdaddress = $("#certificate_health_checkup").data("idaddress");
            var sSex = $("#certificate_health_checkup").data("sex");
            var sCitizen = $("#certificate_health_checkup").data("citizen");
            var sPassport = $("#certificate_health_checkup").data("passport");
            var sWeight = $("#certificate_health_checkup").data("weight");
            var sHeight = $("#certificate_health_checkup").data("height");
            var sBph = $("#certificate_health_checkup").data("bph");
            var sBpd = $("#certificate_health_checkup").data("bpd");
            var sPr = $("#certificate_health_checkup").data("pr");
            var sRr = $("#certificate_health_checkup").data("rr");
            var sSid = $("#certificate_health_checkup").data("sid");

            var sCongenital_disease_pdf = $("[name=congenital_disease_pdf]:checked").val();
            var sSurgery_pdf = $("[name=surgery_pdf]:checked").val();
            var sHospitalized = $("[name=hospitalized]:checked").val();
            var sEpilepsy = $("[name=epilepsy]:checked").val();
            var sOther_history = $("[name=other_history]:checked").val();

            var sCongenital_disease_txt_pdf = $("[name=congenital_disease_txt_pdf]").val();
            var sSurgery_txt_pdf = $("[name=surgery_txt_pdf]").val();
            var sHospitalized_text = $("[name=hospitalized_text]").val();
            var sEpilepsy_text = $("[name=epilepsy_text]").val();
            var sOther_history_text = $("[name=other_history_text]").val();

            var sPhysical_condition = $("[name=healthcheckup_physical_condition]:checked").val();
            var sPhysical_condition_text = $("[name=healthcheckup_physical_condition_text]").val();

            var sComment_text = $("[name=healthcheckup_comment_text]").val();
            var sOther_diseases_text = $("[name=healthcheckup_other_diseases_text]").val();
            var check_staff = $(".check-staff").data("checkstaff");

            var form_id = $("#medical_create_pdf");
            var leg_mode = $("[name=format_leg_doc]").val();

            var chk_custom_citizen_ppn_s = "";
            var citizen_custom_s = "";
            var passport_custom_s = "";
            if($("[name=chk_custom_citizen_ppn]").is(":checked")){
                chk_custom_citizen_ppn_s = "Y";
                citizen_custom_s = $("[name=citizen_no_custom]").val();
                passport_custom_s = $("[name=passport_no_custom]").val();
            }

            if(check_staff != ""){
                var aData = {
                    doc_mode: "insert",
                    uid: sUid,
                    coldate: sColdate,
                    coltime: sColtime,
                    name_doc_th: sDoc_th,
                    name_doc_en: sDoc_en,
                    doc_licen: sDo_licen,
                    patient_name: sPatient_name,
                    patient_name_en: sPatient_name_en,
                    idaddress: sIdaddress,
                    citizen: sCitizen,
                    passport: sPassport,
                    sex: sSex,
                    weight: sWeight,
                    height: sHeight,
                    bph: sBph,
                    bpd: sBpd,
                    pr: sPr,
                    rr: sRr,
                    congenital_disease_pdf: sCongenital_disease_pdf,
                    surgery_pdf: sSurgery_pdf,
                    hospitalized: sHospitalized,
                    epilepsy: sEpilepsy,
                    other_history: sOther_history,
                    congenital_disease_txt_pdf: sCongenital_disease_txt_pdf,
                    surgery_txt_pdf: sSurgery_txt_pdf,
                    hospitalized_text: sHospitalized_text,
                    epilepsy_text: sEpilepsy_text,
                    other_history_text: sOther_history_text,
                    physical_condition: sPhysical_condition,
                    physical_condition_text: sPhysical_condition_text,
                    comment_text: sComment_text,
                    other_diseases_text: sOther_diseases_text,
                    leg_moed: leg_mode,
                    custom_check: chk_custom_citizen_ppn_s,
                    passport_cus: passport_custom_s,
                    citizen_cus: citizen_custom_s
                };
                // console.log(aData);

                $.ajax({url: "health_checkup_certificate_pdf.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var date_now = String(result);
                        var date_now = date_now.split(",");
                        
                        saveFormData_document_health("MEDICAL_HEALTH", "ใบตรวจสุขภาพทั่วไป", date_now[0], "", sUid, sColdate, sColtime, sSid, 1);
                        var data_date_time_con = date_now[0].split(" ");
                        var coldate_con = data_date_time_con[0].split("-");
                        coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                        var coltime_con = data_date_time_con[1].split(":");
                        coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                        var gen_link = "pdfoutput/"+"MEDICAL_HEALTH_"+sUid+"_"+sColdate+""+sColtime+".pdf";

                        $("#btPrintPdfDrivingCertificate").next(".spinner").show();
                        $("#btPrintPdfDrivingCertificate").hide();

                        setTimeout(function(){
                            close_dlg(form_id);
                        }, 1000);
                }});
            }
            else{
                alert("กรุณาเลือกชื่อหมอ(MD) และบันทึกก่อนครับ");
            }
        });

        // Button cancel
        $("#btCancelHealthcheckup").off("click");
        $("#btCancelHealthcheckup").on("click", function(){
            $("#btPrintPdfHealthcheckupCertificate_view").show();
            $(".hide-bt-pdf").hide();
        });

        // Button View
        $("#btPrintPdfHealthcheckupCertificate_view").off("click");
        $("#btPrintPdfHealthcheckupCertificate_view").on("click", function(){
            var sUid = $("#certificate_health_checkup").data("uid");
            var sColdate = $("#certificate_health_checkup").data("coldate");
            var sColtime = $("#certificate_health_checkup").data("coltime");

            var sDoc_th = $("#certificate_health_checkup").data("docname_th");
            var sDoc_en = $("#certificate_health_checkup").data("docname_en");
            var sDo_licen = $("#certificate_health_checkup").data("doclicen");
            var sPatient_name = $("#certificate_health_checkup").data("patient_name");
            var sPatient_name_en = $("#certificate_health_checkup").data("patient_name_en");
            var sIdaddress = $("#certificate_health_checkup").data("idaddress");
            var sSex = $("#certificate_health_checkup").data("sex");
            var sCitizen = $("#certificate_health_checkup").data("citizen");
            var sPassport = $("#certificate_health_checkup").data("passport");
            var sWeight = $("#certificate_health_checkup").data("weight");
            var sHeight = $("#certificate_health_checkup").data("height");
            var sBph = $("#certificate_health_checkup").data("bph");
            var sBpd = $("#certificate_health_checkup").data("bpd");
            var sPr = $("#certificate_health_checkup").data("pr");
            var sRr = $("#certificate_health_checkup").data("rr");

            var sCongenital_disease_pdf = "";//$("[name=congenital_disease_pdf]:checked").val();
            var sSurgery_pdf = "";//$("[name=surgery_pdf]:checked").val();
            var sHospitalized = "";//$("[name=hospitalized]:checked").val();
            var sEpilepsy = "";//$("[name=epilepsy]:checked").val();
            var sOther_history = "";//$("[name=other_history]:checked").val();

            var sCongenital_disease_txt_pdf = "";//$("[name=congenital_disease_txt_pdf]").val();
            var sSurgery_txt_pdf = "";//$("[name=surgery_txt_pdf]").val();
            var sHospitalized_text = "";//$("[name=hospitalized_text]").val();
            var sEpilepsy_text = "";//$("[name=epilepsy_text]").val();
            var sOther_history_text = "";//$("[name=other_history_text]").val();

            var sPhysical_condition = $("[name=healthcheckup_physical_condition]:checked").val();
            var sPhysical_condition_text = $("[name=healthcheckup_physical_condition_text]").val();

            var sComment_text = $("[name=healthcheckup_comment_text]").val();
            var sOther_diseases_text = $("[name=healthcheckup_other_diseases_text]").val();

            var leg_mode = $("[name=format_leg_doc]").val();

            var chk_custom_citizen_ppn_s = "";
            var citizen_custom_s = "";
            var passport_custom_s = "";
            if($("[name=chk_custom_citizen_ppn]").is(":checked")){
                chk_custom_citizen_ppn_s = "Y";
                citizen_custom_s = $("[name=citizen_no_custom]").val();
                passport_custom_s = $("[name=passport_no_custom]").val();
            }
            
            var gen_url_view = "health_checkup_certificate_pdf.php?doc_mode=view&uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&name_doc_th="+sDoc_th+"&name_doc_en="+sDoc_en+"&patient_name="+sPatient_name+"&patient_name_en="+sPatient_name_en+"&idaddress="+sIdaddress+"&citizen="+sCitizen+"&passport="+sPassport+"&weight="+sWeight+"&height="+sHeight+"&bph="+sBph+"&bpd="+sBpd+"&pr="+sPr+"&congenital_disease_pdf="+sCongenital_disease_pdf+"&surgery_pdf="+sSurgery_pdf+"&hospitalized="+sHospitalized+"&epilepsy="+sEpilepsy+"&other_history="+sOther_history+"&congenital_disease_txt_pdf="+sCongenital_disease_txt_pdf+"&surgery_txt_pdf="+sSurgery_txt_pdf+"&hospitalized_text="+sHospitalized_text+"&epilepsy_text="+sEpilepsy_text+"&other_history_text="+sOther_history_text+"&physical_condition="+sPhysical_condition+"&physical_condition_text="+sPhysical_condition_text+"&comment_text="+sComment_text+"&sex="+sSex+"&rr="+sRr+"&other_diseases_text="+sOther_diseases_text+"&doc_licen="+sDo_licen+"&leg_moed="+leg_mode+"&custom_check="+chk_custom_citizen_ppn_s+"&citizen_cus="+citizen_custom_s+"&passport_cus="+passport_custom_s;
            // console.log(gen_url_view);
            window.open(gen_url_view,'_blank');

            $("#btPrintPdfHealthcheckupCertificate_view").hide();
            $(".hide-bt-pdf").show();
        });
    });

    function close_dlg(formid){
        var objthis = formid;
        closeDlg(objthis, "0");
    }

    function saveFormData_document_health(doc_code, title, date_cre, note, uid, coldate, coltime, s_id, sataus){
        var aData = {
            app_mode: "document",
            doc_code: doc_code, 
            doc_datetime: date_cre,
            dataid: [{"doc_code":doc_code}, {"doc_title":title}, {"doc_datetime":date_cre}, {"doc_note":note}, {"uid":uid}, {"collect_date":coldate}, {"collect_time":coltime}, {"s_id":s_id}, {"doc_status":sataus}],
        };
        // console.log(aData);

        callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_document_health);
    }

    function saveFormDataComplete_document_health(flagSave, aData, rtnDataAjax){
        if(flagSave){
            $.notify("Save Data", "success");
        }
    }
</script>