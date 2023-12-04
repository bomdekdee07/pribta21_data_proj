<?
	include_once("in_php_function.php");
	$sClinicId = getQS("clinicid");

	$aDate = array("Sunday / อาทิตย์","Monday / จันทร์","Tuesday / อังคาร","Wednesday / พุธ","Thursday / พฤหัส","Friday / ศุกร์","Saturday / เสาร์");

	include("in_db_conn.php");
    $query = "SELECT clinic_holiday FROM p_clinic WHERE clinic_id=?";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $sClinicId);

    $sHoliday = "";
    if($stmt -> execute()){
        $stmt -> bind_result($clinic_holiday);

        while($stmt -> fetch()){
             $sHoliday=$clinic_holiday;
        }
    }
    $mysqli->close();

    $aHo = explode(",",$sHoliday);
    $aFin = array();
    foreach ($aHo as $key => $value) {
    	$aFin[$value] = $value;
    }

	$sHtml = "";
	foreach ($aDate as $iDate => $sDate) {
		$sHtml.="
		<div class='fl-fix h-ss'>
			<label><input class='bigcheckbox chkclinicholiday row-color' type='checkbox' value='".$iDate."' ".(isset($aFin[$iDate])?"checked":"")." data-odata='$iDate' />".$sDate."</label>
		</div>";
	}


	$sHtml = "
<div id='divClinicHoliday' class='fl-wrap-col'>

	<div class='fl-fix h-s fl-middle'>
		".$sClinicId."'s Holiday
	</div>
	<div class='fl-fill fl-auto' style='text-align: left;text-indent: 30px'>
		$sHtml
	</div>
	<div class='fl-fix h-ss' >
		<input type='button' id='btnSaveHoliday' class='' value='Save' data-clinicid='$sClinicId' />
		<i class='fa fa-spinner fa-spin fa-lg' id='btnSaveHoliday-loader' style='display:none' ></i>
	</div>
	<div class='fl-fix h-xs'>

	</div>
</div>	";

?>

	<? echo($sHtml); ?>


<script>
	$(document).ready(function(){
		$("#divClinicHoliday #btnSaveHoliday").unbind("click");
		$("#divClinicHoliday #btnSaveHoliday").on("click",function(){
			let sVal = "";
			sClinicId = $(this).attr("data-clinicid");

			//clinic_id=IHRI&clinic_addess=tester&colpk=clinic_id&col=clinic_address&u_mode=clinic_update
			$("#divClinicHoliday .chkclinicholiday").each(function(ix,objx){
				if($(objx).is(":checked")){
					sVal += ((sVal=="")?"":",") + $(objx).val();
				}

			});

			aData = {u_mode:"clinic_update",colpk:"clinic_id",clinic_id:"IHRI",col:"clinic_holiday",clinic_holiday:sVal}

			startLoad($("#btnSaveHoliday"),$("#btnSaveHoliday-loader"));

			callAjax("clinic_a.php",aData,function(rtnObj,aData){

				if(rtnObj.res!="1"){
					$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
				}else if(rtnObj.res=="1"){
					$.notify("Data Saved","success");
				}
				//
				endLoad($("#btnSaveHoliday"),$("#btnSaveHoliday-loader"));
			});
		});
	});

</script>