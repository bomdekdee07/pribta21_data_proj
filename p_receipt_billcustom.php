<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $bill_id = getQS("billid");
    $uid = getQS("uid");
    $uid_select = getQS("uid_select");
    $addrtitle = getQS("addrtitle");
    $type_leg = getQS("type_leg");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $sid = getSS("s_id");

    $data_count_sub_billno = "";
    if($bill_id != ""){
        $query_billid = "";
        $billid_convert = str_replace("/", "", $bill_id);
        $query_billid = "and uid like '".$billid_convert."%'";

        $query = "SELECT LPAD(count(*)+1, 2, '0') AS count_sub_billno
        from i_doc_list 
        where doc_code = 'RECEIPT_SUB' 
        ".$query_billid."
        and doc_status = '1';";

        $stmt = $mysqli->prepare($query);
        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $data_count_sub_billno = ($row["count_sub_billno"]==0? "01": $row["count_sub_billno"]);
            }
        }
        //echo $data_count_sub_billno;
    }

    $htmlBind_main = "";
    $htmlBind_main .=   '<div class="fl-wrap-col" id="bill_cash_custom" data-billid="'.$bill_id.'" data-uid="'.$uid.'" data-uidselect="'.$uid_select.'" data-addrtitle="'.$addrtitle.'" data-typeleg="'.$type_leg.'" data-coldate="'.$coldate.'" data-coltime="'.$coltime.'" data-sid="'.$sid.'">
                            <div class="fl-wrap-row h-30"></div>
                            <div class="fl-wrap-row h-25">
                                <div class="fl-fix w-40"></div>
                                <div class="fl-fill fl-mid-left font-s-2 fw-b">
                                    Bill ID: '.$bill_id.'
                                </div>
                            </div>

                            <div class="fl-wrap-row h-25">
                                <div class="fl-fix w-40"></div>
                                <div class="fl-fill fl-mid-left font-s-2 fw-b" id="sub_billid" data-subbillid="'.$data_count_sub_billno.'">
                                    Sub Bill ID: '.$data_count_sub_billno.'
                                </div>
                            </div>

                            <div class="fl-wrap-row h-10"></div>
                            <div class="fl-wrap-row h-25">
                                <div class="fl-fix w-40"></div>
                                <div class="fl-fill">
                                    <div class="fl-wrap-row h-25 border font-s-2 fw-b" style="background-color: #23CAB9; color: white">
                                        <div class="fl-fix w-20"></div>
                                        <div class="fl-fix w-450 fl-mid-left">
                                            รายการ
                                        </div>
                                        <div class="fl-fix w-100 fl-mid-left">
                                            ราคา
                                        </div>
                                        <div class="fl-fill"></div>
                                        <div class="fl-fix w-140 fl-mid-left">
                                            แสดงรายละเอียด:
                                        </div>
                                        <label>
                                            <div class="fl-fix ml-2 w-120 fl-mid-left">
                                                <input data-id="cn_bill_drug" name="cn_bill_drug" type="checkbox" value="1"/> <span> ยาและบริการ</span>
                                            </div>
                                        </label>
                                        <label>
                                            <div class="fl-fix ml-2 w-100 fl-mid-left">
                                                <input data-id="cn_bill_lab" name="cn_bill_lab" type="checkbox" value="1"/> <span> Lab</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="fl-fix w-40"></div>
                            </div>
                            <div class="fl-wrap-col fl-auto h-150" id="bill_custom_detail"></div>
                            <div class="fl-wrap-row h-30">
                                <div class="fl-fix w-40"></div>
                                <div class="fl-fill fl-mid-left border-t">
                                    <button id="btnBillCustom_view" class="btn btn-info font-s-2" style="padding: 1px 10px 1px 10px;"><i class="fa fa-eye" aria-hidden="true"> View</i></button><i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
                                    <button id="btnBillCustom_cfSave" class="btn btn-success font-s-2 btn-show" style="padding: 1px 10px 1px 10px; display: none;"><i class="fa fa-check-square" aria-hidden="true"> Confirm Save</i></button><i class="fa fa-spinner fa-spin spinner" aria-hidden="true" style="display: none;"></i>
                                    <button id="btnBillCustom_cancel" class="btn btn-danger font-s-2 holiday-ml-1 btn-show" style="padding: 1px 10px 1px 10px; display: none;"><i class="fa fa-times" aria-hidden="true"> Cancel Save</i></button>
                                </div>
                                <div class="fl-fix w-40"></div>
                            </div>
                        </div>';

    echo $htmlBind_main;
