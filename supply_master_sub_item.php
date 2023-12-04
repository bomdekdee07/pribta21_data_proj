<?
include_once("in_php_function.php");
$sSupCode=getQS("supply_code");
$sMaster="";
$aItem=array("supply_code"=>"","supply_name"=>"","supply_unit"=>"","bulk_unit"=>"","convert_amt");

include("in_db_conn.php");
$query = "SELECT supply_code,supply_name,supply_unit,bulk_unit,convert_amt
FROM i_stock_master 
WHERE supply_code=?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sSupCode);
if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $aItem = $row;
  }
}
$stmt->close();
$mysqli->close();

$sMaster.="<div class='fl-fix h-30 bg-head-1'>$sSupCode</div><div class='fl-fix h-30 bg-head-1'>".$aItem["supply_name"]."**".$aItem["supply_unit"]."</div>";
//Current Conversion.
$sCurrent="<div class='fl-wrap-row h-50 row-hover row-color'>
			<div class='fl-fill fl-vmid supply-name lh-25'></div>
			<div class='fl-fix w-50 fl-mid convert-amt lh-50'></div>
			<div class='fl-fix w-80 fl-mid supply-unit lh-50'></div>
		</div>
		<div class='fl-fix h-30 fs-xsmall' title=''></div>";



?>
<div id='divSMSI' class='fl-wrap-col' data-supcode='<? echo($sSupCode); ?>' data-supunit='<? echo($aItem["supply_unit"]); ?>'>
	<? echo($sMaster); ?>
	<div class='fl-fix h-30 bg-head-2'>Bulk Unit</div>
	<div class='fl-wrap-row h-30 row-color'>
		<div class='fl-fix w-100 fl-mid'>จำนวน</div>
		<div class='fl-fix w-100 fl-mid'>หน่วยปัจจุบัน</div>
		<div class='fl-fix w-80 fl-mid'>= 1 </div>
		<div class='fl-fill'>ชื่อหน่วยใหญ่</div>
	</div>
	<div class='fl-wrap-row h-30 row-color'>
		<div class='fl-fix w-100 fl-mid'><input id='txtConvRate' type='number' class='w-fill' value='<? echo($aItem["convert_amt"]); ?>' /></div>
		<div class='fl-fix w-100 fl-mid'><? echo($aItem["supply_unit"]); ?></div>
		<div class='fl-fix w-80 fl-mid'>= 1 </div>
		<div class='fl-fill'><input id='txtBulkUnit' class='w-fill' value='<? echo($aItem["bulk_unit"]); ?>' /></div>
	</div>

	<div class='fl-wrap-row h-50 fl-mid fmt-10'>
		<div class='fl-fill fl-mid'><button id='btnCloseSMSI'>Close</button></div>
		<div class='fl-fill fl-mid'><i id='btnSaveRate' class='w-100 h-40 f-border fa fa-save fa-lg fabtn fl-mid'> Save</i> <i id='btnSaveRate-loader' class='fa fa-spinner fa-spin fa-lg ' style='display:none'></i></div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#divSMSI #btnCloseSMSI").off("click");
		$("#divSMSI #btnCloseSMSI").on("click",function(){
			objThis=$(this);
			objMain=$(this).closest("#divSMSI");
			sResult = $(objMain).find(".supply-name").html() +" "+$(objMain).find(".convert-amt").html()+" "+$(objMain).find(".supply-unit").html();
			closeDlg(objThis,sResult);
		});

		$("#divSMSI #btnSaveRate").off("click");
		$("#divSMSI #btnSaveRate").on("click",function(){
			objThis=$(this);
			objMain=$(this).closest("#divSMSI");
			sSupCode=$(objMain).attr("data-supcode");
			sSupUnit=$(objMain).attr("data-supunit");
			sConvRate=$(objMain).find("#txtConvRate").val();
			sBulkUnit=$(objMain).find("#txtBulkUnit").val().trim();

			if(sBulkUnit!="" && (sConvRate=="" || sConvRate <=1)){
				$(objMain).find("#txtConvRate").notify("กรุณาใส่จำนวนมากกว่า 1","error");
				return;
			}else if(sConvRate > 1 && sBulkUnit==""){
				$(objMain).find("#txtBulkUnit").notify("กรุณาใส่หน่วยให้ถูกต้อง","error");
				return;
			}

			objLoad=$(objMain).find("#btnSaveRate-loader");


			aData={u_mode:"update_bulk_unit",supply_code:sSupCode,convert_amt:sConvRate,bulk_unit:sBulkUnit};
			startLoad($(objThis),$(objLoad));
			callAjax("supply_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Data is not save or No data changed. Please try again\r\n"+rtnObj.msg,"error");
					endLoad($(objThis),$(objLoad));
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved.","success");
					//setDlgResult("1 "+sBulkUnit+" = "+sConvRate+" "+sSupUnit,objThis);
					closeDlg(objThis,"1 "+sBulkUnit+" = "+sConvRate+" "+sSupUnit);
				}
				//
				
			});

		});

		$("#divSMSI #ddlType").off("change");
		$("#divSMSI #ddlType").on("change",function(){
			objThis=$(this);
			objMain=$(this).closest("#divSMSI");

			$(objMain).find("#ddlGroup option[value!='']").hide();
			$(objMain).find("#ddlSupply option[value!='']").hide();

			if($(objThis).val()==""){

			}else{
				$(objMain).find("#ddlGroup option[data-type='"+$(objThis).val()+"']").show();
			}
		});

		$("#divSMSI #ddlGroup").off("change");
		$("#divSMSI #ddlGroup").on("change",function(){
			objThis=$(this);
			objMain=$(this).closest("#divSMSI");

			$(objMain).find("#ddlSupply option[value!='']").hide();

			if($(objThis).val()==""){

			}else{
				$(objMain).find("#ddlSupply option[data-group='"+$(objThis).val()+"']").show();
			}
		});

	});
</script>