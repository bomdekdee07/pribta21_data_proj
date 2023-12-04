<?
include("in_session.php");
include_once("in_php_function.php");

include_once("in_setting_row.php");
$isOpt = getQS("opt");

$sUid=getQS("uid");
$sColDate=getQS("collect_date");
$sColTime=getQS("collect_time");

if($sColDate=="") $sColDate=getQS("coldate");
if($sColTime=="") $sColTime=getQS("coltime");

$sHtml="";
if($sUid=="" || $sColDate=="" || $sColTime==""){

$sHtml = "Missing Parameter (UID,coldate,coltime)";

}else{
	include("in_db_conn.php");
//GET FORM
	$aLabInfo=array();
	$query="SELECT lab_order_id,PLT.lab_id,lab_result_type,lab_name,lab_unit,lab_note,lab_result_min,lab_result_max,lab_result_min_male,lab_result_max_male,lab_result_min_female,lab_result_max_female,is_disable,lab_std_male_txt,lab_std_female_txt
	FROM p_lab_order PLO
	LEFT JOIN p_lab_order_lab_test PLOLT
	ON PLO.uid=PLOLT.uid
	AND PLO.collect_date=PLOLT.collect_date
	AND PLO.collect_time=PLOLT.collect_time
	JOIN p_lab_test PLT
	ON PLT.lab_id=PLOLT.lab_id
	LEFT JOIN p_lab_test_result_hist PLTRH
	ON PLTRH.lab_id = PLT.lab_id
	AND PLTRH.start_date <= PLO.collect_date
	AND PLTRH.stop_date >= PLO.collect_date
	JOIN p_lab_result lab_rs on(lab_rs.lab_id = PLOLT.lab_id and lab_rs.uid = PLOLT.uid AND lab_rs.collect_date = PLOLT.collect_date AND lab_rs.collect_time = PLOLT.collect_time and lab_rs.lab_result_status != 'L0')
	WHERE PLO.uid=? AND PLO.collect_date=? AND PLO.collect_time=? AND PLT.is_disable=0
	ORDER BY PLT.lab_group_id, PLT.lab_id2
	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($lab_order_id,$lab_id,$lab_result_type,$lab_name,$lab_unit,$lab_note,$lab_result_min,$lab_result_max,$lab_result_min_male,$lab_result_max_male,$lab_result_min_female,$lab_result_max_female,$is_disable,$lab_std_male_txt,$lab_std_female_txt);
	  while ($stmt->fetch()) {
	  	$aLabInfo[$lab_id]["lab_order_id"]=$lab_order_id;
	  	$aLabInfo[$lab_id]["lab_result_type"]=$lab_result_type;
	  	$aLabInfo[$lab_id]["lab_name"]=$lab_name;
	  	$aLabInfo[$lab_id]["lab_unit"]=$lab_unit;
	  	$aLabInfo[$lab_id]["lab_note"]=$lab_note;
	  	$aLabInfo[$lab_id]["lab_result_min"]=$lab_result_min;
	  	$aLabInfo[$lab_id]["lab_result_max"]=$lab_result_max;
	  	$aLabInfo[$lab_id]["lab_result_min_male"]=$lab_result_min_male;
	  	$aLabInfo[$lab_id]["lab_result_max_male"]=$lab_result_max_male;
	  	$aLabInfo[$lab_id]["lab_result_min_female"]=$lab_result_min_female;
	  	$aLabInfo[$lab_id]["lab_result_max_female"]=$lab_result_max_female;
	  	$aLabInfo[$lab_id]["is_disable"]=$is_disable;
	  	$aLabInfo[$lab_id]["lab_std_male_txt"]=$lab_std_male_txt;
	  	$aLabInfo[$lab_id]["lab_std_female_txt"]=$lab_std_female_txt;
	  }
	}


	$aLabSub=array();
//GET LIST OF CHOICE
	$query="SELECT lab_id,lab_txt_id,lab_txt_name,is_normal
	 FROM p_lab_test_result_txt

	WHERE lab_id IN (SELECT lab_id from p_lab_order_lab_test WHERE
	uid=? AND collect_date=? AND collect_time=?)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($lab_id,$lab_txt_id,$lab_txt_name,$is_normal);
	  while ($stmt->fetch()) {
	  	$aLabSub[$lab_id][$lab_txt_id]["name"]=$lab_txt_name;
	  	$aLabSub[$lab_id][$lab_txt_id]["is_normal"]=$is_normal;
	  }
	}



