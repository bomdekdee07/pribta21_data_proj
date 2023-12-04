<?
	include_once("in_session.php");
	include_once("in_php_function.php");
	$optType="";

	$aTypeList = array(); $aGroupList=array();
	include("in_db_conn.php");
	$query = "SELECT supply_group_code,supply_group_name,ISG.supply_group_type,IST.supply_type_name FROM i_stock_group ISG
		LEFT JOIN i_stock_type IST
		ON IST.supply_group_type = ISG.supply_group_type
		ORDER BY supply_group_name ";
	$stmt=$mysqli->prepare($query);
	//$stmt->bind_param()
	if($stmt->execute()){
		$stmt->bind_result($supply_group_code,$supply_group_name,$supply_group_type,$group_type_name);
		while($stmt->fetch()){
			$aTypeList[$supply_group_type] = $group_type_name;
			$aGroupList[$supply_group_code]["name"] = $supply_group_name;
			$aGroupList[$supply_group_code]["type"] = $supply_group_type;
		}
	}

	$stmt->close();
	$mysqli->close();

	$aPerm = getPerm("STOCK");
	$isSysAdmin = getSS("sysadmin");
	$isView = getPerm("STOCK","1","view");
	$isInsert = getPerm("STOCK","1","insert");
	$isUpdate = getPerm("STOCK","1","update");
	$isDelete = getPerm("STOCK","1","delete");
	$isAdmin = getPerm("STOCK","1","admin");

	$sView = ($isView)?"":" hideme";
	$sInsert = ($isInsert)?"":" hideme";
	$sUpdate = ($isUpdate)?"":" hideme";
	$sDelete = ($isDelete)?"":" hideme";
	$sAdmin = ($isAdmin)?"":" hideme";


	$optType = "<option value=''>---Please select---</option>"; $optGroup = "<option value=''>---Please select---</option>";
	foreach ($aTypeList as $group_type => $type_name) {
		if(getPerm("STOCK",$group_type,"view")) $optType .="<option value='$group_type' >$type_name</option>";
	}
	foreach ($aGroupList as $group_code => $aGrp) {
		if(getPerm("STOCK",$aGrp["type"],"view")) $optGroup .="<option value='$group_code' data-type='".$aGrp["type"]."'>".$aGrp["name"]."</option>";
	}

?>

<div id='divSupplyMaster' class='fl-wrap-col'>
	<div class='fl-wrap-row h-30 row-color-2' >
		
		<div class='fl-mid' style='padding-left:30px;padding-right:10px'>
			Type :
		</div>
		<div class='fl-fill fl-mid'>
			<SELECT id='ddlType' class='fl-fill'>
				<? echo($optType); unset($optType);?>
			</SELECT>
		</div>
		<div id='btnAddSupplyGroup' class='fabtn fl-fix fl-mid w-30' style='color:green'>
			<i class=' fa fa-plus-circle fa-lg' title="เพิ่มกลุ่ม \ Add Group"></i>
		</div>
		<div class='fl-mid' style='padding-left:30px;padding-right:10px'>
			Group :
		</div class='fl-mid'>
		<div class='fl-fill fl-mid'>
			<SELECT id='ddlGroup' class='fl-fill'>
				<? echo($optGroup);  unset($optGroup);?>
			</SELECT>
		</div>
		
		<div class='fl-fix w-50 fl-mid'>
			<i id='btnFilter' title='View List' class="fabtn fas fa-search fa-lg"></i>
		</div>
	</div>
	<div class='row-header fl-wrap-row h-40 row-color-2 fs-smaller ' style='overflow-y:scroll;'>
		<div class='fl-wrap-col w-80 fl-mid' style='color:green'>

			<i id='btnAddSupplyMaster' title='Add New' class="fabtn fas fa-plus-circle fa-2x <? echo($isInsert); ?>"></i>

		</div>
		<div class='fl-wrap-col w-150'>
			<div >Type</div>
			<div  class='btn-sort-col' data-sort='supply_code'>Code</div>
		</div>
		<div class='fl-wrap-col'>
			<div class='btn-sort-col fl-fill h-15 fs-smaller' data-sort='supply_name'>Name</div>
			<div class='fl-fill'><input id='txtFilterSupName' class='h-20' /></div>
		</div>
		<div class='fl-fix w-100'>
			<div >Unit</div>
			<div >วัน/ครั้ง</div>
		</div>
		<div class='fl-fix w-150 fl-mid'>
			<div >เวลา</div>

		</div>
		<div class='fl-wrap-col'>
			<div >Note</div>
			<div >Desc</div>
		</div>
		<div class='fl-fix w-80 fl-mid'>
			ON
		</div>
		<div class='fl-fix w-50 fl-mid'>
			X
		</div>
	</div>
	<div id='divSupplyMasterList' class='row-body fl-wrap-col fs-smaller' style='overflow-y:scroll;background-color: white'>
	</div>
	<div id='divSupplyMasterList-loader' class='fl-fill fl-mid' style='display:none'>
		<i class='fa fa-spinner fa-spin fa-4x'></i>
	</div>
