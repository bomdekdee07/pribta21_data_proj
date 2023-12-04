<?
include("in_session.php");
include_once("in_php_function.php");

$aRes=array();
$aRes["res"] = 0; $isCont = true;
$isEcho=getQS("echo");


$sMode=getQS("u_mode");
$sQ=getQS("q");

$sSid=getSS("s_id");
$sRoom = getSS("room_no","");
if($sRoom=="") $sRoom = getQS("room_no");

$sClinicId = getSS("clinic_id");

$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=getQS("coltime");
$sHideCall=getQS("hidecall");
if($sClinicId=="") $sClinicId=getQS("clinicid");
if($sClinicId=="") $sClinicId=easy_dec(getQS("site"));
$sToday=date("Y-m-d");
$bRecLog=false;
$bLoadCurQ=false;


include("in_db_conn.php");

$sRoomList = ""; 
if($sRoom=="2") $sRoomList = "1,2";
else $sRoomList = $sRoom;

if($sQ!="" && $sColD==""){
	$sColD=$sToday;
}

if($sMode=="q_create"){
	$iQueue = getQS("q");
	$sQType=getQS("qtype");
	$sToday = date("Y-m-d");
	$sTime = date("H:i:s");
	if($sTime=="00:00:00") $sTime="00:00:01";
	//get default room for new Q
	$query ="SELECT room_no FROM i_room_list WHERE default_room=1 AND clinic_id=?";
	$aDefRoom = array(); $sDefRoom = ""; $iDefCount = 0;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$sClinicId);
	$stmt->execute();
	$stmt->bind_result($room_no);
	while ($stmt->fetch()) {
		$sDefRoom=$room_no;
	}
	if($sDefRoom==""){
		//No default Room
		$sDefRoom="1";
	}
	$query = "INSERT INTO i_queue_list(clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_type,queue_call,queue_print) 
	SELECT ?, @newqueue := (IFNULL(MAX(queue*1),0))+1,?,?,NOW(),?,1,1,0,1 FROM i_queue_list WHERE clinic_id=? AND collect_date=? AND queue_type='1' AND queue RLIKE '^[^A-Z]';";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssssss',$sClinicId,$sToday,$sTime,$sDefRoom,$sClinicId,$sToday);
	$stmt->execute();
    $result = $stmt->get_result();
    $iAffRow = $stmt->affected_rows;
    if($iAffRow > 0){
    	$query = "SELECT @newqueue";
    	$stmt = $mysqli->prepare($query);
    	$stmt->execute();
    	$stmt->bind_result($newId);
    	while ($stmt->fetch()) {
    		$iQueue = $newId;
    		$aRes["res"] = 1;
			$aRes["q"] = $iQueue;
    	}
    }else{
    	echo("Error Printing Please try again");
    	exit();
    }	
}else if($sMode=="q_create_extra" || $sMode=="q_create_inhouse" || $sMode=="q_create_anonymous"){
	$sPreFix = ""; $sType="";
	if($sMode=="q_create_extra"){ $sPreFix="L"; $sType="2";}
	if($sMode=="q_create_inhouse"){ $sPreFix="H"; $sType="1"; $_GET["uid"]="P00-00000";}
	if($sMode=="q_create_anonymous"){ $sPreFix="A"; $sType="1";$_GET["uid"]="P99-99999";}

	$iQueue = getQS("q");
	$sQType=getQS("qtype",$sType);
	$sRoom=getQS("room_no");
	$sToday = date("Y-m-d");
	$sTime = date("H:i:s");
	if($sTime=="00:00:00") $sTime="00:00:01";
	$sColD=getQS("coldate",$sToday);
	$sColT=getQS("coltime",$sTime);
	$sQPref=getQS("qprefix",$sPreFix);
	$sUid=getQS("uid");
	$condQPref=$sQPref."%";

	$query = "INSERT INTO i_queue_list(clinic_id,queue,uid,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_type,queue_call,queue_print,s_id,queue_note) SELECT ?,@newqueue := CONCAT(?,IFNULL(MAX( REPLACE(queue,?,'')*1 ),0)+1),?,?,?,NOW(),?,'1','1',0,0,?,? FROM i_queue_list WHERE clinic_id=? AND collect_date=? AND queue LIKE ?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssssssssssss',$sClinicId,$sQPref,$sQPref,$sUid,$sColD,$sColT,$sRoom,$sSid,$sQNote,$sClinicId,$sColD,$condQPref);
	$stmt->execute();
    $result = $stmt->get_result();
    $iAffRow = $stmt->affected_rows;
    if($iAffRow > 0){
    	$query = "SELECT @newqueue";
    	$stmt = $mysqli->prepare($query);
    	$stmt->execute();
    	$stmt->bind_result($newId);
    	while ($stmt->fetch()) {
    		$iQueue = $newId;
    		$aRes["res"] = 1;
			$aRes["q"] = $iQueue;
    	}
    }else{
    	$aRes["msg"] = "Error Create Queue Please try again";
    	$bRecLog=false; $bLoadCurQ=false;
    }
}else if($sMode=="q_print_list"){
	$query ="SELECT queue,collect_time FROM i_queue_list WHERE clinic_id=? AND queue_print = 1 AND collect_date=? AND queue_type='1' ORDER BY collect_time LIMIT 1";

	$aDefRoom = array(); $sDefRoom = ""; $iDefCount = 0;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sClinicId,$sToday);
	$stmt->execute();
	$stmt->bind_result($queue,$collect_time);
	$sPrintList="";
	while ($stmt->fetch()) {
		$sPrintList .= (($sPrintList=="")?"":",").$queue;
	}
	if($sPrintList==""){
		$aRes["res"] = 0;
	}else{
		$aRes["res"] = 1;
		$aRes["msg"] = $sPrintList;
		$aRes["coltime"] = $collect_time;
	}
}else if($sMode=="q_voice_call"){
	if($sQ==""){
		$aRes["res"] = 0;
		$aRes["msg"] = "No Q";
	}else{
		$query = "UPDATE i_queue_list SET queue_call=2 
		WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_status=1 AND queue_type='1'";

		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("sss",$sClinicId,$sToday,$sQ);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
			}else{
				$aRes["res"] = 0;
				$aRes["msg"] = "No Voice Call. Please try again";
			}
		}		
	}
}else if($sMode=="q_recall"){
	$aRes["res"] = 0; $isCont = true;
	if($sQ==""){
		$isCont=false;
		$aRes["msg"] = "No Q/Room Provide";
	}
	if($isCont){
		$query = "UPDATE IGNORE i_queue_list SET queue_datetime=NOW(),queue_call=1
		WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_status=1 AND room_no=? AND queue_type='1' ";

		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("ssss",$sClinicId,$sToday,$sQ,$sRoom);
		if($stmt->execute()){
			//Doesn't need to check if the row is effect.
			$aRes["res"] = 1;
			$aRes["msg"] = "";
		}else{
			$aRes["msg"] = "No Q is called. Please try again";
		}
	}
}else if($sMode=="q_call"){
	$aRes["res"] = 0; $isCont = true;
	if($sQ=="" || $sRoom==""){
		$isCont=false;
		$aRes["msg"] = "No Q/Room Provide";
	}

	if($isCont){
		//Check if the room is already occupied with other Queue
		$query = "SELECT queue FROM i_queue_list WHERE clinic_id=? AND collect_date=? AND queue_type='1' AND queue_status=2 AND room_no = ?;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sClinicId,$sToday,$sRoom);
		if($stmt->execute()){
			$stmt->bind_result($queue);
			while($stmt->fetch()){
				$isCont=false;
				if($queue==$sQ){
					//Queue is already in the room
					$aRes["msg"] = "Q ".$queue." is already in the room";
				}else{
					$aRes["msg"] = "The room is already occupied by Q :".$queue;
				}
			}
		}
	}

	if($isCont){
		//Clear previous q that is not in the room or on called but not the q that just called
		$query = "UPDATE i_queue_list SET queue_call='0',queue_datetime=NOW() WHERE clinic_id=? AND collect_date=? AND queue!=? AND queue_status=1 AND queue_call>0 AND room_no=? AND queue_type='1' ";
		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("ssss",$sClinicId,$sToday,$sQ,$sRoom);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
			}
		}

		$query = "UPDATE i_queue_list SET queue_datetime=NOW(),queue_call=1,room_no=?,s_id=?
		WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_status=1 AND queue_type='1' ";

		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("sssss",$sRoom,$sSid,$sClinicId,$sToday,$sQ);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
				$bLoadCurQ=true;
			}else{
				$aRes["msg"] = "No Q is called. Please try again";
			}
		}

		if($aRes["res"]==1){
			$aRes["msg"] = "";
		}
	}
}else if($sMode=="prepare_drug"){
	
	$query = "UPDATE i_queue_list SET queue_status=1,queue_datetime=NOW(),queue_call=0,s_id=?,prepare_drug_by=?,prepare_drug_date=NOW(),check_drug_by='',check_drug_date='0000-00-00'
	WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=?";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("ssssss",$sSid,$sSid,$sClinicId,$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
		}else{
			$aRes["msg"] = "No Q is called. Please try again";
		}
	}
	
}else if($sMode=="supply_is_check"){
	$aRes["res"] = 0; $isCont = true;
	if($sQ==""){
		$isCont=false;
		$aRes["msg"] = "No Q Provided";
	}
	if($isCont){
		$query = "UPDATE i_queue_list SET queue_status=1,queue_datetime=NOW(),queue_call=1,s_id=?,check_drug_by=?,check_drug_date=NOW()
		WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_type='1' ";
		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("sssss",$sSid,$sSid,$sClinicId,$sToday,$sQ);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
			}else{
				$aRes["msg"] = "No Q is called. Please try again";
			}
		}
	}
}else if($sMode=="cashier_call"){
	$aRes["res"] = 0; $isCont = true;
	if($sQ=="" || $sRoom==""){
		$isCont=false;
		$aRes["msg"] = "No Q/Room Provide";
	}
	if($isCont){
		$query = "UPDATE i_queue_list SET queue_status=1,queue_datetime=NOW(),queue_call=1,s_id=?
		WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_type='1' ";
		$stmt = $mysqli->prepare($query);
		//$stmt->bind_param($sParam,...$aUpdData);
		$stmt->bind_param("ssss",$sSid,$sClinicId,$sToday,$sQ);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
			}else{
				$aRes["msg"] = "No Q is called. Please try again";
			}
		}
	}
}else if($sMode=="cashier_cancel_call"){
	//This will cancel the q that has been called but not comes
	$query = "UPDATE i_queue_list SET queue_status=1,queue_call=0 ,s_id=?
	WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_type='1' ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sSid,$sClinicId,$sToday,$sQ);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
			$bLoadCurQ=true;
		}else{
			$aRes["msg"] = "No Q is called. Please try again";
		}
	}

	if($aRes["res"]==1){
		$aRes["msg"] = "";
	}
}else if($sMode=="q_ready_cancel"){
	//This will cancel the q that has been called but not comes
	$query = "UPDATE i_queue_list SET queue_status=1,queue_call=0 ,prepare_drug_by='',prepare_drug_date='0000-00-00',check_drug_by='',check_drug_date='0000-00-00',issue_drug_by='',issue_drug_date='0000-00-00',s_id=?,queue_note = CONCAT(queue_note,'[',CURTIME(),'] Cancelled Drug Preparation','\r\n')
	WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=? AND queue_type='1' ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss",$sSid,$sClinicId,$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
		}else{
			$aRes["msg"] = "No Q is cancelled. Please try again";
		}
	}

	//Cancel all drug pickup

	if($aRes["res"]==1){
		$query = "UPDATE i_stock_order SET is_pickup='0',pickup_datetime='0000-00-00',updated_datetime=NOW(),updated_by=?

		WHERE clinic_id=? AND uid=? AND collect_date=? AND collect_time=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sssss',$sSid,$sColTime,$sUid,$sColD,$sColT);
		if($stmt->execute()){

		}

	}

}else if($sMode=="q_confirm"){
	//This will confirm that q is ready to call in the room
	$query = "UPDATE i_queue_list SET queue_call=0,queue_status=2 ,s_id=?,queue_datetime=NOW()
	WHERE clinic_id=? AND collect_date=? AND queue=? AND queue_status=1 AND room_no=? AND queue_type='1' ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss",$sSid,$sClinicId,$sToday,$sQ,$sRoom);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
			$bLoadCurQ=true;
		}else{
			$aRes["msg"] = "No Q is called. Please try again";
		}
	}
}else if($sMode=="q_fwd"){
	$sFwdRoom = getQS("fwdroom");
	$sSaleId = getQS("saleid");
	$sProjId=getQS("projid");
	$sSectionName = getQS("section_name");
	$sQ=getQS("q");

	if($sSectionName == ""){
		if($sFwdRoom==""){
			$aRes["res"] = 0;
			$aRes["msg"] = "No Q Provide";
		}else{
			if($sQ!=""){
				$sSSS = "sssss";
				$aBindVal=array($sFwdRoom,$sSid);
				$query = "UPDATE i_queue_list 
				SET queue_datetime=NOW(),room_no=?,queue_status=1,queue_call=0,s_id=?,queue_type=1";

				if($sSaleId!="") {
					$query .= ",sale_opt_id=?";
					$sSSS.="s";
					$aBindVal[]=$sSaleId;
				}

				if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
					$sNote = getQS("notetoall");
					$query.=",queue_note=?";
					$sSSS.="s";
					$aBindVal[]=$sNote;
				}

				$aBindVal[]=$sClinicId;
				$aBindVal[]=$sToday;
				$aBindVal[]=$sQ;

				$query .= " WHERE clinic_id=? AND collect_date=? AND queue=?";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param($sSSS,...$aBindVal);

				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;

						$bRecLog=true;
						if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
							//Update Note
							// echo "TESTPROJECT:".$sProjId;
							$sNote = urlDecode(getQS("notetoall"));
							$query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id,data_result,lastupdate,s_id, proj_id)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),?, ? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=? 
							ON DUPLICATE KEY UPDATE data_result=VALUES(data_result),s_id=VALUES(s_id)";
							$stmt = $mysqli->prepare($query);

							$iNoteRow=0;
							$stmt->bind_param("ssssss",$sNote,$sSid, $sProjId, $sClinicId,$sToday,$sQ);
							if($stmt->execute()){
								$iNoteRow =$stmt->affected_rows;
							}
							$stmt->close();
							$query = "INSERT INTO a_log_data_result (uid,collect_date,collect_time,data_id,data_result,update_time,update_user)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=?";
							$stmt = $mysqli->prepare($query);
							$stmt->bind_param("sssss",$sNote,$sSid,$sClinicId,$sToday,$sQ);
							if($stmt->execute()){}
							
						}
					}else{
						$aRes["res"] = 0;
						$aRes["msg"] = "No Q Call. Please try again";
					}
				}
				$stmt->close();				
			}
		}
	}
	else if($sSectionName == "pharmacy"){
		if($sFwdRoom==""){
			$aRes["res"] = 0;
			$aRes["msg"] = "No Q Provide";
		}else{
			if($sQ!=""){
				$sSSS = "sssss";
				$aBindVal=array($sFwdRoom,$sSid);
				$query = "UPDATE i_queue_list 
				SET queue_datetime=NOW(),room_no=?,queue_status=1,queue_call=0,s_id=?,queue_type=1";

				if($sSaleId!="") {
					$query .= ",sale_opt_id=?";
					$sSSS.="s";
					$aBindVal[]=$sSaleId;
				}

				if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
					$sNote = getQS("notetoall");
					$query.=",queue_note=?";
					$sSSS.="s";
					$aBindVal[]=$sNote;
				}

				$aBindVal[]=$sClinicId;
				$aBindVal[]=$sToday;
				$aBindVal[]=$sQ;

				$query .= " WHERE clinic_id=? AND collect_date=? AND queue=?";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param($sSSS,...$aBindVal);

				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;

						$bRecLog=true;
						if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
							//Update Note
							// echo "TESTPROJECT:".$sProjId;
							$sNote = urlDecode(getQS("notetoall"));
							$query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id,data_result,lastupdate,s_id, proj_id)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),?, ? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=? 
							ON DUPLICATE KEY UPDATE data_result=VALUES(data_result),s_id=VALUES(s_id)";
							$stmt = $mysqli->prepare($query);

							$iNoteRow=0;
							$stmt->bind_param("ssssss",$sNote,$sSid, $sProjId, $sClinicId,$sToday,$sQ);
							if($stmt->execute()){
								$iNoteRow =$stmt->affected_rows;
							}
							$stmt->close();
							$query = "INSERT INTO a_log_data_result (uid,collect_date,collect_time,data_id,data_result,update_time,update_user)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=?";
							$stmt = $mysqli->prepare($query);
							$stmt->bind_param("sssss",$sNote,$sSid,$sClinicId,$sToday,$sQ);
							if($stmt->execute()){}
							
						}
					}else{
						$aRes["res"] = 0;
						$aRes["msg"] = "No Q Call. Please try again";
					}
				}
				$stmt->close();				
			}
		}
	}
	else if($sSectionName == "physician"){
		if($sFwdRoom==""){
			$aRes["res"] = 0;
			$aRes["msg"] = "No Q Provide";
		}else{
			if($sQ!=""){
				$sSSS = "sssss";
				$aBindVal=array($sFwdRoom,$sSid);
				$query = "UPDATE i_queue_list 
				SET queue_datetime=NOW(),room_no=?,queue_status=1,queue_call=0,s_id=?,queue_type=1";

				if($sSaleId!="") {
					$query .= ",sale_opt_id=?";
					$sSSS.="s";
					$aBindVal[]=$sSaleId;
				}

				if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
					$sNote = getQS("notetoall");
					$query.=",queue_note=?";
					$sSSS.="s";
					$aBindVal[]=$sNote;
				}

				$aBindVal[]=$sClinicId;
				$aBindVal[]=$sToday;
				$aBindVal[]=$sQ;

				$query .= " WHERE clinic_id=? AND collect_date=? AND queue=?";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param($sSSS,...$aBindVal);

				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;

						$bRecLog=true;
						if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
							//Update Note
							// echo "TESTPROJECT:".$sProjId;
							$sNote = urlDecode(getQS("notetoall"));
							$query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id,data_result,lastupdate,s_id, proj_id)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),?, ? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=? 
							ON DUPLICATE KEY UPDATE data_result=VALUES(data_result),s_id=VALUES(s_id)";
							$stmt = $mysqli->prepare($query);

							$iNoteRow=0;
							$stmt->bind_param("ssssss",$sNote,$sSid, $sProjId, $sClinicId,$sToday,$sQ);
							if($stmt->execute()){
								$iNoteRow =$stmt->affected_rows;
							}
							$stmt->close();
							$query = "INSERT INTO a_log_data_result (uid,collect_date,collect_time,data_id,data_result,update_time,update_user)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=?";
							$stmt = $mysqli->prepare($query);
							$stmt->bind_param("sssss",$sNote,$sSid,$sClinicId,$sToday,$sQ);
							if($stmt->execute()){}
							
						}
					}else{
						$aRes["res"] = 0;
						$aRes["msg"] = "No Q Call. Please try again";
					}
				}
				$stmt->close();				
			}
		}
	}
	else if($sSectionName == "cashier"){
		if($sFwdRoom==""){
			$aRes["res"] = 0;
			$aRes["msg"] = "No Q Provide";
		}else{
			if($sQ!=""){
				$sSSS = "sssss";
				$aBindVal=array($sFwdRoom,$sSid);
				$query = "UPDATE i_queue_list 
				SET queue_datetime=NOW(),room_no=?,queue_status=1,queue_call=0,s_id=?,queue_type=1";

				if($sSaleId!="") {
					$query .= ",sale_opt_id=?";
					$sSSS.="s";
					$aBindVal[]=$sSaleId;
				}

				if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
					$sNote = getQS("notetoall");
					$query.=",queue_note=?";
					$sSSS.="s";
					$aBindVal[]=$sNote;
				}

				$aBindVal[]=$sClinicId;
				$aBindVal[]=$sToday;
				$aBindVal[]=$sQ;

				$query .= " WHERE clinic_id=? AND collect_date=? AND queue=?";

				$stmt = $mysqli->prepare($query);
				$stmt->bind_param($sSSS,...$aBindVal);

				if($stmt->execute()){
					$iAffRow =$stmt->affected_rows;
					if($iAffRow > 0) {
						$aRes["res"] = 1;

						$bRecLog=true;
						if(isset($_GET["notetoall"]) || isset($_POST["notetoall"])){
							//Update Note
							// echo "TESTPROJECT:".$sProjId;
							$sNote = urlDecode(getQS("notetoall"));
							$query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id,data_result,lastupdate,s_id, proj_id)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),?, ? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=? 
							ON DUPLICATE KEY UPDATE data_result=VALUES(data_result),s_id=VALUES(s_id)";
							$stmt = $mysqli->prepare($query);

							$iNoteRow=0;
							$stmt->bind_param("ssssss",$sNote,$sSid, $sProjId, $sClinicId,$sToday,$sQ);
							if($stmt->execute()){
								$iNoteRow =$stmt->affected_rows;
							}
							$stmt->close();
							$query = "INSERT INTO a_log_data_result (uid,collect_date,collect_time,data_id,data_result,update_time,update_user)
							SELECT uid,collect_date,collect_time,'cn_patient_note',?,NOW(),? FROM i_queue_list
							WHERE clinic_id=? AND collect_date=? AND queue=?";
							$stmt = $mysqli->prepare($query);
							$stmt->bind_param("sssss",$sNote,$sSid,$sClinicId,$sToday,$sQ);
							if($stmt->execute()){}
							
						}
					}else{
						$aRes["res"] = 0;
						$aRes["msg"] = "No Q Call. Please try again";
					}
				}
				$stmt->close();				
			}
		}
	}
}else if($sMode=="q_bind"){
	$sUid = getQS("uid"); $isCont=true;
	$aRes["res"] = "0";
	$aRes["msg"] = "";

	if($sUid=="" || $sQ==""){
		$aRes["msg"] = "Q or Uid is missing";
		$isCont=false;
	}

	//check if Queue exist
	if($isCont){
		//Check if Q is already bind with other? or exist
		$isCont = false;

		$query = "SELECT uid,queue,collect_time FROM i_queue_list WHERE collect_date = ? AND ((queue=? AND uid !='') OR (uid=? AND queue!='')) AND clinic_id= ? AND queue_type='1'";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sColD,$sQ,$sUid,$sClinicId);
		$stmt->execute();
		$stmt->bind_result($uid,$queue,$collect_time);
		$iCount=0;
		while ($stmt->fetch()) {
			$iCount++;
			//Check If Q is Available
			if(($queue==$sQ && $uid=="") || ($uid==$sUid && $queue==$sQ)){
				$aRes["res"] = "1";
				$aRes["coldate"]=$sColD;
				$aRes["coltime"]=$collect_time;
				$isCont = true;
				//Q is available
				//UID is already bind to the queue
			}else if($uid==$sUid && $queue!=$sQ && $queue != ""){
				$aRes["msg"] .= "UID is already bind with Q : ".$queue;
			}else if($uid!=$sUid && $queue==$sQ && $uid != ""){
				$aRes["msg"] .= "Queue is already bind with Uid : ".$uid;
			}else{

			}
		}
		$stmt->close();
		if($iCount==0){
			//No Row Found
			$aRes["res"] = "1";
			$isCont = true;
		}
	}



	if($isCont){
		//If bind at Questionnaire Counter
		if($sSid=="") $sSid="patient";
		//Start bind Q
		$query = "UPDATE i_queue_list SET uid=?,s_id=? WHERE collect_date = ? AND queue=? AND clinic_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sssss',$sUid,$sSid,$sColD,$sQ,$sClinicId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
			}else{
				$aRes["res"] = 0;
				$aRes["msg"] = "Somethings is going wrong. Please try again.";
			}
		}
	}
}else if($sMode=="q_unbind"){
	$sUid = getQS("uid"); $isCont=true;
	$aRes["res"] = "0";
	$aRes["msg"] = "";

	if($sQ=="" || $sUid=="" || $sSid==""){
		$aRes["msg"] = "Q or Uid or Login is missing";
		$isCont=false;
	}
	if($sColD=="") $sColD = $sToday;

	//check if Queue exist and UID match is correct.
	if($isCont){
		//Check if Q is already bind with other? or exist
		$isCont = false;
		$query = "SELECT uid,collect_time FROM i_queue_list WHERE queue=? AND uid=? AND collect_date=? AND clinic_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sQ,$sUid,$sColD,$sClinicId);
		$stmt->execute();
		$stmt->bind_result($uid,$collect_time);
		$iCount=0;
		while ($stmt->fetch()) {
			//Row Found
			$isCont=true;
		}
		$stmt->close();
	}

	if($isCont){
		//Check if Q is already bind with other? or exist
		//$isCont = false;
		$isCont_dataResult = true;
		$isCont_labOrder = true;
		$isCont_stockOrder = true;

		$query = "SELECT IQL.uid,PDR.uid,PLO.uid,ISO.uid, IQL.collect_time FROM i_queue_list IQL
		LEFT JOIN p_data_result PDR
		ON PDR.uid=IQL.uid
		AND PDR.collect_date=IQL.collect_date
		AND PDR.collect_time=IQL.collect_time
		LEFT JOIN p_lab_order PLO
		ON PLO.uid=IQL.uid
		AND PLO.collect_date=IQL.collect_date
		AND PLO.collect_time=IQL.collect_time
		AND PLO.lab_order_status != 'C'
		LEFT JOIN i_stock_order ISO
		ON ISO.uid=IQL.uid
		AND ISO.collect_date=IQL.collect_date
		AND ISO.collect_time=IQL.collect_time
		AND ISO.clinic_id = IQL.clinic_id
		LEFT JOIN i_bill_detail IBD
		ON IBD.bill_q=IQL.queue
		AND IBD.bill_date=IQL.collect_date
		AND IBD.clinic_id = IQL.clinic_id
		WHERE queue=? AND IQL.uid=? AND IQL.collect_date=? AND IQL.clinic_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sQ,$sUid,$sColD,$sClinicId);
		$stmt->execute();
		$stmt->bind_result($iql,$pdr,$plo,$iso, $coltime);
		$iCount=0;
		while ($stmt->fetch()) {
			if($pdr!=""){
				// $aRes["msg"]="พบว่ามีการลงข้อมูลแล้ว กรุณาติดต่อเจ้าหน้าที่แอดมินไอที";
				$isCont_dataResult = false;
			}
			if($plo!=""){
				// $aRes["msg"]="พบว่ามีการลงข้อมูล Lab แล้ว ไม่สามารถลบได้ กรุณาติดต่อเจ้าหน้าที่แอดมินไอที";
				$isCont_labOrder = false;
			}
			if($iso!=""){
				// $aRes["msg"]="พบว่ามีการลงข้อมูลสินค้าแล้ว ไม่สามารถลบได้ กรุณาติดต่อเจ้าหน้าที่แอดมินไอที";
				$isCont_stockOrder = false;
			}
		}
		$stmt->close();
		// echo "CHECK:".$isCont_dataResult."/".$isCont_labOrder."/".$isCont_stockOrder;

		if($isCont_dataResult == false){
			// Add log
			$bind_param = "sssssss";
			$array_val = array($sUid, $sColD, $coltime, "Cancel Queue data result", "delete p_data_result from cancel Queue ".$sQ."", $sSid, date("Y-m-d")." ".date("H:i:s"));

			$query = "Insert into log_i_queue_action values(?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			// Delete p_data_result
			$bind_param = "sss";
			$array_val = array($sUid, $sColD, $coltime);

			$query = "Delete from p_data_result where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$isCont = true;
		}

		if($isCont_labOrder == false){
			// Add log
			$bind_param = "sssssss";
			$array_val = array($sUid, $sColD, $coltime, "Cancel Queue lab", "delete lab all cancel Queue ".$sQ."", $sSid, date("Y-m-d")." ".date("H:i:s"));

			$query = "Insert into log_i_queue_action values(?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$bind_param = "sss";
			$array_val = array($sUid, $sColD, $coltime);

			$query = "Delete from p_lab_order where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$query = "Delete from p_lab_order_specimen where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$query = "Delete from p_lab_result where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$query = "Delete from p_lab_order_lab_test where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$isCont = true;
		}

		if($isCont_stockOrder == false){
			// Add log
			$bind_param = "sssssss";
			$array_val = array($sUid, $sColD, $coltime, "Cancel Queue stock", "delete stock all from cancel Queue ".$sQ."", $sSid, date("Y-m-d")." ".date("H:i:s"));

			$query = "Insert into log_i_queue_action values(?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$bind_param = "sss";
			$array_val = array($sUid, $sColD, $coltime);

			$query = "Delete from i_stock_order where uid = ? and collect_date = ? and collect_time = ?;";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param($bind_param, ...$array_val);
			if($stmt->execute()){}
			$stmt->close();

			$isCont = true;
		}
	}

	if($isCont){
		//Start bind Q
		$query = "UPDATE i_queue_list SET uid='',s_id=? WHERE collect_date = ? AND queue=? AND clinic_id=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ssss',$sSid,$sColD,$sQ,$sClinicId);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
				$aRes["res"] = 1;
				$bRecLog=true;
			}else{
				$aRes["res"] = 0;
				$aRes["msg"] = "Somethings is going wrong. Please try again.";
			}
		}
	}else{

	}
}else if($sMode=="q_current"){
	$bLoadCurQ=true;
	$isEcho="0";
}else if($sMode=="q_cancel"){
	//This will cancel the q that has been called but not comes
	$query = "UPDATE i_queue_list SET queue_status='1' , queue_call=0 ,s_id=?,queue_note= CONCAT(queue_note,'[',CURTIME(),'] Cancelled Call','\r\n')
	WHERE clinic_id=? AND collect_date=? AND queue=? AND room_no=? AND queue_type='1'";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss",$sSid,$sClinicId,$sToday,$sQ,$sRoom);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
			$bLoadCurQ=true;
		}else{
			$aRes["msg"] = "No Q is cancelled. Please try again";
		}
	}

	if($aRes["res"]==1){
		$aRes["msg"] = "";
	}
}else if($sMode=="q_reprint"){
	//This will cancel the q that has been called but not comes
	$query = "UPDATE i_queue_list SET queue_print=1
	WHERE clinic_id=? AND collect_date=? AND queue=? ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinicId,$sToday,$sQ);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			$aRes["res"] = 1;
			$bRecLog=true;
		}else{
			$aRes["msg"] = "No Q is reprint or already in the list to print. Please try again";
		}
	}
}else if($sMode=="q_add_proj"){
	$sUid=getQS("uid");
	$sColD=getQS("coldate");
	$sColT=getQS("coltime");
	$sProjId=getQS("projid");

	$query = "INSERT INTO i_queue_project_list(clinic_id,uid,collect_date,collect_time,proj_id,update_by,update_datetime) 
	VALUES (?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE update_by=VALUES(update_by) AND update_datetime=NOW();";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssssss',$sClinicId,$sUid,$sColD,$sColT,$sProjId,$sSid);
	$stmt->execute();
    $iAffRow = $stmt->affected_rows;
    if($iAffRow > 0){
    	$aRes["res"]="1";
    }else{
    	echo("Error Add ProjId Please try again");
    	exit();
    }
}else if($sMode=="q_unpaid_list"){
	$query = "SELECT queue,IQL.uid,IBL.bill_id,queue_type,collect_date,collect_time,uic,fname,sname,en_fname,en_sname FROM i_bill_list IBL
		JOIN i_bill_detail IBD		ON IBD.bill_id= IBL.bill_id
			AND IBD.clinic_id=IBL.clinic_id
		LEFT JOIN i_queue_list IQL	ON IQL.clinic_id=IBD.clinic_id		
			AND IQL.collect_date=IBD.bill_date		AND IQL.queue=IBD.bill_q		
			AND IQL.queue_type=IBD.bill_q_type
		LEFT JOIN patient_info PI	ON PI.uid=IQL.uid

		WHERE IBL.clinic_id=? AND IBL.receive_by=''
		ORDER BY IBL.created_date DESC ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sClinicId);
	if($stmt->execute()){
		$stmt->bind_result($queue,$uid,$bill_id,$queue_type,$collect_date,$collect_time,$uic,$fname,$sname,$en_fname,$en_sname);
		$sHtml="";
		while($stmt->fetch()){
			$sName = ($fname!="")?$fname." ".$sname:$en_fname." ".$en_sname;
			$sHtml.="<div class='q-row unpaid-row main-q-row row-color fl-wrap-row h-30 row-hover' data-coldate='".$collect_date."' data-queue='".$queue."' data-uid='".$uid."' data-coltime='".$collect_time."' data-billid='".$bill_id."' data-istoday='0'>
				<div class='fl-fix w-5 bg-head-4'></div>
				<div class='fl-wrap-col fabtn btn-q-info fs-small'>
					<div class='fl-wrap-row h-15 lh-15'>
						<div class='fl-fill fw-b'>$bill_id</div>
						<div class='fl-fill fw-b'>$uid</div>
						<div class='fl-fill fw-b'>$collect_date (".$queue.")</div>

					</div>
					<div class='fl-fill h-15 lh-15 fs-smaller'>$sName</div>
				</div>
				
			</div>";
			
		}
		$aRes["res"]="1";
		$aRes["msg"]=$sHtml;
	}
}else if($sMode=="q_unbill_list"){
	$query = "SELECT queue,IQL.uid,IBL.bill_id,queue_type,collect_date,collect_time,uic,fname,sname,en_fname,en_sname FROM i_bill_list IBL
		JOIN i_bill_detail IBD		ON IBD.bill_id= IBL.bill_id
			AND IBD.clinic_id=IBL.clinic_id
		LEFT JOIN i_queue_list IQL	ON IQL.clinic_id=IBD.clinic_id		
			AND IQL.collect_date=IBD.bill_date		AND IQL.queue=IBD.bill_q		
			AND IQL.queue_type=IBD.bill_q_type
		LEFT JOIN patient_info PI	ON PI.uid=IQL.uid

		WHERE IBL.clinic_id=? AND IBL.receive_by=''
		ORDER BY IBL.created_date DESC ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sClinicId);
	if($stmt->execute()){
		$stmt->bind_result($queue,$uid,$bill_id,$queue_type,$collect_date,$collect_time,$uic,$fname,$sname,$en_fname,$en_sname);
		$sHtml="";
		while($stmt->fetch()){
			$sName = ($fname!="")?$fname." ".$sname:$en_fname." ".$en_sname;
			$sHtml.="<div class='q-row unpaid-row main-q-row row-color fl-wrap-row h-30 row-hover' data-coldate='".$collect_date."' data-queue='".$queue."' data-uid='".$uid."' data-coltime='".$collect_time."' data-billid='".$bill_id."' data-istoday='0'>
				<div class='fl-fix w-5 bg-head-4'></div>
				<div class='fl-wrap-col fabtn btn-q-info fs-small'>
					<div class='fl-wrap-row h-15 lh-15'>
						<div class='fl-fill fw-b'>$bill_id</div>
						<div class='fl-fill fw-b'>$uid</div>
						<div class='fl-fill fw-b'>$collect_date (".$queue.")</div>

					</div>
					<div class='fl-fill h-15 lh-15 fs-smaller'>$sName</div>
				</div>
				
			</div>";
			
		}
		$aRes["res"]="1";
		$aRes["msg"]=$sHtml;
	}
}

