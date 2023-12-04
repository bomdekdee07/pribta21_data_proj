<?
	include("in_session.php");
include_once("in_php_function.php");
	include("in_db_conn.php");
	include_once("in_setting_row.php");
	$isOpt = getQS("opt");
	$query ="SELECT section_id,section_name,section_note,section_enable FROM p_staff_section ORDER BY section_id";

	$stmt = $mysqli->prepare($query);
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($section_id,$section_name,$section_note,$section_enable );
	  while ($stmt->fetch()) {
	  	if($isOpt=="1"){
	  		$sHtml.="<option data-secid='".$section_id."' value='$section_id'>".$section_name."</option>";
	  	}else{
	  		$sHtml.= getSecRow($section_id,$section_name,$section_note,$section_enable);
	  	}
	  	
	  }
	}
	$mysqli->close();

	echo($sHtml);	
?>