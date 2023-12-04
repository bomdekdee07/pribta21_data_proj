<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $bill_id = getQS("billid");
    $uid = getQS("uid");
    $addr_title = getQS("addrtitle");
    $sid = getSS("s_id");
    $type_leg = getQS("type_leg");
    // echo "address:".$type_leg;

    function en_numtothaistring($num){
        $ones = array(
            0 =>"ZERO",
            1 => "ONE",
            2 => "TWO",
            3 => "THREE",
            4 => "FOUR",
            5 => "FIVE",
            6 => "SIX",
            7 => "SEVEN",
            8 => "EIGHT",
            9 => "NINE",
            10 => "TEN",
            11 => "ELEVEN",
            12 => "TWELVE",
            13 => "THIRTEEN",
            14 => "FOURTEEN",
            15 => "FIFTEEN",
            16 => "SIXTEEN",
            17 => "SEVENTEEN",
            18 => "EIGHTEEN",
            19 => "NINETEEN",
            "014" => "FOURTEEN"
        );
        $tens = array( 
            0 => "ZERO",
            1 => "TEN",
            2 => "TWENTY",
            3 => "THIRTY", 
            4 => "FORTY", 
            5 => "FIFTY", 
            6 => "SIXTY", 
            7 => "SEVENTY", 
            8 => "EIGHTY", 
            9 => "NINETY" 
        ); 
        $hundreds = array( 
            "HUNDRED", 
            "THOUSAND", 
            "MILLION", 
            "BILLION", 
            "TRILLION", 
            "QUARDRILLION" 
        ); /*limit t quadrillion */

        $num = number_format($num,2,".",","); 
        $num_arr = explode(".",$num); 
        $wholenum = $num_arr[0]; 
        $decnum = $num_arr[1]; 
        $whole_arr = array_reverse(explode(",",$wholenum)); 
        krsort($whole_arr,1);

        $rettxt = ""; 
        foreach($whole_arr as $key => $i){
            while(substr($i,0,1)=="0")
                $i=substr($i,1,5);
                if($i < 20){ 
                /* echo "getting:".$i; */
                $rettxt .= $ones[$i]; 
                }
                elseif($i < 100){ 
                    if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
                    if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
                }
                else{ 
                    if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
                    if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
                    if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
                } 
                if($key > 0){ 
                    $rettxt .= " ".$hundreds[$key]." "; 
                }
        } 

        if($decnum > 0){
            $rettxt .= " and ";
            if($decnum < 20){
                $rettxt .= $ones[$decnum];
            }
            elseif($decnum < 100){
                $rettxt .= $tens[substr($decnum,0,1)];
                $rettxt .= " ".$ones[substr($decnum,1,1)];
            }
        }

        return $rettxt;
    }

    $name_cash = "";
    $query = "select s_name from p_staff where s_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $sid);

    if($stmt -> execute()){
        $stmt -> bind_result($s_name);
        while($stmt -> fetch()){
            $name_cash = $s_name;
        }
    }
    $stmt->close();

    // NAME HEAD
    $name_data_head = "";
    $query = "select fname, sname from patient_info where uid = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $uid);

    if($stmt -> execute()){
        $stmt -> bind_result($fname, $sname);
        while($stmt -> fetch()){
            $name_data_head = $uid." ".$fname." ".$sname;
        }
        // echo $name_data_head;
    }
    $stmt->close();

    // ADDRESS_TITLE
    $address_title_data = array("citizenid" => "", "address" => "");
    if($addr_title == ""){
        $query = "select citizen_id, 
            id_address,
            '' bill_name,
            passport_id
        from patient_info 
        where uid = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $uid);
    }else{
        $query = "select bill_tax, 
            bill_address,
            bill_name,
            '' as passport_id
        from j_bill_custom 
        where bill_title = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $addr_title);
    }

    if($stmt -> execute()){
        $stmt -> bind_result($bill_tax, $bill_address, $bill_name, $passport_id);
        while($stmt -> fetch()){
            if($addr_title != "")
            $name_data_head = $bill_name;

            $address_title_data["citizenid"] = $bill_tax!="0000000000000"? $bill_tax: $passport_id;
            $address_title_data["address"] = $bill_address;
        }
    }
    $stmt->close();
    // print_r($address_title_data);

    // DETAIL ALL
    $bill_id_head = "";
    $queue_head = "";
    $uid_bill_detail = "";
    $supply_group_type_old = "";
    $supply_name_old = "";
    $supply_group_code_old = "";
    $date_full_coldate = "";
    $total_price_sum_group_all = "";
    $total_price_sum = "";

    $total_all_data = array();
    $uid_count_array = array();
    $query = "select bill_d.bill_id,
        queue_l.uid as uid_detail,
        queue_l.collect_date,
        queue_l.collect_time,
        queue_l.queue,
        st_type.supply_type_name as name_n_service,
        st_group.supply_group_name as name_is_service,
        st_master.supply_name,
        st_type.supply_type_initial,
        st_order.total_price,
        st_group.supply_group_type,
        st_type.is_service,
        st_group.supply_group_code
    from i_bill_detail bill_d
    join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join i_stock_order st_order on(st_order.uid = queue_l.uid and st_order.collect_date = queue_l.collect_date and st_order.collect_time = queue_l.collect_time)
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    where bill_id = ?
    order by queue_l.uid, st_group.supply_group_type, st_group.supply_group_code;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($bill_id, $uid_detail, $collect_date, $collect_time, $queue, $name_n_service, $name_is_service, $supply_name, $supply_type_initial, $total_price, $supply_group_type, $is_service, $supply_group_code);
        while ($stmt->fetch()) {
            if($supply_group_type_old != $supply_group_type)
                $total_price_sum = "";

            if($is_service == 1){
                if($supply_group_code_old != $supply_group_code && $supply_name_old != $supply_name)
                    $total_price_sum = "";
                if($supply_group_code_old == $supply_group_code && $supply_name_old != $supply_name)
                    $total_price_sum = "";
                
                $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["uid"] = $uid_detail;
                $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["lab_name"] = $supply_name;
                $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["code_name"] = $supply_type_initial;
                $total_price_sum = $total_price_sum+$total_price;
                $total_all_data[$uid_detail][$supply_group_type][$supply_group_code.$supply_name]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }
            else{
                $total_all_data[$uid_detail][$supply_group_type]["phamar"]["uid"] = $uid_detail;
                $total_all_data[$uid_detail][$supply_group_type]["phamar"]["lab_name"] = $name_n_service;
                $total_all_data[$uid_detail][$supply_group_type]["phamar"]["code_name"] = $supply_type_initial;
                $total_price_sum = $total_price_sum+$total_price;
                $total_all_data[$uid_detail][$supply_group_type]["phamar"]["total_price"] = $total_price_sum;
                $total_price_sum_group_all = $total_price_sum_group_all+$total_price;
            }

            if($uid_detail == $uid){
                $date = date_create($collect_date);
                $date_con = date_format($date,"d/m");
                if($type_leg == 'TH'){
                    $year_con = date_format($date,"Y")+543;
                }
                else{
                    $year_con = date_format($date,"Y");
                }
                $date_full_coldate = $date_con."/".$year_con." ".$collect_time;
                $coldate = $collect_date;
                $coltime = $collect_time;
                $queue_head = $queue;
            }
            
            $supply_group_type_old = $supply_group_type;
            $supply_name_old = $supply_name;
            $supply_group_code_old = $supply_group_code;
            $uid_count_array[$uid_detail] = $uid_detail;
        }
        // print_r($total_all_data);
        // echo count($uid_count_array);
    }
    $stmt->close();

    // LAB TOTAL
    $lab_saleprice_data = array();
    $total_price_sum_lab_all = "";
    $query = "select queue_l.uid as uid_lab,
        lab_test.lab_name,
        lab_order_lab_test.sale_price,
        lab_order_lab_test.lab_id
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join p_lab_order lab_order on(lab_order.uid = queue_l.uid and lab_order.collect_date = queue_l.collect_date and lab_order.collect_time = queue_l.collect_time)
    left join p_lab_order_lab_test lab_order_lab_test on(lab_order_lab_test.uid = lab_order.uid and lab_order_lab_test.collect_date = lab_order.collect_date and lab_order_lab_test.collect_time = lab_order.collect_time)
    left join p_lab_test lab_test on(lab_test.lab_id = lab_order_lab_test.lab_id)
    where lab_order.lab_order_status != 'C'
    and bill_d.bill_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($uid_lab, $lab_name, $sale_price, $lab_id);
        while ($stmt->fetch()) {
            $lab_saleprice_data[$uid_lab]["lab_name"] = "Lab";
            $total_price_sum_lab_all = $total_price_sum_lab_all+$sale_price;
            $lab_saleprice_data[$uid_lab]["total_price"] = $total_price_sum_lab_all;
        }
        // print_r($lab_saleprice_data);
    }

    $stmt->close();
    $mysqli->close();

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    $uid_count_array = count($uid_count_array);
    $date_print_now = "print on ".$day."/".$month."/".($year+543)." ".$hours.":".$munite.":".$sec;
    $name_file = "pdfoutput/RECEIPT_".preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id)."_".$year."".$month."".$day."".$hours."".$munite."".$sec;
    // echo $date_print_now."//".$name_file;

    $total_all_page = 0;
    $total_all_page_num = 0;
    $total_all_page_num = ($total_price_sum_lab_all+$total_price_sum_group_all);
    $total_all_page = number_format(($total_price_sum_lab_all+$total_price_sum_group_all), 0, '.', ',');

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('L', 'A4', 'mm');

    if($type_leg == "TH"){
        $pdf->SetHeaderImage("assets/image/receiptform_th.jpg", 0, 0, 290, 210);
    }
    else{
        $pdf->SetHeaderImage("assets/image/receiptform_en.jpg", 0, 0, 290, 210);
    }
    $pdf->SetPageNo(125, 195, $stxt="Page {p}/{tp}", "THSarabun", "", 11);
    $pdf->SetPageNo(260, 195, $stxt="Page {p}/{tp}", "THSarabun", "", 11);

    // HEADER
    $width = 119.8;
    $height = 25;
    $pdf->SetHeaderTxt($width, $height, $bill_id, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width, $height+4.6, "#".$queue_head, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-93, $height+25.5, $date_full_coldate, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-37, $height+25.3, $name_data_head, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-90, $height+32.6, $address_title_data["address"], "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-78, $height+39.5, $address_title_data["citizenid"], "THSarabun", "", 11, array(0, 0, 0));

    $width = 254.5;
    $pdf->SetHeaderTxt($width, $height, $bill_id, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width, $height+4.6, "#".$queue_head, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-93, $height+25.5, $date_full_coldate, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-37, $height+25.3, $name_data_head, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-90, $height+32.6, $address_title_data["address"], "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetHeaderTxt($width-78, $height+39.5, $address_title_data["citizenid"], "THSarabun", "", 11, array(0, 0, 0));

    $width = 100;
    $height = 177.5;
    $pdf->SetHeaderTxt($width+1.5, $height+25, $date_print_now, "THSarabun", "", 11, array(0, 0, 0));

    $width = 235;
    $pdf->SetHeaderTxt($width+1.5, $height+25, $date_print_now, "THSarabun", "", 11, array(0, 0, 0));

    //Set Start New Page
    $pdf->SetTopMargin(74);//63
    //Set Footer
    $pdf->SetAutoPageBreak(true, 40); //60

    if($uid_count_array > 1){
        $pdf->SetTableColWidth(array(15,78,20,7, 15,78,20));
        $pdf->SetTableColOrient(array("L","L","R","", "L","L","R"));
    }
    else{
        $pdf->SetTableColWidth(array(0,93,20,7, 15,93,20));
        $pdf->SetTableColOrient(array("L","L","R","", "L","L","R"));
    }
    $pdf->SetTableLineHeight(4);
    $pdf->SetTableLineMargin(4);
    $pdf->SetLeftMargin(21);

    // Item name ENG
    $item_text_eng = array("ยา" => "Medicine fee", "Lab" => "Laboratory fee", "ค่าบริการ(Service)" => "Service fee", "ส่วนลด" => "Promotion discount", "ค่าฉีดยา" => "Injection service fee", "ค่าบริการฉีดยา" => "Injection service fee", "Hormones" => "Medicine fee", "ค่าบริการทั่วไป" => "Service fee", "ค่าบริการทางการแพทย์" => "Medical fee", "ส่วนลดจากโปรโมชั่นตรวจจัดหนัก" => "Promotion discount", "ค่าใบรับรองFittofly" => "Fit to fly certificate fee", "ค่าทำบัตร" => "Card making fee", "ส่วนลดจากโครงการPointofCare" => "Discounts from the Point of Care program", "ส่วนลดค่าบริการ" => "Service discount");
    $item_name_leg = "";

    $pdf->SetX(0);
    $width = 16;
    $height_loop = 10.5;
    foreach($total_all_data as $key => $val){
        $uid_loop = strval($key);
        foreach($val as $key => $val_2){
            foreach($val_2 as $key => $val_3){
                $check_have_data = isset($val_3["lab_name"]);
                if($check_have_data != ""){
                    if($type_leg == "EN"){
                        if(array_key_exists(preg_replace('/[[:space:]]+/', '', $val_3["lab_name"]), $item_text_eng))
                        {
                            $item_name_leg = $item_text_eng[preg_replace('/[[:space:]]+/', '', $val_3["lab_name"])];
                        }
                        else{
                            $item_name_leg = "Please contact Bom IT for add new name item english";
                        }
                    }
                    else{
                        $item_name_leg = $val_3["lab_name"];
                    }

                    $pdf->writeRow(
                        array(
                            ($uid_count_array>1? $uid_loop:""),
                            $item_name_leg,
                            $val_3["total_price"],
                            "",
                            ($uid_count_array>1? $uid_loop:""),
                            $item_name_leg,
                            $val_3["total_price"]
                        )
                    );
                }
            }
        }

        if(count($lab_saleprice_data) > 0){
            if($type_leg == "EN"){
                if(array_key_exists(preg_replace('/[[:space:]]+/', '', $lab_saleprice_data[$uid_loop]["lab_name"]), $item_text_eng))
                {
                    $item_name_leg = $item_text_eng[preg_replace('/[[:space:]]+/', '', $lab_saleprice_data[$uid_loop]["lab_name"])];
                }
                else{
                    $item_name_leg = "Please contact Bom IT for add new name item Lab english";
                }
            }
            else{
                $item_name_leg = $lab_saleprice_data[$uid_loop]["lab_name"];
            }
            $pdf->writeRow(
                array(
                    ($uid_count_array>1? $uid_loop:""),
                    $item_name_leg, 
                    $lab_saleprice_data[$uid_loop]["total_price"],
                    "",
                    ($uid_count_array>1? $uid_loop:""),
                    $item_name_leg, 
                    $lab_saleprice_data[$uid_loop]["total_price"]
                )
            );
        }
    }

    $pdf->SetAutoPageBreak(false, "");
    // FOOTER
    $width = 40;
    $height = 167.4;
    $total_text = "";
    if($type_leg == "EN"){
        $total_text = en_numtothaistring($total_all_page_num)." BAHT";
    }
    else{
        $total_text = j_numtothaistring($total_all_page_num);
    }

    $pdf->SetHeaderTxt($width, $height, $total_text, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetXY(116,164.1);
    $pdf->tCell(19, 4, $total_all_page, 0, 0, "R");

    $pdf->SetXY(90,183.5); //85
    $pdf->tCell(37.5, 4, $name_cash, 0, 0, "C");

    $width = 175;
    $pdf->SetHeaderTxt($width, $height, $total_text, "THSarabun", "", 11, array(0, 0, 0));
    $pdf->SetXY(250,164.1);
    $pdf->tCell(19, 4, $total_all_page, 0, 0, "R");

    $pdf->SetXY(224.5,183.5); //219
    $pdf->tCell(37.5, 4, $name_cash, 0, 0, "C");

    // $pdf->Output(); //TEST
    if(count($total_all_data) > 0){
        if($name_file != "" || $name_file != null){
            $pdf->Output($name_file.".pdf", "F"); //I = draf not save, D auto save

            $date_save_date = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;
            $returnData = $date_save_date.",".preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id).","."".","."";//json_encode($name_file);
            echo $returnData;
        }
    }
?>