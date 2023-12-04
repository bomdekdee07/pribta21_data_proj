<?
include("in_session.php");
include_once("in_php_function.php");
$sSid = getQS("sid");

?>

<div class='fl-wrap-col' style=';justify-content: center;'>
	<div class='fl-fix roundcorner' style='display:inline-block;height:90%;border:1px solid lightgrey;margin:0px auto;padding:20px 20px;background-color: white'>

		<div class="card-title">Reset Password</div>
		<div class='txtleft'><label class="">User</label></div>
		<div><input id='txtSID' readonly value='<? echo($sSid); ?>' /></div>
		<div class='txtleft'><label class="">New Password</label></div>
		<div><input type="password" id="txtNewPass" placeholder="Password"  /></div>
		<div class='txtleft'><label class="">Confirm Password</label></div>
		<div><input type="password" id="txtConfPass" placeholder="Password"  /></div>

		<div style='margin-top:10px'><button class="btn pribta-tone" id="btnChange">Change Password</button><img id='imgPassChanging' style='height:25px;display:none' src='assets/image/spinner.gif' /></div>
		
	</div>
</div>

<script>
	$(function(){
		$("#btnChange").unbind("click");
		$("#btnChange").on("click",function(){
			sSid = $("#txtSID").val();
			sP1=$("#txtNewPass").val();
			sP2=$("#txtConfPass").val();

			if(sP1!=sP2 || sP1==""){
				$.notify("New password and confirm is not the same and can't be blank.");
				return;
			}

			objThis = $(this);

			$("#imgPassChanging").show();
			$("#btnChange").hide();
			var aData = {u_mode:"reset_password",pid:sSid ,p1:sP1,p2:sP2};
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					alert(rtnObj.msg);
					$("#btnChange").show();
					$("#imgPassChanging").hide();
					setDlgResult("0");
				}else if(rtnObj.res=="1"){
					
					closeDlg(objThis,"1");
				}

				//$("#btnBindQ").show();
			});
		});
	});

</script>