<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $title = getQS("addrtitle");

    if($title != ""){
        $data_bill_custom_detail = array("title" => "", "name" => "", "address" => "", "tax" => "");
        $query = "select bill_title,
            bill_name,
            bill_address,
            bill_tax
        from j_bill_custom
        where uid = ?
        and bill_title = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $uid, $title);

        if($stmt -> execute()){
            $stmt -> bind_result($bill_title, $bill_name, $bill_address, $bill_tax);
            while($stmt -> fetch()){
                $data_bill_custom_detail["title"] = $bill_title;
                $data_bill_custom_detail["name"] = $bill_name;
                $data_bill_custom_detail["address"] = $bill_address;
                $data_bill_custom_detail["tax"] = $bill_tax;
            }
            // print_r($data_bill_custom_detail);
        }
        $stmt->close();
        $mysqli->close();
    }

    $sJS_create = "";
    $sJS_create .= '$("#receipt_create_edit [name=uid]").val("'.$uid.'");';

    if($title != ""){
        $sJS_create .= '$("#receipt_create_edit [name=uid]").attr("data-odata", "'.$uid.'");';

        $sJS_create .= '$("#receipt_create_edit [name=bill_title]").attr("readonly", true);';
        $sJS_create .= '$("#receipt_create_edit [name=bill_title]").attr("style", "background-color: #F2F4F4");';
        $sJS_create .= '$("#receipt_create_edit [name=bill_title]").val("'.$title.'");';
        $sJS_create .= '$("#receipt_create_edit [name=bill_title]").attr("data-odata", "'.$title.'");';

        $sJS_create .= '$("#receipt_create_edit [name=bill_name]").val("'.$data_bill_custom_detail["name"].'");';
        $sJS_create .= '$("#receipt_create_edit [name=bill_name]").attr("data-odata", "'.$data_bill_custom_detail["name"].'");';

        $sJS_create .= '$("#receipt_create_edit [name=bill_tax]").val("'.$data_bill_custom_detail["tax"].'");';
        $sJS_create .= '$("#receipt_create_edit [name=bill_tax]").attr("data-odata", "'.$data_bill_custom_detail["tax"].'");';

        $sJS_create .= '$("#receipt_create_edit [name=bill_address]").val('.(json_encode($data_bill_custom_detail["address"])).');';
        $sJS_create .= '$("#receipt_create_edit [name=bill_address]").attr("data-odata", '.(json_encode($data_bill_custom_detail["address"])).');';
    }
?>

