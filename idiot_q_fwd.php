<?
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sUid =getQS("uid");
$sQ = getQS("q");
$sToday = date("Y-m-d");

//This one is fixed. Please edit this one if new form is used.
$DEMO_FORM_NAME = "DEMO_PRIBTA";


if($sUid==""){
	echo("No uid is provided.");
	exit();
}
$sJs = "";
include("in_db_conn.php");

$query = "SELECT uid,uic,fname,sname,nickname,clinic_type,sex,date_of_birth,nation,citizen_id,passport_id,id_address,id_district,id_province,id_zone,id_postal_code,country_other,tel_no,email,line_id,remark FROM patient_info WHERE uid=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);

$sHtml="";
if($stmt->execute()){
  $stmt->bind_result($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark );
  while ($stmt->fetch()) {
  	$sHtml.=	$sIDPhoto = "idimg/".$citizen_id.".png";
	if(file_exists($sIDPhoto)){

	}else{
		$sIDPhoto = "assets/image/nophoto.jpg";
	}
	$sTHDOB=$date_of_birth;
	$sDCDOB=$date_of_birth;
	
	if($date_of_birth!="") {
		$aT = explode("-",$date_of_birth);
		if(count($aT)>1){
			$sTHDOB=(($aT[0]>2400)?$aT[0]:(($aT[0]*1) + 543))."-".$aT[1]."-".$aT[2];
			$sDCDOB=(($aT[0]>2400)?(($aT[0]*1) + 543):$aT[0])."-".$aT[1]."-".$aT[2];
		}
	}

	$sex = (($sex=="1")?"M":(($sex=="2")?"F":""));
	
	$sSexIcon = "";
	if($sex=="M"){
		$sSexIcon="<span><i class='fas fa-mars fa-lg'></i></span>";
	}else if($sex=="F"){
		$sSexIcon="<span style='color:pink'><i class='fas fa-venus fa-lg'></i></span>";
	}else{
		$sSexIcon = $sex;
	}

	$sHtml = "
	<div class='fl-wrap-row h-sm row-color-2' style=';text-align:left'>
		<div class='fl-wrap-col w-sm'>
			<div class='fl-fill fl-mid'>
				<input id='txtFwdQueue' class='fl-fill' value='$sQ' readonly='true' style='font-size:30px;height:60px;width:60px;text-align: center' />
			</div>
			<div class='fl-fix h-xs'>
				<span>$uid</span>
			</div>
		</div>
		<div class='fl-wrap-col w-ms'>
			<div class='fl-fill fl-mid uid-fwd'>
				<img style='width:50px;border:1px solid silver' src='".$sIDPhoto."'  />
			</div>
		</div>
		<div class='fl-wrap-col w-xs'>
			<div class='fl-fill fl-mid'>
				$sSexIcon
			</div>
		</div>
		<div class='fl-wrap-col w-l fs-s' style='line-height:15px'>
			<div class='fl-fill'>
				<span style='font-weight:bold'>เกิด คศ.</span> <span>$sDCDOB</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>เกิด พศ.</span> <span>$sTHDOB</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>ID:</span> <span>$citizen_id</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>Passport:</span> <span>$passport_id</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>UIC:</span> <span>$uic</span>
			</div>

		</div>
		<div class='fl-wrap-col fs-s style='line-height:15px;border-right:1px solid silver'>
			<div class='fl-fix h-xs'>
				$fname $sname $nickname
			</div>
			<div class='fl-fill' style='line-height:15px'>
				<span style='font-weight:bold'>ที่อยู่ : </span>$id_address $id_province $id_district $id_zone $id_postal_code
			</div>
		</div>

		<div class='fl-wrap-col fs-s fl-auto w-l' style='line-height:15px'>
			<textarea style='height:99%'>$remark</textarea>
		</div>
		<div class='fl-wrap-col w-xl fs-s' style='line-height:15px;margin-left:20px'>
			<div class='fl-fill'>
				<span style='margin-right:10px'><i class='fas fa-mobile-alt'></i></span><span class='copy-to-clip'>$tel_no</span>
			</div>
			<div class='fl-fill'>
				<span style='margin-right:5px'><i class='far fa-envelope'></i></span><span class='copy-to-clip'>$email</span>
			</div>
			<div class='fl-fill'>
				<span style='margin-right:2px'><i class='fab fa-line fa-lg' style='color:#06c152'></i></span><span class='copy-to-clip'>$line_id</span>
			</div>

		</div>


	</div>";
  	
  }
}

