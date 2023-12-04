<?
include_once("in_db_conn.php");


$query =" INSERT INTO i_system_log(s_id,log_module,log_event,input_col,input_val,where_col,where_val,log_datetime) VALUES(?,?,?,?,?,?,?,NOW());";
$stmt = $mysqli->prepare($query);
foreach ($aLog as $key => $aLogRow) {
	if(count($aLogRow)==7){
		$stmt->bind_param("sssssss",...$aLogRow);
		if($stmt->execute()){
			$iAffRow =$stmt->affected_rows;
			if($iAffRow > 0) {
			}
		}
	}
}

if(get_object_vars($mysqli)["sqlstate"]!=""){
	$mysqli->close();
}

?>