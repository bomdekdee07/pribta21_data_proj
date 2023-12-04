<? 
include_once("in_php_function.php");
$sSupCode=getQS("code");
$sGroupType=getQS("type");
?>

<div id='divItemDlg' class='fl-wrap-col' data-supcode='<? echo($sSupCode); ?>' data-grouptype='<? echo($sGroupType); ?>'>
	<div class='fl-wrap-row fl-mid h-50'>
		<div class='fl-fix w-50'></div>
		<div class='fl-fill'><input id='btnQuickNew' type='button' value='New' /></div>
		<div id='btnViewLog' class='fl-fix w-50 fabtn'><i class='fa fa-info-circle fa-lg'></i></div>
	</div>
	<div id='divItemDetail' class='fl-fill'>
		<? include("supply_master_inc_detail.php"); 
			$sGroupType=getQS("type");
		?>
	</div>
	<div id='divItemDetail-loader' class='fl-wrap-col fl-mid' style='display:none'><i  class='fas fa-spinner fa-spin fa-3x'></i>
	</div>
	<div class='fl-wrap-row h-50 fl-mid' style='color:green' >
		<div class='fl-fix w-100'>
			<input class='btnmoveitem' data-type='prev' type='button' value='<< Prev' />
		</div>
		<div class='fl-fill fl-mid'>
			<? 
			if(getPerm("STOCK",$sGroupType,"admin")) echo("<i id='btnSaveItem' class='pt-btn fas fa-plus-circle f-border' data-mode='".$sMode."'>Save</i>"); 
			?>
			<i id='btnSaveItem-loader' style='display:none' class='fas fa-spinner fa-spin fa-2x'></i>
		</div>
		<div class='fl-fix w-100'>
			<input class='btnmoveitem' data-type='next' type='button' value='Next >>' />

		</div>
	</div>
</div>

