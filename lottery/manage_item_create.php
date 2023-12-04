<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $itemCode = getQS("item_code");
    $sid = getSS("s_id");
    
    $bind_param = "";
    $array_val = array();
    $data_item_master = array();

    $query = "SELECT item_code, 
        item_name
    FROM items_master";

    if($itemCode != ""){
        $query .= " WHERE item_code = ?";
        $bind_param .= "s";
        $array_val[] = $itemCode;
    }

    $query .= " ORDER BY item_code;";

    $stmt = $mysqli->prepare($query);
    if($itemCode != "")
        $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($item_code, $item_name);
        while($stmt->fetch()){
            $data_item_master["code"] = $item_code;
            $data_item_master["name"] = $item_name;
        }
        // print_r($data_item_master);
    }
    $stmt->close();
    $mysqli->close();

    $html_bind = "";
    $item_code_run = "";
    if($itemCode == ""){
        list($mem_prefix,$mem_num) = sscanf($data_item_master["code"],"%[A-Za-z]%[0-9]");
        $item_code_run = $mem_prefix . str_pad($mem_num+1, 5, '0', STR_PAD_LEFT);
        $html_bind .= '$("#main_create_item [name=item_code]").val("'.$item_code_run.'");';
    }
    else{
        $html_bind .= '$("#main_create_item [name=item_code]").val('.json_encode($data_item_master["code"]).');';
        $html_bind .= '$("#main_create_item [name=item_code]").attr("data-odata",'.(json_encode($data_item_master["code"])).');';

        $html_bind .= '$("#main_create_item [name=item_name]").val('.json_encode($data_item_master["name"]).');';
        $html_bind .= '$("#main_create_item [name=item_name]").attr("data-odata",'.(json_encode($data_item_master["name"])).');';
    }
?>

<div class="fl-wrap-col" id="main_create_item" data-sid="<? echo $sid; ?>">
    <div class="fl-wrap-row h-40"></div>
    <div class="fl-wrap-row font-s-2 h-35">
        <div class="fl-fix w-50"></div>
        <div class="fl-fix w-100 fl-mid-right">
            Item Code:
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill fl-mid-left">
            <input type="text" name="item_code" data-id="item_code" data-odata="" class="save-data" style="width: 250px;"/>
        </div>
        <div class="fl-fix w-50"></div>
    </div>

    <div class="fl-wrap-row font-s-2 h-30">
        <div class="fl-fix w-50"></div>
        <div class="fl-fix w-100 fl-mid-right">
            Item Name:
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill fl-mid-left">
            <input type="text" name="item_name" data-id="item_name" data-odata="" class="save-data" style="width: 250px;"/>
        </div>
        <div class="fl-fix w-50"></div>
    </div>
    <div class="fl-wrap-row h-10"></div>

    <div class="fl-wrap-row font-s-2 h-40">
        <div class="fl-fill fl-mid-right">
            <button id="saveBtn" type="button" style="padding: 3px 10px 2px;" onclick="saveFormData()" class="btn btn-success font-s-2">บันทึก</button>
            <button id="cancelBtn" type="button" style="padding: 3px 10px 2px;" onclick="Btcancel(this)" class="btn btn-danger font-s-2 holiday-ml-1">ยกเลิก</button>
        </div>
        <div class="fl-fix w-85"></div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $html_bind; ?>
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

    function saveFormData(){
        var lst_data_obj = [];

        var old_value = "";
        $("#main_create_item .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            if(objVal != old_value){
                objVal = getWObjValue($(objx));
                odata_val = $(objx).data("odata");
                if(typeof odata_val === "undefined"){
                    odata_val = "";
                }
                if(typeof objVal === "undefined"){
                    objVal  = "";
                }
                odata_val = (odata_val?odata_val.toString().replace(/"|'/g,''):odata_val); //ไม่ใช้แล้วเพราะใช้ json_encode()
                // console.log(odata_val+"new"+objVal);
                // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
                // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);
            }

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = (objVal?objVal.toString().replace(/"|'/g,'') : objVal);
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var item_code_s = $("#main_create_item [name=item_code]").val();
            var sid_s = $("#main_create_item").data("sid");
            var aData = {
                app_mode: "create_item",
                item_code: item_code_s,
                sid: sid_s,
                dataid:lst_data_obj,
            };

            callAjax("db_form_ins_update.php", aData, saveFormDataComplete);
            $("#main_create_item #saveBtn").next(".spinner").show();
            $("#main_create_item #saveBtn").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
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

        $("#main_create_item #saveBtn").next(".spinner").hide();
        $("#main_create_item #saveBtn").show();
    }

    function Btcancel(this_v){
        var objthis = this_v;
        closeDlg(objthis, "0");
    }
</script>