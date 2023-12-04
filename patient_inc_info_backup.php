<?
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate =getQS("coldate");
$sColTime=urldecode(getQS("coltime"));
$sNextForm=getQS("next_form_id");

$sQS = "?uid=".$sUid."&coldate=".$sColDate."&coltime=".$sColTime;
$sObjProp = " data-uid='".$sUid."' data-coldate='".$sColDate."' data-coltime='".$sColTime."'";
$jsHtml ="var sAllForm=\"".$sNextForm."\";";

include("in_db_conn.php");

$query ="SELECT uid,uic,fname,sname,en_fname,en_sname,nickname,sex,gender,date_of_birth,nation, 
citizen_id,	passport_id,id_address,id_district,id_province,id_zone,id_postal_code,use_id_address,address, district,province,zone,postal_code,country_other,tel_no,email,blood_type,line_id, em_name_1,em_relation_1,em_phone_1,em_name_2,em_relation_2,em_phone_2,religion FROM patient_info WHERE uid=?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);

$aPInfo = array(); $aPOdata=array();
if($stmt->execute()){
  $stmt->bind_result($uid,$uic,$fname,$sname,$en_fname,$en_sname,$nickname,$sex,$gender,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$blood_type,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$religion);
  while ($stmt->fetch()) {
  		$aPInfo[$uid]["uid"] = $uid;
	  	$aPInfo[$uid]["uic"] = $uic;
	  	$aPInfo[$uid]["fname"] = $fname;
	  	$aPInfo[$uid]["lname"] = $sname;
	  	$aPInfo[$uid]["nname"] = $nickname;
	  	$aPInfo[$uid]["gender"] = $gender;
	  	$aPInfo[$uid]["sex"] = $sex;
	  	$aPInfo[$uid]["dob"] = $date_of_birth;
	  	$aPInfo[$uid]["nation"] = $nation;
	  	$aPInfo[$uid]["id"] = $citizen_id;
	  	$aPInfo[$uid]["passport"] = $passport_id;
	  	$aPInfo[$uid]["id_address"] = $id_address;
	  	$aPInfo[$uid]["id_district"] = $id_district;
	  	$aPInfo[$uid]["id_province"] = $id_province;
	  	$aPInfo[$uid]["id_zone"] = $id_zone;
	  	$aPInfo[$uid]["id_zip"] = $id_postal_code;
	  	$aPInfo[$uid]["use_id_address"] = $use_id_address;
	  	$aPInfo[$uid]["address"] = $address;
	  	$aPInfo[$uid]["district"] = $district;
	  	$aPInfo[$uid]["province"] = $province;
	  	$aPInfo[$uid]["zone"] = $zone;
	  	$aPInfo[$uid]["zip"] = $postal_code;
	  	$aPInfo[$uid]["country"] = $country_other;
	  	$aPInfo[$uid]["phone"] = $tel_no;
	  	$aPInfo[$uid]["email"] = $email;
	  	$aPInfo[$uid]["line_id"] = $line_id;
	  	$aPInfo[$uid]["em_name_1"] = $em_name_1;
	  	$aPInfo[$uid]["em_relation_1"] = $em_relation_1;
	  	$aPInfo[$uid]["em_phone_1"] = $em_phone_1;
	  	$aPInfo[$uid]["em_name_2"] = $em_name_2;
	  	$aPInfo[$uid]["em_relation_2"] = $em_relation_2;
	  	$aPInfo[$uid]["em_phone_2"] = $em_phone_2;
	  	$aPInfo[$uid]["religion"] = $religion;


	  	$aPInfo[$uid]["blood"] = (($blood_type=="0")?"":$blood_type);



  		$aPOdata[$uid]["uid"] = $uid;
	  	$aPOdata[$uid]["uic"] = $uic;
	  	$aPOdata[$uid]["fname"] = $fname;
	  	$aPOdata[$uid]["lname"] = $sname;
	  	$aPOdata[$uid]["nname"] = $nickname;
	  	$aPOdata[$uid]["gender"] = $gender;
	  	$aPOdata[$uid]["sex"] = $sex;
	  	$aPOdata[$uid]["dob"] = $date_of_birth;
	  	$aPOdata[$uid]["nation"] = $nation;
	  	$aPOdata[$uid]["id"] = $citizen_id;
	  	$aPOdata[$uid]["passport"] = $passport_id;
	  	$aPOdata[$uid]["id_address"] = $id_address;
	  	$aPOdata[$uid]["id_district"] = $id_district;
	  	$aPOdata[$uid]["id_province"] = $id_province;
	  	$aPOdata[$uid]["id_zone"] = $id_zone;
	  	$aPOdata[$uid]["id_zip"] = $id_postal_code;

	  	$aPOdata[$uid]["use_id_address"] = $use_id_address;

	  	$aPOdata[$uid]["address"] = $address;
	  	$aPOdata[$uid]["district"] = $district;
	  	$aPOdata[$uid]["province"] = $province;
	  	$aPOdata[$uid]["zone"] = $zone;
	  	$aPOdata[$uid]["zip"] = $postal_code;

	  	$aPOdata[$uid]["country"] = $country_other;
	  	$aPOdata[$uid]["phone"] = $tel_no;
	  	$aPOdata[$uid]["email"] = $email;
	  	$aPOdata[$uid]["line_id"] = $line_id;

	  	$aPOdata[$uid]["em_name_1"] = $em_name_1;
	  	$aPOdata[$uid]["em_relation_1"] = $em_relation_1;
	  	$aPOdata[$uid]["em_phone_1"] = $em_phone_1;
	  	$aPOdata[$uid]["em_name_2"] = $em_name_2;
	  	$aPOdata[$uid]["em_relation_2"] = $em_relation_2;
	  	$aPOdata[$uid]["em_phone_2"] = $em_phone_2;
	  	$aPOdata[$uid]["religion"] = $religion;
	  	$aPOdata[$uid]["blood"] = (($blood_type=="0")?"":$blood_type);

  }
}


