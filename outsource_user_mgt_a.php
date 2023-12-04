<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");

$u_mode = getQS('u_mode');
$sProjid = getQS("projid");
$s_id = getSS("s_id");
$clinic_id = getSS("clinic_id");
$PROJ_SECTION_ID = 'OS1';

$flag_auth=1;

$res = 0;
$msg_error = "";
$msg_info = "";
$returnData = "";



if($flag_auth != 0){ // valid user session

include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

//echo "umode : $u_mode";

if($u_mode == "select_list"){ // form_data_update
  $txtsearch = getQS('txtsearch');
	$txtsearch = "%$txtsearch%";
	$txtrow = ""; $row_amt = 0;

	$query ="SELECT S.s_id, S.s_name, S.s_remark, SA.allow_view, SA.allow_data, SA.allow_data_log
	FROM p_staff_auth SA
	JOIN p_staff S ON(S.s_id = SA.s_id)
	WHERE SA.s_id like 'OS%' AND SA.proj_id =? AND CONCAT(S.s_id,',',S.s_name) LIKE ?
	ORDER BY SA.s_id ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sProjid,$txtsearch);
//echo "$sID, $sClinicID, $sModuleid, $sOptioncode / $query";
	if($stmt->execute()){
		$stmt->bind_result($s_id, $s_name, $s_remark, $allow_view, $allow_data, $allow_data_log);
		while($stmt->fetch()) {
			$txtrow .= addRowList($s_id, $s_name, $s_remark, $allow_view, $allow_data, $allow_data_log);
			$row_amt++;
		}//while
	}
	$rtn['txtrow'] = $txtrow;
	$rtn['row_amt'] = $row_amt;

}
else if($u_mode == "update_row"){
  $sID = getQS("sid");
	$sInfo = getQS("s_info");
	$sAllow = getQS("s_allow");
	$txtrow = "";

	if($sInfo != ''){
		$sInfo = explode(':', $sInfo); //0:name, 1:remark
	}


  if($sID != ''){ // update
		$query = "UPDATE p_staff SET s_name=?, s_remark=?
		WHERE s_id=?";
	  $stmt = $mysqli->prepare($query);
	  $stmt->bind_param("sss", $sInfo[0], $sInfo[1], $sID);
	  if($stmt->execute()){
	    $affect_row = $stmt->affected_rows;
	    if($affect_row > 0){
	      $res = 1;
	    }
	  }
		else{
			error_log($stmt->error);
		}
		$stmt->close();
		if($res) addToLog("update outsource $sID [".$sInfo[0]."|".$sInfo[1]."]", $s_id);
	}
	else{ // insert
		$id_prefix = "OS".(new DateTime())->format('y'); // prefix & current year eg IH20
		$id_digit = 4; // 00001-99999
		$substr_pos_begin = 1+strlen($id_prefix);
		$where_substr_pos_end = strlen($id_prefix);

		$query = "INSERT INTO p_staff (s_id, s_name, s_remark)
		SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(s_id) IS NULL,0,MAX(s_id)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
		,?,? FROM p_staff WHERE SUBSTRING(s_id,1,$where_substr_pos_end) = '$id_prefix'";

	  $stmt = $mysqli->prepare($query);
	  $stmt->bind_param("ss", $sInfo[0],$sInfo[1]);
	  if($stmt->execute()){
	    $affect_row = $stmt->affected_rows;
	    if($affect_row > 0){
	      $res = 1;
	      $inQuery = "SELECT @keyid;";
	      $stmt = $mysqli->prepare($inQuery);
	      $stmt->bind_result($sID);
	        if($stmt->execute()){
	          if($stmt->fetch()){

	          }
	        }
	    }
	  }
		else{
			error_log($stmt->error);
		}
    $stmt->close();

		if($res) addToLog("add outsource $sID [".$sInfo[0]."]", $s_id);

		if($sID != ""){ // update email (user id to login)
			$sPwd = encodeSingleLink($sID);
			$sEmail = $sID."@ihri.org";
			$query = "UPDATE p_staff SET s_email=?, s_pwd=?
			WHERE s_id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss",$sEmail,$sPwd , $sID);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;

				}
			}
			else{
				error_log($stmt->error);
			}
			$stmt->close();


			$query = "INSERT i_staff_clinic (s_id, section_id, clinic_id, sc_status, create_date)
			VALUES(?,?,?,1,now())";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss",$sID,$PROJ_SECTION_ID, $clinic_id);
			if($stmt->execute()){
				$affect_row = $stmt->affected_rows;
				if($affect_row > 0){
					$res = 1;
				}
			}
			else{
				error_log($stmt->error);
			}
		}
  }// end insert


	if($sAllow != '' && $sID != ''){
		$sAllow = explode(':', $sAllow); //0:allow_view, 1:allow_data, 2:allow_data_log
		$query = "INSERT p_staff_auth (s_id, proj_id, allow_view, allow_data, allow_data_log) VALUES (?,?,?,?,?)
		ON  DUPLICATE KEY UPDATE allow_view=VALUES(allow_view), allow_data=VALUES(allow_data), allow_data_log=VALUES(allow_data_log)
		";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssss",$sID, $sProjid, $sAllow[0],  $sAllow[1],  $sAllow[2]);
		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			if($affect_row > 0){
				$res = 1;
			}
		}
		else{
			error_log($stmt->error);
		}
		$stmt->close();
		if($res) addToLog("update outsource auth $sID [view:".$sAllow[0]."|data:".$sAllow[1]."|log:".$sAllow[2]."]", $s_id);
	}

	if($res == 1){
		$query ="SELECT S.s_id, S.s_name, S.s_remark, SA.allow_view, SA.allow_data, SA.allow_data_log
		FROM p_staff_auth SA
		JOIN p_staff S ON(S.s_id = SA.s_id)
		WHERE SA.proj_id =? AND S.s_id= ?
		ORDER BY SA.s_id ";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ss',$sProjid,$sID);
	//echo "$sID, $sClinicID, $sModuleid, $sOptioncode / $query";
		if($stmt->execute()){
			$stmt->bind_result($s_id, $s_name, $s_remark, $allow_view, $allow_data, $allow_data_log);
			if($stmt->fetch()) {
				$txtrow .= addRowList($s_id, $s_name, $s_remark, $allow_view, $allow_data, $allow_data_log);
			}//while
		}
	}


  $rtn['txtrow'] = $txtrow;
	$rtn['res'] = $res;

}