$sRoomList=""; $aRoomCount = array();
$sNoteToAll = "";

//Note to all for the current visit only
	$query = "SELECT note_to_all FROM k_queue_row_detail WHERE time_record > '$sToday' AND queue_row_detail=? ORDER BY time_record DESC LIMIT 1;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sQ);
	if($stmt->execute()){
	  $stmt->bind_result($notetoall);
	  while ($stmt->fetch()) {
	  	//$sNoteToAll = rawurlencode($notetoall);
	  	//$sJs .= "$(\"#divOldFwd #txtNoteToAll\").val(\"\"+(decodeURIComponent('".($notetoall)."')));";
	  	$sNoteToAll = $notetoall;
	  }
	}



// SUM each room
	$query = "SELECT id_room FROM k_queue_row_detail_history WHERE id IN( SELECT Max(id) FROM k_queue_row_detail_history KQRDH WHERE time_record > ? AND from_qrd_id !=0 GROUP BY from_qrd_id ORDER BY time_record DESC)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sToday);
	if($stmt->execute()){
	  $stmt->bind_result($id_room);
	  while ($stmt->fetch()) {
	  	$aRoomCount[$id_room] = (isset($aRoomCount[$id_room])?$aRoomCount[$id_room]:0)+1;
	  }
	}
// SUM each room

//List staff in the room
//Room List
	$query = "SELECT room_number,room_detail,s_name,KR.time_record,room_status FROM k_room KR
	LEFT JOIN p_staff PS
	ON KR.room_who = PS.s_id ORDER BY room_number*1";
	//WHERE room_status IN ('0','1');";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($room_number,$room_detail,$s_name,$time_record,$room_status);
	  while ($stmt->fetch()) {
	  	$sRoomList .= "
	  	<div class='fl-wrap-row h-ss row-hover row-color'>
	  		<div class='fl-fix w-ms'>".$room_number."</div>
	  		<div class='fl-fill'>".$room_detail."</div>
	  		<div class='fl-fill'>".(( $time_record > $sToday)?$s_name:"")."</div>
	  		<div class='fl-fix w-ms'>".(isset($aRoomCount[$room_number])?$aRoomCount[$room_number]:"0")."</div>
	  		
	  		<div class='fl-fix w-ms'><input class='bigcheckbox' type='radio' name='room_number' value='$room_number' /></div>
	  	</div>
	  	";
	  }
	}

/*	'serv_consult','serv_prep','serv_pep','serv_art','serv_buymed',
	'serv_hormone','serv_blood_test','serv_sti_test','serv_hiv','serv_vaccine','serv_internal','serv_online','serv_telemed','serv_schedule','serv_treatment','serv_research',
	'serv_certificate','serv_oth_txt','serv_oth'*/

//Service
	$sService="";
	$query = "SELECT PDR.data_id,data_name_th,data_name_en,data_result
	FROM p_data_result PDR

	LEFT JOIN p_data_list PDL
	ON PDL.data_id = PDR.data_id

	LEFT JOIN p_form_list_data PFI
	ON PFI.data_id = PDL.data_id

	WHERE PDR.data_id IN (
		SELECT data_id FROM p_form_list_data WHERE form_id='$DEMO_FORM_NAME' AND data_seq > 100 AND data_seq < 330	)
	AND PDR.uid = ? AND collect_date = ?  AND PFI.form_id = '$DEMO_FORM_NAME'
	ORDER BY data_seq*1
	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sUid,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($data_id,$data_name_th,$data_name_en,$data_result );
	  while ($stmt->fetch()) {
	  	if($data_result=="1" && $data_id != "serv_oth_txt"){
		  	$sService .= "
		  	
		  		<input type='radio' checked readonly='true' />$data_name_th/$data_name_en <br/>
		  	
		  	";
	  	}else if($data_id = "serv_oth_txt"){
	  		$sService .= "อื่นๆ :$data_result <br/>
	  		";
	  	}

	  }
	}
