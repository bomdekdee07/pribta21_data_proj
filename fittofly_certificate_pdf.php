<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $name = getQS("name");
    $passport = getQS("passport");
    $birthday = getQS("birthday");
    $nation = getQS("nation");
    $date_result = getQS("date_result");
    $time_result = getQS("time_result");
    $diagnosis = getQS("diagnosis");
    $result = getQS("result");

    $sFullMTh = ["01"=>'มกราคม', "02"=>'กุมภาพันธ์', "03"=>'มีนาคม', "04"=>'เมษายน', "05"=>'พฤษภาคม', "06"=>'มิถุนายน', "07"=>'กรกฎาคม', "08"=>'สิงหาคม', "09"=>'กันยายน', "10"=>'ตุลาคม', "11"=>'พฤศจิกายน', "12"=>'ธันวาคม'];

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
    where doc_code = 'MEDICAL_FITTOFLY'
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

    // detail
    $bind_val = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_info_md = array();
    
    $query = "SELECT staff.s_name_en,
        staff.license_md
    from p_data_result p_data
    left join p_staff staff on(staff.s_id = p_data.data_result)
    where p_data.data_id = 'staff_md' 
    and p_data.uid = ?
    and p_data.collect_date = ?
    and p_data.collect_time = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_val, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($s_name_en, $license_md);
        while($stmt->fetch()){
            $data_info_md["name"] = $s_name_en;
            $data_info_md["no_md"] = $license_md;
        }
    }

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");

    $date_print_now = $day." ".$sFullMTh[$month]." ".($year+543);//." ".$hours.":".$munite.":".$sec;
    $date_print_now_en = $year."-".$month."-".$day;
    $dateNow_formate_str = date("d F Y", strtotime($date_print_now_en));
    $data_print_now_normal = $day."/".$month."/".$year;

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/10_fittofly_certificate.jpg", 0, 0, 210, 297);
    $pdf->SetFont('THSarabun', '', 13.5);

    $pdf->SetXY(55,65.5);
    $pdf->tCell(65, 4, $dateNow_formate_str, 0, 0, "L");

    $pdf->SetXY(35,79);
    $pdf->tCell(65, 4, $uid, 0, 0, "L");

    $pdf->SetXY(93,79);
    $pdf->tCell(65, 4, $name, 0, 0, "L");

    $pdf->SetXY(70,85);
    $pdf->tCell(65, 4, $passport, 0, 0, "L");

    $pdf->SetXY(137,85);
    $pdf->tCell(65, 4, $birthday, 0, 0, "L");

    $pdf->SetXY(70,90.9);
    $pdf->tCell(65, 4, $nation, 0, 0, "L");

    $pdf->SetXY(70,97.5);
    $pdf->tCell(65, 4, $date_result, 0, 0, "L");

    $pdf->SetXY(137,97.5);
    $pdf->tCell(65, 4, $time_result, 0, 0, "L");

    $pdf->SetXY(24.5,120);
    $diagnosis_con = strval($diagnosis);
    $pdf->tMultiCell(160, 5.5, str_replace("\\n", "\n", $diagnosis_con), 0, "L");

    $pdf->SetFont('THSarabun', 'B', 13);
    $pdf->SetXY(24.5,162);
    $result_con = strval($result);
    $pdf->tCell(65, 4, str_replace("\\n", "\n", $result_con), 0, 0, "L");

    $pdf->SetFont('THSarabun', '', 13);
    $pdf->SetXY(56,216.5);
    $pdf->tCell(65, 4, $name, 0, 0, "L");

    $pdf->SetXY(40,229.5);
    $pdf->tCell(65, 4, $data_print_now_normal, 0, 0, "L");

    if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
        $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 129, 220, 35, 8);
    }

    $pdf->SetXY(135,216.5);
    $pdf->tCell(65, 4, isset($data_info_md["name"])? $data_info_md["name"]: "", 0, 0, "L");

    $pdf->SetXY(135,229.5);
    $pdf->tCell(65, 4, isset($data_info_md["no_md"])? $data_info_md["no_md"]: "", 0, 0, "L");

    $pdf->SetXY(110,235.6);
    $pdf->tCell(65, 4, $data_print_now_normal, 0, 0, "L");

    // $pdf->Output(); //TEST
    $name_file = "pdfoutput/MEDICAL_FITTOFLY"."_".$uid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

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