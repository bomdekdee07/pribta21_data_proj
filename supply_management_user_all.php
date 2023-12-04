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
    $type_id = isset($_POST["type_id"])?$_POST["type_id"] : getQS("type_id");

    $sJS_head = "";
    
    $sJS_head .= '
            <div id="supply_management_user_all" class="fl-wrap-col holiday-mt-0">
            <span class="data-defult" data-ss="'.$sSID.'" data-clinicid="'.$sClinicID.'" data-type="'.$type_id.'" ></span>
            <div class="fl-fill">
                <div class="fl-wrap-row">
                    <div class="fl-fill holiday-ml-0">
                        <button type="button" id="holiday_supply_group" class="btn btn-secondary sub-group smallfont2 supply-type-btn input-group"><b><i class="fa fa-cube" aria-hidden="true"></i> Supply Group</b></button>
                    </div>
                    <div class="fl-fill holiday-ml-0 holiday-mr-1">
                        <button type="button" id="holiday_group_master" class="btn btn-secondary group-master smallfont2 supply-type-btn input-group"><b><i class="fa fa-cogs" aria-hidden="true"></i> Supply Group Master</b></button>
                    </div>
                </div>';
    $sJS_head .= '
                <div class="fl-wrap-row group-sub-show">
                    <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
                        <div class="fl-wrap-row">';
    if(getPerm("STOCK", $type_id, "insert") == "1"){
            $sJS_head .= '  <div class="fl-fix add-value-sub holiday-text-head smallfont3" style="min-width: 55px;">
                                <i class="fa fa-plus-square" aria-hidden="true"></i>';
    }
    else{
        $sJS_head .= '      <div class="fl-fix holiday-text-head smallfont3" style="min-width: 55px;">';
    }
    $sJS_head .= '          </div>';
    $sJS_head .= '      
                            <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 125px;">
                                <b><span>ประเภท</span></b>
                            </div>
                            <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 170px;">
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
                </div>';

    $sJS_head .= '
                <div class="fl-fill fl-auto holiday-ml-0 holiday-mr-1" id="supply_show_data"></div>';
    $sJS_head .= '<div>
            </div>';

    echo $sJS_head;
?>

<script>
    $(document).ready(function(){
        // <div class="fl-fill holiday-ml-0">
        //     <button type="button" id="holiday_group_type" class="btn btn-secondary type smallfont2 supply-type-btn input-group"><b><i class="fa fa-cubes" aria-hidden="true"></i> Supply List</b></button>
        // </div>
        // $("#supply_management_user_all #holiday_group_type").unbind("click");
        // $("#supply_management_user_all #holiday_group_type").on("click", function(){
        //     $("#supply_management_user_all #holiday_group_type").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
        //     $("#supply_management_user_all #holiday_group_type").attr("value", "selected");
        //     $("#supply_management_user_all #holiday_supply_group").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
        //     $("#supply_management_user_all #holiday_supply_group").attr("value", "");
        //     $("#supply_management_user_all #holiday_group_master").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
        //     $("#supply_management_user_all #holiday_group_master").attr("value", "");
        //     $(".group-sub-show").hide();

        //     $("#supply_management_user_all #supply_show_data").children().remove();
        // });

        $("#supply_management_user_all #holiday_supply_group").unbind("click");
        $("#supply_management_user_all #holiday_supply_group").on("click", function(){
            $("#supply_management_user_all #holiday_supply_group").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
            $("#supply_management_user_all #holiday_supply_group").attr("value", "selected");
            $("#supply_management_user_all #holiday_group_type").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_user_all #holiday_group_type").attr("value", "");
            $("#supply_management_user_all #holiday_group_master").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_user_all #holiday_group_master").attr("value", "");

            $(".group-type-show").hide();
            $(".group-sub-show").show();

            var aDatat = {
                type_id: $("#supply_management_user_all .data-defult").data("type")
            }

            $.ajax({url: "supply_management_user_inc_sub_group.php", 
                method: "POST",
                cache: false,
                data: aDatat,
                success: function(result){
                    $("#supply_management_user_all #supply_show_data").children().remove();
                    $("#supply_management_user_all #supply_show_data").append(result);
            }});
        });

        $("#supply_management_user_all #holiday_group_master").unbind("click");
        $("#supply_management_user_all #holiday_group_master").on("click", function(){
            $("#supply_management_user_all #holiday_group_master").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn input-group selected");
            $("#supply_management_user_all #holiday_group_master").attr("value", "selected");
            $("#supply_management_user_all #holiday_group_type").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_user_all #holiday_group_type").attr("value", "");
            $("#supply_management_user_all #holiday_supply_group").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn input-group");
            $("#supply_management_user_all #holiday_supply_group").attr("value", "");

            $(".group-type-show").hide();
            $(".group-sub-show").hide();

            //supply_master_inc_main.php
            $.ajax({url: "supply_master_inc_main.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_management_user_all #supply_show_data").children().remove();
                    $("#supply_management_user_all #supply_show_data").append(result);
            }});
        });

        // Autu load first page
        $("#supply_management_user_all #holiday_supply_group").trigger('click');

        // Click save
        $("#supply_management_user_all .add-value").unbind("click");
        $("#supply_management_user_all .add-value").on("click", function(){
            saveFormData_supply();
        });
        
        // Check Dup PK
        $("#supply_management_user_all #supply_group_type").unbind("blur");
        $("#supply_management_user_all #supply_group_type").blur(function(){
            $aData = {
                data_new: $(this).val()
            }

            $.ajax({url: "supply_management_ajax_group_type.php", 
                method: "POST",
                cache: false,
                data: $aData,
                success: function(result){
                    if(result == "duplicate"){
                        $("#supply_management_user_all #supply_group_type").val("");
                        $("#supply_management_user_all #supply_group_type").attr("style","background-color: red");
                        alert("ประเภทซ้ำ!");
                    }
                    else{
                        $("#supply_management_user_all #supply_group_type").attr("style","background-color: white");
                    }
            }});
        });

        // Open dialog create
        $("#supply_management_user_all .add-value-sub").unbind("click");
        $("#supply_management_user_all .add-value-sub").on("click", function(){
            var group_type = $("#supply_management_user_all .data-defult").data("type");
            var sUrl_appoint = "supply_management_user_inc_sub_group_create.php?type_id="+group_type;

            showDialog(sUrl_appoint, "Supply Group Main", "600", "500", "", function(sResult){
                var url_gen = "supply_management_user_inc_sub_group.php?type_id="+group_type;

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
        $("#supply_management_user_all .save-data").each(function(ix,objx){
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
                supply_group_type: $("#supply_management_user_all #supply_group_type").val(),
                dataid: lst_data_obj
            };

            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_supply);
            $("#supply_management_user_all .hide-old-date").hide();
            $("#supply_management_user_all .add-value").next("#supply_management_user_all .spinner").show();
            $("#supply_management_user_all .add-value").hide();
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

        $("#supply_management_user_all .add-value").next("#supply_management_user_all .spinner").hide();
        $("#supply_management_user_all .add-value").show();

        $.ajax({url: "supply_management_user_all.php", 
            method: "POST",
            cache: false,
            success: function(result){
                $("#supply_management_user_all #supply_show_data").children().remove();
                $("#supply_management_user_all #supply_show_data").append(result);
        }});
    }
</script>