$sCurService="";
//Today Services
	$query = "SELECT PDR.data_id,data_name_th,data_name_en,data_result
	FROM p_data_result PDR

	LEFT JOIN p_data_list PDL
	ON PDL.data_id = PDR.data_id

	LEFT JOIN p_form_list_data PFI
	ON PFI.data_id = PDL.data_id

	WHERE PDR.data_id IN ('serv_coun_hiv','serv_coun_sti_test','serv_coun_prep','serv_coun_pep','serv_coun_treatment','serv_coun_hormone','serv_coun_internal','serv_coun_blood_test','serv_coun_buymed','serv_coun_consult','serv_coun_research','serv_coun_online','serv_coun_certificate','serv_coun_telemed','serv_coun_vaccine','serv_coun_art','serv_coun_oth','serv_coun_oth_txt')

	AND PDR.uid = ? AND collect_date = ?  AND PFI.form_id = 'PRIBTA_PROVIDER'
	ORDER BY data_seq
	";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sUid,$sToday);

	if($stmt->execute()){
	  $stmt->bind_result($data_id,$data_name_th,$data_name_en,$data_result );
	  while ($stmt->fetch()) {
	  	if($data_result=="1" && $data_id != "serv_oth_txt"){
		  	$sCurService .= "
		  		<input type='radio' checked readonly='true' />$data_name_th/$data_name_en <br/>
		  	
		  	";
	  	}else if($data_id = "serv_oth_txt"){
	  		$sCurService .= "อื่นๆ :$data_result <br/>
	  		";
	  	}

	  }
	}



$sSaleOpt ="";
//sSaleOpt
	$query = "SELECT sale_opt_id,sale_opt_name FROM sale_option WHERE is_enable=1 ORDER BY data_seq";
	//WHERE room_status IN ('0','1');";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($sale_opt_id,$sale_opt_name);
	  while ($stmt->fetch()) {
	  	$sSaleOpt .= "<option value='$sale_opt_id'>$sale_opt_name</option>";
	  }
	}

$sHistory="";
//History
	$query = "SELECT KQRD.id,KQRDH.time_record,id_room,KR.room_detail
	FROM k_queue_row_detail KQRD
	LEFT JOIN k_queue_row_detail_history KQRDH
	ON KQRDH.from_qrd_id = KQRD.id
	LEFT JOIN k_room KR
	ON KR.room_number = KQRDH.id_room

	WHERE queue_row_detail = ? AND KQRD.time_record > ?
	ORDER BY KQRDH.time_record
	";
	
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sQ,$sToday);
	if($stmt->execute()){
	  $stmt->bind_result($id,$time_record,$id_room,$room_detail);
	  while ($stmt->fetch()) {
	  	$sTime = substr($time_record,10);
	  	$sHistory .= "<div class='fl-wrap-row h-xs fs-s' style='text-align:left'>
	  		<div class='fl-fix w-s'>$sTime</div>
	  		<div class='fl-fill'>[$id_room] $room_detail</div>
	  	</div>";
	  }
	}



$mysqli->close();

?>
<style>

	.pinfo-item{
		float:left;
	}
