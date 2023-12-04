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
/*
if(!isset($proj_auth['allow_view'])){
  include_once("project_inc_uid_permission.php");
}

if(!isset($proj_auth['allow_admin'])){
	echo "<center>Can not see this section.</center>";
	exit();
}
*/
?>

<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-setting-data' data-projid='<? echo $sProjid; ?>'>
	<div class='fl-wrap-row ph30 bg-mdark3 ptxt-s10' >
		<div class='fl-fix fl-mid pw100 pbtn pbtn-ok btn-setting-add' >
				<i class='fa fa-plus fa-lg'></i> ADD | เพิ่ม
		</div>
		<div class='fl-fix fl-mid mx-1 pw300 ptxt-s14 ptxt-white' >
				<i class='fa fa-calendar-alt fa-lg mx-3'></i> Project Visit
		</div>

		<div class='fl-fill fl-mid' >
				<input type='text' style='width:80%;' class='txt-setting-search' placeholder="Search Visit"/>
		</div>

		<div class='fl-fix fl-mid pw200 pbtn pbtn-blue btn-setting-search' >
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
		<div class='fl-fix fl-mid pw100' >Group ID</div>
		<div class='fl-fix fl-mid pw100'>Visit ID</div>
		<div class='fl-fix fl-mid pw200' >Visit Name</div>
		<div class='fl-fill fl-mid'>Description</div>
		<div class='fl-fix fl-mid pw80' >Visit Day</div>
		<div class='fl-fix fl-mid pw80' >Day Before</div>
		<div class='fl-fix fl-mid pw80' >Day After</div>

		<div class='fl-fix fl-mid pw80' >Seq.</div>
		<div class='fl-fix fl-mid pw80' >Status</div>
	</div>
	<div class='fl-wrap-row ph50 bg-msoft1 ptxt-s10 div-setting-edit' style='display:none;' >
		<div class='fl-fix fl-mid pw50 pbtn pbtn-ok btn-setting-save' >
				<i class='fa fa-save fa-2x' alt='SAVE'></i>
		</div>
		<div class='fl-fix fl-mid pw50 spinner' style='display:none;'>
				<i class='fa fa-spinner fa-spin  fa-2x'></i>
		</div>
		<div class='fl-fix fl-mid pw100' >
			<select style='width:90%;' class='save-data' data-id='group_id'  data-odata=''>
          <option value=''>All</option>
					<?
              include_once("project_opt_group_list.php");
					?>
			</select>
		</div>
		<div class='fl-fix fl-mid pw200' >
			<input type='text' style='width:90%;' class='save-data edit-lock' data-id='visit_id'  data-odata='' placeholder="Visit ID" />
		</div>
		<div class='fl-fix fl-mid pw200' >
				<input type='text' style='width:90%;' class='save-data ' data-id='visit_name' data-odata=''  placeholder="Visit Name"/>
		</div>
		<div class='fl-fix fl-fill fl-mid' >
				<input type='text' style='width:90%;' class='save-data' data-id='visit_desc' data-odata=''  placeholder="Visit Desc"/>
		</div>
		<div class='fl-fix fl-mid pw80' >
				<input type='number' style='width:90%;' class='save-data' data-id='visit_day' data-odata='' placeholder="Day Amt." />
		</div>
		<div class='fl-fix fl-mid pw80' >
				<input type='number' style='width:90%;' class='save-data' data-id='visit_day_before' data-odata=''  placeholder="Day Before"/>
		</div>.
		<div class='fl-fix fl-mid pw80' >
				<input type='number' style='width:90%;' class='save-data' data-id='visit_day_after' data-odata=''  placeholder="Day After"/>
		</div>
		<div class='fl-fix fl-mid pw80' >
				<input type='number' style='width:90%;' class='save-data' data-id='visit_order' data-odata=''  placeholder="Visit Seq."/>
		</div>
		<div class='fl-fix fl-mid pw80' >
			 <select class='save-data' data-id='visit_status' data-odata=''>
				 <option value='1'>Active</option>
         <option value='0'>Inactive</option>
			 </select>
		</div>



	</div>
	<div class='fl-fill fl-auto bg-msoft2 div-setting-list'>


	</div>
</div>


