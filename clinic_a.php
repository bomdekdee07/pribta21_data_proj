<?
include_once("in_php_function.php");
include_once("in_setting_row.php");
$aRes=array();

include("array_post.php");

include("in_db_conn.php");
if($aPost["u_mode"]=="clinic_add"){
	$query = "INSERT INTO p_clinic (".$sInsCol.") VALUES (".$sInsVal.");";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
	  	$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;		
			$aRes["msg"] = getClinicList($aPost["clinic_id"],$aPost["clinic_name"],$aPost["clinic_address"],$aPost["clinic_email"],$aPost["clinic_tel"],$aPost["clinic_status"],$aPost["main_clinic_id"],$aPost["old_clinic_id"]);
		}
	}
}else if($aPost["u_mode"]=="clinic_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE p_clinic SET ".$sUpdSet." WHERE ".$sUpdWhere;
		
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}else if($aPost["u_mode"]=="clinic_del"){

	$sClinicId = getQS("clinicid");
	if($sClinicId==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "Clinic Id is not provide";
	}else{
		$query = "DELETE FROM p_clinic WHERE clinic_id=?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sClinicId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}

}

$mysqli->close();

$sTemp=json_encode($aRes);
echo($sTemp);
?>