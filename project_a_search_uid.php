<?
/* Project Thumbnail list  */

include("in_session.php");
include_once("in_php_function.php");
$sToday=date("Y-m-d");
$sProjid = getQS("projid","POC");
$sGropuid = getQS("groupid");
$sMode=getQS("u_mode");
$sDateTo = getQS("date_to",$sToday);
$sDateFrom = getQS("date_from",$sToday);
$rtn=array();

include("in_db_conn.php");


$arr_data_list = array();
$query ="";
if($sGropuid ==""){
	$query ="SELECT PUV.uid, PUL.pid, P.uic, PUV.group_id, PUL.clinic_id,
	PUV.schedule_date, PUV.visit_date
	FROM p_project_uid_visit PUV
	LEFT JOIN p_project_uid_list PUL
	ON (PUV.proj_id = BINARY PUL.proj_id AND PUV.group_id=BINARY PUL.proj_group_id AND
		PUV.uid = (binary PUL.uid ) AND PUL.uid_status IN (1,2))
	LEFT JOIN patient_info P ON (binary PUV.uid=P.uid )
	WHERE PUV.proj_id='POC' AND PUV.schedule_date >=? AND PUV.schedule_date <=?
	ORDER BY PUV.schedule_date
	";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sDateFrom, $sDateTo );
}
else{
	$query ="SELECT PUV.uid, PUL.pid, P.uic, PUV.group_id, PUL.clinic_id,
	PUV.schedule_date, PUV.visit_date
	FROM p_project_uid_visit PUV
	LEFT JOIN p_project_uid_list PUL
	ON (PUV.proj_id = PUL.proj_id AND PUV.group_id=PUL.proj_group_id AND
		PUV.uid = PUL.uid AND PUL.uid_status IN (1,2))
	LEFT JOIN patient_info P ON (binary PUV.uid=P.uid )
	WHERE PUV.proj_id=? AND PUL.group_id=?  AND PUV.schedule_date >=? AND PUV.schedule_date <=?
	ORDER BY PUV.schedule_date
	";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sProjid,$sGropuid,$sDateFrom, $sDateTo );
}

$sHtml=""; $sRow="";
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()) {
			$sHtml.="<div class='fl-wrap-row row-color fl-mid row-hover' style='max-height:30px;min-height:30px'>
			<div class='fl-fix div-p-id '>".$row["uid"]."</div>
               <div class='fl-fix div-p-id '>".$row["uid"]."</div>
               <div class='fl-fix div-p-id '>".$row["uid"]."</div>
               <div class='fl-fill'>".$row["uid"]."</div>
               <div class='fl-fix div-p-date '>".$row["uid"]."</div>
               <div class='fl-fix div-p-date '>".$row["uid"]."</div>
              </div>";
		}
	}

	$mysqli->close();

  $rtn["res"]="1";

if($sMode==""){
	//Default
	echo($sHtml);
}else{
  $rtn["res"]="1";
  $rtn["datalist"] = $sHtml;
 $returnData = json_encode($rtn);
 echo $returnData;
 
}


?>
