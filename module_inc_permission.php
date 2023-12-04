<?
include_once("in_session.php");
include_once("in_php_function.php");
$sSid = getSS("s_id");
$sSecId = getQS("secid");
$sSecName = getQS("secname");

if($sSecId==""){
	echo("Please select section id");
	exit();	
}

include("in_db_conn.php");
$optModule="<option value=''>--- Select ---</option>";
$query ="SELECT module_id,module_title,module_color,module_icon FROM i_module";
$stmt = $mysqli->prepare($query);
if($stmt->execute()){
  $stmt->bind_result($module_id,$module_title,$module_color,$module_icon); 
  while ($stmt->fetch()) {
  	 	$optModule.="<option value='$module_id'>$module_title</option>";
  }
}

$optCode="<option value=''>--- Select ---</option>";
$query ="SELECT module_id,option_code,option_title FROM i_module_option WHERE is_enable='1'";
$stmt = $mysqli->prepare($query);
if($stmt->execute()){
  $stmt->bind_result($module_id,$option_code,$option_title); 
  while ($stmt->fetch()) {
  	 	$optCode.="<option value='$option_code' data-moduleid='$module_id' style='display:none'>$option_title</option>";
  }
}


$mysqli->close();

?>

<div id='divMIP' class='fl-wrap-col fs-small module-permission'>
	<div class='fl-fix h-30 fl-mid'>
		<? echo($sSecId.":".$sSecName); ?>
	</div>
	<div class='fl-wrap-row row-header h-30 row-color-2'>
		<div class='fl-fix w-50'></div>
		<div class='fl-fill'>
			Module
		</div>
		<div class='fl-fix w-100'>Code</div>
		<div class='fl-fix w-80'>View</div>
		<div class='fl-fix w-80'>Insert</div>
		<div class='fl-fix w-80'>Update</div>
		<div class='fl-fix w-80'>Delete</div>
		<div class='fl-fix w-80'>Admin</div>
		<div class='fl-fix w-80'></div>
	</div>
	<div class='fl-wrap-row row-header h-30 fl-mid row-color-2 input-header'>
		<div class='fl-fix w-50'>
			<i class='fabtn fas fa-broom fa-2x btnclear'></i>
			<i class='fas fa-spinner fa-spin fa-2x btnclear-loader' style='display:none'></i>
		</div>
		<div class='fl-fill'>

			<SELECT id='ddlModule' class='saveinput fill-box h-25' value='' data-odata='' data-keyid='module_id' data-pk='1'>
				<? echo($optModule); ?>
			</SELECT>
			<input type='hidden' class='saveinput fill-box' value='<? echo($sSecId); ?>' data-odata='<? echo($sSecId); ?>' data-keyid='section_id' data-pk='1'/>
		</div>

		<div class='fl-fix w-100'><input class='saveinput fill-box' value='' data-odata='' data-keyid='option_code' data-pk='1'/></div>

		<div class='fl-fix w-20'>
			<SELECT id='ddlOptCode' title='Option Code' class='w-fill h-fill'><? echo($optCode); ?></SELECT>
		</div>

		<div class='fl-fix w-80 fl-mid'><input class='saveinput  bigcheckbox' type='checkbox' value='' data-odata='' data-keyid='allow_view' /></div>
		<div class='fl-fix w-80 fl-mid'><input class='saveinput  bigcheckbox' type='checkbox' value='' data-odata='' data-keyid='allow_insert' /></div>
		<div class='fl-fix w-80 fl-mid'><input class='saveinput  bigcheckbox' type='checkbox' value='' data-odata='' data-keyid='allow_update' /></div>
		<div class='fl-fix w-80 fl-mid'><input class='saveinput  bigcheckbox' type='checkbox' value='' data-odata='' data-keyid='allow_delete' /></div>
		<div class='fl-fix w-80 fl-mid'><input class='saveinput  bigcheckbox' type='checkbox' value='' data-odata='' data-keyid='is_admin' /></div>
		<div class='fl-fix w-80 fl-mid' style='color:green'>
			<i class='fas fa-plus-square fabtn btnadd fa-2x' data-mode='module_perm_add'></i>
			<i class='fas fa-spinner fa-spin btnadd-loader fa-2x ' style='display:none'></i>
		</div>
	</div>
	<div class='fl-wrap-col row-body' style='overflow-y: scroll;'>
		<? $_GET["u_mode"]="module_perm_list"; $_GET["section_id"]=$sSecId; include("module_a.php"); ?>
	</div>
</div>

