<?
include_once("in_php_function.php");
$sMode = getQS("u_mode");
$sUid = getQS("uid");
$sName = getQS("fname");
$sDob = getQS("dob");
$sId = str_replace("-","",getQS("citizen_id"));
$sPhone = str_replace("-","",getQS("tel_no"));
$sEmail=getQS("email");
include_once("in_setting_row.php");

//SQL Injection Basic Protection
$sUid = str_replace("'","",$sUid);
$sName = str_replace("'","",$sName);
$sDob = str_replace("'","",$sDob);
$sId = str_replace("'","",$sId);
$sPhone = str_replace("'","",$sPhone);
$sEmail = str_replace("'","",$sEmail);



$sToday = date("Y-m-d");
$sHtml="";
include("in_db_conn.php");

if($sName=="" && $sUid=="" && $sDob=="" && $sId=="" && $sPhone=="" && $sEmail=="") {	
	echo("No row found");
	exit();
}

if($sMode=="find_uid"){
	//Find from K_visit_data for today visit
	$query =" SELECT uid,uic,fname,sname,nickname,clinic_type,sex,gender,date_of_birth,nation,citizen_id,passport_id,tel_no,email,id_address,id_district,id_province,address,district,province  FROM patient_info WHERE (uid != '') AND (";
	$sCond = "";

	if($sUid!="") $sCond .= " (uid LIKE '%".$sUid."%' OR uic LIKE '%".$sUid."%')";

	$aName = array();

	if($sName!=""){ 
		$aName = explode(" ",$sName);

		if(count($aName) > 1){
			//Have Lastname input
			$sCond .= (($sCond=="")?"":" OR ")." (fname LIKE '%".$aName[0]."%' AND sname LIKE '%".$aName[1]."%')";
		}else 
			$sCond .= (($sCond=="")?"":" OR "). " fname LIKE '%".$sName."%' OR sname LIKE '%".$sName."%' OR nickname LIKE '%".$sName."%'";
	}


	$sTHDOB=$sDob;
	$sDCDOB=$sDob;

	
	if($sDob!="") {
		$aT = explode("-",$sDob);
		if(count($aT)>1){
			$sTHDOB=(($aT[0]>2400)?$aT[0]:(($aT[0]*1) + 543))."-".$aT[1]."-".$aT[2];
			$sDCDOB=(($aT[0]>2400)?(($aT[0]*1) + 543):$aT[0])."-".$aT[1]."-".$aT[2];
		}

		$sCond .= (($sCond=="")?"":" OR "). " (date_of_birth = '".$sTHDOB."' OR date_of_birth = '".$sDCDOB."')";
	}


	if($sId!="") $sCond .= (($sCond=="")?"":" OR "). " REPLACE(citizen_id,'-','')  LIKE '%".$sId."%' OR passport_id LIKE '%".$sId."%'";
	if($sPhone!="") $sCond .= (($sCond=="")?"":" OR "). " REPLACE(tel_no,'-','') LIKE '%".$sPhone."%'";
	if($sEmail!="") $sCond .= (($sCond=="")?"":" OR "). " email LIKE '%".$sEmail."%'";
	$sCond .= ") LIMIT 50";


	$aPInfo = array();

	$stmt = $mysqli->prepare($query.$sCond);
	if($stmt->execute()){
	  $stmt->bind_result($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$gender,$date_of_birth,$nation,$citizen_id,$passport_id,$tel_no,$email,$id_address,$id_district,$id_province,$address,$district,$province);
	  while ($stmt->fetch()) {
	  	$aPInfo[$uid]["uid"] = $uid;
	  	$aPInfo[$uid]["uic"] = $uic;
	  	$aPInfo[$uid]["fname"] = $fname;
	  	$aPInfo[$uid]["lname"] = $sname;
	  	$aPInfo[$uid]["nname"] = $nickname;
	  	$aPInfo[$uid]["clinic"] = $clinic_type;


	  	$sCurGender="";
	  	if($gender=="1") $sCurGender="ไม่แน่ใจ";
	  	else if($gender=="2") $sCurGender="ชาย";
	  	else if($gender=="3") $sCurGender="หญิง";
	  	else if($gender=="4") $sCurGender="หญิงข้ามเพศ";
	  	else if($gender=="5") $sCurGender="ชายข้ามเพศ";
	  	else if($gender=="6") $sCurGender="เกย์";
	  	else if($gender=="7") $sCurGender="เลสเบี้ยน";
	  	else if($gender=="8") $sCurGender="ไม่อยู่ในกรอบเพศชายหญิง";
		else if($gender=="9") $sCurGender="ไม่ขอตอบ";

		if($sex=="1") $sex="ชาย";
		else if($sex=="2") $sex="หญิง";

	  	$aPInfo[$uid]["sex"] = (($sex.$gender != "")?"":$sex."<br/>".$sCurGender);
	  	
	  	$aDOB = explode("-",$date_of_birth);

	  	$sDateOB = $date_of_birth; $sDCDate = $date_of_birth;
	  	if(count($aDOB) > 1){
	  		if($aDOB[0]<2400){
	  			$sDateOB= (($aDOB[0]*1) +543)."-".$aDOB[1]."-".$aDOB[2];
	  		}else{
	  			$sDCDate= (($aDOB[0]*1) -543)."-".$aDOB[1]."-".$aDOB[2];
	  		}
	  	}
	  	$aPInfo[$uid]["dob"] = $sDateOB;
	  	$aPInfo[$uid]["bcdob"] = $date_of_birth;


	  	$aPInfo[$uid]["nation"] = (($nation=="1")?"ไทย":"อื่นๆ");
	  	$aPInfo[$uid]["id"] = $citizen_id;
	  	$aPInfo[$uid]["passport"] = $passport_id;
	  	$aPInfo[$uid]["id_address"] = $id_address;
	  	$aPInfo[$uid]["id_district"] = $id_district;
	  	$aPInfo[$uid]["id_province"] = $id_province;
	  	$aPInfo[$uid]["address"] = $address;
	  	$aPInfo[$uid]["district"] = $district;
	  	$aPInfo[$uid]["province"] = $province;
	  	$aPInfo[$uid]["phone"] = $tel_no;
	  	$aPInfo[$uid]["email"] = $email;
	  }
	}


	$query =" SELECT uid,UG.uic,flfn,flln,date_o_b,mon_o_b,y_o_b,fname,sname,contact,email,line_id,gender,nation,province,district,national_id,clinic_id FROM uic_gen UG
	LEFT JOIN basic_reg BR
	ON BR.uic = UG.uic
	WHERE ";

	$sCond = "";

	if($sUid!="") $sCond .= " (uid LIKE '%".$sUid."%' OR UG.uic LIKE '%".$sUid."%')";
	
	if($sName!=""){
		if(count($aName) > 1){
			$sCond .= (($sCond=="")?"":" OR "). " (fname LIKE '%".$aName[0]."%' AND sname LIKE '%".$aName[1]."%')";
		}else{
			$sCond .= (($sCond=="")?"":" OR "). " fname LIKE '%".$sName."%' OR sname LIKE '%".$sName."%'";
		}

	} 




	$aY = explode("-",$sDob);
	if($sDob!=""){
		$aY[0] = substr($aY[0],2);
		$aY[1] = $aY[1] * 1;
		$aY[2] = $aY[2] * 1;

		$sCond .=(($sCond=="")?"":" OR ")." (date_o_b = '".$aY[2]."' AND mon_o_b = '".$aY[1]."' AND y_o_b = '".$aY[0]."' )";
	}
	//if($sDob!="") $sCond .= (($sCond=="")?"":" OR "). " date_of_birth = '".$sDob."'";
	if($sId!="") $sCond .= (($sCond=="")?"":" OR "). " REPLACE(national_id,'-','') LIKE '%".$sId."%'";
	if($sPhone!="") $sCond .= (($sCond=="")?"":" OR "). " REPLACE(contact,'-','') LIKE '%".$sPhone."%'";
	if($sEmail!="") $sCond .= (($sCond=="")?"":" OR "). " email LIKE '%".$sEmail."%'";


	$stmt = $mysqli->prepare($query.$sCond);
	if($stmt->execute()){
	  $stmt->bind_result($uid,$uic,$flfn,$flln,$date_o_b,$mon_o_b,$y_o_b,$fname,$sname,$contact,$email,$line_id,$gender,$nation,$province,$district,$national_id,$clinic_id);
	  while ($stmt->fetch()) {
	  	$aPInfo[$uid]["uid"] = $uid;
	  	$aPInfo[$uid]["uic"] = $uic;

	  	if($fname=="" && $flfn!="") $fname=$flfn;
	  	if($sname=="" && $flln!="") $sname=$flln;
	  	if(isset($aPInfo[$uid]["fname"])){
	  		if($aPInfo[$uid]["fname"]!="" && $aPInfo[$uid]["fname"]!=$fname) $fname = $aPInfo[$uid]["fname"];
	  	}
	  	if(isset($aPInfo[$uid]["lname"])){
	  		if($aPInfo[$uid]["lname"]!="" && $aPInfo[$uid]["lname"]!=$fname) $sname = $aPInfo[$uid]["lname"];
	  	}

	  	$aPInfo[$uid]["fname"] = $fname;
	  	$aPInfo[$uid]["lname"] = $sname;

	  	$aPInfo[$uid]["clinic"] = (isset($aPInfo[$uid]["clinic"])?$aPInfo[$uid]["clinic"]:$clinic_id);


	  	$sSexText = $gender;
	  	if($gender==1) $sSexText = "ชาย";
	  	else if($gender==2) $sSexText = "หญิง";
	  	else if($gender==3) $sSexText = "สาวประเภทสอง";
	  	else if($gender==4) $sSexText = "แปลงเพศเป็นหญิง";
	  	else if($gender==5) $sSexText = "แปลงเพศเป็นชาย";
	  	$aPInfo[$uid]["sex"] = (isset($aPInfo[$uid]["sex"])?$aPInfo[$uid]["sex"]:$sSexText);


	  	$thYear = ($y_o_b > 60)?"24":"25"; $sY = $thYear.str_pad($y_o_b,2,"0",STR_PAD_LEFT);

	  	$sDob = $sY."-".str_pad($mon_o_b,2,"0",STR_PAD_LEFT )."-".str_pad($date_o_b,2,"0",STR_PAD_LEFT );
	  	$sDCDob = (($sY*1)-543)."-".str_pad($mon_o_b,2,"0",STR_PAD_LEFT )."-".str_pad($date_o_b,2,"0",STR_PAD_LEFT );

	  	$aPInfo[$uid]["dob"] = ((isset($aPInfo[$uid]["dob"]) && $aPInfo[$uid]["dob"]!="") ?$aPInfo[$uid]["dob"]:$sDob);
	  	$aPInfo[$uid]["bcdob"] = ((isset($aPInfo[$uid]["bcdob"]) && $aPInfo[$uid]["bcdob"]!="") ?$aPInfo[$uid]["bcdob"]:$sDCDob);


	  	$sNation = $nation;
	  	if($nation=="1") $sNation = "ไทย";
	  	else if($nation=="2") $sNation = "พม่า";
	  	else if($nation=="3") $sNation = "ลาว";
	  	else if($nation=="4") $sNation = "กัมพูชา";
	  	else if($nation=="5") $sNation = "อื่นๆ";


	  	$aPInfo[$uid]["nation"] = ((isset($aPInfo[$uid]["nation"]) && $aPInfo[$uid]["nation"]!="") ?$aPInfo[$uid]["nation"]:$sNation);

	  	$aPInfo[$uid]["id"] = ((isset($aPInfo[$uid]["id"]) && $aPInfo[$uid]["id"]!="") ?$aPInfo[$uid]["id"]:$national_id);
	  	$aPInfo[$uid]["passport"] = ((isset($aPInfo[$uid]["passport"]) && $aPInfo[$uid]["passport"]!="") ?$aPInfo[$uid]["passport"]:"");
	  	$aPInfo[$uid]["address"] = ((isset($aPInfo[$uid]["address"]) && $aPInfo[$uid]["address"]!="") ?$aPInfo[$uid]["address"]:$address);
	  	$aPInfo[$uid]["district"] = ((isset($aPInfo[$uid]["district"]) && $aPInfo[$uid]["district"]!="") ?$aPInfo[$uid]["district"]:$district);
	  	$aPInfo[$uid]["province"] = ((isset($aPInfo[$uid]["province"]) && $aPInfo[$uid]["province"]!="") ?$aPInfo[$uid]["province"]:$province);
	  	$aPInfo[$uid]["phone"] =  ((isset($aPInfo[$uid]["phone"]) && $aPInfo[$uid]["phone"]!="") ?$aPInfo[$uid]["phone"]:$contact);
	  	$aPInfo[$uid]["email"] = ((isset($aPInfo[$uid]["email"]) && $aPInfo[$uid]["email"]!="") ?$aPInfo[$uid]["email"]:$email);


	  	$aPInfo[$uid]["id_address"] = ((isset($aPInfo[$uid]["id_address"]) && $aPInfo[$uid]["id_address"]!="") ?$aPInfo[$uid]["id_address"]:$id_address);
	  	$aPInfo[$uid]["id_district"] = ((isset($aPInfo[$uid]["id_district"]) && $aPInfo[$uid]["id_district"]!="") ?$aPInfo[$uid]["id_district"]:$id_district);
	  	$aPInfo[$uid]["id_province"] = ((isset($aPInfo[$uid]["id_province"]) && $aPInfo[$uid]["id_province"]!="") ?$aPInfo[$uid]["id_province"]:$id_province);


	  }
	}
}
$mysqli->close();
if($sMode=="find_uid"){
	foreach ($aPInfo as $subj_uid => $aInfo) {

	$sHtml.=getSearchPatientRow($subj_uid,$aInfo["uic"],$aInfo["fname"],$aInfo["lname"],(isset($aInfo["nname"])?$aInfo["nname"]:""),$aInfo["clinic"],$aInfo["sex"],$aInfo["dob"],$aInfo["bcdob"],$aInfo["nation"],$aInfo["id"],$aInfo["passport"],$aInfo["id_address"],$aInfo["id_district"],$aInfo["id_province"],$aInfo["address"],$aInfo["district"],$aInfo["province"],$aInfo["phone"],$aInfo["email"]);
	}
}
?>
<style>
.sticky {
  position: fixed;
  top: 0;
  width: 100%
}

