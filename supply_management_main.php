<?
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));
?>

<div id="supply_management_main" class="fl-wrap-col holiday-mt-0">
<span class="data-defult" data-ss=<? echo $sSID; ?> data-clinicid=<? echo $sClinicID; ?>></span>
    <div class="fl-wrap-row h-40">
        <div class="fl-fill holiday-ml-0">
            <button type="button" id="holiday_group_type" class="btn btn-secondary type smallfont2 supply-type-btn input-group"><b><i class="fa fa-cubes" aria-hidden="true"></i> Group Type</b></button>
        </div>
        <div class="fl-fill holiday-ml-0">
            <button type="button" id="holiday_supply_group" class="btn btn-secondary sub-group smallfont2 supply-type-btn input-group"><b><i class="fa fa-cube" aria-hidden="true"></i> Supply Group</b></button>
        </div>
        <div class="fl-fill holiday-ml-0 holiday-mr-1">
            <button type="button" id="holiday_group_master" class="btn btn-secondary group-master smallfont2 supply-type-btn input-group"><b><i class="fa fa-cogs" aria-hidden="true"></i> Supply Group Master</b></button>
        </div>
    </div>

    <!-- Group Type -->
    <div class="fl-wrap-row group-type-show h-40">
        <div class="fl-fill holiday-box-serch holiday-ml-0 holiday-mr-1">
            <div class="fl-wrap-row">
                <div class="fl-fix smallfont4 clear-value holiday-ml-2 supply-mt-1" style="min-width: 50px;">
                    <i class="fa fa-eraser" aria-hidden="true"></i>
                </div>
                <div class="fl-fix holiday-mt-2" style="min-width: 100px;">
                    <input type="text" id="supply_group_type" name="supply_group_type" data-id="supply_group_type" class="save-data input-group smallfont2 holiday-mt-01" data-odata="" value="">
                </div>
                <div class="fl-fill holiday-ml-1 holiday-mt-2">
                    <input type="text" id="supply_type_name" name="supply_type_name" data-id="supply_type_name" title="Supply Name Thai" class="save-data input-group smallfont2 holiday-mt-01" data-odata="" value="">
                </div>
                <div class="fl-fill holiday-ml-1 holiday-mt-2">
                    <input type="text" id="supply_type_name_en" name="supply_type_name_en" data-id="supply_type_name_en" title="Supply Name English" class="save-data input-group smallfont2 holiday-mt-01" data-odata="" value="">
                </div>
                <div class="fl-fix holiday-ml-1 holiday-mt-2" style="min-width: 150px;">
                    <input type="text" id="supply_type_initial" name="supply_type_initial" data-id="supply_type_initial" class="save-data input-group smallfont2 holiday-mt-01" data-odata="" value="">
                </div>
                <div class="fl-fix holiday-ml-1 holiday-mt-2 input-group" style="min-width: 200px;">
                    <input type="checkbox" id="is_service" name="is_service" data-id="is_service" data-odata="" value="" class="save-data input-group">
                </div>
                <div class="fl-fix smallfont4 add-value holiday-ml-2 supply-mt-1" style="min-width: 150px;">
                    <i class="fa fa-plus-square" aria-hidden="true"></i>
                    <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="fl-wrap-row group-type-show h-25">
        <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
            <div class="fl-wrap-row">
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 175px;">
                    <b><span style="margin-left: 55px;">ประเภท</span></b>
                </div>
                <div class="fl-fill holiday-text-head holiday-smallfont2">
                    <b><span>ชื่อประเภท</span></b>
                </div>
                <div class="fl-fill holiday-text-head holiday-smallfont2">
                    <b><span>ชื่อประเภท Eng.</span></b>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 165px;">
                    <b><span>รหัสประเภท</span></b>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 370px;">
                    <b><span style="margin-right: 160px;">เงื่อนไขประเภท</span></b>
                </div>
            </div>
        </div>
    </div>
    <!-- END Group Type -->

    <!-- Sub Group Type -->
    <div class="fl-wrap-row group-sub-show h-25">
        <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
            <div class="fl-wrap-row">
                <div class="fl-fix add-value-sub holiday-text-head smallfont3" style="min-width: 55px;">
                    <i class="fa fa-plus-square" aria-hidden="true"></i>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 125px;">
                    <b><span>ประเภท</span></b>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 200px;">
                    <b><span>รหัส</span></b>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 270px;">
                    <b><span>ชื่อประเภท</span></b>
                </div>
                <div class="fl-fill holiday-text-head holiday-smallfont2">
                    <b><span>รายละเอียด</span></b>
                </div>
                <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 350px;">
                    <b><span style="margin-right: 150px;">รหัสประเภท</span></b>
                </div>
            </div>
        </div>
    </div>
    <!-- END Sub Group Type -->

    <!-- DATA show -->
    <div class='fl-wrap-col fl-auto holiday-ml-0 holiday-mr-1 h-800' id="supply_show_data">
        <!-- Ajax reload data -->
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#supply_management_main #holiday_group_type").unbind("click");
        $("#supply_management_main #holiday_group_type").on("click", function(){
            $("#supply_management_main #holiday_group_type").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
            $("#supply_management_main #holiday_group_type").attr("value", "selected");
            $("#supply_management_main #holiday_supply_group").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_supply_group").attr("value", "");
            $("#supply_management_main #holiday_group_master").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_group_master").attr("value", "");

            $(".group-type-show").show();
            $(".group-sub-show").hide();

            $.ajax({url: "supply_management_inc_group_type.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_management_main #supply_show_data").children().remove();
                    $("#supply_management_main #supply_show_data").append(result);
            }});
        });

        $("#supply_management_main #holiday_supply_group").unbind("click");
        $("#supply_management_main #holiday_supply_group").on("click", function(){
            $("#supply_management_main #holiday_supply_group").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
            $("#supply_management_main #holiday_supply_group").attr("value", "selected");
            $("#supply_management_main #holiday_group_type").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_group_type").attr("value", "");
            $("#supply_management_main #holiday_group_master").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_group_master").attr("value", "");

            $(".group-type-show").hide();
            $(".group-sub-show").show();

            $.ajax({url: "supply_management_inc_sub_group.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_management_main #supply_show_data").children().remove();
                    $("#supply_management_main #supply_show_data").append(result);
            }});
        });

        $("#supply_management_main #holiday_group_master").unbind("click");
        $("#supply_management_main #holiday_group_master").on("click", function(){
            $("#supply_management_main #holiday_group_master").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
            $("#supply_management_main #holiday_group_master").attr("value", "selected");
            $("#supply_management_main #holiday_group_type").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_group_type").attr("value", "");
            $("#supply_management_main #holiday_supply_group").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_main #holiday_supply_group").attr("value", "");

            $(".group-type-show").hide();
            $(".group-sub-show").hide();

            //supply_master_inc_main.php
            $.ajax({url: "supply_master_inc_main.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_management_main #supply_show_data").children().remove();
                    $("#supply_management_main #supply_show_data").append(result);
            }});
        });

        // Autu load first page
        $("#supply_management_main #holiday_group_type").trigger('click');

        // Click save
        $("#supply_management_main .add-value").unbind("click");
        $("#supply_management_main .add-value").on("click", function(){
            saveFormData_supply();
        });
        
        // Check Dup PK
        $("#supply_management_main #supply_group_type").unbind("blur");
        $("#supply_management_main #supply_group_type").blur(function(){
            $aData = {
                data_new: $(this).val()
            }

            $.ajax({url: "supply_management_ajax_group_type.php", 
                method: "POST",
                cache: false,
                data: $aData,
                success: function(result){
                    if(result == "duplicate"){
                        $("#supply_management_main #supply_group_type").val("");
                        $("#supply_management_main #supply_group_type").attr("style","background-color: red");
                        alert("ประเภทซ้ำ!");
                    }
                    else{
                        $("#supply_management_main #supply_group_type").attr("style","background-color: white");
                    }
            }});
        });

        // Open dialog create
        $("#supply_management_main .add-value-sub").unbind("click");
        $("#supply_management_main .add-value-sub").on("click", function(){
            var sUrl_appoint = "supply_management_inc_sub_group_create.php?group_type=";

            showDialog(sUrl_appoint, "Supply Group Main", "600", "500", "", function(sResult){
                var url_gen = "supply_management_inc_sub_group.php";

                $("#supply_show_data").load(url_gen);
            }, false, function(sResult){});
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

    function saveFormData_supply(){
        var lst_data_obj = [];

        var old_value = "";
        var date_res_old_or_normal = null;
        var date_res_old_or_normal_id = null;
        $("#supply_management_main .save-data").each(function(ix,objx){
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
                app_mode: "supply_group_type",
                supply_group_type: $("#supply_management_main #supply_group_type").val(),
                dataid: lst_data_obj
            };

            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_supply);
            $("#supply_management_main .hide-old-date").hide();
            $("#supply_management_main .add-value").next("#supply_management_main .spinner").show();
            $("#supply_management_main .add-value").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_supply(flagSave, aData, rtnDataAjax){
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

        $("#supply_management_main .add-value").next("#supply_management_main .spinner").hide();
        $("#supply_management_main .add-value").show();

        $.ajax({url: "supply_management_inc_group_type.php", 
            method: "POST",
            cache: false,
            success: function(result){
                $("#supply_management_main #supply_show_data").children().remove();
                $("#supply_management_main #supply_show_data").append(result);
        }});
    }
</script>