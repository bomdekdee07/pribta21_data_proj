<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $req_id = getQS("uid");
    $sid = getSS("s_id");

    $js_bind = "";
    $js_bind = '$("#document_po_tempfile [name=req_id]").val("'.$req_id.'")';
?>

<div class="fl-wrap-col smallfont2" id="document_po_tempfile">
    <span id="data_defult" data-reqid="<? echo $req_id; ?>" data-sid="<? echo $sid; ?>"></span>
    <div class='fl-wrap-row holiday-mt-1 h-60'>
        <div class="fl-fix w-70"></div>
        <div class='fl-fix' style='min-width:90px'>
            <b><span> หมายเหตุ </span></b>
        </div>
        <div class='fl-fix' style='min-width:520px'>
            <textarea name='doc_note' data-id ='doc_note' rows='3' data-require='' data-odata='' class='smallfont input-group' value=''>
            </textarea>
        </div>
    </div>
    <div class="fl-wrap-row h-190">
        <div class="fl-fix w-70"></div>
        <div class="fl-wrap-col h-180 holiday-mt-1 border" style="min-width: 1000px; max-width: 1000px;">
            <div class="fl-wrap-row h-25 holiday-mt-1">
                <div class="fl-fix w-95 fl-mid-left fs-small fw-b holiday-ml-1">
                    <span>Request ID:</span>
                </div>
                <div class="fl-fix fs-small fl-mid-left w-100">
                    <input type="text" name="req_id" style="height: 24px;" disabled/>
                </div>
            </div>
            <div class="fl-wrap-row h-20">
                <div class="fl-fill fl-mid-right fs-small h-20">
                    <button type="button" id="new_j_bill_custom" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มที่อยู่</b></button>
                </div>
            </div>
            <div class="fl-wrap-row h-25">
                <div class="fl-fix border wper-10 fl-mid fs-small fw-b" style="background-color: #5DADE2;">
                    <span>Title</span>
                </div>
                <div class="fl-fix border wper-20 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                    <span class="holiday-ml-2">Name</span>
                </div>
                <div class="fl-fix border wper-15 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                    <span class="holiday-ml-2">Email</span>
                </div>
                <div class="fl-fill border fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                    <span class="holiday-ml-2">Address</span>
                </div>
                <div class="fl-fix border wper-25 fl-mid-left fs-small fw-b" style="background-color: #5DADE2;">
                    <span class="holiday-ml-2">Attention</span>
                </div>
            </div>
            <div id="bill_custom_sub" class="fl-wrap-col fl-auto h-115"></div>
        </div>
    </div>
    <div class="fl-wrap-row holiday-mt-1 h-35">
        <div class="fl-fix" style="min-width:70px"></div>
        <button type="button" id="document_new_tempfile" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> สร้างเอกสาร</b></button>
        <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $js_bind; ?>

        var reqId_s = $("#document_po_tempfile #data_defult").data("reqid");
        var a_data = {
            req_id: reqId_s
        };
        $.ajax({url: "po_template_ajax_billcustom.php", 
            method: "POST",
            cache: false,
            data: a_data,
            success: function(result){
                $("#document_po_tempfile #bill_custom_sub").children().remove();
                $("#document_po_tempfile #bill_custom_sub").append(result);
            }
        });

        $("#bill_custom_sub .click-row").off("click");
        $("#bill_custom_sub").on("click", ".click-row", function(){
            $(this).find('input[type=radio]').prop('checked', true);
        })

        $("#document_po_tempfile #document_new_tempfile").off("click");
        $("#document_po_tempfile #document_new_tempfile").click(function(){
            $("#document_new_tempfile").next(".spinner").show();
            $("#document_new_tempfile").hide();

            var tempfile = "po_template_pdf";
            var reqId_s = $("#document_po_tempfile #data_defult").data("reqid");
            var sid_s = $("#document_po_tempfile #data_defult").data("sid");
            var addrtitle_s = $("#bill_custom_sub [name=bill_title]").filter(":checked").val();
            var noet = $("#document_po_tempfile [name=doc_note]").val();
            var c_selected_defult = $("#bill_custom_sub [name=bill_title]").filter(":checked").length;

            var aData = {
                req_id: reqId_s,
                addrtitle: addrtitle_s
            };
            // console.log(aData);
            
            if(c_selected_defult > 0){
                $.ajax({url: tempfile+".php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        console.log(result);
                        var data_split = result.split(",");
                        saveFormData_document("B_PO", "ใบสั่งซื้อ", data_split[0], noet, reqId_s, "", "", sid_s, 1);

                        var data_date_time_con = data_split[0].split(" ");
                        var coldate_con = data_date_time_con[0].split("-");
                        coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                        var coltime_con = data_date_time_con[1].split(":");
                        coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                        var gen_link = "pdfoutput/"+"B_PO_"+reqId_s+"_"+coldate_con+""+coltime_con+".pdf";
                        // console.log(gen_link);
                        window.open(gen_link,'_blank');
                    }
                });
            }else{
                alert("กรุณาเลือกที่อยู่");
                $("#document_po_tempfile #document_new_tempfile").next(".spinner").hide();
                $("#document_po_tempfile #document_new_tempfile").show();
            }
        });

        $("#document_po_tempfile #new_j_bill_custom").off("click");
        $("#document_po_tempfile").on("click", "#new_j_bill_custom", function(){
            var reqId_s = $("#document_po_tempfile #data_defult").data("reqid");
            var url_po_create = "po_template_billcustom_create.php?req_id="+reqId_s+"&addrtitle=";

            showDialog(url_po_create, "Receipt management", "410", "350", "", function(sResult){    
                var url_gen_close = "po_template_ajax_billcustom.php?req_id="+reqId_s;

                $("#document_po_tempfile #bill_custom_sub").load(url_gen_close);
            }, false, function(sResult){});
        });

        $("#bill_custom_sub .edit-click").off("click");
        $("#bill_custom_sub").on("click", ".edit-click", function() {
            var reqId_s = $("#document_po_tempfile #data_defult").data("reqid");
            var addr_title_s = $(this).data("addrtitle");
            var url_receipt_create = "po_template_billcustom_create.php?req_id="+reqId_s+"&addrtitle="+addr_title_s;

            showDialog(url_receipt_create, "Receipt management", "410", "350", "", function(sResult){    
                var url_gen_close = "po_template_ajax_billcustom.php?req_id="+reqId_s;

                $("#bill_custom_sub").load(url_gen_close);
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

            $("#document_po_tempfile #document_new_tempfile").next(".spinner").hide();
            $("#document_po_tempfile #document_new_tempfile").show();
        }
    }
</script>