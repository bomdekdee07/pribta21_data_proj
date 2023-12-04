<?
	include_once("in_session.php");
	include_once("in_php_function.php");
	$showAll = getQS("showall");
	$sModule = getQS("module");
	$sClinic=getSS("clinic_id");
	$sSid=getSS("s_id");
	$sShowUnPaid=getQS("showunpaid");
	$sShowUnBill=getQS("showunbill");
	$sShowOnline=getQS("showonline");

	$sBtnOpt="";
	if($sShowUnPaid==1){
		$sBtnOpt="<div id='btnUnPaidList' class='fabtn fl-fix w-20 fl-mid h-fill' title='Show all unpaid' style='color:red'><i  class='fas fa-dollar-sign' ></i></div>";
	}
	if($sShowUnBill==1){
		//$sBtnOpt.="<div id='btnUnBillList' class='fabtn fl-fix w-20 fl-mid h-fill' title='Show all unbill' style='color:red'><i  class='fas fa-file-invoice' ></i></div>";
	}
	if($sShowOnline==1){
		$sBtnOpt.="<div id='btnOnlineResult' class='fabtn fl-fix w-30 fl-mid h-fill' title='Show all waiting for lab result' style='color:orange'><i  class='fas fa-hourglass' ></i></div>";
	}
?>
<div id='divLeftBT' class='' data-showall='<? echo($showAll); ?>' data-sid='<? echo($sSid);?>' data-clinicid='<? echo($sClinic); ?>' data-module='<? echo($sModule); ?>'>
	<div class='fl-wrap-row h-25 fl-mid' style='background-color: #00D9D9'>
		<div id='btnHideResult' class='fabtn fl-fix w-10 fl-mid' style='background-color: red' title='ซ่อนข้อมูล'>-</div>
		<div id='btnTodayAppoint' class='fabtn fl-fix w-30 fl-mid h-fill' title='ดูนัดวันนี้ / Today Appointment'><i  class=" fas fa-calendar-alt"></i></div>
		<div class='fl-fix w-80 fl-mid'>
			<input id='txtHistoryDate' title='ดูคิวย้อนหลัง / View Past Date Queue' class='fill-box h-25 fs-smaller' readonly="true" />
		</div>
		
		<div id='btnMadeAppoint' class='fabtn fl-fix w-20 fl-mid h-fill' title='ดูตารางนัดทั้งหมด / View All Appointment'><i  class=" fas fa-calendar-plus" ></i></div>
		<? echo($sBtnOpt); ?>
		<div id='btnSearchPatient' class='fl-wrap-row h-fill' >
			<div class='fl-fill fl-mid'>
				<input id='txtSearchUid' class='h-25 fill-box fs-smaller al-left' placeholder="P00-00000" />
			</div>
			<div id='btnSearchUid' class='fabtn fl-fix w-25 h-fill fl-mid'><i class=" fas fa-search" title='ค้นหา / Search'></i></div>
		</div>
	</div>

	<div id='divQDetail' class='fl-fill fl-auto h-300' style='display:none'>
		<!--? include("appointments_inc_list.php");  ?-->
	</div>
	<div id='divQDetail-loader' class='fl-fill fl-mid h-300' style='display:none'>
		<i class='fa fa-spinner fa-spin  fa-3x'></i>
	</div>
</div>

