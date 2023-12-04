<?
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION["s_id"]='DTEST01';
$_SESSION["clinic_id"]='IHRI';
$_SESSION["s_email"]='jeng28@hotmail.com';
$_SESSION["sesskey"]=j_enc("DTEST01");

?>