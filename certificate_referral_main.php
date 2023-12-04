<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uids = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $doc_code = getQS("doctype");
    $sid = getSS("s_id");

    function getAgeDetail_con($dob){
        $dob_a = explode("-", $dob);
        $today_a = explode("-", date("Y-m-d"));
        $dob_d = $dob_a[2];$dob_m = $dob_a[1];$dob_y = $dob_a[0];
        $today_d = $today_a[2];$today_m = $today_a[1];$today_y = $today_a[0];
        $years = $today_y - $dob_y;
        $months = $today_m - $dob_m;
        $days=$today_d - $dob_d;
        if ($today_m.$today_d < $dob_m.$dob_d) {
            $years--;
            $months = 12 + $today_m - $dob_m;
        }
    
        if ($today_d < $dob_d){
            $months--;
        }
    
        $firstMonths=array(1,3,5,7,8,10,12);
        $secondMonths=array(4,6,9,11);
        $thirdMonths=array(2);
    
        if($today_m - $dob_m == 1){
            if(in_array($dob_m, $firstMonths)){
                array_push($firstMonths, 0);
            }elseif(in_array($dob_m, $secondMonths)) {
                array_push($secondMonths, 0);
            }elseif(in_array($dob_m, $thirdMonths)){
                array_push($thirdMonths, 0);
            }
        }
    
        return $years;
    }

    function clean($string) {
        return preg_replace('/\r|\n/', '', $string); // Removes special chars.
    }

    $str_coldate_convert = "";
    $strMonthCut = Array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");

    $bind_param = "ss";
    $array_val = array($sid, $uids);

    $data_pateient_info = array();
    $data_detail = array();
    $query = "SELECT result.uid,
        pa_info.fname,
        pa_info.sname,
        pa_info.en_fname,
        pa_info.en_sname,
        pa_info.date_of_birth,
        result.collect_date,
        result.data_id,
        result.data_result,
        st.s_name,
        st.s_name_en
    from p_data_result result
    left join patient_info pa_info on(pa_info.uid = result.uid)
    left join p_staff st on(st.s_id = ?)
    where result.uid = ?
    and result.data_id in ('cn_dx', 'note_demo', 'cn_treatment')
    order by result.data_id, result.collect_date;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $str_illness = "";
    $old_data_id = "";
    $old_coldate = "";
    if($stmt->execute()){
        $stmt->bind_result($uid, $fname, $sname, $en_fname, $en_sname, $date_of_birth, $collect_date, $data_id, $data_result, $s_name, $s_name_en);
        while($stmt->fetch()){
            $data_pateient_info["uid"] = $uid;
            $data_pateient_info["name_th"] = $fname." ".$sname;
            $data_pateient_info["name_en"] = $en_fname." ".$en_sname;
            $data_pateient_info["age"] = getAgeDetail_con($date_of_birth);
            $data_pateient_info["st_name_th"] = $s_name;
            $data_pateient_info["st_name_en"] = $s_name_en;

            $data_detail[$data_id]["data_id"] = $data_id;

            if($old_data_id != $data_id){
                $str_illness = "";
                $old_coldate = "";
            }

            if($data_id == "note_demo"){
                if($old_coldate != $collect_date){
                    $str_coldate_convert = date("d", strtotime($collect_date))." ".$strMonthCut[intval(date("m", strtotime($collect_date)))-1]." ".substr((intval(date("Y", strtotime($collect_date)))+543), 2);
                    $str_illness .= $str_coldate_convert." ".$data_result.", ";
                }
            }
            else if($data_id == "cn_treatment"){
                if($old_coldate != $collect_date){
                    $str_coldate_convert = date("d", strtotime($collect_date))." ".$strMonthCut[intval(date("m", strtotime($collect_date)))-1]." ".substr((intval(date("Y", strtotime($collect_date)))+543), 2);
                    $str_illness .= $str_coldate_convert." ".$data_result.", ";
                }
            }
            else{
                $str_illness = $data_result;
            }

            $data_detail[$data_id]["result"] = $str_illness;

            $old_data_id = $data_id;
            $old_coldate = $collect_date;
        }
        // print_r($data_detail);
    }
    $stmt->close();

    $bind_param = "s";
    $array_val = array($uids);

    $data_lab = array();
    $query = "SELECT lab_order.collect_date,
        detail_test.lab_name,
        lab_result.lab_result_report
    from p_lab_order lab_order
    left join p_lab_order_lab_test lab_order_test on(lab_order_test.uid = lab_order.uid and lab_order_test.collect_date = lab_order.collect_date and lab_order_test.collect_time = lab_order.collect_time)
    left join p_lab_test detail_test on(detail_test.lab_id = lab_order_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = detail_test.lab_group_id)
    left join p_lab_method lab_method on(lab_method.lab_method_id = lab_group.lab_method_id)
    left join p_lab_result lab_result on(lab_result.uid = lab_order.uid and lab_result.collect_date = lab_order.collect_date and lab_result.collect_time = lab_order.collect_time and lab_result.lab_id = lab_order_test.lab_id)
    left join p_lab_test_result_hist lab_hist on(lab_hist.lab_id = lab_result.lab_id)
    left join patient_info pa_info on(pa_info.uid = lab_order.uid)
    where lab_order.uid = ?
    order by lab_order.collect_date, detail_test.lab_seq;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $old_coldate = "";
    $str_lab_result = "";
    $str_coldate_convert = "";
    $count_loop = 0;
    if($stmt->execute()){
        $stmt->bind_result($collect_date, $lab_name, $lab_result_report);
        while($stmt->fetch()){
            $data_lab[$collect_date]["coldate"] = $collect_date;

            if($old_coldate != $collect_date){
                $str_lab_result = "";
                $count_loop = 0;
            }
            
            if($count_loop == 0){
                $str_coldate_convert = date("d", strtotime($collect_date))." ".$strMonthCut[intval(date("m", strtotime($collect_date)))-1]." ".substr((intval(date("Y", strtotime($collect_date)))+543), 2);
                $str_lab_result .= $str_coldate_convert." ".$lab_name." ".$lab_result_report.", ";
            }
            else{
                $str_lab_result .= $lab_name." ".$lab_result_report.", ";
            }

            $count_loop++;

            $data_lab[$collect_date]["result"] = $str_lab_result;

            $old_coldate = $collect_date;
        }
        // print_r($data_lab);
    }
    $stmt->close();
    $mysqli->close();

    $html_js = "";
    $dx_str = "";
    $illness_str = "";
    $treatment_str = "";
    foreach($data_detail as $key_dataId => $val){
        if($key_dataId == "cn_dx"){
            $dx_str = clean($val["result"]);
            $html_js .= '$("[name=referral_diagnosis_edit]").val('.json_encode($dx_str).');';
        }
        else if($key_dataId == "note_demo"){
            $illness_str = clean($val["result"]);
            $html_js .= '$("[name=referral_illness_edit]").val('.json_encode($illness_str).');';
        }
        else if($key_dataId == "cn_treatment"){
            $treatment_str = clean($val["result"]);
            $html_js .= '$("[name=referral_treatment_edit]").val('.json_encode($treatment_str).');';
        }
    }

    $lab_str = "";
    foreach($data_lab as $key_coldate => $val2){
        $lab_str .= clean($val2["result"]);
    }
    $html_js .= '$("[name=referral_investigation_edit]").val('.json_encode($lab_str).');';
    
    $html_bind = "";
    $html_bind .=   '<form action="referral_certificate_pdf.php?doc_mode=view" method="post" target="_blank">
                    <div id="data_defalute" 
                        data-uid="'.$uids.'" 
                        data-coldate="'.$coldate.'"
                        data-coltime="'.$coltime.'"
                        data-naemth="'.$data_pateient_info["name_th"].'" 
                        data-nameen="'.$data_pateient_info["name_en"].'" 
                        data-age="'.$data_pateient_info["age"].'" 
                        data-stth="'.$data_pateient_info["st_name_th"].'" 
                        data-sten="'.$data_pateient_info["st_name_en"].'" 
                        data-coldate="'.$coldate.'" 
                        data-coltime="'.$coltime.'" 
                        data-sid="'.$sid.'">
                    <div class="fl-wrap-row h-30 font-s-2 fw-b">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fix w-80 fl-mid-left">
                            รูปแบบวันที่
                        </div>
                        <div class="fl-fill fl-mid-left">
                            <select id="format_date_referral" name="format_date_referral" style="width: 100px">
                                <option value="TH">Thai</option>
                                <option value="EN">English</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Diagnosis (การวินิจฉัยโรคขั้นต้น)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-80 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="3" class="input-group" name="referral_diagnosis_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Present illness (ประวัติการป่วยปัจจุบัน)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-140 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="6" class="input-group" name="referral_illness_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Investigation result (ผลการตรวจทางห้องปฏิบัติการ)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-80 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="3" class="input-group" name="referral_investigation_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Treatment given (การรักษาที่ให้ไว้แล้ว)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-120 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="5" class="input-group" name="referral_treatment_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Reason for referral (สาเหตุที่ส่งต่อผู้ป่วย)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-60 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="2" class="input-group" name="referral_reason_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    
                    <div class="fl-wrap-row h-25 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left fw-b">
                            Others (อื่นๆ)
                        </div>
                    </div>
                    <div class="fl-wrap-row h-60 font-s-2">
                        <div class="fl-fix w-60"></div>
                        <div class="fl-fill fl-mid-left">
                            <textarea rows="2" class="input-group" name="referral_other_edit"></textarea>
                        </div>
                        <div class="fl-fix w-50"></div>
                    </div>
                    <div class="fl-wrap-row font-s-2 h-10"></div>';

    echo $html_bind;
