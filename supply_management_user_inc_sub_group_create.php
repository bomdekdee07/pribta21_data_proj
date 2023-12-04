<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    $group_type = getQS("group_type");
    $group_code = getQS("group_code");
    $type_id = getQS("type_id");
    // echo $type_id;

    $check_update = getPerm("STOCK", $type_id, "update");
    $check_insert = getPerm("STOCK", $type_id, "insert");
    // echo $check_insert;

    $data_detail_sub = array();
    $query = "select supply_group_type,
        supply_group_code,
        supply_group_name,
        supply_group_desc,
        supply_group_initial,
        supply_group_running
    from i_stock_group
    where supply_group_type = ?
    and supply_group_code = ?
    order by supply_group_type, supply_group_code;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $group_type, $group_code);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_type, $supply_group_code, $supply_group_name, $supply_group_desc, $supply_group_initial, $supply_group_running);
        while($stmt->fetch()){
            $data_detail_sub[$supply_group_code]["group_type"] = $supply_group_type;
            $data_detail_sub[$supply_group_code]["group_code"] = $supply_group_code;
            $data_detail_sub[$supply_group_code]["name"] = $supply_group_name;
            $data_detail_sub[$supply_group_code]["desc"] = $supply_group_desc;
            $data_detail_sub[$supply_group_code]["code"] = $supply_group_initial;
            $data_detail_sub[$supply_group_code]["running"] = $supply_group_running;
        }
    }else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();



    $sJS = "";
    if($group_type != "" && $group_code != ""){
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").val("'.$group_type.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("data-odata","'.$group_type.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("disabled", true);';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("style", "background-color: #eee;");';

        $sJS .= '$("#sub_group_create_main [name=supply_group_code]").val("'.$group_code.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_code]").attr("data-odata","'.$group_code.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_code]").attr("readonly", true);';
        $sJS .= '$("#sub_group_create_main [name=supply_group_code]").attr("style", "background-color: #eee;");';
    }
    else{
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").val("'.$group_type.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("data-odata","'.$group_type.'");';

        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").val("'.$type_id.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("data-odata","'.$type_id.'");';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("disabled", true);';
        $sJS .= '$("#sub_group_create_main [name=supply_group_type]").attr("style", "background-color: #eee;");';
    }

    if(count($data_detail_sub) > 0){
        foreach($data_detail_sub as $key => $value){
            $sJS .= '$("#sub_group_create_main [name=supply_group_name]").val("'.$value["name"].'");';
            $sJS .= '$("#sub_group_create_main [name=supply_group_name]").attr("data-odata","'.$value["name"].'");';

            $sJS .= '$("#sub_group_create_main [name=supply_group_desc]").val("'.$value["desc"].'");';
            $sJS .= '$("#sub_group_create_main [name=supply_group_desc]").attr("data-odata","'.$value["desc"].'");';

            $sJS .= '$("#sub_group_create_main [name=supply_group_initial]").val("'.$value["code"].'");';
            $sJS .= '$("#sub_group_create_main [name=supply_group_initial]").attr("data-odata","'.$value["code"].'");';

            $sJS .= '$("#sub_group_create_main [name=supply_group_running]").val("'.$value["running"].'");';
            $sJS .= '$("#sub_group_create_main [name=supply_group_running]").attr("data-odata","'.$value["running"].'");';
        }
    }
?>

