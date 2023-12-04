<?
include("in_session.php");
include_once("in_php_function.php");
$sSID = getSS("s_id");
$sUid = getQS("uid");

$sColDate=getQS("collect_date");
$sColTime=getQS("collect_time");
if($sColDate=="") $sColDate=getQS("coldate");
if($sColTime=="") $sColTime=getQS("coltime");
/*
$sColDate = getQS("coldate");
$sColTime= urldecode(urldecode(getQS("coltime")));
*/
$sMode=getQS("mode");
$sFileId = getQS("fileid");
$sHtml="";
include("in_db_conn.php");
//echo "<option>yyyy $sUid $sColDate $sColTime / $sMode</option>";
if($sMode==""){
	//Get the list of PDF File
	$query = "SELECT file_id,file_text FROM p_lab_result_pdf WHERE uid=? AND collect_date=? AND collect_time=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

	if($stmt->execute()){
		$stmt->bind_result($file_id,$file_text );
		while ($stmt->fetch()) {
			$sHtml .= "<option value='".$file_id."' title='".$file_id."' >".$file_id.":".$file_text."</option>";

		}
	}
}else if($sMode=="delete_pdf"){

	//error_log("After revoke_order_status update lab result");
	$query =" DELETE FROM p_lab_result_pdf WHERE uid=? AND collect_date=? AND collect_time=? AND file_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sUid,$sColDate,$sColTime,$sFileId);
	$sHtml = $sFileId;
	$sLogDetail = "Delete PDF,".$sUser.",".$sUid.",".$sColDate.",".$sColTime.",".$sFileId;
	if($stmt->execute()){
		//error_log("After revoke_order_status update lab result COMPLETE");

		$sToday = date("Y-m-d-His");
		$sFileTime = str_replace(":", "", $sColTime);
		$sCurFile = "../weclinic/lab/pdf_result/".$sUid."_".$sColDate."_".$sFileTime."_".$sFileId.".pdf";
		$sNewFile = "../weclinic/lab/pdf_result/".$sUid."_".$sColDate."_".$sFileTime."_".$sFileId."_REM_".$sToday.".pdf";
		rename($sCurFile,$sNewFile);
	}else{
		$msg_error = "Error Delete Lab PDF";
		$msg_info = "Can't execute prepare Delete Lab PDF";
		$sHtml = 0;

	}

	//Update Log
	if($sHtml!=""){
		$query = "INSERT INTO j_db_fix_log (s_id,log_detail) VALUES(?,?);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUser,$sLogDetail);
		if($stmt->execute()){
		//error_log("After revoke_order_status update lab result COMPLETE");
		}else{
			$msg_error = "Error Enter Log";
			$msg_info = "Can't Insert Log Details";
			$sHtml = 0;
		}
	}
}
$mysqli->close();
echo($sHtml);
?>
