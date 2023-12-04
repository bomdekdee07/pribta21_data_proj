<?
/* Project Form link */

include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");


$sLink = getQS("link");
$sLink = decodeSingleLink($sLink);
$arr = explode(",",$sLink);
$sFormid = $arr[0];
$sUID = $arr[1];
$sVisitdate = $arr[2];
$sVisittime = $arr[3];

$encode_link = encodeSingleLink("$sFormid,$sUID,$sVisitdate,$sVisittime");
//$link = 'http://'.$_SERVER['HTTP_HOST']."/weclinic/data_mgt/mnu_form_view.php?form_id=$sFormid&lang=th&uid=$sUID&collect_date=$sVisitdate&collect_time=$sVisittime";


$link = 'http://'.$_SERVER['HTTP_HOST']."/pribta21/ext_index.php?file=p_form_view&form_id=$sFormid&lang=th&uid=$sUID&coldate=$sVisitdate&coltime=$sVisittime";

//echo "$link";
?>

<iframe id='frmData' width="100%" height="100%" src='<? echo $link; ?>'>

</iframe>
