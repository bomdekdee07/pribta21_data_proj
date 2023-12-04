<?
include("in_session.php");
include_once("in_php_function.php");
date_default_timezone_set("Asia/Bangkok");
$sSid = getSS("s_id");
$sToday = date("Y-m-d");
$sClinic = getSS("clinic_id");
$sTargetPage =getQS("target");
$isHideCall=getQS("hidecall");
$isWaitList=getQS("waitlist");
$isFormDone=getQS("is_form_done");
$sRoom = getSS("room_no");
$sRoomName = getSS("room_detail");
$sModule=getQS("module");
/*Module 
	PHARMACY,PHYSICIAN,RECEPTION,CASHIER
*/

$sHtml = "";
$sHtmlNoRoom="";
$sCurQ="";
$sCurUid="";
$sDifTime="";
$sCurTime="";
if($sSid==""){
	//Not Login
	echo("<div class='btnquicklogin'>Please login</div>");
	exit();
}
include("in_db_conn.php");

//Not enter the room. Confirm it
if($sRoom=="" || $sRoomName=="" ){
	//Not in the room. Show room enter option

}else{
	//Staff is in the room GET THE Current Q
	/*
	$query="SELECT queue,collect_date,collect_time,queue_datetime,fname,sname,en_fname,en_sname,IQL.uid FROM i_queue_list IQL
	LEFT JOIN patient_info PI
	ON IQL.uid=PI.uid
	WHERE queue_status = 2 AND clinic_id=? AND room_no=? AND collect_date=?";

	$sHtml="";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinic,$sRoom,$sToday);

	$dtNow = new DateTime();

	if($stmt->execute()){
	  $stmt->bind_result($queue,$collect_date,$collect_time,$queue_datetime,$fname,$sname,$en_fname,$en_sname,$uid );
	  while ($stmt->fetch()) {

	  	$dtQTime = new DateTime($queue_datetime);
	  	$sDifTime = $dtQTime->diff($dtNow);
		$sDifTime = $sDifTime->format('%H:%i:%s');

		$sCurQ = $queue;
		$sCurTime = $queue_datetime;
		$sCurUid = $uid;
	  }
	}
	*/
}

$optRoomList ="";

$mysqli->close();

?>

<div id='divQMain' class='fl-wrap-col' data-module='<? echo($sModule); ?>' data-target='<? echo($sTargetPage); ?>' data-isformdone='<? echo($isFormDone); ?>' data-hidecall='<? echo($isHideCall); ?>' data-waitlist='<? echo($isWaitList); ?>' >
	<div id='divRoomInfo' class='fl-wrap-row h-25'>
		<? include("staff_room_inc_info.php"); ?>
	</div>
	<div id='divCurQueue' class='fl-wrap-col hideme h-130'>
		<?  if($isHideCall!="1") {$_GET["u_mode"]="q_current";  include("queue_a.php");} ?>
	</div>
	<div id='divCurQueue-loader' class='fl-wrap-col fl-mid' style='display:none'>
		<i class='fa fa-spinner fa-spin fa-2x'></i>
	</div>
	<div class='fl-wrap-row h-25' style='background-color: #00D9D9'>
		<div class='fl-fix w-50 lh-25' style='text-align:center;color:orange'><input id='chkPlayAlert' type='checkbox' class='bigcheckbox' checked /><i class='fas fa-bell'></i></div>
		<div class='fl-fix w-15'></div>
		<div id='btnAddInhouse' class='fl-fix w-30 fabtn fl-mid' style='color:green;<? if($sModule=="CASHIER" || $sModule=="RECEPTION" || $sModule=="PHARMACY"){}else echo("display:none;"); ?>' title='เพิ่ม Q ใช้ภายใน Clinic / Add Q for In House Usage'>
			<i class='fa fa-clinic-medical'></i>
		</div>
		<div class='fl-fill al-right lh-25 fs-smaller fl-mid'>
			<? echo($sToday); ?> [<span class='qtotal'></span>]
		</div>
		<div id='btnAddAnony' class='fl-fix w-30 fabtn fl-mid' style='color:green;<? if($sModule=="CASHIER" || $sModule=="RECEPTION" || $sModule=="PHARMACY"){}else echo("display:none;"); ?>' title='เพิ่ม Q ซื้อของ / Add Customer Q for buy things'>
			<i class='fa fa-cart-plus'></i>
		</div>
		<div class='fl-fix w-15'></div>
		<div class='fl-fix w-50'>
			<label><input id='chkShowAllQ' type='checkbox' class='bigcheckbox' />All</label>
		</div>
	</div>
	<div id='divQueueList' class='fl-fill fl-auto'>
		<? include("queue_inc_list_new.php"); ?>
	</div>
