<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");

$sToday = date("Y-m-d");
$sVisitDate = getQS("vdate",$sToday);
$COUNSELOR_FORM_NAME = "PRIBTA_PROVIDER";

/*
Sex 
1 = ชาย
2 = หญิง
3 = สองเพศ

gender
<option value="1" data-forsex="3">ไม่แน่ใจ (Questioning)</option>
<option value="2" data-forsex="1">ชาย (male)</option>
<option value="3" data-forsex="2">หญิง (female)</option>
<option value="10" data-forsex="1">ชายที่มีเพศสัมพันธ์กับชาย (MSM)</option>
<option value="4" data-forsex="1">ชายข้ามเพศเป็นหญิง (transgender women)</option>
<option value="5" data-forsex="2">หญิงข้ามเพศเป็นชาย (transgender men)</option>
<option value="6" data-forsex="1">เกย์ (Gay man)</option>
<option value="7" data-forsex="2">เลสเปี้ยน (Lesbian)</option>
<option value="8" data-forsex="3">ไม่อยู่ในกรอบเพศชายหญิง (Gender variance/non-binary)</option>
<option value="9" data-forsex="3">ไม่ขอตอบ</option>





Clinic
1 = Pribta
2 = Tanger
3 = Research
4 = Other


come_service
Y / N


*/
/*
ไม่แน่ใจ (Questioning) > เพศกำเนิด
ชาย (male) > Male
หญิง (female) > Female
ชายที่มีเพศสัมพันธ์กับชาย (MSM) > MSM
ชายข้ามเพศเป็นหญิง (transgender women) > TGW
หญิงข้ามเพศเป็นชาย (transgender men) > TGM
เกย์ (Gay man) > MSM
เลสเปี้ยน (Lesbian) > Lesbian
ไม่อยู่ในกรอบเพศชายหญิง (Gender variance/non-binary) > Non-bi
ไม่ขอตอบ > เพศกำเนิด
*/



$aG=array("2"=>"Male","3"=>"Female","4"=>"TGW","5"=>"TGM","6"=>"MSM","7"=>"Lesbian","8"=>"Non-bi","10"=>"MSM");

$aPData = array();
$aDataId=array();
$sDataId="'service_clinic','come_service','have_visit'";
$aCurService = array();

$query ="SELECT uid,sex,gender,nation FROM patient_info 
WHERE uid IN (SELECT distinct(uid) FROM p_data_result WHERE collect_date=?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sVisitDate);

if($stmt->execute()){
  $stmt->bind_result($uid,$sex,$gender,$nation);
  while ($stmt->fetch()) {
  	$aPData[$uid]["nation"]=$nation;
  	$aPData[$uid]["gender"]=$gender;
  	if($sex=="1") $sex="Male";
  	if($sex=="2") $sex="Female";
  	if($gender=="")$gender=$sex;
  	$aPData[$uid]["sex"]=(isset($aG[$gender])?$aG[$gender]:$sex);
  }
}

//List of data_id
$query ="SELECT data_id FROM `p_form_list_data_attribute`
WHERE attr_id='tagname' AND attr_val='d_report' AND form_id='$COUNSELOR_FORM_NAME';";
$stmt = $mysqli->prepare($query);
if($stmt->execute()){
  $stmt->bind_result($data_id);
  while ($stmt->fetch()) {
  	$aDataId[$data_id]["tagname"] = "d_report";
  	$sDataId.=",'".$data_id."'";
  }
}


$query ="SELECT data_id,attr_val FROM `p_form_list_data_attribute`
WHERE attr_id='keyname' AND attr_val!='' AND form_id='PRIBTA_PROVIDER';";
$stmt = $mysqli->prepare($query);

if($stmt->execute()){
  $stmt->bind_result($data_id,$attr_val);
  while ($stmt->fetch()) {
  	$aDataId[$data_id]["keyname"] = $attr_val;
  }
}

$query = "SELECT PDR.uid,PDR.data_id,data_name_th,data_name_en,data_result
FROM p_data_result PDR

LEFT JOIN p_data_list PDL ON PDL.data_id = PDR.data_id

LEFT JOIN p_form_list_data PFI ON PFI.data_id = PDL.data_id
AND form_id = '$COUNSELOR_FORM_NAME'

WHERE PDR.collect_date = ? AND PDR.collect_time !='00:00:00' AND PDR.data_id IN ($sDataId) ;  ";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sVisitDate);

if($stmt->execute()){
  $stmt->bind_result($uid,$data_id,$data_name_th,$data_name_en,$data_result);
  while ($stmt->fetch()) {
  	//service_clinic','come_service','have_visit
  	if(isset($aPData[$uid]["service"])==false) $aPData[$uid]["service"]="";

  	if($data_id=="service_clinic"){
  		$aPData[$uid]["service_clinic"] = $data_result;
  	}else if($data_id=="come_service"){
  		$aPData[$uid]["come_service"] = $data_result;
  	}else if($data_id=="have_visit"){
  		$aPData[$uid]["have_visit"] = $data_result;
  	}else{
  		if(strpos($data_id, "_txt")>0){
  			$aPData[$uid]["service"] .= " / ".$data_result;
  		}else if(strpos($data_id,"_oth")>0){

  		}else if($data_result=="1"){
  			$aPData[$uid]["service"] .= " / ".(isset($aDataId[$data_id]["keyname"])?$aDataId[$data_id]["keyname"]:$data_name_en);
  		}else {

  		}
  		
  	}

  }
}




$aQList=array();

$query="SELECT queue,uid,collect_date,collect_time FROM i_queue_list WHERE collect_date = ? ORDER BY queue*1";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sVisitDate);