$query =" SELECT uid,UG.uic,flfn,flln,date_o_b,mon_o_b,y_o_b,fname,sname,contact,email,line_id,gender,nation,province,district,national_id,address FROM uic_gen UG
LEFT JOIN basic_reg BR
ON BR.uic = UG.uic
WHERE uid=?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);


if($stmt->execute()){
	$stmt->bind_result($uid,$uic,$flfn,$flln,$date_o_b,$mon_o_b,$y_o_b,$fname,$sname,$contact,$email,$line_id,$gender,$nation,$province,$district,$national_id,$address);
	while ($stmt->fetch()) {
	  	$aPInfo[$uid]["uid"] = $uid;
	  	$aPInfo[$uid]["uic"] = $uic;

	  	if(isset($aPInfo[$uid]["fname"]))
		  $aPInfo[$uid]["fname"] = (!isEmpty($aPInfo[$uid]["fname"])?$aPInfo[$uid]["fname"]:$fname);
		else $aPInfo[$uid]["fname"] = $fname;
	  	if(isset($aPInfo[$uid]["lname"]))
		  $aPInfo[$uid]["lname"] = (!isEmpty($aPInfo[$uid]["lname"])?$aPInfo[$uid]["lname"]:$sname);
		else $aPInfo[$uid]["lname"] = $sname;

	  	$sCurGender = "";

	  	if($gender=="1") $sCurGender="2";
	  	else if($gender=="2") $sCurGender="3";
	  	else if($gender=="3") $sCurGender="4";
	  	else if($gender=="4") $sCurGender="4";
	  	else if($gender=="5") $sCurGender="5";

	  	if($gender==3 || $gender==4) $gender=1;
	  	else if( $gender==5) $gender=2;


	  	if(isset($aPInfo[$uid]["sex"]))
		  $aPInfo[$uid]["sex"] = (!isEmpty($aPInfo[$uid]["sex"])?$aPInfo[$uid]["sex"]:$gender);
		else $aPInfo[$uid]["sex"] = $gender;
	  	if(isset($aPInfo[$uid]["gender"]))
		  $aPInfo[$uid]["gender"] = (!isEmpty($aPInfo[$uid]["gender"])?$aPInfo[$uid]["gender"]:$sCurGender);
		else $aPInfo[$uid]["gender"] = $sCurGender;

	  	$thYear = (($y_o_b > 60)?"24":"25");
	  	$sDob = $thYear.str_pad($y_o_b,2,"0",STR_PAD_LEFT)."-".str_pad($mon_o_b,2,"0",STR_PAD_LEFT )."-".str_pad($date_o_b,2,"0",STR_PAD_LEFT );

	  	if(isset($aPInfo[$uid]["dob"]))
		  $aPInfo[$uid]["dob"] = (!isEmpty($aPInfo[$uid]["dob"])?$aPInfo[$uid]["dob"]:$sDob);
		else $aPInfo[$uid]["dob"] = $sDob;



	  	$sNation = "";
	  	if($nation=="2") $sNation = "พม่า";
	  	else if($nation=="3") $sNation = "ลาว";
	  	else if($nation=="4") $sNation = "กัมพูชา";

	  	if($nation!="1") $nation = 2;

	  	if(isset($aPInfo[$uid]["nation"]))
		  $aPInfo[$uid]["nation"] = (!isEmpty($aPInfo[$uid]["nation"])?$aPInfo[$uid]["nation"]:$nation);
		else $aPInfo[$uid]["nation"] = $nation;

	  	if(isset($aPInfo[$uid]["country"]))
		  $aPInfo[$uid]["country"] = (!isEmpty($aPInfo[$uid]["country"])?$aPInfo[$uid]["country"]:$sNation);
		else $aPInfo[$uid]["country"] = $sNation;

		//$national_id = str_replace("-","",$national_id);
	  	if(isset($aPInfo[$uid]["id"]))
		  $aPInfo[$uid]["id"] = (!isEmpty($aPInfo[$uid]["id"] )?$aPInfo[$uid]["id"]:$national_id);
		else $aPInfo[$uid]["id"] = $national_id;


	  	if(isset($aPInfo[$uid]["address"]))
		  $aPInfo[$uid]["address"] = (!isEmpty($aPInfo[$uid]["address"])?$aPInfo[$uid]["address"]:$address);
		else $aPInfo[$uid]["address"] = $address;


	  	if(isset($aPInfo[$uid]["district"]))
		  $aPInfo[$uid]["district"] = (!isEmpty($aPInfo[$uid]["district"])?$aPInfo[$uid]["district"]:$district);
		else $aPInfo[$uid]["district"] = $district;


	  	if(isset($aPInfo[$uid]["province"]))
		  $aPInfo[$uid]["province"]=(!isEmpty($aPInfo[$uid]["province"])?$aPInfo[$uid]["province"]:$province);
		else $aPInfo[$uid]["province"] = $province;

	  	if(isset($aPInfo[$uid]["phone"]))
		  $aPInfo[$uid]["phone"]=(!isEmpty($aPInfo[$uid]["phone"])?$aPInfo[$uid]["phone"]:$contact);
		else $aPInfo[$uid]["phone"] = $contact;

	  	if(isset($aPInfo[$uid]["email"]))
		  $aPInfo[$uid]["email"]=(!isEmpty($aPInfo[$uid]["email"])?$aPInfo[$uid]["email"]:$email);
		else $aPInfo[$uid]["email"] = $email;
	  	if(isset($aPInfo[$uid]["line_id"]))
		  $aPInfo[$uid]["line_id"]=(!isEmpty($aPInfo[$uid]["line_id"])?$aPInfo[$uid]["line_id"]:$line_id);
		else $aPInfo[$uid]["line_id"] = $line_id;

	  	if(isset($aPInfo[$uid]["blood"]))
		  $aPInfo[$uid]["blood"]=(!isEmpty($aPInfo[$uid]["blood"])?$aPInfo[$uid]["blood"]:"");
		else $aPInfo[$uid]["blood"] = "";

	}
}


