<?
include_once("in_session.php");
include_once("in_php_function.php");
$sCode = getQS("supply_code");
$sClinicId = getSS("clinic_id");
$sUid=getQS("uid");
$sColDate=getQS("coldate");
$sColTime=getQS("coltime");
$sSid = getSS("s_id");

$sHtmlKeyId = getHiddenPk($sUid,$sColDate,$sColTime);
$sGrpType="";

include("in_db_conn.php");
$query = "SELECT ISG.supply_group_type,supply_type_name,supply_group_name,ISM.supply_code,dose_day,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,dose_note AS order_note,supply_name,supply_desc,supply_unit,is_service,stock_lot,stock_amt,stock_exp_date FROM i_stock_master ISM
LEFT JOIN i_stock_group ISG
ON ISG.supply_group_code = ISM.supply_group_code
LEFT JOIN i_stock_type IST
ON IST.supply_group_type = ISG.supply_group_type
LEFT JOIN i_stock_list ISL
ON ISL.supply_code = ISM.supply_code
AND clinic_id=? AND ISL.stock_amt >0
WHERE ISM.supply_code=?";

$aSupInfo = array(); $sJS = "";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sClinicId,$sCode);
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$aSupInfo = $row;

		foreach ($row as $KeyId => $iVal) {
			$sJS .= "setKeyVal($(\"#dlgSOID\"),'".$KeyId."',".json_encode($iVal).",false);";
		}
		if($row["is_service"]=="1"){
			$sJS .= "$(\"#dlgSOID .supply-drug\").hide();";
		}
	}
	/*
		$stmt->bind_result($supply_group_type,$supply_type_name,$supply_group_name,$supply_code,$dose_day,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_name,$supply_desc,$supply_unit,$is_service,$stock_lot,$stock_amt,$stock_exp_date);
		while ($stmt->fetch()) {
			
		}
	*/
}
$sOptSale="";
$query="SELECT SO.sale_opt_id,sale_opt_name ,sale_price,PI.sale_opt_id
FROM sale_option SO
LEFT JOIN  i_stock_price ISP
ON ISP.sale_opt_id=SO.sale_opt_id
LEFT JOIN patient_info PI
ON PI.sale_opt_id=SO.sale_opt_id
AND PI.uid=?

WHERE SO.is_enable=1 AND ISP.supply_code=? ORDER BY SO.data_seq";

$stmt=$mysqli->prepare($query);
$stmt->bind_param("ss",$sUid,$sCode);
$ix=0; $sTemp=""; $sSalePrice=""; $sOptId="";
if($stmt->execute()){
	$stmt->bind_result($sale_opt_id,$sale_opt_name,$sale_price,$patient_sale_opt);
	while($stmt->fetch()){
		$sOptSale .= "<option value='$sale_opt_id' data-saleprice='$sale_price'>$sale_opt_name".": $sale_price บาท</option>";
		if($ix==0){
			$sSalePrice=$sale_price; $sOptId=$sale_opt_id;
		}else if($sale_opt_id==$patient_sale_opt) {
			$sSalePrice=$sale_price; $sOptId=$sale_opt_id;
		}
		$ix++;
	}
}

$sTemp = "setKeyVal($(\"#dlgSOID\"),'sale_price',".json_encode($sSalePrice).",false);
setKeyVal($(\"#dlgSOID\"),'sale_opt_id',".json_encode($sOptId).",false);";


$sJS.=$sTemp;

// Query Section
$bind_param = "s";
$array_val = array($sSid);
$check_access_price_phamar = "";
// echo "TEST".$sSid;

$query = "SELECT count(*) AS check_price_phamar 
from i_staff_clinic 
where s_id = ? and section_id = 'D03_STK_ADMIN' and sc_status = 1;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$check_access_price_phamar = $row["check_price_phamar"];
	}
}
$stmt->close();

