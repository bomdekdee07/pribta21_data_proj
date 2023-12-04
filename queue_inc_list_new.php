<?
include("in_session.php");
include_once("in_php_function.php");
$sColDate = getQS("coldate");
$sClinic = getSS("clinic_id");
$sSid = getSS("s_id");
$isHideName =getQS("hidename");
$isHideCall=getQS("hidecall");
$isWaitList=getQS("waitlist");
$sToday = date("Y-m-d");
$sRoom = getSS("room_no");
$sShowFormDone = getQS("is_form_done");
$sShowBill= getQS("showbill");
$isToday = false;
$sMode=getQS("mode");
$sModule=getQS("module");
//Module List : RECEPTION,PHYSICIAN
$sExQTxt="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$aExQTxt_List=str_split($sExQTxt,1);


if($sClinic==""){
  echo("Please login first");
  exit();
}

if($sColDate==""){
  $sColDate = date("Y-m-d");
} 
$isToday=($sColDate==$sToday);


$sRoomList = ""; 
if($sRoom=="2"){
  $sRoomList = ",1,2,";
  //$sRoomList = "AND room_number IN ('1','2')";
  //Check if Form Done
}else{
  $sRoomList = ",".$sRoom.",";
}

include("in_db_conn.php");

//Query section team search order pharmar
$data_section_all = array();
$query_search = "SELECT section_id from p_staff_section where section_enable = '1';";
$stmt = $mysqli->prepare($query_search);

if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    $data_section_all[$row["section_id"]] = $row["section_id"];
  }
}
// print_r($data_section_all);
$stmt->close();

$bind_param = "ss";
$array_val = array($sSid, $sClinic);
$data_section_check = array();

$query = "SELECT
  section_id
FROM i_staff_clinic 
WHERE s_id = ?
AND sc_status = '1' 
AND clinic_id = ?
AND section_id = 'D03_PHAR_SEARCH'
ORDER BY section_id;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    $data_section_check[$row["section_id"]] = $row["section_id"];
  }
}
// print_r($data_section_check);
$stmt->close();

$sFormList = "'DEMO_PRIBTA','BRA_ASSIST_PRIBTA','BRA_ASSIST_PRIBTA_V2'";
$aUidForm = array();
//Check if all form is done.
$query = "SELECT uid,form_id,collect_time FROM p_data_form_done WHERE form_id IN ($sFormList) AND uid IN (SELECT uid FROM i_queue_list WHERE collect_date=? AND uid != '') AND collect_date = ? ORDER BY uid,collect_time";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sColDate,$sColDate);
if($stmt->execute()){
  $stmt->bind_result($uid,$form_id,$collect_time );
  while ($stmt->fetch()) {
    $aUidForm[$uid.$sColDate.$collect_time] = (isset($aUidForm[$uid.$sColDate.$collect_time])?$aUidForm[$uid.$sColDate.$collect_time]:0) + 1;
  }
}

$aBillList=array();
if($sShowBill=="1"){
  //Load Bill List
}

$aLabDone=array();
$aHasDrug=array();

//Check if there is supply required..
$query="SELECT IQL.queue,ISO.supply_code FROM i_queue_list IQL
LEFT JOIN i_stock_order ISO
ON ISO.clinic_id=IQL.clinic_id AND ISO.uid=IQL.uid
AND ISO.collect_date=IQL.collect_date AND ISO.collect_time=IQL.collect_time
LEFT JOIN i_stock_master ISM ON ISM.supply_code = ISO.supply_code
LEFT JOIN i_stock_group ISG ON ISG.supply_group_code = ISM.supply_group_code
LEFT JOIN i_stock_type IST ON IST.supply_group_type=ISG.supply_group_type
WHERE IQL.clinic_id=? AND IQL.collect_date=? AND is_service=0 ";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sClinic,$sColDate);
if($stmt->execute()){
  $stmt->bind_result($queue,$supply_code );
  while ($stmt->fetch()) {
    if($supply_code!=""){
      $aHasDrug[$queue] = "1";
    }
  }
}




