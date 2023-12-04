<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHP QRCode Manual Insert</title>
</head>

<body>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js" type="text/javascript"></script>

<?
$v_data='5CAn1O8uxHy4PhRiIpF61599294080160';
$v_size='10';
$v_level='H';
?>

<script>
$(document).ready(function(){

		$("#screen").load('manual.php?data=<?=$v_data;?>&size=<?=$v_size;?>&level=<?=$v_level;?>')
		
});
</script>

<div id="screen"></div>
<?

//include('manual.php'); 

?>
</body>
</html>