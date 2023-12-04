<?
/* Project schedule uid/pid */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_fn_date.php");

$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sDateTo = getQS("date_to");
$sDateFrom = getQS("date_from");
$sIsadmin = getQS("is_admin");

$sDataGroupID = getQS("data_group_id"); // data group id filter
$sDataClinicID = getQS("data_clinic_id"); // data clinic id filter
// echo $sDataClinicID;

$s_id = getSS("s_id");

//echo "$sProjid, $sDataGroupID, $sGroupid, $sDateTo, $sDateFrom";

include("in_db_conn.php");
$aUIDFinalResult = array();
$group_view_dataid = "";
$aAndFilter = array(); // AND filter condition
$aORFilter = array(); // OR filter condition
$aDataResult = array();  // AND DataResult condition
$aORDataResult = array(); // OR DataResult condition
$txtarr_group_eval = ""; // text all array group to intersect

if($sDataGroupID != ''){
  $query ="SELECT PFG.group_id,PFG.item_group,PFI.item_group_no,
  PFI.data_id,PFI.data_equation,PFI.data_value, PFG.item_group_type, PFL.group_view_dataid
  FROM i_project_filter_list PFL
  LEFT JOIN i_project_filter_group PFG ON (PFL.proj_id=PFG.proj_id AND PFL.group_id=PFG.group_id)
  LEFT JOIN i_project_filter_item PFI ON (PFI.proj_id=PFG.proj_id AND PFI.group_id=PFG.group_id AND PFI.item_group=PFG.item_group)
  WHERE PFL.proj_id=? AND PFL.group_id=?
  ORDER BY PFL.group_id,  PFI.item_group_no
  ";
  //echo "<br>query: $sProjid, $sDataGroupID / $query";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss', $sProjid, $sDataGroupID);

  if($stmt->execute()){
    $stmt->bind_result($data_group_id,$item_group,$item_group_no,
    $data_id,$data_equation,$data_value, $item_group_type, $group_view_dataid);
    while($stmt->fetch()) {
      if($item_group_type  == 'AND'){
        if(!isset($aAndFilter[$item_group])) $aAndFilter[$item_group] = array();
        if(!isset($aAndFilter[$item_group][$item_group_no])) $aAndFilter[$item_group][$item_group_no] = array();

        $aAndFilter[$item_group][$item_group_no]['dataid']=$data_id;
        $aAndFilter[$item_group][$item_group_no]['equation']=$data_equation;
        $aAndFilter[$item_group][$item_group_no]['value']=$data_value;

      }
      else if($item_group_type  == 'OR'){
        if(!isset($aORFilter[$item_group])) $aORFilter[$item_group] = array();
        if(!isset($aORFilter[$item_group][$item_group_no])) $aORFilter[$item_group][$item_group_no] = array();

        $aORFilter[$item_group][$item_group_no]['dataid']=$data_id;
        $aORFilter[$item_group][$item_group_no]['equation']=$data_equation;
        $aORFilter[$item_group][$item_group_no]['value']=$data_value;

      }
    }//while
  //  print_r($aAndFilter);
  //print_r($aORFilter);
  }
  $stmt->close();
}

// extract data_id to view
 $arr_dataid_view = array();
 $SQL_DATAID_VIEW = "";
 $SQL_DATAID_VIEW_LEFT_JOIN = "";
 $count_dataview = 0;
 if($group_view_dataid != "") {
   $arr_dataid_view = explode(',',$group_view_dataid);
   foreach($arr_dataid_view as $dataid_view){
     //data to view
     $col_dataid = "PDR_view".$count_dataview ;
     $SQL_DATAID_VIEW .= "$col_dataid.data_result as $dataid_view,sub_$col_dataid.data_name_th as sub_$dataid_view,";
     $SQL_DATAID_VIEW_LEFT_JOIN .=
     "LEFT JOIN p_data_result $col_dataid
            LEFT JOIN p_data_sub_list sub_$col_dataid
            ON(sub_$col_dataid.data_id=$col_dataid.data_id AND
            sub_$col_dataid.data_value=$col_dataid.data_result)
     ON ($col_dataid.data_id='$dataid_view'
     AND $col_dataid.uid=PUV.uid AND $col_dataid.collect_date=PUV.visit_date
     AND $col_dataid.collect_time='00:00:00') ";
     $count_dataview++;
   }// foreach
 }

