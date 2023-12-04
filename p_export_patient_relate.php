<?
    include("in_session.php");
    include('in_db_conn.php');
    include_once("class_xlsxwriter.php");
    include_once("in_php_function.php");

    $proj_id = getQS("projid");
    // $proj_id = "POC";

    $sex_text = array("1" => "ชาย", "2" => "หญิง", "3" => "มีเพศสรีระทั้งชายและหญิง");
    $gender_text = array("1" => "ไม่แน่ใจ", "2" => "ชาย", "3" => "หญิง", "4" => "ชายข้ามเพศเป็นหญิง", "5" => "หญิงข้ามเพศเป็นชาย", "6" => "เกย์", "7" => "เลสเบี้ยน", "8" => "ไม่อยู่ในกรอกเพศชายหญิง", "9" => "ไม่ขอตอบ");
    $data_patient_relate_uid = array();
    $query = "select
        info_relate.uid,
        info_relate.rel_uid,
        info_relate.rel_type,
        p_info.sex,
        p_info.gender
    from patient_info_relate info_relate
    JOIN p_project_uid_list p_project_uid on(p_project_uid.uid = info_relate.rel_uid)
    left join patient_info p_info on(p_info.uid = p_project_uid.uid)
    where proj_id = ?
    order by uid;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $proj_id);

    if($stmt->execute()){
        $stmt->bind_result($uid, $rel_uid, $rel_type, $sex, $gender);

        while ($stmt->fetch()) {
            $data_patient_relate_uid[$uid.$rel_uid]["uid"] = $uid;
            $data_patient_relate_uid[$uid.$rel_uid]["ref"] = $rel_uid;
            $data_patient_relate_uid[$uid.$rel_uid]["type"] = $rel_type;
            $data_patient_relate_uid[$uid.$rel_uid]["sex"] = isset($sex)?$sex_text[$sex]:"";
            $data_patient_relate_uid[$uid.$rel_uid]["gebder"] = isset($gender)?$gender_text[$gender]:"";
        }
        // print_r($data_patient_relate_uid);
    }

    $stmt->close();

    $data_patient_uid = array();
    $query = "select p_project_uid.uid, 
        info_relate.rel_uid, 
        info_relate.rel_type,
        p_info.sex,
        p_info.gender
    from p_project_uid_list p_project_uid
    join patient_info_relate info_relate on(info_relate.uid = p_project_uid.uid)
    left join patient_info p_info on(p_info.uid = info_relate.rel_uid)
    where proj_id = ?
    order by uid;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $proj_id);

    if($stmt->execute()){
        $stmt->bind_result($uid, $rel_uid, $rel_type, $sex, $gender);

        while ($stmt->fetch()) {
            $data_patient_uid[$uid.$rel_uid]["uid"] = $uid;
            $data_patient_uid[$uid.$rel_uid]["ref"] = $rel_uid;
            $data_patient_uid[$uid.$rel_uid]["type"] = $rel_type;
            $data_patient_uid[$uid.$rel_uid]["sex"] = isset($sex)?$sex_text[$sex]:"";
            $data_patient_uid[$uid.$rel_uid]["gebder"] = isset($gender)?$gender_text[$gender]:"";
        }
        // print_r($data_patient_uid);
    }

    $stmt->close();
    $mysqli->close();

    $styleArray = array('font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
    $styleArray_row = array( 'font-style'=>'bold', 'halign'=>'center');
    $header = array(
        'UID'=>'string',
        'UID Ref'=>'string',
        'Ref Type'=>'string',
        "Sex" => "string",
        "Gender" => "string"
    );
    
    $writer = new XLSXWriter();
    $writer->writeSheetHeader('Sheet1', $header, $styleArray);

    // $count_loop = count($data_patient_relate_uid);
    // echo $count_loop;
    foreach($data_patient_relate_uid as $key => $values){
        $arrayTemp = array();
        foreach($values as $val){
            array_push($arrayTemp, $val);
        }
        $writer->writeSheetRow('Sheet1', $arrayTemp);

        unset($data_patient_uid[$key]);
    }

    $arrayTemp = array();
    foreach($data_patient_uid as $val){
        $writer->writeSheetRow('Sheet1', $val);
    }

    $sToday = date("Y-m-d_His");
    $file_name = $proj_id."_".$sToday.".xlsx";

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($file_name).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
?>