<div class="fl-wrap-col" id="receipt_create_edit" data-uid="<? echo $uid; ?>">
    <div class="fl-wrap-row h-35 fs-small holiday-mt-1">
        <div class="fl-fix fw-b wper-25 fl-mid-right">
            <span>UID:</span>
        </div>
        <div class="fl-fill fl-mid-left holiday-ml-1">
            <input type="text" name="uid" data-id="uid" class="save-data" data-odata="" readonly style="background-color: #F2F4F4; width: 150px;">
        </div>
        <div class="fl-fix w-100 holiday-ml-1 fl-mid-left">
            <input type="checkbox" name="uid_global" id="uid_global" value="1"/> <span for="uid_global" class="font-s-1 fw-b holiday-ml-0 click-global">Global</span>
        </div>
    </div>

    <div class="fl-wrap-row h-35 fs-small">
        <div class="fl-fix fw-b wper-25 fl-mid-right">
            <span>Title:</span>
        </div>
        <div class="fl-fill fl-mid-left holiday-ml-1">
            <input type="text" name="bill_title" data-id="bill_title" class="save-data" data-odata="" style="width: 200px; text-transform:uppercase">
        </div>
    </div>

    <div class="fl-wrap-row h-35 fs-small">
        <div class="fl-fix fw-b wper-25 fl-mid-right">
            <span>Name:</span>
        </div>
        <div class="fl-fill fl-mid-left holiday-ml-1">
            <input type="text" name="bill_name" data-id="bill_name" class="save-data" data-odata="" style="width: 200px;">
        </div>
    </div>

    <div class="fl-wrap-row h-35 fs-small">
        <div class="fl-fix fw-b wper-25 fl-mid-right">
            <span>Tax:</span>
        </div>
        <div class="fl-fill fl-mid-left holiday-ml-1">
            <input type="text" name="bill_tax" data-id="bill_tax" class="save-data" data-odata="" style="width: 200px;">
        </div>
    </div>
    
    <div class="fl-wrap-row h-95 fs-small">
        <div class="fl-fix fw-b wper-25 fl-mid-right">
            <span>Address:</span>
        </div>
        <div class="fl-fill fl-mid-left holiday-ml-1">
            <textarea name="bill_address" data-id="bill_address" class="save-data" data-odata="" style="width: 200px;" rows="4"></textarea>
        </div>
    </div>

    <div class="fl-wrap-row h-95 fs-small">
        <div class="fl-fix fw-b wper-50 fl-mid">
            <button id='btn_save_form_view' class='btn btn-success border' type='button' onclick='saveFormData_bill_custom();'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> บันทึกข้อมูล </button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
        </div>
        <div class="fl-fix fw-b wper-50 fl-mid">
            <button id='btn_cancel' class='btn btn-danger border' type='button'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> ยกเลิก </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        <? echo $sJS_create; ?>

        var uid_main = $("#receipt_create_edit").data("uid");
        if(uid_main == "P00-00000"){
            $("#receipt_create_edit #uid_global").prop("checked", true);
            $("#receipt_create_edit #uid_global").prop("disabled", true);
        }
        else{
            $("#receipt_create_edit #uid_global").prop("checked", false);
            $("#receipt_create_edit #uid_global").prop("disabled", false);
        }

        $("#receipt_create_edit #uid_global").off("clik");
        $("#receipt_create_edit #uid_global").on("click", function(){
            var uid_main = $("#receipt_create_edit").data("uid");
            if($(this).prop("checked") == true){
                $("#receipt_create_edit [name=uid]").val("P00-00000");
            }
            else{
                $("#receipt_create_edit [name=uid]").val(uid_main);
            }
        });

        $("#receipt_create_edit .click-global").off("click");
        $("#receipt_create_edit .click-global").on("click", function(){
            if($("#receipt_create_edit #uid_global").prop("checked") == true){
                $("#receipt_create_edit #uid_global").prop("checked", false);
            }
            else{
                $("#receipt_create_edit #uid_global").prop("checked", true);
            }
        });

        // Clos dialog
        $("#receipt_create_edit #btn_cancel").off("click");
        $("#receipt_create_edit #btn_cancel").on("click", function(){
            var objthis = $(this);
            closeDlg(objthis, "0");
        })

        // Spexial Charector
        $('#receipt_create_edit [name=bill_title]').off("input");
        $('#receipt_create_edit [name=bill_title]').on('input', function() {
            $(this).val($(this).val().replace(/[^a-z0-9]/gi, ''));
        });
    });

    function getWObjValue(obj){
        var sValue = "";
        if($(obj)){
            var sTagName = $(obj).prop("tagName").toUpperCase();

            if(sTagName=="INPUT"){
                if($(obj).prop("type")){
                    if($(obj).prop("type").toLowerCase()=="checkbox"){
                        sValue = ($(obj).prop("checked"))?1:"";
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

    function saveFormData_bill_custom(){
        var lst_data_obj = [];
        var old_value = "";

        $("#receipt_create_edit .save-data").each(function(ix,objx){
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
            var uid_s = $("#receipt_create_edit [name=uid]").val();
            var addrtitle_s = $("#receipt_create_edit [name=bill_title]").val();
            
            var aData = {
                app_mode: "bill_custom",
                uid: uid_s,
                addrtitle: addrtitle_s,
                dataid: lst_data_obj
            }
            // console.log(aData);

            if(addrtitle_s != ""){
                var aData_s = {
                    uid: $("#receipt_create_edit [name=uid]").val(),
                    title: $("#receipt_create_edit [name=bill_title]").val()
                };
                $.ajax({
                    url: "p_receipt_create_ajax.php",
                    case: false,
                    data: aData_s,
                    success: function(results){
                        if(results == "1"){
                            alert("มี Title ซ้ำแล้วอยู๋ในระบบ");
                        }
                        else{
                            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_bill_custom);
                            $("#receipt_create_edit .hide-old-date").hide();
                            $("#receipt_create_edit #btn_save_form_view").next("#receipt_create_edit .spinner").show();
                            $("#receipt_create_edit #btn_save_form_view").hide();
                        }
                    }
                });
            }
            else{
                alert("กรุณากรอกข้อมูล Title");
            }
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_bill_custom(flagSave, aData, rtnDataAjax){
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

        $("#receipt_create_edit #btn_save_form_view").next("#receipt_create_edit .spinner").hide();
        $("#receipt_create_edit #btn_save_form_view").show();
    }
</script>