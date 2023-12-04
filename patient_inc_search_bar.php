<div class='search-bar fl-wrap-col'>
	<div class='fl-wrap-row h-xs row-color-2'>
		<div class='fl-fill fl-mid fill-box'>
			<input id='txtSearcUid' class='fill-box mar-topdown fs-s ' size='10' placeholder="P00-00000" />
		</div>
		<div class='fl-fix fl-mid w-ss' >
			<i id='btnSearchUid' class='fabtn fa fa-search'></i>
		</div>
	</div>
	<div class='fl-wrap-col pinfo-result fontxsmall fl-auto'>
		
	</div>
	<div class='fl-fill pinfo-result-loading fl-mid ' style='display: none'>
		<i class='fa fa-spinner fa-spin fa-4x'></i>
	</div>
</div>


<script>
$(document).ready(function(){

	$(".search-bar #txtSearcUid").unbind("change");
	$(".search-bar #txtSearcUid").on("change",function(){
		sVal = $(this).val();
		if(sVal.length==9){
			$(".search-bar #btnSearchUid").click();
		}
	});

	$(".search-bar #btnSearchUid").unbind("click");
	$(".search-bar #btnSearchUid").on("click",function(){
		sUid = $(".search-bar #txtSearcUid").val();
		if(sUid==""){
			$(".search-bar #txtSearcUid").notify("Please enter uid");
			return;
		}
		var aData = {u_mode:"find_pinfo_by_uid",u:sUid};

		startLoad($(".search-bar .pinfo-result"),$(".search-bar .pinfo-result-loading"));
		callAjax("patient_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res!="1"){
				$(".search-bar .pinfo-result").html("Error:"+rtnObj.msg);
			}else if(rtnObj.res=="1"){
				$(".search-bar .pinfo-result").html(rtnObj.msg);
			}
			endLoad($(".search-bar .pinfo-result"),$(".search-bar .pinfo-result-loading"));

		});
	});
});

</script>