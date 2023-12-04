<?
/* Project UID Visit Main  */
include("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sUID = getQS("uid");

?>


<div class='fl-wrap-row ph50 bg-msoft3' id="div_proj_uid_info"
data-uid='<? echo $sUID;?>' data-projid='<? echo $sProjid;?>' data-groupid='<? echo $sGroupid;?>'>
	<div class='fl-wrap-col fl-fill'>
       <? include_once("project_inc_uid_info.php"); ?>
  </div>
  <div class='fl-wrap-col' style="max-width:24px;min-width:24px;">
     <i class='fas fa-window-close fa-lg pt-1  btnclose proj-current-close' data-page="proj_uid_visit"  title="Close"></i>
  </div>
</div>



<div class='fl-wrap-row fl-auto' id='div_uid_visit_detail'  >
	<div class='fl-wrap-col fl-fill visit-page visit-schedule-list' >
     <? include_once("project_inc_uid_visit_schedule_list.php"); ?>
  </div>
  <div class='fl-wrap-col visit-page visit-form-list' style="display:none;">

  </div>
	<div class='fl-wrap-col visit-page-load fl-mid' style="display:none;">
		<i class='fa fa-spinner fa-spin fa-lg ' ></i>
	</div>

</div>


<script>
$(document).ready(function(){

	$('#div_uid_visit_detail .view-form-log').unbind("click");
	$('#div_uid_visit_detail').on("click",".view-form-log",function(){

		let formid=$(this).attr("data-formid");
		let formname=$(this).attr("data-formname");
		let uid = $("#div_proj_uid_info").attr("data-uid");
		let projid = $("#div_proj_uid_info").attr("data-projid");
    view_visit_form_log(uid, formid, formname, projid);
	});


});

function view_visit_form_list(visitID, visitDate, groupid_row = ""){
	let uid = $("#div_proj_uid_info").attr("data-uid");
	let projid = $("#div_proj_uid_info").attr("data-projid");
	let groupid = $("#div_proj_uid_info").attr("data-groupid");

	if(groupid == ""){
		groupid = groupid_row;
	}
	let visitTime="00:00:00";
	$(".visit-page").hide();
	$(".visit-form-list").html("");
	let sUrl = "project_inc_uid_visit_form_list.php?uid="+uid+"&projid="+projid+"&groupid="+groupid+"&visitid="+visitID+"&visitdate="+visitDate+"&visittime="+visitTime;
	loadLink(sUrl, $(".visit-form-list"), $(".visit-page-load"));
}

function view_visit_schedule_list(){
	let uid = $("#div_proj_uid_info").attr("data-uid");
	let projid = $("#div_proj_uid_info").attr("data-projid");
	let groupid = $("#div_proj_uid_info").attr("data-groupid");

  $(".visit-page").hide();
	$(".visit-schedule-list").html("");
	let sUrl = "project_inc_uid_visit_schedule_list.php?uid="+uid+"&projid="+projid+"&groupid="+groupid;
//console.log("page: "+sUrl);
	//loadLink(sUrl, $(".visit-schedule-list"), $(".visit-page-load"));

	startLoad($(".visit-schedule-list"),$(".visit-page-load"));
	$(".visit-schedule-list").html("");
	$(".visit-schedule-list").load(sUrl,function(){
		endLoad($(".visit-schedule-list"),$(".visit-page-load"));

	});

}


function view_visit_form_log(uid, formid, formname, projid){
  // console.log("view log form: "+formid+"/"+uid);

	let sUrl = "p_form_view_log.php?uid="+uid+"&formid="+formid+"&projid="+projid;
	showDialog(sUrl,"UID: "+uid+ " ["+formid+"]","90%","99%","",function(sResult){
			if(sResult != ""){
			}
	},false,function(){
	});

}




</script>