//GET LAB RESULT
	$aLabResult=array();
	$query ="SELECT PLOLT.lab_id,PLR.lab_result,lab_result_report,PLR.lab_result_note,lab_result_status,external_lab,time_lastupdate,time_confirm,is_paid,paid_datetime FROM p_lab_order_lab_test PLOLT
	LEFT JOIN p_lab_test PLT
	ON PLT.lab_id = PLOLT.lab_id
	LEFT JOIN p_lab_result PLR
	ON PLR.uid=PLOLT.uid
	AND PLR.collect_date=PLOLT.collect_date
	AND PLR.collect_time=PLOLT.collect_time
	AND PLR.lab_id=PLOLT.lab_id


	WHERE  PLT.is_disable=0 AND  PLOLT.uid=? AND PLOLT.collect_date=? AND PLOLT.collect_time=? AND PLR.lab_result_status != 'L0' ORDER BY external_lab,PLT.lab_group_id,PLT.lab_id2";


	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
	$sHtml = "";
	if($stmt->execute()){
	  $stmt->bind_result($lab_id,$lab_result,$lab_result_report,$lab_result_note,$lab_result_status,$external_lab,$time_lastupdate,$time_confirm,$is_paid,$paid_datetime);
	  while ($stmt->fetch()) {
	  	$aLabResult[$lab_id]["lab_id"]=$lab_id;
	  	$aLabResult[$lab_id]["lab_result"]=$lab_result;
	  	$aLabResult[$lab_id]["lab_result_report"]=$lab_result_report;
	  	$aLabResult[$lab_id]["lab_result_note"]=$lab_result_note;
	  	$aLabResult[$lab_id]["lab_result_status"]=$lab_result_status;
	  	$aLabResult[$lab_id]["external_lab"]=$external_lab;
	  	$aLabResult[$lab_id]["time_lastupdate"]=$time_lastupdate;
	  	$aLabResult[$lab_id]["time_confirm"]=$time_confirm;
	  	$aLabResult[$lab_id]["is_paid"]=$is_paid;
	  	$aLabResult[$lab_id]["paid_datetime"]=$paid_datetime;
	  }
	}
	$mysqli->close();

	foreach ($aLabResult as $lab_id => $aLabR) {
		$aTempSub = (isset($aLabSub[$lab_id])?$aLabSub[$lab_id]:array());
		$aLabI=$aLabInfo[$lab_id];

		//Reference
		$sTempRef = "";
		if($aLabI["lab_std_male_txt"] == $aLabI["lab_std_female_txt"]){
			$sTempRef=$aLabI["lab_std_male_txt"];
		}else{
			$sTempRef="<div><b>Male :</b>".$aLabI["lab_std_male_txt"]."</div>
			<div><b>Female :</b>".$aLabI["lab_std_female_txt"]."</div>";
		}
		/*
		if(isset($aLabSub[$lab_id])){
			//Item is select used labsub value
			$sTempRef = (isset($aLabSub[$lab_id][$aLabR["lab_result"]]["name"])?$aLabSub[$lab_id][$aLabR["lab_result"]]["name"]:"");
		} else{

			//Item is text box
			//For male
			if($aLabI["lab_result_min_male"]!=""){
				$sTempRef .= "<div><b>Male : </b> ";
				if($aLabI["lab_result_min_male"] == $aLabI["lab_result_min"]){
					//Don't show minimum just used <
					$sTempRef .= " &#60;";
				}else{
					//Use range
					$sTempRef .= $aLabI["lab_result_min_male"] ." - ";
				}
				$sTempRef .= $aLabI["lab_result_max_male"]." ".$aLabI["lab_unit"]."</div>";
			}
			if($aLabI["lab_result_min_female"]!=""){
				$sTempRef .= "<div><b>Female : </b> ";

				if($aLabI["lab_result_min_female"] == $aLabI["lab_result_min"]){
					//Don't show minimum just used <
					$sTempRef .= " &#60;";
				}else{
					//Use range
					$sTempRef .= $aLabI["lab_result_min_female"] ." - ";
				}
				$sTempRef .= $aLabI["lab_result_max_female"]." ".$aLabI["lab_unit"]."</div>";
			}

		}
		*/
		$color_result="";
		$isPaid = "<span style='color:green'><i class='fas fa-dollar-sign fa-lg'></i> Paid</span>";
		if($aLabR["lab_result_status"]=="L1") $color_result ="result-green";
		else if($aLabR["lab_result_status"]=="L2") $color_result ="result-red";

		$sHtml.="
		<div class='fl-wrap-row lab-row row-color h-60 pbtn' data-labid='".$lab_id."' data-isconfirm='".(($aLabR["time_confirm"]=="0000-00-00 00:00:00" || is_null($aLabR["time_confirm"]) || $aLabR["time_confirm"]=="" )?"0":"1"  )."' data-orderid='".$aLabI["lab_order_id"]."'>
			<div class='fl-fix w-30 fl-mid'><input  type='checkbox' name='lablist[]' class='bigcheckbox chklabid' value='".$aLabR["lab_id"]."' checked='checked' /></div>
			<div class='fl-wrap-col w-200 lab-id lh-30'>
				<div class='fl-fill ptxt-b'>".$aLabI["lab_name"]."</div>
				<div class='fl-fix h-20 fs-xsmall lh-20'>".$aLabR["time_confirm"]."</div>
			</div>
			<div class='fl-fix lab-paid lh-30 w-80 fl-mid title='".$aLabR["paid_datetime"]."'>".(($aLabR["is_paid"]=="1")?$isPaid:"")."
			</div>
			<div class='fl-wrap-col lab-result'>
				<div class='fl-fill lh-20'>".getLabControl($aLabR,$aLabI,$aTempSub)."</div>
				<div class='fl-fix w-80 h-20 fs-xsmall'>".(($aLabI["lab_unit"]=="N/A")?"":$aLabI["lab_unit"])."</div>
			</div>
			<div class='fl-fix lab-external w-30 fl-mid' title='External Lab'>
				<input type='checkbox' class='lab_save lexternal bigcheckbox' data-odata='".(($aLabR["external_lab"]=="")?"0":$aLabR["external_lab"])."' ".(($aLabR["external_lab"]=="1")?"checked":"")." />
			</div>
			<div class='fl-wrap-col lab-result-report '>
				<div class='fl-fill  lh-30'><input class='lab_save lreport w-fill $color_result ' data-odata='".$aLabR["lab_result_report"]."' value='".$aLabR["lab_result_report"]."' /></div>
				<div class='fl-fix h-20 lh-20 fs-xsmall al-left'>".$aLabR["time_lastupdate"]."</div>
			</div>
			<div class='fl-wrap-col lab-ref fl-auto v-mid'>
				".$sTempRef."
			</div>
			<div class='fl-fix lab-status w-80 fl-mid'>
				<SELECT class='lab_save lstatus w-fill' data-odata='".(($aLabR["lab_result_status"]=="")?"L0":$aLabR["lab_result_status"])."'>
				<option value='L0' ".(($aLabR["lab_result_status"]=="L0")?"selected":"").">Pending</option>
				<option value='L1' ".(($aLabR["lab_result_status"]=="L1")?"selected":"").">Yes</option>
				<option value='L2' ".(($aLabR["lab_result_status"]=="L2")?"selected":"").">No</option>
				</SELECT>
			</div>
			<div class='fl-fill lab-note fl-mid'><textarea class='w-fill h-fill lab_save lnote' data-odata='".$aLabR["lab_result_note"]."' placeholder='Result note' >".$aLabR["lab_result_note"]."</textarea>
			</div>
		</div>";

	}

}

