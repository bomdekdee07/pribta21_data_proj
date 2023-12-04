<? include("in_session.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRIBTA CLINIC</title>
<?
	include("in_head_script.php");
	include_once("in_php_function.php");
?>
</head>

<body id='pribtaBody' style='min-width: 1024px' >
	<div id='pribta21' class='mainbody fl-wrap-col'>
		Please do not close this windows while printing.
	</div>
</body>
<script>


$(function() {
    //Master Code here *DateFormat,emailFormat, etcs. //Jeng
	function popup(url,name,windowWidth,windowHeight){   
		myleft=(screen.width)?(screen.width-windowWidth)/2:100;
		mytop=(screen.height)?(screen.height-windowHeight)/2:100;  
		properties = "width="+windowWidth+",height="+windowHeight;
		properties +=",scrollbars=yes, top="+mytop+",left="+myleft;  
		window.open(url,name,properties);
	}

	
});




</script>
</html>