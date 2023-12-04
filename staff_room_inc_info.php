<? 
include_once("in_session.php"); 
include_once("in_php_function.php");

$sRoom = getSS("room_no");
$sRoomName = getSS("room_detail");

?>


<div class='fl-fix w-30 fl-mid fw-b'>
	<? echo($sRoom); ?>
</div>
<div class='fl-fill'>
	<? echo(($sRoomName=="")?"กรุณาเข้าห้องเพื่อรับคิว ->":$sRoomName); ?>
</div>
<div id='btnRoomLogIn' class='fabtn fl-fix w-30 fl-mid'>
	<i class='fas fa-door-open'></i>
</div>