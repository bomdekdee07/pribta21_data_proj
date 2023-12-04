<?
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sMode = getQS("u_mode");
$sToday = date("Y-m-d");

$sNow=date("Y-m-d H:i:s");
include("array_post.php");
$aRes=array();
$isEcho = getQS("isecho");
$aRes["res"] = "0";
$aRes["msg"] = "";
$sSid = getSS("s_id");
$sClinicId=getSS("clinic_id");
$isLog=false; $aLog=array();
$sQ=getQS("q");

$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");

if($sColD=="")$sColD=getQS("collect_date");
if($sColT=="")$sColT=getQS("collect_time");

$aLogData=array("now"=>$sNow,"s_id"=>$sSid,"event_code"=>$sMode,"collect_date"=>"","collect_time"=>"","uid"=>"","clinic_id"=>$sClinicId,"supply_code"=>"","supply_lot"=>"","order_code"=>"","order_status"=>"","dose_day"=>"","dose_before"=>"","dose_breakfast"=>"","dose_lunch"=>"","dose_dinner"=>"","dose_night"=>"","sale_price"=>"","sale_opt_id"=>"","order_note"=>"","supply_desc"=>"","is_paid"=>"","is_pickup"=>"","total_price"=>"","supply_name"=>"","supply_group_code"=>"","supply_unit"=>"","dose_note"=>"","total_cost"=>"");


foreach ($aPost as $key => $value) {
	if(isset($aLogData[$key])) $aLogData[$key] = $value;
}

