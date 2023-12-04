<?
//This is session tutorial made by Ratchapong Kanaprach.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo("START assign 'DEMO' key into the session <br/> \$_SESSION[\"DEMO\"] = \"HELLO SESSION\";<br/>");
$_SESSION["DEMO"] = "HELLO SESSION";

echo("<br/><br/>Here is echo(\$_SESSION[\"DEMO\"];)<br/>");
echo($_SESSION["DEMO"]);


echo("<br/><br/>Here is isset(\$_SESSION[\"DEMO\"];)<br/>");
echo(isset($_SESSION["DEMO"]));


echo("<br/><br/>Here is isset (isset(\$_SESSION[\"DEMO\"])?\"1\":\"0\") after unset(\$_SESSION[\"DEMO\"];)<br/>");
unset($_SESSION["DEMO"]);
echo(isset($_SESSION["DEMO"])?"1":"0");

echo("<br/><br/>if you try to access \$_SESSION[\"DEMO\"] after session_destroy(); !! Error will show up in php_error_log <br/>");



echo("<br/><br/>Here is isset (isset(\$_SESSION[\"DEMO\"])?\"1\":\"0\") after session_destroy(); <br/>");
session_destroy();
echo(isset($_SESSION["DEMO"])?"1":"0");


echo("<br/><br/>if you try to access \$_SESSION[\"DEMO\"] directly after session_destroy(); !! Error will show up in php_error_log <br/>");


session_start();

echo("<br/><br/>START assign 'DEMO' key into the session <br/> \$_SESSION[\"DEMO\"] = \"HELLO SESSION\";<br/>");
$_SESSION["DEMO"] = "HELLO SESSION";


session_destroy();
echo("<br/><br/>Here is echo(\$_SESSION[\"DEMO\"]); after session_destroy(); <br/>");
echo($_SESSION["DEMO"]);

echo("<br/><br/>Here is isset (isset(\$_SESSION[\"DEMO\"])?\"1\":\"0\") after session_destroy(); <br/>");
echo(isset($_SESSION["DEMO"])?"1":"0");

echo("<br/><br/>Here is isset (isset(\$_SESSION[\"DEMO\"])?\"1\":\"0\") after unset(\$_SESSION); <br/>");
unset($_SESSION);
echo(isset($_SESSION["DEMO"])?"1":"0");
echo($_SESSION["DEMO"]);
?>
