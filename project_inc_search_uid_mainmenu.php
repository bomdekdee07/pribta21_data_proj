<?
/* Project schedule uid/pid */
include("in_session.php");
include_once("in_php_function.php");


//echo $class_auth."<br>";

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


    $arr_data_list = array();
    $txt_row = "";

      $query = "SELECT PUL.uid, PUL.pid, P.uic, PUL.proj_group_id, PUL.proj_id,
      PUL.clinic_id, PUL.uid_status
     FROM p_project_uid_list PUL
     LEFT JOIN patient_info P ON (PUL.uid=P.uid )
     WHERE  PUL.proj_id IN
    	(select proj_id from p_staff_auth where s_id=? AND allow_view=1)
     AND PUL.clinic_id IN
      (select distinct clinic_id from i_staff_clinic where s_id=?)
    AND CONCAT(PUL.uid,',',PUL.pid,',',P.UIC) LIKE ?
     ORDER BY PUL.pid

      ";

    	$stmt = $mysqli->prepare($query);
      $stmt->bind_param('sss',$s_id,$s_id, $sSearch);

    	if($stmt->execute()){
    		$result = $stmt->get_result();
    		while($row = $result->fetch_assoc()) {

          $txt_row .= addRowUIDSearch($row['uid'], $row['uic'], $row['pid'],
          $row['proj_id'], $row['proj_group_id'], $row['clinic_id'], $row['uid_status'] );
    		}
    	}
      $stmt->close();
    	$mysqli->close();

      if($txt_row == "") {
        $txt_row = "<center>No Data Found.</center>";
      }

  }

echo $txt_row;

function addRowUIDSearch($uid, $uic, $pid, $proj_id, $group_id, $clinic_id , $uid_status){
  $class_status = "";
  if($uid_status != '1'){
    if($uid_status == '10') $class_status = "pbtn-cancel";
    else if($uid_status == '2') $class_status = "pbtn-ok";
  }


  $txt_row = "
    <div class='fl-wrap-row ph60  pbtn  $class_status p-row ptxt-s10 px-1 pt-1 view-visit'
    data-uid='$uid' data-projid='$proj_id' data-groupid='$group_id'>
       <div class='fl-fix pw80'>
          <div class='ptxt-u ptxt-s12 ptxt-b'>$pid</div>
          <div>$uid</div>
          <div>$uic</div>
       </div>
       <div class='fl-fix pw100'>
         <div class='ptxt-s12 ptxt-b'>$proj_id</div>
         <div>Group: $group_id</div>
         <div>Clinic: $clinic_id</div>
       </div>
    </div>
  ";
  return $txt_row;
}


?>