<script>
$(document).ready(function(){

	$(".div-setting-data .btn-setting-add").unbind();
	$(".div-setting-data").on("click",".btn-setting-add",function(){
		clearDataEdit();
		$('.edit-lock').prop('disabled', false);
		$(".div-setting-edit").show();
	});


		$(".div-setting-data .btn-setting-search").unbind();
		$(".div-setting-data").on("click",".btn-setting-search",function(){
			btnclick = $(this);
			let aData = {
					u_mode:"select_list_visit",
					projid: $(".div-setting-data").attr("data-projid"),
					txtsearch:$(".txt-setting-search").val().trim()
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.row_amt == '0'){
							$.notify("No data found.", "info");
						//	$('.div-setting-list').html('<center> - No data found. - </center>');
						}
						else{
							$('.div-setting-list').html('');
							$('.div-setting-list').html(rtnObj.txtrow);
						}
			});// call ajax
		});


		$(".div-setting-data .btn-setting-save").unbind();
		$(".div-setting-data").on("click",".btn-setting-save",function(){
			btnclick = $(this);
      let row_id = $('.save-data[data-id="visit_id"]').val();

      let flag_update = 0;
			let s_data_item = {};
			$(".div-setting-data .save-data").each(function(ix,objx){
				let sVal = getWDataCompValue(objx);
				let sOData = getWODataComp(objx);

				s_data_item[$(objx).attr("data-id")] = $(objx).val().trim();

				if(sVal != sOData) {
					flag_update=1;
				}
			});

			if(flag_update){
				let aData = {
						u_mode:"update_row_visit",
						projid: $(".div-setting-data").attr("data-projid"),
						id:row_id,
						lst_data: s_data_item
				};

				startLoad(btnclick,btnclick.next(".spinner"));
				callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
							endLoad(btnclick,btnclick.next(".spinner"));
							if(rtnObj.txtrow != ''){

								$.notify("Update data successfully.", "success");
								$('.div-setting-row[data-id="'+row_id+'"]').remove();

								$('.div-setting-list').prepend(rtnObj.txtrow);
								$(".div-setting-edit").hide();

								$('.div-setting-row[data-id="'+row_id+'"]').addClass('pbg-grey');

								//$('.div-setting-list').html('<center> - No data found. - </center>');
							}
				});// call ajax
			}


		});



$(".div-setting-data .btn-setting-edit").unbind();
$(".div-setting-data").on("click",".btn-setting-edit",function(){
		clearDataEdit();
		$('.save-data[data-id="group_id"]').val($(this).parent().find('.s-group_id').html());
		$('.save-data[data-id="visit_id"]').val($(this).parent().find('.s-visit_id').html());
		$('.save-data[data-id="visit_name"]').val($(this).parent().find('.s-visit_name').html());
		$('.save-data[data-id="visit_desc"]').val($(this).parent().find('.s-visit_desc').html());
		$('.save-data[data-id="visit_day"]').val($(this).parent().find('.s-visit_day').html());
		$('.save-data[data-id="visit_day_before"]').val($(this).parent().find('.s-visit_day_before').html());
		$('.save-data[data-id="visit_day_after"]').val($(this).parent().find('.s-visit_day_after').html());
		$('.save-data[data-id="visit_order"]').val($(this).parent().find('.s-visit_order').html());
		$('.save-data[data-id="visit_status"]').val($(this).parent().find('.s-visit_status').html());

    $('.edit-lock').prop('disabled', true);


		$('.div-setting-edit').show();
		$('.div-setting-edit').focus();

});


$(".div-setting-data .btn-view-form").off("click");
$(".div-setting-data").on("click",".btn-view-form",function(){
	  let projid = $(".div-setting-data").attr("data-projid");
	  let protocolid = $(this).closest(".div-setting-row").attr("data-id");
    //  console.log("click form: "+protocol_id);

			let sUrl = "proj_setting_protocol_form_visit.php?projid="+projid+"&protocolid="+protocolid;
			showDialog(sUrl,"Forms in : "+protocolid,"90%","90%","",function(sResult){
	         if(sResult =='1'){

	         }
	    },false,function(){
	      //Load Done Function

	    });

});


function clearDataEdit(){
	$(".div-setting-data .save-data").each(function(ix,objx){
			$(objx).val('');
	});
	$(".div-setting-data .v-checkbox").each(function(ix,objx){
			$(objx).prop('checked', false);
	});
}


  $(".div-setting-data .btn-setting-search").click();

});






</script>
