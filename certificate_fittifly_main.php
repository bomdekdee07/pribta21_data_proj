<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $doc_code = getQS("doctype");
    $sid = getSS("s_id");

    $data_result_lab = array();
    $bind_val = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT main.time_specimen_collect as time_lastupdate,
        lab_test.lab_name,
        result.lab_result_note,
        result.lab_result,
        info.fname,
        info.sname,
        info.en_fname,
        info.en_sname,
        info.date_of_birth,
        info.nation,
        info.country_other,
        info.passport_id
    from p_lab_order main
    left join p_lab_order_lab_test labid on(labid.uid = main.uid and labid.collect_date = main.collect_date)
    left join p_lab_test lab_test on(lab_test.lab_id = labid.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id= lab_test.lab_group_id)
    left join p_lab_result result on(result.lab_id = labid .lab_id and result.uid = main.uid and result.collect_date = main.collect_date and result.collect_time = main.collect_time)
    left join patient_info info on(info.uid = main.uid)
    where labid.lab_id in ('SARS-CoV-2', 'ATK')
    and main.uid = ?
    and main.collect_date = ?
    and main.collect_time = ?
    order by main.time_specimen_collect;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_val, ...$array_val);

    $split_date_time = "";
    $result_date = "";
    $result_time = "";
    if($stmt->execute()){
        $stmt->bind_result($time_lasttime, $lab_name, $note, $lab_result, $fname, $sname, $en_fname, $en_sname, $date_of_birth, $nation, $country_other, $passport_id);
        while($stmt->fetch()){
            $data_result_lab[$lab_name]["name_patient"] = ($en_sname != ""? $en_fname." ".$en_sname : $fname." ".$sname);
            $data_result_lab[$lab_name]["passport"] = $passport_id;
            $data_result_lab[$lab_name]["dateofbirth"] = date("d/m/Y", strtotime($date_of_birth));
            $data_result_lab[$lab_name]["code_nation"] = ($nation == "THA"? "THA" : $country_other);

            $split_date_time = explode(" ", $time_lasttime);
            $result_date = $split_date_time[0];
            $result_time = isset($split_date_time[1])? $split_date_time[1]: "";
            $data_result_lab[$lab_name]["date_result"] = date("d/m/Y", strtotime($result_date));
            $data_result_lab[$lab_name]["time_result"] = $result_time;
            $data_result_lab[$lab_name]["name"] = $lab_name;
            $data_result_lab[$lab_name]["note"] = $note;
            $data_result_lab[$lab_name]["result"] = $lab_result;
        }
        // print_r($data_result_lab);
    }

    $stmt->close();
    $mysqli->close();

    $html_js = "";
    $html_js .= '$("[name=fittofly_diagnosis_edit]").val('.
    json_encode("This is to certify that above named patient has been examined and tested at Pribta Tangerine Polyclinic. This
person is healthy with no preexisting medical comorbidities. Pertinent negatives:
No fever
No shortness of breath
No concerning symptoms for COVID-19
Otherwise normal physical examination").');';

    $lab_result_st = "";
    $name_patient = "";
    $passport = "";
    $date_of_birth = "";
    $date_result = "";
    $time_result = "";
    foreach($data_result_lab as $keylab => $val){
        $lab_result_st = $val["name"]." (".trim($val["note"], ". ")."): ".($val["result"]=="NEG"? "Undetected":"");
        $html_js .= '$("[name=fittofly_result]").val('.json_encode($lab_result_st).');';

        if($val["code_nation"] != "THA"){
            $html_js .= '$("[name=fittofly_nation_choice][value=2]").prop("checked", true);';
            $html_js .= '$("[name=fittofly_nation]").val('.json_encode($val["code_nation"]).');';
        }
        else{
            $html_js .= '$("[name=fittofly_nation_choice][value=THA]").prop("checked", true);';
        }

        $name_patient = $val["name_patient"];
        $passport = $val["passport"];
        $date_of_birth = $val["dateofbirth"];
        $date_result = $val["date_result"];
        $time_result = $val["time_result"];
    }

    $html_bind = "";
    $html_nation_head = "";
    $html_nation_end = "";
    
    $html_nation_head .= 
                    '<div class="fl-wrap-row h-10"></div>
                    <div class="fl-wrap-row h-20 font-s-2 fw-b">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Nation
                        </div>
                    </div>
                    <div class="fl-wrap-row h-40 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fix w-100 fl-mid-left">
                            <input name="fittofly_nation_choice" type="radio" value="THA" data-odata="" class="savedata"/> ไทย Thai
                        </div>
                        <div class="fl-fix w-85 fl-mid-left">
                            <input name="fittofly_nation_choice" type="radio" value="2" data-odata="" class="savedata checkdata"/> อื่นๆ Other
                        </div>
                        <div class="fl-fix w-100 fl-mid-left">
                            <select name="fittofly_nation">';
    $html_nation_end .= 
                            '</select>
                        </div>
                    </div>';

    $html_bind .=   '<div class="fl-wrap-row h-35 font-s-2 fw-b hide-val-defult-fit" 
                        data-sid="'.$sid.'" 
                        data-uid="'.$uid.'" 
                        data-coldate="'.$coldate.'" 
                        data-coltime="'.$coltime.'" 
                        data-name="'.$name_patient.'" 
                        data-passport="'.$passport.'" 
                        data-birthday="'.$date_of_birth.'" 
                        data-dateresult="'.$date_result.'" 
                        data-timeresult="'.$time_result.'">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Diagnosis
                        </div>
                    </div>
                    <div class="fl-wrap-row h-140 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="6" class="input-group" name="fittofly_diagnosis_edit"></textarea>
                        </div>
                        <div class="fl-fix w-200"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-35 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <input type="text" class="input-group" name="fittofly_result">
                        </div>
                        <div class="fl-fix w-200"></div>
                    </div>';

    echo $html_nation_head;
    include("country_inc_option.php");
    echo $html_nation_end.$html_bind;
?>
<div class="fl-wrap-row h-10"></div>
<div class="fl-wrap-row font-s-2 h-125" id="dlg_signature_main">
    <? include("doc_signature_main.php"); ?>
</div>

<div class="fl-wrap-row font-s-2 h-35">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-130 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-success" id="btPrintPdffittoflyCertificate" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
        <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
    </div>
    <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-danger" id="btCancelfittofly" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
    </div>
    <div class="fl-fix w-100 fl-mid-left">
        <button class="btn btn-primary" id="btPrintPdffittoflyCertificate_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>  
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $html_js; ?>

        // Button confrim
        $("#btPrintPdffittoflyCertificate").off("click");
        $("#btPrintPdffittoflyCertificate").on("click", function(){
            var uid_s = $(".hide-val-defult-fit").data("uid");
            var coldate_s = $(".hide-val-defult-fit").data("coldate");
            var coltime_s = $(".hide-val-defult-fit").data("coltime");
            var sid_s = $(".hide-val-defult-fit").data("sid");
            var name_s = $(".hide-val-defult-fit").data("name");
            var passport_s = $(".hide-val-defult-fit").data("passport");
            var birthday_s = $(".hide-val-defult-fit").data("birthday");

            var check_nation = $("[name=fittofly_nation_choice][value=THA]").is(":checked");
            if(check_nation == true){
                var nation_s = "Thailand";
            }
            else{
                var nation_s = $("[name=fittofly_nation] option:selected").text();
            }

            var date_reaule_s = $(".hide-val-defult-fit").data("dateresult");
            var time_reaule_s = $(".hide-val-defult-fit").data("timeresult");
            var diagnosis_s = $("[name=fittofly_diagnosis_edit]").val().replace(/\n/g, "\\n");
            var result_s = $("[name=fittofly_result]").val().replace(/\n/g, "\\n");
            
            var form_id = $("#medical_create_pdf");
            
            var aData = {
                doc_mode: "insert",
                uid: uid_s,
                coldate: coldate_s,
                coltime: coltime_s,
                name: name_s,
                passport: passport_s,
                birthday: birthday_s,
                nation: nation_s,
                date_result: date_reaule_s,
                time_result: time_reaule_s,
                diagnosis: diagnosis_s,
                result: result_s
            };
            // console.log(aData);

            $.ajax({url: "fittofly_certificate_pdf.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var date_now = String(result);
                    var date_now = date_now.split(",");
                    
                    saveFormData_document("MEDICAL_FITTOFLY", "ใบรับรองแพทย์ Fit-to-Fly and COVID-19", date_now[0], "ใบรับรองแพทย์ Fit-to-Fly and COVID-19", uid_s, coldate_s, coltime_s, sid_s, 1);
                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"MEDICAL_FITTOFLY_"+uid_s+"_"+coldate_con+""+coltime_con+".pdf";
                    
                    $("#btPrintPdffittoflyCertificate").next(".spinner").show();
                    $("#btPrintPdffittoflyCertificate").hide();

                    setTimeout(function(){
                        close_dlg(form_id);
                    }, 1000);
            }});
        });

        // Button cancel
        $("#btCancelfittofly").off("click");
        $("#btCancelfittofly").on("click", function(){
            $("#btPrintPdffittoflyCertificate_view").show();
            $(".hide-bt-pdf").hide();
        });

        // Button View
        $("#btPrintPdffittoflyCertificate_view").off("click");
        $("#btPrintPdffittoflyCertificate_view").on("click", function(){
            var uid_s = $(".hide-val-defult-fit").data("uid");
            var coldate_s = $(".hide-val-defult-fit").data("coldate");
            var coltime_s = $(".hide-val-defult-fit").data("coltime");
            var sid_s = $(".hide-val-defult-fit").data("sid");
            var name_s = $(".hide-val-defult-fit").data("name");
            var passport_s = $(".hide-val-defult-fit").data("passport");
            var birthday_s = $(".hide-val-defult-fit").data("birthday");

            var check_nation = $("[name=fittofly_nation_choice][value=THA]").is(":checked");
            if(check_nation == true){
                var nation_s = "Thailand";
            }
            else{
                var nation_s = $("[name=fittofly_nation] option:selected").text();
            }

            var date_reaule_s = $(".hide-val-defult-fit").data("dateresult");
            var time_reaule_s = $(".hide-val-defult-fit").data("timeresult");
            var diagnosis_s = $("[name=fittofly_diagnosis_edit]").val().replace(/\n/g, "\\n");
            var result_s = $("[name=fittofly_result]").val().replace(/\n/g, "\\n");
            
            var gen_url_view = "fittofly_certificate_pdf.php?doc_mode=view&uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&name="+name_s+"&passport="+passport_s+"&birthday="+birthday_s+"&nation="+nation_s+"&date_result="+date_reaule_s+"&time_result="+time_reaule_s+"&diagnosis="+diagnosis_s+"&result="+result_s;
            // console.log(gen_url_view);
            window.open(gen_url_view,'_blank');

            $("#btPrintPdffittoflyCertificate_view").hide();
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

            // $("#document_master_tempfile_invoice #document_new_tempfile").next(".spinner").hide();
            // $("#document_master_tempfile_invoice #document_new_tempfile").show();
        }
    }
</script>