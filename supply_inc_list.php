<?
//JENG
include_once("in_php_function.php");
$isInc=getQS("isinc");
$isIncServ = getQS("incserv");
$showAmt = getQS("showamt");
$sFind=urldecode(getQS("find"));
$showEmpty =getQS("showempty");
?>
<div id='divSupplySearch' class='fl-wrap-col fs-small' data-isdlg='<? echo($isInc); ?>' data-incserv='<? echo($isIncServ); ?>' data-showamt='<? echo($showAmt); ?>'>
	<div class='fl-wrap-row h-30 fl-mid'>
		<div class='fl-fix w-150'>
			ค้นหา/SEARCH
		</div>
		<div class='fl-fix h-fill w-30 fabtn btnquick fl-mid' data-key='ส่วนลด' title='ส่วนลด'><i class='fa fa-dollar-sign fa-lg'></i></div>
		<div class='fl-fix h-fill w-30 fabtn btnquick fl-mid' data-key='ค่าบริการ' title='ค่าบริการ'><i class='fa fa-syringe fa-lg'></i></div>
		<div class='fl-fix h-fill w-30 fabtn btnquick fl-mid' data-key='เงินสนับสนุน' title='เงินสนับสนุน'><i class='fa fa-hand-holding-usd fa-lg'></i></div>
		<div class='fl-fix h-fill w-30 fabtn btnquick fl-mid' data-key='condom' title='ถุงยาง'><i class='fa fa-umbrella fa-lg'></i></div>
		<div class='fl-fill'>
			<input id='txtSearchSupply' name="txtSearchSupply" class='fill-box al-left' placeholder="พิมพ์คำที่ต้องการค้นหา" />
		</div>
		<div id='btnSearchSupply' class='fl-fix w-50 h-30 fl-mid fabtn'>
			<i  class='fas fa-search fa-lg'></i>
		</div>
	</div>
	<div class='fl-wrap-row row-header row-color-2 h-25'>
		<div class='fl-fix w-110 fl-mid'>Group/Code</div>
		<div class='fl-fill fl-mid'>Name</div>
		<?
			if($showAmt=="1"){
				$sHtml="
				<div class='fl-fix w-100 fl-mid'>Lot/Exp Date</div>
				<div class='fl-fix w-100 fl-mid'>Amt. Left</div>";
				echo($sHtml);
			}

		?>
		<div class='fl-fix w-100 fl-mid'>Unit</div>
		<div class='fl-wrap-row'>
			<div class='fl-fill fl-mid'>Description</div>
			<div class='fl-fix w-50 fl-mid' title='Show Empty Stock'><div class='h-25 lh-25'><label ><input id='chkShowEmpty' type='checkbox' class='bigcheckbox'  />All</label></div></div>
		</div>

	</div>
	<div id='divSupplySearchRes' class='fl-wrap-col fl-scroll'>
		<?	if($sFind!="")  include("supply_inc_list_result.php");  ?>
	</div>
</div>

<script>
	$(document).ready(function(){
		// Autocomplete and select
		$("[name=txtSearchSupply]").autocomplete({
			source: "supply_inc_list_result_autocomplete.php?term="+$("[name=txtSearchSupply]").val(),
			minLength: 0,
			select: function (event, ui) {
				var label = ui.item.label;
				var value = ui.item.value;
				// console.log(label+"/"+value);
				$("#btnSearchSupply").trigger("click");
			}
		});

		$("#divSupplySearch .btnquick").off("click");
		$("#divSupplySearch").on("click",".btnquick",function(){
			sKey = $(this).attr("data-key");
			$(this).closest("#divSupplySearch").find("#txtSearchSupply").val(sKey);
			$(this).closest("#divSupplySearch").find("#btnSearchSupply").trigger("click");
		});

		$("#divSupplySearch #chkShowEmpty").unbind("change");
		$("#divSupplySearch #chkShowEmpty").on("change",function(){
			if($(this).is(":checked")){
				$("#divSupplySearch #divSupplySearchRes .data-row[data-isservice='0'][data-amt='0']").show();
			}else{
				$("#divSupplySearch #divSupplySearchRes .data-row[data-isservice='0'][data-amt='0']").hide();
			}
		});
		$("#divSupplySearch #btnSearchSupply").unbind("click");
		$("#divSupplySearch #btnSearchSupply").on("click",function(){
			sText = $("#txtSearchSupply").val();
			sIncServ = $("#divSupplySearch").attr('data-incserv');
			sShowAmt = $("#divSupplySearch").attr('data-showamt');


			if(sText.length <2) {
				$.notify("Please use least 2 characters to search.\r\nกรุณาค้นหาอย่างน้อย 2 ตัวอักษร");
				return;
			}
			sUrl="supply_inc_list_result.php?showamt="+sShowAmt+"&incserv="+sIncServ+"&find="+encodeURIComponent(sText);
			$("#divSupplySearch #divSupplySearchRes").load(sUrl,function(){
				if($("#divSupplySearch #chkShowEmpty").is(":checked")==false){
					$("#divSupplySearch #divSupplySearchRes .data-row[data-isservice='0'][data-amt='0']").hide();
				}
			});
		});

		$("#divSupplySearch #txtSearchSupply").unbind("keydown");
		$("#divSupplySearch #txtSearchSupply").on("keydown",function(ev){
			var charCode="";
			if (ev.key !== undefined) {
				charCode = ev.key;
			} else if (event.keyIdentifier !== undefined) {
				charCode = ev.keyIdentifier;
			} else if (ev.keyCode !== undefined) {
				charCode = ev.keyCode;
			}

			if(charCode.toUpperCase()=="ENTER"){
				$("#divSupplySearch #btnSearchSupply").trigger("click");
			}
		});

	<?
		$sHtml = "";
		if($showEmpty=="1"){
			$sHtml.="$(\"#chkShowEmpty\").prop('checked',true);$(\"#chkShowEmpty\").trigger('change');";
		}
		if($isInc=="1"){
			$sHtml = "";

			echo($sHtml);
		}else{
			$sHtml.="
			$(\"#divSupplySearch #divSupplySearchRes .data-row\").unbind(\"click\");
				$(\"#divSupplySearch #divSupplySearchRes\").on(\"click\",\".data-row\",function(){
				sCode = JSON.stringify($(this).attr(\"data-supcode\"));
				sCode_con = sCode.replace(/\"|'/g,'');
				sBulk = JSON.stringify($(this).attr(\"data-bulkunit\"));
				sBulk_con = sBulk.replace(/\"|'/g,'');
				sConvAmt = JSON.stringify($(this).attr(\"data-convamt\"));
				sName = JSON.stringify($(this).find(\"div[data-keyid='supply_name']\").html());
				sName_con = sName.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, ' ');
				sUnit = JSON.stringify($(this).find(\"div[data-keyid='supply_unit']\").html());
				sUnit_con = sUnit.replace(/\"|'/g,'');
				sResult = (sCode_con+\",\"+sName_con+\",\"+sUnit_con+\",\"+sBulk_con+\",\"+sConvAmt);
				console.log(sResult);
				closeDlg($(this),sResult);
			});";
		}
		echo($sHtml);
	?>
	if($("#divSupplySearch #divSupplySearchRes .data-row").length==1){
		$("#divSupplySearch #divSupplySearchRes .data-row").trigger("click");
	}else{
		$("#divSupplySearch #txtSearchSupply").focus();	
	}	


	});	
</script>