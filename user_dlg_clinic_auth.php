<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");

$sSid = getQS("sid");

$aClinic = array(); $aSection = array(); $aStaff = array();
$sOptClinic="";
$sRowHeader = "";
$sFixData="";
$sRowData="";




$query ="SELECT clinic_id,clinic_name FROM p_clinic PC ORDER BY clinic_id";

$stmt = $mysqli->prepare($query);
$sHtml = "";
if($stmt->execute()){
  $stmt->bind_result($clinic_id,$clinic_name );
  while ($stmt->fetch()) {
  	$aClinic[$clinic_id] = $clinic_name;
  	$sOptClinic.="<option value='$clinic_id'>$clinic_name</option>";
  }
}
$query ="SELECT section_id,section_name FROM p_staff_section WHERE section_enable=1 ORDER BY section_id";

$stmt = $mysqli->prepare($query);
$sHtml = "";
if($stmt->execute()){
  $stmt->bind_result($section_id,$section_name );
  while ($stmt->fetch()) {
  	$aSection[$section_id] = $section_name;
  	$sFixData.="<div class='fl-wrap-col h-30 col-border row-color al-left fs-smaller row-hover sec-list' data-secid='$section_id'><div class='fl-fill lh-15'>$section_id</div><div class='fl-fill lh-15  fw-b'>$section_name</div></div>";
  }
}

$query ="SELECT s_id,clinic_id,section_id,sc_status FROM i_staff_clinic WHERE s_id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sSid);
$sHtml = "";
if($stmt->execute()){
  $stmt->bind_result($s_id,$clinic_id,$section_id,$sc_status );
  while ($stmt->fetch()) {
  	$aStaff[$clinic_id][$section_id] = $sc_status;
  }
}

$mysqli->close();
$sHtml = ""; $iRow = 0;
foreach ($aStaff as $clinic_id => $aSec) {
	$sRowHeader.="<div class='fl-wrap-row w-200 f-border fl-mid lh-20 clinic-list fs-smaller' data-clinicid='$clinic_id'>
	<div class='fl-fix w-20 h-40 fl-mid fabtn btndelete' title='Remove Clinic Authorization' style='color:red'><i class='fa fa-trash-alt'></i></div>
	<div class='fl-fill'>
	".$aClinic[$clinic_id]."
	</div>
	<div class='fl-wrap-col w-20'>
		<div class='fl-fill lh-40 w-20 h-40 btncopy fabtn fl-mid' title='copy'><i class='fa fa-copy'></i></div>
		<div class='fl-fill lh-20 w-20 h-20 btnpaste fabtn fl-mid' title='paste' style='display:none' data-sourceid=''><i class='fa fa-paste'></i></div>
		<div class='fl-fill lh-20 w-20 h-20 btncancel fabtn fl-mid'  title='cancel copy/paste' style='color:orange;display:none'><i class='fa fa-power-off'></i></div>
		<div class='fl-fill lh-40 w-20 h-40 fl-mid btn-loader' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
	</div>
	</div>";
}
/*$sRowHeader.="<div class='fl-wrap-row w-150'>
<div class='fl-fill fl-mid'><SELECT class='w-fill'>$sOptClinic</SELECT></div>
<div class='fl-fix w-30 fabtn  fl-mid'><i class='fa fa-plus'></i></div>
</div>";
*/
foreach ($aSection as $section_id => $section_name) {
	$sRowData.="<div class='fl-wrap-row h-30 fix-row row-hover row-color'>";
	foreach ($aStaff as $clinic_id => $aSec) {
		$sChk = "";
		if(isset($aSec[$section_id]) && $aSec[$section_id] == 1) $sChk="checked";
		$sRowData.="<div class='fl-fix cell-border fl-mid'><input type='checkbox' class='bigcheckbox chksecauth' data-clinicid='$clinic_id' data-secid='$section_id' $sChk /></div>";
	}
	$sRowData.="</div>";
}


$sHtml ="<div id='divUDCA' class='fl-wrap-col fix-header-wrap fl-auto' data-sid='$sSid'>
	<div class='fl-wrap-row h-40' style='background-color: blue;color:white'>
		<div class='fl-wrap-row w-200'>
			<div class='fl-fill fl-mid'><SELECT id='ddlClinic' class='w-fill'>$sOptClinic</SELECT></div>
			<div id='bnAddClinic' class='fl-fix w-30 fabtn  fl-mid' style='background-color:green'><i class='fa fa-plus'></i></div>
		</div>
		<div class='fl-wrap-row row-header fl-scroll fix-header-head fs-smaller fw-b' style='background-color: blue;color:white'>
			$sRowHeader
		</div>
	</div>

	<div class='fl-wrap-row fs-small ' >
		<div class='fl-wrap-col w-200 fix-header-col' >
			$sFixData
		</div>
		<div class='fl-wrap-col fix-header-body'>
			$sRowData
		</div>
	</div>
