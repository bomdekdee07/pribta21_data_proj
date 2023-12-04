<?
include("in_session.php");
include_once("in_php_function.php");
$sSecId = getQS("secid");
$sSecName = urldecode(getQS("secname"));
?>
<style>
	.expperm-list .expperm-head{
		background-color: white;
	}
	.expperm-list .expperm-cmd{
		min-width: 50px;
	}
	.expperm-list .expperm-section{
		min-width: 60px;
	}
	.expperm-list .expperm-form{
		min-width: 240px;
	}
	.expperm-list .expperm-view{
		min-width: 70px;
	}
	.expperm-list .expperm-edit{
		min-width: 70px;
	}
	.expperm-list .expperm-export{
		min-width: 70px;
	}
	.expperm-list .expperm-start{
		min-width: 60px;
	}
	.expperm-list .expperm-stop{
		min-width: 60px;
	}

	.expperm-list .expperm-action{
		min-width: 50px;
		max-width: 50px;
		text-align: center;
	}
	.expperm-list input{
		width:90%;
	}
	.expperm-list{
		vertical-align: middle;
	}
	.expperm-list .expperm-row{
		max-height: 30px;
		min-height: 30px;
		vertical-align: middle;
		text-align: center;
		align-items: center;
	}
	.expperm-list .expperm-row:hover{
		filter:brightness(80%);
	}
	.expperm-list .expperm-head{
		max-height: 50px;
	}