?>

<script>
    $(document).ready(function(){
        var s_billid = $("#bill_cash_custom").data("billid");
        var s_uid = $("#bill_cash_custom").data("uid");

        var aData = {
            billid: s_billid,
            uid: s_uid
        };
        $("#bill_custom_detail").load("p_receipt_billcustom_detail.php", aData);

        // BT CF Save
        $("#bill_cash_custom #btnBillCustom_cfSave").off("click");
        $("#bill_cash_custom #btnBillCustom_cfSave").on("click", function(){
            var str_group_code_s = "";
            var str_supply_code_s = "";
            var str_group_lab_s = "";
            var str_labid_s = "";
            
            $("input[type=checkbox].list-save").filter(":checked").each(function(){
                str_group_code_s += ($(this).data("groupcode") !== undefined? "'"+$(this).data("groupcode")+"'"+",": "");
                str_supply_code_s += ($(this).data("supplycode") !== undefined? "'"+$(this).data("supplycode")+"'"+",": "");

                str_group_lab_s += ($(this).data("groupcodelab") !== undefined? "'"+$(this).data("groupcodelab")+"'"+",": "");
                str_labid_s += ($(this).data("labid") !== undefined? "'"+$(this).data("labid")+"'"+",": "");
            })
            
            var bill_id_s = $("#bill_cash_custom").data("billid");
            var uid_s = $("#bill_cash_custom").data("uid");
            var uidselect_s = $("#bill_cash_custom").data("uidselect");
            var addrtitle_s = $("#bill_cash_custom").data("addrtitle");
            var typeleg_s = $("#bill_cash_custom").data("typeleg");
            var coldate_s = $("#bill_cash_custom").data("coldate");
            var coltime_s = $("#bill_cash_custom").data("coltime");
            var sid = $("#bill_cash_custom").data("sid");
            var cn_bill_drug_s = $("[name=cn_bill_drug]").filter(":checked").val();
            var cn_bill_lab_s = $("[name=cn_bill_lab]").filter(":checked").val();

            var sub_billid_s = $("#sub_billid").data("subbillid");
            
            var aData = {
                mode: "insert",
                mode_bill: "Y",
                billid: bill_id_s,
                uid: uid_s,
                uid_select: uidselect_s,
                addrtitle: addrtitle_s,
                type_leg: typeleg_s,
                bill_drug: cn_bill_drug_s,
                bill_lab: cn_bill_lab_s,
                str_group_code: str_group_code_s,
                str_supply_code: str_supply_code_s,
                str_labid: str_labid_s,
                sub_billid: sub_billid_s
            };

            var tempfile = "cashier_print_pdf";
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
                    
                    saveFormData_document("RECEIPT_SUB", "ใบเสร็จย่อยใบที่ "+sub_billid_s, date_now[0], "ใบเสร็จย่อยใบที่ "+sub_billid_s, billid[1], coldate_s, coltime_s, sid, 1);

                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    $("#bill_cash_custom #btnBillCustom_cfSave").next(".spinner").show();
                    $("#bill_cash_custom #btnBillCustom_cfSave").hide();

                    var data_date_time_con = date_now[0].split(" ");
                    var coldate_con = data_date_time_con[0].split("-");
                    coldate_con = coldate_con[0]+coldate_con[1]+coldate_con[2];

                    var coltime_con = data_date_time_con[1].split(":");
                    coltime_con = coltime_con[0]+coltime_con[1]+coltime_con[2];

                    var gen_link = "pdfoutput/"+"RECEIPT_SUB_"+billid[1]+"_"+coldate_con+""+coltime_con+".pdf";
                    window.open(gen_link,'_blank');

                    var form_id =$("#bill_cash_custom");
                    setTimeout(function(){
                        close_dlg(form_id);
                    }, 1000);
            }});
        });
        
        // BT View
        $("#bill_cash_custom #btnBillCustom_view").off("click");
        $("#bill_cash_custom #btnBillCustom_view").on("click", function(){
            $("#bill_cash_custom .btn-show").show();
            $(this).hide();
            var str_group_code_s = "";
            var str_supply_code_s = "";
            var str_group_lab_s = "";
            var str_labid_s = "";
            
            $("input[type=checkbox].list-save").filter(":checked").each(function(){
                str_group_code_s += ($(this).data("groupcode") !== undefined? "'"+$(this).data("groupcode")+"'"+",": "");
                str_supply_code_s += ($(this).data("supplycode") !== undefined? "'"+$(this).data("supplycode")+"'"+",": "");

                str_group_lab_s += ($(this).data("groupcodelab") !== undefined? "'"+$(this).data("groupcodelab")+"'"+",": "");
                str_labid_s += ($(this).data("labid") !== undefined? "'"+$(this).data("labid")+"'"+",": "");
            })
            
            var bill_id_s = $("#bill_cash_custom").data("billid");
            var uid_s = $("#bill_cash_custom").data("uid");
            var uidselect_s = $("#bill_cash_custom").data("uidselect");
            var addrtitle_s = $("#bill_cash_custom").data("addrtitle");
            var typeleg_s = $("#bill_cash_custom").data("typeleg");
            var cn_bill_drug_s = $("[name=cn_bill_drug]").filter(":checked").val();
            var cn_bill_lab_s = $("[name=cn_bill_lab]").filter(":checked").val();

            var gen_url_view = "cashier_print_pdf.php?mode=view&mode_bill=Y&billid="+bill_id_s+"&uid="+uid_s+"&uid_select="+uidselect_s+"&addrtitle="+addrtitle_s+"&type_leg="+typeleg_s+"&bill_drug="+cn_bill_drug_s+"&bill_lab="+cn_bill_lab_s+"&str_group_code="+str_group_code_s+"&str_supply_code="+str_supply_code_s+"&str_labid="+str_labid_s;
            window.open(gen_url_view,'_blank');
        });

        // BT Cancel Save
        $("#bill_cash_custom #btnBillCustom_cancel").off("click");
        $("#bill_cash_custom #btnBillCustom_cancel").on("click", function(){
            $("#bill_cash_custom .btn-show").hide();
            $("#bill_cash_custom #btnBillCustom_view").show();
            
            $("#bill_cash_custom #btnBillCustom_cfSave").next(".spinner").hide();
            $("#bill_cash_custom #btnBillCustom_cfSave").hide();
        });

        $("[name=cn_bill_drug]").off("click");
        $("[name=cn_bill_drug]").on("click", function(){
            var s_billid = $("#bill_cash_custom").data("billid");
            var s_uid = $("#bill_cash_custom").data("uid");
            var bill_drug_s = $("#bill_cash_custom [name=cn_bill_drug]").filter(":checked").val();
            var bill_lab_s = $("#bill_cash_custom [name=cn_bill_lab]").filter(":checked").val();

            var aData = {
                billid: s_billid,
                uid: s_uid,
                bill_drug: bill_drug_s,
                bill_lab: bill_lab_s
            };

            $("#bill_custom_detail").load("p_receipt_billcustom_detail.php", aData);
        });

        $("[name=cn_bill_lab]").off("click");
        $("[name=cn_bill_lab]").on("click", function(){
            var s_billid = $("#bill_cash_custom").data("billid");
            var s_uid = $("#bill_cash_custom").data("uid");
            var bill_lab_s = $("#bill_cash_custom [name=cn_bill_lab]").filter(":checked").val();
            var bill_drug_s = $("#bill_cash_custom [name=cn_bill_drug]").filter(":checked").val();

            var aData = {
                billid: s_billid,
                uid: s_uid,
                bill_drug: bill_drug_s,
                bill_lab: bill_lab_s
            };

            $("#bill_custom_detail").load("p_receipt_billcustom_detail.php", aData);
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

            $("#bill_cash_custom #btnBillCustom_cfSave").next(".spinner").hide();
            $("#bill_cash_custom #btnBillCustom_cfSave").show();
        }
    }

    function close_dlg(formid){
        var objthis = formid;
        closeDlg(objthis, "0");
    }
</script>