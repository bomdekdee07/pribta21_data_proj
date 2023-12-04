<?
include_once("in_session.php");
include_once("in_php_function.php");
$sClinicId = getSS("clinic_id");
$sRoom = getSS("room_no");
?>
<div id='divPharM' class='fl-wrap-row' data-clinicid='<? echo($sClinicId); ?>' data-roomno='<? echo($sRoom); ?>'>
	<div class='fl-wrap-col left-bar w-300' style='background-color:white '>
		<div id='btnDashboard' class='fabtn fl-fix h-25 fl-mid' style='background-color: #00D9D9;'>
			<i class="fa fas fa-file-prescription "> Pharmacy System</i>
		</div>
		<div class='fl-wrap-col'>
			<? $_GET["hidecall"]='1'; $_GET["waitlist"]='1'; include("queue_main.php"); ?>
		</div>
		<? include("leftbar_tools.php"); ?>
	</div>
	<div class='fl-fix toggle-bar'>
	</div>
	<div id='divPharPInfo' class='fl-wrap-col right-bar'>
	</div>

	<div class='fl-wrap-col right-bar-loader' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>
	<div class='fl-wrap-col right-bar w-120 ' style='background-color: white'>
		<div class='fl-fix h-25 fl-mid bg-head-2'>รอยืนยัน</div>
		<div id='divPharConfirm' class='fl-wrap-col fl-scroll'></div>
		<div class='fl-fix h-25 fl-mid bg-head-4'>รอรับยา</div>
		<div id='divPharWait' class='fl-wrap-col fl-scroll'></div>
	</div>


