<?
include("in_session.php");
include_once("in_php_function.php");
$sSid = getQS("sid");

?>
<div id='divChangePwd' class='fl-wrap-col '>
	<div class='fl-wrap-col roundcorner f-border' style='margin:20px 20px;padding:10px'>
		<div class='fl-fix'>
			Current Password
		</div>
		<div class='fl-fill'>
			<input id='txtCurPass' class='h-ss' type='password' />
		</div>
		<div class='fl-fix h-xs'>
			รหัสใหม่/New Password
		</div>
		<div class='fl-fill' >
			<input id='txtNewPass' class='h-ss' type='password' />
		</div>
		<div class='fl-fix h-xs'>
			ยืนยันรหัสใหม่/Password Again
		</div>
		<div class='fl-fill'>
			<input id='txtConfPass' class='h-ss' type='password' />
		</div>
		<div class='fl-fill h-s'>
			<i id='btnChange' class='roundcorner f-border fabtn fa' style='padding:5px 10px;background-color: red;color:white'>Change Password</i>
			<i id='btnChange-loader' class='fas fa-spinner fa-spin fa-lg' style='display:none'></i>
			
		</div>
		<div class='fl-fill h-xs'>
			<span id='btnForgetPass' class='fabtn fs-xs'>Forgot Password</span>
		</div>
	</div>
</div>



<script>
	$(function(){
		$("#divChangePwd #btnChange").unbind("click");
		$("#divChangePwd #btnChange").on("click",function(){
			sPCur=$("#txtCurPass").val();
			sP1=$("#txtNewPass").val();
			sP2=$("#txtConfPass").val();

			if(sP1!=sP2 || sP1=="" || sPCur==""){
				$.notify("New password and confirm is not the same and can't be blank.\r\n รหัสใหม่ และ รหัสยืนยัน ไม่ตรงกัน และ ไม่สามารถว่างได้");
				return;
			}
			objThis = $(this);

			startLoad($("#divChangePwd #btnChange"),$("#divChangePwd #btnChange-loader"));
			var aData = {u_mode:"change_password",p1:sP1,p2:sP2,pcur:sPCur};
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					alert(rtnObj.msg);
					setDlgResult("0");
				}else if(rtnObj.res=="1"){
					setDlgResult("1");
					$.notify("Password Changed\r\n Dialog will close after 2 seconds","success");
					setTimeout(function(){
						closeDlg(objThis);
					},2000);
				}
				endLoad($("#divChangePwd #btnChange"),$("#divChangePwd #btnChange-loader"));
				//$("#btnBindQ").show();
			});
		});
	});

</script>