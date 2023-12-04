<?
/* Project Thumbnail list  */
include("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
//echo "qs: $sProjid/$sGroupid";

?>


<style>
.div-p-id{
  min-width:100px;
  max-width:100px;
  padding:5px;
}
.div-p-date{
  min-width:100px;
  max-width:100px;
  padding:5px;
}
.div-p-data{
  padding:5px;
}
</style>

<div class='fl-wrap-row' id='div_search_schedule' style="max-height:40px;padding-bottom:5px;"
data-projid='<? echo $sProjid; ?>' data-groupid='<? echo $sGroupid; ?>'>
 <div class='fl-wrap-col fl-fill pl-2' style='max-width:120px'>
	 <div class='proj-label'>Schedule Fromx:</div>
   <input type='text' class='proj-schedule-date inpproj' id='sDateFrom' >
 </div>
 <div class='fl-wrap-col fl-fill pl-2' style='max-width:120px'>
	 <div class='proj-label'>Schedule To:</div>
	 <input type='text' class='proj-schedule-date inpproj' id='sDateTo' >
 </div>
 <div class='fl-wrap-col pl-1' style='max-width:50px'>
	 <div class='proj-label-btn'>.</div>
	<button id='btnSearchSchedule' class='fabtn' title='Search'>
		<i class='fas fa-search fa-sm'></i>
	</button>
 </div>
</div>

<div class='fl-wrap-row bg-info' >
  <div class='fl-wrap-col px-2'>
    <div class='fl-wrap-row' style="max-height:60px;">
      <div class='fl-fix div-p-id colhead'>PID</div>
      <div class='fl-fix div-p-id colhead'>UID</div>
      <div class='fl-fix div-p-id colhead'>UIC</div>
      <div class='fl-fill div-p-data colhead'>Visit ID</div>
      <div class='fl-fix div-p-date colhead'>Schedule</div>
      <div class='fl-fix div-p-date colhead'>Visit</div>
    </div>
    <div class='fl-wrap-col fl-auto' id='div_proj_search_schedule_data'>
        <? //include_once("")?>
    </div>
  </div>
</div>


<script>
$(document).ready(function(){
	$(".proj-schedule-date").datepicker('setDate',new Date());
//  searchProjSchedule();
});

$(".proj-schedule-date").datepicker({
	dateFormat:"yy-mm-dd",
	changeYear:true,
	changeMonth:true
});


function searchProjSchedule(){
  var aData = {
      u_mode:"search_schedule",
      projid:	$("#div_search_schedule").attr("data-projid"),
      groupid:	$("#div_search_schedule").attr("data-groupid"),
      date_form:$('#sDateFrom').val(),
      date_to:$('#sDateTo').val()
  };
//  console.log("searchProjSchedule: "+aData.projid+"/"+aData.groupid+"/"+aData.date_form+"/"+aData.date_to+"/");
  callAjax("project_a_search_uid.php",aData,function(rtnObj,aData){
        $('#div_proj_search_schedule_data').html("");
        let txt_row = "";
        if(rtnObj.datalist.length > 0){
          let datalist = rtnObj.datalist;

          datalist.forEach(function (itm){
               console.log("uid: "+itm.uid);
/*
               txt_row += "<div class='fl-wrap-row'>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fill div-p-data '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-date '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-date '>"+itm.uid+"</div>";
               txt_row += "</div>";

*/

          //     txt_row += "<div class='fl-wrap-row'>";
               txt_row += "<div>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-id '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fill div-p-data '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-date '>"+itm.uid+"</div>";
               txt_row += "<div class='fl-fix div-p-date '>"+itm.uid+"</div>";
               txt_row += "</div>";

               //txt_row += "<div>uid:"+itm.uid+"</div>";
          });

        }
txt_row = "xxxxdddd "+txt_row;
        $('#div_proj_search_schedule_data').html(txt_row);
  });
}

</script>
