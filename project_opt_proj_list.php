<?
include("in_session.php");
include_once("in_php_function.php");


	include("in_db_conn.php");

	$s_id = getSS("s_id");

	$sOpt = "";

	$query ="SELECT PM.proj_id, 
		PM.proj_name 
	FROM p_project PM
	left join p_staff_auth STA on(STA.proj_id = PM.proj_id)
	where PM.is_enable = 1
	and STA.allow_enroll = 1
	and s_id = ?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s", $s_id);
	if($stmt->execute()){
	  $stmt->bind_result($proj_id, $proj_name );
	  while ($stmt->fetch()) {
	  	$sOpt.="<option value='$proj_id'>[$proj_id] $proj_name</option>";
	  }
	}
	$mysqli->close();

	echo($sOpt);



?>
