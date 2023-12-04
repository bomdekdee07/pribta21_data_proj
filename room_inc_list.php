<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sClinicId = getQS("clinicid");
if($sClinicId=="") $sClinicId=getSS("clinic_id");

$sMode =getQS("u_mode");
$sOpt = getQS("opt");
$sCurRoom = getQS("room_no");
$sToday = date("Y-m-d");
include("in_db_conn.php");
$sList="";

if($sMode=="room-list"){
	$query ="SELECT IRL.clinic_id,IRL.room_no,room_name,room_detail,room_status,section_id,default_room,room_icon FROM i_room_list IRL 
	WHERE IRL.clinic_id=? ORDER BY IRL.room_no*1";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sClinicId);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$room_no,$room_name,$room_detail,$room_status,$section_id,$default_room,$room_icon ); 
	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sList.="<option value='".$room_no."' ".(($sCurRoom==$room_no)?" selected":"")
.">".$room_no." ".$room_detail."</option>";
	  	}else{
	  		$sList.=getRoomRow($clinic_id,$room_no,$room_name,$room_detail,$room_status,$section_id,$default_room,$room_icon );
	  	}
	  	
	  }
	}

}else if($sMode=="room-forward"){
	$aTotalQ = array();

	$query ="SELECT room_no,count(queue) FROM i_queue_list WHERE clinic_id=? AND collect_date = ? GROUP BY room_no";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sClinicId,$sToday);
	if($stmt->execute()){
	  $stmt->bind_result($room_no,$total_queue); 
	  while ($stmt->fetch()) {
	  	$aTotalQ[$room_no] = $total_queue;
	  }
	}

	$query ="SELECT IRL.room_no,room_detail,s_name,IRLIST.s_id,room_icon FROM i_room_list IRL 
	LEFT JOIN i_room_login IRLIST 
	ON IRLIST.room_no=IRL.room_no 
	AND IRLIST.clinic_id=IRL.clinic_id 
	AND IRLIST.visit_date=? 
	 AND IRLIST.room_status='1'
	LEFT JOIN p_staff PS 
	ON PS.s_id=IRLIST.s_id 
	WHERE IRL.room_status='1' AND IRL.clinic_id=? ORDER BY IRL.room_no*1";


	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sToday,$sClinicId);
	if($stmt->execute()){
	  $stmt->bind_result($room_no,$room_detail,$s_name,$s_id,$room_icon ); 
	  while ($stmt->fetch()) {
	  	$sList.=getRoomFwd($sClinicId,$room_no,$room_detail,$s_name,$s_id,(isset($aTotalQ[$room_no])?$aTotalQ[$room_no]:0),$room_icon );
	  }
	}
}
$mysqli->close();
echo($sList);	
?>


