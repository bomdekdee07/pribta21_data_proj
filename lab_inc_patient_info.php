<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_front_php_function.php");
$sUid=getQS("uid");
$sColD=getQS("collect_date");
$sColT=getQS("collect_time");
if($sColD=="" || $sColD=="undefined") $sColD=getQS("coldate");
if($sColT=="" || $sColT=="undefined") $sColT=getQS("coltime");
$sShowInfo=getQS("showinfo");
$sClinicId=getSS("clinic_id");
$sToday = date("Y-m-d");
$sTime = date("H:i:s");
$sQ=getQS("q");
$sSid=getSS("s_id");
include("in_db_conn.php");

//GET list of visit . if not exist just create new.
$optVisit = ""; 
$isFound=false;
$iVCnt=0; 
$sHtml="";
$sLastD="";
$sLastT="";
$sIsARV="";
//Get Visit List
$query="SELECT clinic_id,uid,collect_date,collect_time,queue FROM i_queue_list WHERE uid=? AND collect_date !='0000-00-00' ORDER BY collect_date DESC,collect_time DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);

if($stmt->execute()){
  $stmt->bind_result($clinic_id,$uid,$collect_date,$collect_time,$queue);
  while ($stmt->fetch()) {
  	if($sLastD=="") $sLastD=$collect_date;
  	if($sLastT=="") $sLastT=$collect_time;
  	if($queue==$sQ && ($sColD=="" || $sColD=="undefined") && $collect_date==$sToday){
  		$_GET["coldate"] = $collect_date;
  		$_GET["coltime"] = $collect_time;
	}else if($queue==$sQ && $sColD==$collect_date && $sColD==$collect_time ){
  		$_GET["coldate"] = $collect_date;
  		$_GET["coltime"] = $collect_time;
	}else{

	}

	if($sQ=="" && $collect_date==$sColD && $collect_time==$sColT){
		$sQ=$queue; $_GET["q"]=$queue;
	}else if(($sQ=="" || isset($sQ)==false ) && $collect_date==$sToday && $sColT=="") {
		$sQ=$queue; $_GET["q"]=$queue;
	}

  	$optVisit.="<option value='$collect_date $collect_time' title='$clinic_id' ".((($collect_date." ".$collect_time)==($sColD." ".$sColT))?"selected":"").">".$collect_date." ".$collect_time."</option>";
  	$iVCnt++;
  	if($sToday==$collect_date) $isFound=true;
  }
}
if(getQS("coldate")=="" || $sColD=="undefined"){
	$_GET["coldate"] = $sLastD;
	$_GET["coltime"] = $sLastT;
}

if($sColD=="" || $sColD=="undefined") $sColD=getQS("coldate");
if($sColT=="" || $sColT=="undefined") $sColT=getQS("coltime");

//Get if patient is ARV
$query="SELECT data_result FROM p_data_result WHERE data_id='arv_clinic'
AND uid=? AND collect_date >= ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sUid,$sColD);

if($stmt->execute()){
  $stmt->bind_result($data_result);
  while ($stmt->fetch()) {
  	if($data_result=="1") $sIsARV = "1";
  }
}

//get Project List if exist
$optProj="";
$query="SELECT PPUL.proj_id,PP.proj_name,uid,pid,uid_status,PPUL.clinic_id,clinic_name FROM p_project_uid_list PPUL
LEFT JOIN p_project PP
ON PP.proj_id=PPUL.proj_id
LEFT JOIN p_clinic PC
ON PC.clinic_id=PPUL.clinic_id
WHERE uid=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
$sHtml="";

if($stmt->execute()){
  $stmt->bind_result($proj_id,$proj_name,$uid,$pid,$uid_status,$clinic_id,$clinic_name);
  while ($stmt->fetch()) {
  	$optProj.="<option value='$proj_id' data-pid='$pid' data-status='$uid_status' title='$clinic_name'>".$proj_name.":".$clinic_name.":".$pid."</option>";
  	$iVCnt++;
  }
}


