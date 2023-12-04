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
		$query = "SELECT uid FROM i_queue_list WHERE queue=? AND collect_date=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sQ,$sToday);
		if($stmt->execute()){
		$stmt->bind_result($uid);
			while ($stmt->fetch()) {
				$sUid=$uid;
			}
		}
	}else if($sUid!="" && $sQ==""){
		//Try get queue by using UID

		$query = "SELECT queue FROM i_queue_list WHERE uid=? AND collect_date=?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUid,$sToday);
		if($stmt->execute()){
		$stmt->bind_result($queue);
			while ($stmt->fetch()) {
				$sQ=$queue;
				$_GET["q"]=$queue;
			}
		}
	}else if($sUid!="" && $sQ!=""){

	}


	$sUic=""; $citizen_id="";

	//Load data from database

	$sColList=array("uic","fname","sname","en_fname","en_sname","date_of_birth","citizen_id","passport_id","sex","id_address","id_zone","id_district","id_province","id_postal_code","tel_no","email","line_id","remark","clinic_type", "note_all_clinic");

	$query = "SELECT uic,fname,sname,en_fname,en_sname,date_of_birth,citizen_id,passport_id,sex,id_address,id_zone,id_district,id_province,id_postal_code,tel_no,email,line_id,remark,clinic_type, note_all_clinic FROM patient_info WHERE uid=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);

	if($stmt->execute()){
		$result = $stmt->get_result();
			while($row = $result->fetch_assoc()) {
				$sUic=$row["uic"]; $citizen_id=$row["citizen_id"];
				if($row["clinic_type"]=="") $clinic_type="P";
				foreach ($sColList as $iInd => $sCol) {
					$sPInfoJS.="setKeyVal($(\"#divUidSearch\"),\"$sCol\",".json_encode($row[$sCol]).",true,\"save-data\");";
				}
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
		$query = "SELECT appointment_date
		FROM i_appointment
		WHERE uid=? AND appointment_date >= ? ORDER BY appointment_date LIMIT 1";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss",$sUid,$sToday);

		if($stmt->execute()){
		  $stmt->bind_result($appointment_date);
		  while ($stmt->fetch()) {
			$sNextSchedule = $appointment_date;
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
		if($sUid!="")$sPInfoJS .= "\$(\"#divUidSearch #btnUnbindQ\").show();";
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
	<div class='fl-fix w-10'></div>

	<div class='fl-fill fl-auto'>
		<div class="fl-wrap-row h-5"></div>
		<div class='fl-wrap' style='display:flex'>
			<div class='fl-fix w-160  h-50 '>
				<div class='id-head fs-small '>ชื่อ:(ไทย)*<span id='txtDupName' class='txt-dup fs-xsmall copy-to-clip' style='color:red'></span></div>
				<div class=''><input id='fname' data-keyid='fname' class='h-25 save-data w-p-95 ki-next uidfinder' data-odata='' data-require='1' /></div>
			</div>
			<div class='fl-fix w-160  h-50 '>
				<div class='id-head fs-small '>นามสกุล:(ไทย)*</div>
				<div class=''><input id='sname' data-keyid='sname'class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' data-require='1' /></div>
			</div>
			<div class='fl-fix w-150  h-50 '>
				<div class='id-head fs-small'>Name:(Eng)</div>
				<div class=''><input id='en_fname' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' data-keyid='en_fname' /></div>
			</div>
			<div class='fl-fix w-150  h-50 '>
				<div class='id-head fs-small'>Last Name:(Eng)</div>
				<div class=''><input id='en_sname' data-keyid='en_sname' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' /></div>
			</div>
			<div class='fl-fix w-110  h-50 '>
				<div class='id-head fs-small'>วันเกิด | DOB*</div>
				<div class=''><input id='date_of_birth' data-keyid='date_of_birth' placeholder="yyyy-mm-dd" class='h-25 save-data v-date ki-next  w-p-95 uidfinder' data-odata='' maxlength="10" data-require='1' /></div>
			</div>
			<div class='fl-fix w-160  h-50 '>
				<div class='id-head fs-small'>บัตรประชาชน<span id='txtIdDupUid' class='txt-dup fs-xsmall copy-to-clip ' style='color:red'></span></div>
				<div class=''><input id='citizen_id' data-keyid='citizen_id' type='number' placeholder="เลขที่บัตรประชาชน" class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' /></div>
			</div>
			<div class='fl-fix w-100  h-50 '>
				<div class='id-head fs-small'>Passport No</div>
				<div class=''><input id='passport_id' data-keyid='passport_id' placeholder="Passport" class='h-25 save-data ki-next w-p-95' data-odata='' /></div>
			</div>
			<div class='fl-fix w-130  h-50 '>
				<div class='id-head fs-small'>เพศกำเนิด | Sex</div>
				<div class=''>
					<SELECT id='sex' data-keyid='sex' class='h-25 save-data ki-next w-p-95' style='height:26px' data-odata=''  data-require='1' >
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
				<div class='id-head fs-small '>ที่อยู่</div>
				<div class=''><input id='id_address' data-keyid='id_address' placeholder="ที่อยู่ | Address" class='h-25 save-data ki-next ' data-odata='' style='width:98%' /></div>
			</div>
			<div class='fl-fix w-100 w-50 '>
				<div class='id-head fs-small'>แขวง | Zone</div>
				<div class=''><input id='id_zone' data-keyid='id_zone' placeholder="แขวง | Zone" class='h-25 save-data ki-next' data-odata='' style='width:96%' /></div>
			</div>
			<div class='fl-fix w-120 h-50 '>
				<div class='id-head fs-small'>เขต | District</div>
				<div class=''><input id='id_district' data-keyid='id_district' placeholder="เขต | District" class='h-25 save-data ki-next ' style='width:96%' data-odata='' /></div>
			</div>
			<div class='fl-fix w-150 h-50 '>
				<div class='id-head fs-small'>จังหวัด | Province</div>
				<div class=''><input id='id_province' data-keyid='id_province' placeholder="จังหวัด | Province" class='h-25 save-data ki-next ' data-odata=''  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-100 h-50 '>
				<div class='id-head fs-small'>รหัส | Post</div>
				<div class=''><input id='id_postal_code' data-keyid='id_postal_code' placeholder="รหัสไปรษณีย์" class='h-25 save-data ki-next w-p-95' data-odata=''   style='width:96%' /></div>
			</div>
			<div class='fl-fix w-150 h-50 '>
				<div class='id-head fs-small'>เบอร์โทร | Tel No</div>
				<div class=''><input id='tel_no' data-keyid='tel_no' class='h-25 save-data ki-next w-p-95 uidfinder' data-odata='' placeholder="เบอร์โทร"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-210  h-50 '>
				<div class='id-head fs-small'>อีเมล์ | Email</div>
				<div class=''><input id='email' data-keyid='email' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="อีเมล์ | Email"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-130  h-50 '>
				<div class='id-head fs-small'>Line ID</div>
				<div class=''><input id='line_id' data-keyid='line_id' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="Line ID"  style='width:96%' /></div>
			</div>
			<div class='fl-fix w-150 h-60 '>
				<div class='id-head fs-small'>UIC<span id='txtDupUid' class='h-25 txt-dup fs-xsmall copy-to-clip ' style='color:red'></span></div>
				<div class=''><input id='uic' data-keyid='uic' class='h-25 save-data ki-next uidfinder' data-odata='' placeholder="UIC" style='width:96%' /></div>
			</div>

			<div class='fl-fix w-80 h-20 '>

				<input id='btnSearchID' type='button' class='btn-uid pt-btn' style='background-color: green;color:white' value='ค้นหา'  />
				<input id='btnSavePInfo' type='button' class='pinfobtn btn-uid pt-btn' style='background-color: yellow;;display:none' value='บันทึก'  />
				<i class='btn-uid-loader fa fa-spinner fa-spin fa-2x' style='display:none'></i>
			</div>
			<div id="divClinicCard" class='pinfobtn btn-uid fl-fix w-150' style='display:none'>
				<SELECT id='clinic_type' data-keyid='clinic_type' data-odata='' style='line-height: 15px' class='save-data ki-next w-150 h-20 fs-small'>
					<option value=''>(Please Select)</option>
					<option value='P'>Pribta</option>
					<option value='T'>Tangerine</option>
				</SELECT>
				<input id='btnPrintFront' type='button' class='lh-15 fabtn h-20 fs-small' style='background-color: #5EBA00;color:white' value='หน้าบัตร'  />
				<input id='btnPrintBack' type='button' class='lh-15 fabtn  h-20 fs-small' style='background-color: #F1C40F;color:white' value='หลังบัตร'  />
			</div>

			<div id='btnHistory' class='fabtn fl-fix w-50 pinfobtn lh-40 fl-mid' style='display:none'>
				<i class="fas fa-history fa-2x"></i>
			</div>

			<div class='fl-fix w-20 h-20' style='margin:0px 10px;line-height:40px;display:none'>
				<i class="fabtn fas fa-question-circle fa-2x"></i>
			</div>
			<div id='btnRelation' class='fabtn fl-fix w-50 pinfobtn lh-40 fl-mid' style='display:none'>
				<i class="fas fas fa-users fa-2x"></i>
			</div>
			<div class='fl-wrap-col w-180 pinfobtn' style='margin-left:10px;display:none'>
				<div id='btnSchedule' class='fabtn fl-fix h-30 lh-30' style='' data-schdate='<? echo($sNextSchedule); ?>'>
					<i  class="fas fa-calendar-alt" ></i> : <? echo($sNextSchedule); ?>
				</div>

				<? include("queue_inc_proj.php"); ?>
			</div>
			<div class='fl-wrap-col w-210 plan-next-show' style='margin-left:10px; display:none;'>
				<div class="fl-wrap-row h-20 font-s-1">
					<div class="fl-fix w-65 fw-b">
						Plan Next.
					</div>
					<div class="fl-fill">
						<select class="fill-box ddl-visit-reception h-20" style="min-width: 144px; max-width: 144px;" data-uid="<? echo $sUid; ?>">
							<? include("patient_info_idcard_new_visit_list.php"); ?>
						</select>
					</div>
				</div>
				<div class="fl-wrap-row h-40 font-s-1">
					<div class="fl-fill fl-mid">
						<textarea id="cn_plan_reception" data-keyid="cn_plan_reception" class="" data-odata="" style="min-height: 39px; max-height: 34px; min-width: 209px; max-width:209px;"></textarea>
					</div>
				</div>
				
				<div class="font-s-1"><b>Treatment</b></div>
				<div class="fl-wrap-row h-40 font-s-1">
					<div class="fl-fill fl-mid">
						<textarea id="cn_info_treatment" data-keyid="cn_info_treatment" class="" data-odata="" style="min-height: 39px; max-height: 34px; min-width: 209px; max-width:209px;"></textarea>
					</div>
				</div>
				
			</div>
			<div class='fl-wrap-col w-100 plan-next-show' style='margin-left:10px; display:none;'>
				<div class="fl-wrap-row h-40 font-s-1"></div>
				<div class="fl-wrap-row h-20">
					<button id="bt_order_lab_reception" class="btn btn-success font-s-1" data-queue="<? echo $sQ; ?>" data-coldate="<? echo $sToday; ?>" data-coltime="<? echo $sColTime; ?>" value="1" style="padding: 1px 4px 4px 4px;"><i class="fa fa-thermometer-empty" aria-hidden="true"> Lab Order</i></button>
				</div>
			</div>

			<div class='fl-fix w-100 h-20'>
				<input id='btnRegisterUid' type='button' class='btn-uid pt-btn fs-m' style='background-color: red;color:white;display:none' value='ลงทะเบียน'  />
			</div>

		</div>
	</div>
	<div class='fl-fix w-140'>
		<div class="fl-wrap-row h-10"></div>
		<div class='fl-wrap-col' style='height:20%;'>
			<textarea id='remark' data-keyid='remark' class='save-data' style='text-align:left;font-size:small;line-height: 15px;width: 100%;min-height:100%;box-sizing: border-box; background-color: #EFF0EF;' readonly></textarea>
		</div>
		<div class="fl-wrap-row h-5"></div>
		<div class='fl-wrap-col' style='height:68%;'>
			<textarea id='note_all_clinic' data-keyid='note_all_clinic' class='save-data' style='text-align:left;font-size:small;line-height: 15px;width:100%;min-height:100%;box-sizing: border-box;'></textarea>
		</div>
	</div>
	<div class='fl-fix w-5'></div>
	<div class='fl-wrap-col fl-mid w-100' style='<? echo(($sShowQ=="1")?"":"display:none"); ?>' >
		<div class='fl-fill h-50'>
			<i id='btnRePrintQ' class='fabtn pt-btn fa fa-print' > Print</i>
		</div>
		<div class='fl-fill h-80 fl-mid font-s-7'>
			<input id='txtQueue' class='h-80 fl-fill w-100 fw-b' style='text-align: center' placeholder="Q" />
		</div>
		<div class='fl-fix h-35 fl-mid'>
			<input id='txtUid' data-keyid='uid' data-pk='1' class='save-data h-30 w-100 fw-b' placeholder="PXX-00000"  style='text-align: center' />
		</div>
		<div class='fl-fix h-50 fl-mid'>
			<i id='btnBindQ' class='btn-q pt-btn fa fa-save' style='display:none'>ผูกคิว</i>
			<span style='color:red'><i id='btnUnbindQ' class='btn-q pt-btn fa fa-save' style='display:none' title='ยกเลิกผูกคิว *ได้เฉพาะกรณีที่ยังไม่มีการลงข้อมูลใดๆ หากมีลงข้อมูลแล้วจะยกเลิกไม่ได้'>ยกเลิก</i></span>
			<i id='btnFindUID' class='btn-q pt-btn fas fa-search' style='display:none'>ค้นหา</i>
			<i id='btnQ-loader' class=' fa fa-spinner fa-spin' style='display:none'></i>
		</div>
	</div>
	<div class='fl-fix w-5'></div>
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

	// bt Lab order
	$("#bt_order_lab_reception").off("click");
	$("#bt_order_lab_reception").on("click", function(){
		var queue = $(this).data("queue");
		var sUid = $("#divUidSearch .ddl-visit-reception").data("uid");
		if(sUid != "" && queue != ""){
			console.log("TEST:"+sUid+"/"+queue);
			var sColD = $(this).data("coldate");
			var sColT = $(this).data("coltime");

			sUrl="lab_order_inc_main.php?is_doctor=1&is_pribta=1&"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,"Lab Order "+qsTitle(sUid,sColD,sColT),"99%","99%","",
				function(sResult){
					// resetBill(sUid,sColD,sColT);
					if(sResult=="REFRESH"){
						
					}
				},false,function(){
					//Load Done Function
			});
		}
	});

	// TEXT DB Click show DLG
	$("#divUidSearch #cn_plan_reception").off("dblclick");
	$("#divUidSearch #cn_plan_reception").on("dblclick", function(){
		var coldate_js = $("#divUidSearch .ddl-visit-reception").val().split(" ")[0];
		var coltime_js = $("#divUidSearch .ddl-visit-reception").val().split(" ")[1];
		var uid_js = $("#divUidSearch .ddl-visit-reception").data("uid");
		var sUrl_next_plan = "patient_info_idcard_new_dlg_nextPlan.php?uid="+uid_js+"&coldate="+coldate_js+"&coltime="+coltime_js;
		// console.log(sUrl_next_plan);

		showDialog(sUrl_next_plan, "Plan Next Visit", "260", "308", "", function(sResult){}, "", function(sResult){});
	});

	// DDL next plan show
	$("#divUidSearch .ddl-visit-reception").off("change");
	$("#divUidSearch .ddl-visit-reception").on("change", function(){
		var coldate_js = $(this).val().split(" ")[0];
		var coltime_js = $(this).val().split(" ")[1];
		var uid_js = $(this).data("uid");

		if(uid_js != ""){
			$("#divUidSearch .plan-next-show").show();
		}

		var aData = {
			uid: uid_js,
			coldate: coldate_js,
			coltime: coltime_js
		};
		
		$.ajax({
			url: "patient_info_idcard_new_nextPlan_ajax.php",
			method: "POST",
			cache: false,
			data: aData,
			success: function(sResult){
				if(sResult != ""){
					$("#divUidSearch #cn_plan_reception").val(sResult);
				}
			}
		});
		
		$.ajax({
			url: "patient_info_idcard_new_treatment_ajax.php",
			method: "POST",
			cache: false,
			data: aData,
			success: function(sResult){
				if(sResult != ""){
					$("#divUidSearch #cn_info_treatment").val(sResult);
				}
			}
		});
		
	});
	$("#divUidSearch .ddl-visit-reception").change();
	// CLOSE DDL next plan show

	$("#divUidSearch #fname").focus();

	//$("#divUidSearch .uidfinder").off("");
	$("#divUidSearch .uidfinder").off("keypress");
	$("#divUidSearch .uidfinder").on("keypress",function(e){
		if(e.which == 13) {
			$("#divUidSearch #btnSearchID").click();
		}
	});

	$("#divUidSearch #btnRelation").off("click");
	$("#divUidSearch #btnRelation").on("click",function(){
		sUid =  $("#divUidSearch #txtUid").val();
		let sUrl = "patient_relation_main.php?uid="+sUid;
		showDialog(sUrl,"รายการความสัมพันธ์ :"+sUid,"320","480","","",false,function(){
		});
	});


	$("#divUidSearch #txtQueue").off("change");
	$("#divUidSearch #txtQueue").on("change",function(){
		if($("#txtUid").attr("readonly") && ($("#txtQueue").val()+"").trim()!=""){
			$("#divUidSearch #btnBindQ").show();
		}else if( ($("#txtQueue").val()+"").trim()!=="" && ($("#txtUid").val()+"").trim()==""){
			$("#divUidSearch #btnFindUID").trigger("click");
			
		}else if($("#txtQueue").val()==""){
			$("#btnBindQ").hide();
		}
	});



	$("#divUidSearch .v-date").off("click");
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

	$("#divUidSearch #btnPrintFront").off("click");
	$("#divUidSearch #btnPrintFront").on("click",function(){
		sClinic = $("#divUidSearch #clinic_type").val();
		sUid = $("#divUidSearch #txtUid").val();
		printCard("IHRI",sClinic,"f",sUid);
	});

	$("#divUidSearch #btnRePrintQ").off("click");
	$("#divUidSearch #btnRePrintQ").on("click",function(){
		sQ=$("#divUidSearch #txtQueue").val();
		if(sQ=="") {
			$.notify("กรุณาเลือกคิวที่ต้องการพิมพ์\r\nPlease select queue.");
			return;
		}
		var aData = {u_mode:"q_reprint",q:sQ};
		if(confirm("ยืนยันพิมพ์บัตรคิวอีกครั้ง?\r\nConfirm re-print the queue card")===false){
			return;
		}
		startLoad($("#btnRePrintQ"),$("#btnQ-loader"));
		//callAjax("queue_a.php",aData,function(rtnObj,aData){
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$.notify("บัตรคิวจะพิมพ์ตามรอบพิมพ์\r\nQ will print ASAP.","success");
			}else {
				$.notify("ระบบมีปัญหา\r\nError : "+rtnObj.msg);
			}
			endLoad($("#btnRePrintQ"),$("#btnQ-loader"));
		});
	});

	$("#divUidSearch #btnHistory").off("click");
	$("#divUidSearch #btnHistory").on("click",function(){
		sUid =  $("#divUidSearch #txtUid").val();
		let sUrl = "service_inc_history.php?uid="+sUid;
		showDialog(sUrl,"ประวัติการรับบริการ","90%","90%","","",false,function(){
		});
	});


	$("#divUidSearch #btnPrintBack").off("click");
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
	$("#divUidSearch #btnClearInput").off("click");
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

	$("#divUidSearch #btnSchedule").off("click");
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


	$("#divUidSearch #btnBindQ").off("click");
	$("#divUidSearch #btnBindQ").on("click",function(){
		sQ=($("#txtQueue").val()+"").trim();
		sUid=($("#txtUid").val()+"").trim();
		if(sQ=="" || sUid==""){
			$.notify("Q or Uid can't emptry.","error");
			return;
		}
		var aData = {u_mode:"q_bind",q:sQ,uid:sUid};
		startLoad($("#btnBindQ"),$("#btnQ-loader"));
		//callAjax("queue_a.php",aData,function(rtnObj,aData){
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$.notify("Bind Queue and UID successful.","success");
				$("#txtQueue").attr("readonly",true);
				$("#btnQ-loader").hide();
				qRow=$(".main-q-row[data-queue='"+sQ+"']");
				$(qRow).attr("data-uid",sUid);
				$(qRow).find(".q-share-icon").show();
				$(qRow).find(".q-uid").html(sUid);
				$(qRow).addClass("fabtn btn-q-no");
			}else if(rtnObj.res=="0"){
				alert(rtnObj.msg);
				endLoad($("#btnBindQ"),$("#btnQ-loader"));
			}else{

			}
			
		});
	});
	$("#divUidSearch #btnUnbindQ").off("click");
	$("#divUidSearch #btnUnbindQ").on("click",function(){
		sQ=($("#txtQueue").val()+"").trim();
		sUid=($("#txtUid").val()+"").trim();
		if(sQ=="" || sUid==""){
			$.notify("Q or Uid can't emptry.","error");
			return;
		}

		if(confirm("ยืนยันลบเบอร์ UID :"+sUid+" ออกจาก Q : "+sQ+"?\r\nConfirm remove UID :"+sUid+" From queue : "+sQ+"?")){

		}else{
			return;
		}

		var aData = {u_mode:"q_unbind",q:sQ,uid:sUid};
		startLoad($("#btnUnbindQ"),$("#btnQ-loader"));
		//callAjax("queue_a.php",aData,function(rtnObj,aData){
		callAjax("queue_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="1"){
				$.notify("Unbind Queue and UID successful.","success");
				startLoad($("#divUidSearch #btnFindUID"),$("#divUidSearch #btnQ-loader"));
				qRow=$("#divQueueList .main-q-row[data-queue='"+sQ+"']");
				$(qRow).attr("data-uid","");
				$(qRow).attr("data-qcall","");
				$(qRow).attr("data-billid","");
				$(qRow).attr("data-clinictype","");
				$(qRow).find(".q-share-icon").hide();
				$(qRow).find(".subj_name").html("");
				$(qRow).find(".q-uid").parent().html("-Not Bind-");

				sUrl="patient_info_idcard_new.php?showq=1&q="+sQ;
				$("#divUidSearch").parent().load(sUrl);
			}else{
				alert(rtnObj.msg);
				endLoad($("#btnUnbindQ"),$("#btnQ-loader"));
			}
			
		});
	});

	$("#divUidSearch #btnRegisterUid").off("click");
	$("#divUidSearch #btnRegisterUid").on("click",function(){
		if(validatePInfo()==false) return;
		if(!(confirm("ยืนยันลงทะเบียนผู้รับบริการ?"))){
			return;
		}

		/*
		var aData = {u_mode:"create_uid"};
		$("#divUidSearch .save-data").each(function(ix,objx){
			sOdata = $(objx).attr('data-odata');
			sTemp = ($(objx).val()+"").trim();
			if(sTemp!=sOdata){
				aData[$(objx).attr("id")] = encodeURIComponent(sTemp);
			}
		});
		*/
		aData=getAllData($("#divUidSearch"),"save-data");
		aData.u_mode="create_uid";


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


				setKeyAllOld($("#divUidSearch"),"save-data");
				//updatePInfoOData();
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

		sEmail = $("#email").val().trim();
		if(sEmail.length>0 && !(checkEmail(sEmail))){
			$.notify("Email รูปแบบไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง");
			bIsValid=false;
		}

		if($("#citizen_id").val().trim().length > 0 && $("#citizen_id").val().trim().length != 13){
			$.notify("เลขที่บัตรประชาชน ไม่ครบ 13 หลัก");
			bIsValid = false;
		}

		return bIsValid;
	}

	$("#divUidSearch #btnSavePInfo").off("click");
	$("#divUidSearch #btnSavePInfo").on("click",function(){
		sUid = $("#txtUid").val();
		if($("#txtUid").attr("readonly")){
			
		}else{
			$.notify("Please select UID first");
			return;
		}

		
		var isDataChanged = false;
		//validate input before create uid
		if(validatePInfo==false) return;

 		var aData=getDataRow($("#divUidSearch"),"save-data");

		if(aData!=""){
			aData.u_mode="update_patient_info";
			aData.uid=sUid;
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
					setKeyAllOld($("#divUidSearch"),"save-data");
					//updatePInfoOData();
				}
				endLoad($("#divUidSearch .pinfobtn"),$("#divUidSearch .btn-uid-loader"));
			});
		}else{
			$.notify("No Data Changed");
		}
	});


	$("#divUidSearch #btnFindUID").off("click");
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
		sUrl="patient_info_idcard_new.php?showq=1&uid="+sUid+"&lockq=1&q="+sQ;
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