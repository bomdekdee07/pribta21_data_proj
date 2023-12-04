<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_fn_date.php");

$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");

$txt_row = ""; $row_no = 0;
$proj_uid_status ="";
$proj_uid_remark ="";


if(!isset($proj_auth['allow_view'])){
  include_once("project_inc_uid_permission.php");
}
//echo $class_auth."<br>";

	if($proj_auth['allow_view'] == 1){

		if($sUID !="" && $sProjid != ""){
			include("in_db_conn.php");
/*
				$query ="SELECT PUV.visit_id, PUV.group_id, PUV.schedule_date,
				PUV.visit_date, PUV.visit_status, PUV.visit_note,
				VL.visit_name, PUV.visit_status, VL.visit_day_before, VL.visit_day_after, VS.status_name
				FROM p_project_uid_visit PUV
				LEFT JOIN p_visit_list VL ON ((PUV.proj_id=VL.proj_id OR VL.proj_id='') AND PUV.visit_id=VL.visit_id)
				LEFT JOIN p_visit_status VS ON (PUV.visit_status=VS.status_id)
				WHERE PUV.uid =? AND PUV.proj_id =?
				ORDER BY PUV.schedule_date, VL.visit_order
				";
*/
        $query ="SELECT PUV.visit_id, PUV.group_id, PUV.schedule_date,
        PUV.visit_date, PUV.visit_status, PUV.visit_note, PUV.schedule_note,
        VL.visit_name, PUV.visit_status, VL.visit_day_before, VL.visit_day_after, VS.status_name
        FROM p_project_uid_visit PUV
        LEFT JOIN p_visit_list VL ON ((PUV.proj_id=VL.proj_id ) AND PUV.visit_id=VL.visit_id)
        LEFT JOIN p_visit_status VS ON (PUV.visit_status=VS.status_id)
        WHERE PUV.uid =? AND PUV.proj_id =? AND PUV.visit_status <> 'C'
        ORDER BY PUV.schedule_date, VL.visit_order
        ";

				$stmt = $mysqli->prepare($query);
			  $stmt->bind_param('ss', $sUID, $sProjid);

				if($stmt->execute()){
					$result = $stmt->get_result();
          $is_visit_next = 0;
          $flag_check_next_visit = true;
          $row_v_visit = 0; // visit by doctor

          $prev_visit_date = "0000-00-00";
					while($row = $result->fetch_assoc()) {
						$row_no++;

            if($flag_check_next_visit){
              if($row["visit_date"] == '0000-00-00'){
                if($prev_visit_date == "0000-00-00"){
                  if($row_no != 1){ // not first visit
                    if($row["visit_status"] != 10){
                      $flag_check_next_visit = false;
                      $is_visit_next =1;
                    }
                    else $is_visit_next =0; // prev visit is lost to followup / ไม่มาตามนัด
                  }
                  else{
                    $flag_check_next_visit = false;
                    $is_visit_next =1;
                  }
                //  $is_visit_next = ($row_no != 1)?0:1;
                }
                else{
                  if($row["visit_status"] != 10){
                    $flag_check_next_visit = false;
                    $is_visit_next =1;
                  }
                  else $is_visit_next =0; // prev visit is lost to followup / ไม่มาตามนัด

                }
              }

            }
            else{
              $is_visit_next = 0;
            }

            $visit_name = $row["visit_name"];
            if($row["visit_id"] == 'FU') {
              $row_v_visit++;
              $visit_name .= " $row_v_visit";
            }


            $txt_row .= addRowScheduleList($row_no, $sUID, $sProjid, $row["group_id"],
            $row["schedule_date"], $row["visit_date"],
            $row["visit_id"], $visit_name, $row["visit_status"], $row["status_name"],
            $row["visit_note"], $row["schedule_note"],
            $row["visit_day_before"], $row["visit_day_after"], $is_visit_next);
            $prev_visit_date = $row["visit_date"];
					}//while
				}


			  $stmt->close();


		    if(isset($uid_status)){
					$proj_uid_status = $uid_status;
					$proj_uid_remark = isset($uid_remark)?$uid_remark:"";

				}
				else{
					$query ="SELECT PUL.uid_status, PUL.uid_remark
					FROM p_project_uid_list PUL
					WHERE PUL.uid =? AND PUL.proj_id =?
					";

					$stmt = $mysqli->prepare($query);
					$stmt->bind_param('ss',$sUID, $sProjid);
					if($stmt->execute()){
						$result = $stmt->get_result();
						while($row = $result->fetch_assoc()) {
							$proj_uid_status = $row['uid_status'];
							$proj_uid_remark = $row['uid_remark'];
						}//while
					}

				}

				$mysqli->close();
		}
		else{
		   $txt_row = "No Data Found.";
		}



	}
	else{
		$txt_row = $msg_not_allow_view;
	}




