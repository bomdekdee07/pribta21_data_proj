<?
function getAge($dob){
	$dob_a = explode("-", $dob);
	$today_a = explode("-", date("Y-m-d"));
	$dob_d = $dob_a[2];$dob_m = $dob_a[1];$dob_y = $dob_a[0];
	$today_d = $today_a[2];$today_m = $today_a[1];$today_y = $today_a[0];
	$years = $today_y - $dob_y;
	$months = $today_m - $dob_m;
	$days=$today_d - $dob_d;
	if ($today_m.$today_d < $dob_m.$dob_d) {
		$years--;
		$months = 12 + $today_m - $dob_m;
	}

	if ($today_d < $dob_d){
		$months--;
	}

	$firstMonths=array(1,3,5,7,8,10,12);
	$secondMonths=array(4,6,9,11);
	$thirdMonths=array(2);

	if($today_m - $dob_m == 1){
		if(in_array($dob_m, $firstMonths)){
			array_push($firstMonths, 0);
		}elseif(in_array($dob_m, $secondMonths)) {
			array_push($secondMonths, 0);
		}elseif(in_array($dob_m, $thirdMonths)){
			array_push($thirdMonths, 0);
		}
	}

	return " $years ปี $months เดือน ".abs($days)." วัน";
}
function getReligion($religion){
	$aR=array("","ไม่นับถือศาสนาใด (Irreligion)","คริสต์ (Christianity)","อิสลาม (Islam)","ฮินดู (Hinduism)","อื่นๆ (Others)")	;

	return (isset($aR[$religion])?$aR[$religion]:"");
}

function getSex($sex){
	$aR=array("","ชาย (Male)","หญิง (Female)","มีสรีระทั้งชายและหญิง (Intersex)")	;

	return (isset($aR[$sex])?$aR[$sex]:"");
}
?>
