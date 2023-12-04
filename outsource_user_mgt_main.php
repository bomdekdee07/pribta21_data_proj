<?
include_once("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS('projid');
$module_id = 'PROJ_USER_MGT';
$option_code = 'OS'; // outsource

if(isset($_SESSION['MODULE'][$module_id][$option_code]['view'])){

}
else{
	echo "<center>Can not see this section.</center>";
	exit();
}

?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-os-mgt' data-projid='<? echo $sProjid; ?>'>
	<div class='fl-wrap-row ph30 bg-mdark3 ptxt-s10' >
		<div class='fl-fix fl-mid pw100 pbtn pbtn-ok btn-os-add' >
				<i class='fa fa-plus fa-lg'></i> ADD | เพิ่ม
		</div>
		<div class='fl-fix fl-mid mx-1 pw300 ptxt-s14 ptxt-white' >
				<i class='fa fa-user-astronaut fa-lg mx-3'></i> Outsource User Management
		</div>

		<div class='fl-fill fl-mid' >
				<input type='text' style='width:80%;' class='txt-os-search' placeholder="Search Outsource Name"/>
		</div>

		<div class='fl-fix fl-mid pw200 pbtn pbtn-blue btn-os-search' >
				<i class='fa fa-search fa-lg'></i> Search | ค้นหา
		</div>
		<div class='fl-fix fl-mid pw100 pbtn pbtn-blue spinner' style='display:none;'>
			 <i class='fa fa-spinner fa-spin  fa-2x'></i> Loading
	 </div>

	</div>
	<div class='fl-wrap-row ph30 bg-mdark1 ptxt-s10 ptxt-white' >
		<div class='fl-fix fl-mid pw50' >
				<i class='fa fa-user-edit fa-lg'></i>
		</div>
		<div class='fl-fix fl-mid pw50' >ID</div>
		<div class='fl-fix fl-mid pw300'>Name</div>
		<div class='fl-fix fl-mid pw80' >View</div>
		<div class='fl-fix fl-mid pw80' >Data</div>
		<div class='fl-fix fl-mid pw80' >Data Log</div>
		<div class='fl-fill fl-mid' >Note</div>
	</div>
	<div class='fl-wrap-row ph50 bg-msoft1 ptxt-s10 div-os-edit' style='display:none;' >
		<div class='fl-fix fl-mid pw50 pbtn pbtn-ok btn-os-save' >
				<i class='fa fa-save fa-2x' alt='SAVE'></i>
		</div>
		<div class='fl-fix fl-mid pw50 spinner' style='display:none;'>
				<i class='fa fa-spinner fa-spin  fa-2x'></i>
		</div>
		<div class='fl-fix fl-mid pw80' >
			<input type='text' style='width:90%;' id='txtsid'  data-odata='' placeholder="NEW ID" disabled />
		</div>
		<div class='fl-fix fl-mid pw300' >
				<input type='text' style='width:90%;' id='txtsname' class='input-os' data-odata=''  placeholder="Outsource Name"/>
		</div>

		<div class='fl-fix fl-mid pw80' >
				<input type='checkbox' style='width:80%;' data-odata='' class='chk-os chkview'/>
		</div>
		<div class='fl-fix fl-mid pw80' >
				<input type='checkbox' style='width:80%;' data-odata='' class='chk-os chkdata'/>
		</div>
		<div class='fl-fix fl-mid pw80' >
				<input type='checkbox' style='width:80%;' data-odata='' class='chk-os chklog'/>
		</div>

		<div class='fl-fill fl-mid' >
				<textarea id='txtsremark' class='input-os' style='width:90%;' placeholder="Outsource Note"></textarea>
		</div>

	</div>
	<div class='fl-fill fl-auto bg-msoft2 div-os-list'>


	</div>
</div>








<script>
$(document).ready(function(){

	$(".div-os-mgt .btn-os-add").unbind();
	$(".div-os-mgt").on("click",".btn-os-add",function(){
		clearOSEdit();
		$(".div-os-edit").show();
	});


		$(".div-os-mgt .btn-os-search").unbind();
		$(".div-os-mgt").on("click",".btn-os-search",function(){
			btnclick = $(this);
			let aData = {
					u_mode:"select_list",
					projid: $(".div-os-mgt").attr("data-projid"),
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


		$(".div-os-mgt .btn-os-save").unbind();
		$(".div-os-mgt").on("click",".btn-os-save",function(){
			btnclick = $(this);

      if($('#txtsname').val() == ''){
				$('#txtsname').notify('Please insert data', 'info');
				return;
			}

			let sInfo=""; let sInfo_old="";  let sAllow=""; let sAllow_old="";

			$(".div-os-mgt .input-os").each(function(ix,objx){
				sInfo += $(objx).val().trim()+ ":";
				sInfo_old += $(objx).attr('data-odata')+":";
			});
			$(".div-os-mgt .chk-os").each(function(ix,objx){
				let chkVal = ($(objx).prop("checked"))?'1':'0';
				sAllow += chkVal + ":";
				sAllow_old += $(objx).attr('data-odata')+ ":";
			});


			if(sInfo != sInfo_old ) sInfo = sInfo.substring(0, sInfo.length-1);
			if(sAllow != sAllow_old ) sAllow = sAllow.substring(0, sAllow.length-1);

			let aData = {
					u_mode:"update_row",
					projid: $(".div-os-mgt").attr("data-projid"),
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



$(".div-os-mgt .btn-os-edit").unbind();
$(".div-os-mgt").on("click",".btn-os-edit",function(){
		clearOSEdit();
    $('#txtsid').val($(this).parent().attr('data-sid'));
    $('#txtsname').val($(this).parent().find('.os-name').html());
		$('#txtsremark').val($(this).parent().find('.os-remark').html());

    let allow = $(this).parent().attr('data-allow');
		allow = allow.split(":");

    let chk_seq = 0;
		$(".div-os-mgt .chk-os").each(function(ix,objx){
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

  $(".div-os-mgt .btn-os-search").click();

});


function clearOSEdit(){
	$(".div-os-mgt .input-os").each(function(ix,objx){
			$(objx).val('');
	});
	$(".div-os-mgt .chk-os").each(function(ix,objx){
			$(objx).prop('checked', false);
	});
	$('#txtsid').val('');
	$('#txtsname').focus();
}

$(".div-os-mgt .btn-os-pwd").unbind();
$(".div-os-mgt").on("click",".btn-os-pwd",function(){
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
