<?
include_once("in_session.php");
include_once("in_php_function.php");

$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sDataId=urldecode(getQS("labid"));
$sDataType=getQS("datatype");
$sDataCode=getQS("datacode");
$sClinicId=getSS("clinic_id");

$sDataAttr = getDataAttr($sUid,$sColD,$sColT)." data-labid='".$sDataId."'";


include("in_db_conn.php");
$sSupName="";

$query = "SELECT SO.sale_opt_id,PLTSP.lab_id,ref_lab_id,lab_name,lab_group_name,sale_opt_name,PLTSP.lab_price,PLOLT.sale_price,PLOLT.sale_opt_id
FROM p_lab_test_sale_price PLTSP

LEFT JOIN sale_option SO
ON SO.sale_opt_id = PLTSP.sale_opt_id

LEFT JOIN p_lab_test PLT
ON PLT.lab_id = PLTSP.lab_id
AND PLT.lab_group_id != ''

LEFT JOIN p_lab_test_group PLTG
ON PLTG.lab_group_id = PLT.lab_group_id

LEFT JOIN p_lab_order_lab_test PLOLT
ON PLOLT.lab_id=PLTSP.lab_id
AND PLOLT.uid=?
AND PLOLT.collect_date=?
AND PLOLT.collect_time=?

WHERE PLTSP.lab_id=? 
ORDER BY SO.data_seq
";


$sOptList = "";  $sSalePrice=0; $sDose = 0; $sCurOpt="";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssss",$sUid,$sColD,$sColT,$sDataId);
if($stmt->execute()){
	$stmt->bind_result($sale_opt_id,$lab_id,$ref_lab_id,$lab_name,$lab_group_name,$sale_opt_name,$lab_price,$sale_price,$cur_opt_id);
	while ($stmt->fetch()) {
		$sSupName=$lab_group_name." : ".$lab_name;
		$sOptionName = $sale_opt_id." : ".$sale_opt_name." ราคา ".$lab_price." บาท";
		if($cur_opt_id==$sale_opt_id) $sOptionName = ">>>>> ".$sOptionName;

		$sOptList.="<option value='".$sale_opt_id."' data-price='".$lab_price."' ".(($cur_opt_id==$sale_opt_id)?"selected":"")." >$sOptionName</option>";
		$sSalePrice=$sale_price;
		$sCurOpt=$cur_opt_id;
	}	
}	

$sDataAttr.= " data-saleprice='".$sSalePrice."' data-optid='".$sCurOpt."'";

$mysqli->close();

?>
<div id='divSISO' class='fl-wrap-col  row-color' <? echo($sDataAttr); ?>>
	<div class='fl-wrap-row fl-mid h-30 row-color'>
		<? echo($sSupName); ?>
	</div>
	<div class='fl-wrap-row h-30 row-color'>
		<SELECT id='ddlSaleOptId' class='fill-box'><? echo($sOptList); ?></SELECT>
	</div>
	<div class='fl-fix h-30 row-color'></div>
	<div class='fl-wrap-row h-30 row-color'>
		<div class='fl-fix w-100'>ราคา</div>
		<div class='fl-fill'><input id='txtSalePrice' type='number' class='fill-box' value='<? echo($sSalePrice); ?>' /></div>
		<div class='fl-fix w-50'>บาท</div>
	</div>
	<div class='fl-fix h-30 row-color'></div>	
	<div class='btn-row fl-wrap-row h-30 row-color'>
		<div class='fl-fill'></div>
		<div  id='btnUpdatePrice' class='fl-fix wper-30 fl-mid fabtn f-border' style='background-color: green;color:white'>ยืนยัน</div>
		<div class='fl-fill'></div>
		<div id='btnClose' class='fl-fix wper-30 fl-mid fabtn f-border' style='background-color: orange;color:white'>ยกเลิก</div>
		<div class='fl-fill'></div>
	</div>
	<div class='btn-row-loader fl-wrap-row h-30 row-color fa-mid' style='display:none'><i class='fa fa-spinner fa-spin fa-2x'></i></div>
</div>

<script>
$(function(){
	$("#divSISO #ddlSaleOptId").off("change");
	$("#divSISO").on("change","#ddlSaleOptId",function(){
		sOptId=$(this).val();
		iPrice=$(this).find("option:selected").attr('data-price');
		$("#divSISO #txtSalePrice").val(iPrice);
	});


	$("#divSISO #btnClose").off("click");
	$("#divSISO").on("click","#btnClose",function(){
		closeDlg($(this));
	});

	$("#divSISO #btnUpdatePrice").off("click");
	$("#divSISO").on("click","#btnUpdatePrice",function(){
		sUid = $("#divSISO").attr('data-uid');
		sColD = $("#divSISO").attr('data-coldate');
		sColT = $("#divSISO").attr('data-coltime');
		labId = $("#divSISO").attr('data-labid');
		sSaleP = $("#divSISO #txtSalePrice").val();
		sOptId= $("#divSISO #ddlSaleOptId").val();

		sCurSale = $("#divSISO").attr('data-saleprice');
		sCurOpt = $("#divSISO").attr('data-optid');

		if(sCurSale==sSaleP && sOptId==sCurOpt){

			$.notify("ไม่พบข้อมูลเปลี่ยนแปลง","warn");
			return;
		}

		sUrl="supply_a.php";

		startLoad($("#divSISO .btn-row"),$("#divSISO .btn-row-loader"));
		aData={u_mode:"update_lab_price",uid:sUid,coldate:sColD,coltime:sColT,labid:labId,saleprice:sSaleP,saleopt:sOptId};
        callAjax(sUrl,aData,function(jRes,rData){
			if(jRes.res=="1"){
				closeDlg($("#divSISO #btnUpdatePrice"),"1");
			}else{
				
			}
        });
	});

});
</script>