<?
	/* Project UID Visit Main  */
	include("in_session.php");
	include_once("in_php_function.php");
	include_once("project_inc_uid_permission.php");

	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sSid = getSS("s_id");
	//echo "qs: $sProjid/$sGroupid/$sUID";

	$btn_uid_clinic_transfer = "";
	//echo $class_auth."<br>";

	if(isset($proj_auth['allow_admin'])){
		if($proj_auth['allow_admin'] == 1){
			$btn_uid_clinic_transfer = "<button class='ml-4 pbtn pbtn-blue ptxt-s8 btn_pid_clinic_transfer' data-uid='$sUID' data-projid='$sProjid'>Clinic Transfer</button>";
		}
	}

	$uid_status = "";
	$uid_remark = "";

	if($sUID !=""){
		include_once("in_db_conn.php");
		include_once("in_php_function_patient.php");

		// Check Condition more than 18 or less than 
		$bind_param = "s";
		$array_val = array($sUID);
		$data_check_old = "";

		$query = "SELECT distinct data_result
		from p_data_result where uid = ? and data_id = 'screen_consent'";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param($bind_param, ...$array_val);

		if($stmt->execute()){
			$result = $stmt->get_result();
			while($row = $result->fetch_assoc()){
				$data_check_old = $row["data_result"];
			}
			// echo "TEST:".$data_check_old;
		}
		$stmt->close();

		// Data detail
		$txt_row = "";
		$curAge = "";
		$thDOB ="";

		$query ="SELECT PJ.proj_id,PJ.proj_name, PUL.enroll_date, PUL.uid_status, PUL.uid, PUL.pid, P.uic,
		P.fname, P.sname, P.sex,P.blood_type, P.date_of_birth, P.citizen_id, P.tel_no, P.email, P.line_id, P.blood_type, PUL.uid_remark
		FROM p_project_uid_list PUL
		LEFT JOIN patient_info P ON (PUL.uid=P.uid )
		LEFT JOIN p_project PJ ON ( PUL.proj_id=PJ.proj_id )
		WHERE PUL.uid =? AND PUL.proj_id=?
		";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ss',$sUID, $sProjid);


		if($stmt->execute()){
			$result = $stmt->get_result();
			while($row = $result->fetch_assoc()) {
				$date_of_birth = $row["date_of_birth"];
				if($date_of_birth=="" || $date_of_birth=="0000-00-00"){

				}else{
					$curAge=getAgeDetail($date_of_birth);
					$aD = explode("-",$date_of_birth);
					$thDOB = (($aD[0]*1)+543)."-".$aD[1]."-".$aD[2];
				}

				$uid_status = $row['uid_status'];
				$uid_remark = $row['uid_remark'];

				$txt_condition_pis = "";
				if($data_check_old == "Y")
					$txt_condition_pis = "มีอายุมากกว่า 18 ปี";
				else if($data_check_old == "N")
					$txt_condition_pis = "มีอายุน้อยกว่า 18 ปี";
					
				if($row['proj_id'] == "IMACT"){
					$txt_row .= "
					<div class='fl-wrap-row ph50  fs-s fl-auto' >
						<div class='fl-wrap-col bg-mdark1 ptxt-white pw400 px-2 py-1'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>[".$row['proj_id']."] ".$row['proj_name']."</div>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14' >PID: ".$row['pid'].$btn_uid_clinic_transfer."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw150 ptxt-b ptxt-s12 v-mid bg-mdark3'>
							<div class='fl-fix ph10' ></div>
							<div class='fl-fix ph15 v-mid '>UID: ".$row['uid']."</div>
							<div class='fl-fix ph15 v-mid ' >UIC: ".$row['uic']."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>ชื่อ-นามสกุล: ".$row['fname']." ".$row['sname']."</div>
							<div class='fl-fix ph15 v-mid'>วันเกิด: ".$row['date_of_birth']." | $thDOB </div>
							<div class='fl-fix ph15 v-mid'>อายุ: $curAge | เพศกำเนิด: ".getSex($row['sex'])."</div>
						</div>
						<div class='fl-wrap-col pw200 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>เบอร์ติดต่อ: ".$row['tel_no']."</div>
							<div class='fl-fix ph15 v-mid'>อีเมล์: ".$row['email']."</div>
							<div class='fl-fix ph15 v-mid'>Line ID: ".$row['line_id']."</div>
						</div>
						<button class='ml-4 pbtn pbtn-blue ptxt-s8 btn_pdf_consent' data-uid='$sUID'>PDF Consent</button>
						
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10 bg-mdark4 border pis-pdf-cancel' data-checkpiscondition='".$data_check_old."' style='display:none;'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>PIS PDF ".$txt_condition_pis."</div>
							<div class='fl-fix ph20 fl-mid-left ptxt-b ptxt-s14'>
								<button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis' data-uid='$sUID' data-projid='$sProjid' data-sid='$sSid' data-checkPisCondition='".$data_check_old."'>FILE PIS PDF</button>
							</div>
						</div>
					</div>";
					
				}
				else if($row['proj_id'] == "CLYMAX_ICF"){
					$txt_row .= "
					<div class='fl-wrap-row ph50  fs-s fl-auto' >
						<div class='fl-wrap-col bg-mdark1 ptxt-white pw400 px-2 py-1'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>[".$row['proj_id']."] ".$row['proj_name']."</div>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14' >PID: ".$row['pid'].$btn_uid_clinic_transfer."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw150 ptxt-b ptxt-s12 v-mid bg-mdark3'>
							<div class='fl-fix ph10' ></div>
							<div class='fl-fix ph15 v-mid '>UID: ".$row['uid']."</div>
							<div class='fl-fix ph15 v-mid ' >UIC: ".$row['uic']."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>ชื่อ-นามสกุล: ".$row['fname']." ".$row['sname']."</div>
							<div class='fl-fix ph15 v-mid'>วันเกิด: ".$row['date_of_birth']." | $thDOB </div>
							<div class='fl-fix ph15 v-mid'>อายุ: $curAge | เพศกำเนิด: ".getSex($row['sex'])."</div>
							<input type='hidden' name='consent_age' id='consent_age' value='$curAge'>
						</div>
						<div class='fl-wrap-col pw200 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>เบอร์ติดต่อ: ".$row['tel_no']."</div>
							<div class='fl-fix ph15 v-mid'>อีเมล์: ".$row['email']."</div>
							<div class='fl-fix ph15 v-mid'>Line ID: ".$row['line_id']."</div>
						</div>
						<button class='ml-4 pbtn pbtn-blue ptxt-s8 btn_clymax_consent' data-uid='$sUID'>PDF Consent</button>
						
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10 bg-mdark4 border pis-pdf-cancel' data-checkpiscondition='".$data_check_old."' style='display:none;'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>PIS PDF ".$txt_condition_pis."</div>
							<div class='fl-fix ph20 fl-mid-left ptxt-b ptxt-s14'>
								<button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis' data-uid='$sUID' data-projid='$sProjid' data-sid='$sSid' data-checkPisCondition='".$data_check_old."'>FILE PIS PDF</button>
							</div>
						</div>
					</div>";
					
				}
				else{
					$txt_row .= "
					<div class='fl-wrap-row ph50  fs-s fl-auto' >
						<div class='fl-wrap-col bg-mdark1 ptxt-white pw400 px-2 py-1'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>[".$row['proj_id']."] ".$row['proj_name']."</div>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14' >PID: ".$row['pid'].$btn_uid_clinic_transfer."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw150 ptxt-b ptxt-s12 v-mid bg-mdark3'>
							<div class='fl-fix ph10' ></div>
							<div class='fl-fix ph15 v-mid '>UID: ".$row['uid']."</div>
							<div class='fl-fix ph15 v-mid ' >UIC: ".$row['uic']."</div>
							<div class='fl-fix ph10' ></div>
						</div>
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>ชื่อ-นามสกุล: ".$row['fname']." ".$row['sname']."</div>
							<div class='fl-fix ph15 v-mid'>วันเกิด: ".$row['date_of_birth']." | $thDOB </div>
							<div class='fl-fix ph15 v-mid'>อายุ: $curAge | เพศกำเนิด: ".getSex($row['sex'])."</div>
							<input type='hidden' name='consent_age' id='consent_age' value='$curAge'>
						</div>
						<div class='fl-wrap-col pw200 px-2 py-1 ptxt-s10'>
							<div class='fl-fix ph15 v-mid'>เบอร์ติดต่อ: ".$row['tel_no']."</div>
							<div class='fl-fix ph15 v-mid'>อีเมล์: ".$row['email']."</div>
							<div class='fl-fix ph15 v-mid'>Line ID: ".$row['line_id']."</div>
						</div>
						
						<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10 bg-mdark4 border pis-pdf-cancel' data-checkpiscondition='".$data_check_old."' style='display:none;'>
							<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>PIS PDF ".$txt_condition_pis."</div>
							<div class='fl-fix ph20 fl-mid-left ptxt-b ptxt-s14'>
								<button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis' data-uid='$sUID' data-projid='$sProjid' data-sid='$sSid' data-checkPisCondition='".$data_check_old."'>FILE PIS PDF</button>
							</div>
						</div>
					</div>";
				}	

				// $txt_row .= "
				// 	<div class='fl-wrap-row ph50  fs-s fl-auto' >
				// 		<div class='fl-wrap-col bg-mdark1 ptxt-white pw300 px-2 py-1'>
				// 			<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>[".$row['proj_id']."] ".$row['proj_name']."</div>
				// 			<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14' >PID: ".$row['pid'].$btn_uid_clinic_transfer."</div>
				// 			<div class='fl-fix ph10' ></div>
				// 		</div>
				// 		<div class='fl-wrap-col pw150 ptxt-b ptxt-s12 v-mid bg-mdark3'>
				// 			<div class='fl-fix ph10' ></div>
				// 			<div class='fl-fix ph15 v-mid '>UID: ".$row['uid']."</div>
				// 			<div class='fl-fix ph15 v-mid ' >UIC: ".$row['uic']."</div>
				// 			<div class='fl-fix ph10' ></div>
				// 		</div>
				// 		<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10'>
				// 			<div class='fl-fix ph15 v-mid'>ชื่อ-นามสกุล: ".$row['fname']." ".$row['sname']."</div>
				// 			<div class='fl-fix ph15 v-mid'>วันเกิด: ".$row['date_of_birth']." | $thDOB </div>
				// 			<div class='fl-fix ph15 v-mid'>อายุ: $curAge | เพศกำเนิด: ".getSex($row['sex'])."</div>
				// 		</div>
				// 		<div class='fl-wrap-col pw200 px-2 py-1 ptxt-s10'>
				// 			<div class='fl-fix ph15 v-mid'>เบอร์ติดต่อ: ".$row['tel_no']."</div>
				// 			<div class='fl-fix ph15 v-mid'>อีเมล์: ".$row['email']."</div>
				// 			<div class='fl-fix ph15 v-mid'>Line ID: ".$row['line_id']."</div>
				// 		</div>
				// 		<div class='fl-wrap-col pw250 px-2 py-1 ptxt-s10 bg-mdark4 border pis-pdf-cancel' data-checkpiscondition='".$data_check_old."' style='display:none;'>
				// 			<div class='fl-fix ph20 v-mid ptxt-b ptxt-s14'>PIS PDF ".$txt_condition_pis."</div>
				// 			<div class='fl-fix ph20 fl-mid-left ptxt-b ptxt-s14'>
				// 				<button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis' data-uid='$sUID' data-projid='$sProjid' data-sid='$sSid' data-checkPisCondition='".$data_check_old."'>FILE PIS PDF</button>
				// 			</div>
				// 		</div>
				// 	</div>";

				//  $txt_row .= "<div>".$row['pid']."</div>";
				//echo "uid: ".$row['pid'];
			}
		}
		$stmt->close();
		$mysqli->close();

		echo $txt_row;
	}