$query ="SELECT uid,p1_sex,p1_blood,p1_name,p1_lastname,p1_dob,p1_race,p1_nationality,p1_telephone,p1_email FROM k_physician WHERE uid=? ORDER BY time_record DESC LIMIT 1";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);


if($stmt->execute()){
	$stmt->bind_result($uid,$p1_sex,$p1_blood,$p1_name,$p1_lastname,$p1_dob,$p1_race,$p1_nationality,$p1_telephone,$p1_email);
	while ($stmt->fetch()) {
	  	$aPInfo[$uid]["uid"] = $uid;

	  	if(isset($aPInfo[$uid]["fname"]))
		  $aPInfo[$uid]["fname"]=(!isEmpty($aPInfo[$uid]["fname"])?$aPInfo[$uid]["fname"]:$p1_name);
		else $aPInfo[$uid]["fname"] = $p1_name;

	  	if(isset($aPInfo[$uid]["lname"]))
		  $aPInfo[$uid]["lname"]=(!isEmpty($aPInfo[$uid]["lname"])?$aPInfo[$uid]["lname"]:$p1_lastname);
		else $aPInfo[$uid]["lname"] = $p1_lastname;

	  	if(isset($aPInfo[$uid]["sex"]))
		  $aPInfo[$uid]["sex"]=(!isEmpty($aPInfo[$uid]["sex"])?$aPInfo[$uid]["sex"]:$p1_sex);
		else $aPInfo[$uid]["sex"] = $p1_sex;

		if($p1_blood=="1") $p1_blood='A';
		else if($p1_blood=="2") $p1_blood='B';
		else if($p1_blood=="3") $p1_blood='AB';
		else if($p1_blood=="4") $p1_blood='O';
		else if($p1_blood=="5") $p1_blood='NA';

		if($p1_blood=="0") $p1_blood="";
	  	if(isset($aPInfo[$uid]["blood"]))
		  $aPInfo[$uid]["blood"]=(!isEmpty($aPInfo[$uid]["blood"])?$aPInfo[$uid]["blood"]:$p1_blood);
		else $aPInfo[$uid]["blood"] = $p1_blood;

	  	$aDOB = explode("-",$p1_dob);
	  	$sDob="";
	  	if(count($aDOB)==3) $sDob = (($aDOB[0]*1)+543)."-".$aDOB[1]."-".$aDOB[2];

	  	if(isset($aPInfo[$uid]["dob"]))
		  $aPInfo[$uid]["dob"]=(!isEmpty($aPInfo[$uid]["dob"])?$aPInfo[$uid]["dob"]:$sDob);
		else $aPInfo[$uid]["dob"] = $sDob;


	  	$sNation = "";

	  	if($p1_nationality=="ไทย") {$sNation = ""; $p1_nationality = "1";}
	  	else if($p1_nationality != "" && $p1_nationality!=NULL) {$sNation = $p1_nationality; $p1_nationality = "2";}

	  	if($p1_nationality != "" && $p1_nationality!=NULL){
	  		$aPInfo[$uid]["nation"] = $p1_nationality;	
	  	}
	  	if(isset($aPInfo[$uid]["nation"]))
		  $aPInfo[$uid]["nation"]=(!isEmpty($aPInfo[$uid]["nation"])?$aPInfo[$uid]["nation"]:$p1_nationality);
		else $aPInfo[$uid]["nation"] = $p1_nationality;
	  	if(isset($aPInfo[$uid]["country"]))
		  $aPInfo[$uid]["country"]=(!isEmpty($aPInfo[$uid]["country"])?$aPInfo[$uid]["dob"]:$sNation);
		else $aPInfo[$uid]["country"] = $sNation;
	  	if(isset($aPInfo[$uid]["phone"]))
		  $aPInfo[$uid]["phone"]=(!isEmpty($aPInfo[$uid]["phone"])?$aPInfo[$uid]["phone"]:$p1_telephone);
		else $aPInfo[$uid]["phone"] = $p1_telephone;
	  	if(isset($aPInfo[$uid]["email"]))
		  $aPInfo[$uid]["email"]=(!isEmpty($aPInfo[$uid]["email"])?$aPInfo[$uid]["email"]:$p1_email);
		else $aPInfo[$uid]["email"] = $p1_email;

	}
}

