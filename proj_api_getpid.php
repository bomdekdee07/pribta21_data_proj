<?
/* PID Api
res
1:success pid create
2:pid exist
3:no data (not found data in database)
4:Missing Parameter
5:Invalid Token

0:error


*/
include_once("in_session.php");
include_once("in_php_function.php");

$token = "Ndr38ac1naqi2Ubc4"; 
//$token = "adr3885fda5yuisag4";

$sToken = getQS("token");
$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sSiteid = getQS("site");
$rtn = array();
$sToday_file = date("Y-m-d");
$sToday = date("Y-m-d H:i:s");
$returnData = "";
$txtDataReq = "$sUID,$sProjid,$sGroupid,$sSiteid";

if($token == $sToken){
  if($sUID != "" && $sProjid != "" && $sGroupid != "" && $sSiteid != ""){
      $sSiteid = str_pad($sSiteid, 2, '0', STR_PAD_LEFT);

      include("in_db_conn.php");
      $query ="SELECT C.clinic_id,P.proj_id, PG.proj_group_id, PUL.pid, PUL.clinic_id
      FROM p_clinic C
      LEFT JOIN p_project P ON P.proj_id = ?
      LEFT JOIN p_project_group PG ON (PG.proj_id = P.proj_id AND PG.proj_group_id=?)
      LEFT JOIN p_project_uid_list PUL ON (PUL.uid=? AND PUL.proj_id=P.proj_id)

      WHERE C.clinic_pid=?
      ";

    //  echo "$sUID,$sProjid,$sSiteid / $query";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("ssss",$sProjid,$sGroupid,$sUID,$sSiteid);
        if($stmt->execute()){
          $stmt->bind_result(
            $ihri_clinic_id_found,$ihri_projid_found, $ihri_groupid_found,
           $ihri_pid, $ihri_pid_clinic);
          if($stmt->fetch()){

          }
        }
      $stmt->close();
//echo "$ihri_clinic_id_found, $ihri_pid, $ihri_pid_clinic";
      if($ihri_clinic_id_found == "" || $ihri_clinic_id_found == NULL){
        $rtn["pid"] = '';
        $rtn["nodata"] = 'site';
        $rtn["res"] = 3;
        $rtn["msg_info"] = '';
        $rtn["msg_err"] = "NO Data: Site $sSiteid is not found.";

        $returnData = json_encode($rtn);
        echo $returnData;
      }
      else if($ihri_projid_found == "" || $ihri_projid_found == NULL){
        $rtn["pid"] = '';
        $rtn["nodata"] = 'projid';
        $rtn["res"] = 3;
        $rtn["msg_info"] = "NO Data: Project $sProjid is not found.";

        $returnData = json_encode($rtn);
        echo $returnData;
      }
      else if($ihri_groupid_found == "" || $ihri_groupid_found == NULL){
        $rtn["pid"] = '';
        $rtn["nodata"] = 'groupid';
        $rtn["res"] = 3;
        $rtn["msg_info"] = "NO Data: Project group id $sGroupid is not found.";

        $returnData = json_encode($rtn);
        echo $returnData;
      }
      else{ // found clinic id, proj id, group id
        if($ihri_pid == ""){
          $_SESSION['clinic_id']=$ihri_clinic_id_found;
          $_SESSION['s_id']="eWAT";
          include_once('proj_regis_a_new_pid.php');

        }
        else{

          $rtn["pid"] = $ihri_pid;
          $rtn["uid"] = $sUID;
          $rtn["site"] = $ihri_pid_clinic;
          $rtn["res"] = 2;
          $rtn["msg_info"] = "$sUID already has PID: $ihri_pid in $ihri_pid_clinic.";
          $returnData = json_encode($rtn);
          echo $returnData;
        }
      }

  }else{
    $missing_param = "";
    if($sUID == "") $missing_param .= "uid,";
    if($sProjid == "") $missing_param .= "projid,";
    if($sGroupid == "") $missing_param .= "groupid,";
    if($sSiteid == "") $missing_param .= "site,";
    $missing_param = substr($missing_param,0,strlen($missing_param)-1) ;

      $rtn["pid"] = '';
      $rtn["missing_param"] = $missing_param;
      $rtn["res"] = 4;
      $rtn["msg_info"] = "Missing parameter to create pid. [$missing_param]";
      $returnData = json_encode($rtn);
      echo $returnData;
  }
}
else{

  $rtn["pid"] = '';
  $rtn["res"] = 5;
  $rtn["msg_info"] = 'Invalid token.';

  $returnData = json_encode($rtn);
  echo $returnData;
}

$txtLog = "$sToday|$txtDataReq|$returnData";
file_put_contents('logs/ewat_api_'.$sToday_file.'.txt', $txtLog. PHP_EOL, FILE_APPEND);
/*
$myfile = fopen("logs/ewat_api_".$sToday.".txt", "a+") or die("Unable to open file!");
fwrite($myfile, $txtLog);
fclose($myfile);
*/

?>
