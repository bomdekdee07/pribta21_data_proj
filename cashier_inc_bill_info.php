<?
include_once("in_session.php");
include_once("in_php_function.php");

$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sClinicId=getSS("clinic_id");
$sQ=getQS("q");
$sToday=date("Y-m-d");
$aQStatus = array("queue"=>"","queue_type"=>"","queue_status"=>"","queue_call"=>"");
$sJS="";
include("in_db_conn.php");
$sBillId="";

if($sQ=="" && $sUid==""){
	echo("No queue or UID is provided");
	exit();
}




//GET Bill_id
$query="SELECT uid,collect_date,collect_time,queue,queue_type,queue_status,queue_call,bill_id FROM i_queue_list IQL
LEFT JOIN i_bill_detail IBD
ON IBD.clinic_id = IQL.clinic_id
AND IBD.bill_date=IQL.collect_date
AND IBD.bill_q = IQL.queue
AND IBD.bill_q_type = IQL.queue_type
WHERE IQL.clinic_id=? ";
if($sQ!=""  && $sToday==$sColD) $query.=" AND queue=? AND collect_date=?";
else $query.=" AND uid=? AND collect_date=?  AND collect_time=?";

$stmt = $mysqli->prepare($query);

if($sQ!="" && $sToday==$sColD) $stmt->bind_param("sss",$sClinicId,$sQ,$sToday);
else $stmt->bind_param("ssss",$sClinicId,$sUid,$sColD,$sColT);

if($stmt->execute()){
  $stmt->bind_result($uid,$collect_date,$collect_time,$queue,$queue_type,$queue_status,$queue_call,$bill_id);
  while ($stmt->fetch()) {
	$sBillId=$bill_id;

	if($sColD==""){
		$_GET["uid"] = $uid;
		$_GET["coldate"] = $collect_date;
		$sUid=$uid;
	  	$sColD=$collect_date;
	  	$aQStatus["queue_type"]=$queue_type;
  		$aQStatus["queue_status"]=$queue_status;
  		$aQStatus["queue_call"]=$queue_call;
	}
	$sColT=$collect_time;
	$_GET["coltime"] = $collect_time;
  }
}

if($sBillId=="") $sBillId=getQS("billid");

$sIsToday = ($sColD==$sToday);
$sUidList = ""; $sBillBtn = ""; $isAllPaid = true;



$aBI=array("receive_by"=>"","paid_method"=>"","receive_amt"=>"","paid_amt"=>"","s_name"=>"","paid_datetime"=>"");

