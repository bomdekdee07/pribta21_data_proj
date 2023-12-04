<?
//JENG
include("in_session.php");
include_once("in_php_function.php");
$sMode=getQS("u_mode");
$sSid=getSS("s_id");
$sUid=getQS("uid");
$sColDate=urldecode(getQS("coldate"));
$sColTime=urldecode(getQS("coltime"));
$isEcho=getQS("isecho");
$aRes=array();
$aRes["res"]="0";

include_once("in_setting_row.php");


if($sSid=="") {
	$aRes["res"]="99";
	$aRes["msg"]="Session Expired. Please login again.";
	$returnData = json_encode($aRes);
	if($isEcho!="0") echo($returnData);
	exit();
}

include("in_db_conn.php");

if($sMode=="save_lab_result"){
	$aObj = isset($_POST["aobjdata"])?$_POST["aobjdata"]:[];

	if($sUid=="" || $sColDate == "" || $sColTime=="" || $sMode=="") {
		$mysqli->close();
		$aRes["res"]="0";
		$aRes["msg"]="Not all data are supplied";
		$returnData = json_encode($aRes);
		if($isEcho!="0") echo($returnData);
		exit();
	}

/*
aobjdata[0][labid]: ALT
aobjdata[0][labres]: 3
aobjdata[0][labext]: 0
aobjdata[0][labrep]: 1 U/L
aobjdata[0][labstat]: L1
aobjdata[0][labnote]: asdfdf
*/
	$sNow = date("Y-m-d h:i:s");
	$query ="INSERT INTO p_lab_result(uid,collect_date,collect_time,lab_id,lab_result,lab_result_report,lab_result_note,lab_result_status,external_lab,time_lastupdate,time_confirm) VALUES(?,?,?,?,?,?,?,?,?,?,'0000-00-00') ON DUPLICATE KEY UPDATE lab_result=VALUES(lab_result), lab_result_report=VALUES(lab_result_report), lab_result_note=VALUES(lab_result_note), lab_result_status=VALUES(lab_result_status),external_lab=VALUES(external_lab),time_lastupdate=NOW(),time_confirm=VALUES(time_confirm);";
	$stmt = $mysqli->prepare($query);
		$aFailId = [];
	foreach ($aObj as $key => $aVal) {
		$sLabId = urldecode($aVal["labid"]);
		$sRes = urldecode($aVal["labres"]);
		$sExt = ($aVal["labext"]);
		$sRep = urldecode($aVal["labrep"]);
		$sStat = ($aVal["labstat"]);
		$sNote = urldecode($aVal["labnote"]);

		$stmt->bind_param("ssssssssss",$sUid,$sColDate,$sColTime,$sLabId,$sRes,$sRep,$sNote,$sStat,$sExt,$sNow);
		if($stmt->execute()){
			$iAff = $mysqli->affected_rows;
			if($iAff > 0){

			}else{
				$aFailId[$sLabId] = $stmt->error;
			}
		}
	}

	$query="UPDATE p_lab_order SET lab_order_status='A3' WHERE uid=? AND collect_date=? AND collect_time=? ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
	$isRowFound=false;
	if($stmt->execute()){
	}
	if(count($aFailId)>0){
		$aRes["res"]="0";
		$aRes["msg"]="Not all row saved.";
		$aRes["errlist"]=implode(",",$aFailId);
		$aRes["time"]=$sNow;
	}else{
		$aRes["res"]="1";
		$aRes["time"]=$sNow;
	}
}else if($sMode=="confirm_lab"){
	$sNow = date("Y-m-d h:i:s");
	$sMT=getQS("mt");
	$query =" UPDATE p_lab_result SET time_confirm=? WHERE uid =? AND collect_date =? AND collect_time =? AND lab_result != '' AND (time_confirm='0000-00-00 00:00:00' OR time_confirm = NULL) ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sNow,$sUid,$sColDate,$sColTime );
	$iAff=0;
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$aRes["res"]="1";
			$aRes["time"]=$sNow;

			$query="SELECT time_confirm FROM 
			(SELECT IFNULL(time_confirm,'0000-00-00 00:00:00') AS time_confirm FROM p_lab_order_lab_test PLOLT
			LEFT JOIN p_lab_result PLR
			ON PLR.uid=PLOLT.uid
			AND PLR.collect_date=PLOLT.collect_date
			AND PLR.collect_time=PLOLT.collect_time
			AND PLR.lab_id=PLOLT.lab_id

			WHERE PLOLT.uid=? AND PLOLT.collect_date=? AND PLOLT.collect_time=?) AS TBL_CONFIRM
			WHERE time_confirm ='0000-00-00 00:00:00'";

			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
			$isRowFound=false;
			if($stmt->execute()){
			  $stmt->bind_result($lab_result );
			  while ($stmt->fetch()) {
				$isRowFound = true;
			  }
			}
			if($isRowFound==false){
				//No row found
				$query="UPDATE p_lab_order SET lab_order_status='A4' WHERE uid=? AND collect_date=? AND collect_time=? ";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
				$isRowFound=false;
				if($stmt->execute()){
				}
			}
		}else{
			$aRes["res"]="0";
			$aRes["msg"]=$stmt->error;
		}
	}
}else if($sMode=="update_lab_seq"){
	$aObjList = getQSObj("itemlist");

	$sKey="";
	foreach ($aObjList as $iKey => $sLabString) {
		$aTemp = explode(",",$sLabString);
		$sKey.= (($sKey=="")?"":",")."('".$aTemp[0]."','".$aTemp[1]."','".$aTemp[2]."')";
	}
}

$query="INSERT INTO p_lab_test(lab_id,lab_group_id,lab_seq) VALUES ".$sKey." ON DUPLICATE KEY UPDATE lab_seq=VALUES(lab_seq);";
$stmt = $mysqli->prepare($query);
if($stmt->execute()){
	$iAffRow =$stmt->affected_rows;
	if($iAffRow > 0) {
		$aRes["res"]="1";
	}
}

$mysqli->close();

$returnData = json_encode($aRes);
if($isEcho!="0") echo($returnData);


?>