<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php");
    date_default_timezone_set("Asia/Bangkok");

    $sUid = isset($_POST["uid"])?$_POST["uid"]: getQS("uid");
    $sColDate = isset($_POST["coldate"])?$_POST["coldate"]: getQS("coldate");
    $sColTime = isset($_POST["coltime"])?$_POST["coltime"]: urldecode(getQS("coltime"));
    $doc_code = isset($_POST["doc_code"])?$_POST["doc_code"]: getQS("doc_code");
    // echo "TEST:".$sUid."/".$sColDate;

    function convert_textFull_con($dataID, $dataType, $data_result, $data_array){
        $rt_dataresult = "";
        
        if($dataType == "checkbox"){
            if($data_result == "1"){
                foreach($data_array as $key => $vlaue){
                    if($vlaue["data_id"] == $dataID){
                        $rt_dataresult = $vlaue["data_name_checkbox"];
                    }
                    else if($dataID == "cn_other_checkbox"){
                        $rt_dataresult = "have_other";
                    }
                }
            }
            else{
                $rt_dataresult = "";
            }
        }
        else if($dataType == "dropdown"){
            if($data_result != ""){
                foreach($data_array as $key => $vlaue){
                    if($vlaue["data_id"] == $dataID){
                        $rt_dataresult = $vlaue["data_name_name_staff"];
                    }
                }
            }
            else{
                $rt_dataresult = "";
            }
        }
        else if($dataType == "radio"){
            if($data_result != ""){
                foreach($data_array as $key => $vlaue){
                    if($vlaue["data_id"] == $dataID){
                        $rt_dataresult = $vlaue["data_name_radio"];
                    }
                }
            }
            else{
                $rt_dataresult = "";
            }
        }
        else if($dataType == "number" || $dataType == "text" || $dataType == "textarea"){
            if($data_result != ""){
                foreach($data_array as $key => $vlaue){
                    if($vlaue["data_id"] == $dataID){
                        $rt_dataresult = $vlaue["data_result"];
                    }
                }
            }
            else{
                $rt_dataresult = "";
            }
        }

        return $rt_dataresult;
    }

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
    
        return " $years ปี $months เดือน ".abs($days)." วัน";
    }

    $data_patient_info = array("uid" => "", "name" => "", "age" => "", "sex" => "", "citizenID" => "", "dateOFbirthday" => "", "blood" => "-", "nation" => "", "religion" => "", "tel" => "", "email" >= "", "line" => "");
    $con_sex = "-";
    $con_nation = "-";
    $con_religion = "-";
    $query = "select uid,
        fname, 
        sname,
        date_of_birth,
        sex,
        citizen_id,
        blood_type,
        nation,
        religion,
        tel_no,
        email
    from patient_info 
    where uid = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $sUid);

    if($stmt -> execute()){
        $stmt -> bind_result($uid, $fname, $sname, $date_of_birth, $sex, $citizen_id, $blood_type, $nation, $religion, $tel_no, $email);
        while($stmt -> fetch()){
            $data_patient_info["uid"] = $uid;
            $data_patient_info["name"] = $fname." ".$sname; //this
            $data_patient_info["age"] = getAgeDetail_con($date_of_birth); //this
            
            if($sex == 1){
                $con_sex = "ชาย";
            }
            else if($sex == 2){
                $con_sex = "หญิง";
            }
            $data_patient_info["sex"] = $con_sex; //this
            $data_patient_info["citizenID"] = $citizen_id;

            $date_con = date_create($date_of_birth);
            $nDay = date_format($date_con,"d");
            $nMonth = date_format($date_con,"m");
            $year = date_format($date_con,"Y");
            $data_patient_info["dateOFbirthday"] = $nDay."-".$nMonth."-".($year+543)." | ".$nDay."-".$nMonth."-".$year; //this
            $data_patient_info["blood"] = $blood_type;

            if($nation == 1){
                $con_nation = "ไทย";
            }
            $data_patient_info["nation"] = $con_nation; //this

            if($religion == 1){
                $con_religion = "ไม่ระบุ";
            }
            else if($religion == 2){
                $con_religion = "พุทธ (Buddhist)";
            }
            $data_patient_info["religion"] = $con_religion; //this
            $data_patient_info["tel"] = $tel_no;
            $data_patient_info["email"] = $email;
            $data_patient_info["line"] = "-";
        }
        // print_r($data_patient_info);
    }
    $stmt->close();

    // date lab
    $data_lab_date = "";
    $query = "select distinct collect_date
    from p_lab_result 
    where uid = ?
    and collect_date < ?
    order by collect_date DESC
    LIMIT 1;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("ss", $sUid, $sColDate);

    if($stmt -> execute()){
        $stmt -> bind_result($collect_date);
        $count_t = 0;
        while($stmt -> fetch()){
            $data_lab_date = $collect_date;
        }
        // print_r($data_lab);
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();

    // data lab present
    $data_lab = array();
    if($data_patient_info["sex"] == "ชาย"){
        $query = "select main.collect_date,
            main.lab_result_report, 
            val.lab_name,
            val.lab_result_min_male, 
            val.lab_result_max_male,
            main.lab_result_status as status
        from p_lab_result as main
        left join p_lab_test as val on (main.lab_id = val.lab_id)
        left join p_lab_status as status on (main.lab_result_status = status.id)
        where main.uid = ?
        and main.collect_date = ?
        order by main.barcode;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sUid, $sColDate);

        if($stmt -> execute()){
            $stmt -> bind_result($collect_date ,$lab_result_report, $lab_name, $lab_result_min, $lab_result_max, $status);
            $count_t = 0;
            while($stmt -> fetch()){
                $data_lab[$count_t]["date"] = $collect_date;
                $data_lab[$count_t]["report"] = $lab_result_report;
                $data_lab[$count_t]["lab_name"] = $lab_name;
                $data_lab[$count_t]["min"] = $lab_result_min;
                $data_lab[$count_t]["max"] = $lab_result_max;
                $data_lab[$count_t]["status"] = $status;

                $count_t = ($count_t+1);
            }
            // print_r($data_lab);
        }   
        else{
            $msg_error .= $stmt -> error;
        }
        $stmt->close();
    }
    else if($data_patient_info["sex"] == "หญิง"){
        $query = "select main.collect_date,
            main.lab_result_report, 
            val.lab_name,
            val.lab_result_min_female, 
            val.lab_result_max_female,
            main.lab_result_status as status
        from p_lab_result as main
        left join p_lab_test as val on (main.lab_id = val.lab_id)
        left join p_lab_status as status on (main.lab_result_status = status.id)
        where main.uid = ?
        and main.collect_date = ?
        order by main.barcode;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sUid, $sColDate);

        if($stmt -> execute()){
            $stmt -> bind_result($collect_date, $lab_result_report, $lab_name, $lab_result_min, $lab_result_max, $status);
            $count_t = 0;
            while($stmt -> fetch()){
                $data_lab[$count_t]["date"] = $collect_date;
                $data_lab[$count_t]["report"] = $lab_result_report;
                $data_lab[$count_t]["lab_name"] = $lab_name;
                $data_lab[$count_t]["min"] = $lab_result_min;
                $data_lab[$count_t]["max"] = $lab_result_max;
                $data_lab[$count_t]["status"] = $status;

                $count_t = ($count_t+1);
            }
            // print_r($data_lab);
        }   
        else{
            $msg_error .= $stmt -> error;
        }
        $stmt->close();
    }
    // print_r($data_lab);

    // data lab old
    $data_lab_old = array();
    if($data_patient_info["sex"] == "ชาย"){
        $query = "select main.collect_date,
            main.lab_result_report, 
            val.lab_name,
            val.lab_result_min_male, 
            val.lab_result_max_male,
            main.lab_result_status as status
        from p_lab_result as main
        left join p_lab_test as val on (main.lab_id = val.lab_id)
        left join p_lab_status as status on (main.lab_result_status = status.id)
        where main.uid = ?
        and main.collect_date = ?
        order by main.barcode;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sUid, $data_lab_date);

        if($stmt -> execute()){
            $stmt -> bind_result($collect_date ,$lab_result_report, $lab_name, $lab_result_min, $lab_result_max, $status);
            $count_t = 0;
            while($stmt -> fetch()){
                $data_lab_old[$count_t]["date"] = $collect_date;
                $data_lab_old[$count_t]["report"] = $lab_result_report;
                $data_lab_old[$count_t]["lab_name"] = $lab_name;
                $data_lab_old[$count_t]["min"] = $lab_result_min;
                $data_lab_old[$count_t]["max"] = $lab_result_max;
                $data_lab_old[$count_t]["status"] = $status;

                $count_t = ($count_t+1);
            }
            // print_r($data_lab);
        }   
        else{
            $msg_error .= $stmt -> error;
        }
        $stmt->close();
    }
    else if($data_patient_info["sex"] == "หญิง"){
        $query = "select main.collect_date,
            main.lab_result_report, 
            val.lab_name,
            val.lab_result_min_female, 
            val.lab_result_max_female,
            main.lab_result_status as status
        from p_lab_result as main
        left join p_lab_test as val on (main.lab_id = val.lab_id)
        left join p_lab_status as status on (main.lab_result_status = status.id)
        where main.uid = ?
        and main.collect_date = ?
        order by main.barcode;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sUid, $sColDate);

        if($stmt -> execute()){
            $stmt -> bind_result($collect_date, $lab_result_report, $lab_name, $lab_result_min, $lab_result_max, $status);
            $count_t = 0;
            while($stmt -> fetch()){
                $data_lab_old[$count_t]["date"] = $collect_date;
                $data_lab_old[$count_t]["report"] = $lab_result_report;
                $data_lab_old[$count_t]["lab_name"] = $lab_name;
                $data_lab_old[$count_t]["min"] = $lab_result_min;
                $data_lab_old[$count_t]["max"] = $lab_result_max;
                $data_lab_old[$count_t]["status"] = $status;

                $count_t = ($count_t+1);
            }
            // print_r($data_lab);
        }   
        else{
            $msg_error .= $stmt -> error;
        }
        $stmt->close();
    }

    $data_doctorMain = array();
    $query = "SELECT d.data_id, 
    d.data_result, 
    t.data_type,
    s.data_name_th as data_name_radio,
    c.data_name_th as data_name_checkbox,
    staff.s_name as data_name_name_staff
    FROM p_data_result d
        left join p_form_list_data t on (d.data_id = t.data_id)
        left join p_data_sub_list s on (d.data_id = s.data_id and d.data_result = s.data_value)
        left join p_data_list c on (d.data_id = c.data_id and d.data_result = '1' and c.data_type = 'checkbox')
        left join p_staff staff on (t.data_type = 'dropdown' and d.data_result like 'P%' and d.data_result = staff.s_id)
    WHERE d.uid = ?
    AND d.collect_date = ?
    AND d.collect_time = ?
    and t.form_id = 'PHYSICAIN_CHART'
    order by t.data_type, d.data_id;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("sss", $sUid, $sColDate, $sColTime);

    if($stmt -> execute()){
        $stmt -> bind_result($data_id, $data_result, $data_type, $data_name_radio, $data_name_checkbox, $data_name_name_staff);

        while($stmt -> fetch()){
            $data_doctorMain[$data_id]["data_id"] = $data_id;
            $data_doctorMain[$data_id]["data_result"] = $data_result;
            $data_doctorMain[$data_id]["data_type"] = $data_type;
            $data_doctorMain[$data_id]["data_name_radio"] = $data_name_radio;
            $data_doctorMain[$data_id]["data_name_checkbox"] = $data_name_checkbox;
            $data_doctorMain[$data_id]["data_name_name_staff"] = $data_name_name_staff;
        }
        // print_r($data_doctorMain);
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();
    
    // Lasted
    // $query = "SELECT d.data_id, 
    // d.data_result, 
    // t.data_type,
    // s.data_name_th as data_name_radio,
    // c.data_name_th as data_name_checkbox,
    // staff.s_name as data_name_name_staff
    // FROM p_data_result d
    //     left join p_form_list_data t on (d.data_id = t.data_id)
    //     left join p_data_sub_list s on (d.data_id = s.data_id and d.data_result = s.data_value) 
    //     left join p_data_list c on (d.data_id = c.data_id)
    //     left join p_staff staff on (t.data_type = 'dropdown' and d.data_result like 'P%' and d.data_result = staff.s_id)
    // WHERE d.uid = ?
    // AND d.collect_date = ?
    // AND d.collect_time = ?
    // AND c.data_category = '2'
    // and t.form_id = 'PHYSICAIN_CHART'
    // order by t.data_type, d.data_id;";

    // $stmt = $mysqli -> prepare($query);
    // $stmt -> bind_param("sss", $sUid, $sColDate, $sColTime);

    // if($stmt -> execute()){
    //     $stmt -> bind_result($data_id, $data_result, $data_type, $data_name_radio, $data_name_checkbox, $data_name_name_staff);

    //     while($stmt -> fetch()){
    //         $data_doctorMain[$data_id]["data_id"] = $data_id;
    //         $data_doctorMain[$data_id]["data_result"] = $data_result;
    //         $data_doctorMain[$data_id]["data_type"] = $data_type;
    //         $data_doctorMain[$data_id]["data_name_radio"] = $data_name_radio;
    //         $data_doctorMain[$data_id]["data_name_checkbox"] = $data_name_checkbox;
    //         $data_doctorMain[$data_id]["data_name_name_staff"] = $data_name_name_staff;
    //     }
    //     // print_r($data_doctorMain);
    // }   
    // else{
    //     $msg_error .= $stmt -> error;
    // }
    // $stmt->close();

    $data_date_old = "";
    $query = "SELECT collect_date 
    from p_data_result 
    where uid = ?
    AND collect_date < ?
    order by collect_date DESC
    LIMIT 1;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("ss", $sUid, $sColDate);

    if($stmt -> execute()){
        $stmt -> bind_result($collect_date);

        while($stmt -> fetch()){
            $data_date_old = $collect_date;
        }
        // echo $data_date_old;
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();

    // Old data
    $data_date_old_full = array();
    $query = "SELECT d.data_id, 
        d.data_result, 
        t.data_type,
        s.data_name_th as data_name_radio,
        c.data_name_th as data_name_checkbox,
        staff.s_name as data_name_name_staff
    FROM p_data_result d
        left join p_form_list_data t on (d.data_id = t.data_id)
        left join p_data_sub_list s on (d.data_id = s.data_id and d.data_result = s.data_value)
        left join p_data_list c on (d.data_id = c.data_id and d.data_result = '1' and c.data_type = 'checkbox')
        left join p_staff staff on (t.data_type = 'dropdown' and d.data_result like 'P%' and d.data_result = staff.s_id)
    WHERE d.uid = ?
    AND d.collect_date = ?
    and t.form_id = 'PHYSICAIN_CHART'
    order by d.collect_date DESC, t.data_type, d.data_id";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("ss", $sUid, $data_date_old);

    if($stmt -> execute()){
        $stmt -> bind_result($data_id, $data_result, $data_type, $data_name_radio, $data_name_checkbox, $data_name_name_staff);

        while($stmt -> fetch()){
            $data_date_old_full[$data_id]["data_id"] = $data_id;
            $data_date_old_full[$data_id]["data_result"] = $data_result;
            $data_date_old_full[$data_id]["data_type"] = $data_type;
            $data_date_old_full[$data_id]["data_name_radio"] = $data_name_radio;
            $data_date_old_full[$data_id]["data_name_checkbox"] = $data_name_checkbox;
            $data_date_old_full[$data_id]["data_name_name_staff"] = $data_name_name_staff;
        }
        // print_r($data_date_old_full);
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();
    $mysqli->close();

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $hours = date("H");
    $munite = date("i");
    $sec = date("s");
    $name_file = "pdfoutput/".$doc_code."_".$sUid."_".$year."".$month."".$day."".$hours."".$munite."".$sec;

    $pdf = new PDF();
    $pdf->SetThaiFont();
    $pdf->AddPage('P',"A4",'mm');

    //กำหนด Font Size
    // LOGO
    $width = 32;
    $height = 10;
    $pdf->Image('assets/image/all-combined-logos.png', 5.5, 5.5, -1800);
    // Text Head
    $pdf->SetTextColor(0);
    $pdf->SetFont('THSarabun', 'B', 13);
    $pdf->tText($width, $height, "Pribta Tangerine Polyclinic, Institute of HIV Research and Innovation");

    //กำหนด Font Size
    $pdf->SetFont('THSarabun', '', 12);
    $pdf->tText($width, $height+5, "พริบตา แทนเจอรีน สหคลินิก, สถาบันเพื่อการวิจัยและนวัตกรรมด้านเอชไอวี");
    $pdf->SetTextColor(50);
    $pdf->tText($width, $height+10, "11th Floor, Chamchuri Square Building, 319 Phayathai Road, Pathumwan, Bangkok, 10330 Tel: 02-160-5373: ใบอนุญาตเลขที่ 10110004863");

    // Line dash
    $pdf->SetLineWidth(0.5);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Line(6,22,204.5,22);

    // Head info
    $pdf->SetTextColor(0);
    $pdf->SetFont('THSarabun', '', 11);
    $check_data_found = count($data_patient_info);
    $pdf->SetXY(6, 21);
    $height = 9;
    $pdf->tCell(6, $height, "UID:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(20, $height, $data_patient_info["uid"], 0, 0, "L");
    }

    $pdf->tCell(15, $height, "ชื่อ-นามสกุล:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(25, $height, $data_patient_info["name"], 0, 0, "L");
    }

    $pdf->tCell(20, $height, "เลขบัตรประชาชน:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(25, $height, $data_patient_info["citizenID"], 0, 0, "L");
    }

    // Head info Row2
    $pdf->SetXY(6, 26);
    $pdf->tCell(8.5, $height, "วันเกิด:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(35, $height, $data_patient_info["dateOFbirthday"], 0, 0, "L");
    }

    $pdf->tCell(6.5, $height, "อายุ:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(28, $height, $data_patient_info["age"], 0, 0, "L");
    }

    $pdf->tCell(6.5, $height, "เพศ:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(22, $height, $data_patient_info["sex"], 0, 0, "L");
    }

    $pdf->tCell(12, $height, "กรุ๊ปเลือด:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(9.5, $height, $data_patient_info["blood"], 0, 0, "L");
    }

    $pdf->tCell(10, $height, "ประเทศ:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(10.5, $height, $data_patient_info["nation"], 0, 0, "L");
    }

    $pdf->tCell(9, $height, "ศาสนา:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(20, $height, $data_patient_info["religion"], 0, 0, "L");
    }

    // Head info Row3
    $pdf->SetXY(6, 31);
    $pdf->tCell(11, $height, "เบอร์โทร:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(22, $height, $data_patient_info["tel"], 0, 0, "L");
    }

    $pdf->tCell(7.5, $height, "อีเมล์:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(42, $height, $data_patient_info["email"], 0, 0, "L");
    }

    $pdf->tCell(6.5, $height, "ไลน์:", 0, 0, "L");
    if($check_data_found > 0){
        $pdf->tCell(35, $height, $data_patient_info["line"], 0, 0, "L");
    }

    // box detail 1
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(5.5,40,100,6); //(left page, height row, width, height_size)

    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(105.5,40,99,6); //(left page, height row, width, height_size)

    $pdf->SetXY(6,40.1);
    $height = 6;
    $pdf->tCell(12, $height, "แพ้อาหาร:", 0, 0, 'L');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "food_intolerance"){
                if($value["data_result"] == "Y"){
                    $pdf->tCell(34, $height, convert_textFull_con("food_intolerance_txt", "text", "Y", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(30, $height, "-", 0, 0, 'L');
                }
            }
        }
    }

    $pdf->tCell(8, $height, "แพ้ยา:", 0, 0, 'L');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "drug_allergy"){
                if($value["data_result"] == "Y"){
                    $pdf->tCell(46, $height, convert_textFull_con("drug_allergy_txt", "text", "Y", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(46, $height, "-", 0, 0, 'L');
                }
            }
        }
    }

    $pdf->tText(107, 44, "ประเภทผู้นำส่ง:");
    // $pdf->tCell(17.5, $height, "ประเภทผู้นำส่ง:", 0, 0, 'L');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_carrier_type"){
                if($value["data_result"] == "3"){
                    $pdf->tText(124, 44, convert_textFull_con("cn_carrier_type_text", "text", "3", $data_doctorMain));
                }
                else{
                    $pdf->tText(124, 44, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain));
                }
            }
        }
    }

    // box detail 2
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(5.5,46,199,6); //(left page, height row, width, height_size)

    $pdf->SetXY(6,46.1);
    $pdf->tCell(13, $height, "สุขภาพจิต:", 0, 0, 'L');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            $pos = strpos($key, "cn_psychological");
            if($pos !== false){
                if($value["data_result"] == "1"){
                    $pdf->tCell(13, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
            }
        }
    }
    
    $check_have_p = 0;
    // box detail 3
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(5.5,52,199,17.5); //(left page, height row, width, height_size)

    // row1
    $pdf->SetXY(6,52.1);
    $pdf->tCell(15, $height, "ประวัติคนไข้:", 0, 0, 'L');
    $pdf->tCell(20, $height, "โรคประจำตัว:", 0, 0, 'R');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "congenital_disease"){
                if($value["data_result"] == "Y"){
                    $pdf->tCell(38, $height, convert_textFull_con("congenital_disease_txt", "text", "1", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(38, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(38, $height, "-", 1, 0, 'C');
        }
        $check_have_p = 0;
    }

    $pdf->tCell(15, $height, "สูบบุหรี่:", 0, 0, 'R');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_smoking"){
                if($value["data_result"] != ""){
                    $pdf->tCell(42, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(42, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(42, $height, "-", 0, 0, 'L');
        }
        $check_have_p = 0;
    }

    $pdf->tCell(15, $height, "ยาประจำตัว:", 0, 0, 'R');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_take_regular_medication"){
                if($value["data_result"] == "2"){
                    $pdf->tCell(38, $height, convert_textFull_con("cn_current_medication_text", "text", "1", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(38, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(38, $height, "-", 0, 0, 'L');
        }
        $check_have_p = 0;
    }

    // row2
    $pdf->SetXY(26,57.6);
    $pdf->tCell(15, $height, "การผ่าตัด:", 0, 0, 'R');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "surgery"){
                if($value["data_result"] == "1"){
                    $pdf->tCell(38, $height, convert_textFull_con("surgery_txt", "text", "1", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(38, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(38, $height, "-", 0, 0, 'L');
        }
        $check_have_p = 0;
    }

    $pdf->tCell(15, $height, "ดื่มสุรา:", 0, 0, 'R');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_drink_alcohol"){
                if($value["data_result"] != ""){
                    $pdf->tCell(42, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(42, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(42, $height, "-", 0, 0, 'L');
        }
        $check_have_p = 0;
    }

    // row3
    $pdf->SetXY(6,63.6);
    $pdf->tCell(20, $height, "ประวัติญาติผู้ป่วย:", 0, 0, 'L');
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_family_history"){
                if($value["data_result"] == "2"){
                    $pdf->tCell(38, $height, convert_textFull_con("surgery_txt", "text", "1", $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(38, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                $check_have_p = $check_have_p+1;
            }
        }

        if($check_have_p == 0){
            $pdf->tCell(38, $height, "-", 0, 0, 'L');
        }
        $check_have_p = 0;
    }

    // box BMI
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(5.5,69.4,100,21); //(left page, height row, width, height_size)

    // BOX weight, height
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(105.5,69.4,99,21); //(left page, height row, width, height_size)

    // row 1
    $pdf->SetXY(5.5,69.5);
    $height = 5;
    $pdf->tCell(18, $height, "BT (C)", 1, 0, 'C');
    $pdf->tCell(18, $height, "PR (/min)", 1, 0, 'C');
    $pdf->tCell(18, $height, "RR (/min)", 1, 0, 'C');
    $pdf->tCell(28, $height, "BP (mmHg)", 1, 0, 'C');
    $pdf->tCell(18, $height, "SpO2 (%)", 1, 0, 'C');

    $check_have_bmi = 0;
    $pdf->SetXY(5.5,74.5);
    $height = 5;
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_bt"){
                if($value["data_result"] != ""){
                    $pdf->tCell(18, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(18, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(18, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_pr"){
                if($value["data_result"] != ""){
                    $pdf->tCell(18, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(18, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(18, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_rr"){
                if($value["data_result"] != ""){
                    $pdf->tCell(18, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(18, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(18, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_bp_systolic_h"){
                if($value["data_result"] != ""){
                    $pdf->tCell(14, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(14, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(14, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_diastolic_bp_d"){
                if($value["data_result"] != ""){
                    $pdf->tCell(14, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(14, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(14, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_spo2"){
                if($value["data_result"] != ""){
                    $pdf->tCell(18, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 1, 0, 'C');
                }
                else{
                    $pdf->tCell(18, $height, "-", 1, 0, 'C');
                }
                $check_have_bmi = $check_have_bmi+1;
            }
        }
        if($check_have_bmi == 0){
            $pdf->tCell(18, $height, "-", 1, 0, 'C');
        }
        $check_have_bmi = 0;
    }

    // HEAD
    $pdf->SetXY(150, 69);
    $height = 6;
    $width = 0;
    if($data_date_old != ""){
        $pdf->tCell(27, $height, $data_date_old, 0, 0, 'C');
        $width = 9;
    }
    $pdf->tCell(27-$width, $height, "Present", 0, 0, 'C');

     // Line dash
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Line(104,74.5,204.5,74.5);

    // Present
    $check_data_found = count($data_doctorMain);
    $width = 30;
    $height = 6;
    $pdf->SetXY(106,74.5);
    $pdf->tCell(54, $height, "น้ำหนัก (กก.)", 0, 0, 'L');
    if($data_date_old != ""){
        foreach($data_date_old_full as $key => $value){
            if($key == "cn_weight"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_date_old_full), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }

    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "cn_weight"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }

    $pdf->SetXY(106,79.7);
    $pdf->tCell(54, $height, "ส่วนสูง (ซม.)", 0, 0, 'L');
    if($data_date_old != ""){
        foreach($data_date_old_full as $key => $value){
            if($key == "heigh"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_date_old_full), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }

    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "heigh"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }

    $pdf->SetXY(106,84.9);
    $pdf->tCell(54, $height, "BMI (kg/m2)", 0, 0, 'L');
    if($data_date_old != ""){
        foreach($data_date_old_full as $key => $value){
            if($key == "bp_bmi"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_date_old_full), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }
    
    if($check_data_found > 0){
        foreach($data_doctorMain as $key => $value){
            if($key == "bp_bmi"){
                if($value["data_result"] != ""){
                    $pdf->tCell(23, $height, convert_textFull_con($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
                }
                else{
                    $pdf->tCell(23, $height, "-", 1, 0, 'C');
                }
            }
        }
    }

    $pdf->SetXY(5.5,90.4);
    $height = 6;
    $pdf->tCell(17, $height, "Date", 1, 0, 'C');
    $pdf->tCell(45, $height, "Report", 1, 0, 'C');
    $pdf->tCell(100, $height, "Lab Name", 1, 0, 'C');
    $pdf->tCell(10, $height, "Min", 1, 0, 'C');
    $pdf->tCell(10, $height, "Max", 1, 0, 'C');
    $pdf->tCell(17, $height, "Status", 1, 0, 'C');

    $height = 24;
    $loop_c = 0;
    $row = 88;
    //old
    if($data_lab_date != ""){
        if(count($data_lab_old) > 0){
            foreach($data_lab_old as $key => $val){
                $pdf->SetXY(6.5,$row);
                $pdf->tCell(16, $height, $val["date"], 0, 0, 'L');
                $pdf->tCell(46, $height, $val["report"], 0, 0, 'L');
                $pdf->tCell(100, $height, $val["lab_name"], 0, 0, 'L');
                $pdf->tCell(9.5, $height, $val["min"], 0, 0, 'C');
                $pdf->tCell(9, $height, $val["max"], 0, 0, 'C');
                $pdf->tCell(17, $height, $val["status"], 0, 0, 'C');
    
                $loop_c = ($loop_c+1);
                $row = ($row+5.4);
            }
        }

        $row = ($row+5.4);
        
        $pdf->SetLineWidth(0.1);
        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(6.5,$row+5.4,203,$row+5.4);
    }
    else{
        $row = $row+3;
    }

    // BOX weight, height
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Rect(5.5,96.4,199,$row-72); //(left page, height row, width, height_size)

    $row = $row-3;
    // present
    if(count($data_lab) > 0){
        foreach($data_lab as $key => $val){
            $pdf->SetXY(6.5,$row);
            $pdf->tCell(16, $height, $val["date"], 0, 0, 'L');
            $pdf->tCell(46, $height, $val["report"], 0, 0, 'L');
            $pdf->tCell(100, $height, $val["lab_name"], 0, 0, 'L');
            $pdf->tCell(9.5, $height, $val["min"], 0, 0, 'C');
            $pdf->tCell(9, $height, $val["max"], 0, 0, 'C');
            $pdf->tCell(17, $height, $val["status"], 0, 0, 'C');

            $loop_c = ($loop_c+1);
            $row = ($row+5.4);
        }
    }

    // $pdf->Output(); //TEST
    $filename="pdfoutput/tempFile.pdf";
    if($name_file != "" || $name_file != null){
        $pdf->Output($name_file.".pdf", "F"); //I = draf not save, D auto save

        $date_save_date = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;
        $returnData = $date_save_date.",";//json_encode($name_file);
        echo $returnData;
    }
?>