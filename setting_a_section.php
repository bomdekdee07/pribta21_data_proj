<?
include("in_session.php");
include_once("in_php_function.php");
$sMode=getQS("u_mode");
$sSid=getSS("s_id");
$sSecId=getQS("secid");
$sResult=array();
$isEcho=getQS("isecho");
include_once("in_setting_row.php");

if($sSecId=="" || $sSid == "" || $sMode=="") {
	$sResult["res"]="0";
	$sResult["msg"]="Not all data are supplied";
	$returnData = json_encode($sResult);
	if($isEcho!="0") echo($returnData);
	exit();
}


include("in_db_conn.php");

if($sMode=="sec_add"){
	$sName = getQS("secname");
	$sNote = urldecode(getQS("secnote"));
	$sEnable = getQS("secenable");


	$query ="INSERT INTO p_staff_section(section_id,section_name,section_note,section_enable) VALUES(?,?,?,?);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sSecId,$sName,$sNote,$sEnable);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$sResult["msg"]=getSecRow($sSecId,$sName,$sNote,$sEnable);
			/* Add Log
				$query = "INSERT INTO i_room_log(clinic_id,room_number,s_id,log_datetime) VALUES(?,?,?,NOW());";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss",$sClinic,$sRoom,$sSid);
				if($stmt->execute()){

				}
			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}else if($sMode=="sec_update"){
	$sName = getQS("secname");
	$sNote = getQS("secnote");
	$sEnable = getQS("secenable");

	$query ="UPDATE p_staff_section
	SET section_name = ? ,section_note = ?,section_enable = ?
	WHERE section_id = ?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("ssss",$sName,$sNote,$sEnable,$sSecId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			/* Add Log
				$query = "INSERT INTO i_room_log(clinic_id,room_number,s_id,log_datetime) VALUES(?,?,?,NOW());";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss",$sClinic,$sRoom,$sSid);
				if($stmt->execute()){

				}
			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}


}else if($sMode=="sec_delete"){


	$query ="DELETE FROM p_staff_section
	WHERE section_id = ?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("s",$sSecId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$query ="DELETE FROM i_section_permission
			WHERE section_id = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s",$sSecId);
			if($stmt->execute()){

			}
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}


}else if($sMode=="sec_perm_add"){
	$sPageTitle=urldecode(getQS("pagetitle"));
	$sPageId=getQS("pageid");
	$sPageAllow=getQS("pageallow");
	$sStartD = getQS("startd");
	$sStopD = getQS("stopd");
	$sPageSeq = getQS("pseq");
	$sAdmin = getQS("isadmin");

	$query ="INSERT INTO i_section_permission(section_id,page_id,page_allow,start_date,stop_date,page_seq,is_admin) VALUES(?,?,?,?,?,?,?);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssssss",$sSecId,$sPageId,$sPageAllow,$sStartD,$sStopD,$sPageSeq,$sAdmin);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$sResult["msg"]=getSecPermRow($sSecId,$sPageId,$sPageTitle,$sPageAllow,$sStartD,$sStopD,$sPageSeq,$sAdmin);
			/* Add Log

			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}	

}else if($sMode=="sec_perm_update"){
	$sPageTitle=urldecode(getQS("pagetitle"));
	$sPageId=getQS("pageid");
	$sPageAllow=getQS("pageallow");
	$sStartD = getQS("startd");
	$sStopD = getQS("stopd");
	$sPageSeq = getQS("pseq");
	$sAdmin = getQS("isadmin");
	
	$query ="UPDATE i_section_permission SET page_allow =?,start_date=?,stop_date=?,page_seq=?,is_admin=? WHERE section_id=? AND page_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssssss",$sPageAllow,$sStartD,$sStopD,$sPageSeq,$sAdmin,$sSecId,$sPageId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			/* Add Log

			*/
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}	

}else if($sMode=="sec_perm_delete"){
	$sPageId = getQS("pageid");

	$query ="DELETE FROM i_section_permission
	WHERE section_id = ? AND page_id=?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("ss",$sSecId,$sPageId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}
}


$mysqli->close();



$returnData = json_encode($sResult);
if($isEcho!="0") echo($returnData);


?>