<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getQS("s_id");
    $sClinicID = getQS("clinic_id");
    $dte_get = getQS("date_res");
    $mode = getQS("mode");
?>

<?
    $data_holiday = array();
    if($mode != "create"){
        if($sSID == ""){
            $query = "select clinic_id, holiday_date, s_id, holiday_title, remark from i_holiday 
            where clinic_id = ? 
            and holiday_date = ?
            and s_id = 'none';";

            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ss',$sClinicID, $dte_get); // echo "query : $query";

            if($stmt->execute()){
                $stmt->bind_result($clinic_id, $holiday_date, $s_id, $holiday_title, $remark);
                while ($stmt->fetch()) {
                    $data_holiday[$holiday_date]["clinicid"] = $clinic_id;
                    $data_holiday[$holiday_date]["date"] = $holiday_date;
                    $data_holiday[$holiday_date]["sid"] = "";
                    $data_holiday[$holiday_date]["title"] = $holiday_title;
                    $data_holiday[$holiday_date]["remark"] = $remark;
                    // print $data_appointmentd["appointment"]["l_name"];
                }
            }
            else{
                $msg_error .= $stmt->error; //เดี่ยว
            }
            $stmt->close();
        }
        else if($sSID != ""){
            $data_holiday = array();
            $query = "select clinic_id, holiday_date, s_id, holiday_title, remark from i_holiday 
            where clinic_id = ? 
            and holiday_date = ?
            and s_id = ?;";

            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sss',$sClinicID, $dte_get, $sSID); // echo "query : $query";

            if($stmt->execute()){
                $stmt->bind_result($clinic_id, $holiday_date, $s_id, $holiday_title, $remark);
                while ($stmt->fetch()) {
                    $data_holiday[$holiday_date]["clinicid"] = $clinic_id;
                    $data_holiday[$holiday_date]["date"] = $holiday_date;
                    $data_holiday[$holiday_date]["sid"] = $s_id;
                    $data_holiday[$holiday_date]["title"] = $holiday_title;
                    $data_holiday[$holiday_date]["remark"] = $remark;
                    // print $data_appointmentd["appointment"]["l_name"];
                }
            }
            else{
                $msg_error .= $stmt->error; //เดี่ยว
            }
            $stmt->close();
        }
    }

    $mysqli->close();

    $sJs = '';
    if(count($data_holiday) > 0){
        $sJs .= '$("#holiday_edit_create [name=clinic_id]").val("'.$sClinicID.'");';
        $sJs .= '$("#holiday_edit_create [name=clinic_id]").attr("data-odata","'.$sClinicID.'");';

        $sJs .= '$("#holiday_edit_create [name=holiday_date]").val("'.$dte_get.'");';
        $sJs .= '$("#holiday_edit_create [name=holiday_date]").attr("data-odata","'.$dte_get.'");';

        $s_id = $data_holiday[$dte_get]["sid"];
        $sJs .= '$("#holiday_edit_create [name=s_id]").attr("data-odata","'.$s_id.'");';

        $title = $data_holiday[$dte_get]["title"];
        $sJs .= '$("#holiday_edit_create [name=holiday_title]").val("'.$title.'");';
        $sJs .= '$("#holiday_edit_create [name=holiday_title]").attr("data-odata","'.$title.'");';

        $remark = $data_holiday[$dte_get]["remark"];
        $sJs .= '$("#holiday_edit_create [name=remark]").val("'.$remark.'");';
        $sJs .= '$("#holiday_edit_create [name=remark]").attr("data-odata","'.$remark.'");';
    }else{
        $sJs .= '$("#holiday_edit_create [name=clinic_id]").val("'.$sClinicID.'");';
        $sJs .= '$("#holiday_edit_create [name=clinic_id]").attr("data-odata","'.$sClinicID.'");';

        $sJs .= '$("#holiday_edit_create [name=holiday_date]").val("'.$dte_get.'");';
        $sJs .= '$("#holiday_edit_create [name=holiday_date]").attr("data-odata","'.$dte_get.'");';

        $sJs .= '$("#holiday_edit_create [name=s_id]").attr("data-odata","'.$sSID.'");';
    }
?>