</style>
<div class='fl-wrap-col expperm-list smallfont'>
	<div class='fl-wrap-row expperm-head'>
		<div class='fl-fix expperm-cmd' style='text-align:center;line-height: 50px;vertical-align: middle'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-lg'></i>
		</div>
		<div class='fl-fix expperm-section'>
			Section<br/>
			<input id='txtExpPerSec' class='fill-box' data-odata='' readonly="true" value='<? echo($sSecId); ?>' />
		</div>
		<div class='fl-fill expperm-form'>
			Form<br/>
			<SELECT id='ddlExpPerForm' class='fill-box' data-secid='<? echo($sSecId); ?>'>
				<? $_GET["opt"]=1; include("list_form.php"); ?>
			</SELECT>
		</div>
		<div class='fl-fix expperm-view'>
			View<br/>
			<SELECT id='ddlExpPerView' >
				<option value='1'>Allow</option>
				<option value='0'>Not Allow</option>
			</SELECT>
		</div>
		<div class='fl-fix expperm-edit'>
			Edit<br/>
			<SELECT id='ddlExpPerEdit' >
				<option value='1'>Allow</option>
				<option value='0'>Not Allow</option>
			</SELECT>
		</div>
		<div class='fl-fix expperm-export'>
			Export<br/>
			<SELECT id='ddlExpPerExport' >
				<option value='1'>Allow</option>
				<option value='0'>Not Allow</option>
			</SELECT>
		</div>
		<div class='fl-fix expperm-start'>
			Start<br/>
			<input id='txtExpPerStart' class='datepick' data-odata='' readonly='true'  maxlength="10" />
		</div>
		<div class='fl-fix expperm-stop'>
			Stop<br/>
			<input id='txtExpPerStop' class='datepick' data-odata='' readonly='true'  maxlength="10" />
		</div>
		<div class='fl-fix expperm-action'>
			Control<br/>
			<span style='color:green'><i id='btnAddExpPerm' class="fabtn fas fa-plus-square fa-lg" data-mode='exp_perm_add'></i><i id='imgAddSpinner' style='display:none' class="fas fa-spinner fa-spin"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col expperm-body fl-auto' >
		<div class='fl-fill exppermrow-list'>
			<? $_GET["opt"]="0"; include("list_export_perm.php"); ?>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$(".datepick").datepicker({dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
function filterFormList(){
	$("#ddlExpPerForm option").show();
	$(".expperm-body .form_id_list").each(function(ix,objx){
		$("#ddlExpPerForm option[value='"+$(objx).val()+"']").hide();

	});
	$("#ddlExpPerForm").val("");
}

$("#btnAddExpPerm").unbind("click");
$("#btnAddExpPerm").on("click",function(){
	sSecId = $("#ddlExpPerForm").attr('data-secid');
	sFormId = $("#ddlExpPerForm").val();
	sAllowView = $("#ddlExpPerView").val();
	sAllowEdit = $("#ddlExpPerEdit").val();
	sAllowExport = $("#ddlExpPerExport").val();
	sMode = $(this).attr('data-mode');
	sStartD = $("#txtExpPerStart").val();
	sStopD = $("#txtExpPerStop").val();
	sFormName = $("#ddlExpPerForm option[value='"+sFormId+"']").text();

	if(sSecId!="" && sFormId!=""){
		var aData = {u_mode:sMode,secid:sSecId,formid:sFormId,allowview:sAllowView,allowedit:sAllowEdit,allowexport:sAllowExport,startd:sStartD,stopd:sStopD,formname:sFormName};
		startLoad($(".expperm-head #btnAddExpPerm,.expperm-head #btnCancelData"),$(".expperm-head #imgAddSpinner"));

		callAjax("setting_a_export.php",aData,function(rtnObj,aData){
		if(rtnObj.res!="1"){
			$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");

		}else if(rtnObj.res=="1"){
			$.notify("Data Saved","success");

			if($("#btnAddExpPerm").attr("data-mode")=="exp_perm_update"){
				sFormId = aData.formid
				sObj = $(".expperm-body").find(".expperm-row[data-formid='"+sFormId+"']");
				$(sObj).find(".expperm-start").html(aData.startd);
				$(sObj).find(".expperm-stop").html(aData.stopd);
				$(sObj).find(".expperm-view").html(aData.allowview);
				$(sObj).find(".expperm-edit").html(aData.allowedit);
				$(sObj).find(".expperm-export").html(aData.allowexport);

			}else if($("#btnAddExpPerm").attr("data-mode")=="exp_perm_add"){
				$(".expperm-body .exppermrow-list").prepend(rtnObj.msg);
				filterFormList();
			}
			$(".expperm-cmd #btnCancelData").trigger("click");
		}
		//
		endLoad($(".expperm-head #btnCancelData,.expperm-head #btnAddExpPerm"),$(".expperm-head #imgAddSpinner"));

	});
	}
});
$(".expperm-cmd #btnCancelData").unbind("click");
$(".expperm-cmd #btnCancelData").on("click",function(){
	$("#txtExpPerStart").val("");
	$("#txtExpPerStop").val("");
	$("#ddlExpPerForm").removeAttr("readonly");
	$("#ddlExpPerForm option").show();
	$("#ddlExpPerForm").val("");
	$("#btnAddExpPerm").attr("data-mode","exp_perm_add");
	filterFormList();
});

$(".exppermrow-list .btnexppermdelete").unbind("click");
$(".exppermrow-list .btnexppermdelete").on("click",function(){
	sSecId = $(this).attr('data-secid');
	sFormId = $(this).attr('data-formid');
	if(!confirm("Do you want to delete this export permission?\r\nยืนยันลบข้อมูล\r\n"+sFormId)) return;

	if(sSecId!="" && sFormId!=""){
		var aData = {u_mode:"exp_perm_delete",secid:sSecId,formid:sFormId};
		sObjRow = $(this).closest(".expperm-row");
		$(sObjRow).find(".fabtn").hide();
		$(sObjRow).find(".rowspinner").show();

		callAjax("setting_a_export.php",aData,function(rtnObj,aData){
			objRow = $(".expperm-body .btnexppermdelete[data-secid='"+aData.secid+"'][data-formid='"+aData.formid+"']");

			if(rtnObj.res!="1"){
				$.notify("Data is not removed. Please try again\r\n"+rtnObj.msg,"error");
				//$(objRow).removeClass("fa-spin");
				$(objRow).find(".fabtn").show();
				$(objRow).find(".rowspinner").hide();
			}else if(rtnObj.res=="1"){
				$.notify("Data removed","success");
				$(objRow).closest(".expperm-row").remove();
				filterFormList();
			}
		});
	}
});

$(".exppermrow-list .btneditexpperm").unbind("click");
$(".exppermrow-list .btneditexpperm").on("click",function(){
	objRow = $(this).closest(".expperm-row");
	sFormId = $(this).attr("data-formid");
	sAllowView= $(objRow).find(".expperm-view").html();
	sAllowEdit= $(objRow).find(".expperm-edit").html();
	sAllowExport= $(objRow).find(".expperm-export").html();
	sStartD = $(objRow).find(".expperm-start").html();
	sStopD = $(objRow).find(".expperm-stop").html();

	$("#ddlExpPerForm").attr("readonly","true");
	$("#ddlExpPerForm option").hide();
	$("#ddlExpPerForm option[value='"+sFormId+"']").show();
	$("#ddlExpPerForm").val(sFormId);

	$("#txtExpPerStart").val(sStartD);
	$("#txtExpPerStop").val(sStopD);
	$("#ddlExpPerView").val(sAllowView);
	$("#ddlExpPerEdit").val(sAllowEdit);
	$("#ddlExpPerExport").val(sAllowExport);
	$("#btnAddExpPerm").attr('data-mode',"exp_perm_update");
});



	

	filterFormList();
});//END Document Ready

</script>