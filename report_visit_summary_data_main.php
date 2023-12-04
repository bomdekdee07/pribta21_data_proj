<?
include("in_session.php");
include_once("in_php_function.php");

$sToday=date("Y-m-d");


?>
<div id='divRVSM' class='fl-wrap-col'>
	<div class='fl-wrap-row h-30 row-color-2'>
		<div class='fl-fix w-80 fl-mid'>Date : </div>
		<div class='fl-fix w-150 fl-mid'><input id='txtVdate' class='w-100 h-25' value='<? echo($sToday); ?>' /></div>
		<div id='btnViewSum' class='fl-fix w-50 fl-mid fabtn '><i class='fa fa-search' ></i> </div>
		<div class='fl-fill'> </div>
	</div>
	<div id='divRVSContent' class='fl-wrap-col'>
		<? $_GET["vdate"]=$sToday; include("report_visit_summary_data.php"); ?>
	</div>
	<div id='divRVSContent-loader' class='fl-wrap-col fl-mid' style='display:none'><i class='fa fa-spinner fa-spin fa-4x'></i></div>
</div>

<script>
	$(document).ready(function(){
		$("#divRVSM #txtVdate").datepicker({dateFormat:"yy-mm-dd",
        changeYear:true,
        changeMonth:true,
        maxDate: 0-1});
		$("#divRVSM #btnViewSum").off("click");
		$("#divRVSM #btnViewSum").on("click",function(){
			sVDate=$("#divRVSM #txtVdate").val().trim();
			if(sVDate==""){
				$("#divRVSM #txtVdate").notify("Please select date.");
				return;
			}else{
				startLoad($("#divRVSM #divRVSContent"),$("#divRVSM #divRVSContent-loader"));
				$("#divRVSM #divRVSContent").load("report_visit_summary_data.php?vdate="+sVDate,function(){
					endLoad($("#divRVSM #divRVSContent"),$("#divRVSM #divRVSContent-loader"));
				});
			}
		});

		$("#divRVSM #txtVdate").on("change",function(){
			$("#divRVSM #btnViewSum").trigger("click");
		});
	});
</script>

