<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");


$sLinkdata = getQS("linkdata");
$flag_valid = 0;
if($sLinkdata != ""){

  $sLinkdata = decodeSingleLink($sLinkdata);

  $arr = explode(",",$sLinkdata);
  if(count($arr) == 7){
		$sUID = $arr[0];
		$sColdate = $arr[1];
		$sColtime = $arr[2];
		$sFormid = $arr[3];
		$sProjid = $arr[4];
		$sVisitid = $arr[5];
		$sLang = $arr[6];

		$sToday = date('Y-m-d');
		$s_id = getSS('s_id');

		if($s_id == ""){
			if($sToday == $sColdate){
        $flag_valid = 1;
			}
			else{
				echo "<center>This form link is expired after <u>$sColdate</u>.</center>";
				exit();
			}
		}
		else{
      $flag_valid = 1;
		}
  }
	else{
		echo "<center>Invalid Form Link. Please contact staff.</center>";
		exit();

	}
}
else{
	echo "<center> Incomplete Form Link. Please contact staff.</center>";
	exit();
}

if($flag_valid){
	$_GET['uid'] = $sUID;
	$_GET['coldate'] = $sColdate;
	$_GET['coltime'] = $sColtime;
	$_GET['form_id'] = $sFormid;
	$_GET['visitid'] = $sProjid;
	$_GET['projid'] = $sVisitid;
	$_GET['lang'] = $sLang;
	include('p_form_view.php');
}

?>
