<?
include("in_session.php");
include_once("in_php_function.php");
$sId = getSS("s_id");



?>
<style>


</style>
<div class='fl-wrap-row'  data-rtarget='<? echo($sRTarget); ?>' >
	<div class='fl-wrap-col left-bar' style='max-width:200px'>
		<? include("room_inc_list.php"); ?>
	</div>
	<div class='fl-fix toggle-bar'>
		
	</div>
	
	<div class='fl-wrap-col right-bar fl-auto'>
		<? if($sUid!="") include($sRTarget); ?>
	</div>
	<div class='fl-wrap-col right-bar-load' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$(".left-bar .q-row").unbind("click");
		$(".left-bar").on("click",".q-row",function(){
			sRTarget = $(this).attr("")
			sUid=$(this).attr("data-uid");
			sColDate=$(this).attr("data-coldate");
			sColTime=$(this).attr("data-coltime");
			loadRightBar(sUid,sColDate,sColTime);
		});

		$(".right-bar .ddl-visit").unbind("change");
		$(".right-bar").on("change",".ddl-visit",function(){
			let sT=$(this).val();
			let aT=sT.split(" ");
			sUid=$(this).closest(".pinfo").attr("data-uid");
			loadRightBar(sUid,$aT[0],$aT[1]);
		});

		function loadRightBar(sUid,sColDate,sColTime){
			startLoad($(".main-pchart .right-bar"),$(".main-pchart .right-bar-load"));
			sUrl="doctor_main.php?uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime;
			$(".main-pchart .right-bar").load(sUrl,function(){
				endLoad($(".main-pchart .right-bar"),$(".main-pchart .right-bar-load"));
			});
		}

	});

</script>