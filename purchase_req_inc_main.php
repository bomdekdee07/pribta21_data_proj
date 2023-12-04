<?
include_once("in_session.php");
include_once("in_php_function.php");
$sToday=date("Y-m-d");
$sName=getSS("s_name");
$sReqId=getQS("request_id");
$bHideDetail=false;
$sJs="";
$sNow=date("Y-m-d H:i:s");
$sMode="";
$sReqD="";
$sReqT="";

$sSigSup="";
$sSigFin="";
$sSigApp="";

if($sReqId==""){
	$bHideDetail=true;
	$sJs.="setKeyVal($(\"#divRFBody\"),\"request_by_name\",getShowText(\"".urlencode($sName)."\"),true,'infoinput');
	setKeyVal($(\"#divRFBody\"),\"request_datetime\",getShowText(\"".urlencode($sNow)."\"),true,'infoinput');";
	$aReqDT=explode(" ",$sNow);
	$sReqD=$aReqDT[0];
	$sReqT=$aReqDT[1];

}else{
	include("in_db_conn.php");
	$query="SELECT request_id,ISRL.section_id,request_by,request_title,request_detail,request_datetime,require_date,request_status,delivery_to,delivery_other,request_type,request_proj,finance_req_no,finance_rec_date,finance_rec_by,recieved_date,request_po_no,PS.s_name AS request_by_name,PS1.s_name AS finance_rec_by_name 
	FROM i_stock_request_list ISRL
	LEFT JOIN p_staff PS ON PS.s_id = ISRL.request_by
	LEFT JOIN p_staff PS1 ON PS1.s_id = ISRL.finance_rec_by
	WHERE ISRL.request_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);

	if($stmt->execute()){
		$result = $stmt->get_result();
      	while($row = $result->fetch_assoc()) {
  			$aReqDT=explode(" ",$row["request_datetime"]);
			$sReqD=$aReqDT[0];
			$sReqT=$aReqDT[1];
      		foreach ($row as $sCol => $sVal) {
      			$sJs.="setKeyVal($(\"#divRFBody\"),\"".$sCol."\",getShowText(\"".urlencode($sVal)."\"),true,'infoinput');";
      		}
      		$sMode=$row["request_status"];
      		$sName=$row["request_by_name"];
      	}
    }else{
    	error_log($stmt->error);
    }


	//Get Signature List
	$query="SELECT IDD.sig_code,sig_value,s_name FROM i_doc_data IDD
	LEFT JOIN p_staff PS
	ON PS.s_id=IDD.sig_value
	WHERE IDD.option_code=? AND IDD.doc_code='PURCHASE_REQ'";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);
	if($stmt->execute()){
		$stmt->bind_result($sig_code,$sig_value,$s_name);
		while ($stmt->fetch()) {
			if($sig_code=="SUPERVISOR") $sSigSup=$s_name;
			if($sig_code=="FINANCE") $sSigFin=$s_name;
			if($sig_code=="APPROVE") $sSigApp=$s_name;
		}	
	}



	$mysqli->close();
}
$sJs.="fncChangeMode('".$sMode."');";
?>
<style>
	.pr-qty{
		background-color: #c3f69d;
	}
	.pr-vat{
		background-color: #ffdb79;
	}
	.pr-discount{
		background-color: #91e49f;
	}
	.pr-final{
		background-color: #a695ff;
	}
	.supply-row{
		border-bottom: 1px solid grey;
	}
</style>
<div id='divRF' class='fl-wrap-col' >
	<div id='divRFBody' class='fl-wrap-col fs-xsmall'>
		<div class='fl-wrap-row h-25 fl-mid fw-b row-color row-hover'>
			<div class='fl-fix w-20'></div>
			<div class='fl-fix w-200'>
				Status : <SELECT disabled='true' class='infoinput' data-keyid='request_status' data-odata>
				<option value=''>NEW</option>
				<option value='0'>Pending</option>
				<option value='1'>Submitted</option>
				<option value='CC'>Cancelled</option>
				<option value='CF'>Confirmed</option>
				<option value='FIN'>Item Imported</option>
				</SELECT>
			</div>
			<div class='fl-fill fl-mid'>แบบฟอร์มการขอจัดซื้อ-จัดจ้าง</div>
			<div class='fl-fix w-200'><input readonly="true" class='infoinput w-fill' value='' data-keyid='request_id'  data-pk='1' data-odata /> </div>
			<div class='fl-fix w-20'></div>
		</div>
		<div class='fl-wrap-row f-border fp-5 row-color h-110' >
			<div class='fl-wrap-col '>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-200 al-left'>Request By (ชื่อผู้ขอ) :</div>
					<div class='fl-fill'><input class='w-fill infoinput request-pr' readonly="true" value='' data-keyid='request_by_name' data-odata /></div>
					<div class='fl-fix w-100'>Date (วันที่) :</div>
					<div class='fl-fix w-120'><input class='w-fill infoinput request-pr' data-keyid='request_datetime' data-odata readonly="true" value='' /></div>
				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-200 al-left'>Required Date (วันที่ต้องการ) :</div>
					<div class='fl-fix w-100'><input class='w-fill infoinput date-data request-pr' readonly="true" data-keyid='require_date' data-require='1' value='' data-odata='' /></div>

				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-200 al-left'>Delivery to (สถานที่ส่งของ) :	</div>
					<div class='fl-fill fl-vmid'>
						<label><input type='radio' name='delivery_to' value='IHRI' data-odata='' data-keyid='delivery_to' class='bigcheckbox infoinput request-pr' checked="true" />IHRI</label>
					</div>
					<div class='fl-fill fl-vmid'>
						<label><input type='radio' name='delivery_to' value='HIVNAT'  class='bigcheckbox request-pr' />HIVNAT</label>
					</div>
					<div class='fl-fill fl-vmid'>
						<label><input type='radio' name='delivery_to' value='OPR'  class='bigcheckbox request-pr'  />อปร. OPR</label>
					</div>
					
				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-200 '></div>
					<div class='fl-fix w-170 fl-vmid'><label class='lh-25'><input type='radio' name='delivery_to' value='OTHER' class='bigcheckbox request-pr'  />Other (อื่นๆ โปรดระบุ) : </label></div>
					<div class='fl-fill fl-vmid'>
						<input class='w-fill infoinput request-pr' data-keyid='delivery_other' data-odata=''  />
					</div>
				</div>
			</div>
			<div class='fl-wrap-col w-200 f-border'>
				<div id='btnShowUpload' class='fabtn fl-fix h-25 fl-mid row-color row-hover-2'>
					<i class=' fas fa-upload' >Upload File</i>
				</div>
				<div id='divFUL' class='fl-wrap-col fl-auto fs-xsmall'>
					<? $_GET["u_mode"]="request_file_list"; if($sReqId!="") include("purchase_req_a.php"); ?>
				</div>
			</div>
			<div class='fl-wrap-col w-300 fpl-5'>
				<div class='fl-fill fl-mid fw-b h-25'>
					ส่วนของเจ้าหน้าที่จัดซื้อ
				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-170'>
						Request No. (เลขที่) :
					</div>
					<div class='fl-fill'>
						<input class='fill-box infoinput request-po' data-odata='' data-keyid='finance_req_no' disabled="true" value='' />
					</div>
				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-170'>
						Received Date (วันที่รับ) :
					</div>
					<div class='fl-fill'>
						<input class='fill-box infoinput request-po' data-odata='' data-keyid='recieved_date' disabled="true" value=''/>
					</div>
				</div>
				<div class='fl-wrap-row h-25'>
					<div class='fl-fix w-170'>
						Refer Po. No. :
					</div>
					<div class='fl-fill'>
						<input class='fill-box infoinput request-po' data-odata='' data-keyid='request_po_no' disabled="true" value='' />
					</div>
				</div>
			</div>
		</div>
		<div class='fl-wrap-row  h-25 row-color lh-25'>
			<div class='fl-fix w-200 fl-vmid lh-25 fpl-5'>
				Type(ระบุประเภท):
			</div>
			<div class='fl-fill'>
				<label class='fl-vmid'><input type='radio' name='request_type' value='P' class='bigcheckbox infoinput request-pr' data-keyid='request_type' checked="true" data-odata /> Product (สินค้า)</label>
			</div>

			<div class='fl-fill'>
				<label class='fl-vmid'><input type='radio' name='request_type' value='S' class='bigcheckbox request-pr' />Service (บริการ)</label>
			</div>

			<div class='fl-fix w-500'>
				<label class='fl-vmid'><input type='radio' name='request_type' value='C' class='bigcheckbox request-pr' />Consultant/Temporary Staff (ที่ปรึกษาและเจ้าหน้าที่ชั่วคราว)</label>
			</div>

			<div class='fl-fill'>
				<label class='fl-vmid'><input type='radio' name='request_type' value='O' class='bigcheckbox request-pr' />Other (อื่นๆ)</label>
			</div>
		</div>
		<div class='fl-wrap-row fp-5 h-25 row-color'>
			<div class='fl-fix wper-40 fl-vmid' style='background-color: #d8e4bc'>Project โครงการที่เกี่ยวข้องกับการสั่งซื้อและงบประมาณ ดูจาก หมายเหตุ 1 ด้านล่าง</div>
			<div class='fl-fill fl-mid'><input class='w-fill infoinput h-20 request-pr' data-odata='' data-keyid='request_proj' value='IHRI' /></div>
		</div>
		<div class='fl-wrap-row fp-5 h-30 row-color'>
			<div class='fl-fix wper-40' >Objective (วัตถุประสงค์ของการขอซื้อ)</div>
			<div class='fl-fill fl-mid'><input class='w-fill infoinput request-pr' data-odata='' data-keyid='request_title' data-require='1' value='' /></div>
			<div class='fl-fix w-100'><SELECT id='ddlQuickTxt' class='w-fill h-fill request-pr'>
				<option value=''>--Quick Text--</option>
				<option value='เพื่อจัดซื้อยาสำหรับใช้ที่พริบตาแทนเจอรีนสหคลินิก '>เพื่อจัดซื้อยาสำหรับใช้ที่พริบตาแทนเจอรีนสหคลินิก</option>
				<option value='เพื่องานประชุม '>เพื่องานประชุม</option>
				<option value='เพื่อขอจ้างที่ปรึกษาและเจ้าหน้าที่ชั่วคราว '>เพื่อขอจ้างที่ปรึกษาและเจ้าหน้าที่ชั่วคราว </option>
			</SELECT></div>
		</div>

		<div id='divSupplyList' class='fl-wrap-col' style='display:none'>
			<div class='fl-wrap-row row-color-2 row-header h-30 fw-b' style='border-bottom: 1px solid silver'>
				<div class='fl-fix w-30'>
					
				</div>
				<div class='fl-wrap-col'>
					<div class='fl-wrap-row'>
						Supply Code
					</div>
					<div class='lh-15 fl-fill al-left'>
						Name
					</div>
				</div>
				
				<div class='fl-wrap-col w-70 pr-qty'>
					<div class='fl-fill lh-15'>
						จำนวน ซื้อ*
					</div>
					<div class='lh-15 fl-fill'>
						จำนวนแถม
					</div>
				</div>

				<div class='fl-fix w-70 fl-mid lh-30'>
					หน่วย
				</div>

				<div class='fl-wrap-col w-70'>
					<div class='fl-fill fl-mid ' title='ราคาต่อหน่วย ไม่มี Vat ไม่รวมส่วนลด&#013;'>
						ราคา/หน่วย
					</div>
					<div class='fl-fill pr-discount fl-mid'>
						P/U +%
					</div>
				</div>

				<div class='fl-wrap-col w-80 '>
					<div class='fl-wrap-row h-15 fl-mid pr-vat'>
						P/U +Vat
					</div>
					<div class='fl-wrap-row h-15 fl-mid pr-final'>
						P/U +% +Vat
					</div>
				</div>


				<div class='fl-wrap-col w-80 pr-discount'>
					<div class='fl-wrap-row h-15 fl-mid'>
						%ลดก่อนVat
					</div>
					<div class='fl-wrap-row h-15 fl-mid'>
						บาทลดก่อนVat
					</div>
				</div>

				<div class='fl-wrap-col w-80 pr-vat'>
					<div class='fl-wrap-row h-15 fl-mid'>
						%ลดหลังVat
					</div>
					<div class='fl-wrap-row h-15 fl-mid'>
						บาทลดหลังVat
					</div>
				</div>




				<div class='fl-wrap-col w-70'>
					<div class='h-15 fl-fill fl-mid'>
						รวม
					</div>
					<div class='h-15 fl-fill pr-discount fl-mid'>
						รวม-%
					</div>
				</div>

				<div class='fl-wrap-col w-70'>
					<div class='h-15 fl-fill pr-vat fl-mid'>
						รวม+Vat
					</div>
					<div class='h-15 fl-fill pr-final fl-mid'>
						รวม-%+Vat-%
					</div>
				</div>



				<div class='lh-15 fl-fix w-90  fl-mid'>
					Project
				</div>
				<div class='lh-15 fl-fix w-90 fl-mid'>
					Account
				</div>
				<div class='lh-30 fl-wrap-col w-50' style='color:green'>
					<div class='fl-fill fl-mid'><input type='checkbox' class='bigcheckbox' id='chkCalAuto' checked="true" /> </div>
					<div class='fl-fix h-10'><label for='chkCalAuto' class='fl-mid h-10 lh-10 w-50'>Auto</label></div>
				</div>
			</div>

			<div class='fl-wrap-row row-color-2 h-40 input-row row-header'>
				<div id='btnClearInput' class='fl-fix w-30 fl-mid fabtn'>
					<i class='fa fa-broom fa-2x'></i>
				</div>
				<div class='fl-wrap-col'>
					<div class='fl-wrap-row'>
						<div class='lh-20 fl-fix w-60'>
							<input class='w-fill saveinput h-20' title='รหัส' data-keyid='supply_code' data-odata='' readonly="true" data-pk='1' disabled="true" />
						</div>
						<div  id='btnSearchSupply' class='lh-20 fl-fix w-30 fabtn fl-mid'><i class='fas fa-search fa-lg'></i></div>
					</div>
					<div class='lh-20 fl-fill al-left'>
						<input class='w-fill saveinput h-20' title='ค้นหาชื่อยา หรือ พิมพ์รหัส กด Enter' data-keyid='request_supply_note' data-odata />
					</div>
				</div>
				
				<div class='fl-wrap-col w-70 pr-qty'>
					<div class='fl-fill lh-20'>
						<input class='w-fill saveinput h-20 pr-qty' title='จำนวนซื้อ' type='number' data-keyid='request_amt' data-odata />
					</div>
					<div class='lh-20 fl-fill'>
						<input class='w-fill saveinput h-20 pr-qty' title='จำนวนของแถม&#013;*หากจำนวนของที่ได้รับมากกว่าสินค้าที่สั่งซื้อ ให้ใส่จำนวนของที่แถม&#013;เช่น&#013;สั่งซื้อ 100 ชิ้น ได้แถม 1 ชิ้น ในช่องนี้ให้ใส่ 1' type='number' value='0' data-keyid='request_exact_amt' data-odata />
					</div>
				</div>

				<div class='fl-fix w-70 fl-mid lh-50'>
					<SELECT class='w-fill saveinput' data-keyid='request_unit' title='หน่วยของสินค้าที่ขอซื้อ' data-odata></SELECT>
					<!-- input class='w-fill saveinput' title='หน่วยของสินค้า' data-keyid='request_unit' disabled="true" readonly="true" /-->
				</div>

				<div class='fl-wrap-col w-70'>
					<div class='fl-fill fl-mid' title='ราคาต่อหน่วย ไม่มี Vat ไม่รวมส่วนลด&#013;'>
						<input class='w-fill saveinput h-20' type='number' data-keyid='request_item_price' data-odata  />
					</div>
					<div class='fl-fill pr-discount fl-mid'>
						<input class='w-fill saveinput pr-discount h-20' type='number' data-keyid='request_item_price_discount' data-odata  title='ราคาต่อหน่วย รวมส่วนลด ไม่มี Vat'/>
					</div>
				</div>


				<div class='fl-wrap-col w-80 pr-vat'>
					<div class='fl-fix h-20'>
						<input class='w-fill saveinput h-20 pr-vat' title='ราคาสินค้าต่อหน่วย ราคาสินค้า+Vat ยังไม่หักส่วนลด&#013;= (ราคาxจำนวน) + Vat' type='number' data-keyid='request_item_price_vat'  data-odata='0' value='0' />
					</div>
					<div class='fl-fix h-20'>
						<input class='w-fill saveinput h-20 pr-final' title='ราคาสินค้าต่อหน่วย หักส่วนลด รวม Vat &#013;= [(ราคาxจำนวน)-ส่วนลด] + Vat' type='number' data-keyid='request_item_price_final' data-odata='0' value='0' />
					</div>

				</div>


				<div class='fl-wrap-col w-80 pr-discount'>
					<div class='fl-wrap-row h-20'>
						<div class='fl-fix w-10 fl-mid'>%</div>
						<div class='fl-fill lh-20'>
							<input class='w-fill saveinput h-20 pr-discount' title='% ส่วนลด ก่อน Vat&#013;ใส่ตัว % ในช่องนี้ ระบบจะคำนวน ยอดรวม ส่วนลดให้* &#013;*กรณียอดรวมส่วนลดเป็น 0 หรือ เว้นว่างไว้' type='number' data-keyid='discount_before_vat' maxlength="3" data-odata='0' value='0' />
						</div>
					</div>
					<div class='fl-wrap-row h-20'>
						<div class='fl-fix w-10 fl-mid'>B</div>
						<div class='lh-20 fl-fill'>
							<input class='w-fill saveinput h-20 pr-discount' title='ยอดรวมส่วนลด ก่อน Vat &#013;ใส่ตัวเลขเงินในช่องนี้ ระบบจะคำนวน % ส่วนลดให้* &#013;*กรณี % ส่วนลดเป็น 0 หรือ เว้นว่างไว้&#013;= {[(จำนวน x ราคา) x ส่วนลด ] + Vat} / 100' type='number' data-keyid='discount_before_vat_baht' data-odata='0' value='0' />
						</div>
					</div>
				</div>

				<div class='fl-wrap-col w-80 pr-vat'>
					<div class='fl-wrap-row h-20'>
						<div class='fl-fix w-10 fl-mid'>%</div>
						<div class='fl-fill lh-20'>
							<input class='w-fill saveinput h-20 pr-vat' title='% ส่วนลด หลัง Vat&#013;ใส่ตัว % ในช่องนี้ ระบบจะคำนวน ยอดรวม ส่วนลดให้* &#013;*กรณียอดรวมส่วนลดเป็น 0 หรือ เว้นว่างไว้' type='number' data-keyid='discount_after_vat' maxlength="3" data-odata='0' value='0' />
						</div>
					</div>
					<div class='fl-wrap-row h-20'>
						<div class='fl-fix w-10 fl-mid'>B</div>
						<div class='lh-20 fl-fill'>
							<input class='w-fill saveinput h-20 pr-vat' title='ยอดรวมส่วนลด หลัง Vat &#013;ใส่ตัวเลขเงินในช่องนี้ ระบบจะคำนวน % ส่วนลดให้* &#013;*กรณี % ส่วนลดเป็น 0 หรือ เว้นว่างไว้&#013;= ((ราคา + Vat)*จำนวน)-ส่วนลด' type='number' data-keyid='discount_after_vat_baht' data-odata='0' value='0' />
						</div>
					</div>
				</div>


				


				<div class='fl-wrap-col w-70 fl-mid'>
					<div class='h-20 fl-fill'>
						<input class='w-fill saveinput h-20' type='number' data-keyid='request_total_price' data-odata  title='ยอดรวม ไม่มี Vat ไม่รวมส่วนลด&#013;= (จำนวน x ราคา)'/>
					</div>
					<div class='h-20 fl-fill'>
						<input class='w-fill saveinput pr-discount h-20' type='number' data-keyid='request_total_price_discount' data-odata  title='ราคารวม รวมส่วนลด&#013;= ((จำนวน x ราคา)) - ส่วนลด'/>>
					</div>
				</div>

				<div class='fl-wrap-col w-70 pr-qty fl-mid'>
					<div class='h-20 fl-fill'>
						<input class='w-fill saveinput h-20 pr-vat' type='number' data-keyid='request_total_price_vat' data-odata title='ยอดรวม รวมVat ไม่รวมส่วนลด&#013;= จำนวน x ราคา' />
					</div>
					<div class='h-20 fl-fill'>
						<input class='w-fill saveinput h-20 pr-final' type='number' data-keyid='request_total_price_final' data-odata title='ยอดรวม รวมVat รวมส่วนลด &#013;= {[(จำนวนxราคา)-ส่วนลด] + Vat} - ส่วนลด' />
					</div>
				</div>



				<div class='lh-15 fl-fix w-90  fl-mid'>
					<input class='w-fill saveinput' data-keyid='request_project' data-odata />
				</div>
				<div class='lh-15 fl-fix w-90 fl-mid'>
					<input class='w-fill saveinput'  data-keyid='request_account' data-odata />
				</div>
				<div id='btnAddRequestItem' class='lh-15 fl-fix w-50 fl-mid fabtn' style='color:green'>
					<i class='fa fa-plus fa-2x'></i>
				</div>
				<div id='btnLoader' class='fl-fix w-50 fl-mid' style='display:none'>
					<i class='fa fa-spinner fa-spin fa-2x'></i>
				</div>
			</div>

			<div id="divPRIN" class='fl-wrap-col fl-scroll'>
				<? include("purchase_req_item_list.php"); ?>
			</div>
		
			<div class='fl-wrap-row row-color-2 h-20'>
				<div class='fl-fix w-90'>Summary</div>
				<div class='fl-fill'></div>
				<div class='fl-fix w-30 pr-vat fl-mid'>Vat</div>
				<div class='fl-fix w-30 pr-vat fl-mid'><input id='txtRequestVat' class='h-20 w-fill pr-summary request-pr' value='7' maxlength="3" /></div>
				<div class='fl-fix w-15 fw-b pr-vat  fl-mid'>%</div>
				<div class='fl-fix w-80 pr-vat fl-mid'><input id='txtRequestVatBaht' class='h-20 w-fill pr-summary request-pr' value=''  /></div>
				<div class='fl-fix w-40 fw-b pr-vat  fl-vmid'>บาท</div>

				<div class='fl-fix w-20 pr-discount'></div>
				<div class='fl-fix w-80 pr-discount fl-vmid'>ส่วนลด/ส่วนต่าง</div>
				<div class='fl-fix w-80 pr-discount fl-mid'><input id='txtDiscount' class='h-20 w-fill pr-summary request-pr' value='0' title='ส่วนลด หรือ ส่วนต่างจากการปัดเศษทศนิยม' /></div>
				<div class='fl-fix w-40 fw-b pr-discount fl-vmid'>บาท</div>


				<div class='fl-fix w-20 '></div>
				<div class='fl-fix w-80  fl-vmid'>ราคาก่อน Vat</div>
				<div class='fl-fix w-80 fl-mid '><input id='txtTotalPrice' class='h-20 w-fill pr-summary request-pr' value='0' title='ราคารวม ก่อน Vat หักส่วนลดแล้ว' /></div>
				<div class='fl-fix w-40 fw-b  fl-vmid'>บาท</div>

				<div class='fl-fix w-20 pr-final'></div>
				<div class='fl-fix w-80 pr-final fl-vmid'>ราคารวม Vat</div>
				<div class='fl-fix w-80 fl-mid pr-final'><input id='txtTotalFinal' class='h-20 w-fill pr-summary request-pr' value='0' title='ราคารวมสุดท้าย หักส่วนลด และ รวม Vat แล้ว' /></div>
				<div class='fl-fix w-40 fw-b pr-final fl-vmid'>บาท</div>


			</div>
			<div class='fl-wrap-row h-80 row-color-2'>
				<div class='fl-wrap-col f-border'>
					<div class='fl-fix h-20 fl-mid f-border'>Prepared by (ผู้จัดทำ)</div>
					<div class='fl-fill'><? echo($sName); ?></div>
				</div>
				<div class='fl-wrap-col f-border'>
					<div class='fl-fix h-20 fl-mid f-border'>Verified by (ผู้ตรวจสอบ)</div>
					<div class='fl-wrap-row'>
						<div class='fl-wrap-row f-border  row-hover row-color'>
							<div class='fl-wrap-col'>
								<div class='fl-fill fl-mid'><input class='btnsign' data-sigcode='SUPERVISOR' type='button' title='Digital Sign for Supervisor' value='Sign' /></div>
								<div class='fl-fix h-15 fs-xsmall fl-mid doc-sig-name fl-mid' data-sigcode='SUPERVISOR'><? echo($sSigSup); ?></div>	
							</div>
							<div class='fl-fix w-30 fl-mid' title='Specific the user to sign and send an email to them.' style='display:none'><i class='fa fa-user fa-2x'></i></div>
						</div>
						<div class='fl-wrap-row f-border  row-hover row-color'>
							<div class='fl-wrap-col'>
								<div class='fl-fill fl-mid'><input  class='btnsign' data-sigcode='FINANCE' type='button' title='Digital Sign for Finance' value='Sign' /></div>	
								<div class='fl-fix h-15 fs-xsmall fl-mid doc-sig-name fl-mid' data-sigcode='FINANCE'><? echo($sSigFin); ?></div>	
							</div>
							<div class='fl-fix w-30 fl-mid' title='Specific the user to sign and send an email to them.' style='display:none'><i class='fa fa-user fa-2x'></i></div>
						</div>

					</div>
					<div class='fl-wrap-row h-20'>
						<div class='fl-fill f-border'>Supervisor</div>
						<div class='fl-fill f-border'>Finance Representative</div>
					</div>
				</div>
				<div class='fl-wrap-col f-border'>
					<div class='fl-fix h-20 fl-mid f-border'>Approved by (ผู้อนุมัติ)</div>
					<div class='fl-wrap-row f-border  row-hover row-color'>
						<div class='fl-wrap-col'>
							<div class='fl-fill fl-mid'><input  class='btnsign' data-sigcode='APPROVE' type='button' title='Digital Sign for Approve' value='Sign Approve' /></div>	
							<div class='fl-fix h-15 fs-xsmall fl-mid doc-sig-name fl-mid' data-sigcode='APPROVE'><? echo($sSigApp); ?></div>	
						</div>
						<div class='fl-fix w-30 fl-mid' title='Specific the user to sign and send an email to them.' style='display:none'><i class='fa fa-user fa-2x'></i></div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div class='fl-wrap-row h-40 row-color'>
		<div class='fl-fix w-100 fl-mid'>
			<input id='btnSaveRequest' type='button' class='save-btn fill-box' value='Save' style='display:none' title='Save/Update the request form.' />
			<i id='btnSaveRequest-loader' class='fa fa-spinner fa-spin fa-2x' style='display:none'></i>
		</div>
		<div class='fl-fill'></div>
		<div class='fl-fix w-100 fl-mid'>
			<input id='btnCancelRequest' type='button' class='save-btn fill-box' value='Cancel PR' style='display:none;background-color: red;color:white' title='Cancelled the request form.' />
		</div>
		<div class='fl-fill'></div>
		<div id='btnPrintPR' class='fl-fix w-250 fl-mid'>
			<? 
				 if($sReqId!="") {$_GET["doc_group"]="PURCHASE_REQ"; $_GET["uid"]=$sReqId; $_GET["coldate"]=$sReqD;  $_GET["coltime"]=$sReqT; include("document_sys_bt.php");}
			?>
			<!-- input id='btnPrintPR' type='button' class='save-btn fill-box' value='Print PR' style='display:none' title='Print Purchase Request.' /-->
		</div>
		<div class='fl-fix w-50 fl-mid'></div>
		<div id='btnPrintPO' class='fl-fix w-250 fl-mid'>
			<? 
				 if($sReqId!="") {$_GET["doc_group"]="B_PO"; $_GET["uid"]=$sReqId; $_GET["req_id"]=$sReqId; $_GET["coldate"]=$sReqD;  $_GET["coltime"]=$sReqT; include("document_sys_bt.php");}
			?>
		</div>
		<div class='fl-fill'></div>
		<div class='fl-fix w-100 fl-mid'>
			<input id='btnSubmitRequest' title='Submit the request. It will no longer be able to updated' type='button' class='save-btn fill-box' value='Submit'  style='display:none' />
			<input id='btnCancelSubmit' type='button' class='save-btn fill-box' value='Cancel Submit' style='display:none;background-color: red;color:white' title='Cancelled submitted Only import is not done.' />
		</div>
		

		<div class='fl-fix w-100 fl-mid'>
			<input id='btnImportRequest' title='Import request item(s) to the inventory' type='button' class='save-btn fill-box' value='Import Item' style='display:none' />
		</div>
		<div id='btnImportLog' title='View Import Log' class='fabtn save-btn fl-fix w-40 h-30 fl-mid' style='display:none'>
			<i class='fas fa-info-circle fa-lg'></i>
		</div>
	</div>
</div>

<script>
var iAutoCal = false;
$(document).ready(function(){
	fncCalculateSummary();
	$("#divRFBody .infoinput[data-keyid='require_date'],#divRFBody .infoinput[data-keyid='recieved_date']").datepicker({
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true
	});

	$("#divRF #btnCancelRequest").off("click");
	$("#divRFBody").on("click","#btnClearInput",function(){

	});

	$("#divRF .btnsign").off("click");
	$("#divRF").on("click",".btnsign",function(){
		objPrin=$(this).closest("#divRF");
		sSigCode=$(this).attr("data-sigcode");
		sReqId=getKeyVal(objPrin,"request_id","infoinput");

		sUrl="sign_doc_dlg.php?doc_code=PURCHASE_REQ&sig_code="+sSigCode+"&option_code="+sReqId;
		showDialog(sUrl,"Sign document for Supervisor ","350","500","",
		function(sResult){
			//CLose function
			if(sResult != ""){
				$("#divRF .doc-sig-name[data-sigcode='"+sSigCode+"']").html(sResult);
			}
		},false,function(){});
	});

	$("#divRFBody #btnClearInput").off("click");
	$("#divRFBody").on("click","#btnClearInput",function(){
		fncClearInput();
	});

	$("#divRFBody #btnSearchSupply").off("click");
	$("#divRFBody").on("click","#btnSearchSupply",function(){
		fncShowSearchSupply();
	});

	$("#divRFBody .saveinput").unbind("keypress");
	$("#divRFBody").on("keypress",".saveinput",function(e){
		sKeyId=$(this).attr("data-keyid");
		if(sKeyId=="request_supply_note"){
			if(e.which == 13) fncShowSearchSupply($(this).val().trim());
		}
	});

	$("#divRFBody .saveinput").off("change");
	$("#divRFBody").on("change",".saveinput",function(e){
		objRow=$(this).closest(".input-row");
		sKeyId=$(this).attr("data-keyid");
		iAmt=getKeyVal(objRow,"request_amt")*1;

		if(iAutoCal || iAmt==0 || sKeyId=="request_supply_note") return;
		iAutoCal = true;
		sVal = getKeyVal(objRow,sKeyId)*1;
		iVat=$("#divRFBody #txtRequestVat").val()*1;

		//If auto calculate has been checked
		bAuto = $("#divRFBody #chkCalAuto").is(":checked");
		if(bAuto && iAmt > 0){
			if(sKeyId=="request_amt"){
				iItem = Number(getKeyVal(objRow,"request_item_price")*1);
				iTotal=Number(iAmt*iItem);
				if(sVal>0 && iItem>0){
					setKeyVal(objRow,"request_item_price_vat",Number(iItem+(iItem*(iVat/100))),false);
					setKeyVal(objRow,"request_total_price",iTotal,false);
					setKeyVal(objRow,"request_total_price_vat",Number(iTotal+(iTotal*(iVat/100))),false);
				}
				//setKeyVal(objRow,"request_exact_amt",sVal);
			}else if(sKeyId=="request_item_price"){
				iTotal = Number(iAmt*sVal);
				setKeyVal(objRow,"request_item_price_vat",Number(sVal+(sVal*(iVat/100))),false);
				setKeyVal(objRow,"request_total_price",iTotal,false);
				setKeyVal(objRow,"request_total_price_vat",Number(iTotal+(iTotal*(iVat/100))),false);
			}else if(sKeyId=="request_item_price_vat"){
				iItem = Number(sVal -((sVal*iVat)/(100+iVat)));
				iTotal = Number(iAmt*iItem);
				setKeyVal(objRow,"request_item_price",iItem,false);
				setKeyVal(objRow,"request_total_price",iTotal,false);
				setKeyVal(objRow,"request_total_price_vat",Number(sVal*iAmt),false);
				
			}else if(sKeyId=="request_total_price"){
				iItem=Number(sVal/iAmt);
				iTotal=Number(iAmt*iItem);
				setKeyVal(objRow,"request_item_price",iItem,false);
				setKeyVal(objRow,"request_item_price_vat", Number(iItem+(iItem*(iVat/100))) ,false);
				setKeyVal(objRow,"request_total_price_vat",Number(iTotal+(iTotal*(iVat/100))),false);
			}else if(sKeyId=="request_total_price_vat"){
				iItemV=Number(sVal/iAmt);
				//iVatBaht=sVal-((sVal*iVat)/(iVat+100));
				iItem=Number(iItemV-((iItemV*iVat)/(iVat+100)));
				iTotal=Number(iAmt*iItem);
				setKeyVal(objRow,"request_item_price",iItem,false);
				setKeyVal(objRow,"request_item_price_vat", iItemV ,false);
				setKeyVal(objRow,"request_total_price",iTotal,false);
				//setKeyVal(objRow,"request_total_price_final",iTotal);
			}else if(sKeyId=="discount_before_vat_baht"){
				//Calculate % for discount
				iItemP=getKeyVal(objRow,"request_item_price")*1;
				if(iItemP > 0){
					setKeyVal(objRow,"discount_before_vat",Number((sVal/(iAmt*iItemP))*100) ,false);
				}
			}else if(sKeyId=="request_total_price_final"){
				//Edit Final Price
				var iTotalFinal=Number(sVal); //240075
				var iTotal=Number((iTotalFinal*100)/(100+iVat)); //224,369
				var iItem=Number(iTotal/iAmt); //990
				var iDiscA=Number(getKeyVal(objRow,"discount_after_vat"));
				var iItemV=Number(iItem+((iItem*iVat)/100));
				var iTotalV=Number(iItemV*iAmt);

				if(iDiscA>0){
					iTotalV=Number((iTotalFinal*100)/(100-iDiscA)); //247500;
					iTotal=Number((iTotalV*100)/(100+iVat));//231,308.41
					iVatBaht=Number(iTotalV-iTotal); //16,191.59
					iItem = Number(iTotal/iAmt); //925.23
					iItemV=Number(iItem+((iItem*iVat)/100));//990
					
				}
				iDiscB=getKeyVal(objRow,"discount_before_vat");
				if(iDiscB>0){
					iDiscBBaht=Number((iItem*iDiscB)/100);
					iItemD=Number(iItem-iDiscBBaht);
					iItem = Number(iItem-((iItem*iDiscB)/100));
					iItemV= Number(iItem+((iItem*iVat)/100));
					iTotal = Number(iItem*iAmt);
					iTotalV = Number(iTotal+((iTotal*iVat)/100));

				}

				setKeyVal(objRow,"request_item_price",iItem,false);
				setKeyVal(objRow,"request_item_price_vat",iItemV,false);
				setKeyVal(objRow,"request_total_price",iTotal,false);
				setKeyVal(objRow,"request_total_price_vat",iTotalV,false);

			}

			if(sKeyId=="discount_after_vat_baht" || sKeyId=="discount_before_vat_baht"){
				//Calculate % for discount After Vat
				iItemP=getKeyVal(objRow,"request_item_price")*1;

				if(iItemP > 0){
					iDiscB=getKeyVal(objRow,"discount_before_vat");
					iDiscBBaht = 0;
					if(iDiscB>0) iDiscBBaht = ((iItem*iDiscB)/100);
					iItemD = iItem-iDiscBBaht;
					iItemFinal = iItemD+((iItemD*iVat)/100);

					iDiscABaht = sVal;
					iTotalFinal=iItemFinal*iAmt;
					iDiscA=0;
					//(TotalDisc*100)/Disc = Money
					if(iDiscABaht>0) iDiscA=(sVal/(iTotalFinal/100));

					setKeyVal(objRow,"discount_after_vat",iDiscA ,false);
				}
			}

			iAutoCal = false;
			fncCalculateFinal();
			
		}
	});

	$("#divRFBody .btndelete").off("click");
	$("#divRFBody").on("click",".btndelete",function(){
		if(confirm("ยืนยันลบข้อมูล?\r\nConfirm remove the data?")==false) return;
		objThis=$(this);
		objPrin=$(this).closest("#divRFBody");
		objRow=$(this).closest(".supply-row");
		sReqId=getKeyVal(objPrin,"request_id","infoinput");
		iRowNo=$(objRow).attr('data-rowno');
		sSupCode=$(objRow).attr('data-supcode');
		objLoad=$(objRow).find(".btndelete-loader");
		aData={u_mode:"request_item_show_remove",supply_code:sSupCode,request_item_no:iRowNo,request_id:sReqId};
		startLoad(objThis,objLoad);
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
         	$.notify("Item Removed","success");
         	$(objRow).remove();
         	fncCalculateSummary();
         }else{
         	$.notify(jRes.msg);
         }
         endLoad(objThis,objLoad);
        });
	});

	$("#divRFBody .chkitemshow").off("change");
	$("#divRFBody").on("change",".chkitemshow",function(){
		objThis=$(this); sVal="0";
		objRow=$(this).closest(".supply-row");
		objRF=$(this).closest("#divRF");
		if($(objThis).is(":checked")){
			sVal="1";
		}
		sRowNo=$(objRow).attr('data-rowno');
		sSupCode=$(objRow).attr('data-supcode');;
		sReqId=getKeyVal(objRF,"request_id","infoinput");
		aData={u_mode:"request_item_show_hide",request_item_show:sVal,request_id:sReqId,supply_code:sSupCode,request_item_no:sRowNo};
		callAjax("purchase_req_a.php",aData,function(jRes,retAData){
			if(jRes.res=="1"){
				$.notify("Data updated.","success");
			}else{
				$(objThis).prop("checked",!(sVal*1));
			}
        });
	});

	$("#divRFBody #btnAddRequestItem").off("click");
	$("#divRFBody").on("click","#btnAddRequestItem",function(){
		objThis=$(this);
		objPrin=$(this).closest("#divRFBody");
		objRF=$(this).closest("#divRFBody");
		objRow = $(this).closest(".input-row");
		objLoad=$(objRow).find("#btnLoader");
		iAmt=getKeyVal(objRow,"request_amt");
		sSupCode=getKeyVal(objRow,"supply_code");
		sSupNote=getKeyVal(objRow,"request_supply_note");
		iTotalFinal=getKeyVal(objRow,"request_total_price_final");
		iReqVat=$("#txtRequestVat").val();
		if(sSupCode=="" || sSupNote==""){
			$.notify("กรุณาระบุ รหัสสินค้า และ ชื่อสินค้า ","error");
			$(objRow).find(".saveinput[data-keyid='request_supply_note']").focus();
			return
		}
		if(iAmt<=0){
			$.notify("กรุณาระบุจำนวน","error");
			$(objRow).find(".saveinput[data-keyid='request_amt']").focus();
			return;
		}

		sReqId=getKeyVal(objPrin,"request_id","infoinput");
		if(sReqId==""){
			$.notify("กรุณาทำการสร้างกดบันทึกแบบฟอร์มก่อนเพิ่มราย","error");
			return;
		}
		aData=getDataRow(objRow);
		aData.request_id=sReqId;
		aData.request_vat=iReqVat;
		aData.u_mode="request_item_show_add";
		startLoad(objThis,objLoad);
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
         	$.notify("Item Added","success");
         	$("#divRFBody #btnClearInput").trigger("click");
         	sUrl="purchase_req_item_list.php?request_id="+sReqId;
         	$("#divPRIN").load(sUrl,function(){
         		fncCalculateSummary();
         	});
         }else{
         	$.notify(jRes.msg);
         }
         endLoad(objThis,objLoad);
        });
	});

	$("#divRF").off("change","#ddlQuickTxt");
	$("#divRF").on("change","#ddlQuickTxt",function(){
		objRF = $(this).closest("#divRF");
		$(objRF).find(".infoinput[data-keyid='request_title']").val($(this).val());
	});

	$("#divRF #btnImportLog").off("click");
	$("#divRF #btnImportLog").on("click",function(){

		sReqId = getKeyVal($("#divRF"),"request_id","infoinput");
		sUrl = "purchase_req_import_log.php?reqid="+sReqId;
		showDialog(sUrl,"Import Log Req ID. : "+sReqId,"80%","90%","",
		function(sResult){
			//CLose function
			if(sResult == "REFRESH"){
				requestMode("FIN");
			}
		},false,function(){});
	});

	$("#divRF #btnSaveRequest").off("click");
	$("#divRF #btnSaveRequest").on("click",function(){
		objRF=$(this).closest("#divRF");
		objBody=$(objRF).find("#divRFBody");
		objThis=$(this);
		objLoad=$(objRF).find("#btnSaveRequest-loader");
		var aData = "";

		$(objBody).find(".bg-error").removeClass("bg-error");
        $(objBody).find(".infoinput").each(function(ix,objx){
        	sKeyId=$(objx).attr('data-keyid');
        	sVal=getOV(objx);
        	if($(objx).attr('data-require')=="1"){
        		if(sVal==""){
        			$(objx).addClass("bg-error");
        		}
        	}
        });
        if($(objBody).find(".bg-error").length>0){
        	$.notify("Please enter require field. ");
        	return;
        }

        sReqId=getKeyVal(objBody,"request_id","infoinput");
		if(sReqId==""){
			aData=getAllDataChanged(objBody,"infoinput");
		}else{
			aData=getDataRow(objBody,"infoinput");
		}

        

        if(aData==""){
        	$.notify("ไม่พบข้อมูลเปลี่ยนแปลง\r\nNo Data Changed","warn");
        	return false;
        }else if(sReqId=="") aData.u_mode="request_add";	
		else aData.u_mode="request_update";

        startLoad(objThis,objLoad);
        callAjax("purchase_req_a.php",aData,function(jRes,retAData){
         if(jRes.res=="1"){
          	$.notify("Data Saved","success");
          	if(retAData.u_mode=="request_add"){
          		setKeyVal(objRF,"request_id",jRes.request_id,true,"infoinput");
          		setKeyVal(objRF,"request_status","0",true,"infoinput");
          		fncChangeMode("0");
           	}else if(retAData.u_mode=="request_update"){
          		
          	}
          	setKeyAllOld($("#divRF"),"infoinput");
          	endLoad(objThis,objLoad);
          	
         }else{
         	$.notify(jRes.msg);
         	endLoad(objThis,objLoad);
         }
         
        });
	});

	$("#divRF #btnSubmitRequest").off("click");
	$("#divRF #btnSubmitRequest").on("click",function(){
		if($("#divPRIN .supply-row").length <= 0){
			//No row found can't submit
			$.notify("ไม่พบรายการสั่งซื้อใดๆ\r\nNo items found.");
			return;
		}
		sReqId=getKeyVal($("#divRF"),"request_id","infoinput");
		aData={u_mode:"request_item_show_submit",request_id:sReqId};
		objThis=$(this);
		objLoad=$("#divRF #btnSaveRequest-loader");
		startLoad($("#divRF .save-btn"),objLoad);
		callAjax("purchase_req_a.php",aData,function(jRes,retAData){
			if(jRes.res=="1"){
				$.notify("Request Submitted","success");
				endLoad(objThis,objLoad);
				fncChangeMode("1");

			}else{
				endLoad(objThis,objLoad);
			}
        });
	});

	$("#divRF #btnImportRequest").off("click");
	$("#divRF #btnImportRequest").on("click",function(){
		sReqId = getKeyVal($("#divRF"),"request_id","infoinput");
		//sTitle = getKeyVal($("#divRF"),"request_title","infoinput");
		sUrl = "stock_import_form.php?reqid="+sReqId;
		showDialog(sUrl,"Import Items for : "+sReqId,"450","900","",
		function(sResult){
			//CLose function
			if(sResult == "1"){
				//requestMode("FIN");
			}
		},false,function(){});
	});


	$("#divRF #btnShowUpload").off("click");
	$("#divRF #btnShowUpload").on("click",function(){
		sReqId = getKeyVal($("#divRF"),"request_id","infoinput");
		if(sReqId=="") return;
		sTitle = getKeyVal($("#divRF"),"request_title","infoinput");
		sUrl = "supply_inc_upload.php?reqid="+sReqId;
		showDialog(sUrl,"Upload File For : "+sTitle,"230","440","",
		function(sResult){
			//CLose function
			if(sResult == "1"){
				sUrl="purchase_req_a.php?request_id="+sReqId+"&u_mode=request_file_list";
				$("#divRF #divFUL").load(sUrl,function(){

				});
			}
		},false,function(){});
	});

	/*
	$("#divRF #btnPrintPR").off("click");
	$("#divRF #btnPrintPR").on("click",function(){
		sReqId = getKeyVal($("#divRF"),"request_id","infoinput");
		if($("#divRF #divPRIN .supply-row").length){
			//Item Found
		}else{
			$.notify("no item added.\r\nไม่พบ Item ถูกเพิ่ม");
			return;
		}
		if(sReqId=="") return;

		sUrl = "purchase_req_pdf.php?reqid="+sReqId;
		window.open(sUrl);
	});
	*/
	function fncCalculateFinal(){
		if(iAutoCal) return;
		iAutoCal = true;

		objRow=$("#divRFBody .input-row");
		iAmt = getKeyVal(objRow,"request_amt")*1;
		if(iAmt==0)return;

		iItem=getKeyVal(objRow,"request_item_price")*1;
		iItemV=getKeyVal(objRow,"request_item_price_vat")*1;
		iTotal=getKeyVal(objRow,"request_total_price")*1;
		iTotalVat=getKeyVal(objRow,"request_total_price_vat")*1;

		iDiscB = getKeyVal(objRow,"discount_before_vat")*1;
		iDiscA = getKeyVal(objRow,"discount_after_vat")*1;

		iItemD=0;
		iItemFinal=Number(iItem+((iItem*iVat)/100));
		iTotalFinal=Number(iTotal+((iTotal*iVat)/100));
		iDiscBBaht=0;
		if(iDiscB > 0){
			iDiscBBaht=Number((iItem*iDiscB)/100);
			iItemD=Number(iItem-iDiscBBaht);
			iTotalD=Number(iItemD*iAmt);
			iItemFinal=Number(iItemD+((iItemD*iVat)/100));
			iTotalFinal=Number(iItemFinal*iAmt);
			setKeyVal(objRow,"discount_before_vat_baht",iDiscBBaht*iAmt,false);
			setKeyVal(objRow,"request_item_price_discount",iItemD,false);
			setKeyVal(objRow,"request_item_price_final",iItemFinal,false);
			setKeyVal(objRow,"request_total_price_discount",Number(iTotal-(iDiscBBaht*iAmt)) ,false);
			
		}else{
			setKeyVal(objRow,"discount_before_vat_baht",0,false);
			setKeyVal(objRow,"request_item_price_discount",iItem,false);
			setKeyVal(objRow,"request_item_price_final",iItemFinal,false);
			setKeyVal(objRow,"request_total_price_discount",iTotal ,false);
			
		}
		

		//Update only if iDiscA Exist 
		if(iDiscA > 0){
			iDiscABaht=((iItemFinal*iDiscA)/100);
			//iItemD=iItemFinal-iDiscABaht;
			iItemFinal=iItemFinal-iDiscABaht;
			iTotalFinal=iItemFinal*iAmt;

			setKeyVal(objRow,"discount_after_vat_baht",iDiscABaht*iAmt,false);
			//setKeyVal(objRow,"request_item_price_discount",iItem-iDiscABaht);
			setKeyVal(objRow,"request_item_price_final",iItemFinal,false);
			//setKeyVal(objRow,"request_total_price_discount",iDiscABaht*iAmt);
			setKeyVal(objRow,"request_total_price_final",iTotalFinal,false );
		}else{
			setKeyVal(objRow,"request_item_price_final",iItemFinal,false);
			setKeyVal(objRow,"request_total_price_final",iTotalFinal,false );				
		}

		iAutoCal = false;
	}

	function fncClearInput(){
		iAutoCal=true;
		objRow=$("#divRFBody");

		$("#divRFBody .input-row .saveinput").val("");
		setKeyVal(objRow,"discount_before_vat","0");
		setKeyVal(objRow,"discount_before_vat_baht","0");
		setKeyVal(objRow,"discount_after_vat","0");
		setKeyVal(objRow,"discount_after_vat_baht","0");
		setKeyVal(objRow,"request_exact_amt","0");
		setKeyVal(objRow,"request_item_price_vat","0");
		setKeyVal(objRow,"request_item_price_final","0");
		

		iAutoCal=false;
	}

	function fncShowSearchSupply(sKey=""){
		sUrl = "supply_inc_list.php?showempty=1";
		if(sKey!="") sUrl+="&find="+encodeURIComponent(sKey);
		showDialog(sUrl,"Find Supply List : ","320","640","",
		function(sResult){
			//CLose function
			if(sResult != ""){
				objRow=$("#divRFBody .input-row");
				aRes = sResult.split(",");
				setKeyVal(objRow,"supply_code",aRes[0],false);
				setKeyVal(objRow,"request_supply_note",aRes[1],false);
				
				//setKeyVal(objRow,"bulk_unit",JSON.parse(aRes[3]));
				//setKeyVal(objRow,"convert_amt",JSON.parse(aRes[4]));
				sOptUnit="<option values='"+aRes[2]+"'>"+aRes[2]+"</option>";
				if(aRes[3]!="") sOptUnit+="<option values='"+aRes[3]+"'>"+aRes[3]+"</option>";
				ddlUnit=$(objRow).find(".saveinput[data-keyid='request_unit']");
				$(ddlUnit).html(sOptUnit);
				setKeyVal(objRow,"supply_unit",aRes[2]);

				$("#divRFBody .saveinput[data-keyid='request_amt']").focus();
			}
		},false,function(){});
	}

	function fncCalculateSummary(){
		iTotalFinal=0; iTotalPrice=0;
		iVat = $("#divRF #txtRequestVat").val();
		$("#divPRIN .supply-row").each(function(ix,objx){
			iAmt = $(objx).find(".request-amt").html();
			iUnitPrice = $(objx).find(".request-price").html();
			iPrice = $(objx).find(".total-price").html();
			iFinal = $(objx).find(".total-price-final").html();
			iTotalPrice+=Number(iAmt*iUnitPrice);
			iTotalFinal+=iFinal*1;
		});


		iVatBaht = (iTotalPrice*(iVat/100));

		iDiscount=iTotalFinal-(iTotalPrice+iVatBaht);
		$("#txtTotalPrice").val(iTotalPrice.toFixed(2));
		$("#txtTotalFinal").val(iTotalFinal.toFixed(2));
		$("#txtRequestVatBaht").val(iVatBaht.toFixed(2));
		$("#txtDiscount").val(iDiscount.toFixed(2));
	}

	function fncChangeMode(iMode){
		$("#divRF .save-btn").hide();
		
		$("#divRF #ddlQuickTxt").prop("disabled",false);
		$("#divRF .request-po").prop("disabled",true);
		$("#divRF .request-pr").prop("disabled",false);
		//$("#divRF #btnPrintPO").hide();

		if(iMode=="") {
			$("#divRF #btnSaveRequest").show();
			$("#divRF #btnShowUpload").hide();
		}else {
			$("#divRF #divSupplyList").show();
			$("#divRF #btnShowUpload").show();
			$("#divRF #btnPrintPR").show();
		}
		
		if(iMode=="0"){
			$("#divRF #btnSaveRequest").show();
			$("#divRF #btnSubmitRequest").show();
		}else if(iMode!=""){
			//$("#divRF #btnCancelRequest").show();
			

			$("#divRFBody").off("click",".btndelete");
			$("#divRFBody .btndelete").css("color","silver");
			$("#divRFBody .btndelete").removeClass("fabtn btndelete");
			$("#divRF .input-row").hide();
			//$("#divRF #btnPrintPO").show();
			if(iMode=="1"){
				$("#divRF #ddlQuickTxt").prop("disabled",true);
				$("#divRF .infoinput").prop("disabled",true);
				$("#divRF .request-pr").prop("disabled",true);
				$("#divRF .request-po").prop("disabled",false);
				$("#divRF #btnImportRequest").show();
				$("#divRF #btnImportLog").show();
				$("#divRF #btnSaveRequest").show();

			}else if(iMode=="FIN"){

				$("#divRF #ddlQuickTxt").prop("disabled",true);
				$("#divRF .infoinput").prop("disabled",true);
				$("#divRF #btnImportLog").show();
				//$("#divRF #btnCancelRequest").hide();
			}
		}
		setKeyVal($("#divRF"),"request_status",iMode,true,"infoinput");
	}
	<? echo($sJs); ?>
});
</script>

<?
exit();
