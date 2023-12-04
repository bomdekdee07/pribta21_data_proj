<?
include_once("in_php_function.php");
$sKeyId = getQS("site");
$sClinicId = easy_dec($sKeyId);

if($sClinicId==""){

	exit();
}

?>

<style>
	.get-q-btn{
		background-color: #337ab7;
		min-height: 312px;
		min-width: 500px;
		max-height: 312px;
		max-width: 500px;
		color:white;
		font-size:36px;
	}
</style>

<div id='divQueue' data-site='<? echo($sKeyId); ?>' class='fl-wrap-row fl-mid'>
	<div class='fl-fill'></div>
	<div id='btnGetQueue' class='fabtn fl-fill roundcorner get-q-btn fl-mid' style='text-align: center'>
		รับบัตรคิว<br/>
		Press here for queue ticket.
	</div>
	<div class='fl-fill'></div>
</div>
<div id='btnGetQueue-loader' class='fl-wrap-col fl-mid' style='display:none;font-size:36px'>
	<div class='fl-fix h-100'></div>
	<div class='fl-fix h-80 fl-mid'><i class='fa fa-spinner fa-spin fa-2x'></i></div>
	<div class='fl-fill'>กำลังพิมพ์บัตรคิว... กรุณารอรับบัตรคิวที่เครื่องพิมพ์<br/>Queue Printing... Please wait at the Queue Printer</div>
</div>

<script>
	//Explain how this work by Jeng
	//Okay this file will just update database queue_print to 1
	//Open another link queue_print_server.php to print

	$(document).ready(function(){
		$("#btnGetQueue").unbind("click");
		$("#btnGetQueue").on("click",function(){
			sSite = $("#divQueue").attr('data-site');
			startLoad($("#divQueue"),$("#btnGetQueue-loader"));
			aData = {u_mode:"q_create",site:sSite};
			objThis = $(this);
			
			callAjax("queue_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Printing Error. Please try again.");
				}else if(rtnObj.res=="1"){

					$.notify("Printing queue #"+rtnObj.q+"\r\nกำลังพิมพ์บัตรคิว #"+rtnObj.q);
				}
				//
				setTimeout(function(){ endLoad($("#divQueue"),$("#btnGetQueue-loader")); }, 3500);
			});

		});
	});
</script>