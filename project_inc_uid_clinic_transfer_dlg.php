<?
/* Project UID Visit Main  */
include("in_session.php");
include_once("in_php_function.php");
include_once("project_inc_uid_permission.php");



$sUID = getQS("uid");
$sProjid = getQS("projid");


$txt_row = "";
if(isset($proj_auth['allow_admin'])){
if($proj_auth['allow_admin'] == 1){

		include("in_db_conn.php");

			$query ="SELECT clinic_id, clinic_name
			FROM p_clinic
			WHERE clinic_pid != '' AND clinic_id NOT IN
			(select clinic_id FROM p_project_uid_list WHERE uid=? AND proj_id=? AND uid_status='1')
			ORDER BY clinic_name
			";

			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('ss', $sUID, $sProjid);

			if($stmt->execute()){
				$stmt->bind_result($clinicid, $clinicname);

				while($stmt->fetch()) {
					$txt_row .= "<option value='$clinicid'>$clinicid: $clinicname</option>";
				}//while
			}
			$stmt->close();

			if($txt_row != ''){
				$txt_row = "
				 <div id='div_dlg_uid_clinic_transfer' class='fl-wrap-col pw500'
					 data-uid='$sUID' data-projid='$sProjid' >
					 <div class='fl-wrap-row fl-mid ph30'>
						 Select new clinic:
					 </div>
					 <div class='fl-wrap-row ph30 fl-mid'>
						<SELECT id='sel_clinic_id'>
							<OPTION value='' disabled>Select clinic id</OPTION>
							$txt_row
						</SELECT>
					 </div>
					 <div class='fl-wrap-row fl-mid pbtn pbtn-blue ph20  btn-update-clinicid'>
						 Transfer to new clinic
					 </div>
					 <div class='fl-wrap-row ph20 spinner' style='display:none;'>
						 WAIT
					 </div>
					 <div class='fl-wrap-row fl-mid ph100'>
						 After transfer to new clinic, the old clinic will not be able to see PID data in visit form anymore.
						 The PID's visit data will be shown to new clinic.
					 </div>
				 </div>
				";
			}
			else {
				$txt_row = "- No clinic available. -";
			}
}
else{
	$txt_row = "- No clinic available. -";
}
}
else{
$txt_row = "- Not allow to change clinic. (You may need to login to system)-";
}

echo $txt_row;



?>

<script>
$(document).ready(function(){
	 $('.btn-update-clinicid').off('click');
	 $('.btn-update-clinicid').on("click",function(){
		     let btnclick = $(this);
         let sUid = $('#div_dlg_uid_clinic_transfer').attr('data-uid');
				 let sProjid = $('#div_dlg_uid_clinic_transfer').attr('data-projid');
				 let sClinicid = $('#div_dlg_uid_clinic_transfer #sel_clinic_id').val();
				     var aData = {
				        u_mode:"uid_clinic_transfer",
				        uid:sUid,
				        projid:sProjid,
				        clinicid:sClinicid
				    };
				    startLoad(btnclick, btnclick.next(".spinner"));
				    callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				        endLoad(btnclick, btnclick.next(".spinner"));
				        if(rtnObj.res == 1){
				           $.notify("Transfer to clinic: "+aData.clinicid+" successfully", "success");
				           setDlgResult(aData.clinicid);
				           closeDlg();
				         }
				         else{
				            $.notify("Fail to update.", "error");
				         }
				     });// call ajax

	 });
});
</script>
