<?
/* Project schedule uid/pid */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");

$sUID = getQS("uid");
$txt_row = "";


  //echo "$sProjid, $sGroupid, $sDateTo, $sDateFrom";
  include("in_db_conn.php");

  	$query ="		SELECT PUV.proj_id, P.proj_name, PUL.enroll_date, PUL.pid,
    PUV.visit_id, VL.visit_day, VL.visit_day_before, VL.visit_day_after,  PUL.clinic_id, PUV.schedule_date
  	FROM p_project_uid_visit PUV
    LEFT JOIN p_visit_list VL ON (VL.visit_id=PUV.visit_id AND VL.proj_id=PUV.proj_id)
    LEFT JOIN p_project_uid_list PUL ON (PUL.uid=PUV.uid AND PUL.proj_id=PUV.proj_id)
    LEFT JOIN p_project P ON (P.proj_id=PUV.proj_id)
  	WHERE PUV.uid =? AND PUV.visit_status=0
  	ORDER BY PUV.proj_id, PUV.schedule_date
  	";

  	$stmt = $mysqli->prepare($query);
    $stmt->bind_param('s',$sUID);
    $cur_proj_id = "";

  	if($stmt->execute()){
  		$result = $stmt->get_result();
  		while($row = $result->fetch_assoc()) {

        if($cur_proj_id != $row['proj_id']){
          $cur_proj_id = $row['proj_id'];
          $txt_row .= "<div class='fl-fix fl-mid ph25 pbg-blue ptxt-b ptxt-white ptxt-s10' >";
          $txt_row .= "<i class='fas fa-calendar-day fa-lg pr-2' ></i> [".$row['proj_id']."] ".$row['proj_name'];
          $txt_row .= "</div>";
        }

        if($row['visit_id'] != 'FU'){

          $sactual_scheduledate = addDayToDate($row['enroll_date'], $row['visit_day']);
          $swindow_period_before = addDayToDate($sactual_scheduledate, "-".$row['visit_day_before']);
          $swindow_period_after = addDayToDate($sactual_scheduledate, $row['visit_day_after']);

          $txt_row .= "<div class='fl-fix  ph20 ptxt-s10 p-row' >";
        //  $txt_row .= "  <span class='ptxt-b '>".$row['visit_id']."</span> <span class='ptxt-b ptxt-blue px-2'>".$row['schedule_date']."</span>  (".addDayToDate($row['schedule_date'], "-".$row['visit_day_before'])." to ".addDayToDate($row['schedule_date'], $row['visit_day_after']).")";
          $txt_row .= "  <span class='ptxt-b '>".$row['visit_id']."</span> <span class='ptxt-b ptxt-blue px-2'>".$row['schedule_date']."</span>  ($swindow_period_before to $swindow_period_after)";
          $txt_row .= "</div>";
        }
        else{ // followup visit
          $txt_row .= "<div class='fl-fix fl-mid ph20 ptxt-s10 p-row' >";
          $txt_row .= "  <span class='ptxt-b ptxt-bg-black'>".$row['visit_id']."</span> <span class='ptxt-b ptxt-blue pr-4 pl-2'>".$row['schedule_date']."</span> (Followup Schedule Visit)" ;
          $txt_row .= "</div>";
        }


  		}
  	}
    $stmt->close();
  	$mysqli->close();


  if($txt_row == "") {
    $txt_row = "<center>No Project Schedule.</center>";
  }

echo $txt_row;
?>