if($sBillId!=""){
	$bind_param = "ssss";
	$array_val = array($sBillId, $sUid, $sColD, $sColT);
	$check_condition_approve = "";

	$query = "SELECT status 
	from i_bill_cancel_approve 
	where bill_id = ?
	and uid = ?
	and collect_date = ?
	and collect_time = ?
	order by date_now;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($bind_param, ...$array_val);

	if($stmt->execute()){
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()){
			$check_condition_approve = $row["status"];
		}
		// echo "TEST".$check_condition_approve."/".$sBillId."/".$sUid."/".$sColD."/".$sColT;
	}
	$stmt->close();

	$query="SELECT IBD.bill_id,IQL.uid,IQL.collect_date,IQL.collect_time,IQL.queue,fname,sname,receive_by,paid_method,receive_amt,paid_amt,s_name,paid_datetime
	FROM i_bill_list IBL

	LEFT JOIN i_bill_detail IBD
	ON IBD.bill_id=IBL.bill_id
	AND IBD.clinic_id=IBL.clinic_id

	LEFT JOIN i_queue_list IQL
	ON IQL.clinic_id = IBD.clinic_id
	AND IQL.collect_date = IBD.bill_date
	AND IQL.queue = IBD.bill_q
	AND IQL.queue_type = IBD.bill_q_type

	LEFT JOIN patient_info PI
	ON PI.uid = IQL.uid

	LEFT JOIN p_staff PS
	ON PS.s_id=IBL.receive_by

	WHERE IBL.bill_id=? AND IBL.clinic_id=? ORDER BY IQL.uid";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sBillId,$sClinicId);

	
	if($stmt->execute()){
	  	$stmt->bind_result($bill_id,$uid,$coldate,$coltime ,$q,$fname,$sname,$receive_by,$paid_method,$receive_amt,$paid_amt,$s_name,$paid_datetime);
		while ($stmt->fetch()) {
			if($uid!="") $sUidList.="<div class='fl-wrap-row h-40 row-color row-hover row-bill' data-uid='$uid' data-bill='$bill_id' data-q='$q' data-coldate='$coldate'>
				<div class='fl-wrap-col f-border fs-smaller'>
					<div class='fl-wrap-row '>
						<div class='fl-fill lh-20 fw-b'>$uid</div>
						<div class='fl-fill lh-20'>$coldate</div>
						<div class='fl-fill lh-20 '>$coltime</div>
						<div class='fl-fix w-40 fl-mid fw-b'>$q</div>
					</div>
					<div class='fl-wrap-row '>
						<div class='fl-fill lh-20'>".$fname." ".$sname."</div>
					</div>
					
				</div>
				
				";

			//if($receive_by==""){

			if($receive_by == "")
			$sUidList.="<div class='fl-fix w-20 fl-mid  f-border ".(($sColD==$coldate && $q==$sQ)?"'>":"fabtn btnuidremove' style='color:red' ><i class='fa fa-trash-alt'></i></div><div class='fl-fix w-30 fl-mid btnuidremove-loader' style='display:none'><i class='fa fa-spinner fa-spin' ></i>")."</div>";

			//}
			$sUidList.="</div>";
			if($receive_by != "" && ($check_condition_approve == "" || $check_condition_approve == "C" || $check_condition_approve == "F")){
				$isAllPaid = "";
			}
			else if($receive_by == "" && $check_condition_approve == "W"){
				$isAllPaid = "W";
			}
			else if($receive_by == "" && $check_condition_approve == "A"){
				$isAllPaid = "A";
			}
			else if($receive_by == "" && $check_condition_approve == "F"){
				$isAllPaid = "F";
			}
			else if($receive_by == "" && $check_condition_approve == ""){
				$isAllPaid = "N";
			}
			// echo "TEST:".$receive_by."/".$check_condition_approve.":".$isAllPaid;

			$aBI[$bill_id][$uid]["coldate"]=$coldate;
			$aBI[$bill_id][$uid]["coltime"]=$coltime;
			$aBI["receive_by"]=$receive_by;
			$aBI["paid_method"]=$paid_method;
			$aBI["receive_amt"]=$receive_amt;
			$aBI["paid_amt"]=$paid_amt;
			$aBI["s_name"]=$s_name;
			$aBI["paid_datetime"]=$paid_datetime;
		}
	}	
	$sUidList="<div class='fl-wrap-col fl-auto hmin-100'>$sUidList</div>";
}else{

}
$mysqli->close();




if($sIsToday==false){
	//Not Today
	$sBillBtn="<div class='fl-fill fl-mid fs-smaller h-30 bg-head-1'>ข้อมูลของวันที่ $sColD	</div>";
}