<div id="sub_group_create_main" class="fl-wrap-col appointments-mt-1" style="min-width:500;">
    <div id="data-defult" data-grouptype="<? echo $group_type; ?>" data-groupcode="<? echo $group_code; ?>" data-checkupdate="<? echo $check_update; ?>" data-insert="<? echo $check_insert; ?>" ></div>
    <div class="fl-fill fl-auto">
        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ประเภท:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont3" style="min-width:250px">
                <b><select name='supply_group_type' data-id='supply_group_type' data-odata='' class='save-data input-group'>
                    <!-- include file opt -->
                    <? $doc_code = "supply_group_type"; include("supply_management_opt_sub_group_type.php"); ?>
                </select></b>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>รหัส:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:150px">
                <input type='text' name='supply_group_code' data-id ='supply_group_code' data-require='' data-odata='' class='save-data input-group' value=''>
                <input type="hidden" name='supply_group_running' data-id ='supply_group_running' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ชื่อประเภท:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <input type='text' name='supply_group_name' data-id ='supply_group_name' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>รายละเอียด:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <textarea name="supply_group_desc" data-id="supply_group_desc" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='3'></textarea>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>รหัสประเภท:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:100px">
                <input type='text' name='supply_group_initial' data-id ='supply_group_initial' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>

        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:250px">
                <button id='btn_save_form_view' class='btn btn-success border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> บันทึกข้อมูล </button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
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
        <? echo $sJS ?>

        // Clos dialog
        $("#sub_group_create_main #btn_cancel").on("click", function(){
            var objthis = $(this);
            closeDlg(objthis, "0");
        })

        $("#sub_group_create_main [name=supply_group_type]").unbind("change");
        $("#sub_group_create_main [name=supply_group_type]").on("change", function(){
            var group_type = $(this).val();
            var aData = {
                supply_group_type: group_type
            };

            $.ajax({url: "supply_management_inc_sub_group_runcode.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var code = result.split(",");
                    $("#sub_group_create_main [name=supply_group_code]").val(code[0]);
                    $("#sub_group_create_main [name=supply_group_initial]").val(code[1]);
                    $("#sub_group_create_main [name=supply_group_running]").val(parseInt(code[0].substring(2)));
            }});
        });

        if($("#sub_group_create_main #data-defult").data("groupcode") == ""){
            $("#sub_group_create_main [name=supply_group_type]").trigger("change");

            $("#sub_group_create_main #btn_save_form_view").unbind("click");
            $("#sub_group_create_main #btn_save_form_view").on("click", function(){
                var check_update = $("#sub_group_create_main #data-defult").data("checkupdate");
                var check_insert = $("#sub_group_create_main #data-defult").data("insert");

                if(check_update == "1" || check_insert == "1"){
                    // Check Dup PK.
                    var aData_check = {
                        mode: "dup_supply_code",
                        supply_group_code: $("#sub_group_create_main [name=supply_group_code]").val()
                    };
                    $.ajax({url: "supply_management_ajax_group_type.php", 
                        method: "POST",
                        cache: false,
                        data: aData_check,
                        success: function(result){
                            if(result == 1){
                                alert("ไม่สารถเพิ่มข้อมูลได้ รหัสซ้ำ!");
                            }else{
                                saveFormData_sub_group();
                            }
                    }});
                }else{
                    alert("ไม่สามารถแก้ไขข้อมูลได้ ไม่มีสิทธิ์ในการแก้ไขข้อมูล");
                }
            });
        }
        else{
            $("#sub_group_create_main #btn_save_form_view").unbind("click");
            $("#sub_group_create_main #btn_save_form_view").on("click", function(){
                var check_update = $("#sub_group_create_main #data-defult").data("checkupdate");
                var check_insert = $("#sub_group_create_main #data-defult").data("insert");

                if(check_update == "1" || check_insert == "1"){
                    saveFormData_sub_group();
                }else{
                    alert("ไม่สามารถแก้ไขข้อมูลได้ ไม่มีสิทธิ์ในการแก้ไขข้อมูล");
                }
            });
        }
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

    function saveFormData_sub_group(){
        var lst_data_obj = [];

        var old_value = "";
        var date_res_old_or_normal = null;
        var date_res_old_or_normal_id = null;
        $("#sub_group_create_main .save-data").each(function(ix,objx){
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
                app_mode: "supply_sub_group",
                supply_group_type: $("#sub_group_create_main [name=supply_group_type]").val(),
                supply_group_code: $("#sub_group_create_main [name=supply_group_code]").val(),
                dataid: lst_data_obj
            };
            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_sub_group);
            $("#sub_group_create_main .hide-old-date").hide();
            $("#sub_group_create_main #btn_save_form_view").next("#sub_group_create_main .spinner").show();
            $("#sub_group_create_main #btn_save_form_view").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_sub_group(flagSave, aData, rtnDataAjax){
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

        $("#sub_group_create_main #btn_save_form_view").next("#sub_group_create_main .spinner").hide();
        $("#sub_group_create_main #btn_save_form_view").show();
    }
</script>