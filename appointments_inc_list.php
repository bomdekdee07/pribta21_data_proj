<?
//Done by Jeng
//List all of visit
include_once("in_session.php");
include_once("in_php_function.php");
$sDate = getQS("date");
$sToday = date("Y-m-d");
$sClinic = getSS("clinic_id");
$showAll = getQS("showall");
$sSid = getSS("s_id");

if($sClinic==""){
	echo("Please login");
	exit();
}
if($sDate=="") $sDate = $sToday;

include("in_db_conn.php");
$query="SELECT IA.uid,IA.s_id,appointment_time,is_confirm,fname,sname,en_fname,en_sname,clinic_type,s_name,IQL.uid FROM i_appointment IA
LEFT JOIN patient_info PI
ON PI.uid=IA.uid
LEFT JOIN p_staff PS
ON PS.s_id = IA.s_id
LEFT JOIN i_queue_list IQL
ON IQL.uid=IA.uid
AND IQL.collect_date = IA.appointment_date
AND IQL.clinic_id = IA.clinic_id
WHERE IA.appointment_date=? AND IA.clinic_id=? AND is_confirm<2";
if($showAll=="1"){

}else{
	$query.=" AND IA.s_id = '$sSid'";
}

$sHtml="";
$stmt=$mysqli->prepare($query);
$stmt->bind_param("ss",$sToday,$sClinic);
if($stmt->execute()){
	$stmt->bind_result($uid,$s_id,$appointment_time,$is_confirm,$fname,$sname,$en_fname,$en_sname,$clinic_type,$s_name,$q_uid);
	while($stmt->fetch()){
		$sName = $fname." ".$sname;
		if($sName=="") $sName=$en_fname." ".$en_sname;
		if($q_uid==$uid){

		}else{
			$sHtml.="<div class='fl-wrap-row data-row h-30 fs-xsmall lh-15 row-hover row-color q-row' data-uid='$uid' data-clinicid='$sClinic' data-date='$sToday' data-time='$appointment_time' data-sid='$s_id' data-queue='' >
			<div class='fl-fix w-30 fl-mid fabtn btn-is-confirm hideme'>
				$is_confirm
			</div>
			<div class='fl-fix w-10'></div>
			<div class='fl-wrap-row'>
				<div class='fl-wrap-col fabtn w-80 btn-q-info'>
					<div class='fl-fill fw-b'>
						$uid
					</div>
					<div class='fl-fill'>
						$appointment_time
					</div>
				</div>
				<div class='fl-wrap-col '>
					<div class='fl-fill fw-b'>
						$sName
					</div>
					<div class='fl-fill'>
						by $s_name
					</div>
				</div>
				<div class='fl-fix w-30 fabtn fl-mid btn-made-appoint'>
					<i class='fa fa-calendar-alt fa-lg'></i>
				</div>
			</div>
			
			</div>";
		}
		
	}
}

$mysqli->close();
echo($sHtml);
?>