if($sBillId==""){
	//ยังไม่สร้างบิล
	if($sIsToday){
		$sBillBtn.="<div id='btnCreateBill' class='h-30 fabtn btn-mode fl-wrap-col fl-mid' 	style='background-color:red;color:white;'>
			<div class='fl-fill fl-mid lh-20'>ออกเลขบิล</div>
			<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Bill Issue #</div>
		</div>";
	}else{
		$sBillBtn.="<div class='fl-fix h-30 fl-mid' style='background-color:orange;color:white'>ไม่ได้ออกเลขบิล/Bill is not issued</div>
		<div class='fl-fix h-10'></div>
		<div id='btnCreateBill' class='h-30 fabtn btn-mode fl-wrap-col fl-mid' 	style='background-color:red;color:white;'>
			<div class='fl-fill fl-mid lh-20'>สร้างเลขบิลย้อนหลัง</div>
			<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Back Date Bill Issue #</div>
		</div>
		<div class='fl-fix h-30 fs-smaller fl-mid' style='background-color:white'>
			***เลขบิล จะต่อจากเลขบิลปัจจุบัน
		</div>";
	}

}else{
	//สร้างบิลแล้ว
	$sBillBtn.="<div class='h-30 fl-wrap-row'  style='background-color:red;color:white;'>
				<div class='fl-fill fl-mid'>$sBillId</div>
				".(($isAllPaid == "")?"":"<div id='btnAddMoreBill' class='fl-fix w-30 fl-mid fabtn h-30' data-billid='$sBillId'><i class='fa fa-plus lg'></i></div>")."
			</div>".$sUidList;


	if($isAllPaid == ""){
		$sJS="$(\"#btnCashierAdd\").hide();";
		$sMethod = "เงินสด";
		if($aBI["paid_method"]=="TRANS") $sMethod="โอนเงิน";
		else if($aBI["paid_method"]=="CREDIT") $sMethod="บัตรเครดิต";


		//<div class='fl-fix h-30 fl-mid bg-head-1'>รายละเอียดชำระเงิน</div>
		$sBillBtn.= "

		<div class='fl-wrap-row h-30'>
			<div class='fl-wrap-col' style='background-color:green;color:white;'>
				<div class='fl-fill fl-mid h-20'>ชำระเงินแล้ว</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Payment Received</div>
			</div>

			<div id='btnCancelPaid' class='fl-wrap-col h-30 fabtn' data-billid='$sBillId' data-queue='$sQ' data-uid='$sUid' data-coldate='$sColD' data-coltime='$sColT' data-rquestby='".$aBI["receive_by"]."' data-method='".$aBI["paid_method"]."' data-receiveamt='".$aBI["receive_amt"]."' data-paidamt ='".$aBI["paid_amt"]."' data-paiddatetime='".$aBI["paid_datetime"]."' style='background-color:red;color:white;'>
				<div class='fl-fill fl-mid h-20'>ยกเลิก</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Cancel Payment</div>
			</div>
		</div>
		
		<div class='fl-wrap-row h-30 row-color'>
			<div class='fl-fix w-80 lh-30'>รับโดย</div>
			<div class='fl-fill fs-small lh-30'>$s_name</div>
		</div>
		<div class='fl-wrap-row h-30 row-color'>
			<div class='fl-fix w-80 lh-30'>เมื่อเวลา</div>
			<div class='fl-fill lh-30'>$paid_datetime</div>
		</div>
		<div class='fl-wrap-row h-30 row-color'>
			<div class='fl-fix w-80 lh-30'>รวม</div>
			<div class='fl-fill lh-30 fl-mid'>".$aBI["paid_amt"]." บาท</div>
			<div class='fl-fix w-100 lh-30 fl-mid fs-small'>$sMethod</div>
		</div>

		";
	}
	else if($isAllPaid == "N"){
		$sBillBtn.="
			<div class='fl-fix h-30 fl-mid' style='color:white;background-color:red'>ไม่พบรายละเอียดการชำระเงิน</div>
			<div id='btnCashCall' class='h-30 fabtn btn-mode fl-wrap-col fl-mid' style='background-color:orange;color:white;'>
				<div class='fl-fill fl-mid lh-20'>ชำระเงิน</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Receive Payment</div>
			</div>";
	}
	else if($isAllPaid == "W"){
		$sBillBtn.="
			<div class='fl-fix h-30 fl-mid' style='color:white;background-color:red'>กำลังรอการ 'อนุมัติ'</div>
			<div id='btnCashCall_W' class='h-30 btn-mode fl-wrap-col fl-mid' style='background-color:#8A8C8A;color:white;'>
				<div class='fl-fill fl-mid lh-20'>ชำระเงิน</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Receive Payment</div>
			</div>";
	}
	else if($isAllPaid == "A"){
		$sBillBtn.="
			<div class='fl-fix h-30 fl-mid' style='color:white;background-color:red'>'อนุมัติ' กำลังรอการดำเนินการ</div>
			<div id='btnCashCall_W' class='h-30 btn-mode fl-wrap-col fl-mid' style='background-color:#8A8C8A;color:white;'>
				<div class='fl-fill fl-mid lh-20'>ชำระเงิน</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Receive Payment</div>
			</div>";
	}
	else if($isAllPaid == "F"){
		$sBillBtn.="
			<div class='fl-fix h-30 fl-mid' style='color:white;background-color:red; color:#41EA41;'>ดำเนินการเสร็จสิ้น</div>
			<div id='btnCashCall' class='h-30 fabtn btn-mode fl-wrap-col fl-mid' style='background-color:orange;color:white;'>
				<div class='fl-fill fl-mid lh-20'>ชำระเงิน</div>
				<div class='fl-fix h-10 lh-10 fs-xsmall fl-mid'>Receive Payment</div>
			</div>";
	}
}

