<? include_once("in_session.php"); 
	include_once("in_php_function.php");
	$sSID = getSS("s_id");
	if($sSID == ""){

		//Show Login Dialog
		echo("Please login and refresh
			<script>
				showLogin();
			</script>
			");

		exit();
	}
	
	$sToday = date("Y-m-d");
?>

<div class='fl-wrap-row' style='background-color: white'>
	<div class='fl-wrap-col right-bar'>
		<div id='divPInfoIdCard' class='' style='display:flex;'>
			<? $_GET["showq"] = "1"; include("patient_info_idcard_new.php"); ?>
		</div>
		<div id='divUidSearchResult-loader' class='fl-fix h-150 fl-mid' style='display:none'>
			<i class='fa fa-spinner fa-spin fa-4x'></i>
		</div>
		<div id='divUidSearchResult' class='fl-wrap-col fl-auto'>
		</div>

	</div>
</div>


<script>
	$(document).ready(function(){
		$("#divPInfoIdCard #btnSearchID").unbind("click");
		$("#divPInfoIdCard").on("click","#btnSearchID",function(){
			sQS = getUidSearchQS();
			$("#divPInfoIdCard .txt-dup").html("");
			if(sQS!==""){
				startLoad($("#divUidSearchResult,#divPInfoIdCard #btnSearchID,#divPInfoIdCard #btnRegisterUid"),$("#divUidSearchResult-loader"));

				sUrl="patient_inc_search_result.php"+sQS;

				$("#divUidSearchResult").load(sUrl,function(){
					$("#divPInfoIdCard").find("#btnRegisterUid").show();

					if($("#divUidSearchResult .btnselectuid").length){
						if($("#divUidSearchResult .btnselectuid").length == 100){
							$.notify("More than 100 rows found.",{className:"success",autoHideDelay: 500});
						}else{
							$.notify($("#divUidSearchResult .btnselectuid").length + " rows found.",{className:"success",autoHideDelay: 500});	
						}
						
					}else{
						
						
					}
					endLoad($("#divUidSearchResult,#divPInfoIdCard #btnSearchID,#divPInfoIdCard #btnRegisterUid"),$("#divUidSearchResult-loader"));
					$("#divUidSearch #fname").focus();
				});

			}else{
				$("#divReceptionMain #btnSearchID").notify("Please enter something.");
			}
		});
		//Refresh Q LIST
		//setInterval(refreshQ, 10000);
		function refreshQ(){
			let vMode = "reception";
			
			$("#divQList").load("queue_inc_list.php?hidename=0&v_mode="+vMode,function(){
				if($("#divQueueHead #show-all-q").is(":checked")){
					$("#divQList .row-notin").show();
				}else{
					$("#divQList .row-notin").hide();
				}
			});
		}


		$("#divUidSearchResult .btnselectuid").unbind("click");
		$("#divUidSearchResult").on("click",".btnselectuid",function(){

			sUid = $(this).attr('data-uid');
			startLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));

			sUrl="patient_info_idcard_new.php?showq=1&uid="+sUid;

			$("#divPInfoIdCard").load(sUrl,function(){
				endLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
			});
		});




	});


</script>
</html>