<?
include("in_session.php");
include_once("in_php_function.php");
$sMode=getQS("u_mode");
$sSid=getSS("s_id");
$sPageId=getQS("pid");
$sResult=array();
$isEcho=getQS("isecho");
include_once("in_setting_row.php");

if($sPageId=="" || $sSid == "" || $sMode=="") {
	$sResult["res"]="0";
	$sResult["msg"]="Not all data are supplied";
	$returnData = json_encode($sResult);
	if($isEcho!="0") echo($returnData);
	exit();
}


include("in_db_conn.php");

$sPTitle = getQS("ptitle");
$sPDesc = getQS("pdesc");
$sPLink = getQS("plink");
$sPEnable = getQS("penable");
$sPIcon = getQS("picon");
$sPColor = getQS("pcolor");


if($sMode=="page_add"){


	$query ="INSERT INTO i_page_list(page_id,page_title,page_desc,page_link,page_enable,page_fa_icon,page_color) VALUES(?,?,?,?,?,?,?);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssssss",$sPageId,$sPTitle,$sPDesc,$sPLink,$sPEnable,$sPIcon,$sPColor);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$sResult["msg"]=getPageRow($sPageId,$sPTitle,$sPDesc,$sPLink,$sPEnable,$sPIcon,$sPColor);
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
}else if($sMode=="page_update"){


	$query ="UPDATE i_page_list
	SET page_title = ? ,page_desc = ?,page_link = ?,page_enable = ?,page_fa_icon = ?,page_color=?
	WHERE page_id = ?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("sssssss",$sPTitle,$sPDesc,$sPLink,$sPEnable,$sPIcon,$sPColor,$sPageId);
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


}else if($sMode=="page_delete"){


	$query ="DELETE FROM i_page_list
	WHERE page_id = ?;";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("s",$sPageId);
	if($stmt->execute()){
		$iAff = $mysqli->affected_rows;
		if($iAff > 0){
			$sResult["res"] = 1;
			$query ="DELETE FROM i_section_permission
			WHERE page_id = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s",$sPageId);
			if($stmt->execute()){

			}
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