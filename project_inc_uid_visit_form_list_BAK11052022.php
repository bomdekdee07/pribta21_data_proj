<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");

$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sVisitid = getQS("visitid");
$sVisitdate = getQS("visitdate");
$sVisittime = getQS("visittime");

$bind_param = "ss";
$array_val = array($sProjid, "medical_certificate");
$data_bt_name = array();

$query = "SELECT button_name
from p_project_button 
where proj_id = ?
and button_id = ?
and button_status = 1;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$stmt->bind_result($button_name);
	while($stmt->fetch()){
		$data_bt_name[$button_name]["name"] = $button_name;
	}
}
$stmt->close();

$bind_param = "ss";
$array_val = array($sUID, $sVisitdate);
$data_status_done = "";

$query = "SELECT visit_status
from p_project_uid_visit p_visit
where uid = ?
and visit_date = ?;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$stmt->bind_result($visit_status);
	while($stmt->fetch()){
		$data_status_done = $visit_status;
	}
}
$stmt->close();

if($sVisitdate == "") $sVisitdate = (new DateTime())->format('Y-m-d');
if($sVisittime == "") $sVisittime = (new DateTime())->format('H:i:s');

$txt_row = ""; $txt_row_main = ""; $txt_row_opt=""; $txt_row_log = "";
$visit_name = ""; $visit_status = ""; $visit_note = "";

//echo "qs: $sProjid/$sGroupid/$sUID";

if($sUID !="" && $sProjid != "" && $sVisitid != "" ){

	if(!isset($proj_auth['allow_view'])){
		include_once("project_inc_uid_permission.php");
	}

if($proj_auth['allow_view'] == 1){
	$query ="SELECT VL.visit_id, VL.visit_name , PP.protocol_id, PF.form_id, PF.form_name ,
	PFV.option_form , PP.protocol_version, DFD.uid, DFD.is_done,
	PUV.visit_status, PUV.visit_note, FLDA.attr_val as is_externallink
	FROM p_visit_list VL
	LEFT JOIN p_project_uid_visit PUV ON (PUV.proj_id=VL.proj_id AND PUV.visit_id=VL.visit_id AND  PUV.group_id=? AND PUV.visit_date=? AND PUV.uid=?)
	LEFT JOIN i_project_protocol PP ON ( VL.proj_id=PP.proj_id AND  (PP.group_id=PUV.group_id OR PP.group_id='') )
	LEFT JOIN i_protocol_form_visit PFV ON ( PP.protocol_id=PFV.protocol_id AND  VL.visit_id=PFV.visit_id)
	LEFT JOIN i_protocol_form PF ON ( PFV.form_id=PF.form_id AND  PP.protocol_id=PF.protocol_id)
	LEFT JOIN p_data_form_done DFD ON ( DFD.form_id=PF.form_id AND DFD.uid=? AND DFD.collect_date=? AND DFD.collect_time=?)
	LEFT JOIN p_form_list_data_attribute FLDA ON ( FLDA.form_id=PF.form_id AND FLDA.data_id='' AND FLDA.attr_id='externallink')


	WHERE VL.visit_id=? AND VL.proj_id=?
	AND (VL.group_id=? OR VL.group_id='')
	AND (PUV.visit_date >= PP.start_date AND PUV.visit_date <= PP.stop_date)
	ORDER BY PF.form_seq
	";


	//echo "$sGroupid,$sVisitdate, $sUID,$sUID,$sVisitdate, $sVisittime,$sVisitid, $sProjid, $sGroupid/ $query";
			$stmt = $mysqli->prepare($query);

			$stmt->bind_param('sssssssss', $sGroupid, $sVisitdate, $sUID, $sUID, $sVisitdate, $sVisittime,
			$sVisitid, $sProjid, $sGroupid);



			if($stmt->execute()){
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc()) {
					$visit_name = $row["visit_name"];
					$visit_status = $row["visit_status"];
					$visit_note =$row["visit_note"];

					//$txt_row_main .= addRowVisitForm($row["uid"], $row["form_id"], $row["form_name"], $row["protocol_version"], $row["option_form"]);


					if($row["option_form"] == "0") // standard form
					$txt_row_main .= addRowVisitForm($row["uid"], $row["form_id"], $row["form_name"], $row["protocol_version"], $row["option_form"], $row["is_done"], $row["is_externallink"]);
					else if ($row["option_form"] == "1") // optional form
					$txt_row_opt .= addRowVisitForm($row["uid"], $row["form_id"], $row["form_name"] ,$row["protocol_version"], $row["option_form"], $row["is_done"], $row["is_externallink"]);
					else if ($row["option_form"] == "2") // log form
					$txt_row_log .= addRowVisitForm($row["uid"], $row["form_id"], $row["form_name"] ,$row["protocol_version"], $row["option_form"], '','');

				}//while
			}
			$stmt->close();
			$mysqli->close();
	}

	if($txt_row_main != ""){
		$txt_row_main = "
		<div class='fl-fix ptxt-s12 ptxt-b ph20'>
			<i class='fas fa-angle-right fa-lg'></i> MAIN FORM:
		</div>
		<div class='fl-fill' style='min-height:80px;'>
			$txt_row_main
		</div>
		";
	}
	if($txt_row_opt != ""){
		$txt_row_opt = "
		<div class='fl-fix ptxt-s12 ptxt-b ph20'>
			<i class='fas fa-angle-right fa-lg'></i> OPIONAL FORM:
		</div>
		<div class='fl-fill' style='min-height:80px;'>
			$txt_row_opt
		</div>
		";
	}
	if($txt_row_log != ""){
		$txt_row_log = "
		<div class='fl-fix ptxt-s12 ptxt-b ph20'>
			<i class='fas fa-angle-right fa-lg'></i> LOG FORM:
		</div>
		<div class='fl-fill' style='min-height:80px;'>
			$txt_row_log
		</div>
		";
	}

  $txt_row = "$txt_row_main $txt_row_opt $txt_row_log";

	if($txt_row == "") $txt_row = "No Data Found.";

}
else{
	 $txt_row = "Missing parameter.";
}



