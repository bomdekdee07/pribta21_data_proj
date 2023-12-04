<?
	include("in_session.php");
	include_once("in_php_function.php");
	include("in_db_conn.php");
	include("in_setting_row.php");
	$isOpt = getQS("opt");

	$query ="SELECT s_id,s_name,s_remark,s_email,s_tel,s_status,license_lab FROM p_staff ORDER BY s_id";

	$stmt = $mysqli->prepare($query);
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($s_id,$s_name,$s_remark,$s_email,$s_tel,$s_status,$license_lab );
	  while ($stmt->fetch()) {
	  	if($isOpt=="1"){
	  		$sHtml.="<option data-sid='".$s_id."'>".$s_name."</option>";
	  	}else{
	  		$sHtml.= getUserRow($s_id,$s_name,$s_email,$s_tel,$s_status,$license_lab,$s_remark);
	  	}
	  }
	}
	$mysqli->close();

	echo($sHtml);	
?>