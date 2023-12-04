<?
include("in_session.php");
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate = getQS("coldate");
$sColTime = getQS("coltime");

$optVisit=""; $iVCnt=0; $sToday=date("Y-m-d"); $sNow=date("h:i:s");
if($sColDate=="" || $sColTime==""){
	//get Coldate || coltime of today Else create new
	include("in_db_conn.php");

	$query="SELECT site,uid,date_of_visit,time_of_visit FROM k_visit_data WHERE uid=? AND date_of_visit!='0000-00-00' ORDER BY date_of_visit DESC,time_of_visit DESC";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	$sHtml="";

	if($stmt->execute()){
	  $stmt->bind_result($site,$uid,$date_of_visit,$time_of_visit);
	  while ($stmt->fetch()) {
	  	if($sColDate=="") $sColDate=$date_of_visit;
	  	if($sColTime=="") $sColTime=$time_of_visit;

	  	$optVisit.="<option value='$date_of_visit $time_of_visit' title='$site' ".((($date_of_visit." ".$time_of_visit)==($sColDate." ".$sColTime))?"selected":"").">".$date_of_visit." ".$time_of_visit."</option>";
	  	$iVCnt++;

	  }
	}
	$mysqli->close();
	if($optVisit==""){
		//No visit found used to day and now is time to create visit
		$sColDate = $sToday;
		$sColTime = $sNow;


		$optVisit="<option value='$sColDate $sColTime' title='New Visit' >**".$sColDate." ".$sColTime."</option>";
	}
	$_GET["coldate"] = $sColDate;
	$_GET["coltime"] = $sColTime;
}
?>

<div class='fl-wrap-row' style='max-height: 80px;vertical-align: middle'>
	<div class='fl-fill' >
		<? 
			if($sUid!="") include ("lab_inc_patient_info.php"); 
		?>
	</div>
</div>
<div class='fl-wrap-row' style=''>
	<div class='fl-fill'>
	BLA BLA BLA BLA BLA BLA BLA BLA 
	</div>
	<div class='fl-wrap-col div-supply-order fl-auto' >
		<div class='fl-fill'>
			<? if($sUid!="")  include("supply_inc_order_list.php"); ?>
		</div>
		<div class='fl-fill'>
			<? if($sUid!="")  include("supply_inc_summary.php"); ?>
		</div>
		<div class='fl-fill' style='background-color: silver'>

		</div>
	</div>
	<div class='fl-wrap-col fl-fix div-supply-order-loading fl-mid' style='display:none'>
		<i class='fa fa-spinner fa-spin fa-5x'></i>
	</div>
</div>