$mysqli->close();

$sJS = "";

foreach ($aPInfo as $subj_uid => $aInfo) {
	if(isset($aInfo["fname"])) {
		$sJS.= '$("#txtFName").val(getShowText("'.$aInfo["fname"].'"));';
		if(isset($aPOdata[$subj_uid]["fname"])) 
			$sJS.= '$("#txtFName").attr("data-odata",getShowText("'.$aInfo["fname"].'"));';
	}
	if(isset($aInfo["lname"])) {
		$sJS.= '$("#txtLName").val(getShowText("'.$aInfo["lname"].'"));';
		if(isset($aPOdata[$subj_uid]["lname"])) 
			$sJS.= '$("#txtLName").attr("data-odata",getShowText("'.$aInfo["lname"].'"));';
	}
	if(isset($aInfo["nname"])) {
		$sJS.= '$("#txtNickName").val(getShowText("'.$aInfo["nname"].'"));';
		if(isset($aPOdata[$subj_uid]["nname"])) 
			$sJS.= '$("#txtNickName").attr("data-odata",getShowText("'.$aInfo["nname"].'"));';
	}
	if(isset($aInfo["gender"])) {
		$sJS.= '$("#ddlGender").val(getShowText("'.$aInfo["gender"].'"));';
		if(isset($aPOdata[$subj_uid]["gender"])) 
			$sJS.= '$("#ddlGender").attr("data-odata",getShowText("'.$aInfo["gender"].'"));';
	}
	if(isset($aInfo["sex"])) {
		$sJS.= '$("#ddlSex").val(getShowText("'.$aInfo["sex"].'"));';
		if(isset($aPOdata[$subj_uid]["sex"])) 
			$sJS.= '$("#ddlSex").attr("data-odata",getShowText("'.$aInfo["sex"].'"));';
	}
	if(isset($aInfo["dob"])) {
		$sJS.= '$("#txtDOB").val(getShowText("'.$aInfo["dob"].'"));';
		if(isset($aPOdata[$subj_uid]["dob"])) 
			$sJS.= '$("#txtDOB").attr("data-odata",getShowText("'.$aInfo["dob"].'"));';
	}
	if(isset($aInfo["nation"])) {

		$sJS.= "$(\"input[name='nation']\").filter(\"[value='".$aInfo["nation"]."']\").attr(\"checked\",true);";
		if(isset($aPOdata[$subj_uid]["nation"])) 
			$sJS.= "$(\"input[name='nation']\").attr(\"data-odata\",\"".$aInfo["nation"]."\");";
	}


	if(isset($aInfo["country"])) {
		$sJS.= '$("#txtOtherCountry").val(getShowText("'.$aInfo["country"].'"));';
		if(isset($aPOdata[$subj_uid]["country"])) 
			$sJS.= '$("#txtOtherCountry").attr("data-odata",getShowText("'.$aInfo["country"].'"));';
	}
	if(isset($aInfo["id"])) {
		$sJS.= '$("#txtCitizenID").val(getShowText("'.$aInfo["id"].'"));';
		if(isset($aPOdata[$subj_uid]["id"])) 
			$sJS.= '$("#txtCitizenID").attr("data-odata",getShowText("'.$aInfo["id"].'"));';
	}
	if(isset($aInfo["passport"])) {
		$sJS.= '$("#txtPassportId").val(getShowText("'.$aInfo["passport"].'"));';
		if(isset($aPOdata[$subj_uid]["passport"])) 
			$sJS.= '$("#txtPassportId").attr("data-odata",getShowText("'.$aInfo["passport"].'"));';
	}

	if(isset($aInfo["id_address"])) {
		$sJS.= '$("#txtIdAddress").val(getShowText("'.urlencode($aInfo["id_address"]).'"));';
		//if(isset($aPOdata[$subj_uid]["id_address"])) 
			//$sJS.= '$("#txtIdAddress").attr("data-odata",getShowText("'.urlencode($aInfo["id_address"]).'"));';
	}
	if(isset($aInfo["id_district"])) {
		$sJS.= '$("#txtIdDistrict").val(getShowText("'.$aInfo["id_district"].'"));';
		if(isset($aPOdata[$subj_uid]["id_district"])) 
			$sJS.= '$("#txtIdDistrict").attr("data-odata",getShowText("'.$aInfo["id_district"].'"));';
	}
	if(isset($aInfo["id_province"])) {
		$sJS.= '$("#txtIdProvince").val(getShowText("'.$aInfo["id_province"].'"));';
		if(isset($aPOdata[$subj_uid]["id_province"])) 
			$sJS.= '$("#txtIdProvince").attr("data-odata",getShowText("'.$aInfo["id_province"].'"));';
	}
	if(isset($aInfo["id_zone"])) {
		$sJS.= '$("#txtIdArea").val(getShowText("'.$aInfo["id_zone"].'"));';
		if(isset($aPOdata[$subj_uid]["id_zone"])) 
			$sJS.= '$("#txtIdArea").attr("data-odata",getShowText("'.$aInfo["id_zone"].'"));';
	}
	if(isset($aInfo["id_zip"])) {
		$sJS.= '$("#txtIdPost").val(getShowText("'.$aInfo["id_zip"].'"));';
		if(isset($aPOdata[$subj_uid]["id_zip"])) 
			$sJS.= '$("#txtIdPost").attr("data-odata",getShowText("'.$aInfo["id_zip"].'"));';
	}
	if(isset($aInfo["use_id_address"])) {
		$sJS.= "$(\"input[name='use_id_address']\").filter(\"[value='".$aInfo["use_id_address"]."']\").attr(\"checked\",true);";
		if(isset($aPOdata[$subj_uid]["use_id_address"])) 
			$sJS.= "$(\"input[name='use_id_address']\").attr(\"data-odata\",\"".$aInfo["use_id_address"]."\");";
	}


	if(isset($aInfo["address"])) {
		$sJS.= '$("#txtAddress").val(getShowText("'.urlencode($aInfo["address"]).'"));';
		//if(isset($aPOdata[$subj_uid]["address"])) 
			//$sJS.= '$("#txtAddress").attr("data-odata",getShowText("'.urlencode($aInfo["address"]).'"));';
	}
	if(isset($aInfo["district"])) {
		$sJS.= '$("#txtDistrict").val(getShowText("'.$aInfo["district"].'"));';
		if(isset($aPOdata[$subj_uid]["district"])) 
			$sJS.= '$("#txtDistrict").attr("data-odata",getShowText("'.$aInfo["district"].'"));';
	}
	if(isset($aInfo["province"])) {
		$sJS.= '$("#txtProvince").val(getShowText("'.$aInfo["province"].'"));';
		if(isset($aPOdata[$subj_uid]["province"])) 
			$sJS.= '$("#txtProvince").attr("data-odata",getShowText("'.$aInfo["province"].'"));';
	}
	if(isset($aInfo["zone"])) {
		$sJS.= '$("#txtArea").val(getShowText("'.$aInfo["zone"].'"));';
		if(isset($aPOdata[$subj_uid]["zone"])) 
			$sJS.= '$("#txtArea").attr("data-odata",getShowText("'.$aInfo["zone"].'"));';
	}
	if(isset($aInfo["zip"])) {
		$sJS.= '$("#txtPost").val(getShowText("'.$aInfo["zip"].'"));';
		if(isset($aPOdata[$subj_uid]["zip"])) 
			$sJS.= '$("#txtPost").attr("data-odata",getShowText("'.$aInfo["zip"].'"));';
	}
	if(isset($aInfo["phone"])) {
		$sJS.= '$("#txtPhone").val(getShowText("'.$aInfo["phone"].'"));';
		if(isset($aPOdata[$subj_uid]["phone"])) 
			$sJS.= '$("#txtPhone").attr("data-odata",getShowText("'.$aInfo["phone"].'"));';
	}
	if(isset($aInfo["email"])) {
		$sJS.= '$("#txtEmail").val(getShowText("'.$aInfo["email"].'"));';
		if(isset($aPOdata[$subj_uid]["email"])) 
			$sJS.= '$("#txtEmail").attr("data-odata",getShowText("'.$aInfo["email"].'"));';
	}

	if(isset($aInfo["line_id"])) {
		$sJS.= '$("#txtLine").val(getShowText("'.$aInfo["line_id"].'"));';
		if(isset($aPOdata[$subj_uid]["line_id"])) 
			$sJS.= '$("#txtLine").attr("data-odata",getShowText("'.$aInfo["line_id"].'"));';
	}

	if(isset($aInfo["em_name_1"])) {
		$sJS.= '$("#txtEmName1").val(getShowText("'.$aInfo["em_name_1"].'"));';
		if(isset($aPOdata[$subj_uid]["em_name_1"])) 
			$sJS.= '$("#txtEmName1").attr("data-odata",getShowText("'.$aInfo["em_name_1"].'"));';
	}

	if(isset($aInfo["em_relation_1"])) {
		$sJS.= '$("#txtEmRelation1").val(getShowText("'.$aInfo["em_relation_1"].'"));';
		if(isset($aPOdata[$subj_uid]["em_relation_1"])) 
			$sJS.= '$("#txtEmRelation1").attr("data-odata",getShowText("'.$aInfo["em_relation_1"].'"));';
	}

	if(isset($aInfo["em_phone_1"])) {
		$sJS.= '$("#txtEmPhone1").val(getShowText("'.$aInfo["em_phone_1"].'"));';
		if(isset($aPOdata[$subj_uid]["em_phone_1"])) 
			$sJS.= '$("#txtEmPhone1").attr("data-odata",getShowText("'.$aInfo["em_phone_1"].'"));';
	}	


	if(isset($aInfo["em_name_2"])) {
		$sJS.= '$("#txtEmName2").val(getShowText("'.$aInfo["em_name_2"].'"));';
		if(isset($aPOdata[$subj_uid]["em_name_2"])) 
			$sJS.= '$("#txtEmName2").attr("data-odata",getShowText("'.$aInfo["em_name_2"].'"));';
	}

	if(isset($aInfo["em_relation_2"])) {
		$sJS.= '$("#txtEmRelation2").val(getShowText("'.$aInfo["em_relation_2"].'"));';
		if(isset($aPOdata[$subj_uid]["em_relation_2"])) 
			$sJS.= '$("#txtEmRelation2").attr("data-odata",getShowText("'.$aInfo["em_relation_2"].'"));';
	}

	if(isset($aInfo["em_phone_2"])) {
		$sJS.= '$("#txtEmPhone2").val(getShowText("'.$aInfo["em_phone_2"].'"));';
		if(isset($aPOdata[$subj_uid]["em_phone_2"])) 
			$sJS.= '$("#txtEmPhone2").attr("data-odata",getShowText("'.$aInfo["em_phone_2"].'"));';
	}	

	if(isset($aInfo["blood"])) {
		$sJS.= '$("#ddlBlood").val(getShowText("'.$aInfo["blood"].'"));';
		if(isset($aPOdata[$subj_uid]["blood"])) 
			$sJS.= '$("#ddlBlood").attr("data-odata",getShowText("'.$aInfo["blood"].'"));';
	}

	if(isset($aInfo["religion"])) {
		$sJS.= '$("#ddlReligion").val(getShowText("'.$aInfo["religion"].'"));';
		if(isset($aPOdata[$subj_uid]["religion"])) 
			$sJS.= '$("#ddlReligion").attr("data-odata",getShowText("'.$aInfo["religion"].'"));';
	}
	//error_log(implode(",",$aInfo));
}

