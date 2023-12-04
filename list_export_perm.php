<?
	include("in_session.php");
include_once("in_php_function.php");
	include("in_db_conn.php");
	include_once("in_setting_row.php");
	$isOpt = getQS("opt","0");
	$sSecId = getQS("secid");

	$query ="SELECT section_id,IFP.form_id,form_name_th,allow_view,allow_edit,allow_export,start_date,stop_date FROM i_form_permission IFP LEFT JOIN p_form_list PFL 
	ON PFL.form_id=IFP.form_id WHERE section_id = ?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sSecId );
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($section_id,$form_id,$form_name_th,$allow_view,$allow_edit,$allow_export,$start_date,$stop_date);
	  while ($stmt->fetch()) {
	  	if($isOpt=="1"){
	  		$sHtml.="<option data-sid='".$page_id."'>".$page_title."</option>";
	  	}else{
	  		$sHtml.= getExpPermRow($section_id,$form_id,$form_name_th,$allow_view,$allow_edit,$allow_export,$start_date,$stop_date);
	  	}
	  }
	}
	$mysqli->close();

	echo($sHtml);	
?>