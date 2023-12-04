<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $sUid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $name_doc_th = getQS("name_doc_th");
    $name_doc_en = getQS("name_doc_en");
    $doc_licen = getQS("doc_licen");
    $patient_name = getQS("patient_name");
    $idaddress = getQS("idaddress");
    $citizen = getQS("citizen");
    $sex = getQS("sex");
    $citizen_array  = array_map('intval', str_split($citizen));
    $weight = getQS("weight");
    $height = getQS("height");
    $bph = getQS("bph");
    $bpd = getQS("bpd");
    $pr = getQS("pr");
    $rr = getQS("rr");
    $congenital_disease_pdf = getQS("congenital_disease_pdf");
    $surgery_pdf = getQS("surgery_pdf");
    $hospitalized = getQS("hospitalized");
    $epilepsy = getQS("epilepsy");
    $other_history = getQS("other_history");
    $congenital_disease_txt_pdf = getQS("congenital_disease_txt_pdf");
    $surgery_txt_pdf = getQS("surgery_txt_pdf");
    $hospitalized_text = getQS("hospitalized_text");
    $epilepsy_text = getQS("epilepsy_text");
    $other_history_text = getQS("other_history_text");
    $physical_condition = getQS("physical_condition");
    $physical_condition_text = getQS("physical_condition_text");
    $comment_text = getQS("comment_text");
    $other_diseases_text = getQS("other_diseases_text");

    $bind_param = "sss";
    $array_val = array($sUid, $coldate, $coltime);
    $data_check_status_sign = "";
    $data_sign_sid = "";
    $data_type_leg = "";

    $query = "SELECT sig_status,
        s_id,
        sig_leg_type
    from i_doc_signatures
    where doc_code = 'MEDICAL_DRIVING'
    and uid = ?
    and collect_date = ?
    and collect_time = ?
    and sig_status = '1'
    order by sig_time_stemp;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($sig_status, $sid, $sig_leg_type);
        while($stmt->fetch()){
            $data_check_status_sign = $sig_status;
            $data_sign_sid = $sid;
            $data_type_leg = $sig_leg_type;
        }
    }
    $stmt->close();

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    $sFullMTh = ["01"=>'มกราคม', "02"=>'กุมภาพันธ์', "03"=>'มีนาคม', "04"=>'เมษายน', "05"=>'พฤษภาคม', "06"=>'มิถุนายน', "07"=>'กรกฎาคม', "08"=>'สิงหาคม', "09"=>'กันยายน', "10"=>'ตุลาคม', "11"=>'พฤศจิกายน', "12"=>'ธันวาคม'];

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/03_driving_certificate.jpg", 0, 0, 210, 297);

    $pdf->SetFont('THSarabun', '', 13);
    // Tตั้ง Xนอน
    $pdf->SetXY(73, 65.2); 
    $pdf->tCell(65, 4, $patient_name, 0, 0, "L");
    $idaddress = ""; // คุณหมอไม่เอา
    $pdf->SetXY(28.5, 69.8); 
    $pdf->tMultiCell(151.5, 6.2, "                                            ".$idaddress, 0, "L");

    $pdf->SetFont('THSarabun', '', 13.5);
    if($citizen != ""){
        $pdf->SetXY(80.7, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[0], 0, 0, "L");
        $pdf->SetXY(89.5, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[1], 0, 0, "L"); //+8.8
        $pdf->SetXY(96, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[2], 0, 0, "L");
        $pdf->SetXY(102.5, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[3], 0, 0, "L");
        $pdf->SetXY(109, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[4], 0, 0, "L"); //+6.5
        $pdf->SetXY(117.8, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[5], 0, 0, "L"); //+8.8
        $pdf->SetXY(124.3, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[6], 0, 0, "L");
        $pdf->SetXY(130.8, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[7], 0, 0, "L");
        $pdf->SetXY(137.3, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[8], 0, 0, "L");
        $pdf->SetXY(143.8, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[9], 0, 0, "L"); //+6.5
        $pdf->SetXY(152.2, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[10], 0, 0, "L"); //+8.4
        $pdf->SetXY(158.7, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[11], 0, 0, "L"); //+6.5
        $pdf->SetXY(167.4, 85.7); 
        $pdf->tCell(5, 4, $citizen_array[12], 0, 0, "L"); //+8.7
    }

    $pdf->SetFont('THSarabun', '', 13);
    if($congenital_disease_pdf == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 84.2, 96.5, 9, 12);
    }
    else if($congenital_disease_pdf == "Y"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 94.7, 96.5, 9, 12);
        $pdf->SetXY(115.5, 100.3); 
        $pdf->tCell(73, 4, $congenital_disease_txt_pdf, 0, 0, "L"); //+3.8
    }

    if($surgery_pdf == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 84.2, 103.3, 9, 12);
    }
    else if($surgery_pdf == "Y"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 94.7, 103.3, 9, 12);
        $pdf->SetXY(115.5, 107.1); 
        $pdf->tCell(73, 4, $surgery_txt_pdf, 0, 0, "L"); //+3.8
    }

    if($hospitalized == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 84.2, 110.1, 9, 12); //+6.8
    }
    else if($hospitalized == "Y"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 94.7, 110.1, 9, 12);
        $pdf->SetXY(115.5, 113.9); 
        $pdf->tCell(73, 4, $hospitalized_text, 0, 0, "L"); //+3.8
    }

    if($epilepsy == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 84.2, 116.9, 9, 12); //+6.8
    }
    else if($epilepsy == "Y"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 94.7, 116.9, 9, 12);
        $pdf->SetXY(115.5, 120.7); 
        $pdf->tCell(73, 4, $epilepsy_text, 0, 0, "L"); //+3.8
    }

    if($other_history == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 84.2, 123.7, 9, 12); //+6.8
    }
    else if($other_history == "Y"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 94.7, 123.7, 9, 12);
        $pdf->SetXY(115.5, 127.5); 
        $pdf->tCell(73, 4, $other_history_text, 0, 0, "L"); //+3.8
    }

    $print_convert_month = $sFullMTh[$month];
    $print_convert_year = $year+543;
    $pdf->SetXY(128, 143.1); 
    $pdf->tCell(10, 4, $day, 0, 0, "L");
    $pdf->SetXY(143.6, 143.1); 
    $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
    $pdf->SetXY(172, 143.1); 
    $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

    $pdf->SetXY(128, 164.8); 
    $pdf->tCell(10, 4, $day, 0, 0, "L");
    $pdf->SetXY(143.6, 164.8); 
    $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
    $pdf->SetXY(172, 164.8); 
    $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

    $pdf->SetXY(73, 170.5); 
    $pdf->tCell(15, 4, $name_doc_th, 0, 0, "L");

    $pdf->SetXY(84, 176.7); 
    $pdf->tCell(15, 4, $doc_licen, 0, 0, "L");

    if($sex == "1")
        $pdf->SetHeaderImage("assets/image/oval_pdf.png", 46.5, 188.5, 7, 5); //+6.8

    $pdf->SetXY(73, 188.5); 
    $pdf->tCell(15, 4, $patient_name, 0, 0, "L");

    $pdf->SetXY(47.6, 194.7); 
    $pdf->tCell(10, 4, $day, 0, 0, "L");
    $pdf->SetXY(68.5, 194.7); 
    $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
    $pdf->SetXY(102, 194.7); 
    $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

    $pdf->SetXY(43.5, 200.6); 
    $pdf->tCell(15, 4, $weight, 0, 0, "L");
    $pdf->SetXY(66.5, 200.6); 
    $pdf->tCell(21, 4, $height, 0, 0, "C");
    
    if($bph != ""){
        $pdf->SetXY(113.5, 200.6); 
        $pdf->tCell(21, 4, $bph."/".$bpd, 0, 0, "C");
    }

    if($pr != ""){
        $pdf->SetXY(151.5, 200.6); 
        $pdf->tCell(21, 4, $pr, 0, 0, "C");
    }

    if($physical_condition == "N"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 64.2, 203, 11, 14);
    }
    else{
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 79, 203, 11, 14);
        $pdf->SetXY(105, 208.5); 
        $pdf->tCell(89, 4, $physical_condition_text, 0, 0, "L"); //+3.8
    }

    $pdf->SetXY(58, 250.4); 
    $pdf->tCell(100, 4, $other_diseases_text, 0, 0, "L"); //+3.8

    $pdf->SetXY(26, 255.9); 
    $pdf->tMultiCell(162, 4.9, "                                                    ".$comment_text, 0, "L");

    if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
        $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 116, 261.5, 35, 8);
    }

    $date_save_date = "";
    $name_file = "pdfoutput/MEDICAL_DRIVING"."_".$sUid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

    if($name_file != "" || $name_file != null){
        if($doc_mode == "view"){
            $pdf->Output();
        }
        else{
            $pdf->Output($name_file.".pdf", "F"); //I = draf not save, D auto save
            $date_save_date = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;
            $returnData = $date_save_date.",";//json_encode($name_file);
            echo $returnData;
        }
    }
?>