</div>


<script>
$(document).ready(function(){
	$.ajaxSetup({ cache: false });
	var prevQList = [];
	checkCurQ();

	function checkCurQ(){
		if($("#divCurQueue").html().trim()!=""){
			$("#divCurQueue").show();
			$("#divCurQueue .cur-q-row").show();
		}else{
			$("#divCurQueue").hide();
		}
	}

	function showMainQ(sQueue){
		$("#divQueueList").find(".main-q-list").find(".main-q-row[data-queue='"+sQueue+"']").show();
	}

	addCurrentQ();
	
	$("#divQMain #btnRoomLogIn").off("click");
	$("#divQMain").on("click","#btnRoomLogIn",function(){
		sUrl = "room_inc_selector.php";
		showDialog(sUrl,"Room In/Out","480","720","",function(sResult){
		//CLose function
			if(sResult=="1"){
				$("#divRoomInfo").html("loading...");
				$("#divRoomInfo").load("staff_room_inc_info.php");
			}	
		},false,function(){
			//Load Done Function
		});
	});


	$("#divQMain #btnAddInhouse").off("click");
	$("#divQMain").on("click","#btnAddInhouse",function(){
		if(!(confirm("ยืนยันสร้างคิวใช้ภายในคลินิก? \r\n Confirm Create In House Queue?"))){
			return;
		}
		sModule=$("#divQMain").attr('data-module');

		sRoom = "2";
		if(sModule=="CASHIER") sRoom="26";
		else if(sModule=="PHARMACY") sRoom="27";
		else if(sModule=="RECEPTION") sRoom="2";
		aData={u_mode:"q_create_inhouse",room_no:sRoom};
		
		callAjax("queue_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res=="0"){
				$.notify(rtnObj.msg);
			}else if(rtnObj.res=="1"){
				
				if(sModule=="CASHIER"){
					sTxt="&q="+rtnObj.q+"&coldate="+rtnObj.coldate+"&coltime="+rtnObj.coltime;
					showCashier("P00-00000",sTxt);
				}else if(sModule=="RECEPTION"){
					showFwdQ("P00-00000",rtnObj.q,function(){},function(){});
				}if(sModule=="PHARMACY"){
					sTxt="&q="+rtnObj.q+"&coldate="+rtnObj.coldate+"&coltime="+rtnObj.coltime;
					showPharma("P00-00000",sTxt);
				}
				
			}
			checkCurQ();
		});
	});


	$("#divQMain #btnAddAnony").off("click");
	$("#divQMain").on("click","#btnAddAnony",function(){
		if(!(confirm("ยืนยันสร้างคิวนิรนาม? \r\n Confirm Create Anonymous Queue?"))){
			return;
		}
		sModule=$("#divQMain").attr('data-module');

		sRoom = "2";
		if(sModule=="CASHIER") sRoom="26";
		else if(sModule=="PHARMACY") sRoom="27";
		else if(sModule=="RECEPTION") sRoom="2";
		aData={u_mode:"q_create_anonymous",room_no:sRoom};
		
		callAjax("queue_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res=="0"){
				$.notify(rtnObj.msg);
			}else if(rtnObj.res=="1"){
				
				if(sModule=="CASHIER"){
					sTxt="&q="+rtnObj.q+"&coldate="+rtnObj.coldate+"&coltime="+rtnObj.coltime;
					showCashier("P99-99999",sTxt);
				}else if(sModule=="RECEPTION"){
					showFwdQ("P99-99999",rtnObj.q,function(){},function(){});
				}if(sModule=="PHARMACY"){
					sTxt="&q="+rtnObj.q+"&coldate="+rtnObj.coldate+"&coltime="+rtnObj.coltime;
					showPharma("P99-99999",sTxt);
				}
				
			}
			checkCurQ();
		});
	});


	$("#divQueueList .btncallq").off("click");
	$("#divQueueList").on("click",".btncallq",function(){
		//Call Queue to the room
		objRow = $(this).closest(".q-row");
		sQ = $(objRow).attr('data-queue');
		startLoad($("#divCurQueue"),$("#divCurQueue-loader"));
		//Call Url
		var aData = {u_mode:"q_call",q:sQ};
		callAjax("queue_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res=="0"){
				$.notify(rtnObj.msg);
			}else if(rtnObj.res=="1"){
				$("#divCurQueue").html(rtnObj.msg);
				hideCurQRow();
			}
			endLoad($("#divCurQueue"),$("#divCurQueue-loader"));
			checkCurQ();
		});
	});

	$("#divCurQueue #btnCancelCallQ").off("click");
	$("#divCurQueue").on("click","#btnCancelCallQ",function(){
		//Cancel Queue to the room
		sQ = $(this).closest(".cur-q-row").attr('data-queue');
		startLoad($("#divCurQueue"),$("#divCurQueue-loader"));
		//Call Url
		var aData = {u_mode:"q_cancel",q:sQ};
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="0"){
				$.notify(rtnObj.msg);
			}else if(rtnObj.res=="1"){
				$("#divCurQueue").html("");

			}
			endLoad($("#divCurQueue"),$("#divCurQueue-loader"));
			checkCurQ();
			showMainQ(sQ);
		});
	});



	$("#divCurQueue #btnCancelQ").off("click");
	$("#divCurQueue").on("click","#btnCancelQ",function(){
		//Cancel Queue to the room
		if(confirm("ยืนยัน ยกเลิกคิวในห้อง? **ผู้รับบริการจะกลับไปต่อคิวในห้องนี้\r\nConfirm get patient out of the room? **The patient will waiting to call for this room.")){
			sQ = $(this).closest(".cur-q-row").attr('data-queue');
			startLoad($("#divCurQueue"),$("#divCurQueue-loader"));
			//Call Url
			var aData = {u_mode:"q_cancel",q:sQ};
			callAjax("queue_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					$.notify(rtnObj.msg);
				}else if(rtnObj.res=="1"){
					$("#divCurQueue").html("");

				}
				endLoad($("#divCurQueue"),$("#divCurQueue-loader"));
				checkCurQ();
				showMainQ(sQ);
			});
		}
	});



	$("#divQMain #btnConfirmQ").off("click");
	$("#divQMain").on("click","#btnConfirmQ",function(){
		//Cancel Queue to the room
		sQ = $(this).closest(".cur-q-row").attr('data-queue');
		startLoad($("#divCurQueue"),$("#divCurQueue-loader"));
		//Call Url
		var aData = {u_mode:"q_confirm",q:sQ};
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="0"){
				$.notify(rtnObj.msg);
			}else if(rtnObj.res=="1"){
				$("#divCurQueue").html(rtnObj.msg);
			}
			endLoad($("#divCurQueue"),$("#divCurQueue-loader"));
			checkCurQ();
		});
	});


	$("#divQMain .btn-q-no").off("click");
	$("#divQMain").on("click",".btn-q-no",function(){
		qRow = $(this).closest(".q-row");
		let sUid = $(qRow).attr('data-uid');
		let sQ = $(qRow).attr('data-queue');
		showFwdQ(sUid,sQ,function(sResult){
			if(sResult.indexOf("REFRESH") >= 0) $(qRow).hide();
		},function(){
			
		});
	});

	function showFwdQ(sUid,sQ,fncClose,fncDone){
		if(sUid=="" && sQ!=""){
			$.notify("Queue is not UID bind and can not forward. Please asked reception to bind the queue.\r\nคิวยังไม่มี UID ไม่สามารถ ส่งต่อได้ กรุณาติดต่อเจ้าหน้าที่ตอนรับ เพื่อผูกคิว");
			return;
		}else{
			let sUrl = "queue_inc_fwd.php?uid="+sUid+"&q="+sQ;
			showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","",fncClose,false,fncDone);	
		}
	}


	$("#divCurQueue #btnForwardQ").off("click");
	$("#divCurQueue").on("click","#btnForwardQ",function(){
		objR=$(this).closest(".cur-q-row");
		sQ=$(objR).attr('data-queue');
		sUid=$(objR).attr('data-uid');
		showFwdQ(sUid,sQ,function(sResult){
			if(sResult=="1") {

				$("#divCurQueue").html("");
				$("#divCurQueue").hide();
			}
		},function(){
			
		});
	});

	$("#divQMain #chkShowAllQ").off("change");
	$("#divQMain").on("change","#chkShowAllQ",function(){
		if($(this).is(":checked")){
			$("#divQueueList .row-notin").show();
			$("#divQueueList .q-row").show();
			//$("#divQueueList .q-row").css("display","");
		}else{
			$("#divQueueList .row-notin").hide();
		}
		setQTotal();
	});



	$("#divQMain #btnRecall").off("click");
	$("#divQMain").on("click","#btnRecall",function(){
		
		sQueue = $(this).closest(".cur-q-row").attr("data-queue");
		if(sQueue=="") return;

		startLoad($("#divQMain #btnRecall"), $("#divQMain #btnRecall-loader"));
		//Call Url
		var aData = {u_mode:"q_recall",q:sQueue};
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="0"){
				$.notify("Please try again.");
			}else{

			}
			endLoad($("#divQMain #btnRecall"), $("#divQMain #btnRecall-loader"));
			checkCurQ();
		});
	});




	$("#divQMain #divQList .btn-q-info").off("click");
	$("#divQMain #divQList").on("click",".btn-q-info",function(){
		qRow = $(this).closest(".q-row");
		let sUid = $(qRow).attr('data-uid');
		let sQ = $(qRow).attr('data-queue');

		if(sUid=="" && sQ!=""){
			$.notify("Please add UID to this queue before continue");
			$("#divPInfoIdCard #btnClearInput").trigger("click");					
			$("#divPInfoIdCard #txtQueue").val(sQ);	
		}else{

			startLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
			sUrl="patient_info_idcard.php?showq=1&uid="+sUid+"&loadq=1&lockq=1&q="+sQ;
			$("#divPInfoIdCard").load(sUrl,function(){
				endLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
			});			
		}
	});




	if(aMainObj.queue_main != undefined) {
		clearInterval(aMainObj.queue_main);
	}
    
	aMainObj.queue_main = setInterval(function () { 
		loadQueueList();
	}, 10000);

	function loadQueueList(){
		isDlgClose = true;

		$('.ui-dialog-content').each(function(ix,dlg){
			if($(dlg).dialog("isOpen")){
				isDlgClose = false;
			}
		});

		if($("#divQMain #divQueueList").is(":visible") && isDlgClose){
			//Load Wait Q List
			sFD= $("#divQMain").attr("data-isformdone");
			sHC= $("#divQMain").attr("data-hidecall");
			sWL= $("#divQMain").attr("data-waitlist");
			sMod= $("#divQMain").attr("data-module");

			$("#divQMain #divQueueList").load("queue_inc_list_new.php?module="+sMod+"&is_form_done="+sFD+"&hidecall="+sHC+"&waitlist="+sWL,function(){
				setQTotal();
				checkAudio();
			});
		}else{

		}
	}
	setQTotal();
	function setQTotal(){
		//setQTotal
		$("#divQMain #divQueueList").trigger("change");
		iTotal = $("#divQMain #divQueueList .main-q-list .q-row").length;
		iNotIn = $("#divQMain #divQueueList .main-q-list .row-notin").length;

		
		//If showAll is check don't hide
		if($("#divQMain #chkShowAllQ").is(":checked")){
			$("#divQueueList .row-notin").show();
			$("#divQMain").find(".qtotal").html(iTotal);
		}else{
			$("#divQueueList .row-notin").hide();
			$("#divQMain").find(".qtotal").html(iTotal-iNotIn);
		}
	}

	function addCurrentQ(){
		$("#divQMain #divQueueList .main-q-list .q-row").each(function(i,objX){
			//Compare with prevQList
			sQ=$(objX).attr("data-queue");
			if($(objX).hasClass("row-notin") || prevQList.includes(sQ)){

			}else{
				prevQList.push(sQ);
			}

			
		});
	}
	function checkAudio(){
		if($("#divQMain #chkPlayAlert").is(":checked")){
			curQList=[]; foundNew = false;
			$("#divQMain #divQueueList .main-q-list .q-row").each(function(i,objX){
				//Compare with prevQList
				sQ=$(objX).attr("data-queue");
				if(prevQList.includes(sQ) && $(objX).hasClass("row-notin")==false){
					curQList.push(sQ);
					//console.log(sQ+"Already Exists");
				}else if($(objX).hasClass("row-notin")){

				}else{
					//console.log(sQ+" NEW Queue");
					foundNew=true;
					//prevQList.push(sQ);
					curQList.push(sQ);
				}
				
			});
			prevQList = curQList;
		
			if(foundNew){
				var promise = warnAudio.play();
				if (promise !== undefined) {
					promise.then(_ => {
						warnAudio.play();
					}).catch(error => {
				  		//console.log("Auto Play Off");
					});
				}
			} 

		}else{

		}
	}

	function hideCurQRow(){
		var sQ="";
		if($("#divCurQueue .cur-q-row").length)
		sQ = $("#divCurQueue .cur-q-row").attr("data-queue");
		if(sQ!="") $("#divQueueList .q-row[data-queue='"+sQ+"']").hide();
	}

});
	

</script>