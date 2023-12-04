<?
include_once("in_php_function.php");
$sMode=getQS("u_mode");
$sUid=getQS("uid");
$sColDate=getQS("coldate");
$sColTime=urlDecode(getQS("coltime"));
$sFormList=urlDecode(getQS("formlist"));
$aResult=array();
$noEcho=getQS("noecho");
$sAddMsg=getQS("addmsg");
$sQ=getQS("q");
$sToday=date("Y-m-d");

$aResult["res"]="0";

if($sUid=="" || $sColDate=="" || $sColTime=="" || $sFormList==""){
	
	$returnData = json_encode($aResult);
	if($noEcho!=true) echo($returnData);
	exit();
}


include("in_db_conn.php");
$iAff = 0;
if($sMode=="check_form_done"){
	$aFormList = explode(",",$sFormList);
	$sFormList = "'".implode("','",$aFormList)."'";

	$aDataList = array();
	$query ="SELECT form_id,record_datetime,update_datetime FROM p_data_form_done WHERE uid=? AND collect_date=? AND collect_time=? AND is_done = 1 AND form_id IN (".$sFormList.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
	if($stmt->execute()){
		$stmt->bind_result($form_id,$record_datetime,$update_datetime);
		 while ($stmt->fetch()) {
		 	$aResult["res"]="1";
		 	$aDataList[] = array("form_id"=>$form_id,"record_date"=>$record_datetime,"update_date"=>$update_datetime);
		 }
		 
	}
	if(count($aDataList)>0) $aResult['datalist'] = $aDataList;
}


if($sAddMsg=="1"){
	if($sQ==""){
		//getQ
		$query ="SELECT queue FROM k_visit_data WHERE uid=? AND date_of_visit=? AND time_of_visit=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
		if($stmt->execute()){
			$stmt->bind_result($queue);
			 while ($stmt->fetch()) {
			 	$sQ=$queue;
			 }
		}
	}

	if($sQ!=""){
	$query = "INSERT INTO i_messenger (room_no,queue,msg_by,msg_title,record_datetime)
		VALUES (2,?,'SYSTEM','QN_DONE',NOW());";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sQ);
		if($stmt->execute()){
			$AffRow =$stmt->affected_rows;
			if($AffRow > 0) {
				//SaveSuccess
				//$aRes["res"] = "1";
			}else{
				//$aRes["res"] = "0";
				//$aRes["msg"] = "Error create k_visit_data";
			}
		}
	}

}

$mysqli->close();

$returnData = json_encode($aResult);
if($noEcho!=true) echo($returnData);

?>