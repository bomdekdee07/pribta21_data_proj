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

    $js_html = "";
    foreach($data_result_loop as $data_id => $val){
        if($val["type"] == "radio"){
            $js_html .= '$("[name='.$data_id.'_pdf][value='.$val["result"].']").prop("checked", true);';
        }
        else if($val["type"] == "text"){
            $js_html .= '$("[name='.$data_id.'_pdf]").val('.json_encode($val["result"]).');';
        }
    }
    // echo $js_html;
?>
<div class="fl-wrap-row h-40 font-s-2 fw-b" id="certificate_driving_main" 
data-docname_th="<? echo isset($data_md["s_name"])? $data_md["s_name"]: ""; ?>" 
data-docname_en="<? echo isset($data_md["s_name_en"])? $data_md["s_name_en"]: ""; ?>" 
data-doclicen="<? echo isset($data_md["license_md"])? $data_md["license_md"]: ""; ?>"
data-patient_name="<? echo $patient_name_th; ?>" 
data-idaddress="<? echo isset($data_patient["id_address"])? $data_patient["id_address"]: ""; ?>" 
data-citizen="<? echo isset($data_patient["citizen_id"])? $data_patient["citizen_id"]: ""; ?>" 
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
    <!-- <div class="fl-fix w-60"></div> -->
    <!-- <div class="fl-fill fl-mid-left">
        ประวัติสุขภาพของผู้เข้ารับบริการ
    </div> -->
</div>
<!-- <div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-200 fl-mid-left">
        <label>โรคประจำตัว</label>
    </div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="congenital_disease_pdf" value="N"  /> ไม่มี</label>
    </div>
    <div class="fl-fix w-70 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="congenital_disease_pdf" value="Y"/> มี (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="congenital_disease_txt_pdf" style="height: 24px; width: 500px;"></label>
    </div>
</div>

<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-200 fl-mid-left">
        <label>อุบัติเหตุ และ ผ่าตัด</label>
    </div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="surgery_pdf" value="N"  /> ไม่มี</label>
    </div>
    <div class="fl-fix w-70 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="surgery_pdf" value="Y"/> มี (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="surgery_txt_pdf" style="height: 24px; width: 500px;"></label>
    </div>
</div>

<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-200 fl-mid-left">
        <label>เคยเข้ารับรักษาในโรงพยาบาล</label>
    </div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="hospitalized" value="N" disabled /> ไม่มี</label>
    </div>
    <div class="fl-fix w-70 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="hospitalized" value="Y" disabled /> มี (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="hospitalized_text" style="height: 24px; width: 500px;" readonly></label>
    </div>
</div>

<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-200 fl-mid-left">
        <label>โรคลมชัก</label>
    </div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="epilepsy" value="N" disabled /> ไม่มี</label>
    </div>
    <div class="fl-fix w-70 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="epilepsy" value="Y" disabled /> มี (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="epilepsy_text" style="height: 24px; width: 500px;" readonly></label>
    </div>
</div>

<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-200 fl-mid-left">
        <label>ประวัติอื่นที่สำคัญ</label>
    </div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="other_history" value="N" disabled /> ไม่มี</label>
    </div>
    <div class="fl-fix w-70 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="other_history" value="Y"  disabled/> มี (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="other_history_text" style="height: 24px; width: 500px;" readonly></label>
    </div>
</div> -->

<div class="fl-wrap-row h-30 font-s-2 fw-b">
    <div class="fl-fix w-60"></div>
    <div class="fl-fill fl-mid-left">
        สภาพร่างกายอยู่ในเกณฑ์
    </div>
</div>
<div class="fl-wrap-row h-35 font-s-2">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-60 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="physical_condition" value="N" checked /> ปกติ</label>
    </div>
    <div class="fl-fix w-105 fl-mid-left">
        <label><input type="radio" class="changeRadioDriv" name="physical_condition" value="Y" /> ผิดปกติ (ระบุ)</label>
    </div>
    <div class="fl-fill fl-mid-left">
        <label><input type="text" name="physical_condition_text" style="height: 24px; width: 665px;" maxlength="70"></label>
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
        <label><input type="text" name="other_diseases_text" style="height: 24px; width: 830px;" maxlength="100"></label>
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
        <label><textarea name="comment_text" rows="2" style="height: 50px; width: 830px;" maxlength="125"></textarea></label>
    </div>
