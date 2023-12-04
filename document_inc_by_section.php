<?
include_once("in_session.php");
include_once("in_php_function.php");

$sClinicId = getQS("clinicid");
$sSectionId = getQS("sectionid");

include("in_db_conn.php");

//Create clinic option ... 
$optClinic="";
$optSection = "";

if($sClinicId==""){
	$query = "SELECT clinic_id,clinic_name FROM p_clinic WHERE clinic_status='1' ORDER BY clinic_name;";
	$optSection = "";
	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
	    $stmt->bind_result($clinic_id,$clinic_name);
	    while ($stmt->fetch()) {
	  		$optSection .= "<option value='".$clinic_id."'>".$clinic_name."</option>";
	    }
	}
}else{
	$optClinic = "<option value='".$sClinicId."'>".$sClinicId."</option>";
}



if($sSectionId!=""){
	$optSection .= "<option value='".$sSectionId."'>".$sSectionId."</option>";
}else{
	$query = "SELECT section_id,section_name FROM p_staff_section WHERE section_enable = 1 ORDER BY section_id;";

	$stmt = $mysqli->prepare($query);

	if($stmt->execute()){
	    $stmt->bind_result($section_id,$section_name);
	    while ($stmt->fetch()) {
	  		$optSection .= "<option value='".$section_id."'>".$section_name."</option>";
	    }
	}
}



$mysqli->close();
?>
<div id='divDocBySecMain' class='fl-wrap-col'>
	<div class='fl-wrap-row f-border fl-mid h-ms' style='line-height: 15px'>
		<div class='fl-fill'>
			ClinicId : <SELECT id='ddlClinic'>
				<? echo($optClinic); ?>				
			</SELECT>
		</div>
		<div class='fl-fill'>
			Section : <SELECT id='ddlSection'>
				<? echo($optSection); ?>	
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

	<div id='divDocBySecList' class='fl-wrap-col'>

	</div>
</div>


<script>
$(document).ready(function(){

	$("#divDocBySecMain #btnDocModeSel").unbind("click");
	$("#divDocBySecMain #btnDocModeSel").on("click",function(){
		sClinic = $("#ddlClinic").val();
		sSectionId = $("#ddlSection").val();

		var aData = {u_mode:"doc_list_by_sec",clinic_id:sClinic,section_id:sSectionId};
		startLoad($("#divDocBySecMain #btnDocModeSel"),$("#divDocBySecMain #btnDocModeSel-loader"));
		callAjax("document_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$("#divDocBySecMain #divDocBySecList").html(rtnObj.msg);
			}else{
				alert("Somethings wrong. Please try again"+rtnObj.msg);
			}
			endLoad($("#divDocBySecMain #btnDocModeSel"),$("#divDocBySecMain #btnDocModeSel-loader"));
		});
	});

	$("#divDocBySecList .chkallow").unbind("change");
	$("#divDocBySecList").on("change",".chkallow",function(){
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
				$("#divDocBySecMain #divDocBySecList").html(rtnObj.msg);
			}else{
				alert("Somethings wrong. Please try again"+rtnObj.msg);
			}
			objRow = $("#divDocBySecList .data-row[data-clinicid='"+sClinic+"'][data-secid='"+sSectionId+"'][data-code='"+sCode+"']").find(".chkallow[data-keyid='"+sAllow+"']").removeClass("fa fa-spin");
		});


	});
});

</script>