if($bLoadCurQ){
	$sHtml="";
	$query = "SELECT IQL.uid,queue,fname,sname,queue_status,queue_datetime,IQL.room_no,queue_status,queue_call,queue_note
	FROM i_queue_list IQL
	LEFT JOIN patient_info PI
	ON PI.uid = IQL.uid
	LEFT JOIN i_room_list IRL
	ON IRL.clinic_id=IQL.clinic_id
	AND IRL.room_no = IQL.room_no
	WHERE IQL.clinic_id=? AND queue_type='1' AND (IQL.queue_status='2' OR IQL.queue_call>0) AND IQL.room_no=? AND collect_date=? AND IRL.default_room NOT IN (1,3,9);";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('sss',$sClinicId,$sRoom,$sToday);
	if($stmt->execute()){
		$stmt->bind_result($uid,$queue,$fname,$sname,$queue_status,$queue_datetime,$room_no,$queue_status,$queue_call,$queue_note);
		while($stmt->fetch()){
			if($queue_status=="2"){
				//Queue is in the room
				$sHtml.= getCurrentQ($queue,$uid,$fname,$sname,$queue_datetime,$room_no,$queue_status,$queue_call,$queue_note);
			}else{
				//Queue is on call but not accept
				$sHtml.= getCurrentQ($queue,$uid,$fname,$sname,$queue_datetime,$room_no,$queue_status,$queue_call,$queue_note);
			}
		}
	}
	if($isEcho=="0"){
		echo($sHtml);
	}else{
		$aRes["msg"]=$sHtml;
	}
}

