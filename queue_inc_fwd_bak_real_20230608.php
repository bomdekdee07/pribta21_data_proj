<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sUid =getQS("uid");
$sQ = getQS("q");
$sToday = date("Y-m-d");
$sClinicID = getSS("clinic_id");
$sSelRoom=getQS("selroom");
$sColD=getQS("coldate",$sToday);
$sColT=getQS("coltime");


//This one is fixed. Please edit this one if new form is used.
$DEMO_FORM_NAME = "DEMO_PRIBTA";
$PROVIDER_FORM_NAME = "PRIBTA_PROVIDER";

$sRoomList=""; $aRoomCount = array(); $sNoteToAll = ""; $sColT = ""; $sCurOptId="";


if($sClinicID==""){
	echo("Please Login");
	exit();
}

if($sUid==""){
	echo("No uid is provided.");
	exit();
}
$sJs = "";
include("in_db_conn.php");

//Patient info
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
//Patient Info


//Note to all for the current visit only

/*
	$query = "SELECT queue_note,collect_time FROM i_queue_list WHERE collect_date = '$sToday' AND queue=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sQ);
	if($stmt->execute()){
	  $stmt->bind_result($notetoall,$collect_time);
	  while ($stmt->fetch()) {
	  	$sNoteToAll = $notetoall;
	  	$sTime=$collect_time;
	  }
	}
*/

//Note to all 
/*
('serv_consult','serv_prep','serv_pep','serv_art','serv_buymed',
	'serv_hormone','serv_blood_test','serv_sti_test','serv_hiv','serv_vaccine','serv_internal','serv_online','serv_telemed','serv_schedule','serv_treatment','serv_research',
	'serv_certificate','serv_oth_txt','serv_oth')
*/



$sUidRoom = "";
// SUM each room
	$query = "SELECT room_no,uid,collect_time,sale_opt_id,queue,queue_note FROM i_queue_list WHERE clinic_id=? AND collect_date = ? ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sClinicID,$sToday);
	if($stmt->execute()){
	  $stmt->bind_result($room_no,$uid,$collect_time,$sale_opt_id,$queue,$queue_note);
	  while ($stmt->fetch()) {
	  	if($queue==$sQ) {
	  		$sUidRoom= $room_no;
	  		$sColT=$collect_time;
	  		//$sNoteToAll=$queue_note;
	  	}
	  	$aRoomCount[$room_no] = (isset($aRoomCount[$room_no])?$aRoomCount[$room_no]:0)+1;

	  	if($queue==$sQ){
		  	//$sNoteToAll = $queue_note;
		  	$sColT=$collect_time;	  
		  	$sCurOptId=$sale_opt_id;		
	  	}

	  }
	}

	$query = "SELECT data_result FROM p_data_result WHERE data_id='cn_patient_note' AND uid=? AND  collect_date = ? AND  collect_time = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
	  $stmt->bind_result($data_result);
	  while ($stmt->fetch()) {
	  	$sNoteToAll = $data_result;
	  }
	}


// SUM each room

//List staff in the room
	
//Room List
	$query = "SELECT IRL.room_no,room_detail,s_name,staff_logdate,IRLOG.room_status,default_room,room_icon FROM i_room_list IRL LEFT JOIN i_room_login IRLOG ON IRLOG.room_no = IRL.room_no AND IRLOG.clinic_id = IRL.clinic_id AND IRLOG.visit_date = ? AND IRLOG.room_status='1' LEFT JOIN p_staff PS ON IRLOG.s_id = PS.s_id WHERE IRL.clinic_id=? ORDER BY IRL.room_no *1";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss",$sToday,$sClinicID);
	if($stmt->execute()){
	  $stmt->bind_result($room_no,$room_detail,$s_name,$staff_logdate,$room_status,$default_room,$room_icon);
	  while ($stmt->fetch()) {
	  	$sIcon=(($room_icon=="")?"":"<i class='$room_icon'></i>");
	  	$sRoomList .= "
	  	<div class='row-room fl-wrap-row h-30 row-hover row-color lh-30'>
	  		<div class='fl-fix w-70 fl-mid'>".$room_no."</div>
	  		<div class='fl-fix check-me w-50 fl-mid'>".$sIcon."</div>
	  		<div class='fl-fill check-me'>".$room_detail."</div>
	  		<div class='fl-fill check-me'>".(( $staff_logdate > $sToday)?$s_name:"")."</div>
	  		<div class='fl-fix w-70 check-me'>".(isset($aRoomCount[$room_no])?$aRoomCount[$room_no]:"0")."</div>
	  		
	  		<div class='fl-fix w-70'><input class='bigcheckbox' type='radio' name='room_no' value='$room_no' data-default='$default_room' ".(($sUidRoom==$room_no)?"disabled='true'":"")." /></div>
	  	</div>
	  	";
	  }
	}

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
	ORDER BY data_seq
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

