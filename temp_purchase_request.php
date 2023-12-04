<?

?>

<div id='divReq' class='fl-wrap-col'>
	<div class='fl-wrap-row'>
		<div class='fl-fix w-300'>นำของเข้า ไม่มี Purchase Request</div>
		<div class='fl-fix w-100'></div>
		<div class='fl-fix w-100'><input id='btnView' type='button' value='Submit' /></div>
		<div class='fl-fill'>Request</div>
	</div>
	<div class='fl-wrap-row'>
		<div class='fl-fix w-300'>นำของเข้า ด้วย Purchase Request</div>
		<div class='fl-fix w-100'><input id='txtReqId' class='fill-box' /></div>
		<div class='fl-fix w-100'><input id='btnViewReqId' type='button' value='Submit' /></div>
		<div class='fl-fill'>Request</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$("#divReq #btnView").off("click");
		$("#divReq #btnView").on("click",function(){

			window.open("ex.php?file=supply_req_inc_main");
		});
		$("#divReq #btnViewReqId").off("click");
		$("#divReq #btnViewReqId").on("click",function(){
			sReqId = $("#txtReqId").val();
			window.open("ex.php?file=purchase_req_inc_main&request_id="+sReqId);
		});
	});
</script>