<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    $req_id = getQS("req_id");
    // echo $req_id;

    $data_item_req = array();
    $query = "select request_id,
    request_title,
    request_datetime,
    request_status,
    case 
        when request_status = '00' then 'รอยื่นคำขอ'
        when request_status = '01' then 'รอการยืนยัน'
        when request_status = 'CF' then 'ยืนยันรอสินค้าเข้า'
        when request_status = 'FIN' then 'เสร็จสิ้น'
        when request_status = 'CC' then 'ยกเลิก'
        end status_con,
    section_id,
    request_detail
    from i_stock_request_list
    where clinic_id = ?
    and request_id = ?
    order by request_id;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $sClinicID, $req_id);

    if($stmt->execute()){
        $stmt->bind_result($request_id, $request_title, $request_datetime, $request_status, $status_con, $section_id, $request_detail);
        while($stmt->fetch()){
            $data_item_req[$request_id]["req_id"] = $request_id;
            $data_item_req[$request_id]["title"] = $request_title;
            $data_item_req[$request_id]["date"] = $request_datetime;
            $data_item_req[$request_id]["status"] = $request_status;
            $data_item_req[$request_id]["status_con"] = $status_con;
            $data_item_req[$request_id]["section"] = $section_id;
            $data_item_req[$request_id]["detail"] = $request_detail;
        }
        // print_r($data_item_req);
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    if($req_id != ""){
        if(count($data_item_req) > 0){
            foreach($data_item_req as $key => $value){
                $sJS .= "$('#supply_request_items_create [name=clinic_id]').val('".$sClinicID."');";
                $sJS .= '$("#supply_request_items_create [name=clinic_id]").attr("data-odata","'.$sClinicID.'");';
                $sJS .= '$("#supply_request_items_create [name=clinic_id]").attr("disabled", true);';
                $sJS .= '$("#supply_request_items_create [name=clinic_id]").attr("style", "background-color: #eee;");';

                $sJS .= "$('#supply_request_items_create [name=request_id]').val('".$value["req_id"]."');";
                $sJS .= '$("#supply_request_items_create [name=request_id]").attr("data-odata","'.$value["req_id"].'");';
                $sJS .= '$("#supply_request_items_create [name=request_id]").attr("disabled", true);';
                $sJS .= '$("#supply_request_items_create [name=request_id]").attr("style", "background-color: #eee;");';

                $sJS .= "$('#supply_request_items_create [name=section_id]').val('".$value["section"]."');";
                $sJS .= '$("#supply_request_items_create [name=section_id]").attr("data-odata","'.$value["section"].'");';
                // $sJS .= '$("#supply_request_items_create [name=section_id]").attr("disabled", true);';
                // $sJS .= '$("#supply_request_items_create [name=section_id]").attr("style", "background-color: #eee;");';

                $sJS .= "$('#supply_request_items_create [name=request_title]').val('".$value["title"]."');";
                $sJS .= '$("#supply_request_items_create [name=request_title]").attr("data-odata","'.$value["title"].'");';

                $sJS .= "$('#supply_request_items_create [name=request_detail]').val('".$value["detail"]."');";
                $sJS .= '$("#supply_request_items_create [name=request_detail]").attr("data-odata","'.$value["detail"].'");';
            }
        }
    }
    else{
        $sJS .= "$('#supply_request_items_create [name=clinic_id]').val('".$sClinicID."');";
        $sJS .= '$("#supply_request_items_create [name=clinic_id]").attr("disabled", true);';
        $sJS .= '$("#supply_request_items_create [name=clinic_id]").attr("style", "background-color: #eee;");';
    }
?>

<div id="supply_request_items_create" class="fl-wrap-col appointments-mt-1" style="min-width:500;">
    <div id="data_defult" data-reqid="<? echo $req_id; ?>"></div>
    <div class="fl-fill fl-auto">
        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>คลินิก:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont3" style="min-width:150px">
                <b><select name='clinic_id' data-id='clinic_id' data-odata='' class='save-data input-group'>
                    <option value="">-- Please Select --</option>
                    <option value="IHRI" data-id="clinic_id"> IHRI </option>
                </select></b>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>Request ID:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:200px">
                <input type='text' name='request_id' data-id ='request_id' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>หน่วยงาน:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont3" style="min-width:200px">
                <b><select name='section_id' data-id='section_id' data-odata='' class='save-data input-group'>
                    <? $data_id = "section_id"; include("supply_request_opt_section_list.php"); ?>
                </select></b>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ชื่อ:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:200px">
                <input type='text' name='request_title' data-id ='request_title' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>รายละเอียด:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <textarea name="request_detail" data-id="request_detail" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='3'></textarea>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:250px">
                <button id='btn_save_form_view' class='btn btn-success border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> บันทึกคำร้องขอ </button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
            </div>
            <div class="fl-fix" style="min-width: 30px;"></div>
            <div class="fl-fix appointments-text-left" style="min-width:250px">
                <button id='btn_cancel' class='btn btn-danger border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> ยกเลิก </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $sJS; ?>

        if($("#supply_request_items_create #data_defult").data("reqid") == ""){
            var aData = {
                mode: "gen_running_code_req"
            };

            $.ajax({url: "supply_request_ajax.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#supply_request_items_create [name=request_id]").val(result);
            }});
        }

        // Clos dialog
        $("#supply_request_items_create #btn_cancel").on("click", function(){
            var objthis = $(this);
            closeDlg(objthis, "0");
        });
        
        // Save data
        $("#supply_request_items_create #btn_save_form_view").unbind("click");
        $("#supply_request_items_create #btn_save_form_view").on("click", function(){
            if($("#supply_request_items_create #data_defult").data("reqid") == ""){
                var aData = {
                    mode: "check_dup_running_code_req",
                    req_id: $("#supply_request_items_create [name=request_id]").val()
                };

                $.ajax({url: "supply_request_ajax.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        if(result == 1){
                            alert("ไม่สามารถบันทึกข้อมูลได้ Req ID ซ้ำ!");
                        }
                        else{
                            saveFormData_supply_request();                
                        }
                }});
            }
            else{
                saveFormData_supply_request();
            }
        });
    });

    function getWObjValue(obj){
        var sValue = "";
        if($(obj)){
            var sTagName = $(obj).prop("tagName").toUpperCase();

            if(sTagName=="INPUT"){
                if($(obj).prop("type")){
                    if($(obj).prop("type").toLowerCase()=="checkbox"){
                        sValue = ($(obj).prop("checked"))?1:"0";
                    }
                    else if($(obj).prop("type").toLowerCase()=="radio"){
                        var sName = $(obj).attr("name");
                        sValue = $("input[name='"+sName+"']").filter(":checked").val();
                    }
                    else{
                        sValue = $(obj).val();
                    }
                }
                else{
                    sValue = $(obj).val();
                }
            }
            else{
                sValue = $(obj).val();
            }

            if($(obj).hasClass("v_date")){
                var arrDate = sValue.split("/");

                if(arrDate.length == 3){
                    sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
                }
            }
            
            return sValue;
        }
    }

    function saveFormData_supply_request(){
        var lst_data_obj = [];

        var old_value = "";
        var date_res_old_or_normal = null;
        var date_res_old_or_normal_id = null;
        $("#supply_request_items_create .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            objVal = getWObjValue($(objx));
            odata_val = $(objx).data("odata");
            if(typeof odata_val === "undefined"){
                odata_val = "";
            }
            if(typeof objVal === "undefined"){
                objVal  = "";
            }
            odata_val = odata_val.toString().replace(/'/g,"");
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = objVal;
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var aData = {
                app_mode: "supply_request",
                mode_save: $("#supply_request_items_create #data_defult").data("reqid") == "" ? "true" : "false",
                request_id: $("#supply_request_items_create [name=request_id]").val(),
                dataid: lst_data_obj
            };
            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_supply_request);
            $("#supply_request_items_create .hide-old-date").hide();
            $("#supply_request_items_create #btn_save_form_view").next("#supply_request_items_create .spinner").show();
            $("#supply_request_items_create #btn_save_form_view").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_supply_request(flagSave, aData, rtnDataAjax){
        if(flagSave){
            $.notify("Save Data", "success");

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });
        }

        $("#supply_request_items_create #btn_save_form_view").next("#supply_request_items_create .spinner").hide();
        $("#supply_request_items_create #btn_save_form_view").show();
    }
</script>