// Query type item [9: other supply, 3: discount, 2: service, 1: drug]
$bind_param = "s";
$array_val = array($sCode);
$check_type_item = "";

$query = "SELECT st_group.supply_group_type
from i_stock_master master_st
left join i_stock_group st_group on(st_group.supply_group_code = master_st.supply_group_code)
left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
where master_st.supply_code = ?;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$check_type_item = $row["supply_group_type"];
	}
}
$stmt->close();
$mysqli->close();

$html_accessText = "";
// echo "TEST:".$check_type_item;
if($check_type_item == "1" && $check_access_price_phamar != "1"){
	$html_accessText = "readonly";
}
else{
	$html_accessText = " ";
}

?>
<div  id='dlgSOID' class='fl-wrap-col'>
	<div class='fl-wrap-row'>
		<div class='fl-wrap-col fs-small'>
			<div class='fl-wrap-row h-30 row-color fl-mid fw-b'>
				<? echo($sHtmlKeyId); ?>
				<input type='hidden' class='saveinput' data-pk='1' data-keyid='supply_code' />
				<span class='saveinput' data-keyid='supply_name'></span>
			</div>
			<div class='fl-wrap-row h-30 row-color'>
				<div class='fl-fix w-80 fl-mid'>
					ราคา
				</div>
				<div class='fl-wrap-row'>
					<SELECT id='ddlSaleOptId' class='saveinput fill-box h-25' data-keyid='sale_opt_id' data-odata>
						<? echo($sOptSale); ?>
					</SELECT>
				</div>
			</div>

			<div class='fl-wrap-row h-30 row-color supply-drug supply-product'>
				<div class='fl-fill fl-mid'>
					เวลา
				</div>
				<div class='fl-wrap-row w-200'>
					<div class='fl-fix w-50 fl-mid'>
						เช้า
					</div>
					<div class='fl-fix w-50 fl-mid'>
						เที่ยง
					</div>
					<div class='fl-fix w-50 fl-mid'>
						เย็น
					</div>
					<div class='fl-fix w-50 fl-mid'>
						นอน
					</div>
				</div>
			</div>
			<div class='fl-wrap-row h-30 row-color supply-drug'>
				<div class='fl-fix w-80' ></div>
				<div class='fl-fill fl-mid'>
					<SELECT class='saveinput h-25 fill-box' data-odata='{NEW}' data-keyid='dose_before'>
						<option value=''>----</option>
						<option value='P'>พร้อมมื้อ</option>
						<option value='B'>ก่อนมื้อ</option>
						<option value='A'>หลังมื้อ</option>
					</SELECT> 
				</div>
				<div class='fl-fix w-80 fl-mid'>อาหาร</div>
				<div class='fl-wrap-row w-200'>
					<div class='fl-fix w-50 fl-mid'>
						<input class='bigcheckbox saveinput' type='checkbox' data-keyid='dose_breakfast' />
					</div>
					<div class='fl-fix w-50 fl-mid'>
						<input class='bigcheckbox saveinput' type='checkbox' data-keyid='dose_lunch' />
					</div>
					<div class='fl-fix w-50 fl-mid'>
						<input class='bigcheckbox saveinput' type='checkbox' data-keyid='dose_dinner' />
					</div>
					<div class='fl-fix w-50 fl-mid'>
						<input class='bigcheckbox saveinput' type='checkbox' data-keyid='dose_night' />
					</div>
				</div>
			</div>
			<div class='fl-wrap-row h-30 row-color'>
				<div class='fl-fix w-80'>
					จำนวน
				</div>
				<div class='fl-fix w-70 fl-mid'>
					<input class='fill-box saveinput h-25 sum-price'  data-odata='' value='' data-keyid='dose_day' type='number' />
				</div>
				<div class='fl-fill fl-mid'>
					<span class='saveinput' data-keyid='supply_unit'></span>
				</div>
				<div class='fl-fill fl-mid al-right'>
					<span>ราคา</span>
				</div>
				<div class='fl-fix w-70 fl-mid'>
					<input type='number' class='fill-box saveinput h-25 sum-price'  data-odata='' value='' data-keyid='sale_price' <? echo $html_accessText; ?>/>
				</div>
				<div class='fl-fill fl-mid al-right'>
					<span>รวม</span>
				</div>
				<div class='fl-fix w-70 fl-mid'>
					<input class='fill-box saveinput h-25 sum-price btn-selected'  data-odata='' value='' data-keyid='total_price' readonly="true" />
				</div>
				<div class='fl-fix w-30 fl-mid'>
					บาท
				</div>
			</div>
			<div class='fl-wrap-row h-30 row-color'>
				
				<!-- input type='hidden' class='saveinput' data-odata='' value='' data-pk='1' data-keyid='supply_lot' /-->
				<div class='fl-fix w-80 fl-mid'>
					วิธีใช้
				</div>
				<div class='fl-fill fl-mid'>
					<input class='fill-box saveinput h-25' data-odata='' value='' data-keyid='supply_desc' />
				</div>
			</div>
			<div class='fl-wrap-row h-30 row-color'>
				<div class='fl-fix w-80 fl-mid'>
					Note
				</div>
				<div class='fl-fill fl-mid'>
					<input class='fill-box saveinput h-25' data-odata='' value='' data-keyid='order_note' />
				</div>
			</div>
			<div class='fl-wrap-col fs-small'>
				<div class='fl-fix'>โครงการที่ยังอยู่ / Current Project Enroll</div>
				<div class='fl-wrap-row h-30 bg-head-1 row-header'>
					<div class='fl-fix w-100 fl-mid'>ID</div>
					<div class='fl-fill fl-mid'>Title</div>
					<div class='fl-fix w-150 fl-mid'>PID</div>
					<div class='fl-fill fl-mid'>Enroll Date</div>
					<div class='fl-fix w-50 fl-mid'>Sale</div>
				</div>

				<div id='divCurProjList' class='fl-wrap-col fl-scroll'>
					<? include("proj_inc_current_list.php"); ?>
				</div>
			</div>

		</div>
		<div class='fl-wrap-col fs-small' <? //if($aSupInfo["supply_group_type"]!=3) echo("style='display:none'") ?> >
			<div class='fl-fix'>รายการ/Current Order</div>
			<div class='fl-wrap-row h-30 bg-head-2 row-header'>
				<div class='fl-fix w-50 fl-mid'><label><input id='chkCheckAll' type='checkbox' class='bigcheckbox'/>All</label></div>
				<div class='fl-fix w-150 fl-mid'>Cur. Proj</div>
				<div class='fl-fill fl-mid'>Code / Name</div>
				<div class='fl-fix w-30 fl-mid'>Paid</div>
				<div class='fl-fix w-80 fl-mid'>Total</div>
			</div>

			<div id='divCurOrderList' class='fl-wrap-col fl-scroll'>
				<? if($aSupInfo["supply_group_type"]==3) include("supply_order_check_list.php");
				 ?>
			</div>
		</div>
	</div>
	<div class='fl-wrap-row h-30 row-color'>
		<div class='fl-fill fl-mid'>
			<input id='btnAddOrder' type='button' value='Submit' />
		</div>
	</div>	
