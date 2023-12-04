<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $mode_type = getQS("doc_mode");
    $interpret_val = getQS("txt_interpret");
    $interpret_uid = getQS("interpret_uid");
    $interpret_laborder_id = getQS("interpret_laborder_id");
    $interpret_language_type = getQS("interpret_language_type");
    // echo $interpret_val."/".$interpret_uid."/".$interpret_laborder_id;

    $bind_param = "s";
    $array_val = array($interpret_laborder_id);
    $data_detail_head = array();
    $received_if = "";
    $tmp_diff = "";
    $tmp_age = "";

    $query = "SELECT lab_o.lab_order_id,
        lab_o.uid,
        lab_o.collect_date,
        lab_o.collect_time,
        lab_o.time_specimen_collect,
        lab_o.lab_specimen_receive,
        patient.fname,
        patient.sname,
        patient.en_fname,
        patient.en_sname,
        patient.date_of_birth
    from p_lab_order lab_o
    left join patient_info patient on(patient.uid = lab_o.uid)
    where lab_o.lab_order_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_detail_head["lab_order_id"] = $row["lab_order_id"];
            $data_detail_head["uid"] = $row["uid"];
            $data_detail_head["patient_name_th"] = $row["fname"]." ".$row["sname"];
            $data_detail_head["patient_name_en"] = $row["en_fname"]." ".$row["en_sname"];
            $data_detail_head["visit"] = (new DateTime($row["collect_date"]." ".$row["collect_time"]))->format('d/m/Y H:i');
            $data_detail_head["dob"] = (new DateTime($row["date_of_birth"]))->format('d/m/Y');

            $tmp_diff = abs(strtotime(date("Y-m-d")) - strtotime($row["date_of_birth"]));
            $tmp_age = floor($tmp_diff / (365*60*60*24));
            $data_detail_head["age"] = $tmp_age;

            if($row["lab_specimen_receive"] == "")
                $received_if = $row["time_specimen_collect"];
            else
                $received_if = $row["lab_specimen_receive"];
                
            $data_detail_head["received"] = (new DateTime($received_if))->format('d/m/Y H:i');
        }
    }

    $patient_name = "";
    if($interpret_language_type == "TH"){
        $patient_name = $data_detail_head["patient_name_th"];
    }
    else{
        $patient_name = $data_detail_head["patient_name_en"];
    }

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/interpret_view.jpg", 0, 0, 210, 297);

    // HEAD
    $pdf->SetFont('THSarabun', '', 12);
    // Tตั้ง Xนอน
    $pdf->SetXY(45, 44.6); 
    $pdf->tCell(65, 4, $patient_name, 0, 0, "L");
    $pdf->SetXY(136, 44.6); 
    $pdf->tCell(65, 4, $data_detail_head["uid"], 0, 0, "L");

    $pdf->SetXY(45, 49.1); 
    $pdf->tCell(65, 4, $data_detail_head["age"]." "."YEAR", 0, 0, "L");
    $pdf->SetXY(136, 49.1); 
    $pdf->tCell(65, 4, $data_detail_head["lab_order_id"], 0, 0, "L");
    
    $pdf->SetXY(45, 53.7); 
    $pdf->tCell(65, 4, $data_detail_head["dob"], 0, 0, "L");
    $pdf->SetXY(136, 53.7); 
    $pdf->tCell(65, 4, $data_detail_head["visit"], 0, 0, "L");

    $pdf->SetXY(136, 58.3); 
    $pdf->tCell(65, 4, $data_detail_head["received"], 0, 0, "L");

    $pdf->SetXY(19.5, 71); 
    $pdf->tMultiCell(175, 4.9, $interpret_val, 0, "L");

    $pdf->SetPageNo(270, 195, $stxt="Page {p}/{tp}", "THSarabun", "", 11);

    if($mode_type == "view"){
        $pdf->Output();
    }   
?>