<?
include("inc/connect.php");
include_once("in_php_function.php");

$query = "SELECT id,uid,raw_picture,time_record FROM k_raw_picture ORDER BY uid,time_record";
$stmt =$connect->prepare($query);

if ($stmt->execute()){
} else {
    $errorMessage= 'Error executing!';
}
$stmt->bind_result($id,$uid,$raw_picture,$time_record);

define('UPLOAD_DIR', 'pinfo/');
while ($stmt->fetch()) {
    $image_parts = explode(";base64,", $raw_picture);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    $file = UPLOAD_DIR .$uid.'.png';
    if(file_exists($file)) unlink($file);
    file_put_contents($file, $image_base64);
}

$connect->close();


?>    