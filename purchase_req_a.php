<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sMode =getQS("u_mode");
$aRes=array();
$sSid=getSS("s_id");
$sClinicId=getSS("clinic_id");
$isEcho=getQS("echo");
$sMainSec=getSS("main_section");
$aRes["res"] = "0";


include("array_post.php");
include("in_db_conn.php");
//request_id,clinic_id,section_id,request_by,request_datetime,request_status,finance_rec_date,finance_rec_by
$aReqList=array('request_title','request_detail','require_date','delivery_to','delivery_other','request_type','request_proj','finance_req_no','request_po_no');

//request_id,request_item_no,updated_by,updated_date
$aColList=array('supply_code','request_item_show','request_supply_note','request_amt','request_exact_amt','discount_before_vat','discount_before_vat_baht','request_vat','discount_after_vat','discount_after_vat_baht','request_item_price','request_item_price_discount','request_item_price_vat','request_item_price_final','request_total_price','request_total_price_discount','request_total_price_vat','request_total_price_final','request_item_status','request_project','request_account','request_unit');

$sHtml="";
if($sMode=="request_list"){
	$isEcho="1";
	$sModuleId = getQS("module_id");

	$query ="SELECT module_id,module_title,module_color,module_icon FROM i_module";
	if($sModuleId!=""){
		$query.=" WHERE module_id=?";
	}
	$stmt = $mysqli->prepare($query);
	if($sModuleId!="") $stmt->bind_param("s",$sModuleId);
	if($stmt->execute()){
	  $stmt->bind_result($module_id,$module_title,$module_color,$module_icon); 
	  while ($stmt->fetch()) {
	  	if($isOpt=="1"){
	  	 	$sHtml.="<option value='$module_id'>$module_title</option>";
	  	}else{
	  		//$sHtml.=getModuleList($module_id,$module_title,$module_color,$module_icon);
	  	}
	  }
	}
}else if($sMode=="request_file_list"){
	$isEcho="1";
	$sReqId=getQS("request_id");
	$query ="SELECT request_id,file_title,file_name,updated_datetime,updated_by,original_filename,s_name FROM i_stock_request_file ISRF
		LEFT JOIN p_staff PS
		ON PS.s_id = ISRF.updated_by
		WHERE request_id=?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);
	if($stmt->execute()){
	  $stmt->bind_result($request_id,$file_title,$file_name,$updated_datetime,$updated_by,$original_filename,$s_name); 
	  while ($stmt->fetch()) {
	  	//<a href='supply_files/".$file_name."' target='_blank'><i class='fas fa-search fa-2x'></i></a>
	  	$sHtml.="<div class='fl-wrap-row h-30 row-color-2 row-hover'>
	  		<div class='fabtn btnviewdlg fl-fix w-30 fl-mid' data-file='view_request_file' data-qs='request_id=".$request_id."&file=supply_files/".$file_name."' title='Purchase Request File'>
	  			<i class='fas fa-search fa-2x'></i>
	  		</div>
	  		<div class='fl-wrap-col'>
	  			<div class='fl-fill lh-15'>$file_title</div>
	  			<div class='fl-fill lh-15' title='On : ".$updated_datetime."'>By : $s_name</div>
	  		</div>
	  		
	  	</div>";
	  }
	}
}else if($sMode=="request_add"){
	$isCont = true;
	$sReqId=getQS("request_id");
	if($sReqId!=""){
		$aRes["msg"] = "Error request_id should be empty;";
		$isCont = false;
	}

	if($isCont){
		$sDateForm = "R".date("ym-");
		$sInsCol="request_id,clinic_id,section_id,request_by,request_datetime,request_status";
		$sInsVal="@reqid := CONCAT('".$sDateForm."',LPAD((SUBSTRING(IFNULL(MAX(request_id),0),7,6)*1)+1,6,0)),?,?,?,NOW(),'0'";
		$sParam="sss";
		$aObj=array($sClinicId,$sMainSec,$sSid);
		foreach ($aReqList as $iInd => $sCol) {
			if(isset($aPost[$sCol])){
				$sInsCol.=",".$sCol;
				$sInsVal.=",?";
				$sParam.="s";
				array_push($aObj, $aPost[$sCol]);
			}
		}


		$query ="INSERT INTO i_stock_request_list (".$sInsCol.") 
		SELECT ".$sInsVal." FROM i_stock_request_list WHERE request_id LIKE '".$sDateForm."%'";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aObj);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$query ="SELECT @reqid";
				$stmt = $mysqli->prepare($query);
				if($stmt->execute()){
					$stmt->bind_result($reqid);
					while($stmt->fetch()){
						$aRes["res"] = "1";
						$aRes["request_id"] = $reqid;

					}
					
				}else{
					$aRes["msg"] = "Get ID Failed but already inserted. Please Retry";
				}
			}else{
				//Not Success
				$aRes["msg"] = "There are an error while recording your data. Please try again.";
			}
		}else{
			//Not Success
			$aRes["msg"] = "Request ID already Exists. Please try again";
		}	
	}
}else if($sMode=="request_supply_add"){
	$isCont = true;
	if($aPost["request_id"]!="{NEW}"){
		$aRes["msg"] = "Error;";
		$isCont = false;
	}

	if($isCont){
		$sDateForm = "S".date("ym-");
		$sInsVal = str_replace("{NEW_request_id}","@reqid := CONCAT('".$sDateForm."',LPAD((SUBSTRING(IFNULL(MAX(request_id),0),7,6)*1)+1,6,0))",$sInsVal);

		$sInsCol = "request_datetime,clinic_id,section_id,request_by,".$sInsCol;
		$sInsVal = "?,?,?,".$sInsVal;
		$sParam = "sss".$sParam;
		$query ="INSERT INTO i_stock_request_list (".$sInsCol.") 
		SELECT NOW(),".$sInsVal." FROM i_stock_request_list WHERE request_id LIKE '".$sDateForm."%'
		";

		//$query ="INSERT INTO i_stock_request_list (".$sInsCol.") VALUES (".$sInsVal.");";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,$sClinicId,$sMainSec,$sSid,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$query ="SELECT @reqid";
				$stmt = $mysqli->prepare($query);
				if($stmt->execute()){
					$stmt->bind_result($reqid);
					while($stmt->fetch()){
						$aRes["res"] = "1";
						$aRes["request_id"] = $reqid;

					}
					
				}else{
					$aRes["msg"] = "Get ID Failed but already inserted. Please Retry";
				}
			}else{
				//Not Success
				$aRes["msg"] = "There are an error while recording your data. Please try again.";
			}
		}else{
			//Not Success
			$aRes["msg"] = "Request ID already Exists. Please try again";
		}	
	}
}else if($sMode=="request_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE i_stock_request_list SET ".$sUpdSet." WHERE ".$sUpdWhere;
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				
				$isLoadModule=true;
			}
		}
	}
}else if($sMode=="request_cancel"){
	$sReqId = getQS("request_id");
	if($sReqId=="" || $sReqId=="{NEW}"){
		$aRes["msg"] = "No request_id provide\r\nไม่พบ Request ID ที่ระบุ";
	}else{
		$query = "UPDATE i_stock_request_list SET request_status='CC' WHERE request_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sReqId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$query = "UPDATE i_stock_request_item SET request_item_status='CC' WHERE request_id=?";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("s",$sReqId);
				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;
					}else{
						$aRes["msg"] = "Request has been cancelled but item is not please try again.";
					}
				}
			}else{
				$aRes["msg"] = "Error please try again or refresh page";
			}
		}else{
			$aRes["msg"] = "Error please try again or refresh page";
		}
	}
}else if($sMode=="request_submit"){
	$sReqId = getQS("request_id");
	if($sReqId=="" || $sReqId=="{NEW}"){
		$aRes["msg"] = "No request_id provide\r\nไม่พบ Request ID ที่ระบุ";
	}else{
		$query = "UPDATE i_stock_request_list SET request_status='1',request_datetime=NOW() WHERE request_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sReqId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				
				$query = "UPDATE i_stock_request_item SET request_item_status='1',updated_date=NOW(),updated_by=? WHERE request_id=? AND request_item_status = '0'";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("ss",$sSid,$sReqId);
				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;
					}else{
						$aRes["msg"] = "Request has been updated but item is not please try again.";
					}
				}
				
			}else{
				$aRes["msg"] = "Error please try again or refresh page";
			}
		}else{
			$aRes["msg"] = "Error please try again or refresh page";
		}
	}
}else if($sMode=="request_item_add"){
		$sInsCol = "request_item_status,request_item_no,".$sInsCol;
		$sInsVal = "0,@reqitemno:=IFNULL(MAX(request_item_no),0)+1,".$sInsVal;
		$sParam .= "s";
		$sReqId = getQS("request_id");
		array_push($aUpdData,$sReqId);
		$query ="INSERT INTO i_stock_request_item (".$sInsCol.") 
		SELECT ".$sInsVal." FROM i_stock_request_item WHERE request_id = ?";
		
		//$query ="INSERT INTO i_stock_request_list (".$sInsCol.") VALUES (".$sInsVal.");";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$query =" 
				SELECT ISRI.request_id,request_item_no,ISRI.supply_code,request_supply_note,request_amt,request_item_price,request_total_price,request_item_status,supply_name,supply_unit FROM i_stock_request_item ISRI
				LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISRI.supply_code
				WHERE ISRI.request_id = ? AND ISRI.supply_code=?";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("ss",$aPost["request_id"],$aPost["supply_code"]);
				if($stmt->execute()){
					$stmt->bind_result($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$request_item_price,$request_total_price,$request_item_status,$supply_name,$supply_unit);
					while($stmt->fetch()){
						$aRes["res"] = "1";
						$aRes["request_item_no"] = $request_item_no;
						$aRes["msg"] = getRequestItemRow($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$request_item_price,$request_total_price,$request_item_status,$supply_name,$supply_unit);
					}
					
				}else{
					$aRes["msg"] = "Get ID Failed but already inserted. Please Refresh Page";
				}
			}else{
				//Not Success
				$aRes["msg"] = "There are an error while recording your data. Please try again.";
			}
		}else{
			//Not Success
			$aRes["msg"] = "Supply Code already added";
		}	
}else if($sMode=="request_item_remove"){
	$sReqId = getQS("request_id");
	$sReqSupCode = getQS("supply_code");

	if($sReqId=="" || $sReqId=="{NEW}" || $sReqSupCode==""){
		$aRes["msg"] = "No request_id provide\r\nไม่พบ Request ID ที่ระบุ";
	}else{
		$query = "DELETE FROM i_stock_request_item WHERE request_id=? AND supply_code=? ";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sReqId,$sReqSupCode);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				
			}else{
				$aRes["msg"] = "Error please try again or refresh page";
			}
		}else{
			$aRes["msg"] = "Error please try again or refresh page";
		}
	}
}else if($sMode=="request_item_show_add"){
	$aFreeList=array('supply_code','request_supply_note','request_amt');
	$sReqId=getQS("request_id");
	$sInsCol="request_id,request_item_show,request_item_no,updated_by,updated_date";
	$sInsVal="?,1,IFNULL(MAX(request_item_no),0)+1,?,NOW()";
	$sParam="ss";
	$aInput=array($sReqId,$sSid);

	foreach ($aColList as $iInd => $sCol) {
		if(isset($aPost[$sCol])) {
			$sInsCol.=",".$sCol;
			$sInsVal.=",?";
			$sParam.="s";
			$aInput[]=$aPost[$sCol];
		}
	}

	$sParam.="s";
	$aInput[]=$sReqId;


	$query="INSERT INTO i_stock_request_show_item(".$sInsCol.") 
	SELECT ".$sInsVal." FROM i_stock_request_show_item WHERE request_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aInput);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"]="1";
		}
	}

	/* //Free Item Add....
	if(isset($aPost["request_exact_amt"])){

		$iFreeAmt = $aPost["request_exact_amt"];
		if($iFreeAmt*1>0){
			$query="INSERT INTO i_stock_request_show_item(request_id,request_item_show,request_item_no,updated_by,updated_date,supply_code,request_supply_note,request_amt)
			SELECT ?,'0',IFNULL(MAX(request_item_no),0)+1,?,NOW(),?,?,? FROM i_stock_request_show_item WHERE request_id=?;";
			$stmt = $mysqli->prepare($query);

			$aInput=array($sReqId,$sSid);
			array_push($aInput, $aPost["supply_code"]);
			array_push($aInput, $aPost["request_supply_note"]);
			array_push($aInput, $iFreeAmt);
			array_push($aInput, $sReqId);

			$stmt->bind_param("ssssss",...$aInput);
			if($stmt->execute()){
				$iAffRow =$stmt->affected_rows;
				if($iAffRow > 0) {
					$aRes["res"]="1";
				}
			}		
		}
	}
	*/
}else if($sMode=="request_item_show_remove"){
	$sReqId=getQS("request_id");
	$sRowNo=getQS("request_item_no");
	$sSupCode=getQS("supply_code");

	$query="DELETE FROM i_stock_request_show_item WHERE request_id=? AND request_item_no=? AND supply_code=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sReqId,$sRowNo,$sSupCode);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"]="1";
		}
	}
}else if($sMode=="request_item_show_hide"){
	$sReqId=getQS("request_id");
	$sRowNo=getQS("request_item_no");
	$sChk=getQS("request_item_show");
	$sItemNo=getQS("request_item_no");
	$sSupCode=getQS("supply_code");

	$query = "UPDATE i_stock_request_show_item SET 
	request_item_show = ? ,updated_by=? ,updated_date=NOW()
	WHERE request_id=? AND request_item_no=? AND supply_code=?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss",$sChk,$sSid,$sReqId,$sRowNo,$sSupCode);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
		}
	}
}else if($sMode=="request_item_show_submit"){
	$sReqId = getQS("request_id");
	if($sReqId=="" || $sReqId=="{NEW}"){
		$aRes["msg"] = "No request_id provide\r\nไม่พบ Request ID ที่ระบุ";
	}else{
		$query = "UPDATE i_stock_request_list SET request_status='1',request_datetime=NOW() WHERE request_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sReqId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRows=array();
				$aItems=array();

				$query="SELECT request_id,request_item_no,ISRSI.supply_code,request_unit,bulk_unit,convert_amt,request_item_show,request_supply_note,request_amt,request_exact_amt,discount_before_vat,discount_before_vat_baht,request_vat,discount_after_vat,discount_after_vat_baht,request_item_price,request_item_price_discount,request_item_price_vat,request_item_price_final,request_total_price,request_total_price_discount,request_total_price_vat,request_total_price_final
				FROM i_stock_request_show_item ISRSI
				LEFT JOIN i_stock_master ISM
				ON ISM.supply_code = ISRSI.supply_code

				WHERE ISRSI.request_id=? ORDER BY ISRSI.supply_code";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("s",$sReqId);
				if($stmt->execute()){
				  $result = $stmt->get_result();
				  while($row = $result->fetch_assoc()) {
				    $sSupCode="";
				    $iAmt = (($row["request_amt"]*1)+($row["request_exact_amt"]*1));
				    $sSupCode = $row["supply_code"];

				    if($row["bulk_unit"] == $row["request_unit"] && $row["bulk_unit"] != ""){
				    	$iAmt = $iAmt*$row["convert_amt"];
				    }

				    
				    $aItems[$sSupCode]["amt"]= (isset($aItems[$sSupCode]["amt"])?$aItems[$sSupCode]["amt"]:0)+$iAmt;
				    $aItems[$sSupCode]["name"]=$row["request_supply_note"];
				    $aItems[$sSupCode]["vat"]=$row["request_vat"];
				    $aItems[$sSupCode]["final_price"]=(isset($aItems[$sSupCode]["final_price"])?$aItems[$sSupCode]["final_price"]:0)+$row["request_total_price_final"];
				  }
				}
				$stmt->close();
				
				$query="INSERT INTO i_stock_request_item(request_id,request_item_no,supply_code,request_supply_note,request_amt,request_vat,request_item_price,request_total_price,request_total_price_vat,request_item_status,updated_by,updated_date) 
				SELECT ?,IFNULL(MAX(request_item_no),0)+1,?,?,?,?,?,?,?,'1',?,NOW() FROM i_stock_request_item WHERE request_id=?;";
				$stmt = $mysqli->prepare($query);
				foreach ($aItems as $sSupCode => $aRow) {

					$itemPrice = $aRow["final_price"]/$aRow["amt"];
					$totalPrice = (($aRow["final_price"]*100)/(100+$aRow["vat"]));
					$aTemp=array("request_id"=>$sReqId,"supply_code"=>$sSupCode,"request_supply_note"=>$aRow["name"],"request_amt"=>$aRow["amt"],"request_vat"=>$aRow["vat"],"request_item_price"=>$itemPrice,"request_total_price"=>$totalPrice,"request_total_price_vat"=>$aRow["final_price"],"updated_by"=>$sSid,"where_request_id"=>$sReqId);
					$aTemp=array_values($aTemp);
					$stmt->bind_param("ssssssssss",...$aTemp);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0) {
							$aRes["res"]="1";
						}
					}
				}


			}else{
				$aRes["msg"] = "Error please try again or refresh page";
			}
		}else{
			$aRes["msg"] = "Error please try again or refresh page";
		}
	}
}


$mysqli->close();
if($isEcho=="1"){
	echo($sHtml);
}else{
	$sTemp=json_encode($aRes);
	echo($sTemp);	
}

?>