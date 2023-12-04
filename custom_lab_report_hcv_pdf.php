<?
include_once("in_session.php");
include_once("in_php_function.php");
$sSid=getSS("s_id");
$_POST["printid"] = $sSid;
$_POST["lablist"][] = "HCV_VL";

include("lab_oder_report_print_pdf.php");

?>