<?
include_once("in_session.php");
include_once("in_php_function.php");

function getModuleOptList($sModuleId,$sOptionCode,$sOptionTitle,$sOptionEnable){
	return("<div class='row-option fl-wrap-row h-30 row-color-2 row-hover' data-optcode='$sOptionCode'>
		<div class='fl-fix w-60 fl-mid '>

		</div>
		<div class='fl-fix w-200 fl-mid'>
			$sOptionCode
		</div>
		<div class='fl-fill fl-mid'>
			$sOptionTitle
		</div>
		<div class='fl-fix w-50 fl-mid'>
			".(($sOptionEnable)?"<i class='fas fa-check-circle fa-lg'></i>":"")."
		</div>
		<div class='fl-fix w-60 fl-mid fabtn btndeleteoptcode' style='color:red'>
			<i class='fas fa-trash-alt fa-lg'></i>
		</div>
		<div class='fl-fix w-60 fl-mid fabtn btndeleteoptcode-loader' style='display:none'>
			<i class='fa fa-spinner fa-spin fa-lg'></i>
		</div>
	</div>");
}

function getOldOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid,$is_pickup,$supply_group_icon){
	$isStatus = ""; $isPaid = ""; $isPickup = "";


	if($is_pickup=="1") $isPickup="<i class='fas fa-shopping-basket fa-lg' title='This item was pickup'></i>";
	if($is_paid) {
		$isPaid="<i class='fas fa-dollar-sign' title='This item is paid.'></i>";
	}

	$sUsage = getDoseBefore($dose_before);
	$sUseTime = "";
	if($dose_breakfast) $sUseTime=(($sUseTime=="")?" ":"")."เช้า";
	if($dose_lunch) $sUseTime=(($sUseTime=="")?" ":"")."เที่ยง";
	if($dose_dinner) $sUseTime=(($sUseTime=="")?" ":"")."เย็น";
	if($dose_night) $sUseTime=(($sUseTime=="")?" ":"")."ก่อนนอน";

	if($sUseTime!=""){
		if($sUseTime=="ก่อนนอน")	$sUsage = $sUseTime;
		else $sUsage.=" อาหาร ".$sUseTime;
	}
	$sGIcon = "";
	if($supply_group_icon!="") $sGIcon="<i class='".$supply_group_icon."'></i>";

	return("<div class='fl-wrap-row data-row row-color h-35 lh-15 row-hover' data-ocode='$order_code' data-supcode='$supply_code' data-ostatus='$order_status' data-isservice='$is_service' data-ispaid='$is_paid' data-ispickup='$is_pickup'>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-30' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
			<div class='fl-wrap-col al-left'>
				<div class='fl-fill h-20 lh-20 fs-smaller fw-b' style='overflow:hidden;white-space: nowrap;' title='$supply_name'>$sGIcon $supply_name</div>
				<div class='fl-fill h-15 fs-xsmall' style='overflow:hidden'>$supply_desc $sUsage </div>
			</div>
			<div class='fl-wrap-col w-60'><div class='fl-fill  fl-mid'>".(($is_service=="1")?$total_price:$total_amt)."</div><div class='fl-fill fs-xsmall'>".(($is_service=="1")?"บาท":$supply_unit)."</div></div>
		</div>");
}

function getCashOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid=0,$is_pickup=0,$supply_group_type=""){
	$isStatus = ""; $isPaid = ""; $isPickup = ""; $sEditBtn="<i class='fas fa-edit fa-lg'></i>"; $sEditClass=" fabtn btneditorder";
	$sDelBtn="<i class='fas fa-trash-alt'></i>"; $sDelClass=" fabtn btndeleteorder";

	if($order_status=="0") $isStatus="<i class='fas fa-dollar-sign'></i>";
	if($is_pickup=="1") $isPickup="<i class='fas fa-shopping-basket fa-lg' title='This item was pickup'></i>";
	if($is_paid) {
		$isPaid="<i class='fas fa-dollar-sign fa-lg' title='This item is paid.'></i>";
		$sEditBtn="";$sEditClass=""; $sDelBtn=""; $sDelClass="";
	}

	$aP = getPerm("ORDER",$supply_group_type);
	if($aP!=""){
		if($aP["delete"]!="1") {$sDelClass="";$sDelBtn="";}
		if($aP["update"]!="1") {$sEditClass="";$sEditBtn="";}
	}else{
		$sDelClass="";$sDelBtn="";
		$sEditClass="";$sEditBtn="";
	}




	$sUsage = getDoseBefore($dose_before);
	$sUseTime = "";

	return("<div class='fl-wrap-row data-row row-color h-40 lh-20 row-hover' data-ocode='$order_code' data-supcode='$supply_code' data-ostatus='$order_status' data-isservice='$is_service' data-ispaid='$is_paid' data-ispickup='$is_pickup'>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPaid</div>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPickup</div>
			<div class='fl-fix w-30 fl-mid $sEditClass' title='Edit order'>$sEditBtn</div>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-40' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
			<div class='fl-wrap-col al-left'>
				<div class='fl-fill h-20 supply-name'>$supply_name</div>
				<div class='fl-fill h-20 fs-xsmall' style='overflow:hidden'>$supply_desc </div>
			</div>
			<div class='fl-fix w-50 fl-mid'>$total_amt $supply_unit</div>
			<div class='fl-wrap-col w-100'>
				<div class='fl-fill h-20 fs-xsmall al-right'>$total_amt x $sale_price</div>
				<div class='fl-fill h-20 fs-b al-right'>$total_price บาท</div>
			</div>
			<div class='fl-fix w-40 fl-mid  $sDelClass h-40' style='color:red'>$sDelBtn</div>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-40' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
		</div>");
}

function getViewOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid=0,$is_pickup=0,$supply_group_type="",$show_print_sum="",$supply_lot=""){
	$isStatus = ""; $isPaid = ""; $isPickup = ""; $sEditBtn="<i class='fas fa-edit fa-lg'></i>"; $sEditClass=" btneditorder";
	$sPrintSum="";  $sTodayCode=date("Ymd");
	$sDelBtn="<i class='fas fa-trash-alt'></i>"; $sDelClass=" btndeleteorder";

	if($order_status=="0") $isStatus="<i class='fas fa-dollar-sign'></i>";
	if($is_pickup=="1") $isPickup="<i class='fas fa-shopping-basket fa-lg' title='This item was pickup'></i>";
	if($is_paid) {
		$isPaid="<i class='fas fa-dollar-sign fa-lg' title='This item is paid.'></i>";
		 $sDelBtn=""; $sDelClass="";
	}
	if(strpos($order_code, $sTodayCode)===false){
		$sEditBtn="";$sEditClass="";  $sDelBtn=""; $sDelClass="";
	}

	$sUsage = getDoseBefore($dose_before);	$sUseTime = "";
	if($dose_breakfast) $sUseTime.=(($sUseTime=="")?" ":"")."เช้า";
	if($dose_lunch) $sUseTime.=(($sUseTime=="")?" ":"")."เที่ยง";
	if($dose_dinner) $sUseTime.=(($sUseTime=="")?" ":"")."เย็น";
	if($dose_night) $sUseTime.=(($sUseTime=="")?" ":"")."ก่อนนอน";
	if($sUseTime!=""){
		if($sUseTime=="ก่อนนอน") $sUsage = $sUseTime;
		else $sUsage .= " อาหาร ".$sUseTime;
	}

	$sChkBox="";$sBtnPrint="<div class='fl-fix w-50 fl-mid ";

	$sPrintSum=(($show_print_sum=="1")?"<div class='fl-fix w-30 fl-mid fabtn btnPrintLabelSum' title='Print summary sticker of ".$supply_name."'><i class='fas fa-map fa-lg'></i></div>":"<div class='fl-fix w-30 fl-mid'></div>");

	if($is_service || $is_pickup){
		$sChkBox="<input type='checkbox' class='bigcheckbox' checked='true' disabled='true' />";
		if($is_pickup && $is_service!=1) {
			$sBtnPrint.="fabtn btnprintlabel'>
				<i class='fa fa-print fa-lg'></i>";
		}else{
			$sBtnPrint.="'>";
		}
		$sPrintSum="<div class='fl-fix w-30 fl-mid'></div>";
	}else if($is_paid){
		//$sChkBox="<input type='checkbox' class='bigcheckbox chksupply' checked='true' />";
		$sBtnPrint.="fabtn btnprintlabel'>
				<i class='fa fa-print fa-lg'></i>";
	}else{
		//$sChkBox="<input title='This item is not paid. Please asked Cashier about this.' type='checkbox' class='bigcheckbox' disabled='true' />";
		$sBtnPrint.="fabtn btnprintlabel'>
				<i class='fa fa-print fa-lg'></i>";
	}
	$sBtnPrint.="</div>";
	$aP = getPerm("ORDER",$supply_group_type);
	if(!(isset($aP["delete"]) && $aP["delete"]=="1")){
		$sDelClass="";$sDelBtn="";
	}

	$sHtml="<div class='fl-wrap-row data-row row-color h-30 lh-20 row-hover' data-ocode='$order_code' data-supcode='$supply_code' data-ostatus='$order_status' data-isservice='$is_service' data-ispaid='$is_paid' data-ispickup='$is_pickup' data-stklot='$supply_lot'>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPaid</div>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPickup</div>
			<div class='fl-fix w-30 fl-mid fabtn $sEditClass' title='Edit order'>$sEditBtn</div>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-30' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>";


	if($is_service==1){
		$sHtml.="<div class='fl-fill h-30 lh-30 supply-name'>$supply_name</div>";
	}else{
		$sHtml.="<div class='fl-wrap-col al-left'>
				<div class='fl-fill h-15 lh-15 supply-name'>$supply_name</div>
				<div class='fl-fill h-15 lh-15 fs-xsmall' style='overflow:hidden'>$supply_desc $sUsage </div>
			</div>";
	}


	$sHtml.="<div class='fl-fix al-left fl-auto w-200 fs-xsmall lh-15 popupbox'>
				$order_note
			</div>
			$sPrintSum
			";

	if($is_service==1){
		
	}else{
		$sHtml.="<div class='fl-fix w-70 al-right lh-30'>$total_amt</div>
				<div class='fl-fix w-60 fl-mid'>$supply_unit</div>";	
	}
	
	$sHtml.="<div class='fl-fix w-80 al-right lh-30'>$total_price</div>
				<div class='fl-fix w-80 fl-mid'>บาท</div>";

	$sHtml.="$sBtnPrint</div>";

	return $sHtml;
}
function getOrderList($order_code,$supply_code,$supply_name,$supply_unit,$order_status,$sale_opt_id,$sale_opt_name,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$supply_desc,$order_note,$total_amt,$sale_price,$total_price,$is_service,$is_paid=0,$is_pickup=0,$supply_group_type=""){
	$isStatus = ""; $isPaid = ""; $isPickup = ""; $sEditBtn="<i class='fas fa-edit fa-lg'></i>"; $sEditClass=" btneditorder";
	$sDelBtn="<i class='fas fa-trash-alt'></i>"; $sDelClass=" fabtn btndeleteorder";

	$aP = getPerm("ORDER",$supply_group_type);

	if(isset($aP["update"]) && $aP["update"]=="1"){

	}else{
		$sEditClass="";
	}
	if($order_status=="0") $isStatus="<i class='fas fa-dollar-sign'></i>";
	if($is_pickup=="1") {
		$isPickup="<i class='fas fa-shopping-basket fa-lg' title='This item was pickup'></i>";
		if($is_service!="1") {$sDelBtn=""; $sDelClass="";}
	}
	if($is_paid) {
		$isPaid="<i class='fas fa-dollar-sign fa-lg' title='This item is paid.'></i>";
		$sEditBtn="";$sEditClass=""; $sDelBtn=""; $sDelClass="";
	}else{


		if((isset($aP["delete"]) && $aP["delete"]=="1" )|| $is_service){
		}else {
			$sDelClass="";$sDelBtn="";
		}
	}



	$sUsage = getDoseBefore($dose_before);
	$sUseTime = "";
	if($dose_breakfast) $sUseTime=(($sUseTime=="")?"":" ")."เช้า";
	if($dose_lunch) $sUseTime.=(($sUseTime=="")?"":" ")."เที่ยง";
	if($dose_dinner) $sUseTime.=(($sUseTime=="")?"":" ")."เย็น";
	if($dose_night) $sUseTime.=(($sUseTime=="")?"":" ")."ก่อนนอน";

	if($sUseTime!=""){
		if($sUseTime=="ก่อนนอน"){
			$sUsage = $sUseTime;
		}else{
			$sUsage .= " อาหาร ".$sUseTime;
		}
	}

	return("<div class='fl-wrap-row data-row row-color h-40 lh-20 row-hover' data-ocode='$order_code' data-supcode='$supply_code' data-ostatus='$order_status' data-isservice='$is_service' data-ispaid='$is_paid' data-ispickup='$is_pickup'>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPaid</div>
			<div class='fl-fix w-30 fl-mid' style='color:green'>$isPickup</div>
			<div class='fl-fix w-30 fl-mid fabtn $sEditClass' title='Edit order'>$sEditBtn</div>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-40' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
			<div class='fl-wrap-col al-left'>
				<div class='fl-fill h-20 supply-name'>$supply_name</div>
				<div class='fl-fill h-20 fs-xsmall' style='overflow:hidden'>$supply_desc $sUsage</div>
			</div>
			<div class='fl-fix al-left fl-auto w-200 fs-xsmall'>
				$order_note
			</div>
			<div class='fl-fix w-50 fl-mid'>$total_amt $supply_unit</div>
			<div class='fl-wrap-col w-100'>
				<div class='fl-fill h-20 fs-xsmall al-right'>$total_amt x $sale_price</div>
				<div class='fl-fill h-20 fs-b al-right'>$total_price บาท</div>
			</div>
			<div class='fl-fix w-40 fl-mid  $sDelClass h-40' style='color:red'>$sDelBtn</div>
			<div class='fl-fix w-40 fl-mid fabtn-loader h-40' style='display:none'><i class='fa fa-spinner fa-spin'></i></div>
		</div>");
}
function getRelationship($uid,$rel_uid,$fname,$sname,$en_fname,$en_sname,$rel_type,$is_main=1){
	$sName=$fname." ".$sname;
	if($fname==""){
		$sName = $en_fname." ".$en_fname;
	}

	$sHtml="<div class='fl-wrap-row row-color h-25 lh-25 data-row' data-uid='$uid' data-reluid='$rel_uid' data-ismain='$is_main'>
		<div class='fl-fix w-100 '><span class='copy-to-clip'>$rel_uid</span></div>
		<div class='fl-fill'>$sName</div>
		<div class='fl-fix w-100'>$rel_type</div>
		<div class='fl-fix w-50 fl-mid' style='color:red'><i class='btnreldelete fabtn fas fa-trash-alt'></i><i class='btnreldelete-loader fa fa-spinner fa-spin' style='display:none'></i></div>
		</div>";

	return $sHtml;
}

function getUserRow($staffId,$staffName,$staffEmail,$staffPhone,$staffStatus,$staffLabLicense,$staffRemark){
	return "<div class='fl-wrap-row user-row'>
			<div class='btnedituser hideonsave fabtn fl-fix p-cmd h-50 w-50 fl-mid' data-userid='".$staffId."'>
				<i class=' fa fa-edit fa-2x' ></i>
			</div>
			<div class='fl-fix p-id'>
				<span>".$staffId."</span>
			</div>
			<div class='fl-fix p-name'>
				<span>".$staffName."</span>
			</div>
			<div class='fl-fix p-email'>
				<span>".$staffEmail."</span>
			</div>
			<div class='fl-fix p-phone'>
				<span>".$staffPhone."</span>
			</div>
			<div class='fl-fix p-status'>
				<SELECT class='ddlstaffstatus' data-odata=''>
					<option value='1' ".(($staffStatus)?"selected":"").">Enable</option>
					<option value='0' ".((!$staffStatus)?"selected":"").">Disable</option>
				</SELECT>
			</div>
			<div class='fl-fix p-li-lab'>
				<span>".$staffLabLicense."</span>
			</div>
			<div class='fl-fix p-remark popupbox'>
				<span>".$staffRemark."</span>
			</div>
			<div class='fl-wrap-row p-action fl-mid'>
				<div class='fl-fix w-50 h-50 btnpassword hideonsave fabtn fl-mid' data-sid='".$staffId."' title='Change password' style='color:orange'><i class=' fas fa-key fa-2x' ></i></div>
				<div class='fl-fix w-50 h-50 btnusergroup hideonsave fabtn fl-mid' data-sid='".$staffId."' style='color:blue' title='User Group'><i class='fas fa-clinic-medical fa-2x' ></i></div>
				<div class='fl-fix w-50 h-50 btnresetpassword hideonsave fabtn fl-mid' data-sid='".$staffId."' style='color:grey' title='Reset Password and send email'><i class='fas fa-envelope-square fa-2x' ></i></div>
				<div class='fl-fix w-50 h-50 rowspinner fl-mid' style='color: yellow;display:none'><i class='fas fa-spinner fa-spin'></i></div>
			</div>
		</div>";
}

function getStaffRow($staffId,$staffName,$staffEmail,$staffPhone,$staffStatus,$staffLabLicense,$staffRemark){
	return "<div class='fl-wrap-row fs-s fl-mid h-s row-color-2 data-row' style='line-height:15px' data-sid='$staffId'>
		<div class='fl-fix w-50'>
			<i class='btnedit hideonsave fabtn fa fa-edit fa-2x' data-sid='".$staffId."'></i>
		</div>
		<div class='fl-fix w-100 showinput' data-keyid='s_id'>
			$staffId
		</div>
		<div class='fl-fix w-150  showinput' data-keyid='s_name'>
			$staffName
		</div>
		<div class='fl-fill showinput' data-keyid='s_remark'>
			$staffRemark
		</div>
		<div class='fl-fix w-150 showinput' data-keyid='s_email'>
			$staffEmail
		</div>
		<div class='fl-fix w-120 showinput' data-keyid='s_tel'>
			$staffPhone
		</div>
	</div>
";
}


function getPageRow($page_id,$page_title,$page_desc,$page_link,$page_enable,$page_fa_icon,$page_color){
	return "<div class='fl-wrap-row page-row' style='line-height:15px'>
			<div class='fl-fix p-cmd'>
				<i class='btneditpage hideonsave fabtn fa fa-edit fa-lg' data-pageid='".$page_id."'></i>
			</div>
			<div class='fl-fix p-id'>
				<span>".$page_id."</span>
			</div>
			<div class='fl-fix p-title'>
				<span>".$page_title."</span>
			</div>
			<div class='fl-fill p-desc'>
				<span>".$page_desc."</span>
			</div>
			<div class='fl-fix p-link'>
				<span>".$page_link."</span>
			</div>
			<div class='fl-fix p-enable'>
				<SELECT class='ddlpageenable'><option value='1' ".(($page_enable)?"selected":"").">Enable</option>
				<option value='0' ".((!$page_enable)?"selected":"").">Disable</option>
				</SELECT>
			</div>
			<div class='fl-fix p-icon'>
				<span class='page-fa-color' style='color:".$page_color."'><i class='fa ".$page_fa_icon."'></i></span><span class='page-fa-icon'>".$page_fa_icon."</span>
			</div>
			<div class='fl-fix p-color'>
				<span style='color:".$page_color."'>".$page_color."</span>
			</div>
			<div class='fl-fix p-action'>
				<span style='color: Tomato;'><i class='btnpagedelete hideonsave fabtn fas fa-trash-alt' data-pageid='".$page_id."'></i></span>
				<span class='rowspinner' style='color: yellow;display:none'><i class='fas fa-spinner fa-spin'></i></span>
			</div>

		</div>";
}

function getSecRow($sec_id,$sec_name,$sec_note,$sec_enable){
	return "<div class='fl-wrap-row sec-row'>
			<div class='fl-fix sec-cmd'>
				<i class='btneditsec hideonsave fabtn fa fa-edit fa-lg' data-secid='".$sec_id."'></i>
			</div>
			<div class='fl-fix sec-id'>
				<span>".$sec_id."</span>
			</div>
			<div class='fl-fix sec-name'>
				<span>".$sec_name."</span>
			</div>
			<div class='fl-fill sec-note'>
				<span>".$sec_note."</span>
			</div>
			<div class='fl-fix sec-enable'>
				<SELECT class='ddlsecenable'><option value='1' ".(($sec_enable)?"selected":"").">Enable</option>
				<option value='0' ".((!$sec_enable)?"selected":"").">Disable</option>
				</SELECT>
			</div>
			<div class='fl-fix sec-action'>
				<span style='color: green;'><i class='btnexpperm hideonsave fas fa-file-excel fabtn'  data-secid='".$sec_id."' title='Edit Permission for Export allow for this section'></i></span>
				<span style='color: green;'><i class='btnsecpermission hideonsave fabtn fas fa-link' data-secid='".$sec_id."' title='Edit Permission for this section'></i></span>
				<span style='color: blue;'><i class='btnmodpermission hideonsave fabtn fas fa-layer-group' data-secid='".$sec_id."' title='Module Permission for this section'></i></span>
				<span style='color: Tomato;margin-left:15px'><i class='btnsecdelete hideonsave fabtn fas fa-trash-alt' data-secid='".$sec_id."' title='Delete section'></i></span>
				<span class='rowspinner' style='color: yellow;display:none'><i class='fas fa-spinner fa-spin'></i></span>
			</div>
		</div>";
}

function getSecPermRow($section_id,$page_id,$page_title,$page_allow,$start_date,$stop_date,$page_seq,$is_admin){
	return "<div class='fl-wrap-row secperm-row' data-secid='".$section_id."' data-pageid='".$page_id."'>
			<div class='fl-fix secperm-cmd'>
				<i class='btneditsecperm hideonsave fabtn fa fa-edit fa-lg' data-secid='".$section_id."' data-pageid='".$page_id."'></i>
				<input class='page_id_list' type='hidden' value='".$page_id."' />
			</div>
			<div class='fl-fix secperm-pseq'>".$page_seq."</div>
			<div class='fl-fix secperm-title'>
				<span>".$page_title."</span>
			</div>
			<div class='fl-fix secperm-enable'>
				<SELECT class='ddlpageenable'><option value='1' ".(($page_allow)?"selected":"").">Enable</option>
				</SELECT>
			</div>
			<div class='fl-fix secperm-start'>".$start_date."</div>
			<div class='fl-fix secperm-stop'>".$stop_date."</div>
			<div class='fl-fix secperm-admin'>".$is_admin."</div>
			<div class='fl-fill secperm-action'>
				<span style='color: Tomato;margin-left:15px'><i class='btnsecpermdelete hideonsave fabtn fas fa-trash-alt' data-secid='".$section_id."' data-pageid='".$page_id."' title='Delete section permision'></i></span>
				<span class='rowspinner' style='color: yellow;display:none'><i class='fas fa-spinner fa-spin'></i></span>
			</div>
		</div>";
}
function getExpPermRow($section_id,$form_id,$form_name_th,$allow_view,$allow_edit,$allow_export,$start_date,$stop_date){
	return "<div class='fl-wrap-row expperm-row row-color' data-secid='".$section_id."' data-formid='".$form_id."'>
			<div class='fl-fix expperm-cmd'>
				<i class='btneditexpperm hideonsave fabtn fa fa-edit fa-lg' data-secid='".$section_id."' data-formid='".$form_id."'></i>
				<input class='form_id_list' type='hidden' value='".$form_id."' />
			</div>
			<div class='fl-fix expperm-section'>".$section_id."</div>
			<div class='fl-fill expperm-form' data-formid='$form_id'>".$form_id.":".$form_name_th."</div>
			<div class='fl-fix expperm-view'>".$allow_view."</div>
			<div class='fl-fix expperm-edit'>".$allow_edit."</div>
			<div class='fl-fix expperm-export'>".$allow_export."</div>
			<div class='fl-fix expperm-start'>".$start_date."</div>
			<div class='fl-fix expperm-stop'>".$stop_date."</div>
			<div class='fl-fix expperm-action'>
				<span style='color: Tomato;margin-left:15px'><i class='btnexppermdelete hideonsave fabtn fas fa-trash-alt' data-secid='".$section_id."' data-formid='".$form_id."' title='Delete export permision'></i></span>
				<span class='rowspinner' style='color: yellow;display:none'><i class='fas fa-spinner fa-spin'></i></span>
			</div>
		</div>";
}

function getSupOrderDrug($order_id,$supply_code,$supply_name,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$order_note,$sale_opt_id,$stock_lot,$sale_price,$total_amt,$order_status,$supply_group_type,$supply_desc,$s_name,$lang="th"){
	//Short Version
	$aDoseBefore_th=array("A"=>"หลังอาหาร","B"=>"ก่อนอาหาร","0"=>"");
	$aDoseBefore_en=array("A"=>"After","B"=>"Before","0"=>"");

	if($sale_opt_id==""){
		$sale_opt_id="S01";
		$sale_price = 1;
	}

	$sUsage = "";
	if(isset($aDoseBefore_th[$dose_before])){
		$sUsage.=$aDoseBefore_th[$dose_before];
	}

	if($dose_breakfast==1) $sUsage.=" เช้า";
	if($dose_lunch==1) $sUsage.=" เที่ยง";
	if($dose_dinner==1) $sUsage.=" เยีน";
	if($dose_night==1) $sUsage.=" ก่อนนอน";

	if($sUsage!="")$sUsage.=" ";

	$stock_lot = urldecode($stock_lot);
	if($aDoseBefore_th[$dose_before]=="" && $dose_breakfast==0 && $dose_lunch==0 && $dose_dinner==0 && $dose_night==0)	$isHide = "order-hide";

	$sRow = "<div class='order-row fl-wrap-row row-color-2' data-orderid='$order_id' data-supcode='$supply_code' data-stklot='$stock_lot' data-ordstatus='$order_status'>

		<div class='order-cmd fl-fix w-m'>".getOrderStatus($order_status,$lang)."<br/>".$supply_code."</div>
		<div class='order-name fl-fill'><span class='btneditdrug lang-th'>$supply_name</span><br/><span>".$sUsage.urlDecode($supply_desc).urlDecode($order_note)."</span></div>

		<div class='order-amt fl-fix w-m'><span class='order-amt-no'>$total_amt</span> $supply_unit</div>
		";
	$sRow .= "</div>"; //End of order-row
	return $sRow;
}
function getSupOrder($order_id,$supply_code,$supply_name,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$order_note,$sale_opt_id,$stock_lot,$sale_price,$total_amt,$order_status,$supply_group_type,$supply_desc,$s_name,$lang="th"){
	$aDoseBefore_th=array("A"=>"หลังอาหาร","B"=>"ก่อนอาหาร","0"=>"");
	$aDoseBefore_en=array("A"=>"After","B"=>"Before","0"=>"");

	if($sale_opt_id==""){
	$sale_opt_id="S01";
	$sale_price = 1;
	}

	$stock_lot = urldecode($stock_lot);
	if($aDoseBefore_th[$dose_before]=="" && $dose_breakfast==0 && $dose_lunch==0 && $dose_dinner==0 && $dose_night==0)
	$isHide = "order-hide";

	$sRow = "<div class='order-row fl-wrap-row' data-orderid='$order_id' data-supcode='$supply_code'   data-stklot='$stock_lot' data-ordstatus='$order_status'>

		<div class='order-cmd fl-fix'><i class='fa fa-trash-alt btndelsuporder'></i><br/>".getOrderStatus($order_status,$lang)."<br/><span style='font-size:x-small'>".$s_name."</span></div>
		<div class='order-name btneditdrug fl-fix'><span class='lang-th'>$supply_code : $stock_lot<br/>$supply_name</span><br/>".(($supply_group_type==1)?"<i class='fa fa-print'>Print</i>":"")."</div>
		<div class='order-dose-day fl-fix order-hide'>$dose_day</div>
		<div class='order-details fl-fill'>
			<div class='order-times drug-only'>
				<span class='lang-th'>(".$dose_per_time.") ".$aDoseBefore_th[$dose_before]."</span>
				<span class='lang-th order-bf'>".(($dose_breakfast==1)?" เช้า":"")."</span>
				<span class='lang-th order-lunch'>".(($dose_lunch==1)?" เที่ยง":"")."</span>
				<span class='lang-th order-dinner'>".(($dose_dinner==1)?" เย็น":"")."</span>
				<span class='lang-th order-bed'>".(($dose_night==1)?" ก่อนนอน":"")."</span>
			</div>
			<div>".urlDecode($supply_desc)."</div>
			<div>".urlDecode($order_note)."</div>
		</div>
		<div class='order-amt fl-fix'><span class='order-amt-no'>$total_amt<span> $supply_unit</div>
		<div class='order-price fl-fix'>$sale_price</div>
		<div class='order-total fl-fix'>".($sale_price*$total_amt)."</div>

		";

	//Short Version
	$sRow = "<div class='order-row fl-wrap-row row-color-2' data-orderid='$order_id' data-supcode='$supply_code' data-stklot='$stock_lot' data-ordstatus='$order_status'>

		<div class='order-cmd fl-fix'><i class='fa fa-trash-alt btndelsuporder'></i><br/>".getOrderStatus($order_status,$lang)."<br/></div>
		<div class='order-name fl-fill'><span class='btneditdrug lang-th' style='font-size:x-small'>$supply_code : $stock_lot</span><br/><span>$supply_name</span>".(($supply_group_type==1)?"<i class='fabtn fa fa-print'>Print</i>":"")."</div>
		<div class='order-dose-day fl-fix order-hide'  style='display:none'>$dose_day</div>
		<div class='order-details fl-fill' style='display:none'>
			<div class='order-times drug-only'>
				<span class='lang-th'>(".$dose_per_time.") ".$aDoseBefore_th[$dose_before]."</span>
				<span class='lang-th order-bf'>".(($dose_breakfast==1)?" เช้า":"")."</span>
				<span class='lang-th order-lunch'>".(($dose_lunch==1)?" เที่ยง":"")."</span>
				<span class='lang-th order-dinner'>".(($dose_dinner==1)?" เย็น":"")."</span>
				<span class='lang-th order-bed'>".(($dose_night==1)?" ก่อนนอน":"")."</span>
			</div>
			<div style='display:none'>".urlDecode($supply_desc)."</div>
			<div>".urlDecode($order_note)."</div>
		</div>
		<div class='order-amt fl-fix'><span class='order-amt-no'>$total_amt</span> $supply_unit</div>
		<div class='order-price fl-fix'>$sale_price</div>
		<div class='order-total fl-fix'>".($sale_price*$total_amt)."</div>

		";
	$sRow .= "</div>"; //End of order-row
	return $sRow;
}

function getLabResultRow($lab_id,$lab_result,$lab_result_report,$lab_result_note,$lab_result_status,$external_lab,$time_lastupdate,$time_confirm){

}

function getPInfoShortRow($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark,$sModule=""){

	$sIDPhoto = "idimg/".$citizen_id.".png";
	if(file_exists($sIDPhoto)){

	}else{
		$sIDPhoto = "assets/image/nophoto.jpg";
	}
	$sTHDOB=$date_of_birth;	$sDCDOB=$date_of_birth;

	if($date_of_birth!="") {
		$aT = explode("-",$date_of_birth);
		if(count($aT)>1){
			$sTHDOB=(($aT[0]>2400)?$aT[0]:(($aT[0]*1) + 543))."-".$aT[1]."-".$aT[2];
			$sDCDOB=(($aT[0]>2400)?(($aT[0]*1) + 543):$aT[0])."-".$aT[1]."-".$aT[2];
		}
	}

	$sex = (($sex=="1")?"M":(($sex=="2")?"F":""));

	$sSexIcon = "";
	if($sex=="M"){
		$sSexIcon="<span><i class='fas fa-mars fa-lg'></i></span>";
	}else if($sex=="F"){
		$sSexIcon="<span style='color:red'><i class='fas fa-venus fa-lg'></i></span>";
	}else{
		$sSexIcon = $sex;
	}

	$sLabCreate="";
	if($sModule=="PHYSICIAN"){
		$sLabCreate="<div class='btnlabcreate fl-fix w-30 fl-mid fabtn h-50' title='Create Lab Order for Project' style='color:blue'>
			<i class='fa fa-flask fa-lg'></i>
		</div>";
	}

	$sTemp = "
	<div class='fl-wrap-row h-50 row-hover row-color-2 q-row' data-uid='$uid' data-queue=''>
		<div class='fl-wrap-col w-40 '>
			<div class='fl-fill fl-mid'>
				<img style='width:90%;border:1px solid silver' src='".$sIDPhoto."'  />
			</div>
		</div>
		<div class='fabtn fl-wrap-col  btn-q-info'>
			<div class='fl-fix h-20 lh-20 fw-b fs-smaller' style='overflow:hidden' >
				<span>$sSexIcon $fname $sname $nickname</span>
			</div>
			<div class='fl-wrap-row h-15 fs-xsmall'>
				<div class='fl-fix w-120'>
					UID : <span class='copy-to-clip'>$uid</span>
				</div>
				<div class='fl-fill'>
					DOB : $sDCDOB
				</div>
			</div>
			<div class='fl-wrap-row h-15 fs-xsmall'>
				<div class='fl-fix w-120'>
					UIC : <span class='copy-to-clip'>$uic</span>
				</div>
				<div class='fl-fill'>
					<i class='fas fa-mobile-alt'></i> : <span class='copy-to-clip'>$tel_no</span>
				</div>

			</div>
		</div>
		$sLabCreate
	</div>";

	return $sTemp;
}


function getPInfoFullRow($uid,$uic,$fname,$sname,$nickname,$clinic_type,$sex,$date_of_birth,$nation,$citizen_id,$passport_id,$id_address,$id_district,$id_province,$id_zone,$id_postal_code,$country_other,$tel_no,$email,$line_id,$remark){

	$sIDPhoto = "idimg/".$citizen_id.".png";
	if(file_exists($sIDPhoto)){

	}else{
		$sIDPhoto = "assets/image/nophoto.jpg";
	}
	$sTHDOB=$date_of_birth;
	$sDCDOB=$date_of_birth;

	if($date_of_birth!="") {
		$aT = explode("-",$date_of_birth);
		if(count($aT)>1){
			$sTHDOB=(($aT[0]>2400)?$aT[0]:(($aT[0]*1) + 543))."-".$aT[1]."-".$aT[2];
			$sDCDOB=(($aT[0]>2400)?(($aT[0]*1) + 543):$aT[0])."-".$aT[1]."-".$aT[2];
		}
	}

	$sex = (($sex=="1")?"M":(($sex=="2")?"F":""));

	$sSexIcon = "";
	if($sex=="M"){
		$sSexIcon="<span><i class='fas fa-mars fa-2x'></i></span>";
	}else if($sex=="F"){
		$sSexIcon="<span style='color:pink'><i class='fas fa-venus fa-2x'></i></span>";
	}else{
		$sSexIcon = $sex;
	}

	$sTemp = "
	<div class='fl-wrap-row h-60 row-hover row-color-2'>
		<div class='fl-wrap-col w-80'>
			<div class='fl-fill fl-mid'> <span style='color:green'><i class='btnselectuid fabtn fas fa-check-circle fa-2x' data-uid='$uid'></i></span></div>
			<div class='copy-to-clip h-20 fl-mid' style='width:100%'>$uid</div>
		</div>
		<div class='fl-wrap-col w-80'>
			<div class='fl-fill fl-mid uid-fwd'>
				<img style='width:50px;border:1px solid silver' src='".$sIDPhoto."'  />
			</div>
		</div>
		<div class='fl-wrap-col w-s'>
			<div class='fl-fill fl-mid'>
				$sSexIcon
			</div>
		</div>
		<div class='fl-wrap-col w-l fs-s' style='line-height:15px'>
			<div class='fl-fill'>
				<span style='font-weight:bold'>เกิด คศ.</span> <span class='copy-to-clip'>$sDCDOB</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>เกิด พศ.</span> <span class='copy-to-clip'>$sTHDOB</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>ID:</span> <span class='copy-to-clip'>$citizen_id</span>
			</div>
			<div class='fl-fill'>
				<span style='font-weight:bold'>Passport:</span> <span class='copy-to-clip'>$passport_id</span>
			</div>
		</div>
		<div class='fl-wrap-col h-ms fs-s'>
			<div class='fl-fix h-ss'  style='line-height:15px'>
				<span class='copy-to-clip'>$fname $sname $nickname</span>
			</div>
			<div class='fl-fix h-ss'>
				UIC : <span class='copy-to-clip'>$uic</span>
			</div>
		</div>

		<div class='fl-wrap-col w-xl h-ms fs-s' style='line-height:15px'>
			<div class='fl-fill' style=''>
				<span class='copy-to-clip'>$id_address $id_province $id_district $id_zone $id_postal_code</span>
			</div>

		</div>

		<div class='fl-wrap-col w-l h-ms fs-s' style='line-height:15px'>
			<div class='fl-fill fl-auto' style=''>
				$remark
			</div>

		</div>
		<div class='fl-wrap-col w-xl fs-s' style='line-height:15px;margin-left:20px'>
			<div class='fl-fill'>
				<span style='margin-right:10px'><i class='fas fa-mobile-alt'></i></span><span class='copy-to-clip'>$tel_no</span>
			</div>
			<div class='fl-fill'>
				<span style='margin-right:5px'><i class='far fa-envelope'></i></span><span class='copy-to-clip'>$email</span>
			</div>
			<div class='fl-fill'>
				<span style='margin-right:2px'><i class='fab fa-line fa-lg' style='color:#06c152'></i></span><span class='copy-to-clip'>$line_id</span>
			</div>

		</div>


	</div>";

	return $sTemp;
}

function getSearchPatientRow($uid,$uic,$fname,$lname,$nname,$clinic,$sex,$dob,$bcdob,$nation,$id,$passport,$idaddress,$iddistrict,$idprovince,$address,$district,$province,$phone,$email){
		$sTemp = "<tr class='divdatarow row-color'>
		<td  style='vertical-align:middle'><i class='fabtn fas fa-edit fa-2x btnaddque' data-uid='$uid'></i></td>
		<td >$uid<br/>".$uic."</td>
		<td >".$fname." ".$lname."<br/>".$nname."</td>
		<td >".$clinic."</td>
		<td >".$sex."</td>
		<td >".$dob."<br/>".$bcdob."</td>
		<td >".$nation."<br/>".$id." / ".$passport."</td>
		<td >ID : ".$idaddress."<br/>".$iddistrict." ".$idprovince."</td>
		<td >CONTACT : ".$address."<br/>".$district." ".$province."</td>
		<td >".$phone."<br/>".$email."</td>
		<td ></td></tr>";
		return $sTemp;
}

function getPatientRow($uid,$fname,$sname,$sex,$gender,$date_of_birth){
	$sTemp = "<div class='fl-wrap-row h-xs q-row row-color row-hover fabtn' data-uid='$uid' data-coldate='' data-coltime=''>
		<div class='fl-fix pinfo-uid w-s'>$uid</div>
		<div class='fl-fill pinfo-name' title='$date_of_birth'>$fname $sname</div>

	</div>";
	return $sTemp;
}

function getPatientVisitRow($site,$uid,$coldate,$coltime,$fname,$sname,$date_of_birth){
	$sTemp = "<div class='fl-wrap-row visit-row' data-uid='$uid' data-coldate='$coldate' data-coltime='$coltime'>
			<div class='fl-fix visit-site'>$site</div>
			<div class='fl-fix visit-uid'><b>$uid</b></div>
			<div class='fl-fix visit-date'>$coldate $coltime</div>
			<div class='fl-fill pinfo-name' title='$date_of_birth'>$fname $sname</div>
		</div>";
	 return $sTemp;
}

function getProjInfoRow($sProjId,$sProjName,$sProjDesc,$sProjRemark,$sGroupAmt,$sPIDFormat,$sDigit,$isEnable){
	$sTemp = "<div class='fl-wrap-row proj-row row-color row-hover fl-mid data-row' data-projid='$sProjId'>
			<div class='fl-fix proj-cmd w-50 fl-mid'>
				<i class='fabtn fas fa-edit fa-lg btneditproj' data-projid='$sProjId'></i>
			</div>
			<div class='fl-fix w-150 showinput' data-keyid='proj_id'>$sProjId</div>
			<div class='fl-fill showinput' data-keyid='proj_name'>".htmlentities($sProjName)."</div>
			<div class='fl-fill showinput' data-keyid='proj_desc'>
				".htmlentities($sProjDesc)."
			</div>
			<div class='fl-fill showinput' data-keyid='proj_remark'>
				".htmlentities($sProjRemark)."
			</div>
			<div class='fl-fix w-s showinput fl-mid' data-keyid='proj_group_amt'>
				$sGroupAmt
			</div>
			<div class='fl-fill showinput fl-mid' data-keyid='proj_pid_format'>
				".htmlentities($sPIDFormat)."
			</div>
			<div class='fl-fill w-s showinput fl-mid' data-keyid='proj_pid_runing_digit'>
				$sDigit
			</div>
			<div class='fl-fix w-sm showinput fl-mid' data-keyid='is_enable'>
				$isEnable
			</div>
			<div class='fl-fix proj-action w-l'>
				<span style='color:green;display:none' title='Group' ><i class='btnprojgroup fabtn fas fa-layer-group'></i></span>
				<span style='color:orange;margin-left:5px' title='Visit'><i class='fabtn fas fa-calendar btnprojvisit'></i></span>
				<span style='color:green;margin-left:5px' title='Authorize'><i class='fabtn fas fa-user-lock btnprojauth'></i></span>
				<span style='color:red;margin-left:20px' title='Delete'><i class='fabtn fas fa-trash-alt btnprojdelete'></i></span>
				<i class='fabtn fas fa-spinner fa-spin action-loader' style='display:none'></i>
			</div>
		</div>";
	 return $sTemp;
}




function getClinicList($clinic_id,$clinic_name,$clinic_address,$clinic_email,$clinic_tel,$clinic_status,$main_clinic_id,$old_clinic_id,$clinic_pid=""){
		$sTemp = "<div class='fl-wrap-row data-row fs-small fl-mid h-ss row-color row-hover'  data-clinicid='$clinic_id'>
		<div class='fl-fix w-40 fl-mid'>
			<i class='fabtn fas fa-edit fa-lg btnedit' data-projid='$clinic_id'></i>
		</div>
		<div class='fl-fix w-80 showinput '  data-keyid='clinic_id'>
			$clinic_id
		</div>
		<div class='fl-fill showinput lh-15' data-keyid='clinic_name'>
			$clinic_name
		</div>
		<div class='fl-fill showinput lh-12'  data-keyid='clinic_address'>
			$clinic_address
		</div>
		<div class='fl-fix w-150  showinput' data-keyid='clinic_email'>
			$clinic_email
		</div>
		<div class='fl-fix w-90 showinput' data-keyid='clinic_tel'>
			$clinic_tel
		</div>
		<div class='fl-fix w-50 showinput fl-mid' data-keyid='clinic_status'>
			$clinic_status
		</div>
		<div class='fl-fix w-80 showinput' data-keyid='main_clinic_id'>
			$main_clinic_id
		</div>
		<div class='fl-fix w-80 showinput fl-mid' data-keyid='clinic_pid'>
			$clinic_pid
		</div>
		<div class='fl-fix w-80 showinput' data-keyid='old_clinic_id'>
			$old_clinic_id
		</div>
		<div class='fl-fix clinic-action w-200'>
			<span style='color:blue;margin-right:10px' title='Room' ><i class='fabtn btnclinicroom fas fa-door-closed'></i></span>
			<span style='color:green;margin-right:10px' title='Queue Button Link'><i class='fabtn btnqueuelink fab fa-quora' data-qlink='".easy_enc($clinic_id)."'></i></span>
			<span style='color:blue;margin-right:10px'><i title='clinic holiday' class='fabtn fas fa-plane-departure btnholiday'></i></span>
			<span style='color:pink;margin-right:10px'><i title='Authorize Document' class='fabtn fas fas fa-file-pdf btnDocument'></i></span>
			<span style='color:green;margin-right:10px;display:none'><i title='Module Permission' class='fabtn fas fas fa-key btnModule'></i></span>
			<span style='color:red;margin-left:20px'><i class='fabtn fas fa-trash-alt btndelete'></i></span>
			<i class='fabtn fas fa-spinner fa-spin action-loader' style='display:none'></i>
		</div>
	</div>";
		return $sTemp;
}

function getRoomRow($clinic_id,$room_no,$room_name,$room_detail,$room_status,$section_id,$default_room,$room_icon=""){
	$sTemp = "<div class='fl-wrap-row fs-small fl-mid h-30 row-color data-row' data-clinicid='$clinic_id' data-roomno='$room_no'>
		<div class='fl-fix w-50'>
			<i class='fabtn fas fa-edit fa-lg btneditroom' data-clinicid='$clinic_id' data-roomno='$room_no'></i>
		</div>
		<div class='fl-fix w-100 showinput' data-keyid='clinic_id'>
			$clinic_id
		</div>
		<div class='fl-fix w-50 showinput' data-keyid='room_no'>
			$room_no
		</div>
		<div class='fl-fill  showinput' data-keyid='room_name' style='line-height:15px'>
			$room_name
		</div>
		<div class='fl-fill  showinput' data-keyid='room_detail' style='line-height:15px'>
			$room_detail
		</div>
		<div class='fl-fix w-50 showinput' data-keyid='room_status'>
			$room_status
		</div>
		<div class='fl-fix w-50 showinput' data-keyid='section_id'>
			$section_id
		</div>
		<div class='fl-fix w-80 showinput' data-keyid='default_room'>
			$default_room
		</div>
		<div class='fl-fix w-80 showinput' data-keyid='room_icon' style='line-height:15px'>
			$room_icon
		</div>
		<div class='fl-fix w-80 room-action'>

			<span style='color:red;margin-left:20px'><i class='fabtn fas fa-trash-alt btnroomdelete'></i></span>
			<i class='fabtn fas fa-spinner fa-spin action-loader' style='display:none'></i>
		</div>
	</div>";
	 return $sTemp;

}
function getRoomFwd($clinic_id,$room_no,$room_detail,$s_name,$s_id,$total_queue,$room_icon){
	$sRoomId = "room_no_".$room_no;

	$sTemp = "<div class='fl-wrap-row fs-m  fl-mid h-ss row-color data-row row-hover' data-clinicid='$clinic_id' data-roomno='$room_no'>
		<div class='fl-fix w-s'>
			<input id='$sRoomId' class='bigcheckbox' type='radio' name='room_no' value='$room_no' />
		</div>
		<div class='fl-fix w-m'>
			<label for='$sRoomId'> $room_no</label>
		</div>
		<div class='fl-fix w-xs'>".(($room_icon=="")?"":"<i class='$room_icon'></i>")."</div>
		<div class='fl-fill' style='line-height:25px'>
			<label for='$sRoomId'>$room_detail</label>
		</div>
		<div class='fl-fix w-s'>
			<label for='$sRoomId'> $total_queue</label>
		</div>
		<div class='fl-fill'>
			<label for='$sRoomId'> $s_name</label>
		</div>
	</div>";
	 return $sTemp;

}

function getDocumentList($clinic_id,$doc_code,$doc_name,$doc_template_file,$doc_status){
	$sTemp = "
	<div class='fl-wrap-row fs-s fl-mid h-ss row-color-2 data-row' style='line-height:15px'  data-clinicid='$clinic_id' data-code='$doc_code' >
		<div class='fl-fix w-m'>
			<i class='fabtn fas fa-edit fa-2x btnedit' data-clinicid='$clinic_id' data-code='$doc_code' ></i>
		</div>
		<div class='fl-fix w-l showinput' data-keyid='clinic_id'>
			$clinic_id
		</div>
		<div class='fl-fix w-m  showinput' data-keyid='doc_code'>
			$doc_code
		</div>
		<div class='fl-fill  showinput' data-keyid='doc_name'>
			$doc_name
		</div>
		<div class='fl-fix w-m showinput' data-keyid='doc_template_file'>
			$doc_template_file
		</div>
		<div class='fl-fix w-sm showinput' data-keyid='doc_status'>
			$doc_status
		</div>
		<div class='fl-fix w-xl'>

			<span class='btndelete fabtn fa-lg' style='color:red'><i class='fas fas fa-times-circle'></i></span>
			<i class='fa fa-spinner fa-spin fabtn-loader' style='display:none'></i>
		</div>
	</div>";

	return $sTemp;
}

function getModuleList($module_id,$module_title,$module_color,$module_icon){
	$sTemp = "<div class='fl-wrap-row fs-small fl-mid h-30 row-color row-data'  data-moduleid='$module_id'>
		<div class='fl-fix w-50 fl-mid'>
			<i class='fabtn fas fa-edit fa-2x btnedit' data-moduleid='$module_id'></i>
		</div>
		<div class='fl-fix w-200'>
			$module_id
		</div>
		<div class='fl-fill lh-15'>
			$module_title
		</div>
		<div class='fl-fix w-100'>
			<span class='fw-b' style='".(($module_color=="")?"":"color:".$module_color)."'>$module_color</span>
		</div>
		<div class='fl-fix w-100'>
			$module_icon
		</div>

		<div class='fl-fix w-100 fl-mid btncommand btnoption fabtn '>
			<span style='color:green' ><i class='fas fas fa-list fa-lg'></i></span>
		</div>
		<div class='fl-fix w-100 fl-mid btncommand btndelete fabtn '>
			<span style='color:red' ><i class='fas fas fa-times-circle fa-lg'></i></span>
		</div>
		<div class='fl-fix w-100 fl-mid btndelete-loader'  style='display:none'>
			<i class='fa fa-spinner fa-spin btndelete-loader'></i>
		</div>

	</div>";

	return $sTemp;
}
function getModulePermList($module_id,$section_id,$option_code,$allow_view,$allow_insert,$allow_update,$allow_delete,$allow_admin){
	$sResult="<div class='fl-wrap-row h-30 row-color row-data row-hover lh-15' data-moduleid='$module_id' data-optcode='$option_code' data-secid='$section_id'>
		<div class='fl-fix w-50 fl-mid'><i class='fabtn fas fa-edit fa-2x btnedit'></i></div>
		<div class='fl-fill fl-mid'>$module_id</div>
		<div class='fl-fix w-100 fl-mid'>$option_code</div>
		<div class='fl-fix w-80 fl-mid'>".(($allow_view)?"<i class='fas fa-check-circle'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".(($allow_insert)?"<i class='fas fa-check-circle'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".(($allow_update)?"<i class='fas fa-check-circle'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".(($allow_delete)?"<i class='fas fa-check-circle'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'>".(($allow_admin)?"<i class='fas fa-check-circle'></i>":"")."</div>
		<div class='fl-fix w-80 fl-mid'><span class='btndelete fabtn fa-lg' style='color:red'><i class='fas fas fa-times-circle'></i></span>
			<i class='fa fa-spinner fa-spin fabtn-loader' style='display:none'></i></div>
	</div>";
	return $sResult;
}
function getDocAuthBySecRow($clinic_id,$section_id,$section_name,$doc_code,$doc_name,$allow_view,$allow_edit,$allow_create,$allow_delete){
	$sTemp = "
		<div class='fl-wrap-row fs-s fl-mid h-ss row-color-2 data-row' style='line-height:15px' data-clinicid='$clinic_id' data-secid='$section_id' data-code='$doc_code'  >
		<div class='fl-fill'>
			$clinic_id
		</div>
		<div class='fl-fill'>
			[$section_id] $section_name
		</div>
		<div class='fl-fill'>
			[$doc_code] $doc_name
		</div>
		<div class='fl-fix w-m ' >
			<input type='checkbox' class='chkallow bigcheckbox' data-keyid='allow_view' ".(($allow_view==1)?"checked":"")." />
		</div>
		<div class='fl-fix w-m'>
			<input type='checkbox' class='chkallow bigcheckbox' data-keyid='allow_edit' ".(($allow_edit==1)?"checked":"")." />
		</div>
		<div class='fl-fix w-m'>
			<input type='checkbox' class='chkallow bigcheckbox' data-keyid='allow_create' ".(($allow_create==1)?"checked":"")." />
		</div>
		<div class='fl-fix w-m'>
			<input type='checkbox' class='chkallow bigcheckbox' data-keyid='allow_delete' ".(($allow_delete==1)?"checked":"")." />
		</div>
	</div>";

	return $sTemp;
}
function getProjAuthList($s_id,$s_name,$proj_id,$allow_view,$allow_enroll,$allow_schedule,$allow_data,$allow_data_log,$allow_lab,$allow_export,$allow_query,$allow_delete,$allow_data_backdate,$allow_admin ){
	$sTemp="<div class='fl-wrap-row row-hover fs-s fl-mid h-s row-color data-row' data-projid='$proj_id' data-sid='$s_id' style='line-height:15px'>
		<div class='fl-fix w-s'>
			<i class='fabtn fas fa-edit fa-2x btnedit' ></i>
		</div>
		<div class='fl-wrap-col w-m'>
			<div class='fl-fill showinput' data-keyid='s_id'>$s_id</div>
			<div class='fl-fill fs-xs showinput' data-keyid='s_name' >$s_name </div>
		</div>
		<div class='fl-fill showinput' data-keyid='proj_id'>
			$proj_id
		</div>
		<div class='fl-fix w-s' data-keyid='allow_view'>
			".(($allow_view)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_enroll' >
			".(($allow_enroll)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_schedule' >
			".(($allow_schedule)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_data' >
			".(($allow_data)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_data_log' >
			".(($allow_data_log)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_lab' >
			".(($allow_lab)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_export' >
			".(($allow_export)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_query' >
			".(($allow_query)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_delete' >
			".(($allow_delete)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_data_backdate' >
			".(($allow_data_backdate)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-s  showinput' data-keyid='allow_admin' >
			".(($allow_admin)?"<i class='fas fa-check-circle'></i>":"")."
		</div>
		<div class='fl-fix w-m action-control'>
			<span style='color:red;margin-left:20px'><i class='fabtn fas fa-trash-alt btndelete'></i></span>
			<i class='fabtn fas fa-spinner fa-spin action-loader' style='display:none'></i>
		</div>
		</div>";
		return $sTemp;
}

function getRoomListRow($room_no,$room_detail,$s_id,$s_name,$room_icon=""){
	$sTemp="<div class='fl-wrap-row h-ss fl-mid data-row row-hover row-color' data-roomno='$room_no' data-sid='$s_id'>

		<div class='fl-fix w-s'>
			<i class='fabtn-btn fas fa-caret-right btnenterroom roundcorner'>$room_no</i><i class='fa fa-spinner fa-spin btnenterroom-loader' style='display:none'></i>
		</div>
		<div class='fl-fix w-30 fl-mid'>
			<i class='$room_icon'></i>
		</div>
		<div class='fl-fill fs-small'>
			$room_detail
		</div>
		<div class='fl-fill fs-small al-left'>";
	if($s_name!=""){
		$sTemp.="<input type='checkbox' class='bigcheckbox chkconfirm' title='Overwrite the current user\r\nเข้าห้องทับคนเดิม' />";
	}
	$sTemp.= $s_name."
		</div>
	</div>";

	return $sTemp;
}

function getQueueRow($sUid,$sQueue){
	return $sTemp;
}

function getSupplyMasterRow($supply_group_code,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$dose_day,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$dose_note,$supply_status,$supply_group_type,$sub_supply_name=""){
	$isSysAdmin = getSS("sysadmin");
	$isView = getPerm("STOCK",$supply_group_type,"view");
	$isInsert = getPerm("STOCK",$supply_group_type,"insert");
	$isUpdate = getPerm("STOCK",$supply_group_type,"update");
	$isDelete = getPerm("STOCK",$supply_group_type,"delete");

	$sHtml="	<div class='fl-wrap-row h-50 row-color fs-smaller row-data row-hover' data-code='$supply_code'>
		<div class='fl-wrap-col w-80 fl-mid'>";
		if($isView)
			$sHtml.="<i class='fabtn btneditsupmaster far fa-edit fa-2x'></i>";
		$sHtml.="</div>
		<div class='fl-wrap-col w-150 lh-25'>
			<div><span class='fw-b'>$supply_code </span>$supply_group_name </div>
			<div class='supply_code' style='display:none'>$supply_code</div>
		</div>
		<div class='fl-wrap-col lh-15'>
			<div class='fl-fix h-25 supply_name fw-b'>$supply_name</div>
			<div class='fl-wrap-row'>";

	if(getPerm("STOCK",$supply_group_type,"admin")) $sHtml.="<div class='fl-fix w-20 fabtn btneditmasterexc fl-mid'><i class='fa fa-exchange-alt fa-lg'></i></div>";


	$sHtml.="<div class='fl-fill fl-vmid sub-supply-name'>$sub_supply_name</div>
	</div>
		</div>
		<div class='fl-fix w-100'>
			<div >".(($supply_unit=="")?"...":$supply_unit)."</div>
			<div >$dose_day/$dose_per_time</div>
		</div>
		<div class='fl-fix w-150'>
			<div>".(($dose_before=="B")?"Before":(($dose_before=="A")?"After":"..."))."</div>
			<div>
			".(($dose_breakfast)?"เช้า ":"")."
			".(($dose_lunch)?"เที่ยง ":"")."
			".(($dose_dinner)?"เย็น ":"")."
			".(($dose_night)?"ก่อนนอน":"")."
			</div>
		</div>
		<div class='fl-wrap-col' style='line-height:12px'>
			<div class='fl-fix h-25'>$dose_note</div>
			<div class='fl-fix h-25' style='color:grey' >$supply_desc</div>
		</div>
		<div class='fl-fix w-80 fl-mid' style='
			".(($supply_status)?"color:green'><i class='fas fa-check-circle fa-2x'></i>":"color:red'><i class='fas fa-ban'></i>")."
		</div>
		<div class='fl-fix w-50 fl-mid ' style='color:red'>
		";
		if($isDelete)
			$sHtml.="<i class='fabtn fas btndeletesupply fa-trash-alt fa-2x'></i><i class='fas btn-loader fa-spinner fa-2x fa-spin' style='display:none'></i>";
		$sHtml.="
		</div>
	</div>";
	return $sHtml;
}

function getSupplySearchRow($showAmt,$is_service,$supply_group_type,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$stock_lot,$stock_amt,$stock_exp_date,$bulk_unit="",$convert_amt=""){
	//<div class='fl-fix w-20 fl-mid'><input type='radio' value='$supply_code' /></div>
	if($stock_amt==null || $stock_amt=="") $stock_amt=0;
	$sHtml = "<div class='fl-wrap-row h-30 row-color row-hover lh-30 data-row fabtn' data-supcode='$supply_code' data-amt='$stock_amt' data-stklot='$stock_lot' data-gtype='$supply_group_type' data-isservice='$is_service' data-bulkunit='$bulk_unit' data-convamt='$convert_amt'>
		<div class='fl-fix w-110 lh-15 fs-xsmall' data-keyid='supply_code'><span class='fw-b '>$supply_group_name</span><br/>$supply_code</div>
		<div class='fl-fill lh-15 al-left fs-small' data-keyid='supply_name'>$supply_name</div>";
		if($showAmt=="1"){
			$sHtml.="<div class='fl-fix w-100 fl-mid lh-15'>$stock_lot<br/>$stock_exp_date</div>
				<div class='fl-fix w-100 fl-mid'>$stock_amt</div>";
		}

	$sHtml.="<div class='fl-fix w-100 fw-b' data-keyid='supply_unit'>$supply_unit</div>
		<div class='fl-fill' >$supply_desc</div>
	</div>
	";
	return $sHtml;
}


function getRequestItemShowRow($request_id,$request_item_no,$updated_by,$updated_date,$supply_code,$request_item_show,$request_supply_note,$request_amt,$request_exact_amt,$discount_before_vat,$discount_before_vat_baht,$request_vat,$discount_after_vat,$discount_after_vat_baht,$request_item_price,$request_item_price_discount,$request_item_price_vat,$request_item_price_final,$request_total_price,$request_total_price_discount,$request_total_price_vat,$request_total_price_final,$request_item_status,$request_project,$request_account,$request_unit,$convert_amt){
	$sHtml="<div class='fl-wrap-row row-color supply-row h-40 row-hover' data-rowno='$request_item_no' data-supcode='$supply_code'>
			<div class='fl-fix w-30 fl-mid'>
				<input class='chkitemshow bigcheckbox' title='แสดงใน PR' type='checkbox' ".(($request_item_show=="1")?"checked='true'":"")." />
			</div>
			<div class='fl-wrap-col'>
				<div class='fl-wrap-row fl-vmid'>
					$supply_code
				</div>
				<div class='lh-20 fl-fill al-left'>
					$request_supply_note
				</div>
			</div>
			<div class='fl-wrap-col w-70 pr-qty'>
				<div class='fl-fill lh-20 request-amt'>
					$request_amt
				</div>
				<div class='lh-20 fl-fill'>
					$request_exact_amt
				</div>
			</div>

			<div class='fl-fix w-70 fl-mid lh-50'>
				$request_unit
			</div>

			<div class='fl-wrap-col w-70'>
				<div class='fl-fill fl-mid request-price' title='ราคาต่อหน่วย ไม่มี Vat ไม่รวมส่วนลด&#013;'>
					$request_item_price
				</div>
				<div class='fl-fill pr-discount fl-mid'>
					$request_item_price_discount
				</div>
			</div>

			<div class='fl-wrap-col w-80 '>
				<div class='fl-wrap-row h-20 fl-mid pr-vat'>
					$request_item_price_vat
				</div>
				<div class='fl-wrap-row h-20 fl-mid pr-final'>
					$request_item_price_final
				</div>
			</div>

			<div class='fl-wrap-col w-80 pr-discount'>
				<div class='fl-wrap-row h-20 fl-mid'>
					$discount_before_vat
				</div>
				<div class='fl-wrap-row h-20 fl-mid'>
					$discount_before_vat_baht
				</div>
			</div>

			<div class='fl-wrap-col w-80 pr-vat'>
				<div class='fl-wrap-row h-20 fl-mid'>
					$discount_after_vat
				</div>
				<div class='fl-wrap-row h-20 fl-mid'>
					$discount_after_vat_baht
				</div>
			</div>
			<div class='fl-wrap-col w-70'>
				<div class='h-20 fl-fill fl-mid total-price'>
					$request_total_price
				</div>
				<div class='h-20 fl-fill pr-discount fl-mid total-price-discount'>
					$request_total_price_discount
				</div>
			</div>
			<div class='fl-wrap-col w-70'>
				<div class='h-20 fl-fill pr-vat fl-mid total-price-vat'>
					$request_total_price_vat
				</div>
				<div class='h-20 fl-fill pr-final fl-mid total-price-final'>
					$request_total_price_final
				</div>
			</div>
			<div class='lh-15 fl-fix w-90  fl-mid'>
				$request_project
			</div>
			<div class='lh-15 fl-fix w-90 fl-mid'>
				$request_account
			</div>
			<div class='btndelete fabtn fl-fix fl-mid h-40 w-30' style='color:red'><i class=' fa fa-trash-alt fa-2x'></i>
			</div>
			<div class='btndelete-loader fl-fix fl-mid h-40 w-30' style='display:none'><i class=' fa fa-spinner fa-spin fa-2x'></i>
			</div>
			<div class='fl-wrap-col w-20' style='color:green'>
				
			</div>
		</div>";
	return $sHtml;
}

function getRequestItemRow($request_id,$request_item_no,$supply_code,$request_supply_note,$request_amt,$request_item_price,$request_total_price,$request_item_status,$supply_name,$supply_unit,$show_delete=false){

	$itemStatus = ""; $statusColor="";
	if($request_item_status=="1"){
		$itemStatus = "<i class='fas fa-hourglass-start'></i>";
		$statusColor="orange";
	}else if($request_item_status=="FIN") {
		$itemStatus = "<i class='fas fa-cubes fa-lg'></i>";
		$statusColor="green";
	}else if($request_item_status=="0") {
		$itemStatus = "<i class='fas fa-trash-alt fabtn btndelete fa-lg'></i>";
		$statusColor="red";
	}

	$sHtml = "<div class='fl-wrap-row h-30 lh-20 fl-mid data-row row-color row-hover' data-supcode='$supply_code' data-reqid='$request_id'>
	<div class='fl-fix w-50 fl-mid' style='color:$statusColor'>
		$itemStatus
	</div>
	<div class='fl-fix w-110'>
		$supply_code
	</div>
	<div class='fl-fill lh-15'>
		$request_supply_note
	</div>
	<div class='fl-fix w-80 al-right'>
		$request_amt
	</div>
	<div class='fl-fix w-100 fl-mid'>
		$supply_unit
	</div>
	<div class='fl-fix w-80 al-right'>
		$request_item_price
	</div>
	<div class='fl-fix w-80 al-right'>
		$request_total_price
	</div>
	<div class='fl-fix w-100'>

	</div>
	<div class='fl-fix w-100'>

	</div>
	<div class='fl-fix w-50 fl-mid' style='color:red'>
		".(($show_delete && $request_item_status!="0")?"<i class='btndelete fabtn fa fa-trash-alt fa-lg'></i>":"")."
	</div>
	</div>";
	return $sHtml;
}

function getSaleOptionRow($sale_opt_id,$sale_opt_name,$data_seq,$is_enable){
	$sOpt = "<input type='checkbox' class='bigcheckbox is_enable' onclick='return false;' ".(($is_enable)?"checked":"")."  />";
	$sHtml="<div class='fl-wrap-row row-color-2 saleoption-row h-30 lh-30 row-hover' data-saleid='$sale_opt_id'>
		<div class='fl-fix w-50 fl-mid'><i class='btnsaleoptionedit fabtn fa fa-edit fa-lg fl-mid'></i><i class='saleoption-load fa fa-spinner fa-spin fa-lg' style='display:none'></i></div>
		<div class='fl-fix w-100 fl-mid data_seq'>$data_seq</div>
		<div class='fl-fix w-100 fl-mid sale_opt_id'>$sale_opt_id</div>
		<div class='fl-fill sale_opt_name'>$sale_opt_name</div>
		<div class='fl-fix w-100 fl-mid'>$sOpt</div>
		<div class='fl-fix w-200 fl-mid'></div>
	</div>";
	//<span class='fc-red'><i class='fabtn btnsaleoptiondel fa fa-trash-alt fa-lg'></i></span>
	return $sHtml;
}
?>
