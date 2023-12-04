<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php"); // 1.82

    $lab_order_id = getQS("oid");
    // $lab_order_id = "L2100695";
    // echo "laborderid: ".$lab_order_id;

    $lab_list = getQS("lablist"); //Use this
    // $lab_list = array("CREA", "CrCl", "ALT", "E2", "T", "RPR_Titer", "HIV_VL", "HIV_VL_DT"); //fix test
    $lab_list = array_flip($lab_list); //Use this
    // print_r($lab_list);

    $s_id = getQS("printid");
    // $s_id = "P20053";

    $lab_data = array();
    $cont_row = 0;
    $uid_hospital_num = "";
    $lab_order_id_head = "";
    $coldate_coltime_head = "";
    $time_confirm_data = "";

    $query = "select 
        lab_order.uid,
        lab_order.collect_date,
        lab_order.collect_time,
        lab_order.lab_order_id,
        detail_test.lab_id,
        detail_test.lab_name,
        lab_method.lab_method_name,
        lab_result.lab_result_report,
        lab_result.lab_result_note,
        lab_hist.lab_std_male_txt,
        lab_hist.lab_std_female_txt,
        lab_result.time_confirm
    from p_lab_order lab_order
    left join p_lab_order_lab_test lab_order_test on(lab_order_test.uid = lab_order.uid and lab_order_test.collect_date = lab_order.collect_date and lab_order_test.collect_time = lab_order.collect_time)
    left join p_lab_test detail_test on(detail_test.lab_id = lab_order_test.lab_id)
    left join p_lab_test_group lab_group on(lab_group.lab_group_id = detail_test.lab_group_id)
    left join p_lab_method lab_method on(lab_method.lab_method_id = lab_group.lab_method_id)
    left join p_lab_result lab_result on(lab_result.uid = lab_order.uid and lab_result.collect_date = lab_order.collect_date and lab_result.collect_time = lab_order.collect_time and lab_result.lab_id = lab_order_test.lab_id)
    left join p_lab_test_result_hist lab_hist on(lab_hist.lab_id = lab_result.lab_id)
    where lab_order.lab_order_id = ?
    order by detail_test.lab_seq;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $lab_order_id);

    if($stmt -> execute()){
        $stmt -> bind_result($uid, $collect_date, $collect_time, $lab_order_id, $lab_id, $lab_name, $lab_method_name, $lab_result_report, $lab_result_note, $lab_std_male_txt, $lab_std_female_txt, $time_confirm);
        while($stmt -> fetch()){
            if(isset($lab_list[$lab_id])){
                $lab_data[$lab_id]["uid"] = $uid;
                $lab_data[$lab_id]["coldate"] = $collect_date;
                $lab_data[$lab_id]["coltime"] = $collect_time;
                $lab_data[$lab_id]["lab_order_id"] = $lab_order_id;
                $lab_data[$lab_id]["lab_name"] = $lab_name;
                $lab_data[$lab_id]["method_name"] = $lab_method_name;
                $lab_data[$lab_id]["result_report"] = $lab_result_report;
                $lab_data[$lab_id]["result_note"] = $lab_result_note;
                $lab_data[$lab_id]["male_txt"] = $lab_std_male_txt;
                $lab_data[$lab_id]["female_txt"] = $lab_std_female_txt;
                $time_confirm_data = $time_confirm;
                $uid_hospital_num = $uid;
                $lab_order_id_head = $lab_order_id;
                $coldate_coltime_head = $collect_date." ".$collect_time;
            }
        }
        // print_r($lab_data);
        // echo $count_row;
    }
    $stmt->close();

    $info_data = array("full_name" => "", "age" => "", "date_of_birth" => "");
    if(isset($uid_hospital_num)){
        $query = "select fname,
            sname,
            date_of_birth
        from patient_info 
        where uid = ?";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $uid_hospital_num);

        if($stmt -> execute()){
            $stmt -> bind_result($fname, $sname, $date_of_birth);
            while($stmt -> fetch()){
                $info_data["full_name"] = $fname." ".$sname;
                $date_con = date_create($date_of_birth);
                $year = (date("Y")-date_format($date_con,"Y"));
                $info_data["age"] = $year." ปี";
                $info_data["date_of_birth"] = $date_of_birth;
            }
            // print_r($lab_data);
        }
        $stmt->close();
    }

    $footer_info_data = array("name_save" => "", "name_order" => "", "name_confirm" => "", "name_print" => "");
    if(isset($uid_hospital_num)){
        $query = "select staff_save.s_name as name_save,
            staff_save.license_lab as licen_save,
            staff_order.s_name as name_order,
            staff_confirm.s_name as name_confirm,
            staff_confirm.license_lab as licen_confirm,
            staff_print.s_name as name_print,
            NOW() as now_date_time
        from p_lab_order lab_order
        left join p_staff staff_save on(staff_save.s_id = lab_order.staff_lab_save)
        left join p_staff staff_order on(staff_order.s_id = lab_order.staff_order)
        left join p_staff staff_confirm on(staff_confirm.s_id = lab_order.staff_confirm)
        left join p_staff staff_print on(staff_print.s_id = ?)
        where lab_order.lab_order_id = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $s_id, $lab_order_id);

        if($stmt -> execute()){
            $stmt -> bind_result($name_save, $licen_save, $name_order, $name_confirm, $licen_confirm, $name_print, $now_date_time);
            while($stmt -> fetch()){
                if(isset($name_save))
                $footer_info_data["name_save"] = $name_save." (".$licen_save.")";
                $footer_info_data["name_order"] = $name_order;
                // if(isset($time_confirm_data))
                $footer_info_data["name_confirm"] = $name_confirm." (".$licen_confirm.") ".$time_confirm_data;
                $footer_info_data["name_print"] = $now_date_time." โดย ".$name_print;
            }
            // print_r($footer_info_data);
        }
        $stmt->close();
    }
    $mysqli->close();

    $pdf = new PDF(); //new FPDF('P','mm',array(210,297));
	$pdf->SetThaiFont();
	$pdf->AddPage('P', 'A4', 'mm');

    //กำหนด Font Size
    // LOGO
    $width = 32;
    $height = 10;
    // $pdf->Image('assets/image/lab_report.jpg', 0, 0, 210, 290);

    $pdf->SetHeaderImage("assets/image/lab_report.jpg", 0, 0, 210, 290);
    $pdf->SetPageNo(180, 280, $stxt="Page {p}/{tp}", "THSarabun", "", 11);
    $pdf->SetFont('THSarabun', '', 12);
    // line height = 6
    $width = 17;
    $height = 46.5;
    // $pdf->tText($width+25, $height, $info_data["full_name"]);
    $pdf->SetHeaderTxt($width+25, $height, $info_data["full_name"], "THSarabun", "", 12, array(0, 0, 0));
    $pdf->SetHeaderTxt($width+130, $height, $uid_hospital_num, "THSarabun", "", 12, array(0, 0, 0));

    $width = 17;
    $height = 51;
    $pdf->SetHeaderTxt($width+25, $height, $info_data["age"], "THSarabun", "", 12, array(0, 0, 0));
    $pdf->SetHeaderTxt($width+130, $height, $lab_order_id_head, "THSarabun", "", 12, array(0, 0, 0));

    $width = 17;
    $height = 55.7;
    $pdf->SetHeaderTxt($width+25, $height, $info_data["date_of_birth"], "THSarabun", "", 12, array(0, 0, 0));
    $pdf->SetHeaderTxt($width+130, $height, $coldate_coltime_head, "THSarabun", "", 12, array(0, 0, 0));

    // FOOTER
    $pdf->SetTextColor(0);
    $pdf->SetFont('THSarabun', '', 12);
    $width = 15;
    $height = 263.5;
    $pdf->SetHeaderTxt($width+18, $height, $footer_info_data["name_save"], "THSarabun", "", 10, array(0, 0, 0));
    $pdf->SetHeaderTxt($width+122, $height, $footer_info_data["name_order"], "THSarabun", "", 10, array(0, 0, 0));

    $height = 268.5;
    $pdf->SetHeaderTxt($width+18, $height, $footer_info_data["name_confirm"], "THSarabun", "", 10, array(0, 0, 0));
    $pdf->SetHeaderTxt($width+122, $height, $footer_info_data["name_print"], "THSarabun", "", 10, array(0, 0, 0));

    $width = 17;
    $height = 61;
    $pdf->SetFont('THSarabun', '', 14);

    //Set Start New Page
    $pdf->SetTopMargin(72);
    //Set Footer
    $pdf->SetAutoPageBreak(true,45);

    //สร้าง Column สำหรับ Table

    $pdf->SetTableColWidth(array(44.5,44.5,53.5,40));
    $pdf->SetTableColOrient(array("L","L","L","L"));
    $pdf->SetTableLineHeight(5);
    $pdf->SetTableLineMargin(4);
    $pdf->SetLeftMargin(15);
    $pdf->SetTableColColor(array(
        array(),
        array(35, 140, 0),
        array(),
        array(255, 0, 0)
    ));

    $pdf->SetX(0);
    $width = 16;
    $height_loop = 10.5;
    $i = 0;
    foreach($lab_data as $key => $val){
        $current_Y =  $pdf->GetY();

        if($i == 0){
            $current_Y += 57.8;
        }

        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->tText($width, $current_Y+$height_loop, $val["method_name"], 0, 0, 'L');
        $i++;

        $pdf->SetTextColor(0);
        $pdf->SetFont('THSarabun', '', 12);
        
        $concert_male_female_txt = "";
        if($val["male_txt"] == $val["female_txt"]){
            $concert_male_female_txt = $val["male_txt"];
        }
        else{
            $concert_male_female_txt = "Male: ".$val["male_txt"]."\n"."Female: ".$val["female_txt"];
        }

        $pdf->writeRow(
            array($val["lab_name"], 
            $val["result_report"], 
            $concert_male_female_txt, 
            $val["result_note"])
        );
    }

    $pdf->Output();
?>