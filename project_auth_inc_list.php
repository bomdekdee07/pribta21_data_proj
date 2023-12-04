<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sProjId = getQS("projid");
$sMode =getQS("u_mode");
$sOpt = getQS("opt");
include("in_db_conn.php");
$sList = "";

if($sMode=="projauth-list"){
	$query ="SELECT PSA.s_id,s_name,proj_id,allow_view,allow_enroll,allow_schedule,allow_data,allow_data_log,allow_lab,allow_export,allow_query,allow_delete,allow_data_backdate,allow_admin FROM p_staff_auth PSA
	LEFT JOIN p_staff PS
	ON PS.s_id = PSA.s_id
	WHERE proj_id=?
	ORDER BY PSA.s_id";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sProjId);
	if($stmt->execute()){
	  $stmt->bind_result($s_id,$s_name,$proj_id,$allow_view,$allow_enroll,$allow_schedule,$allow_data,$allow_data_log,$allow_lab,$allow_export,$allow_query,$allow_delete,$allow_data_backdate,$allow_admin ); 


	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sList.="<option value='".$s_id."' >".$s_id."</option>";
	  	}else{
	  		$sList.=getProjAuthList($s_id,$s_name,$proj_id,$allow_view,$allow_enroll,$allow_schedule,$allow_data,$allow_data_log,$allow_lab,$allow_export,$allow_query,$allow_delete,$allow_data_backdate,$allow_admin );
	  	}
	  	
	  }
	}

}else if($sMode=="projauth-list-only"){
	$sClinicId=getQS("projauthid");
	$query ="SELECT projauth_id,projauth_name,projauth_address,projauth_email,projauth_tel,projauth_status,main_projauth_id,old_projauth_id FROM p_projauth WHERE projauth_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sClinicId);
	if($stmt->execute()){
	  $stmt->bind_result($projauth_id,$projauth_name,$projauth_address,$projauth_email,$projauth_tel,$projauth_status,$main_projauth_id,$old_projauth_id ); 
	  while ($stmt->fetch()) {
	  	if($sOpt=="1"){
	  		$sList.="<option value='".$projauth_id."'>".$projauth_name."</option>";
	  	}else{
	  		$sList.=getClinicList($projauth_id,$projauth_name,$projauth_address,$projauth_email,$projauth_tel,$projauth_status,$main_projauth_id,$old_projauth_id);
	  	}
	  	
	  }
	}

}else{

	$query ="SELECT DISTINCT(ISC.projauth_id),PC.projauth_name FROM p_projauth PC
	LEFT JOIN i_staff_projauth ISC
	ON ISC.projauth_id = PC.projauth_id
	LEFT JOIN p_staff PS
	ON PS.s_id = ISC.s_id
	WHERE PS.s_email=? AND ISC.sc_status=1";


	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sEmail);

	if($stmt->execute()){
	  $stmt->bind_result($projauth_id,$projauth_name );
	  while ($stmt->fetch()) {
	  	$sList.="<option value='".$projauth_id."'>".$projauth_name."</option>";
	  }
	}



}
$mysqli->close();
echo($sList);	
?>


