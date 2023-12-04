<?
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate = getQS("coldate");
$sColTime = urldecode(getQS("coltime"));
$sRTarget=getQS("rtarget");
$sModule=getQS("modulename");
$isNoUid=getQS("nouid");
$sLoadR=getQS("loadr");
?>
<style>


</style>
<div class='fl-wrap-row main-queue' data-rtarget='<? echo($sRTarget); ?>' data-nouid='<? $isNoUid ?>' >
	<div class='fl-wrap-col left-bar' style='max-width:200px;max-width:20%;background-color:white '>
		<div class='fl-fix h-25 fl-mid' style='background-color: #00D9D9'>
			<? echo($sModule); ?>
		</div>
		<div class='fl-fill'>
			<? include("queue_main.php"); ?>
		</div>
		<div class='fl-wrap-col h-l'>
			<? //include("patient_inc_search_bar.php"); ?>
		</div>
	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div class='fl-wrap-col right-bar'>
		<? if($sLoadR=="1") include($sRTarget); ?>
	</div>
	<div class='fl-wrap-col right-bar-load' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>


</div>
<!-- div class='fl-wrap-row focus-pane'>
	TESTER
</div -->

<script>
	$(document).ready(function(){
		$(".left-bar .q-row").unbind("click");
		$(".left-bar").on("click",".q-row",function(){
			sUid=$(this).attr("data-uid");
			sColDate=$(this).attr("data-coldate");
			sColTime=$(this).attr("data-coltime");
			loadRightBar(sUid,sColDate,sColTime);
		});


		function loadRightBar(sUid,sColDate,sColTime){
			sNoUid = $(".main-queue").attr(".data-nouid");
			if(sUid=="" && sNoUid=="1"){
				$.notify("No UID available");
				return;
			}
			startLoad($(".main-queue .right-bar"),$(".main-queue .right-bar-load"));
			sTarget = $(".main-queue").attr("data-rtarget");
			sUrl=sTarget+"?uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime;
			$(".main-queue .right-bar").load(sUrl,function(){
				endLoad($(".main-queue .right-bar"),$(".main-queue .right-bar-load"));
			});
		}
		$(".left-bar .btncallq").unbind("click");
		$(".left-bar").on("click",".btncallq",function(){
			sQueue=$(this).attr("data-queue");
			sUid=$(this).attr("data-uid");
			sColDate=$(this).attr("data-coldate");
			sColTime=$(this).attr("data-coltime");
			setCurQ(sQueue,sUid);
			loadRightBar(sUid,sColDate,sColTime);
		});

		$(".left-bar .btnviewq").unbind("click");
		$(".left-bar").on("click",".btnviewq",function(){
			sQueue=$(this).attr("data-queue");
			sUid=$(this).attr("data-uid");
			sColDate=$(this).attr("data-coldate");
			sColTime=$(this).attr("data-coltime");
			loadRightBar(sUid,sColDate,sColTime);
		});

		$(".left-bar .btncurq").unbind("click");
		$(".left-bar").on("click",".btncurq",function(){
			sQueue=$(this).attr("data-queue");
			sUid=$(this).attr("data-uid");

			loadRightBar(sUid,sColDate,sColTime);
		});
	});

</script>