<?
/* Project Thumbnail list  */

include("in_session.php");
include_once("in_php_function.php");


$uMode = getQS("u_mode");
$s_id = getQS("s_id");
$clinic_id = getQS("clinic_id");

if($s_id == ""){
   if(isset($_SESSION["s_id"])){
     $s_id =$_SESSION["s_id"];
   }
}

if($clinic_id == ""){
   if(isset($_SESSION["clinic_id"])){
     $clinic_id =$_SESSION["clinic_id"];
   }
}

include("in_db_conn.php");

$res = 0;
$query_add ="";
$msg_error = "";

if($uMode == "check_uid_data"){
	$aLst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
	$txt_row = "";
	$lst_data_seach = array();
	if($aLst_data !=""){
		 $sPrepare = "";
		 $lst_data_param = array();
     foreach($aLst_data as $key=>$value){
			 $sPrepare .= "s";
			 $query_add .= " $key=? AND";
			 $lst_data_param[] = $value;
		 }

		 $query_add_patient_info = ($query_add !="")?substr($query_add,0,strlen($query_add)-3):"" ;


	   if(strpos($query_add_patient_info,"citizen_id") > 0){
			 //$query_add_patient_info = "($query_add_patient_info) OR citizen_id=? ";
			 $query_add_patient_info = "($query_add_patient_info) OR REPLACE(citizen_id, '-', '')=? ";

			 $sPrepare .= "s";
			 $lst_data_param[] = $aLst_data['citizen_id'];
		 }

		 if(strpos($query_add_patient_info,"tel_no") > 0){
			$query_add_patient_info = "($query_add_patient_info) OR REPLACE(tel_no, '-', '')=REPLACE(?, '-', '') ";

			$sPrepare .= "s";
			$lst_data_param[] = $aLst_data['tel_no'];
		}

/*
		 $query_add_basic_reg = $query_add_patient_info ;


     // translate patient_info to query mapping with basic_reg
     $query_add_basic_reg = str_replace("uid=","UG.uid=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("uic=","UG.uic=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("tel_no","contact",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("date_of_birth=","dob=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("en_fname=","fname=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("en_sname=","sname=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("citizen_id","national_id",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("dob=","UG.dob=",$query_add_basic_reg);

		 $query_add_basic_reg = str_replace("id_address=","address=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("id_zone=","district=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("id_district=","district=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("id_province=","province=",$query_add_basic_reg);
		 $query_add_basic_reg = str_replace("id_postal_code=","postal_code=",$query_add_basic_reg);

		 $query = "SELECT uid, BR.uic, CONCAT(fname, ' ', sname) as name, national_id as citizen_id,  dob, contact as tel_no
     FROM uic_gen UG
	   LEFT JOIN basic_reg as BR ON UG.uic=BR.uic
		 WHERE $query_add_basic_reg
		 ";
		// echo "query: $query";
		 $stmt = $mysqli->prepare($query);
		 $stmt->bind_param($sPrepare,...$lst_data_param);

		 if($stmt->execute()){
			 $result = $stmt->get_result();
			 while($row = $result->fetch_assoc()) {
					 if(!isset($lst_data_seach[$row["uid"]])){
						 $lst_data_seach[$row["uid"]] = array();
					 }
					 $lst_data_seach[$row["uid"]]["uic"] = $row["uic"];
					 $lst_data_seach[$row["uid"]]["name"] = $row["name"];
					 $lst_data_seach[$row["uid"]]["dob"] = $row["dob"];
					 $lst_data_seach[$row["uid"]]["citizen_id"] = $row["citizen_id"];
					 $lst_data_seach[$row["uid"]]["tel_no"] = $row["tel_no"];
					//	echo "data1: ".$row["name"]." ".$row["uid"];
			 }//while
		 }
		 $stmt->close();
*/
		 //print_r($lst_data_param);
		 $query = "SELECT uid, uic, CONCAT(fname, ' ', sname) as name, citizen_id, date_of_birth as dob, tel_no
		 FROM patient_info
		 WHERE $query_add_patient_info
		 ";
		 //echo "query: $query";
		 $stmt = $mysqli->prepare($query);
		 $stmt->bind_param($sPrepare,...$lst_data_param);

		 if($stmt->execute()){
			 $result = $stmt->get_result();
			 while($row = $result->fetch_assoc()) {
				   if(!isset($lst_data_seach[$row["uid"]])){
						 $lst_data_seach[$row["uid"]] = array();
					 }
					 $lst_data_seach[$row["uid"]]["uic"] = $row["uic"];
					 $lst_data_seach[$row["uid"]]["name"] = $row["name"];
					 $lst_data_seach[$row["uid"]]["dob"] = $row["dob"];
					 $lst_data_seach[$row["uid"]]["citizen_id"] = $row["citizen_id"];
					 $lst_data_seach[$row["uid"]]["tel_no"] = $row["tel_no"];
          // echo "data2: ".$row["name"]." ".$row["dob"];
			 }//while
		 }
		 $stmt->close();


		 if(count($lst_data_seach) > 0){
			 ksort($lst_data_seach);
		 }

     foreach($lst_data_seach as $key=>$value){
        $txt_row.="
				<div class='fl-wrap-row fl-fill h-xs p-row uid-row' data-uid='$key' data-uic='".$value['uic']."'>
				  <div class='fl-fix px-1 w-s p-btn enroll'><i class='fas fa-user-check '></i></div>
					<div class='fl-fix px-1 w-m colid'>".$key."</div>
					<div class='fl-fix px-1 w-m colid'>".$value['uic']."</div>
					<div class='fl-fix px-1 fl-fill'>".$value['name']."</div>
					<div class='fl-fix px-1 w-l'>".$value['tel_no']."</div>
					<div class='fl-fix px-1 w-l'>".$value['citizen_id']."</div>
					<div class='fl-fix px-1 w-l'>".$value['dob']."</div>
					<div class='fl-fix px-1 pw20 edit'><i class='fas fa-user-edit'></i></div>
				</div>
				";
			 //echo $key." -- ".$value['uic'];
		 }//foreach
    if($txt_row == "")
		$txt_row = "<div class='fl-wrap-row fl-fill fl-mid'>No data found.</div>";


    $rtn["txtrow"] = $txt_row;
    $res = 1;
	}
}
else if($uMode == "enroll_new_uid"){
	$aLst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];



		$query ="UPDATE p_project_uid_list SET uid_remark=?
		WHERE uid=? AND proj_id=? AND proj_group_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sProjNote, $sUID, $sProjid,$sGroupid );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) $res = 1;
			}
}
else if($uMode == "enroll_uid_to_project"){
	$sUID = getQS("uid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");

		$query ="UPDATE p_project_uid_list SET uid_remark=?
		WHERE uid=? AND proj_id=? AND proj_group_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss",$sProjNote, $sUID, $sProjid,$sGroupid );
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0) $res = 1;
			}
}
else if($uMode == "create_anonymous_uid"){
	$new_uid = create_anonymous_uid();
	if($new_uid != "") $res = 1;
	$rtn['uid'] = $new_uid;
}

