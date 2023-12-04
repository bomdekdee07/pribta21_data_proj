<?
    include('in_db_conn.php');
    include_once("in_php_function.php");
    include_once("class_pdf.php");

    $sTime = urlDecode(getQS("coltime"));
    $sUid = getQS("uid");
    $sColDate = urlDecode(getQS("coldate"));
    $sLang = getQS("lang","th");

    //Get Que #
    $query = "SELECT queue FROM i_queue_list WHERE uid=? AND collect_date = ? AND collect_time=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss",$sUid,$sColDate,$sTime);

    $iQueue ="";
    if($stmt->execute()){
        $stmt->bind_result($queue );
        while ($stmt->fetch()) {
            $iQueue = $queue;
        }
    }

    $query = "select distinct JSO.supply_code,
        (select JSSO_sub.stock_lot
        from i_stock_list JSSO_sub
        where JSSO_sub.supply_code = JSO.supply_code
        and JSSO_sub.clinic_id = JSO.clinic_id
        and JSSO_sub.stock_exp_date = (select MIN(JSSO_sub.stock_exp_date)
        from i_stock_list JSSO_sub
        where JSSO_sub.supply_code = JSO.supply_code
        and JSSO_sub.clinic_id = JSO.clinic_id)) supply_lot,
        JSO.order_by,
        (select sum(JSO_sub.dose_day) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_day,
        (select MAX(JSO_sub.dose_per_time) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_per_time,
        (select MAX(JSO_sub.dose_before) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_before,
        (select MAX(JSO_sub.dose_breakfast) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_breakfast,
        (select MAX(JSO_sub.dose_lunch) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_lunch,
        (select MAX(JSO_sub.dose_dinner) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_dinner,
        (select MAX(JSO_sub.dose_night) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) dose_night,
        (select sum(JSO_sub.dose_day) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) as total_amt,
        JSO.sale_opt_id,
        (select MAX(JSO_sub.order_note) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) order_note,
        JSO.order_status, 
        (select MAX(JSO_sub.supply_desc) from i_stock_order JSO_sub
        where JSO_sub.collect_date = JSO.collect_date and JSO_sub.collect_time = JSO.collect_time and JSO_sub.uid = JSO.uid and JSO_sub.supply_code = JSO.supply_code ) supply_desc,
        (select MIN(JSSO_sub.stock_exp_date)
        from i_stock_list JSSO_sub
        where JSSO_sub.supply_code = JSO.supply_code
        and JSSO_sub.clinic_id = JSO.clinic_id) stock_exp_date,
        JSM.supply_name,
        JSM.dose_note,
        JSM.supply_unit,
        PI.fname,
        PI.sname
    from i_stock_order JSO
    LEFT JOIN i_stock_list JSSO ON (JSSO.supply_code = JSO.supply_code AND JSSO.clinic_id = JSO.clinic_id AND JSSO.stock_lot = JSO.supply_lot)
    LEFT JOIN i_stock_master JSM ON (JSM.supply_code = JSSO.supply_code)
    LEFT JOIN i_stock_group JSG ON (JSG.supply_group_code = JSM.supply_group_code)
    LEFT JOIN i_stock_type JST ON (JST.supply_group_type = JSG.supply_group_type)
    LEFT JOIN patient_info PI ON (PI.uid = JSO.uid)
    where JST.is_service != 1 AND collect_date = ? and collect_time = ? and JSO.uid = ?
    order by order_code ASC";

    $aRes = array();
    $main_count = 1;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss",$sColDate,$sTime,$sUid);
    if($stmt->execute()){
        $stmt->bind_result($supply_code,$supply_lot,
        $order_by,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,
        $dose_lunch,$dose_dinner,$dose_night,$total_amt,$sale_opt_id,
        $order_note,$order_status, $supply_desc, $stock_exp_date, $supply_name, 
        $dose_note,$supply_unit,$fname,$sname);

        while ($stmt->fetch()) {
            $aRes[$supply_lot]["supply_code"] = $supply_code;
            $aRes[$supply_lot]["stock_lot"] = $supply_lot;
            $aRes[$supply_lot]["order_by"] = $order_by;
            $aRes[$supply_lot]["dose_day"] = $dose_day;
            $aRes[$supply_lot]["dose_per_time"] = $dose_per_time;
            $aRes[$supply_lot]["dose_before"] = $dose_before;
            $aRes[$supply_lot]["dose_breakfast"] = $dose_breakfast;
            $aRes[$supply_lot]["dose_lunch"] = $dose_lunch;
            $aRes[$supply_lot]["dose_dinner"] = $dose_dinner;
            $aRes[$supply_lot]["dose_night"] = $dose_night;
            $aRes[$supply_lot]["total_amt"] = $total_amt;
            $aRes[$supply_lot]["sale_opt_id"] = $sale_opt_id;
            $aRes[$supply_lot]["order_note"] = urlDecode($order_note);
            $aRes[$supply_lot]["order_status"] = $order_status;
            $aRes[$supply_lot]["supply_name"] = $supply_name;
            $aRes[$supply_lot]["dose_note"] =  urlDecode($dose_note);
            $aRes[$supply_lot]["supply_unit"] = $supply_unit;
            $aRes[$supply_lot]["supply_desc"] = $supply_desc;
            $aRes[$supply_lot]["stock_exp_date"] = $stock_exp_date;
            $aRes[$supply_lot]["fname"] = $fname;
            $aRes[$supply_lot]["sname"] = $sname;
        }
        // print_r($aRes);
    }
    $stmt->close();
    $mysqli->close();

    $test = 0;
    $pdf = new PDF('L','mm',array(90,60));
    $pdf->SetThaiFont();
    $pdf->SetAutoPageBreak(false,60);
    $pdf->SetFont('THSarabun', 'B', 11);

    foreach($aRes as $key => $aRes_val){
        $pdf->AddPage();

        //Create BC Year
        $aDate = explode("-",$sColDate); 
        if($sLang == "th"){
            $thYear = $aDate[0]+543;
        }
        else{
            $thYear = $aDate[0];
        }
        $th_date="$aDate[2]/$aDate[1]/$thYear";

        // echo ($aRes_val["dose_day"]);
        $temp_total_dose = $aRes_val["dose_breakfast"]+$aRes_val["dose_lunch"]+$aRes_val["dose_dinner"]+$aRes_val["dose_night"];


        if($temp_total_dose =='0' || $temp_total_dose == ""){
            $temp_total_dose = $aRes_val["supply_desc"];
        }
        else{
            $temp_total_dose = $aRes_val["supply_desc"]." วันละ ". $temp_total_dose ." ครั้ง";
            
            if($aRes_val["dose_per_time"]!="0" ) $temp_total_dose .= " ครั้งละ ".$aRes_val["dose_per_time"]." ".$aRes_val["supply_unit"];

            if($aRes_val["dose_breakfast"]=="0" && $aRes_val["dose_lunch"]=="0" && $aRes_val["dose_dinner"]=="0" && $aRes_val["dose_night"]=="1" ){

            }else{
                if($aRes_val["dose_before"]=="A"){ $temp_total_dose .= 'หลัง อาหาร'; }
                else if($aRes_val["dose_before"]=="B"){ $temp_total_dose .= 'ก่อน อาหาร'; }
            }

            if($aRes_val["dose_breakfast"]=="1") $temp_total_dose .= " เช้า";
            if($aRes_val["dose_lunch"]=="1") $temp_total_dose .= " กลางวัน";
            if($aRes_val["dose_dinner"]=="1") $temp_total_dose .= " เย็น";
            if($aRes_val["dose_night"]=="1") $temp_total_dose .= " ก่อนนอน";
        }

        if($sLang == "th"){
            $pdf->Image('assets/image/sticker_drug.jpg', 0, 0, 90, 60);
        }
        else{
            $pdf->Image('assets/image/sticker_drug_en.jpg', 0, 0, 90, 60);
        }

        $pdf->GetPageWidth();  // Width of Current Page
        $pdf->GetPageHeight(); // Height of Current Page

        $pdf->SetFont('THSarabun', 'B', 11);
        $pdf->SetXY(48,11);
        $pdf->tCell(0, 0, "", 0, 1, 'L');
        $pdf->SetXY(49,16);
        $pdf->tCell(0, 0, "", 0, 1, 'L');

        $pdf->SetFont('THSarabun', '', 12);
        $pdf->SetXY(59,15);
        $pdf->Cell(0, 0, $th_date, 0, 1, 'L');

        $pdf->SetXY(75,15);
        $pdf->tCell(0, 0, "#".$iQueue, 0, 1, 'L');

        // ชื่อคน
        $pdf->SetXY(3,24);
        $pdf->tCell(0, 0, $aRes_val["fname"]." ".$aRes_val["sname"], 0, 1, 'L');


        $pdf->SetXY(70,10);
        $pdf->tCell(0, 0, $sUid, 0, 1, 'L');

        $pdf->SetFont('THSarabun', 'B', 10.5);
        //ขื่อยา
        $pdf->SetXY(3,29.5);
        $pdf->tCell(0, 0, $aRes_val["supply_name"], 0, 1, 'L');

        $pdf->SetFont('THSarabun', '', 12);
        //จำนวน
        $pdf->SetXY(73,29.5);
        $pdf->tCell(0, 0, $aRes_val["total_amt"]." ".$aRes_val["supply_unit"], 0, 1, 'L');

        //วิธีใช้
        //ทุก
        $pdf->SetFont('THSarabun', 'B', 11);
        $pdf->SetXY(3,32);
        $pdf->tMultiCell(79,5, $temp_total_dose, 0, "L", '0',false);

        $sDoseNote = (($aRes_val["order_note"] != "")?$aRes_val["order_note"]:$aRes_val["dose_note"]);
        $pdf->SetXY(3,42);
        $pdf->tMultiCell(79,5, $sDoseNote, 0, "L", '0',false);

        // LOT, EXP
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->SetXY(53,56);
        $pdf->tCell(0, 0, "LOT: ".$aRes_val["stock_lot"]."   EXP: ".$aRes_val["stock_exp_date"], 0, 1, 'L');    
    }

    $pdf->Output();
?>