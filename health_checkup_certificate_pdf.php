<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $leg_moed = getQS("leg_moed");
    $sUid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $name_doc_th = getQS("name_doc_th");
    $name_doc_en = getQS("name_doc_en");
    $doc_licen = getQS("doc_licen");
    $patient_name = getQS("patient_name");
    $patient_name_en = getQS("patient_name_en");
    $idaddress = getQS("idaddress");
    $citizen = getQS("citizen");
    $custom_check = getQS("custom_check");
    $citizen_cus = getQS("citizen_cus");
    $passport_cus = getQS("passport_cus");
    $passport_id = getQS("passport");
    $sex = getQS("sex");

    if($custom_check == "Y"){
        $citizen = $citizen_cus;
        $passport_id = $passport_cus;
    }
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
    where doc_code = 'MEDICAL_HEALTH'
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
    $sFullMTh_EN = ["01"=>'January', "02"=>'Febuary', "03"=>'March', "04"=>'April', "05"=>'May', "06"=>'June', "07"=>'July', "08"=>'August', "09"=>'September', "10"=>'October', "11"=>'November', "12"=>'December'];

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    if($leg_moed == "TH")
        $pdf->SetHeaderImage("assets/image/02_medical_specail_certificate.jpg", 0, 0, 210, 297);
    else
        $pdf->SetHeaderImage("assets/image/02_medical_specail_certificate_EN.jpg", 0, 0, 210, 297);

    $pdf->SetFont('THSarabun', '', 13);
    // Tตั้ง Xนอน
    if($leg_moed == "TH"){
        $pdf->SetXY(72.6, 62.5); 
        $pdf->tCell(65, 4, $patient_name, 0, 0, "L");
    }
    else{
        $pdf->SetXY(72.6, 59.6); 
        $pdf->tCell(65, 4, $patient_name_en, 0, 0, "L");
    }
    $pdf->SetXY(28.5, 67.7); 
    $idaddress = ""; // หมอโด่งให้เอาออก กรณีคนไข้อยู่ ตจว ให้เขาเขียนเองดีกว่า
    $parameter_Y = "";
    $pdf->tMultiCell(151.5, 6.2, "                                            ".$idaddress, 0, "L");

    $pdf->SetFont('THSarabun', '', 13.5);
    if($leg_moed == "TH"){
        $parameter_Y = 75.6;
        $parameter_X_dis = 11.6;
    }
    else{
        $parameter_Y = 70.5;
        $parameter_X_dis = 9;
    }

    if(strlen($citizen) >= 13){
        $pdf->SetXY(80.4-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[0], 0, 0, "L");
        $pdf->SetXY(86.3-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[1], 0, 0, "L");
        $pdf->SetXY(90.5-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[2], 0, 0, "L");
        $pdf->SetXY(95-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[3], 0, 0, "L");
        $pdf->SetXY(99.5-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[4], 0, 0, "L");
        $pdf->SetXY(105-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[5], 0, 0, "L");
        $pdf->SetXY(109.5-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[6], 0, 0, "L");
        $pdf->SetXY(114-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[7], 0, 0, "L");
        $pdf->SetXY(118.5-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[8], 0, 0, "L");
        $pdf->SetXY(123-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[9], 0, 0, "L");
        $pdf->SetXY(128.5-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[10], 0, 0, "L");
        $pdf->SetXY(133-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[11], 0, 0, "L");
        $pdf->SetXY(138.4-$parameter_X_dis, $parameter_Y); 
        $pdf->tCell(5, 4, $citizen_array[12], 0, 0, "L");
    }

    if($passport_id != "" && $leg_moed == "TH"){
        $pdf->SetXY(169, $parameter_Y); 
        $pdf->tCell(5, 4, $passport_id, 0, 0, "L");
    }
    else{
        $pdf->SetXY(162, $parameter_Y); 
        $pdf->tCell(5, 4, $passport_id, 0, 0, "L");
    }

    $print_convert_month = ($leg_moed == "TH"? $sFullMTh[$month]: $sFullMTh_EN[$month]);
    $print_convert_year = ($leg_moed == "TH"? $year+543: $year);

    if($leg_moed == "TH"){
        $parameter_Y = 115.5;
        $parameter_X = 3.5;
        $pdf->SetXY(128+$parameter_X, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(143.6+$parameter_X, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(175+$parameter_X, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

        $parameter_Y = 142;
        $parameter_X = 3.5;
        $pdf->SetXY(128+$parameter_X, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(143.6+$parameter_X, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(175+$parameter_X, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");
    }
    else{
        $parameter_Y = 124;
        $parameter_X = -7.5;
        $pdf->SetXY(123+$parameter_X, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(143.6+$parameter_X, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(175+$parameter_X, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

        $parameter_Y = 150.5;
        $parameter_X = -7;
        $pdf->SetXY(128+$parameter_X, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(143.6+$parameter_X, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(175+$parameter_X, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");
    }

    if($leg_moed == "TH"){
        $pdf->SetXY(73, 148.5); 
        $pdf->tCell(15, 4, $name_doc_th, 0, 0, "L");

        $pdf->SetXY(84, 155); 
        $pdf->tCell(15, 4, $doc_licen, 0, 0, "L");

        if($sex == "1")
            $pdf->SetHeaderImage("assets/image/oval_pdf.png", 34.5, 148, 16.5, 6.5); //+6.8
    }
    else{
        $pdf->SetXY(45, 155.5); 
        $pdf->tCell(15, 4, $name_doc_en, 0, 0, "L");

        $pdf->SetXY(155, 155.5); 
        $pdf->tCell(15, 4, $doc_licen, 0, 0, "L");
    }

    if($leg_moed == "TH"){
        $pdf->SetXY(73, 168); 
        $pdf->tCell(15, 4, $patient_name, 0, 0, "L");

        $parameter_Y = 174.8;
        $pdf->SetXY(47.6, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(68.5, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(102, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

        $parameter_Y = 181.3;
        $pdf->SetXY(43.5, $parameter_Y); 
        $pdf->tCell(15, 4, $weight, 0, 0, "L");
        $pdf->SetXY(66.5, $parameter_Y); 
        $pdf->tCell(21, 4, $height, 0, 0, "C");

        if($bph != ""){
            $pdf->SetXY(117.5, $parameter_Y); 
            $pdf->tCell(21, 4, $bph."/".$bpd, 0, 0, "C");
        }

        if($pr != ""){
            $pdf->SetXY(160, $parameter_Y); 
            $pdf->tCell(21, 4, $pr, 0, 0, "C");
        }

        if($physical_condition == "N"){
            $pdf->SetHeaderImage("assets/image/check_pdf.png", 63.4, 184.5, 11, 14);
        }
        else{
            $pdf->SetHeaderImage("assets/image/check_pdf.png", 78.2, 184.5, 11, 14);
            $pdf->SetXY(105, 190); 
            $pdf->tCell(89, 4, $physical_condition_text, 0, 0, "L"); //+3.8
        }

        $pdf->SetXY(58, 235.8); 
        $pdf->tCell(100, 4, $other_diseases_text, 0, 0, "L"); //+3.8

        $pdf->SetXY(26, 247); 
        $pdf->tMultiCell(162, 4.9, "                                                    ".$comment_text, 0, "L");

        if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
            $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 116, 265, 35, 8);
        }
    }
    else{ // EN
        $pdf->SetXY(71, 170.7); 
        $pdf->tCell(15, 4, $patient_name_en, 0, 0, "L");

        $parameter_Y = 175.7;
        $pdf->SetXY(44, $parameter_Y); 
        $pdf->tCell(10, 4, $day, 0, 0, "L");
        $pdf->SetXY(63, $parameter_Y); 
        $pdf->tCell(21, 4, $print_convert_month, 0, 0, "C");
        $pdf->SetXY(96, $parameter_Y); 
        $pdf->tCell(15, 4, $print_convert_year, 0, 0, "L");

        $parameter_Y = 181;
        $pdf->SetXY(39, $parameter_Y); 
        $pdf->tCell(15, 4, $weight, 0, 0, "L");
        $pdf->SetXY(63, $parameter_Y); 
        $pdf->tCell(21, 4, $height, 0, 0, "C");

        if($bph != ""){
            $pdf->SetXY(107, $parameter_Y); 
            $pdf->tCell(21, 4, $bph."/".$bpd, 0, 0, "C");
        }

        if($pr != ""){
            $pdf->SetXY(142, $parameter_Y); 
            $pdf->tCell(21, 4, $pr, 0, 0, "C");
        }

        if($physical_condition == "N"){
            $pdf->SetHeaderImage("assets/image/check_pdf.png", 59.8, 184, 11, 14);
        }
        else{
            $pdf->SetHeaderImage("assets/image/check_pdf.png", 81, 184, 11, 14);
            $pdf->SetXY(128.5, 189); 
            $pdf->tCell(89, 4, $physical_condition_text, 0, 0, "L"); //+3.8
        }

        $pdf->SetXY(55, 225.8); 
        $pdf->tCell(100, 4, $other_diseases_text, 0, 0, "L"); //+3.8

        $pdf->SetXY(26, 236); 
        $pdf->tMultiCell(162, 4.9, "                                                             ".$comment_text, 0, "L");

        if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
            $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 128.5, 252, 35, 8);
        }
    }

    $date_save_date = "";
    $name_file = "pdfoutput/MEDICAL_HEALTH"."_".$sUid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

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