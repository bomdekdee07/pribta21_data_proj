<?
/* Project UID visit schedule list  */
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");


$sUID = getQS("uid");
$sFormid = getQS("formid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");
$sVisitid = getQS("visitid");
$sVisitdate = getQS("coldate");
$sVisittime = getQS("coltime");

$title = "UID: $sUID | Proj:$sProjid |  Visit: $sVisitid [$sVisitdate] ";

$encode_link = encodeSingleLink("$sFormid,$sUID,$sVisitdate,$sVisittime,$sProjid,ihriform");

//$link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/proj_form.php?link='.$encode_link;
//$link = 'http://'.$_SERVER['HTTP_HOST'].'/pribta21/proj_form.php?link='.$encode_link;
//$link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/proj_form.php?link='.$encode_link;
$link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/ext_index.php?file=p_form_view&link=$encode_link";
// $link = 'http://161.82.242.164/'.dirname($_SERVER['PHP_SELF'])."/ext_index.php?file=p_form_view&link=$encode_link";



?>

<div class='bg-msoft1'>
<div class='ptxt-b ptxt-s14 bg-msoft3'>
<? echo $title; ?>
</div>
<div class='fl-wrap-row bg-msoft2 ph50 px-1' >
	<div class='fl-fix ptxt-s10' id='txt_link' style='min-width:350px;max-width:350px; '><? echo $link; ?> </div>
  <div id='btn_copy_link' class='fl-fix fl-mid pw150 bg-mdark2 ptxt-white pbtn ptxt-b ptxt-s12' onclick='copyToClipboard("#txt_link")'>Copy Link</div>
</div>


<div id="div_qr" class="pt-2"></div>


</div>




<script>

$(document).ready(function(){

	$('#div_qr').qrcode({
		text: "<?echo $link; ?>",
		width: 400,
		height: 400
	});
	// $('#btn_copy_link').unbind();
	// $('#btn_copy_link').on("click",function(){
  //   CopyToClipboard('txt_link');
	// });

});


// function CopyToClipboard(containerid) {
//   if (document.selection) {
//     var range = document.body.createTextRange();
//     range.moveToElementText(document.getElementById(containerid));
//     range.select().createTextRange();
//     document.execCommand("copy");
//   } else if (window.getSelection) {
//     var range = document.createRange();
//     range.selectNode(document.getElementById(containerid));
//     window.getSelection().addRange(range);
//     document.execCommand("copy");
//   }
// }

function copyToClipboard(containerid) {
  var text = $("#txt_link").get(0);
  var selection = window.getSelection();
  var range = document.createRange();
  range.selectNodeContents(text);
  selection.removeAllRanges();
  selection.addRange(range);
  //add to clipboard.
  document.execCommand('copy');
}

</script>
