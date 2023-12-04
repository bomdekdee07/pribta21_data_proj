<? 
include("in_session.php");
include_once("in_php_function.php");
//include_once("in_php_function.php"); 
$hideLogo = getQS("hidelogo");

if($hideLogo=="1") $hideLogo = "display:none;";

?>

<style>
.logininput{
	border:1px solid lightgrey;
	min-height:35px;
	width:300px;
}

.txtleft{
 	text-align: left;
 	margin-top:10px;
}


</style>
<div id='divLogin' class=' fl-wrap-col' style=''>
	<div class='fl-wrap-col' style='<? echo($hideLogo); ?> max-height:30%;justify-content: center;' >
		<div style='height:60%;text-align: center'>
			<img style='vertical-align: middle;height:100%' src="assets/image/pribta_logo_mini.png" alt="pribta">
		</div>
	</div>
	<div class='fl-fill' style='' >
		<div class='fl-wrap-col' style=';justify-content: center;text-align: center' >
			<div style='height:90%;'>
				<div class='fl-wrap-col' style=';justify-content: center;'>
					<div class='roundcorner' style='display:inline-block;height:90%;border:1px solid lightgrey;margin:0px auto;padding:30px 30px;line-height:30px;background-color: white'>
						<form class='frmlogin'>
						<div class="card-title">Welcome to Pribta System</div>
						<div class='txtleft'><label class="">Email address</label></div>
						<div><input type="email" class="logininput" id="loginEmail" name="email" placeholder="Enter email" autocomplete="username" /></div>
						<div class='txtleft'><label class="">Password</label></div>
						<div><input type="password" class="logininput" id="loginPass" name="password" placeholder="Password" autocomplete="current-password" /></div>
						<div class='txtleft'><label class="">Clinic</label></div>
						<div><SELECT class='logininput' id='loginClinic'></SELECT><i style='display:none' id='clinicLoading' class='fa fa-spinner fa-spin'></i></div>
						</form>
						<div style='margin-top:10px'><button class="btn logininput pribta-tone" id="btnLogin">Sign in</button><img id='loginloader' style='height:25px;display:none' src='assets/image/spinner.gif' /></div>
						
					</div>
				</div>
			</div>
		</div>

	</div>
</div>



<script>
$(function() {
	<? 
		if($hideLogo!=""){
			echo("var hideLogo = '1';");
		}else {
			echo("var hideLogo = '0';");
		}
	?>

	$("#loginEmail").unbind("change");
	$("#loginEmail").bind("change",function(){
		let sId = $(this).val();
		startLoad($("#loginClinic"), $("#clinicLoading"));
		$("#loginClinic").load("clinic_opt_list.php?email="+sId,function(){
			endLoad($("#loginClinic"), $("#clinicLoading"));
		});
	});

	$("#btnLogin").unbind("click");
	$("#btnLogin").on("click",function(){
		//Login
		let sEmail = $("#loginEmail").val().trim();
		let sPass = $("#loginPass").val().trim();
		let sClinic = (($("#loginClinic").val() != null)?$("#loginClinic").val().trim():"");
		if(sEmail=="" || sPass=="" || sClinic==""){
			$(this).notify("Please complete form.");
			return;
		}
		var aData = {u_mode:"login",e:sEmail,p:sPass,clinic:sClinic};
		if(!checkEmail(sEmail)){
			$("#loginEmail").notify("Invalid Email Format");
			return;
		}
		objThis = $(this);

		startLoad($("#btnLogin"),$("#loginloader"));
		callAjax("login_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="0"){
				$("#pribta21 #loginEmail").notify("email and password doesn't match with the clinic");
				endLoad($("#btnLogin"),$("#loginloader"));
			}else if(rtnObj.res=="1"){
				if(hideLogo=="1"){
					//getCurDialog
					setDlgResult("1");
					closeDlg(objThis,"1");
				}else{
					$("#loginloader").notify("We are preparing page for you. Please wait a bit.","success");
					$("#pribta21").load("main_inc_selector.php",function(){
						endLoad($("#btnLogin"),$("#loginloader"));
					});					
				}

			}
		});
	});



});

</script>