include("in_db_conn.php");
if($sMode=="add"){
	$aRes["res"] = "0"; $isCon = true;
	$query = "INSERT INTO i_stock_master (".$sInsCol.") VALUES (".$sInsVal.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
	  	$AffRow =$stmt->affected_rows;
		if($AffRow > 0) {
			$aRes["res"] = 1;	
			$aRes["addrow"]	= getSupplyMasterRow(getQS("supply_group_code"),getQS("supply_group_name"),getQS("supply_code"),getQS("supply_name"),getQS("supply_desc"),getQS("supply_unit"),getQS("dose_day"),getQS("dose_per_time"),getQS("dose_before"),getQS("dose_breakfast"),getQS("dose_lunch"),getQS("dose_dinner"),getQS("dose_night"),getQS("dose_note"),getQS("supply_status"),getQS("supply_group_type"));
			$isLog=true;
			$aLog[count($aLog)] = array_values($aLogData);
		}else{
			$aRes["msg"] = "Error Insert Row : ".$stmt->error;
		}
	}else{
		$aRes["msg"] = "Duplicate Key";
	}
}else if($sMode=="edit"){
	$aRes["res"] = 0;
	$doUpdate = true;
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
		$isLog=true;
		$doUpdate = false;

	}else if(isset($aPost["priceonly"])){
		$doUpdate = false;
	}
	$sCode = getQS("supply_code");

	if($doUpdate){
		$query = "UPDATE i_stock_master SET ".$sUpdSet." WHERE ".$sUpdWhere;
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$isLog=true;
				$aLog[count($aLog)] = array_values($aLogData);
			}else{
				$aRes["msg"] = "Can't update stock master";
			}
		}		
	}
}else if($sMode=="get_supply_master_row"){
	$sCode = getQS("code");
	$query = "SELECT ISG.supply_group_code,supply_group_name,supply_code,supply_name,supply_desc,supply_unit,dose_day,dose_per_time,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,dose_note,supply_status
 	FROM i_stock_master ISM 
 	LEFT JOIN i_stock_group ISG
 	ON ISG.supply_group_code = ISM.supply_group_code
 	WHERE supply_code = ?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sValue);
	if($stmt->execute()){
		$stmt->bind_result($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status);
		while($stmt->fetch()){
			$aRes["msg"]=getSupplyMasterRow($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status);
		}
	}
}else if($sMode=="delete"){
	//Check if  i_stock_list  is already added.
	$sCode = getQS("supply_code");
	$query = "SELECT clinic_id,supply_code FROM i_stock_list WHERE supply_code=? LIMIT 1";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sCode);
	if($stmt->execute()){
		$stmt->bind_result($clinic_id,$supply_code);
		while($stmt->fetch()){
			$aRes["msg"] = "Supply already in used in clinic : ".$clinic_id;
		}
	}
	if($aRes["msg"]==""){
		//No Row Found
		$query = "DELETE FROM i_stock_master WHERE supply_code = ?";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("s",$sCode);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"]="1";
				$isLog=true;
				$aLog[count($aLog)] = array_values($aLogData);
			}else{
				$aRes["msg"]="Error remove supply master";
			}
		}
		if($aRes["res"]=="1"){
			$query = "DELETE FROM i_stock_price WHERE supply_code = ?";
			$stmt=$mysqli->prepare($query);
			$stmt->bind_param("s",$sCode);
			if($stmt->execute()){
			}
		}
	}
}else if($sMode=="get_lot_exp_date"){
	$supCode = getQS("supply_code");
	$stockLot = getQS("stock_lot");
	$query = "SELECT stock_exp_date FROM i_stock_list
 	WHERE supply_code = ? AND stock_lot = ?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ss",$supCode,$stockLot);
	if($stmt->execute()){
		$stmt->bind_result($stock_exp_date);
		while($stmt->fetch()){
			$aRes["res"]="1";
			$aRes["msg"]=$stock_exp_date;
		}
	}
}else if($sMode=="stock_import" && $sClinicId!=""){
	$sReqId = getQS("request_id");

	$querySup = "INSERT INTO i_stock_list(clinic_id,supply_code,stock_lot,stock_amt,stock_added_datetime,stock_added_by,stock_exp_date,stock_note,stock_cost)
	SELECT ?,supply_code,?,?,NOW(),?,?,?,request_item_price FROM i_stock_request_item ISRI WHERE request_id=? AND supply_code=?
	ON DUPLICATE KEY UPDATE stock_added_datetime=NOW(),stock_cost=((stock_amt*stock_cost)+(ISRI.request_item_price*ISRI.request_amt))/(stock_amt+ISRI.request_amt),stock_amt=stock_amt+ISRI.request_amt";
	//VALUES(?,?,?,?,NOW(),?,?,?,?) ON DUPLICATE KEY UPDATE stock_amt=stock_amt+VALUES(stock_amt),stock_added_datetime=NOW() ";

	$stmtSup = $mysqli->prepare($querySup);
	foreach ($aPost["items"] as $key => $value) {
		$aTemp = explode(":",$value);
		$supCode = urldecode($aTemp[0]);
		$supAmt = urldecode($aTemp[1]); 
		$supLot = urldecode($aTemp[2]);	
		$supExp = urldecode($aTemp[3]);
		$supNote = urldecode($aTemp[4]);

		$stmtSup->bind_param("ssssssss",$sClinicId,$supLot,$supAmt,$sSid,$supExp,$supNote,$sReqId,$supCode);
	
		if($stmtSup->execute()){
			$iAffRow =$stmtSup->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"]="1";
			}else{
				//Not Success
				$aRes["msg"]="Error/Abort";
			}
		}
		if($aRes["msg"]==""){
			//No Error Update to i_stock_recieved
			$query = "INSERT INTO i_stock_recieved(request_id,clinic_id,supply_code,item_no,stock_lot,exp_date,supply_amt,remark,recieved_by,recieved_datetime,recieved_status)

			VALUES(?,?,?,1,?,?,?,?,?,NOW(),'FIN') ON DUPLICATE KEY UPDATE supply_amt=supply_amt+VALUES(supply_amt),recieved_by=VALUES(recieved_by),recieved_datetime=NOW() ;";

			$stmt=$mysqli->prepare($query);
			$stmt->bind_param("ssssssss",$sReqId,$sClinicId,$supCode,$supLot,$supExp,$supAmt,$supNote,$sSid);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					
				}else{
					$aRes["msg"].=",".$aTemp[0];
				}
			}else{
				$aRes["msg"]=" Item Already Added.";
			}
		}
	}


	if($aRes["msg"]==""){
		$bSuccess=true; $aSupChk=array();
		//Checked If All Row Update 

		$query = "SELECT ISRI.supply_code,request_amt,ISR.supply_amt,ISR.stock_lot FROM i_stock_request_item ISRI 
		LEFT JOIN i_stock_recieved ISR
		ON ISR.request_id=ISRI.request_id
		AND ISR.supply_code=ISRI.supply_code
		WHERE ISRI.request_id=? AND request_item_status NOT IN ('CC','FIN') ORDER BY request_item_no;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sReqId);
		if($stmt->execute()){
			$stmt->bind_result($scode,$request_amt,$supply_amt,$stock_lot);
			while ($stmt->fetch()) {
				$aSupChk[$scode]["req"] = $request_amt;
				$aSupChk[$scode]["sum"] = (isset($aSupChk[$scode]["sum"])?$aSupChk[$scode]["sum"]:0) + ($supply_amt*1);
			}
		}

		foreach ($aSupChk as $scode => $aI) {
			if($aI["req"] > $aI["sum"]) $bSuccess=false;
		}


		//ADD LOG HERE
		


		if($bSuccess){
			//No Error Import to i_stock_list
			$aRes["status"]="REFRESH";
			$query="UPDATE i_stock_request_list SET request_status='FIN',recieved_date=NOW() WHERE request_id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s",$sReqId);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow <= 0) $aRes["msg"]="Error setting request list during importing data. But item has been imported";
			}

			/*
			$query="UPDATE i_stock_request_item SET request_item_status='FIN' WHERE request_id=? ";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s",$sReqId);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow <= 0) $aRes["msg"]="Error setting stock request item during import data. But item has been imported";
			}
			*/
		}else{
			$aRes["status"]="PARTIAL";
		}

	}
}else if($sMode=="upload_supply_file"){
	$sReqId=getQS("request_id");
	
	$supportFile=",jpg,png,jpeg,gif,doc,docx,pdf,xlsx,xls,";
	$reqDir = "supply_files/";
	$sFileTitle=getQS("file_title");
	$oldFN = basename($_FILES["request_file"]["name"]);

	
	$fileType = strtolower(pathinfo($reqDir.$oldFN,PATHINFO_EXTENSION));
	$sFileName=$sReqId."_".date("YmdHis").".".$fileType;
	$reqFile = $reqDir.$sFileName;

	if(strpos($supportFile,$fileType)>0){
		if (move_uploaded_file($_FILES["request_file"]["tmp_name"], $reqFile)) {
			$aRes["res"] = "1";
			//Insert into db
			$query = "INSERT INTO i_stock_request_file(request_id,file_title,file_name,updated_datetime,updated_by,original_filename) VALUES(?,?,?,NOW(),?,?);";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sssss",$sReqId,$sFileTitle,$sFileName,$sSid,$oldFN);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					//Success
				}else{
					//Not Success
				}
			}else{
				$aRes["msg"]="Duplicate data. Please try again";
			}
		} else {
			$aRes["msg"] = "Error Upload File. Please try again.";
		}
	}else{
		$aRes["msg"] = "Error file is not support";
	}
}else if($sMode=="add_supply_order"){
	//Get Item Info First. Do not get from Query for security purpose
	$aSup=array();
	$sOCode = date("YmdHis");
	$query = "SELECT supply_name,supply_unit,sale_opt_name,sale_price,is_service,ISG.supply_group_type FROM i_stock_master ISM
	LEFT JOIN i_stock_group ISG
	ON ISG.supply_group_code = ISM.supply_group_code
	LEFT JOIN i_stock_type IST
	ON IST.supply_group_type = ISG.supply_group_type
	LEFT JOIN i_stock_price ISP
	ON ISP.supply_code = ISM.supply_code
	LEFT JOIN sale_option SOI
	ON SOI.sale_opt_id = ISP.sale_opt_id
	WHERE ISM.supply_code=? AND ISP.sale_opt_id = ?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ss",$aPost["supply_code"],$aPost["sale_opt_id"]);
	if($stmt->execute()){
		$stmt->bind_result($supply_name,$supply_unit,$sale_opt_name,$sale_price,$is_service,$supply_group_type);
		while($stmt->fetch()){
			$aSup["supply_name"] = $supply_name;
			$aSup["supply_unit"] = $supply_unit;
			$aSup["sale_opt_name"] = $sale_opt_name;
			$aSup["supply_group_type"] = $supply_group_type;
			$aSup["is_service"] = $is_service;
		}
	}

	$aStock=array(); $isOk = false;
	
	//Get Stock List


	if(count($aSup)==0){

	}else if($aSup["is_service"]=="1"){
		//Service No need to check stock
		$aStock[0]["amt"] = 99999999999999;
		$aStock[0]["exp"] = $sToday;
		$aStock[0]["isamt"]=$aPost["dose_day"];
		$aStock[0]["done"]=true;
		$aStock[0]["price"]=$aPost["dose_day"]*$aPost["sale_price"];
		$aStock[0]["cost"] = 0;
		$isOk = true;
	}else{
		//Product

		$query = "SELECT stock_lot,stock_amt,stock_exp_date,stock_cost FROM i_stock_list 
		WHERE clinic_id=? AND supply_code=? AND stock_amt > 0  AND (stock_exp_date >= ? || stock_exp_date ='0000-00-00') ORDER BY stock_exp_date ASC";
		$stmt=$mysqli->prepare($query);

		$stmt->bind_param("sss",$sClinicId,$aPost["supply_code"],$sToday);
		if($stmt->execute()){
			$stmt->bind_result($stock_lot,$stock_amt,$stock_exp_date,$stock_cost);
			while($stmt->fetch()){
				$aStock[$stock_lot]["amt"] = $stock_amt;
				$aStock[$stock_lot]["exp"] = $stock_exp_date;
				$aStock[$stock_lot]["cost"] = $stock_cost;
				$isOk = true;
			}
		}else{

		}

		if($isOk){
			$iAmt = $aPost["dose_day"];
			$iLeft = $iAmt;$isOk = false;
			foreach ($aStock as $sLot => $aT) {
				if($iLeft > 0){
					$iOrdAmt = $iLeft;
					if($aT["amt"]>=$iLeft){

					}
					else{
						$iOrdAmt = $aT["amt"];
					}
					$query = "UPDATE i_stock_list SET stock_amt=stock_amt-? WHERE clinic_id=? AND supply_code=? AND stock_lot=? AND stock_amt >= ?";
					$stmt=$mysqli->prepare($query);
					
					$stmt->bind_param("sssss",$iOrdAmt,$sClinicId,$aPost["supply_code"],$sLot,$iOrdAmt);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0) {
							$aStock[$sLot]["isamt"]=$iOrdAmt;
							$aStock[$sLot]["done"]=true;
							$aStock[$sLot]["price"]=$iOrdAmt*$aPost["sale_price"];
							$iLeft = $iLeft - $iOrdAmt;
							$isOk = true;
						}else{
							//Not Success
						}
					}
				}
			}
		}else{

		}
	}

	if($isOk){
		//Cut Stock at least one of stock was removed.
		foreach ($aStock as $sLot => $aT) {
			if(isset($aT["done"])){
				$aPost["order_note"]=(isset($aPost["order_note"])?$aPost["order_note"]:"");
				$aPost["supply_desc"]=(isset($aPost["supply_desc"])?$aPost["supply_desc"]:"");
				
				//This lot is deducted. Retry 3 times
				$iRetry = 3;
				do {
					$isPaid = 0; $isPickup = 0;
					//if($aSup["is_service"]==1) $isPickup = 1;
					//if($aT["price"]<=0)  $isPaid=0;
					$query = "INSERT INTO i_stock_order(order_code,collect_date,collect_time,uid,clinic_id,supply_code,supply_lot,order_status,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,dose_day,sale_price,sale_opt_id,order_note,supply_desc,total_price,order_datetime,order_by,is_paid,is_pickup,total_cost) VALUES (?,?,?,?,?,?,?,1,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,'".$isPickup."',?);";
					$stmt=$mysqli->prepare($query);
					$iCost=$aT["cost"]*$aT["isamt"];
					$stmt->bind_param("sssssssssssssssssssss",$sOCode,$aPost["collect_date"],$aPost["collect_time"],$aPost["uid"],$sClinicId,$aPost["supply_code"],$sLot,$aPost["dose_before"],$aPost["dose_breakfast"],$aPost["dose_lunch"],$aPost["dose_dinner"],$aPost["dose_night"],$aT["isamt"],$aPost["sale_price"],$aPost["sale_opt_id"],$aPost["order_note"],$aPost["supply_desc"],$aT["price"],$sSid,$isPaid,$iCost);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0) {
							$iRetry=0;
							$aRes["res"]=1; $isLog=true;
							$aRes["msg"].=getOrderList($sOCode,$aPost["supply_code"],$aSup["supply_name"],$aSup["supply_unit"],1,$aPost["sale_opt_id"],$aSup["supply_name"],$aPost["dose_before"],$aPost["dose_breakfast"],$aPost["dose_lunch"],$aPost["dose_dinner"],$aPost["dose_night"],$aPost["supply_desc"],$aPost["order_note"],$aT["isamt"],$aPost["sale_price"],$aT["price"],$aSup["is_service"]);

							$aTemp=array("now"=>$sNow,
								"s_id"=>$sSid,
								"event_code"=>$sMode,
								"collect_date"=>$aPost["collect_date"],
								"collect_time"=>$aPost["collect_time"],
								"uid"=>$aPost["uid"],
								"clinic_id"=>$sClinicId,
								"supply_code"=>$aPost["supply_code"],
								"supply_lot"=>$sLot,
								"order_code"=>$sOCode,
								"order_status"=>"1",
								"dose_day"=>$aT["isamt"],
								"dose_before"=>$aPost["dose_before"],
								"dose_breakfast"=>$aPost["dose_breakfast"],
								"dose_lunch"=>$aPost["dose_lunch"],
								"dose_dinner"=>$aPost["dose_dinner"],
								"dose_night"=>$aPost["dose_night"],
								"sale_price"=>$aPost["sale_price"],
								"sale_opt_id"=>$aPost["sale_opt_id"],
								"order_note"=>$aPost["order_note"],
								"supply_desc"=>$aPost["supply_desc"],
								"is_paid"=>"0",
								"is_pickup"=>"0",
								"total_price"=>$aT["price"],
								"supply_name"=>"",
								"supply_group_code"=>"",
								"supply_unit"=>"",
								"dose_note"=>"",
								"total_cost"=>$iCost);
							$aLog[count($aLog)] = array_values($aTemp);

							//$aLog[count($aLog)]=array($sSid,$sMode,$aPost["collect_date"],$aPost["collect_time"],$aPost["uid"],$sClinicId,$aPost["supply_code"],$sLot,$sOCode,"1",$aT["isamt"],$aPost["dose_before"],$aPost["dose_breakfast"],$aPost["dose_lunch"],$aPost["dose_dinner"],$aPost["dose_night"],$aPost["sale_price"],$aPost["sale_opt_id"],$aPost["order_note"],$aPost["supply_desc"],"0","0",$aT["price"],$aT["cost"]);
							if($aSup["is_service"]!="1"){
								//Item is stocked must update in_queue_list removed checked by
								$query = "UPDATE i_queue_list SET check_drug_by='',check_drug_date='0000-00-00' WHERE uid=? AND collect_date=? AND collect_time=?;";
								$stmt=$mysqli->prepare($query);
								$stmt->bind_param("sss",$aPost["uid"],$sToday,$aPost["collect_time"]);
								if($stmt->execute()){
								}
							}



						}else{
							//This is a problem. The stock already removed but stock is not added into the order. The stock will be missing
						}
					}else{
						error_log("supply_a.php :".$stmt->error);
					}

					$iRetry--;
				} while ($iRetry > 0);


			}
		}
		
		//Add Project for Discount
		$sProjId = getQS("projid");
		if($aSup["supply_group_type"]=="3" && $sProjId != ""){

			$sSupCode = getQS("suplist");
			$sOrdCode = getQS("ordlist");
			
			$aSupList = explode(",", $sSupCode);
			$aOrdList = explode(",", $sOrdCode);

			$query= "UPDATE i_stock_order SET proj_id=? WHERE uid=? AND collect_date=? AND collect_time=? AND supply_code=? AND order_code=?;";
			$stmt=$mysqli->prepare($query);


			foreach ($aSupList as $ix => $supcode) {
				$ordcode=$aOrdList[$ix];
				//error_log($sProjId.",".$sUid.",".$sColD.",".$sColT.",".$supcode.",".$ordcode);
				$stmt->bind_param("ssssss",$sProjId,$sUid,$sColD,$sColT,$supcode,$ordcode);
				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
				}
			}


			$sLabCode = getQS("labid");
			$aLabList = explode(",", $sLabCode);
			$query= "UPDATE p_lab_order_lab_test SET proj_id=? WHERE uid=? AND collect_date=? AND collect_time=? AND lab_id=?";
			$stmt=$mysqli->prepare($query);
			foreach ($aLabList as $ix => $labid) {
				//error_log($sProjId.",".$sUid.",".$sColD.",".$sColT.",".$labid);
				$stmt->bind_param("sssss",$sProjId,$sUid,$sColD,$sColT,$labid);
				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
				}
			}

		}
		
	}else{
		$aRes["msg"]="ไม่พบยา หรือ ยาหมดอายุ กรุณาตรวจสอบอีกครั้ง";
	}

	/*
	uid: P20-11911
	collect_date: 2021-09-01
	collect_time: 09:25:34
	supply_code: SP90005
	sale_opt_id: S01
	dose_before: P
	dose_breakfast: 0
	dose_lunch: 0
	dose_dinner: 0
	dose_night: 0
	dose_day: 100
	supply_desc: รับประทาน ครั้งละ 1 เม็ด เวลา ....... น.
	order_note: tester
	colpk: uid,collect_date,collect_time,supply_code
	col: sale_opt_id,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,dose_day,supply_desc,order_note
	u_mode: add_supply_order
	*/
}else if($sMode=="delete_supply_order"){
	//get supply info
	$aSup=array(); $isService=""; $isFound = false;
	$query = "SELECT ISO.clinic_id,ISO.supply_lot,ISO.order_status,ISO.dose_day,ISO.is_paid,ISO.is_pickup,is_service,total_price FROM i_stock_order ISO
	LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISO.supply_code
	LEFT JOIN i_stock_group ISG	ON ISG.supply_group_code = ISM.supply_group_code
	LEFT JOIN i_stock_type IST	ON IST.supply_group_type = ISG.supply_group_type
	WHERE uid=? AND collect_date=? AND collect_time=? AND ISO.supply_code=? AND order_code=?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("sssss",$aPost["uid"],$aPost["collect_date"],$aPost["collect_time"],$aPost["supply_code"],$aPost["order_code"]);
	if($stmt->execute()){
		$stmt->bind_result($clinic_id,$supply_lot,$order_status,$total_amt,$is_paid,$is_pickup,$is_service,$total_price);
		while($stmt->fetch()){
			//$aSup["supply_name"] = $supply_name;
			$aSup["clinic"]=$clinic_id;
			$aSup["lot"]=$supply_lot;
			$aSup["status"]=$order_status;
			$aSup["amt"]=$total_amt;
			$aSup["paid"]=$is_paid;
			$aSup["pickup"]=$is_pickup;
			$aSup["service"]=$is_service;
			$aSup["total_price"]=$total_price;
			$isService=$is_service;
			$isFound = true;
		}
	}

	if($isFound){
		if($aSup["paid"]){
			$aRes["msg"]="Item already paid. Please contact cashier to refund first.";
		}else if($isService=="1"){
			$aRes["res"]="1";
		}else if($isService=="0"){
			$query = "UPDATE i_stock_list SET stock_amt=stock_amt+? WHERE clinic_id=? AND supply_code=? AND stock_lot=?";
			$stmt=$mysqli->prepare($query);
			$stmt->bind_param("ssss",$aSup["amt"],$aSup["clinic"],$aPost["supply_code"],$aSup["lot"]);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					$aRes["res"]="1";
				}else{
					$aRes["msg"]="Error Deduct Item";
				}
			}
		}else if($isService==""){
			//No supply code found 
			$aRes["msg"] = "No specific supply code found.";
		}else{
			$aRes["msg"] = "Service code is not correct.";
		}
	}else{
		$aRes["msg"]="No record found.";
		$aRes["errcode"]="REFRESH";
	}

	if($aRes["res"]=="1"){
		$iStep=3;
		do{
			$query = "DELETE FROM i_stock_order WHERE uid=? AND collect_date=? AND collect_time=? AND supply_code=? AND order_code=? AND supply_lot=?";
			$stmt=$mysqli->prepare($query);
			$stmt->bind_param("ssssss",$aPost["uid"],$aPost["collect_date"],$aPost["collect_time"],$aPost["supply_code"],$aPost["order_code"],$aSup["lot"]);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					$aRes["res"]="1"; $iStep=0; $isLog=1;
					$aLog[count($aLog)] = array_values($aLogData);
					//$aLog[count($aLog)]=array($sSid,$sMode,$aPost["collect_date"],$aPost["collect_time"],$aPost["uid"],$aSup["clinic"],$aPost["supply_code"],$aSup["lot"],$aPost["order_code"],$aSup["status"],$aSup["amt"],"","","","","","","","","",$aSup["paid"],$aSup["pickup"],$aSup["total_price"]);
				}else{
					$aRes["res"]="0";
					$aRes["msg"]="Error Deduct Item";
				}
				$iStep--;
			}else{
				$aRes["msg"]=$stmt->error;
			}
		}while($iStep>0);
	}
}else if($sMode=="edit_supply_order"){
	//get supply info
	$aSup=array(); $isService=""; $isFound = false; $isPaid="";
	$query = "SELECT ISO.clinic_id,ISO.supply_lot,ISO.order_status,ISO.dose_day,ISO.is_paid,ISO.is_pickup,is_service,total_price FROM i_stock_order ISO
	LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISO.supply_code
	LEFT JOIN i_stock_group ISG	ON ISG.supply_group_code = ISM.supply_group_code
	LEFT JOIN i_stock_type IST	ON IST.supply_group_type = ISG.supply_group_type
	WHERE uid=? AND collect_date=? AND collect_time=? AND ISO.supply_code=? AND order_code=?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("sssss",$aPost["uid"],$aPost["collect_date"],$aPost["collect_time"],$aPost["supply_code"],$aPost["order_code"]);
	if($stmt->execute()){
		$stmt->bind_result($clinic_id,$supply_lot,$order_status,$total_amt,$is_paid,$is_pickup,$is_service,$total_price);
		while($stmt->fetch()){
			$aLogData["clinic_id"]=$clinic_id;
			$aLogData["supply_lot"]=$supply_lot;
			$aSup["paid"]=$is_paid;
			$aSup["pickup"]=$is_pickup;
			$isService=$is_service;
			$isFound = true;
		}
	}

	if($isFound){
		$sCol = getQS("col");
		if($isService=="0" && isset($aPost["dose_day"])){
			//This shouldn't happen. Item is product. Not allow to update amount.
			$aRes["msg"]="Item is product and not allow to update stock. Please remove the item and added again.";
		}else if(($aSup["paid"] || $aSup["pickup"]) && (strpos($sCol, "sale_price") !== false ||  strpos($sCol, "dose_day") !== false) ){
			$aRes["msg"]="Item is already paid/pickup. Update is now allowed. Please refund and try again.";
		}else{
			$iStep=3;
			do{
				$query = "UPDATE i_stock_order SET ".$sUpdSet." WHERE ".$sUpdWhere;
				$stmt=$mysqli->prepare($query);
				$stmt->bind_param($sParam,...$aUpdData);
				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"]="1"; $iStep=0; $isLog=1;
						$aTemp=array();
						//foreach ($aLogData as $sK=>$sV) $aTemp[count($aTemp)]=$sV;
						$aLog[count($aLog)] = array_values($aLogData);
					}else{
						$aRes["res"]="0";
						$aRes["msg"]="Error Deduct Item";
					}
					$iStep--;
				}else{
					$aRes["msg"]=$stmt->error;
				}
			}while($iStep>0);
		}
	}else{
		$aRes["msg"]="No record found.";
		$aRes["errcode"]="REFRESH";
	}
}else if($sMode=="quick_add_supply"){
	//Only Service is support.
	$aSup=array();
	$sOCode = date("YmdHis");
	$sAmt=getQS("amt");
	$sComment=getQS("comment");
	$query = "SELECT supply_name,supply_unit,dose_day,sale_opt_name,sale_price,is_service,supply_desc FROM i_stock_master ISM
	LEFT JOIN i_stock_group ISG
	ON ISG.supply_group_code = ISM.supply_group_code
	LEFT JOIN i_stock_type IST
	ON IST.supply_group_type = ISG.supply_group_type
	LEFT JOIN i_stock_price ISP
	ON ISP.supply_code = ISM.supply_code
	LEFT JOIN sale_option SOI
	ON SOI.sale_opt_id = ISP.sale_opt_id
	WHERE ISM.supply_code=? AND ISP.sale_opt_id = ?";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ss",$aPost["supply_code"],$aPost["sale_opt_id"]);
	if($stmt->execute()){
		$stmt->bind_result($supply_name,$supply_unit,$dose_day,$sale_opt_name,$sale_price,$is_service,$supply_desc);
		while($stmt->fetch()){
			$aSup["supply_name"] = $supply_name;
			$aSup["supply_unit"] = $supply_unit;
			$aSup["sale_opt_name"] = $sale_opt_name;
			$aSup["sale_price"] = $sale_price;
			$aSup["is_service"] = $is_service;
			$aSup["supply_desc"] = $supply_desc;
			$aSup["dose_day"] = $sAmt;
		}
	}
	$isValid=true;
	if(getQS("nodup")==1){
		//Check if the code is already added.
		$query = "SELECT order_code FROM i_stock_order WHERE uid=? AND collect_date=? AND collect_time=? AND supply_code=?";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("ssss",$aPost["uid"],$aPost["collect_date"],$aPost["collect_time"],$aPost["supply_code"]);
		if($stmt->execute()){
			$stmt->bind_result($order_code);
			while($stmt->fetch()){
				$isValid=false;
				$aRes["msg"]="ซ้ำ/Duplicate";
			}
		}
	}

	if(isset($aSup["is_service"])==false){
		$aRes["msg"]="Error. No service code found.";
	}else if($aSup["is_service"] != "1"){
		$aRes["msg"]="Error. Only Service type is allow quick added";
	}else if($isValid){
		$query = "INSERT INTO i_stock_order(order_code,collect_date,collect_time,uid,clinic_id,supply_code,supply_lot,order_status,dose_day,sale_price,sale_opt_id,supply_desc,total_price,order_datetime,order_by,is_paid,is_pickup,order_note) VALUES (?,?,?,?,?,?,'0',1,?,?,?,?,?,NOW(),?,'0','0',?);";
		$iDoseDay=(isset($aPost["dose_day"])?$aPost["dose_day"]:$aSup["dose_day"]);
		$iTPrice=$aSup["sale_price"]*$iDoseDay;
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("sssssssssssss",$sOCode,$aPost["collect_date"],$aPost["collect_time"],$aPost["uid"],$sClinicId,$aPost["supply_code"],$iDoseDay,$aSup["sale_price"],$aPost["sale_opt_id"],$aSup["supply_desc"],$iTPrice,$sSid,$sComment);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$iRetry=0;
				$aRes["res"]=1; $isLog=true;
				foreach ($aPost as $sKey => $sValue) {
					if(isset($aLogData[$sKey])) $aLogData[$sKey]= $sValue;
				}
				$aLog[count($aLog)]=array_values($aLogData);

			}else{
				//This is a problem. The stock already removed but stock is not added into the order. The stock will be missing
			}
		}else{
			error_log("supply_a.php :".$stmt->error);
		}		
	}
}else if($sMode=="supply_pickup"){
	//get Q Info
	if($sColD=="" || $sUid=="" && $sQ!=""){
		$query="SELECT uid,collect_date,collect_time,room_no FROM i_queue_list WHERE queue=? AND clinic_id=? AND collect_date=?;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sss',$sQ,$sClinicId,$sToday);
		if($stmt->execute()){
			$stmt->bind_result($uid,$collect_date,$collect_time,$room_no);
			while($stmt->fetch()){
				$sColT=$collect_time;
				$sColD=$collect_date;
				$sUid=$uid;

			}
		}		
	}




	$query = "UPDATE i_stock_order SET is_pickup=1,pickup_datetime=NOW(),updated_datetime=NOW(),updated_by=? WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('sssss',$sSid,$sClinicId,$sUid,$sColD,$sColT);
	if($stmt->execute()){
		if($stmt->affected_rows > 0) {
			$aRes["res"]="1";
		}else{
			$aRes["msg"]="Error Q is not finish";
		}
	}

	if($aRes["res"]=="1"){
		
		$query = "UPDATE i_queue_list SET queue_call=0,queue_status=1,queue_datetime=NOW(),s_id=?,issue_drug_by=?,issue_drug_date=NOW() 
		WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=? ";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssssss',$sSid,$sSid,$sClinicId,$sUid,$sColD,$sColT);
		if($stmt->execute()){
			if($stmt->affected_rows > 0) {
				$aRes["res"]="1";
			}else{
				$aRes["res"]="0";
				$aRes["msg"]="Item pickup successful but Q is not sent to home. Please  refresh and send q to home manually.";
			}
		}

		//Update Log
		$query = "INSERT INTO i_queue_list_log(event_code,clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_call,queue_type,s_id,issue_drug_by,issue_drug_date) SELECT ?,clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_call,1,s_id,issue_drug_by,issue_drug_date FROM i_queue_list WHERE clinic_id=? AND queue=? AND collect_date=? AND queue_type='1'";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sMode,$sClinicId,$sQ,$sToday);
		$stmt->execute();
		
	}
}else if($sMode=="update_lab_price"){
	$sLabId=getQS("labid");
	$sSaleP=getQS("saleprice");
	$sSaleOpt=getQS("saleopt");
	$sUid=getQS("uid");
	$sColD=getQS("coldate");
	$sColT=getQS("coltime");


	$query="UPDATE p_lab_order_lab_test SET sale_price=? , sale_opt_id=?
	WHERE uid=? AND collect_date=? AND collect_time=? AND lab_id=?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssssss",$sSaleP,$sSaleOpt,$sUid,$sColD,$sColT,$sLabId);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0){
			$aRes["res"]="1";
			$isLog=true;
			
			$aLogData["collect_date"]=$sColD;
			$aLogData["collect_time"]=$sColT;
			$aLogData["supply_code"]=$sLabId;
			$aLogData["sale_opt_id"]=$sSaleOpt;
			$aLogData["total_price"]=$sSaleP;
			$aLog[count($aLog)] = array_values($aLogData);
		}
	}
}else if($sMode=="supply_return"){
	//get Q Info
	$sColDate=""; $sColTime="";$sUid="";
	$query="SELECT uid,collect_date,collect_time,room_no FROM i_queue_list WHERE queue=? AND clinic_id=? AND collect_date=?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('sss',$sQ,$sClinicId,$sToday);
	if($stmt->execute()){
		$stmt->bind_result($uid,$collect_date,$collect_time,$room_no);
		while($stmt->fetch()){
			$sColTime=$collect_time;
			$sColDate=$collect_date;
			$sUid=$uid;

		}
	}


	$query = "UPDATE i_stock_order SET is_pickup=0,pickup_datetime='0000-00-00',updated_datetime=NOW(),updated_by=? WHERE collect_date=? AND collect_time=? AND uid=? AND clinic_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('sssss',$sSid,$sToday,$sColTime,$sUid,$sClinicId);
	if($stmt->execute()){
		if($stmt->affected_rows > 0) {
			$aRes["res"]="1";
		}else{
			$aRes["msg"]="Error Q is not finish";
		}
	}

	if($aRes["res"]=="1"){
		$query = "UPDATE i_queue_list SET queue_call=0,queue_status=1,queue_datetime=NOW(),s_id=?,issue_drug_by='',issue_drug_date='0000-00-00' WHERE collect_date=? AND collect_time=? AND uid=? AND clinic_id=? AND queue=? ";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssssss',$sSid,$sToday,$sColTime,$sUid,$sClinicId,$sQ);
		if($stmt->execute()){
			if($stmt->affected_rows > 0) {
				$aRes["res"]="1";
			}else{
				$aRes["res"]="0";
				$aRes["msg"]="Item return successful but Q is not sent to home. Please  refresh and send q to home manually.";
			}
		}

		//Update Log
		$query = "INSERT INTO i_queue_list_log(event_code,clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_call,queue_type,s_id,issue_drug_by,issue_drug_date) SELECT ?,clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_call,1,s_id,issue_drug_by,issue_drug_date FROM i_queue_list WHERE clinic_id=? AND queue=? AND collect_date=? AND queue_type='1'";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sMode,$sClinicId,$sQ,$sToday);
		$stmt->execute();
	}
}else if($sMode=="supply_adjust"){
	$sAmt = getQS("amt");
	$sSupCode=getQS("supcode");
	$sSupLot=getQS("suplot");
	$sEvent=getQS("event");
	$sNote=urlDecode(getQS("note"));
	$sCurAmt=getQS("curamt");
	$sDiffAmt=$sAmt-$sCurAmt;

	$query = "UPDATE i_stock_list SET stock_amt=? WHERE clinic_id=? AND supply_code=? AND stock_lot=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssss',$sAmt,$sClinicId,$sSupCode,$sSupLot);
	if($stmt->execute()){
		if(($stmt->affected_rows) > 0) {
			$aRes["res"]="1";

			$query = "INSERT INTO i_stock_log(clinic_id,action_mode,action_text,updated_date,updated_by,supply_code,supply_lot,supply_amt) VALUES(?,?,?,NOW(),?,?,?,?)";
			$stmt = $mysqli->prepare($query);

			$stmt->bind_param('sssssss',$sClinicId,$sEvent,$sNote,$sSid,$sSupCode,$sSupLot,$sDiffAmt);
			if($stmt->execute()){}

		}else{
			$aRes["msg"]="Error Supply can't update";
		}
	}
}else if($sMode=="supply_cost_adjust"){
	$sCost = getQS("cost");
	$sSupCode=getQS("supcode");
	$sSupLot=getQS("suplot");
	$sEvent=getQS("event");
	$sNote=urlDecode(getQS("note"));

	$query = "UPDATE i_stock_list SET stock_cost=? WHERE clinic_id=? AND supply_code=? AND stock_lot=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssss',$sCost,$sClinicId,$sSupCode,$sSupLot);
	if($stmt->execute()){
		if(($stmt->affected_rows) > 0) {
			$aRes["res"]="1";

			$query = "INSERT INTO i_stock_log(clinic_id,action_mode,action_text,updated_date,updated_by,supply_code,supply_lot,supply_cost) VALUES(?,?,?,NOW(),?,?,?,?)";
			$stmt = $mysqli->prepare($query);

			$stmt->bind_param('sssssss',$sClinicId,$sEvent,$sNote,$sSid,$sSupCode,$sSupLot,$sCost);
			if($stmt->execute()){}

		}else{
			$aRes["msg"]="Error Supply can't update";
		}
	}
}else if($sMode=="supply_group_list"){
	$sType=getQS("supply_group_type");

	$query = "SELECT supply_group_code,supply_group_name,supply_group_type FROM i_stock_group
		ORDER BY supply_group_name ";
	$stmt=$mysqli->prepare($query);
	if($stmt->execute()){
		$stmt->bind_result($supply_group_code,$supply_group_name,$supply_group_type);
		while($stmt->fetch()){
			$aRes["res"]="1";
			$aRes["msg"].="<option value='$supply_group_code' data-type='$supply_group_type'>$supply_group_name</option>";
		}
	}
}else if($sMode=="update_master_sub"){
	$sMasterCode=getQS("master_supply_code");
	$sSupCode=getQS("supply_code");
	$sConvAmt=getQS("convert_amt");

	$query = "INSERT INTO i_stock_master_sub(master_supply_code,supply_code,convert_amt,updated_by,updated_datetime) VALUES(?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE convert_amt=VALUES(convert_amt)";
	if($sSupCode!="") $query.=" ,supply_code=VALUES(supply_code);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssss',$sMasterCode,$sSupCode,$sConvAmt,$sSid);
	if($stmt->execute()){
		if(($stmt->affected_rows) > 0) {
			$aRes["res"]="1";
		}else{
			$aRes["msg"]="Error Supply can't update";
		}
	}	
}else if($sMode=="update_bulk_unit"){
	$sSupCode=getQS("supply_code");
	$sConvAmt=getQS("convert_amt");
	$sBulkUnit=getQS("bulk_unit");

	$query = "UPDATE i_stock_master SET bulk_unit=?,convert_amt=? WHERE supply_code=?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('sss',$sBulkUnit,$sConvAmt,$sSupCode);
	if($stmt->execute()){
		if(($stmt->affected_rows) > 0) {
			$aLogData["bulk_unit"]=$sBulkUnit;
			$aLogData["supply_code"]=$sSupCode;
			$aLogData["convert_amt"]=$sConvAmt;
			$aLog[]=array_values($aLogData);
			$aRes["res"]="1";
			$query = "INSERT INTO i_stock_log(clinic_id,action_mode,updated_date,updated_by,supply_code,bulk_unit,convert_amt)
			VALUES (?,?,NOW(),?,?,?,?);";

			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('ssssss',$sClinicId,$sMode,$sSid,$sSupCode,$sBulkUnit,$sConvAmt);
			if($stmt->execute()){}



		}else{
			$aRes["msg"]="Error Supply can't update";
		}
	}	
}




