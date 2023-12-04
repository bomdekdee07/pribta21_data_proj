<?
include("in_session.php");
include_once("in_php_function.php");

$sSID = getSS("s_id");
$sSessKey = getSS("sesskey");
$sClinicId = getSS("clinic_id");
if($sSID==""){

}

include("in_db_conn.php");
$query = "SELECT IPL.page_id,page_title,page_link,page_fa_icon,page_color,page_seq FROM i_staff_clinic ISC
JOIN i_section_permission ISP
ON ISP.section_id = ISC.section_id
JOIN i_page_list IPL
ON IPL.page_id=ISP.page_id
WHERE ISC.s_id=? AND ISC.sc_status=1 AND ISP.page_allow=1 AND start_date <= NOW() AND (stop_date >= NOW() OR stop_date = '0000-00-00') AND IPL.page_enable=1
AND clinic_id=? ORDER BY page_seq,IPL.page_id";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss",$sSID,$sClinicId );
$sDefault = "";
$sHtml = ""; 
$aPage=array();
if($stmt->execute()){
  $stmt->bind_result($page_id,$page_title,$page_link,$page_fa_icon,$page_color,$page_seq);
  while ($stmt->fetch()) {
  	$aPage[$page_id]["page_title"]=$page_title;
  	$aPage[$page_id]["page_link"]=$page_link;
  	$aPage[$page_id]["page_fa_icon"]=$page_fa_icon;
  	$aPage[$page_id]["page_color"]=$page_color;
  	$aPage[$page_id]["page_seq"]=$page_seq;

  	if($page_seq=="0" && $sDefault=="") $sDefault=$page_link.".php";
  }
}
foreach ($aPage as $iK => $aV) {
	
	 $sHtml .= "<div class='fl-fix btnlink fabtn w-30 fl-mid' data-link='".$aV["page_link"]."' title='".$aV["page_title"]."' style='color:".$aV["page_color"].";'><i class='".$aV["page_fa_icon"]." fa-2x' ></i></div>";
}
$mysqli->close();


$sHtml.="<div class='fl-fix btnlink fabtn w-50 fl-mid' data-link='reception_inc_main_old'>Counter</div> ";


echo($sHtml);
?>