<div id="holiday_edit_create" class="fl-wrap-col appointments-mt-1" style="min-width:500;" data-dateget="<? echo $dte_get; ?>" data-clinicid="<? echo $sClinicID; ?>" data-sid="<? echo $sSID; ?>" >
    <div class="fl-fill fl-auto">
        <input type="hidden" name="check_dateDup" data-id="check_dateDup" data-require='' class='input-group' value="">
        <div class="fl-wrap-row holiday-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>Clinic ID:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <b><select name='clinic_id' data-id='clinic_id' data-odata='' class='save-data input-group'>
                    <option value="">-- Please Select --</option>
                    <option value="IHRI" data-id="clinic_id"> IHRI </option>
                </select></b>
            </div>
        </div>
        <div class="fl-wrap-row smallfont3 holiday-mt-2" style="margin-top: 0px;">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>วันที่:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <input name='holiday_date' data-id ='holiday_date' data-require='' data-odata='' class='save-data input-group' value=''>
            </div>
        </div>
        <div class="fl-wrap-row holiday-mt-2 smallfont3" id="holiday_hide">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>ชื่อเจ้าหน้าที่:</span>
            </div>
            <div class="fl-fix smallfont2" style="min-width:160px; margin-left: 3px;">
                <select name='s_id' data-id='s_id' data-odata='' class='save-data input-group'>
                    <? $data_id = "s_id"; $data_result_staff = ""; $sSID; include("doctor_opt_staff_md.php"); ?>
                </select>
            </div>
        </div>
        <div class="fl-wrap-row holiday-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>รายละเอียด:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <textarea name="holiday_title" data-id="holiday_title" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='2'></textarea>
            </div>
        </div>
        <div class="fl-wrap-row holiday-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:150px">
                <span>หมายเหตุ:</span>
            </div>
            <div class="fl-fix" style="min-width: 3px;"></div>
            <div class="fl-fix smallfont2" style="min-width:250px">
                <textarea name="remark" data-id="remark" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='3'></textarea>
            </div>
        </div>
        <div class="fl-wrap-row appointments-mt-2 smallfont3">
            <div class="fl-fix appointments-text-right" style="min-width:250px">
                <button id='btn_save_form_view' class='btn btn-success border' type='button' onclick='saveFormData_holiday();'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> บันทึกข้อมูล </button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
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
        // funtion hide name staff
        var s_id = $("#holiday_edit_create").data("sid");
        if(s_id == ""){
            $("#holiday_edit_create #holiday_hide").hide();
        }
        else{
            $("#holiday_edit_create #holiday_hide").show();
        }

        <? echo $sJs; ?>

        // function datepicker
        $("#holiday_edit_create [name=holiday_date]").datepicker({
			dateFormat:"yy-mm-dd",
			changeYear:true,
			changeMonth:true
		});

        // Clos dialog
        $("#holiday_edit_create #btn_cancel").on("click", function(){
            var objthis = $(this);
            closeDlg(objthis, "0");
        })
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

    function saveFormData_holiday(){
        var lst_data_obj = [];
        $("#holiday_edit_create [name=holiday_date]").val($("#holiday_edit_create [name=holiday_date]").val());

        var old_value = "";
        var date_res_old_or_normal = null;
        var date_res_old_or_normal_id = null;
        $("#holiday_edit_create .save-data").each(function(ix,objx){
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
                console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");

                if($(objx).data("id") == "holiday_date") {
                    date_res_old_or_normal = $("#holiday_edit_create [name=holiday_date]").data("odata");
                    date_res_old_or_normal_id = "holiday_date";
                    // console.log("TEST: "+date_res_old_or_normal+"/"+objVal);
                }
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var aData = {
                app_mode: "holiday",
                clinic_id: $("#holiday_edit_create [name=clinic_id]").val(),
                date_res: $("#holiday_edit_create [name=holiday_date]").val(),
                data_old: date_res_old_or_normal,
                data_old_id: date_res_old_or_normal_id,
                sid: $("#holiday_edit_create [name=s_id]").val(),
                dataid: lst_data_obj,
            };

            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_holiday);
            $("#holiday_edit_create .hide-old-date").hide();
            $("#holiday_edit_create #btn_save_form_view").next("#holiday_edit_create .spinner").show();
            $("#holiday_edit_create #btn_save_form_view").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_holiday(flagSave, aData, rtnDataAjax){
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

        $("#holiday_edit_create #btn_save_form_view").next("#holiday_edit_create .spinner").hide();
        $("#holiday_edit_create #btn_save_form_view").show();
    }
</script>