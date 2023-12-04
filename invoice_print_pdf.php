<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $uid = getQS("uid");
    $bill_id = getQS("billid");
    $bill_id = substr($bill_id, 0, 4)."/".substr($bill_id, 4);
    $sid = getSS("s_id");
    $option_id = getQS("opt_mode");
    $addr_title = getQS("addrtitle");

    $data_detail_1 = array();
    $total_price_sum = 0;
    $old_data_check = "";
    $old_data_group_code_check = "";
    $total_dose_day = 0;
    $col_date = "";
    $query = "select st_order.supply_code,
        st_master.supply_name,
        st_type.supply_type_name as name_n_service,
        st_group.supply_group_name as name_is_service,
        st_order.dose_day,
        st_order.total_price,
        st_group.supply_group_code,
        st_group.supply_group_type,
        st_type.is_service,
        queue_l.uid as uid_detail,
        queue_l.collect_date
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join i_stock_order st_order on(st_order.uid = queue_l.uid and st_order.collect_date = queue_l.collect_date and st_order.collect_time = queue_l.collect_time)
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    where bill_d.bill_id = ?
    and st_order.supply_code is not null
    order by st_group.supply_group_type, st_order.supply_code, st_group.supply_group_code;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($supply_code, $supply_name, $name_n_service, $name_is_service, $dose_day, $total_price, $supply_group_code, $supply_group_type, $is_service, $uid_detail, $collect_date);
        while ($stmt->fetch()) {
            //OPTION ID SELECT
            if($option_id == 1){
                $data_detail_1[$supply_code]["supply_name"] = $supply_name;
                if($is_service == 0){
                    $data_detail_1[$supply_code]["qty"] = $dose_day;
                }
                else{
                    $data_detail_1[$supply_code]["qty"] = "";
                }

                if($old_data_check == $supply_code){
                    $total_price_sum = ($total_price_sum+$total_price);
                }
                else{
                    $total_price_sum = $total_price;
                }

                $data_detail_1[$supply_code]["total_price"] = $total_price_sum;
                $data_detail_1[$supply_code]["group_type"] = $supply_group_type;
                $old_data_check = $supply_code;
                // echo $old_data_check."/".$supply_code.":".$total_price_sum."<br>";
            }
            else{
                if($is_service == 0){
                    $data_detail_1[$supply_group_type]["phamar"]["supply_name"] = $name_n_service;
                    $total_dose_day = $total_dose_day+$dose_day;
                    $data_detail_1[$supply_group_type]["phamar"]["qty"] = $total_dose_day;

                    if($old_data_check == $supply_group_type){
                        $total_price_sum = ($total_price_sum+$total_price);
                    }
                    else{
                        $total_price_sum = $total_price;
                    }

                    $data_detail_1[$supply_group_type]["phamar"]["total_price"] = $total_price_sum;
                    $data_detail_1[$supply_group_type]["phamar"]["group_type"] = $supply_group_type;
                }
                else{
                    $data_detail_1[$supply_group_type][$supply_group_code]["supply_name"] = $name_is_service;
                    $data_detail_1[$supply_group_type][$supply_group_code]["qty"] = "";

                    if($old_data_check == $supply_group_type && $old_data_group_code_check == $supply_group_code){
                        $total_price_sum = ($total_price_sum+$total_price);
                    }
                    else{
                        $total_price_sum = $total_price;
                    }

                    $data_detail_1[$supply_group_type][$supply_group_code]["total_price"] = $total_price_sum;
                    $data_detail_1[$supply_group_type][$supply_group_code]["group_type"] = $supply_group_type;
                }

                $old_data_check = $supply_group_type;
                $old_data_group_code_check = $supply_group_code;
            }

            if($uid == $uid_detail){
                $col_date = $collect_date;
            }
        }
        // print_r($data_detail_1);
    }

    $stmt->close();

    $data_detail_2 = array();
    $total_price_sum = 0;
    $old_data_check = "";
    $query = "select lab_order_lab_test.lab_id,
        lab_test.lab_name,
        lab_order_lab_test.sale_price
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join p_lab_order lab_order on(lab_order.uid = queue_l.uid and lab_order.collect_date = queue_l.collect_date and lab_order.collect_time = queue_l.collect_time)
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = lab_test.lab_group_id)
    where lab_order.lab_order_status != 'C'
    and bill_d.bill_id = ?
    and lab_group.ref_lab_id = ''
    order by lab_id;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($lab_id, $lab_name, $sale_price);
        while ($stmt->fetch()) {
            if($option_id == 1){
                $data_detail_2[$lab_id]["lab_name"] = $lab_name;
            
                if($old_data_check == $lab_id){
                    $total_price_sum = $total_price_sum+$sale_price;
                }
                else{
                    $total_price_sum = $sale_price;
                }
                $data_detail_2[$lab_id]["total_price"] = $total_price_sum;

                $old_data_check = $lab_id;
            }
            else{
                $data_detail_2["lab_name"] = "Lab";

                $total_price_sum = $total_price_sum+$sale_price;
                $data_detail_2["total_price"] = $total_price_sum;
            }
        }
        // print_r($data_detail_2);
    }

    $stmt->close();

    $data_detail_3 = array();
    $total_price_sum = 0;
    $old_data_check = "";
    if($option_id != 1 && count($data_detail_2) > 0)
    $total_all_lab_price = $data_detail_2["total_price"];
    $query = "select lab_order_lab_test.lab_id,
        lab_group.lab_group_name,
        lab_order_lab_test.sale_price
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join p_lab_order lab_order on(lab_order.uid = queue_l.uid and lab_order.collect_date = queue_l.collect_date and lab_order.collect_time = queue_l.collect_time)
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = lab_test.lab_group_id)
    where lab_order.lab_order_status != 'C'
    and bill_d.bill_id = ?
    and lab_group.ref_lab_id != ''
    order by lab_group_name, sale_price;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($lab_id, $lab_group_name, $sale_price);
        while ($stmt->fetch()) {
            if($option_id == 1){
                $data_detail_3[$lab_group_name]["lab_name"] = $lab_group_name;
                if($old_data_check == $lab_id){
                    $total_price_sum = $total_price_sum+$sale_price;
                }
                else{
                    $total_price_sum = $sale_price;
                }
                $data_detail_3[$lab_group_name]["total_price"] = $total_price_sum;

                $old_data_check = $lab_id;
            }
            else{
                $data_detail_2["lab_name"] = "Lab";

                $total_all_lab_price = $total_all_lab_price+$sale_price;
                $data_detail_2["total_price"] = $total_all_lab_price;
            }
        }
        // print_r($data_detail_3);
    }

    $stmt->close();

    $count_gen_inv = 0;
    $query = "select count(*) as inv_count from i_doc_list 
    where uid = ?
    and doc_code = 'B_INVOICE';";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $uid);

    if($stmt->execute()){
        $stmt->bind_result($inv_count);
        while ($stmt->fetch()) {
            $count_gen_inv = $inv_count;
        }
        // print_r($data_detail_3);
    }

    $stmt->close();

    // ADDRESS_TITLE
    $name_data_head = "";
    $query = "select fname, sname from patient_info where uid = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $uid);

    if($stmt -> execute()){
        $stmt -> bind_result($fname, $sname);
        while($stmt -> fetch()){
            $name_data_head = $fname." ".$sname;
        }
        // echo $name_data_head;
    }
    $stmt->close();

    $address_title_data = array("citizenid" => "", "address" => "");
    if($addr_title == ""){
        $query = "select citizen_id, 
            id_address,
            '' bill_name
        from patient_info 
        where uid = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $uid);
    }else{
        $query = "select bill_tax, 
            bill_address,
            bill_name
        from j_bill_custom 
        where bill_title = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $addr_title);
    }

    if($stmt -> execute()){
        $stmt -> bind_result($bill_tax, $bill_address, $bill_name);
        while($stmt -> fetch()){
            if($addr_title != "")
            $name_data_head = $bill_name;
            $address_title_data["address"] = $bill_address;
        }
    }
    $stmt->close();
    $mysqli->close();

    function gen_inv_code($get_num){
        return sprintf("%'.05d\n", $get_num);
    }

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    if($count_gen_inv == 0)
    $count_gen_inv = 1;

    $invoice_no = "INV".$bill_id;

    $convert_date = date_create($col_date);
    $convert_date = date_format($convert_date, 'd/m/Y');
    $date_print_now = $convert_date;//$day."/".$month."/".($year+543);

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/invoice_template.png", 0, 0, 210, 290);
    $pdf->SetPageNo(185, 285, $stxt="Page {p}/{tp}", "THSarabun", "", 11);

    // HEADER
    // $pdf->SetFont('THSarabun', '', 12);
    // $pdf->SetXY(123.5,64);
    // $pdf->tMultiCell(74, 5, $address_title_data["address"], 0, "L");

    $width = 154;
    $height = 48;
    $pdf->SetHeaderTxt($width, $height, $invoice_no, "THSarabun", "", 13, array(0, 0, 0));
    $pdf->SetHeaderTxt($width, $height+4.1, $date_print_now, "THSarabun", "", 12.5, array(0, 0, 0));

    $width = 142;
    $pdf->SetHeaderTxt($width, $height+10.5, $uid, "THSarabun", "", 12.5, array(0, 0, 0));
    $pdf->SetHeaderTxt($width, $height+15, $name_data_head, "THSarabun", "", 12.5, array(0, 0, 0));

    $width = 123.5;
    $height = 64;
    $pdf->SetHeaderTxt($width, $height, $address_title_data["address"], "THSarabun", "", 12, array(0, 0, 0), "L", 75, 5, 0);

    //Set Start New Page
    $pdf->SetTopMargin(101);
    //Set Footer
    $pdf->SetAutoPageBreak(true, 97); //60///

    $pdf->SetTableColWidth(array(82,27,27,40));
    $pdf->SetTableColOrient(array("L","C","R","R"));
    $pdf->SetTableLineHeight(3);
    $pdf->SetTableLineMargin(3);
    $pdf->SetLeftMargin(15);

    $pdf->SetX(0);
    $width = 5;
    $height_loop = 10.5;

    $total_all = 0;
    $count_loop = count($data_detail_1);
    $count_loop2 = count($data_detail_2);
    $count_loop3 = count($data_detail_3);

    if($count_loop > 0){
        if($option_id == 1){
            foreach($data_detail_1 as $key => $val){
                $pdf->writeRow(
                    array(
                        $val["supply_name"],
                        $val["qty"],
                        $val["total_price"],
                        $val["total_price"],
                        ""
                    )
                );

                if($val["group_type"] == "3"){
                    $total_all = ($total_all+$val["total_price"]);
                }
                else{
                    $total_all = $total_all+$val["total_price"];
                }
            }
        }
        else{
            foreach($data_detail_1 as $key => $val){
                foreach($val as $key => $val_2){
                    $pdf->writeRow(
                        array(
                            $val_2["supply_name"],
                            $val_2["qty"],
                            $val_2["total_price"],
                            $val_2["total_price"],
                            ""
                        )
                    );

                    if($val_2["group_type"] == "3"){
                        $total_all = ($total_all+$val_2["total_price"]);
                    }
                    else{
                        $total_all = $total_all+$val_2["total_price"];
                    }
                }
            }
        }
    }

    if($count_loop2 > 0){
        if($option_id == 1){
            foreach($data_detail_2 as $key => $val){
                $pdf->writeRow(
                    array(
                        $val["lab_name"],
                        "",
                        $val["total_price"],
                        $val["total_price"],
                        ""
                    )
                );
                
                $total_all = $total_all+$val["total_price"];
            }
        }
        else{
            $pdf->writeRow(
                array(
                    $data_detail_2["lab_name"],
                    "",
                    $data_detail_2["total_price"],
                    $data_detail_2["total_price"],
                    ""
                )
            );
            
            $total_all = $total_all+$data_detail_2["total_price"];
        }
    }

    if($count_loop3 > 0){
        foreach($data_detail_3 as $key => $val){
            $pdf->writeRow(
                array(
                    $val["lab_name"],
                    "",
                    $val["total_price"],
                    $val["total_price"],
                    ""
                )
            );

            $total_all = $total_all+$val["total_price"];
        }
    }
    // echo $total_all;

    $pdf->SetAutoPageBreak(true, 0); //60

    $pdf->SetXY(186, 175.5);
    $pdf->tCell($width, $height, number_format($total_all, 0, ".", ","), "", "", "R", "");
    // $pdf->SetXY(186, 188);
    // $pdf->tCell($width, $height, "7%", "", "", "R", "");
    $pdf->SetXY(186, 195.6);
    $pdf->tCell($width, $height, number_format($total_all, 0, ".", ","), "", "", "R", "");

    // $pdf->Output(); //TEST
    $name_file = "pdfoutput/B_INVOICE_".preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id)."_".$year."".$month."".$day."".$hours."".$munite."".$sec;
    if($name_file != "" || $name_file != null){
        $pdf->Output($name_file.".pdf", "F"); //I = draf not save, D auto save

        $date_save_date = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;
        $returnData = $date_save_date.",".preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id).","."".","."";//json_encode($name_file);
        echo $returnData;
    }
?>