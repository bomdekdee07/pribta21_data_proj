<?
/* Project Thumbnail list  */

include_once("in_session.php");
include_once("in_php_function.php");

include("in_db_conn.php");

$res = 0;
$msg_info = "";
$msg_err = "";

$query ="";

	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sEnroll = getQS("enroll");
	$s_id = getSS('s_id');

	$pid_format = "";
	$pid_runing_digit = 0;
  $clinic_id = isset($_SESSION["clinic_id"])? $_SESSION["clinic_id"]:"";
	$clinic_pid = "X"; // none clinic pid
	$uid_found = ""; // uid found in project
	$pid = "";


	$query ="SELECT uid
	FROM patient_info WHERE uid=?
	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUID);
		if($stmt->execute()){
			$stmt->bind_result($uid_found);
			if($stmt->fetch()){

			}
		}
	$stmt->close();

	if($uid_found == ""){
		$res = 4;
		$msg_err = "NO DATA: $sUID UID is not found.";
	}
	else{
		$uid_found = '';

			$query ="SELECT P.proj_pid_format, P.proj_pid_runing_digit, P.disable_runno_reset,
			C.clinic_pid, PUL.uid
			FROM p_project P
			LEFT JOIN p_clinic C ON C.clinic_id=?
			LEFT JOIN p_project_uid_list PUL ON (PUL.proj_id=P.proj_id and PUL.uid=?)
			WHERE P.proj_id=?
			";
			//echo "$sUID, $sProjid,$sGroupid  / $query";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss",$clinic_id,$sUID,$sProjid);
				if($stmt->execute()){
					$stmt->bind_result($pid_format, $pid_runing_digit, $pid_disable_runno_reset, $clinic_pid, $uid_found);
					if($stmt->fetch()){

					}
				}
			$stmt->close();

		//echo "$pid_format / $pid_site_id";
		  if($uid_found == ""){

				  if($pid_format != "" && $clinic_id !=""){

						$lst_data_param = array();
						$lst_data_param[] = $sUID;
						$lst_data_param[] = $sProjid;
						$lst_data_param[] = $sGroupid;
						$lst_data_param[] = $clinic_id;
						$lst_data_param[] = '1'; // uid status 1:active, 2:complete (final status), 10:cancel
						$lst_data_param[] = date('Y-m-d');
						$lst_data_param[] = isset($_SESSION["s_id"])? $_SESSION["s_id"]:"";


		         $id_prefix = "";
						 if(strpos($pid_format,"{thYr}") > -1){
							 $thYr = date('Y')+543;
							 $thYr = substr($thYr,2,4) ;
							 $pid_format = str_replace("{thYr}",$thYr,$pid_format);
						 }
						 else if(strpos($pid_format,"{enYr}") > -1){
							 $enYr = date('Y');
							 $enYr = substr($enYr,2,4) ;
							 $pid_format = str_replace("{enYr}",$enYr,$pid_format);
						 }


						 $id_prefix = str_replace("{s}",$clinic_pid,$pid_format);
						 $id_prefix = str_replace("{g}",$sGroupid,$id_prefix);
						 $id_prefix = str_replace("{r}","",$id_prefix);

						 $id_digit = $pid_runing_digit; // 00001-99999


		         if($pid_disable_runno_reset == 1){ //not reset running no after prefix changed
							 $query = "SELECT pid FROM p_project_uid_list
						  	WHERE proj_id = ?  AND clinic_id=? 
								ORDER BY pid DESC LIMIT 1
						  	";
						  	//echo "$sProjid,$sGroupid,$clinic_id / query: $query";
						  	$stmt = $mysqli->prepare($query);
						  	$stmt->bind_param('ss',$sProjid,$clinic_id);

						  	if($stmt->execute()){
						  		$result = $stmt->get_result();
						  		if($row = $result->fetch_assoc()) {
						  			if($row["pid"] != "") {
						  				$arr_pid = explode("-",$row["pid"]);
											$last_run_no = end($arr_pid);
											$last_run_no += 1;
											$last_run_no = str_pad($last_run_no, $pid_runing_digit, '0', STR_PAD_LEFT);
											$new_pid = $id_prefix.$last_run_no;
						  			}
										else{

											$new_pid = $id_prefix.str_pad(1, $pid_runing_digit, '0', STR_PAD_LEFT);
										}
						  		}//while
						  	}
						  	$stmt->close();

		            $query = "INSERT INTO p_project_uid_list (pid, uid, proj_id, proj_group_id,clinic_id, uid_status, enroll_date, create_date, create_by )
		 					   VALUES('$new_pid',?,?,?,?,?,?,now(),?)
		 					 ";
								$stmt = $mysqli->prepare($query);
							 	$stmt->bind_param('sssssss',...$lst_data_param);
							 	if($stmt->execute()){
									$affect_row = $stmt->affected_rows;
									if($affect_row > 0){
										$pid = $new_pid;
									}
								}
								else{
									$res=0;
									$msg_err .= $stmt->error;
								}
								$stmt->close();

						 }
						 else{ //reset running no after prefix changed
							 $where_substr_pos_end = strlen($id_prefix);
							 $substr_pos_begin = 1+$where_substr_pos_end;

							 $inQuery = "INSERT INTO p_project_uid_list (pid, uid, proj_id, proj_group_id,clinic_id, uid_status, enroll_date, create_date, create_by )
							 SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(pid) IS NULL,0,MAX(pid)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
							  , ?,?,?,?,?,?, now(),?
							 	FROM p_project_uid_list WHERE SUBSTRING(pid,1,$where_substr_pos_end) = '$id_prefix' ;
							 ";
							// echo "$inQuery";
					//print_r($lst_data_param);
							 	$stmt = $mysqli->prepare($inQuery);
							 	$stmt->bind_param('sssssss',...$lst_data_param);

							 	if($stmt->execute()){
							 		$inQuery = "SELECT @keyid;";
							 		$stmt = $mysqli->prepare($inQuery.";");
							 		$stmt->bind_result($pid);
							 		if($stmt->execute()){
							 			if($stmt->fetch()){
										 $lst_data_param['pid'] = $pid;

							 			}
							 		}
							 	}
							 	else{
									$res=0;
							 		$msg_err .= $stmt->error;
							 	}
							 	$stmt->close();
						 }



		          if($pid !== NULL && $pid != ''){
								$res = 1;
								$rtn["pid"] = $pid;
								$msg_info = "Create PID: $pid";

		            $msgLog = "$sUID Enroll $sProjid [pid: $pid]";
								$query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
								VALUES(?, ?)";
								//  error_log("addtolog: $s_id,$msgInfo / $query");
								$stmt_log = $mysqli->prepare($query);
								$stmt_log->bind_param('ss',$s_id,$msgLog);
								if($stmt_log->execute()){
								}
								else{
									$res=0;
									$msg_err=$stmt_log->error;
									error_log($stmt_log->error);
								}
								$stmt_log->close();
							}
					}
					else{
						$res=3;
						$msg_info="Data is not completed. Clinic id is missing.";
					}
			}
			else{
				$res=2;
				$msg_info="UID: $sUID is already found in $sProjid.";
			}
	}


	if($sEnroll == '1'){
		$_GET['u_mode'] = "create_schedule_visit";
		$_GET['no_echo'] = "1";
	//	$_GET['no_close'] = '1';
		include('project_a_visit.php');
	}



 $mysqli->close();






  $rtn["pid"] = $pid;
  $rtn["res"] = $res;
	$rtn["msg_info"] = $msg_info;
	$rtn["msg_err"] = $msg_err;

	$returnData = json_encode($rtn);
  echo $returnData;

?>
