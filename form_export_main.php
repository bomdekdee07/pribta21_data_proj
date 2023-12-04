<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
$sSID = getSS("s_id");
$sSessKey = getSS("sesskey");
$sHtml="";
if($sSID==""){
	echo("Session Expire please login and try again. <button class='btnquicklogin'>Login</button>");
}else{
	$isAdmin=0;
	//Check permission
	$query = "SELECT ISC.section_id,is_admin FROM i_staff_clinic ISC LEFT JOIN i_section_permission ISP ON ISP.section_id = ISC.section_id WHERE s_id=? AND page_id='exp_form_data' AND is_admin=1 AND sc_status=1 AND page_allow=1";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sSID);

	if($stmt->execute()){
	  $stmt->bind_result($section_id,$is_admin);
	  while ($stmt->fetch()) {
	  	$isAdmin = 1;
	  }
	}

	/*
	$query ="SELECT IFP.form_id,allow_view,allow_edit,allow_export,form_name_en,form_name_th,form_desc,form_version_id
	FROM i_form_permission IFP
	LEFT JOIN p_form_list PFL
	ON PFL.form_id = IFP.form_id

	WHERE s_id = ? AND IFP.start_date < NOW() AND (stop_date > NOW() OR stop_date = '0000-00-00')";
	*/

	if($isAdmin==1){
		$query ="SELECT form_id,'1','1','1',form_name_en,form_name_th,form_desc,form_version_id,is_log
	FROM p_form_list";

	}else{
		$query ="SELECT IFP.form_id,allow_view,allow_edit,allow_export,form_name_en,form_name_th,form_desc,form_version_id,is_log
		FROM i_form_permission IFP
		LEFT JOIN p_form_list PFL
		ON PFL.form_id = IFP.form_id

		WHERE section_id IN (
			SELECT section_id FROM i_staff_clinic WHERE s_id=?
		) AND IFP.start_date < NOW() AND (stop_date > NOW() OR stop_date = '0000-00-00')";
	}

	$stmt = $mysqli->prepare($query);

	if($isAdmin == 0)
	$stmt->bind_param("s",$sSID); 

	if($stmt->execute()){
	  $stmt->bind_result($form_id,$allow_view,$allow_edit,$allow_export,$form_name_en,$form_name_th,$form_desc,$form_version_id,$is_log);
	  while ($stmt->fetch()) {
	  	if($allow_export==1){
	  		$sHtml .= "<div class='fl-wrap-row row-color form-row h-40 row-hover'>
				<div class='fl-fix w-50 fl-mid'>
					<input type='checkbox' class='chkformid bigcheckbox' value='".$form_id."'  data-islog='".$is_log."' />
				</div>
				<div class='fl-fix w-80 fl-mid btnformpreview fabtn' data-formid='".$form_id."' title='Preview Form'><i class=' fas fa-eye fa-lg' ></i></div>
				<div class='fl-fix w-80 fl-mid'>".(($is_log)?"<i class='fas fa-table' title='This form is log/table type'></i>":"")."</div>

				<div class=' fl-wrap-col form-name'>
					<div class='fl-fill lh-20 al-left fw-b'>".$form_name_en."</div>
					<div class='fl-fill lh-20 al-left'>".$form_name_th."</div>
				</div>
				<div class='fl-fix lh-20 w-300 al-left fs-smaller'>".$form_id."</div>
				<div class='fl-fix w-100'>".$form_version_id."</div>
			</div>";
	  	}

	  }
	}
	$mysqli->close();
}


?>
<style>

	.txtdate{
		width:80px;
	}