function addRowVisitForm($uid, $form_id, $form_name, $protocol_version, $form_opt, $is_done, $is_externallink){
	$form_done = ($uid != "")?"<span class='badge badge-success'><i class='fa fa-check fa-lg'></i> Done</span>":"";
  	$form_done = "";
	if($is_done == "1")  $form_done = "<div class='bg-success ptxt-white'><i class='fa fa-check fa-lg'></i> Done</div>";
	else if ($is_done == "2") $form_done = "<div class='bg-warning'> Incomplete</div>";

	$external_link = "";
	if($is_externallink == '1'){
			$external_link ="	<div class='fl-fix fl-mid pw50 pbtn bg-mdark2 ptxt-white view-form-link' data-formid='$form_id' data-formname='$form_name' data-formopt='$form_opt'>
							LINK
					</div>";
	}
	else{
		$external_link ="	<div class='fl-fix fl-mid pw50'></div>";
	}

	if($form_opt != 2){ // normal form
		$txt_row = "
				<div class='fl-wrap-col pw200 bg-msoft1  ptxt-s8 p-row'>
						<div class='fl-fix ph25 v-mid px-1 ptxt-b pbtn view-form-detail' data-formid='$form_id' data-formname='$form_name'>
									$form_name
						</div>
						<div class='fl-wrap-row ph10 bg-msoft2 '>
							<div class='fl-fix v-mid pw100'>
										V. $protocol_version
							</div>
							$external_link
							<div class='fl-fix v-mid pw50 palign-right pr-1   $form_id' >
									$form_done
							</div>
						</div>
				</div>
		";
	}
	else{ // log form
		$txt_row = "
				<div class='fl-wrap-col pw200 bg-ssoft1  ptxt-s8 p-row'>
						<div class='fl-fix ph25 v-mid px-1 ptxt-b pbtn view-form-log' data-formid='$form_id' data-formname='$form_name'>
									$form_name
						</div>
						<div class='fl-wrap-row ph10 bg-ssoft2 '>
							<div class='fl-fix v-mid pw100'>
										V. $protocol_version
							</div>
						</div>
				</div>
		";
	}

	return $txt_row;
}

