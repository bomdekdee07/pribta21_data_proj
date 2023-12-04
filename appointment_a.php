<?
include_once("in_php_function.php");
include_once("in_setting_row.php");
$aRes=array();
include("array_post.php");

include("in_db_conn.php");
if($aPost["u_mode"]=="del_appointment"){
	$sClinicId = getQS("clinicid");
    $ap_date = getQS("ap_date");
    $uid = getQS("uid");
    $sid = getQS("sid");
	$app_time = getQS("app_time");

	if($ap_date == ""){
		$aRes["res"] = "0";
		$aRes["msg"] = "ไม่สามารถลบตารางนัดนี้ได้เนื่องจากไม่มีวันที่ส่งมา";
	}else{
		$query = "DELETE FROM i_appointment 
        WHERE clinic_id = ?
        and appointment_date = ?
        and uid = ?
        and s_id = ?
		and appointment_time = ?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss", $sClinicId, $ap_date, $uid, $sid, $app_time);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}

$stmt->close();
$mysqli->close();

$sTemp=json_encode($aRes);
echo($sTemp);
?>