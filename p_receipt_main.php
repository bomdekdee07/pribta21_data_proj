<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    if($sSID == ""){
        echo("Please login.");
        exit();
    }
    $bill_id = isset($_POST["billid"])?$_POST["billid"]: getQS("billid");
    $bill_id = substr($bill_id, 0, 4)."/".substr($bill_id, 4);
    $uid = getQS("uid");
    $addr_title = getQS("addrtitle");

    $temp_file = "cashier_print_pdf";

    // Query check botton custom bill
    $bind_param = "s";
    $array_val = array(str_replace("/", "", $bill_id));
    $data_check_botton_custom = 0;

    $query = "SELECT count(*) AS count_check_main 
    from i_doc_list 
    where doc_code = 'RECEIPT' 
    and uid = ?
    and doc_status = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_check_botton_custom = $row["count_check_main"];
        }
        // echo $data_check_botton_custom;
    }
    $stmt->close();
    
    $disabled_botton_custom_bill = "";
    if($data_check_botton_custom < 1){
        $disabled_botton_custom_bill = "disabled title='กรุณาสร้าง Bill ทุก order ก่อนครับ' ";
    }

    $data_uid = array();
    $data_bill_detail = array();
    $coldate_s = "";
    $coltime_s = "";
    $query = "SELECT queue_l.uid as uid_bill,
        queue_l.collect_date,
        queue_l.collect_time,
        bill_drug.data_result AS bill_drug,
        bill_lab.data_result AS bill_lab
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join p_data_result bill_drug on(bill_drug.uid = queue_l.uid and bill_drug.collect_date = queue_l.collect_date and bill_drug.collect_time = queue_l.collect_time and bill_drug.data_id = 'cn_bill_drug')
    left join p_data_result bill_lab on(bill_lab.uid = queue_l.uid and bill_lab.collect_date = queue_l.collect_date and bill_lab.collect_time = queue_l.collect_time and bill_lab.data_id = 'cn_bill_lab')
    where bill_d.bill_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt -> execute()){
        $stmt -> bind_result($uid_bill, $collect_date, $collect_time, $bill_drug, $bill_lab);
        while($stmt -> fetch()){
            $data_uid[$uid_bill] = $uid_bill;
            if($uid == $uid_bill){
                $coldate_s = $collect_date;
                $coltime_s = $collect_time;
            }
            $data_bill_detail["cn_bill_drug"] = $bill_drug != ""? $bill_drug:"0";
            $data_bill_detail["cn_bill_lab"] = $bill_lab != ""? $bill_lab:"0";
        }
        // print_r($data_bill_detail);
    }
    $stmt->close();
    $mysqli->close();

    $html_js_bind = "";
    foreach($data_bill_detail as $key => $val){
        $html_js_bind .= '$("#document_master_tempfile [name='.$key.']").filter("[value='.$val.']").attr("checked", true);';
    }

    $sJS_receipt = "";
    $sJS_receipt_val = "";
    
    $sJS_receipt .=     '<div class="fl-wrap-col smallfont2" id="document_master_tempfile">';
    $sJS_receipt .=         '<span id="data_defult" data-uid="'.$uid.'" data-ss="'.$sSID.'" data-tempfile="'.$temp_file.'" data-billid="'.$bill_id.'" data-addrtitle="'.$addr_title.'" data-coldate="'.$coldate_s.'" data-coltime="'.$coltime_s.'"></span>';
    $sJS_receipt .=         "<div class='fl-wrap-row holiday-mt-1 h-60'>
                                <div class='fl-fix holiday-ml-7' style='min-width:90px'>
                                    <b><span> หมายเหตุ </span></b>
                                </div>
                                <div class='fl-fix' style='min-width:520px'>
                                    <textarea name='doc_note' data-id ='doc_note' rows='3' data-require='' data-odata='' class='smallfont input-group' value=''>
                                    </textarea>
                                </div>
                                <div class='fl-fix w-280'></div>
                                <div class='fl-fix h-60'>
                                    <div class='fl-wrap-row h-35'>
                                        <button type='button' id='bill_custom_order' class='btn smallfont1 holiday-billcustom-btn' style='padding-top:1px; padding-bottom: 1px;' ".$disabled_botton_custom_bill."><b><i class='fa fa-cog' aria-hidden='true'></i> Custom bill order</b></button>
                                    </div>
                                    <div class='fl-wrap-row h-15'>
                                        <div class='fl-fill fl-mid font-s-1' style='color: red'>
                                            *Create Bill all order*
                                        </div>
                                    </div>
                                </div>
                            </div>";
    $sJS_receipt .=         '<div class="fl-wrap-col h-180 holiday-mt-1 border holiday-ml-8" style="min-width: 1000px; max-width: 1000px;">
                                <div class="fl-wrap-row h-15 holiday-mt-1">
                                    <div class="fl-fix w-185 fl-mid-left fs-small fw-b holiday-ml-1">
                                        <span>UID:</span>
                                    </div>
                                    <div class="fl-fix w-185 fl-mid-left fs-small fw-b holiday-ml-1">
                                        <span>Language:</span>
                                    </div>
                                    <div class="fl-fix w-150 fl-mid-left fs-small fw-b holiday-ml-1">
                                        <span>แสดงรายละเอียด:</span>
                                    </div>
                                </div>
                                <div class="fl-wrap-row h-20">
                                    <div class="fl-fix w-185 fl-mid-left fs-small holiday-ml-1">
                                        <select id="uid" style="width: 160px;">
                                            <option value="P00-00000">All Address</option>';
    foreach($data_uid as $key => $val){
        $sJS_receipt .=                     '<option value="'.$val.'">'.$val.'</option>';

        if($uid == $val){
            $sJS_receipt_val .= '$("#document_master_tempfile [name=doc_note]").val("");';
            $sJS_receipt_val .= '$("#document_master_tempfile #uid").val("'.$uid.'");';
        }
    }
    $sJS_receipt .=                     '</select>
                                    </div>
                                    <div class="fl-fix w-185 fl-mid-left fs-small holiday-ml-1">
                                        <select id="type_leg" style="width: 165px;">
                                            <option value="TH">Thai</option>
                                            <option value="EN">English</option>
                                        </select>
                                    </div>
                                    <div class="fl-fix ml-2 font-s-1 w-35 fl-mid-left fw-b">
                                        <input data-id="cn_bill_drug" name="cn_bill_drug" type="checkbox" value="1"/> <span>ยา</span>
                                    </div>
                                    <div class="fl-fix ml-2 font-s-1 w-100 fl-mid-left fw-b">
                                        <input data-id="cn_bill_lab" name="cn_bill_lab" type="checkbox" value="1"/> <span>Lab</span>
                                    </div>
                                    <div class="fl-fill fl-mid-right fs-small h-20">
                                        <button type="button" id="new_j_bill_custom" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มที่อยู่</b></button>
                                    </div>
                                </div>';
    $sJS_receipt .=             '<div class="fl-wrap-row h-25">
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
                                </div>';
    $sJS_receipt .=             '<div id="bill_custom_sub" class="fl-wrap-col fl-auto h-115"></div>';
    $sJS_receipt .=         '</div>';
    $sJS_receipt .=         '<div class="fl-wrap-row holiday-mt-1 h-35">
                                <div class="fl-fix" style="min-width:985px"></div>
                                <button type="button" id="document_new_tempfile" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> สร้างเอกสาร</b></button>
                                <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
                            </div>';
    $sJS_receipt .=     '</div>';

    echo $sJS_receipt;
