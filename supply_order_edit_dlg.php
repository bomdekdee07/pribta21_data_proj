<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
include("array_post.php");
$sCode = getQS("supply_code");
$sClinicId = getSS("clinic_id");
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sMode=getQS("u_mode");
$sJS="";

$sOCode=getQS("order_code");

$sHtmlKeyId = getHiddenPk($sUid,$sColD,$sColT);

include("in_db_conn.php");
$aSup=array(); $isService=""; $isFound = false;
$query = "SELECT clinic_id,supply_name,ISO.supply_code,supply_lot,order_code,order_status,ISO.dose_day,ISO.dose_before,ISO.dose_breakfast,ISO.dose_lunch,ISO.dose_dinner,ISO.dose_night,ISO.sale_price,ISO.sale_opt_id,order_note,ISO.supply_desc,is_paid,is_pickup,total_price,total_price,is_service FROM i_stock_order ISO
LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISO.supply_code
LEFT JOIN i_stock_group ISG	ON ISG.supply_group_code = ISM.supply_group_code
LEFT JOIN i_stock_type IST	ON IST.supply_group_type = ISG.supply_group_type
WHERE uid=? AND collect_date=? AND collect_time=? AND ISO.supply_code=? AND order_code=?";
$stmt=$mysqli->prepare($query);
$stmt->bind_param("sssss",$sUid,$sColD,$sColT,$aPost["supply_code"],$aPost["order_code"]);
if($stmt->execute()){
	$result=$stmt->get_result();
	while($row=$result->fetch_assoc()){
		$aSup = $row;
		foreach ($row as $KeyId => $iVal) {
			$sJS .= "setKeyVal($(\"#dlgSOID\"),'".$KeyId."',".json_encode($iVal).");";
		}
		if($row["is_service"]=="1"){
			$sJS .= "$(\"#dlgSOID .supply-drug\").hide();";
		}
		if($row["is_paid"]=="1"){
			$sJS .= "$(\"#dlgSOID .sum-price\").prop(\"readonly\",true);
			$(\"#dlgSOID #ddlSaleOptId\").prop(\"disabled\",true);
			$(\"#dlgSOID .div-info\").html(\"ชำระเงินแล้ว..Item Paid\");
			$(\"#dlgSOID .div-info\").show();

			";
		}
	}
}

$sOptSale="";
$query="SELECT SO.sale_opt_id,sale_opt_name ,sale_price
FROM i_stock_price ISP
LEFT JOIN sale_option SO 
ON SO.sale_opt_id=ISP.sale_opt_id
WHERE SO.is_enable=1 AND ISP.supply_code=? ORDER BY SO.data_seq";

$stmt=$mysqli->prepare($query);
$stmt->bind_param("s",$sCode);
if($stmt->execute()){
	$stmt->bind_result($sale_opt_id,$sale_opt_name,$sale_price);
	while($stmt->fetch()){
		$sOptSale .= "<option value='$sale_opt_id' data-saleprice='$sale_price'>$sale_opt_name".": $sale_price บาท</option>";
	}
}

$mysqli->close();


if($sMode=="cashier"){
	$sJS .= "$('#dlgSOID .supply-drug').hide(); $('#dlgSOID .doctor-mode').hide(); $('#dlgSOID .cashier-mode').show();";
}
?>

<div id='dlgSOID' class='fl-wrap-col fs-small'>
	<div class='fl-wrap-row h-30 row-color fl-mid fw-b'>
		<? echo($sHtmlKeyId); ?>
		<input type='hidden' class='saveinput' data-pk='1' data-keyid='supply_code' />
		<input type='hidden' class='saveinput' data-pk='1' data-keyid='order_code' />
		<span class='saveinput' data-keyid='supply_name'></span>
	</div>
	<div class='fl-wrap-row h-30 row-color fl-mid'>
		<div class='fl-fix w-80 fl-mid'>
			ราคา
		</div>
		<div class='fl-wrap-row'>
			<SELECT id='ddlSaleOptId' class='saveinput fill-box h-25' data-odata data-keyid='sale_opt_id'>
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
		<div class='fl-fix w-80 lh-30'>
			จำนวน
		</div>
		<div class='fl-fix w-80 fl-mid'>
			<input class='fill-box saveinput h-25 sum-price' <? echo(($aSup["is_service"])?"":"readonly=\"true\""); ?> data-odata='' value='' data-keyid='dose_day' type='number' />
		</div>
		<div class='fl-fill fl-mid'>
			<span class='saveinput' data-keyid='supply_unit'></span>
		</div>
		<div class='fl-fix w-80 lh-30'>
			ราคา
		</div>
		<div class='fl-fix w-80 fl-mid'>
			<input class='fill-box saveinput h-25 sum-price' <? echo(($aSup["sale_price"])?"":"readonly=\"true\""); ?> data-odata='' value='' data-keyid='sale_price' type='number' />
		</div>

		<div class='fl-fill fl-mid'>
			<span>รวม</span>
		</div>
		<div class='fl-fix w-80 fl-mid'>
			<input class='fill-box saveinput h-25 sum-price btn-selected'  data-odata='' value='' data-keyid='total_price' readonly="true" />
		</div>
		<div class='fl-fill fl-mid'>
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
	<div class='fl-wrap-row h-30 row-color '>
		<div class='fl-fix w-80 fl-mid'>
			Note
		</div>
		<div class='fl-fill fl-mid'>
			<input class='fill-box saveinput h-25' data-odata='' value='' data-keyid='order_note' />
		</div>
	</div>
	<div class='fl-fill row-color'>
	</div>
	<div class='fl-wrap-row h-30 row-color'>
		<div class='fl-fill al-right'>
			<input id='btnAddOrder' type='button' value='Submit' />
		</div>
		<div class='fl-fix wper-50 div-info' style='color:red;display:none'></div>
	</div>

</div>

<script>
	$(function(){
		<? echo($sJS); ?>


		$("#dlgSOID #btnAddOrder").unbind("click");
		$("#dlgSOID #btnAddOrder").on("click",function(){
			aData = getDataRow($("#dlgSOID"));

			if(aData==""){
				$.notify("ไม่พบการเปลี่ยนแปลง\r\nNo data changed");
				return;
			}

			aData.u_mode="edit_supply_order";
			sURL="supply_a.php";
			//Code auto generate .Client Validation from server validation
			if(aData.dose_day <= 0 || aData.dose_day==""){
				objx = getKeyObj($("#dlgSOID"),"dose_day");
				$.notify("กรุณาใส่จำนวนรวม\r\nPlease enter the total amount.")
				$(objx).focus();
				return;
			}

	        callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					closeDlg($("#dlgSOID #btnAddOrder"),"1");
				}else{
					$.notify(jRes.msg);
				}
	        });
		});


		$("#dlgSOID #ddlSaleOptId").unbind("change");
		$("#dlgSOID #ddlSaleOptId").on("change",function(){
			iPrice=$(this).find(":selected").attr("data-saleprice");
			setKeyVal($("#dlgSOID"),"sale_price",iPrice);
			calculateTotal();
		});

		$("#dlgSOID .sum-price").unbind("change");
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
			setKeyVal($("#dlgSOID"),"total_price",sTotal,false);

		}
	});
</script>