<script>
	function setPribtaObj(objX,sValue){
		$(objX).val(sValue);
		$(objX).attr('data-odata',sValue);
	}


	$(document).ready(function(){
		function checkButton(supply_code=""){
			var sSupCode = supply_code;
			if(sSupCode=="")
			sSupCode = $("#divItemDetail .saveinput[data-keyid='supply_code']").val();
			if(sSupCode==""){
				$("#divItemDlg .btnmoveitem").hide();
				return;
			}
			//Check Prev
			if($("#divSupplyMasterList").length){
				objx = $("#divSupplyMasterList .row-data[data-code='"+sSupCode+"']").prev(".row-data");
				if($(objx).length) $("#divItemDlg .btnmoveitem[data-type='prev']").show();
				else $("#divItemDlg .btnmoveitem[data-type='prev']").hide();
				
				objx2 = $("#divSupplyMasterList .row-data[data-code='"+sSupCode+"']").next(".row-data");
				if($(objx2).length){
					$("#divItemDlg .btnmoveitem[data-type='next']").show();
				}else{
					$("#divItemDlg .btnmoveitem[data-type='next']").hide();
				}
			}else{
				$("#divItemDlg .btnmoveitem").hide();
				$.notify("divSupplyMasterList Note Found");
			}
		}
		checkButton();

		$("#divItemDlg #btnViewLog").off("click");
		$("#divItemDlg #btnViewLog").on("click",function(){
			sSupCode=$("#divItemDlg").attr('data-supcode');
			sUrl="supply_master_inc_log.php?supply_code="+sSupCode;

			showDialog(sUrl,"Log for :"+sSupCode,"95%","95%","",
				function(sResult){
					//CLose function
					if(sResult=="1"){
					}
				},false,function(){
					//Load Done Function
			});
		});


		$("#divItemDlg .btnmoveitem").off("click");
		$("#divItemDlg").on("click",".btnmoveitem",function(){
			sSupCode = $("#divItemDlg .saveinput[data-keyid='supply_code']").val();
			if($("#divSupplyMasterList").length){
				objx = undefined;
				if($(this).attr("data-type")=="prev"){
					objx = $("#divSupplyMasterList .row-data[data-code='"+sSupCode+"']").prev(".row-data");
				}else if($(this).attr("data-type")=="next"){
					objx = $("#divSupplyMasterList .row-data[data-code='"+sSupCode+"']").next(".row-data");
				}
				if($(objx).length){
					sCode = $(objx).attr("data-code");

					sGroup = $("#divItemDetail #ddlGroup").val();
					sUrl = "supply_master_inc_detail.php?u_mode=edit&code="+sCode+"&group="+sGroup;

					showLoad();

					$("#divItemDlg #divItemDetail").load(sUrl,function(){
						hideLoad();
						checkButton(sCode);
					});
				}else{
					$.notify("No next item in the list.");
				}
			}

		});

		$("#divItemDlg #btnQuickNew").off("click");
		$("#divItemDlg #btnQuickNew").on("click",function(){
			var aData = getDataRow($("#divItemDlg"));

			if(aData!=""){
				if(confirm("Do you want to ignore all changed?")){

				}else{
					return;
				}
			}

			sGroup = $("#divItemDetail #ddlGroup").val();
			sUrl = "supply_master_inc_detail.php?u_mode=add&group="+sGroup;

			showLoad();

			$("#divItemDlg #divItemDetail").load(sUrl,function(){
				hideLoad();
				checkButton();
			});

		});


		$("#divItemDlg #btnSaveItem").off("click");
		$("#divItemDlg #btnSaveItem").on("click",function(){
			var isDataChanged = false;
			//validate input before create uid
			sCode =  getOV($("#divItemDlg .saveinput[data-keyid='supply_code']"));
			sMode = $(this).attr('data-mode');

			if(validateForm()!=""){
				return;
			}


			var aData = getDataRow($("#divItemDlg"));


			let aTemp = [];
			$("#divItemDetail .sale_opt_id").each(function(ix,objx){
				sO = $(objx).attr('data-odata');
				sN = $(objx).val().trim();
				if(sO!=sN){
					saleId = $(objx).attr('data-saleid');
					aTemp.push(saleId+","+sN);
				}
			});

			if(aTemp.length > 0) {
				if(aData=="" && sMode=="edit") {
					aData = getDataRow($("#divItemDlg"),"saveinput",true);

					aData.priceonly = 1;
				}
				aData.saleid = aTemp;
			}

			if(aData==""){
				$.notify("No data changed.");
				return;
			}
			aData["u_mode"]=sMode;
			aData["supply_group_type"]=$("#divItemDlg").attr("data-grouptype");

			showLoad();

			callAjax("supply_a.php",aData,function(rtnObj,aData){

				if(rtnObj.res!="1"){
					$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved","success");
					setODataRow($("#divItemDlg"));
					setODataRow($("#divItemDetail"),"sale_opt_id");
					if(aData.u_mode=="add"){
						if($("#divSupplyMasterList").length){
							objx = $("#divSupplyMasterList").append(rtnObj.addrow);
						}
					}
					sResult = getDlgResult();
					sResult= ((sResult != "")?",":"") + aData.supply_code;
					setDlgResult(sResult);
				}
				//
				hideLoad();
			});

		});

		function showLoad(){
			startLoad($("#divItemDlg #divItemDetail,#divItemDlg .btnmoveitem,#divItemDlg #btnSaveItem,#divItemDlg #btnQuickNew,#divItemDlg #btnSaveItem"),$("#divItemDlg #btnSaveItem-loader"));
		}
		function hideLoad(){
			endLoad($("#divItemDlg #divItemDetail,#divItemDlg .btnmoveitem,#divItemDlg #btnSaveItem,#divItemDlg #btnQuickNew,#divItemDlg #btnSaveItem"),$("#divItemDlg #btnSaveItem-loader"));
		}
		function validateForm(){
			$("#divItemDlg .bg-error").removeClass("bg-error");
			aData = getAllData($("#divItemDlg"));
			sMsg = "";
			if(aData.supply_name=="") {
				sMsg="Please enter name.\r\nกรุณาใส่ชื่อของชิ้นนี้";
				$("#divItemDlg .saveinput[data-keyid='supply_name']").addClass("bg-error");
			}
			objCode = $("#divItemDlg .saveinput[data-keyid='supply_code']");
			if(aData.supply_code=="" || aData.supply_code == $(objCode).attr('data-initial')){
				sMsg="Please enter Supply Code.\r\nกรุณาใส่ Supply Code";
				$(objCode).focus();
				$(objCode).addClass("bg-error");
			}
			$("#divItemDetail .sale_opt_id").each(function(ix,objx){
				var sVal = $(objx).val().trim();
				if(sVal==""){
					sMsg="Please enter the number in sale price.\r\nกรุณาใส่ตัวเลขในช่องราคา";
					$(objx).addClass("bg-error");
				}else if(isNaN(sVal)){
					sMsg="Please enter the number only in sale price.\r\nกรุณาใส่ตัวเลขในช่องราคาเท่านั้น";
					$(objx).addClass("bg-error");
				}
			});

			if(sMsg!="") $.notify(sMsg);
			return sMsg;
		}

	});
</script>