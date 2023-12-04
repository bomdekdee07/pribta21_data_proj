<?
	include("in_session.php");
	include_once("in_php_function.php");
?>
<style>
	.section-list .sec-head{
		background-color: white;
	}
	.section-list .sec-cmd{
		min-width: 80px;
		text-align: center;
	}
	.section-list .sec-id{
		min-width: 150px;
	}
	.section-list .sec-name{
		min-width: 300px;
	}
	.section-list .sec-note{
		
	}
	.section-list .sec-enable{
		min-width: 90px;
	}
	.section-list .sec-action{
		min-width: 100px;
		max-width: 100px;
		text-align: center;
	}
	.section-list input{
		width:90%;
	}
	.section-list{
		vertical-align: middle;
	}
	.section-list .sec-row{
		max-height: 30px;
		min-height: 30px;
		vertical-align: middle;
		text-align: center;
		align-items: center;
	}
	.section-list .sec-row:hover{
		filter:brightness(80%);
	}
	.section-list .sec-head{
		max-height: 50px;
		font-size:12px;
	}
	.section-list .sec-row:nth-child(even){
		background-color: white;
	}
	.section-list .sec-row:nth-child(odd){
		background-color: #b8d1f3;
	}

</style>
<div class='fl-wrap-col section-list'>
	<div class='fl-wrap-row sec-head'>
		<div class='fl-fix sec-cmd' style='text-align:center;line-height: 50px;vertical-align: middle'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix sec-id'>
			ID*<br/>
			<input id='txtSecId' data-odata='' maxlength="20" />
		</div>
		<div class='fl-fix sec-name'>
			Name*<br/>
			<input id='txtSecName' data-odata='' maxlength="100" />
		</div>
		<div class='fl-fill sec-note'>
			Note<br/>
			<input id='txtSecNote' data-odata=''  maxlength="300" />
		</div>
		<div class='fl-fix sec-enable'>
			Enable<br/>
			<SELECT id='ddlSecEnable' data-odata=''>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix sec-action'>
			Control<br/>
			<span style='color:green'><i id='btnAddSection' class="fabtn fas fa-plus-square fa-lg" data-mode='sec_add'></i><i id='imgAddSpinner' style='display:none' class="fas fa-spinner fa-spin"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col secrow-body fl-auto' >
		<div class='fl-fill secrow-list'>
		<? include("list_section.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){

$("#btnAddSection").unbind("click");
$("#btnAddSection").on("click",function(){
	sSecId= $("#txtSecId").val().trim();
	sSecName= $("#txtSecName").val().trim();
	sSecNote= $("#txtSecNote").val().trim();
	sSecEnable= $("#ddlSecEnable").val();
	sMode = $("#btnAddSection").attr("data-mode");
	var aData = {u_mode:sMode,secid:sSecId,secname:sSecName,secnote:sSecNote,secenable:sSecEnable};
	if(sSecId=="" || sSecName==""){
		$.notify("Please complete require input.");
		return;
	}

	startLoad($("#btnAddSection,#btnCancelData"),$("#imgAddSpinner"));

	callAjax("setting_a_section.php",aData,function(rtnObj,aData){
		if(rtnObj.res!="1"){
			$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");

		}else if(rtnObj.res=="1"){
			$.notify("Data Saved","success");

			if($("#btnAddSection").attr("data-mode")=="sec_update"){
				sSecId = $("#txtSecId").val();
				sTRow = $(".secrow-body").find(".btneditsec[data-secid='"+sSecId+"']");
				sObj = $(sTRow).closest(".sec-row");
				$(sObj).find(".sec-name").find("span").html($("#txtSecName").val().trim());
				$(sObj).find(".sec-note").find("span").html($("#txtSecNote").val().trim());
				$(sObj).find(".sec-enable").find(".ddlsecenable").val($("#ddlSecEnable").val());
			}else if($("#btnAddSection").attr("data-mode")=="sec_add"){
				$(".secrow-body .secrow-list").prepend(rtnObj.msg);
			}
			$("#btnCancelData").trigger("click");
		}
		//
		endLoad($("#btnAddSection"),$("#imgAddSpinner"));

	});
});

$(".sec-cmd #btnCancelData").unbind("click");
$(".sec-cmd #btnCancelData").on("click",function(){
	$(".sec-head input").val("");
	$(".sec-head input").attr("data-odata","");
	$("#txtSecId").removeAttr("readonly");
	$("#ddlSecEnable").val("1");
	$("#ddlSecEnable").attr("data-odata","");
	$("#btnAddSection").attr("data-mode","sec_add");
});

$(".secrow-body .btnsecdelete").unbind("click");
$(".secrow-body").on("click",".btnsecdelete",function(){
	sSecId = $(this).attr('data-secid');
	if(!confirm("Do you want to delete this section?\r\nยืนยันลบข้อมูล\r\n"+sSecId)) return;
	
	if(sSecId!="" && sSecId!=undefined){
		var aData = {u_mode:"sec_delete",secid:sSecId};

		sObjRow = $(this).closest(".sec-row");
		$(sObjRow).find(".fabtn").hide();
		$(sObjRow).find(".rowspinner").show();
		//$(this).addClass("fa-spin");
		//$(this).closest(".sec-row").find(".btneditsec").hide();

		callAjax("setting_a_section.php",aData,function(rtnObj,aData){
			objRow = $(".secrow-body .btnsecdelete[data-secid='"+sSecId+"']");

			if(rtnObj.res!="1"){
				$.notify("Data is not removed. Please try again\r\n"+rtnObj.msg,"error");
				//$(objRow).removeClass("fa-spin");
				$(objRow).find(".fabtn").show();
				//$(objRow).closest(".sec-row").find(".btneditsec").show();
			}else if(rtnObj.res=="1"){
				$.notify("Data removed","success");
				$(objRow).closest(".sec-row").remove();
			}
		});
	}
});

$(".secrow-body .btneditsec").unbind("click");
$(".secrow-body").on("click",".btneditsec",function(){

	sObj = $(this).closest(".sec-row");
	sId = $(sObj).find(".sec-id").find("span").html();
	sName = $(sObj).find(".sec-name").find("span").html();
	sNote = $(sObj).find(".sec-note").find("span").html();
	sEnable = $(sObj).find(".sec-enable").find(".ddlsecenable").val();


	$("#txtSecId").attr("readonly",true);
	$("#txtSecId").val(sId);
	$("#txtSecId").attr("data-odata",sId);
	$("#txtSecName").val(sName);
	$("#txtSecName").attr("data-odata",sName);
	$("#txtSecNote").val(sNote);
	$("#txtSecNote").attr("data-odata",sNote);
	$("#ddlSecEnable").val(sEnable);
	$("#ddlSecEnable").attr("data-odata",sEnable);
	$("#btnAddSection").attr("data-mode","sec_update");
});

$(".secrow-body .btnsecpermission").unbind("click");
$(".secrow-body").on("click",".btnsecpermission",function(){
	sObj = $(this).closest(".sec-row");
	sName = encodeURI($(sObj).find(".sec-name").find("span").html());
	secId = $(this).attr("data-secid");

	sUrl = "section_dlg_permission.php?secid="+secId+"&secname="+sName;
	showDialog(sUrl,"Section permission management : "+sName,"480","820","",function(sResult){
		//CLose function
		if(sResult=="1"){
			//$.notify("Password Changed.","success");
		}

	},false,function(){
		//Load Done Function
	});
});
$(".secrow-body .btnmodpermission").unbind("click");
$(".secrow-body").on("click",".btnmodpermission",function(){
	sObj = $(this).closest(".sec-row");
	sName = encodeURI($(sObj).find(".sec-name").find("span").html());
	secId = $(this).attr("data-secid");

	sUrl = "module_inc_permission.php?secid="+secId+"&secname="+sName;
	showDialog(sUrl,"Module permission management : "+sName,"480","820","",
		function(sResult){
			if(sResult=="1"){}
		},false,
		function(){
			//Load Done Function
		}
	);
});

$(".secrow-body .btnexpperm").unbind("click");
$(".secrow-body").on("click",".btnexpperm",function(){
	sObj = $(this).closest(".sec-row");

	sName = encodeURI($(sObj).find(".sec-name").find("span").html());
	secId = $(this).attr("data-secid");

	sUrl = "section_dlg_export_permission.php?secid="+secId+"&secname="+sName;
	showDialog(sUrl,"Section permission management : "+sName,"480","860","",function(sResult){
		//CLose function
		if(sResult=="1"){
			//$.notify("Password Changed.","success");
		}

	},false,function(){
		//Load Done Function
	});
});
});
</script>