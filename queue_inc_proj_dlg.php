<?
	include_once("in_session.php");
	include_once("in_php_function.php");
	$sClinicId=getSS("clinic_id");
	$sUid=getQS("uid");
	$sColD=getQS("coldate");
	$sColT=getQS("coltime");
	$sToday=date("Y-m-d");
	
	include("in_db_conn.php");

	if($sUid!="" && $sColD==""){

	}else if($sUid=="" && $sColD==""){

	}


	$sHtml="";
	$query = "SELECT PP.proj_id,proj_name,IQPL.proj_id AS selproj FROM p_project PP
	LEFT JOIN i_queue_project_list IQPL
	ON IQPL.proj_id=PP.proj_id
	AND clinic_id=? AND uid=? AND collect_date=? AND collect_time=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sClinicId,$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($proj_id,$proj_name,$chk_proj_id);
		while ($stmt->fetch()) {
			$isCheck = (($chk_proj_id=="")?0:1);
			$sHtml.="<div class='fl-wrap-row row-color al-left h-30 lh-30'>
			<div class='fl-fix w-20'></div>
			<div class='fl-fill'><label><input type='checkbox' value='$proj_id' class='btnqproj bigcheckbox' ".(($isCheck)?"checked='true' ":"")." data-odata='$isCheck'  /><i class='btnqproj-loader fa fa-spinner fa-spin' style='display:none'></i>$proj_name</label>
			</div>
			</div>";
		}	
	}
	$mysqli->close();

	$sHtml = "<div id='divQIPDLG' class='fl-wrap-col fl-auto' ".getDataAttr($sUid,$sColD,$sColT)." >
		$sHtml
	</div>";
	
	echo($sHtml);
?>


<script>
	$(function(){
		$("#divQIPDLG .btnqproj").off("click");
		$("#divQIPDLG").on("click",".btnqproj",function(){
			sURL="queue_proj_a.php";
			objDiv=$(this).closest("#divQIPDLG");
			sUid=$(objDiv).attr("data-uid");
			sColD=$(objDiv).attr("data-coldate");
			sColT=$(objDiv).attr("data-coltime");
			sProjId=$(this).val();

			sMode=($(this).is(":checked")?"q_add_proj":"q_del_proj");

			objChk=$(this);
			objLoad=$(this).next(".btnqproj-loader");

			aData={"u_mode":sMode,"uid":sUid,"coldate":sColD,"coltime":sColT,"projid":sProjId};
			startLoad($(objChk),$(objLoad));
	        callAjax(sURL,aData,function(jRes,rData){
				if(jRes.res=="1"){


				}else{
					
				}
				endLoad($(objChk),$(objLoad));
	        });
		});

	});
</script>