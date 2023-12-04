<?
include_once("in_session.php");
include_once("in_php_function.php");


$sProjid = getQS('projid');
$sProtocolid = getQS('protocolid');
$sFormid = getQS('formid');
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

$txtrow = ""; $option_chkall = "";
$query ="SELECT VL.visit_id, VL.visit_name, VL.group_id, PFV.visit_id, PFV.option_form, FL.is_log
FROM p_visit_list VL
LEFT JOIN p_form_list FL ON (FL.form_id=?)
LEFT JOIN i_protocol_form_visit PFV ON (PFV.visit_id=VL.visit_id AND PFV.protocol_id=? AND PFV.form_id=FL.form_id)

WHERE VL.proj_id=?
ORDER BY VL.visit_day, VL.visit_id ";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sss', $sFormid, $sProtocolid, $sProjid);
//error_log()"$sProjid,$txtsearch / $query";
if($stmt->execute()){
	$stmt->bind_result($visit_id, $visit_name, $group_id, $visit_id_form, $option_form, $is_log );
	while($stmt->fetch()) {

    $checked = ""; $is_check = "0";
		if($visit_id==$visit_id_form){
			$checked = "checked"; $is_check = "1";
		}

		$checked_opt = ""; $is_check_opt = "0";
		if($option_form == '1'){
			$checked_opt = "checked"; $is_check_opt = "1";
		}

		$option_chkbox = "";
    if($is_log == '0'){ // normal form
			$is_disable = ($is_check == '1')?"":"disabled";
			$option_chkbox = "<input type='checkbox' class='chk-form-visit-opt' data-visitid='$visit_id' $checked_opt data-odata='$is_check_opt' $is_disable />";
		}

		$txtrow .= "
				 <div class='fl-wrap-row ph20 p-row ptxt-s10 div-form-row ptxt-b' data-visitid='$visit_id' >
					<div class='fl-fix pw50'><input type='checkbox' class='chk-form-visit' $checked data-odata='$is_check'/></div>
					<div class='fl-fix pw200 al-left '>$visit_id</div>
					<div class='fl-fill al-left '>$visit_name</div>
					<div class='fl-fix  al-left pw150 '>$group_id</div>
					<div class='fl-fix pw100 '>$option_chkbox</div>
				</div>
		";

	}//while

	if($is_log == '0'){
		$option_chkall = "<input type='checkbox' class='chkall-opt mx-2' />Optional form?";
	}


}
$stmt->close();

?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-sel-form-visit<? echo $sFormid; ?>' data-protocolid='<? echo $sProtocolid; ?>' data-formid='<? echo $sFormid; ?>'>

	<div class='fl-fill fl-auto bg-msoft2'>
		<div class='fl-wrap-row ph20 p-row ptxt-s10 ptxt-b pbg-blue ptxt-white'>
		 <div class='fl-fix pw100 al-left '>
			 <input type='checkbox' class='chkall mx-2' />
			 Check all visit / Uncheck all visit
		 </div>
		 <div class='fl-fill al-left '>

		 </div>
		 <div class='fl-fix pw150 al-left '>
			 Group ID
		 </div>
		 <div class='fl-fix pw100 al-left '>
			 <? echo $option_chkall; ?>
		 </div>
	 </div>
        <? echo $txtrow; ?>

		<div class='fl-wrap-row fl-mid ph20 p-row mx-2 ptxt-s10 ptxt-b pbtn pbtn-ok btn-update-formvisit'>
			  <i class='fa fa-calendar-check fa-lg'></i> UPDATE Form Visit
		</div>
		<div class='fl-wrap-row ph20 p-row mx-2 ptxt-s10 ptxt-b spinner' style='display:none;'>
				<i class=' fa fa-spinner fa-spin fa-lg' ></i>
		</div>
	</div>

</div>








