<?
include_once("in_session.php");
include_once("in_php_function.php");
$sReqId = getQS("reqid");
$sClinicID =getSS("clinic_id");
if($sReqId==""){
	echo("No Request ID specific.");
	exit();
}
if($sClinicID=="") {
	echo("Please login");
	exit();
}

include("in_db_conn.php");

$sH=""; $aItems=array(); $aSum=array(); $aImported=array();
$query = "SELECT ISRI.request_id,request_item_no,ISRI.supply_code,request_supply_note,request_amt,ISR.supply_amt,supply_unit,ISR.stock_lot,recieved_datetime,exp_date,remark FROM i_stock_request_item ISRI 
	LEFT JOIN i_stock_recieved ISR
	ON ISR.request_id=ISRI.request_id
	AND ISR.supply_code=ISRI.supply_code
	LEFT JOIN i_stock_master ISM
	ON ISM.supply_code = ISRI.supply_code
	WHERE ISRI.request_id=? AND request_item_status NOT IN ('CC','FIN') ORDER BY request_item_no;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sReqId);
if($stmt->execute()){
	$stmt->bind_result($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$supply_amt,$supply_unit,$stock_lot,$recieved_datetime,$exp_date,$remark);
	while ($stmt->fetch()) {
		$aItems[$supply_code]["no"]=$request_item_no;
		$aItems[$supply_code]["note"]=$request_supply_note;
		$aItems[$supply_code]["req_amt"]=$request_amt;
		//$aItems[$supply_code]["import_amt"]=$supply_amt;
		//$aItems[$supply_code]["lot"]=$stock_lot;

		$aSum[$supply_code]=(isset($aSum[$supply_code])?$aSum[$supply_code]*1:0)+($supply_amt*1);

		$aItems[$supply_code]["unit"]=$supply_unit;
		$sLTemp = $recieved_datetime." : ".$stock_lot."-".$supply_amt." $supply_unit\r\n";
		if($stock_lot=="")$sLTemp="";
		$aItems[$supply_code]["log"]=(isset($aItems[$supply_code]["log"])?$aItems[$supply_code]["log"]:"").$sLTemp;

		if($stock_lot!="")
		$aImported[$supply_code][] = "<div class='fl-wrap-row h-30 row-hover' style='background-color:green'>
		<div class='fl-fill lh-15 al-left'  style='color:white'><span class='fw-b'>$request_supply_note</span><br/>$supply_code</div>
		<div class='fl-fix w-100 fl-mid'><input class='fill-box' value='$stock_lot' readonly='true' /></div>
		<div class='fl-fix w-80 fl-mid'><input type='number' readonly='true' class='fill-box' value='$supply_amt' title='Import on $recieved_datetime' /></div>
		<div class='fl-fix w-150 fl-mid'  style='color:white'>$request_amt $supply_unit</div>
		
		<div class='fl-fix w-80 fl-mid'><input class='fill-box ' readonly='true' value='$exp_date' /></div>
		<div class='fl-fix w-200 fl-mid'><input class='fill-box ' readonly='true' value='$remark' /></div>
		</div>";






	}	
}

$mysqli->close();

foreach ($aItems as $supply_code => $aI) {
	$request_item_no = $aI["no"];
	$request_supply_note = $aI["note"];
	$request_amt = $aI["req_amt"];
	$supply_unit = $aI["unit"];
	$import_sum = $aSum[$supply_code];
	$sLog = $aI["log"];
	$request_left = $request_amt - $import_sum;
	if($import_sum==$supply_amt){

	}

	if(isset($aImported[$supply_code])){
		foreach ($aImported[$supply_code] as $key => $sLogRow) {
			$sH.=$sLogRow;
		}
	}

	if($request_left>0)
	$sH.="<div class='fl-wrap-row h-30 row-color row-hover data-row' data-reqid='$request_id' data-itemno='$request_item_no' data-supcode='$supply_code'>
	<div class='fl-fill lh-15 al-left'><span class='fw-b'>$request_supply_note</span><br/>$supply_code</div>
	<div class='fl-fix w-100 fl-mid'><input class='fill-box stock_lot' data-keyid='stock_lot' /></div>
	<div class='fl-fix w-80 fl-mid'><input type='number' class='fill-box' data-keyid='request_amt' value='$request_left' data-maxamt='$request_left' title='$sLog' /></div>
	<div class='fl-fix w-150 fl-mid'>$request_amt $supply_unit</div>
	
	<div class='fl-fix w-80 fl-mid'><input class='fill-box stock_exp_date' readonly='true' data-keyid='stock_exp_date' /></div>
	<div class='fl-fix w-200 fl-mid'><input class='fill-box stock_note' data-keyid='stock_note' /></div>
	</div>";
}