/*$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sClinicId=getSS("clinic_id");
$sQ=getQS("q");
*/




?>
<div id='divPayment' class='fl-wrap-col' <? echo(getDataAttr($sUid,$sColD,$sColT,$sQ)." data-billid='".$sBillId."'" ); ?> data-istoday='<? echo($sIsToday); ?>' data-coldate='<? echo($sColD); ?>'>
	
	<div id='divBillInfo' class='fl-wrap-col' >
		<div class='fl-wrap-col h-40' <? if($sBillId=="") echo("style='display:none'") ?> >
			<? 
				if($sBillId!="") {$_GET["doc_group"]="B_INVOICE"; $_GET["billid"]=$sBillId;include("document_sys_bt.php");}
			?>
		</div>
		<? echo($sBillBtn); ?>
		

		<div class='fl-wrap-col h-40'>
			<? $_GET["doc_group"]="RECEIPT"; if($sBillId!="") {
				$_GET["billid"] = $sBillId;
				include("document_sys_bt.php");
			} ?>
		</div>
	</div>
	<div id='divPaymentReview' class='fl-wrap-col ' style="display:none;background-color:white">
		<div class='fl-wrap-row h-30 bg-head-1 fl-mid'>
			<div class='fl-fill'>รายละเอียดชำระเงิน</div>
			<div id='btnCancelReview' class='fl-fix w-30 h-30 fabtn fl-mid' title='Cancel & Close' style='color:red;background-color: white'><i class='fas fa-window-close fa-lg'></i></div>
		</div>
		<div class='fl-fix h-30'>วิธีชำระเงิน</div>
		<div class='fl-wrap-col al-left fl-auto' style='text-indent: 10px'>
			<div class='fl-fix h-30 row-hover row-color lh-30'><i class='fa fa-money-bill-alt'></i><label><input type='radio' class='bigcheckbox' name='paymethod' value='CASH' checked=true /> CASH</label></div>
			<div class='fl-fix h-30 row-hover row-color lh-30'><i class='far fa-credit-card'></i><label><input type='radio' class='bigcheckbox' name='paymethod' value='CREDIT' />CREDIT CARD</label><br/></div>
			<div class='fl-fix h-30 row-hover row-color lh-30'><i class='fas fa-exchange-alt'></i><label><input type='radio' class='bigcheckbox' name='paymethod' value='TRANS' />TRANSFER</label></div>
		</div>
		<div class='fl-fix'>รายละเอียด/Note</div>
		<div class='fl-fix row-color'><input class='fill-box' id='txtBillNote' /></div>
		<div class='fl-wrap-row h-30'><div class='fl-fix wper-40'>ยอดรวม</div><div class='fl-fill fw-b fl-mid'><input id='txtTotal' class='fill-box  h-25' readonly="true" /></div><div class='fl-fix w-60 fl-mid'>บาท</div></div>

		<div class='fl-wrap-row h-30 fl-mid'>

			<div id='btnFullAmt' class='fabtn fl-fix w-150 h-30 fl-mid f-border'>Full Amount</div>
		</div>
		<div class='fl-wrap-row h-30'><div class='fl-fix wper-40'>ยอดเงินรับ</div><div class='fl-fill fw-b fl-mid'><input id='txtTotalRec' class='fill-box  h-25'  /></div><div class='fl-fix w-60 fl-mid'>บาท</div></div>
		<div class='fl-fill' id='divInfo'></div>

		<div id='btnBillPaid' class='fl-fix fabtn row-hover h-30 bg-head-2 fl-mid'>ยืนยัน ชำระเงิน</div>
		<div id='btnBillPaid-loader' class='fl-fix row-hover h-30 bg-head-2 fl-mid' style='display:none'><i class='fa fa-spinner fa-spin fa-lg'></i></div>
	</div>