?>
<div class="fl-wrap-row font-s-2 h-125" id="dlg_signature_main">
    <? include("doc_signature_main.php"); ?>
</div>

<div class="fl-wrap-row font-s-2 h-35">
    <div class="fl-fix w-60"></div>
    <div class="fl-fix w-130 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-success" id="btPrintPdfReferral" style="padding: 5px 15px 5px 15px;">ยืนยันการบันทึก</button> <!--บน ขวา บน ซ้าย-->
        <i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
    </div>
    <div class="fl-fix w-80 fl-mid-left hide-bt-pdf" style="display:none;"> <!-- style="display:none;" -->
        <button class="btn btn-danger" id="btCancelReferral" style="padding: 5px 15px 5px 15px;">ยกเลิก</button> <!--บน ขวา บน ซ้าย-->
    </div>
    <div class="fl-fix w-100 fl-mid-left">
        <input type="hidden" name="doc_mode" value="view">
        <input type="hidden" name="uid" value="<? echo $uids; ?>">
        <input type="hidden" name="coldate" value="<? echo $coldate; ?>">
        <input type="hidden" name="coltime" value="<? echo $coltime; ?>">
        <input type="hidden" name="name_th" value="<? echo $data_pateient_info["name_th"]; ?>">
        <input type="hidden" name="name_en" value="<? echo $data_pateient_info["name_en"]; ?>">
        <input type="hidden" name="age" value="<? echo $data_pateient_info["age"]; ?>">
        <!-- <input type="hidden" name="dx" value="">
        <input type="hidden" name="illness" value="">
        <input type="hidden" name="lab_result" value="">
        <input type="hidden" name="treatment" value="">
        <input type="hidden" name="reason" value="">
        <input type="hidden" name="other" value="">
        <input type="hidden" name="leg_type" value=""> -->
        <input type="hidden" name="staffth" value="<? echo $data_pateient_info["st_name_th"]; ?>">
        <input type="hidden" name="staffen" value="<? echo $data_pateient_info["st_name_en"]; ?>">
        <button class="btn btn-primary" type="submit" id="btPrintPdfReferral_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>
    </div>
