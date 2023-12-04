<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");
$sEmail = urldecode(getQS("e"));
$sPass=getQS("p");
$sClinic=getQS("clinic");
$sMode=getQS("u_mode");
$aRes = array();
$aRes["res"] = "0";
$sSID = getSS("s_id");
$sSessKey = getSS("sesskey");
$sToday = date("Y-m-d");

if($sMode=="login"){
	if($sEmail == "" || $sPass==""){
		echo("0");
		exit();
	}

	$sPass=encodeSingleLink($sPass);	

	include("in_db_conn.php");
	$query =" SELECT PS.s_id,s_name,ISC.section_id,s_remark,clinic_id,PS.section_id,s_name_en FROM p_staff PS
	JOIN i_staff_clinic ISC
	ON ISC.s_id = PS.s_id
	WHERE ISC.clinic_id = ? AND PS.s_email = ? AND PS.s_pwd = ? AND sc_status=1;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinic,$sEmail,$sPass);

	if($stmt->execute()){
	  $stmt->bind_result($s_id,$s_name,$section_id,$s_remark,$clinic_id,$main_section,$s_name_en);
	  while ($stmt->fetch()) {
		$_SESSION["s_id"]=$s_id;
		$_SESSION["s_name"]=$s_name;
		$_SESSION["s_name_en"]=$s_name_en;
		$_SESSION["main_section"]=$main_section;
		$_SESSION["section_id"][$section_id.""]=$section_id;
		$_SESSION["clinic_id"]=$clinic_id;
		$_SESSION["s_email"]=$sEmail;
		$_SESSION["sesskey"]=j_enc($s_id);
		$aRes["res"] = "1";
		if($section_id=="D99") $_SESSION["sysadmin"] = "1";
	  }
	}

	$sSID=getSS("s_id");

	if($sSID!=""){
		//Load Permission Module
		$query = "SELECT module_id,option_code,allow_view,allow_insert,allow_update,allow_delete,is_admin FROM i_module_permission WHERE section_id IN (SELECT section_id FROM i_staff_clinic WHERE clinic_id=? AND s_id=? AND sc_status=1)";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("ss",$sClinic,$sSID);
		if($stmt->execute()){
			$stmt->bind_result($module_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin);
			while($stmt->fetch()){
				if($allow_view=="1") $_SESSION["MODULE"][$module_id][$option_code]["view"]=$allow_view;
				if($allow_insert=="1") $_SESSION["MODULE"][$module_id][$option_code]["insert"]=$allow_insert;
				if($allow_update=="1") $_SESSION["MODULE"][$module_id][$option_code]["update"]=$allow_update;
				if($allow_delete=="1") $_SESSION["MODULE"][$module_id][$option_code]["delete"]=$allow_delete;
				if($is_admin=="1")$_SESSION["MODULE"][$module_id][$option_code]["admin"]=$is_admin;
			}
		}
		unset($_SESSION["DOC"]);
		$query = "SELECT doc_code,allow_view,allow_create,allow_edit,allow_delete FROM i_doc_section_permission WHERE section_id IN (SELECT section_id FROM i_staff_clinic WHERE clinic_id=? AND s_id=? AND sc_status=1)";
		$stmt=$mysqli->prepare($query);

		$stmt->bind_param("ss",$sClinic,$sSID);
		if($stmt->execute()){
			$stmt->bind_result($doc_code,$allow_view,$allow_create,$allow_edit,$allow_delete);
			while($stmt->fetch()){
				if($allow_view=="1" ) $_SESSION["DOC"][$doc_code]["view"]=$allow_view;
				if($allow_edit=="1" ) $_SESSION["DOC"][$doc_code]["edit"]=$allow_edit;
				if($allow_create=="1") $_SESSION["DOC"][$doc_code]["create"]=$allow_create;
				if($allow_delete=="1") $_SESSION["DOC"][$doc_code]["delete"]=$allow_delete;
			}
			/*
			if(isset($_SESSION["DOC"]["RECEIPT"]["view"])){
				//มีสิทธิ
			}
			*/
		}
	//if staff is in the room
		$query =" SELECT IRL.room_no,room_detail FROM i_room_login IRL
		LEFT JOIN i_room_list IRLIST
		ON IRLIST.room_no = IRL.room_no
		AND IRLIST.clinic_id = IRL.clinic_id
		WHERE IRL.clinic_id=? AND s_id=? AND visit_date=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sss",$sClinic,$sSID,$sToday);

		if($stmt->execute()){
		  $stmt->bind_result($room_no,$room_detail );
		  while ($stmt->fetch()) {
			$_SESSION["room_no"]=$room_no;
			$_SESSION["room_detail"]=$room_detail;
		  }
		}
	}else{
		$aRes["msg"] = "No user found";
	}
	$mysqli->close();


}else if($sMode=="logout"){

	//Exit all room
	$_GET["u_mode"] = "exit_room";
	$_GET["isecho"] = "0";
	//include("room_a.php");
	unset($_SESSION["s_id"]);
	unset($_SESSION["s_name"]);
	unset($_SESSION["s_name_en"]);
	unset($_SESSION["section_id"]);
	unset($_SESSION["clinic_id"]);
	unset($_SESSION["s_email"]);
	unset($_SESSION["sesskey"]);
	unset($_SESSION["room_no"]);
	unset($_SESSION["room_detail"]);
	unset($_SESSION["sysadmin"]);
	unset($_SESSION["projadmin"]);
	unset($_SESSION["main_section"]);
	unset($_SESSION["MODULE"]);
	unset($_SESSION["DOC"]);
	unset($_SESSION["main_section"]);
	//j_dec(urldecode(getQS("sesskey")));
	$_GET["u_mode"] = "logout";
	$aRes["res"] = "1";
	unset($_SESSION);
	//session_destroy();
}else if($sMode=="refresh_module"){
	$sClinicId = getSS("clinic_id");
	include("in_db_conn.php");
	unset($_SESSION["MODULE"]);$sMsg="";
	unset($_SESSION["DOC"]);
	$query = "SELECT module_id,option_code,allow_view,allow_insert,allow_update,allow_delete,is_admin FROM i_module_permission WHERE section_id IN (SELECT section_id FROM i_staff_clinic WHERE clinic_id=? AND s_id=? AND sc_status=1)";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicId,$sSID);
	if($stmt->execute()){
		$stmt->bind_result($module_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$is_admin);

		while($stmt->fetch()){
			if($allow_view=="1") $_SESSION["MODULE"][$module_id][$option_code]["view"]=$allow_view;
			if($allow_insert=="1")$_SESSION["MODULE"][$module_id][$option_code]["insert"]=$allow_insert;
			if($allow_update=="1")$_SESSION["MODULE"][$module_id][$option_code]["update"]=$allow_update;
			if($allow_delete=="1")$_SESSION["MODULE"][$module_id][$option_code]["delete"]=$allow_delete;
			if($is_admin=="1")$_SESSION["MODULE"][$module_id][$option_code]["admin"]=$is_admin;
		}
	}

	//Document
	$query = "SELECT doc_code,allow_view,allow_create,allow_edit,allow_delete FROM i_doc_section_permission WHERE section_id IN (SELECT section_id FROM i_staff_clinic WHERE clinic_id=? AND s_id=? AND sc_status=1)";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicId,$sSID);
	if($stmt->execute()){
		$stmt->bind_result($doc_code,$allow_view,$allow_create,$allow_edit,$allow_delete);
		while($stmt->fetch()){
			if($allow_view=="1") $_SESSION["DOC"][$doc_code]["view"]=$allow_view;
			if($allow_edit=="1") $_SESSION["DOC"][$doc_code]["edit"]=$allow_edit;
			if($allow_create=="1") $_SESSION["DOC"][$doc_code]["create"]=$allow_create;
			if($allow_delete=="1") $_SESSION["DOC"][$doc_code]["delete"]=$allow_delete;
		}
		/*
		if(isset($_SESSION["DOC"]["RECEIPT"]["view"])){
			//มีสิทธิ
		}
		*/
	}



	$mysqli->close();	
	$aRes["res"]="1";
	$aRes["msg"]=((getUserModuleList()));

}

