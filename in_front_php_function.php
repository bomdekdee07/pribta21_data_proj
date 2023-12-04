<?
function getReligion($religion){
	$aR=array("","ไม่นับถือศาสนาใด (Irreligion)","พุทธ (Buddhist)","คริสต์ (Christianity)","อิสลาม (Islam)","ฮินดู (Hinduism)","อื่นๆ (Others)")	;

	return (isset($aR[$religion])?$aR[$religion]:"");
}

function getSex($sex){
	$aR=array("","ชาย (Male)","หญิง (Female)","มีสรีระทั้งชายและหญิง (Intersex)")	;

	return (isset($aR[$sex])?$aR[$sex]:"");
}

?>