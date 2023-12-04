<?
include("in_session.php");
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate = getQS("coldate");
$sColTime = getQS("coltime");
$sClinic = getQS("clinic","IHRI");
$sQueue = getQS("q");
$sId = getQS("s_id");
$sSid = getSS("s_id");


if($sColDate=="") {$sColDate = getQS("curdate"); $_GET["coldate"] = $sColDate;}
if($sColTime=="") {$sColTime = getQS("curtime"); $_GET["coltime"] = $sColTime;}


if($sUid=="") echo("Error No UID Found");

$aFormList = array("DEMO_PRIBTA","BRA_ASSIST_PRIBTA_V2_1");
//Don't forget to edit queue_inc_list.php

$sFormList = "'".implode("','",$aFormList)."'";
$aFormName_en[0] = "Questionnaire 1";
$aFormName_th[0] = "แบบสอบถามชุดที่ 1";

$aFormName_en[1] = "Questionnaire 2";
$aFormName_th[1] = "แบบสอบถามชุดที่ 2";


$aFormLogin = array("MENTAL_HEALTH_PHQ9", "PRIBTA_PROVIDER_V2", "PRIBTA_STIs");
$sFormLoginList="'".implode("','",$aFormLogin)."'";
$aFormLoginName_en[0] = "แบบทดสอบภาวะซึมเศร้า PHQ-9";
$aFormLoginName_th[0] = "แบบทดสอบภาวะซึมเศร้า PHQ-9";

$aFormLoginName_en[1] = "Counselor Form";
$aFormLoginName_th[1] = "ผู้ให้คำปรึกษาพริบตา";

$aFormLoginName_en[2] = "STI Form";
$aFormLoginName_th[2] = "แบบเก็บข้อมูลโรคติดต่อทางเพศสัมพันธ์";

$_GET["next_form_id"] = str_replace("'","",$sFormList);

if($sUid==""){
	echo("No UID Found");
	exit();
}

$sToday = date("Y-m-d");

include("in_db_conn.php");

//GET All Visit List for this UID
$sOptVisit = "";


$query =" SELECT uid,collect_date,collect_time FROM i_queue_list WHERE uid=? AND clinic_id=? ORDER BY collect_date DESC,collect_time DESC ;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sUid,$sClinic);

if($stmt->execute()){
  $stmt->bind_result($uid,$collect_date,$collect_time);
  while ($stmt->fetch()) {
	if($sColDate=="") $sColDate = $collect_date;
	if($sColTime=="") $sColTime = $collect_time;
	$sOptVisit .= "<option value='$collect_date $collect_time' ".((($sColDate." ".$sColTime)==$collect_date." ".$collect_time)?"selected":"").">".$collect_date." ".$collect_time."</option>";
  }
}



//Check Form Done
$jsHtml = ""; 
$query ="SELECT form_id,record_datetime,update_datetime FROM p_data_form_done WHERE uid=? AND collect_date=? AND collect_time=? AND is_done = 1 AND form_id IN (".$sFormList.",".$sFormLoginList.");";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);
if($stmt->execute()){
	$stmt->bind_result($form_id,$record_datetime,$update_datetime);
	 while ($stmt->fetch()) {
	 	$jsHtml .= "$(\".btnform[data-formid='".$form_id."']\").attr(\"data-done\",\"1\");";
	 	$jsHtml .= "$(\".btnform[data-formid='".$form_id."']\").css(\"background-color\",\"lightgreen\");";
	 }
}
$stmt->close();

// ADD new condition PHQ9 RED
$bind_param = "sss";
$array_val = array($sUid,$sColDate,$sColTime);
$data_check_mental = 0;
$data_check_score = "";

$query = "SELECT dt_main.data_result,
	dt_score.data_result AS check_score
from p_data_result dt_main
left join p_data_result dt_score on(dt_score.uid = dt_main.uid and dt_score.collect_date = dt_main.collect_date and dt_score.collect_time = dt_main.collect_time and dt_score.data_id = 'total_score')
where dt_main.uid = ?
and dt_main.collect_date = ?
and dt_main.collect_time = ?
and dt_main.data_id in ('depression_1', 'depression_2');";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$data_check_mental += ($row["data_result"]=="Y"? $data_check_mental++: 0);
		$data_check_score = $row["check_score"];
	}
	// echo $data_check_mental;
}
$stmt->close();

