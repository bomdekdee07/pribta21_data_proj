<?
include_once("in_php_function.php");
include_once("in_setting_row.php");
$aRes=array();

include("array_post.php");
//$query = "UPDATE p_project SET ".$sUpdSet." WHERE ".$sUpdWhere;

include("in_db_conn.php");
if($aPost["u_mode"]=="proj_add"){
	$query = "INSERT INTO p_project (".$sInsCol.") VALUES (".$sInsVal.");";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
	  	$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;		
			$aRes["msg"] = getProjInfoRow($aPost["proj_id"],$aPost["proj_name"],$aPost["proj_desc"],$aPost["proj_remark"],$aPost["proj_group_amt"],$aPost["proj_pid_format"],$aPost["proj_pid_runing_digit"],$aPost["is_enable"]);
		}
	}
}else if($aPost["u_mode"]=="proj_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE p_project SET ".$sUpdSet." WHERE ".$sUpdWhere;

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}else if($aPost["u_mode"]=="proj_del"){

	$sProjId = getQS("projid");
	if($sProjId==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "Proj Id is not provide";
	}else{
		$query = "DELETE FROM p_project WHERE proj_id=?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sProjId);
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