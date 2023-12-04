<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sUid = getQS("uid");


include("in_db_conn.php");
$aRel = array(); $sHtml = ""; $aPInfo=array();
$query = " SELECT PIR.uid,rel_uid,rel_type,rel_remark,fname,sname,en_fname,en_sname,nickname,sex,'1' AS is_main FROM patient_info_relate PIR
LEFT JOIN patient_info PI ON PI.uid=PIR.rel_uid
WHERE PIR.uid=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$aPInfo[$row["rel_uid"]] = $row;
	}
}
$query = " SELECT PIR.rel_uid AS uid ,PIR.uid AS rel_uid,rel_type,rel_remark,fname,sname,en_fname,en_sname,nickname,sex,'0' AS is_main FROM patient_info_relate PIR
LEFT JOIN patient_info PI ON PI.uid=PIR.uid
WHERE PIR.rel_uid=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$aPInfo[$row["rel_uid"]] = $row;
	}
}
$mysqli->close();

foreach ($aPInfo as $uid => $aRow) {
	$sHtml.=getRelationship($aRow["uid"],$aRow["rel_uid"],$aRow["fname"],$aRow["sname"],$aRow["en_fname"],$aRow["en_sname"],$aRow["rel_type"],$aRow["is_main"]);
}


?>

<div id='divPRM' class='fl-wrap-col fs-smaller' data-uid='<? echo($sUid); ?>'>
	<div class='fl-wrap-row row-color-2 h-30 lh-30'>
		<div class='fl-fix w-50 fl-mid fabtn'><i class='fa fa-search' ></i></div>
		<div class='fl-fix w-100'><input id='txtRelUid' placeholder="UID" class='fill-box h-25' ><i id='txtRelUid-loader' class='fa fa-spinner fa-spin' style='display:none'></i></div>

		<div class='fl-fill'><input id='txtRelName' class='h-25 fill-box' readonly="" /></div>
		<div class='fl-fix w-100'><SELECT id='ddlRelation' class='fill-box h-25'><? include("patient_relation_opt.php"); ?></SELECT></div>
		<div class='fl-fix fl-mid  w-50' style='color:green'><i id='btnAddRel' class='fabtn fa fa-plus fa-lg'></i><i id='btnAddRel-loader' class='fa fa-spinner fa-spin' style='display:none'></i></div>
	</div>
	<div class='fl-wrap-row row-header row-color-2 h-30 lh-30'>
		<div class='fl-fix w-100'>UID</div>
		<div class='fl-fill'>Name</div>
		<div class='fl-fix w-100'>Relationship</div>
	</div>
	<div id='divPRMList' class='fl-wrap-col row-color-2 fl-auto'>
		<? echo($sHtml); ?>
	</div>
</div>

<script>
	$(function(){
		$("#divPRM #divPRMList .btnreldelete").unbind("click");
		$("#divPRM #divPRMList").on("click",".btnreldelete",function(){
			sUid = $(this).closest(".data-row").attr("data-uid");
			sRelUid = $(this).closest(".data-row").attr("data-reluid");
			sIsMain = $(this).closest(".data-row").attr("data-ismain");


			if(!confirm("Confirm delete relationship?")){
				return;
			}
			objR = $("#divPRM #divPRMList .data-row[data-uid='"+sUid+"'][data-reluid='"+sRelUid+"']");

			startLoad($(objR).find(".btnreldelete"),$(objR).find(".btnreldelete-loader"));

			if(sIsMain!=1){
				sUid = $(this).closest(".data-row").attr("data-reluid");
				sRelUid = $(this).closest(".data-row").attr("data-uid");
			}

			sURL="patient_a.php";
			aData={u_mode:"del_relationship",uid:sUid,reluid:sRelUid};
			callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$(objR).remove();
					//objR = $("#divPRM #divPRMList .data-row[data-uid='"+sUid+"'][data-reluid='"+sRelUid+"']").remove();
				}else{
					$.notify(jRes.msg);
					//objR = $("#divPRM #divPRMList .data-row[data-uid='"+sUid+"'][data-reluid='"+sRelUid+"']");
					endLoad($(objR).find(".btnreldelete"),$(objR).find(".btnreldelete-loader"));
				}


	        });
		});


		$("#divPRM #txtRelUid").unbind("change");
		$("#divPRM #txtRelUid").on("change",function(){
			sURL="patient_a.php";
			srcUid = $("#divPRM").attr("data-uid");
			sUid = $("#divPRM #txtRelUid").val().trim();
			if(sUid==""){
				return;
			}else if(sUid==srcUid){
				$.notify("Please enter other UID. Self Relationship doesn't exists.");
				return;
			}

			//Name is used for display only. The real data is in patient_info
			startLoad($("#divPRM #txtRelUid"),$("#divPRM #txtRelUid-loader"));
			aData={u_mode:"get_pinfo",uid:sUid};
			callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$("#divPRM #txtRelName").val(jRes.fname+" "+jRes.sname);
				}else{
					$.notify(sUid+" not found");
				}
				endLoad($("#divPRM #txtRelUid"),$("#divPRM #txtRelUid-loader"));
	        });
		});

		$("#divPRM #btnAddRel").unbind("click");
		$("#divPRM #btnAddRel").on("click",function(){
			sURL="patient_a.php";
			srcUid = $("#divPRM").attr("data-uid");
			sUid = $("#divPRM #txtRelUid").val().trim();
			if(sUid==""){
				return;
			}else if(sUid==srcUid){
				$.notify("Please enter other UID. Self Relationship doesn't exists.");
				return;
			}

			//Name is used for display only. The real data is in patient_info
			sName=$("#divPRM #txtRelName").val().trim();
			if(sName=="") return;
			sRelType=$("#divPRM #ddlRelation").val();
			startLoad($("#divPRM #btnAddRel"),$("#divPRM #btnAddRel-loader"));
			aData={u_mode:"add_relationship",uid:srcUid,reluid:sUid,name:sName,reltype:sRelType};
			callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$("#divPRM #divPRMList").append(jRes.msg);
					$("#divPRM #txtRelUid").val("");
					$("#divPRM #txtRelName").val("");
				}else{
					$.notify(jRes.msg);
				}
				endLoad($("#divPRM #btnAddRel"),$("#divPRM #btnAddRel-loader"));
	        });
		});
	});

</script>