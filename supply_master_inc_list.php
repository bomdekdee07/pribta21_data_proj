<?
	include_once("in_session.php");
	include_once("in_php_function.php");
	include_once("in_setting_row.php");

	$sGroup = urldecode(getQS("group"));
	$sType = urldecode(getQS("type"));

	if($sGroup=="" && $sType==""){
		echo("Please select group or type");
		exit();
	}

	$sHtml="";
	$query = "SELECT ISG.supply_group_code,supply_group_name,ISM.supply_code,ISM.supply_name,ISM.supply_desc,ISM.supply_unit,ISM.dose_day,ISM.dose_per_time,ISM.dose_before,ISM.dose_breakfast,ISM.dose_lunch,ISM.dose_dinner,ISM.dose_night,ISM.dose_note,ISM.supply_status,supply_group_type,ISMS.supply_code AS sub_supply_code,ISMM.supply_name AS sub_supply_name,ISMM.supply_unit AS sub_supply_unit,ISMS.convert_amt
 	FROM i_stock_master ISM 
 	LEFT JOIN i_stock_group ISG
 	ON ISG.supply_group_code = ISM.supply_group_code
 	LEFT JOIN i_stock_master_sub ISMS
 	ON ISMS.master_supply_code = ISM.supply_code
 	LEFT JOIN i_stock_master ISMM
 	ON ISMM.supply_code = ISMS.supply_code
 	WHERE ";
 	$sValue="";

 	if($sType!="" && $sGroup==""){
 		//Type only
 		$query .= " supply_group_type=?";
 		$sValue = $sType;
 	}else if($sGroup!=""){
 		$query .= " ISM.supply_group_code=?";
 		$sValue = $sGroup;
 	}else{
 		echo("Please select group or type");
 		exit();
 	}
 	
 	$query .= " ORDER BY ISG.supply_group_name,ISM.supply_name";
 	$aItem = array();
	include("in_db_conn.php");



	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sValue);
	if($stmt->execute()){
		$stmt->bind_result($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status,$supply_group_type,$sub_supply_code,$sub_supply_name,$sub_supply_unit,$convert_amt);
		
		while($stmt->fetch()){
			$aItem[$supply_code]["g_code"]=$supply_group_code;
			$aItem[$supply_code]["g_name"]=$supply_group_name;
			$aItem[$supply_code]["s_name"]=$supply_name;
			$aItem[$supply_code]["s_desc"]=$supply_desc;
			$aItem[$supply_code]["s_unit"]=$supply_unit;
			$aItem[$supply_code]["d_day"]=$dose_day;
			$aItem[$supply_code]["d_p_time"]=$dose_per_time;
			$aItem[$supply_code]["d_before"]=$dose_before;
			$aItem[$supply_code]["d_breakfast"]=$dose_breakfast;
			$aItem[$supply_code]["d_lunch"]=$dose_lunch;
			$aItem[$supply_code]["d_dinner"]=$dose_dinner;
			$aItem[$supply_code]["d_night"]=$dose_night;
			$aItem[$supply_code]["d_note"]=$dose_note;
			$aItem[$supply_code]["s_status"]=$supply_status;
			$aItem[$supply_code]["s_type"]=$supply_group_type;
			//$aItem[$supply_code]["s_sub_code"]=$sub_supply_code;
			if($sub_supply_name!="") $aItem[$supply_code]["s_sub_name"]=(isset($aItem[$supply_code]["s_sub_name"])?$aItem[$supply_code]["s_sub_name"].",":"")."[".$sub_supply_code."]".$sub_supply_name." ".$convert_amt." ".$sub_supply_unit;



			//$sHtml.=getSupplyMasterRow($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status,$supply_group_type,$sub_supply_code,$sub_supply_name);
		}
		
	}
	$stmt->close();
	$mysqli->close();

	foreach ($aItem as $supply_code => $aI) {
		$subName = (isset($aI["s_sub_name"])?$aI["s_sub_name"]:"");
		$sHtml.=getSupplyMasterRow($aI["g_code"],$aI["g_name"],$supply_code,$aI["s_name"],$aI["s_desc"],$aI["s_unit"],$aI["d_day"],$aI["d_p_time"],$aI["d_before"],$aI["d_breakfast"],$aI["d_lunch"],$aI["d_dinner"],$aI["d_night"],$aI["d_note"],$aI["s_status"],$aI["s_type"],$subName);
		//$sHtml.=getSupplyMasterRow($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status,$supply_group_type,$sub_supply_name);
	}


	echo($sHtml);
?>