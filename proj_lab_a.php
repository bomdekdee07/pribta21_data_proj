<?
/* Project Thumbnail list  */

include("in_session.php");

include_once("in_php_function.php");
//include("in_db_conn.php");
include_once("in_php_pop99_sql.php");

$uMode = getQS("u_mode");
$s_id = getSS("s_id");

$res = 0;
$msg_error = "";

if($uMode == "select_lab_visit_timepoint"){
  $sUid = getQS('uid');
  $sProjid = getQS('projid');
  $txtrow = "";

  $is_create_lab = 0;
  $is_create_lab2 = 0;
  $prev_lab_order_id = "";
  $create_lab_class = "";

  $query ="SELECT  PVT.visit_id, PVT.timepoint_id,PVT.timepoint_name,
  LO.lab_order_id, LO.lab_order_note, LO.collect_date, LO.collect_time, (select name from p_lab_status where id = LO.lab_order_status) AS lab_order_status

  FROM p_project_visit_timepoint PVT
  LEFT JOIN p_lab_order LO ON ((LO.proj_id = PVT.proj_id AND LO.proj_visit = PVT.visit_id AND LO.timepoint_id = PVT.timepoint_id AND LO.uid = ?) or (LO.proj_id = 'CancelHSIL_II' AND (LO.proj_visit = 'CancelM6' 
			OR LO.proj_visit = 'CancelBL') AND PVT.visit_id = SUBSTRING(LO.proj_visit, 7) AND LO.timepoint_id = PVT.timepoint_id AND LO.uid = ?))

  WHERE PVT.proj_id=? AND PVT.timepoint_status='1' ORDER BY PVT.seq_no asc"; 
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('sss',$sUid, $sUid, $sProjid);
  //echo "$sID, $sProjid / $query";
  if($stmt->execute()){
    $stmt->bind_result($visit_id, $timepoint_id, $timepoint_name, $lab_order_id, $lab_order_note, $visit_date, $visit_time, $lab_order_status);
    while($stmt->fetch()) {
      if($prev_lab_order_id != ""){
        if($create_lab_class == "")  $create_lab_class = "create-lab-order";
      }
      else{
        if($create_lab_class == "create-lab-order") $create_lab_class = "next-create-lab-order";
        else if($create_lab_class == "next-create-lab-order")$create_lab_class = "";
      }
      
      // ADD BY BOM PURPOSE2
      if($sProjid == "PURPOSE2"){
        $create_lab_class = "create-lab-order";
      }

      $txtrow .= addRowVisitTimepoint($visit_id, $timepoint_id, $timepoint_name, $lab_order_id, $lab_order_note, $visit_date, $visit_time, $create_lab_class, $lab_order_status);
      $prev_lab_order_id = $lab_order_id;
    }
  }
  $stmt->close();

  if($txtrow==''){
    $txtrow="
    <div class='fl-wrap-col fl-mid fl-fill ptxt-s10 '>
          -  No Lab order found. -
            <input type='text' class='txtlabnote' data-visitid='BL' data-timepointid='' size='100' placeholder='Lab Order Note' />
            <button class='btn_new_lab_order my-4' data-visitid='BL' data-timepointid=''>
             Add new lab order to project baseline visit
            </button>

            <i class='fa fa-spinner fa-spin fa-lg spinner' style='display:none;'></i>
    </div>";
  }

  $rtn['txtrow'] = $txtrow;
}

