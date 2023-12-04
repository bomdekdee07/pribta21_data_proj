<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$aRes=array("res"=>"0","msg"=>"");
$sClinicId=getSS("clinic_id");
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sQ=getQS("q");
$sSid=getSS("s_id");
$request_by = getQS("request_by");
$method = getQS("method");
$receive_amt = getQS("receive_amt");
$paid_amt = getQS("paid_amt");
$paid_datetime = getQS("paid_datetime");
$bUpdateLog=false;

include("array_post.php");

$aBillLog=array("s_id"=>$sSid,"event_name"=>$aPost["u_mode"],"bill_id"=>"","clinic_id"=>$sClinicId,"uid"=>"","total_amt"=>"");

if($sUid=="" && $sQ!=""){
	//getUID from the Q
}else if($sQ=="" && $sUid!="" && $sColD!="" && $sColT!="" ){
	//get Q From UID
}

include("in_db_conn.php");
if($aPost["u_mode"]=="cashier_call"){

	$query = "UPDATE i_queue_list SET queue_call='2',queue_date=NOW(),s_id=? WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=? AND queue_type='1'";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,$sClinicId,$sUid);
	if($stmt->execute()){
		$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;		
			$aRes["msg"] = getClinicList($aPost["clinic_id"],$aPost["clinic_name"],$aPost["clinic_address"],$aPost["clinic_email"],$aPost["clinic_tel"],$aPost["clinic_status"],$aPost["main_clinic_id"],$aPost["old_clinic_id"]);
		}
	}
}else if($aPost["u_mode"]=="clinic_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE p_clinic SET ".$sUpdSet." WHERE ".$sUpdWhere;
		
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}else if($aPost["u_mode"]=="clinic_del"){

	$sClinicId = getQS("clinicid");
	if($sClinicId==""){
		$aRes["res"] = "0";
		$aRes["msg"] = "Clinic Id is not provide";
	}else{
		$query = "DELETE FROM p_clinic WHERE clinic_id=?";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sClinicId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}

}else if($aPost["u_mode"]=="bill_create"){
	$sY = date("Y");
	if($sY < 2400) $sY = ($sY*1) + 543;
	$sToday=date("Y-m-d");
	$sBill="";

	$query = "INSERT INTO i_bill_list(bill_id,created_date,created_by,clinic_id) 
		SELECT @bill := CONCAT('".$sY."/',IFNULL(MAX(SUBSTRING(bill_id,6,10)*1),0)+1),NOW(),?,?
	 FROM i_bill_list WHERE clinic_id=? AND bill_id LIKE '".$sY."/%'";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sSid,$sClinicId,$sClinicId);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$query2 = "SELECT @bill";
			$stmt2 = $mysqli->prepare($query2);
			if($stmt2->execute()){
				$stmt2->bind_result($bill);
				while ($stmt2->fetch()) {
					$sBill = $bill;
					$bUpdateLog=true;
					$aBillLog["bill_id"] = $bill;
				}
			}
		}
	}

	if($sBill!=""){
		$aBillLog["uid"] = $sUid;
		if($sToday!=$sColD && $sColD!='' && $sUid !='' && $sColT !=''){
			$query="INSERT INTO i_bill_detail(clinic_id,bill_id,bill_date,bill_q,bill_q_type,added_by,added_datetime)
			SELECT clinic_id,?,collect_date,queue,queue_type,?,NOW() FROM i_queue_list 
			WHERE clinic_id=? AND collect_date=? AND collect_time=? AND uid=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ssssss",$sBill,$sSid,$sClinicId,$sColD,$sColT,$sUid);

		}else if($sQ!=""){
			$query="INSERT INTO i_bill_detail(clinic_id,bill_id,bill_date,bill_q,bill_q_type,added_by,added_datetime)
			SELECT ?,?,?,queue,queue_type,?,NOW() FROM i_queue_list 
			WHERE clinic_id=? AND queue=? AND collect_date=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssssss",$sClinicId,$sBill,$sToday,$sSid,$sClinicId,$sQ,$sToday);
		}else{

		}
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0){
				$aRes["res"]=1;
				$aRes["msg"]=$sBill;
			}
		}

		$query="UPDATE i_queue_list SET queue_call='1' WHERE clinic_id=? AND queue =? AND collect_date=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sClinicId,$sQ,$sToday);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0){
				$aRes["res"]=1;
				$aRes["msg"]=$sBill;
			}
		}


	}
}else if($aPost["u_mode"]=="bill_add_uid"){
	$sBillId = getQS("billid");
	$sColD="";
	$query="SELECT bill_date FROM i_bill_detail WHERE bill_id=? AND clinic_id=? LIMIT 1";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sBillId,$sClinicId);
	if($stmt->execute()){
		$stmt->bind_result($bill_date);
		while($stmt->fetch()){
			$sColD=$bill_date;
		}
	}

	$query="INSERT INTO i_bill_detail(clinic_id,bill_id,bill_date,bill_q,bill_q_type,added_by,added_datetime)
	SELECT clinic_id,?,collect_date,queue,queue_type,?,NOW() FROM i_queue_list
	WHERE clinic_id=? AND uid=? AND queue=? AND collect_date=?;	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssssss",$sBillId,$sSid,$sClinicId,$sUid,$sQ,$sColD);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"]="1";
			$query="UPDATE i_bill_list SET receive_amt =0,receive_by='',paid_amt=0,paid_datetime='0000-00-00 00:00:00' WHERE clinic_id=? AND bill_id=?;	";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss",$sClinicId,$sBillId);
			if($stmt->execute()){

			}


		}else{
			//Not Success
			$aRes["msg"] = "Error : ".$stmt->error;
		}
	}else{
		//For Insert Duplicate จะ Error ตรงนี้ ดู $stmt->error สำหรับ ข้อความ error ได้
		$aRes["msg"] = "This subject already included in the bill or other bills.";
	}
}else if($aPost["u_mode"]=="bill_del_uid"){
	$sBillId = getQS("billid");
	$sQ=getQS("q");
	$sColD=getQS("coldate");
	$sUid=getQS("uid");

	$query="DELETE FROM i_bill_detail
	WHERE clinic_id=? AND bill_id=? AND bill_date=? AND bill_q=?  	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sClinicId,$sBillId,$sColD,$sQ);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"]="1";
		}else{
			//Not Success
			$aRes["msg"] = "Error : ".$stmt->error;
		}
	}else{
		//For Insert Duplicate จะ Error ตรงนี้ ดู $stmt->error สำหรับ ข้อความ error ได้
		$aRes["msg"] = "This subject already included in the bill or other bills.";
	}
}else if($aPost["u_mode"]=="bill_paid"){
	$sBillId=getQS("billid");
	if($sBillId!=""){
		$sNote=getQS("note","0");
		$sRecAmt=getQS("recamt");
		$sRecMethod=getQS("recmethod");
		$aUidList=array();
		$iTotal=0;
		$isStock=false; $isLab=true;

		$aQ[0]="SELECT queue,IQL.uid,IQL.collect_date,IQL.collect_time,total_price FROM i_bill_detail IBD LEFT JOIN i_queue_list IQL ON IQL.clinic_id=IBD.clinic_id AND IQL.collect_date=IBD.bill_date AND IQL.queue=IBD.bill_q AND IQL.queue_type=IBD.bill_q_type     
			LEFT JOIN i_stock_order ISO
		    ON ISO.clinic_id=IQL.clinic_id
		    AND ISO.uid=IQL.uid
		    AND ISO.collect_date=IQL.collect_date
		    AND ISO.collect_time=IQL.collect_time
		    AND total_price != 0
   			WHERE IBD.clinic_id=? AND IBD.bill_id=?;";

		//GET LABLIST
		$aQ[1]="SELECT queue,IQL.uid,IQL.collect_date,IQL.collect_time,sale_price FROM i_bill_detail IBD LEFT JOIN i_queue_list IQL ON IQL.clinic_id=IBD.clinic_id AND IQL.collect_date=IBD.bill_date AND IQL.queue=IBD.bill_q AND IQL.queue_type=IBD.bill_q_type     
			LEFT JOIN p_lab_order_lab_test PLOLT
		    ON PLOLT.uid=IQL.uid AND PLOLT.collect_date=IQL.collect_date 
		    AND PLOLT.collect_time=IQL.collect_time
		    AND sale_price != 0
   			WHERE IBD.clinic_id=? AND IBD.bill_id=?;";

   		foreach ($aQ as $iIndex => $query) {
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss",$sClinicId,$sBillId);
			if($stmt->execute()){
				$stmt->bind_result($queue,$uid,$collect_date,$collect_time,$total_price);
				while($stmt->fetch()){
					$aUidList[$uid]["q"]=$queue;
					$aUidList[$uid]["cold"]=$collect_date;
					$aUidList[$uid]["colt"]=$collect_time;
					$aUidList[$uid]["irow"]= (isset($aUidList[$uid]["irow"])?$aUidList[$uid]["irow"]:0)+ 1;
					$iTotal+=$total_price*1;
					if($iIndex==0){
						$aUidList[$uid]["status"]=0;
						$isStock=true;
					}else{
						$aUidList[$uid]["labstatus"]=0;
						$isLab=true;
					}

				}
			}
   		}

		if(count($aUidList) > 0){
			foreach ($aUidList as $uid => $aVisit) {
				if(isset($aVisit["status"])){
					$query="UPDATE i_stock_order SET is_paid=1 , paid_datetime=NOW() WHERE uid=? AND collect_date=? AND collect_time=?;";
					$stmt = $mysqli->prepare($query);
					$stmt->bind_param("sss",$uid,$aVisit["cold"],$aVisit["colt"]);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0){
							$aUidList[$uid]["status"]="1";
						}
					}

				}

				//Update Lab
				if(isset($aVisit["labstatus"])){
					$query="UPDATE p_lab_order_lab_test SET is_paid=1 , paid_datetime=NOW() WHERE uid=? AND collect_date=? AND collect_time=? AND is_paid!=1;";
					$stmt = $mysqli->prepare($query);
					$stmt->bind_param("sss",$uid,$aVisit["cold"],$aVisit["colt"]);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0){
							$aUidList[$uid]["labstatus"]="1";
						}
					}
				}
			}//Foreach
			$query="UPDATE i_bill_list SET paid_method=?, receive_amt=?,receive_by=?, paid_amt=? , paid_datetime=NOW(),bill_note=? WHERE bill_id=? AND clinic_id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssssss",$sRecMethod,$sRecAmt,$sSid,$iTotal,$sNote,$sBillId,$sClinicId);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0){
					$aBillLog["bill_id"]=$sBillId;
					$aBillLog["total_amt"]=$iTotal;
					$bUpdateLog=true;

				}
			}


			$aRes["res"]="1";
		}

	}
}else if($aPost["u_mode"]=="bill_paid_cancel"){
	$sBillId=getQS("billid");
	if($sBillId!=""){
		//Cancel Order Payment
		$query = "UPDATE i_stock_order  SET is_paid=0 , paid_datetime='0000-00-00'
		WHERE CONCAT(uid,collect_date,collect_time) IN (SELECT CONCAT(uid,collect_date,collect_time) FROM i_bill_detail IBD
			LEFT JOIN i_queue_list IQL ON IQL.clinic_id=IBD.clinic_id AND IQL.collect_date=IBD.bill_date AND IQL.queue=IBD.bill_q AND IQL.queue_type=IBD.bill_q_type     
			WHERE IBD.clinic_id=? AND IBD.bill_id=?	) ;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sClinicId,$sBillId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {

			}
		}
		$stmt->close();

		
		//Cancel Lab Payment
		$query = "UPDATE p_lab_order_lab_test SET is_paid=0 , paid_datetime='0000-00-00' WHERE CONCAT(uid,collect_date,collect_time) IN (SELECT CONCAT(uid,collect_date,collect_time) FROM i_bill_detail IBD
			LEFT JOIN i_queue_list IQL ON IQL.clinic_id=IBD.clinic_id AND IQL.collect_date=IBD.bill_date AND IQL.queue=IBD.bill_q AND IQL.queue_type=IBD.bill_q_type     
			WHERE IBD.clinic_id=? AND IBD.bill_id=?	);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sClinicId,$sBillId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {

			}
		}		
		$stmt->close();

		// INSERT Cancel bill cashiar
		if($sQ == "" || $sQ == "undefined"){
			$bind_param = "sss";
			$array_val = array($sUid, $sColD, $sColT);
			$queue_query = "";
			$prepare_drug_by = "";
			$check_drug_by = "";

			$query = "SELECT queue,
				prepare_drug_by,
				check_drug_by
			from i_queue_list 
			where uid = ?
			and collect_date = ?
			and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);

			if($stmt->execute()){
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc()){
					$queue_query = $row["queue"];
					$prepare_drug_by = $row["prepare_drug_by"];
					$check_drug_by = $row["check_drug_by"];
				}
			}
			$stmt->close();
			$sQ = $queue_query;
		}
		$year = date("Y");
		$month = date("m");
		$day = date("d");
		$hours = date("H");
		$munite = date("i");
		$sec = date("s");
		$date_now = $year."-".$month."-".$day." ".$hours.":".$munite.":".$sec;

		$bind_param = "ssssssssssss";
		$array_val = array($sBillId, $sQ, $sUid, $sColD, $sColT, $date_now, $request_by, $receive_amt, $paid_amt, $paid_datetime, (isset($prepare_drug_by)? $prepare_drug_by: "none"), (isset($check_drug_by)? $check_drug_by: "none"));

		$query = "INSERT into i_bill_cancel_approve(bill_id, queue, uid, collect_date, collect_time, date_now, note, md_sid, update_date, status, receive_by, receive_amt, paid_amt, paid_datetime, prepare_drug_by, check_drug_by) 
		values(?, ?, ?, ?, ?, ?, '', '', '', 'W', ?, ?, ?, ?, ?, ?);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($bind_param, ...$array_val);

		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0){

			}
		}
		
		$query="UPDATE i_bill_list SET paid_method='', receive_amt='',receive_by='', paid_amt='' , paid_datetime='0000-00-00',bill_note='' WHERE clinic_id=? AND bill_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sClinicId,$sBillId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0){
				$aRes["res"]="1";
				$aBillLog["bill_id"]=$sBillId;
				$bUpdateLog=true;
			}
		}
	}
}

//Update Log
if($bUpdateLog){
	$query = "INSERT INTO i_bill_log(s_id,event_name,bill_id,clinic_id,uid,total_amt,log_datetime)
		VALUES(?,?,?,?,?,?,NOW());";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssssss",$aBillLog["s_id"],$aBillLog["event_name"],$aBillLog["bill_id"],$aBillLog["clinic_id"],$aBillLog["uid"],$aBillLog["total_amt"]);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
	}
}
$mysqli->close();

$sTemp=json_encode($aRes);
echo($sTemp);
?>