</div>
</form>

<script>
    $(document).ready(function(){
        <? echo $html_js; ?>

        // Confirm BT
        $("#btPrintPdfReferral").off("click");
        $("#btPrintPdfReferral").on("click", function(ev){
            ev.preventDefault();
            var uid_s = $("#data_defalute").data("uid");
            var coldate_s = $("#data_defalute").data("coldate");
            var coltime_s = $("#data_defalute").data("coltime");
            var naemth_s = $("#data_defalute").data("naemth");
            var nameen_s = $("#data_defalute").data("nameen");
            var age_s = $("#data_defalute").data("age");
            var dx_s = $("[name=referral_diagnosis_edit]").val().replace(/\n/g, "\\n");
            var illness_s = $("[name=referral_illness_edit]").val().replace(/\n/g, "\\n");
            var lab_s = $("[name=referral_investigation_edit]").val().replace(/\n/g, "\\n");
            var treatment_s = $("[name=referral_treatment_edit]").val().replace(/\n/g, "\\n");
            var reason_s = $("[name=referral_reason_edit]").val();
            var other_s = $("[name=referral_other_edit]").val();
            var leg_type_s = $("[name=format_date_referral]").val();
            var st_th_s = $("#data_defalute").data("stth");
            var st_en_s = $("#data_defalute").data("sten");
            var coldate_s = $("#data_defalute").data("coldate");
            var coltime_s = $("#data_defalute").data("coltime");
            var sid_s = $("#data_defalute").data("sid");

            var form_id = $("#medical_create_pdf");
            
            var aData = {
                doc_mode: "insert",
                uid: uid_s,
                coldate: coldate_s,
                coltime: coltime_s,
                name_th: naemth_s,
                name_en: nameen_s,
                age: age_s,
                referral_diagnosis_edit: dx_s,
                referral_illness_edit: illness_s,
                referral_investigation_edit: lab_s,
                referral_treatment_edit: treatment_s,
                referral_reason_edit: reason_s,
                referral_other_edit: other_s,
                format_date_referral: leg_type_s,
                staffth: st_th_s,
                staffen: st_en_s
            };            

            $.ajax({url: "referral_certificate_pdf.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var date_now = String(result);
                    var date_now = date_now.split(",");
                    
                    saveFormData_document("MEDICAL_REFERRAL", "ใบส่งตัว", date_now[0], "ใบส่งตัว", uid_s, coldate_s, coltime_s, sid_s, 1);
                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"MEDICAL_REFERRAL_"+uid_s+"_"+coldate_con+""+coltime_con+".pdf";
                    
                    $("#btPrintPdfReferral").next(".spinner").show();
                    $("#btPrintPdfReferral").hide();

                    setTimeout(function(){
                        close_dlg(form_id);
                    }, 1000);
            }});
        });

        // Button cancel
        $("#btCancelReferral").off("click");
        $("#btCancelReferral").on("click", function(ev){
            ev.preventDefault();
            $("#btPrintPdfReferral_view").show();
            $(".hide-bt-pdf").hide();
        });

        // Button View
        $("#btPrintPdfReferral_view").off("click");
        $("#btPrintPdfReferral_view").on("click", function(){

            $(this).submit();

            $("#btPrintPdfReferral_view").hide();
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

            // $("#document_new_tempfile").next(".spinner").hide();
            // $("#document_new_tempfile").show();
        }
    }
</script>