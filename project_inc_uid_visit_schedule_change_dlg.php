<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");


$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sVisitid = getQS("visitid");
$sRow = getQS("row"); // visit no.

$sScheduledate = getQS("scheduledate");
$sVisitdate = getQS("visitdate");
$sType = getQS("type");



$umode = "";
$window_period_before = $sScheduledate;
$window_period_after = $sScheduledate;

if($sType == "schedule"){
	$umode = "change_schedule_date";
	$selectDate = $sScheduledate;
}
else if($sType == "visit"){
	$umode = "change_visit_date";
	$selectDate = $sVisitdate;
}


if($sVisitid != 'FU'){


include("in_db_conn.php");

$query = "SELECT PUL.enroll_date, VL.visit_day, VL.visit_day_before, VL.visit_day_after
FROM p_project_uid_list PUL, p_visit_list as VL
WHERE VL.proj_id=? AND VL.visit_id=? AND (VL.group_id=? OR VL.group_id='')
AND VL.proj_id=PUL.proj_id AND PUL.uid=?
";
//echo "$sProjid, $sVisitid, $sGroupid, $sUID / query: $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssss", $sProjid, $sVisitid, $sGroupid, $sUID);
if($stmt->execute()){
	$stmt->bind_result($enroll_date, $visit_day, $visit_day_before, $visit_day_after);
	if ($stmt->fetch()) {
  // echo "$enroll_date, $visit_day, $visit_day_before, $visit_day_after";


		$actual_scheduledate = addDayToDate($enroll_date, $visit_day);
	//	echo "actualdate: $actual_scheduledate ** $visit_day_before ** $visit_day_after";

		$window_period_before = addDayToDate($actual_scheduledate, "-$visit_day_before");
		$window_period_after = addDayToDate($actual_scheduledate, $visit_day_after);
	}// if
}
else{
	error_log($stmt->error);
}
$stmt->close();
$mysqli->close();
}
else{ // FU visit

	$window_period_before = addDayToDate($selectDate, "-90");
	$window_period_after = addDayToDate($selectDate, "90");

}


if($sRow == '1'){ // first visit eg. D0, W0, M0
	$window_period_before = addDayToDate($selectDate, "-360");
	$window_period_after = addDayToDate($selectDate, "360");
}




?>



<div class='fl-wrap-col div_change_schedule' style='min-width:800px;max-width:800px;'
 data-umode ='<? echo $umode; ?>' data-uid='<? echo $sUID; ?>' data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>'
 data-visitid='<? echo $sVisitid; ?>' data-sdate='<? echo $selectDate; ?>' data-row='<? echo $sRow; ?>'>
	<div class='fl-wrap-row ph50'>
		<div class='fl-fix pw250 ' >
			<?
			include("doctor_inc_appointments.php");
			?>
		</div>
		<div class='fl-wrap-col ' style='min-width:550px;max-width:550px;'>
			<div class='fl-fix fl-mid ph25 ptxt-b' >
				<? echo "UID: $sUID | Proj: $sProjid"; ?>
			</div>
			<div class='fl-fix fl-mid ph25 ptxt-u daterange' >
				<? echo "เลือกช่วงวันระหว่าง: $window_period_before ถึง $window_period_after"; ?>
			</div>
		</div>



	</div>

	<div class='fl-wrap-row' style='min-height:350px; max-height:350px;'>

		<div class='fl-wrap-col' style='min-width:250px; max-width:250px;'>
			<div class='fl-fill fl-auto '>
				<?
			//	$_REQUEST['uid'] = $sUID;
				include("proj_inc_div_uid_schedulelist.php");
				?>
			</div>
		</div>
		<div class='fl-fix' style='min-width:550px; max-width:550px;'>
			<div id='dateNewSchedule'></div>
		</div>
	</div>

	<div class='fl-fix fl-mid ph30'>
	   <div class='fl-fix fl-mid pw400'>
				<button id='btnNewScheduleDate' class='pbtn pbtn-ok'>Select new schedule date | เลือกวันนัดหมายใหม่</button>
				<i class='fa fa-spinner fa-spin fa-lg' style='display:none;'></i>
		 </div>
	</div>

</div>