//get patient info
$query ="SELECT uid,uic,fname,sname,en_fname,en_sname,nickname,sex,gender,date_of_birth,nation, 
citizen_id,	passport_id,id_address,id_district,id_province,id_zone,id_postal_code,use_id_address,address, district,province,zone,postal_code,country_other,tel_no,email,blood_type,line_id, em_name_1,em_relation_1,em_phone_1,em_name_2,em_relation_2,em_phone_2,religion,remark,note_all_clinic,prep_nhso FROM patient_info WHERE uid=? LIMIT 1";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
$sHtml="";
$imgPath = "";

if($stmt->execute()){
  $stmt->bind_result($uid,$uic,$fname,$sname,$en_fname,$en_sname,$nickname,$sex,$gender,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$use_id_address,$address,$district,$province,$zone,$postal_code,$country_other,$tel_no,$email,$blood_type,$line_id,$em_name_1,$em_relation_1,$em_phone_1,$em_name_2,$em_relation_2,$em_phone_2,$religion,$remark,$note_all_clinic,$prep_nhso);
  while ($stmt->fetch()) {
  	$curAge = ""; $thDOB=""; $sTHDOB=""; $sENDOB="";
	if($date_of_birth=="" || $date_of_birth=="0000-00-00"){

	}else{
		$curAge=getAgeDetail($date_of_birth);
		
		$sTHDOB = getDateText($date_of_birth,"TH");
		$sENDOB = getDateText($date_of_birth,"US","short");

		$aD = explode("-",$date_of_birth);

		$thDOB = (($aD[0]*1)+543)."-".$aD[1]."-".$aD[2];
	}
  	$imgPath = "idimg/".$citizen_id.".png";
	if(!file_exists($imgPath)){
		$imgPath = "idimg/".$uid.".png";
		if(!file_exists($imgPath)){
			$imgPath="assets/image/nophoto.jpg";
		}
		
	}
	
	if($citizen_id=="0000000000000" || $citizen_id*1==0 || $citizen_id*1==1) $citizen_id="";

 	$sHtml.= "<input id='txtSex' type='hidden' value='$sex' />
	<div class='data-row fl-wrap-row h-80 lh-20 fs-smaller' data-uid='".$sUid."' style='background-color:white'>
		".
		(($sQ=="" || $sColD!=$sToday)?"":"<div class='q-info fl-wrap-col w-60 row-color'>
					<div class='fl-fix h-15 lh-15 fw-b fl-mid bg-head-4'>
						Queue
					</div>
					<div class='fl-fill fl-mid fs-xxlarge fw-b'>
						".$sQ."
					</div>
				</div>")
		."

		<div class='fl-wrap-col w-80 row-color'>
			<div id='btnUidHistory' class='fabtn fl-fill pphoto fl-mid' title='View History'>
				<img src='".$imgPath."' class='h-65'>
			</div>
			<div class='fl-fix h-15 lh-15 fw-b fs-smaller copy-to-clip fabtn' style='text-align:center;vertical-align:top;' title='$sUid'>$sUid</div>
		</div>
		<div class='fl-fix w-2 row-color'></div>
		<div class='fl-wrap-col w-300 row-color lh-20'>
			<div class='fl-wrap-row h-20' >
				<div class='fl-fill fw-b fabtn ' id='btnEditPInfo' data-uid='$sUid' style='overflow:hidden'><i class='fa fa-edit'></i>$fname $sname</div> 
			</div>
			<div class='fl-wrap-row h-20'>
				<div class='fl-fix w-60 fs-small fw-b'>Visit</div>
				<div class='fl-fill' ><SELECT class='fill-box fs-smaller ddl-visit h-20 fw-b'  data-uid='".$sUid."'>".$optVisit."</SELECT>

				</div>
				<div class='fl-fix fl-mid w-100'>
					<input type='checkbox' ".(($iVCnt>0)?"checked":"")." readonly />เก่า <input type='checkbox' ".(($iVCnt>0)?"":"checked")."  readonly />ใหม่
				</div>
			</div>
			<div class='fl-wrap-row'>
				<div class='fl-fix fw-b w-60'>In Proj.</div><div class='fl-fill pi-text' ><SELECT id='ddlPProject' class='fontxsmall fill-box' >".$optProj."</SELECT></div> 
				<div id='btnViewProject' class='fl-fix w-30 fl-mid fabtn'><i class='fa fa-search'></i></div>
			</div>
			<div class='fl-wrap-row prow'>
				
				<div class='fl-fill fs-small'><span class='fw-b'>อายุ :</span> $curAge</div> 
				<div class='fl-fix w-150'><span class='fw-b'>เพศ : </span>".getSex($sex)."</div>
			</div>
		</div>
		<div class='fl-fix w-5 row-color'></div>
		<div class='fl-wrap-col w-230 row-color'>
			<div class='fl-wrap-row h-20 fs-small'>
				<div class='fl-fix w-20 fw-b'>ID</div>
				<div class='fl-fill' title='Thai Citizen Id'>".$citizen_id."</div> 
				<div class='fl-fix w-30 fw-b'>PPN</div>
				<div class='fl-fill fl-mid' title='Passport'>".$passport_id."</div>
			</div>
			<div class='fl-wrap-row h-20' title='Date of Birth'>
				<div class='fl-fix w-20 fw-b fl-mid' style='color:green'><i class='fa fa-birthday-cake'></i></div>
				<div class='fl-fill pi-text fl-mid'>".$sTHDOB." / ".$sENDOB."</div> 
			</div>
			<div class='fl-wrap-row h-20'>
				<div class='fl-fix w-20 fw-b fc-red fl-mid' title='กรุ๊ปเลือด'><i class='fa fa-tint'></i></div><div class='fl-fix w-50 pi-text'>".$blood_type."</div> 
				<div class='fl-fix w-30 fw-b' title='สัญชาติ'><i class='fas fa-flag'></i></div><div class='fl-fill pi-text'>".(($nation=="1" || $nation=="THA")?"ไทย":$country_other)."</div>
				<div id='btnEditRelation' class='fabtn fl-fix w-20 fl-mid' style='color:#800080' title='คู่ / Relationship'><i class='fa fa-users'></i></div>
			</div>

			<div class='fl-wrap-row h-20'>
				<div class='fl-fix w-60 fw-b'>NHSO</div>
				<div class='fl-fill'>".$prep_nhso."</div>
				<div class='fl-fix w-40 fw-b fl-mid' title='ARV Participant' style='color:red;".(($sIsARV=="1")?"":"display:none")."'>
					ARV
				</div>
			</div>
		</div>
		<div class='fl-fix w-5 row-color'></div>

		<div class='fl-wrap-col w-200 row-color'>
			<div class='fl-wrap-row  h-20'>
				<div class='fl-fix w-20 fl-mid'><i class='fas fa-pray fa-lg'></i></div>
				<div class='fl-fix w-60 fw-b'>ศาสนา</div><div class='fl-fill pi-text'>".getReligion($religion)."</div> 
			</div>
			<div class='fl-wrap-row  h-20'>
				<div class='fl-fix w-20 fl-mid'><i class='fas fa-mobile-alt fa-lg'></i></div>
				<div class='fl-fill pi-text'>".$tel_no."</div> 
			</div>
			<div class='fl-wrap-row  h-20'>
				<div class='fl-fix w-20 fl-mid' style='color:pink'><i class='far fa-envelope fa-lg' ></i></div>
				<div class='fl-fill pi-text'>$email</div> 
			</div>
			<div class='fl-wrap-row  h-20'>
				<div class='fl-fix w-20 fl-mid' style='color:#06c152'><i class='fab fa-line fa-lg'></i></div>
				<div class='fl-fill pi-text'>$line_id</div> 
			</div>
		</div>
		<div class='fl-fix w-5 row-color'></div>
		<div class='fl-wrap-col'>
			<div class='fl-wrap-row row-color h-15'>
				<div class='fl-fix fl-mid-left wper-35 h-fill font-s-1 fw-b'>Remark:</div>
				<div class='fl-fix fl-mid-left wper-25 h-fill font-s-1 fw-b'>Note to all:</div>
				<div class='fl-fill'></div>
				<div class='fl-fix fl-mid wper-10'>
					<button name='bt_update_patient_info' data-uid='$sUid' class='btn btn-warning font-s-1' style='padding: 0px 11px 0px 11px; font-weight: bold;'>Update</button>
				</div>
			</div>
			<div class='fl-wrap-row row-color'>
				<div class='fl-fix fl-mid-left wper-35 h-fill'>
					<textarea class='h-fill' style='resize: none;width:99.9%; height: 95%; background-color: #EFF0EF;' readonly>$remark</textarea>
				</div>
				<div class='fl-fix fl-mid-left wper-65 h-fill'>
					<textarea name='note_all_clinic' id='note_all_clinic' class='h-fill font-s-1' style='resize: none;width: 99.8%; height: 95%;'>$note_all_clinic</textarea>
				</div>
			</div>
		</div>

	</div>";
	}
}



$mysqli->close();


?>

<div id='divLIPI' class='fl-wrap-col h-80' data-showinfo='<? echo($sShowInfo); ?>'>
	<? echo($sHtml); ?>
</div>

<script>
	$(function(){
		// BT update patient_info note all
		$("#divLIPI [name=bt_update_patient_info]").off("click");
		$("#divLIPI [name=bt_update_patient_info]").on("click", function(){
			var sUid = $(this).attr("data-uid");
			var sNoteAll = $("#divLIPI [name=note_all_clinic]").val();

			var aData = {
				uid: sUid,
				note_all: sNoteAll
			};
			// console.log(aData);

			if(confirm("ต้องการบันทึกข้อมูลหรือไม่?")){
				$.ajax({
					url: "lab_inc_patient_info_upd_ajax.php",
					method: "POST",
					data: aData,
					cache: false,
					success: function(sResult){
						// console.log("update: "+sResult);
						if(sResult == "1"){
							$.notify("Save Data", "success");
						}
						else{
							$.notify("Save Data", "Not success");
						}
					}
				})
			}
		});

		$("#divLIPI #btnViewProject").off("click");
		$("#divLIPI #btnViewProject").on("click",function(){
			oD = $(this).closest("#divLIPI");
			sProjId=$(oD).find("#ddlPProject").val();
			oDR=$(this).closest(".data-row");
			sUid=$(oDR).attr("data-uid");
			if(sProjId=="") return;
			sUrl="ext_index.php?file=project_inc_uid_visit&projid="+sProjId+"&uid="+sUid;
			window.open(sUrl, "ProjView", "width="+screen.availWidth+",height="+screen.availHeight);
			return false;
		});


		$("#divLIPI #btnEditRelation").off("click");
		$("#divLIPI #btnEditRelation").on("click",function(){
			sUid =  $(this).closest(".data-row").attr("data-uid");
			let sUrl = "patient_relation_main.php?uid="+sUid;
			showDialog(sUrl,"รายการความสัมพันธ์ :"+sUid,"320","480","","",false,function(){
			});
		});
		$("#divLIPI #btnUidHistory").off("click");
		$("#divLIPI #btnUidHistory").on("click",function(){
			var sUid = $(this).closest(".data-row").attr("data-uid");

			if(sUid=="P99-99999" || sUid=="P00-00000" ){
				$.notify("ไม่รองรับ UID ใช้เฉพาะภายในคลินิก");
				return;
			}
			showDialog("patient_inc_dx_history.php?uid="+sUid,"History : "+sUid,"90%","310","",
			function(sResult){

			},false,function(){
				//Load Done Function
			},false,"0","0","P_HISTORY");
		
		});
		
		$("#divLIPI #btnEditPInfo").off("click");
		$("#divLIPI #btnEditPInfo").on("click",function(){
			var sUid = $(this).attr("data-uid");
			let sT = $("#divLIPI .ddl-visit").val();
			let aD=['0000-00-00','00:00:00'];
			if(sT+"" !="" && sT !=null) aD=sT.split(" ");
			let sColD=aD[0];
			let sColT=aD[1];
			showDialog("patient_inc_info.php?hideinfo=1&hidedit=1&"+qsTxt(sUid,sColD,sColT),"Edit Patient Info : "+sUid,"90%","1180","",
			function(sResult){
				if(sResult=="REFRESH"){
					var sShowInfo = $("#divLIPI").attr("data-showinfo");
					$("#divLIPI").parent().load("phar_inc_patient_info.php?"+qsTxt(sUid,sColD,sColT)+"&showinfo="+sShowInfo);
				}
			},false,function(){
				//Load Done Function
			});
		});






	});
</script>