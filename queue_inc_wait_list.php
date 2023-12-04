<?
include("in_session.php");
include_once("in_php_function.php");

$sClinic=getSS("clinic_id");
$sRoom=getSS("room_no");
$sToday = date("Y-m-d");
$isFull=getQS("full");


include("in_db_conn.php");

$query="SELECT queue,collect_time,IQL.uid,s_id,fname,sname,IQL.room_no,room_icon,queue_status FROM i_queue_list IQL
LEFT JOIN patient_info PI
ON PI.uid=IQL.uid
LEFT JOIN i_room_list IRL
ON IRL.room_no = IQL.room_no
AND IRL.clinic_id = IQL.clinic_id

WHERE IQL.clinic_id=? AND collect_date=? ";

if($isFull!="1") $query.=" AND IQL.room_no=? AND queue!='' AND queue NOT IN (SELECT queue FROM i_queue_list WHERE clinic_id=? AND room_no=? AND collect_date=? AND queue_status='2') ORDER BY collect_time";
//error_log($sClinic.":".$sRoom.":".$sToday);

$sHtml="";
$stmt = $mysqli->prepare($query);
if($isFull!="1") $stmt->bind_param("ssssss",$sClinic,$sToday,$sRoom,$sClinic,$sRoom,$sToday);
else $stmt->bind_param("ss",$sClinic,$sToday);
$dtNow = new DateTime();
$sHtml = "";

//error_log($query);
if($stmt->execute()){
  $stmt->bind_result($queue,$collect_time,$uid,$s_id,$fname,$sname,$room_no,$room_icon,$queue_status);
  while ($stmt->fetch()) {
  	//error_log($queue);
	$sHtml .= "<div class='q-queue fl-wrap-row fl-mid fs-s row-color row-hover' style='min-height:35px'>";

	if($room_no==$sRoom && $queue_status != 2) $sHtml .= "<div class='btncallq fabtn fl-wrap-col w-ss fl-mid' title='Call the patient into the room' data-uid='$uid' data-queue='$queue' data-coldate='$sToday' data-coltime='$collect_time'>
			<div class='fl-fill h-xs'>$queue</div>
			<div class='fl-fill' style='color:orange'><i class='far fa-bell fa-lg' ></i></div>
		</div>";
	else{
		$sHtml .= "<div class='fl-wrap-col w-ss fl-mid' title='View Only' data-uid='$uid' data-queue='$queue' data-coldate='$sToday' data-coltime='$collect_time'>
				<div class='fl-fill h-xs'>$queue</div>
				<div class='fl-fill' style='color:green'>".(($room_icon=="")?"":"<i class='$room_icon'></i>")."</div>
		</div>";
	}

	$sHtml .= "
		<div class='fl-wrap-col' >
			<div class='fl-fix fs-xs h-xs'><span style='color:red;margin-right:5px;font-weight:bold'>".(($uid=="")?"No UID":$uid)."</span>".$collect_time."
			</div>
			<div class='fl-fill fs-xs'>
				$fname $sname
			</div>
		</div>
		<div class='fl-fix w-ss fabtn btnviewq' title='View Only' data-uid='$uid' data-queue='$queue' data-coldate='$sToday' data-coltime='$collect_time' style='color:black'>
			<i class='fas fa-eye fa-2x'  ></i>
		</div>
	</div>";

  }
}
$mysqli->close();



echo($sHtml);
?>
