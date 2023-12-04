<?
/* Project schedule uid/pid */
include("in_session.php");
include_once("in_php_function.php");

$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sSearch = getQS("txtsearch");

$s_id = getQS("s_id");
if($s_id == ""){
   if(isset($_SESSION["s_id"])){
     $s_id =$_SESSION["s_id"];
   }
}

if($sSearch != ""){

  include("in_db_conn.php");

  $sSearch = "%$sSearch%";
  $query_add = "";

  if($sGroupid != ""){
    $query_add .= " AND PUL.proj_group_id=? ";
  }



  $arr_data_list = array();
  $query ="";
  $txt_row = "";

  	$query ="SELECT PUL.uid, PUL.pid, P.uic, PUL.proj_group_id, PUL.proj_id, PUL.clinic_id
  	FROM p_project_uid_list PUL
  	LEFT JOIN patient_info P ON (PUL.uid=P.uid)
  	WHERE PUL.proj_id=? AND PUL.uid_status <> '10'
    AND CONCAT(PUL.uid,',',PUL.pid,',',P.UIC) LIKE ?
    AND PUL.clinic_id IN
     (select distinct clinic_id from i_staff_clinic where s_id=?)
    $query_add
  	ORDER BY PUL.pid
  	";

  	$stmt = $mysqli->prepare($query);
    if($sGroupid == "") $stmt->bind_param('sss',$sProjid,$sSearch, $s_id);
    else $stmt->bind_param('ssss',$sProjid,$sSearch, $s_id, $sGroupid );



  	if($stmt->execute()){
  		$result = $stmt->get_result();
  		while($row = $result->fetch_assoc()) {
        $txt_row .= addRowSearchProjUid($row['proj_id'], $row['uid'], $row['pid'], $row['uic'], $row['clinic_id'], $row['proj_group_id']);
  		}
  	}
    $stmt->close();
  	$mysqli->close();

    if($txt_row != "") {
      echo $txt_row; 
    }
    else echo "<center>No Data Found.</center>";
}

function addRowSearchProjUid($projid, $uid, $pid, $uic, $clinic_id, $group_id)
{
  $txt_row = "";
  $txt_row .= "<div class='fl-wrap-row p-row ph40 fs-s view-visit' ";
  $txt_row .= "data-uid='$uid' data-projid='$projid' data-groupid='$group_id'>";

  $txt_row .= "<div class='fl-wrap-col fl-mid pw80 ptxt-b ptxt-blue'>";
  $txt_row .= "<div class='fl-fill'>$pid</div><div class='fl-fill ptxt-s8'>$group_id</div>";
  $txt_row .= "</div>";

  $txt_row .= "<div class='fl-wrap-col fl-mid pw80 ptxt-b'>";
  $txt_row .= "<div class='fl-fill'>$uid</div><div class='fl-fill'>$uic</div>";
  $txt_row .= "</div>";

  $txt_row .= "<div class='fl-fix fl-mid pw80 '>$clinic_id</div>";
  $txt_row .= "</div>";

  return $txt_row;

}
?>