?>

<style>
.div-visitform{
  width:70px; height:30px;
}

</style>

<div class='fl-wrap-row pt-1 <? echo $class_auth; ?>' id="div_visit_form_data"
  data-uid='<? echo $sUID; ?>' data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>'
	data-visitid='<? echo $sVisitid; ?>' data-visitdate='<? echo $sVisitdate;?>' data-stvisit="<? echo $data_status_done; ?>" data-btname="<? echo $data_bt_name; ?>">

	<div class='fl-fix fl-fill fl-auto py-1 bg-mdark3' style='min-width:220px;max-width:220px;'>
	  <div class='fl-wrap-col  px-1'>
			<div class='fl-fix '>
				<div class='fl-wrap-row fs-s bg-mdark2'>
					<div class='fl-fill b-txt text-white'>
						<? echo "<div class='ptxt-b ptxt-s20 h-xs' style='color:yellow;'>$sVisitdate</div><div class='h-xs'>[$sVisitid] $visit_name </div>" ; ?>
					</div>
					<div class='fl-fix fl-mid ph20 pbtn pbtn-red' style='min-width:20px;max-width:20px'>
						<i class='fas fa-window-close fa-lg  btnclose back-schedule-list' title="Close"></i>
					</div>
			  </div>
			</div>

			<div class='fl-wrap-row ph20 ptxt-s10 bg-mdark3'>
				<div class='fl-fix pw50'>
					Status:
				</div>
				<div class='fl-fix pw150'>

					<select id='ddlVisitStatus' class='auth-view auth-data auth-admin'>
					 <option value='' disabled>-Select-</option>
					 <? include("project_opt_visit_status.php") ?>
				 </select>
				 <i class='fa fa-spinner fa-spin fa-lg' style="display:none;"></i>

				</div>
			</div>

      <? echo $txt_row; ?>

			<div class='fl-fix ptxt-s12 ptxt-b ph20'>
				Visit Note:
			</div>
			<div class='fl-fill'>
				<textarea id='txtVisitNote' class='ptxt-s10' rows="3" cols="35" data-odata='<? echo $visit_note;?>'><? echo $visit_note;?></textarea>
				<button id='txtVisitNote_update' class='pbtn ptxt-white ptxt-s10 bg-mdark2 auth-data'>Update Visit Note</button>
				<i class='fa fa-spinner fa-spin fa-lg' style="display:none;"></i>
			</div>
		</div>

	</div>
	<div class='fl-wrap-col py-1 bg-sdark2'>
		<div class='fl-wrap-row ph25 ptxt-b ptxt-white frmHead'>
			<div class="fl-fix fl-mid-left w-150 holiday-ml-1 h-25">
				<button class="btn btn-success font-s-2 bt-print-certificate" id="<? echo $data_bt_name; ?>" style="padding: 0px 4px; display:none;"><i class="fa fa-print" aria-hidden="true"></i> Print Certificate</button>
			</div>
			<div class="fl-fill fl-mid">
				- กรุณาเลือกฟอร์ม | Please select visit form -
			</div>
			<div class="fl-fix fl-mid-left w-150"></div>
		</div>
		<div class='fl-fill fl-auto bg-msoft3 frmData'>

		</div>
		<div class='fl-fill fl-mid bg-msoft3 ptxt-s12 spinner'  style="display:none;">
			 <i class='fa fa-spinner fa-spin fa-3x'></i> Loading
		</div>

	</div>
</div> <!-- div_visit_form_data -->

