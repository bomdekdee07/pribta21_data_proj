<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");

include("in_db_conn.php");

$sSupCode=getQS("supcode");
$sSupLot=getQS("suplot");
$sSid=getSS("s_id");
$aInfo=array();
$query="SELECT ISM.supply_name,stock_cost FROM i_stock_list ISL 
LEFT JOIN i_stock_master ISM 
ON ISM.supply_code= ISL.supply_code
WHERE ISL.supply_code=? AND ISL.stock_lot=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sSupCode,$sSupLot);
if($stmt->execute()){
	$stmt->bind_result($supply_name,$stock_cost);
	while($stmt->fetch()){
		$aInfo["name"]=$supply_name;
		$aInfo["cost"]=$stock_cost;
	}
}else{
	//For Insert Duplicate จะ Error ตรงนี้ ดู $stmt->error สำหรับ ข้อความ error ได้

}


$mysqli->close();
?>

<div id='divSA' class='fl-wrap-col' data-supcode='<? echo($sSupCode); ?>'  data-suplot='<? echo($sSupLot); ?>'>
	<div class='fl-fix h-30 bg-head-1'>Cost Adjustment</div>
	<div class='fl-fill row-color h-30'>
		<? echo($aInfo["name"]); ?>
	</div>
	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-50'>Code :</div>
		<div class='fl-fill'><? echo($sSupCode); ?></div>
	</div>
	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-50'>Lot :</div>
		<div class='fl-fill'><? echo($sSupLot); ?></div>
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fill'>From</div>
		<div class='fl-fix w-20'> </div>
		<div class='fl-fill'>To</div>
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fill fl-mid fw-b'><input id='txtCurCost' class='w-100' style='text-align: center'  readonly="true" value='<? echo($aInfo["cost"]); ?>' /></div>
		<div class='fl-fix w-20'> --- </div>
		<div class='fl-fill fl-mid'><input id='txtNewCost' style='text-align: center' class='w-100' type='number' value='<? echo($aInfo["cost"]); ?>' /></div>
	</div>
	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-50'>Reason : </div>
		<div class='fl-fill'><SELECT id='ddlEvent'><option value='ADJUST_COST'>ปรับราคาทุน/Cost Adjust</option></SELECT></div>
	</div>
	<div class='fl-wrap-row row-color h-80'>
		<div class='fl-fix wper-50'>Note : </div>
		<div class='fl-fill'><textarea id='txtNote'></textarea></div>
	</div>
	<div class='fl-fill fl-mid'><button id='btnSubmit' value=''>บันทึก/Save</button>
		<i id='btnSubmit-loader' class='fa fa-spinner fa-spin fa-lg' style='display:none'></i></div>
</div>

<script>
	$(document).ready(function(){
		setDlgResult("NA",$("#divSA #btnSubmit"));

		$("#divSA #btnSubmit").off("click");
		$("#divSA #btnSubmit").on("click",function(){
			objRow=$(this).closest("#divSA");
			sSupCode=$(objRow).attr('data-supcode');
			sSupLot=$(objRow).attr('data-suplot');
			sCurAmt=$(objRow).find("#txtCurCost").val();
			sNewAmt=$(objRow).find("#txtNewCost").val();
			sEvent=$(objRow).find("#ddlEvent").val();
			sNote=encodeURIComponent($(objRow).find("#txtNote").val());
			objThis=$(this);

			if(sCurAmt==sNewAmt){
				$.notify("No data changed.");
				return;
			}
			startLoad($("#btnSubmit"),$("#btnSubmit-loader"));
			aData={u_mode:"supply_cost_adjust",supcode:sSupCode,suplot:sSupLot,cost:sNewAmt,event:sEvent,note:sNote};
			callAjax("supply_a.php",aData,function(jRes,rData){
				if(jRes.res=="1"){
					$.notify("Stock Adjust Success","success");
					closeDlg(objThis,sNewAmt);
				}else{
					setDlgResult("NA",objThis);
				}
				endLoad($("#btnSubmit"),$("#btnSubmit-loader"));
        	});
		});
	});
</script>