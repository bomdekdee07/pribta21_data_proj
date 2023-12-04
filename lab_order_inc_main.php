<?
include_once("in_session.php");
include_once("in_php_function.php");

$uid = getQS("uid");
$sColD = getQS("coldate");
$sColT = urldecode(getQS("coltime"));
$sRoomNo = getSS("room_no");
$sSid=getSS("s_id");
$sIsDoc=getQS("is_doctor","0");

$sUrl = "../weclinic/p_lab_order_edit_mainpage.php?uid=".$uid."&collect_date=".$sColD."&collect_time=".$sColT."&s_id=".$sSid."&s_id_room=".$sRoomNo."&is_pribta=1&is_doctor=".$sIsDoc;

?>
<iframe src='<? echo($sUrl); ?>' style='width:99%;height: 99%'>
</iframe>

