<?
include_once("in_session.php");
include_once("in_php_function.php");

include("in_db_conn.php");

$sSid=getSS("s_id");
$sHtml="";

if($sSid==""){

}else{
	$query="SELECT s_name,s_email,s_remark FROM p_staff WHERE s_id=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sSid);
	if($stmt->execute()){
		$stmt->bind_result($s_name,$s_email,$s_remark);
		while ($stmt->fetch()) {
			$sHtml.="<div class='fl-fill fl-vmid row-color fpl-5'>$s_name</div><div class='fl-fill fl-vmid  row-color fpl-5'>$s_email</div><div class='fl-fill fl-vmid  row-color fpl-5'>$s_remark</div>";
		}	
	}
	echo($sHtml);
}


?>