/*
 if($SQL_DATAID_VIEW != ""){
    $SQL_DATAID_VIEW = substr($SQL_DATAID_VIEW,0,strlen($SQL_DATAID_VIEW)-1);
 }
*/
 $SQL_DATAID_JOIN = "";
 $SQL_DATAID_WHERE = "";

$count_arrAndOr = count($aAndFilter) + count($aORFilter);
// echo "TEST".$count_arrAndOr;
if($count_arrAndOr == 0){ // select all
  $query_main = "SELECT $SQL_DATAID_VIEW PUL.uid, PUL.pid, P.uic, PUV.visit_id, PUV.group_id, PUL.clinic_id,
  PUV.schedule_date, PUV.visit_date, VL.visit_day_before, VL.visit_day_after
  FROM p_project_uid_list PUL
  LEFT JOIN p_project_uid_visit PUV on (PUV.proj_id = PUL.proj_id AND PUV.group_id = PUL.proj_group_id AND PUV.uid = PUL.uid)
  LEFT JOIN p_visit_list VL ON ((PUV.proj_id=VL.proj_id ) AND PUV.visit_id=VL.visit_id)
  LEFT JOIN patient_info P ON (PUV.uid=P.uid)
  $SQL_DATAID_VIEW_LEFT_JOIN
  WHERE PUL.clinic_id IN (
    select distinct clinic_id from i_staff_clinic where s_id=?
  ) AND PUL.uid_status IN ('1', '2')
  AND PUL.proj_id=?
  ";
  if($sDateFrom != "") $query_main .= " AND  ((PUV.schedule_date >= ? AND PUV.schedule_date <= ?) OR (PUL.enroll_date >= ? and PUL.enroll_date <= ?)) ";

  $condition_groupId = "";
  $condition_clinicid = "";
  $bind_param1 = "ssssss";
  $arra_val1 = array($s_id, $sProjid, $sDateFrom, $sDateTo, $sDateFrom, $sDateTo);

  $bind_param2 = "ss";
  $arra_val2 = array($s_id, $sProjid);

  if($sGroupid != ""){
    $bind_param1 .= "s";
    $arra_val1[] = $sGroupid;

    $bind_param2 .= "s";
    $arra_val2[] = $sGroupid;
    $query_main .= " AND PUL.proj_group_id = ? ";
  }

  if($sDataClinicID != ""){
    $bind_param1 .= "s";
    $arra_val1[] = $sDataClinicID;
    $bind_param2 .= "s";
    $arra_val2[] = $sDataClinicID;
    $query_main .= " AND PUL.clinic_id = ? ";
  }

  $stmt = $mysqli->prepare($query_main);
  //  echo "<br><br>$query_main<br>";
  // echo "TEST".$bind_param1;

  if($sDateFrom != ""){
    $stmt->bind_param($bind_param1, ...$arra_val1);
  }
  else{
    $stmt->bind_param($bind_param2, ...$arra_val2);
  }

  if($stmt->execute()){
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
      $key_id = $row['uid'].$row['schedule_date'];
      if(isset($aUIDFinalResult[$key_id]))
      $aUIDFinalResult[$key_id] = array();
      $aUIDFinalResult[$key_id]['uid'] = $row['uid'];
      $aUIDFinalResult[$key_id]['pid'] = $row['pid'];
      $aUIDFinalResult[$key_id]['uic'] = $row['uic'];
      $aUIDFinalResult[$key_id]['visit_id'] = $row['visit_id'];
      $aUIDFinalResult[$key_id]['group_id'] = $row['group_id'];
      $aUIDFinalResult[$key_id]['clinic_id'] = $row['clinic_id'];
      $aUIDFinalResult[$key_id]['schedule_date'] = $row['schedule_date'];
      $aUIDFinalResult[$key_id]['visit_date'] = $row['visit_date'];
      $aUIDFinalResult[$key_id]['visit_day_before'] = $row['visit_day_before'];
      $aUIDFinalResult[$key_id]['visit_day_after'] = $row['visit_day_after'];
      $aUIDFinalResult[$key_id]['data_filter'] = '';
      foreach ($arr_dataid_view as $view_data_id){
        if(isset($row[$view_data_id])){
          $aUIDFinalResult[$key_id]['data_filter'] .= createDataFilterText($view_data_id, $row[$view_data_id], $row['sub_'.$view_data_id]);
        }
      }// foreach
    //  echo $aUIDFinalResult[$key_id]['uid'];
    }//while
  }
  $stmt->close();

}
else{ // select with data filter
  // Group ID
  // AND condition
  foreach ($aAndFilter as $sGroup => $aFilter) {
    $iCount = 0;
    foreach($aFilter as $item_group_no => $aDataId){
        $iCount++;
        if($iCount==1) $SQL_DATAID_WHERE .= " and (";
        else $SQL_DATAID_WHERE .= " and ";

        $col_dataid = "PDR_data".$sGroup.$iCount;
        $SQL_DATAID_JOIN .= " JOIN p_data_result $col_dataid ON ($col_dataid.data_id='".$aDataId["dataid"]."' AND $col_dataid.uid=PUV.uid
        AND $col_dataid.collect_date=PUV.visit_date AND $col_dataid.collect_time='00:00:00')";
        $SQL_DATAID_WHERE .= " $col_dataid.data_result ".$aDataId["equation"]." '".$aDataId["value"]."'";
    } //foreach

    $SQL_DATAID_WHERE .= ") ";
    if(!isset($aDataResult[$sGroup])){
      $aDataResult[$sGroup] = array();
    }

    $query_main = "SELECT $SQL_DATAID_VIEW PUL.uid, PUL.pid, P.uic, PUV.visit_id, PUV.group_id, PUL.clinic_id,
    PUV.schedule_date, PUV.visit_date, VL.visit_day_before, VL.visit_day_after
    FROM p_project_uid_list PUL,
    p_project_uid_visit PUV
    LEFT JOIN p_visit_list VL ON ((PUV.proj_id=VL.proj_id ) AND PUV.visit_id=VL.visit_id)
    LEFT JOIN patient_info P ON (PUV.uid=P.uid)
    $SQL_DATAID_VIEW_LEFT_JOIN
    $SQL_DATAID_JOIN
    WHERE PUL.clinic_id IN (
      select distinct clinic_id from i_staff_clinic where s_id=?
    ) AND PUL.uid_status IN ('1', '2')
    AND PUV.proj_id=? $SQL_DATAID_WHERE
    AND PUV.proj_id = PUL.proj_id AND PUV.group_id=PUL.proj_group_id AND PUV.uid = PUL.uid ";
    if($sDateFrom != "") $query_main .= " AND  (PUV.schedule_date >= ? AND PUV.schedule_date <= ?) ";

    $bind_param1 = "ssss";
    $array_val1 = array($s_id, $sProjid, $sDateFrom, $sDateTo);

    $bind_param2 = "ss";
    $array_val2 = array($s_id, $sProjid);

    if($sGroupid != ""){
      $query_main .= " AND PUL.proj_group_id = ? ";
      $bind_param1 .= "s";
      $array_val1[] = $sGroupid;

      $bind_param2 .= "s";
      $array_val2[] = $sGroupid;
    }

    //Clinic ID
    if($sDataClinicID != ""){
      $query_main .= " AND PUL.clinic_id = ? ";
      $bind_param1 .= "s";
      $array_val1[] = $sDataClinicID;
      $bind_param2 .= "s";
      $array_val2[] = $sDataClinicID;
    }

    $stmt = $mysqli->prepare($query_main);
    // echo "<br><br>$query_main<br>".$bind_param2;
    // echo $bind_param2;

    if($sDateFrom != ""){
      $stmt->bind_param($bind_param1, ...$array_val1);
    }
    else{
      $stmt->bind_param($bind_param2, ...$array_val2);
    }

    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $key_id = $row['uid'].$row['schedule_date'];
        if(isset($aDataResult[$sGroup][$key_id]))
        $aDataResult[$sGroup][$key_id] = array();
        $aDataResult[$sGroup][$key_id]['uid'] = $row['uid'];
        $aDataResult[$sGroup][$key_id]['pid'] = $row['pid'];
        $aDataResult[$sGroup][$key_id]['uic'] = $row['uic'];
        $aDataResult[$sGroup][$key_id]['visit_id'] = $row['visit_id'];
        $aDataResult[$sGroup][$key_id]['group_id'] = $row['group_id'];
        $aDataResult[$sGroup][$key_id]['clinic_id'] = $row['clinic_id'];
        $aDataResult[$sGroup][$key_id]['schedule_date'] = $row['schedule_date'];
        $aDataResult[$sGroup][$key_id]['visit_date'] = $row['visit_date'];
        $aDataResult[$sGroup][$key_id]['visit_day_before'] = $row['visit_day_before'];
        $aDataResult[$sGroup][$key_id]['visit_day_after'] = $row['visit_day_after'];
        $aDataResult[$sGroup][$key_id]['data_filter'] = '';
        foreach ($arr_dataid_view as $view_data_id){
          if(isset($row[$view_data_id])){
            $aDataResult[$sGroup][$key_id]['data_filter'] .= createDataFilterText($view_data_id, $row[$view_data_id], $row['sub_'.$view_data_id]);
          }
        }// foreach

      }//while
    }
    $stmt->close();

  }//foreach AND

  $iC=0;
  foreach ($aDataResult as $sGroup => $aCode) {
    if($iC==0) $aUIDFinalResult = $aDataResult[$sGroup];
    else $aUIDFinalResult = array_intersect_key($aUIDFinalResult,$aDataResult[$sGroup]);
    $iC++;
  }

   // OR condition

  $aDataResult = array();
  foreach ($aORFilter as $sGroup => $aFilter) {
    $iCount = 0;
    $SQL_DATAID_JOIN = "";
    $SQL_DATAID_WHERE = "";
    foreach($aFilter as $item_group_no => $aDataId){
      $iCount++;
      if($iCount==1) $SQL_DATAID_WHERE .= " or (";
      else $SQL_DATAID_WHERE .= " and ";

      $col_dataid = "PDR_data".$sGroup.$iCount;
      $SQL_DATAID_JOIN .= " JOIN p_data_result $col_dataid ON ($col_dataid.data_id='".$aDataId["dataid"]."' AND $col_dataid.uid=PUV.uid
      AND $col_dataid.collect_date=PUV.visit_date AND $col_dataid.collect_time='00:00:00')";
      $SQL_DATAID_WHERE .= " $col_dataid.data_result ".$aDataId["equation"]." '".$aDataId["value"]."'";

    } //foreach
    $SQL_DATAID_WHERE .= ") ";


    $query_main = "SELECT $SQL_DATAID_VIEW PUV.uid, PUL.pid, P.uic, PUV.visit_id, PUV.group_id, PUL.clinic_id,
    PUV.schedule_date, PUV.visit_date, VL.visit_day_before, VL.visit_day_after
    FROM p_project_uid_list PUL,
    p_project_uid_visit PUV
    LEFT JOIN p_visit_list VL ON ((PUV.proj_id=VL.proj_id ) AND PUV.visit_id=VL.visit_id)
    LEFT JOIN patient_info P ON (PUV.uid=P.uid)
    $SQL_DATAID_VIEW_LEFT_JOIN
    $SQL_DATAID_JOIN
    WHERE PUL.clinic_id IN (
      select distinct clinic_id from i_staff_clinic where s_id=?
    ) AND PUL.uid_status IN ('1', '2')
    AND PUV.proj_id=? $SQL_DATAID_WHERE
    AND PUV.proj_id = PUL.proj_id AND PUV.group_id=PUL.proj_group_id AND PUV.uid = PUL.uid
    ";
    if($sDateFrom != "") $query_main .= " AND  (PUV.schedule_date >= ? AND PUV.schedule_date <= ?) ";
    $stmt = $mysqli->prepare($query_main);
    //echo "<br><br>$query_main<br>";
    if($sDateFrom != "") $stmt->bind_param('ssss',$s_id, $sProjid,$sDateFrom,$sDateTo );
    else $stmt->bind_param('ss',$s_id, $sProjid);

    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $key_id = $row['uid'].$row['schedule_date'];
      // echo "<br>OR: $key_id";
        if(!isset($aUIDFinalResult[$key_id])) $aUIDFinalResult[$key_id] = array();
        $aUIDFinalResult[$key_id]['uid'] = $row['uid'];
        $aUIDFinalResult[$key_id]['pid'] = $row['pid'];
        $aUIDFinalResult[$key_id]['uic'] = $row['uic'];
        $aUIDFinalResult[$key_id]['visit_id'] = $row['visit_id'];
        $aUIDFinalResult[$key_id]['group_id'] = $row['group_id'];
        $aUIDFinalResult[$key_id]['clinic_id'] = $row['clinic_id'];
        $aUIDFinalResult[$key_id]['schedule_date'] = $row['schedule_date'];
        $aUIDFinalResult[$key_id]['visit_date'] = $row['visit_date'];
        $aUIDFinalResult[$key_id]['visit_day_before'] = $row['visit_day_before'];
        $aUIDFinalResult[$key_id]['visit_day_after'] = $row['visit_day_after'];

        $aUIDFinalResult[$key_id]['data_filter'] = '';

        foreach ($arr_dataid_view as $view_data_id){
          if(isset($row[$view_data_id])){
            $aUIDFinalResult[$key_id]['data_filter'] .= createDataFilterText($view_data_id, $row[$view_data_id], $row['sub_'.$view_data_id]);
          }
        }// foreach
      }//while
    }
    $stmt->close();

  }//foreach OR
}// else (select with condition)

