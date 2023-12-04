<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
include_once("in_setting_row.php");
$isOpt = getQS("opt");

$query ="SELECT form_id,form_name_en,form_name_th,form_version_id FROM p_form_list ORDER BY form_name_th";

$stmt = $mysqli->prepare($query);
$sHtml = "";
if($stmt->execute()){
  $stmt->bind_result($form_id,$form_name_en,$form_name_th,$form_version_id );
  while ($stmt->fetch()) {
  	if($isOpt=="1"){
  		$sHtml.="<option value='".$form_id."' title='$form_id : $form_name_en'>".$form_name_th."</option>";
  	}else{
  		//$sHtml.= getFormRow($form_id,$form_name_en,$form_name_th,$form_version_id);
  	}
  }
}
$mysqli->close();

echo($sHtml);	
?>