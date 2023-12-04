<?
//include_once("in_session.php");
include_once("class_xlsxwriter.php");
include_once("in_php_function.php");
$sFormList = getQS("formlist");
$sStartD = getQS("strdate");
$sStopD = getQS("stpdate");
$sMode = getQS("mode");
$sLog =getQS("loglist");
set_time_limit(180);
$sColInfo = "'line','html','q_label','colhead'";
if($sMode=="export_xls"){
	include("in_db_conn.php");
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");
	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();
	//Create form list
	$aFormList = explode(",",$sFormList);
	$sFormList = "'".implode("','",$aFormList)."'";

  	$sFormList = str_replace("'',","",$sFormList); //edit2021-11-09 add to remove '',  in formlist

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
	$aPInfo=array();
	$aDataForm=array();


	foreach ($aFormList as $key => $form_id) {
		$aShHeader[$form_id]=array("uid","collect_date","collect_time", "sex", "gender", "dob");
		$aShRow[$form_id]=array("uid"=>"","collect_date"=>"","collect_time"=>"","sex"=>"","gender"=>"","dob"=>"");
	}

	foreach ($aLogList as $key => $form_id) {
		$aShHeader[$form_id]=array("uid","collect_date","row_id");
		$aShRow[$form_id]=array("uid"=>"","collect_date"=>"","collect_time"=>"");
	}

	$sAllList = $sFormList; $aDataList=array();
	if($sLogList!="") $sAllList .= (($sAllList=="")?"":",").$sLogList;


	$query = "SELECT PFLD.form_id,data_id,data_seq FROM p_form_list_data PFLD
	LEFT JOIN p_form_list PFL
	ON PFL.form_id = PFLD.form_id
	 WHERE PFLD.form_id IN (".$sAllList.") AND data_type NOT IN (".$sColInfo.") ORDER BY data_seq";
	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
	  $stmt->bind_result($form_id,$data_id,$data_seq);
	  while ($stmt->fetch()) {
		$aFormItem[$form_id][$data_id] = $data_seq;
		$aShRow[$form_id][$data_id] = "";
		array_push($aShHeader[$form_id],$data_id);
		$aDataList[$data_id]=(isset($aDataList[$data_id])?",":"")."'".$data_id."'";
		$aDataForm[$data_id]=$form_id;
	  }
	}
	$sDataIn="";
	foreach ($aDataList as $data_id => $data_id_2) {
		$sDataIn.=(($sDataIn=="")?"":",")."'".$data_id."'";
	}

	$sSQL = "";

	if($sFormList!=""){
		$sSQL = "(PDR.data_id IN (".$sDataIn.") AND IQL.collect_date >= ? AND IQL.collect_date <= ?)";
	}

	if($sLogList!=""){
		$sSQL .= (($sSQL=="")?"":" OR ")." (PDR.data_id IN (".$sDataIn."))";
	}

	$aData = array(); $aVisit=array();

	$query = "SELECT IQL.uid,IQL.collect_date,IQL.collect_time,PDR.data_id,data_result,
	P.sex, P.gender, P.date_of_birth
	FROM i_queue_list IQL
	JOIN p_data_result PDR ON PDR.uid=IQL.uid 
	-- AND PDR.collect_date=IQL.collect_date AND PDR.collect_time = IQL.collect_time
	JOIN patient_info P ON P.uid = IQL.uid
	WHERE 
		".$sSQL."
	ORDER BY IQL.uid,IQL.collect_date,IQL.collect_time ";
