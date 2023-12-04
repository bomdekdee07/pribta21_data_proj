<?
include("in_session.php");
include_once("in_php_encode.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$aRes=array();
$isEcho=getQS("echo");

include("in_db_conn.php");
include("array_post.php");

if($aPost["u_mode"]=="projauth_add"){

	$query = "INSERT INTO p_staff_auth (".$sInsCol.") VALUES (".$sInsVal.");";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
	  	$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;		
			$aRes["msg"] = getProjAuthList($aPost["s_id"],"",$aPost["proj_id"],$aPost["allow_view"],$aPost["allow_enroll"],$aPost["allow_schedule"],$aPost["allow_data"],$aPost["allow_data_log"],$aPost["allow_lab"],$aPost["allow_export"],$aPost["allow_query"],$aPost["allow_delete"],$aPost["allow_data_backdate"],$aPost["allow_admin"] );
		}else{
			$aRes["res"] = 0;
			$aRes["msg"] = "Duplicate Key";
		}
	}
}else if($aPost["u_mode"]=="projauth_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE p_staff_auth SET ".$sUpdSet." WHERE ".$sUpdWhere;

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}else{
				$aRes["res"] = 0;
				$aRes["msg"] = "No Row Updated";
			}
		}
	}
}else if($aPost["u_mode"]=="projauth_del"){

	$sProjId = getQS("projid");
	$sSid = getQS("sid");
	if($sProjId=="" || $sSid==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "Project Id or S_ID is not provide";
	}else{
		$query = "DELETE FROM p_staff_auth WHERE proj_id=? AND s_id=?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sProjId,$sSid);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}

}else if($aPost["u_mode"]=="projauth_find"){

	$sProjId = getQS("projid");
	$sSid = getQS("sid");
	if($sProjId=="" || $sSid==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "Project Id or S_ID is not provide";
	}else{
		$query = "SELECT proj_id,PSA.s_id,s_name,allow_view,allow_enroll,allow_schedule,allow_data,allow_data_log,allow_lab,allow_export,allow_query,allow_delete,allow_data_backdate,allow_admin FROM p_staff PS
		LEFT JOIN p_staff_auth PSA
		ON PSA.s_id = PS.s_id
		AND proj_id=?
		WHERE PS.s_id=?
		 ";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sProjId,$sSid);
		if($stmt->execute()){
		$stmt->bind_result($proj_id,$s_id,$s_name,$allow_view,$allow_enroll,$allow_schedule,$allow_data,$allow_data_log,$allow_lab,$allow_export,$allow_query,$allow_delete,$allow_data_backdate,$allow_admin);
			while ($stmt->fetch()) {
				$aRes["res"] = 1;
				$aRes["isnew"] = (is_null($s_id)?"1":"0");
				$aRes["s_name"]=$s_name;
				$aRes["allow_view"] = $allow_view;
				$aRes["allow_enroll"] = $allow_enroll;
				$aRes["allow_schedule"] = $allow_schedule;
				$aRes["allow_data"] = $allow_data;
				$aRes["allow_data_log"] = $allow_data_log;
				$aRes["allow_lab"] = $allow_lab;
				$aRes["allow_export"] = $allow_export;
				$aRes["allow_query"] = $allow_query;
				$aRes["allow_delete"] = $allow_delete;
				$aRes["allow_data_backdate"] = $allow_data_backdate;
				$aRes["allow_admin"] = $allow_admin;
			}
		}
	}

}
$mysqli->close();



$returnData = json_encode($aRes);
if($isEcho!="0") echo($returnData);


?>