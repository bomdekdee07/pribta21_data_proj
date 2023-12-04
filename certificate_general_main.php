<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $doc_code = getQS("doctype");
    $sid = getSS("s_id");

    $data_dianosis = array("dx"=>"");
    $query = "SELECT data_result 
    from p_data_result 
    where uid = ?
    and collect_date = ?
    and collect_time  = ?
    and data_id = 'cn_dx';";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $uid, $coldate, $coltime);

    if($stmt->execute()){
        $stmt->bind_result($data_result);
        while($stmt->fetch()){
            $data_dianosis["dx"] = $data_result;
        }
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

    $stHtmlDx = "";
    $stHtmlDx .= "$('#medical_create_pdf [name=diagnosis_edit]').val(".json_encode($data_dianosis["dx"]).");";

    $html_bin = "";
    $html_bind = '<div class="fl-wrap-row h-30 font-s-2 fw-b check-staff" data-checkstaff="'.$data_check_staff.'">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-80 fl-mid-left">
                    รูปแบบวันที่
                </div>
                <div class="fl-fix w-300 fl-mid-left">
                    <select id="format_date" name="format_date" style="width: 100px">
                        <option value="TH">Thai</option>
                        <option value="EN">English</option>
                    </select>
                </div>
                <label>
                    <div class="fl-fix w-350 fl-mid-left">
                        <input type="checkbox" name="change_certificate_drug" class="bigcheckbox"> ใบรับรองการถือยาไปต่างประเทศ
                    </div>
                </label>
            </div>

            <div class="fl-wrap-row h-10 font-s-2"></div>

            <div class="fl-wrap-row h-25 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left fw-b">
                    Diagnosis
                </div>
            </div>
            <div class="fl-wrap-row h-100 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left">
                    <textarea rows="4" class="input-group" name="diagnosis_edit"></textarea>
                </div>
                <div class="fl-fix w-200"></div>
            </div>

            <div class="fl-wrap-row h-30 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left fw-b">
                    ความคิดเห็นแพทย์ (Physician is opnion)
                </div>
            </div>
            <div class="fl-wrap-row h-130 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left">
                    <textarea rows="4" class="input-group" name="phy_opnion"></textarea>
                </div>
                <div class="fl-fix w-200"></div>
            </div>

            <div class="fl-wrap-row h-40 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-25 fl-mid-left">
                    <input type="checkbox" name="live_day" class="bigcheckbox">
                </div>
                <div class="fl-fix w-180 fl-mid-left">
                    ควรหยุดพักรักษาตัวเป็นเวลา
                </div>
                <div class="fl-fix w-40 fl-mid-left hide-untick">
                    <input type="text" name="howMany_day" class="input-group" disabled>
                </div>
                <div class="fl-fix w-50 fl-mid hide-untick">
                    วัน
                </div>
                <div class="fl-fix w-20 fl-mid"></div>
                <div class="fl-fix w-70 fl-mid-left hide-untick">
                    ตั้งแต่วันที่
                </div>
                <div class="fl-fix w-150 fl-mid-left hide-untick">
                    <input type="text" name="start_day" class="input-group">
                </div>
                <div class="fl-fix w-50 fl-mid hide-untick">
                    ถึง
                </div>
                <div class="fl-fix w-150 fl-mid-left hide-untick">
                    <input type="text" name="stop_day" class="input-group">
                </div>
            </div>
            <div class="fl-wrap-row font-s-2 h-60">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-25 h-30 fl-mid-left">
                    <input type="checkbox" name="other" class="bigcheckbox">
                </div>
                <div class="fl-fix w-45 fl-mid-left h-30">
                    อื่นๆ
                </div>
                <div class="fl-fill fl-mid-left h-50 hide-untick-other">
                    <textarea rows="2" class="input-group" name="other_txt"></textarea>
                </div>
                <div class="fl-fix w-200"></div>
            </div>';

    echo $html_bind;
?>
    <div class="fl-wrap-row font-s-2 h-125" id="dlg_signature_main">
        <? include("doc_signature_main.php"); ?>
    </div>
    <div class="fl-wrap-row font-s-2 h-35">
        <div class="fl-fix w-60"></div>
        <div class="fl-fix w-130 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
            <button class="btn btn-success" id="btPrintPdfMedicalCertificate" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
            <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
        </div>
        <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
            <button class="btn btn-danger" id="btCancel" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
        </div>
        <div class="fl-fix w-100 fl-mid-left">
            <button class="btn btn-primary" id="btPrintPdfMedicalCertificate_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>  
        </div>
    </div>

<script>
    $(document).ready(function(){
        <? echo $stHtmlDx; ?>

        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();
        var year = d.getFullYear();
        if (month < 10) {
            month = "0" + month;
        }
        var date_cur = year+"-"+month+"-"+day;
        $("#medical_create_pdf [name=start_day]").datepicker({
			dateFormat: "yy-mm-dd",
            // minDate: date_cur,
			changeMonth: true,
			changeYear: true
		});

        $("#medical_create_pdf [name=stop_day]").datepicker({
			dateFormat: "yy-mm-dd",
            minDate: date_cur,
			changeMonth: true,
			changeYear: true
		});

        // Tick change type certificate to drug
        $("[name=change_certificate_drug]").off("change");
        $("[name=change_certificate_drug]").on("change", function(){
            if($("[name=change_certificate_drug]").is(":checked")){
                var uidS = $("#medical_create_pdf").data("uid");
                var coldateS = $("#medical_create_pdf").data("coldate");
                var coltimeS = $("#medical_create_pdf").data("coltime");
                var langauge_select_s = $("[name=format_date]").val();
                var aData = {
                    uid: uidS,
                    coldate: coldateS,
                    coltime: coltimeS,
                    langauge_select: langauge_select_s
                };

                $.ajax({
                    url: "certificate_general_support_drug.php",
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(sResult){
                        var txt_defult = "The patient needs to take "+sResult+" for medical conditions and needs to carry the medication(s) abroad. Any healthcare support you can provide would be appreciated. If you need any further information, please contact us via the address provided above.";
                        $("[name=phy_opnion]").val(txt_defult);
                    }
                });
            }
            else{
                $("[name=phy_opnion]").val("");
            }
        });

        // Select language certificate to drug
        $("#medical_create_pdf [name=format_date]").off("change");
        $("#medical_create_pdf [name=format_date]").on("change", function(){
            var check_drug = $("#medical_create_pdf [name=change_certificate_drug]").is(":checked");
            if(check_drug){
                var uidS = $("#medical_create_pdf").data("uid");
                var coldateS = $("#medical_create_pdf").data("coldate");
                var coltimeS = $("#medical_create_pdf").data("coltime");
                var langauge_select_s = $("[name=format_date]").val();
                var aData = {
                    uid: uidS,
                    coldate: coldateS,
                    coltime: coltimeS,
                    langauge_select: langauge_select_s
                };

                $.ajax({
                    url: "certificate_general_support_drug.php",
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(sResult){
                        var txt_defult = "The patient needs to take "+sResult+" for medical conditions and needs to carry the medication(s) abroad. Any healthcare support you can provide would be appreciated. If you need any further information, please contact us via the address provided above.";
                        $("[name=phy_opnion]").val(txt_defult);
                    }
                });
            }
        });

        $("#medical_create_pdf [name=start_day]").off("change");
        $("#medical_create_pdf").on("change", "[name=start_day]", function(){
            var start_date = $(this).val();
            var stop_day = $("#medical_create_pdf [name=stop_day]").val();
            
            if(stop_day != ""){
                var diff_start = new Date(start_date);
                var diff_end = new Date(stop_day);
                var cal_date = new Date(diff_end-diff_start);
                var con_days  = (cal_date/1000/60/60/24)+1;

                $("#medical_create_pdf [name=howMany_day]").val("");
                $("#medical_create_pdf [name=howMany_day]").val(con_days);
            }
        });

        $("#medical_create_pdf [name=stop_day]").off("change");
        $("#medical_create_pdf").on("change", "[name=stop_day]", function(){
            var stop_day = $(this).val();
            var start_date = $("#medical_create_pdf [name=start_day]").val();
            
            if(start_date != ""){
                var diff_start = new Date(start_date);
                var diff_end = new Date(stop_day);
                var cal_date = new Date(diff_end-diff_start);
                var con_days  = (cal_date/1000/60/60/24)+1;

                $("#medical_create_pdf [name=howMany_day]").val("");
                $("#medical_create_pdf [name=howMany_day]").val(con_days);
            }
        });

        $("#medical_create_pdf .hide-untick").hide();
        $("#medical_create_pdf .hide-untick-other").hide();

        $("#medical_create_pdf [name=live_day]").off("change");
        $("#medical_create_pdf [name=live_day]").on("change", function(){
            var  check_status = $("#medical_create_pdf [name=live_day]").filter(":checked").val();
            if(check_status == "on"){
                $("#medical_create_pdf .hide-untick").show();
            }
            else{
                $("#medical_create_pdf .hide-untick").hide();
                $("#medical_create_pdf [name=howMany_day]").val("");
                $("#medical_create_pdf [name=start_day]").val("");
                $("#medical_create_pdf [name=stop_day]").val("");
            }
        });

        $("#medical_create_pdf [name=other]").off("change");
        $("#medical_create_pdf [name=other]").on("change", function(){
            var  check_status = $("#medical_create_pdf [name=other]").filter(":checked").val();
            if(check_status == "on"){
                $("#medical_create_pdf .hide-untick-other").show();
            }
            else{
                $("#medical_create_pdf .hide-untick-other").hide();
                $("#medical_create_pdf [name=other_txt]").val("");
            }
        });

        // Button confrim
        $("#medical_create_pdf #btPrintPdfMedicalCertificate").off("click");
        $("#medical_create_pdf #btPrintPdfMedicalCertificate").on("click", function(){
            var uidS = $("#medical_create_pdf").data("uid");
            var coldateS = $("#medical_create_pdf").data("coldate");
            var coltimeS = $("#medical_create_pdf").data("coltime");
            var sidS = $("#medical_create_pdf").data("sid");
            var check_staff = $(".check-staff").data("checkstaff");

            var phy_opnionS = $("#medical_create_pdf [name=phy_opnion]").val().replace(/\n/g, "\\n");
            var live_dayS = $("#medical_create_pdf [name=live_day]").filter(":checked").val();
            var many_dayS = $("#medical_create_pdf [name=howMany_day]").val();
            var start_dayS = $("#medical_create_pdf [name=start_day]").val();
            var stop_dayS = $("#medical_create_pdf [name=stop_day]").val();
            var status_otherS = $("#medical_create_pdf [name=other]").filter(":checked").val();
            var other_textS = $("#medical_create_pdf [name=other_txt]").val();
            var dianosis_editS = $("#medical_create_pdf [name=diagnosis_edit]").val().replace(/\n/g, "\\n");
            var formate_date_leg = $("#medical_create_pdf [name=format_date]").val();
            var form_id = $("#medical_create_pdf");
            var format_leg = $("#doc_signature_main #type_leg_main:checked").val();

            if(check_staff != ""){
                var aData = {
                    doc_mode: "insert",
                    uid: uidS,
                    coldate: coldateS,
                    coltime: coltimeS,
                    phy_opnion: phy_opnionS,
                    live_day: live_dayS,
                    many_day: many_dayS,
                    start_day: start_dayS,
                    stop_day: stop_dayS,
                    status_other: status_otherS,
                    other_text: other_textS,
                    dianosis_edit: dianosis_editS,
                    format_date: formate_date_leg
                };
                // console.log(aData);

                $.ajax({url: "medical_certificate_pdf.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var date_now = String(result);
                        var date_now = date_now.split(",");
                        
                        saveFormData_document("MEDICAL_C", "ใบรับรองแพทย์", date_now[0], phy_opnionS, uidS, coldateS, coltimeS, sidS, 1);
                        var data_date_time_con = date_now[0].split(" ");
                        var coldate_con = data_date_time_con[0].split("-");
                        coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                        var coltime_con = data_date_time_con[1].split(":");
                        coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                        var gen_link = "pdfoutput/"+"MEDICAL_C_"+uidS+"_"+coldate_con+""+coltime_con+".pdf";

                        $("#medical_create_pdf #btPrintPdfMedicalCertificate").next(".spinner").show();
                        $("#medical_create_pdf #btPrintPdfMedicalCertificate").hide();

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
        $("#medical_create_pdf #btCancel").off("click");
        $("#medical_create_pdf #btCancel").on("click", function(){
            $("#medical_create_pdf #btPrintPdfMedicalCertificate_view").show();
            $("#medical_create_pdf .hide-bt-pdf").hide();
        });

        // Button View
        $("#medical_create_pdf #btPrintPdfMedicalCertificate_view").off("click");
        $("#medical_create_pdf #btPrintPdfMedicalCertificate_view").on("click", function(){
            var uidS = $("#medical_create_pdf").data("uid");
            var coldateS = $("#medical_create_pdf").data("coldate");
            var coltimeS = $("#medical_create_pdf").data("coltime");
            var sidS = $("#medical_create_pdf").data("sid");
            var check_staff = $(".check-staff").data("checkstaff");

            var phy_opnionS = $("#medical_create_pdf [name=phy_opnion]").val().replace(/\n/g, "\\n");
            var live_dayS = $("#medical_create_pdf [name=live_day]").filter(":checked").val();
            var many_dayS = $("#medical_create_pdf [name=howMany_day]").val();
            var start_dayS = $("#medical_create_pdf [name=start_day]").val();
            var stop_dayS = $("#medical_create_pdf [name=stop_day]").val();
            var status_otherS = $("#medical_create_pdf [name=other]").filter(":checked").val();
            var other_textS = $("#medical_create_pdf [name=other_txt]").val();
            var dianosis_editS = $("#medical_create_pdf [name=diagnosis_edit]").val().replace(/\n/g, "\\n");
            var formate_date_leg = $("#medical_create_pdf [name=format_date]").val();
            
            var aData = {
                doc_mode: "view",
                uid: uidS,
                coldate: coldateS,
                coltime: coltimeS,
                phy_opnion: phy_opnionS,
                live_day: live_dayS,
                many_day: many_dayS,
                start_day: start_dayS,
                stop_day: stop_dayS,
                status_other: status_otherS,
                other_text: other_textS,
                dianosis_edit: dianosis_editS,
                format_date: formate_date_leg
            };
            // console.log(aData); 
            
            var gen_url_view = "medical_certificate_pdf.php?doc_mode=view&uid="+uidS+"&coldate="+coldateS+"&coltime="+coltimeS+"&phy_opnion="+phy_opnionS+"&live_day="+live_dayS+"&many_day="+many_dayS+"&start_day="+start_dayS+"&stop_day="+stop_dayS+"&status_other="+status_otherS+"&other_text="+other_textS+"&dianosis_edit="+dianosis_editS+"&format_date="+formate_date_leg;
            window.open(gen_url_view,'_blank');

            $("#medical_create_pdf #btPrintPdfMedicalCertificate_view").hide();
            $("#medical_create_pdf .hide-bt-pdf").show();
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

            // $("#document_master_tempfile_invoice #document_new_tempfile").next(".spinner").hide();
            // $("#document_master_tempfile_invoice #document_new_tempfile").show();
        }
    }
</script>