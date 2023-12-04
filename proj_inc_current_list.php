<?
include_once("in_session.php");
include_once("in_php_function.php");


$sUid = getQS("uid");
$sToday=date("Y-m-d");
$aProj = array();

include("in_db_conn.php");
$query = "SELECT PPUL.proj_id,proj_name,pid,enroll_date,uid_status,PP.sale_opt_id
FROM p_project_uid_list PPUL 
LEFT JOIN p_project PP
ON PP.proj_id=PPUL.proj_id
WHERE uid=? AND uid_status=1";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
	$stmt->bind_result($proj_id,$proj_name,$pid,$enroll_date,$uid_status,$sale_opt_id);
	while ($stmt->fetch()) {
		$aProj[$proj_id]["pid"]=$pid;
		$aProj[$proj_id]["proj_name"]=$proj_name;
		$aProj[$proj_id]["enroll_date"]=$enroll_date;
		$aProj[$proj_id]["sale_opt_id"]=$sale_opt_id;
	}	
}

$sHtml="";
if($aProj!=""){
	foreach ($aProj as $proj_id => $aT) {
		$sHtml.="<div class='proj-row fabtn fl-wrap-row h-30 row-hover row-color fl-mid-all' data-saleoptid='".$aT["sale_opt_id"]."'>
			<div class='fl-fix w-100'>$proj_id</div>
			<div class='fl-fill'>".$aT["proj_name"]."</div>
			<div class='fl-fix w-150'>".$aT["pid"]."</div>
			<div class='fl-fill'>".$aT["enroll_date"]."</div>
			<div class='fl-fix w-50'>".$aT["sale_opt_id"]."</div>
		</div>";
	}
}

echo($sHtml);
?>

