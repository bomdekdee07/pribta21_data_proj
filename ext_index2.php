<? include("in_session.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>IHRI</title>
<?
	include("in_head_script.php");
	include_once("in_php_function.php");
	$sLink = getQS("file");

?>
</head>

<body id='pribtaBody'>
	<div id='pribta21' class='mainbody'>
		<? include($sLink.".php"); ?>
	</div>
</body>
<script>


$(function() {
    //Master Code here *DateFormat,emailFormat, etcs. //Jeng

});




</script>
</html>
