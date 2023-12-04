<?
    include("in_session.php");
    include('in_db_conn.php');
    include_once("class_xlsxwriter.php");
    include_once("in_php_function.php");

    function sex_text($val_sex){
        $sex_text = array(0 => "",1 => "ชาย", 2 => "หญิง");

        return $sex_text[$val_sex];
    }

    $visit_date = isset($_POST["visitdate"])? $_POST["visitdate"] : getQS("visitdate");

    $data_dx = array();
    $query = "SELECT KP.uid,
        PI.fname,
        PI.sname,
        PI.date_of_birth,
        PI.sex,
        visit_date,
        visit_time,
        p3_dx,
        time_record
    FROM k_physician KP
    LEFT JOIN patient_info PI ON (PI.uid = KP.uid)
    WHERE visit_date = ?
    AND KP.uid != ''
    ORDER BY KP.uid, KP.time_record;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $visit_date);

    if($stmt->execute()){
        $stmt->bind_result($uid, $fname, $sname, $date_of_birth, $sex, $visit_date, $visit_time, $p3_dx, $time_record);
        while ($stmt->fetch()) {
            $data_dx[$uid]["uid"] = $uid;
            $data_dx[$uid]["name"] = $fname." ".$sname;
            $data_dx[$uid]["date_of_birth"] = $date_of_birth;
            if($sex != ""){
                $data_dx[$uid]["sex"] = sex_text($sex);
            }
            else{
                $data_dx[$uid]["sex"] = sex_text(0);
            }
            $data_dx[$uid]["visit_date"] = $visit_date;
            $data_dx[$uid]["visit_time"] = $visit_time;
            $data_dx[$uid]["dx"] = $p3_dx;
            $data_dx[$uid]["time_rec"] = $time_record;
        }
        // print_r($data_dx);
    }

    $stmt->close();
    $mysqli->close();

    $styleArray = array('font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
    $styleArray_row = array('font-style'=>'bold', 'halign'=>'center');

    $header = array(
        'UID'=>'string',
        'Name'=>'string',
        'Date of birth'=>'string',
        'Sex'=>'string',
        'Date of visit'=>'string',
        'Time of visit'=>'string',
        "Dx"=>"string",
        "Time record"=>"string"
    );

    $writer = new XLSXWriter();
    $writer->writeSheetHeader('Sheet1', $header, $styleArray);

    foreach($data_dx as $key => $values){
        $arrayTemp = array();
        foreach($values as $val){
            array_push($arrayTemp, $val);
        }
        $writer->writeSheetRow('Sheet1', $arrayTemp);
    }

    // $sToday = date("Ymd");
    $file_name = "DX_".$visit_date.".xlsx";

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($file_name).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
?>