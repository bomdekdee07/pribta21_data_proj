<?
include_once("in_session.php");
include_once("in_php_function.php");


$sProtocolid = getQS('protocolid');


include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

$txtrow = "";
$query ="SELECT form_id, form_name_en, form_name_th, is_log
FROM p_form_list
WHERE form_id NOT IN (select form_id from i_protocol_form where protocol_id=?)
ORDER BY form_id ";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$sProtocolid);
//error_log()"$sProjid,$txtsearch / $query";
if($stmt->execute()){
	$stmt->bind_result($form_id, $form_name_en, $form_name_th, $is_log);
	while($stmt->fetch()) {
    $check_is_log = ($is_log==0)?"Normal Form":"Log Form";
		$txtrow .= "
				 <div class='fl-wrap-row ph30 p-row ptxt-s10 div-form-row ptxt-b' data-formid='$form_id' >
					<div class='fl-fix pw50'><input type='checkbox' class='chk-sel-form' /></div>
					<div class='fl-fix pw300 al-left chk'>$form_id</div>
					<div class='fl-fill al-left chk'>$form_name_en | $form_name_th</div>
					<div class='fl-fix  al-left pw80 chk'>$check_is_log</div>
				</div>
		";

	}//while
}
$stmt->close();

?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-col div-setting-sel-form' data-protocolid='<? echo $sProtocolid; ?>'>

	<div class='fl-wrap-row ph30 bg-mdark3 ptxt-s10' >
		<div class='fl-fix fl-mid pw300' >
				<input type='text' class='txt-form-find' Placeholder='Find form here'>
		</div>

		<div class='fl-fill fl-mid ptxt-b ptxt-s14' >
		   Select Forms IN <? echo $sProtocolid; ?>
		</div>
	</div>

	<div class='fl-wrap-row ph30 bg-mdark1 ptxt-s10 ptxt-white' >
		<div class='fl-fix fl-mid pw50' >
				<i class='fa fa-calendar-alt fa-lg'></i>
		</div>
		<div class='fl-fix al-left pw300'>Form ID</div>
		<div class='fl-fill al-left ' >Form Name</div>
		<div class='fl-fix al-left pw80'>Log Form?</div>
	</div>



	<div class='fl-fill fl-auto bg-msoft2 div-sel-form-list'>
        <? echo $txtrow; ?>
	</div>
	<div class='fl-wrap-row fl-mid ph30 bg-mdark2 ptxt-b ptxt-s10 ptxt-white pbtn btn-sel-form' >
         Select Form
	</div>
</div>








<script>
$(document).ready(function(){
  $(".div-setting-sel-form .txt-form-find").off('keyup');
	$(".div-setting-sel-form .txt-form-find").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $(".div-sel-form-list .div-form-row").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

/*
	$(".div-setting-sel-form .chk").off('click');
	$(".div-setting-sel-form .chk").on("click", function() {
		console.log("click"+$(this).closest(".chk-sel-form").is(':checked') );
		 if($(this).closest(".chk-sel-form").is(':checked')){
			 $(this).closest(".chk-sel-form").attr('checked', true);
		 }
		 else{
			 $(this).closest(".chk-sel-form").attr('checked', true);
		 }
	});
*/
	$(".div-setting-sel-form .btn-sel-form").unbind();
	$(".div-setting-sel-form").on("click",".btn-sel-form",function(){
		let arr_form_id = [];
		$(".div-setting-sel-form .chk-sel-form:checked").each(function(ix,objx){
			  arr_form_id.push($(objx).closest(".div-form-row").attr("data-formid"));
		    //console.log("check: "+$(objx).closest(".div-form-row").attr("data-formid"));
		});


			btnclick = $(this);
			let aData = {
					u_mode:"update_row_protocol_formid",
					protocolid: $(".div-setting-sel-form").attr("data-protocolid"),
					lst_data:arr_form_id
			};
			startLoad(btnclick,btnclick.next(".spinner"));
			callAjax("proj_setting_a.php",aData,function(rtnObj,aData){
						endLoad(btnclick,btnclick.next(".spinner"));
						if(rtnObj.res == '1'){
							$.notify("Select form successfully.", "info");
							setDlgResult(1,".div-setting-sel-form");
              closeDlg(".div-setting-sel-form");
						//	$('.div-protocol-form-list').html('<center> - No data found. - </center>');
						}
						else{
              $.notify("Fail to update.", "error");
						}
			});// call ajax

	});





});




</script>
