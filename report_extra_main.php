<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
$sSid=getSS("s_id");
$sClinicId=getSS("clinic_id");

$sHtml="";
$query="SELECT IMP.option_code,option_title FROM i_module_permission IMP
LEFT JOIN i_module_option IMO
ON IMO.module_id = IMP.module_id
AND IMO.option_code = IMP.option_code

WHERE IMP.section_id IN (SELECT section_id FROM i_staff_clinic WHERE s_id=? AND clinic_id=?) AND IMP.module_id='REPORT' AND IMP.allow_view = 1 ORDER BY option_title";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sSid,$sClinicId);
if($stmt->execute()){
	$stmt->bind_result($option_code,$option_title);
$aOpt = array();
  while($stmt->fetch()) {

    if(isset($aOpt[$option_code])==false) $sHtml .= "<div class='row-color'><div class='fl-wrap-row fabtn btnreport hmin-30 ' data-url='$option_code' title=''><div class='fl-fill fl-vmid lh-15'>$option_title</div></div></div>";
    $aOpt[$option_code]=true;
  }
}
$mysqli->close();
?>
<div id='divREM' class='fl-wrap-row' style='background-color: white'>
	<div class='fl-wrap-col w-200 left-bar fs-small'>
		<div class='fl-fix h-20 bg-head-1 lh-20'>Report List</div>
		<? echo($sHtml); ?>
	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div id='divREMContent' class='fl-wrap-col right-bar'>
	</div>
	<div id='divREMContent-loader' class='fl-wrap-col fl-mid' style='display:none'>
		<i class='fa fa-spinner fa-spin fa-4x'></i>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#divREM .btnreport").off("click");
		$("#divREM").on("click",".btnreport",function(){
			sUrl=$(this).attr('data-url')+".php";
			startLoad($("#divREM #divREMContent"),$("#divREM #divREMContent-loader"));
			$("#divREM #divREMContent").load(sUrl,function(){
				endLoad($("#divREM #divREMContent"),$("#divREM #divREMContent-loader"));
			});
		});
	});
</script>