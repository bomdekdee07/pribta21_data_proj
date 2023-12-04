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

if($sMode=="exp_perm_add"){
	$sFormId=getQS("formid");
	$sView=getQS("allowview");
	$sEdit=getQS("allowedit");
	$sExport=getQS("allowexport");
	$sStartD=getQS("startd");
	$sStopD=getQS("stopd");
	$sFormName=getQS("formname");
	$query ="INSERT INTO i_form_permission(section_id,form_id,allow_view,allow_edit,allow_export,start_date,stop_date) VALUES(?,?,?,?,?,?,?);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssssss",$sSecId,$sFormId,$sView,$sEdit,$sExport,$sStartD,$sStopD);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$sResult["msg"]=getExpPermRow($sSecId,$sFormId,$sFormName,$sView,$sEdit,$sExport,$sStartD,$sStopD);
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
}else if($sMode=="exp_perm_update"){
	$sFormId=getQS("formid");
	$sView=getQS("allowview");
	$sEdit=getQS("allowedit");
	$sExport=getQS("allowexport");
	$sStartD=getQS("startd");
	$sStopD=getQS("stopd");

	$query ="UPDATE i_form_permission
	SET allow_view=?,allow_edit=?,allow_export=?,start_date=?,stop_date=?
	WHERE section_id = ? AND form_id=?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("sssssss",$sView,$sEdit,$sExport,$sStartD,$sStopD,$sSecId,$sFormId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
		}else{
			$sResult["res"] = "0";
			$sResult["msg"] = $stmt->error;
		}
	}


}else if($sMode=="exp_perm_delete"){
	$sFormId=getQS("formid");
	$query ="DELETE FROM i_form_permission
	WHERE section_id = ? AND form_id=?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("ss",$sSecId,$sFormId);
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