?>

<script>
	$(document).ready(function(){
		// Show bt PIS PDF
		var check_have_condition =  $(".pis-pdf").data("checkpiscondition");
		if(check_have_condition !== undefined){
			// if(check_have_condition != "")
				//$(".pis-pdf").show(); This Function cancel by BOM
		}

		$('.btn_pid_clinic_transfer').off('click');
		$('.btn_pid_clinic_transfer').on("click",function(){
			let sUid = $(this).attr('data-uid');
					let sProjid = $(this).attr('data-projid');
				// alert("uid : "+sUid);
					let sUrl = "project_inc_uid_clinic_transfer_dlg.php?uid="+sUid+"&projid="+sProjid;
					showDialog(sUrl,"Change Clinic | เปลี่ยนคลีนิก ["+sUid+"|"+sProjid+"]","250","350","",function(sResult){
							if(sResult !=''){
								$('#div_proj_uid_info').html('PID Transfer to '+sResult);
				$('#div_uid_visit_detail').html("");
							}
					},false,"");
		});
		
		$('.btn_pdf_consent').on("click",function(){
			let sUid = $(this).attr('data-uid');
			window.open("http://192.168.100.48/IMACT/imact_ihri004icfv32.php?UID="+sUid , '_blank');
		});
		
		$('.btn_clymax_consent').on("click",function(){
			//alert("Test export");
			let sUid = $(this).attr('data-uid');
			window.open("http://192.168.100.48/CLYMAX/ihri021_icf.php?UID="+sUid , '_blank');
		});
		
		// $('.btn_sdart_consent').on("click",function(){
		// 	//alert("Test export");
		// 	let sUid = $(this).attr('data-uid');
		// 	let tmp_age = $('#consent_age').val().split(" ");
		// 	
		// 	//alert("AGE : "+tmp_age[0]);
		// 	
		// 	if(tmp_age[0] < 18){
		// 		window.open("http://192.168.100.48/SDART/sdart_less18.php?UID="+sUid , '_blank');
		// 	}
		// 	if(tmp_age[0] > 17){
		// 		window.open("http://192.168.100.48/SDART/sdart_over18.php?UID="+sUid , '_blank');
		// 	}
		// });
	});
</script>