</div>


<script>
	$(document).ready(function(){

		function filterGroups(sType){
			
			if(sType==""){
				$("#divSupplyMaster #ddlGroup option").show();
			}else{
				$("#divSupplyMaster #ddlGroup option").hide();
				$("#divSupplyMaster #ddlGroup option[data-type='"+sType+"']").show();
				$("#divSupplyMaster #ddlGroup").val("");
				$("#divSupplyMaster #ddlGroup").notify("updated","success");
			}
		}

		$("#divSupplyMaster #txtFilterSupName").off("keyup");
		$("#divSupplyMaster #txtFilterSupName").on("keyup",function(e){
			sVal=$(this).val();
			$("#divSupplyMasterList .row-data").hide();
			$("#divSupplyMasterList .supply_name:Contains('"+sVal+"')" ).closest(".row-data").show();
		});

		$("#divSupplyMaster #ddlType").off("change");
		$("#divSupplyMaster #ddlType").on("change",function(){
			sType = $("#divSupplyMaster #ddlType").val();
			filterGroups(sType);
		});
		
		$("#divSupplyMaster #ddlGroup").off("change");
		$("#divSupplyMaster #ddlGroup").on("change",function(){
			$(this).closest("#divSupplyMaster").find("#btnFilter").trigger("click");
		});

		$("#divSupplyMasterList .btneditmasterexc").off("click");
		$("#divSupplyMasterList").on("click",".btneditmasterexc",function(){
			objMaster = $(this).closest("#divSupplyMaster");
			objRow = $(this).closest(".row-data");
			sCode = $(objRow).attr("data-code");
			objThis = $(this);
			sUrl="supply_master_sub_item.php?supply_code="+sCode;
			showDialog(sUrl,"Update Item Conversion","300","450","",
				function(sResult){
					if(sResult!=""){
						$(objRow).find(".sub-supply-name").html(sResult);
					}
				},false,function(){}
			);
		});

		$("#divSupplyMasterList .btneditsupmaster").off("click");
		$("#divSupplyMaster").on("click",".btneditsupmaster",function(){
			sGroup = $("#divSupplyMaster #ddlGroup").val();
			sType =  $("#divSupplyMaster #ddlType").val();
			sCode = $(this).closest(".row-data").attr("data-code");

			showItemDetail("edit",sGroup,sType,sCode);
		});

		$("#divSupplyMasterList .btndeletesupply").off("click");
		$("#divSupplyMaster").on("click",".btndeletesupply",function(){
			sCode = $(this).closest(".row-data").attr("data-code");
			if(confirm("Please confirm delete.(If record is in used it will not be deleted.)\r\nกรุณายืนยันลบข้อมูล(ข้อมูลจะไม่สามารถลบได้ หากมีการใช้แล้ว)")){
				aData = {u_mode:"delete",supply_code:sCode};

				startLoad($("#divSupplyMasterList .row-data[data-code='"+aData.supply_code+"']").find(".fabtn"),$("#divSupplyMasterList .row-data[data-code='"+aData.supply_code+"']").find(".btn-loader"));
				callAjax("supply_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res!="1"){
						$.notify("Data is not delete. Please try again\r\n"+rtnObj.msg,"error");
					}else if(rtnObj.res=="1"){
						$.notify("Data Removed.","success");
						$("#divSupplyMasterList .row-data[data-code='"+aData.supply_code+"']").remove();
					}
					//
					endLoad($("#divSupplyMasterList .row-data[data-code='"+aData.supply_code+"']").find(".fabtn"),$("#divSupplyMasterList .row-data[data-code='"+aData.supply_code+"']").find(".btn-loader"));
				});
			}

		});

		$("#divSupplyMaster #btnAddSupplyGroup").off("click");
		$("#divSupplyMaster").on("click","#btnAddSupplyGroup",function(){
			sType =  $("#divSupplyMaster #ddlType").val();
			if(sType==""){
				$("#divSupplyMaster #ddlType").notify("กรุณาเลือก ประเภทที่ต้องการเพิ่ม \r\nPlease select Type to added");
				return;
			}
			sUrl="supply_management_inc_sub_group_create.php?group_type="+sType;
			showDialog(sUrl,"Add New Group Item","450","450","",
				function(sResult){
					//CLose function

						aData={u_mode:"supply_group_list",supply_group_type:sType};
						callAjax("supply_a.php",aData,function(rtnObj,aData){
							if(rtnObj.res!="1"){
								$.notify("Data is not added. Please try again\r\n"+rtnObj.msg,"error");
							}else if(rtnObj.res=="1"){
								$("#divSupplyMaster #ddlGroup").html(rtnObj.msg);
								filterGroups(sType);
							}
							
	
						});
					
				},false,function(){}
			);
		});

		$("#divSupplyMaster #btnAddSupplyMaster").off("click");
		$("#divSupplyMaster").on("click","#btnAddSupplyMaster",function(){
			sType =  $("#divSupplyMaster #ddlType").val();
			sGroup = $("#divSupplyMaster #ddlGroup").val();
			
			if(sGroup == "" || sType==""){
				$.notify("กรุณาเลือกประเภท และ หมวดหมู่ที่ต้องการเพิ่ม \r\nPlease select both Type and Group to add new one.");
				return;
			}
			showItemDetail("add",sGroup,sType,"");
		});

		function showItemDetail(sMode,sGroup,sType,sCode){
			sUrl="supply_master_inc_dlg.php?u_mode="+sMode+"&type="+sType+"&group="+sGroup+"&code="+sCode;
			showDialog(sUrl,"Add New Supply Master Item","90%","900","",
			function(sResult){
			//CLose function
			if(sResult=="1"){
			}
			},false,function(){
			//Load Done Function
			});
		}

		$("#divSupplyMaster #btnFilter").off("click");
		$("#divSupplyMaster #btnFilter").on("click",function(){
			sGroup = $("#divSupplyMaster #ddlGroup").val();
			sType =  $("#divSupplyMaster #ddlType").val();
			if(sGroup == "" && sType==""){
				$.notify("Please select Type or Group");
			}else{
				sUrl = "supply_master_inc_list.php?group="+sGroup+"&type="+sType;
				startLoad($("#divSupplyMaster #divSupplyMasterList,#divSupplyMaster #btnFilter"),$("#divSupplyMaster #divSupplyMasterList-loader"));
				$("#divSupplyMaster #divSupplyMasterList").load(sUrl,function(){
					endLoad($("#divSupplyMaster #divSupplyMasterList,#divSupplyMaster #btnFilter"),$("#divSupplyMaster #divSupplyMasterList-loader"));
				});
			}
		});
	});
</script>