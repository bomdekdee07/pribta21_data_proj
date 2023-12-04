<?
include_once("in_php_function.php");

$aPost = getAllQS();
include_once("in_setting_row.php");
$sMode = getQS("mode");
$sModule=getQS("module");

$sWHERE = ""; $sCount= ""; $aParaList = array(); $aCol = array();

$aCol["uid"]="";$aCol["uic"]="";$aCol["fname"]="";$aCol["sname"]="";$aCol["date_of_birth"]="";$aCol["citizen_id"]="";$aCol["passport_id"]="";$aCol["country_other"]="";$aCol["tel_no"]="";$aCol["email"]="";$aCol["line_id"]="";;


foreach ($aPost as $iKey => $sVal) {
	if(isset($aCol[$iKey])){
		if($iKey=="citizen_id" || $iKey=="tel_no"){
			$sVal=str_replace("-","",$sVal);
			$iKey=" REPLACE(".$iKey.",'-','')";
		}

		if($sMode=="short"){
			$sWHERE .= (($sWHERE=="")?"":" OR ").$iKey." LIKE ?";	
		}else if($iKey=="fname"){
			if(isset($aPost["sname"])){
				//Set Both fname and user name
				$sWHERE .= (($sWHERE=="")?"":" OR ")."(fname LIKE ? AND sname LIKE ?)";	
			}else{
				$sWHERE .= (($sWHERE=="")?"":" OR ").$iKey." LIKE ?";	
			}
		}else if($iKey=="sname"){
			
			if(isset($aPost["fname"])){
				//Set Both fname and user name
			}else{
				$sWHERE .= (($sWHERE=="")?"":" OR ").$iKey." LIKE ?";	
			}
		}else{
			$sWHERE .= (($sWHERE=="")?"":" OR ").$iKey." LIKE ?";	
		}
		
		$sTemp = "%".$sVal."%";
		array_push($aParaList,$sTemp);
		$sCount .= "s";

	}
}
include("in_db_conn.php");
$sRow = "";
$query = "SELECT uid,uic,fname,sname,nickname,clinic_type,sex,date_of_birth,nation,citizen_id,passport_id,id_address,id_district,id_province,id_zone,id_postal_code,country_other,tel_no,email,line_id,remark FROM patient_info WHERE ".$sWHERE." LIMIT 100";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($sCount,...$aParaList);
if($stmt->execute()){
	$stmt->bind_result($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark);
	while ($stmt->fetch()) {
		if($sMode=="short"){
			$sRow .= getPInfoShortRow($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark,$sModule);
		}else{
			$sRow .= getPInfoFullRow($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark);
		}

	}
}
$mysqli->close();
echo($sRow);
?>