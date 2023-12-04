<?
include_once("in_php_function.php");
$sReqId=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sDocId=getQS("doctype");

?>
<div id='divPRM' class='fl-wrap-col' data-reqid='<? echo($sReqId); ?>'>
	<div class='fl-fix h-30 fw-b fl-mid'>Document Note</div>
	<div class='fl-wrap-row fml-10 fmr-10'>
		<textarea class='w-fill h-100'></textarea>
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fill'></div>
		<div id='btnPrintPR' class='fabtn f-border fl-fix w-100 fl-mid'>Print PR</div>
		<div class='fl-fill'></div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#divPRM #btnPrintPR").off("click");
		$("#divPRM #btnPrintPR").on("click",function(){
			sUrl="purchase_req_pdf.php?reqid="+$("#divPRM").attr("data-reqid");
			window.open(sUrl);
		});
	});
</script>