//Check lab result
$query="SELECT IQL.queue,PLOLT.lab_id,PLR.lab_result FROM i_queue_list IQL
LEFT JOIN p_lab_order PLO
ON PLO.uid=IQL.uid
AND PLO.collect_date = IQL.collect_date
AND PLO.collect_time = IQL.collect_time
LEFT JOIN p_lab_order_lab_test PLOLT
ON PLOLT.uid=PLO.uid
AND PLOLT.collect_date = PLO.collect_date
AND PLOLT.collect_time = PLO.collect_time
LEFT JOIN p_lab_result PLR
ON PLR.uid=PLOLT.uid
AND PLR.collect_date = PLOLT.collect_date
AND PLR.collect_time = PLOLT.collect_time
AND PLR.lab_id=PLOLT.lab_id
 AND external_lab=0
WHERE IQL.clinic_id=? AND IQL.collect_date = ? AND PLO.lab_order_status <> 'C'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sClinic,$sColDate);
if($stmt->execute()){
  $stmt->bind_result($queue,$lab_id,$lab_result );
  while ($stmt->fetch()) {
    if($lab_id!="" && !isset($aLabDone[$queue]["lab"])) {
      $aLabDone[$queue]["lab"]=1;
      $aLabDone[$queue]["done"]=1;
    }
    if($lab_result=="") $aLabDone[$queue]["done"]=0;
  }
}



$sHtml = "";
$sWaitHtml=""; $sConfirmHtml="";
$query = "SELECT queue,collect_date,collect_time,IQL.uid,uic,fname,sname,clinic_type,IQL.room_no,room_detail,default_room,room_icon,room_status,queue_status,queue_call,prepare_drug_by,check_drug_by,issue_drug_by,receive_by,queue_type,IBD.bill_id,IRL.section_id FROM i_queue_list IQL 
LEFT JOIN i_bill_detail IBD ON IBD.clinic_id = IQL.clinic_id AND IBD.bill_q = IQL.queue AND IBD.bill_q_type=IQL.queue_type AND IBD.bill_date = IQL.collect_date
LEFT JOIN i_bill_list IBL ON IBL.bill_id = IBD.bill_id
LEFT JOIN patient_info PI ON PI.uid = IQL.uid LEFT JOIN i_room_list IRL ON IRL.room_no = IQL.room_no AND IRL.clinic_id = IQL.clinic_id WHERE IQL.clinic_id=? AND IQL.collect_date = ? AND IQL.queue_type=1";

if($sModule=="RECEPTION"){

}else{
  $query .= " AND IQL.uid !='' ";
}

$query.=" ORDER BY ";

$query.="IQL.queue_datetime,IQL.room_no";
/*
if($sColDate==$sToday) $query.=" IQL.room_no,IQL.queue_datetime";
else $query.=" IQL.queue*1";
*/

