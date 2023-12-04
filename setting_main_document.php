<?
	include("in_session.php");
	include_once("in_php_function.php");
	$sClinicId = getQS("clinicid");


?>

<div class='fl-wrap-col document-master-list'>
	<div class='fl-wrap-row fs-s fl-mid h-xs row-color-2'>
		<div class='fl-fix w-m'>
		</div>
		<div class='fl-fix w-l'>
			Clinic
		</div>
		<div class='fl-fix w-m '>
			Code
		</div>
		<div class='fl-fill '>
			Name
		</div>
		<div class='fl-fix w-m'>
			File
		</div>
		<div class='fl-fix w-sm'>
			Status
		</div>
		<div class='fl-fix w-xl'>
			Control
		</div>
	</div>
	<div class='fl-wrap-row input-header fs-s fl-mid h-ss  row-color-2'>
		<div class='fl-fix w-m'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix w-l'>

			<input class='fill-box mar-topdown saveinput' data-odata='<? echo($sClinicId); ?>' data-keyid='clinic_id' value='<? echo($sClinicId); ?>' readonly='readonly' data-pk='1' />
		</div>
		<div class='fl-fix w-m '>
			<input class='fill-box mar-topdown saveinput' data-odata='' data-keyid='doc_code' maxlength="200" data-pk='1' />
		</div>
		<div class='fl-fill '>
			<input class='fill-box mar-topdown saveinput' data-odata='' data-keyid='doc_name' maxlength="200" />
		</div>
		<div class='fl-fix w-m'>
			<input class='fill-box mar-topdown saveinput' data-odata='' data-keyid='doc_template_file' maxlength="255" />
		</div>
		<div class='fl-fix w-sm'>
			<SELECT class='fill-box mar-topdown saveinput' data-odata='' data-keyid='doc_status' >
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>

			</SELECT>
		</div>
		<div class='fl-fix w-xl'>
			<span style='color:green'><i id='btnAddDoc' class="fabtn fas fa-plus-square fa-2x" data-mode='doc_add'></i><i id='btnAddDoc-loader' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>


	</div>
	<div class='fl-wrap-col docrow-body fl-auto' >
		<div class='fl-fill docrow-list'>
		<? $_GET["u_mode"]="doc_list"; $_GET["clinic_id"]=$sClinicId; $_GET["echo"] = 1; include("document_a.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){

	$(".document-master-list #btnAddDoc").unbind("click");
	$(".document-master-list #btnAddDoc").on("click",function(){
		objCurDiv = $(this).closest(".document-master-list");
		sCode = $(objCurDiv).find(".saveinput[data-keyid='doc_code']").val();
		sLink = $(objCurDiv).find(".saveinput[data-keyid='doc_template_file']").val();

		if(sCode!=""){
			$(objCurDiv).find(".saveinput[data-keyid='doc_code']").val(sCode.toUpperCase());
		}
		if(sLink!=""){
			$(objCurDiv).find(".saveinput[data-keyid='doc_template_file']").val(sLink.toLowerCase());
		}

		aData = getDataRow($(".document-master-list .input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");


		startLoad($(".document-master-list #btnAddDoc,.document-master-list #btnCancelData"),$(".document-master-list #btnAddDoc-loader"));

		callAjax("document_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				sMode = $(".document-master-list #btnAddDoc").attr("data-mode");

				if(sMode=="doc_update"){
					objRow= $(".docrow-list .data-row[data-clinicid='"+aData.clinic_id+"'][data-code='"+aData.doc_code+"']");
					$(".input-header").find(".saveinput").each(function(ix,objx){
						sKey = $(objx).attr("data-keyid");
						$(objRow).find(".showinput[data-keyid='"+sKey+"']").html($(objx).val());
					});
				}else if(sMode=="doc_add"){
					$(".docrow-body .docrow-list").prepend(rtnObj.msg);
				}
				$("#btnCancelData").trigger("click");
			}
			//
			endLoad($(".document-master-list #btnAddDoc,.document-master-list #btnCancelData"),$(".document-master-list #btnAddDoc-loader"));
		});
	});

	$(".document-master-list .btnedit").unbind("click");
	$(".document-master-list .btnedit").on("click",function(){
		sClinicId = $(this).attr("data-clinicid");
		sDocCode = $(this).attr("data-code");
		aData = {u_mode:"doc_master_find",clinic_id:sClinicId,doc_code:sDocCode};
	
		$(".input-header .saveinput[data-keyid='doc_code']").attr("readonly",true);
		startLoad($(".document-master-list #btnAddDoc,.document-master-list #btnCancelData"),$(".document-master-list #btnAddDoc-loader"));
		callAjax("document_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$(".input-header .saveinput[data-keyid='clinic_id']").val(rtnObj.clinic_id);
				$(".input-header .saveinput[data-keyid='doc_code']").val(rtnObj.doc_code);
				$(".input-header .saveinput[data-keyid='doc_name']").val(rtnObj.doc_name);
				$(".input-header .saveinput[data-keyid='doc_template_file']").val(rtnObj.doc_template_file);
				$(".input-header .saveinput[data-keyid='doc_status']").val(rtnObj.doc_status);
				$(".document-master-list #btnAddDoc").attr('data-mode',"doc_update");
			}else{

			}
			endLoad($(".document-master-list #btnAddDoc,.document-master-list #btnCancelData"),$(".document-master-list #btnAddDoc-loader"));
		});

	});

	$("#btnCancelData").unbind("click");
	$("#btnCancelData").on("click",function(){
		$(".input-header .saveinput[data-keyid!='clinic_id']").val("");
		$(".document-master-list #btnAddDoc").attr("data-mode","doc_add");
		$(".input-header .saveinput[data-pk='1'][data-keyid!='clinic_id']").removeAttr('readonly');
		$(".document-master-list .saveinput[data-keyid='doc_code']").removeAttr('readonly');
	});

});
</script>