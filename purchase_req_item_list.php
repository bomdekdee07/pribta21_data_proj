<?
include_once("in_php_function.php");
$sReqId = getQS("request_id","");
$sMode=getQS("mode");


if($sReqId==""){

}else if(substr($sReqId,0,1)=="R") {
	include_once("in_setting_row.php");
	include("in_db_conn.php");
	$query="SELECT ISRSI.request_id,ISRSI.request_item_no,ISRSI.updated_by,ISRSI.updated_date,ISRSI.supply_code,request_item_show,request_supply_note,request_amt,request_exact_amt,discount_before_vat,discount_before_vat_baht,request_vat,discount_after_vat,discount_after_vat_baht,request_item_price,request_item_price_discount,request_item_price_vat,request_item_price_final,request_total_price,request_total_price_discount,request_total_price_vat,request_total_price_final,request_item_status,request_project,request_account,request_unit,convert_amt
	FROM i_stock_request_show_item ISRSI

	LEFT JOIN i_stock_master_unit ISMU
	ON ISMU.supply_code=ISRSI.supply_code
	AND ISMU.unit_name=ISRSI.request_unit

	WHERE ISRSI.request_id = ?  ORDER BY ISRSI.request_item_no*1";
	$sHtml=""; $aSupInfo=array();
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);
	if($stmt->execute()){
		//$stmt->bind_result($request_item_no,$updated_by,$updated_date,$supply_code,$request_item_show,$request_supply_note,$supply_unit,$request_amt,$request_exact_amt,$discount_before_vat,$discount_before_vat_baht,$request_vat,$discount_after_vat,$discount_after_vat_baht,$request_item_price,$request_item_price_discount,$request_item_price_vat,$request_item_price_final,$request_total_price,$request_total_price_discount,$request_total_price_vat,$request_total_price_final,$request_item_status,$request_project,$request_account,$conv_supply_code,$conv_supply_name,$conv_supply_unit,$convert_amt);
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()) {
			$aSupInfo[] = $row;
		}
	}
	$mysqli->close();

	foreach ($aSupInfo as $iRow => $aInfo) {
		$aInfo=array_values($aInfo);
		$sHtml.=getRequestItemShowRow(...$aInfo);
	}


	echo($sHtml);
}else if(substr($sReqId,0,1)=="S") {
	include_once("in_setting_row.php");
	include("in_db_conn.php");
	$query="SELECT ISRI.request_id,request_item_no,ISRI.supply_code,request_supply_note,request_amt,request_item_price,request_total_price,request_item_status,supply_name,supply_unit,supply_amt
	FROM i_stock_request_item ISRI

    LEFT JOIN i_stock_recieved ISR
    ON ISR.request_id = ISRI.request_id    AND ISR.supply_code = ISRI.supply_code
    
	LEFT JOIN i_stock_master ISM	ON ISM.supply_code = ISRI.supply_code
	WHERE ISRI.request_id = ?";
	$sHtml=""; $aSupInfo=array();
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);
	if($stmt->execute()){
		$stmt->bind_result($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$request_item_price,$request_total_price,$request_item_status,$supply_name,$supply_unit,$supply_amt);
		while($stmt->fetch()){
			$aSupInfo[$supply_code]["request_item_no"] = $request_item_no;
			$aSupInfo[$supply_code]["supply_code"] = $supply_code;
			$aSupInfo[$supply_code]["request_supply_note"] = $request_supply_note;
			$aSupInfo[$supply_code]["request_amt"] = $request_amt;
			$aSupInfo[$supply_code]["request_item_price"] = $request_item_price;
			$aSupInfo[$supply_code]["request_total_price"] = $request_total_price;
			$aSupInfo[$supply_code]["request_item_status"] = $request_item_status;
			$aSupInfo[$supply_code]["supply_name"] = $supply_name;
			$aSupInfo[$supply_code]["supply_unit"] = $supply_unit;
			$aSupInfo[$supply_code]["supply_amt"] = (isset($aSupInfo[$supply_code]["supply_amt"])?$aSupInfo[$supply_code]["supply_amt"]:0)+$supply_amt*1;
			if($aSupInfo[$supply_code]["supply_amt"]==$aSupInfo[$supply_code]["request_amt"])
				$aSupInfo[$supply_code]["request_item_status"]="FIN";


			//$sHtml.=getRequestItemRow($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$request_item_price,$request_total_price,$request_item_status,$supply_name,$supply_unit,true);
		};
	}
	$mysqli->close();

	foreach ($aSupInfo as $supply_code => $aInfo) {
		
		$sHtml.=getRequestItemRow($sReqId,$aInfo["request_item_no"],$supply_code,$aInfo["request_supply_note"],$aInfo["request_amt"],$aInfo["request_item_price"],$aInfo["request_total_price"],$aInfo["request_item_status"],$aInfo["supply_name"],$aInfo["supply_unit"],true);
	}


	echo($sHtml);
}


?>