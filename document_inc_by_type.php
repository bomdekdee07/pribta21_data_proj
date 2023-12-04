<?
include_once("in_session.php");
include_once("in_php_function.php");

$sClinicId = getQS("clinicid");
$sDocCode = getQS("doccode");

include("in_db_conn.php");

//Create clinic option ... 
$optClinic="";
$optDocCode = "";

if($sClinicId==""){
	$query = "SELECT clinic_id,clinic_name FROM p_clinic WHERE clinic_status='1' ORDER BY clinic_name;";
	$optSection = "";
	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
	    $stmt->bind_result($clinic_id,$clinic_name);
	    while ($stmt->fetch()) {
	  		$optSection .= "<option value='".$clinic_id."'>".$clinic_name."</option>";
	  		if($sClinicId=="") $sClinicId=$clinic_id;
	    }
	}
}else{
	$optClinic = "<option value='".$sClinicId."'>".$sClinicId."</option>";
}



if($sDocCode!=""){
	$optDocCode .= "<option value='".$sDocCode."'>".$sDocCode."</option>";
}else{
	$query = "SELECT doc_code,doc_name FROM i_doc_master_list WHERE doc_status = 1 AND clinic_id=?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sClinicId);
	if($stmt->execute()){
	    $stmt->bind_result($doc_code,$doc_name);
	    while ($stmt->fetch()) {
	  		$optDocCode .= "<option value='".$doc_code."'>".$doc_name."</option>";
	    }
	}
}



$mysqli->close();
?>
<div id='divDocBySecType' class='fl-wrap-col'>
	<div class='fl-wrap-row f-border fl-mid h-ms' style='line-height: 15px'>
		<div class='fl-fill'>
			ClinicId : <SELECT id='ddlClinic'>
				<? echo($optClinic); ?>				
			</SELECT>
		</div>
		<div class='fl-fill'>
			Document : <SELECT id='ddlDocCode'>
				<? echo($optDocCode); ?>	
			</SELECT>
		</div>
		<div class='fl-fix w-m'>
			<input id='btnDocModeSel' type='button' value='SELECT' />
			<i class='fa fa-spinner fa-spin' id='btnDocModeSel-loader' style='display:none' ></i>
		</div>
	</div>
	<div class='fl-wrap-row fs-s fl-mid h-ss row-color-2' style='line-height:15px'  >
		<div class='fl-fill' data-keyid='clinic_id'>
			Clinic
		</div>
		<div class='fl-fill' data-keyid='section_id'>
			Section
		</div>
		<div class='fl-fill' data-keyid='doc_code'>
			Code
		</div>
		<div class='fl-fix w-m ' data-keyid='allow_view'>
			View
		</div>
		<div class='fl-fix w-m' data-keyid='allow_edit'>
			Edit
		</div>
		<div class='fl-fix w-m' data-keyid='allow_create'>
			Create
		</div>
		<div class='fl-fix w-m' data-keyid='allow_delete'>
			Delete
		</div>
	</div>

	<div id='divDocByTypeList' class='fl-wrap-col fl-auto'>

	</div>
</div>


<script>
$(document).ready(function(){

	$("#divDocBySecType #btnDocModeSel").unbind("click");
	$("#divDocBySecType #btnDocModeSel").on("click",function(){
		sClinic = $("#ddlClinic").val();
		sDocCode = $("#ddlDocCode").val();

		var aData = {u_mode:"doc_list_by_type",clinic_id:sClinic,doc_code:sDocCode};
		startLoad($("#divDocBySecType #btnDocModeSel"),$("#divDocBySecType #btnDocModeSel-loader"));
		callAjax("document_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$("#divDocBySecType #divDocByTypeList").html(rtnObj.msg);
			}else{
				alert("Somethings wrong. Please try again"+rtnObj.msg);
			}
			endLoad($("#divDocBySecType #btnDocModeSel"),$("#divDocBySecType #btnDocModeSel-loader"));
		});
	});

	$("#divDocByTypeList .chkallow").unbind("change");
	$("#divDocByTypeList").on("change",".chkallow",function(){
		sAllowValue = ($(this).is(":checked")?"1":"0");
		objRow = $(this).closest(".data-row");
		sClinic = $(objRow).attr("data-clinicid");
		sSectionId = $(objRow).attr("data-secid");
		sCode = $(objRow).attr("data-code");
		sAllow = $(this).attr("data-keyid");
		objChk = $(this);


		var aData = {u_mode:"doc_auth_update",clinic_id:sClinic,section_id:sSectionId,doc_code:sCode,allow:sAllow,allowvalue:sAllowValue};

		$(objChk).addClass("fa fa-spin");
		callAjax("document_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$("#divDocBySecType #divDocByTypeList").html(rtnObj.msg);
			}else{
				alert("Somethings wrong. Please try again"+rtnObj.msg);
			}
			objRow = $("#divDocByTypeList .data-row[data-clinicid='"+sClinic+"'][data-secid='"+sSectionId+"'][data-code='"+sCode+"']").find(".chkallow[data-keyid='"+sAllow+"']").removeClass("fa fa-spin");
		});


	});
});

</script>