<script>
$(document).ready(function(){
	$("#divMIP #ddlModule").off("change");
	$("#divMIP #ddlModule").on("change",function(){
		sModuleId=$(this).val();
		$("#divMIP #ddlOptCode option").hide();
		$("#divMIP #ddlOptCode option[data-moduleid='"+sModuleId+"']").show();
		$("#divMIP #ddlOptCode").val("");
	});

	$("#divMIP #ddlOptCode").off("change");
	$("#divMIP #ddlOptCode").on("change",function(){
		sOptCode=$(this).val();
		if(sOptCode!=""){
			$("#divMIP .saveinput[data-keyid='option_code']").val(sOptCode);
		}
	});

	$(".module-permission .btnadd").unbind("click");
	$(".module-permission .btnadd").on("click",function(){
		objCurDiv = $(this).closest(".module-permission");
		objInp = $(objCurDiv).find(".input-header");

		aData = getDataRow($(".module-permission .input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");
		startLoad($(".module-permission .btnadd"),$(".module-permission .btnadd-loader"));
		callAjax("module_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				if(aData.u_mode=="module_perm_update"){
					objRow = $(".module-permission .row-body .row-data[data-moduleid='"+aData.module_id+"'][data-optcode='"+aData.option_code+"'][data-secid='"+aData.section_id+"']");
					$(objRow).after(rtnObj.msg);
					$(objRow).remove();
				}else if(aData.u_mode=="module_perm_add"){
					$(".module-permission .row-body").prepend(rtnObj.msg);
				}

				$(".module-permission .btnclear").trigger("click");
			}
			//
			endLoad($(".module-permission .btnadd"),$(".module-permission .btnadd-loader"));
		});
	});

	$(".module-permission .btnedit").unbind("click");
	$(".module-permission").on("click",".btnedit",function(){
		sModuleId = $(this).closest(".row-data").attr("data-moduleid");
		sSecId = $(this).closest(".row-data").attr("data-secid");
		sOptCode = $(this).closest(".row-data").attr("data-optcode");
		aData = {u_mode:"module_perm_item",module_id:sModuleId,section_id:sSecId,option_code:sOptCode};
		$(".module-permission .btnadd").attr("data-mode","module_perm_update");
		startLoad($(".module-permission .btnclear"),$(".module-permission .btnclear-loader"));
		callAjax("module_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$(".input-header .saveinput[data-pk='1']").attr("readonly","true");
				$.each(rtnObj, function(keyid,item) {
					if($(".input-header .saveinput[data-keyid='"+keyid+"']").length)
						setOV($(".input-header .saveinput[data-keyid='"+keyid+"']"),item);
				}); 
			}
			endLoad($(".module-permission .btnclear"),$(".module-permission .btnclear-loader"));
		});

	});
	$(".module-permission .btndelete").unbind("click");
	$(".module-permission").on("click",".btndelete",function(){
		sModuleId = $(this).closest(".row-data").attr("data-moduleid");
		sSecId = $(this).closest(".row-data").attr("data-secid");
		sOptCode = $(this).closest(".row-data").attr("data-optcode");
		sObj= $(this);
		aData = {u_mode:"module_perm_delete",module_id:sModuleId,section_id:sSecId,option_code:sOptCode};
		startLoad($(".module-permission .row-data[data-moduleid='"+sModuleId+"'][data-secid='"+sSecId+"'][data-optcode='"+sOptCode+"']").find(".fabtn"),$(".module-permission .row-data[data-moduleid='"+sModuleId+"'][data-secid='"+sSecId+"'][data-optcode='"+sOptCode+"']").find(".fabtn-loader"));
		callAjax("module_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$.notify("Data Removed");
				$(".module-permission .row-data[data-moduleid='"+sModuleId+"'][data-secid='"+sSecId+"'][data-optcode='"+sOptCode+"']").remove();
			}else{
				endLoad($(".module-permission .row-data[data-moduleid='"+sModuleId+"'][data-secid='"+sSecId+"'][data-optcode='"+sOptCode+"']").find(".fabtn"),$(".module-permission .row-data[data-moduleid='"+sModuleId+"'][data-secid='"+sSecId+"'][data-optcode='"+sOptCode+"']").find(".fabtn-loader"));
			}
			
		});

	});
	$(".module-permission .btnclear").unbind("click");
	$(".module-permission .btnclear").on("click",function(){
		$(".input-header .saveinput[data-pk='1'][type!='hidden']").removeAttr("readonly");
		$(".input-header .saveinput[type!='hidden'][type!='checkbox'][type!='radio']").val("");
		$(".input-header .saveinput").prop("checked",false);
		$(".module-permission .btnadd").attr("data-mode","module_perm_add");
	});
});
</script>