<script>
$(function(){
	$("#divLeftBT #txtHistoryDate").datepicker({dateFormat:"yy-mm-dd",
        changeYear:true,
        changeMonth:true,
        maxDate: 0-1
    });

	$("#divLeftBT #btnHideResult").unbind("click");
	$("#divLeftBT #btnHideResult").on("click",function(){
		$("#divLeftBT #divQDetail").hide();
	});

	$("#divLeftBT #btnOnlineResult").unbind("click");
	$("#divLeftBT #btnOnlineResult").on("click",function(){
		sURL="patient_inc_wait_result_list.php";
		startLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		$("#divLeftBT #divQDetail").load(sURL,function(){
			endLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		});
	});

	$("#divLeftBT #btnUnBillList").unbind("click");
	$("#divLeftBT #btnUnBillList").on("click",function(){
		
	});


	$("#divLeftBT #btnUnPaidList").unbind("click");
	$("#divLeftBT #btnUnPaidList").on("click",function(){
		sURL="queue_a.php";
		aData={u_mode:"q_unpaid_list"};
		startLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
        callAjax(sURL,aData,function(jRes,rData){
        	endLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
			$("#divLeftBT #divQDetail").html(jRes.msg);
        });
	});

	$("#divLeftBT #txtHistoryDate").unbind("change");
	$("#divLeftBT #txtHistoryDate").on("change",function(){
		sDate=$(this).val();
		if(sDate=="") return;
		sUrl="queue_inc_list_new.php?mode=history&coldate="+sDate;
		startLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		$("#divLeftBT #divQDetail").load(sUrl,function(){
			endLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		});
	});
	
	$("#divLeftBT #btnMadeAppoint").unbind("click");
	$("#divLeftBT").on("click","#btnMadeAppoint",function(){
		sRow = $(this).closest("#divLeftBT");
		sClinic = $(sRow).attr("data-clinicid");
		sSid=$("#divLeftBT").attr('data-sid');
		sUrl = "appointments_calendar.php?clinic_id="+sClinic;
		showDialog(sUrl,"ทำนัดหมาย","720","1350","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});

	$("#divLeftBT #btnTodayAppoint").unbind("click");
	$("#divLeftBT").on("click","#btnTodayAppoint",function(){
		sShowAll = $("#divLeftBT").attr('data-showall');
		sUrl="appointments_inc_list.php?showall="+sShowAll;
		startLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		$("#divLeftBT #divQDetail").load(sUrl,function(){
			endLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		});
	});

	$("#divQDetail .btn-made-appoint").unbind("click");
	$("#divQDetail").on("click",".btn-made-appoint",function(){
		objRow = $(this).closest(".data-row");
		sUid = $(objRow).attr('data-uid');
		sClinic = $(objRow).attr("data-clinicid");
		sSid =$(objRow).attr("data-sid")
		sUrl = "appointments_calendar.php?clinic_id="+sClinic+"&uid="+sUid+"&s_id="+sSid;

		showDialog(sUrl,"Schedule","720","1350","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});

	$("#divQDetail .btnlabcreate").unbind("click");
	$("#divQDetail").on("click",".btnlabcreate",function(){
		objRow = $(this).closest(".q-row");
		sUid = $(objRow).attr('data-uid');
		sUrl = "proj_lab_order_new.php?uid="+sUid;

		showDialog(sUrl,"Schedule","50%","95%","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
		
	});


	$("#divLeftBT #txtSearchUid").unbind("keypress");
	$("#divLeftBT #txtSearchUid").on("keypress",function(e){
		if(e.which == 13) {
			$("#divLeftBT #btnSearchUid").click();
		}
	});

	$("#divLeftBT #btnSearchUid").unbind("click");
	$("#divLeftBT #btnSearchUid").on("click",function(){
		sUid = $("#divLeftBT #txtSearchUid").val().trim();
		sModule=$("#divLeftBT").attr("data-module");

		sQS = "";
		if(sUid.indexOf(" ") >= 0){
			//Search Name
			aT = sUid.split(" ");
			sQS+="&fname="+aT[0]+"&module="+sModule;
			if(aT[1]!=undefined && aT[1]!="") sQS+="&sname="+(aT[1]);
		}else if(sUid.length==13 && (sUid*1)==sUid){
			sQS+="&citizen_id="+sUid+"&module="+sModule;
		}else{
			sQS+="&uid="+sUid+"&fname="+sUid+"&sname="+sUid+"&module="+sModule;
		}


		sUrl="patient_inc_search_result.php?mode=short"+sQS;
		startLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		$("#divLeftBT #divQDetail").load(sUrl,function(){
			endLoad($("#divLeftBT #divQDetail"),$("#divLeftBT #divQDetail-loader"));
		});
	});

	
	$("#divQDetail .btnviewlabresult").off("click");
	$("#divQDetail").on("click",".btnviewlabresult",function(){
		objRow = $(this).closest(".q-row");
		sUid = $(objRow).attr('data-uid');
		sColDate = $(objRow).attr('data-coldate');
		sColTime = $(objRow).attr('data-coltime');

		sUrl = "lab_inc_result.php?uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime;
		showDialog(sUrl,"Lab Result","95%","95%","",function(sResult){
		//CLose function
		},false,function(){
			//Load Done Function
		});
	});

	$("#divQDetail .btn-is-confirm").unbind("click");
	$("#divQDetail").on("click",".btn-is-confirm",function(){
		sUid = $(this).closest(".data-row").attr('data-uid');
		sColDate = $(this).closest(".data-row").attr('data-date');
		sUrl = "appointment_inc_confirm.php?uid="+sUid+"&coldate="+sColDate;
		showDialog(sUrl,"Confirm Patient Appointment","400","400","",function(sResult){
		//CLose function
			if(sResult=="1"){
				$("#divQDetail .data-row .btn-is-confirm").html("");
			}	
		},false,function(){
			//Load Done Function
		});
	});
	
});

</script>