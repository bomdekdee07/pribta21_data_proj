<?
    include("in_session.php");
    include('in_db_conn.php');
    include_once("class_xlsxwriter.php");
    include_once("in_php_function.php");

    $month = getQS("month");
    $year = getQS("year");

    $data_purpose_uid = array();
    $name_info = "";
    $query1 = "";
    $query2 = "";
    $query3 = "";

    $query1 .= "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
    $stmt = $mysqli->prepare($query1);
    $stmt->execute();

    $query2 .= "SELECT queue_l.uid,
        pt_info.fname,
        pt_info.sname,
        pt_info.en_fname,
        pt_info.en_sname,
        queue_l.queue_datetime,
        queue_l.sale_opt_id,
        data_re.data_result,
        queue_l.queue
    from i_queue_list queue_l
    left join patient_info pt_info ON(pt_info.uid = queue_l.uid)
    JOIN p_data_result data_re ON(data_re.uid = queue_l.uid and data_re.collect_date = queue_l.collect_date and data_re.collect_time = queue_l.collect_time)
    AND data_re.data_id = 'cn_patient_note' 
    AND (data_re.data_result LIKE '%purp%') ";

    if($month != ""){
        $coldate_f = $year."-".$month."-01";
        $coldate_l = $year."-".$month."-31";

        $query2 .= " AND data_re.collect_date >= ? AND data_re.collect_date <= ?;";
        $bind_param = "ss";
        $array_val = array($coldate_f, $coldate_l);
    }

    $stmt = $mysqli->prepare($query2);

    if($month != ""){
        $stmt->bind_param($bind_param, ...$array_val);
    }

    if($stmt->execute()){
        $stmt->bind_result($uid, $fname, $sname, $en_fname, $en_sname, $queue_datetime, $sale_opt_id, $note_to_all, $queue);
        while ($stmt->fetch()) {
            $data_purpose_uid[$uid.$queue_datetime]["uid"] = $uid;
            $data_purpose_uid[$uid.$queue_datetime]["date"] = $queue_datetime;
            $data_purpose_uid[$uid.$queue_datetime]["sale"] = $sale_opt_id;
            $data_purpose_uid[$uid.$queue_datetime]["note"] = $note_to_all;
            $data_purpose_uid[$uid.$queue_datetime]["queue"] = $queue;
            if(isset($fname)){
                $name_info = $fname." ".$sname;
            }
            else{
                $name_info = $en_fname." ".$en_sname;
            }
            $data_purpose_uid[$uid.$queue_datetime]["name_info"] = $name_info;
        }
        // print_r($data_purpose_uid);
    }

    $query3 .= " COMMIT;";
    $stmt = $mysqli->prepare($query1);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    $styleArray = array('font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
    $styleArray_row = array('font-style'=>'bold', 'halign'=>'center');
    $header = array(
        'UID'=>'string',
        'Date of visit'=>'string',
        "Sale"=>"string",
        "Note"=>"string",
        "Queue"=>"string",
        "Name"=>"string"
    );
    
    $writer = new XLSXWriter();
    $writer->writeSheetHeader('Sheet1', $header, $styleArray);

    foreach($data_purpose_uid as $key => $values){
        $arrayTemp = array();
        foreach($values as $val){
            array_push($arrayTemp, $val);
        }
        $writer->writeSheetRow('Sheet1', $arrayTemp);
    }

    $sToday = date("Y-m-d_His");
    $file_name = "PURPOSE2_".$sToday.".xlsx";

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($file_name).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
?>