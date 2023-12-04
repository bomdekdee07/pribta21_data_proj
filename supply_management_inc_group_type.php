<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    
    $data_group_type = array();
    $query = "select supply_group_type,
    supply_type_name,
    supply_type_name_en,
    supply_type_initial,
    is_service
    from i_stock_type order by supply_group_type;";

    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_type, $supply_type_name, $supply_type_name_en, $supply_type_initial, $is_service);
        while ($stmt->fetch()) {
            $data_group_type[$supply_group_type]["type"] = $supply_group_type;
            $data_group_type[$supply_group_type]["name"] = $supply_type_name;
            $data_group_type[$supply_group_type]["name_en"] = $supply_type_name_en;
            $data_group_type[$supply_group_type]["code"] = $supply_type_initial;
            $data_group_type[$supply_group_type]["service"] = $is_service;
        }
        // print_r($data_doc_detail);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    if(count($data_group_type) > 0){
        foreach($data_group_type as $key => $value){
            $sJS .= '<div id="detail_group_type" class="fl-wrap-row row-color h-25" style="margin-left: 2px; margin-top: 3px">';
            $sJS .=     '<div class="fl-fix holiday-smallfont2 holiday-ml-2" style="min-width: 40px;">';
            $sJS .=         '<a href="#"><b><i class="fa fa-edit edit-click group-type-efit" aria-hidden="true" data-type="'.$value["type"].'" data-name="'.$value["name"].'" data-code="'.$value["code"].'" data-service="'.$value["service"].'"></i></b></a><a href="#"><b><i class="fa fa-plus-circle add-sub-group-main" data-type="'.$value["type"].'" aria-hidden="true"></i></b></a>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 100px;">';
            $sJS .=         '<span>'.$value["type"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 holiday-ml-2">';
            $sJS .=         '<span>'.$value["name"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 holiday-ml-2">
                            <span>'.$value["name_en"].'</span>
                        </div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 150px;">';
            $sJS .=         '<span>'.$value["code"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 230px;">';
            $sJS .=         '<span>'.($value["service"]!=1?'<input type="checkbox" value="" class="input-group" disabled="disabled">':'<input type="checkbox" value="" class="input-group" style="color:#2777bd;" checked="checked" onclick="return false;" onkeydown="return false;">').'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 150px;">';
            $sJS .=         '<a href="#"><b style="margin-right: 135px;"><i class="fa fa-times supply-group-delete" style="color:red;" aria-hidden="true" data-type="'.$value["type"].'" data-name="'.$value["name"].'" data-code="'.$value["code"].'" data-service="'.$value["service"].'"></i></b></a>';
            $sJS .=     '</div>';
            $sJS .= '</div>';
        }
    }

    echo $sJS;
?>

<script>
    $(document).ready(function(){
        $("#detail_group_type .group-type-efit").unbind("click");
        $("#detail_group_type .group-type-efit").on("click", function(){
            var type_pk = $(this).data("type");
            var name = $(this).data("name");
            var code = $(this).data("code");
            var is_servise = $(this).data("service");

            $("#supply_management_main #supply_group_type").val(type_pk);
            $("#supply_management_main #supply_group_type").data("odata", type_pk);
            $("#supply_management_main #supply_group_type").prop("readonly", true);
            $("#supply_management_main #supply_type_name").val(name);
            $("#supply_management_main #supply_type_name").data("odata", name);
            $("#supply_management_main #supply_type_initial").val(code);
            $("#supply_management_main #supply_type_initial").data("odata", code);
            (is_servise != 1? $("#supply_management_main #is_service").prop('checked', false):$("#supply_management_main #is_service").prop('checked', true));
            $("#supply_management_main #is_service").val(is_servise);
            $("#supply_management_main #is_service").data("odata", is_servise);
        });

        $("#supply_management_main .clear-value").unbind("click");
        $("#supply_management_main .clear-value").on("click", function(){
            $("#supply_management_main #supply_group_type").val("");
            $("#supply_management_main #supply_group_type").data("odata", "");
            $("#supply_management_main #supply_group_type").prop("readonly", false);
            $("#supply_management_main #supply_type_name").val("");
            $("#supply_management_main #supply_type_name").data("odata", "");
            $("#supply_management_main #supply_type_initial").val("");
            $("#supply_management_main #supply_type_initial").data("odata", "");
            $("#supply_management_main #is_service").prop('checked', false);
            $("#supply_management_main #is_service").val("");
            $("#supply_management_main #is_service").data("odata", "");
        });

        $("#detail_group_type .supply-group-delete").unbind("click");
        $("#detail_group_type .supply-group-delete").on("click", function(){
            if (confirm('คุณแน่ใจหรือไม่?')) {
                var group_type = $(this).data("type");
                var aData = {
                    app_mode: "deleted_supply_group_type",
                    supply_group_type: group_type
                };

                $.ajax({url: "supply_management_ajax_deleted_group_type.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        if(result > 0){
                            alert("ไม่สามารถลบข้อมูลได้ มีข้อมูลที่ถูกใช้งานอยู่ในปัจจุบัน!");
                        }else{
                            callAjax("doctor_db_form_update.php", aData, function(){
                                $.ajax({url: "supply_management_inc_group_type.php", 
                                    method: "POST",
                                    cache: false,
                                    success: function(result){
                                        $("#supply_management_main #supply_show_data").children().remove();
                                        $("#supply_management_main #supply_show_data").append(result);
                                }});
                            });
                        }
                }});
            }
        });

        $("#detail_group_type .add-sub-group-main").unbind("click");
        $("#detail_group_type .add-sub-group-main").on("click", function(){
            var group_type = $(this).data("type");
            var sUrl_appoint = "supply_management_inc_sub_group_create.php?group_type="+group_type+"&group_code=";

            showDialog(sUrl_appoint, "Supply Group Main", "600", "500", "", function(sResult){
                var url_gen = "supply_management_inc_group_type.php";

                $("#detail_sub_group").parent().load(url_gen);
            }, false, function(sResult){});
        });
    });
</script>