</div>

<div class="fl-wrap-row font-s-2 h-125" id="dlg_signature_main">
    <? include("doc_signature_main.php"); ?>
</div>

<div class="fl-wrap-row font-s-2 h-35">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-130 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-success" id="btPrintPdfDrivingCertificate" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
        <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
    </div>
    <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-danger" id="btCancelDriving" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
    </div>
    <div class="fl-fix w-100 fl-mid-left">
        <button class="btn btn-primary" id="btPrintPdfDrivingCertificate_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>  
    </div>
</div>

<script>
    $(document).ready(function(){
        <?  echo $js_html; ?>

        // Input maxlength check
        $("[name=physical_condition_text]").off("input");
        $("[name=physical_condition_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=physical_condition_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Input maxlength check
        $("[name=comment_text]").off("input");
        $("[name=comment_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=comment_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Input maxlength check
        $("[name=other_diseases_text]").off("input");
        $("[name=other_diseases_text]").on("input", function(){
            var textareas = this.value;
            if (textareas.length >= $("[name=other_diseases_text]").attr("maxlength")) {
                alert("ไม่สามารถพิมพ์ข้อความได้มากกว่านี้ครับ");
            }
        });

        // Radio change
        $(".changeRadioDriv").off("change");
        $(".changeRadioDriv").change(function(){
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
        $(".changeRadioDriv").change();

        // Button confrim
        $("#btPrintPdfDrivingCertificate").off("click");
        $("#btPrintPdfDrivingCertificate").on("click", function(){
            var sUid = $("#certificate_driving_main").data("uid");
            var sColdate = $("#certificate_driving_main").data("coldate");
            var sColtime = $("#certificate_driving_main").data("coltime");

            var sDoc_th = $("#certificate_driving_main").data("docname_th");
            var sDoc_en = $("#certificate_driving_main").data("docname_en");
            var sDo_licen = $("#certificate_driving_main").data("doclicen");
            var sPatient_name = $("#certificate_driving_main").data("patient_name");
            var sIdaddress = $("#certificate_driving_main").data("idaddress");
            var sSex = $("#certificate_driving_main").data("sex");
            var sCitizen = $("#certificate_driving_main").data("citizen");
            var sWeight = $("#certificate_driving_main").data("weight");
            var sHeight = $("#certificate_driving_main").data("height");
            var sBph = $("#certificate_driving_main").data("bph");
            var sBpd = $("#certificate_driving_main").data("bpd");
            var sPr = $("#certificate_driving_main").data("pr");
            var sRr = $("#certificate_driving_main").data("rr");
            var sSid = $("#certificate_driving_main").data("sid");

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

            var sPhysical_condition = $("[name=physical_condition]:checked").val();
            var sPhysical_condition_text = $("[name=physical_condition_text]").val();

            var sComment_text = $("[name=comment_text]").val();
            var sOther_diseases_text = $("[name=other_diseases_text]").val();
            var check_staff = $(".check-staff").data("checkstaff");

            var form_id = $("#medical_create_pdf");

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
                    idaddress: sIdaddress,
                    citizen: sCitizen,
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
                    other_diseases_text: sOther_diseases_text
                };
                // console.log(aData);

                $.ajax({url: "driving_certificate_pdf.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var date_now = String(result);
                        var date_now = date_now.split(",");
                        
                        saveFormData_document("MEDICAL_DRIVING", "ใบรับรองแพทย์ขออนุญาติขับรถ", date_now[0], "", sUid, sColdate, sColtime, sSid, 1);
                        var data_date_time_con = date_now[0].split(" ");
                        var coldate_con = data_date_time_con[0].split("-");
                        coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                        var coltime_con = data_date_time_con[1].split(":");
                        coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                        var gen_link = "pdfoutput/"+"MEDICAL_DRIVING"+sUid+"_"+sColdate+""+sColtime+".pdf";

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
        $("#btCancelDriving").off("click");
        $("#btCancelDriving").on("click", function(){
            $("#btPrintPdfDrivingCertificate_view").show();
            $(".hide-bt-pdf").hide();
        });

        // Button View
        $("#btPrintPdfDrivingCertificate_view").off("click");
        $("#btPrintPdfDrivingCertificate_view").on("click", function(){
            var sUid = $("#certificate_driving_main").data("uid");
            var sColdate = $("#certificate_driving_main").data("coldate");
            var sColtime = $("#certificate_driving_main").data("coltime");

            var sDoc_th = $("#certificate_driving_main").data("docname_th");
            var sDoc_en = $("#certificate_driving_main").data("docname_en");
            var sDo_licen = $("#certificate_driving_main").data("doclicen");
            var sPatient_name = $("#certificate_driving_main").data("patient_name");
            var sIdaddress = $("#certificate_driving_main").data("idaddress");
            var sSex = $("#certificate_driving_main").data("sex");
            var sCitizen = $("#certificate_driving_main").data("citizen");
            var sWeight = $("#certificate_driving_main").data("weight");
            var sHeight = $("#certificate_driving_main").data("height");
            var sBph = $("#certificate_driving_main").data("bph");
            var sBpd = $("#certificate_driving_main").data("bpd");
            var sPr = $("#certificate_driving_main").data("pr");
            var sRr = $("#certificate_driving_main").data("rr");

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

            var sPhysical_condition = $("[name=physical_condition]:checked").val();
            var sPhysical_condition_text = $("[name=physical_condition_text]").val();

            var sComment_text = $("[name=comment_text]").val();
            var sOther_diseases_text = $("[name=other_diseases_text]").val();
            
            var gen_url_view = "driving_certificate_pdf.php?doc_mode=view&uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&name_doc_th="+sDoc_th+"&name_doc_en="+sDoc_en+"&patient_name="+sPatient_name+"&idaddress="+sIdaddress+"&citizen="+sCitizen+"&weight="+sWeight+"&height="+sHeight+"&bph="+sBph+"&bpd="+sBpd+"&pr="+sPr+"&congenital_disease_pdf="+sCongenital_disease_pdf+"&surgery_pdf="+sSurgery_pdf+"&hospitalized="+sHospitalized+"&epilepsy="+sEpilepsy+"&other_history="+sOther_history+"&congenital_disease_txt_pdf="+sCongenital_disease_txt_pdf+"&surgery_txt_pdf="+sSurgery_txt_pdf+"&hospitalized_text="+sHospitalized_text+"&epilepsy_text="+sEpilepsy_text+"&other_history_text="+sOther_history_text+"&physical_condition="+sPhysical_condition+"&physical_condition_text="+sPhysical_condition_text+"&comment_text="+sComment_text+"&sex="+sSex+"&rr="+sRr+"&other_diseases_text="+sOther_diseases_text+"&doc_licen="+sDo_licen;
            console.log(gen_url_view);
            window.open(gen_url_view,'_blank');

            $("#btPrintPdfDrivingCertificate_view").hide();
            $(".hide-bt-pdf").show();
        });
    });

    function close_dlg(formid){
        var objthis = formid;
        closeDlg(objthis, "0");
    }

    function saveFormData_document(doc_code, title, date_cre, note, uid, coldate, coltime, s_id, sataus){
        var aData = {
            app_mode: "document",
            doc_code: doc_code, 
            doc_datetime: date_cre,
            dataid: [{"doc_code":doc_code}, {"doc_title":title}, {"doc_datetime":date_cre}, {"doc_note":note}, {"uid":uid}, {"collect_date":coldate}, {"collect_time":coltime}, {"s_id":s_id}, {"doc_status":sataus}],
        };
        // console.log(aData);

        callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_document);
    }

    function saveFormDataComplete_document(flagSave, aData, rtnDataAjax){
        if(flagSave){
            $.notify("Save Data", "success");
        }
    }
</script>