//echo "qs: $sProjid/$sGroupid/$sUID";





function addRowScheduleList($row_no, $uid, $proj_id, $group_id,
$schedule_date, $visit_date, $visit_id, $visit_name, $visit_status,$status_name, $visit_note, $schedule_note ,
$window_period_before, $window_period_after, $is_visit_next)
{

  global $proj_auth;

  $txt_window_period = "";
  if($window_period_before == '0' && $window_period_after == '0'){

  }
  else{ // defined window period
    $txt_window_before = addDayToDate($schedule_date, ($window_period_before)*-1);
    $txt_window_after = addDayToDate($schedule_date, $window_period_after);
    $txt_window_period = "[$txt_window_before - $txt_window_after]";
  }



  $txt_visit_date = "";
  $btn_change_schedule_date = "";
  $btn_change_visit_id = ""; // change visit date button
  $btn_undo_missing_visit = ""; // undo missing visit to incomming visit

  if(isset($proj_auth['allow_admin'])){
    if($proj_auth['allow_admin'] == 1){
      $btn_change_visit_id = "<i class='fas fa-cog fa-lg pbtn btn-edit-visitid mx-1' title='Change Visit ID'></i>";
    }
  }

  if(isset($proj_auth['allow_schedule'])){
    if($proj_auth['allow_schedule'] == 1){
      $btn_undo_missing_visit = "<i class='fas fa-undo fa-lg pbtn btn-undo-missing-visitid mx-1' title='Undo missing visit'></i>";
    }
  }

	if($visit_date != "0000-00-00"){ // not yet visit
    $txt_visit_date = "
     <div class='fl-wrap-row'>
       <div class='fl-wrap-col fl-mid pbtn pbtn-blue pw100 ph30 pround5 b-txt view-form vd-$row_no' data-visitid='$visit_id' data-visitdate='$visit_date'>
         <div class='fl-fix ' style='max-height:15px;min-height:15px;'><i class='fas fa-hand-point-right fa-lg'></i> <span class='v-$row_no'>  $visit_date</span></div>
         <div class='fl-fix ph10 ptxt-s8'>$status_name</div>

       </div>
       <div class='fl-fix pw50'>
         <button class='pbtn pbtn-warning change-visit auth-admin ' data-sdate='$schedule_date' data-vdate='$visit_date' data-vid='$visit_id' data-before='$window_period_before' data-after='$window_period_after' data-row='$row_no' title='Change Visit Date'><i class='fas fa-calendar-day fa-lg ' ></i></button>
       </div>
    </div>";


	}
	else { // visit date = 0000-00-00
    if($visit_status == 0){ // ยังไม่มาตามนัด
      if($is_visit_next == 0){ // not next visit
        $txt_visit_date = "- WAIT | รอการเข้านัดหมาย -";
      }
      else{ // next visit
        $txt_visit_date = "
        <div class='fl-wrap-row div_checkin' >
          <div class='fl-fix col70'>
          <button class='pbtn-ok btn-visit-checkin' data-sdate='$schedule_date' data-visitid='$visit_id' data-visitstatus='20'><i class='fas fa-person-booth fa-lg'></i> เข้านัด </button>
           </div>
          <div class='fl-fix col70'>
          <button class='pbtn-cancel btn-visit-checkin' data-sdate='$schedule_date'  data-visitid='$visit_id' data-visitstatus='10'><i class='fas fa-times fa-lg'></i> ไม่มานัด</button>
          </div>


        </div>
        <div class='fl-fill fl-mid spinner' style='display:none;'>
        <i class='fa fa-spinner fa-spin fa-lg' ></i>
        </div>
        ";
      }

      $btn_change_schedule_date = "<button class='change-schedule pbtn pbtn-blue'  title='Change Schedule Date'><i class='fas fa-calendar-day fa-lg ' ></i></button>";

    }
    else if($visit_status == 10){ // ไม่มาตามนัด
      $txt_visit_date = "<span class='ptxt-red'><i class='fas fa-times fa-lg'></i> ไม่มานัด</span> $btn_undo_missing_visit";

    }
	}

  //$classRow = ($visit_note == "")?"row35":"row35-a";
  //$classRow = ($visit_note == "")?"ph40":"ph60";
  $classRow = "h-60";

  $classVisit = "blue";
  if($visit_id == 'FU'){
    $classVisit = "black";

    if($visit_status == 0){
      if($proj_auth['allow_schedule'] == 1)
      $btn_change_schedule_date = "<button class='pbtn pbtn-cancel btn-delete-fu-visit' title='Delete Followup Schedule Date'><i class='fas fa-times fa-lg'></i></button><i class='fa fa-spinner fa-spin fa-lg spinner' style='display:none;'></i>";
    }
  }

  $schedule_note = ($schedule_note == '')?'<span class="ptxt-grey">[Add Schedule Note]</span>':$schedule_note;
  $visit_note = ($visit_note == '')?'<span class="ptxt-grey">[Add Visit Note]</span>':$visit_note;

  $txt_row = "
    <div class='fl-wrap-row  visit-row  $classRow p-row ptxt-s10 pt-1 px-1' data-row='$row_no' data-visitid='$visit_id' data-sdate='$schedule_date' data-vdate='$visit_date' data-vstatus='$visit_status' >
			<div class='fl-fix pw200 ptxt-b ptxt-$classVisit'><span class='ptxt-bg-$classVisit ptxt-s12'>$visit_id</span> $visit_name $btn_change_visit_id</div>
			<div class='fl-fix pw50 '>$group_id </div>
      <div class='fl-wrap-col pw200 '>
         <div class='fl-fix ph15 '>
           <span class='ptxt-b visit-$row_no'>$schedule_date</span> $btn_change_schedule_date
         </div>
         <div class='fl-fix ph15 pt-1 '>
           <span class='ptxt-s8'>$txt_window_period</span>
         </div>
      </div>
      <div class='fl-fill fl-auto vs-note' data-note='schedule'>
         $schedule_note
      </div>
    	<div class='fl-fix pw150 '>$txt_visit_date</div>
			<div class='fl-fill fl-auto vs-note' data-note='visit'>
         $visit_note
      </div>

    </div>
  ";
  return $txt_row;
}

