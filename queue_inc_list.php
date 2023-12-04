<?
include("in_session.php");
include_once("in_php_function.php");
$sColDate = getQS("coldate");
$sClinic = getSS("clinic_id");
$isHideName =getQS("hidename");
$sViewMode = getQS("v_mode");
$sToday = date("Y-m-d");

//WARNING Since this is emergency Queue List to solve idiot Dol queue_list. It still using stupid and ugly database by Dol. So in the new system this file should be ignore or updated

$sClinic="IHRI";
if($sClinic==""){
	echo("Please login first");
	exit();
}

if($sColDate==""){
	$sColDate = date("Y-m-d");
}

$sRoomList = ""; 
if($sViewMode=="reception"){
  $sRoomList = "1,2,";
	//$sRoomList = "AND room_number IN ('1','2')";
  //Check if Form Done

}

include("in_db_conn.php");


$sFormList = "'DEMO_PRIBTA','BRA_ASSIST_PRIBTA'";
$aUidForm = array();
if($sColDate==$sToday){
  //Check if all form is done.
  $query = "SELECT uid,form_id,collect_time FROM p_data_form_done WHERE form_id IN ($sFormList) AND uid IN (SELECT uid FROM k_visit_data WHERE date_of_visit=? AND uid != '') AND collect_date = ? ORDER BY uid,collect_time";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ss",$sToday,$sToday);
  if($stmt->execute()){
    $stmt->bind_result($uid,$form_id,$collect_time );
    while ($stmt->fetch()) {
      $aUidForm[$uid] = (isset($aUidForm[$uid])?$aUidForm[$uid]:0) + 1;
    }
  }
}




$sHtml = "";
$query = "SELECT room_number,room_detail,queue_row_detail,PI.uid,uic,fname,sname,en_fname,en_sname,time_of_visit
FROM k_queue_row_detail KQRD

LEFT JOIN k_queue_row_detail_history KQRDH
ON KQRDH.from_qrd_id = KQRD.id
AND KQRDH.time_record > ?

LEFT JOIN patient_info PI
ON PI.uid = KQRD.patient_uid

LEFT JOIN k_room KR
ON KR.id = KQRDH.id_room

LEFT JOIN k_visit_data KVD
ON KVD.uid=KQRD.patient_uid
AND KVD.queue=KQRD.queue_row_detail
AND KVD.date_of_visit=?

WHERE KQRD.time_record > ?
AND KQRD.queue_row_id = (SELECT id FROM k_queue_row WHERE time_record > ?)
AND KQRDH.id IN (
SELECT MAX(id) FROM k_queue_row_detail_history 
WHERE time_record > ? AND from_qrd_id !='0'
GROUP BY from_qrd_id
ORDER BY time_record DESC
)

ORDER BY KQRD.queue_row_detail*1 DESC;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sssss",$sColDate,$sColDate,$sColDate,$sColDate,$sColDate);
/*
$query ="SELECT KVD.uid,time_of_visit,queue,fname,sname FROM k_visit_data KVD
LEFT JOIN patient_info PI 
ON PI.uid= KVD.uid
WHERE KVD.date_of_visit = ? AND site=?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sColDate,$sClinic);
*/
$sUidList = ""; 
if($stmt->execute()){
  $stmt->bind_result($room_number,$room_detail,$queue_row_detail,$uid,$uic,$fname,$sname,$en_fname,$en_sname,$time_of_visit);
  while ($stmt->fetch()) {
  	if($fname=="") $fname=$en_fname;
  	if($sname=="") $sname=$en_sname;
  	$fColor = "";
    if(strpos($sRoomList,$room_number.",") !== false || $sRoomList ==""){
      $sHtml.="<div class='";
    }else{
      $sHtml.="<div style='display:none' class='row-notin ";
    }
      $sColor = ""; $sFormDone = "";
      if(isset($aUidForm[$uid])){
          if($aUidForm[$uid]==2){
            $sFormDone = "<i class='fas fa-check'  title='Questionnaire Done'></i>";
          }
      }

      if($room_number=="28") $sColor = " style='background-color:green;color:white' ";
      $sHtml.=" q-row row-color fl-wrap-row h-s' data-coldate='".$sColDate."' data-coltime='".$time_of_visit."' data-queue='".$queue_row_detail."' data-roomno='$room_number' data-uid='".$uid."'>
          <div class='fabtn fl-fix w-ss fl-mid btn-q-no row-hover ' $sColor title='Forward Queue'>
            $queue_row_detail
          </div>


        <div class='fabtn fl-wrap-col fs-small row-hover btn-q-info' title='Edit Basic Patient Info'>
          <div class='fl-wrap-row lh-15 fw-b' style=''>
            $uid
          </div>
          <div class='fl-fill subj_name lh-15' style='overflow:hidden' >
            ".(($isHideName=="1")?"":$fname." ".$sname)."
          </div>
          <div class='fl-wrap-row lh-15' style=''>
            <div class='fl-fill fs-smaller'>[$room_number] $room_detail</div>
          </div>

        </div>
        <div class='fl-wrap-col w-40'>
          <div class='fl-fix h-30 fl-mid' style='color:green'>
            ".$sFormDone."
          </div>
          <div class='fabtn btnorderlab fl-fill fl-mid lh-20'>
            ".(($sColDate==$sToday && $room_number!=28)?"LAB":"")."
          </div>
        </div>

      </div>
      ";

      if($uid!=""){
        $sUidList .= (($sUidList=="")?"":",")."'".$uid."'";
      }
  }
}


$stmt->close();
$mysqli->close();

foreach ($aUidForm as $sUid => $iCount) {
  
}


?>
<? echo($sHtml); ?>

<script>
	$(function(){
		//$(".tblquelist tbody tr:odd").css("background-color","silver");
		//$(".tblquelist tbody tr:even").css("background-color","lightgrey");

	});

</script>