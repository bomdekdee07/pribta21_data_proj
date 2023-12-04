<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");

$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sVisitid = getQS("visitid");
$sScheduledate = getQS("scheduledate");
$sNotetype = getQS("notetype");

$txtNote= "";

include("in_db_conn.php");

$query ="SELECT PUV.visit_note, PUV.schedule_note
FROM p_project_uid_visit PUV
WHERE PUV.uid =? AND PUV.proj_id =? AND PUV.group_id =? AND PUV.visit_id=? AND PUV.schedule_date=?
ORDER BY PUV.schedule_date
";

//echo "$sUID, $sProjid, $sGroupid, $sVisitid, $sScheduledate / query: $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sssss', $sUID, $sProjid, $sGroupid, $sVisitid, $sScheduledate);

if($stmt->execute()){
  $result = $stmt->get_result();
  if($row = $result->fetch_assoc()) {
    if($sNotetype == 'schedule'){
         $txtNote =  $row['schedule_note'];
    }
    else if($sNotetype == 'visit'){
         $txtNote =  $row['visit_note'];
    }
  }
}
else{
  error_log($stmt->error);
}


$txtrow = "
<div class='div-vs-note fl-wrap-col pw400' data-notetype='$sNotetype'
data-uid='$sUID' data-visitid='$sVisitid' data-projid='$sProjid' data-groupid='$sGroupid' data-scheduledate='$sScheduledate'>
  <div class='fl-fix ph-150 fl-mid'>
    <textarea id='txtnote' style='width: 99%; max-width: 99%;' placeholder='Add $sNotetype note here.'>$txtNote</textarea>
  </div>
  <div class='fl-fix ph-20 fl-mid pbtn pbtn-ok btn-save-note'>
     บันทึกข้อมูล | SAVE DATA
  </div>
  <div class='fl-fix ph-20 fl-mid spinner' style='display:none;'>
     <i class='fa fa-spinner fa-spin fa-lg' ></i>
  </div>
</div>
";

echo $txtrow;

?>





<script>
$(document).ready(function(){
  setDlgResult($("#txtnote").val());
	$('.div-vs-note .btn-save-note').unbind("click");
	$('.div-vs-note').on("click",".btn-save-note",function(){

    let btnsave = $(this);
		let uid = $(".div-vs-note").attr("data-uid");
    let projid = $(".div-vs-note").attr("data-projid");
    let groupid = $(".div-vs-note").attr("data-groupid");
    let visitid = $(".div-vs-note").attr("data-visitid");
    let scheduledate = $(".div-vs-note").attr("data-scheduledate");
    let txtnote = $("#txtnote").val();

    var aData = {
        u_mode:"update_note",
        uid:$(".div-vs-note").attr("data-uid"),
        projid:$(".div-vs-note").attr("data-projid"),
        groupid:$(".div-vs-note").attr("data-groupid"),
        visitid:$(".div-vs-note").attr("data-visitid"),
        scheduledate:$(".div-vs-note").attr("data-scheduledate"),
        notetype:$(".div-vs-note").attr("data-notetype"),
        txtnote:$("#txtnote").val()
    };

       startLoad(btnsave,btnsave.next(".spinner"));
  		 callAjax("project_a_visit.php",aData,function(rtnObj,aData){
  			     endLoad(btnsave,btnsave.next(".spinner"));
  					 if(rtnObj.res == 1){
               setDlgResult($("#txtnote").val());
               closeDlg(btnsave);
             }
  					 else{
  						 $.notify("Fail to update.", "error");
  					 }
  		 });// call ajax

	});


});


</script>