$aQ=array(); $aCurRoom=array(); $aExtraQ=array();
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sClinic,$sColDate);
if($stmt->execute()){
  $stmt->bind_result($queue,$collect_date,$collect_time,$uid,$uic,$fname,$sname,$clinic_type,$room_no,$room_detail,$default_room,$room_icon,$room_status,$queue_status,$queue_call,$prepare_drug_by,$check_drug_by,$issue_drug_by,$receive_by,$queue_type,$bill_id,$section_id );
  while ($stmt->fetch()) {

    $sHtml="";
    $sColor = ""; $sFormDone = ""; $inWait=false; $sStatIcon="";
    $isToday=($collect_date==$sToday);

    $sClinicType="";
    if($clinic_type=="P"){
      $sClinicType="<span style='color:#00D9D9' title='Pribta Clinic'>P</span>";
    }else if($clinic_type=="T"){
      $sClinicType="<span style='color:orange' title='Tangerine Clinic'>T</span>";
    }else{
      $sClinicType="";
    }
    $isInRoom = (strpos($sRoomList,",".$room_no.",")!==false)?true:false;

    if($sColDate==$sToday && $isWaitList && ($isInRoom) && ($queue_call!="0") ){
      $sTemp="<div class='fabtn btn-q-info fl-wrap-col row-color h-75 row-hover q-row' data-coldate='".$sColDate."' data-queue='".$queue."' data-roomno='$room_no' data-uid='".$uid."' data-coltime='".$collect_time."' data-drugprep='".$prepare_drug_by."' data-drugcheck='".$check_drug_by."' data-drugpick='".$issue_drug_by."' data-paid='".$receive_by."' data-qcall='$queue_call' data-billid='".$bill_id."' data-clinictype='$clinic_type'>
        <div class='h-30 fl-fix fl-mid fs-xlarge'>$queue</div>
        <div class='h-15 fl-fix fl-mid fs-small'>$uid</div>
        <div class='fl-fill lh-15 fs-small fl-mid fw-b' style='text-align:center'>".$fname." ".$sname."</div></div>";
        $sWaitHtml.=$sTemp;
        $sStatIcon="<i class='fa fa-hourglass-start fa-lg'></i>";
    }

    if($queue_call !== 0){
      $inWait=true;
    }
    $sFormDone = "<div class='fl-fix h-15 lh-15'></div>";
    if(isset($aUidForm[$uid.$collect_date.$collect_time])){
      if($aUidForm[$uid.$collect_date.$collect_time]==2){
        $sFormDone = "<div class='fl-fix h-15 fl-mid fw-b lh-15' title='ผู้รับบริการทำแบบสอบถามครบ / Questionnaires Done' style='color:green'>Q</div>";
        //
      }
    }

    if (isset($aLabDone[$queue]["lab"])){
      //have lab request
      if (($aLabDone[$queue]["done"]=="1")){
        //Lab is done
        $sLabResult = "";
        if(isset($_SESSION["DOC"]["LAB_REPORT"]["view"])){
          $sLabResult="fabtn btnviewlabresult";
        }
        
        $sFormDone.="<div class='fl-fix fl-mid h-15 lh-15 $sLabResult' title='ผลแล๊บเสร็จสิ้น / Internal lab is complete.' style='color:green'>
          <i class='fa fa-vial'></i>
        </div>";
      }else{
        //Some lab is not release yet.
        $sFormDone.="<div class='fl-fix fl-mid h-15 lh-15'' title='ผลแล๊บอยู่ในขั้นตอนการออกผล / Internal lab is on process.' style='color:orange'>
          <i class='fa fa-vial'></i>
        </div>";
      }
    }else{
      //Doesn't Have Lab Request
    }
    $sIsPaid = "";
    $sIsPick = "";

    if($bill_id!="" && $receive_by==""){
      $sIsPaid="<div class='fl-fix h-15 fl-mid fw-b lh-15' style='color:orange'>
        <i class='fas fa-bold' title='มีเลขบิลแล้วแต่ยังไม่ชำระ / Bill Id has been created but not paid yet.'></i>
      </div>";
    }else if($receive_by!=""){
      $sIsPaid="<div class='fl-fix h-15 fl-mid fw-b lh-15' style='color:green'>
        <i class='fas fa-dollar-sign' title='ผู้รับบริการชำระเงินแล้ว / Money Received'></i>
      </div>";
    }else{
      $sIsPaid="<div class='fl-fix h-15 lh-15'></div>";
    }

    if(isset($aHasDrug[$queue])){
      if($issue_drug_by!=""){
        $sIsPick="<div class='fl-fix fl-mid' style='color:green'>
          <i class='fas fa-shopping-basket' title='ผู้รับบริการรับของแล้ว / Item was pickedup.'></i>
        </div>";
      }else{
        $sIsPick="<div class='fl-fix fl-mid' style='color:orange'>
          <i class='fas fa-shopping-basket' title='ผู้รับบริการยังไม่ได้รับของ / Some item is wating to collect.'></i>
        </div>";
      }
    }






    $sHtml="<div class=' ";

    //error_log($sRoom." ".$room_no);
    if($sRoom==$room_no || ($room_no=="50" && $sModule=="PHYSICIAN")){
      //User Login so just show those in the room
    }else if($sRoom==""){
      if(($sModule=="PHYSICIAN" && ($section_id=="D05" || $section_id=="D06")) ){
        $sHtml.="";
      }else if($sModule=="RECEPTION" && ($section_id=="D01")){
        $sHtml.="";
      }else if($sModule=="CASHIER" && ($section_id=="D07")){
        $sHtml.="";
      }else if($sModule=="PHARMACY" && ($section_id=="D03")){
        $sHtml.="";
      }else{
        $sHtml.=" row-notin";
      }
    }else{
      $sHtml.=" row-notin";
    }

/*
    if(($sModule=="PHYSICIAN" && ($section_id=="D05" || $section_id=="D06")) || $sRoom==$room_no){
      $sHtml.="";
    }else if($sModule=="RECEPTION" && ($section_id=="D01")){
      $sHtml.="";
    }else if($sMode=="history" || ($sColDate!=$sToday) || ($sModule=="RECEPTION" && $default_room=="1") ){
      $sHtml.="";
    }else if(($queue_status=="2" && $sRoom==$room_no) || $sRoom!=$room_no ){
      $sHtml.=" row-notin";
    }else{
      $sHtml.="";
    }
*/

  $check_search_pharmar = "0";
    foreach($data_section_check as $keyid => $val){
      $check_search_pharmar = (isset($data_section_all[$keyid])?"1":"0");
        if($check_search_pharmar == "1")
          break;
    }
    // echo "TEST:".$check_search_pharmar;

    $sBtnFwdColor = "";
    if($default_room=="9"){
      //กลับบ้าน
      $sBtnFwdColor="style='background-color:green;color:white;border-bottom:solid 1px white'";
    }else if($default_room=="10") 


      $sBtnFwdColor="style='background-color:orange;color:white; '";
      $sHtml.=" q-row main-q-row row-color fl-wrap-row h-".($isToday?"50":"30")." row-hover' data-coldate='".$collect_date."' data-queue='".$queue."' data-roomno='$room_no' data-uid='".$uid."' data-coltime='".$collect_time."' data-drugprep='".$prepare_drug_by."' data-drugcheck='".$check_drug_by."' data-drugpick='".$issue_drug_by."' data-paid='".$receive_by."' data-qcall='$queue_call' data-billid='".$bill_id."' data-istoday='".($isToday?"1":"0")."' data-status='$queue_status' data-clinictype='".$clinic_type."'  >

        <div class='fl-wrap-col w-40 fl-mid row-hover ".((!$isToday || $uid=="")?"'":" btn-q-no fabtn' title='Forward Queue'")." $sBtnFwdColor >
            <div class='q-share-icon fl-fix w-40 h-30 fl-mid' ".((!$isToday || $uid=="" ||$default_room=="9")?" style='display:none'":"")."' >
                <i class='fas fa-share'></i>
            </div>
            <div class='fl-fill fl-mid'>
              $queue
            </div>
        </div>

        <div class='fl-wrap-col fs-small row-hover fabtn btn-q-info' title='Edit Basic Patient Info'>
          <div class='fl-wrap-row lh-15 fw-b fs-smaller' style=''>
            <div class='fl-fill q-uid'>".(($uid=="")?"-Not Bind-":$uid)."</div>
            <div class='fl-fix w-15 fl-mid'>$sClinicType</div>
            <div class='fl-fill'>".(($uic=="")?"":$uic)."</div>
          </div>
          <div class='fl-fill subj_name lh-15' style='overflow:hidden' >
            ".(($isHideName=="1")?"":$fname." ".$sname)."
          </div>";
          if($isToday) $sHtml.=" <div class='fl-wrap-row lh-15' style=''>
            <div class='fl-wrap-row fs-smaller'>
              <div class='fl-fix w-20 fl-mid' style='color:orange'>".(($room_icon!="")?"<i class='$room_icon fa-lg'></i>":"")."</div>
              <div class='fl-fill'>[$room_no] $room_detail</div>
            </div>
          </div>";
        $sHtml.=" </div>
        <div class='fl-wrap-row w-60'>
          <div class='fl-wrap-col'>";

    if(!$isToday || $queue_type!=1 || $default_room=="9" || $default_room=="3"){

    }else if($queue_status=="2"){
      //Patient already in the room
      $sHtml.="
            <div class='fl-fill fl-mid' title='คนไข้อยู่ในห้องแล้ว\r\nSubject is already in the room'>
                <i class='far fa-handshake'></i>
            </div>";
    }else if($queue_call=="1"){
      $sHtml.="
            <div class='fl-fill fl-mid' title='คนไข้รอเรียกเข้ารับบริการที่ห้อง\r\nSubject is on call.'>
                <i class='far fa-hourglass-start'></i>
            </div>";
    }else{
      $sHtml.="
            <div class='fabtn btncallq fl-fill fl-mid ".(($isInRoom)?"":"hideme")." '>
                <i class='far fa-bell'></i>
            </div>";
    }

    $isLab=false;

    if( $sColDate==$sToday && $sModule=="RECEPTION"){
      $isLab=true;
    }

    $sHtml.="
    </div>
          <div class='fl-wrap-col'>
            <div class='fl-wrap-row  lh-".($isToday?"25":"12")."'>
              <div class='fl-wrap-col fs-smaller'>
                $sIsPaid
                $sIsPick
              </div>
              <div class='fl-wrap-col fs-smaller'>
                ".$sFormDone."
              </div>
            </div>
            <div class='fabtn btnorderlab fl-fill fl-mid lh-15 h-15 fs-smaller' ".(($isLab)?"":"style='display:none'")."'>
              LAB
            </div>
            <div class='fabtn btnorderorderpharmar fl-fill fl-mid lh-30 h-15 fs-smaller' ".(($check_search_pharmar == "1")?"":"style='display:none'")."' data-uid='".$uid."' data-coldate='".$sColDate."'>
              <i class='fa fa-ambulance' style='color: green' aria-hidden='true'></i>
            </div>
          </div>
        </div>
    </div>";
    if($sColDate==$sToday){
      //if(strpos($queue, )($queue)
      if($room_no=="50") $aCurRoom[$queue]=$sHtml;
      else if (preg_match('/[A-Z]/', $queue)) {
        $iQ = preg_replace('/[A-Z]/', "", $queue);
        
        $aExtraQ[$iQ*1]=$sHtml;
      }
      else $aQ[$queue] = $sHtml;
    }else{
      $aQ[$queue]=$sHtml;
    }

  }
}


$stmt->close();
$mysqli->close();

$sHtmlX="";

//ksort($aCurRoom);
ksort($aQ);
ksort($aExtraQ);
foreach ($aCurRoom as $queue => $sHt) {
  $sHtmlX .= $sHt;
}

foreach ($aQ as $queue => $sHt) {
  $sHtmlX .= $sHt;
}
foreach ($aExtraQ as $queue => $sHt) {
  $sHtmlX .= $sHt;
}
?>
<div class='main-q-list fl-wrap-col fl-auto'>
<? echo($sHtmlX); ?>
</div>
<div class='waiting-list fl-wrap-col' style='display:none'>
<? echo($sWaitHtml); ?>
</div>
<div class='confirm-list fl-wrap-col' style='display:none'>
<? echo($sConfirmHtml); ?>
</div>