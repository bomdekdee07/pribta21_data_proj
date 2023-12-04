<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $name_th = getQS("name_th");
    $name_en = getQS("name_en");
    $age = getQS("age");
    $dx = getQS("referral_diagnosis_edit", "-");
    $illness = getQS("referral_illness_edit", "-");
    $lab_result = getQS("referral_investigation_edit", "-");
    $treatment = getQS("referral_treatment_edit", "-");
    $reason = getQS("referral_reason_edit", "-");
    $other = getQS("referral_other_edit", "-");
    $format_date = getQS("format_date_referral", "TH");
    $stth = getQS("staffth");
    $sten = getQS("staffen");

    // signature
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_check_status_sign = "";
    $data_sign_sid = "";
    $data_type_leg = "";

    $query = "SELECT sig_status,
        s_id,
        sig_leg_type
    from i_doc_signatures
    where doc_code = 'MEDICAL_REFERRAL'
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
    $mysqli->close();

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");
    $sFullMTh = ["01"=>'มกราคม', "02"=>'กุมภาพันธ์', "03"=>'มีนาคม', "04"=>'เมษายน', "05"=>'พฤษภาคม', "06"=>'มิถุนายน', "07"=>'กรกฎาคม', "08"=>'สิงหาคม', "09"=>'กันยายน', "10"=>'ตุลาคม', "11"=>'พฤศจิกายน', "12"=>'ธันวาคม'];

    $date_print_now = $day." ".$sFullMTh[$month]." ".($year+543);//." ".$hours.":".$munite.":".$sec;
    $date_print_now_en = $year."-".$month."-".$day;

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/05_referral_certificate.jpg", 0, 0, 210, 297);
    $pdf->SetFont('THSarabun', '', 13.5);

    $pdf->SetXY(45, 66.4);
    $pdf->tCell(65, 4, "แพทย์ผู้เกี่ยวข้อง", 0, 0, "L");

    $pdf->SetXY(163, 66.4);
    if($format_date == "TH"){
        $pdf->tCell(35, 4, $date_print_now, 0, 0, "L");
    }

    if($format_date == "EN"){
        $dateNow_formate_str = "";
        $dateNow_formate_str = date("d F Y", strtotime($date_print_now_en));
        // $pdf->SetXY(155,96.5);
        $pdf->tCell(35, 4, $dateNow_formate_str, 0, 0, "L");
    }

    $pdf->SetXY(53, 75.5);
    $pdf->tCell(100, 4, $name_th, 0, 0, "L");

    $pdf->SetXY(53, 81);
    $pdf->tCell(100, 4, $name_en, 0, 0, "L");

    $pdf->SetXY(130, 75.5);
    $pdf->tCell(100, 4, $age, 0, 0, "L");

    $pdf->SetXY(163, 75.5);
    $pdf->tCell(100, 4, $uid, 0, 0, "L");

    $pdf->SetFont('THSarabun', '', 13);
    $pdf->SetXY(26, 87.3);
    $dx_con = strval($dx);
    $pdf->tMultiCell(161, 9.1, "                                                           ".str_replace("\\n", "\n", $dx_con), 0, "L");
    // echo "TEST:".$dx_con;

    $pdf->SetXY(26, 114.3);
    $illness_con = strval($illness);
    $pdf->tMultiCell(161, 9.1, "                                                        ".str_replace("\\n", "\n", $illness_con), 0, "L");

    $pdf->SetXY(26, 159.5);
    $lab_result_con = strval($lab_result);
    $pdf->tMultiCell(161, 9, "                                                                        ".str_replace("\\n", "\n", $lab_result_con), 0, "L");

    $pdf->SetXY(26, 186.3);
    $treatment_con = strval($treatment);
    $pdf->tMultiCell(161, 9.1, "                                                       ".str_replace("\\n", "\n", $treatment_con), 0, "L");

    $pdf->SetXY(26, 231.3);
    $pdf->tMultiCell(161, 9.1, "                                                          ".$reason, 0, "L");

    $pdf->SetXY(26, 240.3);
    $pdf->tMultiCell(161, 9.1, "                                       ".$other, 0, "L");

    if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
        $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 137, 252, 35, 8);
    }

    $pdf->SetFont('THSarabun', '', 12.5);
    $pdf->SetXY(126.5, 262.5);
    $pdf->tMultiCell(58, 5, $stth, 0, "C");

    $pdf->SetXY(126.5, 266.5);
    $pdf->tMultiCell(58, 5, $sten, 0, "C");

    // $pdf->Output(); //TEST

    $date_save_date = "";
    $name_file = "pdfoutput/MEDICAL_REFERRAL"."_".$uid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

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