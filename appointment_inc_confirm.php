<?
include_once("in_php_function.php");
$sClinic=getSS("clinic_id");
$sToday=date("Y-m-d");

/*
include("in_db_conn.php");
$query="SELECT uid,";
$stmt=$mysqli->prepare($query);
$stmt->bind_param("ss",$sToday,$sClinic);
if($stmt->execute()){
	$stmt->bind_result($uid,$s_id,$appointment_time,$is_confirm,$fname,$sname,$en_fname,$en_sname,$clinic_type,$s_name,$q_uid);
	while($stmt->fetch()){

	}
}
$mysqli->close();
*/
?>
<div class='fl-wrap-col'>
	<div class='fl-fix h-60'>
		ข้อมูลคนไข้ และ เบอร์ติดต่อ
	</div>
	<div class='fl-wrap-row'>
		<div class='fl-fix w-50'></div>
		<div class='fl-fill'  style='text-align: left'>
			<label><input type='radio' class='bigcheckbox' name='is_confirm' value='0' /> ไม่ยืนยัน/Not Confirm</label><br/>
			<label><input type='radio' class='bigcheckbox' name='is_confirm' value='1' /> ยืนยัน/Confirm</label><br/>
			<label><input type='radio' class='bigcheckbox' name='is_confirm' value='2' /> มาแล้ว/Visited</label><br/>
			<label><input type='radio' class='bigcheckbox' name='is_confirm' value='9' /> ยกเลิกนัด/Cancelled</label>
		</div>
		<div class='fl-fix w-50'></div>
	</div>
	<div class='fl-fix h-30'>ข้อมูลนัด/Note</div>
	<div class='fl-fix h-80'>
		<textarea style='height:99%;width:99%'></textarea>
	</div>
	<div class='fl-fix h-30 fl-mid'>
		<input type='button' value='บันทึก/Save' />
	</div>
</div>