if($bRecLog){
	$query = "INSERT INTO i_queue_list_log(event_code,uid,clinic_id,queue,collect_date,collect_time,queue_datetime,room_no,queue_status,queue_call,queue_type,s_id,queue_note) SELECT ?,uid,clinic_id,queue,collect_date,collect_time,NOW(),room_no,queue_status,queue_call,1,s_id,queue_note FROM i_queue_list WHERE clinic_id=? AND queue=? AND collect_date=? ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ssss',$sMode,$sClinicId,$sQ,$sColD);
	$stmt->execute();
}

$mysqli->close();

if($isEcho!="0"){
	$returnData = json_encode($aRes);
	echo($returnData);
}

function getCurrentQ($queue,$uid,$fname,$sname,$queue_datetime,$room_no,$queue_status,$queue_call,$queue_note){

	$sTemp = "
	<div class='cur-q-row fl-wrap-col q-row' data-queue='$queue' data-uid='$uid'>
		<div class='fl-wrap-row'>
			<div class='fl-wrap-col w-100'>
				".
				(($queue_status!=2)?
				"<div id='btnRecall' class='fl-fix h-25 fl-mid fabtn' style='background-color:orange;color:white'>
					<i class='fa fa-bell'>Re-call</i>
				</div>
				<div id='btnRecall-loader' class='fl-fix h-25 fl-mid' style='display:none'>
					<i class='fa fa-spinner fa-spin'></i>
				</div>":"")."
				<div class='fl-fix fs-xxlarge fl-mid btn-q-info fabtn'>
					$queue
				</div>
				<div class='fl-fix fs-small h-15 fl-mid'>
					$uid
				</div>
			</div>
			<div class='fl-fill fl-auto fs-xsmall h-85 fl-mid'>
				<textarea readonly class='fill-box h-85' style='resize:none'>$queue_note</textarea>
			</div>
		</div>

		<div class='fl-fix fs-small h-15 fl-mid fw-b' style='border-top:1px solid silver;border-bottom:1px solid silver '>
			$fname $sname
		</div>
		".
		(($queue_status!=2)?
		"<div class='fl-wrap-row h-25'>
			<div id='btnCancelCallQ' class='fl-fill fl-mid fabtn fw-b' style='background-color:red;color:white'>
				Cancel
			</div>
			<div  id='btnConfirmQ' class='fl-fill fl-mid fabtn fw-b' style='background-color:green;color:white'>
				Accept
			</div>
		</div>":"")."
		".
		(($queue_status==2)?
		"<div class='fl-wrap-row h-25'>
			<div id='btnCancelQ' class='fabtn fl-fix w-80 fl-mid' style='background-color:red;color:white'>
				Cancel
			</div>
			<div id='btnForwardQ' class='fabtn fl-fill fl-mid'>
			<i class='fas fa-share'></i>
			</div>
		</div>":"")."
	</div>";
	return($sTemp);
}

?>
