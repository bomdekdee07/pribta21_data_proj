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

    $sJS_stock_type_bt = "";
    $query = "select supply_group_type, 
        supply_type_name, 
        supply_type_initial, 
        is_service 
    from i_stock_type;";

    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_type, $supply_type_name, $supply_type_initial, $is_service);
        while($stmt->fetch()){
            if(getPerm("STOCK", $supply_group_type, "view") == "1"){
                $sJS_stock_type_bt .=   '<div class="fl-fix holiday-ml-0 w-150 h-50 smallfont2">
                                            <button type="button" style="width: 150px; height: 40px;" id="supply_'.$supply_type_initial.'" class="btn-secondary type supply-type-btn button-supply" data-type="'.$supply_group_type.'"><b><span class="break-new-line fl-mid"><i class="fa fa-dot-circle" aria-hidden="true"></i> '.$supply_type_name.'</span></b></button>
                                        </div>';
            }
        }
    }
    $stmt->close();

    $option_group = "";
    $query = "SELECT supply_group_code, 
        supply_group_name,
        supply_group_type
    FROM i_stock_group 
    ORDER BY supply_group_name";

    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $stmt->bind_result($supply_group_code, $supply_group_name, $supply_group_type);
        while ($stmt->fetch()) {
            $option_group .= "<option data-supplyType=".$supply_group_type." value=".$supply_group_code." style='display: none'>".$supply_group_name."</option>";
        }
        // print_r($data_group_type);
    }
    $stmt->close();

    $mysqli->close();
?>

