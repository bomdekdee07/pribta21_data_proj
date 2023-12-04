<?
/* Project Register Main */

include("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS("projid");
$is_anonymous = getQS("is_anonymous");
?>



	<div class='div-custom-pid fl-wrap-col bg-msoft1 ptxt-s12' data-projid='<? echo $sProjid; ?>'
		style='min-width:300px; max-width:300px;'>
		<div class='fl-wrap-row ph50 bg-mdark1 ptxt-b ptxt-white'>
			<div class='fl-wrap-col pw300'>
				<div class='fl-fix ph20 '>
				  UID Type:
				</div>
				<div class='fl-wrap-row ph30 ptxt-s10'>
					<div class='fl-fix pw100 '>
						<label><INPUT type='radio' name='uid_type' value='uid' checked /> UID</label>
					</div>
					<div class='fl-fix pw200 div-anonymous'>
						<label><INPUT type='radio' name='uid_type' value='anonymous' /> Make Anonymous UID</label>
					</div>
				</div>
		  </div>
		</div>
		<div class='fl-wrap-row ph50 bg-mdark3 ptxt-b ptxt-white div-txt-uid'>
			<div class='fl-wrap-col pw300 ' >
				<div class='fl-fix ph20'>
					UID:
				</div>
				<div class='fl-fix ph30'>
					<INPUT type='text' id='txtuid' placeholder='Insert UID' />
				</div>
			</div>
		</div>

		<div class='fl-wrap-row ph50 bg-mdark2 ptxt-b ptxt-white'>
			<div class='fl-wrap-col pw300'>
				<div class='fl-fix fl-mid ph20'>
					Custom PID:
				</div>
				<div class='fl-fix ph30'>
					<INPUT type='text' id='txtpid' placeholder='Insert custom pid' />
				</div>
			</div>
		</div>
		<div class='fl-wrap-row ph50 bg-mdark2 ptxt-b ptxt-white'>
			<div class='fl-wrap-col pw300'>
				<div class='fl-fix ph20'>
					Project:
				</div>
				<div class='fl-fix ph30'>
					<SELECT id='ddlProjid'>
						<option value=''>-เลือก | Select-</option>
						<? include("project_opt_proj_list.php");?>
					</SELECT>
				</div>
			</div>
		</div>
		<div class='fl-wrap-row ph50 bg-mdark2 ptxt-b ptxt-white'>
			<div class='fl-wrap-col pw300'>
				<div class='fl-fix ph20'>
					Group:
				</div>
				<div class='fl-fix ph30'>
					<SELECT id='ddlGroupid'>

					</SELECT>
				</div>
			</div>
		</div>

		<div class='fl-wrap-row ph50 bg-msoft3 palign-center'>
			<button class = 'pbtn btn-regis-pid p-btnvisit'><i class='fa fa-user-check fa-lg'></i> ลงทะเบียน | Register</button>
		</div>

	</div>



<script>

$(document).ready(function(){
  <?
     if(!$is_anonymous){
			 echo "$('.div-anonymous').hide();";
		 }
	?>

  setDlgResult("");
	$("INPUT[name='uid_type']").unbind("click");
	$("INPUT[name='uid_type']").bind("click",function(){
		let uid_type = $("INPUT[name='uid_type']:checked").val();
		//console.log('uid_type '+uid_type);
		if(uid_type == 'uid'){
			$('.div-txt-uid').show();
			$('.div-txt-uid').focus();
		}
		else{
			$('.div-txt-uid').hide();
			$('#txtuid').val('');
		}

	});

  $("#ddlProjid").unbind("change");
	$("#ddlProjid").bind("change",function(){
    let projid = $(this).val();
		let sUrl = "project_opt_group_list.php?projid="+projid;
		//console.log("change proj "+sUrl);
		loadLink(sUrl, $("#ddlGroupid"),$("#ddlGroupid").next(".fa-spinner") );

	});

	$(".btn-regis-pid").unbind("click");
	$(".btn-regis-pid").bind("click",function(){
		let uid_type = $("INPUT[name='uid_type']:checked").val();

    if($('#ddlProjid').val() == '') {
			$('#ddlProjid').notify('Please select project.', 'error');
			return;
		}
		else if ($('#ddlGroupid').val() == '') {
			$('#ddlGroupid').notify('Please select group.', 'error');
			return;
		}
		else if(uid_type == 'uid' && $('#txtuid').val()==''){
			$('#txtuid').notify('Please insert UID here.', 'info');
			return;
		}
		else if($('#txtpid').val()==''){
			$('#txtpid').notify('Please insert custom PID here.', 'info');
			return;
		}


		var aData = {
			  u_mode: "create_custom_pid",
				uid:$('#txtuid').val(),
				pid:$('#txtpid').val(),
				projid: $('#ddlProjid').val(),
				groupid: $('#ddlGroupid').val()
		};

		if(confirm("ต้องการลงทะเบียน "+aData.uid+ "เข้าใน "+aData.projid+" / "+aData.groupid)){

					callAjax("proj_regis_a.php",aData,function(rtnObj,aData){
				//		endLoad(btnclick, btnclick.next(".fa-spinner"));
						if(rtnObj.res == 1){
							alert("ลงทะเบียน "+rtnObj.uid+" เข้าโครงการ PID: "+aData.pid );

			        setDlgResult(rtnObj.uid+":"+aData.projid+":"+aData.groupid);
							closeDlg();

						}
						else if(rtnObj.res == 0){
							$(".btn-regis-pid").notify("Fail to register new PID.", "error");

						}

						if(rtnObj.msg_error != "")
						alert(rtnObj.msg_error);

					});// call ajax
		}//confirm

	});

<?
  if($sProjid != ""){
		echo "$('#ddlProjid').val('$sProjid'); $('#ddlProjid').change(); ";
	}
?>



});





</script>