</style>
<div id='divOldFwd' class='fl-wrap-col'>
	<div class='fl-wrap-row h-sm' style='border-bottom: 1px solid silver'>
		<div class='fl-wrap-row' style='line-height: 15px'>
			<? echo($sHtml); ?>
		</div>
	</div>
	<div class='fl-wrap-row'>
		<div class='fl-wrap-col fl-auto' style='text-align:left;max-width: 30%'>
			<div class='fl-fix h-xs row-color-2 fl-mid'> บริการที่ต้องการรับในวันนี้</div>
			<div class='fl-fill fs-s fl-auto'>
				<? echo($sService); ?>
			</div>
		</div>
		<div class='fl-wrap-col fl-auto' style='text-align:left;max-width: 30%;border-left:1px solid black'>
			<div class='fl-fix h-xs row-color-2 fl-mid'> บริการที่ได้รับในวันนี้</div>
			<div class='fl-fill fs-s fl-auto'>
				<? echo($sCurService); ?>
			</div>
		</div>
		<div class='fl-wrap-col'>
			<div class='fl-wrap-col h-s' style='text-align:left;line-height: 15px'>
				<div class='fl-fix h-xs row-color-2 fl-mid' > ส่วนลด</div>
				<div class='fl-fill fl-mid'><SELECT class='fill-box' id='ddlSaleOpt'><? echo($sSaleOpt); ?></SELECT></div>
			</div>
			<div class='fl-wrap-row'>
				<div class='fl-wrap-col'>
					<div class='row-color-2'> Note to All</div>
					<textarea id='txtNoteToAll' style='height: 100%' ><? echo($sNoteToAll); ?></textarea>
				</div>
				<div class='fl-wrap-col'>
					<div class='fl-fix row-color-2 h-xs'> History</div>
					<div class='fl-fill fl-auto'>
						<? echo($sHistory); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
	<div class='fl-wrap-col h-xs'>
	  	<div class='fl-wrap-row h-ss row-color-2 fs-s' style='line-height: 20px;font-weight: bold'>
	  		<div class='fl-fix w-ms'>Room</div>
	  		<div class='fl-fill'>Room Name</div>
	  		<div class='fl-fill'>Name</div>
	  		<div class='fl-fix w-ms'>Total</div>
	  		<div class='fl-fix w-ms'>#</div>
	  		<div class='fl-fix' style='max-width: 20px;min-width: 20px'> </div>
	  	</div>
	</div>
	<div class='fl-wrap-col h-l fl-auto' >

		<? echo($sRoomList); ?>
	</div>
	<div class='fl-fix h-s fl-mid'>
		<input id='btnUidFwd' class='fs-m row-hover fl-mid' type='button' value='ส่งต่อ' style='background-color: orange;height:90%' />
		<i id='btnUidFwd-loader' class='fa fa-spinner fa-spin fa-4x' style='display:none'></i>
	</div>
</div>


<script>
	$(document).ready(function(){
		<? echo($sJs); ?>

		$("#divOldFwd #btnUidFwd").unbind("click");
		$("#divOldFwd #btnUidFwd").on("click",function(){
			sRoomNo = $("#divOldFwd").find("input[name='room_number']:checked").val();
			if(sRoomNo!=undefined) {
				$("#divOldFwd").find("input[name='room_number']:checked").focus();
				let sNote = $("#txtNoteToAll").val();
				sSaleId = $("#ddlSaleOpt").val();
				sSaleTxt = $("#ddlSaleOpt option:selected").text();
				sQ =$("#divOldFwd #txtFwdQueue").val();
				var aData = {u_mode:"room_forward_old",q:sQ,fwdroom:sRoomNo,notetoall:sNote,saleid:sSaleId,saletxt:sSaleTxt};
				startLoad($("#divOldFwd #btnUidFwd"),$("#divOldFwd #btnUidFwd-loader"));
				callAjax("room_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res=="0"){
						alert(rtnObj.msg);
						endLoad($("#divOldFwd #btnUidFwd"),$("#divOldFwd #btnUidFwd-loader"));
					}else{
						$.notify("Save Complete","success");
						setTimeout(closeThis,2000);
					}
					
				});
				
			}else{
				$.notify("Please select room to fwd.");
				return;
			}
		});

		function closeThis(){
			closeDlg($("#divOldFwd"),"1");
		}

	});

</script>