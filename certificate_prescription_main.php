<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $gDoc_code = getQS("doctype");
    $sid = getSS("s_id");
    $clinic_id = getSS("clinic_id");
    $type_leg = getQS("type_leg", "TH");

    // create function age by dob
    function ageByDob($dob) { 
        $birthdate = new DateTime($dob); 
        $today   = new DateTime('today'); 
        $age = $birthdate->diff($today)->y; 

        return $age; 
    } 

    // query patient info
    $bind_param = "s";
    $array_val = array($uid);

    $query = "SELECT uid_info.fname,
        uid_info.sname,
        uid_info.en_fname,
        uid_info.en_sname,
        uid_info.date_of_birth,
        uid_info.uic
    from patient_info uid_info where uid = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_info_patient = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_info_patient["name"] = ($type_leg == "TH"? $row["fname"]." ".$row["sname"]: $row["en_fname"]." ".$row["en_sname"]);
            $data_info_patient["age"] = ageByDob($row["date_of_birth"]);
            $data_info_patient["uic"] = $row["uic"];
        }
    }
    $stmt->close();

    // query detail defult
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT	body_info.data_id,
        body_info.data_result 
    FROM
        p_data_result body_info
    WHERE
    body_info.uid = ?
    AND body_info.collect_date = ?
    AND body_info.collect_time = ?
    AND body_info.data_id IN ('cn_dx');";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_detail_defult = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_detail_defult[$row["data_id"]] = $row["data_result"];
        }
    }
    $stmt->close();

    // query defult lasted
    $bind_param = "s";
    $array_val = array($uid);

    $query = "SELECT	body_info.data_id,
        body_info.data_result 
    FROM
        p_data_result body_info
    WHERE
    body_info.uid = ?
    AND body_info.collect_time != '00:00:00'
    AND body_info.data_id IN ('drug_allergy_txt');";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_detail_defult_lasted = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_detail_defult_lasted[$row["data_id"]] = $row["data_result"];
        }
    }
    $stmt->close();
    $mysqli->close();

    $html_js = "";
    foreach($data_detail_defult as $key_id => $val){
        $html_js .= '$("#report_prescription_info [name='.$key_id.']").val('.json_encode($val).');';
    }
    foreach($data_detail_defult_lasted as $key_id => $val){
        $html_js .= '$("#report_prescription_info [name='.$key_id.']").val('.json_encode($val).');';
    }

    if($type_leg != "TH")
        $html_js .= '$("#report_prescription_info [name=type_leg]").val('.json_encode($type_leg).');';

    $html_prescription = "";
    $html_prescription .= ' <form action="prescription_certificate_pdf.php?doc_mode=view&name='.$data_info_patient["name"].'&uid='.$uid.'&age='.$data_info_patient["age"].'&uic='.$data_info_patient["uic"].'&coldate='.$coldate.'&coltime='.$coltime.'" method="post" target="_blank">
                                <div class="fl-wrap-col" id="report_prescription_info"
                                    data-uid="'.$uid.'"
                                    data-coldate="'.$coldate.'"
                                    data-coltime="'.$coltime.'"
                                    data-doctype="'.$gDoc_code.'"
                                    data-sid="'.$sid.'"
                                    data-uic="'.$data_info_patient["uic"].'"
                                    data-age="'.$data_info_patient["age"].'">
                                    <div class="fl-wrap-row h-20 font-s-2">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fix w-50 fw-b fl-mid-left">ภาษา:</div>
                                        <div class="fl-fix w-100 fw-b fl-mid-left">
                                            <select name="type_leg">
                                                <option value="TH">ไทย</option>
                                                <option value="EN">ENG</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-row h-5 data-leload-name" data-name="'.$data_info_patient["name"].'"></div>

                                    <div class="fl-wrap-row h-20 font-s-2">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fix w-30 fw-b fl-mid-left">ชื่อ:</div>
                                        <div class="fl-fix w-300 fl-mid-left">'.$data_info_patient["name"].'</div>
                                        <div class="fl-fix w-40 fw-b fl-mid-left">UID:</div>
                                        <div class="fl-fix w-100 fl-mid-left">'.$uid.'</div>
                                        <div class="fl-fix w-40 fw-b fl-mid-left">อายุ:</div>
                                        <div class="fl-fix w-100 fl-mid-left">'.$data_info_patient["age"].'</div>
                                    </div>
                                    <div class="fl-wrap-row h-5"></div>

                                    <div class="fl-wrap-row h-30">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fill fl-mid-left font-s-2 fw-b">Diagnosis:</div>
                                    </div>
                                    <div class="fl-wrap-row h-60">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fill fl-mid-left font-s-1">
                                            <textarea name="cn_dx" style="min-width: 90%; max-width: 90%; min-height: 59px; max-height: 59px;"></textarea>
                                        </div>
                                        <div class="fl-fix w-60"></div>
                                    </div>
                                    <div class="fl-wrap-row h-5"></div>

                                    <div class="fl-wrap-row h-30">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fill fl-mid-left font-s-2 fw-b">แพ้ยา:</div>
                                    </div>
                                    <div class="fl-wrap-row h-60">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fill fl-mid-left font-s-1">
                                            <textarea name="drug_allergy_txt" style="min-width: 90%; max-width: 90%; min-height: 59px; max-height: 59px;"></textarea>
                                        </div>
                                        <div class="fl-fix w-60"></div>
                                    </div>
                                    <div class="fl-wrap-row h-10"></div>

                                    <div class="fl-wrap-row h-20">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fill border-bt fl-mid-left fw-b font-s-2">รายการยา</div>
                                        <div class="fl-fix w-60"></div>
                                    </div>
                                    <div class="fl-wrap-row h-5"></div>
                                    <div class="fl-wrap-col prescription-row-add h-100 fl-auto">
                                        <div class="fl-wrap-row h-20">
                                            <div class="fl-fix w-60"></div>
                                            <div class="fl-fix w-30 fl-mid-left">No:</div>
                                            <div class="fl-fix w-40 fl-mid font-s-1">
                                                <input type="text" class="box-add-prescription-no" readonly value="1" name="no_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%; text-align:center; background-color: #D9D7D7;" />
                                            </div>
                                            <div class="fl-fix w-10"></div>
                                            <div class="fl-fix w-60 fl-mid-left">รายการ:</div>
                                            <div class="fl-fix w-450 fl-mid-left font-s-1">
                                                <input type="text" class="box-add-prescription-order"  name="order_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" />
                                            </div>
                                            <div class="fl-fix w-10"></div>
                                            <div class="fl-fix w-60 fl-mid-left">จำนวน:</div>
                                            <div class="fl-fix w-100 fl-mid font-s-1">
                                                <input type="number" class="box-add-prescription-total" name="total_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" />
                                            </div>
                                            <div class="fl-fix w-10"></div>
                                            <div class="fl-fix w-50 fl-mid-left">หน่วย:</div>
                                            <div class="fl-fix w-100 fl-mid font-s-1">
                                                <input type="text" class="box-add-prescription-unit" name="unit_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-row h-15"></div>
                                    <div class="fl-wrap-row h-20">
                                        <div class="fl-fix w-60"></div>
                                        <div class="fl-fix w-80 fl-mid-left border-t">
                                            <button id="bt_add_row_prescription" class="btn btn-warning font-s-1 fw-b" style="padding: 0px 5px 0px 5px;"><i class="fa fa-plus-square" aria-hidden="true"> Add Row</i></button>
                                        </div>
                                        <div class="fl-fix w-120 fl-mid-left border-t">
                                            <button id="delete_row_order_prescription" class="btn btn btn-danger font-s-1 fw-b" style="padding: 0px 5px 0px 5px;"><i class="fa fa-trash" aria-hidden="true"> Delete Row</i></button>
                                        </div>
                                        <div class="fl-fill border-t"></div>
                                        <div class="fl-fix w-60"></div>
                                    </div>
                                    <div class="fl-wrap-row h-15"></div>';
    echo $html_prescription;
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
        <button class="btn btn-primary" type="submit" id="btPrintPdfReferral_view" style="padding: 5px 25px 5px 25px;"><i class="fa fa-search-plus" aria-hidden="true"> View </i></button>
    </div>
