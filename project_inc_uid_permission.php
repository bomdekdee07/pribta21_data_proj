<?
/* Project UID Visit Main  */
include("in_session.php");
include_once("in_php_function.php");

$sID = isset($_SESSION["s_id"])?$_SESSION["s_id"]:"";
$sProjid = getQS("projid");

$msg_not_allow_view = "<center>You do not have permission to view.</center>";

$proj_auth = array();

//echo "$sID, $sProjid " ;
if($sID !="" && $sProjid != ""){
include("in_db_conn.php");

		$query ="SELECT * FROM p_staff_auth
		WHERE s_id =? AND proj_id=? ";
		$stmt = $mysqli->prepare($query);
	  $stmt->bind_param('ss',$sID, $sProjid);

		if($stmt->execute()){
			$result = $stmt->get_result();
			while($row = $result->fetch_assoc()) {
        $proj_auth = $row;
			}
		}
	  $stmt->close();

}

$proj_auth['allow_view'] = isset($proj_auth['allow_view'])?$proj_auth['allow_view']:'0';
$class_auth = "";
foreach($proj_auth as $key=>$val){
	if($val == 1)
	$class_auth .= " $key ";
}
//echo "$class_auth";
//$proj_auth["allow_view"] = 1;
?>