</style>
<div id='divFEM' class='fl-wrap-col' style='overflow: hidden;background-color: white'>
	<div class='fl-fix' style='max-height: 30px'>
		Date <input id='txtStrDate' class='txtdate' placeholder="yyyy-mm-dd" maxlength="10"  readonly="true" style='font-size:12px' /> to <input id='txtStpDate' readonly="true"  placeholder="yyyy-mm-dd" maxlength="10"  class='txtdate' style='font-size:12px' /> <button id='btnExport'>EXPORT</button> | <button id='btnExpPINFO'>PATIENT INFO</button> | <i id='btnExpDataDict' class="fabtn fas fa-spell-check fa-lg"></i>
	</div>
	<div class='fl-wrap-col' style=''>
		<div class='fl-wrap-row bg-head-1 h-30 row-header'>
			<div class='fl-fix w-50 fl-mid'>
				<input id='chkAll' type='checkbox' class='bigcheckbox' title='Check All' />#
			</div>
			<div class='fl-fix w-80 fl-mid'>Preview</div>
			<div class='fl-fix w-80 fl-mid'>Log</div>
			<div class='fl-fix w-150 fl-vmid'>Name EN/TH</div>
			<div class='fl-fill al-left fl-mid'><input id='txtSearchName' class='h-20 w-fill' /></div>
			<div class='fl-fix w-30 fl-mid'><i class='fa fa-search fa-lg'></i></div>
			<div class='fl-fix w-300 fl-mid'>ID</div>
			<div class='fl-fix w-100 fl-mid'>Version</div>
		</div>
		<div id='expFormList' class='fl-wrap-col fl-scroll fs-smaller'><? echo($sHtml); ?></div>


		<form id='formDownload' method="POST" target="_blank" action='form_a_export.php' >
			<input name='formlist' type='hidden' />
			<input name='loglist' type='hidden' />
			<input name='mode' value='export_xls' type='hidden' />
			<input name='strdate' type='hidden' />
			<input name='stpdate' type='hidden' />
		</form>
	</div>
</div>

<script>
	$(document).ready(function(){

		$(".txtdate").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

		$(".btnformpreview").off("click");
		$(".btnformpreview").on("click",function(){
			sFormId = $(this).attr("data-formid");
			sUrl = "form_inc_preview.php?formid="+sFormId;

			showDialog(sUrl,sFormId+" Preview","600","700","","",false,"");


		});


		function validateDate(){
			let strDate = $("#txtStrDate").val();
			let stpDate = $("#txtStpDate").val();

			if(strDate == "" || stpDate==""){
				$("#txtStrDate").notify("Start date or Stop Date is blank.");
				return false;
			}else if(stpDate < strDate){
				$("#txtStrDate").notify("Start date more than stop date.");
				return false;
			}else{
				return true;
			}
		}

		$("#chkAll").off("change");
		$("#chkAll").on("change",function(){
			if($(this).is(":checked")){
				$(".chkformid:visible").attr("checked",true);
			}else{
				$(".chkformid:visible").attr("checked",false);
			}
		});

		$("#divFEM #txtSearchName").off("keyup");
		$("#divFEM #txtSearchName").on("keyup",function(e){
			sVal=$(this).val();
			$("#expFormList .form-row").hide();
			$("#expFormList .form-name:Contains('"+sVal+"')" ).closest(".form-row").show();
		});

		$("#btnExpPINFO").off("click");
		$("#btnExpPINFO").on("click",function(){
				let strDate = $("#txtStrDate").val();
				let stpDate = $("#txtStpDate").val();
				$("#formDownload").find("input[name='formlist']").val("");
				$("#formDownload").find("input[name='strdate']").val(strDate);
				$("#formDownload").find("input[name='stpdate']").val(stpDate);
				$("#formDownload").find("input[name='mode']").val("pinfo_xls");
				$("#formDownload").submit();
		});

		$("#btnExport").off("click");
		$("#btnExport").on("click",function(){
			var sFormList = "";
			var sLogList = "";
			if(validateDate()==false) return;
			$(".chkformid").each(function(ix,objx){
				if($(objx).is(":checked")){
					let tempForm = $(objx).val();
					let tempLog = $(objx).data("islog");
					sFormList += ((tempForm=="")?"":",")+tempForm;
					sLogList += ((tempLog=="")?"":",")+tempLog;
				}
			});
			if(sFormList!=""){
				let strDate = $("#txtStrDate").val();
				let stpDate = $("#txtStpDate").val();
				$("#formDownload").find("input[name='mode']").val("export_xls");
				$("#formDownload").find("input[name='formlist']").val(sFormList.substr(1));
				$("#formDownload").find("input[name='strdate']").val(strDate);
				$("#formDownload").find("input[name='stpdate']").val(stpDate);
				$("#formDownload").find("input[name='loglist']").val(sLogList);

				$("#formDownload").submit();
			}else{
				$.notify("Form is not selected.");
			}
		});

		$("#btnExpDataDict").off("click");
		$("#btnExpDataDict").on("click",function(){
			var sFormList = "";

			$(".chkformid").each(function(ix,objx){
				if($(objx).is(":checked")){
					let tempForm = $(objx).val();
					sFormList += ((tempForm=="")?"":",")+tempForm;
				}
			});
			if(sFormList!=""){
				$("#formDownload").find("input[name='mode']").val("export_datadict");
				$("#formDownload").find("input[name='formlist']").val(sFormList);
				$("#formDownload").submit();
			}else{
				$.notify("Form is not selected.");
			}
		});

	});

</script>
