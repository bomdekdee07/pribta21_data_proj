
<?
include("in_db_conn.php");
include_once("in_php_function.php");
include_once("in_php_function_patient.php");
include_once("in_php_pop99.php");


$sUid = getQS("uid");
$sType = getQS("type"); // 1:origin, 2:destination

$txtrow = "";
$divname = "div-uid-transfer".$sType;
if($sUid != ""){

	$query = "SELECT  uid, uic, fname, sname, en_fname, en_sname, sex,
	citizen_id, passport_id, tel_no, date_of_birth, email, line_id
	FROM patient_info
	WHERE uid=?
	";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sUid);
	//echo "query : $query";
	if($stmt->execute()){
		$stmt->bind_result($uid, $uic, $fname, $sname, $en_fname, $en_sname, $sex,
		$citizen_id, $passport, $tel_no, $date_of_birth, $email, $line_id);
		$stmt->store_result();
	  if ($stmt->fetch()) {
         $row_amt = $stmt->num_rows;
				 if($row_amt > 0){
            $id_card = "";
					  if($citizen_id != ''){
							$id_card = "ID Card: $citizen_id";
						}
						else if($passport != ''){
							$id_card = "Passport: $passport";
						}
						else{
							$id_card = "No ID Card and Passport";
						}

					  $dob_th = changeToThaiDate($date_of_birth);
						$case_age = getAge($date_of_birth);
						$case_sex = getSex($sex);
					 	$txtrow = "
					 	<div class='fl-wrap-row ph20'>
					 	</div>
					 	<div class='fl-wrap-row ph20 ptxt-s14 ptxt-b $divname' data-uid='$uid' >
					 			 <div class='fl-fix pw100 pbg-blue ptxt-white px-1 fl-mid'>$uid</div>
					 			 <div class='fl-fix pw100 pbg-yellow px-1 fl-mid pbtn btn-uic-edit-dlg div-$uid'>$uic</div>
					 			 <div class='fl-fix pw200 pbg-white px-1 fl-mid'>$id_card</div>
					 			 <div class='fl-fill'></div>
					 			 <div class='fl-fix pw100 pbtn pbtn-ok px-1 fl-mid ptxt-s10 btn-uid-edit-dlg' data-uid='$uid'><i class='fa fa-user-edit fa-lg'></i>  แก้ไข | Edit</div>
					 	</div>
					 	<div class='fl-wrap-row ph20 ptxt-s12 pbg-white50'>
					 			 <div class='fl-fix pw250 '>ชื่อ-นามสกุล: <b>$fname $sname</b></div>
					 			 <div class='fl-fill'></div>
					 			 <div class='fl-fix pw200 '>เพศ: <b>$case_sex</b></div>
					 	</div>
					 	<div class='fl-wrap-row ph20 ptxt-s12 pbg-white50'>
					 			 <div class='fl-fix pw250 '>Eng Name: <b>$en_fname $en_sname</b></div>
					 			 <div class='fl-fill'></div>
					 			 <div class='fl-fix pw200 '>Tel: <b>$tel_no</b></div>
					 	</div>
					 	<div class='fl-wrap-row ph20 ptxt-s12 pbg-white50'>
					 			 <div class='fl-fix pw250 '>วันเกิด: <b>$dob_th | $date_of_birth</b></div>
					 			 <div class='fl-fill'></div>
					 			 <div class='fl-fix pw200 '>Email: <b>$email</b></div>
					 	</div>
						<div class='fl-wrap-row ph20 ptxt-s12 pbg-white50'>
								 <div class='fl-fix pw250 '>อายุ: <b>$case_age</b></div>
								 <div class='fl-fill'></div>
								 <div class='fl-fix pw200 '>Line: <b> $line_id</b></div>
						</div>
					 	<div class='fl-wrap-row ph30 '>
					 	</div>

					 	";
				 }
	  }// if
	}
	else{
	  $msg_error .= $stmt->error;
	}
	$stmt->close();

 if($txtrow != ""){
	 $txtrow_proj = "";
	 $query = "SELECT  pid,proj_id,proj_group_id, enroll_date, uid_status, clinic_id
	 FROM p_project_uid_list
	 WHERE uid=? AND uid_status IN('1', '2')
	 ORDER BY enroll_date
	 ";

	 $stmt = $mysqli->prepare($query);
	 $stmt->bind_param('s',$sUid);
	 //echo "query : $query";
	 if($stmt->execute()){
		 $stmt->bind_result($pid,$proj_id,$group_id, $enroll_date, $uid_status, $clinic_id);
		 while ($stmt->fetch()) {
			 $txtrow_proj .= "
			 <div class='fl-wrap-row ph20 ptxt-s10 p-row pbg-white50'>
						<div class='fl-fix pw100 px-1 ptxt-s12'><b>$pid</b></div>
						<div class='fl-fix pw100 px-1'>$proj_id</div>
						<div class='fl-fix pw80 px-1'>$group_id</div>
						<div class='fl-fill px-1'>$clinic_id</div>
						<div class='fl-fix pw80 px-1'>$enroll_date</div>
			 </div>
			 ";
		 }// while
		 if($txtrow_proj != ""){
			 $txtrow .= "
			 <div class='fl-wrap-row ph20 ptxt-s10 ptxt-white p-row pbg-blue'>
						<div class='fl-fix pw100 px-1 ptxt-s12'><u>PID</u></div>
						<div class='fl-fix pw100 px-1'>Project</div>
						<div class='fl-fix pw80 px-1'>Group</div>
						<div class='fl-fill px-1'>Clinic</div>
						<div class='fl-fix pw80 px-1'>Enroll</div>
			 </div>
			 $txtrow_proj
			 ";
		 }
		 else{
			 $txtrow .= "
			 <div class='fl-wrap-row fl-mid ph200 ptxt-s14 pbg-white50'>
						- No project enrollment -
			 </div>
			 ";
		 }
	 }
	 $stmt->close();
 }//$txtrow != ""

}//if($sUid != "")
else{
	$txtrow = "
	<div class='fl-wrap-row fl-fill fl-mid $divname' data-uid=''>
	   No UID data found | ไม่พบ UID ที่ค้นหา
	</div>
	";

}

echo $txtrow;


?>




<script>

$(document).ready(function(){

	$(".<? echo $divname; ?>").on("click",".btn-uic",function(){
       let divmain = $(".<? echo $divname; ?>");
			 let uid = divmain.attr('data-uid');
			 console.log("<? echo $divname; ?> uid: "+uid);

	});

});


</script>
