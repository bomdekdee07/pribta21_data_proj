<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sMode =getQS("u_mode");
$sOpt = getQS("opt");
$aRes=array();
$sSid=getSS("s_id");
include("array_post.php");
include("in_db_conn.php");
$isEcho=getQS("echo");
$aRes["res"] = "0";
$sHtml = "";

if($sMode=="doc_list"){
	$query ="SELECT clinic_id,doc_code,doc_name,doc_template_file,doc_status FROM i_doc_master_list WHERE clinic_id =? ORDER BY doc_code";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$aPost["clinic_id"]);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$doc_code,$doc_name,$doc_template_file,$doc_status); 
	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sHtml .= "<option value='".$clinic_id."'>".$doc_name."</option>";
	  	}else{
	  		$sHtml.=getDocumentList($clinic_id,$doc_code,$doc_name,$doc_template_file,$doc_status);
	  	}
	  	
	  }
	}
}else if($sMode=="doc_master_find"){
	$query ="SELECT clinic_id,doc_code,doc_name,doc_template_file,doc_status FROM i_doc_master_list WHERE clinic_id =? AND doc_code=? ORDER BY doc_code";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$aPost["clinic_id"],$aPost["doc_code"]);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$doc_code,$doc_name,$doc_template_file,$doc_status); 
	  while ($stmt->fetch()) {
	  	$aRes["res"] = "1";
	  	$aRes["clinic_id"] = $clinic_id;
	  	$aRes["doc_name"] = $doc_name;
	  	$aRes["doc_code"] = $doc_code;
	  	$aRes["doc_template_file"] = $doc_template_file;
	  	$aRes["doc_status"] = $doc_status;
	  }
	}
	if($aRes["res"]!="1"){
		$aRes["msg"] = "Error, can't find record";
	}
}else if($sMode=="doc_add"){
	$query ="INSERT INTO i_doc_master_list (".$sInsCol.") VALUES (".$sInsVal.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
			$aRes["msg"] = getDocumentList($aPost["clinic_id"],$aPost["doc_code"],$aPost["doc_name"],$aPost["doc_template_file"],$aPost["doc_status"]);

		}else{
			//Not Success
			$aRes["res"] = "0";
			$aRes["msg"] = "Document Code already Exists. Please try different key";
			
		}
	}
}else if($sMode=="doc_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE i_doc_master_list SET ".$sUpdSet." WHERE ".$sUpdWhere;
		
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}
		}
	}
}else if($sMode=="doc_list_by_sec"){
	$query ="SELECT IDML.clinic_id,?,IDML.doc_code,allow_view,allow_edit,allow_create,allow_delete,s_id,updated_date,doc_name FROM i_doc_master_list IDML
	LEFT JOIN i_doc_section_permission IDSP
	ON IDSP.doc_code = IDML.doc_code
	AND IDSP.clinic_id = IDML.clinic_id

	AND IDSP.section_id=? 
	WHERE IDML.clinic_id =? ORDER BY IDML.doc_code";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$aPost["section_id"],$aPost["section_id"],$aPost["clinic_id"]);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$section_id,$doc_code,$allow_view,$allow_edit,$allow_create,$allow_delete,$s_id,$update_date,$doc_name); 
	  while ($stmt->fetch()) {
	  	$sHtml .= getDocAuthBySecRow($clinic_id,$aPost["section_id"],"",$doc_code,$doc_name,$allow_view,$allow_edit,$allow_create,$allow_delete);
	  }
	}
	if($sHtml!="")  {
		$aRes["res"] = "1";
		$aRes["msg"] = $sHtml;
	}
	else $aRes["msg"] = "No Row Found.";
}else if($sMode=="doc_list_by_type"){
	$query ="SELECT ?,PSS.section_id,section_name,?,allow_view,allow_edit,allow_create,allow_delete,s_id,updated_date,doc_name 
	FROM p_staff_section PSS 
	LEFT JOIN i_doc_section_permission IDSP 
	ON IDSP.section_id = BINARY PSS.section_id 
	AND IDSP.clinic_id = ? 
	AND IDSP.doc_code=?
	LEFT JOIN i_doc_master_list IDML 
	ON IDML.doc_code = IDSP.doc_code 
	AND IDML.clinic_id = IDSP.clinic_id 
	AND IDML.clinic_id=?
	ORDER BY IDSP.section_id";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss",$aPost["clinic_id"],$aPost["doc_code"],$aPost["clinic_id"],$aPost["doc_code"],$aPost["clinic_id"]);
	if($stmt->execute()){
	  $stmt->bind_result($clinic_id,$section_id,$section_name,$doc_code,$allow_view,$allow_edit,$allow_create,$allow_delete,$s_id,$update_date,$doc_name); 
	  while ($stmt->fetch()) {
	  	$sHtml .= getDocAuthBySecRow($clinic_id,$section_id,$section_name,$doc_code,$doc_name,$allow_view,$allow_edit,$allow_create,$allow_delete);
	  }
	}
	if($sHtml!="")  {
		$aRes["res"] = "1";
		$aRes["msg"] = $sHtml;
	}
	else $aRes["msg"] = "No Row Found.";
}else if($sMode=="doc_auth_update"){
	$sKeyCol = "";
	if($aPost["allow"]=="allow_view")			$sKeyCol=" allow_view";
	else if($aPost["allow"]=="allow_edit") 	$sKeyCol=" allow_edit";
	else if($aPost["allow"]=="allow_create")	$sKeyCol=" allow_create";
	else if($aPost["allow"]=="allow_delete")	$sKeyCol=" allow_delete";

	if($sKeyCol==""){
		$aRes["msg"] = "No column 'Allow' Found";
	}else{
		$query = "INSERT INTO i_doc_section_permission (clinic_id,section_id,doc_code,".$sKeyCol;
		$query.=",s_id,updated_date) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE s_id=VALUES(s_id), updated_date=NOW(),".$sKeyCol."=VALUES(".$sKeyCol.");";
		
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss",$aPost["clinic_id"],$aPost["section_id"],$aPost["doc_code"],$aPost["allowvalue"],$sSid);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}else{
				$aRes["msg"] = "No row updated. It might already updated. Please try refresh and try again.";
			}
		}else{
			$aRes["msg"] = $stmt->error;
		}		
	}


}



$mysqli->close();
if($sOpt=="1" || $isEcho=="1"){
	echo($sHtml);
}else{
	$sTemp=json_encode($aRes);
	echo($sTemp);	
}

?>