?>
<div id='divSIF' class='fl-wrap-col' data-reqid='<? echo($sReqId); ?>'>
	<div class='fl-wrap-col  fs-small h-30'>
		<div class='fl-wrap-row fl-mid row-header row-color-2'>
			<div class='fl-fill'>Code / Name</div>
			<div class='fl-fix w-100'>Lot / Serial</div>
			<div class='fl-fix w-80'>Amt</div>
			<div class='fl-fix w-150'>Unit</div>
			<div class='fl-fix w-80'>Exp Date</div>
			<div class='fl-fix w-200'>Note</div>
		</div>
		
	</div>
	<div class='fl-wrap-col fs-small fl-scroll'>
		<? echo($sH); ?>
	</div>
	<div class='div-info fl-wrap-col h-80 f-border  fs-small al-left' style='background-color: #ffdb85'>
		<div class='fl-wrap-row h-20'>
			<div class='fl-fill  fw-b'>วิธีใช้งาน</div>
			<div class='btncloseinfo fabtn fl-fix w-20 fl-mid' style='color:red'><i class='fa fa-window-close fa-lg'></i></div>
		</div>
		<div class='fl-fill'>
			-กรอกชื่อหรือเลข Lot/Serial และ ใส่ Exp Date(วันหมดอายุ) จากนั้น กด Import เพื่อนำของเข้าระบบ<br/>
			<b>**หากจำนวนที่ได้รับ มีน้อยกว่าจำนวนที่สั่งซื้อไว้ หรือ มี Lot/Serial มากกว่า 1</b> ให้ทำการแก้จำนวน ในช่อง Amt ให้ตรงกับที่ได้รับจริง จากนั้น ใส่ Exp Date(วันหมดอายุ) แล้วกด Import จากนั้นระบบจะคำนวนจำนวนที่เหลือให้ และ เมื่อครั้งต่อไปได้รับมาเพิ่ม สามารถเพิ่มเข้าไปในระบบส่วนที่เหลือได้
		</div>
	</div>

	<div class='fl-wrap-row h-30 fl-mid'>
		<input id='btnImportItem' type='button' value='IMPORT to <? echo($sClinicID); ?>' />
	</div>
</div>

<script>
	$(function(){
		$("#divSIF .stock_exp_date").datepicker({
			dateFormat:"yy-mm-dd",
			changeYear:true,
			changeMonth:true
		});

		$("#divSIF .stock_lot").unbind("change");
		$("#divSIF .stock_lot").on("change",function(){
			sLot = $(this).val().trim();
			objRow = $(this).closest(".data-row");
			sCode = $(objRow).attr('data-supcode');
			$(this).val(sLot);

			if(sLot!=""){
				aData = {u_mode:"get_lot_exp_date",supply_code:sCode,stock_lot:sLot};
				sURL = "supply_a.php";
				callAjax(sURL,aData,function(jRes,retAData){
					expDate = $("#divSIF .data-row[data-supcode='"+sCode+"']").find(".stock_exp_date");
					if(jRes.res=="1"){
						$(expDate).val(jRes.msg);
						$(expDate).attr("disabled",true);
					}else{
						
						$(expDate).removeAttr("disabled");
						$(expDate).prop("disabled",false);
					}
		        });
			}
		});

		$("#divSIF #btnImportItem").unbind("click");
		$("#divSIF #btnImportItem").on("click",function(){
			//encodeURIComponent
			//Validate each row and Assign Data
			sReqId = $("#divSIF").attr('data-reqid');
			aData = {u_mode:"stock_import",request_id:sReqId};aTemp = [];
			$("#divSIF .bg-error").removeClass("bg-error");
			$("#divSIF .data-row").each(function(ix,objx){

				objAmt = $(objx).find("input[data-keyid='request_amt']");
				sSupAmt=$(objAmt).val()*1;
				sSupMaxAmt=$(objAmt).attr("data-maxamt");
				expDate = $(objx).find(".stock_exp_date").val();

				if(sSupAmt*1 > sSupMaxAmt*1){
					$(objAmt).notify("Add Item is more than request item left.");
					$(objAmt).addClass('bg-error');
				}
				if(sSupAmt>0){
					if($(objx).find(".stock_lot").val().trim()==""){
						$(objx).find(".stock_lot").addClass('bg-error');
					}
					
					if(expDate==""){
						$(objx).find(".stock_exp_date").addClass('bg-error');
					}else{
						dEx = new Date(expDate);
						dToday = new Date();
						if(dEx.getTime() <= dToday.getTime()){
							$.notify("Expire Date is less than today.");
							$(objx).find(".stock_exp_date").addClass('bg-error');
						}
					}
					sReqId = $(objx).attr('data-reqid');
					sSupCode = $(objx).attr('data-supcode');
					sExpDate = encodeURIComponent($(objx).find(".stock_exp_date").val().trim());


					sLot = encodeURIComponent($(objx).find(".stock_lot").val().trim());
					sNote = encodeURIComponent($(objx).find(".stock_note").val().trim());
					sTemp = sSupCode+":"+sSupAmt+":"+sLot+":"+sExpDate+":"+sNote;
					aTemp.push(sTemp);
				}
			});

			if($("#divSIF .bg-error").length){
				$.notify("Please correct missing data.");
				return;
			}

			if(aTemp.length==0){
				$.notify("ไม่พบข้อมูลที่ต้องนำเข้า / No data import","warning");
				return;
			}

			aData.items = aTemp;
			sURL = "supply_a.php";
			callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$.notify("Data Imported Success","success");
					if(jRes.status=="REFRESH"){
						closeDlg($("#divSIF #btnImportItem"),jRes.status);	
					}else{
						sUrl="stock_import_form.php?reqid="+sReqId;
						$("#divSIF").parent().load(sUrl,function(){

						});
					}
					
				}else{
					$.notify("Error");
				}
        	});

		});
	});
</script>