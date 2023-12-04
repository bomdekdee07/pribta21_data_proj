<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $supply_code = getQS("supply_code");
    $stock_lot = getQS("stock_lot");
    $start_date = getQS("start_date");
    $stop_date = getQS("stop_date")!=""?getQS("stop_date"):"2999-12-01";

    if($stock_lot == ""){
        $data_lot_selected = array();
        $query = "select distinct supply_lot
        from i_stock_order
        where supply_code = ?;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $supply_code);

        if($stmt->execute()){
            $stmt->bind_result($supply_lot);
            while ($stmt->fetch()) {
                $data_lot_selected[$supply_lot]["lot_code"] = ltrim($supply_lot);
            }
            // print_r($data_lot_selected);
        }
        $stmt->close();
    }
    else{
        $data_lot_selected = array();
        $data_lot_selected[$stock_lot]["lot_code"] = ltrim($stock_lot);
    }
    $mysqli->close();

    $sJS_supply_detail = "";
    $sJS_supply_detail .=   '<div id="sub_supply_detail" class="fl-wrap-col" data-strdate="'.$start_date.'" data-stopdate="'.$stop_date.'">
                            <div class="fl-wrap-row h-45 fw-b">';
    $sJS_supply_detail .=       '<div class="fl-wrap-row fs-smaller holiday-box-head">
                                    <div class="fl-wrap-col w-100 fl-mid border">
                                        <div class="fl-fix">
                                            <span>Action</span>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-150 border">
                                        <div class="fl-wrap-row fl-mid">
                                            <div class="fl-fix">
                                                <span>LOT</span>
                                            </div>
                                        </div>
                                        <div class="fl-wrap-row fl-mid h-20">
                                            <div class="fl-fix">
                                                <select id="lot" name="lot" style="width: 145px;" class="input-group event-ajax-query-lot" data-supcode="'.$supply_code.'">
                                                    <option value="">Please Select.</option>';
    foreach($data_lot_selected as $key => $val){
        $sJS_supply_detail .= '<option value="'.$val["lot_code"].'">'.$val["lot_code"].'</option>';
    }
    $sJS_supply_detail .=                       '</select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-150 fl-mid border">
                                        <div class="fl-fix">
                                            <span>UID</span>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-260 fl-mid border">
                                        <div class="fl-fix">
                                            <span>Name</span>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-100 fl-mid border">
                                        <div class="fl-fix">
                                            <span>Amount</span>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-75 fl-mid border">
                                        <div class="fl-fix">
                                            <span>Unit</span>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-col w-250 border">
                                        <div class="fl-wrap-row w-250 fl-mid">
                                            <div class="fl-fix w-250">
                                                <span>Date Receive</span>
                                            </div>
                                        </div>
                                        <div class="fl-wrap-row w-250 h-20 fl-mid">
                                            <div class="fl-fix w-5"></div>
                                            <div class="fl-fill">
                                                <input type="text" id="st_date" name="st_date" class="input-group event-ajax-query-stdate datepick" style="height: 19px">
                                            </div>
                                            <div class="fl-fix w-5"></div>
                                            <div class="fl-fill">
                                                <input type="text" id="end_date" name="end_date" class="input-group event-ajax-query-enddate datepick" style="height: 19px">
                                            </div>
                                            <div class="fl-fix w-5"></div>
                                        </div>
                                    </div>
                                </div>';
    $sJS_supply_detail .=   '<div class="fl-wrap-col" style="min-width: 102px; max-width: 102px;"></div>';
    $sJS_supply_detail .=   '</div>';

    $sJS_supply_detail .=   '<div class="fl-wrap-col fl-auto" id="sub_supply_detail_ajax"></div>';
    $sJS_supply_detail .=   '</div>';

    echo $sJS_supply_detail;

    $js_defult_lot = "";
    if($stock_lot != ""){
        $js_defult_lot .= '$("#sub_supply_detail #lot").val("'.$stock_lot.'");';
        $js_defult_lot .= '$("#sub_supply_detail #lot").attr("disabled","disabled");';
    }
    if($start_date != ""){
        $js_defult_lot .= '$("#sub_supply_detail #st_date").val("'.$start_date.'");';
        echo "IN";
        if($stop_date != "")
        $js_defult_lot .= '$("#sub_supply_detail #end_date").val("'.$stop_date.'");';
    }