else if($uMode == "create_custom_pid"){
	$sUID = getQS("uid");
	$sCustomPID = getQS("pid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");

  $msg_error = "";
	$found_uid = ""; // check existing uid
	$found_pid = ""; // check existing pid

     //check existing PID / UID
			$query = "SELECT uid, pid FROM p_project_uid_list
			WHERE (pid=? OR uid=?) AND proj_id=? ";
			//echo "query: $query";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('sss', $sCustomPID, $sUID, $sProjid);

			if($stmt->execute()){
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc()) {
						$msg_error .= " พบ PID: ".$row['pid']. " นี้จาก UID: ".$row['uid'];
				}//while
			}
			$stmt->close();

	if($msg_error ==""){
		if($sUID != ""){
			$found_uid = "";
			$query = "SELECT uid FROM patient_info WHERE uid=?";
			//echo "query: $query";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('s',  $sUID);

			if($stmt->execute()){
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc()) {
						$found_uid = $row['uid'];
				}//while
			}
			$stmt->close();
			if($found_uid != $sUID){
				$msg_error .= " ไม่พบ UID: ".$sUID. " ในระบบ";
			}
		}
		else{ // make anonymous uid
      $sUID = create_anonymous_uid();
		}


   if($msg_error ==""){

		 $query = "INSERT INTO p_project_uid_list
		 (uid, pid, proj_id, proj_group_id, screen_date, enroll_date, uid_status, clinic_id, create_date, create_by)
		 VALUES(?,?,?,?, now(), now(), '1', ?, now(), ?)";
		 $stmt = $mysqli->prepare($query);
		 $stmt->bind_param("ssssss",$sUID, $sCustomPID, $sProjid,$sGroupid,$clinic_id, $s_id );
			 if($stmt->execute()){
				 $affect_row = $stmt->affected_rows;
				 if($affect_row > 0) $res = 1;
			 }
    $stmt->close();
	 }
	}

  $rtn['uid'] = $sUID;


} 


  //if(isset($stmt)) $stmt->close();

  $mysqli->close();

  $rtn["res"] = $res;
	$rtn["msg_error"] = $msg_error;

	$returnData = json_encode($rtn);
  echo $returnData;



	function create_anonymous_uid(){
	  global $mysqli; // db
	  global $msg_error;

		$new_uid = "";
		$cur_year =  (new DateTime())->format('y');
		$cur_month =  (new DateTime())->format('m');
		$id_prefix = "AN".$cur_year.$cur_month."-" ;

		$id_digit = 6; // 00001-99999
		$where_substr_pos_end = strlen($id_prefix);
		$substr_pos_begin = 1+$where_substr_pos_end;

		$inQuery = "INSERT INTO patient_info (uid, uic, fname, en_fname)
		SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(uid) IS NULL,0,MAX(uid)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
		 ,'Anonymous','', ''
			FROM patient_info WHERE SUBSTRING(uid,1,$where_substr_pos_end) = '$id_prefix' ;
		";

		$stmt = $mysqli->prepare($inQuery);

		if($stmt->execute()){
			$inQuery = "SELECT @keyid;";
			$stmt = $mysqli->prepare($inQuery.";");
			$stmt->bind_result($new_uid);
			if($stmt->execute()){
				if($stmt->fetch()){
		     $res = 1;
				}
			}
		}
		else{
			$msg_info .= $stmt->error;

		}
		$stmt->close();
	  return $new_uid;
	}

?>
