<?
include_once("in_session.php");
include_once("in_php_function.php");



?>
<div id='divUSetting' class='fl-wrap-col' style='overflow: hidden'>
	<div class='fl-wrap-row h-120'>
		<div>
			<div class='fl-wrap-col h-100 w-300 roundcorner row-hover' style='margin:10px'>
				<div class='fl-wrap-row h-20 bg-head-1'>
					<div id='btnViewProfile' title='View All Signed Document' class='fl-fix w-30 fl-mid fabtn' style='display:none'><i class='fa fa-search'></i></div>
					<div class='fl-fill fl-mid'>User Profile</div>
					<div id='btnEditProfile' title='Edit user profile' class='fl-fix w-30 fl-mid fabtn'><i class='fa fa-edit'></i></div>
				</div>
				<div id='divProfilePreview' class='fl-wrap-col h-80  fs-small' style='background-color: white'>
					<? include("staff_inc_user_profile_preview.php"); ?>
				</div>
			</div>
		</div>

		<div>
			<div class='fl-wrap-col h-100 w-200 roundcorner row-hover' style='margin:10px'>
				<div class='fl-wrap-row h-20 bg-head-1'>
					<div id='btnViewSignature' title='View All Signed Document' class='fl-fix w-30 fl-mid fabtn' style='display:none'><i class='fa fa-search'></i></div>
					<div class='fl-fill fl-mid'>e-signature</div>
					<div id='btnEditSignature' title='Edit e-Signature' class='fl-fix w-30 fl-mid fabtn'><i class='fa fa-edit'></i></div>
				</div>
				<div id='divSigPreview' class='fl-fix h-80 fl-mid' style='background-color: white'>
					<? include("staff_inc_signature_preview.php"); ?>
				</div>
			</div>
		</div>


	</div>

	<div class='fl-wrap-row h-30 bg-head-1'>
		<div id='btnRefMod' class='fabtn fl-fill fl-mid'><i class=' fas fa-sync-alt'>Refresh Login Module</i></div>
		<div class='fabtn fl-mid fl-fix w-100 bg-head-4'>VIEW</div>
	</div>
	<div class='fl-wrap-row row-color-2 row-header h-30'>
		<div class='fl-fill'>Name</div>
		<div class='fl-fill'>Code</div>
		<div class='fl-fix w-80 fl-mid'>View</div>
		<div class='fl-fix w-80 fl-mid'>Insert</div>
		<div class='fl-fix w-80 fl-mid'>Update</div>
		<div class='fl-fix w-80 fl-mid'>Delete</div>
		<div class='fl-fix w-80 fl-mid'>Admin</div>
		<div class='fl-fill'></div>
	</div>
	<div id='divShowResult' class='fl-wrap-col  fl-auto row-color-2 '></div>
</div>

<script>
	$(function(){
		$("#divUSetting #btnEditProfile").off("click");
		$("#divUSetting #btnEditProfile").on("click",function(){
			sUrl="user_dlg_profile_edit.php";
			showDialog(sUrl,"Profile Editor","90%","800","",
			function(sResult){
				//CLose function
				if(sResult=="REFRESH"){
					$("#divUSetting #divProfilePreview").load("staff_inc_user_profile_preview.php",function(){
					});
				}
			},false,function(){});
		});

		$("#divUSetting #btnEditSignature").off("click");
		$("#divUSetting #btnEditSignature").on("click",function(){
			sUrl="user_dlg_signature_edit.php";
			showDialog(sUrl,"Signature Editor","300","450","",
			function(sResult){
				//CLose function
				if(sResult=="REFRESH"){
					$("#divUSetting #divSigPreview").load("staff_inc_signature_preview.php",function(){
					});
				}
			},false,function(){});
		});

		$("#divUSetting #btnRefMod").off("click");
		$("#divUSetting #btnRefMod").on("click",function(){
			sUrl="login_a.php";
			$("#divUSetting #divShowResult").html("<i class='fa fa-spinner fa-spin'></i> Loading....");
			aData={u_mode:"refresh_module"};
			callAjax(sUrl,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$("#divUSetting #divShowResult").html(jRes.msg);
				}else{
					
				}
        	});
		});
	});
</script>