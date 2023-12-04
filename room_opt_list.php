<?
include("in_session.php");
include_once("in_php_function.php");
$sClinic = getSS("clinic_id");
$sRoom = getSS("room_no");
$sSID = getSS("s_id");
$sSessKey = getSS("sesskey");

if($sClinic==""){

}else{
	include("in_db_conn.php");
	$sOpt = "<option value=''>(---select room---)</option>";

	$query ="SELECT room_number,room_detail,room_who,s_name,time_record FROM k_room  KR

	LEFT JOIN p_staff PS
	ON PS.s_id = KR.room_who 

	WHERE KR.section_id IN (SELECT section_id FROM i_staff_clinic
		WHERE s_id=? AND clinic_id = ?) 
	AND clinic_id = ?";


	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sSID,$sClinic,$sClinic);

	if($stmt->execute()){
	  $stmt->bind_result($room_number,$room_detail,$room_who,$s_name,$time_record );
	  while ($stmt->fetch()) {
	  	$sOpt.="<option value='".$room_number."' data-sid='".$room_who."' ".(($sRoom==$room_number)?"selected":"")." >".$room_number." ".$room_detail.(($s_name=="")?"":" | ".$s_name." since ".$time_record."")."</option>";
	  }
	}
	$mysqli->close();

	echo($sOpt);	
}


?>