<div id="supply_request_main" class="fl-wrap-col holiday-mt-0">
    <span class="data-defult" data-ss=<? echo $sSID; ?> data-clinicid=<? echo $sClinicID; ?>></span>
    <div class="fl-wrap-col">
        <div class="fl-wrap-row h-40">
            <div class="fl-fix holiday-ml-0" style="min-width: 150px;">
                <button type="button" style="width: 150px; height: 40px;" id="supply_dashboard" class="btn btn-secondary type smallfont2 supply-type-btn"><b><span class="fl-mid"><i class="fa fa-list" aria-hidden="true"></i> Dashboard</span></b></button>
            </div>
            <div class="fl-fix holiday-ml-0" style="min-width: 150px;">
                <button type="button" style="width: 150px; height: 40px;" id="supply_request" class="btn btn-secondary sub-group smallfont2 supply-type-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> Request Items</b></button>
            </div>
            <div class="fl-fix holiday-ml-0" style="min-width: 150px;">
                <button type="button" style="width: 150px; height: 40px;" id="stock_manage" class="btn btn-secondary sub-group smallfont2 supply-type-btn"><b><i class="fa fa-cube" aria-hidden="true"></i> Stock Manage</b></button>
            </div>
            <? echo $sJS_stock_type_bt; ?>
            <div class="fl-fix holiday-ml-3" style="min-width: 150px; margin-top: 17px;">
                <span class="smallfont3"><u><b><? echo $sClinicID; ?></b></u></span>
            </div>
        </div>

        <!-- Head dashboard -->
        <div class="fl-wrap-row dashboard-show h-25">
            <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
                <div class="fl-wrap-row">
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 210px;">
                        <b class="holiday-ml-4"><span>รหัส</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2">
                        <b><span>ชื่อ</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 170px;">
                        <b><span>ล๊อต</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 170px;">
                        <b><span>จำนวน</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 200px;">
                        <b><span>วันหมดอายุ</span></b>
                    </div>
                </div>
            </div>
        </div>

        <!-- Head request item -->
        <div class="fl-wrap-row request-show h-30">
            <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
                <div class="fl-wrap-row">
                    <div class="fl-fix add-request supply-dashboard-text-head smallfont3 h-25 w-105">
                        <button class="btn smallfont2 supply-dashboard-btn-req-local input-group fl-mid" style="width: 105px;"><span><b>ขอสั่งซื้อภายนอก</b></span></button>
                    </div>
                    <div class="fl-fix add-request supply-dashboard-text-head smallfont3 holiday-ml-0 h-25 w-105">
                        <button class="btn smallfont2 supply-dashboard-btn-req-external input-group fl-mid" style="width: 105px;"><span><b>ขอสั่งซื้อภายใน</b></span></button>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 h-25" style="min-width: 160px;">
                        <b><span style="padding-left: 27px;">Request ID</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2 h-25">
                        <b><span>ชื่อ</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 h-25" style="min-width: 170px;">
                        <b><span>วันที่</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 h-25" style="min-width: 170px;">
                        <b><span>สถานะ</span></b>
                    </div>
                </div>
            </div>
        </div>

        <!-- Head request item -->
        <div class="fl-wrap-row h-30 supply-detail">
            <div class="fl-wrap-col h-30">
                <div class="fl-wrap-row holiday-box-head holiday-ml-0 holiday-mr-1 h-30">
                    <div class="fl-wrap-col holiday-text-head holiday-smallfont2 holiday-box-head w-130">
                        <div class="fl-wrap-row fl-mid">
                            <div class="fl-fix w-30 fl-mid lh-30">
                                <input type="checkbox" class="bigcheckbox" id="lost_qty" name="lost_qty" value="1" title="Show Empty Stock"/>
                            </div>
                            <div class="fl-fill">
                                <b><span>Code</span></b>
                            </div>
                        </div>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2 fl-mid" style="min-width: 150px;"> <!-- w-280--> 
                        <b><span>Name</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2 fl-mid" style="min-width: 200px;">
                        <b><span>Desc</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 fl-mid w-80 fw-b">
                        Cost
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 fl-mid w-120">
                        <b><span>Amount</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 fl-mid w-90">
                        <b><span>Unit</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 fl-mid w-90">
                        <b><span>Exprire Date</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2 fl-mid w-100">
                        <b><span>Last update</span></b>
                    </div>
                    <div class="fl-fill fl-mid">
                        <select id="ddGroupSupFilter" class="w-fill">
                                    <option value="">---All---</option>
                                        <? echo $option_group; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATA show -->
        <div class='fl-wrap-col fl-auto holiday-ml-0 holiday-mr-1' id="supply_show_data">
            <!-- Ajax reload data -->
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#supply_show_data .manage-stock").off("click");
        $("#supply_show_data").on("click",".manage-stock", function(){
            sSupCode = $(this).attr("data-supcode");
            sSupLot=$(this).attr("data-suplot");
            objSupRow=$(this).closest(".supply-detail-row");

            sUrl="supply_adjustment.php?supcode="+sSupCode+"&suplot="+sSupLot;
            showDialog(sUrl, sSupCode+"["+sSupLot+"] : Stock adjustment", "380", "500", "", function(sResult){
                if(sResult != "NA")
                $(objSupRow).find(".adjust-val").text(sResult);
            }, false, function(sResult){});
        });

        $("#supply_show_data .view-log").off("click");
        $("#supply_show_data").on("click", ".view-log", function(){
            var supply_code_s = $(this).data("supplycode");
            var name_s = $(this).data("supname");
            var lot_s = $(this).data("lot");
            var type_code = $("#supply_detail").data("typecode");
            var mode = $("#supply_detail").data("mode");
            var sUrl_appoint = "supply_detail_main.php?supply_code="+supply_code_s+"&stock_lot="+lot_s;
            var name_dialog = supply_code_s+" "+name_s+" "+lot_s;

            showDialog(sUrl_appoint, name_dialog, "600", "1200", "", function(sResult){
                var url_gen = "supply_type_detail_function.php?type_code="+type_code+"&mode="+mode+"&supply_code="+supply_code_s+"&stock_lot="+lot_s;
                // console.log(url_gen);
            }, false, function(sResult){});
        });

        $("#supply_show_data .manage-cost").off("click");
        $("#supply_show_data").on("click",".manage-cost", function(){
            sSupCode = $(this).attr("data-supcode");
            sSupLot=$(this).attr("data-suplot");
            objSupRow=$(this).closest(".supply-detail-row");

            sUrl="supply_cost_adjust.php?supcode="+sSupCode+"&suplot="+sSupLot;
            showDialog(sUrl, sSupCode+"["+sSupLot+"] : Cost adjustment", "380", "500", "", function(sResult){
                if(sResult != "NA")
                $(objSupRow).find(".manage-cost").text(sResult);
            }, false, function(sResult){});
        });

        // Filter onchange
        $("#supply_request_main #ddGroupSupFilter").off("change");
        $("#supply_request_main #ddGroupSupFilter").on("change", function(){
            var data_filter_select = "group-"+$(this).val().replace(/ /g, "");
            // console.log(data_filter_select);
            var sGroupType = $(this).val();
            var sChkNoPill = $("#lost_qty").is(":checked");

            var sIsAmt="[data-amt='0']";
            var sIsGroup="[data-group='"+sGroupType+"']";

            $(".supply-detail-row").hide();


            if(sGroupType!="") 
                $(".supply-detail-row"+sIsGroup).show();
            else  
                $(".supply-detail-row").show();
            
            if(sChkNoPill==false) 
                $(".supply-detail-row"+sIsAmt).hide();
        });

        // Dashbord
        $("#supply_request_main #supply_dashboard").off("click");
        $("#supply_request_main #supply_dashboard").on("click", function(){
            $("#supply_request_main #supply_dashboard").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn selected");
            $("#supply_request_main #supply_dashboard").attr("value", "selected");
            $("#supply_request_main #supply_request").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #supply_request").attr("value", "");
            $("#supply_request_main .button-supply").filter(".selected").attr("class", "btn-secondary sub-group smallfont2 supply-type-btn button-supply");
            $("#supply_request_main .button-supply").filter(".selected").attr("value", "");
            $("#supply_request_main #stock_manage").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #stock_manage").attr("value", "");

            $("#supply_request_main .dashboard-show").show();
            $("#supply_request_main .request-show").hide();
            $("#supply_request_main .supply-detail").hide();

            $.ajax({url: "supply_request_dashboard_function.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_request_main #supply_show_data").children().remove();
                    $("#supply_request_main #supply_show_data").append(result);
            }});
        });

        // Req Items
        $("#supply_request_main #supply_request").off("click");
        $("#supply_request_main #supply_request").on("click", function(){
            $("#supply_request_main #supply_request").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn selected");
            $("#supply_request_main #supply_request").attr("value", "selected");
            $("#supply_request_main #supply_dashboard").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #supply_dashboard").attr("value", "");
            $("#supply_request_main .button-supply").filter(".selected").attr("class", "btn-secondary sub-group smallfont2 supply-type-btn button-supply");
            $("#supply_request_main .button-supply").filter(".selected").attr("value", "");
            $("#supply_request_main #stock_manage").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #stock_manage").attr("value", "");

            $("#supply_request_main .request-show").show();
            $("#supply_request_main .dashboard-show").hide();
            $("#supply_request_main .supply-detail").hide();

            $.ajax({url: "supply_request_items_function.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_request_main #supply_show_data").children().remove();
                    $("#supply_request_main #supply_show_data").append(result);
            }});
        });

        // Supply type
        $("#supply_request_main .button-supply").off("click");
        $("#supply_request_main .button-supply").on("click", function(){
            $("#supply_request_main .button-supply").filter(".selected").attr("class", "btn-secondary sub-group smallfont2 supply-type-btn button-supply");
            $("#supply_request_main .button-supply").filter(".selected").attr("value", "");
            $(this).attr("class", "btn-secondary clinic smallfont2 supply-type-btn button-supply selected");
            $(this).attr("value", "selected");
            $("#supply_request_main #supply_dashboard").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #supply_dashboard").attr("value", "");
            $("#supply_request_main #supply_request").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #supply_request").attr("value", "");
            $("#supply_request_main #stock_manage").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #stock_manage").attr("value", "");

            $("#supply_request_main .dashboard-show").hide();
            $("#supply_request_main .request-show").hide();
            $("#supply_request_main .supply-detail").show();
            $("#supply_request_main #lost_qty").prop("checked", false);


            var type_code_s = $(this).data("type");
            var a_adata = {
                type_code: type_code_s,
                mode: "not_exp"
            }

            $.ajax({url: "supply_type_detail_function.php", 
                method: "POST",
                cache: false,
                data: a_adata,
                success: function(result){
                    $("#supply_request_main #supply_show_data").children().remove();
                    $("#supply_request_main #supply_show_data").append(result);
            }});
        });

        // Supply manage
        $("#supply_request_main #stock_manage").off("click");
        $("#supply_request_main #stock_manage").on("click", function(){
            $("#supply_request_main #supply_request").attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn");
            $("#supply_request_main #supply_request").attr("value", "");
            $("#supply_request_main #supply_dashboard").attr("class", "btn btn-secondary sub-group smallfont2 supply-type-btn");
            $("#supply_request_main #supply_dashboard").attr("value", "");
            $("#supply_request_main .button-supply").filter(".selected").attr("class", "btn-secondary sub-group smallfont2 supply-type-btn button-supply");
            $("#supply_request_main .button-supply").filter(".selected").attr("value", "");
            $(this).attr("class", "btn btn-secondary clinic smallfont2 supply-type-btn selected");
            $(this).attr("value", "selected");

            $("#supply_request_main .request-show").hide();
            $("#supply_request_main .dashboard-show").hide();
            $("#supply_request_main .supply-detail").hide();

            $.ajax({url: "supply_master_inc_main.php", 
                method: "POST",
                cache: false,
                success: function(result){
                    $("#supply_request_main #supply_show_data").children().remove();
                    $("#supply_request_main #supply_show_data").append(result);
            }});
        });

        // Tick all supply type
        $("#supply_request_main #lost_qty").off("change");
        $("#supply_request_main #lost_qty").on("change", function(){
            var group_code = $("#ddGroupSupFilter").val();
            var sIsGroup = "";
            $(".supply-detail-row[data-amt='0']").hide();
            if(group_code!=""){
                sIsGroup="[data-group='"+group_code+"']";
            }
            var check_noPill =  $(this).is(":checked");
            if(check_noPill)
                $("#supply_request_main .supply-detail-row"+sIsGroup+"[data-amt='0']").show();
            else
                $("#supply_request_main .supply-detail-row"+sIsGroup+"[data-amt='0']").hide();


        });

        // Autu load first page
        $("#supply_request_main #supply_dashboard").trigger('click');

        $("#supply_request_main .supply-dashboard-btn-req-external").off("click");
        $("#supply_request_main .supply-dashboard-btn-req-external").on("click", function(){
            // var sUrl_appoint = "supply_request_create.php?req_id=";
            var sUrl_appoint = "supply_req_inc_main.php?request_id=";

            showDialog(sUrl_appoint, "Supply Group Main", "90%", "1200", "", function(sResult){
                var url_gen = "supply_request_items_function.php";

                $("#supply_show_data").load(url_gen);
            }, false, function(sResult){});
        });

        $("#supply_request_main .supply-dashboard-btn-req-local").off("click");
        $("#supply_request_main .supply-dashboard-btn-req-local").on("click", function(){
            // var sUrl_appoint = "supply_request_create.php?req_id=";
            var sUrl_appoint = "purchase_req_inc_main.php?request_id=";

            showDialog(sUrl_appoint, "Supply Group Main", "90%", "1200", "", function(sResult){
                var url_gen = "supply_request_items_function.php";

                $("#supply_show_data").load(url_gen);
            }, false, function(sResult){});
        });
    });
</script>