<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");


$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sIsAdmin = getQS("isadmin");
$selectDate = date('Y-m-d');

$window_period_before = $selectDate;
$window_period_after = addDayToDate($selectDate, 90);

$sIsAdmin = '0';

?>

<div class='fl-wrap-col div_change_schedule' style='min-width:900px;max-width:900px;'
 data-uid='<? echo $sUID; ?>' data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>' data-isadmin='<? echo $sIsAdmin; ?>'>
	<div class='fl-wrap-row ph50'>
		<div class='fl-fix v-mid bg-warning' style='min-width:300px;max-width:300px;'>
			<?
			include("doctor_inc_appointments.php");
			?>
		</div>
		<div class='fl-wrap-col fl-fill' >
			<div class='fl-fix fl-mid ph25 ptxt-b' >
				<? echo "UIDxx: $sUID | Proj: $sProjid"; ?>
			</div>
			<div class='fl-fix fl-mid ph25 ptxt-u daterange' >
				<? echo "เลือกช่วงวันระหว่าง: $window_period_before ถึง $window_period_after"; ?>
			</div>
		</div>



	</div>

	<div class='fl-wrap-row' style='min-height:350px; max-height:350px;'>

		<div class='fl-wrap-col' style='min-width:300px;max-width:300px;'>
			<div class='fl-fill fl-auto '>
				<?
			//	$_REQUEST['uid'] = $sUID;
				include("proj_inc_div_uid_schedulelist.php");
				?>
			</div>
		</div>
		<div class='fl-fill' >
			<div id='dateNewSchedule'></div>
		</div>
	</div>

	<div class='fl-fix fl-mid ph30'>
	   <div class='fl-fix fl-mid pw400'>
				<button id='btnNewScheduleDate' class='pbtn pbtn-ok'>Make new visit | สร้างนัดหมายใหม่</button>
				<i class='fa fa-spinner fa-spin fa-lg' style='display:none;'></i>
		 </div>
	</div>

</div>

<script>
$(document).ready(function(){

  setDlgResult('');
	//$( "#dateNewSchedule" ).datepicker();
	var select_date = new Date();

  if($(".div_change_schedule").attr("data-isadmin") == "1"){
		$( ".daterange" ).html('');
		$( "#dateNewSchedule" ).datepicker({
		numberOfMonths: 2,
		showButtonPanel: true,
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true
		});
	}
	else {
		let window_period_before = new Date();
		let window_period_after = new Date();

		let dateVal1 = '<? echo $selectDate;?>'; let arrDate1 = dateVal1.split("-");
		let dateVal2 = '<? echo $window_period_before;?>'; let arrDate2 = dateVal2.split("-");
		let dateVal3 = '<? echo $window_period_after;?>'; let arrDate3 = dateVal3.split("-");

		select_date.setFullYear(arrDate1[0], parseInt(arrDate1[1])-1, arrDate1[2]);
		window_period_before.setFullYear(arrDate2[0], parseInt(arrDate2[1])-1, arrDate2[2]);
		window_period_after.setFullYear(arrDate3[0], parseInt(arrDate3[1])-1, arrDate3[2]);


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


$('#dateNewSchedule').datepicker("setDate",select_date );


$("#btnNewScheduleDate").click(function(){
	 //console.log("date: "+$("#dateNewSchedule").val());
	 var aData = {
			 u_mode:"create_new_fu_visit",
			 uid:	$(".div_change_schedule").attr("data-uid"),
			 projid:$(".div_change_schedule").attr("data-projid"),
			 groupid:$(".div_change_schedule").attr("data-groupid"),
			 scheduledate:$("#dateNewSchedule").val()
	 };

	 startLoad($('#btnNewScheduleDate'), $("#btnNewScheduleDate").next(".fa-spinner"));
	 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				 endLoad($('#btnNewScheduleDate'), $("#btnNewScheduleDate").next(".fa-spinner"));
				 if(rtnObj.res == 1){
					   setDlgResult($("#dateNewSchedule").val());
             closeDlg($("#btnNewScheduleDate"));

				 }
				 else{
					 $("#btnNewScheduleDate").notify("Fail to update.", "error");
					 alert("Fail to update");
				 }
	  });// call ajax
   }); // btnNewScheduleDate



});



</script>