</div>

<script>
	$(function(){
		<? echo($sJS); ?>
		$("#divPayment #btnFullAmt").off("click");
		$("#divPayment #btnFullAmt").on("click",function(){
			$("#divPayment #txtTotalRec").val($("#divPayment #txtTotal").val());
			$("#divPayment #txtTotalRec").trigger("change");
		});

		$("#divPayment .btnuidremove").off("click");
		$("#divPayment").on("click",".btnuidremove",function(){
			objThis=$(this); 
			objRow=$(this).closest(".row-bill");
			objLoad=$(objRow).find(".btnuidremove-loader");
			sUid=$(objRow).attr("data-uid");
			sBillId=$(objRow).attr("data-bill");
			sColD=$(objRow).attr('data-coldate');
			sQ=$(objRow).attr('data-q');
			sURL="cashier_a.php";

			if(confirm("ยืนยันนำ UID "+sUid+" ออกจาก Bill ID "+sBillId+"?\r\nConfirm remove UID "+sUid+" from Bill ID "+sBillId+"?")==false){
				return;
			}

			aData={u_mode:"bill_del_uid",uid:sUid,billid:sBillId,q:sQ,coldate:sColD};

			startLoad(objThis,objLoad);
			callAjax(sURL,aData,function(jRes,rData){
				if(jRes.res=="1"){
					$(objRow).remove();

					reloadSummaryList(sBillId);
				}else{
					endLoad(objThis,objLoad);
				}
				
	    	});

		});

		$("#divPayment #btnBillPaid").off("click");
		$("#divPayment #btnBillPaid").on("click",function(){
			sBillId=$("#divPayment").attr('data-billid');

			sRecAmt=$("#divPayment #divPaymentReview #txtTotalRec").val().trim()*1;
			iTotal=$("#divPayment #txtTotal").val().trim()*1;


			if($("#divPayment #txtTotalRec").val().trim()=="" || sRecAmt < iTotal){
				$("#divPayment #txtTotalRec").notify("จำนวนเงินที่ได้รับไม่ถูกต้อง\r\nPlease enter amount received.");
				return;
			}

			sRecMet=$("#divPayment #divPaymentReview input[name='paymethod']:checked").val();
			if(sRecMet == ""){
				$.notify("Please select pay method");
				return;
			}
			

			if(confirm("ยืนยันชำระเงิน?\r\nConfirm Payment?")==false || sBillId=="") return;

			sNote=$("#divPayment #divPaymentReview #txtBillNote").val().trim();

			sURL="cashier_a.php";

			$("#divPayment #divPaymentReview input").attr("readonly","true");

			aData={u_mode:"bill_paid",recmethod:sRecMet,billid:sBillId,note:sNote,recamt:sRecAmt};
			startLoad($(this),$("#btnBillPaid-loader"));
			callAjax(sURL,aData,function(jRes,rData){
				if(jRes.res=="1"){
					$.notify("Success","success");
					sUid = $("#divPayment").attr('data-uid');
					sQ=$("#divPayment").attr('data-q');

					bToday = $("#divPayment").attr("data-istoday");

					if(bToday){
						let sUrl = "queue_inc_fwd.php?uid="+sUid+"&q="+sQ;

						$("#divCashInfo #divCashWait .btn-q-info[data-uid='"+sUid+"']").remove();
						//$(".unpaid-row[data-uid='"+sUid+"']").remove();

						showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","",function(){

						},false,function(){
							$("#divQueueFwd input[name='room_no'][value='27']").prop("checked",true);
							$("#divQueueFwd input[name='room_no'][value='27']").focus();
							//$("#divQueueFwd #divRoomList").scrollTop($("#divQueueFwd input[name='room_no'][value='27']").scrollIntoView());
							refreshBill();
						});	
					}else{
						refreshBill();
					}
					$("#btnCashierAdd").hide();

				}else{
					endLoad($("#btnBillPaid"),$("#btnBillPaid-loader"));
				}
				
	    	});
		});


		$("#divPayment #txtTotalRec").off("change");
		$("#divPayment #txtTotalRec").on("change",function(){
			iRec = $(this).val();
			iChange = iRec - $("#divPayment #txtTotal").val();
			$("#divPayment #divInfo").html("ส่วนต่าง (เงินทอน) "+iChange);
		});

		$("#divPayment #btnAddMoreBill").off("click");
		$("#divPayment #btnAddMoreBill").on("click",function(){
			sBillId=$(this).attr('data-billid');
			sColD=$(this).closest("#divPayment").attr("data-coldate");
			sUrl="cashier_inc_manage_bill.php?billid="+encodeURIComponent(sBillId)+"&coldate="+sColD;
			showDialog(sUrl,"Bill's Subject List :"+sBillId+" : "+sColD,"80%","70%","",
			function(sResult){
				if(sResult=="REFRESH"){
					refreshBill();
					reloadSummaryList(sBillId);
				}
			},false,function(){
				//Load Done Function
			});
		});

		//Check All Total
		function checkTotal(){
			if($("#divSupplyTotal").length){
				iTotal = $("#divSupplyTotal").attr("data-total");
				$("#divPaymentReview #txtTotal").val(iTotal);
			}
			$("#divInfo").html("");
		}

		$("#divPayment #btnCancelReview").off("click");
		$("#divPayment #btnCancelReview").on("click",function(){
			$("#divPayment #divBillInfo").show();
			$("#divPayment #divPaymentReview").hide();
		});

		$("#divPayment #btnCancelPaid").off("click");
		$("#divPayment #btnCancelPaid").on("click",function(){
			if(confirm("ข้อมูลการชำระเงินทั้งหมดจะถูกยกเลิก ดำเนินการต่อ?\r\nAll Payment information will be removed. Confirm?")){
				sBillId = $(this).attr('data-billid');
				sQueue = $(this).attr('data-queue');
				sUid = $(this).attr('data-uid');
				sColdate = $(this).attr('data-coldate');
				sColtime = $(this).attr('data-coltime');
				sRequest_by = $(this).attr('data-rquestby');
				sMethod = $(this).attr('data-method');
				sReceiveamt = $(this).attr('data-receiveamt');
				sPaidamt = $(this).attr('data-paidamt');
				sPaiddatetime = $(this).attr('data-paiddatetime');
				sURL="cashier_a.php";
				aData={
					u_mode:"bill_paid_cancel",
					billid: sBillId,
					q: sQueue,
					uid: sUid,
					coldate: sColdate,
					coltime: sColtime,
					request_by: sRequest_by,
					method: sMethod,
					receive_amt: sReceiveamt,
					paid_amt: sPaidamt,
					paid_datetime: sPaiddatetime
				};
				startLoad($(this),$("#divPayment #btnBillPaid-loader"));
				callAjax(sURL,aData,function(jRes,rData){
					if(jRes.res=="1"){
						$.notify("Success","success");
						refreshBill();
						reloadSummaryList(sBillId);
						$("#btnCashierAdd").show();

					}else{
						endLoad($("#divPayment #btnCancelPaid"),$("#divPayment #btnBillPaid-loader"));
					}
					
		    	});



			}else{
				return;
			}
		});



		$("#divPayment #btnCashCall").off("click");
		$("#divPayment #btnCashCall").on("click",function(){
			sBillId=$("#divPayment").attr('data-billid');
			sUid=$("#divPayment").attr("data-uid");
			$("#divPayment #divBillInfo").hide();
			$("#divPayment #divPaymentReview").show();
			checkTotal();
		});
		function refreshBill(){
			sUid=$("#divPayment").attr('data-uid');
			sColD=$("#divPayment").attr('data-coldate');
			sColT=$("#divPayment").attr('data-coltime');
			sQ=$("#divPayment").attr('data-q');

			$("#divPayment").parent().load("cashier_inc_bill_info.php?q="+sQ+"&"+qsTxt(sUid,sColD,sColT),function(){
				//try to change edit button to paid

				$(".btn-cashier-edit").remove();

				checkTotal();
			});
		}
	});

</script>

