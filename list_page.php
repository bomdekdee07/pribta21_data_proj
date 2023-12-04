<?
include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");
include_once("in_setting_row.php");
$isOpt = getQS("opt");

$query ="SELECT page_id,page_title,page_desc,page_link,page_enable,page_fa_icon,page_color FROM i_page_list ORDER BY page_title";

$stmt = $mysqli->prepare($query);
$sHtml = "";
if($stmt->execute()){
  $stmt->bind_result($page_id,$page_title,$page_desc,$page_link,$page_enable,$page_fa_icon,$page_color );
  while ($stmt->fetch()) {
  	if($isOpt=="1"){
  		$sHtml.="<option value='".$page_id."' >".$page_title."</option>";
  	}else{
  		$sHtml.= getPageRow($page_id,$page_title,$page_desc,$page_link,$page_enable,$page_fa_icon,$page_color);
  	}
  }
}
$mysqli->close();

echo($sHtml);	
?>