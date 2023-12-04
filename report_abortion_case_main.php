<?
    include("in_session.php");
    include_once("in_php_function.php");

    $sToday=date("Y-m-d"); // format3
?>

<div id='divhcv_paotang_case_data' class='fl-wrap-col'>
	<div class='fl-wrap-row h-30 row-color-2'>
		<div class='fl-fix w-80 fl-mid'>Date : </div>
		<div class='fl-fix w-150 fl-mid'><input id='txtVdate' class='w-100 h-25' value='<? echo($sToday); ?>' /></div>
		<div id='btnViewSum' class='fl-fix w-50 fl-mid fabtn '><i class='fa fa-search' ></i> </div>
		<div class='fl-fill'> </div>
	</div>
	<div id='divabortionCase' class='fl-wrap-col'>
		<? $_GET["vdate"]=$sToday; include("report_abortion_case_detail.php"); ?>
	</div>
	<div id='divabortionCase-loader' class='fl-wrap-col fl-mid' style='display:none'><i class='fa fa-spinner fa-spin fa-4x'></i></div>
</div>

<script>
	$(document).ready(function(){
		$("#divhcv_paotang_case_data #txtVdate").datepicker({dateFormat:"yy-mm-dd",
        changeYear:true,
        changeMonth:true,
        maxDate: 0-1});

		$("#divhcv_paotang_case_data #btnViewSum").off("click");
		$("#divhcv_paotang_case_data #btnViewSum").on("click",function(){
			sVDate=$("#divhcv_paotang_case_data #txtVdate").val().trim();
			if(sVDate==""){
				$("#divhcv_paotang_case_data #txtVdate").notify("Please select date.");
				return;
			}else{
				startLoad($("#divhcv_paotang_case_data #divabortionCase"),$("#divhcv_paotang_case_data #divabortionCase-loader"));
				$("#divhcv_paotang_case_data #divabortionCase").load("report_abortion_case_detail.php?vdate="+sVDate,function(){
					endLoad($("#divhcv_paotang_case_data #divabortionCase"),$("#divhcv_paotang_case_data #divabortionCase-loader"));
				});
			}
		});

		$("#divhcv_paotang_case_data #txtVdate").on("change",function(){
			$("#divhcv_paotang_case_data #btnViewSum").trigger("click");
		});
	});
</script>