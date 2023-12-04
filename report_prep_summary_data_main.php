<?
include("in_session.php");
include_once("in_php_function.php");

$sToday=date("Y-m-d");


?>
<div id='divPREP' class='fl-wrap-col'>
	<div class='fl-wrap-row h-30 row-color-2'>
		<div class='fl-fix w-80 fl-mid'>Date : </div>
		<div class='fl-fix w-150 fl-mid'><input id='txtVdate' class='w-100 h-25' value='<? echo($sToday); ?>' /></div>
		<div id='btnViewSum' class='fl-fix w-50 fl-mid fabtn '><i class='fa fa-search' ></i> </div>
		<div class='fl-fill'> </div>
	</div>
	<div id='divRVSContent' class='fl-wrap-col'>
		<? $_GET["vdate"]=$sToday; include("report_prep_summary_data.php"); ?>
	</div>
	<div id='divRVSContent-loader' class='fl-wrap-col fl-mid' style='display:none'><i class='fa fa-spinner fa-spin fa-4x'></i></div>
</div>

<script>
	$(document).ready(function(){
		$("#divPREP #txtVdate").datepicker({dateFormat:"yy-mm-dd",
        changeYear:true,
        changeMonth:true,
        maxDate: 0-1});
		$("#divPREP #btnViewSum").off("click");
		$("#divPREP #btnViewSum").on("click",function(){
			sVDate=$("#divPREP #txtVdate").val().trim();
			if(sVDate==""){
				$("#divPREP #txtVdate").notify("Please select date.");
				return;
			}else{
				startLoad($("#divPREP #divRVSContent"),$("#divPREP #divRVSContent-loader"));
				$("#divPREP #divRVSContent").load("report_prep_summary_data.php?vdate="+sVDate,function(){
					endLoad($("#divPREP #divRVSContent"),$("#divPREP #divRVSContent-loader"));
				});
			}
		});

		$("#divPREP #txtVdate").on("change",function(){
			$("#divPREP #btnViewSum").trigger("click");
		});
	});
</script>