/*
	$query = "SELECT data_result
	FROM p_data_result
	WHERE  uid = ? AND collect_date = ?  AND collect_time=? AND data_id='cn_patient_note'";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sToday,$sColT);

	if($stmt->execute()){
	  $stmt->bind_result($data_result );
	  while ($stmt->fetch()) {
	  	$sNoteToAll = $data_result ;
	  }
	}
*/



$sSaleOpt ="";
//sSaleOpt
	$query = "SELECT sale_opt_id,sale_opt_name 

	FROM sale_option WHERE is_enable=1 ORDER BY data_seq";
	//WHERE room_status IN ('0','1');";
	$stmt = $mysqli->prepare($query);
	if($stmt->execute()){
	  $stmt->bind_result($sale_opt_id,$sale_opt_name);
	  while ($stmt->fetch()) {
	  	$sSaleName = $sale_opt_name;
	  	if($sCurOptId==$sale_opt_id){
	  		$sSaleName = ">>> ".$sSaleName;
	  	}

	  	$sSaleOpt .= "<option value='$sale_opt_id' ".(($sCurOptId==$sale_opt_id)?"selected":"").">$sSaleName</option>";
	  }
	}
$sCurService="";
//Today Services
/*
PDR.data_id IN ('serv_coun_hiv','serv_coun_sti_test','serv_coun_prep','serv_coun_pep','serv_coun_treatment','serv_coun_hormone','serv_coun_internal','serv_coun_blood_test','serv_coun_buymed','serv_coun_consult','serv_coun_research','serv_coun_online','serv_coun_certificate','serv_coun_telemed','serv_coun_vaccine','serv_coun_art','serv_coun_oth','serv_coun_oth_txt')
*/

	$query = "SELECT PDR.data_id,data_name_th,data_name_en,data_result
	FROM p_data_result PDR

	LEFT JOIN p_data_list PDL
	ON PDL.data_id = PDR.data_id

	LEFT JOIN p_form_list_data PFI
	ON PFI.data_id = PDL.data_id

	WHERE  PDR.uid = ? AND collect_date = ?  AND PFI.form_id = '$PROVIDER_FORM_NAME'
	AND (data_seq > 165 AND data_seq < 191	)

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
$sHistory="";
//History
$sPrevRoom=""; $sCurRoom="";
	$query = "SELECT IQLL.room_no,room_detail,IQLL.s_id,s_name,queue_datetime,queue_status FROM i_queue_list_log IQLL 
	LEFT JOIN i_room_list IRL
	ON IRL.clinic_id = IQLL.clinic_id
	AND IQLL.room_no=IRL.room_no
	LEFT JOIN p_staff PS
	ON PS.s_id = IQLL.s_id
	WHERE IQLL.clinic_id=? AND queue=? AND collect_date=? AND queue_call!=1 ORDER BY queue_datetime DESC
	";
	
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sClinicID,$sQ,$sToday);
	if($stmt->execute()){
	  $stmt->bind_result($room_no,$room_detail,$s_id,$s_name,$queue_datetime,$queue_status);
	  while ($stmt->fetch()) {
	  	//set current Room
	  	if($sCurRoom=="") {
	  		$sCurRoom=$room_no;
	  		$sPrevRoom==$room_no;
	  	}else if($sCurRoom==$room_no){
	  		//Still the current Room
	  	}else if($sPrevRoom==""){
	  		$sPrevRoom=$room_no;
	  	}
	  	$sTime = substr($queue_datetime,10);
	  	$sHistory = "<div class='fl-wrap-row fs-xsmall row-hover row-color-2' style='text-align:left'>
	  		<div class='fl-fix w-s'>$sTime</div>
	  		<div class='fl-fill'>[$room_no] $room_detail ".(($s_name!="")?"<span class='fw-b'>by ".$s_name."</span>":"")."</div>
	  	</div>".$sHistory;
	  }
	}

$mysqli->close();


