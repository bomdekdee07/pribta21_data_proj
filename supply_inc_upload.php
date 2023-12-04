<?
include("in_session.php");
include_once("in_php_function.php");
$sReqId =getQS("reqid");
$sSid=getSS("s_id");
$sToday=date("Y-m-d");


$sRemark=getQS("remark");
if($sReqId=="{NEW}" || $sReqId==""){
	echo("Please select Request ID");
	exit();
}
?>
<div id="divFU"  class='fl-wrap-col' data-reqid='<? echo($sReqId); ?>'>
	<div class='fl-wrap-row h-30 fl-mid'>
		Upload file
		<input type='hidden' name='u_mode' value='upload_supply_file' />
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fix w-80'>
			Title
		</div>
		<div class='fl-fill lh-20'>
			<input id='txtTitle' name='file_title' class='fill-box'/>
		</div>
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fix w-80'>
			File
		</div>
		<div class='fl-fill lh-20'>
			<input type="file" name="request_file" id="fileToUpload">
		</div>
	</div>
	<div class='fl-wrap-row h-80 fl-mid'>
		<div class='fl-fill'><input id='btnUpload' type='button' value='Upload'/><i class='fa fa-spinner fa-spin fa-lg' style='display:none'></i></div>
		<!-- div class='fl-fill'><input id='btnCancel' type='button' value='Cancel'/></div -->
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$("#divFU #btnUpload").unbind("click");
		$("#divFU #btnUpload").on("click",function(){
			sTitle=$("#divFU #txtTitle").val().trim();
			if(sTitle==""){
				$.notify("Please enter title");
				return;
			}
			sReqId = $("#divFU").attr('data-reqid');
			aData = new FormData();
			var files = $('#divFU #fileToUpload')[0].files[0];
			aData.append('u_mode', "upload_supply_file");
			aData.append('request_id', sReqId);
			aData.append('file_title', sTitle);
			aData.append('request_file', files);
			
			
	        startLoad($("#divFU #btnUpload"),$("#divFU #btnUpload-loader"));

	        callAjaxForm("supply_a.php",aData,function(jRes,retAData){
	         if(jRes.res=="1"){
	          	$.notify("File Upload","success");
				closeDlg($("#divFU #btnUpload"),"1");
	         }else{
	         	$.notify(jRes.msg);
	         	endLoad($("#divFU #btnUpload"),$("#divFU #btnUpload-loader"));
	         }
	         
	        });

			
				// Custom XMLHttpRequest
				/*
				xhr: function () {
				  var myXhr = $.ajaxSettings.xhr();
				  if (myXhr.upload) {
				    // For handling the progress of the upload
				    myXhr.upload.addEventListener('progress', function (e) {
				      if (e.lengthComputable) {
				        $('progress').attr({
				          value: e.loaded,
				          max: e.total,
				        });
				      }
				    }, false);
				  }
				  return myXhr;
				}
				*/

		});

		$("#divFU #btnCancel").unbind("click");
		$("#divFU #btnCancel").on("click",function(){
			closeDlg($(this),"");
		});
	});
</script>