</div>
<script>
	$(function(){
		<? echo($sJS); ?>
		calculateTotal();

		$("#dlgSOID #chkCheckAll").off("change");
		$("#dlgSOID #chkCheckAll").on("change",function(){
			iTotal=0;
			objVal = $("#dlgSOID .saveinput[data-keyid='dose_day']");
			if($(this).prop("checked")){
				$("#dlgSOID #divCurOrderList .order-row").each(function(ix,objx){

					$(objx).find(".chksalecode").prop("checked",true);
					iI = $(objx).attr("data-total");
					iTotal += iI*1;
				});
			}else{
				$("#dlgSOID #divCurOrderList .order-row input[type='checkbox']").prop("checked",false);
			}
			$(objVal).val(iTotal);
			calculateTotal();
		});

		$("#dlgSOID #divCurOrderList .chksalecode").off("change");
		$("#dlgSOID #divCurOrderList").on("change",".chksalecode",function(){
			objVal = $("#dlgSOID .saveinput[data-keyid='dose_day']");
			iTotal=0;
			$("#dlgSOID #divCurOrderList .order-row").each(function(ix,objx){
				if($(objx).find(".chksalecode").is(":checked")){
					iI = $(objx).attr("data-total");
					iTotal += iI*1;
				}
			});
			$(objVal).val(iTotal);
			calculateTotal();
		});

		$("#dlgSOID #divCurProjList .proj-row").off("click");
		$("#dlgSOID #divCurProjList").on("click",".proj-row",function(){
			objRow = $(this);
			sSaleId = $(this).attr('data-saleoptid');
			
			if(sSaleId!=""){
				$("#dlgSOID #ddlSaleOptId").val(sSaleId);
				$("#dlgSOID #ddlSaleOptId").trigger("change");
			}
		});


		$("#dlgSOID #btnAddOrder").off("click");
		$("#dlgSOID #btnAddOrder").on("click",function(){
			
			aData = getDataRow($("#dlgSOID"));
			aData.u_mode="add_supply_order";
			sURL="supply_a.php";
			//Code auto generate .Client Validation from server validation
			if(aData.dose_day <= 0 || aData.dose_day==""){
				objx = getKeyObj($("#dlgSOID"),"dose_day");
				$.notify("กรุณาใส่จำนวนรวม\r\nPlease enter the total amount.")
				$(objx).focus();
				return;
			}

			//Get list of checked
			sSup = ""; sOrd=""; sProjId=""; //$("#dlgSOID .saveinput[data-keyid='supply_unit']").html();
			sLabId = "";
			if(sProjId==""){
				
			}else{
				aData.projid=sProjId;
				$("#dlgSOID #divCurOrderList .order-row").each(function(ix,objx){
					if($(objx).find(".chksalecode").is(":checked")){
						if($(objx).attr('data-type')=="supply"){
							sSup+=((sSup=="")?"":",")+$(objx).attr("data-supcode");
							sOrd+=((sOrd=="")?"":",")+$(objx).attr("data-ordercode");
						}else{
							sLabId+=((sLabId=="")?"":",")+$(objx).attr("data-labid");
						}
						
					}
				});

				if(sSup!="") {
					aData.suplist=sSup;
					aData.ordlist=sOrd;
					

				}
				if(sLabId!="") aData.labid=sLabId;

			}


	        callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					closeDlg($("#dlgSOID #btnAddOrder"),"1");
				}else{
					$.notify(jRes.msg);
				}
	        });
		});




		$("#dlgSOID #ddlSaleOptId").off("change");
		$("#dlgSOID #ddlSaleOptId").on("change",function(){
			iPrice=$("#dlgSOID #ddlSaleOptId").find(":selected").attr("data-saleprice");
			setKeyVal($("#dlgSOID"),"sale_price",iPrice,false);
			calculateTotal();
		});

		$("#dlgSOID .sum-price").off("change");
		$("#dlgSOID .sum-price").on("change",function(){
			calculateTotal();
		});

		function calculateTotal(){
			sTotal = 0;
			
			//objX=getKeyObj($("#dlgSOID"),"sale_opt_id");
			//iPrice=$(objX).find(":selected").attr("data-saleprice");
			iPrice=getKeyVal($("#dlgSOID"),"sale_price");
			iAmt=getKeyVal($("#dlgSOID"),"dose_day");
			if(iPrice=="" || iAmt=="") sTotal = 0;
			else sTotal = iPrice*iAmt;
			setKeyVal($("#dlgSOID"),"total_price",sTotal);

		}
	});
</script>