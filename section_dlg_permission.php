<?
include("in_session.php");
include_once("in_php_function.php");
$sSecId = getQS("secid");
$sSecName = urldecode(getQS("secname"));
?>
<style>
	.secperm-list .secperm-head{
		background-color: white;
	}
	.secperm-list .secperm-cmd{
		min-width: 50px;
	}
	.secperm-list .secperm-pseq{
		min-width: 80px;
	}
	.secperm-list .secperm-title{
		min-width: 200px;
	}
	.secperm-list .secperm-enable{
		min-width: 80px;
	}
	.secperm-list .secperm-start{
		min-width: 100px;
	}
	.secperm-list .secperm-stop{
		min-width: 100px;
	}
	.secperm-list .secperm-admin{
		min-width: 100px;
	}
	.secperm-list .secperm-action{
		min-width: 50px;
		max-width: 50px;
		text-align: center;
	}
	.secperm-list input{
		
	}
	.secperm-list{
		vertical-align: middle;
	}
	.secperm-list .secperm-row{
		max-height: 30px;
		min-height: 30px;
		vertical-align: middle;
		text-align: center;
		align-items: center;
	}
	.secperm-list .secperm-row:hover{
		filter:brightness(80%);
	}
	.secperm-list .secperm-head{
		max-height: 50px;
	}
	.secperm-list .secperm-row:nth-child(even){
		background-color: white;
	}
	.secperm-list .secperm-row:nth-child(odd){
		background-color: #b8d1f3;
	}

