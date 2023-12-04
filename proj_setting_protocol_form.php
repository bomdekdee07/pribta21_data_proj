<?
include_once("in_session.php");
include_once("in_php_function.php");


$sProtocolid = getQS('protocolid');
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


include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

$txtrow = "";
$query ="SELECT IPF.form_seq, IPF.form_id, FL.form_name_en, FL.form_name_th, FL.is_log
FROM i_protocol_form IPF
LEFT JOIN p_form_list FL ON FL.form_id=IPF.form_id
WHERE IPF.protocol_id=?
ORDER BY FL.is_log, IPF.form_seq";

//echo "<br>query : $sProtocolid / $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$sProtocolid);
//error_log()"$sProjid,$txtsearch / $query";
if($stmt->execute()){
	$stmt->bind_result($form_seq, $form_id, $form_name_en, $form_name_th, $is_log);
	while($stmt->fetch()) {
    $check_is_log = ""; $btn_visit_view = "";
    if($is_log == 0){
			$check_is_log = "Normal Form";
			$btn_visit_view = "<div class='fl-fix fl-mid pw50 pbtn btn-visit-view' alt='View Visit'><i class='fa fa-calendar-alt fa-2x'></i></div>";
		}
		else{
			$check_is_log = "Log Form";
			$btn_visit_view = "<div class='fl-fix fl-mid pw50' >LOG</div>";

		}


		$txtrow .= "
				 <div class='fl-wrap-row ph30 p-row-green p-row ptxt-s10 div-form-row ptxt-b' data-formid='$form_id' >
					$btn_visit_view
					<div class='fl-fix pw100'><input type='number' class='form-seq-input w-fill' size='5' value='$form_seq' />
					     <i class='pbtn btn-update-form-seq fas fa-sort-numeric-up fa-lg px-1' title='update seq'></i>
							 <i class='spinner fa fa-spinner fa-spin fa-lg' style='display:none;'></i>
					</div>
					<div class='fl-fix pw300 al-left'>$form_id</div>
					<div class='fl-fill al-left'>$form_name_en | $form_name_th</div>
					<div class='fl-fix  al-left pw80'>$check_is_log</div>
					<div class='fl-fix pw80 btn-form-del pbtn'> <i class='fas fa-times fa-lg mx-2'></i></div>
					<div class='fl-fix pw80 spinner' style='display:none;'> <i class='fa-spinner fa-spin fa-lg'></i></div>
				</div>
				<div class='fl-wrap-row fl-auto div-form-row-visit' data-formid='$form_id' style='display:none;min-height:50px;'>
  xxx
				</div>
		";

	}//while
}
$stmt->close();

?>

<div id='divSPTCF' class='fl-wrap-row fl-fill' style='width:100%'>
<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-setting-protocol-form'
<? echo " data-projid='$sProjid' data-protocolid='$sProtocolid'"; ?>>

	<div class='fl-wrap-row ph30 bg-mdark3 ptxt-s10' >
		<div class='fl-fix fl-mid pw100 pbtn pbtn-ok btn-protocol-form-add' >
				<i class='fa fa-plus fa-lg'></i> ADD | เพิ่ม
		</div>
		<div class='fl-fill fl-mid ptxt-b ptxt-s14' >
		   <? echo $sProtocolid; ?> Forms
		</div>
	</div>

	<div class='fl-wrap-row ph30 bg-mdark1 ptxt-s10 ptxt-white' >
		<div class='fl-fix fl-mid pw50' >
				<i class='fa fa-calendar-alt fa-lg'></i>
		</div>
		<div class='fl-fix al-left pw100'>Seq No.</div>
		<div class='fl-fix al-left pw300'>Form ID</div>
		<div class='fl-fill al-left ' >Form Name</div>
		<div class='fl-fix al-left pw80'>Form Type?</div>
		<div class='fl-fix fl-mid pw50' >
				<i class='fa fa-times fa-lg'></i>
		</div>
	</div>
	<div class='fl-fill bg-msoft2 fl-auto div-protocol-form-list'>
          <? echo $txtrow; ?>
	</div>
</div>

</div>


