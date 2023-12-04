<?
//JENG
	include("in_session.php");
	include_once("in_php_function.php");
	$sClinicId = getQS("clinicid");


?>

<div class='fl-wrap-col module-master-list'>
	<div class='fl-wrap-row fs-small fl-mid h-25 row-color-2 row-header'>
		<div class='fl-fix w-50'>
		</div>
		<div class='fl-fix w-200'>
			ID
		</div>
		<div class='fl-fill '>
			Title
		</div>
		<div class='fl-fix w-100'>
			Color
		</div>
		<div class='fl-fix w-100'>
			Icon
		</div>
		<div class='fl-fix w-100'>
			Control
		</div>
	</div>
	<div class='fl-wrap-row fs-small fl-mid h-30 row-color-2 row-header input-header'>
		<div class='fl-fix w-50'>
			<i class='fabtn fas fa-broom fa-2x btnclear'></i>
			<i class='fas fa-spinner fa-spin fa-2x btnclear-loader' style='display:none'></i>
		</div>
		<div class='fl-fix w-200'>
			<input class='w-fill saveinput' data-odata='' data-keyid='module_id' value='' data-pk='1' />
		</div>
		<div class='fl-fill '>
			<input class='fill-box saveinput' data-odata='' data-keyid='module_title' value=''/>
		</div>
		<div class='fl-fix w-100'>
			<input class='fill-box saveinput' data-odata='' data-keyid='module_color' value=''/>
		</div>
		<div class='fl-fix w-100'>
			<input class='fill-box saveinput' data-odata='' data-keyid='module_icon' value=''/>
		</div>
		<div class='fl-fix w-100 fl-mid'>
			<i class='fas fa-plus-square fabtn btnadd fa-2x' data-mode='module_add'></i>
			<i class='fas fa-spinner fa-spin btnadd-loader fa-2x ' style='display:none'></i>
		</div>
	</div>
	<div class='fl-wrap-col row-body fl-auto'>
		<? $_GET["u_mode"] = "module_list"; include("module_a.php"); ?>
	</div>
</div>

<script>
$(document).ready(function(){
	$(".module-master-list .btnadd").off("click");
	$(".module-master-list .btnadd").on("click",function(){
		objCurDiv = $(this).closest(".module-master-list");
		objInp = $(objCurDiv).find(".input-header");

		sModuleId = $(objInp).find(".saveinput[data-keyid='module_id']").val();
		if(sModuleId!=""){
			$(objCurDiv).find(".saveinput[data-keyid='module_id']").val(sModuleId.toUpperCase());
		}

		aData = getDataRow($(".module-master-list .input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");
		startLoad($(".module-master-list .btnadd"),$(".module-master-list .btnadd-loader"));
		callAjax("module_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				if(aData.u_mode=="module_update"){
					objRow = $(".module-master-list .row-body .row-data[data-moduleid='"+aData.module_id+"']");
					$(objRow).after(rtnObj.msg);
					$(objRow).remove();
				}else if(aData.u_mode=="module_add"){
					$(".module-master-list .row-body").prepend(rtnObj.msg);
				}

				$(".module-master-list .btnclear").trigger("click");
			}
			//
			endLoad($(".module-master-list .btnadd"),$(".module-master-list .btnadd-loader"));
		});
	});

	$(".module-master-list .btnedit").off("click");
	$(".module-master-list").on("click",".btnedit",function(){
		sModuleId = $(this).closest(".row-data").attr("data-moduleid");
		aData = {u_mode:"module_item",module_id:sModuleId};
		$(".module-master-list .btnadd").attr("data-mode","module_update");
		startLoad($(".module-master-list .btnclear"),$(".module-master-list .btnclear-loader"));
		callAjax("module_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res=="1"){
				$(".input-header .saveinput[data-pk='1']").attr("readonly","true");
				$.each(rtnObj, function(keyid,item) {
					if($(".input-header .saveinput[data-keyid='"+keyid+"']").length)
						$(".input-header .saveinput[data-keyid='"+keyid+"']").val(item);
				}); 
			}
			endLoad($(".module-master-list .btnclear"),$(".module-master-list .btnclear-loader"));
		});

	});
	$(".module-master-list .btndelete").off("click");
	$(".module-master-list").on("click",".btndelete",function(){
		objRow = $(this).closest(".row-data");
		objCmd = $(objRow).find(".btncommand");
		objLoad = $(objRow).find(".btndelete-loader");

		sModuleId = $(objRow).attr("data-moduleid");
		aData = {u_mode:"module_delete",module_id:sModuleId};
		
		startLoad($(objCmd),$(objLoad));
		callAjax("module_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$.notify("Data Removed");
				$(".module-master-list .row-data[data-moduleid='"+sModuleId+"']").remove();
			}else{
				endLoad($(objCmd),$(objLoad));
			}
		});
	});

	$(".module-master-list .btnoption").off("click");
	$(".module-master-list").on("click",".btnoption",function(){
		objRow = $(this).closest(".row-data");
		objCmd = $(objRow).find(".btncommand");
		objLoad = $(objRow).find(".btndelete-loader");
		sModuleId = $(objRow).attr("data-moduleid");

		sUrl="module_option_main.php?module_id="+sModuleId;
		showDialog(sUrl,"Module Option List"+sModuleId,"500","820","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});


	$(".module-master-list .btnclear").off("click");
	$(".module-master-list .btnclear").on("click",function(){
		$(".input-header .saveinput[data-pk='1']").removeAttr("readonly");
		$(".input-header .saveinput").val("");
		$(".module-master-list .btnadd").attr("data-mode","module_add");
	});

});
</script>