?>
<style>
	#tblPatient th{
		text-align: right;
		font-weight: normal;
	}
	#tblPatient td{
		text-align: left;
	}
	#tblPatient tr{
		height:50px;
		font-size:small;
	}
	#tblPatient tr:hover{
		
	}

</style>
<div style=';text-align: center;padding:20px;'>
	กรุณายืนยันข้อมูลส่วนตัว เพื่อประโยชน์ในการรักษา <br/> Please confirm your information. The data will be used for the treatment information.
</div>
<table id='tblPatient' cellpadding='0' cellspacing="0" style='text-align: center' <? echo($sObjProp); ?> >
	
	<tr>
		<th>ชื่อ<br/><span>First Name</span></th><td>:</td><td><input id='txtFName' name='fname' value='' style='width:90%' data-odata='' class='savedata checkdata' /></td>
			
		<th>นามสกุล<br/><span>Family Name</span></th><td>:</td><td><input id='txtLName' name='sname' style='width:90%' value='' data-odata='' class='savedata checkdata' /></td>

		<th>ชื่อเล่น<br/><span>Nick Name</span></th><td>:</td><td><input id='txtNickName' name='nickname' value='' data-odata='' class='savedata checkdata' /></td>
		
	</tr>
	<tr>
		<th>วันเกิด<br/><span>Date of Birth</span></th><td>:</td><td><input id='txtDOB' name='date_of_birth' value='' data-odata='' class='savedata checkdata w-m' /></td>

		<th>เพศกำเนิด<br/><span>Sex at birth</span></th><td>:</td><td><SELECT id='ddlSex' name='sex' data-odata='' class='savedata checkdata'><option value="">-- เลือก --</option><option value='1'>ชาย / Man</option><option value='2'>หญิง / Women</option><option value='3'>มีเพศสรีระทั้งชายและหญิง / Intersex</option></SELECT></td>
			
		<th>อัตลักษณ์ทางเพศ<br/><span>Gender identity</span></th><td>:</td><td><SELECT id='ddlGender' data-odata='' class='savedata checkdata' name='gender'>
		    <option value="">-- เลือก --</option>
		    <option value="1" data-forsex='3'>ไม่แน่ใจ (Questioning)</option>
		    <option value="2" data-forsex='1'>ชาย (male)</option>
		    <option value="3" data-forsex='2'>หญิง (female)</option>
			<option value="4" data-forsex='1'>ชายข้ามเพศเป็นหญิง (transgender women)</option>
		    <option value="5" data-forsex='2'>หญิงข้ามเพศเป็นชาย (transgender men)</option>
			<option value="6" data-forsex='1'>เกย์ (Gay man)</option>
			<option value="7" data-forsex='2'>เลสเปี้ยน (Lesbian)</option>
			<option value="8" data-forsex='3'>ไม่อยู่ในกรอบเพศชายหญิง (Gender variance/non-binary)</option>
			<option value="9" data-forsex='3'>ไม่ขอตอบ</option>
		    </SELECT></td>

	</tr>
	<tr>

		<th>ศาสนา<br/><span>Religion</span></th><td>:</td><td>
			<SELECT id='ddlReligion' class='savedata checkdata' name='religion'>
				<option value='1'>ไม่นับถือศาสนาใด (Irreligious)</option>
				<option value='2'>พุทธ (Buddhism)</option>
				<option value='3'>คริสต์ (Christianity)</option>
				<option value='4'>อิสลาม (Islam)</option>
				<option value='5'>ฮินดู (Hinduism)</option>
				<option value='6'>อื่นๆ (Others)</option>
			</SELECT>
		</td>

		<th>สัญชาติ<br/><span>Nationality</span></th><td>:</td><td>
			<label><input name='nation' type='radio' value='1' data-odata='' class='savedata' />ไทย Thai</label><br/>
			<label><input name='nation' type='radio' value='2' data-odata='' class='savedata checkdata' />อื่นๆ Other </label><input id='txtOtherCountry' data-odata='' /></td>
			


		<th colspan='3'>
			<div style='display:flex;vertical-align: middle'>
				<div style='vertical-align: middle;line-height: 20px'>
					บัตรประชาชน<br/>Thai Citizen ID 
				</div>
				<div style='line-height: 40px'>
					: <input id='txtCitizenID' size='13' value='' data-odata='' class='savedata checkdata' name='country_other' />
				</div>
				<div style='margin-left: 10px;vertical-align: middle;line-height: 20px'>
					พาสปอร์ท<br/>Passport
				</div>
				<div style='line-height: 40px'>
					: <input id='txtPassportId' size='13' value='' data-odata='' class='savedata checkdata' name='passport_id' />
				</div>
			</div>

		</th>

	</tr>
	<tr style='height:30px;background-color: #dffdff'>

		<th>กรุ๊ปเลือด<br/><span>Blood Group</span></th><td>:</td><td>
			<SELECT id='ddlBlood' class='savedata checkdata' name='blood_type'>
			    <option value="">-- เลือก --</option>
			    <option value="A">เอ A</option>
			    <option value="B">บี B</option>
			    <option value="AB">เอบี AB</option>
			    <option value="O">โอ O</option>
				<option value="NA">ไม่ทราบ Unknown</option>	
    		</SELECT>
		</td>

		<th>ที่อยู่ตามบัตร<br/><span>ID Address</span></th><td>:</td><td><textarea id='txtIdAddress' value='' data-odata='' style='width:95%' class='savedata checkdata' name='id_address'></textarea></td>

		<th>แขวง/ตำบล<br/><span>Area</span></th><td>:</td><td><input id='txtIdArea' name='id_zone' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>

	<tr style='height:30px;background-color: #dffdff'>
		<th>เขต/อำเภอ<br/><span>District</span></th><td>:</td><td><input id='txtIdDistrict' name='id_district' value='' data-odata='' class='savedata checkdata' /></td>
			
		<th>จังหวัด<br/><span>Province</span></th><td>:</td><td><input id='txtIdProvince' name='id_province' value='' data-odata='' class='savedata checkdata' /></td>

		<th>รหัสไปรษณีย์<br/><span>Post Code</span></th><td>:</td><td><input maxlength="8" name='id_postal_code' id='txtIdPost' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>

	<tr style='height:30px;background-color: #ffedfe'>

		<th>ที่อยู่ที่ติดต่อ<br/><span>Contact Address</span></th><td>:</td><td>
			<label><input name='use_id_address' type='radio' value='1' data-odata='' class='savedata' />ใช้ที่อยู่เดียวกับบัตร</label><br/>
			<label><input name='use_id_address' type='radio' value='2' data-odata='' class='savedata checkdata' />อื่นๆ Other </label></td>

		<th class='contactaddress'>ที่อยู่<br/><span>Address</span></th><td class='contactaddress'>:</td><td class='contactaddress'><textarea id='txtAddress' value='' data-odata='' style='width:95%' class='savedata checkdata' name='address'></textarea></td>

		<th class='contactaddress'>แขวง/ตำบล<br/><span>Area</span></th><td class='contactaddress'>:</td><td class='contactaddress'><input id='txtArea' name='zone' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>

	<tr class='contactaddress' style='height:30px;background-color: #ffedfe'>
		<th>เขต/อำเภอ<br/><span>District</span></th><td>:</td><td><input id='txtDistrict' name='district' value='' data-odata='' class='savedata checkdata' /></td>
			
		<th>จังหวัด<br/><span>Province</span></th><td>:</td><td><input id='txtProvince' name='province' value='' data-odata='' class='savedata checkdata' /></td>

		<th>รหัสไปรษณีย์<br/><span>Post Code</span></th><td>:</td><td><input maxlength="8" name='postal_code' id='txtPost' value='' data-odata='' class='savedata checkdata' /></td>

	</tr>

	<tr>
		<th>โทรศัพท์<br/><span>Phone</span></th><td>:</td><td><input id='txtPhone' name='tel_no' value='' data-odata='' class='savedata checkdata' /></td>
			
		<th><span>Email</span></th><td>:</td><td><input id='txtEmail' name='email' style='width:90%' value='' data-odata='' class='savedata checkdata' /></td>

		<th><span>Line Id</span></th><td>:</td><td><input id='txtLine' name='line_id' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>	

	<tr style='height:30px;background-color: #ffedfe'>
		<th>ชื่อ ช่องทางติดต่อฉุกเฉิน 1<br/><span>Emergency Contact 1</span></th><td>:</td><td><input id='txtEmName1' name='em_name_1' value='' data-odata='' class='savedata checkdata' /></td>
			
		<th><span>ความสัมพันธ์ 1<br/>Relationship 1</span></th><td>:</td><td><input id='txtEmRelation1' name='em_relation_1' value='' data-odata='' class='savedata checkdata' /></td>

		<th><span>เบอร์โทรศัพท์ 1<br/>Telephone number 1</span></th><td>:</td><td><input id='txtEmPhone1' name='em_phone_1' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>

	<tr style='height:30px;background-color: #ffedfe'>
		<th>ชื่อ ช่องทางติดต่อฉุกเฉิน 2<br/><span>Emergency Contact 2</span></th><td>:</td><td><input id='txtEmName2' name='em_name_2' value='' data-odata='' class='savedata checkdata' /></td>
			
		<th><span>ความสัมพันธ์ 2<br/>Relationship 2</span></th><td>:</td><td><input id='txtEmRelation2' name='em_relation_2' value='' data-odata='' class='savedata checkdata' /></td>

		<th><span>เบอร์โทรศัพท์ 2<br/>Telephone number 2</span></th><td>:</td><td><input id='txtEmPhone2' name='em_phone_2' value='' data-odata='' class='savedata checkdata' /></td>
	</tr>

	<tr >
		<th colspan='9' style='text-align: center'>  <button id='btnUpdatePInfo' style='font-size:20px'>ยืนยัน / Confirm</button><img id='imgPLoader' src='assets/image/spinner.gif' style='display:none' /></td>
	</tr>

