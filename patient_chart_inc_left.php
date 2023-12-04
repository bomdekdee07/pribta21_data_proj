<div class='fl-wrap-col left-patient-chart' style='background-color:white '>
	<div class='fl-fix' style='min-height: 100px;max-height: 200px;overflow-y:auto'>
		<? include("queue_inc_list.php"); ?>
	</div>
	<div class='fl-wrap-row' style='max-height: 30px'>
		<div class='fl-fill fl-mid fill-box'>
			<input id='txtSearcUid' class='fill-box' size='10' placeholder="P00-00000" />
		</div>
		<div class='fl-fix fl-mid' style='max-width: 30px;min-width: 30px'>
			<i id='btnSearchUid' class='fabtn fa fa-search'></i>
		</div>
	</div>
	<div class='fl-fill pinfo-result fontxsmall' style='overflow-y:auto'>
		
	</div>
	<div class='fl-fill pinfo-result-loading fl-mid' style='display: none'>
		<i class='fa fa-spinner fa-spin fa-4x'></i>
	</div>
</div>

<script>
	$(document).ready(function(){
		$(".left-patient-chart #btnSearchUid").unbind("click");
		$(".left-patient-chart #btnSearchUid").on("click",function(){
			sUid = $(".left-patient-chart #txtSearcUid").val();
			if(sUid==""){
				$(".left-patient-chart #txtSearcUid").notify("Please enter uid");
				return;
			}
			var aData = {u_mode:"find_pinfo_by_uid",u:sUid};

			startLoad($(".left-patient-chart .pinfo-result"),$(".left-patient-chart .pinfo-result-loading"));
			callAjax("patient_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$(".left-patient-chart .pinfo-result").html("Error:"+rtnObj.msg);
				}else if(rtnObj.res=="1"){
					$(".left-patient-chart .pinfo-result").html(rtnObj.msg);
				}
				endLoad($(".left-patient-chart .pinfo-result"),$(".left-patient-chart .pinfo-result-loading"));
				resetRowColor($(".pinfo-result .q-row"));
			});
		});
	});

</script>