</div>
</div>
</form>

<script>
    $(document).ready(function(){
        <?echo $html_js; ?>

        // Change leg
        $("#report_prescription_info [name=type_leg]").off("change");
        $("#report_prescription_info [name=type_leg]").on("change", function(){
            var sUid = $("#report_prescription_info").data("uid");
            var sColdate = $("#report_prescription_info").data("coldate");
            var sColtime = $("#report_prescription_info").data("coltime");
            var sDoctype = $("#report_prescription_info").data("doctype");
            var type_leg = $(this).val();
            var link_load_prescription = "certificate_prescription_main.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&doctype="+sDoctype+"&type_leg="+type_leg;
            // console.log(link_load_prescription);

            $("#report_prescription_info").load(link_load_prescription);
        });

        // BT add row
        $("#report_prescription_info #bt_add_row_prescription").off("click");
        $("#report_prescription_info #bt_add_row_prescription").on("click", function(ev){
            ev.preventDefault();
            var count_no = parseInt($('#report_prescription_info .box-add-prescription-no').length)+1;
            
            $("#report_prescription_info .prescription-row-add").append('<div class="fl-wrap-row h-20 main-row-delete row-delete-'+count_no+'"><div class="fl-fix w-60"></div><div class="fl-fix w-30 fl-mid-left">No:</div><div class="fl-fix w-40 fl-mid font-s-1"><input type="text" class="box-add-prescription-no" readonly value="'+count_no+'" name="no_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%; text-align:center; background-color: #D9D7D7;" /></div><div class="fl-fix w-10"></div><div class="fl-fix w-60 fl-mid-left">รายการ:</div><div class="fl-fix w-450 fl-mid-left font-s-1"><input type="text" class="box-add-prescription-order"  name="order_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" /></div><div class="fl-fix w-10"></div><div class="fl-fix w-60 fl-mid-left">จำนวน:</div><div class="fl-fix w-100 fl-mid font-s-1"><input type="number" class="box-add-prescription-total" name="total_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" /></div><div class="fl-fix w-10"></div><div class="fl-fix w-50 fl-mid-left">หน่วย:</div><div class="fl-fix w-100 fl-mid font-s-1"><input type="text" class="box-add-prescription-unit" name="unit_prescription[]" style="min-width: 100%; max-width: 100%; min-height: 99%; max-height: 99%;" /></div>');
        });

        // BT View
        $("#report_prescription_info #btPrintPdfReferral_view").off("click");
        $("#report_prescription_info #btPrintPdfReferral_view").on("click", function(){
            $(this).submit();

            $("#report_prescription_info #btPrintPdfReferral_view").hide();
            $("#report_prescription_info .hide-bt-pdf").show();
        });

        // BT Cancel
        $("#report_prescription_info #btCancelReferral").off("click");
        $("#report_prescription_info #btCancelReferral").on("click", function(ev){
            ev.preventDefault();
            $("#report_prescription_info #btPrintPdfReferral_view").show();
            $("#report_prescription_info .hide-bt-pdf").hide();
        });

        // BT Delete row
        $("#report_prescription_info #delete_row_order_prescription").off("click");
        $("#report_prescription_info #delete_row_order_prescription").on("click", function(ev){
            ev.preventDefault();
            var count_row_delete =  parseInt($("#report_prescription_info .main-row-delete").length)+1;

            $(".row-delete-"+count_row_delete).remove();
        });

        // Confirm BT
        $("#report_prescription_info #btPrintPdfReferral").off("click");
        $("#report_prescription_info #btPrintPdfReferral").on("click", function(ev){
            ev.preventDefault();
            var form_id = $("#report_prescription_info");

            var uid_s = $("#report_prescription_info").data("uid");
            var coldate_s = $("#report_prescription_info").data("coldate");
            var coltime_s = $("#report_prescription_info").data("coltime");
            var sid_s = $("#report_prescription_info").data("sid");
            var sDoctype = $("#report_prescription_info").data("doctype");
            var sType_leg = $("#report_prescription_info [name=type_leg]").val();
            var sUic = $("#report_prescription_info").data("uic");
            var sAge = $("#report_prescription_info").data("age");
            var sName = $("#report_prescription_info .data-leload-name").data("name");
            var sCn_dx = $("#report_prescription_info [name=cn_dx]").val();
            var sDrug_allergy_txt = $("#report_prescription_info [name=drug_allergy_txt]").val();

            var no_prescription_array = [];
            $("#report_prescription_info .box-add-prescription-no").each(function(x){
                no_prescription_array[x] = $(this).val();
            });


            var order_prescription_array = [];
            $("#report_prescription_info .box-add-prescription-order").each(function(x){
                order_prescription_array[x] = $(this).val();
            });

            var total_prescription_array = [];
            $("#report_prescription_info .box-add-prescription-total").each(function(x){
                total_prescription_array[x] = $(this).val();
            });

            var unit_prescription_array = [];
            $("#report_prescription_info .box-add-prescription-unit").each(function(x){
                unit_prescription_array[x] = $(this).val();
            });
            
            var aData = {
                doc_mode: "insert",
                uid: uid_s,
                coldate: coldate_s,
                coltime: coltime_s,
                type_leg: sType_leg,
                uic: sUic,
                age: sAge,
                name: sName,
                cn_dx: sCn_dx,
                drug_allergy_txt: sDrug_allergy_txt,
                no_prescription: no_prescription_array,
                order_prescription: order_prescription_array,
                total_prescription: total_prescription_array,
                unit_prescription: unit_prescription_array
            };            

            $.ajax({url: "prescription_certificate_pdf.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var date_now = String(result);
                    var date_now = date_now.split(",");
                    
                    saveFormData_document("MEDICAL_PRESCRIPTION", "ใบสั่งยา", date_now[0], "ใบสั่งยา", uid_s, coldate_s, coltime_s, sid_s, 1);
                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"MEDICAL_PRESCRIPTION"+uid_s+"_"+coldate_con+""+coltime_con+".pdf";
                    
                    $("#btPrintPdfReferral").next(".spinner").show();
                    $("#btPrintPdfReferral").hide();

                    setTimeout(function(){
                        close_dlg(form_id);
                    }, 1000);
            }});
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