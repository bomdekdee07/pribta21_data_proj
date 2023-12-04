<?
include("in_session.php");
include_once("in_php_function.php");
$sChoice=getQS("choice");



include("in_db_conn.php");

if($choice == "thumbnail"){

}
else if ($choice == "dropdown"){

}

	$query =" SELECT PS.s_id,s_name,PS.section_id,s_remark,clinic_id, s_name_en FROM p_staff PS
	JOIN i_staff_clinic ISC
	ON ISC.s_id = PS.s_id
	WHERE ISC.clinic_id = ? AND PS.s_email = ? AND PS.s_pwd = ? AND sc_status=1;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinic,$sEmail,$sPass);

	if($stmt->execute()){
	  $stmt->bind_result($s_id,$s_name,$section_id,$s_remark,$clinic_id,$s_name_en);
	  while ($stmt->fetch()) {
		$_SESSION["s_id"]=$s_id;
		$_SESSION["s_name"]=$s_name;
		$_SESSION["s_name_en"]=$s_name_en;
		//$_SESSION["section_id"]["clinic_id"]=$section_id;
		$_SESSION["clinic_id"]=$clinic_id;
		$_SESSION["s_email"]=$sEmail;
		$_SESSION["sesskey"]=j_enc($s_id);

		$sResult["res"] = "1";
	  }
	}

	$mysqli->close();





$returnData = json_encode($sResult);
echo($returnData);


?>
