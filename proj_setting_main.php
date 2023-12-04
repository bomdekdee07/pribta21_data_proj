<?
include_once("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS('projid');
$module_id = 'PROJ_SETTING';
$option_code = '1'; // admin
/*
if(isset($_SESSION['MODULE'][$module_id][$option_code]['view'])){

}
else{
	echo "<center>Can not see this section.</center>";
	exit();
}
*/
if(!isset($proj_auth['allow_view'])){
  include_once("project_inc_uid_permission.php");
}

if(!isset($proj_auth['allow_admin'])){
	echo "<center>Can not see this section.</center>";
	exit();
}



?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">

<div class='fl-wrap-col div-proj-setting' data-projid='<? echo $sProjid; ?>'>
	  <div class='fl-wrap-row ph30 bg-msoft1 ptxt-s8 ptxt-b' >
			<div class='fl-wrap-col pw100 mr-1 pbtn bg-sdark2 ptxt-white btn-proj-setting-mnu'
					data-page='proj_setting_group'  data-title='Project Group'
					title='Project Group'>
					<div class='fl-fix ph15 fl-mid'><i class='fas fa-users fa-lg'></i> </div>
					<div class='fl-fix ph15 fl-mid '>Project Group</div>
			</div>
			<div class='fl-wrap-col pw100 mr-1 pbtn bg-sdark2 ptxt-white btn-proj-setting-mnu'
					data-page='proj_setting_visit'  data-title='Project Visit'
					title='Protocol Visit'>
					<div class='fl-fix ph15 fl-mid'><i class='fas fa-calendar-alt fa-lg'></i> </div>
					<div class='fl-fix ph15 fl-mid '>Main Visit</div>
			</div>
			<div class='fl-wrap-col pw100 mr-1 pbtn bg-sdark2 ptxt-white btn-proj-setting-mnu'
	        data-page='proj_setting_protocol'  data-title='Project Protocol'
	        title='Protocol'>
	        <div class='fl-fix ph15 fl-mid'><i class='fas fa-project-diagram fa-lg'></i> </div>
	        <div class='fl-fix ph15 fl-mid '>Protocol</div>
	    </div>
			<div class='fl-wrap-col pw100 mr-1 pbtn bg-sdark2 ptxt-white btn-proj-setting-mnu'
					data-page='proj_setting_datafilter'  data-title='Data Filter'
					title='Data Filter'>
					<div class='fl-fix ph15 fl-mid'><i class='fas fa-filter fa-lg'></i> </div>
					<div class='fl-fix ph15 fl-mid '>Data Filter</div>
			</div>






		</div>
		<div class='fl-wrap-row fl-fill bg-msoft3 div-setting-detail' >

		</div>
		<div class='fl-wrap-row fl-fill fl-mid bg-msoft3 spinner' style='display:none;' >
       <i class='fa fa-spinner fa-spin fa-2x' ></i>
		</div>
</div>








<script>
$(document).ready(function(){


	  $(".div-proj-setting .btn-proj-setting-mnu").off("click");
	  $(".div-proj-setting .btn-proj-setting-mnu").on("click",function(){
	    let projid = $(".div-proj-setting").attr("data-projid");
	    let page = $(this).attr("data-page");
	    let sUrl = page+".php?projid="+projid;
			$(".div-setting-detail").next(".spinner").show();
			$(".div-setting-detail").load(sUrl, function(responseTxt, statusTxt, xhr){
				if(statusTxt == "success")
					//alert("External content loaded successfully!");
				if(statusTxt == "error")
					alert("Error: " + xhr.status + ": " + xhr.statusText);

					$(".div-setting-detail").next(".spinner").hide();
			});


	  });


		$(".div-proj-setting .btn-os-search").unbind();
		$(".div-proj-setting").on("click",".btn-os-search",function(){
			btnclick = $(this);
			let aData = {
					u_mode:"select_list",
					projid: $(".div-proj-setting").attr("data-projid"),
					txtsearch:$(".txt-os-search").val().trim()
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("outsource_user_mgt_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.row_amt == '0'){
							$.notify("No data found.", "info");
						//	$('.div-os-list').html('<center> - No data found. - </center>');
						}
						else{
							$('.div-os-list').html('');
							$('.div-os-list').html(rtnObj.txtrow);
						}
			});// call ajax
		});


		$(".div-proj-setting .btn-os-save").unbind();
		$(".div-proj-setting").on("click",".btn-os-save",function(){
			btnclick = $(this);

      if($('#txtsname').val() == ''){
				$('#txtsname').notify('Please insert data', 'info');
				return;
			}

			let sInfo=""; let sInfo_old="";  let sAllow=""; let sAllow_old="";

			$(".div-proj-setting .input-os").each(function(ix,objx){
				sInfo += $(objx).val().trim()+ ":";
				sInfo_old += $(objx).attr('data-odata')+":";
			});
			$(".div-proj-setting .chk-os").each(function(ix,objx){
				let chkVal = ($(objx).prop("checked"))?'1':'0';
				sAllow += chkVal + ":";
				sAllow_old += $(objx).attr('data-odata')+ ":";
			});


			if(sInfo != sInfo_old ) sInfo = sInfo.substring(0, sInfo.length-1);
			if(sAllow != sAllow_old ) sAllow = sAllow.substring(0, sAllow.length-1);

			let aData = {
					u_mode:"update_row",
					projid: $(".div-proj-setting").attr("data-projid"),
					sid: $('#txtsid').val(),
          s_info: sInfo,
					s_allow: sAllow
			};

			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("outsource_user_mgt_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.txtrow != ''){
							if(aData.sid == ''){
								$.notify("Add outsource successfully.", "success");
							}
							else{
								$.notify("Update outsource successfully.", "success");
                $('.div-os-row[data-sid="'+aData.sid+'"]').remove();
							}
							$('.div-os-list').prepend(rtnObj.txtrow);
							$(".div-os-edit").hide();

							$('.div-os-row[data-sid="'+aData.sid+'"]').addClass('pbg-grey');

							//$('.div-os-list').html('<center> - No data found. - </center>');
						}
			});// call ajax
		});