<div class='div_other_proj_visit bg-msoft2' style='min-width:800px;max-width:800px;display:none;'>
   <div class='fl-wrap-row ph-30 ptxt-b bg-msoft1'>
		    <div class='fl-fill fl-mid'>มีโครงการอื่นที่มี Visit Date เดียวกัน แต่ยังไม่ได้ย้าย Visit Date ใน Schedule</div>
	 </div>
	 <div class='fl-wrap-row fl-fill bg-msoft3 div_other_proj_visit_info'>

	</div>
</div>


<script>
$(document).ready(function(){
  setDlgResult("0:0000-00-00:"); // 0: not select
	//$( "#dateNewSchedule" ).datepicker();
	let select_date = new Date();
	let window_period_before = new Date();
	let window_period_after = new Date();

	let dateVal1 = '<? echo $selectDate;?>'; let arrDate1 = dateVal1.split("-");
	let dateVal2 = '<? echo $window_period_before;?>'; let arrDate2 = dateVal2.split("-");
	let dateVal3 = '<? echo $window_period_after;?>'; let arrDate3 = dateVal3.split("-");

	select_date.setFullYear(arrDate1[0], parseInt(arrDate1[1])-1, arrDate1[2]);
	window_period_before.setFullYear(arrDate2[0], parseInt(arrDate2[1])-1, arrDate2[2]);
	window_period_after.setFullYear(arrDate3[0], parseInt(arrDate3[1])-1, arrDate3[2]);
/*
  if($(".div_change_schedule").attr("data-umode") == "change_schedule_date"){
		$( "#dateNewSchedule" ).datepicker({
		numberOfMonths: 2,
		showButtonPanel: true,
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true,
		minDate: window_period_before,
		maxDate: window_period_after
		});
	}
	else if($(".div_change_schedule").attr("data-umode") == "change_visit_date"){
		$( ".daterange" ).html('');
		$( "#dateNewSchedule" ).datepicker({
		numberOfMonths: 2,
		showButtonPanel: true,
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true
		});
	}
*/
	$( "#dateNewSchedule" ).datepicker({
	numberOfMonths: 2,
	showButtonPanel: true,
	dateFormat:"yy-mm-dd",
	changeYear:true,
	changeMonth:true,
	minDate: window_period_before,
	maxDate: window_period_after
	});

$('#dateNewSchedule').datepicker("setDate",select_date );


$("#btnNewScheduleDate").click(function(){
	 //console.log("date: "+$("#dateNewSchedule").val());
	 var aData = {
			 u_mode:$(".div_change_schedule").attr("data-umode"),
			 uid:	$(".div_change_schedule").attr("data-uid"),
			 projid:$(".div_change_schedule").attr("data-projid"),
			 groupid:$(".div_change_schedule").attr("data-groupid"),
			 visitid:$(".div_change_schedule").attr("data-visitid"),
			 previousdate:$(".div_change_schedule").attr("data-sdate"),
			 newdate:$("#dateNewSchedule").val(),
			 row:$(".div_change_schedule").attr("data-row")
	 };

	 startLoad($('#btnNewScheduleDate'), $("#btnNewScheduleDate").next(".fa-spinner"));
	 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				 endLoad($('#btnNewScheduleDate'), $("#btnNewScheduleDate").next(".fa-spinner"));
				 if(rtnObj.res == 1){

             let result = aData.row+":"+$("#dateNewSchedule").val();
					   //setDlgResult($("#dateNewSchedule").val());

             setDlgResult(result);
						 //setDlgResult(rtnObj);

						 if(rtnObj.lst_other_proj_visit.length > 0){

							 $('.div_other_proj_visit_info').html('');
							 let txtrow = '';
			         var datalist = rtnObj.lst_other_proj_visit;
			         datalist.forEach(function (itm) {
								 txtrow += '['+itm[1]+'] '+itm[0]+ ' | Group: '+itm[2]+' | Visit: '+itm[3]+' <u>'+aData.previousdate+'</u>';
			          // alert("item: "+itm[0]+" / "+itm[1]);
			         });
							 if(txtrow != ''){
								 console.log("txtrow: "+txtrow);
								 $('.div_other_proj_visit_info').html(txtrow);
								 $('.div_other_proj_visit').show();
								 $('.div_change_schedule').hide();
							 }
			       }
						 else{
							 closeDlg();
						 }


				 }
				 else{
					 $("#btnNewScheduleDate").notify("Fail to update.", "error");
					 alert("Fail to update");
				 }
	  });// call ajax
   }); // btnNewScheduleDate



});



</script>
