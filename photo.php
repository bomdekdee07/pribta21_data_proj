<?
//This file is stand alone. Please do not included it to Pribta21 index. 
include_once("in_php_function.php");


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRIBTA CLINIC PHOTO UPLOAD</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="assets/js/jquery-3.6.0.min.js"></script>  
<script src="assets/js/jquery-ui.min.js"></script> 
<script src="assets/js/pribta.js?t<? echo("=".time()); ?>"></script>
<link rel="stylesheet" href="assets/js/jquery-ui.min.css" />
<link rel="stylesheet" href="assets/css/pribta.css?t<? echo("=".time()); ?>" />
<link href="assets/font-awesome/css/all.min.css" rel="stylesheet" />
</head>

<body id='pribtaBody' style='' >
	<div id='pribta21' class='mainbody fl-wrap-col'>

		<div class='fl-wrap-col fl-mid' >
			<div style='width:100%;text-align: center;'>Please click camera button to upload the photo.</div>
			<form id="form1" enctype="multipart/form-data" method="post" action="uploadnow.php">
			<div><span style='color:green'><i id='btnUpload' class="fas fa-camera fa-6x"></i><i id='btnUpload-loader' style='display:none' class="fas fa-spinner fa-spin"></i></span>
					<input data-id='912425' id="filePhoto" style='display:none' type="file" accept="image/*;capture=camera" />
				<div id="divProgress" class='fl-mid'></div>
			</div>
			</form>
		</div>
	</div>
</body>
<script>


function uploadFile() {
	var fd = new FormData();
	var sId = $("#filePhoto").attr("data-id");
	var sFile = $("#filePhoto").prop("files")[0];
	if(sFile==undefined){ 
		//alert("Please select file and try again");
		return;
	}
	$("#btnUpload").hide();
	$("#btnUpload-loader").show();

	fd.append("photoFile",sFile);
	fd.append("dataid",sId);

	var xhr = new XMLHttpRequest();
	xhr.upload.addEventListener("divProgress", uploadProgress, false);
	xhr.addEventListener("load", uploadComplete, false);
	xhr.addEventListener("error", uploadFailed, false);
	xhr.addEventListener("abort", uploadCanceled, false);
	xhr.open("POST", "photo_a.php");
	xhr.send(fd);
}
  function uploadProgress(evt) {
    if (evt.lengthComputable) {
      var percentComplete = Math.round(evt.loaded * 100 / evt.total);
      $("#divProgress").html(percentComplete.toString() + '%');
    }
    else {
    	$("#divProgress").html("unable to compute");
    }
  }

function uploadComplete(evt) {
/* This event is raised when the server send back a response */
	$("#divProgress").html(evt.target.responseText);
	$("#btnUpload").show();
	$("#btnUpload-loader").hide();
}
function uploadFailed(evt) {
	$("#divProgress").html("There was an error attempting to upload the file.");
}
function uploadCanceled(evt) {
	$("#divProgress").html("The upload has been canceled by the user or the browser dropped the connection.");
}


	$(function(){
		$("#pribta21 #btnUpload").on("click",function(){
			$("#pribta21 #filePhoto").trigger("click");
		});

		$("#pribta21 #filePhoto").on("change",function(event){
			uploadFile();
		});
	});
</script>