</div>";

echo($sHtml);
?>



<script>
	$(document).ready(function(){
		$("#divUDCA").flFixHeader();

		$("#divUDCA .btncopy").off("click");
		$("#divUDCA").on("click",".btncopy",function(){
			objThis=$(this);
			objRow=$(this).closest(".clinic-list");
			sClinic=$(objRow).attr("data-clinicid");
			$("#divUDCA .btncopy").hide();
			$("#divUDCA .btnpaste").show();
			$("#divUDCA .btncancel").show();
			$(objRow).find(".btnpaste").hide();
			$("#divUDCA .btnpaste").attr("data-sourceid",sClinic);
		});
		$("#divUDCA .btnpaste").off("click");
		$("#divUDCA").on("click",".btnpaste",function(){
			objThis=$(this);
			objRow=$(this).closest(".clinic-list");
			sClinic=$(objRow).attr("data-clinicid");
			sSource=$(this).attr("data-sourceid");
			objLoad=$(objRow).find(".btn-loader");
			sSid=$("#divUDCA").attr('data-sid');


			startLoad($(objThis),$(objLoad));
			var aData = {u_mode:"copy_clinic_auth",pid:sSid,clinicid:sClinic,sourceid:sSource};
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Error no data copy. Please try again.");
				}else if(rtnObj.res=="1"){
					$("#divUDCA .chksecauth[data-clinicid='"+sSource+"']").each(function(ix,objx){
						sSecId=$(objx).attr("data-secid");
						sStat = $(objx).prop("checked");
						$("#divUDCA .chksecauth[data-clinicid='"+sClinic+"'][data-secid='"+sSecId+"']").prop("checked",sStat);
					});
				}
				endLoad($(objThis),$(objLoad));
				$("#divUDCA .btncancel").show();
			});

		});

		$("#divUDCA .btncancel").off("click");
		$("#divUDCA").on("click",".btncancel",function(){
			$("#divUDCA .btncopy").show();
			$("#divUDCA .btnpaste").hide();
			$("#divUDCA .btncancel").hide();
			$("#divUDCA .btnpaste").attr("data-sourceid","");
		});

		$("#divUDCA .btndelete").off("click");
		$("#divUDCA").on("click",".btndelete",function(){
			objThis=$(this);
			objRow=$(this).closest(".clinic-list");
			sClinic=$(objRow).attr("data-clinicid");
			objLoad=$(objRow).find(".btn-loader");
			sSid=$("#divUDCA").attr('data-sid');

			startLoad($(objThis),$(objLoad));
			$("#divUDCA .chksecauth[data-clinicid='"+sClinic+"']").hide();
			var aData = {u_mode:"delete_clinic_auth",pid:sSid,clinicid:sClinic};
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Error no data delete. Please try again.");
				}else if(rtnObj.res=="1"){
					$("#divUDCA .clinic-list[data-clinicid='"+sClinic+"']").remove();
					$("#divUDCA .chksecauth[data-clinicid='"+sClinic+"']").parent().remove();
				}
			});
		});


		$("#divUDCA #bnAddClinic").off("click");
		$("#divUDCA").on("click","#bnAddClinic",function(){
			bIsAdded=false;
			sClinic=$("#divUDCA #ddlClinic").val();
			sSid=$("#divUDCA").attr('data-sid');

			$("#divUDCA .clinic-list").each(function(i,obj){
				if($(obj).attr("data-clinicid") == sClinic) bIsAdded=true;
			});
			if(bIsAdded){
				$.notify("Already Added");
				return;
			}

			var aData = {u_mode:"update_clinic_auth",secid:"D01",status:"0",pid:sSid,clinicid:sClinic};
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Error no data added. Please try again.");
				}else if(rtnObj.res=="1"){
					$("#divUDCA").hide();
					sUrl="user_dlg_clinic_auth.php?sid="+sSid;
					$("#divUDCA").parent().load(sUrl,function(){

					});
				}
			});
		});

		$("#divUDCA .chksecauth").off("change");
		$("#divUDCA").on("change",".chksecauth",function(){
			sChk = ($(this).is(":checked")?"1":"0");
			objDiv=$(this).closest("#divUDCA");
			sSid=$(objDiv).attr('data-sid');
			sClinic=$(this).attr('data-clinicid');
			sSecId=$(this).attr("data-secid");
			objThis=$(this);
			var aData = {u_mode:"update_clinic_auth",secid:sSecId,status:sChk,pid:sSid,clinicid:sClinic};
			$(objThis).hide();
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Data is not save. Please try again\r\n"+rtnObj.msg+"\r\n"+aData.clinicid+":"+aData.secid,"error");
					if(sChk=="0"){
						$(objThis).attr('checked',true);
					}else{
						$(objThis).removeAttr('checked');
					}
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved","success");
				}
				//endLoad(objChk,objLoad);
				$(objThis).show();
			});
		});

	});
</script>