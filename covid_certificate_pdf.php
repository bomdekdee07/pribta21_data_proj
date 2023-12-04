<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = getQS("coltime");
    $status_live_day = getQS("live_day");
    $many_day = getQS("many_day");
    $start_day = getQS("start_day");
    $stop_day = getQS("stop_day");
    $status_other = getQS("status_other");    
    $other_text = getQS("other_text");
    $format_date = getQS("format_date", "TH");
    $method_pcr = getQS("method_pcr");
    $method_atk = getQS("method_atk");
    $result_pcr = getQS("result_pcr");
    $result_atk = getQS("result_atk");
    // echo $method_pcr."/".$method_atk;

    $bind_param = "sss";
    $array_val = array($sUid, $sColDate, $sColTime);
    $data_check_status_sign = "";
    $data_sign_sid = "";
    $data_type_leg = "";

    $query = "SELECT sig_status,
        s_id,
        sig_leg_type
    from i_doc_signatures
    where doc_code = 'MEDICAL_COVID'
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

    $dianosis_edit = "";
    if($format_date == "TH" && $method_pcr == "true" && $method_atk == "false"){
        $dianosis_edit = "ตรวจโควิด 19 โดยวิธี: SARS-CoV-2 RT-PCR";
    }
    else if($format_date == "TH" && $method_atk == "true" && $method_pcr == "false"){
        $dianosis_edit = "ตรวจโควิด 19 โดยวิธี: ATK (Rapid Antigen Test)";
    }
    else if($format_date == "TH" && $method_atk == "true" && $method_pcr == "true"){
        $dianosis_edit = "ตรวจโควิด 19 โดยวิธี: - SARS-CoV-2 RT-PCR
                                                           - ATK (Rapid Antigen Test)";
    }
    else if($format_date == "EN" && $method_pcr == "true" && $method_atk != "true"){
        $dianosis_edit = "Covid 19 testing by: SARS-CoV-2 RT-PCR";
    }
    else if($format_date == "EN" && $method_atk == "true" && $method_pcr != "true"){
        $dianosis_edit = "Covid 19 testing by: ATK (Rapid Antigen Test)";
    }
    else if($format_date == "EN" && $method_atk == "true" && $method_pcr == "true"){
        $dianosis_edit = "Covid 19 testing by: - SARS-CoV-2 RT-PCR
                                                            - ATK (Rapid Antigen Test)";
    }

    $phy_opnion = "";
    if($format_date == "TH" && $result_pcr == "Y" && $result_atk != "Y" && $result_atk != "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: พบเชื้อ";
    }
    else if($format_date == "TH" && $result_pcr == "N" && $result_atk != "Y" && $result_atk != "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: ไม่พบเชื้อ";
    }
    else if($format_date == "EN" && $result_pcr == "Y" && $result_atk != "Y" && $result_atk != "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Positive";
    }
    else if($format_date == "EN" && $result_pcr == "N" && $result_atk != "Y" && $result_atk != "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Negative";
    }
    if($format_date == "TH" && $result_atk == "Y" && $result_pcr != "Y" && $result_pcr != "N"){
        $phy_opnion = "ATK (Rapid Antigen Test): พบเชื้อ";
    }
    else if($format_date == "TH" && $result_atk == "N" && $result_pcr != "Y" && $result_pcr != "N"){
        $phy_opnion = "ATK (Rapid Antigen Test): ไม่พบเชื้อ";
    }
    else if($format_date == "EN" && $result_atk == "Y" && $result_pcr != "Y" && $result_pcr != "N"){
        $phy_opnion = "ATK (Rapid Antigen Test): Positive";
    }
    else if($format_date == "EN" && $result_atk == "N" && $result_pcr != "Y" && $result_pcr != "N"){
        $phy_opnion = "ATK (Rapid Antigen Test): Negative";
    }
    else if($format_date == "TH" && $result_pcr == "Y" && $result_atk == "Y"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: พบเชื้อ
                                                   ATK (Rapid Antigen Test): พบเชื้อ";
    }
    else if($format_date == "TH" && $result_pcr == "N" && $result_atk == "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: ไม่พบเชื้อ
                                                   ATK (Rapid Antigen Test): ไม่พบเชื้อ";
    }
    else if($format_date == "TH" && $result_pcr == "Y" && $result_atk == "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: พบเชื้อ
                                                   ATK (Rapid Antigen Test): ไม่พบเชื้อ";
    }
    else if($format_date == "TH" && $result_pcr == "N" && $result_atk == "Y"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: ไม่พบเชื้อ
                                                   ATK (Rapid Antigen Test): พบเชื้อ";
    }
    else if($format_date == "EN" && $result_pcr == "Y" && $result_atk == "Y"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Positive
                                                   ATK (Rapid Antigen Test): Positive";
    }
    else if($format_date == "EN" && $result_pcr == "N" && $result_atk == "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Negative
                                                   ATK (Rapid Antigen Test): Negative";
    }
    else if($format_date == "EN" && $result_pcr == "Y" && $result_atk == "N"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Positive
                                                   ATK (Rapid Antigen Test): Negative";
    }
    else if($format_date == "EN" && $result_pcr == "N" && $result_atk == "Y"){
        $phy_opnion = "SARS-CoV-2 RT-PCR: Negative
                                                   ATK (Rapid Antigen Test): Positive";
    }

    $sFullMTh = ["01"=>'มกราคม', "02"=>'กุมภาพันธ์', "03"=>'มีนาคม', "04"=>'เมษายน', "05"=>'พฤษภาคม', "06"=>'มิถุนายน', "07"=>'กรกฎาคม', "08"=>'สิงหาคม', "09"=>'กันยายน', "10"=>'ตุลาคม', "11"=>'พฤศจิกายน', "12"=>'ธันวาคม'];

    $data_mdc_certificate = array();
    $query = "select st.s_name, 
        st.license_md, 
        p_data_stmd.uid, 
        p_data_stmd.collect_date, 
        p_data_stmd.collect_time,
        ptinfo.fname,
        ptinfo.sname,
        ptinfo.en_fname,
        ptinfo.en_sname,
        st.s_name_en,
        ptinfo.passport_id
    from p_data_result p_data_stmd
    left join p_staff st on(st.s_id = p_data_stmd.data_result)
    left join patient_info ptinfo on(ptinfo.uid = p_data_stmd.uid)
    where p_data_stmd.uid = ?
    and p_data_stmd.collect_date = ?
    and p_data_stmd.collect_time = ?
    and p_data_stmd.data_id in ('staff_md');";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("sss", $sUid, $sColDate, $sColTime);

    if($stmt -> execute()){
        $stmt -> bind_result($s_name, $license_lab, $uid, $collect_date, $collect_time, $fname, $sname, $en_fname, $en_sname, $s_name_en, $passport_id);
        while($stmt -> fetch()){
            $data_mdc_certificate["name_doc"] = $s_name;
            $data_mdc_certificate["licen"] = $license_lab;
            $data_mdc_certificate["uid"] = $uid;
            $data_mdc_certificate["coldate_time"] = $collect_date." ".$collect_time;
            $data_mdc_certificate["name_pateint"] = isset($fname)?$fname." ".$sname : $en_fname." ".$en_sname;
            $data_mdc_certificate["name_pateint_en"] = $en_fname." ".$en_sname;
            $data_mdc_certificate["doctor_name_en"] = $s_name_en;
            $data_mdc_certificate["passport"] = $passport_id;
        }
        // print_r($data_mdc_certificate);
    }
    $stmt->close();
    $mysqli->close();

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    $date_print_now = $day." ".$sFullMTh[$month]." ".($year+543);//." ".$hours.":".$munite.":".$sec;
    $date_print_now_en = $year."-".$month."-".$day;

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/08_9_10_covid_certificate.jpg", 0, 0, 210, 297);
    $pdf->SetFont('THSarabun', '', 13.5);

    if(count($data_mdc_certificate) > 0){
        $pdf->SetXY(155,91);
        if($format_date == "TH"){
            $pdf->tCell(35, 4, $date_print_now, 0, 0, "L");
        }

        if($format_date == "EN"){
            $dateNow_formate_str = "";
            $dateNow_formate_str = date("d F Y", strtotime($date_print_now_en));
            // $pdf->SetXY(155,96.5);
            $pdf->tCell(35, 4, $dateNow_formate_str, 0, 0, "L");
        }

        $pdf->SetXY(39,113);
        $pdf->tCell(65, 4, $data_mdc_certificate["name_doc"], 0, 0, "L");
        $pdf->SetX(153.5);
        $pdf->tCell(30, 4, $data_mdc_certificate["licen"], 0, 0, "L");
        //name doctor 
        $pdf->SetXY(53,118.4);
        $pdf->tCell(65, 4, $data_mdc_certificate["doctor_name_en"], 0, 0, "L");
        //name TH
        $pdf->SetXY(59.5,124);
        $pdf->tCell(67, 4, $data_mdc_certificate["name_pateint"], 0, 0, "L");
        //uid
        $pdf->SetX(158.5);
        $pdf->tCell(30, 4, $data_mdc_certificate["uid"], 0, 0, "L");    
        //name EN
        $pdf->SetXY(71.5,130);
        $pdf->tCell(67, 4, $data_mdc_certificate["name_pateint_en"], 0, 0, "L");

        $convert_date =  date_create($data_mdc_certificate["coldate_time"]);
        $convert_day = date_format($convert_date,"d");
        $convert_month = date_format($convert_date,"m");
        $convert_year = date_format($convert_date, "Y")+543;
        $convert_time = "";//date_format($convert_date, "H:i:s");
        $pdf->SetXY(50,135.5);
        if($format_date == "TH"){
            $pdf->tCell(67, 4, $convert_day." ".$sFullMTh[$convert_month]." ".$convert_year." ".$convert_time, 0, 0, "L");
        }
        
        if($format_date == "EN"){
            $collect_formate_str = "";
            $collect_formate_str = date("d F Y", strtotime($data_mdc_certificate["coldate_time"]));
            // $pdf->SetXY(50,141);
            $pdf->tCell(67, 4, $collect_formate_str, 0, 0, "L");
        }

        $pdf->SetX(158);
        $pdf->tCell(67, 4, $data_mdc_certificate["passport"], 0, 0, "L");

        $pdf->SetXY(30,147);
        $pdf->tMultiCell(149, 5.5, "                               ".$dianosis_edit, 0, "L");
    }

    $pdf->SetXY(30,169);
    $pdf->tMultiCell(149, 5.9, "                                                   ".$phy_opnion, 0, "L");

    if($status_live_day == "on"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 24, 180, 12, 19);

        $pdf->SetXY(86,188);
        $pdf->tCell(65, 4, $many_day, 0, 0, "L");
        
        $convert_date =  date_create($start_day);
        $convert_day = date_format($convert_date,"d");
        $convert_month = date_format($convert_date,"m");
        $convert_year = date_format($convert_date, "Y")+543;
        $pdf->SetX(123.5);
        if($format_date == "TH")
            $pdf->tCell(65, 4, $convert_day." ".$sFullMTh[$convert_month]." ".$convert_year, 0, 0, "L");
        if($format_date == "EN"){
            $collect_formate_str = "";
            $collect_formate_str = date("d F Y", strtotime($start_day));
            // $pdf->SetX(126.5);
            $pdf->tCell(65, 4, $collect_formate_str, 0, 0, "L");
        }
        $convert_date =  date_create($stop_day);
        $convert_day = date_format($convert_date,"d");
        $convert_month = date_format($convert_date,"m");
        $convert_year = date_format($convert_date, "Y")+543;
        $pdf->SetX(155);
        if($format_date == "TH")
            $pdf->tCell(65, 4, $convert_day." ".$sFullMTh[$convert_month]." ".$convert_year, 0, 0, "L");
        if($format_date == "EN"){
            $collect_formate_str = "";
            $collect_formate_str = date("d F Y", strtotime($stop_day));
            // $pdf->SetX(160);
            $pdf->tCell(65, 4, $collect_formate_str, 0, 0, "L");
        }

        // $pdf->SetXY(86.5,194);
        // $pdf->tCell(65, 4, $many_day, 0, 0, "L");
    }
    else{
        $pdf->SetXY(86,188);
        $pdf->tCell(65, 4, "-", 0, 0, "L");

        $pdf->SetX(126.5);
        $pdf->tCell(65, 4, "-", 0, 0, "L");

        $pdf->SetX(160);
        $pdf->tCell(65, 4, "-", 0, 0, "L");
    }

    if($status_other == "on"){
        $pdf->SetHeaderImage("assets/image/check_pdf.png", 24, 191, 12, 19);

        $pdf->SetXY(47,198.6);
        $pdf->tMultiCell(149, 5.9, $other_text, 0, "L");
    }
    else{
        $pdf->SetXY(47,198.6);
        $pdf->tMultiCell(149, 5.9, "-", 0, "L");
    }

    if(count($data_mdc_certificate) > 0){
        $pdf->SetFont('THSarabun', '', 12);

        $pdf->SetXY(121,241.5);
        $pdf->tCell(65, 4, $data_mdc_certificate["name_doc"], 0, 0, "C");

        $pdf->SetXY(121,245);
        $pdf->tCell(65, 4, $data_mdc_certificate["doctor_name_en"], 0, 0, "C");
    }

    // echo $data_check_status_sign."/".$data_check_status_sign."/".$data_sign_sid;
    if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
        $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 134.5, 230, 35, 8);
    }

    // $pdf->Output(); //TEST
    $date_save_date = "";
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");
    $name_file = "pdfoutput/MEDICAL_COVID"."_".$sUid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

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