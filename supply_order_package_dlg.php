<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sSupCode = getQS("supply_code");
    $sUid=getQS("uid");
    $sColDate=getQS("coldate");
    $sColTime=getQS("coltime");
    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    // Query detail main
    $bind_param = "sss";
    $array_val = array($sUid, $sSupCode, $sClinicID);
    $data_group_all = array();
    $left_amt = 0;
    $old_supply_code = "";
    $sOptSale="";
    $ix=0; $sTemp=""; $sSalePrice=""; $sOptId="";
    $sOptSale_array = array();

    $query = "SELECT pack.package_stock_id,
        pack.supply_code,
        stlist.stock_lot,
        mast.supply_name,
        pack.package_stock_name,
        stlist.stock_amt,
        mast.supply_unit,
        stprice.sale_opt_id,
        sale_opt_name,
        sale_price,
        PI.sale_opt_id AS patient_sale_opt,
        mast.dose_note,
        mast.supply_desc,
        stgroup.supply_group_type
    from i_stock_package pack
    left join i_stock_master mast on (mast.supply_code = pack.supply_code)
    LEFT JOIN i_stock_group stgroup on (stgroup.supply_group_code = mast.supply_group_code)
    left join i_stock_list stlist on (stlist.supply_code = pack.supply_code)
    left join i_stock_price stprice on (stprice.supply_code = pack.supply_code)
    left join sale_option saleop on (saleop.sale_opt_id = stprice.sale_opt_id)
    LEFT JOIN patient_info PI ON (PI.sale_opt_id = saleop.sale_opt_id AND PI.uid = ?)
    where pack.package_stock_id = ?
    AND stlist.clinic_id = ?
    order by stgroup.supply_group_type, pack.supply_code, stlist.stock_amt, saleop.data_seq;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_group_all[$row["supply_code"]]["supply_code"] = $row["supply_code"];
            $data_group_all[$row["supply_code"]]["supply_name"] = $row["supply_name"];
            $data_group_all[$row["supply_code"]]["supply_unit"] = $row["supply_unit"];
            $data_group_all[$row["supply_code"]]["dose_note"] = $row["dose_note"];
            $data_group_all[$row["supply_code"]]["supply_desc"] = $row["supply_desc"];
            $data_group_all[$row["supply_code"]]["supply_group_type"] = $row["supply_group_type"];

            $sOptSale_array[$row["supply_code"]][$row["sale_opt_id"]]["sale_opt_id"] = $row['sale_opt_id'];
            $sOptSale_array[$row["supply_code"]][$row["sale_opt_id"]]["sale_opt_name"] = $row['sale_opt_name'];
            $sOptSale_array[$row["supply_code"]][$row["sale_opt_id"]]["sale_price"] = $row['sale_price'];
            $sOptSale_array[$row["supply_code"]][$row["sale_opt_id"]]["patient_sale_opt"] = $row['patient_sale_opt'];
        }
        // print_r($sOptSale_array);
    }
    $stmt->close();
    $mysqli->close();

    $html_stock_group_dt = "";
    $str_supply_code = "";
    $js_note = "";
    $order_loop = 0;
    $js_bind_inject = "";
    foreach($data_group_all as $key_supCode => $val){
        if($val["supply_group_type"] != "2"){
            $order_loop++;
        }
        else{
            $order_type_inject = 100;
            $order_type_inject = $order_type_inject*$order_loop;
            $js_bind_inject .= '$("#stock_package_main [name=order_amt_'.$val["supply_code"].']").val('.json_decode($order_type_inject).');';
        }

        $str_supply_code .= $val["supply_code"]."%";
        $html_stock_group_dt .= '<div class="fl-wrap-row h-25 font-s-2 row-color row-hover">
                                    <div class="fl-fix w-20"></div>
                                    <div class="fl-fill fl-mid-left">'.$val["supply_name"].'</div>
                                    <div class="fl-fix w-150 fl-mid-left">
                                        <input type="text" id="order_amt" name="order_amt_'.$val["supply_code"].'" data-supcode="'.$val["supply_code"].'" class="h-20 w-150 order-amt save-data" style="text-align:center;" value="0"> 
                                    </div>
                                    <div class="fl-fix w-10 fl-mid-left"></div>
                                    <div class="fl-fix w-30 fl-mid-left fw-b">
                                        <button name="bt_decrease" data-supcode="'.$val["supply_code"].'" data-typegroup="'.$val["supply_group_type"].'" class="btn btn-primary" style="padding: 0px 10px 0px 10px;">-</button>
                                    </div>
                                    <div class="fl-fix w-30 fl-mid-left">
                                        <button name="bt_increase" data-supcode="'.$val["supply_code"].'" data-typegroup="'.$val["supply_group_type"].'" class="btn btn-primary" style="padding: 0px 10px 0px 10px;">+</button>
                                    </div>
                                    <div class="fl-fix w-50 fl-mid-left"></div>
                                    <div class="fl-fix w-100 fl-mid-left">'.$val["supply_unit"].'</div>
                                    <div class="fl-fix w-110 fl-mid-right"><input type="text" name="stock_left_amt_'.$val["supply_code"].'" class="h-20 w-110" style="text-align:right; background-color: #E8E8E8;" readonly title="auto get every 3 second."></div>
                                    <div class="fl-fix fl-mid-right" style="min-width: 365px; max-width: 365px;">
                                        <SELECT id="sale_opt_id" name="ddlSaleOptId_'.$val["supply_code"].'" data-supcode="'.$val["supply_code"].'" class="saveinput h-20 ddlSaleOptId save-data" style="min-width: 355px; max-width: 355px;" data-keyid="sale_opt_id" data-saleprice="">';
        foreach($sOptSale_array[$key_supCode] as $key_sale_id => $row){
            $html_stock_group_dt .= "<option value='".$row['sale_opt_id']."' data-saleprice='".$row['sale_price']."'>".$row['sale_opt_name']." ".$row['sale_price']."บาท</option>";
            if($ix==0){
                $sSalePrice = $row['sale_price']; 
                $sOptId = $row['sale_opt_id'];
            }else if($row['sale_opt_id'] == $row["patient_sale_opt"]) {
                $sSalePrice = $row['sale_price'];
                $sOptId = $row['sale_opt_id'];
            }
            $ix++;
    
            $sTemp .= "setKeyVal($(\"#dlgSOID\"),'sale_price',".json_encode($sSalePrice).",false);
            setKeyVal($(\"#dlgSOID\"),'sale_opt_id',".json_encode($sOptId).",false);";
        }
        $html_stock_group_dt .=         '</SELECT>
                                    </div>
                                    <div class="fl-fix w-160 fl-mid-right">
                                        <input type="text" name="total_cost_'.$val["supply_code"].'" data-supcode="'.$val["supply_code"].'" class="h-20 w-150" style="text-align:right; background-color: #E8E8E8;" readonly value="0.00">
                                    </div>
                                    <div class="fl-fix w-100 fl-mid font-s-1">
                                        <input type"text" name="dose_note_'.$val["supply_code"].'" style="display:none;">
                                        <input type"text" name="supply_desc_'.$val["supply_code"].'" style="display:none;">
                                    </div>
                                    <div class="fl-fix w-20"></div>
                                </div>';

        $js_note .= '$("#stock_package_main [name=dose_note_'.$val["supply_code"].']").val('.json_encode($val["dose_note"]).');';
        $js_note .= '$("#stock_package_main [name=supply_desc_'.$val["supply_code"].']").val('.json_encode($val["supply_desc"]).');';
    }

    $html_supcode = "";
    $html_supcode .= '$("#stock_package_main").attr("data-supcode", '.json_encode(substr($str_supply_code, 0, -1)).');';
    $html_supcode .= '$("#stock_package_main").attr("data-uid", '.json_encode($sUid).');';
    $html_supcode .= '$("#stock_package_main").attr("data-coldate", '.json_encode($sColDate).');';
    $html_supcode .= '$("#stock_package_main").attr("data-coltime", '.json_encode($sColTime).');';
?>

<div class="fl-wrap-col" id="stock_package_main" data-supcode = "" data-uid = "" data-coldate = "" data-coltime = "">
    <div class="fl-wrap-row h-20"></div>
    <div class="fl-wrap-row h-25 fw-b font-s-3">
        <div class="fl-fix w-20"></div>
        <div class="fl-fill fl-mid-left border-bt">Item</div>
        <div class="fl-fix fl-mid-left border-bt" style="min-width: 270px; max-width: 270px;">Order</div>
        <div class="fl-fix fl-mid-left border-bt" style="min-width: 100px; max-width: 100px;">Unit</div>
        <div class="fl-fix w-110 fl-mid-right border-bt">AMT. Left</div>
        <div class="fl-fix fl-mid-right border-bt" style="min-width: 365px; max-width: 365px;">Cost</div>
        <div class="fl-fix w-160 fl-mid-right border-bt">Total</div>
        <div class="fl-fix w-100 fl-mid border-bt"></div>
        <div class="fl-fix w-20"></div>
    </div>
    <div class="fl-wrap-row h-5"></div>

    <? echo $html_stock_group_dt; ?>
    <div class="fl-fill"></div>
    <div class="fl-wrap-col h-30">
        <div class="fl-wrap-row h-30">
            <div class="fl-fill"></div>
            <div class="fl-fix w-150 fl-mid-left font-s-2 fw-b">
                <button name="bt_confirm" class="btn btn-success" style="padding: 1px 50px 1px 50px;"><i class="fa fa-database" aria-hidden="true"> ยืนยัน</i></button>
            </div>
            <div class="fl-fix w-10 fl-mid-left font-s-2 fw-b"></div>
            <div class="fl-fix w-100 fl-mid-left font-s-2 fw-b">
                <button name="bt_cancel" class="btn btn-danger" style="padding: 1px 25px 1px 25px;"><i class="fa fa-times" aria-hidden="true"> ยกเลิก</i></button>
            </div>
            <div class="fl-fill"></div>
        </div>
	</div>
</div>

<script>
    $(document).ready(function(){
        <? echo $sTemp; ?>
        <? echo $html_supcode; ?>
        <? echo $js_note; ?>
        <? echo $js_bind_inject; ?>

        // reload data auto 7sec.
        get_amtLeft();
        var get_amtleft_interval = setInterval(function(){
            get_amtLeft();
        }, 7000);

        // bt save
        $("#stock_package_main [name=bt_confirm]").off("click");
        $("#stock_package_main [name=bt_confirm]").on("click", function(){
            if(confirm("คุณต้องการบันทึกหรือไม่?")){
                var obj = [];
                $("#stock_package_main .save-data").each(function(){
                    // console.log($(this).attr("data-supcode"));
                    var supCode = $(this).attr("data-supcode");
                    var idName = $(this).attr("id");
                    var val = $(this).val();
                    var sUid = $("#stock_package_main").attr("data-uid");
                    var sColdate = $("#stock_package_main").attr("data-coldate");
                    var sColtime = $("#stock_package_main").attr("data-coltime");
                    var objthis = $(this);

                    var rowGroup = {
                        sup_code: supCode,
                        id: idName,
                        value: val,
                        uid: sUid,
                        coldate: sColdate,
                        coltime: sColtime
                    };
                    
                    obj.push(rowGroup);
                });
                var jsonString = JSON.stringify(obj);
                // console.log("in:"+jsonString);

                $.ajax({
                    url: "supply_order_package_ins_ajax.php",
                    cache: false,
                    type: "POST",
                    data: {data: jsonString},
                    success: function(sResult){
                        // console.log(sResult);
                        if(sResult == "1"){
                            closeDlg($("#stock_package_main [name=bt_confirm]"), "1");
                        }
                    },
                    async: false
                });
            }
        });

        // increase decrease order
        $("#stock_package_main [name=bt_increase]").off("click");
        $("#stock_package_main [name=bt_increase]").on("click", function(){
            var str_supcode = $(this).attr("data-supcode");
            var get_amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
            var get_amtleft_check = $("#stock_package_main [name=stock_left_amt_"+str_supcode+"]").val();

            if(parseInt(get_amt_order) <= parseInt(get_amtleft_check)){
                var total_cost = 0;
                var type_group = $(this).attr("data-typegroup");
                var sale_price = $("#stock_package_main [name=ddlSaleOptId_"+str_supcode+"]").attr("data-saleprice");

                if(type_group != "2"){
                    $("#stock_package_main [name=order_amt_"+str_supcode+"]").val(parseInt(get_amt_order)+1);
                    var amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
                    total_cost = parseFloat(sale_price).toFixed(2)*parseInt(amt_order).toFixed(2);
                }
                else{
                    $("#stock_package_main [name=order_amt_"+str_supcode+"]").val(parseInt(get_amt_order)+100);
                    var amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
                    total_cost = parseInt(amt_order).toFixed(2);
                }
                
                $("#stock_package_main [name=total_cost_"+str_supcode+"]").val(parseInt(total_cost).toFixed(2));
            }
            else{
                alert("ไม่สามารถเพิ่มมากกว่าที่มีในสต๊อก");
            }
        });

        $("#stock_package_main [name=bt_decrease]").off("click");
        $("#stock_package_main [name=bt_decrease]").on("click", function(){
            var str_supcode = $(this).attr("data-supcode");
            var get_amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();

            if(get_amt_order > 0){
                var total_cost = 0;
                var type_group = $(this).attr("data-typegroup");
                var sale_price = $("#stock_package_main [name=ddlSaleOptId_"+str_supcode+"]").attr("data-saleprice");

                if(type_group != "2"){
                    $("#stock_package_main [name=order_amt_"+str_supcode+"]").val(parseInt(get_amt_order)-1);
                    var amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
                    total_cost = parseFloat(sale_price).toFixed(2)*parseInt(amt_order).toFixed(2);
                }
                else{
                    $("#stock_package_main [name=order_amt_"+str_supcode+"]").val(parseInt(get_amt_order)-100);
                    var amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
                    total_cost = parseInt(amt_order).toFixed(2);
                }
                
                $("#stock_package_main [name=total_cost_"+str_supcode+"]").val(parseInt(total_cost).toFixed(2));
            }
        });

        // key order value
        $("#stock_package_main .order-amt").off("keyup");
        $("#stock_package_main .order-amt").on("keyup", function(){
            
        });

        //Check key input
        $("#stock_package_main .order-amt").off("keyup");
        $("#stock_package_main .order-amt").on("keyup", function(){
            var str_supcode = $(this).attr("data-supcode");
            var get_amt_order = $("#stock_package_main [name=order_amt_"+str_supcode+"]").val();
            var get_amtleft_check = $("#stock_package_main [name=stock_left_amt_"+str_supcode+"]").val();
            // console.log(get_amt_order+"/"+get_amtleft_check);

            if(parseInt(get_amt_order) > parseInt(get_amtleft_check)){
                alert("ไม่สามารถเพิ่มมากกว่าที่มีในสต๊อก");
                $("#stock_package_main [name=order_amt_"+str_supcode+"]").val(0);
            }
        });

        // BT close cancel
        $("#stock_package_main [name=bt_cancel]").off("click");
        $("#stock_package_main [name=bt_cancel]").on("click", function(){
            var dlg_this = $(this);

            clearInterval(get_amtleft_interval);
            close_dlg(dlg_this);
        });

        // BT Change sale opt
        $("#stock_package_main .ddlSaleOptId").off("change");
        $("#stock_package_main .ddlSaleOptId").on("change", function(){
            var sSupply_cdoe = $("#stock_package_main").attr("data-supcode");
            sSupply_cdoe = sSupply_cdoe.split("%");
            
            $.each(sSupply_cdoe, function(key, val){
                var get_sale_opt = $("#stock_package_main [name=ddlSaleOptId_"+val+"]").val();
                var aData = {
                    supply_code: val,
                    sale_opt: get_sale_opt
                };
                // console.log(aData);
                
                $.ajax({
                    url: "supply_order_package_sale_price_ajax.php",
                    cache: false,
                    method: "POST",
                    data: aData,
                    success: function(sResult){
                        var result_split = jQuery.parseJSON(sResult);
                        $.each(result_split, function(key, val){
                            $("#stock_package_main [name=ddlSaleOptId_"+key+"]").attr("data-saleprice", val);

                            var sale_price = val;
                            var amt_order = $("#stock_package_main [name=order_amt_"+key+"]").val();
                            var total_cost = 0;

                            total_cost = parseFloat(sale_price).toFixed(2)*parseInt(amt_order).toFixed(2);
                            // console.log(parseInt(sale_price).toFixed(2)+"/"+parseInt(amt_order).toFixed(2)+":"+total_cost);
                            $("#stock_package_main [name=total_cost_"+key+"]").val(total_cost.toFixed(2));
                        });
                    }
                });
            });
        });

        $("#stock_package_main .ddlSaleOptId").change();
    });

    // get Amt left
    function get_amtLeft(){
        var sSupply_cdoe = $("#stock_package_main").attr("data-supcode");
        aData = {
            supply_code: sSupply_cdoe
        };

        $.ajax({
            url: "supply_order_package_left_ajax.php",
            cache: false,
            method: "POST",
            data: aData,
            success: function(sResult){
                var con_result = jQuery.parseJSON(sResult);
                $.each(con_result, function(key, val){
                    $("[name=stock_left_amt_"+key+"]").val(val);
                })
            }
        });
    }

    // Close DLG
    function close_dlg(objthis){
        closeDlg(objthis, "0");
    }
</script>