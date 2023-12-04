<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }

    $type_id = isset($_POST["type_id"])?$_POST["type_id"] : getQS("type_id");

    $data_sub_group = array();
    $query = "select supply_group_type,
        supply_group_code,
        supply_group_name,
        supply_group_desc,
        supply_group_initial
    from i_stock_group
    where supply_group_type = ?
    order by supply_group_type, supply_group_code;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $type_id);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_type, $supply_group_code, $supply_group_name, $supply_group_desc, $supply_group_initial);
        while ($stmt->fetch()) {
            $data_sub_group[$supply_group_code]["group_type"] = $supply_group_type;
            $data_sub_group[$supply_group_code]["group_code"] = $supply_group_code;
            $data_sub_group[$supply_group_code]["name"] = $supply_group_name;
            $data_sub_group[$supply_group_code]["desc"] = $supply_group_desc;
            $data_sub_group[$supply_group_code]["code"] = $supply_group_initial;
        }
        // print_r($data_sub_group);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    if(count($data_sub_group) > 0){
        foreach($data_sub_group as $key => $value){
            $sJS .= '<div id="detail_sub_group" class="fl-wrap-row row-color" style="margin-left: 2px; margin-top: 3px">';
            $sJS .=     '<div class="fl-fix holiday-smallfont2 holiday-ml-2" style="min-width: 40px;">'; //sub-group-edit
            if(getPerm("STOCK", $type_id, "view") == "1"){
                $sJS .=         '<a href="#"><b><i class="fa fa-edit edit-click sub-group-edit" aria-hidden="true" data-type="'.$value["group_type"].'" data-name="'.$value["name"].'" data-code="'.$value["code"].'" data-groupcode="'.$value["group_code"].'" data-desc="'.$value["desc"].'"></i></b></a>';
            }
            else{
                $sJS .=         '<b><i class="fa fa-edit cannot-view" aria-hidden="true"></i></b>';
            }
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 116px;">';
            $sJS .=         '<span>'.$value["group_type"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["group_code"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail-left holiday-smallfont2" style="min-width: 270px;">';
            $sJS .=         '<span style="margin-left: 10px">'.$value["name"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2">';
            $sJS .=         '<span style="margin-left: 10px">'.$value["desc"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail-left holiday-smallfont2" style="min-width: 200px;">';
            $sJS .=         '<span style="margin-left: 87px">'.$value["code"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 150px;">';
            if(getPerm("STOCK", $type_id, "delete") == "1"){
                $sJS .=         '<a href="#"><b style="margin-right: 135px;"><i class="fa fa-times supply-group-delete" style="color:red;" aria-hidden="true" data-type="'.$value["group_type"].'" data-name="'.$value["name"].'" data-code="'.$value["code"].'" data-groupcode="'.$value["group_code"].'" data-desc="'.$value["desc"].'"></i></b></a>';
            }
            else{
                $sJS .=         '<b style="margin-right: 135px;"><i class="fa fa-times cannot-delete" style="color:black;" aria-hidden="true"></i></b>';
            }
            $sJS .=     '</div>';
            $sJS .= '</div>';
        }
    }

    echo $sJS;
?>

<script>
    $(document).ready(function(){
        $("#detail_sub_group .sub-group-edit").unbind("click");
        $("#detail_sub_group .sub-group-edit").on("click", function(){
            var group_type = $(this).data("type");
            var group_code = $(this).data("groupcode");
            var sUrl_appoint = "supply_management_user_inc_sub_group_create.php?group_type="+group_type+"&group_code="+group_code+"&type_id="+group_type;

            showDialog(sUrl_appoint, "Supply Group Main", "600", "500", "", function(sResult){
                var url_gen = "supply_management_user_inc_sub_group.php?type_id="+group_type;

                $("#detail_sub_group").parent().load(url_gen);
            }, false, function(sResult){});
        });

        $("#detail_sub_group .supply-group-delete").unbind("click");
        $("#detail_sub_group .supply-group-delete").on("click", function(){
            if (confirm('คุณแน่ใจหรือไม่?')) {
                var group_type = $(this).data("type");
                var group_code = $(this).data("groupcode");
                var aData = {
                    app_mode: "deleted_sub_group",
                    supply_group_type: group_type,
                    supply_group_code: group_code
                };

                $.ajax({url: "supply_management_ajax_deleted_sub_group.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        if(result > 0){
                            alert("ไม่สามารถลบข้อมูลได้ มีข้อมูลที่ถูกใช้งานอยู่ในปัจจุบัน!");
                        }else{
                            callAjax("doctor_db_form_update.php", aData, function(){
                                $.ajax({url: "supply_management_inc_sub_group.php", 
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

        $("#detail_sub_group .cannot-view").unbind("ckick");
        $("#detail_sub_group .cannot-view").on("click", function(){
            alert("ขออภัย ไม่มีสิทธิ์ในการดูข้อมูล");
        });

        $("#detail_sub_group .cannot-delete").unbind("ckick");
        $("#detail_sub_group .cannot-delete").on("click", function(){
            alert("ขออภัย ไม่มีสิทธิ์ในการลบข้อมูล");
        });
    });
</script>