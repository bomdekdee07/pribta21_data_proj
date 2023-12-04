<?
include_once("in_session.php");
include_once("in_php_function.php");
$sUid=getQS("uid");
$sColD=getQS("coldate");
$sColT=urldecode(getQS("coltime"));


$aPInfo=array("food_txt"=>"","drug_txt"=>"");
include("in_db_conn.php");
$query ="SELECT uid,collect_date,collect_time,data_id,data_result FROM p_data_result WHERE uid=? AND data_id IN ('food_intolerance_txt','drug_allergy_txt') AND data_result!='' ORDER BY collect_date DESC LIMIT 1";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
	$stmt->bind_result($uid,$collect_date,$collect_time,$data_id,$data_result);
	while($stmt->fetch()){
		if($data_id=="food_intolerance_txt"){
			$aPInfo["food_txt"]=$data_result;
		}if($data_id=="drug_allergy_txt"){
			$aPInfo["drug_txt"]=$data_result;
		}
	}

}
$mysqli->close();

$sFood = ($aPInfo["food_txt"]);
$sDrug = ($aPInfo["drug_txt"]);
$sPK = getHiddenPk($sUid,$sColD,$sColT);
?>

<div id='divPIEA' class='fl-wrap-col'>
	<? echo($sPK); ?>
	<div class='fl-wrap-row row-color'>
		<div class='fl-fix w-100 fl-mid'>แพ้ยา Drug Allergy</div>
		<div class='fl-fill'><textarea id='txtDrugTxt' data-odata='<? echo(urlencode($sDrug)); ?>' class='h-fill w-fill' ><? echo($sDrug); ?></textarea></div>
	</div>
	<div class='fl-wrap-row row-color'>
		<div class='fl-fix w-100 fl-mid'>แพ้อาหาร Food Allergy</div>
		<div class='fl-fill'><textarea id='txtFoodTxt'  data-odata='<? echo(urlencode($sFood)); ?>' class='h-fill w-fill' ><? echo($sFood); ?></textarea></div>
	</div>
	<div class='fl-wrap-row h-30 fl-mid'>
		<div id='btnSaveAllergy' class='fabtn fl-fix w-100 h-30 f-border'>Save</div>
		<i id='btnSaveAllergy-loader' class='fa fa-spinner fa-spin fa-2x' style='display:none'></i>
	</div>
</div>

<script>
	$(function(){

		$("#divPIEA #btnSaveAllergy").off("click");
		$("#divPIEA #btnSaveAllergy").on("click",function(){
			sUid=getKeyVal($("#divPIEA"),"uid");
			sColD=getKeyVal($("#divPIEA"),"collect_date");
			sColT=getKeyVal($("#divPIEA"),"collect_time");
			
			aData=getAllData($("#divPIEA"));
			aDS=[];
			aData.u_mode="save_data";
			
			sVal = ($("#divPIEA #txtDrugTxt").val());
			oVal = (pribtaDecode($("#divPIEA #txtDrugTxt").attr('data-odata')));
			
			if(sVal+""!=oVal+""){
				aDS.push("drug_allergy_txt"+","+encodeURIComponent((sVal)));
			}
			sVal = ($("#divPIEA #txtFoodTxt").val());
			oVal = pribtaDecode($("#divPIEA #txtFoodTxt").attr('data-odata'));
			if(sVal+""!=oVal+""){
				aDS.push("food_intolerance_txt"+","+encodeURIComponent((sVal)));
			}

			if((aDS.length)==0){
				$.notify("No data changed");
				return;
			}else{
				aData.items=aDS;
			}
			startLoad($("#divPIEA #btnSaveAllergy"),$("#divPIEA #btnSaveAllergy-loader"));
			sURL="data_result_a.php";
	        callAjax(sURL,aData,function(jRes,rData){
				if(jRes.res=="1"){
					$.notify("Data Saved","success");
					closeDlg($("#divPIEA #btnSaveAllergy"),"REFRESH");
				}else{
					$.notify("Data is not saved.","error");
					endLoad($("#divPIEA #btnSaveAllergy"),$("#divPIEA #btnSaveAllergy-loader"));
				}

	        });
		});

	});
</script>