else if($uMode == "add_proj_lab_order"){
  $sUid = getQS('uid');
  $sProjid = getQS('projid');
  $sVisitid = getQS('visitid');
  $sTimepointid = getQS('timepointid');
  $sProjpid = getQS('pid');
  $sLabNote = getQS('labnote');

  $sColdate = date('Y-m-d');
  $sColtime = date('H:i:s');

  $laborderid = createLabOrderID($sUid, $sColdate, $sColtime,
  '0', '0', '0', 'A2', $s_id, '24',
  $sProjid,$sProjpid,$sVisitid, $sTimepointid, $sLabNote);

  if($laborderid != ""){
    $res=1;

    addToLog("add proj lab order [$sUid|$sColdate|$sColtime] $laborderid", $s_id);


    $queryInsertLab = "";
    $query ="SELECT PKI.lab_id, PKI.laboratory_id, PKI.sale_opt_id, SP.lab_price, SC.lab_cost
    from p_project_visit_timepoint PVT
    JOIN p_package_item PKI ON PKI.package_id=PVT.package_id
    LEFT JOIN p_lab_test_sale_price SP ON SP.lab_id=PKI.lab_id AND SP.sale_opt_id=PKI.sale_opt_id
    LEFT JOIN p_lab_test_sale_cost SC ON SC.lab_id=PKI.lab_id AND SC.laboratory_id=PKI.laboratory_id
    WHERE PVT.proj_id=? AND  PVT.visit_id=? AND PVT.timepoint_id=?

    ";


    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss',$sProjid, $sVisitid, $sTimepointid );
    //echo "$sID, $sProjid / $query";
    if($stmt->execute()){
      $stmt->bind_result($lab_id, $laboratory_id, $sale_opt_id, $lab_price, $lab_cost);
      while($stmt->fetch()) {
         $queryInsertLab .= "('$sUid','$sColdate','$sColtime' ,'$sProjid','$lab_id','$laboratory_id','$sale_opt_id', '$lab_price', '$lab_cost', '0'),";

      }
    }
    $stmt->close();


    if($queryInsertLab != ""){
      $queryInsertLab = substr($queryInsertLab,0,strlen($queryInsertLab)-1);
      $queryInsertLab = "INSERT INTO p_lab_order_lab_test
      (uid, collect_date, collect_time, proj_id, lab_id, laboratory_id, sale_opt_id, sale_price, sale_cost, is_paid)
      VALUES $queryInsertLab ON DUPLICATE KEY UPDATE
      laboratory_id=values(laboratory_id), sale_opt_id=values(sale_opt_id), sale_price=values(sale_price), sale_cost=values(sale_cost)
      ";

    //  error_log("query: $queryInsertLab");
      $stmt = $mysqli->prepare($queryInsertLab);
      if($stmt->execute()){
        $affect_row = $stmt->affected_rows;
        if($affect_row > 0){
          addToLog("add proj p_lab_order_lab_test [$sUid|$sColdate|$sColtime] $laborderid|amt:$affect_row", $s_id);
        }else{
          error_log("ERROR: add p_lab_order_lab_test [$sUid|$sColdate|$sColtime] $laborderid");
          $msg_error = 'Fail to insert lab test in lab order.';
        }
      }
      else{
        error_log($stmt->error);
        $msg_error .= " ".$stmt->error;
      }
      $stmt->close();
    }

  }
  else{
    $laborderid='';
    $sColdate='';
    $sColtime='';
  }

  $rtn['laborderid']=$laborderid;
  $rtn['coldate']=$sColdate;
  $rtn['coltime']=$sColtime;
}//add_proj_lab_order




  //if(isset($stmt)) $stmt->close();

  $mysqli->close();

  $rtn["res"] = $res;
	$rtn["msg_error"] = $msg_error;

	$returnData = json_encode($rtn);
  echo $returnData;

  function addRowVisitTimepoint($visit_id, $timepoint_id, $timepoint_name, $lab_order_id, $lab_order_note, $visit_date, $visit_time, $create_lab_class, $lab_order_status){
    //$row = "$visit_id $visit_date ";

    $btnCreateLab = "";
    if($lab_order_id == "" ) { // visit lab complete
      $visit_date = ""; $visit_time= "";

      if($create_lab_class == "create-lab-order"){
        $btnCreateLab = "<button class='btn_new_lab_order ptxt-bg-blue pbtn'>Create Lab Order: $timepoint_name</button>";
        $lab_order_note = "<input type='text' class='txt-lab-note' value='$timepoint_name' placeholder='Lab Order Note' />";
      }
      else if($create_lab_class == "next-create-lab-order"){
        $btnCreateLab = "next-create-lab-order";
        $create_lab_class .= " pbtn";
        $btnCreateLab = "<button class='btn_new_lab_order bg-msoft3 pbtn' style='display:none;'>Create Next Lab Order: $timepoint_name</button>";
        $lab_order_note = "<input type='text' class='txt-lab-note' value='$timepoint_name' placeholder='Lab Order Note' style='display:none;' />";
      }

    }
    else{
        $btnCreateLab = "<button class='btn_view_laborder bg-msoft3 pbtn'>View Lab Order</button> <button class='btn_view_labresult  bg-ssoft2 pbtn'>Lab Result</button>";

    }

    $row = "<div class='fl-wrap-row fl-fix fl-mid ph30 p-row labrow'
    data-visitid='$visit_id' data-coldate='$visit_date' data-coltime='$visit_time' data-timepointid='$timepoint_id'>
      <div class='fl-fill'></div>
        <div class='fl-fix pw150 mx-1'>
          <div class='fl-wrap-row fl-fix ph10 ptxt-b bg-mdark1 ptxt-white $create_lab_class'>
            $visit_id
          </div>
          <div class='fl-wrap-row fl-fix ph10 bg-msoft3'>
            $timepoint_name
          </div>
        </div>
        <div class='fl-fix pw100'>$visit_date</div>
        <div class='fl-fix pw100'>$visit_time</div>
        <div class='fl-fix pw100'>$timepoint_id</div>
        <div class='fl-fix pw100'>$lab_order_id</div>
        <div class='fl-fix pw100 mx-1'>$lab_order_note</div>
        <div class='fl-fill mx-1' >$btnCreateLab</div>
        <div class='fl-fix w-150' >$lab_order_status</div>
      <div class='fl-fill'></div>
    </div>";


    return $row;
  }

  function createLabOrderID($sUID, $sColdate, $sColtime,
  $sTotalCost, $sTotalSale, $sWaitresult, $orderstatus, $sID, $roomid,
  $sProjid,$sProjpid,$sProjvisitid,$sProjtimepointid,$sOrdernote){
  global $mysqli;

        $lab_order_id = "";
        $is_call = '10'; // pending call lab
        $now = (new DateTime ())->format('Y-m-d H:i:s');

        $id_prefix = "L".(new DateTime())->format('y'); // prefix & current year eg IH20

        $id_digit = 5; // 00001-99999
        $substr_pos_begin = 1+strlen($id_prefix);
        $where_substr_pos_end = strlen($id_prefix);

          $inQuery = "INSERT INTO p_lab_order (lab_order_id,
          uid, collect_date, collect_time, is_call,  ttl_cost, ttl_sale, wait_lab_result, lab_order_status, staff_order, staff_order_room,
          proj_id, proj_pid, proj_visit, timepoint_id,  lab_order_note )
          SELECT @keyid := CONCAT('$id_prefix',  LPAD( (SUBSTRING(  IF(MAX(lab_order_id) IS NULL,0,MAX(lab_order_id)) ,$substr_pos_begin,$id_digit))+1, '$id_digit','0'))
           ,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
            FROM p_lab_order WHERE SUBSTRING(lab_order_id,1,$where_substr_pos_end) = '$id_prefix' ;
         ";
 //error_log($inQuery);
                $stmt = $mysqli->prepare($inQuery);
                $stmt->bind_param('sssssssssssssss', $sUID,$sColdate,$sColtime, $is_call,
                $sTotalCost,$sTotalSale,$sWaitresult,$orderstatus,$sID,$roomid,
                $sProjid,$sProjpid,$sProjvisitid,$sProjtimepointid, $sOrdernote
                );

                if($stmt->execute()){
                  $inQuery = "SELECT @keyid;";
                  $stmt = $mysqli->prepare($inQuery);
                  $stmt->bind_result($lab_order_id);
                  if($stmt->execute()){
                    if($stmt->fetch()){

                    }
                  }
                }
                else{
                  error_log($stmt->error);
                }
                $stmt->close();
         return $lab_order_id;
  }//createLabOrderID

?>
