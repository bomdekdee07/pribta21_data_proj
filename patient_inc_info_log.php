<?
include_once("in_session.php");
include_once("in_php_function.php");

$sUid=getQS("uid");

$aP = array('event_code'=>'','update_by'=>'','s_name'=>'','uic'=>'','fname'=>'','sname'=>'','en_fname'=>'','en_sname'=>'','nickname'=>'','clinic_type'=>'','sex'=>'','gender'=>'','date_of_birth'=>'','nation'=>'','blood_type'=>'','citizen_id'=>'','passport_id'=>'','id_address'=>'','id_district'=>'','id_province'=>'','id_zone'=>'','id_postal_code'=>'','use_id_address'=>'','address'=>'','district'=>'','province'=>'','zone'=>'','postal_code'=>'','country_other'=>'','tel_no'=>'','email'=>'','line_id'=>'','em_name_1'=>'','em_relation_1'=>'','em_phone_1'=>'','em_name_2'=>'','em_relation_2'=>'','em_phone_2'=>'','religion'=>'','remark'=>'','prep_nhso'=>'');

$query ="SELECT event_code,uic,fname,sname,en_fname,en_sname,nickname,clinic_type,sex,gender,date_of_birth,nation,blood_type,citizen_id,passport_id,id_address,id_district,id_province,id_zone,id_postal_code,use_id_address,address,district,province,zone,postal_code,country_other,tel_no,email,line_id,em_name_1,em_relation_1,em_phone_1,em_name_2,em_relation_2,em_phone_2,update_datetime,religion,remark,prep_nhso,update_by,s_name FROM patient_info_log PIL
LEFT JOIN p_staff PS
ON PS.s_id=PIL.update_by
WHERE uid=? ORDER BY update_datetime";
include("in_db_conn.php");
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
$aLog=array();
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		//$aLog[$row["update_datetime"]]=$aP;
		foreach ($aP as $sCol => $value) {
			$aLog[$row["update_datetime"]][$sCol]=$row[$sCol];
		}
	}
}
$mysqli->close();

$sHeader=""; $sFixCol=""; $sLogData="";
foreach ($aP as $sCol => $value) {
	$sFixCol.="<div class='fl-fix h-40 col-border fl-mid row-color'>$sCol</div>";
}
foreach ($aLog as $update_datetime => $aData) {
	$sHeader.="<div class='fl-fix w-200 f-border'>$update_datetime</div>";
}

foreach ($aP as $sCol => $value) {
	$sLogData.="<div class='fl-wrap-row h-40 fix-row row-color row-hover'>";
	foreach ($aLog as $update_datetime => $aData) {
		
		$sLogData.="<div class='fl-fix cell-border popupbox'>".$aData[$sCol]."</div>";
	}
	$sLogData.="</div>";
}
unset($aLog);

?>

<div id='divPIIL' class='fl-wrap-col fix-header-wrap'>
	<div class='fl-wrap-row h-40' style='background-color: blue;color:white'>
		<!-- Fixed Header List -->
		<div class='fl-fix w-200 fl-mid'>Data</div>
		<!-- End of Fix Header Column -->

		<div class='fl-wrap-row row-header fl-scroll fix-header-head' style='background-color: blue'>
			<!-- Header List -->
			<? echo($sHeader); ?>
			<!-- End of Header List -->
		</div>
	</div>

	<div class='fl-wrap-row fs-small ' >

		<!-- Fix Data for each Header -->
		<div class='fl-wrap-col w-200 fix-header-col' >
			<? echo($sFixCol); ?>
		</div>
		<!-- End Fix Data for Header -->


		<div class='fl-wrap-col fix-header-body'>
			<!-- Data Row -->
			<? echo($sLogData); ?>
			<!-- End Data Row -->
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$("#divPIIL").flFixHeader();
	});
</script>