<?
/* Project schedule uid/pid */
include("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sDateTo = getQS("date_to");
$sDateFrom = getQS("date_from");
$sIsadmin = getQS("is_admin");


$s_id = getQS("s_id");
if($s_id == ""){
   if(isset($_SESSION["s_id"])){
     $s_id =$_SESSION["s_id"];
   }
}


//echo "$sProjid, $sGroupid, $sDateTo, $sDateFrom";
include("in_db_conn.php");

$query_add = "";

if($sIsadmin != '1'){
  $query_add .= " AND PUL.uid_status <> '10' ";
}

if($sGroupid != ""){
  $query_add .= " AND PUL.proj_group_id=? ";
}

$arr_data_list = array();
$query ="";
$txt_row = "";

/*
$clinic_id = isset($_SESSION["clinic_id"])? $_SESSION["clinic_id"]:"NONE";
$query_add .= " AND PUL.clinic_id='$clinic_id' ";
*/
	$query ="SELECT PUV.uid, PUL.pid, P.uic, PUV.visit_id, PUV.group_id, PUL.clinic_id,
	PUV.schedule_date, PUV.visit_date
	FROM p_project_uid_list PUL,
  p_project_uid_visit PUV
	LEFT JOIN patient_info P ON (PUV.uid=P.uid )
	WHERE PUL.clinic_id IN (
    select distinct clinic_id from i_staff_clinic where s_id=?
  )
  AND PUV.proj_id=?
  AND PUV.proj_id = PUL.proj_id AND PUV.group_id=PUL.proj_group_id AND PUV.uid = PUL.uid
  AND (PUV.schedule_date >=? AND PUV.schedule_date <=?)
  $query_add
	ORDER BY PUV.schedule_date
	";
/*
print_r($lst_data_param);
echo "$sPrepare / $query";
*/
//  echo "$s_id, $sProjid, $sGroupid, $sDateTo, $sDateFrom / $query";
	$stmt = $mysqli->prepare($query);
  if($sGroupid == "")
    $stmt->bind_param('ssss', $s_id, $sProjid, $sDateFrom, $sDateTo );
  else
  $stmt->bind_param('sssss', $s_id, $sProjid,  $sDateFrom, $sDateTo, $sGroupid );



	if($stmt->execute()){
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()) {

      $txt_row .= "<div class='fl-wrap-row p-row ph20 fs-s view-visit' ";
      $txt_row .= "data-uid='".$row['uid']."' data-projid='".$sProjid."' data-groupid='".$row['group_id']."'>";


      $txt_row .= "<div class='fl-fix fl-mid pw80 ptxt-b ptxt-blue'>".$row['pid']."</div>";
      $txt_row .= "<div class='fl-fix fl-mid pw80 '>".$row['uid']."</div>";
      $txt_row .= "<div class='fl-fix fl-mid pw80 '>".$row['uic']."</div>";
      $txt_row .= "<div class='fl-fix fl-mid pw80 '>".$row['clinic_id']."</div>";
      $txt_row .= "<div class='fl-fill fl-mid pw80 '>".$row['group_id']."</div>";
      $txt_row .= "<div class='fl-fill fl-mid pw80 '>".$row['visit_id']."</div>";
      $txt_row .= "<div class='fl-fix fl-mid pw80 '>".$row['schedule_date']."</div>";
      $txt_row .= "<div class='fl-fix fl-mid pw80 '>".$row['visit_date']."</div>";
      $txt_row .= "</div>";

    //  $txt_row .= "<div>".$row['pid']."</div>";
    //echo "uid: ".$row['pid'];
		}
	}
  $stmt->close();
	$mysqli->close();
//echo "textrow: $txt_row";
  if($txt_row != "") {
    $txt_row = str_replace("0000-00-00","-",$txt_row);
    echo $txt_row;
  }
  else echo "<center>No Data Found.</center>";

?>
