<?
include_once("in_session.php");
include_once("in_php_function.php");

?>
<div id='divEU' class='fl-wrap-col' style="background-color:white">
	<div class='fl-wrap-row h-30 bg-head-1'>
		<div class='fl-fix w-100 lh-30'>LAB REPORT</div>
		<div class='fl-fill'></div>
	</div>
	<div class='fl-wrap-row h-30 f-border'>
		<div class='fl-fix w-150 fl-mid'>ค้นหา UID / UIC</div>
		<div class='fl-fix w-250 fl-mid'><input id='txtUidSearch' class='w-fill h-25' placeholder="PXX-XXXXX or กกXXXXXX" /></div>
		<div class='fl-fix w-50 fl-mid'><input id='btnSearch' class='h-25' type='button' value='Find' /></div>
		<div class='fl-fill fs-small lh-15'>**UID - PXX-XXXXX<br/>**UIC - กกXXXXXX</div>
	</div>
	<div class='hideme'>
		<form id='divCustomLab' method="POST" action="../weclinic/lab/custom_lab_report.php" target="_blank">

			<input type='hidden' name='uid' value='' />
			<input type='hidden' name='oid' value='' />
			<input type='hidden' name='collect_date' value=''/>
			<input type='hidden' name='collect_time' value=''/>
			<input type='hidden' name='lablist[]' value='HCV_VL'/>
			<input type='hidden' name='lablist[]' value='HCV_VL_DT'/>
			<input type='hidden' name='s_id' value=''/>
			<input type='hidden' name='printid' value=''/>

		</form>
	</div>
	<div class='fl-wrap-row h-30 row-color-2'>
		<div class='fl-fix w-80'>UID</div>
		<div class='fl-fix w-80'>UIC</div>
		<div class='fl-fix w-80'>PID</div>
		<div class='fl-fix w-80'>Visit</div>
		<div class='fl-fill'>Name</div>
		<div class='fl-fix w-150'>Order ID</div>
		<div class='fl-fix w-150'>Visit Date</div>
		<div class='fl-fill'>Result</div>
		<div class='fl-fix w-100 fl-mid fw-b'>View Report</div>
	</div>
	<div id='divSearchResult' class='fl-wrap-col fs-smaller fl-auto'>		
	</div>
	<div id='divSearchResult-loader' class='fl-wrap-col fl-mid' style='display:none'>	
		<i class='fa fa-spinner fa-spin fa-3x'></i>	
	</div>
</div>

<script>
	$(function(){
		$("#divEU #btnSearch").off("click");
		$("#divEU #btnSearch").on("click",function(){
			sTxt=$("#divEU #txtUidSearch").val().trim();
			if(sTxt==""){ 
				$("#divEU #txtUidSearch").notify("กรุณากรอก UID หรือ UIC เพื่อทำการค้นหา");
				return;
			}
			sURL="custom_lab_report_list.php?txt="+sTxt;
			startLoad($("#divEU #divSearchResult"),$("#divEU #divSearchResult-loader"));
			$("#divEU #divSearchResult").load(sURL,function(){
				endLoad($("#divEU #divSearchResult"),$("#divEU #divSearchResult-loader"));
			});
		});

		$("#divEU #divSearchResult .btnviewpdf").off("click");
		$("#divEU #divSearchResult").on("click",".btnviewpdf",function(){
			objRow=$(this).closest(".lab-row");
			sOrderId=$(objRow).attr("data-orderid");
			sColD=$(objRow).attr("data-coldate");
			sColT=$(objRow).attr("data-coltime");
			sSid=$(objRow).attr("data-sid");
			sUid=$(objRow).attr("data-uid");
			$("#divEU #divCustomLab input[name='uid']").val(sUid);
			$("#divEU #divCustomLab input[name='oid']").val(sOrderId);
			$("#divEU #divCustomLab input[name='collect_date']").val(sColD);
			$("#divEU #divCustomLab input[name='collect_time']").val(sColT);
			$("#divEU #divCustomLab input[name='s_id']").val(sSid);
			$("#divEU #divCustomLab input[name='printid']").val(sSid);
			$("#divEU #divCustomLab").submit();

		});

	});

</script>