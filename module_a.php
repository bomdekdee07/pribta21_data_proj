<?
//JENG
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sMode =getQS("u_mode");
$aRes=array();
$sSid=getSS("s_id");

include("array_post.php");
include("in_db_conn.php");
$isOpt=getQS("opt");
$isEcho=getQS("echo");
$sModId = getQS("module_id");


$aRes["res"] = "0";
$aRes["msg"] = "";
$sHtml = "";
$isLoadModule=false;
$isLoadModulePerm=false;
if($sMode=="module_list"){
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
	  		$sHtml.=getModuleList($module_id,$module_title,$module_color,$module_icon);
	  	}
	  }
	}
}else if($sMode=="module_item"){
	$isLoadModule = true;
}else if($sMode=="module_add"){
	$query ="INSERT INTO i_module (".$sInsCol.") VALUES (".$sInsVal.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
			$aRes["msg"]=getModuleList($aPost["module_id"],$aPost["module_title"],$aPost["module_color"],$aPost["module_icon"]);
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while recording your data. Please try again.";
		}
	}else{
		//Not Success
		$aRes["msg"] = "Document Code already Exists. Please try different key";
	}
}else if($sMode=="module_delete"){
	$sModId = getQS("module_id");
	$query ="DELETE FROM i_module WHERE module_id = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sModId);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while delete the. Please try again.";
		}
	}
}else if($sMode=="module_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE i_module SET ".$sUpdSet." WHERE ".$sUpdWhere;
		
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
}else if($sMode=="module_perm_add"){
	$query ="INSERT INTO i_module_permission (".$sInsCol.") VALUES (".$sInsVal.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = "1";
			$aRes["msg"]=getModulePermList($aPost["module_id"],$aPost["section_id"],$aPost["option_code"],$aPost["allow_view"],$aPost["allow_insert"],$aPost["allow_update"],$aPost["allow_delete"],$aPost["is_admin"]);
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while recording your data. Please try again.";
		}
	}else{
		//Not Success
		$aRes["msg"] = "Module with Option Code already Exists.";
	}
}else if($sMode=="module_perm_list"){
	$isEcho="1";
	$sSecId = getQS("section_id");

	$query ="SELECT module_id,section_id,option_code,allow_view,allow_insert,allow_update,allow_delete,is_admin FROM i_module_permission ";
	
	if($sSecId !="") $query.=" WHERE section_id=? ";
	
	$stmt = $mysqli->prepare($query);
	if($sSecId!="") $stmt->bind_param("s",$sSecId);
	if($stmt->execute()){
	  $stmt->bind_result($module_id,$section_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin); 
	  while ($stmt->fetch()) {
  		$sHtml.=getModulePermList($module_id,$section_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin);
	  }
	}
}else if($sMode=="module_perm_item"){
	$isLoadModulePerm=true;
}else if($sMode=="module_perm_update"){
	if(isset($aPost["colpk"])==false){
		$aRes["res"] = "0";
		$aRes["msg"] = "No pk provide";
	}else{
		$query = "UPDATE i_module_permission SET ".$sUpdSet." WHERE ".$sUpdWhere;
		
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($sParam,...$aUpdData);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$isLoadModulePerm=true;
			}
		}
	}
}else if($sMode=="module_perm_delete"){
	$sModId = getQS("module_id");
	$sSecId = getQS("section_id");
	$sOptCode = getQS("option_code");


	$query ="DELETE FROM i_module_permission WHERE module_id = ? AND section_id=? AND option_code=?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sModId,$sSecId,$sOptCode);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while delete the. Please try again.";
		}
	}
}else if($sMode=="option_code_list"){
	$query ="SELECT option_code,option_title,is_enable FROM i_module_option ";
	
	if($sModId !="") $query.=" WHERE module_id=? ";
	
	$stmt = $mysqli->prepare($query);
	if($sModId!="") $stmt->bind_param("s",$sModId);
	if($stmt->execute()){
	  $stmt->bind_result($option_code,$option_title,$is_enable); 
	  while ($stmt->fetch()) {
  		$sHtml.=getModuleOptList($sModId,$option_code,$option_title,$is_enable);
	  }
	}
}else if($sMode=="option_code_add"){
	$query ="INSERT INTO i_module_option (".$sInsCol.") VALUES (".$sInsVal.");";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($sParam,...$aUpdData);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
			$aRes["msg"]=getModuleOptList($aPost["module_id"],$aPost["option_code"],$aPost["option_title"],$aPost["is_enable"]);
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while recording your data. Please try again.";
		}
	}else{
		//Not Success
		$aRes["msg"] = "Option Code already Exists. Please try different key";
	}

}else if($sMode=="option_code_delete"){
	$sOptCode=$_POST["option_code"];
	$query ="DELETE FROM i_module_option WHERE module_id=? AND option_code=?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sModId,$sOptCode);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			//Success'
			$aRes["res"] = "1";
		}else{
			//Not Success
			$aRes["msg"] = "There are an error while delete your data. Please try again.";
		}
	}
}else{
	$aRes["msg"]="No mode found";
}

if($isLoadModule){
	$sModuleId = getQS("module_id");
	$query ="SELECT module_id,module_title,module_color,module_icon FROM i_module
	WHERE module_id=? ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sModuleId);
	if($stmt->execute()){
	  $stmt->bind_result($module_id,$module_title,$module_color,$module_icon); 
	  while ($stmt->fetch()) {
	  	$aRes["res"]="1";
	  	$aRes["module_id"]=$module_id;
	  	$aRes["module_title"]=$module_title;
	  	$aRes["module_color"]=$module_color;
	  	$aRes["module_icon"]=$module_icon;
	  	$aRes["msg"]=getModuleList($aRes["module_id"],$aRes["module_title"],$aRes["module_color"],$aRes["module_icon"]);
	  }
	}	
}
if($isLoadModulePerm){
	$sModuleId = getQS("module_id");
	$sSecId = getQS("section_id");
	$sOptCode = getQS("option_code");
	$query ="SELECT module_id,section_id,option_code,allow_view,allow_insert,allow_update,allow_delete,is_admin FROM i_module_permission
	WHERE module_id=? AND section_id=? AND option_code=? ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sModuleId,$sSecId,$sOptCode);
	if($stmt->execute()){
	  $stmt->bind_result($module_id,$section_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin); 
	  while ($stmt->fetch()) {
	  	$aRes["res"]="1";
	  	$aRes["module_id"]=$module_id;
	  	$aRes["section_id"]=$section_id;
	  	$aRes["option_code"]=$option_code;
	  	$aRes["allow_view"]=$allow_view;
	  	$aRes["allow_insert"]=$allow_insert;
	  	$aRes["allow_update"]=$allow_update;
	  	$aRes["allow_delete"]=$allow_delete;
	  	$aRes["is_admin"]=$is_admin;

	  	$aRes["msg"]=getModulePermList($module_id,$section_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin);
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


