<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sUid = getQS("uid");
$aLab=array();
$sColD=getQS("coldate");
$sColT=getQS("coltime");


if($sUid==""){

}else{
	include("in_db_conn.php");
	$query="SELECT PLO.collect_date,PLO.collect_time,PLOLT.lab_id,lab_name,PLR.lab_result ,lab_unit
	FROM p_lab_order PLO
	LEFT JOIN p_lab_order_lab_test PLOLT
	ON PLOLT.uid=PLO.uid
	AND PLOLT.collect_date=PLO.collect_date
	AND PLOLT.collect_time=PLO.collect_time

	LEFT JOIN p_lab_result PLR
	ON PLR.uid=PLOLT.uid
	AND PLR.collect_date=PLOLT.collect_date
	AND PLR.collect_time=PLOLT.collect_time
	AND PLR.lab_id=PLOLT.lab_id
	LEFT JOIN p_lab_test PLT
	ON PLT.lab_id=PLOLT.lab_id

	WHERE PLO.uid=? AND lab_order_status NOT IN ('C','CC') AND  PLO.collect_date=? AND PLO.collect_time=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($collect_date,$collect_time,$lab_id,$lab_name,$lab_result,$lab_unit);
		while($stmt->fetch()){
			$aLab[$collect_date." ".$collect_time][]="<div class='fl-wrap-row h-25 fs-small row-hover row-color'>
				<div class='fl-wrap-col'>
					<div class='fl-fill h-15 al-left'>$lab_name</div>
					<div class='fl-fill h-10 al-left fs-xsmall lh-10'>$lab_id</div>
				</div>
				<div class='fl-fix w-80'>$lab_result</div>
				<div class='fl-fix w-80'>$lab_unit</div>
			</div>";
		}
	}
	$mysqli->close();
	ksort($aLab);
	$sTemp="";
	foreach ($aLab as $sDate => $aRow) {
		$aDate = explode(" ",$sDate);
		if(isset($aLab[$sDate])){
			//$sTemp.="<div class='fl-fix h-20 al-left bg-head-4'> LAB </div>";
			foreach ($aLab[$sDate] as $i => $sRow) {
				$sTemp.=$sRow;
			}
		}
			
		
		
		$sTemp.="<div class='fl-fix h-20'></div>";
	}
	if($sTemp=="") $sTemp="<div class='fl-fill fl-mid'>No lab order</div>";
	echo($sTemp);
}
?>