function getLabControl($aLabResult,$aLabInfo,$aLabSub){
	$sControl="";
	if(count($aLabSub)>0){
		//Item is SELECT
		$sControl.="<SELECT class='lab_save lresult' data-odata='".$aLabResult["lab_result"]."'
		data-min='".$aLabInfo["lab_result_min"]."'
		data-max='".$aLabInfo["lab_result_max"]."'
		data-minm='".$aLabInfo["lab_result_min_male"]."'
		data-maxm='".$aLabInfo["lab_result_max_male"]."'
		data-minf='".$aLabInfo["lab_result_min_female"]."'
		data-maxf='".$aLabInfo["lab_result_max_female"]."'
		><option value=''>Pending</option>";
		foreach ($aLabSub as $lab_txt_id => $aSub) {
			$sControl.="<option value='".$lab_txt_id."' data-isnormal='".$aSub["is_normal"]."' ".(($aLabResult["lab_result"]==$lab_txt_id)?"selected":"").">".$aSub["name"]."</option>";
		}
		$sControl.="</SELECT>";
	}else{
		$sControl.="<input class='lab_save lresult' data-ltype='".$aLabInfo["lab_result_type"]."' data-odata='".$aLabResult["lab_result"]."'
		data-min='".$aLabInfo["lab_result_min"]."'
		data-max='".$aLabInfo["lab_result_max"]."'
		data-minm='".$aLabInfo["lab_result_min_male"]."'
		data-maxm='".$aLabInfo["lab_result_max_male"]."'
		data-minf='".$aLabInfo["lab_result_min_female"]."'
		data-maxf='".$aLabInfo["lab_result_max_female"]."'
		value='".$aLabResult["lab_result"]."' readonly='true' />";
	}

	return $sControl;
}
echo($sHtml);
?>