if($sSelRoom==""){
	//Do nothing
}else if($sSelRoom=="last"){
	//Use $sPrevRoom 
	$sJs.="$(\"#divQueueFwd #divRoomList\").find(\"input[name='room_no'][value='".$sPrevRoom."']\").prop(\"checked\",true);
	$(\"#divQueueFwd #divRoomList\").find(\"input[name='room_no'][value='".$sPrevRoom."']\").focus();";
}else if($sSelRoom!=""){
	//Use $sSelRoom
	$sJs.="$(\"#divQueueFwd #divRoomList\").find(\"input[name='room_no'][value='".$sSelRoom."']\").prop(\"checked\",true);
	$(\"#divQueueFwd #divRoomList\").find(\"input[name='room_no'][value='".$sSelRoom."']\").focus();";
}



?>
<style>

	.pinfo-item{
		float:left;
	}
</style>
<div id='divQueueFwd' class='fl-wrap-col'>
	<div class='fl-wrap-row h-80 f-border'>
		<div class='fl-wrap-row lh-15' >
			<? echo($sHtml); ?>
		</div>
	</div>
	<div class='fl-wrap-row hmin-150'>
		<div class='fl-wrap-col fl-auto wper-30 al-left'>
			<div class='fl-fix h-20 row-color-2 fl-mid'> บริการที่ต้องการรับในวันนี้</div>
			<div class='fl-fill fs-small fl-auto'>
				<? echo($sService); ?>
			</div>
		</div>
		<div class='fl-wrap-col fl-auto wper-30 al-left f-border'>
			<div class='fl-fix h-20 row-color-2 fl-mid'> บริการที่ได้รับในวันนี้</div>
			<div class='fl-fill fs-small fl-auto'>
				<? echo($sCurService); ?>
			</div>
		</div>
		<div class='fl-wrap-col'>
			<div class='fl-wrap-row h-30 row-color-2' style='text-align:left;line-height: 15px'>
				<div class='fl-fix h-30 fl-mid'  style='line-height: 15px'> ส่วนลด</div>
				<div class='fl-fill fl-mid fs-smaller'><SELECT class='fill-box' id='ddlSaleOpt'><? echo($sSaleOpt); ?></SELECT></div>
			</div>
			<div class='fl-wrap-row'>
				<div class='fl-wrap-col'>
					<div class='row-color-2 h-25'> Note to All</div>
					<div class='fl-fill fs-small'>
					<textarea id='txtNoteToAll' class='' data-change='0' style='height: 100%;width:100%' ><? echo($sNoteToAll); ?></textarea>
					</div>
				</div>
				<div class='fl-wrap-col'>
					<div class='fl-fix row-color-2 h-25'> History</div>
					<div class='fl-fill fl-auto'>
						<? echo($sHistory); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
	<div class='fl-wrap-col h-20'>
	  	<div class='fl-wrap-row h-ss row-color-2 fs-s' style='line-height: 20px;font-weight: bold'>
	  		<div class='fl-fix w-70'>Room</div>
	  		<div class='fl-fix w-50'></div>
	  		<div class='fl-fill'>Room Name</div>
	  		<div class='fl-fill'>Name</div>
	  		<div class='fl-fix w-70'>Total</div>
	  		<div class='fl-fix w-70'>#</div>
	  		<div class='fl-fix' style='max-width: 20px;min-width: 20px'> </div>
	  	</div>
	</div>
	<div id='divRoomList' class='fl-wrap-col fl-auto' >
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
		
		$("#divQueueFwd #txtNoteToAll").off("change");
		$("#divQueueFwd #txtNoteToAll").on("change",function(){
			$(this).attr('data-change','1');
		});

		$("#divQueueFwd .check-me").off("click");
		$("#divQueueFwd").on("click",".check-me",function(){
			chkBox = $(this).closest(".row-room").find(".bigcheckbox");
			if($(chkBox).attr("disabled")){

			}else {
				$("#divQueueFwd .bigcheckbox").attr("checked",false);
				$(chkBox).attr("checked",true);
			}
		});


		$("#divQueueFwd #btnUidFwd").off("click");
		$("#divQueueFwd #btnUidFwd").on("click",function(){
			sRoomNo = $("#divQueueFwd").find("input[name='room_no']:checked").val();
			if(sRoomNo!=undefined) {
				$("#divQueueFwd").find("input[name='room_no']:checked").focus();

				sSaleId = $("#ddlSaleOpt").val();
				sQ =$("#divQueueFwd #txtFwdQueue").val();

				var aData = {u_mode:"q_fwd",q:sQ,fwdroom:sRoomNo,saleid:sSaleId};
				
				if($("#divQueueFwd #txtNoteToAll").attr('data-change')=="1"){
					sNote= $("#divQueueFwd #txtNoteToAll").val();
					aData.notetoall = sNote;
				}


				

				startLoad($("#divQueueFwd #btnUidFwd"),$("#divQueueFwd #btnUidFwd-loader"));
				callAjax("queue_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res=="0"){
						alert(rtnObj.msg);
						endLoad($("#divQueueFwd #btnUidFwd"),$("#divQueueFwd #btnUidFwd-loader"));
					}else{
						$.notify("Save Complete","success");
						setDlgResult("REFRESH",$("#divQueueFwd #btnUidFwd"));
						setTimeout(closeThis,1200);
					}
					
				});
				
			}else{
				$.notify("Please select room to fwd.");
				return;
			}
		});

		function closeThis(){
			sResult = getDlgResult($("#divQueueFwd"));
			closeDlg($("#divQueueFwd #btnUidFwd"),sResult);
		}

	});

</script>