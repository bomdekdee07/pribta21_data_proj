<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
include_once("in_setting_row.php");
$isOpt = getQS("opt");


$sProjId = getQS("projid");
$sHtml = "";
if($sProjId!=""){
	$query ="SELECT proj_id,proj_name,proj_desc,proj_remark,proj_group_amt,proj_pid_format,proj_pid_runing_digit,is_enable
	FROM p_project WHERE proj_id=? ORDER BY proj_id";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sProjId);
}else{
	$query ="SELECT proj_id,proj_name,proj_desc,proj_remark,proj_group_amt,proj_pid_format,proj_pid_runing_digit,is_enable FROM p_project ORDER BY proj_id";

	$stmt = $mysqli->prepare($query);

}
if($stmt->execute()){
  $stmt->bind_result($proj_id,$proj_name,$proj_desc,$proj_remark,$proj_group_amt,$proj_pid_format,$proj_pid_runing_digit,$is_enable);

  while ($stmt->fetch()) {
  	if($isOpt=="1"){
  		$sHtml.="<option value='".$proj_id."' >".$proj_name."</option>";
  	}else{
  		$sHtml.= getProjInfoRow($proj_id,$proj_name,$proj_desc,$proj_remark,$proj_group_amt,$proj_pid_format,$proj_pid_runing_digit,$is_enable);
  	}
  }
}


$mysqli->close();

echo($sHtml);
?>
