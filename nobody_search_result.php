<?
include_once("in_php_function.php");
$sPref=getQS("prefix");
$sProjId=getQS("projid");
$sKey=getQS("kw");

include('in_db_conn.php');
$query ="SELECT proj_id,uid,pid,screen_date,enroll_date,uid_remark,clinic_id FROM p_project_uid_list WHERE proj_id=? AND (uid LIKE ? OR pid = ?) ;";
$stmt = $mysqli->prepare($query);
$sPhone=$sKey;
$sUid="%".$sKey;
$stmt->bind_param("ss",$projid,$sUid,$sPhone);
$sHtml="";
if($stmt->execute()){
	$stmt->bind_result($proj_id,$uid,$pid,$screen_date,$enroll_date,$uid_remark,$clinic_id);
	 while ($stmt->fetch()) {
	 	$sHtml.="<div class='fl-wrap-row row-color'>
	 		<div class='fl-fix nobody-uid'>
	 			$uid
	 		</div>
	 		<div class='fl-fill'>
	 			$pid
	 		</div>
	 	</div>";
	 	
	 }
	 if($sHtml=="") $sHtml.="<div class='fl-wrap-row' >No result found</div>";
}
$mysqli->close();

echo($sHtml);

?>