krsort($aUIDFinalResult);
$txtrow = "";
foreach($aUIDFinalResult as $row){

//echo "<br>"
//print_r($row);
   if(isset($row['uid'])){
     $txtrow .= addRowSearchUidDashboard($sProjid, $row['uid'], $row['pid'], $row['uic'], $row['clinic_id'], $row['group_id'],
     $row['visit_id'], $row['visit_date'], $row['schedule_date'], $row['visit_day_before'], $row['visit_day_after'], $row['data_filter']);

   }


 }//foreach
echo $txtrow;

   function createDataFilterText($dataID, $dataResult, $dataSubValue){
     $data_view = "";
     if($dataResult == ''){
     }
     else{
       if($dataSubValue !== NULL ) $dataResult = $dataSubValue;

    //   $data_view = "<span class='ml-2 pbadge-blue'>$dataID: </span> <span class='pbadge-white'>$dataResult</span>";
       $data_view = "<div class='fl-wrap  ph20 ptxt-s10 py-1' style='margin-left:10px'>
       <div class='fl-fill pbadge-blue'>$dataID</div>
       <div class='fl-fill pbadge-white'>$dataResult</div>
       </div> ";
     }
     return $data_view;
   }

   function addRowSearchUidDashboard($projid, $uid, $pid, $uic, $clinic_id, $group_id,
   $visit_id, $visit_date, $schedule_date,$window_period_before, $window_period_after,  $data_filter_value){

     $txt_window_period = "";
     if($window_period_before == '0' && $window_period_after == '0'){

     }
     else{ // defined window period
       $txt_window_before = addDayToDate($schedule_date, ($window_period_before)*-1);
       $txt_window_after = addDayToDate($schedule_date, $window_period_after);
       $txt_window_period = "[$txt_window_before - $txt_window_after]";
     }

     $visit_date = ($visit_date == '0000-00-00')? "Wait":"<b>$visit_date</b>";

        $txt_row = "";

        $txt_row .= "<div class='fl-wrap-row p-row ph40 ptxt-s10 view-visit' ";
        $txt_row .= "data-uid='$uid' data-projid='$projid' data-groupid='$group_id'>";

        $txt_row .= "<div class='fl-wrap-col fl-mid pw120 ptxt-b ptxt-blue'> 
                     <div class='fl-fill'>$pid</div><div class='fl-fill ptxt-s8'>$group_id</div>
                     </div>
                     <div class='fl-wrap-col fl-mid pw80 ptxt-b'>
                     <div class='fl-fill'>$uid</div><div class='fl-fill'>$uic</div>
                     </div>
                     <div class='fl-fix fl-mid pw50'>$clinic_id</div>
                     <div class='fl-fix fl-mid pw50'>$visit_id</div>
                     <div class='fl-wrap-col fl-mid pw150 '>
                       <div class='fl-fix ph15'>
                         $schedule_date
                       </div>
                       <div class='fl-fix ph10 ptxt-s8'>
                         $txt_window_period
                       </div>
                     </div>";

        $txt_row .= "<div class='fl-fix fl-mid pw80 '>$visit_date</div>";
        $txt_row .= "<div class='fl-wrap fl-auto fl-mid'>$data_filter_value</div>";
        $txt_row .= "</div>";

        return $txt_row;
  }

?>
