<?
include_once("in_session.php"); 
include_once("in_php_function.php");

$sSID = getSS("s_id");
$sShowQ = getQS("showq");
$sQ = getQS("q");
$sLockQ = getQS("lockq");
$sUid=getQS("uid");
$sColDate =getQS("coldate");
$sColTime=getQS("coltime");
$sPInfoJS = "var bFromQ = \"0\"; $(\"#txtUid\").val(".(json_encode($sUid)).");";
$isFound = false;
$sToday = date("Y-m-d");
$bFromQ = false;
$sLoadQ = getQS("loadq");




if($sQ!="") $sShowQ = "1";
if($sLoadQ == "1") $bFromQ = true;
$sNextSchedule = "";
if($sUid!=""){
	$sUid=strtoupper($sUid);
}
if($sUid!="" || $sQ!=""){
	//Try finding Uid from Q
	include("in_db_conn.php");

	if($sQ!="" && $sUid==""){
		$bFromQ=true;
		//Get data from old system when everythings done use i_queue_list
		//$query = "SELECT uid FROM i_queue_list WHERE queue=? AND collect_date=?";
		$query = "SELECT uid FROM k_visit_data WHERE queue=? AND date_of_visit=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sQ,$sToday);
		if($stmt->execute()){
		$stmt->bind_result($uid);
			while ($stmt->fetch()) {
				$sUid=$uid;
			}
		}
	}


	$sUic="";

	//Load data from database

	$query = "SELECT uic,fname,sname,en_fname,en_sname,date_of_birth,citizen_id,passport_id,sex,id_address,id_zone,id_district,id_province,id_postal_code,tel_no,email,line_id,remark,clinic_type FROM patient_info WHERE uid=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);

	if($stmt->execute()){
		$stmt->bind_result($uic,$fname,$sname,$en_fname,$en_sname,$date_of_birth,$citizen_id,$passport_id,$sex,$id_address,$id_zone,$id_district,$id_province,$id_postal_code,$tel_no,$email,$line_id,$remark,$clinic_type);
		while ($stmt->fetch()) {
			$sUic=$uic;
			if($clinic_type=="") $clinic_type="P";

			$sPInfoJS .= "
			$(\"#uic\").val(".(json_encode($uic)).");
			$(\"#fname\").val(".(json_encode($fname)).");
			$(\"#sname\").val(".(json_encode($sname)).");
			$(\"#en_fname\").val(".(json_encode($en_fname)).");
			$(\"#en_sname\").val(".(json_encode($en_sname)).");
			$(\"#date_of_birth\").val(".(json_encode(trim($date_of_birth))).");
			$(\"#citizen_id\").val(".(json_encode($citizen_id)).");
			$(\"#passport_id\").val(".(json_encode($passport_id)).");
			$(\"#sex\").val(".(json_encode($sex)).");
			$(\"#id_address\").val(".(json_encode($id_address)).");
			$(\"#id_zone\").val(".(json_encode($id_zone)).");
			$(\"#id_district\").val(".(json_encode($id_district)).");
			$(\"#id_province\").val(".(json_encode($id_province)).");
			$(\"#id_postal_code\").val(".(json_encode($id_postal_code)).");
			$(\"#tel_no\").val(".(json_encode($tel_no)).");
			$(\"#email\").val(".(json_encode($email)).");
			$(\"#line_id\").val(".(json_encode($line_id)).");
			$(\"#clinic_type\").val(".(json_encode($clinic_type)).");
			$(\"#remark\").val(".(json_encode($remark)).");";

			$sPInfoJS .= "
			$(\"#uic\").attr(\"data-odata\",".(json_encode($uic)).");
			$(\"#fname\").attr(\"data-odata\",".(json_encode($fname)).");
			$(\"#sname\").attr(\"data-odata\",".(json_encode($sname)).");
			$(\"#en_fname\").attr(\"data-odata\",".(json_encode($en_fname)).");
			$(\"#en_sname\").attr(\"data-odata\",".(json_encode($en_sname)).");
			$(\"#date_of_birth\").attr(\"data-odata\",".(json_encode($date_of_birth)).");
			$(\"#citizen_id\").attr(\"data-odata\",".(json_encode($citizen_id)).");
			$(\"#passport_id\").attr(\"data-odata\",".(json_encode($passport_id)).");
			$(\"#sex\").attr(\"data-odata\",".(json_encode($sex)).");
			$(\"#id_address\").attr(\"data-odata\",".(json_encode($id_address)).");
			$(\"#id_zone\").attr(\"data-odata\",".(json_encode($id_zone)).");
			$(\"#id_district\").attr(\"data-odata\",".(json_encode($id_district)).");
			$(\"#id_province\").attr(\"data-odata\",".(json_encode($id_province)).");
			$(\"#id_postal_code\").attr(\"data-odata\",".(json_encode($id_postal_code)).");
			$(\"#tel_no\").attr(\"data-odata\",".(json_encode($tel_no)).");
			$(\"#email\").attr(\"data-odata\",".(json_encode($email)).");
			$(\"#line_id\").attr(\"data-odata\",".(json_encode($line_id)).");
			$(\"#remark\").attr(\"data-odata\",".(json_encode($remark)).");

			$(\"#clinic_type\").attr(\"data-odata\",".(json_encode($clinic_type)).");";
			$isFound=true;
		}
	}

	//Check if UIC is duplicate
	if($sUic!=""){
		$query = "SELECT uid FROM patient_info WHERE uic = ? AND uid != ?;";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUic,$sUid);

		if($stmt->execute()){
		  $stmt->bind_result($uid);
		  while ($stmt->fetch()) {
			$sPInfoJS .= "\$(\"#divUidSearch #uic\").notify(\"UIC is duplicate with UID : $uid\",\"warn\");
			\$(\"#divUidSearch #txtDupUid\").html(\"".$uid."\");";

		  }
		}
	}
	//Next Schedule 
	
	if($isFound){
		//Load Next Schedule
		$query = "SELECT data_result
		FROM p_data_result
		WHERE data_id = 'nextvisit_date' AND uid=? AND data_result > NOW() ORDER BY data_result LIMIT 1";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sUid);

		if($stmt->execute()){
		  $stmt->bind_result($data_result);
		  while ($stmt->fetch()) {
			$sNextSchedule = $data_result;
		  }
		}


	}

	$mysqli->close();

	if($sQ!=""){
		$sPInfoJS.="\$(\"#txtQueue\").val(\"".$sQ."\");";
	}

	if($sUid!=""){
		$sPInfoJS.="\$(\"#txtUid\").val(\"".$sUid."\");";
	}

	$sPInfoJS.="var lockQ = '".$sLockQ."';";
	if($isFound) {
		//Found Patient Info in patient_info.
		$sPInfoJS.="$(\"#btnSearchID\").hide(); 
			$(\"#txtUid\").attr(\"readonly\",\"true\");
			$(\"#divUidSearch .pinfobtn\").show();
			$(\"#divUidSearch #divClinicCard\").show();

			";

		$sIDPhoto = "idimg/".$citizen_id.".png";
		if(!file_exists($sIDPhoto)){
			//$sIDPhoto = "assets/image/nophoto.jpg";
		}else{
			$sPInfoJS .="\$(\"#imgIDPhoto\").attr(\"src\",\"".$sIDPhoto."\");";	
		}
		
	}else{
		$sPInfoJS .="\$.notify(\"UID : ".$sUid." not found.\\r\\nไม่พบ UID นี้ในระบบ\");
		\$(\"#divUidSearch #btnFindUID\").show();
		";
	}
	if($bFromQ){
		$sPInfoJS .= "\$(\"#divUidSearch #btnBindQ\").hide();";
	}else if($sLockQ=="1" && $isFound){
		$sPInfoJS .= "\$(\"#divUidSearch #btnBindQ\").show();";
	}


}else{
	//Show Default
	$sPInfoJS .="\$(\"#divUidSearch #btnFindUID\").show();";	
}


if($sLockQ=="1" && $sQ != ""){
	$sPInfoJS .= "\$(\"#divUidSearch #txtQueue\").attr(\"readonly\",\"true\");";
}



?>
<style>
	.uidfinder{
		background-color: pink;
	}
</style>

<div id='divUidSearch' class='fl-wrap-row row-color'>
	<div class='fl-wrap-col fl-mid w-100' >
		<div class='fl-fill fl-mid'>
			<img id='imgIDPhoto' style='width:80px;border:1px solid silver;' src='assets/image/nophoto.jpg'  />
		</div>
		<div class='fl-fix h-50 fl-mid'>
			<i id='btnClearInput' class='pt-btn fa fa-broom'>Clear</i><i class='fa fa-spinner fa-spin' style='display:none'></i>
		</div>
	</div>
	<div class='fl-fix w-10'>

	</div>
	<div class='fl-fill fl-auto'>
		<div class='fl-wrap' style='display:flex'>
			<div class='fl-fix w-160  h-50 '>
				<div class='id-head fs-small'>ชื่อ:(ไทย)*<span id='txtDupName' class='txt-dup fs-xsmall copy-to-clip' style='color:red'></span></div>
				<div class=''><input id='fname' class='h-25 save-data w-p-95 ki-next uidfinder' data-odata='' data-require='1' /></div>
			</div>
			<div class='fl-fix w-160  h-50 '>
				<div class='id-head fs-small'>นามสกุล:(ไทย)*</div>
				<div class=''><input id='sname' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' data-require='1' /></div>
			</div>
			<div class='fl-fix w-150  h-50 '>
				<div class='id-head fs-small'>Name:(Eng)</div>
				<div class=''><input id='en_fname' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' /></div>
			</div>
			<div class='fl-fix w-150  h-50'>
				<div class='id-head fs-small'>Last Name:(Eng)</div>
				<div class=''><input id='en_sname' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' /></div>
			</div>
			<div class='fl-fix w-110  h-50'>
				<div class='id-head fs-small'>วันเกิด | DOB*</div>
				<div class=''><input id='date_of_birth' placeholder="yyyy-mm-dd" class='h-25 save-data v-date ki-next  w-p-95 uidfinder' data-odata='' maxlength="10" data-require='1' /></div>
			</div>
			<div class='fl-fix w-160  h-50'>
				<div class='id-head fs-small'>บัตรประชาชน<span id='txtIdDupUid' class='txt-dup fs-xsmall copy-to-clip ' style='color:red'></span></div>
				<div class=''><input id='citizen_id' placeholder="เลขที่บัตรประชาชน" class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' /></div>
			</div>
			<div class='fl-fix w-100  h-50'>
				<div class='id-head fs-small'>Passport No</div>
				<div class=''><input id='passport_id' placeholder="Passport" class='h-25 save-data ki-next w-p-95' data-odata='' /></div>
			</div>
			<div class='fl-fix w-130  h-50'>
				<div class='id-head fs-small'>เพศกำเนิด | Sex</div>
				<div class=''>
					<SELECT id='sex' class='h-25 save-data ki-next w-p-95' style='height:26px' data-odata=''  data-require='1' >
						<option value=''>Select|เลือก</option>
						<option value='1'>ชาย|Male</option>
						<option value='2'>หญิง|Female</option>
					</SELECT>

				</div>
			</div>
			<div class='fl-fix w-2'>
				<input id='issue_date' class='fake-hide' style='float:right;' />
				<textarea id='txtIDPhoto' class='fake-hide' style='float:right;'></textarea>
			</div>
			<div class='fl-fix w-300'>
				<div class='id-head fs-small'>ที่อยู่</div>
				<div class=''><input id='id_address' placeholder="ที่อยู่ | Address" class='h-25 save-data ki-next ' data-odata='' style='width:98%' /></div>
			</div>
			<div class='fl-fix w-100 w-50'>
				<div class='id-head fs-small'>แขวง | Zone</div>
				<div class=''><input id='id_zone' placeholder="แขวง | Zone" class='h-25 save-data ki-next' data-odata='' style='width:96%' /></div>
			</div>
			<div class='fl-fix w-100 h-50'>
				<div class='id-head fs-small'>เขต | District</div>
				<div class=''><input id='id_district' placeholder="เขต | District" class='h-25 save-data ki-next ' style='width:96%' data-odata='' /></div>
			</div>
			<div class='fl-fix w-150 h-50'>
				<div class='id-head fs-small'>จังหวัด | Province</div>
				<div class=''><input id='id_province' placeholder="จังหวัด | Province" class='h-25 save-data ki-next ' data-odata=''  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-100 h-50'>
				<div class='id-head fs-small'>รหัส | Post</div>
				<div class=''><input id='id_postal_code' placeholder="รหัสไปรษณีย์" class='h-25 save-data ki-next w-p-95' data-odata=''   style='width:96%' /></div>
			</div>
			<div class='fl-fix w-110 h-50'>
				<div class='id-head fs-small'>เบอร์โทร | Tel No</div>
				<div class=''><input id='tel_no' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' placeholder="เบอร์โทร"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-200  h-50'>
				<div class='id-head fs-small'>อีเมล์ | Email</div>
				<div class=''><input id='email' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="อีเมล์ | Email"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-100  h-50'>
				<div class='id-head fs-small'>Line ID</div>
				<div class=''><input id='line_id' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="Line ID"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-90 h-50'>
				<div class='id-head fs-small'>UIC <span id='txtDupUid' class='h-25 txt-dup fs-xsmall copy-to-clip ' style='color:red'></span></div>
				<div class=''><input id='uic' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="UIC" style='width:96%' /></div>
			</div>

			<div class='fl-fix w-80 h-20 '>

				<input id='btnSearchID' type='button' class='btn-uid pt-btn' style='background-color: green;color:white' value='ค้นหา'  />
				<input id='btnSavePInfo' type='button' class='pinfobtn btn-uid pt-btn' style='background-color: yellow;;display:none' value='บันทึก'  />
				<i class='btn-uid-loader fa fa-spinner fa-spin fa-2x' style='display:none'></i>
			</div>
			<div id="divClinicCard" class='pinfobtn btn-uid fl-fix w-150' style=';display:none'>
				<SELECT id='clinic_type' data-odata='' style=';line-height: 15px' class='save-data ki-next w-150 h-20 fs-small'>
					<option value=''>(Please Select)</option>
					<option value='P'>Pribta</option>
					<option value='T'>Tangerine</option>
				</SELECT>
				<input id='btnPrintFront' type='button' class='lh-15 fabtn h-20 fs-small' style='background-color: #5EBA00;color:white' value='หน้าบัตร'  />
				<input id='btnPrintBack' type='button' class='lh-15 fabtn  h-20 fs-small' style='background-color: #F1C40F;color:white' value='หลังบัตร'  />
			</div>
			<div class='fl-fix w-20 h-20 pinfobtn' style='margin:0px 10px;line-height:40px;display:none'>
				<i id='btnHistory' class="fabtn fas fa-history fa-2x"></i>
			</div>
			<div class='fl-fix w-20 h-20' style='margin:0px 10px;line-height:40px;display:none'>
				<i class="fabtn fas fa-question-circle fa-2x"></i>
			</div>
			<div id='btnRelation' class='fabtn fl-fix w-50 pinfobtn lh-40 fl-mid' style='margin-left:10px;display:none'>
				<i class="fas fas fa-users fa-2x"></i>
			</div>
			<div id='btnSchedule' class='fabtn fl-fix w-130 pinfobtn' style='margin-left:10px;line-height:40px;display:none' data-schdate='<? echo($sNextSchedule); ?>'>
				<i class="fas fa-calendar-alt"></i> : <? echo($sNextSchedule); ?>
			</div>
			

			<div class='fl-fix w-100 h-20'>
				<input id='btnRegisterUid' type='button' class='btn-uid pt-btn fs-m' style='background-color: red;color:white;display:none' value='ลงทะเบียน'  />
			</div>

		</div>
	</div>
	<div class='fl-fix w-150'>
		<div class='fl-wrap-col' style='height:100%'><textarea id='remark' class='save-data' style='text-align:left;font-size:small;line-height: 15px;width:150px;min-height:100%;box-sizing: border-box;'></textarea></div>
	</div>
	<div class='fl-fix w-10'></div>
	<div class='fl-wrap-col fl-mid w-100' style='<? echo(($sShowQ=="1")?"":"display:none"); ?>' >
		<div class='fl-fix h-50 '>
			<input id='txtQueue' class='h-50 fl-fill w-100 fs-xl' style='text-align: center' placeholder="Q" />
		</div>
		<div class='fl-fix h-25 fl-mid' >
			<input id='txtUid' class='h-20 w-100' placeholder="PXX-00000"  style='text-align: center' />
		</div>
		<div class='fl-fix h-50 fl-mid'>
			<i id='btnBindQ' class='btn-q pt-btn fa fa-save' style='display:none'>ผูกคิว</i>
			<i id='btnFindUID' class='btn-q pt-btn fas fa-search' style='display:none'>ค้นหา</i>
			<i id='btnQ-loader' class=' fa fa-spinner fa-spin' style='display:none'></i>
		</div>
	</div>
</div>


<script>
$(document).ready(function(){
	$("#divUidSearch .btn-q").hide();
	<? echo($sPInfoJS); ?>

/*

	if(bFromQ=="1"){

	}if(($("#txtUid").val()+"").trim()!="" && ($("#txtQueue").val()+"").trim()!="" && $("#txtUid").attr("readonly")){

		if(lockQ=="1"){
			$("#divUidSearch #txtQueue").attr("readonly",true);
			//$("#divUidSearch #btnBindQ").show();
		}else{
			$("#divUidSearch #btnBindQ").show();
			
		}
		
	}else if(($("#txtUid").val()+"").trim()!="" && $("#txtUid").attr("readonly")){
		//$("#divUidSearch #btnFindUID").show();
	}else{
		$("#divUidSearch #btnFindUID").show();
	}
*/


	$("#divUidSearch #fname").focus();

	$("#divUidSearch #btnSchedule").unbind("click");
	$("#divUidSearch").on("click","#btnSchedule",function(){
		sUid = $("#divUidSearch #txtUid").val();
		sSchDate = $(this).attr("data-schdate");

		sUrl = "appointments_calendar.php?uid="+sUid+"&appointment_date="+sSchDate;
		showDialog(sUrl,"ทำนัดหมาย","680","1350","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});

	$("#divUidSearch #txtQueue").unbind("change");
	$("#divUidSearch #txtQueue").on("change",function(){
		if($("#txtUid").attr("readonly") && ($("#txtQueue").val()+"").trim()!=""){
			$("#divUidSearch #btnBindQ").show();
		}else if( ($("#txtQueue").val()+"").trim()!=="" && ($("#txtUid").val()+"").trim()==""){
			$("#divUidSearch #btnFindUID").trigger("click");
		}else if($("#txtQueue").val()==""){
			$("#btnBindQ").hide();
		}
	});



	$("#divUidSearch .v-date").unbind("click");
	$("#divUidSearch .v-date").on("click",function(){
	    if($(this).hasClass('hasDatepicker')){
	    }
	    else{
	      let sObj = $(this).datepicker({
	        dateFormat:"yy-mm-dd",
	        changeYear:true,
	        changeMonth:true
	      });
	      $(sObj).datepicker("show");
	    }
	});

	$("#divUidSearch #btnPrintFront").unbind("click");
	$("#divUidSearch #btnPrintFront").on("click",function(){
		sClinic = $("#divUidSearch #clinic_type").val();
		sUid = $("#divUidSearch #txtUid").val();
		printCard("IHRI",sClinic,"f",sUid);
	});

	$("#divUidSearch #btnHistory").unbind("click");
	$("#divUidSearch #btnHistory").on("click",function(){
		sUid =  $("#divUidSearch #txtUid").val();
		let sUrl = "service_inc_history.php?uid="+sUid;
		showDialog(sUrl,"ประวัติการับบริการ","600","1024","","",false,function(){
		});
	});

	$("#divUidSearch #btnRelation").unbind("click");
	$("#divUidSearch #btnRelation").on("click",function(){
		sUid =  $("#divUidSearch #txtUid").val();
		let sUrl = "patient_relation_main.php?uid="+sUid;
		showDialog(sUrl,"รายการความสัมพันธ์ :"+sUid,"320","480","","",false,function(){
		});
	});


	$("#divUidSearch #btnPrintBack").unbind("click");
	$("#divUidSearch #btnPrintBack").on("click",function(){
		sClinic = $("#divUidSearch #clinic_type").val();
		if(sClinic==""){
			alert("Please select Clinic before print.");
			return;
		}
		sUid = $("#divUidSearch #txtUid").val();
		printCard("IHRI",sClinic,"b",sUid);
	});

	function printCard(clinicid,clinictype,side,uid){
		if(sClinic == "" || sUid=="") return;
		sUrl = "clinic_card_print.php?clinicid="+clinicid+"&ct="+sClinic+"&side="+side+"&uid="+sUid;
		window.open(sUrl);
	}
	$("#divUidSearch #btnClearInput").unbind("click");
	$("#divUidSearch #btnClearInput").on("click",function(){
		$("#divUidSearch #txtUid").val("");
		clearPInfoInput();
	});
	function clearPInfoInput(){
		$("#divUidSearch .save-data").val("");
		$("#divUidSearch .save-data").attr("data-odata","");
		
		$("#divUidSearch #txtIDPhoto").val("");

		$("#divUidSearch #txtQueue").val("");
		$("#divUidSearch #imgIDPhoto").attr("src","assets/image/nophoto.jpg");
		$("#divUidSearch #txtUid").removeAttr("readonly");
		$("#divUidSearch .pinfobtn").hide();
		$("#divUidSearch .btn-uid").hide();
		$("#divUidSearch .btn-q").hide();
		$("#divUidSearch .btn-uid-loader").hide();
		$("#divUidSearch #btnQ-loader").hide();
		$("#divUidSearch #btnSearchID").show();
		$("#divUidSearch #btnFindUID").show();
		$("#txtQueue").removeAttr("readonly");
		$("#divUidSearch #txtDupUid").html("");
		$("#divUidSearch #txtIdDupUid").html("");
		$("#divUidSearch #txtDupName").html("");
		$("#divUidSearch #fname").focus();
		$("#divUidSearch #fname").notify("Please enter ID Card\r\nกรุณาเสียบบัตรประชาชน");
	}

	$("#divUidSearch #btnBindQ").unbind("click");
	$("#divUidSearch #btnBindQ").on("click",function(){
		//This is for old system. Idiot programmer make everything difficult.
		sQ=($("#txtQueue").val()+"").trim();
		sUid=($("#txtUid").val()+"").trim();
		if(sQ=="" || sUid==""){
			$.notify("Q or Uid can't emptry.","error");
			return;
		}
		var aData = {u_mode:"q_bind",q:sQ,uid:sUid};
		startLoad($("#btnBindQ"),$("#btnQ-loader"));
		//callAjax("queue_a.php",aData,function(rtnObj,aData){
		callAjax("idiot_q_bind.php",aData,function(rtnObj,aData){
			if(!rtnObj.res.length)	rtnObj.res = 0;
			
			if(rtnObj.res=="0"){
				alert(rtnObj.msg);
				endLoad($("#btnBindQ"),$("#btnQ-loader"));
			}else{
				$.notify("Bind Queue and UID successful. Please ask patient to Questionnaire station.","success");
				$("#txtQueue").attr("readonly",true);
				$("#btnQ-loader").hide();
			}
			
		});
	});

	$("#divUidSearch #btnRegisterUid").unbind("click");
	$("#divUidSearch #btnRegisterUid").on("click",function(){
		if(validatePInfo()==false) return;
		if(!(confirm("ยืนยันลงทะเบียนผู้รับบริการ?"))){
			return;
		}

		var aData = {u_mode:"create_uid"};
		$("#divUidSearch .save-data").each(function(ix,objx){
			sOdata = $(objx).attr('data-odata');
			sTemp = ($(objx).val()+"").trim();
			if(sTemp!=sOdata){
				aData[$(objx).attr("id")] = encodeURIComponent(sTemp);
			}
		});

		//startLoad($("#divUidSearch #btnRegisterUid,#btnSearchID"),$("#divUidSearch .btn-uid-loader"));
		startLoad($("#divUidSearch #btnRegisterUid, #btnSearchID"),$("#divUidSearch .btn-uid-loader"));
		callAjax("patient_a.php",aData,function(rtnObj,aData){
			endLoad($("#divUidSearch #btnRegisterUid,#btnSearchID"),$("#divUidSearch .btn-uid-loader"));
			if (typeof rtnObj.res === 'undefined')	rtnObj.res = "0";
			if(rtnObj.res=="0"){
				if(rtnObj.dupuid!=""){
					$("#divUidSearch #citizen_id").notify("Citizen ID is duplicate with UID : "+rtnObj.dupuid);
					$("#divUidSearch #txtIdDupUid").html(rtnObj.dupuid);
				}else if(rtnObj.dupname!=""){
					$("#divUidSearch #fname").notify("Name and DOB exact match with UID : "+rtnObj.dupname);
					$("#divUidSearch #txtDupName").html(rtnObj.dupname);
				}else{
					$("#divUidSearch #txtIdDupUid").html("");

				}
			}else if(rtnObj.res=="1"){

				$.notify("Register Success.","success");
				$("#divUidSearch #txtUid").val(rtnObj.uid);
				if($("#divUidSearch #uic").val()=="") $("#divUidSearch #uic").val(rtnObj.uic);
				$("#divUidSearch #txtUid").attr("readonly",true);
				$("#divUidSearch #btnRegisterUid").hide();
				$("#divUidSearch #btnSearchID").hide();
				$("#divUidSearch #btnFindUID").hide();
				$("#divUidSearch .pinfobtn").show();
				$("#divUidSearch #divClinicCard").show();

				if(rtnObj.uicdup=="1"){
					$("#divUidSearch #uic").notify("UIC is duplicate with UID : "+rtnObj.dupuid);
					$("#divUidSearch #txtDupUid").html(rtnObj.dupuid);
				}else{
					$("#divUidSearch #txtDupUid").html("");
				}



				updatePInfoOData();
			}
			
		});


	});

	function validatePInfo(){
		bIsValid = true;

		if($("#fname").val()=="" || $("#sname").val()==""){
			$("#fname").notify("กรุณาใส่ ชื่อ หรือ นามสกุล หรือ ตัวอักษรตัวแรก");
			bIsValid = false;
		}
		else if($("#date_of_birth").val()=="" || $("#date_of_birth").val()=="0000-00-00"){
			$("#date_of_birth").notify("กรุณาใส่ วันเกิด");
			bIsValid = false;
		}else{
			sDCDate = getDCDate($("#date_of_birth").val());
			$("#date_of_birth").val(sDCDate);
			if(sDCDate.length != 10){

				bIsValid = false;
			}
		}

		return bIsValid;
	}

	$("#divUidSearch #btnSavePInfo").unbind("click");
	$("#divUidSearch #btnSavePInfo").on("click",function(){
		sUid = $("#txtUid").val();
		if($("#txtUid").attr("readonly")){
			
		}else{
			$.notify("Please select UID first");
			return;
		}

		/*
		sUrl = "patient_inc_info.php?hideinfo=1&uid="+sUid;
		showDialog(sUrl,"Patient Info","640","1360","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
		*/

		
		var isDataChanged = false;
		//validate input before create uid
		if(validatePInfo==false) return;



		var aData = {u_mode:"update_patient_info",u:sUid};
		$("#divUidSearch .save-data").each(function(ix,objx){
			sOdata = ($(objx).attr('data-odata'));
			sTemp = ($(objx).val()+"").trim();

			if(sTemp!=sOdata){
				aData[$(objx).attr("id")] = encodeURIComponent(sTemp);
				isDataChanged = true;
			}
		});
		if(isDataChanged){
			if(!confirm("ยืนยันบันทึกข้อมูล?")){
				return;
			}
			startLoad($("#divUidSearch .pinfobtn"),$("#divUidSearch .btn-uid-loader"));
			callAjax("patient_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					$("#divUidSearch #citizen_id").notify(rtnObj.msg);
					if(rtnObj.dupuid!=""){
						$("#divUidSearch #citizen_id").notify("Citizen ID is duplicate with UID : "+rtnObj.dupuid);
						$("#divUidSearch #txtIdDupUid").html(rtnObj.dupuid);
					}
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved.","success");
					if(rtnObj.uicdup=="1"){
						$("#divUidSearch #uic").notify("UIC is duplicate with UID : "+rtnObj.dupuid);
						$("#divUidSearch #txtDupUid").html(rtnObj.dupuid);
					}else{
						$("#divUidSearch #txtDupUid").html("");
					}
					updatePInfoOData();
				}
				endLoad($("#divUidSearch .pinfobtn"),$("#divUidSearch .btn-uid-loader"));
			});
		}else{
			$.notify("No Data Changed");
		}
	});


	$("#divUidSearch #btnFindUID").unbind("click");
	$("#divUidSearch #btnFindUID").on("click",function(){
		sUid=($("#txtUid").val()+"").trim();

		sQ=($("#txtQueue").val()+"").trim();
		if(sUid=="" && sQ==""){
			$("#txtUid").notify("Empty","error");
			return;
		}

		sUid = sUid.toUpperCase();
		$("#txtUid").val(sUid);

		startLoad($("#divUidSearch #btnFindUID"),$("#divUidSearch #btnQ-loader"));
		sUrl="patient_info_idcard.php?showq=1&uid="+sUid+"&lockq=1&q="+sQ;
		$("#divUidSearch").parent().load(sUrl);
	});

	$("#divUidSearch .save-data").on("change",function(){
		if($("#divUidSearch #txtUid").attr("readonly")){
			$("#divUidSearch .pinfobtn").show();
			$("#divUidSearch #divClinicCard").show();
		}
	});

	$("#divUidSearch #txtIDPhoto").on("paste",function(event){
    // use event.originalEvent.clipboard for newer chrome versions
    var items = (event.clipboardData  || event.originalEvent.clipboardData).items;
    //console.log(JSON.stringify(items)); // will give you the mime types
    // find pasted image among pasted items
    var objImage = null;
    for (var i = 0; i < items.length; i++) {
      if (items[i].type.indexOf("image") === 0) {
        objImage = items[i].getAsFile();
      }
    }
    // load image if there is a pasted image
    if (objImage !== null) {
      var imgReader = new FileReader();
      imgReader.onload = function(objBlob) {
        //console.log(objBlob.target.result); // data url!
        //document.getElementById("pastedImage").src = objBlob.target.result;
        sUrl="patient_a.php";
        sCitiID = $("#citizen_id").val();
        sIssue = $("#issue_date").val();
        if(sCitiID != ""){
	        var aData={u_mode:"upload_image",cid:sCitiID,idimg:objBlob.target.result,issued:sIssue};
	        callAjax(sUrl,aData,function(jRes,retAdata){
	         if(jRes.res=="1"){
	          $("#pastedImage").attr("src","idimg/"+retAdata.cid+".png");
	         }
	        });
        }

      };
      imgReader.readAsDataURL(objImage);
    }
    });


	function updatePInfoOData(){
		$("#divUidSearch .save-data").each(function(ix,objx){
			sOdata = ($(objx).attr('data-odata'));
			sTemp = (($(objx).val()+"").trim());

			if(sTemp!=sOdata){
				$(objx).attr('data-odata',(sTemp));
			}
		});
	}
});
function getDCDate(sBCDate){
	sTemp=sBCDate;
	aT = sTemp.split("-");
	if(aT.length==3){
		if(aT[0]>2400){
			sTemp = (aT[0]-543)+"-"+aT[1]+"-"+aT[2];
			
		}
	}
	return sTemp;
}
function getUidSearchQS(){
	var sQS = "";
	$("#divUidSearch .uidfinder").each(function(ix,objx){
		sTemp = ($(objx).val()+"").trim();
		if($(objx).attr("id")=="date_of_birth"){
			sTemp=getDCDate(sTemp);
			$(objx).val(sTemp);
		}

		if(sTemp!=""){
			sQS += ((sQS=="")?"?":"&")+$(objx).attr("id") + "="+encodeURIComponent(sTemp);
		}
	});

	return sQS;
}

</script>