<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    if($sSID == ""){
        echo("Please login.");
        exit();
    }
    $sClinicID = getSS("clinic_id");
    $uid = getQS("uid");
    $bill_id = getQS("billid");
    $sColDate = getQS("coldate");
    $sColTime = getQS("coltime");
    $temp_file = "invoice_print_pdf";
    $doc_type = getQS("doctype");

    $data_uid = array();
    $coldate_s = "";
    $coltime_s = "";
    $bill_id_query = substr($bill_id, 0, 4)."/".substr($bill_id, 4);
    $query = "select queue_l.uid as uid_bill,
        queue_l.collect_date,
        queue_l.collect_time
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    where bill_d.bill_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id_query);

    if($stmt -> execute()){
        $stmt -> bind_result($uid_bill, $collect_date, $collect_time);
        while($stmt -> fetch()){
            $data_uid[$uid_bill] = $uid_bill;
            if($uid == $uid_bill){
                $coldate_s = $collect_date;
                $coltime_s = $collect_time;
            }
        }
        // print_r($data_uid);
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    $sJS .= '$("#document_master_tempfile_invoice [name=doc_note]").val("");';
?>

<div class="fl-fill smallfont2" id="document_master_tempfile_invoice">
    <span id="data_defult" data-uid="<? echo $uid; ?>" data-ss="<? echo $sSID; ?>" data-clinicid="<? echo $sClinicID; ?>" data-tempfile="<? echo $temp_file; ?>" data-doctype="<? echo $doc_type; ?>" data-coldate="<? echo $sColDate; ?>" data-coltime="<? echo $sColTime; ?>" data-namedoc="Invoice" data-billid="<? echo $bill_id;?>" data-coldate_s="<? echo $coldate_s; ?>" data-coltime_s="<? echo $coltime_s; ?>"></span>
    <div class="fl-wrap-row holiday-mt-3">
        <div class='fl-fix w-30'></div>
        <div class="fl-fix w-150">
            <label><input type="radio" data-id="opt_mode" name="opt_mode" value="1" class="v_radio_master" checked> แสดงรายละเอียด</label>
        </div>
        <div class="fl-fix w-150">
            <label><input type="radio" data-id="opt_mode2" name="opt_mode" value="2" class="v_radio_master"> ซ่อนรายละเอียด</label>
        </div>
    </div>
    <div class="fl-wrap-row holiday-mt-1">
        <div class='fl-fix w-30'></div>
        <div class='fl-fix w-70'>
            <b><span> หมายเหตุ </span></b>
        </div>
        <div class='fl-fix' style='min-width:450px'>
            <textarea name='doc_note' data-id ='doc_note' rows="3" data-require='' data-odata='' class='smallfont input-group' value=''>
            </textarea>
        </div>
    </div>

    <div class="fl-wrap-col h-180 holiday-mt-1 border holiday-ml-2" style="min-width: 1000px; max-width: 1000px;">
        <div class="fl-wrap-row h-15 holiday-mt-1">
            <div class="fl-fix w-30 fl-mid-left fs-small fw-b holiday-ml-1">
                <span>UID:</span>
            </div>
        </div>
        <div class="fl-wrap-row h-20">
            <div class="fl-fix w-160 fl-mid-left fs-small holiday-ml-1">
                <select id="uid" style="width: 160px;">
                    <option value="">Please Select</option>
                    <? $_GET["billid"] = $bill_id; $_GET["dataid"] = "uid"; $_GET["uid"] = $uid; include("invoice_print_opt_uid.php"); ?>
                </select>
            </div>
            <div class="fl-fill fl-mid-right fs-small h-20">
                <button type="button" id="new_j_bill_custom" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มที่อยู่</b></button>
            </div>
        </div>
        <div class="fl-wrap-row h-25">
            <div class="fl-fix border wper-20 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                <span class="holiday-ml-7">Bill title</span>
            </div>
            <div class="fl-fix border wper-25 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                <span class="holiday-ml-2">Bill name</span>
            </div>
            <div class="fl-fix border wper-15 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                <span class="holiday-ml-2">Bill tax</span>
            </div>
            <div class="fl-fill border fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                <span class="holiday-ml-2">Bill address</span>
            </div>
        </div>
        <div id="bill_custom_sub" class="fl-wrap-col fl-auto h-115"></div>
    </div>

    <div class="fl-wrap-row holiday-mt-1">
    <div class='fl-fix' style='min-width:420px'></div>
        <button type="button" id="document_new_tempfile" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> สร้างเอกสาร</b></button>
        <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#document_master_tempfile_invoice [name=opt_mode]").checkboxradio();

        $("#document_master_tempfile_invoice #uid").off("change");
        $("#document_master_tempfile_invoice #uid").on("change", function(){
            var uid_s = $(this).val();
            
            var a_data = {
                uid: uid_s
            };

            $.ajax({url: "p_receipt_ajax_billcustom.php", 
                method: "POST",
                cache: false,
                data: a_data,
                success: function(result){
                    $("#document_master_tempfile_invoice #bill_custom_sub").children().remove();
                    $("#document_master_tempfile_invoice #bill_custom_sub").append(result);
                }
            });
        });

        <? echo $sJS; ?>
        $('#document_master_tempfile_invoice #uid').trigger('change');

        // สร้างเงื่อนไขปุ่ม new แต่ละคนครับ
        $("#document_master_tempfile_invoice #document_new_tempfile").unbind("click");
        $("#document_master_tempfile_invoice #document_new_tempfile").click(function(){
            $("#document_new_tempfile").next(".spinner").show();
            $("#document_new_tempfile").hide();

            var tempfile =  $("#document_master_tempfile_invoice #data_defult").data("tempfile");
            var uid = $("#document_master_tempfile_invoice #data_defult").data("uid");
            var coldate = $("#document_master_tempfile_invoice #data_defult").data("coldate");
            var coltime = $("#document_master_tempfile_invoice #data_defult").data("coltime");
            var doc_code = $("#document_master_tempfile_invoice #data_defult").data("doctype");
            var name_doc = $("#document_master_tempfile_invoice #data_defult").data("namedoc");
            var sid = $("#document_master_tempfile_invoice #data_defult").data("ss");
            var noet = $("#document_master_tempfile_invoice [name=doc_note]").val();
            var bill_id_s = $("#document_master_tempfile_invoice #data_defult").data("billid");
            var opt_mode_s = $("#document_master_tempfile_invoice [name=opt_mode]:checked").val();
            var addrtitle_s = $("#ajax_bill_custom [name=bill_title]").filter(":checked").val();

            var aData = {
                uid: uid,
                coldate: coldate,
                coltime: coltime,
                doc_code: doc_code,
                billid: bill_id_s,
                opt_mode: opt_mode_s,
                addrtitle: addrtitle_s
            };
            // console.log(aData);
            
            
            $.ajax({url: tempfile+".php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var date_now = String(result);
                    var date_now = date_now.split(",");
                    
                    saveFormData_document(doc_code, name_doc, date_now[0], noet, date_now[1], coldate, coltime, sid, 1);

                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"B_INVOICE_"+bill_id_s+"_"+coldate_con+""+coltime_con+".pdf";
                    // console.log(gen_link);
                    window.open(gen_link,'_blank');
            }});
        });

        $("#document_master_tempfile_invoice #new_j_bill_custom").off("click");
        $("#document_master_tempfile_invoice #new_j_bill_custom").on("click", function(){
            var uid_s = $("#document_master_tempfile_invoice #uid").val();
            var url_receipt_create = "p_receipt_create.php?uid="+uid_s+"&addrtitle=";

            showDialog(url_receipt_create, "Receipt management", "360", "350", "", function(sResult){    
                var url_gen_close = "p_receipt_ajax_billcustom.php?uid="+uid_s;
                console.log("INNN");

                $("#document_master_tempfile_invoice #bill_custom_sub").load(url_gen_close);
            }, false, function(sResult){});
        });
    });

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