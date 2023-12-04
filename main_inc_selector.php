<?
	include("in_session.php");
	include_once("in_php_function.php");
	if(isset($_SESSION["s_id"]) && isset($_SESSION["clinic_id"]) ){
		if(getSS("s_id") != "") include("main.php");
		else include("login_inc.php");
	}else{
		include("login_inc.php");
	}
?>