</div>
<script>
$(document).ready(function(){
	$("#divPharM #divPharPInfo .ddl-visit").off("change");
	$("#divPharM #divPharPInfo").on("change",".ddl-visit",function(){
		sOpt=$(this).val();
		if(sOpt=="") return;
		sUid=$(this).attr("data-uid");
		aVisit=sOpt.split(" ");
		showPharma(sUid,"&coldate="+aVisit[0]+"&coltime="+aVisit[1]);
	});

	function showPharma(sUid,sOthQS){
		startLoad($("#divPharM #divPharPInfo"),$("#divPharM .right-bar-loader"));
		
		sUrl="pharmacy_inc_main.php?showq=1&uid="+sUid+sOthQS;
		$("#divPharM #divPharPInfo").load(sUrl,function(){
			endLoad($("#divPharM #divPharPInfo"),$("#divPharM .right-bar-loader"));
		});	
	}

	$("#divPharM .btn-q-info").off("click");
	$("#divPharM").on("click",".btn-q-info",function(){

		qRow = $(this).closest(".q-row");
		let sUid = $(qRow).attr('data-uid');
		//let sQ = (($($(qRow).attr("data-queue")).length)?$(qRow).attr("data-queue"):"");
		let sQ = (($(qRow).is('[data-queue]'))?$(qRow).attr('data-queue'):"");
		let colDate = (($(qRow).is('[data-coldate]'))?$(qRow).attr('data-coldate'):"");
		let colTime = (($(qRow).is('[data-coltime]'))?$(qRow).attr('data-coltime'):"");

		if(sUid=="" && sQ!=""){
			$.notify("Please add UID to this queue before continue");
			$("#divPharM #btnClearInput").trigger("click");					
			$("#divPharM #txtQueue").val(sQ);	
		}else{
			showPharma(sUid,"&coldate="+colDate+"&coltime="+colTime+"&q="+sQ);
		}
	});


	$("#divPharM #divPharPInfo .btnpharupdate").off("click");
	$("#divPharM #divPharPInfo").on("click",".btnpharupdate",function(){
		sQ=$(this).attr('data-queue');
		sMode=$(this).attr("data-mode");
		sURL="supply_a.php";

		sRow = $(this).closest("#divPharIncM");
		sUid=$(sRow).attr('data-uid');
		sColD=$(sRow).attr('data-coldate');
		sColT=$(sRow).attr('data-coltime');

		if(sMode=="prepare_drug"|| sMode=="supply_is_check"|| sMode=="q_ready_cancel") sURL="queue_a.php";
		isCont=true;

		if(sMode=="prepare_drug"){
			iNotPickup=0; 
			$("#divPharM #divPharPInfo .supply-order-list .data-row").each(function(ix,objx){
				if($(objx).attr('data-isservice')!=1){
					iNotPickup++;
				}
			});	
			if(iNotPickup==0){
				$.notify("ไม่พบ รายการใดๆ\r\nNo Item added");
				return;
			}
		}else if(sMode=="q_ready_cancel"){
			if(confirm("ยกเลิกเรียกคิวนี้มารับยา?\r\nCancel this q?\r\nQUEUE : "+sQ )==false){
				return;
			}
		}else if(sMode=="supply_return"){
			if(confirm("คนไข้คืนยา?\r\nDrug is return?\r\nQUEUE : "+sQ )==false){
				return;
			}
		}else if(sMode=="supply_is_check"){
			if(confirm("ยืนยันตรวจสอบยาถูกต้อง?\r\nConfirm the prepare medicine is corrected and ready to pickup?\r\nQUEUE : "+sQ )==false){
				return;
			}
		}else if(sMode=="supply_pickup"){
			let isValid=true; let sMsg=""; iNotPickup=0; 
			$("#divPharM #divPharPInfo .supply-order-list .data-row").each(function(ix,objx){
				if($(objx).attr('data-isservice')=="1"){
					if($(objx).attr('data-ispaid')!="1"){
						isValid=false;
						$(objx).notify("ยังไม่ชำระเงิน");
						//sMsg+="มี บริการ บางรายการยังไม่ได้ติดต่อการเงิน กรุณาติดต่อการเงิน\r\n";
					}
				}else if($(objx).attr('data-ispaid')!="1"){
					isValid=false;
					$(objx).notify("ยังไม่ชำระเงิน");
						//sMsg+="มี ของ บางรายการยังไม่ได้ติดต่อการเงิน กรุณาติดต่อการเงิน\r\n";
				}else if($(objx).attr('data-ispickup')!="1"){
					
				}
				iNotPickup++;
			});

			if(iNotPickup==0){
				$.notify("ไม่พบ รายการใดๆ\r\nNo Item added");
				return;
			}else if(!isValid){
				//$.notify(sMsg);
				return;
			}else if(iNotPickup > 0 && isValid){
			}else{
				$.notify("Somethings wrong...");
				return;
			}

			if(confirm("ยืนยันคนไข้รับยาเสร็จสิ้น?\r\nConfirm this queue pickup medicine?\r\nQUEUE : "+sQ )==false){
				return;
			}
		}

		aData={u_mode:sMode,q:sQ,uid:sUid,coldate:sColD,coltime:sColT};
		startLoad($("#divPharM #divPharPInfo .btnpharupdate"),$("#divPharM #divPharPInfo .btnpharupdate-loader"));
		callAjax(sURL,aData,function(jRes,retAData){
			endLoad($("#divPharIncM .btnpharupdate"),$("#divPharIncM .btnpharupdate-loader"));
			if(jRes.res=="1"){
				if(sMode=="prepare_drug"){
					objQ=$("#divPharM .q-row[data-queue='"+retAData.q+"']");
					$("#divPharM #divPharPInfo .btnpharupdate").hide();
					$(objQ).hide();
					colD=$(objQ).attr("data-coldate");
					colT=$(objQ).attr("data-coltime");
					$("#divPharM #divPharConfirm").append(getWaitQRow(retAData.q));
				}else if(sMode=="supply_is_check"){
					$("#divPharM #divPharPInfo .btnpharupdate").hide();
					objW=$("#divPharM #divPharConfirm .btn-q-info[data-queue='"+retAData.q+"']");
					$("#divPharM #divPharWait").append(objW);
					$("#divPharM #divPharConfirm .btn-q-info[data-queue='"+retAData.q+"']").hide();

				}else if(sMode=="q_ready_cancel"){
					$("#divPharM #divPharPInfo .btnpharupdate").hide();
					$("#divPharM .q-row[data-queue='"+retAData.q+"']").show();
					$("#divPharM #divPharWait").find(".btn-q-info[data-queue='"+sQ+"']").remove();
					$("#divPharM #divPharConfirm").find(".btn-q-info[data-queue='"+sQ+"']").remove();
				}else if(sMode=="supply_pickup"){
					$("#divPharM #divPharPInfo .btnpharupdate").hide();
					//sUid = $("#divPayment").attr('data-uid');
					//sQ=$("#divPayment").attr('data-q');
					$("#divPharM #divPharWait").find(".btn-q-info[data-queue='"+sQ+"]").hide();

					let sUrl = "queue_inc_fwd.php?uid="+sUid+"&q="+sQ;
					showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","",function(sResult){
						if(sResult=="REFRESH"){
							$("#divPharM .main_q_list").find(".main-q-row[data-queue='"+sQ+"'][data-istoday='1']").hide();
						}
					},false,function(){
						$("#divQueueFwd input[name='room_no'][data-default='9']").prop("checked",true);
						$("#divQueueFwd input[name='room_no'][data-default='9']").focus();
					});	
				}else if(sMode=="supply_return"){
					$("#divPharM #divPharPInfo .btnpharupdate").hide();
					$("#divPharM .q-row[data-queue='"+retAData.q+"']").show();
					$("#divPharM #divPharWait").find(".btn-q-info[data-queue='"+sQ+"']").remove();
					$("#divPharM #divPharConfirm").find(".btn-q-info[data-queue='"+sQ+"']").remove();
				}
			}else{
				
			}
        });
	});

	function getUIDfromQ(sQ){
		objR = $("#divPharM .q-row[data-queue='"+sQ+"']");
		if((objR).length){
			return $(objR).attr("data-uid");
		}else{
			return "";
		}
	}

	loadWaitList();

	$("#divPharM #divQueueList").off("change");
	$("#divPharM").on("change","#divQueueList",function(){
		loadWaitList();
	});

	function getWaitQRow(sQ){
		objQ=$("#divPharM .q-row[data-queue='"+sQ+"']");
		colD=$(objQ).attr("data-coldate");
		colT=$(objQ).attr("data-coltime");
		sName=$(objQ).find(".subj_name").html();
		return("<div class='fabtn btn-q-info fl-wrap-col row-color h-75 row-hover q-row' data-queue='"+sQ+"' data-uid='"+getUIDfromQ(sQ)+"' data-coldate='"+colD+"' data-coltime='"+colT+"' ><div class='h-30 fl-fix fl-mid fs-xlarge'>"+sQ+"</div><div class='h-15 fl-fix fl-mid fs-small'>"+getUIDfromQ(sQ)+"</div><div class='fl-fill lh-15 fs-small fl-mid fw-b' style='text-align:center'>"+sName+"</div></div>");
	}

	function loadWaitList(){
		objList = $("#divPharM").find(".main-q-list");
		$("#divPharM #divPharConfirm").html("");
		$("#divPharM #divPharWait").html("");

		$(objList).find(".main-q-row").each(function(ix,objx){
			sQ= $(objx).attr("data-queue");
			sUid= $(objx).attr("data-uid");
			sColD= $(objx).attr("data-coldate");
			sColT= $(objx).attr("data-coltime");
			sPrep= $(objx).attr("data-drugprep");
			sCheck= $(objx).attr("data-drugcheck");
			sPick= $(objx).attr("data-drugpick");
			sPaid= $(objx).attr("data-paid");
			sCallCode= $(objx).attr("data-qcall");
			sRoom=$(objx).attr("data-roomno");
			sCurRoom=$("#divPharM").attr('data-roomno');
			sName=$(objx).find(".subj_name").html();
			sRow=getWaitList(sQ,sUid,sColD,sColT,sName);
			if(sRoom==sCurRoom){
				if(sCheck=="" && sPrep!="" ){
					$("#divPharM #divPharConfirm").append(sRow);
					$(objx).hide();
				}else if(sCheck!="" && sPrep!="" && sPick==""){
					$("#divPharM #divPharWait").append(sRow);
					$(objx).hide();
				}else{

				}
			}
			$(objx).find(".btncallq").hide();
		});
		
		

		/*
		objList = $("#divPharM").find(".confirm-list");
			if($(objList).length){
				$("#divPharM #divPharConfirm").html($(objList).html());
		}*/
	}


});

</script>