$(".div-proj-setting .btn-os-edit").unbind();
$(".div-proj-setting").on("click",".btn-os-edit",function(){
		clearOSEdit();
    $('#txtsid').val($(this).parent().attr('data-sid'));
    $('#txtsname').val($(this).parent().find('.os-name').html());
		$('#txtsremark').val($(this).parent().find('.os-remark').html());

    let allow = $(this).parent().attr('data-allow');
		allow = allow.split(":");

    let chk_seq = 0;
		$(".div-proj-setting .chk-os").each(function(ix,objx){
		  	$(objx).attr('odata', allow[chk_seq]);
			  if(allow[chk_seq] == 1) $(objx).prop('checked', true);

				chk_seq ++;
		});
/*
		$('.chkview').attr('odata', allow[0]);
		$('.chkdata').attr('odata', allow[1]);
		$('.chklog').attr('odata', allow[2]);

		if(allow[0]) $('.chkview').prop('checked', true);
		if(allow[1]) $('.chkdata').prop('checked', true);
		if(allow[2]) $('.chklog').prop('checked', true);
*/

		$('.div-os-edit').show();
		$('.div-os-edit').focus();


});

  $(".div-proj-setting .btn-os-search").click();

});


function clearOSEdit(){
	$(".div-proj-setting .input-os").each(function(ix,objx){
			$(objx).val('');
	});
	$(".div-proj-setting .chk-os").each(function(ix,objx){
			$(objx).prop('checked', false);
	});
	$('#txtsid').val('');
	$('#txtsname').focus();
}

$(".div-proj-setting .btn-os-pwd").unbind();
$(".div-proj-setting").on("click",".btn-os-pwd",function(){
    let sid = $(this).parent().attr('data-sid');
		let sUrl = "user_dlg_reset_pass.php?sid="+sid;
		//console.log("permission: "+sUrl);
		showDialog(sUrl,"Change Password ["+sid+"]","400","300","",function(sResult){
				 if(sResult =='1'){

				 }
		},false,function(){
			//Load Done Function
			$.notify("Change password here", "info");
		});
});

</script>
