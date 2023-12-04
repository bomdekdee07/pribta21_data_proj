<?
	include("in_session.php");
include_once("in_php_function.php");
	include("in_db_conn.php");
	include_once("in_setting_row.php");
	$isOpt = getQS("opt");
	$sSecId = getQS("secid");

	$query ="SELECT section_id,ISP.page_id,IPL.page_title,ISP.page_allow,ISP.start_date,ISP.stop_date,page_seq,is_admin FROM i_section_permission ISP
		LEFT JOIN i_page_list IPL 
		ON IPL.page_id = ISP.page_id
		WHERE section_id=? ORDER BY section_id,page_seq";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sSecId );
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($section_id,$page_id,$page_title,$page_allow,$start_date,$stop_date,$page_seq,$is_admin);
	  while ($stmt->fetch()) {
	  	if($isOpt=="1"){
	  		$sHtml.="<option data-sid='".$page_id."'>".$page_title."</option>";
	  	}else{
	  		$sHtml.= getSecPermRow($section_id,$page_id,$page_title,$page_allow,$start_date,$stop_date,$page_seq,$is_admin);
	  	}
	  }
	}
	$mysqli->close();

	echo($sHtml);	
?>