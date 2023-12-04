<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");

$sUID = getQS("uid");
$sFormid = getQS("formid");
$sVisitdate = getQS("visitdate");
$sVisittime = getQS("visittime");

if($sVisittime == "") $sVisittime= "00:00:00";

$txt_row = "";


//echo "qs: $sProjid/$sGroupid/$sUID";

if($sUID !="" && $sFormid != ""){
}
else{
   $txt_row = "Missing parameter.";
}

if($txt_row == "") $txt_row = "No Data Found.";


?>


<div class='fl-wrap-row pt-1' id="div_visit_form_data"
  data-uid='<? echo $sUID; ?>' data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>'
	data-visitid='<? echo $sVisitid; ?>' data-visitdate='<? echo $visit_date;?>'>
	<div class='fl-wrap-col px-2 py-1 w-xl fs-s bg-info form-menu'>
		<div class = 'b-txt  text-white'>
      <? echo "
			<div class='h-xs'>[$sVisitid] $visit_name </div>
			<div class='h-xs'>Date: $sVisitdate </div>"
			?>
		</div>
		<div class = 'h-xs b-txt text-white mt-4'>
			Visit Form List
		</div>
		<div class = 'pt-2'>
		 <? echo $txt_row; ?>

		</div>
  </div>
</div> <!-- div_visit_form_data -->


<script>
$(document).ready(function(){
	$('#div_visit_form_data').on("click",".view-form-detail",function(){
		let formid=$(this).attr("data-formid");
		let uid=$("#div_visit_form_data").attr("data-uid");
		let visitdate=$("#div_visit_form_data").attr("data-visitdate");
	//	console.log("visit: "+visitid+"/"+visitdate);
		 view_visit_form_list(visitid, visitdate);
	});
});


</script>
