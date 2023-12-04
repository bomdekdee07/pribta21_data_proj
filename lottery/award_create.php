<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $itemCode = getQS("item_code");
    $sid = getSS("s_id");

    $bind_param = "s";
    $array_val = array($itemCode);
    $data_award = array();

    $query = "SELECT correct_number,
        item_code,
        upd_date
    FROM award_number
    WHERE item_code = ?
    and upd_date = curdate();";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($correct_number, $item_code, $upd_date);
        while($stmt->fetch()){
            $data_award["number"] = $correct_number;
            $data_award["code"] = $item_code;
            $data_award["date"] = $upd_date;
        }
    }
    $stmt->close();
    $mysqli->close();

    $check_have_data = count($data_award);
    $html_bind = "";
    if($check_have_data > 0){
        $html_bind .= '$("#main_create_award [name=correct_number]").val('.json_encode($data_award["number"]).');';
        $html_bind .= '$("#main_create_award [name=correct_number]").attr("data-odata",'.(json_encode($data_award["number"])).');';

        $html_bind .= '$("#main_create_award [name=upd_date]").attr("data-odata",'.(json_encode($data_award["date"])).');';
        $html_bind .= '$("#main_create_award [name=item_code]").attr("data-odata",'.(json_encode($data_award["code"])).');';
    }

    $html_bind .= '$("#main_create_award [name=item_code]").val('.json_encode($itemCode).')';
?>

<div class="fl-wrap-col" id="main_create_award" data-sid="<? echo $sid; ?>">
    <div class="fl-wrap-row h-40"></div>
    <div class="fl-wrap-row font-s-2 h-35">
        <div class="fl-fix w-50"></div>
        <div class="fl-fix w-100 fl-mid-right">
            Date:
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill fl-mid-left">
            <input type="text" name="upd_date" data-id="upd_date" data-odata="" class="save-data" style="width: 250px;" disabled/>
        </div>
        <div class="fl-fix w-50"></div>
    </div>

    <div class="fl-wrap-row font-s-2 h-30">
        <div class="fl-fix w-50"></div>
        <div class="fl-fix w-100 fl-mid-right">
            Item Code:
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill fl-mid-left">
            <input type="text" name="item_code" data-id="item_code" data-odata="" class="save-data" style="width: 250px;" disabled/>
        </div>
        <div class="fl-fix w-50"></div>
    </div>

    <div class="fl-wrap-row font-s-2 h-30">
        <div class="fl-fix w-50"></div>
        <div class="fl-fix w-100 fl-mid-right">
            Award Number:
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill fl-mid-left">
            <input type="text" name="correct_number" data-id="correct_number" data-odata="" class="save-data" style="width: 250px;"/>
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

        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();
        var today = (day<10 ? '0' : '') + day + '/' +(month<10 ? '0' : '') + month + '/' + d.getFullYear();
        
        $("#main_create_award [name=upd_date]").val(today);
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
        $("#main_create_award .save-data").each(function(ix,objx){
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
            var item_code_s = $("#main_create_award [name=item_code]").val();
            var date_s = $("#main_create_award [name=upd_date]").val();
            var date_s = date_s.split("/");
            var d = new Date(date_s[2]+"-"+date_s[1]+"-"+date_s[0]);
            var month = d.getMonth()+1;
            var day = d.getDate();
            var year = d.getFullYear();
            var date_convert_format = year+'-'+(month<10 ? '0' : '')+ month+'-'+(day<10 ? '0' : '')+day;

            var number_s = $("#main_create_award [name=correct_number]").val();
            var sid_s = $("#main_create_award").data("sid");
            var aData = {
                app_mode: "create_award",
                item_code: item_code_s,
                upd_date: date_convert_format,
                correct_number: number_s,
                sid: sid_s,
                dataid:lst_data_obj,
            };

            callAjax("db_form_ins_update.php", aData, saveFormDataComplete);
            $("#main_create_award #saveBtn").next(".spinner").show();
            $("#main_create_award #saveBtn").hide();
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

        $("#main_create_award #saveBtn").next(".spinner").hide();
        $("#main_create_award #saveBtn").show();
    }

    function Btcancel(this_v){
        var objthis = this_v;
        closeDlg(objthis, "0");
    }
</script>