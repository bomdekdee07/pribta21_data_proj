<?
	include_once("in_session.php");
	include_once("in_php_function.php");
	$sUid=getQS("uid");
	//$sColT=getQS("coltime");
	$sToday=date("Y-m-d");
	$sClinicId=getSS("clinic_id");

	$sHtml="";
	//get Last Visit
	include("in_db_conn.php");

	$sProjList="";

	$query="SELECT PPUL.proj_id,proj_name,enroll_date FROM p_project_uid_list PPUL 
	LEFT JOIN p_project PP
	ON PP.proj_id=PPUL.proj_id
	WHERE PPUL.uid=? AND uid_status=1";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
	if($stmt->execute()){
		$stmt->bind_result($proj_id,$proj_name,$enroll_date);
		while ($stmt->fetch()) {
			$sProjList.="<div class='fl-wrap row-hover f-border h-20' style='margin-right:5px;padding:2px'>$proj_name</div>";
		}	
	}



	$mysqli->close();


		$sHtml.="
			<div id='divQIP' class='fl-wrap-row fs-smaller h-20' data-uid='$sUid' title='Enrolled Project\rโครงการที่เข้าร่วม'>
				<div class='fl-wrap-row fs-xsmall'>
					$sProjList
				</div>
			</div>
		";


	echo($sHtml);
?>
<script>
	$(function(){
		/*
		$("#divQIP").off("click");
		$("#divQIP").on("click",function(){
			sUid=$(this).attr("data-uid");
			sColD=$(this).attr("data-coldate");
			sColT=$(this).attr("data-coltime");
			sUrl="queue_inc_proj_dlg.php?"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,"Title","300","820","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
				}
			},false,function(){
				//Load Done Function
			});
		});
		*/
	});

</script>