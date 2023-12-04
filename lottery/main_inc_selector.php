<?
	include("in_session.php");
	include_once("in_php_function.php");
	if(isset($_SESSION["s_id"])){
		if(getSS("s_id") != "") 
			include("main.php");
		else 
			include("patient_system_login.php");
	}else{
		include("patient_system_login.php");
	}
?>