if($data_check_mental > 0 && $data_check_score == ""){
	$jsHtml .= "$(\".btnform[data-formid='MENTAL_HEALTH_PHQ9']\").css(\"background-color\",\"#E74C3C\");";
}

//Check form consent
$bind_param = "s";
$array_val = array($sUid);
$condition_consent = "";

$query = "SELECT count(*) AS count_consent
from consent_data
where uid = ?;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$stmt->bind_result($count_consent);
	while($stmt->fetch()){
		$condition_consent = $count_consent;
	}
}
$stmt->close();

//Check section
$bind_param = "ss";
$array_val = array($sSid, $sClinic);
$check_section_consent = array();

$query = "select count(*) AS check_section from i_staff_clinic where s_id = ? and clinic_id = ? and section_id = 'D01' and sc_status = '1';";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$check_section_consent = $row;
	}
	// print_r($check_section_consent);
}
$stmt->close();

$bind_param = "s";
$array_val = array($sSid);
$check_section_receive_st = "";
$check_section_data_st = "";

$query = "select count(recieve.s_id) AS check_section_receive,
count(s_data.s_id) AS check_section_data
from i_staff_clinic recieve
left join i_staff_clinic s_data on(s_data.s_id = recieve.s_id and s_data.clinic_id = recieve.clinic_id and s_data.section_id = 'D10')
where recieve.s_id = ? 
and recieve.clinic_id = 'IHRI' 
and recieve.section_id = 'D01';";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$stmt->bind_result($check_section_receive, $check_section_data);
	while($stmt->fetch()){
		$check_section_receive_st = $check_section_receive;
		$check_section_data_st = $check_section_data;
	}
}
$stmt->close();
$mysqli->close();

$jsHtml.="var sAllForm=\"".str_replace("'","",$sFormList)."\";";

$sQS = "?uid=".$sUid."&coldate=".$sColDate."&coltime=".$sColTime."&next_form_id=".str_replace("'","",$sFormList.",THANKS_CLINIC");
$sObjProp = " data-uid='".$sUid."' data-coldate='".$sColDate."' data-coltime='".$sColTime."'";
if($sColDate==""){
	echo("No Record Found");
	exit();
}
?>
<style>
	.btnform{
		background-color: silver;
	}
</style>
<div class='fl-wrap-col' id='qa_data_defult' data-consent='<? echo $condition_consent; ?>' data-suid='<? echo $sUid; ?>' data-checkrec='<? echo $check_section_receive_st; ?>' data-checdata='<?echo $check_section_data_st; ?>' data-sid='<?echo $sSid; ?>' data-checkss='<? echo $check_section_consent["check_section"]; ?>'>
	<div class='fl-wrap-row h-50 lh-25'>
		<div class='fl-fix w-550'>
			<!-- Visit : <span id='txtColDate'><? echo($sColDate); ?></span> - <span id='txtColTime'><? echo($sColTime); ?></span --> 
			<? echo($sClinic." : ".$sUid." - Queue # : ".$sQueue); ?> Visit : <SELECT id='ddlVisitList' ><? echo($sOptVisit); ?></SELECT><i id='btnViewData' class='fabtn fas fa-search-plus fa-lg'></i>
		</div>
		<div class="fl-fill">
			<button class="btn" id="bt_consent_view" style="padding-bottom: 1px; padding-top: 1px;">เงื่อนไขข้อตกลง</button>
		</div>
		<div class="fl-fix w-400 btnclinicform" id='divAppointment'>
            <? include("doctor_inc_appointments.php"); ?>
        </div>

		<div class='fl-fix fl-mid w-ss' style='padding:0 5px'>
			<input id='isLogin' type='hidden' value='<? echo(($sSid=="")?"0":"1"); ?>' /><i class='fabtn fas fa-door-open btnQuickLogin'></i>
		</div>
		
	</div> 
	<div class='fl-wrap-row fl-fix div-btn-list' style='min-height:30px;line-height: 30px' <? echo($sObjProp); ?> data-sid='<? echo($sId); ?>' >

		<?
			$sHtml = "";
			foreach ($aFormList as $iKey => $form_id) {
				$sHtml .="<div class='fl-fill btn div-btn btnform' data-formid='".$form_id."' data-done='0' data-open='0'>
					".$aFormName_th[$iKey]."<br/>".$aFormName_en[$iKey]."
					</div>";
			}


			foreach ($aFormLogin as $iKey => $form_id) {
				$sHtml .="<div class='fl-fill btn div-btn btnform btnclinicform' data-formid='".$form_id."' data-done='0' data-open='0' style='".(($sSid=="")?"display:none":"")."'>
					".$aFormLoginName_th[$iKey]."<br/>".$aFormLoginName_en[$iKey]."
					</div>";
			}	

			echo($sHtml);
		?>
		
	</div>
	<div class='fl-fill' style='text-align:center'>
		<iframe id='frmQN' width="100%" height="100%" style='display:none' src='patient_info_index.php<? echo($sQS); ?>  '>

		</iframe>
		<img id='frmQN-loader' style='margin-top:30px;height:100px' src='assets/image/spinner.gif' />
	</div>
