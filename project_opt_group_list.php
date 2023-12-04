<?
include("in_session.php");
include_once("in_php_function.php");

$sProjid = (isset($sProjid))?$sProjid:"";
if($sProjid == ""){
	$sProjid = getQS("projid");
}


if($sProjid==""){

}else{
	include("in_db_conn.php");

	//$sOpt = "<option value=''>All Group</option>";
	$sOpt = "";

	$query ="SELECT proj_group_id, proj_group_name FROM p_project_group
	WHERE proj_id=? ORDER BY proj_group_seq asc";


	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sProjid);

	if($stmt->execute()){
	  $stmt->bind_result($proj_group_id, $proj_group_name );
	  while ($stmt->fetch()) {
	  	$sOpt.="<option value='$proj_group_id'>[$proj_group_id] $proj_group_name</option>";
	  }
	}
	$mysqli->close();

	if($sOpt == "")
	$sOpt.="<option value=''>No Group</option>";



	echo($sOpt);
}


?>
