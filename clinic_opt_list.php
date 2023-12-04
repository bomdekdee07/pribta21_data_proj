<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sEmail = urldecode(getQS("email"));
$sMode =getQS("u_mode");
$sOpt = getQS("opt");
include("in_db_conn.php");
$sList = "";


if($sMode=="clinic-list"){
	$query ="SELECT clinic_id,clinic_name,clinic_address,clinic_email,clinic_tel,clinic_status,main_clinic_id,old_clinic_id,clinic_pid FROM p_clinic ORDER BY clinic_name";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$clinic_name,$clinic_address,$clinic_email,$clinic_tel,$clinic_status,$main_clinic_id,$old_clinic_id ,$clinic_pid); 
	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sList.="<option value='".$clinic_id."'>".$clinic_name."</option>";
	  	}else{
	  		$sList.=getClinicList($clinic_id,$clinic_name,$clinic_address,$clinic_email,$clinic_tel,$clinic_status,$main_clinic_id,$old_clinic_id,$clinic_pid);
	  	}
	  	
	  }
	}

}else if($sMode=="clinic-list-only"){
	$sClinicId=getQS("clinicid");
	$query ="SELECT clinic_id,clinic_name,clinic_address,clinic_email,clinic_tel,clinic_status,main_clinic_id,old_clinic_id FROM p_clinic WHERE clinic_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sClinicId);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$clinic_name,$clinic_address,$clinic_email,$clinic_tel,$clinic_status,$main_clinic_id,$old_clinic_id ); 
	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sList.="<option value='".$clinic_id."'>".$clinic_name."</option>";
	  	}else{
	  		$sList.=getClinicList($clinic_id,$clinic_name,$clinic_address,$clinic_email,$clinic_tel,$clinic_status,$main_clinic_id,$old_clinic_id);
	  	}
	  	
	  }
	}

}else{
	$query ="SELECT DISTINCT(ISC.clinic_id),
		PC.clinic_name,
		PS.section_id
	FROM p_clinic PC
	LEFT JOIN i_staff_clinic ISC
	ON ISC.clinic_id = PC.clinic_id
	LEFT JOIN p_staff PS
	ON PS.s_id = ISC.s_id
	WHERE PS.s_email=? AND ISC.sc_status=1";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sEmail);

	if($stmt->execute()){
		$stmt->bind_result($clinic_id, $clinic_name, $section_id);
		while ($stmt->fetch()) {
			if($section_id != ""){
				if($section_id == $clinic_id){
					$sList.="<option value='".$clinic_id."' selected>".$clinic_name."</option>";
				}
				else{
					$sList.="<option value='".$clinic_id."'>".$clinic_name."</option>";
				}
			}
			else{
				$sList.="<option value='".$clinic_id."'>".$clinic_name."</option>";
			}
		}
	}
}

$mysqli->close();
echo($sList);	
?>


