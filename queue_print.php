<? include_once("in_session.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Clinic Queue</title>
</head>
<?
	$sClinicId="";

	include_once("in_php_function.php");
	$sColTime=getQS("coltime");
	$sKeyId = getQS("site");
	if($sKeyId!=""){
		$sClinicId = easy_dec($sKeyId);	
	}else{
		$sClinicId = getQS("clinicid");
	}
	$iQueue = getQS("q");

	if($sClinicId=="" || $iQueue==""){
		echo("No site provided. Please try again.");
		exit();
	}

	include("in_db_conn.php");

	$sToday = date("Y-m-d");
	$sTime = date("H:i:s");


	//Waiting Number
	$iWaiting=0;

	$query = "SELECT count(IQL1.queue) FROM i_queue_list IQL1   
		LEFT JOIN i_queue_list IQL ON IQL.room_no=IQL1.room_no
		AND IQL.clinic_id=IQL1.clinic_id
		AND IQL.collect_date=IQL1.collect_date
		AND IQL.collect_time < IQL1.collect_time
		AND IQL.queue= ?
		WHERE IQL1.clinic_id=? AND IQL1.collect_date = ? AND IQL1.queue_status != 2 AND IQL1.queue_type='1'";

		if($sColTime!=""){
			$query .= " AND IQL1.collect_time < ?";
		}

	$stmt = $mysqli->prepare($query);
	
	if($sColTime!=""){
		$stmt->bind_param('ssss',$iQueue,$sClinicId,$sToday,$sColTime);
	}else $stmt->bind_param('sss',$iQueue,$sClinicId,$sToday);

	$stmt->execute();
	$stmt->bind_result($iCount);
	while ($stmt->fetch()) {
		$iWaiting = (($iCount==0)?0:$iCount);
	}

	$query = "UPDATE i_queue_list SET queue_print=0 
	WHERE clinic_id=? AND collect_date=? AND queue=?";

	$stmt = $mysqli->prepare($query);
	//$stmt->bind_param($sParam,...$aUpdData);
	$stmt->bind_param("sss",$sClinicId,$sToday,$iQueue);
	if($stmt->execute()){
		$iAffRow =$stmt->affected_rows;
		if($iAffRow > 0) {
			
		}else{
			echo("Error update print.".$iQueue);
		}
	}	


	$mysqli->close();

	$aDate = explode("-",$sToday);
	$sBudDay = $aDate[2]."/".$aDate[1]."/".(($aDate[0]*1) + 543);
	$sQR = "<font size='30'><b>".$iQueue."</b></font>
	<br/><br/>จำนวนคิวที่รอ:".$iWaiting."<br/><br/>".$sBudDay." ".$sTime;
	$sImage = "assets/image/logo_".$sClinicId.".png";
	if(!file_exists($sImage)){
		$sImage = "assets/image/logo_IHRI.png";
	}
?>
<body>
<center>
<img src="<? echo($sImage); ?>" border="0" width="150" />
<br/>
<br/>
    <!-- <div id="qrcode"></div> -->
<!-- <br/> -->
<?
	echo($sQR);
?>
</center>
<script type="text/javascript" src="assets/js/qrcode.min.js"></script>
<script type="text/javascript">
	// new QRCode(document.getElementById("qrcode"),{
	//         text: "<? //echo($iQueue); ?>",
	//         width: 150,
	//         height: 150});

	print_and_close();
	function print_and_close() {
		window.print();
		setTimeout(window.close,3500);
	}
</script>
</body>
</html>