</table>

<script>
	function isEmpty(sText){
		if(!sText|| $sText=="") return "";
		else return sText; 
	}
	$(function(){
		<? echo($jsHtml); ?>


		$("#txtDOB").datepicker({dateFormat:"yy-mm-dd",changeMonth:true,changeYear:true});


		$("#btnUpdatePInfo").unbind("click");
		$("#btnUpdatePInfo").on("click",function(){
			let sUid = $("#tblPatient").attr("data-uid");
			let sColDate = $("#tblPatient").attr("data-coldate");
			let sColTime = $("#tblPatient").attr("data-coltime");

			var aData = {u_mode:"update_patient_info",u:sUid,coldate:sColDate,coltime:sColTime};

			sEmail = $("#txtEmail").val();


			if(sEmail != "" && !checkEmail(sEmail)){
				$.notify("Invalid Email Format");
				$("#txtEmail").focus();
				return;
			}

			isChanged = false;
			$(".checkdata").each(function(ix,objx){
				sNData = $(objx).val();
				if($(objx).attr("type")=="radio"){
					sNData = $("input[name='"+$(objx).attr("name")+"']:checked").val();
				}
				if($(objx).attr('data-odata') != sNData){
					aData[$(objx).attr("name")] = sNData;
					//$.notify(aData[$(objx).attr("name")]);
					isChanged=true;
				}
			});

			
			startLoad($("#btnUpdatePInfo"),$("#imgPLoader"));
			
			aAllForm = sAllForm.split(",");  sNextForm = "";
			for(ix=1;ix<aAllForm.length;ix++){
				sNextForm += ((sNextForm=="")?"":",")+aAllForm[ix];
			}
			var sUrl = "../weclinic/data_mgt/mnu_form_view.php?form_id="+aAllForm[0]+"&uid="+sUid+"&collect_date="+sColDate+"&collect_time="+sColTime+"&s_id=patient&next_form_id="+sNextForm;

			$.notify("Please wait.\r\nกรุณารอสักครู่","success");
			if(isChanged){
				
				//return; 
				callAjax("patient_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res=="0"){

					}else if(rtnObj.res=="1"){
						window.location.href=sUrl;
					}
				});
			}else{

				window.location.href=sUrl;
			}
		});

		/* Open Sex
		$("#ddlSex").unbind("change");
		$("#ddlSex").on("change",function(){
			if($(this).val()=="" || $(this).val()=="3"){
				$("#ddlGender option").show();
			}else {
				$("#ddlGender option").hide();
				$("#ddlGender option[data-forsex='"+$(this).val()+"']").show();
				$("#ddlGender option[data-forsex='3']").show();
			}
		});
		*/

		$("input[name='use_id_address']").unbind("change");
		$("input[name='use_id_address']").on("change",function(){
			if($("input[name='use_id_address']:checked").val()=="1"){
				//Use Citizen
				$(".contactaddress").hide();
			}else{
				$(".contactaddress").show();
			}
		});


		$("input[name='nation']").unbind("change");
		$("input[name='nation']").on("change",function(){
			if($("input[name='nation']:checked").val()=="1"){
				//Thai
				$("#txtCitizenID").removeAttr("disabled");
				$("#txtOtherCountry").val("");
				$("#txtOtherCountry").attr("disabled",true);
			}else{
				if($("#txtCitizenID").val()=="") $("#txtCitizenID").val("0000000000000");
				$("#txtCitizenID").attr("disabled",true);
				$("#txtOtherCountry").removeAttr("disabled");
			}
		});

		<? echo($sJS); ?>

		$("input[name='nation']").trigger("change");
		$(".checkdata").trigger("change");

		function getShowText(sObjValue){
			var	result = "";

			if(sObjValue){
				skey = new RegExp(/&/i,'g');
				result = sObjValue.replace(skey,"&#38;");
				skey = new RegExp(/'/i,'g');
				result = result.replace(skey,"&#39;");

				skey = new RegExp(/"/i,'g');
				result = result.replace(skey,"&#34;");

				skey = new RegExp(/</i,'g');
				result = result.replace(skey,"&#60;");

				skey = new RegExp(/>/i,'g');
				result = result.replace(skey,"&#62;");

				skey = new RegExp(/ /i,'g');
				result = result.replace(skey,"&#32;");
			}
			result = decodeURIComponent(result.replace(/\+/g, ' '));

			return result;
		}
	});
</script>