<script>
$(document).ready(function(){
	//** INIT DATA **
	$('#ddlVisitStatus').val('<? echo $visit_status;?>');

	$('.auth-data').prop('disabled', true); // allow_view
	if($('#div_visit_form_data').hasClass('allow_data')) {
		if($('#ddlVisitStatus').val() != '1'){ // visit complete
			 $('.auth-data').prop('disabled', false);
		}
	}
  if($('#div_visit_form_data').hasClass('allow_admin')) {
		$('.auth-data').prop('disabled', false);
		$("#ddlVisitStatus").append(new Option("นัดหมาย", "0"));
	}
	//***************

	$('#div_visit_form_data').on("click",".view-form-detail",function(){
		let formid=$(this).attr("data-formid");
		let uid=$("#div_visit_form_data").attr("data-uid");
		let visitdate=$("#div_visit_form_data").attr("data-visitdate");
		let visittime="00:00:00";
    $(".frmHead").html($(this).attr("data-formname"));
		//console.log("formname: "+$(this).next('.form-name').text());
		 view_visit_form_detail(uid, formid, visitdate, visittime);
	});
	
	var check_st_done = $("#div_visit_form_data").data("stvisit");
	if(check_st_done == "1"){
		$("#div_visit_form_data .bt-print-certificate").show();
	}
	else{
		$("#div_visit_form_data .bt-print-certificate").hide();
	}

	$("#div_visit_form_data .bt-print-certificate").off("click");
	$("#div_visit_form_data .bt-print-certificate").on("click", function(){
		var suid = $("#div_visit_form_data").attr("data-uid");
		var scoldate = $("#div_visit_form_data").attr("data-visitdate");
		var gen_open_html = "project_inc_uid_visit_form_certificate_pdf.php?uid="+suid+"&coldate="+scoldate;

		window.open(gen_open_html);
	});

	$('#div_visit_form_data').on("click",".view-form-link",function(){
		let formid=$(this).attr("data-formid");
		let formname=$(this).attr("data-formname");
		let formopt=$(this).attr("data-formopt");

		let uid=$("#div_visit_form_data").attr("data-uid");
		let projid = $("#div_visit_form_data").attr("data-projid");
		let groupid= $("#div_visit_form_data").attr("data-groupid");
		let visitid= $("#div_visit_form_data").attr("data-visitid");
		let visitdate=$("#div_visit_form_data").attr("data-visitdate");
		let visittime="00:00:00";

			let sUrl = "project_inc_uid_visit_form_link.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&visitid="+visitid+"&coldate="+visitdate+"&coltime="+visittime+"&formid="+formid;
		//  console.log("sUrl: "+sUrl);
			showDialog(sUrl,formname+" ["+visitdate+"]","550","500","",function(sResult){
				 //  console.log("sResult: "+sResult+"/"+scheduledate+"/"+row_no);
			},false,"");


	});






	$('#div_visit_form_data').on("click",".back-schedule-list",function(){
    	view_visit_schedule_list();
	});

	$("#div_visit_form_data").on("change","#ddlVisitStatus",function(){
		 var aData = {
				 u_mode:"update_visit_status",
				 status_id:	$(this).val(),
				 uid:	$("#div_visit_form_data").attr("data-uid"),
				 projid:$("#div_visit_form_data").attr("data-projid"),
				 groupid:$("#div_visit_form_data").attr("data-groupid"),
				 visitid:$("#div_visit_form_data").attr("data-visitid"),
				 visitdate:$("#div_visit_form_data").attr("data-visitdate")
		 };

		 startLoad($('#ddlVisitStatus'), $("#ddlVisitStatus").next(".fa-spinner"));
		 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
					 endLoad($('#ddlVisitStatus'), $("#ddlVisitStatus").next(".fa-spinner"));
					 if(rtnObj.res == 1){
						 $("#ddlVisitStatus").notify("Visit status updated.", "success");

						 if(aData.status_id == '0'){

							flag_update = confirm("ตั้งค่าเป็นสถานะนัดหมายแล้ว ข้อมูลที่เคยเก็บในวันที่ "+ aData.visitdate+" ต้องการลบด้วยหรือไม่ ?");
              if(flag_update){
                  deleteDataResult(aData.uid, aData.visitdate);
							}

						 }

					 }
					 else{
						 $("#ddlVisitStatus").notify("Fail to update.", "error");
					 }
		 });// call ajax
	 });



	 $("#div_visit_form_data").on("click","#txtVisitNote_update",function(){
	 	 if($('#txtVisitNote').val().trim() == $('#txtVisitNote').attr("data-odata") ){
	 		 $('#txtVisitNote').notify("No data changed.", "info");
	 		 return;
	 	 }
	 	 var aData = {
	 			 u_mode:"update_visit_note",
	 			 visitnote:	$('#txtVisitNote').val().trim(),
	 			 uid:	$("#div_visit_form_data").attr("data-uid"),
	 			 projid:$("#div_visit_form_data").attr("data-projid"),
	 			 groupid:$("#div_visit_form_data").attr("data-groupid"),
				 visitid:$("#div_visit_form_data").attr("data-visitid"),
				 visitdate:$("#div_visit_form_data").attr("data-visitdate")

	 	 };
	 	 startLoad($('#txtVisitNote_update'), $("#txtVisitNote_update").next(".fa-spinner"));
	 	 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
	 				 endLoad($('#txtVisitNote_update'), $("#txtVisitNote_update").next(".fa-spinner"));
	 				 if(rtnObj.res == 1){
	 					 $("#txtVisitNote").attr("data-odata", aData.proj_note);
	 					 $("#txtVisitNote_update").notify("Update Note.", "success");

	 				 }
	 				 else{
	 					 $("#txtVisitNote_update").notify("Fail to update.", "error");
	 				 }
	 	 });// call ajax
	  });


});


