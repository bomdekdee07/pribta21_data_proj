<?
	include_once("in_php_function.php");
	$sGroup = urldecode(getQS("group"));
	$sType = urldecode(getQS("type"));
	$sMode = urldecode(getQS("u_mode"));
	$sCode =getQS("code");


	include("in_db_conn.php");
	$sJs="";
	$aType=array(); $aGroup=array(); $aItem=array();
	$optType="";$optGroup=""; $sMaxID=""; $sInitial = ""; $iRunning = 0;

	if($sMode=="add"){
		//Load Group Name and Type if not found ignore
		$query = "SELECT IST.supply_group_type,supply_type_name
		,supply_group_code,supply_group_name,is_service,supply_group_initial,supply_group_running
		FROM i_stock_type IST LEFT JOIN i_stock_group ISG
		ON ISG.supply_group_type = IST.supply_group_type
		WHERE ISG.supply_group_code = ?";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("s",$sGroup);
		if($stmt->execute()){
			$stmt->bind_result($supply_group_type,$supply_type_name,$supply_group_code,$supply_group_name,$is_service,$supply_group_initial,$supply_group_running);
			while($stmt->fetch()){
				$optType="<option value='$supply_group_type'>$supply_type_name</option>";
				$optGroup="<option value='$supply_group_code'>$supply_group_name</option>";
				$sJs .= "var isService='".$is_service."';
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_code']\"),'".$supply_group_initial."');

				";
				$sInitial = $supply_group_initial;
				$iRunning = $supply_group_running;

			}
		}
		if($sInitial!=""){
			$sTemp = "";
			for($ix=0;$ix<$iRunning-1;$ix++){
				$sTemp.="_";
			}
			$query = "SELECT MAX(supply_code) FROM i_stock_master WHERE supply_code LIKE '".$sInitial."____%'";
			$stmt=$mysqli->prepare($query);
			if($stmt->execute()){
				$stmt->bind_result($maxCode);
				while($stmt->fetch()){
					$sMaxID =$maxCode;
				}
			}
		}
	}else if($sMode=="edit"){
		$query = "SELECT IST.supply_group_type,supply_type_name,
		ISG.supply_group_code,supply_group_name,
		supply_code,supply_name,supply_desc,supply_unit, supply_unit_en, dose_day,dose_per_time,dose_before,dose_breakfast,dose_lunch,dose_dinner,dose_night,dose_note,supply_status ,is_service

		FROM i_stock_master ISM
		LEFT JOIN i_stock_group ISG
		ON ISG.supply_group_code = ISM.supply_group_code

		LEFT JOIN i_stock_type IST
		ON IST.supply_group_type = ISG.supply_group_type

		WHERE ISM.supply_code = ?";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("s",$sCode);
		if($stmt->execute()){
			$stmt->bind_result($supply_group_type,$supply_type_name,$supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$supply_unit_en,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status,$is_service );
			while($stmt->fetch()){
				$optType="<option value='$supply_group_type'>$supply_type_name</option>";
				$optGroup="<option value='$supply_group_code'>$supply_group_name</option>";

				$sJs .= "setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_code']\"),".json_encode($supply_code).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_name']\"),".json_encode($supply_name).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_desc']\"),".json_encode($supply_desc).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_unit']\"),".json_encode($supply_unit).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='supply_unit_en']\"),".json_encode($supply_unit_en).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='dose_day']\"),".json_encode($dose_day).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='dose_per_time']\"),".json_encode($dose_per_time).");
				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='dose_before']\"),".json_encode($dose_before).");

				$(\"#divItemDetail .saveinput[data-keyid='dose_breakfast']\").attr('checked',".(($dose_breakfast=="1")?"true":"false").");
				$(\"#divItemDetail .saveinput[data-keyid='dose_breakfast']\").attr('data-odata',".$dose_breakfast.");
				$(\"#divItemDetail .saveinput[data-keyid='dose_lunch']\").attr('checked',".(($dose_lunch=="1")?"true":"false").");
				$(\"#divItemDetail .saveinput[data-keyid='dose_lunch']\").attr('data-odata',".$dose_lunch.");
				$(\"#divItemDetail .saveinput[data-keyid='dose_dinner']\").attr('checked',".(($dose_dinner=="1")?"true":"false").");
				$(\"#divItemDetail .saveinput[data-keyid='dose_dinner']\").attr('data-odata',".$dose_dinner.");
				$(\"#divItemDetail .saveinput[data-keyid='dose_night']\").attr('checked',".(($dose_night=="1")?"true":"false").");
				$(\"#divItemDetail .saveinput[data-keyid='dose_night']\").attr('data-odata',".$dose_night.");

				setPribtaObj($(\"#divItemDetail .saveinput[data-keyid='dose_note']\"),".json_encode($dose_note).");
				$(\"#divItemDetail .saveinput[data-keyid='supply_status']\").attr('checked',".(($supply_status=="1")?"true":"false").");
				$(\"#divItemDetail .saveinput[data-keyid='supply_status']\").attr('data-odata',".$supply_status.");";

				$sJs .= "var isService='".$is_service."';";
			}
		}
	}

	$sHtmlPrice = "";
	$query = "SELECT SO.sale_opt_id,sale_opt_name,data_seq,is_enable,supply_code,sale_price FROM sale_option SO
	LEFT JOIN i_stock_price ISSO
	ON ISSO.sale_opt_id = SO.sale_opt_id
	AND supply_code = ?
	ORDER BY data_seq ";
	$stmt=$mysqli->prepare($query);
	$stmt->bind_param("s",$sCode);
	$iRow=0;
	if($stmt->execute()){
		$stmt->bind_result($sale_opt_id,$sale_opt_name,$data_seq,$is_enable,$supply_code,$sale_price);
		while($stmt->fetch()){
			$sHtmlPrice .= "<div class='fl-wrap-row row-hover row-color'>
				<div class='fl-fill lh-15 fl-mid'>
					$sale_opt_name ".(($iRow==0)?"<input id='btnCopyAll' class='fs-smaller' type='button' style='margin-left:20px' value='Copy to All' />":"")."
				</div>
				<div class='fl-fix w-60 fl-mid'>
					<input class='fill-box sale_opt_id' type='number' data-saleid='$sale_opt_id' data-odata='$sale_price' value='$sale_price' />
				</div>
			</div>";
			$iRow++;
		}
	}

	$mysqli->close();

?>
<div  class='fl-wrap-row'>
	<div id='divItemInfo' class='fl-wrap-col'>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Type :
			</div>
			<div class='fl-fill'>
				<SELECT id='ddlGroupType' class='fill-box' data-keyid='supply_group_type'>
					<? echo($optType); ?>
				</SELECT>
			</div>
			<div class='fl-fill'>
				<SELECT id='ddlGroup' class='saveinput fill-box' data-pk='1' data-keyid='supply_group_code'>
					<? echo($optGroup); ?>
				</SELECT>
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Code :
			</div>
			<div class='fl-fill'>
				<input class='saveinput fill-box' data-initial='<? echo($sInitial); ?>' data-keyid='supply_code' style='background-color: silver' <? echo(($sMode=="add")?"":"readonly='true'"); ?> data-odata='<? echo(isset($supply_code)?$supply_code:""); ?>' value='<? echo(isset($supply_code)?$supply_code:""); ?>' data-pk='1' />
			</div>
			<div class='fl-fix w-100 fs-smaller' style='color:red'>
				<? echo(($sMaxID=="")?"":"*Max Code is ".$sMaxID); ?>
			</div>
			<div class='fl-fill' >
				Active : <input type='checkbox' data-keyid='supply_status'  value='1' class='saveinput bigcheckbox' checked="true" data-odata='' />
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Name :
			</div>
			<div class='fl-fill'>
				<input class='saveinput fill-box' data-keyid='supply_name' data-odata='' value='' />
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Unit : 
			</div>
			<div class='fl-fill'>
				<input class='saveinput fill-box' data-keyid='supply_unit' data-odata='' value='' />
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Unit(EN): 
			</div>
			<div class='fl-fill'>
				<input class='saveinput fill-box' data-keyid='supply_unit_en' data-odata='' value='' />
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Description :
			</div>
			<div class='fl-fill'>
				<input class='saveinput fill-box' data-keyid='supply_desc' data-odata='' value='' />
			</div>
		</div>
		<div class='fl-wrap-row h-30 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				<span class='supply'>ครั้งล่ะ : </span>
				<span class='non-supply'>จำนวนครั้ง : </span>
			</div>
			<div class='fl-fix w-60'>
				<input class='saveinput fill-box' data-keyid='dose_per_time' data-odata='' value='1' title='ใส่จำนวนครั้ง' />
			</div>
			<div class='fl-fill al-right' >
				<span class='supply'>จำนวน :</span>
				<span class='non-supply'>ค่าเริ่มต้น : </span>
			</div>
			<div class='fl-fix w-60'>
				<input class='saveinput fill-box' data-keyid='dose_day'  data-odata='' value='' title='ราคาเริ่มต้นที่ต้องการให้แสดง สามารถแก้ไขได้' />
			</div>

		</div>
		<div class='fl-wrap-row h-50 row-color lh-25 fl-mid supply'>
			<div class='fl-fix w-120' style='text-align: right'>
				Direction :
			</div>
			<div class='fl-fill'>
				<SELECT id='ddlBefore' class='saveinput fill-box' data-keyid='dose_before'>
					<option value=''>---(เวลา)---</option>
					<option value='P'>พร้อมมื้อ</option>
					<option value='B'>ก่อนมื้อ</option>
					<option value='A'>หลังมื้อ</option>
				</SELECT>
			</div>
			<div class='fl-wrap-col w-50' >
				<div>เช้า</div>
				<div><input type='checkbox' data-keyid='dose_breakfast' class='saveinput bigcheckbox' data-odata='0' /></div>
			</div>
			<div class='fl-wrap-col w-50' >
				<div>เที่ยง</div>
				<div><input type='checkbox' data-keyid='dose_lunch' class='saveinput bigcheckbox' data-odata='0' /></div>
			</div>
			<div class='fl-wrap-col w-50' >
				<div>เย็น</div>
				<div><input type='checkbox' data-keyid='dose_dinner' class='saveinput bigcheckbox'  data-odata='0' /></div>
			</div>
			<div class='fl-wrap-col w-50' >
				<div>นอน</div>
				<div><input type='checkbox' data-keyid='dose_night' class='saveinput bigcheckbox'  data-odata='0' /></div>
			</div>
		</div>
		<div class='fl-wrap-row h-70 row-color lh-15 fl-mid'>
			<div class='fl-fix w-120' style='text-align: right'>
				Note :
			</div>
			<div class='fl-fill'>
				<textarea class='saveinput fill-box' style='height:68px' data-keyid='dose_note'></textarea>
			</div>
		</div>
	</div>
	<div class='fl-fix w-10'>
	</div>
	<div id='divItemPrice' class='fl-wrap-col' style='min-width:400px;max-width:400px;'>
		<div class='fl-fix h-30 lh-15'>
			Sale Option (Baht)/ราคาขาย (บาท)<br/>
			<span style='color:red'>*หากเป็นส่วนลด ให้ใส่ -1 หรือ จำนวนส่วนลด</span>
		</div>
		<div class='fl-wrap-col fl-auto fs-smaller'>
			<? echo($sHtmlPrice); ?>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		<? echo($sJs); ?>
		if(isService!="1"){
			$("#divItemDetail .supply").show();
			$("#divItemDetail .non-supply").hide();
		}else{
			$("#divItemDetail .supply").hide();
			$("#divItemDetail .non-supply").show();
		}

		$("#divItemPrice #btnCopyAll").unbind("click");
		$("#divItemPrice #btnCopyAll").on("click",function(){
			sVal = $("#divItemPrice .sale_opt_id:first-child").val();
			$("#divItemPrice .sale_opt_id").val(sVal);
		});
	});
</script>