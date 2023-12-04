<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sUid = getQS("uid");
$aHis=array(); $aLab=array(); $aMed=array();

if($sUid==""){

}else{
	include("in_db_conn.php");
	$query="SELECT ISO.collect_date,ISO.collect_time,order_code,ISO.supply_code,supply_name,supply_unit,order_status,ISO.sale_opt_id,sale_opt_name,ISO.dose_before,ISO.dose_breakfast,ISO.dose_lunch,ISO.dose_dinner,ISO.dose_night,ISO.supply_desc,ISO.order_note,ISO.dose_day,ISO.sale_price,ISO.total_price,is_service,is_paid,is_pickup,supply_group_icon
	FROM i_stock_order ISO
	LEFT JOIN i_stock_master ISM
	ON ISM.supply_code = ISO.supply_code
	LEFT JOIN i_stock_group ISG
	ON ISG.supply_group_code = ISM.supply_group_code
	LEFT JOIN i_stock_type IST
	ON IST.supply_group_type = ISG.supply_group_type
	LEFT JOIN sale_option SO
	ON SO.sale_opt_id = ISO.sale_opt_id
	WHERE uid=?  ORDER BY collect_date,collect_time";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	if($stmt->execute()){
		$stmt->bind_result($collect_date,$collect_time,$order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_icon);
		while($stmt->fetch()){
			$aHis[$collect_date." ".$collect_time][]=getOldOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_icon);
		}
	}


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

	WHERE PLO.uid=? AND lab_order_status NOT IN ('C','CC') ORDER BY PLO.collect_date,PLO.collect_time";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	if($stmt->execute()){
		$stmt->bind_result($collect_date,$collect_time,$lab_id,$lab_name,$lab_result,$lab_unit);
		while($stmt->fetch()){
			$aHis[$collect_date." ".$collect_time][]="";
			$aLab[$collect_date." ".$collect_time][]="<div class='fl-wrap-row h-25 fs-small row-hover row-color'>
				<div class='fl-wrap-col'>
					<div class='fl-fill h-15 al-left fw-b fs-small'>$lab_name</div>
					<div class='fl-fill h-10 al-left fs-xsmall lh-10'>$lab_id</div>
				</div>
				<div class='fl-fix w-80'>$lab_result</div>
				<div class='fl-fix w-80'>$lab_unit</div>
			</div>";
		}
	}

	$aMedInfo=array();
	$query ="SELECT uid,collect_date,collect_time,data_id,data_result FROM p_data_result PDR WHERE uid=? AND data_id IN ('food_intolerance','food_intolerance_txt','drug_allergy','drug_allergy_txt','cn_dx','cn_advise_urgen','cn_weight','staff_md') ORDER BY collect_date,collect_time";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	if($stmt->execute()){
		$stmt->bind_result($uid,$collect_date,$collect_time,$data_id,$data_result);
		while($stmt->fetch()){
			if($data_result!="") $aMedInfo[$collect_date." ".$collect_time][$data_id]=$data_result;
		}
	}

	foreach ($aMedInfo as $sDate => $aDataId) {
		$aDate = explode(" ",$sDate);
		$sTDate = getDateText($aDate[0]);
		
		if(isset($aDataId["cn_dx"]) || isset($aDataId["cn_advise_urgen"])){
			if(!isset($aHis[$sDate])) $aHis[$sDate][]="";
			$aMed[$sDate][]="
				<div class='fl-wrap-row h-60 fs-small '>
					<div class='fl-fill  row-hover row-color al-left fl-auto'><span class='fw-b'>DX : </span>".(isset($aDataId["cn_dx"])?$aDataId["cn_dx"]:"")."</div>
					<div class='fl-fill  row-hover row-color al-left  fl-auto border-left-1'><span class='fw-b'>Advice : </span>".(isset($aDataId["cn_advise_urgen"])?$aDataId["cn_advise_urgen"]:"")."</div>
				</div>";
		}

	}


	$mysqli->close();
	ksort($aHis);
	$sTemp="";
	foreach ($aHis as $sDate => $aRow) {
		$aDate = explode(" ",$sDate);
		$sTDate = getDateText($aDate[0]);
		$sTemp.="
			<div class='fl-fix h-20 al-left bg-head-1 fl-cas-head'>$sTDate ".$aDate[1].(isset($aMedInfo[$sDate]["staff_md"]))."</div>";
			$sTemp.="<div class=' fl-cas-body'>";
		if(isset($aMed[$sDate])){
			
			foreach ($aMed[$sDate] as $i => $sRow) {
				$sTemp.=$sRow;
			}
			
		}
		foreach ($aRow as $i => $sRow) {
			$sTemp.=$sRow;
		}

		if(isset($aLab[$sDate])){
			$sTemp.="<div class='fl-fix h-20 al-left bg-head-4'> LAB </div>";

			foreach ($aLab[$sDate] as $i => $sRow) {
				$sTemp.=$sRow;
			}

		}

		$sTemp.="<div class='fl-fix h-20'></div>";
		$sTemp.="</div>";
		
		
		
	}
	
}
?>
<div class='fl-wrap-col fl-auto'>
	<? echo($sTemp); ?>
</div>