<?
	/* Project UID visit schedule list  */
	include("in_session.php");
	include_once("in_php_function.php");
	include_once("in_php_encode.php");

	/*
	$sUID = getQS("uid");
	$sFormid = getQS("formid");
	$sProjid = getQS("projid");
	$sGroupid = getQS("groupid");
	$sVisitid = getQS("visitid");
	$sVisitdate = getQS("coldate");
	$sVisittime = getQS("coltime");
	*/
	$sUID = getQS("uid");
	$sVisitdate = getQS("coldate");
	$sVisittime = getQS("coltime");
	$sFormid = getQS("formid");
	$sVisitid = getQS("visitid");
	$sProjid = getQS("projid");
	$sLang = getQS("lang");

	$title = "UID: $sUID | Proj:$sProjid |  Visit: $sVisitid [$sVisitdate] ";

	$encode_link = encodeSingleLink("$sUID,$sVisitdate,$sVisittime,$sFormid,$sProjid,$sVisitid,$sLang");
	//$link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/ext_index.php?file=p_form_view_link_user&linkdata=$encode_link";
	$link = 'http://161.82.242.164'.dirname($_SERVER['PHP_SELF'])."/ext_index.php?file=p_form_view_link_user&linkdata=$encode_link";
?>

<div class='bg-msoft1' style='height:100%;'>
<div class='ptxt-b ptxt-s14 bg-msoft3'>
<? echo $title; ?>
</div>
<div class='fl-wrap-row fl-mid bg-msoft2 ph50 px-1' >
	<div class='fl-fill ptxt-s10 txt-link' id='txt_link'><? echo $link; ?> </div>
	<div class='fl-fix ptxt-s10 pw200 pbtn bg-mdark2 ptxt-white ptxt-b ptxt-s12' onclick='copyToClipboard("#txt_link")' id='btn_copy_link'> COPY LINK </div>
</div>
	<div class='fl-wrap-row fl-auto fl-mid'>
		<div id="div_qr" class="pt-4"></div>
	</div>
</div>





<script>
$(document).ready(function(){

	$('#div_qr').qrcode({
		text: "<?echo $link; ?>",
		width: 400,
		height: 400
	});
});

function copyToClipboard(element) {
	var text = $("#txt_link").get(0);
	var selection = window.getSelection();
	var range = document.createRange();
	range.selectNodeContents(text);
	selection.removeAllRanges();
	selection.addRange(range);
	//add to clipboard.
	document.execCommand('copy');
}

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
</script>