//echo "$sStartD,$sStopD / $query";

  
	$stmt = $mysqli->prepare($query); 

	if($sFormList != "") $stmt->bind_param("ss",$sStartD,$sStopD);
	if($stmt->execute()){
	  $stmt->bind_result($uid,$collect_date,$collect_time,$data_id,$data_result, $sex, $gender, $dob);
	  while ($stmt->fetch()) {
	  	$aVisit[$aDataForm[$data_id]][$uid][$collect_date." ".$collect_time]["coldate"]=$collect_date;
	  	$aVisit[$aDataForm[$data_id]][$uid][$collect_date." ".$collect_time]["coltime"]=$collect_time;
	  	
		//$aData[$uid][$data_id][$collect_date][$collect_time] = $data_result;
		$aData[$uid][$data_id][$collect_date." ".$collect_time]=$data_result;

		$aPInfo[$uid]["sex"] = $sex;
		$aPInfo[$uid]["gender"] = $gender;
		$aPInfo[$uid]["dob"] = $dob;
	  }
	}

	$mysqli->close();

	$aAllForm = array();
	$aAllForm = array_merge($aFormList,$aLogList);



	foreach ($aFormItem as $form_id => $aFormData) {
		$writer->writeSheetRow($form_id,$aShHeader[$form_id],$styleArray);
		if(isset($aVisit[$form_id]))
		foreach ($aVisit[$form_id] as $sUid => $aV) {
			foreach ($aV as $sKeyId => $aVi) {
				$aTemp = $aShRow[$form_id];
				$aTemp["uid"] = $sUid;
				$aTemp["collect_date"] = $aVi["coldate"];
				$aTemp["collect_time"] = $aVi["coltime"];
				$aTemp["sex"] = $aPInfo[$sUid]["sex"];
				$aTemp["gender"] = $aPInfo[$sUid]["gender"];
				$aTemp["dob"] = $aPInfo[$sUid]["dob"];
				foreach ($aFormData as $data_id => $data_seq) {
					if(isset($aData[$sUid][$data_id][$sKeyId]))
					$aTemp[$data_id]=$aData[$sUid][$data_id][$sKeyId];
				}
				$writer->writeSheetRow($form_id,$aTemp);
			}
		}

	}


	$sToday = date("Y-m-d_His");
	$sFileName = "Data_".$sStartD."-".$sStopD."_on_".$sToday.".xlsx";

	header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($sFileName).'"');
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	$writer->writeToStdOut();
}else if($sMode=="pinfo_xls"){
	include("in_db_conn.php");

	$styleArray = array( 'font-style'=>'bold', 'fill'=>'#2980b9', 'halign'=>'center');
	$writer = new XLSXWriter();

	$iRowCount=0;

	$writer->writeSheetRow("PINFO",array("uid","uic","fname","sname","nickname","clinic_type","sex","gender","date_of_birth","nation","religion","blood_type","citizen_id","passport_id","id_address","id_district","id_province","id_zone","id_postal_code","use_id_address","address","district","province","zone","postal_code","country_other","tel_no","email","line_id","em_name_1","em_relation_1","em_phone_1","em_name_2","em_relation_2","em_phone_2","last_modify_date","remark","prep_nhso"));

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");
	
	$query = "SELECT uid,uic,fname,sname,nickname,clinic_type,sex,gender,date_of_birth,nation,religion,blood_type,citizen_id,passport_id,id_address,id_district,id_province,id_zone,id_postal_code,use_id_address,address,district,province,zone,postal_code,country_other,tel_no,email,line_id,em_name_1,em_relation_1,em_phone_1,em_name_2,em_relation_2,em_phone_2,last_modify_date,remark,prep_nhso FROM patient_info  ORDER BY uid";

	
	//This one work as well..
	//$before = microtime(true);
	
	$stmt = $mysqli->prepare($query);  
	$aRow=array();
    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_row()) {
      	$aRow[]=$row;
      }
    }
	

    /*
    //This one is work but not on the same browser
    $bNoStop = true; $iStart=0; $iStop=5000;
    while($bNoStop){
		$stmt = $mysqli->query($query." LIMIT $iStart,$iStop", MYSQLI_USE_RESULT);
		$iRowCount=0;
		if ($stmt) {
		   while ($row = $stmt->fetch_assoc()) {
		       $aRow[]=$row;
		       $iRowCount++;
		   }
		} 
		$stmt->close();
		 if($iRowCount==0 || $iRowCount < $iStop)$bNoStop=false;
		 else $iStart+=$iStop;
    }

	$stmt = $mysqli->prepare($query);  
	if($stmt->execute()){
	  $stmt->bind_result($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$gender,$date_of_birth,$nation,$religion,$blood_type,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$last_modify_date,$remark,$prep_nhso);
	  while ($stmt->fetch()) {
		$writer->writeSheetRow("PINFO",array($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$gender,$date_of_birth,$nation,$religion,$blood_type,"".$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$last_modify_date,$remark,$prep_nhso));
	  }
	}
	


	$after = microtime(true);
	error_log(($after-$before)/1);
	*/

	$mysqli->query("COMMIT;");
	$mysqli->close();
	
	
	foreach ($aRow as $key => $row) {
		$writer->writeSheetRow("PINFO",$row);
	}
	
	

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
	WHERE PFLD.form_id IN (".$sFormList.") AND PFLD.data_type NOT IN (".$sColInfo.") ORDER BY PFLD.form_id,data_seq";
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
		WHERE PFLD.form_id IN (".$sFormList.") AND PFLD.data_type NOT IN (".$sColInfo.")
		ORDER BY form_id,PFLD.data_seq,PDSL.data_id,PDSL.data_seq";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($form_id,$data_id,$data_value,$data_name_th,$data_name_en,$data_seq);
	  while ($stmt->fetch()) {
	  	$aFormSub[$form_id][$data_id."_".$data_value]["form_id"] = $form_id;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_id"] = $data_id;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_value"] = $data_value;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_name_th"] = $data_name_th;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_name_en"] = $data_name_en;
		$aFormSub[$form_id][$data_id."_".$data_value]["data_seq"] = $data_seq;

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