$create_new_followup_schedule = "";

if(isset($proj_auth['allow_schedule'])){

  if($proj_auth['allow_schedule'] == 1){
    $create_new_followup_schedule= "
    <div class = 'fl-fix ph30 pt-2 ptxt-s10 pl-2 pbtn pbtn-grey btn-make-visit'>
       <i class='fa fa-calendar-day fa-lg'></i> สร้างนัดหมายใหม่ | Make new visit
    </div>

    <div class = 'fl-fix ph30 pt-2 ptxt-s10 pl-2' style='display:none;'>
       <i class='fa fa-spinner fa-spin fa-lg'></i> Making Visit
    </div>
    ";
  }
}


?>

<style>
.btn-visit-checkin{
  width:70px; height:30px;
}
</style>

  <div class='fl-wrap-row pt-1 <? echo $class_auth; ?>' id="div_visit_schedule_data"
    data-uid='<? echo $sUID; ?>' data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>'
    data-uidstatus='<? echo $proj_uid_status; ?>'  data-today='<? echo date('Y-m-d'); ?>'>

  	<div class='fl-wrap-col px-2 py-1  ptxt-s10 bg-msoft3 proj-pid-prop' style='min-width:220px; max-width:220px;'>
  		<div class = 'fl-fix ph20 fl-mid ptxt-white ptxt-b bg-mdark1'>

        PROJECT PID Information

  		</div>
  		<div class = 'fl-fix ph50 pt-2'>

        PID Status: <select id='ddUIDStatus' class='auth-view auth-data'>
         <option value='' disabled>-Select-</option>
         <? include("project_opt_uid_status.php") ?>
       </select>
         <i class='fa fa-spinner fa-spin fa-lg' style="display:none;"></i>
         <button class='ml-4 pbg-red ptxt-white btn-remove-pid' title='Remove this pid from project' style='display:none;'>Remove</button>
  	  </div>


  <?
      echo $create_new_followup_schedule;
      include("project_inc_uid_visit_form_log.php");
  ?>



  		<div class = 'fl-fix ph150 pt-2'>
  		Note: <br>
  		  <textarea id='txtProjNote' class='auth-view' rows="4" cols="37" data-odata='<? echo $proj_uid_remark;?>'><? echo $proj_uid_remark;?></textarea>
        <button id='txtProjNote_update' class='pbtn ptxt-white bg-mdark2 auth-data'>Update Note</button>
  			<i class='fa fa-spinner fa-spin fa-lg' style="display:none;"></i>

  		</div>

    </div>
    <div class='fl-wrap-col fl-fill pbg-white '>
  		<div class='fl-wrap-row bg-sdark2 ph20 ptxt-white ptxt-b ptxt-s10 px-1' >
  			<div class='fl-fix pw200'>Visit Name</div>
  			<div class='fl-fix pw50'>Group</div>
  			<div class='fl-fix pw100'>Schedule Date</div>
        <div class='fl-fill'>Schedule Note</div>
  			<div class='fl-fix pw150'>Visit Date</div>
  			<div class='fl-fill'>Visit Note</div>
  		</div>
      <div class='fl-wrap-row bg-msoft1 px-1' >
        <div class='fl-wrap-col fl-auto div-schedule-main'>
          <? echo $txt_row; ?>
        </div>
      </div>


    </div>
  </div>


