<?
include_once("in_php_function.php");
$sFormId=getQS("formid");
$sUrl = "../weclinic/data_mgt/mnu_form_view.php?form_id=".$sFormId;

?>




</iframe>
<div class='fl-wrap-col'>
	<div id='divPreview' class='' style=''>
		<iframe id='frmPreview' src='<? echo($sUrl); ?>' style='height: 530px;width:100%' class='fill-box'>
	</div>
	<div id='divPreview-loader' class=' fl-mid' style='display:none;'>
		<i class='fa fa-spinner fa-4x' ></i>
	</div>
</div>

<script>
	$(document).ready(function(){
		/*
		$('#frmPreview').unbind('load');
		$('#frmPreview').on('load', function() {
		    $("#divPreview-loader").hide();
			$("#divPreview").show();
			$("#frmPreview").css("width","100%");
			$("#frmPreview").css("height","530px");

		});
		*/
	});

</script>