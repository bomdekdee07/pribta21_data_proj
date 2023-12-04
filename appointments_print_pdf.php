<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php");

    $sClinicID = getQS("clinic_id");
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urldecode(getQS("coltime"));

    $ap_date = getQS("ap_date");
    $sid = getQS("sid");

    function DateThai($strDate, $type)
	{
		$strYear = date("Y",strtotime($strDate))+543;
        $strYear_EN = date("Y",strtotime($strDate));
		$strMonth= date("n",strtotime($strDate))-1;
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
        $strMonthCut_EN = Array("Jan.","Feb.","Mar.","Apr.","May.","Jun.","Jul.","Aug.","Sep.","Oct.","Nov.","Dec.");
		$strMonthThai=$strMonthCut[$strMonth];
        $edaytxt = array("Sunday" => "อาทิตย์","Monday" => "จันทร์","Tuesday" => "อังคาร","Wednesday" => "พุธ","Thursday" => "พฤหัสบดี","Friday" => "ศุกร์","Saturday" => "เสาร์");
        $date_name = date ("l", strtotime($strDate));

        if($type == "TH"){
            return "$edaytxt[$date_name] $strDay $strMonthThai $strYear, $strHour:$strMinute";
        }
        else{
            return "$date_name $strDay $strMonthCut_EN[$strMonth] $strYear_EN, $strHour:$strMinute";
        }
	}

    $data_appoint = array();
    if($ap_date == ""){
        $query = "SELECT app.uid, CONCAT(app.appointment_date, ' ', app.appointment_time) as date_time, name.s_name, app.remark from i_appointment app
        left join p_staff name on (app.s_id = name.s_id)
        where app.uid = ?
        and app.clinic_id = ?
        and app.is_confirm = 0
        and app.appointment_date >= CURDATE()
        order by app.appointment_date DESC;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sUid, $sClinicID);
    }
    else{
        $query = "SELECT app.uid, CONCAT(app.appointment_date, ' ', app.appointment_time) as date_time, name.s_name, app.remark from i_appointment app
        left join p_staff name on (app.s_id = name.s_id)
        where app.uid = ?
        and app.appointment_date = ?
        and app.s_id = ?
        and app.clinic_id = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ssss", $sUid, $ap_date, $sid, $sClinicID);
    }

    if($stmt -> execute()){
        $stmt -> bind_result($uid, $date_time, $s_name, $remark);

        while($stmt -> fetch()){
            $data_appoint[$uid]["uid"] = $uid;
            $data_appoint[$uid]["date"] = $date_time;
            $data_appoint[$uid]["name"] = $s_name;
            $data_appoint[$uid]["remark"] = $remark;
        }
    }   
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();
    $mysqli->close();

    $pdf = new PDF();
    $pdf->SetThaiFont();
    $pdf->AddPage('P',"A4",'mm');

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
    $pdf->tText($width, $height+5, "พริบตา แทนเจอรีน สหคลินิก สถาบันเพื่อการวิจัยและนวัตกรรมด้านเอชไอวี");
    $pdf->SetTextColor(50);
    $pdf->tText($width, $height+10, "11th Floor, Chamchuri Square Building, 319 Phayathai Road, Pathumwan, Bangkok, 10330 Tel: 02-160-5372: ใบอนุญาตเลขที่ 10110004863");

    // table head detail
    $pdf->SetTextColor(0);
    $height = 8;
    $pdf->SetXY(5,27);
    $pdf->tCell(25, $height, "UID", 1, 0, 'C');
    $pdf->tCell(55, $height, "วันที่, เวลา (DATE, TIME)", 1, 0, 'C', 0);
    $pdf->tCell(50, $height, "ผู้ให้บริการ (Provider)", 1, 0, 'C');
    $pdf->tCell(70, $height, "รายละเอียด (Detail)", 1, 0, 'C');

    // table detail info
    $pdf->SetXY(5,35);
    $height = 5.5;
    $height_mt = 5;
    if(count($data_appoint) > 0){
        foreach($data_appoint as $key => $value){
            $pdf->tCell(25, $height, $value["uid"], 0, 0, 'C');
            $pdf->tCell(55, $height, DateThai($value["date"], "TH")." น.", 0, 0, 'L');
            $pdf->tCell(50, $height, $value["name"], 0, 0, 'L');
            // $pdf->tMultiCell(70, $height_mt, $value["remark"], 0, 'L');
        }
        $pdf->Ln(5.5);
        $pdf->SetX(5);
        foreach($data_appoint as $key => $value){
            $pdf->tCell(25, $height, "", 0, 0, 'C');
            $pdf->tCell(55, $height, DateThai($value["date"], "EN")." hrs.", 0, 0, 'L');
            $pdf->tCell(50, $height, "", 0, 0, 'L');
            $pdf->SetXY(136,35);
            $pdf->tMultiCell(70, $height_mt, $value["remark"], 0, 'L');
        }
        // end line
        $height_Y = $pdf->GetY()+5.5;
        $pdf->SetLineWidth(0.2);
        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(5.3,$height_Y+0.5,205,$height_Y+0.5);

        // between
        $height_Y = $pdf->GetY()+5.5;
        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(5,35,5,$height_Y+0.5);

        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(30,35,30,$height_Y+0.5);

        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(85,35,85,$height_Y+0.5);

        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(135,35,135,$height_Y+0.5);

        $pdf->SetDash(); //5mm on, 5mm off
        $pdf->Line(205,35,205,$height_Y+0.5);
    }

    // Footer
    $width = 6;
    $height = 60;
    $pdf->tText($width, $height, "เปิดทำการ จันทร์-เสาร์ เวลา 10:00-19:00 น.");
    $pdf->tText($width, $height+5, "พริบตาคลินิก โทร. 02-160-5372");
    $pdf->tText($width, $height+10, "แทนเจอรีนคลินิก โทร. 02-160-5372 ต่อ 205,");
    $pdf->tText($width, $height+15, "061-979-0866, 099-452-5411");

    // Line dash
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(); //5mm on, 5mm off
    $pdf->Line(60,76,60,57);

    $width = 62;
    $pdf->tText($width, $height, "Operating hours: Mon-Sat. (10:00 AM - 07:00 PM)");
    $pdf->tText($width, $height+5, "Pribta: Tel. 02-160-5372");
    $pdf->tText($width, $height+10, "Tangerine: Tel. 02-160-5372 ext. 205,");
    $pdf->tText($width, $height+15, "061-979-0866, 099-452-5411");

    $width = 6;
    $pdf->Image('assets/image/icon_line.png', $width, 77, -4500);
    $pdf->tText(12, 81.5, "@PribtaClinic");
    $pdf->Image('assets/image/icon_line.png', 30, 77, -4500);
    $pdf->tText(36, 81.5, "@TangerineClinic");
    $pdf->Image('assets/image/Logo_facebook.png', 60, 77, -2100);
    $pdf->tText(66, 81.5, "PribtaClinic");
    $pdf->Image('assets/image/Logo_facebook.png', 82, 77, -2100);
    $pdf->tText(88, 81.5, "TangerineClinic");

    // Line dash
    $pdf->SetLineWidth(0.1);
    $pdf->SetDash(1,1); //5mm on, 5mm off
    $pdf->Line(0,84,300,84);

    // $filename="pdfoutput/tempFile.pdf";
    $pdf->Output();
?>