<script>
$(document).ready(function(){
   <?

      echo" $('#ddUIDStatus').val('$proj_uid_status');";
      if($row_no < 2){
        echo" $('.btn-remove-pid').show();";
      }
   ?>
  setDivAuthComponent('#div_visit_schedule_data'); // set auth apply to div
  if(!$('#div_visit_schedule_data').hasClass('allow_schedule')){
    $('.change-schedule').remove();
  }
  if(!$('#div_visit_schedule_data').hasClass('allow_admin')) $('.change-visit').remove();


  $('#div_visit_schedule_data').off('click');
	$('#div_visit_schedule_data').on("click",".view-form",function(){
		let visitid=$(this).attr("data-visitid");
		let visitdate=$(this).attr("data-visitdate");
		//console.log("visit: "+visitid+"/"+visitdate);
    view_visit_form_list(visitid, visitdate);
	});


  	$('#div_visit_schedule_data').on("click",".btn-remove-pid",function(){
      $flag_del = confirm('Are you sure to remove this PID?  This will delete all related visit and data of this PID in this project.');

      if($flag_del){
        let btnclick = $(this);
        let sUid = $('#div_visit_schedule_data').attr("data-uid");
        let sProjid = $("#div_visit_schedule_data").attr("data-projid");
        //console.log("visit: "+visitid+"/"+visitdate);
        var aData = {
           u_mode:"remove_pid",
           uid:sUid,
           projid:sProjid
       };

        startLoad(btnclick, btnclick.next(".spinner"));
        callAjax("project_a_visit.php",aData,function(rtnObj,aData){
              endLoad(btnclick, btnclick.next(".spinner"));
              if(rtnObj.res == 1){
                $.notify("Remove PID successfully", "success");
                $('#div_proj_uid_info').html('PID Remove');
                $('#div_uid_visit_detail').html("");

              }
              else{
                $.notify("Fail to update.", "error");
                if(rtnObj.msg_error != '')
                $.notify(rtnObj.msg_error, "error");
              }
        });// call ajax
      }


  	});

  $('#div_visit_schedule_data').on("click",".btn-make-visit",function(){
    let btn = $(this);
    let uid = $('#div_visit_schedule_data').attr("data-uid");
    let projid = $("#div_visit_schedule_data").attr("data-projid");
    let groupid = $("#div_visit_schedule_data").attr("data-groupid");
    let sIsAdmin = ($("#div_visit_schedule_data").hasClass("allow_admin"))?'1':'0';
    //console.log("visit: "+visitid+"/"+visitdate);
    let sUrl = "project_inc_uid_visit_schedule_new_dlg.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&isadmin="+sIsAdmin;
    showDialog(sUrl,"Make New Visit | สร้างนัดหมายใหม่ ["+uid+"]","500","900","",function(sResult){
       //  console.log("sResult: "+sResult+"/"+scheduledate+"/"+row_no);
         if(sResult !=''){
            if(sResult == $('#div_visit_schedule_data').attr("data-today")){
              view_visit_form_list('FU', sResult);
            }
            else{
              view_visit_schedule_list();
            }
         }
    },false,"");
  });


	$('#div_visit_schedule_data').on("click",".btn-visit-checkin",function(){
		let sVisitid=$(this).parents('.visit-row').attr('data-visitid');
    let sScheduledate=$(this).parents('.visit-row').attr('data-sdate');

		//console.log("visit: "+visitid+"/"+visitdate);
		var aData = {
 			 u_mode:"checkin_visit",
 			 uid:	$("#div_visit_schedule_data").attr("data-uid"),
 			 projid:$("#div_visit_schedule_data").attr("data-projid"),
 			 groupid:$("#div_visit_schedule_data").attr("data-groupid"),

       visitid: sVisitid,
       scheduledate: sScheduledate,
       visitstatus: $(this).attr("data-visitstatus"),

 	 };

		startLoad($('.div_checkin'), $(".div_checkin").next(".spinner"));
		callAjax("project_a_visit.php",aData,function(rtnObj,aData){
					endLoad($('.div_checkin'), $(".div_checkin").next(".spinner"));
					if(rtnObj.res == 1){
						if(aData.visitstatus == '20') { // รอให้คำปรึกษา
							alert("เข้านัดหมาย "+aData.visitid+" ["+rtnObj.visitdate+"]");
							view_visit_form_list(aData.visitid, rtnObj.visitdate);
						}
						else if(aData.visitstatus == '10') {
							alert("ไม่มาตามนัดหมาย");
              view_visit_schedule_list();
						//	$(".div_checkin").html("<div class='fl-fix pw100'><span class='ptxt-red'><i class='fas fa-times fa-lg'></i> ไม่มานัด</span></div>");
						}
					}
					else{
						$(".btn-visit-checkin").notify("Fail to update.", "error");

            if(rtnObj.msg_error != '')
            $.notify(rtnObj.msg_error, "error");
					}
		});// call ajax
	});



$("#div_visit_schedule_data .btn-edit-visitid").off("click");
$("#div_visit_schedule_data").on("click",".btn-edit-visitid",function(){
   let sVisitid = $(this).closest(".visit-row").attr("data-visitid");
   let sScheduledate = $(this).closest(".visit-row").attr("data-sdate");
   let uid =	$("#div_visit_schedule_data").attr("data-uid");
   let projid = $("#div_visit_schedule_data").attr("data-projid");
   let groupid = $("#div_visit_schedule_data").attr("data-groupid");


  let sUrl = "project_inc_uid_visit_id_edit_dlg.php?uid="+uid+"&projid="+projid+"&visitid="+sVisitid+"&scheduledate="+sScheduledate;
  showDialog(sUrl," Change visit id  ["+sScheduledate+"]","130","500","",function(sResult){
     //  console.log("sResult: "+sResult+"/"+scheduledate+"/"+row_no);
       if(sResult == '1'){
         sUrlX = "project_inc_uid_visit_schedule_list.php?uid="+uid+"&projid="+projid+"&groupid="+groupid;
         $("#div_visit_schedule_data").parent().load(sUrlX);
       }
   },false,"");


});

$("#div_visit_schedule_data").on("click","#txtProjNote_update",function(){
	 if($('#txtProjNote').val().trim() == $('#txtProjNote').attr("data-odata") ){
		 $('#txtProjNote').notify("No data changed.", "info");
		 return;
	 }
	 var aData = {
			 u_mode:"update_project_note",
			 proj_note:	$('#txtProjNote').val().trim(),
			 uid:	$("#div_visit_schedule_data").attr("data-uid"),
			 projid:$("#div_visit_schedule_data").attr("data-projid"),
			 groupid:$("#div_visit_schedule_data").attr("data-groupid")
	 };
	 startLoad($('#txtProjNote_update'), $("#txtProjNote_update").next(".fa-spinner"));
	 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
				 endLoad($('#txtProjNote_update'), $("#txtProjNote_update").next(".fa-spinner"));
				 if(rtnObj.res == 1){
					 $("#txtProjNote").attr("data-odata", aData.proj_note);
					 $("#txtProjNote_update").notify("Project Note updated.", "success");

				 }
				 else{
					 $("#txtProjNote_update").notify("Fail to update.", "error");
				 }
	 });// call ajax
 });

	$("#div_visit_schedule_data").on("change","#ddUIDStatus",function(){
		 var aData = {
				 u_mode:"update_project_status",
				 status_id:	$(this).val(),
				 uid:	$("#div_visit_schedule_data").attr("data-uid"),
				 projid:$("#div_visit_schedule_data").attr("data-projid"),
				 groupid:$("#div_visit_schedule_data").attr("data-groupid")
		 };
		 startLoad($('#ddUIDStatus'), $("#ddUIDStatus").next(".fa-spinner"));
		 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
			     endLoad($('#ddUIDStatus'), $("#ddUIDStatus").next(".fa-spinner"));
					 if(rtnObj.res == 1){
						 $("#ddUIDStatus").notify("Status updated.", "success");
					 }
					 else{
						 $("#ddUIDStatus").notify("Fail to update.", "error");
					 }
		 });// call ajax
	 });


	 $("#div_visit_schedule_data").on("click",".change-schedule",function(){

       let objData = $(this);

       let row_no = objData.parents('.visit-row').attr('data-row');
       let scheduledate = objData.parents('.visit-row').attr('data-sdate');
       let visitid = objData.parents('.visit-row').attr('data-visitid');

/*
		   let row_no = $(this).attr("data-row"); // visit row ref no
		   let scheduledate = $(this).attr("data-sdate");
			 let visitid = $(this).attr("data-vid");
       */
			 let window_period_before = $(this).attr("data-before");
			 let window_period_after = $(this).attr("data-after");
			 let uid =$("#div_visit_schedule_data").attr("data-uid");
			 let projid =$("#div_visit_schedule_data").attr("data-projid");
			 let groupid =$("#div_visit_schedule_data").attr("data-groupid");
       let type = "schedule";

			 let sUrl = "project_inc_uid_visit_schedule_change_dlg.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&visitid="+visitid+"&scheduledate="+scheduledate+"&type="+type;
//       let sUrl = "project_inc_uid_visit_schedule_change_dlg.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&visitid="+visitid+"&scheduledate="+scheduledate+"&before="+window_period_before+"&after="+window_period_after+"&type="+type;

			 showDialog(sUrl,visitid+" Change Schedule | เปลี่ยนวันนัด ["+scheduledate+"]","500","800","",function(sResult){

          if(sResult == '1'){
            sUrlX = "project_inc_uid_visit_schedule_list.php?uid="+uid+"&projid="+projid+"&groupid="+groupid;
            $("#div_visit_schedule_data").parent().load(sUrlX);
          }
/*
            if(sResult != scheduledate && sResult!='' && sResult!='0:0000-00-00:'){
							$(".visit-"+row_no).html(sResult);
							$(".visit-"+row_no).notify("Change schedule to  "+sResult+".", "success");
              objData.parents('.visit-row').attr("data-sdate", sResult);
						}

*/
			 },false,"");

 	 });


	 $("#div_visit_schedule_data").on("click",".change-visit",function(){
       let objData = $(this);
       let row_no = objData.parents('.visit-row').attr('data-row');
       let scheduledate = objData.parents('.visit-row').attr('data-sdate');
       let visitid = objData.parents('.visit-row').attr('data-visitid');
       let visitdate = objData.parents('.visit-row').attr('data-vdate');

			 let uid =$("#div_visit_schedule_data").attr("data-uid");
			 let projid =$("#div_visit_schedule_data").attr("data-projid");
			 let groupid =$("#div_visit_schedule_data").attr("data-groupid");
			 let type = "visit";

			 let sUrl = "project_inc_uid_visit_schedule_change_dlg.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&visitid="+visitid+"&scheduledate="+scheduledate+"&visitdate="+visitdate+"&type="+type+"&row="+row_no;
			 showDialog(sUrl,visitid+" Change Visit | เปลี่ยนวันเข้าตรวจ ["+visitdate+"]","500","800","",function(sResult){
            //console.log("sResult: "+sResult+"/"+visitdate+"/"+row_no);
            if(sResult == '1'){
              sUrlX = "project_inc_uid_visit_schedule_list.php?uid="+uid+"&projid="+projid+"&groupid="+groupid;
              $("#div_visit_schedule_data").parent().load(sUrlX);
            }

            /*
            let arr_sResult = sResult.split(":");
            if(arr_sResult[0] == '0'){ // not select
            }
            else if(arr_sResult[0] == '1'){ // first visit
              view_visit_schedule_list(uid,projid,groupid);
            }
            else { // other visit
              let visit_date_change = arr_sResult[1];
              $(".v-"+row_no).html(visit_date_change);
              $(".v-"+row_no).notify("Change visit to  "+visit_date_change+".", "success");
              $(".vd-"+row_no).attr("data-visitdate", visit_date_change);
              objData.parents('.visit-row').attr('data-vdate', visit_date_change);
            }
            */

			 },false,"");

  });


   $("#div_visit_schedule_data").on("click",".btn-undo-missing-visitid",function(){
       let objData = $(this);
       let sScheduledate = objData.parents('.visit-row').attr('data-sdate');
       let sVisitid = objData.parents('.visit-row').attr('data-visitid');

			 let sUID =$("#div_visit_schedule_data").attr("data-uid");
			 let sProjid =$("#div_visit_schedule_data").attr("data-projid");
			 let sGroupid =$("#div_visit_schedule_data").attr("data-groupid");

       var aData = {
    			 u_mode:"undo_missing_visit",
    			 uid:sUID,
    			 projid:sProjid,
           visitid:sVisitid,
    			 scheduledate:sScheduledate
    	 };

       startLoad(objData,objData.next(".fa-spinner"));
  		 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
  			     endLoad(objData,objData.next(".fa-spinner"));
  					 if(rtnObj.res == 1){
                sUrlX = "project_inc_uid_visit_schedule_list.php?uid="+sUID+"&projid="+sProjid+"&groupid="+sGroupid;
                $("#div_visit_schedule_data").parent().load(sUrlX);
             }
  					 else{
  						 $.notify("No row update.", "info");
  					 }
  		 });// call ajax

 	 });


   $("#div_visit_schedule_data").on("click",".btn-delete-fu-visit",function(){
       let objData = $(this);
       let row_no = objData.parents('.visit-row').attr('data-row');
       let sScheduledate = objData.parents('.visit-row').attr('data-sdate');
       let sVisitid = objData.parents('.visit-row').attr('data-visitid');

			 let sUID =$("#div_visit_schedule_data").attr("data-uid");
			 let sProjid =$("#div_visit_schedule_data").attr("data-projid");
			 let sGroupid =$("#div_visit_schedule_data").attr("data-groupid");

       var aData = {
    			 u_mode:"remove_fu_visit",
    			 uid:sUID,
    			 projid:sProjid,
           visitid:sVisitid,
    			 scheduledate:sScheduledate
    	 };

       startLoad(objData,objData.next(".fa-spinner"));
  		 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
  			     endLoad(objData,objData.next(".fa-spinner"));
  					 if(rtnObj.res == 1){
  						 $.notify("Delete Followup Visit ["+aData.scheduledate+"].", "success");
               $('.visit-row[data-row="'+row_no+'"]').remove();

             }
  					 else{
  						 $.notify("Fail to update.", "error");
  					 }
  		 });// call ajax

 	 });

   $('#div_visit_schedule_data').on("click",".vs-note",function(){
     let objData = $(this);
     let scheduledate = objData.parents('.visit-row').attr('data-sdate');
     let visitid = objData.parents('.visit-row').attr('data-visitid');
     let visitdate = objData.parents('.visit-row').attr('data-vdate');

    let uid =$("#div_visit_schedule_data").attr("data-uid");
    let projid =$("#div_visit_schedule_data").attr("data-projid");
    let groupid =$("#div_visit_schedule_data").attr("data-groupid");

 	  let notetype=$(this).attr("data-note");
    let sUrl = 'project_inc_uid_visit_note_dlg.php?uid='+uid+'&projid='+projid+'&groupid='+groupid+'&scheduledate='+scheduledate+'&visitid='+visitid+'&notetype='+notetype;
    //console.log("link:"+sUrl);

    showDialog(sUrl,notetype+" note [UID: "+uid+ "|Visit:"+visitid+"]","140","400","",function(sResult){

        if(sResult != ""){
           objData.html(sResult);
  			}
        else{
           objData.html('<span class="ptxt-grey">[Add '+notetype+' note]</span>');
        }
  	},false,function(){

  	});

 	});

});

//function create_new_schedule(sUID, sProjid, sGroupid )

function loadLink(sUrl, selector_dest, selector_loader){

//	console.log("loadlink99: "+sUrl);
	startLoad(selector_dest,selector_loader);
	selector_dest.html("");
	selector_dest.load(sUrl,function(){
		endLoad(selector_dest,selector_loader);
	});
}

</script>
