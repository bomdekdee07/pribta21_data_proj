<?
include_once("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS('projid');
$sGroupid = getQS('groupid');
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

$attr_data_filter_group = "data-projid='$sProjid'  data-groupid='$sGroupid'";
?>

<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-main-data-filter-group' <? echo $attr_data_filter_group; ?>>

	<div class='fl-wrap-row ph30 bg-mdark1 ptxt-s10 ptxt-white' >
		<div class='fl-fix fl-mid pw50' ></div>
		<div class='fl-fix fl-mid pw200'>Filter Item Group</div>
		<div class='fl-fix fl-mid pw200' >Group Operator</div>
		<div class='fl-fix fl-mid pw80' ></div>
	</div>
	<div class='fl-wrap-row ph50 bg-msoft1 ptxt-s10' >
		<div class='fl-fix fl-mid pw50 pbtn pbtn-ok btn-add-row' >
				<i class='fa fa-save fa-2x' alt='Add'></i>
		</div>
		<div class='fl-fix fl-mid pw50 spinner' style='display:none;'>
				<i class='fa fa-spinner fa-spin  fa-2x'></i>
		</div>
		<div class='fl-fix fl-mid pw200' >
			<input type='text' style='width:90%;' class='save-data v-no-blank' data-id='item_group'  data-odata='' placeholder="Item Group" />
		</div>
		<div class='fl-fix fl-mid pw200' >
			<select data-id='item_group_type' class='save-data v-no-blank'><option value='' disabled>-select-</option><option value='AND'>AND</option><option value='OR'>OR</option></select>
		</div>
		<div class='fl-fix fl-mid pw80' ></div>
	</div>
	<div class='fl-wrap-row fl-fill'>
		<div class='fl-wrap-col fl-fill fl-auto div-data-filter-group-list'>


		</div>
	</div>
</div>

<script>
$(document).ready(function(){

	$(".div-main-data-filter-group .btn-add-row").unbind();
	$(".div-main-data-filter-group").on("click",".btn-add-row",function(){
		btnclick = $(this);
		let s_data_item = {};
    let flag_error = 0;
		$(".div-main-data-filter-group .save-data").each(function(ix,objx){
			let sVal = getWDataCompValue(objx);

			if($(objx).hasClass('v-no-blank') && sVal == ''){
				flag_error = 1;
				$(objx).notify($(objx).attr("data-id")+" is missing.", "info");

			}
			s_data_item[$(objx).attr("data-id")] = sVal;
		});

		if(flag_error) return;

		let aData = {
				u_mode:"update_row_datafilter_group",
				projid: $(".div-main-data-filter-group").attr("data-projid"),
				groupid:$(".div-main-data-filter-group").attr("data-groupid"),
				lst_data: s_data_item
		};

		startLoad(btnclick,btnclick.next(".spinner"));
		callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
					endLoad(btnclick,btnclick.next(".spinner"));
					if(rtnObj.txtrow != ''){

						$.notify("Update data successfully.", "success");
						$('.div-data-filter-group-list').prepend(rtnObj.txtrow);
						clearDataEdit_itemgroup();
					}
		});// call ajax
	});

	$(".div-main-data-filter-group .btn-remove-filter-group").unbind();
	$(".div-main-data-filter-group").on("click",".btn-remove-filter-group",function(){
		//console.log("click : "+$(this).closest('.div-filter-group').find('.item_group').text());
		btnclick = $(this);
		let row = $(this).closest('.div-filter-group')

		let aData = {
				u_mode:"remove_row_datafilter_group",
				projid: $(".div-main-data-filter-group").attr("data-projid"),
				groupid:$(".div-main-data-filter-group").attr("data-groupid"),
				itemgroup: $(this).closest('.div-filter-group').find('.item_group').text()
		};

		startLoad(btnclick,btnclick.next(".spinner"));
		callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
					endLoad(btnclick,btnclick.next(".spinner"));
					if(rtnObj.res == '1'){

						$.notify("Remove data successfully.", "info");
						row.remove();
					}
		});// call ajax
	});

	$(".div-main-data-filter-group .btn-view-filter-group").unbind();
	$(".div-main-data-filter-group").on("click",".btn-view-filter-group",function(){
		let sItemgroup = $(this).closest('.div-filter-group').find('.item_group').text();
		let sProjid = $(".div-main-data-filter-group").attr("data-projid");
		let sGroupid = $(".div-main-data-filter-group").attr("data-groupid");

		let sUrl = "proj_setting_datafilter_item.php?projid="+sProjid+"&groupid="+sGroupid+"&itemgroup="+sItemgroup;

			showDialog(sUrl,"Data Group Item : "+sProjid+"|"+sGroupid+"|"+sItemgroup,"600","800","",function(sResult){
					 if(sResult =='1'){

					 }
			},false,function(){
				//Load Done Function

			});
	});


  selectItemGroup();

  function selectItemGroup(){
		let aData = {
				u_mode:"select_list_datafilter_group",
				projid: $(".div-main-data-filter-group").attr("data-projid"),
				groupid:$(".div-main-data-filter-group").attr("data-groupid")
		};

		callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res == '1'){
						$('.div-data-filter-group-list').html('');
						$('.div-data-filter-group-list').html(rtnObj.txtrow);
				  }
					else{
						if(rtnObj.msg_info != '') $.notify(rtnObj.msg_info, "info");
						if(rtnObj.msg_error != '') $.notify(rtnObj.msg_error, "error");
					}
		});// call ajax

	}

	function clearDataEdit_itemgroup(){
		$(".div-main-data-filter-group .save-data").each(function(ix,objx){
				$(objx).val('');
		});
	}

});






</script>