</div>

<script>
	<? echo($jsHtml); ?>

	$(document).ready(function(){
		//BOM add bt consent
		$("#bt_consent_view").off("click");
		$("#bt_consent_view").on("click", function(){
			var s_uid = $("#qa_data_defult").data("suid");
			var check_sid = $("#qa_data_defult").data("sid");
			var sUrl_consent_main = "consent_data_main.php?uid="+s_uid;
			// console.log(sUrl_consent_main);

			if(check_sid == ""){
            	showDialog_edit(sUrl_consent_main, "หนังสือให้ความยินยอมเกี่ยวกับข้อมูลส่วนบุคคล", "100%", "70%", false, function(sResult){}, true, function(sResult){});
			}
			else{
				showDialog(sUrl_consent_main, "หนังสือให้ความยินยอมเกี่ยวกับข้อมูลส่วนบุคคล[Admin]", "100%", "70%", "", function(sResult){}, "", function(sResult){});
			}
		});

		var check_condition_consent = $("#qa_data_defult").data("consent");
		var check_rec = $("#qa_data_defult").data("checkrec");
		var check_data = $("#qa_data_defult").data("checdata");
		var check_sid = $("#qa_data_defult").data("sid");
		var check_ss = $("#qa_data_defult").data("checkss");
		// console.log("TEST:"+check_ss);
		if(check_condition_consent == "0"){
			var s_uid = $("#qa_data_defult").data("suid");
			var sUrl_consent_main = "consent_data_main.php?uid="+s_uid;
			// console.log(sUrl_consent_main);

			if(check_sid == ""){
				showDialog_edit(sUrl_consent_main, "หนังสือให้ความยินยอมเกี่ยวกับข้อมูลส่วนบุคคล", "100%", "70%", false, function(sResult){}, true, function(sResult){});	
			}
			else{
				if(check_ss == "1"){
					showDialog(sUrl_consent_main, "หนังสือให้ความยินยอมเกี่ยวกับข้อมูลส่วนบุคคล[Admin]", "100%", "70%", "", function(sResult){}, "", function(sResult){});
				}
			}
		}
	});

	$(".btnQuickLogin").unbind("click");
	$(".btnQuickLogin").on("click",function(){
		if($("#isLogin").val()=="0"){
			sUrl = "login_inc.php?hidelogo=1";
			showDialog(sUrl,"Staff Login","440","420","",function(sResult){
				//CLose function
				if(sResult=="1"){
					$("#isLogin").val("1");
					$(".btnclinicform").show();
				}

			},false,function(){
				//Load Done Function
			});
		}else{
			if(confirm("Would you like to logout?\r\nต้องการออกจากระบบ?")){
				var aData = {u_mode:"logout",sesskey:$("#txtSS").val()};

				//$("body").find(".mainbody").hide();
				callAjax("login_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res=="1"){
						$.notify("Logout","success");
						$("#isLogin").val("0");
						$(".btnclinicform").hide();
					}else{
						$.notify("Logout Fail."+rtnObj.msg,"warn");
					}
				});
				


			}
		}

	});

	$(".btnform").unbind("click");
	$(".btnform").on("click",function(){
		sFormId = $(this).attr('data-formid');
		$(this).attr('data-open',"1");
		objRow = $(this).closest(".div-btn-list");
		sUid = $(objRow).attr("data-uid");
		coldate = $(objRow).attr("data-coldate"); 
		coltime = $(objRow).attr("data-coltime");

		let aForm = sAllForm.split(","); let sNextForm = ""; isNextOne = false;

		for ( var ix = 0 ; ix < aForm.length; ix++ ) {
			if(isNextOne) {
				sNextForm = aForm[ix];
				ix = aForm.length;
			}
			else if(aForm[ix]==sFormId){
				isNextOne = true;
			}
		}

		sNextForm += ((sNextForm=="")?"":",")+"THANKS_CLINIC";
		//sUrl = "p_form_view.php?form_id="+sFormId+"&uid="+sUid+"&coldate="+coldate+"&coltime="+coltime+"&lang=th&s_id=patient&next_form_id="+sNextForm;
 		sUrl = "ext_index.php?file=p_form_view&s_id=patient&form_id="+sFormId+"&lang=th&uid="+sUid+"&coldate="+coldate+"&coltime="+coltime+"&next_form_id="+sNextForm;

		//sUrl = "../weclinic/data_mgt/mnu_form_view.php?form_id="+sFormId+"&uid="+sUid+"&collect_date="+coldate+"&collect_time="+coltime+"&s_id=patient&next_form_id="+sNextForm;


		$("#frmQN-loader").show();
		$("#frmQN").hide();
		$("#frmQN").attr("src",sUrl);
	});

   	var isLoad = false; var isDone = false; 

	function checkFormDone() {
		if(isDone) return;

		sFormList = ""; iDone = 0; iTotal = 0;
		sUid = $(".div-btn-list").attr("data-uid");
		sColDate = $(".div-btn-list").attr("data-coldate");
		sColTime = $(".div-btn-list").attr("data-coltime");

		//Making a list of form that is not done
		$(".btnform").each(function(ix,objx){
			if($(objx).attr("data-done")=="0" && $(objx).attr("data-open")=="1"){
				sFormList += ((sFormList=="")?"":",")+$(objx).attr('data-formid');
			}else if($(objx).attr("data-done")=="1"){
				iDone++;
			}
			iTotal++;
		});
		//All Done

		var aData = {u_mode:"check_form_done",formlist:sFormList,uid:sUid,coldate:sColDate,coltime:sColTime};

		if(iDone==iTotal) {
			isDone = true;
			//Alert reception since it done.
			aData.addmsg="1";
		}



		if(sFormList=="") return;

		ajaxFormDone(aData);


	}

	function ajaxFormDone(aData){
		callAjax("form_a_done_list.php",aData,function(rtnObj,aData){

			if(rtnObj.res=="0"){
									
			}else if(rtnObj.res=="1"){
				var datalist = rtnObj.datalist;

				for (i = 0; i < datalist.length; i++) {
					var objX = datalist[i];
					$(".btnform[data-formid='"+objX.form_id+"']").attr("data-done","1");
					$(".btnform[data-formid='"+objX.form_id+"']").css("background-color","lightgreen");
				}
			}
		});		
	}

	$("#btnViewData").unbind("click")
	$("#btnViewData").on("click",function(){
	    $("#frmQN-loader").show();
		$("#frmQN").hide();

		aVisit = $("#ddlVisitList").val().split(" ");
		colDate =aVisit[0];
		colTime =aVisit[1];

		sUid = $(".div-btn-list").attr("data-uid");
		$(".div-btn-list").attr("data-coldate",colDate);
		$(".div-btn-list").attr("data-coltime",colTime);
		//$("#txtColDate").html(colDate);
		//$("#txtColTime").html(colTime);

		$(".btnform").attr("data-done","0");
		$(".btnform").css("background-color","silver");

		$(".btnform").attr("data-open","1");
		checkFormDone();
		isDone=false;
		let sUrl = "patient_info_index.php?uid="+sUid+"&coldate="+colDate+"&coltime="+colTime+"&next_form_id="+sAllForm+",THANKS_CLINIC";
		$('#frmQN').attr("src",sUrl);

	});

	$('#frmQN').unbind('load');
	$('#frmQN').on('load', function() {
	    // code will run after iframe has finished loading
		let aForm = sAllForm.split(","); 

		for ( var ix = 0 ; ix < aForm.length; ix++ ) {
			if($("#frmQN").attr("src").indexOf("form_id="+aForm[ix])){
				$(".btnform[data-formid='"+aForm[ix]+"']").attr("data-open","1");
			}
		}

	    $("#frmQN-loader").hide();
		$("#frmQN").show();
		checkFormDone();
		/*
		if(isLoad==false && isDone==false){
			setInterval(checkFormDone, 10000);
			isLoad=true;
		}
		*/
	});
</script>