</style>

<table id='divSearchResult' class='sortable' style=''>
	<thead>
		<tr style=''>
			<th>
				#
			</th>
			<th  style='width:75px'>
				UID / UIC
			</th>
			<th >
				Name
			</th>
			<th >
				Clinic
			</th>
			<th >
				Sex
			</th>
			<th  style='width:80px'>
				DOB
			</th>
			<th >
				Nation<br/>
				CitizenID/Passport
			</th>
			<th >
				Address
			</th>

			<th >
				Contact
			</th>
			<th >
				
			</th>
		</tr>
	<thead>
	<tbody>
	<? echo($sHtml); ?>
	</tbody>
</table>



<script>
	$(document).ready(function(){
		var table = $("#divSearchResult");
		$('#divSearchResult').fixedHeaderTable('show');

		$('.sortable th')
		.wrapInner('<span title="sort this column"/>')
		.each(function(){
			var th = $(this),
			thIndex = th.index(),
			inverse = false;

			th.click(function(){
			table.find('td').filter(function(){

			return $(this).index() === thIndex;
			}).sortElements(function(a, b){

			let sTxtA = $.text([a]);
			let sTxtB = $.text([b]);

			if (Number.isInteger(sTxtA*1)){
				sTxtA = sTxtA*1;
			}
			if (Number.isInteger(sTxtB*1)){
				sTxtB = sTxtB*1;
			}

			if( sTxtA == sTxtB )
			    return 0;

			return sTxtA > sTxtB ?
			    inverse ? -1 : 1
			    : inverse ? 1 : -1;
			}, function(){
			// parentNode is the element we want to move
			return this.parentNode; 
			});

			inverse = !inverse;

			});

		});
	});

</script>
