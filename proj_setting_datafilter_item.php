<?
include_once("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS('projid');
$sGroupid = getQS('groupid');
$sItemgroup = getQS('itemgroup');

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

$attr_data_filter_group = "data-projid='$sProjid'  data-groupid='$sGroupid' data-itemgroup='$sItemgroup' ";
?>

<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-main-data-filter-item' <? echo $attr_data_filter_group; ?>>

	<div class='fl-wrap-row ph30 bg-mdark1 ptxt-s10 ptxt-white' >
		<div class='fl-fix fl-mid pw50 pbtn pbtn-ok btn-add-filter-item' >
				<i class='fa fa-plus fa-lg'></i>+ ADD
		</div>
		<div class='fl-fix fl-mid pw50'></div>
		<div class='fl-fix fl-mid pw200'>Filter Data ID</div>
		<div class='fl-fix fl-mid pw200' >Data Equation</div>
		<div class='fl-fix fl-mid pw200' >Data Value</div>
		<div class='fl-fix fl-mid pw80' ></div>
	</div>
	<div class='fl-wrap-row ph50 bg-msoft1 ptxt-s10 div-edit-filteritem' style='display:none;'>
		<div class='fl-fix fl-mid pw50 pbtn pbtn-ok btn-filter-item-save' >
				<i class='fa fa-save fa-2x' alt='SAVE DATA'></i>
		</div>
		<div class='fl-fix fl-mid pw50 spinner' style='display:none;'>
				<i class='fa fa-spinner fa-spin  fa-2x'></i>
		</div>
		<div class='fl-fix fl-mid pw50' >
			<input type='text' style='width:90%;' class='save-data ' data-id='item_group_no'  data-odata='' placeholder="" disabled />
		</div>
		<div class='fl-fix fl-mid pw200' >
			<input type='text' style='width:90%;' class='save-data v-no-blank' data-id='data_id'  data-odata='' placeholder="Data ID" />
		</div>
		<div class='fl-fix fl-mid pw200' >
			<select data-id='data_equation' class='save-data v-no-blank' data-odata='' >
				<option value=''>-เลือก | select-</option>
				<option value='='> = </option>
				<option value='!='> != </option>
				<option value='>'> > </option>
				<option value='<'> < </option>
				<option value='>='> >= </option>
				<option value='<='> <= </option>
				<option value='LIKE'> LIKE </option>
			</select>
		</div>
		<div class='fl-fix fl-mid pw200' >
			<input type='text' style='width:90%;' class='save-data' data-id='data_value'  data-odata='' placeholder="Data Value" />
		</div>
		<div class='fl-fix fl-mid pw80' ></div>
	</div>
	<div class='fl-wrap-row fl-fill'>
		<div class='fl-wrap-col fl-fill fl-auto div-data-filter-item-list'>


		</div>
	</div>
</div>

<script>
$(document).ready(function(){

		$(".div-main-data-filter-item .btn-add-filter-item").unbind();
		$(".div-main-data-filter-item").on("click",".btn-add-filter-item",function(){
        clearDataEdit_filteritem();
				$('.save-data[data-id="item_group_no"]').val('ADD');
			  $('.div-edit-filteritem').show();
    });

		$(".div-main-data-filter-item .btn-remove-filter-item").unbind();
		$(".div-main-data-filter-item").on("click",".btn-remove-filter-item",function(){
      let btnclick = $(this);
			let sItemgroupno = $(this).closest(".div-item-filter").attr("data-item_group_no");
			let aData = {
					u_mode:"remove_row_datafilter_group_item",
					projid: $(".div-main-data-filter-item").attr("data-projid"),
					groupid:$(".div-main-data-filter-item").attr("data-groupid"),
					itemgroup:$(".div-main-data-filter-item").attr("data-itemgroup"),
					itemgroupno:sItemgroupno
			};

			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.res == '1'){
							let row_id = aData.itemgroupno;
							$.notify("Deleted.", "info");
							$('.div-item-filter[data-item_group_no="'+row_id+'"]').remove();
						}
			});// call ajax
		});




		$(".div-main-data-filter-item .btn-filter-item-save").unbind();
		$(".div-main-data-filter-item").on("click",".btn-filter-item-save",function(){
			btnclick = $(this);

			let flag_update = 0;
			let s_data_item = {};
			let flag_error = 0;
			$(".div-main-data-filter-item .save-data").each(function(ix,objx){
				let sVal = getWDataCompValue(objx);
        if($(objx).hasClass('v-no-blank') && sVal == ''){
					flag_error = 1;
					$(objx).notify($(objx).attr("data-id")+" is missing.", "info");
				}


				let sOData = getWODataComp(objx);
				s_data_item[$(objx).attr("data-id")] = $(objx).val().trim();

				if(sVal != sOData) {
					flag_update=1;
				}
			});


      if(flag_error) return;

			if(flag_update){
				let aData = {
						u_mode:"update_row_datafilter_item",
						projid: $(".div-main-data-filter-item").attr("data-projid"),
						groupid:$(".div-main-data-filter-item").attr("data-groupid"),
						itemgroup:$(".div-main-data-filter-item").attr("data-itemgroup"),
						lst_data: s_data_item
				};

				startLoad(btnclick,btnclick.next(".spinner"));
				callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
							endLoad(btnclick,btnclick.next(".spinner"));
							if(rtnObj.txtrow != ''){
                let row_id = rtnObj.item_group_no;
								$.notify("Update data successfully.", "success");
								$('.div-item-filter[data-item_group_no="'+row_id+'"]').remove();

								$('.div-data-filter-item-list').prepend(rtnObj.txtrow);
								$(".div-edit-filteritem").hide();

								$('.div-main-data-filter-item[data-id="'+row_id+'"]').addClass('pbg-grey');
							}
				});// call ajax
			}
		});

	$(".div-main-data-filter-item .btn-edit-filter-item").unbind();
	$(".div-main-data-filter-item").on("click",".btn-edit-filter-item",function(){
		  clearDataEdit_filteritem();
			$('.save-data[data-id="item_group_no"]').val($(this).parent().find('.s-item_group_no').html());
			$('.save-data[data-id="data_id"]').val($(this).parent().find('.s-data_id').html());
			$('.save-data[data-id="data_equation"]').val($(this).parent().find('.s-data_equation').html());
			$('.save-data[data-id="data_value"]').val($(this).parent().find('.s-data_value').html());

			$('.div-edit-filteritem').show();
	});

	$(".div-main-data-filter-item .btn-view-filter-group").unbind();
	$(".div-main-data-filter-item").on("click",".btn-view-filter-group",function(){

	});
  selectItemFilter();

  function selectItemFilter(){

		let aData = {
				u_mode:"select_list_datafilter_item",
				projid: $(".div-main-data-filter-item").attr("data-projid"),
				groupid:$(".div-main-data-filter-item").attr("data-groupid"),
				itemgroup:$(".div-main-data-filter-item").attr("data-itemgroup")
		};

		callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res == '1'){
						$('.div-data-filter-item-list').html('');
						$('.div-data-filter-item-list').html(rtnObj.txtrow);
				  }
					else{
						if(rtnObj.msg_info != '') $.notify(rtnObj.msg_info, "info");
						if(rtnObj.msg_error != '') $.notify(rtnObj.msg_error, "error");
					}
		});// call ajax

	}

  function clearDataEdit_filteritem(){
		$('.div-main-data-filter-item').find('.save-data').val('');
		$('.div-main-data-filter-item').find('.save-data').attr('data-odata', '');
	}

});






</script>
