<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $doc_mode = getQS("doc_mode");
    $type_leg = getQS("type_leg");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $uic = getQS("uic");
    $age = getQS("age");
    $name = getQS("name");
    $cn_dx = getQS("cn_dx");
    $drug_allergy_txt = getQS("drug_allergy_txt");

    // get $_POST to array
    // loop no
    $no_prescription_array = array();
    $order_prescription_array = array();
    $total_prescription_array = array();

    foreach($_POST["no_prescription"] AS $no_prescription){
        $no_prescription_array[] = $no_prescription;
    }
     // loop order
    foreach($_POST["order_prescription"] AS $order_prescription){
        $order_prescription_array[] = $order_prescription;
    }

    // loop total
    foreach($_POST["total_prescription"] AS $total_prescription){
        $total_prescription_array[] = $total_prescription;
    }
    // loop total
    foreach($_POST["unit_prescription"] AS $unit_prescription){
        $unit_prescription_array[] = $unit_prescription;
    }
    // parameter for loop write row
    $count_no_array = count($no_prescription_array);

    // Query md info
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT
        pst.s_name,
        pst.s_name_en,
        pst.license_md
    FROM
        p_data_result dtr
        join p_staff pst on(pst.s_id = dtr.data_result)
    WHERE
        dtr.uid = ?
        AND dtr.collect_date = ?
        AND dtr.collect_time = ?
        AND dtr.data_id = 'staff_md';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $data_md_val = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_md_val["name"] = ($type_leg == "TH"? $row["s_name"]: $row["s_name_en"]);
            $data_md_val["license"] = $row["license_md"];
        }
    }
    $stmt->close();

    // Query signature
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);
    $data_check_status_sign = "";
    $data_sign_sid = "";
    $data_type_leg = "";

    $query = "SELECT sig_status,
        s_id,
        sig_leg_type
    from i_doc_signatures
    where doc_code = 'MEDICAL_PRESCRIPTION'
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
    $sFullMTh_EN = ["01"=>'January', "02"=>'Febuary', "03"=>'March', "04"=>'April', "05"=>'May', "06"=>'June', "07"=>'July', "08"=>'August', "09"=>'September', "10"=>'October', "11"=>'November', "12"=>'December'];

    $date_print_now = $day." ".$sFullMTh[$month]." ".($year+543);//." ".$hours.":".$munite.":".$sec;
    $date_print_now_en = $day." ".$sFullMTh_EN[$month]." ".$year;
    $today_val = ($type_leg == "TH")? $date_print_now: $date_print_now_en;

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/06_prescription_certificate.jpg", 0, 0, 210, 297);

    $pdf->SetFont('THSarabun', '', 14);
    $pdf->SetXY(165, 66.4);
    $pdf->tCell(38, 4, $today_val, 0, 0, "C");

    $pdf->SetXY(55, 78.5);
    $pdf->tCell(65, 4, $name, 0, 0, "L");

    $pdf->SetXY(184, 78.5);
    $pdf->tCell(30, 4, $age, 0, 0, "L");

    $pdf->SetXY(67, 91.5);
    $pdf->tCell(30, 4, $uid, 0, 0, "L");

    $pdf->SetXY(113, 91.5);
    $pdf->tCell(30, 4, $uic, 0, 0, "L");

    $pdf->SetFont('THSarabun', '', 13.5);
    $pdf->SetXY(50, 101.5);
    $pdf->tMultiCell(153, 4, $cn_dx, 0, "L");

    $pdf->SetXY(50, 115);
    $pdf->tMultiCell(153, 4, $drug_allergy_txt, 0, "L");

    //Set Start
    $pdf->SetTopMargin(144);//74
    $pdf->SetTableColWidth(array(30, 120, 17, 20));
    $pdf->SetTableColOrient(array("C", "L", "C", "L"));
    $pdf->SetTableLineHeight(4.1);
    $pdf->SetTableLineMargin(4);
    $pdf->SetLeftMargin(1);
    $pdf->SetX(0);

    // loop write all
    for($x = 0; $x < $count_no_array; $x++){
        $pdf->writeRow(array(
            $no_prescription_array[$x],
            $order_prescription_array[$x],
            $total_prescription_array[$x],
            $unit_prescription_array[$x]
        ));
    }

    // detail doctor
    $pdf->SetFont('THSarabun', '', 14);
    $pdf->SetXY(121, 263);
    $pdf->tCell(65, 4, $data_md_val["name"], 0, 0, "C");

    $pdf->SetXY(135, 272.3);
    $pdf->tCell(30, 4, $data_md_val["license"], 0, 0, "L");

    if($data_check_status_sign != "" && $data_check_status_sign == "1" && $data_sign_sid != ""){
        $pdf->SetHeaderImage("staff_signature/".$data_sign_sid."_".$data_type_leg.".png", 135, 248, 35, 8);
    }

    $date_save_date = "";
    $name_file = "pdfoutput/MEDICAL_PRESCRIPTION"."_".$uid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

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