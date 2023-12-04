<?
    include("in_session.php");
    include('in_db_conn.php');
    include_once("class_xlsxwriter.php");
    include_once("in_php_function.php");

    $start_date = getQS("start_date");
    $end_date = getQS("end_date");
    $sup_code = getQS("sup_code");
    $clinic_id = getSS("clinic_id");

    $bind_parameter = "s";
    $bind_value = array($clinic_id);

    $data_loop_medician_summary = array();
    $query = "SELECT st_order.supply_code,
        st_master.supply_name,
        st_order.uid,
        st_order.dose_day,
        st_master.supply_unit
    FROM i_stock_order st_order
    LEFT JOIN i_stock_master st_master ON(st_master.supply_code = st_order.supply_code)
    LEFT JOIN i_stock_group st_group ON(st_group.supply_group_code = st_master.supply_group_code)
    WHERE st_group.supply_group_type = '1'
    AND st_order.clinic_id = ?";

    if($sup_code != ""){
        $query .= " AND st_group.supply_group_code = ?";
        $bind_parameter .= "s";
        $bind_value[] = $sup_code;
    }

    if($start_date != ""){
        $query .= " AND st_order.order_datetime >= ?";
        $bind_parameter .= "s";
        $bind_value[] = $start_date;

        if($end_date != ""){
            $query .= " AND st_order.order_datetime <= ?";
            $bind_parameter .= "s";
            $bind_value[] = $end_date." 23:59:59";
        }
    }

    $query .= " ORDER BY st_order.supply_code, st_order.supply_lot, st_order.uid;";

    // echo $query;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_parameter, ...$bind_value);

    $old_supCode = "";
    $old_uid = "";
    $total_caseUid = 0;
    $total_dispendeDrug = 0;
    if($stmt->execute()){
        $stmt->bind_result($supply_code, $supply_name, $uid, $amt, $supply_unit);
        while($stmt->fetch()){
            if($old_supCode != $supply_code){
                $total_caseUid = 0;
            }

            if($old_uid == $uid){
                $total_caseUid = 0;
            }

            if($old_supCode != $sup_code){
                $total_dispendeDrug = 0;
            }

            $data_loop_medician_summary[$supply_code]["code"] = $supply_code;
            $data_loop_medician_summary[$supply_code]["name"] = $supply_name;

            $total_caseUid = ($total_caseUid+(count($uid)));
            $data_loop_medician_summary[$supply_code]["total_uid"] = $total_caseUid;

            $total_dispendeDrug = $total_dispendeDrug+$amt;
            $data_loop_medician_summary[$supply_code]["total_dispense"] = $total_dispendeDrug;
            $data_loop_medician_summary[$supply_code]["unit"] = $supply_unit;

            $old_supCode = $supply_code;
            $old_uid = $uid;
        }
    }

    $stmt->close();
    $mysqli->close();

    $sToday = date("Y-m-d_His");
    $genStName = "";
    if($start_date == "")
        $genStName = $sToday;
    else
        $genStName = $start_date."-".$end_date."_".$sup_code;
    $file_name = "Report_Monthly_Drug_".$genStName.".xlsx";
    $sheet_name = $genStName;

    $styleArray = array('font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
    $styleArray_row = array('font-style'=>'bold', 'halign'=>'center');
    $header = array(
        'Supply Code'=>'string',
        'ชื่อยา'=>'string',
        'ยอดรวมเคส UID'=>'string',
        'ยอดรวมจำนวนยาที่จ่ายออก'=>'string',
        "หน่วย"=>"string"
    );
    
    $writer = new XLSXWriter();
    $writer->writeSheetHeader($sheet_name, $header, $styleArray);

    foreach($data_loop_medician_summary as $key => $values){
        $arrayTemp = array();
        foreach($values as $val){
            array_push($arrayTemp, $val);
        }
        $writer->writeSheetRow($sheet_name, $arrayTemp);
    }

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($file_name).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
?>