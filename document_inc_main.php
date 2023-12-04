<?
include_once("in_session.php");
include_once("in_php_function.php");

$sClinicId = getQS("clinicid");
?>
<div id='divDocMain' class='fl-wrap-col' data-clinicid='<? echo($sClinicId); ?>'>
	<div class='fl-wrap-row h-ss'>
		<div id='btnDocMaster' class='docbtn fabtn fl-fill f-border btn-selected' data-name='setting_main_document'>
			Master File
		</div>
		<div id='btnDocBySec' class='docbtn fabtn fl-fill f-border' data-name='document_inc_by_section'>
			By Section
		</div>
		<div id='btnDocByType' class='docbtn fabtn fl-fill f-border' data-name='document_inc_by_type'>
			By Type
		</div>
	</div>
	<div id='divDocAuthList' class='fl-wrap-col'>
		<? include("setting_main_document.php"); ?>
	</div>
	<div id='divDocAuthList-loader' class='fl-wrap-col fl-mid' style='display:none;'>
		<i class="fas fa-spinner fa-spin fa-4x"></i>
	</div>
</div>

<script>
	$(document).ready(function(){
		$("#divDocMain .docbtn").unbind("click");
		$("#divDocMain .docbtn").on("click",function(){
			$("#divDocMain .btn-selected").removeClass("btn-selected");
			sName = $(this).attr("data-name");
			sClinicId = $(this).closest("#divDocMain").attr("data-clinicid");
			if(sName == "" || sClinicId == "") return;
			sUrl = sName + ".php?clinicid="+sClinicId;
			startLoad($("#divDocMain #divDocAuthList"),$("#divDocMain #divDocAuthList-loader"));
			$(this).addClass("btn-selected");
			$("#divDocAuthList").load(sUrl,function(){
				
				endLoad($("#divDocMain #divDocAuthList"),$("#divDocMain #divDocAuthList-loader"));
			});
		});
	});

</script>
