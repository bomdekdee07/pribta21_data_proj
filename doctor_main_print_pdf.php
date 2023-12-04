<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("class_pdf.php");

    $sClinicID = getQS("clinic_id");
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urldecode(getQS("coltime"));

    function convert_textFull($dataID, $dataType, $data_result, $data_array){
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

    $sUid = "TEST12";
    $sColDate = "1994-01-01";
    $sColTime = "00:00:00";

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
    $mysqli->close();

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
    $pdf->Line(6,23,204,23);

    

    // Detail info
    // $pdf->SetXY(1,27);
    // $pdf->tCell(30, 8, "แพ้อาหาร:", 0, 0, 'R');

    // if(count($data_doctorMain) > 0){
    //     foreach($data_doctorMain as $key => $value){
    //         if($key == "food_intolerance"){
    //             if($value["data_result"] == "Y"){
    //                 $pdf->tCell(35, 8, convert_textFull("food_intolerance_txt", "text", "Y", $data_doctorMain), 0, 0, 'L');
    //             }
    //             else{
    //                 $pdf->tCell(35, 8, convert_textFull($value["data_id"], $value["data_type"], $value["data_result"], $data_doctorMain), 0, 0, 'L');
    //             }
    //         }
    //     }
    // }

    // $filename="pdfoutput/tempFile.pdf";
    $pdf->Output("",'I'); //I = draf not save, D auto save
    $pdf->Output();
?>