function getUserModuleList(){
	$optT = "<input type='checkbox' class='bigcheckbox' checked='true' onclick='return false;' />";
	$optF = "<input type='checkbox' class='bigcheckbox' disabled  />";

	$sT="<div class='fl-fix h-30 fl-mid'>MODULE</div>";
	if(isset($_SESSION["MODULE"])){
		foreach ($_SESSION["MODULE"] as $module_id => $aOption) {
			foreach ($aOption as $option_code => $aMode) {
				$sT.="<div class='fl-wrap-row row-color row-hover h-30'>
					<div class='fl-fill'>$module_id</div>
					<div class='fl-fill'>$option_code</div>
					<div class='fl-fix w-80 fl-mid'>".(isset($aMode["view"])?$optT:$optF)."</div>
					<div class='fl-fix w-80 fl-mid'>".(isset($aMode["insert"])?$optT:$optF)."</div>
					<div class='fl-fix w-80 fl-mid'>".(isset($aMode["update"])?$optT:$optF)."</div>
					<div class='fl-fix w-80 fl-mid'>".(isset($aMode["delete"])?$optT:$optF)."</div>
					<div class='fl-fix w-80 fl-mid'>".(isset($aMode["admin"])?$optT:$optF)."</div>
					<div class='fl-fill'></div>
					</div>";
			}
		}		
	}else{
		$sT.="No module loaded. Please try login again.";
	}	

	$sT.="<div class='fl-fix h-30 fl-mid'>DOCUMENT</div>";
	if(isset($_SESSION["DOC"])){
		foreach ($_SESSION["DOC"] as $doc_code => $aMode) {
			$sT.="<div class='fl-wrap-row row-color row-hover  h-30'>
				<div class='fl-fill'>$doc_code</div>
				<div class='fl-fill'></div>
				<div class='fl-fix w-80 fl-mid'>".(isset($aMode["view"])?$optT:$optF)."</div>
				<div class='fl-fix w-80 fl-mid'>".(isset($aMode["create"])?$optT:$optF)."</div>
				<div class='fl-fix w-80 fl-mid'>".(isset($aMode["edit"])?$optT:$optF)."</div>
				<div class='fl-fix w-80 fl-mid'>".(isset($aMode["delete"])?$optT:$optF)."</div>
				<div class='fl-fix w-80 fl-mid'></div>
				<div class='fl-fill'></div>
				</div>";
		}		
	}else{
		$sT.="No document loaded. Please try login again.";
	}	



	return $sT;
}

$returnData = json_encode($aRes);
echo($returnData);


?>