$mysqli->close();

}//$flag_auth != 0

 // return object
 $rtn['res'] = $res;
 $rtn['mode'] = $u_mode;
 $rtn['msg_error'] = $msg_error;
 $rtn['msg_info'] = $msg_info;

 $rtn['flag_auth'] = $flag_auth;



 // change to javascript readable form
 $returnData = json_encode($rtn);
 echo $returnData;


 function addRowList($s_id, $s_name, $s_remark, $allow_view, $allow_data, $allow_data_log){
	 $data_allow = "$allow_view:$allow_data:$allow_data_log";
	 $allow_view = ($allow_view == "1")?"<i class='fa fa-check fa-lg'></i>" : "";
	 $allow_data = ($allow_data == "1")?"<i class='fa fa-check fa-lg'></i>" : "";
	 $allow_data_log = ($allow_data_log == "1")?"<i class='fa fa-check fa-lg'></i>" : "";

   $txtrow = "<div class='fl-wrap-row ph50 p-row-green ptxt-s10 div-os-row' data-sid='$s_id' data-allow='$data_allow'>";
	 $txtrow .= "<div class='fl-fix fl-mid pw50 pbtn btn-os-edit' alt='edit'><i class='fa fa-edit fa-2x'></i></div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw80 ptxt-b' >$s_id</div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw300 os-name' >$s_name</div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw80' os-chk>$allow_view</div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw80' os-chk>$allow_data</div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw80' os-chk>$allow_data_log</div>";
	 $txtrow .= "<div class='fl-fill fl-mid os-remark'>$s_remark</div>";
	 $txtrow .= "<div class='fl-fix fl-mid pw30 pbtn btn-os-pwd'><i class='fa fa-key fa-lg'></i></div>";
	 $txtrow .="</div>";

	 return $txtrow;
 }
