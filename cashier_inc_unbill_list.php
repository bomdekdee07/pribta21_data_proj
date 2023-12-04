<?
include_once("in_session.php");
include_once("in_php_function.php");
$sClinicId=getSS("clinic_id");
$sColD=getQS("coldate");
$sBillId=getQS("billid");

include("in_db_conn.php");
//List Q whose bill is not added yet. ** ALL queue_type **TODAY ONLY you can add bill from different days
$aUid=array(); $aUidA=array(); $aBillUid=array();


$query="SELECT bill_id,IQL.uid,fname,sname,IQL.collect_date,IQL.collect_time,queue FROM i_queue_list IQL
LEFT JOIN i_bill_detail IBD
ON IBD.bill_q = IQL.queue
AND IBD.bill_date=IQL.collect_date
AND IBD.clinic_id=IQL.clinic_id
LEFT JOIN patient_info PI
ON PI.uid = IQL.uid

WHERE IQL.clinic_id=? AND IQL.uid !='' AND IQL.collect_date=? ORDER BY collect_date,collect_time";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sClinicId,$sColD);
if($stmt->execute()){
	$stmt->bind_result($bill_id,$uid,$fname,$sname,$collect_date,$collect_time,$queue);
	while ($stmt->fetch()) {
		if($bill_id=="" || is_null($bill_id)){
			$sFirst=substr($queue, 0,1);
			if($sFirst*1 > 0){
				$aUid[$queue]["bill_id"] = $bill_id;
				$aUid[$queue]["uid"] = $uid;
				$aUid[$queue]["fname"] = $fname;
				$aUid[$queue]["sname"] = $sname;
				$aUid[$queue]["coldate"] = $collect_date;
				$aUid[$queue]["coltime"] = $collect_time;
				$aUid[$queue]["q"] = $queue;
			}else{
				$iQ = preg_replace('/[A-Z]/', "", $queue);
				$iQ=$iQ*1;
				$aUidA[$sFirst.$iQ]["bill_id"] = $bill_id;
				$aUidA[$sFirst.$iQ]["uid"] = $uid;
				$aUidA[$sFirst.$iQ]["fname"] = $fname;
				$aUidA[$sFirst.$iQ]["sname"] = $sname;
				$aUidA[$sFirst.$iQ]["coldate"] = $collect_date;
				$aUidA[$sFirst.$iQ]["coltime"] = $collect_time;
				$aUidA[$sFirst.$iQ]["q"] = $queue;
			}

		}else if($bill_id!="" && $bill_id==$sBillId){
			$aBillUid[$uid] = $fname;
		}
	}	
}

// echo print_r($aUid);
// echo print_r($aUidA);


//List all uid in the bill
$mysqli->close();
$sUidList=""; 
ksort($aUidA);
$aUid = array_merge($aUid,$aUidA);

foreach ($aUid as $queue => $aInfo) {
	$sUid=$aInfo["uid"];
	$sTemp="
	<div class='fl-wrap-row data-row h-50 row-color fabtn btn-uid fs-smaller'  data-uid='".$sUid."' data-coldate='".$aInfo["coldate"]."' data-coltime='".$aInfo["coltime"]."' data-q='".$aInfo["q"]."'>
		<div class='fl-fix w-50 fl-mid fw-b f-border' style='".(isset($aBillUid[$sUid])?"background-color:lime":"")."'>".$aInfo["q"]."</div>
		<div class='fl-wrap-col '>
			<div class='fl-fix h-20 fl-mid fw-b f-border-b'>$sUid</div>
			<div class='fl-fix h-30 fl-mid lh-15 fname'>".$aInfo["fname"]." ".$aInfo["sname"]."</div>
		</div>
	</div>
	";
	$sUidList.=$sTemp;
}

echo($sUidList);
?>