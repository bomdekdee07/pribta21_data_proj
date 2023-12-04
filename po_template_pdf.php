<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82
    include("in_db_conn.php");

    $req_id = getQS("req_id");
    $addrtitle = getQS("addrtitle");

    $bind_param = "ss";
    $array_val = array($req_id, $addrtitle);
    $data_bill_cus = array();

    $query = "SELECT uid,
        bill_title,
        bill_name,
        bill_address,
        email,
        bill_attention
    FROM j_bill_custom
    where uid = ?
    and bill_title = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($uid, $bill_title, $bill_name, $bill_address, $email, $bill_attention);
        while($stmt->fetch()){
            $data_bill_cus["uid"] = $uid;
            $data_bill_cus["title"] = $bill_title;
            $data_bill_cus["name"] = $bill_name;
            $data_bill_cus["addr"] = $bill_address;
            $data_bill_cus["email"] = $email;
            $data_bill_cus["att"] = $bill_attention;
        }
    }
    $stmt->close();

    $bind_param = "s";
    $array_val = array($req_id);
    $data_st_list = array();

    $query = "SELECT finance_req_no,
        request_po_no,
        recieved_date,
        request_by,
        staff.s_name,
        request_proj
    FROM i_stock_request_list st_list
    left join p_staff staff on(staff.s_id = st_list.request_by)
    WHERE request_id = ?;";

    $stmt=$mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($finance_req_no, $request_po_no, $recieved_date, $request_by, $s_name, $request_proj);
        while($stmt->fetch()){
            $data_st_list["req_id"] = $finance_req_no;
            $data_st_list["po_no"] = $request_po_no;
            $data_st_list["rec_date"] = $recieved_date;
            $data_st_list["req_by"] = $s_name;
            $data_st_list["project"] = $request_proj;
        }
    }
    $stmt->close();

    $bind_param = "s";
    $array_val = array($req_id);
    $data_st_show = array();

    $query = "SELECT request_id,
        request_item_no,
        supply_code,
        request_unit,
        request_supply_note,
        request_amt,
        request_vat,
        request_item_price,
        request_total_price_final,
        request_project,
        updated_by
    FROM i_stock_request_show_item 
    WHERE request_id = ?
    AND request_item_show = '1';";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $total_item_price = 0;
    $total_sum_item_price = 0;
    $total_price_final = 0;
    if($stmt->execute()){
        $stmt->bind_result($request_id, $request_item_no, $supply_code, $request_unit, $request_supply_note, $request_amt, $request_vat, $request_item_price, $request_total_price_final, $request_project, $updated_by);
        while($stmt->fetch()){
            $data_st_show[$request_item_no]["req_id"] = $request_id;
            $data_st_show[$request_item_no]["item_no"] = $request_item_no;
            $data_st_show[$request_item_no]["sup_code"] = $supply_code;
            $data_st_show[$request_item_no]["unit"] = $request_unit;
            $data_st_show[$request_item_no]["note"] = $request_supply_note;
            $data_st_show[$request_item_no]["amt"] = $request_amt;
            $data_st_show[$request_item_no]["vat"] = $request_vat;
            $data_st_show[$request_item_no]["price"] = $request_item_price;
            $data_st_show[$request_item_no]["pricr_final"] = $request_total_price_final;
            $data_st_show[$request_item_no]["req_proj"] = $request_project;
            $data_st_show[$request_item_no]["update_by"] = $updated_by;

            $total_item_price = ($request_amt*$request_item_price); 
            $data_st_show[$request_item_no]["amt_thb"] = $total_item_price; // Amount THB
            $total_sum_item_price += $total_item_price;
            $total_price_final += $request_total_price_final; // Grand TOTAL
        }
    }
    // print_r($data_st_show);
    $stmt->close();
    $mysqli->close();

    $total_sum_item_price_vat = 0;
    $total_dif_condition = 0;
    $condition_discount = false;
    $total_price_final_beforVat = 0; // Sub total
    $total_beforVat = 0; // TAx7%
    $total_discount = 0; // DISCOUNT

    $total_sum_item_price_vat = (($total_sum_item_price*7)/100)+$total_sum_item_price;
    $total_dif_condition = ($total_price_final-$total_sum_item_price_vat);

    if(abs($total_dif_condition) >= 1){
        $condition_discount = true;
    }

    if($condition_discount){
        $total_price_final_beforVat = (($total_price_final*100)/107);
        $total_beforVat = ($total_price_final-$total_price_final_beforVat);
        $total_discount = ($total_sum_item_price-$total_price_final_beforVat);
    }
    else{
        $total_price_final_beforVat = $total_sum_item_price;
        $total_beforVat = (($total_sum_item_price*7)/100);
    }

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/po_template.jpg", 0, 0, 210, 297);
    $pdf->SetFont('THSarabun', '', 13.5);

    $check_data_stList = count($data_st_list);
    $check_data_billCustom = count($data_bill_cus);
    $check_data_stShow = count($data_st_show);
    $str_formatDate = "";

    if($check_data_stList != 0){
        $pdf->SetHeaderTxt(179, 10, $data_st_list["req_id"], "THSarabun", "", 13, array(0, 0, 0), "R", 20, 5, 0);
        $pdf->SetHeaderTxt(172, 59.5, $data_st_list["po_no"], "THSarabun", "", 13, array(0, 0, 0), "L", 20, 5, 0);

        $str_formatDate = date("d-M-Y", strtotime($data_st_list["rec_date"]));
        $pdf->SetHeaderTxt(172, 65.5, $str_formatDate, "THSarabun", "", 13, array(0, 0, 0), "L", 20, 5, 0);

        $pdf->SetHeaderTxt(45, 222.5, $data_st_list["req_by"], "THSarabun", "", 13, array(0, 0, 0), "L", 60, 5, 0);
        $pdf->SetHeaderTxt(45, 239, $data_st_list["project"], "THSarabun", "", 13, array(0, 0, 0), "L", 60, 5, 0);
    }

    if($check_data_billCustom > 0){
        $pdf->SetHeaderTxt(34, 60, $data_bill_cus["name"], "THSarabun", "", 13, array(0, 0, 0), "L", 100, 5, 0);
        $pdf->SetHeaderTxt(34, 65.5, $data_bill_cus["addr"], "THSarabun", "", 13, array(0, 0, 0), "L", 70, 5.5, 0);
        $pdf->SetHeaderTxt(34, 79, $data_bill_cus["email"], "THSarabun", "", 13, array(0, 0, 0), "L", 100, 5, 0);
        $pdf->SetHeaderTxt(34, 88, $data_bill_cus["att"], "THSarabun", "", 13, array(0, 0, 0), "L", 100, 5, 0);
    }

    //Set Start New Page
    $pdf->SetTopMargin(106.5);//63
    //Set Footer
    $pdf->SetAutoPageBreak(true, 80); //60

    $pdf->SetTableColWidth(array(15,84,18,17,20, 27));
    $pdf->SetTableColOrient(array("C","L","R","C","R","R"));

    $pdf->SetTableLineHeight(4);
    $pdf->SetTableLineMargin(3);
    $pdf->SetLeftMargin(15);

    foreach($data_st_show as $key_itemNo => $val){
        $pdf->writeRow(
            array(
                $val["item_no"],
                $val["note"],
                $val["amt"],
                $val["unit"],
                number_format($val["price"], 2, ".", ","),
                number_format($val["amt_thb"], 2, ".", ",")
            )
        );
    }

    $pdf->SetAutoPageBreak(false, "");

    $get_height = 0;
    $get_height = $pdf->GetY();
    if($condition_discount){
        $pdf->SetXY(100,$get_height+6);
        $pdf->tCell(26, 4, "ส่วนลด", 0, 0, "L");

        $pdf->SetXY(170,$get_height+6);
        $pdf->tCell(26, 4, number_format($total_discount, 2, ".", ","), 0, 0, "R");
    }

    $pdf->SetXY(170,223);
    $pdf->tCell(26, 4, number_format($total_price_final_beforVat, 2, ".", ","), 0, 0, "R");

    $pdf->SetXY(170,229);
    $pdf->tCell(26, 4, number_format($total_beforVat, 2, ".", ","), 0, 0, "R");

    $pdf->SetXY(170,234.5);
    $pdf->tCell(26, 4, number_format($total_price_final, 2, ".", ","), 0, 0, "R");

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    $name_file = "pdfoutput/B_PO"."_".$req_id."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

    if($name_file != "" || $name_file != null){
        $pdf->Output($name_file.".pdf", "F"); //I = draf not save, D auto save

        $date_save_date = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;
        $returnData = $date_save_date.",";//json_encode($name_file);
        echo $returnData;
    }
    // $pdf->Output(); //TEST
?>