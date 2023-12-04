<?
include("in_session.php");
include_once("in_php_function.php");

include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

$res = 0;
$msg_error = ''; $newFileName = '';

$maxAllowedFileSize = 50000000;
$s_id = getQS("s_id");
$sUid = getQS('uid');
$sDataid = getQS('dataid');
$sFiletype = getQS('filetype');
$sColdate = getQS('collect_date');
$sColtime = getQS('collect_time');
$sColtime = str_replace(":", "", $sColtime);


 //print_r($_FILES);
//error_log("  fileSize: ".$_FILES['file']['size'] );
if($_FILES['file']['size'] > $maxAllowedFileSize){
  $msg_error = 'File size is more than 5MB (Exceed maximum limit file size)';
}
else{
  $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
  if($sFiletype == "fileimage"){
    if( !in_array($extension, array('jpg', 'jpeg', 'png','gif','bmp') ) )
    {
        $msg_error = 'File type is not valid, Please use .jpg .jpeg .png .gif ';
    }
  }

  if($msg_error == ''){
    $data_result = $sDataid.'_'.$sUid.'_'.$sColdate.'_'.$sColtime.'.'.$extension; //filename

    $oldfile = "filedata/".$sDataid.'_'.$sUid.'_'.$sColdate.'_'.$sColtime.'_'.date('YmdHis').'.'.$extension; //filename
    $newFileName = "filedata/$data_result";

    if(file_exists($newFileName)){ 
		   rename($newFileName, $oldfile);
	  }

    if(move_uploaded_file($_FILES['file']['tmp_name'], $newFileName))
    {

      $lastupdate = date("Y-m-d H:i:s");
      $query = "INSERT INTO p_data_result (uid,collect_date,collect_time,data_id, data_result, lastupdate, s_id)
      VALUES(?,?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE lastupdate=?, s_id=?, data_result=?;
      ";
      //error_log( "query : $uid, $collect_date, $collect_time, $data_id, $data_result, $lastupdate, $s_id/ $query");
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("ssssssssss", $sUid, $sColdate, $sColtime, $sDataid, $data_result, $lastupdate, $s_id, $lastupdate, $s_id, $data_result);
      if($stmt->execute()){
        $affect_row = $stmt->affected_rows;
        if($affect_row > 0){
          $res = 1;
          addToLog("upload file [$sUid] $sDataid:$data_result", $s_id);
        }
      }
      else{
        error_log($stmt->error);
      }
      $stmt->close();
      $res = 1;
    }
  }



}





// return object
$rtn['res'] = $res;
$rtn['msg_error'] = $msg_error;
$rtn['filename'] = $newFileName;

// change to javascript readable form
$returnData = json_encode($rtn);
echo $returnData;



/*
foreach($_FILES['file']['name'] as $i => $name)
{
  echo "<br> $i: $name";
  $tmp_name = $_FILES['file']['tmp_name'][$i];
  $error = $_FILES['file']['error'][$i];
  $size = $_FILES['file']['size'][$i];
  $type = $_FILES['file']['type'][$i];
  if($error === UPLOAD_ERR_OK)
    {
        $extension = getExtension($name);
        if( ! in_array(strtolower($extension, array('jpg', 'jpeg', 'png') )) )
        {
            // Error, invalid extension detected
        }
        else if ($size > $maxAllowedFileSize )
        {
            // Error, file too large
        }
        else
        {
            // No errors
            $newFileName = 'filedata/'.$sUid.'.'.$extension; // You'll probably want something unique for each file.
            if(move_uploaded_file($tmp_file, $newFileName))
            {
                // Uploaded file successfully moved to new location
                $thumbName = 'thumb_image_name';
                //$thumb = make_thumb($newFileName, $thumbName, WIDTH, HEIGHT);
            }
        }
    }

}//foreach

*/

?>
