<?
include("in_session.php");
include_once("in_php_function.php");

$sUid=getQS("uid");
$sColDate=getQS("collect_date");
$sColTime=getQS("collect_time");

if($sColDate=="") $sColDate=getQS("coldate");
if($sColTime=="") $sColTime=getQS("coltime");

?>
<style>
	.lab-pinfo{
		max-height: 70px;
	}
	.lab-id{
		min-width:300px;
		border:1px solid #dee2e6;
	}
	.lab-result{
		border:1px solid #dee2e6;
	}
	.lab-paid{
		min-width: 50px;
		max-width: 50px;
		text-align: center;
	}
	.lab-external{
		min-width:50px;
		text-align: center;
		border:1px solid #dee2e6;

	}
	.lab-result-report{
		border:1px solid #dee2e6;
	}
	.lab-ref{
		min-width:200px;
		border:1px solid #dee2e6;

	}
	.lab-body .lab-ref{
		font-size:small;
		border:1px solid #dee2e6;
		text-align: center;
	}
	.lab-status{
		min-width:60px;
		border:1px solid #dee2e6;

	}

	.lab-note{
		border:1px solid #dee2e6;
	}
	.lab-body input{
		width:100%;
		-webkit-box-sizing: border-box; 
	    -moz-box-sizing: border-box;
	    box-sizing: border-box;
		text-align: center;

	}
	.lab-body select{
		width:100%;
		-webkit-box-sizing: border-box; 
	    -moz-box-sizing: border-box;
	    box-sizing: border-box;
		text-align: center;

	}
	.lab-body textarea{
		width:100%;
		-webkit-box-sizing: border-box; 
	    -moz-box-sizing: border-box;
	    box-sizing: border-box;
		text-align: center;

	}
	.lab-body .lab-row{
		min-height:50px;
	}
	.lab-footer{
		min-height:50px;
		max-height:50px;
		vertical-align: middle;
		align-items: center;
	}
	.lab-footer .fabtn{
		padding:10px;
		border-radius: 5px;
	}
	.subinfo{
		font-size:x-small;
	}
	.lab-body .lab-row:nth-child(odd){
		background-color:#e0f5fe;
	}
	.lab-body .lab-row:nth-child(even){
		background-color:#c8f1ff;
	}
	.lab-body .lab-row:hover{
		filter:brightness(80%);
	}
	.lab-head{
		font-weight: bold;
		max-height:30px;
	}
	.lab-info-header{
		color:white;
		font-weight: bold;
	}
	.lab-body .lab-row div{
		padding:5px;
	}
	.lab-head div{
		padding:2px 5px;
	}
	.row-unsaved{
		background-color:red;
	}
	.row-notconfirm{
		background-color:red;
	}

</style>
<div class='fl-wrap-row lab-pinfo'>
	<? include("lab_inc_patient_info.php"); ?>
</div>
<div id='divLabInfo' class='fl-wrap-col'>
	<? include("lab_inc_result.php"); ?>
</div>
<div id='divLabLoading' style='text-align: center;display:none'>
	<i class='fa fa-spinner fa-spin fa-4x' style='margin-top:5px'></i>
</div>
<script>
$(document).ready(function(){

	$(".lab-pinfo #ddlPVisit").unbind("change");
	$(".lab-pinfo #ddlPVisit").on("change",function(){
		sVisit = $(this).val();
		aVisit = sVisit.split(" ");
		sColDate=aVisit[0];
		sColTime=aVisit[1];
		sUid=$(this).attr('data-uid');
		sUrl="lab_inc_result.php?uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime;
		$("#divLabInfo").hide();
		$("#divLabLoading").show();
		$("#divLabInfo").load(sUrl,function(){
			$("#divLabInfo").show();
			$("#divLabLoading").hide();
		});

	});


	checkConfirm();

});

function checkDataChanged(){
	$(".lab-body .lab_save").each(function(ix,objx){
	
		if($(objx).attr('data-odata') != getObjValue($(objx))){
			//$.notify(sLabId+":"+$(objx).attr('data-odata')+":"+getObjValue($(objx)));
			$(objx).closest("div").addClass("row-unsaved");
		}else{
			$(objx).closest("div").removeClass("row-unsaved");
		}
	});
}

function setOdata(errList="",sTime){
	aErrList = {};
	if(errList!=""){
		//Some row not saved.
		aErrList = errList.split(",");
	}
	$(".lab-body .lab_save").each(function(ix,objx){
		if($(objx).attr('data-odata') != getObjValue($(objx))){
			curRow=$(objx).closest(".lab-row");
			$(curRow).attr('data-isconfirm',"0");

			sLabId=$(curRow).attr('data-labid');
			if($.inArray(sLabId, aErrList) === -1){
				$(objx).closest("div").removeClass("row-unsaved");
				$(objx).attr("data-odata",getObjValue(objx));
				$(curRow).find(".lupdate").html(sTime);
				$(curRow).find(".lconfirm").html("0000-00-00 00:00:00");
				$(curRow).find(".lconfirm").addClass("row-notconfirm");
			}else{
				//On Error
			}
		}
	});
	checkConfirm();
}

function checkConfirm(){
	$(".lab-footer #btnConfirmLabResult").hide();
	$(".lab-footer #ddlLabMT").hide();
	$(".lab-body .lconfirm").each(function(ix,objx){
		objRow = $(objx).closest(".lab-row");
		sHtml = $(objx).html();
		sResult = $(objRow).find(".lresult").val();
		
		if((sHtml=="" || sHtml=="0000-00-00 00:00:00") && sResult!=""){
			//Data Save but not confirm
			$(objx).addClass("row-notconfirm");
			$(".lab-footer #btnConfirmLabResult").show();
			$(".lab-footer #ddlLabMT").show();
		}else{
			$(objx).removeClass("row-notconfirm");
		}
	});
}

function getA_LabDataChanged(){
	var aTempId=[];
	$(".lab-body .lab_save").each(function(ix,objx){
		sLabId = $(objx).closest(".lab-row").data("labid");
		
		if($(objx).attr('data-odata') != getObjValue($(objx))){
			//$.notify(sLabId+":"+$(objx).attr('data-odata')+":"+getObjValue($(objx)));
			if($.inArray(sLabId, aTempId) === -1) aTempId.push(sLabId);
		}
	});

	return aTempId;
}

function getObjValue(sObj){
	if($(sObj).attr("type") == "checkbox"){
		
		return ($(sObj).is(":checked")?"1":"0");
	}else{
		return $(sObj).val();
	}
}

</script>
