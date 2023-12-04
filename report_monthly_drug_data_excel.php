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
    $query = "SELECT st_order.collect_date,
        st_order.uid,
        st_order.supply_code,
        st_order.supply_lot,
        st_master.supply_name,
        st_order.dose_day,
        st_master.supply_unit,
        st_order.updated_datetime 
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
        $query .= " AND st_order.collect_date >= ?";
        $bind_parameter .= "s";
        $bind_value[] = $start_date;

        if($end_date != ""){
            $query .= " AND st_order.collect_date <= ?";
            $bind_parameter .= "s";
            $bind_value[] = $end_date;
        }
    }

    $query .= " ORDER BY st_order.collect_date, st_order.supply_code, st_order.uid;";

    // echo $query;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_parameter, ...$bind_value);

    $old_supCode = "";
    $old_uid = "";
    $old_lot = "";
    $total_caseUid = 0;
    $total_dispendeDrug = 0;
    $old_update_date = ""; 
    if($stmt->execute()){
        $stmt->bind_result($collect_date, $uid, $supply_code, $supply_lot, $supply_name, $amt, $supply_unit, $updated_datetime);
        while($stmt->fetch()){
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["coldate"] = $collect_date;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["uid"] = $uid;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["code"] = $supply_code;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["name"] = $supply_name;

            if($old_supCode != $supply_code && $old_uid != $uid && $old_lot != $supply_lot){
                $total_dispendeDrug = 0;
            }
            else if($old_supCode == $supply_code && $old_uid != $uid && $old_lot == $supply_lot){
                $total_dispendeDrug = 0;
            }
            else if($old_supCode == $supply_code && $old_uid != $uid && $old_lot != $supply_lot){
                $total_dispendeDrug = 0;
            }
            else if($old_supCode != $supply_code && $old_uid != $uid && $old_lot == $supply_lot){
                $total_dispendeDrug = 0;
            }
            else if($old_supCode != $supply_code && $old_lot != $supply_lot){
                $total_dispendeDrug = 0;
            }
            else if($old_supCode == $supply_code && $old_uid == $uid && $old_lot == $supply_lot && $old_update_date != $updated_datetime){
                $total_dispendeDrug = 0;
            }

            $total_dispendeDrug += $amt;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["dispense_amt"] = $total_dispendeDrug;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["unit"] = $supply_unit;
            $data_loop_medician_summary[$collect_date][$supply_code][$uid][$updated_datetime]["date_update"] = $updated_datetime;

            $old_supCode = $supply_code;
            $old_uid = $uid;
            $old_lot = $supply_lot;
            $old_update_date = $updated_datetime;
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
    $file_name = "Report_Monthly_Drug_Data".$genStName.".xlsx";
    $sheet_name = $genStName;

    $styleArray = array('font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
    $styleArray_row = array('font-style'=>'bold', 'halign'=>'center');
    $header = array(
        'Collect Date'=>'string',
        'UID'=>'string',
        'Supply Code'=>'string',
        'Name'=>'string',
        "Dispense Amount"=>"string",
        "Unit"=>"string",
        "Update date"=>"string"
    );
    
    $writer = new XLSXWriter();
    $writer->writeSheetHeader($sheet_name, $header, $styleArray);

    foreach($data_loop_medician_summary as $coldate => $supcode){
        foreach($supcode as $key_supcode => $uid){
            foreach($uid as $key_uid => $lot){
                foreach($lot as $key_lot => $values){
                    $arrayTemp = array();
                    foreach($values as $val){
                        array_push($arrayTemp, $val);
                    }
                    $writer->writeSheetRow($sheet_name, $arrayTemp);
                }
            }
        }
    }

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($file_name).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
?>