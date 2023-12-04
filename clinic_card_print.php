<? 
	include_once("in_php_function.php");
	include("in_db_conn.php");
	$sUid=getQS("uid");
	$sClinicType=strtoupper(getQS("ct")); //Pribta = P //Tangerine = T
	$sClinicId=getQS("clinicid"); //IHRI
	$sCardSide=strtolower(getQS("side")); //Front = F // Back= B
	use PHPImageWorkshop\ImageWorkshop; // Use the namespace of ImageWorkshop

	//Usage clinic_card_print.php?uid=P21-04000&side=b&ct=P

	$file="";$dirPath = ""; $filename = "";

	if($sClinicType!="") $sClinicId = strtolower($sClinicType);
	$sFileTemplate=$sClinicId."_".$sCardSide."_card.jpg";

	if(!file_exists("assets/image/card_template/".$sFileTemplate)){
		echo("Invalid provided data :".$sFileTemplate);
		exit();
	}



	if($sCardSide=="b"){
		$dirPath = "assets/image/card_template/";
		$filename=$sFileTemplate;
		$file = $dirPath.$filename;
	}else{
		//Add Initial to the Card. By Jeng
		$sFname = ""; $sSname="";
		$query = "SELECT fname,sname FROM patient_info WHERE uid=?;";
		$stmt=$mysqli->prepare($query);
		$stmt->bind_param("s",$sUid);
		if($stmt->execute()){
			$stmt->bind_result($fname,$sname);
			while ($stmt->fetch()) {
			   $sFname=$fname;
			   $sSname=$sname;
			}
		}
		$mysqli->close();

		$aConsonant=array("ก","ฃ","ข","ค","ฅ","ฆ","ง","จ","ฉ","ช","ซ","ฌ","ญ","ฏ","ฎ","ฐ","ฑ","ฒ","ณ","ด","ต","ถ","ท","ธ","น","บ","ป","ผ","ฝ","พ","ฟ","ภ","ม","ย","ร","ล","ว","ศ","ษ","ส","ห","ฬ","อ","ฮ","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$sInF="";$sInS="";

		$aName = str_split_unicode($sFname);
		for($ix=0;$ix<count($aName);$ix++){
			if(in_array($aName[$ix],$aConsonant)){
				$sInF=$aName[$ix];
				$ix=count($aName);
			}
		}
		$aName = str_split_unicode($sSname);
		for($ix=0;$ix<count($aName);$ix++){
			if(in_array($aName[$ix],$aConsonant)){
				$sInS=$aName[$ix];
				$ix=count($aName);
			}
		}
		//End add Initial
		$sPrintUid=$sUid." ".$sInF." ".$sInS;


		
		require_once("assets/phpqrcode/qrlib.php");

		$temp_dir='tempqr/'.$sUid.'.png';
		$temp_filename=$sUid.'.png';
		QRcode::png($sUid,$temp_dir,QR_ECLEVEL_H, 10);


		 
		require_once('assets/PHPImageWorkshop/ImageWorkshop.php'); // Be sure of the path to the class
		require_once('assets/PHPImageWorkshop/Core/ImageWorkshopLayer.php');
		require_once('assets/PHPImageWorkshop/Core/ImageWorkshopLib.php');	
		require_once('assets/PHPImageWorkshop/Exception/ImageWorkshopBaseException.php');		
		require_once('assets/PHPImageWorkshop/Exception/ImageWorkshopException.php');
		


		$norwayLayer = ImageWorkshop::initFromPath('assets/image/card_template/'.$sFileTemplate);
		$watermarkLayer = ImageWorkshop::initFromPath($temp_dir);
		$textLayer = ImageWorkshop::initTextLayer($sPrintUid, 'assets/PHPImageWorkshop/THSarabun_Bold.ttf', 50, 'ffffff', 0);

		$thumbWidth = 200; // px
		$thumbHeight = null;
		$conserveProportion = true;
		$positionX = 0; // px
		$positionY = 0; // px
		$position = 'MM';
	 
		$watermarkLayer->resizeInPixel($thumbWidth, $thumbHeight, $conserveProportion, $positionX, $positionY, $position);
	 
		$norwayLayer->addLayer(1, $watermarkLayer, 223,154, "LB");
		$norwayLayer->addLayer(2, $textLayer, 180,380, "LB");
	 
		$image = $norwayLayer->getResult();


		$dirPath = "tempcard/";
		$filename = $sUid."_IN_card.jpg";
		$file=$dirPath.$filename;

		$createFolders = true;
		$backgroundColor = null; // transparent, only for PNG (otherwise it will be white if set null)
		$imageQuality = 100; // useless for GIF, usefull for PNG and JPEG (0 to 100%)
		 
		$norwayLayer->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);

	}





	
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
?>




