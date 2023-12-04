<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $suid = getQS("uid");
    $scoldate = getQS("coldate");
    $clinic_id = getSS("clinic_id");

    function getAgeDetail_con($dob){
        $dob_a = explode("-", $dob);
        $today_a = explode("-", date("Y-m-d"));
        $dob_d = $dob_a[2];$dob_m = $dob_a[1];$dob_y = $dob_a[0];
        $today_d = $today_a[2];$today_m = $today_a[1];$today_y = $today_a[0];
        $years = $today_y - $dob_y;
        $months = $today_m - $dob_m;
        $days=$today_d - $dob_d;
        if ($today_m.$today_d < $dob_m.$dob_d) {
            $years--;
            $months = 12 + $today_m - $dob_m;
        }
    
        if ($today_d < $dob_d){
            $months--;
        }
    
        $firstMonths=array(1,3,5,7,8,10,12);
        $secondMonths=array(4,6,9,11);
        $thirdMonths=array(2);
    
        if($today_m - $dob_m == 1){
            if(in_array($dob_m, $firstMonths)){
                array_push($firstMonths, 0);
            }elseif(in_array($dob_m, $secondMonths)) {
                array_push($secondMonths, 0);
            }elseif(in_array($dob_m, $thirdMonths)){
                array_push($thirdMonths, 0);
            }
        }
    
        return $years;
    }

    $bind_param = "ss";
    $array_val = array($suid, $scoldate);
    $data_hormon = array();

    $query = "SELECT homon_women.data_id as homon_women_id,
        homon_women.data_result as homon_women_result,
        homon_men.data_id as homon_men_id,
        homon_men.data_result as homon_men_result,
        dx_result.data_result as dx_result,
        E2_sign_result.data_result as E2_sign_result,
        T_sign_result.data_result as T_sign_result,
        proj_list.pid,
        patient.fname,
        patient.sname,
        patient.date_of_birth,
        clinic.clinic_name
    from p_data_result homon_women
    left join p_data_result homon_men on(homon_men.uid = homon_women.uid and homon_men.collect_date = homon_women.collect_date and homon_men.collect_time = homon_women.collect_time and homon_men.data_id = 'T')
    left join p_data_result dx_result on(dx_result.uid = homon_women.uid and dx_result.collect_date = homon_women.collect_date and dx_result.collect_time = homon_women.collect_time and dx_result.data_id = 'cn_dx')
    left join p_data_result E2_sign_result on (E2_sign_result.uid = homon_women.uid and E2_sign_result.collect_date = homon_women.collect_date and E2_sign_result.collect_time = homon_women.collect_time and E2_sign_result.data_id = 'E2_sign')
    left join p_data_result T_sign_result on (T_sign_result.uid = homon_women.uid and T_sign_result.collect_date = homon_women.collect_date and T_sign_result.collect_time = homon_women.collect_time and T_sign_result.data_id = 'T_sign')
    left join p_project_uid_list proj_list on(proj_list.proj_id = 'HORMONES' and proj_list.uid = homon_women.uid)
    left join patient_info patient on(patient.uid = homon_women.uid)
    left join p_clinic clinic on(clinic.clinic_id = proj_list.clinic_id)
    where homon_women.data_id = 'E2'
    and homon_women.uid = ?
    and homon_women.collect_date = ?
    and homon_women.collect_time = '00:00:00';";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($women_id, $women_result, $men_id, $men_result, $dx_result, $E2_sign_result, $T_sign_result, $pid, $fname, $sname, $date_of_birth, $clinic_name);
        while($stmt->fetch()){
            $data_hormon["women_result"] = $women_result;
            $data_hormon["men_result"] = $men_result;
            $data_hormon["dx_result"] = $dx_result;
            $data_hormon["E2_sign_result"] = $E2_sign_result;
            $data_hormon["T_sign_result"] = $T_sign_result;
            $data_hormon["pid"] = $pid;
            $data_hormon["name_patient"] = $fname." ".$sname;
            $data_hormon["birth_day"] = $date_of_birth;
            $data_hormon["clinic_name"] = $clinic_name;
            $data_hormon["uid"] = $suid;
            $data_hormon["coldate"] = $scoldate;
        }
    }
    $stmt->close();
    $mysqli->close();

    // START NEW PAGE PDF
    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    $pdf->SetHeaderImage("assets/image/project_certificate.jpg", 0, 0, 210, 297);
    $pdf->SetFont('THSarabun', '', 13.5);

    $date_st_format = date("d F Y", strtotime($data_hormon["coldate"]));
    $pdf->SetXY(68, 48.5);
    $pdf->tCell(50, 4, $date_st_format, 0, 0, "L");
    $pdf->SetXY(153, 48.5);
    $pdf->tCell(80, 4, $data_hormon["clinic_name"], 0, 0, "L");

    $pdf->SetXY(68, 54.4);
    $pdf->tCell(50, 4, $data_hormon["uid"], 0, 0, "L");
    $pdf->SetXY(153, 54.4);
    $pdf->tCell(80, 4, $data_hormon["pid"], 0, 0, "L");

    $pdf->SetXY(68, 60.3);
    $pdf->tCell(200, 4, $data_hormon["name_patient"], 0, 0, "L");

    $date_st_format = date("d F Y", strtotime($data_hormon["birth_day"]));
    $pdf->SetXY(68, 66.5);
    $pdf->tCell(50, 4, $date_st_format, 0, 0, "L");

    $age_convert = getAgeDetail_con($data_hormon["birth_day"]);
    $pdf->SetXY(153, 66.5);
    $pdf->tCell(80, 4, $age_convert, 0, 0, "L");

    $pdf->SetXY(116.5, 85.5);
    $pdf->tCell(50, 4, $data_hormon["E2_sign_result"], 0, 0, "L");

    $pdf->SetXY(120, 86);
    $pdf->tCell(50, 4, $data_hormon["women_result"], 0, 0, "L");

    $pdf->SetXY(116.5, 92);
    $pdf->tCell(50, 4, $data_hormon["T_sign_result"], 0, 0, "L");

    $pdf->SetXY(120, 92.5);
    $pdf->tCell(50, 4, $data_hormon["men_result"], 0, 0, "L");

    $pdf->SetXY(20,113);
    $pdf->tMultiCell(170, 5.5, $data_hormon["dx_result"], 0, "L");

    $pdf->Output(); //TEST
?>