if($stmt->execute()){
  $stmt->bind_result($queue,$uid,$collect_date,$collect_time);
  while ($stmt->fetch()) {
  	$aQList[$queue]["uid"]=$uid;
  	$aQList[$queue]["coldate"]=$collect_date;
  	$aQList[$queue]["coltime"]=$collect_time;
  }
}

$mysqli->close();

$sHtml=""; $iRow=0;  $iPribta=0; $iTG=0;$iResearch=0;$iOther=0; $iSum=0;
$sTP ="";$sTT ="";$sTR ="";$sTSum = "";$sT1="";

$sTemp="<div class='row-color lh-25 row-hover'><div class='fl-wrap-row'>
					<div class='fl-fill'>";


foreach ($aQList as $queue => $aUid) {
	$bShow = true; $sUid = $aUid["uid"]; $sColD=$aUid["coldate"];$sColT=$aUid["coltime"];

	if(isset($aPData[$sUid]["service_clinic"])){
		$sClinic = $aPData[$sUid]["service_clinic"];

		$sT1 = "[".$aUid["uid"]."] ".$aPData[$sUid]["sex"];
		//if($aPData[$sUid]["nation"]!="1") $sT1.= " (ต่างชาติ)";
		if($aPData[$sUid]["nation"]=="1" || $aPData[$sUid]["nation"]=="THA") $sT1.= "";
		else if($aPData[$sUid]["nation"]=="2") $sT1.= " ต่างชาติ";
		else $sT1.=" ไม่ระบุ";


		if(isset($aPData[$sUid]["come_service"]) && $aPData[$sUid]["come_service"] == "Y")	$sT1.=" เคสเก่า";
		else $sT1.=" เคสใหม่";


		if(isset($aPData[$sUid]["have_visit"])){
			if($aPData[$sUid]["have_visit"] == "1") $sT1.=" คลินิกนัด";
		} 

		if(isset($aPData[$sUid]["service"])){
			$sT1.=" ".$aPData[$sUid]["service"];
		} 



		if($sClinic == "1") {
			$iPribta++;
			$iRow++;
			$sTP.= "$sTemp <span title='$sUid'>$iPribta</span>. $sT1</div>
			</div></div>";
			$sTSum.="$sTemp $iRow. $sT1</div>
			</div></div>";
		}else if($sClinic == "2") {
			$iTG++;
			$iRow++;
			$sTT.= "$sTemp $iTG. $sT1</div>
			</div></div>";
			$sTSum.="$sTemp $iRow. $sT1</div>
			</div></div>";
		}else if($sClinic == "3") {
			$iResearch++;
			$sTR.= "$sTemp $iResearch. $sT1</div>
			</div></div>";
			
		}else if($sClinic == "4"){
			$iOther++;
			$sT1.=" Other";
			
		}
	} 	
}



/*
foreach ($aPData as $uid => $aInfo) {
	$bShow = true;
	if(isset($aInfo["service_clinic"])){
		$sClinic = $aInfo["service_clinic"];

		$sT1 = $aInfo["sex"];
		if($aInfo["nation"]!="1") $sT1.= " (ต่างชาติ)";

		if(isset($aInfo["come_service"]) && $aInfo["come_service"] == "Y")	$sT1.=" เคสเก่า";
		else $sT1.=" เคสใหม่";


		if(isset($aInfo["have_visit"])){
			if($aInfo["have_visit"] == "1") $sT1.=" คลินิกนัด";
		} 

		if(isset($aInfo["service"])){
			$sT1.=" ".$aInfo["service"];
		} 



		if($sClinic == "1") {
			$iPribta++;
			$iRow++;
			$sTP.= "$sTemp $iPribta. $sT1</div>
			</div></div>";
			$sTSum.="$sTemp $iRow. $sT1</div>
			</div></div>";
		}else if($sClinic == "2") {
			$iTG++;
			$iRow++;
			$sTT.= "$sTemp $iTG. $sT1</div>
			</div></div>";
			$sTSum.="$sTemp $iRow. $sT1</div>
			</div></div>";
		}else if($sClinic == "3") {
			$iResearch++;
			$sTR.= "$sTemp $iResearch. $sT1</div>
			</div></div>";
			
		}else if($sClinic == "4"){
			$iOther++;
			$sT1.=" Other";
			
		}
	} 
}

*/
$selDate = date_create($sVisitDate);
$showDate = date_format($selDate,"d/m/Y");

$sHtml="
<div class='fl-fix h-25'></div>
<div class='fl-wrap-row h-25'><div class='fl-fill w-200'>Pribta : $iPribta ราย</div></div>
<div class='fl-wrap-row h-25'><div class='fl-fill w-200'>TG : $iTG ราย</div></div>
<div class='fl-wrap-row h-25'><div class='fl-fill w-200'>Research : $iResearch ราย</div></div>
<div class='fl-wrap-row h-25'><div class='fl-fill w-100'>Other : $iOther ราย</div></div>
<div class='fl-wrap-row h-25'><div class='fl-fill w-200 fw-b'>Total : ".($iPribta+$iTG+$iResearch+$iOther)." ราย</div></div>";

?>
<div class='fl-wrap-col fl-auto fs-smaller'>
	
	<div class='fl-wrap-row'>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>รายงานเคสพริบตาคลินิก</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($iPribta)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTP); ?></div>
			
		</div>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>รายงานเคสแทนเจอรีนคลินิก</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($iTG)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTT); ?></div>
			
		</div>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>รายงานเคสคลินิก</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($iPribta+$iTG)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTSum); ?></div>
			
		</div>
	</div>
	<div class='fl-wrap-col h-180 fl-auto'>
		<? echo($sHtml);	?>
	</div>
</div>