?>

<script>
    $(document).ready(function(){
        $("#sub_supply_detail #st_date").off("datepicker");
        $("#sub_supply_detail #st_date").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
        $("#sub_supply_detail #end_date").off("datepicker");
        $("#sub_supply_detail #end_date").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#sub_supply_detail .event-ajax-query-lot").off("change")
        $("#sub_supply_detail .event-ajax-query-lot").on("change", function() {
            var stock_lot_s = $(this).val();
            var supply_code_s = $(this).data("supcode");
            var st_date_s = $("#sub_supply_detail #st_date").val();
            var end_date_s = $("#sub_supply_detail #end_date").val();
            var a_data = {
                supply_code: supply_code_s,
                stock_lot: stock_lot_s,
                start_date: st_date_s,
                stop_date: end_date_s
            }
            // console.log(a_data);

            $.ajax({url: "supply_detail_function.php", 
                method: "POST",
                cache: false,
                data: a_data,
                success: function(result){
                    $("#sub_supply_detail #sub_supply_detail_ajax").children().remove();
                    $("#sub_supply_detail #sub_supply_detail_ajax").append(result);
            }});
        });

        $("#sub_supply_detail #st_date").off("change");
        $("#sub_supply_detail #st_date").on("change", function(){
            var st_date_s = $(this).val();
            var end_date_s = $("#sub_supply_detail #end_date").val();
            var stock_lot_s = $("#sub_supply_detail .event-ajax-query-lot").val();
            var supply_code_s = $("#sub_supply_detail .event-ajax-query-lot").data("supcode");
            var a_data = {
                supply_code: supply_code_s,
                stock_lot: stock_lot_s,
                start_date: st_date_s,
                stop_date: end_date_s
            }
            console.log(a_data);

            $.ajax({url: "supply_detail_function.php", 
                method: "POST",
                cache: false,
                data: a_data,
                success: function(result){
                    $("#sub_supply_detail #sub_supply_detail_ajax").children().remove();
                    $("#sub_supply_detail #sub_supply_detail_ajax").append(result);
            }});
        });

        $("#sub_supply_detail #end_date").off("change");
        $("#sub_supply_detail #end_date").on("change", function(){
            var st_date_s = $("#sub_supply_detail #st_date").val();
            var end_date_s = $(this).val();
            var stock_lot_s = $("#sub_supply_detail .event-ajax-query-lot").val();
            var supply_code_s = $("#sub_supply_detail .event-ajax-query-lot").data("supcode");
            var a_data = {
                supply_code: supply_code_s,
                stock_lot: stock_lot_s,
                start_date: st_date_s,
                stop_date: end_date_s
            }
            console.log(a_data);

            $.ajax({url: "supply_detail_function.php", 
                method: "POST",
                cache: false,
                data: a_data,
                success: function(result){
                    $("#sub_supply_detail #sub_supply_detail_ajax").children().remove();
                    $("#sub_supply_detail #sub_supply_detail_ajax").append(result);
            }});
        });

        <? echo $js_defult_lot; ?>
        $("#sub_supply_detail .event-ajax-query-lot").change();

        $("#sub_supply_detail .button-action-detail").off("click");
        $("#sub_supply_detail").on("click", ".button-action-detail", function(){
            var uid_s = $(this).data("uid");
            var coldate_s = $(this).data("coldate");
            var coltime_s = $(this).data("time");
            var req_id_s = $(this).data("reqid");
            var sup_code_s = $(this).data("supplycode");
            var sup_lot_s = $(this).data("lot");

            if(req_id_s == undefined){
                var sUrl_appoint = "supply_detail_specification.php?uid="+uid_s+"&collect_date="+coldate_s+"&collect_time="+coltime_s+"&req_id="+req_id_s+"&sup_code="+sup_code_s+"&sup_lot="+sup_lot_s;
            }
            else{
                var sUrl_appoint = "supply_detail_specification.php?req_id="+req_id_s;
            }
            // console.log(sUrl_appoint);

            showDialog(sUrl_appoint, "Supply detail", "300", "700", "", function(sResult){
            }, false, function(sResult){});
        });
    });
</script>