function view_visit_form_detail(uid, formid, visitdate, visittime){
	let projid = $("#div_visit_form_data").attr('data-projid');
  // console.log("view form: "+formid+"/"+uid+"/"+visitdate);
	 sUrl = "p_form_view.php?form_id="+formid+"&lang=th&uid="+uid+"&coldate="+visitdate+"&coltime="+visittime+"&projid="+projid;

   $(".frmData").html('');
	 $(".frmData").next('.spinner').show();
	 $(".frmData").load(sUrl, function(responseTxt, statusTxt, xhr){
			if(statusTxt == "success"){
				$(".frmData").next('.spinner').hide();
			}
			else if(statusTxt == "error"){
				$(".frmData").html('Error Load Form: '+formid);
				$(".frmData").next('.spinner').hide();
				console.log("error load "+sUrl);
			}

   });

}


function deleteDataResult(uid, visitdate){
	var aData = {
			u_mode:"delete_data_result",
			uid:	$("#div_visit_form_data").attr("data-uid"),
			visitdate:$("#div_visit_form_data").attr("data-visitdate")
	};
	startLoad($('#ddlVisitStatus'), $("#ddlVisitStatus").next(".fa-spinner"));
	callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				endLoad($('#ddlVisitStatus'), $("#ddlVisitStatus").next(".fa-spinner"));
				if(rtnObj.res == 1){
					$.notify("Delete data "+aData.uid+" ["+aData.visitdate+"].", "info");

				}
				else{
					alert("No data delete.");
				}
				view_visit_schedule_list();

	});// call ajax
}

function callback_func(form_id, is_done){
	//console.log("callback : "+form_id+is_done);
	$.notify("Form done ["+form_id+"]", "info");
	$('.'+form_id).html("<span class='badge badge-success'><i class='fa fa-check fa-lg'></i> Done</span>");


	/*
	var aData = {
			u_mode:"update_form_done",
			uid:	$("#div_visit_form_data").attr("data-uid"),
			visitdate:$("#div_visit_form_data").attr("data-visitdate"),
			formid:form_id
	};
	callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				if(rtnObj.res == 1){

					$.notify("Form done "+aData.uid+" ["+aData.visitdate+"].", "info");
					$('.'+aData.formid).html("<span class='badge badge-success'><i class='fa fa-check fa-lg'></i> Done</span>");
				}
	});// call ajax
*/
}
</script>
