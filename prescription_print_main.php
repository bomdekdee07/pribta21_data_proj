<?
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    if($sSID == ""){
        echo("Please login.");
        exit();
    }
    $sClinicID = getSS("clinic_id");
    $bill_id = getQS("billid");
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));
    $doc_type = getQS("doctype");
    $temp_file = "drug_prescription_pdf";
    // echo $sUid."/".$sColDate."/".$sColTime."/".$sSID."/".$sClinicID."/".$doc_type."/".$temp_file;

    $sJS = "";
    $sJS .= '$("#document_master_prescription [name=doc_note]").val("")';
?>

<div class="fl-fill smallfont2" id="document_master_prescription">
    <span id="data_defult" data-uid="<? echo $sUid; ?>" data-ss="<? echo $sSID; ?>" data-clinicid="<? echo $sClinicID; ?>" data-tempfile="<? echo $temp_file; ?>" data-doctype="<? echo $doc_type; ?>" data-billid="<? echo $bill_id; ?>" data-coldate="<? echo $sColDate; ?>" data-coltime="<? echo $sColTime; ?>"></span>
    <div class="fl-wrap-row holiday-mt-1">
        <div class='fl-fix' style='min-width:90px'></div>
        <div class='fl-fix' style='min-width:90px'>
            <b><span> หมายเหตุ </span></b>
        </div>
        <div class='fl-fix' style='min-width:350px'>
            <textarea name='doc_note' data-id ='doc_note' rows="3" data-require='' data-odata='' class='smallfont input-group' value=''>
            </textarea>
        </div>
    </div>
    <div class="fl-wrap-row holiday-mt-1">
    <div class='fl-fix' style='min-width:420px'></div>
        <button type="button" id="document_new_tempfile" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> สร้างเอกสาร</b></button>
        <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $sJS; ?>
        // สร้างเงื่อนไขปุ่ม new แต่ละคนครับ
        $("#document_master_prescription #document_new_tempfile").unbind("click");
        $("#document_master_prescription #document_new_tempfile").click(function(){
            $("#document_new_tempfile").next(".spinner").show();
            $("#document_new_tempfile").hide();

            var tempfile =  $("#document_master_prescription #data_defult").data("tempfile");
            var uid = $("#document_master_prescription #data_defult").data("uid");
            var coldate_s = $("#document_master_prescription #data_defult").data("coldate");
            var coltime_s = $("#document_master_prescription #data_defult").data("coltime");
            var doc_code = $("#document_master_prescription #data_defult").data("doctype");
            var sid = $("#document_master_prescription #data_defult").data("ss");
            var noet = $("#document_master_prescription [name=doc_note]").val();
            var bill_id_s = $("#document_master_prescription #data_defult").data("billid");
            
            var aData_stock = {
                uid: uid,
                coldate: coldate_s,
                coltime: coltime_s
            };
            $.ajax({
                url: "prescription_print_main_ajax.php",
                method: "POST",
                cache: false,
                data: aData_stock,
                success: function(result){
                    if(result == "1"){
                        var aData = {
                            uid: uid,
                            coldate: coldate_s,
                            coltime: coltime_s
                        };
                        // console.log(aData);

                        $.ajax({url: tempfile+".php", 
                            method: "POST",
                            cache: false,
                            data: aData,
                            success: function(result){
                                var date_now = String(result);
                                var date_now = date_now.split(",");
                                
                                saveFormData_document(doc_code, "ใบสั่งยา", date_now[0], noet, date_now[1], coldate_s, coltime_s, sid, 1);

                                var data_date_time_con = date_now[0].split(" ");
                                var coldate_con = data_date_time_con[0].split("-");
                                coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                                var coltime_con = data_date_time_con[1].split(":");
                                coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                                var gen_link = "pdfoutput/"+"DRUG_PRESC_"+uid+"_"+coldate_con+""+coltime_con+".pdf";
                                // console.log(gen_link);
                                window.open(gen_link,'_blank');
                        }});
                    }
                    else{
                        alert("ไม่มีข้อมูลสั่งยา");
                        $("#document_master_prescription #document_new_tempfile").next(".spinner").hide();
                        $("#document_master_prescription #document_new_tempfile").show();
                    }
                }
            });
        });
    });

    function saveFormData_document(doc_code, title, date_cre, note, uid, coldate, coltime, s_id, sataus){
        var aData = {
            app_mode: "document",
            doc_code: doc_code, 
            doc_datetime: date_cre,
            dataid: [{"doc_code":doc_code}, {"doc_title":title}, {"doc_datetime":date_cre}, {"doc_note":note}, {"uid":uid}, {"collect_date":coldate}, {"collect_time":coltime}, {"s_id":s_id}, {"doc_status":sataus}]
        };
        // console.log(aData);

        callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_document);
    }

    function saveFormDataComplete_document(flagSave, aData, rtnDataAjax){
        if(flagSave){
            $.notify("Save Data", "success");

            $("#document_master_prescription #document_new_tempfile").next(".spinner").hide();
            $("#document_master_prescription #document_new_tempfile").show();
        }
    }
</script>