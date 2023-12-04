<?
include("in_session.php");
include_once("class_xlsxwriter.php");
include_once("in_php_function.php");
$sProjid = getQS("projid");
$sFormList = getQS("formlist");
$sStartD = getQS("strdate");
$sStopD = getQS("stpdate");
$sMode = getQS("mode");
$sLog =getQS("loglist");
$gVisitId = getQS("visitid");

// echo $sLog."<br>";
// echo $sFormList."<br>";
//error_log("data: $sProjid /$sFormList /$sStartD /$sStopD /$sMode /$sLog");

$sColInfo = "'line','html','q_label','colhead'";

// SET TIME STOP
set_time_limit(350);

if($sMode=="export_xls"){
	include("in_db_conn.php");

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");
	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();
	//Create form list
	$aFormList = explode(",",$sFormList);
	$sFormList = "'".implode("','",$aFormList)."'";
	$aLogList = explode(",",$sLog);
	$sLogList = "'".implode("','",$aLogList)."'";

	//No formlist only log is sent.
	if($sFormList=="''" || $sFormList=="") {
		$sFormList = "";
		$aFormList = array();
	}
	if($sLogList=="''" || $sLogList==""){
		$sLogList = "";
		$aLogList = array();
	}



	$aFormItem = array();
	$aShHeader = array();
	$aShRow=array();

	foreach ($aFormList as $key => $form_id) {
		$aShHeader[$form_id]=array("uid","collect_date","collect_time", "visit_id", "sex", "gender", "dob", "site");
		$aShRow[$form_id]=array("uid"=>"","collect_date"=>"","collect_time"=>"","visit_id"=>"","sex"=>"","gender"=>"","dob"=>"", "site"=>"");
	}

	foreach ($aLogList as $key => $form_id) {
		$aShHeader[$form_id]=array("uid","collect_date","row_id");
		$aShRow[$form_id]=array("uid"=>"","collect_date"=>"","collect_time"=>"");
	}

	$sAllList = $sFormList;
	if($sLogList!="") $sAllList .= (($sAllList=="")?"":",").$sLogList;

	$query = "SELECT PFLD.form_id,data_id FROM p_form_list_data PFLD
	LEFT JOIN p_form_list PFL
	ON PFL.form_id = PFLD.form_id
	WHERE PFLD.form_id IN ($sAllList) AND data_type NOT IN ($sColInfo) ORDER BY data_seq";
	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
		$stmt->bind_result($form_id,$data_id);
		while ($stmt->fetch()) {
		$aFormItem[$form_id][$data_id] = $data_id;
		$aShRow[$form_id][$data_id] = "";

		array_push($aShHeader[$form_id],$data_id);
		}
	}
	$stmt->close();

	$sSQL_where_visitid = "";
	$bind_param = "ssss";
	$array_val = array($sProjid, $sStopD, $sStartD, $sStopD);

	$convert_visitid = "";
	if($gVisitId != ""){
		$bind_param .= "";
		$gVisitId = explode(",", $gVisitId);
		// print_r($gVisitId);
		foreach($gVisitId as $val){
			if($val != "")
				$convert_visitid .= "'".$val."',";
		}
		$convert_visitid = substr($convert_visitid, 0, -1);
		// $array_val[] = $gVisitId;
		$sSQL_where_visitid .= " where MN.visit_id in ($convert_visitid) ";
	}

	$sSQL = "";
	$sSQL_Log = "";
	$sSQL_case_match_visitdate_collectdat = "";
	$sSQL_Log_join_row_id = "";
	$sSQL_Log_join_row_id_and = "";
	$sSQL_Log_select = "";
	$sSQL_Log_orderby = "";
	$sSQL_log_visit_id = "";
	$sql_visit_clinic_id = "";

	if($sProjid == "HORMONES"){
		$bind_param = "sss";
		$array_val = array($sProjid, $sStartD, $sStopD);
		$sSQL_case_match_visitdate_collectdat = " PDR.collect_date = PUV.visit_date";
	}
	else{
		$sSQL_case_match_visitdate_collectdat = " PDR.collect_date >= PUV.visit_date and PDR.collect_date <= ?";
	}

	if($sFormList!=""){
		$sSQL .= " (PFLD.form_id IN (".$sFormList.")
		AND PUV.visit_date >= ? AND PUV.visit_date <= ?)";

		$sSQL_Log .= " JOIN p_project_uid_visit PUV ON (PUV.uid=PDR.uid AND PUV.proj_id=PUL.proj_id and $sSQL_case_match_visitdate_collectdat)";
		$sSQL_Log_join_row_id .= " ";
		$sSQL_Log_join_row_id_and .= " ";
		$sSQL_Log_select .= " ";
		$sSQL_Log_select .= " PFLD.form_id";
		$sSQL_Log_orderby .= " PFLD.form_id";
		$sSQL_log_visit_id .= " (SELECT visitid.visit_id from p_project_uid_visit visitid where visitid.uid = PDR.uid and visitid.proj_id = PUL.proj_id and visitid.visit_date = PDR.collect_date limit 1) AS visit_id, ";
		$sql_visit_clinic_id .= " AND visitid.visit_date = PDR.collect_date ";
	}

	if($sLogList!=""){
		$sSQL .= " (PFLD.form_id IN (".$sLogList.")
		AND PUV.visit_date >= ? AND PUV.visit_date <= ?)";
		//(($sSQL=="")?"":" OR ")."(PFLD.form_id IN (".$sLogList."))";
		// สรุปยังไงก็ต้อง join กัน เพราะมันไม่ควรกรอกข้อมูลก่อน visit_date
		$sSQL_Log .= " JOIN p_project_uid_visit PUV ON (PUV.uid=PDR.uid AND PUV.proj_id=PUL.proj_id and (($sSQL_case_match_visitdate_collectdat) or (PUV.visit_id = logR.visit_id and PDR.collect_date = '0000-00-00')))";
		$sSQL_Log_join_row_id .= " JOIN p_data_log_row logR ON (logR.uid = PDR.uid and logR.collect_date = PDR.collect_date and logR.collect_time = PDR.collect_time)";
		$sSQL_Log_join_row_id_and .= " AND PFLD.form_id = logR.form_id";
		$sSQL_Log_select .= " logR.form_id";
		$sSQL_Log_orderby .= " logR.form_id";
		$sSQL_log_visit_id .= " (SELECT visitid.visit_id FROM p_data_log_row visitid WHERE visitid.uid = PDR.uid and visitid.collect_date = PDR.collect_date AND visitid.collect_time = PDR.collect_time AND visitid.form_id = PFLD.form_id LIMIT 1) AS visit_id, ";
		$sql_visit_clinic_id .= " AND visit_clinic_id != '' ";

		array_push($aShHeader[$form_id],"visit_id(In Form)");
		array_push($aShHeader[$form_id],"sex");
		array_push($aShHeader[$form_id],"gender");
		array_push($aShHeader[$form_id],"date_of_birth");
		array_push($aShHeader[$form_id],"site");
	}

	$aData = array(); $aVisit=array();
	$query = "SELECT * FROM (SELECT distinct ".$sSQL_Log_select.",
		PDR.uid,
		PDR.collect_date,
		PDR.collect_time, 
		PDR.data_id,
		PDR.data_result,
		$sSQL_log_visit_id
		P.sex, 
		P.gender, 
		P.date_of_birth,
		( SELECT clinic_n.visit_clinic_id FROM p_project_uid_visit clinic_n WHERE clinic_n.uid = PDR.uid AND clinic_n.proj_id = PUV.proj_id AND clinic_n.visit_id = (SELECT
			visitid.visit_id 
		FROM
			p_project_uid_visit visitid 
		WHERE
			visitid.uid = PDR.uid 
			AND visitid.proj_id = PUL.proj_id 
			$sql_visit_clinic_id
			LIMIT 1) LIMIT 1 ) AS visit_clinic_id
	FROM p_data_result PDR 
	$sSQL_Log_join_row_id
	JOIN p_form_list_data PFLD ON PFLD.data_id = PDR.data_id $sSQL_Log_join_row_id_and
	JOIN patient_info P ON P.uid = PDR.uid
	JOIN p_project_uid_list PUL ON (PUL.proj_id=? AND PUL.uid=PDR.uid AND PUL.uid_status<>'C')
	$sSQL_Log
	WHERE PFLD.data_id NOT IN ($sColInfo) AND (
		$sSQL
	)
	AND PDR.collect_time<'00:10:00'
	ORDER BY $sSQL_Log_orderby, PDR.uid, PDR.collect_date, PDR.collect_time, PUV.schedule_date) MN $sSQL_where_visitid";

	// echo $bind_param."/".$query."<br>";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($bind_param, ...$array_val);

	if($stmt->execute()){
		$stmt->bind_result($form_id,$uid,$collect_date,$collect_time,$data_id,$data_result,$visit_id, $sex, $gender, $dob, $visit_clinic_id);
		while ($stmt->fetch()) {
			$aData[$uid][$collect_date][$collect_time][$data_id] = $data_result;

			if($sLogList!=""){
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["uid"] = $uid;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["collect_date"] = $collect_date;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["collect_time"] = $collect_time;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["visit_id"] = $visit_id;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["sex"] = $sex;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["gender"] = $gender;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["dob"] = $dob;
				$aVisit[$form_id][$uid.$collect_date.$collect_time][$data_id] = $data_result;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["site"] = $visit_clinic_id;
			}
			else{
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["uid"] = $uid;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["collect_date"] = $collect_date;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["collect_time"] = $collect_time;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["visit_id"] = $visit_id;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["sex"] = $sex;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["gender"] = $gender;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["dob"] = $dob;
				$aVisit[$form_id][$uid.$collect_date.$collect_time][$data_id] = $data_result;
				$aVisit[$form_id][$uid.$collect_date.$collect_time]["site"] = $visit_clinic_id;
			}
		}

		// print_r($aVisit);
	}

	$mysqli->close();

	$aAllForm = array();
	$aAllForm = array_merge($aFormList,$aLogList);


	foreach ($aAllForm as $iKey => $form_id) {
		$writer->writeSheetRow($form_id,$aShHeader[$form_id],$styleArray);
		if(isset($aVisit[$form_id]))
		foreach ($aVisit[$form_id] as $sUDT => $aDataId) {
			$aTemp = $aShRow[$form_id];
			$sUid = $aDataId["uid"]; 
			$aTemp["uid"] = $sUid;
			$sDate = $aDataId["collect_date"]; 
			$aTemp["collect_date"] = $sDate;
			$sTime = $aDataId["collect_time"]; 
			$aTemp["collect_time"] = $sTime;
			if($sLogList!=""){
				$sVisitid = $aDataId["visit_id"]; 
				$aTemp["visit_id"] = $sVisitid;
			}
			else{
				$sVisitid = $aDataId["visit_id"]; 
				$aTemp["visit_id"] = $sVisitid;
			}
			$sSex = $aDataId["sex"]; 
			$aTemp["sex"] = $sSex;
			$sGender = $aDataId["gender"]; 
			$aTemp["gender"] = $sGender;
			$sDob = $aDataId["dob"]; 
			$aTemp["dob"] = $sDob;
			$sSite = $aDataId["site"]; 
			$aTemp["site"] = $sSite;

			foreach ($aDataId as $data_id => $sData) {
				$aTemp[$data_id] = $sData;
			}
			$writer->writeSheetRow($form_id,$aTemp);
			unset($aTemp);
		}
		unset($aVisit[$form_id]);
	}

	$sToday = date("Y-m-d_His");
	$sFileName = "Data_".$sStartD."-".$sStopD."_on_".$sToday.".xlsx";

	header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($sFileName).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
}
else if($sMode=="projvisit_xls"){
	include("in_db_conn.php");

	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();

	$query = "SELECT PUV.proj_id,PUV.group_id,PUL.pid,PUV.uid,
	PUV.visit_id,PUV.schedule_date,PUV.visit_date,PUV.visit_clinic_id,  PS.status_name, concat(P.fname, ' ', P.sname), P.tel_no, PUV.visit_note
	FROM p_project_uid_visit PUV
	LEFT JOIN p_project_uid_list PUL ON PUL.uid=PUV.uid and PUL.proj_id = PUV.proj_id
	LEFT JOIN p_visit_status PS ON PS.status_id=PUV.visit_status
	LEFT JOIN patient_info P ON P.uid=PUV.uid
	WHERE PUV.proj_id=?
	ORDER BY PUV.proj_id, PUV.group_id, PUL.pid, PUV.schedule_date ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sProjid);

	$writer->writeSheetRow("visit_$sProjid",array("proj_id","group_id","pid","uid",
	"visit_id","schedule_date","visit_date","clinic_id", "status_id", "Name", "Tel No", "visit_note"));

	if($stmt->execute()){
	  $stmt->bind_result($proj_id,$group_id,$pid,$uid,
	$visit_id,$schedule_date,$visit_date,$visit_clinic_id, $status_name, $patient_name, $tel_no, $visit_note);
	  while ($stmt->fetch()) {
		$writer->writeSheetRow("visit_$sProjid",array($proj_id,$group_id,$pid,$uid,
	$visit_id,$schedule_date,$visit_date,$visit_clinic_id, $status_name, $patient_name, $tel_no, $visit_note));
	  }
	}
	$mysqli->close();
	$sToday = date("Y-m-d_His");
	$sFileName = "visit_$sProjid"."_".$sToday.".xlsx";
	header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($sFileName).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
	exit();
}else if($sMode=="pinfo_xls"){
	include("in_db_conn.php");

	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();

	$query = "SELECT PUL.pid, P.uid,P.uic,fname,sname,nickname,clinic_type,sex,gender,date_of_birth,nation,religion,blood_type,citizen_id,passport_id,id_address,id_district,id_province,id_zone,id_postal_code,use_id_address,address,district,province,zone,postal_code,country_other,tel_no,email,line_id,em_name_1,em_relation_1,em_phone_1,em_name_2,em_relation_2,em_phone_2,last_modify_date,remark
	FROM patient_info P
	LEFT JOIN p_project_uid_list PUL ON (P.uid=PUL.uid AND PUL.proj_id=?)
	WHERE P.uid IN (select uid from p_project_uid_list where proj_id=? AND uid_status <> '10')
	ORDER BY PUL.pid";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sProjid, $sProjid);

	$writer->writeSheetRow("PINFO_$sProjid",array("pid","uid","uic","fname","sname","nickname","clinic_type","sex","gender","date_of_birth","nation","religion","blood_type","citizen_id","passport_id","id_address","id_district","id_province","id_zone","id_postal_code","use_id_address","address","district","province","zone","postal_code","country_other","tel_no","email","line_id","em_name_1","em_relation_1","em_phone_1","em_name_2","em_relation_2","em_phone_2","last_modify_date","remark"));

	if($stmt->execute()){
	  $stmt->bind_result($pid,$uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$gender,$date_of_birth,$nation,$religion,$blood_type,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$last_modify_date,$remark);
	  while ($stmt->fetch()) {
		$writer->writeSheetRow("PINFO_$sProjid",array($pid,$uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$gender,$date_of_birth,$nation,$religion,$blood_type,"".$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$last_modify_date,$remark));
	  }
	}
	$mysqli->close();
	$sToday = date("Y-m-d_His");
	$sFileName = "pinfo_".$sToday.".xlsx";
	header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($sFileName).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
	exit();
}else if($sMode=="export_datadict"){
	include("in_db_conn.php");

	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();
	//Create form list
	$aFormList = explode(",",$sFormList);
	$sFormList = "'".implode("','",$aFormList)."'";

	$aFormItem = array();
	$aShHeader = array();
	$aSubHeader=array("form_id","data_id","data_value","data_name_th","data_name_en","data_seq");

	foreach ($aFormList as $key => $form_id) {
		$aShHeader[$form_id]=array("data_id","data_export_code","data_type","data_name_th","data_question_th","data_prefix_th","data_suffix_th","data_name_en","data_question_en","data_prefix_en","data_suffix_en");

	}


	$query = "SELECT form_id,PFLD.data_id,data_export_code,PDL.data_type,data_name_th,data_question_th,data_prefix_th,data_suffix_th,data_name_en,data_question_en,data_prefix_en,data_suffix_en FROM
	p_form_list_data PFLD
	JOIN p_data_list PDL
	ON PDL.data_id = PFLD.data_id
	WHERE PFLD.form_id IN ($sFormList) AND PFLD.data_type NOT IN ($sColInfo) ORDER BY PFLD.form_id,data_seq";
	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
	  $stmt->bind_result($form_id,$data_id,$data_export_code,$data_type,$data_name_th,$data_question_th,$data_prefix_th,$data_suffix_th,$data_name_en,$data_question_en,$data_prefix_en,$data_suffix_en );
	  while ($stmt->fetch()) {
		$aFormItem[$form_id][$data_id]["data_id"] = $data_id;
		$aFormItem[$form_id][$data_id]["data_export_code"] = $data_export_code;
		$aFormItem[$form_id][$data_id]["data_type"] = $data_type;
		$aFormItem[$form_id][$data_id]["data_name_th"] = $data_name_th;
		$aFormItem[$form_id][$data_id]["data_question_th"] = $data_question_th;
		$aFormItem[$form_id][$data_id]["data_prefix_th"] = $data_prefix_th;
		$aFormItem[$form_id][$data_id]["data_suffix_th"] = $data_suffix_th;
		$aFormItem[$form_id][$data_id]["data_name_en"] = $data_name_en;
		$aFormItem[$form_id][$data_id]["data_question_en"] = $data_question_en;
		$aFormItem[$form_id][$data_id]["data_prefix_en"] = $data_prefix_en;
		$aFormItem[$form_id][$data_id]["data_suffix_en"] = $data_suffix_en;
		//array_push($aShHeader[$form_id],$data_id);
	  }
	}

	unset($data_name_en); unset($data_name_th); unset($data_id); unset($form_id);
	$aFormSub=array();
	$query = "SELECT form_id,PDSL.data_id,PDSL.data_value,data_name_th,data_name_en,PDSL.data_seq
		FROM p_data_sub_list PDSL

		JOIN p_form_list_data PFLD
		ON PFLD.data_id=PDSL.data_id
		WHERE PFLD.form_id IN ($sFormList) AND PFLD.data_type NOT IN ($sColInfo)
		ORDER BY form_id,PFLD.data_seq,PDSL.data_id,PDSL.data_seq";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($form_id,$data_id,$data_value,$data_name_th,$data_name_en,$data_seq);
	  while ($stmt->fetch()) {

			$data_value = ($data_value == '=')?"'=":$data_value;
			$data_name_th = ($data_name_th == '=')?"'=":$data_name_th;
			$data_name_en = ($data_name_en == '=')?"'=":$data_name_en;

	  $aFormSub[$form_id][$data_id."_".$data_value]["form_id"] = $form_id;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_id"] = $data_id;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_value"] = $data_value;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_name_th"] = $data_name_th;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_name_en"] = $data_name_en;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_seq"] = $data_seq;
//error_log("sub: $data_id / $data_value");
		//array_push($aShHeader[$form_id],$data_id);
		}
	}

	$mysqli->close();

	foreach ($aFormList as $iKey => $form_id) {
		if($form_id!=""){
			$writer->writeSheetRow($form_id,$aShHeader[$form_id],$styleArray);
			$writer->writeSheetRow($form_id."_sub",$aSubHeader,$styleArray);
			foreach ($aFormItem[$form_id] as $data_id => $aValue) {
				$writer->writeSheetRow($form_id,$aValue);
			}

			if(isset($aFormSub[$form_id]) ){
				foreach ($aFormSub[$form_id] as $keyId => $aRow) {
					$writer->writeSheetRow($form_id."_sub",$aRow);
				}
			}


		}

	}
	unset($aFormSub);
	unset($aFormItem);


	$sToday = date("Y-m-d_His");
	$sFileName = "DataDict_on_".$sToday.".xlsx";

	header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($sFileName).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
}

?>