?>

<script>
    $(document).ready(function(){
        // Defult checkbox bill detail
        <? echo $html_js_bind; ?>

        //Botton custom bill order
        $("#bill_custom_order").off("click");
        $("#bill_custom_order").on("click", function(){
            var bill_id_s = $("#document_master_tempfile #data_defult").data("billid");
            var uid_s = $("#document_master_tempfile #data_defult").data("uid");
            var uid_select_s = $("#document_master_tempfile #uid").val();
            var addrtitle_s = $("#ajax_bill_custom [name=bill_title]").filter(":checked").val();
            var leg_s = $("#document_master_tempfile #type_leg").val();
            var coldate_s = $("#document_master_tempfile #data_defult").data("coldate");
            var coltime_s = $("#document_master_tempfile #data_defult").data("coltime");

            var url_bill_custom = "p_receipt_billcustom.php?billid="+bill_id_s+"&uid="+uid_s+"&uid_select="+uid_select_s+"&addrtitle="+addrtitle_s+"&type_leg="+leg_s+"&coldate="+coldate_s+"&coltime="+coltime_s;
            var form_id = $("#document_master_tempfile");

            showDialog(url_bill_custom, "Receipt management", "50%", "80%", "", function(sResult){}, false, function(sResult){});
        })

        // สร้างเงื่อนไขปุ่ม new แต่ละคนครับ
        $("#document_master_tempfile #document_new_tempfile").off("click");
        $("#document_master_tempfile #document_new_tempfile").click(function(){
            $("#document_new_tempfile").next(".spinner").show();
            $("#document_new_tempfile").hide();

            var tempfile =  $("#document_master_tempfile #data_defult").data("tempfile");
            var billid_s = $("#document_master_tempfile #data_defult").data("billid");
            var uid_s = $("#document_master_tempfile #data_defult").data("uid");
            var uid_select_s = $("#document_master_tempfile #uid").val();
            var addrtitle_s = $("#ajax_bill_custom [name=bill_title]").filter(":checked").val();
            var sid = $("#document_master_tempfile #data_defult").data("ss");
            var noet = $("#document_master_tempfile [name=doc_note]").val();
            var coldate_s = $("#document_master_tempfile #data_defult").data("coldate");
            var coltime_s = $("#document_master_tempfile #data_defult").data("coltime");
            var c_selected_defult = $("#ajax_bill_custom [name=bill_title]").filter(":checked").length;
            var leg_s = $("#document_master_tempfile #type_leg").val();
            var bill_drug_s = $("#document_master_tempfile [name=cn_bill_drug]").filter(":checked").val();
            var bill_lab_s = $("#document_master_tempfile [name=cn_bill_lab]").filter(":checked").val();

            var aData = {
                mode: "insert",
                billid: billid_s,
                uid: uid_s,
                uid_select: uid_select_s,
                addrtitle: addrtitle_s,
                type_leg: leg_s,
                bill_drug: bill_drug_s,
                bill_lab: bill_lab_s
            };
            // console.log(aData);
            
            if(c_selected_defult > 0){
                $.ajax({url: tempfile+".php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        console.log(result);
                        var date_now = result.split(",");
                        var billid = result.split(",");
                        var coldate = result.split(",");
                        var coltime = result.split(",");
                        
                        saveFormData_document("RECEIPT", "ใบเสร็จ", date_now[0], noet, billid[1], coldate_s, coltime_s, sid, 1);

                        var constr_billID = billid_s.replace("/", "");
                        var genlink_all = "p_receipt_main.php?uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&doctype=RECEIPT&temp_file=p_receipt&billid="+constr_billID;
                        // console.log(genlink_all);
                        $("#document_master_tempfile").load(genlink_all);

                        var data_date_time_con = date_now[0].split(" ");
                        var coldate_con = data_date_time_con[0].split("-");
                        coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                        var coltime_con = data_date_time_con[1].split(":");
                        coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                        var gen_link = "pdfoutput/"+"RECEIPT_"+billid[1]+"_"+coldate_con+""+coltime_con+".pdf";
                        // console.log(gen_link);
                        window.open(gen_link,'_blank');
                }});
            }else{
                alert("กรุณาเลือกที่อยู่");
                $("#document_new_tempfile").next(".spinner").hide();
                $("#document_new_tempfile").show();
            }
        });

        $("#document_master_tempfile #uid").off("change");
        $("#document_master_tempfile #uid").on("change", function(){
            var uid_s = $(this).val();
            
            var a_data = {
                uid: uid_s
            };

            $.ajax({url: "p_receipt_ajax_billcustom.php", 
                method: "POST",
                cache: false,
                data: a_data,
                success: function(result){
                    $("#document_master_tempfile #bill_custom_sub").children().remove();
                    $("#document_master_tempfile #bill_custom_sub").append(result);
                }
            });
        });

        <? echo $sJS_receipt_val; ?>
        $('#document_master_tempfile #uid').trigger('change');

        $("#document_master_tempfile #new_j_bill_custom").off("click");
        $("#document_master_tempfile #new_j_bill_custom").on("click", function(){
            var uid_s = $("#document_master_tempfile #uid").val();
            var url_receipt_create = "p_receipt_create.php?uid="+uid_s+"&addrtitle=";

            showDialog(url_receipt_create, "Receipt management", "360", "350", "", function(sResult){    
                var url_gen_close = "p_receipt_ajax_billcustom.php?uid="+uid_s;

                $("#document_master_tempfile #bill_custom_sub").load(url_gen_close);
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

            $("#document_new_tempfile").next(".spinner").hide();
            $("#document_new_tempfile").show();
        }
    }

    function close_dlg(formid){
        var objthis = formid;
        closeDlg(objthis, "0");
    }
</script>