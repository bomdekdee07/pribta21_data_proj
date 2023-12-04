<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $uid = getQS("uid");
    $bill_id = getQS("billid");

    function date_formate_convert($date){
        $date_formate = date_create($date);
        $year_con = date_format($date_formate,"Y")+543;
        $date_of_birth = date_format($date_formate,"d/m/").$year_con;

        return $date_of_birth;
    }

    $data_main = array();
    $data_main_supply = array();
    $data_uid_info = array();
    $query = "select queue_l.queue,
        queue_l.uid,
        queue_l.collect_date,
        queue_l.collect_time,
        p_info.uic,
        p_info.fname,
        p_info.sname,
        p_info.en_fname,
        p_info.en_sname,
        p_info.date_of_birth,
        st_order.supply_code,
        st_master.supply_name,
        st_order.dose_day,
        st_order.total_price,
        st_group.supply_group_code,
        st_type.is_service,
        st_order.dose_per_time,
        st_order.dose_before,
        st_order.dose_breakfast,
        st_order.dose_lunch,
        st_order.dose_dinner,
        st_order.dose_night,
        st_order.supply_desc,
        staff_prepare.s_name as prepare_drug_by,
        staff_check.s_name as check_drug_by,
        staff_issue.s_name as issue_drug_by
    from i_bill_detail bill_d
    left join i_queue_list queue_l on(queue_l.queue = bill_d.bill_q and queue_l.collect_date = bill_d.bill_date)
    left join i_stock_order st_order on(st_order.uid = queue_l.uid and st_order.collect_date = queue_l.collect_date and st_order.collect_time = queue_l.collect_time)
    left join i_stock_master st_master on(st_master.supply_code = st_order.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    left join patient_info p_info on(p_info.uid = queue_l.uid)
    left join p_staff staff_prepare on(staff_prepare.s_id = queue_l.prepare_drug_by)
    left join p_staff staff_check on(staff_check.s_id = queue_l.check_drug_by)
    left join p_staff staff_issue on(staff_issue.s_id = queue_l.issue_drug_by)
    where bill_d.bill_id = ?
    and st_group.supply_group_type = 1 
    order by st_group.supply_group_type, st_order.supply_code, st_group.supply_group_code;";    

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $bill_id);

    if($stmt->execute()){
        $stmt->bind_result($queue, $uid, $collect_date, $collect_time, $uic, $fname, $sname, $en_fname, $en_sname, $date_of_birth, $supply_code, $supply_name, $dose_day, $total_price, $supply_group_code, $is_service, $dose_per_time, $dose_before, $dose_breakfast, $dose_lunch, $dose_dinner, $dose_night, $supply_desc, $prepare_drug_by, $check_drug_by, $issue_drug_by);
        while ($stmt->fetch()) {
            $data_main[$uid]["queue"] = $queue;
            $data_main[$uid]["uid"] = $uid;
            $data_main[$uid]["coldate"] = $collect_date;
            $data_main[$uid]["time"] = $collect_time;
            $data_main[$uid]["uic"] = $uic;
            $data_main[$uid]["name"] = isset($fname)?$fname." ".$sname : $en_fname." ".$en_sname;
            $data_main[$uid]["date_birth"] = $date_of_birth;
            $data_main[$uid]["prepare"] = $prepare_drug_by;
            $data_main[$uid]["check"] = $check_drug_by;
            $data_main[$uid]["issue"] = $issue_drug_by;
            $data_main_supply[$uid][$supply_code]["supply_name"] = $supply_name;
            $data_main_supply[$uid][$supply_code]["qty"] = $dose_day;
            $data_main_supply[$uid][$supply_code]["total_price"] = $total_price;
            $data_main_supply[$uid][$supply_code]["dose_per_time"] = $dose_per_time;
            $data_main_supply[$uid][$supply_code]["dose_before"] = $dose_before;
            $data_main_supply[$uid][$supply_code]["dose_breakfast"] = $dose_breakfast;
            $data_main_supply[$uid][$supply_code]["dose_lunch"] = $dose_lunch;
            $data_main_supply[$uid][$supply_code]["dose_dinner"] = $dose_dinner;
            $data_main_supply[$uid][$supply_code]["dose_night"] = $dose_night;
            $data_main_supply[$uid][$supply_code]["supply_desc"] = $supply_desc;
        }
        // print_r($data_main);
    }

    $data_patient_info = array();
    $data_sid_name = array();
    foreach($data_main as $key => $val){
        $query = "select rs.data_id, 
            rs.data_result,
            rs.uid as uid_result,
            rs.collect_date as date_result,
            rs.collect_time as time_result,
            pi.s_name
            from p_data_result rs 
            left join p_staff pi on(pi.s_id = rs.data_result)
            where rs.uid = ?
            and rs.collect_date = ?
            and rs.collect_time = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("sss", $val["uid"], $val["coldate"], $val["time"]);

        if($stmt->execute()){
            $stmt->bind_result($data_id, $data_result, $uid_result, $date_result, $time_result, $s_name);
            while ($stmt->fetch()) {
                if($data_id == "staff_md" || $data_id == "staff_cl" || $data_id == "staff_rn"){
                    $data_patient_info[$uid_result.$date_result.$time_result][$data_id] = $s_name;
                }
                else{
                    $data_patient_info[$uid_result.$date_result.$time_result][$data_id] = $data_result;
                }
            }
            // echo $data_patient_info[$uid_result.$date_result.$time_result]["bp_bmi"];
        }
    }
    $stmt->close();
    $mysqli->close();

    $c_data_result = count($data_patient_info) > 0? true : false;

    foreach($data_main as $key => $val){
        // START NEW PAGE PDF
        $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
        $pdf->SetThaiFont();
        $pdf->AddPage('P', 'A4', 'mm');

        $pdf->SetHeaderImage("assets/image/drug_order.png", 0, 0, 210, 290);
        $pdf->SetPageNo(178, 285, $stxt="Page {p}/{tp}", "THSarabun", "", 11);

        $width = 172;
        $height = 33;
        $pdf->SetHeaderTxt($width, $height, $val["queue"], "THSarabun", "", 15, array(0, 0, 0));

        $height = 45.6;
        $pdf->SetHeaderTxt($width-133, $height, $val["uid"], "THSarabun", "", 15, array(0, 0, 0));
        $pdf->SetHeaderTxt($width-79, $height, $val["uic"], "THSarabun", "", 15, array(0, 0, 0));
        $pdf->SetHeaderTxt($width-32, $height, $val["name"], "THSarabun", "", 15, array(0, 0, 0));

        $height = 58;
        $pdf->SetHeaderTxt($width-133, $height, date_formate_convert($val["date_birth"]), "THSarabun", "", 15, array(0, 0, 0));
        $pdf->SetHeaderTxt($width-80, $height, getAgeDetail($val["date_birth"]), "THSarabun", "", 15, array(0, 0, 0));
        if($c_data_result)
        $pdf->SetHeaderTxt($width-15, $height, $data_patient_info[$val["uid"].$val["coldate"].$val["time"]]["bp_bmi"], "THSarabun", "", 15, array(0, 0, 0));
        $height = 54.5;
        $pdf->SetHeaderTxt($width-128, $height, $data_patient_info[$val["uid"].$val["coldate"].$val["time"]]["cn_dx"], "THSarabun", "", 13.5, array(0, 0, 0), "L", 140, 20, 0);
        $height = 81.5;
        $pdf->SetHeaderTxt($width-134.5, $height, $data_patient_info[$val["uid"].$val["coldate"].$val["time"]]["drug_allergy_txt"], "THSarabun", "", 13.5, array(0, 0, 0), "L", 140, 20, 0);

        $height = 117.4;
        $pdf->SetHeaderTxt($width-126, $height-0.3, $data_patient_info[$val["uid"].$val["coldate"].$val["time"]]["staff_md"], "THSarabun", "", 15, array(0, 0, 0));
        $pdf->SetHeaderTxt($width-55, $height, date_formate_convert($val["coldate"]), "THSarabun", "", 15, array(0, 0, 0));

        //Set Start New Page
        $pdf->SetTopMargin(128.5);
        //Set Footer
        $pdf->SetAutoPageBreak(true, 97); //60///

        $pdf->SetTableColWidth(array(14,95,33,19.5));
        $pdf->SetTableColOrient(array("C","L","C","R"));
        $pdf->SetTableLineHeight(4);
        $pdf->SetTableLineMargin(4);
        $pdf->SetLeftMargin(22);

        // $pdf->SetX(0);
        $width = 37;
        $height_loop = 2;

        $loop_seq = 1;
        $temp_total_dose = "";
        foreach($data_main_supply[$val["uid"]] as $key => $val_phamar){
            $temp_total_dose = $val_phamar["dose_breakfast"]+$val_phamar["dose_lunch"]+$val_phamar["dose_dinner"]+$val_phamar["dose_night"];

            if($temp_total_dose == "0" || $temp_total_dose == ""){
                $temp_total_dose = $val_phamar["supply_desc"];
            }
            else{
                $temp_total_dose = $val_phamar["supply_desc"];

                if($val_phamar["dose_breakfast"]=="0" && $val_phamar["dose_lunch"]=="0" && $val_phamar["dose_dinner"]=="0" && $val_phamar["dose_night"]=="1" ){}
                else{
                    if($val_phamar["dose_before"]=="A"){ 
                        $temp_total_dose .= ' หลังอาหาร'; 
                    }
                    else if($val_phamar["dose_before"]=="B")
                    { 
                        $temp_total_dose .= ' ก่อนอาหาร'; 
                    }
                }

                if($val_phamar["dose_breakfast"]=="1") $temp_total_dose .= "เช้า";
                if($val_phamar["dose_lunch"]=="1") $temp_total_dose .= "กลางวัน";
                if($val_phamar["dose_dinner"]=="1") $temp_total_dose .= "เย็น";
                if($val_phamar["dose_night"]=="1") $temp_total_dose .= "ก่อนนอน";
            }

            $pdf->SetFont('THSarabun', '', 9);
            $current_Y =  $pdf->GetY();
            if($loop_seq == 1){
                $current_Y = $current_Y+33.5;
                $pdf->tText($width, $current_Y, $temp_total_dose);
            }
            else{
                $pdf->tText($width, $current_Y+8.3+$height_loop, $temp_total_dose);
            }

            $pdf->SetFont('THSarabun', '', 11);
            $pdf->writeRow(
                array(
                    $loop_seq,
                    $val_phamar["supply_name"],
                    $val_phamar["qty"],
                    $val_phamar["total_price"]
                )
            );

            $loop_seq = $loop_seq+1;
        }

        $width = 24.5;
        $height = 270.5;
        $pdf->tText($width, $height, $val["prepare"]);
        $pdf->tText($width+55, $height, $val["check"]);
        $pdf->tText($width+110, $height, $val["issue"]);

        $pdf->Output(); //TEST
    }
?>