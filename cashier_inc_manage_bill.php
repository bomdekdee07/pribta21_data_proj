<?
include_once("in_session.php");
include_once("in_php_function.php");
$sBillId = urldecode(getQS("billid"));
$sClinicId=getSS("clinic_id");
$sToday=date("Y-m-d");
$sColD=getQS("coldate");


?>

<div id='divCIMB' class='fl-wrap-row' data-billid='<? echo($sBillId); ?>'>
	<div class='fl-wrap-col'>
		<div id='divCashierDetail' class='fl-wrap-col'>

		</div>
		<div id='divBtnSelect' class='fl-wrap-row h-30' data-uid=''  style='display:none'>
			<div class='fl-fill'></div>
			<div id='btnAddQ' class='fabtn fl-fix f-border bg-color-2 fl-mid w-200' data-q='' data-uid='' data-coldate=''><i class='fa fa-check-square fa-lg'> SELECT</i></div>
			<div class='fl-fill'></div>
		</div>
		<div id='divBtnSelect-loader' class='fl-wrap-row h-30 fl-mid' data-uid=''  style='display:none'>
			<i class='fa fa-spinner fa-spin fa-lg'></i>
		</div>
	</div>
	<div id='divUIDList' class='fl-wrap-col w-300'>
		<div class='fl-wrap-row h-30' style='background-color:red;color:white'>
			UID ที่ยังไม่มีบิล/Available UID
		</div>
		<div class='fl-wrap-row h-30'>
			<div class='fl-fix w-30 fl-mid'><i class='fa fa-calendar fa-lg'></i></div>
			<div class='fl-fill fl-mid'><input id='txtBillDate' class='w-fill' value='<? echo($sColD); ?>' tabindex="-1"  /></div>
			<div class='fl-fill fl-mid'><input id='txtNameFilter' class='w-fill' placeholder="ค้นหาชื่อ" /></div>
			<div class='fl-fix w-30 fl-mid fabtn'><i class='fa fa-search fa-lg'></i></div>
		</div>
		<div id='divUIDListDetail' class='fl-wrap-col fl-auto'>
			<? include("cashier_inc_unbill_list.php"); ?>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		var isRefresh = "";
		//include("cashier_inc_summary.php");
		$("#divCIMB #txtBillDate").datepicker({dateFormat:"yy-mm-dd",
	        changeYear:true,
	        changeMonth:true
    	});

		$("#divCIMB #txtBillDate").on("change",function(){
			objMain=$(this).closest("#divCIMB");
			sDate=$(this).val();
			sUrl="cashier_inc_unbill_list.php?coldate="+sDate;
			$(objMain).find("#divUIDListDetail").load(sUrl,function(){
				
			});
		});


		$("#divCIMB #divUIDList .btn-uid").off("click");
		$("#divCIMB #divUIDList").on("click",".btn-uid",function(){
			if($(this).hasClass("btn-selected")) return;
			$("#divCIMB #divUIDList .btn-selected").removeClass("btn-selected");
			$(this).addClass("btn-selected");

			sUid=$(this).attr('data-uid');
			sColD=$(this).attr('data-coldate');
			sColT=$(this).attr('data-coltime');
			sQ=$(this).attr('data-q');

			sUrl= "cashier_inc_summary.php?"+qsTxt(sUid,sColD,sColT);
			$("#divCIMB #divCashierDetail").load(sUrl,function(){
				$("#divCIMB #divBtnSelect").show();
				$("#divCIMB #divBtnSelect #btnAddQ").attr("data-q",sQ);
				$("#divCIMB #divBtnSelect #btnAddQ").attr("data-uid",sUid);
				$("#divCIMB #divBtnSelect #btnAddQ").attr("data-coldate",sColD);
			});
		});

		$("#divCIMB #txtNameFilter").off("keyup");
		$("#divCIMB #txtNameFilter").on("keyup",function(e){
			sVal=$(this).val();
			$("#divUIDListDetail .data-row").hide();
			$("#divUIDListDetail .fname:contains('"+sVal+"')" ).closest(".data-row").show();
		});

		$("#divCIMB #divBtnSelect").off("click");
		$("#divCIMB #divBtnSelect").on("click","#btnAddQ",function(){
			sQ=$(this).attr("data-q");
			sUid=$(this).attr("data-uid");
			sColD=$(this).attr("data-coldate");
			sBtn=$(this);
			sBillId=$("#divCIMB").attr("data-billid");
			objTotal = $("#divCIMB #divCashierDetail").find("#divSupplyOrder");
			iTotal=0;
			if($(objTotal).length){
				iTotal = $(objTotal).attr("data-total");
			}

			sUrl="cashier_a.php";
			aData={u_mode:"bill_add_uid",billid:sBillId,q:sQ,uid:sUid,coldate:sColD};
			startLoad($(sBtn),$("#divCIMB #divBtnSelect-loader"));
			callAjax(sUrl,aData,function(jRes,rData){
				if(jRes.res=="1"){
					isRefresh="REFRESH";
					closeDlg($(sBtn),"REFRESH");
				}else{
					$.notify(jRes.res,"error");
					endLoad($(sBtn),$("#divCIMB #divBtnSelect-loader"));
				}
				
	    	});
		});
	});

</script>