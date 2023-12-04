<?
include_once("in_php_function.php");
$sFile=urldecode(getQS("file"));
$sHtml="";
if(strpos($sFile,".jpg")!==false){
	$sHtml="
	<div id='divZoomControl' class='fl-wrap-col'>
		<div class='fl-wrap-row h-60'>
			<div class='fl-fill'></div>
			<div class='fabtn btnviewzoom fl-fix w-50 fl-mid' data-mode='zoomout'><i class='fas fa-search-minus fa-2x'></i></div>
			<div class='fabtn btnviewzoom fl-fix w-50 fl-mid' data-mode='zoom'><i class='fas fa-search fa-2x'></i></div>
			<div class='fabtn btnviewzoom fl-fix w-50 fl-mid' data-mode='zoomin'><i class='fas fa-search-plus fa-2x'></i></div>
			<div class='fabtn btnviewzoom fl-fix w-50 fl-mid' data-mode='rotate' data-rotate='0'><i class='fas fa-sync-alt fa-2x' ></i></div>
			<div class='fl-fill'></div>
		</div>
		<div class='fl-fill fl-auto'><img id='divZoomArea' src='".$sFile."' width='50%' /></div>
	</div>";
}else{
	$sHtml="<iframe src='".$sFile."' class='h-fill w-fill'></iframe>";
}
?>

<? echo($sHtml); ?>

<script>
	$(function(){
		$("#divZoomControl .btnviewzoom").off("click");
		$("#divZoomControl .btnviewzoom").on("click",function(){
			sMode=$(this).attr("data-mode");
			sW=$("#divZoomArea").attr("width").replace("%","");

			if(sMode=="zoomin"){
				sW=(sW*1)+10;
			}else if(sMode=="zoomout"){
				sW=(sW*1)-10;
			}else if(sMode=="zoom"){
				sW=50;
			}else if(sMode=="rotate"){
				sW=($(this).attr('data-rotate')*1) + 90;
				if(sW==360) sW=0;
			}
			if(sMode=="rotate"){
				$("#divZoomArea").css("transform","rotate("+sW+"deg)");
				$(this).attr('data-rotate',sW);
			}else{
				if(sW<10) sW=10;
				$("#divZoomArea").attr("width",sW+"%");
			}
		});
	});
</script>


