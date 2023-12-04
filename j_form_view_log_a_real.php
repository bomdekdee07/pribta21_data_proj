<?
include_once("in_session.php");
include_once("in_php_function.php");


$aPost=getAllQS();
$sUid=getQS("uid");
$sFormId=getQS("formid");
$sMode=getQS("u_mode");
$aRes=array("res"=>"0","msg"=>"");
$sSid = getSS("s_id");

//include("in_db_conn.php");
include("in_php_pop99_sql.php");
if($sMode=="update_log_data"){

	$sColD = "";
	$sColT = "";

	$lastupdate = date("Y-m-d H:i:s");

	foreach ($aPost["lst_data"] as $key => $itm) {
		$arr_data = explode(':', $itm);
        $row_id = $arr_data[0];
        $data_id = $arr_data[1];
        $data_result = urldecode($arr_data[2]);

        if($sColD==""){
        	$query="SELECT collect_date,collect_time FROM p_data_log_row WHERE form_id=? AND uid=? AND row_id=?";
        	$stmt=$mysqli->prepare($query);
        	$stmt->bind_param("sss",$sFormId,$sUid,$row_id);
        	if($stmt->execute()){
        		$stmt->bind_result($collect_date,$collect_time);
        		while($stmt->fetch()){
        			$sColD = $collect_date;
        			$sColT = $collect_time;
        		}
        	}
        }

        $iAffRow = 0;
		$query="INSERT INTO p_data_result(uid,collect_date,collect_time,data_id,data_result,lastupdate,s_id)
			VALUES (?,?,?,?,?,NOW(),?)
			ON DUPLICATE KEY UPDATE lastupdate=NOW(), s_id=VALUES(s_id), data_result=VALUES(data_result);";
		 	//$query=" SELECT ?, collect_date, collect_time, ?, ?, NOW(), ?
	        // FROM p_data_log_row WHERE uid=? AND form_id=? AND row_id=?
	        // ON DUPLICATE KEY UPDATE lastupdate=NOW(), s_id=VALUES(s_id), data_result=VALUES(data_result);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssssss", $sUid,$sColD,$sColT,$data_id, $data_result, $sSid);
		$stmt->execute();
		$iAffRow=$stmt->affected_rows;
		$stmt->close();

		if($iAffRow > 0) {
			$aRes["res"]="1";
			addToLogDataResult($sSid, $sUid, $sColD, $sColT,$sFormId, $data_id, $data_result , $lastupdate);
 

	        $sql_cmd = "update log [$sUid|$sFormId|$row_id] $data_id:$data_result";
	        $query = "INSERT INTO a_log_cmd (update_user, sql_cmd)
	        VALUES(?, ?)";
	        $stmt = $mysqli->prepare($query);
	        $stmt->bind_param('ss',$sSid,$sql_cmd);
	        if($stmt->execute()){
	        }
	        else{
	          $aRes["msg"]="Failed to added log";
	        }
	        $stmt->close();
					$aRes['lastupdate'] = $lastupdate;
		}
	}
}
$mysqli->close();
$sTemp=json_encode($aRes);
echo($sTemp);
?>
