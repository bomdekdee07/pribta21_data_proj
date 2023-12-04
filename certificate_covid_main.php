<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $doc_code = getQS("doctype");
    $sid = getSS("s_id");

    $data_dianosis = "";
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
    $mysqli->close();

    $html_bin = "";
    $html_bind = '<div class="fl-wrap-row h-35 font-s-2 fw-b hide-val-defult" 
                data-sid="'.$sid.'" 
                data-uid="'.$uid.'" 
                data-coldate="'.$coldate.'" 
                data-coltime="'.$coltime.'">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-80 fl-mid-left">
                    รูปแบบภาษา
                </div>
                <div class="fl-fill fl-mid-left holiday-ml-1">
                    <select id="format_date" name="format_date" style="width: 100px">
                        <option value="TH">Thai</option>
                        <option value="EN">English</option>
                    </select>
                </div>
            </div>
            <div class="fl-wrap-row h-25 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left fw-b">
                    ตรวจโควิด 19 โดยวิธี
                </div>
            </div>
            <div class="fl-wrap-row font-s-2 h-35">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-25 fl-mid-left">
                    <input type="checkbox" name="pcr_chkbox" class="bigcheckbox check_chk_nethod">
                </div>
                <div class="fl-fix w-180 fl-mid-left">
                    SARS-CoV-2 RT-PCR Test
                </div>
                <div class="fl-fix w-200"></div>
            </div>
            <div class="fl-wrap-row font-s-2 h-25">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-25 fl-mid-left">
                    <input type="checkbox" name="atk_chkbox" class="bigcheckbox check_chk_nethod">
                </div>
                <div class="fl-fix w-180 fl-mid-left">
                    ATK (Rapid Antigen Test)
                </div>
                <div class="fl-fix w-200"></div>
            </div>

            <div class="fl-wrap-row h-10"></div>
            <div class="fl-wrap-row h-30 font-s-2">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left fw-b">
                    ผลการตรวจ
                </div>
            </div>
            <div class="fl-wrap-row h-35 font-s-2 show-head">
                <div class="fl-fix w-60"></div>
                <div class="fl-fill fl-mid-left fw-b" style="color: red">
                    *โปรดเลือกวิธีการตรวจ*
                </div>
            </div>
            <div class="fl-wrap-row h-35 font-s-2 show-pcr" style="display: none;">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-200 fl-mid-left">
                    SARS-CoV-2 RT-PCR Test
                </div>
                <div class="fl-fix w-200 fl-mid-left">
                    <select id="result_pcr" name="result_pcr"><option value="">กรุณาเลือก</option><option value="N">ไม่พบเชิ้อ</option><option value="Y">พบเชิ้อ</option></select>
                </div>
            </div>
            <div class="fl-wrap-row h-35 font-s-2 show-atk" style="display: none;">
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-200 fl-mid-left">
                    ATK (Rapid Antigen Test)
                </div>
                <div class="fl-fix w-200 fl-mid-left">
                    <select id="result_atk" name="result_atk"><option value="">กรุณาเลือก</option><option value="N">ไม่พบเชิ้อ</option><option value="Y">พบเชิ้อ</option></select>
                </div>
            </div>

            <div class="fl-wrap-row h-10"></div>
            <div class="fl-wrap-row h-35 font-s-2">
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
        <button class="btn btn-success" id="btPrintPdfCovidCertificate" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
        <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
    </div>
    <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-danger" id="btCancelCovid" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
    </div>
    <div class="fl-fix w-100 fl-mid-left">
        <button class="btn btn-primary" id="btPrintPdfCovidCertificate_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>  
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
        var date_cur = year+"-"+month+"-"+day;
        $("#medical_create_pdf [name=start_day]").datepicker({
			dateFormat: "yy-mm-dd",
            // minDate: date_cur,
			changeMonth: true,
			changeYear: true
		});

        $("#medical_create_pdf [name=stop_day]").datepicker({
			dateFormat: "yy-mm-dd",
            // minDate: date_cur,
			changeMonth: true,
			changeYear: true
		});

        // Show or hide ผลตรวจ
        $("#medical_create_pdf [name=pcr_chkbox]").off("change");
        $("#medical_create_pdf [name=pcr_chkbox]").on("change", function(){
            var check_tick =  $(this).is(":checked");
            var check_length = $(".check_chk_nethod").filter(":checked").length;
            if(check_tick){
                $("#medical_create_pdf .show-head").hide();
                $("#medical_create_pdf .show-pcr").show();
            }
            else{
                $("#medical_create_pdf .show-pcr").hide();
                $("#medical_create_pdf [name=result_pcr]").val("");
            }

            if(check_length < 1){
                $("#medical_create_pdf .show-head").show();
            }
        });
        $("#medical_create_pdf [name=atk_chkbox]").off("change");
        $("#medical_create_pdf [name=atk_chkbox]").on("change", function(){
            var check_tick =  $(this).is(":checked");
            var check_length = $(".check_chk_nethod").filter(":checked").length;
            if(check_tick){
                $("#medical_create_pdf .show-head").hide();
                $("#medical_create_pdf .show-atk").show();
            }
            else{
                $("#medical_create_pdf .show-atk").hide();
                $("#medical_create_pdf [name=result_atk]").val("");
            }

            if(check_length < 1){
                $("#medical_create_pdf .show-head").show();
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

        $("#medical_create_pdf #btPrintPdfCovidCertificate").off("click");
        $("#medical_create_pdf #btPrintPdfCovidCertificate").on("click", function(){
            var uidS = $("#medical_create_pdf").data("uid");
            var coldateS = $("#medical_create_pdf").data("coldate");
            var coltimeS = $("#medical_create_pdf").data("coltime");
            var sidS = $("#medical_create_pdf .hide-val-defult").data("sid");
            var live_dayS = $("#medical_create_pdf [name=live_day]").filter(":checked").val();
            var many_dayS = $("#medical_create_pdf [name=howMany_day]").val();
            var start_dayS = $("#medical_create_pdf [name=start_day]").val();
            var stop_dayS = $("#medical_create_pdf [name=stop_day]").val();
            var status_otherS = $("#medical_create_pdf [name=other]").filter(":checked").val();
            var other_textS = $("#medical_create_pdf [name=other_txt]").val();
            var formate_date_leg = $("#medical_create_pdf [name=format_date]").val();
            var method_pcr_s = $("#medical_create_pdf [name=pcr_chkbox]").is(":checked");
            var method_atk_s = $("#medical_create_pdf [name=atk_chkbox]").is(":checked");
            var result_pcr_s = $("#medical_create_pdf [name=result_pcr]").val();
            var result_atk_s = $("#medical_create_pdf [name=result_atk]").val();
            var form_id = $("#medical_create_pdf");

            var aData = {
                doc_mode: "insert",
                uid: uidS,
                coldate: coldateS,
                coltime: coltimeS,
                live_day: live_dayS,
                many_day: many_dayS,
                start_day: start_dayS,
                stop_day: stop_dayS,
                status_other: status_otherS,
                other_text: other_textS,
                format_date: formate_date_leg,
                method_pcr: method_pcr_s,
                method_atk: method_atk_s,
                result_pcr: result_pcr_s,
                result_atk: result_atk_s
            };
            // console.log(aData);            

            $.ajax({url: "covid_certificate_pdf.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var date_now = String(result);
                    var date_now = date_now.split(",");
                    
                    saveFormData_document("MEDICAL_COVID", "ใบรับรองแพทย์ ตรวจโควิด 19", date_now[0], "ใบรับรองแพทย์ ตรวจโควิด 19", uidS, coldateS, coltimeS, sidS, 1);
                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"MEDICAL_COVID_"+uidS+"_"+coldate_con+""+coltime_con+".pdf";

                    $("#btPrintPdfCovidCertificate").next(".spinner").show();
                    $("#btPrintPdfCovidCertificate").hide();

                    setTimeout(function(){
                        close_dlg(form_id);
                    }, 1000);
            }});
        });

        // Button cancel
        $("#btCancelCovid").off("click");
        $("#btCancelCovid").on("click", function(){
            $("#btPrintPdfCovidCertificate_view").show();
            $(".hide-bt-pdf").hide();
        });

        // Button View
        $("#btPrintPdfCovidCertificate_view").off("click");
        $("#btPrintPdfCovidCertificate_view").on("click", function(){
            var uidS = $("#medical_create_pdf").data("uid");
            var coldateS = $("#medical_create_pdf").data("coldate");
            var coltimeS = $("#medical_create_pdf").data("coltime");
            var sidS = $("#medical_create_pdf .hide-val-defult").data("sid");
            var live_dayS = $("#medical_create_pdf [name=live_day]").filter(":checked").val();
            var many_dayS = $("#medical_create_pdf [name=howMany_day]").val();
            var start_dayS = $("#medical_create_pdf [name=start_day]").val();
            var stop_dayS = $("#medical_create_pdf [name=stop_day]").val();
            var status_otherS = $("#medical_create_pdf [name=other]").filter(":checked").val();
            var other_textS = $("#medical_create_pdf [name=other_txt]").val();
            var formate_date_leg = $("#medical_create_pdf [name=format_date]").val();
            var method_pcr_s = $("#medical_create_pdf [name=pcr_chkbox]").is(":checked");
            var method_atk_s = $("#medical_create_pdf [name=atk_chkbox]").is(":checked");
            var result_pcr_s = $("#medical_create_pdf [name=result_pcr]").val();
            var result_atk_s = $("#medical_create_pdf [name=result_atk]").val();
            
            var gen_url_view = "covid_certificate_pdf.php?doc_mode=view&uid="+uidS+"&coldate="+coldateS+"&coltime="+coltimeS+"&live_day="+live_dayS+"&many_day="+many_dayS+"&start_day="+start_dayS+"&stop_day="+stop_dayS+"&status_other="+status_otherS+"&other_text="+other_textS+"&format_date="+formate_date_leg+"&method_pcr="+method_pcr_s+"&method_atk="+method_atk_s+"&result_pcr="+result_pcr_s+"&result_atk="+result_atk_s;
            // console.log(gen_url_view);
            window.open(gen_url_view,'_blank');

            $("#btPrintPdfCovidCertificate_view").hide();
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

            $("#document_master_tempfile_invoice #document_new_tempfile").next(".spinner").hide();
            $("#document_master_tempfile_invoice #document_new_tempfile").show();
        }
    }
</script>