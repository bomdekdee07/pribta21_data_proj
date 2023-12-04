<?
include_once("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
$sKey=(urldecode(urldecode(getQS("txt"))));
$sSid=getSS("s_id");
$query =" SELECT PI.uid,uic,proj_pid,proj_visit,fname,sname,en_fname,en_sname,lab_order_id,PLO.collect_date,PLO.collect_time,PLOLT.lab_id,lab_result_report FROM patient_info PI
LEFT JOIN p_lab_order PLO
ON PLO.uid=PI.uid
LEFT JOIN p_lab_order_lab_test PLOLT
ON PLOLT.uid = PLO.uid
AND PLOLT.collect_date = PLO.collect_date
AND PLOLT.collect_time = PLO.collect_time

LEFT JOIN p_lab_result PLR
ON PLR.uid = PLOLT.uid
AND PLR.collect_date = PLOLT.collect_date
AND PLR.collect_time = PLOLT.collect_time
AND PLR.lab_id=PLOLT.lab_id";


if(strpos($sKey, "-")>0) $query.=" WHERE PI.uid=?";	
else if(mb_strlen($sKey)==8 && preg_match('/([0-9]{6})/', $sKey)) {
   $query .=" WHERE PI.uic=?";
}else {
	$query.=" WHERE proj_pid=?";	

}



$query .= " AND PLO.lab_order_status != 'C' AND PLOLT.lab_id='HCV_VL'";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sKey);
$sHtml="";
if($stmt->execute()){
  $stmt->bind_result($uid,$uic,$proj_pid,$proj_visit,$fname,$sname,$en_fname,$en_sname,$lab_order_id,$collect_date,$collect_time,$lab_id,$lab_result_report);
  while ($stmt->fetch()) {
	if($uid != "") {
		$sHtml.="<div class='fl-wrap-row lab-row h-30 lh-30 row-color row-hover' data-orderid='$lab_order_id' data-sid='$sSid' data-coldate='$collect_date' data-coltime='$collect_time' data-uid='$uid'> 
			<div class='fl-fix w-80'>$uid</div>
			<div class='fl-fix w-80'>$uic</div>
			<div class='fl-fix w-80'>$proj_pid</div>
			<div class='fl-fix w-80'>$proj_visit</div>

			<div class='fl-fill'>".$fname." ".$sname." ".$en_fname." ".$en_sname."</div>
			<div class='fl-fix w-150'>$lab_order_id</div>
			<div class='fl-fix w-150'>".$collect_date." ".$collect_time."</div>
			<div class='fl-fill'>".$lab_id." : ".$lab_result_report."</div>
			<div class='fl-fix w-100 fabtn btnviewpdf fl-mid fw-b' style='color:red'><i class='far fa-file-pdf'> Report </i></div>

		</div>";
	}
  }
}
$mysqli->close();
echo($sHtml);
?>