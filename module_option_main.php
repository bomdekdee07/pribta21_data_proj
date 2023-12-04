<?
include_once("in_php_function.php");
$sModId=getQS("module_id");
?>

<div id='divMOM' class='fl-wrap-col' data-modid='<? echo($sModId); ?>'>
	<div class='fl-wrap-row h-30 row-header bg-head-1'>
		<div class='fl-fix w-60 fl-mid'>

		</div>
		<div class='fl-fix w-200 fl-mid'>
			Option Code
		</div>
		<div class='fl-fill fl-mid'>
			Option Description
		</div>
		<div class='fl-fix w-50 fl-mid'>
			Enable
		</div>
		<div class='fl-fix w-60 fl-mid'>

		</div>
	</div>
	<div class='fl-wrap-row h-30 row-header row-color-2 input-header'>
		<div id='btnClearInput' class='fl-fix w-60 fl-mid fabtn'>
			<i class='fa fa-broom fa-lg'></i>
		</div>
		<div class='fl-fix w-200 fl-mid'>
			<input type='hidden' class='w-fill saveinput' data-odata='' data-keyid='module_id' value='<? echo($sModId); ?>' data-pk='1'  />

			<input id='txtOptionCode' type='' class='w-fill saveinput clearok' data-odata='' data-keyid='option_code' value='' data-pk='1'  />
		</div>
		<div class='fl-fill fl-mid'>
			<input id='txtOptionDesc' type='' class='w-fill saveinput clearok'  data-odata='' data-keyid='option_title' value='' />
		</div>
		<div class='fl-fix w-50 fl-mid'>
			<input id='chkOptionEnable' type='checkbox' class='bigcheckbox saveinput clearok'  data-odata='' data-keyid='is_enable' value='' />
		</div>
		<div id='btnSaveOption' class='fl-fix w-60 fl-mid fabtn' data-mode='option_code_add'>
			<i class='fas fa-save fa-lg'></i>
		</div>
		<div id='btnSaveOption-loader' class='fl-fix w-60 fl-mid' style='display:none'>
			<i class='fa fa-spinner fa-spin fa-lg'></i>
		</div>
	</div>
	<div id='divMOMContent' class='fl-wrap-col fl-auto'>
		<? $_GET["u_mode"]="option_code_list"; $_GET["echo"]="1"; include("module_a.php"); ?>
	</div>
	<div id='divMOMContent-loader' class='fl-wrap-col fl-mid'>
		<i class='fa fa-spiner fa-spin fa-4x'></i>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#divMOM #btnSaveOption").off("click");
		$("#divMOM #btnSaveOption").on("click",function(){
			curBtn = $(this); 
			objCurDiv = $(this).closest("#divMOM");
			curLoad = $(objCurDiv).find("#btnSaveOption-loader");
			curInput = $(objCurDiv).find(".input-header");

			objInp = $(objCurDiv).find(".input-header");
			sModuleId = $(objInp).attr("data-moduleid");

			aData = getDataRow($(curInput));
			if(aData==""){
				$.notify("No data changed");
				return;
			}
			aData["u_mode"]=$(this).attr("data-mode");
			startLoad($(curBtn),$(curLoad));
			callAjax("module_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved","success");
					if(aData.u_mode=="option_code_add"){
						$(objCurDiv).find("#divMOMContent").prepend(rtnObj.msg);
					}else if(aData.u_mode=="option_code_update"){
						
					}
					clearInput();
				}
				//
				endLoad($(curBtn),$(curLoad));
			});
		});

		$("#divMOM .btndeleteoptcode").off("click");
		$("#divMOM").on("click",".btndeleteoptcode",function(){
			if(confirm("ยืนยันลบข้อมูล?\r\nConfirm Delete?") == false){
				return
			}
			curRow = $(this).closest(".row-option");
			sModId = $(this).closest("#divMOM").attr("data-modid");
			sOptCode=$(curRow).attr("data-optcode");
			curBtn = $(this);
			curLoad = $(curRow).find(".btndeleteoptcode-loader");

			startLoad($(curBtn),$(curLoad));
			aData={u_mode:"option_code_delete",module_id:sModId,option_code:sOptCode};
			callAjax("module_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Data is not delete. Please try again\r\n"+rtnObj.msg,"error");
					endLoad($(curBtn),$(curLoad));
				}else if(rtnObj.res=="1"){
					$(curRow).remove();
				}
				//
				
			});
		});
		$("#divMOM .btneditoptcode").off("click");
		$("#divMOM").on("click",".btneditoptcode",function(){


		});
		$("#divMOM #btnClearInput").off("click");
		$("#divMOM #btnClearInput").on("click",function(){
			clearInput();

		});

		function clearInput(){
			setKeyVal($("#divMOM .input-header"),"option_code","");
			setKeyVal($("#divMOM .input-header"),"option_title","");
			setKeyVal($("#divMOM .input-header"),"is_enable","");
		}
	});

</script>