//Update Price
$sCode = getQS("supply_code");
if($sCode != "" && isset($aPost["saleid"]) && gettype($aPost["saleid"])!="string" && ($sMode=="add" || $sMode=="edit") && $aRes["msg"]==""){
	$errMsg = ""; $sLogSQL=""; 
	$query = "INSERT INTO i_stock_price(supply_code,sale_opt_id,sale_price) VALUES (?,?,?) ON DUPLICATE KEY update sale_price=VALUES(sale_price);";
	$stmt=$mysqli->prepare($query);
	foreach ($aPost["saleid"] as $key => $value) {
		$aTemp = explode(",",$value);
		$stmt->bind_param("sss",$sCode,$aTemp[0],$aTemp[1]);
		$sLogSQL.=(($sLogSQL=="")?"":",")."('".$sCode."','".$aTemp[0]."','".$aTemp[1]."','".$sNow."','".$sSid."')";
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				//Success
			}else{
				$errMsg .= (($errMsg=="")?"":","). $aTemp[0];
			}
		}
	}
	if($errMsg!="") {
		$aRes["msg"] = "Not all row update.";
		$aRes["errsaleid"] = $errMsg;
	}else $aRes["res"] = 1;

	//Add Price Log

	$query="INSERT INTO i_stock_price_log(supply_code,sale_opt_id,sale_price,updated_datetime,updated_by) VALUES".$sLogSQL;
	//error_log($query);
	$stmt=$mysqli->prepare($query);
	if($stmt->execute()){}

}

if($isLog){
	$query="INSERT INTO i_stock_order_log(log_datetime,s_id,event_code,collect_date,collect_time,uid,clinic_id,supply_code,supply_lot,order_code,order_status,dose_day,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,sale_price,sale_opt_id,order_note,supply_desc,is_paid,is_pickup,total_price,supply_name,supply_group_code,supply_unit,dose_note,total_cost) 
	VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";

	$stmt=$mysqli->prepare($query);

	foreach ($aLog as $key => $aLogR) {
		//print_r($aLogR);

		$stmt->bind_param("sssssssssssssssssssssssssssss",...$aLogR);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
		}
	}
}
$mysqli->close();



$returnData = json_encode($aRes);
if($isEcho!="1") echo($returnData);


?>