<script>
$(document).ready(function(){

	$(".div-setting-protocol-form .btn-protocol-form-add").unbind();
	$(".div-setting-protocol-form").on("click",".btn-protocol-form-add",function(){
    let protocolid = $(".div-setting-protocol-form").attr("data-protocolid");
		let sUrl = "proj_setting_select_form.php?protocolid="+protocolid;
    let projid = $(".div-setting-protocol-form").attr("data-projid");

		showDialog(sUrl,"Select Forms ","90%","90%","",function(sResult){
				 if(sResult =='1'){
					 sUrlX = "proj_setting_protocol_form.php?projid="+projid+"&protocolid="+protocolid;
					 $("#divSPTCF").parent().load(sUrlX);
				 }
		},false,function(){
			//Load Done Function

		});
	});


		$(".div-setting-protocol-form .btn-update-form-seq").unbind();
		$(".div-setting-protocol-form").on("click",".btn-update-form-seq",function(){
			btnclick = $(this);
      //console.log('val: '+$(this).closest(".div-form-row").find('.form-seq-input').val());
			let seq = $(this).closest(".div-form-row").find('.form-seq-input').val();
      if(  isNaN(seq) ) {
				$(this).closest(".div-form-row").find('.form-seq-input').notify('Fill number', 'info');
				return;
			}

			let aData = {
					u_mode:"update_protocol_form_seq",
					protocolid: $(".div-setting-protocol-form").attr("data-protocolid"),
					formid:$(this).closest(".div-form-row").attr("data-formid"),
					formseq:seq,
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.res == '1'){
							$.notify("Update Seq.", "success");
						}
						else{
							$.notify("Fail update.", "error");
						}
			});// call ajax

		});


		$(".div-setting-protocol-form .btn-form-del").unbind();
		$(".div-setting-protocol-form .btn-form-del").on("click",function(){

			btnclick = $(this);
			let s_formid = $(this).closest(".div-form-row").attr("data-formid");
      if(confirm("Are you sure to delete "+s_formid)){

				let aData = {
						u_mode:"remove_row_protocol_formid",
						protocolid: $(".div-setting-protocol-form").attr("data-protocolid"),
						formid:s_formid
				};
				startLoad(btnclick,btnclick.next(".spinner"));
				callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
							endLoad(btnclick,btnclick.next(".spinner"));
							if(rtnObj.res == '1'){
								$.notify("Remove Form successfully.", "success");
								$(".div-form-row[data-formid='"+s_formid+"']").remove();
								$(".div-form-row-visit[data-formid='"+s_formid+"']").remove();

							}
							else{
								$.notify("Fail update.", "error");
							}
				});// call ajax
			}



		});



		$(".div-setting-protocol-form .btn-visit-view").unbind();
		$(".div-setting-protocol-form").on("click",".btn-visit-view",function(){
			  let formid = $(this).closest(".div-form-row").attr("data-formid");
				let protocolid = $(".div-setting-protocol-form").attr("data-protocolid");
				let projid = $(".div-setting-protocol-form").attr("data-projid");

        if($('.div-form-row-visit[data-formid="'+formid+'"]').is(':visible')){
					$('.div-form-row-visit[data-formid="'+formid+'"]').hide();
					$('.div-form-row-visit[data-formid="'+formid+'"]').html('');
				}
				else{
					let sUrl = 'proj_setting_select_visit.php?formid='+formid+'&projid='+projid+'&protocolid='+protocolid;
					$('.div-form-row-visit[data-formid="'+formid+'"]').load(sUrl,
					function(){
             $('.div-form-row-visit[data-formid="'+formid+'"]').show();
          });

				}
		});


		$(".div-setting-protocol-form .btn-setting-search").unbind();
		$(".div-setting-protocol-form").on("click",".btn-setting-search",function(){
			btnclick = $(this);
			let aData = {
					u_mode:"select_list_protocol",
					projid: $(".div-setting-protocol-form").attr("data-projid"),
					txtsearch:$(".txt-setting-search").val().trim()
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.row_amt == '0'){
							$.notify("No data found.", "info");
						//	$('.div-protocol-form-list').html('<center> - No data found. - </center>');
						}
						else{
							$('.div-protocol-form-list').html('');
							$('.div-protocol-form-list').html(rtnObj.txtrow);
						}
			});// call ajax
		});


		$(".div-setting-protocol-form .btn-setting-save").unbind();
		$(".div-setting-protocol-form").on("click",".btn-setting-save",function(){
			btnclick = $(this);
      let row_id = $('.save-data[data-id="protocol_id"]').val();

      let flag_update = 0;
			let s_data_item = {};
			$(".div-setting-protocol-form .save-data").each(function(ix,objx){
				let sVal = getWDataCompValue(objx);
				let sOData = getWODataComp(objx);

				s_data_item[$(objx).attr("data-id")] = $(objx).val().trim();

				if(sVal != sOData) {
					flag_update=1;
				}
			});

			if(flag_update){
				let aData = {
						u_mode:"update_row_protocol",
						projid: $(".div-setting-protocol-form").attr("data-projid"),
						id:row_id,
						lst_data: s_data_item
				};

				startLoad(btnclick,btnclick.next(".spinner"));
				callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
							endLoad(btnclick,btnclick.next(".spinner"));
							if(rtnObj.txtrow != ''){
								/*
								if(aData.sid == ''){
									$.notify("Add data successfully.", "success");
								}
								else{
									$.notify("Update data successfully.", "success");
									$('.div-setting-row[data-id="'+row_id+'"]').remove();
								}
								*/

								$.notify("Update data successfully.", "success");
								$('.div-setting-row[data-id="'+row_id+'"]').remove();

								$('.div-protocol-form-list').prepend(rtnObj.txtrow);
								$(".div-setting-edit").hide();

								$('.div-setting-row[data-id="'+row_id+'"]').addClass('pbg-grey');

								//$('.div-protocol-form-list').html('<center> - No data found. - </center>');
							}
				});// call ajax
			}


		});



$(".div-setting-protocol-form .btn-setting-edit").unbind();
$(".div-setting-protocol-form").on("click",".btn-setting-edit",function(){
		clearDataEdit();
		$('.save-data[data-id="group_id"]').val($(this).parent().find('.s-group_id').html());
		$('.save-data[data-id="protocol_id"]').val($(this).parent().find('.s-protocol_id').html());
		$('.save-data[data-id="protocol_version"]').val($(this).parent().find('.s-protocol_version').html());
		$('.save-data[data-id="protocol_note"]').val($(this).parent().find('.s-protocol_note').html());
		$('.save-data[data-id="start_date"]').val($(this).parent().find('.s-start_date').html());
		$('.save-data[data-id="stop_date"]').val($(this).parent().find('.s-stop_date').html());

    $('.edit-lock').prop('disabled', true);


		$('.div-setting-edit').show();
		$('.div-setting-edit').focus();

});



function clearDataEdit(){
	$(".div-setting-protocol-form .save-data").each(function(ix,objx){
			$(objx).val('');
	});
	$(".div-setting-protocol-form .v-checkbox").each(function(ix,objx){
			$(objx).prop('checked', false);
	});
}


  $(".div-setting-protocol-form .btn-setting-search").click();

});






</script>
