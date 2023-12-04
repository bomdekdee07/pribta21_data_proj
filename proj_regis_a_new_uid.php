<?
/* Project Thumbnail list  */

include("in_session.php");
include_once("in_php_function.php");

include("in_db_conn.php");

$flag_valid = 1;
$uid = "";
$uic = "";
$res = 1;
$query_add ="";
$msg_err = "";

$aLst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];

if(isset($aLst_data['uid'])) unset($aLst_data['uid']);
if(isset($aLst_data['uic'])) unset($aLst_data['uic']);

$fname = $aLst_data['fname'];
$lname = $aLst_data['sname'];
$dob = $aLst_data['date_of_birth'];

//echo "data: $fname / $date_of_birth";

if(isset($aLst_data['citizen_id'])){ // check citizen id

	$query = "SELECT count(uid) found_amt
	FROM patient_info
	WHERE citizen_id = ?
	";
	//echo "query: $query";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('s',$aLst_data['citizen_id']);

	if($stmt->execute()){
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc()) {
			if($row["found_amt"] > 0) {
				$msg_err = "Duplicate Citizen ID in existing data.";
				$flag_valid = 0;
				$res = 0;
			}
		}//while
	}
	$stmt->close();
}

if($flag_valid == 1){

 // create UIC
 $fname = str_replace("ุ","",$fname);
 $lname = str_replace("ุ","",$lname);
 $arr_uic =  generateUIC($fname, $lname, $dob);
 $uic = $arr_uic["uic"];


 $sPrepare=""; 	$col_insert=""; $col_insert_val="";
 $lst_data_param = array();
 $aLst_data['uic']=$uic;
 foreach($aLst_data as $key=>$value){
 	$sPrepare .= "s";
 	$col_insert .= "$key,";
	$col_insert_val .= "?,";
 	$lst_data_param[] = $value;
}// foreach
if($col_insert!=""){
	$col_insert = substr($col_insert,0,strlen($col_insert)-1);
	$col_insert_val = substr($col_insert_val,0,strlen($col_insert_val)-1);
}
// for patient_info
$cur_year =  (new DateTime())->format('y');
$id_prefix = "P".$cur_year."-" ;

$id_digit = 5; // 00001-99999
$where_substr_pos_end = strlen($id_prefix);
$substr_pos_begin = 1+$where_substr_pos_end;

$inQuery = "INSERT INTO patient_info (uid, $col_insert)
SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(uid) IS NULL,0,MAX(uid)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
 ,$col_insert_val
	FROM patient_info WHERE SUBSTRING(uid,1,$where_substr_pos_end) = '$id_prefix' ;
";

$stmt = $mysqli->prepare($inQuery);
$stmt->bind_param($sPrepare,...$lst_data_param);


if($stmt->execute()){
	$inQuery = "SELECT @keyid;";
	$stmt = $mysqli->prepare($inQuery.";");
	$stmt->bind_result($uid);
	if($stmt->execute()){
		if($stmt->fetch()){
     $res = 1;
		}
	}
}
else{
	$msg_info .= $stmt->error;

}
$stmt->close();


 // for uic_gen, basic_reg
 $d=$arr_uic["d"]; $m=$arr_uic["m"]; $y=$arr_uic["y"];

}





  //if(isset($stmt)) $stmt->close();

  $mysqli->close();

  $rtn["res"] = $res;
	$rtn["uid"] = $uid; $rtn["uic"] = $uic;
	$rtn["msg_err"] = $msg_err;

	$returnData = json_encode($rtn);
  echo $returnData;



	function generateUIC($fname, $lname, $dob){ // $char_pos :อักษรตัวที่ n ของนามสกุล เลื่อนนามสกุลไปได้เรื่อยๆ ถ้า uic ซ้ำ
		$uic = "";
		$arr_uic = array();
		$arr_dob = explode("-",$dob);

		$f = getFirstChar($fname);
		$l = getFirstChar($lname);
		if(($f !="") && ($l != "")){
		$uic .= $f; // first fname
		$uic .= $l; // last lname
		}

		$arr_uic["d"] = str_pad($arr_dob[2], 2, '0', STR_PAD_LEFT);// date
		$arr_uic["m"] = str_pad($arr_dob[1], 2, '0', STR_PAD_LEFT);// month
		$arr_uic["y"] = substr(strval((int)$arr_dob[0] + 543), 2, 4); // year

		$arr_uic["uic"]= $uic.$arr_uic["d"].$arr_uic["m"].$arr_uic["y"];
		return $arr_uic;
	}


	function validateDate($date, $format = 'Y-m-d'){
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) === $date;
	}

	function getFirstChar($strName){
	  $f = ""; $k=0;
	  $count= strlen($strName);
	  for($k=0; $k<$count; $k++){
	    $f = mb_substr($strName,$k,1,'UTF-8');
	    if(checkFirstChar($f)){
	      break;
	    }
	  }// for
	  return $f;
	}

	function checkFirstChar($str_name){
		  if(preg_match('/^[A-Za-zกขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรฤลฦวศษสหฬอฮ]+$/', $str_name))
	  {
	    return true;
	  }
	  else{
	    return false;
	  }
	}

?>
