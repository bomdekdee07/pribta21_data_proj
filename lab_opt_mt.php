<?
include("in_session.php");
include_once("in_php_function.php");


include("in_db_conn.php");

$sOpt = "<option value=''>Please Select</option>";

$query ="SELECT s_id,s_name,license_lab FROM p_staff WHERE license_lab LIKE 'MT%';";
$stmt = $mysqli->prepare($query);

if($stmt->execute()){
  $stmt->bind_result($s_id,$s_name,$license_lab );
  while ($stmt->fetch()) {
  	$sOpt.="<option value='".$s_id."'>".$s_name." [".$license_lab."]"."</option>";
  }
}
$mysqli->close();

echo($sOpt);	



?>