</style>
<div class='fl-wrap-col secperm-list smallfont'>
	<div class='fl-wrap-row secperm-head h-50'>
		<div class='fl-fix secperm-cmd' style='text-align:center;line-height: 50px;vertical-align: middle'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix secperm-pseq'>
			Seq<br/>
			<input id='txtSecPerPSeq' class='fill-box h-25' data-odata='' maxlength="5" />
		</div>
		<div class='fl-fix secperm-title'>
			Title<br/>
			<SELECT id='ddlSecPermPageList' class=' fill-box h-25' data-secid='<? echo($sSecId); ?>'>
				<? $_GET["opt"]="1"; include("list_page.php"); ?>
			</SELECT>
		</div>
		<div class='fl-fix secperm-enable'>
			Enable<br/>
			<SELECT id='ddlSecPermPageEnable' class=' fill-box h-25' data-odata=''>
				<option value='1'>Enable</option>
			</SELECT>
		</div>
		<div class='fl-fix secperm-stop'>
			Start<br/>
			<input id='txtSecPerStart' class='datepick fill-box h-25' data-odata='' readonly='true'  maxlength="10" />
		</div>
		<div class='fl-fix secperm-stop'>
			Stop<br/>
			<input id='txtSecPerStop' class='datepick fill-box h-25' data-odata='' readonly='true'  maxlength="10" />
		</div>
		<div class='fl-fix secperm-admin'>
			IsAdmin<br/>
			<SELECT id='ddlSecPermIsAdmin' data-odata='' class=' fill-box h-25'>
				<option value='0'>User</option>
				<option value='1'>Admin</option>
			</SELECT>
		</div>
		<div class='fl-fill secperm-action'>
			Control<br/>
			<span style='color:green'><i id='btnAddSecPerm' class="fabtn fas fa-plus-square fa-lg" data-mode='sec_perm_add'></i><i id='imgAddSpinner' style='display:none' class="fas fa-spinner fa-spin"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col secperm-body fl-auto' >
		<div class='fl-fill secpermrow-list'>
			<? $_GET["opt"]="0"; include("list_section_perm.php"); ?>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$(".datepick").datepicker({dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
function filterPageList(){
	$("#ddlSecPermPageList option").show();
	$(".secperm-body .page_id_list").each(function(ix,objx){
		$("#ddlSecPermPageList option[value='"+$(objx).val()+"']").hide();
	});
	$("#ddlSecPermPageList").val("");
}

$("#btnAddSecPerm").unbind("click");
$("#btnAddSecPerm").on("click",function(){
	sSecId = $("#ddlSecPermPageList").attr('data-secid');
	sPageSeq = $("#txtSecPerPSeq").val();
	sPageId = $("#ddlSecPermPageList").val();
	sPageTitle = $("#ddlSecPermPageList option[value='"+sPageId+"']").text();
	sPageAllow = $("#ddlSecPermPageEnable").val();
	sMode = $(this).attr('data-mode');
	sStartD = $("#txtSecPerStart").val();
	sStopD = $("#txtSecPerStop").val();
	sAdmin = $("#ddlSecPermIsAdmin").val();
	if(sSecId!="" && sPageId!=""){
		var aData = {u_mode:sMode,secid:sSecId,pageid:sPageId,pageallow:sPageAllow,pagetitle:sPageTitle,startd:sStartD,stopd:sStopD,pseq:sPageSeq,isadmin:sAdmin};
		startLoad($(".secperm-head #btnAddSecPerm,.secperm-head #btnCancelData"),$(".secperm-head #imgAddSpinner"));

		callAjax("setting_a_section.php",aData,function(rtnObj,aData){
		if(rtnObj.res!="1"){
			$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");

		}else if(rtnObj.res=="1"){
			$.notify("Data Saved","success");

			if($("#btnAddSecPerm").attr("data-mode")=="sec_perm_update"){
				sPageId = aData.pageid
				sObj = $(".secperm-body").find(".secperm-row[data-pageid='"+sPageId+"']");
				$(sObj).find(".secperm-start").html(aData.startd);
				$(sObj).find(".secperm-stop").html(aData.stopd);
				$(sObj).find(".secperm-pseq").html(aData.pseq);
				$(sObj).find(".secperm-admin").html(aData.isadmin);
			}else if($("#btnAddSecPerm").attr("data-mode")=="sec_perm_add"){
				$(".secperm-body .secpermrow-list").prepend(rtnObj.msg);
				filterPageList();
			}
			$(".secperm-cmd #btnCancelData").trigger("click");
		}
		//
		endLoad($(".secperm-head #btnCancelData,.secperm-head #btnAddSecPerm"),$(".secperm-head #imgAddSpinner"));

	});
	}
});
$(".secperm-cmd #btnCancelData").unbind("click");
$(".secperm-cmd #btnCancelData").on("click",function(){
	$(".secperm-head input").val("");
	$(".secperm-head input").attr("data-odata","");
	$("#ddlSecPermPageList").removeAttr("readonly");
	$("#btnAddSection").attr("data-mode","secperm_add");
});

$(".secpermrow-list .btnsecpermdelete").unbind("click");
$(".secpermrow-list .btnsecpermdelete").on("click",function(){
	sSecId = $(this).attr('data-secid');
	sPageId = $(this).attr('data-pageid');
	if(!confirm("Do you want to delete this section permission?\r\nยืนยันลบข้อมูล\r\n"+sSecId)) return;

	if(sSecId!="" && sPageId!=""){
		var aData = {u_mode:"sec_perm_delete",secid:sSecId,pageid:sPageId};
		sObjRow = $(this).closest(".secperm-row");
		$(sObjRow).find(".fabtn").hide();
		$(sObjRow).find(".rowspinner").show();

		callAjax("setting_a_section.php",aData,function(rtnObj,aData){
			objRow = $(".secperm-body .btnsecpermdelete[data-secid='"+aData.secid+"'][data-pageid='"+aData.pageid+"']");

			if(rtnObj.res!="1"){
				$.notify("Data is not removed. Please try again\r\n"+rtnObj.msg,"error");
				//$(objRow).removeClass("fa-spin");
				$(objRow).find(".fabtn").show();
				$(objRow).find(".rowspinner").hide();
			}else if(rtnObj.res=="1"){
				$.notify("Data removed","success");
				$(objRow).closest(".secperm-row").remove();
				filterPageList();
			}
		});
	}
});

$(".secpermrow-list .btneditsecperm").unbind("click");
$(".secpermrow-list .btneditsecperm").on("click",function(){
	objRow = $(this).closest(".secperm-row");
	sPageId = $(this).attr("data-pageid");
	sPageSeq= $(objRow).find(".secperm-pseq").html();
	sStartD = $(objRow).find(".secperm-start").html();
	sStopD = $(objRow).find(".secperm-stop").html();
	sAdmin = $(objRow).find(".secperm-admin").html();

	$("#txtSecPerStart").val(sStartD);
	$("#txtSecPerStop").val(sStopD);
	$("#ddlSecPermPageList").attr("readonly","true");
	$("#ddlSecPermPageList option").hide();
	$("#ddlSecPermPageList option[value='"+sPageId+"']").show();

	$("#ddlSecPermPageList").val(sPageId);
	$("#btnAddSecPerm").attr('data-mode',"sec_perm_update");
	$("#txtSecPerPSeq").val(sPageSeq);
	$("#ddlSecPermIsAdmin").val(sAdmin);
});



	

	filterPageList();
});//END Document Ready

</script>