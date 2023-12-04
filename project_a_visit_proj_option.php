<?
/* Project Special Option */

function doProjectVisitOption($sProjid, $sVisitid, $sUid, $s_id){
//error_log("doProjectVisitOption: $sProjid, $sVisitid, $sUid, $s_id");
//print_r($lst_data_item);
  global $mysqli; // db
  global $msg_error;
  $proj_opt_affect_row = 0;
  $arr_tp = array();
  $arr_tp_use = array();
	if($sProjid == 'IFACT'){
		// create IFACT log timepoint
    $arr_tp['IFACT_ESTRADIOL_LOG_V3'] = array('0', '0.5','1','2','4','6','8','10','12','24');
    $arr_tp['IFACT_ESTRADIOL_FTV_FTC_TAF_LOG_V3'] = array('0', '0.5','1','2','4','6','8','10','12','24');
    $arr_tp['IFACT_TFV_DP_AND_FTC_TP_LOG_V3'] = array('2', '24');
    //$arr_tp['IFACT_URINE_FTV_FTC_TAF_LOG_V3'] = array('1', '2','3','4','5','6');
    //$arr_tp['IFACT_RECTAL_FTC_TFV_LOG_V3'] = array('1', '2');
    $arr_tp['IFACT_FTV_FTC_TAF_LOG_V3'] = array('0', '0.5','1','2','4','6','8','10','12','24');


    if($sVisitid == 'W3'){
      $arr_tp_use['IFACT_ESTRADIOL_LOG_V3'] = $arr_tp['IFACT_ESTRADIOL_LOG_V3'];
    }
    else if($sVisitid == 'W9'){
      $arr_tp_use['IFACT_ESTRADIOL_FTV_FTC_TAF_LOG_V3'] = $arr_tp['IFACT_ESTRADIOL_FTV_FTC_TAF_LOG_V3'];
      $arr_tp_use['IFACT_TFV_DP_AND_FTC_TP_LOG_V3'] = $arr_tp['IFACT_TFV_DP_AND_FTC_TP_LOG_V3'];
      $arr_tp_use['IFACT_FTV_FTC_TAF_LOG_V3'] = $arr_tp['IFACT_FTV_FTC_TAF_LOG_V3'];
    //  $arr_tp_use['IFACT_RECTAL_FTC_TFV_LOG_V3'] = $arr_tp['IFACT_FTV_FTC_TAF_LOG_V3'];

    }
    else if($sVisitid == 'W12'){
      $arr_tp_use['IFACT_TFV_DP_AND_FTC_TP_LOG_V3'] = $arr_tp['IFACT_TFV_DP_AND_FTC_TP_LOG_V3'];
      $arr_tp_use['IFACT_FTV_FTC_TAF_LOG_V3'] = $arr_tp['IFACT_FTV_FTC_TAF_LOG_V3'];
    }

	}//IFACT
  else if($sProjid == 'IMACT'){
		// create IMACT log timepoint
    $arr_tp['IMACT_TESTOSTERONE_LOG_V3'] = array('0DAY', '0.5DAY','1DAY','1.5DAY','2DAY','2.5DAY','3DAY','4DAY','7DAY','14DAY');
    $arr_tp['IMACT_FTV_FTC_TAF_LOG_V3'] = array('0', '0.5','1','2','4','6','8','10','12','24');


    if($sVisitid == 'W4'){
      $arr_tp_use['IMACT_TESTOSTERONE_LOG_V3'] = $arr_tp['IMACT_TESTOSTERONE_LOG_V3'];
    }
    else if($sVisitid == 'W12'){
      $arr_tp_use['IMACT_TESTOSTERONE_LOG_V3'] = $arr_tp['IMACT_TESTOSTERONE_LOG_V3'];
      $arr_tp_use['IMACT_FTV_FTC_TAF_LOG_V3'] = $arr_tp['IMACT_FTV_FTC_TAF_LOG_V3'];
    }

	}//IMACT

  if(count($arr_tp_use)> 0){ // there is timepoint update to log
    $query = "INSERT INTO p_data_log_row (form_id, visit_id, timepoint_id, uid, collect_date, collect_time, row_id)
    SELECT ?, ?,?,?, '0000-00-00', DATE_ADD(IFNULL(max(collect_time),STR_TO_DATE('00:00:00','%H:%i:%s')), INTERVAL 1 second),
    @rowid :=  (IFNULL(max(row_id),0)+1) from p_data_log_row where uid=?";

    foreach($arr_tp_use as $sFormid=>$arrTPData){
      foreach($arrTPData as $sTimepointid){
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss",$sFormid, $sVisitid, $sTimepointid, $sUid, $sUid);
      //  error_log("enter $sFormid, $sVisitid, $sTimepointid, $sUid, $sUid");
        if($stmt->execute()){
        }
        else{
          $msg_error .= $stmt->error;
        }
        $stmt->close();
      }
    }
  }


}//doProjectVisitOption


?>
