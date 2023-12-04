<?
include_once("in_php_function.php");


?>
<div class='fl-wrap-col'>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fix w-100'>Name</div>
		<div class='fl-fix w-200'><input id='txtName' class='fill-box' /></div>
		<div class='fl-fill'></div>
	</div>
	<div class='fl-wrap-row h-40 fl-mid'>
		<div class='fl-fix w-300 fl-mid h-fill lh-15'><i id='btnSearch' class=' fabtn-1 fas fa-search '>
		ค้นหา</i></div>
		<div class='fl-fill'></div>
	</div>
	<div id='divResult' class='fl-wrap-col fl-auto'></div>
	<div id='divResult-loader' class='fl-mid fl-fill' style='display:none'><i class='fa fa-spinner fa-spin fa-3x'></i> </div>
</div>
<script>
	$(function(){
		$("#btnSearch").on("click",function(){
			sTxt=$("#txtName").val();
			//sUrl = "amed_a.php?u_mode=TEST&name="+encodeURIComponent(sTxt);
			sUrl = "amed_a.php?name="+encodeURIComponent(sTxt);
			aData = {name:sTxt};
			startLoad($("#divResult"),$("#divResult-loader"));
			$("#divResult").load(sUrl,function(){
				endLoad($("#divResult"),$("#divResult-loader"));
			});
			/*
	        callAjax(sUrl,aData,function(jRes,retAData){
				$("#divResult").html(jRes);
			});*/
		});
	});
</script>