<script>
$(document).ready(function(){
var class_main_div = ".div-sel-form-visit<? echo $sFormid; ?>";

/*
	$(class_main_div+" .chk").off('click');
	$(class_main_div+" .chk").on("click", function() {
		console.log("click"+$(this).closest(".chk-sel-form").is(':checked') );
		 if($(this).closest(".chk-sel-form").is(':checked')){
			 $(this).closest(".chk-sel-form").attr('checked', true);
		 }
		 else{
			 $(this).closest(".chk-sel-form").attr('checked', true);
		 }
	});
*/

$(class_main_div+" .chkall").off('click');
$(class_main_div+" .chkall").on("click", function() {

	 let formid = $(this).closest(".div-sel-form-visit").attr('data-formid');
	 //let formid = $(this).closest(".div-sel-form-visit").attr('data-formid');
	 //	console.log("formid: "+formid+" /  click"+$(this).is(':checked') );
	 if($(this).is(':checked')){
     $(class_main_div+" .chk-form-visit").prop('checked', true);
	 }
	 else{
     $(class_main_div+" .chk-form-visit").prop('checked', false);
	 }
});

$(class_main_div+" .chk-form-visit").off('click');
$(class_main_div+" .chk-form-visit").on("click", function() {
	let visitid = $(this).closest(".div-form-row").attr('data-visitid');
	 if($(this).is(':checked')){
     $(class_main_div+" .chk-form-visit-opt[data-visitid='"+visitid+"']").prop('disabled', false);
	 }
	 else{
     $(class_main_div+" .chk-form-visit-opt[data-visitid='"+visitid+"']").prop('disabled', true);
	 }
});

$(class_main_div+" .chkall-opt").off('click');
$(class_main_div+" .chkall-opt").on("click", function() {
	 if($(this).is(':checked')){
     $(class_main_div+" .chk-form-visit-opt:enabled").prop('checked', true);
	 }
	 else{
     $(class_main_div+" .chk-form-visit-opt:enabled").prop('checked', false);
	 }
});




/*
$(class_main_div+" .chkall").off('click');
$(class_main_div+" .chkall").on("click", function() {
	console.log("click"+$(this).closest(".chk-sel-form").is(':checked') );
	 if($(this).closest(".chk-sel-form").is(':checked')){
		 $(this).closest(".chk-sel-form").attr('checked', true);
	 }
	 else{
		 $(this).closest(".chk-sel-form").attr('checked', true);
	 }
});
*/
	$(class_main_div+" .btn-update-formvisit").unbind();
	$(class_main_div+" .btn-update-formvisit").on("click",function(){
		let arr_visit_id = [];

		$(class_main_div+" .chk-form-visit").each(function(ix,objx){
			let visitid = $(this).closest(".div-form-row").attr('data-visitid');
      let chkbox_opt = $(class_main_div+" .chk-form-visit-opt[data-visitid='"+visitid+"']");

			  let chk_val = getWDataCompValue($(this));
				let chk_val_opt = getWDataCompValue(chkbox_opt);
				if(($(this).attr('data-odata') != chk_val) || (chkbox_opt.attr('data-odata') != chk_val_opt)){
					arr_visit_id.push(visitid+":"+chk_val+":"+chk_val_opt);
				}

		  //  console.log("check: "+$(objx).closest(".div-form-row").attr("data-formid"));
		});

		if(arr_visit_id.length == 0){
			$.notify("No data changed.", "info");
			return;
		}

		let s_formid = $(this).closest(class_main_div).attr('data-formid');
		let s_protocolid = $(this).closest(class_main_div).attr('data-protocolid');

			btnclick = $(this);
			let aData = {
					u_mode:"update_protocol_form_visit",
					formid:s_formid,
					protocolid:s_protocolid,
					lst_data:arr_visit_id
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.res == '1'){
							$.notify("Update form visit successfully.", "success");
						}
						else{
              $.notify("Fail to update.", "error");
						}
			});// call ajax

	});






});




</script>
