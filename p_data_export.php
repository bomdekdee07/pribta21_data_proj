<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
$sSID = getSS("s_id");
$sProjid = getQS("projid");
$sHtml="";
if($sSID==""){
	echo("Session Expire please login and try again. <button class='btnquicklogin'>Login</button>");
}else{
	$query ="SELECT PFL.form_id,allow_view,allow_data,allow_export,form_name_en,form_name_th,form_desc,protocol_version,is_log
	FROM p_form_list PFL
	JOIN p_staff_auth PSA ON 1=1
	JOIN i_project_protocol IPP
	ON IPP.proj_id=PSA.proj_id
	JOIN i_protocol_form IPF
	ON IPF.protocol_id = IPP.protocol_id
	AND IPF.form_id = PFL.form_id

	WHERE PSA.s_id=? AND PSA.proj_id=? AND allow_export=1";

  //echo "$sSID, $sProjid / $query";
  	$stmt = $mysqli->prepare($query);
  	$stmt->bind_param("ss",$sSID, $sProjid);

	if($stmt->execute()){
	  $stmt->bind_result($form_id,$allow_view,$allow_edit,$allow_export,$form_name_en,$form_name_th,$form_desc,$form_version_id,$is_log);
	  while ($stmt->fetch()) {
	  	if($allow_export==1){
	  		$sHtml .= "<div class='fl-wrap-row row-color h-40 row-hover'>
				<div class='fl-fix w-50 fl-mid'>
					<input type='checkbox' class='chkformid bigcheckbox' value='".$form_id."'  data-islog='".$is_log."' />
				</div>
				<div class='fl-fix w-80 fl-mid btnformpreview fabtn' data-formid='".$form_id."' title='Preview Form'><i class=' fas fa-eye fa-lg' ></i></div>
				<div class='fl-fix w-80 fl-mid'>".(($is_log)?"<i class='fas fa-table' title='This form is log/table type'></i>":"")."</div>
				<div class='fl-wrap-col'>
					<div class='fl-fill lh-20 al-left'>".$form_name_en."</div>
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
<div class='fl-wrap-col' style='overflow: hidden;background-color: white'>
	<div class='fl-wrap-row ptxt-s10 ptxt-b' style='max-height: 30px'>
		<div  class='fl-fix pw250'>
			Date <input id='txtStrDate' class='txtdate' placeholder="yyyy-mm-dd" maxlength="10"  readonly="true" style='font-size:12px' /> to <input id='txtStpDate' readonly="true"  placeholder="yyyy-mm-dd" maxlength="10"  class='txtdate' style='font-size:12px' />
		</div>
		<div class="fl-fix w-45 fl-mid-left">Visit ID</div>
		<div class="fl-fix w-105 fl-mid-left multiselect-select-bom selectBox-select-bom" onclick="showCheckboxes()">
			<select name='visit_id' data-id='visit_id' style="width: 99px;">
				<option value=''>All</option>
			</select>
			<div class="overSelect-select-bom"></div>
		</div>

		<div  class='fl-fix pw150 fl-mid pbtn pbtn-blue mr-1' id='btnExport' >
		DATA EXPORT
		</div>
			<div  class='fl-fix pw150 fl-mid pbtn pbtn-blue' id='btnExpDataDict' >
				<i class="fas fa-spell-check fa-lg"></i> Data Dictionary
			</div>
			<div  class='fl-fill'  >
			</div>
		<div  class='fl-fix pw150 fl-mid pbtn bg-mdark1 ptxt-white mr-1' id='btnExpVisit' >
		PROJECT VISIT
		</div>
		<div  class='fl-fix pw150 fl-mid pbtn bg-mdark2 ptxt-white mr-1' id='btnExpPINFO' >
		PATIENT INFO
		</div>
	</div>
	<div class="fl-wrap-row h-80" id="checkboxes_detail_bom" style="display:none;">
		<div class="fl-fix w-280"></div>
		<div class="fl-fix w-120 fl-mid-left fl-auto font-s-1" id="checkboxes_select_bom">
			<div class="fl-wrap-row h-5"></div>
			<? include_once("p_data_export_visitid_list.php") ?>
		</div>
	</div>
	<div class='fl-wrap-col' >
		<div class='fl-wrap-row bg-head-1 h-30 row-header'>
			<div class='fl-fix w-50'>
				#<input id='chkAll' type='checkbox' title='Check All' />
			</div>
			<div class='fl-fix w-80 fl-mid'>Preview</div>
			<div class='fl-fix w-80 fl-mid'>Log</div>
			<div class='fl-fill al-left'>Form Name EN/TH</div>
			<div class='fl-fix w-300 fl-mid'>ID</div>
			<div class='fl-fix w-100 fl-mid'>Version</div>
		</div>
		<div id='expFormList' class='fl-wrap-col fl-auto fl-scroll fs-smaller fw-b'><? echo($sHtml); ?></div>

		<form id='formDownload' method="POST" target="_blank" action='p_data_export_a.php' >
			<input name='formlist' type='hidden' />
			<input name='mode' value='export_xls' type='hidden' />
			<input name='strdate' type='hidden' />
			<input name='stpdate' type='hidden' />
			<input name='visitid' type='hidden' />
			<input name='loglist' type='hidden' />
      <input name='projid' type='hidden' value='<? echo $sProjid; ?>' />
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

		$(".btnformpreview").unbind("click");
		$(".btnformpreview").on("click",function(){
			sFormId = $(this).attr("data-formid");
			sUrl = "form_inc_preview.php?formid="+sFormId;
			let screen_width = screen.width;
			showDialog(sUrl,sFormId+"Form Preview: "+sFormId,"600",screen_width.toString(),"","",false,"");
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

		$("#chkAll").unbind("change");
		$("#chkAll").on("change",function(){
			if($(this).is(":checked")){
				$(".chkformid").attr("checked",true);
			}else{
				$(".chkformid").attr("checked",false);
			}
		});


		$("#btnExpPINFO").unbind("click");
		$("#btnExpPINFO").on("click",function(){
				let strDate = $("#txtStrDate").val();
				let stpDate = $("#txtStpDate").val();
				$("#formDownload").find("input[name='formlist']").val("");
				$("#formDownload").find("input[name='strdate']").val(strDate);
				$("#formDownload").find("input[name='stpdate']").val(stpDate);
				$("#formDownload").find("input[name='mode']").val("pinfo_xls");
				$("#formDownload").submit();
		});


		$("#btnExpVisit").unbind("click");
		$("#btnExpVisit").on("click",function(){
				let strDate = $("#txtStrDate").val();
				let stpDate = $("#txtStpDate").val();
				$("#formDownload").find("input[name='formlist']").val("");
				$("#formDownload").find("input[name='strdate']").val(strDate);
				$("#formDownload").find("input[name='stpdate']").val(stpDate);
				$("#formDownload").find("input[name='mode']").val("projvisit_xls");
				$("#formDownload").submit();
		});


		$("#btnExport").unbind("click");
		$("#btnExport").on("click",function(){
			var sFormList = ""; var sLogList = "";
			if(validateDate()==false) return;
			$(".chkformid").each(function(ix,objx){
				if($(objx).is(":checked")){
					isLog = $(objx).attr("data-islog");
					let tempForm = $(objx).val();

					if(isLog=="1") {
						sLogList += ((sLogList=="")?"":",")+tempForm;
					}else{
						sFormList += ((tempForm=="")?"":",")+tempForm;
					}

				}
			});
			if(sFormList!="" || sLogList!=""){
				let strDate = $("#txtStrDate").val();
				let stpDate = $("#txtStpDate").val();
				// var svisit_id = $("[name=visit_id]").val();
				var svisit_id = "";
				$(".visit-list-all").filter(":checked").each(function(){
					svisit_id += $(this).val()+",";
				});
				// console.log(svisit_id);

				$("#formDownload").find("input[name='mode']").val("export_xls");
				$("#formDownload").find("input[name='formlist']").val(sFormList);
				$("#formDownload").find("input[name='strdate']").val(strDate);
				$("#formDownload").find("input[name='stpdate']").val(stpDate);
				$("#formDownload").find("input[name='loglist']").val(sLogList);
				$("#formDownload").find("input[name='visitid']").val(svisit_id);

				$("#formDownload").submit();
			}else{
				$.notify("Form is not selected.");
			}
		});

		$("#btnExpDataDict").unbind("click");
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

	var expanded = false;
	function showCheckboxes() {
		var checkboxes = document.getElementById("checkboxes_select_bom");
		if (!expanded) {
			checkboxes.style.display = "block";
			expanded = true;
			$("#checkboxes_detail_bom").show();
		} else {
			checkboxes.style.display = "none";
			expanded = false;
			$("#checkboxes_detail_bom").hide();
		}
	}

</script>
