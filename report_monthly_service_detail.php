<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $start_date = getQS("start_date");
    $end_date = getQS("end_date");
    $sex_code = getQS("sex_id");
    $clinic_id = getSS("clinic_id");

    $st_sex_array = array("1" => "ชาย", "2" => "หญิง", "3" => "มีเพศสรีระทั้งชายและหญิง", "" => "ไม่ได้กรอกเพศไว้");

    $bind_param = "sss";
    $array_val = array($clinic_id, $start_date, $end_date);
    $data_servcie_loop = array();

    $query = "SELECT st_order.collect_date,
        st_order.supply_code,
        st_master.supply_name,
        st_master.supply_name_en,
        st_type.supply_type_name,
        st_type.supply_type_name_en,
        st_type.is_service,
        st_type.supply_group_type,
        st_order.total_price,
        patient.sex -- 1 ชาย, 2 หญิง, 3 มีทั้งสอง
    from i_stock_order st_order
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    left join patient_info patient on(patient.uid = st_order.uid)
    where st_order.clinic_id = ?
    and st_order.collect_date >= ? and st_order.collect_date <= ? ";

    if($sex_code != ""){
        $query .= "and patient.sex = ?";
        $bind_param .= "s";
        $array_val[] = $sex_code;
    }

    $query .= " order by st_type.supply_group_type, patient.sex, st_order.supply_code;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $old_supply_code = "";
    $old_sex = "";
    $old_supply_type = "";
    $total_price_sum = 0;
    $total_case_sum = 0;
    if($stmt->execute()){
        $stmt->bind_result($collect_date, $supply_code, $supply_name, $supply_name_en, $supply_type_name, $supply_type_name_en, $is_service, $supply_group_type, $total_price, $sex);
        while($stmt->fetch()){
            if($is_service == 1){
                $data_servcie_loop[$supply_code][$sex]["date"] = $collect_date;
                $data_servcie_loop[$supply_code][$sex]["code"] = $supply_code;
                $data_servcie_loop[$supply_code][$sex]["name_th"] = $supply_name;
                $data_servcie_loop[$supply_code][$sex]["name_en"] = $supply_name_en;
                $data_servcie_loop[$supply_code][$sex]["sex"] = $st_sex_array[$sex];

                if($supply_code != $old_supply_code && $sex != $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                if($supply_code == $old_supply_code && $sex != $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                if($supply_code != $old_supply_code && $sex == $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                $total_price_sum += $total_price;
                $data_servcie_loop[$supply_code][$sex]["price"] = number_format($total_price_sum, 2, ".", ",");

                $total_case_sum += 1;
                $data_servcie_loop[$supply_code][$sex]["total_case"] = $total_case_sum;
            }
            else{
                $data_servcie_loop[$supply_group_type][$sex]["date"] = $collect_date;
                $data_servcie_loop[$supply_group_type][$sex]["code"] = "";
                $data_servcie_loop[$supply_group_type][$sex]["name_th"] = $supply_type_name;
                $data_servcie_loop[$supply_group_type][$sex]["name_en"] = $supply_type_name_en;
                $data_servcie_loop[$supply_group_type][$sex]["sex"] = $st_sex_array[$sex];

                if($supply_group_type != $old_supply_type && $sex != $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                if($supply_group_type == $old_supply_type && $sex != $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                if($supply_group_type != $old_supply_type && $sex == $old_sex){
                    $total_price_sum = 0;
                    $total_case_sum = 0;
                }
                $total_price_sum += $total_price;
                $data_servcie_loop[$supply_group_type][$sex]["price"] = number_format($total_price_sum, 2, ".", ",");

                $total_case_sum += 1;
                $data_servcie_loop[$supply_group_type][$sex]["total_case"] = $total_case_sum;
            }

            $old_supply_code = $supply_code;
            $old_sex = $sex;
            $old_supply_type = $supply_group_type;
        }
        // print_r($data_servcie_loop);
    }
    $stmt->close();

    $bind_param = "ss";
    $array_val = array($start_date, $end_date);

    $query = "SELECT lab_order.collect_date,
        lab_test.lab_name,
        lab_order_lab_test.sale_price,
        lab_order_lab_test.lab_id,
        patient.sex
    from p_lab_order lab_order 
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    left join patient_info patient on(patient.uid = lab_order.uid)
    where lab_order.lab_order_status != 'C'
    and lab_order.collect_date >= ? and lab_order.collect_date <= ? ";

    if($sex_code != ""){
        $query .= "and patient.sex = ?";
        $bind_param .= "s";
        $array_val[] = $sex_code;
    }

    $query .= " order by patient.sex, lab_order_lab_test.lab_id;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $total_price_sum = 0;
    $old_lab_id = "";
    $total_case_sum = 0;
    if($stmt->execute()){
        $stmt->bind_result($collect_date, $lab_name, $sale_price, $lab_id, $sex);
        while($stmt->fetch()){
            $data_servcie_loop[$lab_id][$sex]["date"] = $collect_date;
            $data_servcie_loop[$lab_id][$sex]["code"] = $lab_id;
            $data_servcie_loop[$lab_id][$sex]["name_th"] = $lab_name;
            $data_servcie_loop[$lab_id][$sex]["name_en"] = "";
            $data_servcie_loop[$lab_id][$sex]["sex"] = $st_sex_array[$sex];
            
            if($lab_id != $old_lab_id && $sex != $old_sex){
                $total_price_sum = 0;
                $total_case_sum = 0;
            }
            if($lab_id == $old_lab_id && $sex != $old_sex){
                $total_price_sum = 0;
                $total_case_sum = 0;
            }
            if($lab_id != $old_lab_id && $sex == $old_sex){
                $total_price_sum = 0;
                $total_case_sum = 0;
            }
            $total_price_sum += $sale_price;
            $data_servcie_loop[$lab_id][$sex]["price"] = number_format($total_price_sum, 2, ".", ",");

            $total_case_sum += 1;
            $data_servcie_loop[$lab_id][$sex]["total_case"] = $total_case_sum;

            $old_sex = $sex;
            $old_lab_id = $lab_id;
        }
        // print_r($data_servcie_loop);
    }
    $stmt->close();
    $mysqli->close();

    $html_bind = "";
    foreach($data_servcie_loop as $key_supcode => $data_sex){
        foreach($data_sex as $key_sex => $val){
            $html_bind .=        '<div class="fl-wrap-row font-s-2 row-hover row-color h-30">
                                    <div class="fl-fix w-20" style="background-color: white"></div>
                                    <div class="fl-fix fl-mid-left w-150">
                                        <span class="ml-1">'.$val["code"].'</span>
                                    </div>
                                    <div class="fl-fill fl-mid-left">
                                        <span class="ml-1">'.$val["name_th"].'</span>
                                    </div>
                                    <div class="fl-fix fl-mid w-250">
                                        '.$val["sex"].'
                                    </div>
                                    <div class="fl-fix fl-mid w-250">
                                        '.$val["total_case"].'
                                    </div>
                                    <div class="fl-fix fl-mid-right w-300">
                                        '.$val["price"].'
                                    </div>
                                </div>';
        }
    }

    echo $html_bind;
?>