<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));

    $data_name_type = array();
    $query = "select supply_group_type, supply_type_name from i_stock_type
    order by supply_group_type;";

    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_type, $supply_type_name);
        while ($stmt->fetch()) {
            $data_name_type[$supply_group_type]["name"] = $supply_type_name;
        }
        // print_r($data_name_type);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    $sJS_head = "";
    $sJS_bottom = "";
    $sJS_bottom_bt = "";

    $sJS_head .= '
                <div id="supply_management_user_main" class="fl-wrap-col holiday-mt-0">
                <span class="data-defult" data-ss="'.$sSID.'" data-clinicid="'.$sClinicID.'" ></span>
                <div class="fl-fill">
                    <div class="fl-wrap-row">
                        <div class="fl-fill holiday-ml-0 stock-user-title">
                            <span id="stock_user_type" class="smallfont3 holiday-ml-1 input-group"><b><i class="fa fa-database" aria-hidden="true"></i> Stock Management</b></span>
                        </div>
                    </div>
                    
                    <div class="fl-wrap-row">
                        <div class="fl-fill holiday-box-serch holiday-ml-0 holiday-mr-1">
                            <div class="fl-wrap-row">
                                <div class="fl-fix holiday-ml-0" style="min-height: 300px; min-width: 1024px;">';

    $sJS .= '                       <div class="fl-wrap-row">';
    $sJS .= '                           <div class="fl-fill holiday-ml-0">';
    foreach(getPerm("STOCK") as $key => $val){
        $sJS .= '                           <button type="button" class="btn holiday-ml-1 user-type smallfont2 user-type-btn" data-typeid="'.$key.'"><b>'.$data_name_type[$key]["name"].'</b></button>';
    }
    $sJS .= '                           </div>';
    $sJS .= '                       </div>';

    $sJS_bottom .= '            </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fl-wrap-row holiday-mt-1">
                        <div class="fl-fill holiday-box-serch holiday-ml-0 holiday-mr-1" id="supply_user_sub">';
    $sJS_bottom_bt .= ' </div>
                    </div>
                </div>
                </div>';
    
    echo $sJS_head.$sJS.$sJS_bottom;
    echo $sJS_bottom_bt;
?>                  

<script>
    $(document).ready(function(){
        $("#supply_management_user_main .user-type").unbind("click");
        $("#supply_management_user_main .user-type").on("click", function(){
            $("#supply_management_user_main .user-type").attr("class", "btn holiday-ml-1 user-type smallfont2 user-type-btn");
            $(this).attr("class", "btn holiday-ml-1 user-type smallfont2 user-type-btn selected");

            var type_id_s = $(this).data("typeid");
            
            var aData = {
                type_id: type_id_s
            };

            $.ajax({url: "supply_management_user_all.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#supply_management_user_main #supply_user_sub").children().remove();
                    $("#supply_management_user_main #supply_user_sub").append(result);
            }});
        });

        if($('#supply_management_user_main .user-type').data("typeid") != ""){
            $('#supply_management_user_main .user-type')[0].click()
        } 
    });
</script>