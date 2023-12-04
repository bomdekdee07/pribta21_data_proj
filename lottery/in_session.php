<?
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 43200);
	ini_set('session.cookie_lifetime', 43200);
	ini_set('session.gc_probability', 1);
	ini_set('session.gc_divisor', 100);
    session_start();
}



//$sSID = getSS("s_id");
//$sSessKey = getSS("sesskey");
?>