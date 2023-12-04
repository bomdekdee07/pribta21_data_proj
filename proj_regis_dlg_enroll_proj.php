<?
/* Project Register Main */

include("in_session.php");
include_once("in_php_function.php");
$sUID = getQS("uid");
$sUIC = getQS("uic");
$sProjid = getQS("projid");
$sEnroll = getQS("enroll");
$attrData = " data-uid='$sUID' data-uic='$sUIC' data-projid='$sProjid' data-enroll='$sEnroll' "
?>

	<div class='fl-wrap-col bg-msoft1 pw400' id='div_pid_enroll' <? echo $attrData; ?> >
		<div class='fl-wrap-row fl-mid ph50 bg-mdark1 ptxt-b ptxt-s20 ptxt-white'>
			<div class='fl-wrap-col pw400'>
			UID: <? echo $sUID; ?> | UIC: <? echo $sUIC; ?>
		  </div>
	  </div>
		<div class='fl-wrap-row v-mid ph50 ptxt-b ptxt-s14'>
				<div class='fl-fix pw100 palign-right'>
					Project:
				</div>
				<div class='fl-fix pw300'>
					<SELECT id='ddlProjid'>
						<option value=''>-เลือก | Select-</option>
						<? include("project_opt_proj_list.php");?>
					</SELECT>
				</div>
		</div>
		<div class='fl-wrap-row v-mid ph50 ptxt-b ptxt-s14'>
			<div class='fl-fix pw100 palign-right'>
				Group:
			</div>
			<div class='fl-fix pw300'>
				<SELECT id='ddlGroupid'></SELECT>
			</div>
		</div>
		<div class="fl-wrap-row h-30 fw-b font-s-1 project-link-pid" style="display: none;">
			<div class="fl-fix w-100 fl-mid-right">PID Link:</div>
			<div class="fl-fix w-300 fl-mid-left">
				<input type="text" name="pid_project_main" class="h-25 w-300 fw-b" style="background-color: #EFEEEB;" readonly value=" 
		            not found data.">
			</div>
		</div>
		<div class='fl-wrap-row ph50 bg-msoft3 palign-center'>
			<button class = 'pbtn btn-regis-pid p-btnvisit'><i class='fa fa-user-check fa-lg'></i> ลงทะเบียน | Register</button>
		</div>
	</div>



<script>
$(document).ready(function(){
  //setDlgResult("");
  $("#ddlProjid").unbind("change");
	$("#ddlProjid").bind("change",function(){
    	let sId = $(this).val();
		let sUrl = "project_opt_group_list.php?projid="+sId;
		//console.log("change proj "+sUrl);
		loadLink(sUrl, $("#ddlGroupid"),$("#ddlGroupid").next(".fa-spinner") );
		
		// FIX Custom code for project HCV
		var val_select = $(this).val();
		var  sUID = $("#div_pid_enroll").attr('data-uid');

		var aData = {
			uid: sUID,
			proj_id: "KP_led_HCV_ENRO"
		}
		// console.log(aData);
		
		if(val_select == "KP_Led_HCV"){
			$(".project-link-pid").show();
			$.ajax({
				url:"proj_regis_dlg_enroll_get_pid_proj_ajax.php",
				method: "POST",
				cache: "false",
				data: aData,
				success: function(sResult){
					if(sResult != ""){
						$("[name=pid_project_main]").val(sResult);
					}
					else{
						$("#div_pid_enroll .p-btnvisit").hide();
					}
				}
			});
			
		}
		else{
			$(".project-link-pid").hide();
			$("[name=pid_project_main]").val("");
		}
	});

	$(".btn-regis-pid").off("click");
	$(".btn-regis-pid").bind("click",function(){
		let objX = $(this).closest('#div_pid_enroll');
		let sUID = $(objX).attr('data-uid');
		let sEnroll = $(objX).attr('data-enroll');

		let objThis = $(this);
		if($('#ddlProjid').val() == '') {
			$('#ddlProjid').notify('Please select project.', 'error');
			return;
		}
		else if ($('#ddlGroupid').val() == '') {
			$('#ddlGroupid').notify('Please select group.', 'error');
			return;
		}

		var val_select_proj = $("#ddlProjid").val();
		var val_pid_link = $("[name=pid_project_main]").val();
		var new_pid = "";
		if(val_select_proj = "KP_Led_HCV"){
			new_pid = val_pid_link;
		}

		var aData = {
				uid:sUID,
				enroll:sEnroll,
				projid: $(objX).find("#ddlProjid").val(), 
				groupid: $(objX).find("#ddlGroupid").val(),
				new_pid: new_pid
		};
		console.log("pid:"+aData);

		if(confirm("ต้องการลงทะเบียน "+aData.uid+ "เข้าใน "+aData.projid+" / "+aData.groupid)){
			$.ajax({
				url: "proj_regis_ajax_check.php",
				method: "POST",
				case: false,
				data: aData,
				success: function(gResult){
					if(gResult.split(",")[0] == "1"){
						alert("มี UID นี้ใน Project นี้แล้วใน group "+gResult.split(",")[1]);
					}
					else{
						callAjax("proj_regis_a_new_pid.php",aData,function(rtnObj,aData){
							//endLoad(btnclick, btnclick.next(".fa-spinner"));
							if(rtnObj.res == 1){
								alert("ลงทะเบียนเข้าโครงการ PID: "+rtnObj.pid );
								//setDlgResult(aData.uid+":"+aData.projid+":"+aData.groupid);
								closeDlg(objThis, aData.uid+":"+aData.projid+":"+aData.groupid);
							}
							else{
								$(".btn-regis-pid").notify("Fail to register new PID.", "error");
								if(rtnObj.msg_info) $.notify(rtnObj.msg_info, 'info');
								if(rtnObj.msg_err) $.notify(rtnObj.msg_err, 'error');
							}

							if(rtnObj.msg_info != "")
							alert(rtnObj.msg_info);
							//$(".btn-regis-pid").notify(rtnObj.msg_info, "info");

							if(rtnObj.msg_err != "")
							alert(rtnObj.msg_err);
							//$(".btn-regis-pid").notify(rtnObj.msg_err, "error");
						});// call ajax
					}
				}
			});
		}//confirm

	});

<?
  if($sProjid != ""){
		echo "$('#ddlProjid').val('$sProjid'); $('#ddlProjid').change(); ";
	}
?>



});





</script>
