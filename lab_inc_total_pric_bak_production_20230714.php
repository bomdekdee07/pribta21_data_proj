<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("in_session.php");

    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = getQS("coltime");
    // echo $sUid."/".$sColDate;

    // Get staff confirm lab
    $staff_cf_lab = "";
    $bind_param = "sss";
    $array_val = array($sUid, $sColDate, $sColTime);

    $query = "SELECT staff_confirm from p_lab_order 
    where uid = ? 
    and collect_date = ?
    and collect_time = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $staff_cf_lab = $row["staff_confirm"];
        }
        // echo "IN:".$staff_cf_lab;
    }
    $stmt->close();
    $js_report_lab = "";
    if($staff_cf_lab == ""){
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").text("pending report Lab...");';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").css("background-color", "#9F9F9F");';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").css("color", "#FFFFFF");';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").prop("disabled", true);';
    }
    else{
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").text("Report Lab");';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").prop("disabled", false);';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").css("background-color", "#F2FB87");';
        $js_report_lab .= '$("[name=bt_rp_lab_doctor]").css("color", "#000000");';
    }
    

    $lab_total_data = array();
    $total_price_sum = 0;
    $supply_type_code_old = "";
    $supply_name_old = "";
    $supply_group_code_old = "";
    $total_price_sum_group_all = 0;
    $query = "SELECT st_type.supply_type_name as name_n_service,
        st_group.supply_group_name as name_is_service,
        st_master.supply_name,
        st_order.total_price,
        st_group.supply_group_code,
        st_group.supply_group_type,
        st_type.is_service
    from i_stock_order st_order
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    where st_order.uid = ?
    and st_order.collect_date = ?
    and st_order.collect_time = ?
    order by st_group.supply_group_type, st_group.supply_group_code;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $sUid, $sColDate, $sColTime);

    if($stmt->execute()){
        $stmt->bind_result($name_n_service, $name_is_service, $supply_name, $total_price, $supply_group_code, $supply_group_type, $is_service);
        while ($stmt->fetch()) {
            if($supply_type_code_old != $supply_group_type && $supply_group_type != 2){
                $total_price_sum = 0;
            }
            if($supply_name_old != $supply_name && $supply_group_type == 2){
                $total_price_sum = 0;
            }
            if($supply_group_code_old != $supply_group_code && $supply_group_type == 9){
                $total_price_sum = 0;
            }

            // ส่วนลด
            if($is_service == 1 && $supply_group_type != 2){
                $lab_total_data[$supply_group_type]["lab_name"] = $name_is_service;
                $total_price_sum = $total_price_sum+$total_price;
                $lab_total_data[$supply_group_type]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }
            // บริการ
            else if($supply_group_type == 2){
                $lab_total_data[$supply_group_type.$supply_name]["lab_name"] = $supply_name;
                $total_price_sum = $total_price_sum+$total_price;
                $lab_total_data[$supply_group_type.$supply_name]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }
            // ยา
            else if($supply_group_type == "1"){
                $lab_total_data[$supply_group_type]["lab_name"] = $name_n_service;
                $total_price_sum = $total_price_sum+$total_price;
                $lab_total_data[$supply_group_type]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }
            // Other supply
            else{
                $lab_total_data[$supply_group_type.$supply_group_code]["lab_name"] = $supply_name;//$name_n_service;
                $total_price_sum = $total_price_sum+$total_price;
                $lab_total_data[$supply_group_type.$supply_group_code]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }
            
            $supply_type_code_old = $supply_group_type;
            $supply_group_code_old = $supply_group_code;
            $supply_name_old = $supply_name;
        }
        // print_r($lab_total_data);
        // echo $total_price_sum_group_all;
    }
    $stmt->close();

    // $lab_saleprice_data = array("lab_name" => "", "total_price" => "");
    $lab_saleprice_data = array();
    $total_price_sum_lab_all = 0;
    $query = "select lab_test.lab_name,
        lab_order_lab_test.sale_price,
        lab_order_lab_test.lab_id
    from p_lab_order lab_order
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = lab_test.lab_group_id)
    where lab_order.lab_order_status != 'C'
    and lab_order.uid = ?
    and lab_order.collect_date = ?
    and lab_order.collect_time = ?
    and lab_group.ref_lab_id = ''
    order by lab_order_lab_test.lab_id, sale_price;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $sUid, $sColDate, $sColTime);

    if($stmt->execute()){
        $stmt->bind_result($lab_name, $sale_price, $lab_id);
        while ($stmt->fetch()) {
            // $lab_saleprice_data["lab_name"] = "Lab";
            // $total_price_sum_lab_all = $total_price_sum_lab_all+$sale_price;
            // $lab_saleprice_data["total_price"] = $total_price_sum_lab_all;

            $lab_saleprice_data[$lab_name]["lab_name"] = $lab_name;
            $lab_saleprice_data[$lab_name]["total_price"] = $sale_price;
            $total_price_sum_lab_all = $total_price_sum_lab_all+$sale_price;
        }
        // print_r($lab_saleprice_data);
    }

    $stmt->close();

    $lab_saleprice_data2 = array();
    $total_price_sum_lab_all2 = 0;
    $query = "select lab_test.lab_name,
        lab_group.lab_group_name,
        lab_order_lab_test.sale_price,
        lab_order_lab_test.lab_id
    from p_lab_order lab_order
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = lab_test.lab_group_id)
    where lab_order.lab_order_status != 'C'
    and lab_order.uid = ?
    and lab_order.collect_date = ?
    and lab_order.collect_time = ?
    and lab_group.ref_lab_id != ''
    order by lab_group_name, sale_price;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $sUid, $sColDate, $sColTime);

    if($stmt->execute()){
        $stmt->bind_result($lab_name, $lab_group_name, $sale_price, $lab_id);
        while ($stmt->fetch()) {
            $lab_saleprice_data2[$lab_group_name]["lab_name"] = $lab_group_name;
            $lab_saleprice_data2[$lab_group_name]["total_price"] = $sale_price;
            $total_price_sum_lab_all2 = $total_price_sum_lab_all2+$sale_price;
        }
        // print_r($lab_saleprice_data);
    }

    $stmt->close();
    $mysqli->close();

    $total_all_page = 0;
    $total_all_page = number_format(($total_price_sum_group_all+$total_price_sum_lab_all+$total_price_sum_lab_all2), 2, '.', ',');
    $SJS_group = "";
    $SJS_group .=   '<div class="fl-wrap-col" id="lab_total_price_main">
                        <div class="fl-wrap-row fw-b h-30 fs-smaller border" style="background-color: #D6B5E2;">
                            <div class="fl-fix wper-5 fl-mid"></div>
                            <div class="fl-fix wper-55 fl-mid-left">
                                <span>ชื่อ</span>
                            </div>
                            <div class="fl-fix wper-15 fl-mid-left fw-b">
                                <span>ราคา</span>
                            </div>
                            <div class="fl-fill fl-mid-right">
                                <button class="btn rp-lab font-s-1" name="bt_rp_lab_doctor" style="padding: 0px 6px 0px 6px; background-color: #F2FB87; font-weight : bold;">Report LAB</button>
                            </div>
                        </div>

                        <div class="fl-wrap-col fl-auto h-205">';

    foreach($lab_total_data as $key => $val){
        $SJS_group .=   '<div class="fl-wrap-row h-35 fs-smaller row-color">
                            <div class="fl-fix wper-5 fl-mid"></div>
                            <div class="fl-fix wper-55 fl-mid-left fw-b">
                                <span>'.$val["lab_name"].'</span>
                            </div>
                            <div class="fl-fix wper-15 fl-mid-left fw-b">
                                <span>'.$val["total_price"].'</span>
                            </div>
                            <div class="fl-fill fl-mid-left fw-b">
                                <span>บาท</span>
                            </div>
                        </div>';
    }
    // if($lab_saleprice_data["lab_name"] != ""){
    if(count($lab_saleprice_data) > 0){
        foreach($lab_saleprice_data as $key => $val){
            $SJS_group .=   '<div class="fl-wrap-row h-35 fs-smaller row-color">
                                <div class="fl-fix wper-5 fl-mid"></div>
                                <div class="fl-fix wper-55 fl-mid-left fw-b">
                                    <span>'.$val["lab_name"].'</span>
                                </div>
                                <div class="fl-fix wper-15 fl-mid-left fw-b">
                                    <span>'.$val["total_price"].'</span>
                                </div>
                                <div class="fl-fill fl-mid-left fw-b">
                                    <span>บาท</span>
                                </div>
                            </div>';
        }                            
    }                        
    if(count($lab_saleprice_data2) > 0){
        foreach($lab_saleprice_data2 as $key => $val){
            $SJS_group .=   '<div class="fl-wrap-row h-35 fs-smaller row-color">
                                <div class="fl-fix wper-5 fl-mid"></div>
                                <div class="fl-fix wper-55 fl-mid-left fw-b">
                                    <span>'.$val["lab_name"].'</span>
                                </div>
                                <div class="fl-fix wper-15 fl-mid-left fw-b">
                                    <span>'.$val["total_price"].'</span>
                                </div>
                                <div class="fl-fill fl-mid-left fw-b">
                                    <span>บาท</span>
                                </div>
                            </div>';
        }                            
    }                        
    $SJS_group .=       '</div>';

    $SJS_group .=       '<div class="fl-wrap-row fw-b h-30 smallfont3" style="background-color: #68C95C;">
                            <div class="fl-fix wper-5 fl-mid"></div>
                            <div class="fl-fix wper-55 fl-mid-left">
                                <span>ยอดรวม</span>
                            </div>
                            <div class="fl-fix wper-15 fl-mid-left fw-b">
                                <span style="color: #3742F0;">'.$total_all_page.'</span>
                            </div>
                            <div class="fl-fill fl-mid-left fw-b">
                                <span style="color: #000000;">บาท</span>
                            </div>
                        </div>';
    $SJS_group .=   '</div>';

    echo $SJS_group;
?>

<script>
    $(document).ready(function(){
        <? echo $js_report_lab;?>
    });
</script>