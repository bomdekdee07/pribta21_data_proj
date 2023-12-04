<?

include('in_db_conn.php');
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate = getQS("coldate");
$sColTime = urldecode(getQS("coltime"));

//Row
function getSupSumRow($sCode,$sName,$isPaid,$sAmt){
	$sHtml = "
	<div class='fl-wrap-row h-xs row-color'>
		<div class='fl-fix w-m'>
		".(($isPaid=="1")?"<i>\$</i>":"")."	$sCode
		</div>
		<div class='fl-fill'>
			$sName
		</div>
		<div class='fl-fix w-m' style='text-align:right'>
			$sAmt
		</div>
		<div class='fl-fix w-xxs'>

		</div>
	</div>";

	return $sHtml;
}

//Today Date
$year=date("Y");
$dm=date("d/m");
$year_thai=$year+543;
$full_date_thai_print=$dm.'/'.$year_thai;

if($sColDate=="" || strpos("-",$sColDate) !== false  ) $sColDate==date("Y-m-d");
$aD = explode("-",$sColDate); 

$full_date_thai=(isset($aD[2])?$aD[2]:"")."/".(isset($aD[1])?$aD[1]:"")."/".(($aD[0]*1+543));


$iFinalTotal = 0; $iDF = 0; $iLab = 0; $iDrug = 0; 

	
//LAB Cost
$query = "SELECT lab_price
FROM p_lab_order_lab_test PLOLT
LEFT JOIN p_lab_test_sale_price PLTSP
ON PLTSP.lab_id = PLOLT.lab_id
AND PLTSP.sale_opt_id = PLOLT.sale_opt_id
WHERE uid=? AND collect_date = ? AND collect_time = ? ";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

if($stmt->execute()){
  $stmt->bind_result($lab_price );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iFinalTotal += ($lab_price*1);
  	$iLab += ($lab_price*1); 

  }
}
//ค่าหมอ
$sHtml="";

$query =" SELECT p2_doctor_fee from k_physician
WHERE uid = ? AND visit_date = ? AND visit_time = ? AND p2_doctor_fee !='' order by time_record DESC LIMIT 1";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);

if($stmt->execute()){
  $stmt->bind_result($p2_doctor_fee );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iFinalTotal += ($p2_doctor_fee*1);
  	$sHtml.=getSupSumRow("DF","Doctor Fee",false,$p2_doctor_fee);
  }
}


//Drug Cost
$query = "SELECT JSO.supply_code,supply_name,supply_unit,total_amt,sale_price,JSG.supply_group_type,JSO.order_note,JSG.supply_group_code,supply_group_name FROM j_stock_order JSO
LEFT JOIN j_stock_master JSM
ON JSM.supply_code = JSO.supply_code

LEFT JOIN j_stock_group JSG
ON JSG.supply_group_code = JSM.supply_group_code

LEFT JOIN j_stock_sale_opt JSSO
ON JSSO.supply_code = JSO.supply_code
AND JSSO.sale_opt_id = JSO.sale_opt_id

WHERE JSO.uid = ? AND JSO.collect_date = ? AND JSO.collect_time = ? AND JSO.order_status != '00'
ORDER BY JSG.supply_group_type,JSG.supply_group_code,JSM.supply_code";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sUid,$sColDate,$sColTime);


$aService = array();

if($stmt->execute()){
  $stmt->bind_result($supply_code,$supply_name,$supply_unit,$total_amt,$sale_price,$supply_group_type,$order_note,$supply_group_code,$supply_group_name );

  $sTableBody = "";
  while ($stmt->fetch()) {
  	$iFinalTotal += $total_amt * $sale_price;
  	if($supply_group_type==1) $iDrug += $total_amt * $sale_price;

  	$iCount = count($aService);
  	$aService[$iCount]["code"] = $supply_code;
  	$aService[$iCount]["type"] = $supply_group_type;
  	$aService[$iCount]["group"] = $supply_group_code;
  	$aService[$iCount]["groupname"] = $supply_group_name;
  	$aService[$iCount]["name"] = urldecode($supply_name);
  	$aService[$iCount]["unit"] = $supply_unit;
  	$aService[$iCount]["note"] = urldecode($order_note);
  	$aService[$iCount]["price"] = ($total_amt * $sale_price);
  }
}
//Doctor Fee //SV0002

$mysqli->close();



if($iLab!="0") $sHtml.=getSupSumRow("Lab","ค่าตรวจทางห้องปฏิบัติการ",false,$iLab);
if($iDrug!="0") $sHtml.=getSupSumRow("Drug","ค่ายา",false,$iDrug);

$curCode = ""; $iTotal = 0; $sName = "	";
foreach ($aService as $iRow => $aInfo) {
	if($aInfo["group"]=='S00005' || $aInfo["group"]=='S00006'){
		//$sText = $aInfo["name"].$aInfo["note"];
		if($curCode == $aInfo["group"]){
			//Same Code DO SUM
			$iTotal = $iTotal + ($aInfo["price"]*1);
		}else{
			if($curCode!=""){
				// NEW Code End Previous
				if($iTotal!=0){
					$sHtml.=getSupSumRow("",$sName,false,$iTotal);
				}
			}
			$curCode = $aInfo["group"];
			$sName = $aInfo["groupname"];
			$iTotal = ($aInfo["price"]*1);
		}

	}
}



foreach ($aService as $iKey => $aInfo) {
	if($aInfo["type"]!=1 && $aInfo["group"]!='S00005' && $aInfo["group"]!='S00006'){
		//$sText = $aInfo["name"].$aInfo["note"];
		if($aInfo["price"]!="0"){

			$sHtml.=getSupSumRow($aInfo["code"],$aInfo["name"],false,$aInfo["price"]);
		}
	}
}



?>
<div class='fl-wrap-col fs-xs' style='background-color: white'>
	<div class='fl-fill hmi-xl fl-auto'>
		<? echo($sHtml); ?>
	</div>
	<div class='fl-wrap-row h-xs row-color'>
		<div class='fl-fix w-m'>

		</div>
		<div class='fl-fill' style='font-weight: bold'>
			รวมทั้งหมด
		</div>
		<div class='fl-fix w-m' style='text-align:right'>
			<? echo($iFinalTotal); ?>
		</div>
		<div class='fl-fix w-xxs'>

		</div>
	</div>
</div>
