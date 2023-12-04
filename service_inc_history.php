<?
include_once("in_php_function.php");
include_once("in_setting_row.php");

$sUid =getQS("uid");
$sQ = getQS("q");
$sToday = date("Y-m-d");
$COUNSELOR_FORM_NAME = "PRIBTA_PROVIDER";
include("in_db_conn.php");

$aStaff = array();

$query = "SELECT collect_date,data_id,s_name FROM p_data_result PDR 
LEFT JOIN p_staff PS ON PS.s_id = PDR.data_result
WHERE data_id IN ('staff_md','staff_rn','staff_cl') 
AND data_result !=''
AND uid = ?
ORDER BY collect_date,data_id";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
  $stmt->bind_result($collect_date,$data_id,$s_name );
  while ($stmt->fetch()) {

  	$aStaff[$collect_date] = (isset($aStaff[$collect_date])?$aStaff[$collect_date]:"")."<div style='background-color:cyan'>".(($data_id=="staff_md")?"MD":(($data_id=="staff_rn")?"RN": (($data_id=="staff_cl")?"CL": "" ) )  ) .": $s_name</div>";
  }
}


$query = "SELECT collect_date,PDR.data_id,data_name_th,data_name_en,data_result
	FROM p_data_result PDR

	LEFT JOIN p_data_list PDL
	ON PDL.data_id = PDR.data_id

	LEFT JOIN p_form_list_data PFI
	ON PFI.data_id = PDL.data_id

	WHERE PDR.data_id IN ('serv_coun_hiv','serv_coun_sti_test','serv_coun_prep','serv_coun_pep','serv_coun_treatment','serv_coun_hormone','serv_coun_internal','serv_coun_blood_test','serv_coun_buymed','serv_coun_consult','serv_coun_research','serv_coun_online','serv_coun_certificate','serv_coun_telemed','serv_coun_vaccine','serv_coun_art','serv_coun_oth','serv_coun_oth_txt')

	AND PDR.uid = ?  AND PFI.form_id = '$COUNSELOR_FORM_NAME'
	ORDER BY collect_date,data_seq*1";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);

	$sHistory = "";
	$sCurDate = "";
	if($stmt->execute()){
	  $stmt->bind_result($collect_date,$data_id,$data_name_th,$data_name_en,$data_result );
	  while ($stmt->fetch()) {
	  	if($sCurDate!=$collect_date){
	  		
	  		if($sCurDate==""){
	  			//First Record

	  		}else{
	  			//New Row	 - END PREVIOUS DATE
	  			$sHistory .= "</div></div>";
	  		}
	  		$sHistory .= "<div class='fl-wrap-col' style='min-width:300px;border-left:1px solid black'>
	  			<div class='fl-fix h-xs row-color-2 fl-mid'>
	  				$collect_date
	  			</div>
	  			".(isset($aStaff[$collect_date])?$aStaff[$collect_date]:"")."
	  			<div class='fl-fill fl-auto row-hover' style='text-align:left;background-color:#FFFFFE'>


	  		";
	  	}

	  	$sCurDate=$collect_date;
	  	if($data_result=="1"){
		  	$sHistory .= "
		  		<input type='radio' data-dataid='$data_id' checked readonly='true' />$data_name_th";
		  	if($data_id=="serv_coun_oth"){

		  	}else{
		  		$sHistory .= "<br/>";
		  	}
	  	}else if($data_id == "serv_coun_oth_txt"){
		  		$sHistory .= ": $data_result <br/>";
		}


	  }
	  if($sHistory!="") $sHistory .= "</div></div>";
	}
?>
<div id='divServiceHistory' class='fl-wrap-col'>
	<div class='fl-wrap-row fl-auto' style='border:1px solid black'>
		<? echo($sHistory); ?>
	</div>
	<div class='fl-fix h-ss' style=''>
		<input id='closeDialog' type='button' class='fill-box' value='Close' />
	</div>
	<div class='fl-fix h-xxs' style=''>
	</div>
</div>

<script>
	$(document).ready(function(){
		$("#divServiceHistory #closeDialog").unbind("click");
		$("#divServiceHistory #closeDialog").on("click",function(){
			closeDlg(this);
		});

	});

</script>